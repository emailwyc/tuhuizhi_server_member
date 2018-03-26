<?php
/**
 * 昆泰项目(b端)
 */
namespace MerAdmin\Controller;
use common\ServiceLocator;
use MerAdmin\Model\ComplaintModel;
use MerAdmin\Model\PropertyModel;
use MerAdmin\Model\HydropowerModel;
use MerAdmin\Model\AroundModel;
use MerAdmin\Model\SpotsModel;
use MerAdmin\Model\ActivityModel;
use Think\Model;


class KuntaiController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    
    public function _initialize(){
        parent::_initialize();
        $this->key_admin = $this->ukey;
        $this->table_action();
        
        $this->admin_arr=$this->getMerchant($this->ukey);
//         print_r($this->admin_arr);die;
        $this->property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业

        $this->hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
    }
    
    /**
     *  获取投诉建议列表
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/complaint_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function complaint_list(){
        $type   = I('type');//1投诉，2建议
        $buildNum = I('buildNum');//楼号
        $unit = I('unit');//单元号
        $houseNum = I('houseNum');//门牌号
        $status = I('status');//状态0未处理，1已处理
        $page = I('page') ? I('page') : 1;
        $offset = 10;
        $start = ($page - 1) * $offset;
        
        $complaintService = ServiceLocator::getComplaintService();
        $arr = $complaintService->getAll($this->admin_arr['id'], $type, $buildNum, $unit, $houseNum, $status, $start, $offset);
        $count = $complaintService->getCount($this->admin_arr['id'], $type, $buildNum, $unit, $houseNum, $status);
        
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
     *  查看单个投诉建议
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/complaint_once?key_admin=202cb962ac59075b964b07152d234b70&id=2
     */
    public function complaint_once(){
        $id   = I('id');
        
        if(empty($id))
        {
            $msg = array('code'=>1030);
            echo returnjson($msg, $this->returnstyle,$this->callback);exit();
        }
        
        $complaintService = ServiceLocator::getComplaintService();
        $arr = $complaintService->getOnce($id);
        
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
     *  处理投诉建议
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/operation?key_admin=202cb962ac59075b964b07152d234b70&id=2
     */
    public function operation(){
        $id   = I('id');
        $reply = I('reply');
        
        if(empty($id) || empty($reply))
        {
            $msg = array('code'=>1030);
            echo returnjson($msg, $this->returnstyle,$this->callback);exit();
        }
    
        $complaintService = ServiceLocator::getComplaintService();
        $arr = $complaintService->getOnce($id);
    
        if(empty($arr))
        {
            $msg = array('code'=>102);
        }
        else
        {
            $arr['reply'] = $reply;
            $arr['status'] = ComplaintModel::STATUS_1;
            $arr['utime'] = time();
            $arr = $complaintService->updateById($id, $arr);
            
            $msg = array('code'=>200,'data'=>$arr);
        }
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }

    /**
     *  获取访客列表信息
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/visitor_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function visitor_list(){
        $buildNum = I('buildNum');//楼号
        $unit = I('unit');//单元号
        $houseNum = I('houseNum');//门牌号
        $status = I('status');//状态0等待来访，1已登记，2已过期
        $date = I('date');
        $page = I('page') ? I('page') : 1;
        $offset = 10;
        $start = ($page - 1) * $offset;

        $visitorService = ServiceLocator::getVisitorService();
        $arr = $visitorService->getAll($this->admin_arr['id'], $buildNum, $unit, $houseNum, $status, $date, $start, $offset);
        $count = $visitorService->getCount($this->admin_arr['id'], $buildNum, $unit, $houseNum, $status, $date);

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
     *  查看单个访客信息
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/visitor_once?key_admin=202cb962ac59075b964b07152d234b70&id=2
     */
    public function visitor_once(){
        $id   = I('id');
    
        if(empty($id))
        {
            $msg = array('code'=>1030);
            echo returnjson($msg, $this->returnstyle,$this->callback);exit();
        }
    
        $visitorService = ServiceLocator::getVisitorService();
        $arr = $visitorService->getOnce($id);
    
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
     * 客服信息列表
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/customer_list?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function customer_list(){
        $customerService = ServiceLocator::getCustomerService();
        $arr = $customerService->getAll($this->admin_arr['id']);
    
        $key = 'show_customer_'.$this->admin_arr['id'];
        
        $res = $this->redis->get($key);
        
        if(empty($arr))
        {
            $msg = array('code'=>102, 'customer_ishide' => $res);
        }
        else
        {
            $msg = array('code'=>200,'data'=> $arr, 'customer_ishide' => $res);
        }
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 客服信息显示开关
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/show_customer?key_admin=202cb962ac59075b964b07152d234b70
     */
    public function show_customer(){
        $key = 'show_customer_'.$this->admin_arr['id'];
        
        $res = $this->redis->get($key);
        
        if($res != 1)
        {
            $value = 1;//隐藏
        }
        else
        {
            $value = 0;//显示
        }
        
        $this->redis->set($key, $value);
        
        $msg = array('code'=>200,'data'=> $value);
        
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    /**
     * 添加客服信息
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/add_customer?key_admin=202cb962ac59075b964b07152d234b70&name=a&phone=111111
     */
    public function add_customer(){
        $name  = I('name');
        $phone = I('phone');
    
        if(empty($name) || empty($phone))
        {
            $msg = array('code'=> 1030);
            echo returnjson($msg, $this->returnstyle,$this->callback);exit();
        }
        
        $customerService = ServiceLocator::getCustomerService();
        $arr = $customerService->add(array('name' => $name, 'adminid' => $this->admin_arr['id'],'phone' => $phone));

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
     * 删除客服信息
     * @return
     * http://localhost/member/index.php/MerAdmin/Kuntai/del_customer?key_admin=202cb962ac59075b964b07152d234b70&id=1
     */
    public function del_customer(){
        $id  = I('id');
    
        if(empty($id))
        {
            $msg = array('code'=> 1030);
            echo returnjson($msg, $this->returnstyle,$this->callback);exit();
        }
    
        $customerService = ServiceLocator::getCustomerService();
        $res = $customerService->delById($id);
    
        $msg = array('code'=>200,'data'=>$res);
    
        echo returnjson($msg, $this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 物业公告添加
     */
    public function property_notice(){
        $params['title'] = I('title');
        $params['content'] = I('content');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $dbm=M();
        
        $c=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'property_notice"');
        
        if(!$c){
            
            $sql="CREATE TABLE `".$this->admin_arr['pre_table']."property_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '状态',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $res=$dbm->execute($sql);
        }
        
        
//         $sort=$this->property_DB->getSortOnce('sort');
        
//         $params['sort']=$sort?$sort+1:1;

//         print_r($params);
        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
        
        $params['datetime']=date('Y-m-d H:i:s');
        
        $res=$property_DB->adds($params);
        
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 　获取物业公告列表
     */
    public function property_notice_list(){
        
        $params['name']=I('name');
        $params['lines']=I('lines')?I('lines'):10;
        $params['page']=I('page')?I('page'):1;

        $dbm=M();
        
        $c=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'property_notice"');
        
        if(!$c){
            $msg['code']=102;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
        $arr=$property_DB->getList($params['name'],$params['lines'],$params['page']);
        
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
       
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 　获取物业公告详情
     */
    public function property_notice_once(){
        
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
     * 　修改物业公告
     */
    public function property_notice_save(){
        $params['id']               =  I('id');
        $params['title']            =  I('title');
        $params['content']      =  I('content');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $data['title']=$params['title'];
        $data['content']=$params['content'];
        
        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
        $res=$property_DB->getOnceSave($params['id'],$data);
        
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        
    }
    
    
    /**
     * 　删除物业公告
     */
    public function property_notice_del(){
        
        $params['id']=I('id');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }

        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
        $res=$property_DB->getOnceDel($params['id']);
        
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 　置顶物业公告
     */
    public function property_notice_top() {
        
        $params['id']=I('id');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $sort = 2;//排序最高
        
        $property_DB = new PropertyModel('',$this->admin_arr['pre_table']);//物业
        $res=$property_DB->getOnceTop($params['id'],$sort);
        
        if($res){
            $msg['code']=200;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        
    }
    
    
    /**
     * 　水电剩余（所有房间）
     */
    public function  hydropower_list(){
        $params['floor']=I('floor');
        $params['unit']=I('unit');
        $params['door']=I('door');
        
        $params['lines']=I('lines')?I('lines'):10;
        $params['page']=I('page')?I('page'):1;
        
        $dbm=M();
        
        $c=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'hydropower"');
        
        if(!$c){
            $msg['code']=102;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
        $arr=$hydropower_DB->getHydropowerListFloor($params['floor'],$params['unit'],$params['door'],$params['lines'],$params['page']);
        
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     *　水电历史记录详情（每个房间）
     */
    public function hydropower_once(){
        $params['floor']=I('floor');
        $params['unit']=I('unit');
        $params['door']=I('door');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $dbm=M();
        
        $c=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'hydropower"');
        
        if(!$c){
            $msg['code']=102;
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }
        
        $hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
        
        $params['lines']=I('lines')?I('lines'):100;
        $params['page']=I('page')?I('page'):1;
        
        $arr = $hydropower_DB->getHydropowerDoorOnce($params['floor'],$params['unit'],$params['door'],$params['lines'],$params['page']);
        
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     *　水电添加与更新
     */
    public function hydropower_save(){
        //楼层房间号
        $params['floor']=I('floor');
        $params['unit']=I('unit');
        $params['door']=I('door');
        $params['floor_name']=I('floor_name');
        $params['door_name']=I('door_name');
        //水电数
        $params['water']=I('water');
        $params['electric']=I('electric');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $dbm=M();
        
        $c=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'hydropower"');
        
        if(!$c){
            
            $sql="CREATE TABLE `".$this->admin_arr['pre_table']."hydropower` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor` varchar(30) NOT NULL COMMENT '楼号',
  `unit` varchar(30) NOT NULL COMMENT '单元号',
  `door` varchar(30) NOT NULL COMMENT '门牌号',
  `water` varchar(30) DEFAULT NULL COMMENT '剩余水',
  `electric` varchar(30) DEFAULT NULL COMMENT '剩余电',
  `floor_name` varchar(60) DEFAULT NULL COMMENT '楼名称',
  `uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日期',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '状态:1正常，２删除',
  `is_category` tinyint(10) NOT NULL DEFAULT '1' COMMENT '是否首页显示：１是，２否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $res=$dbm->execute($sql);
        }
        
        $params['uptime']=date('Y-m-d H:i:s');
        $params['status']=1;
        $params['is_category']=1;
        
        $hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
        $arr = $hydropower_DB->setHydropowerDoorSave($params['floor'],$params['unit'],$params['door'],$params);
        
        if($arr){
            $msg['code']=200;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     *　水电记录删除
     */
    public function hydropower_del(){
        $params['floor']=I('floor');
        $params['unit']=I('unit');
        $params['door']=I('door');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
        
        $arr = $hydropower_DB->setHydropowerDel($params['floor'],$params['unit'],$params['door']);
        
        if($arr){
            $msg['code']=200;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 　水电最后更新日期
     */
    public function hydropower_details(){
        
        $params['floor']=I('floor');
        $params['unit']=I('unit');
        $params['door']=I('door');
        
        if(in_array('', $params)){
            echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $hydropower_DB = new HydropowerModel('',$this->admin_arr['pre_table']);//水电
        
        $arr = $hydropower_DB->getHydropowerOnce($params['floor'],$params['unit'],$params['door']);
        
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 周边配置 分类列表
     */
    public function AroundTypeList(){
        $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类

        $data = $around_DB->getDataList(1,'sort asc');
        
        if($data){
            $msg['code']=200;
            $msg['data']=$data;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     * 周边配置 分类添加
     */
    public function AroundTypeAdd(){
        
        $params['name']=I('name');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类
        
        $count = $around_DB->getDataCount(1);
        
        if($count >= 4){
            returnjson(array('code'=>1009),$this->returnstyle,$this->callback);exit();
        }
        
        $where['name']=$params['name'];
        
        $once_data = $around_DB->getDataOnce($where);
        
        if($once_data){
            returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();
        }
        
        $params['status'] = 1;
        $params['sort'] = $count+1;
        
        $res = $around_DB->getDataInster($params);
        
        if(!$res){
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
        }
        
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
    }
    
    /**
     *  周边配置 分类删除
     */
    public function AroundTypeDel(){
        
        $params['id'] = I('id');
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        
        $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类
        
        $once_data = $around_DB->getDataOnce($params);
        
        if(!$once_data){
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
        }
        
        $res = $around_DB->getDataOnceDel($params);
        
        $around_DB->getSortAction($once_data['sort']); // 删除后所有排序减一
        
        if(!$res){
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
        }
        
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
    }
    
    
    /**
     *  周边配置 上移下移
     */
    public function AroundTypeSortAction(){
        
        $params['id'] = I('id');
        $sort = I('sort')?I('sort'):'up';
        if(in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
        }
        $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类
        
        $once_data_up = $around_DB->getDataOnce($params);
        
        if($sort == 'up'){
            if($once_data_up['sort'] == 1){
                returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
            }
            
            $map['sort'] = $once_data_up['sort']-1;

        }else{

            $map['sort'] = $once_data_up['sort']+1;   
            
        }
        
        $once_data_down = $around_DB->getDataOnce($map);
        
        if($sort == 'up'){
            
            $data = $around_DB->setSort($params['id'] , $once_data_down['id']);
            
        }else{
            
            $data = $around_DB->setSort($once_data_down['id'] , $params['id']);
            
        }
        
        if($data['up'] && $data['down']){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
    }
    
    /**
     *  周边配置 分类置顶
     */
     public function AroundTypeTop(){
            
         $params['id'] = I('id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类
         
         $once_data = $around_DB->getDataOnce($params);
         
         if($once_data['sort'] == 1){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
         
         $res = $around_DB->getSortActionTop($once_data['sort']); // 删除后所有排序加一
         
         $data['sort']=1;
         $around_DB->setDataSave($params['id'],$data);
         
         if(!$res){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
         
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     /**
      *  周边配置 分类修改
      */
     public function AroundTypeSave(){
         
         $params['id'] = I('id');
         $params['name']=I('name');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         $around_DB = new AroundModel('',$this->admin_arr['pre_table']);//周边分类
         
         $data['name']=$params['name'];
         
         $res = $around_DB->setDataSave($params['id'] , $data);
         
         if($res === false){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
          
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     
     /**
      *  周边配置 景点列表
      */
     public function SpotsList(){
         
         $params['around_id'] = I('around_id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         $page = I('page')?I('page'):1;
         $lines = I('lines')?I('lines'):10;
         $name = I('name')?I('name'):'';
         $sort = I('sort')?I('sort'):'default';
         
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
         
         $count = $spots_DB->getSpotsDataCount($params,$name);
         
         if(!$count){
             returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
         }
         
         $num = ceil($count/$lines);
         $page = $page>$num?$num:$page;
         $start_lines = ($page-1)*$lines;
         
         $orders = $sort == 'up'?'distance asc':'distance desc';
         $order = $sort == 'default'?'sort asc':$orders;
         
         $spots_data = $spots_DB->getSpotsData($params,$name,$order,$start_lines,$lines);
 
         if(!$spots_data){
             returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
         }
         
         $data['data']=$spots_data;
         $data['num']=$num;
         $data['count']=$count;
         $data['page']=$page;
         returnjson(array('code'=>200,'data'=>$data),$this->returnstyle,$this->callback);exit();
     }
     
     /**
      *  周围配置 景点添加
      */
     public function SpotsDataAdd(){
         
         $params['sitename'] = I('name');
         $params['address'] = I('address');
         $params['distance'] = I('distance');
         $params['remarks'] = I('remarks');
         $params['imageurl'] = I('imageurl');
         $params['around_id'] = I('around_id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         
         $params = $this->paramsreplace($params);
         
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
         
         $count = $spots_DB->getSpotsDataCount(array('around_id'=>$params['around_id']));
         
         $params['sort'] = $count+1;
         $params['starttime'] = date('Y-m-d H:i:s');
         
         $res = $spots_DB->getSpotsDataAdd($params);
         
         if(!$res){
             returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
         }
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     /**
      *  周围配置 景点删除
      */
     public function SpotsDataDel(){
         
         $params['around_id'] = I('around_id');
         $params['id'] = I('id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
         
         $arr = $spots_DB->getSpotsDataOnce(array('id'=>$params['id']));
         
         if(!$arr || $arr['around_id'] != $params['around_id']){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
         
         $res = $spots_DB->getSpotsDataDel($params['id']);
         
         $spots_DB->getSpotsSortAction($arr['sort'],$params['around_id']);
     
         if(!$res){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
         
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     
     /**
      *  周围配置 单条景点
      */
     public function SpotsDataOnce(){
         
         $params['around_id'] = I('around_id');
         $params['id'] = I('id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
          
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
          
         $arr = $spots_DB->getSpotsDataOnce(array('id'=>$params['id']));
         
         if(!$arr){
             returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
         }
          
         returnjson(array('code'=>200,'data'=>$arr),$this->returnstyle,$this->callback);exit();
     }
     
     /**
      *  周围配置 景点修改
      */
     public function SpotsDataSave(){
         
         $params['id'] = I('id');
         $params['sitename'] = I('name');
         $params['address'] = I('address');
         $params['distance'] = I('distance');
         $params['remarks'] = I('remarks');
         $params['imageurl'] = I('imageurl');
         $params['around_id'] = I('around_id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
          
         $params = $this->paramsreplace($params);
         
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
     
         $data=$params;
         unset($data['id']);
         $res = $spots_DB->setSpotsDataOnceSave($params['id'],$data);
     
         if($res === false){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
     
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     /**
      *  周边配置 景点上移下移
      */
     public function SpotsSortAction(){
         
         $params['around_id'] = I('around_id');
         $params['id'] = I('id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         $status = I('status')?I('status'):'up';
         
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
          
         $data = $spots_DB->getSpotsDataOnce(array('id'=>$params['id']));
         
         if(!$data || $data['around_id'] != $params['around_id']){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
         
         $where['around_id'] = $params['around_id'];
         
         if($status == 'up'){
             
             $where['sort'] = $data['sort']-1;
             
         }else{
             
             $where['sort'] = $data['sort']+1;
             
         }
         
         $sort_data = $spots_DB->getSpotsDataOnce($where);
     
         if($status == 'up'){
             
             $res = $spots_DB->setSpotsSort($params['id'],$sort_data['id']);
             
         }else{
             
             $res = $spots_DB->setSpotsSort($sort_data['id'],$params['id']);
             
         }
         
         if($res['up'] && $res['down']){
             $msg['code']=200;
         }else{
             $msg['code']=104;
         }
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     /**
      *  周边配置 景点置顶
      */
     public function SpotsSortTop(){
         
         $params['around_id'] = I('around_id');
         $params['id'] = I('id');
         if(in_array('', $params)){
             returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
         }
         
         $spots_DB = new SpotsModel('',$this->admin_arr['pre_table']);//周边景点
         
         $data = $spots_DB->getSpotsDataOnce(array('id'=>$params['id']));
         
         if($data['around_id'] != $params['around_id']){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
         
         $spots_DB->getSpotsSortActionTop($data['sort'],$params['around_id']);
         
         $res = $spots_DB->setSpotsDataOnceSave($params['id'],array('sort'=>1));
         
         if(!$res){
             returnjson(array('code'=>104),$this->returnstyle,$this->callback);exit();
         }
          
         returnjson(array('code'=>200),$this->returnstyle,$this->callback);exit();
     }
     
     //替换参数
     public function paramsreplace($params){
         
         $params['sitename'] = html_entity_decode($params['sitename']);
         $params['address'] = html_entity_decode($params['address']);
         $params['remarks'] = html_entity_decode($params['remarks']);
         $params['imageurl'] = html_entity_decode($params['imageurl']);
         return $params;
     }
     
     public function table_action(){
         
         $dbm=M();
         
         $c=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'hydropower"');
         
         if(!$c){
         
             $sql="CREATE TABLE `".$this->admin_arr['pre_table']."hydropower` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor` varchar(30) NOT NULL COMMENT '楼号',
  `unit` varchar(30) NOT NULL COMMENT '单元号',
  `door` varchar(30) NOT NULL COMMENT '门牌号',
  `water` varchar(30) DEFAULT NULL COMMENT '剩余水',
  `electric` varchar(30) DEFAULT NULL COMMENT '剩余电',
  `floor_name` varchar(60) DEFAULT NULL COMMENT '楼名称',
  `uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日期',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '状态:1正常，２删除',
  `is_category` tinyint(10) NOT NULL DEFAULT '1' COMMENT '是否首页显示：１是，２否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         
             $res=$dbm->execute($sql);
         }
         
         $c2=$dbm->execute('SHOW TABLES like "'.$this->admin_arr['pre_table'].'hydropower"');
         
         if(!$c2){
         
             $sql2="CREATE TABLE `".$this->admin_arr['pre_table']."property_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `status` tinyint(10) NOT NULL DEFAULT '1' COMMENT '状态',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         
             $res=$dbm->execute($sql2);
         }
     }
     
}

?>

