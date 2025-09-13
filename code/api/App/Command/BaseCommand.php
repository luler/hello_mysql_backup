<?php
/**
 * @Author: 我只想看看蓝天 <1207032539@qq.com>
 * @Datetime: 2020/1/22 0022 0:06
 */

namespace App\Command;


use App\Helper\ConfigHelper;
use EasySwoole\EasySwoole\Command\CommandInterface;

abstract class BaseCommand implements CommandInterface
{
    abstract public function commandName(): string;

    abstract public function execute();

    public function exec(): ?string
    {
        /**
         * **************** 加载配置文件 **********************
         */
        ConfigHelper::initCustomConfig();

        go(function () {
            try {
                $this->execute();
            } catch (\Throwable $e) {
                if (!empty($e->getMessage())) {
                    dump('程序异常:' . $e->getMessage());
                }
            }
//            \swoole_process::kill(getmypid()); //终止进程
        });
        return null;
    }

    public function help(\EasySwoole\Command\AbstractInterface\CommandHelpInterface $commandHelp): \EasySwoole\Command\AbstractInterface\CommandHelpInterface
    {
        return $commandHelp;
    }
}