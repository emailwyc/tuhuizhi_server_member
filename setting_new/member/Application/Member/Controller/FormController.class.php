<?php
/**
 * Created by 张凯锋.
 * User: zhangkaifeng
 * Date: 2017/2/27
 * Time: 16:27
 * 一些话：
 * 如果你觉得难以维护，去找王鑫，他的回答会让你想拍死他，真的，相信我
 * 年会要等到四五月份才开，坑不坑啊，现在人心惶惶的
 */

namespace Member\Controller;


use Common\Controller\JaleelController;
use PublicApi\Service\CouponService;
class FormController extends JaleelController
{


    /**
     * 创建会员表单
     * 每一个商户的auto_form表
     *
     * total_form
     * total_form_default
     * xxx_auto_form
     * 完成日期：2017-3-1
     */
    public function CreateMemberForm()
    {
        $key_admin=$this->ukey;
        if ('' == $key_admin){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($key_admin);
        $db=M('auto_form',$admininfo['pre_table']);
        $join=' join `total_form` on `form_key_id` = `total_form`.`id`';
//        $sel=$db->join($join)->where(array('isenable'=>1))->select();
        $sel=$db->field('total_form.id,isrequired,placeholder,sub,value,minlength,maxlength,content,content_key,content_type,function_name,sort')->join($join)->where(array('isenable'=>1,'type'=>1))->order('sort asc')->select();

        $dbdefault=M('form_default','total_');
        $dbself = M('form_default', $admininfo['pre_table']);
        //循环处理每一个表单项
        foreach ($sel as $key => $value){
            $selself = null;
            if ('' == $value['value']){
                $selself = $dbself->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                $sel[$key]['value'] = $selself;
                //如果商户自己的表里面没有添加默认值，就从公共模块里面找默认值
                if ( !$selself ) {
                    if ('' != $value['function_name']){
                        $defaultreturn=$value['function_name']();
                        $sel[$key]['value']=$defaultreturn;
                    }else{
                        $seldefault=$dbdefault->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                        if ($seldefault) {
                            if ($value['content_type']=='radio' || $value['content_type']=='checkbox' || $value['content_type']=='select') {
                                $sel[$key]['value']=$seldefault;
                            }else{
                                $sel[$key]['value']=$seldefault[0]['default_content'];
                            }
                        }else {
                            $sel[$key]['value']='';
                        }
                    }
                }



            }
            unset($sel[$key]['function_name']);
        }
        if ($sel){
            $msg['code']=200;
            $msg['data']=$sel;
        }else{
            $msg['code']=104;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }


    /**
     * 创建会员C端自动表单提交
     */
    public function RegisterMember()
    {
        $params=I("param.");
        $key_admin=I('key_admin');
        $openid=I('openid');
        if ('' == $key_admin || '' == $openid) {
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }

        //验证手机验证码
        if (isset($params['validcade'])){
            /**
             * 此处可以添加如下对验证码的验证错误提示:
             * 验证码和手机号不一致
             * 验证码失效
             * 验证码错误
             */
            $server_code = $this->redis->get($params['mobile']);
            //白名单
            $phone=array(13522667528,18910124223,13521625139);
            if (!in_array($params['mobile'], $phone)){
                //验证验证码
                if ($params['validcade'] != $server_code) {
                    $data = array('code' => '1031', 'msg' => 'invalid check code');
                    returnjson($data, $this->returnstyle, $this->callback);
                }
            }
            // 验证码使用完则清除redis中的记录
            $this->redis->delete($params['mobile']);
        }



        $admininfo=$this->getMerchant($key_admin);
        $db=M('auto_form',$admininfo['pre_table']);
        $join=' join `total_form` on `form_key_id` = `total_form`.`id`';
        $sel=$db->join($join)->where(array('isenable'=>1,'type'=>1))->select();
//        $sel=$db->field('total_form.id,isrequired,placeholder,sub,value,minlength,maxlength,content,content_key,content_type,function_name')->join($join)->where(array('isenable'=>1))->select();
        $dbdefault=M('form_default','total_');
        $dbself = M('form_default', $admininfo['pre_table']);
        //循环处理每一个表单项
        foreach ($sel as $key => $value){
            $selself = null;
            if ('' == $value['value']){
                $selself = $dbself->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                $sel[$key]['value'] = $selself;
                if ( !$selself ){
                    if ('' != $value['function_name']){
                        $defaultreturn=$value['function_name']();
                        $sel[$key]['value']=$defaultreturn;
                    }else{
                        $seldefault=$dbdefault->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                        if ($seldefault) {
                            if ($value['content_type']=='radio' || $value['content_type']=='checkbox' || $value['content_type']=='select') {
                                $sel[$key]['value']=$seldefault;
                            }else{
                                $sel[$key]['value']=$seldefault[0]['default_content'];
                            }
                        }else {
                            $sel[$key]['value']='';
                        }
                    }
                }

            }
        }

        //根据B端后台设置，验证传递的参数完整性
        foreach ($sel as $item => $value) {
            if ($value['isrequired'] == 1){
                if ( array_key_exists($value['content_key'],$params) ){
                    if ('' == $params[$value['content_key']]){
                        returnjson(array('code'=>1030,'data'=>$value['content_key'].' value is null'), $this->returnstyle, $this->callback);
                        break;
                    }
                }else{
                    returnjson(array('code'=>1030,'data'=>$value['content_key']." is'ent setting"), $this->returnstyle, $this->callback);
                    break;
                }
            }
        }



        $user=M('mem', $admininfo['pre_table']);
        // 验证此微信是否注册过
        $check_re = $this->checkUserExists($admininfo['pre_table'], $this->user_openid);
        if (is_array($check_re)) {
            $up_re = $user->where(array('mobile' => $check_re['mobile']))->save(array('openid' => ''));
            if ($up_re === false) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
        /**
         * 这里调用会员接口传递参数
         */
        $params['sign_key']=$admininfo['signkey'];
        if (isset($params['callback'])){//删除jaonp的回调函数
            unset($params['callback']);
        }
        $params['sign']=sign($params);
        unset($params['sign_key']);
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/createMember';
        $uinfo=http($url, $params, 'POST');
        $uinfo=json_decode($uinfo, true);
        // 创建失败
        if ($uinfo['code'] != 200) {
            if ($uinfo['code'] == 1012 || $uinfo['code'] == '2001') {
                $data = array('code' => '2001', 'msg' => 'u are already our member!');
                returnjson($data, $this->returnstyle, $this->callback);
            } else {
                $msg = $uinfo['code'] == 1018 ? $uinfo['msg'] : 'register member failed!';
                $data = array('code' => $uinfo['code'] , 'data'=>'app error', 'msg' => $msg);
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        //赠送Ycoin埋点 start
        $ycoin = I('ycoin');
        if($ycoin && isset($ycoin['openid']) && isset($ycoin['nickname']) && isset($ycoin['headimg'])){
            //注册
            $ycoin['key_admin'] = $admininfo['ukey'];
            $subParams = $ycoin;$subParams['event'] = 'register';
            $subParams['sign'] = $this->getSign($subParams,$admininfo);
            $url = C('DOMAIN')."/ClientApi/Inside/addYcoinMem";
            $result=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
            $result = json_decode($result,true);
            if($result['code']==200){
                //积分赠送
                $subParams = array('key_admin'=>$admininfo['ukey'],'openid'=>$ycoin['openid'],'title'=>'注册赠送','remarks'=>'注册系统赠送','mark'=>'register');
                $subParams['sign'] = $this->getSign($subParams,$admininfo);
                $url = C('DOMAIN')."/ClientApi/Inside/ycoinChangeLog";
                $result1=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
                $result1 = json_decode($result1,true);
            }
        }
        //赠送Ycoin埋点 end

        // 记录用户cookie
        $cookie = strtoupper(md5($this->user_openid . rand(1, 1000)));;
        setcookie($admininfo['pre_table'] . 'ck', '',time() - 1);
        cookie($admininfo['pre_table'] . 'ck', $cookie, array('expire' => time() + 365 * 24 * 3600, 'path' => '/', 'domain' => '.rtmap.com'));

        // 更新数据表
        $re = $user->where(array('cardno' => $uinfo['data']['cardno']))->save(array('openid' => $this->user_openid, 'cookie' => $cookie));
        if (!$re) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        $create_url=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'AttributeConfiguration');
        $welfare_data['config']=$create_url['function_name']?$create_url['function_name']:'no'; //属性
        $find_status=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'CreateIsWelfare');
        if($find_status['function_name'] == 2){
            if($create_url['function_name'] != '' && $create_url['function_name'] != 'no'){
            
                $welfare=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, $create_url['function_name']);
            
                if($create_url['function_name'] == 'createsuccessurl'){
                    //跳转地址
                    $jump_url=$welfare['function_name']?$welfare['function_name']:'';
                    $welfare_data['url']=$jump_url?$jump_url:'';
                    $welfare_data['config']='url';
                }
            
                if($create_url['function_name'] == 'ActivityCreateWelfare'){
                    //抽奖
                    $coupon_data = $this->GetOneAmindefault($admininfo['pre_table'],$this->ukey,'coupon_default');
                    $par1['register_coupon_return1'] = $coupon_data;
                    writeOperationLog($par1, 'newIntegral');
                    if($coupon_data['function_name'] == 2){
                    
                        $coupon_return_data = CouponService::giveCouponBatch1($welfare['function_name'],$openid,1);
                        $par['register_coupon_return'] = $coupon_return_data;
                        writeOperationLog($par, 'newIntegral');
                        if($coupon_return_data['code']==200){
                            $welfare_data['activity']=1;
                        }else{
                            $welfare_data['activity']='';
                        }
                    }else{
                        $url='http://182.92.31.114/rest/act/'.$welfare['function_name'].'/'.$openid;
                        $uinfo=http($url,array());
                        $unifo_arr=json_decode($uinfo,true);
                        if($unifo_arr['code'] == 0){
                            $welfare_data['activity']=1;
                        }else{
                            $welfare_data['activity']='';
                        }
                    }
                    $welfare_data['config']='activity';
                }
            
                if($create_url['function_name'] == 'ScoreCreateWelfare'){
                    //首次注册赠送积分
                    $res['key_admin']=$key_admin;
                    $res['sign_key']=$admininfo['signkey'];
                    $res['scoreno']=$welfare['function_name'];
                    $res['scorecode']=date('Y-m-d');
                    $res['cardno']=$uinfo['data']['cardno'];
                    $res['membername']=$uinfo['data']['usermember'];
                    $res['why']='注册赠积分';
                    $res['sign']=sign($res);
                    unset($res['sign_key']);
                    $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
                    $uinfo=http($url, $res, 'POST');
                    $uinfo=json_decode($uinfo, true);
                    if($uinfo['code']==200){
                        $welfare_data['score']=1;
                        $welfare_data['score_num'] = $res['scoreno'];
                    }else{
                        $welfare_data['score']='';
                    }
                    $welfare_data['config']='score';
                }
            }
        }

        $data = array('code' => '200','data'=>$welfare_data, 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    protected function getSign($subParams,$admininfo) {
        $subParams['sign_key']=$admininfo['signkey'];
        $sign = sign($subParams);
        return $sign;
    }







    /**
     * 通过openid来判断是否数据是否存在(用来避免同一个微信号绑定多个手机号)
     * @param $prefix
     * @param $openid
     * @return mixed
     */
    protected function checkUserExists($prefix, $openid) {
        $user = M('mem', $prefix);
        $re = $user->where(array('openid' => $openid))->find();
        return $re;
    }




    /**
     * 创建会员表单
     * 每一个商户的auto_form表
     *
     * total_form
     * total_form_default
     * xxx_auto_form
     * 完成日期：2017-3-1
     */
    public function EditMemberForm()
    {
//        $json='{"code":200,"data":[{"id":"21","isrequired":"0","placeholder":"","sub":"","value":[],"minlength":"","maxlength":"","content":"\u59d3\u540d","content_key":"name","content_type":"text","userinfo":"aaa"},{"id":"22","isrequired":"1","placeholder":"","sub":"","value":[{"default_content":"\n                           \u5973","default_content_key":"0"},{"default_content":"\n                           \u7537","default_content_key":"1"}],"minlength":"","maxlength":"","content":"\u6027\u522b","content_key":"sex","content_type":"radio","userinfo":1},{"id":"29","isrequired":"1","placeholder":"","sub":"","value":[],"minlength":"","maxlength":"","content":"\u624b\u673a\u53f7","content_key":"mobile","content_type":"number","userinfo":"12345678913"},{"id":"32","isrequired":"0","placeholder":"","sub":"","value":[],"minlength":"","maxlength":"","content":"\u8eab\u4efd\u8bc1\u53f7","content_key":"idnumber","content_type":"text","userinfo":null},{"id":"26","isrequired":"0","placeholder":"","sub":"","value":[],"minlength":"","maxlength":"","content":"\u8f66\u724c\u53f7","content_key":"carnumber","content_type":"text","userinfo":null},{"id":"33","isrequired":"0","placeholder":"","sub":"","value":[],"minlength":"","maxlength":"","content":"\u751f\u65e5","content_key":"birth","content_type":"date","userinfo":"2017-03-15"},{"id":"31","isrequired":"0","placeholder":"","sub":"","value":[{"default_content": "跑步","default_content_key": "a"},{"default_content": "看电影","default_content_key": "b"},{"default_content": "吃吃吃","default_content_key": "c"}],"minlength":"","maxlength":"","content":"\u7231\u597d","content_key":"hobby","content_type":"checkbox","userinfo":"a,b"},{"id":"30","isrequired":"1","placeholder":"","sub":"","value":[],"minlength":"","maxlength":"","content":"\u65f6\u95f4","content_key":"time","content_type":"time","userinfo":"18:30:10"}],"msg":"SUCCESS."}';
////        $json = str_replace('\/','',$json);
////        echo $json;
////        echo json_decode($json, true);
////        dump(json_decode($json, true));
//        returnjson(json_decode($json, true), $this->returnstyle, $this->callback);
        $key_admin=$this->ukey;
        $openid=I('openid');
        if ('' == $key_admin || '' == $openid){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($key_admin);
        $dbuser=$this->checkUserExists($admininfo['pre_table'], $openid);//获取会员信息
        if (!$dbuser){//如果数据库会员信息为空，则返回无此会员
            returnjson(array('code'=>103), $this->returnstyle, $this->callback);
        }else{//有此会员，根据卡号获取会员信息
            $patams['key_admin']=$admininfo['ukey'];
            $patams['sign_key']=$admininfo['signkey'];
            $patams['card']=$dbuser['cardno'];
            $get = I("param.");
            unset($get['callback']);
            unset($get['_']);
            $patams = array_merge($patams, $get);
            $patams['sign']=sign($patams);
            unset($patams['sign_key']);
            $url=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';
            $userinfo=http($url, $patams, 'POST');
            $userinfo=json_decode($userinfo, true);
            if (!isset($userinfo['code']) || $userinfo['code'] != 200){
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);
            }
        }


        $db=M('auto_form',$admininfo['pre_table']);
        $join=' join `total_form` on `form_key_id` = `total_form`.`id`';
//        $sel=$db->join($join)->where(array('isenable'=>1))->select();
        $sel=$db->field('total_form.id,ischange,isrequired,placeholder,sub,value,minlength,maxlength,content,content_key,content_type,function_name,sort')->join($join)->where(array('isenable'=>1,'type'=>0))->order('sort asc')->select();


        $dbdefault=M('form_default','total_');
        $dbself = M('form_default', $admininfo['pre_table']);
        //循环处理每一个表单项
        foreach ($sel as $key => $value){
            if ('' == $value['value']){
                $selself = null;
                $selself = $dbself->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                $sel[$key]['value'] = $selself;
                if ( !$selself ) {
                    if ('' != $value['function_name']) {
                        $defaultreturn = $value['function_name']();
                        $sel[$key]['value'] = $defaultreturn;
                    } else {
                        $seldefault=$dbdefault->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                        if ($seldefault) {
                            if ($value['content_type']=='radio' || $value['content_type']=='checkbox' || $value['content_type']=='select') {
                                $sel[$key]['value']=$seldefault;
                            }else{
                                $sel[$key]['value']=$seldefault[0]['default_content'];
                            }
                        }else {
                            $sel[$key]['value']='';
                        }
                    }
                }
            }
            $sel[$key]['userinfo']=$userinfo['data'][$sel[$key]['content_key']];
            unset($sel[$key]['function_name']);
        }
        if ($sel){
            $msg['code']=200;
            $msg['data']=$sel;
        }else{
            $msg['code']=104;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }







    /**
     * 创建会员C端自动表单提交
     */
    public function EditMember()
    {
        $params=I("param.");
        $key_admin=I('key_admin');
        $openid=I('openid');
        if ('' == $key_admin || '' == $openid) {
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($key_admin);
        $db=M('auto_form',$admininfo['pre_table']);
        $join=' join `total_form` on `form_key_id` = `total_form`.`id`';
        $sel=$db->join($join)->where(array('isenable'=>1, 'type'=>0))->select();
//        $sel=$db->field('total_form.id,isrequired,placeholder,sub,value,minlength,maxlength,content,content_key,content_type,function_name')->join($join)->where(array('isenable'=>1))->select();
        $dbdefault=M('form_default','total_');
        $dbself = M('form_default', $admininfo['pre_table']);
        //循环处理每一个表单项
        foreach ($sel as $key => $value){
            if ('' == $value['value']){
                $selself = null;
                $selself = $dbself->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                $sel[$key]['value'] = $selself;
                if ( !$selself ) {
                    if ('' != $value['function_name']) {
                        $defaultreturn = $value['function_name']();
                        $sel[$key]['value'] = $defaultreturn;
                    } else {
                        $seldefault=$dbdefault->field('default_content,default_content_key')->where(array('form_id'=>$value['id']))->select();
                        if ($seldefault) {
                            if ($value['content_type']=='radio' || $value['content_type']=='checkbox' || $value['content_type']=='select') {
                                $sel[$key]['value']=$seldefault;
                            }else{
                                $sel[$key]['value']=$seldefault[0]['default_content'];
                            }
                        }else {
                            $sel[$key]['value']='';
                        }
                    }
                }
            }
        }
        //根据B端后台设置，验证传递的参数完整性
        foreach ($sel as $item => $value) {
            if ($value['isrequired'] == 1){
                if ( array_key_exists($value['content_key'],$params) ){
                    if ('' == $params[$value['content_key']]){
                        returnjson(array('code'=>1030,'data'=>$value['content_key'].' value is null'), $this->returnstyle, $this->callback);
                        break;
                    }
                }else{
                    returnjson(array('code'=>1030,'data'=>$value['content_key']." is'ent setting"), $this->returnstyle, $this->callback);
                    break;
                }
            }
        }

        $server_code = $this->redis->get($params['mobile']);
        //白名单
        $phone=array(13522667528,18910124223,13521625139);
        if (!in_array($params['mobile'], $phone)){
            if ($_SERVER['SERVER_ADDR'] != '123.56.138.28'){
                /**
                 * 此处可以添加如下对验证码的验证错误提示:
                 * 验证码和手机号不一致
                 * 验证码失效
                 * 验证码错误
                 */

                //验证验证码
                if (isset($code) && $code != $server_code) {
                    $data = array('code' => '1031', 'msg' => 'invalid check code');
                    returnjson($data, $this->returnstyle, $this->callback);
                }
            }
        }
        // 验证码使用完则清除redis中的记录
        $this->redis->delete($params['mobile']);

        $unionid = I('unionid');

        // 查询会员表
        $user = $this->getUserCardByOpenId($admininfo['pre_table'], $this->user_openid);
        if ($user == '2000') {
            $data = array('code' => '2000', 'msg' => 'u are not our member!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $unionid = $unionid?$unionid:"";
        $uinfo = $this->getMemberByCard($user['cardno'],$unionid);

        if ($uinfo['code'] == '102') {
            $data = array('code' => '3000', 'msg' => 'invalid card no.!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        //查询是否需要判断会员信息中生日和身份证号只能修改一次
        $ischangeone=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'birthday&idcardonly');
        //如果传入的参数有生日和卡号
        if ($params['birth'] || $params['idnumber']){
            if (is_array($ischangeone)){//如果有配置
                if ($ischangeone['function_name'] == 1){//判断条件符合，需要判断
                    if ($user['ischangeone'] == 1){//已经修改过
                        if ($params['birth'] != $user['birthday'] || $params['idnumber'] != $user['idnumber']){
                            returnjson(array('code'=>1083), $this->returnstyle, $this->callback);
                        }
                    }
                }
            }
        }

        /**
         * 这里调用会员接口传递参数
         */
        $params['sign_key']=$admininfo['signkey'];
        if (isset($params['callback'])){//删除jaonp的回调函数
            unset($params['callback']);
            unset($params['_']);
        }
        
        if($unionid != ''){
            $params['unionid'] = $unionid;
        }
        
        $params['cardno']=$user['cardno'];
        $params['sign']=sign($params);
        unset($params['sign_key']);
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/editMember';
        $uinfo=http($url, $params, 'POST');
        $uinfo=json_decode($uinfo, true);
        // 修改会员资料失败
        if ($uinfo['code'] != 200) {
            if ($uinfo['code'] == 1012) {
                $data = array('code' => '2003', 'msg' => '该手机号已经注册过');
                returnjson($data, $this->returnstyle, $this->callback);
            } else {
                $data = array('code' => '3000', 'msg' => 'update member information failed!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
        $cardno = $user['cardno'];
        // 更新数据表
        $user = M('mem', $admininfo['pre_table']);

        if($this->ukey == '808a88b3307936086d5f9b3419c3247a' || $this->ukey == '202cb962ac59075b964b07152d234b70'){//未来中心修改资料赠送积分
        
            $userinfo = $user->where(array('cardno'=>$cardno))->field('is_save')->find();

            if($userinfo['is_save'] == 1){
                $res_Save = $this->add_integral($admininfo['signkey'],$params['name'],200,$cardno,'','首次修改资料赠送积分');
        
                if($res_Save['code'] == 200){
                    $user->where(array('cardno'=>$uinfo['data']['cardno']))->save(array('is_save'=>2));
                }
        
            }
        
        }
        
        $updata['mobile'] = $params['mobile'];
        $updata['phone'] = $params['mobile'];
        $updata['idnumber'] = $params['idnumber'];
        $updata['usermember'] = $params['name'];
        $updata['sex'] = $params['sex'];
        $updata['birthday'] = empty($params['birth']) ? time() : strtotime($params['birth']);
//        $updata['address'] = $params['address'];
        if ($params['birth'] || $params['idnumber']){
            if (is_array($ischangeone)){//如果有配置
                if ($ischangeone['function_name'] == 1){//判断条件符合，需要判断
                    $updata['ischangeone']=1;
                }
            }
        }

        $re = $user->where(array('openid' => $this->user_openid))->save($updata);
        if ($re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        //赠送Ycoin埋点
        $ycoin = I('ycoin');
        if($ycoin && isset($ycoin['openid']) && isset($ycoin['nickname']) && isset($ycoin['headimg'])){
            //判断有没有编辑会员赠送过Ycoin
            $db5=M('coin_changelog',$admininfo['pre_table']);
            $editLog = $db5->where(array('openid' =>$ycoin['openid'],'mark'=>'editMember'))->find();
            if(empty($editLog)){
                $ycoin['key_admin'] = $admininfo['ukey'];
                $subParams = $ycoin;
                $subParams['event'] = 'editMember';
                $subParams['mark'] = '编辑会员资料赠送';
                $subParams['sign'] = $this->getSign($subParams, $admininfo);
                $url = C('DOMAIN') . "/ClientApi/Inside/ycoinchange";
                $result = curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
            }
        }


        $data = array('code' => '200', 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    //增加积分
    public function add_integral($sign_key,$usermember,$scoreno,$cardno,$scorecode,$main){
        $url= C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';//增加积分接口
    
        $res['data']['key_admin']=$this->ukey;
        $res['data']['sign_key']=$sign_key;
        $res['data']['membername']=$usermember;
        $res['data']['scoreno']=$scoreno;
        $res['data']['cardno']=$cardno;
        $res['data']['scorecode']=$scorecode?$scorecode:date('Y-m-d');
        $res['data']['why']=$main;
        $res['data']['sign']=sign($res['data']);
        unset($res['data']['sign_key']);
        $add_integral_arr=http($url,$res['data']);
        $return_integral=json_decode($add_integral_arr,true);
        return $return_integral;
    
    }

    /**
     * 验证用户是否登录
     * @param $prefix 查询表前缀
     * @param $openid 会员openid
     * @return string
     */
    public function getUserCardByOpenId($prefix, $openid) {

        // 读取缓存
        //$uinfo = $this->redis->get('member:' . $openid);
        //if (!$uinfo) {
        $re = $this->checkUserExists($prefix, $openid);
        if (!$re) {
            return '2000';
        }
        /*else {
            if ($re['cookie'] != cookie($prefix . 'ck')) {
                return '2000';
            }
        }*/
        //$this->redis->set('member:' . $openid, json_encode($re));
        //} else {
        //$re = json_decode($uinfo, true);
        //}

        return $re;
    }



    /**
     * 调用会员平台按卡号查询会员信息
     * @param $cardNo 会员卡号
     * @return bool
     * @throws \Exception
     */
    protected function getMemberByCard($cardNo,$unionid = '') {
        if (!$cardNo) return false;

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            return false;
        }

        if($unionid != ''){
            $data['unionid'] = $unionid;
        }
        $data['card'] = $cardNo;
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $mer_re['signkey'];
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobycard';
        $curl_re = http($url, $data, 'post');
//         writeOperationLog(array('get member by card' => $curl_re), 'jaleel_logs');
        $info = json_decode($curl_re, true);
        if ($info['code'] == 200){
            $info['data']['score']=number_format($info['data']['score'],2,'.','');
        }
        return $info;
    }

}