<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;

use App\Helper\ConfigHelper;
use App\WebSocket\WebSocketParser;
use EasySwoole\Component\AtomicManager;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Socket\Dispatcher;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        /**
         * **************** 加载配置文件 **********************
         */
        ConfigHelper::initCustomConfig();
    }

    public static function mainServerCreate(EventRegister $register)
    {
        /**
         * **************** 增加原子计数器 **********************
         */
        AtomicManager::getInstance()->add('lock');
        /**
         * **************** 数据库连接配置 **********************
         */
        $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('database.default'));
        DbManager::getInstance()->addConnection(new Connection($config));

        /**
         * **************** redis缓存配置 **********************
         */
        $redis_config = Config::getInstance()->getConf('redis.default');
        $redisPoolConfig = RedisPool::getInstance()->register(new RedisConfig($redis_config));
        $redisPoolConfig->setMinObjectNum($redis_config['minObjectNum'] ?? 5)
            ->setMaxObjectNum($redis_config['maxObjectNum'] ?? 200);
        /**
         * **************** fastcache缓存配置 **********************
         */
        $config = new \EasySwoole\FastCache\Config();
        $config->setTempDir(EASYSWOOLE_TEMP_DIR);
        Cache::getInstance($config)->attachToServer(ServerManager::getInstance()->getSwooleServer());
        /**
         * **************** 定时任务配置 **********************
         */
        $files = glob('App/Crontab/*.php');
        foreach ($files as $file) {
            preg_match('/\/(\w+)\.php$/', $file, $matchs);
            if (!empty($matchs)) {
                $class = '\\App\\Crontab\\' . $matchs[1];
                Crontab::getInstance()->addTask($class);
            }
        }
        /**
         * **************** websocket控制器 **********************
         */
        // 创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        // 设置 Dispatcher 为 WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        // 设置解析器对象
        $conf->setParser(new WebSocketParser());
        // 创建 Dispatcher 对象 并注入 config 对象
        $dispatch = new Dispatcher($conf);
        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });

        /**
         * **************** 热重载 **********************
         */
        if (Config::getInstance()->getConf('app.debug')) {
            // 配置同上别忘了添加要检视的目录
            $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
            $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
            $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);

            $server = ServerManager::getInstance()->getSwooleServer();
            $hotReload->attachToServer($server);
        }
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        //跨域处理
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            return false;
        }
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
