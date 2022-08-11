<?php

namespace App\Http\Controllers\API;

use App\Models\MstHrInfo;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;

/**
 * Class FavoriteAPIController
 * @package App\Http\Controllers\API
 */

class MstHrInfoAPIController extends AppBaseController
{
    var $table = 'mst_hr_info';
    var $model = null;

    public function __construct(MstHrInfo $mstHrInfo)
    {
        $this->model = $mstHrInfo;
    }

    /**
     * Display a listing of the MstHrInfo.
     * GET|HEAD /hr-info
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        try {
            $hrInfo = $this->model->where('mst_user_id', $user->id)->orderBy('create_at', 'DESC')->first();
            return $this->sendResponse($hrInfo, 'HR情報データの取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('MstHrInfoAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
