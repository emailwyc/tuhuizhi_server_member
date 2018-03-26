<?php
namespace Coupon\Controller;
/**
 * 小程序C端(营销平台4.0)
 * User: wutong
 * Date: 2017/8/10
 * Time: 上午11:47
 */

use Common\Controller\JaleelController;
use common\ServiceLocator;
use MerAdmin\Model\AppletCouponModel;
use Pingpp\Charge;
use Pingpp\Error\Base;
use Pingpp\Order;
use Pingpp\Pingpp;
use MerAdmin\Model\AppletCouponLogModel;
use MerAdmin\Model\AppletShopModel;

class AppletController extends JaleelController {

    protected $merchant;
    public $url = 'http://211.157.182.226:8080';//测试
    
    public function _initialize()
    {
        parent::_initialize();
        $this->merchant = $this->getMerchant($this->ukey);
        include_once './Class/pingpp-php/init.php';

        if(C('DOMAIN') == 'http://mem.rtmap.com' || C('DOMAIN') == 'https://mem.rtmap.com')//正式环境
        {
            $this->url = 'http://47.94.115.24';
        }
    }

    /**
     * 获取小程序title
     * http://localhost/member/index.php/Coupon/Applet/appletTitle?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function appletTitle(){
        
        $appletConfigService = ServiceLocator::getAppletConfigService();
        $data = $appletConfigService->getOnce($this->merchant['id']);
        
        if($data)
        {
            $msg = array('code'=>200,'data'=> $data);
        }
        else
        {
            $msg['code']=102;
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 首页获取店铺列表
     * http://localhost/member/index.php/Coupon/Applet/indexData?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function indexData(){
        $appletShopService = ServiceLocator::getAppletShopService();
        $appletCouponService = ServiceLocator::getAppletCouponService();
        $data = $appletShopService->getAll($this->merchant['id'], AppletShopModel::STATUS_0);//商户列表
        $coupon = $appletCouponService->getAll($this->merchant['id'], 0, AppletCouponModel::ONLINE_1);//首页全场券
        
        $msg = array('code'=>200,'data'=> array('shop' => $data, 'coupon' => $coupon));
    
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 搜索店铺
     * @param $name  店铺名
     * http://localhost/member/index.php/Coupon/Applet/searchShop?key_admin=202cb962ac59075b964b07152d234b70&name=清
     */
    public function searchShop(){
        $params['name'] = I('name');
        if(empty($params['name']))
        {
            $msg['code'] = 1030;
        }
        else
        {
            $appletShopService = ServiceLocator::getAppletShopService();
            $data = $appletShopService->getByName($params['name']);//商户列表
            
            $msg = array('code'=>200,'data'=> $data);
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 店铺详情
     * @param $shopId
     * http://localhost/member/index.php/Coupon/Applet/shopInfo?key_admin=202cb962ac59075b964b07152d234b70&shopId=70596
     */
    public function shopInfo(){
        $shopId = I('shopId');
        if(empty($shopId))
        {
            $msg['code'] = 1030;
        }
        else
        {
            $appletShopService = ServiceLocator::getAppletShopService();
            $appletCouponService = ServiceLocator::getAppletCouponService();
            $shopInfo = $appletShopService->getOnce($shopId);//商户信息
            $couponList = $appletCouponService->getAll($this->merchant['id'], $shopId, AppletCouponModel::ONLINE_1);//店铺中的优惠券
            
            $msg = array('code'=>200,'data'=> array('shopInfo' => $shopInfo, 'couponList' => $couponList));
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 优惠券详情
     * @param $couponId
     * http://localhost/member/index.php/Coupon/Applet/couponInfo?key_admin=202cb962ac59075b964b07152d234b70&couponId=1267
     */
    public function couponInfo(){
        $couponId = I('couponId');
        if(empty($couponId))
        {
            $msg['code'] = 1030;
        }
        else
        {
            $appletCouponService = ServiceLocator::getAppletCouponService();
            $coupon = $appletCouponService->getOnce($couponId);//优惠券

            $msg = array('code'=>200,'data'=> $coupon);
        }
    
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 领取卡券
     * @param $couponId
     * @param $couponActivityId
     * @param $openid
     * http://localhost/member/index.php/Coupon/Applet/drawCoupon?key_admin=202cb962ac59075b964b07152d234b70&couponId=1281&couponActivityId=BEgTsmAA&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function drawCoupon()
    {       
        $couponId = I('couponId');//券id
        $couponActivityId = I('couponActivityId');//活动id
        $openid = I('openid');
        $type = 1;//领取方式:1:微信openId 2:手机号
        
        if(empty($couponId) || empty($couponActivityId) || empty($openid))
        {
            $data['code'] = 1030;
        }
        else
        {
            //检查领取限制
            $appletCouponService = ServiceLocator::getAppletCouponService();
            $couponInfo = $appletCouponService->getOnce($couponId, $couponActivityId);
            
            if(empty($couponInfo))
            {
                $msg['code'] = 1035;
                echo returnjson($msg,$this->returnstyle,$this->callback);exit;
            }
            
            $appletCouponLogService = ServiceLocator::getAppletCouponLogService();
            //每日领取上限
            $dayParam['openid'] = $openid;
            $dayParam['couponActivityId'] = $couponActivityId;
            $dayParam['ctime'] = array('EGT', strtotime(date('Ymd')));
            $dayNum = $appletCouponLogService->getNum($dayParam);
            
            if($dayNum >= $couponInfo['daymax'])
            {
                $msg['code'] = 1599;
                echo returnjson($msg,$this->returnstyle,$this->callback);exit;
            }
            
            //累积领取上限
            $allParam['openid'] = $openid;
            $allParam['couponActivityId'] = $couponActivityId;
            $allNum = $appletCouponLogService->getNum($allParam);
            
            if($allNum >= $couponInfo['allmax'])
            {
                $msg['code'] = 1599;
                echo returnjson($msg,$this->returnstyle,$this->callback);exit;
            }
            
            $url = $this->url.'/rtmap-luck-web/api/coupon/get';
            $header = array('Content-Type:application/json');
            $postjson = json_encode(array('couponId' => $couponId, 'openId' => $openid, 'type' => $type, 'couponActivityId' => $couponActivityId));
            $curl = http($url, $postjson,'POST', $header, true);
            $arr = json_decode($curl, true);
            
            //记录领取日志
            if(!empty($arr) && $arr['status'] == 200)
            {
                $inster['adminid'] = $this->merchant['id'];
                $inster['openid'] = $openid;
                $inster['couponActivityId'] = $arr['data']['couponActivityId'];
                $inster['couponId'] = $arr['data']['couponId'];
                $inster['activityId'] = $arr['data']['activityId'];
                $inster['mainInfo'] = $arr['data']['mainInfo'];
                $inster['marketId'] = $arr['data']['marketId'];
                $inster['shopId'] = !empty($arr['data']['shopId']) ? $arr['data']['shopId'] : '';
                $inster['issuerName'] = $arr['data']['issuerName'];
                $inster['qrCode'] = '';
                $inster['type'] = AppletCouponLogModel::TYPE_0;
                $inster['ctime'] = time();
                
                $appletCouponLogService->add($inster);//领券日志
            }
            
            $data = array('code' => $arr['status'], 'msg' => $arr['message'], 'data' => $arr['data']);
        }
        
        returnjson($data,$this->returnstyle,$this->callback);
    }
    
    /**
     * 用户卡包
     * @param $openid
     * http://localhost/member/index.php/Coupon/Applet/cardInfo?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function cardInfo()
    {
        $openid = I('openid');
        
        if(empty($openid))
        {
            $data['code'] = 1030;
        }
        else
        {
            $url = $this->url.'/rtmap-coupon-web/api/instance/user/coupon/card';
            
            $curl = http($url, array('openId' => $openid));
            $arr = json_decode($curl, true);
            
            $data = array('code' => $arr['status'], 'msg' => $arr['message'], 'data' => $arr['data']);
        }
        
        returnjson($data,$this->returnstyle,$this->callback);
    }
    
    /**
     * 卡包详情接口
     * @param $openid
     * http://localhost/member/index.php/Coupon/Applet/cardDetail?key_admin=202cb962ac59075b964b07152d234b70&qrCode=33868035819832289
     */
    public function cardDetail()
    {
        $qrCode = I('qrCode');
    
        if(empty($qrCode))
        {
            $data['code'] = 1030;
        }
        else
        {
            $url = $this->url.'/rtmap-coupon-web/api/instance/card/detail';
    
            $curl = http($url, array('qrCode' => $qrCode));
            $arr = json_decode($curl, true);
    
            $data = array('code' => $arr['status'], 'msg' => $arr['message'], 'data' => $arr['data']);
        }
    
        returnjson($data,$this->returnstyle,$this->callback);
    }
    
    /**
     * 选券（展示在当前商户中可使用的优惠券）
     * @param $price
     * @param $shopid
     * @param $openid
     * http://localhost/member/index.php/Coupon/Applet/chooseCoupon?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE&price=9999&shopId=365435
     */
    public function chooseCoupon()
    {
        $openid = I('openid');
        $shopId = I('shopId');
        $price = I('price');//消费金额（单位分）
    
        if(empty($openid))
        {
            $data['code'] = 1030;
        }
        else
        {
            $url = $this->url.'/rtmap-coupon-web/api/instance/user/coupon/card';
            
            $curl = http($url, array('openId' => $openid));
            $arr = json_decode($curl, true);
            
            $coupon = array();
            if(!empty($arr['data']))
            {
                foreach ($arr['data'] as $k => $v)
                {
                    $couponApplyShopList = array();
                    if(!empty($v['couponApplyShopList']))
                    {
                        foreach ($v['couponApplyShopList'] as $k2 => $v2)
                        {
                            $couponApplyShopList[] = $v2['shopId'];
                        }
                    }
                    
                    if($v['status'] == 2)//已领取
                    {
                        if(empty($v['shopId']) || (!empty($v['shopId']) && in_array($shopId, $couponApplyShopList)))
                        {
                            //无消费门槛或者满足了消费门槛
                            if($v['conditionType'] == 0 || ($v['conditionType'] == 1 && $price >= $v['conditionPrice']))
                            {
                                $coupon[] = $v;
                            }
                        }
                    }
                }
            }
            
            $data = array('code' => $arr['status'], 'msg' => $arr['message'], 'data' => $coupon);
        }
        
        returnjson($data,$this->returnstyle,$this->callback);
    }
    
    /**
     * 带有券的支付订单创建(ping++)
     * @param $openid    openid
     * @param $amount    实际金额(单位元)
     * @param $mount     总金额(单位元)
     * @param $buildid   buildid
     * @param $channel   订单支付类型：wx_lite微信，支付宝，小程序等等
     * @param $key_admin 
     * @param $couponqr  券码
     * @param $shopname  店铺名
     * @param $shopid    店铺id
     * http://localhost/member/index.php/Coupon/Applet/createChargeCoupon?key_admin=ab09925ed498728aa4c70fdfd98b4e26&openid=oF8EK0fjBy6VHc9r-6SGZFSDShuU&mount=0.01&amount=0.01&channel=1&poiid=1&buildid=863200020020300002&couponqr=67639722528286996&channel=wx_lite&nickname=111
    */
    public function createChargeCoupon()
    {
        $params['openid'] = I('openid');
        $params['amount'] = (float)I('amount');//实际金额
        $params['mount'] = (float)I('mount');//总金额
        $params['buildid'] = I('buildid');
        $params['channel'] = I('channel');
        $params['key_admin'] = I('key_admin');
        
        if (in_array('', $params)) 
        {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        //支付总金额小于0，错误
        if ($params['amount'] <= 0 ) {
            returnjson(array('code'=>5001),$this->returnstyle,$this->callback);
        }
        
        $params['couponqr'] = I('couponqr');//券qr值
        $params['shopname'] = I('shopname');//商户名称
        $params['shopid'] = I('shopid');//商户id
        $params['nickname'] = I('nickname') ? I('nickname') : '';//昵称
        
        // 获取商户和此商户的pingxx配置信息
        $admininfo = $this->getMerchant($this->ukey);//商户信息
        $pingxxConfig = $this->getPingxxConfig($admininfo, $params['buildid']);
        
        $status = 0;
        //判断有没有传qr（要不要用券核销）,计算总金额减去券的面值后的实际支付金额
        if ($params['couponqr']){
            if ($params['amount'] > 0) {//订单总额大于0时，计算总额减去券的差
    
                //现获取券价值，以免核销后获取价值失败
                $url = $this->url.'/rtmap-coupon-web/api/instance/card/detail';
                $curl = http($url, array('qrCode'=>$params['couponqr']));
                if (!is_json($curl)){//请求接口错误
                    returnjson(array('code'=>101, 'data'=>$curl),$this->returnstyle,$this->callback);
                }
                $arr = json_decode($curl, true);
                
                if ($arr['status'] == 200){
                    $arr = $arr['data'];
                    $arr['main'] = $arr['main_info'];
                }else{
                    returnjson(array('code'=>101, 'data'=>$curl),$this->returnstyle,$this->callback);
                }
    
                //判断使用门槛
                if ($params['mount'] < $arr['conditionPrice']){
                    returnjson(array('code'=>1502, 'data'=>$curl),$this->returnstyle,$this->callback);
                }
    
                if (!$arr['price']){//如果营销平台没有返回抵扣券金额，则默认抵扣0，否则抵扣金额为实际券的抵扣金额，在此做一个判断
                    $arr['price'] =0;
                }

                //获取完券信息后，核销券
                $amount = round($params['amount']-(float)$arr['price'],2);
                if ($amount <= 0){
                    $appletCouponService = ServiceLocator::getAppletCouponService();
                    
                    $amount = 0;
                    $verifycoupon = $appletCouponService->verifyCoupon($this->url, $params['couponqr'], '', $params['openid'], $this->merchant['id']);
                    $status = 1;
                }
                $pingxx['amount'] = $amount * 100;//元换算为分

            }
    
            if ($params['amount'] == 0 ) {//且直接支付
                $pingxx['amount'] = $params['amount'];
    
            }
        }else{//如果没有传递qr值，直接拿总金额计算实际金额
            $pingxx['amount'] = $params['amount']*100;//将人民币元换为人民币分
            $arr['main'] = '购买商品';//给一个默认值，下面的pingxx的charge用
        }

        switch ($params['channel']) {
            case 'wx_lite':
                $extra = array(
                'open_id' => $params['openid']// 请求参数中的open_id
                );
                $pingxx['orderNO'] = date('YmdHis').substr(md5(time()), 0, 12);
                break;
            default:
                returnjson(array('code'=>1501),$this->returnstyle,$this->callback);
                break;
        }
        //获取商场信息
        $buildid_db=M('total_buildid');
        $buildid_arr=$buildid_db->where(array('buildid'=>array('eq',$params['buildid'])))->find();
    
        try {
            $metadata = array(
                'shopid'=>$params['shopid'],
                'key_admin'=>$params['key_admin'],
                'adminid'=>$this->merchant['id'],
                'buildid'=>$params['buildid'],
                'openid'=>$params['openid'],
                'orderno'=>$pingxx['orderNO'],
                'qrcode'=>$params['couponqr'] ? $params['couponqr'] : '',
                'payType'=> 'appletCoupon',//支付业务，营销平台4.0小程序
            );
            
            $arr['main'] = '小程序付款';
            
            /**
             * 数据信息存入数据库
            */
            $db = M('pingxx_pay', $admininfo['pre_table']);
            $data['main'] = $arr['main'];
            $data['mount'] = $params['mount'] * 100;//总金额
            $data['amount'] = $pingxx['amount'];//实付金额
            $data['couponprice'] = $arr['price'];//券面额
            $data['orderno'] = $pingxx['orderNO'];
            $data['openid'] = $extra['open_id'];
            $data['channel'] = $params['channel'];
            $data['currency'] = 'cny';
            $data['status'] = $status;
            $data['shopid']=$params['shopid'];
            $data['key_admin']=$params['key_admin'];
            $data['buildid']=$params['buildid'];
            $data['couponqr']=$params['couponqr'];//优惠券号
            $data['marketname']=$buildid_arr['name'];//商场名称
            $data['shopname']=$arr['issuerName'];//商户名称
            $data['datetime']=date('Y-m-d H-i-s',time());//支付时间
            $data['nickname'] = $params['nickname'];
            
            $add = $db->add($data);
            if ($pingxx['amount'] == 0) {
                returnjson(array( 'code'=>200, 'data'=>(int)0 ),$this->returnstyle,$this->callback);
            }
            
            /**
             * pingxx支付开始
             */
            Pingpp::setApiKey($pingxxConfig['live_secret_key']);// 设置 API Key，测试正式注意切换
            Pingpp::setPrivateKeyPath($pingxxConfig['private_key_path']);// 设置私钥
            
            $createArr = array(
                //请求参数字段规则，请参考 API 文档：https://www.pingxx.com/api#api-c-new
                'subject'   => $arr['main'] ? $arr['main'] : '',
                'body'      => $arr['main'],
                'amount'    => $pingxx['amount'],//订单总金额, 人民币单位：分（如订单总金额为 1 元，此处请填 100）
                'order_no'  => $pingxx['orderNO'],// 推荐使用 8-20 位，要求数字或字母，不允许其他字符
                'currency'  => 'cny',
                'extra'     => $extra,//https://www.pingxx.com/api#支付渠道-extra-参数说明
                'channel'   => $params['channel'],// 支付使用的第三方支付渠道取值，请参考：https://www.pingxx.com/api#api-c-new
                'client_ip' => $_SERVER['REMOTE_ADDR'],//$_SERVER['REMOTE_ADDR'],// 发起支付请求客户端的 IP 地址，格式为 IPV4，如: 127.0.0.1
                'app'       => array('id' => $pingxxConfig['appid']),
                'metadata'  => $metadata
            );
            
            $ch = Charge::create($createArr);
            $ch = json_decode($ch, true);

            returnjson(array('code'=>200, 'data'=>$ch, 'other'=>'where'),$this->returnstyle,$this->callback);
        }
        catch (Base $e) 
        {
            // 捕获报错信息
            if ($e->getHttpStatus() != null) 
            {
                header('Status: ' . $e->getHttpStatus());
                returnjson(array('code'=>104, 'data'=>$e->getHttpBody()),$this->returnstyle,$this->callback);
            } 
            else 
            {
                returnjson(array('code'=>104, 'data'=>$e->getMessage()),$this->returnstyle,$this->callback);
            }
        }
    }
    
    //http://localhost/member/index.php/Coupon/Applet/testVerifyCoupon?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE&qrcode=86933693333983838
    function testVerifyCoupon()
    {
        $openid = I('openid');
        $qrcode = I('qrcode');
        
        $appletCouponService = ServiceLocator::getAppletCouponService();
        $res = $verifycoupon = $appletCouponService->verifyCoupon($this->url, $qrcode, '', $openid);
        print_r($res);exit;
    }
    
    
    
}