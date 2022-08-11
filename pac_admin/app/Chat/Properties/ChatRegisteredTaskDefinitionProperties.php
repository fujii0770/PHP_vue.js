<?php
namespace App\Chat\Properties;


class ChatRegisteredTaskDefinitionProperties extends AbstractProperties {
    
    /**
     * @return string
     */
    public function task_definition_arn(string $value = null) {
        return $this->_getset_immutable(__FUNCTION__, func_get_args());
    }
    
}
