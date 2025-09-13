<?php


namespace App\HttpController\Api\Home;

use App\Helper\LockHelper;
use App\Helper\SqliteHelper;
use App\Helper\ValidateHelper;
use App\HttpController\Common\HomeBaseController;
use App\Task\BackupImportTask;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\FastCache\Cache;

class BackupImportController extends HomeBaseController
{
    /**
     * @logname 立即执行导入备份
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function startBackupImport()
    {
        $field = ['target_database_id', 'num', 'is_overwrite_tables',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'target_database_id' => 'required',
            'num' => 'required',
        ]);

        $database = SqliteHelper::table('database')->where('id', $param['target_database_id'])->first();
        if (empty($database) || $database['can_import'] != 1) {
            throwException('当前数据库不支持导入备份');
        }
        $key = 'import_working';
        LockHelper::blockLock('lock');
        try {
            $working = Cache::getInstance()->get($key) ?: [];
            if (in_array($param['target_database_id'], $working)) {
                throwException('正在导入备份，请稍后再试');
            }
            $working[] = $param['target_database_id'];
            Cache::getInstance()->set($key, $working, 60 * 60 * 24);
        } finally {
            LockHelper::releaseLock('lock');
        }
        TaskManager::getInstance()->async(new BackupImportTask($param), function () use ($param) {
            LockHelper::blockLock('lock');
            try {
                $key = 'import_working';
                $backup_working = Cache::getInstance()->get($key) ?: [];
                $backup_working = array_diff($backup_working, [$param['target_database_id']]);
                $backup_working = array_values($backup_working);
                Cache::getInstance()->set($key, $backup_working, 60 * 60 * 24);
            } finally {
                LockHelper::releaseLock('lock');
            }
        });
        return $this->successResponse('导入备份任务发布成功');
    }

    /**
     * 获取正在执行的备份配置
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getBackupImportWorking()
    {
        $key = 'import_working';
        $working = Cache::getInstance()->get($key) ?: [];
        return $this->successResponse('获取成功', $working);
    }
}
