<?php


namespace App\Console\Commands;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class DeleteTimeoutBbs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bbs_timeout:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '掲示終了日が過ぎた掲示物を削除します';

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
        Log::channel('cron-daily')->debug("delete timeout bbs start");

        try{
            $yesterday = Carbon::yesterday();
            $timeout_bbs = DB::table("bbs")
                ->where('end_date','<=',$yesterday)
                ->get();
            foreach ($timeout_bbs as $bbs){
                $s3path = $bbs->s3path;
                if (Storage::disk('s3')->exists($s3path)){
                    Storage::disk('s3')->delete($s3path);
                }
            }
            DB::table("bbs")->where('end_date','<=',$yesterday)->delete();
            Log::channel('cron-daily')->debug('delete timeout bbs finished');
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('delete timeout bbs failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }
}