<?php

namespace App\Chat\Exceptions;

class ExistsSubdomainException extends ChatException
{
    public function getExceptionCodeName(){
        return "CE-ESE";
    }
}
