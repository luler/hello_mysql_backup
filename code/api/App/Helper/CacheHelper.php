<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;

class CacheHelper
{
    private static $redis_pool_name = 'default'; //这个要跟连接池的名称对应
    private static $defer_time_out = 60; //从连接池中获取一个链接最大耗时，超过这个时间没获取到会报错"EasySwoole\\RedisPool\\RedisPool pool is empty"

    public static function set($key, $val, int $timeout = 0)
    {
        return RedisPool::defer(self::$redis_pool_name, self::$defer_time_out)->set($key, $val, $timeout);
    }

    public static function get($key)
    {
        return RedisPool::defer(self::$redis_pool_name, self::$defer_time_out)->get($key);
    }

    public static function del($key)
    {
        return RedisPool::defer(self::$redis_pool_name, self::$defer_time_out)->del($key);
    }

    public static function exists($key)
    {
        return RedisPool::defer(self::$redis_pool_name, self::$defer_time_out)->exists($key);
    }

    /**
     * ****************自定义配置链接（适合短链接，请求完成就销毁了）**********************
     */

    /**
     * 获取redis连接
     * @Author: 我只想看看蓝天 <1207032539@qq.com>
     * @param array $config
     */
    public static function getConnect($config = []): ?\EasySwoole\Redis\Redis
    {
        $base_config = Config::getInstance()->getConf('redis.default');
        foreach ($config as $key => $item) {
            $base_config[$key] = $item;
        }
        $connect = new \EasySwoole\Redis\Redis(new RedisConfig($base_config));
        return $connect;
    }
}
