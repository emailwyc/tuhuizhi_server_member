<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/1/19
 * Time: 11:29
 * 表：total_form,total_form_default
 * 各个商户表：xxx_auto_form
 */

namespace DevAdmin\Controller;


class AutoFormController extends DevcommonController
{
    public function _initialize(){
        parent::__initialize();
    }



    /*
     * 创建表单字段
     * content_type值为：radio,input,select,checkbox,date,time
     */
    public function CreateForm()
    {
        $params['content']=I('content');
        $params['content_key']=I('content_key');
        $params['content_type']=I('content_type');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $content_type=array('text','password','number','radio','checkbox','date','time','select','textarea');
        if (!in_array($params['content_type'], $content_type)){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }
        $content_default=I('content_default');
        $params['function_name']=I('function_name');//如果传入函数名，则返回该函数的返回值
        $db=M('total_form');
        $dbdefault=M('total_form_default');
        //查询之前是否有此项
        $find=$db->where(array('content'=>$params['content'],'content_key'=>$params['content_key'],'_logic'=>'or'))->find();
        if ($find != null){
            returnjson(array('code'=>1008), $this->returnstyle, $this->callback);
        }
        //因同时操作两张表，故使用事务
        $db->startTrans();
        $add=$db->add($params);
        if ($content_default != ''){
            $arr1=explode(',', $content_default);
            foreach ($arr1 as $item => $value){
                if ($value != ''){
                    $arr2=explode('|', $value);
                    $da[$item]['form_id']=$add;
                    $da[$item]['default_content']=$arr2[1];
                    $da[$item]['default_content_key']=$arr2[0];
                }
            }
            $add_default=$dbdefault->addAll($da);
        }else{
            $add_default=true;
        }
        if ($add && $add_default){
            $db->commit();
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            $db->rollback();
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 删除一个表单项
     * 1、为防止出现意外，不提供批量删除
     * 2、删除为物理删除
     */
    public function DeleteForm()
    {
        $id=I('id');
        $db=M('total_form');
        $dbdefault=M('total_form_default');
        if (!$db->where(array('id'=>$id))->find()){
            returnjson(array('code'=>1035), $this->returnstyle, $this->callback);
        }

        $db->commit();
        $delform=$db->where(array('id'=>$id))->delete();
        $deldefault=$dbdefault->where(array('form_id'=>$id))->delete();
        if ($delform !== false && $deldefault !== false){
            $db->commit();
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            $db->rollback();
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 自动表单项列表
     */
    public function FormList()
    {
        $db=M('total_form');
        $sel=$db->select();
        $dbdefault=M('total_form_default');
        foreach ($sel as $item => $value) {
            $seldefault=$dbdefault->where(array('form_id'=>$value['id']))->select();
            if ($seldefault != null){
                $sel[$item]['content_default']=$seldefault;
            }
        }
        returnjson(array('code'=>200, 'data'=>$sel), $this->returnstyle, $this->callback);
    }




}