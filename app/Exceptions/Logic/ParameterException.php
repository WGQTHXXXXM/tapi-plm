<?php
namespace App\Exceptions\Logic;

use Exception;
use App\Exceptions\LogicException;

/*
 * 参数错误
 */
class ParameterException extends LogicException
{
    protected $code = 101001;

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
