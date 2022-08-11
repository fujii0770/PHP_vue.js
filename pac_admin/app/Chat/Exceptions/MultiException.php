<?php

namespace App\Chat\Exceptions;

class MultiException extends ChatException {

    private $exceptions = [];

    public function add(\Exception $e) {
        $this->exceptions[] = (object) ["timestamp" => time(), "exception" => $e];
    }

    public function getExceptions() {
        return $this->exceptions;
    }

    public function __toString() {
        $ret = __CLASS__ . ": [{$this->code}]: {$this->message}\n";

        $es = $this->getExceptions();
        $mx = count($es);
        for ($i = 0; $i < $mx; $i++) {
            $ret .= "[$i] ".$es[$i];
        }
        return $ret;
    }

    public function getExceptionCodeName(){
        return "CE-ME";
    }
}
