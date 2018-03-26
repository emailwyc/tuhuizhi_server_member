<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 20/09/2017
 * Time: 17:36
 */

namespace Common\Service;

class RedisService
{
    public static function connectredis($num = 1)
    {
        $redis=new \Redis();
        $redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
        $redis->auth(C('REDIS_AUTH'));
        $redis->select($num);
        return $redis;
    }
}