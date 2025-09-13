<?php

return [
    'cas' => [
        'host' => \App\Helper\ConfigHelper::getEnv('cas.host', 'https://127.0.0.1'),
        'appid' => \App\Helper\ConfigHelper::getEnv('cas.appid', 'test'),
        'appsecret' => \App\Helper\ConfigHelper::getEnv('cas.appsecret', 'test'),
    ],
];