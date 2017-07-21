<?php
namespace Platformservice\Controller;

use Think\Controller;
use Common\Controller\RedisController;

/**
 * 每日定时任务类，不需要登陆
 * @author ut
 *
 */
class CliController extends Controller
{
    protected $redis;
    public function __construct(){
        $re = new RedisController();
        $this->redis=$re->connectredis();
    }
    // TODO - Insert your code here
    /**
     * 每日凌晨执行定时任务，将ｐｖ数据保存到ｍｙｓｑｌ
     */
    public function getpv_from_redis(){
        
        $dbmca=M('pv_name','total_','DB_CONFIG1');
        $mcaname=$dbmca->select();
        $mcanamearr='';
        foreach ($mcaname as $k => $v){
            $mcanamearr[$v['url']]=$v['name'];//组合成一维数组
        }
        $date=date('Y-m-d',strtotime('-1 day'));
        $keys = $this->redis->keys($date.'--*');
        $db=M('pv','total_','DB_CONFIG1');
        $result=$this->savedata($keys,$mcanamearr,$db, $date);
    }
   
    /**
     * 查询某一天的接口访问量，日期为传入日期
     * @param unknown $keys
     * @param unknown $mcanamearr
     * @param unknown $db
     * @param unknown $date
     */
    private function savedata($keys,$mcanamearr,$db,$date,$cut=2,$isstop=0){
        $array='';
        //如果ｋｅｙ有值，循环redis的key
        if (null != $keys){
            foreach ($keys as $key => $val){
                $nums = $this->redis->get($val);//获取这个key的值
                $arr=explode('--',$val);
                $tmp=array_key_exists($arr[1],$mcanamearr) ? 1 : 0;
                if (0==$tmp){
                    $url_name_array=strtolower($arr[1]);//全部转小写
                    $dbnames=M('pv_name','total_','DB_CONFIG1');
                    $sel=$dbnames->where(array('url'=>$url_name_array))->select();
                    if (null == $sel){
                        $dbnames->add(array('url'=>$url_name_array));
                    }
                }
                
                $name= 1 ==$tmp?$mcanamearr[$arr[1]] : '暂无注释';
                $array[]=array('date'=>$date,'url_name'=>$arr[1],'nums'=>$nums,'name'=>$name);
                $this->redis->del($val);
                unset($arr);
            }
            $json=json_encode($array);
            $data['date']=$date;
            $data['names']=$json;
            $sel=$db->where(array('date'=>$date))->find();
            if (null == $sel){
                $db->add($data);
            }else{
                $db->where(array('id'=>$sel['id']))->save($data);
            }
        }
        //达到最大指数
        if (10 <= $isstop){
            return true;
        }
        $date=date('Y-m-d',strtotime('-'.$cut.' day'));
        $keys=$this->redis->keys($date.'--*');
        if (null == $keys){
            $isstop++;
        }
        //递归
        $this->savedata($keys, $mcanamearr, $db, $date,$cut+1,$isstop);
    }
    
    
    
    
    /**
     * 将每日的微信授权量从redis更新到数据库
     * 时间为每天凌晨00:00:00
     */
    public function wechatoathnum()
    {
        //先获取所有的key
        $yesterday=date('Y-m-d',strtotime("-1 day"));
        $other='other:'.$yesterday;
        $android='android:'.$yesterday;
        $iphone='iphone:'.$yesterday;
        $ipod='ipod:'.$yesterday;
        $ipad='ipad:'.$yesterday;
        $macosx='macosx:'.$yesterday;
        
        $rediss=new RedisController();
        $redis= $rediss->connectredis(2);
        
        //获取数量
        $data['othercount']=$redis->get($other);
        $data['androidcount']=$redis->get($android);
        $data['iphonecount']=$redis->get($iphone);
        $data['ipodcount']=$redis->get($ipod);
        $data['ipadcount']=$redis->get($ipad);
        $data['macosxcount']=$redis->get($macosx);
        $data['date']=$yesterday;
        $data['todaytotal']=(int)$data['othercount']+(int)$data['androidcount']+(int)$data['iphonecount']+(int)$data['ipodcount']+(int)$data['ipadcount']+(int)$data['macosxcount'];
        
        //所有历史总和
        $androidnum=$redis->get('android');
        $iphonenum=$redis->get('iphone');
        $macosxnum=$redis->get('macosx');
        $ipadnum=$redis->get('ipad');
        $othernum=$redis->get('other');
        $ipodnum=$redis->get('ipod');
        $count=$androidnum+$iphonenum+$macosxnum+$ipadnum+$othernum+$ipodnum;
        $data['alltotal']=$count;
        
        $db=M('wechatoath','total_');
        $add=$db->add($data);
        //保存完后删除redis
        if ($add){
            $redis->del($other);
            $redis->del($android);
            $redis->del($iphone);
            $redis->del($ipod);
            $redis->del($ipad);
            $redis->del($macosx);
        }
        
    }
    
    
    
}
?>