<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 17/10/2017
 * Time: 11:08
 */

namespace MerAdmin\Controller;


use MerAdmin\Service\TagService;

class TagController extends AuthController
{
// TODO - Insert your code here
    public function _initialize() {
        parent::_initialize();
    }



    /**
     * 栏目列表
     * @param $id
     * @return mixed
     */
    public function jurisdiction_list(){
        $quan_db=M('auth','total_');
        $quan_arr=$quan_db->where(array('istag'=>1))->select();
        if(empty($quan_arr)){
            $msg=array('code'=>102);
        }else{
            $msg=array('code'=>200,'data'=>$quan_arr);
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }


    /**
     * 标签添加
     */
    public function tagcreate()
    {
        $params['tagname'] = I('tagname');
        $params['menu'] = I('menuid');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params) || !is_array($params['menu']) || in_array('', $params['menu'])){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $add = TagService::store($params['tagname'], $params['menu'], $admininfo);
        returnjson($add, $this->returnstyle,$this->callback);
    }


    /**
     * 标签列表
     */
    public function tagslist()
    {
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['name'] = I('tagname');
        $params['menu'] = I('menuid');
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = TagService::tagList($admininfo, false, $params['name'], $params['menu']);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 单个标签详情
     */
    public function taginfo()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('tagid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = TagService::tagList($admininfo, (int)$params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 标签修改
     */
    public function tagedit()
    {
        $params['id'] = I('tagid');
        $params['tagname'] = I('tagname');
        $params['menu'] = I('menuid');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params) || !is_array($params['menu']) || in_array('', $params['menu'])){
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $add = TagService::updateTag($params['id'], $params['tagname'], $params['menu'], $admininfo);
        returnjson($add, $this->returnstyle,$this->callback);
    }


    public function tagdestroy()
    {
        $params['id'] = I('id');//id是数组格式
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params) || !is_array($params['id'])) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $del = TagService::destroyTages($admininfo, $params['id']);
        returnjson($del, $this->returnstyle,$this->callback);
    }








}