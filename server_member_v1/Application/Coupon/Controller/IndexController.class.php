<?php
/**
 * 商户优惠券C端
 * User: jaleel
 * Date: 2017/4/20
 * Time: 下午2:07
 */

namespace Coupon\Controller;
use Common\Controller\JaleelController;

class IndexController extends JaleelController {

    protected $obj;
    protected $merchant;
    protected $act_obj;

    public function _initialize()
    {
        parent::_initialize();

        $this->merchant = $this->getMerchant($this->ukey);
        $this->obj = M('total_buildid');

        $this->act_obj = M('total_activities');
    }

    /**
     * 获取小程序title
     */
    public function get_applet_title(){
        $find=$this->GetOneAmindefault($this->merchant['pre_table'], $this->ukey, 'applet_title');
        if($find){
            $msg['code']=200;
            $msg['data']=$find['function_name'];
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 获取城市下所有商场
     */
    public function get_city_market(){
        $params['city']=I('city');
        $params['long']=I('long');
        $params['lat']=I('lat');
        if(in_array('',$params)){
            $msg['code']=1030;
        }else{
            
            $params['name']=I('name');
            if($params['name'] != ''){
                $where['name']=array('like',array('%'.$params['name'].'%'));
            }
            
            $where['city']=array('eq',$params['city']);
            $where['is_del']=array('eq',2);
            $where['adminid']=array('eq',$this->merchant['id']);
            $where['_logic']='and';
            $market_data=$this->obj->where($where)->select();
            if(!empty($market_data)){
                $msg['code']=200;
                foreach($market_data as $k=>$v){
                    if($v['long']!='' && $v['lat']!=''){
                        $market_data[$k]['limit']=getDistance($params['lat'],$params['long'],$v['lat'],$v['long']);
                    }else{
                        $market_data[$k]['limit']=0;
                    }
                    $market_data[$k]['address']=$v['address']?$v['address']:'';
                    $market_data[$k]['district']=$v['district']?$v['district']:'';
                    $arr_sort[$k]=$market_data[$k]['limit'];
                }
                
                asort($arr_sort);
                
                foreach($arr_sort as $k=>$v){
                    $data[]=$market_data[$k];
                }
                
                $msg['data']=$data;
            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 获取推荐列表
     */
    public function show_promote_list(){
        $params['city']=I('city');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $where['city']=array('eq',$params['city']);
            $where['is_promote']=array('eq',1);
            $where['is_del']=array('eq',2);
            $where['adminid']=array('eq',$this->merchant['id']);
            $where['short_name']=array('neq','');
            $where['_logic']='and';
            $arr=$this->obj->where($where)->field('id,buildid,adminid,short_name')->select();
            if(count($arr) > 9 ){
                for($i=0;$i<9;$i++){
                    $data[]=$arr[$i];
                }
            }else{
                $data=$arr;
            }
            $msg['code']=200;
            $msg['data']=$data;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    
    /**
     *  获取当前支持的所有城市列表
     */
    public function get_city_list(){
        $where['city']=array('neq','');
        $where['adminid']=array('eq',$this->merchant['id']);
        $where['_logic']='and';
        $arr=$this->obj->where($where)->field('id,country,province,city')->select();
        
        foreach($arr as $k=>$v){
            $data[$v['id']]=$v['city'];
        }
        
        $re_data=array_unique($data);
        
        foreach($re_data as $k=>$v){
            foreach($arr as $key=>$val){
                if($k == $val['id']){
                    $datas[]=$val;
                }
            }
        }
        if($arr){
            $msg['code']=200;
            $msg['data']=$datas;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 获取场馆信息
     */
    public function index()
    {
        $city = I('city');
        $re = $this->buildingList($city);

        $data = array('code' => 200, 'msg' => 'success', 'data' => $re);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 场馆券列表
     */
    public function couponList()
    {
        $build_id = I('build_id');
        $search=I('search');
        
        $re = $this->act_obj->where(array('buildid' => $build_id, 'adminid' => $this->merchant['id']))->find();

        $id= $re['activeid'];

        if($search != ''){
            $params['mainInfo']=$search;
            $params['buildIf']=$build_id;
            $params['type']=0;
            $params['activityId']=$id;
            $url = 'http://101.201.175.219/promo/mini/apps/prize/list';//正式
            $return = http($url, json_encode($params), 'POST', array('Content-Type:application/json'), true);
            
            $return_arr=json_decode($return,true); 
            
            foreach($return_arr['data'] as $k=>$v){
                $data['buildId']=$v['build_id'];
                $data['main']=$v['main_info'];
                $data['extend']=$v['extend_info'];
                $data['startTime']=$v['start_time'];
                $data['endTime']=$v['end_time'];
                $data['position']='';
                $data['imgUrl']=$v['image_url'];
                $data['status']=$v['status'];
                $data['template']='';
                $data['pid']=$v['id'];
                $data['num']=$v['num'];
                $data['issue']=$v['issue'];
                $data['coupon']=$v['coupon_type'];
                $data['desc']=$v['desc_clause'];
                $data['price']=$v['price'];
                $return_data[]=$data;
            }
            $curl=json_encode($return_data);
        }else{
            $url = 'http://182.92.31.114/rest/act/levels/' . $id;
            $curl = http($url, array());
        }

        $arr = json_decode($curl, true);

        if (!is_array($arr)) {
            $data = array();
        }

        $db=M('property','coupon_');
        
        $cou_arr=$db->where(array('buildid' => $build_id, 'admin_id' => $this->merchant['id'],'is_status'=>1))->select();
//         print_r($cou_arr);
//         $i=9999999999;
        if($cou_arr && $arr){
            foreach($cou_arr as $k=>$v){
                foreach($arr as $key=>$val){
                    if($v['pid'] == $val['pid']){
                        $arr[$key]['sort']=$v['sort'];
                        $sort[$key]=$v['sort'];
                    }else{
                        $sort[$key]=10000000000000;
//                         $i++;
                    }  
                }
            }

            asort($sort);

            foreach($sort as $k=>$v){
                $data[]=$arr[$k];
            }
        }else{
            $data=$arr;
        }
        
        $data = array('code' => 200, 'msg' => 'success', 'data' => $data);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 券详情 按二维码查询
     */
    public function couponDetails()
    {
        $qr = I('qr');
        $url = 'http://182.92.31.114/rest/act/prize/' . $qr;
        $curl = http($url, array());
        $arr = json_decode($curl, true);
        $data = array('code' => 200, 'msg' => 'success', 'data' => $arr);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 券详情 按券id查询
     */
    public function couponDetailsById()
    {
        $id = I('prize_id');
        $url = 'http://182.92.31.114/rest/act/level/' . $id;
        $curl = http($url, array());
        if (is_json($curl)) {
            $arr = json_decode($curl, true);
            $data = array('code' => 200, 'msg' => 'success', 'data' => $arr);
            returnjson($data,$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>101),$this->returnstyle,$this->callback);//接口错误
        }

    }

    /**
     * 我的卡包
     * http://211.157.182.226:8090/pages/viewpage.action?pageId=6685622
     */
    public function myCoupons()
    {
        $status=I('status');

        $bagparams['floor'] = I('floor');
//        $bagparams['type'] = I('type');//拉取类型:0：活动下所有券 1:提供方为商场 2:提供方为商户 3:核销方为商户或全商户::::::::已确认不需要此参数
        $bagparams['buildId'] = I('buildid');
        $bagparams['poiNo'] = I('poi');
        $bagparams['openId'] = $this->userucid;
        if (!in_array('', $bagparams)) {
            $re = $this->act_obj->where(array('buildid' => $bagparams['buildId'], 'adminid' => $this->merchant['id']))->find();
            if ($re['activeid'] == false) {
                returnjson(array('code'=>307),$this->returnstyle,$this->callback);
            }
            $bagparams['activityId'] = $re['activeid'];
            $url = 'http://101.201.175.219/promo/mini/apps/prize/bag';
            $curl = http($url, json_encode($bagparams), 'POST', array('Content-Type:application/json'), true);
            $arr = json_decode($curl, true);
            $arr = $arr['data'];
        }else{
            $url = 'http://182.92.31.114/rest/act/myBag/' . $this->user_openid;
            $curl = http($url, array());
            $arr = json_decode($curl, true);
        }


        $erp_url=C('DOMAIN') . '/ErpService/Erpoutput/get_exchange_list';
        $return_data=array();
        $db = M();
        $c=$db->execute('SHOW TABLES like "'.$this->merchant['pre_table'].'mem"');
        if (1 === $c){
            $mem_db=M('mem',$this->merchant['pre_table']);
            $map['openid']=array('eq',$this->user_openid);
            $user_arr=$mem_db->where($map)->find();
            
            $params['key_admin']=$this->ukey;
            $params['cardno']=$user_arr['cardno'];
            $params['sign_key']=$this->merchant['signkey'];
            $params['sign']=sign($params);
            unset($params['sign_key']);
            $return_arr=json_decode(http($erp_url,$params),true);
            if($return_arr['code']==200){
                $return_data=$return_arr['data']['data'];   
            }
//             print_r($return_data);die;
        }           
        
        if(!empty($return_data)){
            if($arr){
                $arr=array_merge($arr,$return_data);
            }else{
                $arr=$return_data;
            }
        }
        
        if($status != null){
            if(!empty($arr)){
                foreach($arr as $k=>$v){
                    if($v['status'] == $status){
                        $data[]=$v;
                    }
                }
            }
        }else{
            $data=$arr;
        }
        
        $find=$this->GetOneAmindefault($this->merchant['pre_table'], $this->ukey, 'couponcolorset');
        $color = $find['function_name']?$find['function_name']:'';
        
        $msg = array('code' => 200, 'msg' => 'success', 'data' => $data,'sum'=>count($data), 'color'=>$color);
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    /**
     * 领取卡券
     * http://10.10.11.47:8090/pages/viewpage.action?pageId=1966106
     */
    public function drawCoupon()
    {
        $prizeId = I('prize_id');
        $url = 'http://101.201.176.54/rest/act/prize/' . $prizeId . '/' . $this->user_openid;
        $curl = http($url, array());
        $arr = json_decode($curl, true);

        if ($arr['code'] == 0) {
            $data = array('code' => 200, 'msg' => 'success', 'data' => $arr);
        } else {
            $data = array('code' => $arr['code'], 'msg' => $arr['message']);
        }
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 当前商户所有场馆列表
     * @return array
     */
    protected function buildingList($city)
    {
        $re = $this->obj->where(array('adminid' => $this->merchant['id'], 'is_del' => 2))->select();

        if (is_array($re)) {
            foreach ($re as $k => $v) {
                if (!empty($v['city'])) {
                    $data[$v['city']][] = $v;
                }
            }
        }

        if (count($data) > 0) {

            if (isset($data[$city])) {
                $need = $data[$city];
            } else {
                $need = $data[$re[0]['city']];
            }
        } else {
            $need = array();
        }

        //获取buildid的广告banner
        $builidlist = array_column($need, 'buildid');
        $db = M('couponbanner', $this->merchant['pre_table']);
        $sel = $db->where(array('buildid'=>array('in', $builidlist)))->order('sort asc')->select();
        if (false != $need){
            foreach ($need as $key => $value){
                foreach ($sel as $k => $v){
                    if ($value['buildid'] == $v['buildid']) {
                        $need[$key]['bannerimages'][]=$v;
                    }
                }
            }
        }
        return $need;
    }

    /**
     * 获取当前商户的所有商场城市列表
     */
    public function buildCity() {
        $re = $this->obj->where(array('adminid' => $this->merchant['id'], 'is_del' => 2))->select();

        if (is_array($re)) {
            foreach ($re as $v) {
                if (!empty($v['city'])) {
                    $data[] = iconv('utf-8', 'gbk', $v['city']);
                }
            }
        } else {
            $data = array();
        }

        asort($data);

        if (count($data) > 0) {
            foreach ($data as $val) {
                $need[] = iconv('gbk', 'utf-8', $val);
            }
        } else {
            $need = $data;
        }

        $need = array_unique($need);

        $data = array('code' => 200, 'msg' => 'success', 'data' => $need);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 获取地理位置信息
     */
    public function LocationInfo()
    {
        $lng = I('lng');
        $lat = I('lat');
        $url = 'http://api.map.baidu.com/geocoder/v2/';
        $params['location'] = "{$lat},{$lng}";
        $params['output'] = 'json';
        $params['pois'] = 0;
        $params['ak'] = '0zHhysoFEPMSK4Y0yCOm15nr';
        $curl = http($url, $params);
        $arr = json_decode($curl, true);

        if ($arr['status'] == 0) {
            $data['city'] = $arr['result']['addressComponent']['city'];
        } else {
            $data = array();
        }

        $data = array('code' => 200, 'msg' => 'success', 'data' => $data);
        returnjson($data,$this->returnstyle,$this->callback);
    }




    /**
     * 皇庭小程序接口，其他场馆也可用，注意，此部分用到了外包的数据库，用第三个配置：DB_CONFIG2（第二个配置为微信）
     */

    /**
     *按条件查询券结果:拉取类型:0：活动下所有券 1:提供方为商场 2:提供方为商户 3:核销方为商户或全商户
     * 1、查询全场
     * 2、查询商户
     */
    public function getShopCoupon()
    {
        $params['type'] = I('type');
        $params['key_admin'] = I('key_admin');
        $params['buildId'] = I('build_id');

        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['floor'] = I('floor');
        $params['poiNo'] = I('poi');

        $re = $this->act_obj->where(array('buildid' => $params['buildId'], 'adminid' => $this->merchant['id']))->find();
        if ($re['activeid'] == false) {
            returnjson(array('code'=>307),$this->returnstyle,$this->callback);
        }
        $params['activityId'] = $re['activeid'];
        unset($params['key_admin']);
//        $url = 'http://211.157.182.226:8080/promo/mini/apps/prize/list';//测试
        $url = 'http://101.201.175.219/promo/mini/apps/prize/list';//正式
        $return = http($url, json_encode($params), 'POST', array('Content-Type:application/json'), true);
        $return = json_decode($return, true);
        if ( $return && $return['status'] == 200 ) {
            $data['code'] = 200;
            $data['data'] = $return['data'];
        }else{
            $data['code'] = 104;
            $data['data'] = $return['status'];
        }
        returnjson($data,$this->returnstyle,$this->callback);
    }



    /**
     * 获取build对应的分类
     */
    public function getPoiClass()
    {
        $params['key_admin'] = I('key_admin');
        $params['buildid'] = I('buildid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $db = M( $admininfo['pre_table'].'map_poi_class_'.$params['key_admin'] , '', 'DB_CONFIG2');
        $sel = $db->select();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }


    /**
     * 获取楼层
     */
    public function getFloor()
    {
        $params['buildid'] = I('buildid');
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($this->ukey);
        $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
        $sel = $db->field('floor')->group('floor')->select();
        $arr = array_column($sel, 'floor');
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$arr),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }


    /**
     * 根据指定的分类，查询商户列表
     */
    public function getPoiClassCoupon()
    {
        $params['buildid'] = I('buildid');
        $params['key_admin'] = I('key_admin');
        $params['class'] = I('class');
        if (in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
        $sel = $db->field('id,poi_logo,poi_image,id_build,floor,class_id,poi_no,poi_name,monetary,path,poi_describe')->where(array('class_id'=>$params['class'], 'del_status'=>1))->order('sorting asc')->select();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }


    /**
     * 获取楼层商户列表
     */
    public function getFloorCoupon()
    {
        $params['buildid'] = I('buildid');
        $params['key_admin'] = I('key_admin');
        $params['floor'] = I('floor');
        if (in_array('', $params)){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
        $sel = $db->field('id,poi_logo,poi_image,id_build,floor,class_id,poi_no,poi_name,monetary,path,poi_describe')->where(array('floor'=>$params['floor'], 'del_status'=>1))->order('sorting asc')->select();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }


    /**
     * 小程序C端banner接口
     */
    public function getBannerList()
    {
        $key_admin = I('key_admin');
        $admininfo = $this->getMerchant($key_admin);

        $db = M('couponbanner', $admininfo['pre_table']);
        $sel = $db->field('id', true)->order('sort asc')->select();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }

    }


    /**
     * 按消费水平获取商户列表
     */
    public function getConsumptionLevel()
    {
        $params['key_admin'] = I('key_admin');
        $params['buildid'] = I('buildid');

        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['order'] = I('order');//asc 低到高，desc高到低,为空则按id
        $admininfo = $this->getMerchant($params['key_admin']);
        $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
        if ($params['order'] == 'asc' || $params['order'] == 'desc'){
            $order = 'monetary ' . $params['order'] . ', sorting asc ';
        }else {
            $order = 'sorting asc';
        }
        $sel = $db->where(array('del_status'=>1))->field('id,poi_logo,poi_image,id_build,floor,class_id,poi_no,poi_name,monetary,path,poi_describe')->order($order)->select();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }


    /**
     * 商户（POI）详情
     */
    public function getPoiDescription()
    {
        $params['key_admin'] = I('key_admin');
        $params['buildid'] = I('buildid');
        $params['id'] = I('id');

        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');

        $sel = $db->where(array('id'=>$params['id']))->find();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }

    }


    /**
     * 模糊搜索商铺（外包POI点表）
     */
    public function getFuzzySearch()
    {
        $params['key_admin'] = I('key_admin');
        $params['buildid'] = I('buildid');
        $params['str'] = I('str');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }

        $admininfo = $this->getMerchant($params['key_admin']);
        $db = M( $admininfo['pre_table'].'map_poi_'.$params['buildid'] , '', 'DB_CONFIG2');
        $like['poi_name'] = array('like', array('%'.$params['str'].'%'));
        $like['path'] = array('like', array('%'.$params['str'].'%'));
        $like['_logic'] = 'OR';
        $map['_complex']= $like;
        $map['del_status'] = 1;
        $sel = $db->where($map)->order('sorting asc')->select();
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }

    }
















}