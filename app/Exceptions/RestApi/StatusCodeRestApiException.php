<?php
namespace App\Exceptions\RestApi;

use Exception;
use App\Exceptions\RestApiException;

class StatusCodeRestApiException extends RestApiException
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
    public function __construct($statusCode, $code = 202001, Exception $previous = null)
    {
        $message = 'The response status code is '.$statusCode;
        parent::__construct($message, $code, $previous);
    }

}
