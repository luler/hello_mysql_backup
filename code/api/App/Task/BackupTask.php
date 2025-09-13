<?php


namespace App\Task;

use App\Service\OperationLogService;
use EasySwoole\Task\AbstractInterface\TaskInterface;

class BackupTask implements TaskInterface
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    function run(int $taskId, int $workerIndex)
    {
        try {
            \App\Service\BackupService::backup(
                $this->data['database'],
                $this->data['root_dir'],
                $this->data['backup_id'],
                $this->data['keep_count'],
                $this->data['tables'],
            );
        } catch (\Throwable $e) {
            OperationLogService::saveLog(0, '立即备份异常', __METHOD__, '异常信息：' . $e->getMessage());
        }
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // TODO: Implement onException() method.
    }
}