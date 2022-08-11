<?php

namespace App\Http\Controllers;

use App\Http\Utils\PermissionUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Department;
use App\Http\Utils\AppUtils;
use Carbon\Carbon;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DownloadUtils;


class TemplateAdminController extends AdminController
{

    private $model;
    private $department;
    
    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_TEMPLATE_CSV_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $kind           = $request->get('kind');
        $documentName       = CircularDocumentUtils::charactersReplace($request->get('documentName'));
        $status         = $request->get('status', false);
        $page           = $request->get('page', 1);
        $limit          = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy        = $request->get('orderBy', "update_at");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword        = CircularDocumentUtils::charactersReplace($request->get('keyword'));
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $system_server_flg = config('app.server_flg');

        $data = null;

        // 回覧完了日時
        $finishedDateKey = $request->get('finishedDate');

        // 当月
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }
        Log::info('$finishedDate' . var_export($finishedDateKey, true));
        Log::info('$documentName' . var_export($documentName, true));
        
        //長期保存対応
        if($finishedDateKey === '12'){
            try {
                $mst_user = DB::table('mst_user')
                    ->where('id',$user->id)
                    ->get();
                $arrOrder   = ['circular_kind' => 'circular_kind','file_names' => 'd_file_names', 'sender' => 'sender',
                        'emails' => 'emails', 'update_at' => 'update_at'];
                $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';
                $data = DB::table("long_term_document as D")
                        ->join('template_input_data as T', 'D.circular_id', '=', 'T.circular_id')
                        ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                            $query->on('D.circular_id', 'auto_his.circular_id')
                                ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
                        })
                        ->leftjoin('template_csv_permit_user as X', 'D.circular_id', '=' ,'X.circular_id')
                        ->selectRaw(
                            'D.id, 
                            D.completed_at as update_at, 
                            D.title as file_names, 
                            CONCAT(D.sender_name,\'<br>\', \' 【\',D.sender_email, \'】\') as sender,
                            CONCAT(D.destination_name,\'<br>\', \' 【\',D.destination_email, \'】\') as emails, 
                            D.sender_name as sender_name, 
                            auto_his.result'
                        )
                        ->whereRaw("D.mst_company_id=$user->mst_company_id and ((D.sender_email='$user->email') or (X.csv_permit_user='$user->email'))");

                        if($documentName){
                            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?))';
                            $where_arg[]    = "%$documentName%";
                            $where_arg[]    = "%$documentName%";
                            $data = $data->whereRaw(implode(" AND ", $where), $where_arg);
                        }

                        if($documentName){
                            $data = $data->where('D.title', 'like', '%' . $documentName . '%');
                        } 
                        
                        //U.receiver_title as d_file_names,
                        //C.circular_status, 
                        // C.access_code, 
                        //     C.outside_access_code, 
                        //     CASE WHEN C.mst_user_id = ' . $user->id  .' and C.edition_flg = '.config('app.edition_flg') . ' and C.env_flg = ' . config('app.server_env') . ' and C.server_flg = ' . config('app.server_flg'). ' THEN 1 ELSE 0 END AS circular_kind, 
                        //     CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender_env,

                    // 当月又は上月コピーなし条件追加
                    if (0 == $finishedDateKey || null == $finishedDateKey) {
                        $data->whereRaw("DATE_FORMAT( C.completed_date, '%Y%m' ) = ".date('Ym'));
                    }

                    $data = $data->groupByRaw('D.id, D.sender_name, D.sender_email, D.title, D.destination_name, D.destination_email, auto_his.result,D.completed_at')
                        ->orderBy($orderBy, $orderDir)
                        ->paginate($limit)
                        ->appends(request()->input());

                    foreach($data as $item) {
                        $item-> access_code = "";
                    }

                    $this->assign('itemsTemplate', $data);
                    $this->assign('orderBy', $orderBy);
                    $this->assign('orderDir', $orderDir);

                    $this->setMetaTitle('回覧完了テンプレート一覧');
                    return $this->render('Circulars.template_csv');

            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
            }
        }else{
            try {
                $arrOrder   = ['circular_kind' => 'circular_kind','file_names' => 'd_file_names', 'sender' => 'sender',
                    'emails' => 'emails', 'update_at' => 'update_at'];
                $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';

                $where = [];
                $where_arg = [];

            
                $data = DB::table("circular$finishedDate as C")
                    ->join("circular_user$finishedDate as U", function ($join) use ($user) {
                        $join->on('C.id', 'U.circular_id')
                            ->on('U.mst_company_id', DB::raw($user->mst_company_id));
                    })
                    ->join('template_input_data as T', 'C.id', '=', 'T.circular_id')
                    ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                        $query->on('C.id', 'auto_his.circular_id')
                            ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
                    })
                    ->selectRaw(
                        'C.id, 
                        C.access_code, 
                        C.outside_access_code, 
                        CASE WHEN C.mst_user_id = ' . $user->id  .' and C.edition_flg = '.env('PAC_APP_ENV') . ' and C.server_flg = ' . 0 .  ' and C.env_flg = ' . env('PAC_CONTRACT_APP') . ' THEN 1 ELSE 0 END AS circular_kind, 
                        CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender_env,
                        C.completed_date as update_at, 
                        C.circular_status, 
                        U.title as file_names, 
                        U.receiver_title as d_file_names,
                        CONCAT(U.sender_name, \' 【\',U.sender_email, \'】\') as sender,
                        REPLACE(REPLACE(U.receiver_name_email,\'&lt;\',\'【\'),\'&gt;\',\'】\') as emails,
                        U.sender_name as sender_name, 
                        auto_his.result'
                    )->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);

                    if($documentName){
                        $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?))';
                        $where_arg[]    = "%$documentName%";
                        $where_arg[]    = "%$documentName%";
                    $data = $data->whereRaw(implode(" AND ", $where), $where_arg);
                    }
                        

                // 当月又は上月コピーなし条件追加
                if (0 == $finishedDateKey || null == $finishedDateKey) {
                    $data->whereRaw("DATE_FORMAT( C.completed_date, '%Y%m' ) = ".date('Ym'));
                }

                $data = $data->groupByRaw('C.id, U.sender_name, U.sender_email, U.title, U.receiver_title, U.receiver_name_email, result')
                    ->orderBy($orderBy, $orderDir)
                    ->paginate($limit)
                    ->appends(request()->input());
                
                // 件名設定
                foreach ($data as $item) {
                    if (!$item->file_names || trim($item->file_names,' ') == '') {
                        $fileNames = $item->d_file_names != '' ?  explode(', ', $item->d_file_names) : explode(', ', $item->vd_file_names);
                        $item->file_names = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames),0,100) : reset($fileNames);
                    }
                }
            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
            }
        }
        $this->assign('itemsTemplate', $data);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);


        $this->setMetaTitle('回覧完了テンプレート一覧');
        return $this->render('Circulars.template_csv');

    }

    /**
     * ダウンロード予約処理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function download(Request $request)
    {
        try{
            $user = $request->user();
            if(!$user->can(PermissionUtils::PERMISSION_TEMPLATE_CSV_VIEW)){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            $input_file_name = $request->get('fileName');

            if($input_file_name == NULL){
                $input_file_name = 'template.csv';
            }else{
                $input_file_name = $input_file_name.'.csv';
            }

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\TemplateAdminControllerUtils', 'getCircularCompleteTemplateData', $input_file_name,
                $request->all(), $request->user()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $input_file_name])]);

        }catch(\Throwable $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $ex->getMessage()])]]);
        }
    }
}