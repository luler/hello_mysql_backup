<?php


namespace App\HttpController\Api\Home;

use App\Helper\LockHelper;
use App\Helper\SqliteHelper;
use App\Helper\ValidateHelper;
use App\HttpController\Common\HomeBaseController;
use App\Service\DatabaseService;
use App\Task\BackupTask;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\FastCache\Cache;
use hollodotme\CrontabValidator\CrontabValidator;
use Luler\Helpers\CommonHelper;

class BackupController extends HomeBaseController
{

    /**
     * 获取备份列表
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getBackupList()
    {
        $field = ['id', 'search'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'id' => 'required',
        ]);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['a.tables', 'like', "%{$param['search']}%"];
        }
        $res = SqliteHelper::table('backup', 'a')
            ->join('a', 'database as b', 'a.database_id=b.id')
            ->where('a.database_id', '=', $param['id'])
            ->where($where)
            ->select(['a.*', 'b.title', 'b.database',])
            ->orderBy('a.id', 'desc')
            ->paginate();
        $available_size = CommonHelper::convertSize(disk_free_space(EASYSWOOLE_ROOT . '/Public'));
        foreach ($res['list'] as &$value) {
            $value['last_cost_time'] = second_to_time($value['last_cost_time'] ?? 0);
            $value['create_time'] = date_simple_format($value['create_time']);
            $value['update_time'] = date_simple_format($value['update_time']);
            $value['tables_arr'] = array_filter(explode(',', $value['tables']));
            $value['size'] = SqliteHelper::table('backup_file')->where('backup_id', '=', $value['id'])->value('sum(size)') ?: 0;
            $value['size'] = CommonHelper::convertSize($value['size']);
            $value['available_size'] = $available_size;
            $value['use_count'] = SqliteHelper::table('backup_file')->where('backup_id', '=', $value['id'])->count('distinct num');
        }
        return $this->successResponse('获取成功', $res);
    }

    /**
     * @logname 保存备份信息
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function saveBackup()
    {
        $field = ['id', 'database_id', 'cron', 'table_limit', 'tables', 'keep_count',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'database_id|数据库' => 'required|integer',
            'cron|定时配置' => 'required',
            'keep_count|保留数量' => 'required|integer|min:1',
            'table_limit|限定表类型' => 'required|integer|in:0,1,2',
//            'tables|数据表' => 'required',
        ]);
        $v = new CrontabValidator();
        if (!$v->isExpressionValid($param['cron'])) {
            throwException('定时配置有误');
        }
        empty($param['table_limit']) && $param['tables'] = '';
        if (!empty($param['table_limit']) && empty($param['tables'])) {
            throwException('数据表不能为空');
        }

        $param['update_time'] = time();
        if (!empty($param['id'])) {
            unset($param['tables']); //不允许变更备份的表，保持备份数据格式一致性
            unset($param['table_limit']); //不允许变更备份的表，保持备份数据格式一致性
            SqliteHelper::table('backup')->where('id', '=', $param['id'])->update($param);
        } else {
            $param['create_time'] = time();
            SqliteHelper::table('backup')->insert($param);
        }

        return $this->successResponse('保存成功');
    }

    /**
     * @logname 保存备份状态
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function saveBackupStatus()
    {
        $field = ['id', 'status'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'id|备份' => 'required|integer',
            'status|状态' => 'inArray:0,1',
        ]);


        $param['update_time'] = time();
        SqliteHelper::table('backup')->where('id', '=', $param['id'])->update($param);
        return $this->successResponse('保存成功');
    }

    /**
     * @logname 删除备份
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function delBackup()
    {
        $field = ['id',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'id' => 'required',
        ]);

        $connect = SqliteHelper::getConnection();
        $connect->beginTransaction();
        try {
            SqliteHelper::table('backup')->useConnect($connect)->where('id', '=', $param['id'])->delete();
            SqliteHelper::table('backup_file')->useConnect($connect)->where('backup_id', '=', $param['id'])->delete();
            delDirAndFile(EASYSWOOLE_ROOT . '/Public/Backup/' . $param['id']);
            $connect->commit();
        } catch (\Exception $e) {
            $connect->rollBack();
            throwException($e->getMessage());
        }

        return $this->successResponse('删除成功');
    }

    /**
     * 获取数据库数据表
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getDatabaseTables()
    {
        $field = ['database_id',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'database_id|数据库' => 'required',
        ]);

        $res = DatabaseService::getDatabaseTables($param['database_id']);
        return $this->successResponse('获取成功', $res);
    }

    /**
     * @logname 立即执行备份
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function startBackup()
    {
        $field = ['id',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'id' => 'required',
        ]);

        $backup_id = $param['id'];
        $value = SqliteHelper::table('backup', 'a')
            ->join('a', 'database as b', 'b.id=a.database_id')
            ->where('a.id', $backup_id)
            ->select(['b.*', 'a.cron', 'a.keep_count', 'a.table_limit', 'a.tables', 'a.id as backup_id'])
            ->first();
        $key = 'backup_working';
        LockHelper::blockLock('lock');
        try {
            $backup_working = Cache::getInstance()->get($key) ?: [];
            if (in_array($backup_id, $backup_working)) {
                throwException('备份正在执行，请稍后再试');
            }
            $tables = $value['tables'];
            if ($value['table_limit'] == 2) { //排除表
                $tables = DatabaseService::getDatabaseTables($value['id']);
                $tables = array_values(array_diff($tables, explode(',', $value['tables'])));
                $tables = join(',', $tables);
            }
            $backup_working[] = $backup_id;
            Cache::getInstance()->set($key, $backup_working, 60 * 60 * 24);
        } finally {
            LockHelper::releaseLock('lock');
        }

        TaskManager::getInstance()->async(new BackupTask([
            'database' => $value,
            'root_dir' => EASYSWOOLE_ROOT . '/Public/Backup',
            'backup_id' => $value['backup_id'],
            'keep_count' => $value['keep_count'],
            'tables' => $tables,
        ]), function () use ($backup_id) {
            LockHelper::blockLock('lock');
            try {
                $key = 'backup_working';
                $backup_working = Cache::getInstance()->get($key) ?: [];
                $backup_working = array_diff($backup_working, [$backup_id]);
                $backup_working = array_values($backup_working);
                Cache::getInstance()->set($key, $backup_working, 60 * 60 * 24);
            } finally {
                LockHelper::releaseLock('lock');
            }
        });
        return $this->successResponse('备份任务发布成功');
    }

    /**
     * 获取正在执行的备份配置
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getBackupWorking()
    {
        $key = 'backup_working';
        $backup_working = Cache::getInstance()->get($key) ?: [];
        return $this->successResponse('获取成功', $backup_working);
    }
}
