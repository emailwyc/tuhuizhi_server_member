项目尽量用php 的 PSR语法，统一编码规范

上线前：php扩展mcrypt

数据库配置，包括redis

微信公众号授权给第三方开发平台地址：http://open.weixin.rtmap.com/Thirdwechat/Thirdwechat/Getauthorizer

查看redis 1数据库下的所有key和key值：http://mem.rtmap.com/Home/Public/getkeys?name=1

自动回复的redis值在后台设置

是否设置自动回复：$isauto_reply=$this->redis->get('wechat:.'.$get_data['appid'].':isauto_reply');

自动回复列表：$auto_reply=$this->redis->get('wechat:'.$get_data['appid'].':auto_reply:list'); 
