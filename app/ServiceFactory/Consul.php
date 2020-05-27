<?php
namespace App\ServiceFactory;

class Consul {

    public $count = 0;

    /**
     * consul sdk
     *
     * @var SensioLabs\Consul\ServiceFactory
     */
    private $_sf;

    /**
     * 已获取服务列表缓存
     *
     * @var array
     */
    private $_services_cache = [];

    function __construct()
    {
        $options = [
            'base_uri' => env("CONSUL_HOST")
        ];
        $this->_sf = new \SensioLabs\Consul\ServiceFactory($options);
    }

    /**
     * 获取服务
     *
     * @param   string $service_name 服务名称
     * @return  App\ServiceFactory\Consol\Services
     */
    function getServices(string $service_name)
    {
        if (isset($this->_services_cache[$service_name])) {
            return $this->_services_cache[$service_name];
        } else {
            $catelog = $this->_sf->get(\SensioLabs\Consul\Services\CatalogInterface::class);
            $response = $catelog->service($service_name);
            $services = $response->json();
            $list = new \App\ServiceFactory\Consul\Services($services);
            $this->_services_cache[$service_name] = $list;
            return $list;
        }
    }

    public function registerService($body){

        $agent = $this->_sf->get(\SensioLabs\Consul\Services\AgentInterface::class);

        $result = $agent->registerService($body)->getStatusCode();

        return $result;
    }
}
