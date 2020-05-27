<?php

namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回的用户授权错
 */
class AuthorizationBackendException extends LogicException {

    protected $code = 102004;

    protected $message = '后端授权失败';

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
