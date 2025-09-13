<?php


namespace App\HttpController\Common;

use App\Helper\AuthHelper;

class AuthController extends BaseController
{
    public function onRequest(?string $action): ?bool
    {
        if (parent::onRequest($action)) {
            if (!is_login()) {
                throwException('未登录或登录授权已过期', 401);
            }
            return true;
        }
        return false;
    }


}
