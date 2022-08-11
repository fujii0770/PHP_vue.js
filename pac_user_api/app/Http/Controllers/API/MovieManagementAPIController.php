<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Models\MovieManagement;
use App\Http\Requests\API\GetMovieManagementAPIRequest;
use Carbon\Carbon;use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class MovieManagementAPIController
 * @package App\Http\Controllers\API
 */

class MovieManagementAPIController extends AppBaseController
{
    var $table = 'movie_management';
    var $model = null;

    public function __construct(MovieManagement $movieManagement)
    {
        $this->model = $movieManagement;
    }

    /**
     * Display a listing of the Movie.
     * GET|HEAD /moviemg
     *
     * @param GetMovieManagementAPIRequest $request
     * @return Response
     */
    public function index(GetMovieManagementAPIRequest $request)
    {
        $data = $request->all();
        $limitPage = isset($data['limit'])?$data['limit']:10;
        $page = isset($data['page'])?$data['page']:1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        $theme_id = $data['theme_id'] ?? 0;
        try {
            if (!isset($data['mst_movie_id']) && !isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
                $user = $request->user();
                $data['mst_company_id'] = $user->mst_company_id;
                $userInfo = DB::table('mst_user_info')->select('mst_department_id', 'mst_position_id')->where('mst_user_id', $user->id)->first();
                if (!isset($userInfo)) {
                    return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                // Get department_id and position_id if exit
                if (isset($userInfo->mst_department_id)) {
                    $data['mst_department_id'] = $userInfo->mst_department_id;
                }

                if (isset($userInfo->mst_position_id)) {
                    $data['mst_position_id'] = $userInfo->mst_position_id;
                }

            }
            /**
             * - Get movie --- Problem: update_at is column take from other api of movie table
             * - Now use order by create_at then update_at db
             * - Comeback when have design API /movie
             */
            $movieQuery = $this->model
                ->select('id', 'mst_movie_id', 'mst_department_id', 'mst_position_id', 'create_at', 'create_user', 'update_at', 'update_user', 'location_type')
                ->getMovie($data)->orderBy('update_at', 'desc')->orderBy('create_at', 'desc');
            if (isset($data['location_type'])) {
                if (!is_array($data['location_type'])) $data['location_type'] = explode(',', $data['location_type']);
            }
            $movies = $movieQuery->get();

            // Get list id of notices
            $adIds = [];
            $movie_list = [];
            foreach ($movies as $movie) {
                $adIds[$movie->id] = $movie->mst_movie_id;
                $movie_list[$movie->mst_movie_id] = $movie;
            }

            if (count($adIds) == 0) {
                return $this->sendResponse(new LengthAwarePaginator($adIds, 0, $limit, $page, []), '動画データを取得のが成功になった。');
            }
            
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("movie",[
                RequestOptions::JSON => ['id' => "[".implode(',', array_values($adIds))."]", 'page' => $page, 'limit' => $limit, 'is_public' => 1, 'theme_id' => $theme_id]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $mstMovies = json_decode((string) $response->getBody())->data;
                if ($mstMovies && isset($mstMovies->data) && is_array($mstMovies->data) && isset($data['location_type'])) {
                    if (is_array($data['location_type'])) $data['location_type'] = implode(',', $data['location_type']);
                    if ($data['location_type'] == '1,2') {
                        $mstMovies->data = $this->filterLocationMovie($mstMovies->data, $movie_list);
                    }
                }
                return $this->sendResponse($mstMovies, '動画データを取得するのが成功になった。');
            } else {
                Log::error('Get Movie response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            } 
        } catch (Exception $ex) {
            Log::error('MovieManagementAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getTopList(GetMovieManagementAPIRequest $request)
    {
        $data = $request->all();
        try {
            $show_num = $data['show_num'] ?? 5;
            $theme_id = $data['theme_id'] ?? 0;
            if (!isset($data['mst_movie_id']) && !isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
                $user = $request->user();
                $data['mst_company_id'] = $user->mst_company_id;
                $userInfo = DB::table('mst_user_info')->select('mst_department_id', 'mst_position_id')->where('mst_user_id', $user->id)->first();
                if (!isset($userInfo)) {
                    return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            
                // Get department_id and position_id if exit
                if (isset($userInfo->mst_department_id)) {
                    $data['mst_department_id'] = $userInfo->mst_department_id;
                }
                if (isset($userInfo->mst_position_id)) {
                    $data['mst_position_id'] = $userInfo->mst_position_id;
                }
            
            }
            /**
             * - Get movie --- Problem: update_at is column take from other api of movie table
             * - Now use order by create_at then update_at db
             * - Comeback when have design API /movie
             */
            $movieQuery = $this->model
                ->select('id', 'mst_movie_id', 'create_at', 'create_user', 'update_at', 'update_user')
                ->getMovie($data)->orderBy('update_at', 'desc')->orderBy('create_at', 'desc');
            $movies = $movieQuery->get();

            // Get list id of notices
            $adIds = [];
            foreach ($movies as $movie) {
                $adIds[$movie->id] = $movie->mst_movie_id;
            }
        
            if (count($adIds) == 0) {
                return $this->sendResponse([], '動画データを取得のが成功になった。');
            }

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("movietop",[
                RequestOptions::JSON => ['id' => "[".implode(',', array_values($adIds))."]", 'is_public' => 1, 'show_num' => $show_num, 'theme_id' => $theme_id]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $mstMovies = json_decode((string) $response->getBody())->data;
                return $this->sendResponse($mstMovies, '動画データを取得するのが成功になった。');
            } else {
                Log::error('Get Movie response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $ex) {
            Log::error('MovieManagementAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    
    }

    /**
     * Display a listing of the Movie Theme.
     * GET|HEAD /movietheme
     *
     * @param GetMovieManagementAPIRequest $request
     * @return Response
     */
    public function getThemeList(GetMovieManagementAPIRequest $request)
    {
        $data = $request->all();
        try {
            if (!isset($data['mst_movie_id']) && !isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
                $user = $request->user();
                $data['mst_company_id'] = $user->mst_company_id;
                $userInfo = DB::table('mst_user_info')->select('mst_department_id', 'mst_position_id')->where('mst_user_id', $user->id)->first();
                if (!isset($userInfo)) {
                    return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                
                if (!empty($userInfo->mst_department_id)) {
                    $data['mst_department_id'] = $userInfo->mst_department_id;
                }
                if (isset($userInfo->mst_position_id)) {
                    $data['mst_position_id'] = $userInfo->mst_position_id;
                }
                
            }

            $movieQuery = $this->model
                ->select('id', 'mst_movie_id', 'create_at', 'create_user', 'update_at', 'update_user')
                ->getMovie($data)->orderBy('update_at', 'desc')->orderBy('create_at', 'desc');
            $movies = $movieQuery->get();

            $adIds = [];
            foreach ($movies as $movie) {
                $adIds[$movie->id] = $movie->mst_movie_id;
            }

            if (count($adIds) == 0) {
                return $this->sendResponse([], 'ビデオテーマの取得に成功。');
            }
            
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("movietheme",[
                RequestOptions::JSON => ['id' => "[".implode(',', array_values($adIds))."]", 'is_public' => 1]
            ]);
            
            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $mstMovies = json_decode((string) $response->getBody())->data;
                return $this->sendResponse($mstMovies, 'ビデオテーマの取得に成功。');
            } else {
                Log::error('Get Movie response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $ex) {
            Log::error('MovieManagementAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }
    
    /**
     * Add video play count
     * GET|HEAD /movietheme
     *
     * @param GetMovieManagementAPIRequest $request
     * @return Response
     */
    public function addPlayCount(GetMovieManagementAPIRequest $request)
    {
        $data = $request->all();
        try {
            if (!isset($data['mst_movie_id'])) {
                return $this->sendError('動画が存在しません。', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
                $user = $request->user();
                $data['mst_company_id'] = $user->mst_company_id;
                $userInfo = DB::table('mst_user_info')->select('mst_department_id', 'mst_position_id')->where('mst_user_id', $user->id)->first();
                if (!isset($userInfo)) {
                    return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                
                if (!empty($userInfo->mst_department_id)) {
                    $data['mst_department_id'] = $userInfo->mst_department_id;
                }
                if (isset($userInfo->mst_position_id)) {
                    $data['mst_position_id'] = $userInfo->mst_position_id;
                }
            }

            $movie = $this->model
                ->select('mst_movie_id')
                ->getMovie($data)->first();

            $movieId = 0;
            if ($movie) {
                $movieId = $movie['mst_movie_id'];
            } else {
                return $this->sendError('動画が存在しません。', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("movieaddplays",[
                RequestOptions::JSON => ['movie_id' => $movieId, 'is_public' => 1]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                return $this->sendResponse([], '動画の再生回数が正常に追加されました。');
            } else {
                Log::error('Get Movie response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $ex) {
            Log::error('MovieManagementAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    private function filterLocationMovie($data, $movie_list)
    {
        $movie_arr = [];
        $management_item_0 = null;
        $management_item_1 = null;

        foreach ($data as $item) {
            $movie_item = clone $movie_list[$item->id];
            if ($movie_list[$item->id]->location_type == 1) {
                if (!isset($movie_arr[0])) {
                    $movie_arr[0] = clone $item;
                    $management_item_0 = clone $movie_list[$movie_arr[0]->id];
                } else {
                    if ((is_null($management_item_0->mst_department_id) && is_null($management_item_0->mst_position_id) && (!is_null($movie_item->mst_department_id) || !is_null($movie_item->mst_position_id) || $movie_item->id > $management_item_0->id))
                        || ((!is_null($management_item_0->mst_department_id) || !is_null($management_item_0->mst_position_id)) && (!is_null($movie_item->mst_department_id) || !is_null($movie_item->mst_position_id)) && $movie_item->id > $management_item_0->id)) {
                        $movie_arr[0] = clone $item;
                        $management_item_0 = clone $movie_list[$movie_arr[0]->id];
                    }
                }
            }
            if ($movie_list[$item->id]->location_type == 2) {
                if (!isset($movie_arr[1])) {
                    $movie_arr[1] = clone $item;
                    $management_item_1 = clone $movie_list[$movie_arr[1]->id];
                } else {
                    if ((is_null($management_item_1->mst_department_id) && is_null($management_item_1->mst_position_id) && (!is_null($movie_item->mst_department_id) || !is_null($movie_item->mst_position_id) || $movie_item->id > $management_item_1->id))
                        || ((!is_null($management_item_1->mst_department_id) || !is_null($management_item_1->mst_position_id)) && (!is_null($movie_item->mst_department_id) || !is_null($movie_item->mst_position_id)) && $movie_item->id > $management_item_1->id)) {
                        $movie_arr[1] = clone $item;
                        $management_item_1 = clone $movie_list[$movie_arr[1]->id];
                    }
                }
            }
        }

        return $movie_arr;
    }
}
