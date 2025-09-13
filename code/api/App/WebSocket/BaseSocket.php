<?php

namespace App\WebSocket;

class BaseSocket extends ApiSocket
{
    /**
     * 空参数时默认调用该方法
     * @Author: 我只想看看蓝天 <1207032539@qq.com>
     */
    public function index()
    {
        $this->response()->setMessage('hello world！');
    }

    /**
     * 获取请求参数(白名单筛选)
     * @param array $fields
     * @return array|mixed
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    protected function _apiParam(array $fields = [])
    {
        $param = $this->caller()->getArgs()['param'];
        if (!empty($fields)) {
            foreach ($param as $key => $value) {
                if (!in_array($key, $fields)) {
                    unset($param[$key]);
                }
            }
        }
        return $param;
    }
}