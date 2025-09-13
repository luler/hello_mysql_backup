<?php

return [
    'jwt' => [
        'secret' => \App\Helper\ConfigHelper::getEnv('jwt.jwt_secret', '$87g>q!b'),
        'expires' => \App\Helper\ConfigHelper::getEnv('jwt.jwt_expires', 7200),//过期时间，单位秒
        'delay' => \App\Helper\ConfigHelper::getEnv('jwt.jwt_delay', 0),//延迟解析时间，单位秒
    ],
];