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

class ClearFileCacheData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearCurrentFileCache:clearAll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'laravelによって自動的に削除されないファイルキャッシュをクリア--FILE';

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
        Log::channel('cron-daily')->debug('map cache data dir');

        $storage = Cache::store('file')->getStore(); // will return instance of FileStore
 
        $filesystem = $storage->getFilesystem(); // will return instance of Filesystem
        
        $strDirPath = config('cache.stores.file.path');
        $arrDeleteFileData = [];
        $arrDeleteDirData = [];

        Log::channel('cron-daily')->info('----------------- DELETE 2322 Cache START  ------------------');
        $intHandNum = 0;
        foreach ($filesystem->directories($strDirPath) as $dir) {
            $boolIsEmpty = true;
            if($intHandNum >= 5){
                sleep(1);
                $intHandNum = 0;
            }
            foreach ($filesystem->directories($dir) as $sonDir) {
                $arrCurrentFile = $filesystem->allFiles($sonDir);
                foreach($arrCurrentFile as $fileKey => $file){
                    if(strlen(basename($file)) == 40){
                        $arrDeleteFileData[] = $file;
                        $filesystem->delete($file);
                        unset($arrCurrentFile[$fileKey]);
                    }
                }
                if(empty($arrCurrentFile)){
                    $filesystem->deleteDirectory($sonDir);
                    $arrDeleteDirData[] = $sonDir;
                    continue;
                }
                $boolIsEmpty = false;
            }
            $intHandNum++;
            if($boolIsEmpty === true){
                $arrDeleteDirData[] = $dir;
                $filesystem->deleteDirectory($dir);
            }
        }
        Log::channel('cron-daily')->info('DEL FILES:{'.implode("\r\n",$arrDeleteFileData)."}");
        Log::channel('cron-daily')->info('DEL DIR:{'.implode("\r\n",$arrDeleteDirData)."}");
        Log::channel('cron-daily')->info('----------------- DELETE 2322 Cache END  ------------------');
    }
}
