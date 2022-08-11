<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\AppUtils;

class CheckCompanyContractChat
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $companyUser = DB::table('mst_company')
                ->join('mst_chat', 'mst_chat.mst_company_id', 'mst_company.id')
                ->where('mst_company.id', $user->mst_company_id)
                ->where('mst_company.chat_flg', AppUtils::MST_COMPANY_CHAT_FLG_USING)
                ->select( 'mst_company.chat_trial_flg',
                    'mst_chat.contract_end_date', 'mst_chat.trial_end_date')
                ->first();
            if ($companyUser) {
                $presentTime = Carbon::now()->format('Y-m-d H:i:s');
                if ($companyUser->chat_trial_flg == AppUtils::MST_COMPANY_CHAT_TRIAL_FLG_USE) {
                    if (isset($companyUser->trial_end_date) &&
                        $companyUser->trial_end_date >= $presentTime) {
                        return $next($request);
                    }
                } else {
                    if (isset($companyUser->contract_end_date) &&
                        $companyUser->contract_end_date >= $presentTime) {
                        return $next($request);
                    }
                }
            }
        }
        return response()->json(['status' => false, 'message' => [__('message.false.company_talk_contract_invalid')]]);
    }
}
