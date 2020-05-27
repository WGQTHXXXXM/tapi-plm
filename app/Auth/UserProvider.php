<?php
namespace App\Auth;

use App\Models\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as Provider;

class UserProvider implements Provider
{
    /**
     * The provider config.
     *
     * @var Array
     */
    protected $config;

    /**
     * The user resolver.
     *
     * @var \App\Auth\Contracts\UserResolver
     */
    protected $userResolver;


    public function __construct(array $config = [])
    {
        $this->config = $config;
    }


    /**
     * Retrieve a user by their unique identifier.
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->userResolver()->retrieveById($identifier); 
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * @param  mixed  $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     * @return bool
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        return true;
    }

    /**
     * Retrieve a user by the given credentials.
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!array_key_exists('api_token', $credentials)) {
            return null;
        }

        return $this->userResolver()->retrieveByToken($credentials['api_token']);
    }

    /**
     * Rules a user against the given credentials.
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }


    /**
     * Get an user resolver
     * @return \App\Auth\Contracts\UserResolver
     */
    protected function userResolver()
    {
        if (is_null($this->userResolver)) {
            $resolverClass = $this->config['resolver'] ?: Auth::class;
            $this->userResolver = new $resolverClass;
        }

        return $this->userResolver;
    }

}
