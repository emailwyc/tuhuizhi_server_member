<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 25/12/2017
 * Time: 17:23
 */

namespace EnterpriseWechat\Service\Open;


use Common\Service\PublicService;
use Common\Service\RedisService;

class AppInfoService
{
    public static function getRelationShip($adminInfo, $appname)
    {
        $authInfo = RedisService::connectredis()->get('enterprise:wechat:total:enterprise:relation:info:' . $adminInfo['id'] . ':' . $appname);
        if (!$authInfo){
            $db = M('total_enterprise_relation');
            $data = $db->field('id' ,true)->where(['appname'=>$appname, 'admin_id'=>$adminInfo['id']])->find();
            if ($data){
                RedisService::connectredis()->set('enterprise:wechat:total:enterprise:relation:info:' . $adminInfo['id'] . ':' . $appname, json_encode($data), ['ex'=>86400]);
                $data= [
                    'code'=>200,
                    'data'=>$data
                ];
            }else{
                $data['code'] = 102;
            }
        }else{
            $data = [
                'code'=>200,
                'data'=>json_decode($authInfo, true)
            ];
        }
        return $data;
    }
}