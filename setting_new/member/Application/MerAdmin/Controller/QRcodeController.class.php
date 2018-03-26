<?php
/**
 * ycoinController
 */
namespace MerAdmin\Controller;
class QRcodeController extends AuthController
{
    public $key_admin;
    public $admin_arr;
    public function _initialize(){
        parent::_initialize();
        //查询商户信息
        $this->key_admin=$this->ukey;

    }

    //添加或者编辑(name,link,desc,id-)
    public function editQRcode(){
        $params = $this->params;
        $this->emptyCheck($params,array('name','link'));//desc,id
        $db=M('qrcode',$this->admin_arr['pre_table']);
        if(empty($params['id'])){
            //添加
            $insert = array(
                'name'=>$params['name'],
                'desc'=>$params['desc'],
                'link'=>$params['link'],
                'visitnum'=>0
            );
            $lastid = $db->add($insert);
        }else{
            //编辑
            $insert = array(
                'name'=>$params['name'],
                'desc'=>$params['desc'],
                'link'=>$params['link']
            );
            $db->where(array('id'=>(int)$params['id']))->save($insert);
        }
        $msg['code']=200; $msg['data']=$insert;
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取单条二维码信息(id)
    public function getQRcodeOne(){
        //获取单条
        $params = $this->params;
        $this->emptyCheck($params,array('id'));//id
        $db = M('qrcode',$this->admin_arr['pre_table']);
        $arr=$db->where(array('id'=>$params['id']))->find();
        if($arr){
            //获取今日访问量
            $db1 = M('qrcode_statistic',$this->admin_arr['pre_table']);
            $arr1=$db1->where(array('date'=>date('Y-m-d'),'qid'=>$arr['id']))->find();
            $todayVisit = empty($arr1['num'])?0:(int)$arr1['num'];
            $arr['todayVisit'] = $todayVisit;
            $msg['code']=200; $msg['data']=$arr;
        }else{
            $msg = array('code'=>102,'data'=>$arr);
        }
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取二维码列表(keyword,page)
    public function getQRcodeList(){
        $params = $this->params;
        $this->emptyCheck($params,array('page'));
        $page = ((int)$params['page'])<=0?1:((int)$params['page']);
        $offset = 10;
        $start = ($page-1)*$offset;
        $db=M('qrcode',$this->admin_arr['pre_table']);
        $where = empty($params['keyword'])?array():array('name'=>array('like','%'.$params['keyword'].'%'));
        $arr=$db->where($where)->order("id desc")->limit($start,$offset)->select();
        if($arr){
            $qids = ArrKeyAll($arr,'id',0);
            //获取今日访问量
            $db1 = M('qrcode_statistic',$this->admin_arr['pre_table']);
            $arr1=$db1->where(array('date'=>date('Y-m-d'),'qid'=>array('in',$qids)))->select();
            $arr1 = ArrKeyFromId($arr1,'qid');
            foreach ($arr as $k=>$v){
                $arr[$k]['todayvisit'] = empty($arr1[$v['id']]['num'])?0:(int)$arr1[$v['id']]['num'];
                $arr[$k]['visitnum']  = (int)$v['visitnum'];
            }
        }
        $count = (int)$db->where($where)->count();
        $allpage = ceil($count/$offset);
        $msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    //获取访问量列表(qid,type(1,2),daynum,startdate,enddate)// 开始时间不能为空
    public function getStatisticList(){
        $params = $this->params;
        $this->emptyCheck($params,array('type','qid'));
        $curtime = time();
        if($params['type'] ==1){
            $this->emptyCheck($params,array('daynum'));
            $startdate = date('Y-m-d',$curtime-86400*($params['daynum']-1));
            $enddate = date('Y-m-d',time());
        }else{
            $this->emptyCheck($params,array('startdate'));
            $startdate = $params['startdate'];
            $enddate = empty($params['enddate'])?date('Y-m-d',strtotime($startdate)+86400*(30-1)):$params['enddate'];
        }
        $db=M('qrcode_statistic',$this->admin_arr['pre_table']);
        $where = array('date'=>array('egt',$startdate),'date'=>array('elt',$enddate),'qid'=>$params['qid']);
        $arr=$db->where($where)->order("id desc")->select();
        $arr = ArrKeyFromId($arr,'date');
        $newArr = array();
        while($enddate>=$startdate){
            $num = empty($arr[$enddate])?0:$arr[$enddate]['num'];
            $newArr[$enddate] = array('date'=>$enddate,'num'=>(int)$num);
            $enddate = date("Y-m-d",strtotime($enddate)-86400);
        }
        $newArr = ArrObjChangeList($newArr);
        $msg = array('code'=>200,'data'=>$newArr);
        returnjson($msg,$this->returnstyle,$this->callback);
    }

}

?>
