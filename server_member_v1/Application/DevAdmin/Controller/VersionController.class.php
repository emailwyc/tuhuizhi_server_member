<?php
/**
 * Info: 版本相关接口
 * User: wangyc
 * Date: 12/20/16
 * Time: 13:00
 */
namespace DevAdmin\Controller;
use Think\Controller;
use DevAdmin\Controller\DevcommonController;
use Common\Controller\RedisController as A;

class VersionController extends DevcommonController
{
	public function _initialize(){
		parent::_initialize();
	}

	/**
	 * 1.获取列表
	 * @param  $classes
     * @return mixed
	 */
	public function getList(){
		$this->emptyCheck(array('classes'));
		$db=M('version','total_');
		$arr=$db->where(array('classes'=>$this->params['classes']))->select();
		$msg = !empty($arr) ? array('code'=>200,'data'=>$arr) : array('code'=>102);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 2.添加新的版本
	 * @param  array('classes','name','code','url','desc')
     * @return mixed
	 */
	public function create(){
		$params = $this->params;
		$saveParams = $this->emptyCheck(array('classes','name','url'));
		$saveParams['desc'] = $params['desc'];
		$saveParams['code'] = $params['code'];
		if(!in_array($params['classes'],C('VERSION_PATH'))){
			echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
		}
		$db = M('version','total_');
		$arr = $db->where(array('classes'=>$params['classes'],'url'=>$params['url']))->find();
		if($arr){
			echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();
		}
		$res = $db->add($saveParams);
		$msg = $res == false ? array('code'=>104):array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 3.更新版本
	 * @param  array('id','classes','name','code','url','desc')
     * @return mixed
	 */
	public function update(){
		$params = $this->params;
		$saveParams = $this->emptyCheck(array('id','classes','name','url'));
		$saveParams['desc'] = $params['desc'];
		$saveParams['code'] = $params['code'];
		if(!in_array($params['classes'],C('VERSION_PATH'))){
			echo returnjson(array('code'=>1030),$this->returnstyle,$this->callback);exit();
		}
		$db = M('version','total_');
		$arr = $db->where(array('classes'=>$params['classes'],'url'=>$params['url'],'id'=>array('<>'=>$params['id'])))->find();
		if($arr){
			echo returnjson(array('code'=>1008),$this->returnstyle,$this->callback);exit();
		}
		$res = $db->where(array('id'=>$params['id']))->save($saveParams);
		//$msg = $res == false ? array('code'=>104):array('code'=>200);
		$msg = array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 4.删除版本
	 * @param  array('id')
     * @return mixed
	 */
	public function del(){
		$params = $this->params;
		$saveParams = $this->emptyCheck(array('id'));
		$db = M('version','total_');
		$urlDb = M('version_url','total_');
		$selInfo = $urlDb->where(array('version_id'=>$params['id']))->find();
		if($selInfo){
			echo returnjson(array('code'=>4008),$this->returnstyle,$this->callback);exit();
		}
		$res = $db->where(array('id'=>$params['id']))->delete();
		$msg = $res == false ? array('code'=>104):array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 5.获取所有版本类别
	 * @param  array
     * @return mixed
	 */
	public function getAllClasses(){
		$classes = C('VERSION_TYPE');
		$msg=array('code'=>200,'data'=>$classes);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 6.获取商户当前配置
	 * @param  array
     * @return mixed
	 */
	public function getAdminSetting(){
		$params = $this->params;
		$this->emptyCheck(array('classes','adminid','domain','ukey'));
		$db=M('version','total_');
		$join = ' `total_version_url` on `total_version`.`id` = `total_version_url`.`version_id`';
		$where = array('`total_version_url`.`adminid`'=>$params['adminid'],'`total_version`.`classes`'=>$params['classes']);
		$field = 'total_version_url.id as version_url_id,total_version.id as version_id,total_version.name,total_version.code,total_version.classes,total_version_url.desc as version_url_desc,total_version.desc as version_desc';
		$arr = $db->field($field)->join($join)->where($where)->find();

        //根据id查询此ID的key_admin
        $admindb=M('admin', 'total_');
		$find=$admindb->where(array('id'=>$params['adminid']))->find();
        //根据域名判断当前是正式还是测试
        if ($params['domain']=='dashboard.rtmap.com'){
            $url='https://h5.rtmap.com';
            $activity='https://h5.rtmap.com/market/activity/activity/'.$find['ukey'].'/0';
            $burl='https://vip.rtmap.com';
            $wangzhan='http://h5.rtmap.com/market/main/page/'.$find['ukey'];
            $scorestore='https://h5.rtmap.com/mall/product/list?key_admin='.$find['ukey'];
            $scoreadd='https://h5.rtmap.com/point/add?key_admin='.$find['ukey'];
            $scoretransfer='https://h5.rtmap.com/intergal/index?key_admin='.$find['ukey'];
            $carpay='https://h5.rtmap.com/park?key_admin='.$find['ukey'];
            $question='https://h5.rtmap.com/survey/index?key_admin='.$find['ukey'];
        }else{
            $url='https://h2.rtmap.com';
            $activity='http://zht.wemalltech.com/market/activity/activity/'.$find['ukey'].'/1';
            $burl='https://viptest.rtmap.com';
            $wangzhan='http://zht.wemalltech.com/market/main/page/'.$find['ukey'];
            $scorestore='https://h2.rtmap.com/mall/product/list?key_admin='.$find['ukey'];
            $scoreadd='https://h2.rtmap.com/point/add?key_admin='.$find['ukey'];
            $scoretransfer='https://h2.rtmap.com/intergal/index?key_admin='.$find['ukey'];
            $carpay='https://h2.rtmap.com/park?key_admin='.$find['ukey'];
            $question='https://h2.rtmap.com/survey/index?key_admin='.$find['ukey'];
        }
		//会员url
        if (empty($arr)) {
            $arr['memburl']=$url.'/user/?key_admin='.$find['ukey'];
        }else{
            $arr['memburl'] = $url.'/member/tz.html?key_admin='.$find['ukey'];
        }
        $arr['lookcarsurl']=$url.'/lookcars/index?key_admin='.$find['ukey'];//寻车url
        //微商城URL
        $smallstoreurl=$url.'/market/main/website/'.$find['ukey'];
        $arr['smallstore_c_url']='https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/getuserinfo?jumpurl='.urlencode($smallstoreurl).'&key_admin='.$find['ukey'].'&scope=snsapi_base';
        $arr['smallstore_b_url']=$burl.'/marketsku/index/page/'.$find['ukey'];


        //微活动
        $arr['activityurl']='https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/getuserinfo?jumpurl='.urlencode($activity).'&key_admin='.$find['ukey'].'&scope=snsapi_base';

        //微网站
        $arr['websiteurl']='https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/getuserinfo?jumpurl='.urlencode($wangzhan).'&key_admin='.$find['ukey'].'&scope=snsapi_base';

        //积分商城
        $arr['scorestoreurl']=$scorestore;

        //积分补录
        $arr['scoreaddurl']=$scoreadd;

        //积分转增
        $arr['scoretransferurl']=$scoretransfer;

        //卡包
        
        $default_db=M('default',$find['pre_table']);
        $op_arr=$default_db->where(array('customer_name'=>'op'))->find();

        if($op_arr['function_name']){
            $one='https://mem.rtmap.com/Card/Card/showmycard?url=http://res.rtmap.com/image/vs3/proj/standard3/pack.html?op=open_id_m,'.$op_arr['function_name'];
            $two='http://res.rtmap.com/static/dist/card/card.html?oa='.$op_arr['function_name'];
        }else{
            $one='https://mem.rtmap.com/Card/Card/showmycard?url=http://res.rtmap.com/image/vs3/proj/standard3/pack.html?op=open_id_m,{营销平台分配的账户密钥}';
            $two='http://res.rtmap.com/static/dist/card/card.html?oa={营销平台分配的账户密钥}';
        }
        
        $arr['cardbagurloneurl']='https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/getuserinfo?key_admin='.$find['ukey'].'&scope=snsapi_userinfo&jumpurl='.urlencode($one);
        $arr['cardbagurltwourl']='https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/getuserinfo?key_admin='.$find['ukey'].'&scope=snsapi_base&jumpurl='.urlencode($two);

        //停车
        $arr['carpayurl']=$carpay;

        //问卷调查
        $arr['questionurl']='https://mem.rtmap.com/Thirdwechat/Wechat/Oauth/getuserinfo?jumpurl='.urlencode($question).'&key_admin='.$find['ukey'].'&scope=snsapi_base';

		$arr = empty($arr) ? array():$arr;
		$msg = array('code'=>200,'data'=>$arr);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

	/**
	 * 7.修改商户当前配置
	 * @param  array
     * @return mixed
	 */
	public function updateAdminSetting(){
		$params = $this->params;
		$saveParams = $this->emptyCheck(array('version_id','classes','adminid'));
		//检查是否存在version
		$db = M('version','total_');
		$arr = $db->where(array('id'=>$params['version_id'],'classes'=>$params['classes']))->find();
		if(!$arr){
			echo returnjson(array('code'=>102),$this->returnstyle,$this->callback);exit();
		}
		//检查是否已经设置过该classes的version
		$join = ' `total_version_url` on `total_version`.`id` = `total_version_url`.`version_id`';
		$where = array('`total_version_url`.`adminid`'=>$params['adminid'],'`total_version`.`classes`'=>$params['classes']);
		$field = 'total_version_url.id,total_version.classes';
		$setArr = $db->field($field)->join($join)->where($where)->find();
		//更新或添加数据
		$urlDb = M('version_url','total_');
		$newData = array('version_id'=>$params['version_id'],'adminid'=>$params['adminid'],'desc'=>$params['desc']);
		if($setArr){//更新
			$res = $urlDb->where(array('id'=>$setArr['id']))->save($newData);
		}else{//添加
			$res = $urlDb->add($newData);
		}
        $admindb=M('admin', 'total_');
        $find=$admindb->where(array('id'=>$params['adminid']))->find();
        $version_path = C('VERSION_PATH');
        $this->redis->del(md5($version_path['member'] . "_" . $find['ukey']));
		$msg = $res == false ? array('code'=>104):array('code'=>200);
		echo returnjson($msg,$this->returnstyle,$this->callback);exit();
	}

}
