<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 14/08/2017
 * Time: 17:51
 */

namespace MerAdmin\Service;


use Common\core\Singleton;

class TotalService
{
    public $model;
    public function __construct($pre_table)
    {
        $this->model = Singleton::getModel('MerAdmin\\Model\\TotalModel',$pre_table);//客房订单model
    }

    /**
     * 酒店服务标签列表
     * @return mixed
     */
    public function getTaglist()
    {
        $list = $this->model->hotelTags();
        return $list;
    }
}