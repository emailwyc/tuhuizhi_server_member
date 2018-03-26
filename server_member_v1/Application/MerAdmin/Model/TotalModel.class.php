<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 14/08/2017
 * Time: 17:47
 */

namespace MerAdmin\Model;


use common\MSDaoBase;

class TotalModel extends MSDaoBase
{
    public $db;
    public function __construct()
    {
        $this->db = M('total_hotel_tag');
    }

    /**
     * 酒店服务标签
     * @return mixed
     */
    public function hotelTags()
    {
        $sel = $this->db->field('name')->select();
        return $sel;
    }
}