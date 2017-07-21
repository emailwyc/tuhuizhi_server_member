<?php
return array(
	//'配置项'=>'配置值'
	'URL_MODEL'=>2,
    'CONTROLLER_LEVEL'      =>  2,//多级控制器
    'LOAD_EXT_CONFIG'			=> 'db,xiaoju',//加载自定义配置文件
    'XIDANAPICODE'=>array(
//        'APIURL'=>'http://joycitycrmws.cofco.com',//test
//        'PORT' => 8081,//TEST
         'APIURL'=>'http://10.5.1.52',//正式
         'PORT'=>'37987',
        'USERNAME'=>'BE',
        'PASSWORD'=>'123',
        
    ),
    'CLIENT_URL'=>'http://rest.rtmap.com/ws/taxi/state/set',
    'TENCENTMAPAPI'=>array(
        'URL'=>'http://apis.map.qq.com/ws/geocoder/v1/',
        'KEY'=>'L2DBZ-J3ERF-CQTJA-JHQBX-5DJXK-XJBOS',
    ),
    'ORDER_STATUS'=>array(
        '0'=>'waiting',
        '300'=>'waiting',
        '400'=>'ordertaking',
        '410'=>'arrived',
        '500'=>'drivestart',
        '700'=>'drivestop',
    ),

//     'CAR'=>array(
//         'XIDAN'=>array(
//             'CLIENT_ID'=>'',
//             'CLIENT_SECRET'=>'',
//             'SIGN_KEY'=>'',
//             'API_URL'=>'http://api.es.xiaojukeji.com'
//         ),
//     ),

    

);