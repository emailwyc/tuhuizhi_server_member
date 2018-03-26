<?php
return array(
	//'配置项'=>'配置值'
    // 数据库配置信息
//    'DB_TYPE'   => 'mysql', // 数据库类型
//    'DB_HOST'   => 'localhost', // 服务器地址
//    'DB_NAME'   => 'rtmap_ka', // 数据库名
//    'DB_USER'   => 'root', // 用户名
//    'DB_PWD'    => 'root', // 密码
//    'DB_PORT'   => 3306, // 端口
//    'DB_PREFIX' => '', // 数据库表前缀
//    'DB_CHARSET'=> 'utf8', // 字符集
//    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
//
//    // 表单令牌
//    'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
//    'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
//    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
	//    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true
    'CONTROLLER_LEVEL'      =>  2,//多级控制器
	'ALIPAY_SET_LIST' => array(
		'GATEWAYURL'  => 'https://openapi.alipay.com/gateway.do',
		'APIVERSION'  => '1.0',
		'POSTCHARSET' => 'utf-8',
		'FORMAT'      => 'json',
        'ZHT_APPID'   => '2017111509955599',
        'ZHT_PRIKEY'  => 'MIIEpAIBAAKCAQEA4WIgB0tCv/gasl0ASSiYU+hfnwwAUpNjPgpoAGZ8bvPBymw1PPf8emE2Ys/ZIogW9PaxE8sum/IkQjX7mAr5wsK/leveVikX8Zk54zNvrP88vGPbbLlE0DxAG1uuDUCPIixgwi+MJiPUc9hmRszrtpDQEHQsbJDIJq2ZdniPs16IZfQR7XMjzbG0pEw0LKt7jvBcHH7OsJ38qY8Usg/OS2HFDV67e2qlcZc9WHiMvgd0s2bcW/e+jszKMaTD7rQVLqWvrJRg8LjyyxWYo0SnkThvmO+XoNz6fzNQIUvgBtB+UeWTc1FOEKd8CyH7Q/REETif3yH3UFk/qmud5oJZNQIDAQABAoIBABb6xD06JTIhf8dTyCWtZ+qWNLmopZfFw0aeFQCFcoZ1f/q9kagHuMxZgQwOGRt2OLD9PgzAmJ350EyX6HEWHWUIKjSE+gRa6EAP2WEa63X+CBlFLSgms6dvnTGs5VAglvdrAuqJooZ4/L5bAAKT/ix9E1m6HpVrJZ0b8husKyqkdcoUaGnf7eoQtqbuygJIl7s3emnqn0/vEo/vDUGA80q/s4Riq+Wqbjc0/pmtmsyOhpD48KDghLjgY0JunFCyhXOe1ateGLjChWQHl4uF3/uh3zqLoBb7vUfnpoW7lCO1jd7hY215PwAvRhIFEvaGDc4ON63YCIFlN/Ap1Q0/joECgYEA+h18Zoal3l0UzG1XJEioD62IzBZqC77qBuhyLPj7ebtEcMDOvYPMADoPDuurwWiQKZ/F2opRzUkKGBG+3mDBbM0VLOhZlkzNi8++786yHt5+w+UJYED9W8SQNkggF2YCAnICtCOpV1Yb9uP6ks9I4xaQjXRPE9RWhdqggQKbq5ECgYEA5q+sD7D149JIvyhrkB4l0y03OibWHOEPlT8Y4AoQFeVvdbQmjAPKU1HruRaXI6zELjVwXcZDkqpTM/hxF+w9XDYvOdAdhJLI6nJzaOIoMvmbmej3gkoK02IZ2fe9c0vrT34khu5Rb/gj1qGOC9VAyFcj+5S/fUpK2rT74CmumWUCgYBab+j4ZX16XuvTU0HsI71pFdVd/kjQAHM8ljYantjHVnhT0NOwYQSVnGive3W6VGW0N0piUBtuABf/RgNfA1tNTQZ4G3exSgoUoMSoj3OGh+sMSDfQrw8tbHC8v+2iqEbXvYPOwQpNQxyPdDW5eewf+JvCGikxwVibw8a/f0LskQKBgQDb9RsxpwD+Jq64nnjZZPWaAx4Ks6cqyCoMm6pDTTUDO85oQ8sRbDfJ9G24AONB3+T7TncC4x3hRcip4DUto1Lqjru++0J5+1/ZtCF5G1NLL5d/TJRbxe/GmF6f58nbmsMW++cmRSyff9HkQAzqGX94xyYHJ1DizSjMmw8jMXtKxQKBgQC1B4AjE59O7lOfhmEZL6mHQLPV/wLrU5buFHqEB3ehW02UFxB1MD7SMNqmatCHhZrk0jtlcF0/NNnbZtDxmlMUUGCy9kn4DV6MqtGJlGt7pfquJ6b30sBY+yu05NCHc0ATk1gYK6PNU0q9wEkIjz7jsM4doyymDEbMBs8yC3olTQ==',
        'ZHT_PUBKEY'  => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsCLD+QNodMEEjmT/opeszw+5pduaLFAfTzSpjqUpQIHd1+VafbTqxHXknRfEdWjdpGsWMoKa+mtsLtWAbkxqezUCLBrkrGwI0fSTBMkXRm+SBESXqjKoapIRHFVwcZBFfZdE9SpEtNeSU6DJBVNckQTbmslqsA+71YoNxnbLDPbNhmOVESy6IuFp6mn8iQ19EaFPyCKPGV7g9SnZl6qdC25+LQqYD5B7lsnrKQiGyyO3NdXe1/z2c+F+6UJf1yf5hprwdqj345U60IIVRYf/vURoH2ddlflc8UEbUVnK4FJ2fPSB3Wp88282CWKfIydnDbDfL8Njr6xUfo9k9jKsqQIDAQAB',
	)
);
