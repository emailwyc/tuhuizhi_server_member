<?php
/**
 * 积分转赠B端配置
 */
namespace MerAdmin\Controller;

use Common\Controller\CommonController;
use PublicApi\Controller\QiniuController;
// use Common\Controller\CommonController;
class ScoreTransferController extends CommonController
{
    public function _initialize()
    {
        parent::__initialize();
    }
    public function setting()
    {
        $key_admin=$this->ukey;
        $data['urlexpirydate']=(int)I('urlexpirydate');//有效期
        $data['mixscore']=(int)I('mixscore');
        $data['maxscore']=(int)I('maxscore');
        $data['timeinterval']=(int)I('timeinterval');
        
        //判断传入的条件是否符合条件
        if ($data['urlexpirydate'] <1 || $data['urlexpirydate'] > 24 || $data['mixscore'] < 1 || $data['mixscore'] > 100000 || $data['maxscore'] < 1 || $data['maxscore'] > 100000 || $data['timeinterval'] < 1 || $data['timeinterval'] > 31){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }
        
        if (in_array('', $data)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        if ($data['mixscore'] > $data['maxscore']){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }
        
        $admininfo=$this->getMerchant($key_admin);
        $db=M('default',$admininfo['pre_table']);
        foreach ($data as $key => $val){
            if (null != $db->where(array('customer_name'=>$key))->find()){//之前有
                $result[] = $db->where(array('customer_name'=>$key))->save(array('function_name'=>$val));
                $this->redis->del('admin:default:one:'.$key.':'. $key_admin);
            }else {
                $result[] = $db->add(array('function_name'=>$val,'customer_name'=>$key));//新增
            }
        }
        if (in_array(false, $result, true)){
            $msg['code']=104;
        }else{
            $msg['code']=200;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 获取积分转赠历史配置
     */
    public function getsetting()
    {
        $data['key_admin']=I('key_admin');
        $admininfo=$this->getMerchant($data['key_admin']);
        
        $urlexpirydate=$this->GetOneAmindefault($admininfo['pre_table'], $data['key_admin'], 'urlexpirydate');
        $mixscore=$this->GetOneAmindefault($admininfo['pre_table'], $data['key_admin'], 'mixscore');
        $maxscore=$this->GetOneAmindefault($admininfo['pre_table'], $data['key_admin'], 'maxscore');
        $timeinterval=$this->GetOneAmindefault($admininfo['pre_table'], $data['key_admin'], 'timeinterval');
        
        $arr=array(
            'code'=>200,
            'data'=>array(
                'urlexpirydate'=>false==$urlexpirydate['function_name'] ? '' : $urlexpirydate['function_name'],
                'mixscore'=>false==$mixscore['function_name'] ? '' : $mixscore['function_name'],
                'maxscore'=>false==$maxscore['function_name'] ? '' : $maxscore['function_name'],
                'timeinterval'=>false==$timeinterval['function_name'] ? '' : $timeinterval['function_name'],
            )
        );
        
        returnjson($arr, $this->returnstyle, $this->callback);
    }
    
    
    /**
     * 积分转赠记录
     */
    public function scoretransferlist()
    {
        $key_admin=$this->ukey;
        $params['status']=(int)I('status');
        $params['startdate']=strtotime(I('startdate'));
        $params['enddate']=strtotime(I('enddate') . '+ 1 days');
        $params['sharer']=I('sharer');
        $params['receiver']=I('receiver');
        $params['scorenum']=I('scorenum');
        $page=I('page') ? I('page') : 1;//页数
        $rows=I('rows') ? I('rows') : 2;//行数
        $p= ($page - 1) * $rows;
        $export=I('export') ? I('export') : 1;//1查询，2导出
//         dump($params);
        if ($params['sharer'] !=='' && !is_numeric( $params['sharer'] )){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        
        
        $admininfo=$this->getMerchant($key_admin);
        $this->CheckTable($admininfo);
        
        $sellist=array(
            0=>array(//未领取
                'status'=>array('isreceive', '='),
                'startdate'=>array('sharetime', '>'),
                'enddate'=>array('sharetime', '<'),
                'sharer'=>array('sharermobile', '='),
                'receiver'=>array('receivermobile', '='),
                'scorenum'=>array('scorenumber', '>'),
            ),
            1=>array(//已领取
                'status'=>array('isreceive', '='),
                'startdate'=>array('receivetime', '>'),
                'enddate'=>array('receivetime', '<'),
                'sharer'=>array('sharermobile', '='),
                'receiver'=>array('receivermobile', '='),
                'scorenum'=>array('scorenumber', '>'),
            ),
            2=>array(//过期
                'status'=>array('isreceive', '='),
                'startdate'=>array('sharetime', '>'),
                'enddate'=>array('sharetime', '<'),
                'sharer'=>array('sharermobile', '='),
                'receiver'=>array('receivermobile', '='),
                'scorenum'=>array('scorenumber', '>'),
            ),
            3=>array(//全部
//                 'status'=>array('isreceive', '='),
                'startdate'=>array('sharetime', '>'),
                'enddate'=>array('sharetime', '<'),
                'sharer'=>array('sharermobile', '='),
                'receiver'=>array('receivermobile', '='),
                'scorenum'=>array('scorenumber', '>'),
            ),
        );
        $list=array(0,1,2,3);
        //如果接收到的参数不在允许范围内，则视为非法操作
        if (!in_array($params['status'], $list)){
            returnjson(array('code'=>1017), $this->returnstyle, $this->callback);
        }
        $where='';
        foreach ($sellist[$params['status']] as $key => $val){
            if ('' !== $params[$key] && false !== $params[$key]){
//                 dump($val);
                $where.=' `'.$val[0].'` '.$val[1].' '.$params[$key].' and';
            }
        }
        $where=substr($where, 0, -3);
        $db=M('scoretransfer',$admininfo['pre_table']);
        
        if ($export == 1){
            $count=$db->field(array('sharetime','sharermobile','isreceive','scorenumber','receivermobile','receivetime'))->where($where)->count();
            $data=$db->field(array('sharetime','sharermobile','isreceive','scorenumber','receivermobile','receivetime'))->where($where)->limit($p, $rows)->order('id desc')->select();
            
//             echo $db->_sql();
            if (null != $data){
                returnjson(array('code'=>200,'data'=>array('data'=>$data,'page'=>$page,'count'=>(int)$count)), $this->returnstyle, $this->callback);
            }else {
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);
            }
        }elseif ($export == 2) {
            $data=$db->field(array('sharetime','sharermobile','isreceive','scorenumber','receivermobile','receivetime'))->where($where)->order('id desc')->select();
            $str="序号,转赠时间,转赠用户,状态,积分,领取人,领取时间\r\n";
            $i=1;
            
            foreach ($data as $keys => $val){
                $status=$val['isreceive']==1 ? "已领取" : ($val['isreceive'] ==2 ? "已超时" : "未领取");
                $receivetime = 0 != $val['receivetime'] ? date('Y-m-d H:i:s', $val['receivetime']) : ' ';
                $str .= $i.",".date('Y-m-d H:i:s',(int)$val['sharetime']).",".$val['sharermobile'].",".$status.",".$val['scorenumber'].",".$val['receivermobile'].",".$receivetime."\r\n";
                $i++;
            }
            $str=iconv('utf8', 'gb2312', $str);
            $return=mkdir_ext($str,RUNTIME_PATH.'scoretransfer/','csv');
            if($return['code']==200){
                $time = date("Ymd");
                $uniqid = uniqid();
                $key = 'scoretransfer_'.$time.'_'.$uniqid.'.csv';
                $qiniu=new QiniuController();
                list($ret, $err)=$qiniu->uploadfile($return['path'],$key);
                unlink($return['path']);
                if ($err !== null) {
                    $msg['code']=104;
                }else{
                    $msg['code']=200;
                    $msg['data']=array('path'=>"https://img.rtmap.com/".$key);
                }
            }else{
                $msg['code']=$return['code'];
            }
            returnjson($msg, $this->returnstyle, $this->callback);
        }
        
        
    }


    private function CheckTable($admin)
    {
        $db=M();
        $check=$db->execute('SHOW TABLES like "'.$admin['pre_table'].'scoretransfer"');
        if (1 !==$check) {//没有，则自动创建表
            $sql="CREATE TABLE `".$admin['pre_table']."scoretransfer` (
`id` int(100) NOT NULL AUTO_INCREMENT,
`scorenumber` int(50) NOT NULL COMMENT '分享的积分数',
`sharetime` int(20) NOT NULL COMMENT '分享时的时间戳',
`shareusercard` varchar(20) NOT NULL DEFAULT '' COMMENT '分享人的卡号',
`sharewechatname` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '分享者微信用户名',
`sharerheaderimg` varchar(300) NOT NULL COMMENT '分享人头像',
`sharermobile` varchar(15) NOT NULL DEFAULT '' COMMENT '分享人手机号',
`duetime` int(20) NOT NULL COMMENT '过期时间戳',
`receiveusercard` varchar(20) NOT NULL COMMENT '领取人的卡号',
`receivewechatuser` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '领取者的微信用户名',
`receiverheaderimg` varchar(300) NOT NULL DEFAULT '' COMMENT '领取者头像',
`receivermobile` varchar(15) NOT NULL DEFAULT '' COMMENT '领取人手机号',
`receivetime` int(20) NOT NULL COMMENT '领取时间',
`urlstr` varchar(200) NOT NULL DEFAULT '' COMMENT '分享后C端URL内的字符串',
`isreceive` smallint(1) NOT NULL DEFAULT '0' COMMENT '0未领取，1已领取，2已超时',
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;";
            $return = $db->execute($sql);
            if (0 !== $return){
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);//如果没有表，则直接返回没有结果
            }
        }else{
            return true;
        }
    }


    
}

?>