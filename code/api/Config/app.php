<?php

return [
    'app' => [
        'app_name' => \App\Helper\ConfigHelper::getEnv('app.app_name', 'test'),//项目名称
        'debug' => \App\Helper\ConfigHelper::getEnv('app.debug', true), //是否调试状态，调试会热重载等
        'admin_user' => \App\Helper\ConfigHelper::getEnv('app.admin_user', 'admin'), //初始管理员账号
        'admin_password' => \App\Helper\ConfigHelper::getEnv('app.admin_password', 'admin'), //初始管理员密码
    ],
];