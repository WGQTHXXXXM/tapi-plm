<?php

namespace App\Models\Abstracts;

use App\Exceptions\LogicException;
use Psr\Http\Message\ResponseInterface;
use App\Exceptions\RestApi\AuthorizeRestApiException;
use App\Exceptions\RestApi\UndefinedRestApiException;
use App\Exceptions\RestApi\ResponseFormatRestApiException;

class WikiResponseResolver implements Contracts\ResponseResolver
{
    public function resolve(ResponseInterface $response)
    {
        $body = $response->getBody();
        $data = json_decode($body, true);
        if (null === $data) {
            $html = (string)$body;
            preg_match('/<title>登录/',$html,$matches);
            if(!empty($matches)){//显示的是登录页面，说明没有权限
                throw new LogicException('登录wiki失败');
            }
            preg_match('/<!DOCTYPE html>/',$html,$matches);
            if(empty($matches)){//说明不是html
                throw new ResponseFormatRestApiException($body);
            }
            return $response;
        }
        if (!array_key_exists('statusCode', $data)) {
            return $data;
        }
        switch ($data['statusCode']) {
            case 0:
                throw new AuthorizeRestApiException('过期重登录');
            default:
                throw new UndefinedRestApiException($data['message'], $data['statusCode']);
        }

    }

}
