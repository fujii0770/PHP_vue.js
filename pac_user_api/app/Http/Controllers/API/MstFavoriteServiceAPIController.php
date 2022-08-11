<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\MstFavoriteService;
use App\Http\Requests\API\CreateMstFavoriteServiceAPIRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

/**
 * Class FavoriteServiceController
 * @package App\Http\Controllers\API
 */

class MstFavoriteServiceAPIController extends AppBaseController
{
    var $table = 'mst_favorite_service';
    var $model = null;

    public function __construct(MstFavoriteService $mstFavoriteService)
    {
        $this->model = $mstFavoriteService;
    }

    /**
     * Display a listing of the MstFavoriteService.
     * GET|HEAD /internalsv
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            $mstFavoriteService = $this->model->get();
            return $this->sendResponse($mstFavoriteService, 'Mst favorite service successfully');
        } catch (Exception $ex){
            Log::error('MstFavoriteServiceAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
