<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 19/10/2017
 * Time: 14:38
 */

namespace MerAdmin\Model;


use Think\Model\RelationModel;

class TotalTagsThemsModel extends RelationModel
{
    protected $_link = array(
        'tags' =>array(//添加时使用
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'TotalTagsThemsTags',
            'foreign_key'   => 'themsid',
//            'mapping_key'  => 'id',
        ),
        'banners' =>array(//添加时使用
            'mapping_type'  => self::HAS_MANY,
            'class_name'    => 'TotalTagsThemsBanners',
            'foreign_key'   => 'themsid',
//            'mapping_key'  => 'id',
        ),
    );
}