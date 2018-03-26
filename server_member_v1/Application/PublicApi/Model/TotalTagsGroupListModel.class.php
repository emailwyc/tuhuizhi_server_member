<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 19/10/2017
 * Time: 19:00
 */

namespace PublicApi\Model;


use Think\Model\RelationModel;

class TotalTagsGroupListModel extends RelationModel
{
    protected $trueTableName = 'total_tags_groups';
    protected $_link = array(
        'themtagslist' => array(//获取列表
            'mapping_type'      =>  self::MANY_TO_MANY,
            'class_name'        =>  'TotalTags',
//            'mapping_name'      =>  'groups',
            'foreign_key'       =>  'groupid',
            'mapping_key'=>'id',
            'relation_foreign_key'  =>  'tagid',
            'relation_table'    =>  'total_tags_groups_tags' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        ),

    );
}