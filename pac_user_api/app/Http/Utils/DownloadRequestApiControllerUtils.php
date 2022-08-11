<?php

namespace App\Http\Utils;

use App\AuditUser;
use App\Http\Utils\AppUtils;
use App\Http\Utils\UserApiUtils;
use DB;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\EnvApiUtils;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use App\Models\User;
use function Complex\argument;

/**
 * ダウンロード要求処理ユーティリティクラス
 * Class DownloadRequestApiControllerUtils
 * @package App\Http\Utils
 */
class DownloadRequestApiControllerUtils
{
	
	/**
	* ダウンロードファイルのデフォルト名取得
	*
	* @param $user 利用者情報
	* @param array $cids 回覧IDリスト
	* @param string $finishedDate 完了日
	* @param string $finishedDateKey 完了日キー
	* @param string $check_add_stamp_history 捺印履歴フラグ
	* @return string デフォルト名
	*/
	public static function getDefaultFileName($user, $cids, $finishedDate, $finishedDateKey, $check_add_stamp_history, $frmFlg = '',$upload_id=[]){
		// 複数のファイル名のクエリ
		$query_sub = DB::table("circular_document$finishedDate as D")
			->select(DB::raw('D.circular_id, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
			->whereIn('D.circular_id', $cids)
			->where(function ($query) use ($user) {
				$query->where(function ($query1) {
					$query1->where('confidential_flg', 0);
				});
				$query->orWhere(function ($query1) use ($user) {
					$query1->where('confidential_flg', 1)
						->where('create_company_id', $user->mst_company_id)
						->where('origin_env_flg', config('app.server_env'))
						->where('origin_edition_flg', config('app.edition_flg'))
						->where('origin_server_flg', config('app.server_flg'));
				});
			})
			->groupBy('circular_id');

        // 帳票回覧判定
        // 帳票回覧あれば、帳票回覧です。
        if($frmFlg != '') {
            // 請求書　もしくは　その他 回覧取得
            $invoices = DB::table("circular$finishedDate as C")
                ->select(DB::raw("C.id, C.edition_flg, C.env_flg, C.server_flg, C.origin_circular_id, C.update_at, U.title, D.file_names"))
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', 'D.circular_id');
                })
                ->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
                ->where('U.edition_flg', config('app.edition_flg'))
                ->whereIn('C.id', $cids)
                // 5:回覧前 と 2,3:完了 だけダウンロード対象
                ->whereIn('C.circular_status', [CircularUtils::RETRACTION_STATUS, CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
            $circulars = DB::table("circular$finishedDate as C")
                ->select(DB::raw("C.id, C.edition_flg, C.env_flg, C.server_flg, C.origin_circular_id, C.update_at, '' as title, D.file_names"))
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', 'D.circular_id');
                })
                ->whereIn('C.id', $cids)
                // 0 だけダウンロード対象
                ->whereIn('C.circular_status', [CircularUtils::SAVING_STATUS])
                ->union($invoices)
                ->get()->keyBy('id');
        }else{
            // 回覧取得
            $circulars = DB::table("circular$finishedDate as C")
                ->select(['C.id', 'C.edition_flg', 'C.env_flg', 'C.server_flg', 'C.origin_circular_id', 'C.update_at', 'U.title', 'D.file_names'])
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', 'D.circular_id');
                })
                ->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
                // ->where('circular_user.env_flg', config('app.server_env'))
                ->where('U.edition_flg', config('app.edition_flg'))
                ->whereIn('C.id', $cids)
                // ->where('C.circular_status', '!=', CircularUtils::DELETE_STATUS)
                ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                ->get()->keyBy('id');
        }

		// 他環境env
		$other_env = null;
		// 他環境回覧ID集合
		$origin_env_circulars = [];
		$current_circulars_exits = false;
		foreach ($circulars as $key => $circular) {
			// 他環境存在の場合
			if ($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))) {
				$origin_env_circulars[$circular->env_flg . $circular->server_flg][] = ['circular_id' => $circular->id, 'origin_circular_id' => $circular->origin_circular_id];
				// $other_env = $circular->env_flg;
				// unset($circulars[$key]);
			}
			//todo 現行側
			if ($circular->edition_flg == 0) {
				$current_circulars_exits = true;
			}
		}
		// 他環境ファイル集合
		$env_document_datas =[];
		if (!empty($origin_env_circulars)) {

			foreach ($origin_env_circulars as $key => $origin_env_circular) {
				$env = substr($key, 0, 1);
				$server = substr($key, 1, strlen($key)-1);
				$envClient = EnvApiUtils::getAuthorizeClient($env, $server);

				if (!$envClient) {
					//TODO message
					throw new \Exception('Cannot connect to Env Api');
				}

				$company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
				$hasSignature = $company->esigned_flg == 1;

				// 他環境処理を呼び出し
				$response = $envClient->post("getEnvDocuments", [
					RequestOptions::JSON => [
					    'create_company_id' => $user->mst_company_id,
                        'origin_env_flg' => config('app.server_env'),
                        'origin_server_flg' => config('app.server_flg'),
						'origin_edition_flg' => config('app.edition_flg'),
                        'circulars' => $origin_env_circular,
                        'finishedDate' => $finishedDateKey,
                        'check_add_stamp_history' => $check_add_stamp_history,
                        'hasSignature' => $hasSignature,
                        'is_get_file_data' => false,
                    ]
				]);

				if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
					Log::error($response->getBody());
					throw new \Exception('Cannot get env documents');
				}
				
				$result = json_decode($response->getBody(), true);

				$env_document_datas = $result['document_data'];
                unset($result);
			}
		}
        $env_document_data=json_decode(json_encode($env_document_datas),true)??[];
		//  現在環境にファイルを取得
		$cids = $circulars->keys();
		$circular_docs = DB::table("circular_document$finishedDate")
			->whereIn('circular_id', $cids)
			->where(function ($query) use ($user) {
				$query->where(function ($query1) {
					$query1->where('origin_document_id', 0);
					$query1->where('confidential_flg', 0);
				});
				$query->orWhere(function ($query1) use ($user) {
					$query1->where('create_company_id', $user->mst_company_id)
						->where('origin_env_flg', config('app.server_env'))
						->where('origin_edition_flg', config('app.edition_flg'))
						->where('origin_server_flg', config('app.server_flg'));
				});
			})
			->select('id', 'circular_id', 'file_name', 'origin_edition_flg', 'origin_env_flg', 'origin_server_flg')
			->get()->keyBy('id');

		$document_datas = array();
		// mysql最長クエリ
		$max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;

        // 選択文書総容量
        $doc_size = DB::table("circular_document$finishedDate")->select(DB::raw('sum(file_size) as file_size'))
            ->whereIn('id',$circular_docs->keys())->value('file_size');

        //現在環境にファイルのデータ
        $document_data = DB::table("document_data$finishedDate")
            ->whereIn('circular_document_id', $circular_docs->keys())
            ->select('id', 'circular_document_id')
            ->get()
            ->keyBy('circular_document_id')
            ->toArray();
        foreach ($document_data as $key => $item) {
            $document_datas[$key] = $item;
        }

        $long_term_document_datas=[];
        $long_term_document_sizes = 0;
        if(count($upload_id)){
            $long_term_document_datas = DB::table('long_term_document')
                ->whereIn('upload_id',$upload_id)
                ->select('id','file_name','circular_id','upload_status','upload_id','file_size')
                ->get();
            $long_term_document_sizes = $long_term_document_datas->sum(function ($item){
                return $item->file_size;
            });
        }
        //文書情報を取得
        if (count($document_datas) === 0 && count($env_document_data) === 0 && count($long_term_document_datas)===0) {
            throw new \Exception(__('message.false.download_request.file_detail_get'));
        }
        //ダウンロードファイルのサイズ
        if (($doc_size + $long_term_document_sizes) * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {
            throw new \Exception(__('message.warning.download_request.order_size_max'));
        }

        if (count($document_datas) == 1&& count($long_term_document_datas) ==0 && count($env_document_data) == 0) {       // 現在の環境に一つがあるの場合
            $fileName = $circular_docs[reset($document_datas)->circular_document_id]->file_name;
        } elseif (count($env_document_data) == 1 && count($document_datas) == 0) {  // 他の環境に一つがあるの場合
            $first_document = Arr::first($env_document_data);
            $fileName = $first_document['file_name'];
        } elseif (count($long_term_document_datas) == 1 && count($document_datas)==0 ) {  // 他の環境に一つがあるの場合
            $fileName = $long_term_document_datas->first()->file_name;
        } else { // 複数ファイル
            $fileName = Carbon::now()->copy()->format('YmdHis') . ".zip";
        }

		return $fileName;
	}
    public static function getLongTermData($ids):array
    {
        $long_term_document_datas=DB::table('long_term_document')->whereIn('id',$ids)->select('id','file_name','circular_id','upload_status','upload_id')->get();
        $cids=[];
        $upload_id=[];
        foreach ($long_term_document_datas as $circular_id=>$long_term_document_data){
            if(!$long_term_document_data->upload_status){
                array_push($cids,$long_term_document_data->circular_id);
            }else{
                array_push($upload_id,$long_term_document_data->upload_id);
            }
        }
        return [$long_term_document_datas,$cids,$upload_id];
    }


    public static function getLongTermDocumentDownloadData($user, $param, $dl_request_id)
    {
        ini_set('memory_limit', '2048M');

        $cids = !empty($param['cids']) ? $param['cids'] : [];
        $check_add_stamp_history = !empty($param['stampHistory']) ? $param['stampHistory'] : false;
        $finishedDateKey = !empty($param['finishedDate']) ? $param['finishedDate'] : null;
        $finishedDate = !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        $usingTas = !empty($param['usingTas']) ? $param['usingTas'] : 0;
        $frmFlg = !empty($param['frmFlg']) ? $param['frmFlg'] : '';
        $upload_id = !empty($param['upload_id']) ? $param['upload_id'] : [];

        $strDName = !empty($param['dName']) ? $param['dName'] : '';
        // 長期保存の場合
        $doc_size = array_sum(
            array_column(
                DB::table('long_term_document')
                    ->whereIn('id', $cids)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select(DB::raw('file_size'))->get()->toArray(),
                'file_size'
            )
        );

        // mysql最長クエリ
        $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;
        if ($doc_size * DownloadUtils::MULTIPLE_SIZE > $max_allowed_packet) {

            return response()->json(['status' => false,
                'message' => [__('message.warning.download_request.order_size_max')]]);
        }

        // long_term_document 情報取得
        $long_term_document_datas = DB::table('long_term_document')
            ->whereIn('id', $cids)
            ->where('mst_company_id', $user->mst_company_id)
            ->select('id', 'circular_id', 'file_name', 'file_size', 'upload_id', 'upload_status', 'completed_at', 'title', 'create_at','is_other_env_circular_flg')
            ->get()
            ->keyBy('id');
        // 0件の場合、取得失敗
        if (count($long_term_document_datas) === 0) {
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.file_detail_get')]]);
        }
        try {
            // 再ダウンロード処理対策
            DB::table("download_proc_wait_data")->where('download_request_id', $dl_request_id)->delete();
            $type = Storage::disk('s3');
            if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5) {
                $type = Storage::disk('k5');
            }
            $count = 0;
            $input = [];
            $newDocument = [];

            foreach ($long_term_document_datas as $k => $circular) {

                $s3path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                    . '/' . config('app.server_flg') . '/' . $user->mst_company_id . '/';
                $path = $s3path . (($circular->upload_status == 1) ? ('upload_' . $circular->upload_id) : $circular->circular_id);

                $arrAllCurrentDocumentData = DB::table("long_term_circular_operation_history")
                    ->where("circular_document_id", '>', 0)
                    ->where('circular_id', $circular->circular_id)
                    ->groupBy("circular_document_id", 'file_name', 'file_size')
                    ->select("circular_document_id", 'file_name', 'file_size')
                    ->get();

                $arrFileNameCount = [];

                
                if(($arrAllCurrentDocumentData->isEmpty()) && isset($circular->upload_id) && $circular->upload_id > 0){
                    $objFindUpData = DB::table('upload_data')->where("id",$circular->upload_id)->first();
                    
                    if(empty($objFindUpData)){
                        continue;
                    }
                    $newDocument[$count] = [
                        'file_name' => $circular->file_name,
                        'circular_id' => $circular->circular_id,
                        'num' => $count,
                        'title' => $circular->title,
                        'create_at' => $circular->create_at,
                        'file_size' => $circular->file_size,
                        'file_data' => AppUtils::decrypt($objFindUpData->upload_data),
                        'id' => $k,
                        'document_id' => 0,
                    ];
                    $count++;
                }
                foreach ($arrAllCurrentDocumentData as $aacddKey => $aacddVal) {
                    $arrFileNameCount[$aacddVal->file_name] = isset($arrFileNameCount[$aacddVal->file_name]) ? $arrFileNameCount[$aacddVal->file_name] + 1 : 0;
                    if($arrFileNameCount[$aacddVal->file_name] > 0){
                        $aacddVal->file_name = basename($aacddVal->file_name,'.pdf') .' ('.$arrFileNameCount[$aacddVal->file_name].') .pdf';
                    }
                    $strFilePath = ($path . '/' . $aacddVal->file_name);
                    if ($type->exists($strFilePath)) {
                        $file_content = $type->get($strFilePath);
                        $newDocument[$count] = [
                            'file_name' => $aacddVal->file_name,
                            'circular_id' => $circular->circular_id,
                            'num' => $count,
                            'title' => $circular->title,
                            'create_at' => $circular->create_at,
                            'file_size' => $aacddVal->file_size,
                            'file_data' => base64_encode($file_content),
                            'id' => $k,
                            'document_id' => $aacddVal->circular_document_id,
                        ];
                        $count++;
                    }
                }
            }

            if ($check_add_stamp_history) {
                $stampApiClient = UserApiUtils::getStampApiClient();
                $circular_edition_flg = config('app.edition_flg');
                $circular_env_flg = config('app.server_env');
                $circular_server_flg = config('app.server_flg');
                foreach ($newDocument as $key => $long_term_document) {
                    $resultBody = CircularDocumentUtils::getLongTermHistory(
                        $long_term_document['id'],
                        $user->mst_company_id,
                        $circular_edition_flg,
                        $circular_env_flg,
                        $circular_server_flg,
                        $check_add_stamp_history,
                        $long_term_document['file_name'],
                        $long_term_document,
                        $finishedDate
                    );
                    if ($resultBody['status']) {
                        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                        $hasSignature = $company->esigned_flg == 1;

                        $resultC = $stampApiClient->post("signatureAndImpress", [
                            RequestOptions::JSON => [
                                'signature' => $hasSignature,
                                'signatureKeyFile' => null,
                                'signatureKeyPassword' => null,
                                'data' => [
                                    [
                                        'circular_document_id' => $key,
                                        'pdf_data' => base64_encode($resultBody['circular_document']->file_data),
                                        'append_pdf_data' => $resultBody['circular_document']->append_pdf,
                                        'stamps' => [],
                                        'texts' => [],
                                        'usingTas' => 0
                                    ],
                                ],
                            ]
                        ]);

                        $resData = json_decode((string)$resultC->getBody());
                        if ($resData->data) {
                            $newDocument[$key]['file_data'] = $resData->data[0]->pdf_data;
                        }
                        unset($resData);
                        unset($resultBody);
                    } else {
                        Log::error('Log getCircularDoc: ' . $long_term_document['file_name']);
                        return Response::json(['status' => false, 'message' => $long_term_document['file_name'] . "の履歴を取得することが失敗です。", 'data' => null], 500);
                    }
                }
            }
            $newCount = 1;
            foreach ($newDocument as $document) {
                $input[] = [
                    'state' => 0,
                    'download_request_id' => $dl_request_id,
                    'num' => $newCount,
                    'circular_document_id' => $newCount,
                    'document_data_id' => $newCount,
                    'document_data' => AppUtils::encrypt($document['file_data']),
                    'create_at' => Carbon::now(),
                    'create_user' => $user->id,
                    'circular_id' => $document['circular_id'],
                    'file_name' => $document['file_name'],
                    'title' => $document['title'],
                    'circular_update_at' => $document['create_at'],
                    'file_size' => $document['file_size'],
                ];
                $newCount++;
            }

            DB::table('download_proc_wait_data')->insert($input);
            unset($newDocument);
            unset($input);

            // ダウンロード要求情報取得
            return self::handlerSendMailAndHandlerData($dl_request_id, $param);

        } catch (\Exception $e) {
            Log::info($e->getTraceAsString() . $e->getMessage());

            // リトライ
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $e;
        }
    }
	/**
	 * 回覧文書ダウンロードファイルデータ取得
	 *
	 * @param $user 利用者情報
	 * @param $param ダウンロード予約要求パラメータ
	 * @param integer $dl_request_id ダウンロード要求ID
	 * @return string ファイルデータ
	 */
	public static function getCircularsDownloadData($user, $param, $dl_request_id){
		try {
			ini_set('memory_limit','2048M');

            $download = !empty($param['download']) ? $param['download'] : false;
            if($download){
                return DownloadRequestApiControllerUtils::getFormCircularData($user, $param);
            }else{
                DownloadRequestApiControllerUtils::setCircularsDownloadDataForDownloadProcWaitData($user, $param, $dl_request_id);
            }
			// ダウンロード要求情報取得
			return self::handlerSendMailAndHandlerData($dl_request_id, $param);

        } catch (\Exception $e) {
            // リトライ
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $e;
        }
	}

	public static function handlerSendMailAndHandlerData($dl_request_id, $param){
        $finishedDateKey 			= !empty($param['finishedDate']) ? $param['finishedDate'] : null;
        $finishedDate 				= !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
                ->where('id', $dl_request_id)->first();

            if (!$dl_req) {
                Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
                return;
            }

            // 回覧文書ID
            $dl_proc_wait_datas = DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->select('circular_document_id', 'circular_id', 'file_name', 'document_data','title','id','circular_update_at')
                ->orderBy('circular_id', 'asc')
                ->orderBy('circular_document_id', 'asc')
                ->get();

            //ダウンロード件数
            $dl_count = $dl_proc_wait_datas->count();

            // 回覧情報
            $circular_info = DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->select('circular_id', 'title', 'circular_update_at')
                ->groupBy('circular_id', 'title', 'circular_update_at')
                ->get()->keyBy('circular_id')->toArray();


            $arguments = unserialize($dl_req->arguments);
            $isAuditUser = false;
            if ($dl_req->class_path=='App\Http\Utils\DownloadRequestApiControllerUtils' && $dl_req->function_name='getLongTermDocumentDownloadData'&& isset($arguments[0]) && $arguments[0] instanceof AuditUser){
                $user_info = DB::table('mst_audit')
                    ->where('id', $arguments[0]->id)
                    ->select(['id', 'email', 'mst_company_id'])
                    ->first();
                $isAuditUser = true;
            }else{
                // 申請者情報
                $user_info = DB::table('mst_user')
                    ->where('id', $dl_req->mst_user_id)
                    ->select(['id', 'email', 'mst_company_id'])
                    ->first();
            }
            foreach ($dl_proc_wait_datas as $dl_proc_wait_data) {
                $cir_doc_ids_array[] = $dl_proc_wait_data->circular_document_id;
                unset($dl_proc_wait_data);
            }
            
        $path = '';
        if ($dl_count === 1) {
            // 回覧一件and文書一件の場合
            $data = AppUtils::decrypt($dl_proc_wait_datas->first()->document_data);
        } else {
            // 回覧or文書複数件の場合
            $path = sys_get_temp_dir()."/download-circular-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $dl_req->mst_company_id, $dl_req->id) . ".zip";
            $zip = new \ZipArchive();
            if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == false) {
                throw new \Exception(__('message.false.download_request.zip_create'));
            }
            $arrayFolderName = [];//key="回覧ID(ID==false時0)"、value="決定されたフォルダ名"
            foreach ($dl_proc_wait_datas as $dl_proc_wait_data){
                //フォルダ生成
                $circularIDKey = $dl_proc_wait_data->circular_id ?: 0;//回覧ID判別用(ID==false時0)
                if (!key_exists($circularIDKey, $arrayFolderName)){
                    //初出の回覧IDkey
                    $title = preg_replace('/[\t]/', '', $dl_proc_wait_data->title);
                    $title = CommonUtils::changeSymbols(trim($title, ' ') ? $title : $dl_proc_wait_data->file_name);
                    $title = mb_substr($title, 0, AppUtils::MAX_TITLE_LETTERS);
                    $updateName = $title;
                    $loopCount = 0;
                    while(in_array($updateName, $arrayFolderName, true)){
                        //フォルダ名重複時
                        $loopCount++;
                        $updateName = $title . ' (' . $loopCount . ') ';
                    }
                    $arrayFolderName[$circularIDKey] = $updateName;
                    $zip->addEmptyDir($updateName);
                }
            }
            $arrayFileName = [];//value="ファイルパス(フォルダ名)/決定されたファイル名(拡張子除く)"
            foreach ($dl_proc_wait_datas as $dl_proc_wait_data){
                //ファイル保存
                $circularIDKey = $dl_proc_wait_data->circular_id ?: 0;//回覧ID判別用(ID==false時0)
                $title = mb_substr($dl_proc_wait_data->file_name, mb_strrpos($dl_proc_wait_data->file_name, '/'));
                $title = mb_substr($title, 0, mb_strrpos($dl_proc_wait_data->file_name, '.'));
                $updateName = $arrayFolderName[$circularIDKey] . '/' . $title;
                $loopCount = 0;
                while(in_array($updateName, $arrayFileName, true)){
                    //ファイル名重複時
                    $loopCount++;
                    $updateName = $arrayFolderName[$circularIDKey] . '/' . $title . ' (' . $loopCount . ') ';
                }
                $arrayFileName[] = $updateName;
                $documentData = AppUtils::decrypt($dl_proc_wait_data->document_data);
                $zip->addFromString($updateName . '.pdf', base64_decode($documentData));
            }
            if (!$zip->close()) {
                throw new \Exception(__('message.false.download_request.zip_create'));
            }
        }

        if ($dl_count != 1 && !file_exists($path)) {
                if (file_exists($path)) {
                    throw new \Exception(__('message.false.download_request.compress_e', ['attribute' => $dl_count, 'path' => $path]));
                } else {
                    throw new \Exception(__('message.false.download_request.compress_n', ['attribute' => $dl_count, 'path' => $path]));
                }
            }

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                ->where('id', $dl_req->mst_company_id)->first()
                ->sanitizing_flg;

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::PROC_PROCESS_END,
                ]);

            if ($dl_count > 1) {
                $data = \file_get_contents($path);
            }else{
                $data = base64_decode($data);
            }

            // 完了お知らせ
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];

                if ($user_info && ($isAuditUser || CircularUserUtils::checkAllowReceivedEmail($user_info->email, 'download', $user_info->mst_company_id,config('app.server_env'),config('app.edition_flg'),config('app.server_flg')))) {
                    MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                        $user_info->email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                        // パラメータ
                        json_encode($email_data, JSON_UNESCAPED_UNICODE),
                        // タイプ
                        $isAuditUser?AppUtils::MAIL_TYPE_AUDIT:AppUtils::MAIL_TYPE_USER,//
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                        // メールボディ
                        trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                    );
                }
            }

        return $data;
    }

	/**
	 * 帳票回覧文書ダウンロードファイルデータ取得
	 * TODO 関数名は要修正(処理内容と一致していない可能性あり)
	 *
	 * @param $user 利用者情報
	 * @param $param ダウンロード予約要求パラメータ
	 * @return string ファイルデータ
	 */
	public static function getFormCircularData($user, $param){
		$ids 						= !empty($param['ids']) ? $param['ids'] : [];
		$check_add_stamp_history 	= !empty($param['stampHistory']) ? $param['stampHistory'] : false;
		$finishedDateKey 			= !empty($param['finishedDate']) ? $param['finishedDate'] : null;
		$finishedDate 				= !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		$frmFlg 					= !empty($param['frmFlg']) ? $param['frmFlg'] : '';
        list($long_term_documents,$cids,$upload_id)=self::getLongTermData($ids);
		if(!$check_add_stamp_history){
			$circularIds = DB::table('long_term_document')->whereIn('id', $ids)->where('mst_company_id', $user->mst_company_id)->get()->toArray();
			$zipPath = sys_get_temp_dir()."/download-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $user->mst_company_id, $user->id) . ".zip";

			$zip = new Filesystem(new ZipArchiveAdapter($zipPath));
            $S3Path=$pathTmp = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                . '/' . config('app.server_flg') . '/'. $user->mst_company_id . '/';
            $count=0;
			foreach ($circularIds as $circular){
				$pathTmp = $S3Path.  ($circular->upload_status==0?$circular->circular_id:'upload_'.$circular->upload_id);
                if ( Storage::disk('s3')->exists($pathTmp)){
					$file_names = Storage::disk('s3')->files($pathTmp);
					foreach($file_names as $file_name){
                        if(strpos($file_name, '.pdf') ===false){
                            continue;
                        }
						$file_content = Storage::disk('s3')->get($file_name);
						$zip->put($circular->upload_status==0?$circular->circular_id:$circular->upload_id.'/'.substr($file_name, strrpos($file_name, '/')), $file_content);
                        $file_content=chunk_split(base64_encode($file_content));
                        $size = AppUtils::getFileSize($file_content);
                        DB::table('download_proc_wait_data')->insert([
                            'state' => 0,
                            'download_request_id' => $count,
                            'num' => $count,
                            'circular_document_id' =>$count,
                            'document_data_id' => $count,
                            'document_data' => AppUtils::encrypt($file_content),
                            'create_at' => Carbon::now(),
                            'create_user' => $user->id,
                            'circular_id' =>$circular->upload_status==0 ?0: $circular->circular_id,
                            'file_name' => $circular->file_name,
                            'title' => $circular->upload_status==0 ? $circular->title:$circular->file_name,
                            'circular_update_at' =>$circular->upload_status==0 ? $circular->update_at:Carbon::now(),
                            'file_size' => $size,
                        ]);
                        $count++;
					}

				}
			}
			$zip->getAdapter()->getArchive()->close();
			return file_exists($zipPath) ? \file_get_contents($zipPath) : null;
		}
		// 複数のファイル名のクエリ
		$query_sub = DB::table("circular_document$finishedDate as D")
						->select(DB::raw('D.circular_id, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
						->whereIn('D.circular_id', $cids)
						->where(function ($query) use ($user) {
							$query->where(function ($query1) {
								$query1->where('confidential_flg', 0);
							});
							$query->orWhere(function ($query1) use ($user) {
								$query1->where('confidential_flg', 1)
									->where('create_company_id', $user->mst_company_id)
									->where('origin_env_flg', config('app.server_env'))
									->where('origin_edition_flg', config('app.edition_flg'))
									->where('origin_server_flg', config('app.server_flg'));
							});
						})
						->groupBy('circular_id');
	
		// 帳票回覧判定
		// 帳票回覧あれば、帳票回覧です。
		if($frmFlg != '') {
			// 請求書　もしくは　その他 回覧取得
			$invoices = DB::table("circular$finishedDate as C")
				->select(DB::raw("C.id, C.edition_flg, C.env_flg, C.server_flg, C.origin_circular_id, C.update_at, U.title, D.file_names"))
				->leftJoinSub($query_sub, 'D', function ($join) {
					$join->on('C.id', 'D.circular_id');
				})
				->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
				->where('U.edition_flg', config('app.edition_flg'))
				->whereIn('C.id', $cids)
				// 5:回覧前 と 2,3:完了 だけダウンロード対象
				->whereIn('C.circular_status', [CircularUtils::RETRACTION_STATUS, CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
			$circulars = DB::table("circular$finishedDate as C")
				->select(DB::raw("C.id, C.edition_flg, C.env_flg, C.server_flg, C.origin_circular_id, C.update_at, '' as title, D.file_names"))
				->leftJoinSub($query_sub, 'D', function ($join) {
					$join->on('C.id', 'D.circular_id');
				})
				->whereIn('C.id', $cids)
				// 0 だけダウンロード対象
				->whereIn('C.circular_status', [CircularUtils::SAVING_STATUS])
				->union($invoices)
				->get()->keyBy('id');
		}else{
			// 回覧取得
			$circulars = DB::table("circular$finishedDate as C")
				->select(['C.id', 'C.edition_flg', 'C.env_flg', 'C.server_flg', 'C.origin_circular_id', 'C.update_at', 'U.title', 'D.file_names'])
				->leftJoinSub($query_sub, 'D', function ($join) {
					$join->on('C.id', 'D.circular_id');
				})
				->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
				// ->where('circular_user.env_flg', config('app.server_env'))
				->where('U.edition_flg', config('app.edition_flg'))
				->whereIn('C.id', $cids)
				// ->where('C.circular_status', '!=', CircularUtils::DELETE_STATUS)
				->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
				->get()->keyBy('id');
		}

		// 他環境のファイルを取得
		$env_document_data = DownloadRequestApiControllerUtils::getRelatedCircularDocumentsOfOtherEnvironment($user, $circulars, $param);

		// 現在環境のファイルを取得
		$cids = $circulars->keys();
		$circular_docs = DB::table("circular_document$finishedDate")
			->whereIn('circular_id', $cids)
			->where(function ($query) use ($user) {
				$query->where(function ($query1) {
					$query1->where('origin_document_id', 0);
					$query1->where('confidential_flg', 0);
				});
				$query->orWhere(function ($query1) use ($user) {
					$query1->where('create_company_id', $user->mst_company_id)
						->where('origin_env_flg', config('app.server_env'))
						->where('origin_edition_flg', config('app.edition_flg'))
						->where('origin_server_flg', config('app.server_flg'));
				});
			})
			->select('id', 'circular_id', 'file_name', 'origin_edition_flg', 'origin_env_flg', 'origin_server_flg')
			->get()->keyBy('id');

		// 現在環境のファイルを取得
		$document_datas = DownloadRequestApiControllerUtils::getRelatedCircularDocumentsOfSameEnvironment($user, $circular_docs, $param);

		if (count($document_datas) === 0 && count($env_document_data) === 0) {
			throw new \Exception(__('message.warning.download_request.file_detail_get'));
		}

		$cirs = array();
		// 同一回覧内文書集合
		foreach ($document_datas as $circularId) {
			$document_id = $circularId->circular_document_id;
			if (!isset($circular_docs[$document_id])) continue;
			$circular_document = $circular_docs[$document_id];
			$cirs[$document_id][] = ['fileName' => $circular_document->file_name,
				'data' => AppUtils::decrypt($circularId->file_data)];
		}

		$fileName = "download-" . time() . ".zip";
		$path = sys_get_temp_dir()."/download-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $user->mst_company_id, $user->id) . ".zip";
		$zip2 = new \ZipArchive();
		$open=$zip2->open($path, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE );
		if ($open !== true) {
			Log::error(__('message.false.download_request.zip_create'));
		}
		$cir_ids = array_keys($cirs);
		$countFilename = [];
		foreach ($cir_ids as $cir_id) {
			$zip2->addEmptyDir(collect($cids)->first());
			foreach ($cirs[$cir_id] as $doc) {
				$filename=$doc['fileName'];
				if (key_exists($filename, $countFilename)) {
					$countFilename[$filename]++;
					$filename = $filename . ' (' . $countFilename[$filename] . ') ';
				} else {
					$countFilename[$filename] = 0;
				}
				$filename=collect($cids)->first().'/' .$filename;
				$zip2->addFromString($filename, base64_decode($doc['data']));
			}
		}
		if(count($env_document_data)>0){
			$countFilename = [];
			foreach ($env_document_data as $cir_id) {
				$zip2->addEmptyDir(collect($cids)->first());
				$filename=$cir_id['file_name'];
				if (key_exists($filename, $countFilename)) {
					$countFilename[$filename]++;
					$filename = $filename . ' (' . $countFilename[$filename] . ') ';
				} else {
					$countFilename[$filename] = 0;
				}
				$data=AppUtils::decrypt($cir_id['file_data']);
				$filename=collect($cids)->first().'/' .$filename;
				$zip2->addFromString($filename, base64_decode($data));
			}
		}
		$zip2->close();
		return file_exists($path) ? \file_get_contents($path) : null;
		
	}

	/**
	 * ダウンロードファイルデータ生成用のデータをダウンロード待ちデータテーブルに保存
	 *
	 * @param User $user 利用者情報
	 * @param $param ダウンロード予約要求パラメータ
	 * @param integer $dl_request_id ダウンロード要求ID
	 * @return void
	 */
	public static function setCircularsDownloadDataForDownloadProcWaitData($user, $param, $dl_request_id){
		$cids 						= !empty($param['cids']) ? $param['cids'] : [];
		$check_add_stamp_history 	= !empty($param['stampHistory']) ? $param['stampHistory'] : false;
		$finishedDateKey 			= !empty($param['finishedDate']) ? $param['finishedDate'] : null;
		$finishedDate 				= !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		$usingTas 					= !empty($param['usingTas']) ? $param['usingTas'] : 0;
		$frmFlg 					= !empty($param['frmFlg']) ? $param['frmFlg'] : '';
        $upload_id                  = !empty($param['upload_id']) ? $param['upload_id'] : [];
        $cid                        = !empty($param['cid']) ? $param['cid'] : 0;
        $did                        = !empty($param['did']) ? $param['did'] : 0;
        $is_sanitizing              = !empty($param['is_sanitizing']) ? $param['is_sanitizing'] : 0;

		// 複数のファイル名のクエリ
		$query_sub = DB::table("circular_document$finishedDate as D")
			->select(DB::raw('D.circular_id, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
            // PAC_5-2853 S
            ->where(function ($query) use ($is_sanitizing, $cids, $cid, $did) {
                if($is_sanitizing && $cid && $did){
                    $query->where('D.circular_id', '=', $cid)
                        ->where('D.id', '=', $did);
                }else{
                    $query->whereIn('D.circular_id', $cids);
                }
            })
            // PAC_5-2853 E
			->where(function ($query) use ($user) {
				$query->where(function ($query1) {
					$query1->where('confidential_flg', 0);
				});
				$query->orWhere(function ($query1) use ($user) {
					$query1->where('confidential_flg', 1)
						->where('create_company_id', $user->mst_company_id)
						->where('origin_env_flg', config('app.server_env'))
						->where('origin_edition_flg', config('app.edition_flg'))
						->where('origin_server_flg', config('app.server_flg'));
				});
			})
			->groupBy('circular_id');
        $special_circular_ids = [];
		// 帳票回覧判定
		// 帳票回覧あれば、帳票回覧です。
		if($frmFlg != '') {
			// 請求書　もしくは　その他 回覧取得
			$invoices = DB::table("circular$finishedDate as C")
				->select(DB::raw("C.id, C.edition_flg, C.env_flg, C.server_flg, C.origin_circular_id, C.update_at, U.title, D.file_names"))
				->leftJoinSub($query_sub, 'D', function ($join) {
					$join->on('C.id', 'D.circular_id');
				})
				->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
				->where('U.edition_flg', config('app.edition_flg'))
                // PAC_5-2853 S
                ->where(function ($query) use ($is_sanitizing, $cids, $cid) {
                    if($is_sanitizing && $cid){
                        $query->where('C.id', '=', $cid);
                    }else{
                        $query->whereIn('C.id', $cids);
                    }
                })
                // PAC_5-2853 E
				// 5:回覧前 と 2,3:完了 だけダウンロード対象
				->whereIn('C.circular_status', [CircularUtils::RETRACTION_STATUS, CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
			$circulars = DB::table("circular$finishedDate as C")
				->select(DB::raw("C.id, C.edition_flg, C.env_flg, C.server_flg, C.origin_circular_id, C.update_at, '' as title, D.file_names"))
				->leftJoinSub($query_sub, 'D', function ($join) {
					$join->on('C.id', 'D.circular_id');
				})
                // PAC_5-2853 S
                ->where(function ($query) use ($is_sanitizing, $cids, $cid) {
                    if($is_sanitizing && $cid){
                        $query->where('C.id', '=', $cid);
                    }else{
                        $query->whereIn('C.id', $cids);
                    }
                })
                // PAC_5-2853 E
				// 0 だけダウンロード対象
				->whereIn('C.circular_status', [CircularUtils::SAVING_STATUS])
				->union($invoices)
				->get()->keyBy('id');
		}else{
			// 回覧取得
			$circulars = DB::table("circular$finishedDate as C")
				->select(['C.id', 'C.edition_flg', 'C.env_flg', 'C.server_flg', 'C.origin_circular_id', 'C.update_at', 'C.special_site_flg', 'U.title', 'D.file_names'])
				->leftJoinSub($query_sub, 'D', function ($join) {
					$join->on('C.id', 'D.circular_id');
				})
				->join("circular_user$finishedDate as U", 'C.id', '=', 'U.circular_id')
				// ->where('circular_user.env_flg', config('app.server_env'))
				->where('U.edition_flg', config('app.edition_flg'))
                // PAC_5-2853 S
                ->where(function ($query) use ($is_sanitizing, $cids, $cid) {
                    if($is_sanitizing && $cid){
                        $query->where('C.id', '=', $cid);
                    }else{
                        $query->whereIn('C.id', $cids);
                    }
                })
                // PAC_5-2853 E
				// ->where('C.circular_status', '!=', CircularUtils::DELETE_STATUS)
				->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
				->get()->keyBy('id')->each(function ($item)use (&$special_circular_ids){
                    if($item->special_site_flg){
                        $special_circular_ids[]=$item->id;
                    }
                });
		}

		// 他環境のファイルを取得
		$env_document_data = DownloadRequestApiControllerUtils::getRelatedCircularDocumentsOfOtherEnvironment($user, $circulars, $param);

		// 現在環境のファイルを取得
		$cids = $circulars->keys();
		$circular_docs = DB::table("circular_document$finishedDate as cd")
            // PAC_5-2853 S
            ->where(function ($query) use ($is_sanitizing, $cids, $cid, $did) {
                if($is_sanitizing && $cid && $did){
                    $query->where('cd.circular_id', '=', $cid)
                        ->where('cd.id', '=', $did);
                }else{
                    $query->whereIn('cd.circular_id', $cids);
                }
            })
            ->join("circular$finishedDate as c","c.id",'=','cd.circular_id')
            // PAC_5-2853 E
            ->whereIn('cd.circular_id', $cids)
            ->where(function ($query) use ($user) {
                $query->where(function ($query1) {
                    $query1->where('cd.origin_document_id', 0);
                    $query1->where('cd.confidential_flg', 0);
                });
                $query->orWhere(function ($query1) use ($user) {
                    $query1->where('cd.create_company_id', $user->mst_company_id)
                        ->where('cd.origin_env_flg', config('app.server_env'))
                        ->where('cd.origin_edition_flg', config('app.edition_flg'))
                        ->where('cd.origin_server_flg', config('app.server_flg'));
                });
            })
            ->select('cd.id', 'cd.circular_id', 'cd.file_name', 'cd.origin_edition_flg', 'cd.origin_env_flg', 'cd.origin_server_flg','c.origin_circular_id')
            ->get()->keyBy('id');

		// 現在環境のファイルを取得

		$document_datas = DownloadRequestApiControllerUtils::getRelatedCircularDocumentsOfSameEnvironment($user,$circular_docs, $param,$upload_id);

		if (count($document_datas) === 0 && count($env_document_data) === 0) {
			throw new \Exception(__('message.warning.download_request.file_detail_get'));
		}
//		$env_doc_size = 0;
//		foreach ($env_document_data as $item) {
//			$env_doc_size += $item['file_size'];
//            unset($item);
//        }
        $count = 1;
        if(count($document_datas)){
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            $hasSignature = $company->esigned_flg == 1;
            // 履歴取得
            if ($check_add_stamp_history) {
                $stampApiClient = UserApiUtils::getStampApiClient();
                foreach ($circular_docs as $circular_doc) {
                    if ($circular_doc->origin_edition_flg != config('app.edition_flg')
                        || $circular_doc->origin_env_flg != config('app.server_env')
                        || $circular_doc->origin_server_flg != config('app.server_flg') || in_array($circular_doc->circular_id, $special_circular_ids) || $circular_doc->origin_circular_id) {
                        continue;
                    }
                    $resultBody = CircularDocumentUtils::getHistory($circular_doc->circular_id, $circular_doc->id, $user->mst_company_id, config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $finishedDate, $check_add_stamp_history);
                    if ($resultBody['status']) {
                        $result = $stampApiClient->post("signatureAndImpress", [
                            RequestOptions::JSON => [
                                'signature' => $hasSignature,
                                'data' => [
                                    [
                                        'circular_document_id' => $circular_doc->id,
                                        'pdf_data' => $resultBody['circular_document']->file_data,
                                        'append_pdf_data' => $resultBody['circular_document']->append_pdf,
                                        'stamps' => [],
                                        'texts' => [],
                                        'usingTas' => $usingTas
                                    ],
                                ],
                                'signatureKeyFile' => null,
                                'signatureKeyPassword' => null,
                            ]
                        ]);
                        $resData = json_decode((string)$result->getBody());
                        if ($resData->data) {
                            $document_datas[$circular_doc->id]->file_data = AppUtils::encrypt($resData->data[0]->pdf_data);
                        }
                        unset($resData);
                        unset($resultBody);
                    } else {
                        throw new \Exception(__('message.false.download_request.doc_histories_request', ['attribute' => $circular_doc->circular_id]));
                    }
                }
            }
            // timestamp のためタイムゾーン注意
            $now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;
            $doInputData=[];
            // Download Process Wait Data
            // 現在の環境のファイル
            foreach ($document_datas as $document_data) {
                $upload_status=isset($document_data->circular_document_id);
                if($upload_status){
                    $document_id = $document_data->circular_document_id;
                    if (isset($circular_docs[$document_id])) {
                        $circular_document = $circular_docs[$document_id];
                    };

                }
                $size = AppUtils::getFileSize($document_data->file_data);
                $doInputData[]= [
                    'state' => 0,
                    'download_request_id' => $dl_request_id,
                    'num' => $count,
                    'circular_document_id' =>$upload_status==true && $document_id?$circular_document->id:$document_data->id,
                    'document_data_id' => $document_data->id,
                    'document_data' => $document_data->file_data,
                    'create_at' => Carbon::now(),
                    'create_user' => $user->id,
                    'circular_id' =>$upload_status==true && $document_id? $circular_document->circular_id:0,
                    'file_name' => $upload_status==true && $document_id?$circular_document->file_name:$document_data->file_name,
                    'title' => $upload_status==true && $document_id?$circulars[$circular_document->circular_id]->title:$document_data->file_name,
                    'circular_update_at' =>$upload_status==true && $document_id? $circulars[$circular_document->circular_id]->update_at:Carbon::now(),
                    'file_size' => $size,
                ];
                $count++;
            }
            Log::debug('request id =' . $dl_request_id . ' ,circular ids =' .json_encode($cids));
            foreach ($doInputData as $downloadItem){
                DB::table('download_proc_wait_data')->insert($downloadItem);
            }
            unset($document_data);
            unset($document_datas);
        }
        
        if(count($env_document_data)){
            $envInputData=[];
            // 他の環境のファイル
            foreach ($env_document_data as $document_data) {
                foreach ($circular_docs as $circular_doc) {
                    if ($circular_doc->circular_id == $document_data['circular_id']) {
                        $circular_document = $circular_docs[$circular_doc->id];
                        break;
                    }
                }
                $size = AppUtils::getFileSize($document_data['file_data']);
                $envInputData[]=[
                    'state' => 0,
                    'download_request_id' => $dl_request_id,
                    'num' => $count,
                    'circular_document_id' => 0,
                    'document_data_id' => 0,
                    'document_data' => $document_data['file_data'],
                    'create_at' => Carbon::now(),
                    'create_user' => $user->id,
                    'circular_id' => $circular_document->circular_id,
                    'file_name' => $document_data['file_name'],
                    'title' => $document_data['title'],
                    'circular_update_at' => $document_data['circular_update_at'],
                    'file_size' => $size,
                ];
                $count++;
            }
            foreach ($envInputData as $envDownloadItem){
                DB::table('download_proc_wait_data')->insert($envDownloadItem);
            }
            unset($document_data);
            unset($env_document_data);
        }
        
	}

	/**
	 * 現在環境のファイルを取得
	 *
	 * @param $user 利用者情報
	 * @param $param 回覧文書情報
	 * @return array ファイルデータ
	 */
	public static function getRelatedCircularDocumentsOfSameEnvironment($user, $circular_docs, $param,$upload_id=[]):array
    {

		$cids 						= !empty($param['cids']) ? $param['cids'] : [];
		$finishedDateKey 			= !empty($param['finishedDate']) ? $param['finishedDate'] : null;
		$finishedDate 				= !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		$document_datas = array();
        $Long_document_datas=[];
        if (!empty($circular_docs)){
            $circular_ids_all = $circular_docs->keys()->toArray();
            // mysql最長クエリ
            $max_allowed_packet = DB::select("show variables like 'max_allowed_packet'")[0]->Value;
            foreach (array_chunk($circular_ids_all, 5) as $circular_ids_item){
                // 選択文書総容量
                $doc_size = array_sum(
                    array_column(
                        DB::table("document_data$finishedDate")
                            ->whereIn('circular_document_id', $circular_ids_item)
                            ->select(DB::raw('length(file_data) as data_len'))->get()->toArray(), 'data_len')
                );
                $upload_size=array_sum(
                    array_column(
                        DB::table("upload_data")
                            ->whereIn('id', $upload_id)
                            ->select(DB::raw('length(upload_data) as data_len'))->get()->toArray(), 'data_len')
                );
                if ($doc_size+$upload_size > $max_allowed_packet) {
                    throw new \Exception(__('message.warning.download_request.order_size_max'));
                }
                $document_data = DB::table("document_data$finishedDate")
                    ->whereIn('circular_document_id',$circular_docs->keys() )
                    ->select('id', 'circular_document_id', 'file_data')
                    ->get()
                    ->keyBy('circular_document_id')
                    ->toArray();
                foreach ($document_data as $key => $item) {
                    $document_datas[$key] = $item;
                }

            }
        }
        if(!empty($upload_id)){
            $Long_document_datas=DB::table("long_term_document as ltd")
                ->leftJoin('upload_data as ud','ud.id','=','ltd.upload_id')
                ->whereIn('ltd.upload_id', $upload_id)
                ->select('ltd.id','ud.upload_data as file_data','ltd.file_size','ltd.file_name','ltd.circular_id')
                ->get()
                ->toArray();
        }
		return $document_datas+$Long_document_datas;
	}

	/**
	 * 他環境のファイルを取得
	 *
	 * @param $user 利用者情報
	 * @param $param 回覧情報
	 * @return array ファイルデータ
	 */
	public static function getRelatedCircularDocumentsOfOtherEnvironment($user, $circulars, $param){
		$check_add_stamp_history 	= !empty($param['stampHistory']) ? $param['stampHistory'] : false;
		$finishedDateKey 			= !empty($param['finishedDate']) ? $param['finishedDate'] : null;
//        list($long_term_document_datas,$cids,$upload_id)=self::getLongTermData($ids);
		// 他環境env
		$other_env = null;
		// 他環境回覧ID集合
		$origin_env_circulars = [];
		$current_circulars_exits = false;
		foreach ($circulars as $key => $circular) {
			// 他環境存在の場合
			if ($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))) {
				$origin_env_circulars[$circular->env_flg . $circular->server_flg][] = ['circular_id' => $circular->id, 'origin_circular_id' => $circular->origin_circular_id];
			}
			//todo 現行側
			if ($circular->edition_flg == 0) {
				$current_circulars_exits = true;
			}
		}
		// 他環境ファイル集合
		$env_document_data = [];
		if (!empty($origin_env_circulars)) {

			foreach ($origin_env_circulars as $key => $origin_env_circular) {
				$env = substr($key, 0, 1);
				$server = substr($key, 1, strlen($key)-1);
				$envClient = EnvApiUtils::getAuthorizeClient($env, $server);

				if (!$envClient) {
					//TODO message
					throw new \Exception('Cannot connect to Env Api');
				}

				$company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
				$hasSignature = $company->esigned_flg == 1;

				// 他環境処理を呼び出し
				$response = $envClient->post("getEnvDocuments", [
					RequestOptions::JSON => ['create_company_id' => $user->mst_company_id, 'origin_env_flg' => config('app.server_env'), 'origin_server_flg' => config('app.server_flg'),
						'origin_edition_flg' => config('app.edition_flg'), 'circulars' => $origin_env_circular, 'finishedDate' => $finishedDateKey, 'check_add_stamp_history' => $check_add_stamp_history, 'hasSignature' => $hasSignature]
				]);

				if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
					Log::error($response->getBody());
					throw new \Exception('Cannot get env documents');
				}

				$result = json_decode($response->getBody(), true);

				$env_document_data = $result['document_data'];
			}
			unset($response);
			unset($result);
			unset($origin_env_circulars);
		}

		return $env_document_data;
	}

    // PAC_5-2447 add
    public static function getDownloadAttachment($user, $param, $dl_request_id)
    {
        $circula_id=$param['circular_id'];
        $longTermId=$param['id'];
        $attachments=DB::table('long_term_document')->where('id', $longTermId)->where('mst_company_id', $user->mst_company_id)->pluck('circular_attachment_json','circular_id')->toArray();
        $count = 1;
        $input=[];
        foreach ($attachments as $k=>$v){
            $v=json_decode($v);
            if(!empty($v)){
                array_walk_recursive($v,function($val, $key) use(&$input,$user,$v,$k,$dl_request_id,&$count){
                    if((isset($val->company_id,$val->confidential_flg)) && !(($val->company_id == $user->mst_company_id )||($val->confidential_flg == 0))){unset($v[$key]);}

                    if(isset($val->type)){
                        $file_content = Storage::disk($val->type)->get($val->server_url);
                    }else{
                        if(!self::handlerAttachment($k,$user->mst_company_id,$val->server_url)){
                            return ;
                        }
                        if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS){
                            $file_content =Storage::disk('s3')->get($val->server_url);
                        }else if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                            $file_content = Storage::disk('k5')->get($val->server_url);
                        }
                    }
                    $val->file_content = $file_content;
                    $size = $val->file_size;
                    $input[]=[
                        'state' => 0,
                        'download_request_id' => $dl_request_id,
                        'num' => $count,
                        'circular_document_id' => $k,
                        'document_data_id' => $k,
                        'document_data' => AppUtils::encrypt($file_content),
                        'create_at' => Carbon::now(),
                        'create_user' => $user->id,
                        'circular_id' => $k,
                        'file_name' => $val->file_name,
                        'title' => '',
                        'circular_update_at' =>Carbon::now(),
                        'file_size' => $size
                    ];
                    $count++;
                });
            }
        }
        DB::table('download_proc_wait_data')->insert($input);
        // ダウンロード要求情報取得
        $dl_req = DB::table('download_request')
            ->where('id', $dl_request_id)->first();

        if (!$dl_req) {
            Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
            return;
        }

        // 回覧文書ID
        $dl_proc_wait_datas = DB::table('download_proc_wait_data')
            ->where('download_request_id', $dl_request_id)
            ->select('circular_document_id', 'circular_id', 'file_name', 'document_data')->get();
        // 申請者情報
        $user_info = DB::table('mst_user')
            ->where('id', $dl_req->mst_user_id)
            ->select(['id', 'email', 'mst_company_id'])
            ->first();
        $zipPath = sys_get_temp_dir()."/download-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $user->mst_company_id, $user->id) . ".zip";
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == false) {
            throw new \Exception(__('message.false.download_request.zip_create'));
        }
        foreach ($dl_proc_wait_datas as $val){
            $zip->addEmptyDir($circula_id);
            $zip->addFromString($circula_id . '/' . $val->file_name, AppUtils::decrypt($val->document_data));

        }

        if (!$zip->close()) {
            throw new \Exception(__('message.false.download_request.zip_create'));
        }
        if ($dl_proc_wait_datas->count() != 1 && !file_exists($zipPath)) {
            if (file_exists($zipPath)) {
                throw new \Exception(__('message.false.download_request.compress_e', ['attribute' => $dl_proc_wait_datas->count(), 'path' => $zipPath]));
            } else {
                throw new \Exception(__('message.false.download_request.compress_n', ['attribute' => $dl_proc_wait_datas->count(), 'path' => $zipPath]));
            }
        }
        // 無害化サーバで無害化処理するか
        $isSanitizing = DB::table('mst_company')
            ->where('id', $dl_req->mst_company_id)->first()
            ->sanitizing_flg;

        DB::table('download_proc_wait_data')
            ->where('download_request_id', $dl_request_id)
            ->update([
                'state' => DownloadRequestUtils::PROC_PROCESS_END,
            ]);
        $data = file_get_contents($zipPath);
        // 完了お知らせ
        // 無害化サーバ経由時はここでは通知無し
        if($isSanitizing != 1) {
            $email_data = [
                'file_name' => $dl_req->file_name,
                'dl_period' => $dl_req->download_period
            ];
            if ($user_info && CircularUserUtils::checkAllowReceivedEmail($user_info->email, 'download', $user_info->mst_company_id,config('app.server_env'),config('app.edition_flg'),config('app.server_flg'))) {
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $user_info->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                    // パラメータ
                    json_encode($email_data, JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                    // メールボディ
                    trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                );
            }
        }
        return $data;
    }
    private static function handlerAttachment($intCircularID,$intCompanyID,$server_url){
        $objAttachment = DB::table("circular_attachment")->where("circular_id",$intCircularID)->where("server_url",$server_url)->where("status",1)->first();
        if(empty($objAttachment)){
            return false;
        }
        if($objAttachment->confidential_flg == 1  && $objAttachment->create_company_id != $intCompanyID){
            return false;
        }
        return true;
    }

    /**
     * 掲示板 添付ファイルダウンロード予約
     * @param $user object ユーザーの情報
     * @param $param array パラメータ
     * @param $dl_request_id int download_request.id
     * @return string|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getBbsAttachmentData($user, $param, $dl_request_id){
        try {
            $bbs_id = !empty($param['bbs_id']) ? $param['bbs_id'] : '';
            $filename = !empty($param['file_name']) ? $param['file_name'] : '';
            $s3path = DB::table('bbs')->where('id',$bbs_id)->first()->s3path;

            $bbs_file_name_attachment = 'BBS_';
            $file_data = Storage::disk('s3')->get($s3path.'/'.$bbs_file_name_attachment.$filename);
            $file_size = AppUtils::getFileSize(base64_encode($file_data));

            DB::table('download_proc_wait_data')->insert([
                'state' => 0,
                'download_request_id' => $dl_request_id,
                'num' => 1,
                'circular_document_id' => 0,
                'document_data_id' => 0,
                'document_data' => AppUtils::encrypt(base64_encode($file_data)),
                'create_at' => Carbon::now(),
                'create_user' => $user->id,
                'circular_id' => 0,
                'file_name' => $filename,
                'title' => '',
                'circular_update_at' => Carbon::now(),
                'file_size' => $file_size
            ]);

            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
                ->where('id', $dl_request_id)->first();

            if (!$dl_req) {
                Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
                return;
            }

            // 申請者情報
            $user_info = DB::table('mst_user')
                ->where('id', $dl_req->mst_user_id)
                ->select(['id', 'email', 'mst_company_id'])
                ->first();

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                ->where('id', $dl_req->mst_company_id)->first()
                ->sanitizing_flg;

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::PROC_PROCESS_END,
                ]);

            // 完了お知らせ
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];

                if ($user_info && CircularUserUtils::checkAllowReceivedEmail($user_info->email, 'download', $user_info->mst_company_id,config('app.server_env'),config('app.edition_flg'),config('app.server_flg'))) {
                    MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                        $user_info->email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                        // パラメータ
                        json_encode($email_data, JSON_UNESCAPED_UNICODE),
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                        // メールボディ
                        trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                    );
                }
            }
            return $file_data;
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            // リトライ
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $ex;
        }
    }

    /**
     * 文書プレビューダウンロード予約
     * @param $user object ユーザーの情報
     * @param $param array パラメータ
     * @param $dl_request_id int download_request.id
     * @return false|string|void
     * @throws \Exception
     */
    public static function getPreviewFileDownloadData($user, $param, $dl_request_id){
        try {
            $circular_document_id = !empty($param['document_id']) ? $param['document_id'] : '';//選択文書のID
            $all_company_history  = !empty($param['stampHistory']) ? $param['stampHistory'] : false;//全ての捺印履歴を付ける
            $self_company_history = !empty($param['addTextHistory']) ? $param['addTextHistory'] : false;//自社のみの捺印履歴を付ける
            $finishedDateKey 	  = !empty($param['finishedDate']) ? $param['finishedDate'] : null;//完了日キー
            $finishedDate 		  = !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');//完了日
            $usingTas             = !empty($param['usingTas']) ? $param['usingTas'] : 0;

            $document_data = DB::table("document_data$finishedDate")->where('circular_document_id',$circular_document_id)->first();
            $circular_document = DB::table("circular_document$finishedDate")->where('id',$circular_document_id)->first();
            $circular = DB::table("circular$finishedDate")->select('update_at')->where('id',$circular_document->circular_id)->first();

            //PDFデータと操作履歴可能取得
            $history_data = CircularDocumentUtils::getPDFHistory($circular_document_id, $user->mst_company_id, config('app.edition_flg'), config('app.server_env'), config('app.server_flg'), $finishedDate, $self_company_history);
            //タイムスタンプを付与 または　捺印履歴を付ける
            if ($all_company_history || (!$all_company_history && $usingTas) || $self_company_history){
                $stampApiClient = UserApiUtils::getStampApiClient();
                //PDFへの電子署名付加
                $hasSignature = DB::table('mst_company')->select('esigned_flg')->where('id', $user->mst_company_id)->value('esigned_flg');
                $result = $stampApiClient->post("signatureAndImpress", [
                    RequestOptions::JSON => [
                        'signature' => $hasSignature == 1,
                        'data' => [
                            [
                                'circular_document_id' => $circular_document_id,
                                'pdf_data' => AppUtils::decrypt($document_data->file_data),
                                'append_pdf_data' => ($all_company_history || $self_company_history) ? $history_data :null,
                                'stamps'=> [],
                                'texts'=> [],
                                'usingTas' => false,
                                'usingDTS' => $usingTas,
                            ],
                        ],
                        'signatureKeyFile' => null,
                        'signatureKeyPassword' => null,
                    ]
                ]);
                if ($result->getStatusCode() == StatusCodeUtils::HTTP_OK){
                    $resData = json_decode((string)$result->getBody());
                    if ($resData->data) {
                        $document_data->file_data = AppUtils::encrypt($resData->data[0]->pdf_data);
                    }
                }else{
                    Log::error('Log signatureAndImpress: '. $result->getBody());
                    throw new \Exception(__('message.false.download_request.stamp_request'));
                }
            }

            DB::table('download_proc_wait_data')->insert([
                'state' => 0,
                'download_request_id' => $dl_request_id,
                'num' => 1,
                'circular_document_id' => $circular_document_id,
                'document_data_id' => $document_data->id,
                'document_data' => $document_data->file_data,
                'create_at' => Carbon::now(),
                'create_user' => $user->id,
                'circular_id' => $circular_document->circular_id,
                'file_name' => $circular_document->file_name,
                'title' => '',
                'circular_update_at' => $circular->update_at,
                'file_size' => AppUtils::getFileSize($document_data->file_data)
            ]);

            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
                ->where('id', $dl_request_id)->first();

            if (!$dl_req) {
                Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
                return;
            }

            // 回覧文書ID
            $dl_proc_wait_datas = DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->select('circular_document_id', 'circular_id', 'file_name', 'document_data')->get();


            // 申請者情報
            $user_info = DB::table('mst_user')
                ->where('id', $dl_req->mst_user_id)
                ->select(['id', 'email', 'mst_company_id'])
                ->first();

            // 文書一件の場合
            $data = base64_decode(AppUtils::decrypt($dl_proc_wait_datas->first()->document_data));

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                ->where('id', $dl_req->mst_company_id)->first()
                ->sanitizing_flg;

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::PROC_PROCESS_END,
                ]);

            // 完了お知らせ
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];

                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $user_info->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                    // パラメータ
                    json_encode($email_data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                    // メールボディ
                    trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                );
            }

            return $data;
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            // リトライ
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $ex;
        }

    }

    /**
     * 添付ファイルダウンロード予約
     * @param $user object ユーザーの情報
     * @param $param array パラメータ
     * @param $dl_request_id int download_request.id
     * @return string|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getAttachmentData($user, $param, $dl_request_id){
        try {
            $circular_attachment_id = !empty($param['circular_attachment_id']) ? $param['circular_attachment_id'] : '';

            $attachment = DB::table('circular_attachment')->where('id',$circular_attachment_id)->first();

            if (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_AWS){
                $file_data = Storage::disk('s3')->get($attachment->server_url);
            }elseif (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5){
                $file_data = Storage::disk('k5')->get($attachment->server_url);
            }


            DB::table('download_proc_wait_data')->insert([
                'state' => 0,
                'download_request_id' => $dl_request_id,
                'num' => 1,
                'circular_document_id' => 0,
                'document_data_id' => 0,
                'document_data' => AppUtils::encrypt(base64_encode($file_data)),
                'create_at' => Carbon::now(),
                'create_user' => $user->id,
                'circular_id' => $attachment->circular_id,
                'file_name' => $attachment->file_name,
                'title' => '',
                'circular_update_at' => Carbon::now(),
                'file_size' => $attachment->file_size
            ]);

            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
                ->where('id', $dl_request_id)->first();

            if (!$dl_req) {
                Log::error('この申請データは存在しない。download_request_id:'.$dl_request_id);
                return;
            }


            // 申請者情報
            $user_info = DB::table('mst_user')
                ->where('id', $dl_req->mst_user_id)
                ->select(['id', 'email', 'mst_company_id'])
                ->first();


            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                ->where('id', $dl_req->mst_company_id)->first()
                ->sanitizing_flg;

            DB::table('download_proc_wait_data')
                ->where('download_request_id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::PROC_PROCESS_END,
                ]);

            // 完了お知らせ
            // 無害化サーバ経由時はここでは通知無し
            if($isSanitizing != 1) {
                $email_data = [
                    'file_name' => $dl_req->file_name,
                    'dl_period' => $dl_req->download_period
                ];

                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $user_info->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
                    // パラメータ
                    json_encode($email_data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
                    // メールボディ
                    trans('mail.SendDownloadReserveCompletedMail.body', $email_data)
                );
            }
            return $file_data;
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            // リトライ
            DB::table('download_request')
                ->where('id', $dl_request_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $ex;
        }
    }
}