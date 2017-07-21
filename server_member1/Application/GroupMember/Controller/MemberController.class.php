<?php
/**
 * 集团客户会员C端类
 * User: jaleel
 * Date: 2017/4/27
 * Time: 上午11:48
 */

namespace GroupMember\Controller;

use Common\Controller\JaleelController;

class MemberController extends  JaleelController
{
    protected $merchant;
    protected $uid;
    protected $member_id;

    public function _initialize()
    {
        parent::_initialize();
        $this->merchant = $this->getMerchant($this->ukey);
        $this->uid = I('uid');
    }

    protected function checkLogin()
    {
        $obj = M('mem', $this->merchant['pre_table']);
        $result = $obj->where(array('unionid' => $this->uid, 'key_admin' => $this->ukey))->select();

        if (is_array($result) && count($result) > 0) {

            if (isset($result[0])) {
                foreach ($result as $v) {
                    if ($v['default_card'] == 1) {
                        $this->member_id = $v['member_id'];
                    }
                }
            } else {
                $this->member_id = $result['member_id'];
            }
        } else {
            $data = array('code' => '2000', 'msg' => 'u are not our member!');
            returnjson($data, $this->returnstyle, $this->callback);
        }
    }

    public function getUserInfo() {
        $this->checkLogin();
        $info = $this->getInfoByMemId();

        if (is_array($info)) {

            // 判断用户是否已经签到
            $sign_bonus = $this->checkisSign($info['cardno'], $this->merchant['id']);

            if ($sign_bonus == null) {
                $info['sign'] = 0;
            } else {
                $info['sign'] = $sign_bonus;
            }

            $cardList = $this->getCardType();

            if (is_array($cardList)) {
                foreach ($cardList as $v) {
                    if ($v['cardtype_id'] == $info['member_type']) {
                        $info['cardType'] = $v['cardtype_name'];
                        break;
                    }
                }
            }

            $data = array('code' => '200', 'msg' => 'success', 'data' => $info);
        } else {
            $data = array('code' => '2000', 'msg' => 'u are not our member!');
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    protected function getInfoByMemId()
    {
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $this->merchant['signkey'];
        $data['member_id'] = $this->member_id;
        $data['sign'] = sign($data);
        unset($data['sign_key']);

        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/GetUserinfoByID';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array($this->merchant['describe'] . ' get member by member card id' => $curl_re), 'jaleel_logs');

        $data = json_decode($curl_re, true);
        return $data['data'];
    }

    public function getMemCardsList()
    {
        $this->checkLogin();
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $this->merchant['signkey'];
        $data['unionid'] = $this->uid;
        $data['sign'] = sign($data);
        unset($data['sign_key']);

        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/GetUserlistByUID';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array($this->merchant['describe'] . ' get member card list' => $curl_re), 'jaleel_logs');
        $re =  json_decode($curl_re, true);

        if (is_array($re) && $re['code'] == 200) {
            $obj = M('mem', $this->merchant['pre_table']);
            $result = $obj->where(array('unionid' => $this->uid, 'key_admin' => $this->ukey, 'default_card' => 1))->find();

            if (is_array($result) && count($result) > 0) {

                $cardList = $this->getCardType();

                foreach($re['data'] as $k=>$v) {
                    if ($v['member_id'] == $result['member_id']) {
                        $re['data'][$k]['default'] = 1;
                    } else {
                        $re['data'][$k]['default'] = 0;
                    }

                    if (is_array($cardList)) {
                        foreach ($cardList as $val) {
                            if ($val['cardtype_id'] == $v['member_type']) {
                                $type_name = $val['cardtype_name'];
                                break;
                            }
                        }
                    }

                    $re['data'][$k]['cardType'] = $type_name;
                }
            }
        }

        if (!is_array($re['data'])) {
            $re['data'] = array();
        }

        $data = array('code' => '200', 'msg' => 'success', 'data' => $re['data']);
        returnjson($data, $this->returnstyle, $this->callback);
    }

    public function bindCard()
    {
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $this->merchant['signkey'];
        $data['openid'] = $this->user_openid;
        $data['appid'] = $this->merchant['wechat_appid'];
        $data['unionid'] = $this->uid;
        $data['cardno'] = I('cardNo');
        $data['mobile'] = I('mobile');
        $data['sign'] = sign($data);

        $code = I('code');
        $redis_code = $this->redis->get($data['mobile']);

        /*if ($redis_code != $code) {
            $data = array('code' => 404, 'msg' => 'invalid check code!');
            returnjson($data, $this->returnstyle, $this->callback);
            exit;
        }*/
        unset($data['sign_key']);

        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/UserinfoByTie';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array($this->merchant['describe'] . ' bind card' => $curl_re), 'jaleel_logs');
 
        $re_arr = json_decode($curl_re, true);

        if($re_arr['code'] == 200){
            // 记录用户cookie
            $cookie = strtoupper(md5($this->user_openid . rand(1, 1000)));
            setcookie($this->merchant['pre_table'] . 'ck', '', time() - 1);
            cookie($this->merchant['pre_table'] . 'ck', $cookie, array('expire' => time() + 365 * 24 * 3600, 'path' => '/', 'domain' => '.rtmap.com'));
            
            $user=M('mem',$this->merchant['pre_table']);
            // 更新数据表
            $re = $user->where(array('cardno' => $re_arr['data']['cardno']))->save(array('openid' => $this->user_openid, 'cookie' => $cookie));
            if (!$re) {
                $data = array('code' => '1011', 'msg' => 'system error!');
                returnjson($data, $this->returnstyle, $this->callback);
            }
        }
        
        if (is_array($re_arr) && $re_arr['code'] == 200) {
            $data = array('code' => '200', 'msg' => 'success', 'data' => array());
        } else {
            $data = array('code' => $re_arr['code'], 'msg' => $re_arr['msg']);
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    public function setDefaultCard()
    {
        $this->checkLogin();
        $obj = M('mem', $this->merchant['pre_table']);
        $obj->where(array('unionid' => $this->uid, 'key_admin' => $this->ukey))->save(array('default_card' => 2));

        $result = $obj->where(array('member_id' => I('cardId')))->save(array('default_card' => 1));

        if ($result !== false) {
            $data = array('code' => '200', 'msg' => 'success');
        } else {
            $data = array('code' => '1011', 'msg' => 'failed!');
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    public function costDetails()
    {
        $this->checkLogin();
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $this->merchant['signkey'];
        $data['member_id'] = I('cardId');

        $page = I('page');

        $page = isset($page) ? $page : 1;

        $data['page'] = $page;
        $data['lines'] = 10;
        $time_num = I('time_num');
        $data['time_num'] = isset($time_num) ? $time_num : 3;
        $data['sign'] = sign($data);
        unset($data['sign_key']);

        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/UserinfoByConsumptionList';
        $curl_re = http($url, $data, 'post');
//        writeOperationLog(array($this->merchant['describe'] . ' get cost details' => $curl_re), 'jaleel_logs');
        $re = json_decode($curl_re, true);

        // 查询卡类别
        $obj = M('mem', $this->merchant['pre_table']);
        $result = $obj->where(array('member_id' => $data['member_id'], 'key_admin' => $this->ukey))->find();

        if (is_array($result) && count($result) > 0) {


            // select card type list(including card type num and card type name)
            $cardList = $this->getCardType();
            if (is_array($cardList)) {
                foreach ($cardList as $val) {
                    if ($val['cardtype_id'] == $result['member_type']) {
                        $return['cardType'] = $val['cardtype_name'];
                        break;
                    }
                }
            }
        } else {
            $return['cardType'] = '';
        }

        if (is_array($re) && $re['code'] == 102) {
            $return['totalFee'] = 0;
            $return['costList'] = array();
            $data = array('code' => '200', 'msg' => 'success', 'data' => $return);
        } else if (is_array($re) && $re['code'] == 200) {

            $total = 0;

            foreach ($re['data'] as $v) {
                $total += $v['money_num'];
            }

            $return['totalFee'] = $total;
            $return['costList'] = $re['data'];

            $data = array('code' => '200', 'msg' => 'success', 'data' => $return);
        } else {
            $data = array('code' => $re['code'], 'msg' => $re['msg'], 'data' => array());
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    protected function checkisSign($cardno, $adminid){
        //从最后一次签到表中查询
        $db=M('last_history','sign_');
        $find=$db->field('cardno,lastdate,totalday')->where(array('cardno'=>$cardno,'lastdate'=>date('Y-m-d'),'adminid'=>$adminid))->find();
        if (null != $find){
            $dbsign=M('history','sign_');
            $sign=$dbsign->where(array('signdate'=>date('Y-m-d'),'cardno'=>$cardno,'adminid'=>$adminid))->find();
            if (isset($sign['scores'])){
                $find['scores']=$sign['scores'];
            }
        }
        return (int)$find['scores'];
    }

    public function unbind() {
        $this->checkLogin();
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $this->merchant['signkey'];
        $data['member_id'] = I('cardId');
        $data['unionid'] = $this->uid;
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/member_untie';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array($this->merchant['describe'] . ' unbind card' => $curl_re), 'jaleel_logs');
        $re = json_decode($curl_re, true);

        if (is_array($re) && $re['code'] == 200) {

            $obj = M('mem', $this->merchant['pre_table']);
            $obj->where(array('member_id' => $data['member_id']))->save(array('unionid' => ''));

            $unbind_card = $obj->where(array('member_id' => $data['member_id']))->find();
            if ($unbind_card['default_card'] == 1) {
                $result = $obj->where(array('unionid' => $this->uid, 'key_admin' => $this->ukey))->find();

                if (is_array($result) && count($result) > 0) {
                    $obj->where(array('member_id' => $result['member_id']))->save(array('default_card' => 1));
                }
            }

            $data = array('code' => '200', 'msg' => 'success', 'data' => $re['data']);
        } else {
            $data = array('code' => $re['code'], 'msg' => $re['msg'], 'data' => array());
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    public function bonusDetails() {
        $this->checkLogin();
        $data['key_admin'] = $this->ukey;
        $data['sign_key'] = $this->merchant['signkey'];
        $data['member_id'] = I('cardId');

        $page = I('page');
        $page = isset($page) ? $page : 1;

        $data['page'] = $page;
        $data['lines'] = 10;
        $data['sign'] = sign($data);
        unset($data['sign_key']);
        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/scorelist';
        $curl_re = http($url, $data, 'post');
        writeOperationLog(array($this->merchant['describe'] . ' get cost details' => $curl_re), 'jaleel_logs');
        $re = json_decode($curl_re, true);

        if (is_array($re) && $re['code'] == 200) {
            $info = $this->getInfoByMemId($data['member_id']);

            if (is_array($info)) {
                $re['data']['bonus'] = $info['score_num'];
            }

            $data = array('code' => '200', 'msg' => 'success', 'data' => $re['data']);
        } else {
            $data = array('code' => $re['code'], 'msg' => $re['msg'], 'data' => array());
        }

        returnjson($data, $this->returnstyle, $this->callback);
    }

    protected function getCardType() {
        $d['key_admin'] = $this->ukey;
        $d['sign_key'] = $this->merchant['signkey'];
        $d['sign'] = sign($d);
        unset($d['sign_key']);

        $url = C('DOMAIN') . '/CrmService/OutputApi/Index/cardtype_list';
        $curl_re = http($url, $d, 'post');
        writeOperationLog(array($this->merchant['describe'] . ' get card type name list' => $curl_re), 'jaleel_logs');
        $re_data = json_decode($curl_re, true);

        if (is_array($re_data) && $re_data['code'] == 200) {
            return $re_data['data'];
        } else {
            return false;
        }
    }
}