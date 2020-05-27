<?php
namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回操作错误
 */
class OperateException extends LogicException
{
    protected $code = 103001;

    protected $message = '不能执行该操作';

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
