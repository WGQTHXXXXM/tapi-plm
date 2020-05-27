<?php

namespace App\ServiceFactory\Consul;

class Service {

    private $_service = [];

    function __construct($service)
    {
        $this->_service = $service;
    }

    /**
     *
     *
     * @return string
     */
    function getId()
    {
        return isset($this->_service['ID'])?
            $this->_service['ID']:
            '';
    }

    /**
     * base uri
     *
     * @return string
     */
    function getBaseUri()
    {
        return sprintf('http://%s:%s/%s/',
            trim($this->_service['ServiceAddress'], '/'),
            trim($this->_service['ServicePort'], '/'),
            trim($this->_getTag('contextPath', $this->_service['ServiceTags']), '/')
        );
    }

    /**
     * 
     * @return string
     */
    private function _getTag($tag_name, array $tag_list)
    {
        foreach ($tag_list as $tag_string) {
            list($key, $value) = explode('=', $tag_string);
            if ($key == $tag_name) {
                return $value;
            }
        }
        return '';
    }
}
