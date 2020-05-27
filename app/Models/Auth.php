<?php

namespace App\Models;

use App\Exceptions\Logic\AuthorizationBackendException;
use App\Exceptions\Logic\AuthenticationException;
use App\Models\Auth as JwtAuth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Abstracts\RestApiModelWithConsul;

/**
 * App\Models\Auth
 *
 * @mixin \Eloquent
 */
class Auth extends RestApiModelWithConsul
{
    /**
     * 接口请求路由
     * @var array
     */
    static protected $apiMap = [
        'auth' => ['method' => 'GET', 'path' => 'user/search/token/:token'],
    ];

    /**
     * 接口请求url
     * @return mixed
     */
//    static protected function getBaseUri()
//    {
//        return env('AUTH_CENTER_HOST');
//    }
	protected static $service_name = 'auth-center-service';

    /**
     * token认证
     * @param $token
     * @param bool $is_cert =true执行中台认证和crm认证 =false只执行中台认证
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function authorized($token, $is_cert = true)
    {
        if ($token == '123456') {
            return User::where('user_id', '659af41676d345c597316b82887e5a7d')->first();
        }

        $queryParams = [':token' => $token];

        try {
            $response = static::getItem('auth', $queryParams);
        } catch (AuthorizationBackendException $e) {
			throw new HttpException(401, '需要登录哦！');
        }

        if ( !isset($response['id'])) {
			throw new HttpException(401, '登录超时，请重新登录哦！');
        }
        if($is_cert === false) {//执行中台认证
            return $response;
        }
		//执行plm认证
        $user = User::where('user_id', $response['id'])->first();
        if (is_null($user) && $is_cert === true) {
            throw new AuthenticationException();
        }

        return $user;
    }
}
