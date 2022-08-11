<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\BoxUtils;
use App\Http\Utils\MailUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateBoxRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:BoxRefreshToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BOX更新トークン自動更新';

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
        Log::channel('cron-daily')->debug('BOX更新トークン自動更新開始');
        try {
            // 更新トークンの有効期限は60日間
            $mst_company = DB::table('mst_company as c')->select(['c.id', 'l.box_refresh_token', 'a.email','a.given_name','a.family_name','a.state_flg'])
                ->join('mst_limit as l', 'c.id', 'l.mst_company_id')
                ->join('mst_admin as a', function ($join) {
                    $join->on('a.mst_company_id', 'c.id')
                        ->on('a.role_flg', DB::raw(1));
                })
                ->where('c.state', AppUtils::COMPANY_STATE_VALID)
                ->where('c.box_enabled', BoxUtils::BOX_ENABLED)
                ->where('l.box_enabled_automatic_storage', BoxUtils::BOX_ENABLED_AUTO_STORAGE)
                ->where(function ($query) {
                    $query->where('l.box_refresh_token_updated_date', null)
                        ->orWhere('l.box_refresh_token_updated_date', '<', Carbon::now()->modify('-50 days'));
                })->get();

            foreach ($mst_company as $item) {
                $refresh_token = BoxUtils::refreshToken($item->box_refresh_token);
                if ($refresh_token) {
                    //フレッシュトークン更新
                    DB::table('mst_limit')->where('mst_company_id', $item->id)->update([
                        'box_refresh_token' => $refresh_token,
                        'box_refresh_token_updated_date' => Carbon::now()
                    ]);
                } else {
                    Log::channel('cron-daily')->warning(sprintf('BOX更新トークン自動更新失敗しました。mst_company_id={%s}', $item->id));
                    if ($item->state_flg == AppUtils::STATE_VALID){
                        $data['admin_name'] = $item->family_name . $item->given_name;
                        //更新失敗の場合、メール送信
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $item->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['BOX_REFRESH_TOKEN_UPDATE_FAILED']['CODE'],
                            // パラメータ
                            json_encode($data, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_ADMIN,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendBoxRefreshTokenUpdateFailedMail.subject'),
                            // メールボディ
                            trans('mail.SendBoxRefreshTokenUpdateFailedMail.body', $data)
                        );
                    }
                }
            }
            Log::channel('cron-daily')->debug('BOX更新トークン自動更新終了');
        } catch (\Exception $e) {
            Log::channel('cron-daily')->debug('BOX更新トークン自動更新異常発生しました。');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}
