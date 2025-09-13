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

use App\Helper\SqliteHelper;
use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\Config;

class DatabaseService
{
    /**
     * 获取数据库数据表
     * @param $database_id
     * @return array
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public static function getDatabaseTables($database_id)
    {
        $res = SqliteHelper::table('database')->where('id', '=', $database_id)->first();
        if (empty($res)) {
            throwException('数据库不存在');
        }
        $config = new Config($res);
        $client = new Client($config);
        $res = $client->rawQuery('show tables');
        $res = array_map(function ($value) {
            $value = array_values($value);
            return $value[0];
        }, $res);

        return $res;
    }
}