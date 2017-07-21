<?php
namespace Platformservice\Controller;

use Think\Controller;
use Common\Controller\RedisController;
class RedispController extends PlatcommonController{
    // TODO - Insert your code here
    
    
    /**
     * 查看所有key
     */
    public function showkeys(){
//        $dbname=''!=I('name')?I('name'):1;
//        $rediss = new RedisController();
//        $redis=$rediss->connectredis($dbname);
//        $allKeys = $redis->keys('*');
//        $valarr;
//        foreach ($allKeys as $key =>$val){
//            $valarr[]=array('key'=>$val,'val'=>$redis->get($val),'name'=>$dbname);//$val.'：：'.$redis->get($val);
//        }
        if (IS_GET){
            $this->display();
        }else{
            $dbname=''!=I('post.name')?I('post.name'):1;
            $key=I('post.key_name');
            $rediss = new RedisController();
            $redis=$rediss->connectredis($dbname);
            $val=$redis->get($key);
            returnjson(array('code'=>200,'data'=>$val), true , '');
        }
    }
    
    
    /**
     * 将服务器端redis缓存到本地
     */
    public function setlocalrediskeys(){
        $serverip=$_SERVER["SERVER_ADDR"];
        $host=$this->hostarr;
        if (in_array($serverip,$host)){
            $show='您现在是在服务器端执行此段代码,故无效。';
        }else{
            $name=isset($_GET['name'])?$_GET['name']:1;
            $array=array('name'=>$name,'auth'=>'f78d93bb3aa39ffe46756aa447bd2199','key'=>'441c3b36c06d43dede83c3f2e9fb3dee');
            $sign=sign($array);
            $array['sign']=$sign;
            unset($array['key']);
            
            $return=http('https://mem.rtmap.com/Home/Public/getallrediskeys', $array, 'POST');
            $arr=json_decode($return,true);
            $rediss = new RedisController();
            $redis=$rediss->connectredis($name);
            foreach ($arr['data'] as $key => $val){
                $redis->set($key,$val);
            }
            $show='success';
        }
        $this->assign('d',$show)->display();
    }
    
    /**
     * 添加一个redis key，为了避免对业务的影响，默认给的时间为半个小时
     */
    public function setredis()
    {
        if (!IS_POST){
            $this->assign('host',C('DOMAIN'))->display();exit;
        }
        $params['key']=$_POST['rediskey'];
        $params['value']=$_POST['redisvalue'];
        $ttl=I('post.ttl');
        
        if (in_array('', $params)){
            $msg=array('code'=>100);
        }else{
            //如果勾选了永久存储
            if ('true'==I('post.isforever')){
                $set=$this->redis->set($params['key'], $params['value']);
                if ($set){
                    $msg=array('code'=>200);
                }else{
                    $msg=array('code'=>104);
                }
            }else{//如果没有勾选永久存储,默认存储30分钟
                $ttl= false==$ttl ? 1800 : $ttl * 60;//默认30分钟
                $set=$this->redis->set($params['key'], $params['value'], array('ex'=>$ttl));
                if ($set){
                    $msg=array('code'=>200);
                }else{
                    $msg=array('code'=>104);
                }
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @desc  删除某个redis key
     */
    public function delrediskeys(){
        if (IS_POST){//duotiaogongjiaoluxiantingyunle
            if (''==I('post.pppddd') || md5(I('post.pppddd'))!='5578f579a33d05b7b5781e8968357d00' ){

            }else{
                $dbname=I('post.dbname');
                $str=I('post.keys');
                if ($dbname=='' || $str==''){
                    $this->display();
                }else{
                    $keys=explode(',',$str);
                    $rediss = new RedisController();
                    $redis=$rediss->connectredis($dbname);
                    $aaa=$redis->del($keys);
                    dump($aaa);
                    echo '<a href='.U('Platformservice/Redisp/delrediskeys').'>继续删除</a>';
                }
            }

        }else {
            $this->display();
        }
    }
}

?>