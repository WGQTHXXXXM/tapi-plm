<?php

namespace App\Models\Abstracts;

use App\Exceptions\LogicException;
use Psr\Http\Message\ResponseInterface;
use App\Exceptions\RestApi\ExistRestApiException;
use App\Exceptions\RestApi\UnknownRestApiException;
use App\Exceptions\RestApi\NotFoundRestApiException;
use App\Exceptions\RestApi\AuthorizeRestApiException;
use App\Exceptions\RestApi\UndefinedRestApiException;
use App\Exceptions\RestApi\StatusCodeRestApiException;
use App\Exceptions\RestApi\ResponseFormatRestApiException;
use App\Exceptions\RestApi\InvalidParameterRestApiException;

class JavaRestResponseResolver implements Contracts\ResponseResolver
{
    public function resolve(ResponseInterface $response)
    {
//        if ($response->getStatusCode() !== 200) {
//            throw new StatusCodeRestApiException($response->getStatusCode());
//        }
        $body = $response->getBody();
        $data = json_decode($body, true);
        if (null === $data) {
            throw new ResponseFormatRestApiException($body);
        }
        if (!array_key_exists('code', $data)) {
            throw new ResponseFormatRestApiException($body);
        }
        if ($data['code'] > 1000)
            throw new LogicException($data['message']);
        switch ($data['code']) {
            case 0:
            case 200:
                return $data['businessObj'];
            case 201:
                throw new NotFoundRestApiException();
            case 300:
                throw new ExistRestApiException();
            case 400:
                throw new InvalidParameterRestApiException();
            case 401:
                throw new AuthorizeRestApiException();
            case 500:
                throw new UnknownRestApiException($data['message'] ?: '');
            default:
                throw new UndefinedRestApiException($data['message'], $data['code']);
        }
    }

}
