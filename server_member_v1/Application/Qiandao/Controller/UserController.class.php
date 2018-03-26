<?php
namespace Qiandao\Controller;

/**
 * 
 * 会员
 * @author 张凯锋
 *
 */
class UserController extends CommonController{
    /**
     * 添加会员
     * get方式
     */
    public function createuser(){
        $wechat=new WechatController();
        $openid=$wechat->redirecturl();
        $data['openid']=$openid;
        $M=D('user');
        $add=$M->add($data);
        if ($add){
            $return = array('msg'=>'用户添加成功','status'=>true);
        }else {
            $return = array('msg'=>'用户添加失败','status'=>false);
        }
        
        
        if (I('get.callback','','htmlspecialchars')){
            echo I('get.callback','','htmlspecialchars').'('.json_encode($return).')';exit();
        }else{
            echo json_encode($return);exit();
        }
        
        
    }
    
    
    
    /**
     * 会员列表查询
     */
    public function userlist(){
        $page = !empty($_get['page']) ?I('get.page'):1;//I('get.page');//$_POST ['page'];
        $rows = !empty($_get['rows']) ? I('get.rows'):10;
        $M                              = D('user');
        $p = !empty($page)?($page - 1) * $rows:0;
        $c = $M->count ();
        $result = $M->limit ( $p, $rows )->order('id desc')->select();
        
        if (null!=$result){
            $data=array('msg'=>'查询结果成功','rows'=>$result,'total'=>$c,'status'=>true);
        }else {
            $data=array('msg'=>'没有查询到相关数据','rows'=>$result,'total'=>$c,'status'=>false);
        }
        
        if (I('get.callback','','htmlspecialchars')){
            echo I('get.callback','','htmlspecialchars').'('.json_encode($data).')';exit();
        }else{
            echo json_encode($data);exit();
        }
        
    }
    
    
}

?>