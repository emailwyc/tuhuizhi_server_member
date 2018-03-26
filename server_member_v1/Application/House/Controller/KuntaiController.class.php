<?php
/**
 * 昆泰（c端）
 * User: wutong
 * Date: 17-7-29
 * Time: 下午16:29
 */
namespace House\Controller;
use Common\Controller\CommonController;
use common\ServiceLocator;

use MerAdmin\Model\ComplaintModel;
use MerAdmin\Model\VisitorModel;
use MerAdmin\Model\VisitorLogModel;
use MerAdmin\Model\PropertyModel;
use MerAdmin\Model\HydropowerModel;
use Common\Service\UploadService;
use MerAdmin\Model\AroundModel;
use MerAdmin\Model\SpotsModel;


class KuntaiController extends AuthController {

    /**
     *  提交建议反馈
     * @return
     * http://localhost/member/index.php/House/Kuntai/add_complaint?key_admin=202cb962ac59075b964b07152d234b70&name=name&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc&type=0&desc=desc&images=a.jpg,b.png&buildNum=1&unit=2&houseNum=3
     */
    public function add_complaint(){
        $openid = I('openid');
        $type   = I('type');//分类，0投诉，1建议
        $desc   = I('desc');//建议描述
        $images = I('images');
        $buildNum = I('buildNum');//楼号
        $unit = I('unit');//单元号
        $houseNum = I('houseNum');//门牌号
        
        if(empty($type) || empty($openid) || empty($desc) || empty($buildNum) || empty($unit) || empty($houseNum))
        {
            $msg = array('code'=>1030);
        }
        
        $imagesName = '';
        $upload = new UploadService();
        if(!empty($images))
        {
            $nameArr = explode(',', $images);
            
            foreach ($nameArr as $K => $v)
            {
                $return = $upload->FetchWeChatQiniu($this->admin_arr['wechat_appid'] , $v);
                $arr[] = "https://img.rtmap.com/".$return['data'];
            }
            
            $imagesName = implode(',', $arr);
        }
        
        $inster = array('name' => $this->userInfo['name'], 'adminid' => $this->admin_arr['id'], 'userid' => $this->userInfo['id'], 'openid' => $openid, 'type' => $type, 'desc' => $desc, 'images' => $imagesName, 'buildNum' => $buildNum, 'unit' => $unit, 'houseNum' => $houseNum, 'status' => ComplaintModel::STATUS_0, 'ctime' => time());
        $complaintService = ServiceLocator::getComplaintService();
        $res = $complaintService->add($inster);
    
        if($res !== false)
        {
            $msg['code']=200;
        }
        else
        {
            $msg['code']=104;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 我的反馈
     * @return
     * http://localhost/member/index.php/House/Kuntai/my_complaint?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc
     */
    public function my_complaint(){
        $openid = I('openid');
        
        $complaintService = ServiceLocator::getComplaintService();
        $arr = $complaintService->getByUserid($this->userInfo['id']);
    
        if(empty($arr))
        {
            $msg = array('code'=>102);
        }
        else
        {
            $msg = array('code'=>200,'data'=> $arr);
        }
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     *  提交访客通行证
     * @return
     * http://localhost/member/index.php/House/Kuntai/add_visitor?key_admin=202cb962ac59075b964b07152d234b70&name=name&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc&mobile=1371&desc=desc&date=2017-07-29&buildNum=1&unit=2&houseNum=3&master=zhangsan
     */
    public function add_visitor(){
        $name   = I('name');//访客
        $openid = I('openid');
        $mobile  = I('mobile');//电话
        $desc   = I('desc');//事由
        $date   = I('date');
        $master = I('master');//拜访人
        $buildNum = I('buildNum');//楼号
        $unit = I('unit');//单元号
        $houseNum = I('houseNum');//门牌号

        if(empty($name) || empty($openid) || empty($mobile) || empty($desc) || empty($date) || empty($master) || empty($buildNum) || empty($unit) || empty($houseNum))
        {
            $msg = array('code'=>1030);
        }
    
        $inster = array('name' => $name, 'adminid' => $this->admin_arr['id'], 'userid' => $this->userInfo['id'], 'openid' => $openid, 'mobile' => $mobile, 'desc' => $desc, 'date' => $date, 'master' => $master, 'buildNum' => $buildNum, 'unit' => $unit, 'houseNum' => $houseNum, 'ctime' => time());
        $visitorService = ServiceLocator::getVisitorService();
        $res = $visitorService->add($inster);
        
        $lastdate = date('Y-m-d 23:59:59', strtotime($date));
        
        if($res !== false)
        {
            $msg['code'] =200;
            $msg['id'] = $res;
            $msg['lastdate'] = $lastdate;
        }
        else
        {
            $msg['code']=104;
        }
    
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     * 查看访客通行证历史记录
     * @return
     * http://localhost/member/index.php/House/Kuntai/my_visitor?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc
     */
    public function my_visitor(){
        $page = I('page') ? I('page') : 1;
        $offset = 10;
        $start = ($page - 1) * $offset;
        
        $visitorLogService = ServiceLocator::getVisitorLogService();
        $arr = $visitorLogService->getAll($this->admin_arr['id'], $start, $offset);
        $count = $visitorLogService->getCount();
        
        if(empty($arr))
        {
            $msg = array('code'=>102);
        }
        else
        {
            $allpage = ceil($count/$offset);
            $msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        }
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查看所有访客通行证
     * @return
     * http://localhost/member/index.php/House/Kuntai/visitor_list?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc
     */
    public function visitor_list(){
        $visitorService = ServiceLocator::getVisitorService();
        $arr = $visitorService->getAll($this->admin_arr['id']);

        if(empty($arr))
        {
            $msg = array('code'=>102);
        }
        else
        {
            $msg = array('code'=>200,'data'=> $arr);
        }
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 查看单个访客记录
     * @return
     * http://localhost/member/index.php/House/Kuntai/visitor_once?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc&id=1
     */
    public function visitor_once(){
        $id = I('id');
    
        if(empty($id))
        {
            $msg = array('code'=>1030);
            echo returnjson($msg, $this->returnstyle,$this->callback);exit();
        }
    
        $visitorLogService = ServiceLocator::getVisitorLogService();
        $visitorService = ServiceLocator::getVisitorService();
        $res = $visitorService->getOnce($id);
        $status = VisitorLogModel::STATUS_0;
        
        if(empty($res))
        {
            $msg = array('code'=>1505);
        }
        else
        {
            $lastDate = date('Y-m-d 23:59:59', strtotime($res['date']));
            
            if(time() >  strtotime($lastDate))
            {
                $msg = array('code'=>1506);//已过期
            }
            else
            {
                //更新访客记录的认证状态
                $arr['status'] = VisitorModel::STATUS_1;
                $upres = $visitorService->updateById($id, $arr);
                $status = VisitorLogModel::STATUS_1;
                
                $msg = array('code'=>200,'data'=> $res);
            }
            
            $info = $visitorLogService->getByVisitorid($id, $status);
            if(empty($info))
            {
                //插入认证记录
                $visitorLogService->add(array('name' => $res['name'], 'visitorid' => $id, 'adminid' => $this->admin_arr['id'], 'userid' => $this->userInfo['id'], 'openid' => $res['openid'],'status' => $status, 'ctime' => time()));
            }
        }
        
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();

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
    
    /**
     * 　水电剩余
     */
    public function hydropower_once(){
        $this->emptyCheck($this->params, array('build_id','floor','poi_no'));
        $hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
        
        $data=$hydropower_DB->getHydropowerOnce($this->params['build_id'],$this->params['floor'],$this->params['poi_no']);
    
        $data['name']=$this->userInfo['name'];//业主姓名
        $msg['code']=200;
        $msg['data']=$data;
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 客服信息列表
     * @return
     * http://localhost/member/index.php/House/Kuntai/customer_list?key_admin=202cb962ac59075b964b07152d234b70&openid=oWm-rt8foeoMJqAjbZORswWDT-Sc
     */
    public function customer_list(){
        $customerService = ServiceLocator::getCustomerService();
        
        $key = 'show_customer_'.$this->admin_arr['id'];
        $res = $this->redis->get($key);
        
        if($res == 0)//显示
        {
            $arr = $customerService->getAll($this->admin_arr['id']);
        }
        else
        {
            $arr = array();//隐藏
        }
        
        if(empty($arr))
        {
            $msg = array('code'=>102);
        }
        else
        {
            $msg = array('code'=>200,'data'=>$arr);
        }
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     *  周边配置分类列表
     */
    public function CAroundTypeList(){
        $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类
        
        $data = $around_DB->getDataList(1,'sort asc');
        
        if(!$data){
            $data = array();
        }
        returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     *  周边配置景点列表
     */
    public function CSpotsList(){
        $params['around_id'] = I('around_id');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        $page = I('page')?I('page'):1;
        $lines = I('lines')?I('lines'):10;
         
        $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
         
        $count = $spots_DB->getSpotsDataCount($params);
         
        if(!$count){
            returnjson(array('code'=>200,'data'=>array('data'=>array())),$this->returnstyle,$this->callback);exit();
        }
         
        $num = ceil($count/$lines);
        $page = $page>$num?$num:$page;
        $start_lines = ($page-1)*$lines;
         
        $spots_data = $spots_DB->getSpotsData($params,'','sort asc',$start_lines,$lines);
        
        if(!$spots_data){
            returnjson(array('code'=>200,'data'=>array('data'=>array())),$this->returnstyle,$this->callback);exit();
        }
         
        $data['data']=$spots_data;
        $data['num']=$num;
        $data['count']=$count;
        $data['page']=$page;
        returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);exit();
    }
    
}
?>
