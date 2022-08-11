<?php
namespace App\Chat\Properties;

class ChatAwsProperties extends AbstractProperties {

    /**
     * Access Key ID
     * 
     * @return string
     */
    public function access_key_id(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * Secret Access Key
     * 
     * @return string
     */
    public function secret_access_key(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * Region
     * 
     * @return string
     */
    public function region(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "ap-northeast-1");
    }

}
