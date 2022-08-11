<?php

namespace App\Http\Controllers\API\FormIssuance;

use App\Http\Requests\API\SearchFormIssuanceListAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularUserUtils;
use App\Utils\FormIssuanceUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Models\CircularUser;
use App\Jobs\SendNotification;
use App\Jobs\PushNotify;
use App\Repositories\CompanyRepository;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use Response;
use Image;
use Symfony\Component\VarDumper\Cloner\Data;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spread;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsxDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as XlsxNumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType as XlsxDataType;
use PhpOffice\PhpSpreadsheet\Writer\Csv  as XlsxCsv;
/**
 * Class FormIssuanceListAPIController
 * @package App\Http\Controllers\API\FormIssuance
 */

class FormIssuanceListAPIController extends AppBaseController
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    var $model = null;


    private $rootDirectory;
    private $templateDirectory;
    private $remoteStorage;
    private $storagePutOption;
    private $templateTypeDirectory;
    private $importTypeDirectory;
    private $expTemplateTypeDirectory;

    private $templateFileNamePrefix;
    private $expTemplateFileNamePrefix;
    private $importFileNamePrefix;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;

        $this->rootDirectory = config('app.s3_imprintservice_root_folder');
        $this->templateTypeDirectory = config('app.s3_storage_form_template_folder_type');
        $this->importTypeDirectory = config('app.s3_storage_form_import_folder_type');
        $this->expTemplateTypeDirectory = config('app.s3_storage_exp_template_folder_type');

        $this->templateFileNamePrefix = config('app.s3_storage_template_file_name_prefix');
        $this->expTemplateFileNamePrefix = config('app.s3_storage_exp_template_file_name_prefix');
        $this->expTemplateOutFileNamePrefix = config('app.s3_storage_exp_template_file_out_name_prefix');
        $this->importFileNamePrefix = config('app.s3_storage_import_file_name_prefix');

        $this->templateDirectory = config('app.server_env') . '/' . config('app.edition_flg')
            . '/' . config('app.server_flg');
        $this->remoteStorage = 's3'; // TODO s3
    }

    public function actionMultiple($action, Request $request){
        if($action =="deleteReport" || $action =="deleteReportOther" )
            return $this->$action($request);
        else return $this->sendError("FormIssuanceListAPIController not found action: " . $action);
    }

    /**
     * delete list report
     */
    public function deleteReport(Request $request){
        $user = $request->user();
        $requestCids = $request->get('cids',[]);
        if(count($requestCids)){
            try{
                $list_frm_invoice_data  =  DB::table('frm_invoice_data')
                ->whereIn('id', $requestCids)
                ->get();
                $cidsInvoice = $list_frm_invoice_data->pluck('circular_id')->toArray();

                $listCircular   =   DB::table('circular')
                    ->whereIn('id', $cidsInvoice)->get();

                $cids = $listCircular->pluck('id')->toArray();
                if (count($cids)){

                    $fileNames  = [];
                    $circular_docs  =   DB::table('circular_document')
                        ->whereIn('circular_id', $cids)
                        ->where(function($query) use ($user){
                            $query->where('confidential_flg', 0);
                            $query->orWhere(function($query1) use ($user){
                                $query1->where('confidential_flg', 1);
                                $query1->where('create_company_id', $user->mst_company_id);
                                $query1->where('origin_edition_flg', config('app.edition_flg'));
                                $query1->where('origin_env_flg', config('app.server_env'));
                                $query1->where('origin_server_flg', config('app.server_flg'));
                            });
                        })
                        ->select('id','circular_id','file_name')
                        ->get()->keyBy('id');

                    if(count($circular_docs)){
                        foreach($circular_docs as $circular_doc){
                            $cids[]         = $circular_doc->circular_id;
                            $fileNames[]    = $circular_doc->file_name;
                        }
                        Session::flash('fileNames', $fileNames);
                    }
                    $filtered = $listCircular->filter(function ($item, $index) { //0:保存中、5引戻　以外の場合エラー　
                        return ($item->circular_status != CircularUtils::RETRACTION_STATUS) && ($item->circular_status != CircularUtils::SAVING_STATUS) && ($item->circular_status != CircularUtils::RETRACTION_STATUS) ;
                    });
                    if(count($filtered)){
                        return $this->sendError('回覧前データ以外が存在するため削除できませんでした。');
                    }
                    
                    //削除権限があるかのチェック
                    $circular  =   DB::table('circular')
                        ->whereIn('id', $cids)
                        ->where(function($query) use ($user){
                            $query->where('mst_user_id','<>', $user->id);
                        })
                        ->select('id')
                        ->get()->keyBy('id');

                    if(count($circular)){
                        return $this->sendError('削除権限が無いため削除できませんでした。');
                    }

                    DB::beginTransaction();

                    DB::table('frm_invoice_data')
                    ->whereIn('id', $requestCids)
                    ->delete()
                    ;

                    DB::commit();
                }
            }catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        return $this->sendResponse(true,'請求書の削除処理に成功しました。');
    }

    /**
     * delete list report
     */
    public function deleteReportOther(Request $request){
        $user = $request->user();
        $requestCids = $request->get('cids',[]);
        if(count($requestCids)){
            try{
                $list_frm_others_data  =  DB::table('frm_others_data')
                ->whereIn('id', $requestCids)
                ->get();
                $cidsothers = $list_frm_others_data->pluck('circular_id')->toArray();

                $listCircular   =   DB::table('circular')
                    ->whereIn('id', $cidsothers)->get();

                $cids = $listCircular->pluck('id')->toArray();
                if (count($cids)){

                    $fileNames  = [];
                    $circular_docs  =   DB::table('circular_document')
                        ->whereIn('circular_id', $cids)
                        ->where(function($query) use ($user){
                            $query->where('confidential_flg', 0);
                            $query->orWhere(function($query1) use ($user){
                                $query1->where('confidential_flg', 1);
                                $query1->where('create_company_id', $user->mst_company_id);
                                $query1->where('origin_edition_flg', config('app.edition_flg'));
                                $query1->where('origin_env_flg', config('app.server_env'));
                                $query1->where('origin_server_flg', config('app.server_flg'));
                            });
                        })
                        ->select('id','circular_id','file_name')
                        ->get()->keyBy('id');

                    if(count($circular_docs)){
                        foreach($circular_docs as $circular_doc){
                            $cids[]         = $circular_doc->circular_id;
                            $fileNames[]    = $circular_doc->file_name;
                        }
                        Session::flash('fileNames', $fileNames);
                    }
                    $filtered = $listCircular->filter(function ($item, $index) { //0:保存中、5引戻　以外の場合エラー　
                        return ($item->circular_status != CircularUtils::RETRACTION_STATUS) && ($item->circular_status != CircularUtils::SAVING_STATUS) && ($item->circular_status != CircularUtils::RETRACTION_STATUS) ;
                    });
                    if(count($filtered)){
                        return $this->sendError('回覧前データ以外が存在するため削除できませんでした。');
                    }

                    //削除権限があるかのチェック
                    $circular  =   DB::table('circular')
                        ->whereIn('id', $cids)
                        ->where(function($query) use ($user){
                            $query->where('mst_user_id','<>', $user->id);
                        })
                        ->select('id')
                        ->get()->keyBy('id');

                    if(count($circular)){
                        return $this->sendError('削除権限が無いため削除できませんでした。');
                    }

                    DB::beginTransaction();

                    DB::table('frm_others_data')
                    ->whereIn('id', $requestCids)
                    ->delete()
                    ;
                    DB::commit();
                }
            }catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        return $this->sendResponse(true,'請求書の削除処理に成功しました。');
    }

    /**
     * 請求書一覧リスト画面初期化
     *
     * @param SearchFormIssuanceListAPIRequest $request
     * @return
     * @throws \Exception
     */
    public function indexReport(SearchFormIssuanceListAPIRequest $request){
        $user       = $request->user();

        $csv_flg        = $request->get('csv_flg');
        $id             = $request->get('id');
        $filename       = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $reviewStatus   = $request->get('reviewStatus');
        $fromdate       = $request->get('fromdate');
        $todate         = $request->get('todate');
        $fromdateKijitu = $request->get('fromdateKijitu');
        $todateKijitu   = $request->get('todateKijitu');
        $fromdateHakko  = $request->get('fromdateHakko');
        $todateHakko    = $request->get('todateHakko');
        $receiverName   = $request->get('receiverName');
        $status         = $request->get('status', false);
        $page           = $request->get('page', 1);
        $limit          = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy        = $request->get('orderBy', "invoice_date");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $useTemplate    = false;
        $system_server_flg = config('app.server_flg');
        $max_issu_export_count     = config('app.max_issu_export_count');

        // 回覧完了日時
        $finishedDateKey = $request->get('finishedDate');
        // circularテーブル分割対応
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = date('Ym', strtotime(date('Ym')." - $finishedDateKey month"));
        }
        $arrOrder   = ['company_frm_id' => 'company_frm_id','frm_name' => 'frm_name', 'customer_name' => 'customer_name', 'circular_status' => 'circular_status'
            , 'invoice_amt' => 'invoice_amt', 'trading_date' => 'trading_date', 'invoice_date' => 'invoice_date', 'payment_date' => 'payment_date', 'create_at' => 'create_at', 'update_user' => 'update_user', 'update_at' => 'update_at'];
 
        if($csv_flg){//exportボタン押下時は売上計上日降順
            $orderBy  = 'trading_date';
            $orderDir = 'DESC';
        }else{
            $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';
        }
 
        $where = [];
        $where_arg = [];
        $where_temp = [];
        $where_arg_temp = [];
        //共通の抽出条件
        $where[] = "((I.mst_company_id = ?) )";
        $where_arg[] = $user->mst_company_id;

        if (is_array($id) ||  is_array($filename) || is_array($receiverName) || is_array($fromdate) || is_array($todate) || is_array($fromdateKijitu) || is_array($todateKijitu) || is_array($fromdateHakko) || is_array($fromdateHakko) ||is_array($todateHakko))
        {
            $where[]        = '0 = ?'; //検索を無効にする
            $where_arg[]    = '1';
        }else{
            if($id){
                $where[]        = 'I.company_frm_id like ?';
                $where_arg[]    = "%$id%";
            }
            if($filename){
                $where[]        = 'I.frm_name like ?';
                $where_arg[]    = "%$filename%";
            }
            if($receiverName){
                $where[]        = 'I.customer_name like ?';
                $where_arg[]    = "%$receiverName%";
            }
            if($fromdate){
                $where[]        = 'I.trading_date >= ?';
                $where_arg[]    = $fromdate;
            }
            if($todate){
                $where[]        = 'I.trading_date <= ?';
                $where_arg[]    = $todate;
            }
            if($fromdateKijitu){
                $where[]        = 'I.payment_date >= ?';
                $where_arg[]    = $fromdateKijitu;
            }
            if($todateKijitu){
                $where[]        = 'I.payment_date <= ?';
                $where_arg[]    = $todateKijitu;
            }
            if($fromdateHakko){
                $where[]        = 'I.invoice_date >= ?';
                $where_arg[]    = $fromdateHakko;
            }
            if($todateHakko){
                $where[]        = 'I.invoice_date < ?';
                $where_arg[]    = (new \DateTime($todateHakko))->modify('+1 day')->format('Y-m-d');
            }
            //PAC_5-3124 S
            if($reviewStatus == 2) {//完了がチェックオンの場合
                //完了日時条件を追加
                //完了日時From/Toの日付範囲を設定
                $date = Carbon::now()->addMonth($finishedDateKey ? 0 - $finishedDateKey : 0);
                $firstDay = $date->firstOfMonth()->format('Y-m-d');
                $lastDay = $date->lastOfMonth()->format('Y-m-d');

                $where[] = 'C.completed_date >= ?';
                $where_arg[] = $firstDay;
                $where[] = 'C.completed_date < ?';
                $where_arg[] = (new \DateTime($lastDay))->modify('+1 day')->format('Y-m-d');
            }
            //PAC_5-3124 E
        }
        //状況
        $where_status_child = [];
        $where_status_child_arg = [];
        $where_status_cond = [];
        $where_status_cond_str =  '';
        if(!$reviewStatus){//回覧前がチェックオンの場合 ログインユーザが作成したものだけが対象
            $where_status_child[] =  'C.circular_status IN(%d,%d)' ;
            $where_status_child_arg[] =  CircularUtils::SAVING_STATUS ;
            $where_status_child_arg[] =  CircularUtils::RETRACTION_STATUS ;
            $where_status_child[] =  'C.mst_user_id = %d' ;
            $where_status_child_arg[] =  $user->id ;
            $where_status_cond[] = '(' .implode(' AND ' ,$where_status_child) .')';
        }
        if($reviewStatus == 1){//回覧中がチェックオンの場合 $where_status_child_argはSQL生成時に使用するのでここでは初期化しない
            $where_status_child = [];
            $where_status_child[] =  'C.circular_status IN(%d,%d)' ;
            $where_status_child_arg[] =  CircularUtils::CIRCULATING_STATUS ;
            $where_status_child_arg[] =  CircularUtils::SEND_BACK_STATUS ;

            $where_status_child[] =  ' U.del_flg = %d ' ;
            $where_status_child_arg[] =  CircularUserUtils::NOT_DELETE ;
            $where_status_child[] =  ' U.edition_flg = %d ' ;
            $where_status_child_arg[] =  $system_edition_flg ;
            $where_status_child[] =  ' U.env_flg = %d ' ;
            $where_status_child_arg[] =  $system_env_flg ;
            $where_status_child[] =  ' U.server_flg = %d ' ;
            $where_status_child_arg[] =  $system_server_flg ;

            $where_status_cond[] = '(' .implode(' AND ' ,$where_status_child) .')';
        }
        if (!empty($where_status_cond)){
            $where_status_cond_str =  '(' .vsprintf(implode(" OR ",$where_status_cond ),$where_status_child_arg) .')';
        }else{
            $where_status_cond_str =  '(0 = 1)';
        }

        $where_complete_status_cond = [];
        $where_complete_status_cond_str =  '';
        if($reviewStatus == 2){//完了がチェックオンの場合
            $where_status_child = [];
            $where_status_child_arg = [];
            $where_status_child[] =  'C.circular_status IN(%d,%d)' ;
            $where_status_child_arg[] =  CircularUtils::CIRCULAR_COMPLETED_STATUS ;
            $where_status_child_arg[] =  CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS ;

            $where_status_child[] =  ' U.del_flg = %d ' ;
            $where_status_child_arg[] =  CircularUserUtils::NOT_DELETE ;
            $where_status_child[] =  ' U.edition_flg = %d ' ;
            $where_status_child_arg[] =  $system_edition_flg ;
            $where_status_child[] =  ' U.env_flg = %d ' ;
            $where_status_child_arg[] =  $system_env_flg ;
            $where_status_child[] =  ' U.server_flg = %d ' ;
            $where_status_child_arg[] =  $system_server_flg ;

            $where_complete_status_cond[] = '(' .implode(' AND ' ,$where_status_child) .')';
        }
        if (!empty($where_complete_status_cond)){
            $where_complete_status_cond_str =  '(' .vsprintf(implode(" OR ",$where_complete_status_cond ),$where_status_child_arg) .')';
        }else{
            $where_complete_status_cond_str =  '(0 = 1)';
        }
        
        try{
            $admin_user_flg = 0;//利用者
            if($admin_user_flg){//管理者の場合
                // //回覧前、回覧中の抽出
                // $data_before = DB::table("frm_invoice_data as I")
                // ->join('circular as C', 'I.circular_id', 'C.id')
                // ->join('circular_user as U', 'C.id', 'U.circular_id')
                // ->select(DB::raw('I.frm_data, C.circular_status,U.title as file_names, I.id, I.circular_id, I.company_frm_id, I.frm_name, CONCAT(I.to_name, \' &lt;\',I.To_email, \'&gt;\') as to_name, CONCAT(I.to_name, \' <\',I.To_email, \'>\') as to_name_excel, I.invoice_amt, I.invoice_date, I.payment_date, I.create_at, IFNULL(I.update_user, I.create_user) as update_user, IFNULL(I.update_at, I.create_at) as update_at,I.invoice_no'))
                // ->whereRaw(implode(" AND ", $where), $where_arg)
                // ->WhereRaw("U.parent_send_order = 0 AND U.child_send_order = 0")
                // ->WhereRaw(DB::raw($where_status_cond_str))
                // ;
                // // ->orderBy($orderBy,$orderDir);

                // //完了の抽出
                // $data = DB::table("frm_invoice_data as I")
                // ->join("circular$finishedDate as C", "I.circular_id", "C.id")
                // ->join("circular_user$finishedDate as U", "C.id", "U.circular_id")
                // ->select(DB::raw('I.frm_data, C.circular_status,U.title as file_names, I.id, I.circular_id, I.company_frm_id, I.frm_name, CONCAT(I.to_name, \' &lt;\',I.To_email, \'&gt;\') as to_name, CONCAT(I.to_name, \' <\',I.To_email, \'>\') as to_name_excel, I.invoice_amt, I.invoice_date, I.payment_date, I.create_at, IFNULL(I.update_user, I.create_user) as update_user, IFNULL(I.update_at, I.create_at) as update_at,I.invoice_no'))
                // ->whereRaw(implode(" AND ", $where), $where_arg)
                // ->WhereRaw("U.parent_send_order = 0 AND U.child_send_order = 0")
                // ->WhereRaw(DB::raw($where_complete_status_cond_str))
                // ->union($data_before)
                // ->orderBy($orderBy,$orderDir)
                // ;

            }else{//利用者の場合 　重複させない
                //回覧を抽出するサブクエリ
                $viewingQueryUser = DB::table('circular_user as CU')
                ->select('CU.circular_id')
                ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("CU.mst_user_id = ".$user->id)
                ->groupBy(['CU.circular_id'])
                ;
                //閲覧を抽出するサブクエリ
                $viewingQuerySub = DB::table('viewing_user as VU')
                ->select('VU.circular_id')
                ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("VU.mst_user_id = ".$user->id)
                ->groupBy(['VU.circular_id'])
                ->union($viewingQueryUser)
                ->distinct()
                ;
                
                //回覧前、回覧中の抽出 あとで完了とunionする
                $data_before = DB::table("frm_invoice_data as I")
                ->join('circular as C', 'I.circular_id', 'C.id')
                ->join('circular_document as CD', function($join) use ($user){
                    $join->on('C.id', '=', 'CD.circular_id');
                    $join->on(function($condition) use ($user){
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function($condition) use ($user){
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('CD.parent_send_order', DB::raw('0'));
                        });
                    });
                })
                ->leftJoin('circular_user as U', function ($join) { //U.titleを取得するためのleftJoin
                    $join->on('C.id', '=', 'U.circular_id')
                    ->where('U.parent_send_order', '=', 0)
                    ->where('U.child_send_order', '=', 0);
                })
                ->leftJoinSub($viewingQuerySub, 'V', function ($join) {
                    $join->on('C.id', '=', 'V.circular_id');
                })
                // ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, I.frm_data, C.circular_status,U.title as file_names, I.id, I.circular_id, I.company_frm_id, I.frm_name, CONCAT(I.to_name, \' &lt;\',I.To_email, \'&gt;\') as to_name, CONCAT(I.to_name, \' <\',I.To_email, \'>\') as to_name_excel, I.invoice_amt, I.invoice_date, I.payment_date, I.create_at, IFNULL(I.update_user, I.create_user) as update_user, IFNULL(I.update_at, I.create_at) as update_at,I.invoice_no'))
                ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, I.frm_data, C.circular_status, IF(U.title IS NULL or trim(U.title) = \'\', GROUP_CONCAT(CD.file_name  ORDER BY CD.id ASC SEPARATOR \', \'), U.title) as file_names, I.id, I.circular_id, I.company_frm_id, I.frm_name, I.customer_name, I.invoice_amt, FORMAT(I.invoice_amt,0) as invoice_amt_comma, I.trading_date, I.invoice_date, I.payment_date, I.create_at, IFNULL(I.update_user, I.create_user) as update_user, IFNULL(I.update_at, I.create_at) as update_at,I.invoice_no'))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->WhereRaw(DB::raw($where_status_cond_str))
                ->groupBy(['I.id', 'C.id','C.final_updated_date','U.title'])
                ;

                //完了にかかわる回覧
                $viewingQueryUserComplete = DB::table("circular_user$finishedDate as CU")
                ->select('CU.circular_id')
                ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("CU.mst_user_id = ".$user->id)
                ->groupBy(['CU.circular_id'])
                ;
                //完了にかかわる閲覧
                $viewingQuerySubComplete = DB::table('viewing_user as VU')
                ->select('VU.circular_id')
                ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("VU.mst_user_id = ".$user->id)
                ->groupBy(['VU.circular_id'])
                ->union($viewingQueryUserComplete)
                ->distinct()
                ;
                //完了の抽出
                $data = DB::table("frm_invoice_data as I")
                ->join("circular$finishedDate as C", "I.circular_id", "C.id")
                ->join("circular_user$finishedDate as U", "C.id", "U.circular_id")
                ->leftJoinSub($viewingQuerySubComplete, 'V', function ($join) {
                    $join->on('C.id', '=', 'V.circular_id');
                })
                // ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, I.frm_data, C.circular_status,U.title as file_names, I.id, I.circular_id, I.company_frm_id, I.frm_name, CONCAT(I.to_name, \' &lt;\',I.To_email, \'&gt;\') as to_name, CONCAT(I.to_name, \' <\',I.To_email, \'>\') as to_name_excel, I.invoice_amt, I.invoice_date, I.payment_date, I.create_at, IFNULL(I.update_user, I.create_user) as update_user, IFNULL(I.update_at, I.create_at) as update_at,I.invoice_no'))
                ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, I.frm_data, C.circular_status,U.title as file_names, I.id, I.circular_id, I.company_frm_id, I.frm_name, I.customer_name, I.invoice_amt,FORMAT(I.invoice_amt,0) as invoice_amt_comma, I.trading_date, I.invoice_date, I.payment_date, I.create_at, IFNULL(I.update_user, I.create_user) as update_user, IFNULL(I.update_at, I.create_at) as update_at,I.invoice_no'))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->WhereRaw("U.parent_send_order = 0 AND U.child_send_order = 0")
                ->WhereRaw(DB::raw($where_complete_status_cond_str))
                ->union($data_before)
                ->orderBy($orderBy,$orderDir)
                ->orderBy('id',$orderDir)
                ;
            }

            if($csv_flg){//exportボタン押下時はpaginateしない
                return $data->limit($max_issu_export_count)
                            ->get();
            }else{//初期表示時、検索ボタン押下時
                $data = $data->paginate($limit)->appends(request()->input());
            }

            //差し戻し判定用
            if(!$data->isEmpty()){
                $listCircular_id = $data->pluck('id')->all();
                $listUserSend = DB::table('circular_user')
                    ->whereIn('circular_id', $listCircular_id)
                    ->get();

                foreach($data as $item){
                    $circularUsers = $listUserSend->filter(function ($value) use ($item){
                        return $value->circular_id == $item->id;
                    });

                    $item->hasRequestSendBack = false;

                    if($circularUsers->some(function($value){ return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->hasRequestSendBack = true;
                    }
                }
            }

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($data,'請求書一覧の取得処理に成功しました。');
    }

    /**
     * その他帳票一覧リスト画面初期化
     *
     * @param SearchFormIssuanceListAPIRequest $request
     * @return
     * @throws \Exception
     */
    public function indexReportOther(SearchFormIssuanceListAPIRequest $request){
        $user       = $request->user();

        $csv_flg        = $request->get('csv_flg');
        $id             = $request->get('id');
        $filename       = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $reviewStatus   = $request->get('reviewStatus');
        $receiverName   = $request->get('receiverName');
        $status         = $request->get('status', false);
        $page           = $request->get('page', 1);
        $limit          = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy        = $request->get('orderBy', "create_at");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        // $keyword        = CircularDocumentUtils::charactersReplace($request->get('keyword'));
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $useTemplate    = false;
        $system_server_flg = config('app.server_flg');
        $max_issu_export_count     = config('app.max_issu_export_count');

        // 回覧完了日時
        $fromReferenceDate = $request->get('fromReferenceDate');
        $toReferenceDate = $request->get('toReferenceDate');
        $indexes = $request->get('indexes');

        $finishedDateKey = $request->get('finishedDate');
        // 当月
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = date('Ym', strtotime(date('Ym')." - $finishedDateKey month"));
        }

        $arrOrder   = ['company_frm_id' => 'company_frm_id','frm_name' => 'frm_name', 'customer_name' => 'customer_name',
        'reference_date' => 'reference_date','create_at' => 'create_at', 'update_user' => 'update_user', 'update_at' => 'update_at'];
        if($csv_flg){//exportボタン押下時は基準日降順
            $orderBy  = 'reference_date';
            $orderDir = 'DESC';
        }else{
            $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'create_at';
        }

        $where = [];
        $where_arg = [];
        $where_temp = [];
        $where_arg_temp = [];

        $where[] = "((O.mst_company_id = ?) )";
        $where_arg[] = $user->mst_company_id;

        //PAC_5-3124 S
        if($reviewStatus == 2) {//完了がチェックオンの場合
            //完了日時条件を追加
            //完了日時From/Toの日付範囲を設定
            $date = Carbon::now()->addMonth($finishedDateKey ? 0 - $finishedDateKey : 0);
            $firstDay = $date->firstOfMonth()->format('Y-m-d');
            $lastDay = $date->lastOfMonth()->format('Y-m-d');

            $where[] = 'C.completed_date >= ?';
            $where_arg[] = $firstDay;
            $where[] = 'C.completed_date < ?';
            $where_arg[] = (new \DateTime($lastDay))->modify('+1 day')->format('Y-m-d');
        }
        //PAC_5-3124 E

        if (is_array($id) ||  is_array($filename) || is_array($receiverName) || is_array($fromReferenceDate) || is_array($toReferenceDate))
        {
            $where[]        = '0 = ?'; //検索を無効にする
            $where_arg[]    = '1';
        }else{
            if($id){
                $where[]        = 'O.company_frm_id like ?';
                $where_arg[]    = "%$id%";
            }
            if($filename){
                $where[]        = 'O.frm_name like ?';
                $where_arg[]    = "%$filename%";
            }
            if($receiverName){
                $where[]        = 'O.customer_name like ?';
                $where_arg[]    = "%$receiverName%";
            }
            if($fromReferenceDate){
                $where[]        = 'O.reference_date >= ?';
                $where_arg[]    = $fromReferenceDate;
            }
            if($toReferenceDate){
                $where[]        = 'O.reference_date <= ?';
                $where_arg[]    = $toReferenceDate;
            }
            if($indexes){
                foreach ($indexes as $value) {
                    if($value['id'] && ($value['fromvalue'] || $value['tovalue'])){
                        $frmIndex = DB::table('frm_index')
                            ->where('id', $value['id'])
                            ->first();
                        $frmIndexContent = 'frm_index'.$frmIndex->frm_index_number;
                        if($frmIndex->data_type === FormIssuanceUtils::DATA_TYPE_TEXT){
                            $where[]        = "O.$frmIndexContent like ?";
                            $where_arg[]    = '%'.$value['fromvalue'].'%';
                        }else{
                            if($value['fromvalue']){
                                $where[]        = "O.$frmIndexContent >= ?";
                                $where_arg[]    = $value['fromvalue'];
                            }
                            if($value['tovalue']){
                                $where[]        = "O.$frmIndexContent <= ?";
                                $where_arg[]    = $value['tovalue'];
                            }
                        }
                    }
                }
            }
        }
        
        if (!empty($where_status_cond)){
            $where_status_cond_str =  '(' .vsprintf(implode(" OR ",$where_status_cond ),$where_status_child_arg) .')';
        }else{
            $where_status_cond_str =  '(0 = 1)';
        }  
        //状況
        $where_status_child = [];
        $where_status_child_arg = [];
        $where_status_cond = [];
        $where_status_cond_str =  '';
        if(!$reviewStatus){//回覧前がチェックオンの場合 ログインユーザが作成したものだけが対象
            $where_status_child[] =  'C.circular_status IN(%d,%d)' ;
            $where_status_child_arg[] =  CircularUtils::SAVING_STATUS ;
            $where_status_child_arg[] =  CircularUtils::RETRACTION_STATUS ;
            $where_status_child[] =  'C.mst_user_id = %d' ;
            $where_status_child_arg[] =  $user->id ;
            $where_status_cond[] = '(' .implode(' AND ' ,$where_status_child) .')';
        }
        if($reviewStatus == 1){//回覧中がチェックオンの場合 $where_status_child_argはSQL生成時に使用するのでここでは初期化しない
            $where_status_child = [];
            $where_status_child[] =  'C.circular_status IN(%d,%d)' ;
            $where_status_child_arg[] =  CircularUtils::CIRCULATING_STATUS ;
            $where_status_child_arg[] =  CircularUtils::SEND_BACK_STATUS ;

            $where_status_child[] =  ' U.del_flg = %d ' ;
            $where_status_child_arg[] =  CircularUserUtils::NOT_DELETE ;
            $where_status_child[] =  ' U.edition_flg = %d ' ;
            $where_status_child_arg[] =  $system_edition_flg ;
            $where_status_child[] =  ' U.env_flg = %d ' ;
            $where_status_child_arg[] =  $system_env_flg ;
            $where_status_child[] =  ' U.server_flg = %d ' ;
            $where_status_child_arg[] =  $system_server_flg ;

            $where_status_cond[] = '(' .implode(' AND ' ,$where_status_child) .')';
        }
        if (!empty($where_status_cond)){
            $where_status_cond_str =  '(' .vsprintf(implode(" OR ",$where_status_cond ),$where_status_child_arg) .')';
        }else{
            $where_status_cond_str =  '(0 = 1)';
        }

        $where_complete_status_cond = [];
        $where_complete_status_cond_str =  '';
        if($reviewStatus == 2){//完了がチェックオンの場合
            $where_status_child = [];
            $where_status_child_arg = [];
            $where_status_child[] =  'C.circular_status IN(%d,%d)' ;
            $where_status_child_arg[] =  CircularUtils::CIRCULAR_COMPLETED_STATUS ;
            $where_status_child_arg[] =  CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS ;

            $where_status_child[] =  ' U.del_flg = %d ' ;
            $where_status_child_arg[] =  CircularUserUtils::NOT_DELETE ;
            $where_status_child[] =  ' U.edition_flg = %d ' ;
            $where_status_child_arg[] =  $system_edition_flg ;
            $where_status_child[] =  ' U.env_flg = %d ' ;
            $where_status_child_arg[] =  $system_env_flg ;
            $where_status_child[] =  ' U.server_flg = %d ' ;
            $where_status_child_arg[] =  $system_server_flg ;

            $where_complete_status_cond[] = '(' .implode(' AND ' ,$where_status_child) .')';
        }
        if (!empty($where_complete_status_cond)){
            $where_complete_status_cond_str =  '(' .vsprintf(implode(" OR ",$where_complete_status_cond ),$where_status_child_arg) .')';
        }else{
            $where_complete_status_cond_str =  '(0 = 1)';
        }

        try{
            $admin_user_flg = 0;//利用者
            if($admin_user_flg){//管理者の場合
                // //回覧前、回覧中の抽出
                // $data_before = DB::table('frm_others_data as O')
                // ->join('circular as C', 'O.circular_id', 'C.id')
                // ->join('circular_user as U', 'C.id', 'U.circular_id')
                // ->select(DB::raw('O.frm_data, C.circular_status,U.title as file_names, O.company_frm_id, O.id, O.circular_id, O.frm_name as frm_name, CONCAT(O.to_name, \' &lt;\',O.To_email, \'&gt;\') as to_name, O.create_at, IFNULL(O.update_user, O.create_user) as update_user, IFNULL(O.update_at, O.create_at) as update_at'))
                // ->whereRaw(implode(" AND ", $where), $where_arg)
                // ->WhereRaw("U.parent_send_order = 0 AND U.child_send_order = 0")
                // ->orderBy($orderBy,$orderDir)
                // ;

                // //完了の抽出
                // $data = DB::table('frm_others_data as O')
                // ->join("circular$finishedDate as C", "O.circular_id", "C.id")
                // ->join("circular_user$finishedDate as U", "C.id", "U.circular_id")
                // ->select(DB::raw('O.frm_data, C.circular_status,U.title as file_names, O.company_frm_id, O.id, O.circular_id, O.frm_name as frm_name, CONCAT(O.to_name, \' &lt;\',O.To_email, \'&gt;\') as to_name, O.create_at, IFNULL(O.update_user, O.create_user) as update_user, IFNULL(O.update_at, O.create_at) as update_at'))
                // ->whereRaw(implode(" AND ", $where), $where_arg)
                // ->WhereRaw("U.parent_send_order = 0 AND U.child_send_order = 0")
                // ->WhereRaw(DB::raw($where_complete_status_cond_str))
                // ->union($data_before)
                // ->orderBy($orderBy,$orderDir)
                // ;
            }else{//利用者の場合
                //回覧を抽出するサブクエリ
                $viewingQueryUser = DB::table('circular_user as CU')
                ->select('CU.circular_id')
                ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("CU.mst_user_id = ".$user->id)
                ->groupBy(['CU.circular_id'])
                ;
                //閲覧を抽出するサブクエリ
                $viewingQuerySub = DB::table('viewing_user as VU')
                ->select('VU.circular_id')
                ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("VU.mst_user_id = ".$user->id)
                ->groupBy(['VU.circular_id'])
                ->union($viewingQueryUser)
                ->distinct()
                ;
                
                //回覧前、回覧中の抽出 あとで完了とunionする
                $data_before = DB::table('frm_others_data as O')
                ->join('circular as C', 'O.circular_id', 'C.id')
                ->join('circular_document as CD', function($join) use ($user){
                    $join->on('C.id', '=', 'CD.circular_id');
                    $join->on(function($condition) use ($user){
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function($condition) use ($user){
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('CD.parent_send_order', DB::raw('0'));
                        });
                    });
                })
                ->leftJoin('circular_user as U', function ($join) {
                    $join->on('C.id', '=', 'U.circular_id')
                    ->where('U.parent_send_order', '=', 0)
                    ->where('U.child_send_order', '=', 0);
                })
                ->leftJoinSub($viewingQuerySub, 'V', function ($join) {
                    $join->on('C.id', '=', 'V.circular_id');
                })
                // ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, O.frm_data, C.circular_status,U.title as file_names, O.company_frm_id, O.id, O.circular_id, O.frm_name as frm_name, CONCAT(O.to_name, \' &lt;\',O.To_email, \'&gt;\') as to_name, O.create_at, IFNULL(O.update_user, O.create_user) as update_user, IFNULL(O.update_at, O.create_at) as update_at'))
                ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, O.frm_data, C.circular_status, IF(U.title IS NULL or trim(U.title) = \'\', GROUP_CONCAT(CD.file_name  ORDER BY CD.id ASC SEPARATOR \', \'), U.title) as file_names, O.company_frm_id, O.id, O.circular_id, O.frm_name as frm_name, O.customer_name, O.reference_date, O.create_at, IFNULL(O.update_user, O.create_user) as update_user, IFNULL(O.update_at, O.create_at) as update_at'))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->WhereRaw(DB::raw($where_status_cond_str))
                ->groupBy(['O.id', 'C.id','C.final_updated_date','U.title'])
                ;

                //完了にかかわる回覧
                $viewingQueryUserComplete = DB::table("circular_user$finishedDate as CU")
                ->select('CU.circular_id')
                ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("CU.mst_user_id = ".$user->id)
                ->groupBy(['CU.circular_id'])
                ;
                //完了にかかわる閲覧
                $viewingQuerySubComplete = DB::table('viewing_user as VU')
                ->select('VU.circular_id')
                ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                ->whereRaw("VU.mst_user_id = ".$user->id)
                ->groupBy(['VU.circular_id'])
                ->union($viewingQueryUserComplete)
                ->distinct()
                ;

                //完了の抽出
                $data = DB::table("frm_others_data as O")
                ->join("circular$finishedDate as C", "O.circular_id", "C.id")
                ->join("circular_user$finishedDate as U", "C.id", "U.circular_id")
                ->JoinSub($viewingQuerySubComplete, 'V', function ($join) {
                    $join->on('C.id', '=', 'V.circular_id');
                })
                // ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, O.frm_data, C.circular_status,U.title as file_names, O.company_frm_id, O.id, O.circular_id, O.frm_name as frm_name, CONCAT(O.to_name, \' &lt;\',O.To_email, \'&gt;\') as to_name, O.create_at, IFNULL(O.update_user, O.create_user) as update_user, IFNULL(O.update_at, O.create_at) as update_at'))
                ->select(DB::raw('IFNULL(V.circular_id,0) AS v_circular_id, O.frm_data, C.circular_status,U.title as file_names, O.company_frm_id, O.id, O.circular_id, O.frm_name as frm_name, customer_name, O.reference_date, O.create_at, IFNULL(O.update_user, O.create_user) as update_user, IFNULL(O.update_at, O.create_at) as update_at'))
                ->WhereRaw("U.parent_send_order = 0 AND U.child_send_order = 0")
                ->WhereRaw(DB::raw($where_complete_status_cond_str))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->union($data_before)
                ->orderBy($orderBy,$orderDir)
                ->orderBy('id',$orderDir)
                ;

            }
            if($csv_flg){//exportボタン押下時はpaginateしない
                return $data->limit($max_issu_export_count)
                            ->get();
            }else{//初期表示時、検索ボタン押下時
                $data = $data->paginate($limit)->appends(request()->input());
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($data,'明細一覧の取得処理に成功しました。');
    }

    /**
     * 請求書テンプレートリスト取得
     *
     * @param SearchFormIssuanceListAPIRequest $request
     * @return
     * @throws \Exception
     */
    public function indexTemplate(SearchFormIssuanceListAPIRequest $request){
        $user       = $request->user();

        $id             = $request->get('id');
        $filename       = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $orderBy        = $request->get('orderBy', "update_at");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $max_issu_export_count     = config('app.max_issu_export_count');
        $limit = 10000;
        $arrOrder   = ['update_at' => 'update_at'];
        $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';

        try{
            $data = DB::table('frm_exp_template as E')
            ->select(DB::raw('E.id as frm_template_id, E.file_name as template_name, E.remarks'))
            ->whereRaw('E.mst_company_id = ?', $user->mst_company_id)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit)
            ->appends(request()->input());

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['data_export' => $data, 'max_issu_export_count' => $max_issu_export_count],'テンプレート一覧の取得処理に成功しました。');
    }

    /**
     * その他帳票テンプレートリスト取得
     *
     * @param SearchFormIssuanceListAPIRequest $request
     * @return
     * @throws \Exception
     */
    public function indexTemplateOther(SearchFormIssuanceListAPIRequest $request){
        $user       = $request->user();

        $id             = $request->get('id');
        $filename       = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $orderBy        = $request->get('orderBy', "update_at");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $system_server_flg = config('app.server_flg');
        $max_issu_export_count     = config('max_issu_export_count');
        $limit = 10000;
        $arrOrder   = ['update_at' => 'update_at'];
        $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';

        try{
            $data = DB::table('frm_exp_template as E')
            ->select(DB::raw('E.id as frm_template_id, E.file_name as template_name, E.remarks'))
            ->whereRaw('E.mst_company_id = ?', $user->mst_company_id)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit)
            ->appends(request()->input());
            
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['data_export' => $data, 'max_issu_export_count' => $max_issu_export_count],'明細のテンプレート一覧取得処理に成功しました。');
    }

    /**
     * get detail Invoice
     */
    public function detailShowInvoice($id,Request $request){
        try {
            $user       = $request->user();

            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            // 当月 （完了以外の場合も0がセットされてくるようにした）
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = date('Ym', strtotime(date('Ym')." - $finishedDateKey month"));
            }
            $circular = DB::table("circular$finishedDate as C")
            ->join('frm_invoice_data as I', 'I.circular_id', 'C.id')
            ->where('I.id', $id)->first();
            
            if(!$circular || !$circular->id) {
                return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            //権限チェック
            switch($circular->circular_status){
                //回覧前
                Case CircularUtils::SAVING_STATUS:
                Case CircularUtils::RETRACTION_STATUS:
                    $circular_check = DB::table("circular as C")
                    // ->join('frm_invoice_data as I', 'I.circular_id', 'C.id')
                    ->where('C.mst_user_id', $user->id)
                    ->where('C.id', $circular->circular_id)->first();
                    break;
                //回覧中
                Case CircularUtils::CIRCULATING_STATUS:
                Case CircularUtils::SEND_BACK_STATUS:
                    //回覧を抽出するサブクエリ
                    $viewingQueryUser = DB::table('circular_user as CU')
                    ->select('CU.circular_id')
                    ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("CU.mst_user_id = ".$user->id)
                    ->groupBy(['CU.circular_id'])
                    ;
                    //閲覧を抽出するサブクエリ
                    $viewingQuerySub = DB::table('viewing_user as VU')
                    ->select('VU.circular_id')
                    ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("VU.mst_user_id = ".$user->id)
                    ->groupBy(['VU.circular_id'])
                    ->union($viewingQueryUser)
                    ->distinct()
                    ;
                    
                    $circular_check = DB::table('circular as C')
                    ->JoinSub($viewingQuerySub, 'V', function ($join) {
                        $join->on('C.id', '=', 'V.circular_id');
                    })
                    ->where('C.id', $circular->circular_id)->first();
                    break;

                //完了
                Case CircularUtils::CIRCULAR_COMPLETED_STATUS:
                Case CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS:
                    //回覧を抽出するサブクエリ
                    $viewingQueryUser = DB::table("circular_user$finishedDate as CU")
                    ->select('CU.circular_id')
                    ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("CU.mst_user_id = ".$user->id)
                    ->groupBy(['CU.circular_id'])
                    ;
                    //閲覧を抽出するサブクエリ
                    $viewingQuerySub = DB::table('viewing_user as VU')
                    ->select('VU.circular_id')
                    ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("VU.mst_user_id = ".$user->id)
                    ->groupBy(['VU.circular_id'])
                    ->union($viewingQueryUser)
                    ->distinct()
                    ;
                    
                    $circular_check = DB::table("circular$finishedDate as C")
                    ->JoinSub($viewingQuerySub, 'V', function ($join) {
                        $join->on('C.id', '=', 'V.circular_id');
                    })
                    ->where('C.id', $circular->circular_id)->first();
                    break;

            }
            if(!$circular_check || !$circular_check->id) {
                return $this->sendError('表示権限がありません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            $frm_invoice_data = DB::table('frm_invoice_data')
            ->select(DB::raw('*,FORMAT(invoice_amt,0) as invoice_amt_comma'))
            ->where('id', $id)
            ->first();

            return $this->detailShow($circular,$frm_invoice_data,$request,$finishedDate);

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * get detail Other
     */
    public function detailShowOther($id,Request $request){
        try {
            $user       = $request->user();

            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = date('Ym', strtotime(date('Ym')." - $finishedDateKey month"));
            }
            $circular = DB::table("circular$finishedDate as C")
            ->join('frm_others_data as O', 'O.circular_id', 'C.id')
            ->where('O.id', $id)->first();
            
            if(!$circular || !$circular->id) {
                return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            //権限チェック
            switch($circular->circular_status){
                //回覧前
                Case CircularUtils::SAVING_STATUS:
                Case CircularUtils::RETRACTION_STATUS:
                    $circular_check = DB::table("circular as C")
                    // ->join('frm_invoice_data as I', 'I.circular_id', 'C.id')
                    ->where('C.mst_user_id', $user->id)
                    ->where('C.id', $circular->circular_id)->first();
                    break;
                //回覧中
                Case CircularUtils::CIRCULATING_STATUS:
                Case CircularUtils::SEND_BACK_STATUS:
                    //回覧を抽出するサブクエリ
                    $viewingQueryUser = DB::table('circular_user as CU')
                    ->select('CU.circular_id')
                    ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("CU.mst_user_id = ".$user->id)
                    ->groupBy(['CU.circular_id'])
                    ;
                    //閲覧を抽出するサブクエリ
                    $viewingQuerySub = DB::table('viewing_user as VU')
                    ->select('VU.circular_id')
                    ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("VU.mst_user_id = ".$user->id)
                    ->groupBy(['VU.circular_id'])
                    ->union($viewingQueryUser)
                    ->distinct()
                    ;
                    
                    $circular_check = DB::table('circular as C')
                    ->JoinSub($viewingQuerySub, 'V', function ($join) {
                        $join->on('C.id', '=', 'V.circular_id');
                    })
                    ->where('C.id', $circular->circular_id)->first();
                    break;

                //完了
                Case CircularUtils::CIRCULAR_COMPLETED_STATUS:
                Case CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS:
                    //回覧を抽出するサブクエリ
                    $viewingQueryUser = DB::table("circular_user$finishedDate as CU")
                    ->select('CU.circular_id')
                    ->whereRaw("CU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("CU.mst_user_id = ".$user->id)
                    ->groupBy(['CU.circular_id'])
                    ;
                    //閲覧を抽出するサブクエリ
                    $viewingQuerySub = DB::table('viewing_user as VU')
                    ->select('VU.circular_id')
                    ->whereRaw("VU.mst_company_id = ".$user->mst_company_id)
                    ->whereRaw("VU.mst_user_id = ".$user->id)
                    ->groupBy(['VU.circular_id'])
                    ->union($viewingQueryUser)
                    ->distinct()
                    ;
                    
                    $circular_check = DB::table("circular$finishedDate as C")
                    ->JoinSub($viewingQuerySub, 'V', function ($join) {
                        $join->on('C.id', '=', 'V.circular_id');
                    })
                    ->where('C.id', $circular->circular_id)->first();
                    break;

            }
            if(!$circular_check || !$circular_check->id) {
                return $this->sendError('表示権限がありません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            $frm_others_data = DB::table('frm_others_data')
            ->where('id', $id)
            ->first();

            return $this->detailShow($circular,$frm_others_data,$request,$finishedDate);

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * get detail
     */
    public function detailShow($circular,$frm_table_data,Request $request,$finishedDate){
        try {
            $user = $request->user();

            $firstDocument = DB::table("circular_document$finishedDate")
                ->where('circular_id', $circular->circular_id)
                ->orderBy('id')->first();

                if ($firstDocument && $firstDocument->confidential_flg
                && $firstDocument->origin_edition_flg == config('app.edition_flg')
                && $firstDocument->origin_env_flg == config('app.server_env')
                && $firstDocument->origin_server_flg == config('app.server_flg')
                && $firstDocument->create_company_id == $user->mst_company_id){
                $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
            }else if ($firstDocument && !$firstDocument->confidential_flg){
                $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
            }else{
                $noPreviewPath =  public_path()."/images/no-preview.png";
                $data = file_get_contents($noPreviewPath);
                $base64 = 'data:image/png;base64,' . base64_encode($data);
                $circular->first_page_data = $base64;
            }

            $userReceives = DB::table("circular_user$finishedDate")
                ->where('circular_id', $circular->circular_id)
                ->orderBy('parent_send_order', 'ASC')
                ->orderBy('child_send_order', 'ASC')
                ->get();

            $hasRequestSendBack = $userReceives->some(function($value) {
                return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;
            });

            $author = null;
            if(count($userReceives)){
                $arrNew = [[]];
                foreach($userReceives as $userReceive){
                    if($userReceive->parent_send_order == 0 OR $user->mst_company_id == $userReceive->mst_company_id)
                        $userReceive->isOutCopany = 0;
                    else $userReceive->isOutCopany = 1;

                    if($userReceive->parent_send_order == 0 AND $userReceive->child_send_order == 0){
                        $author = $userReceive;
                        continue;
                    }
                    $arrNew[$userReceive->parent_send_order][] = $userReceive;

                    if ($circular->edition_flg != config('app.edition_flg')
                        || $circular->env_flg != config('app.server_env')
                        || $circular->server_flg != config('app.server_flg')){
                        Log::debug("Loop User: $userReceive->edition_flg - $userReceive->env_flg - $userReceive->server_flg - $userReceive->mst_company_id - $userReceive->email");
                        Log::debug("Auth User: ".config('app.edition_flg')." - ".config('app.server_env')." - ".config('app.server_flg')." - $user->mst_company_id - $user->email");
                        // Set view_url for circular
                        if ($userReceive->edition_flg === (int)config('app.edition_flg')
                            && $userReceive->env_flg === (int)config('app.server_env')
                            && $userReceive->server_flg === (int)config('app.server_flg')
                            && strtolower(trim($user->email)) == strtolower(trim($userReceive->email))){
                            Log::debug("Set origin_circular_url");
                            $circular->origin_circular_url = $userReceive->origin_circular_url;
                        }
                    }
                }

                if (($circular->edition_flg != config('app.edition_flg') || $circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))
                    && (!isset($circular->origin_circular_url) || !$circular->origin_circular_url)){
                    Log::debug("External circular, check origin_circular_url from viewing user");
                    $viewingUser = DB::table('viewing_user')->where('circular_id', $circular->circular_id)->where('mst_user_id', $user->id)->first();
                    if ($viewingUser){
                        Log::debug("Set origin_circular_url from viewing user");
                        $circular->origin_circular_url = $viewingUser->origin_circular_url;
                    }
                }

                $userReceives = [];
                if($author AND $user->mst_company_id != $author->mst_company_id)
                    $userReceives = [$author];
                foreach($arrNew as $parent_send_order => $items){
                    if (count($items) > 0){
                        $item   = $items[0];
                        $num_done = $num_wait = 0; // default
                        foreach($items as $userReceive){
                            if($user->mst_company_id == $userReceive->mst_company_id ){
                                $userReceives[] = $userReceive;
                            }else{
                                if($userReceive->circular_status == CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS
                                    OR $userReceive->circular_status == CircularUserUtils::APPROVED_WITH_STAMP_STATUS){
                                    $num_done ++;
                                }else if($userReceive->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS OR $userReceive->circular_status == CircularUserUtils::READ_STATUS) {
                                    $num_wait ++;
                                }
                            }
                        }
                        if($parent_send_order != 0 AND $user->mst_company_id != $userReceive->mst_company_id){
                            if($num_done == count($items)) $item->status = 2;
                            else if($num_wait != 0 OR $num_done != 0) $item->status = 1;
                            else $item->status = 0;
                            $userReceives[] = $item;
                        }
                    }
                }
            }

            if ($author){
                $userSend = new \stdClass();
                $userSend->family_name = $author->name;
                $userSend->given_name = '';
            }else{
                $userSend = new \stdClass();
                $userSend->family_name = '';
                $userSend->given_name = '';
            }

            if(!$userReceives){
                Log::error("CircularAPIController@getDetailSend not found userReceives");
            }

            // 閲覧ユーザを取得
            $viewingUser = DB::table('mst_user as u')
                ->select('u.email', DB::raw('CONCAT(u.family_name,\' \',u.given_name) as name'))
                ->join('viewing_user as v', 'v.mst_user_id', '=', 'u.id')
                ->where('v.circular_id', $circular->circular_id)
                ->where('v.mst_company_id', $user->mst_company_id)
                ->get();

            //frm_dataを取得
            $frm_data_array = json_decode($frm_table_data->frm_data,true);
   
            return $this->sendResponse(['frm_data_array' =>$frm_data_array,'frm_table_data' => $frm_table_data, 'circular'=>$circular, 'userSend' => $userSend, 'userReceives' => $userReceives, 'viewingUser'=>$viewingUser, 'mid' => $user->mst_company_id,'hasRequestSendBack'=> $hasRequestSendBack],'回覧取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Export FormIssuanceList to file csv.
     * post /form-issuances/list/export-list
     *
     * @param SearchFormIssuanceListAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function exportFormIssuanceListToCSV(SearchFormIssuanceListAPIRequest $request) {

        $user = $request->user();
        $max_issu_export_count     = config('max_issu_export_count');
        $columnSelect = $request->all();
        $export_file_name = $columnSelect['export_file_name'];
        $other_flg        = $columnSelect['other_flg'];
        $template_id      = $columnSelect['template_id'];
        $output_kind      = $columnSelect['output_kind'];
        $columnSelect     = $columnSelect['export_work_list_columns'];
        //0,5:回覧前 1,4:回覧中 2,3:完了
        $options_status= ['回覧前','回覧中','完了','完了','回覧中','回覧前','','','','削除'];

        try {
            $formIssuanceListExport = [];
            $formIssuanceListExportExcel = [];
            $data = '';
            if (!$other_flg){//その他帳票
                $data = $this->indexReportOther($request);
            }else{//請求書
                $data = $this->indexReport($request);
            }

            if($template_id=='Simple'){//シンプルモード
                if(!$export_file_name){
                    $export_file_name = 'シンプル';
                }
                // Create header format file export
                $headerFormat = '明細ID,明細名';
                if (in_array('customer_name', $columnSelect)) {
                    $headerFormat .= ',取引先';
                }
                if (in_array('invoice_amt', $columnSelect)) {
                    $headerFormat .= ',請求金額';
                }
                if (in_array('trading_date', $columnSelect)) {
                    $headerFormat .= ',売上計上日';
                }
                if (in_array('payment_date', $columnSelect)) {
                    $headerFormat .= ',入金期日';
                }
                if (in_array('invoice_date', $columnSelect)) {
                    $headerFormat .= ',請求日';
                }
                if (in_array('reference_date', $columnSelect)) {//その他で使う
                    $headerFormat .= ',基準日';
                }
                if (in_array('circular_status', $columnSelect)) {
                    $headerFormat .= ',状況';
                }
                if (in_array('update_user', $columnSelect)) {
                    $headerFormat .= ',最終更新者';
                }
                if (in_array('update_at', $columnSelect)) {
                    $headerFormat .= ',最終更新日';
                }
            }
                // 見出し列を確定させる
                $first_flg = true;
                $headerFormat2='';
                $array_headerFormat2=[];
                foreach ($data as $issuanceDetail) {
                    if($first_flg){//最初のレコードはそのまま入れる $headerFormat2にはCSV用、$array_headerFormat2にはExcel用の見出しデータが入る。
                        if (isset($issuanceDetail->frm_data)) {
                            $array_head = json_decode($issuanceDetail->frm_data, true);
                            $first_column_flg = true;
                            foreach ($array_head as $key => $value){
                                if($first_column_flg){
                                    $headerFormat2 .= $this->replace_symbol($key,$output_kind);
                                    $first_column_flg = false;
                                }else{
                                    $headerFormat2 .= ','.$this->replace_symbol($key,$output_kind);
                                }
                                $array_headerFormat2[]=$key;
                            }
                        }
                    }else{//２番目以降のレコードは、同一キーが無かったら入れる
                        $array_head = json_decode($issuanceDetail->frm_data, true);
                        foreach ($array_head as $key => $value){
                            if (!in_array($key, $array_headerFormat2)) {
                                $headerFormat2 .= ','.$this->replace_symbol($key,$output_kind);
                                $array_headerFormat2[]=$key;
                            }
                        }
                    }
                    $first_flg = false;
                }
                // Process data before export
                $first_flg = true;
                foreach ($data as $issuanceDetail) {
                    $issuance = [];
                    if($template_id=='Simple'){//シンプルモード
                            $issuance[] = $issuanceDetail->company_frm_id;
                        $issuance[] = $issuanceDetail->frm_name;
                        if (in_array('customer_name', $columnSelect)) {
                            $issuance[] = $this->replace_symbol($issuanceDetail->customer_name,$output_kind);
                        }
                        if (in_array('invoice_amt', $columnSelect)) {
                            $issuance[] = $issuanceDetail->invoice_amt;
                        }
                        if (in_array('trading_date', $columnSelect)) {
                            if (isset($issuanceDetail->trading_date)) {
                                $date = new DateTime($issuanceDetail->trading_date);
                                $issuance[] = date_format($date, 'Y-m-d');
                            }else{
                                $issuance[] = '';
                            }
                        }
                        if (in_array('reference_date', $columnSelect)) {//その他用のカラム
                            if (isset($issuanceDetail->reference_date)) {
                                $date = new DateTime($issuanceDetail->reference_date);
                                $issuance[] = date_format($date, 'Y-m-d');
                            }else{
                                $issuance[] = '';
                            }
                        }
                        if (in_array('payment_date', $columnSelect)) {
                            if (isset($issuanceDetail->payment_date)) {
                                $date = new DateTime($issuanceDetail->payment_date);
                                $issuance[] = date_format($date, 'Y-m-d');
                            }else{
                                $issuance[] = '';
                            }
                        }
                        if (in_array('invoice_date', $columnSelect)) {
                            if (isset($issuanceDetail->invoice_date)) {
                                $date = new DateTime($issuanceDetail->invoice_date);
                                $issuance[] = date_format($date, 'Y-m-d');
                            }else{
                                $issuance[] = '';
                            }
                        }
                        if (in_array('circular_status', $columnSelect)) {
                            $issuance[] = $options_status[$issuanceDetail->circular_status];
                        }
                        if (in_array('update_user', $columnSelect)) {
                            $issuance[] = $this->replace_symbol($issuanceDetail->update_user,$output_kind);
                        }
                        if (in_array('update_at', $columnSelect)) {
                            if (isset($issuanceDetail->create_at)) {
                                $date = new DateTime($issuanceDetail->update_at);
                                $issuance[] = date_format($date, 'Y-m-d h:m:s');
                            }else{
                                $issuance[] = '';
                            }
                        }
                    }
                    //帳票データ 可変部分
                    $arr_frm_data=[];
                    //空配列を作成
                    $arr_frm_data = array_pad($arr_frm_data, count($array_headerFormat2), '');
                    //空配列に、読み込んだデータを見出しに沿ってセットしていく
                    if (in_array('frm_data', $columnSelect)) {
                        if (isset($issuanceDetail->frm_data)) {
                            $array_frm_data = json_decode($issuanceDetail->frm_data, true);
                            foreach ($array_frm_data as $key => $value){
                                $arr_frm_data[array_search($key, $array_headerFormat2)] = $this->replace_symbol($value,$output_kind);
                            }
                        }
                    }
                    if($first_flg){//見出し行
                        //CSV用
                        if($template_id=='Simple'){
                            array_push($formIssuanceListExport, $headerFormat.','.$headerFormat2);
                        }else{
                            array_push($formIssuanceListExport, $headerFormat2);
                        }
                        //excel用
                        if($template_id=='Simple'){
                                $work_arry_header = [];
                            $work_arry_header = explode(',',$headerFormat);
                            $work_arry_header = array_merge($work_arry_header,$array_headerFormat2);
                            array_push($formIssuanceListExportExcel, $work_arry_header); 
                        }else{
                            array_push($formIssuanceListExportExcel, $array_headerFormat2);
                        }

                    }

                    if($output_kind =='CSV'){//CSVデータ一行分
                        if($template_id=='Simple'){
                            array_push($formIssuanceListExport, implode(',', $issuance).','.implode(',', $arr_frm_data));
                        }else{
                            array_push($formIssuanceListExportExcel, $arr_frm_data);
                        }
                    }else{//Excelデータ一行分
                        if($template_id=='Simple'){
                            array_push($formIssuanceListExportExcel, array_merge($issuance,$arr_frm_data));
                        }else{
                            array_push($formIssuanceListExportExcel, $arr_frm_data);
                        }
                    }
                    $first_flg = false;
                }
            // }
            if($output_kind =='CSV'){
                if($template_id=='Simple'){//シンプルモード
                    $result['csv_excel_data'] = $formIssuanceListExport;
                }else{
                    $result['csv_excel_data'] = $this->editExcelExpTmp($request,$formIssuanceListExportExcel,$output_kind);
                }
            }else{
                if($template_id=='Simple'){//シンプルモード
                    $result['csv_excel_data'] = $this->editExcel($request,$formIssuanceListExportExcel);
                }else{
                    $result['csv_excel_data'] = $this->editExcelExpTmp($request,$formIssuanceListExportExcel,$output_kind);
                }
                
            }
            // $timeDownload = Carbon::now()->format('YmdHis');
            $result['file_name'] = $export_file_name ;
            return $this->sendResponse($result, '明細データの取得処理に成功しました。');
        } catch (Exception $ex) {
            Log::error('WorkListAPIController@exportHrWorkListToCSV:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * replace " ⇒ ""
     * Enclose by "
     * ex : A,B"CDE　⇒ "A,B""CDE"
     * 
     */
    private function replace_symbol($base_string,$output_kind) {
        if($output_kind =='Excel'){
            return $base_string;
        }
        //CSVのみ置換する
        $after_string = $base_string;

        $return_flg = 0;
        $match_num = preg_match('/\n|\r|\r\n/', $after_string ); //改行が含まれるかチェック
        if($match_num){
            $return_flg = 1;
        }
        if(strpos($after_string,'"') !== false){ // "を含んでいる場合
            $after_string = str_replace('"','""',$after_string) ;
        }
        if(strpos($after_string,'"') !== false || strpos($after_string,',') !== false || $return_flg ==1){ // "or,を含んでいる場合
            $after_string = '"'.$after_string.'"';
        }

        return $after_string;
    }

    /**
     * edit the excel files
     */
    public function editExcel(SearchFormIssuanceListAPIRequest $request,$formIssuanceListExport) {

        try {
                $user = $request->user();

                Log::info('ExportExcelファイル編集開始');

                $spread = new Spread();
                $sheet = $spread -> getActivesheet();
                $sheet ->fromArray($formIssuanceListExport,NULL,"A1");
                $lastrow = $sheet->getHighestRow(); //最大行

                //全カラムに対して幅自動設定を有効にする
                foreach ($sheet->getColumnIterator() as $column) {
                    $dim = $sheet -> getColumnDimension($column->getColumnIndex()); //対象列のオブジェクト作成　
                    $dim -> setAutoSize(true); //リサイズを有効にする　
                    $sheet -> calculateColumnWidths(); //列幅を数える　
                    $dim -> setAutoSize(false); //リサイズの初期化

                    //列内の最大幅のセルを探索
                    $max_value = 0;
                    for($row=1; $row<=$lastrow; $row++){
                        $value = $sheet->getCell($column->getColumnIndex().$row)->getValue();
                        //strlen( mb_convert_encoding($max_value, 'SJIS', 'UTF-8')で、全角文字を2バイトとしてバイト数を計算
                        if (strlen( mb_convert_encoding($max_value, 'SJIS', 'UTF-8') ) < strlen( mb_convert_encoding($value, 'SJIS', 'UTF-8') )){
                            $max_value = $value;
                        }
                    }

                    $col_width = $dim -> getWidth(); //列幅を取得　
                    // $value = $sheet -> getCell("A1") -> getValue();
                    $margin = (strlen( mb_convert_encoding($max_value, 'SJIS', 'UTF-8') ) > mb_strlen($max_value) )? 1.7:0.98; //全角か半角かで倍率を任意で変更
                    $dim -> setWidth($col_width * $margin );
                }

                $writer = new XlsxWriter($spread);
                $path = 'formlist' . explode(".", (microtime(true) . ""))[0] . '_' .$user->id . '.xlsx';
                $writer->save(storage_path('app/public') . '/' . $path);

                $result = \base64_encode(\file_get_contents(storage_path('app/public') . '/' . $path));
                Storage::disk('public')->delete($path);

                Log::info(storage_path());

                Log::info('ExportExcelファイル送信');

                return $result;
        } catch (\Exception $ex) {
            Log::error('FormIssuanceListAPIController@editExcel:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError("Excelの作成に失敗しました。", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * edit the excel files
     */
    public function editExcelExpTmp(SearchFormIssuanceListAPIRequest $request,$formIssuanceListExport,$output_kind) {

        try {
                $user = $request->user();
                $template_id      = $request['template_id'];

                Log::info('ExportExcelファイル編集開始');
                $result = $this->getExpTemplate($template_id,$request);
                $filePath = $result[0];
                $fileNameS3_out = $result[1];

                $reader = new XlsxReader();
                $reader->setReadDataOnly(false);
                $spreadsheet = $reader->load($filePath);

                Storage::disk('local')->delete($fileNameS3_out);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

                $style_test='';
                $col_ix =0;
                $arr_style =array(); //書式を格納する
                $arr_value =array(); //テンプレートセルの値を格納する
                $arr_value_head =array(); //テンプレートセルの見出しの値を格納する CSVのみ使用する
                $sheet = $spreadsheet -> getActiveSheet(); //処理したいシートを取得
                foreach( $sheet -> getRowIterator() as $row){
                        $cells = $row -> getCellIterator();

                        $tmp_col_ix = 0;
                        foreach ($sheet->getColumnIterator() as $column) {
                            $addr = $sheet->getCell($column->getColumnIndex() . $row->getRowIndex())->getCoordinate();

                            $pattern   = '/^\$\{.*\}\z/'; // フォーマットチェック ${}であるか
                            $match_num = preg_match($pattern, $sheet->getCell($addr)->getValue() );
                            if($match_num){
                                $heading = explode(':',substr($sheet->getCell($addr)->getValue(), 2, -1));
                                $heading_value = '${'.$heading[0].'}'; //プレースホルダに対して該当データなしの場合のために、削除できる（もとがプレースホルダだと分かる）ように印をつける
                            }else{
                                $heading = explode(':',$sheet->getCell($addr)->getValue() );
                                $heading_value = $heading[0];
                            }
                            if(count($heading) > 1 ){ //書式がテンプレートに設定されている場合
                                $heading_style = $heading[1];
                            }else{
                                $heading_style = '';
                            }
                            if($row->getRowIndex() == 1 ){//見出し行
                                $arr_value_head[0][$tmp_col_ix] = $this->replace_symbol($sheet->getCell($addr)->getValue(),$output_kind);
                            }
                            if($row->getRowIndex() > 1 ){ //明細行
                                $arr_value[$row->getRowIndex() - 2][$tmp_col_ix] = $this->replace_symbol($heading_value,$output_kind); //テンプレートの値を保持　「:」の左辺
                                $arr_style[$row->getRowIndex() - 2][$tmp_col_ix] = $heading_style; //テンプレートの値を保持　「:」の右辺
                            }
                    
                            $tmp_col_ix++;
                        }
                }

                $max_row = $sheet->getHighestRow(); //最終行（最下段）の取得
                $max_col = $sheet->getHighestColumn(); //最終列（右端）の取得
                $maxCellAddress = $max_col.$max_row; //最終セルのアドレスを格納する変数
                $range = $sheet->rangeToArray("A2:{$maxCellAddress}"); //指定の位置から最終セルまで取得する
                $tmp_row_count = $max_row - 1; //テンプレートの行数
                $ins_max_row = 2; //挿入する位置
                
                $sheetData_replace_csv    = array();
                //コピーしたテンプレート←データ
                $row_ix =0;
                $col_ix =0;
                $search_row_ix = 0;
                foreach ($formIssuanceListExport as $dataRow) {//入力データ行
                    if($row_ix ==0){//見出し
                        $sheetData_replace_csv[] = array_merge($sheetData_replace_csv,$arr_value_head[$row_ix]);
                    }else{
                        $sheetData_replace        = $arr_value; //置き換えワークエリアを元のテンプレートで初期化
                        $sheetData_replace_target = array();    //変換した座標が分かるようにしておく
                        $sheetData_replace_style  = array();
                        $col_ix =0;
                        foreach ($dataRow as $dataCol) {//入力データ列
                            $search_row_ix =0;
                            if($dataCol){
                                $template_row_ix =0;
                                foreach($arr_value as $row){ //テンプレート行
                                    $template_col_ix = 0;
                                    foreach($row as $template_col){ //テンプレート列 を探す
                                        if(($template_col == '#{rownum}') || ($template_col == '行番号')){
                                            $sheetData_replace[$template_row_ix][$template_col_ix] = $row_ix;  
                                        }
                                        elseif($this->replaceSymbol($formIssuanceListExport[0][$col_ix]) == $this->replaceSymbol($template_col)){//入力データの見出しとテンプレート項目が一致するか
                                            $sheetData_replace[$template_row_ix][$template_col_ix]          = $dataCol;  //$dataColには表示すべきデータ値が入っている
                                            $sheetData_replace_target[$template_row_ix][$template_col_ix]   = $dataCol;
                                        }
                                        ;
                                    $template_col_ix ++;
                                    }
                                $template_row_ix++;
                                }
                            }
                            $col_ix ++;
                        }
                        $ins_max_row += $tmp_row_count;

                        //対応データが存在しないテンプレートを空にする処理
                        $clear_row_ix = 0;
                        foreach($sheetData_replace as $row){
                            $clear_col_ix = 0;
                            foreach($row as $col){
                                $pattern   = '/^\$\{.*\}\z/'; // フォーマットチェック ${}であるか
                                $match_num = preg_match($pattern, $col );
                                if($match_num){
                                    $sheetData_replace[$clear_row_ix][$clear_col_ix] = ''; //  ${}形式のセルはクリアする
                                }
                                $clear_col_ix ++;
                            }
                            $clear_row_ix++;
                        }
                        $sheet -> fromArray($sheetData_replace,NULL,"A".$ins_max_row); //指定の位置に貼り付ける
                        $sheetData_replace_csv = array_merge($sheetData_replace_csv,$sheetData_replace);
                    }
                $row_ix++;
                }

                //スタイル設定処理
                $style_row_ix = 0;
                foreach($arr_style as $rowStyle){ //スタイル行
                    $char = 'A';
                    $style_col_ix = 0;
                    foreach($rowStyle as $rowCol){ //スタイル列
                            $work_style_row_ix = $style_row_ix + 2;
                            $style_value = $sheet->getstyle($char.$work_style_row_ix); // A2セルから順に参照していく
                            if(count($formIssuanceListExport) > 0){
                                $max = count($formIssuanceListExport) -1;
                                for($i = 0; $i < $max; $i++){
                                    $work_ix = 2 + $tmp_row_count + $style_row_ix + ($i * $tmp_row_count);//tmp_row_countはテンプレートの行数
                                    $sheet->duplicateStyle($style_value,$char.$work_ix);
                                }
                            }

                            switch($rowCol){
                                case 'num':
                                case 'number':
                                case 'n':
                                    if(count($formIssuanceListExport) > 0){
                                        $max = count($formIssuanceListExport) -1;
                                        for($i = 0; $i < $max; $i++){
                                            $work_ix = 2 + $tmp_row_count + $style_row_ix + ($i * $tmp_row_count);
                                            $num = $sheet->getCellByColumnAndRow($style_col_ix + 1 , $work_ix)->getValue();
                                            if(is_numeric($num)){
                                                $sheet->setCellValueExplicitByColumnAndRow($style_col_ix + 1 , $work_ix, $num,XlsxDataType::TYPE_NUMERIC);
                                            }else{
                                                $sheet->setCellValueByColumnAndRow($style_col_ix + 1 , $work_ix, ''); //数値でない場合、空にする。
                                            }
                                        }
                                    }
                                break;
                                case 'date':
                                case 'd':
                                    if(count($formIssuanceListExport) > 0){
                                        $max = count($formIssuanceListExport) -1;
                                        for($i = 0; $i < $max; $i++){
                                            $work_ix = 2 + $tmp_row_count + $style_row_ix + ($i * $tmp_row_count);
                                                $excelDateValue = XlsxDate::PHPToExcel($sheet->getCellByColumnAndRow($style_col_ix + 1 , $work_ix)->getValue());
                                                if($excelDateValue){
                                                    //セルにエクセルタイムスタンプ値設定
                                                    $sheet->setCellValueByColumnAndRow($style_col_ix + 1 , $work_ix, $excelDateValue);
                                                    // セルの数値フォーマットに日付時刻の設定(yyyy-mm-dd)
                                                    $sheet->getStyleByColumnAndRow($style_col_ix + 1 , $work_ix)->getNumberFormat()->setFormatCode(XlsxNumberFormat::FORMAT_DATE_YYYYMMDD);
                                                }
                                        }
                                    }
                                    break;
                                case 'datetime':
                                case 'dt':
                                    if(count($formIssuanceListExport) > 0){
                                        $max = count($formIssuanceListExport) -1;
                                        for($i = 0; $i < $max; $i++){
                                            $work_ix = 2 + $tmp_row_count + $style_row_ix + ($i * $tmp_row_count);
                                                $excelDateValue = XlsxDate::PHPToExcel($sheet->getCellByColumnAndRow($style_col_ix + 1 , $work_ix)->getValue());
                                                if($excelDateValue){
                                                    //セルにエクセルタイムスタンプ値設定
                                                    $sheet->setCellValueByColumnAndRow($style_col_ix + 1 , $work_ix, $excelDateValue);
                                                    // セルの数値フォーマットに日付時刻の設定(yyyy-mm-dd)
                                                    $sheet->getStyleByColumnAndRow($style_col_ix + 1 , $work_ix)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
                                                }
                                        }
                                    }
                                    break;
                                default:
                                    break;
                            }
                        $style_col_ix++;
                        $char++;
                    }
                    $style_row_ix++;
                }
                $sheet->removeRow(2, $tmp_row_count); //テンプレートコピー元を削除

                if($output_kind =='CSV'){//CSVデータ一行分
                    $max_row = $sheet->getHighestRow(); //最終行（最下段）の取得
                    $max_col = $sheet->getHighestColumn(); //最終列（右端）の取得
                    $maxCellAddress = $max_col.$max_row; //最終セルのアドレスを格納する変数
                    $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
                    $result = $sheetData_replace_csv;
                }else{
                    $writer = new XlsxWriter($spreadsheet);
                    $path = 'formlist' . explode(".", (microtime(true) . ""))[0] . '_' .$user->id . '.xlsx';
                    $writer->save(storage_path('app/public') . '/' . $path);
                    $result = \base64_encode(\file_get_contents(storage_path('app/public') . '/' . $path));
                    Storage::disk('public')->delete($path);
                    }

                Log::info(public_path());

                Log::info('ExportExcelファイル送信');

                return $result;
        } catch (\Exception $ex) {
            Log::error('FormIssuanceListAPIController@editExcel:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError("Excelの作成に失敗しました。", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    //プレースホルダ置換 ${}を取り除く
    private function replaceSymbol($targetString){

        $pattern   = '/^\$\{.*\}\z/'; // フォーマットチェック ${}であるか
        $match_num = preg_match($pattern, $targetString);
        if($match_num){
            return substr($targetString, 2, -1);
        }
        return $targetString;
    }
    
    //プレースホルダ置換 ${値}を抽出する
    private function replaceSymbol2($targetString){

        $pattern   = '/^\$\{.*\}\z/'; // フォーマットチェック ${}であるか
        $match_num = preg_match($pattern, $targetString);
        if($match_num){
            return substr($targetString, 2, -1);
        }
        return $targetString;
    }

    public function getExpTemplate($templateId, SearchFormIssuanceListAPIRequest $request) {
        $user = $request->user();
        try {
            $file = DB::table('frm_exp_template')
                ->where('id', $templateId)
                ->first();

            $result = $this->getContentTemplateBase64($file, $user, true);

            return $result;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getContentTemplateBase64($template, $user, $isExpTemplate){
        //ファイルの存在を確認してS3から取得をする
        $tplFolder = config('app.s3_storage_form_template_folder');
        $path = $this->rootDirectory.'/'.$tplFolder.'/'.$this->templateDirectory.'/';

        $templateType = $this->expTemplateTypeDirectory;
        $fileNameS3 = $this->getS3FileName($this->expTemplateFileNamePrefix,$template->id,$template->file_name);//取得するときのファイル名
        $fileNameS3_out = $this->getS3FileName($this->expTemplateOutFileNamePrefix,$template->id,$template->file_name).'_'.$user->id.'_'.explode(".", (microtime(true) . ""))[0];//出力する時のファイル名

        $path = $path.$user->mst_company_id.'/'.$templateType;
        if ( Storage::disk($this->remoteStorage)->exists($path)){
            $relative_path = $path.'/'.$fileNameS3;
            $getFile = Storage::disk($this->remoteStorage)->get($relative_path);
            $isStore = Storage::disk('local')->put($fileNameS3_out, $getFile);
            Log::info('expテンプレート取得: '. $relative_path);
        }

        $filePath = storage_path('app/' . $fileNameS3_out);
        // $fileEncode = \base64_encode(\file_get_contents($filePath.$user->id));
        
        $result[0] = $filePath;
        $result[1] = $fileNameS3_out;
        // 削除ロジックはExcel編集後に移動
        // Storage::disk('local')->delete($fileNameS3);
        // return $fileEncode;
        return $result;
    }
    private function getS3FileName($file_name_directory, $template_id, $file_name){
        $split_name = explode(".", $file_name);
        $fileName = $file_name_directory.$template_id . '.' . end($split_name);
        return $fileName;
    }
}
