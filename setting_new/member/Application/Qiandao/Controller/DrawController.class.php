<?php
namespace Qiandao\Controller;

/**
 *
 * @author 张凯锋
 * @deprecated 抽奖接口
 *        
 */
class DrawController extends CommonController{
    
    public $openid;//微信openid
    public $supermarketid;
    
    public function __construct(){
        
        
        $this->first();
    }
    /**
     * @deprecated 抽奖动作接口，请求即可抽奖，参数isbuy是   是否用积分抽奖
     */
    public function getDrawing(){
        $openid=I('get.uid');
        if (empty($openid)){
            exit();
        }
        $this->openid=$openid;
        
        $supermarketid=I('get.marketid');
        if (empty($supermarketid)){
            exit();
        }
        
        $this->supermarketid=$supermarketid;
        
        $isbuy=I('get.isbuy');
        
        $Dsupmarket=D('supermarket');
        $find=$Dsupmarket->where(array('id'=>$this->supermarketid))->find();
        if (null!=$find){//如果有这个商场，则进行抽奖
            $Duser=D('user');
            $seluser=$Duser->where(array('openid'=>$this->openid))->find();
            if ($isbuy==1 && $seluser['score'] <$find['drawdelscore']){
                echo returnjson(array('msg'=>'会员积分数不满足积分抽奖','mstatus'=>false),$this->returnstyle,$this->callback);exit();
            }
            
            if (null!=$seluser ){
                //调用抽奖接口
                $url=C('DRAW_URL').C('DRAW_CODE').'/'.$this->openid;
                try {
                    $return =http($url);
                } catch (Exception $e) {
                    echo returnjson(array('msg'=>$e->getMessage(),'mstatus'=>false),$this->returnstyle,$this->callback);exit();
                }
                
                $arr=json_decode($return,true);
                
                if ($isbuy==1){//如果是1，则扣除积分
                    //抽完奖扣除会员的积分
                    $delscore=$Duser->where(array('openid'=>$this->openid))->setDec('score',$find['drawdelscore']);
                }
                //查询此会员连续签到多少天，将天数添加进抽奖时连续签到的天数
                $qiandao=D('qiandao')->field('lxqdtianshu')->where(array('uid'=>$this->openid))->find();
                //抽奖完成后将信息保存到数据库，不管有没有抽到将，用作抽奖记录
                $D=D('draw');
                $arr['lxqdtsdraw']=$qiandao['lxqdtianshu'];
                $arr['isbuy']=(int)$isbuy;
                $arr['date']=date('Y-m-d');
                $add=$D->add($arr);
                
                
                echo returnjson($return,$this->returnstyle,$this->callback);
            }else {
                echo returnjson(array('msg'=>'没有找到此会员','mstatus'=>false),$this->returnstyle,$this->callback);
            }
        }else {
            echo returnjson(array('msg'=>'没有找到此超市','mstatus'=>false),$this->returnstyle,$this->callback);
        }
        
        
    }
    
    
    /**
     * 获取中奖历史中奖，中奖，中奖
     * 可按openid或查询所有的id
     */
    public function getdrawlist(){
        $uid                                       = I('get.uid','','htmlspecialchars');
        $where                                  = empty($uid)?array('code'=>0):array('code'=>0,'openId'=>$uid);
        $ddraw=D('draw','','DB_CONFIG1');
        
        $page = !empty($_GET['page']) ?I('get.page'):1;
        $rows = !empty($_GET['rows']) ? I('get.rows'):10;
        $p                                  = ($page - 1) * $rows;
        $c                                  = $ddraw->where($where)->count();
        $join                               = ' join `total_user` on `total_user`.`openid` = `qiand_draw`.`openId`';
        $list                               = $ddraw->join($join)->where($where)->order('draw_id desc')->limit ( $p, $rows )->select();
        
//         if (null != $list){
//             $duser                           = D('qiandao','','DB_CONFIG1');
//             $openids                        = array_unique(array_column($list, 'openid'));
//             $seluser                         = $duser->where(array('uid'=>array('in',$openids)))->select();
//             if (null != $seluser){
//                 foreach ($list as $key => $val){
//                     foreach ($seluser as $k => $v){
//                         if ($val['openid']==$v['uid']){
//                             $list[$key]['lxqdtianshu']=$v['lxqdtianshu'];
//                             break;
//                         }
//                     }
//                 }
//             }else {
//                 $list=null;
//             }
//         }
        if (null!=$list){
            $data=array('msg'=>'查询结果成功','rows'=>$list,'total'=>$c,'status'=>true);
        }else {
            $data=array('msg'=>'没有查询到相关数据','rows'=>$list,'total'=>$c,'status'=>false);
        }
        echo returnjson($data,$this->returnstyle,$this->callback);
    }
    
    
    /**
     * 某一个用多少积分抽奖
     */
    public function getmarketdrawscore(){
        $supermarketid=I('get.marketid');
        if (empty($supermarketid)){
            exit();
        }
        $dmarket                                = D('supermarket');
        $find                                       = $dmarket->field('drawdelscore')->where(array('id'=>$supermarketid))->find();
        if (null != $find){
            $data                               = array('msg'=>'查询结果成功','score'=>$find['drawdelscore'],'status'=>true);
        }else {
            $data                               =array('msg'=>'没有查询到相关数据','score'=>'','status'=>false);
        }
        
        
        echo returnjson($data,$this->returnstyle,$this->callback);
    }
    



    

}

?>