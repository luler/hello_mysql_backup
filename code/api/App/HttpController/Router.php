<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 10:45
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        $this->setGlobalMode(true);
        $this->setMethodNotAllowCallBack(function (Request $request, Response $response) {
            $response->redirect('/noRouter');
            return false;
        });
        $this->setRouterNotFoundCallBack(function (Request $request, Response $response) {
            $response->redirect('/noRouter');
            return false;
        });
        $routeCollector->get('/noRouter', function (Request $request, Response $response) {
            $response->withStatus(404);
            $response->withHeader('content-type', 'application/json;charset=utf-8');
            $response->write('{"message":"路由不存在","code":404,"info":[]}');
            return false;
        });

        // +----------------------------------------------------------------------
        // | 开发接口
        // +----------------------------------------------------------------------
        $routeCollector->addGroup('/api', function (RouteCollector $routeCollector) {
            // +----------------------------------------------------------------------
            // | 公共接口
            // +----------------------------------------------------------------------
            $routeCollector->get('/casLogin', 'api/home/LoginController/casLogin');
            $routeCollector->post('/getAccessToken', 'api/home/LoginController/getAccessToken');
            $routeCollector->get('/logout', 'api/home/LoginController/logout');
            $routeCollector->post('/changPassword', 'api/home/LoginController/changPassword');
            // +----------------------------------------------------------------------
            // | 前台接口
            // +----------------------------------------------------------------------
            $routeCollector->addGroup('/home', function (RouteCollector $routeCollector) {
                $routeCollector->get('/getUserInfo', 'api/home/IndexController/getUserInfo');
                $routeCollector->get('/getDatabaseList', 'api/home/IndexController/getDatabaseList');
                $routeCollector->post('/saveDatabase', 'api/home/IndexController/saveDatabase');
                $routeCollector->post('/delDatabase', 'api/home/IndexController/delDatabase');
                $routeCollector->get('/getDropDatabaseList', 'api/home/IndexController/getDropDatabaseList');
                $routeCollector->post('/startBackupImport', 'api/home/BackupImportController/startBackupImport');
                $routeCollector->get('/getBackupImportWorking', 'api/home/BackupImportController/getBackupImportWorking');
                //数据库备份
                $routeCollector->get('/getBackupList', 'api/home/BackupController/getBackupList');
                $routeCollector->post('/saveBackup', 'api/home/BackupController/saveBackup');
                $routeCollector->post('/delBackup', 'api/home/BackupController/delBackup');
                $routeCollector->post('/saveBackupStatus', 'api/home/BackupController/saveBackupStatus');
                $routeCollector->get('/getDatabaseTables', 'api/home/BackupController/getDatabaseTables');
                $routeCollector->post('/startBackup', 'api/home/BackupController/startBackup');
                $routeCollector->get('/getBackupWorking', 'api/home/BackupController/getBackupWorking');
                //备份文件
                $routeCollector->get('/getBackupFileList', 'api/home/BackupFileController/getBackupFileList');
                $routeCollector->get('/getDropBackupNumList', 'api/home/BackupFileController/getDropBackupNumList');
                $routeCollector->post('/delBackupFile', 'api/home/BackupFileController/delBackupFile');
                $routeCollector->get('/downloadFile', 'api/home/BackupFileController/downloadFile');
                //操作日志
                $routeCollector->get('/getOperationLogList', 'api/home/OperationLogController/getOperationLogList');
                $routeCollector->post('/delOperationLog', 'api/home/OperationLogController/delOperationLog');
            });
            // +----------------------------------------------------------------------
            // | 后台接口
            // +----------------------------------------------------------------------
            $routeCollector->addGroup('/admin', function (RouteCollector $routeCollector) {
                $routeCollector->get('/test', 'api/admin/IndexController/test');
            });
        });
    }
}
