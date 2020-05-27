<?php
namespace App\Exceptions\Logic;

use App\Exceptions\ServerHttpException;
use Exception;

/*
 * 后端 API 返回未知错误
 */
class UnknownBackendException extends ServerHttpException
{
    protected $code = 201003;

    protected $message = '';

    /**
     * @param string      $message
     * @param \Exception  $previous
     * @param array       $headers
     *
     * @return void
     */
    public function __construct($message, Exception $previous = null, $headers = [])
    {
        parent::__construct($message, $this->code, $previous, $headers);
    }

}
