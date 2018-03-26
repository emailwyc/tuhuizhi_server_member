<?php
/**
 * Created by PhpStorm.
 * User: jaleel
 * Date: 8/16/16
 * Time: 2:06 PM
 */

namespace CrmService\Controller\Crmapi;


use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;
use Common\Controller\WebserviceController;

class TaiguliCrmController extends CommonController implements CrminterfaceController
{
    protected $md5_key = '201608171049'; // md5加密密钥
    protected $uname = 'ZHTWS';
    //protected $request_url = 'http://211.157.182.226:2001/CRM_VIP_Proxy.asmx?wsdl';
//     protected $request_url = 'http://60.205.156.90:2880/CRM_VIP_Proxy.asmx?wsdl';//旧地址
    protected $request_url = 'http://210.12.102.100:2880/CRM_VIP_Proxy.asmx?wsdl';//新地址
    
    

    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     *
     */
    public function GetUserinfoByCard() 
    {
        $params['key_admin']=I('key_admin');
        $params['card']=I('card');
        $sign=I('sign');

        //获取的参数不完整
        if (in_array('',$params)){
            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);
        }

        //签名错误
//         if (false==$this->sign($params['key_admin'], $params, $sign)){
//             $msg['code']=1002;
//             returnjson($msg,$this->returnstyle,$this->callback);
//         }
        $admininfo=$this->getMerchant($params['key_admin']);
        
        //webservice
        $kechuancrmsign = $this->buildHeader();
        $data[0]['request']['Header']=$kechuancrmsign;//header部分
        $data[0]['request']['Data']=array(//data部分
            'vipcode'=>$params['card']);
        $webservice= new WebserviceController($admininfo['pre_table']);
        $client=$webservice->soapClient($this->request_url);
        $result= $webservice->sopaCall('GetVipInfo', $client, $data);
        if (is_object($result)){
            $array=$this->objtoarray($result);
            if (0 == $array['GetVipInfoResult']['Header']['ERRCODE']){
                $db=M('mem',$admininfo['pre_table']);
                
                $rt['cardno']=$array['GetVipInfoResult']['DATA']['VIP']['xf_vipcode'];
                $rt['usermember']=$array['GetVipInfoResult']['DATA']['VIP']['xf_surname'];
                $rt['idnumber']=$array['GetVipInfoResult']['DATA']['VIP']['xf_vipid'];
                $rt['status']=$array['GetVipInfoResult']['DATA']['VIP']['xf_active'];
                $rt['status_description']='';
                $rt['getcarddate']=$array['GetVipInfoResult']['DATA']['VIP']['xf_issuedate'];
                $rt['expirationdate']=$array['GetVipInfoResult']['DATA']['VIP']['xf_expirydate'];//到期时间
                $mm= 1==strlen($array['GetVipInfoResult']['DATA']['VIP']['xf_birthdaymm']) ? '0'.$array['GetVipInfoResult']['DATA']['VIP']['xf_birthdaymm'] : $array['GetVipInfoResult']['DATA']['VIP']['xf_birthdaymm'];
                $dd= 1==strlen($array['GetVipInfoResult']['DATA']['VIP']['xf_birthdaydd']) ? '0'.$array['GetVipInfoResult']['DATA']['VIP']['xf_birthdaydd'] : $array['GetVipInfoResult']['DATA']['VIP']['xf_birthdaydd'];
                
                $rt['birthday']=$array['GetVipInfoResult']['DATA']['VIP']['xf_birthdayyyyy'].'-'.$mm.'-'.$dd;
                $rt['company']='';
                $rt['phone']=$array['GetVipInfoResult']['DATA']['VIP']['xf_telephone'];
                $rt['mobile']=$array['GetVipInfoResult']['DATA']['VIP']['xf_telephone'];
                $rt['address']=$array['GetVipInfoResult']['DATA']['VIP']['xf_address1'];
                $rt['level']=$array['GetVipInfoResult']['DATA']['VIP']['xf_grade'];
                $rt['sex']= 'M'==$array['GetVipInfoResult']['DATA']['VIP']['xf_sex'] ? 1 : 0;
                $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                if (null == $sel){
                    $sv=$db->add($rt);
                }else{
//                    $sv = $this->checkupdate($db, $sel, $rt);
                }
                $datas['cardno']=$rt['cardno'];
                $datas['xf_vipcardno']=$array['GetVipInfoResult']['DATA']['VIP']['xf_vipcardno'];
                $datas['user']=$rt['usermember'];
                $datas['cardtype']=$array['GetVipInfoResult']['DATA']['VIP']['xf_grade'];
                $datas['status']=$rt['status'];
                $datas['status_description']='';
                $datas['getcarddate']=$array['GetVipInfoResult']['DATA']['VIP']['xf_issuedate'];//创建时间
                $datas['expirationdate']=$array['GetVipInfoResult']['DATA']['VIP']['xf_expirydate'];//到期时间
                $datas['birthday']=$rt['birthday'];
                $datas['company']='';
                $datas['phone']=$rt['phone'];
                $datas['mobile']=$rt['phone'];
                $datas['address']=$rt['address'];
                $datas['sex']=$rt['sex'];
                $datas['score']=(float) str_replace(',', '', $array['GetVipInfoResult']['DATA']['VIP']['xf_bonus']);//剩余积分
                $msg['code']=200;
                $msg['data']=$datas;
            }else{
                $msg['code']=104;
                $msg['data']=$array['GetVipInfoResult']['Header']['ERRCODE'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * 判断是否需要进行更新操作
     * $db  数据库
     * $old 老数据
     * $new 最新数据
     */
    function checkupdate($db, $old, $new)
    {
        if($old['idnumber'] != $new['idnumber'] ||
           $old['status'] != $new['status'] ||
           $old['getcarddate'] != $new['getcarddate'] ||
           $old['expirationdate'] != $new['expirationdate'] ||
           $old['birthday'] != $new['birthday'] ||
           $old['phone'] != $new['phone'] ||
           $old['mobile'] != $new['mobile'] ||
           $old['address'] != $new['address'] ||
           $old['level'] != $new['level'] ||
           $old['sex'] != $new['sex']
          )
        {
            $sv = $db->where(array('cardno'=>$new['cardno']))->save($new);
        }
        
        return $sv;
    }
    
    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile() 
    {
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        $sign=I('sign');
        $openid=I('openid');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $admininfo=$this->getMerchant($params['key_admin']);
                //webservice
                $kechuancrmsign = $this->buildHeader();
                $data[0]['request']['Header']=$kechuancrmsign;//header部分
                $data[0]['request']['Data']=array(//data部分
                    'mobile'=>$params['mobile']
                );
                $webservice= new WebserviceController($admininfo['pre_table']);
                $client=$webservice->soapClient($this->request_url);
                $result= $webservice->sopaCall('GetVipInfoByMobileOpenID', $client, $data);
                
                
                if (is_object($result)){
                    $array=$this->objtoarray($result);
                    if (0 == $array['GetVipInfoByMobileOpenIDResult']['Header']['ERRCODE']){
                        $db=M('mem',$admininfo['pre_table']);
                
                        $rt['cardno']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_vipcode'];
                        $rt['usermember']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_surname'];
                        $rt['idnumber']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_vipid'];
                        $rt['status']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_active'];
                        $rt['status_description']='';
                        $rt['getcarddate']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_issuedate'];
                        $rt['expirationdate']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_expirydate'];//到期时间
                        $rt['birthday']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_birthdayyyy'].'-'.$array['DATA']['VIP']['xf_birthdaymm'].'-'.$array['DATA']['VIP']['xf_birthdaydd'];
                        $rt['company']='';
                        $rt['phone']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_telephone'];
                        $rt['mobile']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_telephone'];
                        $rt['address']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_address1'];
                        $rt['level']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_grade'];
                        $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                        if (null == $sel){
                            $sv=$db->add($rt);
                        }else{
                              $sv = $this->checkupdate($db, $sel, $rt);
                        }
                        $datas['cardno']=$rt['cardno'];
                        $datas['xf_vipcardno']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_vipcardno'];
                        $datas['user']=$rt['usermember'];
                        $datas['cardtype']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_grade'];
                        $datas['status']=$rt['status'];
                        $datas['status_description']='';
                        $datas['getcarddate']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_issuedate'];//创建时间
                        $datas['expirationdate']=$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_expirydate'];//到期时间
                        $datas['birthday']=$rt['birthday'];
                        $datas['company']='';
                        $datas['phone']=$rt['phone'];
                        $datas['mobile']=$rt['phone'];
                        $datas['address']=$rt['address'];
                        $datas['score']=(float) str_replace(',', '', $array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_bonus']);//剩余积分
                        $msg['code']=200;
                        $msg['data']=$datas;
                        
                        //绑定openid
                        $this->bindopenid($rt['cardno'], $openid, $params['mobile'], $admininfo['pre_table'], $db);

                        //判断是否可以发消息
                        $params['sendmsg'] = I('sendmsg');
                        if ($params['sendmsg'] == 'sendmsg') {
                            //发送模板消息
                            $sir='M'==$array['GetVipInfoByMobileOpenIDResult']['DATA']['VIP']['xf_sex'] ? '先生' : '女士';
                            $man=$rt['usermember'].$sir;
                            $tempmessage=array(array(
                                'touser'=>$openid,
                                'template_id'=>'hwaFiV9FhgPhGSKnZkHul-F84tMrD4hRSkme75peOX8',
                                'url'=>'',
                                'data'=>array(
                                    'first'=>array('value'=>'尊敬的'.$man.'，恭喜您成功登录，享受相应会员权益。三里屯太古里感谢您的支持与厚爱，让我们一起潮玩！','color'=>'#173177'),
                                    'keyword1'=>array('value'=>$datas['xf_vipcardno'], 'color'=>'#173177'),
                                    'keyword2'=>array('value'=>date('Y-m-d H:i:s'), 'color'=>'#173177'),
                                    'remark'=>array('value'=>'谢谢您的支持！', 'color'=>'#173177'),
                                )
                            ));
                            $url='https://mem.rtmap.com/Thirdwechat/Wechat/Template/outsideSendMessage';
                            $sign=sign(array('sign_key'=>$admininfo['signkey'],'key_admin'=>$params['key_admin']));
                            $url=$url.'?key_admin='.$params['key_admin'].'&sign='.$sign;
                            curl_https($url,json_encode($tempmessage), array(), 30, true);//发送模板消息
                        }

                        //测试服务器不能测
//                         $tempmessageobj= new \Thirdwechat\Controller\Wechat\TemplateController();
//                         $tempmessageobj->insideSendMessage($tempmessage, $params['key_admin']);
                        
                        
                        
                        
                    }else{
                        $msg['code']=104;
                        $msg['data']=$array['GetVipInfoByMobileOpenIDResult']['Header']['ERRCODE'];
                    }
                }else{
                    $msg['code']=101;
                }
//             }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }



    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name
     */
    public function createMember() 
    {
        $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        $params['sex']=I('sex');
        
        $params['name']=I('name');
        $openid=I('openid');
        $address=I('address');
        $sign=I('sign');

        //获取的参数不完整
        if (in_array('',$params)){
            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);
        }
        $params['idnumber']=I('idnumber');//身份证号不是必填
        //签名错误
//         if (false==$this->sign($params['key_admin'], $params, $sign)){
//             $msg['code']=1002;
//             returnjson($msg,$this->returnstyle,$this->callback);
//         }
        $params['birthday']=I('birth');
        $admininfo=$this->getMerchant($params['key_admin']);
//         $zsmobile=array('18611144242','18910124223','18612814568','15201035014','18618445866');
//         if ('15020021701'==$params['mobile']){
//             $type='GG';
//         }elseif (in_array($params['mobile'], $zsmobile)){
//             $type='DD';
//         }else {
            $type='AA';
//         }
        //webservice
        $kechuancrmsign = $this->buildHeader($params['mobile']);
        $data[0]['request']['Header']=$kechuancrmsign;//header部分
        $data[0]['request']['Data']['vip']=array(//data部分
            'surname'=>$params['name'],
            'mobile'=>$params['mobile'],
            'sex'=> 1== $params['sex'] ? 'M' : 'F',
            'idcardtype'=>'0',
            'idcardno'=>$params['idnumber'],
            'weixin'=>$openid,
            'address'=>$address,
            'birthday'=>date('Y-m-d H:i:s', $params['birthday']),
            'vipgrade'=>$type);
        $webservice= new WebserviceController($admininfo['pre_table']);
        $client=$webservice->soapClient($this->request_url);
        $result= $webservice->sopaCall('VipCreate', $client, $data);
        
        if (is_object($result)){
            $array=$this->objtoarray($result);
            if (0 == $array['VipCreateResult']['Header']['ERRCODE']){
                $d['cardno']=$array['VipCreateResult']['DATA']['xf_vipcode'];
                $d['xf_vipcardno']=$array['VipCreateResult']['DATA']['xf_vipcardno'];
                
                $d['openid']=$openid;
                $d['datetime']=date('Y-m-d H:i:s');
                $d['usermember']=$params['name'];
                $d['idnumber']=$params['idnumber'];
                $d['getcarddate']=date('Y-m-d');
                $d['phone']=$params['mobile'];
                $d['mobile']=$params['mobile'];
                $d['sex']=$params['sex'];
                $db=M('mem', $admininfo['pre_table']);
                $add=$db->add($d);
                $msg['code']=200;
                $list=array(
                    'cardno'=>$d['cardno'],
                    'usermember'=>$params['name'],
                    'getcarddate'=>$d['getcarddate'],
                    'expirationdate'=>'',
                    'mobile'=>$params['mobile'],
                    'sex'=>$params['sex'],
                    'idnumber'=>$params['idnumber']
                );
                $msg['data']=$list;
                //发送模板消息
                $sir=1==$params['sex'] ? '先生' : '女士';
                $man=$params['name'].$sir;
                $tempmessage=array(array(
                    'touser'=>$openid,
                    'template_id'=>'zPwNukjvokLuexJYA9aI_xLx2oW7S0zprUKkoduSSdU',
                    'url'=>'',
                    'data'=>array(
                        'first'=>array('value'=>'尊敬的'.$man.'，恭喜您成为三里屯太古里银卡会员，享受相应会员权益。三里屯太古里感谢您的支持与厚爱，让我们一起潮玩！注册信息如下：','color'=>'#173177'),
                        'keyword1'=>array('value'=>$params['mobile'], 'color'=>'#173177'),
                        'keyword2'=>array('value'=>$d['xf_vipcardno'], 'color'=>'#173177'),
                        'keyword3'=>array('value'=>$d['datetime'], 'color'=>'#173177'),
                        'remark'=>array('value'=>'谢谢您的支持！', 'color'=>'#173177'),
                    )
                ));
                $url='https://mem.rtmap.com/Thirdwechat/Wechat/Template/outsideSendMessage';
                $sign=sign(array('sign_key'=>$admininfo['signkey'],'key_admin'=>$params['key_admin']));
                $url=$url.'?key_admin='.$params['key_admin'].'&sign='.$sign;
                curl_https($url,json_encode($tempmessage), array(), 30, true);//发送模板消息

                //发送图文消息6aCe3_cQfGC0zcoW1H1XXDgupgimT5WuPlv1HWqjmIA
                $servicearray=array('touser'=>$openid,'msgtype'=>'mpnews','mpnews'=>array('media_id'=>'6aCe3_cQfGC0zcoW1H1XXDgupgimT5WuPlv1HWqjmIA'));
                $sign=sign(array('sign_key'=>$admininfo['signkey'],'key_admin'=>$params['key_admin']));
                $service_url='https://mem.rtmap.com/Thirdwechat/Wechat/Servicemessage/send_service_message?key_admin='.$params['key_admin'].'&sign='.$sign;
                curl_https($service_url, json_encode($servicearray), array(), 30, true);

                
//                 $tempmessageobj= new \Thirdwechat\Controller\Wechat\TemplateController();
//                 $tempmessageobj->insideSendMessage($tempmessage, $params['key_admin']);
                
                //发送模板消息结束
            }else{
                if (101 == $array['VipCreateResult']['Header']['ERRCODE']){
                    $msg['code']=1012;
                }else {
                    $msg['code']=3000;
                    $msg['data']=$array['VipCreateResult']['Header']['ERRCODE'];
                }
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember() 
    {
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        
        
        $params['name']=I('name');
        $params['cardno']=I('cardno');
        $sign=I('sign');
        $openid=I('openid');
        $address=I('address');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $params['idnumber']=I('idnumber');
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $params['birthday']=I('birth');
                $params['sex']=1==I('sex') ? 'M' : 'F';//sex不参加签名
                $admininfo=$this->getMerchant($params['key_admin']);
                $kechuancrmsign = $this->buildHeader($params['cardno']);//header数组
                $data[0]['request']['Header']=$kechuancrmsign;//header部分
                $db=M('mem', $admininfo['pre_table']);
                $sel= $db->where(array('cardno'=>$params['cardno'],'mobile'=>$params['mobile'] ))->select();
                if (!$sel){//crm要求，如果手机号和之前的一样，不能传入，否则会报此手机号已绑定其它会员wtf
                    $data[0]['request']['Data']['vip']['mobile']=$params['mobile'];
                }
                //如果传入的openid与数据库中存的不一样，则去请求绑定openid接口，wtf
                if (!$db->where(array('cardno'=>$params['cardno'],'openid'=>$openid ))->select()){
                    $this->bindopenid($params['cardno'], $openid, $params['mobile'], $admininfo['pre_table'], $db);
                }
                
                $data[0]['request']['Data']['vip']=array(//data部分
                    'xf_vipcode'=>$params['cardno'],
                    'surname'=>$params['name'],
                    'sex'=> $params['sex'],
                    'birthday'=>date('Y-m-d H:i:s', $params['birthday']),
                    'idcardtype'=>'0',
                    'idcardno'=>$params['idnumber'],
                    'address'=>$address,
                    'email'=>'',
                    'vipgrade'=>'',
                    'vip_souce'=>'',
                    'jointdate'=>'',
                    'xf_issuestore'=>'',
                    'xf_issuestaffcode'=>'',
                    'xf_vipcodeprefix'=>'AA',
                );
                $webservice= new WebserviceController($admininfo['pre_table']);
                $client=$webservice->soapClient($this->request_url);
                $result= $webservice->sopaCall('VipModify', $client, $data);
                if (is_object($result)){
                    $array=$this->objtoarray($result);
                    if (0 == $array['VipModifyResult']['Header']['ERRCODE']){
                        
                        $rt['mobile']=$params['mobile'];
                        $rt['sex']= 'M'== $params['sex'] ? 1 : 0;
                        $rt['idnumber']=$params['idnumber'];
                        $rt['usermember']=$params['name'];
                        $rt['openid']=$openid;
                        $sel=$db->where(array('cardno'=>$params['cardno']))->find();
                        if (null == $sel){
                            $rt['cardno']=$params['cardno'];
                            $sv=$db->add($rt);
                        }else{
                            $sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
                        }
                        $datas['cardno']=$params['cardno'];
                        $datas['usermember']=$params['name'];
                        $datas['getcarddate']='';//创建时间
                        $datas['expirationdate']='';//到期时间
                        $datas['mobile']=$params['mobile'];
                        $datas['sex']='M'==$params['sex'] ? 1 : 0;
                        $datas['idnumber']=$params['idnumber'];
                        $msg['code']=200;
                        $msg['data']=$datas;
                    }else{
                        $msg['code']=104;
                        $msg['data']=$array['VipModifyResult']['Header']['ERRCODE'];
                    }
                }else{
                    $msg['code']=101;
                }
//             }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    
    private function bindopenid($cardno, $openid, $mobile, $tab_pre, $db)
    {
        $header=$this->buildHeader($cardno);
        $data[0]['request']['Header']=$header;
        $data[0]['request']['Data']=array(
            'vipcode'=>$cardno,
            'mobile'=>$mobile,
            'openid'=>$openid
        );
        $webservice= new WebserviceController($tab_pre);
        $client=$webservice->soapClient($this->request_url);
        $result= $webservice->sopaCall('BindOpenID', $client, $data);
        if (is_object($result)){
            $array=$this->objtoarray($result);
            if (isset($array['BindOpenIDResult']['Header']['ERRCODE']) && 0==$array['BindOpenIDResult']['Header']['ERRCODE']){
                $find = $db->where(array('cardno'=>$cardno))->find();
                if ($find['openid'] != $openid){
                    $db->where(array('cardno'=>$cardno))->save(array('openid'=>$openid));
                }
            }
        }
    }

    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore() 
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        //$params['scorecode']=I('scorecode');
        //$params['membername']=I('membername');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $this->changescore($params['key_admin'], $params['cardno'], date('Y-m-d'), -($params['scoreno']), $params['why'], $params['why']);
//             }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }




    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral() 
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        $params['scorecode']=I('scorecode');
        $params['membername']=I('membername');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $store=I('store');
                $this->changescore($params['key_admin'], $params['cardno'], date('Y-m-d'), $params['scoreno'], $params['why'], $params['why'],$store);
//             }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * 
     * @param unknown $key_admin
     * @param unknown $vipcode  卡号
     * @param unknown $expdate  变更日期
     * @param unknown $bonus    积分数
     * @param unknown $reasoncode  //变更编码
     * @param unknown $remark  //备注
     */
    private function changescore($key_admin, $vipcode, $expdate, $bonus, $reasoncode, $remark,$store='')
    {
        $admininfo=$this->getMerchant($key_admin);
        $kechuancrmsign = $this->buildHeader($bonus);//header数组
        $data[0]['request']['Header']=$kechuancrmsign;//header部分
        
        $reasoncode=cut_str($reasoncode, 5);
        $data[0]['request']['Data']=array(//data部分
            'vipcode'=>$vipcode,
            'expdate'=>date('Y-m-d', strtotime('+1 year')),
            'bonus'=>$bonus,
            'reasoncode'=>$reasoncode,
            'remark'=>$remark,
        );
        $webservice= new WebserviceController($admininfo['pre_table']);
        $client=$webservice->soapClient($this->request_url);
        $result= $webservice->sopaCall('BonusChange', $client, $data);
        if (is_object($result)){
            $array=$this->objtoarray($result);
            if (0 == $array['BonusChangeResult']['Header']['ERRCODE']){
                $data['cardno']=$vipcode;
                $data['scorenumber']=$bonus;
                $data['why']=$remark;
                $data['scorecode']='';
                $data['cutadd']=2;
                $admininfo=$this->getMerchant($key_admin);
                $db=M('score_record',$admininfo['pre_table']);
                $data['store']=$store?$store:'';
                $add=$db->add($data);
                if ($add){
                    $msg['code']=200;
                }else{
                    $msg['code']=200;
                    $msg['data']='数据保存错误';
                }
            }else {
                $msg['code']=104;
                $msg['data']=$array['BonusChangeResult']['Header']['ERRCODE'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist() 
    {
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['page']=I('page');
        $params['lines']=I('lines');
        $params['startdate']=I('startdate');
        $params['enddate']=I('enddate');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{//执行业务
                $params['startdate']=date('Y-m-d H:i:s', $params['startdate']);
                $params['enddate']=date('Y-m-d H:i:s', $params['enddate']);
                
                $admininfo=$this->getMerchant($params['key_admin']);
                $kechuancrmsign = $this->buildHeader($params['cardno']);//header数组
                $data[0]['request']['Header']=$kechuancrmsign;//header部分
                
                $data[0]['request']['Data']=array(//data部分
                    'vipcode'=>$params['cardno'],
                    'frmtxdate'=>$params['startdate'],
                    'totxdate'=>$params['enddate'],
                    'action'=>null,
                    'remark'=>''
                );
                $webservice= new WebserviceController($admininfo['pre_table']);
                $client=$webservice->soapClient($this->request_url);
                $result= $webservice->sopaCall('GetBonusledgerRecord', $client, $data);
                if (is_object($result)){
                    $array=$this->objtoarray($result);
                    if (0 == $array['GetBonusledgerRecordResult']['Header']['ERRCODE']){
                        if (count($array['GetBonusledgerRecordResult']['DATA']['xf_bonusledger'], true) != count($array['GetBonusledgerRecordResult']['DATA']['xf_bonusledger'])){
                            foreach ($array['GetBonusledgerRecordResult']['DATA']['xf_bonusledger'] as $key => $val){
                                $scorelist[]=array(
                                    'date'=>$val['XF_TXDATE'],
                                    'description'=>str_replace('销售', '消费', $val['XF_ACTION']),
                                    'score'=>$val['XF_BONUS']
                                );
                            }
                        }else{
                            foreach ($array['GetBonusledgerRecordResult']['DATA']['xf_bonusledger'] as $key => $val){
                                if ('XF_TXDATE'==$key){
                                    $scorelist[0]['date']=$val;
                                }
                                if ('XF_ACTION'==$key){
                                    $scorelist[0]['description']=str_replace('销售', '消费', $val);
                                }
                                if ('XF_BONUS'==$key){
                                    $scorelist[0]['score']=$val;
                                }
                            }
                        }
                        
                        $msg['code']=200;
                        $msg['data']=array(
                            'cardno'=>$params['cardno'],
                            'scorelist'=>$scorelist,
                        );
                    }else{
                        $msg['code']=104;
                        $msg['data']=$array['GetBonusledgerRecordResult']['Header']['ERRCODE'];
                    }
                }else{
                    $msg['code']=101;
                }
//             }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
        
    }

    protected function buildHeader($str = '') 
    {
        $data['REQDATE'] = date('Ymd');
        $data['REQTIME'] = date('His');
        $data['USER'] = $this->uname;
        $str=$data['REQDATE'] . $data['REQTIME'] . $str . $this->md5_key;
        //echo '<br>'.$str;
        $data['SIGN'] = md5($str); // MD5(REQDATE+REQTIME+mobile+密钥)
        return $data;
    }
    
    
    private function objtoarray($obj)
    {
        return json_decode(json_encode($obj),true);
    }


    public function integral_save(){
        //         $params['']
        $params['key_admin']=I('key_admin');
        $params['s_vipcode']=I('vipcode');//卡号
        $params['s_docno']=I('docno');//单据号
        $params['s_storecode']=I('storecode');//店铺号
        $params['s_tillid']=I('tillid');//收银机号
        $params['dt_txdate']=I('txdate');//销售日期 yyyymmdd
        $params['s_cashier']=I('cashier');//用户名
        $log['params']=$params;
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $admininfo=$this->getMerchant($params['key_admin']);
            $kechuancrmsign = $this->buildHeader($params['s_storecode']);
            $data[0]['request']['Header']=$kechuancrmsign;//header部分
            $data[0]['request']['Data']['salestotal']=array(//data部分
                's_vipcode'=>$params['s_vipcode'],
                's_docno'=>$params['s_docno'],
                's_storecode'=>$params['s_storecode'],
                's_tillid'=>$params['s_tillid'],
                'dt_txdate'=>$params['dt_txdate'],
                's_cashier'=>$params['s_cashier'],
            );
            $webservice= new WebserviceController($admininfo['pre_table']);
            $client=$webservice->soapClient($this->request_url);
            $result= $webservice->sopaCall('RepairConsume', $client, $data);
            $log['result']=$result;
            if (is_object($result)){
                $arr=$this->objtoarray($result);
                if(1 == $arr['RepairConsumeResult']['Header']['ERRCODE']){
                    $msg['code']=104;
                    $msg['data']=$arr['RepairConsumeResult']['Header']['ERRMSG'];
                }else{
                    //记录本地积分明细表
                    $save_data['cardno']=$params['s_vipcode'];
                    $save_data['scorenumber']=$arr['GetBonusledgerRecordResult']['DATA']['bonusredeem']?1:2;
                    $save_data['why']='积分补录';
                    $save_data['scorecode']=$arr['GetBonusledgerRecordResult']['DATA']['traceno']?1:2;
                    $save_data['cutadd']=2;
                    $save_data['datetime']=date('Y-m-d');
                    $save_data['is_del']=1;
                    $save_data['store']=$params['storecode'];
                    $db=M('score_record',$admininfo['pre_table'])->add($save_data);
                    //记录结束
                    $msg['code']=200;
                    $msg['data']=$arr['RepairConsumeResult']['DATA'];
                }
            }else{
                $msg['code']=101;
            }
        }
        $sql['taiguli_integral']=$log;
        writeOperationLog($sql,'zhanghang');
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){
        
    }
    public function GetUserinfoByOpenid(){

    }
    
    protected function requestInter($method, $header, $data) {
        $url = $this->request_url . '/' . $method;
    
    }
    
    /**
     * 解绑
     */
    public function UnBind(){}
}