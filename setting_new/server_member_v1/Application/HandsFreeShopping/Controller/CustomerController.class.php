<?php
namespace HandsFreeShopping\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
use Pay\Service\PayApiService;
use ErpService\Service\ErpCommonService;

use EnterpriseWechat\Service\Message\SendMessageService;
use Thirdwechat\Controller\Wechat\TemplateController;

class CustomerController extends ClientCommonController
{
    public function _initialize(){
        parent::_initialize();
        //$params = $this->params;
        $this->curtime = time();
        //订单状态
        $this->statusArr = array('0'=>array(3,4,5,6,7),'1'=>array(3,4,5),'2'=>array(6),'3'=>array(7));
        $this->statusArrMatch = array('3'=>1,'4'=>1,'5'=>1,'6'=>2,'7'=>3);
    }

    /**
     * 通过openid获取会员信息
     * @param $prefix
     * @param $openid
     * @return mixed
     */
    protected function checkUserExists($prefix, $openid) {
        $user = M('mem', $prefix);
        $re = $user->where(array('openid' => $openid))->find();
        return $re;
    }


    //扫一扫得到订单商品数量
    public function getGoodsNum() {
        $params = $this->params;
        $this->emptyCheck($params,array('cart_id','openid'));
        $cart_id = (int)decrypt_zht($params['cart_id']);
        $db = M('shopping_cart_goods', $this->setting['pre_table']);
        $db1 = M('shopping_status', $this->setting['pre_table']);
        $db2 = M('shopping_cart', $this->setting['pre_table']);
        //check orderid
        $userInfo = $this->checkUserExists($this->setting['pre_table'],$params['openid']);
        $orderInfo = $db2->where(array('id'=>$cart_id))->find();
        if(!$orderInfo || $userInfo['cardno']!=$orderInfo['cardno']){
            returnjson(array('code'=>1082,'msg'=>"卡号不匹配，请联系店员！"), $this->returnstyle, $this->callback);
        }
        //返回商品数量
        $where = array('cart_id'=>$cart_id,'status'=>0);
        $count = $db->field("id,num")->where($where)->select();
        if(count($count)<=0){
            returnjson(array('code'=>1082,'msg'=>"该二维码已经使用，不可重复使用！"), $this->returnstyle, $this->callback);
        }
        //更新状态
        $db->where($where)->save(array('status'=>1));
        $db2->where(array('id'=>$cart_id))->save(array('scantime'=>$this->curtime,'openid'=>$params['openid']));
        //$db1->add(array('order_id'=>$cart_id,'status'=>1));
        //更新数量from zhanghang
        //$db3->where(array("poi_id"=>$orderInfo['store_id'],"member_cardno"=>$orderInfo['cardno']))->save(array('shop_num'=>0));
        $count1 = 0;
        foreach ($count as $v){
            $count1+=$v['num'];
        }
        $msg = array('code'=>200,'data'=>$count1);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    //获取所有自提点
    public function getPlace() {
        $params = $this->params;
        $db = M('shopping_place', $this->setting['pre_table']);
        $placeInfo = $db->field("id,name")->select();
        $msg = array('code'=>200,'data'=>$placeInfo);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    //获取购物车里面的商品（默认两个小时）
    public function getShopCarGoods() {
        $params = $this->params;
        $this->emptyCheck($params,array('openid'));
        $db = M('shopping_cart_goods', $this->setting['pre_table']);
        $db1 = M('shopping_status', $this->setting['pre_table']);
        $db2 = M('shopping_cart', $this->setting['pre_table']);
        $timelimit = $this->curtime-86400*30;
        //查询该客户两个小时内订单(目前取出近1个月的，最多100个订单，购物车无分页)
        $where = array('openid'=>$params['openid'],'scantime'=>array('egt',$timelimit));
        $fields = "id,openid,store_name,scantime";
        $orderList = $db2->field($fields)->where($where)->order("scantime desc")->limit(0,100)->select();
        $orderids = ArrKeyAll($orderList,'id',false);
        if(empty($orderids)){
            returnjson(array('code'=>200,'data'=>array()), $this->returnstyle, $this->callback);
        }
        $orderList = ArrKeyFromId($orderList,'id');
        //根据订单查询订单详情
        $where1 = array('cart_id'=>array('in',$orderids),'status'=>1);
        $orderRecord = $db->where($where1)->order("id desc")->select();
        if(empty($orderRecord)){
            returnjson(array('code'=>200,'data'=>array()), $this->returnstyle, $this->callback);
        }
        //组装整理订单数据
        foreach ($orderRecord as $k=>$v){
            
            $v['goods_image'] = json_decode($v['goods_image'],true);
            $orderList[$v['cart_id']]['record'][] = $v;
            $orderList[$v['cart_id']]['remaintime'] = $this->curtime-$orderList[$v['cart_id']]['scantime'];
            $orderList[$v['cart_id']]['goods_image'] = $v['goods_image'];
        }
        $orderList = ArrObjChangeList($orderList);
        returnjson(array('code'=>200,'data'=>$orderList), $this->returnstyle, $this->callback);
    }

    //删除购物车物品
    public function delShopCarGoods() {
        $params = $this->params;
        $this->emptyCheck($params,array('cart_goods_id'));
        $db = M('shopping_cart_goods', $this->setting['pre_table']);
        $db->where(array("id"=>(int)$params['cart_goods_id'],"status"=>1))->delete();
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    ////更新购物车上的自提点
    public function updateCartGoodsPlace() {
        $params = $this->params;
        $this->emptyCheck($params,array('cart_id_list','place_id'));
        $db = M('shopping_cart', $this->setting['pre_table']);
        $db1 = M('shopping_place', $this->setting['pre_table']);
        //检查是否有该自提点
        $placeInfo = $db1->where(array("id"=>$params['place_id']))->find();
        if(empty($params['cart_id_list'])){
            returnjson(array('code'=>1082,'msg'=>"订单ID不可为空！"), $this->returnstyle, $this->callback);
        }
        if(empty($placeInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找到该自提点！"), $this->returnstyle, $this->callback);
        }
        $db->where(array("id"=>array('in',$params['cart_id_list'])))->save(array("place_id"=>$params['place_id']));
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    ////获取支付类型和优惠券活动id配置
    public function getPayType() {
        $params = $this->params;
        $this->emptyCheck($params,array('classes'));
        if($params['classes']=="other") {
            $other_pay_config = $this->GetOneAmindefault($this->setting['pre_table'], $params['key_admin'], "other_pay_config");
            if (empty($other_pay_config['function_name'])) {
                returnjson(array('code' => 1082, 'msg' => "还未设置支付帐号！"), $this->returnstyle, $this->callback);
            }
            $conf = json_decode($other_pay_config['function_name'], true);
            $paytype = $conf['otherismacc'];
        }
        //后续可以添加其他类型

        //获取优惠券配置活动id
        $actid = $this->GetOneAmindefault($this->setting['pre_table'], $params['key_admin'], "handsfreeshopping_coupon");
        $actid = empty($actid['function_name'])?array():json_decode($actid['function_name'],true);

        returnjson(array('code'=>200,'data'=>$paytype,'handsfreeshopping_coupon'=>$actid), $this->returnstyle, $this->callback);
    }
    ////下单微信支付
    public function paybyweixin() {
        $params = $this->params;
        $this->emptyCheck($params,array('cart_goods_id_list','openid'));
        $coupon = I('coupon');
        $coupon = !empty($coupon)?explode(',',$coupon):"";
        if(empty($params['cart_goods_id_list'])){
            returnjson(array('code'=>1082,'msg'=>"参数有误！"), $this->returnstyle, $this->callback);
        }
        $db = M('shopping_cart_goods', $this->setting['pre_table']);
        $db1 = M('shopping_order', $this->setting['pre_table']);
        $db2 = M('shopping_order_goods', $this->setting['pre_table']);
        $cartT = $this->setting['pre_table']."shopping_cart";
        $goodsT = $this->setting['pre_table']."shopping_cart_goods";
        $fields = "$goodsT.id,$goodsT.cart_id,$goodsT.num,$goodsT.total_price,$goodsT.status,$cartT.operator_guide,$cartT.operator_guide_openid,$cartT.openid,$cartT.cardno,$cartT.store_name,$cartT.place_id,$cartT.buildid,$cartT.floor,$cartT.poi,$cartT.scantime";
        $recordsInfo = $db->field($fields)->join("left join ".$cartT." on ".$goodsT.".cart_id=".$cartT.".id")->where(array("$goodsT.id"=>array('in',$params['cart_goods_id_list'])))->select();
        //查询订单计算总金额，并且把订单入库;
        if(empty($recordsInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找订单商品！"), $this->returnstyle, $this->callback);
        }
        $total_price = 0;
        $cartOrderSave = array();
        $cartOrderRelationSave = array();
        foreach ($recordsInfo as $key=>$val){
            $total_price += $val['total_price'];
            if(!in_array($val['status'],array(1))){
                returnjson(array('code'=>1082,'msg'=>"购物车商品状态有误！"), $this->returnstyle, $this->callback);
            }
            //订单表添加记录
            if(empty($cartOrderSave[$val['cart_id']])){
                $cartOrderSave[$val['cart_id']] = array(
                    "order_no"=>md5($this->curtime.$val['cart_id']),
                    'operator_guide' =>$val['operator_guide'],
                    'operator_guide_openid' => $val['operator_guide_openid'],
                    'openid' =>$val['openid'],
                    'cardno' =>$val['cardno'],
                    'store_name' =>$val['store_name'],
                    'place_id' =>$val['place_id'],
                    'buildid' =>$val['buildid'],
                    'floor' =>$val['floor'],
                    'poi' =>$val['poi'],
                    'goods_num'=>$val['num'],
                    'goods_price'=>$val['total_price'],
                    'status' =>1,
                    'ordertype' =>0,
                    'scantime' =>$val['scantime']
                );
                $cosid = $db1->add($cartOrderSave[$val['cart_id']]);
                if(empty($cosid)){
                    returnjson(array('code'=>1082,'msg'=>"订单入库失败，请联系管理员！"), $this->returnstyle, $this->callback);
                }
                $cartOrderSave[$val['cart_id']]['id'] = $cosid;
            }else{
                $cartOrderSave[$val['cart_id']]['goods_num']+=$val['num'];
                $cartOrderSave[$val['cart_id']]['goods_price']+=$val['total_price'];
                $updateGoodsInfo = array(
                    'goods_num'=>$cartOrderSave[$val['cart_id']]['goods_num'],
                    'goods_price' =>$cartOrderSave[$val['cart_id']]['goods_price']
                );
                $db1->where(array('id'=>$cartOrderSave[$val['cart_id']]['id']))->save($updateGoodsInfo);
            }
            $cartOrderRelationSave[] = array(
                'orderid'=>$cartOrderSave[$val['cart_id']]['id'],
                'cart_good_id'=>$val['id'],
                'cartid' => $val['cart_id'],
            );
        }
        //添加订单记录信息
        $check = $db2->addAll($cartOrderRelationSave);
        if(empty($check)){
            returnjson(array('code'=>1082,'msg'=>"订单入库失败，请联系管理员！"), $this->returnstyle, $this->callback);
        }
        $orderids = ArrKeyAll($cartOrderSave,'id',false);
        $orderids_copy = $orderids;
        //使用优惠券
        if (!empty($coupon)) {
            $coupon_ftmoney = 0;
            foreach ($coupon as $v){
                $url = "http://182.92.31.114/rest/act/prize/$v";
                $curl = http($url, array(),"GET");
                $arr = json_decode($curl, true);
                if($arr['status']==2){
                    $coupon_ftmoney+=($arr['price']*100);
                }
            }
            if($coupon_ftmoney>0){
                $orderNum = count($orderids_copy);
                $EachDiscount = (int)($coupon_ftmoney/$orderNum);
                $lastDiscount = ($coupon_ftmoney%$orderNum)+$EachDiscount;
                $insertcoupon = json_encode($coupon);
                $lastorder = array_pop($orderids_copy);
                $cartUpdateArr = array('status'=>2,'paytime'=>date('Y-m-d H:i:s',$this->curtime));
                if($EachDiscount ==$lastDiscount){
                    $couponUpdateArr = array('coupon'=>$insertcoupon,'discount_price'=>$EachDiscount);
                    $checkcoupon = $db1->where(array('id'=>array('in',$orderids)))->save($couponUpdateArr);
                    $checkcoupon1 = true;
                }else{
                    $couponUpdateArr = array('coupon'=>$insertcoupon,'discount_price'=>$EachDiscount);
                    $checkcoupon = $db1->where(array('id'=>array('in',$orderids_copy)))->save($couponUpdateArr);
                    $couponUpdateArr['discount_price'] = $lastDiscount;
                    $checkcoupon1 = $db1->where(array('id'=>$lastorder))->save($couponUpdateArr);
                }
                if(!$checkcoupon1 || !$checkcoupon){
                    returnjson(array('code'=>1082,'msg'=>"优惠券更新错误，请重新下单！"), $this->returnstyle, $this->callback);
                }
            }
        }


        //调用支付接口
        if($total_price<=0){
            //更新订单状态并返回（可以和回调接口中用同一个方法处理）
            $check = $this->updateOrderStatus($orderids,$coupon);
            if($check) {
                $msg = array('code' => 200, 'data' => array('total_price' => $total_price));
            }else{
                $msg = array('code' => 1082, 'msg' =>"订单错误，请联系管理员！");
            }
            returnjson($msg, $this->returnstyle, $this->callback);
        }else{
            //请求支付接口
            //['total_fee'=>1,'body'=>1,'notify_url'=>1,'attach'=>1,
            //'attachtransmittag'=>1,'device_info'=>1,'detail'=>1,'fee_type'=>1,'goods_tag'=>1,
            //'outtradeno'=>1,'receipt'=>1,'openid'=>'oWm-rt2OE-JtS9JSxlldzjpV1V7M'],
            $attach = array(
                "total_fee"=>$total_price,
                'appid' => $params['pay_openid']==$params['openid']?$this->setting['wechat_appid']:"wxf3a057928b881466",
                'wxa_tag'=>"N",
                'body' =>"免提购物",
                'notify_url' => C('DOMAIN')."/HandsFreeShopping/Customer/confirmPay",
                'attach' =>urlencode(json_encode(array('orderids' => $orderids, 'coupon'=>$coupon, 'key_admin' => $params['key_admin'], 'total_fee' => $total_price,'openid' => $params['openid']))),
                'attach_transmit_tag' =>'N',
                'detail'=>"免提购物-",
                'openid'=>$params['pay_openid']
            );
            $paydata = PayApiService::requestOrder(
                'wechat',
                'jsapi',
                $attach,
                $params['key_admin'],
                'other'
            );
            writeOperationLog(array('请求微信支付接口' => $paydata), 'handsfreeShopping');
            if ($paydata['status'] != 200) {
                $data = array('code' => 1000, 'data'=>$paydata, 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
            //更新外部支付订单号
            //整合信息返回
            $return = $paydata['data'];
            $return['total_fee'] = $total_price;
            if (isset($paydata['data']['timeStamp'])) {
                $return['timeStamp'] = (string)$paydata['data']['timeStamp'];
            } else {
                $return['timeStamp'] = (string)time();
                $return['outTradeNo'] = $paydata['data']['ordId'];
            }
            $msg = array('code'=>200,'data'=>$return);
            returnjson($msg, $this->returnstyle, $this->callback);
        }

    }

    //微信下单支付回调(使用第三方支付)
    public function confirmPay() {
        $content = file_get_contents("php://input");
        writeOperationLog(array('handfreeshoping_' => $content), 'handsfreeShopping');
        $attach = $this->confirmPayAttach;
        writeOperationLog(array('handfreeshoping_attach' => json_encode($attach)), 'handsfreeShopping');
        $orderids = $attach['orderids'];
        $coupon = $attach['coupon'];
        writeOperationLog(array('handfreeshoping_orderNo' => $orderids), 'handsfreeShopping');
        $this->setting = $this->getMerchant($attach['key_admin']);
        //更改订单状态并且清更新购物车中商品状态
        $this->updateOrderStatus($orderids);
    }

    //更新订单状态
    private function updateOrderStatus($orderid,$coupon = array()) {
        $db = M('shopping_cart_goods', $this->setting['pre_table']);
        $db1 = M('shopping_order', $this->setting['pre_table']);
        $db2 = M('shopping_order_goods', $this->setting['pre_table']);
        //查询订单
        $orderGoodsInfo = $db2->where(array('orderid'=>array('in',$orderid)))->select();
        //检查订单数量
        $goodsId = ArrKeyAll($orderGoodsInfo,'cart_good_id',0);
        $orderInfo = $db1->where(array('id'=>array('in',$orderid)))->select();
        writeOperationLog(array('handfreeshoping_goodsId' => $goodsId), 'handsfreeShopping');
        writeOperationLog(array('handfreeshoping_orderInfo' => $orderInfo), 'handsfreeShopping');
        if(empty($orderInfo) || count($orderInfo)!=count($orderid)){
            writeOperationLog(array('handfreeshoping_1' => array("s"=>count($orderInfo),"ss"=>count($orderid))), 'handsfreeShopping');
            return false;
        }
        foreach ($orderInfo as $k=>$v){
            if($v['status']>=3){
                return false;
            }
        }
        //核销优惠券
        $coupon_info = "";
        if(!empty($coupon)){
            foreach ($coupon as $v) {
                $url = 'http://101.200.216.60:8080/proxy/verify/pos';
                $data = array('code' => $v);
                $res = http($url, json_encode($data), 'POST', array('Content-Type:application/json'), true);
                if (is_json($res)) {
                    $array = json_decode($res, true);
                    if ($array['code'] == 0) {
                        $coupon_info .= "success:$v-";
                    } else {
                        $coupon_info .= "fail:$v-";
                    }
                } else {
                    $coupon_info .= "fail:$v-";
                }
            }
        }
        //更新订单
        $updateOrder = $db1->where(array('id'=>array('in',$orderid)))->save(array('status'=>3,'couponoff_info'=>$coupon_info));
        $cartUpdateArr = array('status'=>2,'paytime'=>date('Y-m-d H:i:s',$this->curtime));
        $updateCart = $db->where(array('id'=>array('in',$goodsId)))->save($cartUpdateArr);


        //发送微信模板消息
        $total_price = 0;
        $orderIdStr = "";
        foreach ($orderInfo as $k=>$v){
            $total_price +=($v['goods_price']-$v['discount_price']);
            $orderIdStr .= ($v['order_no']." |");
            $openid = $v['openid'];
        }
        if($orderIdStr){
            $orderIdStr = substr($orderIdStr,0,strlen($orderIdStr)-1);
        }
        $total_price = ($total_price/100);
        $tempmessage=array(array(
            'touser'=>$openid,
            'template_id'=>'ImAIu4qbN4tH_sBJ0wf0O1aTqeryfhF6juysTawHmjs',
            'url'=>'',
            'data'=>array(
                'first'=>array('value'=>'您的订单支付成功！','color'=>'#173177'),
                'keyword1'=>array('value'=>sprintf('%.2f', $total_price).'元', 'color'=>'#173177'),
                'keyword2'=>array('value'=>$orderIdStr, 'color'=>'#173177'),
                'remark'=>array('value'=>'请等待货物送往自提点后，凭取货码自提取货。谢谢！', 'color'=>'#173177'),
            )
        ));
        writeOperationLog(array('handfreeshoping_msg_openid' => $openid), 'handsfreeShopping');
        $wechat = new TemplateController();
        $sendMessage = $wechat->insideSendMessage($tempmessage, $this->setting['ukey'], $this->setting['wechat_appid']);
        //给商户发送消息
        writeOperationLog(array('handfreeshoping_msg_return' => $sendMessage), 'handsfreeShopping');
        $cartGoodsT = $this->setting['pre_table']."shopping_cart_goods";
        $orderGoodsT = $this->setting['pre_table']."shopping_order_goods";
        $fields = "$cartGoodsT.goods_name";
        
        $total_db = M('admin','total_');
        $merchant_info = $total_db->where(array('pid'=>$this->setting['id']))->find();
        
        foreach ($orderInfo as $k=>$v){
            $recordsInfo = $db2->field($fields)->join("left join ".$cartGoodsT." on ".$cartGoodsT.".id=".$orderGoodsT.".cart_good_id")->where(array("$orderGoodsT.orderid"=>$v['id']))->select();
            $goodsNStr = "";
            foreach ($recordsInfo as $j){
                $goodsNStr.=$j['goods_name']." |";
            }
            if($goodsNStr){
                $goodsNStr = trim($goodsNStr,"|");
            }
            $price = ($v['goods_price']-$v['discount_price'])/100;
            $price = sprintf('%.2f', $price).'元';
            $tempmessage1=array(array(
                'touser'=>$v['operator_guide_openid'],
                'template_id'=>'m9gz80jpfg4HU82cbEqWOiJGEShIN2c3BsSf_UkDf0U',
                'url'=>'',
                'data'=>array(
                    'first'=>array('value'=>'您好，顾客订单付款成功，请尽快进行打包出货。','color'=>'#173177'),
                    'keyword1'=>array('value'=>$goodsNStr, 'color'=>'#173177'),
                    'keyword2'=>array('value'=>$v['store_name'], 'color'=>'#173177'),
                    'keyword3'=>array('value'=>$price, 'color'=>'#173177'),
                    'keyword4'=>array('value'=>date('Y-m-d H:i:s',time()), 'color'=>'#173177'),
                    'remark'=>array('value'=>'', 'color'=>'#173177'),
                )
            ));
            writeOperationLog(array('handfreeshoping_msg_return' => $tempmessage1), 'handsfreeShopping');
            writeOperationLog(array('handfreeshoping_msg_return' => $merchant_info), 'handsfreeShopping');
            $sendMessage = $wechat->insideSendMessage($tempmessage1, $merchant_info['ukey'], $merchant_info['wechat_appid']);
        }

        return true;
    }

    //获取单个商品详情（配合订单记录）
    public function getGoodsDetail() {
        $params = $this->params;
        $this->emptyCheck($params,array('cart_goods_id'));
        $db = M('shopping_cart_goods', $this->setting['pre_table']);
        $cartT = $this->setting['pre_table']."shopping_cart";
        $goodsT = $this->setting['pre_table']."shopping_cart_goods";
        $recordInfo = $db->join("left join ".$cartT." on ".$goodsT.".cart_id=".$cartT.".id")->where(array("$goodsT.id"=>$params['cart_goods_id']))->find();
        if(empty($recordInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找到该订单记录！"), $this->returnstyle, $this->callback);
        }
        //如果需要商品描述需要调用外包接口获取
        $recordInfo['goods_image'] = json_decode($recordInfo['goods_image'],true);
        
        $msg = array('code'=>200,'data'=>$recordInfo);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    //获取订单列表status(0:全部1:待发货2待收获3已完成)
    public function getOrderList() {
        $params = $this->params;
        $this->emptyCheck($params,array('status','page','openid'));
        $statusArr = $this->statusArr;
        $statusArrMatch = $this->statusArrMatch;
        if(empty($statusArr[$params['status']])){
            returnjson(array('code'=>1082,'msg'=>"订单状态参数为找到匹配！"), $this->returnstyle, $this->callback);
        }
        //查询记录
        $page = ((int)$params['page'])<=0?1:((int)$params['page']);
        $offset = !empty($params['offset'])?(int)$params['offset']:10;
        $start = ($page-1)*$offset;
        $db = M('shopping_order', $this->setting['pre_table']);
        $db2 = M('shopping_place', $this->setting['pre_table']);

        $where = array("status"=>array('in',$statusArr[$params['status']]),"openid"=>$params['openid']);
        $recordInfo = $db->where($where)->order("createtime desc")->limit($start,$offset)->select();
        //查询所有自提点
        $placeInfo = $db2->field("id,name")->select();
        $placeInfo = ArrKeyFromId($placeInfo,'id');
        //组装数据
        if($recordInfo){
            foreach ($recordInfo as $k=>$v){
                $recordInfo[$k]['show_status']=$statusArrMatch[$v['status']];
                $recordInfo[$k]['place_name']=$placeInfo[$v['place_id']]['name'];
            }
        }
        $msg = array('code'=>200,'data'=>$recordInfo);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    //获取订单详情
    public function getOrderDetail() {
        $params = $this->params;
        $this->emptyCheck($params,array('order_id'));
        $statusArr = $this->statusArr;
        $statusArrMatch = $this->statusArrMatch;
        //查询记录
        $db = M('shopping_order_goods', $this->setting['pre_table']);
        $db1 = M('shopping_order', $this->setting['pre_table']);
        $db2 = M('shopping_place', $this->setting['pre_table']);
        $db3 = M('shopping_cart_goods', $this->setting['pre_table']);
        $orderInfo = $db1->where(array("id"=>$params['order_id']))->find();
        if(empty($orderInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找到该订单！"), $this->returnstyle, $this->callback);
        }
        $cartGoodsT = $this->setting['pre_table']."shopping_cart_goods";
        $orderGoodsT = $this->setting['pre_table']."shopping_order_goods";
        $fields = "$cartGoodsT.*";
        $join = "left join ".$cartGoodsT." on ".$orderGoodsT.".cart_good_id=".$cartGoodsT.".id";
        $recordsInfo = $db->field($fields)->join($join)->where(array("$orderGoodsT.orderid"=>$params['order_id']))->select();
        //查询所有自提点
        $placeInfo = $db2->field("id,name")->select();
        $placeInfo = ArrKeyFromId($placeInfo,'id');
        //组装数据
        
        foreach ($recordsInfo as $k=>$v){
            $recordsInfo[$k]['goods_image'] = json_decode($v['goods_image'],true);
        }
        
        $orderInfo['show_status']=$statusArrMatch[$orderInfo['status']];
        $orderInfo['place_name']=$placeInfo[$orderInfo['place_id']]['name'];
        $orderInfo['record_list'] = $recordsInfo;
        $orderInfo['handsFreeQRcode'] = encrypt_zht($orderInfo['order_no']);

        $msg = array('code'=>200,'data'=>$orderInfo);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    //获取小票信息
    public function getTicketDetail() {
        $params = $this->params;
        $this->emptyCheck($params,array('ticket','openid'));
        $params['ticket'] = urldecode($params['ticket']);
        //查询erpclassname配置
        $ErpClass = $this->GetOneAmindefault($this->setting['pre_table'],$params['key_admin'],"erpclassname");
        if(empty($ErpClass)){
            returnjson(array('code'=>1082,'msg'=>"未找erpclassname相关配置！"), $this->returnstyle, $this->callback);
        }
        //获取并检查小票信息
        $ticketInfo = ErpCommonService::receiveInfoByScan(array('code'=>$params['ticket']),$this->setting,$ErpClass);
        if($ticketInfo['code'] !=200){
            returnjson(array('code'=>1082,'msg'=>"未找到小票相关信息！"), $this->returnstyle, $this->callback);
        }
        //检查小票订单相关信息
        $db1 = M('shopping_order', $this->setting['pre_table']);
        $orderInfo = $db1->where(array("order_no"=>$ticketInfo['data']['orderid'],'ordertype'=>1,'status'=>array('egt',3)))->find();
        if($orderInfo){
            //已经下过单
            returnjson(array('code'=>1082,'msg'=>"该二维码已经使用，不可重复使用！"), $this->returnstyle, $this->callback);
        }

        returnjson($ticketInfo, $this->returnstyle, $this->callback);
    }

    //小票下单接口返回二维码
    public function handsFreeByTicket() {
        $params = $this->params;
        $this->emptyCheck($params,array('ticket','openid','place_id'));
        $params['ticket'] = urldecode($params['ticket']);
        //查询erpclassname配置
        $ErpClass = $this->GetOneAmindefault($this->setting['pre_table'],$params['key_admin'],"erpclassname");
        if(empty($ErpClass)){
            returnjson(array('code'=>1082,'msg'=>"未找erpclassname相关配置！"), $this->returnstyle, $this->callback);
        }
        //获取并检查小票信息
        $ticketInfo = ErpCommonService::receiveInfoByScan(array('code'=>$params['ticket']),$this->setting,$ErpClass);
        if($ticketInfo['code'] !=200 || empty($ticketInfo['data']['orderid'])){
            returnjson(array('code'=>1082,'msg'=>"未找到小票相关信息！"), $this->returnstyle, $this->callback);
        }
        //检查小票订单相关信息
        $db1 = M('shopping_order', $this->setting['pre_table']);
        $db2 = M('shopping_place', $this->setting['pre_table']);
        $orderInfo = $db1->where(array("order_no"=>$ticketInfo['data']['orderid'],'ordertype'=>1))->find();
        if($orderInfo && $orderInfo['status']>=3){
            //已经下过单
            returnjson(array('code'=>1082,'msg'=>"该二维码已经使用，不可重复使用！"), $this->returnstyle, $this->callback);
        }
        if($orderInfo && $orderInfo['status']==2){
            $enctypt_orderid= encrypt_zht($orderInfo['order_no']);
            $msg = array('code'=>200,'data'=>$enctypt_orderid);
            returnjson($msg, $this->returnstyle, $this->callback);
        }
        $placeInfo = $db2->where(array("id"=>$params['place_id']))->find();
        if(empty($placeInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找到该自提点，请检查！"), $this->returnstyle, $this->callback);
        }
        //获取用户信息
        $userInfo = $this->checkUserExists($this->setting['pre_table'],$params['openid']);
        if(empty($userInfo['cardno'])){
            returnjson(array('code'=>1082,'msg'=>"您还不是会员！"), $this->returnstyle, $this->callback);
        }
        //小票信息入库
        $saleTime = (int)($ticketInfo['data']['saleTime']/1000);
        $addOrderArr = array(
            "order_no"  => $ticketInfo['data']['orderid'],
            "openid"    => $params['openid'],
            "cardno"    => $userInfo['cardno'],
            "store_name"=> $ticketInfo['data']['shopEntityName'],
            "place_id"=> $params['place_id'],
            "goods_num"=> 1,
            "goods_price"=> (int)($ticketInfo['data']['paidAmount']*100),
            "status"=> 2,
            "createtime"=> date("Y-m-d H:i:s",$saleTime),
            "ordertype"=> 1
        );
        $lastid = $db1->add($addOrderArr);
        if(empty($lastid)){
            returnjson(array('code'=>1082,'msg'=>"插入订单失败，请联系管理员！"), $this->returnstyle, $this->callback);
        }
        $enctypt_orderid= encrypt_zht($addOrderArr['order_no']);
        $msg = array('code'=>200,'data'=>$enctypt_orderid);
        returnjson($msg, $this->returnstyle, $this->callback);
    }





}

?>
