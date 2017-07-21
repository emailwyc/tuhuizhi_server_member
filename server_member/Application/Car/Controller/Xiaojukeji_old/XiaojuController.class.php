<?php
namespace Car\Controller\Xiaojukeji;

use Car\Controller\CarcommonController;
use Common\Controller\RedisController;
use Common\Controller\PublicController;
/**
 * @desc    滴滴打车接口部分
 * @author ut
 *
 */
class XiaojuController extends CarcommonController implements xiaojukeji{

    public $access_token;//授权认证，缓存半小时
    public $user_card;//用户会员卡号
    
    public $user_mobile;//用户手机号
    public $score;//用户积分数
    public $ratio_score=null;//设置的积分比率
    public $ratio_price=null;//设置的积分比率
    public $minscore;//设置的最小积分数
    
    public function __initialize(){
        parent::__initialize();
        $this->apiurl=C('XIAOJU_API_URL');
        //redis
        $nredis=new RedisController();
        $redis=$nredis->connectredis();
        //这个地方不用判断手机号，暂时先这样，以后改
        $access_token=$redis->get('access_token');
        if (empty($access_token)){
            $this->auth();
        }else {
            $this->access_token=$access_token;
        }
        
    }
    
    /**
     * @desc  城市列表
     */
    public function getcitylist(){
        $this->__initialize();
        $msg=$this->myerrorcode;
        $msg['code']=200;
        $msg['msg']=L('success');
        $msg['data']=F('city');//echo MODULE_PATH;
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    

    /**
     * @desc 根据ip定位
     */
    public function getlocation(){
        $this->__initialize();
        $ip=get_client_ip();
        $params=array('ip'=>$ip,'key'=>C('TENCENTMAPAPI.KEY'));
        $location=http('http://apis.map.qq.com/ws/location/v1/ip',$params);
        $msg=$this->myerrorcode;
        if (is_json($location)){
            $location=json_decode($location,true);
            $city=$location['result']['ad_info']['city'];
//             if(strpos($city,'市')){
//                 $city = str_replace('市','',$city);
//             }
            $didicity=F('city');//echo $city;
            if (false==$didicity){//万一呢，so～
                $this->getallcity();
                $didicity=F('city');
            }
            $key=array_search($city,array_column($didicity, 'name'));
            //dump($key);dump(array_column($didicity, 'name'));
            if (''!=$key || null != $key){
                $msg['code']=200;
                $msg['data']=$didicity[$key];
                $msg['msg']=L('success');
            }else {
                $msg['code']=102;
                $msg['msg']=L('cnfrd');
            }
        }else{
            $msg['code']=101;
            $msg['msg']=L('interface_error');
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    
    

    
    


    /**
     * 根据经纬度定位，前端传经纬度过来,lat<纬度>,lng<经度>
     */
    public function getlocationbylan(){
        $this->__initialize();
        $pa['lat']=I('lat');
        $pa['lng']=I('lng');
        $msg=$this->myerrorcode;
        if (in_array('',$pa)){
            $msg['code']=100;
            $msg['msg']=L('Incomplete_parameters');
        }else{
            $url=C('TENCENTMAPAPI.URL');
            $key=C('TENCENTMAPAPI.KEY');
            $params=array('location'=>$pa['lat'].','.$pa['lng'],'get_poi'=>0,'key'=>$key);
            $data=http($url, $params);
            if (is_json($data)){
                $dataarr=json_decode($data,true);
                if(0==$dataarr['status']){
                    $city=$dataarr['result']['address_component']['city'];
//                     if(strpos($city,'市')){
//                         $city = str_replace('市','',$city);
//                     }
                    $didicity=F('city');//echo $city;
                    if (false==$didicity){//万一呢，so～
                        $this->getallcity();
                        $didicity=F('city');
                    }
                    $keys=array_search($city,array_column($didicity, 'name'));
                    //dump($key);dump(array_column($didicity, 'name'));
                    if (''!=$keys || null != $keys){
                        $msg['code']=200;
                        $msg['data']=$didicity[$keys];
                        $msg['msg']=L('success');
                    }else {
                        $msg['code']=102;
                        $msg['msg']=L('cnfrd');
                    }
                }else {
                    $msg['code']=104;
                    $msg['msg']=L('error').'_'.$dataarr['status'];
                }
            }else{
                $msg['code']=101;
                $msg['msg']=L('interface_error');
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    

    
    
    
    
    
    
    
    ###################################################################
    
    

    
    
    /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::auth()
     */
    public function auth(){
        // TODO Auto-generated method stub
        
        $url=$this->apiurl.'/v1/Auth/authorize';
        $params['client_id']=$this->AppKey;
        $params['client_secret']=$this->AppSecret;
        $params['grant_type']='client_credentials';
        $params['phone']=$this->TEST_CALL_PHONE;
        $params['timestamp']=time();
        $sign=$this->sign($params);//获取签名
        $params['sign']=$sign;
        //$auth=$this->auth($params);
        $url=$this->apiurl.'/v1/Auth/authorize';
        $auth =http($url, $params,'POST');
        $nredis=new RedisController();
        $redis=$nredis->connectredis();
        
        $auth=json_decode($auth,true);
        //如果有access_token这个key，则保存，否则跳出
        if (array_key_exists('access_token',$auth)){
            $redis->set('access_token',$auth['access_token'],array('ex'=>1800));
            $this->access_token=$auth['access_token'];
            return $auth;
        }else{
            $msg=$this->myerrorcode;
            $msg['code']=104;
            $msg['msg']=L('error');
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        
    }

 /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::carlevel()
     */
    public function carlevel($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/common/CarLevel/getCityCar';
        $data=http($url, $params);
        return $data;
        
    }

 /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::estimate()
     */
    public function estimateprice($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/common/Estimate/price';
        $data=http($url, $params);
        return $data;
        
    }

 /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::getaddress()
     */
    public function getaddress($params){
        $url=$this->apiurl.'/v1/common/Address/getAddress';
        $getdata=http($url,$params);
        return $getdata;
        // TODO Auto-generated method stub
        
    }

 /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::orderrequest()
     */
    public function orderrequest($client_id, $access_token, $timestamp, $sign, $order_id, $rule, $type, $passenger_phone, $city, $flat, $flng, $start_name, $start_address, $tlat, $tlng, $end_name, $end_address, $clat, $clng, $departure_time, $require_level, $app_time, $map_type, $combo_id, $sms_policy, $extra_info, $callback_info)
    {
        // TODO Auto-generated method stub
        
    }

 /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::sign()
     */
    public function sign($params){
        // TODO Auto-generated method stub
        $params['sign_key'] = $this->key; 
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }
        return md5($str);
        
    }

 // TODO - Insert your code here
    
    /**
     * @desc    检测是否是会员，每一步都要执行，只有会员才可以打车
     * @param unknown $ppa url里面的参数值
     * @param $k 哪一种方式
     */
    public function checkvip($ppa,$k){
        /**
         * 1、判断是哪一种提交
         * 2、根据提交方式调用对应的api接口
         */
        $db=M('api',$this->table_pre);
        $msg=$this->myerrorcode;
        $find=$db->where(array('apt_type'=>$this->fromid))->find();
        if (null != $find ){
            //赋值
            $url=$find['api_url'];
            $params['openida']=$this->ppa;
            $method=$find['request_param_type'];
            $header=is_json($find['header'])?json_decode($find['header'],true):array();
            //平台key和商家key转换
            $params=params_to_params($params,$find['api_request']);
            //按对应的方式请求
            if ('http'==$find['request_type']){
                $public =new PublicController();
                $usercard=$public->getusercard($url, $params,$method,$header);
                
            }elseif ('https'==$find['request_type']){
                
            }elseif ('webservice'==$find['request_type']){
                
            }
            //把数据转换为数组格式
            $usercard=objtoarray($usercard,$find['response_data_type']);
            //获取数据库中设置的返回映射值
            $responsearr=json_decode($find['api_response'],true);
            $code=$responsearr['code'];
            $card=$responsearr['user_card'];
            
            eval("\$card =  \$usercard$card;");//获取用户card
            if (!empty($card)){
                //如果获取到了卡号，则获取会员信息
                $userinfo=
                
            }else {
                $msg['code']=102;
            }
        }else {
            $msg['code']=102;
        }
        returnjson($msg);
        
        

        
        //第一步，先获取卡号
//         $useradmin=$this->useradmin;
//         $db=M('api',$useradmin['pre_table']);
//         dump($useradmin);
//         $card=$db->where(array('api_type'=>1))->find();

        
        
        
        
//         $db=M('user','market_');
//         $phone =$db->where(array('user_openid'=>$openid))->max('user_mobile');//->field('user_card,user_openid,user_mobile')->where(array('user_openid'=>$openid))->select();
//         if (0 != count($phone)){
//             $sel=$db->field('user_card,user_openid,user_mobile')->where(array('user_openid'=>$openid,'user_mobile'=>$phone))->find();
// //             $url=C('XIDANAPICODE.APIURL').':'.C('XIDANAPICODE.PORT').'/ws_member.asmx/GetMemberInfo';
// //             $params['strCallUserCode']=C('XIDANAPICODE.USERNAME');
// //             $params['strCallPassword']=C('XIDANAPICODE.PASSWORD');
// //             $params['strTelephone']=$sel['user_mobile'];
// //             $header=array('Content-Type:application/x-www-form-urlencoded',true);
// //             $data=http($url,$params,'POST',$header);

//             $userinfo=$this->getuserinfo($phone);
//             if (is_array($userinfo)){
//                 $this->user_card=$userinfo['Member']['VIPCODE'];
//                 $this->user_openid=$sel['user_openid'];
//                 $this->user_mobile=$sel['user_mobile'];
//                 $this->score=floor($userinfo['Member']['CURRENTBONUS']);
//                 return true;
//             }else{
//                 return false;
//             }
            
//         }else {
//             return false;
//         }
        
    }
    
    /**
     * @desc    根据openid或userid，或手机号等获取用户卡号
     * @param unknown $key
     */
    public function getcard($key){
        
    }
    
    
    
    
    
    
    
    /**
     * @desc 获取用户基本信息，包括积分
     */
    public function getuserinfo($phone){
        $url=C('XIDANAPICODE.APIURL').':'.C('XIDANAPICODE.PORT').'/ws_member.asmx/GetMemberInfo';
        $params['strCallUserCode']=C('XIDANAPICODE.USERNAME');
        $params['strCallPassword']=C('XIDANAPICODE.PASSWORD');
        $params['strTelephone']=$phone;
        $header=array('Content-Type:application/x-www-form-urlencoded',true);
        $data=http($url,$params,'POST',$header);
        $xml = simplexml_load_string($data);
        $xmlarr= json_decode(json_encode($xml),TRUE);
        if (array_key_exists('Error',$xmlarr)){
            return $xmlarr['Error']['ErrorCode'];
        }else{
            return $xmlarr;
        }
//         $postObj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
//         $jsonstr = objectToArray($postObj);
//         $arr=json_decode($jsonstr[0],true);
//         dump($arr);
    
    }
    
    
    
    
    
 /* (non-PHPdoc)
     * @see \Car\Controller\Xiaojukeji\Xiaojukeji::getaorderid()
     */
    public function getaorderid($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/order/Create/orderId';
        $data=http($url, $params);
        return $data;
        
    }
 /* (non-PHPdoc)
     * @see \Car\Controller\Xiaojukeji\Xiaojukeji::getrequest()
     */
    public function getrequest($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/order/Create/request';
        $data=http($url, $params,'POST');
        return $data;
        
    }
 /* (non-PHPdoc)
     * @see \Car\Controller\Xiaojukeji\Xiaojukeji::requeset()
     */
    public function requeset($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/order/Create/reRequest';
        $data=http($url, $params,'POST');
        return $data;
        
    }
 /* (non-PHPdoc)
     * @see \Car\Controller\Xiaojukeji\Xiaojukeji::getorderdetail()
     */
    public function getorderdetail($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/order/Detail/getOrderDetail';
        $data=http($url,$params);
        return $data;
    }
 /* (non-PHPdoc)
     * @see \Car\Controller\Xiaojukeji\Xiaojukeji::cancel_order()
     */
    public function cancel_order($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/order/Cancel';
        $data=http($url, $params,'POST');
        return $data;
    }



    /**
     * @desc  支付确认回调URL:
     */
    public function orderpayconfirm() {
        $db=M('');
        echo $this->AppKey;
        dump($this->useradmin);
        F('orderpayconfirm',$_GET);
        F('orderpayconfirm',$_POST);
        dump($_GET);
        dump($_POST);
        
    }
    
//     public function test(){
//         $par['action']='json';
//         $par['option']='send';
//         $par['openid']='oq1Gkt0SAMCs_hZ-EtZICFGGSRAQ';
//         $par['message']='您本次打车共消费1积分，为您节省了1元，打车不花钱，宝宝心里乐啊  :)';
//         $result=http('http://fw.joycity.mobi/weixin/index.php', $par);
//         echo $result;
//     }
    
    
    /**
     * @desc  支付成功回调URL:
     */
    public function odersuccess() {
        $data['normal_distance']=I('normal_distance');
        $data['total_price']=I('total_price');
        $data['pay_time']=I('pay_time');
        $data['status']=I('status');
        $data['callback_info']=I('callback_info');
        $data['timestamp']=I('timestamp');
        $data['sign']=I('sign');
        $db=M('carorder','xidanvip_');
        $sel=$db->where(array('order_id'=>I('order_id')))->find();
        if (null != $sel || '' != $sel){
            //扣积分
            if (700==I('status') && 700 != $sel['status']){
                $str=base64_decode(I('callback_info'));
                parse_str($str);
                $this->user_openid=$openid;
                $this->ratio_price=$ratio_price;
                $this->ratio_score=$ratio_score;
                $this->checkvip(I('callback_info'));//根据openid查询相关信息
                $price=$this->ratio_price;
                $score=$this->ratio_score;
                $this->checkvip($this->user_openid);
                
                $totalscore=round( ((int)$price/(int)$score) * $data['total_price'] );//echo $totalscore;echo ((int)$price/(int)$score) * $data['total_price'];
                $data['scoresense']=$totalscore;
                $data['orderstatus']=5;
//                 $url=C('XIDANAPICODE.APIURL').':'.C('XIDANAPICODE.PORT').'/ws_member.asmx/BonusAdjustment';
//                 //echo $url;
//                 $params['strCallUserCode']=C('XIDANAPICODE.USERNAME');
//                 $params['strCallPassword']=C('XIDANAPICODE.PASSWORD');
//                 $params['strMemberCode']=$this->user_card;
//                 $params['strBonusPoint']=(int)-$totalscore;
//                 $params['strAdjustReason']='滴滴打车测试，本次打车实际价格：'.$data['total_price'];
//                 $header=array('Content-Type:application/x-www-form-urlencoded',true);//dump($params);
//                 //die;
//                 $datas=http($url,$params,'POST',$header);
//                 $xml = simplexml_load_string($datas);
//                 $xmlarr= json_decode(json_encode($xml),TRUE);//dump($params);dump($datas);dump($xmlarr);

                $xmlarr=$this->cutscore($totalscore,$data['total_price']);
                
                if (array_key_exists('Error',$xmlarr)){
                    $data['xdcode']=$xmlarr['Error']['ErrorCode'];
                    $data['xdmsg']=$xmlarr['Error']['Description'];
                    //return $xmlarr['Error']['ErrorCode'];
                }else{
                    $data['xdcode']=$xmlarr['Success']['ReturnCode'];
                    $data['xdmsg']=$xmlarr['Success']['Description'];
                }
                if ($data['xdcode']==0){//如果扣除积分成功，发消息
                    $par['action']='json';
                    $par['option']='send';
                    $par['openid']=$sel['openid'];
                    $par['message']='您本次打车共消费'.$data['scoresense'].'积分，为您节省了'.$data['total_price'].'元，打车不花钱，宝宝心里乐啊  :)。';
                    http('http://fw.joycity.mobi/weixin/index.php', $par);
                }
                //查询剩余积分
                $userinfo=$this->getuserinfo($sel['passenger_phone']);
//                 if (is_array($userinfo)){
//                     $myscore=$userinfo['Member']['CURRENTBONUS'];
//                 }
                $myscore= is_array($userinfo)?floor($userinfo['Member']['CURRENTBONUS']):'';
                
                //向前端发送请求
                $url=C('CLIENT_URL');
                $orderstatus=C('ORDER_STATUS');
                $signpas='signature:'.I('order_id').'|'.$orderstatus[$data['status']].'|5cb7bbcb7eb8c42a049a4222f6fcabf2;';//MD5(lunxunhaha)
                $signqd=sha1($signpas);
                
                $pas=array('id'=>I('order_id'),'status'=>$data['status'],'state'=>$orderstatus[700],'code'=>107,'price'=>$data['total_price'],'sign'=>$signqd,'spoint'=>$data['scoresense'],'points'=>$myscore);
                
                http($url, $pas);//如果成功请求前端长链接接口
                
                
            }
            
            //dump($data);
            $change=$db->where(array('order_id'=>I('order_id')))->save($data);
            //echo $db->_sql();
            if (false !== $change){
                echo '{"errno":0,"errmsg":"success"}';
            }else {
                echo '{"errno":2,"errmsg":"error"}';//订单修改失败
            }
        }else {
            echo '{"errno":1,"errmsg":"未找到此订单"}';
        }
        
        
        
        
        
    }
    
    
    /**
     * 扣除积分
     */
    protected  function cutscore($totalscore,$total_price){
        $url=C('XIDANAPICODE.APIURL').':'.C('XIDANAPICODE.PORT').'/ws_member.asmx/BonusAdjustment';
        //echo $url;
        $params['strCallUserCode']=C('XIDANAPICODE.USERNAME');
        $params['strCallPassword']=C('XIDANAPICODE.PASSWORD');
        $params['strMemberCode']=$this->user_card;
        $params['strBonusPoint']=(int)-$totalscore;
        $params['strAdjustReason']='滴滴打车，本次打车实际价格：'.$total_price;
        $header=array('Content-Type:application/x-www-form-urlencoded',true);//dump($params);
        //die;
        $datas=http($url,$params,'POST',$header);
        $xml = simplexml_load_string($datas);
        $xmlarr= json_decode(json_encode($xml),TRUE);//dump($params);dump($datas);dump($xmlarr);
        return $xmlarr;
    } 
    
    
 /* (non-PHPdoc)
     * @see \Car\Controller\Xiaojukeji\Xiaojukeji::userinfo()
     */
    public function userinfo($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/user/Info/getUserInfo';
        $data=http($url, $params);
        return $data;
        
    }


    
    
    
}






interface Xiaojukeji{



    /**
     * @author 凯锋
     * @desc签名算法,操蛋的签名算法
     */
    public function sign($params);
    /**
     * @desc   授权认证
    */
    public function auth();
    /**
     *
     * @desc    地址联想
    */
    public function getaddress($params);
    /**
     *
     * @desc    获取城市车型
    */
    public function carlevel($params);


    /**
     *@desc  价格预估
    */

    public function estimateprice($params);
    
    
    /**
     * 获取订单id
     */
    public function getaorderid($params);
    
    
    /**
     * 发起叫车请求
     */
    public function getrequest($params);
    
    
    /**
     * @desc  重新叫单
     */
    public function requeset($params);
    
    
    /**
     * @desc  获取订单详情
     */
    public function getorderdetail($params);
    
    
    /**
     * @desc  取消订单
     */
    public function cancel_order($params);
    
    /**

    */
    public function orderrequest($client_id,$access_token,$timestamp,$sign,$order_id,$rule,$type,$passenger_phone,$city,$flat,$flng,$start_name,$start_address,$tlat,$tlng,$end_name,$end_address,$clat,$clng,$departure_time,$require_level,$app_time,$map_type,$combo_id,$sms_policy,$extra_info,$callback_info);

    
    /**
     * @desc  企业当前可用额度
     */
    public function userinfo($params);
    
    
    
    
    
    public function orderpayconfirm();

    public function odersuccess();
}


?>