<?php
namespace MerAdmin\Service;
use Common\core\Singleton;
use MerAdmin\Model\ActivityModel;
use common\ServiceLocator;
use MerAdmin\Model\AppletCouponLogModel;

//小程序券
class AppletCouponService{
    
    /** 
     * AppletCouponModel对象
     *
     * @var MerAdmin\Model\AppletCouponModel
     */
    public $applet_coupon_model;//小程序商户model
    
    public function __construct()
    {
        $this->applet_coupon_model = Singleton::getModel('MerAdmin\\Model\\AppletCouponModel');
    }
    
    /**
     * 核销券接口
     */
    public function verifyCoupon($url, $qrcode = '', $shopId = '', $openId = '', $adminid = 0)
    {
        $url = $url.'/rtmap-coupon-web/api/writeoff/coupon/common/writeoff';
        
        $data = array('qrCode' => $qrcode, 'writeOffChannel' => '皇庭小程序', 'shopId'=> $shopId, 'openId' => $openId);
        $postjson = json_encode($data);
        $header = array('Content-Type:application/json');
         
        $res = http($url, $postjson, 'POST', $header, true);
        
        if (is_json($res))
        {
            $array = json_decode($res, true);
            if ($array['status'] == 200)
            {
                $appletCouponLogService = ServiceLocator::getAppletCouponLogService();
                $inster['adminid'] = $adminid;
                $inster['openid'] = $openId;
                $inster['couponActivityId'] = '';
                $inster['couponId'] = '';
                $inster['activityId'] = '';
                $inster['mainInfo'] = '';
                $inster['marketId'] = '';
                $inster['shopId'] = $shopId;
                $inster['issuerName'] = '';
                $inster['qrCode'] = $qrcode;
                $inster['type'] = AppletCouponLogModel::TYPE_1;
                $inster['ctime'] = time();
                
                $appletCouponLogService->add($inster);//核销日志
                
                return $array;
            }
            else
            {
                return array('code'=>1500);
            }
        }
        else
        {
            return array('code'=>101, 'data'=>$res);
        }
    }
    
    /**
     * 获取小程序券信息
     *
     * @param int $adminid
     * @param int $shopid
     * @return
     */
    public function getAll($adminid, $shopid, $online = 0)
    {
        $arr = $this->applet_coupon_model->getAll($adminid, $shopid, $online);
    
        return $arr;
    }
    
    /**
     * 获取一条小程序券信息
     *
     * @param int $couponId
     * @param int $couponActivityId
     * @return
     */
    public function getOnce($couponId, $couponActivityId = '')
    {
        $arr = $this->applet_coupon_model->getOnce($couponId, $couponActivityId);
    
        return $arr;
    }
    
    /**
     * 插入一条小程序券信息
     *
     * @param array $insert
     * @return int
     */
    public function add($insert)
    {
        $lastid = $this->applet_coupon_model->add($insert);
    
        return $lastid;
    }
    
    /**
     * 更新一条小程序券信息
     *
     * @param int $id
     * @param arr $arr
     * @return
     */
    public function updateById($id, $arr)
    {
        $this->applet_coupon_model->updateById($id, $arr);
    
        return $id;
    }
}
?>