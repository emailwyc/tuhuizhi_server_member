<?php
namespace Commands\Controller;

use Common\Controller\RedisController;

/**
 * 计划任务控制器
 *
 */
class ScheduleController
{
    //linux执行 crontab -e 加入计划任务
    //* * * * * /usr/bin/php /var/www/html/ka/cliservice.php Commands/Schedule/exec
    
    /**
     * 当前周数
     *
     * @var int
     */
    private $currWeek = null;
    /**
     * 当前小时数
     *
     * @var int
     */
    private $currHour = null;
    /**
     * 当前分钟数
     *
     * @var int
     */
    private $currMinute = null;
    
    /**
     * 预处理和检查
     *
     */
    public function __construct($execTime = null)
    {
        if (!$execTime) $execTime = time();
        $this->currWeek = date('N', $execTime);
        $this->currHour = date('G', $execTime);
        $this->currMinute = date('i', $execTime);
        
        $redis_con = new RedisController();
        $this->redis = $redis_con->connectredis();
    }

    /**
     * 用于相应单个命名调用请求的魔术方法
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (!method_exists($this, $method))
        {
            return null;
        }

        return call_user_func_array(array($this, $method), array($this->params));
    }

    /**
     * 执行计划任务并记录异常
     *
     * @param string    $taskName   任务名
     * @param int|null  $execWeek   周周期
     * @param int|null  $execHour   小时周期
     * @param int|null  $execMinute 分钟周期
     */
    private function doTask($taskName, $execWeek, $execHour, $execMinute)
    {
        if (($execWeek === null || $this->currWeek == $execWeek) &&
            ($execHour === null || $this->currHour == $execHour) &&
            ($execMinute === null || $this->currMinute == $execMinute))
        {
            try
            {
                $this->$taskName();
                $this->markEnd($taskName);
            }
            catch (\Exception $exception)
            {
                $errorMsg = "Error Time: {$GLOBALS['NOW_DATE']}\n";
                $errorMsg .= "{$exception}\n\n";

                //$this->writeLog($errorMsg);
            }
        }
    }

    /**
     * 定时执行的计划任务
     *
     */
    public function exec()
    {
        // 统计页面访问量（每小时）
        $this->doTask('totalePagepv', null, null, 59);

         //每天05:01 
//       $this->doTask('clearAttackNum', null, 5, 1);
         //每天00:00分 
//       $this->doTask('resetUserExtend', null, 0, 0);
         //每天02:00分 
//       $this->doTask('renewFilterWords', null, 2, 0);
         //每周一晚00:00分 
//       $this->doTask('resetTaskDailyStar', 1, 0, 0);

    }

    /**
     * 统计页面访问并入库
     */
    private function totalePagepv()
    {
        $user = M('total_admin');
        $pagepv = M('total_pagepv');
        $catalog = M('catalog','total_');
        $res = $user->where('')->select();
        
        if(!empty($res))
        {
            foreach ($res as $k => $v)
            {
                $key = getPagepvKey($v['id']);
                $data = $this->redis->get($key);
                
                //当前小时访问量
                if($data)
                {
                    $info = json_decode($data, true);
                    
                    foreach ($info as $k2 => $v2)
                    {
                        if(!empty($k2) && !empty($v2))
                        {
                            $arr = $catalog->field('name')->where(array('page_mark' => $k2))->find();
                            $insert = array('adminid' => $v['id'], 'num' => $v2,'name' => $arr['name'], 'rote' => $k2, 'ctime' => date('Y-m-d H:i;s', time()));
                            $pagepv->add($insert);
                        }
                    }
                    
                    $this->redis->del($key);
                }
            }
        }
        
        return true;
    }

    private function markEnd($func)
    {
        echo "[ ", $GLOBALS['NOW_DATE'], " ][ ", $func, " Success ] \n";
    }
    
    /**
     * 远程执行维护命令接口
     *
     */
    public function remoteExec()
    {
        ini_set('display_errors','On');
    
        if (empty($_SERVER['argv'][2]))
        {
            die("\n!! Command error!\n\n");
        }
    
        $command = $_SERVER['argv'][2];
        $cmdInfo = pathinfo($command);
    
        $fileName = $this->mergePath(ROOT_PATH, 'webroot', $command);
    
        if ($cmdInfo['extension'] == 'php')
        {
            echo "\n++ Execute php file ... \n";
    
            include($fileName);
            unlink($fileName);
    
            echo "++ OK\n\n";
        }
        elseif ($cmdInfo['extension'] == 'sql')
        {
            echo "\n++ Execute sql file ... \n";
    
            $mysqlCmd = '/usr/bin/mysql --default-character-set=utf8 -u ' . DB_USER . ' -p' . DB_PASS . ' -h ' . DB_HOST . ' -P ' . DB_PORT . ' ' . DB_LIBR . ' < '.$fileName."\n";
            passthru($mysqlCmd);
            unlink($fileName);
    
            echo "++ OK\n\n";
        }
        else
        {
            echo "\n++ Execute command '{$command}' ... \n";
    
            passthru('webexec '.SERVER_MARK.' '.$command);
    
            echo "++ OK\n\n";
        }
    }
    
    /**
     * 合并路径
     *
     * @return string
     */
    public static function mergePath ()
    {
        return implode(DIRECTORY_SEPARATOR, func_get_args());
    }
}
