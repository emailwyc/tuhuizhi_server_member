<?php
/**
 * 免提购物 商户端
 * User: 张行
 * Date: 2018/1/25
 * Time: 上午10:00
 */

namespace HandsFreeShopping\Controller;
use Common\Controller\CommonController;

class IndexController extends CommonController {
    protected $admin_arr;
    protected $api_url = 'https://memo.rtmap.com/d-platform-web';//外包接口地址
    protected $perInfo;//人员信息
    protected $info;

    public function _initialize()
    {
        parent::__initialize();
        $this->admin_arr = $this->getMerchant($this->ukey);
        
        $db = M('admin','total_');
        $this->info = $db->where(array('pid'=>$this->admin_arr['id']))->find();
        
        $url = $this->api_url.'/dkpt/get_auth_info';
        
        $return = http($url,array('keyAdmin'=>$this->ukey,'openId'=>$this->user_openid));
        
        $perData = json_decode($return,true);
        
        if($perData['errcode'] != 0 || empty($perData['data'])){
        
            returnjson(array('code'=>505), $this->returnstyle, $this->callback);
        }
        $perData['data']['id'] = 1;
        
        $this->perInfo = $perData['data'];
    }
    
    
    //获取人员信息
    public function PersonnelStatus(){
        $url = $this->api_url.'/dkpt/get_auth_info';
        
        $return = http($url,array('keyAdmin'=>$this->ukey,'openId'=>$this->user_openid));
        
        $perData = json_decode($return,true);
        
        if($perData['errcode'] != 0 || empty($perData['data'])){
            
            returnjson(array('code'=>2000), $this->returnstyle, $this->callback);
        }
        
        returnjson(array('code'=>200,'data'=>$perData['data']), $this->returnstyle, $this->callback);
    }
}