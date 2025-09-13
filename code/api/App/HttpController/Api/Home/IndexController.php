<?php


namespace App\HttpController\Api\Home;

use App\Helper\SqliteHelper;
use App\Helper\ValidateHelper;
use App\HttpController\Common\HomeBaseController;
use App\Task\DeleteBackupTask;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\Config;

class IndexController extends HomeBaseController
{
    /**
     * 获取用户信息
     * @throws \Doctrine\DBAL\Exception
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getUserInfo()
    {
        $info = SqliteHelper::table('users')->where('id', '=', is_login())->first();
        unset($info['appsecret']);
        return $this->successResponse('获取成功', $info);
    }

    /**
     * 获取数据库列表
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getDatabaseList()
    {
        $field = ['search',];
        $param = _apiParam($field);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['host|port|user|database|title', 'like', "%{$param['search']}%"];
        }
        $res = SqliteHelper::table('database')->where($where)->orderBy('id', 'desc')->paginate();
        foreach ($res['list'] as &$value) {
            $value['create_time'] = date_simple_format($value['create_time']);
            $value['update_time'] = date_simple_format($value['update_time']);
        }
        return $this->successResponse('获取成功', $res);
    }

    /**
     * 获取数据库下拉列表
     * @return void
     */
    public function getDropDatabaseList()
    {
        $field = ['search',];
        $param = _apiParam($field);
        $where = [];
        if (!empty($param['search'])) {
            $where[] = ['host|port|user|database|title', 'like', "%{$param['search']}%"];
        }
        $res = SqliteHelper::table('database')->where($where)->orderBy('id', 'desc')->select(['id', 'title'])->get();
        return $this->successResponse('获取成功', $res);
    }

    /**
     * @logname 保存数据库信息
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function saveDatabase()
    {
        $field = ['id', 'title', 'host', 'port', 'user', 'password', 'database', 'can_import',];
        $param = _apiParam($field);
        $param['can_import'] = $param['can_import'] ?? 0;
        ValidateHelper::checkData($param, [
            'title|名称' => 'required|mbLengthMax:255',
            'host|主机' => 'required',
            'port|端口' => 'required|integer',
            'user|账号' => 'required',
            'password|密码' => 'required',
            'database|数据库' => 'required',
            'can_import|是否可以导入备份' => 'required|in:0,1',
        ]);
        $config = new Config($param);
        $client = new Client($config);
        if (!$client->connect()) {
            throwException('配置有误，无法建立有效连接');
        }
        $db = SqliteHelper::table('database');
        if ($db->where('title', $param['title'])->where('id', '!=', $param['id'] ?? 0)->count()) {
            throwException('名称已被使用');
        }
        if (!empty($param['id'])) {
            $param['update_time'] = time();
            SqliteHelper::table('database')->where('id', '=', $param['id'])->update($param);
        } else {
            $param['create_time'] = time();
            $param['update_time'] = time();
            SqliteHelper::table('database')->insert($param);
        }
        return $this->successResponse('保存成功');
    }

    /**
     * @logname 删除配置
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function delDatabase()
    {
        $field = ['id',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'id' => 'required',
        ]);

        SqliteHelper::table('database')->where('id', '=', $param['id'])->delete();
        TaskManager::getInstance()->async(new DeleteBackupTask(['type' => 'database', 'id' => $param['id']]));
        return $this->successResponse('删除成功');
    }
}
