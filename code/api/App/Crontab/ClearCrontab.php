<?php


namespace App\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;

class ClearCrontab extends AbstractCronTask
{

    public static function getRule(): string
    {
        // TODO: Implement getRule() method.
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        // TODO: Implement getTaskName() method.
        return '定时清理临时文件';
    }

    function run(int $taskId, int $workerIndex)
    {
        //定期清理打包下载的压缩文件
        $temp_dir = EASYSWOOLE_ROOT . '/Public/Temp';
        if (file_exists($temp_dir)) {
            $files = scanFiles($temp_dir);
            foreach ($files as $file) {
                $create_time = filectime($file['file_path']);
                //过期删除
                if ($create_time + 60 * 5 < time()) {
                    unlink($file['file_path']);
                }
            }
        }

        //定时递归清理空目录
        $root_path = EASYSWOOLE_ROOT . '/Public';
        rm_empty_dir($root_path);
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // TODO: Implement onException() method.
    }
}