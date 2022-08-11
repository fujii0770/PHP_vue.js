<?php

namespace App\Chat\Exceptions;

use App\Http\Controllers\API\PacException;

class ChatException extends PacException {

    public function getExceptionCodeName() {
        return "CE";
    }

    private $result = [];

    public function setResult(array $result) : self {
        $this->result = $result;
        return $this;
    }

    public function getResult() : array {
        return $this->result;
    }

    public function getDescribe() : string {
        $ret = $this->getMessage();
        if (empty($ret)) {
            $ret = parent::getDescribe();
        }
        return $ret;
    }
}
