<?php
/**
 * Created by PhpStorm.
 * User: jaleel
 * Date: 2017/2/15
 * Time: 上午10:24
 */

namespace MerAdmin\Controller;

use DevAdmin\Controller\DevcommonController;

class SendMessageController extends DevcommonController
{
    protected $staticObj;
    protected $adminObj;

    public function _initialize()
    {
        parent::__initialize();
        $this->staticObj = M('total_static');
        $this->adminObj = M('total_admin');
    }

    /**
     * 返回所有配置过短信签名的签名列表信息
     */
    public function getMsgSign()
    {
        $page = I('page');
        $page = empty($page) ? 1 : $page;
        $sign = I('sign');

        if (!empty($sign)) {
            $sign_arr = $this->getHaveMsgSign($sign);
        } else {
            $sign_arr = $this->getHaveMsgSign();
        }

//        writeOperationLog(array('get have sign' => json_encode($sign_arr)), 'jaleel_logs');

        $return_data['page']['total_page'] = $total_page = ceil(count($sign_arr) / 12);
        $return_data['page']['count_data'] = $count_data = count($sign_arr);
        $return_data['page']['current_page'] = $page;

        if ($sign_arr) {
            foreach ($sign_arr as $k=>$v) {
                $bid_arr[] = $v['admin_id'];
                $signs[$v['admin_id']] = $v['content'];
            }
        }

        if (count($bid_arr) == 0) {
            $data = array('code' => 200, 'data' => array('data' => array(),'page' => array()));
            returnjson($data,$this->returnstyle,$this->callback);
        }

        if ($total_page > 1) {
            $need_bid = array_slice($bid_arr, ($page-1) * 12, 12);
        } else {
            $need_bid = $bid_arr;
        }

//        writeOperationLog(array('page show merchant ids' => json_encode($need_bid)), 'jaleel_logs');

        $admin_arr = $this->adminObj->where(array('id'=>array('in', $need_bid)))->select();

//        writeOperationLog(array('select merchants' => json_encode($admin_arr)), 'jaleel_logs');

        if (is_array($admin_arr)) {
            foreach ($admin_arr as $k=>$v) {
                $return[$k]['id'] = $v['id'];
                $return[$k]['name'] = $v['describe'];
                $return[$k]['sign'] = $signs[$v['id']];

                $obj = M('default', $v['pre_table']);
                $result = $obj->where(array('customer_name' => 'sendmsg'))->find();
                $return[$k]['interName'] = $result['function_name'];
            }
        }

        $return_data['data'] = $return;
        $data = array('code' => 200, 'data' => $return_data);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 添加短信通道签名
     */
    public function addMsgSign()
    {
        $id = I('id');
        $sign = I('sign');
        $interName = I('interName');

        $data['tid'] = 12;
        $data['admin_id'] = $id;
        $data['title'] = '短信通道签名';
        $data['content'] = $sign;
        $data['des'] = '短信通道签名';
        $result = $this->staticObj->add($data);

        if (!$result) {
            $data = array('code' => 1011, 'msg' => 'add message signature failed!');
            returnjson($data,$this->returnstyle,$this->callback);
        }

        $merchant = $this->getMerchantInfo($id);

        if (is_array($merchant)) {
            $default = M('default', $merchant['pre_table']);
            $msg = $default->where(array('customer_name' => 'sendmsg'))->find();
            if ($msg) {
                $re = $default->where(array('customer_name' => 'sendmsg'))->save(array('function_name' => $interName));
            } else {
                $re = $default->add(array('customer_name' => 'sendmsg', 'function_name' => $interName, 'description' => '短信验证码'));
            }
        }

        if ($re === false) {
            $data = array('code' => 1011, 'msg' => 'config send msg interface failed!');
            returnjson($data,$this->returnstyle,$this->callback);
        }

        $data = array('code' => 200, 'msg' => 'success');
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询没有配置短信通道签名的商户
     */
    public function getUnMsgSign()
    {
        $merchant_arr = $this->getAllMerchant();
        $sign_arr = $this->getHaveMsgSign();

        foreach ($merchant_arr as $k=>$v) {
            foreach ($sign_arr as $val) {
                if ($v['id'] == $val['admin_id']) {
                    unset($merchant_arr[$k]);
                }
            }
        }

        $return = array_values($merchant_arr);

        $data = array('code' => 200, 'msg' => 'success', 'data' => $return);
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 编辑短信通道签名
     */
    public function editMsgSign()
    {
        $id = I('id');
        $sign = I('sign');
        $interName = I('interName');

        $data['content'] = $sign;
        $result = $this->staticObj->where(array('tid' => 12, 'admin_id' => $id))->save($data);

        if ($result === false) {
            $data = array('code' => 1011, 'msg' => 'edit message signature failed!');
            returnjson($data,$this->returnstyle,$this->callback);
        }

        $merchant = $this->getMerchantInfo($id);

        if (is_array($merchant)) {
            $default = M('default', $merchant['pre_table']);
            $msg = $default->where(array('customer_name' => 'sendmsg'))->find();
            if ($msg) {
                $re = $default->where(array('customer_name' => 'sendmsg'))->save(array('function_name' => $interName));
            } else {
                $re = $default->add(array('customer_name' => 'sendmsg', 'function_name' => $interName, 'description' => '短信验证码'));
            }
        }

        if ($re === false) {
            $data = array('code' => 1011, 'msg' => 'config send msg interface failed!');
            returnjson($data,$this->returnstyle,$this->callback);
        }

        $data = array('code' => 200, 'msg' => 'success');
        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 删除短信通道签名
     */
    public function delMsgSign()
    {
        $id = I('id');
        $re = $this->staticObj->where(array('tid' => 12, 'admin_id' => $id))->delete();

        if ($re) {
            $data = array('code' => 200, 'msg' => 'success');
        } else {
            $data = array('code' => 1011, 'msg' => 'delete message signature failed!');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 查询所有配置过的短信通道签名
     * @param string $sign
     * @return bool
     */
    protected function getHaveMsgSign($sign = '')
    {
        if (!empty($sign)) {
            $where['content'] = array('like', '%' . $sign . '%');
        }

        $where['tid'] = array('eq', 12);
        $sign_arr = $this->staticObj->where($where)->order('id')->select();

        if (is_array($sign_arr)) {
            return $sign_arr;
        }
        return false;
    }

    /**
     * 查询所有商户信息
     * @return bool
     */
    protected function getAllMerchant()
    {
        $result = $this->adminObj->select();

        if (is_array($result)) {
            return $result;
        }
        return false;
    }

    /**
     * 根据商户ID获取短信通道签名
     */
    public function getSignById()
    {
        $id = I('id');

        $sign_arr = $this->staticObj->where(array('tid' => 12, 'admin_id' => $id))->find();

        $admin_arr = $this->adminObj->where(array('id' => $id))->find();

        if (is_array($sign_arr) && is_array($admin_arr)) {
            $return = array();
            $return['admin_id'] = $id;
            $return['describe'] = $admin_arr['describe'];
            $return['content'] = $sign_arr['content'];

            $default = M('default', $admin_arr['pre_table']);
            $msg = $default->where(array('customer_name' => 'sendmsg'))->find();
            $return['interName'] = $msg['function_name'];

            $data = array('code' => 200, 'msg' => 'success', 'data' => $return);
        } else {
            $data = array('code' => 1011, 'msg' => 'get message signature failed!');
        }

        returnjson($data,$this->returnstyle,$this->callback);
    }

    /**
     * 根据商户id查询商户信息
     * @param $id
     * @return mixed
     */
    protected function getMerchantInfo($id)
    {
        $result = $this->adminObj->where(array('id' => $id))->find();
        return $result;
    }
}