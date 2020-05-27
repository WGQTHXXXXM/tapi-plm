<?php
namespace App\Exceptions;

use Exception;
use LogicException;

class RestApiException extends LogicException
{
    /**
     * Create a new logic exception instance.
     *
     * @param string                               $message
     * @param int                                  $code
     * @param \Exception                           $previous
     *
     * @return void
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
