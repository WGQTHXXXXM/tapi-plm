<?php

namespace App\Models;

use App\Exceptions\RestApiException;
use App\Models\Abstracts\RestApiModelWithConsul;

class EmployeeCenter extends RestApiModelWithConsul
{
    protected static $service_name = 'employee-center-service';

    protected $primaryKey = 'guid';

    public static $apiMap = [
        'getUserByPhone' => ['method' => 'GET', 'path' => 'employee/phone/:phone'],
        'getUserByGuid' => ['method' => 'GET', 'path' => 'employee/guid/:guid'],
    ];


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

    public static function getUserByGuid($guid)
    {
        try {
            $response = self::getItem('getUserByGuid', [
                ':guid' => $guid
            ]);
        } catch (RestApiException $e) {
            return  new static();
        }

        return $response;
    }

}
