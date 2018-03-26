<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 29/01/2018
 * Time: 17:58
 */

namespace HandsFreeShopping\Service;


use Common\Service\PublicService;
use Common\Service\RedisService;
use DPlatform\Service\DPlatformService;
use EnterpriseWechat\Service\Message\SendMessageService;
use EnterpriseWechat\Service\Users\getUserInfoService;
use Thirdwechat\Controller\Wechat\TemplateController;

class MarketService
{
    public static function getUserInfo($keyAdmin, $openid)
    {
        $data = DPlatformService::getDPlatformUserInfo($keyAdmin, $openid);
        if (isset($data['code'])){
            return $data;
        }
        //只有用户等级为1的时候才可以
        if (isset($data['roleType']) && $data['roleType'] == 1) {
            return $data;
        }
        return ['code'=>1003];
    }


    /**
     * 取出所有的提货点
     */
    public static function placeList($adminInfo, $id = false)
    {
        $db = M('shopping_place', $adminInfo['pre_table']);
        $sel = $db->select();
        foreach ($sel as $key => $value){
            RedisService::connectredis()->set('handsfreeshoppingplace:' . $adminInfo['ukey'] . ':' . $value['id'] , $value['name']);
        }
        if ($id != false) {
            return RedisService::connectredis()->get('handsfreeshoppingplace:' . $adminInfo['ukey'] . ':' . $id);
        }
    }


    /**
     * （全部）待取件，接单->（代取件）送达->（待收货）没有按钮
     * 商场端获取订单列表
     * @param $keyAdmin
     * @param $buildId |建筑物id
     * @param $floor | 楼层
     * @param $pickUpid | 自提点id
     * @param $dPlatformUserInfo | d客平台用户信息
     * @param bool $isQueryAll | 是否查看所有快递员的订单信息，true是全部，false是自己的
     * @param bool $orderStatus | 订单状态，接单、送达、待收货等等
     * @param $id| 订单列表主键id
     * @return array|bool|mixed
     */
    public static function getOrderList($keyAdmin, $buildId, $floor, $pickUpid, $dPlatformUserInfo, $isQueryAll = false, $orderStatus = false, $id = false)
    {
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])) {
            return $adminInfo;
        }
        $db = M('shopping_order', $adminInfo['pre_table']);

        $where = [
            'buildid'=>$buildId,
            'floor'=>$floor,
            'place_id'=>$pickUpid
        ];
        if ($orderStatus && is_array($orderStatus) && !empty($orderStatus)){
            $where['status'] = ['in', $orderStatus];//$orderStatus;
        }

        if ($isQueryAll === false) {
            $where['operator_gather'] = $dPlatformUserInfo['id'];
        }
        if ($id != false) {
            $where['id'] = $id;
        }
        $ordergoodstable = $adminInfo['pre_table'] . 'shopping_order_goods';
        $cartgoodstable = $adminInfo['pre_table'] . 'shopping_cart_goods';
        //符合条件的订单信息
        $data = $db->where($where)->select();
        if ($data){
            $goodDb = M('shopping_cart_goods', $adminInfo['pre_table']);
            foreach ($data as $key => $value) {
                if ($value['ordertype'] == 0) {
                    $orderGoods = $goodDb
                        ->join($ordergoodstable . ' on ' . $ordergoodstable . '.cart_good_id = ' . $cartgoodstable . '.id')
                        ->where(['orderid'=>$value['id']])
                        ->select();
                    $data[$key]['goods'] = $orderGoods;
                }
            }
            return $data;
        }else{
            return false;
        }
    }


    /**
     * 快递员更改订单状态
     * @param $keyAdmin
     * @param $orderId
     * @param $dPlatformUserInfo
     * @param $status
     * @return array|mixed
     */
    public static function changeOrderStatus($keyAdmin, $dPlatformUserInfo, $orderIds, $status)
    {
        foreach ($orderIds as $key => $val){
            if (!is_numeric($val) || !is_numeric($status) || $status >6 ) {//7是已完成，已完成是扫码修改，不允许按钮点击修改
                return ['code'=>1051];
            }
        }

        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])) {
            return $adminInfo;
        }
        $db = M('shopping_order', $adminInfo['pre_table']);
        $sel = $db->where(['id'=>['in', $orderIds]])->select();//, 'operator_gather'=>$dPlatformUserInfo['id']

        if ($sel) {
            foreach ($sel as $key => $val){
                if (($status - 1) != $val['status']){
                    return ['code'=>1051, 'data'=>'status error'];
                }else{
                    $saveData['status']=$status;
                    if ($status == 5) {
                        $saveData['operator_gather'] = $dPlatformUserInfo['id'];
                        $saveData['operator_gather_openid'] = $dPlatformUserInfo['openId'];

                        //发送微信模板消息
//                        $wechatMessageContent = '';//模板消息主体
//                        $wechat = new TemplateController();
//                        $sendMessage = $wechat->insideSendMessage($wechatMessageContent, $keyAdmin, $adminInfo['wechat_appid']);


                        //发送企业微信模板消息
//                        $userinfo = getUserInfoService::getUserInfo('zukepingtai', $adminInfo, $dPlatformUserInfo['openId']);//获取用户在企业微信的个人信息
//                        if ($userinfo['code']== 200){
//                            $messageContent = [
//                                'toparty' => implode('|', $userinfo['data']['department']),
//                                'msgtype'=>'text',
//                                'agentid'=>'agentid',
//                                'content'=>''//只需要在这里把消息主体写入即可发送
//                            ];
//                            SendMessageService::sendMessage('zukepingtai', false, $messageContent, $adminInfo);//按部门发送模板消息
//                        }

                    }
                    if ($status == 6) {
                        $place = RedisService::connectredis()->get('handsfreeshoppingplace:' . $adminInfo['ukey'] . ':' . $val['place_id']);
                        if (!$place) {
                            $place = self::placeList($adminInfo);
                        }
                        $wechatMessageContent=array(array(
                            'touser'=>$val['openid'],
                            'template_id'=>'F9xDicKuunf3Gl2yxIBHnnYWiRYcLpYlLQ45EGVem50',
                            'url'=>'',
                            'data'=>array(
                                'first'=>array('value'=>'您好，您的' .$val['store_name']. '店铺订单已送完' .$place. '自提点！ '),
                                'keyword1'=>array('value'=>$val['order_no']),
                                'keyword2'=>array('value'=>$val['store_name']),
                                'keyword3'=>array('value'=>date('Y-m-d H:i:s')),
                                'remark'=>array('value'=>'请凭取货码前往' .$place. '自提点取货！'),
                            )
                        ));

                        $wechat = new TemplateController();
                        $sendMessage = $wechat->insideSendMessage($wechatMessageContent, $keyAdmin, $adminInfo['wechat_appid']);
                    }
                    
                    $save = $db->where(['id'=>$val['id']])->save($saveData);//, 'operator_gather'=>$dPlatformUserInfo['id']

                    if ($save !== false){
                        return ['code'=>200];
                    }else{
                        return ['code'=>104];
                    }
                    
                }
            }

        }else{
            return ['code'=>102];
        }
    }


    /**
     * 顾客出示二维码，快递员扫码订单完成
     * @param $encryOrderId
     * @param $keyAdmin
     * @param $dPlatformUserInfo
     * @return array|mixed
     */
    public static function orderSuccessByScan($encryOrderId, $keyAdmin, $dPlatformUserInfo = false)
    {
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])) {
            return $adminInfo;
        }
        //解密订单号
        $orderId = decrypt_zht($encryOrderId);
        if (!$orderId) {
            return ['code'=>1815, 'data'=>'orderid error'];
        }

        $db = M('shopping_order', $adminInfo['pre_table']);
        $find = $db->where(['order_no'=>$orderId])->find();
        if ($find) {
            if (6 != $find['status']){
                return ['code'=>1815, 'data'=>'status error'];
            }else{
                $save = $db->where(['order_no'=>$orderId])->save(['status'=>7]);
                if ($save !== false){
                    $cartGoodsDb = M('shopping_cart_goods', $adminInfo['pre_table']);
                    $orderTableName = $adminInfo['pre_table'] . 'shopping_order_goods';//订单关联表名
                    $cartTableName = $adminInfo['pre_table'] . 'shopping_cart_goods';//购物车表名
                    $data = $cartGoodsDb->join(' join ' . $orderTableName .' on ' . $cartTableName . '.id = ' . $orderTableName . '.cart_good_id')->where(['orderid'=>$orderId])->select();
                    $find['status'] = 7;
                    $find['goodsInfo'] = $data;
                    $data = [
                        'code'=>200,
                        'data'=>$find,
                    ];


                    $place = RedisService::connectredis()->get('handsfreeshoppingplace:' . $adminInfo['ukey'] . ':' . $find['place_id']);
                    if (!$place) {
                        $place = self::placeList($adminInfo);
                    }
                    $wechatMessageContent=array(array(
                        'touser'=>$find['openid'],
                        'template_id'=>'F9xDicKuunf3Gl2yxIBHnnYWiRYcLpYlLQ45EGVem50',
                        'url'=>'',
                        'data'=>array(
                            'first'=>array('value'=>'您好，您的' .$find['store_name']. '订单已完成取货！  '),
                            'keyword1'=>array('value'=>$find['order_no']),
                            'keyword2'=>array('value'=>$find['store_name']),
                            'keyword3'=>array('value'=>date('Y-m-d H:i:s')),
                            'remark'=>array('value'=>'欢迎您下次光临！'),
                        )
                    ));

                    $wechat = new TemplateController();
                    $sendMessage = $wechat->insideSendMessage($wechatMessageContent, $keyAdmin, $adminInfo['wechat_appid']);



                    $findAdmin = M('total_admin')->where(['pid'=>$adminInfo['id']])->find();
                    if ($findAdmin && isset($findAdmin['wechat_appid'])){
                        $wechatMessageContentsh=array(array(
                            'touser'=>$find['operator_guide_openid'],
                            'template_id'=>'0rLZr_1busEKzCQlw2bzAoQgpMuU3e5FV39uVFRY0d0',
                            'url'=>'',
                            'data'=>array(
                                'first'=>array('value'=>'顾客订单已取货'),
                                'keyword1'=>array('value'=>$find['order_no']),
                                'keyword2'=>array('value'=>$find['store_name']),
                                'keyword3'=>array('value'=>date('Y-m-d H:i:s')),
                                'remark'=>array('value'=>'该订单已被顾客取货提走，订单已完成！'),
                            )
                        ));

                        $wechat = new TemplateController();
                        $sendMessage = $wechat->insideSendMessage($wechatMessageContentsh, $findAdmin['ukey'], $findAdmin['wechat_appid']);
                    }




                    return $data;
                }else{
                    return ['code'=>104];
                }
            }
        }else{
            return ['code'=>102];
        }
    }





}