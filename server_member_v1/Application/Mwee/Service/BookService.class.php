<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 31/07/2017
 * Time: 20:41
 */

namespace Mwee\Service;


use Mwee\Controller\CommonMwee;

class BookService
{

    /**
     * 4.1.1获取预订配置信息接口
     * @param $sid
     * @return array
     */
    public static function bookInfo($sid)
    {
        $content = json_encode(array('vendorShopIds'=>$sid));
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/book/info', $data, 'POST', array(), true);

        //判断数据正确性
        if (is_json($re)){
            $arr = json_decode($re, true);
            if (50000 === $arr['code']){//如果code等于0，则成功，执行解密
                $data = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                if (is_json($data)){
                    $array = json_decode($data,true);
                    if (is_array($array)){
                            return array('code'=>200, 'data'=>$array);
                    }else{
                        return array('code'=>101, 'data'=>3);
                    }
                }else{
                    return array('code'=>101, 'data'=>2);
                }
            }else{
                return array('code'=>1082, 'data'=>$arr, 'msg'=>$arr['msg']);
            }
        }else{
            return array('code'=>101, 'data'=>1);
        }
    }


    /**
     * 4.1.5 预订余量接口
     * @param $sid
     * @param $date
     * @return array
     */
    public static function bookIdle($sid, $date)
    {
        $content = json_encode(array('vendorShopIds'=>$sid, 'orderDate'=>$date));
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/book/idle', $data, 'POST', array(), true);

        //判断数据正确性
        if (is_json($re)){
            $arr = json_decode($re, true);
            if (50000 === $arr['code']){//如果code等于，则成功，执行解密
                $data = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                if (is_json($data)){
                    $array = json_decode($data,true);
                    if (is_array($array)){
                        return array('code'=>200, 'data'=>$array);
                    }else{
                        return array('code'=>101, 'data'=>3);
                    }
                }else{
                    return array('code'=>101, 'data'=>2);
                }
            }else{
                return array('code'=>1082, 'data'=>$arr, 'msg'=>$arr['msg']);
            }
        }else{
            return array('code'=>101, 'data'=>1);
        }
    }

    /**
     * 订座提交
     */
    public static function bookSubmit($data, $admininfo)
    {
        $content = json_encode($data);
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/book/submit', $data, 'POST', array(), true);
        $dbdata = $data;
        //判断数据正确性
        if (is_json($re)){
            $arr = json_decode($re, true);
            if (60002 === $arr['code']){//如果code等于，则成功，执行解密
                $data = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                if (is_json($data)){
                    $array = json_decode($data,true);
                    if (is_array($array)){
                        $db = M('mweebookorder', $admininfo['pre_table']);
                        $dbdata['vendorOrderId'] = $array['vendorOrderId'];
                        $dbdata['onlineStatus'] = $array['onlineStatus'];
//                        dump($data);
                        $add = $db->add($dbdata);
                        return array('code'=>200, 'data'=>$array);
                    }else{
                        return array('code'=>101, 'data'=>3);
                    }
                }else{
                    return array('code'=>101, 'data'=>2);
                }
            }else{
                return array('code'=>1082, 'data'=>$arr, 'msg'=>$arr['msg']);
            }
        }else{
            return array('code'=>101, 'data'=>1);
        }
    }






    /**
     * 4.1.3 预订状态查询接口
     * @param $orderid
     */
    public static function bookStatus($orderid, $admininfo)
    {
        $content = json_encode(array('orderId'=>$orderid));
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/book/status', $data, 'POST', array(), true);

        //判断数据正确性
        if (is_json($re)){
            $arr = json_decode($re, true);
            if (60008 === $arr['code']){//如果code等于0，则成功，执行解密
                $data = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                if (is_json($data)){
                    $array = json_decode($data,true);
                    if (is_array($array)){
                        $db = M('mweebookorder', $admininfo['pre_table']);
                        $save = $db->where(array('orderId'=>$orderid))->save(array('status'=>$array['status']));
                        return array('code'=>200, 'data'=>$array);
                    }else{
                        return array('code'=>101, 'data'=>3);
                    }
                }else{
                    return array('code'=>101, 'data'=>2);
                }
            }else{
                return array('code'=>1082, 'data'=>$arr, 'msg'=>$arr['msg']);
            }
        }else{
            return array('code'=>101, 'data'=>1);
        }
    }
}