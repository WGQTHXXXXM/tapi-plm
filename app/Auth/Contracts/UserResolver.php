<?php
namespace App\Auth\Contracts;

interface UserResolver
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \App\Auth\Contracts\UserResolver|null
     */
    public static function retrieveById($identifier);


    /**
     * Retrieve a user by their token.
     *
     * @param  mixed  $token
     * @return \App\Auth\Contracts\UserResolver|null
     */
    public static function retrieveByToken($token);
}
