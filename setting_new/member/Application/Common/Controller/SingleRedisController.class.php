<?php
namespace Common\Controller;

class SingleRedisController{

    private $_db;
    private $_dbnum;
    private static $_instance;

    private function __construct($dbnum=1)
    {
        $this->_dbnum = $dbnum;
        $this->_db=new \Redis();
        $this->_db->connect(C('REDIS_HOST'),C('REDIS_PORT'));
        $this->_db->auth(C('REDIS_AUTH'));
        $this->_db->select($dbnum);
    }

    private function __clone() {}  //覆盖__clone()方法，禁止克隆

    public static function getInstance($dbnum=1)
    {
        if(!(self::$_instance instanceof self) || self::$_instance->_dbnum !=$dbnum) {
            self::$_instance = new self($dbnum);
        }
        return self::$_instance->_db;

    }
}

?>