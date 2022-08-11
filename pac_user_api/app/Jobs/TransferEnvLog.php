<?php

namespace App\Jobs;

use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\OperationsHistoryUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;

// 操作履歴転送用
class TransferEnvLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // don't retry

    private $env;
    private $requestBody;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $records, $env_flg, $server_flg) {
        $this->env = [
            'env_flg' => $env_flg,
            'server_flg' => $server_flg,
        ];
        $this->requestBody = [
            'email' => $email,
            'records' => $records,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $env = $this->env;
        $body = $this->requestBody;

        try {
            $envClient = EnvApiUtils::getAuthorizeClient($env['env_flg'], $env['server_flg']);
            if (!$envClient) {
                throw new \Exception('Cannot connect to Env Api');
            }

            $response = $envClient->post('store-env-log', [
                RequestOptions::JSON => $body
            ]);

            if($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::error($response->getBody());
                throw new \Exception('Log transfer not accepted');
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());

            // 失敗した もしくは 失敗した可能性あり
            OperationsHistoryUtils::storeRecordsAsFailedLog($body['records'],
                "ログ転送異常終了: $env[env_flg], $env[server_flg], $body[email]");
        }
    }
}
