<?php
namespace CrmService\Controller\Crmapi;

use Org\Util\Stringnew;
use CrmService\Controller\CrminterfaceController;
use CrmService\Controller\CommonController;

class HuiyuechengcrmController extends CommonController implements CrminterfaceController
{ 
	
	/*
	*创建会员
	*/
	public function createMember(){
		$params['key_admin']=I('key_admin');
		$sign=I('sign');
		$params['name']=I('name');
		$params['mobile']=I('mobile');//手机号
		if(in_array('',$params)){//获取的参数不完整
			$msg['code']=1030;
		}else{
			
			if(isset($_POST['sex']) || isset($_GET['sex'])){
				$params['sex']= I('sex');
			}
			if(isset($_POST['idnumber']) || isset($_GET['idnumber'])){
				$params['idnumber']=I('idnumber');//身份证号
			}
// 			if(false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
// 				$msg['code']=1002;
// 			}else{
				$params['birth']=I('birth');
				$params['sex']=I('sex');
				$string=new Stringnew();
				$openid=rand(10,999).substr(time(),rand(1,5)).$string->randString(6,2,rand(1,99));

				if($params['birth']){
					$dat=date('Ymd',$params['birth']);
				}else{
					$dat='';
				}

				if($params['sex']== '1'){
					$sex='男';
				}else{
					$sex='女';
				}
				
				
				$res='[\]
FOPENID='.$openid.'
FCARDID=0101
FMBRNAME='.$params['name'].'
FMBRSEX='.$sex.'
FMBRBIRTH='.$dat.'
FMBRMOBILEPHONE='.$params['mobile'].'';
				
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'WXOPENCARD','sParams'=>$res);

				$return_res=$this->fun_currency($query_arr);
				//print_R($return_res);die;
				$sql['huiyuecheng_create_return']=$return_res;
				writeOperationLog($sql,'zhanghang');
				
				if($return_res['FRESULT']=='0'){
					$arr['cardno']=$return_res['FCARDNUM'];
					$arr['usermember']=$params['name'];
					$arr['getcarddate']=date('YmdHis');
					$arr['expirationdate']='';
					$arr['mobile']=$params['mobile'];
					$arr['sex']=$sex;
					$arr['idnumber']=$params['idnumber'];

					//记录入库
                    $data['datetime']=date('Y-m-d H:i:s');
                    $data['usermember']=$params['name'];
                    $data['idnumber']=$params['idnumber'];
                    $data['getcarddate']=date('Y-m-d');  	
                	$data['sex']=$sex;
					$data['birthday']=$dat;
					$admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('mem',$admininfo['pre_table']);

					$arr1=$db->where('mobile='.$params['mobile'])->find();
					if($arr1){
						$add=$db->where('mobile='.$params['mobile'])->save($data);
					}else{
						$data['cardno']=$arr['cardno'];
						$data['phone']=$params['mobile'];
						$data['mobile']=$params['mobile'];
						$add=$db->add($data);
					}
                    //print_r($params);die;
					//记录入库结束
					$sql['createMember']=$db->_sql();
					writeOperationLog($sql,'zhanghang');

					$msg['code']=200;
					$msg['data']=$arr;
				}else{
					$msg['code']=104;
				}
			
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*修改会员信息
	*/
	public function editMember(){
		$params['key_admin']=I('key_admin');
		$sign=I('sign');
		//$params['sex']=I('sex');
	//	$params['name']=I('name');
		$params['mobile']=I('mobile');//手机号
		$params['cardno']=I('cardno');
		$params['name']= I('name');
		//print_R($params);die;
		//$params['birth']=I('birth');//会员生日
		if(in_array('',$params)){//获取的参数不完整
			$msg['code']=1030;
		}else{
			//$birth=$params['birth'];
			//unset($params['birth']);
			
			$params['idnumber']=I('idnumber');
				
// 			if(false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				$params['sex']= I('sex');
				$birth=I('birth');//会员生日
				if($params['sex']=='1'){
					$sex='男';
				}else{
					$sex='女';
				}
				if($birth){
					$dat=date('Ymd',$birth);
				}else{
					$dat='';
				}
				$res='[\]
FACCOUNTNO='.$params['cardno'].'
FMOBILEPHONE='.$params['mobile'].'
FEMAILADR=
FNAME='.$params['name'].'
FSEX='.$sex.'
FBIRTH='.$dat.'
FADDRESS=';
				
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'UPDATEMBRINFO','sParams'=>$res);
				
				$return_res=$this->fun_currency($query_arr);
				//print_r($return_res);die;
				$sql['huiyuecheng_edit_return']=$return_res;
				writeOperationLog($sql,'zhanghang');
				
				if($return_res['Result']== '0'){
					$arr['usermember']=$params['name'];
					$arr['getcarddate']=date('YmdHis');
					$arr['expirationdate']='';
					$arr['mobile']=$params['mobile'];
					$arr['sex']=$params['sex'];
					//$arr['idnumber']=$params['idnumber'];
					$arr['cardno']=$params['cardno'];
				}

				if($arr){
					
					//修改库里操作
					$admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('mem',$admininfo['pre_table']);
					//$rt['phone']=$params['mobile'];
                    $rt['sex']=$sex;
					//$rt['mobile']=$params['mobile'];
					$rt['idnumber']=$params['idnumber'];
                    $rt['usermember']=$params['name'];
                    $sel=$db->where(array('cardno'=>$params['cardno']))->find();
                    if (null == $sel){
                        $rt['cardno']=$params['cardno'];
                        $sv=$db->add($rt);
                    }else{
                        $sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
                    }
					//修改结束
					//echo $db->_sql();die;
					
					$sql['editMember']=$db->_sql();
					writeOperationLog($sql,'zhanghang');


					$msg['code']=200;
					$msg['data']=$arr;
				}else{
					$msg['code']=104;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*根据卡号获取会员信息
	*/
	public function GetUserinfoByCard(){
		$params['key_admin']=I('key_admin');
        $params['card']=I('card');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				$res=array('FCARDNUM'=>$params['card'],'FMOBILEPHONE'=>'','FQUERYTYPE'=>0,'FOPENID'=>'');
				
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'QUERYMEMBERINFOJSON','sParams'=>json_encode($res));
				
				$return_res=$this->fun_currency($query_arr);
				
				if($return_res['FRESULT']== '0'){
					$arr['cardno']=$return_res['FCARDNUM'];//卡号
                    $arr['user']=$return_res['FMEMNAME'];//会员卡用户名
                    $arr['name']=$return_res['FMEMNAME'];//会员卡用户名
                    $arr['cardtype']=$return_res['FCARDTYPE'];//会员卡类别
					$arr['status']=$return_res['FCARDSTAT'];//会员卡状态
					$arr['status_description']='';//会员卡详细状态
					$arr['getcarddate']=$return_res['FCARDMAKETIME'];//会员卡创建日期
					$arr['expirationdate']='';//会员卡到期日期
                    $arr['birthday']=date('Y-m-d',strtotime($return_res['FMEMBIRTH']));//会员生日
                    $arr['birth']=date('Y-m-d',strtotime($return_res['FMEMBIRTH']));//会员生日
                    $arr['company']=$return_res['FMEMCOMPANY'];//会员公司信息
					$arr['phone']=$return_res['FMEMMOBILEPHONE'];//会员手机号
					$arr['mobile']=$return_res['FMEMMOBILEPHONE'];//会员手机号
					$arr['address']=$return_res['FMEMADDRESS'];//会员地址
					$arr['score']=$return_res['FCARDTOTALSCORE'];//会员积分
					if($return_res['FMEMSEX']== '男' ){
						$sex=1;
					}else{
						$sex=0;
					}
					$arr['sex']=$sex;//会员性别
					
					//入库操作
					$admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('mem',$admininfo['pre_table']);
                    $rt['usermember']=$return_res['FMEMNAME'];
                    $rt['status']=$return_res['FCARDSTAT'];
                    $rt['status_description']='';
                    $rt['getcarddate']=$return_res['FCARDMAKETIME'];
                    $rt['expirationdate']='';//到期时间
                    $rt['birthday']=$return_res['FMEMBIRTH'];
                    $rt['company']=$return_res['FMEMCOMPANY'];
                    $rt['phone']=$return_res['FMEMMOBILEPHONE'];
                    $rt['mobile']=$return_res['FMEMMOBILEPHONE'];
                    $rt['address']=$return_res['FMEMADDRESS'];
					$rt['score_num']=$return_res['FCARDTOTALSCORE'];//会员积分
					$rt['sex']=$sex;
					$sel=$db->where(array('cardno'=>$return_res['FCARDNUM']))->find();
                    if (null == $sel){
						$rt['cardno']=$return_res['FCARDNUM'];
                        $sv=$db->add($rt);
                    }else{
                        $sv=$db->where(array('cardno'=>$return_res['FCARDNUM']))->save($rt);
                    }
					//入库操作结束
						
					$sql['GetUserinfoByCard']=$db->_sql();
					writeOperationLog($sql,'zhanghang');

					$msg['code']=200;
					$msg['data']=$arr;

				}else{
					$msg['code']=102;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*根据手机号获取会员信息
	*/
	public function GetUserinfoByMobile(){
		$params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				$res=array('FCARDNUM'=>'','FMOBILEPHONE'=>$params['mobile'],'FQUERYTYPE'=>1,'FOPENID'=>'');
				
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'QUERYMEMBERINFOJSON','sParams'=>json_encode($res));
				
				$return_res=$this->fun_currency($query_arr);
				//print_r($return_res);die;
				if($return_res['FRESULT']== '0'){

					$arr['cardno']=$return_res['FCARDNUM'];//卡号
                    $arr['user']=$return_res['FMEMNAME'];//会员卡用户名
                    $arr['name']=$return_res['FMEMNAME'];//会员卡用户名
                    $arr['cardtype']=$return_res['FCARDTYPE'];//会员卡类别
					$arr['status']=$return_res['FCARDSTAT'];//会员卡状态
					$arr['status_description']='';//会员卡详细状态
					$arr['getcarddate']=$return_res['FCARDMAKETIME'];//会员卡创建日期
					$arr['expirationdate']='';//会员卡到期日期
                    $arr['birthday']=date('Y-m-d',strtotime($return_res['FMEMBIRTH']));//会员生日
                    $arr['birth']=date('Y-m-d',strtotime($return_res['FMEMBIRTH']));//会员生日
                    $arr['company']=$return_res['FMEMCOMPANY'];//会员公司信息
					$arr['phone']=$return_res['FMEMMOBILEPHONE'];//会员手机号
					$arr['mobile']=$return_res['FMEMMOBILEPHONE'];//会员手机号
					$arr['address']=$return_res['FMEMADDRESS'];//会员地址
					$arr['score']=$return_res['FCARDTOTALSCORE'];//会员积分
					if($return_res['FMEMSEX']== '男' ){
						$sex=1;
					}else{
						$sex=0;
					}
					$arr['sex']=$sex;//会员性别

					//入库操作
					$admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('mem',$admininfo['pre_table']);
                    $rt['usermember']=$return_res['FMEMNAME'];
                    $rt['status']=$return_res['FCARDSTAT'];
                    $rt['status_description']='';
                    $rt['getcarddate']=$return_res['FCARDMAKETIME'];
                    $rt['expirationdate']='';//到期时间
                    $rt['birthday']=$return_res['FMEMBIRTH'];
                    $rt['company']=$return_res['FMEMCOMPANY'];
                    $rt['phone']=$return_res['FMEMMOBILEPHONE'];
                    $rt['mobile']=$return_res['FMEMMOBILEPHONE'];
                    $rt['address']=$return_res['FMEMADDRESS'];
					$rt['score_num']=$return_res['FCARDTOTALSCORE'];//会员积分
					$rt['sex']=$sex;
					$sel=$db->where(array('cardno'=>$return_res['FCARDNUM']))->find();
                    if (null == $sel){
						$rt['cardno']=$return_res['FCARDNUM'];
                        $sv=$db->add($rt);
                    }else{
                        $sv=$db->where(array('cardno'=>$return_res['FCARDNUM']))->save($rt);
                    }	
					//入库操作结束
					
					$sql['GetUserinfoByMobile']=$db->_sql();
					writeOperationLog($sql,'zhanghang');

					$msg['code']=200;
					$msg['data']=$arr;

				}else{
					$msg['code']=102;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*积分扣除
	*/
	public function cutScore(){
		$params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{
				$int_code=$this->int_code('cutScore',$params['why']);
				$string=new Stringnew();
				$uuid=rand(1,999).substr(time(),rand(1,5)).$string->randString(6,2,rand(1,999));
				$dat=date('Y.m.d H:i:s');
				$res='[\]
FUUID='.$uuid.'
FCARDNUM='.$params['cardno'].'
FCOUNT=1
FXID=zhihuitu
FFilDate='.$dat.'
FPosNo=zhihuitu
FPayAmount=0

[FSCORESORT1]
CODE=-
SUBJECT='.$int_code.'
SCORE='.$params['scoreno'];	
			
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'CRMSAVESCORE','sParams'=>$res);
					
				$return_res=$this->fun_currency($query_arr);
			
				foreach($return_res as $k=>$v){
					if($v['FRESULT']== '0'){
						$arr['cardno']=$params['cardno'];//卡号
						$arr['scorenumber']=$params['scoreno'];//扣除积分数
						$arr['why']=$params['why'];//扣除理由
						$arr['scorecode']=$uuid;//编码
					}
				}

				if($arr){

					$data['cardno']=$params['cardno'];
                    $data['scorenumber']=$params['scoreno'];
                    $data['why']=$params['why'];
                    $data['scorecode']=$uuid;
                    $data['cutadd']=1;
					$admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('score_record',$admininfo['pre_table']);
                    $add=$db->add($data);
					

					$msg['code']=200;
					$msg['data']=$arr;
				}else{
					$msg['code']=104;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	/*
	*积分添加
	*/
	public function addintegral(){
		$params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $params['scoreno']=abs(I('scoreno'));
        $params['why']=I('why');
        $scorecode=I('scorecode');
        $params['membername']=I('membername');
        $sign=I('sign');
        if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
            $store=I('store');
			$params['scorecode']=$scorecode;
			//print_R($params);die;
//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
//             }else{
				$int_code=$this->int_code('addintegral',$params['why']);
				$string=new Stringnew();
				$uuid=rand(1000,9999).$string->randString(6,2,'zhihuitu');
				$dat=date('Y.m.d H:i:s');
				$res='[\]
FUUID='.$uuid.'
FCARDNUM='.$params['cardno'].'
FCOUNT=1
FXID=zhihuitu
FFilDate='.$dat.'
FPosNo=zhihuitu
FPayAmount=0

[FSCORESORT1]
CODE=-
SUBJECT='.$int_code.'
SCORE='.$params['scoreno'];	
			
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'CRMSAVESCORE','sParams'=>$res);
					
				$return_res=$this->fun_currency($query_arr);
				
				foreach($return_res as $k=>$v){
					if($v['FRESULT']== '0'){
						$code=200;
					}
				}
				
				if($code){

					$data['cardno']=$params['cardno'];
                    $data['scorenumber']=$params['scoreno'];
                    $data['why']=$params['why'];
                    $data['scorecode']=$params['scorecode'];
                    $data['cutadd']=2;
					$admininfo=$this->getMerchant($params['key_admin']);
                    $db=M('score_record',$admininfo['pre_table']);
                    $data['store']=$store?$store:'';
					$add=$db->add($data);

					$msg['code']=200;
				}else{
					$msg['code']=104;
				}

// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	//登陆
	public function operation_login(){
		$arr=array('sOper'=>'123[123]','sStoreCode'=>'0101','sWorkStation'=>'999','sUserGid'=>'1000286','sUserPwd'=>'328CBC3E742BCFAF','sClientCookie'=>'');//配置登录信息

		$client=new \SoapClient("http://218.14.157.14:8800/HDCRMWebService.dll/wsdl/IHDCRMWebService") or die("Error");

		$response = $client->__call('LogIn',$arr) or die("Error");//调用登录接口

		if($response['return']==1){
			return $response['sClientCookie'];
		}else{
			return false;
		}
	}

	//公共调用接口方法
	public function fun_currency($query_arr){
		$client=new \SoapClient("http://218.14.157.14:8800/HDCRMWebService.dll/wsdl/IHDCRMWebService") or die("Error");
		
		$response=$client->__call('DoClientCommand',$query_arr) or die("Error");//调用接口
		
		//判断是否为json形式，如果不是则对ini格式进行处理
		if(!is_json($response['sOutParams'])){
		//处理ini格式的数据
			$str=substr($response['sOutParams'],3);

			//print_r($response);die;

			$res=parse_ini_string($str,true);
			
			return $res;

		}else{
			return json_decode($response['sOutParams'],true);
		}
	}
	
	/*
	*积分明细
	*/
	public function scorelist(){
		$params['key_admin']=I('key_admin');
		$params['cardno']=I('cardno');
		$params['startdate']=I('startdate');
		$params['enddate']=I('enddate');
		$sign=I('sign');
		//dump($params);die;
		if (in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else {
			$params['page']=I('page');
			$params['lines']=I('lines');

//             if (false==$this->sign($params['key_admin'], $params, $sign)){//签名错误
//                 $msg['code']=1002;
// 			}else{
				$params['startdate']=date('Y.m.d',$params['startdate']);
				$params['enddate']=date('Y.m.d',$params['enddate']);
				//echo json_encode($params);die;
				//print_r($params);die;
				//echo "<br />";
				$res='[\]
FCARDNUM='.$params['cardno'].'
FBEGINDATE='.$params['startdate'].'
FENDDATE='.$params['enddate'];
				$query_arr=array('sClientCookie'=>$this->operation_login(),'sCommand'=>'QueryScoreHst','sParams'=>$res);
					
				$return_res=$this->fun_currency($query_arr);
				//dump($return_res);die;

				//echo json_encode($return_res);die;
				if($return_res['FRESULT']=='0'){
					$count=$return_res['FCOUNT'];

					$score_arr=array(105,107,108,109,203,209);

					for($i=1;$i<=$count;$i++){

						$arr[$i-1]['date']=$return_res['FSCODTL'.$i]['FilDate'];

						if(in_array($return_res['FSCODTL'.$i]['ScoreSubject'],$score_arr)){
							$arr[$i-1]['description']=$this->code_list($return_res['FSCODTL'.$i]['ScoreSubject']);
							$arr[$i-1]['score']='-'.$return_res['FSCODTL'.$i]['Score'];
						}else{
							$arr[$i-1]['description']=$this->code_list($return_res['FSCODTL'.$i]['ScoreSubject']);
							$arr[$i-1]['score']=$return_res['FSCODTL'.$i]['Score'];
						}

					}

					$data['cardno']=$params['cardno'];
					$data['scorelist']=$arr;

					$msg['code']=200;
					$msg['data']=$data;
				}else{
					$msg['code']=102;
				}
// 			}
		}
		echo returnjson($msg,$this->returnstyle,$this->callback);
	}


	public function int_code($fun,$why){
		if($fun=='cutScore'){
			$code=107;
		}else{
			if($why=='签到送积分'){
				$code=208;
			}else{
				$code=101;
			}
		}
		return $code;
	}

	public function code_list($int_code){
		$score_arr=array('101'=>'增加积分',
			'102'=>'由储值而增加的积分',
			'103'=>'由于积分促销而增加的积分',
			'104'=>'由于进行人工调整而增加或减少的积分',
			'105'=>'由于进行积分的转移而减少的积分',
			'106'=>'由于进行积分的转移而增加的积分',
			'107'=>'兑奖扣除积分',
			'108'=>'年末积分清理而扣除的积分',
			'109'=>'参加抽奖而扣除的积分',
			'201'=>'登录系统奖励的积分',
			'202'=>'参与评价奖励的积分',
			'203'=>'积分支付扣除的积分',
			'204'=>'开卡的时候奖励的积分',
			'205'=>'注册的时候系统奖励的积分',
			'206'=>'参与便民活动系统奖励的积分',
			'207'=>'储值转成积分的积分',
			'208'=>'签到积分',
			'209'=>'积分转储值系统扣除的积分',
		);

		return $score_arr[$int_code];
	}
	
	
	/**
     * @deprecated 欧亚卖场
     * @传入参数 key_admin、sign 、skt、Jlbh、md
     */
    public function billInfo(){
        
    }
    public function GetUserinfoByOpenid(){

    }
    
    /**
     * 解绑
     */
    public function UnBind(){}
}
?>