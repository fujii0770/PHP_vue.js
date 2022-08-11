<?php
namespace App\Chat\Properties;


class ChatReservedIdServerProperties extends AbstractProperties {
    /**
     * @return string
     */
    public function cluster_arn(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function image(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function mongo_url(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function tenant_key(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }
    
    /**
     * @return string
     */
    public function server_url(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }

        
    /**
     * @return int
     */
    public function company_id(int $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function subdomain(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }
    
}
