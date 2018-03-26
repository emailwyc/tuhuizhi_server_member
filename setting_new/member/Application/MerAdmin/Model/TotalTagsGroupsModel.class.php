<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 19/10/2017
 * Time: 14:38
 */

namespace MerAdmin\Model;


use Think\Model\RelationModel;

class TotalTagsGroupsModel extends RelationModel
{
    protected $_link = array(
        'tags' =>array(//添加时使用
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'TotalTagsGroupsTags',
            'foreign_key'   => 'groupid',
//            'mapping_key'  => 'id',
        ),
    );
}