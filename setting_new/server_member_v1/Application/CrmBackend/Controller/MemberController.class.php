<?php
namespace CrmBackend\Controller;

//use Common\Controller\ErrorcodeController;
use MerAdmin\Controller\AuthController;
/**
 * 自有CRM后台
 * @author kaifeng
 *
 */
class MemberController extends AuthController
{
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
        $this->admin_arr=$this->getMerchant($this->ukey);
    }
    
    
    public function user_lists(){
        $params['page']=I('page');
        $params['lines']=I('lines');
        $params['key_admin']=I('key_admin');
        $params['usermember']=I('usermember');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $start=($params['page']-1)*$params['lines'];
            $map['usermember'] =array('like',array('%'.$params['usermember'].'%','%'.$params['usermember']),'OR');
            $map['is_del']=array('NEQ',2);
            $map['_logic']='and';
            $sel=$db->field('id,cardno,usermember,idnumber,sex,getcarddate,expirationdate,birthday,phone,is_del as status,score_num,headerimg,address,career,wechat,star,remark,openid,email')->where($map)->limit($start,$params['lines'])->select();
            //echo $db->_sql();die;
            if (false != $sel){
                $num=$db->where($map)->select();
                $total=count($num);
                $msg['code']=200;
                $msg['data']=array(
                    'data'=>$sel,
                    'total'=>$total,
                );
            }else {
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    /**
     * 会员列表读取
     */
    public function Lists()
    {
        $params['page']=I('page');
        $params['lines']=I('lines');
        $params['key_admin']=I('key_admin');
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=1030;
        }else{
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $start=($params['page']-1)*$params['lines'];
            $map['is_del']=array('NEQ',2);
            $sel=$db->field('id,cardno,usermember,idnumber,sex,getcarddate,expirationdate,phone,is_del as status,score_num,headerimg,headerimg,address,career,wechat,star,remark,openid,email')->where($map)->order('id desc')->limit($start,$params['lines'])->select();
           //echo $db->_sql();die;
            if (false != $sel){
                $num=$db->where($map)->select();
                $total=count($num);
                $msg['code']=200;
                $msg['data']=array(
                    'data'=>$sel,
                    'total'=>$total,
                );
            }else {
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
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
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $map['cardno']=array('eq',$params['cardno']);
            $map['is_del']=array('NEQ',2);
            $map['_logic']='and';
            $find=$db->field('cardno,usermember,idnumber,sex,getcarddate,expirationdate,birthday,phone,is_del as status,score_num,headerimg,headerimg,address,career,wechat,star,remark,openid,email')->where($map)->find();
            //echo $db->_sql();die;
            if (null != $find){
                $find['birthday']=date('Y-m-d',$find['birthday'])?date('Y-m-d',$find['birthday']):"";
                $msg['code']=200;
                $msg['data']=array(
                    'data'=>$find
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
        $params['key_admin']=I('key_admin');
        $params['mobile']=I('mobile');
        $params['idnumber']=I('idnumber');
        $params['name']=I('name');
        $msg=$this->commonerrorcode;
        if (in_array('', $params)){
            $msg['code']=1030;
        }else{
            $admininfo=$this->getMerchant($params['key_admin']);
            $params['sign_key']=$admininfo['signkey'];
            $params['sex']=I('sex')?I('sex'):'';
            
            $params['birth']=I('birthday')?strtotime(I('birthday')):'';
            $params['address']=I('address');//地址
            $params['wechat']=I('wechat');//微信号
            $params['career']=I('career');//职业
            $params['star']=I('star');//星座
            $params['remark']=I('remark');//备注
            $params['email']=I('email');//邮箱
            $params['sign']=sign($params);
            unset($params['sign_key']);
            $url=C('DOMAIN').'/CrmService/OutputApi/Index/createMember';
            $result=http($url,$params);
            if (is_json($result)){
                $array=json_decode($result,true);
                if (200 === $array['code']){
                    $msg['code']=200;
                    $msg['data']=$array['data'];
                }else{
                    $msg['code']=$array['code'];
                }
            }else{
                
                $msg['code']=3000;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 修改会员信息
     */
    public function editMember()
    {
        $params['key_admin']=I('key_admin');
        //$params['sex']=I('sex');
        $params['name']=I('name');
        $params['mobile']=I('mobile');//手机号
        $params['idnumber']=I('idnumber');//身份证号
        $params['cardno']=I('cardno');
        $params['is_del']=I('is_del');
        if(in_array('',$params)){//获取的参数不完整
            $msg['code']=1030;
        }else{
            if(!preg_match('/^1([0-9]{9})/',$params['mobile'])){
                $msg['code']=1050;
            }else{
                $params['birth']=I('birthday')?strtotime(I('birthday')):'';
                $params['address']=I('address');//地址
                //$params['wechat']=I('wechat');//微信号
                $params['career']=I('career');//职业
                $params['star']=I('star');//星座
                $params['remark']=I('remark');//备注
                $params['email']=I('email');
                //print_r($params);die;
                $admininfo=$this->getMerchant($params['key_admin']);
    
                $db=M('mem',$admininfo['pre_table']);
    
                //根据传入的手机号和身份证号查询表中是否存在这样的数据
                $mem_arr=$db->where(array('mobile'=>$params['mobile']))->find();
                
                if(!empty($mem_arr) and $mem_arr['cardno']!=$params['cardno']){
                    $msg['code']=1012;
                }else{
                    $rt['mobile']=$params['mobile'];
                    $rt['phone']=$params['mobile'];
                    //$rt['sex']=$params['sex'];
                    $rt['idnumber']=$params['idnumber'];
                    $rt['usermember']=$params['name'];
                    $rt['is_del']=$params['is_del'];
                    $rt['address']=$params['address'];//地址
                    //$rt['wechat']=$params['wechat'];//微信号
                    $rt['career']=$params['career'];//职业
                    $rt['star']=$params['star'];//星座
                    $rt['remark']=I('remark');//备注
                    $rt['birthday']=$params['birth'];//日期
                    $rt['email']=$params['email'];
                    $sv=$db->where(array('cardno'=>$params['cardno']))->save($rt);
                    //echo $db->_sql();die;
                    if($sv === false){
                        $msg['code']=104;
                    }else{
                        //$arr=$db->where(array('cardno'=>$params['cardno']))->find();    
                        $msg=array('code'=>200);
                    }
                }
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
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            unset($params['key_admin']);
            $find=$db->where($params)->find();
            //echo $db->_sql();die;
            if($find['is_del']!=2){
                if (null != $find){
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
            $admininfo=$this->getMerchant($params['key_admin']);
            $db=M('mem',$admininfo['pre_table']);
            $save=$db->where(array('cardno'=>$params['cardno']))->save(array('is_del'=>$id));
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
	    $params['score']=I('score');//积分数
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
	    $params['score']=I('score');//积分数
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
	    $page=(int)I('page')?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
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
	    
	    $page=(int)I('page')?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
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
	
	
	
	//积分明细--根据卡号模糊查询
	public function score_Detailed(){
	    $params['cardno']=I('cardno');
	    $page=(int)I('page')?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
	    $lines=(int)I('lines')?abs(I('lines')):10;//获取每页显示条数，如果没有传入，默认每页10条
	    $start_time=I('start_time')?date('Y-m-d H:i:s',strtotime(I('start_time'))):'';//如果不传入，查询最近一个月的明细
	    $end_time=I('end_time')?date('Y-m-d H:i:s',strtotime(I('end_time'))+60*60*24):date('Y-m-d H:i:s');//如果不传入，则是当前按照当前时间查询
	    if(in_array('',$params)){
	        $msg['code']=1030;
	    }else{
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
	                        $return[$k]['usermember']=$mem_arr['usermember'];
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
	        $count=$db->where(array('cardno'=>$params['cardno']))->select();
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
	    $why=$why<count($arr)?$why:0;
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
	
	
	
}