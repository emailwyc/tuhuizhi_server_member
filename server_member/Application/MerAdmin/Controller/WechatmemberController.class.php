<?php
/**
 * User: zhanghang
 * Date: 10/24/16
 * Time: 09:30 AM
 */


namespace MerAdmin\Controller;

use PublicApi\Controller\QiniuController;

class WechatmemberController extends AuthController {
    
    public $admin_arr;
    public $key_admin;
	public function _initialize(){
		parent::_initialize();
		$this->admin_arr=$this->getMerchant($this->ukey);
		$this->key_admin=$this->ukey;
	}
	
	
	
	/**
	 * 微信会员列表
	 */
	public function wechat_member_list(){
// 	    $startime=strtotime(I('startime'))?strtotime(I('startime')):strtotime(date('Y-m-d'))-1;
// 	    $end=strtotime(I('end'))?strtotime(I('end')):time();
// 	    $page=I('page')?I('page'):1;
// 	    $lines=I('lines')?I('lines'):10;
// 	    $map=array('subscribe_time'=>array('between',$startime.','.$end));
	    
	    $msg=$this->select_action();
	    
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	
	/**
	 * 模糊查询微信会员列表
	 */
	public function wechat_member_like(){
	    
	    
	    $msg=$this->select_action();
	    
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
	
	//公共方法
	public function select_action($status=''){    
	    $params['nickname']=I('nickname');
	    $params['sex']=I('sex');
	    $params['city']=I('city');
// 	    $startime=0;
	    $startime=strtotime(I('startime'));
	    $end=strtotime(I('end'))?strtotime(I('end')):time();
// 	    $end=strtotime(I('startime'))?strtotime(I('startime')):time();
	    $page=I('page')>1?I('page'):1;
	    $lines=I('lines')?I('lines'):10;
	     
	    if($startime){
	        $map=array('subscribe_time'=>array('between',$startime.','.$end));
	    }
	    if($params['nickname']){
	        $map['nickname'] =array('like',array('%'.$params['nickname'].'%'));
	    }
	    if($params['sex']){
	        $map['sex']=array('EQ',$params['sex']);
	    }
	    if($params['city']){
	        $map['city']=array('like',array('%'.$params['city'].'%'));
	    }
	    if(count($map)>=2){
	        $map['_logic']='and';
	    }
	    
	    $wechat_openid=M('wechat_openid',$this->admin_arr['pre_table']);
	    $count=$wechat_openid->where($map)->count();
// 	    if($status=='export'){
// 	        $page_num=ceil($count/$lines);
// 	        $page=$page>$page_num?$page_num:$page;
// 	        $pian=($page-1)*$lines;
// 	        $weixin_arr=$wechat_openid->where($map)->field('headimgurl,subscribe_time,nickname,sex,province,city,openid')->limit($pian,$lines)->order('subscribe_time desc')->select();
// 	        $par['list_export_lastsql']=$wechat_openid->_sql();
// 	        writeOperationLog($par,'zhanghang');
// 	        if($weixin_arr){
// 	            $msg['code']=200;
// 	            $msg['data']=$weixin_arr;
// 	        }else{
// 	            $msg['code']=102;
// 	        }
// 	        return $msg;die;
// 	    }

	    //echo $wechat_openid->_sql();die;
	    if($count){
	        $page_num=ceil($count/$lines);
	        $page=$page>$page_num?$page_num:$page;
	        $pian=($page-1)*$lines;
	        
	        $weixin_arr=$wechat_openid->where($map)->field('headimgurl,subscribe_time,nickname,sex,province,city,openid')->limit($pian,$lines)->order('subscribe_time desc')->select();
	        if($status=='export'){
	            $msg['code']=200;
	            $msg['data']=$weixin_arr;
	            return $msg;die;
	        }
	        foreach($weixin_arr as $k=>$v){
	            $weixin_arr[$k]['subscribe_time']=date('Y-m-d',$v['subscribe_time']);
	        }
	        
	        $msg['code']=200;
	        $msg['data']=array(
	            'data'=>$weixin_arr,
	            'page'=>$page,
	            'page_num'=>$page_num,
	            'count'=>$count
	        );
	    }else{
	        $msg['code']=102;
	    }
	    return $msg;
	}
	
	
	
	/**
	 * 导出接口
	 */
	public function wechat_member_export(){
	    $data=$this->select_action('export');
	    //print_r($data);
	    $par['list_export_data']=count($data['data']);
	    writeOperationLog($par,'zhanghang');
	    if($data['code']==200){
	        $str="OpenID(openid),关注时间(subscribe_time),昵称(nickname),性别(sex),地区(city)\r\n";
	        //echo $str;die;
	        foreach($data['data'] as $k=>$v){
	            if($v['sex']==1){
	                $sex="男";
	            }else{
	                $sex="女";
	            }
	            $str=$str.$v['openid'].",".date('Y-m-d H:i:s',$v['subscribe_time']).",".$v['nickname'].",".$sex.",".$v['city']."\r\n";
	        }
// 	        $par['list_export_strings']=$str;
	        $str=iconv("UTF-8", "GB2312//IGNORE", $str);
	        $par['list_export_string']=$str;
	        writeOperationLog($par,'zhanghang');
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
	                $msg['data']=array('path'=>"https://img.rtmap.com/".$key);
	            }
	        }else{
	            $msg['code']=$return['code'];
	        }
	    }else{
	        $msg['code']=$data['code'];
	    } 
	    echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}
    
	
	
}