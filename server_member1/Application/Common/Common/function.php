<?php

use Common\Controller\RedisController;
header ( "Content-Type:text/html;charset=utf-8" );
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
    function curl_request_json(&$error, $url, $param = array(), $method = 'GET', $timeout = 30, $exOptions = null) {
        $error = false;
        $responseText = curl_request_text($error, $url, $param, $method, $timeout, $exOptions);
        $response = null;
           
        if ($error == false && $responseText > 0) {
            $response = json_decode($responseText, true);
            if ($response == null) {
                $error = array('errorCode'=>-1, 'errorMessage'=>'json decode fail', 'responseText'=>$responseText);
                //将错误信息记录日志文件里
                $logText = 'json decode fail : $url';
                if (!empty($param)) {
                    $logText .= ', param='.json_encode($param);
                }
                $logText .= ', responseText=$responseText';
                file_put_contents(RUNTIME_PATH.'/wechat/'.date('Y-m-d').'/error.log', $logText);
            }
        }
        return $response;
    }
            
                    
     /**
     *  发起一个HTTP(S)请求，并返回响应文本
     *   @param array 错误信息  array($errorCode, $errorMessage)
    *   @param string 请求Url
     *   @param array 请求参数
    *   @param string 请求类型(GET|POST)
     *   @param int 超时时间
    *   @param array 额外配置
    *   @return string
    */
    
    function curl_request_text(&$error, $url, $param = array(), $method = 'GET', $timeout = 30, $exOptions = NULL) {
        //判断是否开启了curl扩展
        if (!function_exists('curl_init')) exit('please open this curl extension');
        //将请求方法变大写
        $method = strtoupper($method);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (isset($_SERVER['HTTP_USER_AGENT'])) curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        if (isset($_SERVER['HTTP_REFERER'])) curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($param)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($param)) ? http_build_query($param) : $param);
                }
                break;
            case 'GET':
                
            case 'DELETE':
                if ($method == 'DELETE') {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                }
                if (!empty($param)) {
                    $url = $url.(strpos($url, '?') ? '&' : '?').(is_array($param) ? http_build_query($param) : $param);
                }
            break;
        }
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置额外配置
        if (!empty($exOptions)) {
            foreach ($exOptions as $k => $v) {
                curl_setopt($ch, $k, $v);
            }
        }
        $response = curl_exec($ch);
        $error = false;
        //看是否有报错
        $errorCode = curl_errno($ch);
        if ($errorCode) {
            $errorMessage = curl_error($ch);
            $error = array('errorCode'=>$errorCode, 'errorMessage'=>$errorMessage);
            //将报错写入日志文件里
            $logText = '$method $url: [$errorCode]$errorMessage';
            if (!empty($param)) $logText .= ',$param'.json_encode($param);
            file_put_contents(RUNTIME_PATH.'/wechat/'.date('Y-m-d').'/error.log', $logText);
        }
        curl_close($ch);
        return $response;
    }


    /**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @param  array  $header 一维数组
     * @return array  $data   响应数据
     */
    function http($url, $params, $method = 'GET', $header = array(), $multi = false, $timeout=6000){
        $opts = array(
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );
        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) throw new Exception('请求发生错误：' . $error);
        
        $arr['url']=$url;
        $arr['data']=json_encode($params);
        $arr['method']=$method;
        $arr['header']=json_encode($header);
        $arr['multi']=(int)$multi;
        $arr['returndata']=logcheck($data);
        writeOperationLog($arr,'http');//记录日志
        return  $data;
    }
    
    /**
     * 
     * @param unknown $arr  要记录的日志
     * @param string $destination 日志目录，包括日志文件地址
     * @param $type  哪一个日志
     * @param string $extra
     */
    function writeOperationLog($arr=array(),$file,$destination='',$extra='') {
        $arr ['time'] = date('Y-m-d H:i:s');
        $arr ['ip'] = get_client_ip ();
        $arr['visibletype']	= $_SERVER['HTTP_USER_AGENT'];
        $type=3;
        if(empty($destination)){
            $destination = RUNTIME_PATH.'Logs/'.$file.'/'.$file.'_'.date('y_m_d').'.log';//echo $destination;
        }
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && C('LOG_FILE_SIZE') <= filesize($destination) ){
            rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }//echo $destination;
        $logstr=null;
        foreach ($arr as $k=>$v){
            if (is_array($v)){
                $v=json_encode($v);
            }
            $logstr.=$k.'='.$v."\r\n";
        }
        //$logstr=json_encode($arr)."\r\n";
        $logstr.="**************************************************************************************** \r\n";
        error_log($logstr."\r\n", $type,$destination ,$extra);
    
        $logstr=null;
    }
    
    
    function logcheck($data){
        if (is_json($data)){
            $d=$data;
        }else if (is_object($data)){
            $d=$data;
        }else{
            $d=$data;
        }
        return $d;
    }
    
    
    /**
     * 
     * @param unknown $data  传入的参数
     * @return unknown  判断是json还是xml，并返回json字符串
     */
    function checktype($data){
        if (is_array($data)){
            $d=http_build_query($data);
        }elseif (is_json($data)){
            $d=$data;
        }else {
            $d=$data;//json_encode(simplexml_load_string($data));
        }
        return $d;
    }
    
    
    
    /**
     *
     * @param unknown $data  传入的参数
     * @return unknown  判断是json还是xml，并返回json字符串
     */
    function checktypetwo($data){
        if (is_array($data)){
            $d=http_build_query($data);
        }elseif (is_json($data)){
            $d=$data;
        }else {
            $d=$data;//json_encode(simplexml_load_string($data));
        }
        return $d;
    }
    
    
    
    
    
    /**
     *根据请求方式返回json格式或jsonp格式
     * @param unknown $returnstyle 请求的方式，一般的ajax还是jsonp,true是json返回，false是jsonp返回
     * @param unknown $data           返回的信息,数组或json串格式
     */
    function returnjson($data,$returnstyle=true,$callback=''){
        //第一步先把msg根据浏览器语言加上
        if (!is_array($data)){
            $data=json_decode($data,true);
        }
        $codeslan=C('ERROR_CODES');
        if (array_key_exists($data['code'],$codeslan)){
            $lan=$codeslan[$data['code']];
            $data['code']=(int)$data['code'];
            $data['msg']=L($lan);
        }
        if (isset($data['code'])){
            $data['code']=(int)$data['code'];
        }
        writeOperationLog($data,'returnjson');//记录日志
        //浏览器语言判断结束
        $data=json_encode($data);
        
        //根据请求方式返回信息
        if (true==$returnstyle){//json格式
            echo $data;exit;
        }else{//jsonp格式
            echo  $callback.'('.$data.')';exit;
        }
    }
    
    
    /**
     * (PHP 5 >= 5.5.0, PHP 7)
     *array_column — 返回数组中指定的一列
     */
    // for php < 5.5
    if (!function_exists('array_column')) {
        function array_column($input, $column_key, $index_key = null) {
            $arr = array_map(function($d) use ($column_key, $index_key) {
                if (!isset($d[$column_key])) {
                    return null;
                }
                if ($index_key !== null) {
                    return array($d[$index_key] => $d[$column_key]);
                }
                return $d[$column_key];
            }, $input);

                if ($index_key !== null) {
                    $tmp = array();
                    foreach ($arr as $ar) {
                        $tmp[key($ar)] = current($ar);
                    }
                    $arr = $tmp;
                }
                return $arr;
        }
    }
    
    
    
    /** curl 获取 https 请求
     * @param String $url 请求的url
     * @param Array $data 要發送的數據
     * @param Array $header 请求时发送的header
     * @param bool $multi 是否传文件，false否，true是
     * @param int $timeout 超时时间，默认30s
     */
    function curl_https($url, $data=array(), $header=array(), $timeout=30, $multi = false,$method='POST'){
        $ch = curl_init();
        if ($method=='POST'){
            curl_setopt($ch, CURLOPT_POST, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        //http_build_query($data);
        false==$multi?curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)):curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 安全考虑，不跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $response = curl_exec($ch);
//         if($error=curl_error($ch)){
//             die($error);
//         }
        curl_close($ch);
        
        $arr['url']=$url;
        $arr['data']=checktype($data);
        $arr['header']=json_encode($header);
        $arr['multi']=(int)$multi;
        $arr['returndata']=checktypetwo($response);
        writeOperationLog($arr,'http');//记录日志
        return $response;
    }
    
    /**
     * 判断是否是合法的json
     * @param unknown $string
     * @return boolean
     */
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    
    /**
     * 两个时间戳的时间差
     * @param unknown $begin_time
     * @param unknown $end_time
     * @return multitype:number unknown
     */
    function timediff($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }
        else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        $remain = $remain%3600;
        $mins = intval($remain/60);
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }
    
    
    

    function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)objectToArray($v);
        }
        return $e;
    }
    
    /**
     * @desc    平台字段转为第三方商户的平台api接口所需的接口字段
     * @param unknown $keys        要转换的key,数组格式
     * @param unknown $params   array数组，数据，数组格式
     */
    function params_to_params($parms,array $keys){
        $keys= !is_array($keys)?json_decode($keys,true):$keys;
        $param='';
        foreach ($parms as $k => $v){
            if (array_key_exists($k,$keys))
                $param[$keys[$k]]=$v;
            else{
                echo 'Maybe something was warring';die;
            }
        }
        return $param;
    }
    
    
    /**
     * @desc    第三方商户的平台api接口返回的接口字段转为平台字段
     * @param unknown $keys        要转换的key,数组格式
     * @param unknown $params   array数组，数据，数组格式
     */
    function apiarr_to_params($parms,array $keys){
        //dump($parms);dump($keys);
        $keys= !is_array($keys)?json_decode($keys,true):$keys;
        $param='';
        foreach ($parms as $k => $v){//echo $k;dump($keys);dump(in_array($k,$keys));dump($v);
            foreach ($keys as $key => $val){
                $ptkey=array_search($k,$keys);
                if (false !=$ptkey){
                    $param[$ptkey]=$v;
                }
            }
        }
        return $param;
    }
    
    
    /**
     * @desc    key转换
     * @param array $parms  crm对接类传过来的参数
     * @param array $keys     配置文件定义的转换参数
     * @return Ambigous <string, unknown>
     */
    function apptocrmkeys(array $parms,array $keys){
        $param='';
        foreach ($parms as $key => $val){
            if (isset($keys[$key])){
                $param[$keys[$key]]=$val;
            }else {
                $param[$key]=$val;
            }
        }
        return $param;
    }
    
    
    /**
     * @desc    把数据转换为数组
     * @param unknown $data
     * @param unknown $type
     */
    function objtoarray($obj,$type){
        //把获取到的数据转换为数组
        if ('json'==$type){
            $data=json_decode($obj,true);
        }elseif ('xml'==$type){
            $data=xmltoarray($obj);
        }
        return $data;
    }
    
    
    /**
     * @desc    把xml转换成数组
     * @param unknown $data
     * @return unknown
     */
    function xmltoarray($data){
        $xml = simplexml_load_string($data,NULL,LIBXML_NOCDATA);
        $xmlarr= json_decode(json_encode($xml),TRUE);
        return $xmlarr;
    }
    
    /**
     * @desc    把xml转换成数组，收到的数据是特殊的字符串格式
     * @param unknown $data
     * @return unknown
     */
    function xmltoarray_csv($data){
        $data=str_replace('soap:Body','soapBody',$data);
        $xml = simplexml_load_string($data,NULL,LIBXML_NOCDATA);
        $xmlarr= json_decode(json_encode($xml),TRUE);
        if (null==$xmlarr['soapBody']['processdataResponse']['errormsg']){
            $arr=explode('	',$xmlarr['soapBody']['processdataResponse']['outputpara']);//dump($arr);
            $array['card']=$arr[1];
            $array['user']=$arr[2];
            $array['idcode']=$arr[9];
            $array['idnumber']=$arr[10];
            $array['cardtype']=$arr[4];
            $array['status']=$arr[5];
            $array['status_description']=$arr[6];
            $array['getcarddate']=$arr[7];
            $array['expirationdate']=$arr[8];
            $array['birthday']=$arr[11];
            $array['company']=$arr[14];
            $array['phone']=$arr[15];
            $array['mobile']=$arr[16];
            $array['address']=$arr[19];
            $array['score']=$arr[21];
            
            return $array;
        }else{
            return false;
        }
    }
    
    
    function is_https(){
        if(!isset($_SERVER['HTTPS']))
            return FALSE;
        if($_SERVER['HTTPS'] === 1){  //Apache
            return TRUE;
        }elseif($_SERVER['HTTPS'] === 'on'){ //IIS
            return TRUE;
        }elseif($_SERVER['SERVER_PORT'] == 443){ //其他
            return TRUE;
        }
        return FALSE;
//         if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'){
//             return TRUE;
//         }elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
//             return TRUE;
//         }elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'){
//             return TRUE;
//         }
//         return FALSE;
    }

    /**
     * 签名函数
     * @param array $params
     * @return string
     */
    function sign(array $params){
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
//             if (preg_match("/[\x7f-\xff]/", $str)){
//                 $v=urlencode($v);
//             }
            if ('' == $str) {
                $str .= $k . '=' . trim($v);
            } else {
                $str .= '&' . $k . '=' . trim($v);
            }
        }
        $sign=md5($str);//echo $sign;
        //新增签名日志
        $params['sign']=$sign;
        writeOperationLog($params,'sign_log');//记录日志
        return $sign;
    }
    
    
    /**
     * 生成卡号，并替换xml的相关字段
     * @param unknown $xml
     * @param array $admininfo
     * @param array $params
     * @return string
     */
    function shuijingchengcreatecard($xml,array $adminapiinfo,array $params,array $crmdata,$memdefault){//dump($crmdata);
        //dump($xml);
        $str=rand(100000,999999);
        $str='77'.$str;
        $db=M('mem',$crmdata['pre_table']);
        $sel=$db->where(array('cardno'=>$str))->select();
        if (null==$sel){
            $xml=str_replace('__card__',$str,$xml);
            $api_request=json_decode($adminapiinfo['api_request'],true);
            $xml=str_replace('__startdate__',date('YmdHis'),$xml);
            $xml=str_replace('__enddate__',date('YmdHis',strtotime("+50 year",time())),$xml);
            $xml=str_replace('__name__',$params['name'],$xml);
            $xml=str_replace('__mobile__',$params['mobile'],$xml);
            //dump($xml);
            return array('xml'=>$xml,'cardno'=>$str,'usermember'=>$params['name'],'getcarddate'=>date('YmdHis'),'expirationdate'=>date('YmdHis',strtotime("+50 year",time())) );
        }else {
            return shuijingchengcreatecard($xml,$adminapiinfo,$params,$crmdata,$memdefault);
        }
    }
    
    
    
    
    
    
    
    /**
     * 生成卡号，并替换xml的相关字段
     * @param unknown $xml
     * @param array $admininfo
     * @param array $params
     * @return string
     */
    function shuijingchengeditmemberfunction($xml,array $adminapiinfo,array $params,array $crmdata,$memdefault){//dump($crmdata);
        //dump($xml);
        $str=rand(100000,999999);
        $str='77'.$str;
        $db=M('mem',$crmdata['pre_table']);
        $sel=$db->where(array('cardno'=>$str))->select();
        if (null==$sel){
            $xml=str_replace('__card__',$params['cardno'],$xml);
            $api_request=json_decode($adminapiinfo['api_request'],true);
            $xml=str_replace('__startdate__',date('YmdHis'),$xml);
            $xml=str_replace('__enddate__',date('YmdHis',strtotime("+50 year",time())),$xml);
            $xml=str_replace('__name__',$params['name'],$xml);
            $xml=str_replace('__mobile__',$params['mobile'],$xml);
            //dump($xml);
            return array('xml'=>$xml,'cardno'=>$str,'usermember'=>$params['name'],'getcarddate'=>date('YmdHis'),'expirationdate'=>date('YmdHis',strtotime("+50 year",time())) );
        }else {
            return shuijingchengcreatecard($xml,$adminapiinfo,$params,$crmdata,$memdefault);
        }
    }
    
    
    /**
     * 
     * @param unknown $xml
     */
    function shuijingchengcreatecallbackfun($xml){
        $xml=str_replace('soap:Body','soapBody',$xml);
        $xml = simplexml_load_string($xml,NULL,LIBXML_NOCDATA);
        $xmlarr= json_decode(json_encode($xml),TRUE);
        if (null==$xmlarr['soapBody']['processdataResponse']['errormsg']){
            return true;
        }else {
            return false;
        }
    }
    
    function shuijingchengcutscoreputcontent($xml,$card,$score,$membername,$changestr){
        $str=$membername.date('Ymd').rand(10000,99999);
        $params['scorenumber']=$str;
        $re=new Common\Controller\RedisController();
        $redis=$re->connectredis();
        $redis->set($card.':scorenumber',$str);
        $str=$str.','.$card.',201,'.$score.',';
        $xml=str_replace($changestr,$str,$xml);//dump($xml);
        return $xml;
        
    }
    
    function custscorebackfunc($xml){
        $xml=str_replace('soap:Body','soapBody',$xml);
        $xml = simplexml_load_string($xml,NULL,LIBXML_NOCDATA);
        $xmlarr= json_decode(json_encode($xml),TRUE);
        if (null==$xmlarr['soapBody']['processdataResponse']['errormsg']){
            return true;
        }else {
            return false;
        }
    }
    
    /**
     * @desc 杭州积分添加接口函数
     */
    function shuijingchengaddscoreputcontent($xml,$card,$score,$scorecode,$changestr,$membername){
        $str=$membername.date('Ymd').rand(10000,99999).','.$scorecode;
        $re=new Common\Controller\RedisController();
        $redis=$re->connectredis();
        $redis->set($card.':scorenumber:'.'add',$str);
        $xml=str_replace($changestr,$str,$xml);//dump($xml);
        return $xml;
    }
    
    /**
     * @desc    判断客户端设备
     */
    function checkdevice(){
        $useragent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'^^';
        $client='other';
        if (stripos($useragent,'Android')){
            $client='android';
        }elseif (stripos($useragent,'iphone')){
            $client='iphone';
        }elseif (stripos($useragent,'ipod')){
            $client='ipod';
        }elseif (stripos($useragent,'ipad')){
            $client='ipad';
        }elseif (stripos($useragent,'mac os x')){
            $client='macosx';
        }
        $data['client']=$client;
        $data['ip']=get_client_ip();
        $data['date']=date('Y-m-d H:i:s');
        //写日志文件
        writeOperationLog($data,'device');
        //记录数量
        $rediss= new RedisController();
        $redis=$rediss->connectredis(2);
        $redis->incr($client);//总量加1
        
        $client=$client.':'.date('Y-m-d');
        $redis->incr($client);//当天加1
        
//         $db=M('client_list','total_');
//         $db->add($data);
//         $dbclientnums=M('client_nums','total_');
//         $sel=$dbclientnums->where(array('client'=>$data['client']))->select();
//         if (0==count($sel)) {
//             $dbclientnums->add(array('client'=>$data['client'],'clientnums'=>1));
//         }else{
//             $dbclientnums->where(array('client'=>$data['client']))->setInc('clientnums');
//         }
    }
    
    /**
     * 判断身份证号是否正确
     * @param unknown $vStr
     * @return boolean
     */
    function isIdCard($vStr){
        $vCity = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
        if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);
        if ($vLength == 18){
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18){
            $vSum = 0;
            for ($i = 17 ; $i >= 0 ; $i--){
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }
            if($vSum % 11 != 1) return false;
        }
        return true;
    }
    
    function echostr($str){
        writeOperationLog(array('str'=>$str), 'echostr');
        echo $str;exit;
        
    }
    
    
    /**
     * 保存二进制文件到服务器，主要用于微信接口接收用户的二进制文件
     * @param unknown $data    二进制文件
     * @param unknown $exts    允许的文件类型，如果为true，则视为不限文件类型
     * @param unknown $path    保存的路径
     * @param array $server  接收到的server  
     * @param array $allow    允许的上传头信息    如果为空数组，则视为允许上传所有类型
     */
    function saveBinaryFile($data, $exts, $path, array $server=array(), array $allow=array())
    {
        
        if (null == $allow || checkMine($server['CONTENT_TYPE'], $allow)){//验证mime类型
            //判断后缀名是否符合条件
            $check=getBinarytype($data);
            //如果限制了后缀名
            if (is_array($exts)){
                $ext=$check;
            }else{
                $ext=$check;
                $check= true===$exts ? true : false;
            }
            //如果后缀名错误
            if (false != $check){
                //保存文件
                $filename=time().'_'.mt_rand(10000000, 99999999).'.'.$ext;
                $path=$path.$filename;
                // 自动创建日志目录
                $log_dir = dirname($path);
                if (!is_dir($log_dir)) {
                    mkdir($log_dir, 0755, true);
                }
                //写入文件
                $file=fopen($path, 'w');
                fwrite($file, $data);
                $save=fclose($file);
                if (true == $save){
                    $code=array('code'=>200, 'path'=>$path);
                }
            }else{
                $code=array('code'=>1042);
            }
        }else{
            $code=array('code'=>1052);
        }
        return $code;
    }
    
    /**
     * 保存二进制文件到服务器，主要用于微信接口接收用户的二进制文件
     * @param unknown $data    二进制文件
     * @param unknown $exts    允许的文件类型，如果为true，则视为不限文件类型
     * @param unknown $path    保存的路径
     * @param array $server  接收到的server
     * @param array $allow    允许的上传头信息    如果为空数组，则视为允许上传所有类型
     * @param unknown $ext    文件后缀名
     */
    function saveBinaryFile_exts($data, $exts, $path, array $server=array(), array $allow=array(),$ext='')
    {
    
        if (null == $allow || checkMine($server['CONTENT_TYPE'], $allow)){//验证mime类型
            //判断后缀名是否符合条件
            if(empty($ext)){
                $check=getBinarytype($data);
                if (is_array($exts)){
                    $ext=$check;
                }else{
                    $ext=$check;
                    $check= true===$exts ? true : false;
                }
            }else{
                $check=true;
            }
            if (false != $check){
                //保存文件
                $filename=time().'_'.mt_rand(10000000, 99999999).'.'.$ext;
                $path=$path.$filename;
                // 自动创建日志目录
                $log_dir = dirname($path);
                if (!is_dir($log_dir)) {
                    mkdir($log_dir, 0755, true);
                }
                //写入文件
                $file=fopen($path, 'w');
                fwrite($file, $data);
                $save=fclose($file);
                if (true == $save){
                    $code=array('code'=>200, 'path'=>$path);
                }
          }else{
               $code=array('code'=>1042);
          }       
        }else{
            $code=array('code'=>1052);
        }
        return $code;
    }
    
    
    
    /**
     * 判断允许的mine类型
     * @param unknown $get
     * @param unknown $allow
     */
    function checkMine($get, $allow)
    {
        if (in_array($get, $allow)){
            return true;
        }else {
            return false;
        }
    }
    
    
    
    /**
     * 判断二进制流的文件格式
     * @param unknown $data
     * @return Ambigous <string, boolean>
     */
    function getBinarytype($data)
    {
        $bin = substr($data,0,2);
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';//echo '<br>'.$typeCode;
        //如果不知道要上传的类型的$typepCode，可以上传一个文件echo出来看看，虽然不保证完全正确，某些是会重复的
        switch ($typeCode)
        {
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
            case 7368:
                $fileType = 'mp3';
                break;
            default:
                $fileType= false;
        }
        return $fileType;
    }
    
    
    
    /**
     * base64特殊字符处理函数
     * @param unknown $str
     * @return mixed
     */
    function base_encode($str) {
        $src  = array("/","+","=");
        $dist = array("__a","__b","__c");
        $old  = base64_encode($str);
        $new  = str_replace($src,$dist,$old);
        return $new;
    }
    
    /**
     * base64特殊字符处理函数
     * @param unknown $str
     * @return string
     */
    function base_decode($str) {
        $src = array("__a","__b","__c");
        $dist  = array("/","+","=");
        $old  = str_replace($src,$dist,$str);
        $new = base64_decode($old);
        return $new;
    }
    
    
    
    function testfun(){
        echo C('LOG_FILE_SIZE');
        if(is_file('') && C('LOG_FILE_SIZE') <= filesize('') ){
            echo 23423;
        }else echo 1111;
    }
    
    
    function mkdir_ext($data,$path,$ext,$filename=''){
        $filename=time().'_'.mt_rand(10000000, 99999999).'.'.$ext;
        $path=$path.$filename;
        // 自动创建日志目录
        $log_dir = dirname($path);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        //写入文件
        $file=fopen($path, 'wb');
        fwrite($file, $data);
        $save=fclose($file);
//         $save=file_put_contents($path, $data);
        if (true == $save){
            $code=array('code'=>200, 'path'=>$path);
        }else{
            $code=array('code'=>1044);
        }
        return $code;
    }


    /**
     * @param $data key必须是从0开始，且连续
     * @param $path 文件路径
     * @param $ext  文件后缀
     */
    function CreateCsvFile($data, $path, $ext){
        $count=count($data);
        $filename=time().'_'.mt_rand(10000000, 99999999).'.'.$ext;
        $path=$path.$filename;
        // 自动创建日志目录
        $log_dir = dirname($path);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        $file=fopen($path, 'w');
        $m=0;
        $limit=1;
        for ($i=0; $i<$count; $i++){
            $m++;
            if ($m >= $limit){
                ob_flush();
                flush();
                $m = 0;
            }
            $csvfile=iconv('utf-8', 'GB2312//IGNORE', $data[$i]);
            $put[$i]=fputcsv($file, explode(',',$csvfile));
        }
        fclose($file);
        if (in_array(false, $put)){
            return false;
        }else{
            return $path;
        }
    }
    
    /**
     * 取两个数之间的小数，小数位数默认1位
     * @param real $min
     * @param number $max
     * @return number
     */
    function randomFloat($min = 0.1, $max = 1, $format=1) {
        $rand=$min + mt_rand() / mt_getrandmax() * ($max - $min);
        if ($rand <= $min){
            $rand = $rand+0.1;
        }
        if ($rand >= $max) {
            $rand = $rand - 0.1;
        }
        return number_format($rand, $format);
    }
    
    function cut_str($sourcestr,$cutlength)  
    {  
        $returnstr='';  
        $i=0;  
        $n=0;  
        $str_length=strlen($sourcestr);//字符串的字节数  
        while (($n<$cutlength) and ($i<=$str_length))  
        {  
            $temp_str=substr($sourcestr,$i,1);  
            $ascnum=Ord($temp_str);//得到字符串中第$i位字符的ascii码  
            if ($ascnum>=224)    //如果ASCII位高与224，  
            {  
                $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符          
                $i=$i+3;            //实际Byte计为3  
                $n++;            //字串长度计1  
            }  
            elseif ($ascnum>=192) //如果ASCII位高与192，  
            {  
                $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符  
                $i=$i+2;            //实际Byte计为2  
                $n++;            //字串长度计1  
            }  
            elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，  
            {  
                $returnstr=$returnstr.substr($sourcestr,$i,1);  
                $i=$i+1;            //实际的Byte数仍计1个  
                $n++;            //但考虑整体美观，大写字母计成一个高位字符  
            }  
            else                //其他情况下，包括小写字母和半角标点符号，  
            {  
                $returnstr=$returnstr.substr($sourcestr,$i,1);  
                $i=$i+1;            //实际的Byte数计1个  
                $n=$n+0.5;        //小写字母和半角标点等与半个高位字符宽...  
            }  
        }  
        return $returnstr;  
    }

    /**
     *   array value change arraykey
     *   @param array $info,key
     *   @return array
     */
    if (! function_exists ( 'ArrKeyFromId' )) {
        function ArrKeyFromId($Info,$key='id') {
            $gzIds = array();
            if(!empty($Info) && is_array($Info)){
                foreach($Info as $k=>$v){
                    $gzIds[(string)$v[$key]] = $v;
                }
            }
            return $gzIds;
        }
    }

    if (! function_exists ( 'ArrObjChangeList' )) {
        function ArrObjChangeList($Info) {
            $gzIds = array();
            if(!empty($Info) && is_array($Info)){
                foreach($Info as $k=>$v){
                    $gzIds[] = $v;
                }
            }
            return $gzIds;
        }
	}   
    /**
    *   array value change arraykey
    *   @param array $info,key
    *   @return array
    */
    if (! function_exists ( 'ArrKeyAll' )) {
        function ArrKeyAll($Info,$key='id',$type=1) {
            $gzIds = array();
            if(!empty($Info) && is_array($Info)){
                foreach($Info as $k=>$v){
                    $gzIds[] = $v[$key];
                }
            }
            if($type){ $gzIds = implode(',',$gzIds); }
            return $gzIds;
        }
    }

/**
 *   array search
 *   @param array $value,array
 *   @return bool
 */
if (! function_exists ( 'deep_in_array' )) {
    function deep_in_array($value, $array)
    {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }

            if(in_array($value, $item)) {
                return true;
            } else if(deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }
}
function namfunction(){
    return 'aaa';
}

    /**
     * 仅供微信发送客服消息时使用，json时，不将中文转义
     * 除图文消息外，其它消息类型都是二维数组
     */
    function json_encode_chinese($array)
    {
        if ('news' == $array['msgtype']){
            foreach ($array['news']['articles'] as $key => $value){
                $array['news']['articles'][$key]['title']=urlencode($array['news']['articles'][$key]['title']);
                $array['news']['articles'][$key]['description']=urlencode($array['news']['articles'][$key]['description']);
                $array['news']['articles'][$key]['url']=urlencode($array['news']['articles'][$key]['url']);
                $array['news']['articles'][$key]['picurl']=urlencode($array['news']['articles'][$key]['picurl']);
            }
        }else{
            foreach ($array[$array['msgtype']] as $item => $value) {
                $array[$array['msgtype']][$item]=urlencode($value);
            }
        }
        return urldecode(json_encode($array));
	}


// 过滤掉emoji表情
function filterEmoji($str)
{
	 $str = preg_replace_callback(
	   '/./u',
	   function (array $match) {
		return strlen($match[0]) >= 4 ? '' : $match[0];
	   },
	   $str);
	 
	  return $str;
}

/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http_auth($url, $params, $method = 'GET',$auth="", $header = array(), $multi = false, $timeout=6000){
    $opts = array(
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $header
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            //$opts[CURLOPT_RETURNTRANSFER] = true;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    if($auth) {
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
    }
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);

    $arr['url']=$url;
    $arr['data']=json_encode($params);
    $arr['method']=$method;
    $arr['header']=json_encode($header);
    $arr['multi']=(int)$multi;
    $arr['returndata']=logcheck($data);
    writeOperationLog($arr,'http');//记录日志
    return  $data;
}

/**
 * @desc 根据两点间的经纬度计算距离
 * @param float $lat 纬度值
 * @param float $lng 经度值
 */
function getDistance($lat1, $lng1, $lat2, $lng2){
    $earthRadius = 6367000; //approximate radius of earth in meters
    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;
    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}


