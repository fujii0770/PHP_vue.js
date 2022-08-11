<?php


namespace App\Console\Commands;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Http\Utils\AppUtils;

class TokenClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:token_Access_Remember';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ユーザ削除、無効時、accessToken削除、rememberToken削除';

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
        Log::channel('cron-daily')->debug("delete accessToken　rememberToken start");

        try{

            // ユーザ削除、無効時、accessToken削除
            DB::table('oauth_access_tokens')
                ->join('mst_user', 'oauth_access_tokens.user_id', 'mst_user.id')
                ->where('oauth_access_tokens.scopes', AppUtils::SCOPES_TYPE_USER)
                ->where('mst_user.state_flg', '<>', AppUtils::STATE_VALID)
                ->delete();
            DB::table('oauth_access_tokens')
                ->join('mst_audit', 'oauth_access_tokens.user_id', 'mst_audit.id')
                ->where('oauth_access_tokens.scopes', AppUtils::SCOPES_TYPE_AUDIT)
                ->where('mst_audit.state_flg', '<>', AppUtils::STATE_VALID)
                ->delete();

            // ユーザ削除、無効時、rememberToken削除
            DB::table('mst_admin')->where('state_flg', '<>', AppUtils::STATE_VALID)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);
            DB::table('mst_user')->where('state_flg', '<>', AppUtils::STATE_VALID)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);
            DB::table('mst_audit')->where('state_flg', '<>', AppUtils::STATE_VALID)
                ->where('remember_token', '!=', '')
                ->update(['remember_token' => '']);

            Log::channel('cron-daily')->debug('delete accessToken　rememberToken finished');
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('delete accessToken　rememberToken failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
}