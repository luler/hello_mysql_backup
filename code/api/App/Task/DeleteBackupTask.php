<?php


namespace App\Task;

use App\Helper\SqliteHelper;
use EasySwoole\Task\AbstractInterface\TaskInterface;

class DeleteBackupTask implements TaskInterface
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    function run(int $taskId, int $workerIndex)
    {
        switch ($this->data['type']) {
            case 'database':
                //删除字表及文件
                $backup_ids = SqliteHelper::table('backup')->where('database_id', '=', $this->data['id'])->column('id');
                SqliteHelper::table('backup')->where('database_id', '=', $this->data['id'])->delete();
                SqliteHelper::table('backup_file')->where('backup_id', 'in', $backup_ids)->delete();
                foreach ($backup_ids as $backup_id) {
                    delDirAndFile(EASYSWOOLE_ROOT . '/Public/Backup/' . $backup_id);
                }
                break;
        }
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        dump($throwable->getMessage());
        // TODO: Implement onException() method.
    }
}