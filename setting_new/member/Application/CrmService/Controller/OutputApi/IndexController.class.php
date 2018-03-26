<?php
namespace CrmService\Controller\OutputApi;
use CrmService\Controller\Crmapi\CrmController;
use CrmService\Service\CrmService;

class IndexController extends CrmController {
    private $crm;

    public function _initialize(){
        parent::_initialize();
        $key_admin=I('key_admin');
        if ('' == $key_admin){
            $msg['code']=1001;
            returnjson($msg,$this->returnstyle,$this->callback);
        }
        $signkey=I('sign_key');
        if ($signkey != ''){
            returnjson(array('code'=>104,'data'=>'bad'),$this->returnstyle, $this->callback);
        }
        //获取本条key_admin的total_admin表数据
        $crmdata=$this->getMerchant($key_admin);
        $params=I('param.');
        if($params['store']){
            $params['store']=html_entity_decode($params['store']);
        }

        //验证签名
        if (isset($params['secure'])){//此参数是显示错误页面的参数
            unset($params['secure']);
        }
        $othersign=$params['sign'];
        unset($params['key_admin']);
        unset($params['sign']);
        if (false == $this->sign($key_admin, $params, $othersign)){
            returnjson(array('code'=>1002), $this->returnstyle, $this->callback);
        }
        $params['key_admin']=$key_admin;
        //读取类名
        $classname=$this->GetOneAmindefault($crmdata['pre_table'], $key_admin, 'crmclassname');
        $crmserviceclass=$classname['function_name'];
        $action=ACTION_NAME;
        //扣减积分的时候所做的操作
        if (strtolower($action) == 'cutscore') {
            $scorerule = $this->GetOneAmindefault($crmdata['pre_table'], $key_admin, 'sconsumptionscorerule');
            $params['unionid'] = $params['unionid']?$params['unionid']:'';
            $check = CrmService::CheckScoreRule($crmdata, $params['cardno'], $scorerule, $params['scoreno'],$params['unionid']);
            if (is_array($check)){
                if ($check['code'] != 200) {
                    returnjson($check, $this->returnstyle, $this->callback);
                }
            }
        }

        if ('' != $crmserviceclass && 'java' != $crmserviceclass){
            $obj = new $crmserviceclass();//dump($obj);
            $a=$obj->$action();
            exit;
        }elseif ('java' == $crmserviceclass){
            $action=strtolower($action);
            if ('createmember' == $action){
                if (isset($params['birth'])){
                    $birth=date('Y-m-d', $params['birth'])?date('Y-m-d', $params['birth']):false;
                    if ($birth===false){
                        $params['birth']=$params['birth'];
                    }else{
                        $params['birth']=$birth;
                    }
                }
                $url='http://101.200.187.233:8080/crm/rest/api/create';
                $return=http($url, $params,'POST');
                $arr=json_decode($return, true);

                if (is_array($arr) && $arr['code'] == 200){
                    $rt['cardno']=$arr['data']['cardno'];
                    $rt['usermember']=$arr['data']['usermember']?$arr['data']['usermember']:'';
                    $rt['idnumber']='';
                    $rt['status']=$arr['data']['status']?$arr['data']['status']:'';
                    $rt['status_description']='';
                    $rt['getcarddate']=$arr['data']['getcarddate']?$arr['data']['getcarddate']:'';
                    $rt['expirationdate']=$arr['data']['expirationdate']?$arr['data']['expirationdate']:'';//到期时间

                    $rt['birthday']=$arr['data']['birthday']?$arr['data']['birthday']:'';
                    $rt['company']='';
                    $rt['phone']=$arr['data']['phone']?$arr['data']['phone']:'';
                    $rt['mobile']=$arr['data']['mobile']?$arr['data']['mobile']:'';
                    $rt['address']=$arr['data']['address']?$arr['data']['address']:'';
                    $rt['sex']= 0;
                    $user=M('mem', $crmdata['pre_table']);
                    $find=$user->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $find){
                        $add=$user->add($rt);
                    }
                }

            }elseif ('editmember' == $action){
//                 if (isset($paramssign['cardno'])){
//                     $paramssign['carano']=$paramssign['cardno'];
//                     unset($paramssign['cardno']);
//                 }
                $birth=date('Y-m-d', $params['birth'])?date('Y-m-d', $params['birth']):false;
                if ($birth===false){
                    $params['birth']=$params['birth'];
                }else{
                    $params['birth']=$birth;
                }
                $url='http://101.200.187.233:8080/crm/rest/api/update';
                $return=http($url, $params,'POST');


            }elseif ('getuserinfobymobile' == $action){
//                 if (!isset($paramssign['name']) || $paramssign['name']==''){
//                     $paramssign['name']='aa';
//                 }
                $url='http://101.200.187.233:8080/crm/rest/api/findByMobile';
                $return=http($url, $params,'POST');
                $arr=json_decode($return, true);
                if (is_array($arr) && $arr['code'] == 200){
//                    $arr['data']['cardno']=$arr['data']['cardno'];
//                    $return=json_encode($arr);//f u c k

                    $rt['cardno']=$arr['data']['cardno'];
                    $rt['usermember']=$arr['data']['usermember']?$arr['data']['usermember']:'';
                    $rt['idnumber']='';
                    $rt['status']=$arr['data']['status']?$arr['data']['status']:'';
                    $rt['status_description']='';
                    $rt['getcarddate']=$arr['data']['getcarddate']?$arr['data']['getcarddate']:'';
                    $rt['expirationdate']=$arr['data']['expirationdate']?$arr['data']['expirationdate']:'';//到期时间

                    $rt['birthday']=$arr['data']['birthday']?$arr['data']['birthday']:'';
                    $rt['company']='';
                    $rt['phone']=$arr['data']['phone']?$arr['data']['phone']:'';
                    $rt['mobile']=$arr['data']['mobile']?$arr['data']['mobile']:'';
                    $rt['address']=$arr['data']['address']?$arr['data']['address']:'';
                    $rt['sex']= 0;
                    $user=M('mem', $crmdata['pre_table']);
                    $find=$user->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $find){
                        $add=$user->add($rt);
                    }else{
                        $user->where(array('cardno'=>$rt['cardno']))->save(array('level'=>$arr['data']['cardtype']));
                    }
                }
            }elseif ('getuserinfobycard' == $action){
                $url='http://101.200.187.233:8080/crm/rest/api/findByCard';
                $return=http($url, $params,'POST');
                $arr=json_decode($return, true);
                if (is_array($arr) && $arr['code'] == 200){
                    $rt['cardno']=$arr['data']['cardno'];
                    $rt['cardno']=$arr['data']['cardno'];
                    $rt['usermember']=$arr['data']['usermember'];
                    $rt['idnumber']=$arr['data']['idnumber'] ? $arr['data']['idnumber'] : '';
                    $rt['status']=$arr['data']['status']?$arr['data']['status'] : '';
                    $rt['status_description']='';
                    $rt['getcarddate']=$arr['data']['getcarddate']?$arr['data']['getcarddate']:'';
                    $rt['expirationdate']=$arr['data']['expirationdate']?$arr['data']['expirationdate'] : '';//到期时间

                    $rt['birthday']=$arr['data']['birthday'] ? $arr['data']['birthday'] : '';
                    $rt['company']='';
                    $rt['phone']=$arr['data']['phone'] ? $arr['data']['phone'] :'';
                    $rt['mobile']=$arr['data']['mobile'] ? $arr['data']['mobile'] :'';
                    $rt['address']=$arr['data']['address'] ? $arr['data']['address'] : '';
                    $rt['sex']= 0;
                    $user=M('mem', $crmdata['pre_table']);
                    $find=$user->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $find){
                        $add=$user->add($rt);
                    }else{
                        $user->where(array('cardno'=>$rt['cardno']))->save(array('level'=>$arr['data']['cardtype']));
                    }
                }
            }elseif ('cutscore' == $action){
                $url='http://101.200.187.233:8080/crm/rest/api/subScore';
                $return=http($url, $params,'POST');


            }elseif ('addintegral' == $action){
                $url='http://101.200.187.233:8080/crm/rest/api/addScore';
                $return=http($url, $params,'POST');


            }elseif ('checkwechatopenid' == $action){
                $url='http://101.200.187.233:8080/crm/rest/api/findByWx';
//                 $pdata=array('key_admin'=>$params['key_admin'],'openid'=>$params['openid']);
                $return=http($url, $params, 'POST');
            }else if ('scorelist' == $action){
                $url='http://101.200.187.233:8080/crm/rest/api/record';
                $return=http($url, $params, 'POST');
            }



            if (is_json($return)){
                $return=json_decode($return, true);
                if (isset($return['data']['idnumber'])){
                    $return['data']['idcard']=$return['data']['idnumber'];
//                    unset($return['data']['idnumber']);
                }else{
                    $return['data']['idcard']='';
                }



                if (isset($return['data']['usermember'])){
                    $return['data']['user']=$return['data']['usermember'];
                    unset($return['data']['usermember']);
                }else{
                    $return['data']['user']=$return['data']['user'];
                }

                if (!isset($return['data']['birthday'])){
                    $return['data']['birthday']='';
                }


                if ($return['code']=='-501'){
                    $return['code']=103;
                }

                if ($return['code'] == '-403'){
                    $return['code']=1012;
                }
                if ($return['code'] == 0){
                    $return['code']=200;
                }
                $code= (int)$return['code'];
                $return['code']=$code;
                $return=json_encode($return);
            }
            echo $return;
            exit;
        }else {
            returnjson(array('code'=>106), $this->returnstyle, $this->callback);
        }
    }









}
