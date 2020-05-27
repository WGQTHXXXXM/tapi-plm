<?php
namespace App\Exceptions\RestApi;

use Exception;
use App\Exceptions\RestApiException;

class InvalidParameterRestApiException extends RestApiException
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
    public function __construct($message = 'Invalid Parameters', $code = 201003, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
