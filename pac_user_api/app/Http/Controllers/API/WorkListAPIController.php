<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use App\Models\HrTimeCard;
use App\Http\Requests\API\GetHrWokListAPIRequest;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Response;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\MailUtils;

/**
 * Class WorkListAPIController
 * @package App\Http\Controllers\API
 */

class WorkListAPIController extends AppBaseController
{

    var $table = 'hr_timecard';
    var $model = null;

    public function __construct(HrTimeCard $hrTimeCard)
    {
        $this->model = $hrTimeCard;
    }

    /**
     * Display a listing of the HrTimeCard.
     * GET|HEAD /work-list
     *
     * @param GetHrWokListAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */

    public function index(GetHrWokListAPIRequest $request)
    {
        $data = $request->all();
        $limitPage = isset($data['limit']) ? $data['limit'] : 12;
        $limit = AppUtils::normalizeLimit($limitPage, 12);
        $userId = $request->user()->id;
        try {
            $orderBy = $data['orderBy'] ? $data['orderBy'] : 'working_month';
            $orderDir = $data['orderDir'] ? $data['orderDir'] : 'DESC';
            $workListQuery = $this->model->select('working_month', 'submission_state', 'submission_date', 'approval_state', 'approval_user', 'approval_date')
                                        ->getHrWorkList($data)->where('mst_user_id', $userId)
                                        ->orderBy($orderBy, $orderDir);
            $workList = $workListQuery->paginate($limit)->appends(request()->input());
            return $this->sendResponse($workList, '勤務一覧の取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('WorkListAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function updateSubmissionState(Request $request, $working_month){
        $user = $request->user();
        try {
            DB::beginTransaction();
            $data['submission_state'] = 1;
            $data['submission_date'] = Carbon::now();
            $data['update_user'] = $user->email;
            $data['update_at'] = Carbon::now();

            $this->model->where([
                'mst_user_id' => $user->id,
                'working_month' => $working_month
            ])->update($data);
            // start send mail
            $adminInfo = DB::table('hr_admin_has_users as A')
                ->leftJoin('mst_user as U', 'U.id',  'A.admin_mst_user_id')
                ->leftJoin("hr_timecard as T", "A.user_mst_user_id", "T.mst_user_id")
                ->where('A.user_mst_user_id', $user->id)
                ->where('T.working_month', $working_month)
                ->first();

            if ($adminInfo) {
                $data_mail = [
                    'user_name' => $user->family_name . ' ' . $user->given_name,
                    'admin_name' => $adminInfo->family_name . ' ' . $adminInfo->given_name,
                    'working_month' =>  substr($working_month, 0, -2) . '/' .substr($working_month, -2),
                ];

                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $adminInfo->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['SEND_WORK_DETAIL_SUBMISSION_MAIL']['CODE'],
                    // パラメータ
                    json_encode($data_mail,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    trans('mail.prefix.user') . trans('mail.SendWorkDetailSubmissionMail.subject'),
                    // メールボディ
                    trans('mail.SendWorkDetailSubmissionMail.body', $data_mail)
                );
            }
            DB::commit();

            return $this->sendSuccess( 'Submit Success');
        } catch (Exception $ex) {
            DB::rollback();
            Log::error('WorkListAPIController@updateSubmissionState:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
