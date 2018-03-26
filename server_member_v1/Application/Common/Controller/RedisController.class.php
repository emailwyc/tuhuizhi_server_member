<?php
namespace Common\Controller;

use Think\Controller;

//以后可以优化成单例模式
class RedisController extends Controller{

    // TODO - Insert your code here
    
//     public function __construct(){
//         $redis=new \Redis();
//         $redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
//         $redis->auth(C('REDIS_AUTH'));
//         $redis->select(0);
//         return $redis;
//     }
    
    /**
     * 连接redis
     * $dbnum redis数据库信息，前端用0了，那我用1吧，免得弄混
     */
    public function connectredis($dbnum=1){
        $redis=new \Redis();
        $redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
        $redis->auth(C('REDIS_AUTH'));
        $redis->select($dbnum);
        return $redis;
    }
}

?>