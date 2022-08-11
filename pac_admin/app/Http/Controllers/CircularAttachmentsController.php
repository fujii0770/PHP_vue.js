<?php

namespace App\Http\Controllers;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CircularAttachmentsController extends AdminController
{


    /**
     * CircularAttachmentsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * すべての添付ファイル情報を表示します。
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){

        $user = $request->user();
        if(!$user->can(PermissionUtils::PERMISSION_ATTACHMENTS_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        $action   = $request->get('action','');
        $limit    = $request->get('limit',10);
        $orderBy  = $request->get('orderBy',"A.create_at");
        $orderDir = $request->get('orderDir','DESC');

        $where = [ ];
        $where_arg = [];

        if($request->isMethod('post') && $action){
            $aids = $request->get('cids',[]);
            if($action == "delete"){
                $this->deletes($aids, $user);
            }
        }

        if ($request->get('search')){
            $w = [];
            $w[] = 'A.file_name like ?';
            $w[] = 'A.title like ?';
            $w[] = 'A.name like ?';

            $where[] = '('.implode(' OR ',$w).')';
            $where_arg[] = '%'.$request->get('search').'%';
            $where_arg[] = '%'.$request->get('search').'%';
            $where_arg[] = '%'.$request->get('search').'%';
        }

        //attachment upload time
        if ($request->get('create_fromdate')){
            $where[] = 'DATE(A.create_at) >= ?';
            $where_arg[] = $request->get('create_fromdate');
        }
        if ($request->get('create_todate')){
            $where[] = 'DATE(A.create_at) <= ?';
            $where_arg[] = $request->get('create_todate');
        }

        $mst_company_id = $user->mst_company_id;
        $edition_flg = config('app.pac_contract_app');
        $env_flg = config('app.pac_app_env');
        $server_flg = config('app.pac_contract_server');

        $data_query = DB::table('circular_attachment as A')
            ->selectRaw('A.id,A.file_name,A.title,A.create_at,A.status,A.create_user,A.name')
            ->join('mst_user as U','U.id','A.apply_user_id')
            ->where('U.mst_company_id',$mst_company_id)
            ->where(function ($query) use($mst_company_id,$edition_flg,$env_flg,$server_flg){
                $query->where('A.confidential_flg',DB::raw(CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_FALSE));
                $query->orWhere(function ($query1) use($mst_company_id,$edition_flg,$env_flg,$server_flg){
                    $query1->where('A.confidential_flg',DB::raw(CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_TRUE))
                        ->where('A.create_company_id',DB::raw($mst_company_id))
                        ->where('A.edition_flg',DB::raw($edition_flg))
                        ->where('A.env_flg',DB::raw($env_flg))
                        ->where('A.server_flg',DB::raw($server_flg));
                });
            })
            ->where('A.status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS);

        if ($where){
            $data_query->whereRaw(implode(' AND ',$where),$where_arg);
        }

        if ($request->get('department')){
            // PAC_5-2098 Start
            $multiple_department_position_flg = DB::table('mst_company')->where('id', $mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;
            if ($multiple_department_position_flg === 1) {
                $data_query->leftJoin('mst_user_info as UI','A.apply_user_id','UI.mst_user_id')
                    // PAC_5-1599 追加部署と役職 Start
                    ->where(function($query) use ($request) {
                        $query->orWhere('UI.mst_department_id', $request->get('department'))
                            ->orWhere('UI.mst_department_id_1', $request->get('department'))
                            ->orWhere('UI.mst_department_id_2', $request->get('department'));
                    });
                    // PAC_5-1599 End
            } else {
                $data_query->leftJoin('mst_user_info as UI','A.apply_user_id','UI.mst_user_id')
                    ->where('UI.mst_department_id', $request->get('department'));
            }
        }

        $data_query = $data_query->orderBy($orderBy,$orderDir);

        $attachments = $data_query->paginate($limit)->appends(request()->input());

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($mst_company_id);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->assign('limit',$limit);
        $this->assign('orderBy',$orderBy);
        $this->assign('orderDir', strtolower($orderDir)=="asc"?"desc":"asc");
        $this->assign('itemAttachments',$attachments);
        $this->assign('listDepartmentTree',$listDepartmentTree);
        $this->setMetaTitle('添付ファイル一覧');

        return $this->render('Attachments.index');
    }

    /**
     * 選択したすべての添付ファイルを削除します
     * @param $aids integer 添付ファイルID
     * @param $user object currentUser
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletes($aids,$user){
        if(!$user->can(PermissionUtils::PERMISSION_ATTACHMENTS_SETTING_DELETE) AND !$user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        try{

            $attachments = DB::table('circular_attachment')
                ->whereIn('id',$aids)
                ->get();

            //PAC_1398 すべての添付ファイルを削除
            if ($attachments){
                foreach ($attachments as $attachment){
                    if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
                        if (Storage::disk('s3')->exists($attachment->server_url)){
                            Storage::disk('s3')->delete($attachment->server_url);
                        }
                    }else if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                        if (Storage::disk('k5')->exists($attachment->server_url)){
                            Storage::disk('k5')->delete($attachment->server_url);
                        }
                    }
                }

                DB::table('circular_attachment')
                    ->whereIn('id',$aids)
                    ->update([
                        'status' => CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS,
                        'update_user' => $user->family_name . ' ' . $user->given_name,
                        'update_at' => Carbon::now()
                    ]);
            }
          $this->raiseSuccess(__('message.success.attachment_request.delete_attachment'));
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $this->raiseDanger(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }


    /**
     * 添付ファイルのダウンロード
     * @param Request $request
     * @throws \Exception
     */
    public function download(Request $request){

        $user = $request->user();
        if (!$user->can(Permissionutils::PERMISSION_ATTACHMENTS_SETTING_VIEW)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $aids = $request->get('cids',[]);
        $response = new StreamedResponse;
        $response->setCallback(function (){
        });
        //PAC_1398 1件ずつダウンロードしてください。
        if (count($aids) > 1){
            $this->raiseDanger(__('message.false.attachment_request.download_count'));
            return $response->setStatusCode(Response::HTTP_OK,'false');
        }

        $attachment = DB::table('circular_attachment')
            ->whereIn('id',$aids)
            ->first();

        try{

            if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_NOT_CHECK_STATUS){//添付ファイルはウイルス検査を完成していません
                $this->raiseDanger(__('message.false.attachment_request.is_checking'));
                return $response->setStatusCode(Response::HTTP_OK,'false');
            }else if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_CHECK_FAIL_STATUS){//ウイルスを検知しました。ダウンロードできません。
                $this->raiseDanger(__('message.false.attachment_request.check_attachment'));
                return $response->setStatusCode(Response::HTTP_OK,'false');
            }

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\CircularAttachmentUtils', 'getCircularAttachmentData', $attachment->file_name,
                $aids
            );

            if(!($result === true)){
                return response()->json([
                    'status' => false, 
                    'message' =>    [__('message.false.attachment_request.download_attachment', ['attribute' => $result])]
                ]);
            }
            
            return response()->json([
                'status' => true, 
                'message' =>    [__('message.success.attachment_request.download_attachment')]
            ]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json([
                'status' => false, 
                'message' =>    [__('message.false.attachment_request.download_attachment', ['attribute' => $ex->getMessage()])]
            ]);
        }

    }
}
