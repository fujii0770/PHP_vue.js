<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Models\CustomizeManagement;
use App\Http\Requests\API\GetCustomizeManagementAPIRequest;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class CustomizeManagementAPIController
 * @package App\Http\Controllers\API
 */
class CustomizeManagementAPIController extends AppBaseController
{
    var $table = 'customize_management';
    var $model = null;

    public function __construct(CustomizeManagement $customizeManagement)
    {
        $this->model = $customizeManagement;
    }

    /**
     * Display a listing of the Customize.
     * GET|HEAD /customizemg
     *
     * @param GetCustomizeManagementAPIRequest $request
     * @return Response
     */
    public function index(GetCustomizeManagementAPIRequest $request)
    {
        $data = $request->all();
        $limitPage = isset($data['limit']) ? $data['limit'] : 10;
        $page = isset($data['page']) ? $data['page'] : 1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        $location_type = $data['location_type'] ?? '';
        try {
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
            /**
             * - Get customize --- Problem: update_at is column take from other api of customize table
             * - Now use order by create_at then update_at db
             * - Comeback when have design API /customize
             */
            $customizeQuery = $this->model->select('id', 'mst_customize_id', 'location_type', 'type')
                ->getCustomize($data)->orderBy('update_at', 'desc')->orderBy('create_at', 'desc');
            $customizes = $customizeQuery->get();

            $customizeIds = [];
            $customize_list = [];
            $customize_company = [];
            $customize_department = [];
            $customize_position = [];
            foreach ($customizes as $customize) {
                $customizeIds[$customize->id] = $customize->mst_customize_id;
                $customize_list[$customize->mst_customize_id] = $customize;
            }

            if (count($customizeIds) == 0) {
                return $this->sendResponse(new LengthAwarePaginator($customizeIds, 0, $limit, $page, []), 'カスタムエリアデータを正常に取得する。');
            }
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("customize", [
                RequestOptions::JSON => ['id' => "[" . implode(',', array_values($customizeIds)) . "]",
                    'location_type' => $location_type,
                    'page' => $page,
                    'limit' => $limit,
                ]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $mstCustomizes = json_decode((string)$response->getBody())->data;
                $customize = (object)[];
                if ($mstCustomizes && $mstCustomizes->customizes && $mstCustomizes->customizes->data) {
                    $ancillary_info = $mstCustomizes->customizes_ancillary ?? [];
                    $customize = $mstCustomizes->customizes;
                    $customize->area_title = '';
                    $customize->type = 0;
                    if (!empty($customizes) && count($customizes) > 0) {
                        if (isset($ancillary_info[0]) && isset($ancillary_info[0]->area_title)){
                            $customize->area_title = $ancillary_info[0]->area_title;
                        }
                        $customize->type = $customizes[0]->type;
                    }
                }
                return $this->sendResponse($customize, 'カスタムエリアデータを正常に取得する');
            } else {
                Log::error('Get Customize response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $ex) {
            Log::error('CustomizeManagementAPIController@index:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
