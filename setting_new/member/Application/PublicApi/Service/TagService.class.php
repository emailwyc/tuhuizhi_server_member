<?php
/**
 * 第三方获取标签
 * Created by PhpStorm.
 * User: zhang
 * Date: 17/10/2017
 * Time: 14:14
 */

namespace PublicApi\Service;


class TagService
{


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


}