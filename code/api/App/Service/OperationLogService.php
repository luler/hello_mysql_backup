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

class OperationLogService
{
    /**
     * 保存操作日志
     * @param int $uid //用户id
     * @param string $name //操作名称
     * @param string $url //操作路径
     * @param string $param //操作相关参数
     * @return int
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public static function saveLog(int $uid, string $name, string $url, string $param)
    {
        return SqliteHelper::table('operation_log')->insert([
            'uid' => $uid,
            'name' => $name,
            'url' => $url,
            'param' => $param,
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }
}