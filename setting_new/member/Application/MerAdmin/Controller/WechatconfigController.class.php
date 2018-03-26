<?php
namespace MerAdmin\Controller;
use PublicApi\Controller\QiniuController;
class WechatconfigController extends AuthController
{
    // TODO - Insert your code here
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        
        parent::_initialize();
        //查询商户信息
        $this->admin_arr=$this->getMerchant($this->ukey);        	
        $this->key_admin=$this->ukey;  
    }
    
    
    //微信页面配置    会员条款
    
    /*
     * 微信页面配置    会员条款配置
     * 添加或者修改会员条款
     */
    
    public function member_terms(){
        //$params['tid']=I('tid');
        //$params['title']=I('title');
        //$params['content']=I('content');
        $params['function_name']=I('content');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $static = M('default',$this->admin_arr['pre_table']);
            $page_info = $static->where(array('customer_name'=>'termsofservice'))->find();
            
            $data=array(
                'function_name'=>$params['function_name'],
                'customer_name'=>'termsofservice',
                'description'=>'会员条款',
            );
            
            if($page_info){
                $db_up = $static->where(array('customer_name'=>'termsofservice'))->save($data);
            }else{
                $db_up = $static->add($data);
            }
            
            if($db_up === false){
                $msg['code']=104;
            }else{
                $msg['code']=200;
            }

        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
 
    /*
     * 微信页面配置    获取会员条款信息
     * 
     */
    public function member_terms_one(){
        //$params['tid']=I('tid');
        $params['customer_name']=I('customer_name')?I('customer_name'):'termsofservice';
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $static = M('default',$this->admin_arr['pre_table']);
            $page_info = $static->where(array('customer_name'=>$params['customer_name']))->find();
            if($page_info){
                $msg['code']=200;
                $msg['data']['content']=htmlspecialchars_decode($page_info['function_name']);
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
            
    }

    
    
    
    //微信页面配置    表单
    
    
    /*
     * 微信页面配置    注册表单字段
     */
    public function wechat_field($status){
        $params['function_name']=I('function_name');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $par_arr=json_decode($params,true);
            foreach($par_arr as $k=>$v){
                if(!isset($v['key']) and !isset($v['is_require']) and !isset($v['content'])){
                    $code=1030;
                    break;
                }
                if($v['type'] == 'rediao' || $v['type'] == 'checkbox' || $v['type'] == 'select'){
                    if(count($v['option']) <= 1){
                        $code=1030;
                        break;
                    }
                }
            }
            if(isset($code)){
                $msg['code']=$code;
            }else{
                $db=M('default',$this->admin_arr['pre_table']);
                $res=$db->where(array('customer_name'=>'backend_create_json'))->find();
                
                $data['function_name']=$params;
                
                if($res){
                    $data['customer_name']='fieldconfig';
                    $data['description']='字段配置';
                    $db_res=$db->add($data);
                }else{
                    $db_res=$db->where(array('customer_name'=>'backend_create_json'))->save($data);
                }
                
                if($db_res !== false){
                    $this->redis->del('admin:default:'.$this->key_admin);
                    $msg['code']=200;  
                }else{
                    $msg['code']=104;
                }
            }     
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /*
     * 微信页面配置    获取表单列表
     */
    public function wechat_field_list(){
        $return=$this->GetOneAmindefault($this->admin_arr['pre_table'],$this->key_admin,'backend_create_json');
        
        $arr=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->ukey, 'member_level');
            
        $arr['function_name']=json_decode($arr['function_name'],true);
        
        foreach($arr['function_name'] as $key=>$val){
            $level[$key]['value']=$val['code'];
            $level[$key]['text']=$val['level'];
        }
        
        $return['function_name']=json_decode($return['function_name'],true);
        
        foreach($return['function_name'] as $key=>$val){
            
            if($val['fromtype'] == 'select'){
                foreach($val['option'] as $ks=>$vs){
                    if($vs['type'] == 'level'){
                        $return['function_name'][$key]['option'][$ks]['value']=$level;
                    }
                }
            }
        }
        $msg['code']=200;
        $msg['data']=$return;
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    
    //微信页面配置        会员手册/会员权益   

    /*
     * 微信页面配置    获取会员手册/会员权益列表    
     */
    public function get_manual_list(){
        $db=M('manual',$this->admin_arr['pre_table']);
        $arr=$db->order('sort asc')->select();
        if($arr){
            foreach($arr as $k=>$v){
                $arr[$k]['content']=htmlspecialchars_decode($v['content']);
            }
            
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /*
     * 微信页面配置    添加会员手册/会员权益
     */
    public function manual_add(){
        $msg=$this->manual_action('add');
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /*
     * 微信页面配置    修改会员手册/会员权益
     */
    public function manual_save(){
//         $params['title']=I('title');
//         $params['content']=I('content');
//         //$params['sort']=I('sort');
//         if(in_array('',$params)){
//             $msg['code']=1030;
//         }else{
//             $db=M('manual',$this->admin_arr['pre_table']);
//             $res=$db->where(array('title'=>$params['title']))->find();
//             if($res){
//                 //$re_res=$db->where(array('title'=>$params['title']))->save($data);
//                 $msg['code']=1008;
//             }else{
//                 $res=$db->order('sort desc')->field('sort')->find();
//                 $data['sort']=$res['sort']+1;
//                 $data=array(
//                     'title'=>$params['title'],
//                     'content'=>$params['content']
//                 );
//                 $re_res=$db->save($data);
//                 if($re_res !== false){
//                     $msg['code']=200;
//                 }else{
//                     $msg['code']=104;
//                 }
//             }
//         }
        $msg=$this->manual_action('save');
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /*
     * 微信页面配置    会员手册/会员权益物理删除
     */
    public function manual_del(){
        $params['id']=I('id');
        //$params['sort']=I('sort');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('manual',$this->admin_arr['pre_table']);
            $map=array('id'=>$params['id']);
            $man_arr=$db->where($map)->find();
            //print_r($man_arr);die;
            if($man_arr){
                $db->where(array('sort'=>array('GT',$man_arr['sort'])))->setDec('sort',1);
                //echo $db->_sql();die;
                $res=$db->where($map)->delete();
                //echo $db->_sql();die;
                if($res){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=104;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /*
     * 微信页面配置    会员手册/会员权益获取单挑记录
     */
    public function manual_content_one(){
        $params['id']=I('id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('manual',$this->admin_arr['pre_table']);
            $res=$db->where(array('id'=>$params['id']))->find();
            if($res){
                $res['content']=htmlspecialchars_decode($res['content']);
                $msg['code']=200;
                $msg['data']=$res;
            }else{
                $msg['code']=102;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /*
     * 微信页面配置    会员手册/会员权益上移
     */
    public function manual_sort_up(){
        $this->manual_sort_action('up');
        
    }
    
    /*
     * 微信页面配置    会员手册/会员权益下移
     */
    public function manual_sort_down(){
        $this->manual_sort_action('down');
    }
    
    /*
     * 微信页面配置    会员手册/会员权益排序上移与下移方法
     */
    public function manual_sort_action($status){
        $params['id']=I('id');
        $params['sort']=abs(I('sort'))>0?abs(I('sort')):'';
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            if($status == 'up'){
                if($params['sort'] == 1){//历史的排序
                    echo returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit(); 
                }
                $params['re_sort']=$params['sort']-1;
            }else if($status == 'down'){
                
                $params['re_sort']=$params['sort']+1;
            }
            
            $db=M('manual',$this->admin_arr['pre_table']);
            
            $re_arr=$db->where(array('sort'=>$params['re_sort']))->find();
            
            if($re_arr){
                
                $res=$db->where(array('id'=>$params['id']))->save(array('sort'=>$params['re_sort']));
                $res1=$db->where(array('id'=>$re_arr['id']))->save(array('sort'=>$params['sort']));
                if($res !== false or $res1 !==false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=104;
            }
            
            
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    //会员手册/会员权益方法
    public function manual_action($status){
        $id=I('id');
        $params['title']=I('title');
        $params['content']=I('content');
        //$params['sort']=I('sort');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('manual',$this->admin_arr['pre_table']);
            $res=$db->where(array('title'=>$params['title']))->find();
            $data=array(
                'title'=>$params['title'],
                'content'=>$params['content']
            );
            if(empty($id)){
                if($res){
                    $msg['code']=1008;
                }else{
                    $res=$db->order('sort desc')->field('sort')->select();
                    if(count($res)>=6){
                        $msg['code']=1009;
                    }else{
                        $data['sort']=$res[0]['sort']+1;
                        $re_res=$db->add($data);
                        if($re_res !== false){
                            $msg['code']=200;
                        }else{
                            $msg['code']=104;
                        }
                    }
                }  
            }else{
                    $re_res=$db->where(array('id'=>$id))->save($data);
                    if($re_res !== false){
                        $msg['code']=200;
                    }else{
                        $msg['code']=104;
                    }
            }
        }
        return $msg;
    }
    
    
    
    //会员意见反馈
    
    
    /*
     * 微信页面配置   会员意见反馈列表/根据时间搜索
     */   
    public function member_feedback(){
        $page=(int)I('page')?abs(I('page')):1;//接当前页，并转化成数字，如果没有传默认成1
        $lines=(int)I('lines')?abs(I('lines')):10;//获取每页显示条数，如果没有传入，默认每页10条
        $start_time=I('start_time')?strtotime(I('start_time')):'';//如果不传入，查询最近一个月的明细
        $end_time=I('end_time')?strtotime(I('end_time'))+60*60*24:time();//如果不传入，则是当前按照当前时间查询
        
        $db=M('feedback',$this->admin_arr['pre_table']);
        $member_table=$this->admin_arr['pre_table'].'mem';
        $feedback_table=$this->admin_arr['pre_table'].'feedback';
        $map['createtime']=array('between',array($start_time,$end_time));
        $map[$feedback_table.'.status']=array('EQ',1);
        $map['gid']=array('eq',0);
        $map['pid']=array('eq',0);
        $map['_logic']='and';
        
        $number=$db->where($map)->join('LEFT JOIN '.$member_table.' on '.$feedback_table.'.mem_id = '.$member_table.'.id')->field($feedback_table.'.id')->select();
//        echo $db->_sql();die;
        $num=count($number);
        if($num){
            //echo $num;die;
            $count=ceil($num/$lines);
            $page=$page<$count?$page:$count;
            $end=($page-1)*$lines;
            
            $return=$db->join('LEFT JOIN '.$member_table.' on '.$feedback_table.'.mem_id = '.$member_table.'.id')->where($map)->order('createtime desc')->field($feedback_table.'.openid,'.$feedback_table.'.id,'.$feedback_table.'.status,'.$feedback_table.'.phone,'.$feedback_table.'.content,'.$feedback_table.'.createtime,'.$member_table.'.usermember')->limit($end,$lines)->select();

            if($return){
                foreach($return as $k=>$v){
                    $return[$k]['createtime']=date('Y-m-d H:i',$v['createtime']);
                }
                $msg['code']=200;
                $msg['data']=$return;
                $msg['page']=$page;
                $msg['count']=$num;
                $msg['pagenum']=$count;
            }else{
                $msg['code']=102;
            }
        }else{
            $msg['code']=102;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /*
     * 微信页面配置    会员意见反馈删除
     */
    public function member_feedback_del(){
        $msg=$this->member_feedback_action('del');
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /*
     * 微信页面配置    获取单挑会员意见反馈
     */
    public function member_feedback_one(){
        $msg=$this->member_feedback_action('find');
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /*
     * 微信页面配置    会员意见反馈/查询方法
     */
    public function member_feedback_action($status){
        $params['id']=I('id');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('feedback',$this->admin_arr['pre_table']);
            $member_table=$this->admin_arr['pre_table'].'mem';
            $feedback_table=$this->admin_arr['pre_table'].'feedback';
            //$map=array('id'=>$params['id']);
            $map[$feedback_table.'.id']=array('eq',$params['id']);
            if($status=='del'){
                $id=json_decode($params['id'],true);
                $res=$db->where(array('id'=>array('in',$id)))->save(array('status'=>2));
            }else if($status=='find'){
                $res=$db->join($member_table.' on '.$feedback_table.'.mem_id = '.$member_table.'.id')->where($map)->order('createtime desc')->field($feedback_table.'.openid,'.$feedback_table.'.id,'.$feedback_table.'.status,'.$feedback_table.'.phone,'.$feedback_table.'.content,'.$feedback_table.'.createtime,'.$member_table.'.usermember')->select();
            }
            
            if($res === false){
                $msg['code']=104;
            }else{      
                $msg['code']=200;
                if(is_array($res)){
                    $resp['createtime']=date('Y-m-d H:i',$res['createtime']);
                    $msg['data']=$res;
                }
            }
        }
        return $msg;
    }
    
    
    /*
     * 根据前端请求参数,查询参数值,查询省份,城市,区数据
     */
    public function getarea()
    {
        $provnice=I('province');
        $city=I('city');
        if ($provnice=='' && $city ==''){//查询所有省的数据
            $db=M('province', 'total_');
            $sel=$db->field('id', true)->select();
        }elseif ($provnice != ''){
            $db=M('city', 'total_');
            $sel=$db->field('id,provincecode', true)->where(array('provincecode'=>$provnice))->select();
        }elseif ($city != ''){
            $db=M('area', 'total_');
            $sel=$db->field('id,citycode', true)->where(array('citycode'=>$city))->select();
        }
        if ( empty($sel) ){
            $data['code']=102;
        }else{
            $data['code']=200;
            $data['data']=$sel;
        }
        returnjson($data, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 微信页面配置    联系我们添加或修改
     */
    public function contactus(){
        $params['feedback']=I('feedback');//意见反馈
        $params['wechatservice']=I('wechatservice');//微信客服
        $params['phoneservice']=I('phoneservice');//电话客服
        $params['servicedescription']=$_POST['servicedescription']?$_POST['servicedescription']:$_GET['servicedescription'];//客服说明
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
           // $params['feedback']='{"feedback":{"enable":false,"form":"feedback_content"}}';
           $params['feedback']=htmlspecialchars_decode($params['feedback']);
           $params['wechatservice']=htmlspecialchars_decode($params['wechatservice']);
           $params['phoneservice']=htmlspecialchars_decode($params['phoneservice']);
           $params['servicedescription']=htmlspecialchars_decode($params['servicedescription']);
            $params['feedback']=json_decode($params['feedback'],true);  
            //print_r($params['feedback']);die;
            if($params['feedback']['feedback']['enable'] !=true){
                $params['feedback']['feedback']['form']='';
            }
            $params['feedback']=json_encode($params['feedback']);
            //print_r($params['feedback']);die;
            $db=M('default',$this->admin_arr['pre_table']);
            foreach($params as $k=>$v){
                $res=$db->where(array('customer_name'=>$k))->find();
                if(empty($res)){
                    $return=$db->add(array('customer_name'=>$k,'function_name'=>$v));
                    if(!$return){
                        $msg['code']=104;
                        $msg['msg']=$k.','.$v;
                        break;
                    }
                }else{
                    $return=$db->where(array('customer_name'=>$k))->save(array('function_name'=>$v));
                    if($return===false){
                        $msg['code']=104;
                        $msg['msg']=$k.','.$v;
                        break;
                    }
                }
            }
            if(!$msg){
                $msg['code']=200;
                foreach($params as $k=>$v){
                    $this->redis->del('admin:default:one:'.$k.':'. $this->ukey);
                }               
            }
        }
            //print_r($data);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
   }
  
   
   /*
    * 微信页面配置    联系我们状态配置
    */
   public function contactus_type(){
       $db=M('default',$this->admin_arr['pre_table']);
       $return['feedback']=$db->where(array('customer_name'=>'feedback'))->field('function_name')->find();//意见反馈
       $return['wechatservice']=$db->where(array('customer_name'=>'wechatservice'))->field('function_name')->find();//微信客服
       $return['phoneservice']=$db->where(array('customer_name'=>'phoneservice'))->field('function_name')->find();//客服电话
       $return['servicedescription']=$db->where(array('customer_name'=>'servicedescription'))->field('function_name')->find();//客服说明
       $data=$this->contacaus_action($return);
       $msg['code']=200;
       $msg['data']=$data;
       echo returnjson($msg,$this->returnstyle,$this->callback);exit();
   }
   
   public function contacaus_action(array $arr){
       
       foreach($arr as $k=>$v){
           //echo $v['function_name'];die;
           //echo $k;die;
           if($v['function_name']){
               $service=json_decode($v['function_name'],true);
               $return[$k]=$service[$k];
           }else{
               $return[$k]['enable']=false;
           }
       }
//        print_r($return);die;
       return $return;
   }
   
   
   /**
    * 微信页面配置   联系我们上传二维码图片  -- 普通上传文件
    */
    public function contactus_code_one(){
        $params['name']=I('name');
        if(in_array('', $params)){
            $msg['code']=1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $file=$_FILES['UpLoadFile'];
        $name = $file['name'];
        $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
        $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
        if(!in_array($type, $allow_type)){
            $msg['code']=104;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
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
                $db=M('default',$this->admin_arr['pre_table']);
                $return=$db->where(array('customer_name'=>'wechatservice'))->field('function_name')->find();//微信客服
                if($return){
                    $service=json_decode($return['service'],true);
                    foreach($service['service']['server'] as $k=>$v){
                        if($v['name']==$params['name']){
                            $service['service']['server'][$k]['qrcode']="https://img.rtmap.com/".$key;
                        }
                    }
                    $db->where(array('customer_name'=>'wechatservice'))->save(array('function_name'=>json_encode($service)));
                }else{
                    $arr=array('service'=>array('enable'=>true,'service'=>array(array('name'=>$params['name'],'qrcode'=>"https://img.rtmap.com/".$key))));
                    $service=json_encode($arr);
                    $res=$db->add(array('customer_name'=>'wechatservice','function_name'=>$service));
                }
                $msg['code']=200;
                $msg['data']=array('path'=>"https://img.rtmap.com/".$key);
            }
        }else{
            //echo "Failed!";
            $msg['code']=102;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    public function contactus_code_two(){
        $params['name']=I('name');
        if(in_array('', $params)){
            $msg['code']=1030;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
       $name=I('UpLoadFile');
       $name=str_replace(' ','+',$name);
       preg_match('/^(data:\s*image\/(\w+);base64,)/', $name,$result);
       $type=$result[2];
        $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
        if(!in_array($type, $allow_type)){
            $msg['code']=104;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $path=RUNTIME_PATH.'wechat/fans/'.time().".".$type;//上传文件的存放路径
        $contents=str_replace($result[1], '', $name);
        $content=base64_decode($contents);
        if(file_put_contents($path,$content)){
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
                $db=M('default',$this->admin_arr['pre_table']);
                $return=$db->where(array('customer_name'=>'wechatservice'))->field('function_name')->find();//微信客服
                if($return){
                    $service=json_decode($return['service'],true);
                    foreach($service['service']['server'] as $k=>$v){
                        if($v['name']==$params['name']){
                            $service['service']['server'][$k]['qrcode']="https://img.rtmap.com/".$key;
                        }
                    }
                    $db->where(array('customer_name'=>'wechatservice'))->save(array('function_name'=>json_encode($service)));
                }else{
                    $arr=array('service'=>array('enable'=>true,'service'=>array(array('name'=>$params['name'],'qrcode'=>"https://img.rtmap.com/".$key))));
                    $service=json_encode($arr);
                    $res=$db->add(array('customer_name'=>'wechatservice','function_name'=>$service));
                }
                $msg['code']=200;
                $msg['data']=array('path'=>"https://img.rtmap.com/".$key);
            }
        }else{
            //echo "Failed!";
            $msg['code']=102;
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    
    /**
     * 微信页面配置    联系我们上传图片
     */
    public function contactus_code(){
        $params['name']=I('name');
        $params['file']=I('UpLoadFile');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('default',$this->admin_arr['pre_table']);
            $return=$db->where(array('customer_name'=>'wechatservice'))->field('function_name')->find();//微信客服
            //dump($return);die;
            if($return){
                $service=json_decode($return['function_name'],true);
                //print_r($service);die;
                $num=count($service['service']['server']);
                //print_r($service['service']['server']);
                //echo $num;die;
                if($num>=1){
                    foreach($service['service']['server'] as $k=>$v){
                        if($v['name']==$params['name']){
                            $service['service']['server'][$k]['qrcode']=$params['file'];
                        }else{
                            $arr=array('name'=>$params['name'],'qrcode'=>$params['file']);
                            $service['service']['server'][]=$arr;
                        }
                    }
                    //print_r($service);die;
                }else{
                    $service=array('service'=>array('enable'=>true,'server'=>array(array('name'=>$params['name'],'qrcode'=>$params['file']))));
                }
                //$services=array('service'=>array('enable'=>true,'server'=>array()));
                
                $db->where(array('customer_name'=>'wechatservice'))->save(array('function_name'=>json_encode($service)));
                
           }else{
                $arr=array('service'=>array('enable'=>true,'server'=>array(array('name'=>$params['name'],'qrcode'=>$params['file']))));
                $service=json_encode($arr);
                $res=$db->add(array('customer_name'=>'wechatservice','function_name'=>$service));
            }
            $msg['code']=200;
            $msg['data']=array('path'=>$params['file']);
            $this->redis->del('admin:default:one:'.$params['name'].':'. $this->ukey);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 微信页面配置    联系我们删除接口
     */
    public function contactus_del(){
        $params['name']=I('name');
        $params['customer_name']=I('customer_name');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('default',$this->admin_arr['pre_table']);
            $array=$db->where(array('customer_name'=>$params['customer_name']))->find();
            if($array){
                $arr=json_decode($array['function_name'],true);
                foreach($arr[$params['customer_name']]['server'] as $k=>$v){
                    if($v['name'] == $params['name']){
                        unset($arr[$params['customer_name']]['server'][$k]);
                    }else{
                        $res[$params['customer_name']]['server'][]=$v;
                    }
                }
               // print_r($res);die;
                $str=json_encode($res);
                $return=$db->where(array('customer_name'=>$params['customer_name']))->save(array('function_name'=>$str));
                //echo $db->_sql();die;
                if($return !== false){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=102;
            }
        }
//         print_r($msg);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /*
     * B端意见反馈回复列表
     * 修改feedback表结构：openid默认值为空、phone默认值为空、mem_id默认值为0，新增pid、gid
     */
    public function FeedbackReplayList()
    {
        $feedbackid=I('id');
        if ($feedbackid == ''){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $dbfeedback=M('feedback', $admininfo['pre_table']);
        $where['gid']=$feedbackid;
        $where['id']=$feedbackid;
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['status']=1;
        $feedback=$dbfeedback->where($map)->order('id asc')->select();
        if ($feedbackid != null){
            $db=M('mem', $admininfo['pre_table']);
            $mem=$db->where(array('id'=>$feedback[0]['mem_id']))->find();
            
            foreach ($feedback as $key => $value) {
                if ($value['openid'] != ''){
                    $feedback[$key]['usermember']=$mem['usermember']?$mem['usermember']:'';
                }
            }
            
            $data['code']=200;
            $data['data']['feedbacklist']=$feedback;
            $data['data']['feedbackid']=$feedbackid;
            returnjson($data, $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }



    /*
     * 意见反馈回复
     */
    public function ReplayFeedback()
    {
        $params['id']=I('id');
        $params['content']=I('content');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $db=M('feedback', $admininfo['pre_table']);
        $find=$db->where(array('id'=>$params['id']))->find();//查询是否有此id
        if ($find != null){
            $find2=$db->where(array('gid'=>$params['id']))->order('id desc')->find();
            if ($find2 == null){
                $find2['id']=$params['id'];
            }
            $add=$db->add(array('pid'=>$find2['id'], 'gid'=>$params['id'], 'content'=>$params['content'], 'createtime'=>time()));
            if ($add){
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
    }
}

?>