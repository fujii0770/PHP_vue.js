<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\IdAppApiUtils;
use App\Models\AdvertisementManagement;
use App\Http\Requests\API\GetAdvertiseManagementAPIRequest;
use Carbon\Carbon;use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class AdvertisementManagementAPIController
 * @package App\Http\Controllers\API
 */

class AdvertisementManagementAPIController extends AppBaseController
{
    var $table = 'advertisement_management';
    var $model = null;

    public function __construct(AdvertisementManagement $advertisementManagement)
    {
        $this->model = $advertisementManagement;
    }

    /**
     * Display a listing of the Advertisement.
     * GET|HEAD /advertisementmg
     *
     * @param GetAdvertiseManagementAPIRequest $request
     * @return Response
     */
    public function index(GetAdvertiseManagementAPIRequest $request)
    {
        $data = $request->all();
        try {
            if (!isset($data['mst_advertisement_id']) && !isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
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
             * - Get advertisement --- Problem: update_at is column take from other api of advertisement table
             * - Now use order by create_at then update_at db
             * - Comeback when have design API /advertisement
             */
            $advertisementQuery = $this->model
                ->select('id', 'mst_advertisement_id', 'create_at', 'create_user', 'update_at', 'update_user')
                ->getAdvertisement($data)->orderBy('update_at', 'desc')->orderBy('create_at', 'desc');
            $advertisements = $advertisementQuery->get();

            // Get list id of notices
            $adIds = [];
            foreach ($advertisements as $advertisement) {
                $adIds[$advertisement->id] = $advertisement->mst_advertisement_id;
            }
            
            if (count($adIds) == 0) {
                return $this->sendResponse($adIds, 'お知らせデータを取得のが成功になった。');
            }
            
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("advertisement",[
                RequestOptions::JSON => ['id' => "[".implode(',', array_values($adIds))."]", 'is_public' => 1]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $mstAdvertisements = json_decode((string) $response->getBody())->data;
                return $this->sendResponse($mstAdvertisements, '広告データを取得するのが成功になった');
            } else {
                Log::error('Get Advertisement response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            } 
        } catch (Exception $ex) {
            Log::error('AdvertisementManagementAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
