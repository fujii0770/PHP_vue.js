<?php

namespace App\Chat\Exceptions;

class InvalidSubdomainFormatException extends ChatException
{
    public function getExceptionCodeName(){
        return "CE-ISFE";
    }
}
