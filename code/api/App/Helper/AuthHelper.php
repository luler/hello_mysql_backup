<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\FastCache\Cache;
use Luler\Helpers\EncryptHelper;

class AuthHelper
{
    use Singleton;

    private $secret = 'dofkshgasfyusegfioilkndahugf67aswegfssdgfshdf547c84dsg5SDAGSD#$^$&';
    private $delay = 0;
    private $expires = 7200;

    public function __construct()
    {
        $config = Config::getInstance()->getConf('jwt');
        $this->secret = $config['secret'] ?? $this->secret;
        $this->expires = $config['expires'] ?? $this->expires;
    }

    /**
     * 解析token
     * @Author: 我只想看看蓝天 <1207032539@qq.com>
     * @param $authorization
     * @return mixed
     * @throws \Exception
     */
    public function checkAuthorization($authorization)
    {
        $key = 'logout:' . $authorization;
        if (!is_null(Cache::getInstance()->get($key))) { //已经登出
            throwException('token已失效', 401);
        }
        $res = EncryptHelper::instance()->aesDecrypt($authorization, $this->secret, $this->delay);
        if (!is_array($res) || !isset($res['id'])) {
            throw new \Exception('token非法或已过期');
        }
        return $res;
    }

    /**
     * 生成token
     * @Author: 我只想看看蓝天 <1207032539@qq.com>
     * @param array $data
     * @return string
     */
    public function createAuthorization(array $data)
    {
        if (!isset($data['id'])) {
            throw new \Exception('请设置授权id');
        }
        $res = EncryptHelper::instance()->aesEncrypt($data, $this->secret, $this->expires);
        return $res;
    }

    /**
     * 记录登出
     * @param $authorization
     * @return bool
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function logout($authorization)
    {
        $key = 'logout:' . $authorization;
        Cache::getInstance()->set($key, date('Y-m-d H:i:s'), $this->expires);
        return true;
    }
}
