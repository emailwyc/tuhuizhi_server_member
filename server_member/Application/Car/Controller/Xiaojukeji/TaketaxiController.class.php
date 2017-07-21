<?php
namespace Car\Controller\Xiaojukeji;
/**
 * 商户打车接口
 * 每一个请求里面都要加ukey，openid，phone
 */
class TaketaxiController extends XiaojuController{
	// TODO - Insert your code here
	public $ratio;
	public function __construct(){
		parent::__initialize();
        $openid=I('openid');
        $ukey=I('ukey');
		if (empty($openid) || empty($ukey)){
            $msg['code']=100;
			$msg['msg']=L('Incomplete_parameters');
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
		}
		//获取商户信息
		$this->admininfo = $this->getMerchant($ukey);
        //最重要的一步，判断是否是会员，暂时注释
		if(!$this->checkvip($openid,$this->admininfo['pre_table'])){
            $msg['code']=103;
            $msg['msg']=L('notvip');
            returnjson($msg,$this->returnstyle,$this->callback);exit;
        };
        //根据传过来的参数，获取哪一家的appid等参数
		$this->useradmin=$this->admininfo;
            
        //查询积分比率
        $dbratio=M('admin_xiaoju','total_');
		$ratio=$dbratio->where(array('adminid'=>$this->admininfo['id']))->find();
		$this->ratio = $ratio;
		if(empty($ratio) || $ratio['price']<=0){
			returnjson(array('code'=>11,'msg'=>"商户还未设置积分比率！"),$this->returnstyle,$this->callback);exit;
        }
		$this->ratio_score=(int)$ratio['points'];
		$this->ratio_price=(int)$ratio['price'];
		$this->minscore=(int)$ratio['minscore'];
		//滴滴配置
		if(empty($ratio['setting'])){
			returnjson(array('code'=>12,'msg'=>"商户未开通滴滴打车服务！"),$this->returnstyle,$this->callback);exit;
		}
		$setting = json_decode($ratio['setting'],true);
		//todo
		$this->AppKey=$setting['CLIENT_ID'];
		$this->AppSecret=$setting['CLIENT_SECRET'];
		$this->key=$setting['SIGN_KEY'];
		$this->TEST_CALL_PHONE=$setting['TEST_CALL_PHONE'];
         
        //查看redis里面缓存的access_token有没有超时，如果超时则重新获取
		$access_token=$this->redis->get('xiaoju_access_token:'.$this->admininfo['id']);
		if (empty($access_token)){
			$this->auth();
		}else {
			$this->access_token=$access_token;
		}
    }
    
    /**
    * @desc  返回当前用户积分和积分是否满足最小数
    */
    public function getuserinfos(){
        $msg=$this->myerrorcode;
        if ($this->score < $this->minscore){
            $data['isyes']=false;
        }else {
            $data['isyes']=true;
        }
        $data['score']=$this->score;
        $msg['code']=200;
        $msg['msg']=L('success');
        $msg['data']=$data;
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
	 * @desc 根据客户选择的专车还是快车，获取城市车型列表，如不选，默认快车，获取后判断城市是否有此车
	 * change_soone
     */
    public function getcarlevel(){
        $msg=$this->myerrorcode;
        $area=I('area');
		$rules=I('rule');
        if (empty($area) || empty($rules)){
            $msg['code']=(int)100;
            $msg['msg']=L('Incomplete_parameters');
        }else{
    
            $rule=!empty($rules)?$rules:301;//201专车，301快车
			$rule=301==$rule || 201==$rule?$rule:301;//两步判断，以免传入有误
			$params['client_id']=$this->AppKey;
            $params['access_token']=$this->access_token;
            $params['timestamp']=time();
            $params['rule']=$rule;
            $params['city']=(int)$area;
            $sign=$this->sign($params);
            $params['sign']=$sign;//dump($params);die;
            $cardata=$this->carlevel($params);
            $data=json_decode($cardata,true);
            if (is_json($cardata)){
                if (0==$data['errno']){//如果返回数据成功
                    $car = array();
                    $i=0;
                    foreach($data['data'] as $key => $val){//循环所有车，搜索和用户选择的车一致的车类别，专车还是快车
						$car[$i]['name']=$val['c_level_name'];
						$car[$i]['car_level']=$val['car_level'];
						$car[$i]['area'] = $val['area'];
						$i++;
                    }
                    if (null!=$car){
                        $msg['code']=200;
                        $msg['dataofapi']=$car;
                        $msg['msg']=L('success');
                    }else {
                        $msg['code']=102;
                        $msg['dataofapi']=$car;
                        $msg['msg']=L('resultisnull');
                    }
                }else {
                    $msg['code']=$data['errno'];
                    $msg['msg']=$data['errmsg'];
                }
            }else {
                $msg['code']=(int)101;
                $msg['msg']=L('interface_error');
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    
    /**
     * @desc    用户输入地址联想
     */
    public function getAddressdata(){
        $city=I('city');
        $input=I('input');
        $msg=$this->myerrorcode;
        if (empty($city) || empty($input)){
            $msg['code']=(int)100;
            $msg['msg']=L('Incomplete_parameters');
        }else {
            $params['client_id']=$this->AppKey;
            $params['access_token']=$this->access_token;
            $params['city']=$city;
            $params['input']=$input;
            $params['timestamp']=time();
            $sign=$this->sign($params);//签名
            $params['city']=$city;
            $params['input']=$input;
            $params['sign']=$sign;
            urlencode($params);
            $getaddress=$this->getaddress($params);
			$data=json_decode($getaddress,true);
            if (is_json($getaddress)){
                if (0==$data['errno']){
                    $msg['code']=(int)200;
                    $msg['msg']=L('success');
                    $msg['dataofapi']=$data;
                }else {
                    $msg['code']=$data['errno'];
                    $msg['msg']=$data['errmsg'];
                }
            }else {
                $msg['code']=(int)101;
                $msg['msg']=L('interface_error');
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * @desc  查询商户信息
     */
    public function getcompanyinfo(){
        $params['client_id']=$this->AppKey;
        $params['access_token']=$this->access_token;
        $params['timestamp']=time();
        $sign=$this->sign($params);
        $params['sign']=$sign;
        $companyinfo=$this->userinfo($params);
        print_r($companyinfo);
    }
    
    /**
     * @desc  价格预估
     */
    public function estimationprici(){
        $msg=$this->myerrorcode;
        $params['flat']=I('flat');//出发地纬度
        $params['flng']=I('flng');//出发地经度
        $params['tlat']=I('tlat');//目的地纬度
        $params['tlng']=I('tlng');//目的地经度
        $params['require_level']=I('level');//车型代码（专车如：100、200等；快车如：600等）
        $params['rule']=I('rule');//计价模型分类，201(专车)；301(快车)
        $params['city']=I('area');//城市id
        if (empty($params['flat']) || empty($params['flng']) || empty($params['tlat']) || empty($params['tlng']) || empty($params['require_level']) || empty($params['rule']) || empty($params['city'])){
            $msg['code']=100;
            $msg['msg']=L('Incomplete_parameters');
        }else {
            $params['type'] = 0;
            $params['client_id']=$this->AppKey;
            $params['access_token']=$this->access_token;
            $params['timestamp']=time();
            $params['departure_time']=date('Y-m-d H:i:s');
            $sign=$this->sign($params);
            $params['sign']=$sign;
            $msg=$this->priceyg($params);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 预估价格，并判断积分是否满足条件
     */
    private function priceyg($params){
		$ratio=$this->ratio;
        if (null!=$ratio){
            $data=$this->estimateprice($params);
            $pdata=json_decode($data,true);
            if (is_json($data)){
                if (0==$pdata['errno']){
                    
                    $price=$pdata['data'][$params['require_level']]['price'];
                    $jifen=round(((int)$ratio['points'] / (int)$ratio['price']) * (float)$price);//用获取的钱数计算积分数
                    $msg['code']=200;
                    $msg['msg']=L('success');
                    $msg['dataofapi']=$pdata;
                    $isyes=$jifen<$this->score?(bool)true:(bool)false;
					$dynamic_md5 = $pdata['data'][$params['require_level']]['dynamic_md5'];
                    $returndata = array('price'=>$price,'points'=>$jifen,'userscore'=>$this->score,'isyes'=>$isyes,'dynamic_md5'=>$dynamic_md5);
                    $msg['data']=$returndata;
                }else{
                    $msg['code']=104;
                    $msg['msg']=L('error').'_'.$pdata['errno'];
                }
            }else{
                $msg['code']=101;
                $msg['msg']=L('interface_error');
            }
        }else{
            $msg['code']=105;
            $msg['msg']=L('noratio');
        }
        return $msg;
    }
    
    /**
     * @desc  创建订单
     */
	public function getorderid(){
        $params['client_id']=$this->AppKey;
        $params['access_token']=$this->access_token;
        $params['timestamp']=time();
        $sing=$this->sign($params);
        $params['sign']=$sing;
		$data=$this->getaorderid($params);
        $msg=$this->myerrorcode;
        if(is_json($data)){
            $dataarr=json_decode($data,true);
            if (0==$dataarr['errno']){
                return $dataarr['data']['order_id'];
            }else {
                $msg['code']=104;
                $msg['msg']=L('error').'_'.$dataarr['errno'];
            }
        }else {
            $msg['code']=101;
            $msg['msg']=L('interface_error');
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * @desc  发起订单请求，叫车前必须预估价格
     */
    public function request(){
		$result=$this->checkuserishaveorder($this->user_mobile);
        if (108 == $result['code']){
            echo returnjson($result,$this->returnstyle,$this->callback);exit();
        }else{
            //定义两个参数
            if (null!=$this->ratio_score && null != $this->ratio_price){
                $msg=$this->myerrorcode;
                $yg['rule']=$params['rule']=I('rule');//201zhuanche,301快车
                $yg['city']=$params['city']=(int)I('area');
                $yg['flat']=$params['flat']=I('flat');//出发地纬度
                $yg['flng']=$params['flng']=I('flng');//出发地经度
                $params['start_name']=I('start_name');//出发地名称，最多50个字
                //$params['start_address']=I('start_address');//出发地详细名称100个字
                $yg['tlat']=$params['tlat']=I('tlat');//目的地纬度，rule为201和301时必须
                $yg['tlng']=$params['tlng']=I('tlng');//目的地纬度，rule为201和301时必须
                $params['end_name']=I('end_name');//目的地名称(rule为201,301时必须,最多50个字)
                //$params['end_address']=I('end_address');//目的地详细地址(rule为201,301时必须,最多100个字)
                $yg['require_level']=$params['require_level']=I('level');//所需车型,车型代码（专车如：100、200等；快车如：600等）
				$callback='ratio_price='.$this->ratio_price.'&ratio_score='.$this->ratio_score.'&openid='.$this->user_openid.'&aid='.$this->admininfo['id'];//注意这个地方不能超过100个字符，按理说应该不会
                $callback=base64_encode($callback);
                $params['callback_info']=$callback;
            
                if (!in_array('',$params)){
                    $yg['client_id']=$params['client_id']=$this->AppKey;
                    $yg['access_token']=$params['access_token']=$this->access_token;
                    $yg['timestamp']=$params['timestamp']=time();
            	    $yg['type'] = 0;
                    $signs=$this->sign($yg);
                    $yg['sign']=$signs;
                    $ygprice=$this->priceyg($yg);
                    if (true==$ygprice['data']['isyes']){
                        $params['type']=0;//0实时，1预约,不做预约，写死，做实时
                        $params['passenger_phone']=$this->user_mobile;
                        $params['departure_time']=date('Y-m-d H:i:s');//date('Y-m-d H:i:s',strtotime("$time +40 minutes"));//出发地时间  date('Y-m-d H:i:s')
                        $params['app_time']=date('Y-m-d H:i:s');//客户端时间
                        $params['order_id']=$this->getorderid();
                        $params['dynamic_md5']= $ygprice['data']['dynamic_md5'];
                        $sign=$this->sign($params);
                        $params['sign']=$sign;
                        //dump($params);
                        urlencode($params);
                        $data=$this->getrequest($params);
                        $params['city_name']=I('city_name');
                        $params['openid']=$this->user_openid;
                        $params['orderstatus']=0;
                        $admin=$this->useradmin;
                        $params['adminid']=$admin['id'];
						$db= M('carorder',$this->admininfo['pre_table']);
                        $add = $db->add($params);
            
            
                        if (is_json($data)){
                            $datap=json_decode($data,true);
                            if (0 == $datap['errno']){
								$this->redis->set($params['order_id'].$this->admininfo['id'],10,array('ex'=>1800));//设置这个订单的轮询时间，轮询方法在此方法下面
                                $change['orderstatus']=1;
                                $change['errno']=$datap['errno'];
                                $change['scoreplan']=round(($this->ratio_score / $this->ratio_price)*$datap['data']['price']['estimate']);
                                $change['id']=$add;
                                $db->save($change);
                                $msg['code']=200;
                                $msg['msg']=L('success');
                                $msg['dataofapi']=$datap;
                                $msg['data']=$ygprice;
                            }else {
                                $change['orderstatus']=2;
                                $change['errno']=$datap['errno'];
                                $change['id']=$add;
                                $db->save($change);
                                $msg['code']=104;
                                $msg['msg']=L('error').'_'.$datap['errno'];
                            }
                        }else {
                            $change['orderstatus']=2;
                            $change['errno']='返回值不是json';
                            $change['id']=$add;
                            $db->save($change);
                            $msg['code']=101;
                            $msg['msg']=L('interface_error');
                        }
                    }else{
                        $msg=$ygprice;
                    }
                    ####################################################################
                }else{
                    $msg['code']=100;
                    $msg['msg']=L('Incomplete_parameters');
                }
            }else{
                $msg['code']=105;
                $msg['msg']=L('noratio');
            }
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

    }
    
    
    /**
     * @desc  页面打开检查用户是否有未完成订单
     */
    public function checkunprocessed(){
        $data=$this->checkuserishaveorder($this->user_mobile);
        echo returnjson($data,$this->returnstyle,$this->callback);exit();
    }
    
    
    
    /**
     * @desc  检查此用户当前是否有未完成的订单
     * @param $phone 
     * @param $openid 用户openid
     */
    public function checkuserishaveorder($phone){
		$db=M('carorder',$this->admininfo['pre_table']);
        $msg=$this->myerrorcode;
        $sel=$db->field('order_id,status')->where(array('passenger_phone'=>$phone,'status'=>array('in',array('0','300','400','410','500')),'orderstatus'=>array('in',array('1','3')) ))->find();
        //echo $db->_sql();dump($sel);
        if (null != $sel){
            $orderstatus=C('ORDER_STATUS');
            $msg['code']=108;
            $msg['data']=array('id'=>$sel['order_id'],'state'=>$orderstatus[$sel['status']]);
            $msg['msg']=L('ishaveorder');
            //return false;
        }else {
            $msg['code']=109;
            $msg['msg']=L('nohaveorder');
        }
        return $msg;
    }
    
    /**
     * @desc  轮询执行订单详情请求
     */
    public  function trunsorder(){
        $order_id=I('order_id');//echo $order_id;die;
        if (empty($order_id)){
            break;
        }
        ignore_user_abort();
		$db=M('carorder',$this->admininfo['pre_table']);
        $sel=$db->field('order_id,status')->where(array('order_id'=>$order_id))->select();
		$time=$redis->get($order_id.$this->admininfo['id']);
        $i=0;
        while (1==count($sel) && 500>$sel[0]['status'] && !empty($time)) {
            $url=C('CLIENT_URL');
            if ($i>=150){
                break;
            }
			if ('no'==$redis->get($order_id.$this->admininfo['id'].'_isturn')){//如果订单被取消，停止轮询，并删除redis值
				$redis->delete($order_id.$this->admininfop['id'].'_isturn');
                break;
            }
            
            sleep($time);//第一次睡的时间是第一次订单请求时设置的时间
            $data=$this->turnorder($order_id);
            if (is_json($data)){
                $datap=json_decode($data,true);
                if (0 == $datap['errno']){
                    $result=array(
                        'status'=>$datap['data']['order']['status'],
                        'driver_phone'=>$datap['data']['order']['drive_phone'],
                        'driver_name'=>$datap['data']['order']['driver_name'],
                        'driver_card'=>$datap['data']['order']['driver_card'],
                        'driver_level'=>$datap['data']['order']['driver_level'],
                        'start_name'=>$datap['data']['order']['start_name'],
                        'end_name'=>$datap['data']['order']['name'],
                    );
                    $orderinfo['status']=$datap['data']['order']['status'];
                    $db->where(array('order_id'=>$order_id))->save($orderinfo);
//                     $msg['data']=$result;
                    //$dbdata['status']=$datap['data']['order']['status'];
                    
                    try {
                        $i++;
                        $orderstatus=C('ORDER_STATUS');
                        if (array_key_exists($datap['data']['order']['status'],$orderstatus)){//如果滴滴返回的状态码符合前端要求的状态码
							if ($redis->get($order_id.$this->admininfo['id'].'status') != $datap['data']['order']['status']){//如果redis里面的状态吗不等于现在的状态
                                $signpas='signature:'.$order_id.'|'.$orderstatus[$datap['data']['order']['status']].'|5cb7bbcb7eb8c42a049a4222f6fcabf2;';//MD5(lunxunhaha)
                                $signqd=sha1($signpas);
                                if ($i>=50){
                                    $pas=array('id'=>$order_id,'state'=>$orderstatus[$datap['data']['order']['status']],'code'=>107,'sign'=>$signqd);
                                }else{
                                    $pas=array('id'=>$order_id,'state'=>$orderstatus[$datap['data']['order']['status']],'code'=>106,'sign'=>$signqd);
                                }
								$redis->set($order_id.$this->admininfo['id'].'status',$datap['data']['order']['status'],array('ex'=>120));
                                http($url, $pas);//如果成功请求前端长链接接口
                            }
                        }
                        
                        
                    } catch (Exception $e) {
                        
                    }
                    if ($datap['data']['order']['status'] ==311){
//                         $signpas='signature:'.$order_id.'|'.$datap['data']['order']['status'].'|5cb7bbcb7eb8c42a049a4222f6fcabf2';//MD5(lunxunhaha)
//                         $signqd=sha1($signpas);
//                         $pas=array('orderid'=>$order_id,'status'=>$datap['data']['order']['status'],'code'=>107,'secret'=>$signqd);
//                         http($url, $pas);//如果成功请求前端长链接接口
                        break;
                    }
                    if ($datap['data']['order']['status'] >=500){
//                         $signpas='signature:'.$order_id.'|'.$datap['data']['order']['status'].'|5cb7bbcb7eb8c42a049a4222f6fcabf2';//MD5(lunxunhaha)
//                         $signqd=sha1($signpas);
//                         $pas=array('orderid'=>$order_id,'status'=>$datap['data']['order']['status'],'code'=>107,'secret'=>$signqd);
//                         http($url, $pas);//如果成功请求前端长链接接口
                        //$pas=array('orderid'=>$order_id,'status'=>'','code'=>107,'secret'=>$signqd);
                        break;
                    }
                }else {
                    if (36003!=$datap['errno']){
//                         $signpas='signature:'.$order_id.'|'.$datap['data']['order']['status'].'|5cb7bbcb7eb8c42a049a4222f6fcabf2';//MD5(lunxunhaha)
//                         $signqd=sha1($signpas);
//                         $pas=array('orderid'=>$order_id,'status'=>'','code'=>107,'secret'=>$signqd);
//                         http($url, $pas);
//                         $msg['code']=107;
//                         $msg['msg']=L('finish').'_'.$datap['errno'];
                        //echo returnjson($msg,$this->returnstyle,$this->callback);
                        break;//如果不是超频状态码，跳出循环
                    }
                }
            }else {//如果不是json，跳出循环
//                 $signpas='signature:'.$order_id.'|'.$datap['data']['order']['status'].'|5cb7bbcb7eb8c42a049a4222f6fcabf2';//MD5(lunxunhaha)
//                 $signqd=sha1($signpas);
//                 $pas=array('orderid'=>$order_id,'status'=>'','code'=>107,'secret'=>$signqd);
//                 http($url, $pas);
                break;
            }
        }
    }
    
    
    /**
     * @desc  轮询请求订单详情，单独拿出来，不然签名失败
     */
    private function turnorder($order_id){
        $params['order_id']=$order_id;
        $params['client_id']=$this->AppKey;
        $params['access_token']=$this->access_token;
        $params['timestamp']=time();
        $sign=$this->sign($params);
        $params['sign']=$sign;
        $data=$this->getorderdetail($params);
        return $data;
    }
    
    
    
    
    /**
     * @desc  重新叫单
     */
    public function resetrequest(){
        $msg=$this->myerrorcode;
        $params['order_id']=I('order_id');
        if(!in_array('',$params)){
            $params['client_id']=$this->AppKey;
            $params['access_token']=$this->access_token;
            $params['timestamp']=time();
            $sign=$this->sign($params);
            $params['sign']=$sign;
            $data=$this->requeset($params);
            if (is_json($data)){
                $datap=json_decode($data,true);
                if (0 == $datap['errno']){
//                     $db=M('carorder','xidanvip_');
//                     $update=$db->where(array('order_id'=>$params['order_id']))->save(array('orderstatus'=>6));
                    $msg['code']=200;
                    $msg['msg']=L('success');
                    $msg['data']=array('time'=>date('Y/m/d H:i:s'));
                    $msg['dataofapi']=$datap;
                }else {
                    $msg['code']=104;
                    $msg['msg']=L('error').'_'.$datap['errno'];
                }
            }else {
                $msg['code']=101;
                $msg['msg']=L('interface_error');
            }
        }else{
            $msg['code']=100;
            $msg['msg']=L('Incomplete_parameters');
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * @desc  获取订单详情
     */
    public function getorderinfo(){
        $params['order_id']=I('orderid');
        $msg=$this->myerrorcode;
        if (!empty($params['order_id'])){
            $params['client_id']=$this->AppKey;
            $params['access_token']=$this->access_token;
            $params['timestamp']=time();
            $sign=$this->sign($params);
            $params['sign']=$sign;
            $data=$this->getorderdetail($params);
            if (is_json($data)){
                $datap=json_decode($data,true);
                if (0 == $datap['errno']){
                    $msg['code']=200;
                    $msg['msg']=L('success');
                    $msg['dataofapi']=$datap;
                }else {
                    $msg['code']=104;
                    $msg['msg']=L('error').'_'.$datap['errno'];
                }
            }else {
                $msg['code']=101;
                $msg['msg']=L('interface_error');
            }
        }else {
            $msg['code']=100;
            $msg['msg']=L('Incomplete_parameters');
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    
    /**
     * @desc  取消订单
     */
    public function cancelorder(){
        $params['order_id']=I('orderid');
        $msg=$this->myerrorcode;
		$db=M('carorder',$this->admininfo['pre_table']);
        if (!0!=1){//null != $db->where(array('order_id'=>$params['order_id'],'orderstatus'=>4))->find()
            $msg['code']=200;
            $msg['msg']=L('success');
        }else{
            if (!empty($params['order_id'])){
                $force=I('forces');//是否强制取消订单
                $params['client_id']=$this->AppKey;
                $params['access_token']=$this->access_token;
                $params['timestamp']=time();
                $params['force']='true';//0==$force?'false':'true';//怎么简单怎么来
                $sign=$this->sign($params);
                $params['sign']=$sign;
                $data=$this->cancel_order($params);
                //dump($data);
                if (is_json($data)){
                    $datap=json_decode($data,true);
//                     dump($datap);
//                     dump(array_key_exists('cost',$datap['data']));
//                     die;
                    if (0 == $datap['errno']){
            
                        $da['orderstatus']=4;
                        if (array_key_exists('cost',$datap['data']) && $datap['data']['cost']>0 ){//如果扣钱，则扣积分
                            $price=$this->ratio_price;
                            $score=$this->ratio_score;
                            $totalscore=round( ((int)$score/(int)$price) * $datap['data']['cost'] );
                            $data['scoresense']=$totalscore;
                            $xmlarr=$this->cutscore($totalscore,$datap['data']['cost']);
                            $da['scoresense']=$totalscore;
                            $da['total_price']=$datap['data']['cost'];
                            if (array_key_exists('Error',$xmlarr)){
                                $da['xdcode']=$xmlarr['Error']['ErrorCode'];
                                $da['xdmsg']=$xmlarr['Error']['Description'];
                                //return $xmlarr['Error']['ErrorCode'];
                            }else{
                                $da['xdcode']=$xmlarr['Success']['ReturnCode'];
                                $da['xdmsg']=$xmlarr['Success']['Description'];
                            }
                        }
                        $da['xdcode']='';
                        $da['scoresense']=0;
                        $da['xdmsg']='此订单未扣除积分，未请求扣除积分接口';
            
                        $change=$db->where(array('order_id'=>$params['order_id']))->save($da);
                        //echo $db->_sql();
                        $msg['code']=200;
                        $msg['msg']=L('success');
                        $msg['data']=array('total_score'=>$totalscore);
                        $msg['dataofapi']=$datap;
            
                        //取消订单时设置redis，轮询代码读取到设置的订单值以后停止轮询
						$this->redis->set($params['order_id'].$this->admininfo['id'].'_isturn','no');
            
                    }elseif (20010==$datap['errno']){
                        $da['xdcode']='';
                        $da['xdmsg']='此订单已超时，未请求接口';
                        $da['orderstatus']=4;
                        $change=$db->where(array('order_id'=>$params['order_id']))->save($da);
                        
                        $msg['code']=200;
                        $msg['msg']=L('success');
                        //$msg['data']=array('total_score'=>$totalscore);
                        $msg['dataofapi']=$datap;
                        
                        
                        //取消订单时设置redis，轮询代码读取到设置的订单值以后停止轮询
						$this->redis->set($params['order_id'].$this->admininfo['id'].'_isturn','no');
                    }else {
                        $msg['code']=104;
                        $msg['msg']=L('error').'_'.$datap['errno'];
                    }
                }else {
                    $msg['code']=101;
                    $msg['msg']=L('interface_error');
                }
            }else {
                $msg['code']=100;
                $msg['msg']=L('Incomplete_parameters');
            }
        }
        
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * @desc 定时从滴滴获取所有city，做计划任务，每天访问请求此url
     */
    public function getallcity(){header ( "Content-Type:text/html;charset=utf-8" );
        $data201=$this->get201city();
        $data301=$this->get301city();
        dump($data201);
        dump($data301);
        if (is_array($data201) && is_array($data301)){
            $array1=null;
            $i=0;
            if (0==$data301['errno']){
                foreach ($data301['data'] as $key =>$val){
//                     if(strpos($val['name'],'市')){
//                         $city = str_replace('市','',$val['name']);
//                     }
                    $array1[$i]['area']=$val['area'];
                    $array1[$i]['name']=$val['name'];
                    $array1[$i]['district']=$val['district'];
    
                    $i++;
                }
            }
    
            if (0==$data201['errno']){
                foreach ($data201['data'] as $key =>$val){
                    //dump(array_search($val['district'],array_column($array1, 'district')));
                    if (array_search($val['district'],array_column($array1, 'district'))){
                        continue;
                    }
//                     if(strpos($val['name'],'市')){
//                         $city = str_replace('市','',$val['name']);
//                     }
                    $array1[$i]['area']=$val['area'];
                    $array1[$i]['name']=$val['name'];
                    $array1[$i]['district']=$val['district'];
                    $i++;
                }
            }
            //dump($array1);
            //dump($data201);
            $write=F('city',$array1);//,MODULE_PATH.'Conf/'
            dump($write);
            //dump(F('city','',MODULE_PATH.'Conf/'));
    
        }
    }
    
    public function get201city(){
        $params['client_id']=$this->AppKey;
        $params['access_token']=$this->access_token;
        $params['timestamp']=time();
        $params['rule']=201;
        $sign=$this->sign($params);
        $params['sign']=$sign;
        $cardata201=$this->carlevel($params);
        return json_decode($cardata201,true);
    }
    
    public function get301city() {
        $params['client_id']=$this->AppKey;
        $params['access_token']=$this->access_token;
        $params['timestamp']=time();
        $params['rule']=301;
        $sign=$this->sign($params);
        $params['sign']=$sign;
        $cardata301=$this->carlevel($params);
        return json_decode($cardata301,true);
    }
    
  
    
    
}

?>
