<?php

namespace App\Jobs;

use App\Http\Utils\EnvApiUtils;
use App\Models\UploadData;
use App\Http\Utils\AppUtils;
use App\Http\Utils\UserApiUtils;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Matrix\Exception;

class UpdateUploadData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $user;
    private $upload_id;
    private $unique_name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($upload_id,$unique_name,$user)
    {
        $this->upload_id=$upload_id;
        $this->user=$user;
        $this->unique_name=$unique_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
            . '/' . config('app.server_flg').'/'.$this->user->mst_company_id.'/'.'upload_'.$this->upload_id;
        $type='s3';
        if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
            $type='k5';
        }
        if(!Storage::disk($type)->exists($path.'/'.$this->unique_name)){
            throw new Exception('アップロード処理に失敗しました。');
        }
        $file=Storage::disk($type)->get($path.'/'.$this->unique_name);
        $stampApiClient = UserApiUtils::getStampApiClient();
        $company = DB::table('mst_company')->where('id', $this->user->mst_company_id)->first();
        $time=DB::table('long_term_document')->where('upload_id',$this->upload_id)->value('add_timestamp_automatic_date');
        $username = $this->user->family_name.' '. $this->user->given_name;
        $signatureReason = $this->getSignatureReason($username, $this->user->email);
        $result = $stampApiClient->post("signatureAndImpress", [
            RequestOptions::JSON => [
                'signature' => 1,
                'data' => [
                    [
                        'pdf_data' => base64_encode($file),
                        'append_pdf_data' => null,
                        'stamps' => [],
                        'texts' => [],
                        'usingTas'=>0,
                        'usingDTS'=>true
                    ]
                ],
                'signatureReason' => $signatureReason,
                'signatureKeyFile' => $company->certificate_flg?$company->certificate_destination:null,
                'signatureKeyPassword' => $company->certificate_flg?$company->certificate_pwd:null,
                'documentTimestampSignatureReason' => $this->getDTSSignatureReason(),
                'timestampSignatureReason'=>$time
            ]
        ]);
        $resData = json_decode((string)$result->getBody());
        if ($result->getStatusCode() == 200) {
            if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS){
                Storage::disk('s3')->put($path.'/'.$this->unique_name, base64_decode($resData->data[0]->pdf_data), 'pub');
            }else if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                Storage::disk('k5')->put($path.'/'.$this->unique_name, base64_decode($resData->data[0]->pdf_data));
            }
            UploadData::where('id',$this->upload_id)->update(['upload_data'=>AppUtils::encrypt($resData->data[0]->pdf_data)]);
        } else {
            Log::debug("Update upload_data_circular response body: " . $result->getBody());
            throw new Exception('アップロード処理に失敗しました。');
        }
    }
    private function getSignatureReason($username, $email){
        return sprintf("%s（%s）により%sに署名されています。", $username, $email, Carbon::now()->format("Y-m-d H:i:s.u"));
    }

    private function getDTSSignatureReason(){
        return sprintf("MIND Timestamp Service DiaStamp A2E01により%sに署名されています", Carbon::now()->format("Y-m-d H:i:s.u"));
    }
}
