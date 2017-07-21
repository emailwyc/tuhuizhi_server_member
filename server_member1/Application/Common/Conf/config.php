<?php
return array(
	//'配置项'=>'配置值'
    'MODULE_ALLOW_LIST'    =>    array('Home','Wechat','Localtest','Car','DevAdmin','Thirdwechat','Member','CrmService','Qiandao','Card','Integral','MerAdmin','Platformservice','Parkservice', 'ParkApp','PublicApi','Sign','CrmBackend','PublicService','ScoreTransfer','WeBuy','ResourcesApi','ClientApi','WIFI','EnterpriseWechat', 'Oywechat', 'Coupon', 'GroupMember', 'BuildManagement','ErpService', 'Pay', 'Mwee', 'AlipayService','Commands'),
    'LOAD_EXT_CONFIG'			=> 'wechat,errorlan,db',//加载自定义配置文件
    'LANG_SWITCH_ON' => true,   // 开启语言包功能
    'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'        => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'     => 'l', // 默认语言切换变量
    'URL_MODEL'         =>2,
    'LOG_FILE_SIZE'=>104857600,//日志文件大小
    'POI_URL'=>'http://lbsapi.rtmap.com:80/rtmap_lbs_api/v1/floor_poilist',
    'POI_APPKEY'=>'vUbk87ZHpF',
    'TULING_API_URL'=>'http://www.tuling123.com/openapi/api',
    'TULING_API_KEY'=>'79197f6b0966427aa2b910335ce8f117',
//     'TMPL_EXCEPTION_FILE'     =>  './Public/error.html',
//     'ERROR_PAGE' =>'./Public/error.html',
    #'ERROR_PAGE'=>'/xiaojukeji/Public/error.html', // 定义错误跳转页面URL地址
    //在什么客户端请求，微信？支付宝？浏览器？等？
//     'CLIENT_FROM'=>array(
//         'wechat'=>'openid',
//         'alipay'=>'userid',
//         'browser'=>'phone',
//     ),

    'CRM_BACKEND'=>array(
        '1'=>'积分调整',
        '2'=>'积分补入',
        '3'=>'兑换礼品',
        '4'=>'积分清零',
        '5'=>'开卡送积分',
        '6'=>'退货返积分',
        '7'=>'参加活动',
        '8'=>'礼品退货',
        '9'=>'储物柜',
        '10'=>'发展奖励',
        '11'=>'积分购买',
        '12'=>'签到送积分',
        '13'=>'停车扣积分',
        '14'=>'积分转赠',
        '15'=>'积分转赠失败'
    ),
    
    //redis队列KEY名
    'REDIS_QUEUE_NAME' => 'webstar:redislist:callback:queue',
   
    'BACKEND_CRM_OUTPUT_INPUT_UPDATE'=>array(
        'name'=>'name',
        'mobile'=>'mobile',
        'sex'=>'sex',
        'idcard'=>'idnumber',
    ),
    
    'BACKEND_CRM_OUTPUT_INPUT_CREATE'=>array(
        'name'=>'name',
        'mobile'=>'mobile',
        'sex'=>'sex',
        'idcard'=>'idnumber',
	),

	'VERSION_TYPE'=>array(
	    //'标示' => '描述',
		'member' => '会员系统',
	),

	'VERSION_PATH'=>array(
	    //'路径' => '标示',
		'member' => 'member',
	),
    'CAR_CODE'=>array('京','津','冀','晋','鲁','蒙','辽','吉','黑','沪','苏','浙','皖','闽','赣','豫','鄂','湘','粤','桂','琼','渝','川','贵','云','藏','陕','甘','青','宁','新','台'),

);
