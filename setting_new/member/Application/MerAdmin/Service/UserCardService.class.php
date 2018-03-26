<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use PublicApi\Service;
use PublicApi\Service\CouponService;
class UserCardService{
    
    /**
     * UserCard对象
     *
     * @var MerAdmin\Model\UserCardModel
     */
    public $userCard_model;//用户卡包model
    
    public function __construct()
    {
        
    }
    
    //查看用户是否登录
    public function getUserCardByOpenId($admin_arr,$key_admin,$prefix, $openid, $unionid=''){
        $user = M('mem', $prefix);
        $re = $user->where(array('openid' => $openid))->find();
        
        if (!$re) 
        {
            return '2000';
        } 
        else 
        {
            $data['card'] = $re['cardno'];
            $data['key_admin'] = $this->key_admin?$this->key_admin:$key_admin;
            $data['sign_key'] = $admin_arr['signkey'];
            $data['openid'] = $openid;
            $data['unionid'] = $unionid;
            $data['sign'] = sign($data);
    
            unset($data['sign_key']);
            $url = C('DOMAIN') . '/CrmService/OutputApi/Index/GetUserinfoByCard';
            $curl_re = http($url, $data, 'post');
    
            $return_arr = json_decode($curl_re, true);
            if($return_arr['code'] == 200)
            {
                $return_arr['data']['level'] = $return_arr['data']['cardtype'];
                return $return_arr['data'];
            }
            else
            {
                return $re;
            }
        }
    }
    
    //查看用户积分
    public function getUserCardInfo($key_admin, $cardno, $signkey,$unionid=''){
        $url = C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';//调用会员信息接口
        $sigs = sign(array('key_admin' => $key_admin, 'card' => $cardno, 'sign_key' => $signkey,'unionid'=>$unionid));
        $url3_arr = http($url, array('key_admin' => $key_admin, 'card' => $cardno,'unionid'=>$unionid, 'sign' => $sigs));
        $arr = json_decode($url3_arr, true);
        
        return $arr;
    }
    
    //查看用户是否登录
    public function getMemberCode($admin_arr,$key_admin,$openid, $pre_table, $adminId,$unionid=''){ 
        if($openid)
        {
            $user_arr = $this->getUserCardByOpenId($admin_arr,$key_admin,$pre_table, $openid,$unionid);
            $use_db = M('member_code','total_');
            if($user_arr != 2000)
            {
                $code_arr = $use_db->where("code='".$user_arr['level']."' and admin_id=".$adminId)->field('name,id')->find();
            }
            else
            {
                $level = 'default';
                $code_arr = $use_db->where("code='".$level."' and admin_id=".$adminId)->field('name,id')->find();
            }
        }
        else
        {
            $code_arr = array();
        }
        
        return $code_arr;
    }
    
    //领券接口
    public function prize_integral($activity, $pid, $openid,$coupon_default,$couponID){
        
        if($coupon_default == 2){
//             $coupon_return = CouponService::giveCouponCheck($pid,$activity,$openid,$couponID,1,'','');
            $coupon_return = CouponService::giveCoupon($pid,$openid,$couponID,1,'','');
            if($coupon_return['code']==200){
                $act_res['code'] = 0;
                $act_res['qr'] = $coupon_return['data']['qrCode'];//需要更改返回的编号。qr
            }else{
                $act_res['code'] = 1;
                $act_res['message'] = $coupon_return['msg'];
            }
        }else{
            $url2    = 'http://101.201.176.54/rest/act/prize/'.$activity.'/'.$pid.'/'.$openid;
            $act_arr = http($url2,array());
            $act_res = json_decode($act_arr,true);
        }
        $par['integral_return_coupon'] = $coupon_return;
        $par['integral_return_coupon_params'] = $pid.'---------'.$openid.'----------'.$coupon_default.'----------'.$couponID;
        $par['integral_delete_return'] = $act_res;
        writeOperationLog($par,'newIntegral');
        return $act_res;
    }
    
    //扣除积分
    public function del_integral($key_admins,$admin_arrs,$cardno,$once_arr,$main,$unionid=''){
        $param['key_admin']=$key_admins;
        $param['sign_key']=$admin_arrs;
        $param['cardno']=$cardno;
        $param['scoreno']=$once_arr;
        $param['why']='兑换'.$main;
        $param['unionid'] = $unionid;
        $param['sign']=sign($param);
        unset($param['sign_key']);
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/cutScore';//扣除积分接口
        $curl_res=http($url,$param);
        $res=json_decode($curl_res,true);
        return $res;
    }
    
    //退券接口
    public function coupon_return($activityId,$prizeId,$openId,$qrCode,$coupon_default,$couponID,$shop_id){
        
        if($coupon_default == 2){
            $coupon_return = CouponService::returnTicket($prizeId,$openId,$couponID,$qrCode,$shop_id);
            if($coupon_return['code']==200){
                $act_res['code'] = 0;
                $act_res['qr'] = $coupon_return['data']['qr'];//需要更改返回的编号。qr
            }else{
                $act_res['code'] = 1;
            }
        }else{
            $url="http://101.201.175.219/promo/api/ka/coupon/return?activityId=$activityId&prizeId=$prizeId&openId=$openId&qrCode=$qrCode";//退券接口
            $res = file_get_contents($url);
        }
        return $res;
    }
    
    //恢复积分
    public function add_integral($key_admins,$admin_arrs,$arr,$scorenumber,$cardno,$main,$scorecode,$unionid=''){
        $url4=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';//恢复积分接口
        $res['data']['key_admin']=$key_admins;
        $res['data']['sign_key']=$admin_arrs;
        $res['data']['membername']=$arr;
        $res['data']['scoreno']=$scorenumber;
        $res['data']['cardno']=$cardno;
        $res['data']['scorecode']=$scorecode?$scorecode:date('Y-m-d');
        $res['data']['why']='兑换'.$main;
        $res['data']['unionid'] = $unionid;
        $res['data']['sign']=sign($res['data']);
        unset($res['data']['sign_key']);
        $add_integral_arr=http($url4,$res['data']);
        $return_integral=json_decode($add_integral_arr,true);
        return $return_integral;
    }
    
    //添加日志
    public function log_integral($activity_id,$cardno,$integral,$main,$re,$pre_table,$id=null,$openid='',$pid='',$code='',$buildid=''){
        $log_integral_db=M('integral_log',$pre_table);
        $data['cardno']=$cardno;
        $data['integral']=$integral;
        $data['description']="兑换".$main;
        $data['activity_id']=$activity_id;
        $data['buildid']=$buildid;
        if($re=='F'){//支付成功积分券给成功
            $data['starttime']=date('Y-m-d H:i:s');
            $data['status']=1;
            $data['openid']=$openid;
            $data['pid']=$pid;
            $data['prize_name']=$main;
            $data['code']=$code;
            $log_integral_db->add($data);
        }else if($re=='M'){//支付成功券没成功，积分返回失败
            $data['starttime']=date('Y-m-d H:i:s');
            $data['status']=2;
            $data['prize_name']=$main;
            $data['openid']=$openid;
            $data['pid']=$pid;
            $res=$log_integral_db->add($data);
            return $res;
        }else if($re=='A'){//支付成功券没成功，积分返回成功
            $upda['status']=3;
            $map['id']=$id;
            $upda['prize_name']=$main;
            $log_integral_db->where($map)->save($upda);
        }
    }
    
    //添加日志
    public function newlog_integral($activity_id,$cardno,$integral,$main,$re,$pre_table,$openid='',$pid='',$code='',$buildid=''){
        $log_integral_db     = M('integral_log',$pre_table);
        $data['cardno']      = $cardno;
        $data['integral']    = $integral;
        $data['description'] = "兑换".$main;
        $data['activity_id'] = $activity_id;
        $data['buildid']     = $buildid;
        $data['starttime']   = date('Y-m-d H:i:s');
        $data['status']      = $re; 
        $data['openid']      = $openid;
        $data['pid']         = $pid;
        $data['prize_name']  = $main;
        $data['code']        = $code;
        $log_integral_db->add($data);
    }

}
?>