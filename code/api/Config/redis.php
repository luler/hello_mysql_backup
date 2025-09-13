<?php

return [
    'redis' => [
        ////
        'default' => [
            'host' => \App\Helper\ConfigHelper::getEnv('redis.host', '127.0.0.1'),
            'port' => \App\Helper\ConfigHelper::getEnv('redis.port', 6379),
            'auth' => \App\Helper\ConfigHelper::getEnv('redis.auth', ''),
            'db' => \App\Helper\ConfigHelper::getEnv('redis.db', 0),
            'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE,
            'minObjectNum' => 1,
            'maxObjectNum' => 20,
        ],
        ///
    ],
];