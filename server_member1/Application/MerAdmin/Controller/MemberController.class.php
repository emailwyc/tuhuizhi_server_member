<?php
namespace MerAdmin\Controller;
use PublicApi\Controller\QiniuController;
/**
 * 自有CRM后台
 * @author zhanghang
 *
 */
class MemberController extends AuthController
{
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
        $this->admin_arr=$this->getMerchant($this->ukey);
    }
    
    //会员接口导出
    public function member_export(){
        $res=$this->member_actions('export');
//         print_r($res);die;
        if($res['code']==200){
            $str[]="用户,会员卡号,性别,等级,状态,积分,注册时间";
            foreach($res['data']['data'] as $k=>$v){
                if($v['status']==1){
                    $status="有效";
                }else{
                    $status="冻结";
                }
                $sex=$v['sex']==1?"男":"女";
                $str[]=$v['usermember'].",".$v['cardno'].",".$sex.",".$v['level'].",".$status.",".$v['score_num'].",".$v['getcarddate'];
            }
            $return=CreateCsvFile($str,RUNTIME_PATH.'wechat/fans/','csv');
            if($return){
                $time = date("Ymd");
                $uniqid = uniqid();
                $key = 'fans_'.$time.'_'.$uniqid.'.csv';
                $qiniu=new QiniuController;
                list($ret, $err)=$qiniu->uploadfile($return,$key);
                unlink($return);
                if ($err !== null) {
                    $msg['code']=104;
                }else{
                    $msg['code']=200;
                    $msg['data']=array('path'=>"https://img.rtmap.com/".$key);
                }
            }else{
                $msg['code']=$return['code'];
            }
        }else{
            $msg['code']=$res['code'];
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    
    /**
     * 获取会员等级
     */
    public function MemberLevelList(){
        $db=M('member_code','total_');
	    $where['admin_id']=array('eq',$this->admin_arr['id']);
	    $arr=$db->where($where)->order('sort asc')->select();
	    if($arr){
	        foreach($arr as $k=>$v){
	            $res['id']=$v['id'];
	            $res['level']=$v['name'];
	            $res['code']=$v['code'];
	            $data[]=$res;
	        }
	        $msg['code']=200;
	        $msg['data']=$data;
	    }else{
	        $msg['code']=102;
	    }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /*
     * 新建自有crm卡等级
     */
    public function CreateMemberLevel()
    {
        $params['level']=I('level');
        $params['code']=I('code');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->ukey, 'member_level');
        if ($find){
            $array=json_decode($find['function_name'], true);
            //判断code和level是否已经存在
            $codes=array_column($array, 'code');
            if (in_array($params['code'], $codes)){
                returnjson(array('code'=>1008), $this->returnstyle, $this->callback);
            }
            $levels=array_column($array, 'level');
            if (in_array($params['level'], $levels)){
                returnjson(array('code'=>1008), $this->returnstyle, $this->callback);
            }
            //如果判断通过

            $ids=array_column($array, 'id');
            //求最大值
            $maxid=$ids[0];
            foreach ($ids as $key => $val){
                if ($val > $maxid){
                    $maxid=$ids[$key];
                }
            }
            $id=$maxid+1;//最大值加1
            $arr=array(
                'id'=>$id,
                'level'=>$params['level'],
                'code'=>$params['code']
            );
            array_push($array, $arr);
            $newjson=json_encode($array);
        }else{
            $find=array(
                'id'=>1,
                'level'=>$params['level'],
                'code'=>$params['code']
            );
            $newjson=json_encode($find);
        }
        $db = M('default', $this->admin_arr['pre_table']);
        $sel=$db->where(array('customer_name'=>'member_level'))->find();
        if ($sel){
            $save=$db->where(array('customer_name'=>'member_level'))->save(array('function_name'=>$newjson));
        }else{
            $save=$db->add(array('customer_name'=>'member_level','function_name'=>$newjson));
        }

        if ($save !== false){
            $this->redis->del('admin:default:one:member_level:'. $this->ukey);
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }


    /**
     * 编辑crm会员卡等级
     */
    public function EditCardLevel()
    {
        $params['id']=I('id');
        $params['level']=I('level');
        $params['code']=I('code');
        $params['key_admin']=I('key_admin');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->ukey, 'member_level');
        if ($find) {
            $array = json_decode($find['function_name'], true);
            //判断code和level是否已经存在
            foreach ($array as $key => $value){
                //如果
                if ($value['code'] == $params['code'] && $value['id'] != $params['id']){
                    returnjson(array('code' => 1008), $this->returnstyle, $this->callback);
                    break;
                }
                if ($value['level'] == $params['level'] && $value['id'] != $params['id']){
                    returnjson(array('code' => 1008), $this->returnstyle, $this->callback);
                    break;
                }

                if ($value['id'] == $params['id'] && $value['code'] == $params['code'] && $value['level'] == $params['level']){
                    returnjson(array('code' => 1034), $this->returnstyle, $this->callback);
                    break;
                }
            }
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('default', $admininfo['pre_table']);
            $find=$db->where(array('customer_name'=>'member_level'))->find();
            $data=json_decode($find['function_name'], true);
            foreach ($data as $key => $value){
                if ($value['id'] == $params['id']){
                    $data[$key]['code']=$params['code'];
                    $data[$key]['level']=$params['level'];
                }
            }
            $datajson=json_encode($data);
            $save=$db->where(array('customer_name'=>'member_level'))->save(array('function_name'=>$datajson));
            $this->redis->del('admin:default:one:member_level:'.$params['key_admin']);
            if ($save !==false){
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }

    }


    /**
     * 删除指定卡等级
     */
    public function DelCardLevel()
    {
        $id=I('id');
        if ($id == ''){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $find=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->ukey, 'member_level');
        if ($find){
            $arr=json_decode($find['function_name'], true);
            foreach ($arr as $key => $value) {
                if ($value['id'] == $id){
                    unset($arr[$key]);//删除指定id，得到结果
                    break;
                }
            }
            $arr=array_values($arr);
            $db = M('default', $this->admin_arr['pre_table']);
            $save=$db->where(array('customer_name'=>'member_level'))->save(array('function_name'=>json_encode($arr)));
            if ($save !== false){
                $this->redis->del('admin:default:one:member_level:'. $this->ukey);
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
            returnjson($msg, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
    }



    /**
     * 会员搜索后分页
     */
    public function user_lists(){
        $params['page']=I('page')<1?1:I('page');
        $params['lines']=I('lines')?I('lines'):10;
        $params['key_admin']=I('key_admin');
        $params['usermember']=I('usermember');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('mem',$this->admin_arr['pre_table']);
            $start=($params['page']-1)*$params['lines'];
            $map['_string'] ='(usermember like "%'.$params['usermember'].'%") or cardno like "%'.$params['usermember'].'%" or mobile like "%'.$params['usermember'].'%"';
            $map['is_del']=array('NEQ',2);
            $map['_logic']='and';
            $sel=$db->field('id,cardno,usermember,idnumber,sex,getcarddate,expirationdate,birthday,phone,is_del as status,score_num,headerimg,address,career,wechat,star,remark,openid,email,level')->where($map)->limit($start,$params['lines'])->select();
            //echo $db->_sql();die;
            if (false != $sel){
                $num=$db->where($map)->select();
//                 echo $db->_sql();die;
                $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'member_level');
                $arr_level=json_decode($return['function_name'],true);
                foreach($arr_level as $k=>$v){
                    $arr[$v['id']]=$v['level'];
                }
                foreach($sel as $k=>$v){
                    $sel[$k]['level']=$arr[$v['level']];
                }
                $total=count($num);
                $pagenum=ceil($total/$params['lines']);
                $msg['code']=200;
                $msg['data']=array(
                    'data'=>$sel,
                    'total'=>(int)$total,
                    'pagenum'=>(int)$pagenum,
                    'page'=>(int)$params['page']
                );
            }else {
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    /**
     * 会员列表读取 V
     */
    public function Lists()
    {
        $msg=$this->member_actions('list');
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    public function member_actions($status){
        $params['page']=I('page')<1?1:I('page');
        $params['lines']=I('lines')?I('lines'):10;
        $params['key_admin']=I('key_admin');
        $start_time=I('start_time')?date('Y-m-d H:i:s',strtotime(I('start_time'))):'';//如果不传入，查询最近一个月的明细
        $end_time=I('end_time')?date('Y-m-d H:i:s',strtotime(I('end_time'))+60*60*24):date('Y-m-d H:i:s',time());
        $birth_sttime=I('birth_sttime')?strtotime(I('birth_sttime')):'';//生日开始日期
        $birth_endtime=I('birth_endtime')?strtotime(I('birth_endtime')):time();//生日结束日期
        $level=I('level');
        $arr['status']=I('status');
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=1030;
        }else{
            if($start_time){
                $map=array('getcarddate'=>array('between',$start_time.','.$end_time));
            }
            if($birth_sttime){
                $map=array('birthday'=>array('between',array($birth_sttime,$birth_endtime)));
            }
            if($level){
                $map['level']=array('EQ',$level);
            }
            if(!in_array('', $arr)){
                $map['is_del']=array('EQ',$arr['status']);
            }else{
                $map['is_del']=array('NEQ',2);
            }
            
            $db=M('mem',$this->admin_arr['pre_table']);
            $start=($params['page']-1)*$params['lines'];
            $map['_logic']='and';
            if($status== 'export'){
                $sel=$db->field('id,cardno,usermember,idnumber,sex,getcarddate,expirationdate,phone,is_del as status,score_num,headerimg,address,career,wechat,star,remark,openid,email,level')->where($map)->order('id desc')->select();
            }else{
                $sel=$db->field('id,cardno,usermember,idnumber,sex,getcarddate,expirationdate,phone,is_del as status,score_num,headerimg,address,career,wechat,star,remark,openid,email,level')->where($map)->order('id desc')->limit($start,$params['lines'])->select();
            }
//             echo $db->_sql();die;
            if (false != $sel){
        
                $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'member_level');
                $arr_level=json_decode($return['function_name'],true);
                foreach($arr_level as $k=>$v){
                    $arr[$v['id']]=$v['level'];
                }
                foreach($sel as $k=>$v){
                    $sel[$k]['level']=$arr[$v['level']];
                }
                if($status != 'export'){
                    $num=$db->where($map)->select();
                    $total=count($num);
                    $pagenum=ceil($total/$params['lines']);
                    $msg['code']=200;
                    $msg['data']=array(
                        'data'=>$sel,
                        'total'=>(int)$total,
                        'pagenum'=>(int)$pagenum,
                        'page'=>(int)$params['page']
                    );
                }else{
                    $msg['code']=200;
                    $msg['data']=array(
                        'data'=>$sel,
                    );
                }
            }else {
                $msg['code']=102;
            }
        }
        return $msg;
        
    }
    /**
     * 会员详情
     */
    public function showMember()
    {
        $params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=1030;
        }else{
            $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'backend_update_json');
            $default_arr=json_decode($return['function_name'],true);
            $db=M('mem',$this->admin_arr['pre_table']);
            $map['cardno']=array('eq',$params['cardno']);
            $map['is_del']=array('NEQ',2);
            $map['_logic']='and';
            $find=$db->field('cardno,usermember as name,idnumber as idcard,sex,getcarddate,expirationdate,birthday,mobile,is_del,score_num,headerimg,address,career,wechat,star,remark,openid,email,level,province,city,district')->where($map)->find();
            //echo $db->_sql();die;
            if (null != $find){
                $find['birth']=$find['birthday']?date('Y-m-d',$find['birthday']):"";
                $find['province']=$find['province'] != 0?$find['province']:'';
                $find['city']=$find['city'] != 0?$find['city']:'';
                $find['district']=$find['district'] != 0?$find['district']:'';
                //print_r($default_arr);
                //print_r($find);die;
                foreach($default_arr as $k=>$v){
                    if($v['fromtype'] != 'select'){
                        $default_arr[$k]['default']=$find[$v['type']];
                    }else{
                        foreach($v['option'] as $ks=>$vs){
                            $default_arr[$k]['option'][$ks]['default']=$find[$vs['type']];
                        }
                    }  
                }
               // print_r($default_arr);die;
                $msg['code']=200;
                $msg['data']=array(
                    'data'=>$default_arr
                );
            }else{
                $msg['code']=102;
            }
        }
        //dump($msg['data']['data']);die;
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     *
     *创建会员，直接调用创建会员方法
     */
    public function createMember()
    {
//         $params['key_admin']=I('key_admin');
//         $params['mobile']=I('mobile');
//         $params['idnumber']=I('idnumber');
//         $params['name']=I('name');
//         $db=M('default',$this->admin_arr['pre_table']);
//         $default_json=$db->where(array('customer_name'=>'backend_create_json'))->field('function_name')->find();
//         $default_arr=json_decode($default_json['function_name'],true);
        $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'backend_create_json');
        $default_arr=json_decode($return['function_name'],true);
        foreach($default_arr as $k=>$v){
            if($v['fromtype'] != 'select'){
                if($v['required']){
                    $params1[$v['type']]=I($v['type']);
                }else{
                    $params2[$v['type']]=I($v['type']);
                }
            }else{
                foreach($v['option'] as $k=>$vs){
                    if($vs['required']){
                        $params1[$vs['type']]=I($vs['type']);
                    }else{
                        $params2[$vs['type']]=I($vs['type']);
                    }
                }          
            }
        }
        $msg=$this->commonerrorcode;
        //print_r($params1);
        if (in_array('', $params1)){
            $msg['code']=1030;
        }else{
            $params=array_merge($params1,$params2);
            $params=apptocrmkeys($params,C('BACKEND_CRM_OUTPUT_INPUT_UPDATE'));
            $params['key_admin']=$this->ukey;
            $params['birth']=strtotime($params['birth']);
            $params['address']=$params['address']==''?'|':$params['address'];
            $params['customer_name']='backend_create_json';
            $params['sign_key']=$this->admin_arr['signkey'];
            $params['sign']=sign($params);  
            unset($params['sign_key']);
            //print_r($params);die;
            $url=C('DOMAIN').'/CrmService/OutputApi/Index/createMember';
            //print_r($param);
            $result=http($url,$params);
            //dump($result);die;
            if (is_json($result)){
                $array=json_decode($result,true);
                $sql['member_crm_return']=$array;
                writeOperationLog($sql,'zhanghang');
                $mem_db=M('mem',$this->admin_arr['pre_table']);
                if (200 === $array['code']){
                    //CRM返回200以后,记录入库
                    $mem_arr=$mem_db->where(array('cardno'=>$array['data']['cardno']))->find();
                    if($mem_arr){
                        $crm_retrun['province']=$params['province'];
                        $crm_retrun['city']=$params['city'];
                        $crm_retrun['district']=$params['district'];
                        $mem_db->where(array('cardno'=>$array['data']['cardno']))->save($crm_retrun);
                    }else{
                        $params['usermember']=$array['data']['usermember'];
                        $params['datetime']=$array['data']['datetime'];
                        $params['cardno']=$array['data']['cardno'];
                        $params['getcarddate']=$array['data']['getcarddate'];
                        $params['expirationdate']=$array['data']['expirationdate'];
                        $params['score_num']=$array['data']['score_num'];
                        $params['is_del']=$array['data']['is_del'];
                        unset($params['name']);
                        $mem_db->add($params);
                    }
//                     $sql['member_createMember']=$mem_db->_sql();
//                     $sql['member_params']=$array['data']['cardno'];
//                     $sql['member_select_arr']=$mem_arr;
//                     writeOperationLog($sql,'zhanghang');
                    
                     
                    $msg['code']=200;
                    $msg['data']=$array['data'];
                }else{
                    $msg['code']=$array['code'];
                }
            }else{
                
                $msg['code']=3000;
            }
        }
//         $sql['member_params_arr2']=$params2;
//         writeOperationLog($sql,'zhanghang');
        
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 修改会员信息
     */
    public function editMember()
    {
//         $params1['cardno']=I('cardno');
        $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'backend_update_json');
        $default_arr=json_decode($return['function_name'],true);
        //print_r($default_arr);die;
        foreach($default_arr as $k=>$v){
            if($v['fromtype'] != 'select'){
                    if($v['required']){
                        $params1[$v['type']]=I($v['type']);
                    }else{
                        $params2[$v['type']]=I($v['type']);
                    }
            }else{
                foreach($v['option'] as $ks=>$vs){
                        if($vs['required']){
                            $params1[$vs['type']]=I($vs['type']);
                        }else{
                            $params2[$vs['type']]=I($vs['type']);
                        }
                }
            }
        }
        //print_r($params1);die;
        if(in_array('',$params1)){//获取的参数不完整
            $msg['code']=1030;
        }else{
                $params=array_merge($params1,$params2);
                $params=apptocrmkeys($params,C('BACKEND_CRM_OUTPUT_INPUT_UPDATE'));
                $params['key_admin']=$this->ukey;
                $params['customer_name']='backend_update_json';
                $params['sign_key']=$this->admin_arr['signkey'];
                $params['birth']=strtotime($params['birth']);
                $params['address']=$params['address']==''?'|':$params['address'];
                $params['sign']=sign($params);
                unset($params['sign_key']);
                //print_r($params);die;
                $url=C('DOMAIN').'/CrmService/OutputApi/Index/editMember';
                //print_r($param);die;
                $result=http($url,$params);
//                 $db=M('mem',$this->admin_arr['pre_table']);
                if (is_json($result)){
                    $array=json_decode($result,true);
                    //print_r($array);die;
                    $sql['member_crm_return_editMember']=$array;
                    writeOperationLog($sql,'zhanghang');
                    if (200 === $array['code']){
                        $msg['code']=200;
                    }else{
                        $msg['code']=$array['code'];
                    }
                }else{
                    $msg['code']=3000;
                }
        }
        //dump($params);die;
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 查询会员信息，可以根据手机号或卡号
     */
    public function getMemberinfo()
    {
        $msg=$this->commonerrorcode;
        $params['key_admin']=I('key_admin');
        if (isset($_GET['card']) || isset($_POST['card'])){
            $params['card']=I('card');
        }elseif (isset($_GET['mobile']) || isset($_POST['mobile'])){
            $params['mobile']=I('mobile');
        }
        if (in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('mem',$this->admin_arr['pre_table']);
            unset($params['key_admin']);
            $find=$db->where($params)->find();
            //echo $db->_sql();die;
            if($find['is_del']!=2){
                if (null != $find){
                    $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->ukey,'member_level');
                    $arr=json_decode($return['function_name'],true);
                    foreach($arr as $k=>$v){
                        if($v['id']==$find['level']){
                            $find['level']=$v['level'];
                        }
                    }
                    $find['birthday']=date('Y-m-d',$find['birthday'])?date('Y-m-d',$find['birthday']):"";
                    $msg['code']=200;
                    $msg['data']=$find;
                }else{
                    $msg['code']=102;
                }
            }else{
                $msg['code']=102;
            }
            
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 冻结会员接口
     */
    public function delMember() {
		$this->Member_action('0');    
	}
	
	/**
     * 恢复会员接口
     */
	public function renewMember(){
		$this->Member_action('1');
	}
	
	/**
	 * 删除会员接口
	 * 删除以后不再显示
	 */
	public function real_delMember(){
	    $this->Member_action('2');
	}
	
	public function Member_action($id){
		$params['key_admin']=I('key_admin');
        $params['cardno']=I('cardno');
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('mem',$this->admin_arr['pre_table']);
            if($id=='2'){
                $save=$db->where(array('cardno'=>$params['cardno']))->save(array('is_del'=>$id,'openid'=>''));
            }else{
                $save=$db->where(array('cardno'=>$params['cardno']))->save(array('is_del'=>$id));
            }
            
            if (false !== $save){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
	}

	
	//积分扣减
	public function score_sub(){
	    $params['score']=abs(I('score'));//积分数
	    $params['cardno']=I('cardno');//卡号
	    $type=(int)I('type')?abs(I('type')):1;//状态原因
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	
	        $where=array('cardno'=>$params['cardno']);
	
	        $db=M('mem',$this->admin_arr['pre_table']);
	        $find=$db->field('cardno,score_num')->where(array('cardno'=>$params['cardno']))->find();
	        	
	        if($params['score']>$find['score_num'] or $find['score_num']==0){
	            $msg['code']=1052;
	            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	        }
	
	        $return=$db->where($where)->setDec('score_num',$params['score']);
	
	        //$return=$this->db_res('mem','score_sub',$where,$params['score']);
	
	        if( $return ){
	
	            $this->score_save_action(1,$params['score'],$params['cardno'],$type);
	
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	
	
	    }
	
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//积分添加
	public function score_add(){
	    $params['score']=abs(I('score'));//积分数
	    $params['cardno']=I('cardno');//卡号
	    $type=(int)I('type')?abs(I('type')):1;//状态原因
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	
	        $where=array('cardno'=>$params['cardno']);
	        	
	        $db=M('mem',$this->admin_arr['pre_table']);
	        $return=$db->where($where)->setInc('score_num',$params['score']);
	        //	dump($return);die;
	        //echo $db->_sql();die;
	        //$return=$this->db_res('mem','score_add',$where,$score);
	        if( $return ){
	
	            $this->score_save_action(2,$params['score'],$params['cardno'],$type);
	
	            $msg['code']=200;
	        }else{
	            $msg['code']=104;
	        }
	        	
	    }
	
	
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	//积分明细--查询所有（会员）
	public function score_list(){
	    $page=(int)I('page')>1?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
	    $lines=(int)I('lines')?abs(I('lines')):10;//获取每页显示条数，如果没有传入，默认每页10条
	    $start_time=I('start_time')?date('Y-m-d H:i:s',strtotime(I('start_time'))):'';//如果不传入，查询最近一个月的明细
	    $end_time=I('end_time')?date('Y-m-d H:i:s',strtotime(I('end_time'))+60*60*24):date('Y-m-d H:i:s');//如果不传入，则是当前按照当前时间查询
	
	    $db=M('score_record',$this->admin_arr['pre_table']);
	    
	    $mem_name=$this->admin_arr['pre_table'].'mem';
	    $score_record_name=$this->admin_arr['pre_table'].'score_record';
	    $map[$mem_name.'.is_del']=array('NEQ',2);
	    $map[$score_record_name.'.datetime']=array('between',array($start_time,$end_time));
	    $map['_logic']='and';
	    $return_num=$db->join($mem_name.' on '.$score_record_name.'.cardno = '.$mem_name.'.cardno')->where($map)->field('usermember')->select();
	    if(!$return_num){
	        $msg['code']=102;
	    }else{

    	    $count=ceil(count($return_num)/$lines);
    	    $page=$page<$count?$page:$count;
    	    $end=($page-1)*$lines;
    	    
    	    $return_nums=$db->join($mem_name.' on '.$score_record_name.'.cardno = '.$mem_name.'.cardno')->where($map)->order($score_record_name.'.datetime desc')->limit($end,$lines)->field($score_record_name.'.id,'.$score_record_name.'.cardno,scorenumber,why,scorecode,cutadd,'.$score_record_name.'.datetime,backend_admin,'.$score_record_name.'.is_del,store,usermember')->select();
            
    	    if($return_num){
    	        $msg['code']=200;
    	        $msg['data']['arr']=$return_nums;
    	        $msg['data']['page']=$count;
    	        $msg['data']['number']=count($return_num);
    	    }else{
    	        $msg['code']=102;
    	    }
    	    
	    }
	   echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	//积分兑换查询--订单号查询
	public function score_list_one(){
	    $params['scorecode']=I('scorecode');
	
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	
	        $db=M('score_record',$this->admin_arr['pre_table']);
	        $return=$db->where(array('scorecode'=>$params['scorecode']))->find();
	        //$msg=$this->db_res('score_record','score_list_one');
	
	        if($return){
	            $mem_db=M('mem',$this->admin_arr['pre_table']);
	            $mem_arr=$mem_db->where(array('cardno'=>$return['cardno']))->field('usermember')->find();
	            $return['usermember']=$mem_arr['usermember'];
	            $msg['code']=200;
	            $msg['data']=$return;
	        }else{
	            $msg['code']=102;
	        }
	    }
	
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//根据手机号模糊查询
	public function user_Detailed(){
	    $params['usermember']=I('usermember');
	     
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        
// 	        $where['usermember']=array('like',"%".$params['usermember']."%");
// 	        $where['is_del']=array('NEQ',2);
// 	        $where['_logic']='and';   
//     	    $mem_db=M('mem',$this->admin_arr['pre_table']);
//     	    $mem_arr=$mem_db->where($where)->field('usermember,cardno')->select();
//     	    //echo $mem_db->_sql();die;
//     	    if(empty($mem_arr)){
//     	        $msg['code']=102;
//     	    }else{
    	
//     	        foreach($mem_arr as $k=>$v){
//     	            $arr[$v['cardno']]=$v['usermember'];
//     	            $mem_arr_cardno[]=$v['cardno'];
//     	        }
//     	        $wheres['cardno']=array('in',$mem_arr_cardno);
//     	        $wheres['datetime']=array('between',array($start_time,$end_time));
//     	        $wheres['_logic']='and';
//     	        $db=M('score_record',$this->admin_arr['pre_table']);
//     	        $db_count=$db->where($wheres)->select();
//     	        if($db_count){
//     	            $count=ceil(count($db_count)/$lines);
//     	            $page=$page<$count?$page:$count;
//     	            $end=($page-1)*$lines;
//     	            $return=$db->where($wheres)->order('datetime desc')->limit($end,$lines)->select();
//     	            //echo $db->_sql();die;
//     	            if($return){
//     	                foreach($return as $k=>$v){
//     	                    $return[$k]['usermember']=$arr[$v['cardno']];
//     	                }
//     	                $msg['code']=200;
//     	                $msg['data']['page']=$count;
//     	                $msg['data']['arr']=$return;
//     	                $msg['data']['number']=count($db_count);
//     	            }else{
//     	                $msg['code']=102;
//     	            }
//     	        }else{
//     	            $msg['code']=102;
//     	        }
//     	    }
    	    $msg=$this->action_Detailed($params);
	    }
	   // print_r($msgs);echo "<hr/>";
	    //print_r($msg);die;
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	public function action_Detailed($params){
	    
	    $page=(int)I('page')>1?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
	    $lines=(int)I('lines')?abs(I('lines')):10;//获取每页显示条数，如果没有传入，默认每页10条
	    //date('Y-m-d H:i:s',strtotime("-1 month"))
	    $start_time=I('start_time')?date('Y-m-d H:i:s',strtotime(I('start_time'))):'';//如果不传入，查询最近一个月的明细
	    $end_time=I('end_time')?date('Y-m-d H:i:s',strtotime(I('end_time'))+60*60*24):date('Y-m-d H:i:s');//如果不传入，则是当前按照当前时间查询
	    $where['usermember']=array('like',"%".$params['usermember']."%");

	    $where['is_del']=array('NEQ',2);
	    $where['_logic']='and';
	    $mem_db=M('mem',$this->admin_arr['pre_table']);
	    $mem_arr=$mem_db->where($where)->field('usermember,cardno')->select();
	    //echo $mem_db->_sql();die;
	    if(empty($mem_arr)){
	        $msg['code']=102;
	    }else{
	         
	        foreach($mem_arr as $k=>$v){
	            $arr[$v['cardno']]=$v['usermember'];
	            $mem_arr_cardno[]=$v['cardno'];
	        }
	        $wheres['cardno']=array('in',$mem_arr_cardno);
	        $wheres['datetime']=array('between',array($start_time,$end_time));
	        $wheres['_logic']='and';
	        $db=M('score_record',$this->admin_arr['pre_table']);
	        $db_count=$db->where($wheres)->select();
	        if($db_count){
	            $count=ceil(count($db_count)/$lines);
	            $page=$page<$count?$page:$count;
	            $end=($page-1)*$lines;
	            $return=$db->where($wheres)->order('datetime desc')->limit($end,$lines)->select();
	            //echo $db->_sql();die;
	            if($return){
	                foreach($return as $k=>$v){
	                    $return[$k]['usermember']=$arr[$v['cardno']];
	                }
	                $msg['code']=200;
	                $msg['data']['page']=$count;
	                $msg['data']['arr']=$return;
	                $msg['data']['number']=count($db_count);
	            }else{
	                $msg['code']=102;
	            }
	        }else{
	            $msg['code']=102;
	        }
	    }

	return $msg;
	}
	
	
	
	//积分明细--根据卡号查询
	public function score_Detailed(){
	    $params['cardno']=I('cardno');
	    $page=(int)I('page')>1?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
	    $lines=(int)I('lines')>0?abs(I('lines')):10;//获取每页显示条数，如果没有传入，默认每页10条
	    $start_time=I('start_time')?date('Y-m-d H:i:s',strtotime(I('start_time'))):'';//如果不传入，查询最近一个月的明细
	    $end_time=I('end_time')?date('Y-m-d H:i:s',strtotime(I('end_time'))+60*60*24):date('Y-m-d H:i:s');//如果不传入，则是当前按照当前时间查询
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $export=I('export');
	        
	        $where['cardno']=array('eq',$params['cardno']);
	        $where['is_del']=array('NEQ',2);
	        $where['_logic']='and';
	        $mem_db=M('mem',$this->admin_arr['pre_table']);
	        $mem_arr=$mem_db->where($where)->field('usermember,cardno')->find();
	        if($mem_arr){
	            $wheres['cardno']=array('eq',$params['cardno']);
	            $wheres['datetime']=array('between',array($start_time,$end_time));
	            $wheres['_logic']='and';
	            $db=M('score_record',$this->admin_arr['pre_table']);
	            $db_count=$db->where($wheres)->select();
	            if($db_count){
	                $count=ceil(count($db_count)/$lines);
	                $page=$page<$count?$page:$count;
	                $end=($page-1)*$lines;
  
	                $return=$db->where($wheres)->order('datetime desc')->limit($end,$lines)->select();
	                //echo $db->_sql();die;
	                // print_r($return);
	                if($return){
	                    foreach($return as $k=>$v){
	                        if($v['cutadd']=='2'){
	                            $return[$k]['scorecode']='+'.$v['scorenumber'];
	                        }else{
	                            $return[$k]['scorecode']='-'.$v['scorenumber'];
	                        }
	                        $return[$k]['usermember']=$mem_arr['usermember'];
	                    }
	                    if($export=='yes'){
	                        $msg=$this->export_action($return);
	                    }else{
	                        $msg['code']=200;
	                        $msg['data']['data']=$return;
	                        $msg['data']['total']=count($db_count);
	                        $msg['data']['page']=$page;
	                        $msg['data']['pagenum']=$count;
	                    }
	                }else{
	                    $msg['code']=102;
	                }
	            }else{
	                $msg['code']=102;
	            }
	            
	        }else{
	            $msg['code']=102;
	        }
	    }
// 	        $where['cardno']=array('like',"%".$params['cardno']."%");
// 	        $where['is_del']=array('NEQ',2);
// 	        $where['_logic']='and';
// 	        $mem_db=M('mem',$this->admin_arr['pre_table']);
// 	        $mem_arr=$mem_db->where($where)->select();
// 	        if(empty($mem_arr)){
// 	            $msg['code']=102;
// 	        }else{
// 	            $db=M('score_record',$this->admin_arr['pre_table']);
	             
// 	            $db_count=$db->where($where)->select();
// 	            if($db_count){
	                 
// 	                $count=ceil(count($db_count)/$lines);
// 	                $page=$page<$count?$page:$count;
// 	                $end=($page-1)*$lines;
	                 
// 	                $return=$db->where($where)->order('datetime desc')->limit($end,$lines)->select();
// 	                if($return){
// 	                    $return=$this->arr_action($return);
// 	                    $msg['code']=200;
// 	                    $msg['data']['page']=$count;
// 	                    $msg['data']['arr']=$return;
// 	                    $msg['data']['number']=count($db_count);
// 	                }else{
// 	                    $msg['code']=102;
// 	                }
// 	            }else{
// 	                $msg['code']=102;
// 	            }
// 	        }
	        
// 	        //}
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	public function export_action($data){
	        $str="会员名称(usermember),卡号(cardno),积分变动(scorecode),积分行为(why),时间(datetime),状态(is_del)\r\n";
	        foreach($data as $k=>$v){
	            if($v['is_del']==1){
	                $is_del='正常';
	            }else{
	                $is_del='已删除';
	            }
	            $str=$str.$v['usermember'].",".$v['cardno'].",".$v['scorecode'].",".$v['why'].",".$v['datetime'].",".$is_del."\r\n";
	        }
	        $str=iconv('utf8', 'gb2312', $str);
	        $return=mkdir_ext($str,RUNTIME_PATH.'wechat/fans/','csv');
	        if($return['code']==200){
	            $time = date("Ymd");
	            $uniqid = uniqid();
	            $key = 'fans_'.$time.'_'.$uniqid.'.csv';
	            $qiniu=new QiniuController;
	            list($ret, $err)=$qiniu->uploadfile($return['path'],$key);
	            unlink($return['path']);
	            if ($err !== null) {
	                $msg['code']=104;
	            }else{
	                $msg['code']=200;
	                $msg['data']=array('url'=>"https://img.rtmap.com/".$key);
	            }
	        }else{
	            $msg['code']=$return['code'];
	        }
	    return $msg;
	}
	
	
	
	
	//获取商户登录信息
	public function total_admin_ones(){
	    $msg['code']=200;
	    $msg['data']['name']=$this->admin_arr['name'];
	    $msg['data']['describe']=$this->admin_arr['describe'];
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	
	
	
	//获取当前用户的积分数
	public function mem_scord(){
	    $params['cardno']=I('cardno');
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $db=M('mem',$this->admin_arr['pre_table']);
	        $find=$db->field('cardno,score_num')->where(array('cardno'=>$params['cardno']))->find();
	
	        $score_db=M('score_record',$this->admin_arr['pre_table']);   
	        $count=$score_db->where(array('cardno'=>$params['cardno']))->select();
	        $find['count']=count($count);
	        if (null != $find){
	            $msg['code']=200;
	            $msg['data']=$find;
	        }else{
	            $msg['code']=102;
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//是否删除积分记录（不是真正的物理删除）
	public function score_del(){
	    $this->score_del_action('0');
	}
	
	public function renew_score(){
	    $this->score_del_action('1');
	}
	
	public function score_del_action($status){
	    $params['cardno']=I('cardno');
	    $params['ID']=I('ID');//积分记录的ID
	    //print_R($params);die;
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
	        $db=M('score_record',$this->admin_arr['pre_table']);
	        $cardno_arr=$db->where(array('id'=>$params['ID']))->find();//根据ID查看传入的卡号是否一致
	        if($cardno_arr['cardno'] != $params['cardno']){
	            $msg['code']=1055;
	        }else{
	            $res=$db->where(array('id'=>$params['ID']))->save(array('is_del'=>$status));
	            if($res){
	                $msg['code']=200;
	            }else{
	                $msg['code']=104;
	            }
	        }
	    }
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	public function score_save_action($type,$score,$cardno,$why){
	    $arr=C('CRM_BACKEND');
	    $why=$why<=count($arr)?$why:1;
	    $data['scorenumber']=$score;//积分数
	    $data['cardno']=$cardno;//卡号
	    $data['why']=$arr[$why];//理由
	    $data['scorecode']=date('YmdHis').$this->rand_num();//交易编号
	    $data['datetime']=date('Y-m-d H:i');//时间
	    $data['cutadd']=$type;//积分类型
	    $data['backend_admin']=$this->admin_arr['name'];//操作管理员
	    //$data['is_del']=1;
	
	    $db=M('score_record',$this->admin_arr['pre_table']);
	   
	    $res=$db->add($data);
	    //echo $db->_sql();
	    //$write_arr['integral_action']=$db->_sql();
	   // writeOperationLog($write_arr,'MerAdmin');
	    if($res){
	        return true;
	    }else{
	        return false;
	    }
	
	}
	
	public function rand_num(){
	    $numbers = range (1,10);
	    //var_dump($numbers);die;
	    //播下随机数发生器种子，可有可无，测试后对结果没有影响
	    srand ((float)microtime()*1000000);
	    shuffle ($numbers);
	    //跳过list第一个值（保存的是索引）
	    while (list(, $number) = each ($numbers)) {
	        return $number;
	    }
	}
	//获取所有原由列表
	public function reason_list(){
	    $arr=C('CRM_BACKEND');
	     
	    if($arr){
	        $msg['code']=200;
	        $msg['data']=$arr;
	    }else{
	        $msg['code']=102;
	    }
	     
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	
	/*public function arr_action($return){
	    foreach($return as $k=>$v){
	        $arr[]=$v['cardno'];
	    }
	     
	    $arr=array_unique($arr);
	    $mem_db=M('mem',$this->admin_arr['pre_table']);
	    $map['cardno']=array('in',$arr);
	    $map['is_del']=array('NEQ',2);
	    $map['_logic']='and';
	    $mem_arr=$mem_db->where($map)->field('usermember,cardno')->select();
 	   // print_r($arr);
// 	    echo $mem_db->_sql();die;
// 	    print_R($mem_arr);
	   
	    foreach($mem_arr as $k=>$v){
	        $return_arr[$v['cardno']]=$v['usermember'];
	    }
	   // print_r($return);echo '<hr/>';
	    foreach($return as $k=>$v){
	        //$return[$k]['usermember']=$return_arr[$v['cardno']];
	        if($return_arr[$v['cardno']]!=""){
	            $return[$k]['usermember']=$return_arr[$v['cardno']];
	            $returns[]=$return[$k];
	            
	        }
	    }
	   // print_r($returns);die;
	    return $returns;
	}*/


	/*
	 *积分赠送设置
	 */
	public function GiveScore()
    {
        $params['key_admin']=I('key_admin');
        $params['isenable']=I('isenable');//首次送积分开启关闭
        if($params['isenable'] == 1){
            $params['scorenum']=I('scorenum');//首次送积分数
        }
        $params['isbirthdayenable']=I('isbirthdayenable');//生日送积分倍数开启或关闭
        if($params['isbirthdayenable'] ==1 ){
            $params['birthdayscorenum']=I('birthdayscorenum');//生日送积分倍数
        }
       $params['istimetotimeenable']=I('istimetotimeenable');//时间段内送积分开启或关闭
       if($params['istimetotimeenable'] == 1){
           $params['timetotimescorenum']=I('timetotimescorenum');//时间段内送积分倍数
           $params['starttime']=I('starttime');
           $params['endtime']=I('endtime');
       }
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $params['starttime']=strtotime($params['starttime']);
        $params['endtime']=strtotime($params['endtime']);
        $params['timetotimescorenum']=I('timetotimescorenum');//时间段内送积分倍数
        $params['starttime']=I('starttime');
        $params['endtime']=I('endtime');
        $params['birthdayscorenum']=I('birthdayscorenum');//生日送积分倍数
        $params['scorenum']=I('scorenum');//首次送积分数
        $admininfo=$this->getMerchant($params['key_admin']);
        $find=$this->GetOneAmindefault($admininfo['pre_table'],$params['key_admin'], 'firstgivescore');//获取是否设置了要首次赠送积分
        $findbirthday=$this->GetOneAmindefault($admininfo['pre_table'],$params['key_admin'], 'birthdaygivescore');//获取是否设置了要首次赠送积分
        $findtimetotime=$this->GetOneAmindefault($admininfo['pre_table'],$params['key_admin'], 'timetotimegivescore');//获取是否设置了要首次赠送积分
        $db=M('default', $admininfo['pre_table']);
        //如果数据库里面有，则根据id修改
        if ($find){
            //是否赠送积分
            $data=array(
                'id'=>$find['id'],
                'customer_name'=>'firstgivescore',
                'function_name'=>(int)$params['isenable']
            );
            $save1=$db->save($data);
            if ($save1 !== false){
                $data=null;
                $findnum=$this->GetOneAmindefault($admininfo['pre_table'], $params['key_admin'], 'firstgivescorenum');
                //赠送积分数
                $data=array(
                    'id'=>$findnum['id'],
                    'customer_name'=>'firstgivescorenum',
                    'function_name'=>(int)$params['scorenum']
                );
                $save2=$db->save($data);
            }else{
                $save2=false;
            }
        }else{
            $data=array(
                'customer_name'=>'firstgivescore',
                'function_name'=>(int)$params['isenable'],
                'description'=>'是否开启首次加积分时送积分'
            );
            $save1=$db->add($data);
            if ($save1){
                $data=null;
                //赠送积分数
                $data=array(
                    'customer_name'=>'firstgivescorenum',
                    'function_name'=>(int)$params['scorenum'],
                    'description'=>'首次赠送多少积分'
                );
                $save2=$db->add($data);
            }else{
                $save2=false;
            }
        }


        //保存生日
        //如果数据库里面有，则根据id修改
        if ($findbirthday){
            //是否赠送积分
            $data=array(
                'id'=>$findbirthday['id'],
                'customer_name'=>'birthdaygivescore',
                'function_name'=>json_encode(array('isenable'=>(int)$params['isbirthdayenable'],'scorenum'=>(int)$params['birthdayscorenum'])),
            );
            $savebirthday=$db->save($data);
        }else{
            $data=array(
                'customer_name'=>'birthdaygivescore',
                'function_name'=>json_encode(array('isenable'=>(int)$params['isbirthdayenable'],'scorenum'=>(int)$params['birthdayscorenum'])),
                'description'=>'会员生日积分加倍'
            );
            $savebirthday=$db->add($data);
        }


        //保存某段时间到某段时间
        //如果数据库里面有，则根据id修改
        if ($findtimetotime){
            //是否赠送积分
            $data=array(
                'id'=>$findtimetotime['id'],
                'customer_name'=>'timetotimegivescore',
                'function_name'=>json_encode(array('isenable'=>(int)$params['istimetotimeenable'],'time'=>array('start'=>$params['starttime'],'endtime'=>$params['endtime']),'scorenum'=>(int)$params['timetotimescorenum'])),
            );
            $savetime=$db->save($data);
        }else{
            $data=array(
                'customer_name'=>'timetotimegivescore',
                'function_name'=>json_encode(array('isenable'=>(int)$params['istimetotimeenable'],'time'=>array('start'=>$params['starttime'],'endtime'=>$params['endtime']),'scorenum'=>(int)$params['timetotimescorenum'])),
                'description'=>'某段时间内积分加倍'
            );
            $savetime=$db->add($data);
        }


        $this->redis->del('admin:default:one:firstgivescore:'. $params['key_admin']);
        $this->redis->del('admin:default:one:firstgivescorenum:'. $params['key_admin']);
        $this->redis->del('admin:default:one:birthdaygivescore:'. $params['key_admin']);
        $this->redis->del('admin:default:one:timetotimegivescore:'. $params['key_admin']);

        if ($save2 !== false && $savebirthday !== false && $savetime !== false){
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /*
     *获取赠送积分配置
     */
    public function GetGiveScoreSetting()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $isenable=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'firstgivescore');//首次赠送积分配置
        $birthday=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'birthdaygivescore');//生日双倍或多倍（可配）积分
        $timetotime=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'timetotimegivescore');//某时间段内双倍或多倍（可配）积分

        if (is_array($isenable)){
            $scorenum=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'firstgivescorenum');
            $givescore=array('isenable'=>(int)$isenable['function_name'], 'scorenum'=>(int)$scorenum['function_name']);
            returnjson(array('code'=>200,'data'=>array('givescore'=>$givescore,'birthday'=>json_decode($birthday['function_name']), 'timetotime'=>json_decode($timetotime['function_name']))), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle, $this->callback);
        }
    }




    /*
     * 获取积分流水或下载接口
     */
    public function GetScoreList()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $params['starttime']=I('starttime');
        $params['endtime']=I('endtime');
        $params['page']=I('page') != '' ? abs(I('page')) : 1;
        $params['lines']=I('lines') != '' ? abs(I('lines')) : 10;
        $params['export']=I('export');
        $db=M('score_record', $admininfo['pre_table']);
        $where='';
        if ($params['starttime']){
            $where .= ' `datetime` >= "'.$params['starttime'].'"';
        }
        if ($params['endtime']){
            if ($where != ''){
                $where .= ' and `datetime` <= "'.$params['endtime'].'"';
            }else{
                $where .= ' `datetime` <= "'.$params['endtime'].'"';
            }
        }
        if ($params['export'] != 1){
            $count=$db->where($where)->count('id');
            $pages=ceil($count/$params['lines']);
            $start=($params['page']-1)*$params['lines'];
            $sel=$db->where($where)->limit($start, $params['lines'])->order('datetime desc')->select();
            if ($sel == null){
                $msg['code']=102;
            }else{
                $msg['code']=200;
                $msg['data']['datas']=$sel;
                $msg['data']['count']=$count;
                $msg['data']['page']=$params['page'];
            }
        }else{
            $sel=$db->where($where)->order('datetime desc')->select();
            $csvarr[]='卡号,时间,积分缘由,积分数';
            foreach ($sel as $key => $val){
                $csvarr[]=$val['cardno'].','.$val['datetime'].','.$val['why'].','.$val['scorenumber'];
            }
            $return=CreateCsvFile($csvarr, RUNTIME_PATH.'score_history/','csv');//正确返回路径
            if($return !== false){
                $time = date("Ymd");
                $uniqid = uniqid();
                $key = 'score_history_'.$time.'_'.$uniqid.'.csv';
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
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }
	
}


