<?php
/**
 * Created by PhpStorm.
 * 美味订单：预下单检测，预点下单，到店后4.1.4秒点下单接口到厨房
 * User: zhangkaifeng
 * Date: 21/07/2017
 * Time: 15:19
 */

namespace Mwee\Service;


use Common\Controller\RedisController;
use Mwee\Controller\CommonMwee;

class OrderService
{

    /**
     * @param $params 接收到的c端提交的订单内的菜单信息
     * @param $admininfo
     * @param $userid
     * @return array|mixed
     */
    public static function order($params, $admininfo, $userid)
    {
        //外层数据是否正确，这里只验证几个参数，按接口来说，除了sid以外，其他都可以错误，醉了吧乡亲们。。。
        if (!$params['sid'] || !$params['contacts'] || !$params['mobile']){
            return array('code'=>1030);
        }

        //查询商铺是快餐还是正餐，0正餐，1快餐
        $shoptype = self::getSidType($params['sid']);


        //正餐流程：预下单检测，预点下单，到店后秒点下单
        if ($shoptype === 0){
            $orderid = date('YmdHis') . rand(1000, 9999) . rand(100, 999);
            //预下单检测
            $return = self::checkOrder($params,$params['sid'], $orderid);//返回数组
            if ($return['code'] == 2310){//预下单
                $orderstatus = self::goodsOrder($params, $params['sid'], $orderid, $admininfo, $userid);
                return $orderstatus;
            }else{
                //如果接口返回错误，则报错
                return $return;
            }
        }elseif($shoptype == 1){
            //快餐相关
            return ['code'=>104,'data'=>1];
        }else{
            return ['code'=>104, 'data'=>2];
        }

    }


    /**
     * @param $sid
     * @return array|int|mixed
     */
    private static function getSidType($sid)
    {
        $redis = new RedisController();
        $redis = $redis->connectredis();
        $value = $redis->get('mwee:sid:type:' . $sid);
        if (false !== $value){
            return (int)$value;
        }else{
            //查询这个shopid在我们数据库中的信息，并缓存在redis内
            $db = M('total_mwee_shopid');
            $find = $db->where(array('sid'=>$sid))->find();
            if (!$find){
                return array('code'=>102);
            }else{
                $redis->set('mwee:sid:type:' . $sid, (int)$find['shoptype'], array('ex'=>86400));
                return (int)$find['shoptype'];
            }
        }
    }

    /**
     *  生成订单信息——预下单，此时要对菜品进行严格验证，否则秒点可能会下单失败，所有c端传入后端的数据都认为是危险的、假的、不符合规定的，需严格验证
     * @param $params
     * @param $sid
     * @param $orderid
     * @param $admininfo
     * @param $userid
     * @return array
     */
    private static function goodsOrder($params, $sid, $orderid, $admininfo, $userid)
    {
//        //获取店铺菜单
//        $goodmenus = GoodsMenuService::getGoodsMenu($sid);
//        if ($goodmenus['code'] == 200){
//            $goodmenus = $goodmenus['data']['categories'];
//            $goods = array_column($goodmenus, 'dishes');
//            //取出所有菜单到统一的列表，没有类别，只有菜单
//            $goodsall=[];
//            foreach ($goods as $goodkey => $good) {
//                $goodsall = array_merge($goodsall, $good);
//            }
//            $arr2 = array_column($goodsall,'vendorDishId');//美味菜单数组
//            dump($arr2);
//            //遍历c端数据，验证数据准确性
//            foreach ($params['items'] as $key => $val){
//                if (!$val['vendorDishId']) {
//                    return array('code'=>102,'data'=>$val);
//                    break;
//                }
//                $key = array_search($val['vendorDishId'], $arr2);
//
//                //如果没有菜品id，返回错误
//                if (false === $key){
//                    return array('code'=>1051,'data'=>array('w'=>1, 'data'=>$val));
//                    break;
//                }
//                $good = $goodsall[$key];//用c端提交的菜单id，获取美味菜单中这个菜单id的信息
//                //判断菜品类型，简单菜还是套餐，除简单菜外，其余类型暂时一律返回1051
//                if (1 == $good['itemType']) {
//                    $modifiers=[];
//                    //验证是否有规格等数组项
//                    if (isset($val['modifiers'])) {
////                        foreach ($val['modifiers'] as $k => $v) {
////                            $modifiers[] = array(
//////                        'modifierName'=>$v['name'],
////                                'modifierId'=>$v['modifierId'. ],
////                                'modifierNum'=>$v['qty'],
////                                'modifierPrice'=>$v['price'], //价格
////                                'spec'=>$v['outerModifierId']?$v['outerModifierId'] :''
////                            );
////                        }
//                    }
//                }else{
//                    return array('code'=>1051,'data'=>array('w'=>2, 'data'=>$goodmenus));
//                    break;
//                }
//            }
//
//        }else{//获取菜单错误
//            return $goodmenus;
//        }
//        die;
        //循环，组合出预下单检测和预点下单接口的数组
//        $orderid = '12345669';//date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        $checkorder=array(

            'shopId'=>$sid,
            'manageShopId'=>'',
            'userId'=>'',
            'people'=>$params['people'],
            'shopName'=>$params['shopName'],
            'sourceType'=>14,
            'devType'=>0,
//            'orderId'=>$orderid,
            'bizType'=>14,
            'total'=>$params['totalPrice'],
            'goodsnum'=>$params['goodsnum'],
            'thirdType'=>"4",
            'tableNo'=>$params['tableNumber'],
            'tableName'=>$params['tableName'],
            'preOrderId'=>'',
            'contact'=>$params['contacts'],
            'mobile'=>$params['mobile'],
            'subTotal'=>$params['totalPrice'],
            'orderRemark'=>$params['note'],
            'commitId'=>date('YmdHis') . rand(1000, 9999) . rand(10000, 99999),
            'comment'=>$params['comment']
        );
        foreach ($params['items'] as $key => $val){
            if (!$val['vendorDishId']) {
                return array('code'=>102,'data'=>$val);
                break;
            }
            $modifiers=array();
            //验证是否有规格等数组项
            if (isset($val['modifiers'])) {
                foreach ($val['modifiers'] as $k => $v) {
                    $modifiers[] = array(
//                        'modifierName'=>$v['name'],
                        'modifierId'=>$v['modifierId'],
                        'modifierNum'=>$v['qty'],
                        'modifierPrice'=>$v['price'], //价格
                        'spec'=>$v['outerModifierId']?$v['outerModifierId'] :''
                    );
                }
            }
            $checkorder['itemList'][]=array(
                'itemId'=>$val['vendorDishId'],
//                'referDishId'=>$val['vendorDishId'],
                'outerItemId'=>$orderid,//填错也能通过（此处应有一个无奈的笑），但肯定关联了一个错误的订单id
                'name'=>$val['dishName'],//填错也行……菜名
                'unit'=>$val['unit'],//填错也行……单位
                'itemNum'=>$val['quantity'],//填错也行……几份
//                'itemPrice'=>$val['price'],//填错也行……原价
//                'itemStatus'=>(int)$val['status'],//填错也行……菜品状态
                'modifiers'=> $modifiers
            );
        }
        $content = json_encode($checkorder);
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/menu/baseorder/submit', $data, 'POST', array(), true);
        if (!is_json($re)){
            return array('code'=>101);
        }else{
            $arr= json_decode($re, true);
            if ($arr['code'] == 0) {
                $decontent = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                $dearr = json_decode($decontent, true);
                $orderitemlists = $checkorder['itemList'];//菜品详情，先留着
                unset($checkorder['itemList']);
                $checkorder['orderId'] = $dearr['orderId'];
                $checkorder['userucid'] = $userid;
                $checkorder['ourorderid'] = $orderid;
                $checkorder['itemlists'] = json_encode($orderitemlists);
                $db = M('mweegoodsorder', $admininfo['pre_table']);
                $add = $db->add($checkorder);
                if ($add){
                    return array('code'=>200, 'data'=>$checkorder);
                }else{
                    return array('code'=>104, 'data'=>'d error');
                }
            }else{
                return array('code'=>1082, 'data'=>array('code'=>$arr['code'],'msg'=>$arr['msg']), 'msg'=>$arr['msg']);
            }
        }
    }


    /**
     * @param $params
     * @param $sid
     * @return array|mixed
     */
    private static function checkOrder($params, $sid, $orderid)
    {
        //循环，组合出预下单检测
//        $orderid = '12345669';//date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        $checkorder=array(
            'orderId'=>$orderid,//必须
            'vendorShopId'=>$params['sid'],//必须
            'tableNumber'=>$params['tableNumber'],//否
            'totalPrice'=>$params['totalPrice'],//否
            'concat'=>$params['contacts'],//否
            'mobile'=>$params['mobile'],//否
            'createTime'=>date('Y-m-d H:i:s'),//否
            'people'=>$params['people'],//否
            'note'=>$params['note'],//否
        );
        $i=1;
        foreach ($params['items'] as $key => $val){
            if (!$val['vendorDishId']) {
                return array('code'=>102,'data'=>$val);
                break;
            }
            $modifiers=array();
            //验证是否有规格等数组项
            if (isset($val['modifiers'])) {
                foreach ($val['modifiers'] as $k => $v) {
                    $modifiers[] = array(
                        'modifierName'=>$v['name'],
                        'modifierId'=>$v['modifierId'],
                        'modifierNum'=>$v['qty'],
                        'modifierPrice'=>$v['price'] //价格
                    );
                }
            }
            $checkorder['items'][]=array(
                'itemId'=>$i,
                'referDishId'=>$val['vendorDishId'],
                'referOrderId'=>$orderid,//填错也能通过（此处应有一个无奈的笑），但肯定关联了一个错误的订单id
                'itemName'=>$val['dishName'],//填错也行……菜名
                'unit'=>$val['unit'],//填错也行……单位
                'quantity'=>$val['quantity'],//填错也行……几份
                'itemSum'=>$val['price'],//填错也行……原价
                'itemStatus'=>(int)$val['status'],//填错也行……菜品状态
                'modifiers'=> $modifiers
            );
            ++$i;
        }
        $content = json_encode($checkorder);
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/menu/order/check', $data, 'POST', array(), true);
        if (!is_json($re)){
            return array('code'=>101);
        }else{
            $arr= json_decode($re, true);
            return $arr;
        }
//        dump($arr);
//        $data = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
//        dump(json_decode($data, true));
    }






    public static function submitOrder($codeurl, $userucid, $admininfo)
    {
        $code = trim(str_replace('http://qr.mwee.cn/qr/', '', $codeurl));
        if (!$code) {
            return array('code'=>1051);
        }
        $codeInfo = self::qrCode($code);
        if ($codeInfo['code'] == 0) {

        }else{
            return $codeInfo;
        }
    }


    /**
     * 返回链接内的code码详情
     * @param $code
     * @return array|mixed
     */
    private static function qrCode($code)
    {
        $content = array(
            'code'=>$code,
            '_timestamp'=>time(),
        );
        $content = json_encode($content);
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/pay/code/qr', $data, 'POST', array(), true);
        if (!is_json($re)){
            return array('code'=>101);
        }else{
            $arr= json_decode($re, true);
            return $arr;
        }
    }



    public static function orderStatus($params)
    {
        $content = json_encode($params);
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/menu/order/status', $data, 'POST', array(), true);
        if (!is_json($re)){
            return array('code'=>101);
        }else{
            $arr= json_decode($re, true);
            if ($arr['code'] == 0) {
                $decontent = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                $dearr = json_decode($decontent, true);
                return array('code'=>200, 'data'=>$dearr);
            }else{
                return array('code'=>1082, 'data'=>array('code'=>$arr['code'],'msg'=>$arr['msg']), 'msg'=>$arr['msg']);
            }
        }
    }








}