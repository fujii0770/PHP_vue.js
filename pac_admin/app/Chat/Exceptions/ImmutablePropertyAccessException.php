<?php

namespace App\Chat\Exceptions;

class ImmutablePropertyAccessException extends ChatException
{
    public function getExceptionCodeName(){
        return "CE-IPAE";
    }
}
