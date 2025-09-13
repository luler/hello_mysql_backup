<?php


namespace App\HttpController\Api\Home;

use App\Helper\AuthHelper;
use App\Helper\CasHelper;
use App\Helper\ConfigHelper;
use App\Helper\SqliteHelper;
use App\Helper\ValidateHelper;
use App\HttpController\Common\BaseController;
use App\Service\UserService;

class LoginController extends BaseController
{
    /**
     * 登录接口
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getAccessToken()
    {
        $this->limitRequestRate(1, 10);
        $field = ['appid', 'appsecret'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'appid|账号' => 'required',
            'appsecret|密码' => 'required',
        ]);
        again:
        $user = SqliteHelper::table('users')
            ->where('appid', $param['appid'])
            ->first();
        if (empty($user)) {
            if ($param['appid'] == ConfigHelper::get('app.admin_user')) { //初始化admin
                SqliteHelper::table('users')->insert([
                    'appid' => ConfigHelper::get('app.admin_user'),
                    'appsecret' => UserService::createPassword(ConfigHelper::get('app.admin_password')),
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                goto  again;
            }
            throwException('用户不存在');
        }
        if ($user['appsecret'] !== UserService::createPassword($param['appsecret'])) {
            throwException('密码输入有误');
        }

        $info = AuthHelper::getInstance()->createAuthorization([
            'id' => $user['id'],
            'appid' => $user['appid'],
        ]);
        $this->successResponse('获取成功', $info);
    }

    /**
     * CAS登录
     * @return void
     * @throws \Exception
     */
    public function casLogin()
    {
        $field = ['code', 'open_id'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'code' => 'required',
            'open_id' => 'required',
        ]);

        $msg = '当前用户无权限';
        try {
            $res = CasHelper::getInstance()->getUserInfo($param['code']);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        if (!isset($res['is_admin']) || $res['is_admin'] != 1) {
            return $this->response()->write('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>温馨提示</title></head><body><div style="text-align: center;font-size: 64px;font-weight: bold;position: absolute;width:100%;top: 30%">' . $msg . '</div></body></html>');
        }
        $user = SqliteHelper::table('users')->where('cas_open_id', $param['open_id'])->first();
        if (empty($user)) {
            $user = SqliteHelper::table('users')->where('appid', $res['username'])->first();
            if (empty($user)) {
                SqliteHelper::table('users')->insert([
                    'appid' => $res['username'],
                    'appsecret' => UserService::createPassword(ConfigHelper::get('app.admin_password')),
                    'cas_open_id' => $param['open_id'],
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                $user = SqliteHelper::table('users')->where('appid', $res['username'])->first();
            } else {
                SqliteHelper::table('users')->where('appid', $res['username'])->update([
                    'cas_open_id' => $param['open_id'],
                    'update_time' => time(),
                ]);
            }
        }

        $info = AuthHelper::getInstance()->createAuthorization([
            'id' => $user['id'],
            'appid' => $user['appid'],
        ]);

        $this->response()->redirect('/?authorization=' . $info);
    }

    /**
     * 退出登录
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function logout()
    {
        $field = ['redirect', 'authorization'];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'authorization|授权token' => 'required',
            'redirect|回调地址' => 'required',
        ]);

        AuthHelper::getInstance()->logout($param['authorization']);
        $this->response()->redirect($param['redirect']);
    }

    /**
     * @logname 修改密码
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function changPassword()
    {
        if (!is_login()) {
            throwException('请先登录');
        }
        $field = ['password', 'confirm_password',];
        $param = _apiParam($field);
        ValidateHelper::checkData($param, [
            'password|密码' => 'required|mbLengthMin:6',
            'confirm_password|确认密码' => 'required',
        ]);
        if ($param['confirm_password'] != $param['password']) {
            throwException('密码与确认密码不一致');
        }

        SqliteHelper::table('users')->where('id', is_login())->update([
            'appsecret' => UserService::createPassword($param['password']),
            'update_time' => time(),
        ]);
        return $this->successResponse('修改密码成功');
    }
}
