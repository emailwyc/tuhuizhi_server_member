<?php
/**
 * 商户配置类
 * User: jaleel
 * Date: 7/07/16
 * Time: 3:33 PM
 */

namespace MerAdmin\Controller;

class ConfigureController extends AuthController
{

	public function _initialize(){

		parent::_initialize();
// 		//查询商户信息
// 		$admin_arr=$this->getMerchant($this->ukey);

// 		//key_admin是否正确
// 		if(empty($admin_arr)){
// 			echo returnjson(array('code'=>1001),$this->returnstyle,$this->callback);exit();
// 		}
		
// 		//判断商户是否具有权限
// 		$ret=$this->Auth_Admin($admin_arr['id']);
		
// 		if(!$ret){
// 			echo returnjson(array('code'=>5002,'msg'=>'权限不足'),$this->returnstyle,$this->callback);exit();	
// 		}
		
// 		//判断用户是否登录超时
// 		$get=$_SESSION['admin_name'];

// 		if(empty($get)){
// 			echo returnjson(array('code'=>502),$this->returnstyle,$this->callback);exit();
// 		}
	}


    /**
     * 静态页面的添加及更新
     * 会员权益的添加及更新
     * 联系我们的添加及更新
     * 普通页面的添加及更新
     */
    public function staticpage() {
        $tid = I('tid'); // 1为会员权益 2为联系我们 3为商户公众号二维码 4为普通页面 5为会员卡样
        $title = I('title');
        $content = I('content');
        $sid = I('sid');

        //参数为空验证
        if (!$tid or !$title or !$content) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        writeOperationLog(array('key_admin' => json_encode($this->ukey)), 'jaleel_logs');

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        writeOperationLog(array('get merchant' => json_encode($mer_re)), 'jaleel_logs');
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $static = M('total_static');

        if (isset($sid)) {
            $page_info = $static->where(array('id' => $sid))->find();
        } else if ($tid != 4) {
            $page_info = $static->where(array('tid' => $tid, 'admin_id' => $mer_re['id']))->find();
        }


        // 有数据则更新 没有则添加
        $data = array(
            'tid' => $tid,
            'admin_id' => $mer_re['id'],
            'title'     => $title,
            'content'   => $content,
        );
        if ($page_info) {
            $db_up = $static->where(array('id' => $page_info['id']))->save($data);
            writeOperationLog(array('修改tid为' . $tid . '的结果为' => $db_up), 'jaleel_logs');
            $sid = $page_info['id'];
        } else {
            $db_re = $static->add($data);
            writeOperationLog(array('添加tid为' . $tid . '的结果为' => $db_re), 'jaleel_logs');
            $sid = $db_re;
        }

        if ($tid == 1) { // 会员权益或者联系我们
            $url = "https://mem.rtmap.com/Member/Member/memberRight?key_admin=" . $this->ukey;
        } else if ($tid == 2) {
            $url = "https://mem.rtmap.com/Member/Member/merchantContact?key_admin=" . $this->ukey;
        } else if ($tid == 3) {
            $url = I('qrurl');
        } else if ($tid == 4) { // 普通静态页面
            $url = "https://h5.rtmap.com/resconf/?sid={$sid}&key_admin={$this->ukey}";
        }

        $static->where(array('id' => $sid))->save(array('url' => $url));

        // 操作数据库失败
        if ($db_re === false or $db_up === false) {
            $return_data = array('code' => '1011', 'msg' => 'system error!');
            returnjson($return_data, $this->returnstyle, $this->callback);
        }

        $return_data = array('code' => '200', 'msg' => 'success', 'data' => array('url' => $url));
        returnjson($return_data, $this->returnstyle, $this->callback);
    }

    /**
     * 按tid类别ID查询客户配置相关列表
     * tid的取值如下:
     * 1为会员权益
     * 2为联系我们
     * 3为商户公众号二维码
     * 4为普通静态页面
     * 5为会员卡样
     */
    public function staticpagedetails() {
        $tid = I('tid'); // 1为会员权益 2为联系我们 3为商户公众号二维码 4为普通静态页面 5为会员卡样
        $sid = I('sid');

        //参数为空验证
        if (!$tid) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        $page_info = $this->getstaticpage($tid, $mer_re['id'], $sid);
        
        $page_info['content'] = html_entity_decode($page_info['content']);

        if($tid == 5){
            $page_info['content']=json_decode($page_info['content'],true);
            $page_info['url']=json_decode($page_info['url'],true);

            if(count($page_info['content'])>=1){
                foreach($page_info['content'] as $k=>$v){
                    $arr[$k]['cardtypeno']=$k;
                    $arr[$k]['cardtypename']=$v;
                    $arr[$k]['cardtypeurl']=$page_info['url'][$k];
                }
                $page_info['content']=$arr;
            }
        }
        
        $return_data = array('code' => '200', 'msg' => 'success', 'data' => $page_info);
        returnjson($return_data, $this->returnstyle, $this->callback);
    }

    /**
     * @param $tid
     * @param $mid
     * @param string $sid
     * @return mixed
     */
    protected function getstaticpage($tid, $mid, $sid = '') {
        $static = M('total_static');

        if (empty($sid)) {
            $page_info = $static->where(array('tid' => $tid, 'admin_id' => $mid))->find();
        } else {
            $page_info = $static->where(array('id' => $sid, 'tid' => $tid, 'admin_id' => $mid))->find();
        }
        return $page_info;
    }

    /**
     * 查询普通静态页面列表
     * @return mixed
     */
    public function getStaticList() {

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);

        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $static = M('total_static');
        $list = $static->where(array('tid' => 4, 'admin_id' => $mer_re['id']))->select();

        $return_data = array('code' => '200', 'msg' => 'success', 'data' => $list);
        returnjson($return_data, $this->returnstyle, $this->callback);
    }

    /**
     * 删除普通静态页面
     */
    public function deleteStaticPage() {
        $sid = I('sid');

        if (!$sid) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $static = M('total_static');
        $re = $static->where(array('id' => $sid))->delete();

        if ($re === false) {
            $return_data = array('code' => '1011', 'msg' => 'system error');
        } else {
            $return_data = array('code' => '200', 'msg' => 'success');
        }
        returnjson($return_data, $this->returnstyle, $this->callback);
    }

    /**
     * 添加卡样
     */
    public function addvipcardtpl() {
        $ctno = I('cardtypeno');
        $ctname = I('cardtypename');
        $cturl = I('cardtypeurl');

        //参数为空验证
        if (!$ctno or !$ctname or !$cturl) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);

        $static = M('total_static');

        // 判断之前是否有添加过其他的卡样 添加过将新的卡样push到json串中 重复添加则进行提示
        $old_info = $this->getstaticpage(5, $mer_re['id']);
        if (is_array($old_info) && $old_info['content'] != '' && $old_info['url'] != '') {
            $content = json_decode($old_info['content'], true);
            $url = json_decode($old_info['url'], true);
//            if (array_key_exists($ctno, $content) && array_key_exists($ctno, $url)) {
//
//                // 卡编码已经存在
//                $data = array('code' => '5050', 'msg' => 'the card code is already exists!');
//                returnjson($data, $this->returnstyle, $this->callback);
//            }

            $content[$ctno] = $ctname;
            $url[$ctno] = $cturl;

            // 更新记录
            $up_re = $static->where(array('tid' => 5, 'admin_id' => $mer_re['id']))->save(array('content' => json_encode($content), 'url' => json_encode($url)));

            // 更新失败
            if (!$up_re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        } else {
            $content = array();
            $url = array();

            $content[$ctno] = $ctname;
            $url[$ctno] = $cturl;

            $save_data['tid'] = 5;
            $save_data['admin_id'] = $mer_re['id'];
            $save_data['title'] = '会员卡样';
            $save_data['content'] = json_encode($content);
            $save_data['url'] = json_encode($url);
            $save_data['des'] = '会员卡样';

            // 添加记录
            $add_re = $static->add($save_data);

            // 添加失败
            if (!$add_re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 修改和删除卡样接口
     */
    public function savevipcardtpl(){
        $ctno = I('cardtypeno');
        $status=I('status');
    
        //参数为空验证
        if (!$ctno or !$status) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
    
        $ctname = I('cardtypename');
        $cturl = I('cardtypeurl');
        $msg=$this->savevipcard_action($ctno,$ctname ,$cturl,strtoupper($status));
    
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    
    public function savevipcard_action($ctno , $ctname = '' , $cturl = '' ,$status){
        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
    
        $static = M('total_static');
    
        // 判断之前是否有添加过其他的卡样 添加过将新的卡样push到json串中 重复添加则进行提示
        $old_info = $this->getstaticpage(5, $mer_re['id']);
        if($old_info){
    
            $content=json_decode($old_info['content'],true);
            $url=json_decode($old_info['url'],true);
            if($status == 'F'){
                unset($content[$ctno]);
                unset($url[$ctno]);
            }else{
                $content[$ctno]=$ctname;
                $url[$ctno]=$url;
            }
    
            $res=$static->where(array('tid' => 5, 'admin_id' => $mer_re['id']))->save(array('content' => json_encode($content), 'url' => json_encode($url)));
    
            if($res === false){
                $msg['code']=104;
            }else{
                $msg['code']=200;
            }
    
        }else{
            $msg['code']=102;
        }
        return $msg;
    }
    
    
    public function addwxqrcode() {
        $codeurl = I('codeurl');

        //参数为空验证
        if (!$codeurl) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $static = M('total_static');
        $page_info = $static->where(array('tid' => 3, 'admin_id' => $mer_re['id']))->find();


    }

    /**
     * 上传文件接口
     */
    public function uploadfile(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Public/member/img/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录

        // 上传文件
        $info = $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $data = array('code' => '3333', 'msg' => $upload->getError());
            writeOperationLog(array('上传文件错误信息' => $upload->getError()), 'jaleel_logs');
            returnjson($data, $this->returnstyle, $this->callback);
        }else{// 上传成功
            $data = array('code' => '200', 'msg' => 'SUCCESS', 'data' => array('url' => 'https://mem.rtmap.com/Public/member/img/' . $info['savename']));
            returnjson($data, $this->returnstyle, $this->callback);
        }
    }

    /**
     * 查询商户的注册页面表单配置
     */
    public function getRegForm() {
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户配置
        $mer_re = $this->getMerchant($this->ukey);
        if (!$mer_re) {
            $data = array('code' => '1001', 'msg' => 'invalid ukey!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $form = M('total_static');
        $form_data = $form->where(array('admin_id' => $mer_re['id'], 'tid' => 6))->find();

        if (!$form_data) {
            $data = array('code' => '1011', 'msg' => 'no data!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $form_data);
        returnjson($data, $this->returnstyle, $this->callback);
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
        $params['isopenedactivity']=I('isopenedactivity');
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


    /**
     * 获取C端生日、身份证号是否只能修改一次
     */
    public function GetChangeConfig()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $find=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'birthday&idcardonly');
        
        //获取注册成功后跳转链接URL
        $find_url=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'createsuccessurl');
        
        if ($find !='' ){
            if($find_url){
                $msg=array('code'=>200, 'data'=>array('id_birth'=>$find['function_name'],'url'=>$find_url['function_name']));
            }else{
                $msg=array('code'=>200, 'data'=>$find['function_name']);
            }
        }else{
            $msg=array('code'=>200, 'data'=>0);
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }



    /**
     * 设置C端生日、身份证号是否只能修改一次
     */
    public function SetChangeConfig()
    {
        $params['key_admin']=$this->ukey;
        $params['value']=html_entity_decode(I('setting'));
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $db=M('default', $admininfo['pre_table']);
        $find=$db->where(array('customer_name'=>'birthday&idcardonly'))->find();
        if ($find){
            $save=$db->where(array('customer_name'=>'birthday&idcardonly'))->save(array('function_name'=>$params['value']));
        }else{
            $save=$db->add(array('customer_name'=>'birthday&idcardonly','function_name'=>$params['value'], 'description'=>'设置C端身份证号号生日只能修改一次'));
        }
        
        $url=I('url')?I('url'):'';
        if($url != ''){
            $find_url=$db->where(array('customer_name'=>'createsuccessurl'))->find();
            if ($find_url){
                $save_url=$db->where(array('customer_name'=>'createsuccessurl'))->save(array('function_name'=>$url));
            }else{
                $save_url=$db->add(array('customer_name'=>'createsuccessurl','function_name'=>$url, 'description'=>'设置注册成功之后跳转地址'));
            }
            $this->redis->del('admin:default:one:createsuccessurl:'. $params['key_admin']);//删除redis缓存
        }
        
        $this->redis->del('admin:default:one:birthday&idcardonly:'. $params['key_admin']);//删除redis缓存
        if ($save !== false){
            if($url != ''){
                if($save_url !== false){
                    $msg=array('code'=>200);
                }else{
                    $msg=array('code'=>104,'msg'=>'跳转地址设置失败');
                }
            }
            $msg=array('code'=>200);
        }else{
            $msg=array('code'=>104);
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }



    /**
     * 自动表单项列表
     */
    public function FormList()
    {
        $type=I('type');
        if ('' == $type){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $dbnull=M();
        $admininfo=$this->getMerchant($this->ukey);
        $c=$dbnull->execute('SHOW TABLES like "'.$admininfo['pre_table'].'auto_form"');
        if (1 === $c){
            $create = 0;
        }else{
            $sql="CREATE TABLE `".$admininfo['pre_table']."auto_form` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `isenable` smallint(1) NOT NULL DEFAULT '1' COMMENT '1开启，0不开启，默认开启',
  `ischange` smallint(1) NOT NULL DEFAULT '0' COMMENT '1开启，0不开启，默认不开启',
  `form_key_id` int(10) NOT NULL COMMENT '表单id',
  `isrequired` smallint(1) NOT NULL DEFAULT '1' COMMENT '是否必填，1是，0否',
  `placeholder` varchar(100) NOT NULL DEFAULT '' COMMENT 'input内的提示内容',
  `sub` varchar(100) NOT NULL DEFAULT '' COMMENT '字段后面的提示',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '默认值，对所有字段类型全部符合',
  `minlength` varchar(10) NOT NULL DEFAULT '1' COMMENT '输入框最短多少位,转int',
  `maxlength` varchar(50) NOT NULL DEFAULT '200' COMMENT '输入框最多多少位，转int',
  `sort` smallint(20) NOT NULL DEFAULT '1' COMMENT '排序',
  `type` smallint(1) NOT NULL DEFAULT '1' COMMENT '注册或是修改表单，1注册，0修改',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;CREATE TABLE `".$admininfo['pre_table']."form_default` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `form_id` int(100) NOT NULL COMMENT '属于哪一个form字段',
  `default_content` varchar(100) DEFAULT '' COMMENT '默认',
  `default_content_key` varchar(10) DEFAULT '' COMMENT '默认值的key，可空',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
            $create=$dbnull->execute($sql);
//            returnjson(array('code' => 102, 'data' => array('form' => $sel, 'mylist' => $selbussiness)), $this->returnstyle, $this->callback);
        }
        $db = M('total_form');
        $sel = $db->select();
        $dbdefault = M('total_form_default');
        foreach ($sel as $item => $value) {
            $seldefault = $dbdefault->where(array('form_id' => $value['id']))->select();
            if ($seldefault != null) {
                $sel[$item]['content_default'] = $seldefault;
            }
        }
        $ids=array_column($sel, 'id');
        $admininfo = $this->getMerchant($this->ukey);
        $dbbussiness = M('auto_form', $admininfo['pre_table']);
        $selbussiness = $dbbussiness->where(array('isenable' => 1,'type'=>$type,'form_key_id'=>array('in', $ids)))->select();
        returnjson(array('code' => 200, 'data' => array('form' => $sel, 'mylist' => $selbussiness)), $this->returnstyle, $this->callback);
    }
    /**
     * 签到设置
     */
    public function GetSignSetting()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $info=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'scorenum');
        if (false == $info){
            returnjson(array('code'=>102), $this->returnstyle, $this->callback);
        }
        $scorep=json_decode($info['function_name'], true);//值为一维数组格式:[0.1,1]
        if (false == $scorep){//如果json解析成功
            $msg['code']=1000;
        }else {
            $msg=array('code'=>200, 'data'=>array('mix'=>$scorep[0], 'max'=>$scorep[1]));
        }
        returnjson($msg, $this->returnstyle, $this->callback);
	}

    /**
     * 签到图片设置
     */
    public function GetSignSettingImg()
    {
		$info=$this->GetOneAmindefault($this->admin_arr['pre_table'], $this->ukey, 'signimg');
		if (false == $info){
			$data = (object)array();
			returnjson(array('code'=>200,'data'=>$data), $this->returnstyle, $this->callback);
        }
        $scorep=json_decode($info['function_name'], true);//值为一维数组格式:[0.1,1]
        if (false == $scorep){//如果json解析成功
            $msg['code']=1000;
        }else {
            $msg=array('code'=>200, 'data'=>$scorep);
        }
        returnjson($msg, $this->returnstyle, $this->callback);
    }
    /**
     *设置商户自己的表单，点击一个表单项，弹出层，把所有字段填完后，提交本表单
     */
    public function SetMyAutoForm()
    {
        $params['form_key_id']=I('form_key_id');
        $params['type']=(int)I('type');
        if (in_array('', $params, true)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        $params['sort'] = I('sort') ? I('sort') : 1;
        $params['type']=(int)$params['type'];
        $params['isenable']=I('isenable');
        $params['ischange'] = I('ischange') ? 1 : 0;
        $params['isrequired']=I('isrequired');
        $params['placeholder']=I('placeholder');
        $params['sub']=I('sub');
        $params['value']=I('value');
        $params['minlength']=I('minlength');
        $params['maxlength']=I('maxlength');
        //转int
        $params['form_key_id']=(int)$params['form_key_id'];
        $params['isenable']=(int)$params['isenable'];
        $params['isrequired']='' != $params['isrequired'] ? (int)$params['isrequired'] : 1;
        $dbnull=M();
        $admininfo=$this->getMerchant($this->ukey);
        $c=$dbnull->execute('SHOW TABLES like "'.$admininfo['pre_table'].'auto_form"');
        if (1 === $c){
            $create = 0;
        }else{
            $sql="CREATE TABLE `".$admininfo['pre_table']."auto_form` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `isenable` smallint(1) NOT NULL DEFAULT '1' COMMENT '1开启，0不开启，默认开启',
  `ischange` smallint(1) NOT NULL DEFAULT '0' COMMENT '1开启，0不开启，默认不开启',
  `form_key_id` int(10) NOT NULL COMMENT '表单id',
  `isrequired` smallint(1) NOT NULL DEFAULT '1' COMMENT '是否必填，1是，0否',
  `placeholder` varchar(100) NOT NULL DEFAULT '' COMMENT 'input内的提示内容',
  `sub` varchar(100) NOT NULL DEFAULT '' COMMENT '字段后面的提示',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '默认值，对所有字段类型全部符合',
  `minlength` varchar(10) NOT NULL DEFAULT '1' COMMENT '输入框最短多少位,转int',
  `maxlength` varchar(50) NOT NULL DEFAULT '200' COMMENT '输入框最多多少位，转int',
  `type` smallint(1) NOT NULL DEFAULT '1' COMMENT '注册或是修改表单，1注册，0修改',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;";
            $create=$dbnull->execute($sql);
        }
        if ($create == 0){
            $db=M('auto_form', $admininfo['pre_table']);
            $find=$db->where(array('form_key_id'=>$params['form_key_id'], 'type'=>$params['type']))->find();
            if ($find == null){
                $save=$db->add($params);
            }else{
                $save=$db->where(array('id'=>$find['id']))->save($params);
            }
            if ($save !== false){
                returnjson(array('code'=>200), $this->returnstyle, $this->callback);
            }else{
                returnjson(array('code'=>104), $this->returnstyle, $this->callback);
            }
        }else{
            returnjson(array('code'=>101), $this->returnstyle, $this->callback);
        }

    }
    
    /**
     * 获取签到积分设置
     */
    public function SetSignScoreRule()
    {
        $params['mix']=(int)I('mixscore');
        $params['max']=(int)I('maxscore');
        $params['key_admin']=I('key_admin');
        //判断参数是否完整
        if (in_array('', $params)){
            returnjson(array('code'=>1030), $this->returnstyle, $this->callback);
        }
        //如果最小积分数大于最大积分数，返回错误
        if ($params['mix'] > $params['max']){
            returnjson(array('code'=>1051), $this->returnstyle, $this->callback);
        }
        $admininfo=$this->getMerchant($this->ukey);
        $info=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'scorenum');

        $db=M('default', $admininfo['pre_table']);
        $score=json_encode(array($params['mix'], $params['max']));
        if (false == $info){//如果之前没有，则执行添加操作
            $save=$db->add(array('customer_name'=>'scorenum', 'function_name'=>$score, 'description'=>'每次签到随机获取的积分范围'));
        }else{//否则，执行修改操作
            $save=$db->where(array('customer_name'=>'scorenum'))->save(array('function_name'=>$score));
		}
		//new signimg form soone
		$signimg = array();
		$signimg['sign'] = !empty($this->params['sign'])?$this->params['sign']:"";
		$signimg['unsign'] = !empty($this->params['unsign'])?$this->params['unsign']:"";
		$signimg['coin'] = !empty($this->params['coin'])&&$this->params['coin']=="coin"?"coin":"score";
		$info1=$this->GetOneAmindefault($admininfo['pre_table'], $this->ukey, 'signimg');
        $signimg=json_encode($signimg);
        if (false == $info1){//如果之前没有，则执行添加操作
            $db->add(array('customer_name'=>'signimg', 'function_name'=>$signimg, 'description'=>'签到图片设置'));
        }else{//否则，执行修改操作
            $db->where(array('customer_name'=>'signimg'))->save(array('function_name'=>$signimg));
		}
		$this->redis->del('admin:default:one:signimg:'. $params['key_admin']);//删除redis缓存
		//new signimg form soone
        if (false !== $save){
            $msg['code']=200;
        }else{
            $msg['code']=104;
        }
        $this->redis->del('admin:default:one:scorenum:'. $params['key_admin']);//删除redis缓存
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 增加或者修改一键WIFI相关配置
     */
    public function confwifi()
    {
        $txt = I('des');
        $img = I('img');
        $ip = I('ip');

        $data['customer_name'] = 'wifi';
        $data['function_name'] = json_encode(array('txt' => $txt, 'img' => $img, 'wifi_ip' => $ip));
        $data['description'] = '一键wifi';

        $merchant = $this->getMerchant($this->ukey);
        $obj = M('default', $merchant['pre_table']);
        $wifi_info = $obj->where(array('customer_name' => 'wifi'))->find();

        if (is_array($wifi_info)) {
            $result = $obj->where(array('customer_name' => 'wifi'))->save($data);
            $this->redis->delete('admin:default:one:wifi:' . $this->ukey);
        } else {
            $result = $obj->add($data);
        }

        if ($result === false) {
            $data = array('code' => 1011);
        } else {
            $data = array('code' => 200);
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询一键wifi相关配置
     */
    public function getwifi()
    {
        $merchant = $this->getMerchant($this->ukey);
        $wifi_info = $this->GetOneAmindefault($merchant['pre_table'], $this->ukey, 'wifi');

        if (is_array($wifi_info)) {
            $wifi_info['function_name'] = json_decode($wifi_info['function_name'], true);
            $data = array('code' => 200, 'msg' => 'success', 'data' => $wifi_info);
        } else {
            $data = array('code' => 200, 'msg' => 'success', 'data' => array());
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    public function confmemwifi()
    {
        $is_mem = I('is_mem');
        $merchant = $this->getMerchant($this->ukey);
        $obj = M('default', $merchant['pre_table']);
        $wifi_info = $obj->where(array('customer_name' => 'wifi'))->find();

        if (!is_array($wifi_info)) {
            $data = array('code' => 1011, 'msg' => 'please add the wifi config first!!!', 'data' => array());
        } else {
            $wifi_info['function_name'] = json_decode($wifi_info['function_name'], true);
            $wifi_info['function_name']['is_mem'] = $is_mem;
            $save['function_name'] = json_encode($wifi_info['function_name']);
            $result = $obj->where(array('customer_name' => 'wifi'))->save($save);
            $this->redis->delete('admin:default:one:wifi:' . $this->ukey);

            if ($result === false) {
                $data = array('code' => 1011, 'msg' => 'config the wifi member failed!!!', 'data' => array());
            } else {
                $data = array('code' => 200, 'msg' => 'success', 'data' => array());
            }
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询黑名单过滤配置
     */
    public function getBlackFilter()
    {
        $merchant = $this->getMerchant($this->ukey);
        $obj = M('default', $merchant['pre_table']);
        $black = $obj->where(array('customer_name' => 'black_filter'))->find();

        if (!is_array($black)) {
            $data = array('code' => 200, 'msg' => 'success', 'data' => array('customer_name' => 'black_filter','function_name' => 2));
        } else {
            $data = array('code' => 200, 'msg' => 'success', 'data' => $black);
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 配置是否进行黑名单过滤(添加或者修改)
     */
    public function setBlackFilter()
    {
        $is_filter = I('is_filter');

        $merchant = $this->getMerchant($this->ukey);
        $obj = M('default', $merchant['pre_table']);
        $black = $obj->where(array('customer_name' => 'black_filter'))->find();

        if (!is_array($black)) {
            $save['customer_name'] = 'black_filter';
            $save['function_name'] = $is_filter;
            $save['description'] = '黑名单过滤';
            $result = $obj->add($save);
        } else {
            $save['function_name'] = $is_filter;
            $result = $obj->where(array('customer_name' => 'black_filter'))->save($save);
        }

        if ($result === false) {
            $data = array('code' => 1011, 'msg' => 'config the black filter failed!!!', 'data' => array());
        } else {
            $data = array('code' => 200, 'msg' => 'success', 'data' => array());
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 问卷调查分组(添加或者更新)
     */
    public function questionGroup()
    {
        $id = I('id');
        $name = I('name');
        $des = I('des');

        $merchant = $this->getMerchant($this->ukey);
        $obj = M('ques_group', $merchant['pre_table']);

        $save['group_name'] = $name;
        $save['group_des'] = $des;

        if (!empty($id)) {
            $result = $obj->where(array('id' => $id))->save($save);
        } else {
            $result = $obj->add($save);
        }

        if ($result === false) {
            $data = array('code' => 1011, 'msg' => 'config the question group failed!!!');
        } else {
            $data = array('code' => 200, 'msg' => 'success');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询问卷调查分组
     */
    public function getQuesGroup()
    {
        $merchant = $this->getMerchant($this->ukey);
        $obj = M('ques_group', $merchant['pre_table']);
        $result = $obj->select();

        if (!is_array($result)) {
            $result = array();
        }

        $data = array('code' => 200, 'msg' => 'success', 'data' => $result);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 删除分组
     */
    public function delQuesGroup()
    {
        $id = I('id');
        $merchant = $this->getMerchant($this->ukey);

        $wj = M('wenjuan_questionnaire_list');
        $re = $wj->where(array('groupId' => $id))->find();

        if (is_array($re)) {
            $data = array('code' => 1119, 'msg' => '该分组下有关联问卷，不能删除');
            returnjson($data,$this->returnstyle,$this->callback);
        }

        $obj = M('ques_group', $merchant['pre_table']);
        $result = $obj->where(array('id' => $id))->delete();

        if ($result === false) {
            $data = array('code' => 1011, 'msg' => 'delete the question group failed!!!');
        } else {
            $data = array('code' => 200, 'msg' => 'success', 'data' => $result);
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 按ID查询分组信息
     */
    public function getQuesGroupById()
    {
        $id = I('id');
        $merchant = $this->getMerchant($this->ukey);
        $obj = M('ques_group', $merchant['pre_table']);
        $result = $obj->where(array('id' => $id))->find();

        if ($result === false) {
            $data = array('code' => 1011, 'msg' => 'delete the question group failed!!!');
        } else {
            $data = array('code' => 200, 'msg' => 'success', 'data' => $result);
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 获取会员卡样
     */
    public function get_member_code(){
        $db=M('member_code','total_');
        $merchant = $this->getMerchant($this->ukey);
        $arr=$db->where(array('admin_id'=>array('eq',$merchant['id'])))->order('code asc')->select();
        if($arr){
            $msg['code']=200;
            $msg['data']=$arr;
        }else{
            $msg['code']=102;
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    
    /**
     * 修改会员卡样信息
     */
    public function save_member_code(){
        $params['code']=I('code');     
        $params['name']=I('name');
        $params['imgurl']=I('imgurl');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['id']=I('id');
            $params['is_register']=I('is_register');
            $params['is_default']=I('is_default');
            $params['sort']=I('sort');
            
            $db=M('member_code','total_');
            $merchant = $this->getMerchant($this->ukey);
            
            if($params['id']){
                
                $where['admin_id']=array('eq',$merchant['id']);
                $where['id']=array('eq',$params['id']);
                $where['_logic']='and';
                $arr=$db->where($where)->save($params);
            }else{
                unset($params['id']);
                if(in_array('',$params)){
                    $msg['code']=1030;
                    echo returnjson($msg,$this->returnstyle,$this->callback);
                }
                $params['admin_id']=$merchant['id'];
                $arr=$db->add($params);
            }
            
            if($arr !== false){
                $msg['code']=200;
            }else{
                $msg['code']=104;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

    
    /**
     * 欧亚更新会员卡类别接口
     */
    public function save_codetype_ouya(){
        $url=C('DOMAIN').'/CrmService/OutputApi/Index/cardtype_list';
        $params['key_admin']=$this->ukey;
        $params['sign_key']=$this->admin_arr['signkey'];
        $params['sign']=sign($params);  
        unset($params['sign_key']);
        $data=json_decode(http($url,$params),true);
        if($data['code']==200){
            $db=M('member_code','total_');
            foreach($data['data'] as $k=>$v){
                $save['name']=$v['cardtype_name'];
                $save['code']=$v['cardtype_id'];
                $save['admin_id']=$this->admin_arr['id'];
                $db->add($save);
            }
        }
        
    }
    
//     //更新数据
//     public function update_data(){
//         $total_db=M('admin','total_');
//         $total_arr=$total_db->field('id,ukey')->select();
//         foreach($total_arr as $k=>$v){
//                 $total_id[$v['id']]=$v['ukey'];
//         }
//         $total_id=array_unique($total_id);
//         foreach($total_id as $key=>$val){
//             $db=M('static','total_');
//             $where['tid']=array('eq',5);
//             $where['admin_id']=array('eq',$key);
//             $where['_logic']='and';
//             $arr=$db->where($where)->find();

//             if($arr){
//                 $code_arr=json_decode($arr['content'],true);
//                 $img_arr=json_decode($arr['url'],true);
//                 foreach($code_arr as $k=>$v){
//                     $data['code']=$k;
//                     $data['name']=$v;
//                     $data['admin_id']=$key;
//                     $data['imgurl']=$img_arr[$k];
//                     $datas[]=$data;
//                 }
                
//                 $member_code=M('member_code','total_');
//                 $res=$member_code->addAll($datas);
//             }
//             $datas=array();
//         }
//         if($res){
//             $msg['code']=200;
//         }else{
//             $msg['code']=104;
//         }
//         returnjson($msg,$this->returnstyle,$this->callback);
//     }
                
    /**
     * 获取单个会员卡样
     */
    public function once_member_code(){
        $params['id']=I('id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('member_code','total_');
            $arr=$db->where(array('id'=>array('eq',$params['id'])))->find();
            if($arr){
                $msg['code']=200;
                $msg['data']=$arr;
            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }
    /**
     * 删除会员卡样
     */
    public function  delete_member_code(){
        $params['id']=I('id');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $db=M('member_code','total_');
            $arr=$db->where(array('id'=>array('eq',$params['id'])))->delete();
            if($arr !== false){
                $msg['code']=200;
            }else{
                $msg['code']=102;
            }
        }
        returnjson($msg,$this->returnstyle,$this->callback);
    }

}
