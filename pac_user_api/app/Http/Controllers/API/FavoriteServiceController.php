<?php

namespace App\Http\Controllers\API;

use App\Models\FavoriteService;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateFavoriteServiceAPIRequest;
use App\Http\Requests\API\GetFavoriteServiceAPIRequest;
use App\Http\Requests\API\DeleteFavoriteServiceAPIRequest;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Session;

/**
 * Class FavoriteServiceController
 * @package App\Http\Controllers\API
 */

class FavoriteServiceController extends AppBaseController
{
    var $table = 'favorite_service';
    var $model = null;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->model = $favoriteService;
    }

    /**
     * Display a listing of the FavoriteService.
     * GET|HEAD /favorite
     *
     * @param GetFavoriteServiceAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index(GetFavoriteServiceAPIRequest $request)
    {
        $user = $request->user();
        $input = $request->all();
        try {
            $favorite = $this->model->where([
                    'mst_user_id' => $user->id,
                    'mypage_id' => $input['mypage_id']
                    ])->orderBy('id', 'asc')->get();
            return $this->sendResponse($favorite, 'お気に入りデータを取得するのが成功');
        } catch (Exception $ex){
            Log::error('FavoriteServiceController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created FavoriteService in storage.
     * POST /favorite
     *
     * @param CreateFavoriteServiceAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function store(CreateFavoriteServiceAPIRequest $request)
    {
        $input = $request->all();
        $user = $request->user();
        $input['mst_user_id'] = $user->id;
        $input['create_user'] = $user->email;
        $input['create_at'] = Carbon::now();
        $client = new Client(['http_errors' => false, 'verify' => false]);
        try {
            // Get image from url by GuzzleHttp\Client
            $response  = $client->request('GET', $input['logo_src']);

            if ($response->getStatusCode() != 200) {
                $input['logo_src'] = '';
//                Log::error("Get logo_src response body - FavoriteServiceController@store" . $response->getBody());
//                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                $img = (string)$response->getBody();
                // Convert image to base64
                $input['logo_src'] = base64_encode($img);
            }

            $this->model->insert($input);
            return $this->sendSuccess('お気に入りの追加処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('FavoriteServiceController@store:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified FavoriteService from storage.
     * DELETE /favorite/{id}
     *
     * @param DeleteFavoriteServiceAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function destroy(DeleteFavoriteServiceAPIRequest $request)
    {
        $input = $request->all();
        $user = $request->user();
        try {
            $favorite = DB::table('favorite_service')->where([
                'mst_user_id' => $user->id,
                'id' => $input['id']
            ])->first();
            $serviceName = '';
            $url = '';
            if (isset($favorite)) {
                $serviceName = $favorite->service_name;
                $url = $favorite->url;
            }
            DB::table('favorite_service')->where([
                'mst_user_id' => $user->id,
                'id' => $input['id']
            ])->delete();
            Session::flash('service_name', $serviceName);
            Session::flash('url', $url);
            return $this->sendSuccess('お気に入りの削除処理に成功しました。');
        } catch (Exception $ex){
            Log::error('FavoriteServiceController@destroy:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

