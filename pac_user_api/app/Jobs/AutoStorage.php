<?php

namespace App\Jobs;

use App\Http\Utils\AppUtils;
use App\Http\Utils\BoxUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\EnvApiUtils;
use DB;
use Exception;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Storage;

class AutoStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    private $company_id;
    private $circular_id;
    private $auto_storage_his_id; // 履歴テーブルのid circular_auto_storage_history.id
    private $failed_count = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($company_id, $circular_id)
    {
        $this->company_id = $company_id;
        $this->circular_id = $circular_id;
        // 存在するかどうかを判断し(キューの再試行の場合)
        $circular_auto_storage_history = DB::table('circular_auto_storage_history')
            ->where('circular_id', $this->circular_id)
            ->where('mst_company_id', $this->company_id)
            ->first();

        if ($circular_auto_storage_history) {
            $this->auto_storage_his_id = $circular_auto_storage_history->id;
            $this->failed_count = $circular_auto_storage_history->failed_count;
        } else {
            // get circular info
            // file_name つづり合わせ
            $circular_document_sub = DB::table('circular_document')
                ->select(DB::raw('circular_document.circular_id, GROUP_CONCAT(circular_document.file_name  ORDER BY circular_document.id ASC SEPARATOR \', \') as file_names'))
                ->where('circular_document.circular_id', $this->circular_id)
                ->where(function ($query) use ($company_id) {
                    $query->where(function ($query1) {
                        $query1->where('origin_document_id', 0);
                        $query1->where('confidential_flg', 0);
                    });
                    $query->orWhere(function ($query1) use ($company_id) {
                        $query1->where('create_company_id', $company_id)
                            ->where('confidential_flg', 1)
                            ->where('origin_env_flg', config('app.server_env'))
                            ->where('origin_edition_flg', config('app.edition_flg'))
                            ->where('origin_server_flg', config('app.server_flg'));
                    });
                })
                ->groupBy('circular_document.circular_id');

            // history from circular
            $auto_history = DB::table('circular')
                ->joinSub($circular_document_sub, 'D', function ($query) {
                    $query->on('circular.id', '=', 'D.circular_id');
                })
                ->join('circular_user', 'circular.id', 'circular_user.circular_id')
                ->where('circular.id', $this->circular_id)
                ->where('circular_user.parent_send_order', '0')
                ->where('circular_user.child_send_order', '0')
                ->select('circular.id', 'circular_user.email', 'circular_user.name', 'circular_user.title', 'D.file_names')
                ->first();

            $auto_storage_his_id = DB::table('circular_auto_storage_history')->insertGetId([
                'circular_id' => $this->circular_id,
                'mst_company_id' => $this->company_id,
                'applied_email' => $auto_history->email,
                'applied_name' => $auto_history->name,
                'title' => $auto_history->title,
                'file_name' => $auto_history->file_names,
                'result' => BoxUtils::BOX_AUTOMATIC_STORAGE_DEFAULT,
                'create_at' => Carbon::now(),
            ]);
            $this->auto_storage_his_id = $auto_storage_his_id;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $settings = BoxUtils::getAutoStorageBoxSettings($this->company_id);
            if (!$settings) {
                throw new \Exception(__('message.false.auto_storage.no_settings', ['company_id' => $this->company_id]));
            }

            $documents_data = CircularDocumentUtils::getDocumentsDataByCircular($this->circular_id, $this->company_id, '', $settings['hasHistory'], false);
            if (!$documents_data) {
                throw new \Exception(__('message.false.auto_storage.circular_not_exist'));
            }
            $folder_name = '';
            $box_remote_folder_id = '';
            $folder_to_store = "";
            $access_token = BoxUtils::refreshAccessToken($settings['box_refresh_token'], $this->company_id, true);
            if (!$access_token) {
                throw new \Exception(__('message.false.auto_storage.token', ['company_id' => $this->company_id]));
            }
            foreach ($documents_data as $document) {
                $document = (array)$document;
                if (!$folder_name) {
                    // フォルダ作成 {email_subject(file_name)_document_id(encryp)}
                    $folder_name = BoxUtils::getBoxFolderName($document['create_user'], trim($document['title']), $document['file_name'], $document['circular_document_id']);
                    $folder_to_store = $settings['box_enabled_folder_to_store'] . "\\" . $folder_name;
                    $create_box_folder_res = BoxUtils::createBoxFolder($access_token, $folder_name, $settings['box_auto_save_folder_id'], $this->company_id);
                    if (!$create_box_folder_res) {
                        throw new \Exception(__('message.false.auto_storage.create_folder', ['circular_id' => $this->circular_id]));
                    }
                    $box_remote_folder_id = $create_box_folder_res->id;
                }
                foreach ($settings['file'] as $key => $item) {
                    $box_auto_file = BoxUtils::makeBoxDocuments($document, $item['history'], $item['signature'], $item['name'], $settings['signatureKeyFile'], $settings['signatureKeyPassword']);

                    if (!$box_auto_file) {
                        throw new \Exception(__('message.false.auto_storage.create_documents', ['circular_id' => $this->circular_id]));
                    }

                    $params['filename'] = $box_auto_file['file_name'];
                    $params['folder_id'] = $box_remote_folder_id;
                    $pdf_data = $box_auto_file['file_data'];
                    $server_file_name = hash('SHA256', $box_auto_file['file_name'] . rand() . AppUtils::getUnique());
                    $stored_path = 'auto_storage_tmp/';
                    Storage::disk('local')->put($stored_path . $server_file_name . '.pdf', base64_decode($pdf_data));
                    $pdfPath = $stored_path . $server_file_name . '.pdf';
                    $upload_result = BoxUtils::autoStorageToBox($access_token, $params, storage_path('app') . '/' . $pdfPath, $this->company_id);
                    // tmp file delete
                    if (Storage::disk('local')->exists($pdfPath)) {
                        Storage::disk('local')->delete($pdfPath);
                    }
                    if (!$upload_result) {
                        throw new \Exception(__('message.false.auto_storage.send_document', ['circular_id' => $this->circular_id]));
                    }
                }
            }

            DB::table('circular_auto_storage_history')->where('id', $this->auto_storage_his_id)->update([
                'result' => BoxUtils::BOX_AUTOMATIC_STORAGE_SUCCESS,
                'route' => $folder_to_store,
                'update_at' => Carbon::now(),
            ]);

            DB::table('circular')->where('id', $this->circular_id)->update([
                'circular_status' => CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS
            ]);

            // その他の環境の場合
            $other_envs = [];
            $circular_users = DB::table('circular_user')->where('circular_id', $this->circular_id)->select('edition_flg', 'env_flg', 'server_flg')->distinct()->get();
            foreach ($circular_users as $circular_user) {
                if ($circular_user->edition_flg == config('app.edition_flg')) {
                    if ($circular_user->env_flg != config('app.server_env') || $circular_user->server_flg != config('app.server_flg')) {
                        $other_envs[] = [$circular_user->env_flg, $circular_user->server_flg];
                    }
                }
            }
            foreach ($other_envs as $other_env) {
                $envClient = EnvApiUtils::getUnauthorizeClient($other_env[0], $other_env[1]);
                if (!$envClient) {
                    Log::error('自動保存:Cannot connect to other Env Api Client');
                    return $this->sendError(['status' => false, 'data' => null], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
                }
                $response = $envClient->post('public/circulars/' . $this->circular_id . '/autoStorageUpdateStatus', [
                    RequestOptions::JSON => ['edition_flg' => config('app.edition_flg'), 'env_flg' => config('app.server_env'), 'server_flg' => config('app.server_flg'),
                        'circular_status' => CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS
                    ]
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                    Log::error('自動保存:other Env Api autoStorageUpdateStatus failed');
                    Log::error($response->getBody());
                    return $this->sendError(['status' => false, 'data' => null], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch (\Exception $e) {
            Log::alert($e->getMessage());
            $this->job->fail($e);
        }
    }

    public function failed(Exception $exception)
    {
        Log::debug('自動保存:連携失敗履歴番号= ' . $this->auto_storage_his_id);
        Log::error($exception->getMessage() . $exception->getTraceAsString());
        DB::table('circular_auto_storage_history')->where('id', $this->auto_storage_his_id)->update([
            'result' => BoxUtils::BOX_AUTOMATIC_STORAGE_FAIL,
            'failed_count' => $this->failed_count + 1,
            'update_at' => Carbon::now(),
        ]);
    }
}
