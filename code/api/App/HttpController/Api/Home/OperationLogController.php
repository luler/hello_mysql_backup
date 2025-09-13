<?php


namespace App\HttpController\Api\Home;

use App\Helper\SqliteHelper;
use App\HttpController\Common\HomeBaseController;

class OperationLogController extends HomeBaseController
{
    /**
     * 获取操作日志列表
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getOperationLogList()
    {
        $field = ['search'];
        $param = _apiParam($field);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['a.name|a.url|b.appid', 'like', "%{$param['search']}%"];
        }
        $res = SqliteHelper::table('operation_log', 'a')
            ->join('a', 'users as b', 'a.uid=b.id', 'left')
            ->where($where)
            ->select(['a.*', 'b.appid',])
            ->orderBy('a.id', 'desc')
            ->paginate();
        foreach ($res['list'] as &$value) {
            $value['create_time'] = date_simple_format($value['create_time']);
            $value['update_time'] = date_simple_format($value['update_time']);
        }
        return $this->successResponse('获取成功', $res);
    }

    /**
     * @logname 删除操作日志
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function delOperationLog()
    {
        $field = ['search'];
        $param = _apiParam($field);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['a.name|a.url|b.appid', 'like', "%{$param['search']}%"];
        }
        $ids = SqliteHelper::table('operation_log', 'a')
            ->join('a', 'users as b', 'a.uid=b.id', 'left')
            ->where($where)
            ->column('a.id');
        SqliteHelper::table('operation_log')->where('id', 'in', $ids)->delete();
        return $this->successResponse('删除成功');
    }

}
