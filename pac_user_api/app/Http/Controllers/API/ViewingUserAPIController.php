<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\ViewingUserAPIRequest;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\EnvApiUtils;
use App\Models\ViewingUser;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Response;

/**
 * Class ViewingUserAPIController
 * @package App\Http\Controllers\API
 */

class ViewingUserAPIController extends AppBaseController
{
    public function __construct(ViewingUser $viewingUser)
    {
        $this->modal = $viewingUser;
    }

    /**
     * Update the specified  ViewingUser in storage.
     * PUT/PATCH /viewing-user/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, ViewingUserAPIRequest $request)
    {
        $user = $request->user();

        $input = $request->all();

        $viewingUser =  $this->modal->where('mst_user_id', $user->id)->find($id);

        if (empty($viewingUser)) {
            return $this->sendError('メモの更新処理に失敗しました。');
        }
        if ($viewingUser->mst_user_id != $user->id){
            return $this->sendError('メモの更新処理に失敗しました。');
        }
        $viewingUser->update_user = $user->email;
        try{
            $viewingUser->fill($input);
            $viewingUser->save();
            return $this->sendResponse($viewingUser->toArray(), 'メモの更新処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('メモの更新処理に失敗しました。');
        }
    }

    public function updateMemo($circular_id, Request $request)
    {
        $user = $request->user();

        $env_flg = config('app.server_env');
        $server_flg = config('app.server_flg');
        if (isset($request['usingHash']) && $request['usingHash']){
            $user = $request['user'];

            $env_flg = $request['current_env_flg'];
            $server_flg = $request['current_server_flg'];
            $email = $request['current_email'];
            $mst_user_id = $user->mst_user_id;
        }else{
            $email = $user->email;
            $mst_user_id = $user->id;
        }
        $input = $request->all();
        if (!$request->get('memo')) $input['memo'] = '';

        if ($env_flg == config('app.server_env') && $server_flg == config('app.server_flg')){
            $viewingUser =  $this->modal->where('circular_id',$circular_id)->where('mst_user_id',$mst_user_id)->first();

            if (empty($viewingUser)) {
                return $this->sendError('メモの更新処理に失敗しました。');
            }
            if ($viewingUser->circular_id != $circular_id){
                return $this->sendError('メモの更新処理に失敗しました。');
            }
            if ($viewingUser->mst_user_id != $user->id){
                return $this->sendError('メモの更新処理に失敗しました。');
            }
            $viewingUser->update_user = $user->email;
            try{
                $viewingUser->fill($input);
                $viewingUser->save();
                return $this->sendResponse($viewingUser->toArray(), 'メモの更新処理に成功しました。');
            }catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError('メモの更新処理に失敗しました。');
            }
        }else{
            Log::debug('Update viewing user from other env');
            $envClient = EnvApiUtils::getAuthorizeClient($env_flg,$server_flg);
            if (!$envClient){
                //TODO message
                throw new \Exception('Cannot connect to Env Api');
            }
            $input['circular_id'] = $circular_id;
            $input['email'] = $email;
            $input['finishedDate'] = $request['finishedDate'];

            $response = $envClient->get("updateViewingUser",[
                RequestOptions::JSON =>$input
            ]);
            if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                return $this->sendSuccess('メモの更新処理に成功しました。');
            }else {
                Log::warning('updateViewingUser response: '.$response->getBody());
                return $this->sendError('メモの更新処理に失敗しました。');
            }
        }
    }

    public function storeTransfer(ViewingUserAPIRequest $request)
    {
        $viewingUser =  new $this->modal;
        $info = $request->only('circular_id',
            'parent_send_order',
            'mst_company_id',
            'email',
            'del_flg',
            'memo',
            'origin_circular_url',
            'create_at',
            'create_user',
            'update_user');
        $info['update_at'] = Carbon::now();
        DB::beginTransaction();
        try{
            $circular = DB::table('circular')->where('origin_circular_id', $info['circular_id'])->select('id')->first();
            $email = $info['email'];
            unset($info['email']);
            if ($circular){
                $user = DB::table('mst_user')->where('email', $email)->where('mst_company_id', $info['mst_company_id'])->first();
                if ($user){
                    $info['mst_user_id'] = $user->id;
                    $viewingUser->fill($info);
                    $viewingUser->memo = $viewingUser->memo?:'';
                    $viewingUser->circular_id = $circular->id;
                    $viewingUser->save();
                }
            }
            DB::commit();
            return $this->sendResponse($viewingUser,'');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTransfer(Request $request)
    {
        $originCircularId = $request->get('circular_id');
        $email = $request->get('email');

        if (!$originCircularId || !$email){
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_BAD_REQUEST], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        try{
            $circular = DB::table('circular')->where('origin_circular_id', $originCircularId)->select('id')->first();
            if ($circular){
                $viewingUser = DB::table('viewing_user')
                                        ->join('mst_user', 'mst_user.id', '=', 'viewing_user.mst_user_id')
                                        ->where('mst_user.email', $email)
                                        ->where('viewing_user.circular_id', $circular->id)
                                        ->where('viewing_user.del_flg', CircularUserUtils::NOT_DELETE)
                                        ->select(['viewing_user.*', 'mst_user.family_name', 'mst_user.given_name'])
                                        ->first();
                if ($viewingUser){
                    return $this->sendResponse($viewingUser,'');
                }else{
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
                }
            }else{
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateTransfer(ViewingUserAPIRequest $request)
    {
        $info = $request->only('circular_id', 'email', 'memo');
        try{
            $finishedDateKey = $request['finishedDate'];
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }
            $circular = DB::table("circular$finishedDate")->where('origin_circular_id', $info['circular_id'])->select('id')->first();
            if ($circular){
                $viewingUser = DB::table('viewing_user')
                    ->join('mst_user', 'mst_user.id', '=', 'viewing_user.mst_user_id')
                    ->where('mst_user.email', $info['email'])
                    ->where('viewing_user.circular_id', $circular->id)
                    ->select(['viewing_user.*', 'mst_user.family_name', 'mst_user.given_name'])
                    ->first();

                if ($viewingUser){
                    DB::table('viewing_user')->where('id', $viewingUser->id)->update(['memo' => $info['memo']?:'', 'update_at' => Carbon::now()]);

                    return $this->sendSuccess('');
                }else{
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
                }
            }else{
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_BAD_REQUEST], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /*PAC_5-2331 S*/
    public function getAllTransfer(Request $request)
    {
        $originCircularId = $request->get('circular_id');
        $email = $request->get('email');
        if (!$originCircularId || !$email){
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_BAD_REQUEST], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        try{
            $circular = DB::table('circular')->where('origin_circular_id', $originCircularId)->select('id')->first();
            if ($circular && isset($circular->id)){
                $curr_user = DB::table('mst_user')->where('email' , $email)->select('mst_company_id')->first();
                if(!$curr_user || !isset($curr_user->mst_company_id)){
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
                }
                $viewingUser = DB::table('viewing_user')
                    ->join('mst_user', 'mst_user.id', '=', 'viewing_user.mst_user_id')
                    ->where('viewing_user.mst_company_id', $curr_user->mst_company_id)
                    ->where('viewing_user.circular_id', $circular->id)
                    ->where('viewing_user.del_flg', CircularUserUtils::NOT_DELETE)
                    ->select(['mst_user.email', 'mst_user.family_name', 'mst_user.given_name'])
                    ->get();
                if ($viewingUser){
                    return $this->sendResponse($viewingUser,'');
                }else{
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
                }
            }else{
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_NOT_FOUND], \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /*PAC_5-2331 E*/
}
