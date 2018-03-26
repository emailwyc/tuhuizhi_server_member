<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 22/09/2017
 * Time: 14:43
 */

namespace CrmService\Controller\Crmapi;


use Common\Controller\WebserviceController;
use CrmService\Controller\CommonController;
use CrmService\Controller\CrminterfaceController;

class AnhuahuicrmController extends CommonController implements CrminterfaceController
{
    private $securityUserId = 100;

    /**
     * 接口地址不是一个，所以没法定一个变量
     * http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices?wsdl
     * addorupdatecustomer,注册，修改会员
     * searchcustomers，获取会员基础信息，可以不用
     * retrieveCustomer 获取会员全部信息，可通用
     * http://183.233.189.84:8084/soap/AHH/v2_1/CardServices?wsdl
     * getcardinquirydata
     * http://183.233.189.84:8084/soap/AHH/v1_0/LoyaltyAccountServices?wsdl
     * issuepoints
     */



    /**
     * @deprecated 根据openid获取会员信息
     * @传入参数   key_admin、sign、openid
     *
     */
    public function GetUserinfoByOpenid()
    {
        // TODO: Implement GetUserinfoByOpenid() method.
    }

    /**
     * @deprecated 根据卡号获取会员信息
     * @传入参数   key_admin、sign、card
     *
     */
    public function GetUserinfoByCard()
    {
        // TODO: Implement GetUserinfoByCard() method.
        $params['cardno'] = I('card');
        $params['key_admin'] = I('key_admin');
        if(in_array('', $params)){
            returnjson(['code'=>1030],$this->returnstyle,$this->callback);
        }
        
        $xml = $this->memberInfo_XML('',$params['cardno']);
 
        $url = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
//         print_r($array);die;
//         writeOperationLog(array('httpreturndata' => $array), 'zhanghang');
        if (isset($array['S:Body']['ns2:retrieveCustomerResponse']['return']['CustomerID'])){
            
            $return_data = $this->InfoAction($array,$params,$ret);
            
            $msg['code'] = 200;
            $msg['data'] = $return_data;
        }else{
            $msg['code'] = 102;
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    public function memberInfo_XML($mem_id = '',$cardno = ''){
        $xml = <<<EOD
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://v2_3.customer.webservices.csx.dtv.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <v2:retrieveCustomer>
         <!--Optional:-->
         <customerId>{$mem_id}</customerId>
         <!--Zero or more repetitions:-->
         <alternateKeyList TypeCode="?">
            <AlternateID></AlternateID>
         </alternateKeyList>
         <!--Optional:-->
         <cardNumber>{$cardno}</cardNumber>
         <!--Optional:-->
    <!--这个东西虽然不知道什么意思,但是必须填写100-->
         <securityUserId>100</securityUserId>
      </v2:retrieveCustomer>
   </soapenv:Body>
</soapenv:Envelope>
EOD;
        return $xml;
    }
    
    
    public function InfoAction($array,$params,$xml){
        
        $xml_data = $this->xml_action($xml,array('HOME','OTHER'));
//         print_r($array);die;
        $return_data = $array['S:Body']['ns2:retrieveCustomerResponse']['return'];
        $data['cardno'] = $params['cardno']?$params['cardno']:$return_data['CustomerCards']['Instrument']['CardNumber'];
        $data['mobile'] = $return_data['EntityInformation']['Individual']['ContactInformation']['Telephone']['PhoneNumber'];
        $data['usermember'] = $return_data['EntityInformation']['Individual']['Name']['Name'][0]['@content'];
        $data['workaddress'] = $xml_data['HOME']?$xml_data['HOME']:'';
        $data['collectaddress'] = $xml_data['HOME']?$xml_data['HOME']:'';
        $data['sex'] = $return_data['EntityInformation']['Individual']['PersonalSummary']['GenderType'] == 'M' ? 1 : 0;
        $data['birthday'] = $return_data['EntityInformation']['Individual']['PersonalSummary']['BirthDate'];
        $data['member_id'] = $return_data['CustomerID'];
        
        $score_data = $this->score_value($data['cardno']);//获取积分
        $data['score'] = $score_data['score'];
        
        writeOperationLog(array('sqlcreateorupdatedata' => $data), 'zhanghang');
        $admininfo = $this->getMerchant($params['key_admin']);
        $db = M('mem',$admininfo['pre_table']);
        $infodata = $db->where(array('cardno'=>$data['cardno']))->find();
        
        if($infodata){
            $db->where(array('cardno'=>$data['cardno']))->save($data);
        }else{
            $db->add($data);
        }
        $data['name'] = $data['usermember'];
        $data['user'] = $data['usermember'];
        $data['birth'] = $data['birthday']?date('Y-m-d',strtotime($data['birthday'])):'';
        $data['birthday'] = $data['birthday']?date('Y-m-d',strtotime($data['birthday'])):'';
        $data['level'] = 1;//CRM没有卡等级
        $data['cardtype'] = 1;
        return $data;
    }
    
    public function xml_action($xml,$array = array()){
        $note_str = str_replace(array('S:','ns2:'),'',$xml);
        $xml=simplexml_load_string($note_str);
        //var_dump($xml);
        $xml_data = $xml->Body->retrieveCustomerResponse->return->EntityInformation->Individual->ContactInformation;
        $data = array();
        foreach($xml_data->Address as $a => $b)
        {
//             print_r($b->attributes());die;
            foreach($b->attributes() as $c=>$d){
                if($c == 'TypeCode'){
                    if(in_array($d, $array)){
                        $name = (string)$d;
                        $data[$name] = (string)$b->AddressLine1;
                    }
                }
            }
        }
        return $data;
    }
    
    /**
     * @deprecated 根据手机号获取会员信息
     * @传入参数  key_admin、sign、mobile
     */
    public function GetUserinfoByMobile()
    {
        // TODO: Implement GetUserinfoByMobile() method.
        // TODO: Implement GetUserinfoByCard() method.
        $params['mobile'] = I('mobile');
        $params['key_admin'] = I('key_admin');
        if(in_array('', $params)){
            returnjson(['code'=>1030],$this->returnstyle,$this->callback);
        }
        
        $xml = <<<EOD
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <ns2:searchCustomers xmlns:ns2="http://v2_3.customer.webservices.csx.dtv.com/">
      <customerData>
        <Instrument />
        <EntityInformation>
          <Individual>
            <Name />
            <ContactInformation>
              <Address>
              </Address>
              <EMail />
              <Telephone TypeCode="MOBILE">
                <PhoneNumber>{$params['mobile']}</PhoneNumber><!-- 手机号 -->
                <Extension />
              </Telephone>
            </ContactInformation>
          </Individual>
        </EntityInformation>
      </customerData>
      <securityUserId>100</securityUserId><!-- 固定的值100 -->
    </ns2:searchCustomers>
  </S:Body>
</S:Envelope>
EOD;
        
        $url = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices?wsdl';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        
        $array = xmlstr_to_array($ret);

        if($array['S:Body']['ns2:searchCustomersResponse']['return']['Customer']['CustomerID']){
            $mem_id = $array['S:Body']['ns2:searchCustomersResponse']['return']['Customer']['CustomerID'];
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
//         echo $mem_id;die;
        if ($mem_id){
            $xml_cardno = $this->memberInfo_XML($mem_id,'');
            
            $url_cardno = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices';
            $ret_cardno = http($url_cardno, $xml_cardno, 'POST', ['Content-Type:text/xml'], true);
            $array_cardno = xmlstr_to_array($ret_cardno);
            if (isset($array_cardno['S:Body']['ns2:retrieveCustomerResponse']['return']['CustomerID'])){
            
                $return_data = $this->InfoAction($array_cardno,$params,$ret_cardno);
            
                $msg['code'] = 200;
                $msg['data'] = $return_data;
            }else{
                $msg['code'] = 102;
            }
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
        $params['openid']=I('openid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030, 'data'=>'crm'),$this->returnstyle,$this->callback);
        }

        $sex = $params['sex'] ==1 ? 'M' : 'F';
        $params['workaddress'] = I('workaddress');
        $params['birth'] = I('birth');
        $params['birth'] = date('Y-m-d',strtotime($params['birth']));
        $params['collectaddress'] = I('collectaddress');
        
        $xml = <<<EOD
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <ns2:addOrUpdateCustomer xmlns:ns2="http://v2_3.customer.webservices.csx.dtv.com/">
      <customerData>
        <EntityInformation>
          <Individual>
            <Name>
              <!-- 姓名 -->
              <Name Location="First">{$params['name']}</Name>
            </Name>
            <ContactInformation>
     <!-- 送货地址 -->
              <Address TypeCode="HOME">
                <AddressLine1>{$params['collectaddress']}</AddressLine1>
              </Address>
     <!-- 工作区域 -->
              <Address TypeCode="WORK">
                <AddressLine1>{$params['workaddress']}</AddressLine1>
              </Address>
     <!-- 微信的OpenId 
              <Address TypeCode="OTHER">
                <AddressLine1></AddressLine1>
              </Address> 
      -->
     <!-- 手机号 -->
              <Telephone TypeCode="MOBILE">
                <PhoneNumber>{$params['mobile']}</PhoneNumber>
              </Telephone>
            </ContactInformation>
            <PersonalSummary>
     <!-- 性别 M:男 F:女-->
              <GenderType>{$sex}</GenderType>
     <!-- 生日日期-->
              <BirthDate>{$params['birth']}</BirthDate>
            </PersonalSummary>
          </Individual>
        </EntityInformation>
        <AlternateKey TypeCode="OPEN_ID">
               <AlternateID>{$params['openid']}</AlternateID>
        </AlternateKey>
  <!-- 车牌号
        <CustomAttribute name="CAR_NUMBER">
          <AttributeValue>322123</AttributeValue>
        </CustomAttribute> -->
      </customerData>
   <!-- 必填项（100） -->
      <securityUserId>100</securityUserId>
    </ns2:addOrUpdateCustomer>
  </S:Body>
</S:Envelope>
EOD;
//        echo $xml;
        $url = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
        if (isset($array['S:Body']['ns2:addOrUpdateCustomerResponse']['customerId'])){
            $userinfo = $this->getUserinfoByUserId($array['S:Body']['ns2:addOrUpdateCustomerResponse']['customerId']);
            if ($userinfo == false) {
                returnjson(array('code'=>101, 'data'=>'crm'),$this->returnstyle,$this->callback);
            }
            $admininfo = $this->getMerchant($params['key_admin']);
            $db = M('mem',$admininfo['pre_table']);
            $member_data = $db->where(array('mobile'=>$params['mobile']))->find();
            if($member_data){
                $add = $db->where(array('mobile'=>$params['mobile']))->save($userinfo);
            }else{
                $add = $db->add($userinfo);
            }
            if ($add){
                returnjson(['code'=>200, 'data'=>$userinfo],$this->returnstyle,$this->callback);
            }else{
                returnjson(['code'=>104, 'data'=>$add],$this->returnstyle,$this->callback);
            }
        }else{
            returnjson(['code'=>101],$this->returnstyle,$this->callback);
        }
//
//        $webservice= new WebserviceController($admininfo['pre_table']);
//        $url = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices?wsdl';
//        $client=$webservice->soapClient($url);
////        dump($client->__getFunctions());
////        dump($client->__getTypes());
//        $result= $webservice->sopaCall('addorupdatecustomer', $client, $data);
//        dump($result);


//        $data = [
//            0=>[
//                'customerData'=>[
//                    'RetailStoreID'=>'',
//                    'Instrument'=>[
//                        'CardNumber'=>34534,
//                        'CardSerialNumber'=>345
//                    ],
//                    'CustomerID'=>34553,
//                    'OrgName'=>'name',
//                    'CustOrgTypcode'=>34534,
//                    'EmployeeID'=>23543,
//                    'BusinessName'=>'adfad',
//                    'CustomerClass'=>435435,
//                    'Source'=>34534,
//                    'CustomerOwnerID'=>'',
//                    'CustomerNumber'=>'',
//                    'CustomerReference'=>'',
//                    'LastUpdateInfo'=>[
//                        'UpdateUserID'=>'',
//                        'UpdateDate'=>''
//                    ],
//                    'EntityInformation'=>[
//                        'Individual'=>[
//                            'Name'=>[
//                                'Name'=>''
//                            ],
//                            'Suffix'=>'',
//                            'SortingName'=>'',
//                            'NickName'=>'',
//                            'Salutation'=>'',
//                            'ContactInformation'=>[
//                                'Address'=>[
//                                    'AddressLine1'=>'',
//                                    'AddressLine2'=>'',
//                                    'AddressLine3'=>'',
//                                    'AddressLine4'=>'',
//                                    'ApartmentNumber'=>'',
//                                    'City'=>'',
//                                    'Country'=>'',
//                                    'County'=>'',
//                                    'Territory'=>'',
//                                    'PostalCode'=>''
//                                ]
//                            ],
//                            'PersonalSummary'=>[
//                                'GenderType'=>'',
//                                'BirthDate'=>'',
//                                'MaritalStatusCode'=>'',
//                                'Ethnicity'=>'',
//                                'Rent'=>'',
//                                'LanguageCode'=>''
//                            ],
//                            'SocioEconomicProfile'=>[
//                                'AnnualIncomeAmount'=>'',
//                                'NetWorth'=>'',
//                                'HighestEducationalLevelName'=>'',
//                            ]
//                        ],
//                    ],
//                    'PersonalPreferences'=>[
////                        'ContactPreference'=>[
//                        'ContactType',true
////                        ]
//                    ]
//                ]
//            ],
//            1=>['security'=>100]
//        ];

    }

    /**
     * @deprecated  修改会员信息
     * @传入参数  key_admin、sign、mobile、sex、idnumber、name、cardno
     */
    public function editMember()
    {
        // TODO: Implement editMember() method.
      
        $params['cardno'] = I('cardno');
        $params['key_admin'] = I('key_admin');
        if(in_array('', $params)){
            returnjson(array('code'=>1030, 'data'=>'crm'),$this->returnstyle,$this->callback);
        }
        
        $params['mobile'] = I('mobile'); 
        $params['birth'] = I('birth');
        $params['openid'] = I('openid');
        $params['name'] = I('name');
        $params['collectaddress'] = I('collectaddress');
        $params['workaddress'] = I('workaddress');
        $params['sex'] = I('sex');
        
        $admininfo = $this->getMerchant($params['key_admin']);
        $db = M('mem',$admininfo['pre_table']);
        
        $member_arr = $db->where(array('cardno'=>$params['cardno']))->find();
        
        if($member_arr['member_id'] == ''){
            returnjson(array('code'=>104, 'data'=>'member_id null'),$this->returnstyle,$this->callback);
        }
        
        $sex = $params['sex'] ==1 ? 'M' : 'F';
        $params['birth'] = date('Y-m-d',strtotime($params['birth']));
        $xml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <ns2:addOrUpdateCustomer xmlns:ns2="http://v2_3.customer.webservices.csx.dtv.com/">
      <customerData>
<!-- 用户ID -->
        <CustomerID>{$member_arr['member_id']}</CustomerID>
        <EntityInformation>
          <Individual>
            <Name>
              <Name Location="First">{$params['name']}</Name>
            </Name>
            <ContactInformation>
        <!-- 送货地址 --> 
              <Address  TypeCode="HOME">
                <AddressLine1>{$params['collectaddress']}</AddressLine1>
              </Address>
        <!-- 工作地址 -->
              <Address TypeCode="WORK">
                <AddressLine1>{$params['workaddress']}</AddressLine1>
              </Address>
        <!-- 微信的OpenId 
              <Address  TypeCode="OTHER">
                <AddressLine1>{$params['openid']}</AddressLine1>
              </Address>
         -->
              <Telephone TypeCode="MOBILE">
                <PhoneNumber>{$params['mobile']}</PhoneNumber>
              </Telephone>
            </ContactInformation>
            <PersonalSummary>
              <GenderType>{$sex}</GenderType>
              <BirthDate>{$params['birth']}</BirthDate>
            </PersonalSummary>
          </Individual>
        </EntityInformation>
<!-- 车牌号
        <CustomAttribute name="CAR_NUMBER">
          <AttributeValue>54110</AttributeValue>
        </CustomAttribute>
-->
      </customerData>
      <securityUserId>100</securityUserId>
    </ns2:addOrUpdateCustomer>
  </S:Body>
</S:Envelope>
EOD;
        $url = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
        if (isset($array['S:Body']['ns2:addOrUpdateCustomerResponse']['customerId'])){
            
//             $params['usermember'] = $params['name'];
//             unset($params['name']);
//             $params['birthday'] = $params['birth'];
//             unset($params['birth']);
//             $db->where(array('member_id'=>$params['member_id']))->save($params);

            returnjson(['code'=>200],$this->returnstyle,$this->callback);
        }else{
            returnjson(['code'=>101],$this->returnstyle,$this->callback);
        }
    }

    /**
     * @deprecated  积分扣除
     * @传入参数  key_admin、sign、cardno、scoreno、why
     */
    public function cutScore()
    {
        // TODO: Implement cutScore() method.
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']='-'.abs(I('scoreno'));
        $params['why']=I('why');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $return_data = $this->score_action($params);
        
        if(isset($return_data['S:Body']['ns2:issuePointsResponse']['return']['accountActivityId'])){
            $admininfo = $this->getMerchant($params['key_admin']);
            $db = M('score_record',$admininfo['pre_table']);
            
            $data['cardno']=$params['cardno'];
            $data['scorenumber']=abs($params['scoreno']);
            $data['why']=$params['why'];
            $data['scorecode']='';
            $data['cutadd']=1;
            $db->add($data);
            $msg['code'] = 200;
            $msg['data']['score'] = $return_data['S:Body']['ns2:issuePointsResponse']['return']['ytdPointsBalance'];
            $msg['data']['cardno'] = $params['cardno'];
        }else{
            $msg['code'] = 104;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    //积分增减方法
    public function score_action($params){
        
        $score_data = $this->score_value($params['cardno']);
        
        $xml = <<<EOT
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="http://v1_0.loyalty.webservices.csx.dtv.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <v1:issuePoints>
		 <!-- 卡号 -->
         <cardNumber>{$params['cardno']}</cardNumber>
		 <!-- 积分规则ID -->
         <lylAccountId>{$score_data['lylaccountid']}</lylAccountId>
		 <!--积分（正是加，负是减）-->
         <pointsAmount>{$params['scoreno']}</pointsAmount>
         <clientComments>{$params['why']}</clientComments>
         <clientUserId>100</clientUserId>
      </v1:issuePoints>
   </soapenv:Body>
</soapenv:Envelope>
EOT;
        $url = 'http://183.233.189.84:8084/soap/AHH/v1_0/LoyaltyAccountServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
        writeOperationLog(array('anhuahui_score_action_return_data' => $array), 'zhanghang');
        return $array;
    }
    
    /**
     * @deprecated  积分添加
     * @传入参数  key_admin、sign、cardno、scoreno、scorecode、why、membername
     */
    public function addintegral()
    {
        // TODO: Implement addintegral() method.
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        
        $return_data = $this->score_action($params);
        
        if(isset($return_data['S:Body']['ns2:issuePointsResponse']['return']['accountActivityId'])){
            $admininfo = $this->getMerchant($params['key_admin']);
            $db = M('score_record',$admininfo['pre_table']);
            
            $data['cardno']=$params['cardno'];
            $data['scorenumber']=abs($params['scoreno']);
            $data['why']=$params['why'];
            $data['scorecode']='';
            $data['cutadd']=2;
            $db->add($data);
            $msg['code'] = 200;
            $msg['data']['score'] = $return_data['S:Body']['ns2:issuePointsResponse']['return']['ytdPointsBalance'];
            $msg['data']['cardno'] = $params['cardno'];
        }else{
            $msg['code'] = 104;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    public function score_value($cardno){
        $xml = <<<EOT
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header/>
  <S:Body>
    <ns2:getCardInquiryData xmlns:ns2="http://v2_1.card.webservices.csx.dtv.com/">
      <cardNumber>{$cardno}</cardNumber>
      <currencyCode>CNY</currencyCode>
      <securityUserId>100</securityUserId>
    </ns2:getCardInquiryData>
  </S:Body>
</S:Envelope>
EOT;
        $url = 'http://183.233.189.84:8084/soap/AHH/v2_1/CardServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
        if(isset($array['S:Body']['ns2:getCardInquiryDataResponse']['return']['Instrument']['CardNumber'])){
            $data['score'] = round($array['S:Body']['ns2:getCardInquiryDataResponse']['return']['Instrument']['LoyaltyAccount']['PointsBalance']['Points'][0]['@content'],2);
            $data['cardno'] = $array['S:Body']['ns2:getCardInquiryDataResponse']['return']['Instrument']['CardNumber'];
            $data['lylaccountid'] = $array['S:Body']['ns2:getCardInquiryDataResponse']['return']['Instrument']['LoyaltyAccount']['LoyaltyAccountID'];
        }else{
            $data['score'] = 0;
            $data['cardno'] = $cardno;
            $data['lylaccountid'] = '';
        }
        
        return $data;
    }
    
    /**
     * @deprecated 用户积分详细列表
     */
    public function scorelist()
    {
        // TODO: Implement scorelist() method.
        $cardno=I('cardno');
        $time_Begin=I('startdate');
        $time_end=I('enddate');
        
        $xml = <<<EOT
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <ns2:getLoyaltyAccountHistory xmlns:ns2="http://v1_0.loyalty.webservices.csx.dtv.com/">
      <cardNumber>{$cardno}</cardNumber>
      <currencyCode>CNY</currencyCode>
      <historyStartDate>{$time_Begin}</historyStartDate>
      <historyEndDate>{$time_end}</historyEndDate>
    </ns2:getLoyaltyAccountHistory>
  </S:Body>
</S:Envelope>
EOT;
        
        $url = 'http://183.233.189.84:8084/soap/AHH/v1_0/LoyaltyAccountServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
        if(isset($array['S:Body']['ns2:getLoyaltyAccountHistoryResponse']['return']['Instrument']['CardNumber'])){
            
            $return_data = $array['S:Body']['ns2:getLoyaltyAccountHistoryResponse']['return'];
            
            foreach($return_data['Instrument']['LoyaltyAccount']['LoyaltyActivityList']['LoyaltyActivity'] as $k=>$v){
                if($v['TransactionType'] != 'Inquiry'){
                    $data['description']=$v['Comments'];
                    $data['date']=$v['BusinessDate'];
                    $data['score']=$v['NumPoints'];
                    $msg_data[] = $data;
                }
            }
            
            $return_score = $this->score_value($cardno);
            
            $msg['code'] = 200;
            $msg['data']['scorelist'] = $data;
            $msg['data']['score_num'] = $return_score['score'];
            $msg['data']['cardno'] = $cardno;
        }else{
            $msg['code'] = 102;
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);
        
    }

    /**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo()
    {
        // TODO: Implement billInfo() method.
    }



    private function getUserinfoByUserId($userid = false)
    {
        if (!$userid){
            return false;
        }
        $xml = <<<EOD
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://v2_3.customer.webservices.csx.dtv.com/">
   <soapenv:Header/>
   <soapenv:Body>
      <v2:retrieveCustomer>
         <!--Optional:-->
         <customerId>{$userid}</customerId>
         <!--Optional:-->
         <securityUserId>100</securityUserId>
      </v2:retrieveCustomer>
   </soapenv:Body>
</soapenv:Envelope>
EOD;
        $url = 'http://183.233.189.84:8084/soap/AHH/v2_3/CustomerServices';
        $ret = http($url, $xml, 'POST', ['Content-Type:text/xml'], true);
        $array = xmlstr_to_array($ret);
        if (isset($array['S:Body']['ns2:retrieveCustomerResponse']['return'])){
            $data['name'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['EntityInformation']['Individual']['Name']['Name'][0]['@content'];
            $data['mobile'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['EntityInformation']['Individual']['ContactInformation']['Telephone']['PhoneNumber'];
            $data['workaddress'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['EntityInformation']['Individual']['ContactInformation']['Address'][1]['AddressLine1'];
            $data['collectaddress'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['EntityInformation']['Individual']['ContactInformation']['Address'][0]['AddressLine1'];
            $data['sex'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['EntityInformation']['Individual']['PersonalSummary']['GenderType'] == 'M' ? 1 : 0;
            $data['birthday'] = date('Y-m-d', strtotime($array['S:Body']['ns2:retrieveCustomerResponse']['return']['EntityInformation']['Individual']['PersonalSummary']['BirthDate']) );
            $data['cardno'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['CustomerCards']['Instrument']['CardNumber'];
            $data['cardserialnumber'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['CustomerCards']['Instrument']['CardSerialNumber'];
            $data['cardtype'] = 1;//crm没有卡等级
            $data['member_id'] = $array['S:Body']['ns2:retrieveCustomerResponse']['return']['CustomerID'];
            return $data;
        }else{
            return false;
        }
    }

    
    public function addintegralbyopenid()
    {
        // TODO Auto-generated method stub
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        $params['score']=abs((int)I('score'));
        $params['why']=I('why');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $params1 = array('openid'=>array('eq',$params['openid']));
            
            $admininfo = $this->getMerchant($params['key_admin']);
            $db = M('mem',$admininfo['pre_table']);
            $info_data = $db->where($params1)->find();
            
            if(!$info_data){
                returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit;
            }
            
            $params['scoreno']=$params['score'];
            $params['cardno'] = $info_data['cardno'];
            $return_data = $this->score_action($params);
        
            if(isset($return_data['S:Body']['ns2:issuePointsResponse']['return']['accountActivityId'])){
                $admininfo = $this->getMerchant($params['key_admin']);
                $db = M('score_record',$admininfo['pre_table']);
                
                $data['cardno']=$params['cardno'];
                $data['scorenumber']=abs($params['scoreno']);
                $data['why']=$params['why'];
                $data['scorecode']='';
                $data['cutadd']=2;
                $db->add($data);
                $msg['code'] = 200;
                $msg['data']['score'] = $return_data['S:Body']['ns2:issuePointsResponse']['return']['ytdPointsBalance'];
                $msg['data']['cardno'] = $params['cardno'];
            }else{
                $msg['code'] = 104;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 解绑
     */
    public function UnBind(){}
}