<?php
/**
 *活动券系统
 */
namespace MerAdmin\Controller;
use Commands\Controller\QueueController;

use common\ServiceLocator;

class CouponController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    
    public function _initialize(){
        parent::_initialize();
        $this->key_admin=$this->ukey;
    }

    //添加或者编辑活动券
    //localhost/member/index.php/MerAdmin/Coupon/editClassOne?buildId=4&buildName=ghi&activityId=5&type=1&openId=3,2,3,5&httpadd=&key_admin=202cb962ac59075b964b07152d234b70
    public function editClassOne(){
        $params = $this->params;
        $this->emptyCheck($params,array('buildId','buildName','activityId','type'));
        
        //过滤特殊符号
        $search = array(" ","　","\n","\r","\t",'，');
        $replace = array("","","","","","");
        $params['openId'] = str_replace($search, $replace, $params['openId']);
        
        $adminInfo = $this->getMerchant($this->key_admin);
        $couponService = ServiceLocator::getCouponService();
        
        if(empty($params['class_id'])){
            //添加
            $insert = array('buildId' => $params['buildId'], 'buildName' => $params['buildName'] , 'activityId' => $params['activityId'],'adminid' => $adminInfo['id'], 'httpadd' => $params['httpadd'], 'type' => $params['type'], 'openId' => $params['openId'], 'createtime' => date('Y-m-d H:i;s', time()));
            $lastid = $couponService->add($insert);
        }
        else
        {
            //编辑
            $upDate = array('buildId' => $params['buildId'], 'buildName' => $params['buildName'] , 'activityId' => $params['activityId'],'adminid' => $adminInfo['id'], 'httpadd' => $params['httpadd'], 'type' => $params['type'], 'openId' => $params['openId']);
            $couponService->updateById($params['class_id'], $upDate);
            
            $lastid = $params['class_id'];
        }

        $msg['code'] = 200;
        $msg['data'] = $lastid;

        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //删除活动券http://localhost/member/index.php/MerAdmin/Coupon/delTagsOne?id=20
    public function delTagsOne(){
        $params = $this->params;
        $this->emptyCheck($params,array('id'));
        
        if(!empty($params['id'])){
            //删除
            $couponService = ServiceLocator::getCouponService();
            $couponService->deleteById($params['id']);
        }
        $msg['code'] = 200;
        $msg['data'] = $params;

        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取单条活动券
    public function getOne(){
        $params = $this->params;
        $this->emptyCheck($params,array('id'));
        if(empty($params['id'])){
            $msg = array('code'=>1030);
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        $couponService = ServiceLocator::getCouponService();
        $arr = $couponService->getById($params['id']);
        
        if(empty($arr)){
            $msg = array('code'=>102);
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        //标签处理完毕返回数据
        if($arr){
            $msg = array('code'=>200,'data'=>$arr);
        }else{
            $msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取活动券列表
    //getCouponList?page=1&key_admin=202cb962ac59075b964b07152d234b70
    public function getCouponList(){
        $params = $this->params;
        $this->emptyCheck($params,array('page'));
        $page = ((int)$params['page'])<=0?1:((int)$params['page']);
        $offset = 99999999;
        $start = ($page-1)*$offset;
        $db = M('total_coupon');

        $adminInfo = $this->getMerchant($this->key_admin);

        $field = "*";
        $where = array('adminid' => $adminInfo['id']);
        
        $couponService = ServiceLocator::getCouponService();
        $arr = $couponService->getAll($field, $where);
        $arr = ArrKeyFromId($arr,'id');
        foreach($arr as $k=>$v){
            $arr[$k]['class_name'] = array();
        }
        
        $count = $couponService->getCount($where);
        $count = @(int)$count[0]['count'];

        //标签处理完毕返回数据
        $arr = ArrObjChangeList($arr);
        if($arr){
            $allpage = ceil($count/$offset);
            $msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        }else{
            $allpage = ceil($count/$offset);
            $msg = array('code'=>102,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //发送活动券 http://localhost/member/index.php/MerAdmin/Coupon/sendCoupon?id=9&key_admin=202cb962ac59075b964b07152d234b70
    public function sendCoupon(){
        set_time_limit(300);
        $params = $this->params;
    
        $this->emptyCheck($params,array('id'));
        if(empty($params['id'])){
            $msg = array('code'=>1030);
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        $couponService = ServiceLocator::getCouponService();
        $arr = $couponService->getById($params['id']);

        if(empty($arr)){
            $msg = array('code'=>102);
            returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

        $opids = array();//推送openid
        //推送全部会员
        if($arr['type'] == 0)
        {
            $memberService = ServiceLocator::getMemberService($this->admin_arr['pre_table']);
            $allMem = $memberService->getAllByOpenIdNotNull();
            
            if(!empty($allMem))
            {
                foreach ($allMem as $k => $v)
                {
                    $this->setqueuedata($v['openid'], $arr['activityid']);
                }
            }
        }
        elseif($arr['type'] == 1)//推送指定openid
        {
            //过滤中文逗号
            $arr['openid'] = str_replace('，',',',$arr['openid']);
            $opids = explode(',', $arr['openid']);
        }

        $trueNum = 0;
        $falseNum = 0;
        if(!empty($opids))
        {
            foreach ($opids as $k => $v)
            {
                if($v)
                {
                    //如果设置了自定义的发券地址
                    if(!empty($arr['httpadd']))
                    {
                        $res = http($arr['httpadd'].$v,array());
                        $resArr = json_decode($res, true);
                        if($resArr['code'] == 0)
                        {
                            $trueNum++;
                        }
                        else
                        {
                            $falseNum++;
                        }
                    }
                    else
                    {
                        $this->setqueuedata($v, $arr['activityid']);
                        //$res = http("http://182.92.31.114/rest/act/".$arr['activityid']."/$v",array());////101.201.176.54/rest/act/activity/openid
                    }
                }
            }
        }
    
        $msg = array('code'=>200,'data'=> array('trueNum' => $trueNum, 'falseNum' => $falseNum));
    
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    //数据入队列
    public function setqueuedata($openId, $activityId)
    {
        $redisData = array('openId' => $openId, 'activityId' => $activityId, 'type' => QueueController::TYPE_1);

        $res = $this->redis->lPush(C('REDIS_QUEUE_NAME'), json_encode($redisData, true));

        return true;

    }
    
}

?>

