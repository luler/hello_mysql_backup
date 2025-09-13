<?php


namespace App\Task;

use App\Service\OperationLogService;
use EasySwoole\Task\AbstractInterface\TaskInterface;

class BackupImportTask implements TaskInterface
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    function run(int $taskId, int $workerIndex)
    {
        try {
            \App\Service\BackupService::backupImport($this->data['target_database_id'], $this->data['num'], $this->data['is_overwrite_tables'] ?? false);
        } catch (\Throwable $e) {
            OperationLogService::saveLog(0, '导入备份异常', __METHOD__, '异常信息：' . $e->getMessage());
        }
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // TODO: Implement onException() method.
    }
}