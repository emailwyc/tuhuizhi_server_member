<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 18/10/2017
 * Time: 18:28
 */

namespace MerAdmin\Controller;


use MerAdmin\Service\ManageThemeGroupService;
use MerAdmin\Service\ThemGroupBannerService;

class ManageThemeGroupController extends AuthController
{
    public function _initialize() {
        parent::_initialize();
    }


    /**
     * 创建主题
     */
    public function createtheme()
    {
        $params['name'] = I('name');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['tags'] = I('tags');
        $params['icon'] = I('icon');
        $params['banners'] = I('banners');
        $params['classes'] = I('classes');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $add = ManageThemeGroupService::ThemeStore($params['name'],$params['classes'], $params['tags'], $params['icon'], $params['banners'], $adminInfo);
        returnjson($add,$this->returnstyle,$this->callback);
    }


    /**
     * 主题列表
     */
    public function themlist()
    {
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = ManageThemeGroupService::ThemeList($admininfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 主题详情
     */
    public function themeinfo()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('themeid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = ManageThemeGroupService::ThemeInfo($admininfo, (int)$params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 主题修改
     */
    public function themeedit()
    {
        $params['name'] = I('name');
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['tags'] = I('tags');
        $params['icon'] = I('icon');
        $params['banners'] = I('banners');
        $params['class'] = I('class');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $add = ManageThemeGroupService::ThemeUpdate($params['id'], $params['name'], $params['class'], $params['tags'], $params['icon'], $params['banners'], $adminInfo);
        returnjson($add,$this->returnstyle,$this->callback);
    }


    /**
     * 删除主题，批量删除
     */
    public function themedestroy()
    {
        $params['id'] = I('id');//id是数组格式
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params) || !is_array($params['id'])) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $del = ManageThemeGroupService::ThemeDestroy($admininfo, $params['id']);
        returnjson($del, $this->returnstyle,$this->callback);
    }


    /**
     * 主题排序
     */
    public function themsort()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        $params['type'] = I('type');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        if (!in_array($params['type'], array('up', 'down'))) {
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($params['key_admin']);
        $save = ManageThemeGroupService::ThemSort($params['id'], $params['type'], $adminInfo);
        returnjson($save,$this->returnstyle,$this->callback);
    }







    /**
     * 第二部分：
     * 分组设置
     */
    /**
     * 创建分组
     */
    public function creategroup()
    {
        $params['name'] = I('name');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['tags'] = I('tags');//标签id，数组
        $params['maxselect'] = (int)I('maxselect');
//        dump($params);die;
        $adminInfo = $this->getMerchant($params['key_admin']);
        $add = ManageThemeGroupService::GroupStore($params['name'], $params['tags'], $params['maxselect'], $adminInfo);
        returnjson($add,$this->returnstyle,$this->callback);
    }


    /**
     * 分组列表
     */
    public function grouplist()
    {
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = ManageThemeGroupService::GroupList($admininfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 分组详情
     */
    public function groupinfo()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('groupid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = ManageThemeGroupService::GroupInfo($admininfo, (int)$params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     * 分组修改
     */
    public function groupedit()
    {
        $params['name'] = I('name');
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['tags'] = I('tags');//标签id，数组
        $params['maxselect'] = (int)I('maxselect');
        $adminInfo = $this->getMerchant($params['key_admin']);
        $add = ManageThemeGroupService::GroupUpdate($params['id'], $params['name'], $params['tags'], $params['maxselect'], $adminInfo);
        returnjson($add,$this->returnstyle,$this->callback);
    }


    /**
     * 删除分组，批量删除
     */
    public function groupdestroy()
    {
        $params['id'] = I('id');//id是数组格式
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params) || !is_array($params['id'])) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $del = ManageThemeGroupService::GroupDestroy($admininfo, $params['id']);
        returnjson($del, $this->returnstyle,$this->callback);
    }


    /**
     * 分组排序
     */
    public function groupsort()
    {
        $params['id'] = I('id');
        $params['type'] = I('type');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        if (!in_array($params['type'], array('up', 'down'))) {
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $save = ManageThemeGroupService::GroupSort($params['id'], $params['type'], $admininfo);
        returnjson($save,$this->returnstyle,$this->callback);
    }


    /**
     * 第三部分
     * banner
     */
    /**
     * banner列表
     */
    public function bannerlist()
    {
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($params['key_admin']);
        $data = ThemGroupBannerService::listBanner($admininfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    /**
     *
     */
    public function addbanner()
    {
        $params['redirect'] = I('redirect');
        $params['bannerurl'] = I('bannerurl');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($this->ukey);
        $data = ThemGroupBannerService::addBanner($params['bannerurl'], $params['redirect'], $adminInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    public function editbanner()
    {
        $params['redirect'] = I('redirect');
        $params['bannerurl'] = I('bannerurl');
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($this->ukey);
        $data = ThemGroupBannerService::editBanner($params['id'], $params['bannerurl'], $params['redirect'], $adminInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }


    public function destroybanner()
    {
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($this->ukey);
        $data = ThemGroupBannerService::destroyBanner($params['id'], $adminInfo);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    public function sortbanner()
    {
        $params['key_admin'] = I('key_admin');
        $params['type'] = I('type');
        $params['id']=I('id');
        if (in_array('', $params) || !in_array($params['type'], array('up','down'))) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $adminInfo = $this->getMerchant($this->ukey);
        $data = ThemGroupBannerService::sortBanner($adminInfo, $params['type'], $params['id']);
        returnjson($data,$this->returnstyle,$this->callback);
    }





}