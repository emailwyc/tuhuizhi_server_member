<?php
namespace Home\Controller;

use Think\Controller;
use Common\Controller\RedisController;
class PublicController extends Controller{
    // TODO - Insert your code here
    
    
//     public function getkeys(){
//         $dbname=''!=I('name')?I('name'):1;
//         $rediss = new RedisController();
//         $redis=$rediss->connectredis($dbname);
//         $allKeys = $redis->keys('*');
//         $valarr;
//         foreach ($allKeys as $key =>$val){
//             $valarr[]=array($val=>$redis->get($val));//$val.'：：'.$redis->get($val);
//         }
//         dump($allKeys);
//         dump($valarr);
//     }
    
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
                    echo '<a href='.U('Home/Public/delrediskeys').'>继续删除</a>';
                }
            }
            
        }else {
            $this->display();
        }
    }

    public function getip()
    {
        $mobile=I('mobile');
        $phone=array(13522667528,18910124223,13521625139);
        echo $_SERVER['SERVER_ADDR'];
        echo '<br>';
        if ($_SERVER['SERVER_ADDR'] != '123.56.138.28'){
            echo 1;
        }
        elseif (!in_array($mobile, $phone)){
            echo 2;
        }else{
            echo 3;
        }

        echo '<br>';
        if (!in_array($mobile, $phone) || $_SERVER['SERVER_ADDR'] != '123.56.138.28'){
            echo 4;
        }
    }

    
    public function getallrediskeys(){
        $dbname=I('post.name') ;
        $sign=I('post.sign');
        $auth=I('post.auth');//'f78d93bb3aa39ffe46756aa447bd2199';\xiaotuzaizi,
        $key='441c3b36c06d43dede83c3f2e9fb3dee';//rediskeyqianming
        if (''==$dbname || ''== $sign || ''==$auth){
            echo json_encode(array('code'=>100));exit;
        }
        if (sign(array('name'=>$dbname,'auth'=>$auth,'key'=>$key)) == $sign){
            $rediss = new RedisController();
            $redis=$rediss->connectredis($dbname);
            $allKeys = $redis->keys('*');
            $valarr;
            foreach ($allKeys as $key =>$val){
                $valarr[$val]=$redis->get($val);
            }
            echo json_encode(array('code'=>200,'data'=>$valarr));
        }else{
            echo json_encode(array('code'=>104));
        }
        
        
    }
    
    
//     public function setlocalrediskeys(){
//         $name=isset($_GET['name'])?$_GET['name']:1;
//         $return=http('http://mem.rtmap.com/Home/Public/getallrediskeys?name='.$name);
//         $arr=json_decode($return,true);
//         $rediss = new RedisController();
//         $redis=$rediss->connectredis($name);
//         foreach ($arr as $key => $val){
//             $redis->set($key,$val);
//         }
//         dump($arr);
//     }
    
    
//     public function test(){
        
//         echo $_SERVER["SERVER_ADDR"];
//         echo gethostbyname($_SERVER["SERVER_NAME"]);
//         $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
//         echo $host;
        
//         if (isset($_SERVER)) {
//             if($_SERVER['SERVER_ADDR']) {
//                 $server_ip = $_SERVER['SERVER_ADDR'];
//             } else {
//                 $server_ip = $_SERVER['LOCAL_ADDR'];
//             }
//         } else {
//             $server_ip = getenv('SERVER_ADDR');
//         }
//         echo  $server_ip;
//     }

    public function rd() {
        $rediss = new RedisController();
        $redis=$rediss->connectredis();
        
    }
}

?>