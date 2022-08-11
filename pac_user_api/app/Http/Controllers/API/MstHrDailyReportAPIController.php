<?php

namespace App\Http\Controllers\API;

use App\Models\MstHrDailyReport;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMstDailyReportAPIRequest;
use App\Http\Requests\API\GetDailyReportAPIRequest;
use App\Http\Requests\API\UpdateMstHrDailyReportAPIRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Session;

/**
 * Class MstHrDailyReportAPIController
 * @package App\Http\Controllers\API
 */

class MstHrDailyReportAPIController extends AppBaseController
{
    var $table = 'mst_hr_daily_report';
    var $model = null;

    public function __construct(MstHrDailyReport $mstHrDailyReport)
    {
        $this->model = $mstHrDailyReport;
    }

    /**
     * Display a MstHrDailyReport in storage.
     * GET|HEAD /daily-report
     *
     * @param GetDailyReportAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
     public function index(GetDailyReportAPIRequest $request)
     {
         $data = $request->all();
         $userId = $request->user()->id;
         try {
             $report = $this->model->where([
                 'mst_user_id' => $userId,
                 'report_date' => $data['report_date']
             ])->first();
             if (isset($data['searchReportDate']) && $data['searchReportDate'] == 'true') {
                 Session::put('search', true);
             }
             return $this->sendResponse($report, '報告日報の取得処理に成功しました。');
         } catch (Exception $ex) {
             Log::error('MstHrDailyReportAPIController@index:' . $ex->getMessage().$ex->getTraceAsString());
             return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
         }
     }

    /**
     * Store a newly created MstHrDailyReport in storage.
     * POST /daily-report
     *
     * @param CreateMstDailyReportAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function store(CreateMstDailyReportAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        try {
            $data['mst_user_id'] = $user->id;
            $data['create_at'] = Carbon::now();
            $data['create_user'] = $user->email;
            $this->model->insert($data);
            return $this->sendSuccess('日報を登録しました。');
        } catch (Exception $ex) {
            Log::error('MstHrDailyReportAPIController@store:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a MstHrDailyReport in storage.
     * PUT /daily-report/{daily-report_id}
     *
     * @param UpdateMstHrDailyReportAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function update (UpdateMstHrDailyReportAPIRequest $request) {
        $data = $request->all();
        $user = $request->user();
        try {
            $data['update_user'] = $user->email;
            $data['update_at'] = Carbon::now();
            $this->model->where([
                'id' => $data['id'],
                'mst_user_id' => $user->id
            ])->update($data);
            return $this->sendSuccess('日報を登録しました。');
        } catch (Exception $ex) {
            Log::error('MstHrDailyReportAPIController@update:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

