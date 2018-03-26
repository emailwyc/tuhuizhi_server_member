<?php
/**
 * Created by EditPlus.
 * User: zhanghang
 * Date: 6/24/16
 * Time: 10:10 AM
 */

namespace Integral\Controller;
use Think\Controller;
use Common\Controller\CommonController;
use common\ServiceLocator;
use PublicApi\Controller\QiniuController;

class IntegralController extends CommonController
{

    public $admin_arr;
    public $key_admin;

    public function _initialize(){
        parent::__initialize();
        $key_admin=I('key_admin');
        if(empty($key_admin)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }else{
            $admin_db=M('admin','total_');
            $admin_arr=$admin_db->where(array('ukey'=>$key_admin))->find();
            if(empty($admin_arr)){
                echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
            }else{
                $this->admin_arr=$admin_arr;
                $this->key_admin=$key_admin;
            }
        }
    }

    /**
     * 获取颜色接口
     */
    public function get_integral_color(){
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'integralcolorset');
        if($find){
            $msg['code']=200;
            $msg['data']=$find['function_name'];
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 查询活动下所有券
     * @param $key_admin $type_id
     * @return mixed
     */
    public function curl_api(){
        $type_id=I('type_id');
        $params['buildid']=I('buildid')?I('buildid'):"";
        $params['openid']=$this->userucid;

        if($params['openid']){
            $user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$params['openid']);
            $use_db=M('member_code','total_');
            if($user_arr != 2000){
                $code_arr=$use_db->where("code='".$user_arr['level']."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
            }else{
                $level='default';
                $code_arr=$use_db->where("code='".$level."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
            }
        }

// 		        $type_db=M('type','integral_');
// 		        $type_arr=$type_db->where(array('id'=>$type_id))->find();
// 		        if(!$type_arr){
// 		            $msg['code']=1081;
// 		            returnjson($msg,$this->returnstyle,$this->callback);exit();
// 		        }
        $integral_db=M('activity','integral_');//查询活动ID

        $activityService = ServiceLocator::getActivityService();
        $integral_arr = $activityService->getOnce($this->admin_arr['id'], 'ZHT_YX', $params['buildid'], 0);
        
        $erp_integral_arr = $activityService->getOnce($this->admin_arr['id'], 'ERP_YX', $params['buildid'], 0);
//         $zht_where['admin_id']=array('eq',$this->admin_arr['id']);
//         $zht_where['buildid']=array('eq',$params['buildid']);
//         $zht_where['type']=array('eq','ZHT_YX');
//         $zht_where['_logic']='and';
//         $integral_arr=$integral_db->where($zht_where)->find();

//         $erp_where['admin_id']=array('eq',$this->admin_arr['id']);
//         $erp_where['buildid']=array('eq',$params['buildid']);
//         $erp_where['type']=array('eq','ERP_YX');
//         $erp_where['_logic']='and';
//         $erp_integral_arr=$integral_db->where($erp_where)->find();

// 				$integral_arr=$integral_db->where(array('admin_id'=>$this->admin_arr['id']))->find();
        if(empty($integral_arr) && empty($erp_integral_arr)){
            $msg=array('code'=>307,'data'=>'暂无活动，敬请期待...');
        }else{
// 					$url=http("http://182.92.31.114/rest/act/status/".$integral_arr['activity'],array());
// 					if($url==1){
            if(!empty($integral_arr)){
                $act_arr=explode(',', $integral_arr['activity']);
                if(count($act_arr)>1){
                    $i=0;
                    foreach($act_arr as $k=>$v){
                        $url="http://101.201.175.219/promo/prize/ka/prize/list/".$v;//活动下所有券
                        $return=json_decode(http($url,array()),true);//处理返回结果
                        $res[$i]=$return['data'];
                        
                        foreach($res[$i] as $k=>$val){
                            $res[$i][$k]['activity']=$v;
                            $res[$i][$k]['activity_type']='ZHT_YX';
                        }
                        $i++;
                    }
                    foreach($res as $key=>$l){
                        foreach($l as $k=>$v){
                            $score_arr[]=$v;
                        }
                    }
                }else{
                    $url="http://101.201.175.219/promo/prize/ka/prize/list/".$integral_arr['activity'];//活动下所有券
                    $return=json_decode(http($url,array()),true);//处理返回结果
                    $score_arr=$return['data'];
                    foreach($score_arr as $key=>$val){
                        $score_arr[$key]['activity']=$integral_arr['activity'];
                        $score_arr[$key]['activity_type']='ZHT_YX';
                    }
                }
            }

            if(!empty($erp_integral_arr)){
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_list';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['activity']=$erp_integral_arr['activity'];
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                if($erp_arr['code']==200){
                    foreach($erp_arr['data'] as $k=>$v){
                        $erp_arr['data'][$k]['activity']=$erp_integral_arr['activity'];
                        $erp_arr['data'][$k]['activity_type']='ERP_YX';
                    }
                }else{
                    $erp_arr=array();
                }
            }

            if(empty($erp_arr) && !empty($score_arr)){
                $arr=$score_arr;
            }else if(!empty($erp_arr) && empty($score_arr)){
                $arr=$erp_arr['data'];
            }else if(!empty($erp_arr) && !empty($score_arr)){
                $arr=array_merge($erp_arr['data'],$score_arr);
            }else{
                $arr=array();
            }
            //处理数据，返回实用数据
            foreach($arr as $k=>$v){
                $api_arr[$v['id']]['main']=$v['main_info'];
                $api_arr[$v['id']]['extend']=$v['extend_info']?$v['extend_info']:$v['main_info'];
                $api_arr[$v['id']]['imgUrl']=$v['image_url'];
                $api_arr[$v['id']]['num']=$v['num'];
                $api_arr[$v['id']]['issue']=$v['issue'];
                $api_arr[$v['id']]['startTime']=$v['start_time'];
                $api_arr[$v['id']]['endTime']=$v['end_time'];
                $api_arr[$v['id']]['pid']=$v['id'];
                $api_arr[$v['id']]['status']=$v['status'];
                $api_arr[$v['id']]['activity']=$v['activity'];
                $api_arr[$v['id']]['activity_type']=$v['activity_type'];
                $api_pid[]=$v['id'];
            }
            $integral_pro_db=M('property','integral_');
            if($type_id){
                $where_integral['type_id']=array('eq',$type_id);
            }
            if($params['buildid']){
                $where_integral['buildid']=array('eq',$params['buildid']);
            }
            $where_integral['admin_id']=array('eq',$this->admin_arr['id']);
            $where_integral['is_status']=array('eq',2);
            $where_integral['_logic']='and';
// 						'type_id='.$type_id.' and admin_id='.$this->admin_arr['id'].' and is_status=2'
            $pro_arr=$integral_pro_db->where($where_integral)->order('des')->select();
            foreach($pro_arr as $k=>$v){
                if($v['discount']==2){
                    if($code_arr){
                        $integral=json_decode($v['integral'],true);
                        if($integral[$code_arr['name']]){
                            $v['integral']=$integral[$code_arr['name']];
                        }else{
                            $v['integral']=null;
                        }
                    }else{
                        $v['integral']=json_decode($v['integral'],true);
                    }
                }

                if(in_array($v['pid'],$api_pid) && $v['integral']>=0){
                    $res_api[]=array_merge($api_arr[$v['pid']],$v);
                }
            }
            $msg=array('code'=>200,'data'=>$res_api);
// 					}else{
// 						$msg=array('code'=>307);
// 					}
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 查询所有分类
     * @param $key_admin
     * @return mixed
     */
    public function type_api(){
//         $integral_type_db=M('type','integral_');
//         $integral_type_arr=$integral_type_db->where(array('admin_id'=>$this->admin_arr['id']))->select();
        $activityTypeService = ServiceLocator::getActivityTypeService();
        $integral_type_arr = $activityTypeService->getAll($this->admin_arr['id'], 0);
        if(!is_array($integral_type_arr)){
            $msg=array('code'=>102,'msg'=>'暂无数据');
        }else{
            $msg=array('code'=>200,'data'=>$integral_type_arr);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 查询某个奖品的详细信息
     * @param $key_admin $pid
     * @return mixed
     */
    public function curl_api_once(){
        $act_id=I('pid');//券ID
        if(empty($act_id)){
            $msg=array('code'=>1030);
        }else{
            $params['buildid']=I('buildid')?I('buildid'):"";

            $userucid=$this->userucid;

            if($userucid){
                $user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$userucid);
                $use_db=M('member_code','total_');
                if($user_arr!=2000){
                    $code_arr=$use_db->where("code='".$user_arr['level']."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
                }else{
                    $level='default';
                    $code_arr=$use_db->where("code='".$level."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
                }
            }

            $status=I('status')?I('status'):'ZHT_YX';
            $activity=I('activity');
            if($status == 'ZHT_YX'){
                $url="http://182.92.31.114/rest/act/level/".$act_id;
                $once_arr=json_decode(http($url,array()),true);
                $once_arr['start_time']=$once_arr['startTime'];
                $once_arr['end_time']=$once_arr['endTime'];
            }else if($status == 'ERP_YX'){
                if($activity == ''){
                    $msg=array('code'=>1030);
                    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
                }
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_list';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['activity']=$activity;
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                $once_arr='';
                foreach($erp_arr['data'] as $k=>$v){
                    if($v['id'] == $act_id){
                        $once_arr=$v;
                        $once_arr['imgUrl']=$v['image_url'];
                        $once_arr['pid']=$v['id'];
                    }
                }
            }

            if(!is_array($once_arr)){
                $msg=array('code'=>317);
            }else{
                $once_arr_db=M('property','integral_');
                $where['pid']=$once_arr['pid'];
                $where['admin_id']=$this->admin_arr['id'];
                if($params['buildid'] != ''){
                    $where['buildid'] =$params['buildid'];
                    $where['_logic']='and';
                    //"pid='".$once_arr['pid']."' and admin_id=".$this->admin_arr['id']." and buildid=".$params['buildid']
                    $res=$once_arr_db->where($where)->find();
                }else{
                    $res=$once_arr_db->where("pid='".$once_arr['pid']."' and admin_id=".$this->admin_arr['id'])->find();
                }
                $data['main']=$once_arr['main'];
                $data['extend']=$once_arr['extend'];
                $data['imgUrl']=$once_arr['imgUrl'];
                $data['num']=$once_arr['num'];
                $data['startTime']=$once_arr['start_time'];
                $data['endTime']=$once_arr['end_time'];
                $data['issue']=$once_arr['issue'];
                $data['desc']=$once_arr['desc'];
                if($res['discount']==2){

                    if($code_arr){
                        $integral=json_decode($res['integral'],true);
                        if($integral[$code_arr['name']]){
                            $res['integral']=$integral[$code_arr['name']];
                        }else{
                            $res['integral']=null;
                        }
                    }else{
                        $res['integral']=json_decode($res['integral'],true);
                    }
                }
                $data=array_merge($data,$res);
                $msg=array('code'=>200,'data'=>$data);
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询用户积分
     * @param $key_admin $openid
     * @return mixed
     */
    public function integral_admin(){
        //判断openid
        $openid= $this->userucid;
        if(empty($openid)){
            $msg=array('code'=>1030);
        }else{
            //判断用户是否登录
            $user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$openid);
            if($user_arr==2000){
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }else{
                $url3=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//查询会员信息接口
                $sigs=sign(array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign_key'=>$this->admin_arr['signkey']));
                $url3_arr=http($url3,array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign'=>$sigs));
                //返回会员信息
                $arr=json_decode($url3_arr,true);
                if($arr['code']!=200){
                    $msg=array('code'=>3000,'data'=>'没有用户信息');
                }else{
                    $msg=array('code'=>200,'data'=>$arr['data']);
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 扣除用户积分
     * @param $key_admin $pid $main $openid
     * @return mixed
     */
    public function integral_delete(){
        $pid=I('pid');
        $main=I('main');
        $openid=$this->userucid;
        if(empty($pid) || empty($main) || empty($openid)){
            $msg=array('code'=>1030);
        }else{
            $params['buildid']=I('buildid')?I('buildid'):"";
            $status=I('status')?I('status'):'ZHT_YX';
            $activity_id=I('activity');

            $user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$openid);
            //判断用户是否存在
            if($user_arr==2000){
                echo returnjson(array('code'=>2000),$this->returnstyle,$this->callback);exit();
            }
            //判断奖品数据是否纯在
            $once_arr_db=M('property','integral_');
            if($params['buildid'] != ''){
                $once_arr=$once_arr_db->where("pid='".$pid."' and admin_id=".$this->admin_arr['id']." and buildid=".$params['buildid'])->find();//查询奖品数据
            }else{
                $once_arr=$once_arr_db->where("pid='".$pid."' and admin_id=".$this->admin_arr['id'])->find();//查询奖品数据
            }
            if(empty($once_arr)){
                echo returnjson(array('code'=>307),$this->returnstyle,$this->callback);exit();
            }


            if($once_arr['discount'] == 2){
                $use_db=M('member_code','total_');

                if($user_arr['level']){
                    $code_arr=$use_db->where("code='".$user_arr['level']."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
                    if($code_arr){
                        $once_arr['integral']=json_decode($once_arr['integral'],true);
                        if($once_arr['integral'][$code_arr['name']] == ''){
                            $msg['code']=1503;
                            echo returnjson($msg,$this->returnstyle,$this->callback);die;
                        }
                        
                        $once_arr['integral']=$once_arr['integral'][$code_arr['name']];
                    }else{
                        $msg['code']=1503;
                        echo returnjson($msg,$this->returnstyle,$this->callback);die;
                    }

                }else{
                    $msg['code']=1503;
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
            }

            if($once_arr['vip_area'] == 2 ){
                if($code_arr['name'] != '黑钻卡'){
                    $msg['code']=15;
                    $msg['msg']="卡级别不足!";
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }
            }


            //判断是否开启线下兑换
            $default_db=M('default',$this->admin_arr['pre_table']);
            $default_arr=$default_db->where(array('customer_name'=>array('eq','integral_status')))->find();
            if($default_arr['function_name']==1){
                $par['default_return_description']="请到".$default_arr['description']."兑换";
                writeOperationLog($par,'zhanghang');
                $msg['code']=1033;
                $msg['data']="请到".$default_arr['description']."兑换";
                echo returnjson($msg,$this->returnstyle,$this->callback);die;
            }

            //判断用户积分是否充足
            $url3=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//调用会员信息接口
            $sigs=sign(array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign_key'=>$this->admin_arr['signkey']));
            $url3_arr=http($url3,array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign'=>$sigs));
            $arr=json_decode($url3_arr,true);
            //print_R($arr);die;
            if($arr['data']['score']<(int)$once_arr['integral']){
                echo returnjson(array('code'=>319),$this->returnstyle,$this->callback);die;
            }

            //扣除用户积分积分
            if($once_arr['integral']==0){
                $res['code']=200;
            }else{
                $res=$this->del_integral($this->key_admin,$this->admin_arr['signkey'],$user_arr['cardno'],$once_arr['integral'],$main);
            }

            if($res['code']!=200){
                $msg=array('code'=>$res['code']);
                echo returnjson($msg,$this->returnstyle,$this->callback);die;
            }

            if($activity_id != '' && $activity_id != null){
                $activity_arr['activity']=$activity_id;
            }else{
                //获取活动ID
                $activityService = ServiceLocator::getActivityService();
                $activity_arr = $activityService->getOnce($this->admin_arr['id'], $status, $params['buildid'], 0);
                
//                 $activity_db=M('activity','integral_');
//                 $activity_where['admin_id']=array('eq',$this->admin_arr['id']);
//                 $activity_where['status']=array('eq',$status);
//                 $activity_where['buildid']=$params['buildid'];
//                 $activity_where['_logic']='and';
//                 $activity_arr=$activity_db->where($activity_where)->find();
                if(empty($activity_arr)){
                    $msg['code']=307;
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }

                $activity_res=explode(',', $activity_arr['activity']);
                $activity_arr['activity']=$activity_res[0];
            }

            if($status=='ZHT_YX'){
                $url2='http://101.201.176.54/rest/act/prize/'.$activity_arr['activity'].'/'.$pid.'/'.$openid;//领券接口
                $act_arr=http($url2,array());
                $act_res=json_decode($act_arr,true);
                $par['integral_delete_return']=$act_res;
                writeOperationLog($par,'zhanghang');
                if($act_res['code']==0 && is_json($act_arr)){
                    //领券成功写入日志
                    $this->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'F',$this->admin_arr['pre_table'],'',$openid,$pid,$act_res['qr']);
                    $msg=array('code'=>200,'data'=>'领奖成功');
                    echo returnjson($msg,$this->returnstyle,$this->callback);die;
                }

                if(!is_json($act_arr)){
                    $act_res['message'] = '系统错误';
                }
                
                //判断是否为0积分
                if($once_arr['integral']==0){
                    $return_integral['code']=200;
                }else{
                    //领券失败返回积分
                    $return_integral=$this->add_integral($this->key_admin,$this->admin_arr['signkey'],$arr['data']['user'],$once_arr['integral'],$user_arr['cardno'],$main,$res['data']['scorecode']);
                }

                //记录日志
                $return_log_id=$this->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'M',$this->admin_arr['pre_table'],'',$openid,$pid,'');
                if($return_integral['code']==200){
                    //恢复积分成功修改日志状态
                    $this->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'A',$this->admin_arr['pre_table'],$return_log_id);
                    $msg=array('code'=>1082,'msg'=>$act_res['message']);
                }else{
                    $msg=array('code'=>304);
                }
                echo returnjson($msg,$this->returnstyle,$this->callback);die;
            }

            if($status=='ERP_YX'){
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_exchange';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['cardno']=$user_arr['cardno'];
                $erp_params['pid']=$pid;
                $erp_params['activity']=$activity_arr['activity'];
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                if($erp_arr['code']==200){
                    $msg['code']=200;
                    $this->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'F', $this->admin_arr['pre_table'],'',$openid,$pid);
                }else{
                    $this->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'M', $this->admin_arr['pre_table'],'',$openid,$pid);
                    $msg=$erp_arr;
                }
                echo returnjson($msg,$this->returnstyle,$this->callback);die;
            }

        }
    }

// 	public function integral_delete(){
// 		$pid=I('pid');
// 		$main=I('main');
// 		$openid=I('openid');
// 		if(empty($pid) || empty($main) || empty($openid)){
// 			$msg=array('code'=>1030);
// 		}else{
// 		        $status=I('status')?I('status'):'ZHT_YX';
// 				//查询用户
// 				$user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$openid);
// 				if($user_arr==2000){
// 					echo returnjson(array('code'=>2000,'data'=>$user_arr),$this->returnstyle,$this->callback);exit();
// 				}else{
// 					//判断奖品数据是否纯在
// 					$once_arr_db=M('property','integral_');
// 					$once_arr=$once_arr_db->where('pid='.$pid.' and admin_id='.$this->admin_arr['id'])->find();//查询奖品数据
// 					if($once_arr){
// 					    $default_db=M('default',$this->admin_arr['pre_table']);
// 					    $default_arr=$default_db->where(array('customer_name'=>array('eq','integral_status')))->find();
// 					    if($default_arr['function_name']==1){
// 					        $par['default_return_description']="请到".$default_arr['description']."兑换";
// 					        writeOperationLog($par,'zhanghang');
// 					        $msg['code']=1033;
// 					        $msg['data']="请到".$default_arr['description']."兑换";
// 					        echo returnjson($msg,$this->returnstyle,$this->callback);die;
// 					    }
// 						//判断用户积分是否充足
// 						$url3=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//调用会员信息接口
// 						$sigs=sign(array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign_key'=>$this->admin_arr['signkey']));
// 						$url3_arr=http($url3,array('key_admin'=>$this->key_admin,'card'=>$user_arr['cardno'],'sign'=>$sigs));
// 						$arr=json_decode($url3_arr,true);
// 						//print_R($arr);die;
// 						if($arr['data']['score']<(int)$once_arr['integral']){
// 							$msg=array('code'=>319);
// 						}else{
// 							//扣除用户积分积分
// 							if($once_arr['integral']==0){
// 							    $res['code']=200;
// 							}else{
//                                 $res=$this->del_integral($this->key_admin,$this->admin_arr['signkey'],$user_arr['cardno'],$once_arr['integral'],$main);
// 							}

// 							if($res['code']==200){
// 								$activity_db=M('activity','integral_');
// 								$activity_where['admin_id']=array('eq',$this->admin_arr['id']);
// 								$activity_where['status']=array('eq',$status);
// 								$activity_where['_logic']='and';
// 								$activity_arr=$activity_db->where($activity_where)->find();
// 								if(!empty($activity_arr) && $status=='ZHT_YX'){
// 									$url2='http://101.201.176.54/rest/act/prize/'.$activity_arr['activity'].'/'.$pid.'/'.$openid;//领券接口
// 									$act_arr=http($url2,array());
// 									$act_res=json_decode($act_arr,true);
// 									$par['integral_delete_return']=$act_res;
// 									writeOperationLog($par,'zhanghang');
// 									if($act_res['code']==0){
// 										//领券成功写入日志
// 										$this->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'F',$this->admin_arr['pre_table'],'',$openid,$pid);
// 										$msg=array('code'=>200,'data'=>'领奖成功');
// 									}else{
// 									    if($once_arr['integral']==0){
// 									        $return_integral['code']=200;
// 									    }else{
// 									        //领券失败返回积分
// 									        $return_integral=$this->add_integral($this->key_admin,$this->admin_arr['signkey'],$arr['data']['user'],$once_arr['integral'],$user_arr['cardno'],$main,$res['data']['scorecode']);
// 									    }
// 										//写入日志
// 										$return_log_id=$this->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'M',$this->admin_arr['pre_table'],'',$openid,$pid);
// 										if($return_integral['code']==200){
// 											//恢复积分成功修改日志状态
// 											$this->log_integral($activity_arr['activity'],$user_arr['cardno'],$once_arr['integral'],$main,'A',$this->admin_arr['pre_table'],$return_log_id);
// 											$msg=array('code'=>1082,'msg'=>$act_res['message']);
// 										}else{
// 											$msg=array('code'=>304);
// 										}
// 									}
// 								}else if(!empty($activity_arr) && $status=='ERP_YX'){
// 								    $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_exchange';
// 								    $erp_params['key_admin']=$this->key_admin;
// 								    $erp_params['cardno']=$user_arr['cardno'];
// 								    $erp_params['pid']=$pid;
// 								    $erp_params['activity']=$activity_arr['activity'];
// 								    $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
// 								    if($erp_arr['code']==200){
// 								        $msg['code'];
// 								        $this->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'F', $this->admin_arr['pre_table']);
// 								    }else{
// 								        $this->log_integral($activity_arr['activity'], $user_arr['cardno'], $once_arr['integral'], $main, 'M', $this->admin_arr['pre_table'],'',$openid,$pid);
// 								        $msg=$erp_arr;
// 								    }
// 							    }else{
// 									$msg=array('code'=>307);
// 								}
// 							}else{
// 							    $msg=array('code'=>$res['code']);
// 							}
// 						}
// 					}else{
// 						$msg=array('code'=>307);
// 					}
// 				}
// 		}
// 		$par['integral_delete_return']='pid:'.$pid.',main:'.$main.',openid:'.$openid;
// 		writeOperationLog($par,'zhanghang');
// 		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
// 	}


    //恢复积分
    public function add_integral($key_admins,$admin_arrs,$arr,$scorenumber,$cardno,$main,$scorecode){
        $url4=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';//恢复积分接口
        $res['data']['key_admin']=$key_admins;
        $res['data']['sign_key']=$admin_arrs;
        $res['data']['membername']=$arr;
        $res['data']['scoreno']=$scorenumber;
        $res['data']['cardno']=$cardno;
        $res['data']['scorecode']=$scorecode?$scorecode:date('Y-m-d');
        $res['data']['why']='兑换'.$main;
        $res['data']['sign']=sign($res['data']);
        $res['data']['why']='兑换'.html_entity_decode($main);
        unset($res['data']['sign_key']);
        $add_integral_arr=http($url4,$res['data']);
        $return_integral=json_decode($add_integral_arr,true);
        return $return_integral;
    }


    //扣除积分
    public function del_integral($key_admins,$admin_arrs,$cardno,$once_arr,$main){
        $param['key_admin']=$key_admins;
        $param['sign_key']=$admin_arrs;
        $param['cardno']=$cardno;
        $param['scoreno']=$once_arr;
        $param['why']='兑换'.$main;
        $param['sign']=sign($param);
        $param['why']='兑换'.html_entity_decode($main);
        unset($param['sign_key']);
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/cutScore';//扣除积分接口
        $curl_res=http($url,$param,'post');
        $res=json_decode($curl_res,true);
        return $res;
    }



    //添加日志
    public function log_integral($activity_id,$cardno,$integral,$main,$re,$pre_table,$id=null,$openid='',$pid='',$code=''){
        $log_integral_db=M('integral_log',$pre_table);
        $data['cardno']=$cardno;
        $data['integral']=$integral;
        $data['description']="兑换".$main;
        $data['activity_id']=$activity_id;
        if($re=='F'){
            $data['starttime']=date('Y-m-d H:i:s');
            $data['status']=1;
            $data['openid']=$openid;
            $data['pid']=$pid;
            $data['prize_name']=$main;
            $data['code']=$code;
            $log_integral_db->add($data);
        }else if($re=='M'){
            $data['starttime']=date('Y-m-d H:i:s');
            $data['status']=2;
            $data['prize_name']=$main;
            $data['openid']=$openid;
            $data['pid']=$pid;
            $res=$log_integral_db->add($data);
            return $res;
        }else if($re=='A'){
            $upda['status']=3;
            $map['id']=$id;
            $upda['prize_name']=$main;
            $log_integral_db->where($map)->save($upda);
        }
    }

    /**
     * 判断活动id状态
     * @param $key_admin $activity_id
     * @return mixed
     */
    public function activity_id_status(){
        $params['activity_id']=I('activity_id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $url="http://182.92.31.114/rest/act/status/".$params['activity_id'];//活动下所有券
            $arr=json_decode(http($url),true);//处理返回结果
            $msg['code']=200;
            $msg['data']=$arr;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    //查看用户是否登录
    public function getUserCardByOpenId($prefix, $openid) {

        $user = M('mem', $prefix);
        if($this->from=="wechat"){
            $key = "openid";
        }elseif($this->from=="alipay"){
            $key = "userid";
        }
        $re = $user->where(array($key => $openid))->find();

        //return $re['cookie'].','.$_COOKIE[$prefix.'ck'];exit();

        if (!$re) {
            return '2000';exit();
        } else {
            //if ($re['cookie'] != $_COOKIE[$prefix.'ck']) {
            //return '2000';exit();
            //}
            $data['mobile'] = $re['mobile'];
            $data['key_admin'] = $this->key_admin;
            $data['sign_key'] = $this->admin_arr['signkey'];
            $data['openid'] = $openid;
            $data['sign'] = sign($data);

            unset($data['sign_key']);
            $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobymobile';
            $curl_re = http($url, $data, 'post');

            $return_arr=json_decode($curl_re, true);
            if($return_arr['code']==200){
                $return_arr['data']['level']=$return_arr['data']['cardtype'];
                return $return_arr['data'];exit();
            }else{
                return $re;exit();
            }
        }


    }

    //超哥用
    public function sum(){
        $par['day']=I('day');
        $db1=M('score_type',$this->admin_arr['pre_table']);
        $start_time=$par['day']." 00:00:00";
        $end_time=$par['day']." 23:59:59";
        $map=array('createtime'=>array('between',$start_time.','.$end_time));
        $map['status']=array('EQ',2);
        $map['_logic']='and';
        $arr1=$db1->where($map)->field('money,score_number')->select();

        $db2=M('integral_log',$this->admin_arr['pre_table']);
        $map1=array('starttime'=>array('between',$start_time.','.$end_time));
        $map1['status']=array('EQ',1);
        $map1['_logic']='and';
        $arr2=$db2->where($map1)->field('integral')->select();

        $sum=0;
        $money=0;
        $sum1=0;

        foreach($arr1 as $k=>$v){
            $sum=$sum+$v['score_number'];
            $money=$money+$v['money'];
        }

        foreach($arr2 as $k=>$v){
            $sum1=$sum1+$v['integral'];
        }

        echo "消费金额：".$money."<br/>"."添加总积分：".$sum."<br/>"."兑换总积分：".$sum1;


    }


    /**
     * 获取banner接口
     */
    public function banner_list(){
        $params['buildid']=I('buildid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner','integral_');
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['buildid']=array('eq',$params['buildid']);
            $map['status']=array('eq',1);
            $map['_logic']='and';
            $res=$banner_db->where($map)->order('sort desc')->limit(5)->select();
            if($res){
                $msg['code']=200;
                $msg['data']=$res;
            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 获取banner接口1
     */
    public function banner_list_new(){
        $params['buildid']=I('buildid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $banner_db=M('banner_new','integral_');
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['buildid']=array('eq',$params['buildid']);
            $map['status']=array('eq',1);
            $map['_logic']='and';
            $res=$banner_db->where($map)->order('sort desc')->limit(5)->select();
            if($res){
                $msg['code']=200;
                $msg['data']=$res;
            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 获取建筑物ID
     */
    public function builid_list(){
        $where['adminid']=array('eq',$this->admin_arr['id']);
        $where['is_del']=array('eq',2);
        $where['_logic']='and';
        $db=M('buildid','total_');
        $arr=$db->where($where)->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 获取专区所有券
     * @param $key_admin $type_id
     * @return mixed
     */
    public function vip_area_curl(){
        $type_id=I('type_id');
        $params['buildid']=I('buildid')?I('buildid'):"";
        $params['openid']=I('openid');

        if($params['openid']){
            $user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$params['openid']);
            $use_db=M('member_code','total_');
            if($user_arr != 2000){
                $code_arr=$use_db->where("code='".$user_arr['level']."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
            }else{
                $level='default';
                $code_arr=$use_db->where("code='".$level."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();
            }
        }

//         $type_db=M('type','integral_');
//         $type_arr=$type_db->where(array('id'=>$type_id))->find();
//         if(!$type_arr){
//             $msg['code']=1081;
//             returnjson($msg,$this->returnstyle,$this->callback);exit();
//         }
        $integral_db=M('activity','integral_');//查询活动ID

        $zht_where['admin_id']=array('eq',$this->admin_arr['id']);
        $zht_where['buildid']=array('eq',$params['buildid']);
        $zht_where['type']=array('eq','ZHT_YX');
        $zht_where['_logic']='and';
        $integral_arr=$integral_db->where($zht_where)->find();

        $erp_where['admin_id']=array('eq',$this->admin_arr['id']);
        $erp_where['buildid']=array('eq',$params['buildid']);
        $erp_where['type']=array('eq','ERP_YX');
        $erp_where['_logic']='and';
        $erp_integral_arr=$integral_db->where($erp_where)->find();

        // 				$integral_arr=$integral_db->where(array('admin_id'=>$this->admin_arr['id']))->find();
        if(empty($integral_arr) && empty($erp_integral_arr)){
            $msg=array('code'=>307,'data'=>'暂无活动，敬请期待...');
        }else{
            // 					$url=http("http://182.92.31.114/rest/act/status/".$integral_arr['activity'],array());
            // 					if($url==1){
            if(!empty($integral_arr)){
                $act_arr=explode(',', $integral_arr['activity']);
                if(count($act_arr)>1){
                    $i=0;
                    foreach($act_arr as $k=>$v){
                        $url="http://101.201.175.219/promo/prize/ka/prize/list/".$v;//活动下所有券
                        $return=json_decode(http($url,array()),true);//处理返回结果
                        $res[$i]=$return['data'];
                        if($res[$i]!= ''){
                            if($i==1){
                                $score_arr=array_merge($res[$i],$res[0]);
                            }else if($i>1){
                                $score_arr=array_merge($res[$i],$score_arr);
                            }
                            foreach($score_arr as $key=>$val){
                                $score_arr[$key]['activity']=$v;
                                $score_arr[$key]['activity_type']='ZHT_YX';
                            }
                            $i++;
                        }
                    }
                }else{
                    $url="http://101.201.175.219/promo/prize/ka/prize/list/".$integral_arr['activity'];//活动下所有券
                    $return=json_decode(http($url,array()),true);//处理返回结果
                    $score_arr=$return['data'];
                    foreach($score_arr as $key=>$val){
                        $score_arr[$key]['activity']=$integral_arr['activity'];
                        $score_arr[$key]['activity_type']='ZHT_YX';
                    }
                }
            }

            if(!empty($erp_integral_arr)){
                $url=C('DOMAIN') . '/ErpService/Erpoutput/prize_list';
                $erp_params['key_admin']=$this->key_admin;
                $erp_params['activity']=$erp_integral_arr['activity'];
                $erp_params['sign_key']=$this->admin_arr['signkey'];
                $erp_params['sign']=sign($erp_params);
                unset($erp_params['sign_key']);
                $erp_arr=json_decode(http($url,$erp_params),true);//处理返回结果
                if($erp_arr['code']==200){
                    foreach($erp_arr['data'] as $k=>$v){
                        $erp_arr['data'][$k]['activity']=$erp_integral_arr['activity'];
                        $erp_arr['data'][$k]['activity_type']='ERP_YX';
                    }
                }else{
                    $erp_arr=array();
                }
            }

            if(empty($erp_arr) && !empty($score_arr)){
                $arr=$score_arr;
            }else if(!empty($erp_arr) && empty($score_arr)){
                $arr=$erp_arr['data'];
            }else if(!empty($erp_arr) && !empty($score_arr)){
                $arr=array_merge($erp_arr['data'],$score_arr);
            }else{
                $arr=array();
            }
            //处理数据，返回实用数据
            foreach($arr as $k=>$v){
                $api_arr[$v['id']]['main']=$v['main_info'];
                $api_arr[$v['id']]['extend']=$v['extend_info']?$v['extend_info']:$v['main_info'];
                $api_arr[$v['id']]['imgUrl']=$v['image_url'];
                $api_arr[$v['id']]['num']=$v['num'];
                $api_arr[$v['id']]['issue']=$v['issue'];
                $api_arr[$v['id']]['startTime']=$v['start_time'];
                $api_arr[$v['id']]['endTime']=$v['end_time'];
                $api_arr[$v['id']]['pid']=$v['id'];
                $api_arr[$v['id']]['status']=$v['status'];
                $api_arr[$v['id']]['activity']=$v['activity'];
                $api_arr[$v['id']]['activity_type']=$v['activity_type'];
                $api_pid[]=$v['id'];
            }
            $integral_pro_db=M('property','integral_');
            //'type_id='.$type_id.' and admin_id='.$this->admin_arr['id'].' and is_status=2 and vip_area=2'
            if($type_id!=''){
                $map['type_id']=array('eq',$type_id);
            }
            $map['admin_id']=array('eq',$this->admin_arr['id']);
            $map['is_status']=array('eq',2);
            $map['vip_area']=array('eq',2);
            $map['_logic']='and';
            $pro_arr=$integral_pro_db->where($map)->order('des')->select();
            foreach($pro_arr as $k=>$v){
                if($v['discount']==2){
                    if($code_arr){
                        $integral=json_decode($v['integral'],true);
                        if($integral[$code_arr['name']]){
                            $v['integral']=$integral[$code_arr['name']];
                        }else{
                            $v['integral']=null;
                        }
                    }else{
                        $v['integral']=json_decode($v['integral'],true);
                    }
                }

                if(in_array($v['pid'],$api_pid) && $v['integral']>=0){
                    $res_api[]=array_merge($api_arr[$v['pid']],$v);
                }
            }
            $msg=array('code'=>200,'data'=>$res_api);
            // 					}else{
            // 						$msg=array('code'=>307);
            // 					}
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     *　获取用户卡等级
     */
    public function member_cardtype(){
        $params['openid']=I('openid');
        $user_arr=$this->getUserCardByOpenId($this->admin_arr['pre_table'],$params['openid']);

        if($user_arr != 2000){
            $msg['code']=200;

            $use_db=M('member_code','total_');
            $code_arr=$use_db->where("code='".$user_arr['level']."' and admin_id=".$this->admin_arr['id'])->field('name,id')->find();

            $msg['data']['level']=$user_arr['level'];
            $msg['data']['name']=$code_arr['name'];
        }else{
            $msg['code']=$user_arr;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    public function taiguli(){
        
        $where['status']=array('eq',2);
        $where['activity_id']=array('eq',18872);
        
        $db=M('integral_log',$this->admin_arr['pre_table']);
//         BAD FARMERS &amp;amp; OUR BAKERY “脏出活力，脏的自由”活动
        $arr=$db->where($where)->field('id,cardno,activity_id,prize_name,integral')->select();
        
        foreach($arr as $k=>$v){
            $url3=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//调用会员信息接口
            $sigs=sign(array('key_admin'=>$this->key_admin,'card'=>$v['cardno'],'sign_key'=>$this->admin_arr['signkey']));
            $url3_arr=http($url3,array('key_admin'=>$this->key_admin,'card'=>$v['cardno'],'sign'=>$sigs));
            $arronce=json_decode($url3_arr,true);
            
            if($arronce){
                
                $res=$this->add_integral($this->key_admin,$this->admin_arr['signkey'],$arronce['data']['user'],$v['integral'],$v['cardno'],$v['prize_name']);
                
                if($res['code']==200){
                    $save=$db->where(array('id'=>$v['id']))->save(array('status'=>3));
                    
                    if($save){
                        echo "数据库处理失败".$v['cardno']." <br>";
                    }
                    
                }else{
                    echo "返回积分失败".$res['msg'].$v['cardno']." <br>";
                    
                }
            }else{
                echo "没有用户信息".$v['cardno']." <br>";
            }
        }
                
    }
    
    
    
    
}
