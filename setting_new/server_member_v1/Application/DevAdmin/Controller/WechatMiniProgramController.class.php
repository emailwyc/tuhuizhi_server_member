<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 24/08/2017
 * Time: 14:10
 */

namespace DevAdmin\Controller;


use Thirdwechat\Controller\Wechat\WechatcommonController;
use Thirdwechat\Service\MiniProgram\Open\DomainService;
use Thirdwechat\Service\MiniProgram\Open\MiniProgramManagerCodeService;
use Thirdwechat\Service\MiniProgram\Open\MiniProgramTemplateService;
use Thirdwechat\Service\MiniProgram\Open\MiniProgramTesterService;

class WechatMiniProgramController extends DevcommonController
{
    public function _initialize(){
        parent::__initialize();
    }

    /********************************************************小程序安全域名部分******************************************************************/
    public function setdomain()
    {
        $admininfo = $this->getMerchant($this->ukey);
        $params['action'] = I('post.type');//add添加, delete删除, set覆盖, get获取,当参数是get时不需要填四个域名字段。
        $params['requestdomain'] = $_POST['requestdomain'];
        $params['wsrequestdomain'] = $_POST['wsrequestdomain'];
        $params['uploaddomain'] = $_POST['uploaddomain'];
        $params['downloaddomain'] = $_POST['downloaddomain'];
        if (!in_array($params['action'], array('add', 'delete', 'set', 'get'))){
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }
        if (false != $admininfo['applet_appid']) {
            $set = DomainService::modify_domain($admininfo['applet_appid'], $params['action'], $params['requestdomain'], $params['wsrequestdomain'], $params['uploaddomain'], $params['downloaddomain']);
            returnjson($set,$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }

    }


    /**
     * 获取智慧图安全域名，没有添加编辑，直接去数据库增删改查，数据结构如下：
     * $arrayre = array('https://o2o.rtmap.com', 'https://mem.rtmap.com','https://backend.rtmap.com');
     * dump(serialize($arrayre));
     */
    public function getdomain()
    {
        $admininfo = $this->getMerchant($this->ukey);
        $d = D('total_wechat_miniprogram');
        $sel = $d->where(array('miniprogram_key'=>array('in', array('wsrequestdomain', 'uploaddomain', 'requestdomain', 'downloaddomain'))))->select();//这几个需要是一位数组序列化的字符串
        if ($sel) {
            foreach ($sel as $key => $value) {
                if ($value['miniprogram_value']){
                    $sel[$key]['miniprogram_value'] = json_decode($value['miniprogram_value'], true);
                }
            }
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }

    }

    /********************************************************小程序体验者部分******************************************************************/
    
    /**
     * 微信小程序绑定体验者
     * type bind绑定、unbind解绑
     */
    public function bindtester()
    {
        $params['type'] = I('type');
        $params['key_admin'] = I('key_admin');
        $params['wechatid'] = I('wechatid');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        if (!in_array($params['type'], array('bind', 'unbind'))){
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }
        
        $admininfo = $this->getMerchant($params['key_admin']);
        if (false != $admininfo['applet_appid']) {
            $bind = MiniProgramTesterService::bindTester($admininfo['applet_appid'], $params['type'], $params['wechatid']);
            returnjson($bind,$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }  
    }


    /********************************************************小程序模板消息部分******************************************************************/
    /**
     * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1500465446_j4CgR&token=&lang=zh_CN
     * 1.获取小程序模板库标题列表
     */
    public function templatelibrarylist()
    {
        $params['pageno'] = I('pageno');
        $params['line'] = I('line');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramTemplateService::templateLibraryList( $params['pageno'], $params['line'], $admininfo['applet_appid']);

        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /**
     * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1500465446_j4CgR&token=&lang=zh_CN
     * 2.获取模板库某个模板标题下关键词库
     */
    public function templatelibraryword()
    {
        $params['id'] = I('id');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramTemplateService::templateLibraryWxopen( $params['id'], $admininfo['applet_appid']);

        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /**
     * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1500465446_j4CgR&token=&lang=zh_CN
     * ****************************************
     * 用前两个接口返回的信息，组合模板消息的模板  **
     * ****************************************
     * 3.组合模板并添加至帐号下的个人模板库
     */
    public function templateaddword()
    {
        $params['key_admin'] = I('key_admin');
        $params['keywords_id'] = I('key_words_id');//数组
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        if (!is_array($params['keywords_id'])) {
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramTemplateService::templateWxopenAdd( $params['keywords_id'], $params['id'], $admininfo['applet_appid']);

        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /**
     * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1500465446_j4CgR&token=&lang=zh_CN
     * 1.获取帐号下已存在的模板列表
     */
    public function templatewxopenlist()
    {
        $params['pageno'] = I('pageno');
        $params['line'] = I('line');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramTemplateService::templateWxopenList( $params['pageno'], $params['line'], $admininfo['applet_appid']);

        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /**
     *https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1500465446_j4CgR&token=&lang=zh_CN
     * 2.删除帐号下的某个模板(模板消息id)
     */
    public function templatelibraryworddel()
    {
        $params['id'] = I('id');
        $params['key_admin'] = I('key_admin');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramTemplateService::templateWxopenDel( $params['id'], $admininfo['applet_appid']);

        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /********************************************************小程序代码部分******************************************************************/
    /**
     * 获取智慧图安全域名，没有添加编辑，直接去数据库增删改查，数据结构如下：
     * $arrayre = array('https://o2o.rtmap.com', 'https://mem.rtmap.com','https://backend.rtmap.com');
     * dump(serialize($arrayre));
     */
    public function getminiprogramtemplate()
    {
        $admininfo = $this->getMerchant($this->ukey);
        $d = D('total_miniprogram_template');
        $sel = $d->select();
        if ($sel) {
            returnjson(array('code'=>200, 'data'=>$sel),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }

    }





    /**
     * 1、为授权的小程序帐号上传小程序代码
     */
    public function codecommit()
    {
        if (IS_GET) {
            returnjson(array('code'=>106),$this->returnstyle,$this->callback);
        }
        $params['id'] = I('post.id');//数据库存的id，不是模板id

        $params['key_admin'] = I('post.key_admin');
        $params['user_version'] = I('post.user_version');
        $params['user_desc'] = I('post.user_desc');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        $params['ext'] = $_POST['ext'] ? : '';
        $extarr = json_decode($params['ext'], true);
        if ($params['ext'] && !is_array($extarr)) {//如果有值，并且值转完后不是数组
            returnjson(array('code'=>1051,'data'=>3),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramManagerCodeService::codeCommit( $admininfo['applet_appid'], $params['id'], $extarr, $params['user_version'], $params['user_desc']);

        returnjson($ret,$this->returnstyle,$this->callback);

    }


    /**
     * 2、获取体验小程序的体验二维码
     * get请求，不用接口请求，跳转
     */
    public function getexperienceqrcode()
    {
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($params['key_admin']);
        $getAuthorizerAccessToken = MiniProgramManagerCodeService::getAuthorizerAccessToken($admininfo['applet_appid']);
        $url = WechatcommonController::$wechatMiniProgramExperienceQrcode;
        $url = str_replace('[TOKEN]', $getAuthorizerAccessToken, $url);
        header('Content-type: image/jpeg');
        echo file_get_contents($url);
//        header('Location:'.$url);
    }


    /**
     * 3、获取授权小程序帐号的可选类目
     */
    public function getcategory()
    {
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($params['key_admin']);
        $return = MiniProgramManagerCodeService::getCategory($admininfo['applet_appid']);
        returnjson($return,$this->returnstyle,$this->callback);
    }


    /**
     * 4、获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
    {
    "code": 200,
    "data": {
    "page_list": [
    "pages/index/index",
    "pages/city/city",
    "pages/mycoupon/mycoupon",
    "pages/details/details",
    "pages/receive/receive"
    ]
    },
    "msg": "SUCCESS."
    }
     */
    public function getminiprogrampages()
    {
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($params['key_admin']);
        $return = MiniProgramManagerCodeService::getMiniProgramPage($admininfo['applet_appid']);
        returnjson($return,$this->returnstyle,$this->callback);
    }


    /**
     * 5.将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     */
    public function submitaudit()
    {
        $params['key_admin'] = I('post.key_admin');
        $params['item_list'] = I('post.item');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        if (!is_array($params['item_list'])) {
            returnjson(array('code'=>1051),$this->returnstyle,$this->callback);
        }

        $admininfo = $this->getMerchant($params['key_admin']);
        $ret = MiniProgramManagerCodeService::submitAudit($admininfo['applet_appid'], $params['item_list']);
        if ($ret['code'] == 200) {
            $db = D('total_miniprogram_submit_audit');
            $data= array('auditid'=>$ret['data']['auditid'],'adminid'=>$admininfo['id'], 'item_list'=>$ret['item']);
            $add = $db->add($data);
            unset($data['item_list']);
        }
        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /**
     * 7.查询某个指定版本的审核状态（仅供第三方代小程序调用）
     */
    public function getauditstatus()
    {
        $params['key_admin'] = I('key_admin');
        $params['id'] = I('id');
        if (in_array('', $params)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }

        $admininfo = $this->getMerchant($params['key_admin']);
        $db = D('total_miniprogram_submit_audit');
        $find = $db->where(array('id'=>$params['id']))->find();//查询auditid
        $ret = MiniProgramManagerCodeService::getAuditStatus($admininfo['applet_appid'], $find['auditid']);
        if ($ret['code'] == 200) {
            $db = D('total_miniprogram_submit_audit');
            $data= array(
                'id'=>$params['id'],
                'status'=>$ret['data']['status'],
                'reason'=> $ret['data']['status'] == 1 ? $ret['data']['reason'] : ''
            );
            $save = $db->save($data);
        }
        returnjson($ret,$this->returnstyle,$this->callback);
    }


    /**
     * 8.查询最新一次提交的审核状态（仅供第三方代小程序调用）
     */
    public function getlatestauditstatus()
    {
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($params['key_admin']);
        $return = MiniProgramManagerCodeService::getLastAuditStatus($admininfo['applet_appid']);
        if ($return['code'] == 200) {
            $db = D('total_miniprogram_submit_audit');
            $data= array(
                'status'=>$return['data']['status'],
                'reason'=> $return['data']['status'] == 1 ? $return['data']['reason'] : ''
            );
            $save = $db->where(array('auditid'=>$return['data']['auditid']))->save($data);
        }
        returnjson($return,$this->returnstyle,$this->callback);
    }




    /**
     * 9、发布已通过审核的小程序（仅供第三方代小程序调用）
     */
    public function release()
    {
        $params['key_admin'] = I('key_admin');
        $admininfo = $this->getMerchant($params['key_admin']);
        $return = MiniProgramManagerCodeService::release($admininfo['applet_appid']);
        returnjson($return,$this->returnstyle,$this->callback);
    }



    /**
     * 10、修改小程序线上代码的可见状态（仅供第三方代小程序调用）
     */
    public function changevisitstatus()
    {
        $params['key_admin'] = I('key_admin');
        $params['status'] = (int)I('status');//只要传过来的值是真的或是错的，强转不会错
        if (in_array('', $params, true)) {
            returnjson(array('code'=>1030),$this->returnstyle,$this->callback);
        }
        if (!in_array($params['status'], array(0,1))){
            returnjson(array('code'=>1051, 'data'=>2),$this->returnstyle,$this->callback);
        }
        $admininfo = $this->getMerchant($params['key_admin']);
        $return = MiniProgramManagerCodeService::changeVisitStatus($admininfo['applet_appid'], $params['status']);
        returnjson($return,$this->returnstyle,$this->callback);
    }


    /**
     * 只是查库，没有调接口
     * 获取最后一次提交的代码审核的信息
     */
    public function getlastsubmitaudit()
    {
        $admininfo = $this->getMerchant($this->ukey);
        $d = D('total_miniprogram_submit_audit');
        $data = $d->field('auditid', true)->where(array('adminid'=>$admininfo['id']))->order('id desc')->find();
        if ($data) {
            returnjson(array('code'=>200, 'data'=>$data),$this->returnstyle,$this->callback);
        }else{
            returnjson(array('code'=>102),$this->returnstyle,$this->callback);
        }
    }



































}