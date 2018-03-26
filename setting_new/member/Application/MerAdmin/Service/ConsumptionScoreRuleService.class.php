<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 19/09/2017
 * Time: 11:06
 */

namespace MerAdmin\Service;


class ConsumptionScoreRuleService
{
    /**
     * 积分消费规则设置
     * @param $isopen
     * @param $scorenum
     * @param $admininfo
     * @return array
     */
    public static function SetSconsumptionScoreRule($isopen, $scorenum, $admininfo)
    {
        if ($isopen == 1) {
            foreach ($scorenum as $key => $value){
                if ($value['score'] < 0){//如果是开启状态，积分数小于零不符合规定
                    return array('code'=>1051, 'data'=>1);
                    break;
                }else{
                    $scorenum[$key]['score'] = (float)$value['score'];
                }
            }
        }
        //不是0或1报错
        if ($isopen != 1 && $isopen != 0) {
            return array('code'=>1051, 'data'=>2);
        }
        $d = D($admininfo['pre_table'].'default');
        $data = array(
            'isopen'=>$isopen,
            'score'=>$scorenum
        );
        $find = $d->where(array('customer_name'=>'sconsumptionscorerule'))->select();
        if ($find && count($find) == 1){//从前某一天，发现了一个tp里面不知道是不是bug的"bug"。但愿没人改这一行代码。
            $save = $d->where(array('customer_name'=>'sconsumptionscorerule'))->save(array('function_name'=>json_encode($data)));
        }else{
            $save = $d->add(array('customer_name'=>'sconsumptionscorerule', 'function_name'=>json_encode($data), 'description'=>'积分消费规则'));
        }

        if ($save !== false) {
            return array('code'=>200);
        }else{
            return array('code'=>104);
        }
    }


    /**
     * 获取配置
     * @param $admininfo
     * @return array
     */
    public static function GetSconsumptionScoreRule($admininfo)
    {
        $d = D($admininfo['pre_table'].'default');
        $select = $d->where(array('customer_name'=>'sconsumptionscorerule'))->select();
        if ($select && count($select) == 1) {
            return array('code'=>200, 'data'=>json_decode($select[0]['function_name'], true));
        }else{
            return array('code'=>104);
        }
    }
}