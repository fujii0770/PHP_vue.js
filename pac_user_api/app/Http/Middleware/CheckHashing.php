<?php

namespace App\Http\Middleware;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\EnvApiUtils;
use Closure;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class CheckHashing
{
    const ATTRIBUTES_KEY_USER = 'CheckHashing:hashUser';

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
        $hash = $request->bearerToken();
        $array = [
            'ZTScmkHZR52Z1tmMqp6MoumdPROofFNyzVoG2ZJ+v*I5mkGSoIu8uC9gEw9GCmTR',
            'Y9Lfb5lwpVmI4982N79PJJz4Paefjlku3OsKg8Jc6dtAcdtxhtV*oyvAfm3ZCbdt'
        ];
        if (in_array($hash,$array)){
            abort(404,'回覧文書が存在しません。');
        }
        if ($hash) {
            $decryptedHashing = AppUtils::decrypt($hash,true);
            $decryptedHashing = explode('#', $decryptedHashing);
            if (count($decryptedHashing) > 3){
                $email = mb_strtolower($decryptedHashing[0]); //PAC_5-2467大文字から小文字に転換
                $circular_id = $decryptedHashing[1];
                $edition_flg = $decryptedHashing[2];
                $env_flg = $decryptedHashing[3];
                $server_flg = count($decryptedHashing) > 4 ? $decryptedHashing[4] : 0;
                $finishedDateKey = '';
                $finishedDate = '';

                $circular =  DB::table("circular")
                    ->where('id', $circular_id)
                    ->where('circular_status', '!=', CircularUtils::DELETE_STATUS)
                    ->select('id', 'edition_flg', 'env_flg', 'server_flg')
                    ->first();

                if (!$circular) {
                    // 回覧完了日時テーブル取得
                    $finished_month = DB::table("circular_finished_month")
                        ->where('circular_id', $circular_id)
                        ->first();
                    if ($finished_month) {
                        $finishedDate = $finished_month->month;
                    }
                    $circular =  DB::table("circular$finishedDate")
                        ->where('id', $circular_id)
                        ->where('circular_status', '!=', CircularUtils::DELETE_STATUS)
                        ->select('id', 'edition_flg', 'env_flg', 'server_flg')
                        ->first();

                    for ($i = 0; $i < 12; $i++) {
                        if ($finishedDate === (Carbon::now()->addMonthsNoOverflow(-$i)->format('Ym'))) {
                            $finishedDateKey = $i;
                            break;
                        }
                    }
                    if (!$circular) {
                        $deletedCircular =  DB::table("circular$finishedDate")
                            ->where('id', $circular_id)
                            ->where('circular_status', CircularUtils::DELETE_STATUS)
                            ->first();
                        if (!$deletedCircular) {
                            $deletedCircular =  DB::table("circular")
                                ->where('id', $circular_id)
                                ->where('circular_status', CircularUtils::DELETE_STATUS)
                                ->first();
                        }
                        if ($deletedCircular) {
                            abort(404,'文書が削除されました。');
                        }
                    }
                }

                // 共通
                $foundUser = [
                    'current_email' => $email,
                    'current_circular' => $circular_id,
                    'current_edition_flg' => $edition_flg,
                    'current_env_flg' => $env_flg,
                    'current_server_flg' => $server_flg,
                    'usingHash' => true,
                    'finishedDate' => $finishedDateKey
                ];

                if ($circular){
                    $circularUser = DB::table("circular_user$finishedDate")
                        ->where('circular_id', $circular_id)
                        ->where('email', $email)
                        ->where('edition_flg', $edition_flg)
                        ->where('env_flg', $env_flg)
                        ->where('server_flg', $server_flg)
                        ->where('del_flg', CircularUserUtils::NOT_DELETE)
                        ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS])
                        ->orderByDesc('parent_send_order')
                        ->orderByDesc('child_send_order')
                        ->first();
                    if (!$circularUser){
                        $circularUser = DB::table("circular_user$finishedDate")
                            ->where('circular_id', $circular_id)
                            ->where('email', $email)
                            ->where('edition_flg', $edition_flg)
                            ->where('env_flg', $env_flg)
                            ->where('server_flg', $server_flg)
                            ->where('del_flg', CircularUserUtils::NOT_DELETE)
                            ->orderByDesc('parent_send_order')
                            ->orderByDesc('child_send_order')
                            ->first();
                    }
                    if ($circularUser){
                        if (($circular->edition_flg != config('app.edition_flg') || $circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))
                            && $circularUser->origin_circular_url){
                            abort(206, $circularUser->origin_circular_url);
                            return;
                        }

                        $user = null;
                        if (config('app.edition_flg') == $edition_flg && config('app.server_env') == $env_flg && config('app.server_flg') == $server_flg){
                            $user = DB::table('mst_user')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
                            if ($user){
                                $viewingUser = DB::table('viewing_user')
                                    ->join('mst_user', 'mst_user.id', '=', 'viewing_user.mst_user_id')
                                    ->where('circular_id', $circular_id)
                                    ->where('mst_user.email', $email)
                                    ->where('viewing_user.del_flg', CircularUserUtils::NOT_DELETE)
                                    ->first();

                                $foundUser['current_viewing_user'] = $viewingUser;
                            }
                        }

                        if(!$user || !$user->id) {
                            $user = new \stdClass();
                            $user->id = 0;
                            $user->email = $email;
                            $user->mst_company_id = null;
                        }

                        $foundUser = array_merge($foundUser, [
                            'current_circular_user' => $circularUser,
                            'current_name' => $circularUser->name,
                            'is_external' => ($circularUser->mst_company_id === null),
                            'user' => $user
                        ]);

                        $request->attributes->set(Self::ATTRIBUTES_KEY_USER, $foundUser);
                        $request->merge($foundUser);

                        return $next($request);
                    }

                    if (config('app.edition_flg') == $edition_flg && config('app.server_env') == $env_flg && config('app.server_flg') == $server_flg){
                        $viewingUser = DB::table('viewing_user')
                            ->join('mst_user', 'mst_user.id', '=', 'viewing_user.mst_user_id')
                            ->where('circular_id', $circular_id)
                            ->where('mst_user.email', $email)
                            ->where('viewing_user.del_flg', CircularUserUtils::NOT_DELETE)
                            ->first();

                        if ($viewingUser){
                            $foundUser = array_merge($foundUser, [
                                'current_circular_user' => null,
                                'current_viewing_user' => $viewingUser,
                                'current_name' => implode(' ', [$viewingUser->family_name, $viewingUser->given_name]),
                                'is_external' => ($viewingUser->mst_company_id === null),
                                'user' => $viewingUser
                            ]);

                            $request->attributes->set(Self::ATTRIBUTES_KEY_USER, $foundUser);
                            $request->merge($foundUser);

                            return $next($request);
                        }
                    }else if (config('app.edition_flg') == $edition_flg && (config('app.server_env') != $env_flg || config('app.server_flg') != $server_flg)){
                        Log::debug('Checking viewing user from other env');
                        $envClient = EnvApiUtils::getAuthorizeClient($env_flg,$server_flg);
                        if (!$envClient){
                            //TODO message
                            throw new \Exception('Cannot connect to Env Api');
                        }
                        $response = $envClient->get("getViewingUser",[
                            RequestOptions::JSON =>[ 'circular_id' => $circular_id, 'email' => $email]
                        ]);
                        if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $result = json_decode((string) $response->getBody());
                            $viewingUser = $result->data;

                            $foundUser = array_merge($foundUser, [
                                'current_viewing_user' => $viewingUser,
                                'current_name' => $viewingUser->family_name.' '. $viewingUser->given_name,
                                'is_external' => false,
                                'user' => $viewingUser
                            ]);

                            $request->attributes->set(Self::ATTRIBUTES_KEY_USER, $foundUser);
                            $request->merge($foundUser);

                            return $next($request);
                        }else if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_NOT_FOUND){
                            Log::warning('getViewingUser response: '.$response->getBody());
                        }
                    }
                }
            }
        }
        abort(404,'回覧文書が存在しません。');
    }
}
