<?php
    /**
    *   发起一个HTTP(S)请求，并返回json格式的响应数据
    *   @param array 错误信息  array($errorCode, $errorMessage)
    *   @param string 请求Url
    *   @param array 请求参数
    *   @param string 请求类型(GET|POST)
    *   @param int 超时时间
    *   @param array 额外配置
    *   @return array
    */
//     function curl_request_json(&$error, $url, $param = array(), $method = 'GET', $timeout = 30, $exOptions = null) {
//         $error = false;
//         $responseText = curl_request_text($error, $url, $param, $method, $timeout, $exOptions);
//         $response = null;
           
//         if ($error == false && $responseText > 0) {
//             $response = json_decode($responseText, true);
//             if ($response == null) {
//                 $error = array('errorCode'=>-1, 'errorMessage'=>'json decode fail', 'responseText'=>$responseText);
//                 //将错误信息记录日志文件里
//                 $logText = 'json decode fail : $url';
//                 if (!empty($param)) {
//                     $logText .= ', param='.json_encode($param);
//                 }
//                 $logText .= ', responseText=$responseText';
//                 file_put_contents(RUNTIME_PATH.'/wechat/'.date('Y-m-d').'/error.log', $logText);
//             }
//         }
//         return $response;
//     }
            
                    
//      /**
//      *  发起一个HTTP(S)请求，并返回响应文本
//      *   @param array 错误信息  array($errorCode, $errorMessage)
//     *   @param string 请求Url
//      *   @param array 请求参数
//     *   @param string 请求类型(GET|POST)
//      *   @param int 超时时间
//     *   @param array 额外配置
//     *   @return string
//     */
    
//     function curl_request_text(&$error, $url, $param = array(), $method = 'GET', $timeout = 30, $exOptions = NULL) {
//         //判断是否开启了curl扩展
//         if (!function_exists('curl_init')) exit('please open this curl extension');
//         //将请求方法变大写
//         $method = strtoupper($method);
//         $ch = curl_init();
//         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//         curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//         curl_setopt($ch, CURLOPT_HEADER, false);
//         if (isset($_SERVER['HTTP_USER_AGENT'])) curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
//         if (isset($_SERVER['HTTP_REFERER'])) curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
//         curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
//         switch ($method) {
//             case 'POST':
//                 curl_setopt($ch, CURLOPT_POST, true);
//                 if (!empty($param)) {
//                     curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($param)) ? http_build_query($param) : $param);
//                 }
//                 break;
//             case 'GET':
                
//             case 'DELETE':
//                 if ($method == 'DELETE') {
//                     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
//                 }
//                 if (!empty($param)) {
//                     $url = $url.(strpos($url, '?') ? '&' : '?').(is_array($param) ? http_build_query($param) : $param);
//                 }
//             break;
//         }
//         curl_setopt($ch, CURLINFO_HEADER_OUT, true);
//         curl_setopt($ch, CURLOPT_URL, $url);
//         //设置额外配置
//         if (!empty($exOptions)) {
//             foreach ($exOptions as $k => $v) {
//                 curl_setopt($ch, $k, $v);
//             }
//         }
//         $response = curl_exec($ch);
//         $error = false;
//         //看是否有报错
//         $errorCode = curl_errno($ch);
//         if ($errorCode) {
//             $errorMessage = curl_error($ch);
//             $error = array('errorCode'=>$errorCode, 'errorMessage'=>$errorMessage);
//             //将报错写入日志文件里
//             $logText = '$method $url: [$errorCode]$errorMessage';
//             if (!empty($param)) $logText .= ',$param'.json_encode($param);
//             file_put_contents(RUNTIME_PATH.'/wechat/'.date('Y-m-d').'/error.log', $logText);
//         }
//         curl_close($ch);
//         return $response;
//     }


    /**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
//     function http($url, $params, $method = 'GET', $header = array(), $multi = false){
//         $opts = array(
//             CURLOPT_TIMEOUT        => 30,
//             CURLOPT_RETURNTRANSFER => 1,
//             CURLOPT_SSL_VERIFYPEER => false,
//             CURLOPT_SSL_VERIFYHOST => false,
//             CURLOPT_HTTPHEADER     => $header
//         );
//         /* 根据请求类型设置特定参数 */
//         switch(strtoupper($method)){
//             case 'GET':
//                 $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
//                 break;
//             case 'POST':
//                 //判断是否传输文件
//                 $params = $multi ? $params : http_build_query($params);
//                 $opts[CURLOPT_URL] = $url;
//                 $opts[CURLOPT_POST] = 1;
//                 $opts[CURLOPT_POSTFIELDS] = $params;
//                 break;
//             default:
//                 throw new Exception('不支持的请求方式！');
//         }
//         /* 初始化并执行curl请求 */
//         $ch = curl_init();
//         curl_setopt_array($ch, $opts);
//         $data  = curl_exec($ch);
//         $error = curl_error($ch);
//         curl_close($ch);
//         if($error) throw new Exception('请求发生错误：' . $error);
//         return  $data;
//     }


