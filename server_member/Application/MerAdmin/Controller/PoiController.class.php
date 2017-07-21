<?php
/**
 * 刷新poi点
 */
namespace MerAdmin\Controller;

class PoiController extends AuthController
{
    // TODO - Insert your code here
    private $errorcode;
    public function _initialize(){
        $this->errorcode=array('code'=>'','data'=>'','msg'=>'');
        parent::_initialize();
    }
    
    /**
     * 后台商户刷新poi点的操作
     */
    public function refresh() {
        $params['key_admin']=I('key_admin');
        $params['buildid']=I('buildid');
        $msg=$this->errorcode;
        if (in_array('',$params)){
            $msg['code']=100;
        }else{//如果数据符合
            $admininfo=$this->getMerchant($params['key_admin']);
            $builddb=M('buildid','total_');
            $find=$builddb->where(array('buildid'=>$params['buildid'],'adminid'=>$admininfo['id']))->find();
            if (null != $find){
                $poidata=$this->GetPoi($params['buildid']);//获取buildid的poi点数据
                if (is_json($poidata)){
                    $poidata=json_decode($poidata, true);
                    if (0==$poidata['result']['error_code']){
                        //最后一步，更新操作
                        $change=$this->UpdatePoi($admininfo['pre_table'], $params['buildid'], $poidata['poilist']);
                        if ($change==true){
                            $msg['code']=200;
                        }else{
                            $msg['code']=104;
                        }
                    }else{
                        $msg['code']=101;
                        $msg['data']=$poidata['result']['error_code'];
                    }
                }else {
                    $msg['code']=101;
                }
            }else{
                $msg['code']=102;
            }
            
            
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * @deprecated    获取当前建筑物id下面的poi点列表，楼层可选，如果选则楼层，则获取的是本层的poi点
     * @param unknown $buildid
     * @param string $floor
     */
    private function GetPoi($buildid,$floor=''){
        $url=C('POI_URL');
        $arr=''==$floor?array('key'=>C('POI_APPKEY'),'buildid'=>$buildid):array('key'=>C('POI_APPKEY'),'buildid'=>$buildid,'floor'=>$floor);
        $postjson=json_encode($arr);
        $header=array('Content-Type:application/json;charset=UTF-8');
        $result=http($url, $postjson,'POST',$header,true);
        return $result;
    }
    
    
    /**
     * 通过key_admin和buildid获取数据表数据，然后对比更新
     * @param string $table_pre
     * @param string $buildid
     * @param array $poi
     */
    private function UpdatePoi(string $table_pre, string $buildid, array $poi) {
        $db=M('map_poi_'.$buildid, $table_pre);
        $sel=$db->select();
        if (null == $sel){//如果数据表中本来没有，则执行批量添加操作
            $adddata=null;
            $add=null;
            foreach ($poi as $key => $val){
                if ('' != $val['name_chn']){
                    $adddata[]=array(
                        'id_build'=>$val['buildid'],
                        'floor'=>$val['floor'],
                        'poi_no'=>$val['poi_no'],
                        'poi_name'=>$val['name_chn']
                    );
                    //为防止数据量过大，mysql存储失败，设置一次最多保存10000条
                    if (10000 <= count($adddata) ){
                        $add[]=$db->addAll($adddata);
                        unset($adddata);
                    }
                }
            }
            //给最后不足10000条的数据执行一次addall
            $add[]=$db->addAll($adddata);
            if (in_array(false,$add)){
                return false;
            }else return true;
        }else{
            foreach ($poi as $key => $val){//获取到的poi点数据
                if ('' != $val['name_chn']){
                    dump($key);
                    dump($val);
                    $ishave=false;
                    foreach ($sel as $k => $v){//数据库中保存的数据
                        if ($val['name_chn'] == $v['poi_name']){//如果poi点名称不为空，
                            $adddata=array('id'=>$v['id'],'id_build'=>$val['buildid'],'floor'=>$val['floor'],'poi_no'=>$val['poi_no']);
                            $db->save($adddata);
                            $ishave=true;
                            continue;
                        }
                    }
                    if (false == $ishave){//如果之前没有，则是新增
                        $data=array('id_build'=>$val['buildid'],'floor'=>$val['floor'],'poi_no'=>$val['poi_no'],'poi_name'=>$val['name_chn']);
                        $db->add();
                    }
                }
            }
            return true;
        }
        
        
    }
}

?>