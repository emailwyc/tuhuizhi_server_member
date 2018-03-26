<?php
namespace Member\Controller;

use Common\Controller\JaleelController;
use Common\Service\RedisService;
use PublicApi\Service\SendMessageService;
use PublicApi\Service\CouponService;

class MemberController extends JaleelController {

    /**
     * 验证用户是否是会员接口
     */
    public function checkopenid() {
        if (!$this->ukey or !$this->user_openid) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $uinfo = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);

        if (is_array($uinfo)) {
            $data = array('code' => '200', 'msg' => 'success');
        } else if ($uinfo == '2000') {
            $data = array('code' => '2000', 'msg' => 'u are not our member!');
        }
        returnjson($data, $this->returnstyle, $this->callback);
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
     * 用户注销接口
     */
    public function logout() {

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);

        $user = M('mem', $mer_re['pre_table']);

        // 验证此微信是否注册过
        $check_re = $this->checkUserExists($mer_re['pre_table'], $this->user_openid);

        if (is_array($check_re)) {
            $up_re = $user->where(array('mobile' => $check_re['mobile']))->save(array('openid' => ''));
            if (!$up_re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/UnBind';
        $data = array('cardno'=>$check_re['cardno'],'openid'=>$this->user_openid,'key_admin'=>$this->ukey,'sign_key'=>$mer_re['signkey']);
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $curl_re = http($url, $data, 'post');
        
        $data = array('code' => '200', 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
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
     * 绑卡接口
     */
    public function bindCard() {
        $mobile = I('mobile');
        $code = I('code');
        $server_code = $this->redis->get($mobile);

        writeOperationLog(array('bind card parameter' => 'mobile:' . $mobile . ', user_openid:' . $this->user_openid . ', code:' . $server_code . ', key_admin:' . $this->ukey), 'jaleel_logs');

        if (!$mobile or !$this->user_openid or !$code or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        /**
         * 此处可以添加如下对验证码的验证错误提示:
         * 验证码和手机号不一致
         * 验证码失效
         * 验证码错误
         */

         //白名单
         $phone=array(13522667528,18910124223,13521625139);
         if (!in_array($mobile, $phone)){
             if ($_SERVER['SERVER_ADDR'] != '123.56.138.28'){
                 //验证验证码
                 if ($code != $server_code) {
                     $data = array('code' => '1031', 'msg' => 'invalid check code');
                     returnjson($data, $this->returnstyle, $this->callback);
                 }
             }
         }


        // 验证码使用完则清除redis中的记录
        $this->redis->delete($mobile);

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $user = M('mem', $mer_re['pre_table']);



        // 按手机号查询会员信息
        $uinfo = $this->getMemberByTel($mobile, $this->ukey, $mer_re['signkey'], $this->user_openid);
        if ($uinfo['code'] != '200') {
            $data = array('code' => '2000', 'msg' => $uinfo['msg']);
            returnjson($data, $this->returnstyle, $this->callback);
        }


        // 验证此微信是否注册过
        $check_re = $this->checkUserExists($mer_re['pre_table'], $this->user_openid);
        if (is_array($check_re)) {
            $up_re = $user->where(array('mobile' => $check_re['mobile']))->save(array('openid' => ''));
            if (!$up_re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        // 记录用户cookie
        $cookie = strtoupper(md5($this->user_openid . rand(1, 1000)));
        setcookie($mer_re['pre_table'] . 'ck', '', time() - 1);
        cookie($mer_re['pre_table'] . 'ck', $cookie, array('expire' => time() + 365 * 24 * 3600, 'path' => '/', 'domain' => '.rtmap.com'));

        // 更新数据表
        $re = $user->where(array('cardno' => $uinfo['data']['cardno']))->save(array('openid' => $this->user_openid, 'cookie' => $cookie));
        if (!$re) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        writeOperationLog(array('update member in our db' => $re), 'jaleel_logs');

        // 生成二维码
//        $this->createQrCode($this->user_openid, $uinfo['data']['cardno']);

        // 生成条形码
//        $this->createQrBar($this->user_openid, $uinfo['data']['cardno']);

        $data = array('code' => '200', 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 注册会员接口
     */
    public function register() {
        $mobile = I('mobile');
        $idcard = I('idcard');
        $name = I('name');
        $sex = I('sex'); // 前端传递0和1
        $birthday = I('birthday');
        $star = I('star');
        $career = I('career');
        $wechat = I('wechat');
        $area = I('area');
        $address = $area . '|' . I('address');
        $email = I('email');
        $ycoin = I('ycoin');

        //参数为空验证
        if (!$mobile or !$name or !$this->user_openid or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        writeOperationLog(array('register parmas' => 'mobile:' . $mobile . ' ,sex:' . $sex . ' ,idnumber:' . $idcard . ' ,name:' . $name . ', birth:' . $birthday . ',openid:' . $this->user_openid. ',email:' . $email), 'jaleel_logs');

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $user = M('mem', $mer_re['pre_table']);

        // 验证此微信是否注册过
        $check_re = $this->checkUserExists($mer_re['pre_table'], $this->user_openid);
        if (is_array($check_re)) {
            $up_re = $user->where(array('mobile' => $check_re['mobile']))->save(array('openid' => ''));
            if ($up_re === false) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        if ($sex == '') {
            $sex = 1;
        }

        // 创建会员
        $uinfo = $this->registerMem($mobile, $idcard, $name, $this->ukey, $mer_re['signkey'], $sex, $birthday, $this->user_openid, $star, $career, $wechat, $address, $email);

        // 创建失败
        if ($uinfo['code'] != 200) {

            if ($uinfo['code'] == 1012 || $uinfo['code'] == '2001') {
                $data = array('code' => '2001', 'msg' => 'u are already our member!');
                returnjson($data, $this->returnstyle, $this->callback);
            } elseif($uinfo['code'] <= 50) {
                $data = array('code' => $uinfo['code'], 'msg' => @$uinfo['data']);
                returnjson($data, $this->returnstyle, $this->callback);
            }else{
                $data = array('code' => $uinfo['code'], 'msg' => 'register member failed!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        // 生成二维码
//        $this->createQrCode($this->user_openid, $uinfo['data']['cardno']);

        // 生成条形码
//        $this->createQrBar($this->user_openid, $uinfo['data']['cardno']);

        //缓存会员信息
        //$this->redis->set('member:' . $this->user_openid, json_encode($uinfo['data']));

        // 记录用户cookie
        $cookie = strtoupper(md5($this->user_openid . rand(1, 1000)));;
        setcookie($mer_re['pre_table'] . 'ck', '',time() - 1);
        cookie($mer_re['pre_table'] . 'ck', $cookie, array('expire' => time() + 365 * 24 * 3600, 'path' => '/', 'domain' => '.rtmap.com'));

        // 更新数据表
        $re = $user->where(array('cardno' => $uinfo['data']['cardno']))->save(array('openid' => $this->user_openid, 'cookie' => $cookie));
        if (!$re) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
		}
		//赠送Ycoin埋点 start
		if($ycoin && isset($ycoin['openid']) && isset($ycoin['nickname']) && isset($ycoin['headimg'])){
			//注册
			$ycoin['key_admin'] = $mer_re['ukey'];
			$subParams = $ycoin;$subParams['event'] = 'register';
			$subParams['sign'] = $this->getSign($subParams,$mer_re);
			$url = C('DOMAIN')."/ClientApi/Inside/addYcoinMem";
			$result=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
			$result = json_decode($result,true);
			if($result['code']==200){
				//积分赠送
				$subParams = array('key_admin'=>$mer_re['key_admin'],'openid'=>$params['openid'],'title'=>'注册赠送','remarks'=>'注册系统赠送','mark'=>'register');
				$subParams['sign'] = $this->getSign($subParams,$mer_re);
				$url = C('DOMAIN')."/ClientApi/Inside/ycoinChangeLog";
				$result1=curl_https($url, $subParams, array('Accept-Charset: utf-8'), 600, true);
				$result1 = json_decode($result1,true);
			}
		}
		//赠送Ycoin埋点 end

        writeOperationLog(array('update member in our db' => $re), 'jaleel_logs');

        $create_url=$this->GetOneAmindefault($mer_re['pre_table'], $this->ukey, 'AttributeConfiguration');
        $data['config']=$create_url['function_name']?$create_url['function_name']:'no'; //属性
        $find_status=$this->GetOneAmindefault($mer_re['pre_table'], $this->ukey, 'CreateIsWelfare');
        
        if($find_status['function_name'] == 2){
            if($create_url['function_name'] != '' && $create_url['function_name'] != 'no'){
            
                $welfare=$this->GetOneAmindefault($mer_re['pre_table'], $this->ukey, $create_url['function_name']);
            
                if($create_url['function_name'] == 'createsuccessurl'){
                    //跳转地址
                    $jump_url=$welfare['function_name']?$welfare['function_name']:'';
                    $data['url']=$jump_url?$jump_url:'';
                    $data['config']='url';
                }
            
                if($create_url['function_name'] == 'ActivityCreateWelfare'){
                    
                    //抽奖
                    $coupon_data = $this->GetOneAmindefault($mer_re['pre_table'],$this->ukey,'coupon_default');
                    $par1['register_coupon_return1'] = $coupon_data;
                    writeOperationLog($par1, 'newIntegral');
                    if($coupon_data['function_name'] == 2){
                        
//                         $coupon_return_data = CouponService::giveCouponBatch($welfare['function_name'],$this->user_openid,1);
                        $coupon_return_data = CouponService::giveCouponBatch1($welfare['function_name'],$this->user_openid,1);
                        $par['register_coupon_return'] = $coupon_return_data;
                        writeOperationLog($par, 'newIntegral');
                        if($coupon_return_data['code']==200){
                            $data['activity']=1;
                        }else{
                            $data['activity']='';
                        }

                    }else{
                        $url='http://182.92.31.114/rest/act/'.$welfare['function_name'].'/'.$this->user_openid;
                        $uinfo=http($url);
                        $unifo_arr=json_decode($uinfo,true);
                        if($unifo_arr['code'] == 0){
                            $data['activity']=1;
                        }else{
                            $data['activity']='';
                        }
                    }
                    $data['config']='activity';
                }
            
                if($create_url['function_name'] == 'ScoreCreateWelfare'){
                    //首次注册赠送积分
                    $res['key_admin']=$this->ukey;
                    $res['sign_key']=$mer_re['signkey'];
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
                        $data['score']=1;
                        $data['score_num'] = $res['scoreno'];
                    }else{
                        $data['score']='';
                    }
                    $data['config']='score';
                }
            }
        }
        $data = array('code' => '200','data'=>$data, 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 更新会员信息接口
     */
    public function updater() {
        // 参数为空验证
        $mobile = I('mobile');
        $idcard = I('idcard');
        $name = I('name');
        $sex = I('sex');
        $birth = I('birthday');
        $code = I('code');
        $area = I('area');
        $address = $area . '|' . I('address');
        $email = I('email');
        $star = I('star');
        $career = I('career');
        $wechat = I('wechat');
        $server_code = $this->redis->get($mobile);

        writeOperationLog(array('update user information params' => 'mobile:' . $mobile . ', user_openid:' . $this->user_openid . ', code:' . $server_code . ', key_admin:' . $this->ukey, 'birth:' . $birth . 'address:' . $address . ',email:' . $email . ',star:' . $star . ',career:' . $career . ',wechat:' . $wechat), 'jaleel_logs');



        //验证参数
        if (!$mobile or !$this->user_openid or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        //白名单
        $phone=array(13522667528,18910124223,13521625139);
        if (!in_array($mobile, $phone)){
            if ($_SERVER['SERVER_ADDR'] != '123.56.138.28'){
                /**
                 * 此处可以添加如下对验证码的验证错误提示:
                 * 验证码和手机号不一致
                 * 验证码失效
                 * 验证码错误
                 */

                //验证验证码
//                 if (isset($code) && $code != $server_code) {
//                     $data = array('code' => '1031', 'msg' => 'invalid check code');
//                     returnjson($data, $this->returnstyle, $this->callback);
//                 }
            }
        }



        // 验证码使用完则清除redis中的记录
        $this->redis->delete($mobile);

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员表
        $user = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);
        if ($user == '2000') {
            $data = array('code' => '2000', 'msg' => 'u are not our member!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $uinfo = $this->getMemberByCard($user['cardno']);

        if ($uinfo['code'] == '102') {
            $data = array('code' => '3000', 'msg' => 'invalid card no.!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        //查询是否需要判断会员信息中生日和身份证号只能修改一次
        $ischangeone=$this->GetOneAmindefault($mer_re['pre_table'], $this->ukey, 'birthday&idcardonly');
        if(is_numeric($ischangeone['function_name'])){
            $ischangeone_function['idnumber']=$ischangeone['function_name'];
        }else{
            $ischangeone_function=json_decode($ischangeone['function_name'],true);
        }
        //如果传入的参数有生日和卡号
        if ($birth || $idcard){
            if (is_array($ischangeone)){//如果有配置
                if ($ischangeone_function['idnumber'] == 1){//判断条件符合，需要判断
                    if ($user['ischangeone'] == 1){//已经修改过
                        if ($idcard != $user['idnumber']){
                            returnjson(array('code'=>1083), $this->returnstyle, $this->callback);
                        }
                    }
                }
                if ($ischangeone_function['birthday'] == 1){//判断条件符合，需要判断
                    if ($user['ischangeonebirth'] == 1){//已经修改过
                        if ($birth != $user['birthday']){
                            returnjson(array('code'=>1083), $this->returnstyle, $this->callback);
                        }
                    }
                }
            }
        }


        $params['mobile'] = $mobile;
        $params['key_admin'] = $this->ukey;
        $params['sign_key'] = $mer_re['signkey'];
        $params['idnumber'] = $idcard;
        $params['name'] = $name;
        $params['cardno'] = $uinfo['data']['cardno'];
        $params['carano'] = $uinfo['data']['cardno'];

        $params['sex'] = $sex;
        $params['openid'] = $this->user_openid;
        $params['address'] = $address;
        $params['star'] = $star;
        $params['career'] = $career;
        $params['wechat'] = $wechat;
        $params['email'] = $email;
        
        if($birth == '' && $idcard != ''){
            $births=getIDCardInfo($idcard);
            $birth=$births['birthday']?$births['birthday']:'';
        }
        
        $params['birth'] = empty($birth) ? time() : strtotime($birth);

        if ($this->ukey == '808a88b3307936086d5f9b3419c3247a'){
            unset($params);
            $params=I('param.');
            unset($params['callback']);
            unset($params['_']);

            $params['address']=$params['province'].','.$params['city'].','.$params['district'].','.$params['address'];
            if($params['hobby']){
                $params['hobby']=trim($params['hobby'],'&');
            }
            $params['carano'] = $uinfo['data']['cardno'];
            $params['sign_key'] = $mer_re['signkey'];
            $params['cardno']= $uinfo['data']['cardno'];
            $params['key_admin'] = $this->ukey;           
            $params['birth']= empty($params['birth']) ? time() : strtotime($params['birth']);
            $update_re=$this->updateMem($params);
        }else{
            // 修改会员资料
            $update_re = $this->updateMem($params);
        }

        // 修改会员资料失败
        if ($update_re['code'] != 200) {
            if ($update_re['code'] == 1012) {
                $data = array('code' => '2003', 'msg' => '该手机号已经注册过');
                returnjson($data, $this->returnstyle, $this->callback);
            } else {
                returnjson($update_re, $this->returnstyle, $this->callback);
            }
        }

        $user = M('mem', $mer_re['pre_table']);
        
        if($this->ukey == '808a88b3307936086d5f9b3419c3247a' || $this->ukey == '202cb962ac59075b964b07152d234b70'){//未来中心修改资料赠送积分

            $userinfo = $user->where(array('cardno'=>$uinfo['data']['cardno']))->field('is_save')->find();

            if($userinfo['is_save'] == 1){
                $res_Save = $this->add_integral($mer_re['signkey'],$name,200,$uinfo['data']['cardno'],'','首次修改资料赠送积分');
                
                if($res_Save['code'] == 200){
                    $user->where(array('cardno'=>$uinfo['data']['cardno']))->save(array('is_save'=>2));
                }
                
            }

        }
        
        // 更新数据表
        $updata['mobile'] = $mobile;
        $updata['phone'] = $mobile;
        $updata['idnumber'] = $idcard;
        $updata['usermember'] = $name;
        $updata['sex'] = $sex;
        $updata['birthday'] = empty($birth) ? time() : strtotime($birth);
        $updata['address'] = $address;
        if ($birth || $idcard){
            if (is_array($ischangeone)){//如果有配置
                if ($ischangeone_function['idnumber'] == 1){//判断条件符合，需要判断
                    $updata['ischangeone']=1;
                }
                if ($ischangeone_function['birthday'] == 1){//判断条件符合，需要判断
                    $updata['ischangeonebirth']=1;
                }
            }
        }

        $re = $user->where(array('openid' => $this->user_openid))->save($updata);
        writeOperationLog(array('update member in our db' => $re), 'jaleel_logs');
        if ($re === false) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 删除会员缓存
        //$this->redis->delete('member:' . $this->user_openid);

        //缓存会员信息
        //$this->redis->set('member:' . $this->user_openid, json_encode($update_re['data']));

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
     * 出示卡片接口
     */
    public function showCard() {

        if (!$this->user_openid or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);
        writeOperationLog(array('show card user' => json_encode($uinfo)), 'jaleel_logs');
        if ($uinfo == '2000') {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 判断已经给用户生成二维码和条形码 没有则生成
        /*$qrcode = './public/member/qrcode/' . $this->user_openid . $uinfo['cardno'] . '.png';

        if(!file_exists($qrcode)) {
            $this->createQrCode($this->user_openid, $uinfo['cardno']);
        }

        $bar = './public/member/bar/' . $this->user_openid . $uinfo['cardno'] . '.png';

        if(!file_exists($bar)) {
            $this->createQrBar($this->user_openid, $uinfo['cardno']);
        }*/

        $data = array('code' => '200', 'data' => array('cardno' => $uinfo['cardno']),'msg' => 'SUCCESS');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 查询远程会员资料接口(以远程为准,数据更准确)
     */
    public function getuserinfo() {
        writeOperationLog(array('get user info interface' => $this->user_openid . ',' . $this->ukey), 'jaleel_logs');
        if (!$this->user_openid or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        $unionid = I('unionid');
        
        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);
        if ($uinfo == '2000') {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        /**
         * 汇悦城会员升级会改变卡号
         * 所以对其做按手机号查询会员信息处理
         */
        if ($mer_re['pre_table'] == 'huiyuecheng_') {
            $tel = empty($uinfo['mobile']) ? $uinfo['phone'] : $uinfo['mobile'];
            $minfo = $this->getMemberByTel($tel, $this->ukey, $mer_re['signkey'], $this->user_openid);
            writeOperationLog(array('get user info openid' => $this->user_openid . ',' . $this->ukey), 'jaleel_logs');
            if ($minfo) {
                $user = M('mem', $mer_re['pre_table']);
                $user->where(array('openid' => $this->user_openid))->save(array('cardno'=>$minfo['data']['cardno']));
            }
        } else {
            $minfo = $this->getMemberByCard($uinfo['cardno'],$unionid);
            
            
        }

        if (!$minfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        if ($minfo['code']!=200) {
            $data = array('code' => '102', 'data' => $minfo['data']);
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员等级名称
        /*$default = M('default', $mer_re['pre_table']);
        $re = $default->where(array('customer_name' => 'viplevel'))->find();
        $level_json = $re['function_name'];
        $level_arr = json_decode($level_json, true);*/

        // 性别以crm为准 返回了性别则使用 没有则使用数据库中的
        if (isset($minfo['data']['sex'])) {
            $sex = $minfo['data']['sex'];
        } else {
            $sex = $uinfo['sex'];
        }

        // 若有地址字段则将地址分开 如北京|北京|东城区|三里屯路17号三里屯太古里 分成area:北京|北京|东城区和address:三里屯路17号三里屯太古里
        $full_address = $minfo['data']['address'];
        if($this->ukey == '808a88b3307936086d5f9b3419c3247a'){
            $address_num = strpos($full_address, ',');
            if($address_num>0){
                $address_arr = explode(',', $full_address);
                $address_count = count($address_arr);
                if($address_count >3){
                    $minfo['data']['province'] = $address_arr[0];
                    $minfo['data']['city'] = $address_arr[1];
                    $minfo['data']['district'] = $address_arr[2];
                    unset($address[0],$address_arr[1],$address_arr[2]);
                    if($address_count == 4){
                        $minfo['data']['address'] = $address_arr[3];
                    }else{
                        $address_arr = array_values($address_arr);
                        $minfo['data']['address'] = implode(',', $address_arr);
                    }
                }
            }
            if($minfo['data']['hobby']){
                $minfo['data']['hobby']=trim($minfo['data']['hobby'],'&');
            }
        }else{
            if (isset($full_address) && $mer_re['id'] == 9)  {
                $pos = strrpos($full_address, '|');
                $area = substr($full_address, 0, $pos);
                $address = substr($full_address, $pos+1);
    
                if ($address === false) {
                    $address = '';
                }
    
                $minfo['data']['area'] = $area;
                $minfo['data']['address'] = $address;
            }
        }
        // 太古里卡样是根据xf_vipcardno显示的
        if ($minfo['data']['xf_vipcardno']) {
            $minfo['data']['cardno'] = $minfo['data']['xf_vipcardno'];
        }

        if (!$minfo['data']['idcard']){
            $minfo['data']['idcard'] = $uinfo['idnumber'];
        }

//        $minfo['data']['cardtype'] = $level_arr[$minfo['data']['cardtype']];
//        $minfo['data']['cardtype'] = $minfo['data']['cardtype'];
        if($sex!=''){
            $minfo['data']['sex'] = intval($sex);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $minfo['data']);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 获取会员详情接口(对外)
     * 需要签名验证
     */
    public function getUserDetails() {
        $timestamp = I('timestamp');
        $sign_par = I('sign');

        if (!$this->user_openid or !$this->ukey or !$timestamp or !$sign_par) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 签名验证
        $sign_arr = array(
            'key_admin' => $this->ukey,
            'openid'    => $this->user_openid,
            'timestamp' => $timestamp,
            'sign_key'   => $mer_re['signkey'],
        );

        $sign = sign($sign_arr);

        writeOperationLog(array('get user details parameters:' => json_encode($sign_arr)), 'jaleel_logs');
        writeOperationLog(array('get user details outside sign:' => $sign_par), 'jaleel_logs');
        writeOperationLog(array('get user details our sign:' => $sign), 'jaleel_logs');
        writeOperationLog(array('get merchant'=> json_encode($mer_re)), 'jaleel_logs');

        // 签名错误
        if ($sign != $sign_par) {
            $data = array('code' => '1002', 'msg' => 'invalid sign!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);

        if ($uinfo == '2000') {
            //本地没有查西单接口
            if($mer_re['pre_table'] == 'xidan_'){
                
                $openid_data=$this->getMemberByOpenid($this->user_openid);
                
                if($openid_data['code'] != '200'){
                    $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
                    returnjson($data, $this->returnstyle, $this->callback);
                }else{
                    $uinfo=$openid_data['data'];
                }
                
            }else{
                $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
        

        /**
         * 汇悦城会员升级会改变卡号
         * 所以对其做按手机号查询会员信息处理
         */
        if ($mer_re['pre_table'] == 'huiyuecheng_') {
            $tel = empty($uinfo['mobile']) ? $uinfo['phone'] : $uinfo['mobile'];
            $minfo = $this->getMemberByTel($tel, $this->ukey, $mer_re['signkey'], $this->user_openid);

            if ($minfo) {
                $user = M('mem', $mer_re['pre_table']);
                $user->where(array('openid' => $this->user_openid))->save(array('cardno'=>$minfo['data']['cardno']));
            }
        } else {
            
            if($this->ukey == 'c5e30385614b9fb90d3c677ab51824ce'){
                $minfo = $this->getMemberByCard($uinfo['cardno'],$uinfo['unionid']);
            }else{
                $minfo = $this->getMemberByCard($uinfo['cardno']);
            }
            
        }

        if (!$minfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员等级名称
        /*$default = M('default', $mer_re['pre_table']);
        $re = $default->where(array('customer_name' => 'viplevel'))->find();
        $level_json = $re['function_name'];
        $level_arr = json_decode($level_json, true);*/

        /*获取卡类型start*/
        $cardInfo = M('member_code', 'total_')->where(array('admin_id' => $mer_re['id'],'code'=>@$minfo['data']['cardtype']))->find();
        $minfo['data']['cardname'] = isset($cardInfo['name'])?$cardInfo['name']:"";
        /*获取卡类型end*/

        // 性别以crm为准 返回了性别则使用 没有则使用数据库中的
        if (isset($minfo['data']['sex'])) {
            $sex = $minfo['data']['sex'];
        } else {
            $sex = $uinfo['sex'];
        }

        $minfo['data']['idcard'] = $uinfo['idnumber'];
//        $minfo['data']['cardtype'] = $level_arr[$minfo['data']['cardtype']];
        $minfo['data']['sex'] = intval($sex);

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $minfo['data']);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 调用会员平台注册接口
     * @param $mobile 手机号
     * @param $idcard 身份证号
     * @param $name 姓名
     * @param $ukey 商户key_admin
     * @param $sign_key 商户加密密钥
     * @param int $sex 性别
     * @param string $birth 生日
     * @param string $openid 微信openid
     * @param string $star 星座
     * @param string $career 职业
     * @param string $wechat 微信号
     * @param string $address 地址
     * @param string $email 邮箱
     * @return mixed
     * @throws \Exception
     */
    protected function registerMem($mobile, $idcard, $name, $ukey, $sign_key, $sex = 1, $birth = '', $openid = '', $star = '', $career = '', $wechat = '', $address = '', $email = '') {

        $data['mobile'] = $mobile;
        $data['idnumber'] = $idcard;
        $data['name'] = $name;
        $data['sex'] = $sex;
        $data['key_admin'] = $ukey;
        $data['sign_key'] = $sign_key;
        
        if($birth == '' && $idcard !=''){
            $births=getIDCardInfo($idcard);
            $birth=$births['birthday']?$births['birthday']:'';
        }
        
        $data['birth'] = empty($birth) ? time() : strtotime($birth);
        $data['birth1'] = empty($birth) ? "" : strtotime($birth);
        $data['openid'] = $openid;
        $data['star'] = $star;
        $data['career'] = $career;
        $data['wechat'] = $wechat;
        $data['address'] = $address;
        $data['email'] = $email;
        $data['sign'] = sign($data);
        writeOperationLog(array('sign str' => 'mobile:' . $mobile . ' ,sex:' . $sex . ' ,idnumber:' . $idcard . ' ,name:' . $name . ' ,key_admin:' . $ukey . ' ,sign_key:' . $sign_key . ' ,sign:' . $data['sign'] . ', birth:' . $birth . ', openid:' . $data['openid'] . ', star:' . $data['star'] . ', career:' . $data['career'] . ', wechat:' . $data['wechat'] . ', address:' . $data['address'] . ',email:' . $email), 'jaleel_logs');
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/createMember';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('register member' => $curl_re), 'jaleel_logs');
        return json_decode($curl_re, true);
//        echo $curl_re;
    }

    /**
     * 调用会员平台更新会员资料接口
     * @param $mobile 手机号
     * @param $idcard 身份证号
     * @param $name 姓名
     * @param $cardno 会员卡号
     * @param $ukey 商户key_admin
     * @param $sign_key 商户加密密钥
     * @param $sex 性别
     * @param string $birth 生日
     * @param string $openid 微信openid
     * @param string $address 住址
     * @param string $star 星座
     * @param string $career 职业
     * @param string $wechat 微信号
     * @param string $email 邮箱
     * @return mixed
     * @throws \Exception
     */
    protected function updateMem($data) {

//        $data['mobile'] = $mobile;
//        $data['key_admin'] = $ukey;
//        $data['sign_key'] = $sign_key;
//        $data['idnumber'] = $idcard;
//        $data['name'] = $name;
//        $data['cardno'] = $cardno;
//
//        $data['sex'] = $sex;
//        $data['openid'] = $openid;
//        $data['address'] = $address;
//        $data['star'] = $star;
//        $data['career'] = $career;
//        $data['wechat'] = $wechat;
//        $data['email'] = $email;
//        $data['birth'] = empty($birth) ? time() : strtotime($birth);
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/editMember';
        $curl_re = http($url, $data, 'post');
//        writeOperationLog(array('update member params' => json_encode($data)), 'jaleel_logs');
//        writeOperationLog(array('update member' => $curl_re), 'jaleel_logs');
        return json_decode($curl_re, true);
//        echo $curl_re;
    }

    /**
     * 调用会员平台按手机号查询会员信息接口
     * @param $tel 会员手机号
     * @param $ukey 商户密钥
     * @param $sign_key 加密密钥
     * @return bool
     * @throws \Exception
     */
    protected function getMemberByTel($tel, $ukey, $sign_key, $openid) {
        if (!$tel) return false;
        
        $data['mobile'] = $tel;
        $data['key_admin'] = $ukey;
        $data['sign_key'] = $sign_key;
        $data['openid'] = $openid;
        $data['sendmsg'] = 'sendmsg';
        $data['sign'] = sign($data);
        writeOperationLog(array('make sign' => 'mobile:' . $data['mobile'] . ' ,key_admin:' . $data['key_admin'] . ' ,sign_key:' . $data['sign_key'] . ' ,sign' . $data['sign']), 'jaleel_logs');
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getuserinfobymobile';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('get member by tel' => $curl_re), 'jaleel_logs');
        return json_decode($curl_re, true);
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

    /**
     * 按openid查询会员信息
     */
    public function getMemberByOpenid($openid){
        $mer_re = $this->getMerchant($this->ukey);     

        $data['openid'] = $openid;
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $mer_re['signkey'];
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/getInfoByOpenid';
        $curl_re = http($url, $data, 'post');
        //         writeOperationLog(array('get member by card' => $curl_re), 'jaleel_logs');
        $info = json_decode($curl_re, true);
        if ($info['code'] == 200){
            $info['data']['score']=number_format($info['data']['score'],2,'.','');
        }
        return $info;
    }
    
    
    /**
     * 生成二维码接口
     * @param $openId 会员openid
     * @param $userCard 会员卡号
     */
    protected function createQrCode($openId, $userCard) {

        $fileName = './public/member/qrcode/' . $openId . $userCard . '.png';

        if(!file_exists(dirname($fileName))) {
            mkdir(dirname($fileName), 0777, true);
        }

        if(!file_exists($fileName)) {
            vendor('phpqrcode.qrlib');
            \QRcode::png($userCard, $fileName, 'L', 4, 2);
        }
    }

    /**
     * 生成条形码接口
     * @param $openId 会员openid
     * @param $userCard 会员卡号
     */
    protected function createQrBar($openId, $userCard) {
        $fileName = './public/member/bar/' . $openId . $userCard . '.png';

        if(!file_exists(dirname($fileName))) {
            mkdir(dirname($fileName), 0777, true);
        }

        if(!file_exists($fileName)) {
            vendor('barcodegen.class.BCGColor');
            vendor('barcodegen.class.BCGDrawing');
            vendor('barcodegen.class.BCGFontFile');
            vendor('barcodegen.class.BCGcode128');

            $font = new \BCGFontFile('./public/font/Arial.ttf', 18);

            $color_black = new \BCGColor(0, 0, 0);
            $color_white = new \BCGColor(255, 255, 255);

            $drawException = null;

            try {
                $code = new \BCGcode128();
                $code->setScale(2);
                $code->setThickness(40);
                $code->setForegroundColor($color_black);
                $code->setBackgroundColor($color_white);
                $code->setFont($font);
                $code->parse($userCard);
            } catch(Exception $exception) {
                $drawException = $exception;
            }

            $drawing = new \BCGDrawing('', $color_white);

            if($drawException) {
                $drawing->drawException($drawException);
            } else {
                $drawing->setBarcode($code);

                $drawing->setDPI(72);

                $drawing->draw();
            }

            $drawing->setFilename($fileName);

            $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
        }
    }

    /**
     * 发送验证码接口
     * @throws \Exception
     */
//    public function sendMsg() {
//        if (time() >= 1514520000){//2017-12-30零点不再使用此接口
//            returnjson(['code'=>111], $this->returnstyle, $this->callback);
//        }
//        $phone = I('mobile');
////        writeOperationLog(array('key_admin:' => $this->ukey . $phone), 'jaleel_logs');
//        if (!$phone) {
//            $data = array('code' => '1030', 'msg' => 'please input your phone num!');
//            returnjson($data, $this->returnstyle, $this->callback);
//        }
//
//        // 查询商户配置
////        writeOperationLog(array('key_admin:' => $this->ukey), 'jaleel_logs');
//        $mer_re = $this->getMerchant($this->ukey);
//        if (!$mer_re) {
//            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
//            returnjson($data, $this->returnstyle, $this->callback);
//        }
//
////        session_start();
////        $_SESSION['check_code'] = rand(100000, 999999);
//        $code = rand(100000, 999999);
//        $this->redis->setex($phone, 300,$code);
//
//        $default = M('default', $mer_re['pre_table']);
//        $re = $default->where(array('customer_name' => 'sendmsg'))->find();
//        $fun_name = $re['function_name'];
//
//        $merchant = array(
//            'huiyuecheng_'  => '汇悦城',
//            'xidan_'        => '西单大悦城',
//            'maoye_'        => '茂业',
//            'aoyong_'       => '奥永广场',
//            'taiguli_'      => '三里屯太古里',
//            'baotai_'      => '东方宝泰',
//            'jinjue_'      => '金爵万象奥莱广场',
//            'daweilai_'=>'未来中心服务号',
//            'zhihuitu_'=>'智慧图开发账号',
//        );
//
//        // 调用商户对应的发送验证码的方法
////        $this->$fun_name($phone, $_SESSION['check_code'], $merchant[$mer_re['pre_table']]);
//        $tag = $merchant[$mer_re['pre_table']];
//        if (empty($merchant[$mer_re['pre_table']])) {
//            $static = M('total_static');
//            $result = $static->where(array('tid' => 12, 'admin_id' => $mer_re['id']))->find();
//            $tag = $result['content'];
//        }
//
//        $this->$fun_name($phone, $code, $tag);
//    }







    /**
     * 验证验证码是否正确（对外）
     */
    public function checkMsgValidCode() {
        $phone = I('tel');
        $code = I('code');
        $redis_code = $this->redis->get($phone);

        if ($redis_code == $code) {
            $data = array('code' => 200, 'msg' => 'success');
        } else {
            $data = array('code' => 404, 'msg' => 'invalid check code!');
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 商户会员权益接口
     */
    public function memberRight()
    {
        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        $static = M('total_static');
        $page_info = $static->where(array('tid' => 1, 'admin_id' => $mer_re['id']))->find();
        returnjson(array('code'=>200,'data'=>array('content'=>html_entity_decode($page_info['content']))), $this->returnstyle, $this->callback);
    }



    //商户会员手册接口
    public function memberBook()
    {
        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);

        // 查询权益内容
        $right = M('manual', $mer_re['pre_table']);
        $re = $right->order('sort asc')->select();

        $mdata=null;
        foreach ($re as $key => $val){
            $mdata[$key]['title']=$val['title'];
            $mdata[$key]['content']=htmlspecialchars_decode($val['content']);
            $mdata[$key]['sort']=(int)$val['sort'];

        }

        if ($re) {
            $data = array("code" => '200', "msg" => "SUCCESS", "data" => $mdata);
        } else {
            $data = array("code" => '3000', "msg" => "system error");
        }
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 商户联系方式接口
     */
    public function merchantContact() {

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询联系方式
        $contact = M('total_static');
        $re = $contact->where(array('admin_id' => $mer_re['id'], 'tid' => '2'))->find();

        if ($re) {
            $data = array("code" => '200', "msg" => "SUCCESS", "data" => $re);
        } else {
            $data = array("code" => '3000', "msg" => "system error");
        }
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 按sid返回相关页面数据
     */
    public function staticinterface() {
        $sid = I('sid');

        if (!$sid) {
            $data = array('code' => '1030', 'msg' => 'miss params!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询静态页面文本
        $contact = M('total_static');
        $re = $contact->where(array('admin_id' => $mer_re['id'], 'id' => $sid))->find();

        if ($re) {
            $re['content'] = html_entity_decode($re['content']);
            $data = array("code" => '200', "msg" => "SUCCESS", "data" => $re);
        } else {
            $data = array("code" => '3000', "msg" => "system error");
        }
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 水晶城发送验证码方法
     * @param $phone
     * @param $code
     * @throws \Exception
     */
    protected function shuijingchengmsg($phone, $code, $tag = '') {
        $url = 'http://120.55.193.51:8098/smtp/http/submit';
        $data['timestamp'] = date('YmdHis');
        $data['userName'] = 'sjsc';
        $data['userPass'] = 'sjsc';
        $data['sign'] = strtoupper(md5($data['userPass'].$data['timestamp']));
        $data['phones'] = $phone;
        $data['mhtMsgIds'] = time() . rand(1, 1000);
        $data['sendTime'] = '';
        $data['serviceCode'] = 'sjsc';
        $data['priority'] = '5';
        $data['msgType'] = '1';
        $data['msgContent'] = iconv('utf8', 'gbk', '验证码'.$code.',请勿向任何人提供您收到的短信验证码');
        $data['reportFlag'] = '0';

        $curl_re = http($url, $data, 'post');
        $result = json_decode($curl_re, true);

        writeOperationLog(array('send shuijingcheng msg' => $curl_re), 'jaleel_logs');

        if ($result['result'] != 0) {
            $data = array('code' => '3000', 'msg' => 'send message failed!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 智慧图发送验证码方法
     * @param $phone
     * @param $code
     * @throws \Exception
     */
    protected function zhihuitumsg($phone, $code, $tag)
    {
        $url = 'http://m.5c.com.cn/api/send/index.php';
        $data['username'] = 'zhihuitu';
        $data['password_md5'] = md5('rtmap_911');
        $data['apikey'] = 'd40d62eec4fbd6a6ce6dfdec1d9315cf';
        $data['mobile'] = $phone;
        $data['encode'] = 'UTF-8';
        $data['content'] = urlencode('您好,您的验证码为' . $code . ',【请勿向任何人提供您收到的短信验证码】【' . $tag . '】');


        /*限制 start */
        $limitnumkey = "phone:" . date('Y-m-d', time()) . ":limit:" . $phone;
        $limitnum = $this->redis->get($limitnumkey);
        $limitnum = empty($limitnum) ? 1 : ($limitnum + 1);
        if ($this->ukey == "06568534959b7d222680a9063ac49394"){
            if (true) {
                returnjson(array('code' => 200), $this->returnstyle, $this->callback);
            }
        }else{
            if ($limitnum > 6) {
                returnjson(array('code' => '1082', 'msg' => '您的短信次数已经用完了，明天再来试吧！'), $this->returnstyle, $this->callback);
            }
        }


        $limitnumkey1 = "phone:send:limit:".$phone;
        $limitnum1 = $this->redis->get($limitnumkey1);
        if(!empty($limitnum1)){
            returnjson(array('code' => '1082', 'msg' => '您的短信发送过于频繁，稍后再试吧！'), $this->returnstyle, $this->callback);
        }
        /*限制 end */



        $curl_re = http($url, $data, 'post');
        $result = json_decode($curl_re, true);

        writeOperationLog(array("send {$tag} msg" => $curl_re), 'jaleel_logs');

        if ($result['result'] != 0) {
            $data = array('code' => '3000', 'msg' => 'send message failed!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        /*限制 */
        $this->redis->setex($limitnumkey, 86400,$limitnum);
        $this->redis->setex($limitnumkey1, 60,1);
        /*限制 */
        $data = array('code' => '200', 'msg' => 'success');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 发短信接口，对外(新对外接口，包含除智慧图以外的所有商场短信接口)
     */
    public function sendmessagepublic()
    {
        $params = file_get_contents('php://input');
        $params = json_decode($params, true);
        //这几个是基础的
        if (!is_array($params) || empty($params['key_admin']) || empty($params['mobile']) || empty($params['sign']) || !empty($params['sign_key']) ){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $adminInfo = $this->getMerchant($params['key_admin']);
        $params['sign_key'] = $adminInfo['signkey'];
        $sign = $params['sign'];
        unset($params['sign']);
//        echo sign($params);
        if ($sign != sign($params)){
            returnjson(array('code'=>1002), $this->returnstyle, $this->callback);
        }
        $sendmsg = $this->GetOneAmindefault($adminInfo['pre_table'], $params['key_admin'], 'sendmsg');
        $sendmsg = $sendmsg['function_name'];
        $send = SendMessageService::sendMessages($adminInfo, $sendmsg, $params);
        if (is_array($send)){
            returnjson($send, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }
    /**
     * 发送短信接口（对外），旧接口，只有智慧图
     */
//    public function sendMessage() {
//        if (time() >= 1514520000){
//            returnjson(['code'=>111], $this->returnstyle, $this->callback);
//        }
//        $phone = I('mobile');
//        $msg = I('msg');
//
//        if (!$msg or !$phone) {
//            $data = array('code' => '1030', 'msg' => 'miss params!');
//            returnjson($data, $this->returnstyle, $this->callback);
//        }
//
//        $mer_re = $this->getMerchant($this->ukey);
//        if (!$mer_re) {
//            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
//            returnjson($data, $this->returnstyle, $this->callback);
//        }
//
//        $static = M('total_static');
//        $result = $static->where(array('tid' => 12, 'admin_id' => $mer_re['id']))->find();
//        $tag = $result['content'];
//
//
//
//        $ukey_arr = array(
//            'add3989b02c752457bcafbb556cbf0df',
//        );
//        if(in_array($this->ukey, $ukey_arr)){
//            $redis_code = $this->redis->get($phone);
//            if($redis_code != ''){
//                $code = $redis_code;
//            }else{
//                $code = rand(100000, 999999);
//            }
//            $this->redis->setex($phone, 300,$code);
//            $msg = str_replace("[code]",$code,$msg);
//        }
//
//
//
//        $msg=$msg.'【'. $tag .'】';
//
//        $url = 'http://m.5c.com.cn/api/send/index.php';
//        $data['username'] = 'zhihuitu';
//        $data['password_md5'] = md5('rtmap_911');
//        $data['apikey'] = 'd40d62eec4fbd6a6ce6dfdec1d9315cf';
//        $data['mobile'] = $phone;
//        $data['encode'] = 'UTF-8';
//        $data['content'] = urlencode($msg);
//
//        $curl_re = http($url, $data, 'post');
//        $result = json_decode($curl_re, true);
//
//        if ($result['result'] != 0) {
//            $data = array('code' => '3000', 'msg' => 'send message failed!');
//            returnjson($data, $this->returnstyle, $this->callback);
//        }
//
//        $code = $code?$code:'';
//        $data = array('code' => '200', 'msg' => 'success','data'=>array('messagecode'=>$code));
//        returnjson($data, $this->returnstyle, $this->callback);
//    }

    /**
     * 查询商户公众号的二维码
     */
    public function weixinqrcode() {

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询公众号的二维码
        $contact = M('total_static');
        $re = $contact->where(array('admin_id' => $mer_re['id'], 'tid' => '3'))->find();

        if ($re) {
            $data = array("code" => '200', "msg" => "SUCCESS", "data" => $re);
        } else {
            $data = array("code" => '3000', "msg" => "system error");
        }
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 意见反馈接口
     */
    public function feedback() {
        $content = I('content');

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        //为了兼容之前的意见反馈提交表单
//         if (!$content){
        //暂时注释
//             $default_feedback=$this->GetOneAmindefault($mer_re['pre_table'], $this->ukey, 'feedback');
//             if (!$default_feedback){//如果没有设置这个字段
//                 $data['code']=1017;
//                 $data['data']=1;
//                 returnjson($data,$this->returnstyle, $this->callback);
//             }
//             $default=json_decode($default_feedback['function_name'], true);
//             if (!$default['feedback']['enable']){//如果设置的值是false，不允许提交反馈
//                 $data['code']=1017;
//                 $data['data']=2;
//                 returnjson($data,$this->returnstyle, $this->callback);
//             }
//         }
        if (!$this->user_openid or !$this->ukey or !$content) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        $feed = M('feedback', $mer_re['pre_table']);
        $status = I('status');
        // 入库
        if($status== 'message'){    //注意,目前没有区分留言和意见反馈
            $seluser=M('mem',$mer_re['pre_table'])->where(array('openid'=>$this->user_openid))->find();
            $add_data['openid'] = $this->user_openid;
            //         $add_data['phone'] = empty($uinfo['phone']) ? $uinfo['mobile'] : $uinfo['phone'];
            $add_data['mem_id']=$seluser['id']?$seluser['id']:'';
            $add_data['content'] = $content;
            $add_data['createtime'] = time();
            $add_data['is_message']=2;
            $re = $feed->add($add_data);
        
            if (!$re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        
            $data = array('code' => '200', 'msg' => 'success!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);
        writeOperationLog(array('feedback user info' => json_encode($uinfo)), 'jaleel_logs');

        if ($uinfo == '2000') {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        $seluser=M('mem',$mer_re['pre_table'])->where(array('openid'=>$this->user_openid))->find();
        
        // 入库
        $add_data['mem_id']=$seluser['id'];
        $add_data['openid'] = $this->user_openid;
        $add_data['phone'] = empty($uinfo['phone']) ? $uinfo['mobile'] : $uinfo['phone'];
        $add_data['content'] = $content;
        $add_data['createtime'] = time();
        $add_data['is_message'] = 1;
        $re = $feed->add($add_data);

        if (!$re) {
            $data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'success!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 积分明细接口
     * @throws \Exception
     */
    public function getscoredetails() {
        $page = I('page');
        $page = empty($page) ? 1 : $page;
        $enddate = time() - ($page-1)*24*3600;
        $startdate = $enddate - 365 * 24 * 3600;

        if (!$this->user_openid or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($mer_re['pre_table'], $this->user_openid);
        writeOperationLog(array('score list user info' => json_encode($uinfo)), 'jaleel_logs');

        if ($uinfo == '2000') {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询会员积分总数
        $minfo = $this->getMemberByCard($uinfo['cardno']);
        if (!$minfo) {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data['key_admin'] = $this->ukey;
        $data['cardno'] = $uinfo['cardno'];
        $data['page'] = $page;
        if($this->ukey == 'ad357006c826abc7555f0f7e8a5e5493'){
            $data['lines'] = 100;
        }else{
            $data['lines'] = 10;
        }
        $data['startdate'] = $startdate;
        $data['enddate'] = $enddate;
        $data['sign_key'] = $mer_re['signkey'];
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/scorelist';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array('get score list params' => json_encode($data)), 'jaleel_logs');
//         writeOperationLog(array('get score list result' => $curl_re), 'jaleel_logs');
        $result = json_decode($curl_re, true);
       
        // 只取20条
        if (count($result['data']['scorelist']) > 20) {
            $result['data']['scorelist'] = array_slice($result['data']['scorelist'], 0, 20);
        }
        $result['data']['total_score'] = $minfo['data']['score'];
        returnjson($result, $this->returnstyle, $this->callback);
    }
    

    /**
     * 注册服务条款,供前端调用
     */
    public function termsofservice()
    {
        $key_admin=I('key_admin');
        if (!$key_admin){
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $admin=$this->getMerchant($key_admin);
        $db=M('default', $admin['pre_table']);
        $data=$db->where(array('customer_name'=>'termsofservice'))->find();
    
        if (empty($data)){
            $msg['code']=102;
        }else{
            $msg['code']=200;
            $msg['data']['content']=htmlspecialchars_decode($data['function_name']);
            $msg['data']['title']=$data['description'];
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
  
    /**
     *  我的留言
     */
    public function Mymessage(){
        if (!$this->user_openid or !$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss mobile params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        
        $page=I('page')?I('page'):1;
        $lines=I('lines')?I('lines'):100;
        //实例化各种表
        $admininfo=$this->getMerchant($this->ukey);
        $feedbackDB=M('feedback', $admininfo['pre_table']);
//         $memberDB=M('mem',$admininfo['pre_table']);
        
//         //先获取用户信息
//         $mem_data=$memberDB->where(array('openid'=>array('eq',$this->user_openid)))->find();
        
//         if($mem_data == ''){
//             returnjson(array('code'=>2000), $this->returnstyle, $this->callback);
//         }
        
        // 如果有一天需要区分留言和意见反馈 , 只需加上,'is_message'=>2即可
        
        //用户信息存在获取留言信息
        $count=$feedbackDB->where(array('openid'=>$this->user_openid,'status'=>1))->order('createtime desc')->count();

        if(!$count){
            $msg['code']=200;
            $msg['data']['data']=array();
            returnjson($msg, $this->returnstyle, $this->callback);
        }
        
        $num=ceil($count/$lines);
        $page=$page<$num?$page:$num;
        $end=($page-1)*$lines;
        
        $feedback_data=$feedbackDB->where(array('openid'=>$this->user_openid,'status'=>1))->order('createtime desc')->limit($end,$lines)->select();
        
        if(count($feedback_data) == 1){
            
            $where['gid']=$feedback_data[0]['id'];
//             $where['is_message']=2;
            $where['status']=1;
            $service_data=$feedbackDB->where($where)->order('createtime desc')->select();
            
            $feedback_data[0]['data']=$service_data;
            
            $data=$feedback_data;
            
        }else if(count($feedback_data) > 1){
            
            foreach($feedback_data as $k=>$v){
                $feedback_id[]=$v['id'];
            }
            
            $service_id=implode(',', $feedback_id);
            
            $where['gid']=array('in',$service_id);
            $where['status']=array('eq',1);
//             $where['is_message']=array('eq',2);
            $where['_logic']='and';
            $service_arr=$feedbackDB->where($where)->order('createtime desc')->select();
            
            foreach($service_arr as $k=>$v){
                $service_data[$v['gid']][]=$v;
            }
            foreach($service_data as $k=>$v){
                $gid[]=$k;
            }

            $j=0;
            foreach($gid as $key=>$val){
                foreach($feedback_data as $k=>$v){
                    if($v['id'] == $val ){
                        $data[$j]=$v;
                        $data[$j]['data']=$service_data[$v['id']];
                        $j++;
                    }
                }
            }

            foreach($feedback_data as $k=>$v){
                if(!in_array($v['id'], $gid)){
                    $data[$j]=$v;
                    $data[$j]['data']=$service_data[$v['id']];
                    $j++;
                }
            }
            
        }else{
            $data=array();
        }
        
        $msg['code']=200;
        $msg['data']['data']=$data;
        $msg['data']['num']=$num;
        $msg['data']['count']=$count;
        $msg['data']['page']=$page;
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    

    /**
     * 联系我们页面接口
     */
    public function contactus()
    {
        $key_admin=I('key_admin');
        $admin=$this->getMerchant($key_admin);
        $feedback=$this->GetOneAmindefault($admin['pre_table'], $key_admin, 'feedback');
        $wechatservice=$this->GetOneAmindefault($admin['pre_table'], $key_admin, 'wechatservice');
        $phoneservice=$this->GetOneAmindefault($admin['pre_table'], $key_admin, 'phoneservice');
        $servicedescription=$this->GetOneAmindefault($admin['pre_table'], $key_admin, 'servicedescription');
    
        $feedback=json_decode($feedback['function_name'], true);
        $wechatservice=json_decode($wechatservice['function_name'], true);
        $phoneservice=json_decode($phoneservice['function_name'], true);
        $servicedescription=json_decode($servicedescription['function_name'], true);
    
        if (true == $wechatservice['wechatservice']['enable']){
            $wechatservice['wechatservice']=$wechatservice['wechatservice'];
        }else{
            $wechatservice['wechatservice']=array('enable'=>false, 'description'=>'');
        }
    
        if (true == $phoneservice['phoneservice']['enable']){
            $phoneservice['phoneservice']=$phoneservice['phoneservice'];
        }else{
            $phoneservice['phoneservice']=array('enable'=>false, 'server'=>array());
        }
    
        if (true == $servicedescription['servicedescription']['enable']){
            $servicedescription['servicedescription']['enable']=true;
            $servicedescription['servicedescription']['description']=$servicedescription['servicedescription']['description'];
        }else{
            $servicedescription['servicedescription']=array('enable'=>false,'description'=>'');
        }
        $array=array(
            'feedback'=>$feedback['feedback'],
            'wechatservice'=>$wechatservice['wechatservice'],
            'phoneservice'=>$phoneservice['phoneservice'],
            'servicedescription'=>$servicedescription['servicedescription']
        );
        $data['code']=200;
        $data['data']=$array;
        returnjson($data, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 会员卡样
     */
    public function cardimg()
    {
        $params['key_admin']=$this->ukey;
        $params['openid']=I('openid');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $admin=$this->getMerchant($params['key_admin']);
        
        //检查商场是否设置了卡样，否则返回106，前端调用一个默认卡样图片
        $dbs=M('member_code','total_');
//         $find=$dbs->where(array('admin_id'=>$admin['id'],'tid'=>5))->find();
//         if (null == $find){
//             returnjson(array('code'=>106, 'data'=>'default'), $this->returnstyle, $this->callback);
//         }
//         $find1=json_decode($find['content'], true);
//         $cardtype=json_decode($find['url'], true);
        $find=$dbs->where(array('admin_id'=>$admin['id']))->select();
        if (null == $find){
            returnjson(array('code'=>106, 'data'=>'default'), $this->returnstyle, $this->callback);
        }
        foreach($find as $k=>$v){
            $cardtype[$v['code']]['name']=$v['name'];
            $cardtype[$v['code']]['url']=$v['imgurl'];
        }
        //查询openid是否存在
        $db=M('mem',$admin['pre_table']);
        $seluser=$db->where(array('openid'=>$params['openid']))->find();
        if (null == $seluser){
            $img=$cardtype['default']['url'];
            returnjson(array('code'=>106, 'data'=>array('img'=>$img,'cardname'=>$cardtype['default']['name'])), $this->returnstyle, $this->callback);
        }
        
        //调用CRM接口获取会员信息
        $userinfo=$this->getMemberByCard($seluser['cardno']);
        if ($userinfo['code']==200){

            if (isset($userinfo['data']['cardtype'])){
//                 $cardgrade=$userinfo['data']['cardtype'];
//                 $grade=array_search($cardgrade, $find1);
                $img=$cardtype[$userinfo['data']['cardtype']]['url'];
                $cardname = $cardtype[$userinfo['data']['cardtype']]['name'];
                $msg=array('code'=>200,'data'=>array('img'=>$img,'cardname'=>$cardname));
            }else{
                $img=$cardtype['default']['url'];
                $cardname=$cardtype['default']['name'];
                $msg=array('code'=>106,'data'=>array('img'=>$img,'cardname'=>$cardname));
            }
            returnjson($msg, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
        
	}

    /**
     * 获取首页icon菜单接口
     */
    public function getSquaredMenuList()
    {
        $key_admin=I('key_admin');
        $catalog_id=I('catalog_id');
        $version_id=I('version_id');
        if (!$key_admin){ returnjson(array('code'=>1001), $this->returnstyle, $this->callback); }
        $admin=$this->getMerchant($key_admin);
        $dbm=M();
        $c=$dbm->execute('SHOW TABLES like "'.$admin['pre_table'].'squared"');
        if (1 !==$c){
            $datas=array();
        }else{
            $catalog_id = empty($catalog_id) ? 1 : $catalog_id;
            $db=M('squared', $admin['pre_table']);

            $where = array('catalog_id'=>$catalog_id, 'isopenedactivity' => 1);
//            $where['catalog_id']=array('eq',$catalog_id);
//            $where['isopenedactivity'] = array('eq', 1);
            $datas=$db->where($where)->order(array('order','id'))->select();
            foreach ($datas as $key => $val){
                if($val['url']==''){
                    $columnid[]=$val['column_id'];
                }else{
                    $datas[$key]['url']=html_entity_decode($val['url']);
                }
            }
            if($columnid){
                $column_str=implode(',', $columnid);
                if($version_id){
                    $version_arr['version_id']=$version_id;
                }else{
                    /*   获取绑定的版本id   */
                    $db=M('version_url','total_');
                    $wheres['adminid']=array('eq',$admin['id']);
                    $wheres['type_id']=array('eq',$catalog_id);
                    $wheres['_logic']='and';
                    $version_arr=$db->where($wheres)->find();
                    /*   获取绑定的版本id   */
                }
                $version_column_db=M('version_column','total_');
                $sub_column_db=M('sub_column','total_');
                $map['status']=array('eq',1);
                $map['total_version_column.catalog_id']=array('eq',$catalog_id);
                $map['version_id']=array('eq',$version_arr['version_id']);
                $map['column_id']=array('in',$column_str);
                $map['_logic']='and';
                $arr=$sub_column_db->join('total_version_column on total_sub_column.id=total_version_column.column_id')->where($map)->field('total_sub_column.id,total_version_column.url')->select();

                foreach($arr as $key=>$val){
                    $column_arr[$val['id']]=$val['url'];
                }

                foreach($datas as $k=>$v){
                    if($v['url']==''){
                        if($column_arr[$v['column_id']]){
                            $datas[$k]['url']=html_entity_decode($column_arr[$v['column_id']]);
                        }else{
                            $datas[$k]['url']='';
                        }
                    }
                }
            }
        }
        $defaultdb=M('default', $admin['pre_table']);
        $find=$defaultdb->where(array('customer_name'=>'squaredlist'))->find();
        $data['data']=$datas;
        $data['type']=(int)$find['function_name'];
        $msg['code']=200;
        $msg['data']=$data;
        returnjson($msg, $this->returnstyle, $this->callback);
    }


    /**
     * 根据前端请求参数,查询参数值,查询省份,城市,区数据
     */
    public function getarea()
    {
        $provnice=I('province');
        $city=I('city');
        $key_admin = I('key_admin');
        if('e52693d642d3d9f61a7cf90990f38d6a' == $key_admin){
            $data['id'] = $provnice?$provnice:100000;
            $data['key_admin'] = $key_admin;
            $mer_re = $this->getMerchant($key_admin);
            $data['sign_key'] = $mer_re['signkey'];
            $data['sign'] = sign($data);
            writeOperationLog(array('city_data' => $data), 'jaleel_logs');
            unset($data['sign_key']);
            $url = C('DOMAIN') . '/CrmService/OutputApi/Index/Provincialcity';
            $curl_re = http($url, $data, 'post');
            $return_data = json_decode($curl_re,true);
//             print_r($return_data);die;
            if($return_data['code'] == 200){
                foreach($return_data['data'] as $k=>$v){
                    $return_data['data'][$k]['code']=$v['id'];
                }
//                 print_r($return_data);die;
                $msg['code'] = 200;
                $msg['data'] = $return_data['data'];
             }else{
                 $msg['code'] = 102;
             }
            
            returnjson($msg, $this->returnstyle, $this->callback);
        }
        
        
        
        if ('c24bb91b6766b3c9c430c776cee9e7cf'!=I('key_admin')){
            if ($provnice=='' && $city ==''){//查询所有省的数据
                $sel=$this->getstaticarea('province', 'code,name', '', '');
            }elseif ($provnice != ''){
                $sel=$this->getstaticarea('city', 'code,name', 'provincecode', $provnice);
            }elseif ($city != ''){
                $sel=$this->getstaticarea('area', 'code,name', 'citycode', $city);
            }
            if ( empty($sel) ){
                $data['code']=102;
            }else{
                $data['code']=200;
                $data['data']=$sel;
            }
        }else {
            $data['code']=200;
            if ($provnice=='' && $city ==''){//查询所有省的数据
                $data['data']=C('TAIGULI');
            }elseif ($provnice != ''){
                $data['data']=C('DISTRICT.'.$provnice);
            }elseif ($city != ''){
                $data['data']=C('ROAD.'.$city);
            }
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }



    /**
     * 获取地区
     * @param unknown $table
     * @param unknown $field
     * @param unknown $wherefield
     * @param unknown $code
     */
    protected function getstaticarea($table,$field,$wherefield,$code)
    {
        $area=$this->redis->get($table.$code);
        if ($area){
            return json_decode($area);
        }else {
            $db=M($table,'total_');
            if ($wherefield){
                $where=array($wherefield=>$code);
            }else {
                $where=array(1=>1);
            }
            $sel=$db->field($field)->where(array($wherefield=>$code))->select();
            $this->redis->set('province:city:area:'.$table.':'.$code, json_encode($sel));
            return $sel;
        }
    }



    /*
     * 获取意见反馈列表
     */
    public function GetFeedbackReplay()
    {
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($params['key_admin']);
        $userinfo=$this->getUserCardByOpenId($admininfo['pre_table'], $params['openid']);
        if (is_array($userinfo)){
            $db=M('feedback', $admininfo['pre_table']);
            $findid=$db->field('id')->where(array('pid'=>0,'gid'=>0,'mem_id'=>$userinfo['id']))->limit(10)->order('id desc')->select();
            if ($findid != null){
                foreach ($findid as $key => $value){
                    $sel[]=$db->field('id,openid,content,createtime')->where(array('gid'=>$value['id'],'id'=>$value['id'],'_logic'=>'or'))->select();
                }
                returnjson(array('code'=>200,'data'=>$sel), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>2000), $this->returnstyle, $this->callback);
        }
    }
    
    
    
    
    //获取商户详细信息
    public function getmemberones(){
        $params['key_admin']=I('key_admin');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $admin_arr=$this->getMerchant($this->ukey);
            $msg['code']=200;
            $arr['pre_table']=$admin_arr['pre_table'];
            $arr['describe']=$admin_arr['describe'];
            $arr['sign_key']=$admin_arr['signkey'];
            $build_db=M('buildid','total_');
            $build_arr=$build_db->field('name,buildid')->where(array('adminid'=>$admin_arr['id']))->select();
            $arr['build_list']=$build_arr;
            $result = $this->GetOneAmindefault($admin_arr['pre_table'], $this->ukey, 'parkingfindcar');
            if($result){
                $arr['map_url']=$result['function_name'];
            }else{
                $arr['map_url']='';
            }
            $msg['data']=$arr;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查询用户的openid和mac地址
     */
    public function getMac()
    {
        $date = I('date');
        if ($this->ukey == 'e4273d13a384168962ee93a953b58ffd') {
            $url = 'http://fw.joycity.mobi/getMac.php';
        }

        if (isset($date)) {
            $param['date'] = $date;
        } else {
            $param = array();
        }

        $result = http($url, $param);

        $re_arr = json_decode($result, true);

        $data = array('code' => $re_arr['code'], 'msg' => $re_arr['msg'], 'data' => $re_arr['data']);

        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 验证用户是否被拉入黑名单接口
     */
    public function checkBlackUser()
    {
        $merchant=$this->getMerchant($this->ukey);
        $obj = M('black_users', $merchant['pre_table']);
        $old_data = $obj->find(1);

        if (is_array($old_data)) {
            if (date('w') == 1) {
                $old = date('Ymd', strtotime($old_data['create_time']));
                $now = date('Ymd');

                if ($old < $now) {
                    $this->updateBlackUsers();
                }
            }
        } else {
            $this->updateBlackUsers();
        }

        $result = $obj->where(array('openid' => $this->user_openid))->find();

        if (is_array($result)) {
            $data = array('code' => 1111, 'msg' => 'black user');
        } else {
            $data = array('code' => 200, 'msg' => 'success');
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    protected function updateBlackUsers()
    {
        $merchant=$this->getMerchant($this->ukey);

        if ($this->ukey == 'e4273d13a384168962ee93a953b58ffd') {
            $url = 'http://120.132.32.132:8080/httpServer1.1/dayuechengServer';
            $params['build_id'] = '860100010020300001';
//            $params['day_id'] = date('Ymd');
            $params['day_id'] = 20170409;
            $result = http($url, $params);

            $arr = json_decode($result, true);
            if ($arr['code'] == 200) {
                if (count($arr['data']) > 0) {
                    $obj = M('black_users', $merchant['pre_table']);
                    $obj->execute('truncate ' . $merchant['pre_table'] . 'black_users');
                    $re = $obj->addAll($arr['data']);
                }
                return true;
            }
            return false;
        }
    }
    
	protected function getSign($subParams,$admininfo) {
        $subParams['sign_key']=$admininfo['signkey'];
		$sign = sign($subParams);
		return $sign;
	}

    /**
     * 查询商户会员等级相关信息（对外接口）
     */
    public function memberGrade()
    {
        $out_sign = I('sign');
        $merchant = $this->getMerchant($this->ukey);

        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $merchant['signkey'];
        $sign = sign($data);

        if (!$out_sign) {
            $data = array('code' => '1030');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        if($sign != $out_sign) {
            $data = array('code' => '1002');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $db=M('member_code','total_');
	    $where['admin_id']=array('eq',$merchant['id']);
	    $arr=$db->where($where)->order('sort asc')->select();
	    if($arr){
	        foreach($arr as $k=>$v){
	            $page_info[$v['code']]=$v['name'];
	        }
	    }else{
	        $page_info=(object)array();
	    }
	    $return_data = array('code' => '200', 'msg' => 'success', 'data' => $page_info);
        returnjson($return_data, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 获取首页轮播接口
     */
    public function member_carousel(){
        
        $merchant = $this->getMerchant($this->ukey);
        
        $where['status']=array('eq',1);
        $where['admin_id']=array('eq',$merchant['id']);
        
        $db=M('banner','total_');
        
        $arr = $db->where($where)->order('sort desc')->select();
        
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 　升降级接口
     */
    public function lift_member(){
    
        $params['status']=I('status');
    
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
    
        // 查询商户配置
        $merchant = $this->getMerchant($this->ukey);
        if (!$merchant) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
    
        // 查询会员信息
        $uinfo = $this->getUserCardByOpenId($merchant['pre_table'], $this->user_openid);
        if ($uinfo == '2000') {
            $data = array('code' => '2000', 'msg' => 'sorry,u are not our member,please go to register!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
    
        $params['card']=$uinfo['cardno'];
        $params['key_admin']=$this->ukey;
        $params['sign_key']=$merchant['signkey'];
        $params['sign'] = sign($params);
    
        unset($params['sign_key']);
    
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/LiftMember';
        $curl_re = http($url, $params, 'post');
        writeOperationLog(array('register member' => $curl_re), 'jaleel_logs');
        $return = json_decode($curl_re, true);
    
        if($return['code'] == 200){
            $msg['code'] = 200;
        }else{
            $msg['code']=$return['code'];
        }
    
        returnjson($msg, $this->returnstyle, $this->callback);
    
    }
    
      
    /**
     * 扫码积分
     */
    public function Scan_code(){
        set_time_limit(0);
        $params['openid'] = I('openid');
        $params['orderno'] = I('orderno');
        $params['status'] = I('status');
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $merchant = $this->getMerchant($this->ukey);
        
        if($params['status']  == 'wechat'){//微信
            $sign_data2['openid'] = $params['openid'];
            $sign_data2['sign_key'] = $merchant['signkey'];
            $sign_data2['key_admin'] = $this->ukey;
            $sign_data2['sign'] = sign($sign_data2);
            unset($sign_data2['sign_key']);
            
            $openid_url = C('DOMAIN') . '/CrmService/OutputApi/Index/getInfoByOpenid';
            $return_msg2 = http($openid_url, $sign_data2, 'post');
            $openid_return = json_decode($return_msg2,true);
            if($openid_return['code'] != 200){
                returnjson(array('code'=>$openid_return['code']), $this->returnstyle, $this->callback);
            }
            
            $sign_data['orderno'] = $params['orderno'];
            $sign_data['cardno'] = $openid_return['data']['cardno'];
            $sign_data['sign_key'] = $merchant['signkey'];
            $sign_data['key_admin'] = $this->ukey;
            $sign_data['sign'] = sign($sign_data);
            $sign_data['orderno'] = html_entity_decode($params['orderno']);
            unset($sign_data['sign_key']);
            $url = C('DOMAIN') . '/CrmService/OutputApi/Index/WechatScanScore';
            $return_msg = http($url, $sign_data, 'post');
            if(!is_json($return_msg)){
                returnjson(array('code'=>104),$this->returnstyle,$this->callback);
            }
        }else{//支付宝
            
            //注:支付宝接口返回接口最好和微信返回一样
            returnjson(array('code'=>101,'data'=>'暂无此接口'), $this->returnstyle, $this->callback);
        }
        
        $return_arr = json_decode($return_msg,true);
        
        if($return_arr['code'] != 200){
            returnjson(array('code'=>1811,'data'=>$return_arr['data']), $this->returnstyle, $this->callback);
        }
        
        $data['mobile'] = $return_arr['data']['mobile'];
        $data['name'] = $return_arr['data']['username'];
        $data['cardno'] = $return_arr['data']['cardno'];
        $data['amount'] = $return_arr['data']['amount'];
        $data['orderno'] = $return_arr['data']['orderno'];
        $data['tradeno'] = $return_arr['data']['tradeno'];
        $data['score'] = $return_arr['data']['score'];
        
        returnjson(array('code'=>200,'data'=>$data), $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 积分支付
     */
    public function offsetscore(){
        $params['openid'] = I('openid');
//         $params['key_admin'] = I('key_admin');
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        $merchant = $this->getMerchant($this->ukey);
        
        $sign_data2['openid'] = $params['openid'];
        $sign_data2['sign_key'] = $merchant['signkey'];
        $sign_data2['key_admin'] = $this->ukey;
        $sign_data2['sign'] = sign($sign_data2);
        unset($sign_data2['sign_key']);
        
        $openid_url = C('DOMAIN') . '/CrmService/OutputApi/Index/getInfoByOpenid';
        $return_msg2 = http($openid_url, $sign_data2, 'post');
        $openid_return = json_decode($return_msg2,true);
        if($openid_return['code'] != 200){
            returnjson(array('code'=>$openid_return['code']), $this->returnstyle, $this->callback);
        }
        
        $param['cardno'] = $openid_return['data']['cardno'];
        $param['key_admin'] = $this->ukey;
        $param['sign_key'] = $merchant['signkey'];
        $param['sign'] = sign($param);
        unset($param['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/offset_score';
        $return_msg = http($url, $param, 'post');

        $return_arr = json_decode($return_msg,true);
        
        if($return_arr['code'] != 200){
            returnjson(array('code'=>1811,'data'=>$return_arr['data']), $this->returnstyle, $this->callback);
        }
        
        if($return_arr['data']['level'] == '72'){
            returnjson(array('code'=>1811,'data'=>'预享卡不支持此功能'), $this->returnstyle, $this->callback);
        }
        
        returnjson(array('code'=>200,'data'=>$return_arr['data']), $this->returnstyle, $this->callback);
    }



    public function getCardList()
    {
        $adminInfo = $this->getMerchant($this->ukey);
        $data = RedisService::connectredis()->get('keyadmin:card:type:'. $this->ukey);
        if (!$data){
            $db = M('total_member_code');
            $data = $db->where(['admin_id'=>$adminInfo['id']])->select();
            if ($data){
                RedisService::connectredis()->set('keyadmin:card:type:'. $this->ukey, json_encode($data), ['ex'=>86400]);
            }
        }else{
            $data = json_decode($data, true);
        }

        if ($data){
            returnjson(['code'=>200,'data'=>$data], $this->returnstyle, $this->callback);
        }else{
            returnjson(['code'=>102,'data'=>$data], $this->returnstyle, $this->callback);
        }
    }



    //最新发送验证码接口，旧接口最晚于2018-1-5全部删除

    /**
     * 发送验证码短信的前置条件，图形验证码
     * //http://document.thinkphp.cn/manual_3_2.html#verify
     */
    public function getverify()
    {
        $key_admin = I('get.key_admin');

        $adminInfo = $this->getMerchant($key_admin);

        $config =    [
            'expire'=>120,
            'setKey'=>$key_admin,
            'imageW'=>212,
            'imageH'=>80,
            'length'=>4,
            'fontSize'=>31,
            'useCurve'=>false,
            'codeSet'=>'0123456789'
        ];
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }


    /**
     * 发送验证码
     */
    public function sendverifycode()
    {
        $params['mobile'] = I('mobile');
        $params['imgverify'] = I('imgverify');
        $params['key_admin'] = I('key_admin');


        /*限制 start */
        $nolimitarr = array('15650782278','18515906013','18610818845','18612814568');
        $limitnumkey  = "phone:".date('Y-m-d',time()).":limit:".$params['mobile'];
        $limitnum = $this->redis->get($limitnumkey);
        $limitnum = empty($limitnum)?1:($limitnum+1);
        if($limitnum>6 && !in_array($params['mobile'],$nolimitarr)){
            returnjson(array('code' => '1082', 'msg' => '您的短信次数已经用完了，明天再来试吧！'), $this->returnstyle, $this->callback);
        }

        $limitnumkey1 = "phone:send:limit:".$params['mobile'];
        $limitnum1 = $this->redis->get($limitnumkey1);
        if(!empty($limitnum1)){
            returnjson(array('code' => '1082', 'msg' => '您的短信发送过于频繁，稍后再试吧！'), $this->returnstyle, $this->callback);
        }
        /*限制 end */

        if (in_array(false, $params)) {
            $data = array('code' => '1030');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $ver=new \Think\Verify();
        $isverify=$ver->check($params['imgverify']);
        if ($isverify != true){
            returnjson(['code'=>1036], $this->returnstyle, $this->callback);
        }



        $adminInfo = $this->getMerchant($params['key_admin']);
        $sendmsg = $this->GetOneAmindefault($adminInfo['pre_table'], $params['key_admin'], 'sendmsg');
        $sendmsg = $sendmsg['function_name'];
        $send = SendMessageService::sendMessages($adminInfo, $sendmsg, $params);
        if (is_array($send)){
            /*限制 */
            $this->redis->setex($limitnumkey, 86400,$limitnum);
            $this->redis->setex($limitnumkey1, 60,1);
            /*限制 */
            returnjson($send, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }




}
?>
