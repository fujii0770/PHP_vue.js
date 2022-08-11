<?php

namespace App\Console\Commands;
use App\Http\Utils\AppUtils;
use App\Models\UsageSituation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\EnvApiUtils;
use GuzzleHttp\RequestOptions;


class LowercaseEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:lowercase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert email to lowercase';

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
        Log::channel('cron-daily')->debug("Convert email to lowercase start");

        try{
            DB::statement("UPDATE mst_user SET email = TRIM(LOWER(REPLACE(CONVERT(email USING ASCII), '?', ''))) where option_flg = 0");
            DB::statement("UPDATE mst_admin SET email = TRIM(LOWER(REPLACE(CONVERT(email USING ASCII), '?', '')))");
            DB::statement("UPDATE address SET email = TRIM(LOWER(REPLACE(CONVERT(email USING ASCII), '?', '')))");
            DB::statement("UPDATE circular_user SET email = TRIM(LOWER(REPLACE(CONVERT(email USING ASCII), '?', '')))");
            DB::statement("UPDATE favorite_route SET email = TRIM(LOWER(REPLACE(CONVERT(email USING ASCII), '?', '')))");
            DB::statement("UPDATE mst_audit SET email = TRIM(LOWER(REPLACE(CONVERT(email USING ASCII), '?', '')))");

            Log::channel('cron-daily')->debug('Convert email to lowercase finished');
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to Convert email to lowercase failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
}
