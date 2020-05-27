<?php
namespace App\Exceptions\RestApi;

use Exception;
use App\Exceptions\RestApiException;

class ResponseFormatRestApiException extends RestApiException
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
    public function __construct($body = null, $code = 202002, Exception $previous = null)
    {
        $message = 'The response body of backend API is invalid. Body:'.(string)$body;
        parent::__construct($message, $code, $previous);
    }

}
