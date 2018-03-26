<?php
namespace common;
use Common\Controller\RedisController;

class MSDaoBase
{
    private $redis;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $redis_con = new RedisController();
        $this->redis = $redis_con->connectredis();
    }

}
