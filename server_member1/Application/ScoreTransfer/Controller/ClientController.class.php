<?php
/**
 * 积分转赠C端
 */
namespace ScoreTransfer\Controller;

use Common\Controller\ErrorcodeController;
class ClientController extends ErrorcodeController {
    
    /**
     * 浮层确认框
     */
    public function confim(){
        $params['key_admin']=$this->ukey;
        $params['scorenumber']=(int)I('number');
        $params['phone']=I('phone');
        $params['openid']=$this->user_openid;
        $params['shareuser']=I('shareuser');//微信用户名
        $params['shareuserheaderimg']=I('shareuserheaderimg');
        
        //参数不完整
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);exit;
        }
        
        //如果积分数不是整数
        if (!is_int($params['scorenumber'])){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }
        
        //获取商户信息
        $admininfo=$this->getMerchant($params['key_admin']);
        
        $mixscore=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'mixscore');
        
        $maxscore=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'maxscore');
        
        //判断传入的积分数是否符合条件
        $mixscore=(int)$mixscore['function_name'];
        $maxscore=(int)$maxscore['function_name'];
        if ($params['scorenumber'] > $maxscore || $params['scorenumber'] < $mixscore){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }
        
        //根据openid查询用户的信息
        $userinfo=$this->getUserByOpenid($params['openid'], $admininfo['pre_table']);
        
        //判断能否查到用户信息
        if (false !== $userinfo){
            $data['card']=$userinfo['cardno'];
            $data['key_admin']=$params['key_admin'];
            $data['sign_key']=$admininfo['signkey'];
            $data['openid']=$params['openid'];
            $data['sign']=sign($data);
            unset($data['sign_key']);
            //请求crm，得到手机号后验证手机号是否和传入的一致
            $url=C('DOMAIN').'/CrmService/OutputApi/Index/getuserinfobycard';
            $crmuser=http($url, $data);
            if ( is_json($crmuser) ){
                $array=json_decode($crmuser, true);
                
                //判断CRM查询结果是否正确
                if ($array['code']==200){
                    if ($array['data']['phone'] == $params['phone'] || $array['data']['mobile'] == $params['phone']){
                        //验证完成，扣除积分
                        $cs['key_admin']=$params['key_admin'];
                        $cs['cardno']=$array['data']['cardno'];
                        $cs['scoreno']=$params['scorenumber'];
                        $cs['why']='积分转赠扣除积分';
                        $cs['type']=14;//积分转赠
                        $cs['sign_key']=$admininfo['signkey'];
                        $cs['sign']=sign($cs);
                        unset($cs['sign_key']);
                        $url=C('DOMAIN').'/CrmService/OutputApi/Index/cutScore';
                        $cutscore=http($url, $cs);
                        if (is_json($cutscore)){
                            $cutarr=json_decode($cutscore, true);
                            if ($cutarr['code']==200){
                                //保存积分转赠到数据库
                                $urlexpirydate=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'urlexpirydate');
                                $urlexpirydate=(int)$urlexpirydate['function_name'];//分享的链接有效期
                                $duetime=strtotime("+".$urlexpirydate." hours");
                                $urlstr='guoqitime'.$duetime.'scorenumber'.$params['scorenumber'].time();
                                $urlstr=md5($urlstr);
                                
                                $d['scorenumber']=$params['scorenumber'];
                                $d['sharetime']=time();
                                $d['shareusercard']=$array['data']['cardno'];
                                $d['sharewechatname']=$params['shareuser'];
                                $d['sharerheaderimg']=$params['shareuserheaderimg'];
                                $d['sharermobile']=$array['data']['mobile'] ? $array['data']['mobile'] : $array['data']['phone'];
                                $d['duetime']=$duetime;
                                
                                $d['receiveusercard']='';
                                $d['receivewechatuser']='';
                                $d['receivermobile']='';
                                $d['receivetime']=0;
                                $d['urlstr']=$urlstr;
                                $d['isreceive']=0;
                                
                                $db=M('scoretransfer', $admininfo['pre_table']);
                                $add=$db->add($d);
                                if ($add){
                                    $msg=array(
                                        'code'=>200,
                                        'data'=>array(
                                            'urlstr'=>$urlstr,
                                            'scorenumber'=>(int)$params['scorenumber'],
                                            'headerimg'=>$params['shareuserheaderimg'],
                                            'sharewechatname'=>$params['shareuser'],
                                        )
                                    );
                                    returnjson($msg, $this->returnstyle, $this->callback);
                                }else {//数据库保存失败，积分加回去
                                    $as['key_admin']=$params['key_admin'];
                                    $as['cardno']=$array['data']['cardno'];
                                    $as['scoreno']=$params['scorenumber'];
                                    $as['why']='积分转赠失败返还积分';
                                    $as['scorecode']=date('Y-m-d');//积分转赠
                                    $as['sign_key']=$admininfo['signkey'];
                                    $as['membername']=$array['data']['user'];
                                    $as['sign']=sign($as);
                                    unset($as['sign_key']);
                                    $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
                                    $cutscore=http($url, $as);
                                    returnjson(array('code'=>104,'data'=>4), $this->returnstyle, $this->callback);
                                }
                                
                            }else{
                                returnjson(array('code'=>$cutarr['code'],'data'=>'cm'), $this->returnstyle, $this->callback);
                            }
                        }else{
                            returnjson(array('code'=>104,'data'=>3), $this->returnstyle, $this->callback);
                        }
                    }else{
                        returnjson(array('code'=>1017), $this->returnstyle, $this->callback);
                    }
                }else {
                    returnjson(array('code'=>104, 'data'=>1), $this->returnstyle, $this->callback);
                }
            }else {
                returnjson(array('code'=>104, 'data'=>2), $this->returnstyle, $this->callback);
            }
            
        }else {
            returnjson(array('code'=>103), $this->returnstyle, $this->callback);
        }
        
        
    }
    
    
    
    
    /**
     * 领取页面接口
     * 1、如果过期，需要将积分退还，这里就直接执行了，不走定时任务
     * 2、 自己打开，需要隐藏按钮
     * 
     */
    public function transferscorepage()
    {
        $params['urlstr']=I('urlstr');
        $params['openid']=$this->user_openid;
        $params['key_admin']=$this->ukey;
        
        //验证参数是否完整
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        //获取商户信息
        $admininfo=$this->getMerchant($params['key_admin']);
        
        $db=M('scoretransfer', $admininfo['pre_table']);
        $find=$db->where(array('urlstr'=>$params['urlstr']))->find();
        //如果库里没有这条信息记录
        if (null == $find){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        
        $urlexpirydate=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'urlexpirydate');
        
        $re=array(
            'isclick'=>false,
            'isself'=>false,
            'isduetime'=>false,
            'scorenumber'=>(int)$find['scorenumber'],
            'sharewechatname'=>$find['sharewechatname'],
            'sharerheaderimg'=>$find['sharerheaderimg'],
            'sharetime'=>$find['sharetime'],
            'transferdue'=>$urlexpirydate['function_name']
        );
        //会员信息
        $userinfo=$this->getUserByOpenid($params['openid'], $admininfo['pre_table'], $re);
        
        $isself=false;//默认不是本人
        //如果没有过期
        if ($find['duetime'] > time()){
            
            
            
            //判断是否已经被领取，如果没有被领取，则将积分返还给分享者
            if ($find['isreceive'] == 1){//已领取
                $code=1024;
                $receiverwechatuser=$find['receivewechatuser'];
                $receiverheaderimg=$find['receiverheaderimg'];
            }else{//未领取
                $code=1025;
                $receiverwechatuser=null;
                $receiverheaderimg=null;
            }
            
            //判断是否是自己打开的页面
            if ($userinfo['cardno'] == $find['shareusercard']){//如果打开页面的会员的卡号和分享人的卡号一致}
                $isclick=false;
                $isself=true;
            }else if ( $code == 1024){//如果已被领取
                $isclick=false;
                $receiverwechatuser=$find['receivewechatuser'];
                $receiverheaderimg=$find['receiverheaderimg'];
            }else{
                $isclick=true;
            }
            
            $arr=array(
                'code'=>$code,
                'data'=>array(
                    'isclick'=>$isclick,
                    'isself'=>$isself,
                    'isduetime'=>false,
                    'scorenumber'=>(int)$find['scorenumber'],
                    'sharewechatname'=>$find['sharewechatname'],
                    'sharerheaderimg'=>$find['sharerheaderimg'],
                    'receivewechatuser'=>$receiverwechatuser,
                    'receiverheaderimg'=>$receiverheaderimg,
                    'sharetime'=>$find['sharetime'],
                    'transferdue'=>$urlexpirydate['function_name']
                ),
            );
        }else {//如果过期，最重要的是要将没有领取的积分退回
            //判断是否已经被领取，如果没有被领取，则将积分返还给分享者
            if ($find['isreceive'] == 1){//已领取
                $code=1026;
                $receiverwechatuser=$find['receivewechatuser'];
                $receiverheaderimg=$find['receiverheaderimg'];
            }else{//未领取
                $code=1026;
                $receiverwechatuser=null;
                $receiverheaderimg=null;
                
                //将扣除的积分退回，可减少定时任务的工作量
                $as['key_admin']=$params['key_admin'];
                $as['cardno']=$find['shareusercard'];
                $as['scoreno']=$find['scorenumber'];
                $as['why']='积分转赠失败返还积分';
                $as['scorecode']=date('Y-m-d');//积分转赠
                $as['sign_key']=$admininfo['signkey'];
                $as['membername']='name';
                $as['sign']=sign($as);
                unset($as['sign_key']);
                $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
                $cutscore=http($url, $as);
                $array=json_decode($cutscore, true);
                if ($array['code']==200){
                    $change=$db->where(array('urlstr'=>$params['urlstr']))->save(array('isreceive'=>2));
                    if (false === $change){//再来一次
                        $change=$db->where(array('urlstr'=>$params['urlstr']))->save(array('isreceive'=>2));
                    }
                }else {
                    //积分增加没有成功，定时任务里面再执行一次
                    
                }
            }
            
            $arr=array(
                'code'=>$code,
                'data'=>array(
                    'isclick'=>false,
                    'isself'=>$isself,
                    'isduetime'=>true,
                    'scorenumber'=>(int)$find['scorenumber'],
                    'sharewechatname'=>$find['sharewechatname'],
                    'sharerheaderimg'=>$find['sharerheaderimg'],
                    'receivewechatuser'=>$receiverwechatuser,
                    'receiverheaderimg'=>$receiverheaderimg,
                    'sharetime'=>$find['sharetime'],
                    'transferdue'=>$urlexpirydate['function_name']
                ),
            );
            
            
        }
        
        returnjson($arr, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 领取积分接口
     * 1、判断openid是否是会员，判断是否是会员接口返回200是成功
     * 2、获取卡号后添加积分
     * 3、改变数据表
     */
    public function receivescore()
    {
        $params['urlstr']=I('urlstr');
        $params['openid']=I('openid');
        $params['key_admin']=I('key_admin');
        $params['receivewechatuser']=I('wechatname');
        $params['receiverheaderimg']=I('wechatimg');
        
        //参数不完整
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        //获取商户信息
        $admininfo=$this->getMerchant($params['key_admin']);
        
        $db=M('scoretransfer', $admininfo['pre_table']);

        //根据接收条件查询是否有存在
        $transfer=$db->where(array('urlstr'=>$params['urlstr']))->find();
        
        //查询不到结果，返回bad guys
        if (false == $transfer){
            returnjson(array('code'=>1017), $this->returnstyle, $this->callback);
        }
        
        
        if ($transfer['isreceive'] == 2){
            returnjson(array('code'=>1026,'data'=>1), $this->returnstyle, $this->callback);
        }
        
        //获取会员信息
        $userinfo=$this->getUserByOpenid($params['openid'], $admininfo['pre_table']);
        
        //已被领取并且卡号不是自己
        if ($transfer['isreceive'] == 1 && $transfer['receiveusercard'] != $userinfo['cardno']){
            returnjson(array('code'=>1028), $this->returnstyle, $this->callback);
        }
        
        //已被领取并且卡号是自己
        if ($transfer['isreceive'] == 1 && $transfer['receiveusercard'] == $userinfo['cardno']){
            returnjson(array('code'=>1032), $this->returnstyle, $this->callback);
        }
        
        
        
        
        //如果当前时间已经过期
        if ((int)$transfer['duetime'] < time() && $transfer['isreceive'] == 0){
            
            //将扣除的积分退回，可减少定时任务的工作量
            $as['key_admin']=$params['key_admin'];
            $as['cardno']=$transfer['shareusercard'];
            $as['scoreno']=$transfer['scorenumber'];
            $as['why']='积分转赠失败返还积分';
            $as['scorecode']=date('Y-m-d');//积分转赠
            $as['sign_key']=$admininfo['signkey'];
            $as['membername']='name';
            $as['sign']=sign($as);
            unset($as['sign_key']);
            $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
            $cutscore=http($url, $as);
            $array=json_decode($cutscore, true);
            if ($array['code']==200){
                $change=$db->where(array('urlstr'=>$params['urlstr']))->save(array('isreceive'=>2));
                if (false === $change){//再来一次
                    $change=$db->where(array('urlstr'=>$params['urlstr']))->save(array('isreceive'=>2));
                }
            }else {
                //积分增加没有成功，定时任务里面再执行一次
                returnjson(array('code'=>$array['code'],'data'=>2), $this->returnstyle, $this->callback);
            }
            returnjson(array('code'=>1026,'data'=>2), $this->returnstyle, $this->callback);
        }else {
            
            
            //获取传入的openid上次领取的时间
            $find=$db->where(array('receiveusercard'=>$userinfo['cardno']))->order('id desc')->find();
            
            //如果有数据，证明之前领取过
            if (null != $find){
                $result=$this->checklastreceivetime( (int)$find['receivetime'], $find['duetime'], $admininfo['pre_table'], $params['key_admin'], $params['openid']);
                if (true == $result){
                    $mixtime=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'timeinterval');
                    $mixtime=(int)$mixtime['function_name'];//最短间隔
                    returnjson(array('code'=>1027,'data'=>array('daynums'=>$mixtime )), $this->returnstyle, $this->callback);
                }
                
            }
            
            //取到用户信息后领取积分
            $as['key_admin']=$params['key_admin'];
            $as['cardno']=$userinfo['cardno'];//传入openid对应的卡号
            $as['scoreno']=(int)$transfer['scorenumber'];//库里面存的积分数
            $as['why']='积分转赠领取积分';
            $as['scorecode']=date('Y-m-d');//积分转赠
            $as['sign_key']=$admininfo['signkey'];
            $as['membername']='name';
            $as['sign']=sign($as);
            unset($as['sign_key']);
            $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
            $cutscore=http($url, $as);
            $array=json_decode($cutscore, true);
            if ($array['code']==200){
                $data=array(
                    'receiveusercard'=>$userinfo['cardno'],
                    'receivewechatuser'=>$params['receivewechatuser'],
                    'receiverheaderimg'=>$params['receiverheaderimg'],
                    'receivermobile'=>$userinfo['mobile'],
                    'receivetime'=>time(),
                    'isreceive'=>1
                );
                $change=$db->where(array('urlstr'=>$params['urlstr']))->save($data);
                if (false === $change){//再来一次
                    $change=$db->where(array('urlstr'=>$params['urlstr']))->save($data);
                }
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>$array['code']), $this->returnstyle, $this->callback);
            }
        }
    }
    
    
    
    
    /**
     *根据openid从数据库中获取用户信息
     *
     */
    private function getUserByOpenid($openid, $pre_table, $re=null)
    {
        $db=M('mem', $pre_table);
        $find=$db->where(array('openid'=>$openid))->find();
        if (null != $find && is_array($find)){
            return $find;
        }else {
            returnjson(array('code'=>103,'data'=>$re), $this->returnstyle, $this->callback);
        }
    }
    
    
    /**
     * 判断上次领取是什么时候，符不符合条件
     * @param unknown $receivetime  上次领取时间
     * @param unknown $duetime        过期时间
     * @param unknown $pre_table      表前缀
     * @param unknown $key_admin
     * @param unknown $openid
     */
    private function checklastreceivetime( int $receivetime, $duetime, $pre_table, $key_admin, $openid)
    {
        $mixtime=$this->GetOneAmindefault($pre_table, $key_admin, 'timeinterval');
        $mixtime=(int)$mixtime['function_name'];//最短间隔
        
        $second=$mixtime*60*60*24;
        $time=time()-$receivetime;//现在的时间减去上次的时间
        
        //判断，如果得数大于最短间隔时间，则符合条件
        if ($time < $second){
            return true;
        }else {
            return false;
        }
    }
    
    
    
    
    
    
    
    
    
}