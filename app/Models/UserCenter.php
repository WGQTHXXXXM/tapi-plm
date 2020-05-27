<?php
/**
 * Created by PhpStorm.
 * User: weijinlong
 * Date: 2018/8/24
 * Time: 下午4:01
 */

namespace App\Models;

use App\Exceptions\RestApiException;
use App\Models\Abstracts\RestApiModelWithConsul;

class UserCenter extends RestApiModelWithConsul
{
    protected static $service_name = 'user-center-service';

    protected $primaryKey = 'guid';

    public static $apiMap = [
        'getUserByPhone' => ['method' => 'GET', 'path' => 'user/phone/:phone'],
        'getUsers' => ['method' => 'POST', 'path' => 'users'],
        'loginSecretKey' => ['method' => 'get', 'path' => 'login/secretkey'],
        'login' => ['method' => 'POST', 'path' => 'login'],

    ];

    public static function getUsers(Array $ids)
    {


        $response = self::getData('getUsers', [], [
            'ids' => $ids,
        ]);
        return $response;
    }


    public static function retrieveByPhone($phone)
    {
        try {
            $response = self::getItem('getUserByPhone', [
                ':phone' => $phone
            ]);
        } catch (RestApiException $e) {
            return  new static();
        }

        return $response;
    }

    public static function loginSecretKey()
    {
        $response = self::getData('loginSecretKey');
        return $response;

    }

    public static function login($params)
    {
        $data = UserCenter::loginSecretKey();
        $key = $data['publicKey'];
        $key_eol   = (string) implode("\n", str_split((string) $key, 64));
        $publicKey = (string) "-----BEGIN PUBLIC KEY-----\n" . $key_eol . "\n-----END PUBLIC KEY-----";
        $name = '';
        $password = '';
        openssl_public_encrypt($params['name'],$name,$publicKey,OPENSSL_PKCS1_PADDING);
        openssl_public_encrypt($params['password'],$password,$publicKey,OPENSSL_PKCS1_PADDING);
        $name = base64_encode($name);
        $password = base64_encode($password);
        $response = self::getData('login',[],[
            'account'=>$name,
            'password'=>$password,
            'timeStamp'=>$data['timeStamp'],
            'type'=>1,
        ]);
        return $response['token'];
    }

}
