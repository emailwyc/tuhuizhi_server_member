<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 14/08/2017
 * Time: 14:51
 */

namespace MerAdmin\Service;


use Common\core\Singleton;
use MerAdmin\Model\HotelModel;

class HotelService
{
    public $model;
    public $pre_table;

    public function __construct($pre_table)
    {
        $this->pre_table = $pre_table;
        $this->model = Singleton::getModel('MerAdmin\\Model\\HotelModel',$pre_table);//客房订单model
    }


    /**
     * 酒店客房添加
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        $add = $this->model->add($data);
        return $add;
    }


    /**
     * 酒店客房上下架
     * @param $idlist
     * @param $status
     * @return mixed
     */
    public function changeSale($idlist, $status)
    {
        $change = $this->model->changeSale($idlist, $status);
        return $change;
    }


    /**
     * 客房列表
     * @param $params
     * @return mixed
     */
    public function roomsList($params, $page, $lines)
    {
        $data = $this->model->roomsList($params, $page, $lines);
        return $data;
    }

    /**
     * 逻辑删除某条
     * @param $id
     * @return mixed
     */
    public function delRoom($id)
    {
        $del = $this->model->delRoom($id);
        return $del;
    }


    public function onceRoom($id, $admininfo)
    {
        $Droom = new HotelModel($this->pre_table);
        $data = $Droom->relation(true)->where(array('id'=>$id, 'adminid'=>$admininfo['id']))->find();
        return $data;
    }


    public function editRoom($data)
    {
        $save = $this->model->edit($data);
        return $save;
    }


    /**
     * 客房订单列表
     * @param $params
     * @param $page
     * @param $lines
     * @param $admininfo
     * @return mixed
     */
    public static function orderList($params, $page, $lines, $admininfo)
    {
        $where['status'] = $params['status'];
        $where['adminid'] = $admininfo['id'];
        if ($params['startdate'] ){
            $where['startdate'] = array('egt', $params['startdate']);
        }
        if ($params['enddate']) {
            $where['enddate'] = array('elt', $params['enddate']);
        }
        if ($params['issuccess']) {
            $where['issuccess'] = $params['issuccess'];
        }

        $page= false != $page ? $page : 1;
        $lines= false != $lines ? $lines : 1;
        $start=($page-1)*$lines;
        $d = D('HotelOrder');
        if ($params['name']) {
//            $d->condition(" `name` like '%". $params['name'] ."%' ");//有结果，结果变了，但不是我想要的
            //获取哪些房型符合条件
            $dd = D('Hotel');
            $ids = $dd->field('id')->where(array('name'=>array('like', '%'. $params['name'] .'%')))->select();
            if ($ids) {
                $ids = array_column($ids, 'id');
                $where['roomid'] = array('in', $ids);
            }else{
                return $ids;
            }
        }
        $total = $d->relation(true)->where($where)->order('id desc')->count();
        if (false == $total){
            return false;
        }
        $data['total'] = $total;
        $data['page'] = $page;
        $data['totalpage'] = ceil($data['total'] / $lines);
        $data['data'] = $d->relation(true)->where($where)->limit($start, $lines)->order('id desc')->select();//echo $d->_sql();
        return $data;
    }


    /**
     * 某个订单信息
     * @param $admininfo
     * @param $id
     * @return mixed
     */
    public static function orderInfo($admininfo, $params)
    {
        $where['adminid'] = $admininfo['id'];
        $where['id'] = $params['id'];
        $d = D('MerAdmin/HotelOrder');
        $data = $d->relation(true)->where($where)->find();//echo  $d->_sql();
//        dump($data);
        //查询业主信息
        $dout = M('dkpt_user', '', 'DB_CONFIG2');
        $userInfo = $dout->where(array('id'=>$data['userid']))->find();//dump($userInfo);
        if($userInfo){
            //返回200,并返回用户身份信息;
            $logic = DD("User","",'Logic');
            $userInfo = $logic->FileUserBuildInfo($userInfo);
            $arr = $logic->PackageUserData($userInfo);
            $data['userinfo'] = $arr;
        }
//        $dout = M('dkpt_user', '', 'DB_CONFIG2');
//        $find = $dout->where(array('id'=>$data['userid']))->find();
        return $data;
    }


    /**
     * 订单处理
     * @param $params
     * @return mixed
     */
    public static function checkOrder($params, $id, $admininfo)
    {
        $d = D('HotelOrder');
        $data = $d->relation(true)->where(array('adminid'=>$admininfo['id'], 'id'=>$id))->save($params);
        return $data;
    }





}