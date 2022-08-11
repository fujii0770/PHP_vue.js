<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\StatusCodeUtils;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExpireAuditAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire Audit Account';

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
     * 監査用アカウントの自動削除バッジ
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        Log::channel('cron-daily')->debug('Run to ExpireAuditAccount');

        try{
            $expiredAudits = DB::table('mst_audit as a')
                ->select('a.id','a.email','a.account_name','a.mst_company_id','a.state_flg','c.system_name','c.company_name')
                ->leftJoin('mst_company as c','c.id','a.mst_company_id')
                ->where('a.state_flg', '!=', AppUtils::STATE_DELETE)
                ->where('a.expiration_date', '<', Carbon::now())
                ->get();

            if (count($expiredAudits) > 0){
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client){
                    Log::channel('cron-daily')->error('Run to ExpireAuditAccount failed. Cannot connect to ID App.');
                }

                foreach ($expiredAudits as $expiredAudit){
                    DB::beginTransaction();
                    try{
                        DB::table('mst_audit')
                            ->where('id', $expiredAudit->id)
                            ->update([
                                'email' => $expiredAudit->email . '.del',
                                'state_flg' => AppUtils::STATE_DELETE,
                                'update_at' => Carbon::now(),
                            ]);
                        // ユーザ無効時、rememberToken削除
                        DB::table('mst_audit')->where('id', $expiredAudit->id)
                            ->where('remember_token', '!=', '')
                            ->update(['remember_token' => '']);

                        $apiUser = [
                            "user_email" => $expiredAudit->email,
                            "email"=> strtolower($expiredAudit->email),
                            "contract_app"=> config('app.edition_flg'),
                            "app_env"=> config('app.server_env'),
                            "contract_server"=> config('app.server_flg'),
                            "user_auth"=> AppUtils::AUTH_FLG_AUDIT,
                            "user_first_name"=> $expiredAudit->account_name,
                            "company_name"=> $expiredAudit->company_name,
                            "company_id"=> $expiredAudit->mst_company_id,
                            "status"=> AppUtils::STATE_INVALID,
                            "system_name"=>$expiredAudit->system_name,
                            "update_user_email" => 'master-pro@shachihata.co.jp',
                        ];
                        $result = $client->put("users", [
                            RequestOptions::JSON => $apiUser
                        ]);
                        if ($result->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                            DB::commit();
                        }elseif ($result->getStatusCode() != StatusCodeUtils::HTTP_OK){
                            DB::rollBack();
                            Log::channel('cron-daily')->error("Call ID App Api to update audit account failed. Response Body " . $result->getBody());
                        }
                    }catch (\Exception $e){
                        DB::rollBack();
                        Log::channel('cron-daily')->error('Run to ExpireAuditAccount failed');
                        Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
                        throw $e;
                    }
                }
            }

        }catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to ExpireAuditAccount failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }

        Log::channel('cron-daily')->debug('Run to ExpireAuditAccount finished');
    }
}
