<?php
namespace Member\Controller;
use Common\Controller\CommonController;
use Common\Controller\RedisController;
class AddrProController extends CommonController {
    public $redis;
    /**
     * 页面跳转
     * @param $key_admin
     */
    public function dumpUrl() {
        $key_admin    = I("key_admin");
        //$key_admin    = "61fbefbc8ce619440e275820e4effdd1";   //测试数据
        $version_path = C('VERSION_PATH');
        if(!$key_admin) {
            header("http/1.1 404 not found");exit;
        }
        $redis_con   = new RedisController();
        $this->redis = $redis_con->connectredis();
        $version_url = $this->redis->get(md5($version_path['member'] . "_" . $key_admin));
        if($version_url) {
            $v_url = json_decode($version_url);
            header("Location:".$v_url);exit;
        }else{
            $totalAdmin = M("admin",'total_');
            $adminId    = $totalAdmin->where(array('ukey' => $key_admin))->field("id")->find();
            if(empty($adminId)){
                header("http/1.1 404 not found");
            }
            $totalVersion    = M('version','total_');

			$join = ' `total_version_url` on `total_version`.`id` = `total_version_url`.`version_id`';
			$where = array('`total_version_url`.`adminid`'=>$adminId['id'],'`total_version`.`classes`'=>$version_path['member']);
			$field = 'total_version.url';
			$versionUrl = $totalVersion->field($field)->join($join)->where($where)->find();

            if(empty($versionUrl)){
                header("http/1.1 404 not found");exit;
			}
			if(strpos($versionUrl['url'],"key_admin")){
				$url = str_replace('{key_admin}',$key_admin , $versionUrl['url']);
			}else{
				$url = $versionUrl['url']."?key_admin=".$key_admin;	
			}
            //$this->redis->set(md5($version_path['member'] . "_" . $key_admin), json_encode($url),array('ex'=>432000));
            header("Location:".$url);exit;
        }
    }
    
    /**
     * 调转接口
     */
    public function dumpUrls() {
        $key_admin    = I("key_admin");
        //$key_admin    = "61fbefbc8ce619440e275820e4effdd1";   //测试数据
        $type_id = I('key_p');
        if(!$key_admin || empty($type_id)) {
            header("http/1.1 404 not found");exit;
        }
//         $redis_con   = new RedisController();
//         $this->redis = $redis_con->connectredis();
//         $version_url = $this->redis->get(md5("catalog_url_" . $key_admin));
//         if($version_url) {
//             $v_url = json_decode($version_url);
//             header("Location:".$v_url);exit;
//         }else{
            $totalAdmin = M("admin",'total_');
            $adminId    = $totalAdmin->where(array('ukey' => $key_admin))->find();
            if(empty($adminId)){
                header("http/1.1 404 not found");exit;
            }
            $totalcatalog    = M('catalog','total_');
            $map['id']=array('eq',$type_id);
            $map['status']=array('eq',1);
            $map['_logic']='and';
            $catalog = $totalcatalog->where($map)->find();
            if(empty($catalog)){
                header("http/1.1 404 not found");exit;
            }

            //流量统计

         $pagename=$this->getPageName($catalog['id']);
            if($pagename){

                $this->addPagePv($adminId['id'],$pagename);
            }


            $totalVersion    = M('version','total_');
            $totalVersionUrl = M('version_url','total_');
            $where['type_id']=array('eq',$type_id);
            $where['adminid']=array('eq',$adminId['id']);
            $where['_logic']='and';
            $versionId = $totalVersionUrl->where($where)->find();
            $wheres['id']=array('eq',$versionId['version_id']);
            $wheres['status']=array('eq',1);
            $wheres['_logic']='and';
            $versionUrl = $totalVersion->where($wheres)->field("url")->find();
            if(empty($versionUrl)){
                header("http/1.1 404 not found");exit;
            }
            $url = str_replace('{key_admin}',$key_admin , $versionUrl['url']);
			//             $this->redis->set(md5("catalog_url_" . $key_admin), json_encode($url),array('ex'=>432000));
			if(strpos($url,'{op}')){
				$childDb = M('default',$adminId['pre_table']);
				$opInfo = $childDb->where(array('customer_name'=>'op'))->find();
				$op = '';
				if($opInfo['function_name']){ $op = $opInfo['function_name']; }
				$url = str_replace('{op}',$op, $url);
			}
			if(strpos($url,'{buildid}')){
				$childDb = M('buildid','total_');
				$buildInfo = $childDb->where(array('adminid'=>$adminId['id']))->find();
				$bdid = '';
				if($buildInfo['buildid']){ $bdid = $buildInfo['buildid']; }
				$url = str_replace('{buildid}',$bdid, $url);
			}
            header("Location:".html_entity_decode($url));exit;
        }
//     }

 /**
  *流量统计
  **/
    public function addPagePv($adminid,$pagename)
    {

        $params['pagename'] = $pagename;

        //更新流量

        $key = getPagepvKey($adminid);
        $redis_con   = new RedisController();
        $this->redis = $redis_con->connectredis();
        $data = $this->redis->get($key);

        if(empty($data))
        {
            $data = json_encode(array($params['pagename'] => 1), true);
        }
        else
        {
            $data = json_decode($data, true);
            if(empty($data[$params['pagename']]))
            {
                $data[$params['pagename']] = 1;
            }
            else
            {
                $data[$params['pagename']] += 1;
            }
            $data = json_encode($data, true);
        }

        $res = $this->redis->set($key, $data);

        return true;
    }
    //获取页面标识
    public function  getPageName($id){


        $db=M('catalog','total_');

        $data=$db->field('page_mark')->where(array('id'=>$id))->find();

        if($data){

            return $data['page_mark'];
        }
        return false;
    }
}
?>
