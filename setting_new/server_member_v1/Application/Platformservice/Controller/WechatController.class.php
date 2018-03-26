<?php
namespace Platformservice\Controller;

use Common\Controller\RedisController;
class WechatController extends PlatcommonController{
    // TODO - Insert your code here
    
    public function index(){
        $this->display();
    }
    
    
    //查看微信授权图表
    public function showclient(){
        $serverip=gethostbyname($_SERVER["SERVER_NAME"]);
        if ('123.56.109.26'!=$serverip || '101.200.229.5' !=$serverip){
            $show='您现现在查看的是本地redis哦';
        }else{
            $show='';
        }
        $rediss=new RedisController();
        $redis= $rediss->connectredis(2);
        $android=$redis->get('android');
        $iphone=$redis->get('iphone');
        $macosx=$redis->get('macosx');
        $ipad=$redis->get('ipad');
        $other=$redis->get('other');
        $arr=array(array('clientnums'=>$android,'client'=>'android'),array('clientnums'=>$iphone,'client'=>'iphone'),array('clientnums'=>$ipad,'client'=>'ipad'),array('clientnums'=>$macosx,'client'=>'mac os x'),array('clientnums'=>$other,'client'=>'other'));
        $this->assign(array('data'=>$arr,'show'=>$show,'total'=>$android+$iphone+$macosx+$ipad+$other))->display();
    
    }
    
    public function showlinemarker()
    {
        $db=M('wechatoath', 'total_');
        $othercount=$db->field('othercount,date')->group('date')->order('id asc')->select();
        $androidcount=$db->field('androidcount,date')->group('date')->order('id asc')->select();
        $iphonecount=$db->field('iphonecount,date')->group('date')->order('id asc')->select();
        $ipodcount=$db->field('ipodcount,date')->group('date')->order('id asc')->select();
        $ipadcount=$db->field('ipadcount,date')->group('date')->order('id asc')->select();
        $macosxcount=$db->field('macosxcount,date')->group('date')->order('id asc')->select();
        $todaytotal=$db->field('todaytotal,date')->group('date')->order('id asc')->select();
        
        $other=null;
        foreach ($othercount as $key =>$val){
            $other[]=$val['othercount'] ? (int)$val['othercount'] : 0;
        }
        $android=null;
        foreach ($androidcount as $key =>$val){
            $android[]=$val['androidcount'] ? (int)$val['androidcount'] : 0;
        }
        $iphone=null;
        foreach ($iphonecount as $key =>$val){
            $iphone[]=$val['iphonecount'] ? (int)$val['iphonecount'] :0;
        }
        $ipod=null;
        foreach ($ipodcount as $key =>$val){
            $ipod[]=$val['ipodcount'] ? (int)$val['ipodcount'] : 0;
        }
        $ipad=null;
        foreach ($ipadcount as $key =>$val){
            $ipad[]=$val['ipadcount'] ? (int)$val['ipadcount'] : 0;
        }
        $macosx=null;
        foreach ($macosxcount as $key =>$val){
            $macosx[]=$val['macosxcount'] ? (int)$val['macosxcount'] : 0;
        }
        $date=null;
        $today=null;
        foreach ($todaytotal as $key =>$val){
            $today[]=$val['todaytotal'] ? (int)$val['todaytotal'] : 0;
            $date[]=$val['date'];
        }
        
        
        $arr=array(
            'android'=>$android,
            'iphone'=>$iphone,
            'ipod'=>$ipod,
            'ipad'=>$ipad,
            'macosx'=>$macosx,
            'other'=>$other,
            'today'=>$today
        );
//         dump($date);
//         dump($arr);
        $this->assign(array('device'=>$arr,'date'=>$date))->display();
//         $this->assign($sel);
    }
    
}

?>