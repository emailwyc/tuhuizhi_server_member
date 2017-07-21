<?php
namespace MerAdmin\Controller;

use Common\Controller\CommonController;
class CliController extends CommonController
{
    
    
    /**
     * 积分转赠定时任务
     * 定时任务，执行超时退回积分
     */
    public function checkDue()
    {
        $db=M('admin', 'total_');
        $admin=$db->field('pre_table,ukey,signkey')->where(array('wechat_appid'=>array('neq','')))->select();
        $m=M();
        foreach ($admin as $key => $val){
            $sql=' SHOW TABLES LIKE "'.$val['pre_table'].'scoretransfer"';
            $re=$m->execute($sql);
            if ($re===1){
                $dbscore=M('scoretransfer', $val['pre_table']);
                $sel=$dbscore->where(array('isreceive'=>0,'duetime'=>array('elt', time())))->select();//查询未领取的
                
                echo $dbscore->_sql();
                
                //如果查询有结果
                if (null != $sel){
                    foreach ($sel as $k => $v){
                        //将扣除的积分退回，可减少定时任务的工作量
                        $as['key_admin']=$val['ukey'];
                        $as['cardno']=$v['shareusercard'];
                        $as['scoreno']=$v['scorenumber'];
                        $as['why']='积分转赠超时返还积分';
                        $as['scorecode']=date('Y-m-d');//积分转赠
                        $as['sign_key']=$val['signkey'];
                        $as['membername']='name';
                        $as['sign']=sign($as);
                        unset($as['sign_key']);
                        $url=C('DOMAIN').'/CrmService/OutputApi/Index/addintegral';
                        $backscore=http($url, $as);
                        unset($as);
                        
                        $array=json_decode($backscore, true);
                        if ($array['code']==200){
                            $change=$dbscore->where(array('urlstr'=>$v['urlstr']))->save(array('isreceive'=>2));
                            if (false === $change){//再来一次
                                $change=$dbscore->where(array('urlstr'=>$v['urlstr']))->save(array('isreceive'=>2));
                            }
                        }
                    }
                }else {
                    continue;
                }
            }else {
                continue;
            }
        }
    }
    
    
    
    
}

?>