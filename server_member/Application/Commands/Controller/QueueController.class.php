<?php
/**
 *redis队列
 */
namespace Commands\Controller;

use MerAdmin\Controller\CouponController;
use Common\Controller\RedisController;

class QueueController 
{
    private $redis;
    private $_count = 1;//一次处理条数
    private $_queue_name = 'webstar:couponlist:callback:queue';
    
    public function __construct(){
        $redis_con = new RedisController();
        $this->redis = $redis_con->connectredis();
    }
    
    /*
     * 发互动券操作
     * nohup php cliservice.php Commands/Queue/run  >> queuedata.log &
     */
    public function run ()
    {
        $redis = $this->redis;
        
        if ($redis == false)
        {
            writeOperationLog('redis连接失败','queue');//记录日志
            return false;
        }
        
        while (true)
        {
            $list = $this->_getList();
    
            if (!empty($list))
            {
                // 存取纪录
                foreach ($list as $_k => $_v)
                {
                    // 解析数据
                    $data = json_decode($_v, true);
    
                    // 处理队列方法
                    $queueMethod = 'doQueue';
    
                    if ($data && $data['openId'])
                    {
                        $this->$queueMethod($data);
                    }
                }
            }
            else
            {
                usleep(10);// 无数据休眠10秒
            }
         }
    }
    
    /**
     * 从队列取出数据
     *
     * @return array
     */
    private function _getList ()
    {
        $redis = $this->redis;
    
        if ($redis == false)
        {
            $arr['message'] = 'redis连接失败';
            writeOperationLog($arr, 'queue');//记录日志
            return false;
        }
        
        $list = array();
        for ($i = 0; $i < $this->_count; $i ++)
        {
            $data = $this->redis->rPop($this->_queue_name);
    
            if (empty($data))
            {
                break;
            }
            $list[] = $data;
        }
    
        return $list;
    }
    
    /**
     * 发送活动券
     * @param array $data
     */
    private function doQueue($data)
    {
        $res = http("http://182.92.31.114/rest/act/".$data['activityId']."/".$data['openId'],array());////101.201.176.54/rest/act/activity/openid
        
        $resArr = json_decode($res, true);
        $arr['message'] = $resArr;
        writeOperationLog($arr,'queue');//记录日志
        
        return true;
    }
    
}

?>