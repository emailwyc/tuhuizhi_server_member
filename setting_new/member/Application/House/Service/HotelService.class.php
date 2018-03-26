<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 15/08/2017
 * Time: 19:01
 */

namespace House\Service;


class HotelService
{

//    public static function orderlist()
//    {
//        $d = D('HOtelOrder');
//        $data = $d->relation('order')->select;
//        return $data;
//    }



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
//        $where['adminid'] = $admininfo['id'];
//        if ($params['startdate'] ){
//            $where['startdate'] = array('gt', $params['startdate']);
//        }
//        if ($params['enddate']) {
//            $where['enddate'] = array('lt', $params['enddate']);
//        }
//        if ($params['issuccess']) {
//            $where['issuccess'] = $params['issuccess'];
//        }
//
        $page= false != $page ? $page : 1;
        $lines= false != $lines ? $lines : 1;
        $start=($page-1)*$lines;
        $d = D('MerAdmin/HotelOrder');
//        if ($params['name']) {
////            $d->condition(" `name` like '%". $params['name'] ."%' ");//有结果，结果变了，但不是我想要的
//            //获取哪些房型符合条件
//            $dd = D('Hotel');
//            $ids = $dd->field('id')->where(array('name'=>array('like', '%'. $params['name'] .'%')))->select();
//            if ($ids) {
//                $ids = array_column($ids, 'id');
//                $where['roomid'] = array('in', $ids);
//            }else{
//                return $ids;
//            }
//        }
        $userInfo = DD("DkptUser")->getUserByOpenid($admininfo['ukey'],$params['userucid']);//dump($userInfo);die;
        $where['userid'] = $userInfo['id'];
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


    public static function addOrder($data)
    {
        $d = D('HotelOrder');
        $re = $d->relation(true)->add($data);
        return $re;
    }
}