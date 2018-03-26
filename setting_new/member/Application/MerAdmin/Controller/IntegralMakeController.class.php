<?php
/*
 * 积分补录
 */
namespace MerAdmin\Controller;
use MerAdmin\Service\ShoppingReceiptService;
use PublicApi\Controller\QiniuController;
class IntegralMakeController extends AuthController
{
    
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        
        parent::_initialize();
        //查询商户信息
        $this->admin_arr=$this->getMerchant($this->ukey);        	
        $this->key_admin=$this->ukey;
        
    }
    
    //积分补录列表
    public function integral_list(){           
        //$this->key_admin;
        $params['page']=I('page');
        $export=I('export');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $starttime=I('starttime');
            $stoptime=I('endtime');
            $stat = I('status');
            //$cardno=I('cardno');
            //$user_mobile=I('user_mobile');
            $where='';
            //查询条件
            if (!empty($starttime)){
//                $starttime=date('Y-m-d', strtotime("$starttime -1 day"));
                $where .= ' `createtime` > "'.$starttime.'" ';
            }
            if (!empty($stoptime)){
                $stoptime=date('Y-m-d', strtotime("$stoptime +1 day"));
                if ($where != ''){
                    $where .= ' and ';
                }
                $where .= ' `createtime` < "'.$stoptime.'"';
            }
            if (!empty($stat)){
                if ($where != ''){
                    $where .= ' and ';
                }
                $where .= ' `status` = "'.$stat.'"';
            }
           /*
            if (!empty($cardno)){
                if ($where != ''){
                    $where .= ' and ';
                }
                $where .= ' `cardno` = "'.$cardno.'"';
            }

            if (!empty($user_mobile)){
                if ($where != ''){
                    $where .= ' and ';
                }
                $where .= ' `user_mobile` = "'.$user_mobile.'"';
            }
           */
            $db=M('score_type',$this->admin_arr['pre_table']);
            $re_arr=$db->where($where)->select();
            $lines=10;
            $count=ceil(count($re_arr)/$lines);
            
            if($params['page']==0){
                $params['page']=1;
            }else if($params['page']>$count){
                $params['page']=$count;
            }

            $end=($params['page']-1)*$lines;
            $end = 0 > $end ? 0 : $end;
            if ($export == 1){
                $res=$db->where($where)->order('createtime desc')->select();//如果是导出，则查询所有结果，不分页
                $title='申请时间,会员卡号,用户,状态,积分,金额,门店名称,订单号,审核时间,设置人';
                $csvarr=array(0=>$title);
                foreach ($res as $key => $val){
                    if ($val['status'] == 1){//1、等待审核2、审核通过3、审核失败
                        $status='等待审核';
                    }elseif ($val['status'] == 2){
                        $status='审核通过';
                    }elseif ($val['status'] == 3){
                        $status='审核失败';
                    }
                    $str=$val['createtime'].','.$val['cardno'].','.$val['user_mobile'].','.$status.','.$val['score_number'].','.$val['money'].','.$val['store'].','.$val['ordernumber'].','.$val['opertime'].','.$val['backend_user'];
                    array_push($csvarr, $str);
                }
                $return=CreateCsvFile($csvarr, RUNTIME_PATH.'score_export/','csv');//正确返回路径
                if($return !== false){
                    $time = date("Ymd");
                    $uniqid = uniqid();
                    $key = 'score_export_'.$time.'_'.$uniqid.'.csv';
                    $qiniu=new QiniuController();
                    list($ret, $err)=$qiniu->uploadfile($return,$key);
                    unlink($return);
                    if ($err !== null) {
                        $msg['code']=104;
                    }else{
                        $msg['code']=200;
                        $msg['data']=array('path'=>"https://img.rtmap.com/".$key);
                    }
                }else{
                    $msg['code']=104;
                }
            }else{
                $res=$db->where($where)->limit($end,$lines)->order('createtime desc')->select();
                if($res){
                    $res=$this->array_action($res);
                    $msg['code']=200;
                    $msg['data']['data']=$res;
                    $msg['data']['page']=$params['page'];//当前页数
                    $msg['data']['count']=$count;//页数
                    $msg['data']['total']=count($re_arr);
                }else{
                    $msg['code']=102;
                }
            }

            
        }       
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();  
    }
    
    //修改积分补录每条状态
    public function integral_type(){
        $params['id']=I('type_id');
        $params['status']=I('status');//状态
        $params['store']=html_entity_decode(I('store'));
        $params['money']=I('money');
        if($params['status']==3){//审核不通过
            //$arr['ordernumber']='';
            //$arr['score_number']='';
            $params['order_number']=I('order_number');//订单号
            if(in_array('', $params)){
                $msg['code']=1019;
            }else{
                $arr['store']=$params['store'];
                $arr['money']=$params['money'];
                $arr['status']=$params['status'];
                $arr['ordernumber']=$params['order_number'];
                $arr['opertime']=date('Y-m-d H:i:s');
                $arr['backend_user']=$this->admin_arr['describe'];
                $db=M('score_type',$this->admin_arr['pre_table']);
                $res=$db->where(array('id'=>$params['id']))->save($arr);
                if($res !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }
            
        }else if ($params['status']==2){//审核成功
            $params['order_number']=I('order_number');//订单号
            $params['score_number']=I('score_number');//积分数
            $params['cardno']=I('cardno');//卡号
            $params['username']=I('username');//用户名
            //print_R($params);die;
            if(in_array('',$params)){
                $msg['code']=1030;
            }else{
                
                $db=M('score_type',$this->admin_arr['pre_table']);
                $red_score_type=$db->where(array('ordernumber'=>$params['order_number']))->find();
                if($red_score_type){//查询订单号是否存在
                    if($red_score_type['id']==$params['id']){
                        $score_add['key_admin']=$this->ukey;
                        $score_add['cardno']=$params['cardno'];
                        $score_add['scoreno']=$params['score_number'];
                        $score_add['why']='小票补录积分';
                        $score_add['scorecode']=$params['order_number'];
                        $score_add['membername']=$params['username'];
                        $score_add['sign_key']=$this->admin_arr['signkey'];
                        $score_add['store']=$params['store'];
                        $sign=sign($score_add);
                        $score_add['sign']=$sign;
                        unset($score_add['sign_key']);
                        $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
                        $return_res=http($url,$score_add);
                        if(is_json($return_res)){
                            $return_arr=json_decode($return_res,true);
                            if($return_arr['code']==200){
                                $arr['ordernumber']=$params['order_number'];
                                $arr['score_number']=$params['score_number'];
                                $arr['status']=$params['status'];
                                $arr['opertime']=date('Y-m-d H:i:s');
                                $arr['backend_user']=$this->admin_arr['describe'];
                                $arr['store']=$params['store'];
                                $arr['money']=$params['money'];
                                $res=$db->where(array('id'=>$params['id']))->save($arr);
                                $msg['code']=200;
                            }else{
                                $msg=$return_arr;
                            }
                        }
                    }else{
                        $msg['code']=1008;
                    }
                }else{
                    $score_add['key_admin']=$this->ukey;
                    $score_add['cardno']=$params['cardno'];
                    $score_add['scoreno']=$params['score_number'];
                    $score_add['why']='小票补录积分';
                    $score_add['scorecode']=$params['order_number'];
                    $score_add['membername']=$params['username'];
                    $score_add['sign_key']=$this->admin_arr['signkey'];
                    $score_add['store']=$params['store'];
                    $sign=sign($score_add);
                    $score_add['sign']=$sign;
                    unset($score_add['sign_key']);
                    $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
                    $return_res=http($url,$score_add);
                    if(is_json($return_res)){
                        $return_arr=json_decode($return_res,true);
                        if($return_arr['code']==200){
                            $arr['ordernumber']=$params['order_number'];
                            $arr['score_number']=$params['score_number'];
                            $arr['status']=$params['status'];
                            $arr['opertime']=date('Y-m-d H:i:s');
                            $arr['backend_user']=$this->admin_arr['describe'];
                            $arr['store']=$params['store'];
                            $arr['money']=$params['money'];
                            $res=$db->where(array('id'=>$params['id']))->save($arr);
                            $msg['code']=200;
                        }else{
                            $msg=$return_arr;
                        }
                    }
                }
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    //获取单个信息
    public function get_integral_one(){
        
        $params['id']=I('id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('score_type',$this->admin_arr['pre_table']);
            $re_arr=$db->where(array('id'=>$params['id']))->find();
            if($re_arr){
                if($re_arr['ordernumber'] == null){
                    $re_arr['ordernumber']='';
                }
                if($re_arr['opertime'] == null){
                    $re_arr['opertime']='';
                }
                if($re_arr['score_number'] == null){
                    $re_arr['score_number']='';
                }
                if($re_arr['backend_user'] == null){
                    $re_arr['backend_user']='';
                }
                $msg['code']=200;
                $msg['data']=$re_arr;
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    public function array_action($arr){
        foreach($arr as $k=>$v){
            if($v['ordernumber'] == null){
                $arr[$k]['ordernumber']='';
            }
            if($v['opertime'] == null){
                $arr[$k]['opertime']='';
            }
            if($v['score_number'] == null){
                $arr[$k]['score_number']='';
            }
            if($v['backend_user'] == null){
                $arr[$k]['backend_user']='';
            }
        }
        return $arr;
    }
    
    //添加积分补录记录
    public function integral_save(){
        $params['mobile']=I('mobile');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['store']=I('store');
            $params['money']=I('money');
            $params['order_number']=I('order_number');
            $params['score_number']=I('score_number');
            $pre_score_db=M('score_type',$this->admin_arr['pre_table']);
            //查询数据库中有无此订单号
            $find=$pre_score_db->where(array('ordernumber'=>$params['order_number']))->find();
            if ($find != null){
                returnjson(array('code'=>1008), $this->returnstyle, $this->callback);
            }
            $mem_db=M('mem',$this->admin_arr['pre_table']);
            $map['mobile']=array('eq',$params['mobile']);
            $user=$mem_db->where($map)->find();
            if($user){
//             $path=$this->uploadfile_integral();
//             if($path['code']==200){

//                 $data['img_src']=$path['data'];
                $data['createtime']=date('Y-m-d H:i:s');
                $data['status']=1;
                $data['user_mobile']=$params['mobile'];//用户手机号
                $data['username']=$user['usermember'];//用户名称
                $data['cardno']=$user['cardno'];//用户卡号
                $data['money']=$params['money'];//金额
                $data['store']=html_entity_decode($params['store']);
                $data['ordernumber']=$params['order_number'];
                $data['score_number']=$params['score_number'];
//                 print_r($data);die;
                $score_type_res=$pre_score_db->add($data);
                if($score_type_res){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
//             }else{
//                 $msg['code']=$path['code'];
//             }
            }else{
                $msg['code']=2000;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    //获取门店名称
    public function integral_getstore(){
        $params['name']=I('name');
        if(!isset($params['name'])){
			echo returnjson(array('code'=>1030,'msg'=>"参数错误"),$this->returnstyle,$this->callback);exit;
		}else{
			$build_db=M('buildid','total_');
			//查询建筑物id
			$buildInfo = $build_db->where(array('adminid'=>$this->admin_arr['id']))->find();
			if(empty($buildInfo['buildid'])){
				echo returnjson(array('code'=>11,'msg'=>"未找到对应建筑物"),$this->returnstyle,$this->callback);exit;
			}else{
				$buildDefault = explode(',',$buildInfo['buildid']);
			}
			$suffix = "map_poi_".$buildDefault[0];
			//查看表是否存在
			$storedb = $build_db->db(2,"DB_CONFIG2");
			$tab = $this->admin_arr['pre_table'].$suffix;
			$c=$storedb->execute('SHOW TABLES like "'.$tab.'"');
			if (1!=$c){ echo returnjson(array('code'=>12,'msg'=>"未找到对应建筑物"),$this->returnstyle,$this->callback);exit; }
			//查找相关匹配内容
			if(empty($params['name'])){
				$res = $storedb->query("select floor,poi_no,poi_name from $tab limit 100");
			}else{
				$res = $storedb->query("select floor,poi_no,poi_name from $tab where poi_name like '%".$params['name']."%' limit 100");
			}
			$res = empty($res)?array():$res;
        }
		echo returnjson(array('code'=>200,'data'=>$res),$this->returnstyle,$this->callback);exit();
    }
    
    
     protected function uploadfile_integral(){
        $file=$_FILES['UpLoadFile'];
        $name = $file['name'];
        $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
        $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
        if(!in_array($type, $allow_type)){
            $msg['code']=104;
            $mag['data']='文件类型错误！';
        }
        $path=RUNTIME_PATH.'wechat/fans/'.time().".".$type;//上传文件的存放路径
        if(move_uploaded_file($file['tmp_name'],$path)){
            //echo "Successfully!";
            $time = date("Ymd");
            $uniqid = uniqid();
            $key = 'wecaht_'.$time.'_'.$uniqid.'.'.$type;
            $qiniu=new QiniuController;
            list($ret, $err)=$qiniu->uploadfile($path,$key);
            unlink($path);
            if ($err !== null) {
                $msg['code']=104;
            }else{
                $msg['code']=200;
                $msg['data']="https://img.rtmap.com/".$key;
            }
        }else{
            $msg['code']=104;
        }
        return $msg;
    }









    /**
     * 小票返利设置条件
     * 1、积分设置
     * 2、优惠券设置
     * 3、抽奖设置
     */

    public function shopreceivesettings()
    {
//        dump($_POST);
        //赠送配置，送多张券，还是送单张券
        $givetype = I('givetype');
        if (!is_numeric($givetype) || !in_array($givetype, [1,2])) {//1代表返一张券，按总价算，2代表返多张券，按实际购物的单品总价算
            returnjson(array('code'=>1051, 'data'=>'type_c'),$this->returnstyle,$this->callback);
        }

        $params['rulename'] = I('rulename');
        $params['starttime'] = I('starttime');
        $params['endtime'] = I('endtime');
        $params['isopenscore'] = I('isopenscore');
        $params['isopencoupon'] = I('isopencoupon');
        //一些基本信息的判断
        if (!is_numeric($params['starttime']) ||
            !is_numeric($params['endtime']) ||
            !is_numeric($params['isopenscore']) ||
            !is_numeric($params['isopencoupon']) ||
            $params['starttime'] < time() ||
            $params['endtime'] < $params['starttime'] ||
            !in_array($params['isopenscore'] ,[0,1]) ||
            !in_array($params['isopencoupon'], [0,1])
            ) {
            returnjson(array('code'=>1051, 'data'=>'type_c'),$this->returnstyle,$this->callback);
        }

        $params['couponsetting'] = I('couponsetting');//优惠券设置

        if ($params['isopencoupon'] == 1){//如果赠送优惠券开启，则必须配置优惠券
            if ($params['couponsetting'] == false || !is_array($params['couponsetting'])) {
                returnjson(array('code'=>1051, 'data'=>'coupon_c'),$this->returnstyle,$this->callback);
            }
        }



        $params['key_admin'] = I('key_admin');

        //下面四项因需求更改，不再处理，但为了不更改调用的代码，依然接收和传递，但调用的shopReceipt静态方法不再使用下面三接收到的字段
        $params['scoresetting'] = I('scoresetting');
        $params['birthdaytimes'] = I('birthdaytimes');
        $params['timetotime'] = I('timetotime');
        $params['prizesetting'] = I('prizesetting');

        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::shopReceipt($params['scoresetting'], $params['couponsetting'], $params['prizesetting'], $params['birthdaytimes'], $params['timetotime'], $adminInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 获取小票返利信息
     */
    public function showshopreceive()
    {
        $params['key_admin'] = I('key_admin');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::getShoppingReceiptSettings($adminInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 扫码返利列表
     */
    public function scanshopreceipt()
    {
        $params['key_admin'] = I('key_admin');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $params['search'] = I('search');
        $params['pagenum'] = I('pagenum');
        $params['line'] = I('line');
        $data = ShoppingReceiptService::scanShoppingReceiptList($adminInfo, $params['search'], $params['pagenum'], $params['line']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 小票返利设置
     */
    public function shopreceiptrebatesettings()
    {
        $params['key_admin'] = I('key_admin');
        $params['rulename'] = I('rulename');//规则名称
        $params['starttime'] = I('starttime');//开始时间戳
        $params['endtime'] = I('endtime');//结束时间戳
        $params['isopenscore'] = I('isopenscore');//是否返积分
        $params['isopencoupon'] = I('isopencoupon');//是否返券
        $params['isrepeatedlycoupon'] = I('isrepeatedlycoupon');//是否循环返券
        $params['isclass'] = I('isclass');//是否区分品类

        if (in_array('', $params, true)){
            returnjson(['code'=>1030, 'data'=>['y'=>'app']],$this->returnstyle,$this->callback);
        }
        //循环设置
        $params['settings'] = I('settings') ? I('settings') : [];//赠送券设置，值必须是多维数组，每维内根据是否循环返券和是否区分品类不同，值也不同
        $params['id'] = I('id');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::scanShoppingReceiptSettings($adminInfo, $params['rulename'], $params['starttime'], $params['endtime'], $params['isopenscore'], $params['isopencoupon'], $params['isrepeatedlycoupon'], $params['isclass'], $params['settings'], $params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 获取单个小票设置
     */
    public function getshoppingreceiptsettingsinfo()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        if (in_array('', $params, true)){
            returnjson(['code'=>1030, 'data'=>['y'=>'app']],$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::getShoppingReceiptSettingsInfo($adminInfo, $params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 获取扫码配置信息列表
     */
    public function getshoppingreceiptsettingslist()
    {
        $params['key_admin'] = I('key_admin');
        $params['rulename'] = I('rulename');
        $params['starttime'] = I('starttime');
        $params['endtime'] = I('endtime');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::getShoppingReceiptSettingsList($adminInfo, $params['rulename'], $params['starttime'], $params['endtime']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     *删除单条扫码配置
     */
    public function destroyshoppingreceiptsetting()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        if (in_array('', $params, true)){
            returnjson(['code'=>1030, 'data'=>['y'=>'app']],$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::destroyShoppingReceiptSetting($adminInfo, $params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 小票返利公共配置：规则，抽奖图标，或许以后还会有
     */
    public function receiptpublicsettings()
    {
        $params['rule'] = I('rule');
        $params['icon'] = I('icon');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params, true)){
            returnjson(['code'=>1030, 'data'=>['y'=>'app']],$this->returnstyle,$this->callback);
        }

        $adminInfo = $this->getMerchant($params['key_admin']);
        $data = ShoppingReceiptService::receiptPublicSetting($params['rule'], $params['icon'], $adminInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }





































    
}
?>
