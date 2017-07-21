<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        echo 'This is API of zhihuitu company.';
    }
    
//     public function test() {
//         echo $_SERVER['SERVER_PORT'];
//         $http=!is_https()?'http':'https';//判断当前域名是否是https链接
//         dump(is_https());dump($http);
//     }
}