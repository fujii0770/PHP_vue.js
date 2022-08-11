<?php

namespace App\Chat\Exceptions;

class UnreservedDomainException extends ChatException
{
    public function getExceptionCodeName(){
        return "CE-UDE";
    }
}
