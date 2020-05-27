<?php

namespace App\ServiceFactory\Consul;

class Services {

    private $_services = [];

    function __construct(array $services)
    {
        foreach ($services as $service) {
            $this->_services[] = new Service($service);
        }
    }

    /**
     * 第一个服务
     *
     * @return App\ServiceFactory\Consul\Service | boolean
     */
    function getFirst()
    {
        return isset($this->_services[0])?
            $this->_services[0]:
            false;
    }
}
