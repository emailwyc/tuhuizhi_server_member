<?php
namespace Thirdwechat\Controller\Wechat\Member;

use Thirdwechat\Controller\Wechat\Member\MemberController;
use Curl\MultiCurl;
use Thirdwechat\Controller\Thirdwechat\EventsController;
class MemberioController extends MemberController
{
    // TODO - Insert your code here


    /**
     * 获取关注传入appid的openid列表信息，存入redis
     * @param string $table_pre
     * @param string $appid
     * @param string $next_openid
     * @return boolean
     */
    protected function GetOpenidList(string $table_pre,string $appid,$next_openid=''){
        ini_set('memory_limit',-1);
        //此方法不能有任何删除redis key的代码，否则，会造成redis存储不完整

        $result=$this->get_wechat_user_openid($appid,$next_openid);
        if (false != $result){
            if (is_json($result)){
                $array=json_decode($result,true);
                unset($result);
                if (!array_key_exists('errcode',$array)){
                    //dump($array);die;
                    //试用一下redis有序集合array_chunk
                    $chunarr=array_chunk($array['data']['openid'],100);//把openid列表按100个一个数组，分割成多个二位数组
                    $number=$this->redis->get('wechat:openid:number:'.$appid);//获取本次redis有序集合的序号
                    $number=null==$number ? 1 : $number;
                    $localtotal=$this->redis->get('wechat:openid:localtotal:'.$appid);//本地已经获取了多少个
                    //将openid列表存到redis有序集合中
                    foreach ($chunarr as $key => $val){
                        //获取openid详细信息的json参数，在这里组合，存redis，
                        $openid_arr=null;
                        foreach ($val as $k => $v){
                            $openid_arr[]=array('openid'=>$v,'lang'=>'zh_CN');//参数里面还有一个非必要参数lang，语言参数，传递中文
                        }
                        $openid_array['user_list']=$openid_arr;
                        $openid_json=json_encode($openid_array);
                        $this->redis->zadd('wechat:openidlist:'.$appid,$number,$openid_json);
                        writeOperationLog(['number'=>$number, 'openidarray'=>$openid_array],'tmptotalopenidnum');
                        $number++;
                        unset($openid_arr);
                        unset($openid_array);
                    }
                    $this->redis->set('wechat:openid:number:'.$appid,$number);//记录本次获取完openid后的redis有序集合的序号
                    $localtotal=$this->redis->incrBy('wechat:openid:localtotal:'.$appid,$array['count']);//记录本地总数
                    if ($localtotal < $array['total']){//如果当前本地更新量小于全部
                        $this->GetOpenidList($table_pre, $appid,$array['next_openid']);
                    }elseif ($localtotal >= $array['total']){//如果当前本地更新量等于或大于全部更新量，不会出现大于的情况，但防止意外情况，把大于的判断写上
                        //$this->redis->del('wechat:openid:number:'.$appid);
//                        writeOperationLog(['totalopenidnum'=>$localtotal, 'youxuxuhao'=>$number],'totalopenidnum');
                        //删除记录的本地获取openid总数
                        $this->redis->del('wechat:openid:localtotal:'.$appid);
                        //保存完所有openid列表以后，按openid列表获取用户详细信息
                        $a=$this->GetWechatInfo($appid,$table_pre);
                        return true;
                    }
                }else{//echo 'aaa';//print_r($array);
                    $result=array('appid'=>$appid,'msg'=>'从微信获取openid列表时,微信返回了错误码:'.$array['errcode'],'where'=>'aaa','data'=>$array);
                    writeOperationLog($result,'wechatgetuser');
                    return false;
                }
            }else{//echo 'bbb';//print_r($result);
                $result=array('appid'=>$appid,'msg'=>'从微信获取openid列表时返回参数不是json格式','where'=>'bbb', 'data'=>$result);
                writeOperationLog($result, 'wechatgetuser');
                return false;
            }
        }else {//echo 'ccc';//dump($result);
            $result=array('appid'=>$appid,'msg'=>'获取openid列表时,中间错误','where'=>'ccc', 'data'=>$result);
            writeOperationLog($result, 'wechatgetuser');
            return false;
        }
    }



    protected function GetWechatInfo(string $appid,string $table_pre){
        $db=M('','','DB_CONFIG4');
//        $db=M();
        $c=$db->execute('SHOW TABLES like "'.$table_pre.'wechat_openid"');
        if (1 === $c){
            $db->execute('TRUNCATE '.  $table_pre.'wechat_openid');
        }else{
            $sql="
DROP TABLE IF EXISTS  `".$table_pre."wechat_openid`;
CREATE TABLE `".$table_pre."wechat_openid` (
  `openid` varchar(50) NOT NULL DEFAULT '',
  `nickname` varchar(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `sex` tinyint(1) DEFAULT NULL COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `country` varchar(50)  CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '微信用户所在国家',
  `province` varchar(50)  CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '微信用户所在省份',
  `city` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '微信用户所在城市',
  `language` varchar(20) NOT NULL DEFAULT '' COMMENT '用户的语言',
  `headimgurl` varchar(255) NOT NULL DEFAULT '' COMMENT '微信用户头像',
  `unionid` varchar(50) NOT NULL DEFAULT '' COMMENT '微信用户unionid',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '公众号运营者对粉丝的备注',
  `groupid` tinyint(2) DEFAULT NULL COMMENT '用户所在的分组ID',
  `subscribe` tinyint(1) DEFAULT NULL COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。',
  `subscribe_time` varchar(15) DEFAULT NULL COMMENT '用户关注时间，为时间戳',
  `tagid_list` varchar(100) NOT NULL DEFAULT '' COMMENT '用户被打上的标签ID列表',
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;";
            $db->execute($sql);
        }


        $number=$this->redis->get('wechat:openid:number:'.$appid);//获取$appid的redis有序集合的序号
//         require  './vendor/autoload.php';
//         $multi_curl=new MultiCurl();
//         $event=new EventsController();
        $openid_list_array=null;
        $mulit=100;//每次请求个数
        $page = ceil(($number-1)/$mulit);
        $addall=null;
        $nums=0;//循环计数
        $total = 0;
        for ($i=0;  $i<$number - 1; $i++){
            $user_json=$this->redis->zRange('wechat:openidlist:'.$appid,$i,$i);//返回的是一个数组
            $user_json=$user_json[0];//因为只获取了一个，所以取第一个
            $total = $total + count($user_json);
//            writeOperationLog(['totalthis'=>count($user_json), 'totaltotal'=>$total], 'totalopenidnum');
            if ( $nums >= $mulit || ceil($i/$mulit) >= $page) {
                $openid_list_array[]=$user_json;
                $this->get_wechat_user_info_mulit($appid, $openid_list_array,$table_pre);
                echo 'runing:'.date('Y-m-d H:i:s')."\r\n";
//                $this->redis->zRemRangeByRank('wechat:openidlist:'.$appid,$i,$i);
                unset($openid_list_array);
                $openid_list_array=null;
                $nums=0;
            }else{
                $openid_list_array[]=$user_json;
                $nums++;
            }
        }
        //最后删除本次代码记录的redis和其它缓存等
        $this->redis->del('wechat:openid:number:'.$appid);//删除本次计数的redis key，按100个计算
        $this->redis->del('wechat:openid:localtotal:'.$appid);//删除本地一共获取了多少个openid
        $this->redis->del('wechat:openidlist:'.$appid);//删除有序集合key
        session('table_pre',null);
        echo 'end:'.date('Y-m-d H:i:s')."\r\n";

    }
}

?>