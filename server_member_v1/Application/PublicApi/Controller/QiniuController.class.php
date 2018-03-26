<?php
namespace PublicApi\Controller;

require './vendor/autoload.php';

use Common\Controller\ErrorcodeController;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Zone;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Tests\BucketTest;
class QiniuController extends ErrorcodeController
{
    // TODO - Insert your code here
    
    private $accessKey='DUFjIw35CsXadpPZMrVcKWrO-G7GNNeJ8GZeL0W4';
    private $secretKey='wRKplPAozSp8lSadFoJ-ThDbB6YCGMfDIMkxxvbX';
    
    public function _initialize(){
        parent::__initialize();
    }
    
    /**
     * 获取ｔｏｋｅｎ
     */
    protected function get_qiniu_upload_token(){
       
        $auth= new Auth($this->accessKey, $this->secretKey);
        $bucket='ka-images';
        $token=$auth->uploadToken($bucket);
        return $token;
    }
    
    public function get_upload_token(){
        $token=$this->get_qiniu_upload_token();
        $msg=$this->commonerrorcode;
        $msg['code']=200;
        $msg['data']=$token;
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    //七牛普通上传
    public function uploadfile($filePath,$key=''){
        $upToken=$this->get_qiniu_upload_token();
        $uploadmgr=new UploadManager();
        $re=$uploadmgr->putFile($upToken, $key, $filePath);
        return $re;
    }

    //七牛fetch抓取
    public function qiniu_fetch($url='',$imgname,$bucket='ka-imgs')
    {
        $auth= new Auth($this->accessKey, $this->secretKey);
        $fetch=new BucketManager($auth);
        $re=$fetch->fetch($url, $bucket,$imgname);
        return $re;
    }
}

?>