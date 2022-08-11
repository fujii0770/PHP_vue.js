<?php

namespace App\Console\Commands;

use App\Http\Utils\EnvApiUtils;
use App\Models\CircularOperationHistory;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateOperationAndDocumentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateOperationAndDocumentData:changeOldData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    protected $intHandlerNum = 0;

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
     */
    public function handle()
    {
        Log::info("Change Old LongTerm Data On Start");
        $intPage = 1;
        $intLimit = 200;
        $boolFlg = true;
        while($boolFlg === true){
            Log::info($intPage);
            $offset = ($intPage - 1) * $intLimit;
            $objDocument = DB::table("long_term_document")->select('id', 'completed_at','mst_company_id', 'circular_id', "file_name", "circular_id", 'update_at')
                ->where("circular_id",'>',0)
                ->where('is_other_env_circular_flg',0)
                ->orderBy("id", "desc")->offset($offset)->limit($intLimit)->get();
            if($objDocument->isEmpty()){
                $boolFlg = false;
                continue;
            }
            foreach ($objDocument as $key => $item) {
                $intCountWaitChangeData = DB::table("long_term_circular_operation_history")
                    ->where("circular_id",$item->circular_id)
                    ->where('long_term_document_id',$item->id)
                    ->where("circular_document_id",'>',0)->count();
                if($intCountWaitChangeData > 0){
                    continue;
                }
                $arrFileData = explode(".pdf,", $item->file_name);
                $strDateTime = date("Ym", strtotime($item->completed_at));
                $strNowDateTime = date("Ym");
                $strDateTime = ($strDateTime >= $strNowDateTime) || ($strNowDateTime < 202007) ? '' : $strDateTime;
                if (!empty($arrFileData) && Schema::hasTable("circular_document$strDateTime")){
                    try {
                        $arrCountData = [];
                        foreach($arrFileData as $key=>$val){
                            $val = trim($val,'.pdf');
                            $val .= '.pdf';
                            $arrFileData[$key] = trim($val);
                            $val = $arrFileData[$key];
                            if(isset($arrCountData[$val])){
                                $arrCountData[$val][$key] = count($arrCountData[$val]);
                            }else{
                                $arrCountData[$val] = [$key => 0];
                            }
                        }
                        DB::transaction(function () use($strDateTime,$item,$arrFileData,$arrCountData) {
                            $arrFileAndID = DB::table("circular_document$strDateTime")->where("circular_id", $item->circular_id)->get();
                            $this->handlerCurrnetLongTermData($item, $arrFileAndID, $arrFileData,$strDateTime,$arrCountData);
                        });
                    } catch (\Exception $ex) {
                        Log::error($ex->getTraceAsString()." line:".$ex->getLine());
                    }
                }
            }
            $intPage++;
        }
        Log::info(" long term handler total count : ".$this->intHandlerNum);
        Log::info("Change Old LongTerm Data On END");
    }

    private function handlerCurrnetLongTermData($objLongTermData, $arrFileAndID, $arrFileData, $strDateTime,$arrCountData)
    {
        try {
            $intCircularID = $objLongTermData->circular_id;
            $objCircular = DB::table("circular$strDateTime")->where("id", $intCircularID)->select('origin_circular_id', 'completed_date', 'origin_circular_id', "id", 'mst_user_id', 'env_flg', 'edition_flg', 'server_flg')->first();
            Log::info(print_r($objCircular, true));
            if (!isset($objCircular->id)) {
                return;
            }
            if ($objCircular->edition_flg == config('app.pac_contract_app') && $objCircular->env_flg == config('app.pac_app_env') && $objCircular->server_flg == config('app.pac_contract_server')) {
                $arrObjData = DB::table("circular_operation_history")->where("circular_id", $intCircularID)->get();
                foreach ($arrFileData as $key => $item) {
                    $objCD = $arrFileAndID->where('file_name', $item)->values();
                    $intNum = $objCD->count();
                    if ($intNum <= 0) {
                        continue;
                    }
                    $objCD = $objCD[$arrCountData[$item][$key]];
                    // get first or get the key
                    if(!isset($objCD->id)){
                        throw  new  \Exception("current document is not exists");
                    }
                    $arrAllCurrentDocumentData = $arrObjData->where("circular_document_id", $objCD->id);

                    if ($arrAllCurrentDocumentData->isEmpty()) {
                        continue;
                    }
                    
                    DB::table("long_term_circular_operation_history")
                        ->whereIn('id', $arrAllCurrentDocumentData->pluck('id'))->where("circular_id",$objCD->circular_id)->update([
                            'circular_document_id' => $objCD->id,
                            'file_name' => $objCD->file_name,
                            'file_size' => $objCD->file_size,
                        ]);
                }
                if ($arrObjData->isNotEmpty()) {
                    $arrStampInfo = DB::table("long_term_stamp_info")->where('long_term_document_id', $objLongTermData->id)->get();
                    $arrTextInfo = DB::table("long_term_text_info")->where('long_term_document_id', $objLongTermData->id)->get();
                    $arrCommentInfo = DB::table("long_term_document_comment_info")->where('long_term_document_id', $objLongTermData->id)->get();
                    $arrIDToOperationID = array_column($arrObjData->toArray(), 'circular_document_id', 'id');

                    foreach ($arrStampInfo as $key => $item) {
                        if (!isset($arrIDToOperationID[$item->long_term_operation_id])) {
                            continue;
                        }
                        DB::table("long_term_stamp_info")->where("id", $item->id)->update(['circular_document_id' => $arrIDToOperationID[$item->long_term_operation_id]]);
                    }
                    foreach ($arrTextInfo as $key => $item) {
                        if (!isset($arrIDToOperationID[$item->circular_operation_id])) {
                            continue;
                        }
                        DB::table("long_term_text_info")->where("id", $item->id)->update(['circular_document_id' => $arrIDToOperationID[$item->circular_operation_id]]);
                    }
                    foreach ($arrCommentInfo as $key => $item) {
                        if (!isset($arrIDToOperationID[$item->long_term_operation_id])) {
                            continue;
                        }
                        DB::table("long_term_document_comment_info")->where("id", $item->id)->update(['circular_document_id' => $arrIDToOperationID[$item->long_term_operation_id]]);
                    }
                }
            } else {
                $this->handlerOtherEnvData($strDateTime, $objLongTermData, $objCircular);
            }
            $this->intHandlerNum++;
        }catch (\Exception $exception){
            throw  $exception;
        }
    }
    
    protected function handlerOtherEnvData($strDateTime,$objLongTermData,$objCircular){
        
        // 他環境処理を呼び出し
        try {
            Log::info('他環境処理開始');
            $objCircularDocumentData = DB::table("circular_document$strDateTime")->where('circular_id',$objCircular->id)->get();
            $envClient = EnvApiUtils::getAuthorizeClient($objCircular->env_flg,$objCircular->server_flg);
            if (!$envClient){
                throw new \Exception('Cannot connect to Env Api');
            }
            $response = $envClient->post("getEnvCircularHistoryAndOtherData", [
                RequestOptions::JSON => [
                    'create_company_id' => $objLongTermData->mst_company_id,
                    'origin_env_flg' => $objCircular->env_flg,
                    'origin_server_flg' => $objCircular->server_flg,
                    'origin_edition_flg' => $objCircular->edition_flg,
                    'origin_circular_id' => $objCircular->origin_circular_id,
                    'finishedDate'=>$strDateTime
                ]
            ]);
            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::info($response->getBody());
                Log::info("------------------   Get Other ENV SERVER  DOCUMENT DATA  ERROR circular {$objCircular->id} - {$objCircular->origin_circular_id} - {$objLongTermData->id}  ------------------------");
                return ['msg' => 'Get Other ENV SERVER ERROR'];
            }
            $arrAllCircularData = json_decode(json_encode(json_decode($response->getBody())->all_data),true);

            DB::table("long_term_document")->where('id',$objLongTermData->id)->update([
                'is_other_env_circular_flg' => 1
            ]);
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
            if(empty($arrFindDocumentData)){
                Log::info("can not find circular by  circular_id:{$objCircular->id} - origin__circular_id:{$objCircular->origin_circular_id} - longtermID:{$objLongTermData->id}");
                return ;
            }

            $intCircularCurrentID = $arrFindDocumentData[0]->circular_id;
            $arrFindDocumentData = collect($arrFindDocumentData);

            if(!empty($arrAllCircularData['history'])){
                $arrHistory = [];
                
                $intFindHistoryData = DB::table("long_term_circular_operation_history")->whereIn("id",array_column($arrAllCircularData['history'],'id'))->where("long_term_document_id",$objLongTermData->id)->count();
                if($intFindHistoryData > 0){
                    $arrAllCircularData['history'] = [];
                    Log::info($objLongTermData->id."history is not empty");
                }
                
                foreach($arrAllCircularData['history'] as $key => $value) {
                    $arrTemp = [
                        'id' => $value['id'],
                        'circular_id' => $intCircularCurrentID,
                        'long_term_document_id' => $objLongTermData->id,
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
                if(!empty($arrHistory)){
                    DB::table("long_term_circular_operation_history")->insert($arrHistory);
                }
            }
            if(!empty($arrAllCircularData['text'])){
                $arrText = [];
                
                $intFindTextData = DB::table("long_term_text_info")->whereIn("id",array_column($arrAllCircularData['text'],'id'))->where("long_term_document_id",$objLongTermData->id)->count();
                if($intFindTextData > 0){
                    $arrAllCircularData['text'] = [];
                    Log::info($objLongTermData->id."text is not empty");
                }
                foreach($arrAllCircularData['text']  as $key => $value) {
                    $arrTemp = [
                        'id' => $value['id'],
                        'long_term_document_id' => $objLongTermData->id,
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
                if(!empty($arrText)){
                    DB::table("long_term_text_info")->insert($arrText);
                }
            }

            if(!empty($arrAllCircularData['comment'])){
                $arrComment = [];

                $intFindTextData = DB::table("long_term_document_comment_info")->whereIn("id",array_column($arrAllCircularData['comment'],'id'))->where("long_term_document_id",$objLongTermData->id)->count();
                if($intFindTextData > 0){
                    $arrAllCircularData['comment'] = [];
                    Log::info($objLongTermData->id."comment is not empty");
                }
                
                foreach($arrAllCircularData['comment']  as $key => $value) {
                    $arrAllCircularData['comment'][$key]['long_term_document_id'] = $objLongTermData->id;
                    $arrAllCircularData['comment'][$key]['long_term_operation_id'] = $value['circular_operation_id'];
                    unset($arrAllCircularData['comment'][$key]['circular_operation_id']);

                    $arrTemp = [
                        'id' => $value['id'],
                        'long_term_document_id' => $objLongTermData->id,
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
                if(!empty($arrComment)){
                    DB::table("long_term_document_comment_info")->insert($arrComment);
                }
            }
            if(!empty($arrAllCircularData['stamp'])){
                $arrStamp = [];

                $intFindTextData = DB::table("long_term_stamp_info")->whereIn("id",array_column($arrAllCircularData['stamp'],'id'))->where("long_term_document_id",$objLongTermData->id)->count();
                if($intFindTextData > 0){
                    $arrAllCircularData['stamp'] = [];
                    Log::info($objLongTermData->id."stamp is not empty");
                }
                
                foreach($arrAllCircularData['stamp']  as $key => $value) {

                    $arrTemp = [
                        'id' => $value['id'],
                        'long_term_document_id' => $objLongTermData->id,
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
                if(!empty($arrStamp)){
                    DB::table("long_term_stamp_info")->insert($arrStamp);
                }
            }

        }catch (\Exception $exception){
            Log::info($exception->getMessage().$exception->getLine());
            Log::info("文書IDが「{$objCircular->id}」のファイルはサーバ origin_circular_id「{$objCircular->origin_circular_id}」で見つかりませんでした。");
            throw  $exception;
        }
        Log::info("他環境処理 end");
    }
}
