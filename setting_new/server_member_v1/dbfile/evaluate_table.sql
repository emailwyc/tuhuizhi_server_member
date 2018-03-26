SET FOREIGN_KEY_CHECKS=0; 
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_evaluate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(300) NOT NULL DEFAULT '' COMMENT '头像',
  `staff_num` varchar(30) NOT NULL COMMENT '员工编号',
  `star` tinyint(4) NOT NULL COMMENT '星星',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `message` varchar(255) NOT NULL COMMENT '评价消息',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_evaluate_class` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_evaluate_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `class_id` int(11) NOT NULL COMMENT '分类id',
  `tags_id` int(11) NOT NULL COMMENT '标签id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_evaluate_sfrel` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `staff_id` int(11) NOT NULL COMMENT '员工id',
  `class_id` int(11) NOT NULL COMMENT '分类id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_evaluate_staff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `number` varchar(30) NOT NULL COMMENT '工号',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码地址',
  `comment` varchar(255) NOT NULL DEFAULT '' COMMENT '评价次数统计',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `MeradminTablePrefix_evaluate_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(100) NOT NULL COMMENT '标签名称',
  `star` tinyint(4) NOT NULL COMMENT '星级1-5',
  `order` int(11) NOT NULL COMMENT '顺序：小到大',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;