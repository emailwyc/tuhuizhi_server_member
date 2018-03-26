<?php
namespace Thirdwechat\Controller\Wechat;

use Thirdwechat\Controller\Wechat\Material\MaterialsController;

class MaterialController extends MaterialsController
{
    private $type=array('image','video','voice','news');
    private $material_type=array('image','voice','video','thumb');
    
    /**
     * 获取素材总数量
     */
    public function getmaterialCount()
    {
        $params['key_admin']=I('key_admin');
        $sign=I('sign');
        $msg=$this->commonerrorcode;
        if (null == $params['key_admin']){
            $msg['code']=100;
        }else{
            $admininfo=$this->getMerchant($params['key_admin']);
            $params['sign_key']=$admininfo['signkey'];
            //验证签名
            if (sign($params) != $sign){
                $msg['code']=1002;
            }else{
                $result=$this->materialCount($admininfo['wechat_appid']);
                if (is_json($result)){
                    $array=json_decode($result, true);
                    if (isset($array['errcode'])){
                        $msg['code']=104;
                    }else{
                        $msg['code']=200;
                        $msg['data']=$array;
                    }
                }else{
                    $msg['code']=101;
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 批量获取素材列表
     */
    public function getMaterial()
    {
        $params['type']=I('type');
        $params['key_admin']=I('key_admin');
        $sign=I('sign');
        $params['lines']=I('lines');
        $params['page']=I('page');//因为条数可能会很多，所以在业务上做了分页处理
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=100;
        }else{
            //验证
            if ($params['lines'] > 20 || $params['lines'] < 0 || $params['page'] < 0 || !in_array($params['type'], $this->type)){
                $msg['code']=1051;
            }else{
                $admininfo=$this->getMerchant($params['key_admin']);
                $params['sign_key']=$admininfo['signkey'];
                //验证签名
                if (sign($params) != $sign){
                    $msg['code']=1002;
                }else{
                    $result=$this->materialCount($admininfo['wechat_appid']);
                    $array=json_decode($result,true);
                    if (isset($array['errcode'])){
                        $msg['code']=101;
                    }else{
                        $count=$array[$params['type'].'_count'];
                        $offset=($params['page']-1) * $params['lines'];
                        if ($offset > $count ){
                            $msg['code']=1051;
                        }else{
                            $data=array('type'=>$params['type'],'offset'=>$offset,'count'=>$params['lines']);
                            $return=$this->batchgetMaterial(json_encode($data), $admininfo['wechat_appid']);
                            if (is_json($return)){
                                $materialarray=json_decode($return,true);
                                if (!isset($materialarray['errcode'])){
                                    $msg['code']=200;
                                    $msg['data']=array('count'=>$count,'data'=>$materialarray);
                                }else{
                                    $msg['code']=104;
                                }
                            }else{
                                $msg['code']=101;
                            }
                        }
                    }
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 新增永久图文素材
     */
    public function addNews()
    {
        $params['key_admin']=isset($_GET['key_admin']) ? $_GET['key_admin'] : '';
        $sign=isset($_GET['sign']) ? $_GET['sign'] : '';
        $jsondata=file_get_contents('php://input');//接收json串
        $checkparams=$this->checkParams($params, $params['key_admin'], $sign);//验证是否正确
        $msg=$this->commonerrorcode;
        if (true === $checkparams){
            $admininfo=$this->getMerchant($params['key_admin']);
            $return=$this->addNewsWechat($jsondata, $admininfo['wechat_appid']);dump($return);
            if (is_json($return)){
                $array=json_decode($return, true);
                if (isset($array['media_id'])){
                    $msg['code']=200;
                    $msg['data']=$array['media_id'];
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=101;
            }
        }else{
            $msg['code']=$checkparams;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 上传图文消息内的图片获取URL，注意是消息内容里面的图片，不是封面图
     * 注意，返回的是url
     */
    public function uploadimg()
    {
        $params['key_admin']=isset($_GET['key_admin']) ? $_GET['key_admin'] : '';
        $sign=isset($_GET['sign']) ? $_GET['sign'] : '';
        $img=file_get_contents('php://input');//获取二进制流
        $checkparams=$this->checkParams($params, $params['key_admin'], $sign);//验证是否正确
        $msg=$this->commonerrorcode;
        if (true === $checkparams){//如果验证成功
            //上传图片文件
            $saveimg=saveBinaryFile($img, true, APP_ROOT_PATH.'/Application/Runtime/Upload/Wechat/img/', $_SERVER, array('image/png','image/jpeg'));
            if (200 == $saveimg['code']){
                $admininfo=$this->getMerchant($params['key_admin']);
                $data=array('media'=>'@'.$saveimg['path']);
                $return=$this->uploadImage( $admininfo['wechat_appid'], $data);
                if (is_json($return)){
                    $array=json_decode($return, true);
                    if (isset($array['url'])){
                        $msg['code']=200;
                        $msg['data']=$array['url'];
                    }else{
                        $msg['code']=104;
                    }
                }else{
                    $msg['code']=101;
                }
            }else{
                $msg['code']=$saveimg['code'];
            }
        }else{
            $msg['code']=$checkparams;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    
    /**
     * 上传其它类型的永久素材
     * return:
{
  "code": 200,
  "data": {
    "media_id": "9mkLaeEAFcl-h_EVGk5pm-jLFyZC6_fmJJgrRzVDT8Y",
    "url": "http://mmbiz.qpic.cn/mmbiz_jpg/M4KMicPnIicSYve13ibXgvPlJw4fW9STClcd3tNTQbrw9GEibECHONjUqpD2zf7qv9mxT04sAlTzYXibx9kamvvaZag/0?wx_fmt=jpeg"
  },
  "msg": "SUCCESS."
}
     */
    public function add_material()
    {
        $params['key_admin']=isset($_GET['key_admin']) ? $_GET['key_admin'] : '';
        $params['type']=isset($_GET['type']) ? $_GET['type'] : '';//image,voice,video,thumb
        $params['thumb']=isset($_GET['thumb']) ? $_GET['thumb'] : '';
        $sign=isset($_GET['sign']) ? $_GET['sign'] : '';
        $img=file_get_contents('php://input');//获取二进制流
        if ('video'==$params['type']){//如果是视频格式，需要多加两个字段
            $params['title']=$_GET['title'];
            $params['introduction']=$_GET['introduction'];
        }
        
        $msg=$this->commonerrorcode;
        $types=$this->material_type;
        if (false == $img){
            $msg['code']=1054;
            echo returnjson($msg,$this->returnstyle, $this->callback);exit;
        }
        if (in_array($params['type'], $types)){//验证type字段是否正确
            $checkparams=$this->checkParams($params, $params['key_admin'], $sign);//验证是否正确
            if (true === $checkparams){//如果验证成功
                //上传图片文件
                $type=true;
                $saveimg=saveBinaryFile($img, $type, APP_ROOT_PATH.'/Application/Runtime/Upload/Wechat/material/');
                if (200 == $saveimg['code']){
                    $admininfo=$this->getMerchant($params['key_admin']);
                    if ('video'==$params['type']){
                        $data=array('media'=>'@'.$saveimg['path'], 'description'=>json_encode(array('title'=>$params['title'],'introduction'=>$params['introduction'])));
                    }else {
                        $data=array('media'=>'@'.$saveimg['path']);
                    }
                    
                    $return=$this->addMaterial( $admininfo['wechat_appid'], $data, $params['type'], $params['thumb']);
                    if (is_json($return)){
                        $array=json_decode($return, true);
                        if (isset($array['url']) || isset($array['media_id'])){
                            $msg['code']=200;
                            $msg['data']=$array;
                        }else{
                            $msg['code']=104;
                            $msg['data']=$array;
                        }
                    }else{
                        $msg['code']=101;
                    }
                }else{
                    $msg['code']=$saveimg['code'];
                }
            }else{
                $msg['code']=$checkparams;
            }
        }else{
            $msg['code']=1053;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 群发接口上传图文消息
     */
    public function upload_news()
    {
        $send=file_get_contents('php://input');
        $key_admin=$_GET['key_admin'];
        $sign=$_GET['sign'];
        $msg=$this->commonerrorcode;
        if (false==$send || empty($key_admin) || !is_json($send)){
            $msg['code']=100;
        }else{
            $array=json_decode($send,true);
            $nums=count($array);
            
            $admininfo=$this->getMerchant($key_admin);
            //验证签名
            $params=array('key_admin'=>$key_admin,'sign_key'=>$admininfo['signkey']);
            if (sign($params) != $sign){
                $msg['code']=1002;
            }else{
                if (!isset($admininfo['wechat_appid']) || empty($admininfo['wechat_appid'])){
                    $msg['code']=102;
                }else{
                    $return=$this->uploadNews($send,$admininfo['wechat_appid']);
                    if (is_json($return)){
                        $return_array=json_decode($return,true);
                        if (isset($return_array['media_id'])){
                            $msg['code']=200;
                            $msg['data']=$return_array;
                        }else{
                            $msg['code']=104;
                            $msg['data']=$return_array;
                        }
                    }else{
                        $msg['code']=101;
                    }
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * 获取视频media_id的media_id，上传视频请看上传其它素材文件接口，有点绕口
     * 但这就是微信的文档，可恶至极，我想对你说三个字“WTF”
     * http://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140549&token=&lang=zh_CN
     */
    public function uploadvideo()
    {
        $params['key_admin']=isset($_GET['key_admin']) ? $_GET['key_admin'] : '';
        $sign=isset($_GET['sign']) ? $_GET['sign'] : '';
        $jsondata=file_get_contents('php://input');//接收json串
        $checkparams=$this->checkParams($params, $params['key_admin'], $sign);//验证是否正确
        $msg=$this->commonerrorcode;
        if (true === $checkparams){
            $admininfo=$this->getMerchant($params['key_admin']);
            $return=$this->uploadVideoMediaId($jsondata, $admininfo['wechat_appid']);
            if (is_json($return)){
                $array=json_decode($return, true);
                if (isset($array['media_id'])){
                    $msg['code']=200;
                    $msg['data']=$array;
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=101;
            }
        }else{
            $msg['code']=$checkparams;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
}

?>