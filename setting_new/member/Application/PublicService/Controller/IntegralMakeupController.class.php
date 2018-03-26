<?php
namespace PublicService\Controller;

use PublicApi\Controller\QiniuController;
use Common\Service\UploadService;
// use Thirdwechat\Controller\Thirdwechat\EventsController;

class IntegralMakeupController extends  QiniuController{

    /*
     * 积分补录
     */
    public function file_weixin_qiniu(){

        $params['media_id']=I('media_id');
        //$params['media_id']='-eiUsrRup0rDRSyBXPnszGWALRLkHb2znADm_m58j6IwDrSuFQqAUyxC4CdU9pXB';
        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['ext']=I('ext')?I('ext'):'';
            $arr['IntegralMakeup_params']=$params;//记录微信返回错误json


            $total_arr=$this->getMerchant($params['key_admin']);

            $upload = new UploadService();

            $return = $upload->FetchWeChatQiniu($total_arr['wechat_appid'] , $params['media_id'] , $params['ext']);

            $arr['IntegralMakeup_Qiniu_Up_return']=$return;//记录微信返回错误json

            if($return['code'] == 200){

                $pre_db=M('mem',$total_arr['pre_table']);
                $pre_arr=$pre_db->where(array('openid'=>$params['openid']))->find();

                if($pre_arr){

                    $pre_score_db=M('score_type',$total_arr['pre_table']);

                    $data['img_src']="https://img.rtmap.com/".$return['data'];
                    $data['createtime']=date('Y-m-d H:i:s');
                    $data['status']=1;
                    $data['user_mobile']=$pre_arr['mobile'];
                    $data['username']=$pre_arr['usermember'];
                    $data['cardno']=$pre_arr['cardno'];
                    $data['openid']=$params['openid'];

                    $score_type_res=$pre_score_db->add($data);

                    $arr['IntegralMakeup_sql']=$pre_score_db->_sql();
                    $arr['IntegralMakeup_sql_status']=$score_type_res;
                    if($score_type_res){
                        $msg['code']=200;
                    }else{
                        $msg['code']=104;
                    }

                }else{
                    $msg['code']=2000;
                }
            }else{
                $msg['code']=1044;
            }
            writeOperationLog($arr,'IntegralMakeup');
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /*
    * 积分补录(支付宝)
    */
    public function file_alipay_qiniu(){

        $params['key_admin']=I('key_admin');
        $params['userid']=I('userid');
        $params['imgUrl']=I('imgUrl');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $total_arr=$this->getMerchant($params['key_admin']);
            $upload = new UploadService();
            $return = $upload->FetchImgQiniu($params['imgUrl']);
            if($return['code'] == 200){
                $pre_db=M('mem',$total_arr['pre_table']);
                $pre_arr=$pre_db->where('`userid` = '. $params['userid'])->find();
                if($pre_arr){
                    $pre_score_db=M('score_type',$total_arr['pre_table']);
                    $data['img_src']="https://img.rtmap.com/".$return['data'];
                    $data['createtime']=date('Y-m-d H:i:s');
                    $data['status']=1;
                    $data['user_mobile']=$pre_arr['mobile'];
                    $data['username']=$pre_arr['usermember'];
                    $data['cardno']=$pre_arr['cardno'];
                    $score_type_res=$pre_score_db->add($data);

                    if($score_type_res){
                        $msg['code']=200;
                    }else{
                        $msg['code']=104;
                    }
                }else{
                    $msg['code']=2000;
                }
            }else{
                $msg['code']=1044;
            }
        }
        echo returnjson($msg,$this->returnstyle,$this->callback);exit();
    }

    /**
     *用户积分记录列表
     * @openid 用户的openid
     * @starttime 开始时间
     * @starttime 结束时间
     * @status  审核状态
     * @page   页码
     * @param key_admin 商家key
     ***/
    public  function recordList(){


        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        $params['page']=I('page');
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{

            $total_arr=$this->getMerchant($params['key_admin']);

            $starttime=I('starttime');
            $stoptime=I('endtime');
            $status = I('status');

            $where='';
            //查询条件
            //用户openid

            $where .= ' `openid` = "'.$params['openid'].'" ';

            if (!empty($starttime)){

                $where .= 'and  `createtime` > "'.$starttime.'" ';
            }
            if (!empty($stoptime)){
                $stoptime=date('Y-m-d', strtotime("$stoptime +1 day"));
                if ($where != ''){
                    $where .= ' and ';
                }
                $where .= ' `createtime` < "'.$stoptime.'"';
            }
            //状态
            if (!empty($status)){
                if ($where != ''){
                    $where .= ' and ';
                }
                $where .= ' `status` = "'.$status.'"';
            }

            $db=M('score_type',$total_arr['pre_table']);
            $re_arr=$db->where($where)->select();

            $lines=2;
            $count=ceil(count($re_arr)/$lines);

            if($params['page']==0){
                $params['page']=1;
            }else if($params['page']>$count){
                $params['page']=$count;
            }

            $end=($params['page']-1)*$lines;
            $end = 0 > $end ? 0 : $end;

            $field=array('id','createtime','status','score_number');
            $res=$db->field($field)->where($where)->limit($end,$lines)->order('createtime desc')->select();

            if($res) {

                $msg['code'] = 200;
                $msg['data']['data'] = $res;
                $msg['data']['page'] = $params['page'];//当前页数
                $msg['data']['count'] = $count;//页数
                $msg['data']['total'] = count($re_arr);
            }else{

                $msg['code']=102;
            }
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();
        }

    }

    /**
     *获取单条积分的数据
     * @param  openid 用户的openid
     * @param key_admin 商家Key
     * @param  id  记录的Id
     ***/
    public function recordDedail(){

        $params['key_admin']=I('key_admin');
        $params['openid']=I('openid');
        $params['id']=I('id');

        if(in_array('', $params)){
            $msg['code']=1030;
        }else{

            $total_arr=$this->getMerchant($params['key_admin']);
            $db=M('score_type',$total_arr['pre_table']);
            $data=$db->where(array('id'=>$params['id'],'openid'=>$params['openid']))->find();
            if($data) {
                if (empty($data['img_src'])) {

                    $db = M('score_img_src', $total_arr['pre_table']);
                    $pic_url = $db->field('img_src')->where(array('score_id' => $params['id']))->select();

                    $pic = array();
                    foreach ($pic_url as $key => $pics) {

                        $pic[$key] = $pics['img_src'];
                    }
                    $data['img_src'] = $pic;

                }
                $msg['code'] = 200;
                $msg['data'] =$data;
            }else{

                $msg['code']=102;
            }
            echo returnjson($msg,$this->returnstyle,$this->callback);exit();

        }

    }

    /**
     *多图保存
     * @param id 补录的主键
     * @param prefix 表前缀
     * @param img_src 图片地址
     **/
    public function pictureSave($id,$prefix,$img_src){

        $db=M('score_img_src',$prefix);

        $data=array('img_src'=>$img_src,'score_id'=>$id);

        $db->add($data);


    }



}
?>

