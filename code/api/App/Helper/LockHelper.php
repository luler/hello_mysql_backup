<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use EasySwoole\Component\AtomicManager;
use Swoole\Coroutine;

class LockHelper
{
    public static function blockLock($name)
    {
        while (!AtomicManager::getInstance()->get($name)->cmpset(0, 1)) {
            Coroutine::sleep(0.001);//协程挂起睡眠一毫秒
        }
    }

    public static function releaseLock($name)
    {
        AtomicManager::getInstance()->get($name)->set(0);
    }
}
