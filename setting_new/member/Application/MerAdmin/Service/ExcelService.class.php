<?php
namespace MerAdmin\Service;
use Common\core\Singleton;

class ExcelService{
    
    /**
     * 将数据以Excel文件形式导出(文件头)
     */
    public function exportHeader() {
        // 文件头部信息
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: attachment; filename=".date('Ymd').".xls");
        header("Content-Type: application/vnd.ms-excel; charset=GBK;vnd.ms-excel.numberformat:@");
    }
    
    /**
     * 获取数据，并输出
     * @param array $dataArray 主体数据 必须为二维数组或一维数组
     * @return boolean 为true，数据获取成功，否则没有获取数据
     */
    public function addArray($Array=array()) {
        $dataArray[]=$Array;
        foreach ($dataArray as $key => $val) {
            $lineStr = "";
            foreach ($val as $k => $v) {
                if($v != null){
                    $lineStr .= $this->str2csv(iconv('UTF-8', 'GBK', $v))."\t";
                }else{
                    $lineStr .= '""'."\t";
                }
            }
            echo $lineStr."\n";
            flush();
        }
        return true;
    }
    
    //导出ping++订单
    public function export_pingxx_pay($arr){
        $content = array();
        foreach($arr as $k => $v){
            $content[] = $v['id']."\t";
            $content[] = $v['main']."\t";
            $content[] = (string)($v['mount']/100)."\t";
            $content[] = (string)($v['amount']/100)."\t";
            $content[] = $v['orderno']."\t";
            $content[] = $v['openid']."\t";
            $content[] = $v['channel']."\t";
            $content[] = $v['currency']."\t";
            $content[] = $v['status']."\t";
            $content[] = $v['shopid']."\t";
            $content[] = $v['key_admin']."\t";
            $content[] = $v['buildid']."\t";
            $content[] = $v['couponqr']."\t";
            $content[] = $v['marketname']."\t";
            $content[] = $v['shopname']."\t";
            $content[] = $v['datetime']."\t";
            $content[] = $v['couponprice']."\t";
            $content[] = $v['nickname']."\t";
            //结束加上."\t" 解决数字长度太长时显示不完整或者乱码的问题
            
            $this->addArray($content);

            unset($content);
        }
    }
    
    //导出优惠券领取记录
    public function export_coupon_log($arr){
        $content = array();
        foreach($arr as $k => $v){
            $content[] = $v['id']."\t";
            $content[] = $v['adminid']."\t";
            $content[] = $v['openid']."\t";
            $content[] = $v['couponActivityid']."\t";
            $content[] = $v['couponid']."\t";
            $content[] = $v['activityid']."\t";
            $content[] = $v['maininfo']."\t";
            $content[] = $v['marketid']."\t";
            $content[] = $v['shopid']."\t";
            $content[] = $v['issuername']."\t";
            $content[] = $v['qrcode']."\t";
            $content[] = $v['type']."\t";
            $content[] = date('Y-m-d H:i:s', $v['ctime'])."\t";
            //结束加上."\t" 解决数字长度太长时显示不完整或者乱码的问题

            $this->addArray($content);
    
            unset($content);
        }
    }
    
    //导出积分商城兑换记录
    public function integral_log($arr, $buildname = ''){
        $status = array(
            '0'=>'未开放',
            '1'=>'已开放',
            '2'=>'已领取',
            '3'=>'已核销',
            '4'=>'已撤销',
            '5'=>'已过期',
            '6'=>'转增中',
            '7'=>'核销中',
            '8'=>'退款中',
            '9'=>'已退款'
        );
        
        $content = array();
        foreach($arr as $k=>$v){
            $content[] = $buildname."\t";
            $content[] = $v['activity_id']."\t";
            $content[] = $v['prize_name']."\t";
            $content[] = $v['usermember']."\t";
            $content[] = $v['cardno']."\t";
            $content[] = $v['level']."\t";
            $content[] = $v['mobile']."\t";
            $content[] = $v['integral']."\t";
            $content[] = $v['starttime']."\t";
            $content[] = $status[$v['status']]."\t";
            
            $this->addArray($content);
            unset($content);
        }
    }
    
    private function str2csv($s) {
        $s = str_replace('"', '""', $s);
        return '"'.$s.'"';
    }
}
?>