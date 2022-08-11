<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Models\AppRole;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;

/**
 * Class GroupwareAppRoleAPIController
 * @package App\Http\Controllers\API
 */

class GroupwareAppRoleAPIController extends AppBaseController
{
    //var $table = 'app_role';
    var $table = 'mst_application_companies';   //app_roleが出来たら差し替える
    var $model = null;

    public function __construct(AppRole $appRole)
    {
        $this->model = $appRole;
    }

    /**
     * グループウェアの権限を取得する
     * GET|HEAD /groupware/app_role
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function app_role(Request $request)
    {
        $user   = \Auth::user();
        try {
            // 掲示板の設定を取得する
            //$board = DB::table('app_role')
//            $board = DB::table('mst_application_companies')
//                ->where('mst_company_id', $user->mst_company_id)
//                ->where('mst_application_id', AppUtils::GW_APPLICATION_ID_BOARD)
//                ->first();
//
//            $item['board_flg'] = is_null($board) ? 0 : 1;
            $auth = ApplicationAuthUtils::getUserRole($user->id, $user->mst_company_id);
            return $this->sendResponse($auth, 'アプリの権限取得に成功しました。');
        } catch (Exception $ex) {
            Log::error('GroupwareAppRoleAPIController@app_role:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
