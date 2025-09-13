<?php


namespace App\HttpController\Common;

use EasySwoole\Http\AbstractInterface\Controller;

class ApiController extends Controller
{
    /**
     * 返回提示语
     */
    protected $returnMessage = 'system error';
    /**
     * @var string
     */
    protected $returnCode = '500';
    /**
     * 返回数据列表
     */
    protected $returnInfo = [];

    protected function setReturnMessage($message)
    {
        $this->returnMessage = $message;
        return $this;
    }

    protected function setReturnCode($code)
    {
        $this->returnCode = $code;
        return $this;
    }

    protected function setReturnInfo($data = [])
    {
        $this->returnInfo = $data;
        return $this;
    }

    /**
     * 成功返回操作，已默认code=200
     */
    protected function successResponse($message = 'success', $info = [])
    {
        return $this->setReturnCode(200)->setReturnMessage($message)->setReturnInfo($info)->returnDo();
    }

    /**
     * 资源创建成功返回
     */
    protected function createdResponse($message = 'success', $info = [])
    {
        return $this->setReturnCode(201)->returnDo();
    }

    /**
     * 资源删除后无内容返回
     */
    protected function noContentResponse()
    {
        return $this->setReturnCode(204)->returnDo();
    }

    /**
     * 常规失败返回操作，已默认code=400
     */
    protected function errorResponse($message = 'error', $info = [])
    {
        return $this->setReturnCode(400)->setReturnMessage($message)->setReturnInfo($info)->returnDo();
    }

    /**
     * 通用返回
     */
    protected function commonResponse($code = 200, $message = 'success', $info = [])
    {
        return $this->setReturnCode($code)->setReturnMessage($message)->setReturnInfo($info)->returnDo();
    }

    /**
     * 返回接口数据
     */
    protected function returnDo()
    {
        //设置返回内容
        $return = [
            'message' => $this->returnMessage,
            'code' => (int)$this->returnCode,
            'info' => $this->returnInfo
        ];

        $this->response()->withStatus($return['code']);
        $this->response()->withHeader('content-type', 'application/json;charset=utf-8');
        $this->response()->write(json_encode($return, 256));
    }

    function index()
    {
        // TODO: Implement index() method.
    }
}
