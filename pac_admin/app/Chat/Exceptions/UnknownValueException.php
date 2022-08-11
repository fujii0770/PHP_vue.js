<?php

namespace App\Chat\Exceptions;

class UnknownValueException extends ChatException
{
    public function getExceptionCodeName(){
        return "CE-UVE";
    }
}
