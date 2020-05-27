<?php
namespace App\Models\Abstracts\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseResolver
{
    /**
     * Determine if the given configuration value exists.
     *
     * @param Psr\Http\Message\ResponseInterface  $response
     * @return mixed
     */
    public function resolve(ResponseInterface $response);
}
