<?php
namespace App\Models\Abstracts;

use App\Models\Abstracts\Contracts\ResponseResolver;
use Psr\Http\Message\ResponseInterface;
use App\Exceptions\RestApi\ExistRestApiException;
use App\Exceptions\RestApi\UnknownRestApiException;
use App\Exceptions\RestApi\NotFoundRestApiException;
use App\Exceptions\RestApi\AuthorizeRestApiException;
use App\Exceptions\RestApi\UndefinedRestApiException;
use App\Exceptions\RestApi\StatusCodeRestApiException;
use App\Exceptions\RestApi\ResponseFormatRestApiException;
use App\Exceptions\RestApi\InvalidParameterRestApiException;

class PhpOldRestResponseResolver implements ResponseResolver
{
    public function resolve(ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 400) {
            throw new StatusCodeRestApiException($response->getStatusCode());
        }
        $body = $response->getBody();
        $data = json_decode($body, true);
        if (null === $data) {
            throw new ResponseFormatRestApiException($body);
        }
        return $data;
    }

}
