<?php
namespace ResourcesApi\Controller;
use Think\Controller;
use Common\Controller\RedisController as A;
/**
 * 会员对外接口
 * @author soone
 * @date 2017-07-22
 */
class MemberController extends ResCommonController
{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 根据key_admin获取会员卡类型
     * @param array
     * @return mixed
     */
    public function getCardType() {
        $db = M('member_code', 'total_');
        $field = "code as cardtype,name,imgurl";
        $data = $db->field($field)->where(array('admin_id'=>$this->setting['id']))->select();
        $msg = array('code'=>200,'data'=>$data);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 根据卡类型获取会员列表
     * @param array
     * @return mixed
     */
    public function getMemberListByCardType() {
        $params = $this->params;
        $this->emptyCheck($params,array('cardtype','offset','page'));
        $page = ((int)$params['page'])<=0?1:((int)$params['page']);
        $offset = ((int)$params['offset'])<=0?1:((int)$params['offset'])>200?200:((int)$params['offset']);
        $start = ($page-1)*$offset;
        $db = M('mem',$this->setting['pre_table']);
        $where = array('level'=>(string)$params['cardtype']);
        $field = "usermember,cardno,mobile,openid,level";
        $arr=$db->field($field)->where($where)->order("id asc")->limit($start,$offset)->select();
        $count =$db->where($where)->count();
        $allpage = ceil($count/$offset);
        $msg = array('code'=>200,'data'=>array('pageall'=>$allpage,'countall'=>$count,'curpage'=>$page,'data'=>$arr));
        returnjson($msg, $this->returnstyle, $this->callback);
    }

}

?>