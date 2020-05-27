<?php
namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回的参数错误
 */
class ContactDataException extends LogicException
{
    protected $code = 102004;

    protected $message = '参数错误';

    /**
     * ContactDataException constructor.
     * @param null $message
     * @param Exception|null $previous
     * @param array $headers
     */
    public function __construct($message = null, Exception $previous = null, $headers = [])
    {
		if (!empty($message)) $this->message = $message;
        parent::__construct($this->message, $this->code, $previous, $headers);
    }

}
