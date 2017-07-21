<?php
namespace Platformservice\Controller;

class VisitdataController extends PlatcommonController{
    // TODO - Insert your code here
    
    
    /**
     * 展示接口访问量
     */
    public function shownums(){
        $db = M('pv','total_');
        $sel=$db->order('date asc')->select();
        $dbname=M('pv_name','total_');
        $names=$dbname->select();
        foreach ($sel as $key => $val){//循环接口访问量
            foreach ($names as $k => $v){//循环ｎａｍｅ
            
            }
        }
        
        
        
        
        $this->display();
    }
    
}

?>