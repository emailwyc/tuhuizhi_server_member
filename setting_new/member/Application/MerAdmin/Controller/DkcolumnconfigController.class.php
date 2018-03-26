<?php
/**
 * 商户配置类
 * User: jaleel
 * Date: 7/07/16
 * Time: 3:33 PM
 */

namespace MerAdmin\Controller;

class DkcolumnconfigController extends AuthController
{

//     private $admin_arr;
	public function _initialize(){
		parent::_initialize();
// 		//查询商户信息
// 		$this->admin_arr=$this->getMerchant($this->ukey);
	}
    /**
     * C端会员首页九宫格设置
     */
    public function SetSquared()
    {
        $params['key_admin']=I('key_admin');
        $params['title']=I('title');
        $params['logo']=I('logo');

        $id=I('sid');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }

        $params['isverify']=I('isverify');
//         $params['isopenedactivity']=I('isopenedactivity');
        $params['url']=I('url');//C端跳转地址
        $params['column_id']=I('column_id');//C端跳转地址
		$params['catalog_id']=$this->params['catalog_id']?$this->params['catalog_id']:1;//C端跳转地址
        $params['order']=(int)I('order');
		$params['postion']=!empty($this->params['postion'])?(int)$this->params['postion']:1;//C端跳转地址
		if(isset($this->params['istwolevel'])){
			$params['istwolevel']=(int)$this->params['istwolevel']==1?1:0;//是否在二级菜单显示
		}
        $admin=$this->getMerchant($params['key_admin']);

        //判断表是否被创建
        $this->CheckSquared($admin);

        $db=M('squared', $admin['pre_table']);
        $params['time']=time();
        $params['content']=I('content');
        //数据表中没有key_admin，移除
        unset($params['key_admin']);
        //如果没有id，则是新添加
        if (empty($id)){
            /*$numCheck = $db->where(array('order'=>$params['order']))->find();
            if($numCheck){
                returnjson(array('code'=>4009), $this->returnstyle, $this->callback);
                exit;
            }*/
            $save=$db->add($params);
        }else{
            //如果传入了id，则是修改之前的数据
            $find=$db->where(array('id'=>$id))->find();
            if (empty($find)){
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);exit;
			}
			$params['isopenedactivity']=1;
			$save=$db->where(array('id'=>$id))->save($params);
        }
        if ($save !== false){
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     *  获取配置后的子栏目管理list
     */
    public function get_column_list(){
        $params['key_admin']=I('key_admin');
        $catalog_id=I('catalog_id');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admin=$this->getMerchant($params['key_admin']);
        /*   获取绑定的版本id   */
        $catalog_id= empty($catalog_id) ? 1 : $catalog_id;
        $db=M('version_url','total_');
        $wheres['adminid']=array('eq',$admin['id']);
        $wheres['type_id']=array('eq',$catalog_id);
        $wheres['_logic']='and';
        $version_arr=$db->where($wheres)->find();
        /*   获取绑定的版本id   */
        $version_column_db=M('version_column','total_');
        $sub_column_db=M('sub_column','total_');
        $map['status']=array('eq',1);
        $map['total_version_column.catalog_id']=array('eq',$catalog_id);
        $map['version_id']=array('eq',$version_arr['version_id']);
        $map['_logic']='and';
        $arr=$sub_column_db->join('total_version_column on total_sub_column.id=total_version_column.column_id')->where($map)->field('total_sub_column.id,total_sub_column.name')->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }


    /**
     * B端页面获取C端九宫格list
     */
    public function GetSquaredList()
    {
        $key_admin=I('key_admin');
        $catalog_id=I('catalog_id');
        if (empty($key_admin)){
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $admin=$this->getMerchant($key_admin);

        //判断表是否被创建
        $this->CheckSquared($admin);
        //实例化表
        $catalog_id = empty($catalog_id) ? 1 : $catalog_id;
        $db=M('squared', $admin['pre_table']);
        $where['catalog_id']=array('eq',$catalog_id);
        $sel=$db->where($where)->order(array('order','id'))->select();
        if (0 == count($sel)){
            $msg['code']=102;
        }else{
            foreach($sel as $k=>$v){
               if($v['url']==''){
                    $columnid[]=$v['column_id'];
               }
            }
            if($columnid){
                $column_str=implode(',', $columnid);
                /*   获取绑定的版本id   */
                $db=M('version_url','total_');
                $wheres['adminid']=array('eq',$admin['id']);
                $wheres['type_id']=array('eq',$catalog_id);
                $wheres['_logic']='and';
                $version_arr=$db->where($wheres)->find();
                /*   获取绑定的版本id   */
                $version_column_db=M('version_column','total_');
                $sub_column_db=M('sub_column','total_');
                $map['status']=array('eq',1);
                $map['total_version_column.catalog_id']=array('eq',$catalog_id);
                $map['version_id']=array('eq',$version_arr['version_id']);
                $map['column_id']=array('in',$column_str);
                $map['_logic']='and';
                $arr=$sub_column_db->join('total_version_column on total_sub_column.id=total_version_column.column_id')->where($map)->field('total_sub_column.id,total_version_column.url')->select();

                foreach($arr as $key=>$val){
                    $column_arr[$val['id']]=$val['url'];
                }

                foreach($sel as $k=>$v){
                    if($v['url']==''){
                        if($column_arr[$v['column_id']]){
                            $sel[$k]['url']=$column_arr[$v['column_id']];
                        }else{
                            $sel[$k]['url']='';
                        }
                    }
                }
            }

            $msg['code']=200;
            $msg['data']=$sel;
        }
        returnjson($msg, $this->returnstyle, $this->callback);

    }

    /**
     * B端页面获取C端九宫格所有已有排序数子
     */
    public function GetSquaredOrderNum()
    {
        $key_admin=I('key_admin');
        $catalog_id = I('catalog_id');

        if (empty($key_admin)){
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $admin=$this->getMerchant($key_admin);

        $catalog_id = empty($catalog_id) ? 1 : $catalog_id;
        $column_ids = $this->getSubColumn($catalog_id);

        //实例化表
        $db=M('squared', $admin['pre_table']);

        if (is_array($column_ids) && count($column_ids) > 0) {
            $where['column_id'] = array('in', $column_ids);
            $sel=$db->where($where)->select();
        } else {
            $sel=false;
        }

        $num = array();
        if($sel){
            foreach($sel as $v){
                $num[] = $v['order'];
            }
        }
        returnjson(array('code'=>200, 'data'=>$num), $this->returnstyle, $this->callback);
    }

    public function getSubColumn($type) {
        $obj = M('total_sub_column');
        $where['catalog_id'] = $type;
        $result = $obj->where($where)->select();

        if ($result) {
            if (is_array($result)) {
                foreach ($result as $v) {
                    $data[] = $v['id'];
                }
            }

            return $data;
        }

        return false;
    }

    /**
     * B端九宫格删除
     */
    public function DelSquared()
    {
        $params['key_admin']=I('key_admin');
        $params['id']=I('sid');
        //判断id是否传入
        if(in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admin=$this->getMerchant($params['key_admin']);
        $db=M('squared', $admin['pre_table']);
        $del=$db->where(array('id'=>$params['id']))->delete();
        if (false !== $del){
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }

    }


    private function CheckSquared($admin)
    {
        $dbm=M();
        $c=$dbm->execute('SHOW TABLES like "'.$admin['pre_table'].'squared"');
        if (1 !==$c){//没有，则自动创建表
            $sql="CREATE TABLE IF NOT EXISTS `".$admin['pre_table']."squared` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `url` varchar(500) DEFAULT '' COMMENT '跳转url地址',
	  `logo` varchar(200) DEFAULT '' COMMENT '图片地址',
	  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
	  `isverify` smallint(1) NOT NULL DEFAULT 1 COMMENT '是否需要验证',
	  `time` bigint(15) NOT NULL COMMENT '时间戳',
	  `content` varchar(50) NOT NULL DEFAULT '' COMMENT '时间戳',
	  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
	  `isopenedactivity` smallint(1) NOT NULL DEFAULT 1 COMMENT '活动是否开启',
      `column_id` int(11) DEFAULT NULL COMMENT '子栏目id',
	  `catalog_id` int(11) NOT NULL COMMENT '类别id',
	  `postion` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:底部2：顶部',
	  `istwolevel` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是二级菜单1:是0：否',
	  PRIMARY KEY (`id`),
	  KEY `order` (`order`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
            $return=$dbm->execute($sql);
            if (0 !== $return){
                returnjson(array('code'=>102), $this->returnstyle, $this->callback);//如果没有表，则直接返回没有结果
            }
        }
    }


    public function GetShow()
    {
        // 	    echo json_encode(array(array('id'=>1,'name'=>'列表'),array('id'=>2,'name'=>'九宫格')));
        $key_admin=I('key_admin');
        if (!$key_admin){
            returnjson(array('code'=>1001), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($key_admin);
        $getsquaredlist=$this->GetOneAmindefault($admininfo['pre_table'], $key_admin, 'squaredlist');
        if (false != $getsquaredlist){
            $id=(int)$getsquaredlist['function_name'];
        }else{
            $id=false;
        }
        $db=M('default_type', 'total_');
        $sel=$db->where(array('default'=>'squaredlist'))->find();
        $list['list']=json_decode($sel['description'], true);
        $list['myid']=$id;
        returnjson(array('code'=>200, 'data'=>$list), $this->returnstyle, $this->callback);
    }


    //设置C端显示端样式，九宫格还是列表
    public function SetShow()
    {
        $params['key_admin']=I('key_admin');
        $params['type']=I('type');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        //查询样式中有没有此项
        $db=M('default_type', 'total_');
        $sel=$db->where(array('default'=>'squaredlist'))->find();
        $ids=array_column(json_decode($sel['description'], true), 'id');
        if (!in_array($params['type'], $ids)){//如果没有此项
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }


        $admin=$this->getMerchant($params['key_admin']);
        $defaultdb=M('default', $admin['pre_table']);
        $find=$defaultdb->where(array('customer_name'=>'squaredlist'))->find();
        if (null != $find){
            $save=$defaultdb->where(array('customer_name'=>'squaredlist'))->save(array('function_name'=>$params['type'],'description'=>'C端功能模块显示样式'));
        }else {
            $save=$defaultdb->add(array('customer_name'=>'squaredlist','function_name'=>$params['type'],'description'=>'C端功能模块显示样式'));
        }
        if ($save !== false){
            $this->redis->del('admin:default:one:squaredlist:'.$params['key_admin']);//删除redis缓存
            returnjson(array('code'=>200), $this->returnstyle, $this->callback);
        }else {
            returnjson(array('code'=>104), $this->returnstyle, $this->callback);
        }
    }


    /**
     * 根据id，获取单个九宫格样式
     */
    public function GetOneSquared()
    {
        $params['id']=I('sid');
        $params['key_admin']=I('key_admin');
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $db=M('squared', $admininfo['pre_table']);
        $find=$db->where(array('id'=>$params['id']))->find();
        if (null != $find){
            $find['url']=html_entity_decode($find['url']);
            returnjson(array('code'=>200, 'data'=>$find), $this->returnstyle, $this->callback);
        }else{
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }

    }
}
