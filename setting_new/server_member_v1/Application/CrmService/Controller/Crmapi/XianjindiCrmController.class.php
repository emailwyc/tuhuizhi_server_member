<?php
namespace CrmService\Controller\Crmapi;

use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;

class XianjindiCrmController extends CommonController implements CrminterfaceController
{
    //protected $request_url = 'http://117.48.194.215:8080/jcrm-server-card/rest';//测试地址
    protected $request_url = 'http://117.48.194.216:8080/jcrm-server-card/rest';//正式地址
    protected $rheader  = array('Content-Type: application/json; charset=utf-8');
    protected $rauth = "admin:www.hd123.com";
    protected $belongStore = '900101';
    protected $scoreType = "-";
    protected $baseConf;
    protected $timezoneStr;
    public function _initialize()
    {
        parent::_initialize();
        $this->returnstyle = true;
        $this->timezoneStr = date('Y-m-d\TH:i:s.000+0800',time());
        $this->baseConf = array(
            'operCtx'=>array(
                'time' => $this->timezoneStr,
                'operator' =>array(
                    'namespace' =>'西安微信服务号',
                    'id' =>'西安微信服务号',
                    'fullName' =>'西安微信服务号',
                ),
                'terminalId' =>'0001',
                'store' =>'900101',
            )
        );
    }

    /**
     * @deprecated 根据Openid获取会员信息
     * @传入参数   key_admin、sign、openid
     */
    public function GetUserinfoByOpenid(){}

    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     */
    public function GetUserinfoByCard(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','card'),array('card'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);

        $url = $this->request_url."/mbr/query";
        $reqParams = array(
            "conditions"=>array(array("operation"=>"cardNumEquals", "parameters"=>array($params['card']))),
            "orders" => array(array("field"=>"id", "direction"=>"asc")),
            "pageSize" => "10",
            "page"=>"0",
        );
        $memInfo = http_auth($url,json_encode($reqParams),'POST',$this->rauth,$this->rheader,true);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(0 == (int)$array['errorCode']){
                if(!empty($array['records'][0])){
                    $firstRd = $array['records'][0];
                    //查询卡号和积分
                    $cardInfo = $this->getCardInfoByCard($params['card']);
                    if(!cardInfo){
                        returnjson(array("code"=>104,"data"=>"没有找到相应卡资料"),$this->returnstyle,$this->callback);
                    }
                    $score = $this->getScoreByCard($cardInfo['cardNum']);

                    //查询卡号和积分
                    $rt = array();
                    $rt['cardno']=$cardInfo['cardNum'];
                    $rt['usermember']=$firstRd['name'];
                    $rt['idnumber']=@$firstRd['idCard']['id'];
                    $rt['status']=$cardInfo['state'];
                    $rt['status_description']='';
                    $rt['expirationdate']=$cardInfo['bytime'];//到期时间

                    $rt['birthday']=@$firstRd['birthday']['year']."-".@$firstRd['birthday']['month']."-".@$firstRd['birthday']['day'];
                    $rt['company']='';
                    $rt['phone']=@$firstRd['cellphone'];
                    $rt['mobile']=@$firstRd['cellphone'];
                    $rt['address']=@$firstRd['address'];
                    $rt['level']=@$firstRd['grade'];
                    $rt['remark']=@$firstRd['id'];
                    $rt['sex']= 'male'==@$firstRd['gender'] ? 1 : 0;
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $db->add($rt);
                    }else{
                        $db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    $datas['cardno']=$rt['cardno'];
                    $datas['xf_vipcardno']=$rt['cardno'];
                    $datas['cardtype']=@$firstRd['grade'];
                    $datas['name']=$rt['usermember'];
                    $datas['status']=$rt['status'];
                    $datas['status_description']='';
                    $datas['getcarddate']="";//创建时间
                    $datas['expirationdate']=$rt['expirationdate'];//到期时间
                    $datas['birth']=date("Y-m-d",strtotime($rt['birthday']));
                    $datas['company']='';
                    $datas['ismarry'] = @$firstRd['webLock']=="married"?2:1;
                    $datas['phone']=$rt['phone'];
                    $datas['idnumber']=@$firstRd['idCard']['id'];
                    $datas['mobile']=$rt['phone'];
                    $datas['address']=$rt['address'];
                    $datas['sex']=$rt['sex'];
                    $datas['score']=(float)$score;
                    $msg['code']=200;
                    $msg['data']=$datas;
                }else{
                    $msg['code']=101;
                }
            }else{
                $msg['code']=101;
                $msg['data'] = $array['message'];
            }

        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','cardno','scoreno','why'),array('cardno','scoreno'));
        $tranId = $params['cardno']."-".time().mt_rand(1000, 9999);
        $url = $this->request_url."/score/adjustScore";
        $params['scoreno'] = (float)(abs($params['scoreno']));
        $this->baseConf['request'] = array(
            "tranId"=>$tranId,
            "xid"=>$tranId,
            "account"=>array("type"=>"cardNum","id"=>$params['cardno']),
            "scoreRec"=>array(
                "scoreType"=>$this->scoreType,
                "scoreSubject"=>"储值",
                "score"=>number_format((string)$params['scoreno'],2,".",""),
            ),
            "remark"=>$params['why']
        );
        $result = http_auth($url,json_encode($this->baseConf),'POST',$this->rauth,$this->rheader,true);
        if(is_json($result)){
            $array=json_decode($result,true);
            if (0 == $array['errorCode']){
                $store=@I('store');
                $data = array();
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']=@$array['result']['flowNo'];
                $data['cutadd']=2;
                $data['datetime'] = date('Y-m-d H:i:s',time());
                $admininfo=$this->getMerchant($params['key_admin']);
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
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  创建会员
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name   (address\idnumber\birth)
     */
    public function createMember(){
        $params = I('param.');
        writeOperationLog($params,'soone');
        $this->paramsCheck($params,array('mobile','openid'),array('mobile','openid'));
        $params['idnumber'] = !empty($params['idnumber'])?$params['idnumber']:"";
        $params['address'] = !empty($params['address'])?$params['address']:"";
        $params['name'] = !empty($params['name'])?$params['name']:"";
        $params['sex'] = (0== $params['sex']) ? 0 : 1;
        $admininfo=$this->getMerchant($params['key_admin']);
        $url = $this->request_url."/card/openCard";
        $extid = $params['openid'].":".$params['mobile'];
        $this->baseConf['request'] = array(
            'extMember'=>array(
                "openId" => $extid,
                "cardId" => $extid,
            ),
            'member' =>array(
                "id"=> "",
                "name"=> @$params['name'],
                "gender"=> 1== $params['sex'] ? 'male' : 'female',
                "idCard" =>array('type'=>"idCard",'id'=>@$params['idnumber']),
                "cellphone"=> $params['mobile'],
                "belongStore"=>  $this->belongStore,//所属门店
                "wedLock"=> "secret",//婚姻状况
                "grade"=>  "",//会员等级代码
                "address"=> @$params['address'],
                "mobileChecked"=>  "false",
                "lastUpdateTime"=> $this->timezoneStr,
            ),
        );
        if($params['idnumber']){
            $this->baseConf['request']['member']['idCard'] = array('type'=>"idCard",'id'=>@$params['idnumber']);
            $birth = strlen($params['idnumber'])==15 ? ('19' . substr($params['idnumber'], 6, 6)) : substr($params['idnumber'], 6, 8);
            if(strlen($birth)!=8){
                returnjson(array('code'=>15,'msg'=>"身份证不合法！"),$this->returnstyle,$this->callback);
            }
            $year = substr($birth,0,4);
            $month = substr($birth,4,2);
            $day = substr($birth,6,2);
            $this->baseConf['request']['member']['birthday']=array(
                "year"=> $year,
                "month"=>$month,
                "day"=>$day
            );
        }
        $memInfo = http_auth($url,json_encode($this->baseConf),'POST',$this->rauth,$this->rheader,true);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            writeOperationLog($array,'soone');
            if(0 == (int)$array['errorCode'] && !empty($array['card'])){
                //检查该会员是否已经有
                $db=M('mem', $admininfo['pre_table']);
                $check = $db->where(array('cardno'=>$array['card']['cardNum']))->find();
                if(empty($check)) {
                    $d = array();
                    $d['cardno'] = $array['card']['cardNum'];
                    $d['openid'] = $params['openid'];
                    $d['datetime'] = date('Y-m-d H:i:s');
                    $d['usermember'] = $params['name'];
                    $d['idnumber'] = $params['idnumber'];
                    $d['expirationdate'] = @$array['card']['bytime'];
                    $d['getcarddate'] = date('Y-m-d H:i');
                    $d['birthday'] = date('Y-m-d',(int)@$params['birth']);
                    $d['phone'] = $params['mobile'];
                    $d['status'] = @$array['card']['state'];
                    $d['mobile'] = $params['mobile'];
                    $d['address'] = $params['address'];
                    $d['sex'] = $params['sex'];
                    $d['remark'] = @$array['card']['carrier'];
                    $add = $db->add($d);
                }
                $msg['code']=200;
                $list=array(
                    'cardno'=>$array['card']['cardNum'],
                    'usermember'=>$params['name'],
                    'getcarddate'=>date('Y-m-d'),
                    'expirationdate'=>'',
                    'mobile'=>$params['mobile'],
                    'sex'=>$params['sex'],
                    'idnumber'=>$params['idnumber']
                );
                $msg['data']=$list;
                //发送模板消息
            }else{
                $msg['code'] = 15;
                $msg['data'] = $array['message'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','cardno','scoreno','why'),array('cardno','scoreno'));
        $tranId = $params['cardno']."-".time().mt_rand(1000, 9999);
        $url = $this->request_url."/score/adjustScore";
        $params['scoreno'] = (float)(abs($params['scoreno'])*-1);
        $this->baseConf['request'] = array(
            "tranId"=>$tranId,
            "xid"=>$tranId,
            "account"=>array("type"=>"cardNum","id"=>$params['cardno']),
            "scoreRec"=>array(
                "scoreType"=>$this->scoreType,
                "scoreSubject"=>"消费",
                "score"=>number_format((string)$params['scoreno'],2,".",""),
            ),
            "remark"=>$params['why']
        );
        $result = http_auth($url,json_encode($this->baseConf),'POST',$this->rauth,$this->rheader,true);
        if(is_json($result)){
            $array=json_decode($result,true);
            if (0 == $array['errorCode']){
                $store=@I('store');
                $data = array();
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']=@$array['result']['flowNo'];
                $data['cutadd']=1;
                $data['datetime'] = date('Y-m-d H:i:s',time());
                $admininfo=$this->getMerchant($params['key_admin']);
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
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember(){
        $params = I('param.');
        writeOperationLog($params,'soone');
        $this->paramsCheck($params,array('key_admin','openid'),array('openid'));
        $params['idnumber'] = !empty($params['idnumber'])?$params['idnumber']:"";
        $params['address'] = !empty($params['address'])?$params['address']:"";
        $params['name'] = !empty($params['name'])?$params['name']:"";
        $params['sex'] = (0== $params['sex']) ? 0 : 1;
        $params['wedLock'] = (2== $params['ismarry']) ? "married" : "single";
        $params['ismarry'] = (2== $params['ismarry']) ? 2 : 1;
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);
        //查找会员标识；
        $memCheck = $db->where(array('cardno'=>$params['cardno']))->find();
        if(!$memCheck){
            //可先查询西安金地CRM系统确认是否拥有该会员并取的会员标识
            returnjson(array('code'=>104,'data'=>"根据卡号未找到会员信息"),$this->returnstyle,$this->callback);
        }
        $url = $this->request_url."/mbr/saveModify";
        $this->baseConf['member'] = array(
                "id"=> $memCheck['remark'],
                "name"=> $params['name'],
                "gender"=> 1== $params['sex'] ? 'male' : 'female',
                "webLock"=> $params['wedLock'],
                "belongStore"=>  $this->belongStore,//所属门店
                "address"=> @$params['address'],
                "mobileChecked"=>  "false",
                "lastUpdateTime"=> $this->timezoneStr,
        );
        if($params['idnumber']){
            $this->baseConf['member']['idCard'] = array('type'=>"idCard",'id'=>@$params['idnumber']);
            $birth = strlen($params['idnumber'])==15 ? ('19' . substr($params['idnumber'], 6, 6)) : substr($params['idnumber'], 6, 8);

            if(strlen($birth)!=8){
                returnjson(array('code'=>15,'msg'=>"身份证不合法！"),$this->returnstyle,$this->callback);
            }
            $year = substr($birth,0,4);
            $month = substr($birth,4,2);
            $day = substr($birth,6,2);
            $this->baseConf['member']['birthday']=array(
                "year"=> $year,
                "month"=>$month,
                "day"=>$day
            );
        }
        $memInfo = http_auth($url,json_encode($this->baseConf),'POST',$this->rauth,$this->rheader,true);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            writeOperationLog($array,'soone');
            if(0 == (int)$array['errorCode']){
                $d = array();
                $d['openid'] = $params['openid'];
                $d['usermember'] = $params['name'];
                $d['idnumber'] = $params['idnumber'];
                $d['birthday'] = date('Y-m-d',(int)@$params['birth']);
                $d['address'] = $params['address'];
                $d['sex'] = $params['sex'];
                $sv = $db->where(array('cardno'=>$params['cardno']))->save($d);
                $msg['code']=200;
                $datas = array();
                $datas['cardno']=$params['cardno'];
                $datas['usermember']=$params['name'];
                $datas['getcarddate']='';//创建时间
                $datas['ismarry'] = $params['ismarry'];
                $datas['expirationdate']='';//到期时间
                $datas['sex']=@$params['sex'];
                $datas['idnumber']=@$params['idnumber'];
                $msg['data']=$datas;
                //发送模板消息
            }else{
                $msg['code'] = 3000;
                $msg['data'] = $array['message'];
            }
        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile(){
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','mobile','openid'),array('mobile'));
        $admininfo=$this->getMerchant($params['key_admin']);
        $db=M('mem', $admininfo['pre_table']);

        $url = $this->request_url."/mbr/query";
        $reqParams = array(
            "conditions"=>array(array("operation"=>"cellphoneEquals", "parameters"=>array($params['mobile']))),
            "orders" => array(array("field"=>"id", "direction"=>"asc")),
            "pageSize" => "10",
            "page"=>"0",
        );
        $memInfo = http_auth($url,json_encode($reqParams),'POST',$this->rauth,$this->rheader,true);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(0 == (int)$array['errorCode']){
                if(!empty($array['records'][0])){
                    $firstRd = $array['records'][0];
                    //查询卡号和积分
                    $cardInfo = $this->getCardInfoByMem($firstRd['id']);
                    if(!cardInfo){
                        returnjson(array("code"=>104,"data"=>"根据会员没有找到相应卡资料"),$this->returnstyle,$this->callback);
                    }
                    $score = $this->getScoreByCard($cardInfo['cardNum']);

                    //查询卡号和积分
                    $rt = array();
                    $rt['cardno']=$cardInfo['cardNum'];
                    $rt['usermember']=$firstRd['name'];
                    $rt['idnumber']=@$firstRd['idCard']['id'];
                    $rt['status']=$cardInfo['state'];
                    $rt['status_description']='';
                    $rt['expirationdate']=$cardInfo['bytime'];//到期时间

                    $rt['birthday']=@$firstRd['birthday']['year']."-".@$firstRd['birthday']['month']."-".@$firstRd['birthday']['day'];
                    $rt['company']='';
                    $rt['phone']=@$firstRd['cellphone'];
                    $rt['mobile']=@$firstRd['cellphone'];
                    $rt['address']=@$firstRd['address'];
                    $rt['level']=@$firstRd['grade'];
                    $rt['remark']=@$firstRd['id'];
                    $rt['sex']= 'male'==@$firstRd['gender'] ? 1 : 0;
                    $sel=$db->where(array('cardno'=>$rt['cardno']))->find();
                    if (null == $sel){
                        $db->add($rt);
                    }else{
                        $db->where(array('cardno'=>$rt['cardno']))->save($rt);
                    }
                    $datas['cardno']=$rt['cardno'];
                    $datas['xf_vipcardno']=$rt['cardno'];
                    $datas['user']=$rt['usermember'];
                    $datas['cardtype']=@$firstRd['grade'];
                    $datas['status']=$rt['status'];
                    $datas['status_description']='';
                    $datas['getcarddate']="";//创建时间
                    $datas['expirationdate']=$rt['expirationdate'];//到期时间
                    $datas['birthday']=$rt['birthday'];
                    $datas['company']='';
                    $datas['ismarry'] = @$firstRd['webLock']=="married"?2:1;
                    $datas['phone']=$rt['phone'];
                    $datas['mobile']=$rt['phone'];
                    $datas['address']=$rt['address'];
                    $datas['sex']=$rt['sex'];
                    $datas['score']=(float)$score;
                    $msg['code']=200;
                    $msg['data']=$datas;
                }else{
                    $msg['code']=101;
                }
            }else{
                $msg['code']=101;
                $msg['data'] = $array['message'];
            }

        }else{
            $msg['code']=101;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist(){
        //目前西安金地没有相应接口，我们目前调用系统内部积分消费列表
        $params = I('param.');
        $this->paramsCheck($params,array('key_admin','cardno','startdate','enddate'),array('cardno'));
        $page = !empty($params['page'])?(string)(abs($params['page'])-1):"0";
        $page = $page<0?"0":$page;
        $offset = !empty($params['lines'])?(string)$params['lines']:"20";
        $url = $this->request_url."/score/queryScoreHst";
        $reqParams = array(
            "conditions"=>array(array("operation"=>"cardNumEquals", "parameters"=>array($params['cardno']))),
            "orders" => array(array("field"=>"tranTime", "direction"=>"desc")),
            "pageSize" => $offset,
            "page"=>$page,
        );
        $memInfo = http_auth($url,json_encode($reqParams),'POST',$this->rauth,$this->rheader,true);
        if(is_json($memInfo)){
            $array = json_decode($memInfo,true);
            if(0 == (int)$array['errorCode'] && !empty($array['records'])){
                $scorelist = array();
                if($array['paging']['page']==$page) {
                    foreach ($array['records'] as $k => $v) {
                        $regex = '/name=(.*)code=/';
                        $matches = array();
                        if(preg_match($regex, $v['remark'], $matches)){
                            if($matches['1']){
                                $v['remark'] = $matches['1'].$v['scoreSubject'];
                            }
                        }
                        $scorelist[] = array(
                            "date" => date("Y-m-d H:i:s", strtotime($v['tranTime'])),
                            "description" => $v['remark'],
                            "score" => $v['occurScore'],
                            "scoreType" => $v['scoreSubject']
                        );
                    }
                }
                $msg=array("code"=>200,"data"=>array(
                    'cardno'=>$params['cardno'],
                    'scorelist'=>$scorelist,
                ));
            }else{
                $msg=array("code"=>102);
            }
        }else{
            $msg=array("code"=>101);
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){}


    protected function getCardInfoByMem($carrier){
        $url = $this->request_url."/card/query";
        $reqParams = array(
            "conditions"=>array(array("operation"=>"carrierEquals", "parameters"=>array($carrier))),
            "pageSize" => "10",
            "page"=>"0",
        );
        $cardInfo = http_auth($url,json_encode($reqParams),'POST',$this->rauth,$this->rheader,true);
        if(is_json($cardInfo)){
            $array = json_decode($cardInfo, true);
            if (0 == (int)$array['errorCode'] && !empty($array['records'])) {
                return $array['records'][0];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    protected function getCardInfoByCard($card){
        $url = $this->request_url."/card/".$card;
        $cardInfo = http_auth($url,"",'GET',$this->rauth,$this->rheader);
        if(is_json($cardInfo)){
            $array = json_decode($cardInfo, true);
            if (0 == (int)$array['errorCode'] && !empty($array['card'])) {
                return $array['card'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    protected function getScoreByCard($cardno){
        $url = $this->request_url."/score/total/$cardno/cardNum";
        $scoreInfo = http_auth($url,array(),'GET',$this->rauth,$this->rheader);
        if(is_json($scoreInfo)){
            $array = json_decode($scoreInfo, true);
            if (0 == (int)$array['errorCode']) {
                return (int)@$array['value'];
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }


    protected function paramsCheck($params,$key_arr,$emptyArr=array()){
        if(!empty($key_arr)) {
            foreach ($key_arr as $v) {
                if (!isset($params[$v])) {
                    $msg['code'] = 1051;
                    returnjson($msg, $this->returnstyle, $this->callback);exit;
                }
            }
        }
        if(!empty($emptyArr)) {
            foreach ($emptyArr as $k) {
                if (empty($params[$k])) {
                    $msg['code'] = 1030;
                    returnjson($msg, $this->returnstyle, $this->callback);exit;
                }
            }
        }
    }

    /**
     * 解绑
     */
    public function UnBind(){}
    
}
