<?php
namespace App\Chat\Properties;

class ChatEcsServiceProperties extends AbstractProperties {
    /**
     * @return string
     */
    public function cluster(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function service_name(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * タスク定義のfamily:リビジョン
     * @return string
     */
    public function task_definition(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function service_arn(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }
}
