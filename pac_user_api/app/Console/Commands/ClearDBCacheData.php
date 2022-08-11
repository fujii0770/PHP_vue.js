<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Storage;

class ClearDBCacheData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearCurrentFileCache:clearAll2669DBCache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'laravelによって自動的に削除されないファイルキャッシュをクリア--DB';

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
        Log::channel('cron-daily')->info('----------------- DELETE 2669 Cache START  ------------------');
        $intNowTime = time();
        DB::table("cache_user_api")->where("expiration",'<',$intNowTime)->where("key",'like', "%stamp_cache_%")->delete();
        Log::channel('cron-daily')->info('----------------- DELETE 2669 Cache END  ------------------');
    }
}
