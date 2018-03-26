<?php
/**
 * 免提购物 商户端
 * User: 张行
 * Date: 2018/1/25
 * Time: 上午10:00
 */

namespace HandsFreeShopping\Controller;

use Common\Service\PublicService;
use PublicService\Controller\UpWechatImageController;
use Thirdwechat\Controller\Wechat\TemplateController;

class MerchantController extends IndexController {
    protected $admin_arr;

    public function _initialize()
    {
        parent::_initialize();
        $this->admin_arr = $this->getMerchant($this->ukey);
    }
    
    /**
     * 获取客户列表
     */
    public function CustomerList() {
        $params['search'] = I('search');
        $params['poiNo']  = I('poiNo');
        $params['floor'] = I('floor');
        $params['buildid'] = I('buildid');
        
        $cutDB = M('customer_list',$this->admin_arr['pre_table']);
        $merDB = M('mem',$this->admin_arr['pre_table']);
        $CartGoodsDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        $CartDB = M('shopping_cart',$this->admin_arr['pre_table']);
        
        $mem = $this->admin_arr['pre_table'].'mem';
        $cut = $this->admin_arr['pre_table'].'customer_list';
        
        if($params['search']){
            
            $map['_string'] ='(usermember like "%'.$params['search'].'%") or mobile like "%'.$params['search'].'%"';
        }
        $map['member_status'] = array('eq',1);
        $map['poi_id'] = array('eq',$params['poiNo']);
        $map['per_id'] = array('eq',$this->perInfo['id']);
        $map['_logic'] = 'and';
        
        $cutList = $cutDB->where($map)->join('Left join '.$mem.' on '.$cut.'.member_cardno = '.$mem.'.cardno')->order('update_time desc')->select();
        
        if($cutList){
            
            foreach($cutList as $k=>$v){
                $orderNoInfo = $CartDB->where(array('poi'=>$params['poiNo'],'floor'=>$params['floor'],'cardno'=>$v['cardno'],'scantime'=>0))->find();
                
                if($orderNoInfo){
                    $cutList[$k]['goods_num'] = $CartGoodsDB->where(array('cart_id'=>$orderNoInfo['id']))->count();
                }else{
                    $cutList[$k]['goods_num'] = 0;
                }
                
            }
            
            
            $msg = array('code'=>200,'data'=>$cutList);
        }else{
            $msg = array('code'=>102);
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    /**
     * 删除客户
     */
    public function CustomerDel(){
        $params['poiNo']  = I('poiNo');
        $params['floor'] = I('floor');
        $params['buildid'] = I('buildid');
        $params['member_cardno'] = I('cardno');
        $params['per_id'] = $this->perInfo['id'];
        
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $cutDB = M('customer_list',$this->admin_arr['pre_table']);
        $CartGoodsDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        $CartDB = M('shopping_cart',$this->admin_arr['pre_table']);
        
        $res = $cutDB->where(array('poi_id'=>$params['poiNo'],'per_id'=>$params['per_id'],'member_cardno'=>$params['member_cardno']))->delete();
        
        if($res!==false){
            $orderNoInfo = $CartDB->where(array('poi'=>$params['poiNo'],'floor'=>$params['floor'],'cardno'=>$params['member_cardno'],'scantime'=>0))->find();
            
            if($orderNoInfo){
                $orderRes = $CartDB->where(array('id'=>$orderNoInfo['id']))->delete();
                $orderGoodsRes = $CartGoodsDB->where(array('cart_id'=>$orderNoInfo['id']))->delete();
            }
            $msg['code'] = 200;
        }else{
            $msg['code'] = 104;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     *  获取客户购物车列表
     */
    public function CustomerCartList(){
        $params['cardno'] = I('cardno');
        $params['poiNo']  = I('poiNo');       
        $params['floor'] = I('floor');
        $params['buildid'] = I('buildid');
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $shopNoDB = M('shopping_cart',$this->admin_arr['pre_table']);
        $commodityNoDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        
        //获取当前客户在该店铺下的订单号
        $map['poi'] = array('eq',$params['poiNo']);
        $map['floor'] = array('eq',$params['floor']);
        $map['cardno'] = array('eq',$params['cardno']);
        $map['scantime'] = array('eq',0);
        $orderNoInfo = $shopNoDB->where($map)->find();
        
        if(!$orderNoInfo){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        
        //根据订单号获取该订单下导购的所有商品
        $map1['cart_id'] = $orderNoInfo['id'];
        $commodityList = $commodityNoDB->where($map1)->select();
        
        if($commodityList){
            
            foreach($commodityList as $k=>$v){
                $commodityList[$k]['goods_image'] = json_decode($v['goods_image'],true);
            }
            
            $msg['code'] = 200;
            $msg['data'] = $commodityList;
        }else{
            $msg['code'] = 102;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 删除购物车列表
     */
    public function CustomerCartDel(){
        $params['cart_id'] = I('cart_id');//购物车订单ID
        $params['id'] = I('goods_id');//购物车商品ID
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $commodityNoDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        
        $info = $commodityNoDB->where(array('cart_id'=>$params['cart_id'],'id'=>$params['id']))->delete();
    
        if(!$info){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
        
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }
    
    /**
     *  扫码客户接口
     *  //修改wfzh_shopping_cart表中会员和店员的关联关系
     */
    public function  ScanCustomerInfo(){
        $params['cardno'] = I('cardno');
        $params['poiNo'] = I('poiNo');
        $params['buildid'] = I('buildid');//建筑物ID
        $params['floor'] = I('floor');//楼层
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $cutDB = M('customer_list',$this->admin_arr['pre_table']);
        $merDB = M('mem',$this->admin_arr['pre_table']);
        $memInfo = $merDB->where(array('cardno'=>$params['cardno']))->find();
        
        if(!$memInfo){
            returnjson(array('code'=>504), $this->returnstyle, $this->callback);
        }
        
        $shopNoDB = M('shopping_cart',$this->admin_arr['pre_table']);
        $commodityNoDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);

        $map['poi_id'] = array('eq',$params['poiNo']);
        $map['floor'] = array('eq',$params['floor']);
        $map['member_status'] = array('eq',1);
        $map['member_cardno'] = array('eq',$params['cardno']);
        $cutInfo = $cutDB->where($map)->find();
        
        if($cutInfo){
            if($cutInfo['per_id'] != $this->perInfo['id']){
                $res = $cutDB->where(array('id'=>$cutInfo['id']))->save(array('per_id'=>$this->perInfo['id']));
            }else{
                $res = true;
            }
        }else{
            $data['poi_id'] = $params['poiNo'];
            $data['floor'] = $params['floor'];
            $data['per_id'] = $this->perInfo['id'];
            $data['member_cardno'] = $params['cardno'];
            $data['member_status'] = 1;
            $data['member_datetime'] = date('Y-m-d H:i:s');
            
            $res = $cutDB->add($data);
        }
        
        if($res !== false){
            
            //获取当前客户在该店铺下的订单号
            $map1['poi'] = array('eq',$params['poiNo']);
            $map1['floor'] = array('eq',$params['floor']);
            $map1['cardno'] = array('eq',$params['cardno']);
            $map1['scantime'] = array('eq',0);
            $orderNoInfo = $shopNoDB->where($map1)->find();
            
            if(!$orderNoInfo){
                returnjson(array('code'=>200,'data'=>array('mobile'=>$memInfo['mobile'],'name'=>$memInfo['usermember'],'data'=>array())), $this->returnstyle, $this->callback);
            }
            
            //如果存在订单，判断该导购是否之前的导购，不是则替换。
            if($orderNoInfo['operator_guide'] != $this->perInfo['id']){
                $orderNoInfo = $shopNoDB->where(array('id'=>$orderNoInfo['id']))->save(array('operator_guide'=>$this->perInfo['id']));
            }
            
            //根据订单号获取该订单下导购的所有商品
            $map2['cart_id'] = $orderNoInfo['id'];
            $commodityList = $commodityNoDB->where($map2)->select();
            
            $data['mobile'] = $memInfo['mobile'];
            $data['name'] = $memInfo['usermember'];
            $data['data'] = $commodityList?$commodityList:array();
            $msg['code'] = 200;
            $msg['data'] = $data;
            
        }else{
            $msg['code'] = 104;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 添加购物车
     */
    public function CustomerShopCart(){
        $params['shop_id'] = I('shop_id');//商品ID
        $params['goods_name'] = I('goods_name');//商品名称
        $params['goods_image'] = I('goods_image'); //商品图片
        $params['goods_no'] = I('goods_no');//商品货号
        $params['poiNo'] = I('poiNo');//店铺ID
        $params['store_name'] = I('store_name');//店铺名称
        $params['cardno'] = I('cardno');//卡号
        $params['num'] = I('num');//总数
        $params['pre_id'] = $this->perInfo['id'];//店长或店员信息
        $params['colour'] = I('colour');//颜色
        $params['price'] = I('price'); //单价       
        $params['size'] = I('size');//尺码
        $params['buildid'] = I('buildid');//建筑物ID
        $params['floor'] = I('floor');//楼层
        $params['remark'] = I('remark');//备注
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $params['price'] = $params['price']*100;//转化分
        $CartGoodsDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        $CartDB = M('shopping_cart',$this->admin_arr['pre_table']);
        
        $where['cardno'] = array('eq',$params['cardno']);//会员卡号
        $where['poi'] = array('eq',$params['poiNo']);//poi店铺ID
        $where['floor'] = array('eq',$params['floor']);//楼层
        $where['scantime'] = array('eq',0);//扫码时间  判断是否有扫码时间
        $CartInfo = $CartDB->where($where)->find();
        
        if($CartInfo){
            $data['cart_id'] = $CartInfo['id'];
        }else{
            
            //入库-购物车表
            $cart_data['operator_guide'] = $params['pre_id'];
            $cart_data['operator_guide_openid'] = $this->perInfo['openId'];
            $cart_data['cardno'] = $params['cardno'];
            $cart_data['store_name'] = $params['store_name'];
            $cart_data['buildid'] = $params['buildid'];
            $cart_data['poi'] = $params['poiNo'];
            $cart_data['floor'] = $params['floor'];
            $cart_data['createtime'] = date('Y-m-d H:i:s');
            $CartInsertInt = $CartDB->add($cart_data);
            
            if(!$CartInsertInt){
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
            
            $data['cart_id'] = $CartInsertInt;
        }
        if(!$data['cart_id']){
            returnjson(array('code'=>1082,'msg'=>'代码写错了'), $this->returnstyle, $this->callback);
        }
        
        $data['goods_id'] = $params['shop_id'];
        $data['goods_name'] = $params['goods_name'];
        $data['goods_image'] = json_encode($params['goods_image']);
        $data['goods_no'] = $params['goods_no'];
        $data['colour'] = $params['colour'];
        $data['size'] = $params['size'];
        $data['num'] = $params['num'];
        $data['price'] = $params['price'];
        $data['total_price'] = $params['num']*$params['price'];
        $data['buy_type'] = 1;
        $data['status'] = 0;
        $data['remarks'] = $params['remark'];
        $data['createtime'] = date('Y-m-d H:i:s');

        $CartRes = $CartGoodsDB->add($data);
        
        if(!$CartRes){
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
        
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }
    
    /**
     * 购物车列表
     */
    public function CartGoodsList(){
        $params['buildid'] = I('buildid');//建筑物ID
        $params['floor'] = I('floor');//楼层
        $params['poiNo'] = I('poiNo');//店铺ID
        $params['cardno'] = I('cardno');//会员卡号
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $CartGoodsDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        $CartDB = M('shopping_cart',$this->admin_arr['pre_table']);
        
        $where['cardno'] = array('eq',$params['cardno']);//会员卡号
        $where['poi'] = array('eq',$params['poiNo']);//poi店铺ID
        $where['floor'] = array('eq',$params['floor']);//楼层
        $where['scantime'] = array('eq',0);//楼层
        $CartInfo = $CartDB->where($where)->find();
        
        if(!$CartInfo){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        
        $GoodsList = $CartGoodsDB->where(array('cart_id'=>$CartInfo['id'],'status'=>0))->select();
        
        if(!$GoodsList){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        
        foreach($GoodsList as $k=>$v){
            $GoodsList[$k]['goods_image'] = json_decode($v['goods_image'],true);
        }
        
        returnjson(array('code'=>200,'data'=>$GoodsList), $this->returnstyle, $this->callback);
    } 
    
    /**
     * 购物车详情
     */
    public function CartGoodsOnceData(){
        $params['buildid'] = I('buildid');//建筑物ID
        $params['floor'] = I('floor');//楼层
        $params['poiNo'] = I('poiNo');//店铺ID
        $cart_goods_id = I('cart_goods_id');//购物车商品ID
        $goods_id = I('goods_id');//商品ID
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
    
        $CartGoodsDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
    
        $Info = $CartGoodsDB->where(array('id'=>$cart_goods_id,'goods_id'=>$goods_id))->find();
    
        if(!$Info){
            returnjson(array('code'=>104,'data'=>'传入ID有误'), $this->returnstyle, $this->callback);
        }
        $Info['goods_image'] = json_decode($Info['goods_image'],true);
    
        returnjson(array('code'=>200,'data'=>$Info), $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 编辑购物车
     */
    public function CartGoodsOnceSave(){
        
        $cart_goods_id = I('cart_goods_id');//购物车商品ID
        $goods_id = I('goods_id');//商品ID
        $params['colour'] = I('colour');//颜色
        $params['num'] = I('num');//总数
        $params['size'] = I('size');//尺码
        $params['price'] = I('price');//单价
        $params['remarks'] = I('remark');//备注
        if(in_array('', $params) || $cart_goods_id == '' || $goods_id == ''){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $params['price'] = $params['price']*100;//转化分
        $CartGoodsDB = M('shopping_cart_goods',$this->admin_arr['pre_table']);
        
        $Info = $CartGoodsDB->where(array('id'=>$cart_goods_id,'goods_id'=>$goods_id))->find();
        
        if(!$Info){
            returnjson(array('code'=>104,'data'=>'传入ID有误'), $this->returnstyle, $this->callback);
        }
        
        if($Info['num'] != $params['num'] || $Info['price'] != $params['price']){
            $params['total_price'] = $params['num']*$params['price'];
        }
        
        $res = $CartGoodsDB->where(array('id'=>$cart_goods_id,'goods_id'=>$goods_id))->save($params);

        if($res!==false){
            $msg['code'] = 200;
        }else{
            $msg['code'] = 104;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 生成二维码接口
     */
    public function QrCode(){
        $params['id'] = I('id');
        
        $str = encrypt_zht($params['id']);
        
        returnjson(array('code'=>200,'data'=>$str), $this->returnstyle, $this->callback);
    }
    
    /**
     * 搜索订单列表
     */
    public function SearchOrderNoDataList(){
        $params['order_status'] = I('order_status');
        $params['floors'] = I('floors');//取货楼层
        $params['place_id'] = I('place_id');
        $params['poiNo'] = I('poiNo');
        $params['buildid'] = I('buildid');
        
        $params['pay_status'] = I('pay_status');
        
        if($params['pay_status'] == 'overdue' || $params['pay_status'] == 'unpaid'){
            $data = $this->getNewOrderList($this->ukey,$params['buildid'],$params['floors'],$params['poiNo'],$params['pay_status']);
        }else{
            $data = $this->getOrderList($this->ukey,$params['buildid'],$params['floors'],$params['poiNo'],$params['place_id'],$this->perInfo['id'],false,$params['order_status']);
        }
        
        if($data){
            foreach($data as $k=>$v){
                foreach($v['goods'] as $key=>$val){
                    $data[$k]['goods'][$key]['goods_image'] = json_decode($val['goods_image'],true);
                }
            }
            
            $msg['code'] = 200;
            $msg['data'] = $data;
        }else{
            $msg['code'] = 102;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    
    /**
     * （全部）待取件，接单->（代取件）送达->（待收货）没有按钮
     * 商场端获取订单列表
     * @param $keyAdmin
     * @param $buildId |建筑物id
     * @param $floor | 楼层
     * @param $poi | 店铺
     * @param $pickUpid | 自提点id
     * @param $per_id | 导购员
     * @param bool $isQueryAll | 是否查看所有快递员的订单信息，true是全部，false是自己的
     * @param bool $orderStatus | 订单状态，接单、送达、待收货等等
     * @return array|bool|mixed
     */
   
    private function getOrderList($keyAdmin, $buildId, $floor,$poi, $pickUpid='',$per_id = '', $isQueryAll = false, $orderStatus = false)
    {
        $adminInfo = PublicService::getMerchant($keyAdmin);
        if (isset($adminInfo['code'])) {
            return $adminInfo;
        }
        $db = M('shopping_order', $adminInfo['pre_table']);
        $ordertable = $adminInfo['pre_table'] . 'shopping_order';

        $where['buildid'] = array('eq',$buildId);
        $where['floor'] = array('eq',$floor);
        $where['poi'] = array('eq',$poi);
        
        if($pickUpid !=''){
            $where['place_id'] = array('eq',$pickUpid);
        }
        
        //状态
        if ($orderStatus){
            $where[$ordertable.'.status'] = array('eq',$orderStatus);
        }else{
            $where[$ordertable.'.status'] = array('egt',3);
        }
    
        //导购员
        if($per_id){
            $where['operator_guide'] = array('eq',$per_id);
        }
        
        $ordergoodstable = $adminInfo['pre_table'] . 'shopping_order_goods';
        $cartgoodstable = $adminInfo['pre_table'] . 'shopping_cart_goods';
        $membertable =  $adminInfo['pre_table'] . 'mem';
        //符合条件的订单信息
        $data = $db->where($where)->join($membertable.' on '.$membertable.'.cardno = '.$ordertable.'.cardno')->order($ordertable.'.status asc,'.$ordertable.'.createtime desc')->field($ordertable.'.*,usermember,mobile')->select();
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
    
    
    private function getNewOrderList($keyAdmin,$buildId,$floor,$poi,$status){
        
        $adminInfo = PublicService::getMerchant($keyAdmin);
        
        if (isset($adminInfo['code'])) {
            return $adminInfo;
        }
        $db = M('shopping_cart', $adminInfo['pre_table']);
        $db2 = M('shopping_cart_goods', $adminInfo['pre_table']);
        $carttable = $adminInfo['pre_table'] . 'shopping_cart';
        $cartgoodstable = $adminInfo['pre_table'] . 'shopping_cart_goods';
        
        $where['buildid'] = array('eq',$buildId);
        $where['floor'] = array('eq',$floor);
        $where['poi'] = array('eq',$poi);
        
        if($status == 'overdue'){
            $time = time()-3600*2;
            $where[$carttable.'.scantime'] = array('LT',$time);
        }elseif ($status == 'unpaid'){
            $where[$carttable.'.scantime'] = array('GT',0);
        }

        $where[$cartgoodstable.'.status'] = array('eq',1);
        
        $data = $db->where($where)->join($cartgoodstable.' on '.$carttable.'.id = '.$cartgoodstable.'.cart_id')->select();
        
        $id = array();
        $Alldata = array();
        $res = array();
        foreach($data as $k => $v){
            
            if(!in_array($v['cart_id'], $id)){
                $res['operator_guide'] = $v['operator_guide'];
                $res['openid'] = $v['openid'];
                $res['store_name'] = $v['store_name'];
                $res['store_id'] = $v['store_id'];
                $res['place_id'] = $v['place_id'];
                $res['buildid'] = $v['buildid'];
                $res['floor'] = $v['floor'];
                $res['poi'] = $v['poi'];
                $res['status'] = $status;
                $res['createtime'] = $v['createtime'];
                $res['cardno'] = $v['cardno'];
                $res['operator_guide_openid'] = $v['operator_guide_openid'];
                $res['id'] = $v['cart_id'];
                
                $id[] = $v['cart_id'];
                
                $goods_res = $this->goods_value($v);
                
                $res['goods'][] = $goods_res;
                
                $Alldata[] = $res;
                
                $res = array();
            }else{
                
                $goods_res = $this->goods_value($v);
                
                foreach($Alldata as $key=>$val){
                    if($val['id'] == $v['cart_id']){
                        $Alldata[$key]['goods'][] = $goods_res;
                    }
                }
            }
        }
        return $Alldata;
    }
    
    private function goods_value($v){
        $goods_res['cart_id'] = $v['cart_id'];
        $goods_res['goods_id'] = $v['goods_id'];
        $goods_res['goods_name'] = $v['goods_name'];
        $goods_res['goods_image'] = $v['goods_image'];
        $goods_res['goods_no'] = $v['goods_no'];
        $goods_res['colour'] = $v['colour'];
        $goods_res['size'] = $v['size'];
        $goods_res['num'] = $v['num'];
        $goods_res['price'] = $v['price'];
        $goods_res['total_price'] = $v['total_price'];
        $goods_res['status'] = $v['status'];
        $goods_res['remarks'] = $v['remarks'];
        $goods_res['goods_id'] = $v['goods_id'];
        
        return $goods_res;
    }
    
    /**
     * 订单打包接口
     */
    public function  OrderPackingAll(){
        $params['ordergoodsid'] = I('ordergoodsid');
        $params['operator_guide_openid'] = $this->perInfo['openId'];
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $orderDB = M('shopping_order',$this->admin_arr['pre_table']);
        $orderId = implode(',', $params['ordergoodsid']);
        
        $data = $orderDB->where(array('id'=>array('in',$orderId)))->select();
        
        if($data){
            $res = true;
            foreach($data as $k=>$v){
                if($v['status'] != 3){
                    $res = false;
                }
            }
            
            if(!$res){
                returnjson(array('code'=>104,'data'=>'传入ID有误'), $this->returnstyle, $this->callback);
            }
            
            $res1 = $orderDB->where(array('id'=>array('in',$orderId)))->save(array('status'=>4,'operator_guide_openid'=>$params['operator_guide_openid']));
            
            if(!$res1){
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    } 
    
    
    /**
     * 搜索商品列表（封装张强胜接口）
     */
    public function searchgoodsdata(){
        $params['searchInfo'] = I('searchInfo');
        $params['keyAdmin'] = $this->ukey;
        $params['buildId'] = I('buildId');
        $params['Floor'] = I('Floor');
        $params['poiNo'] = I('poiNo');
        $params['pageNum'] = I('pageNum')?I('pageNum'):1;
        $params['pageSize'] = I('pageSize')?I('pageSize'):0;
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $url = $this->api_url.'/dkpt/list_product_by_store';

        $return_data = http($url,$params);
        
        if(!is_json($return_data)){
            returnjson(array('code'=>3000), $this->returnstyle, $this->callback);
        }
        $data = json_decode($return_data,true);
        
        if($data['errcode'] != 0){
            $msg['code'] = 1082;
            $msg['msg'] = $data['errmsg'];
        }else{
            $msg['code'] = 200;
            $msg['data'] = $data['data'];
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 获取商品详情（封装张强胜接口）
     */
    public function GetgoodsInfo(){
        $params['keyAdmin'] = $this->ukey;
        $params['productId'] = I('pid');
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $url = $this->api_url.'/dkpt/get_product_detail';
        
        $return_data = http($url,$params);
        
        if(!is_json($return_data)){
            returnjson(array('code'=>3000), $this->returnstyle, $this->callback);
        }
        $data = json_decode($return_data,true);
        
        if($data['errcode'] != 0){
            $msg['code'] = 1082;
            $msg['msg'] = $data['errmsg'];
        }else{
            $msg['code'] = 200;
            $msg['data'] = $data['data'];
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 添加商品（封装张强胜接口）
     */
    public function insertgoods() {
        $params['buildId'] = I('buildId');//建筑物ID
        $params['floor'] = I('floor');//楼层
        $params['poiNo'] = I('poiNo');//店铺编号
        $params['productName'] = I('productName');//商品名称
        $params['price'] = I('price');//价格
        $params['photoList'] = I('photoList');//商品图片  如array('www.baidu.com','www.taobao.com')
        $params['keyAdmin'] = $this->ukey;
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $params['productDesc'] = I('productDesc');//使用说明  否
        $params['productSecondCategoryId'] = I('productSecondCategoryId');//商品二级分类 否
        $params['productTagIds'] = I('productTagIds');//商品标签ID 如8,10   否
        $params['itemNo'] = I('itemNo');//商品货号   否
        $params['barCode'] = I('barCode');//条码   否
        $params['startDate'] = date('Y-m-d');//有效起始时间
        $params['endDate'] = date('Y',time()) + 1 . '-' . date('m-d');//有效结束时间
        $params['pShow'] = I('pShow')?I('pShow'):true;//是否显示
        
        $url = $this->api_url.'/product';
        $json_data = json_encode($params);
        $return_data = http_auth($url,$json_data,'POST','',array('Content-Type:application/json'),true);

        if(!is_json($return_data)){
            returnjson(array('code'=>3000), $this->returnstyle, $this->callback);
        }
        $data = json_decode($return_data,true);
        
        if($data['errcode'] != 0){
            $msg['code'] = 1082;
            $msg['msg'] = $data['errmsg'];
        }else{
            $msg['code'] = 200;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 扫描二维码
     */
    public function ScanCode(){
        $params['order_no'] = I('order_no');
        $params['floors'] = I('floor');//取货楼层
        $params['poiNo'] = I('poiNo');
        $params['buildid'] = I('buildid');
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        writeOperationLog($params,'zhanghang');
        $order_no = decrypt_zht($params['order_no']);
        $db = M('shopping_order',$this->admin_arr['pre_table']);
        $info = $db->where(array('order_no'=>$order_no))->find();

        if(!$info){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        
        if($info['status']>=3){
            returnjson(array('code'=>1008), $this->returnstyle, $this->callback);
        }
        
        $res = $db->where(array('order_no'=>$order_no))->save(array('buildid'=>$params['buildid'],'floor'=>$params['floors'],'poi'=>$params['poiNo'],'operator_guide'=>$this->perInfo['id'],'status'=>3));
        
        if($res){
            $msg['code'] = 200;

            $price = $info['goods_price']/100;
            $price = sprintf('%.2f', $price).'元';
            //商场端
            $wechatMessageContent=array(array(
                'touser'=>$info['openid'],
                'template_id'=>'ImAIu4qbN4tH_sBJ0wf0O1aTqeryfhF6juysTawHmjs',
                'url'=>'',
                'data'=>array(
                    'first'=>array('value'=>'您的订单已确认！','color'=>'#173177'),
                    'keyword1'=>array('value'=>$price, 'color'=>'#173177'),
                    'keyword2'=>array('value'=>$info['order_no'], 'color'=>'#173177'),
                    'remark'=>array('value'=>'请等待货物送往自提点后，凭取货码自提取货。谢谢！', 'color'=>'#173177'),
                )
            ));
            //商户端
            $wechatMessageContent1=array(array(
                'touser'=>$this->perInfo['openId'],
                'template_id'=>'m9gz80jpfg4HU82cbEqWOiJGEShIN2c3BsSf_UkDf0U',
                'url'=>'',
                'data'=>array(
                    'first'=>array('value'=>'您好，顾客订单付款成功，请尽快进行打包出货。','color'=>'#173177'),
                    'keyword1'=>array('value'=>$info['store_name'], 'color'=>'#173177'),
                    'keyword2'=>array('value'=>$info['store_name'], 'color'=>'#173177'),
                    'keyword3'=>array('value'=>$price, 'color'=>'#173177'),
                    'keyword4'=>array('value'=>$info['createtime'], 'color'=>'#173177'),
                    'remark'=>array('value'=>'谢谢！', 'color'=>'#173177'),
                )
            ));
            
            $wechat = new TemplateController();
            $sendMessage = $wechat->insideSendMessage($wechatMessageContent, $this->ukey, $this->admin_arr['wechat_appid']);
            
            $sendMessage = $wechat->insideSendMessage($wechatMessageContent1, $this->info['ukey'], $this->info['wechat_appid']);
        }else{
            $msg['code'] = 104;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    } 
    
    
    /**
     * 上传图片
     */
    public function UpImage(){
        $params['media_id']=I('media_id');
        //$params['media_id']='-eiUsrRup0rDRSyBXPnszGWALRLkHb2znADm_m58j6IwDrSuFQqAUyxC4CdU9pXB';
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            
            $Image = new UpWechatImageController();
            
            foreach($params['media_id'] as $key=>$val){
                
                $ImageInfo = $Image->UpImageAction($val, $params['key_admin']);
                
                if($ImageInfo){
                    $data['success'][] = array('media_id'=>$val,'url'=>$ImageInfo);
                }else{
                    $data['error'][] = array('media_id'=>$val);
                }
            }
            
        }
        
        returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);exit();
    }
    
}