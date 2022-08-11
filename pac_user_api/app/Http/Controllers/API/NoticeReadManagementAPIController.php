<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\NoticeReadManagement;
use Carbon\Carbon;
use Illuminate\Http\Response;
use App\Http\Requests\API\CreateNoticeReadManagementAPIRequest;
use Illuminate\Support\Facades\Log;

/**
 * Class NoticeManagementAPIController
 * @package App\Http\Controllers\API
 */

class NoticeReadManagementAPIController extends AppBaseController
{
    var $table = 'notice_read_management';
    var $model = null;

    public function __construct(NoticeReadManagement $noticeReadManagement)
    {
        $this->model = $noticeReadManagement;
    }

    /**
     * Store a newly Notification in storage.
     * POST /noticeread
     *
     * @param CreateNoticeReadManagementAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function store(CreateNoticeReadManagementAPIRequest $request)
    {
        $input = $request->all();
        $user = $request->user();
        try {
            $noticeNumber = $this->model->select('mst_user_id', 'notice_management_id', 'is_read')->where([
                'mst_user_id' => $user->id,
                'notice_management_id' => $input['mst_notice_management_id']
            ])->count();
            // Insert or update notice_read_management
            if ($noticeNumber == 0) {
                $data['mst_user_id'] = $user->id;
                $data['create_user'] = $user->email;
                $data['create_at'] = Carbon::now();
                $data['notice_management_id'] = $input['mst_notice_management_id'];
                $data['is_read'] = $input['read_flg'];
                $this->model->insert($data);
            } else {
                $this->model->where([
                    'mst_user_id' => $user->id,
                    'notice_management_id' => $input['mst_notice_management_id']
                ])->update([
                    'is_read' => $input['read_flg'],
                    'update_user' => $user->email
                ]);
            }
            return $this->sendSuccess('既読状況を更新するのが成功されました。');

        } catch (Exception $ex){
            Log::error('NoticeReadManagementAPIController@store:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
