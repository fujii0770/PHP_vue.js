<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use App\Models\Company;
use App\Models\PasswordPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Mail\SendMailInitPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;
use App\Jobs\SendEmail;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $data = [];

    public $meta = [];

    public function __construct()
    {

    }

    public function assign($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function addMeta($name, $value)
    {
        $this->meta[$name] = $value;
    }

    public function addStyleSheet($name, $content = "", $isLink = true)
    {
        if (!isset($this->meta['css'])) $this->meta['css'] = [];
        $style = "";
        if ($isLink) {
            $style = "<link rel=\"stylesheet\" href=\"$content\" />";
        } else {
            $style = "<style type=\"text/css\">$content</style>";
        }
        $this->meta['css'][$name] = $style;
    }

    public function addScript($name, $content = "", $isLink = true)
    {
        if (!isset($this->meta['js'])) $this->meta['js'] = [];
        $script = "";
        if ($isLink) {
            $script = "<script rel=\"text/javascript\" src=\"$content\"></script>";
        } else {
            $script = "<script type=\"text/javascript\">$content</script>";
        }
        $this->meta['js'][$name] = $script;
    }

    public function setMetaTitle($value)
    {
        $this->meta['title'] = $value;
    }

    public function setMetaDesc($value)
    {
        $this->meta['desc'] = $value;
    }

    public function render($view)
    {
        $this->data['meta'] = $this->meta;
        $request = new Request();
        return view($view, $this->data);
    }

    /**
     * パスワードを設定通知メール
     * @param $accountType
     * @param $email
     * @param null $link_root
     * @return bool
     * @throws \Exception
     */
    public function sendMailResetPassword($accountType, $email, $link_root = null, $notification_email = null, $company_name = null,int $mst_company_id = 0)
    {
        $email = trim($email);
        if ($email) {
            DB::beginTransaction();
            $token = Hash::make($email . time());

            $table = $accountType == AppUtils::ACCOUNT_TYPE_ADMIN ? 'admin_password_resets' :
                ($accountType == AppUtils::ACCOUNT_TYPE_USER || $accountType == AppUtils::ACCOUNT_TYPE_OPTION ||
                $accountType == AppUtils::ACCOUNT_TYPE_SIMPLE_USER || $accountType == AppUtils::AUTH_FLG_RECEIVE ? 'user_password_resets' : 'audit_password_resets');

            DB::table($table)->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => new \DateTime(),
            ]);

            $user = Auth::user();
            if ($user){
                $policy = PasswordPolicy::where('mst_company_id', $user->mst_company_id)->first();
            }else{
                $policy = PasswordPolicy::where('mst_company_id', $mst_company_id)->first();
            }

            $data = [];
            $data['email'] = $email;
            $data['token'] = Hash::make($email . $token);
            $data['link_root'] = $link_root;
            if ($policy && $policy->password_mail_validity_days === 0) {
                $data['time_out'] = '無期限';
            } else {
                // PAC_5-1970 パスワードメールの有効期限を変更する Start
                $data['time_out'] = date("Y/m/d H:i", time() + 86400 * ($policy->password_mail_validity_days ?? 7));
                // PAC_5-1970 End
            }

            // ユーザーに初期パスワード付与 メール通知
            $pass = $this->getPassword();

            Log::info('Reset password:' . $email);
            Log::info('- token: ' . $token);
            Log::info('- hash: ' . $data['token']);

            if ($link_root) {
                $company = Company::where('id', $user ? $user->mst_company_id : $mst_company_id)->first();
                if ($company->login_type == AppUtils::LOGIN_TYPE_SSO){
                    $data['login_url'] = config('app.url_app_user').'/'.rtrim(config('app.saml_url_prefix'), "/").'/'.$company->url_domain_id;
                }else{
                    $data['login_url'] = config('app.url_app_user');
                }
                if ($accountType == AppUtils::ACCOUNT_TYPE_AUDIT) {
                    $data['password'] = $pass[0];
                    $data['account_type'] = AppUtils::ACCOUNT_TYPE_AUDIT;
                } else if ($accountType == AppUtils::ACCOUNT_TYPE_SIMPLE_USER){
                    $data['password'] = $pass[0];
                    $data['account_type'] = AppUtils::ACCOUNT_TYPE_SIMPLE_USER;
                } else {
                    $data['password'] = $pass[0];
                    $data['account_type'] = AppUtils::ACCOUNT_TYPE_USER;
                }
            } else {
                $data['password'] = $pass[0];
                $data['account_type'] = AppUtils::ACCOUNT_TYPE_ADMIN;
            }
            if ($data['account_type'] == 'user' || $data['account_type'] == AppUtils::ACCOUNT_TYPE_OPTION || $accountType == AppUtils::AUTH_FLG_RECEIVE) {
                $code = MailUtils::MAIL_DICTIONARY['USER_PASSWORD_SET_REQUEST']['CODE'];
                $temlb = 'mail.email_reset_link_user.body';
                $subject = trans('mail.prefix.user') . trans('mail.email_reset_link_user.subject');
                $mail_type = AppUtils::MAIL_TYPE_USER;
                $data['user_id'] = $accountType == AppUtils::ACCOUNT_TYPE_OPTION || $accountType == AppUtils::AUTH_FLG_RECEIVE ? $email : '';
                $data['company_name'] = $accountType == AppUtils::ACCOUNT_TYPE_OPTION || $accountType == AppUtils::AUTH_FLG_RECEIVE ? $company_name : '';
            } else if ($data['account_type'] == 'simple_user' ) {
                $code = MailUtils::MAIL_DICTIONARY['SIMPLE_USER_PASSWORD_SET_REQUEST']['CODE'];
                $temlb = 'mail.email_reset_link_simple_user.body';
                $subject = trans('mail.prefix.user') . trans('mail.email_reset_link_simple_user.subject');
                $mail_type = AppUtils::MAIL_TYPE_USER;
            } else if ($data['account_type'] == 'audit') {
                $code = MailUtils::MAIL_DICTIONARY['AUDIT_PASSWORD_SET_REQUEST']['CODE'];
                $temlb = 'mail.email_reset_link_audit.body';
                $subject = trans('mail.prefix.user') . trans('mail.email_reset_link_audit.subject');
                $mail_type = AppUtils::MAIL_TYPE_AUDIT;
            } else {
                $subject = trans('mail.prefix.admin') . trans('mail.email_reset_link_admin.subject');
                $code = MailUtils::MAIL_DICTIONARY['ADMIN_PASSWORD_SET_REQUEST']['CODE'];
                $temlb = 'mail.email_reset_link_admin.body';
                $mail_type = AppUtils::MAIL_TYPE_ADMIN;
            }

            $resume_id = MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                $email,
                // メールテンプレート
                $code,
                // パラメータ
                json_encode($data, JSON_UNESCAPED_UNICODE),
                // タイプ
                $mail_type,
                // 件名
                config('app.mail_environment_prefix') . $subject,
                // メールボディ
                trans($temlb, $data), AppUtils::MAIL_STATE_WAIT, AppUtils::MAIL_SEND_DEFAULT_TIMES,
                // オプション利用者の受信メールアドレス
                $notification_email
            );

            if(!$resume_id){
                DB::rollBack();
                Log::error('send reset password email failed:'.$email);
                return false;
            }
            $mst_table = $accountType == AppUtils::ACCOUNT_TYPE_ADMIN ? 'mst_admin' :
                ($accountType == AppUtils::ACCOUNT_TYPE_USER || $accountType == AppUtils::ACCOUNT_TYPE_OPTION ||
                $accountType == AppUtils::ACCOUNT_TYPE_SIMPLE_USER || $accountType == AppUtils::AUTH_FLG_RECEIVE ? 'mst_user' : 'mst_audit');
            if(isset($data['password']) && $pass[1]){
                DB::table($mst_table)
                    ->where('email',strtolower($email))
                    ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                    ->update([
                        'password' => $pass[1],
                        'password_change_date' => Carbon::now(),
                    ]);
            }
            DB::commit();
            return true;
        }

        return false;
    }

    public function raiseNotice($message)
    {
        $this->setMessage($message, 'notice');
    }

    public function raiseWarning($message)
    {
        $this->setMessage($message, 'warning');
    }

    public function raiseInfo($message)
    {
        $this->setMessage($message, 'info');
    }

    public function raiseSuccess($message)
    {
        $this->setMessage($message, 'success');
    }

    public function raiseDanger($message)
    {
        $this->setMessage($message, 'danger');
    }

    protected function setMessage($mess, $status)
    {
        $message = session('raise-message', []);
        $checkSum = md5(json_encode($mess) . $status);
        $message[$checkSum] = [$mess, $status];
        session(['raise-message' => $message]);
    }

    /**
     * 初期パスワード取得
     * @return mixed
     */
    private function getPassword()
    {
        // トライアルユーザーに初期パスワード付与
        $randStr = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz123456789');
        $pass[0] = substr($randStr, 0, 8);
        $pass[1] = Hash::make($pass[0]);
        return $pass;
    }
}
