<?php
//                            _ooOoo_
//                           o8888888o
//                           88" . "88
//                           (| -_- |)
//                            O\ = /O
//                        ____/`---'\____
//                      .   ' \\| |// `.
//                       / \\||| : |||// \
//                     / _||||| -:- |||||- \
//                       | | \\\ - /// | |
//                     | \_| ''\---/'' | |
//                      \ .-\__ `-` ___/-. /
//                   ___`. .' /--.--\ `. . __
//                ."" '< `.___\_<|>_/___.' >'"".
//               | | : `- \`.;`\ _ /`;.`/ - ` : | |
//                 \ \ `-. \_ __\ /__ _/ .-` / /
//         ======`-.____`-.___\_____/___.-`____.-'======
//                            `=---='
//
//         .............................................
//                  佛祖镇楼                  BUG退散

defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', realpath(getcwd()));

use App\Helper\SqliteHelper;
use App\Service\DatabaseService;
use Luler\Helpers\MultiProcessHelper;

require_once 'vendor/autoload.php';

$res = SqliteHelper::table('backup', 'a')
    ->join('a', 'database as b', 'b.id=a.database_id')
    ->where('a.status', '=', 1)
    ->select(['b.*', 'a.cron', 'a.keep_count', 'a.tables', 'a.table_limit', 'a.id as backup_id'])
    ->get();
$root = EASYSWOOLE_ROOT . '/Public/Backup';
if (!file_exists($root)) { //先创建，防止多进程创建出现资源竞争问题
    mkdir($root, 0777, true);
}
foreach ($res as $value) {
    MultiProcessHelper::instance()->multiProcessTask(function () use ($value, $root) {
        $cron = new \Cron\CronExpression($value['cron'], new \Cron\FieldFactory());
        //未到定时生效，直接退出
        if (!$cron->isDue()) {
            return;
        }
        $tables = $value['tables'];
        if ($value['table_limit'] == 2) { //排除表
            $tables = DatabaseService::getDatabaseTables($value['id']);
            $tables = array_values(array_diff($tables, explode(',', $value['tables'])));
            $tables = join(',', $tables);
        }
        \App\Service\BackupService::backup($value, $root, $value['backup_id'], $value['keep_count'], $tables);
    });
}