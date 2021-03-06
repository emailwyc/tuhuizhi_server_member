<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/5/3
 * Time: 16:15
 * http://qydev.weixin.qq.com/wiki/index.php?title=创建应用菜单
 */

namespace EnterpriseWechat\Controller\App;


use EnterpriseWechat\Controller\EnterprisewConfigController;

class MenuController extends EnterprisewConfigController
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }


    /**
     * @param $data json数据包
     * @param $access_token
     * @param $agentid
     * @return mixed
     */
    protected function setMenu( $data, $access_token, $agentid)
    {
        $url = $this->menuurl;
        $url = str_replace('[ACCESS_TOKEN]', $access_token, $url);
        $url = str_replace('[AGENTID]', $agentid, $url);
        $res = curl_https( $url, $data, array(), 60, true);
        return $res;
    }

    /**
     * @param $access_token
     * @param $agentid
     * @return mixed
     */
    protected function delMenu( $access_token, $agentid)
    {
        $url = $this->delmenuurl;
        $url = str_replace('[ACCESS_TOKEN]', $access_token, $url);
        $url = str_replace('[AGENTID]', $agentid, $url);
        $res = curl_https( $url, array(), array(), 60, true);
        return $res;
    }



    /**
     * @param $access_token
     * @param $agentid
     * @return mixed
     */
    protected function getMenu( $access_token, $agentid)
    {
        $url = $this->getmenuurl;
        $url = str_replace('[ACCESS_TOKEN]', $access_token, $url);
        $url = str_replace('[AGENTID]', $agentid, $url);
        $res = curl_https( $url, array(), array(), 60, true);
        return $res;
    }





}