<?php
namespace App\Chat\Properties;


class ChatTaskProperties extends AbstractProperties {
    /**
     * @return string
     */
    public function task_arn(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function service_arn(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function cluster_arn(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function task_definition(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }
    
}
