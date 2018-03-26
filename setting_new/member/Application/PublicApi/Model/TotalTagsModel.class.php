<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 18/10/2017
 * Time: 11:10
 */

namespace PublicApi\Model;


use Think\Model\RelationModel;

class TotalTagsModel extends RelationModel
{


    protected $_link = array(
        'tagsadd' =>array(//添加时使用
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'TotalTagsMenu',
            'foreign_key'   => 'tagsid',
//            'mapping_key'  => 'id',
        ),
        'tagslist' => array(//获取列表
            'mapping_type'      =>  self::MANY_TO_MANY,
            'class_name'        =>  'TotalAuth',
//            'mapping_name'      =>  'groups',
            'foreign_key'       =>  'tagsid',
            'mapping_key'=>'id',
            'relation_foreign_key'  =>  'menuid',
            'relation_table'    =>  'total_tags_menu' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        )
    );

    public function condition($where){
        return $this->_link['tagslist']['condition']=$where;
    }
}