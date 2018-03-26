<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 20/09/2017
 * Time: 17:10
 */

namespace CrmService\Service;


use Common\Service\RedisService;

class CrmService
{
    /**
     * 返回时200，可以做任何事
     * @param $admininfo
     * @param $usercard 卡号
     * @param $scorerule 积分限额,default表中村json格式
     * @param $score 本次要消费的积分数
     * @return array|mixed
     */
    public static function CheckScoreRule($admininfo, $usercard, $scorerule, $score,$unionid='')
    {
        $data = array(
            'card'=>$usercard,
            'key_admin'=>$admininfo['ukey'],
            'sign_key'=>$admininfo['signkey']
        );
        
        if($unionid != ''){
            $data['unionid'] = $unionid;
        }
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $userinfo = http(C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobycard', $data);
        if (is_json($userinfo)) {
            $userinfo = json_decode($userinfo, true);
            if ($userinfo['code'] == 200){
                $scorerule = json_decode($scorerule['function_name'], true);
                //如果是1的话，开启限制
                if (1 == $scorerule['isopen']){
                    $rules = array_column($scorerule['score'], 'score', 'code');
                    //限制规则，如果配置的积分数大于等于零则限制
                    $rulesscore = $rules[$userinfo['data']['cardtype']];//这个卡类型的积分限制



                    //1、先判断传入的积分数是否已经超过了最大限额，则不用查库查redis，也不设置redis，因为没有扣除、还没有消费
                    if ($score > $rulesscore){
//                        RedisService::connectredis()->set('crm:user:sconsumption:score:rule:' . $usercard, 'yes', array('ex'=>$tomorrowtime-time()));
                        return array('code'=>6000, 'data'=>array('scorerule'=>$rulesscore));
                    }

                    //2、第一步通过，判断积分规则数是否超过0
                    if ($rulesscore >= 0) {
                        //2.1：如果redis有值，验证一下
                        $isused = RedisService::connectredis()->get('crm:user:sconsumption:score:rule:' . $usercard);
//                        if ($isused != false) {
//                            $used = json_decode($isused, true);
//                            if (is_array($used)  && 'yes' == $used['isused']) {
//                                //已用积分数加传入的积分数是否超过设置的积分数，大B端的积分上线可能随时会改，如果判断积分数符合规则，则删除之前设置的redis
//                                if ( ($used['scorenum'] + $score) < $rulesscore) {
//                                    RedisService::connectredis()->del('crm:user:sconsumption:score:rule:' . $usercard);
//                                    //这里需要更新一下数据库，没写，所以整个大if暂时注释
//                                    return array('code'=>200);
//                                }elseif (($used['scorenum'] + $score) == $rulesscore){
//                                    //这里需要更新一下数据库，没写，所以整个大if暂时注释
//                                    return array('code'=>200);
//                                }else{
//                                    return array('code'=>6000, 'data'=>array('scorerule'=>$rulesscore));
//                                }
//                            }
//                        }


                        //2.2如果没有查到Redis，数据库查询积分数
                        $m = M();
                        $d = D($admininfo['pre_table'] . 'mem');
                        $user = null;

                        //没有字段的加个字段，有字段的查库
                        if (!$m->execute('Describe ' . $admininfo['pre_table'] . 'mem' . ' `sconsumptionscore`')){
                            $m->execute('alter table `' . $admininfo['pre_table'] . 'mem` ADD COLUMN `sconsumptionscore` varchar(100) DEFAULT "" COMMENT "用户每日积分限额";');
                            $user = array('sconsumptionscore'=>'');
                        }else{

                            $user = $d->field('sconsumptionscore')->where('`cardno` = "' . $usercard . '"')->find();
                        }
                        //2.2.1如果没有数据，则直接加库，并返回200
                        if ($user['sconsumptionscore'] == '') {
                            $array = array(
                                'date'=>date('Y-m-d'),
                                'scorenum'=>(float)$score
                            );
                            $d->where('`cardno` = ' . "'$usercard'")->save(array('sconsumptionscore'=>json_encode($array)));
                            return array('code'=>200);
                        }else{
                            //2.2.2如果库里面有数据，解析json，成功则判断，解析不成功则报错
                            $usertodayscore = json_decode($user['sconsumptionscore'], true);
                            if (isset($usertodayscore['date']) && isset($usertodayscore['scorenum'])) {
                                //如果不是今天，重新设置查询出来的数组，按道理说，应该更新一下数据库，但是为了减少对数据库的读写操作，不更新！！！
                                if ($usertodayscore['date'] != date('Y-m-d')) {
                                    $usertodayscore['date'] = date('Y-m-d');
                                    $usertodayscore['scorenum'] = 0;
                                }


                                //之前的积分数加本次要扣减的积分数如果大于配置的积分数，则报错，但不加redis，因为没有真正扣除，有可能下次要扣减的积分数不会超过配置的积分数
                                if (($score + $usertodayscore['scorenum']) > $rulesscore){
                                    return array('code'=>6000, 'data'=>array('scorerule'=>$rulesscore));
                                }
                                //当天消费积分数加本次要扣减的积分数小于配置的积分数
                                if (($score + $usertodayscore['scorenum']) < $rulesscore) {
                                    $array = array(
                                        'date'=>date('Y-m-d'),
                                        'scorenum'=>(float)$score + $usertodayscore['scorenum']
                                    );
                                    $d->where('`cardno` = ' . "'$usercard'")->save(array('sconsumptionscore'=>json_encode($array)));
                                    return array('code'=>200);
                                }
                                //当天消费积分数加本次要扣减的积分数等于配置的积分数，虽然很少有这种碰巧的时候
                                if (($score + $usertodayscore['scorenum']) == $rulesscore) {
                                    $array = array(
                                        'date'=>date('Y-m-d'),
                                        'scorenum'=>(float)$score + $usertodayscore['scorenum']
                                    );
                                    $d->where('`cardno` = ' . "'$usercard'")->save(array('sconsumptionscore'=>json_encode($array)));

                                    //下面会用到的一些参数
                                    $tomorrowtime = strtotime(date('Y-m-d'))+86400;//明天凌晨零点的时间戳（今天凌晨24点）
                                    RedisService::connectredis()->set('crm:user:sconsumption:score:rule:' . $usercard, json_encode(array('isused'=>'yes' ,'scorenum'=>(float)$score + $usertodayscore['scorenum'])), array('ex'=>$tomorrowtime-time()));
                                    return array('code'=>200);
                                }

                            }else{
                                return array('code'=>106, 'data'=>'cutscore!');
                            }
                        }
                    }else{
                        return array('code'=>200);
                    }
                }else{
                    return array('code'=>200);
                }
            }else{
                return $userinfo;
            }
        }else{
            return array('code'=>101);
        }
    }
}