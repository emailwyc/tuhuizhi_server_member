<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 25/10/2017
 * Time: 17:16
 */

namespace MerAdmin\Service;


class ThemGroupBannerService
{

    /**
     * 新建banner
     * @param $banner
     * @param $url
     * @param $adminInfo
     * @return array
     */
    public static function addBanner( $banner,$url, $adminInfo)
    {
        if ($url==false || $banner == false || !is_array($adminInfo)){
            return array('code'=>1051, 'data'=>1);
        }
        $d = D('TotalThemgroupbanners');
        $find = $d->where(array('isdel'=>0, 'adminid'=>$adminInfo['id']))->max('sort');
        $data = array(
            'bannerurl'=>$banner,
            'bannerredirect'=>$url,
            'sort'=>$find['sort'] + 1,
            'adminid'=>$adminInfo['id']
        );
        $add = $d->add($data);
        if ($add) {
            return ['code'=>200];
        }else{
            return ['code'=>104];
        }
    }


    /**
     * 修改banner
     * @param $id
     * @param $banner
     * @param $url
     * @param $adminInfo
     * @return array
     */
    public static function editBanner($id, $banner,$url, $adminInfo)
    {
        if ($url==false || $banner == false || !is_array($adminInfo)){
            return array('code'=>1051, 'data'=>1);
        }
        $d = D('TotalThemgroupbanners');
        $data = array(
            'bannerurl'=>$banner,
            'bannerredirect'=>$url,
        );
        $save = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save($data);
        if ($save !== false) {
            return ['code'=>200];
        }else{
            return ['code'=>104];
        }
    }


    /**
     * 删除banner
     * @param $id
     * @param $adminInfo
     * @return array
     */
    public static function destroyBanner($id, $adminInfo)
    {
        $d = D('TotalThemgroupbanners');
        $del = $d->where(array('id'=>$id,'adminid'=>$adminInfo['id']))->delete();
        if ($del !== false) {
            return ['code'=>200];
        }else{
            return ['code'=>104];
        }
    }


    /**
     * banner列表
     * @param $adminInfo
     * @return array
     */
    public static function listBanner($adminInfo)
    {
        $d = D('TotalThemgroupbanners');
        $select = $d->where(array('adminid'=>$adminInfo['id']))->order('sort asc, id desc')->select();
        if ($select) {
            return array('code'=>200, 'data'=>$select);
        }else{
            return array('code'=>102);
        }
    }


    /**
     * banner排序
     * @param $adminInfo
     * @param $type
     * @param $id
     * @return array
     */
    public static function sortBanner($adminInfo, $type, $id) {
        $d = D('TotalThemgroupbanners');
        $find = $d->where(array('id'=>$id,'adminid'=>$adminInfo['id'],'isdel'=>0))->find();
        if (!$find){
            return array('code'=>102, 'data'=>1);
        }

        if ($type == 'up'){
            if ($find['sort'] <= 1){//如果之前的排序本来就是最高的或因为某些因素导致数据错误，则修改为正确数据
                $d->where(array('id'=>$id))->save(array('sort'=>1));
                return array('code'=>200, 'data'=>1);
            }

            $findone = $d->where(array('sort'=>array('lt', $find['sort']), 'adminid'=>$adminInfo['id'], 'isdel'=>0))->order('sort desc')->find();//查询，比要升序的这个id排序大的数据，有则将其改为id的排序，没有则只管将id的排序加一
            if ($findone) {
                //交换两者的排序
                $save = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>$findone['sort']));
                $save = $d->where(array('id'=>$findone['id'], 'adminid'=>$adminInfo['id']))->save(array('sort'=>$find['sort']));
            }else{
                $save = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>1));//如果找不到的话，说明是这个前面没数据，没有比他数字小的了
            }

            if ($save) {
                return array('code'=>200, 'data'=>2);
            }else{
                return array('code'=>104, 'data'=>1);
            }

        }else if ($type == 'down'){
            $findone = $d->where(array('sort'=>array('gt', $find['sort']), 'adminid'=>$adminInfo['id'], 'isdel'=>0))->order('sort asc')->find();//查询，比要升序的这个id排序大的数据，有则将其改为id的排序，没有则只管将id的排序加一
            if ($findone) {
                //交换两者的排序
                $save = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>$findone['sort']));
                $save = $d->where(array('id'=>$findone['id'], 'adminid'=>$adminInfo['id']))->save(array('sort'=>$find['sort']));
                if ($save) {
                    return array('code'=>200, 'data'=>3);
                }else{
                    return array('code'=>104, 'data'=>1);
                }
            }else{//没有排序数字比他大的了，已经是最后一个
                return array('code'=>200, 'data'=>4);
            }

        }else{
            return array('code'=>1051, 'data'=>1);
        }
    }
}