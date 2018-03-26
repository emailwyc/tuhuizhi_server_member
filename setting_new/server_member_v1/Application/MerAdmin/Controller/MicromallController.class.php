<?php
/**
 * 微商城 -- 泡泡
 */
namespace MerAdmin\Controller;
// use Common\Controller\CommonController;
class MicromallController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        
        parent::_initialize();
        //查询商户信息
        $this->admin_arr=$this->getMerchant($this->ukey);           
        $this->key_admin=$this->ukey;
        
    }
    
    
    /**
     * 获取center导航列表
     */
    public function get_navigation_center(){
        $db=M('navigation',$this->admin_arr['pre_table']);
		$arr=$db->where(array('position'=>'center'))->find();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
            $msg['data']='请找管理员添加center navigation';
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    /**
     * edit center导航列表
     */
    public function edit_navigation_center(){
        $params['bg_color']=I('bg_color');
		$db=M('navigation',$this->admin_arr['pre_table']);
		$uparr = array('bg_color'=>$params['bg_color']);
		$res=$db->where(array('position'=>'center'))->save($uparr);
		$msg['code']=200;
		$msg['msg']="success";
        returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

    /**
     * 获取导航列表
     */
    public function navigation_list(){
        $db=M('navigation',$this->admin_arr['pre_table']);
        $arr=$db->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
            $msg['data']='请找管理员添加广告';
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 修改导航状态
     */    
    public function navigation_status(){
        $params['position']=I('position');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            $db=M('navigation',$this->admin_arr['pre_table']);
            $where['position']=array('eq',$params['position']);
            $nav_data=$db->where($where)->find();
            if($nav_data){
                $data['status']=$nav_data['status']==1?2:1;
                $res=$db->where($where)->save($data);
                if($res){
                    $msg['code']=200;
                }else{
                    $msg['code']=104;
                }
            }else{
                $msg['code']=102;
            }
            
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 获取顶部导航接口
     */
    public function navigation_top(){
        $db=M('navigation',$this->admin_arr['pre_table']);
        $arr=$db->where(array('position'=>array('eq','top')))->find();
        if($arr){
            $resour_db=M('nav_resour',$this->admin_arr['pre_table']);
            if($arr['status']==1){
                $top_wehre['type_id']=array('eq',$arr['id']);
                $top_wehre['sort']=array('eq',1);
                $top_wehre['_logic']='and';
                $top_arr=$resour_db->where($top_wehre)->find();
                $msg['code']=200;
                $msg['data']['status']=$arr['status'];
                $msg['data']['url']=$top_arr['property'];
            }else{
                $msg['code']=200;
                $msg['data']['status']=$arr['status'];
            }
        }else{
            $msg['code']=102;
            $msg['data']='请找管理员添加广告';
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit(); 
    }
    
    
    /**
     * 获取功能区导航接口
     */
    public function navigation_center(){
        $db=M('navigation',$this->admin_arr['pre_table']);
        $arr=$db->where(array('position'=>array('eq','center')))->find();
        if($arr){
            $resour_db=M('nav_resour',$this->admin_arr['pre_table']);
            $top_wehre['type_id']=array('eq',$arr['id']);
            $center_arr=$resour_db->where($top_wehre)->select();
            $msg['code']=200;
//             $msg['data']['status']=$arr['status'];
            $msg['data']=$center_arr;
        }else{
            $msg['code']=102;
            $msg['data']='请找管理员添加广告';
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 获取底部导航接口
     */
    public function navigation_foot(){
        $db=M('navigation',$this->admin_arr['pre_table']);
        $arr=$db->where(array('position'=>array('eq','foot')))->find();
        if($arr){
            $resour_db=M('nav_resour',$this->admin_arr['pre_table']);
            if($arr['status']==1){
                $top_wehre['type_id']=array('eq',$arr['id']);
                $top_arr=$resour_db->where($top_wehre)->find();
                $msg['code']=200;
                $msg['data']['status']=$arr['status'];
                $msg['data']['url']=$top_arr['property'];
            }else{
                $msg['code']=200;
                $msg['data']['status']=$arr['status'];
            }
        }else{
            $msg['code']=102;
            $msg['data']='请找管理员添加广告';
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
    * 广告添加/修改接口
    */
    public function ad_operate(){
       $db = M('nav_resour',$this->admin_arr['pre_table']);
       $db_nav = M('navigation',$this->admin_arr['pre_table']);
       $params = I('param.');
       $ad_id = $params['ad_id'];
       $status = $params['status'];
       $params['createtime'] = date("Y-m-d H:i:s",time());
       if($params == '' || $status == '' || $params['position'] == '' || $params['name'] == '' || $params['property'] == ''){
            $msg['code'] = 1004;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
       }
       if(empty($params['link']) && empty($params['content'])){
            $msg['code'] = 1004;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
       }
       unset($params['key_admin']);
       unset($params['status']);
       unset($params['ad_id']);
       $nav_info = $db_nav->where(array('position'=>$params['position']))->find();
       $params['type_id'] = $nav_info['id'];
       $max_sort = $db->order('sort desc')->field('sort')->find();
       switch ($params['position']) {
            case 'top':
                $count = $db->where(array('type_id'=>$nav_info['id']))->count();
                if($status == 1){
                    if($count >= 5){
                        $msg['code'] = 1009;
                        returnjson($msg,$this->returnstyle,$this->callback);exit();
                    }else{
						$params['sort'] = $max_sort['sort']+1;
                        $db->add($params);
                        $msg['code'] = 200;
                    }                    
                }else if($status == 2){
                    $db->where(array('id'=>$ad_id))->save($params);
                    $msg['code'] = 200;
                }else{
                    $msg['code'] = 1004;
                }
               break;
            case 'center':
                if($params['sort'] == ''){
                    $msg['code'] = 1004;
                }else{
                    $menu_arr = $db->where(array('sort'=>$params['sort'],'type_id'=>$nav_info['id']))->find();
                    if(!$menu_arr){
                       $db->add($params); 
                       $msg['code'] = 200;
                    }else{
                        $db->where(array('sort'=>$params['sort'],'type_id'=>$nav_info['id']))->save($params);
                        $msg['code'] = 200;
                    }
                }
               break;
            case 'foot':
               $foot_arr = $db->where(array('type_id'=>$nav_info['id']))->find();
                if($foot_arr){
                    $db->where(array('id'=>$foot_arr['id']))->save($params);
                }else{
                    $db->add($params);
                }
                $msg['code'] = 200;
               break;
           default:
                $msg['code'] = 1004;
               break;
       }
       returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    /**
    * 广告编辑
    */
    public function ad_update(){
        $db = M('nav_resour',$this->admin_arr['pre_table']);
        $db_nav = M('navigation',$this->admin_arr['pre_table']);
        $ad_id = I('ad_id');
        $position = I('position');
        if($ad_id == '' && $position == 'top'){
            $msg['code'] = 1004;
        }else{
            if($position == 'foot'){
                $nav_info = $db_nav->where(array('position'=>$position))->find();
                $msg['data'] = $db->where(array('type_id'=>$nav_info['id']))->find();
                if($msg['data'] == ''){
					$msg['data'] = array('id'=>'','name'=>'','link'=>'','author'=>'','content'=>'','property'=>'','sort'=>'','type_id'=>'','createtime'=>'');
				}else{
					$msg['data']['content'] = htmlspecialchars_decode($msg['data']['content']);
				}
                $msg['code'] = 200;
            }else if($position == 'center' && $ad_id == ''){
				$msg['data'] = array('id'=>'','name'=>'','link'=>'','author'=>'','content'=>'','property'=>'','sort'=>'','type_id'=>'','createtime'=>'');
                $msg['code'] = 200;
            }else{
                $msg['data'] = $db->where(array('id'=>$ad_id))->find();
                $msg['data']['content'] = htmlspecialchars_decode($msg['data']['content']);
                if($msg['data'] == ''){
                    $msg['code'] = 102;
                }else{
                    $msg['code'] = 200;
                }
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    /**
    * 广告删除
    */
    public function ad_del(){
        $db = M('nav_resour',$this->admin_arr['pre_table']);
        $ad_id = I('ad_id');
        $ad_info = $db->where(array('id'=>$ad_id))->field('type_id,sort')->find();
        if($ad_id == ''){
            $msg['code'] = 1004;
        }else{
            $res = $db->where(array('id'=>$ad_id))->delete();
            if(!$res){
                $msg['code'] = 104;
            }else{
                $db->where(array('type_id'=>$ad_info['type_id']))
                ->where(array('sort'=>array('gt',$ad_info['sort'])))->setDec('sort');
                $msg['code'] = 200;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    /**
    * 广告置顶
    */
    public function ad_top(){
        $db = M('nav_resour',$this->admin_arr['pre_table']);
        $ad_id = I('ad_id');
        if($ad_id == ''){
            $msg['code'] = 1004;
        }else{
            $res = $db->where(array('id'=>$ad_id))->find();
            if(!$res){
                $msg['code'] = 102;
                returnjson($msg,$this->returnstyle,$this->callback);exit();
            }
            if($db->where(array('type_id'=>$res['type_id'],'sort'=>array('lt',$res['sort'])))->setInc('sort')){
                if($db->where(array('id'=>$ad_id))->save(array('sort'=>1))){
                    $msg['code'] = 200;
                }else{
                    $msg['code'] = 104;
                }
            }else{
                $msg['code'] = 104;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    /**
    * 广告列表
    */
    public function ad_list(){
        $db = M('nav_resour',$this->admin_arr['pre_table']);
        $db_nav = M('navigation',$this->admin_arr['pre_table']);
        $position = I('position');
        if($position == ''){
            $msg['code'] = 1004;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $nav_id = $db_nav->where(array('position'=>$position))->field('id')->find();
		$data = $db->where(array('type_id'=>$nav_id['id']))->order('sort asc')->select();
		if($position == 'center'){
			$data = ArrKeyFromId($data,'sort');
		}
        if(count($data) < 8 && $position == 'center'){
            for ($i=1; $i < 9; $i++) { 
                if(!$data[$i]){
					$data[$i] = array('id'=>'','name'=>'','link'=>'','author'=>'','content'=>'','property'=>'','sort'=>$i,'type_id'=>'','createtime'=>'');
                }
            }
        }
        foreach ($data as $key => $value) {
            $data[$key]['content'] = htmlspecialchars_decode($value['content']);
        }
        if($msg['data'] == ''){
            $msg['code']=102;
        }
        $msg['code']=200;
        $msg['data']=$data;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    /**
    * 泡泡c端列表
    */
    public function bubble_list(){
        $db = M('nav_resour',$this->admin_arr['pre_table']);
        $db_nav = M('navigation',$this->admin_arr['pre_table']);
        $position = "center";
        if($position == ''){
            $msg['code'] = 1004;
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $nav_id = $db_nav->where(array('position'=>$position))->field('id')->find();
        if($data = $db->where(array('type_id'=>$nav_id['id']))->select()){
            $arr = json_decode($this->hubble(),true);
            $num = 0;
            foreach ($arr['items'] as $key => $value) {
                if($value['value'] != ''){
                    $arr['items'][$key]['value'] = $data[$num]['name'];
                    if($data[$num]['property'] != ''){
                        $arr['items'][$key]['backgroundColor'] = $data[$num]['property'];
                    }
                    $arr['items'][$key]['linkUrl'] = $data[$num]['link'];
                    $num++;
                }
            }
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    /**
    * 泡泡规则
    */
    public function hubble(){
        return $json_str = '{
            "config": {
                "title": "西单大悦城",
                "background": "#ffffff",
                "height": "320"
            },
            "items": [
                {
                    "timestamp": 1441524278233,
                    "index": 0,
                    "value": "极致<br>单品",
                    "screenY": "43px",
                    "screenX": "42px",
                    "lineHeight": 19,
                    "textAlign": "center",
                    "fontSize": 12,
                    "borderRadius": 99,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#00d7ff",
                    "color": "#ffffff",
                    "height": "72",
                    "width": "72",
                    "type": "text",
                    "linkUrl": "sku"
                },
                {
                    "timestamp": 1441524793892,
                    "index": 2,
                    "value": "优惠",
                    "screenY": "28px",
                    "screenX": "160px",
                    "lineHeight": 15,
                    "textAlign": "center",
                    "fontSize": 12,
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#89de61",
                    "color": "#ffffff",
                    "height": "60",
                    "width": "60",
                    "type": "text",
                    "linkUrl": "./index.php?action=sale"
                },
                {
                    "timestamp": 1441524735532,
                    "index": 1,
                    "value": "",
                    "screenY": "27px",
                    "screenX": "112px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#4ce7a4",
                    "color": "#000",
                    "height": "36",
                    "width": "36",
                    "type": "text"
                },

                {
                    "timestamp": 1441524838650,
                    "index": 3,
                    "value": "",
                    "screenY": "69px",
                    "screenX": "122px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 97,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#8fc64c",
                    "color": "#000",
                    "height": "32",
                    "width": "32",
                    "type": "text"
                },
                {
                    "timestamp": 1441524917995,
                    "index": 4,
                    "value": "",
                    "screenY": "119px",
                    "screenX": "20px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#b14ce3",
                    "color": "#000",
                    "height": "38",
                    "width": "38",
                    "type": "text"
                },
                {
                    "timestamp": 1441524965224,
                    "index": 5,
                    "value": "",
                    "screenY": "127px",
                    "screenX": "65px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#0079ac",
                    "color": "#000",
                    "height": "36",
                    "width": "36",
                    "type": "text"
                },
                {
                    "timestamp": 1441525011709,
                    "index": 6,
                    "value": "美食",
                    "screenY": "171px",
                    "screenX": "19px",
                    "lineHeight": 19,
                    "textAlign": "center",
                    "fontSize": 12,
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#f800a0",
                    "color": "#ffffff",
                    "height": "70",
                    "width": "70",
                    "type": "text",
                    "linkUrl": "http://api.mwee.cn/api/web/weixin/near.php?token=9e4773d8e05c80a49cf420198ac4fb5a509279d9&mall=22694&menu=1"
                },
                {
                    "timestamp": 1441525063276,
                    "index": 7,
                    "value": "",
                    "screenY": "248px",
                    "screenX": "66px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#00ae7c",
                    "color": "#000",
                    "height": "40",
                    "width": "40",
                    "type": "text"
                },
                {
                    "timestamp": 1441525110276,
                    "index": 8,
                    "value": "",
                    "screenY": "218px",
                    "screenX": "98px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#c500ff",
                    "color": "#000",
                    "height": "28",
                    "width": "28",
                    "type": "text"
                },
                {
                    "timestamp": 1441525155179,
                    "index": 9,
                    "value": "",
                    "screenY": "255px",
                    "screenX": "114px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ad4ca1",
                    "color": "#000",
                    "height": "42",
                    "width": "42",
                    "type": "text"
                },
                {
                    "timestamp": 1441525223341,
                    "index": 10,
                    "value": "看电影",
                    "screenY": "55px",
                    "screenX": "229px",
                    "lineHeight": "26",
                    "textAlign": "center",
                    "fontSize": "11",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ffc164",
                    "color": "#fff",
                    "height": "55",
                    "width": "55",
                    "type": "text",
                    "linkUrl": "http://smart.wepiao.com/sddyy/cinema/1002149"
                },
                {
                    "timestamp": 1441525272538,
                    "index": 11,
                    "value": "会员",
                    "screenY": "110px",
                    "screenX": "118px",
                    "lineHeight": 17,
                    "textAlign": "center",
                    "fontSize": 12,
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#00c466",
                    "color": "#ffffff",
                    "height": "60",
                    "width": "60",
                    "type": "text",
                    "linkUrl": "./index.php?action=user"
                },
                {
                    "timestamp": 1441525365732,
                    "index": 13,
                    "value": "",
                    "screenY": "175px",
                    "screenX": "144px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#5575c5",
                    "color": "#000",
                    "height": "32",
                    "width": "32",
                    "type": "text"
                },
                {
                    "timestamp": 1441525421751,
                    "index": 14,
                    "value": "",
                    "screenY": "167px",
                    "screenX": "94px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 99,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#00adad",
                    "color": "#000",
                    "height": "43",
                    "width": "43",
                    "type": "text"
                },
                {
                    "timestamp": 1441525474778,
                    "index": 15,
                    "value": "",
                    "screenY": "212px",
                    "screenX": "136px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ff6ba4",
                    "color": "#000",
                    "height": "40",
                    "width": "40",
                    "type": "text"
                },
                {
                    "timestamp": 1441525514482,
                    "index": 16,
                    "value": "地图",
                    "screenY": "250px",
                    "screenX": "168px",
                    "lineHeight": 16,
                    "textAlign": "center",
                    "fontSize": 11,
                    "borderRadius": 99,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ff868b",
                    "color": "#ffffff",
                    "height": "60",
                    "width": "60",
                    "type": "text",
                    "linkUrl": "./index.php?action=map"
                },
                {
                    "timestamp": 1441525576465,
                    "index": 17,
                    "value": "",
                    "screenY": "230px",
                    "screenX": "233px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ef00a6",
                    "color": "#000",
                    "height": "40",
                    "width": "40",
                    "type": "text"
                },
                {
                    "timestamp": 1441525615648,
                    "index": 18,
                    "value": "团购",
                    "screenY": "187px",
                    "screenX": "180px",
                    "lineHeight": "26",
                    "textAlign": "center",
                    "fontSize": "12",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#b11f9f",
                    "color": "#fff",
                    "height": "55",
                    "width": "55",
                    "type": "text",
                    "linkUrl": "https://h5.rtmap.com/purchase?key_admin=e4273d13a384168962ee93a953b58ffd"
                },
                {
                    "timestamp": 1441525700022,
                    "index": 19,
                    "value": "",
                    "screenY": "139px",
                    "screenX": "188px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ff7100",
                    "color": "#000",
                    "height": "38",
                    "width": "38",
                    "type": "text"
                },
                {
                    "timestamp": 1441525737283,
                    "index": 20,
                    "value": "",
                    "screenY": "93px",
                    "screenX": "194px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#ffdc4c",
                    "color": "#000",
                    "height": "34",
                    "width": "34",
                    "type": "text"
                },
                {
                    "timestamp": 1441525773343,
                    "index": 21,
                    "value": "",
                    "screenY": "120px",
                    "screenX": "226px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#fa4cbc",
                    "color": "#000",
                    "height": "32",
                    "width": "32",
                    "type": "text"
                },
                {
                    "timestamp": 1441525806051,
                    "index": 22,
                    "value": "",
                    "screenY": "112px",
                    "screenX": "265px",
                    "lineHeight": "26",
                    "textAlign": "left",
                    "fontSize": "14",
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#c04cb2",
                    "color": "#000",
                    "height": "40",
                    "width": "40",
                    "type": "text"
                },
                {
                    "timestamp": 1441525846559,
                    "index": 23,
                    "value": "“停”<br>车场",
                    "screenY": "159px",
                    "screenX": "244px",
                    "lineHeight": 15,
                    "textAlign": "center",
                    "fontSize": 11,
                    "borderRadius": 100,
                    "borderColor": "black",
                    "borderWidth": "0",
                    "borderType": "none",
                    "backgroundColor": "#942cb1",
                    "color": "#ffffff",
                    "height": "60",
                    "width": "60",
                    "type": "text",
                    "linkUrl": "./index.php?action=map&type=1&floor=b3"
                }
            ]
        }';
    }
}

?>
