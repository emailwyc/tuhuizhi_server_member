<?php
/**
 * 商户优惠券B端
 * User: jaleel
 * Date: 2017/4/20
 * Time: 下午2:07
 */

namespace Coupon\Controller;
use Common\Controller\JaleelController;
use PublicApi\Controller\QiniuController;
class BackEndController extends JaleelController
{
    protected $lbs_key = 'vUbk87ZHpF';
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
     * 小程序title
     */
    public function applet_title(){
        $params['function_name']=I('content');
        if(in_array('', $params)){
            $msg['code']=1030;
            returnjson($msg,$this->returnstyle,$this->callback);
        }
        
        $db=M('default',$this->merchant['pre_table']);
        $where['customer_name']=array('eq','applet_title');
        $arr=$db->where($where)->find();
        if($arr){
            $res=$db->where($where)->save($params);
        }else{
            $params['customer_name']='applet_title';
            $params['description']='小程序title';
            $res=$db->add($params);    
        }
        $this->redis->del('admin:default:one:applet_title:'.$this->ukey);//删除redis缓存
        
        if($res !== false){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 支付明细
     */
    public function pingxx_list(){
        $params['shopname']=I('shopname');
        $params['starttime']=I('starttime');
        $params['endtime']=I('endtime');
        $params['export']=I('export');
        $params['orderno']=I('orderno');
        $params['lines']=I('lines')?I('lines'):10;
        $params['page']=I('page')?I('page'):1;
        $pingxx_db=M('pingxx_pay',$this->merchant['pre_table']);
        
        if($params['starttime']){
            $where['datetime']=array('between',array($params['starttime'],$params['endtime']));
        }
        if($params['shopname']){
            $where['shopname']=array('like',array('%'.$params['shopname'].'%'));
        }
        if($params['orderno'] != ''){
            $where['orderno']=array('like',array('%'.$params['orderno'].'%'));
        }
        if(count($where)>1){
            $where['_logic']='and';
        }else if(empty($where)){
            $where='1=1';
        }
        if($params['export']){
            $pingxx_db=M('pingxx_pay',$this->merchant['pre_table']);
            $arr=$pingxx_db->where($where)->select();
            
            if($arr){
                $str[]="商场名称,商户名称,订单号,优惠券号,应付金额,实付金额,支付时间,券面额,状态";
                foreach($arr as $k=>$v){
                    $status=$v['status']==1?'已支付':'未支付';
                    $str[]=$v['marketname'].",".$v['shopname'].",".$v['orderno'].",".$v['couponqr'].",".($v['mount'] / 100).",".($v['amount'] / 100).",".$v['datetime'].",".$v['couponprice'].",".$status;
                }
                $return=CreateCsvFile($str,RUNTIME_PATH.'wechat/fans/','csv');
                if($return){
                    $time = date("Ymd");
                    $uniqid = uniqid();
                    $key = 'fans_'.$time.'_'.$uniqid.'.csv';
                    $qiniu=new QiniuController;
                    list($ret, $err)=$qiniu->uploadfile($return,$key);
                    unlink($return);
                    if ($err !== null) {
                        $msg['code']=104;
                    }else{
                        $msg['code']=200;
                        $msg['data']=array('url'=>"https://img.rtmap.com/".$key);
                    }
                }else{
                    $msg['code']=$return['code'];
                }
                
            }else{
                $msg['code']=102;
            }
            
        }else{
            
            $count=$pingxx_db->where($where)->count();
            if($count){
                $page_num=ceil($count/$params['lines']);
                $page=$params['page']>$page_num?$page_num:$params['page'];
                $pian=($page-1)*$params['lines'];
            
                $data=$pingxx_db->where($where)->limit($pian,$params['lines'])->order('id desc')->select();
                if ($data){
                    foreach ($data as $key => $value) {
                        $data[$key]['mount'] = $value['mount'] / 100;
                        $data[$key]['amount']= $value['amount'] / 100;
                    }
                    $msg['code']=200;
                    $msg['data']=array(
                        'page'=>$page,
                        'page_num'=>$page_num,
                        'data'=>$data
                    );
                }else{
                    $msg['code'] = 102;
                }
            

            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    } 
    
    /**
     * 建筑物列表
     */
    public function buildList()
    {
        $childid = I("childid");
        $where = array('adminid' => $this->merchant['id'], 'is_del' => 2);
        if(!empty($childid)){
            $childMerInfo = $this->getChildMerchant($childid);
            $where['id'] = $childMerInfo['buildid'];
        }
        $result = $this->obj->where($where)->select();

        if (is_array($result)) {
            foreach ($result as $k=>$v) {
                if (empty($v['city'])) {
                    $details = $this->buildDetails($v['buildid']);

                    if (is_array($details)) {
                        $data['name'] = $result[$k]['name'] = $details['name_chn'];
                        $data['buildid'] = $result[$k]['buildid'] = $details['buildid'];
                        $data['long'] = $result[$k]['long'] = $details['long'];
                        $data['lat'] = $result[$k]['lat'] = $details['lat'];
                        $data['country'] = $result[$k]['country'] = $details['country'];
                        $data['province'] = $result[$k]['province'] = $details['province'];
                        $data['city'] = $result[$k]['city'] = $details['city'];
                        $data['district'] = $result[$k]['district'] = $details['district'];
                        $data['url'] = $result[$k]['url'] = $details['url'];
                        $this->updateBuildInfo($data);
                    }
                }
            }
        } else {
            $result = array();
        }

        $data = array('code' => 200, 'msg' => 'success', 'data' => $result);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 编辑建筑物
     */
    public function editBuild()
    {
        $id = I('id');
        $data['customerbid'] = I('cus_id');
        $data['url'] = I('url');
        $data['buildimg'] = I('img');
        $data['introduction'] = I('introduction');
        $data['is_promote']=I('promote');
        $data['short_name']=I('short_name');
        
        $re = $this->obj->where(array('id' => $id))->save($data);

        if ($re === false) {
            $data = array('code' => 1011, 'msg' => 'failed');
        } else {
            $data = array('code' => 200, 'msg' => 'success');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询建筑物详情
     */
    public function getBuild()
    {
        $id = I('id');
        $re = $this->obj->where(array('id' => $id))->find();

        if ($re) {
            $data = array('code' => 200, 'msg' => 'success', 'data' => $re);
        } else {
            $data = array('code' => 1011, 'msg' => 'failed');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 删除建筑物(非真正删除)
     */
    public function delBuild()
    {
        $id = I('id');
        $result = $this->obj->where(array('id' => $id))->save(array('is_del' => 1));

        if ($result === false) {
            $data = array('code' => 1011, 'msg' => 'delete build failed');
        } else {
            $data = array('code' => 200, 'msg' => 'success', 'data' => $result);
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 请求LBS接口来获得建筑物详细信息
     * @param $build_id
     * @return mixed
     */
    protected function buildDetails($build_id)
    {
        $data['key'] = $this->lbs_key;
        $data['buildid'] = $build_id;

        $url = 'http://lbsapi.rtmap.com/rtmap_lbs_api/v1/rtmap/build_detail';

        $string = json_encode($data);
        $re = $this->curl_json($url, $string);
        $re_arr = json_decode($re, true);
        return $re_arr['build_detail'];
    }

    /**
     * 将LBS拉取过来的建筑物信息更新到本地表中
     * @param array $data
     */
    protected function updateBuildInfo(array $data) {
        $this->obj->where(array('buildid' => $data['buildid'], 'adminid' => $this->merchant['id']))->save($data);
    }

    /**
     * 活动列表
     */
    public function activitiesList()
    {
        $typeid = I('type_id'); // 优惠券为1

        $childid = I("childid");
        if(!empty($childid)){
            $childMerInfo = $this->getChildMerchant($childid);
            $buildInfo = $this->obj->where(array('id'=>(int)@$childMerInfo['buildid']))->find();
        }
        $where = array('adminid' => $this->merchant['id'], 'typeid' => $typeid);
        if(!empty($buildInfo)){
            $where['buildid'] = $buildInfo['buildid'];
        }

        $re = $this->act_obj->where($where)->select();

        if (is_array($re) && count($re) > 0) {
            if(!empty($childMerInfo['buildid'])) {
                $b_info = $this->buildNames($re,$childMerInfo['buildid']);
            }else{
                $b_info = $this->buildNames($re);
            }
        } else {
            $b_info = array();
        }

        $data = array('code' => 200, 'msg' => 'success', 'data' => $b_info);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询建筑物名称
     * @param array $builds
     * @return array
     */
    protected function buildNames(array $builds)
    {
        foreach ($builds as $v) {
            $bids[] = $v['buildid'];
        }
        $where = array('adminid' => $this->merchant['id'], 'buildid' => array('in', $bids));
        $re = $this->obj->where($where)->select();
        if (is_array($re)) {
            foreach ($re as $v) {

                foreach ($builds as $k => $val) {
                    if ($val['buildid'] == $v['buildid']) {
                        $builds[$k]['name'] = $v['name'];
                    }
                }
            }
        }

        return $builds;
    }

    /**
     * 添加活动
     */
    public function addActivity()
    {
        $data['buildid'] = I('build_id');
        $data['activeid'] = I('act_id');
        $data['adminid'] = $this->merchant['id'];
        $data['typeid'] = I('type_id');

        $re = $this->act_obj->add($data);

        if ($re) {
            $data = array('code' => 200, 'msg' => 'success');
        } else {
            $data = array('code' => 1011, 'msg' => 'add active failed!');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 编辑活动
     */
    public function editActivity()
    {
        $id = I('id');
        $data['buildid'] = I('build_id');
        $data['activeid'] = I('act_id');
        $data['adminid'] = $this->merchant['id'];

        $re = $this->act_obj->where(array('id' => $id))->save($data);

        if ($re !== false) {
            $data = array('code' => 200, 'msg' => 'success');
        } else {
            $data = array('code' => 1011, 'msg' => 'add active failed!');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 删除活动
     */
    public function delActivity()
    {
        $id = I('id');
        $re = $this->act_obj->where(array('id' => $id))->delete();

        if ($re) {
            $data = array('code' => 200, 'msg' => 'success');
        } else {
            $data = array('code' => 1011, 'msg' => 'add active failed!');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * curl请求
     * POST数据为JSON数据
     * @param $url
     * @param $data_string
     * @return mixed
     */
    protected function curl_json($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $curl_re = curl_exec($ch);
        curl_close($ch);

        return $curl_re;
    }


    /**
     * B端设置轮播图片
     */
    public function createMiniProgramBanner()
    {
        $params['imgurl'] = I('imgurl');
        $params['key_admin'] = I('key_admin');
        $params['jumpurl']=I('jumpurl');
        $params['buildid']=I('buildid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['sort'] = I('sort') ? I('sort') : 1;
        $db = M('couponbanner', $this->merchant['pre_table']);
        $add = $db->add($params);
        if ($add) {
            returnjson(array('code'=>200),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>104),$this->returnstyle,$this->callback);
        }
    }


    /**
     * 小程序banner列表
     */
    public function miniProgramBannerList()
    {
        $params['key_admin'] = I('key_admin');
        $childid = I("childid");
        if(!empty($childid)){
            $childMerInfo = $this->getChildMerchant($childid);
            $buildInfo = $this->obj->where(array('id'=>(int)@$childMerInfo['buildid']))->find();
        }
        $admininfo= $this->getMerchant($params['key_admin']);
        $this->checkMiniProgramBannerTable($admininfo);
        $db = M('couponbanner', $admininfo['pre_table']);
        if($buildInfo['buildid']) {
            $sel = $db->where(array('buildid'=>$buildInfo['buildid']))->order('sort asc')->select();
        }else{
            $sel = $db->order('sort asc')->select();
        }
        if ( $sel ) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else {
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }


    /**
     *
     * 判断banner列表是否存在
     * @param $admininfo
     * @return bool
     */
    private function checkMiniProgramBannerTable($admininfo)
    {
        $db = M();
        $c=$db->execute('SHOW TABLES like "'.$admininfo['pre_table'].'couponbanner"');
        if (1 === $c){
            $create = 0;
        }else{
            $sql = "CREATE TABLE `".$admininfo['pre_table']."couponbanner` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `imgurl` varchar(255) NOT NULL COMMENT '图片在七牛上的地址',
  `sort` smallint(10) NOT NULL DEFAULT '1' COMMENT '排序',
  `jumpurl` varchar(50) NOT NULL DEFAULT '' COMMENT '图片跳转url',
  `buildid` varchar(20) NOT NULL DEFAULT '' COMMENT 'buildid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
            $create=$db->execute($sql);
        }
        return true;
    }


    /**
     * 小程序banner删除
     */
    public function miniProgramDelBanner()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        if ( in_array('', $params) ) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }else{
            $admininfo = $this->getMerchant($params['key_admin']);
            $db = M('couponbanner', $admininfo['pre_table']);
            $del = $db->where(array('id'=>$params['id']))->delete();
            if ($del) {
                returnjson(array('code'=>200),$this->returnstyle,$this->callback);
            }else {
                returnjson(array('code'=>104),$this->returnstyle,$this->callback);
            }
        }
    }
}