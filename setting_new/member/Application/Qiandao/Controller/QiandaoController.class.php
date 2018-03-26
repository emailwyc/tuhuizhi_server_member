<?php
/**
 *  签到接口
 */
namespace Qiandao\Controller;


use Think\Model;
class QiandaoController extends CommonController{
    
    public function __construct(){
        
        
        $this->first();
    }
    
    /**
     * 签到动作方法，获取的积分数为按商场设置的规则生成
     * 接口必须用ajax和get提交
     * $supermarket 商场id
     * $uid                会员id
     * 
     */
    public function qiandaoactive() {
    
        if (IS_GET){
            $uid                                       = I('get.uid','','htmlspecialchars');
            $supermarketid                      = I('get.supermarket','','htmlspecialchars');//商场id
            $qiandaotype                        = I('get.qdtype','','htmlspecialchars');
            if ($uid==null || $uid =='' || I('openid')=='null'){
                echo I('get.callback','','htmlspecialchars').'('.json_encode(array('msg'=>'erroe','status'=>false)).')';exit();
            }
//             if (empty($uid) || empty($supermarketid)|| empty($qiandaotype)){
//                 $return =array('msg'=>'未获取到必要信息','status'=>false);
//             }else {
                $muser=M('user','total_');
                $finduser                       = $muser->where(array('openid'=>$uid))->count();
                if ($finduser<=0){
                    $return =array('msg'=>'未找到此会员','status'=>false);
                }else{
//                         $qdtype                            = D('qdtype','','DB_CONFIG1');
//                         $qiandaotypes                       = $qdtype->where(array('qdtype'=>$qiandaotype))->find();
//                         if (null == $qiandaotypes){
//                             $return =array('msg'=>'签到类型未找到','status'=>false);
//                         }else{
//                         $Msupermarket                      = D('supermarket');
//                         $supermarket                         = $Msupermarket->where(array('id'=>$supermarketid))->find();
//                         if (null == $supermarket){
//                             $return =array('msg'=>'未找到商场','status'=>false);
//                         }else{
                            $qiandaolog                                = D('qiandaolog');//保存积分数
                            //判断今天是否已经签到过
                            $isqiandao                              = $qiandaolog->where(array('uid'=>$uid,'datetime'=>date('Y-m-d')))->select();
                            if (!empty($isqiandao)){//如果已经签到过
                                $return                                 = array('msg'=>'今日您已经签到过','status'=>false);
                            }else {//如果今日没有签到，则进行积分获取和签到插入
                                //查询签到表
                                $qiandao                            = D('qiandao');//不保存积分数
                                $sel                                    = $qiandao->where(array('uid'=>$uid))->find();
                                if (null==$sel) {//如果之前没有此用户
                                    $dataq['uid']                       = $uid;
                                    $dataq['lastqddate']            = date('Y-m-d');
                                    $dataq['lxqdtianshu']          = 1;
                                    $qiandao->add($dataq);
                                }else {//如果之前有此用户
                                    if ($sel['lastqddate'] == date("Y-m-d",strtotime("-1 day"))) {//如果等于昨天，则连续签到天数加1
                                        $dataq['uid']                       = $uid;
                                        $dataq['lastqddate']            = date('Y-m-d');
                                        $dataq['lxqdtianshu']           = $sel['lxqdtianshu']+1;
                                        $dataq['id']                        = $sel['id'];
                                    }else {//如果不是昨天，则连续签到天数设为1
                                        $dataq['uid']                       = $uid;
                                        $dataq['lastqddate']            = date('Y-m-d');
					if(isset($_GET['type']) && $_GET['type']==0){
                                        	$dataq['lxqdtianshu']          = 1;
					}else{
						$dataq['lxqdtianshu']           = $sel['lxqdtianshu']+1;
					}
                                        $dataq['id']                        = $sel['id'];
                                    }
                                    $Mqiandao=M('qiandao','qiand_','DB_CONFIG1');
                                    $save=$Mqiandao->save($dataq);
                                }
                                if ($save!==false){//积分获取，不同时间段，会有不同的积分规则
//                                     $Mscoreplan=D('scoreplan');
//                                     $scoreplansel=$Mscoreplan->where(array('scoreplan'=>$supermarket['scoresplan'],'marketid'=>$supermarketid))->select();
//                                     if (null != $scoreplansel){
//                                         $fanwei=getmath($scoreplansel,$dataq['lxqdtianshu']);
//                                         if (1==$supermarket['scoreplan']){
//                                             $jifen=$fanwei['scores'];
//                                         }else {
//                                             $jifen=mt_rand($fanwei[0],$fanwei[1]);
//                                         }
//                                     }else {//如果没有查到积分，
                                        $jifen=0;
//                                     }
                                    //插入签到log表
                                    $data['uid']                            = $uid;
                                    $data['datetime']                   = date('Y-m-d');
                                    $data['scores']                       = intval($jifen);//随机的积分
                                    $data['qdtype']                      = '111';$qiandaotypes['qdtype'];
                                    $data['doip']                          = get_client_ip();
                                    //$qiandaolog->add($data);
                                    $doqiandao                          = $qiandaolog->add($data);
                                }
                                
                                //增加用户的总积分
                                $userscore=$muser->where(array('openid'=>$uid))->setInc('score',intval($jifen));
                                //判断当前连续签到天数是否可以抽奖
                                $dscoredraw                             = M('scoredraw','qiand_');//D('scoredraw');
                                $find                                          = $dscoredraw->where(array('adminuid'=>$supermarketid))->find();
                                if (null != $find){
                                    if (in_array($dataq['lxqdtianshu'],explode(',',$find['thisdaydraw']))){
                                        $isdraw                             = 1;
                                    }else $isdraw                       = 2;
                                }else {
                                    $isdraw                                 = 2;
                                }
                                
                                if ($doqiandao && $userscore!==false){
                                    $return=array('msg'=>'签到成功','tianshu'=>$dataq['lxqdtianshu'],'isdraw'=>$isdraw,'jifen'=>intval($jifen),'status'=>true);
                                }
                            }
//                         }
//                     }
                }
//             }
            //根据请求方式返回信息
            if (I('get.callback','','htmlspecialchars')){
                echo I('get.callback','','htmlspecialchars').'('.json_encode($return).')';exit();
            }else{
            echo json_encode($return);exit();
            }
        }else{
            return ;
        }
    }
    
    
    
    /**
     * 今天是否已经签到过,用于页面打开时按钮的判断
     */
    public function todayisqd(){
        //判断今天是否已经签到过
        $uid                                       = I('get.uid','','htmlspecialchars');
        if (empty($uid)){
            $return =array('msg'=>'未获取到必要信息','status'=>false);
        }else {
            $qiandaolog                                = D('qiandaolog');//保存积分数
            $isqiandao                              = $qiandaolog->where(array('uid'=>$uid,'datetime'=>date('Y-m-d')))->select();
            if (!empty($isqiandao)){//如果已经签到过
                $return                                 = array('msg'=>'今日您已经签到过','status'=>false);
            }else {
                $return                                 = array('msg'=>'今日未签到','status'=>true);
            }
        }
        //根据请求方式返回信息
        if (I('get.callback','','htmlspecialchars')){
            echo I('get.callback','','htmlspecialchars').'('.json_encode($return).')';exit();
        }else{
            echo json_encode($return);exit();
        }
    }
    
    
    /**
     * 获取连续签到总数,只有天数
     */
    public function getqddates(){
        $uid                                       = I('get.uid','','htmlspecialchars');
        if (empty($uid)){
            $return =array('msg'=>'未获取到必要信息','status'=>false);
        }else {
            $qiandao                                = D('qiandao');//保存积分数
            $sel                              = $qiandao->where(array('uid'=>$uid))->find();
            $return                             = array('dates'=>intval($sel['lxqdtianshu']),'status'=>true);
        }
        //根据请求方式返回信息
        if (I('get.callback','','htmlspecialchars')){
            echo I('get.callback','','htmlspecialchars').'('.json_encode($return).')';exit();
        }else{
            echo json_encode($return);exit();
        }
    }
    
    /**
     * 获取连续签到天数和连续签到天数内每天的积分和抽奖历史
     */
    public function getday_score(){
        $uid                                       = I('get.uid','','htmlspecialchars');
//         $supermarketid                      = I('get.supermarketid','','htmlspecialchars');//商场id，用于查询抽奖计划
//         if(empty($supermarketid)){
//             $return=array('msg'=>'没有获取到必要信息','rows'=>'','total'=>'','status'=>false);
//         }else {
//             $seldrawplan                  = D('scoredraw')->where(array('supermarketid'=>$supermarketid))->find();
//             if (null == $seldrawplan){
//                 $return=array('msg'=>'此商场id未获取到抽奖计划','rows'=>'','total'=>'','status'=>false);
//             }else {
                $isbuy                                    = I('get.isbuy');//是否是积分抽奖的结果
                $isbuy                                    = empty($isbuy)?0:$isbuy;
                if (empty($uid)){
                    $return =array('msg'=>'未获取到必要信息','status'=>false);
                }else {
                    $qiandao                                = D('qiandao');//保存积分数
                    $sel                                        = $qiandao->where(array('uid'=>$uid))->find();
                    $qiandaolog                         = D('qiandaolog');
                    $log                                        = $qiandaolog->field('scores,datetime')->where(array('uid'=>$uid))->order('datetime asc')->limit($sel['lxqdtianshu'])->select();
                    //$scores                                 = array_column($log,'scores');
                    //dump($log);
                    //echo $qiandaolog->_sql();
                    //查询抽奖
                    $ddraw                                  = D('draw');
                    $seldraw                                 = $ddraw->where(array('draw'=>$uid,'isbuy'=>$isbuy,'code'=>0,'openId'=>$uid))->order('date desc')->select();
                     //echo $ddraw->_sql();
                     //dump($seldraw);
                    foreach ($log as $key => $val){
                        foreach ($seldraw as $k => $v){//echo $v['date'];
                            if ($val['datetime'] == $v['date']){
                                $vala=array_merge($val,$v);
                                $log[$key]=$vala;
                            }
                        }
                    }
                    $return                             = array('tianshu'=>intval($sel['lxqdtianshu']),'drawplan'=>$seldrawplan['thisdaydraw'],'jifen'=>$log,'status'=>true);
                }
//             }
//         }
        echo returnjson($return,$this->returnstyle,$this->callback);
//         //根据请求方式返回信息
//         if (I('get.callback','','htmlspecialchars')){
//             echo I('get.callback','','htmlspecialchars').'('.json_encode($return).')';exit();
//         }else{
//             echo json_encode($return);exit();
//         }
    }
    
    
    /**
     * 获取总积分
     */
    public function scoresall(){
        $uid                                       = I('get.uid','','htmlspecialchars');
        if (empty($uid)){
            $return =array('msg'=>'未获取到必要信息','status'=>false);
        }else {
            $qiandao                                = D('user');//保存积分数
            $sel                                        = $qiandao->field('score')->where(array('openid'=>$uid))->find();
            $return                                 = array('scores'=>intval($sel['score']),'status'=>true);
        }
        
        //根据请求方式返回信息
        if (I('get.callback','','htmlspecialchars')){
            echo I('get.callback','','htmlspecialchars').'('.json_encode($return).')';exit();
        }else{
            echo json_encode($return);exit();
        }
        
        
        
    }
    
    
    
    
    
    /**
     * 积分历史记录
     */
    public function scorelist(){
        $uid                                       = I('get.uid','','htmlspecialchars');
        $where                                  = empty($uid)?array():array('uid'=>$uid);
        $ddraw                                  =D('qiandaolog','','DB_CONFIG1');
        $page                                   = !empty($_GET['page']) ?I('get.page'):1;
        $rows                                   = !empty($_GET['rows']) ? I('get.rows'):10;
        $p                                  = ($page - 1) * $rows;
        $join                                  = ' join  `total_user` on `total_user`.`openid`=`qiand_qiandaolog`.`uid` join `qiand_qdtype` on `qiand_qiandaolog`.`qdtype`=`qiand_qdtype`.`qdtype` ';
        $c                                  = $ddraw->join($join)->where($where)->count();
        $list                               = $ddraw->join($join)->where($where)->order('datetime desc')->limit ( $p, $rows )->select();
        if (null!=$list){
            $data=array('msg'=>'查询结果成功','rows'=>$list,'total'=>$c,'status'=>true);
        }else {
            $data=array('msg'=>'没有查询到相关数据','rows'=>$list,'total'=>$c,'status'=>false);
        }
        echo returnjson($data,$this->returnstyle,$this->callback);
    }
    
/**
临时接口
*/    
public function saveuser(){
    if (I('openid')==null || I('openid') == '' || I('openid')=='null'){
        echo returnjson(array('msg'=>'添加失败','status'=>false),$this->returnstyle,$this->callback);exit();
    }
	$db=D('user');
	if($db->where(array('openid'=>I('get.openid')))->count() == 0){
		$data['openid']=I('get.openid');
		$data['headimgurl']=urldecode(I('get.headimgurl'));
		$data['uname']=preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', I('get.uname'));
		$data['time']=date('Y-m-d H:i:s');
		$add = $db->add($data);
		}else{
		$add=1;
	}
	//type值是0清零
	if(isset($_GET['type']) && $_GET['type']==0){
		//判断是否清零签到记录
		$dbqd=D('qiandao');
		$find=$dbqd->where(array('uid'=>I('get.openid')))->find();
		//echo $dbqd->_sql();
		if ($find!=null && $find['lastqddate'] < date("Y-m-d",strtotime("-1 day"))) {
		//echo 111;
		$change=$dbqd->where(array('uid'=>I('get.openid')))->save(array('lxqdtianshu'=>0));
		//echo $dbqd->_sql();
		}
	}
	if($add){
		echo returnjson(array('msg'=>'添加成功','status'=>true),$this->returnstyle,$this->callback);
	}else{
		echo returnjson(array('msg'=>'添加失败','status'=>false),$this->returnstyle,$this->callback);
	}



}




}

?>
