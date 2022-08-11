<?php

namespace App\Http\Controllers\API;

class ResponseBody
{

    private $data;
    private $message = null;
    private $code = 0;

    public function __construct($data = null, string $message = "", int $code = 1)
    {
        $this->data = $data;
        $this->message = $message;
        $this->code = $code;
    }

    public function getData() {
        return $this->data;
    }

    public function getMessage(){
        return $this->message;
    }

    public function getCode() {
        return $this->code;
    }
}
