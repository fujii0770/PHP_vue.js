<?php
namespace App\Http\Controllers\API;
use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadRequestUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\MailUtils;
use App\Mail\SendAccessCodeNoticeMail;
use App\Mail\SendCircularUserMail;
use App\Mail\SendMailInitPassword;
use App\Mail\SendCircularPullBackMail;
use App\Models\CircularUser;
use App\Jobs\SendNotification;
use App\Jobs\PushNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Repositories\CircularUserRepository;
use App\Repositories\CompanyRepository;
use GuzzleHttp\RequestOptions;
use Session;
use Response;
use Image;
use Symfony\Component\VarDumper\Cloner\Data;

class CsvTemplateDownloadController extends AppBaseController
{
    public function index(SearchCircularUserAPIRequest $request){
        $user       = $request->user();

        $kind           = $request->get('kind');
        $filename       = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $status         = $request->get('status', false);
        $page           = $request->get('page', 1);
        $limit          = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy        = $request->get('orderBy', "update_at");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword        = CircularDocumentUtils::charactersReplace($request->get('keyword'));
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $system_server_flg = config('app.server_flg');

        // 回覧完了日時
        $finishedDateKey = $request->get('finishedDate');

        // 当月
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }
        
        //長期保存対応
        if($finishedDateKey === 12){
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
                            CONCAT(D.sender_name,\'<br>\', \' &lt;\',D.sender_email, \'&gt;\') as sender,
                            CONCAT(D.destination_name,\'<br>\', \' &lt;\',D.destination_email, \'&gt;\') as emails, 
                            D.sender_name as sender_name, 
                            auto_his.result'
                        )
                        ->whereRaw("D.mst_company_id=$user->mst_company_id and ((D.sender_email='$user->email') or (X.csv_permit_user='$user->email'))");
                        
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

                    return $this->sendResponse($data,'完了文書の取得処理に成功しました。');
            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
            try {
                $arrOrder   = ['circular_kind' => 'circular_kind','file_names' => 'd_file_names', 'sender' => 'sender',
                    'emails' => 'emails', 'update_at' => 'update_at'];
                $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';

                $where = [];
                $where_arg = [];

                if (isset($kind) && $kind === '0') {
                    // 受信 PAC_5-1303 送信者自身が受信者に含まれるケースの回避 U.child_send_order != 0 → C.mst_user_id != U.mst_user_id
                    $where[] = "(C.mst_user_id != U.mst_user_id AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ? and (T.user_id = ? or X.mst_user_id = ?))";
                    $where_arg[] = CircularUserUtils::NOT_DELETE;
                    $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
                    $where_arg[] = $system_edition_flg;
                    $where_arg[] = $system_env_flg;
                    $where_arg[] = $system_server_flg;
                    $where_arg[] = $user->id;
                    $where_arg[] = $user->id;
                } else if (isset($kind) && $kind === '1') {
                    // 送信 PAC_5-1303 受信側の条件に合わせる
                    $where[] = "(C.mst_user_id = U.mst_user_id AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ? and (T.user_id = ? or X.mst_user_id = ?))"; 
                    $where_arg[] = CircularUserUtils::NOT_DELETE;
                    $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
                    $where_arg[] = $system_edition_flg;
                    $where_arg[] = $system_env_flg;
                    $where_arg[] = $system_server_flg;
                    $where_arg[] = $user->id;
                    $where_arg[] = $user->id;
                
                } else {
                    $where[] = "((U.email = ? AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?) OR (V.mst_user_id = ? AND V.del_flg = ?))";
                    $where_arg[] = $user->email;
                    $where_arg[] = CircularUserUtils::NOT_DELETE;
                    $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
                    $where_arg[] = $system_edition_flg;
                    $where_arg[] = $system_env_flg;
                    $where_arg[] = $system_server_flg;
                    $where_arg[] = $user->id;
                    $where_arg[] = CircularUserUtils::NOT_DELETE;
                }

                if($filename){
                    $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?))';
                    $where_arg[]    = "%$filename%";
                    $where_arg[]    = "%$filename%";
                }
            
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
                    ->leftjoin('template_csv_permit_user as X', 'C.id', '=' ,'X.circular_id')
                    ->selectRaw(
                        'C.id, 
                        C.access_code, 
                        C.outside_access_code, 
                        CASE WHEN C.mst_user_id = ' . $user->id  .' and C.edition_flg = '.config('app.edition_flg') . ' and C.env_flg = ' . config('app.server_env') . ' and C.server_flg = ' . config('app.server_flg'). ' THEN 1 ELSE 0 END AS circular_kind, 
                        CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender_env,
                        C.completed_date as update_at, 
                        C.circular_status, 
                        U.title as file_names, 
                        U.receiver_title as d_file_names,
                        CONCAT(U.sender_name, \' &lt;\',U.sender_email, \'&gt;\') as sender,
                        U.receiver_name_email AS emails, 
                        U.sender_name as sender_name, 
                        auto_his.result'
                    )
                    ->whereRaw(implode(" AND ", $where), $where_arg)
                    ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
                    

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
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            return $this->sendResponse($data,'完了文書の取得処理に成功しました。');
        }
    }

    /**
     * ダウンロード予約処理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function csvDownloadReserve(Request $request)
    {
        try {
            $user = $request->user();
            $input_file_name = $request->get('filename') == null ? 'template.csv' : $request->get('filename').'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\CsvTemplateDownloadControllerUtils', 'getFinishedCircularTemplateData', $input_file_name,
                $user, $request->all() 
            );

            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $input_file_name])]);

        } catch (\Throwable $th) {
            Log::error($th->getMessage() . $th->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}