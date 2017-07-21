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
        /*子账户判断同名账户start*/
        if(trim($params['password'])!=trim($params['passwords'])){
            returnjson(array('code' => 11, 'msg' => '两次输入密码不一致'),$this->returnstyle,$this->callback);
        }
        $child_arr=M('admin','total_')->where(array('name'=>$params['name']))->select();
        if(!empty($child_arr)) {
            returnjson(array('code' => 2001, 'msg' => '用户已存在'),$this->returnstyle,$this->callback);
        }
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
        $db=M('auth_admin',"total_");
        $arr1 =$db->where(array('admin_id'=>$this->admin_arr['id']))->find();
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

}

?>
