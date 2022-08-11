<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MstMyPageLayout;
use Illuminate\Support\Facades\Log;

/**
 * Class MstMyPageLayoutAPIController
 * @package App\Http\Controllers\API
 */

class MstMyPageLayoutAPIController extends AppBaseController
{
    var $table = 'mst_mypage_layout';
    var $model = null;

    public function __construct(MstMyPageLayout $mstMyPageLayout)
    {
        $this->model = $mstMyPageLayout;
    }

    /**
     * Display a listing of the MstMyPageLayout.
     * GET|HEAD /mstmypage
     *
     * @return Response
     */
    public function index ()
    {
        try {
            $mstMyPageLayout = $this->model->select('id', 'layout_name', 'layout_src', 'layout')
                                    ->orderBy('id', 'asc')->get();
            return $this->sendResponse($mstMyPageLayout, 'マイページレイアウトデータを取得するのが成功になった。');
        } catch (Exception $ex){
            Log::error('MstMyPageLayoutAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
