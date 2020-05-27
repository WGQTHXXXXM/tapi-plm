<?php

namespace App\Http\Requests;

use App\Exceptions\AuthorizeException;
use App\Exceptions\ClientHttpException;
use Illuminate\Foundation\Http\FormRequest;
use \Illuminate\Contracts\Validation\Validator;

abstract class BaseRequest extends FormRequest
{
    abstract public function errorCode(): array;

    public $authorizationMsg = '没有权限';


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $message = null;
        if (count($errors)) {
            $firstKey = array_keys($errors)[0];
            $message = $errors[$firstKey][0];
        }

        $errorCodes = $this->errorCode();
        $errorCode = isset($errorCodes[$firstKey]) ? $errorCodes[$firstKey] : 1000;

        throw new ClientHttpException($message, $errorCode);
    }


    protected function failedAuthorization()
    {

        throw new AuthorizeException($this->authorizationMsg,4000);
    }


}
