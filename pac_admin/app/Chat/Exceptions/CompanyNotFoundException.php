<?php

namespace App\Chat\Exceptions;

class CompanyNotFoundException extends ChatException {

    private $value = 0;

    public function __construct(string $value, $message="", $code=0, $previous=null)
    {
        parent::__construct($message, $code, $previous);
        $this->value = $value;
    }

    public function getDescribe(): string
    {
        return __("api.fail.value_not_exists", [
            "column"=>__("api.columns.company"),
            "value"=>$this->value]);
    }

}
