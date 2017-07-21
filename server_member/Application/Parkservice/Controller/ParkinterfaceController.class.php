<?php
namespace Parkservice\Controller;


/**
 * 
 * @author kaifeng
 * 如果是对接的自己写的第三方类，则直接把接收到的内容返回
 *
 */
interface ParkinterfaceController
{

    /**
     * 获取剩余车位数
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getleftpark($sign_key,$key_admin);
    
    
    /**
     * 搜索车辆列表
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function searchcar($carno,$sing_key,$key_admin,$page,$lines);
    
    /**
     * 从列表中选择我的车辆信息
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function choosemycar($carno,$sing_key,$key_admin);
    
    /**
     * 支付状态确认
     *  正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function paystatus($carno,$sign_key,$paytype,$key_admin);
    
    
    /**
     * 车场车位状态
     * @param unknown $floor
     * @param unknown $build
     * @param unknown $sign_key
     * @param unknown $key_admin
     * 正确数据以数组方式返回，否则，返回的是一个错误码
     */
    public function getparkstatus($build,$floor,$sign_key,$key_admin,$admininfo);
}

?>