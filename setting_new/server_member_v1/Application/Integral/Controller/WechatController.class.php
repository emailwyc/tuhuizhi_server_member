<?php
/**
 * Created by EditPlus.
 * User: wutong
 * Date: 2017/8/15
 * Time: 11:43 AM
 */

namespace Integral\Controller;

use common\ServiceLocator;
use PublicApi\Service\CouponService;
use Common\Controller\CommonController;
class WechatController extends CommonController
{
    public function _initialize()
    {
        parent::__initialize();
    }
    /**
     * 微信支付回调
     * @param $key_admin $pid $main $openid
     * @return mixed
     * localhost/member/index.php/Integral/Wechat/confirmPay?key_admin=202cb962ac59075b964b07152d234b70&pid=170067&openid=oWm-rt658BqthlmdaWd4I20L871Y&main=优惠券2&payType=1&activity=18675
     */
    public function confirmPay(){
        $content = file_get_contents("php://input");
        $par_arr = json_decode($content, true);
        $attach = json_decode(urldecode($par_arr['attach']), true);
    
        writeOperationLog($attach, 'wechat');
    
        $commonService = ServiceLocator::getCommonService();
        $res = $commonService->confirmPay($par_arr, $attach);
        
        writeOperationLog(array('res'=>$res), 'wechat');
        
        //发券
        if($res)
        {
            if($attach['status'] == 'ZHT_YX')
            {
                $coupon_data = $this->GetOneAmindefault($attach['pre_table'],$attach['key_admin'],'coupon_default');
                $write['coupon_data'] = $coupon_data['function_name'];
                if($coupon_data['function_name'] == 2 ){
//                     $coupon_return = CouponService::giveCouponCheck($attach['pid'],$attach['activityId'],$attach['openid'],$attach['couponID'],1,'','');
                    $coupon_return = CouponService::giveCoupon($attach['pid'],$attach['openid'],$attach['couponID'],1,'','');
                    $write['coupon_return'] = $coupon_return;
                    if($coupon_return['code'] == 200){
                        $return['code'] = 0;
                        $userCardService = ServiceLocator::getUserCardService();
                        $userCardService->log_integral($attach['activityId'], $attach['cardno'], 0, $attach['name'],'F',$attach['pre_table'],'',$attach['openid'],$attach['pid'],$coupon_return['data']['qrCode']);
                    }else{
                        $return['code'] = 1082;
                        $return['message'] = $coupon_return['msg'];
                    }
                }else{
                    $return = $commonService->getPrize($attach['activityId'], $attach['openid'], $attach['pid'], $attach['cardno'], $attach['name'], $attach['pre_table']);
                }
                $write['return_status'] = $return;
                writeOperationLog($write, 'wechat');
            }
            elseif($attach['status'] == 'ERP_YX')
            {
                $return = $commonService->getErpPrize($attach['activityId'], $attach['openid'], $attach['pid'], $attach['cardno'], $attach['name'], $attach['pre_table'], $attach['signkey']);
            }
            
            //微信退款
            if($return['code'] != 0 && $res['paytype'] == 2)
            {
                $merchant = M('total_admin');
                $adminInfo = $merchant->where(array('id' => $res['admin_id']))->find();
                
                // 查询商户账号
                $def_re = $commonService->GetOneAmindefault($adminInfo['pre_table'], $adminInfo['ukey'], 'public_pay_config');
                $sub_mich = json_decode($def_re['function_name'], true);
                
                if(!empty($sub_mich['publicmchid']))
                {
                    $sub_mich = $sub_mich['publicmchid'];
                }
                else
                {
                    $def_re = $commonService->GetOneAmindefault($adminInfo['pre_table'], $adminInfo['ukey'], 'subpayacc');
                    $sub_mich = $def_re['function_name'];
                }

                $post_arr['out_trade_no'] = (string)$res['orderno'];
                $post_arr['refund_fee'] = (int)$res['total_fee'];
                $post_arr['sign'] = $commonService->paySign($post_arr, $adminInfo['signkey']);
                $url = "http://pay.rtmap.com/pay-api/v3/wx/{$sub_mich}/refund";

                $curl_re = $commonService->curl_json($url, json_encode($post_arr));

                writeOperationLog(array('请求微信退款接口参数' => json_encode($post_arr)), 'testwechat');
                writeOperationLog(array('请求微信退款请求url' => $url), 'testwechat');
                writeOperationLog(array('请求微信退款接口' => $curl_re), 'testwechat');

            }else{
                $activityPropertyNewService = ServiceLocator::getActivityPropertyNewService();
                $activityPropertyNewService->updateIssue($attach['pid']);
            }
        }
    }
    
    /**
     * 王府中环微信支付回调
     * @param $key_admin $pid $main $openid
     * @return mixed
     * localhost/member/index.php/Integral/Wechat/palaceConfirmPay?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function palaceConfirmPay(){
        $content = file_get_contents("php://input");
        $par_arr = json_decode($content, true);
        $attach = json_decode(urldecode($par_arr['attach']), true);
    
        writeOperationLog($par_arr, 'wechat');

        $commonService = ServiceLocator::getCommonService();
        $res = $commonService->confirmPay($par_arr, $attach);

        //支付成功
        if($res)
        {
            $db = M('total_admin');
            $admin = $db->where(array('id' => $res['admin_id']))->find();
            
            if($admin)
            {
                //通知外包支付成功回调
                $url = "https://memo.rtmap.com/marketweb/actionweb/finishpay";
                $arr['keyAdmin'] = $admin['ukey'];
                $arr['orderId']  = $res['outsource_orderno'];//外包订单id
                $arr['outtradeno']   = $res['orderno'];//微信订单id
                $arr['paytype']   = $res['paytype'];//微信订单id
                
                $return = json_decode(http($url,$arr), true);//处理返回结果
            }
        }
    }
    
}
