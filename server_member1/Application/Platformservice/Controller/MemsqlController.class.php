<?php
namespace Platformservice\Controller;

use Common\Controller\RedisController;


/**
 * 新增场馆后，批量新增数据表
 * @author ut
 *
 */
class MemsqlController extends PlatcommonController
{

//    protected $redis;
    public function _initialize(){
        parent::_initialize();
        $redisdb=new RedisController();
        $this->redis=$redisdb->connectredis();
        $this->sql='
CREATE TABLE IF NOT EXISTS `{tablename}api` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT \'索引id\',
  `api_type` int(4) NOT NULL COMMENT \'接口类型ID\',
  `api_request` text NOT NULL COMMENT \'请求参数映射\',
  `request_type` varchar(20) NOT NULL COMMENT \'请求类型  http 或 webservice 或 https\',
  `request_param_type` varchar(20) NOT NULL COMMENT \'请求方式 post 或get\',
  `request_data` text NOT NULL COMMENT \'如果是webservice方式，并且是用http方式请求，xml字符串保存在这里\',
  `response_data_type` varchar(20) NOT NULL COMMENT \'返回数据类型 json 或xml\',
  `api_response` varchar(500) NOT NULL COMMENT \'返回参数映射\',
  `api_url` varchar(100) NOT NULL COMMENT \'api地址\',
  `header` varchar(150) DEFAULT \'\' COMMENT \'header头信息\',
  `is_sign` tinyint(1) unsigned DEFAULT \'0\' COMMENT \'是否需要签名，0否，1是\',
  `from_id` int(100) NOT NULL DEFAULT \'0\' COMMENT \'哪一个来源的id（total_from表的id）\',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'接口表\';

CREATE TABLE IF NOT EXISTS `{tablename}carpay_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT \'索引ID\',
  `orderno` varchar(50) NOT NULL COMMENT \'定单号\',
  `openid` varchar(50) NOT NULL COMMENT \'微信openid\',
  `carno` varchar(32) NOT NULL COMMENT \'车牌号\',
  `total_fee` decimal(10,2) NOT NULL COMMENT \'支付金额\',
  `paytype` tinyint(1) NOT NULL COMMENT \'支付类别,0代表微信支付,1代表积分支付\',
  `client_orderno` varchar(50) DEFAULT NULL COMMENT \'车场订单号(有些车场确认订单时需要传递订单号)\',
  `begintime` int(10) NOT NULL DEFAULT \'0\' COMMENT \'停车开始时间\',
  `endtime` int(10) NOT NULL DEFAULT \'0\' COMMENT \'停车结束时间\',
  `freetime` int(11) DEFAULT \'0\' COMMENT \'免费时长\',
  `payfee` decimal(10,2) DEFAULT NULL COMMENT \'实际支付金额\',
  `discountfee` decimal(10,2) DEFAULT \'0.00\' COMMENT \'折扣金额\',
  `lowpricefee` decimal(10,2) DEFAULT \'0.00\' COMMENT \'低价抵扣金额\',
  `freetimefee` decimal(10,2) DEFAULT \'0.00\' COMMENT \'免费时长抵扣金额\',
  `status` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'支付状态,0代表失败,1代表支付成功,2代表通知车场成功\',
  `createtime` int(10) NOT NULL DEFAULT \'0\' COMMENT \'下单时间\',
  `invoice_time` int(10) DEFAULT \'0\' COMMENT \'开发票时间\',
  `invoice_admin` varchar(32) DEFAULT NULL COMMENT \'开发票人\',
  `pay_time` int(10) DEFAULT \'0\' COMMENT \'支付时间\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8 COMMENT=\'停车缴费定单表\';

CREATE TABLE IF NOT EXISTS `{tablename}default` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT \'索引id\',
  `customer_name` varchar(50) NOT NULL COMMENT \'用途\',
  `function_name` text NOT NULL COMMENT \'用途属性\',
  `description` varchar(150) DEFAULT \'\' COMMENT \'描述\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT=\'商户常量配置表\';

CREATE TABLE IF NOT EXISTS `{tablename}feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `content` text NOT NULL,
  `createtime` int(10) NOT NULL,
  `status` int(4) NOT NULL DEFAULT \'1\' COMMENT \'是否删除:\n1:正常\n2:删除\',
  `mem_id` int(4) NOT NULL COMMENT \'用户ID\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{tablename}integral_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardno` int(11) NOT NULL COMMENT \'卡号\',
  `starttime` datetime NOT NULL COMMENT \'时间\',
  `status` int(4) NOT NULL COMMENT \'状态：\n1、兑换成功扣除积分成功\n2、兑换失败返回积分失败\n3、兑换失败返回积分成功\',
  `integral` int(11) NOT NULL COMMENT \'积分数\',
  `description` varchar(50) DEFAULT NULL COMMENT \'原由\',
  `openid` varchar(32) DEFAULT NULL COMMENT \'openid\',
  `pid` int(10) DEFAULT NULL COMMENT \'奖品id\',
  `prize_name` varchar(100) DEFAULT NULL COMMENT \'奖品名称\',
  `activity_id` int(10) DEFAULT NULL COMMENT \'活动id\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `{tablename}manual` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT \'索引ID\',
  `title` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT \'标题\',
  `content` text NOT NULL COMMENT \'标题对应内容\n\',
  `sort` int(11) NOT NULL DEFAULT \'0\' COMMENT \'排序\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{tablename}mem` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) CHARACTER SET utf8mb4 DEFAULT \'\' COMMENT \'微信会员名\',
  `password` varchar(30) DEFAULT \'\',
  `cardno` varchar(50) DEFAULT \'\' COMMENT \'会员卡号\',
  `wecchatphone` varchar(15) DEFAULT \'\',
  `openid` varchar(50) DEFAULT \'\' COMMENT \'会员openid\',
  `headerimg` varchar(255) DEFAULT \'\' COMMENT \'会员头像\',
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'本次添加时间\',
  `usermember` varchar(100) NOT NULL DEFAULT \'\' COMMENT \'会员卡用户名\',
  `idcode` smallint(1) NOT NULL DEFAULT \'0\' COMMENT \'证件编码\',
  `idnumber` varchar(40) NOT NULL DEFAULT \'\' COMMENT \'证件编码\',
  `status` varchar(5) NOT NULL DEFAULT \'\' COMMENT \'状态\',
  `status_description` varchar(5) NOT NULL DEFAULT \'\' COMMENT \'详细状态\',
  `getcarddate` varchar(25) NOT NULL DEFAULT \'\' COMMENT \'领卡日期\',
  `expirationdate` varchar(25) NOT NULL DEFAULT \'\' COMMENT \'过期日期\',
  `birthday` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'生日\',
  `company` varchar(150) NOT NULL DEFAULT \'\' COMMENT \'公司信息\',
  `phone` varchar(20) NOT NULL DEFAULT \'\' COMMENT \'联系电话\',
  `mobile` varchar(20) NOT NULL DEFAULT \'\' COMMENT \'手机\',
  `address` varchar(255) NOT NULL DEFAULT \'\' COMMENT \'通讯地址\',
  `sex` varchar(32) NOT NULL COMMENT \'性别\',
  `cookie` varchar(32) NOT NULL COMMENT \'用户cookie加密串\',
  `score_num` float(11,2) DEFAULT \'0.00\' COMMENT \'用户积分\',
  `is_del` smallint(1) DEFAULT \'1\' COMMENT \'是否删除，0删除，1正常显示\',
  `star` varchar(30) DEFAULT \'\' COMMENT \'星座\',
  `career` varchar(40) DEFAULT \'\' COMMENT \'职业\',
  `wechat` varchar(50) DEFAULT \'\' COMMENT \'微信号\',
  `remark` varchar(50) DEFAULT \'\' COMMENT \'备注\',
  `email` varchar(50) DEFAULT \'\' COMMENT \'邮箱\',
  `level` varchar(30) NOT NULL DEFAULT \'1\' COMMENT \'会员等级\',
  `province` int(7) DEFAULT \'0\' COMMENT \'省\',
  `city` int(7) DEFAULT \'0\' COMMENT \'市\',
  `district` int(7) DEFAULT \'0\' COMMENT \'区\',
  `hobby` varchar(50) DEFAULT \'\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cardno_unique` (`cardno`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COMMENT=\'商户会员表\';

CREATE TABLE IF NOT EXISTS `{tablename}score_record` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `cardno` varchar(30) NOT NULL DEFAULT \'\' COMMENT \'卡号\',
  `scorenumber` float(20,2) NOT NULL DEFAULT \'0.00\' COMMENT \'扣除的积分数\',
  `why` varchar(200) NOT NULL COMMENT \'积分扣除理由\',
  `scorecode` varchar(30) NOT NULL DEFAULT \'\' COMMENT \'交易编号\',
  `cutadd` smallint(1) NOT NULL DEFAULT \'0\' COMMENT \'积分类型，1减还是2加\',
  `datetime` varchar(30) DEFAULT \'\',
  `backend_admin` varchar(50) DEFAULT NULL COMMENT \'操作管理员\',
  `is_del` int(11) NOT NULL DEFAULT \'1\' COMMENT \'是否删除：1、否\n0、是\',
  `store` varchar(70) DEFAULT \'\' COMMENT \'门店\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COMMENT=\'积分流水\';

CREATE TABLE IF NOT EXISTS `{tablename}score_type` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT \'索引id\',
  `img_src` varchar(200) NOT NULL COMMENT \'图片路径\',
  `createtime` datetime NOT NULL COMMENT \'创建时间\',
  `status` int(4) NOT NULL COMMENT \'审核状态：\n1、等待审核\n2、审核通过\n3、审核失败\',
  `user_mobile` varchar(30) NOT NULL COMMENT \'用户手机号\',
  `ordernumber` varchar(30) DEFAULT NULL COMMENT \'订单号\',
  `opertime` datetime DEFAULT NULL COMMENT \'审核时间\',
  `score_number` int(4) DEFAULT NULL COMMENT \'积分数\',
  `backend_user` varchar(30) DEFAULT NULL COMMENT \'审核人\',
  `username` varchar(30) NOT NULL COMMENT \'用户名\',
  `cardno` varchar(30) NOT NULL COMMENT \'卡号\',
  `money` float(10,2) DEFAULT NULL COMMENT \'消费金额\',
  `store` varchar(50) DEFAULT NULL COMMENT \'门店\',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'奥永积分补录\';

CREATE TABLE IF NOT EXISTS `{tablename}scoretransfer` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `scorenumber` int(50) NOT NULL COMMENT \'分享的积分数\',
  `sharetime` int(20) NOT NULL COMMENT \'分享时的时间戳\',
  `shareusercard` varchar(20) NOT NULL DEFAULT \'\' COMMENT \'分享人的卡号\',
  `sharewechatname` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT \'\' COMMENT \'分享者微信用户名\',
  `sharerheaderimg` varchar(300) NOT NULL COMMENT \'分享人头像\',
  `sharermobile` varchar(15) NOT NULL DEFAULT \'\' COMMENT \'分享人手机号\',
  `duetime` int(20) NOT NULL COMMENT \'过期时间戳\',
  `receiveusercard` varchar(20) NOT NULL COMMENT \'领取人的卡号\',
  `receivewechatuser` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT \'\' COMMENT \'领取者的微信用户名\',
  `receiverheaderimg` varchar(300) NOT NULL DEFAULT \'\' COMMENT \'领取者头像\',
  `receivermobile` varchar(15) NOT NULL DEFAULT \'\' COMMENT \'领取人手机号\',
  `receivetime` int(20) NOT NULL COMMENT \'领取时间\',
  `urlstr` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'分享后C端URL内的字符串\',
  `isreceive` smallint(1) NOT NULL DEFAULT \'0\' COMMENT \'0未领取，1已领取，2已超时\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;';
    }


    public function CreateTable()
    {
        $key_admin=I('post.key');
        $admininfo=$this->getMerchant($key_admin);
        $tablename=$admininfo['pre_table'];
        $sql=str_replace('{tablename}',$tablename, $this->sql);
        $db=M();
        $re=$db->execute($sql);
        if (0 !== $re){
            returnjson(array('code'=>200, 'data'=>$sql), $this->returnstyle, $this->callback);//如果没有表，则直接返回没有结果
        }else{
            returnjson(array('code'=>104, 'data'=>$sql), $this->returnstyle, $this->callback);//如果没有表，则直接返回没有结果
        }

    }








    /**
     * 按商户密钥查询商户配置信息
     * @param $key_admin 商户密钥
     * @return bool
     */
    protected function getMerchant($key_admin) {
        if (!$key_admin) {
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $m_info = $this->redis->get('member:' . $key_admin);
        if ($m_info) {
            //writeOperationLog(array('get merchant' => $m_info), 'jaleel_logs');
            return json_decode($m_info, true);
        } else {
            $merchant = M('total_admin');
            $re = $merchant->where(array('ukey' => $key_admin))->find();

            if ($re) {
                //writeOperationLog(array('get merchant' => $re), 'jaleel_logs');
                $this->redis->set('member:' . $key_admin, json_encode($re),array('ex'=>86400));//一天
            }else {
                $data['code']=1001;
                echo returnjson($data,$this->returnstyle,$this->callback);exit();
            }

            return $re;
        }
    }

}
?>