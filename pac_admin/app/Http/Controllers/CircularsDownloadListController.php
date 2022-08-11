<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Company;
use App\Models\Constraint;
use App\Models\Department;
use App\Models\DownloadRequest;
use App\Models\DownloadWaitData;
use App\Http\Utils\AppUtils;
use Carbon\Carbon;
use App\Http\Utils\DownloadUtils;
use Session;

class CircularsDownloadListController extends AdminController
{
    private $department;

    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
    }

    public function index(Request $request)
    {

        $user = $request->user();

        $action = $request->get('action', '');

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $orderBy = $request->get('orderBy', "R.contents_create_at");
        $orderDir = $request->get('orderDir', "DESC");

        //PAC_5-2874 S
        // 無害化するかを確認
        $company = Company::where('id', $user->mst_company_id)->first();
        $is_sanitizing = $company ? $company->sanitizing_flg : 0;
        $is_private = config('app.app_lgwan_flg');
        //PAC_5-2874 E

        DB::enableQueryLog();

        // Remove Download Request Row
        try {
            if ($request->isMethod('post') && $action) {
                if ($action == "delete") {
                    $this->delete($request->get('rid', ''), $user);
                }
            }

            $que = DB::table('download_request as R')
                ->select(DB::raw('R.id, R.user_auth, R.file_name, R.contents_create_at, R.download_period,
                 IF(R.download_period<=CURRENT_TIMESTAMP,'. DownloadUtils::EXPIRED_STATE.',state) AS state, R.sanitizing_state'))
                ->where('R.mst_user_id', $user->id)
                ->where('R.user_auth', AppUtils::AUTH_FLG_ADMIN)
                ->where('R.state', '!=', DownloadUtils::DELETE_STATE); // 9 : 削除

            $que = $que->orderBy($orderBy, $orderDir);

            if ($action) {
                $itemsCircular = $que->get();
                $this->assign('itemsCircular', $itemsCircular);
            }

            $itemsCircular = $que->paginate($limit)->appends(request()->except('_token'));
            //　PAC_5-2874 S
            foreach ($itemsCircular as $item){
                // 無害化無効　又は　LGWAN public環境
                if(!$is_sanitizing || !$is_private){
                    $item->sanitizing_state = DOwnloadUtils::SANITIZING_UNNEEDED;
                }
            }
            //PAC_5-2874 E
            $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
            $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
            $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
            $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

            $this->assign('limit', $limit);
            $this->assign('orderBy', $orderBy);
            $this->assign('orderDir', strtolower($orderDir) == "asc" ? "desc" : "asc");
            $this->assign('itemsCircular', $itemsCircular);

            $this->setMetaTitle('ダウンロード状況確認');
            return $this->render('Circulars.downloadlist');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.data_get')]]);
        }
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $rid = $request->get('rid', '');
        ini_set('memory_limit','2048M');
        try {
            $req_data = DownloadRequest::where('id', $rid)
                ->select('id', 'file_name')->first();
            $doc_data = DownloadWaitData::where('download_request_id', $rid)->first();

            if (!$doc_data) {
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.file_download',['attribute' => ''])]]);
            }

            $data = AppUtils::decrypt($doc_data->data);

            $constraints = Constraint::where('mst_company_id', $user->mst_company_id)
                ->select('dl_after_keep_days', 'dl_after_proc')
                ->first();

            // 0の場合はダウンロード実行日中
            if ($constraints->dl_after_keep_days === 0) {
                $dl_period = Carbon::today()->copy()->addDay(1)->subSecond();
            } else {
                $dl_period = Carbon::now()->copy()->addDay($constraints->dl_after_keep_days);
            }

            if ($constraints->dl_after_proc === 0) {
                $this->delete($rid, $user);
            } else {
                DownloadRequest::where('id', $rid)
                    ->where('state', DownloadUtils::REQUEST_DOWNLOAD_WAIT)
                    ->update([
                        'state' => DownloadUtils::REQUEST_DOWNLOAD_END,
                        'download_period' => $dl_period
                    ]);
            }
            Session::flash('file_name', $req_data->file_name);
            return response()->json(['status' => true, 'fileName' => $req_data->file_name,
                'file_data' => $data, 'message' => [__('message.success.download_request.file_download')]]);
        } catch (\Exception $e) {
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.file_download',['attribute' => $e->getMessage() . $e->getTrace()])]]);
        }
    }

    /**
     * ダウンロード済み文書削除処理
     *
     * @param $id
     * @param $user
     * @throws \Exception
     */
    public function delete($id, $user)
    {
        try {
            DownloadUtils::RemoveRequestData($id, $user);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            throw new \Exception($e);
        }
    }

    /**
     * ダウンロード再申請
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rerequest(Request $request)
    {
        try {
            $user = $request->user();
            $dl_request_id = $request->get('rid');
            
            DownloadUtils::reDownloadRequest($user, $dl_request_id);

            return response()->json(['status' => true, 'message' => [__('message.success.download_request.re_order')]]);
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.re_order', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 無害化状態を更新
     *
     * @param Request $request
     * @return mixed
     */
    public function sanitizingUpdate(Request $request)
    {
        try {
            $rid = $request->rid;
            $user = $request->user();

            //1：無害化要⇒2：無害化待ち
            DB::table('download_request')
                ->where('mst_user_id', $user->id)
                ->where('user_auth', AppUtils::AUTH_FLG_ADMIN)
                ->where('id', $rid)
                ->update([
                    'sanitizing_state' => DownloadUtils::SANITIZING_WAIT,
                ]);

            return response()->json(['status' => true, 'message' => [__('message.success.download_request.sanitizing_update')]]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.sanitizing_update'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]);
        }
    }
}
