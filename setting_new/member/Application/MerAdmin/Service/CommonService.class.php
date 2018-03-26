<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use Common\Controller\RedisController;
use common\ServiceLocator;

class CommonService{
    public $redis;
    
    public function __construct($pre_table)
    {
        $redis_con = new RedisController();
        $this->redis = $redis_con->connectredis();
    }
    
    /**
     * 按商户密钥查询商户配置信息
     * @param $key_admin 商户密钥
     * @return bool
     */
    public function getMerchant($key_admin)
    {
        if (!$key_admin) 
        {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        
        $m_info = $this->redis->get('member:'.$key_admin);
        
        if ($m_info)
        {
            return json_decode($m_info, true);
        }
        else 
        {
            $merchant = M('total_admin');
            $re = $merchant->where(array('ukey' => $key_admin))->find();
    
            if($re) 
            {
                $this->redis->set('member:' . $key_admin, json_encode($re),array('ex'=>86400));//一天
            }
            else
            {
                $data['code']=1001;
                echo returnjson($data,$this->returnstyle,$this->callback);exit();
            }
            
            return $re;
        }
    }
    
    /**
     * 获取admin单条配置
     */
    public function GetOneAmindefault($table_pre,$key_admin,$function_name)
    {
        if (!$key_admin) 
        {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        
        $default=$this->redis->get('admin:default:one:'.$function_name.':'. $key_admin);
        if($default)
        {
            return json_decode($default,true);
        }
        else
        {
            $dbm = M();
            $c = $dbm->execute('SHOW TABLES like "'.$table_pre.'default"');
            if (1 !== $c)
            {
                $sql="CREATE TABLE `".$table_pre."default`  (
                    `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
                      `customer_name` varchar(50) NOT NULL COMMENT '用途',
                      `function_name` text NOT NULL COMMENT '用途属性',
                      `description` varchar(150) DEFAULT '' COMMENT '描述',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='商户常量配置表'";
                $dbm->execute($sql);
                return null;
            }
            else
            {
                $db = M('default',$table_pre);
                $select = $db->where(array('customer_name'=>$function_name))->find();
                if ($select) 
                {
                    $this->redis->set('admin:default:one:'.$function_name.':'. $key_admin, json_encode($select),array('ex'=>86400));//一天
                }
                
                return $select;
            }
        }
    }
    
    /**
     * curl请求
     * POST数据为JSON数据
     * @param $url
     * @param $data_string
     * @return mixed
     */
    public function curl_json($url, $data_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        $curl_re = curl_exec($ch);
        curl_close($ch);
    
        return $curl_re;
    }
    
    /**
     * 微信支付签名
     * @param $data
     * @param $key
     * @return mixed
     */
    public function paySign($data, $key) 
    {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if ($v == '') { // 值为空不参与签名
                continue;
            }
    
            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }
    
        $str .= '&key=' . $key;
        $sign = strtoupper(md5($str));
        return $sign;
    }
    
    /**
     * 微信支付下单接口
     *
     */
    public function paybyweixin($key_admin, $openid, $amount, $body, $notify_url, $pre_table, $pay_class, $attach, $adminid = 0, $outsource_orderno = 0, $payType = 1)
    {
        if($amount == 0)
        {
            return array('code' => '200', 'msg' => 'SUCCESS!', 'data' => array());
        }
        
        // 验证key_admin
        $mer_chant = $this->getMerchant($key_admin);
        
        // 请求微信支付接口进行支付
        $post_arr['total_fee'] = $amount; // 单位分

        $post_arr['attach'] = urlencode(json_encode($attach));//回调参数urlencode(json_encode($attach));

        $post_arr['attach_transmit_tag'] = 'N';
        $post_arr['notify_url'] = $notify_url;
        $post_arr['body'] = $body;
        $post_arr['appid']   = $pay_class == "applet" ? $mer_chant['applet_appid'] : $mer_chant['wechat_appid'];
        $post_arr['wxa_tag'] = $pay_class == "applet" ? "Y" : "N";
        
        // 查询商户账号
        $def_re = $this->GetOneAmindefault($pre_table, $key_admin, 'public_pay_config');
        $sub_mich = json_decode($def_re['function_name'], true);
        
        if(!empty($sub_mich['publicmchid']))
        {
            $sub_mich = $sub_mich['publicmchid'];
        }
        else
        {
            $def_re = $this->GetOneAmindefault($pre_table, $key_admin, 'subpayacc');
            $sub_mich = $def_re['function_name'];
        }
        
        $post_arr['openid'] = $openid;
        
        $post_arr['sign'] = $this->paySign($post_arr, $mer_chant['signkey']);//8bf759b5ab8056e93f2f1620bc601170
        $url = "http://pay.rtmap.com/pay-api/v3/wx/{$sub_mich}/jsapi/prepay";//1390600002 1234667902
        
        $curl_re = $this->curl_json($url, json_encode($post_arr));
        
        writeOperationLog(array('请求微信支付接口参数' => json_encode($post_arr)), 'jaleel_logs');
        writeOperationLog(array('请求微信支付请求url' => $url), 'jaleel_logs');
        writeOperationLog(array('请求微信支付回调url' => $post_arr['notify_url']), 'jaleel_logs');
        writeOperationLog(array('请求微信支付接口' => $curl_re), 'jaleel_logs');
        
        $curl_arr = json_decode($curl_re, true);
        
        if ($curl_arr['status'] != 200) 
        {
            $data = array('code' => 1000, 'data'=>$curl_arr, 'msg' => $curl_arr['message'] ? $curl_arr['message'] : 'system error!'); 

            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        $return = $curl_arr['data'];
        $return['total_fee'] = $amount;
    
        if (isset($curl_arr['data']['timeStamp']))
        {
            $return['timeStamp'] = (string)$curl_arr['data']['timeStamp'];
        }
        else
        {
            $return['timeStamp'] = (string)time();
            $return['outTradeNo'] = $curl_arr['data']['ordId'];
        }
    
        $orderNo = $curl_arr['data']['outTradeNo'];
        
        // 插入定单
        $orderService = ServiceLocator::getOrderService();
        $orderService->add($amount, $openid, $curl_arr['data']['outTradeNo'], $adminid, $outsource_orderno, $payType);
        
        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $return);
        
        return $data;
    }
    
    
    /**
     * 最新微信支付接口
     *
     */
    public function paybyweixinvs2($key_admin, $openid, $amount, $body, $notify_url, $pre_table, $pay_class, $attach, $adminid = 0, $outsource_orderno = 0, $payType = 1,$zhihuitu_openid='')
    {
        if($amount == 0)
        {
            return array('code' => '200', 'msg' => 'SUCCESS!', 'data' => array());
        }
    
        // 验证key_admin
        $mer_chant = $this->getMerchant($key_admin);
    
        // 请求微信支付接口进行支付
        $post_arr['total_fee'] = $amount; // 单位分
    
        $post_arr['attach'] = urlencode(json_encode($attach));//回调参数urlencode(json_encode($attach));
    
        $post_arr['attach_transmit_tag'] = 'N';
        $post_arr['notify_url'] = $notify_url;
        $post_arr['body'] = $body;
        $post_arr['appid']   = $pay_class == "applet" ? $mer_chant['applet_appid'] : $mer_chant['wechat_appid'];
        $post_arr['wxa_tag'] = $pay_class == "applet" ? "Y" : "N";
    
        // 查询商户账号
        $def_re = $this->GetOneAmindefault($pre_table, $key_admin, 'public_pay_config');
        $sub_mich1 = json_decode($def_re['function_name'], true);
    
        if(!empty($sub_mich1['publicmchid']))
        {
            $sub_mich = $sub_mich1['publicmchid'];
        }
        else
        {
            $def_re = $this->GetOneAmindefault($pre_table, $key_admin, 'subpayacc');
            $sub_mich = $def_re['function_name'];
        }
    
        if($sub_mich1['publicsignkey'] == ''){
            $data = array('code' => 1000, 'data'=>'未设置签名key', 'msg' => '');
            
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        $sign_key = $sub_mich1['publicsignkey'];
        
        if($sub_mich1['publicismacc'] == 1){
            $post_arr['openid'] = $zhihuitu_openid;
        }else{
            $post_arr['openid'] = $openid;
        }
    
        $post_arr['sign'] = $this->paySign($post_arr, $sign_key);//8bf759b5ab8056e93f2f1620bc601170
        $url = "http://pay.rtmap.com/pay-api/v3/wx/{$sub_mich}/jsapi/prepay";//1390600002 1234667902
    
        $curl_re = $this->curl_json($url, json_encode($post_arr));
    
        writeOperationLog(array('请求微信支付接口参数' => json_encode($post_arr)), 'jaleel_logs');
        writeOperationLog(array('请求微信支付请求url' => $url), 'jaleel_logs');
        writeOperationLog(array('请求微信支付回调url' => $post_arr['notify_url']), 'jaleel_logs');
        writeOperationLog(array('请求微信支付接口' => $curl_re), 'jaleel_logs');
    
        $curl_arr = json_decode($curl_re, true);
    
        if ($curl_arr['status'] != 200)
        {
            $data = array('code' => 1000, 'data'=>$curl_arr, 'msg' => $curl_arr['message'] ? $curl_arr['message'] : 'system error!');
    
            returnjson($data, $this->returnstyle, $this->callback);
        }
    
        $return = $curl_arr['data'];
        $return['total_fee'] = $amount;
    
        if (isset($curl_arr['data']['timeStamp']))
        {
            $return['timeStamp'] = (string)$curl_arr['data']['timeStamp'];
        }
        else
        {
            $return['timeStamp'] = (string)time();
            $return['outTradeNo'] = $curl_arr['data']['ordId'];
        }
    
        $orderNo = $curl_arr['data']['outTradeNo'];
    
        // 插入定单
        $orderService = ServiceLocator::getOrderService();
        $orderService->add($amount, $openid, $curl_arr['data']['outTradeNo'], $adminid, $outsource_orderno, $payType);
    
        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $return);
    
        return $data;
    }
    
    
    /**
     * 支付回调通知接口(用于微信支付成功后的回调)
     * @return array
     * @throws \Exception
     */
    public function confirmPay($par_arr, $attach) {
        /**
         * 此处接收的是json字符串
         * 注意不能使用TP中的I函数
         * 因为会被转义
         * 转义后无法使用json_decode函数转换成数组
         */
        
        $orderNo = $par_arr['out_trade_no'];
        
        $orderService = ServiceLocator::getOrderService();
        $order_info = $orderService->getOnceByOrderNo($orderNo);
        
        if (empty($order_info) || $order_info['status'] == 1) 
        {
            return false;
        }
    
        // 更新定单状态为支付成功状态
        $order_info['status'] = 1;
        $up_re = $orderService->updateById($orderNo, $order_info);
        
//         if ($up_re === false) 
//         {
//             return false;
//         }
        
        return $order_info;
    }
    
    //微信支付成功后领券
    public function getPrize($activityId, $openid, $pid, $cardno, $main, $pre_table){
        $userCardService = ServiceLocator::getUserCardService();
        
        $url2 = 'http://101.201.176.54/rest/act/prize/'.$activityId.'/'.$pid.'/'.$openid;//领券接口
        $act_arr = http($url2,array());
        $act_res = json_decode($act_arr,true);
        
        writeOperationLog($act_res, 'wechat');
        
        if($act_res['code'] == 0)
        {
            //领券成功写入日志
            $userCardService->log_integral($activityId, $cardno, 0, $main,'F', $pre_table, '', $openid, $pid, $act_res['qr']);
        }
        
        return $act_res;
    }
    
    //微信支付成功后ERP领券
    public function getErpPrize($activityId, $openid, $pid, $cardno, $main, $pre_table, $signkey){
        $userCardService = ServiceLocator::getUserCardService();
        
        $url = C('DOMAIN') . '/ErpService/Erpoutput/prize_exchange';
        $erp_params['key_admin'] = $this->key_admin;
        $erp_params['cardno'] = $cardno;
        $erp_params['pid'] = $pid;
        $erp_params['activity'] = $activityId;
        $erp_params['sign_key'] = $signkey;
        $erp_params['sign'] = sign($erp_params);
        unset($erp_params['sign_key']);
        $erp_arr = json_decode(http($url,$erp_params),true);//处理返回结果
        
        writeOperationLog($erp_arr, 'wechat');
        
        if($erp_arr['code'] == 200)
        {
            $msg['code']=200;
            $userCardService->log_integral($activityId, $cardno, 0, $main, 'F', $pre_table,'',$openid,$pid);
        }
        else
        {
            $userCardService->log_integral($activityId, $cardno, 0, $main, 'M', $pre_table,'',$openid,$pid);
            $msg=$erp_arr;
        }
        
        return $erp_arr;
    }
    
    //营销平台数据库实例
    public function db_connect(){
        if(C('DOMAIN') == 'http://localhost/member/index.php' ){
            $connection = array(
                'db_type'    =>   'mysql',
                'db_host'    =>   '10.10.11.47',
                'db_user'    =>   'rtmap',
                'db_pwd'     =>   'rtmap911',
                'db_port'    =>    3306,
                'db_name'    =>    'promo3-full',
            );
        }else{
            //正式
            $connection = array(
                'db_type'    =>   'mysql',
                'db_host'    =>   'rdsbu5ogq3pvu740c9c9.mysql.rds.aliyuncs.com',
                'db_user'    =>   'luck3_read',
                'db_pwd'     =>   '123456A',
                'db_port'    =>    3306,
                'db_name'    =>    'promo',
            );
        }
    
        $db = M('prize_instance','shake_', $connection);//实例化营销平台记录表
        return $db;
    }

    
    //营销平台数据库实例4.0
    public function db_connect4(){
        if(C('DOMAIN') == 'http://localhost/member/index.php' ){
            $connection = array(
                'db_type'    =>   'mysql',
                'db_host'    =>   '10.10.11.47',
                'db_user'    =>   'rtmap',
                'db_pwd'     =>   'rtmap911',
                'db_port'    =>    3306,
                'db_name'    =>    'rts_1.1.0',
            );
        }else{
            //正式
            //等待营销平台提供MySQL地址
            $connection = array(
                'db_type'    =>   'mysql',
                'db_host'    =>   'rm-2zen60usm7mg8p7ld.mysql.rds.aliyuncs.com',
                'db_user'    =>   'rts_read',
                'db_pwd'     =>   'rtmap2017123',
                'db_port'    =>    3306,
                'db_name'    =>    'rts_1.1.0',
            );
//             $connection = array(
//                 'db_type'    =>   'mysql',
//                 'db_host'    =>   'rdsbu5ogq3pvu740c9c9.mysql.rds.aliyuncs.com',
//                 'db_user'    =>   'luck3_read',
//                 'db_pwd'     =>   '123456A',
//                 'db_port'    =>    3306,
//                 'db_name'    =>    'promo',
//             );
        }
    
        $db = M('coupon_instance','rts_', $connection);//实例化营销平台记录表
        return $db;
    }
    
    //王府中环，根据活动id获取活动信息
    public function self_trip_detail($url, $key_admin, $openid, $id){
        $url = $url."/marketweb/actionweb/selectById";
        $arr['keyAdmin'] = $key_admin;
        $arr['openid'] = $openid;
        $arr['id']  = $id;
        
        $return = json_decode(http($url,$arr), true);//处理返回结果
        
        return $return;

    }
    
    //王府中环，根据店铺id获取店铺信息
    public function shop_detail($url, $key_admin, $buildId, $openid, $poiId){
        $url = $url."/marketweb/mappoiweb/queryPoi_detail";
        $arr['keyAdmin'] = $key_admin;
        $arr['buildId'] = $buildId;
        $arr['openid'] = $openid;
        $arr['poiId']  = $poiId;//楼层
    
        $return = json_decode(http($url,$arr), true);//处理返回结果
    
        return $return;
    }

    
    /**
     * 微信支付下单接口（新，支持子账号）
     *
     */
    public function newPaybyweixin($key_admin, $openid, $amount, $body, $notify_url, $pre_table, $pay_class, $attach, $adminid = 0, $outsource_orderno = 0, $childopenid, $payType = 1)
    {
        if($amount == 0)
        {
            return array('code' => '200', 'msg' => 'SUCCESS!', 'data' => array());
        }
    
        // 验证key_admin
        $mer_chant = $this->getMerchant($key_admin);
    
        // 请求微信支付接口进行支付
        $post_arr['total_fee'] = $amount; // 单位分
        $post_arr['attach'] = urlencode(json_encode($attach));//回调参数
        $post_arr['attach_transmit_tag'] = 'N';
        $post_arr['notify_url'] = $notify_url;
        $post_arr['body'] = $body;
        $post_arr['appid']   = $pay_class == "applet" ? $mer_chant['applet_appid'] : $mer_chant['wechat_appid'];
        $post_arr['wxa_tag'] = $pay_class == "applet" ? "Y" : "N";
    
        // 查询子商户账号
        $def_re = $this->GetOneAmindefault($pre_table, $key_admin, 'public_pay_config');
        $sub_mich = json_decode($def_re['function_name'], true);
        
        //支付配置信息为空
        if(empty($sub_mich['publicmchid']))
        {
            $data = array('code' => 1501, 'data'=>'');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        if($sub_mich['publicismacc'] == 1)//使用子账号时使用智慧图openid
        {
            $post_arr['appid'] = 'wxf3a057928b881466';
            $post_arr['openid'] = $childopenid;
        }
        else
        {
            $post_arr['openid'] = $openid;
        }
        
        $post_arr['sign'] = $this->paySign($post_arr, $mer_chant['signkey']);//8bf759b5ab8056e93f2f1620bc601170
        $url = "http://pay.rtmap.com/pay-api/v3/wx/{$sub_mich['publicmchid']}/jsapi/prepay";//1390600002 1234667902
    
        $curl_re = $this->curl_json($url, json_encode($post_arr));

        writeOperationLog(array('请求微信支付接口参数' => json_encode($post_arr)), 'jaleel_logs');
        writeOperationLog(array('请求微信支付请求url' => $url), 'jaleel_logs');
        writeOperationLog(array('请求微信支付回调url' => $post_arr['notify_url']), 'jaleel_logs');
        writeOperationLog(array('请求微信支付接口' => $curl_re), 'jaleel_logs');
    
        $curl_arr = json_decode($curl_re, true);

        if ($curl_arr['status'] != 200)
        {
            $data = array('code' => 1000, 'data'=>$curl_arr, 'msg' => $curl_arr['message'] ? $curl_arr['message'] : 'system error!'); 
            returnjson($data, $this->returnstyle, $this->callback);
        }
    
        $return = $curl_arr['data'];
        $return['total_fee'] = $amount;
    
        if (isset($curl_arr['data']['timeStamp']))
        {
            $return['timeStamp'] = (string)$curl_arr['data']['timeStamp'];
        }
        else
        {
            $return['timeStamp'] = (string)time();
            $return['outTradeNo'] = $curl_arr['data']['ordId'];
        }
    
        $orderNo = $curl_arr['data']['outTradeNo'];
    
        // 插入定单
        $orderService = ServiceLocator::getOrderService();
        $orderService->add($amount, $openid, $curl_arr['data']['outTradeNo'], $adminid, $outsource_orderno, $payType);
    
        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $return);
    
        return $data;
    }
    
    
    
    
    
    
    
}

?>