<?php

return [
    'email' => [
        'smtp_host' => \App\Helper\ConfigHelper::getEnv('email.smtp_host', ''),//邮件服务器
        'smtp_port' => \App\Helper\ConfigHelper::getEnv('email.smtp_port', ''),//端口
        'smtp_email' => \App\Helper\ConfigHelper::getEnv('email.smtp_email', ''),//邮箱账号
        'smtp_password' => \App\Helper\ConfigHelper::getEnv('email.smtp_password', ''),//授权密码
        'smtp_nickname' => \App\Helper\ConfigHelper::getEnv('email.smtp_nickname', ''),//发送人昵称
    ],
];