<?php
/**
 * 停车后台类
 * User: jaleel
 * Date: 10/18/16
 * Time: 7:49 PM
 */

namespace MerAdmin\Controller;

use Common\Controller\JaleelController;

class ParkController extends JaleelController
{
    /**
     * 查询订单列表接口
     * @param key_admin 商户的key
     */
    public function getOrderList()
    {

        // 验证为空性
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 接收搜索条件 可能一个条件都没有
        $status = I('status'); // 1代表未付款 2代表已付款未开发票 3代表已付款并开发票
        $create_time = I('create_time'); // 订单创建时间
        $pay_time = I('pay_time'); // 订单付款时间
        $invoice_time = I('invoice_time'); // 订单开票时间
        $openid = I('openid'); // 用户openid或者用户名
        $orderno = I('orderno'); // 订单编号
        $carno = I('carno'); // 订单编号
        $page = I('page');


        // 查询总的记录数

        //writeOperationLog(array('后台查询停车缴费记录参数' . 'status:' . $status . ',create_time:' . $create_time . ',pay_time:' . $pay_time . ',invoice_time:' . $invoice_time . ',openid:' . $openid . ',orderno:' . $orderno . ',page:' . $page), 'jaleel_logs');

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $where = array();

        // 拼搜索条件
        if (isset($status)) {
            if ($status == 1) {
                $where['status'] = 0; // 未付款
            } else if ($status == 2) {
                $where['status'] = 2; // 付款成功
//                $where['invoice_time'] = 0; // 未来开发票
            }
        }

        if (!empty($create_time)) {
            $where['createtime'] = array('between', array(strtotime($create_time . ' 00:00:00'), strtotime($create_time . ' 23:59:59')));
        }

        if (!empty($pay_time)) {
            $where['pay_time'] = array('between', array(strtotime($pay_time . ' 00:00:00'), strtotime($pay_time . ' 23:59:59')));
        }

        if (!empty($invoice_time)) {
            $where['invoice_time'] = array('between', array(strtotime($invoice_time . ' 00:00:00'), strtotime($invoice_time . ' 23:59:59')));
        }

        if (!empty($openid)) {

            if(is_numeric($openid)){
                //获取openid
                $user = M('mem', $mer_chant['pre_table']);
                $user_list = $user->field('openid')->where(array('mobile' => $openid,'openid'=>array('neq','')))->find();

                if($user_list&&!empty($user_list['openid'])) {

                    $where['openid'] = $user_list['openid'];
                }else{
                    $where['openid'] = $openid;
                }
            }else{

                $where['openid'] = $openid;
            }

        }

        if (!empty($orderno)) {
            $where['orderno'] = $orderno;
        }

        if (!empty($carno)) {
            $where['carno'] = array('like','%'.$carno.'%');;
        }

        $page = isset($page) ? $page : 1;
        $show_num = 10;

        $order = M('carpay_order', $mer_chant['pre_table']);
        if (count($where) > 0) {
            $count = $order->where($where)->count('createtime');
            $orders = $order->where($where)->page($page, $show_num)->order('createtime desc')->select();
        } else {
            $count = $order->count('createtime');
            $orders = $order->page($page, $show_num)->order('createtime desc')->select();
        }

        $total_page = ceil($count / $show_num);
        // 按openid查询每个订单的用户手机号并返回给前端
        if (is_array($orders) && !empty($orders)) {

            foreach ($orders as $k=>$v) {
                $ids_arr[] = $v['openid'];
                if ($v['paytype'] == 0) {
                    $orders[$k]['total_fee'] = $v['total_fee'] / 100; // 库中存的单位是分 转化成元
                    $orders[$k]['payfee'] = $v['payfee'] / 100; // 库中存的单位是分 转化成元
                }
                if($v['pay_time']=='0'){

                    $orders[$k]['pay_time']='';
                }
            }

            $openid_str = implode(',', array_unique($ids_arr));

            $user = M('mem', $mer_chant['pre_table']);
            $user_list = $user->where(array('openid' => array('in', $openid_str)))->select();

            if (is_array($user_list)) {
                foreach ($user_list as $v) {
                    foreach ($orders as $k => $value) {

                        if ($value['openid'] == $v['openid']) {
                            $orders[$k]['mobile'] = $v['mobile'];
                        }

                        if($value['pay_time']=='0'){

                            $orders[$k]['pay_time']="";

                        }
                    }

                }
            }
        }
//        writeOperationLog(array('后台查询停车缴费记录sql' . $order->_sql()), 'jaleel_logs');

        $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $orders, 'page' => array('page' => $page, 'total_page' => $total_page));
        returnjson($data, $this->returnstyle, $this->callback);
    }


    public function getOrderStatistic()
    {

        // 验证为空性
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 接收搜索条件 可能一个条件都没有
        $status = I('status'); // 1代表未付款 2代表已付款未开发票 3代表已付款并开发票
        $create_time = I('create_time'); // 订单创建时间
        $pay_time = I('pay_time'); // 订单付款时间
        $invoice_time = I('invoice_time'); // 订单开票时间
        $openid = I('openid'); // 用户openid
        $orderno = I('orderno'); // 订单编号
        $carno = I('carno'); // 订单编号
        $page = I('page');

        // 查询总的记录数

        //writeOperationLog(array('后台查询停车缴费记录参数' . 'status:' . $status . ',create_time:' . $create_time . ',pay_time:' . $pay_time . ',invoice_time:' . $invoice_time . ',openid:' . $openid . ',orderno:' . $orderno . ',page:' . $page), 'jaleel_logs');

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $where = array();

        // 拼搜索条件
        if (isset($status)) {
            if ($status == 1) {
                $where['status'] = 0; // 未付款
            } else if ($status == 2) {
                $where['status'] = 2; // 付款成功
//                $where['invoice_time'] = 0; // 未来开发票
            }
        }

        if (!empty($create_time)) {
            $where['createtime'] = array('between', array(strtotime($create_time . ' 00:00:00'), strtotime($create_time . ' 23:59:59')));
        }

        if (!empty($pay_time)) {
            $where['pay_time'] = array('between', array(strtotime($pay_time . ' 00:00:00'), strtotime($pay_time . ' 23:59:59')));
        }

        if (!empty($invoice_time)) {
            $where['invoice_time'] = array('between', array(strtotime($invoice_time . ' 00:00:00'), strtotime($invoice_time . ' 23:59:59')));
        }

        if (!empty($openid)) {

            if(is_numeric($openid)){
                //获取openid
                $user = M('mem', $mer_chant['pre_table']);
                $user_list = $user->field('openid')->where(array('mobile' => $openid,'openid'=>array('neq','')))->find();
                if($user_list&&!empty($user_list['openid'])) {
                    $where['openid'] = $user_list['openid'];
                }else{
                    $where['openid'] = $openid;
                }
            }else{

                $where['openid'] = $openid;
            }

        }

        if (!empty($orderno)) {
            $where['orderno'] = $orderno;
        }

        if (!empty($carno)) {
            $where['carno'] = array('like','%'.$carno.'%');;
        }

        $page = isset($page) ? $page : 1;
        $show_num = 10;

        $order = M('carpay_order', $mer_chant['pre_table']);

        $where['paytype']= 0;
        $money1 = $order->field("sum(total_fee) as total_fee,sum(payfee) as payfee")->where($where)->select();
        $where['paytype']= 1;
        $score1 = $order->field("sum(total_fee) as total_fee,sum(payfee) as payfee")->where($where)->select();
        $statistic = array(
            'money'=>$money1,
            'score'=>$score1,
        );

//        writeOperationLog(array('后台查询停车缴费记录sql' . $order->_sql()), 'jaleel_logs');

        $data = array('code' => '200', 'msg' => 'SUCCESS!','statistic'=>$statistic);
        returnjson($data, $this->returnstyle, $this->callback);
    }


    /**
     * 停车缴费标准添加及更新
     * @param key_admin 商户的key
     * @param content 缴费标准图文
     */
    public function designParkIntro()
    {
        $content = I('content'); // 接收编辑过后的文本

        // 验证为空性
        if (!$this->ukey or !$content) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $intro_obj = M('default', $mer_chant['pre_table']);
        $intro = $intro_obj->where(array('customer_name' => 'carpayintro'))->find();

        $data['customer_name'] = 'carpayintro';
        $data['description'] = '停车缴费收费标准';
        $data['function_name'] = $content;

        // 之前没有添加过
        if (!$intro) {
            $re = $intro_obj->add($data);
        } else { // 添加过则进行更新
            $re = $intro_obj->where(array('customer_name' => 'carpayintro'))->save($data);
            $this->redis->del('admin:default:one:'.$data['customer_name'].':'. $mer_chant['ukey']);
        }

        if ($re === false) {
            $data = array('code' => '1011', 'msg' => '系统错误!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $data = array('code' => '200', 'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 优惠配置接口
     * @param key_admin 商户的key
     */
    public function discountConf()
    {
        $discount = I('discount');
        $freetime = I('freetime');
        $scorepay = I('scorepay');

        // 验证为空性
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        // 默认情况下折扣 免费时长和积分支付为未开启状态
        $is_discount = 0;
        $is_freetime = 0;
        $is_scorepay = 0;

        // 开启折扣
        if (!empty($discount)) {
            $is_discount = 1;

            // 更新折扣配置
        }

        // 开启免费时长
        if (!empty($freetime)) {
            $is_freetime = 1;

            // 更新免费时长配置
        }

        // 开启积分支付
        if (!empty($scorepay)) {
            $is_scorepay = 1;
        }

        // 开启则进行更新数据库存
        if ($is_discount == 1 or $is_freetime == 1 or $is_scorepay == 1) {
            $data['is_scorepay'] = $is_scorepay;
            $data['is_freetime'] = $is_freetime;
            $data['is_discount'] = $is_discount;
            $merchant = M('total_admin');
            $merchant->where(array('id' => $mer_chant['id']))->save($data);
        }


    }

    /**
     * 导出停车缴费定单记录
     */
    public function exportParkOrders()
    {


        // 验证为空性
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 接收搜索条件 可能一个条件都没有
        $status = I('status'); // 1代表未付款 2代表已付款未开发票 3代表已付款并开发票
        $create_time = I('create_time'); // 订单创建时间
        $pay_time = I('pay_time'); // 订单付款时间
        $invoice_time = I('invoice_time'); // 订单开票时间
        $openid = I('openid'); // 用户openid
        $orderno = I('orderno'); // 订单编号
        $carno = I('carno'); // 订单编号
       // $page = I('page');

        // 查询总的记录数

        //writeOperationLog(array('后台查询停车缴费记录参数' . 'status:' . $status . ',create_time:' . $create_time . ',pay_time:' . $pay_time . ',invoice_time:' . $invoice_time . ',openid:' . $openid . ',orderno:' . $orderno . ',page:' . $page), 'jaleel_logs');

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $where = array();

        // 拼搜索条件
        if (isset($status)) {
            if ($status == 1) {
                $where['status'] = 0; // 未付款
            } else if ($status == 2) {
                $where['status'] = 2; // 付款成功
//                $where['invoice_time'] = 0; // 未来开发票
            }
        }

        if (!empty($create_time)) {
            $where['createtime'] = array('between', array(strtotime($create_time . ' 00:00:00'), strtotime($create_time . ' 23:59:59')));
        }

        if (!empty($pay_time)) {
            $where['pay_time'] = array('between', array(strtotime($pay_time . ' 00:00:00'), strtotime($pay_time . ' 23:59:59')));
        }

        if (!empty($invoice_time)) {
            $where['invoice_time'] = array('between', array(strtotime($invoice_time . ' 00:00:00'), strtotime($invoice_time . ' 23:59:59')));
        }

        if (!empty($openid)) {

            if(is_numeric($openid)){
                //获取openid
                $user = M('mem', $mer_chant['pre_table']);
                $user_list = $user->field('openid')->where(array('mobile' => $openid,'openid'=>array('neq','')))->find();
                if($user_list&&!empty($user_list['openid'])) {
                    $where['openid'] = $user_list['openid'];
                }else{

                    $where['openid'] = $openid;
                }
            }else{

                $where['openid'] = $openid;
            }

        }

        if (!empty($orderno)) {
            $where['orderno'] = $orderno;
        }

        if (!empty($carno)) {
            $where['carno'] = array('like','%'.$carno.'%');;
        }

       // $page = isset($page) ? $page : 1;
        $show_num = 10;

        $order = M('carpay_order', $mer_chant['pre_table']);
        if (count($where) > 0) {
            $count = $order->where($where)->count('createtime');
            $orders = $order->where($where)->order('createtime desc')->select();
        } else {
            $count = $order->count('createtime');
            $orders = $order->order('createtime desc')->select();
        }

        $total_page = ceil($count / $show_num);
        // 按openid查询每个订单的用户手机号并返回给前端
        if (is_array($orders) && !empty($orders)) {

            foreach ($orders as $k=>$v) {
                $ids_arr[] = $v['openid'];
                if ($v['paytype'] == 0) {
                    $orders[$k]['total_fee'] = $v['total_fee'] / 100; // 库中存的单位是分 转化成元
                    $orders[$k]['payfee'] = $v['payfee'] / 100; // 库中存的单位是分 转化成元
                }
            }

            $openid_str = implode(',', array_unique($ids_arr));

            $user = M('mem', $mer_chant['pre_table']);
            $user_list = $user->where(array('openid' => array('in', $openid_str)))->select();

            if (is_array($user_list)) {
                foreach ($user_list as $v) {
                    foreach ($orders as $k => $value) {

                        if ($value['openid'] == $v['openid']) {
                            $orders[$k]['mobile'] = $v['mobile'];
                        }
                    }
                }
            }
        }

//        writeOperationLog(array('后台查询停车缴费记录sql' . $order->_sql()), 'jaleel_logs');
     //导出数据
     // print_r($orders);exit;
        $datas=array();

        foreach ($orders as $va){

            if($va['status']=='0'){

                $status='未付款';

            }else{
                $status='已付款';
            }

            if($va['status']=='0'){

                $pay_time="";//支付时间
                $discount="";//优惠
                $payfee=""; //实际支付

            }else{
                $pay_time=empty($va['pay_time'])?"":date("Y-m-d H:i:s",$va['pay_time']);
                $discount=$va['total_fee']-$va['payfee'];
                $payfee=$va['payfee'];


            }
           $datas[]=array(
               'name'=>$va['orderno'],
               'createtime'=>date("Y-m-d H:i:s",$va['createtime']),
               'mobile'=>$va['mobile'],
               'openid'=>$va['openid'],
               'carno'=>$va['carno'],
               'status'=>$status,
               'pay_time'=>$pay_time,
               'total_fee'=>$va['total_fee'],
               'discount'=>$discount,
               'payfee'=>$payfee,
               'paytype'=>$va['paytype']==1?'积分支付':'微信支付',



           );
        }
       // print_r($datas);exit;
        $title=array('订单号','创建时间','用户','OPENID','车牌号','状态','付款时间','应付￥','优惠￥','实付￥','支付类型');
        vendor("Csv.Csv");
        $cvs =new  \Csv();
        $cvs->put_csv($datas,$title);



       // $data = array('code' => '200', 'msg' => 'SUCCESS!', 'data' => $orders, 'page' => array('page' => $page, 'total_page' => $total_page));
       // returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 导出excel表格
     * @param $fileName 文件名
     * @param $title 表格名
     * @param array $tableHead 表头数组
     * @param array $data 定入数据
     */
    protected function exportExcel($fileName, $title, array $tableHead, array $data)
    {
        vendor('phpexcel.Classes.PHPExcel');
        vendor('phpexcel.Classes.PHPExcel.IOFactory');

        //创建一个excel对象
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties();

        // 设置表头相关信息
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);

        // 设置表头文字加粗
        $styleArray = array(
            'fonts' => array(
                'bold' => true,
                'color' => array(
                    'argb' => '00000000',
                ),
            ),
        );

        $key = ord('A');

        foreach ($tableHead as $v) {
            $column = chr($key); // 列名

            // 设置表格列宽
            $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);

            // 设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle($column)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            // 设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($column)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            // 设置文字加粗
            $objPHPExcel->getActiveSheet()->getStyle($column . '1')->getFont()->setBold(true);

            // 设置表头名称
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . '1', $v);
            $key += 1;
        }

        // 写入数据
        $col = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) { //行写入

            // 设置行高
            $objActSheet->getRowDimension($col)->setRowHeight(35);

            $span = ord("A");
            foreach ($rows as $keyName => $value) {// 列写入

                if ($keyName == 'openid') {
                    $value = $rows['mobile'];
                }

                if ($keyName == 'paytype') {
                    if ($value == 0) {
                        $value = '微信支付';
                    } else if ($value == 1) {
                        $value = '支分支付';
                    }
                }

                if ($keyName == 'status') {
                    if ($value == 0) {
                        $value = '未付款';
                    } else if ($value == 1) {
                        $value = '已付款通知车场失败';
                    } else if ($value == 2) {
                        $value = '已付款通知车场成功';
                    }
                }

                if ($keyName == 'begintime' or $keyName == 'endtime' or $keyName == 'createtime' or $keyName == 'invoice_time' or $keyName == 'pay_time') {

                    if ($value == 0) {
                        $value = '';
                    } else if (!empty($value)) {
                        $value = date('Y-m-d H:i:s', $value);
                    }
                }

                $j = chr($span);

                // 设置单元格值
                $objActSheet->setCellValue($j . $col, $value);
                $span++;
            }
            $col++;
        }

        // 重命名表
        $objPHPExcel->getActiveSheet()->setTitle($title);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        //ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        //$objWriter->save($fileName);
    }

    /**
     * 确认开发票接口
     */
    public function confirmInvoice()
    {

        $orderIds = I('orderIds');

        // 验证为空性
        if (!$this->ukey or !$orderIds) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $orderids_arr = json_decode($orderIds, true);

        if (!is_array($orderids_arr)) {
            $data = array('code' => '3000', 'msg' => '系统错误!');
            returnjson($data, $this->returnstyle, $this->callback);
        }

        $id_str = '';
        foreach ($orderids_arr as $v) {
            $id_str .= ',' . $v;
        }

        $id_str = ltrim($id_str, ',');

        $save_data['invoice_time'] = time();

        $order = M('carpay_order', $mer_chant['pre_table']);
        $order->where(array('id' => array('in', $id_str)))->save($save_data);

        $data = array('code' => '200', 'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 　获取车辆关联配置
     */
    public function getCarRelationlimit()
    {
        // 验证为空性
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);

        $order = M('default', $mer_chant['pre_table']);
        $carlimit = $order->where(array('customer_name' =>"carrelationlimit"))->find();
        if(!$carlimit){
            //默认不限制
            $res = array('limit'=>0);
        }else{
            $res = array('limit'=>(int)$carlimit['function_name']);
        }
        $data = array('code' => '200','data'=>$res,'msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }

    /**
     * 编辑车辆关联配置
     */
    public function editCarRelationlimit()
    {
        $limit = (int)I("limit");
        // 验证为空性
        if (!$this->ukey) {
            $data = array('code' => '1030', 'msg' => 'miss params');
            returnjson($data, $this->returnstyle, $this->callback);
        }
        // 查询商户信息
        $mer_chant = $this->getMerchant($this->ukey);
        $order = M('default', $mer_chant['pre_table']);
        $carlimit = $order->where(array('customer_name' =>"carrelationlimit"))->find();
        if($carlimit){
            $this->redis->del('admin:default:one:carrelationlimit:'. $this->ukey);
            $order->where(array('customer_name' =>"carrelationlimit"))->save(array('function_name'=>$limit));
        }else{
            $insertData = array(
                'customer_name'=>"carrelationlimit",
                'function_name'=>$limit,
                'description' =>"车辆关联配置"
            );
            $order->add($insertData);
        }
        $data = array('code' => '200','msg' => 'SUCCESS!');
        returnjson($data, $this->returnstyle, $this->callback);
    }


    //车厂限免收费标准
    public function GetMoneyPreHour()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $isenable=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'memberfreeprice');
        $isenable = !empty($isenable['function_name'])?$isenable['function_name']:'';
        $isenable1=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'score');
        $isenable1 = !empty($isenable1['function_name'])?$isenable1['function_name']:'';
        returnjson(array('code'=>200,'data'=>array('moneyPreHour'=>$isenable,'score'=>$isenable1)), $this->returnstyle, $this->callback);
    }

    //车厂限免收费标准
    public function SetMoneyPreHour()
    {
        $isenable = I('moneyPreHour');
        $isenable = !empty($isenable)?$isenable:'';
        $admininfo=$this->getMerchant($this->ukey);
        $db = M('default', $admininfo['pre_table']);
        $sel=$db->where(array('customer_name'=>'memberfreeprice'))->find();
        if ($sel){
            $save=$db->where(array('customer_name'=>'memberfreeprice'))->save(array('function_name'=>$isenable));
        }else{
            $save=$db->add(array('customer_name'=>'memberfreeprice','function_name'=>$isenable,'description'=>"车厂收费标准"));
        }
        $this->redis->del("admin:default:one:memberfreeprice:$this->ukey");
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }


    //车场优惠券
    public function GetCouponConf()
    {
        $admininfo=$this->getMerchant($this->ukey);
        $isenable=$this->GetOneAmindefault($admininfo['pre_table'],$this->ukey, 'parkcouponconf');
        $isenable = !empty($isenable['function_name'])?json_decode($isenable['function_name'],true):array('actid'=>'','overlying_coupon'=>0,'overlying_mem'=>0);
        returnjson(array('code'=>200,'data'=>$isenable), $this->returnstyle, $this->callback);
    }

    //车场限免收费标准//actid,overlying_coupon,overlying_mem
    public function SetCouponConf()
    {
        $actid = I('actid');
        $overlying_coupon = I('overlying_coupon');
        $overlying_coupon = ((int)$overlying_coupon==1)?(int)$overlying_coupon:0;
        $overlying_mem = I('overlying_mem');
        $overlying_mem = ((int)$overlying_mem==1)?(int)$overlying_mem:0;
        $admininfo=$this->getMerchant($this->ukey);
        $db = M('default', $admininfo['pre_table']);
        $isenable = array('actid'=>$actid,'overlying_coupon'=>$overlying_coupon,'overlying_mem'=>$overlying_mem);
        $isenable = json_encode($isenable);
        $sel=$db->where(array('customer_name'=>'parkcouponconf'))->find();
        if ($sel){
            $save=$db->where(array('customer_name'=>'parkcouponconf'))->save(array('function_name'=>$isenable));
        }else{
            $save=$db->add(array('customer_name'=>'parkcouponconf','function_name'=>$isenable,'description'=>"车场优惠券配置"));
        }
        $this->redis->del("admin:default:one:parkcouponconf:$this->ukey");
        returnjson(array('code'=>200), $this->returnstyle, $this->callback);
    }



}