<?php

namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回的用户授权错
 */

class AuthenticationException extends LogicException
{
    protected $code = 102005;

    protected $message = '对不起，您没有权限登录，请联系管理员';

    /**
     * @param \Exception $previous
     * @param array $headers
     *
     * @return void
     */
    public function __construct(Exception $previous = null, $headers = [])
    {
        parent::__construct($this->message, $this->code, $previous, $headers);
    }

}
