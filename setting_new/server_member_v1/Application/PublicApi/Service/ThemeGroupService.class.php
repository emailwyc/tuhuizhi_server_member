<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 18/10/2017
 * Time: 18:29
 */

namespace PublicApi\Service;


class ThemeGroupService
{

    /**
     * 第一部分：
     * 主题管理
     */

    /**
     * 主题列表
     * @param $adminInfo
     * @param null $id 传入$id ，查询一个
     * @param null $name
     * @return array
     */
    public static function ThemeList($adminInfo, $id = null, $name=null)
    {
        $d = D('TotalTagsThemsList');
        $where = array('adminid'=>$adminInfo['id'], 'isdel'=>0);
        if ($id != false && is_int($id)) {
            $where['id'] = $id;
        }
        if ($name != false) {
            $where['themname'] = array('like', '%'.$name.'%');
        }

        $sel = $d->where($where)->relation(true)->order('sort asc')->select();
        if ($sel){
            return array('code'=>200, 'data'=>$sel);
        }else{
            return array('code'=>104, 'data'=>1);
        }
    }


    /**
     * 调用self::ThemeList()
     * @param $adminInfo
     * @param null $id
     * @param null $name
     * @return array
     */
    public static function ThemeInfo($adminInfo, $id = null, $name=null)
    {
        return self::ThemeList($adminInfo, $id, $name);
    }





    /***********************************************************优美的分割线**********************************************************/
    /**
     * 第二部分：
     * 分组管理
     */

    /**
     * 分组列表
     * @param $adminInfo
     * @param null $id 传入$id ，查询一个
     * @param null $name 分组的名字
     * @return array
     */
    public static function GroupList($adminInfo, $id = null, $name=null)
    {
        $d = D('TotalTagsGroupList');
        $where = array('adminid'=>$adminInfo['id'], 'isdel'=>0);
        if ($id != false && is_int($id)) {
            $where['id'] = $id;
        }
        if ($name != false) {
            $where['groupname'] = array('like', '%'.$name.'%');
        }

        $sel = $d->where($where)->relation(true)->order('sort asc')->select();
        if ($sel){
            return array('code'=>200, 'data'=>$sel);
        }else{
            return array('code'=>104, 'data'=>1);
        }
    }


    /**
     * 调用self::GroupList()
     * @param $adminInfo
     * @param null $id
     * @param null $name
     * @return array
     */
    public static function GroupInfo($adminInfo, $id = null, $name=null)
    {
        return self::GroupList($adminInfo, $id, $name);
    }


}