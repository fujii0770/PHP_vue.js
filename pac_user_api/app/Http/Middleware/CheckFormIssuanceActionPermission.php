<?php

namespace App\Http\Middleware;

use App\Http\Utils\TemplateUtils;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Request;

class CheckFormIssuanceActionPermission
{
    private $usingURLs = ['usingFormIssuance', 'saveInputFormIssuance', 'uploadCSVImport'];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $login_user = $request->user();

        $formId = $request->route('formId');

        $user_info = DB::table('mst_user_info')
            ->where('mst_user_id', $login_user->id)
            ->first();
        if(!$user_info) {
            abort(403);
        }

        $frm_template = DB::table('frm_template')
            ->where('id', $formId)
            ->where('mst_company_id', $login_user->mst_company_id)
            ->first();
        if (!$frm_template){
            abort(403,'処理が失敗しました。明細テンプレートを保存していません。');
        }
        $user_create = DB::table('mst_user_info')
            ->where('mst_user_id', $frm_template->mst_user_id)->first();
        if (!$user_create){
            abort(403);
        }

        $hasViewPermission = false;
        if ($login_user->id == $frm_template->mst_user_id ||
            $frm_template->frm_template_access_flg == TemplateUtils::COMPANY_ACCESS_TYPE ||
            ($frm_template->frm_template_access_flg == TemplateUtils::DEPARTMENT_ACCESS_TYPE && $user_create->mst_department_id == $user_info->mst_department_id)) {
            $hasViewPermission = true;
        }
        if ($hasViewPermission){
            $hasEditPermission = false;
            if ($login_user->id == $frm_template->mst_user_id ||
                $frm_template->frm_template_edit_flg == TemplateUtils::COMPANY_ACCESS_TYPE ||
                ($frm_template->frm_template_edit_flg == TemplateUtils::DEPARTMENT_ACCESS_TYPE
                    && $user_create->mst_department_id == $user_info->mst_department_id)) {
                $hasEditPermission = true;
            }
            $isUsingURL = false;
            foreach ($this->usingURLs as $url){
                if (Route::currentRouteName() == $url) {
                    $isUsingURL = true;
                }
            }

            if ($isUsingURL){
                if ($frm_template->disabled_at){
                    Log::debug("Cannot use the template $frm_template->id that is disabled!");
                    abort(403,'処理が失敗しました。明細テンプレート状態が更新されました。');
                }
            }else if ($request->getMethod() != 'GET' && !$hasEditPermission){
                abort(403, "処理が失敗しました。編集権限が更新されました。");
            }
            $request['hasEditPermission'] = $hasEditPermission;
            return $next($request);
        }else{
            abort(403, "処理が失敗しました。利用権限が更新されました。");
        }
    }
}
