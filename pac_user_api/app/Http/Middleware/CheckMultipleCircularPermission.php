<?php

namespace App\Http\Middleware;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Closure;

class CheckMultipleCircularPermission
{
    public function handle($request, Closure $next, $guard = null){
        $action = $request->route('action');
        $cids = $request->get('cids',[]);

        // 完了一覧
        if (isset($request['finishedDate']) && $request['finishedDate']) {    // 回覧完了日時、当月以外
            $finishedDateKey = $request['finishedDate'];
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        } else {    // 完了一覧以外
            $finishedDate = '';
        }
        Log::debug("Checking permission for multiple circular ".implode(',', $cids));
        if (!empty($cids)){
            if (Auth::check()){
                // logged user, check by table
                Log::debug("logged user, check by database");
                $workingCirculars = DB::table("circular_user$finishedDate")->select('circular_id')->whereIn('circular_id', $cids)
                    ->where('email', Auth::user()->email)
                    ->where('edition_flg', config('app.edition_flg'))
                    ->where('env_flg', config('app.server_env'))
                    ->where('server_flg', config('app.server_flg'))
                    ->get();
                $workingCircularIds = [];
                foreach ($workingCirculars as $workingCircular){
                    $workingCircularIds[] = $workingCircular->circular_id;
                }
                $cids = array_diff($cids, $workingCircularIds);
                if (empty($cids)){
                    return $next($request);
                }else{
                    $authorCirculars = DB::table("circular$finishedDate")->select('id')->whereIn('id', $cids)
                        ->where('mst_user_id', Auth::id())
                        ->get();
                    $authorCircularIds = [];
                    foreach ($authorCirculars as $authorCircular){
                        $authorCircularIds[] = $authorCircular->id;
                    }
                    $cids = array_diff($cids, $authorCircularIds);
                    if (empty($cids)){
                        Log::debug("This is author user, checking permission for action ".$action);
                        switch ($action){
                            case 'deleteSaved':
                                return $next($request);
                            case 'deleteSent':
                                return $next($request);
                            case 'deleteCompleted':
                                return $next($request);
                            case 'downloadFile':
                                return $next($request);
                            case 'reNotification':
                                return $next($request);
                            case 'storeMultipleCircular':
                                return $next($request);
                        }
                    }

                    $viewingCirculars = DB::table('viewing_user')->select('circular_id')->whereIn('circular_id', $cids)
                        ->where('mst_user_id', Auth::id())
                        ->get();
                    $viewingCircularIds = [];
                    foreach ($viewingCirculars as $viewingCircular){
                        $viewingCircularIds[] = $viewingCircular->circular_id;
                    }
                    $cids = array_diff($cids, $viewingCircularIds);
                    if (empty($cids)){
                        Log::debug("This is viewing user, checking permission for action ".$action);
                        switch ($action){
                            case 'deleteCompleted':
                                return $next($request);
                            case 'downloadFile':
                                return $next($request);
                            case 'storeMultipleCircular':
                                return $next($request);
                        }
                    }
                }
            }
        }

        abort(403);
    }
}
