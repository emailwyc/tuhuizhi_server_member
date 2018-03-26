<?php
namespace PublicService\Controller;

use PublicApi\Controller\QiniuController;
use Common\Service\UploadService;
// use Thirdwechat\Controller\Thirdwechat\EventsController;

class UpWechatImageController extends  QiniuController{

    /*
     * 微信图片抓取上传（单条）
     * $media_id  微信media_id
     * $key_admin   商户key
     * $ext   文件后缀名
     */
    public function UpImageAction($media_id,$key_admin,$ext=''){

            $total_arr=$this->getMerchant($key_admin);
            
            $upload = new UploadService();
            
            $return = $upload->FetchWeChatQiniu($total_arr['wechat_appid'] , $media_id , $ext);
            
            $arr['IntegralMakeup_Qiniu_Up_return']=$return;//记录微信返回错误json
            writeOperationLog($arr,'IntegralMakeup');
            
            if($return['code'] == 200){
                
                return "https://img.rtmap.com/".$return['data'];
                
            }else{
                
                return false;
                
            }
            
    }
}
?>

