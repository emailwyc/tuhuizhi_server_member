<?php
namespace Common\core;

/**
 * 单例管理器
 *
 * @package Common\core
 */
class Singleton
{
    private static $instances = array();

    /**
     * 根据类名获取该类的单例
     *
     * @param string $className
     * @return Object
     */
    public static function get($className, $pre_table = '')
    {
        if (!array_key_exists($className, self::$instances))
        {
            //$fileName = str_replace('\\', '/', $className);//
            //require_once APP_PATH.$fileName.'.php';//引入文件
            self::$instances[$className] = new $className($pre_table);
        }

        return self::$instances[$className];
    }
    
    public static function getModel($className, $pre_table = '')
    {
        return self::get($className, $pre_table);
    }
}
