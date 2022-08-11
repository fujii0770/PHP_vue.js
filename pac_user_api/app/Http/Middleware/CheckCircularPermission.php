<?php

namespace App\Http\Middleware;

use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use Illuminate\Support\Carbon;
use function foo\func;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckCircularPermission
{
    protected function checkCircularPermission($request, $action = 'view'){
        $circular_id = $request->route('circular_id');
        Log::debug("Checking permission for circular ".$circular_id);
        if($action == 'update') {
            $hasReqSendBack = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK)
                ->count();
            if($hasReqSendBack){
                abort(406);
            }
        }
        if ($circular_id){
            if (!Auth::check()){
                Log::debug("Guest user, check by current email and current circular");
                // Guest user, check by current email and current circular
                if (isset($request['current_circular']) && $request['current_circular'] == $circular_id){
                    return true;
                }
            }else{

                // logged user, check by table
                Log::debug("logged user, check by database");
                // 完了一覧
                if (isset($request['finishedDate']) && $request['finishedDate']) {  // 回覧完了日時、当月以外
                    $finishedDateKey = $request['finishedDate'];
                    $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
                } else {    // 完了一覧以外
                    $finishedDate = '';
                }

                try {
                    $workingUsers = DB::table("circular_user$finishedDate")->where('circular_id', $circular_id)
                        ->where('email', Auth::user()->email)
                        ->where('edition_flg', config('app.edition_flg'))
                        ->where('env_flg', config('app.server_env'))
                        ->where('server_flg', config('app.server_flg'))
                        ->get();
                } catch (\Exception $ex) {
                    Log::info($request);
                    Log::info($request->path());
                    Log::info($request->ip());
                    Log::info($request->server());
                    throw $ex;
                }

                if(isset(Auth::user()->account_name)){
                    $objFindAudit = DB::table("mst_audit")->where("email", Auth::user()->email)
                        ->where("state_flg", 1)->first();
                    if ($action == 'view' && empty($workingUsers->count()) && !empty($objFindAudit)) {
                        return true;
                    }
                }

                foreach ($workingUsers as $workingUser){
                    if (in_array($workingUser->circular_status,[CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                                                CircularUserUtils::READ_STATUS,
                                                                CircularUserUtils::SEND_BACK_STATUS,
                                                                CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                                                                CircularUserUtils::REVIEWING_STATUS,
                                                                CircularUserUtils::NOT_NOTIFY_STATUS])){
                        Log::debug("This is working user");
                        // This is working user
                        return true;
                    }
                    if($action == 'view') {
                        return true;
                    }

                }

                $authorCircular = DB::table("circular$finishedDate")->where('id', $circular_id)
                    ->where('mst_user_id', Auth::id())
                    ->first();
                if ($authorCircular){
                    Log::debug("This is author user, checking permission for action ".$action);
                    $boolSkipFlg = !empty($request->input('skipCurrentHandler'));
                    if($boolSkipFlg){
                        return true;
                    }
                    switch ($action){
                        case 'update':
                            return ($authorCircular->circular_status == CircularUtils::SAVING_STATUS
                                || $authorCircular->circular_status == CircularUtils::CIRCULAR_COMPLETED_STATUS
                                || $authorCircular->circular_status == CircularUtils::RETRACTION_STATUS);
                        case 'view':
                            return true;
                    }
                }

                $viewingCircular = DB::table('viewing_user')->where('circular_id', $circular_id)
                    ->where('mst_user_id', Auth::id())
                    ->first();
                if ($viewingCircular){
                    Log::debug("This is view user, checking permission for action ".$action);
                    switch ($action){
                        case 'view':
                            $request['current_viewing_user'] = $viewingCircular;
                            return true;
                    }
                }
                $request['current_viewing_user'] = null;

                if($action == 'pullback') {
                    $parent_send_order = $request['parent_send_order'];
                    $child_send_order = $request['child_send_order'];
                    return $this->checkPullbackPermission($circular_id,$parent_send_order,$child_send_order);
                }
                if($action == 'request-sendback') {
                    $parent_send_order = $request['parent_send_order'];
                    $child_send_order = $request['child_send_order'];
                    return $this->checkRequestSendbackPermission($circular_id,$parent_send_order,$child_send_order);
                }
                if($action == 'approval-sendback') {
                    return $this->checkApprovalSendbackPermission($circular_id);
                }
            }
        }else{
            if($action == 'view' && isset($request['longTermFlg']) && $request['longTermFlg']) {
                if(isset($request['lid']) && !empty($request['lid'])){
                    $user = \Auth::user();
                    $longDocData = DB::table('long_term_document')
                        ->where('id', '=', $request['lid'])
                        ->where('mst_company_id', '=', $user->mst_company_id)
                        ->where('user_id', '=', $user->id)
                        ->first();
                    if(!empty($longDocData) && $longDocData->upload_status == 1){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function checkPullbackPermission($circular_id,$parent_send_order,$child_send_order) {
        $login_user = Auth::user();

        $circular_users = DB::table('circular_user')
            ->where('circular_id', $circular_id)
            ->orderBy('parent_send_order')
            ->orderBy('child_send_order')
            ->get();

        if(!$circular_users->some(function ($value) use($login_user){return $value->email == $login_user->email;})) {
            return false;
        }

        // PAC_5-2131課題キーと件名をクリップボードにコピーします。Shiftキーを押しながらクリックすると課題キーのみがコピーされます。
        //ゲストユーザーに回覧されている時は引き戻しができるようにしたい
        $arrCountCompanyID = [];
        $collectionCircularUsers = $circular_users->sortBy('id')->all();
        foreach($collectionCircularUsers as $iV){
            if(empty($iV->mst_company_id)){
                $arrCountCompanyID['guesser'] = isset($arrCountCompanyID['guesser']) ? $arrCountCompanyID['guesser'] + 1 : 1;
            }else{
                // Solve the problem of the same company ID caused by inconsistent environment
                $arrCountCompanyID[sprintf("%s%s%s_%s",$iV->env_flg,$iV->edition_flg,$iV->server_flg,$iV->mst_company_id)] = true;
            }
        }
        if(isset($arrCountCompanyID['guesser']) && (count($arrCountCompanyID) > 2 || $arrCountCompanyID['guesser'] > 1)){
            return false;
        }
        // PAC_5-2709 ゲストユーザー宛に送信した回覧が引き戻せない
        if(count($arrCountCompanyID) == 2 && isset($arrCountCompanyID['guesser']) && $arrCountCompanyID['guesser'] == 1){
            return true;
        }

//        $check_edition = $circular_users->some(function($value) {
//            return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null;
//        });

        //if($check_edition) return false;


        $login_circular_user = $circular_users->first(function ($value)  use ($login_user, $parent_send_order, $child_send_order){
            return $value->email == $login_user->email && $value->parent_send_order == $parent_send_order && $value->child_send_order == $child_send_order;
        });
        if(!$login_circular_user) return false;


        return $circular_users->some(function($value) use($login_circular_user) {
            return $value->parent_send_order == $login_circular_user->parent_send_order && in_array($value->circular_status, [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS]);
        });

    }

    private function checkRequestSendbackPermission($circular_id,$parent_send_order,$child_send_order) {
        $login_user = Auth::user();

        $circular_users = DB::table('circular_user')
            ->where('circular_id', $circular_id)
            ->orderBy('parent_send_order')
            ->orderBy('child_send_order')
            ->get();

        // cannot make request send back if there is any current edition, external user or the circular is in request send back
        $check_edition = $circular_users->some(function($value) {
            return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null || $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;
        });

        if($check_edition) {
            abort(406);
        }

        // check login user: exist, first user of company and approved
        $login_circular_user = $circular_users->first(function ($value)  use ($login_user, $parent_send_order, $child_send_order){
            return $value->email == $login_user->email && $value->parent_send_order == $parent_send_order && $value->child_send_order == $child_send_order;
        });
        if(!$login_circular_user) return false;
        if($login_circular_user->parent_send_order == 0 && $login_circular_user->child_send_order > 0) return false;
        if($login_circular_user->parent_send_order > 0 && $login_circular_user->child_send_order > 1) return false;

        if(!in_array($login_circular_user->circular_status, [CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::APPROVED_WITH_STAMP_STATUS])) return false;

        // check if the working user is after the login user
        $current_circular_user = $circular_users->first(function($value) {
            return in_array($value->circular_status, [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
        });
        if(!$current_circular_user) {
            abort(406);
        }

        return $current_circular_user->parent_send_order > $login_circular_user->parent_send_order;

    }

    private function checkApprovalSendbackPermission($circular_id) {
        $login_user = Auth::user();

        $circular_users = DB::table('circular_user')
            ->where('circular_id', $circular_id)
            ->orderBy('parent_send_order')
            ->orderBy('child_send_order')
            ->get();

        // cannot make request send back if there is any current edition, external user or the circular is in request send back
        $check_edition = $circular_users->some(function($value) {
            return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null;
        });

        if($check_edition) return false;

        // check login user: exist
        $login_circular_user = $circular_users->first(function ($value)  use ($login_user){
            return $value->email == $login_user->email && $value->parent_send_order > 0 && $value->child_send_order == 1
                && in_array($value->circular_status, [CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                                            CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                && $value->env_flg == config('app.server_env') && $value->server_flg == config('app.server_flg');
        });
        if(!$login_circular_user) return false;

        // check if the request sendback user is after the login user
        $requestSendbackUser = $circular_users->first(function ($value) {
            return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;
        });
        if(!$requestSendbackUser || ($requestSendbackUser->parent_send_order >= $login_circular_user->parent_send_order)) {
            return false;
        }

        // check if the working user is before the login user
        $current_circular_user = $circular_users->first(function($value) {
            return in_array($value->circular_status, [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::END_OF_REQUEST_SEND_BACK]);
        });

        return $current_circular_user->parent_send_order >= $login_circular_user->parent_send_order;

    }
}
