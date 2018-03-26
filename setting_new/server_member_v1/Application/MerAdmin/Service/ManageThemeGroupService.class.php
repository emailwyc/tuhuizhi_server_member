<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 18/10/2017
 * Time: 18:29
 */

namespace MerAdmin\Service;


class ManageThemeGroupService
{

    /**
     * 第一部分：
     * 主题管理
     */

    /**
     * 新增主题
     * @param $name 主题名字
     * @param $class 业态，产品更改需求，去掉
     * @param array $tags 传入的标签数组
     * @param string $icon 传入的标签icon
     * @param $banners 传入的主题banner列表
     * @param $adminInfo 商户详细信息
     * @return array
     */
    public static function ThemeStore($name, $class, $tags, $icon='', $banners, $adminInfo)//
    {
        //除了名字，其它都可以维空
        if (empty($name) || !is_array($adminInfo)) {
            return array('code'=>1030, 'data'=>'class1');
        }
        $d = D('TotalTagsThems');
        //查询name是否已经存在
        $sel = $d->where(array('themname'=>$name, 'isdel'=>0, 'adminid'=>$adminInfo['id']))->select();
        if ($sel){
            return array('code'=>1008);
        }

        $classArray = [];
        if (is_array($class) && count($class) > 0){
            foreach ($class as $key => $value) {
                if (!array_key_exists('id', $value) && !array_key_exists('icon', $value) && !array_key_exists('className', $value) && !array_key_exists('classInfo', $value)){
                    return ['code'=>1030, 'data'=>['code'=>'class2','data'=>$value]];
                    break;
                }elseif(!is_numeric($value['id']) || empty($value['icon']) || empty($value['className']) || empty($value['classInfo'])){
                    return ['code'=>1051, 'data'=>['code'=>'class1','data'=>$value]];
                    break;
                }else{
                    $classArray[] = array(
                        'id' => (int)$value['id'],
                        'icon' => $value['icon'],
                        'className' => $value['className'],
                        'classInfo'=>$value['classInfo']
                    );
                }
            }
        }



        //如果tags数组有值，则判断数组内的是否有tagid字段
        $tagsArray = [];
        if (is_array($tags) && count($tags) > 0) {
            $tags = array_unique($tags);
            //验证标签id是否存在
            $data = TagService::tagList($adminInfo, false);
            $tagids = array_column($data['data'], 'id');//取出所有的标签主键id

            foreach ($tags as $key => $value) {
                if (empty($value)){//没有值或字段
                    return array('code'=>1051, 'data'=>'tag1');
                    break;
                }elseif (!in_array($value, $tagids)){//判断标签id是否在所有id内
                    return array('code'=>1051, 'data'=>'tag2');
                    break;
                }else{
                    $tagsArray[] = array(
                        'tagid'=>$value,//防止有多余的数据，重新赋值给新数组
                        'adminid'=>$adminInfo['id']
                    );
                }
            }
        }

        //如果有banner数据，判断是否有banner图片、跳转链接、顺序
        $bannersArray = [];
        if (is_array($banners) && count($banners) > 0 ) {
            foreach ($banners as $key => $value) {
                if (empty($value['imgurl']) || empty($value['imgredirect']) || empty($value['order'])){
                    return array('code'=>1051, 'data'=>'banner1');
                    break;
                }else{
                    $bannersArray[] = array(
                        'imgurl' => $value['imgurl'],
                        'imgredirect' => $value['imgredirect'],
                        'order' => $value['order'],
                        'adminid'=>$adminInfo['id']
                    );
                }
            }
        }
        $find = $d->where(array('isdel'=>0, 'adminid'=>$adminInfo['id']))->max('sort');
        $data = array(
            'themname'=>$name,
            'icon'=>$icon,
            'adminid'=>$adminInfo['id'],
            'tags'=>$tagsArray,
            'banners'=>$bannersArray,
            'classes'=>json_encode($classArray),
            'sort'=>$find + 1,
            'createtime'=>date('Y-m-d H:i:s')
        );
//        $d = D('TotalTagsThems');
        $add = $d->relation(true)->add($data);
        if ($add) {
            return array('code'=>200);
        }else{
            return array('code'=>104);
        }



    }


    /**
     * 主题修改
     * @param $id
     * @param $name
     * @param $tags
     * @param string $icon
     * @param $banners
     * @param $adminInfo
     * @return array
     */
    public static function ThemeUpdate($id, $name, $class, $tags, $icon='', $banners, $adminInfo)
    {
        //除了名字，其它都可以维空
        if (empty($name) || !is_array($adminInfo)) {
            return array('code'=>1030, 'data'=>1);
        }
        $d = D('TotalTagsThems');
        //判断是否已经删除
        $find = $d->where(array('id'=>$id, 'isdel'=>1, 'adminid'=>$adminInfo['id']))->find();
        if ($find){
            return array('code'=>102, 'data'=>1);
        }

        //查询name是否已经存在
        $sel = $d->where(array('themname'=>$name, 'id'=>array('neq', $id), 'isdel'=>0, 'adminid'=>$adminInfo['id']))->select();
        if ($sel){
            return array('code'=>1008);
        }

        $classArray = [];
        if (is_array($class) && count($class) > 0){
            foreach ($class as $key => $value) {
                if (!array_key_exists('id', $value) && !array_key_exists('icon', $value) && !array_key_exists('className', $value) && !array_key_exists('classInfo', $value)){
                    return ['code'=>1030, 'data'=>['code'=>'class2','data'=>$value]];
                    break;
                }elseif(!is_numeric($value['id']) || empty($value['icon']) || empty($value['className']) || empty($value['classInfo'])){
                    return ['code'=>1051, 'data'=>['code'=>'class1','data'=>$value]];
                    break;
                }else{
                    $classArray[] = array(
                        'id' => (int)$value['id'],
                        'icon' => $value['icon'],
                        'className' => $value['className'],
                        'classInfo'=>$value['classInfo']
                    );
                }
            }
        }

        //如果tags数组有值，则判断数组内的是否有tagid字段
        $tagsArray = [];
        if (is_array($tags) && count($tags) > 0) {
            $tags = array_unique($tags);
            //验证标签id是否存在
            $data = TagService::tagList($adminInfo, false);
            $tagids = array_column($data['data'], 'id');//取出所有的标签主键id

            foreach ($tags as $key => $value) {
                if (empty($value)){//没有值或字段
                    return array('code'=>1051, 'data'=>1);
                    break;
                }elseif (!in_array($value, $tagids)){//判断标签id是否在所有id内
                    return array('code'=>1051, 'data'=>2);
                    break;
                }else{
                    $tagsArray[] = array(
                        'tagid'=>$value,//防止有多余的数据，重新赋值给新数组
                        'adminid'=>$adminInfo['id']
                    );
                }
            }
        }

        //如果有banner数据，判断是否有banner图片、跳转链接、顺序
        $bannersArray = [];
        if (is_array($banners) && count($banners) > 0 ) {
            foreach ($banners as $key => $value) {
                if (empty($value['imgurl']) || empty($value['imgredirect']) || empty($value['order'])){
                    return array('code'=>1051, 'data'=>2);
                    break;
                }else{
                    $bannersArray[] = array(
                        'imgurl' => $value['imgurl'],
                        'imgredirect' => $value['imgredirect'],
                        'order' => $value['order'],
                        'adminid'=>$adminInfo['id']
                    );
                }
            }
        }
        $data = array(
            'id'=>$id,
            'themname'=>$name,
            'icon'=>$icon,
            'classes'=>json_encode($classArray),
//            'adminid'=>$adminInfo['id'],
            'tags'=>$tagsArray,
            'banners'=>$bannersArray
        );

        $themestage = D('TotalTagsThemsTags');//关联的标签
        $themebanner = D('TotalTagsThemsBanners');
        $themestage->where(array('themsid'=>$id, 'adminid'=>$adminInfo['id']))->delete();
        $themebanner->where(array('themsid'=>$id, 'adminid'=>$adminInfo['id']))->delete();

        $d = D('TotalTagsThems');
        $add = $d->relation(true)->save($data);
//        echo $d->_sql();dump($add);
        if ($add !== false) {
            return array('code'=>200);
        }else{
            return array('code'=>104);
        }



    }


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
            foreach ($sel as $key => $val) {
                $sel[$key]['classes'] = json_decode($val['classes'], true);
            }
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


    /**
     * 主题删除
     * @param array $adminInfo
     * @param array $ids
     * @return array
     */
    public static function ThemeDestroy(array $adminInfo, array $ids)
    {
        $d = D('TotalTagsThems');
        $where = array('adminid'=>$adminInfo['id'], 'id'=>array('in', $ids));
        $del = $d->where($where)->save(array('isdel'=>1));
        if ($del != false){ //删除0个也算失败
            return array('code'=>200);
        }else{
            return array('code'=>104, 'data'=>1);
        }
    }


    /**
     * 主题排序
     * @param $id 主键id
     * @param $type 升或降：up or down
     * @return array
     */
    public static function ThemSort($id, $type, $adminInfo)
    {
        $d = D('TotalTagsThems');
        $find = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->find();
        if (!$find){
            return array('code'=>102, 'data'=>1);
        }

        if ($type == 'up'){
            if ($find['sort'] == 1 || $find['sort'] < 1){//如果之前的排序本来就是最高的或因为某些因素导致数据错误，则修改为正确数据
                $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>1));
                return array('code'=>200, 'data'=>1);
            }

            $findone = $d->where(array('sort'=>array('lt', $find['sort']), 'adminid'=>$adminInfo['id'], 'isdel'=>array('eq', 0)))->order('sort desc')->find();//查询，比要升序的这个id排序大的数据，有则将其改为id的排序，没有则只管将id的排序加一
            if ($findone) {
                //交换两者的排序
                $save = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>$findone['sort']));
                $save = $d->where(array('id'=>$findone['id'], 'adminid'=>$adminInfo['id']))->save(array('sort'=>$find['sort']));
            }else{
                $save = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>1));//没有比他数字小的了
            }

            if ($save) {
                return array('code'=>200, 'data'=>2);
            }else{
                return array('code'=>104, 'data'=>1);
            }

        }else if ($type == 'down'){
            $findone = $d->where(array('sort'=>array('gt', $find['sort']), 'adminid'=>$adminInfo['id'], 'isdel'=>array('eq', 0)))->order('sort asc')->find();//查询，比要升序的这个id排序大的数据，有则将其改为id的排序，没有则只管将id的排序加一
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

















    /***********************************************************优美的分割线**********************************************************/
    /**
     * 第二部分：
     * 分组管理
     */

    /**
     * 新增分组
     * @param $name 分组名字
     * @param array $tags 传入的标签数组
     * @param string $icon 传入的标签icon
     * @param $adminInfo 商户详细信息
     * @return array
     */
    public static function GroupStore($name, $tags, $maxselect=0, $adminInfo)
    {
        //除了名字，其它都可以维空
        if (empty($name) || !is_array($adminInfo)) {
            return array('code'=>1030, 'data'=>1);
        }
        $d = D('TotalTagsGroups');
        //查询name是否已经存在
        $sel = $d->where(array('groupname'=>$name, 'isdel'=>0, 'adminid'=>$adminInfo['id']))->select();
        if ($sel){
            return array('code'=>1008);
        }

        //如果tags数组有值，则判断数组内的是否有tagid字段
        $tagsArray = [];
        if (is_array($tags) && count($tags) > 0) {
            $tags = array_unique($tags);
            //验证标签id是否存在
            $data = TagService::tagList($adminInfo, false);
            $tagids = array_column($data['data'], 'id');//取出所有的标签主键id

            foreach ($tags as $key => $value) {
                if (empty($value)){//没有值或字段
                    return array('code'=>1051, 'data'=>1);
                    break;
                }elseif (!in_array($value, $tagids)){//判断标签id是否在所有id内
                    return array('code'=>1051, 'data'=>2);
                    break;
                }else{
                    $tagsArray[] = array(
                        'tagid'=>$value,//防止有多余的数据，重新赋值给新数组
                        'adminid'=>$adminInfo['id']
                    );
                }
            }
        }
        $find = $d->where(array('isdel'=>0, 'adminid'=>$adminInfo['id']))->max('sort');
        //dump($find);die;
        $data = array(
            'groupname'=>$name,
            'maxselect'=>$maxselect,
            'adminid'=>$adminInfo['id'],
            'tags'=>$tagsArray,
            'sort'=>$find + 1,
            'createtime'=>date('Y-m-d H:i:s')
        );
//        $d = D('TotalTagsThems');
        $add = $d->relation(true)->add($data);
        if ($add) {
            return array('code'=>200);
        }else{
            return array('code'=>104);
        }



    }


    /**
     * 修改分组
     * @param $id
     * @param $name
     * @param $tags
     * @param string $icon
     * @param $banners
     * @param $adminInfo
     * @return array
     */
    public static function GroupUpdate($id, $name, $tags, $maxselect=0, $adminInfo)
    {
        //除了名字，其它都可以维空
        if (empty($id) || empty($name) || !is_array($adminInfo)) {
            return array('code'=>1030, 'data'=>1);
        }
        $d = D('TotalTagsGroups');
        //判断是否已经删除
        $find = $d->where(array('id'=>$id, 'isdel'=>1, 'adminid'=>$adminInfo['id']))->find();
        if ($find){
            return array('code'=>102, 'data'=>1);
        }


        //查询name是否已经存在
        $sel = $d->where(array('groupname'=>$name, 'id'=>array('neq', $id), 'isdel'=>0, 'adminid'=>$adminInfo['id']))->select();
        if ($sel){
            return array('code'=>1008);
        }


        //如果tags数组有值，则判断数组内的是否有tagid字段
        $tagsArray = [];
        if (is_array($tags) && count($tags) > 0) {
            $tags = array_unique($tags);
            //验证标签id是否存在
            $data = TagService::tagList($adminInfo, false);
            $tagids = array_column($data['data'], 'id');//取出所有的标签主键id

            foreach ($tags as $key => $value) {
                if (empty($value)){//没有值或字段
                    return array('code'=>1051, 'data'=>1);
                    break;
                }elseif (!in_array($value, $tagids)){//判断标签id是否在所有id内
                    return array('code'=>1051, 'data'=>2);
                    break;
                }else{
                    $tagsArray[] = array(
                        'tagid'=>$value,//防止有多余的数据，重新赋值给新数组
                        'adminid'=>$adminInfo['id']
                    );
                }
            }
        }
        $data = array(
            'id'=>$id,
            'groupname'=>$name,
            'maxselect'=>$maxselect,
            'tags'=>$tagsArray,
        );
        $themestage = D('TotalTagsGroupsTags');//关联的标签
        $themestage->where(array('groupid'=>$id))->delete();

        $add = $d->relation(true)->save($data);
        if ($add !== false) {
            return array('code'=>200);
        }else{
            return array('code'=>104);
        }



    }


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
     * 分组详情
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


    /**
     * 分组删除
     * @param $adminInfo
     * @param array $ids
     * @return array
     */
    public static function GroupDestroy($adminInfo, array $ids)
    {
        if (!is_array($adminInfo) || !is_array($ids)) {
            return array('code'=>1051, 'data'=>1);
        }
        $d = D('TotalTagsGroups');
        $where = array('adminid'=>$adminInfo['id'], 'id'=>array('in', $ids));
        $del = $d->where($where)->save(array('isdel'=>1));
        if ($del != false){ //删除0个也算失败
            return array('code'=>200);
        }else{
            return array('code'=>104, 'data'=>1);
        }
    }




    /**
     * 主题排序
     * @param $id 主键id
     * @param $type 升或降：up or down
     * @return array
     */
    public static function GroupSort($id, $type, $adminInfo)
    {
        $d = D('TotalTagsGroups');
        $find = $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id'], 'isdel'=>0))->find();
        if (!$find){
            return array('code'=>102, 'data'=>1);
        }

        if ($type == 'up'){
            if ($find['sort'] <= 1){//如果之前的排序本来就是最高的或因为某些因素导致数据错误，则修改为正确数据
                $d->where(array('id'=>$id, 'adminid'=>$adminInfo['id']))->save(array('sort'=>1));
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
                $save = $d->where(array('id'=>$id))->save(array('sort'=>$findone['sort']));
                $save = $d->where(array('id'=>$findone['id']))->save(array('sort'=>$find['sort']));
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