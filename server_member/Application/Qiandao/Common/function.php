<?php
/**
 * 公共函数库
 */
 

/**
 * 获取一个数在哪两个数之间
 * @param unknown $array
 */
function getmath($array,$num){
    $length=count($array);
    for ($i=0;$i<$length;$i++){
        $arr=explode(',',$array[$i]['datetime']);
        if ($num>=$arr[0] && $arr[1] >=$num){
            return array($arr[0],$arr[1]);
            
        }
    }
}





function get($str){
    $data = array();
    $parameter = explode('&',end(explode('?',$str)));
    foreach($parameter as $val){
        $tmp = explode('=',$val);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}

?>