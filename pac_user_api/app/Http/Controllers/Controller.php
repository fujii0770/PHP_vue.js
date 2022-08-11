<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getTablePasswordReset($accountType){
        return $accountType == AppUtils::ACCOUNT_TYPE_AUDIT ? 'audit_password_resets':'user_password_resets';
    }

    protected function getTableAccount($accountType){
        return $accountType == AppUtils::ACCOUNT_TYPE_AUDIT ? 'mst_audit' : 'mst_user';
    }

}