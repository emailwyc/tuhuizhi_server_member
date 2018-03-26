<?php
namespace Common\Controller;

use Common\Controller\CommonController;
/**
 * @desc     错误码解决办法
 * @author ut
 *
 */
class ErrorcodeController extends CommonController{
    // TODO - Insert your code here
    public $errorcode;//滴滴打车错误码
    
    public $myerrorcode;//自定义错误返回信息，此变量暂时保留，滴滴打车使用，将打车迁往平台完成后，可以删除
    public $commonerrorcode;//公共错误状态码
    public function _initialize(){
        parent::__initialize();
        $this->errorcode=array(
            '401'=>'',
        );
        
        ############################################
        #                                                                                                             #
        #   在语言部分添加语言后，在公共配置文件下面把状态码对应的语言编码补全       #
        #                                                                                                             #
        ############################################
        $this->myerrorcode=array(
        /**
         * 200正确；
         * 100,缺少参数；
            101接口返回格式与预期不一样，如应该返回json，结果返回object等等；
            102，根据接受参数查询不到内容；
            103，不是会员；
            104,错误；
            105，未获取到积分比率；
            106，成功后报告前端结束长链接；
            107，报告前端长链接结束，不管成功还是失败；
            108，当前有未完成的订单；
            109，没有未完成的订单;
            110，当前商户被锁定
         */
            
            'code'=>'',
            'data'=>'',
            'dataofapi'=>'',
            'msg'=>'',//返回的msg按浏览器语言设置返回对应语言
        );
        $this->commonerrorcode=array(
            'code'=>'',
            'data'=>'',
            'msg'=>'',//返回的msg按浏览器语言设置返回对应语言
        );
        

        
    }
    
//     public function _401(){
//         echo '11111';
//     }
    
}

?>