<?php
/**
 * Created by PhpStorm.
 * User: zhangkaifeng
 * Date: 2017/2/17
 * Time: 18:18
 */

namespace PublicApi\Controller;


use Common\Controller\CommonController;

class JumpController extends CommonController
{
    public function jumpmarket()
    {
        $keyadmin=I('key_admin');
        $buildid=I('buildid');
        $floor=I('floor');
        $store=I('store');
        header('Location:http://h5.rtmap.com/market/mappoione/poipage/'.$keyadmin.'/'.$buildid.'/'.$floor.'/'.$store);
    }
}