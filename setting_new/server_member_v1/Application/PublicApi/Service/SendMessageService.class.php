<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 28/09/2017
 * Time: 11:48
 */

namespace PublicApi\Service;

use Common\Service\RedisService;

class SendMessageService
{
    /**
     * @param $adminInfo admin信息
     * @param $adminDefaultSetting 发消息的方法名字
     * @param $params 接收到的所有参数
     */
    public static function sendMessages($adminInfo, $adminDefaultSetting, $params)
    {
        if (!$adminDefaultSetting) {
            return array('code'=>101);
        }

        try{
//            $merchant = array(
//                'huiyuecheng_'  => '汇悦城',
//                'xidan_'        => '西单大悦城',
//                'maoye_'        => '茂业',
//                'aoyong_'       => '奥永广场',
//                'taiguli_'      => '三里屯太古里',
//                'baotai_'      => '东方宝泰',
//                'jinjue_'      => '金爵万象奥莱广场',
//                'daweilai_'=>'未来中心服务号',
//                'zhihuitu_'=>'智慧图开发账号',
//            );

//            $tag = $merchant[$adminInfo['pre_table']];
//            if (empty($merchant[$adminInfo['pre_table']])) {
                $static = M('total_static');
                $result = $static->where(array('tid' => 12, 'admin_id' => $adminInfo['id']))->find();
                $tag = $result['content'];
//            }
            $code = rand(100000, 999999);//如果有传入content，则默认发送content的内容
            if (empty($params['content'])){
                RedisService::connectredis()->setex($params['mobile'], 300, $code);
            }

            $re = self::$adminDefaultSetting($params, $code, $tag);
            if ($re['code'] == 200){
                $re['data']['ttl']=300;
            }
            return $re;
        }catch (\Exception $exception){
            return array('code'=>101, 'data'=>$exception);
        }
    }



    /**
     * 智慧图发送验证码方法
     * @param $phone
     * @param $code
     * @throws \Exception
     */
    private static function zhihuitumsg($params, $code, $tag) {
        $url = 'http://m.5c.com.cn/api/send/index.php';
        $data['username'] = 'zhihuitu';
        $data['password_md5'] = md5('rtmap_911');
        $data['apikey'] = 'd40d62eec4fbd6a6ce6dfdec1d9315cf';
        $data['mobile'] = $params['mobile'];
        $data['encode'] = 'UTF-8';
        $content = !empty($params['content']) ?  $params['content'] : '您好,您的验证码为' . $code . ',【请勿向任何人提供您收到的短信验证码】【'. $tag .'】';
        $data['content'] = urlencode($content);

        $curl_re = http($url, $data, 'post');
        $array = explode(':', $curl_re);

        writeOperationLog(array("send {$tag} msg" => $curl_re), 'jaleel_logs');

        if ($array[0] == 'success') {
            $data = array('code' => '200', 'msg' => 'success');
            return $data;
        }else{
            $data = array('code' => '3000','data'=>$curl_re, 'msg' => 'send message failed!');
            return $data;
        }


    }

    /**
     * 西单发送验证码方法
     * @param $phone
     * @param $code
     * @throws \Exception
     */
    protected function xidanmsg($params, $code, $tag)
    {
        $account = "208817";       //账号
        $password = md5("z%@B1ul3"); //密码
        $content = !empty($params['content']) ?  $params['content'] : '您好,您的验证码为' . $code . ',请勿向任何人提供您收到的短信验证码!';
        $sendSmsAddress = "http://www.yxuntong.com/emmpdata/sms/Submit";
        $message ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
            ."<message>"
            . "<account>"
            . $account
            . "</account><password>"
            . $password
            . "</password>"
            . "<msgid></msgid><phones>"
            . $params['mobile']
            . "</phones><content>"
            . $content
            . "</content><subcode>"
            ."</subcode>"
            ."<sendtime></sendtime>"
            ."</message>";
        $params = array(
            'message' => $message);
        $data = http_build_query($params);
        $context = array('http' => array(
            'method' => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $data,
        ));
        writeOperationLog($params, 'xidanmsg');
        $contents = file_get_contents($sendSmsAddress, false, stream_context_create($context));
        $p = xml_parser_create();
        xml_parse_into_struct($p, $contents, $vals, $index);
        xml_parser_free($p);
        writeOperationLog($vals, 'xidanmsg');
        $sms_res = $vals[2]['value'];
        //if send msg success. timeout 60s.cacaca,bu neng da han zi.

        if ($sms_res != 0) {
            return array('code' => '3000', 'msg' => 'send message failed!');
        }
        return  array('code' => '200', 'msg' => 'success');
    }





    /**
     * 水晶城发送验证码方法
     * @param $phone
     * @param $code
     * @throws \Exception
     */
    private static function shuijingchengmsg($params, $code, $tag = '') {
        $url = 'http://120.55.193.51:8098/smtp/http/submit';
        $data['timestamp'] = date('YmdHis');
        $data['userName'] = 'sjsc';
        $data['userPass'] = 'sjsc';
        $data['sign'] = strtoupper(md5($data['userPass'].$data['timestamp']));
        $data['phones'] = $params['mobile'];
        $data['mhtMsgIds'] = time() . rand(1, 1000);
        $data['sendTime'] = '';
        $data['serviceCode'] = 'sjsc';
        $data['priority'] = '5';
        $data['msgType'] = '1';
        $content = !empty($params['content']) ? $params['content'] : '校验码' . $code . ',【请勿向任何人提供您收到的短信校验码】';
        $data['msgContent'] = iconv('utf8', 'gbk', $content);
        $data['reportFlag'] = '0';

        $curl_re = http($url, $data, 'post');
        $curl_re=iconv('gbk','utf8',$curl_re);//转码，世上码太多，为何不能无码
        $result = json_decode($curl_re, true);

        writeOperationLog(array('send shuijingcheng msg' => $curl_re), 'jaleel_logs');

        if ($result['result'] != 0) {
            $data = array('code' => '3000','data'=>$result, 'msg' => 'send message failed!');
            return $data;
        }else{
            $data = array('code' => '200', 'msg' => 'success');
            return $data;
        }
    }




    private static function kuntaimsg($params, $code, $tag='')
    {
        $url = 'http://112.90.92.102:16655/smsgwhttp/sms/submit';
        $data['spid'] = '80010';//企业帐号
        $data['password'] = 'Xg@2017kun';//企业密码
        $data['ac'] = '10691306074';//下发接入码
        $data['mobiles'] = $params['mobile'];
        $content = !empty($params['content']) ? $params['content'] : '你的验证码是:'. $code .'【'. $tag .'】';
        $data['content'] = $content;

        $retu = http($url, $data, 'POST');
        $array =xmlstr_to_array($retu);
        if ($array['result'] == 0){
            $data = array('code' => '200', 'msg' => 'success');
            return $data;
        }else{
            $data = array('code' => '3000', 'data'=>$array, 'msg' => 'success');
            return $data;
        }
    }
}