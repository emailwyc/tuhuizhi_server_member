<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 21/07/2017
 * Time: 10:32
 */

namespace Mwee\Service;


use Mwee\Controller\CommonMwee;

class GoodsMenuService
{
    /**
     * 美味不用等菜单
     * @param $id
     * @return array
     */
    public static function getGoodsMenu($id)
    {
        $content = json_encode(array('shopId'=>$id));
        $data['token'] = CommonMwee::$apptoken;
        $data['content'] = CommonMwee::encrypt($content, CommonMwee::$appkey);
        $re = http('http://api.mwee.cn/api/menu/goods', $data, 'POST', array(), true);

        //判断数据正确性
        if (is_json($re)){
            $arr = json_decode($re, true);
            if (0 === $arr['code']){//如果code等于0，则成功，执行解密
                $data = CommonMwee::decrypt($arr['content'], CommonMwee::$appkey);
                if (is_json($data)){
                    $array = json_decode($data,true);
                    if (is_array($array)){
                        if (count($array['categories']) > 0){
                            return array('code'=>200, 'data'=>$array);
                        }else{
                            return array('code'=>102, 'data'=>$array);
                        }
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