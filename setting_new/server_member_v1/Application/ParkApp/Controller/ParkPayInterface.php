<?php
/**
 * 停车缴费支付应用接口
 * User: jaleel
 * Date: 7/14/16
 * Time: 4:27 PM
 */

namespace ParkApp\Controller;

interface ParkPayInterface
{
    public function getFreeParking();

    public function searchParkNo();

    public function payByScore();

    public function payByWeiXin();

    public function notifyCarPark();
}