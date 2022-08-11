<?php


namespace App\Http\Utils;

class ChatUtils
{
    const ACTION_TYPE_REGISTER = 1;
    const ACTION_TYPE_CANCEL = 0;
    const ACTION_TYPE_STOP = 2;
    const ACTION_TYPE_UNSTOP = 3;

    const ACTION_SINGLE_REGISTER = 'singleRegister';
    const ACTION_MULTIPLE_REGISTER = 'multipleRegister';
    const ACTION_SINGLE_DELETE = 'singleDelete';
    const ACTION_MULTIPLE_DELETE = 'multipleDelete';
    const ACTION_SINGLE_STOP = 'singleStop';
    const ACTION_MULTIPLE_STOP = 'multipleStop';
    const ACTION_SINGLE_UNSTOP = 'singleUnstop';

    const ACTION_GROUP_REGISTER = 'register';
    const ACTION_GROUP_DELETE = 'delete';
    const ACTION_GROUP_STOP = 'stop';
    const ACTION_GROUP_UNSTOP = 'unstop';

    const SINGLE_DATA = 1;
    const NONE_DATA = 0;


}
