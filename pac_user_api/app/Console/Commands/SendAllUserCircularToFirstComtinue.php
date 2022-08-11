<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SendAllUserCircularToFirstComtinue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'circular:SendAllUserCircularToFirstComtinue {circular_id} {params}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send All User Circular To First Comtinue';

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
        $circular_id = $this->argument('circular_id');
        $strParams = $this->argument('params');
        $arrParams = json_decode($strParams,true);

        // Get Current Circular
        $objCircularData = DB::table("circular")->where("id",$circular_id)->first();

        try {
            $request = Request::create("/circulars/$circular_id/users/handlerCircularUserInsert", 'POST',[
                'circular_id' => $circular_id
            ]);//创建request

            $request->merge($arrParams);
            $objCircularUserController = app()->make("App\\Http\\Controllers\\API\\CircularUserAPIController");

            app()->call([$objCircularUserController,'handlerCircularUserInsert'],[
                'circular_id' => $circular_id,
                'arrParams' => $arrParams,
                'request' => $request,
            ]);
        } catch (\Exception $e) {
            Log::channel('cron-daily')->warning($e->getMessage().$e->getTraceAsString());
            return ;
        }

        Log::channel('cron-daily')->debug("一斉送信  circular_id $circular_id SUCCESS");
    }
}
