<?php
namespace Common\Service;

use Common\Controller\RedisController;
use Thirdwechat\Controller\Thirdwechat\EventsController;
use PublicApi\Controller\QiniuController;

//上传Service
class UploadService{
     
    public $redis;
    public $Events;
    public $Qiniu;
    
    public function __construct()
    {
        $redis_con = new RedisController();
        $this->redis = $redis_con->connectredis();
        
        $this->Events = new EventsController();
        
        $this->Qiniu = new QiniuController();

    }
    
    /**
     * 微信抓取图片上传七牛
     * @param $appid 商户appid
     * @param $mem_id 微信图片标识
     * @param $ext 微信图片后缀名
     */
    public function  FetchWeChatQiniu($appid , $mem_id , $ext = ''){

        //获取微信access_token
        $authorizer_access_token=$this->Events->authorizer_access_token($appid);
        $params['access_token']=$authorizer_access_token; 
        
        //获取图片（返回为base64格式）
        $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$params['access_token'].'&media_id='.$mem_id;
        $weixin_return=curl_https($url,array(),array(),30,false,'GET');
        
        //返回json，则获取图片失败
        if(is_json($weixin_return)){
            $msg['code']=104;
            
            $arr['IntegralMakeup_weixin']=$weixin_return;//记录微信返回错误json
        }else{
            
            //七牛没有获取图片base64格式的接口，先将图片存入本地
            $res_return=saveBinaryFile_exts($weixin_return, true, RUNTIME_PATH.'Logs/Integral/Make/img/', $_SERVER,array(),$ext);
            
            $arr['IntegralMakeup_Qiuniu_exts']=$res_return;//存入本地
            if($res_return['code']==200){
                
                //处理图片
                $extension = substr($res_return['path'], strrpos($res_return['path'], '.') + 1);
                $time = date("Ymd");
                $uniqid = uniqid();//生成当前时间的唯一id
                $key = 'image_'.$time.'_'.$uniqid.'.'.$extension;//生成新图片名称

                //上产七牛
                list($ret, $err)=$this->Qiniu->uploadfile($res_return['path'],$key);
                
                //删除本地图片
                unlink($res_return['path']);
                
                if ($err !== null) {
                    $msg['code']=104;
                }else{
                    $msg['code']=200;
                    $msg['data'] = $key;
                    
                    $arr['IntegralMakeup_keys']=$key;
                }
            }else{
                $msg['code']=$res_return['code'];
            }
        }
        writeOperationLog($arr,'IntegralMakeup');
        return $msg;
    }

    /**
     * 抓取图片上传七牛
     */
    public function  FetchImgQiniu($imgUrl){
        //七牛没有获取图片base64格式的接口，先将图片存入本地
        $res_return=saveBinaryFile_exts(base64_decode($imgUrl), true, RUNTIME_PATH.'Logs/Integral/Make/img/', $_SERVER);
        if($res_return['code']==200) {
            //处理图片
            $extension = substr($res_return['path'], strrpos($res_return['path'], '.') + 1);
            $time = date("Ymd");
            $uniqid = uniqid();//生成当前时间的唯一id
            $key = 'image_' . $time . '_' . $uniqid . '.' . $extension;//生成新图片名称
            //上产七牛
            list($ret, $err)=$this->Qiniu->uploadfile($res_return['path'],$key);
            if ($err !== null) {
                $msg['code'] = 104;
            } else {
                $msg['code'] = 200;
                $msg['data'] = $key;
            }
        }else{
            $msg['code']=$res_return['code'];
        }
        return $msg;
    }
    
}

?>