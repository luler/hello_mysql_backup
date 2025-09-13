<?php

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/Database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/Database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'sqlite',
        'mysql' => [
            'adapter' => 'mysql',
            'host' => \App\Helper\ConfigHelper::getEnv('database.host') ?: 'localhost',
            'name' => \App\Helper\ConfigHelper::getEnv('database.database') ?: 'test',
            'user' => \App\Helper\ConfigHelper::getEnv('database.user') ?: 'root',
            'pass' => \App\Helper\ConfigHelper::getEnv('database.password') ?: 'root',
            'port' => \App\Helper\ConfigHelper::getEnv('database.port') ?: '3306',
            'charset' => \App\Helper\ConfigHelper::getEnv('database.charset') ?: 'utf8',
            'table_prefix' => '',
        ],
        'sqlite' => [
            'adapter' => 'sqlite',
            'host' => \App\Helper\ConfigHelper::getEnv('database.host') ?: 'localhost',
            'name' => \App\Helper\ConfigHelper::getEnv('database.database') ?: 'test',
            'user' => \App\Helper\ConfigHelper::getEnv('database.user') ?: 'root',
            'pass' => \App\Helper\ConfigHelper::getEnv('database.password') ?: 'root',
            'port' => \App\Helper\ConfigHelper::getEnv('database.port') ?: '3306',
            'charset' => \App\Helper\ConfigHelper::getEnv('database.charset') ?: 'utf8',
            'table_prefix' => '',
        ],
    ],
    'version_order' => 'creation'
];
