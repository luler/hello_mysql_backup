<?php

/**
 * **************** 设置时区 **********************
 */
date_default_timezone_set('Asia/Shanghai');

/**
 * **************** 加载自定义命令 **********************
 */
$command_files = glob('App/Command/*.php');
foreach ($command_files as $command_file) {
    preg_match('/\/(\w+)\.php$/', $command_file, $matchs);
    if (!empty($matchs) && !in_array($matchs[1], ['BaseCommand'])) {
        $class = '\\App\\Command\\' . $matchs[1];
        \EasySwoole\Command\CommandManager::getInstance()->addCommand(new $class);
    }
}