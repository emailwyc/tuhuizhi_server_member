<?php
/**
 * 微信素材类
 */
namespace Thirdwechat\Controller\Wechat\Material;

use Thirdwechat\Controller\Thirdwechat\EventsController;
use Thirdwechat\Controller\Wechat\WechatcommonController;

class MaterialsController extends WechatcommonController
{
    
    /**
     * 获取素材总数量
     */
    protected function materialCount($appid)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            $url=$this->batch_get_material_count.$authorizer_access_token;
            $curl=curl_https($url);
            return $curl;
        }
    }
    
    
    /**
     * 批量获取微信素材
     */
    protected function batchgetMaterial($data, $appid)
    {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            $url=$this->batch_get_material.$authorizer_access_token;
            $curl=curl_https($url,$data,array(),30,true);
            return $curl;
        }
    }
    
    /**
     * 新增永久图文素材
     * @param unknown $data
     * @param unknown $appid
     */
    protected function addNewsWechat($data, $appid) {
        $events=new EventsController();
        $authorizer_access_token=$events->authorizer_access_token($appid);
        if (false==$authorizer_access_token){
            return false;
        }else{
            $url=$this->add_news_url.$authorizer_access_token;
            $curl=curl_https($url,$data,array(),30,true);
            return $curl;
        }
    }
    
    
    
    /**
     * 上传图文消息内的图片获取URL
     * @param string $appid
     * @param array $data
     * @return boolean|mixed
     */
    protected function uploadImage(string $appid, array $data)
    {
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            $url=$this->uploadimg.$authorizer_access_token;
            $result=curl_https($url,$data,array(),600, true);
            return $result;
        }
    }
    
    
    /**
     * 上传其它素材文件
     * @param string $appid
     * @param array $data
     * @return boolean|mixed
     */
    protected function addMaterial(string $appid, array $data, string $type, $thumb=1)
    {
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            if (2 == $thumb){
                $url=$this->upload;
            }else{
                $url=$this->add_material_url;
            }
            
            $url=str_replace('[ACCESS_TOKEN]',$authorizer_access_token,$url);
            $url=$url.$type;
            $result=curl_https($url,$data,array(),600, true);
            return $result;
        }
    }
    
    
    /**
     * 上传群发消息的图文消息
     * @param string $appid
     * @param unknown $data
     */
    protected function uploadNews($data, string $appid)
    {
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            $url=$this->upload_news_url.$authorizer_access_token;
            $result=curl_https($url,$data,array(),600, true);
            return $result;
        }
    }
    
    
    /**
     * 获取视频media_id的media_id，上传视频请看上传其它素材文件接口，有点绕口
     * 但这就是微信的文档，可恶至极，我想对你说三个字“wtf”
     * @param unknown $data
     * @param string $appid
     */
    protected function uploadVideoMediaId($data, string $appid)
    {
        $event=new EventsController();
        $authorizer_access_token=$event->authorizer_access_token($appid);
        if (false == $authorizer_access_token){
            return false;
        }else{
            $url=$this->upload_video_media_id.$authorizer_access_token;
            $result=curl_https($url,$data,array(),600, true);
            return $result;
        }
    }
    
}

?>