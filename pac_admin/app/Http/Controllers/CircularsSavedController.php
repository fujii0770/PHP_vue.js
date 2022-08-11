<?php

namespace App\Http\Controllers;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\DepartmentUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\Department;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use Session;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;
use App\Http\Utils\DownloadRequestUtils;

class CircularsSavedController extends AdminController
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
        if(!$user->can(PermissionUtils::PERMISSION_CIRCULARS_SAVED_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $action     = $request->get('action','');

        $page       = $request->get('page', 1);
        $limit      = $request->get('limit', 20);
        $orderBy    = $request->get('orderBy', "C.final_updated_date");
        $orderDir   = $request->get('orderDir', "DESC");

        $where = [ ];
        $where_arg = [];

        $strSearch = $request->get('search');
        $arrStatus = null !== ($request->get('status')) ? [$request->get('status')]:array_keys(\App\Http\Utils\AppUtils::CIRCULAR_SAVED_STATUS);
        $intCircularID = $request->get('document_id');

        // filter document_id
        if($request->get('document_id')){
            $where[]        = 'C.id = ?';
            $where_arg[]    = $request->get('document_id');
        }

        // filter update_at
        if($request->get('update_fromdate')){
            $where[]        = 'DATE(C.final_updated_date) >= ?';
            $where_arg[]    = $request->get('update_fromdate');
        }
        if($request->get('update_todate')){
            $where[]        = 'DATE(C.final_updated_date) <= ?';
            $where_arg[]    = $request->get('update_todate');
        }

        if($request->isMethod('post') && $action){
            $cids = $request->get('cids',[]);
            if($action == "delete"){
                $this->deletes($cids, $user);
            }
        }

        // PAC_5-2853 S
        $company = DB::table('mst_company')
            ->where('id', $user->mst_company_id)
            ->first();
        // PAC_5-2853 E

        $query_sub = DB::table('circular as C')
                    ->join('mst_user as MU', function($join) use($user){
                        $join->on('MU.id', 'C.mst_user_id');
                        $join->where('MU.mst_company_id', $user->mst_company_id);
                    })
                    ->join('circular_document as D', function($join) use ($user){
                        $join->on('C.id', '=', 'D.circular_id');
                        $join->on(function($condition) use ($user){
                            $condition->on('confidential_flg', DB::raw('0'));
                            $condition->orOn(function($condition1) use ($user){
                                $condition1->on('confidential_flg', DB::raw('1'));
                                $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                            });
                        });
                        $join->on(function($condition) use ($user){
                            $condition->on('origin_document_id', DB::raw('0'));
                            $condition->orOn('D.parent_send_order', DB::raw('0'));
                        });
                    })
                    ->whereIn("C.circular_status",$arrStatus)
                    ->selectRaw("
                        (
                            SELECT
                                GROUP_CONCAT( CD.file_name ORDER BY CD.id ASC SEPARATOR ', ' ) 
                            FROM
                                circular_document AS CD 
                            WHERE
                                CD.circular_id = C.id 
                        ) AS file_names,
                        ( 
                            SELECT title FROM circular_user AS CU WHERE CU.circular_id = C.id AND CU.parent_send_order = 0 AND child_send_order = 0 
                        ) AS title,
                        C.id,CONCAT(MU.family_name, MU.given_name) user_name
                    ")
                    ->groupBy(['C.id', 'title']);

        if($strSearch){
            $query_sub->havingRaw("title LIKE ? OR file_names LIKE ? OR user_name like ?",["%".$strSearch."%","%".$strSearch."%","%".$strSearch."%"]);
        }
        if($intCircularID){
            $query_sub->where("C.id",$intCircularID);
        }

        $data_query = DB::table('circular as C')
            ->joinSub($query_sub, 'D', function ($join) {
                $join->on('C.id', '=', 'D.id');
            })
            ->join('mst_user as A', 'C.mst_user_id', 'A.id')
            ->select(DB::raw('C.id, C.applied_date, C.final_updated_date, C.circular_status, D.file_names, A.email user_email,A.family_name, A.given_name,
                CONCAT(A.family_name, A.given_name) user_name, D.title'))
            ->where('A.mst_company_id', $user->mst_company_id)
            ->whereIn("C.circular_status",$arrStatus);
        if($where){
            $data_query->whereRaw(implode(" AND ", $where), $where_arg);
        }

        if($request->get('department')){
            // PAC_5-2098 Start
            $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;
            $data_query->join('mst_user_info as UI', 'A.id', 'UI.mst_user_id');
            if ($multiple_department_position_flg === 1) {
                $data_query->where(function($query) use ($request) {
                        $query->orWhere('UI.mst_department_id', $request->get('department'))
                            ->orWhere('UI.mst_department_id_1', $request->get('department'))
                            ->orWhere('UI.mst_department_id_2', $request->get('department'));
                    });
            } else {
                $data_query->where('UI.mst_department_id', $request->get('department'));
            }
        }

        // PAC_5-2394 END

        $data_query = $data_query->orderBy($orderBy, $orderDir);

        $itemsCircular = collect([]);

        if($action != ""){
            $itemsCircular = $data_query->paginate($limit)->appends(request()->except('_token'));
            if($action == "export")
                return $this->render('Circulars.csv');
        }

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);
        $this->assign('company', $company);// PAC_5-2853
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir)=="asc"?"desc":"asc");
        $this->assign('itemsCircular', $itemsCircular);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->setMetaTitle('保存文書一覧');
        return $this->render('Circulars.saved');
    }
    /**
     * PAC_5-998
     * 保存文書一覧情報を取得
     * @param Request $request
     * @return mixed
     */
    public function exports(Request $request){
        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_CIRCULARS_SAVED_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $cids = $request->get('cids',[]);
        //クエリファイル情報
        $query_sub  = DB::table('circular as C')
            ->leftjoin('circular_user as U', function($join){
                $join->on('C.id', 'U.circular_id');
                $join->on('U.parent_send_order', DB::raw('0'));
                $join->on('U.child_send_order', DB::raw('0'));
            })
            ->join('circular_document as D', function($join) use ($user){
                $join->on('C.id', '=', 'D.circular_id');
                $join->on(function($condition) use ($user){
                    $condition->on('confidential_flg', DB::raw('0'));
                    $condition->orOn(function($condition1) use ($user){
                        $condition1->on('confidential_flg', DB::raw('1'));
                        $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                    });
                });
                $join->on(function($condition) use ($user){
                    $condition->on('origin_document_id', DB::raw('0'));
                    $condition->orOn('D.parent_send_order', DB::raw('0'));
                });
            })
            ->whereIn('C.id', $cids)
            ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
            ->groupBy(['C.id', 'U.title']);

        $circulars = DB::table('circular as C')
            ->leftJoinSub($query_sub, 'D', function ($join) {
                $join->on('C.id', '=', 'D.id');
            })
            ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
            ->select(DB::raw('C.id, C.create_at, C.final_updated_date, C.circular_status, D.file_names, A.email user_email,A.family_name, A.given_name,
                     CONCAT(A.family_name, A.given_name) user_name, D.title'))
            ->where('A.mst_company_id', $user->mst_company_id)
            ->whereIn('C.id', $cids)
            ->get()->keyBy('id');

        $cids       =   $circulars->keys();

        foreach($circulars as $circular){
            $filenames[]        = $circular->file_names;
            $subjects[]         = $circular->title;
            $creator_emails[]   = $circular->user_email;
            $creator_names[]    = $circular->family_name.$circular->given_name;
            $circular_statuss[] = isset(AppUtils::CIRCULAR_STATUS[$circular->circular_status])?AppUtils::CIRCULAR_STATUS[$circular->circular_status]:"";
        }

        Session::flash('file_names', $filenames);
        Session::flash('subject', $subjects);
        Session::flash('creator_email', $creator_emails);
        Session::flash('creator_name', $creator_names);
        Session::flash('circular_status', $circular_statuss);

        $circular_docs  =   DB::table('circular_document')
                    ->whereIn('circular_id', $cids)
                    ->select('id','circular_id','file_name')
                    ->get()->keyBy('id');

        $document_datas = DB::table('document_data')
                ->whereIn('circular_document_id', $circular_docs->keys())
                ->select('circular_document_id','file_data')
                ->get();

        if(count($document_datas) == 1){
            $fileName = $circular_docs[$document_datas[0]->circular_document_id]->file_name;
            return response()->json(['status' => true, 'fileName' => $fileName,
                'file_data' => AppUtils::decrypt( $document_datas[0]->file_data),
                'message' => ['文書ダウンロード処理に成功しました。']]);
        }elseif(count($document_datas) > 1){

            foreach($document_datas as $document_data){
                $document_id = $document_data->circular_document_id;
                if(!isset($circular_docs[$document_id])) continue;
                $circular_document = $circular_docs[$document_id];

                $circular_id = $circular_document->circular_id;
                if(!isset($circulars[$circular_id]->docs)) $circulars[$circular_id]->docs = [];

                $circulars[$circular_id]->docs[] = ['fileName' => $circular_document->file_name,
                            'data' => AppUtils::decrypt($document_data->file_data)];
            }

            $fileName = "download-circular-" . time() . ".zip";
            $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) . ".zip";

            $zip = new \ZipArchive();
            $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            //PAC_5-998BEGIN
            $fileList = array();
            //PAC_5-998END
            foreach($circulars as $circular){
                if(isset($circular->docs)){
                    //PAC_5-998BEGIN
                    //文書が格納されるフォルダ名は「件名_申請日」（件名がなければファイル名）
                    //申請日は数字のみで西暦から秒まで。コンマ秒はなし。
                    //日付形式の変更
                    $time = date("YmdHis", strtotime($circular->final_updated_date));
                    //名前はありますか
                    if (!trim($circular->title)) {
                        //ファイル名の生成
                        $fileNameList = explode(',', $circular->file_names);
                        $subjectName = $fileNameList[0] . '_' . $time;
                    } else {
                        //ファイル名の生成
                        $subjectName = $circular->title . '_' . $time;
                    }
                    //ファイル名が繰り返されていますか , 繰り返されるときにサフィックスを追加する
                    if (array_key_exists($subjectName, $fileList)) {
                        $suffix = $fileList[$subjectName] + 1;
                        $fileList[$subjectName] = $suffix;
                        $subjectName = $subjectName . '_' . $suffix;
                    } else {
                        $fileList[$subjectName] = 1;
                    }
                    $zip->addEmptyDir($subjectName);
                    //PAC_5-998END
                    $countFilename = [];
                    foreach($circular->docs as $doc){

                        $filename = mb_substr($doc['fileName'], mb_strrpos($doc['fileName'],'/'));
                        $filename = mb_substr($filename, 0, mb_strrpos($doc['fileName'],'.'));
                        if(key_exists($filename, $countFilename)) {
                            $countFilename[$filename]++;
                            $filename = $filename.' ('.$countFilename[$filename].') ';
                        } else {
                            $countFilename[$filename] = 0;
                        }
                        $zip->addFromString ($subjectName.'/'.$filename.'.pdf', base64_decode($doc['data']));
                    }
                }
            }
            $zip->close();
            return response()->json(['status' => true, 'fileName' => $fileName,
                'file_data' => \base64_encode(\file_get_contents($path)),
                'message' => ['文書ダウンロード処理に成功しました。']]);
        }else{
            return response()->json(['status' => false,
                'message' => ['送信文書のダウンロード処理に失敗しました。']]);
            return $this->sendError("送信文書のダウンロード処理に失敗しました。");
        }

    }

    public function deletes($cids, $user){
        if(!$user->can(PermissionUtils::PERMISSION_CIRCULARS_SAVED_DELETE)
            AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
        ){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        $query_sub  = DB::table('circular as C')
            ->join('circular_user as U', function($join){
                $join->on('C.id', 'U.circular_id');
                $join->on('U.parent_send_order', DB::raw('0'));
                $join->on('U.child_send_order', DB::raw('0'));
            })
            ->join('circular_document as D', function($join) use ($user){
                $join->on('C.id', '=', 'D.circular_id');
                $join->on(function($condition) use ($user){
                    $condition->on('confidential_flg', DB::raw('0'));
                    $condition->orOn(function($condition1) use ($user){
                        $condition1->on('confidential_flg', DB::raw('1'));
                        $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                    });
                });
                $join->on(function($condition) use ($user){
                    $condition->on('origin_document_id', DB::raw('0'));
                    $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                });
            })
            ->whereIn('C.id', $cids)
            ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
            ->groupBy(['C.id', 'U.title']);

        $circulars = DB::table('circular as C')
            ->leftJoinSub($query_sub, 'D', function ($join) {
                $join->on('C.id', '=', 'D.id');
            })
            ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
            ->select(DB::raw('C.id, C.create_at, C.final_updated_date, C.circular_status, D.file_names, A.email user_email,A.family_name, A.given_name,
                    CONCAT(A.family_name, A.given_name) user_name, D.title'))
            ->where('A.mst_company_id', $user->mst_company_id)
            ->whereIn('C.id', $cids)
            ->get();

        $cids       =   $circulars->pluck('id')->toArray();
        $filenames          = [];
        $subjects           = [];
        $creator_emails     = [];
        $creator_names      = [];
        $circular_statuss   = [];
        foreach($circulars as $circular){
            $filenames[]        = $circular->file_names;
            $subjects[]         = $circular->title;
            $creator_emails[]   = $circular->user_email;
            $creator_names[]    = $circular->family_name.$circular->given_name;
            $circular_statuss[] = isset(AppUtils::CIRCULAR_STATUS[$circular->circular_status])?AppUtils::CIRCULAR_STATUS[$circular->circular_status]:"";
        }

        Session::flash('file_names', $filenames);
        Session::flash('subject', $subjects);
        Session::flash('creator_email', $creator_emails);
        Session::flash('creator_name', $creator_names);
        Session::flash('circular_status', $circular_statuss);
        Session::flash('log_info', \App\Http\Utils\OperationsHistoryUtils::LOG_INFO['CircularsSaved']['deletes']);

        DB::beginTransaction();
        try{
            $listDocument = DB::table('circular_document')->whereIn('circular_id', $cids)->get();
            $circular_document_id = $listDocument->pluck('id')->toArray();
            if(count($listDocument)){
                DB::table('text_info')->whereIn('circular_document_id', $circular_document_id)->delete();
                DB::table('document_data')->whereIn('circular_document_id', $circular_document_id)->delete();
            }
            DB::table('circular_document')->whereIn('circular_id', $cids)->delete();
            DB::table('circular_user')->whereIn('circular_id', $cids)->delete();
            DB::table('viewing_user')->whereIn('circular_id', $cids)->delete();
            DB::table('guest_user')->whereIn('circular_id', $cids)->delete();
            CircularAttachmentUtils::deleteAbsoluteAttachments($cids);//PAC_5-1398 回覧中のすべての添付ファイルを削除します。
            DB::table('circular')->whereIn('id', $cids)->delete();
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::warning($ex->getMessage().$ex->getTraceAsString());
            $this->raiseWarning(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        $this->raiseSuccess(__('message.success.delete_circular'));

        foreach ($circulars as $circular) {
            $data = [
                'deleteTime' => date("Y/m/d H:i"),
                'title' => $circular->title,
                'fileName' => $circular->file_names,
            ];

            //利用者:保存文書削除通知
            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $circular->user_email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['SAVED_CIRCULAR_DELETE_ALERT']['CODE'],
                // パラメータ
                json_encode($data,JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_USER,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendMailDeleteCircular.subject'),
                // メールボディ
                trans('mail.SendMailDeleteCircular.body', $data)
            );
        }
    }

    /**
     * ダウンロード予約処理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserve(Request $request){
        $user       = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_CIRCULARS_SAVED_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $reqFileName = $request->get('fileName', '');
        $cids = $request->get('cids', []);

        return DownloadRequestUtils::reserveDownload($cids,$reqFileName, '');
    }
}
