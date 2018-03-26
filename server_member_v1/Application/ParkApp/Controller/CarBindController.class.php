<?php
/**
 * 车辆绑定相关
 * User: soone
 * Date: 17-7-12
 * Time: 下午4:48
 */

namespace ParkApp\Controller;

use Common\Controller\JaleelController;

class CarBindController extends JaleelController
{
    /**
     * 获取各省份车牌号
     */
    public function getCarCode() {
        $carcode = C('CAR_CODE');
        $msg = array('code'=>200,'data'=>$carcode);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 获取用户车辆列表
     */
    public function getUserCarList() {
        $mer_chant = $this->getMerchant($this->ukey);
        $cardb = M('carpay_bind', $mer_chant['pre_table']);
        $re = $cardb->where(array('userid' =>$this->userucid,'from'=>$this->from))->limit(50)->select();
        $msg = array('code'=>200,'data'=>$re);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 添加用户车辆关联
     */
    public function addCarRelation() {
        $carcode = I("carcode");
        if(empty($carcode) || empty($this->userucid)){
            returnjson(array('code'=>11,"msg"=>"参数错误！"), $this->returnstyle, $this->callback);
        }
        $mer_chant = $this->getMerchant($this->ukey);
        $cardb = M('carpay_bind', $mer_chant['pre_table']);
        $re = $cardb->where(array('carcode' =>$carcode))->find();
        if($re){
            returnjson(array('code'=>12,"msg"=>"该车牌号已经被绑定,不可重复绑定！"), $this->returnstyle, $this->callback);
        }
        //检查是否超出绑定限制
        $bindLimit = $this->GetOneAmindefault($mer_chant['pre_table'],$this->ukey,'carrelationlimit');
        if(!empty($bindLimit['function_name']) && ((int)$bindLimit['function_name'])!=0){
            $carnum = $cardb->where(array('userid' =>$this->userucid))->count();
            if($carnum>=((int)$bindLimit['function_name'])){
                $alertmsg = "抱歉,您的账户最多可以绑定".$bindLimit['function_name']."辆车！";
                returnjson(array('code'=>13,"msg"=>$alertmsg), $this->returnstyle, $this->callback);
            }
        }
        $insertArr = array(
            'userid'=>$this->userucid,
            'from'=>$this->from,
            'carcode'=>$carcode
        );
        $cardb->add($insertArr);
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }

    /**
     * 删除用户车辆关联
     */
    public function delCarRelation() {
        $carcode = I("carcode");
        if(empty($carcode) || empty($this->userucid)){
            returnjson(array('code'=>11,"msg"=>"参数错误！"), $this->returnstyle, $this->callback);
        }
        $mer_chant = $this->getMerchant($this->ukey);
        $cardb = M('carpay_bind', $mer_chant['pre_table']);
        $re = $cardb->where(array('carcode' =>$carcode,'userid'=>$this->userucid))->delete();
        $msg = array('code'=>200);
        returnjson($msg, $this->returnstyle, $this->callback);
    }


}