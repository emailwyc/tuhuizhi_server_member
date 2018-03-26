<?php
namespace ClientApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
use Thirdwechat\Controller\Wechat\TemplateController;
/**
 * Ｃ端内部接口（仅供其他接口调用，不对外）
 * @date 2016-03-14
 * error:11,12,13,14,15,16,17
 */
class InsideController extends ClientCommonController
{
	public function _initialize(){
		parent::_initialize();
		writeOperationLog($this->params,'ycoin_log');
		$check = $this->checkSign1($this->params);
		$this->returnstyle = true;
		if(!$check){
			returnjson(array('code'=>22,'msg'=>"sign error"), $this->returnstyle, $this->callback);exit;
		}
	}
    
	//C获注册Ｙ币会员接口
	public function addYcoinMem() {
		$params = $this->params;
		$this->emptyCheck($params,array('openid','event'));
        $dbnull=M();
		$c=$dbnull->execute('SHOW TABLES like "'.$this->setting['pre_table'].'coin"');
        if (1 != $c){
			returnjson(array('code'=>22,'msg'=>"table not find!"), $this->returnstyle, $this->callback);exit;
		}		
		if(!isset($params['nickname'])){
			//调取获取用户信息接口
			$subParams = array('key_admin'=>$this->setting['ukey'],'openid'=>$params['openid'],'timestamp'=>time());
			$sign = $this->sign1($subParams);	
			$subParams['sign'] = $sign;
			$url = C('DOMAIN')."/Thirdwechat/Wechat/Getwechatuserinfo/checkOpenidFollowed";
			$result=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
		//	$result= R('Thirdwechat/Wechat/Getwechatuserinfo/checkOpenidFollowed',$subParams);
			if(is_json($result)){
				$result = json_decode($result,true);
				if($result['code']!=4006){
					returnjson(array('code'=>11,'msg'=>"info error"), $this->returnstyle, $this->callback);exit;
				}else{
					$params['nickname']	 = @$result['data']['nickname'];
					$params['headimg']	 = @$result['data']['headimgurl'];
				}
			}else{
				returnjson(array('code'=>104), $this->returnstyle, $this->callback);exit;
			}
		}
		$db = M('coin', $this->setting['pre_table']);
		$arr = $db->where(array('openid'=>$params['openid']))->find();
		if($params['event']=="follow"){//如果是关注事件
			$changeLog = array('key_admin'=>$this->setting['ukey'],'openid'=>$params['openid'],'title'=>'首次关注赠送','remarks'=>"系统自动赠送",'mark'=>'follow');
			$changeLog['sign'] = $this->sign1($changeLog);
			if(empty($arr)){
				$addArr = array('openid'=>$params['openid'],'nickname'=>$params['nickname'],'headimg'=>$params['headimg'],'ycoin'=>0,'submark'=>0);
				$db->add($addArr);
				//赠送金币
				$url = C('DOMAIN')."/ClientApi/Inside/ycoinChangeLog";
				$result=curl_https($url, $changeLog, array('Accept-Charset: utf-8'), 600, true);
				$result = json_decode($result,true);
				if($result['code']==200){
					$db->where(array('openid'=>$params['openid']))->save(array('submark'=>1));
				}
				returnjson(array('code'=>200,'data'=>$addArr), $this->returnstyle, $this->callback);
			}else{
				if($arr['submark']==0){
					//赠送金币
					$url = C('DOMAIN')."/ClientApi/Inside/ycoinChangeLog";
					$result=curl_https($url, $changeLog, array('Accept-Charset: utf-8'), 600, true);
					$result = json_decode($result,true);
					if($result['code']==200){
						$db->where(array('openid'=>$arr['openid']))->save(array('submark'=>1));
					}
				}
			}
		}else{//如果不是关注事件
			if(empty($arr)){
				$addArr = array('openid'=>$params['openid'],'nickname'=>$params['nickname'],'headimg'=>$params['headimg'],'ycoin'=>0,'submark'=>0);
				$db->add($addArr);
				returnjson(array('code'=>200,'data'=>$addArr), $this->returnstyle, $this->callback);
			}
		}
		returnjson(array('code'=>200,'data'=>$arr), $this->returnstyle, $this->callback);
	}

	//积分增减记录
	public function ycoinChangeLog() {
		$params = $this->params;
		$this->emptyCheck($params,array('openid','title','remarks','mark'));
		$db = M('coin', $this->setting['pre_table']);
		$db1 = M('coin_changelog', $this->setting['pre_table']);
		$db2 = M('coin_setting', $this->setting['pre_table']);
		//检测用户Ｙ币是否达到上限
        $Time = date('Ymd',time());
		if(empty($params['coin_change'])){
            $limit_setting = $this->getYcoinSeting($db2,$params['key_admin'],'limit');
			$Time = date('Y-m-d',time());
			$sT = $Time." 00:00:00";
            $userYcoinAllToday = $this->getUserTodayYcoin($params['key_admin'],$params['openid'],$Time);
			if((int)$limit_setting['num']<=$userYcoinAllToday){
				returnjson(array('code'=>13,'data'=>"Ｙ币达到上线"), $this->returnstyle, $this->callback);
			}
			$shengYcoin = (int)$limit_setting['num']-$userYcoinAllToday;
		}
		$user = $db->where(array('openid'=>$params['openid']))->find();
		//$marks = array('follow','register','sign','exchange');//以后判断改值必须在这个范围内
		if($user){
			if(!empty($params['coin_change'])){
				$changeYcoin = (int)$params['coin_change'];
			}else{
                $setting = $this->getYcoinSeting($db2,$params['key_admin'],$params['mark']);
				if(!$setting || $setting['status']==0){
					returnjson(array('code'=>12,'data'=>"系统已禁用对应mark"), $this->returnstyle, $this->callback);
				}else{
					$changeYcoin = $shengYcoin>$setting['num']?$setting['num']:$shengYcoin;
				}
			}
			$addArr = array('userid'=>$user['id'],'openid'=>$params['openid'],'title'=>$params['title'],'coin_change'=>$changeYcoin,'remarks'=>$params['remarks'],'mark'=>$params['mark']);
			$db1->add($addArr);
			//更新用户金币
			$changeYcoins = $changeYcoin;
			if($changeYcoin<0){
				$changeYcoin=abs($changeYcoin);
				$db->where(array('id'=>$user['id']))->setDec('ycoin',$changeYcoin);
			}else{
				if(isset($userYcoinAllToday)) {
                    $userYcoinAllToday += $changeYcoin;
                    $this->setUserTodayYcoin($params['key_admin'], $params['openid'], $userYcoinAllToday, $Time);
                }
				$db->where(array('id'=>$user['id']))->setInc('ycoin',$changeYcoin);
			}
			if($params['key_admin'] == '93afca9f6607d7e311dd3888a219d188'){
			    $this->templateContent($params['title'],$user['ycoin'],$changeYcoins,$params['openid'],$params['key_admin']);
			}
		}else{
			returnjson(array('code'=>11,'data'=>"用户不存在"), $this->returnstyle, $this->callback);
		}
		returnjson(array('code'=>200), $this->returnstyle, $this->callback);
	}


    //Y币增减接口
    public function ycoinchange() {
        $params = $this->params;
        $this->emptyCheck($params,array('key_admin','openid','nickname','headimg','event','mark'));
        //目前只允许天河城商户调用;
        if(!in_array($params['key_admin'],array('202cb962ac59075b964b07152d234b70','93afca9f6607d7e311dd3888a219d188'))){
            returnjson(array('code'=>17,'data'=>"该商户ycoin变更暂不可用"), $this->returnstyle, $this->callback);
        }
        $db = M('coin', $this->setting['pre_table']);
        $db1 = M('coin_changelog', $this->setting['pre_table']);
        $db2 = M('coin_setting', $this->setting['pre_table']);
		//获取有没有该用户,没有则创建
        $arr = $db->where(array('openid'=>$params['openid']))->find();
        $user_ycion = $arr['ycoin'];
		if(empty($arr)){
			$arr = array('openid'=>$params['openid'],'nickname'=>$params['nickname'],'headimg'=>$params['headimg'],'ycoin'=>0,'submark'=>0);
            $arr['id'] = $db->add($arr);
            $user_ycion=0;
		}
		//开始赠送ycoin
        $setting = $this->getYcoinSeting($db2,$params['key_admin'],$params['event']);
        $Time = date('Ymd',time());
        if($setting && $setting['status']!=0){
            //判断ycoin赠送是否超出上限
            $limit_setting = $this->getYcoinSeting($db2,$params['key_admin'],'limit');
            $userYcoinAllToday = $this->getUserTodayYcoin($params['key_admin'],$params['openid'],$Time);
            if((int)$limit_setting['num']<=$userYcoinAllToday){
                returnjson(array('code'=>13,'data'=>"Ｙ币达到上线"), $this->returnstyle, $this->callback);
            }
            $shengYcoin = (int)$limit_setting['num']-$userYcoinAllToday;
            $changeYcoin = $shengYcoin>$setting['num']?$setting['num']:$shengYcoin;
        }else{
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
		}
        $addArr = array('userid'=>$arr['id'],'openid'=>$params['openid'],'title'=>$params['mark'],'coin_change'=>$changeYcoin,'remarks'=>'系统自动赠送','mark'=>$params['event']);
        $db1->add($addArr);
        $changeYcoins = $changeYcoin;
        if($changeYcoin<0){
            $changeYcoin=abs($changeYcoin);
            $db->where(array('id'=>$arr['id']))->setDec('ycoin',$changeYcoin);
        }else{
            $userYcoinAllToday +=$changeYcoin;
            $this->setUserTodayYcoin($params['key_admin'],$params['openid'],$userYcoinAllToday,$Time);
            $db->where(array('id'=>$arr['id']))->setInc('ycoin',$changeYcoin);
        }
        if($params['key_admin'] == '93afca9f6607d7e311dd3888a219d188'){
            $this->templateContent($params['mark'],$user_ycion,$changeYcoins,$params['openid'],$params['key_admin']);
        }
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }

    protected function getYcoinSeting($db2,$key_admin,$event){
        $m_info = $this->redis->get('ycoin:setting1:'.$event.':'. $key_admin);
        if ($m_info) {
            return json_decode($m_info, true);
        } else {
            $limit_setting = $db2->where(array('mark'=>$event))->find();
            if ($limit_setting) {
                $this->redis->set('ycoin:setting1:'.$event.':'. $key_admin, json_encode($limit_setting),array('ex'=>300));//5分钟
            }
            return $limit_setting;
        }
        return false;
	}

    protected function getUserTodayYcoin($key_admin,$openid,$datestr){
		$m_info = (int)$this->redis->get('ycoin:userycoin:'.$datestr.':'.$openid.':'.$key_admin);
		return $m_info;
    }

    protected function setUserTodayYcoin($key_admin,$openid,$ycoin,$datestr){
        $this->redis->set('ycoin:userycoin:'.$datestr.':'.$openid.':'.$key_admin, $ycoin,array('ex'=>86400));//一天
    }


    public function templateContent($mark,$ycion,$changeycion,$openid,$key_admin){
        $template_id = '7jlkuYaPEeLA7dX69um2fTOfk9eIGOaSvadJlKY5xx8';
        
        if($changeycion<0){
            $str = '减少'.abs($changeycion).'Y币';
        }else{
            $str = '增加'.$changeycion.'Y币';
        }
        
        $template_ycion = $ycion+$changeycion;
        
        $template = new TemplateController();

        $send =array( array(
            'touser'=>$openid,
            'template_id'=>$template_id,
            'url'=>'',
            'data'=>array(
                'first'=>array(
                    'value'=>'您好，您的Y币有新的变动',
                    'color'=>'#173177',
                ),
                'keyword1'=>array(
                    'value'=>$mark,
                    'color'=>'#173177',
                ),
                'keyword2'=>array(
                    'value'=>$str,
                    'color'=>'#173177',
                ),
                'keyword3'=>array(
                    'value'=>$template_ycion.'Y币',
                    'color'=>'#173177',
                ),
                'remark'=>array(
                    'value'=>'感谢参与',
                    'color'=>'#173177',
                ),
            )
        ));
        $msg = $template->insideSendMessage($send,$key_admin,'');
    }
    
}

?>
