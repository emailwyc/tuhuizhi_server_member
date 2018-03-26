<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 15/08/2017
 * Time: 19:14
 */

namespace House\Model;


use Think\Model\RelationModel;

class HotelOrderModel extends RelationModel
{
    protected $tableName = 'total_hotel_order';

    protected $_link = array(
        'persons' => array(
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'total_hotel_order_person',
            'foreign_key'   => 'pid',
            'mapping_order' => 'id desc',
        ),
    );
}