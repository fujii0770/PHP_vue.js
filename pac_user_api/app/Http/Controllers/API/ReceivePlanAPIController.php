<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Monolog\Handler\IFTTTHandler;
use Response;


/**
 * Class ReceivePlanAPIController
 * @package App\Http\Controllers\API
 */
class ReceivePlanAPIController extends AppBaseController
{

    public function getUrl(Request $request)
    {
        $user = $request->user();
        $company = DB::table('mst_company')
            ->select('receive_plan_flg','receive_plan_url')
            ->where('id',$user->mst_company_id)
            ->first();
        if ($company->receive_plan_flg){
            return $this->sendResponse($company->receive_plan_url, "");
        }
        return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
    }
    
}
