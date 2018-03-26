<?php
/**
 * ChildAccountController
 */
namespace MerAdmin\Controller;
class ChildAccountController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
        //查询商户信息
        $this->key_admin=$this->ukey;

    }

    //获取子账号列表
    public function getList(){
        $params = $this->params;
        $this->emptyCheck($params,array('page'));
        $page = ((int)$params['page'])<=0?1:((int)$params['page']);
        $offset = 10;
        $start = ($page-1)*$offset;
        $db=M('admin_child',"total_");
        $where = array('admin_id'=>$this->admin_arr['id']);
        if(!empty($params['keyword'])){
            $keyword = $params['keyword'];
            //$where["_complex"] = array("openid"=>array('like',"%$keyword%"),"nickname"=>array('like',"%$keyword%"),'_logic'=>'or');
            $where["name"] = array('like',"%$keyword%");
        }
        $arr=$db->where($where)->order("id desc")->limit($start,$offset)->select();
        $count =$db->where($where)->count();
        $allpage = ceil($count/$offset);
        $msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //getDetailById
    public function getDetailById(){
        $params = $this->params;
        $this->emptyCheck($params,array('accid'));
        if(empty($params['accid'])){
            $msg = array('code'=>11,'msg'=>'参数错误,请重新提交');
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        $db=M('admin_child',"total_");
        $arr1=$db->where(array('id'=>(int)$params['accid']))->find();

        $auth_db=M('admin_auth','total_');
        $pre = 'total_admin_auth';
        $auth = 'total_auth';
        
        $where['admin_id'] = array('eq',$this->admin_arr['id']);
        $where['auth_id'] = array('in',$arr1['auth_id']);
        $where['show_status'] = array('eq',1);
        $where['_logic'] = 'and';
         
        $res = $auth_db->where($where)->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($pre.'.auth_id asc')->select();
        // 	    print_r($res);die;
        foreach($res as $k=>$v){
            $return_data['column_name'] = $v['column_name'];
            $return_data['column_api'] = $v['column_api'];
            $column = $v['show_status'] == 2?'':$v['column_html'];
            $return_data['column_html'] = $column;
            $return_data['id'] = $v['id'];
            $auth_id[] = $v['id'];
            $return_arr[]=$return_data;
        }
        $save_auth = implode(',', $auth_id);
         
        if($save_auth != $arr1['auth_id']){
            M('admin_child','total_')->where(array('id'=>$arr1['id']))->save(array('auth_often'=>$save_auth));
        }
        
        $arr1['column'] = json_encode($return_arr);
        $arr1 = empty($arr1)?((object)array()):$arr1;
        $msg = array('code'=>200,'data'=>$arr1);
        returnjson($msg,$this->returnstyle,$this->callback);exit;
    }

    //editAccountOne
    public function editAccountOne(){
        $params = $this->params;
        $this->emptyCheck($params,array('name','password','passwords','column','buildid'));
        $db=M('admin_child',"total_");
        $insertArr = array();
        $insertArr['name'] = $params['name'];
        $insertArr['buildid'] = (int)$params['buildid'];
        $insertArr['column'] = urldecode($params['column']);
        $insertArr['auth_childid'] = urldecode($params['column2']);
        
        /*子账户判断同名账户start*/
        if(trim($params['password'])!=trim($params['passwords'])){
            returnjson(array('code' => 11, 'msg' => '两次输入密码不一致'),$this->returnstyle,$this->callback);
        }
        $child_arr=M('admin','total_')->where(array('name'=>$params['name']))->select();
        if(!empty($child_arr)) {
            returnjson(array('code' => 2001, 'msg' => '用户已存在'),$this->returnstyle,$this->callback);
        }
        
        $column = json_decode($insertArr['column'],true);
        
        foreach($column as $k=>$v){
            $auth_arr[]=$v['id'];
        }
        $insertArr['auth_id'] = implode(',', $auth_arr);
        /*子账户判断同名账户end*/
        if(empty($params['accid'])){
            //添加
            $insertArr['admin_id'] = $this->admin_arr['id'];
            $insertArr['password'] = md5(trim($params['password']));
            $child_arr1=$db->where(array('name'=>$params['name']))->select();
            if(!empty($child_arr1)) {
                returnjson(array('code'=>2001,'msg'=>'用户已存在'),$this->returnstyle,$this->callback);
            }
            $check = $db->add($insertArr);
        }else{
            //编辑
            //检查数据是否存在
            $child_check=$db->where(array('id'=>$params['accid']))->find();
            if(empty($child_check)){ returnjson(array('code'=>104),$this->returnstyle,$this->callback);}
            $insertArr['password'] = $child_check['password']==$params['password']?$params['password']:md5(trim($params['password']));
            $child_arr1=$db->where(array('name'=>$params['name'],'id'=>array('neq',$params['accid'])))->select();
            if(!empty($child_arr1)) {
                returnjson(array('code'=>2001,'msg'=>'用户已存在'),$this->returnstyle,$this->callback);
            }
            
            if($child_check['auth_often']!=''){
                
                $auth_often = explode(',',$child_check['auth_often']);
                
                foreach($auth_often as $k=>$v){
                    if(!in_array($v, $auth_arr)){
                        unset($auth_often[$k]);
                    }
                }
                if($auth_often != ''){
                    $auth_often_str = implode(',', $auth_often);
                    
                    if($child_check['auth_often'] != $auth_often_str){
                        $insertArr['auth_often'] = $auth_often_str;
                    }
                }else{
                    $insertArr['auth_often']='';
                }
                
            }
            
            $check = $db->where(array('id'=>$params['accid']))->save($insertArr);
        }
        $msg['code']=200;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //delAccountOne
    public function delAccountOne(){
        $params = $this->params;
        $this->emptyCheck($params,array('accid'));
        $db=M('admin_child',"total_");
        if(!empty($params['accid'])){
            //删除
            $db->where(array('id'=>$params['accid']))->delete();
        }
        $msg['code']=200;
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    //getColumnList
    public function getColumnList(){
        $params = $this->params;
        $arr1 = $this->getAuthIds($this->admin_arr['id']);
        $column = !empty($arr1['check_auth'])?json_decode($arr1['check_auth']):array();
        $msg['code']=200;
        $msg['data'] = $column;
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    //获取评价所有评价分类
    public function getBuildidAll(){
        $db=M('buildid',"total_");
        $arr=$db->field("id,buildid,name")->where(array("adminid"=>$this->admin_arr['id']))->select();
        $msg['code']=200;
        $msg['data']=$arr;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取主帐号下所有栏目
    public function getMenuAll(){
        //获取所有一级栏目
        $often_db = M('admin_auth','total_');
        $pre = 'total_admin_auth';
        $auth = 'total_auth';
        $often_data = $often_db->field("$auth.id,$auth.column_name")->where(array('admin_id'=>$this->admin_arr['id'],'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($auth.'.id asc')->select();
        //获取所有一级栏目下的子栏目(数据多了以后加缓存)
        $ids = ArrKeyAll($often_data,'id',0);
        $CMdb =  M('auth_child','total_');
        $menu_arr2=$CMdb->field("id,pid,menu_name,sid")->where(array('pid'=>array('in',$ids)))->order("sid asc,`order` asc")->select();
        //一级栏目和二级栏目进行数据组装
        $menu_arr_new = array();
        if($menu_arr2) {
            foreach ($menu_arr2 as $key => $val) {
                if ($val['sid'] == 0) {
                    $val['child'] = array();
                    $menu_arr_new[$val['id']] = $val;
                } else {
                    if(isset($menu_arr_new[$val['sid']])){
                        $menu_arr_new[$val['sid']]['child'][] = $val;
                    }
                }
            }
        }
        unset($menu_arr2);
        $often_data_new = array();
        foreach ($often_data as $i=>$j){
            $j['child'] = array();
            $often_data_new[$j['id']] = $j;
        }
        unset($often_data);
        foreach ($menu_arr_new as $k=>$v){
            if(!empty($often_data_new[$v['pid']])){
                $often_data_new[$v['pid']]['child'][] = $v;
            }
        }
        $msg['code']=200;
        $msg['data']=$often_data_new;//object_to_list($often_data_new);;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取主帐号下所有栏目
    public function getMenuAllByChildid(){
        $params = $this->params;
        $this->emptyCheck($params,array('accid'));
        $db=M('admin_child',"total_");
        $arr1=$db->where(array('id'=>(int)$params['accid']))->find();
        //获取所有一级栏目
        $often_db = M('admin_auth','total_');
        $pre = 'total_admin_auth';
        $auth = 'total_auth';
        $often_data = $often_db->field("$auth.id,$auth.column_name")->where(array('admin_id'=>$this->admin_arr['id'],'show_status'=>1))->join($auth.' on '.$auth.'.id='.$pre.'.auth_id')->order($auth.'.id asc')->select();
        $cloumn1 = empty($arr1['auth_id'])?array():explode(",",$arr1['auth_id']);
        $cloumn23 = empty($arr1['auth_childid'])?array():json_decode($arr1['auth_childid'],true);
        $ids = array();
        foreach ($often_data as $x=>$y){
            $often_data[$x]['status'] = in_array($y['id'],$cloumn1)?1:0;
            $ids[] = $y['id'];
        }
        //获取所有一级栏目下的子栏目(数据多了以后加缓存)
        $CMdb =  M('auth_child','total_');
        $menu_arr2=$CMdb->field("id,pid,menu_name,sid")->where(array('pid'=>array('in',$ids)))->order("sid asc,`order` asc")->select();
        //一级栏目和二级栏目进行数据组装
        $menu_arr_new = array();
        if($menu_arr2) {
            foreach ($menu_arr2 as $key => $val) {
                $val['status'] = in_array($val['id'],$cloumn23)?1:0;
                if ($val['sid'] == 0) {
                    $val['child'] = array();
                    $menu_arr_new[$val['id']] = $val;
                } else {
                    if(isset($menu_arr_new[$val['sid']])) {
                        $menu_arr_new[$val['sid']]['child'][] = $val;
                    }
                }
            }
        }
        unset($menu_arr2);
        $often_data_new = array();
        foreach ($often_data as $i=>$j){
            $j['child'] = array();
            $often_data_new[$j['id']] = $j;
        }
        unset($often_data);
        foreach ($menu_arr_new as $k=>$v){
            if(!empty($often_data_new[$v['pid']])){
                $often_data_new[$v['pid']]['child'][] = $v;
            }
        }
        $msg['code']=200;
        $msg['data']=$often_data_new;//object_to_list($often_data_new);;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

}

?>