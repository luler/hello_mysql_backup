<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-01
 * Time: 20:06
 */

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 8027,
        'SERVER_TYPE' => 3, //可选为 1-EASYSWOOLE_SERVER  2-EASYSWOOLE_WEB_SERVER 3-EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => swoole_cpu_num(),
            'reload_async' => true,
            'max_wait_time' => 3,
        ],
        'TASK' => [
            'workerNum' => swoole_cpu_num(),
            'maxRunningNum' => 128,
            'timeout' => 15,
        ],
    ],
    'TEMP_DIR' => '/tmp', //本地开发，unix虚拟机不能共享目录会导致报错，所以设置到临时目录中
    'LOG_DIR' => null,
];
