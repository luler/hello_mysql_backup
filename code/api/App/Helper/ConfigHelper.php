<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use EasySwoole\EasySwoole\Config;

class ConfigHelper
{
    private static $data = [];
    private static $env_path = '.env';

    public static function initCustomConfig()
    {
        $config_files = glob('Config/*.php');
        foreach ($config_files as $config_file) {
            \EasySwoole\EasySwoole\Config::getInstance()->loadFile($config_file);
        }
        //初始化.env里面的jwt秘钥
        if (file_exists(self::$env_path)) {
            $env_data = file_get_contents(self::$env_path);
            $env_data_lines = explode("\n", $env_data);
            foreach ($env_data_lines as $line) {
                $line = trim($line);
                if ($line == 'jwt_secret=') { //需要初始化
                    $jwt_secret = session_create_id();
                    $env_data = str_replace('jwt_secret=', 'jwt_secret=' . $jwt_secret, $env_data);
                    file_put_contents(self::$env_path, $env_data);
                    self::getEnv('jwt.jwt_secret');
                    self::$data['jwt']['jwt_secret'] = $jwt_secret;
                }
            }
        }
    }

    /**
     * 获取env数据
     * @param $name
     * @param null $default_value
     * @return mixed|null
     * Author: 我只想看看蓝天 <1207032539@qq.com>
     */
    public static function getEnv($name, $default_value = null)
    {
        if (empty(self::$data)) {
            if (file_exists(self::$env_path)) {
                self::$data = parse_ini_file(self::$env_path, true);
            }
        }

        $config = self::$data;
        $keys = explode('.', $name);
        $value = $default_value;
        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
                $value = $config;
            } else {
                $value = $default_value;
            }
        }
        return $value;
    }

    /**
     * 获取配置
     * @param $name
     * @return mixed|null
     * Author: 我只想看看蓝天 <1207032539@qq.com>
     */
    public static function get($name = '')
    {
        return Config::getInstance()->getConf($name);
    }

}
