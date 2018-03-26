<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 29/01/2018
 * Time: 18:05
 */

namespace HandsFreeShopping\Controller;


use Common\Controller\CommonController;
use EnterpriseWechat\Service\Users\getUserInfoService;
use HandsFreeShopping\Service\MarketService;

class MarketController extends CommonController
{
    private $dPlatformUserInfo;//商场人员信息

    public function _initialize()
    {
        parent::__initialize();

        //获取openid信息并判断是否有权限
        $userInfo = MarketService::getUserInfo($this->ukey, $this->user_openid);
        if (isset($userInfo['code'])) {
            returnjson($userInfo,$this->returnstyle,$this->callback);
        }
        $this->dPlatformUserInfo = $userInfo;
    }


    /**
     * 扫码：客户出示二维码，扫码确认收货
     */
    public function scanCustomerCode()
    {
        $params['code'] = I('code');
        $params['code'] = urldecode($params['code']);
        if (in_array('', $params)) {
            returnjson(['code'=>1030],$this->returnstyle,$this->callback);
        }
        $data = MarketService::orderSuccessByScan($params['code'], $this->ukey, $this->dPlatformUserInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 商场"快递员"和"派送员"订单页面
     * 接收的status参数说明：1点击支付按钮，2支付下单接口请求成功，3支付成功，4店铺打包完成（全部，状态显示待取件，按钮显示接单），5接单状态（待取件，状态待取件，按钮显示送达），6送货员取到货（待收货，在途中，没有按钮），7已完成
     */
    public function orderList()
    {
        $params['key_admin'] = $this->ukey;
        $params['openid'] = $this->user_openid;
        $params['buildId'] =I('buildid');//建筑物id
        $params['floor'] = I('floor');//楼层
        $params['pickupid'] = I('pickup');//自提点
        $params['isqueryall'] = I('getall');//是否查看所有人
        if (in_array('', $params)) {
            returnjson(['code'=>1030],$this->returnstyle,$this->callback);
        }
        if (!is_numeric($params['isqueryall']) || !in_array($params['isqueryall'], ['1', '2'])) {
            returnjson(['code'=>1051],$this->returnstyle,$this->callback);
        }
        if ($params['isqueryall'] == 1) {
            $isQueryAll = true;
        }else{
            $isQueryAll = false;
        }

        $params['status'] = I('status') ? I('status') : false;//状态，非必填，不填查所有
        $params['id'] = I('id') ? I('id') : false;//主键id

        $data = MarketService::getOrderList($params['key_admin'], $params['buildId'], $params['floor'], $params['pickupid'], $this->dPlatformUserInfo, $isQueryAll,  $params['status'], $params['id']);

        if ($data){
            returnjson(['code'=>200, 'data'=>$data],$this->returnstyle,$this->callback);
        }else{
            returnjson(['code'=>102],$this->returnstyle,$this->callback);
        }
    }


    /**
     * 快递员更改订单状态
     */
    public function changeOrderStatus()
    {
        $params['orderid'] = I('orderid');
        $params['status'] = I('status');
        if (in_array('', $params) || !is_array($params['orderid'])) {
            returnjson(['code'=>1030],$this->returnstyle,$this->callback);
        }
        $data = MarketService::changeOrderStatus($this->ukey, $this->dPlatformUserInfo, $params['orderid'], $params['status']);
        returnjson($data,$this->returnstyle,$this->callback);
    }







}