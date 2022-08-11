<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Utils\MailUtils;
use App\Models\User;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\RequestOptions;
use App\Http\Utils\AppUtils;

/**
 * Class SendDepartmentStampActivateSuccessMail
 * 部署捺印状態は利用可能な状態を変更します
 * PAC_5-821対応追加
 * @package App\Console\Commands
 */
class DepartmentStampActivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stampStatus:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'department stamp activate';


    private $model;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $model)
    {
        parent::__construct();
        $this->model       = $model;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            Log::channel('cron-daily')->debug("捺印の有効化バッチ実行開始");

            // 更新結果集合
            $resultStamp = [
                'success' => [],
                'failure' => []
            ];

            // 無効化捺印情報を取得
            $stamps = DB::table('mst_assign_stamp')
                ->select('mst_assign_stamp.id', 'mst_assign_stamp.mst_user_id', 'mst_user.family_name', 'mst_user.given_name', 'mst_user.mst_company_id', 'mst_admin.email', 'mst_company.contract_edition', 'mst_company.system_name', 'mst_company.company_name',
                    'mst_company.default_stamp_flg')
                ->leftJoin('mst_user', 'mst_assign_stamp.mst_user_id', 'mst_user.id')
                ->leftJoin('mst_admin', 'mst_assign_stamp.mst_admin_id', 'mst_admin.id')
                ->leftJoin('mst_company', 'mst_user.mst_company_id', 'mst_company.id')
                ->where('mst_assign_stamp.state_flg', 2)
                ->whereRaw("DATE_FORMAT(NOW(),'%Y-%m-%d') > DATE_FORMAT(mst_assign_stamp.create_at,'%Y-%m-%d')")
                ->get();

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                Log::channel('cron-daily')->error('client取得失敗しました。');
                return;
            }

            foreach ($stamps as $stamp) {
                // 更新を実行
                DB::beginTransaction();
                try {
                    DB::table('mst_assign_stamp')->where('id', $stamp->id)
                        ->update([
                            'state_flg' => 1,
                            'update_at' => Carbon::now(),
                            'update_user' => 'Shachihata',
                        ]);

                    $userdata = $this->model->find($stamp->mst_user_id);
                    $stamps = $userdata->getStamps($stamp->mst_user_id);
                    if($stamp->contract_edition == 1 || $stamp->contract_edition == 2){
                        // get users stamp count and company stamp limit
                        $arrCUTotal = Company::getCompanyStampLimitAndUserStampCount($userdata->mst_company_id);
                        
                        //デフォルト印がONの場合は氏名印３つ、日付印３つ以外の共通印や部署名入り日付印のどれかが少なくとも一つ登録されていないと有効にできない
                        if(
                            ($stamp->default_stamp_flg == 1) && (count($stamps['stampMaster']) + count($stamps['stampDepartment']) + count($stamps['stampCompany'])) == 1
                            &&
                            ($arrCUTotal['intUserStampCount'] + 1 <= $arrCUTotal['intCompanyStampLimit'])
                        ){
                            $userdata->state_flg = 1;
                            $userdata->save();
                            // API連携
                            $apiUser = [
                                "user_email" => $userdata->email,
                                "email"=> $userdata->email,
                                "contract_app"=> config('app.pac_contract_app'),
                                "app_env"=> config('app.pac_app_env'),
                                "contract_server"=> config('app.pac_contract_server'),
                                "user_auth"=> AppUtils::AUTH_FLG_USER,
                                "user_first_name"=> $userdata->given_name,
                                "user_last_name"=> $userdata->family_name,
                                "company_name"=> $stamp->company_name,
                                "company_id"=> $userdata->mst_company_id,
                                "status"=> AppUtils::convertState($userdata->state_flg),
                                "system_name"=> $stamp->system_name,
                                "update_user_email"=> 'master-pro@shachihata.co.jp',
                            ];

                            $result = $client->put("users",[
                                RequestOptions::JSON => $apiUser
                            ]);

                            if($result->getStatusCode() != 200) {
                                DB::rollBack();
                                Log::channel('cron-daily')->error($result->getBody());
                                // 有効化失敗の印鑑ID保存（後ほど申請人へ失敗メールで一括連携）
                                $resultStamp['failure'][] = $stamp->id;
                                continue;
                            }
                        }

                    }

                    $this->sendEmailForSuccess($stamp->family_name, $stamp->given_name, $stamp->mst_company_id, $stamp->email);

                    DB::commit();
                    // 失敗場合、メール連携用
                    $resultStamp['success'][] = $stamp->id;

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::channel('cron-daily')->error('捺印の有効化処理時エラーが発生');
                    Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
                    // 有効化失敗の印鑑ID保存（後ほど申請人へ失敗メールで一括連携）
                    $resultStamp['failure'][] = $stamp->id;
                }
            }

            // 失敗があるの場合
            if ($resultStamp['failure']) {
                $this->sendEmailForFailed($resultStamp);
            }

            Log::channel('cron-daily')->debug('捺印の有効化バッチ実行終了');
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('捺印の有効化バッチ実行エラーが発生');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * 捺印の有効化更新成功したときにメールを送信する
     * @param $family_name 名前：姓
     * @param $given_name 名前：名
     * @param $mst_company_id 会社Id
     * @param $email メール
     */
    protected function sendEmailForSuccess($family_name, $given_name, $mst_company_id, $email)
    {
        if ($email) {
            $param = [];
            $param['userName'] = '';
            if ($family_name || $given_name) {
                $param['userName'] = $family_name . $given_name;
            }

            // メールテーブルにインサート
            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['DEPARTMENT_STAMP_ACTIVATE_SUCCESS']['CODE'],
                // パラメータ
                json_encode($param,JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_ADMIN,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendDepartmentStampActivateSuccessMail.subject'),
                // メールボディ
                trans('mail.SendDepartmentStampActivateSuccessMail.body', $param)
            );
        }
    }

    /**
     * 捺印の有効化更新失敗したときにメールを送信する
     * @param $result 更新結果
     */
    protected function sendEmailForFailed($resultStamp)
    {
        // システム管理者メールアドレスを取得
        $adminEmail = DB::table('mst_shachihata')->select('email')->first();
        if ($adminEmail && $adminEmail->email) {
            $param = [];

            // 有効化失敗な捺印ID文字列
            $param['stampIds'] = "";
            $param['stampIdStr'] = "";
            foreach ($resultStamp['failure'] as $stampId) {
                $param['stampIds'] .= $stampId . "\t";
                $param['stampIdStr'] .= $stampId . "&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            // 成功総件数
            $param["successCount"] = count($resultStamp['success']);
            // 失敗総件数
            $param["failureCount"] = count($resultStamp['failure']);

            // メールテーブルにインサート
            MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $adminEmail->email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['DEPARTMENT_STAMP_ACTIVATE_FAILED']['CODE'],
                // パラメータ
                json_encode($param, JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_ADMIN,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendDepartmentStampActivateFailedMail.subject', ['date' => date("Y/m/d")]),
                // メールボディ
                trans('mail.SendDepartmentStampActivateFailedMail', $param)['body']
            );
        }
    }
}
