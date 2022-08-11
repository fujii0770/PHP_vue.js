<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\LongtermIndexUtils;
use App\Http\Utils\TemplateUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\EnvApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Matrix\Exception;


class StoreCircularToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'circular:storeS3 {circular_id} {company_id} {--keyword} {finishedDate} {--keyword_flg} {--folder_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store circular to S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $circular_id = $this->argument('circular_id');
        $mst_company_id = $this->argument('company_id');
        $finishedDateKey = $this->argument('finishedDate');
        // 当月
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }
        $keyword = $this->option('keyword');
        $keyword_flg = $this->option('keyword_flg');
        $folderId = $this->option('folder_id');
        Log::channel('cron-daily')->debug("Start StoreCircularToS3 for circular $circular_id and company $mst_company_id");
        try{
            $path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                . '/' . config('app.server_flg').'/'.$mst_company_id.'/'.$circular_id;

            $windowCircularUser = DB::table("circular_user$finishedDate")->where('circular_id', $circular_id)
                ->where('mst_company_id', $mst_company_id)
                ->where('env_flg', config('app.server_env'))
                ->where('edition_flg', config('app.edition_flg'))
                ->where('server_flg', config('app.server_flg'))
                ->where('del_flg', 0)
                ->orderBy('parent_send_order')
                ->orderBy('child_send_order')
                ->first();

            //申請者の情報を取得する
            $sendCircularUser = DB::table("circular_user$finishedDate")
                ->where('circular_id', $circular_id)
                ->where('parent_send_order', 0)
                ->where('child_send_order', 0)
                ->first();

            if ($windowCircularUser){

                $allCircularUser = DB::table("circular_user$finishedDate")->where('circular_id', $circular_id)
                    ->where('parent_send_order', $windowCircularUser->parent_send_order)
                    ->get();

                $mstUserIds = [];
                foreach ($allCircularUser as $circularUser){
                    $mstUserIds[] = $circularUser->mst_user_id;
                }

                $departmentIds = null;
                if (count($mstUserIds)){
                    $departmentIds = DB::table('mst_user_info')->whereIn('mst_user_id', $mstUserIds)->whereNotNull('mst_department_id')->pluck('mst_department_id')->toArray();
                }

                $query_sub = DB::table("circular$finishedDate as C")
                    ->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
                    ->join("circular_document$finishedDate as D", function($join) use ($mst_company_id){
                        $join->on('C.id', '=', 'D.circular_id');
                        $join->on(function($condition) use ($mst_company_id){
                            $condition->on('confidential_flg', DB::raw('0'));
                            $condition->orOn(function($condition1) use ($mst_company_id){
                                $condition1->on('confidential_flg', DB::raw('1'));
                                $condition1->on('create_company_id', DB::raw($mst_company_id));
                            });
                        });
                        $join->on(function($condition) use ($mst_company_id){
                            $condition->on('origin_document_id', DB::raw('0'));
                            $condition->orOn(function($condition1) use ($mst_company_id){
                                $condition1->on('D.parent_send_order', 'U.parent_send_order');
                            });
                        });
                    })
                    ->select(DB::raw('C.id, U.parent_send_order, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names, SUM(D.file_size) as file_size'))
                    // 宛先に自分自身を設定していた場合の対策としてNOT EXISTS
                    ->whereRaw("((U.email = '$windowCircularUser->email' AND NOT EXISTS (SELECT * FROM circular_user WHERE circular_id = U.circular_id AND email=U.email
                    AND edition_flg = ".config('app.edition_flg')." AND env_flg = ".config('app.server_env')." AND server_flg = ".config('app.server_flg')."
                    AND parent_send_order = 0 AND child_send_order = 0)) OR (C.mst_user_id = '$windowCircularUser->mst_user_id' AND U.parent_send_order = 0 AND U.child_send_order = 0))")
                    ->where('U.edition_flg', config('app.edition_flg'))
                    ->where('U.env_flg', config('app.server_env'))
                    ->where('U.server_flg', config('app.server_flg'))
                    ->groupBy(['C.id', 'U.parent_send_order']);

                $query_sub2 = DB::table("circular_user$finishedDate as E")
                    ->join("circular_user$finishedDate as M", function($join) use ($windowCircularUser){
                        $join->on('E.circular_id', '=', 'M.circular_id');

                        $join->where('M.email', '=', "$windowCircularUser->email");
                        $join->where('M.edition_flg', '=',config('app.edition_flg'));
                        $join->where('M.env_flg', '=',config('app.server_env'));
                        $join->where('M.server_flg', '=',config('app.server_flg'));
                    })
                    ->select(DB::raw('E.circular_id as id, GROUP_CONCAT(E.name ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \',\') as names, GROUP_CONCAT(trim(E.email) ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \',\') as emails'))
                    // 宛先に自分自身を設定していた場合の対策としてNOT EXISTS
                    ->where('E.child_send_order', '!=', 0)
                    ->whereRaw('((E.parent_send_order != 0 AND E.child_send_order = 1) OR (E.parent_send_order = M.parent_send_order))')
                    ->groupBy(['E.circular_id']);

                $circularInfo = DB::table("circular$finishedDate as C")
                    ->leftJoinSub($query_sub, 'D', function ($join) {
                        $join->on('C.id', '=', 'D.id');
                    })
                    ->leftJoinSub($query_sub2, 'E', function ($join) {
                        $join->on('C.id', '=', 'E.id');
                    })
                    ->join("circular_user$finishedDate as U", 'C.id', 'U.circular_id')
                    ->leftjoin("circular_user$finishedDate as A", function($join){
                        $join->on('A.circular_id', '=', 'C.id');
                        $join->on('A.parent_send_order','=',DB::raw("0"));
                        $join->on('A.child_send_order','=',DB::raw("0"));
                    })
                    ->select(DB::raw('C.id, C.applied_date, C.completed_date, D.file_size, D.file_names, IF(U.title IS NULL or trim(U.title) = \'\', D.file_names, U.title) as title,
                                    A.name as sender_name, A.email as sender_email, E.emails AS destination_email, E.names AS destination_name'))
                    ->where('C.id', $circular_id)
                    ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS])
                    ->groupBy(DB::raw('C.id, C.applied_date, C.completed_date, A.name, A.email, U.title, D.file_size, D.file_names, E.emails, E.names'))
                    ->first();
                $boolOtherEnvFlg = false; 
                //本環境の文書データを取得する
                if ($sendCircularUser->edition_flg == config('app.edition_flg') && $sendCircularUser->env_flg == config('app.server_env') && $sendCircularUser->server_flg == config('app.server_flg')){
                    $documents = DB::table("circular_document$finishedDate as D")
                        ->select('file_name', 'file_data')
                        ->leftJoin("document_data$finishedDate as DD", 'D.id', '=', 'DD.circular_document_id')
                        ->where('circular_id', $circular_id)
                        ->where(function ($query) use ($windowCircularUser){
                            $query->where('origin_document_id', 0);
                            $query->orWhere('parent_send_order', $windowCircularUser->parent_send_order);
                        })
                        ->orderBy('document_no')
                        ->get();

                    $countFilename = [];
                    foreach ($documents as $document) {
                        if ($document->file_data){
                            $document->file_data = base64_decode(AppUtils::decrypt($document->file_data));

                            $filename = mb_substr($document->file_name, mb_strrpos($document->file_name,'/'));
                            $filename = mb_substr($filename, 0, mb_strrpos($document->file_name,'.'));
                            if(key_exists($filename, $countFilename)) {
                                $countFilename[$filename]++;
                                $filename = $filename.' ('.$countFilename[$filename].') ';
                            } else {
                                $countFilename[$filename] = 0;
                            }

                            Storage::disk('s3')->put($path.'/'.$filename.'.pdf', $document->file_data, 'pub');
                        }
                    }
                }else{
                    //PAC_5-1655 元の環境の回覧idを取得します。
                    $circular = DB::table("circular$finishedDate")
                        ->where('id', $circular_id)
                        ->select('origin_circular_id')
                        ->first();

                    // 他環境処理を呼び出し
                    $envClient = EnvApiUtils::getAuthorizeClient($sendCircularUser->env_flg,$sendCircularUser->server_flg);
                    if (!$envClient){
                        throw new \Exception('Cannot connect to Env Api');
                    }
                    $origin_env_circular = [['circular_id' => $circular_id, 'origin_circular_id' => $circular->origin_circular_id]];
                    //他の環境に保存されている文書データを取得する
                    $response = $envClient->post("getEnvDocuments",[
                        RequestOptions::JSON => ['create_company_id' => $mst_company_id, 'origin_env_flg' => $sendCircularUser->env_flg, 'origin_server_flg' => $sendCircularUser->server_flg,
                            'origin_edition_flg' => $sendCircularUser->edition_flg, 'circulars' => $origin_env_circular]
                    ]);

                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                        Log::channel('cron-daily')->error($response->getBody());
                        throw new \Exception('Cannot get env documents');
                    }
                    $result = json_decode($response->getBody(), true);

                    $documents = $result['document_data'];
                    $countFilename = [];
                    foreach ($documents as $document) {
                        if ($document['file_data']){
                            $document['file_data'] = base64_decode(AppUtils::decrypt($document['file_data']));

                            $filename = mb_substr($document['file_name'], mb_strrpos($document['file_name'],'/'));
                            $filename = mb_substr($filename, 0, mb_strrpos($document['file_name'],'.'));
                            if(key_exists($filename, $countFilename)) {
                                $countFilename[$filename]++;
                                $filename = $filename.' ('.$countFilename[$filename].') ';
                            } else {
                                $countFilename[$filename] = 0;
                            }

                            Storage::disk('s3')->put($path.'/'.$filename.'.pdf', $document['file_data'], 'pub');
                        }
                    }
                    $boolOtherEnvFlg = true;
                }
                // find to copy  all Attachment
                $mixedJson = $this->copyCurrentCircularAllAttachment($circular_id,$finishedDate,$mst_company_id);
                // error
                if(!empty($mixedJson) && isset($mixedJson['msg'])){
                    Log::info($mixedJson['msg']);
                    throw new \Exception($mixedJson['msg']);
                }

                //タイムスタンプ付署名があるかどうかを判断する
                $is_time_stamp =  DB::table('circular_document as cd')
                    ->join('time_stamp_info as tsi','tsi.circular_document_id','cd.id')
                    ->where('cd.circular_id',$circular_id)
                    ->count();

                DB::beginTransaction();
                if ($circularInfo){
                    if($keyword_flg === 0){
                        $newId = DB::table('long_term_document')->insertGetId([
                            'circular_id' => $circular_id,
                            'mst_company_id' => $mst_company_id,
                            'sender_name' => $circularInfo->sender_name,
                            'sender_email' => $circularInfo->sender_email,
                            'destination_email' => $circularInfo->destination_email,
                            'destination_name' => $circularInfo->destination_name,
                            'file_name' => $circularInfo->file_names,
                            'file_size' => $circularInfo->file_size + ($mixedJson['file_size'] ?? 0),
                            'request_at' => $circularInfo->applied_date,
                            'completed_at' => $circularInfo->completed_date,
                            'title' => $circularInfo->title,
                            'circular_attachment_json' => !empty($mixedJson['data']) ? $mixedJson['data'] : '',
                            'create_at' => Carbon::now(),
                            'create_user' => $windowCircularUser->email,
                            'add_timestamp_automatic_date' => $is_time_stamp ? $circularInfo->completed_date : null,
                            'long_term_folder_id' => $folderId,
                        ]);
                    }else{
                        $newId = DB::table('long_term_document')->insertGetId([
                            'circular_id' => $circular_id,
                            'mst_company_id' => $mst_company_id,
                            'sender_name' => $circularInfo->sender_name,
                            'sender_email' => $circularInfo->sender_email,
                            'destination_email' => $circularInfo->destination_email,
                            'destination_name' => $circularInfo->destination_name,
                            'file_name' => $circularInfo->file_names,
                            'file_size' => $circularInfo->file_size + ($mixedJson['file_size'] ?? 0),
                            'request_at' => $circularInfo->applied_date,
                            'completed_at' => $circularInfo->completed_date,
                            'title' => $circularInfo->title,
                            'circular_attachment_json' => !empty($mixedJson['data']) ? $mixedJson['data'] : '',
                            'create_at' => Carbon::now(),
                            'create_user' => $windowCircularUser->email,
                            'keyword' => $keyword,
                            'add_timestamp_automatic_date' => $is_time_stamp ? $circularInfo->completed_date : null,
                            'long_term_folder_id' => $folderId,
                        ]);
                    }

                    $circular = DB::table("circular$finishedDate")
                        ->where('id', $circular_id)
                        ->select('origin_circular_id')
                        ->first();

                    $this->copyCircularDataToLongTerm($circular_id,$finishedDate,$newId,$boolOtherEnvFlg,$sendCircularUser,$mst_company_id,$circular->origin_circular_id);
                    if ($departmentIds){
                        $departmentIds = array_unique($departmentIds);

                        $insertDatas = [];
                        foreach ($departmentIds as $departmentId){
                            $insertDatas[] = [
                                'long_term_document_id'=>$newId,
                                'create_at' => Carbon::now(),
                                'department_id' => $departmentId,
                                'create_user' => $windowCircularUser->email,
                            ];
                        }
                        DB::table('long_term_department')->insert($insertDatas);
                    }

                    //PAC_5-1784テンプレート項目インデックス追加処理
                    $template_flg = DB::table('template_input_data')->where('circular_id', $circular_id)->exists();
                    if($template_flg) {
                        $input_datas = DB::table('template_input_data')->where('circular_id', $circular_id)->get();

                        foreach($input_datas as $input_data) {
                            $data_type = $input_data->data_type;
                            if($input_data -> template_placeholder_data !== "" && $input_data -> template_placeholder_data !== null) {
                                //同一企業ですでに登録済みのテンプレート項目がある場合
                                if($data_type === TemplateUtils::DATE_TYPE) {
                                    $lonterm_exists = DB::table('mst_longterm_index')
                                        ->where('index_name', $input_data->template_placeholder_name)
                                        ->where('template_flg',1)
                                        ->where('mst_company_id',$mst_company_id)
                                        ->where('data_type',LongtermIndexUtils::DATE_TYPE)
                                        ->get();

                                    if(!empty($lonterm_exists[0])) {
                                        $long_term_id = DB::table('longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'circular_id' => $circular_id,
                                            'longterm_index_id' => $lonterm_exists[0]->id,
                                            'date_value' => $input_data->date_data,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user
                                        ]);

                                        DB::table('mst_longterm_index')
                                            ->where('id', $lonterm_exists[0]->id)
                                            ->update(['circular_id' => $circular_id]);
                                    }else{
                                        $mst_longterm_id = DB::table('mst_longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'index_name' => $input_data->template_placeholder_name,
                                            'data_type' => LongtermIndexUtils::DATE_TYPE,
                                            'permission' => 1,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user,
                                            'template_flg' => 1,
                                            'circular_id' => $circular_id,
                                            'template_valid_flg' => 0,
                                            'auth_flg' => 0
                                        ]);

                                        $long_term_id = DB::table('longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'circular_id' => $circular_id,
                                            'longterm_index_id' => $mst_longterm_id,
                                            'date_value' => $input_data->date_data,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user
                                        ]);
                                    }
                                }else if($data_type === TemplateUtils::NUMERIC_TYPE){
                                    $lonterm_exists = DB::table('mst_longterm_index')
                                        ->where('index_name', $input_data->template_placeholder_name)
                                        ->where('template_flg',1)
                                        ->where('mst_company_id',$mst_company_id)
                                        ->where('data_type',LongtermIndexUtils::NUMERIC_TYPE)
                                        ->get();

                                    if(!empty($lonterm_exists[0])) {
                                        $long_term_id = DB::table('longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'circular_id' => $circular_id,
                                            'longterm_index_id' => $lonterm_exists[0]->id,
                                            'num_value' => $input_data->num_data,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user
                                        ]);

                                        DB::table('mst_longterm_index')
                                            ->where('id', $lonterm_exists[0]->id)
                                            ->update(['circular_id' => $circular_id]);
                                    }else{
                                        $mst_longterm_id = DB::table('mst_longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'index_name' => $input_data->template_placeholder_name,
                                            'data_type' => LongtermIndexUtils::NUMERIC_TYPE,
                                            'permission' => 1,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user,
                                            'template_flg' => 1,
                                            'circular_id' => $circular_id,
                                            'template_valid_flg' => 0,
                                            'auth_flg' => 0
                                        ]);

                                        $long_term_id = DB::table('longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'circular_id' => $circular_id,
                                            'longterm_index_id' => $mst_longterm_id,
                                            'num_value' => $input_data->num_data,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user
                                        ]);
                                    }
                                }else{
                                    $lonterm_exists = DB::table('mst_longterm_index')
                                        ->where('index_name', $input_data->template_placeholder_name)
                                        ->where('template_flg',1)
                                        ->where('mst_company_id',$mst_company_id)
                                        ->where('data_type',LongtermIndexUtils::STRING_TYPE)
                                        ->get();


                                    if(!empty($lonterm_exists[0])) {
                                        $long_term_id = DB::table('longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'circular_id' => $circular_id,
                                            'longterm_index_id' => $lonterm_exists[0]->id,
                                            'string_value' => $input_data->text_data,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user
                                        ]);

                                        DB::table('mst_longterm_index')
                                            ->where('id', $lonterm_exists[0]->id)
                                            ->update(['circular_id' => $circular_id]);
                                    }else{
                                        $mst_longterm_id = DB::table('mst_longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'index_name' => $input_data->template_placeholder_name,
                                            'data_type' => LongtermIndexUtils::STRING_TYPE,
                                            'permission' => 1,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user,
                                            'template_flg' => 1,
                                            'circular_id' => $circular_id,
                                            'template_valid_flg' => 0,
                                            'auth_flg' => 0
                                        ]);

                                        $long_term_id = DB::table('longterm_index')->insertGetId([
                                            'mst_company_id' => $mst_company_id,
                                            'mst_user_id' => $input_data->user_id,
                                            'circular_id' => $circular_id,
                                            'longterm_index_id' => $mst_longterm_id,
                                            'string_value' => $input_data->text_data,
                                            'create_at' => Carbon::now(),
                                            'create_user' => $input_data->create_user
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    // PAC_5-2495 帳票発行
                    $frm_invoice_flg = DB::table('frm_invoice_data')->where('circular_id', $circular_id)->exists();
                    $frm_others_flg = DB::table('frm_others_data')->where('circular_id', $circular_id)->exists();
                    if($frm_invoice_flg){
                        $this->formIssuanceIndex($circular_id, $mst_company_id, 'frm_invoice_data');
                    }
                    if($frm_others_flg){
                        $this->formIssuanceIndex($circular_id, $mst_company_id, 'frm_others_data');
                    }

                }
                DB::commit();
                $this->info("0");
                Log::channel('cron-daily')->debug("StoreCircularToS3 for circular $circular_id and company $mst_company_id success");
            }else{
                Log::channel('cron-daily')->warning("Cannot StoreCircularToS3 for circular $circular_id and company $mst_company_id by without window user");
                $this->error("0");
            }
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::channel('cron-daily')->warning("StoreCircularToS3 for circular $circular_id and company $mst_company_id failed");
            Log::channel('cron-daily')->warning($ex->getMessage().$ex->getTraceAsString());

            $this->error(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
        Log::channel('cron-daily')->debug("StoreCircularToS3 for circular $circular_id and company $mst_company_id finished");
    }

    /**
     * 帳票の項目で入力された項目が長期保管のインデックスの検索項目
     * @param $circular_id
     * @param $mst_company_id
     * @param $table_name
     */
    private function formIssuanceIndex($circular_id, $mst_company_id, $table_name)
    {
        $form_issuance_data = DB::table($table_name." as frm")->where('circular_id', $circular_id)
            ->select([
                'circular.mst_user_id as user_id',
                'frm.frm_data',
                'frm.create_user'
            ])
            ->join("circular", "frm.circular_id","=","circular.id")
            ->first();

        $input_datas = [];
        if(isset($form_issuance_data->frm_data) && $form_issuance_data->frm_data != null){
            $frm_data = json_decode($form_issuance_data->frm_data);
            foreach ($frm_data as $key => $value){
                if($this->isDate($value)) {
                    $input_data = [
                        "user_id" => $form_issuance_data->user_id,
                        "create_user" => $form_issuance_data->create_user,
                        "name" => $key,
                        "data" => $value,
                        "data_type" => TemplateUtils::DATE_TYPE,
                    ];
                } else if(is_numeric($value)) {
                    $input_data = [
                        "user_id" => $form_issuance_data->user_id,
                        "create_user" => $form_issuance_data->create_user,
                        "name" => $key,
                        "data" => $value,
                        "data_type" => TemplateUtils::NUMERIC_TYPE,
                    ];
                } else {
                    $input_data = [
                        "user_id" => $form_issuance_data->user_id,
                        "create_user" => $form_issuance_data->create_user,
                        "name" => $key,
                        "data" => $value,
                        "data_type" => TemplateUtils::STRING_TYPE,
                    ];
                }
                $input_datas[] = $input_data;
            }
        }

        foreach($input_datas as $input_data) {
            $data_type = $input_data['data_type'];
            if($input_data['data'] !== "" && $input_data['data'] !== null) {
                //同一企業ですでに登録済みのテンプレート項目がある場合
                if($data_type === TemplateUtils::DATE_TYPE) {
                    $lonterm_exists = DB::table('mst_longterm_index')
                        ->where('index_name', $input_data['name'])
                        ->where('template_flg',2)
                        ->where('mst_company_id',$mst_company_id)
                        ->where('data_type',LongtermIndexUtils::DATE_TYPE)
                        ->get();

                    if(!empty($lonterm_exists[0])) {
                        $long_term_id = DB::table('longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'circular_id' => $circular_id,
                            'longterm_index_id' => $lonterm_exists[0]->id,
                            'date_value' => $input_data['data'],
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user']
                        ]);

                        DB::table('mst_longterm_index')
                            ->where('id', $lonterm_exists[0]->id)
                            ->update(['circular_id' => $circular_id]);
                    }else{
                        $mst_longterm_id = DB::table('mst_longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'index_name' => $input_data['name'],
                            'data_type' => LongtermIndexUtils::DATE_TYPE,
                            'permission' => 1,
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user'],
                            'template_flg' => 2,
                            'circular_id' => $circular_id,
                            'template_valid_flg' => 0,
                            'auth_flg' => 0
                        ]);

                        $long_term_id = DB::table('longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'circular_id' => $circular_id,
                            'longterm_index_id' => $mst_longterm_id,
                            'date_value' => $input_data['data'],
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user']
                        ]);
                    }
                }else if($data_type === TemplateUtils::NUMERIC_TYPE){
                    $lonterm_exists = DB::table('mst_longterm_index')
                        ->where('index_name', $input_data['name'])
                        ->where('template_flg',2)
                        ->where('mst_company_id',$mst_company_id)
                        ->where('data_type',LongtermIndexUtils::NUMERIC_TYPE)
                        ->get();

                    if(!empty($lonterm_exists[0])) {
                        $long_term_id = DB::table('longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'circular_id' => $circular_id,
                            'longterm_index_id' => $lonterm_exists[0]->id,
                            'num_value' => $input_data['data'],
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user']
                        ]);

                        DB::table('mst_longterm_index')
                            ->where('id', $lonterm_exists[0]->id)
                            ->update(['circular_id' => $circular_id]);
                    }else{
                        $mst_longterm_id = DB::table('mst_longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'index_name' => $input_data['name'],
                            'data_type' => LongtermIndexUtils::NUMERIC_TYPE,
                            'permission' => 1,
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user'],
                            'template_flg' => 2,
                            'circular_id' => $circular_id,
                            'template_valid_flg' => 0,
                            'auth_flg' => 0
                        ]);

                        $long_term_id = DB::table('longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'circular_id' => $circular_id,
                            'longterm_index_id' => $mst_longterm_id,
                            'num_value' => $input_data['data'],
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user']
                        ]);
                    }
                }else{
                    $lonterm_exists = DB::table('mst_longterm_index')
                        ->where('index_name', $input_data['name'])
                        ->where('template_flg',2)
                        ->where('mst_company_id',$mst_company_id)
                        ->where('data_type',LongtermIndexUtils::STRING_TYPE)
                        ->get();

                    if(!empty($lonterm_exists[0])) {
                        $long_term_id = DB::table('longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'circular_id' => $circular_id,
                            'longterm_index_id' => $lonterm_exists[0]->id,
                            'string_value' => $input_data['data'],
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user']
                        ]);

                        DB::table('mst_longterm_index')
                            ->where('id', $lonterm_exists[0]->id)
                            ->update(['circular_id' => $circular_id]);
                    }else{
                        $mst_longterm_id = DB::table('mst_longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'index_name' => $input_data['name'],
                            'data_type' => LongtermIndexUtils::STRING_TYPE,
                            'permission' => 1,
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user'],
                            'template_flg' => 2,
                            'circular_id' => $circular_id,
                            'template_valid_flg' => 0,
                            'auth_flg' => 0
                        ]);

                        $long_term_id = DB::table('longterm_index')->insertGetId([
                            'mst_company_id' => $mst_company_id,
                            'mst_user_id' => $input_data['user_id'],
                            'circular_id' => $circular_id,
                            'longterm_index_id' => $mst_longterm_id,
                            'string_value' => $input_data['data'],
                            'create_at' => Carbon::now(),
                            'create_user' => $input_data['create_user']
                        ]);
                    }
                }
            }
        }
    }

    private function isDate($date) {
        // e.g. 2020/01/01 is true
        if(preg_match('/^(\d{1,4})\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])$/', $date)) {
            return true;
            // e.g. 2020/01/01 00:00:00 is true
        } else if(preg_match('/^(\d{1,4})\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01]) ([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9]$/',$date)) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    /**
     * copy AllAttachment
     * @param $intCircularID
     * @param $finishedDate
     * @return false|int|string
     */
    private function copyCurrentCircularAllAttachment($intCircularID, $finishedDate,$mst_company_id)
    {
        try {
            // ALL FIlE SIZE
            $intTotalSize = 0;
            // Get current circular
            $objCircular = DB::table("circular$finishedDate")
                ->where('id', $intCircularID)
                ->first();
            //特設サイトの場合、
            if($objCircular ->special_site_flg){
                $objCircularUser = DB::table("circular_user$finishedDate")
                    ->where('circular_id', $intCircularID)
                    ->orderBy('parent_send_order', 'desc')
                    ->orderBy('child_send_order')
                    ->first();
            }else{
                $objCircularUser = DB::table("circular_user$finishedDate")
                    ->where('circular_id', $intCircularID)
                    ->where('child_send_order', 0)
                    ->where('parent_send_order', 0)
                    ->first();
            }
            $objCurrentCompanyCircularUser = DB::table("circular_user$finishedDate")
                ->where("mst_company_id",$mst_company_id)
                ->where("circular_id",$intCircularID)
                ->where('edition_flg', '=',config('app.edition_flg'))
                ->where('env_flg', '=',config('app.server_env'))
                ->where('server_flg', '=',config('app.server_flg'))
                ->first();
            ;
            Log::info("------------------   START    ------------------------");
            $folderPath = config('app.server_env') ? config('app.k5_storage_attachment_root_folder') : config('app.s3_storage_attachment_root_folder');

            $folderPath .= ("/".config("app.long_term_back_attachment_folder_pre"));
            $arrRealPath = [];
            if ($objCircularUser->edition_flg == config('app.edition_flg') && ($objCircularUser->env_flg != config('app.server_env') || $objCircularUser->server_flg != config('app.server_flg'))) {

                Log::info("------------------   other ENV SERVER    ------------------------");
                Log::info($objCircularUser->env_flg . $objCircularUser->server_flg);
                // Get Origin Service
                $envClient = EnvApiUtils::getAuthorizeClient($objCircularUser->env_flg, $objCircularUser->server_flg);
                if (!$envClient) {
                    Log::info("------------------  Get Other ENV SERVER stop   ------------------------");
                    return ['msg' => '添付ファイル情報の取得に失敗しました'];
                }

                $response = $envClient->get("circulars/find_other_attachment_info", [
                    RequestOptions::JSON => [
                        'circular_id' => $objCircular->origin_circular_id,
                        'user_email' => $objCircular->create_user,
                        'opposite_user_mail' => $objCurrentCompanyCircularUser->email,
                        'finishedDate' => $finishedDate,
                    ]
                ]);

                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                    Log::info($response->getBody());
                    Log::info("------------------   Get Other ENV SERVER ERROR    ------------------------");
                    return ['msg' => 'Get Other ENV SERVER ERROR'];
                }
                $arrAllAttachment = json_decode($response->getBody())->data;

            } else {
                Log::info("Get Current ENV SERVER Info----------------------------");
                $arrAllAttachment = DB::table("circular_attachment")
                    ->where("circular_id", $intCircularID)
                    ->where("status", CircularAttachmentUtils::ATTACHMENT_CHECK_SUCCESS_STATUS)
                    ->where(function($query) use ($mst_company_id){
                            $query->where(function($query1) use($mst_company_id){
                                $query1->where('confidential_flg',CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_FALSE);
                                $query1->whereNotIn('create_company_id',[$mst_company_id]);
                            });
                            $query->orWhere(function($query2) use($mst_company_id){
                                $query2->where('create_company_id',$mst_company_id);
                            });
                    })
                    ->get()->toArray();
            }
            if (empty($arrAllAttachment)) {
                Log::info("Attachment is emptty ----------------------------");
                return ['data' => [],'file_size' => $intTotalSize];
            }

            foreach($arrAllAttachment as $item){
                $intTotalSize += $item->file_size;
            }

            $attachment_total_size = DB::table('circular_attachment')
                ->select(DB::raw(' SUM(file_size) as total_size'))
                ->where('create_company_id',$mst_company_id)
                ->where('edition_flg',$objCurrentCompanyCircularUser->edition_flg)
                ->where('env_flg',$objCurrentCompanyCircularUser->env_flg)
                ->where('server_flg',$objCurrentCompanyCircularUser->server_flg)
                ->where('circular_id',$intCircularID)
                ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                ->value('total_size');

            $constraints = DB::table('mst_constraints')
                ->select('max_total_attachment_size','max_attachment_count','max_attachment_size')
                ->where('mst_company_id',$mst_company_id)
                ->first();

            if (($attachment_total_size + $intTotalSize) >= ($constraints->max_total_attachment_size * 1024 * 1024 * 1024)){
                return [
                    'msg' =>__('message.warning.attachment_request.upload_attachment_size_max',['max_total_attachment_size' => $constraints->max_total_attachment_size])
                ];
            }

            $server_url_tmp = sprintf("%s/%s/%s/%s", $folderPath, $objCurrentCompanyCircularUser->edition_flg . $objCurrentCompanyCircularUser->env_flg . $objCurrentCompanyCircularUser->server_flg, $mst_company_id, $intCircularID);
            $server_url = config('filesystems.prefix_path') . '/' . $server_url_tmp;
            Log::info("server_url---------------------------- $server_url");
            Log::info("map start ---------------------------- ");

            $isDirectory = Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5':'s3')->exists($server_url);
            Log::info("exists : $isDirectory ");
            if(!$isDirectory){
                Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5':'s3')->makeDirectory($server_url);
            }
            foreach ($arrAllAttachment as $item) {

                // Get this file name
                $file_name = $item->id . '_' . substr(md5(time()), 0, 8) . '.' . substr(strrchr($item->file_name, '.'), 1);

                $arrTmp = [
                    'server_url' => $server_url .'/'. $file_name,
                    'server_path' => $server_url,
                    'file_path_name' => $file_name,
                    'server_flg' => $item->server_flg,
                    'env_flg' => $item->env_flg,
                    'edition_flg' => $item->edition_flg,
                    'file_name' => $item->file_name,
                    'file_size' => $item->file_size,
                    'name' => $item->name,
                    'create_user' => $item->create_user,
                    'create_at' => $item->create_at,
                    'id' => $item->id,
                    'server_path_url' => $item->server_url,
                    'company_id'=>$item->create_company_id,
                    'status'=>$item->status,
                    'confidential_flg'=>$item->confidential_flg,
                    'create_user_id'=>$item->create_user_id,
                ];
                if(isset($item->file_data)){
                    $file_data = $item->file_data;
                }else{
                    $file_data = chunk_split(base64_encode(Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5':'s3')->get($item->server_url)));
                }
                // decode
                $file_data = base64_decode($file_data);
                // K5 SERVER
                if (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5) {
                    Storage::disk('k5')->put($server_url.'/'.$file_name, $file_data);
                    $arrRealPath[] = array_merge($arrTmp, [
                        'type' => "k5",
                    ]);
                }
                // AWS SERVER
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                    Storage::disk('s3')->put($server_url.'/'.$file_name, $file_data,'pub');
                    $arrRealPath[] = array_merge($arrTmp, [
                        'type' => "s3",
                    ]);
                }
            }
        } catch (\Exception $ex) {
            Log::info("message :" . $ex->getMessage() . " Line :" . $ex->getLine(). " Line :" . $ex->getFile());
            return ['msg' => '添付ファイル情報の取得に失敗しました'];
        }
        Log::info("map END---------------------------- ");
        return ['data' => $arrRealPath ? json_encode($arrRealPath) : '','file_size' => $intTotalSize];
    }

    private function copyCircularDataToLongTerm($circular_id,$finishedDate,$longTermId,$boolOtherEnvFlg,$sendCircularUser,$mst_company_id,$intCircular)
    {
        try {
            $circular = DB::table("circular$finishedDate")
                ->where('id', $circular_id)
                ->first();
            if(!$circular){
                Log::channel('cron-daily')->warning('copyCircularDataToLongTerm circular not found');
                return;
            }
            $circular_user=DB::table("circular_user$finishedDate")->where('circular_id',$circular->id)->get()->toArray();
            $objCircularDocumentData = DB::table("circular_document$finishedDate")->where('circular_id',$circular->id)->get();

            $circular_documents= DB::table("circular_document$finishedDate")->where('circular_id',$circular->id)->get()->toArray();
            $circular_operation_history=DB::table('circular_operation_history')->where('circular_id',$circular->id)->get()->toArray();
            if(empty($circular_documents)){
                Log::channel('cron-daily')->warning('copyCircularDataToLongTerm circular_document not found');
                return;
            }
            $insert_long_term_users=[];
            $insert_long_term_document_comment=[];
            $insert_long_term_circular_operation_history=[];
            $insert_long_term_stamp_infos=[];
            $insert_long_term_text_infos=[];
            DB::table('long_term_circular')->insert([
                'id'=>$circular->id,
                'mst_user_id'=>$circular->mst_user_id,
                'access_code_flg'=>$circular->access_code_flg,
                'access_code'=>$circular->access_code,
                'outside_access_code_flg'=>$circular->outside_access_code_flg,
                'outside_access_code'=>$circular->outside_access_code,
                'hide_thumbnail_flg'=>$circular->hide_thumbnail_flg,
                're_notification_day'=>$circular->re_notification_day,
                'circular_status'=>$circular->circular_status,
                'create_at'=>$circular->create_at,
                'create_user'=>$circular->create_user,
                'update_at'=>$circular->update_at,
                'update_user'=>$circular->update_user,
                'address_change_flg'=>$circular->address_change_flg,
                'first_page_data'=>$circular->first_page_data,
                'env_flg'=>$circular->env_flg,
                'edition_flg'=>$circular->edition_flg,
                'server_flg'=>$circular->server_flg,
                'origin_circular_id'=>$circular->origin_circular_id,
                'current_aws_circular_id'=>$circular->current_aws_circular_id,
                'current_k5_circular_id'=>$circular->current_k5_circular_id,
                'applied_date'=>$circular->applied_date,
                'completed_date'=>$circular->completed_date,
                'completed_copy_flg'=>$circular->completed_copy_flg,
                'has_signature'=>$circular->has_signature,
                'final_updated_date'=>$circular->final_updated_date,
                'special_site_flg'=>$circular->special_site_flg,
            ]);
            foreach ($circular_user as $k=>$v){
                $insert_long_term_users[$k]['id']=$v->id;
                $insert_long_term_users[$k]['circular_id']=$v->circular_id;
                $insert_long_term_users[$k]['parent_send_order']=$v->parent_send_order;
                $insert_long_term_users[$k]['env_flg']=$v->env_flg;
                $insert_long_term_users[$k]['edition_flg']=$v->edition_flg;
                $insert_long_term_users[$k]['server_flg']=$v->server_flg;
                $insert_long_term_users[$k]['mst_company_id']=$v->mst_company_id;
                $insert_long_term_users[$k]['email']=$v->email;
                $insert_long_term_users[$k]['name']=$v->name;
                $insert_long_term_users[$k]['title']=$v->title;
                $insert_long_term_users[$k]['text']=$v->text;
                $insert_long_term_users[$k]['circular_status']=$v->circular_status;
                $insert_long_term_users[$k]['create_at']=$v->create_at;
                $insert_long_term_users[$k]['create_user']=$v->create_user;
                $insert_long_term_users[$k]['update_at']=$v->update_at;
                $insert_long_term_users[$k]['update_user']=$v->update_user;
                $insert_long_term_users[$k]['child_send_order']=$v->child_send_order;
                $insert_long_term_users[$k]['del_flg']=$v->del_flg;
                $insert_long_term_users[$k]['mst_user_id']=$v->mst_user_id;
                $insert_long_term_users[$k]['origin_circular_url']=$v->origin_circular_url;
                $insert_long_term_users[$k]['return_flg']=$v->return_flg;
                $insert_long_term_users[$k]['mst_company_name']=$v->mst_company_name;
                $insert_long_term_users[$k]['received_date']=$v->received_date;
                $insert_long_term_users[$k]['sent_date']=$v->sent_date;
                $insert_long_term_users[$k]['sender_name']=$v->sender_name;
                $insert_long_term_users[$k]['sender_email']=$v->sender_email;
                $insert_long_term_users[$k]['receiver_name']=$v->receiver_name;
                $insert_long_term_users[$k]['receiver_email']=$v->receiver_email;
                $insert_long_term_users[$k]['receiver_name_email']=$v->receiver_name_email;
                $insert_long_term_users[$k]['receiver_title']=$v->receiver_title;
                $insert_long_term_users[$k]['stamp_flg']=$v->stamp_flg;
                $insert_long_term_users[$k]['special_site_receive_flg']=$v->special_site_receive_flg;
                $insert_long_term_users[$k]['plan_id']=$v->plan_id;
                $insert_long_term_users[$k]['return_send_back']=$v->return_send_back;
                $insert_long_term_users[$k]['node_flg']=$v->node_flg;
                $insert_long_term_users[$k]['is_skip']=$v->is_skip;
                unset($circular_user[$k]);
            }
            DB::table('long_term_circular_user')->insert($insert_long_term_users);
            
            if($boolOtherEnvFlg === true){
                DB::table("long_term_document")->where('id',$longTermId)->update([
                    'is_other_env_circular_flg' => 1
                ]);
                $this->handlerOtherEnvCircularData($objCircularDocumentData,$longTermId,$circular->id,$intCircular,$sendCircularUser,$mst_company_id,$finishedDate);
                return;
            }
            foreach ($circular_operation_history as $k=>$v){
                $insert_long_term_circular_operation_history[$k]['id']=$v->id;
                $insert_long_term_circular_operation_history[$k]['long_term_document_id']=$longTermId;
                $insert_long_term_circular_operation_history[$k]['circular_id']=$v->circular_id;
                $insert_long_term_circular_operation_history[$k]['operation_email']=$v->operation_email;
                $insert_long_term_circular_operation_history[$k]['operation_name']=$v->operation_name;
                $insert_long_term_circular_operation_history[$k]['acceptor_email']=$v->acceptor_email;
                $insert_long_term_circular_operation_history[$k]['acceptor_name']=$v->acceptor_name;
                $insert_long_term_circular_operation_history[$k]['circular_status']=$v->circular_status;
                $insert_long_term_circular_operation_history[$k]['create_at']=$v->create_at;
                $insert_long_term_circular_operation_history[$k]['is_skip']=$v->is_skip;
                $insert_long_term_circular_operation_history[$k]['circular_document_id']= 0;
                $insert_long_term_circular_operation_history[$k]['file_name']='';
                $insert_long_term_circular_operation_history[$k]['file_size']=0;
                if(!empty($v->circular_document_id)){
                    $objCD = $objCircularDocumentData->where("id",$v->circular_document_id)->first();
                    if(isset($objCD->id)){
                        $insert_long_term_circular_operation_history[$k]['circular_document_id']= $objCD->id;
                        $insert_long_term_circular_operation_history[$k]['file_name']=$objCD->file_name;
                        $insert_long_term_circular_operation_history[$k]['file_size']=$objCD->file_size;
                    }
                }
                unset($circular_operation_history[$k]);
            }
            DB::table('long_term_circular_operation_history')->insert($insert_long_term_circular_operation_history);
            unset($insert_long_term_circular_operation_history);
            foreach ($circular_documents as $circular_document){
                $document_comment=DB::table('document_comment_info')->where('circular_document_id',$circular_document->id)->get()->toArray();
                $stamp_info=DB::table('stamp_info')->where('circular_document_id',$circular_document->id)->get()->toArray();
                $text_info=DB::table('text_info')->where('circular_document_id',$circular_document->id)->get()->toArray();
                foreach ($document_comment as $k=>$v){
                    $insert_long_term_document_comment[$k]['id']=$v->id;
                    $insert_long_term_document_comment[$k]['long_term_document_id']=$longTermId;
                    $insert_long_term_document_comment[$k]['circular_document_id']=$v->circular_document_id;
                    $insert_long_term_document_comment[$k]['long_term_operation_id']=$v->circular_operation_id;
                    $insert_long_term_document_comment[$k]['parent_send_order']=$v->parent_send_order;
                    $insert_long_term_document_comment[$k]['name']=$v->name;
                    $insert_long_term_document_comment[$k]['email']=$v->email;
                    $insert_long_term_document_comment[$k]['text']=$v->text;
                    $insert_long_term_document_comment[$k]['private_flg']=$v->private_flg;
                    $insert_long_term_document_comment[$k]['create_at']=$v->create_at;
                    unset($document_comment[$k]);
                }
                DB::table('long_term_document_comment_info')->insert($insert_long_term_document_comment);
                foreach ($stamp_info as $k=>$v){
                    $insert_long_term_stamp_infos[$k]['id']=$v->id;
                    $insert_long_term_stamp_infos[$k]['long_term_document_id']=$longTermId;
                    $insert_long_term_stamp_infos[$k]['circular_document_id']=$v->circular_document_id;
                    $insert_long_term_stamp_infos[$k]['long_term_operation_id']=$v->circular_operation_id;
                    $insert_long_term_stamp_infos[$k]['mst_assign_stamp_id']=$v->mst_assign_stamp_id;
                    $insert_long_term_stamp_infos[$k]['parent_send_order']=$v->parent_send_order;
                    $insert_long_term_stamp_infos[$k]['stamp_image']=$v->stamp_image;
                    $insert_long_term_stamp_infos[$k]['name']=$v->name;
                    $insert_long_term_stamp_infos[$k]['email']=$v->email;
                    $insert_long_term_stamp_infos[$k]['bizcard_id']=$v->bizcard_id;
                    $insert_long_term_stamp_infos[$k]['env_flg']=$v->env_flg;
                    $insert_long_term_stamp_infos[$k]['server_flg']=$v->server_flg;
                    $insert_long_term_stamp_infos[$k]['edition_flg']=$v->edition_flg;
                    $insert_long_term_stamp_infos[$k]['info_id']=$v->info_id;
                    $insert_long_term_stamp_infos[$k]['file_name']=$v->file_name;
                    $insert_long_term_stamp_infos[$k]['create_at']=$v->create_at;
                    $insert_long_term_stamp_infos[$k]['time_stamp_permission']=$v->time_stamp_permission;
                    $insert_long_term_stamp_infos[$k]['serial']=$v->serial;
                    unset($stamp_info[$k]);
                }
                DB::table('long_term_stamp_info')->insert($insert_long_term_stamp_infos);
                foreach ($text_info as $k=>$v){
                    $insert_long_term_text_infos[$k]['id']=$v->id;
                    $insert_long_term_text_infos[$k]['long_term_document_id']=$longTermId;
                    $insert_long_term_text_infos[$k]['circular_document_id']=$v->circular_document_id;
                    $insert_long_term_text_infos[$k]['circular_operation_id']=$v->circular_operation_id;
                    $insert_long_term_text_infos[$k]['text']=$v->text;
                    $insert_long_term_text_infos[$k]['name']=$v->name;
                    $insert_long_term_text_infos[$k]['email']=$v->email;
                    $insert_long_term_text_infos[$k]['create_at']=$v->create_at;
                    unset($text_info[$k]);
                }
                DB::table('long_term_text_info')->insert($insert_long_term_text_infos);

            }
            unset($insert_long_term_text_infos);
            unset($insert_long_term_stamp_infos);
            unset($insert_long_term_document_comment);
        }catch (\Exception $ex){
            throw $ex;
        }
    }
    
    private function handlerOtherEnvCircularData($objCircularDocumentData,$longTermId,$intCurrentCircularID,$intCircular,$sendCircularUser,$mst_company_id,$finishedDate){
        Log::info('他環境処理開始');
        // 他環境処理を呼び出し
        try {
            $envClient = EnvApiUtils::getAuthorizeClient($sendCircularUser->env_flg,$sendCircularUser->server_flg);
            if (!$envClient){
                throw new \Exception('Cannot connect to Env Api');
            }
            $response = $envClient->post("getEnvCircularHistoryAndOtherData", [
                RequestOptions::JSON => [
                    'create_company_id' => $mst_company_id,
                    'origin_env_flg' => $sendCircularUser->env_flg,
                    'origin_server_flg' => $sendCircularUser->server_flg,
                    'origin_edition_flg' => $sendCircularUser->edition_flg,
                    'origin_circular_id' => $intCircular,
                    'finishedDate'=>$finishedDate
                ]
            ]);
            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::info($response->getBody());
                Log::info("------------------   Get Other ENV SERVER  DOCUMENT DATA  ERROR    ------------------------");
                return ['msg' => 'Get Other ENV SERVER ERROR'];
            }
            $arrAllCircularData = json_decode(json_encode(json_decode($response->getBody())->all_data),true);
      
            $arrFindDocumentData = [];
            foreach($arrAllCircularData['circular_document'] as $fkey => $item) {
                $objFindData = $objCircularDocumentData->where("document_no", $item['document_no'])
                    ->where("parent_send_order")->where('create_user_id', $item['create_user_id'])
                    ->where('create_company_id', $item['create_company_id'])->where('origin_server_flg', $item['origin_server_flg'])
                    ->where("origin_edition_flg", $item['origin_edition_flg'])->where("file_size", $item['file_size'])
                    ->where("origin_env_flg", $item['origin_env_flg'])->where("file_name", $item['file_name'])->first();
                if (empty($objFindData)) {
                    
                    continue;
                }
                $objFindData->origin_id = $item['id'];
                $arrFindDocumentData[] = $objFindData;
            }
            if(empty($arrFindDocumentData)){return ;}
            
            $intCircularCurrentID = $arrFindDocumentData[0]->circular_id;
            $arrFindDocumentData = collect($arrFindDocumentData);
                
            if(!empty($arrAllCircularData['history'])){
                $arrHistory = [];
                foreach($arrAllCircularData['history'] as $key => $value) {
                    $arrTemp = [
                        'id' => $value['id'],
                        'circular_id' => $intCircularCurrentID,
                        'long_term_document_id' => $longTermId,
                        'operation_email' => $value['operation_email'],
                        'operation_name' => $value['operation_name'],
                        'acceptor_email' => $value['acceptor_email'],
                        'acceptor_name' => $value['acceptor_name'],
                        'circular_status' => $value['circular_status'],
                        'create_at' => $value['create_at'],
                        'is_skip' => $value['is_skip'],
                        'circular_document_id' => 0,
                        'file_name' => '',
                        'file_size' => 0,
                    ];
                    $arrHistory[$value['id']] = $arrTemp;
                    if(empty($arrAllCircularData['history'][$key]['circular_document_id'])){
                        continue;
                    }
                    $objHistoryFIndData = $arrFindDocumentData->where('origin_id',$value['circular_document_id'])->first();
                    if(!$objHistoryFIndData){
                        continue;
                    }
                    $arrTemp['circular_document_id'] = $objHistoryFIndData->id;
                    $arrTemp['file_name'] = $objHistoryFIndData->file_name;
                    $arrTemp['file_size'] = $objHistoryFIndData->file_size;
                    $arrHistory[$value['id']] = $arrTemp;
                }
                
                DB::table("long_term_circular_operation_history")->insert($arrHistory);
            }
            if(!empty($arrAllCircularData['text'])){
                $arrText = [];
                foreach($arrAllCircularData['text']  as $key => $value) {
                    $arrTemp = [
                        'id' => $value['id'],
                        'long_term_document_id' => $longTermId,
                        'circular_document_id' => 0,
                        'circular_operation_id' => $value['circular_operation_id'],
                        'text' => $value['text'],
                        'name' => $value['name'],
                        'email' => $value['email'],
                        'create_at' => $value['create_at'],
                    ];
                    $arrText[$value['id']] = $arrTemp;
                    if(empty($value['circular_document_id'])){
                        continue;
                    }
                    $objTextFIndData = $arrFindDocumentData->where('origin_id',$value['circular_document_id'])->first();

                    if(!$objTextFIndData){
                        continue;
                    }
                    $arrTemp['circular_document_id'] = $objTextFIndData->id;
                    $arrText[$value['id']] = $arrTemp;
                }
                DB::table("long_term_text_info")->insert($arrText);
            }
            
            if(!empty($arrAllCircularData['comment'])){
                $arrComment = [];
                foreach($arrAllCircularData['comment']  as $key => $value) {
                    $arrAllCircularData['comment'][$key]['long_term_document_id'] = $longTermId;
                    $arrAllCircularData['comment'][$key]['long_term_operation_id'] = $value['circular_operation_id'];
                    unset($arrAllCircularData['comment'][$key]['circular_operation_id']);
                    
                    $arrTemp = [
                        'id' => $value['id'],
                        'long_term_document_id' => $longTermId,
                        'long_term_operation_id' => $value['circular_operation_id'],
                        'parent_send_order' => $value['parent_send_order'],
                        'name' => $value['name'],
                        'email' => $value['email'],
                        'text' => $value['text'],
                        'private_flg' => $value['private_flg'],
                        'create_at' => $value['create_at'],
                        'circular_document_id' => 0,
                    ];
                    $arrComment[$value['id']] = $arrTemp;
                    if(empty($value['circular_document_id'])){
                        continue;
                    }
                    $objCommentFIndData = $arrFindDocumentData->where('origin_id',$value['circular_document_id'])->first();

                    if(!$objCommentFIndData){
                        continue;
                    }
                    $arrTemp['circular_document_id'] = $objCommentFIndData->id;
                    $arrComment[$value['id']] = $arrTemp;
                }
                DB::table("long_term_document_comment_info")->insert($arrComment);
            }
            if(!empty($arrAllCircularData['stamp'])){
                $arrStamp = [];
                foreach($arrAllCircularData['stamp']  as $key => $value) {
                    
                    $arrTemp = [
                        'id' => $value['id'],
                        'long_term_document_id' => $longTermId,
                        'long_term_operation_id' => $value['circular_operation_id'],
                        'mst_assign_stamp_id' => $value['mst_assign_stamp_id'],
                        'parent_send_order' => $value['parent_send_order'],
                        'stamp_image' => $value['stamp_image'],
                        'name' => $value['name'],
                        'email' => $value['email'],
                        'bizcard_id' => $value['bizcard_id'],
                        'env_flg' => $value['env_flg'],
                        'server_flg' => $value['server_flg'],
                        'edition_flg' => $value['edition_flg'],
                        'info_id' => $value['info_id'],
                        'file_name' => $value['file_name'],
                        'create_at' => $value['create_at'],
                        'time_stamp_permission' => $value['time_stamp_permission'],
                        'serial' => $value['serial'],
                        'circular_document_id' => 0,
                    ];
                    $arrStamp[$value['id']] = $arrTemp;
                    if(empty($value['circular_document_id'])){
                        continue;
                    }
                    $objStampFIndData = $arrFindDocumentData->where('origin_id',$value['circular_document_id'])->first();
                    if(!$objStampFIndData){
                        continue;
                    }
                    $arrTemp['circular_document_id'] = $objStampFIndData->id;
                    $arrStamp[$value['id']] = $arrTemp;
                }
                DB::table("long_term_stamp_info")->insert($arrStamp);
            }
            
        }catch (\Exception $exception){
            Log::info("--------------------------------".$exception->getMessage().$exception->getLine());
            throw  new  \Exception($exception->getMessage());
        }
        Log::info("他環境処理 end");
    }
}
