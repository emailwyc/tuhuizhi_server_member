<?php
namespace CrmService\Controller\Crmapi;

//use Org\Util\String;
use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class AoyongcrmController extends CommonController implements CrminterfaceController
{

	/*
	*创建会员
	*/
public function createMember(){
		$params['key_admin']=I('key_admin');
		$params['sex']= I('sex');
		$params['name']=I('name');
		$params['mobile']=I('mobile');//手机号
		$params['idnumber']=I('idnumber');//身份证号
		$params['birth']=I('birth');
		$params['address']=I('address');//地址
		$params['wechat']=I('wechat');//微信号
		$params['career']=I('career');//职业
		$params['star']=I('star');//星座
		$params['remark']=I('remark');//备注
		$params['email']=I('email');//邮箱
		$params['level']=I('level');
// 		$params['level']=!empty($params['level'])?$params['level']:1;//会员等级
		$params['province']=I('province');//省
		$params['city']=I('city');//市
		$params['district']=I('district');//区
		$admininfo=$this->getMerchant($params['key_admin']);
		$db=M('mem',$admininfo['pre_table']);
		$save_where['mobile']=array('eq',$params['mobile']);
		$save_where['is_del']=array('NEQ',2);
        $save_where['_logic']='and';
		$Ay_db=$db->where($save_where)->find();//判断手机号是否存在
// 			print_r($Ay_db);die;
		if(!empty($Ay_db)){
            $msg['code']=2001;
		}else{
		    $msg=$this->mem_action($params,$admininfo);
		}         
        
		$par['aoyong_createMember_params']=$params;
		$par['aoyong_createMember_return_msg']=$msg;
		$par['aoyong_createMember_sql']=$db->_sql();
		writeOperationLog($par,'zhanghang');	

		echo returnjson($msg,$this->returnstyle,$this->callback);
	}

	protected function mem_action($params,$admininfo){
	    $db=M('mem',$admininfo['pre_table']);
	    $dat=date('Y-m-d H:i:s');
	    $cardno_num=rand(1,5);
	    $number='';
	    for($i=1;$i<=$cardno_num;$i++){
	        $number=$number.rand(0,9);
	    }
	    $cardno=$admininfo['pre_cardno'].$number.substr(time(),$cardno_num);
	    $data['cardno']=$cardno;
	    $data['datetime']=$dat;
	    $data['usermember']=$params['name'];
	    $data['idnumber']=$params['idnumber'];
	    $data['getcarddate']=date('Y-m-d H:i');
	    $data['phone']=$params['mobile'];
	    $data['mobile']=$params['mobile'];
	    $data['birthday']=$params['birth'];
	    $data['sex']=$params['sex'];
	    $data['score_num']=0;
	    $data['is_del']=I('is_del')!=''?I('is_del'):1;
	    $data['address']=$params['address'];
	    $data['wechat']=$params['wechat'];
	    $data['career']=$params['career'];
	    $data['star']=$params['star'];
	    $data['remark']=$params['remark'];
	    $data['email']=$params['email'];
	    $data['province']=$params['province'];
	    $data['city']=$params['city'];
	    $data['district']=$params['district'];
	    $data['level']=!empty($params['level'])?$params['level']:1;
	    
	    $add=$db->add($data);
	    if(!$add){
	        $cardno_num=rand(1,5);
	        $number='';
	        for($i=1;$i<=$cardno_num;$i++){
	            $number=$number.rand(0,9);
	        }
	        	
	        $cardno=$admininfo['pre_cardno'].$number.substr(time(),$cardno_num);
	        $data['cardno']=$cardno;
	        $adds=$db->add($data);
	        if(!$adds){
	            $msg['code']=104;
	            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	        }
	    }
	    $sql['aoyong_createMember']=$db->_sql();
	    writeOperationLog($sql,'zhanghang');
	    
	    $msg['code']=200;
	    $arr['cardno']=$cardno;
	    $arr['usermember']=$params['name'];
	    $arr['getcarddate']=$dat;
	    $arr['expirationdate']='';
	    $arr['mobile']=$params['mobile'];
	    $arr['sex']=$params['sex'];
	    $arr['idnumber']=$params['idnumber'];
	    $msg['data']=$arr;
	    //echo $db->_sql();echo "oM.1";die;
	    return $msg;
	}


	/*
	*修改会员信息
	*/
	public function editMember(){
		$params['key_admin']=I('key_admin');
	    $params['mobile']=I('mobile');//手机号
	    $params['cardno']=I('carano') ? I('carano') : I('cardno');
	    $params['name']= I('name');
	    $params['idnumber']=I('idnumber');
		$params['sex']= I('sex');
		$params['birth']=I('birth');
		$params['email']=I('email');//邮箱
		$params['address']=I('address');//地址
		$params['wechat']=I('wechat');//微信号
		$params['career']=I('career');//职业
		$params['star']=I('star');//星座
		$params['province']=I('province');//省
		$params['city']=I('city');//市
		$params['district']=I('district');//区
		$params['remark']=I('remark');//备注
		$params['level']=I('level');
// 		if(!preg_match('/^1([0-9]{9})/',$params['mobile'])){
// 			$msg['code']=1050;
// 		}else{
			$admininfo=$this->getMerchant($params['key_admin']);

			$db=M('mem',$admininfo['pre_table']);	
            $save_where['mobile']=array('eq',$params['mobile']);
			$save_where['is_del']=array('NEQ',2);
            $save_where['_logic']='and';
			//根据传入的手机号和身份证号查询表中是否存在这样的数据
			$mem_arr=$db->where($save_where)->find();
			if(!empty($mem_arr)){
				if($mem_arr['cardno']==$params['cardno']){
					$rt['mobile']=$params['mobile'];
					if($params['sex'] != ''){
					    $rt['sex']=$params['sex'];
					}
					$rt['birthday']=$params['birth'];
					$rt['idnumber']=$params['idnumber'];
					$rt['usermember']=$params['name'];
					if($params['address'] != '|'){
					    $rt['address']=$params['address'];//职业
					}
					$rt['wechat']=$params['wechat'];//微信号
					if($params['career'] !=''){
					   $rt['career']=$params['career'];//职业
					}
					if($params['star'] !=''){
					    $rt['star']=$params['star'];//星座
					}
					if($params['email'] !='' ){
					    $rt['email']=$params['email'];//邮箱
					}
					if($params['level'] !=''){
					    $rt['level']=$params['level'];//等级
					}
					if($params['province'] !=''){
					    $rt['province']=$params['province'];//省
					}
					if($params['city'] !=''){
					    $rt['city']=$params['city'];//省
					}
					if($params['district'] !=''){
					    $rt['district']=$params['district'];//省
					}
					if($params['remark'] !=''){
					    $rt['remark']=$params['remark'];//备注
					}
					$rt['is_del']=I('is_del')!=''?I('is_del'):1;
					$sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
					$sqll['aoyong_editMemberss']=$db->_sql();
					if($sv === false){
						$msg['code']=104;
					}else{
						$arr=$db->where(array('cardno'=>$params['cardno']))->find();
						$data['usermember']=$params['name'];
						$data['getcarddate']=$arr['getcarddate'];
						$data['expirationdate']=$arr['expirationdate'];
						$data['mobile']=$params['mobile'];
						$data['sex']=$params['sex'];
						$data['idnumber']=$params['idnumber'];
						$data['cardno']=$params['cardno'];

						$msg=array('code'=>200,'data'=>$data);
					}
				}else{
					$msg['code']=1012;
				}
			}else{
				$rt['mobile']=$params['mobile'];
				if($params['sex'] !=''){
				    $rt['sex']=$params['sex'];
				}
				$rt['birthday']=$params['birth'];
				$rt['idnumber']=$params['idnumber'];
				$rt['usermember']=$params['name'];
				$rt['address']=$params['address'];//地址
				$rt['wechat']=$params['wechat'];//微信号
				$rt['career']=$params['career'];//职业
				$rt['star']=$params['star'];//星座
				if($params['email'] !='' ){
				    $rt['email']=$params['email'];//邮箱
				}
				if($params['level'] !=''){
				    $rt['level']=$params['level'];//等级
				}
				$rt['province']=$params['province'];//省
				$rt['city']=$params['city'];//市
				$rt['district']=$params['district'];//区
			    if($params['remark'] !=''){
					    $rt['remark']=$params['remark'];//备注
			    }
				$rt['is_del']=I('is_del')!=''?I('is_del'):1;
				$sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
				$sqll['aoyong_editMembers']=$db->_sql();
				if($sv === false){
					$msg['code']=104;
				}else{
					$arr=$db->where(array('cardno'=>$params['cardno']))->find();
					$data['usermember']=$params['name'];
					$data['getcarddate']=$arr['getcarddate'];
					$data['expirationdate']=$arr['expirationdate'];
					$data['mobile']=$params['mobile'];
					$data['sex']=$params['sex'];
					$data['idnumber']=$params['idnumber'];
					$data['cardno']=$params['cardno'];

					$msg=array('code'=>200,'data'=>$data);
				}
			}
		//}
		$sqll['aoyong_editMember_params']=$params;
		$sqll['response']=$msg;
		writeOperationLog($sqll,'zhanghang');
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*根据卡号获取会员信息
	*/
	public function GetUserinfoByCard(){
		$params['key_admin']=I('key_admin');
        $params['card']=I('card');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $pd, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				$admininfo=$this->getMerchant($params['key_admin']);

                $db=M('mem',$admininfo['pre_table']);	
                $map['cardno']=array('eq',$params['card']);
				$map['is_del']=array('NEQ',2);
                $map['_logic']='and';
                $mem_data=$db->where($map)->find();

				$sql['aoyong_GetUserinfoByCard']=$db->_sql();
				writeOperationLog($sql,'zhanghang');

				if($mem_data){
				    if($mem_data['is_del']!=1){
				        $msg['code']=1018;
				    }else{
				        $msg['code']=200;
				        	
				        $arr['cardno']=$mem_data['cardno'];//卡号
				        $arr['user']=$mem_data['usermember'];//会员卡用户名
                        $arr['name']=$mem_data['usermember'];
				        $arr['cardtype']=$mem_data['level'];//会员卡类别
				        $arr['status']=$mem_data['status'];//会员卡状态
				        $arr['status_description']=$mem_data['status_description'];//会员卡详细状态
				        $arr['getcarddate']=$mem_data['getcarddate'];//会员卡创建日期
				        $arr['expirationdate']=$mem_data['expirationdate'];//会员卡到期日期
				        $arr['birthday']=date('Y-m-d',$mem_data['birthday']);//会员生日
                        $arr['birth']=date('Y-m-d',$mem_data['birthday']);//会员生日
				        $arr['company']=$mem_data['company'];//会员公司信息
				        $arr['phone']=$mem_data['phone'];//会员手机号
				        $arr['mobile']=$mem_data['mobile'];//会员手机号
				        $arr['address']=$mem_data['address'];//会员地址
				        $arr['score']=$mem_data['score_num'];//会员积分
				        $arr['career']=$mem_data['career'];//职业
				        //$arr['star']=$mem_data['star'];//星座
				        $arr['wechat']=$mem_data['wechat'];//微信号
				        $arr['email']=$mem_data['email'];//邮箱
                        $arr['sex']=$mem_data['sex'];
                        $arr['idnumber']=$mem_data['idnumber'];
				        
				        $msg['data']=$arr;
				    }
				}else{
					$msg['code']=102;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*根据手机号获取会员信息
	*/
	public function GetUserinfoByMobile(){
		$params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				$admininfo=$this->getMerchant($params['key_admin']);

                $db=M('mem',$admininfo['pre_table']);	
				
                $map['mobile']=array('eq',$params['mobile']);
                $map['is_del']=array('NEQ',2);
                $map['_logic']='and';
                
				$mem_data=$db->where($map)->find();
				$sql['aoyong_GetUserinfoByMobile']=$db->_sql();
				writeOperationLog($sql,'zhanghang');

				if($mem_data){
				    
				    if($mem_data['is_del']!=1){
				        $msg['code']=1018;
				    }else{
				        $msg['code']=200;
				        	
				        $arr['cardno']=$mem_data['cardno'];//卡号
				        $arr['user']=$mem_data['usermember'];//会员卡用户名
                        $arr['name']=$mem_data['usermember'];
				        $arr['cardtype']=$mem_data['level'];;//会员卡类别
				        $arr['status']=$mem_data['status'];//会员卡状态
				        $arr['status_description']=$mem_data['status_description'];//会员卡详细状态
				        $arr['getcarddate']=$mem_data['getcarddate'];//会员卡创建日期
				        $arr['expirationdate']=$mem_data['expirationdate'];//会员卡到期日期
                        $arr['birthday']=date('Y-m-d',$mem_data['birthday']);//会员生日
                        $arr['birth']=date('Y-m-d',$mem_data['birthday']);//会员生日
                        $arr['company']=$mem_data['company'];//会员公司信息
				        $arr['phone']=$mem_data['phone'];//会员手机号
				        $arr['mobile']=$mem_data['mobile'];//会员手机号
				        $arr['address']=$mem_data['address'];//会员地址
				        $arr['score']=$mem_data['score_num'];//会员积分
				        $arr['career']=$mem_data['career'];//职业
				        //$arr['star']=$mem_data['star'];//星座
				        $arr['wechat']=$mem_data['wechat'];//微信号
				        $arr['email']=$mem_data['email'];//邮箱
				        $return['aoyong_GetUserinfoByMobile_data']=$arr;
				        writeOperationLog($return,'zhanghang');
				        $msg['data']=$arr;
				    }
				}else{
					$msg['code']=102;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*积分扣除
	*/
	public function cutScore(){
		$params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
//         $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
                $params['scoreno']=abs(I('scoreno'));
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{
				//判断当前用户的积分是否充足
				$admininfo=$this->getMerchant($params['key_admin']);

                $db=M('mem',$admininfo['pre_table']);	
				
				$mem_data=$db->where(array('cardno'=>$params['cardno']))->find();	

				if($mem_data['score_num']<$params['scoreno']){
					$msg['code']=319;
				}else{
				    if($params['key_admin']=='75458833a43dc64df5069e03bdad1ec5' && $params['scoreno']==0){
				        $res['data']='宝泰0积分兑换奖品';
				        $sql['aoyong_cutScore']=$res;
				        writeOperationLog($sql,'zhanghang');
				    }else{
				        $res=$db->where(array('cardno'=>$params['cardno']))->setDec('score_num',$params['scoreno']);//扣除用户积分
				        $sql['aoyong_cutScore']=$db->_sql();
				        writeOperationLog($sql,'zhanghang');
				    }
					//echo $db->_sql();die;
					if($res){

						$data['cardno']=$params['cardno'];//卡号
						$data['scorenumber']=$params['scoreno'];//扣除积分数
						$data['why']=$params['why'];//扣除原由
						$data['scorecode']='';//编码
						$data['cutadd']=1;
						$data['datetime']=date('Y-m-d H:i');
						$admininfo=$this->getMerchant($params['key_admin']);
						$db=M('score_record',$admininfo['pre_table']);
						$add=$db->add($data);
						
						$arr['cardno']=$params['cardno'];//卡号
						$arr['scorenumber']=$params['scoreno'];//扣除积分数
						$arr['why']=$params['why'];//扣除理由
						$arr['scorecode']='';//编码

						$msg['code']=200;
						$msg['data']=$arr;
					}else{
						$msg['code']=104;
					}
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*积分添加
	*/
	public function addintegral(){
		$params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
//         $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        $sign=I('sign');
		$params['scorecode']=I('scorecode');
        $params['membername']=I('membername');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
                $store=I('store');
                $params['scoreno']=abs(I('scoreno'));
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{

                $admininfo=$this->getMerchant($params['key_admin']);
                $db=M('mem',$admininfo['pre_table']);
                //判断当前商户是否设置了生日积分加倍
                $isbirthday=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'birthdaygivescore');
                $isbirthday=json_decode($isbirthday['function_name'], true);
                $scorebeishu=0;
                if ($isbirthday['isenable'] ==1 && $isbirthday['scorenum'] > 0){
                    $userinfo=$db->field('birthday')->where(array('cardno'=>$params['cardno']))->find();
                    if (date('Y-m-d', $userinfo['birthday']) == date('Y-m-d')){
                        $scorebeishu = $isbirthday['scorenum'];
                    }
                }
                //判断某个时间段内是否设置了积分加倍
                $istimegivescore=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'timetotimegivescore');
                $istimegivescore=json_decode($istimegivescore['function_name'], true);
                if ($istimegivescore['isenable'] == 1 && $istimegivescore['time']['start'] != false && $istimegivescore['time']['endtime'] != false && $istimegivescore['scorenum'] > 0){
                    if (time() > (int)$istimegivescore['time']['starttime'] && time() < (int)$istimegivescore['time']['endtime']){
                        $scorebeishu += $istimegivescore['scorenum'];
                    }
                }
                if ($scorebeishu > 0){
                    $params['scoreno']=$params['scoreno']*$scorebeishu;
                }

				
                if($params['key_admin']=='75458833a43dc64df5069e03bdad1ec5' && $params['scoreno']==0){
                    $res['data']='宝泰0积分兑换奖品';
                    $sql['aoyong_cutScore']=$res;
                    writeOperationLog($sql,'zhanghang');
                }else{
                    $res=$db->where(array('cardno'=>$params['cardno']))->setInc('score_num',$params['scoreno']);//添加用户积分
                    $sql['aoyong_addintegral']=$db->_sql();
                    writeOperationLog($sql,'zhanghang');
                }
				//echo $db->_sql();die;
				if($res){

					$data['cardno']=$params['cardno'];//卡号
					$data['scorenumber']=$params['scoreno'];//扣除积分数
					$data['why']=$params['why'];//扣除原由
					$data['scorecode']=$params['scorecode'];//编码
					$data['cutadd']=2;
					$data['datetime']=date('Y-m-d H:i');

					$db=M('score_record',$admininfo['pre_table']);
					$data['store']=$store?$store:'';
					$add=$db->add($data);
					$data=null;
					//判断当前商户是否设置了首次积分赠送
                    $getone=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'firstgivescore');
                    if (is_array($getone) && $getone['function_name'] == 1){
                        $getnums=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'firstgivescorenum');
                        if (is_array($getnums) && $getnums['function_name'] >0){
                            $dbscore=M('score_record',$admininfo['pre_table']);
                            $historyscore=$dbscore->where(array('cardno'=>$params['cardno'],'cutadd'=>2))->find();//查一条即可，只要有数据，则不执行代码
                            if (null == $historyscore){
                                $db=M('mem',$admininfo['pre_table']);
                                $ress=$db->where(array('cardno'=>$params['cardno']))->setInc('score_num',(int)$getnums['function_name']);//添加用户积分
                                if ($ress){
                                    $data['cardno']=$params['cardno'];//卡号
                                    $data['scorenumber']=$getnums['function_name'];//扣除积分数
                                    $data['why']='首次送积分';//扣除原由
                                    $data['scorecode']=date('Y-m-d');//编码
                                    $data['cutadd']=2;
                                    $data['store']=$store?$store:'';
                                    $data['datetime']=date('Y-m-d H:i');
                                    $add=$dbscore->add($data);
                                }
                            }
                        }
                    }
						
					$arr['cardno']=$params['cardno'];//卡号
					$arr['scorenumber']=$params['scoreno'];//扣除积分数
					$arr['why']=$params['why'];//扣除理由
					$arr['scorecode']=$params['scorecode'];//编码

					$msg['code']=200;
					$msg['data']=$arr;
				}else{
					$msg['code']=104;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback); 
	}


	/*
	*积分明细
	*/
    public function scorelist(){
		$params['key_admin']=I('key_admin');
		$params['cardno']=I('cardno');
		$params['page']=I('page');//页数
		$params['lines']=I('lines');//条数
		$sign=I('sign');
		if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
			$params['startdate']=I('startdate');
			$params['enddate']=I('enddate');
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				
				$params['page']=I('page')?abs(I('page')):1;
				$params['lines']=I('lines')?abs(I('lines')):10;
		
				$admininfo=$this->getMerchant($params['key_admin']);

                $db=M('score_record',$admininfo['pre_table']);
				
				$pian=($params['page']-1)*$params['lines'];//从哪开始查询
				$where['cardno']=array('eq',$params['cardno']);
				$where['is_del']=array('eq',1);
				$where['_logic']='and';
				$res=$db->where($where)->order('datetime desc')->limit($pian,$params['lines'])->select();
				
				$sql['aoyong_scorelist']=$db->_sql();
				writeOperationLog($sql,'zhanghang');

				if($res){
					$i=0;
					foreach($res as $k=>$v){
						$arr[$i]['date']=$v['datetime'];
						$arr[$i]['description']=$v['why'];
						if($v['cutadd']==1){
							$arr[$i]['score']='-'.$v['scorenumber'];
						}else{
							$arr[$i]['score']='+'.$v['scorenumber'];
						}
						$i++;
					}

					$msg['code']=200;
					$data['cardno']=$params['cardno'];
					$data['scorelist']=$arr;
					$msg['data']=$data;
				}else{
					$msg['code']=102;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}
	
	/*
	 * 积分补录
	 */
    public function integral_save(){}
    
    
    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){
        
    }

    public function GetUserinfoByOpenid(){

    }
}
?>