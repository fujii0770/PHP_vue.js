<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\GetHrDailyReportListAPIRequest;
use App\Http\Requests\API\UpdateUserDailyReportAPIRequest;
use App\Http\Utils\AppUtils;
use App\Models\MstHrDailyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Session;

/**
 * Class UserDailyReportAPIController
 * @package App\Http\Controllers\API
 */

class UserDailyReportAPIController extends AppBaseController
{
    var $table = 'mst_hr_daily_report';
    var $model = null;

    public function __construct(MstHrDailyReport $mstHrDailyReport)
    {
        $this->model = $mstHrDailyReport;
    }

    /**
     * Display a MstHrDailyReport in storage for HR-Administrator.
     * GET|HEAD /user-daily-report/{daily-report_id}
     *
     * @throws Exception
     *
     * @return Response
     */
     public function show($id, Request $request)
     {
         $userId = $request->user()->id;
         try {
             $report = $this->model
             ->leftJoin('mst_user as U', 'U.id', '=', 'mst_user_id')
             ->select(DB::raw('mst_hr_daily_report.id, U.id as mst_user_id, CONCAT(U.family_name, U.given_name) as user_name, report_date, daily_report'))
             ->where('mst_hr_daily_report.id', $id)
             ->whereIn('mst_user_id', function ($q) use ($userId)
             {
                 $q->select('A.user_mst_user_id')
                     ->from('hr_admin_has_users as A')
                     ->where('A.del_flg',           '=', DB::raw(1))
                     ->where('A.admin_mst_user_id', '=', $userId);
             })
             ->first();
             return $this->sendResponse($report, '報告日報の取得処理に成功しました。');
         } catch (Exception $ex) {
             Log::error('UserDailyReportAPIController@show:' . $ex->getMessage().$ex->getTraceAsString());
             return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
         }
     }

    /**
     * Update a MstHrDailyReport in storage for HR-Administrator.
     * PUT /user-daily-report/{daily-report_id}
     *
     * @throws Exception
     *
     * @return Response
     */
    public function update ($id, UpdateUserDailyReportAPIRequest $request) {
        $user = $request->user();
        try {
            $data = [];
            $data['update_user'] = $user->email;
            $data['update_at'] = Carbon::now();
            $data['daily_report'] = $request->get('daily_report');
            $this->model->where('id', $id)
                ->whereIn('mst_user_id', function ($q) use ($user)
                {
                    $q->select('A.user_mst_user_id')
                        ->from('hr_admin_has_users as A')
                        ->where('A.admin_mst_user_id', '=', $user->id);
                })
                ->update($data);
            return $this->sendSuccess('日報を登録しました。');
        } catch (Exception $ex) {
            Log::error('UserDailyReportAPIController@update:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of the daily report for HR-Administrator.
     * GET|HEAD /user-daily-report
     *
     * @param GetHrDailyReportListAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function index(GetHrDailyReportListAPIRequest $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $query  =  null;

        // get list user
        $limitPage = isset($data['limit']) ? $data['limit'] : 12;
        $limit = AppUtils::normalizeLimit($limitPage, 12);
        $orderBy    = $request->get('orderBy') ? $request->get('orderBy') : 'report_date';
        $orderDir   = $request->get('orderDir') ? $request->get('orderDir'): 'DESC';
        $arrOrder   = ['user' => 'user_name','report_date' => 'report_date', 'daily_report' => 'daily_report'];

        $filter_user                = $request->get('user_name','');
        $filter_month               = $request->get('report_month','');

        try {
            $query = $this->model
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'report_date',$orderDir)
                ->leftJoin('mst_user as U', 'U.id', '=', 'mst_user_id')
                ->select(DB::raw('mst_hr_daily_report.id, U.id as mst_user_id, CONCAT(U.family_name, U.given_name) as user_name,U.email, report_date, daily_report, mst_hr_daily_report.update_at'))
                ->whereIn('mst_user_id', function ($q) use ($userId)
                {
                    $q->select('A.user_mst_user_id')
                        ->from('hr_admin_has_users as A')
                        ->where('A.del_flg',           '=', DB::raw(1))
                        ->where('A.admin_mst_user_id', '=', $userId);
                })
                ->where('U.mst_company_id', $user->mst_company_id)
            ;

            if($filter_month){
                $date = Carbon::createFromFormat('Ym', $filter_month);
                $query->whereMonth('report_date', $date->month);
                $query->whereYear('report_date', $date->year);
            }
            if($filter_user){
                $query->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%");
            }

            $workList = $query->paginate($limit)->appends(request()->input());
            return $this->sendResponse($workList, '日報一覧の取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('UserDailyReportAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

