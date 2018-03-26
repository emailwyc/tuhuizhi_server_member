<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 11/08/2017
 * Time: 14:04
 */

namespace MerAdmin\Controller;


use Common\core\Singleton;
use common\ServiceLocator;
use MerAdmin\Service\HotelService;

class HotelController extends AuthController
{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     *获取服务设施标签列表
     */
    public function getservicetag()
    {
        $service = ServiceLocator::getServiceTagService();
        $list = $service->getTaglist();
        if ($list){
            returnjson(array('code'=>200, 'data'=>$list), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle,$this->callback);
        }

    }


    /**
     * 酒店客房管理
     */
    public function addhotelroom()
    {
        $params['key_admin'] = I('key_admin');
        $params['name'] = I('name');//名称
        $params['issale'] = I('issale');//1上架，0下架
        $params['price'] = I('price');//价格
        $params['isbreakfast'] = I('isbreakfast');//是否含早餐
        $params['iswindow'] = I('iswindow');//是否有窗
        $params['bedtype'] = I('bedtype');//床型
        $params['isaddbed'] = I('isaddbed');//1可以加床，0不可以
        $params['size'] = I('size');//面积大小
        $params['direction'] = I('direction');//朝向
        $params['service']=I('service');
        $params['banner'] = I('banner');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        $params['description'] = I('description');//简介
        $admininfo = $this->getMerchant($params['key_admin']);
        $params['adminid'] = $admininfo['id'];
//         dump($params);die;
        $service = ServiceLocator::getHotelService($admininfo);
        $add = $service->add($params);
        if ($add){
            returnjson(array('code'=>200), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 客房上下架
     */
    public function sale()
    {
        $params['houseids'] = I('houseidlist');
        $params['issale'] = I('issale');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $service = ServiceLocator::getHotelService($admininfo);
        $change = $service->changeSale($params['houseids'], $params['issale']);
        if ($change !== false){
            returnjson(array('code'=>200), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 客房列表
     */
    public function roomlist()
    {
        $params['issale'] = I('issale');
        $params['name'] = I('name');
        $params['priceorder'] = I('priceorder');
        $params['page'] = I('page');
        $params['lines'] = I('lines');
        $admininfo = $this->getMerchant($this->ukey);
        $service = ServiceLocator::getHotelService($admininfo);
        if ($params['issale'] === ''){
            unset($params['issale']);
        }
        $data = $service->roomsList($params, $params['page'], $params['lines']);
        if ($data){
            returnjson(array('code'=>200, 'data'=>$data), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 删除
     */
    public function roomdel()
    {
        $params['id'] = I('id');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $service = ServiceLocator::getHotelService($admininfo);
        $del = $service->delRoom((int)$params['id']);
        //删除是逻辑删除
        if ($del !== false){
            returnjson(array('code'=>200), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 获取一个房间信息
     */
    public function getoneroom()
    {
        $params['id'] = I('id');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $service = ServiceLocator::getHotelService($admininfo);
        $data = $service->onceRoom($params['id'], $admininfo);
        if ($data){
            returnjson(array('code'=>200, 'data'=>$data), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 客房编辑
     */
    public function editroom()
    {
        $params['id'] = I('id');
        $params['key_admin'] = I('key_admin');
        $params['name'] = I('name');//名称
        $params['issale'] = I('issale');//1上架，0下架
        $params['price'] = I('price');//价格
        $params['isbreakfast'] = I('isbreakfast');//是否含早餐
        $params['iswindow'] = I('iswindow');//是否有窗
        $params['bedtype'] = I('bedtype');//床型
        $params['isaddbed'] = I('isaddbed');//1可以加床，0不可以
        $params['size'] = I('size');//面积大小
        $params['direction'] = I('direction');//朝向
        $params['description'] = I('description');//简介
        $params['service']=I('service');
        $params['banner'] = I('banner');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $params['adminid'] = $admininfo['id'];
        $service = ServiceLocator::getHotelService($admininfo);
        $save = $service->editRoom($params);
        if ($save){
            returnjson(array('code'=>200), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 订单列表
     */
    public function orderlist()
    {
        $params['startdate'] = (int)I('startdate');
        $params['enddate'] = (int)I('enddate');
        $params['status'] = I('status') ? : 0;//0未回访，1已回访
        $params['issuccess'] = I('issuccess');
        $params['name'] = I('name');
        $page = I('page') ? : 1;
        $lines = I('lines') ? : 1;
        $admininfo = $this->getMerchant($this->ukey);
        $data = HotelService::orderList($params, $page, $lines, $admininfo);
        if ($data){
            returnjson(array('code'=>200, 'data'=>$data), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle,$this->callback);
        }
    }


    /**
     * 订单详情
     */
    public function getorderinfo()
    {
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $data = HotelService::orderInfo($admininfo, $params);
        if ($data){
            returnjson(array('code'=>200, 'data'=>$data), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }
    }


    public function checkorder()
    {
        $params['id'] = I('orderid');
        $params['issuccess'] = (int)I('issuccess');
        if (in_array('', $params, true)) {
            returnjson(array('code'=>1030), $this->returnstyle,$this->callback);
        }
        if (!in_array($params['issuccess'], array(1,2,3,4))){
            returnjson(array('code'=>1051), $this->returnstyle,$this->callback);
        }
        $params['content'] = I('content') ? :'';

        $admininfo = $this->getMerchant($this->ukey);
        $id = $params['id'];
        unset($params['id']);
        $params['status'] = 1;
        $data = HotelService::checkOrder($params, $id, $admininfo);
        if ($data !== false){
            returnjson(array('code'=>200), $this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle,$this->callback);
        }

    }











}