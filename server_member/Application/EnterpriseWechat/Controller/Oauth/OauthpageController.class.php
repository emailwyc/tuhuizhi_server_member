<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/4/23
 * Time: 17:01
 */

namespace EnterpriseWechat\Controller\Oauth;


use EnterpriseWechat\Controller\EnterprisewConfigController;

class OauthpageController extends EnterprisewConfigController
{
    public function index()
    {
        $url = $_GET['url'];
        $url = urldecode($url);
        echo
        $url;

        echo '<a href='.$url.'>点</a>';

        echo '<a href="https://mem.rtmap.com/EnterpriseWechat/Oauth/oauth/getUserInfo?scope=snsapi_privateinfo&jumpurl=http%3a%2f%2fwww.baidu.com%3fa%3d1%26b%3d2&agentid=1&abc=1&def=2">agent1</a>';
        echo '<a href="https://mem.rtmap.com/EnterpriseWechat/Oauth/oauth/getUserInfo?scope=snsapi_privateinfo&jumpurl=http%3a%2f%2fwww.baidu.com%3fa%3d1%26b%3d2&agentid=tj44599569b840cf27&abc=1&def=2">agent1</a>';

    }
}