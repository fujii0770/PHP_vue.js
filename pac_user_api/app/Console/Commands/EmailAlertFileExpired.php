<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Repositories\CompanyRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;
use App\Http\Utils\AppUtils;

class EmailAlertFileExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:alertFileExpired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * Create a new command instance.
     *
     * @param CompanyRepository $companyRepository
    */
    public function __construct(CompanyRepository $companyRepository)
    {
        parent::__construct();
        $this->companyRepository = $companyRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron-daily')->debug('Run to alertFileExpired');

        try{
            $circularIds = DB::table('circular_document as D')
                                ->join('circular as CI', 'D.circular_id', 'CI.id')
                                ->join('mst_constraints as C', 'D.create_company_id', 'C.mst_company_id')
                               ->where('D.origin_env_flg', config('app.server_env'))
                                ->where('D.origin_edition_flg', config('app.edition_flg'))
                                ->where('D.origin_server_flg', config('app.server_flg'))
                                ->whereIn('CI.circular_status', [CircularUtils::CIRCULATING_STATUS,
                                                                CircularUtils::SEND_BACK_STATUS])
                                ->whereRaw('DATE_ADD(DATE_SUB(NOW(), INTERVAL (C.max_keep_days) DAY ),INTERVAL C.delete_informed_days_ago DAY ) > D.create_at')
                                ->pluck('D.circular_id');

            if(count($circularIds)){
                $circularUsers = DB::table('circular_user')
                    ->whereIn('circular_id', $circularIds)
                    ->where('parent_send_order', 0)
                    ->where('child_send_order', 0)
                    ->orderBy('circular_id')
                    ->get();

                $mapSenders = [];
                $mapSenderCompanies = [];
                $mapSameEnvCompanies = [];   
                foreach($circularUsers as $circularUser){
                    if(!key_exists($circularUser->email, $mapSenders)){
                        $mapSenders[$circularUser->email] = [];
                    }
                    $mapSenders[$circularUser->email][] = $circularUser;
                    $mapSenderCompanies[$circularUser->email] = $circularUser->mst_company_id;
                    $mapSameEnvCompanies[$circularUser->mst_company_id] = null;
                }
                
                $mapSameEnvCompanies = $this->companyRepository->getSameEnvCompanies($mapSameEnvCompanies);     

                $documents = DB::table('circular_document')
                    ->whereIn('circular_id', $circularIds)
                    ->where(function ($query){
                        $query->where('parent_send_order', 0);
                        $query->orWhere('origin_document_id', 0);
                    })
                    ->orderBy('circular_id')
                    ->get();

                $mapDocuments = [];
                foreach($documents as $document){
                    if(!key_exists($document->circular_id, $mapDocuments)){
                        $mapDocuments[$document->circular_id] = [];
                    }
                    $mapDocuments[$document->circular_id][] = $document;
                }

                foreach($mapSenders as $email => $circularUsers){
                    $circularDocuments = [];
                    $circularDocumentstext = '';
                    foreach ($circularUsers as $circularUser){
                        if(isset($mapDocuments[$circularUser->circular_id])){
                            $document = $mapDocuments[$circularUser->circular_id];
                            $circularDocuments[] = ['files' => $document, 'subject' => $circularUser->title];

                            foreach ($document as $file){
                                $circularDocumentstext = $circularDocumentstext . '・送信日時: ';
                                $circularDocumentstext = $circularDocumentstext . $file->create_at . ', ';
                                if(trim($circularUser->title) != ''){
                                    $circularDocumentstext = $circularDocumentstext . '件名: ' . $circularUser->title . ', ';
                                }
                                $circularDocumentstext = $circularDocumentstext . 'ファイル名: ' . $file->file_name;
                                $circularDocumentstext = $circularDocumentstext . '\r\n';
                            }
                        }
                    }

                    Log::channel('cron-daily')->debug("AlertFileExpired to email $email");
                    $data = [];
                    $data['circularDocuments'] = $circularDocuments;
                    // check to use SAML Login URL or not
                    if (isset($mapSenderCompanies[$email]) && isset($mapSameEnvCompanies[$mapSenderCompanies[$email]])){
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'), config('app.server_flg'), CircularUserUtils::NEW_EDITION, $mapSameEnvCompanies[$mapSenderCompanies[$email]]);
                    }else{
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'), config('app.server_flg'), CircularUserUtils::NEW_EDITION, null);;
                    }
                    
                    $json_arr_text = ["circularDocuments" => $circularDocumentstext];

                    //ファイル保存期間の通知
                    MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                        $email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['FILE_EXPIRED_ALERT']['CODE'],
                        // パラメータ
                        json_encode($data,JSON_UNESCAPED_UNICODE),
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendMailAlertFileExpired.subject'),
                        // メールボディ
                        trans('mail.SendMailAlertFileExpired.body', $json_arr_text)
                    );
                }
            }
        } catch(\Exception $e){
            Log::channel('cron-daily')->error('Run to alertFileExpired failed');
            Log::channel('cron-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
        Log::channel('cron-daily')->debug('Run to alertFileExpired finished');
        
    }
}
