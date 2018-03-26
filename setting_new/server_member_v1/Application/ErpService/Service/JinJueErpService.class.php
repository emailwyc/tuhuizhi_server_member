<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 12/07/16
 * Time: 18:09 PM
 */

namespace ErpService\Service;

class JinJueErpService implements ErpInterface
{
    public static  $params=array(
        'strCallUserCode'=>'FZ',
        'strCallPassword'=>123456
    );
    public static  $url='http://222.171.50.49:9000/ws_member.asmx/';

    /**
     * 通过扫码获取小票信息
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function receiveInfoByScan($code, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement receiveInfoByScan() method.
    }

    /**
     * 通过手动编写信息获取小票信息
     * @param $code
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function receiveInfoByWrite($code, $adminInfo=null, $adminDefault=null)
    {
        // TODO: Implement receiveInfoByWrite() method.
    }

    /**
     * 调用erp接口，erp赠送积分
     * @param $params
     * @param $adminInfo
     * @param $adminDefault
     * @return mixed
     */
    public static function giveScoreByReceive($params, $adminInfo=null, $adminDefault=null, $receiptInfo=null)
    {
        // TODO: Implement giveScoreByReceive() method.
    }

    //获取ftp 图片方法
    private static function qi_fetch($imgname){
        $ftp_server="http://www.hrbjjwx.com.cn";          //定义ftp服务器
//       $ftp_user="admin";            //定义用户名
//       $ftp_pass="A1abcdWEB";            //定义用户对应的密码
//       $conn_id=ftp_connect($ftp_server)or die("couldn't connect to $ftp_server"); //连接到指定ftp服务器
//       if(@ftp_login($conn_id,$ftp_user,$ftp_pass))       //如果成功登录
//       { 
//           $here = ftp_pwd($conn_id);
//           $path=RUNTIME_PATH.'wechat/fans/'.$imgname;
//           if(ftp_get($conn_id,$path,$imgname,FTP_BINARY)){
//               $qiniu=new QiniuController;
//               list($ret, $err)=$qiniu->uploadfile($path,$imgname);
//               unlink($path);
//               return 'https://img.rtmap.com/'.$imgname;
//           }else{
//               return false;
//           }
//       }
//       else
//      {
//           return false;        //输出不能登录的信息
//       }
        return $ftp_server.'/tp/'.$imgname;
    }

    /**
     *  获取兑换记录列表
     */
    public static function getExchangeList($paramss, $adminInfo=null, $adminDefault=null){
        $params['strMemberCode']=$paramss['cardno'];
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $params['strStartRow']=$paramss['startlines']?$paramss['startlines']:1;
            $params['strEndRow']=$paramss['endlines']?$paramss['endlines']:99999;
            $return_arr=self::prize_action($params,'GetMemberRedeem');
            $par['jinjue_prize_change']=$return_arr;
            writeOperationLog($par,'zhanghang');
            if(!empty($return_arr)){
                $msg['code']=200;
                $data=array();

                if(count($return_arr['SalesRecord']) != count($return_arr['SalesRecord'],1)){
                    foreach($return_arr['SalesRecord'] as $k=>$v){
                        $activity[] = $v['MALLID'];
                        $res['lines']=$v['ROWNUM'];
                        $res['cardno']=$v['VIPCODE'];
                        $res['username']=$v['SURNAME'];
                        $res['change_num']=$v['CHANGEQTY'];
                        $res['prize_id']=$v['GIFTCODE'];
                        $res['get_time']=$v['TXDATE'];
                        $res['prize_name']=$v['GIFTNAME'];
                        $res['qr'] = $v['DOCNO'];
                        $res['desc'] = '';
                        if($v['ISRECEIVE'] == '未领取'){
                            $res['status']=2;
                        }else if($v['ISRECEIVE'] == '已领取'){
                            $res['status']=3;
                        }else{
                            $res['status']=5;
                        }
                        $data['data'][]=$res;
                    }
                }else{
                    $res['lines']=$return_arr['SalesRecord']['ROWNUM'];
                    $res['cardno']=$return_arr['SalesRecord']['VIPCODE'];
                    $res['username']=$return_arr['SalesRecord']['SURNAME'];
                    $res['change_num']=$return_arr['SalesRecord']['CHANGEQTY'];
                    $res['prize_id']=$return_arr['SalesRecord']['GIFTCODE'];
                    $res['get_time']=$return_arr['SalesRecord']['TXDATE'];
                    $res['prize_name']=$return_arr['SalesRecord']['GIFTNAME'];
                    $res['qr'] = $return_arr['SalesRecord']['DOCNO'];
                    $res['desc'] = '';
                    $activity[] = $return_arr['SalesRecord']['MALLID'];
                    if($return_arr['SalesRecord']['ISRECEIVE'] == '未领取'){
                        $res['status']=2;
                    }else if($return_arr['SalesRecord']['ISRECEIVE'] == '已领取'){
                        $res['status']=3;
                    }else{
                        $res['status']=5;
                    }
                    $data['data'][]=$res;
                }

                $params['strMallId']='JJWX';
                $url=self::$url.'GetMemberGiftList';

                $paramsparams = self::$params;
                $paramsparams['strMallId']=$params['strMallId'];
                $paramsparams['strIsHaveQoh']='N';
                $return_xml=http($url,$paramsparams);
                $return_arr=xmltoarray($return_xml);

                $return_list = self::prizedataaction($return_arr);//获取所有券列表

                foreach($return_list as $k=>$v){
                    $return_list_new['pid'] = $v['id'];
                    $return_list_new['imgUrl'] = $v['image_url'];
                    $return_list_new['shopName'] = $v['main_info'];
                    $return_list_new['main'] = $v['main_info'];
                    $return_list_new['endTime'] = $v['end_time'];
                    $return_list_new['position'] = $v['position'];
                    $save_list_data[$v['id']] = $return_list_new;
                }

                foreach($save_list_data as $k=>$v){
                    foreach($data['data'] as $key=>$val){
                        if($k == $val['prize_id']){
                            $return_data['data'][$key] = array_merge($v,$val);
                        }
                    }
                }

                $data['startlines']=$params['strStartRow'];
                $data['endlines']=$params['strEndRow'];
                $msg['data']=$return_data;
            }else{
                $msg['code']=102;
            }
        }
        return $msg;
    }


    /**
     *  兑换礼品接口
     */
    public static function prizeExchange($paramss, $adminInfo=null, $adminDefault=null){
        $params['strMemberCode']=$paramss['cardno'];
        $params['strGiftCode']=$paramss['pid'];
        $params['strMallId']=$paramss['activity']?$paramss['activity']:'JJWX';
        $params['strRedeemQty']=1;
        if(in_array('', $params)){
            $msg['code']=1030;
        }else{
            $return_arr=self::prize_action($params,'GetMemberGiftRedeem');
            $par['jinjue_prize_exchange']=$return_arr;
            writeOperationLog($par,'zhanghang');
            if($return_arr['Error']){
                $msg['code']=104;
                $msg['msg']=$return_arr['Error']['Description'];
            }else{
                $msg['code']=200;
            }
        }
        return $msg;
    }


    /**
     *  礼品退还接口
     */
    public static function prizeReturn($paramss, $adminInfo=null, $adminDefault=null){
        $params['strMemberCode']=$paramss['cardno'];
        $params['strGiftCode']=$paramss['pid'];
        $params['strMallId']=$paramss['activity']?$paramss['activity']:'JJWX';
        $params['strRedeemQty']=-1;
        if(!in_array('', $params)){
            $msg['code']=1030;
        }else{
            $return_arr=self::prize_action($params,'GetMemberGiftRedeem');
            if($return_arr['Success']['ReturnCode']==0){
                $msg['code']=200;
            }else{
                $msg['code']=1018;
                $msg['msg']=$return_arr['Success']['Description'];
            }
        }
        return $msg;
    }

    protected static function prize_action($params,$action){
        $url=self::$url.$action;
        $params['strCallUserCode']=self::$params['strCallUserCode'];
        $params['strCallPassword']=self::$params['strCallPassword'];
        $return_xml=http($url,$params);
        $return_arr=xmltoarray($return_xml);
        return $return_arr;
    }


    /**
     *  获取会员礼品列表
     */
    public static function prizeList($paramss, $adminInfo=null, $adminDefault=null){
        $params['strMallId']=$paramss['activity']?$paramss['activity']:'JJWX';
        $url=self::$url.'GetMemberGiftList';
        $paramsparams = self::$params;
        $paramsparams['strMallId']=$params['strMallId'];
        $paramsparams['strIsHaveQoh']=$paramss['status']?$paramss['status']:'N';
        $return_xml=http($url,$paramsparams);
        $return_arr=xmltoarray($return_xml);
        if(!empty($return_arr)){
            $msg['code']=200;
            $data=array();
            $data = self::prizedataaction($return_arr);
            $msg['data']=$data;
        }else{
            $msg['code']=1018;
            $msg['msg']='错误';
        }
        return $msg;
    }

    private static function prizedataaction($return_arr){
        if(count($return_arr['GiftRecord']) != count($return_arr['GiftRecord'],1)){
            foreach($return_arr['GiftRecord'] as $k=>$v){
                $res_arr['id']=$v['ITEMNO'];
                $res_arr['integral']=$v['BONUS'];
                $res_arr['main_info']=$v['ITEMDESCI'];
                $res_arr['num']=$v['QOH'];
                $res_arr['issue']=0;
                $res_arr['start_time']=$v['EFFECTDATE'];
                $res_arr['end_time']=$v['CUTOFFDATE'];
                $res_arr['status']=0;
                $res_arr['writeoff_count']=0;
                $res_arr['position']=$v['LONGDESCI'];
                $res_arr['image_url']=self::qi_fetch($v['ITEMNO'].'.jpg')?self::qi_fetch($v['ITEMNO'].'.JPG'):'';
                $data[]=$res_arr;
            }
        }else{
            $res_arr['id']=$return_arr['GiftRecord']['ITEMNO'];
            $res_arr['integral']=$return_arr['GiftRecord']['BONUS'];
            $res_arr['main_info']=$return_arr['GiftRecord']['ITEMDESCI'];
            $res_arr['num']=$return_arr['GiftRecord']['QOH'];
            $res_arr['issue']=0;
            $res_arr['start_time']=$return_arr['GiftRecord']['EFFECTDATE'];
            $res_arr['end_time']=$return_arr['GiftRecord']['CUTOFFDATE'];
            $res_arr['status']=0;
            $res_arr['writeoff_count']=0;
            $res_arr['position']=$return_arr['GiftRecord']['LONGDESCI'];
            $res_arr['image_url']=self::qi_fetch($return_arr['GiftRecord']['ITEMNO'].'.jpg')?self::qi_fetch($return_arr['GiftRecord']['ITEMNO'].'.JPG'):'';
            $data[]=$res_arr;
        }
        return $data;
    }
}