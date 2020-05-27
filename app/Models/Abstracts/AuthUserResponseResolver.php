<?php

namespace App\Models\Abstracts;

use Psr\Http\Message\ResponseInterface;
use App\Exceptions\RestApi\AuthorizeRestApiException;
use App\Exceptions\RestApi\UndefinedRestApiException;
use App\Exceptions\RestApi\ResponseFormatRestApiException;

class AuthUserResponseResolver implements Contracts\ResponseResolver
{
    public function resolve(ResponseInterface $response)
    {
        $body = $response->getBody();
        $data = json_decode($body, true);
        if (null === $data) {
            throw new ResponseFormatRestApiException($body);
        }
        if (!array_key_exists('code', $data)) {
            throw new ResponseFormatRestApiException($body);
        }
        switch ($data['code']) {
            case 0:
            case 200:
                return $data['businessObj'];
            case 4001:
            case 4002:
                throw new AuthorizeRestApiException('过期重登录');
            default:
                throw new UndefinedRestApiException($data['message'], $data['code']);
        }
    }

}
