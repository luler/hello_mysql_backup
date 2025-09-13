<?php


namespace App\HttpController\Api\Admin;

use App\HttpController\Common\AdminBaseController;

class IndexController extends AdminBaseController
{
    /**
     * 测试
     * @author 我只想看看蓝天 <1207032539@qq.com>
     */
    public function test()
    {
        $res = [];
        $res['ip'] = $this->clientRealIP();
        $this->successResponse('获取成功', $res);
    }


}
