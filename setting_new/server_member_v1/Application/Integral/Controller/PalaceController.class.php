<?php
/**
 * 王府中环(C端)
 * Created by EditPlus.
 * User: wutong
 * Date: 10/19/17
 * Time: 10:25
 */

namespace Integral\Controller;
use Think\Controller;
use Common\Controller\CommonController;
use common\ServiceLocator;
use Sign\Controller\YcoinController;
use MerAdmin\Model\PropertyModel;

class PalaceController extends CommonController
{
    public $admin_arr;
    public $key_admin;
    public $url;

    public function _initialize(){
        parent::__initialize();
        $key_admin = I('key_admin');
        if(empty($key_admin))
        {
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        else
        {
            $adminService = ServiceLocator::getAdminService();
            $admin_arr = $adminService->getByUkey($key_admin);

            if(empty($admin_arr))
            {
                echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
            }
            else
            {
                $this->admin_arr = $admin_arr;
                $this->key_admin = $key_admin;
            }
            
            if(C('DOMAIN') == 'http://mem.rtmap.com' || C('DOMAIN') == 'https://mem.rtmap.com')
            {
                $this->url = 'https://memo.rtmap.com';
            }
            else
            {
                $this->url = 'http://zht.wemalltech.com';
            } 
        }
    }

    /**
     * 主题界面
     * localhost/member/index.php/Integral/Palace/tags_thems?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function tags_thems(){
        $tagsThemsService = ServiceLocator::getTagsThemsService();
        $themgroupBannersService = ServiceLocator::getThemgroupBannersService();
        
        $data['tagsThems'] = $tagsThemsService->getAll($this->admin_arr['id']);
        $data['themgroupBanners'] = $themgroupBannersService->getAll($this->admin_arr['id']);
        
        $msg = array('code' => 200,'data' => $data);
        
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 主题详情
     * localhost/member/index.php/Integral/Palace/tags_thems_details?key_admin=202cb962ac59075b964b07152d234b70&id=1
     */
    public function tags_thems_details(){
        $key_admin = I('key_admin');
        $params['id'] = I('id');
    
        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $data = array();
        $tagsThemsBannersService = ServiceLocator::getTagsThemsBannersService();
        $banners = $tagsThemsBannersService->getAll($this->admin_arr['id'], $params['id']);
        
        $tagsThemsTagsService = ServiceLocator::getTagsThemsTagsService();
        $tags = $tagsThemsTagsService->getAll($this->admin_arr['id'], $params['id']);
        
        $data['banners'] = $banners;
        $data['tags'] = $tags;

        $msg = array('code' => 200,'data' => $data);
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 自制行程选项界面
     * http://localhost/member/index.php/Integral/Palace/self_trip?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function self_trip(){
        $params['openid'] = I('openid');
        $tagsGroupService = ServiceLocator::getTagsGroupService();
        $data = $tagsGroupService->getAll($this->admin_arr['id']);

        if(empty($data))
        {
            $msg = array('code'=>102);
        }
        else
        {
            //已选项
            $tagsChooseService = ServiceLocator::getTagsChooseService();
            $mychoose = $tagsChooseService->getOnce($this->admin_arr['id'], $params['openid']);
            
            $choosed = array();
            if(!empty($mychoose['tagids']))
            {
               $choosed = explode(',', $mychoose['tagids']);
            }
            
            $arr = array();
            $tagsGroupTagsService = ServiceLocator::getTagsGroupTagsService();
            $tagsService = ServiceLocator::getTagsService();
            foreach ($data as $k => $v)
            {
                $arr[$k]['id']  = $v['id'];
                $arr[$k]['name'] =  $v['groupname'];//分组名
                $arr[$k]['maxselect'] =  $v['maxselect'];//最大选择数量
                $arr[$k]['tags'] = array();
                
                $groupTags = $tagsGroupTagsService->getAll($this->admin_arr['id'], $v['id']);
                if(!empty($groupTags))
                {
                    foreach ($groupTags as $k2 => $v2)
                    {
                       $tag  = $tagsService->getOnce($v2['tagid']);
                       if(!empty($tag))
                       {
                           if(!empty($choosed))
                           {
                               foreach ($choosed as $k3 => $v3)
                               {
                                   $ids = explode('-', $v3);
                                   
                                   if($v['id'] == $ids[0] && $tag['id'] == $ids[1])
                                   {
                                       $tag['ischoose'] = 1;break;
                                   }
                                   else
                                   {
                                       $tag['ischoose'] = 0;
                                   }
                               }
                           }
                           
                           $arr[$k]['tags'][] = $tag;
                       }
                    }
                }
                
            }
            
            $msg = array('code'=>200,'data'=>$arr);
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 自制行程保存选项
     * http://localhost/member/index.php/Integral/Palace/choose_tags?key_admin=202cb962ac59075b964b07152d234b70&ids=6-19,6-21,7-22&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function choose_tags(){
        $params['openid'] = I('openid');
        $params['ids'] = I('ids');
        
        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        //选项保存
        $tagsChooseService = ServiceLocator::getTagsChooseService();
        $data = $tagsChooseService->getOnce($this->admin_arr['id'], $params['openid']);
        
        if(empty($data))
        {
            $insert['adminid'] = $this->admin_arr['id'];
            $insert['openid']  = $params['openid'];
            $insert['tagids']  = $params['ids'];
            $insert['ctime']   = date('Y-m-d H:i:s', time());
            $tagsChooseService->add($insert);
        }
        else
        {
            $data['tagids'] = $params['ids'];
            $tagsChooseService->updateById($data['id'], $data);
        }
        
        $msg = array('code'=>200);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 自制行程信息列表
     * http://localhost/member/index.php/Integral/Palace/self_trip_list?key_admin=202cb962ac59075b964b07152d234b70&buildid=860100010040500017&ids=75&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function self_trip_list(){
        $params['ids'] = I('ids');
        $params['openid'] = I('openid');
        
        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        $params['buildid'] = I('buildid');

        $ids = json_encode(array(array('tags' => $params['ids'], 'type' => 'duo')));
        
        $url = $this->url."/marketweb/actionweb/queryActionsForActivity";//根据多个标签查询同时拥有这些标签的 活动
        $arr['keyAdmin'] = $this->key_admin;
        $arr['buildId']  = $params['buildid'];
        $arr['tagids']      = $ids;//tagids   标签id ， '[{"tags":"1","type":"dan"},{"tags":"1","type":"dan"},{"tags":"75,76","type":"duo"}]'
        $arr['openid']  = $params['openid'];
        $arr['tagnum']   = count(explode(',', $params['ids']));
    
        $return = json_decode(http($url,$arr), true);//处理返回结果
        
        $data['tags'] = array();
        $data = array();
        $data['activity'] = $return['actionlist'];
        
        $url = $this->url."/marketweb/mappoiweb/queryPoisForActivity";//根据多个标签查询同时拥有这些标签的 店铺 
        $arr2['keyAdmin'] = $this->key_admin;
        $arr2['buildId']  = $params['buildid'];
        $arr2['tagids']      = $ids;
        $arr2['openid']  = $params['openid'];
        $arr2['tagnum']   = count(explode(',', $params['ids']));
        
        $marketData = json_decode(http($url,$arr2), true);//处理返回结果
        $data['shop'] = $marketData['poilist'];
        
        $tagsService = ServiceLocator::getTagsService();
        $tags  = $tagsService->getIdIn($params['ids']);
        
        $data['tags'] = $tags;
        
        $msg = array('code' => $return['code'] ? $return['code'] : 200, 'data' => $data);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查询推荐的活动
     * localhost/member/index.php/Integral/Palace/recommend_activity?key_admin=202cb962ac59075b964b07152d234b70&buildid=860100010040500017&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function recommend_activity(){
        $params['openid'] = I('openid');
        
        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $params['buildid']  = I('buildid');
        
        $url = $this->url.'/marketweb/actionweb/queryRecActions';//根据活动id获取体验信息
        $arr['keyAdmin'] = $this->key_admin;
        $arr['buildId']  = $params['buildid'];
        $arr['openid']   = $params['openid'];
    
        $return = json_decode(http($url,$arr), true);//处理返回结果
        
        $msg = array('code' => $return['code'] ? $return['code'] : 200, 'data' => $return['actionlist'] ? $return['actionlist'] : array());
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 体验信息列表
     * localhost/member/index.php/Integral/Palace/theme_list?key_admin=202cb962ac59075b964b07152d234b70&buildid=860100010040500017&ids=1&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function theme_list(){
        $params['openid'] = I('openid');

        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $params['ids'] = I('ids');
        $params['buildid'] = I('buildid');

        $url = $this->url."/marketweb/actionweb/queryActionsForTheme";//根据活动id获取体验信息
        $arr['keyAdmin'] = $this->key_admin;
        $arr['buildId']  = $params['buildid'];
        $arr['tagids']   = $params['ids'];
        $arr['openid']   = $params['openid'];

        $return = json_decode(http($url,$arr), true);//处理返回结果

        $msg = array('code' => $return['code'] ? $return['code'] : 200, 'data' => $return['actionlist']);

        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 根据活动id获取活动信息
     * http://localhost/member/index.php/Integral/Palace/self_trip_detail?key_admin=202cb962ac59075b964b07152d234b70&id=1&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function self_trip_detail(){
        $params['id'] = I('id');
        $params['openid'] = I('openid');
        
        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $commonService = ServiceLocator::getCommonService();
        $return = $commonService->self_trip_detail($this->url, $this->key_admin, $params['openid'], $params['id']);//根据活动id获取活动信息
        
        $msg = array('code' => 200, 'data' => $return['action'], 'msg' => $return['msg']);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 根据店铺id获取店铺信息
     * localhost/member/index.php/Integral/Palace/shop_detail?key_admin=202cb962ac59075b964b07152d234b70&id=1
     */
    public function shop_detail(){
        $params['buildid'] = I('buildid');
        $params['openid'] = I('openid');
        $params['poiid'] = I('poiid');//楼层
        
        if(in_array('', $params))
        {
            $msg['code'] = 100;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
    
        $commonService = ServiceLocator::getCommonService();
        $return = $commonService->shop_detail($this->url, $this->key_admin, $params['buildid'], $params['openid'], $params['poiid']);//根据活动id获取活动信息
    
        $msg = array('code' => $return['code'], 'data' => $return['action']);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 支付
     * @param $key_admin $pid $main $openid
     * @return mixed
     * localhost/member/index.php/Integral/Palace/exchange?key_admin=e52693d642d3d9f61a7cf90990f38d6a&id=157&openid=oWm-rt-q1wfKpYO80kRhi_UizYpE&main=test&activity=165&infoStr=t&ticketStr=t&orderId=20&amount=1&childopenid=oWm-rt-q1wfKpYO80kRhi_UizYpE
     */
    public function exchange(){
        $id = I('id');//商品唯一id
        $main = I('main');
        $openid = I('openid');
        $childopenid = I('childopenid');
        $pay_class = I('pay_class');
        $orderId = I('orderId');//第三方支付id
        $type = I('type');// 0普通活动 1会员活动
        $activity_id = I('activity');
        $payType = I('payType') ? I('payType') : 2;//付款方式 1积分 2微信
        
        if(empty($id) || empty($main) || empty($openid) || empty($orderId)  || empty($activity_id)  || empty($payType))
        {
            $msg = array('code'=>1030);
        }
        else
        {
            $main = str_replace(array('&','?'), '', $main);
            
            $params['buildid'] = I('buildid') ? I('buildid') : "";

            $userCardService = ServiceLocator::getUserCardService();
            $user_arr = $userCardService->getUserCardByOpenId($this->admin_arr, $this->ukey, $this->admin_arr['pre_table'], $openid);
            
            //判断用户是否存在
            if($user_arr == 2000 && $type == 1)
            {
                returnjson(array('code'=>2000),$this->returnstyle,$this->callback);
            }
            
            //判断奖品数据是否存在
            $commonService = ServiceLocator::getCommonService();
            
            //积分支付
            if($payType == 1)
            {
                $amount = I('amount') ? I('amount') : 0;
                
                //判断用户积分是否充足
                $arr = $userCardService->getUserCardInfo($this->key_admin, $user_arr['cardno'], $this->admin_arr['signkey']);
                
                if($arr['data']['score'] < (int)$amount){
                    returnjson(array('code'=>319),$this->returnstyle,$this->callback);
                }
                
                //扣除用户积分积分
                if($amount == 0)//花费为0或者免费
                {
                    $res['code'] = 200;
                }
                else
                {
                    $res = $userCardService->del_integral($this->key_admin,$this->admin_arr['signkey'],$user_arr['cardno'],$amount,$main);
                }
                
                if($res['code'] >= 6000 && $res['code'] < 7000)
                {
                    returnjson($res,$this->returnstyle,$this->callback);
                }
                
                if($res['code'] != 200)
                {
                    $msg = array('code'=>$res['code']);
                    returnjson($msg,$this->returnstyle,$this->callback);
                }
                
                $url = $this->url."/marketweb/actionweb/finishpay";
                $arr['keyAdmin'] = $this->key_admin;
                $arr['orderId']  = $orderId;//外包订单id
                $arr['outtradeno']   = 'MIANFEI';//微信订单id
                $arr['paytype']   = $payType;//付款方式 1积分 2微信
                
                $return = json_decode(http($url,$arr), true);//处理返回结果
                
                $data =  array('code' => '200', 'msg' => 'SUCCESS!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
            else
            {
                $amount = I('amount') ? I('amount') * 100 : 0;
                
                //如果是免费券直接发券
                if($amount == 0)
                {
                    $url = $this->url."/marketweb/actionweb/finishpay";
                    $arr['keyAdmin'] = $this->key_admin;
                    $arr['orderId']  = $orderId;//外包订单id
                    $arr['outtradeno']   = 'MIANFEI';//微信订单id
                    $arr['paytype']   = $payType;//付款方式 1积分 2微信
                    $return = json_decode(http($url,$arr), true);//处理返回结果
                
                    $data =  array('code' => '200', 'msg' => 'SUCCESS!');
                    returnjson($data, $this->returnstyle, $this->callback);
                }
                
                //微信支付
                $notify_url = C('DOMAIN') . "/Integral/Wechat/palaceConfirmPay";
                
                //微信回调参数
                $attach = array(
                    'name' => $main,
                    'key_admin' => $this->key_admin,
                    'amount' => $amount,
                    'openid' => $openid,
                    'activityId' => $activity_id,
                    'pid' => $id,
                    'cardno' => $user_arr['cardno'],
                    'pre_table' => $this->admin_arr['pre_table'],
                    'signkey' => $this->admin_arr['signkey'],
                );
                
                $data = $commonService->newPaybyweixin($this->key_admin, $openid, $amount, $main, $notify_url, $this->admin_arr['pre_table'], $pay_class, $attach, $this->admin_arr['id'], $orderId, $childopenid, 2);
                
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
        
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 获取支付相关信息
     * @return mixed
     * http://localhost/member/index.php/Integral/Palace/get_pay_info?key_admin=e52693d642d3d9f61a7cf90990f38d6a
     */
    public function get_pay_info(){
        $buildid_db = M('total_buildid');
    
        $arr['adminid'] = $this->admin_arr['id'];
        $commonService = ServiceLocator::getCommonService();
        $data = $commonService->GetOneAmindefault($this->admin_arr['pre_table'], $this->key_admin, 'public_pay_config');
        
        if(!empty($data))
        {
            $function_name = json_decode($data['function_name'], true);
            $res['publicismacc'] = $function_name['publicismacc'] ? $function_name['publicismacc'] : 0;
        }
        else
        {
            $res['publicismacc'] = 0;
        }
        
        $msg = array('code' => 200, 'data' => $res);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 建筑物列表
     * @return mixed
     * http://localhost/member/index.php/Integral/Palace/builidlist?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function builidlist(){
        $buildid_db = M('total_buildid');
    
        $arr['adminid'] = $this->admin_arr['id'];
        $buildid_arr = $buildid_db->where($arr)->select();
    
        $msg = array('code' => 200, 'data' => $buildid_arr);
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 　物业公告列表
     */
    public function property_list(){
        $params['lines']=I('lines')?I('lines'):20;
        $params['page']=I('page')?I('page'):1;
    
        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
    
        $arr=$property_DB->getList('',$params['lines'],$params['page']);
    
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
    
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 　物业公告详情
     */
    public function property_once(){
        $params['id']=I('id');
    
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
    
        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
    
        $arr=$property_DB->getOnce($params['id']);
    
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
         
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
}
