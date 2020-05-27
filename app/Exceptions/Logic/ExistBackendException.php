<?php
namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 后端 API 返回的数据已存在错误
 */
class ExistBackendException extends LogicException
{
    protected $code = 102001;

    protected $message = '数据已存在';

    /**
     * @param int         $statusCode
     * @param \Exception  $previous
     * @param array       $headers
     *
     * @return void
     */
    public function __construct(Exception $previous = null, $headers = [])
    {
        parent::__construct($this->message, $this->code, $previous, $headers);
    }

}
