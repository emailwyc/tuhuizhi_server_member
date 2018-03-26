<?php
namespace ClientApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
use Pay\Service\PayApiService;

class ShoppingController extends ClientCommonController
{
    public function _initialize(){
        parent::_initialize();
        //$params = $this->params;
        $this->curtime = time();
    }

    /**
     * 通过openid来判断是否数据是否存在(用来避免同一个微信号绑定多个手机号)
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
        $this->emptyCheck($params,array('orderid','openid'));
        $orderid = (int)decrypt($params['orderid']);
        $db = M('shopping_record', $this->setting['pre_table']);
        $db1 = M('shopping_status', $this->setting['pre_table']);
        $db2 = M('shopping_order', $this->setting['pre_table']);
        $db3 = M('customer_list', $this->setting['pre_table']);
        //check orderid
        $userInfo = $this->checkUserExists($this->setting['pre_table'],$params['openid']);
        $orderInfo = $db2->where(array('id'=>$orderid))->find();
        if(!$orderInfo || $userInfo['cardno']!=$userInfo['cardno']){
            returnjson(array('code'=>1082,'msg'=>"无效订单，请联系店员！"), $this->returnstyle, $this->callback);
        }
        //返回商品数量
        $where = array('order_id'=>$orderid,'status'=>0);
        $count = $db->field("id")->where($where)->count();
        if($count<=0){
            returnjson(array('code'=>1082,'msg'=>"该二维码已经使用，不可重复使用！"), $this->returnstyle, $this->callback);
        }
        //更新状态
        $db->where($where)->save(array('status'=>1,'scantime'=>$this->curtime));
        $db2->where(array('id'=>$orderid))->save(array('scantime'=>$this->curtime,'openid'=>$params['openid']));
        $db1->add(array('order_id'=>$orderid,'status'=>1));
        //更新数量
        $db3->where(array("poi_id"=>$orderInfo['store_id'],"member_cardno"=>$orderInfo['cardno']))->save(array('shop_num'=>0));

        $msg = array('code'=>200,'data'=>$count);
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
        $db = M('shopping_record', $this->setting['pre_table']);
        $db1 = M('shopping_status', $this->setting['pre_table']);
        $db2 = M('shopping_order', $this->setting['pre_table']);
        $timelimit = $this->curtime-86400*30;
        //查询该客户两个小时内订单(目前取出近1个月的，最多100个订单，购物车无分页)
        $where = array('openid'=>$params['openid'],'scantime'=>array('egt',$timelimit));
        $fields = "id,order_no,openid,store_name,scantime";
        $orderList = $db2->field($fields)->where($where)->order("scantime desc")->limit(0,100)->select();
        $orderids = ArrKeyAll($orderList,'id',false);
        if(empty($orderids)){
            returnjson(array('code'=>200,'data'=>array()), $this->returnstyle, $this->callback);
        }
        $orderList = ArrKeyFromId($orderList,'id');
        //根据订单查询订单详情
        $where1 = array('order_id'=>array('in',$orderids),'status'=>1);
        $orderRecord = $db->where($where1)->order("id desc")->select();
        if(empty($orderRecord)){
            returnjson(array('code'=>200,'data'=>array()), $this->returnstyle, $this->callback);
        }
        //组装整理订单数据
        foreach ($orderRecord as $k=>$v){
            $orderList[$v['order_id']]['record'][] = $v;
            $orderList[$v['order_id']]['remaintime'] = $this->curtime-$orderList[$v['order_id']]['scantime'];
        }
        $orderList = ArrObjChangeList($orderList);
        returnjson(array('code'=>200,'data'=>$orderList), $this->returnstyle, $this->callback);
    }

    //删除购物车物品
    public function delShopCarGoods() {
        $params = $this->params;
        $this->emptyCheck($params,array('record_id'));
        $db = M('shopping_record', $this->setting['pre_table']);
        $db->where(array("id"=>(int)$params['record_id'],"status"=>1))->delete();
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    ////更新订单上的自提点
    public function updateOrderPlace() {
        $params = $this->params;
        $this->emptyCheck($params,array('order_id_list','place_id'));
        $db = M('shopping_order', $this->setting['pre_table']);
        $db1 = M('shopping_place', $this->setting['pre_table']);
        //检查是否有该自提点
        $placeInfo = $db1->where(array("id"=>$params['place_id']))->find();
        if(empty($params['order_id_list'])){
            returnjson(array('code'=>1082,'msg'=>"订单ID不可为空！"), $this->returnstyle, $this->callback);
        }
        if(empty($placeInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找到该自提点！"), $this->returnstyle, $this->callback);
        }
        $db->where(array("id"=>array('in',$params['order_id_list'])))->save(array("place_id"=>$params['place_id']));
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    ////下单微信支付
    public function paybyweixin() {
        $params = $this->params;
        $this->emptyCheck($params,array('record_id_list','openid'));
        if(empty($params['record_id_list'])){
            returnjson(array('code'=>1082,'msg'=>"参数有误！"), $this->returnstyle, $this->callback);
        }
        $db = M('shopping_record', $this->setting['pre_table']);
        //查询订单计算总金额，并且判断订单状态,是否超时（）;
        $recordsInfo = $db->where(array("id"=>array('in',$params['record_id_list'])))->select();
        if(empty($recordsInfo)){
            returnjson(array('code'=>1082,'msg'=>"未找订单商品！"), $this->returnstyle, $this->callback);
        }
        $total_price = 0;
        foreach ($recordsInfo as $key=>$val){
            $total_price += $val['total_price'];
            if(in_array($val['status'],array(1,2))){
                returnjson(array('code'=>1082,'msg'=>"订单状态有误！"), $this->returnstyle, $this->callback);
            }
        }

        //调用支付接口
        if($total_price<=0){
            //更新订单状态并返回
            $updateArr = array("status"=>3,'paytime'=>date('Y-m-d H:i:s',time()));
            $db->where(array("id"=>array('in',$params['record_id_list'])))->save($updateArr);
            $msg = array('code'=>200,'data'=>array('total_price'=>$total_price));
            returnjson($msg, $this->returnstyle, $this->callback);
        }else{
            //请求支付接口
        }
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    //微信下单支付回调




}

?>
