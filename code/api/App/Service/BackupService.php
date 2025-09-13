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
namespace App\Service;

use App\Helper\ConfigHelper;
use App\Helper\EmailHelper;
use App\Helper\SqliteHelper;
use Luler\Helpers\CommonHelper;

class BackupService
{
    /**
     * 根据指定数据库的备份配置执行备份
     * @param array $database
     * @param string $root_dir
     * @param int $backup_id
     * @param int $keep_count
     * @param string $tables
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public static function backup(array $database, string $root_dir, int $backup_id, int $keep_count, string $tables = '')
    {
        $log_name = "数据库名称：{$database['title']},备份ID：{$backup_id}";
        OperationLogService::saveLog(0, $log_name, __METHOD__, '开始备份');
        $time = microtime(true);
        $command = [];
        $command[] = 'mydumper';
        $command[] = "-h {$database['host']}";
        $command[] = "-P {$database['port']}";
        $command[] = "-u {$database['user']}";
        $command[] = "-p {$database['password']}";
        $command[] = "-F 512"; //限制单个文件在512MB，避免导出超大表时，单个文件好几个G以上
        $command[] = "-c";
        $command[] = "-B {$database['database']}";
        if (!empty($tables)) {
            $command[] = "-T {$tables}";
        }
        $num = CommonHelper::generateOrderId(1);
        $dir = $root_dir . '/' . $backup_id . '/' . $num;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $command[] = "-o " . $dir;

        $command = join(' ', $command);
        $ciper_command = preg_replace('/-u \S*/', '-u ***', $command);
        $ciper_command = preg_replace('/-p \S*/', '-p ***', $ciper_command);
        OperationLogService::saveLog(0, $log_name, __METHOD__, '执行命令：' . $ciper_command);
        $process = proc_open($command, [
            0 => ["pipe", "r"],    //标准输入，子进程从此管道读取数据
            1 => ["pipe", "w"],    //标准输出，子进程向此管道写入数据
            2 => ["pipe", "w",]    //标准错误，写入到指定文件
        ], $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], "");
            fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error_out = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $return_value = proc_close($process);    //在调用proc_close之前必须关闭所有管道
            if ($return_value != 0) {
                OperationLogService::saveLog(0, $log_name, __METHOD__, '备份失败：' . $error_out);
                try {
                    //忽略通知的错误字符
                    $alert_ignore_str = ConfigHelper::getEnv('email.alert_ignore_str');
                    $alert_ignore_str = array_filter(explode(',', $alert_ignore_str));
                    $is_ignore = false;
                    foreach ($alert_ignore_str as $str) {
                        if (strpos($error_out, $str) !== false) {
                            $is_ignore = true;
                        }
                    }
                    $alert_emails = array_filter(explode(',', ConfigHelper::getEnv('email.alert_emails')));
                    //失败通知邮箱
                    !empty($alert_emails) && !$is_ignore && EmailHelper::instance()->sendEmail($alert_emails, '【' . $database['title'] . '】数据备份失败', '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
  <meta name="theme-color" content="#000000">
  <meta name="renderer" content="webkit">
  <meta name="google" content="notranslate">
  <meta name="format-detection" content="telephone=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>mysql数据备份系统通知</title>
</head>
<body>
<table>
  <tr><td align="right" style="word-break: keep-all">备份ID：</td><td style="word-break: break-all">' . $backup_id . '</td></tr>
  <tr><td align="right" style="word-break: keep-all">数据库名称：</td><td style="word-break: break-all">' . $database['title'] . '</td></tr>
  <tr><td align="right" style="word-break: keep-all">主机：</td><td style="word-break: break-all">' . $database['host'] . '</td></tr>
  <tr><td align="right" style="word-break: keep-all">数据库：</td><td style="word-break: break-all">' . $database['database'] . '</td></tr>
  <tr><td align="right" style="word-break: keep-all">数据表：</td><td style="word-break: break-all">' . ($tables ?: '整库') . '</td></tr>
  <tr><td align="right" style="word-break: keep-all">失败原因：</td><td style="word-break: break-all">' . $error_out . '</td></tr>
</table>
</body>
</html>');
                } catch (\Exception $e) {
                    //邮箱通知失败
                    OperationLogService::saveLog(0, $log_name, __METHOD__, '邮箱通知失败：' . $e->getMessage());
                }
            }
            //扫描到备份文件表
            $files = scanFiles($dir);
            foreach ($files as $file) {
                SqliteHelper::table('backup_file')->insert([
                    'backup_id' => $backup_id,
                    'num' => $num,
                    'file_name' => $file['file_name'],
                    'file_path' => $file['file_path'],
                    'size' => $file['size'],
                    'create_time' => $file['create_time'],
                    'update_time' => $file['update_time'],
                ]);
            }
            //更新最近备份耗时
            $last_cost_time = ceil(microtime(true) - $time);
            SqliteHelper::table('backup')->where('id', $backup_id)->update(['last_cost_time' => $last_cost_time]);
            OperationLogService::saveLog(0, $log_name, __METHOD__, '备份成功，总耗时：' . second_to_time($last_cost_time));
        }
        //删除超出限制数量的备份批次的数据
        $nums = SqliteHelper::table('backup_file')
            ->where('backup_id', $backup_id)
            ->groupBy('num')
            ->column('num');
        rsort($nums);
        $nums = array_slice($nums, $keep_count);
        //删除额外数据
        foreach ($nums as $num) {
            SqliteHelper::table('backup_file')
                ->where('backup_id', $backup_id)
                ->where('num', $num)
                ->delete();
            $dir = $root_dir . '/' . $backup_id . '/' . $num;
            delDirAndFile($dir);
        }
    }

    /**
     * 执行导入备份命令
     * @param $database_id
     * @param $num
     * @return void
     */
    public static function backupImport($database_id, $num, $is_overwrite_tables = false)
    {
        $time = microtime(true);
        try {
            $database = SqliteHelper::table('database')->where('id', $database_id)->first();
            $log_name = "数据库名称：{$database['title']}，导入备份序号：{$num}";
            OperationLogService::saveLog(0, $log_name, __METHOD__, '开始导入');
            $command = [];
            $command[] = 'myloader';
            $command[] = "-h {$database['host']}";
            $command[] = "-P {$database['port']}";
            $command[] = "-u {$database['user']}";
            $command[] = "-p {$database['password']}";
            $command[] = "-B {$database['database']}";
            if ($is_overwrite_tables) {
                $command[] = "--overwrite-tables";  //表存在则删除后覆盖
            }
            $backup_id = SqliteHelper::table('backup_file')->where('num', $num)->value('backup_id');
            $dir = EASYSWOOLE_ROOT . '/Public/Backup/' . $backup_id . '/' . $num;
            $command[] = "-d {$dir}";
            $command = join(' ', $command);
            $ciper_command = preg_replace('/-u \S*/', '-u ***', $command);
            $ciper_command = preg_replace('/-p \S*/', '-p ***', $ciper_command);
            OperationLogService::saveLog(0, $log_name, __METHOD__, '执行命令：' . $ciper_command);
            $process = proc_open($command, [
//                0 => ["pipe", "r"],    //标准输入，子进程从此管道读取数据
                1 => ["file", '/dev/null', "w",],    //标准输出，子进程向此管道写入数据
                2 => ["file", '/dev/null', "w",]    //标准错误，写入到指定文件
            ], $pipes);

            if (is_resource($process)) {
                fwrite($pipes[0], "");
                fclose($pipes[0]);
                $out = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $error_out = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                $return_value = proc_close($process);    //在调用proc_close之前必须关闭所有管道
                if ($return_value != 0) {
                    OperationLogService::saveLog(0, $log_name, __METHOD__, '导入失败：' . $error_out);
                } else {
                    OperationLogService::saveLog(0, $log_name, __METHOD__, '导入成功，总耗时：' . second_to_time(ceil(microtime(true) - $time)));
                }
            }
        } catch (\Exception $e) {
            OperationLogService::saveLog(0, $log_name, __METHOD__, '导入异常：' . $e->getMessage());
        }
    }
}