<?php
namespace PublicService\Controller;

use Common\Controller\ErrorcodeController;
use Thirdwechat\Controller\Thirdwechat\EventsController;
use PublicApi\Controller\QiniuController;
use Integral;

class IntegralMakeupController extends  QiniuController{
    
    /*
     * 积分补录
     */
    public function file_weixin_qiniu(){
        $params['media_id']=I('media_id');
        //$params['media_id']='-eiUsrRup0rDRSyBXPnszGWALRLkHb2znADm_m58j6IwDrSuFQqAUyxC4CdU9pXB';
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        if(in_array('', $params)){
            $msg['code']=1030;
       }else{
            $params['ext']=I('ext')?I('ext'):'';
            $total_arr=$this->getMerchant($params['key_admin']);
            $events=new EventsController();
            $authorizer_access_token=$events->authorizer_access_token($total_arr['wechat_appid']);
            $params['access_token']=$authorizer_access_token;
            $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$params['access_token'].'&media_id='.$params['media_id'];
            $weixin_return=curl_https($url,array(),array(),30,false,'GET');
            if(is_json($weixin_return)){
                $msg['code']=104;   
                $arr['IntegralMakeup_weixin']=$weixin_return;
                writeOperationLog($arr,'IntegralMakeup');
            }else{
                $res_return=saveBinaryFile_exts($weixin_return, true, APP_ROOT_PATH.'/Application/Runtime/Integral/Make/img/', $_SERVER,array(),$params['ext']);
                if($res_return['code']==200){
                    $extension = substr($res_return['path'], strrpos($res_return['path'], '.') + 1);
                    $time = date("Ymd");
                    $uniqid = uniqid();
                    $key = 'image_'.$time.'_'.$uniqid.'.'.$extension;
                     list($ret, $err)=$this->uploadfile($res_return['path'],$key);
                     unlink($res_return['path']);
                     if ($err !== null) {
                         $msg['code']=104;
                     } else{   
                         $arr['IntegralMakeup_params']=$params;
                         $arr['IntegralMakeup']=$ret;
                         writeOperationLog($arr,'IntegralMakeup');
                         $pre_db=M('mem',$total_arr['pre_table']);
                         $pre_arr=$pre_db->where(array('openid'=>$params['openid']))->find();
                         if($pre_arr){
                             $pre_score_db=M('score_type',$total_arr['pre_table']);
                             $data['img_src']="https://img.rtmap.com/".$key;
                             $data['createtime']=date('Y-m-d H:i:s');
                             $data['status']=1;
                             $data['user_mobile']=$pre_arr['mobile'];
                             $data['username']=$pre_arr['usermember'];
                             $data['cardno']=$pre_arr['cardno'];
                             $score_type_res=$pre_score_db->add($data);
                             if($score_type_res){
                                 $msg['code']=200;
                             }else{
                                 $msg['code']=104;
                             }
                             $arr['IntegralMakeup_sql']=$pre_score_db->_sql();
                             writeOperationLog($arr,'IntegralMakeup');
                         }else{
                            $msg['code']=2000;
                         }   
                     }
                }else{
                    $msg['code']=$res_return['code'];
                }
           }
       }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
}
?>