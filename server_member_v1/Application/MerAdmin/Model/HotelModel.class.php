<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 14/08/2017
 * Time: 14:54
 */

namespace MerAdmin\Model;

use Think\Model\RelationModel;

class HotelModel extends RelationModel
{
    const   TABLENAME = 'total_hotel_rooms';
    public $db;
    public $hoteltag;
    public $totaltag;
    protected $tableName = 'total_hotel_rooms';
    protected $admininfo;
    public function __construct($admininfo)
    {
        $this->db = M(self::TABLENAME);
        $this->hoteltag = M('total_hotel_tag_setting');//服务项、图片表
        $this->admininfo = $admininfo;
    }
    protected $_link = array(
        'Hoteltag' =>array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'total_hotel_tag_setting',
            'foreign_key'   => 'pid',
            'mapping_name'  => 'articles',
        ),
    );


    /**
     * 添加一条
     */
    public function add($data)
    {
        $services = $data['service'];
        $banner = $data['banner'];
        unset($data['services']);
        unset($data['banner']);
        $this->db->startTrans();
        $this->hoteltag->startTrans();
        $add = $this->db->add($data);//dump($services);die;
        if ($add) {
            $servicesarr = null;
            foreach ($services as $item) {//dump($item);die;
                $servicesarr[] = array(
                    'type' => 1,//1是服务设施
                    'pid' =>$add,
                    'ischoose' => $item['ischoose'],
                    'content' => $item['content']
                );
            }
            $addtag = $this->hoteltag->addAll($servicesarr);
            if ($addtag) {
                $bannerarr = null;
                foreach ($banner as $item) {
                    $bannerarr[] = array(
                        'type' => 2,//2是banner图片
                        'pid' =>$add,
                        'content' => $item
                    );
                }
                $addbanner = $this->hoteltag->addAll($bannerarr);
                if ($addbanner) {
                    $this->db->commit();
                    $this->hoteltag->commit();
                    return true;
                }else{
                    $this->db->rollback();
                    $this->hoteltag->rollback();
                    return false;
                }
            }else{
                $this->db->rollback();
                $this->hoteltag->rollback();
                return false;
            }
        }else{
            $this->db->rollback();
            return false;
        }
    }


    /**
     * 酒店客房上下架
     * @param $idlist
     * @param $status
     * @return bool
     */
    public function changeSale($idlist, $status)
    {
        $change = $this->db->where(array('id'=>array('in', $idlist),'adminid'=>$this->admininfo['id']))->save(array('issale'=>$status));
        return $change;
    }

    /**
     * 酒店列表
     * @param $params
     * @return bool|mixed
     */
    public function roomsList($params, $page=1, $lines=1)
    {
        $page= false != $page ? $page : 1;
        $lines= false != $lines ? $lines : 1;
        $start=($page-1)*$lines;
        if (isset($params['issale']) ){
            $where['issale'] = $params['issale'];
        }
        if ($params['name']){
            $where['name'] = array('like','%'.$params['name'].'%');
        }
        $where['isdel'] = 0;
        $where['adminid'] = $this->admininfo['id'];
        if (isset($params['priceorder']) && ($params['priceorder'] == 'asc' || $params['priceorder'] == 'desc') ) {
            $order = ' price '. $params['priceorder'];
        }else{
            $order = ' id desc ';
        }

        //总数
        $total = $this->db->where($where)->order($order)->count();//echo  $this->db->_sql();
        if (false == $total){
            return false;
        }
        $data['total'] = $total;
        $data['page'] = $page;
        $data['totalpage'] = ceil($data['total'] / $lines);

        $data['data'] = $this->db->where($where)->order($order)->limit($start, $lines)->select();//echo  $this->db->_sql();
        if (!$data['data']){
            return false;
        }
        $idlist = array_column( $data['data'], 'id');
        $tag = $this->hoteltag->where(array('pid'=>array('in', $idlist), 'type'=>2))->select();//echo $this->hoteltag->_sql();
        foreach ($data['data'] as $key => $value){
            foreach ($tag as $k => $v){
                if ($v['pid'] == $value['id']){
                    $data['data'][$key]['banner'][] = $v;
                }
            }
        }
        return $data;
    }


    /**
     * 删除不做物理删除
     * @param $id
     * @return bool
     */
    public function delRoom($id)
    {
        $del = $this->db->where(array('id'=>$id,'adminid'=>$this->admininfo['id']))->save(array('isdel'=>1));
        return $del;
    }


    public function edit($data) {

        $services = $data['service'];
        $banner = $data['banner'];
        unset($data['services']);
        unset($data['banner']);
        $this->db->startTrans();
        $this->hoteltag->startTrans();
        $save = $this->db->where(array('id'=>$data['id'],'adminid'=>$this->admininfo['id']))->save($data);
        if ($save !== false) {
            $this->hoteltag->where(array('pid'=>$data['id']))->delete();
            $servicesarr = null;
            foreach ($services as $item) {
                $servicesarr[] = array(
                    'type' => 1,//1是服务设施
                    'pid' =>$data['id'],
                    'ischoose' => $item['ischoose'],
                    'content' => $item['content']
                );
            }
            $addtag = $this->hoteltag->addAll($servicesarr);
            if ($addtag) {
                $bannerarr = null;
                foreach ($banner as $item) {
                    $bannerarr[] = array(
                        'type' => 2,//2是banner图片
                        'pid' =>$data['id'],
                        'content' => $item
                    );
                }
                $addbanner = $this->hoteltag->addAll($bannerarr);
                if ($addbanner) {
                    $this->db->commit();
                    $this->hoteltag->commit();
                    return true;
                }else{
                    $this->db->rollback();
                    $this->hoteltag->rollback();
                    return false;
                }
            }else{
                $this->db->rollback();
                $this->hoteltag->rollback();
                return false;
            }
        }else{
            $this->db->rollback();
            return false;
        }
    }
    
}