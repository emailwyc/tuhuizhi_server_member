<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/3/8
 * Time: 11:00
 */

namespace MerAdmin\Controller;


use Thirdwechat\Controller\Wechat\TemplateController;

class WechatMessageController extends AuthController
{


    /**
     * @desc 客服消息部分
     */
    public function MessageList()
    {
        $key_admin=I('key_admin');
        $page=I('page');
        $lines=I('lines');
        $page= false != $page ? $page : 1;
        $lines= false != $lines ? $lines : 1;
        $start=($page-1)*$lines;
        $admininfo=$this->getMerchant($key_admin);
        $this->CheckTable($admininfo);
        $db=M('event_message', $admininfo['pre_table']);
        $sel= $db->limit($start, $lines)->select();
        if ($sel){
            $count=$db->count();
            $pagenum=ceil($count/$lines);
            $data=array('data'=>$sel,'page'=>$page, 'count'=>$count, 'count_page'=>$pagenum);
            $msg=array('code'=>200,'data'=>$data);
            returnjson($msg, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 添加消息
     * 事件类型暂时只接受subscribe,LOCATION，自定义事件后期添加
     * 注：不管什么类型的事件，只能有text或news一种类型
     */
    public function CreateMessage()
    {
        $params['title']=I('title');
        $params['url']=I('url');
        $params['picurl']=I('picurl');
        $params['sort']=I('sort');
        $params['message_event_type']=I('message_event_type');//什么事件，关注、进入等，subscribe,LOCATION
        $params['isopen']=I('isopen');//0或1
        $params['bigimg']=I('bigimg');//0或1
        $params['message_type']='news';//I('message_type');//暂时维护text和图文消息
        $params['key_admin']=I('key_admin');
        //根据传入的条件判断参数是否为空
//        if ('news' == $params['message_type']){//如果消息是图文消息，则除简介外，都要填
            if (in_array('', $params)){
                returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
            }
//        }
        $params['description']=I('description');//可空
//        if ('text' == $params['message_type']){//如果要设置文本消息，则简介不能为空
//            if ('' == $params['description']){
//                returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
//            }
//        }
//        //判断传入的参数是否符合条件
//        if ($params['message_type'] != 'text' && $params['message_type'] != 'news'){
//            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
//        }
        if ($params['message_event_type'] != 'subscribe' && $params['message_event_type'] != 'location' ){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }


        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('event_message', $admininfo['pre_table']);
        unset($params['key_admin']);
        $add=$db->add($params);
        if ($add){
            //设置redis
            $this->SetMessage($params['message_event_type'], $admininfo, $params['message_type']);
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     *
     */
    public function GetOneMessage()
    {
        $params['id']=I('id');
        $params['key_admin']=I('key_admin');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('event_message', $admininfo['pre_table']);
        $find=$db->where(array('id'=>$params['id']))->find();
        if ($find){
            returnjson(array('code'=>200, 'data'=>$find), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 修改某个消息
     */
    public function EditMessage()
    {
        $params['title']=I('title');
        $params['url']=I('url');
        $params['picurl']=I('picurl');
        $params['sort']=I('sort');
        $params['message_event_type']=I('message_event_type');//什么事件，关注、进入等，subscribe,LOCATION
        $params['isopen']=I('isopen');//0或1
        $params['bigimg']=I('bigimg');//0或1
        $params['message_type']='news';//I('message_type');//暂时维护text和图文消息
        $params['key_admin']=I('key_admin');
        $params['id']=I('id');
        //根据传入的条件判断参数是否为空
//        if ('news' == $params['message_type']){//如果消息是图文消息，则除简介外，都要填
            if (in_array('', $params)){
                returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
            }
//        }
        $params['description']=I('description');//可空
//        if ('text' == $params['message_type']){//如果要设置文本消息，则简介不能为空
//            if ('' == $params['description']){
//                returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
//            }
//        }
//        //判断传入的参数是否符合条件
//        if ($params['message_type'] != 'text' && $params['message_type'] != 'news' ){
//            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
//        }
        if ($params['message_event_type'] != 'subscribe' && $params['message_event_type'] != 'location' ){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }


        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('event_message', $admininfo['pre_table']);
        unset($params['key_admin']);
        $find=$db->where(array('id'=>$params['id']))->find();
        if (false != $find){
            $save=$db->save($params);
            if (false !== $save){
                //设置redis
                $this->SetMessage($params['message_event_type'], $admininfo, $params['message_type']);
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 删除某条消息
     */
    public function DelMessage()
    {
        $params['key_admin']=I('key_admin');
        $params['id']=I('id');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('event_message', $admininfo['pre_table']);
        $find=$db->where(array('id'=>$params['id']))->find();
        if (false != $find){
            $del=$db->where(array('id'=>$params['id']))->delete();
            if (false !== $del){
                $this->SetMessage($find['message_event_type'], $admininfo, $find['message_type']);
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }

        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 从数据库中读取微信消息放入redis缓存
     * $message_event_type : subscribe、LOCATION
     * $admininfo : key_admin对应的商户数据
     * $message_type : 传入的消息类型 news、text
     */
    private function SetMessage($message_event_type, $admininfo, $message_type)
    {
        /**
         * 重置本次事件类型的redis
         */
        $message_event_type=strtolower($message_event_type);
        $db=M('event_message', $admininfo['pre_table']);
        //根据本次传入的条件查询对应语句
//        if ($message_type == 'news') {
            $where = array('isopen' => 1);//, 'message_type' => 'news'
//        }
//        if ($message_type == 'text') {
//            $where = array('isopen' => 1, 'message_type' => 'text');
//        }
        $where['message_event_type']=$message_event_type;
        $where['bigimg']=1;
        $find1 = $db->field('title,description,url,picurl')->where($where)->order(' `sort` asc, `id` desc')->limit(8)->select();
        $where['bigimg']=0;
        $find2 = $db->field('title,description,url,picurl')->where($where)->order(' `sort` asc, `id` desc')->limit(8)->select();


        if ($find1 == false && $find2 == false){//如果按本次传入的条件查询不到结果，则查询另一个条件
//            if ($message_type == 'news') {
                $where = array('isopen' => 1);//'message_type' => 'text'
//                $message_type='text';
//            }
//            if ($message_type == 'text') {
//                $where = array('isopen' => 1, 'message_type' => 'news');
//                $message_type='news';
//            }
            $where['message_event_type']=$message_event_type;
            $where['bigimg']=1;
            $find1 = $db->field('title,description,url,picurl')->where($where)->order(' `sort` asc, `id` desc')->limit(8)->select();
            $where['bigimg']=0;
            $find2 = $db->field('title,description,url,picurl')->where($where)->order(' `sort` asc, `id` desc')->limit(8)->select();
        }
        $find=array_merge($find1, $find2);
        //如果两次查询的结果都不是空
        if ($find != null){
            $is_subscribe_message = $this->redis->set('wechat:'.$message_event_type.':ismessage:' . $admininfo['wechat_appid'], 'yes');//设置关注自动回复开启
            $messagtype = $this->redis->set('wechat:'.$message_event_type.':message:type:' . $admininfo['wechat_appid'], $message_type);//设置回复消息方式
            //按条件生成json
            if ($message_type == 'news') {
                $array = array(
                    'touser' => '',
                    'msgtype' => 'news',
                    'news' => array(
                        'articles' => $find
                    )
                );
            }
            if ($message_type == 'text') {
                $array = array('touser' => '', 'msgtype' => 'text', 'text' => array('content' => $find[0]['description']));
            }
            $this->redis->set('wechat:'.$message_event_type.':message:type:content:' . $admininfo['wechat_appid'], json_encode($array));//把所有数据存入redis
        }else{
            //如果查询数据库的结果为空，则删除之前的所有redis
            $is_subscribe_message = $this->redis->del('wechat:'.$message_event_type.':ismessage:' . $admininfo['wechat_appid']);
            $messagtype = $this->redis->del('wechat:'.$message_event_type.':message:type:' . $admininfo['wechat_appid']);
            $this->redis->del('wechat:'.$message_event_type.':message:type:content:' . $admininfo['wechat_appid']);
        }





    }

    private function CheckTable($admin)
    {
        $db=M();
        $check=$db->execute('SHOW TABLES like "'.$admin['pre_table'].'event_message"');
        if (1 !==$check) {//没有，则自动创建表
            $sql="CREATE TABLE `".$admin['pre_table']."event_message` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' COMMENT '标题',
  `description` text COMMENT '简介',
  `url` varchar(255) DEFAULT NULL COMMENT 'URL',
  `picurl` varchar(255) DEFAULT NULL COMMENT '图片URL',
  `sort` smallint(2) NOT NULL DEFAULT '1' COMMENT '消息排序',
  `message_event_type` varchar(20) NOT NULL DEFAULT '' COMMENT '发送消息事件，关注、进入时等发送',
  `isopen` smallint(2) DEFAULT '1' COMMENT '是否开启',
  `bigimg` smallint(2) NOT NULL DEFAULT '1' COMMENT '是否大图',
  `message_type` varchar(20) DEFAULT 'news' COMMENT '消息类型：news,text,template等等',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;";
            $return = $db->execute($sql);
            if (0 !== $return){
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);//如果没有表，则直接返回没有结果
            }
        }else{
            return true;
        }
    }






    /**
     * @desc 模板消息部分
     */


    /**
     * 模板消息列表
     */
    public function TemplateList()
    {
        $key_admin=I('key_admin');
        $page=I('page');
        $lines=I('lines');
        $page= false != $page ? $page : 1;
        $lines= false != $lines ? $lines : 10;
        $start=($page-1)*$lines;
        if ('' == $key_admin){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }

        $admininfo=$this->getMerchant($key_admin);
        $this->CheckTemplateTable($admininfo);
        $db=M('template_message_send', $admininfo['pre_table']);
        $sel= $db->field('id,title,templateid')->limit($start, $lines)->select();
        if ($sel){
            $count=$db->count();
            $pagenum=ceil($count/$lines);
            returnjson(array('code'=>200, 'data'=>array('page'=>$page, 'page_count'=>$pagenum, 'count'=>$count, 'data'=>$sel)), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }






    /**
     * @deprecated 按输入的模板id查询消息类型
     */
    public function SearchTemplateId()
    {
        $params['key_admin']=I('key_admin');
        $params['templateid']=I('templateid');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $templatekeys=$this->GetTemplate($params['key_admin'], $params['templateid']);
//        array_pop($templatekeys);//删除最后一个标题
        if ($templatekeys !== false){
            $msg=array('code'=>200, 'data'=>$templatekeys);
        }else{
            $msg=array('code'=>104);
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }


    /**
     * 创建模板消息
     */
    public function CreateTemplate()
    {
        $patams=I();
        if ('' == $patams['templateid'] || '' == $patams['openid'] || '' == $patams['key_admin'] ){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $db=M('template_message_send', $admininfo['pre_table']);
//        $find=$db->where(array('templateid'=>$patams['templateid']))->find();
//        if ($find){
//            returnjson(array('code'=>1008), $this->returnstyle, $this->callback);
//        }
        $save=$this->TemplateDbArray($patams['templateid'], $patams);

        $save=$db->add($save);
        if ($save){
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 获取某个模板消息的相信信息
     */
    public function ShowTemplateInfo()
    {
        $params['key_admin']=I('key_admin');
        $params['id']=I('id');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $db=M('template_message_send', $admininfo['pre_table']);
        $find=$db->field('params,id,openid')->where(array('id'=>$params['id']))->find();
        if ($find){
            $find['params']=json_decode($find['params'], true);
            $find['params']['openid']=$find['openid'];
            unset($find['openid']);
            returnjson(array('code'=>200, 'data'=>$find), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 修改模板消息
     */
    public function EditTemplate()
    {
        $patams=I();
        if ('' == $patams['templateid'] || '' == $patams['openid'] || '' == $patams['key_admin'] || '' == $patams['id']){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $db=M('template_message_send', $admininfo['pre_table']);
        $find=$db->where(array('id'=>$patams['id'],'templateid'=>$patams['templateid']))->find();
        if (!$find){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        $save=$this->TemplateDbArray($patams['templateid'], $patams);

        $save=$db->where(array('id'=>$patams['id']))->save($save);
        if (false !== $save){
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 删除某条模板消息
     */
    public function DelTemplate()
    {
        $params['key_admin']=I('key_admin');
        $params['id']=I('id');
        $params['templateid']=I('templateid');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('template_message_send', $admininfo['pre_table']);
        $find=$db->where(array('id'=>$params['id'], 'templateid'=>$params['templateid']))->find();
        if (!$find){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }else{
            $del=$db->where(array('id'=>$params['id'], 'templateid'=>$params['templateid']))->delete();
            if (false !== $del){
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
        }
    }


    /**
     * 发送模板消息
     */
    public function SendTemplateMessage()
    {
        $params['key_admin']=I('key_admin');
        $params['id']=I('id');
        $params['templateid']=I('templateid');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }

        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('template_message_send', $admininfo['pre_table']);
        $find=$db->field('openid,message_json')->where(array('id'=>$params['id'], 'templateid'=>$params['templateid']))->find();
        if (!$find){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        $message=json_decode($find['message_json'], true);
        $openid=explode(',', $find['openid']);
        foreach ($openid as $item => $value) {
            $message['touser']=$value;
            $array[]=$message;
        }

        $url='https://mem.rtmap.com/Thirdwechat/Wechat/Template/outsideSendMessage';
        $sign=sign(array('sign_key'=>$admininfo['signkey'],'key_admin'=>$params['key_admin']));
        $url=$url.'?key_admin='.$params['key_admin'].'&sign='.$sign;
        $return=curl_https($url,json_encode($array), array(), 30, true);//发送模板消息
        $re=json_decode($return, true);
        $msg['code']=$re['code'];
        $msg['data']=$re['data'];
        returnjson($msg, $this->returnstyle, $this->callback);
    }


    private function CheckTemplateTable($admin)
    {
        $db=M();
        $check=$db->execute('SHOW TABLES like "'.$admin['pre_table'].'template_message_send"');
        if (1 !==$check) {//没有，则自动创建表
            $sql="CREATE TABLE `".$admin['pre_table']."template_message_send` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题，模板的标题',
  `templateid` varchar(100) NOT NULL DEFAULT '' COMMENT '模板id',
  `params` text COMMENT '参数，保存时用json',
  `openid` text COMMENT '要发送的openid',
  `message_json` text COMMENT '发送的模板json',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
            $return = $db->execute($sql);
            if (0 !== $return){
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);//如果没有表，则直接返回没有结果
            }
        }else{
            return true;
        }
    }

    /**
     * 创建和修改的时候，返回添加数据库所需的数组
     * @param $templateid
     * @param $patams
     * @return mixed
     */
    private function TemplateDbArray($templateid, $patams)
    {

        $templatekeys=$this->GetTemplate($this->ukey, $templateid);

        $title=$templatekeys['template']['title'];
        $pakey=array_keys($patams);
        foreach ($templatekeys['keys'] as $k => $v){//判断模板的key是否存在在传入的key中
            if (!in_array($v, $pakey)){
                returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
            }
        }

        $array=array(
            'touser'=>'',
            'template_id'=>$patams['templateid'],
            'url'=>$patams['url']
        );

        foreach ($templatekeys['keys'] as $key => $val){
            $data[$val]=array('value'=>$patams[$val],'color'=>'#173177');
            $params['keys'][$val]=$patams[$val];
        }
        $array['data']=$data;//要发送的模板数组
        $save['message_json']=json_encode($array);
        $save['openid']=$patams['openid'];
        $params['templateid']=$patams['templateid'];
        $params['openid']=$patams['openid'];
        $save['params']=json_encode($params);
        $save['templateid']=$patams['templateid'];
        $save['title']=$title;
        return $save;
    }





    /**
     * 获取微信模板id的模板内容
     * @param $key_admin
     * @param $templateid
     * @return array|bool
     */
    private function GetTemplate($key_admin, $templateid)
    {
                $admininfo=$this->getMerchant($this->ukey);
        $wechat= new TemplateController();
        $templates=$wechat->InsideGetAlltemplate($admininfo['wechat_appid']);
        //$json='{"template_list":[{"template_id":"Gy3YdfbQ6oT11Akl8bNnpVoq_KEmKeuWH2XzMeqg40I","title":"\u8ba2\u5355\u652f\u4ed8\u6210\u529f","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\n\u652f\u4ed8\u91d1\u989d\uff1a{{orderMoneySum.DATA}}\n\u5546\u54c1\u4fe1\u606f\uff1a{{orderProductName.DATA}}\n{{Remark.DATA}}","example":"\u6211\u4eec\u5df2\u6536\u5230\u60a8\u7684\u8d27\u6b3e\uff0c\u5f00\u59cb\u4e3a\u60a8\u6253\u5305\u5546\u54c1\uff0c\u8bf7\u8010\u5fc3\u7b49\u5f85: )\n\u652f\u4ed8\u91d1\u989d\uff1a30.00\u5143\n\u5546\u54c1\u4fe1\u606f\uff1a\u6211\u662f\u5546\u54c1\u540d\u5b57\n\n\u5982\u6709\u95ee\u9898\u8bf7\u81f4\u7535400-828-1878\u6216\u76f4\u63a5\u5728\u5fae\u4fe1\u7559\u8a00\uff0c\u5c0f\u6613\u5c06\u7b2c\u4e00\u65f6\u95f4\u4e3a\u60a8\u670d\u52a1\uff01"},{"template_id":"ms5fPUNXB0_Xpe-271_STWDABdgiNKLh51TXEqkhEWk","title":"\u8d2d\u4e70\u6210\u529f\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u7535\u5b50\u6280\u672f","content":"\u60a8\u597d\uff0c\u60a8\u5df2\u8d2d\u4e70\u6210\u529f\u3002\n\n{{productType.DATA}}\uff1a{{name.DATA}}\n\u8d2d\u4e70\u6570\u91cf\uff1a{{number.DATA}}\n{{remark.DATA}}","example":"\u60a8\u597d\uff0c\u60a8\u5df2\u8d2d\u4e70\u6210\u529f\u3002\n\n\u5546\u54c1\u540d\uff1a\u53d1\u5149\u4e8c\u6781\u7ba1\n\u6570\u91cf\uff1a1000\u4efd\n\u6211\u4eec\u5c06\u5c3d\u5feb\u5b89\u6392\u53d1\u8d27\uff0c\u5982\u6709\u7591\u95ee\uff0c\u8bf7\u81f4\u753513912345678\u8054\u7cfb\u6211\u4eec\u3002"},{"template_id":"ZTSI0sLhy64iB6UpeLU44BWEE8SA4ryX3txBIOiY310","title":"\u8d2d\u4e70\u6210\u529f\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u5546\u54c1\u540d\u79f0\uff1a{{keyword1.DATA}}\n\u5546\u54c1\u4ef7\u683c\uff1a{{keyword2.DATA}}\n\u8d2d\u4e70\u65f6\u95f4\uff1a{{keyword3.DATA}}\n\u5f00\u5956\u65f6\u95f4\uff1a{{keyword4.DATA}}\n{{remark.DATA}}","example":"\u60a8\u597d\uff0c\u60a8\u5df2\u8d2d\u4e70\u6210\u529f\uff01\r\n\u5546\u54c1\u540d\u79f0:name\r\n\u5546\u54c1\u4ef7\u683c:price\r\n\u8d2d\u4e70\u65f6\u95f4:ktime\r\n\u5f00\u5956\u65f6\u95f4:etime\r\n\u611f\u8c22\u60a8\u7684\u4f7f\u7528\uff0c\u795d\u60a8\u751f\u6d3b\u6109\u5feb"},{"template_id":"ShhFFImpo7e8XBeNIDNxu9ROWiJk9HKkuf1HvoI7uA8","title":"\u8d2d\u4e70\u6210\u529f\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u5546\u54c1\u540d\u79f0\uff1a{{keyword1.DATA}}\n\u6d88\u8d39\u91d1\u989d\uff1a{{keyword2.DATA}}\n\u8d2d\u4e70\u65f6\u95f4\uff1a{{keyword3.DATA}}\n{{remark.DATA}}","example":"\u606d\u559c\u4f60\u8d2d\u4e70\u6210\u529f\uff01\n\u5546\u54c1\u540d\u79f0\uff1a\u5de7\u514b\u529b\n\u6d88\u8d39\u91d1\u989d\uff1a39.8\u5143\n\u8d2d\u4e70\u65f6\u95f4\uff1a2014\u5e749\u670816\u65e5\n\u6b22\u8fce\u518d\u6b21\u8d2d\u4e70\uff01"},{"template_id":"4WwDUKTShqYq-3T_I6ebLoKl2gLdSzRv8WgdEMIHBk0","title":"\u62a5\u8b66\u7cfb\u7edf\u7ef4\u4fee\u670d\u52a1\u63d0\u9192","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u7528\u6237\u540d\u79f0\uff1a{{keyword1.DATA}}\n\u670d\u52a1\u5546\uff1a{{keyword2.DATA}}\n\u670d\u52a1\u65f6\u95f4\uff1a{{keyword3.DATA}}\n{{remark.DATA}}","example":"\u60a8\u7684\u62a5\u8b66\u7cfb\u7edf2015 \u5e7409 \u670821 \u65e5\u7684\u7ef4\u4fee\u670d\u52a1\u5df2\u5b8c\u6210\uff1a\r\n\u7528\u6237\u540d\u79f0\uff1a\u559c\u5fb7\u9686\r\n\u670d\u52a1\u5546\uff1a\u6cb3\u5357\u5927\u534e\u5b89\u9632\u79d1\u6280\u80a1\u4efd\u6709\u9650\u516c\u53f8\r\n\u670d\u52a1\u65f6\u95f4\uff1a2015-09-21  11:31:30\r\n\u70b9\u51fb\u67e5\u770b\u8be6\u7ec6\uff0c\u5982\u6709\u7591\u95ee\uff0c\u8bf7\u81f4\u7535\u5168\u56fd24\u5c0f\u65f6\u670d\u52a1\u7535\u8bdd\uff1a400-619-7777"},{"template_id":"490uAuDy-OguIyggq0O3q7Mhvt1v1tF8wpUHsCbJ6H4","title":"\u8ba2\u5355\u652f\u4ed8\u6210\u529f","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\n\u652f\u4ed8\u91d1\u989d\uff1a{{orderMoneySum.DATA}}\n\u5546\u54c1\u4fe1\u606f\uff1a{{orderProductName.DATA}}\n{{Remark.DATA}}","example":"\u6211\u4eec\u5df2\u6536\u5230\u60a8\u7684\u8d27\u6b3e\uff0c\u5f00\u59cb\u4e3a\u60a8\u6253\u5305\u5546\u54c1\uff0c\u8bf7\u8010\u5fc3\u7b49\u5f85: )\n\u652f\u4ed8\u91d1\u989d\uff1a30.00\u5143\n\u5546\u54c1\u4fe1\u606f\uff1a\u6211\u662f\u5546\u54c1\u540d\u5b57\n\n\u5982\u6709\u95ee\u9898\u8bf7\u81f4\u7535400-828-1878\u6216\u76f4\u63a5\u5728\u5fae\u4fe1\u7559\u8a00\uff0c\u5c0f\u6613\u5c06\u7b2c\u4e00\u65f6\u95f4\u4e3a\u60a8\u670d\u52a1\uff01"},{"template_id":"oYCxx8_I129Sb3oDO_k5Uaj5O08BQ_RY3Pii_3wOjdg","title":"\u4f1a\u5458\u6ce8\u518c\u6210\u529f\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u624b\u673a\u53f7\uff1a{{keyword1.DATA}}\n\u4f1a\u5458\u7f16\u53f7\uff1a{{keyword2.DATA}}\n\u6ce8\u518c\u65f6\u95f4\uff1a{{keyword3.DATA}}\n{{remark.DATA}}","example":"\u4f1a\u5458\u6ce8\u518c\u6210\u529f\uff01\u6ce8\u518c\u4fe1\u606f\u5982\u4e0b\uff1a\r\n\u624b\u673a\u53f7\uff1a13800138000\r\n\u4f1a\u5458\u7f16\u53f7\uff1a10008\r\n\u6ce8\u518c\u65f6\u95f4\uff1a2014\u5e747\u670821\u65e5 18:36\r\n\u8c22\u8c22\u60a8\u7684\u652f\u6301\uff01\u8bf7\u4f7f\u7528\u624b\u673a\u53f7\u5728APP\u767b\u5f55\uff01"},{"template_id":"ILONd6FweWaRb2MYcSF5TG0vWJGp7875499jCIxyhLk","title":"\u79ef\u5206\u6d88\u8d39\u63d0\u9192","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u670d\u52a1\u5185\u5bb9\uff1a{{keyword1.DATA}}\n\u79ef\u5206\u53d8\u5316\uff1a{{keyword2.DATA}}\n\u5546\u6237\u540d\u79f0\uff1a{{keyword3.DATA}}\n\u65e5\u671f\u65f6\u95f4\uff1a{{keyword4.DATA}}\n{{remark.DATA}}","example":"\u5df2\u6210\u529f\u5151\u6362\u7cbe\u7ec6\u6d17\u8f66\u670d\u52a1\r\n\u670d\u52a1\u5185\u5bb9\uff1a\u7cbe\u7ec6\u6d17\u8f66\r\n\u79ef\u5206\u53d8\u5316\uff1a-100\r\n\u5546\u6237\u540d\u79f0\uff1a\u84dd\u4eac\u9c7c\u6d17\u8f66\u884c\r\n\u65e5\u671f\u65f6\u95f4\uff1a2015-09-14\r\n\u611f\u8c22\u60a8\u7684\u4f7f\u7528\u3002"},{"template_id":"1JCOl9WFAXXOkQBYydn2OaOlc2mvd6fLlIXQ0ir_8rM","title":"\u79ef\u5206\u83b7\u5f97\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u5546\u54c1\u540d\u79f0\uff1a{{keyword1.DATA}}\n\u652f\u4ed8\u91d1\u989d\uff1a{{keyword2.DATA}}\n\u83b7\u5f97\u79ef\u5206\uff1a{{keyword3.DATA}}\n\u7d2f\u79ef\u79ef\u5206\uff1a{{keyword4.DATA}}\n\u53d1\u751f\u65f6\u95f4\uff1a{{keyword5.DATA}}\n{{remark.DATA}}","example":"\u60a8\u597d\uff0c\u60a8\u7684\u8ba2\u5355\u5df2\u786e\u8ba4\u6536\u8d27\uff01\r\n\u5546\u54c1\u540d\u79f0\uff1aXXX\u723d\u80a4\u6c34\u4e73\u6db22\u4ef6\u5957\r\n\u652f\u4ed8\u91d1\u989d\uff1a1000\u5143\r\n\u83b7\u5f97\u79ef\u5206\uff1a1000\u5206\r\n\u7d2f\u79ef\u79ef\u5206\uff1a3456\u5206\r\n\u53d1\u751f\u65f6\u95f4\uff1a2015-08-20 02:00:00\r\n\u611f\u8c22\u60a8\u7684\u53c2\u4e0e\uff01"},{"template_id":"uPoZ9DkzX_9hMhTxloxwq389ioU2vxgp9DZ7HiRz3BU","title":"\u4f1a\u5458\u5361\u5347\u7ea7\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\n\u4f1a\u5458\u5361\u53f7\uff1a{{keynote1.DATA}}\n\u6709\u6548\u671f\uff1a{{keynote2.DATA}}\n{{remark.DATA}}","example":"\u606d\u559c\u4f60\uff0c\u4f60\u7684\u4f1a\u5458\u5361\u53ef\u4ee5\u5347\u7ea7\u4e3a\u5fae\u4fe1\u4f1a\u5458\u5361\uff0c\u4f7f\u7528\u4f1a\u5458\u5361\u66f4\u52a0\u65b9\u4fbf\uff0c\u67e5\u8be2 \u79ef\u5206\u66f4\u52a0\u5feb\u6377\n\n\u4f1a\u5458\u5361\u53f7\uff1a\u5149\u660e\u9876\u5de5\u4f5c\u574a \n\u6709\u6548\u671f\uff1a222 \n\u70b9\u51fb\u8be6\u60c5\uff0c\u7acb\u523b\u5347\u7ea7\u4f1a\u5458\u5361\u3002"},{"template_id":"PxUHhhVGCI8kDzp2pCLdi98ywd-1a9O2x7t2QOC2N9Y","title":"\u79ef\u5206\u53d8\u52a8\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\n{{FieldName.DATA}}:{{Account.DATA}}\n{{change.DATA}}\u79ef\u5206:{{CreditChange.DATA}}\n\u79ef\u5206\u4f59\u989d:{{CreditTotal.DATA}}\n{{Remark.DATA}}","example":"\u60a8\u7684\u79ef\u5206\u8d26\u6237\u53d8\u66f4\u5982\u4e0b\n\n\u589e\u52a0\u79ef\u5206\uff1a34\n\u79ef\u5206\u4f59\u989d\uff1a5623\n\n\u60a8\u53ef\u4ee5\u5728\u7f51\u7ad9\u6216\u624b\u673aAPP\u4f7f\u7528\u79ef\u5206\u4e0b\u5355\u62b5\u73b0\uff0c10\u79ef\u5206=1\u5143\u3002"},{"template_id":"hddd-TtXF4ATc08Mi-gYa1DGRh8b17e4lBUqy0fyymg","title":"\u4f1a\u5458\u6ce8\u518c\u6210\u529f\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u624b\u673a\u53f7\uff1a{{keyword1.DATA}}\n\u4f1a\u5458\u7f16\u53f7\uff1a{{keyword2.DATA}}\n\u6ce8\u518c\u65f6\u95f4\uff1a{{keyword3.DATA}}\n{{remark.DATA}}","example":"\u4f1a\u5458\u6ce8\u518c\u6210\u529f\uff01\u6ce8\u518c\u4fe1\u606f\u5982\u4e0b\uff1a\r\n\u624b\u673a\u53f7\uff1a13800138000\r\n\u4f1a\u5458\u7f16\u53f7\uff1a10008\r\n\u6ce8\u518c\u65f6\u95f4\uff1a2014\u5e747\u670821\u65e5 18:36\r\n\u8c22\u8c22\u60a8\u7684\u652f\u6301\uff01\u8bf7\u4f7f\u7528\u624b\u673a\u53f7\u5728APP\u767b\u5f55\uff01"},{"template_id":"ga8LS7QmbQWGDzlVEQrGHixUvwvs6P_miXd3inzbY9E","title":"\u5173\u6ce8\u6210\u529f\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u4f1a\u5458\u6635\u79f0\uff1a{{keyword1.DATA}}\n\u5173\u6ce8\u65f6\u95f4\uff1a{{keyword2.DATA}}\n{{remark.DATA}}","example":"\u606d\u559c\u60a8\u901a\u8fc7\u5206\u4eab\u94fe\u63a5\u6210\u529f\u9501\u5b9a\u4e00\u4f4d\u4f1a\u5458\uff01\r\n\u4f1a\u5458\u6635\u79f0\uff1aadmin\r\n\u5173\u6ce8\u65f6\u95f4\uff1a2016-06-23 16:15:33\r\n\u8bb0\u5f97\u63d0\u9192\u4ed6\u5173\u6ce8\u5e73\u53f0\u3002"},{"template_id":"AaD_w1wQ1GZm9FkylTfXIdk58F4BhuH-5cxHfv-AGCY","title":"\u6210\u4e3a\u4f1a\u5458\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\n\u4f1a\u5458\u53f7\uff1a{{cardNumber.DATA}}\n{{type.DATA}}\u5730\u5740\uff1a{{address.DATA}}\n\u767b\u8bb0\u59d3\u540d\uff1a{{VIPName.DATA}}\n\u767b\u8bb0\u624b\u673a\u53f7\uff1a{{VIPPhone.DATA}}\n\u6709\u6548\u671f\uff1a{{expDate.DATA}}\n{{remark.DATA}}","example":"\u60a8\u597d\uff0c\u60a8\u5df2\u6210\u4e3a\u5fae\u4fe1\u67d0\u67d0\u5e97\u4f1a\u5458\u3002\n\n\u4f1a\u5458\u53f7\uff1a87457\n\u5546\u6237\u5730\u5740\uff1a\u5fae\u4fe1\u67d0\u67d0\u5e97\u30109\u5e97\u901a\u7528\u3011\n\u767b\u8bb0\u59d3\u540d\uff1a\u90b9\u67d0\u67d0\n\u767b\u8bb0\u624b\u673a\u53f7\uff1a13912345678\n\u6709\u6548\u671f\uff1a2014\u5e749\u670830\u65e5\n\u5907\u6ce8\uff1a\u5982\u6709\u7591\u95ee\uff0c\u8bf7\u54a8\u8be213912345678\u3002"},{"template_id":"ZCU4Pjxd2-V04BGv2Lx7CC6W1bqC7v1eOAucQuTaOxg","title":"\u5f00\u5956\u7ed3\u679c\u901a\u77e5","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u6d3b\u52a8\u5956\u54c1\uff1a{{keyword1.DATA}}\n\u5f00\u5956\u65f6\u95f4\uff1a{{keyword2.DATA}}\n{{remark.DATA}}","example":"\u5c0a\u656c\u7684\u4f1a\u5458\uff0c\u60a8\u53c2\u4e0e\u7684\u62bd\u5956\u6d3b\u52a8\u5373\u5c06\u51fa\u7ed3\u679c\r\n\u6d3b\u52a8\u5956\u54c1\uff1a\u5143\u7ebf\u8def\u7531\u5668\r\n\u5f00\u5956\u65f6\u95f4\uff1a2015\u5e747\u670821\u65e5 18:36\r\n\u8be6\u60c5\u8bf7\u767b\u9646\u5b98\u7f51\u67e5\u770b\u5f00\u5956\u7ed3\u679c"},{"template_id":"SRrBjOT5UUPwcfpyP95gD69X-89hmiPdSMZ9IXaT-Nw","title":"\u62a5\u8b66\u4fe1\u606f\u63d0\u9192","primary_industry":"IT\u79d1\u6280","deputy_industry":"\u4e92\u8054\u7f51|\u7535\u5b50\u5546\u52a1","content":"{{first.DATA}}\n\u62a5\u8b66\u65f6\u95f4\uff1a{{keyword1.DATA}}\n\u62a5\u8b66\u4f4d\u7f6e\uff1a{{keyword2.DATA}}\n\u62a5\u8b66\u5185\u5bb9\uff1a{{keyword3.DATA}}\n{{remark.DATA}}","example":"\u60a8\u597d\uff0c\u60a8\u6709\u4e00\u6761\u65b0\u7684\u62a5\u8b66\u4fe1\u606f\u3002\r\n\u62a5\u8b66\u65f6\u95f4\uff1a2014\u5e7411\u670810\u65e5 17:19\r\n\u62a5\u8b66\u4f4d\u7f6e\uff1a\u5ba2\u5385\u95e8\u78c1\r\n\u62a5\u8b66\u5185\u5bb9\uff1a\u5ba2\u5385\u95e8\u78c1\u53d1\u751f\u62a5\u8b66\u4fe1\u606f\u3002\r\n\u667a\u80fd\u5bb6\u5c45\uff0c\u5f00\u542f\u60a8\u7684\u65b0\u751f\u6d3b\u3002"}]}';
//        $templates=array('code'=>200, 'data'=>json_decode($json, true));
        if (104 == $templates['code']){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }

        //解析字符串，返回前端数组
        foreach ($templates['data']['template_list'] as $item => $value){
            if ($value['template_id'] == $templateid){
                $title=$value['title'];
                $example=$value['example'];
                $str=$value['content'];
                break;
            }
        }

        $temps=null;
        $a=explode('{{', $str);
        foreach ($a as $key => $val){
            if ('' != $val && false !== strpos($val, '}}')){
                $b=explode('}}', $val);
                $temps[]=$b[0];
            }
        }
        foreach ($temps as $keys => $value){
            $c=explode('.', $value);
            $templatekeys['keys'][]=$c[0];
        }

        if (count($templatekeys) > 0){
            $templatekeys['keys'][]='url';
            $templatekeys['template']['title']=$title;
            $templatekeys['template']['content']=$str;
            $templatekeys['template']['example']=$example;
            return $templatekeys;
        }else{
            return false;
        }
    }





































}