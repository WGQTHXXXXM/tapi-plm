<?php
namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回的参数错误
 */
class ParameterBackendException extends LogicException
{
    protected $code = 102003;

    protected $message = '参数错误';

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
