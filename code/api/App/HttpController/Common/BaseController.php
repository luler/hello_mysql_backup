<?php


namespace App\HttpController\Common;

use App\Helper\AuthHelper;
use App\Helper\ConfigHelper;
use App\Service\OperationLogService;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\FastCache\Cache;

class BaseController extends ApiController
{
    protected function onRequest(?string $action): ?bool
    {
        //给每个请求注入全局变量
        ContextManager::getInstance()->set('request', $this->request());
        //检查登录
        try {
            $authorization = $this->getAuthorization();
            if (!empty($authorization)) {
                $res = AuthHelper::getInstance()->checkAuthorization($authorization);
            }
            $this->request()->withAttribute('__user_info__', $res ?? []);
        } catch (\Exception $e) {
            //
        }
        //记录日志
        $ref = new \ReflectionMethod($this->getAllowMethodReflections()[$this->getActionName()]->class, $this->getActionName());
        $parser = new \Anodoc\Parser();
        $log_name = $parser->parse($ref->getDocComment())->getTag(ConfigHelper::get('simple_log.tag'))->getValue();
        if (!empty($log_name)) {
            $param = _apiParam();
            isset($param['password']) && $param['password'] = '***'; //脱敏
            OperationLogService::saveLog(is_login() ?: 0, $log_name, $this->request()->getSwooleRequest()->server['path_info'], json_encode($param, 256));
        }
        return true;
    }

    /**
     * 获取用户的真实IP
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP($headerName = 'x-real-ip')
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');
        if ($clientAddress === '127.0.0.1') {
            if (!empty($xri)) {  // 如果有xri 则判定为前端有NGINX等代理
                $clientAddress = $xri[0];
            } elseif (!empty($xff)) {  // 如果不存在xri 则继续判断xff
                $list = explode(',', $xff[0]);
                if (isset($list[0])) $clientAddress = $list[0];
            }
        }
        return $clientAddress;
    }

    /**
     * 捕获异常
     * @param \Throwable $throwable
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    protected function onException(\Throwable $throwable): void
    {
        if (in_array($throwable->getCode(), [400, 401, 403, 404, 405, 429, 500, 501, 502, 503])) {
            $this->commonResponse($throwable->getCode(), $throwable->getMessage());
        } else {
            //找不到异常原因，就统一报500
            $this->commonResponse(500, $throwable->getMessage());
        }
    }

    /**
     * dump出数据
     * @param $object
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    protected function dump($object)
    {
        ob_start();
        var_dump($object);
        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        if (!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, ENT_SUBSTITUTE);
        }
        $output = '<pre>' . $output . '</pre>';
        $this->response()->write($output);
    }

    /**
     * dump出数据后，立即结束返回
     * @param $object
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    protected function halt($object)
    {
        ob_start();
        var_dump($object);
        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        if (!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, ENT_SUBSTITUTE);
        }
        $output = '<pre>' . $output . '</pre>';
        $this->response()->write($output);
        $this->response()->end();
        throw new \Exception(); //停止往下执行
    }

    /**
     * 限制请求次数（基于ip唯一）
     * @param int $minutes
     * @param int $count
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function limitRequestRate(int $minutes = 1, int $count = 60)
    {
        $ip = $this->clientRealIP();
        $key = 'limitRequestRate:' . $ip;
        $times = Cache::getInstance()->get($key) ?: [];
        $current_time = time();
        $start_time = $current_time - $minutes * 60;
        $minutes_times = [];
        foreach ($times as $time) {
            if ($time > $start_time) {
                $minutes_times[] = $time;
            }
        }
        if (count($minutes_times) >= $count) {
            throwException('频率限制触发，已联系开发人员处理', 429);
        }
        $minutes_times[] = $current_time;
        Cache::getInstance()->set($key, $minutes_times, $minutes * 60);
    }

    /**
     * 获取请求中携带的授权token
     * @return array|mixed|string|string[]
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function getAuthorization()
    {
        $authorization = $this->request()->getHeader('authorization')[0] ?? '';
        if (empty($authorization)) {
            $authorization = _apiParam(['authorization'])['authorization'] ?? '';
        }
        return $authorization;
    }
}
