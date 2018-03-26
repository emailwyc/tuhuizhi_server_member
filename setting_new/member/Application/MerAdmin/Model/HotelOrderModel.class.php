<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 16/08/2017
 * Time: 13:59
 */

namespace MerAdmin\Model;


use Think\Model\RelationModel;

class HotelOrderModel extends RelationModel
{
    protected $tableName = 'total_hotel_order';

    protected $_link = array(
        'room' => array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'    => 'total_hotel_rooms',
            'foreign_key'   => 'roomid',
        ),
        'roominfo'=>array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'total_hotel_tag_setting',
            'foreign_key'   => 'pid',
            'mapping_key'    => 'roomid',
//            'mapping_fields'=>'id,type,content',
        ),
        'persons' => array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'total_hotel_order_person',
            'foreign_key'   => 'pid',
            'mapping_order' => 'id asc',
//            'mapping_fields'=>'id,personname',
        ),
    );
    public function condition($where){
        return $this->_link['room']['condition']=$where;
    }




}