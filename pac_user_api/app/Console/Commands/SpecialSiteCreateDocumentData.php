<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\SpecialApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord;


class SpecialSiteCreateDocumentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'specialSite:createDocumentData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'special site';

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
        DB::table('special_site_document_data_request')->where('status', 0)->update([
            'status' => 1
        ]);
        $requests = DB::table('special_site_document_data_request')->where('status', 1)->orderBy('id')->get();
        if (count($requests) > 0) {
            foreach ($requests as $request) {
                try {
                    $client = SpecialApiUtils::getAuthorizeClient();
                    if (!$client) {
                        $this->failed($request->id);
                        Log::channel('cron-daily')->error('文書データ作成処理：特設サイトAPI接続失敗しました。');
                        return;
                    }

                    $response = $client->post("/sp/api/get-input-data", [
                        RequestOptions::JSON => [
                            'company_id' => $request->company_id,
                            "env_flg" => config('app.server_env'),
                            "edition_flg" => config('app.edition_flg'),
                            "server_flg" => config('app.server_flg'),
                            "entered_circular_token" => $request->token,
                        ]
                    ]);
                    $response_dencode = json_decode($response->getBody(), true);  //配列へ
                    if ($response->getStatusCode() == 200) {
                        $response_body = json_decode($response->getBody(), true);  //配列へ
                        if ($response_body['status'] != 'success') {
                            $this->failed($request->id);
                            Log::channel('cron-daily')->error('文書データ作成処理：特設サイトAPI呼出失敗しました。:リクエストID=' . $request->id . ' status=' . $response->getStatusCode());
                            Log::channel('cron-daily')->error($response_dencode);
                            continue;
                        }
                        $circular_infos = $response_body['result']['input_inf'];
                    } else {
                        $this->failed($request->id);
                        Log::channel('cron-daily')->error('文書データ作成処理：特設サイトAPI呼出失敗しました。:リクエストID=' . $request->id . ' status=' . $response->getStatusCode());
                        Log::channel('cron-daily')->error($response_dencode);
                        continue;
                    }
                    //パラメーター作成
                    $template_placeholder_data = DB::table('template_placeholder_data')->where('template_file_id', $request->template_file_id)
                        ->select(['id', 'template_placeholder_name'])
                        ->get()
                        ->pluck('template_placeholder_name', 'id');

                    $input_data = [];
                    foreach ($circular_infos as $circular_info) {
                        $input_data[$template_placeholder_data[$circular_info['template_placeholder_id']]] = $circular_info['input_data'];
                    }
                    $templateDirectory = config('app.edition_flg') . '/' . config('app.server_env') . '/' . config('app.server_flg') . '/' . $request->company_id . '/' . $request->id;

                    $placeholderList = DB::table('template_file')
                        ->leftjoin('template_placeholder_data', 'template_file.id', '=', 'template_placeholder_data.template_file_id')
                        ->where('template_file.id', $request->template_file_id)
                        ->get();

                    if (count($placeholderList) <= 0) {
                        $this->failed($request->id);
                        Log::channel('cron-daily')->error('文書データ作成処理：文書データ見えない。');
                        continue;
                    }
                    $arr = explode('.', $placeholderList[0]->location);
                    $suffix = end($arr);
                    $s3_relative_path = str_replace(env('AWS_URL', ''), '', $placeholderList[0]->location);
                    $local_relative_path = "$templateDirectory/original.$suffix";
                    Log::channel('cron-daily')->debug("文書データ作成処理のファイルパス：$s3_relative_path");
                    $getFile = Storage::disk('s3')->get($s3_relative_path);
                    $isStore = Storage::disk('local')->put("special/$local_relative_path", $getFile);

                    if (!$isStore) {
                        $this->failed($request->id);
                        Log::channel('cron-daily')->error('文書データ作成処理：s3から文書ダウンロード失敗しました。');
                        continue;
                    }
                    $filePath = storage_path("app/special/$templateDirectory/original.$suffix");
                    // excel file
                    if ($placeholderList[0]->document_type === 0) {
                        if (!$placeholderList[0]->cell_address) {
                            Log::channel('cron-daily')->debug('文書データ作成処理：Excelファイル編集開始、inputなし');
                            $filePath_process = $filePath;
                        } else {
                            Log::channel('cron-daily')->debug('文書データ作成処理：Excelファイル編集開始、inputあり');
                            $reader = new XlsxReader();
                            $reader->setReadDataOnly(false);
                            $spreadsheet = $reader->load($filePath);
                            $sheet = $spreadsheet->getActiveSheet();

                            foreach ($placeholderList as $value) {
                                $cellData = $sheet->getCell($value->cell_address)->getValue();
                                $newCellData = str_replace($value->template_placeholder_name, $input_data[$value->template_placeholder_name], $cellData);
                                $sheet->setCellValue($value->cell_address, $input_data[$value->template_placeholder_name]);
                            }

                            $writer = new XlsxWriter($spreadsheet);
                            $filePath_process = storage_path("app/special/$templateDirectory/process.$suffix");
                            $writer->save($filePath_process);
                        }
                    } else {
                        if (!$placeholderList[0]->template_placeholder_name) {
                            Log::channel('cron-daily')->debug('文書データ作成処理：Wordファイル編集開始、inputなし');
                            $filePath_process = $filePath;
                        } else {
                            Log::channel('cron-daily')->debug('文書データ作成処理：Wordファイル編集開始、inputあり');
                            $templateProcessor = new PhpWord\TemplateProcessor($filePath);
                            foreach ($placeholderList as $value) {
                                $templateProcessor->setValue($value->template_placeholder_name, $input_data[$value->template_placeholder_name]);
                            }
                            $filePath_process = storage_path("app/special/$templateDirectory/process.$suffix");
                            $templateProcessor->saveAs($filePath_process);
                        }
                    }
                    // excel or docx =》pdf
                    $stored_basename = hash('SHA256', $request->id . rand() . AppUtils::getUnique()) . '.pdf';
                    $out_path = storage_path("app/special/$templateDirectory/$stored_basename");
                    $errorMessage = AppUtils::tryConvertOfficeToPdf($filePath_process, $out_path);
                    if ($errorMessage) {
                        // 変換失敗
                        $this->failed($request->id);
                        Log::channel('cron-daily')->error('文書データ作成処理失敗しました。' . $errorMessage);
                        continue;
                    }
                    $circular_user = DB::table('circular_user')->where('circular_id', $request->circular_id)->orderBy('id')->get()->toArray();
                    DB::beginTransaction();
                    DB::table('document_data')->updateOrInsert(
                        ['circular_document_id' => $request->circular_document_id],
                        [
                            'circular_document_id' => $request->circular_document_id,
                            'file_data' => AppUtils::encrypt(\base64_encode(\file_get_contents($out_path))),
                            'create_at' => Carbon::now(),
                            'create_user' => 'Shachihata',
                        ]);
                    // 会社をまたいで申請した場合、文書コピー処理が追加されます
                    if (count($circular_user) > 1 && $circular_user[1]->parent_send_order == 1) {
                        $copy_document = DB::table('circular_document')->where('id', $request->circular_document_id)->first();
                        $copy_document_id = DB::table('circular_document')->insertGetId([
                                'circular_id' => $request->circular_id,
                                'origin_env_flg' => $copy_document->origin_env_flg,
                                'origin_edition_flg' => $copy_document->origin_edition_flg,
                                'origin_server_flg' => $copy_document->origin_server_flg,
                                'origin_document_id' => $copy_document->id,
                                'parent_send_order' => 1,
                                'create_company_id' => $copy_document->create_company_id,
                                'create_user_id' => $copy_document->create_user_id,
                                'confidential_flg' => $copy_document->confidential_flg,
                                'file_name' => $copy_document->file_name,
                                'create_user' => $copy_document->create_user,
                                'create_at' => $copy_document->create_at,
                                'update_at' => $copy_document->update_at,
                                'update_user' => $copy_document->update_user,
                                'document_no' => $copy_document->document_no,
                                'file_size' => $copy_document->file_size,
                            ]
                        );
                        DB::table('document_data')->Insert(
                            [
                                'circular_document_id' => $copy_document_id,
                                'file_data' => AppUtils::encrypt(\base64_encode(\file_get_contents($out_path))),
                                'create_at' => Carbon::now(),
                                'create_user' => 'Shachihata',
                            ]);
                        $circular_operation_histories = DB::table('circular_operation_history')
                            ->where('circular_id', $request->circular_id)
                            ->where('circular_document_id', $request->circular_document_id)
                            ->get();
                        $operation_history_id = 0;
                        foreach ($circular_operation_histories as $circular_operation_history) {
                            $circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
                                'circular_id' => $circular_operation_history->circular_id,
                                'circular_document_id' => $copy_document_id,
                                'operation_email' => $circular_operation_history->operation_email,
                                'operation_name' => $circular_operation_history->operation_name,
                                'acceptor_email' => $circular_operation_history->acceptor_email,
                                'acceptor_name' => $circular_operation_history->acceptor_name,
                                'circular_status' => $circular_operation_history->circular_status,
                                'create_at' => $circular_operation_history->create_at,
                            ]);
                            if ($circular_operation_history->circular_status == 4) {
                                $operation_history_id = $circular_operation_history_id;
                            }
                        }
                        $document_comment_infos = DB::table('document_comment_info')
                            ->where('circular_document_id', $request->circular_document_id)
                            ->get();
                        foreach ($document_comment_infos as $document_comment_info) {
                            DB::table('document_comment_info')->insert([
                                'circular_document_id' => $copy_document_id,
                                'circular_operation_id' => $operation_history_id,
                                'parent_send_order' => $document_comment_info->parent_send_order,
                                'name' => $document_comment_info->name,
                                'email' => $document_comment_info->email,
                                'text' => $document_comment_info->text,
                                'private_flg' => $document_comment_info->private_flg,
                                'create_at' => $document_comment_info->create_at,
                            ]);
                        }
                    }
                    DB::table('circular_document')->where('id', $request->circular_document_id)->update([
                        'origin_document_id' => -1,
                        'parent_send_order' => 0,
                    ]);
                    DB::table('special_site_document_data_request')->where('id', $request->id)->update(['status' => 2, 'update_at' => Carbon::now()]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->failed($request->id);
                    Log::channel('cron-daily')->error('文書データ作成処理：システムエラー発生しました。');
                    Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
                    continue;
                }
            }
        }
    }

    /**
     * ミリセカンド
     * @param string $time
     */
    protected function wait($time)
    {
        $wait = $time * 1000 * 1000;
        usleep($wait);
    }

    protected function failed($id)
    {
        DB::table('special_site_document_data_request')->where('id', $id)->update(['status' => 3, 'update_at' => Carbon::now()]);
    }

}
