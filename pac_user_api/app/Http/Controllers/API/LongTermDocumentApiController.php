<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CheckLongTermUploadRequest;
use App\Http\Middleware\CheckHashing;
use App\Http\Requests\API\SearchLongTermDocumentAPIRequest;
use App\Http\Requests\GetPageRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\LongTermDocumentUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Jobs\UpdateUploadData;
use App\Models\UploadData;
use App\Models\ViewingUser;
use App\Utils\CircularDocumentUtils;
use App\Utils\OfficeConvertApiUtils;
use App\Utils\PDFUtils;
use App\Http\Utils\UserApiUtils;
use Carbon\Carbon;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Knox\PopplerPhp\Constants;
use Knox\PopplerPhp\PdfToCairo;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use App\Http\Utils\LongtermIndexUtils;
 use App\Http\Utils\DownloadRequestUtils;
use Matrix\Exception;
use Session;
use Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Howtomakeaturn\PDFInfo\PDFInfo;

class LongTermDocumentApiController extends AppBaseController
{
    /**
     * 長期保管一覧検索
     * @param SearchLongTermDocumentAPIRequest $request
     * @return mixed
     */
    public function index(SearchLongTermDocumentAPIRequest $request){
        $user       = $request->user();

        $documentName   = $request->get('documentName');
        $fromdate   = $request->get('fromdate');
        $todate   = $request->get('todate');
        $fromMoney   = $request->get('fromMoney');
        $toMoney   = $request->get('toMoney');
        $customer   = $request->get('customer');
        $fileName   = $request->get('fileName');
        $keyword   = $request->get('keyword');
        $indexes = $request['indexes'];
        $page       = $request->get('page', 1);
        $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy    = $request->get('orderBy', "LTD.create_at");
        $orderDir   = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $folderId   = $request->get('folderId', 0);//選択したフォルダのID

        $arrOrder   = ['title' => 'title','fileSize' => 'file_size', 'LTD.create_at' => 'LTD.create_at'];
        $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'LTD.create_at';

        $mst_company = DB::table('mst_company')
            ->where('id',$user->mst_company_id)
            ->first();

        $data = DB::table('long_term_document as LTD')
        ->select(['LTD.id','LTD.circular_id','LTD.mst_company_id','LTD.sender_name','LTD.sender_email','LTD.file_name','LTD.file_size','LTD.keyword','LTD.request_at','LTD.completed_at','LTD.title',
                'LTD.create_at','LTD.destination_name','LTD.destination_email','LTD.sender_name','LTD.sender_email', 'LTD.create_user', 'LTD.timestamp_automatic_flg','LTD.circular_attachment_json', 'LTD.add_timestamp_automatic_date','LTD.upload_status','LTD.upload_id','LTD.user_id']);

        if($documentName){
            $data = $data->where('LTD.title', 'like', '%' . $documentName . '%');
        }
        if($keyword){
            $data = $data->where('LTD.keyword', 'like', '%' . $keyword . '%');
        }
        if($mst_company->long_term_folder_flg){
            if (!$user->isAuditUser()){//通常利用者
                if ($folderId){
                    //企業名フォルダ以外の文書検索
                    $data = $data->join('long_term_folder_auth','long_term_folder_auth.long_term_folder_id','LTD.long_term_folder_id')
                        ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                        ->where('auth_link_id', $user->id);
                    //ユーザーにフォルダの権限があるかどうか
                    $hasPermission = DB::table('long_term_folder_auth')
                        ->where('long_term_folder_id',$folderId)
                        ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                        ->where('auth_link_id', $user->id)
                        ->first();
                    $data = $data->where('LTD.long_term_folder_id', '=' , $folderId);
                    if (!$hasPermission){
                        $data = $data->orderBy($orderBy,$orderDir)->paginate($limit)->appends(request()->input());
                        return $this->sendResponse(['status' => false, 'data' => $data], __('message.false.long_term_folder_permission'));
                    }
                }else{
                    //企業名フォルダ検索
                    $data = $data->whereNotExists(function ($query) use($user){
                        $query->selectRaw('1')
                            ->from('long_term_folder_auth')
                            ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                            ->where('auth_link_id', $user->id)
                            ->whereRaw("long_term_folder_auth.long_term_folder_id=LTD.long_term_folder_id");
                    });
                }
            }else{//監査用アカウント
                if ($folderId){
                    //企業名フォルダ以外の文書検索
                    $data = $data->where('LTD.long_term_folder_id', '=' , $folderId)
                        ->whereExists(function ($query) use($user){
                        $query->selectRaw('1')
                            ->from('long_term_folder_auth as ltfa')
                            ->join('mst_user as mu',function($inner_query) use($user){
                               $inner_query->on('ltfa.auth_link_id','mu.id')
                                   ->where('mu.state_flg', AppUtils::STATE_VALID)
                                   ->where('mu.option_flg', AppUtils::USER_NORMAL)
                                   ->where('mu.mst_company_id', $user->mst_company_id);
                            })
                            ->whereRaw('ltfa.long_term_folder_id=LTD.long_term_folder_id');
                    });
                }else{
                    //企業名フォルダ検索
                    $data = $data->where('LTD.long_term_folder_id', AppUtils::LONG_TERM_FOLDER_AUTH_ALL);
                }
            }
        }

        $data = $data->where('LTD.mst_company_id', $user->mst_company_id )->where("LTD.is_del",0);
        if ((!$user->isAuditUser() && !$mst_company->long_term_folder_flg) || (!$user->isAuditUser() && $mst_company->long_term_folder_flg && !$folderId)){
            $userEmail = mb_convert_encoding($user->email, 'UTF-8', 'UTF-8');
            //ユーザ参加の文書
            $data = $data->where(function($query) use ($userEmail){
                $query->where('LTD.sender_email', $userEmail);
                $query->orWhere('LTD.destination_email', 'like', '%' . $userEmail . '%');
                $query->orWhereExists(function ($query){
                    $query->select(DB::raw('1'))
                        ->from('viewing_user AS VU')
                        ->leftJoin('mst_user as mu','mu.id','=','VU.mst_user_id')
                        ->where('VU.circular_id', DB::raw('LTD.circular_id'));

                });
            });
        }
        $ids = array();
        $condition = false;
        $date_condition = false;
        $money_condition = false;
        $customer_condition = false;
        $index_condition= false;
        $long_term_document_id=[];
        try{
            // PAC_5-2114 Start
            // 統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to ID App');
            }

            $id_app_user_id = 0;
            $response = $client->post("users/checkEmail", [
                RequestOptions::JSON => ['email' => $user->email]
            ]);
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $resData = json_decode((string) $response->getBody());
                if(!empty($resData) && !empty($resData->data)){
                    $id_app_users = $resData->data;
                    // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                    foreach ($id_app_users as $id_app_user) {
                        if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                            $id_app_user_id = $id_app_user->id;
                            break;
                        }
                    }
                }
            }
            if (!$user->isAuditUser() && !$mst_company->long_term_folder_flg) {
                $data = $data->where(function($query) use ($user, $id_app_user_id) {
                    $query->whereExists(function ($query) use ($user, $id_app_user_id) {
                        $query->select(DB::raw('CU.id'))
                            ->from('circular_user AS CU')
                            ->where('CU.circular_id', DB::raw('LTD.circular_id'))
                            ->where(function ($query) use ($user, $id_app_user_id) {
                                $query->where('CU.mst_user_id', $user->id)
                                    ->orWhere('CU.mst_user_id', $id_app_user_id);
                            });

                    })->orWhereExists(function ($query) use ($user) {
                        $query->select(DB::raw('VU.id'))
                            ->from('viewing_user AS VU')
                            ->where('VU.circular_id', DB::raw('LTD.circular_id'))
                            ->where('VU.mst_user_id', $user->id);
                    });
                });
            }
            //PAC_5-2114 End

            if($fromdate || $todate){
                $condition = true;
                $longtermIndexDate = DB::table('longterm_index')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('longterm_index_id', 1);
                if($fromdate){
                    $longtermIndexDate = $longtermIndexDate->whereDate('date_value', '>=', $fromdate);
                }
                if($todate){
                    $longtermIndexDate = $longtermIndexDate->whereDate('date_value', '<=', $todate);
                }
                $longtermIndexDate = $longtermIndexDate->get();

                if(count($longtermIndexDate)){

                    foreach ($longtermIndexDate as $value) {
                        if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                            $long_term_document_id[]=$value->long_term_document_id;
                        }else{
                            $ids[] = $value->circular_id;
                        }

                    }
                }
                $date_condition = true;

            }

            if($fromMoney || $toMoney){
                $condition = true;
                $fromMoney=str_replace(',', '', $fromMoney);
                $toMoney=str_replace(',', '', $toMoney);
                $longtermIndexMoney = DB::table('longterm_index')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('longterm_index_id', 2);
                if($fromMoney){
                    $longtermIndexMoney = $longtermIndexMoney->where('num_value', '>=', $fromMoney);
                }
                if($toMoney){
                    $longtermIndexMoney = $longtermIndexMoney->where('num_value', '<=', $toMoney);
                }

                if(!$ids && !$date_condition){

                    $longtermIndexMoney = $longtermIndexMoney->get();
                }else{
                    $longtermIndexMoney = $longtermIndexMoney->where(function ($query)use ($ids,$long_term_document_id){
                       $query->whereIn('circular_id', $ids)->orWhereIn('long_term_document_id',$long_term_document_id);
                    });
                    $longtermIndexMoney = $longtermIndexMoney->get();
                }

                $ids = array();
                $long_term_document_id=[];
                if(count($longtermIndexMoney)){
                    foreach ($longtermIndexMoney as $value) {
                        if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                            $long_term_document_id[]=$value->long_term_document_id;
                        }else{
                            $ids[] = $value->circular_id;
                        }
                    }
                }
                $money_condition = true;
            }

            //PAC_5_2134 取引先検索
            if($customer){
                $condition = true;
                $longtermIndexCustomer = DB::table('longterm_index')
                    ->join('mst_longterm_index','mst_longterm_index.id','longterm_index.longterm_index_id')

                    ->where('mst_longterm_index.permission',LongtermIndexUtils::CUSTOMER_PERMISSION)
                    ->where('mst_longterm_index.index_name', LongtermIndexUtils::CUSTOMER_NAME)
                    ->where('longterm_index.mst_company_id', $user->mst_company_id)
                    // PAC_5-2496 add
                    ->where('longterm_index.string_value','like', '%'.$customer.'%')
                    ->select('longterm_index.circular_id','longterm_index.long_term_document_id');

                if(!$ids && !$date_condition && !$money_condition){
                    $longtermIndexCustomer = $longtermIndexCustomer->get();
                }else{
                    $longtermIndexCustomer = $longtermIndexCustomer->where(function ($query)use ($ids,$long_term_document_id){
                        $query->whereIn('longterm_index.circular_id', $ids)->orWhereIn('longterm_index.long_term_document_id',$long_term_document_id);
                    });
                    $longtermIndexCustomer = $longtermIndexCustomer->get();
                }

                //以上の検索条件を満たすcirular_id。
                $ids = array();
                $long_term_document_id=[];
                if(count($longtermIndexCustomer)){
                    foreach ($longtermIndexCustomer as $value) {
                        if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                            $long_term_document_id[]=$value->long_term_document_id;
                        }else{
                            $ids[] = $value->circular_id;
                        }

                    }
                }

                $customer_condition = true;
            }

            if($indexes){
                foreach ($indexes as $value) {
                    if($value['id'] && ($value['fromvalue'] || $value['tovalue'])){
                        $condition = true;
                        $mstLongtermIndex = DB::table('mst_longterm_index')
                            ->where('id', $value['id'])
                            ->first();
                        $longtermIndex = DB::table('longterm_index')
                            ->where('mst_company_id', $user->mst_company_id)
                            ->where('longterm_index_id', $value['id']);
                        if($mstLongtermIndex->data_type === LongtermIndexUtils::NUMERIC_TYPE){
                            if($value['fromvalue']){

                                $longtermIndex->where('num_value', '>=', str_replace(',', '', $value['fromvalue']));
                            }
                            if($value['tovalue']){
                                $longtermIndex->where('num_value', '<=', str_replace(',', '', $value['tovalue']));
                            }
                        } else if($mstLongtermIndex->data_type === LongtermIndexUtils::STRING_TYPE){
                            // PAC_5-2496 add
                                $longtermIndex->where('string_value', 'like','%'.$value['fromvalue'].'%');
                        } else if($mstLongtermIndex->data_type === LongtermIndexUtils::DATE_TYPE){
                            if($value['fromvalue']){
                                $longtermIndex->whereDate('date_value', '>=', $value['fromvalue']);
                            }
                            if($value['tovalue']){
                                $longtermIndex->whereDate('date_value', '<=', $value['tovalue']);
                            }
                        }
                        if(!$ids && !$date_condition && !$money_condition && !$customer_condition && !$index_condition){
                            $longtermIndex = $longtermIndex->get();
                        }else{
                            $longtermIndex = $longtermIndex->where(function ($query)use ($ids,$long_term_document_id){
                                $query->whereIn('circular_id', $ids)->orWhereIn('long_term_document_id',$long_term_document_id);
                            });
                            $longtermIndex = $longtermIndex->get();
                        }

                        $ids = $ids = array();
                        $long_term_document_id=[];
                        if(count($longtermIndex)){
                            foreach ($longtermIndex as $value) {
                                if(isset($value->long_term_document_id)&& $value->long_term_document_id){
                                    $long_term_document_id[]=$value->long_term_document_id;
                                }else{
                                    $ids[] = $value->circular_id;
                                }
                            }
                        }
                        $index_condition= true;
                    }
                }
            }

            if($condition){
             $data = $data->whereIn('LTD.circular_id', $ids)->orWhereIn('LTD.id',$long_term_document_id);
                 $data=$data->where(function ($query)use ($user,$documentName,$keyword,$mst_company,$folderId) {
                     $query->where("LTD.is_del",0);
                     if($mst_company->long_term_folder_flg){
                         $query->where("LTD.long_term_folder_id", $folderId);
                     }
                     $query->whereExists(function ($query) use ($user) {
                         $query->select(DB::raw('ud.id'))
                             ->from('upload_data AS ud')
                             ->where('ud.id', DB::raw('LTD.upload_id'))
                             ->where(function ($query) use ($user) {
                                 $query->where('LTD.user_id', $user->id)
                                     ->where('LTD.mst_company_id', $user->mst_company_id);
                             });
                     });
                     if($documentName){
                         $query  ->where('LTD.title', 'like', '%' . $documentName . '%');
                     }
                     if($keyword){
                         $query->where('LTD.keyword', 'like', '%' . $keyword . '%');
                     }
                 });
            }else{
                    $data=$data->orWhere(function ($query)use ($user,$documentName,$keyword,$mst_company,$folderId){
                        $query->where("LTD.is_del",0);
                        if($mst_company->long_term_folder_flg){
                            $query->where("LTD.long_term_folder_id", $folderId);
                        }
                        $query->whereExists(function ($query) use ($user) {
                            $query->select(DB::raw('ud.id'))
                                ->from('upload_data AS ud')
                                ->where('ud.id', DB::raw('LTD.upload_id'))
                                ->where(function ($query)use ($user){
                                    $query->where('LTD.user_id',$user->id)
                                        ->where('LTD.mst_company_id', $user->mst_company_id);
                                });
                        });
                        if($documentName){
                            $query  ->where('LTD.title', 'like', '%' . $documentName . '%');
                        }
                        if($keyword){
                            $query->where('LTD.keyword', 'like', '%' . $keyword . '%');
                        }
                    });
            }
            $data = $data->groupBy('LTD.id','LTD.circular_id','LTD.mst_company_id','LTD.sender_name','LTD.sender_email','LTD.file_name','LTD.file_size','LTD.keyword','LTD.request_at','LTD.completed_at','LTD.title',
                'LTD.create_at','LTD.destination_name','LTD.destination_email','LTD.sender_name','LTD.sender_email', 'LTD.create_user', 'LTD.timestamp_automatic_flg');

            $data = $data->orderBy($orderBy,$orderDir)
                        ->paginate($limit)->appends(request()->input());

            foreach($data as $key=>$val){
                $val->circular_attachment_name_string = '';
                // PAC_5-2359 add
                $ValTmpQuery= DB::table('longterm_index as li')
                    ->leftJoin('mst_longterm_index as mli','li.longterm_index_id','=','mli.id')
                    ->where('li.circular_id',$val->circular_id);
                if($val->upload_status){
                    $ValTmpQuery=$ValTmpQuery->where('li.long_term_document_id',$val->id);
                }

                $val->circular_index=$ValTmpQuery->select('mli.index_name','mli.data_type','mli.id','li.longterm_index_id','li.circular_id','li.string_value','li.num_value','li.date_value')->get();
                if(!empty($val->circular_attachment_json) ){
                    $arrTemp = json_decode($val->circular_attachment_json,true);
                    foreach ($arrTemp as $k=>$v){
                        if((isset($v['company_id'],$v['confidential_flg'])) && !(($v['company_id'] == $user->mst_company_id )||($v['confidential_flg'] == 0))){unset($arrTemp[$k]);}
                        if(!isset($v['type'])){
                            if(!$this->handlerAttachment($val->circular_id,$user->mst_company_id,$v['server_url'])){
                                unset($arrTemp[$k]);
                            }
                        }
                    }
                    $val->circular_attachment_name_string = implode("<br />",array_column($arrTemp,'file_name'));

                }else{
                    $val->circular_attachment_json = '';
                }
                $data[$key] = $val;
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse($data,'文書保存処理に成功しました。');
    }

    public function show($circular_id, Request $request)
    {
        if(isset($request['usingHash']) && $request['usingHash']) {
            if ($request['current_edition_flg'] == config('app.edition_flg') && $request['current_env_flg'] == config('app.server_env') && $request['current_server_flg'] == config('app.server_flg') ) {
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company = DB::table('mst_company')->where('id', $request['current_circular_user']->mst_company_id)->first();
                } else {
                    $company = DB::table('mst_company')->where('id', $request['current_viewing_user']->mst_company_id)->first();
                }
            }else{
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company_id = $request['current_circular_user']->mst_company_id;
                } else {
                    $company_id = $request['current_viewing_user']->mst_company_id;
                }

                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                $response = $envClient->get("getCompany/$company_id", []);
                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                    //check stamps flg company
                    $company = json_decode($response->getBody())->data;
                }
            }
        }else {
            $user = $request->user();
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
        }
        if (!$company || !$company->long_term_storage_flg){
            $this->sendError("Cannot find Circular", \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        $longTermDocument = DB::table('long_term_document')
            ->where('circular_id', $circular_id)
            ->where('mst_company_id', $company->id)
            ->first();
        return response()->json(['status' => true, 'item' => $longTermDocument]);
    }

    public function delete(Request $request)
    {
        $user       = $request->user();
        try{
            // ================
            // ファイル削除
            // ================
            $longTermDocumentDatas = null;

            if($user->isAuditUser()){
                return $this->sendError('文書削除処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            // update
            $mixedResult = DB::table('long_term_document')
                ->whereIn('id', $request->all())
                ->where('mst_company_id', $user->mst_company_id)
                ->where(function ($query) use ($user){
                    $query->where('create_user', $user->email);
                    $query->orWhere('user_id',$user->id);
                })
                ->update([
                    'is_del' => 1
                ]);
            if(!$mixedResult){
                return $this->sendError('文書削除処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $this->sendSuccess('文書削除処理に成功しました。');

            if ($user->isAuditUser()){
                $longTermDocumentDatas = DB::table('long_term_document')
                    ->whereIn('id', $request->all())
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select(['id','circular_id','upload_status','upload_id'])
                    ->get();
            }else{
                $longTermDocumentDatas = DB::table('long_term_document')
                    ->whereIn('id', $request->all())
                    ->where(function ($query) use ($user){
                        $query->where('create_user', $user->email);
                        $query->orWhere('user_id',$user->id);
                    })
                    ->select(['id','circular_id','upload_status','upload_id'])
                    ->get();
            }
            if($longTermDocumentDatas){
                $type='s3';
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                    $type='k5';
                }
                foreach($longTermDocumentDatas as $longTermDocumentData){
                    // S3サーバのフォルダパス編集
                    $s3path=config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                        . '/' . config('app.server_flg').'/'.$user->mst_company_id ;
                    $path=  $s3path. '/' .($longTermDocumentData->upload_status? ('upload_'.$longTermDocumentData->upload_id) :$longTermDocumentData->circular_id);
                    // 存在する場合

                    if ( Storage::disk($type)->exists($path)){
                        // フォルダパス下のファイルを取得して、
                        $file_names = Storage::disk($type)->files($path);
                        foreach($file_names as $file_name){
                            if (Storage::disk($type)->exists($file_name)){
                                Storage::disk($type)->delete($file_name);
                                Log::info("s3ファイルが削除しました：".$file_name);
                            }
                        }
                    }
                }
            }

            // ================
            // DB削除
            // ================
            DB::beginTransaction();
            $fileNames = [];
            $documentIds = [];
            $upload_ids=[];
            $circular_ids=[];
            if ($user->isAuditUser()){
                $long_term_documents = DB::table('long_term_document')->whereIn('id', $request->all())->where('mst_company_id', $user->mst_company_id)->select(['id', 'file_name','upload_status','upload_id','circular_id'])->get();
                foreach ($long_term_documents as $long_term_document){
                    $documentIds[] = $long_term_document->id;
                    $fileNames[] = $long_term_document->file_name;
                    if($long_term_document->upload_status){
                        $upload_ids[]=$long_term_document->upload_id;
                    }
                    if($long_term_document->circular_id){
                        $circular_ids[]=$long_term_document->circular_id;
                    }
                }
            }else{
                $long_term_documents = DB::table('long_term_document')
                    ->whereIn('id', $request->all())
                    ->where(function ($query) use ($user){
                        $query->where('create_user', $user->email);
                        $query->orWhere('user_id',$user->id);
                    })
                    ->select(['id', 'file_name','upload_status','upload_id','circular_id'])->get();
                foreach ($long_term_documents as $long_term_document){
                    $documentIds[] = $long_term_document->id;
                    $fileNames[] = $long_term_document->file_name;
                    if($long_term_document->upload_status){
                        $upload_ids[]=$long_term_document->upload_id;
                    }
                    if($long_term_document->circular_id){
                        $circular_ids[]=$long_term_document->circular_id;
                    }
                }
            }
            $arrDocumentAttachment = DB::table("long_term_document")->whereIn("id",$documentIds)
                ->where("circular_attachment_json",'<>','')->select("circular_attachment_json")->get();
            DB::table('long_term_department')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_document')->whereIn('id', $documentIds)->delete();
            DB::table('upload_data')->whereIn('id', $upload_ids)->delete();
            DB::table('long_term_circular')->whereIn('id',$circular_ids)->delete();
            DB::table('long_term_circular_operation_history')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_text_info')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_stamp_info')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_document_comment_info')->whereIn('long_term_document_id',$documentIds)->delete();
            DB::table('long_term_circular_user')->whereIn('circular_id',$circular_ids)->delete();
            $arrPath = [];
            foreach($arrDocumentAttachment as $attachment){
                $arrItem = json_decode($attachment->circular_attachment_json,true);
                foreach ($arrItem as $item){
                    if(!isset($arrPath[$item['server_url']])){
                        $arrPath[$item['server_url']] = true;
                    }
                }

            }
            if(!empty($arrPath)){
                foreach(array_keys($arrPath) as $strPath){
                    if($strPath == '' || mb_strlen($strPath,'UTF-8') < 30){
                        continue;
                    }
                    Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5':'s3')->delete($strPath);
                }
            }
            Session::flash('fileNames', $fileNames);
            DB::commit();
            return $this->sendSuccess('文書削除処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('文書削除処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $document = $request->all();

        try{
            DB::beginTransaction();
            DB::table('long_term_document')->where('id', $id)->update(['keyword' => $document['keyword'], 'is_del' => 0,'update_user' => $user->email, 'update_at' => Carbon::now()]);
            DB::commit();
            return $this->sendSuccess('文書保存処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('文書保存処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function automaticUpdateTimestamp(Request $request)
    {
        $user = $request->user();
        $circular = $request->all();
        try{
            $ids = [];

            $long_term_circulars = DB::table('long_term_document')->select('id','circular_id','timestamp_automatic_flg','add_timestamp_automatic_date')
                ->whereIn('id',$circular['id'])
                ->get()->keyBy('id');

            if ($long_term_circulars){
                foreach ($long_term_circulars as $id => $long_term_circular){
                    //文書を完了した際にタイムスタンプを付与していた場合
                    $is_time_stamp =  DB::table('circular_document as cd')
                        ->join('time_stamp_info as tsi','tsi.circular_document_id','cd.id')
                        ->where('cd.circular_id',$long_term_circular->circular_id)
                        ->count();

                    if ($is_time_stamp || ($long_term_circular->timestamp_automatic_flg ||$long_term_circular->add_timestamp_automatic_date )){
                        $ids[] = $id;
                    }elseif ($circular['automatic']==1){
                        return $this->sendError('タイムスタンプが付与されていない文書には「自動更新：ON」にすることはできません。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }

            DB::beginTransaction();

            DB::table('long_term_document')
                ->whereIn('id', $ids)
                ->whereNull('add_timestamp_automatic_date')
                ->update([
                    'timestamp_automatic_flg' => $circular['automatic'] ? 1 : 0,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now(),
                    'add_timestamp_automatic_date' => $circular['automatic'] ? DB::raw("`completed_at`") : null,
                ]);

            DB::table('long_term_document')
                ->whereIn('id', $ids)
                ->whereNotNull('add_timestamp_automatic_date')
                ->update([
                    'timestamp_automatic_flg' => $circular['automatic'] ? 1 : 0,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now(),
                ]);
            DB::commit();
            return $this->sendSuccess($circular['automatic']?'ON自動タイムスタンプ更新に成功しました。':'OFF自動タイムスタンプ更新に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('タイムスタンプ自動更新に失敗しました・', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function download(Request $request)
    {
        try{
            $user       = $request->user();
            $circularIds = DB::table('long_term_document')->whereIn('id', $request->all())->where('mst_company_id', $user->mst_company_id)->pluck('circular_id')->toArray();

            $circularIds = array_unique($circularIds);

            $fileName = "download-" . time() . ".zip";
            $zipPath = sys_get_temp_dir()."/download-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $user->mst_company_id, $user->id) . ".zip";

            $zip = new Filesystem(new ZipArchiveAdapter($zipPath));
            foreach ($circularIds as $circularId){
                $path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                    . '/' . config('app.server_flg') . '/'. $user->mst_company_id . '/' . $circularId;
                if ( Storage::disk('s3')->exists($path)){
                    $file_names = Storage::disk('s3')->files($path);
                    foreach($file_names as $file_name){
                        $file_content = Storage::disk('s3')->get($file_name);
                        $zip->put($circularId.'/'.substr($file_name, strrpos($file_name, '/')), $file_content);
                    }
                }
            }
            $zip->getAdapter()->getArchive()->close();
            if (file_exists ( $zipPath )){
                return $this->sendResponse(['fileName' => $fileName,
                        'file_data' => \base64_encode(\file_get_contents($zipPath)) ]
                    ,'文書ダウンロード処理に成功しました。');
            }else{
                return $this->sendError('送信文書のダウンロード処理に失敗しました。');
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ファイルダウンロードリスト
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadList(Request $request)
    {
        $reqFileName = $request->get('fileName', '');
        $longTermDocument = $request->get('longTermDocumentIds', []);

        // 回覧ID取得
        $circularIds = [];
        $circularIdsStdClass = DB::table('long_term_document')
            ->whereIn('id', $longTermDocument)
            ->select('circular_id')->get()->toArray();
        for($i = 0; $i < count($circularIdsStdClass); $i++){
            $circularIds[$i] = $circularIdsStdClass[$i]->circular_id;
        }

        // long_term_document 情報取得
        $long_term_document_datas = DB::table('long_term_document')
            ->whereIn('circular_id', $circularIds)
            ->where('mst_company_id', \Auth::user()->mst_company_id)
            ->select('id', 'circular_id', 'file_name', 'file_size')
            ->get();
        // 0件の場合、取得失敗
        if (count($long_term_document_datas) === 0) {
            return response()->json(['status' => false,
                'message' => __('message.false.download_request.file_detail_get')]);
        }

        // デフォルトファイル名設定
        $fileName = Carbon::now()->copy()->format('YmdHis') . ".zip";
        if (count($long_term_document_datas) == 1) {
            // 一つcircular_idの場合
            $documentFileName = $long_term_document_datas[0]->file_name;
            $arr = explode(".", $documentFileName);
            // 一つファイルの場合
            if (count($arr) <= 2 || !strpos($documentFileName,", ")){
                $fileName = $reqFileName . $documentFileName;
            }
        }

        // If InputData, FileName is changed
        if ($reqFileName != '') {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            // No Extension
            $ext = $ext == "" ? "" : '.' . $ext;
            $fileName = $reqFileName . $ext;
        }

        // ダウンロード予約処理
        return DownloadRequestUtils::reserveLongTermDocumentDownload($circularIds, $reqFileName);
    }

    public function listIndex(Request $request) {
        $user = $request->user();
        try {
            $mst_company_id = $user->mst_company_id;
            $mst_user_info = DB::table('mst_user_info')
                ->where('mst_user_id',$user->id)
                ->get();


            $mst_company_info = DB::table('mst_company')
                ->where('id',$mst_company_id)
                ->get();

            $userEmail = mb_convert_encoding($user->email, 'UTF-8', 'UTF-8');

            $longterm_circular_id = DB::table('long_term_document')
                ->select('circular_id')
                ->where('sender_email', $userEmail)
                ->orWhere('destination_email', 'like', '%' . $userEmail . '%')
                ->get();
            $circularIdArray = json_decode(json_encode($longterm_circular_id), true);
            $longtermIndexId = DB::table('longterm_index')
                ->select('longterm_index_id')
                ->whereIn('circular_id', $circularIdArray)
                ->get();
            $longTermIndexArray = json_decode(json_encode($longtermIndexId), true);
            $longTermIndex = DB::table('mst_longterm_index')
                // 通常長期保管インデックス
                ->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id',$mst_company_id)
                        ->Where('template_flg', 0);
                })
                // デフォルト
                ->orwhere('mst_company_id',0);
            // テンプレート
            if($mst_user_info[0]->template_flg && $mst_company_info[0]->template_flg) {
                $longTermIndex = $longTermIndex->orwhere(function($query) use ($mst_company_id,$longTermIndexArray){
                    $query->whereIn('id',$longTermIndexArray)
                        ->where('mst_company_id',$mst_company_id)
                        ->where('template_valid_flg',1)
                        ->Where('template_flg', 1);
                });
            }
            // 帳票
            if($user->frm_srv_user_flg && $mst_company_info[0]->frm_srv_flg) {
                $longTermIndex = $longTermIndex->orwhere(function($query) use ($mst_company_id,$longTermIndexArray){
                    $query->whereIn('id',$longTermIndexArray)
                        ->where('mst_company_id',$mst_company_id)
                        ->where('template_valid_flg',1)
                        ->Where('template_flg', 2);
                });
            }
            $longTermIndex = $longTermIndex->orderBy('sort_id', 'asc')
                ->get();
            foreach ($longTermIndex as $value) {
                $value->index_value = '';
            }

                return $this->sendResponse($longTermIndex,'長期保管インデックスの取得に成功しました。');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('長期保管インデックスの取得に失敗しました。');
        }
    }

    public function listIndexOption(Request $request) {
        $user = $request->user();
        try {
//            $mst_company_id = $user->mst_company_id;
            $cid=$request->input('id');
//            $longTermIndex = DB::table('mst_longterm_index')
//                ->where('mst_company_id',$mst_company_id)
//                ->orWhere('mst_company_id', 0)
//                ->get();
//            foreach ($longTermIndex as $value) {
//                $value->index_value = '';
//            }

            $mst_company_id = $user->mst_company_id;

            $template_flg = DB::table('template_input_data')->where('circular_id', $cid)->exists();
            $frm_invoice_flg = DB::table('frm_invoice_data')->where('circular_id', $cid)->exists();
            $frm_others_flg = DB::table('frm_others_data')->where('circular_id', $cid)->exists();
            $longTermIndex = DB::table('mst_longterm_index')
                // 通常長期保管インデックス
                ->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id',$mst_company_id)
                        ->Where('template_flg', 0);
                })
                // デフォルト
                ->orwhere('mst_company_id',0);
            // テンプレート回覧すれば
            if($template_flg){
                $longTermIndex = $longTermIndex->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id', $mst_company_id)
                        ->Where('template_flg', 1);
                });
            }
            // 帳票回覧すれば
            if($frm_invoice_flg || $frm_others_flg){
                $longTermIndex = $longTermIndex->orwhere(function($query) use ($mst_company_id){
                    $query->where('mst_company_id', $mst_company_id)
                        ->Where('template_flg', 2);
                });
            }
            $longTermIndex = $longTermIndex->orderBy('sort_id', 'asc')
                ->get();
            foreach ($longTermIndex as $value) {
                $value->index_value = '';
            }

            return $this->sendResponse($longTermIndex,'長期保管インデックスの取得に成功しました。');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('長期保管インデックスの取得に失敗しました。');
        }
    }

    public function setIndex(Request $request)
    {
        $user = $request->user();

        $cid = $request['cid'];
        $indexes = $request['indexes'];
        if (isset($request['finishedDate']) && $request['finishedDate']) {
            $finishedDateKey = $request['finishedDate'];
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        } else {
            $finishedDate = '';
        }
        try {

            $indexes = Arr::where($indexes, function ($value, $key) {
                return $value['longterm_index_id'] != '' && $value['value'] != '';
            });
            $circular = DB::table("circular_document$finishedDate  as CD")
                ->join('circular_user as CU', 'CU.circular_id', '=', 'CD.circular_id')
                ->where('CD.circular_id', $cid)
                ->select('CD.circular_id', 'CD.file_name', 'CU.title')
                ->first();
            if (empty($circular)) {
                return $this->sendError(__('message.false.download_request.file_detail_get'));
            }

            $circularValue = $circular->title == ' ' ? $circular->file_name : $circular->title;
            DB::beginTransaction();
            // 長期保管 - 回覧の承認者が完了一覧文書の長期保管インデックスの追加や削除を実施すると、既に登録されている項目が必ず登録されてしまう。
            DB::table('longterm_index')->where('circular_id', $cid)->where('mst_company_id', $user->mst_company_id)->delete();
            $data = [];
            $msg = nl2br("{$circularValue}にインデックス \n");
            foreach ($indexes as $k => $value) {
                if (strlen($value['value']) > 128) {
                    return $this->sendError('長期保管インデックス文字列の長さは128ビット以上に設定できません。');
                }
                if($value['data_type'] === LongtermIndexUtils::NUMERIC_TYPE){
                    if(str_replace(',', '', $value['value']) > 9999999999 ) {
                        return $this->sendError('数字型は十億以上に設定できません。');
                    }
                }

                $tmp = [
                    'mst_company_id' => $user->mst_company_id,
                    'mst_user_id' => $user->id,
                    'circular_id' => $cid,
                    'longterm_index_id' => $value['longterm_index_id'],
                    'create_at' => Carbon::now(),
                    'create_user' => $user->family_name . $user->given_name,
                    'num_value' => $value['data_type'] === LongtermIndexUtils::NUMERIC_TYPE ? str_replace(',', '', $value['value']) : 0,
                    'string_value' => $value['data_type'] === LongtermIndexUtils::STRING_TYPE ? $value['value'] : null,
                    'date_value' => $value['data_type'] === LongtermIndexUtils::DATE_TYPE ? Carbon::parse($value['value'] ?? '') : null
                ];
                $data[] = $tmp;
                if (!$value['index_name']) {
                    $value['index_name'] = DB::table('mst_longterm_index')->where('id', $value['longterm_index_id'])->value('index_name');
                }
                $msg .= nl2br("「{$value['index_name']}:{$value['value']}」、");
            }
            $msg = rtrim($msg, '、');
            $msg .= nl2br("\n を付与しました。");
            $info = OperationsHistoryUtils::LOG_INFO['LongTermDocumentApi']['setIndex'];
            $log_info = [[
                'auth_flg' => OperationsHistoryUtils::HISTORY_FLG_USER,
                'mst_display_id' => $info[0],
                'mst_operation_id' => $info[1],
                'result' => 1,
                'detail_info' => $msg,
                'ip_address' => $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR') : $request->getClientIp(),
                'create_at' => date("Y-m-d H:i:s"),
            ]];
            DB::table('longterm_index')->insert($data);
            if (!empty($indexes)) {
                OperationsHistoryUtils::storeRecordsToCurrentEnv($log_info, $user->id, OperationsHistoryUtils::DESTINATION_DATABASE);
            }
            DB::commit();

            return $this->sendSuccess('長期保管インデックスの保存に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::info($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('長期保管インデックスの保存に失敗しました。');
        }
    }

        // PAC-5_1852 ▼
    public function getLongTermIndex(Request $request) {
        try {
            $isCurrentEnv = true;
            $company_id = '';
            if(isset($request['usingHash']) && $request['usingHash']) {
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company_id = $request['current_circular_user']->mst_company_id;
                } else {
                    $company_id = $request['current_viewing_user']->mst_company_id;
                }
                // 他環境の場合、フラグ設定
                if ($request['current_edition_flg'] != config('app.edition_flg') || $request['current_env_flg'] != config('app.server_env') || $request['current_server_flg'] != config('app.server_flg') ) {
                    $isCurrentEnv = false;
                }

            }

            if($isCurrentEnv){
                // 本環境の場合
                $longTermIndex = DB::table('mst_longterm_index')
                    ->where('mst_company_id',$company_id)
                    ->orWhere('mst_company_id', 0)
                    ->get();

                foreach ($longTermIndex as $value) {
                    $value->index_value = '';
                }
                return $this->sendResponse($longTermIndex,'長期保管インデックスの取得に成功しました。');

            }else{
                // 他環境の場合
                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                if (!$envClient){
                    throw new \Exception('Cannot connect to Env Api');
                }

                $response = $envClient->get("getEnvLongTermIndex",[
                    RequestOptions::JSON =>[ 'company_id' => $company_id]
                ]);

                $longTermIndex = [];
                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                    $longTermIndex = json_decode($response->getBody())->data;
                    return $this->sendResponse($longTermIndex,'長期保管インデックスの取得に成功しました。');
                }else{
                    return $this->sendError($response->getStatusCode());
                }
            }

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('長期保管インデックスの取得に失敗しました。');
        }
    }

    public function getEnvLongTermIndex(Request $request)
    {
        $company_id = $request->input('company_id');

        try {
            $longTermIndex = DB::table('mst_longterm_index')
                ->where('mst_company_id', $company_id)
                ->orWhere('mst_company_id', 0)
                ->get();

            foreach ($longTermIndex as $value) {
                $value->index_value = '';
            }

            return Response::json(['status' => true, 'message' =>'長期保管インデックスの取得に成功しました。', 'data' => $longTermIndex]);

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' =>false, 'message' =>'長期保管インデックスの取得に成功しました。', 'data' => []]);
        }
    }

    public function setApprovalIndex(Request $request) {

        try {
            $isCurrentEnv = true;
            $user = $request['user'];
            if(isset($request['usingHash']) && $request['usingHash']) {
                if ($request['current_edition_flg'] != config('app.edition_flg') || $request['current_env_flg'] != config('app.server_env') || $request['current_server_flg'] != config('app.server_flg') ) {
                    $isCurrentEnv = false;
                }
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $user = $request['current_circular_user'];
                }
            }

            $userArr = json_decode( json_encode( $user),true);
            $cid = $request['cid'];
            $indexes = $request['indexes'];
            if ($isCurrentEnv){
                // 本環境の場合
                log::debug("本環境呼び出す（insertLongTermIndex）");
                if ($this->insertLongTermIndex($cid, $indexes, $userArr)){
                    return $this->sendSuccess('長期保管インデックスの保存に成功しました。');
                }else{
                    return $this->sendError('長期保管インデックスの保存に失敗しました。');
                }

            }else{
                // 他環境の場合
                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                $response = $envClient->post("setEnvApprovalIndex",[
                    RequestOptions::JSON =>[
                        'cid' => $cid,
                        'indexes' => $indexes,
                        'user' => $userArr,
                        'edition_flg' => config('app.edition_flg'),
                        'env_flg' => config('app.server_env'),
                        'server_flg' => config('app.server_flg')
                    ]
                ]);

                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                    return $this->sendSuccess('長期保管インデックスの保存に成功しました。');
                }else{
                    return $this->sendError('長期保管インデックスの保存に失敗しました。');
                }
            }

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('長期保管インデックスの保存に失敗しました。');
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function setEnvApprovalIndex(Request $request) {
        $user = $request['user'];
        $cid = $request['cid'];
        $indexes = $request['indexes'];
        $edition_flg = $request['edition_flg'];
        $env_flg = $request['env_flg'];
        $server_flg = $request['server_flg'];

        $circular = DB::table('circular')->where('origin_circular_id', $cid)
            ->where('edition_flg', $edition_flg)
            ->where('env_flg', $env_flg)
            ->where('server_flg', $server_flg)
            ->first();

        if (!$circular) {
            return Response::json(['status' => false, 'message' =>'circular_not_exist']);
        }

        log::debug("他環境呼び出す（insertLongTermIndex）");
        if ($this->insertLongTermIndex($circular->id, $indexes, $user)){
            return Response::json(['status' => true, 'message' =>'長期保管インデックスの保存に成功しました。']);
        }else{
            return Response::json(['status' => false, 'message' =>'長期保管インデックスの保存に失敗しました。']);
        }
    }

    /**
     * @param $cid 回覧ID
     * @param $indexes インデックス値
     * @param $user ユーザー
     * @return bool
     */
    private function insertLongTermIndex($cid, $indexes, $user){
        try {

            $longTermIndex = DB::table('mst_longterm_index')
                ->where('mst_company_id',$user['mst_company_id'])
                ->where('template_flg',0)
                ->orWhere('mst_company_id', 0)
                ->get();

            DB::beginTransaction();
            foreach ($longTermIndex as $value) {
                if($indexes[$value->index_name]){
                    $data = [
                        'mst_company_id' => $user['mst_company_id'],
                        'mst_user_id' => $user['mst_user_id'],
                        'circular_id' => $cid,
                        'longterm_index_id' => $value->id,
                        'create_at' => Carbon::now(),
                        'create_user' => $user['name']
                    ];

                    if($value->data_type === LongtermIndexUtils::NUMERIC_TYPE){
                        $data += array('num_value' => $indexes[$value->index_name]);
                    } else if ($value->data_type === LongtermIndexUtils::STRING_TYPE) {
                        $data += array('string_value' => $indexes[$value->index_name]);
                    } else if ($value->data_type === LongtermIndexUtils::DATE_TYPE) {
                        $data += array('date_value' => Carbon::parse($indexes[$value->index_name]));
                    }
                    DB::table('longterm_index')
                        ->insert($data);
                }
            }
            DB::commit();

            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return false;
        }
    }
    // PAC-5_1852 ▲

    // PAC_5_2377
    public function downloadattachment(Request $request)
    {
        try{
            $user       = $request->user();
            $fileName = "download-" . time() . ".zip";
            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getDownloadAttachment', $fileName,
                $user, $request->all()
            );
            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $fileName])]);
        }catch (\Throwable $th){
            Log::error($th->getMessage() . $th->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // PAC_5-2359 S
    public function getLongTermIndexValue(Request $request)
    {
        try {
            $user = $request->user();
            $cid = $request['cid'];
            $data=DB::table('longterm_index as li')->where('li.circular_id',$cid)->where('li.mst_company_id', $user->mst_company_id)
                ->leftJoin('mst_longterm_index as mli', 'li.longterm_index_id', '=', 'mli.id')
                ->select('li.date_value','li.longterm_index_id','li.circular_id','li.string_value','li.num_value','mli.index_name','mli.data_type',DB::raw("date_format(li.date_value,'%Y-%m-%d') as date_value"))->
                get();
            foreach ($data as $k=>$v){
                if (!$v->string_value &&  !$v->num_value && !$v->date_value) {
                    unset($data[$k]);

                }
            }
            return $this->sendResponse($data,'長期保管した文書インデックスが取得に成功しました。');

        }catch (\Exception $e){
            return $this->sendError('長期保管した文書インデックスが取得に失敗しました。');

        }
    }
    //PAC_5-2359 E

    private function handlerAttachment($intCircularID,$intCompanyID,$server_url){
        $objAttachment = DB::table("circular_attachment")->where("circular_id",$intCircularID)->where("server_url",$server_url)->where("status",1)->first();
        if(empty($objAttachment)){
            return false;
        }
        if($objAttachment->confidential_flg == 1  && $objAttachment->create_company_id != $intCompanyID){
            return false;
        }
        return true;
    }
    public function longTermUpload(CheckLongTermUploadRequest $request)
    {
        $user = Auth::user();
        $file = $request->file('file');
        $fileExe=['doc','docx','pdf','xls','xlsx'];
        $stored_basename = hash('SHA256', rand(). AppUtils::getUnique()). '.pdf';
        try {
            if ($request->hasFile('file') ) {
                $realExe=$file->getClientOriginalExtension();
                if(!in_array($realExe,$fileExe)){
                    return $this->sendError('アップロード可能な形式は、doc、docx、pdf、xls、xlsxのみです。');
                }
                $realName=$file->getClientOriginalName();
                $pdf= LongTermDocumentUtils::ToPdfAndImg($user,$file,$stored_basename);
                $folder=storage_path('app/'.pathinfo($pdf)['dirname'].'/'.pathinfo($pdf)['filename']);
                if (file_exists($folder)){
                    $thumb=$folder.'/1.jpg';
                    $thumbnail=$folder.'/1-thumbnail.jpg';
                    $file_size=filesize(storage_path('app/'.$pdf));
                //  $thumbBase=LongTermDocumentUtils::getFolderToBase($thumb);
                    $thumbnailBase=LongTermDocumentUtils::getFolderToBase($thumbnail);
                    $pdfBase=LongTermDocumentUtils::getFolderToBase(storage_path('app/'.$pdf));
                    $upload=  UploadData::create([
                        'upload_data'=>$pdfBase,
                        'first_img_review'=>$thumbnailBase,
                        'file_size'=>$file_size,
                    ]);
                    $path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                        . '/' . config('app.server_flg').'/'.$user->mst_company_id.'/'.'upload_'.$upload->id;
                    $pdfFile=File::get(storage_path('app/'.$pdf));
                    $thumbFile=File::get($thumb);
                    $thumbnailFile=File::get($thumbnail);
                    if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS){
                        $isFolderExist = Storage::disk('s3')->exists($path);
                        if(!$isFolderExist){
                            Storage::disk('s3')->makeDirectory($path);
                        }
                        Storage::disk('s3')->put($path.'/'.$stored_basename, $pdfFile, 'pub');
                        Storage::disk('s3')->put($path.'/'.$thumb, $thumbFile, 'pub');
                        Storage::disk('s3')->put($path.'/'.$thumbnail, $thumbnailFile, 'pub');
                    }else if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                        $isFolderExist = Storage::disk('k5')->exists($path);
                        if(!$isFolderExist){
                            Storage::disk('k5')->makeDirectory($path);
                        }
                        Storage::disk('k5')->put($path.'/'.$stored_basename, $pdfFile);
                        Storage::disk('k5')->put($path.'/'.$thumb, $thumbFile);
                        Storage::disk('k5')->put($path.'/'.$thumbnail, $thumbnailFile);
                    }
                    $data=['upload_id'=>$upload->id,'file_name'=>$realName,'unique_name'=>$stored_basename];
                    return $this->sendResponse($data,'ファイルアップロード成功');
                }
            }
        }catch (\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
    public function saveLongTermDocument(Request $request)
    {
        $user = Auth::user();
        $indexes = $request->input('indexes',[]);
        $keywords=$request->input('keywords');
        $upload_id=$request->input('upload_id');
        $file_name=$request->input('file_name');
        $unique_name=$request->input('unique_name');
        $folder_id=$request->input('folder_id')?:0;
        $StampPermissionFlg=$request->input('StampPermissionFlg',false);
        $file_name=strpos($file_name,'.pdf')?$file_name:str_replace('.','_',$file_name).'.pdf';
        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();

        if($company->long_term_folder_flg){
            $hasPermission = DB::table('long_term_folder_auth')
                ->where('long_term_folder_id',$folder_id)
                ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                ->where('auth_link_id', $user->id)
                ->first();
            if (!$hasPermission) return $this->sendError(__('message.false.long_term_folder_permission'));
        }

        try {
            DB::beginTransaction();
            $upload_data=UploadData::where('id',$upload_id)->first();
            $long_term_document_id=DB::table('long_term_document')->insertGetId([
                'circular_id'           =>      0,
                'mst_company_id'        =>      $user->mst_company_id,
                'sender_name'           =>      '',
                'sender_email'          =>      '',
                'destination_name'      =>      '',
                'destination_email'     =>      '',
                'file_name'             =>      $file_name,
                'file_size'             =>      $upload_data->file_size,
                'keyword'               =>      $keywords,
                'request_at'            =>      Carbon::now(),
                'completed_at'          =>      Carbon::now(),
                'title'                 =>      $file_name,
                'create_user'           =>      $user->email,
                'update_user'           =>      null,
                'create_at'             =>      Carbon::now(),
                'update_at'             =>      Carbon::now() ,
                'upload_status'         =>      1 ,
                'upload_id'             =>      $upload_id,
                'user_id'               =>      $user->id,
                'long_term_folder_id'   =>      $folder_id,
                'add_timestamp_automatic_date'=>$StampPermissionFlg?Carbon::now():null,
                'timestamp_automatic_flg'=>0
            ]);
            if($StampPermissionFlg){
                $this->dispatch(new UpdateUploadData($upload_id,$unique_name,$user));
            }
            $this->setLongTermIndex($long_term_document_id,$indexes,$user);
            DB::commit();
            return $this->sendSuccess('アップロード処理に成功しました。');
        }catch (\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return $this->sendError('アップロード処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function setLongTermIndex($id,$index,$user)
    {
        $indexes = Arr::where($index, function ($value, $key) {
            return $value['longterm_index_id']!='' && $value['value']!='';
        });
        $data=[];
        foreach ($indexes  as $k=>$value) {
            if(strlen($value['value'])>128){
                return $this->sendError('長期保管インデックス文字列の長さは128ビット以上に設定できません。');
            }
            $tmp= [
                'mst_company_id' => $user->mst_company_id,
                'mst_user_id' => $user->id,
                'circular_id' => 0,
                'long_term_document_id' => $id,
                'longterm_index_id' => $value['longterm_index_id'],
                'create_at' => Carbon::now(),
                'create_user' => $user->family_name . $user->given_name,
                'num_value'=>$value['data_type'] === LongtermIndexUtils::NUMERIC_TYPE ?str_replace(',', '', $value['value']):0,
                'string_value'=>$value['data_type'] === LongtermIndexUtils::STRING_TYPE ?$value['value']:null,
                'date_value'=>$value['data_type'] === LongtermIndexUtils::DATE_TYPE ?Carbon::parse( $value['value']??''):null
            ];
            $data[]=$tmp;
        }
        DB::table('longterm_index')->insert($data);
    }

    /**
     * 会社関連フォルダ
     * @param Request $request
     * @return mixed
     */
    public function getMyFolders(Request $request){
        try {
            $isCurrentEnv = true;
            if (isset($request['usingHash']) && $request['usingHash']){
                $user = $request->get('user');
                $isAuditUser = false;
                $env_flg = $request['current_env_flg'];
                $server_flg = $request['current_server_flg'];
                if($env_flg != config('app.server_env') || $server_flg != config('app.server_flg')){
                    $isCurrentEnv = false;
                }
            }else{
                $user = $request->user();
                $isAuditUser = $user->isAuditUser();
            }
            if(!$isCurrentEnv){
                // 他環境処理を呼び出し
                $envClient = EnvApiUtils::getAuthorizeClient($env_flg,$server_flg);
                if (!$envClient) {
                    Log::error('長期保管:Cannot connect to other Env Api Client');
                    return $this->sendError(['status' => false, 'data' => null], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
                }

                $response = $envClient->post('getMyLongTermFolders', [
                    RequestOptions::JSON => ['email' => $user->email]
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                    Log::error('長期保管:other Env Api longStoreDocument failed');
                    return $this->sendError(__('message.false.long_term_folder_get'), StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
                }
                $result = json_decode($response->getBody(), true);
                $listFoldersTree = collect($result['listFoldersTree']);
            }else{
                // user_info
                $user_info = DB::table('mst_user_info')
                    ->where('mst_user_id', '=', $user->id)
                    ->first();

                if(!$user_info){
                    return $this->sendError(__('message.false.long_term_folder_get'));
                }
                $foldersList = DB::table('long_term_folder as ltf')
                    ->select('ltf.id'
                        , 'ltf.parent_id'
                        , 'ltf.display_no'
                        , 'ltf.tree'
                        , 'ltf.folder_name'
                    )
                    ->where('ltf.mst_company_id', $user->mst_company_id)
                    ->orderBy('ltf.id')
                    ->distinct()
                    ->get();

                // リストをフォルダ階層考慮追加
                $listFoldersTree = CommonUtils::arrToTree($foldersList);
            }
            return $this->sendResponse($listFoldersTree, __('message.success.long_term_folder_get'));
        }catch (\Exception $e){
            return $this->sendError(__('message.false.long_term_folder_get'));
        }
    }

    /**
     * その他環境フォルダ取得
     * @param Request $request
     * @return mixed
     */
    public function getMyLongTermFolders(Request $request){
        $email = $request->input('email');

        // user_info
        $user = DB::table('mst_user')
            ->where('email','=',$email)
            ->first();
        if(!$user){
            return Response::json(['status' => false, 'message' => "フォルダを取得することが失敗です。", 'data' => null], 500);
        }
        $user_info = DB::table('mst_user_info')
            ->where('mst_user_id', '=', $user->id)
            ->first();
        if(!$user_info){
            return Response::json(['status' => false, 'message' => "フォルダを取得することが失敗です。", 'data' => null], 500);
        }
        // フォルダリスト
        $foldersList = DB::table('long_term_folder as ltf')
            ->join('long_term_folder_auth as au', 'ltf.id', '=', 'au.long_term_folder_id')
            ->select('ltf.id'
                , 'ltf.parent_id'
                , 'ltf.display_no'
                , 'ltf.tree'
                , 'ltf.folder_name'
            )
            ->where('ltf.mst_company_id', $user->mst_company_id)
            ->orderBy('ltf.id')
            ->distinct()
            ->get();

        // リストをフォルダ階層考慮追加
        $listFoldersTree = CommonUtils::arrToTree($foldersList);
        return Response::json(['status' => true, 'listFoldersTree' => $listFoldersTree]);
    }

    /**
     * 文書にフォルダを移動
     * @param Request $request
     * @return mixed
     */
    public function updateFolderId(Request $request){

        try {
            $user = $request->user();

            $cids = $request->get('cids');
            $folderId = $request->get('folderId');

            $hasPermission = DB::table('long_term_folder_auth')
                ->where('long_term_folder_id',$folderId)
                ->where('auth_kbn', AppUtils::LONG_TERM_FOLDER_AUTH_PERSON)
                ->where('auth_link_id', $user->id)
                ->first();

            if (!$hasPermission){
                return $this->sendError(__('message.false.long_term_folder_permission'));
            }

            DB::table("long_term_document")->whereIn('id', $cids)
                ->update([
                    'long_term_folder_id' => $folderId,
                    'update_at' => Carbon::now(),
                    'update_user' => $user->email,
                ]);
            return $this->sendResponse(['status' => true, 'data' => null], __('message.success.long_term_folder_move'));
        } catch (\Exception $ex) {
            Log::error('文書移動:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.long_term_folder_move'));
        }

    }

    public function getCircularPageData(Request $request){
        try {
            $user = $request->user();
            $id = $request->get('id');
            $longTermFlg=$request->get('longTermFlg',0);
            $lid=$request->get('lid');
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = \Illuminate\Support\Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            if(!$longTermFlg){
                $circular = DB::table("circular$finishedDate")->where('id', $id)->first();
                if (!$circular || !$circular->id) {
                    return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
                }
                $firstDocument = DB::table("circular_document$finishedDate")
                    ->where('circular_id', $id)
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
            }else{
                $long_term_documents=DB::table('long_term_document')->where('id',$lid)->first();
                $circular=DB::table('upload_data as ud')->leftJoin('long_term_document as ltd','ud.id','=','ltd.upload_id')
                    ->where('ud.id',$long_term_documents->upload_id)
                    ->select(['ud.id','ud.first_img_review as first_page_data','ltd.upload_id'])->first();
                if ($circular && $circular->first_page_data){
                    $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
                }else{
                    $noPreviewPath =  public_path()."/images/no-preview.png";
                    $data = file_get_contents($noPreviewPath);
                    $base64 = 'data:image/png;base64,' . base64_encode($data);
                    $circular->first_page_data = $base64;
                }
            }

            return $this->sendResponse(['circular'=>$circular],'回覧取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
