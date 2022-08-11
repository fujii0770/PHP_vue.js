<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class LoginLayoutChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login:layoutchange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ログイン画面状態変更バッチ';

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
        Log::channel('cron-daily')->debug("ログイン画面状態変更バッチ開始");
        try {

            $s3path = config('filesystems.prefix_path') . '/' . config('app.s3_login_root_folder') . '/loginSetting/';

            //画像変更処理
            $path = dirname(__FILE__, 4) . '/public/images/login/';

            $path2 = dirname(__FILE__, 5) . '/pac_user/public/images/login/';

            if (Storage::disk('s3')->exists($s3path . '/' . 'show2.png')) {

                $contents = Storage::disk('s3')->get($s3path . '/' . 'show2.png');

                header('Content-type: image/png');

                Storage::disk('login')->put($path . 'show2.png', $contents);

                $contents = Storage::disk('login')->get($path . 'show2.png');

                move_uploaded_file($contents, $path . 'show2.png');

                copy($path . 'show2.png', $path2 . 'show2.png');

            }


            //login_r.txt書き換え
            $file1 = dirname(__FILE__, 4) . '/public/login_r.txt';

            $file2 = dirname(__FILE__, 5) . '/pac_user/public/login_r.txt';

            if (Storage::disk('s3')->exists($s3path . '/' . 'login_r.txt')) {

                $contents = Storage::disk('s3')->get($s3path . '/' . 'login_r.txt');

                $fp = @fopen($file1, 'w');

                fwrite($fp, $contents);
                fclose($fp);

                $fp2 = @fopen($file2, 'w');

                fwrite($fp2, $contents);
                fclose($fp2);

            }

            //top_link.txt書き換え
            $file1 = dirname(__FILE__, 4) . '/public/top_link.txt';

            $file2 = dirname(__FILE__, 5) . '/pac_user/public/top_link.txt';

            if (Storage::disk('s3')->exists($s3path . '/' . 'top_link.txt')) {

                $contents = Storage::disk('s3')->get($s3path . '/' . 'top_link.txt');

                $fp = @fopen($file1, 'w');

                fwrite($fp, $contents);
                fclose($fp);

                $fp2 = @fopen($file2, 'w');

                fwrite($fp2, $contents);
                fclose($fp2);

            }

            Log::channel('cron-daily')->debug("ログイン画面状態変更バッチ終了");
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('ログイン画面状態変更失敗');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}