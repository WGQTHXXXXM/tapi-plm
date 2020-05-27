<?php
namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回的数据不存在错误
 */
class NotFoundBackendException extends LogicException
{
    protected $code = 102002;

    protected $message = '数据不存在';

    /**
     * @param int         $statusCode
     * @param \Exception  $previous
     * @param array       $headers
     *
     * @return void
     */
    public function __construct($message = null, Exception $previous = null, $headers = [])
    {
		if (!empty($message)) $this->message = $message;
        parent::__construct($this->message, $this->code, $previous, $headers);
    }

}
