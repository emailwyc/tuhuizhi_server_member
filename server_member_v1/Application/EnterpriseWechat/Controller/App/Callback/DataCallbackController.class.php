<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 08/12/2017
 * Time: 14:38
 */

namespace EnterpriseWechat\Controller\App\Callback;


use Common\Controller\CommonController;
use EnterpriseWechat\Service\MsgcryptService;

class DataCallbackController extends CommonController
{

    public function dataCallback()
    {
        $arr['where'] = 'datacallback';
        $arr['get']=$_GET;
        $arr['post']=$_POST;
        $arr['file']=file_get_contents('php://input');
        writeOperationLog($arr, 'enterprisedata');
        if (isset($_GET['echostr'])){
            $get = I('param.');
            $decrypt = MsgcryptService::CallbackValid(urldecode($get['msg_signature']), urldecode($get['timestamp']), urldecode($get['nonce']), $get['echostr'], urldecode($get['appname']));
            if ($decrypt !== false){
                echo $decrypt;
            }else{
                echostr('success');//其实回复success是错误的，必须要回复echostr的解密内容
            }
        }else{
            $appid = $_GET['appid'];
            $decrypt = MsgcryptService::receiveEventMessageDecrypt($arr['file'], $_GET['appname'], $_GET['msg_signature'], $_GET['timestamp'], $_GET['nonce'], $appid, $arr['where'], $_GET);
            if ($decrypt !== false){
                echostr($decrypt);
            }else{
                echostr('success');
            }
        }

    }
}