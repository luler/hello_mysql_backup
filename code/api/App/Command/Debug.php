<?php
/**
 * @Author: 我只想看看蓝天 <1207032539@qq.com>
 * @Datetime: 2020/1/22 0022 0:06
 */

namespace App\Command;

class Debug extends BaseCommand
{
    public function commandName(): string
    {
        return 'debug';
    }

    public function desc(): string
    {
        return '调试专用';
    }

    public function execute()
    {
        //
    }
}