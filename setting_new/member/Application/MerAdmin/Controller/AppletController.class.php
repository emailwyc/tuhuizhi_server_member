<?php
/**
 * 小程序B端(营销平台4.0)
 * User: wutong
 * Date: 2017/8/8
 * Time: 上午10:00
 */
namespace MerAdmin\Controller;
use Common\Controller\CommonController;

use common\ServiceLocator;
use MerAdmin\Model\AppletShopModel;
use MerAdmin\Model\AppletCouponModel;

class AppletController extends CommonController
{
    public $key_admin;
    public $admin_arr;
    public $url = 'http://211.157.182.226:8080';
    
    public function _initialize(){
        parent::__initialize();
        
        $this->params = I('param.');
        $this->key_admin = I('key_admin');
        $this->admin_arr = $this->getMerchant($this->key_admin);
        
        if(C('DOMAIN') == 'http://mem.rtmap.com' || C('DOMAIN') == 'https://mem.rtmap.com')//正式环境
        {
            $this->url = 'http://47.94.115.24';
        }
    }

    /**
     * 添加或者编辑小程序配置信息
     * localhost/member/index.php/MerAdmin/Applet/updateConfig?key_admin=202cb962ac59075b964b07152d234b70&title=1&images=2
     */
    public function updateConfig(){
        $params = $this->params;
        $this->emptyCheck($params,array('title','images'));
        
        $appletConfigService = ServiceLocator::getAppletConfigService();
        $data = $appletConfigService->getOnce($this->admin_arr['id']); 
        
        $images = html_entity_decode($params['images']);
        $imageArr = json_decode($images, true);
        
        // 取得列的列表
        foreach ($imageArr as $key => $row) {
            $imageSort[$key]  = $row['sort'];
        }

        array_multisort($imageSort, SORT_DESC, $imageArr);
        
        $images = json_encode($imageArr, true);
        
        if(empty($data)){
            //添加
            $insert = array('adminid' => $this->admin_arr['id'], 'title' => $params['title'] , 'images' => $images);
            $lastid = $appletConfigService->add($insert);
        }
        else
        {
            //编辑
            $upDate = array('title' => $params['title'] , 'images' => $images);
            $appletConfigService->updateById($data['id'], $upDate);
            
            $lastid = $params['class_id'];
        }

        $msg['code'] = 200;
        $msg['data'] = $lastid;

        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 获取小程序配置信息
     * localhost/member/index.php/MerAdmin/Applet/getConfig?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function getConfig(){
        $appletConfigService = ServiceLocator::getAppletConfigService();
        $data = $appletConfigService->getOnce($this->admin_arr['id']);
    
        if(empty($data))
        {
            $msg['code'] = 1035;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit;
        }
        else
        {
            $msg = array('code'=>200,'data'=> $data);
        }

        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

     /**
     * 店铺列表
     * @param $marketId   商场id
     * @param $range      0全部，1已关联，2未关联
     * @param $industryId 业态id
     * @param $shopName   商户名称
     * @param $page       页数
     * localhost/member/index.php/MerAdmin/Applet/shopList?key_admin=202cb962ac59075b964b07152d234b70&marketId=290
     */
     public function shopList(){
         $params = $this->params;
         $this->emptyCheck($params,array('marketId'));
         
         $url = $this->url."/rtmap-coupon-web/api/basic/shop/list";//商场下的商户列表接口
         $arr = json_decode(http($url,array('marketId' => $params['marketId'], 'industryId' => $params['industryId'], 'shopName' => $params['shopName'])),true);
         
         $page = $params['page'] ? $params['page'] : 1;
         $offset = 10;
         $start = ($page - 1) * $offset;
         
         $newarr = array();
         if(!empty($arr['data']))
         {
             $appletShopService = ServiceLocator::getAppletShopService();
             if($params['range'] == 0)//全部
             {
                 foreach($arr['data'] as $k => $v)
                 {
                     $res = $appletShopService->getOnce($v['shopId']);
                     $newarr['data'][$k] = $this->dataOperation($v, $res);
                     $newarr['data'][$k]['relation'] = !empty($res) ? 1 : 0;
                 }
             }
             elseif($params['range'] == 1)//已关联
             {
                 foreach($arr['data'] as $k => $v)
                 {
                     $res = $appletShopService->getOnce($v['shopId']);
                     if($v['isRelation'] == 1)//关联状态：0：未关联，1：关联
                     {
                         $newarr['data'][$k] = $this->dataOperation($v, $res);
                         $newarr['data'][$k]['relation'] = 1;
                     }
                 }
             }
             elseif($params['range'] == 2)//未关联
             {
                 foreach($arr['data'] as $k => $v)
                 {
                     $res = $appletShopService->getOnce($v['shopId']);
                     if($v['isRelation'] == 0)
                     {
                         $newarr['data'][$k] = $this->dataOperation($v, $res);
                         $newarr['data'][$k]['relation'] = 0;
                     }
                 }
             }
         }
         
         $count = count($newarr['data']);
         $allpage = ceil($count/$offset);//总页数
         $newarr = array_slice($newarr['data'], $start, $offset);
         
         $msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$newarr ? $newarr : array()));
         
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 店铺数据处理
      */
     public function dataOperation($arr, $res)
     {
         $arr['consumption'] = !empty($res) ? $res['consumption'] : '';//人均消费，单位元
         $arr['desc'] = !empty($res) ? $res['desc'] : '';//活动说明
         $arr['sort'] = !empty($res) ? $res['sort'] : '';//排序
         $arr['discount'] = !empty($res) ? $res['discount'] : '';//0不优惠买单，1优惠买单
         $arr['status'] = !empty($res) ? $res['status'] : '';//0上架，1下架
         $arr['imgLogoUrl'] = !empty($res['imglogourl']) ? $res['imglogourl'] : $arr['imgLogoUrl'];//图片
         $arr['imgRealUrl'] = !empty($res['imgRealUrl']) ? $res['imgRealUrl'] : $arr['imgRealUrl'];//实景图
         
         return $arr;
     }
     
     /**
      * 编辑店铺信息
      * @param $shopId       店铺id
      * @param $consumption  人均消费
      * @param $desc         活动说明
      * @param $sort         店铺排序
      * @param $imgLogoUrl   店铺实景图片
      * @param $discount     是否优惠买单
      * localhost/member/index.php/MerAdmin/Applet/updateShopInfo?key_admin=202cb962ac59075b964b07152d234b70&shopId=70596&consumption=1&desc=desc&discount=0
      */
     public function updateShopInfo(){
         $params = $this->params;
         $this->emptyCheck($params,array('shopId','consumption','desc','discount'));
         
         $url = $this->url."/rtmap-coupon-web/api/basic/shop/detail";//店铺信息
         $info = json_decode(http($url,array('shopId' => $params['shopId'])),true);
         
         if($info['status'] != 200 || empty($info['data']))//找不到匹配结果
         {
             $msg['code'] = 1035;
             echo returnjson($msg,$this->returnstyle,$this->callback);exit;
         }
         
         $appletShopService = ServiceLocator::getAppletShopService();
         $arr = $appletShopService->getOnce($params['shopId']);
         
         if(!empty($arr))
         {
             $arr['consumption'] = $params['consumption'];
             $arr['desc'] = $params['desc'];
             $arr['discount'] = $params['discount'];
             $arr['sort'] = !empty($params['sort']) ? $params['sort'] : 1;
             
             if(!empty($params['imgLogoUrl']))
             {
                 $arr['imgLogoUrl'] = $params['imgLogoUrl'];
             }
             
             $res = $appletShopService->updateById($arr['id'], $arr);
         }
         else
         {
             $imgLogoUrl = !empty($info['data']['imgLogoUrl']) ? $info['data']['imgLogoUrl'] : '';
             $imgRealUrl = !empty($info['data']['imgRealUrl']) ? $info['data']['imgRealUrl'] : '';
             
             $inster = array(
                 'adminid' => $this->admin_arr['id'],
                 'shopId' => $params['shopId'],//店铺id
                 'shopName' => $info['data']['shopName'],//店铺名
                 'address' => !empty($info['data']['address']) ? $info['data']['address'] : '',//地址
                 'marketId' => $info['data']['marketId'],//商场ID
                 'brandId' => !empty($info['data']['brandId']) ? $info['data']['brandId'] : 0,//品牌ID
                 'brandName' => !empty($info['data']['brandName']) ? $info['data']['brandName'] : '',//品牌名称
                 'industryId' => !empty($info['data']['industryId']) ? $info['data']['industryId'] : 0,//业态ID
                 'industryName' => !empty($info['data']['industryName']) ? $info['data']['industryName'] : '',//业态名
                 'buildId' => !empty($info['data']['buildId']) ? $info['data']['buildId'] : '',//建筑物ID
                 'floor' => !empty($info['data']['floor']) ? $info['data']['floor'] : '',//楼层
                 'imgLogoUrl' =>  $imgLogoUrl,//logo
                 'imgRealUrl' =>  !empty($params['imgLogoUrl']) ? $params['imgLogoUrl'] : $imgRealUrl,//实景图
                 'consumption' => $params['consumption'],//人均消费
                 'tel' => !empty($info['data']['tel']) ? $info['data']['tel'] : '',//电话
                 'desc' => $params['desc'],//活动说明
                 'sort' => $params['sort'] ? $params['sort'] : 1,//店铺排序
                 'discount' => $params['discount'],//是否优惠买单
                 'status' => AppletShopModel::STATUS_0,
                 'ctime' => time()
             );
             
             $res = $appletShopService->add($inster);
         }
         
         $msg = array('code'=>200,'data'=> $res);
          
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 业态列表接口
      * http://localhost/member/index.php/MerAdmin/Applet/industryList?key_admin=202cb962ac59075b964b07152d234b70
      */
     public function industryList(){
         $url = $this->url."/rtmap-coupon-web/api/basic/industry/list";
          
         $arr = json_decode(http($url,array()),true);
          
         if($arr['status'] == 200)
         {
             $msg = array('code'=>200,'data'=> $arr['data']);
         }
         else
         {
             $msg = array('code'=>200,'data'=> array());
         }
          
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
    
     /**
      * 商户上下架操作
      * @param $shopId  店铺id
      * @param $status  0上架，1下架
      * localhost/member/index.php/MerAdmin/Applet/updateStatus?key_admin=202cb962ac59075b964b07152d234b70&shopId=70596&status=0
      */
     public function updateStatus(){
         $params = $this->params;
         $this->emptyCheck($params,array('shopId'));

         $appletShopService = ServiceLocator::getAppletShopService();
         $arr = $appletShopService->getOnce($params['shopId']);
         
         if(empty($arr))
         {
             $msg['code'] = 1035;
             echo returnjson($msg,$this->returnstyle,$this->callback);exit;
         }

         $arr['status'] = $params['status'] ? $params['status'] : 0;
         $res = $appletShopService->updateById($arr['id'], $arr);

         $msg = array('code'=>200,'data'=> $res);

         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 优惠券列表
      * @param $token      
      * @param $activityId 活动id
      * @param $status     券状态1 投放中(默认)  2 待审核
      * @param $couponName 券名称
      * @param $type       0折扣券，1礼品券，2代金券，3促销信息，4优惠券，6免费试吃券，7单品优惠，8品牌活动
      * @param $page       页数
      * @param $pageNum    每页显示数量
      * localhost/member/index.php/MerAdmin/Applet/couponList?key_admin=202cb962ac59075b964b07152d234b70&token=087a0ba671614bd8af25cd38e679ac57&activityId=A01YXD2rX  A01d1YWYc
      */
     public function couponList(){
         $params = $this->params;
         $url = $this->url."/rtmap-coupon-web/api/coupon/activity/pagelist";
         $arr = json_decode(http($url,array('token' => $params['token'], 'activityId' => $params['activityId'], 'couponCategoryId' => $params['type'], 'couponName' => $params['couponName'], 'status' => !empty($params['status']) ? $params['status'] : 1, 'page' => $params['page'])),true);
         
         $page = $params['page'] ? $params['page'] : 1;
         $offset = !empty($pageNum) ? $params['pageNum'] : 10;
         $start = ($page - 1) * $offset;

         $newArr = array();
         if(!empty($arr['data']['list']))
         {
             $appletCouponService = ServiceLocator::getAppletCouponService();
             foreach($arr['data']['list'] as $k => $v)
             {
                     $res = $appletCouponService->getOnce($v['couponId'], $v['couponActivityId']);
                     $arr['data']['list'][$k] = $v;
                     
                     $arr['data']['list'][$k]['sort'] = !empty($res) ? $res['sort'] : '';//排序
                     $arr['data']['list'][$k]['dayMax'] = !empty($res) ? $res['daymax'] : '';//每日领取上限
                     $arr['data']['list'][$k]['allMax'] = !empty($res) ? $res['allmax'] : '';//累积领取上限
                     $arr['data']['list'][$k]['online'] = !empty($res) ? $res['online'] : AppletCouponModel::ONLINE_2;//是否上线
             }
         }
         
         $msg = array('code'=>200,'data'=> $arr['data']);
          
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 编辑优惠券信息
      * @param $couponId
      * @param $couponActivityId   优惠券id
      * @param $sort       排序
      * @param $dayMax     每天领取上限
      * @param $allMax     累计领取上限
      * localhost/member/index.php/MerAdmin/Applet/updateCouponInfo?key_admin=202cb962ac59075b964b07152d234b70&couponActivityId=BEgTsmAA&couponId=1281&sort=0&dayMax=1&allMax=1
      */
     public function updateCouponInfo(){
         $params = $this->params;
         $this->emptyCheck($params,array('couponId', 'couponActivityId','dayMax','allMax'));

         $url = $this->url."/rtmap-coupon-web/api/common/coupon/detail";//优惠券详情   /rtmap-coupon-web/api/coupon/activity/detail
         $info = json_decode(http($url,array('id' => $params['couponId'], 'couponActivityId' => $params['couponActivityId'])),true);

         if($info['status'] != 200 || empty($info['data']))//找不到匹配结果
         {
             $msg['code'] = 1035;
             echo returnjson($msg,$this->returnstyle,$this->callback);exit;
         }
         
         //领取上限参数错误
         if($params['dayMax'] > $info['data']['quantity'] || $params['allMax'] > $info['data']['quantity'])
         {
             $msg['code'] = 1597;
             echo returnjson($msg,$this->returnstyle,$this->callback);exit;
         }
         
         $appletCouponService = ServiceLocator::getAppletCouponService();
         $arr = $appletCouponService->getOnce($params['couponId'], $params['couponActivityId']);
         
         if(!empty($arr))
         {
             $arr['sort'] = $params['sort'];
             $arr['dayMax'] = $params['dayMax'];
             $arr['allMax'] = $params['allMax'];
             $res = $appletCouponService->updateById($arr['id'], $arr);
         }
         else
         {
             $inster = array(
                 'adminid' => $this->admin_arr['id'],
                 'couponId' => $params['couponId'],//券id
                 'couponActivityId' => $info['data']['couponActivityId'],//券id
                 'activityId' => $info['data']['activityId'],//活动id
                 'mainInfo' => $info['data']['mainInfo'],//券标题
                 'validateStatus' => $info['data']['validateStatus'],//券状态
                 'statusDesc' => $info['data']['statusDesc'],//状态解释
                 'categoryId' => $info['data']['categoryId'],//券类型
                 'categoryDesc' => !empty($info['data']['categoryDesc']) ? $info['data']['categoryDesc'] : '',//券类型描述
                 'facePrice' => $info['data']['facePrice'],//券价值
                 'marketId' => $info['data']['marketId'],//商场ID
                 'shopId' => $info['data']['shopId'] ? $info['data']['shopId'] : 0,//商户ID
                 'issuerName' => $info['data']['issuerName'],//发券方
                 'tel' => $info['data']['tel'] ? $info['data']['tel'] : '',//发券主体联系方式
                 'quantity' => $info['data']['quantity'],//券批数量
                 'imgLogoUrl' => $info['data']['imgLogoUrl'],//logo
                 'sort' => $params['sort'] ? $params['sort'] : 1,//排序
                 'dayMax' => $params['dayMax'],//每日领取上限
                 'allMax' => $params['allMax'],//累计领取上限
                 'online' => AppletCouponModel::ONLINE_2,//上下线状态
                 'ctime' => time()
             );
             
             $res = $appletCouponService->add($inster);
         }
          
         $msg = array('code'=>200,'data'=> $res);
     
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 券类型下拉列表
      * http://localhost/member/index.php/MerAdmin/Applet/couponCategory?key_admin=202cb962ac59075b964b07152d234b70&token=24347d2c6810419ca214d1ad5133f016
      */
     public function couponCategory(){
         $params = $this->params;
         $this->emptyCheck($params,array('token'));

         $url = $this->url."/rtmap-coupon-web/dict/categoryAll";
         
         $arr = json_decode(http($url, array('token' => $params['token'])),true);
         
         if($arr['status'] == 200)
         {
             $msg = array('code'=>200,'data'=> $arr['data']);
         }
         else
         {
             $msg = array('code'=>200,'data'=> array());
         }
         
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 优惠券状态操作
      * @param $status            0启动,1暂停,2退还,3审核通过,4审核驳回
      * @param $activityId        活动id
      * @param $couponId
      * @param $couponActivityId  券id
      * http://localhost/member/index.php/MerAdmin/Applet/upCouponStatus?key_admin=202cb962ac59075b964b07152d234b70&token=94c56b1b69f24a1eb3a72469b34a6942&status=1&activityId=APjMoUa&couponActivityId=BJldceh9
      */
     public function upCouponStatus(){
         $params = $this->params;
         $this->emptyCheck($params,array('status'));
         
         if($params['status'] == 0)
         {
             $pathVariableName = 'restart';//启用
         }
         elseif($params['status'] == 1)
         {
             $pathVariableName = 'pause';//暂停
         }
         elseif($params['status'] == 2)
         {
             $pathVariableName = 'return';//退还
         }
         elseif($params['status'] == 3)
         {
             $pathVariableName = 'audit';//审核通过
         }
         elseif($params['status'] == 4)
         {
             $pathVariableName = 'reject';//审核驳回
         }
         
         $url = $this->url."/rtmap-coupon-web/api/coupon/activity/status/$pathVariableName?token=".$params['token'];
         
         $data = array('activityId' => $params['activityId'], 'couponActivityId' => $params['couponActivityId']);
         $postjson = json_encode($data);
         $header = array('Content-Type:application/json');
         
         $arr = json_decode(http($url, $postjson, 'POST', $header, true),true);
         
         if($arr['status'] == 200)
         {
             if($params['status'] == 2)//退还
             {
                 $appletCouponService = ServiceLocator::getAppletCouponService();
                 $arr = $appletCouponService->getOnce($params['couponId'], $params['couponActivityId']);
                 
                 if(!empty($arr))
                 {
                     $arr['online'] = AppletCouponModel::ONLINE_2;
                     $res = $appletCouponService->updateById($arr['id'], $arr);
                 }
             }
             
             $msg = array('code'=>200,'data'=> $arr['data']);
         }
         else
         {
             $msg = array('code'=> $arr['status'],'data'=> $arr['message']);
         }
          
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 上下线操作
      * @param $status          1上线,2下线
      * @param $couponActivityId
      * @param $couponId        券id
      * http://localhost/member/index.php/MerAdmin/Applet/upOnlinetatus?key_admin=202cb962ac59075b964b07152d234b70&&status=1&couponId=2309
      */
     public function upOnlinetatus(){
         $params = $this->params;
         $this->emptyCheck($params,array('status'));
         
         $appletCouponService = ServiceLocator::getAppletCouponService();
         $arr = $appletCouponService->getOnce($params['couponId'], $params['couponActivityId']);
         
         if(empty($arr))//找不到匹配结果
         {
             $msg['code'] = 1035;
             echo returnjson($msg,$this->returnstyle,$this->callback);exit;
         }
         
         $arr['online'] = $params['status'];
         $res = $appletCouponService->updateById($arr['id'], $arr);
         
         if($res == true)
         {
             $msg = array('code'=>200);
         }
         else
         {
             $msg = array('code'=>200);
         }
     
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 数据统计
      * http://localhost/member/index.php/MerAdmin/Applet/dataStatistics?key_admin=ab09925ed498728aa4c70fdfd98b4e26
      */
     public function dataStatistics(){
         $params = $this->params;
         
         $db = M('pingxx_pay', $this->admin_arr['pre_table']);
          
         //今日买单数
         $arr['datetime'] = array('EGT', date('Y-m-d 00:00:00', time()));
         $count = $db->where($arr)->count();
          
         //今日买单金额
         $sum = $db->where($arr)->sum('amount');
         
         //今日最多买单数店铺
         $countMaxShop = $db->field('*,count(*) as num')->where($arr)->group('shopid')->order('num DESC')->find();
         
         //今日最多买单金额店铺
         $amountShop = $db->field('*,sum(amount) as amount')->where($arr)->group('shopid')->order('amount DESC')->find();
         
         //今日发券数
         $appletCouponLogService = ServiceLocator::getAppletCouponLogService();
         $param['ctime'] = array('EGT', strtotime(date('Ymd')));
         $param['type'] = AppletShopModel::STATUS_0;
         $dayNum = $appletCouponLogService->getNum($param);
         
         //今日券核销数
         $writeoff['ctime'] = array('EGT', strtotime(date('Ymd')));
         $writeoff['type'] = AppletShopModel::STATUS_1;
         $writeoffNum = $appletCouponLogService->getNum($writeoff);
         
         //今日发放最多券
         $dayMax['ctime'] = array('EGT', strtotime(date('Ymd')));
         $dayMax['type'] = AppletShopModel::STATUS_0;
         $coupon_log = M('applet_coupon_log');
         $dayMaxName = $coupon_log->field('*,count(*) as num')->where($arr)->group('couponId')->order('num DESC')->find();//maininfo
         
         $data['count']        = $count ? $count : 0;//今日买单数
         $data['sum']          = $sum ? $sum : 0;//今日买单金额
         $data['countMaxShop'] = !empty($countMaxShop['shopname']) ? $countMaxShop['shopname'] : '';//今日最多买单数店铺
         $data['amountShop']   = !empty($amountShop['shopname']) ? $amountShop['shopname'] : '';//今日最多买单金额店铺
         $data['dayNum']       = $dayNum;//今日发券数
         $data['writeoffNum']  = $writeoffNum;//今日券核销数
         $data['dayMaxName'] = !empty($dayMaxName['maininfo']) ? $dayMaxName['maininfo'] : '';//今日发放最多券
         $data['dayMaxShop'] = !empty($dayMaxName['issuername']) ? $dayMaxName['issuername'] : '';//今日买单数
         
         $payList = array();
         
         //默认显示一个月
         if(empty($params['startdate']) || empty($params['enddate']))
         {
             $params['startdate'] = date('Y-m-d H:i:s', time() - 30 * 86400);
             $params['enddate']   = date('Y-m-d H:i:s', time());
         }
         
         //订单数据查询
         $page = $params['page'] ? $params['page'] : 1;
         $offset = 10;
         $start = ($page-1) * $offset;
          
         $arr['datetime'] = array(array('EGT', $params['startdate']), array('ELT', $params['enddate']), 'and');
         if($params['shopid'])
         {
             $arr['shopid'] = $params['shopid'];
         }
          
         $list = $db->where($arr)->limit($start,$offset)->select();
         
         $count = $db->where($arr)->count();
          
         $allpage = ceil($count/$offset);

         $payList = array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=> $list);

         $msg = array('code'=>200,'data'=> $data, 'payList' => $payList);
         
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
 
     /**
      * 优惠券领取数据统计
      * http://localhost/member/index.php/MerAdmin/Applet/couponDataStatistics?key_admin=ab09925ed498728aa4c70fdfd98b4e26
      */
     public function couponDataStatistics(){
         $params = $this->params;
         
         $list = array();
         
         //默认显示一个月
         if(empty($params['startdate']) || empty($params['enddate']))
         {
             $params['startdate'] = date('Y-m-d H:i:s', time() - 30 * 86400);
             $params['enddate']   = date('Y-m-d H:i:s', time());
         }
          
         //订单数据查询
         $page = $params['page'] ? $params['page'] : 1;
         $offset = 10;
         $start = ($page-1) * $offset;

         $db = M('applet_coupon_log');
         $arr['datetime'] = array(array('EGT', $params['startdate']), array('ELT', $params['enddate']), 'and');
         if($params['shopid'])
         {
             $arr['shopid'] = $params['shopid'];
         }
          
         $list = $db->where($arr)->limit($start,$offset)->select();
     
         $count = $db->where($arr)->count();
     
         $allpage = ceil($count/$offset);
         $payList = array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=> $list);
          
         $msg = array('code'=>200,'data' => $payList);
          
         returnjson($msg,$this->returnstyle,$this->callback);exit();
     }
     
     /**
      * 优惠买单excel导出
      * http://localhost/member/index.php/MerAdmin/Applet/getExcel?key_admin=ab09925ed498728aa4c70fdfd98b4e26
      */
     public function getExcel(){
         $excelService = ServiceLocator::getExcelService();
         
         set_time_limit(0);
         $excelService->exportHeader();
         $title = array("id","支付信息","总金额","实际金额","订单号","openid","支付方式","货币","订单状态","商户id","key_admin","建筑物id","优惠券编号","商场名称","商户名称","支付时间","券面额","昵称");
         $excelService->addArray($title);
     
         //默认显示一个月
         if(empty($params['startdate']) || empty($params['enddate']))
         {
             $params['startdate'] = date('Y-m-d H:i:s', time() - 30 * 86400);
             $params['enddate']   = date('Y-m-d H:i:s', time());
         }
         $arr['datetime'] = array(array('EGT', $params['startdate']), array('ELT', $params['enddate']), 'and');
         if($params['shopid'])
         {
             $arr['shopid'] = $params['shopid'];
         }
         
         $db = M('pingxx_pay', $this->admin_arr['pre_table']);
         $list = $db->where($arr)->select();
         
         $excelService->export_pingxx_pay($list);
     }
     
     /**
      * 优惠券领取excel导出
      * http://localhost/member/index.php/MerAdmin/Applet/getCouponExcel?key_admin=ab09925ed498728aa4c70fdfd98b4e26
      */
     public function getCouponExcel(){
         $excelService = ServiceLocator::getExcelService();
          
         set_time_limit(0);
         $excelService->exportHeader();
         $title = array("id","adminid","openid","券批ID","券id","活动id","券标题","商场ID","店铺ID","发券方","券码","状态","创建时间");
         $excelService->addArray($title);
          
         //默认显示一个月
         if(empty($params['startdate']) || empty($params['enddate']))
         {
             $params['startdate'] = time() - 30 * 86400;
             $params['enddate']   = time();
         }
         $arr['ctime'] = array(array('EGT', $params['startdate']), array('ELT', $params['enddate']), 'and');
         if($params['shopid'])
         {
             $arr['shopid'] = $params['shopid'];
         }
         
         $db = M('applet_coupon_log');
         $list = $db->where($arr)->select();
         
         $excelService->export_coupon_log($list);
     }
     
     protected function emptyCheck($params,$key_arr) {
         foreach($key_arr as $v){
             if(!isset($params[$v])){
                 $msg['code']=1051;
                 echo returnjson($msg,$this->returnstyle,$this->callback);exit;
             }
         }
     }

}

?>

