<?php

return [
    'database' => [
        ////
        'default' => [
            'host' => \App\Helper\ConfigHelper::getEnv('database.host', '127.0.0.1'),
            'port' => \App\Helper\ConfigHelper::getEnv('database.port', 3306),
            'user' => \App\Helper\ConfigHelper::getEnv('database.user', 'root'),
            'password' => \App\Helper\ConfigHelper::getEnv('database.password', 'root'),
            'database' => \App\Helper\ConfigHelper::getEnv('database.database', 'test'),
            'timeout' => 5, //超时时间
            'connect_timeout' => 5,//连接超时时间
            'charset' => 'utf8mb4',
            'strict_type' => true, //开启严格模式，返回的字段将自动转为数字类型
            'minObjectNum' => 1,
            'maxObjectNum' => 200,
        ],
        ///
    ],
];