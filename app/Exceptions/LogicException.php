<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LogicException extends HttpException
{
    /**
     * Create a new logic exception instance.
     *
     * @param string                               $message
     * @param int                                  $code
     * @param \Exception                           $previous
     * @param array                                $headers
     *
     * @return void
     */
    public function __construct($message = null, $code = 0, Exception $previous = null, $headers = [])
    {
        parent::__construct(409, $message, $previous, $headers, $code);
    }

}
