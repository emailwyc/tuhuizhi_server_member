<?php
/**
 * 大B端标签管理
 * Created by PhpStorm.
 * User: zhang
 * Date: 17/10/2017
 * Time: 14:14
 */

namespace MerAdmin\Service;


class TagService
{
    /**
     * 添加标签
     * @param $tagName
     * @param array $tagMenu
     * @param array $admininfo
     * @return array
     */
    public static function store($tagName, array $tagMenu, array $admininfo)
    {
        if (empty($tagName) || in_array('', $tagMenu)){
            return array('code'=>1030, 'data'=>1);
        }

        $tagMenu = array_unique($tagMenu);//去重

        //验证标签名是否重复
        $d = D('TotalTags');
        $find = $d->where(array('tagname'=>$tagName,'adminid'=>$admininfo['id'], 'isdel'=>0))->find();
        if ($find){
            return array('code'=>1008);
        }

        //验证menuid是否开启了标签绑定
        $db = M('total_auth');
        $sel = $db->where(array('istag'=>1))->select();
        $munuids = array_column($sel, 'id');
        foreach ($tagMenu as $key => $value) {
            if (!in_array($value, $munuids)){//判断id是否允许
                return array('code'=>1051, 'data'=>1);
                break;
            }
            $data['tagsadd'][] = array(
                'menuid'=>$value
            );
        }
        $data['adminid'] = $admininfo['id'];
        $data['tagname'] = $tagName;
        $data['create'] = date('Y-m-d H:i:s');

        $add = $d->relation('tagsadd')->add($data);
        if ($add){
            return array('code'=>200);
        }else{
            return array('code'=>104, 'data'=>1);
        }
    }


    /**
     * 获取标签列表 or 获取一个
     * @param $admininfo
     * @return array
     */
    public static function tagList($admininfo, $id = null, $name = null, $menuid = null)
    {
        $d = D('TotalTags');
        $where = array('adminid'=>$admininfo['id'], 'isdel'=>0);
        if ($id != false && is_int($id)) {
            $where['id'] = $id;
        }
        if ($name != false) {
            $where['tagname'] = array('like', '%'.$name.'%');
        }
        if ($menuid != false) {
            $dd = D('TotalTagsMenu');
            $ids = $dd->field('tagsid')->where(array('menuid'=>array('eq', $menuid)))->select();
            if ($ids) {
                $ids = array_column($ids, 'tagsid');
                $where['id'] = array('in', $ids);
            }else{
                return array('code'=>102);
            }
        }

        $sel = $d->where($where)->relation('tagslist')->select();
        if ($sel){
            return array('code'=>200, 'data'=>$sel);
        }else{
            return array('code'=>104, 'data'=>1);
        }
    }


    /**
     * 标签修改
     * @param $id
     * @param $tagName
     * @param array $tagMenu
     * @param array $admininfo
     * @return array
     */
    public static function updateTag($id, $tagName, array $tagMenu, array $admininfo)
    {
        if (empty($tagName) || in_array('', $tagMenu)){
            return array('code'=>1030, 'data'=>1);
        }

        $d = D('TotalTags');
        //判断是否已经删除
        $find = $d->where(array('id'=>$id, 'isdel'=>1))->find();
        if ($find){
            return array('code'=>102, 'data'=>1);
        }

        //查询是否有这个id
        $sel = $d->where(array('id'=>$id, 'isdel'=>0))->select();
        if (!$sel){
            return array('code'=>102, 'data'=>1);
        }

        $tagMenu = array_unique($tagMenu);//去重

        //验证标签名是否重复

        $find = $d->where(array('tagname'=>$tagName,'adminid'=>$admininfo['id'], 'isdel'=>0, 'id'=>array('neq', $id)))->find();
        if ($find){
            return array('code'=>1008);
        }

        //验证menuid是否开启了标签绑定
        $db = M('total_auth');
        $sel = $db->where(array('istag'=>1))->select();
        $munuids = array_column($sel, 'id');
        foreach ($tagMenu as $key => $value) {
            if (!in_array($value, $munuids)){//判断id是否允许
                return array('code'=>1051, 'data'=>1);
                break;
            }
            $data['tagsadd'][] = array(
                'tagsid'=>$id,
                'menuid'=>$value
            );
        }
        $data['adminid'] = $admininfo['id'];
        $data['tagname'] = $tagName;
        $data['id'] = $id;

        $dtagsmenu = D('TotalTagsMenu');
//        $dtagsmenu->startTrans();
//        $d->startTrans();
        $deltagsmenu = $dtagsmenu->where(array('tagsid'=>$id))->delete();// 的关联数据如果传入主键的值 则表示更新 否则就表示新增,tp文档

        $save = $d->relation('tagsadd')->save($data);
        if ($deltagsmenu !== false && $save !== false){
//            $dtagsmenu->commit();
//            $d->commit();
            return array('code'=>200);
        }else{
//            $dtagsmenu->rollback();
//            $d->rollback();
            return array('code'=>104, 'data'=>1);
        }
    }




    public static function destroyTages(array $adminInfo, array $tags)
    {
        $d = D('TotalTags');
        $where = array('adminid'=>$adminInfo['id'], 'id'=>array('in', $tags));
        $del = $d->where($where)->save(array('isdel'=>1));
        if ($del != false){ //删除0个也算失败
            return array('code'=>200);
        }else{
            return array('code'=>104, 'data'=>1);
        }


    }

}