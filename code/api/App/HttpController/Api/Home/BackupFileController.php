<?php


namespace App\HttpController\Api\Home;

use App\Helper\SqliteHelper;
use App\Helper\ValidateHelper;
use App\HttpController\Common\HomeBaseController;
use Luler\Helpers\CommonHelper;
use ZipStream\Option\Archive;

class BackupFileController extends HomeBaseController
{

    /**
     * 获取备份文件列表
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getBackupFileList()
    {
        $field = ['id', 'search', 'sort_type'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'id' => 'required',
        ]);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['a.file_name|a.num', 'like', "%{$param['search']}%"];
        }
        $order = [];
        switch ($param['sort_type'] ?? '') {
            case 'size.descend':
                $order['a.size'] = 'desc';
                break;
            case 'size.ascend':
                $order['a.size'] = 'asc';
                break;
            default:
                $order['a.num'] = 'desc';
                $order['a.create_time'] = 'desc';
                $order['a.id'] = 'desc';
                break;
        }

        $res = SqliteHelper::table('backup_file', 'a')
            ->where('a.backup_id', '=', $param['id'])
            ->where($where)
            ->select(['a.*',])
            ->orderBy($order)
            ->paginate();
        foreach ($res['list'] as &$value) {
            $value['create_time'] = date_simple_format($value['create_time']);
            $value['update_time'] = date_simple_format($value['update_time']);
            $value['size'] = CommonHelper::convertSize($value['size']);
        }
        return $this->successResponse('获取成功', $res);
    }

    /**
     * 获取数据库的备份序号下拉列表
     * @return void
     */
    public function getDropBackupNumList()
    {
        $field = ['database_id', 'search'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'database_id' => 'required',
        ]);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['a.file_name|a.num|b.tables', 'like', "%{$param['search']}%"];
        }
        $where[] = ['c.id', '=', $param['database_id']];
        $res = SqliteHelper::table('backup_file', 'a')
            ->join('a', 'backup as b', 'a.backup_id=b.id')
            ->join('b', 'database as c', 'b.database_id=c.id')
            ->where($where)
            ->select(['c.title', 'b.tables', 'a.num'])
            ->groupBy('a.num')
            ->orderBy('a.id', 'desc')
            ->get();
        return $this->successResponse('获取成功', $res);
    }

    /**
     * @logname 删除备份文件
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function delBackupFile()
    {
        $field = ['ids',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'ids' => 'required',
        ]);
        foreach ($param['ids'] as $id) {
            $file = SqliteHelper::table('backup_file')->where('id', '=', $id)->first();
            if (empty($file)) {
                throwException('备份文件不存在');
            }
            //开始删除
            SqliteHelper::table('backup_file')->where('id', '=', $id)->delete();
            unlink($file['file_path']);
        }

        return $this->successResponse('删除成功');
    }

    /**
     * 下载文件
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function downloadFile()
    {
        $field = ['id', 'num', 'backup_id'];
        $param = _apiParam($field);

        if (!empty($param['id'])) {
            $ids = explode(',', $param['id']);
            $file_paths = SqliteHelper::table('backup_file')->where('id', 'in', $ids)->column('file_path');
        } else {
            ValidateHelper::checkData($param, [
                'num' => 'required',
                'backup_id' => 'required|integer',
            ]);
            $file_paths = SqliteHelper::table('backup_file')
                ->where('num', $param['num'])
                ->where('backup_id', $param['backup_id'])
                ->column('file_path');
        }

        if (empty($file_paths)) {
            throwException('备份文件不存在');
        }
        if (count($file_paths) > 1) {
            $temp_dir = EASYSWOOLE_ROOT . '/Public/Temp';
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir, 0777, true);
            }
            $filename = session_create_id(date('YmdHis')) . '.zip';
            $res_path = $temp_dir . '/' . $filename;
            $fd = fopen($res_path, 'w');

            $options = new Archive();
            $options->setOutputStream($fd);

            $zip = new \ZipStream\ZipStream(null, $options);
            foreach ($file_paths as $file_path) {
                $zip->addFileFromPath(preg_replace('/.*\/Backup\/\d+\//', '', $file_path), $file_path);
            }
            $zip->finish();
            fclose($fd);
        } else {
            $res_path = $file_paths[0];
            $filename = basename($file_paths[0]);
        }

        $this->response()->withHeader('Content-Disposition', 'attachment; filename=' . urlencode($filename));
        $this->response()->withHeader('Content-Type', 'application/octet-stream');
        $this->response()->sendFile($res_path);
    }
}
