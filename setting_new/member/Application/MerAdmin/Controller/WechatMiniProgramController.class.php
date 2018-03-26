<?php
/**
 * 微信小程序二维码业务层
 */
namespace MerAdmin\Controller;

use Common\Controller\CommonController;
use Thirdwechat\Service\MiniProgram\Open\MiniProgramQrcodeService;

class WechatMiniProgramController extends CommonController
{
    // TODO - Insert your code here
    public function _initialize() {
        parent::__initialize();
    }


    public function qrcode()
    {
        $type = I('type');
        if (!in_array($type, ['a', 'b', 'c'])) {
            returnjson(array('code'=>1051, 'data'=>'type'), $this->returnstyle, $this->callback);
        }
        /**
         * 接收的是数组
         * 根据type不同，传递参数也不同
         * type=a：path，width，auto_color，line_color
         * type=b：scene，page，width，auto_color，line_color
         * type=c：path，width
         * 重要！！！！！！
         * a和c的数量相加数量最多100000，b无限，因为b接口是临时二维码接口
         */
        $params = I('qrparams');//接收的是数组

        //参数必须完整
        if (in_array('', $params, true)) {
            returnjson(array('code'=>1030, 'data'=>1), $this->returnstyle, $this->callback);
        }
        $admininfo = $this->getMerchant($this->ukey);
        $a = MiniProgramQrcodeService::miniProgramPageQrcode($params, $admininfo['applet_appid'], $type);
        if (!is_array($a)) {

            echo 'data:image/png;base64,'.base64_encode($a);
        }else{
            returnjson($a, $this->returnstyle, $this->callback);
        }

    }


}

?>