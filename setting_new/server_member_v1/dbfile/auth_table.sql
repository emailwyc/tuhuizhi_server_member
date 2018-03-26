SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_api` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `api_type` int(4) NOT NULL COMMENT '接口类型ID',
  `api_request` text NOT NULL COMMENT '请求参数映射',
  `request_type` varchar(20) NOT NULL COMMENT '请求类型  http 或 webservice 或 https',
  `request_param_type` varchar(20) NOT NULL COMMENT '请求方式 post 或get',
  `request_data` text NOT NULL COMMENT '如果是webservice方式，并且是用http方式请求，xml字符串保存在这里',
  `response_data_type` varchar(20) NOT NULL COMMENT '返回数据类型 json 或xml',
  `api_response` varchar(500) NOT NULL COMMENT '返回参数映射',
  `api_url` varchar(100) NOT NULL COMMENT 'api地址',
  `header` varchar(150) DEFAULT '' COMMENT 'header头信息',
  `is_sign` tinyint(1) unsigned DEFAULT '0' COMMENT '是否需要签名，0否，1是',
  `from_id` int(100) NOT NULL DEFAULT '0' COMMENT '哪一个来源的id（total_from表的id）',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='西单接口表';

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_carpay_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `orderno` varchar(50) NOT NULL COMMENT '定单号',
  `openid` varchar(50) NOT NULL COMMENT '微信openid',
  `carno` varchar(32) NOT NULL COMMENT '车牌号',
  `total_fee` decimal(10,2) NOT NULL COMMENT '支付金额',
  `paytype` tinyint(1) NOT NULL COMMENT '支付类别,0代表微信支付,1代表积分支付',
  `client_orderno` varchar(50) DEFAULT NULL COMMENT '车场订单号(有些车场确认订单时需要传递订单号)',
  `begintime` int(10) NOT NULL DEFAULT '0' COMMENT '停车开始时间',
  `endtime` int(10) NOT NULL DEFAULT '0' COMMENT '停车结束时间',
  `freetime` int(11) DEFAULT '0' COMMENT '免费时长',
  `payfee` decimal(10,2) DEFAULT NULL COMMENT '实际支付金额',
  `discountfee` decimal(10,2) DEFAULT '0.00' COMMENT '折扣金额',
  `lowpricefee` decimal(10,2) DEFAULT '0.00' COMMENT '低价抵扣金额',
  `freetimefee` decimal(10,2) DEFAULT '0.00' COMMENT '免费时长抵扣金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态,0代表失败,1代表支付成功,2代表通知车场成功',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '下单时间',
  `invoice_time` int(10) DEFAULT '0' COMMENT '开发票时间',
  `invoice_admin` varchar(32) DEFAULT NULL COMMENT '开发票人',
  `pay_time` int(10) DEFAULT '0' COMMENT '支付时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='停车缴费定单表';

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_default` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `customer_name` varchar(50) NOT NULL COMMENT '用途',
  `function_name` text NOT NULL COMMENT '用途属性',
  `description` varchar(150) DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商户常量配置表';

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(100) NOT NULL DEFAULT '0',
  `gid` int(100) NOT NULL DEFAULT '0',
  `openid` varchar(50) DEFAULT '',
  `phone` varchar(11) DEFAULT '',
  `content` text NOT NULL,
  `createtime` int(10) NOT NULL,
  `status` int(4) NOT NULL DEFAULT '1' COMMENT '是否删除:\n1:正常\n2:删除',
  `mem_id` int(4) DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_integral_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardno` varchar(30) NOT NULL COMMENT '卡号',
  `starttime` datetime NOT NULL COMMENT '时间',
  `status` int(4) NOT NULL COMMENT '状态：\n1、兑换成功扣除积分成功\n2、兑换失败返回积分失败\n3、兑换失败返回积分成功',
  `integral` int(11) NOT NULL COMMENT '积分数',
  `description` varchar(50) DEFAULT NULL COMMENT '原由',
  `openid` varchar(32) DEFAULT NULL COMMENT 'openid',
  `pid` int(10) DEFAULT NULL COMMENT '奖品id',
  `prize_name` varchar(100) DEFAULT NULL COMMENT '奖品名称',
  `activity_id` int(10) DEFAULT NULL COMMENT '活动id',
  `code` varchar(100) DEFAULT NULL COMMENT 'code',
  `buildid` varchar(255) DEFAULT NULL COMMENT 'buildid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_manual` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `title` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '标题对应内容\n',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_mem` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) CHARACTER SET utf8 DEFAULT '' COMMENT '微信会员名',
  `password` varchar(30) DEFAULT '',
  `cardno` varchar(50) DEFAULT '' COMMENT '会员卡号',
  `wecchatphone` varchar(15) DEFAULT '',
  `openid` varchar(50) DEFAULT '' COMMENT '会员openid',
  `headerimg` varchar(255) DEFAULT '' COMMENT '会员头像',
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '本次添加时间',
  `usermember` varchar(100) NOT NULL DEFAULT '' COMMENT '会员卡用户名',
  `idcode` smallint(1) NOT NULL DEFAULT '0' COMMENT '证件编码',
  `idnumber` varchar(40) NOT NULL DEFAULT '' COMMENT '证件编码',
  `status` varchar(5) NOT NULL DEFAULT '' COMMENT '状态',
  `status_description` varchar(5) NOT NULL DEFAULT '' COMMENT '详细状态',
  `getcarddate` varchar(25) NOT NULL DEFAULT '' COMMENT '领卡日期',
  `expirationdate` varchar(25) NOT NULL DEFAULT '' COMMENT '过期日期',
  `birthday` varchar(50) NOT NULL DEFAULT '' COMMENT '生日',
  `company` varchar(150) NOT NULL DEFAULT '' COMMENT '公司信息',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '通讯地址',
  `sex` varchar(32) NOT NULL COMMENT '性别',
  `cookie` varchar(32) NOT NULL COMMENT '用户cookie加密串',
  `score_num` float(11,2) DEFAULT '0.00' COMMENT '用户积分',
  `is_del` smallint(1) DEFAULT '1' COMMENT '是否删除，0删除，1正常显示',
  `star` varchar(30) DEFAULT '' COMMENT '星座',
  `career` varchar(40) DEFAULT '' COMMENT '职业',
  `wechat` varchar(50) DEFAULT '' COMMENT '微信号',
  `remark` varchar(50) DEFAULT '' COMMENT '备注',
  `email` varchar(50) DEFAULT '' COMMENT '邮箱',
  `level` varchar(30) NOT NULL DEFAULT '1' COMMENT '会员等级',
  `province` int(7) DEFAULT '0' COMMENT '省',
  `city` int(7) DEFAULT '0' COMMENT '市',
  `district` int(7) DEFAULT '0' COMMENT '区',
  `hobby` varchar(50) DEFAULT '',
  `ischangeone` smallint(1) NOT NULL DEFAULT '0' COMMENT 'C端身份证号号生日是否只能修改一次，1是，0不是',
  `parkft` int(11) DEFAULT '0' COMMENT '停车免费时长使用时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cardno` (`cardno`),
  KEY `mobile` (`mobile`),
  KEY `openid` (`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商户会员表';

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_score_record` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `cardno` varchar(30) NOT NULL DEFAULT '' COMMENT '卡号',
  `scorenumber` float(20,2) NOT NULL DEFAULT '0.00' COMMENT '扣除的积分数',
  `why` varchar(200) NOT NULL COMMENT '积分扣除理由',
  `scorecode` varchar(30) NOT NULL DEFAULT '' COMMENT '交易编号',
  `cutadd` smallint(1) NOT NULL DEFAULT '0' COMMENT '积分类型，1减还是2加',
  `datetime` varchar(30) DEFAULT '',
  `backend_admin` varchar(50) DEFAULT NULL COMMENT '操作管理员',
  `is_del` int(11) NOT NULL DEFAULT '1' COMMENT '是否删除：1、否\n0、是',
  `store` varchar(70) DEFAULT '' COMMENT '门店',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='积分流水';

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_score_type` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `img_src` varchar(200) NOT NULL COMMENT '图片路径',
  `createtime` datetime NOT NULL COMMENT '创建时间',
  `status` int(4) NOT NULL COMMENT '审核状态：\n1、等待审核\n2、审核通过\n3、审核失败',
  `user_mobile` varchar(30) NOT NULL COMMENT '用户手机号',
  `ordernumber` varchar(30) DEFAULT NULL COMMENT '订单号',
  `opertime` datetime DEFAULT NULL COMMENT '审核时间',
  `score_number` int(4) DEFAULT NULL COMMENT '积分数',
  `backend_user` varchar(30) DEFAULT NULL COMMENT '审核人',
  `username` varchar(30) NOT NULL COMMENT '用户名',
  `cardno` varchar(30) NOT NULL COMMENT '卡号',
  `money` float(10,2) DEFAULT NULL COMMENT '消费金额',
  `store` varchar(50) DEFAULT NULL COMMENT '门店',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分补录';

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_scoretransfer` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `scorenumber` int(50) NOT NULL COMMENT '分享的积分数',
  `sharetime` int(20) NOT NULL COMMENT '分享时的时间戳',
  `shareusercard` varchar(20) NOT NULL DEFAULT '' COMMENT '分享人的卡号',
  `sharewechatname` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '分享者微信用户名',
  `sharerheaderimg` varchar(300) NOT NULL COMMENT '分享人头像',
  `sharermobile` varchar(15) NOT NULL DEFAULT '' COMMENT '分享人手机号',
  `duetime` int(20) NOT NULL COMMENT '过期时间戳',
  `receiveusercard` varchar(20) NOT NULL COMMENT '领取人的卡号',
  `receivewechatuser` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '领取者的微信用户名',
  `receiverheaderimg` varchar(300) NOT NULL DEFAULT '' COMMENT '领取者头像',
  `receivermobile` varchar(15) NOT NULL DEFAULT '' COMMENT '领取人手机号',
  `receivetime` int(20) NOT NULL COMMENT '领取时间',
  `urlstr` varchar(200) NOT NULL DEFAULT '' COMMENT '分享后C端URL内的字符串',
  `isreceive` smallint(1) NOT NULL DEFAULT '0' COMMENT '0未领取，1已领取，2已超时',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_coin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardno` int(11) NOT NULL COMMENT '卡号',
  `starttime` datetime NOT NULL COMMENT '时间',
  `status` int(4) NOT NULL COMMENT '状态：\n1、兑换成功扣除积分成功\n2、兑换失败返回积分失败\n3、兑换失败返回积分成功',
  `integral` int(11) NOT NULL COMMENT '积分数',
  `description` varchar(50) DEFAULT NULL COMMENT '原由',
  `openid` varchar(32) DEFAULT NULL COMMENT 'openid',
  `pid` int(10) DEFAULT NULL COMMENT '奖品id',
  `prize_name` varchar(100) DEFAULT NULL COMMENT '奖品名称',
  `activity_id` int(10) DEFAULT NULL COMMENT '活动id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_squared` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(500) DEFAULT '' COMMENT '跳转url地址',
  `logo` varchar(200) DEFAULT '' COMMENT '图片地址',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `isverify` smallint(1) NOT NULL DEFAULT '1' COMMENT '是否需要验证',
  `time` bigint(15) NOT NULL COMMENT '时间戳',
  `content` varchar(50) NOT NULL DEFAULT '' COMMENT '时间戳',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `isopenedactivity` smallint(1) DEFAULT '1' COMMENT '活动是否开启',
  `column_id` int(11) DEFAULT NULL COMMENT '子栏目id',
  `catalog_id` int(11) NOT NULL COMMENT '类别id',
  `postion` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:底部2：顶部',
  `istwolevel` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是二级菜单1:是0：否',
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_nav_resour` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '标识',
  `name` varchar(50) DEFAULT NULL COMMENT '名字',
  `link` varchar(200) DEFAULT NULL COMMENT '链接',
  `property` varchar(60) DEFAULT NULL COMMENT '属性：\n图片地址或者按钮颜色',
  `author` varchar(50) DEFAULT '' COMMENT '作者',
  `content` text COMMENT '图文描述',
  `sort` int(7) DEFAULT NULL COMMENT '排序',
  `type_id` int(11) DEFAULT NULL COMMENT '导航id',
  `createtime` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MeradminTablePrefix_navigation`;
CREATE TABLE `MeradminTablePrefix_navigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '标识',
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态：\n1、启用\n2、禁用',
  `position` char(30) NOT NULL COMMENT '位置：\n上：top\n中：center\n下：foot\n左：left\n右：right',
  `url` varchar(200) NOT NULL COMMENT '跳转地址',
  `bg_color` varchar(20) NOT NULL DEFAULT '' COMMENT '背景颜色',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
INSERT INTO `MeradminTablePrefix_navigation` VALUES ('1', '顶部广告', '1', 'top', '/tinymall/topad', '');
INSERT INTO `MeradminTablePrefix_navigation` VALUES ('2', '功能区', '1', 'center', '/tinymall/facility', '');
INSERT INTO `MeradminTablePrefix_navigation` VALUES ('3', '底部广告', '1', 'foot', '/tinymall/newbottom', '');

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_scanreceiptprizenums` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `ruleclassmoney` decimal(65,2) NOT NULL COMMENT '规则内的总品类的商品总价',
  `prizenums` int(100) NOT NULL COMMENT '总抽奖次数',
  `prizednums` int(100) NOT NULL DEFAULT '0' COMMENT '已抽奖次数',
  `satisfied` decimal(65,2) NOT NULL DEFAULT '0.00' COMMENT '基数',
  `activityid` varchar(1000) NOT NULL COMMENT '活动id',
  `scanid` int(100) NOT NULL COMMENT '哪一个的id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_scanshoppingreceipt` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `openid` varchar(30) NOT NULL DEFAULT '' COMMENT '微信openid',
  `paidamount` decimal(65,2) DEFAULT NULL COMMENT '实付金额',
  `receiptid` varchar(100) NOT NULL COMMENT '小票流水号',
  `shopentityname` varchar(100) DEFAULT '' COMMENT '商家名称',
  `scorenum` decimal(65,2) DEFAULT NULL COMMENT '赠送的积分金额',
  `terminalnumber` varchar(100) DEFAULT '' COMMENT '收银机号',
  `prizednums` int(100) NOT NULL DEFAULT '0' COMMENT '已抽奖次数，默认0',
  `prizenums` int(100) NOT NULL COMMENT '抽奖次数',
  `createtime` bigint(255) DEFAULT NULL COMMENT '扫码时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_scanshoppingreceiptcouponsettings` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `settingsid` int(100) NOT NULL COMMENT '设置表主键id',
  `satisfied` decimal(65,2) NOT NULL COMMENT '金额',
  `activityid` int(100) NOT NULL COMMENT '活动id',
  `classes` text NOT NULL COMMENT '品类编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_scanshoppingreceiptsettings` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `rulename` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
  `starttime` bigint(200) NOT NULL COMMENT '规则开始时间',
  `endtime` bigint(200) NOT NULL COMMENT '规则结束时间',
  `isopenscore` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否开启赠送积分，0否，1是，默认0',
  `isopencoupon` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否赠送优惠券，0否，1是，默认0',
  `isrepeatedlycoupon` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否可循环赠送优惠券，0否，1是，默认0',
  `isclass` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否区分品类，0否，1是，默认0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_receipt` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule` text,
  `icon` varchar(200) DEFAULT NULL COMMENT '抽奖的图标',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(100) NOT NULL DEFAULT '0',
  `gid` int(100) NOT NULL DEFAULT '0',
  `openid` varchar(50) DEFAULT '',
  `phone` varchar(11) DEFAULT '',
  `content` text NOT NULL,
  `createtime` int(10) NOT NULL,
  `status` int(4) NOT NULL DEFAULT '1' COMMENT '是否删除:1:正常2:删除',
  `mem_id` int(4) DEFAULT '0' COMMENT '用户ID',
  `is_message` int(4) NOT NULL DEFAULT '1' COMMENT '1为意见反馈,2为留言',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_couponbanner` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `imgurl` varchar(255) NOT NULL COMMENT '图片在七牛上的地址',
  `sort` smallint(10) NOT NULL DEFAULT '1' COMMENT '排序',
  `jumpurl` varchar(50) NOT NULL DEFAULT '' COMMENT '图片跳转url',
  `buildid` varchar(20) NOT NULL DEFAULT '' COMMENT 'buildid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_ques_group` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(20) NOT NULL,
  `group_des` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_qrcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(500) DEFAULT '' COMMENT '描述',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `visitnum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总访问量',
  `createtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_qrcode_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `qid` int(11) NOT NULL,
  `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `date,qid` (`date`,`qid`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `MeradminTablePrefix_black_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
