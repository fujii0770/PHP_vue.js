<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\IdAppApiUtils;
use App\Models\NoticeManagement;
use App\Models\NoticeReadManagement;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use App\Http\Requests\API\GetNoticeManagementAPIRequest;
use App\Http\Requests\API\DeleteInsertNoticeManagementAPIRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;

/**
 * Class NoticeManagementAPIController
 * @package App\Http\Controllers\API
 */

class NoticeManagementAPIController extends AppBaseController
{
    var $table = 'notice_management';
    var $model = null;

    public function __construct(NoticeManagement $noticeManagement)
    {
        $this->model = $noticeManagement;
    }

    /**
     * Display a listing of the MstFavoriteService.
     * GET|HEAD /noticemg
     *
     * @param GetNoticeManagementAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index(GetNoticeManagementAPIRequest $request)
    {

        $data = $request->all();
        $limitPage = isset($data['limit'])?$data['limit']:10;
        $page = isset($data['page'])?$data['page']:1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        try {
            // parameter is current user login if don't have any parameter pass in request
            if (!isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
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

            $noticeQuery = null;
            /**
             * - Get notice --- Problem: create_at is column take from other api of mst_notice table
             *
             */
            $noticeQuery = $this->model->select('id', 'type', 'mst_notice_id', 'create_at')
                                            ->getNotice($data)->orderBy('create_at', 'DESC');

            $notices = $noticeQuery->get();

            // Get list id of notices
            $noticeIds = [];
            foreach ($notices as $notice) {
                $noticeIds[$notice->mst_notice_id] = $notice->id;
            }
            
            if (count($noticeIds) == 0) {
                return $this->sendResponse(['notices'=>new LengthAwarePaginator($noticeIds, 0, $limit, $page, []),'countNoticeUnread'=>0], 'お知らせデータを取得のが成功になった。');
            }

            // Get notice read
            $noticeRead = NoticeReadManagement::select('notice_management_id', 'is_read')->whereIn(
                'notice_management_id', array_values($noticeIds))->where('mst_user_id', $user->id)->get();

            $arrayNoticeRead = [];
            foreach ($noticeRead as $notice) {
                // Change attribute is_read
                if (is_null($notice->is_read) ) {
                    $notice->is_read = 0;
                }
                // Create array ['notice_management_id' => 'is_read'] to map data faster
                $arrayNoticeRead[$notice->notice_management_id] = $notice->is_read;
            }

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("notice",[
                RequestOptions::JSON => ['id' => "[".implode(',', array_keys($noticeIds))."]", 'page' => $page, 'limit' => $limit, 'valid_to' => Carbon::now()->format('Y/m/d')]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $responseData = json_decode((string) $response->getBody())->data;
                $mstNoticesResponse = $responseData->notices;
                $allNoticeIds = $responseData->allIds;
                $mstNotices = $mstNoticesResponse->data;
                foreach ($mstNotices as $notice) {
                    if (array_key_exists($notice->id, $noticeIds)) {
                        $notice->id = $noticeIds[$notice->id];
                    } else {
                        $notice->id = 0;
                    }
                    // Map List notice_management with notice_read_management                    
                    if (array_key_exists($notice->id, $arrayNoticeRead)) {
                        $notice->read_flg = $arrayNoticeRead[$notice->id];
                    } else {
                        $notice->read_flg = 0;
                    }
                }
                $mstNoticesResponse->data = $mstNotices;
                $countNoticeRead = $this->model->getNotice($data)
                    ->join('notice_read_management', 'notice_management.id', '=', 'notice_read_management.notice_management_id')
                    ->whereIn('mst_notice_id', $allNoticeIds)
                    ->where('notice_read_management.is_read', 1)
                    ->where('mst_user_id', $user->id)
                    ->select('notice_management.mst_notice_id')
                    ->distinct('notice_management.mst_notice_id');
                    
                $countNoticeRead = $countNoticeRead->count();
                return $this->sendResponse(['notices'=>$mstNoticesResponse,'countNoticeUnread'=>(count($allNoticeIds) - $countNoticeRead)], 'お知らせデータを取得のが成功になった。');
            } else {
                Log::debug('Get Notice response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }            
        } catch (Exception $ex) {
            Log::error('NoticeManagementAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 未読のお知らせ件数を取得する
     * GET|HEAD /noticeunread
     *
     * @param GetNoticeManagementAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function unread(GetNoticeManagementAPIRequest $request)
    {

        $data = $request->all();
        $limitPage = isset($data['limit'])?$data['limit']:10;
        $page = isset($data['page'])?$data['page']:1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        try {
            // parameter is current user login if don't have any parameter pass in request
            if (!isset($data['mst_company_id']) && !isset($data['mst_department_id']) && !isset($data['mst_position_id'])) {
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

            $noticeQuery = null;
            /**
             * - Get notice --- Problem: create_at is column take from other api of mst_notice table
             *
             */
            $noticeQuery = $this->model->select('id', 'type', 'mst_notice_id', 'create_at')
                                            ->getNotice($data)->orderBy('create_at', 'DESC');

            $notices = $noticeQuery->get();

            // Get list id of notices
            $noticeIds = [];
            foreach ($notices as $notice) {
                $noticeIds[$notice->mst_notice_id] = $notice->id;
            }
            
            if (count($noticeIds) == 0) {
                return $this->sendResponse(0,"");
            }

            // Get notice read
            $noticeRead = NoticeReadManagement::select('notice_management_id', 'is_read')->whereIn(
                'notice_management_id', array_values($noticeIds))->where('mst_user_id', $user->id)->get();

            $arrayNoticeRead = [];
            foreach ($noticeRead as $notice) {
                // Change attribute is_read
                if (is_null($notice->is_read) ) {
                    $notice->is_read = 0;
                }
                // Create array ['notice_management_id' => 'is_read'] to map data faster
                $arrayNoticeRead[$notice->notice_management_id] = $notice->is_read;
            }

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $response = $client->post("notice",[
                RequestOptions::JSON => ['id' => "[".implode(',', array_keys($noticeIds))."]", 'page' => $page, 'limit' => $limit, 'valid_to' => Carbon::now()->format('Y/m/d')]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == 200) {
                $responseData = json_decode((string) $response->getBody())->data;
                $mstNoticesResponse = $responseData->notices;
                $allNoticeIds = $responseData->allIds;
                $mstNotices = $mstNoticesResponse->data;
                foreach ($mstNotices as $notice) {
                    if (array_key_exists($notice->id, $noticeIds)) {
                        $notice->id = $noticeIds[$notice->id];
                    } else {
                        $notice->id = 0;
                    }
                    // Map List notice_management with notice_read_management                    
                    if (array_key_exists($notice->id, $arrayNoticeRead)) {
                        $notice->read_flg = $arrayNoticeRead[$notice->id];
                    } else {
                        $notice->read_flg = 0;
                    }
                }
                $mstNoticesResponse->data = $mstNotices;
                $countNoticeRead = $this->model->getNotice($data)
                    ->join('notice_read_management', 'notice_management.id', '=', 'notice_read_management.notice_management_id')
                    ->whereIn('mst_notice_id', $allNoticeIds)
                    ->where('notice_read_management.is_read', 1)
                    ->where('mst_user_id', $user->id)
                    ->select('notice_management.mst_notice_id')
                    ->distinct('notice_management.mst_notice_id');
                    
                $countNoticeRead = $countNoticeRead->count();
                return $this->sendResponse(count($allNoticeIds) - $countNoticeRead,"");
            } else {
                Log::debug('Get Notice response body ' . $response->getBody());
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $ex) {
            Log::error('NoticeManagementAPIController@unread:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
