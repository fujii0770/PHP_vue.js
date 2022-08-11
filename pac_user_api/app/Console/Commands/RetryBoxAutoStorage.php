<?php

namespace App\Console\Commands;

use App\Http\Utils\BoxUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Jobs\AutoStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RetryBoxAutoStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boxAutoStorage:retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


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
        $circular_auto_storage_histories = DB::table('circular_auto_storage_history')
            ->select('circular_id', 'mst_company_id')
            ->where('result', 2)
            ->where('failed_count', '<', 3)
            ->get();
        foreach ($circular_auto_storage_histories as $history) {
            //ファイルの生成
            dispatch((new AutoStorage($history->mst_company_id, $history->circular_id))->onQueue('default'));
        }
    }

}
