<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Utils\UserApiUtils;
use GuzzleHttp\RequestOptions;
use Response;
use App\Http\Utils\AppUtils;
use App\Http\Requests\API\CreateNoticeManagementAPIRequest;
use Illuminate\Support\Facades\Storage;

class AddTimestampAutomatic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:timestampAutomatic';

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
        Log::channel('cron-daily')->debug('Run to AddTimestampAutomatic : startTime ' . Carbon::now()->format('H:i:s'));
        try{
            $longTermDocument = DB::table('long_term_document')
                ->select('mst_company_id','circular_id','id')
                ->where('timestamp_automatic_flg', AppUtils::TIMESTAMP_AUTOMATIC_ON)
                ->whereRaw('DATE_SUB(now(),INTERVAL 1 YEAR ) >= date(add_timestamp_automatic_date)')
                ->get();

            Log::channel('cron-daily')->debug("AddTimestampAutomatic: get longTermDocument needs add timestamp " . $longTermDocument);

            $circularByCompany = [];

            $stampApiClient = UserApiUtils::getStampApiClient();
            $arrCompanyId = [];

            foreach($longTermDocument as $item){
                if(!in_array($item->mst_company_id, $arrCompanyId)){
                    array_push($arrCompanyId,$item->mst_company_id);
                }
                $circularByCompany[$item->mst_company_id][] = [
                        'id' => $item->id,
                        'circular_id' => $item->circular_id,
                ];
            }

            if(count($arrCompanyId)){
                $arrCompany = DB::table('mst_company')
                            ->select('id','certificate_flg','certificate_destination','certificate_pwd')
                            ->whereIn('id', $arrCompanyId)
                            ->get()
                            ->keyBy('id')
                            ->toArray();
            }


            foreach($circularByCompany as $key=>$value){
                Log::channel('cron-daily')->debug("Start AddTimestampAutomatic for company $key");

                $company = $arrCompany[$key];

                foreach ($value as $long_term_document){

                    if(Carbon::now()->format('H:m') > config('app.add_timestamp_end_time')){
                        break;
                    }
                    try{
                        $path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                            . '/' . config('app.server_flg').'/'.$key.'/'.$long_term_document['circular_id'];

                        if ( Storage::disk('s3')->exists($path)){
                            Log::channel('cron-daily')->debug("Start AddTimestampAutomatic on s3 for circular ".$long_term_document['circular_id']." and company $key");

                            $file_names = Storage::disk('s3')->files($path);
                            $files = [];
                            $fileNames = [];
                            foreach($file_names as $index=>$file_name){
                                Log::channel('cron-daily')->debug("AddTimestampAutomatic get file_name : $file_name");

                                $fileNameArr = explode("/",$file_name);
                                $fileName = array_pop($fileNameArr);

                                $file_content = Storage::disk('s3')->get($file_name);

                                $file = chunk_split(base64_encode($file_content));

                                $fileNames[$index] = $fileName;

                                $files[]= [
                                    'circular_document_id' => $index,
                                    'pdf_data' =>  $file,
                                    'append_pdf_data' => null,
                                    'file_name' => $fileName,
                                    'usingTas'=>1
                                ];
                            }
                            if(count($files)){
                                Log::channel('cron-daily')->debug("AddTimestampAutomatic: call api signatureAndImpress add timestamp for circular ".$long_term_document['circular_id']." and company $key");

                                $result = $stampApiClient->post("signatureAndImpress", [
                                    RequestOptions::JSON => [
                                        'signature' => 1,
                                        'data' => $files,
                                        'signatureKeyFile' => $company->certificate_flg ? $company->certificate_destination : null,
                                        'signatureKeyPassword' => $company->certificate_flg ? $company->certificate_pwd : null,
                                    ]
                                ]);

                                $resData = json_decode((string)$result->getBody());

                                if ($result->getStatusCode() == 200) {
                                    Log::channel('cron-daily')->debug("AddTimestampAutomatic: call api signatureAndImpress add timestamp for circular ".$long_term_document['circular_id']." and company $key success");
                                    if ($resData && $resData->success && $resData->data) {
                                        foreach($resData->data as $data){
                                            Log::channel('cron-daily')->debug("AddTimestampAutomatic: update circular to S3 for circular ".$long_term_document['circular_id']." and company $key");

                                            Storage::disk('s3')->put($path.'/'.$fileNames[$data->circular_document_id], base64_decode($data->pdf_data), 'pub');

                                            Log::channel('cron-daily')->debug("AddTimestampAutomatic on S3 for circular ".$long_term_document['circular_id']." and company $key success");
                                        }
                                        DB::table('long_term_document')
                                        ->where('id',$long_term_document['id'])
                                        ->update([
                                            'update_at' => Carbon::now(),
                                            'add_timestamp_automatic_date' =>  Carbon::now()
                                        ]);
                                    }else{
                                        Log::error('AddTimestampAutomatic : Log signatureAndImpress: '. $result->getBody());
                                    }
                                }else{
                                    Log::error('AddTimestampAutomatic : Log signatureAndImpress: '. $result->getBody());
                                }
                            }
                        }else{
                            Log::channel('cron-daily')->warning("path $path does not exist on s3");
                        }
                    }catch (\Exception $ex) {
                        DB::rollBack();
                        Log::error($ex->getMessage().$ex->getTraceAsString());
                    }
                }      
            }
        }catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to AddTimestampAutomatic failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
        Log::channel('cron-daily')->debug("AddTimestampAutomatic finished - endTime : " . Carbon::now()->format('H:i:s'));
    }
}