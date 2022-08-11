<?php
namespace App\Chat\Properties;

class PlainProperties extends AbstractProperties {

    final public function getset(string $key, array $args = [], $default = null) {
        return $this->_getset($key, $args, $default);
    }

    final public function getset_immutable(string $key, array $args = [], $default = null) {
        return $this->_getset_immutable($key, $args, $default);
    }
}
