<?php
namespace Car\Controller\Xiaojukeji;
use Car\Controller\CarcommonController;
use Common\Controller\RedisController;
use Common\Controller\PublicController;
/**
 *滴滴打车接口部分
 */
class XiaojuController extends CarcommonController implements xiaojukeji{

	public $templateId;
    public function __initialize(){
        parent::__initialize();
    }

    private function getAdminInfoById($id){
        $this->apiurl=C('XIAOJU_API_URL');
        $admindb=M('admin','total_');
		$dbratio=M('admin_xiaoju','total_');
		$ratio=$dbratio->where(array('adminid'=>$this->admininfo['id']))->find();
		$setting = $ratio['setting'];
		$this->AppKey=$setting['CLIENT_ID'];
		$this->AppSecret=$setting['CLIENT_SECRET'];
		$this->key=$setting['SIGN_KEY'];
		$this->TEST_CALL_PHONE=$setting['TEST_CALL_PHONE'];
		$this->templateId=$setting['TEMPLATE'];

		$this->admininfo=$admindb->where(array('id'=>$id))->find();
		$access_token=$this->redis->get('xiaoju_access_token:'.$this->admininfo['id']);
	    if (empty($access_token)){
	 	   $this->auth();
	    }else {
		   $this->access_token=$access_token;
	    }

	    $this->ratio_score=(int)$ratio['points'];
	    $this->ratio_price=(int)$ratio['price'];
	    $this->minscore=(int)$ratio['minscore'];
	
    } 
    /**
     * @desc  城市列表
     */
    public function soonetest(){  
		$this->getAdminInfoById(I('id'));
        $params1 = array();       
        $params1['order_id']=I('order_id');
        $params1['client_id']=$this->AppKey;
        $params1['access_token']=@$this->access_token;
        $params1['timestamp']=time();
        $sign1=$this->sign($params1);
        $params1['sign']=$sign1;  
        $data1=$this->getorderdetail($params1);
        print_r($data1);
    } 
    private function soonekoujifen(){
		$this->getAdminInfoById(I('id'));
        $params1 = array();       
        $params1['order_id']=I('order_id');
        $params1['client_id']=$this->AppKey;
        $params1['access_token']=@$this->access_token;
        $params1['timestamp']=time();
        $sign1=$this->sign($params1);
        $params1['sign']=$sign1;  
        $data1=$this->getorderdetail($params1);
		$data1 = json_decode($data1,true);
		if($data1['data']['order']['status']==700){
			$totalscore=round( (int)150 * $data1['data']['price']['total_price'] );
			//echo $totalscore;echo ((int)$price/(int)$score) * $data['total_price'];
			$data = array();
			$data['scoresense']=$totalscore;
			$data['orderstatus']=5;
			$data['xdmsg']='还未请求扣积分接口';
			$db=M('carorder',$this->admininfo['pre_table']);
			$change=$db->where(array('order_id'=>I('order_id')))->save($data);
			$xmlarr=$this->cutscore($totalscore,$data['data']['price']['total_price']);
            $change=$db->where(array('order_id'=>I('order_id')))->save(array('status'=>700,'scoresense'=>$totalscore,'orderstatus'=>5));

		}
        print_r($data1);
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
            $didicity=F('city');
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
                    $didicity=F('city');//echo $city;
                    $keys=array_search($city,array_column($didicity, 'name'));
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
    
    //@see \Car\Controller\xiaojukeji::auth()
    public function auth(){
		$url=$this->apiurl.'/v1/Auth/authorize';
        $params['client_id']=$this->AppKey;
        $params['client_secret']=$this->AppSecret;
        $params['grant_type']='client_credentials';
        $params['phone']=$this->TEST_CALL_PHONE;
        $params['timestamp']=time();
        $sign=$this->sign($params);//获取签名
		$params['sign']=$sign;
        $url=$this->apiurl.'/v1/Auth/authorize';
        $auth =http($url, $params,'POST');
		$auth=json_decode($auth,true);
        //如果有access_token这个key，则保存，否则跳出
        if (array_key_exists('access_token',$auth)){
		$access_token=$this->redis->get('xiaoju_access_token:'.$this->admininfo['id']);
		$this->redis->set('xiaoju_access_token:'.$this->admininfo['id'],$auth['access_token'],array('ex'=>1800));
            $this->access_token=$auth['access_token'];
            return $auth;
        }else{
            $msg['code']=104;
            $msg['msg']=L('error');
            //echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        
    }

 /* (non-PHPdoc)
  * @see \Car\Controller\xiaojukeji::carlevel()
  * change_soone
     */
    public function carlevel($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/common/Cities/getPrice';
        $data=http($url, $params);
        return $data;
        
    }

 /* (non-PHPdoc)
     * @see \Car\Controller\xiaojukeji::estimate()
     */
    public function estimateprice($params)
    {
        // TODO Auto-generated method stub
        $url=$this->apiurl.'/v1/common/Estimate/priceCoupon';
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

    
    
    
    
    /**
     * @desc 获取用户基本信息，包括积分
     */
    public function getuserinfo($phone){
        if (!$phone) return false;
        $data['mobile'] = $phone;
        $data['key_admin'] = $this->admininfo['ukey'];
		$data['sign_key'] = $this->admininfo['signkey'];
		$data['openid'] = $this->user_openid;
        $data['sign'] = sign($data);
        writeOperationLog(array('make sign' => 'mobile:' . $data['mobile'] . ' ,key_admin:' . $data['key_admin'] . ' ,sign_key:' . $data['sign_key'] . ' ,sign' . $data['sign']), 'jaleel_logs');
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobymobile';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('get member by tel' => $curl_re), 'xiaoju_logs');
        $arr = json_decode($curl_re, true);
		if($arr['code'] != 200){
			return false;
		}else{
			return $arr['data'];
        }
    
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
    
    
    /**
     * @desc  支付成功回调URL:
     */
    public function odersuccess() {
        $data['normal_distance']=@I('normal_distance');
        $data['total_price']=@I('total_price');
        $data['pay_time']=@I('pay_time');
        $data['status']=@I('status');
        $data['callback_info']=@I('callback_info');
        $data['timestamp']=I('timestamp');
		$data['sign']=I('sign');
		parse_str(base64_decode($data['callback_info']),$callbackInfo);

        $admindb=M('admin','total_');
		$this->admininfo=$admindb->where(array('id'=>$callbackInfo('aid')))->find();

		$db=M('carorder',$this->admininfo['pre_table']);
        $sel=$db->where(array('order_id'=>I('order_id')))->find();//dump($sel);
		$paramsWrite = @I('param.');
		if(is_array($paramsWrite)){ F('ordercallback',$paramsWrite); }

        if (null != $sel || '' != $sel){
			//调用订单详情接口
			$this->getAdminInfoById($sel['adminid']);
			$params1 = array();
			$params1['order_id']=I('order_id');
			$params1['client_id']=$this->AppKey;
			$params1['access_token']=@$this->access_token;
			$params1['timestamp']=time();
			$sign1=$this->sign($params1);
			$params1['sign']=$sign1;
			$data1=@$this->getorderdetail($params1);
			$mark = false;
			if(is_json($data1)){
				$data1p=json_decode($data1,true);
				if (0 == $data1p['errno'] && $data1p['data']['order']['status']==700){
					$mark = true;	
				}
			}
			//支付确认
            //扣积分
            if ((700==$data['status'] || $mark) && 700 != $sel['status']){
				$callsoone = I('callback_info');
				if(!empty($callsoone)){
					$str=base64_decode(I('callback_info'));
					parse_str($str);
					$this->ratio_price=$ratio_price;
					$this->ratio_score=$ratio_score;
				} 
                $this->user_openid=$sel['openid'];
                $price=$this->ratio_price;
				$score=$this->ratio_score;

				//查询会员卡号
				$dbmem=M('mem',$this->admininfo['pre_table']);
				$memInfo =$dbmem->where(array('openid'=>$sel['openid']))->find();
				$this->user_card = $memInfo['cardno'];

                $totalscore=round( ((int)($score/$price)) * $data['total_price'] );
                $data['scoresense']=$totalscore;
                $data['orderstatus']=5;
                $data['xdmsg']='还未请求扣积分接口';
				$order_idss=I('order_id');
                $change=$db->where(array('order_id'=>$order_idss))->save($data);
                $xmlarr=$this->cutscore($totalscore,$data['total_price']);
                
                if (array_key_exists('Error',$xmlarr)){
                    $data['xdcode']=$xmlarr['Error']['ErrorCode'];
                    $data['xdmsg']=$xmlarr['Error']['Description'];
                }else{
                    $data['xdcode']=$xmlarr['Success']['ReturnCode'];
                    $data['xdmsg']=$xmlarr['Success']['Description'];
                }
                if ($data['xdcode']==0 && !empty($this->templateId)){//如果扣除积分成功，发消息
					$tempmessage=array(array(
						'touser'=>$sel['openid'],
						'template_id'=>$this->templateId,
						'url'=>'',
						'data'=>array(
							'first'=>array('value'=>'您本次打车共消费'.$data['scoresense'].'积分，为您节省了'.$data['total_price'].'元，打车不花钱，宝宝心里乐啊  :)。','color'=>'#173177'),
							'keyword1'=>array('value'=>$data['scoresense'], 'color'=>'#173177'),
							'keyword2'=>array('value'=>$data['total_price'], 'color'=>'#173177'),
							'remark'=>array('value'=>'谢谢您的支持！', 'color'=>'#173177'),
						)
					));
					$url='https://mem.rtmap.com/Thirdwechat/Wechat/Template/outsideSendMessage';
					$sign=sign(array('sign_key'=>$this->admininfo['signkey'],'key_admin'=>$this->admininfo['ukey']));
					$url=$url.'?key_admin='.$this->admininfo['ukey'].'&sign='.$sign;
					curl_https($url,json_encode($tempmessage), array(), 30, true);//发送模板消息
                }
                //查询剩余积分
                $userinfo=$this->getuserinfo($sel['passenger_phone']);
                $myscore= is_array($userinfo)?floor($userinfo['score']):'';
                
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
        $param['key_admin']=$this->admininfo['ukey'];
        $param['sign_key']=$this->admininfo['signkey'];
		$param['cardno']=$this->user_card;
        $param['scoreno']=$totalscore;
        $param['why']='兑换滴滴打车服务';
        $param['sign']=sign($param);
        unset($param['sign_key']);
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/cutScore';//扣除积分接口
        $curl_res=http($url,$param);
		$res=json_decode($curl_res,true);
		return $res;
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
