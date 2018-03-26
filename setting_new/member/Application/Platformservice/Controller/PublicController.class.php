<?php
namespace Home\Controller;

use Think\Controller;
use Common\Controller\RedisController;
class PublicController extends Controller{
    // TODO - Insert your code here
    
    
    
    public function getkeys(){
        $dbname=''!=I('name')?I('name'):1;
        $rediss = new RedisController();
        $redis=$rediss->connectredis($dbname);
        $allKeys = $redis->keys('*');
        $valarr=null;
        foreach ($allKeys as $key =>$val){
            $valarr[]=$val.'：：'.$redis->get($val);
        }
        dump($allKeys);
        dump($valarr);
    }
    
//     /**
//      * @desc  删除某个redis key
//      */
//     public function delrediskeys(){
//         if (IS_POST){//duotiaogongjiaoluxiantingyunle
//             if (''==I('post.pppddd') || md5(I('post.pppddd'))!='5578f579a33d05b7b5781e8968357d00' ){
                
//             }else{
//                 $dbname=I('post.dbname');
//                 $str=I('post.keys');
//                 if ($dbname=='' || $str==''){
//                     $this->display();
//                 }else{
//                     $keys=explode(',',$str);
//                     $rediss = new RedisController();
//                     $redis=$rediss->connectredis($dbname);
//                     $aaa=$redis->del($keys);
//                     dump($aaa);
//                     echo '<a href='.U('Home/Public/delrediskeys').'>继续删除</a>';
//                 }
//             }
            
//         }else {
//             $this->display();
//         }
//     }
    
    public function getallrediskeys(){
        $dbname=''!=I('name')?I('name'):1;
        $rediss = new RedisController();
        $redis=$rediss->connectredis($dbname);
        $allKeys = $redis->keys('*');
        $valarr=null;
        foreach ($allKeys as $key =>$val){
            $valarr[$val]=$redis->get($val);
        }
        echo json_encode($valarr);
    }
    
    
    public function setlocalrediskeys(){
        $name=isset($_GET['name'])?$_GET['name']:1;
        $return=http('http://mem.rtmap.com/Home/Public/getallrediskeys?name='.$name);
        $arr=json_decode($return,true);
        $rediss = new RedisController();
        $redis=$rediss->connectredis($name);
        foreach ($arr as $key => $val){
            $redis->set($key,$val);
        }
        dump($arr);
    }
}

?>