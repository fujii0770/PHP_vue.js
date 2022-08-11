<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUserAPIRequest;
use App\Http\Requests\API\UpdateUserAPIRequest;
use App\Http\Requests\API\UpdateUserInfoAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\StampUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\UserApiUtils;
use App\Models\User;
use App\Models\UserInfo;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Response;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class UserAPIController extends AppBaseController
{
    private $table_policy = "password_policy";

    /** @var  UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the User.
     * GET|HEAD /users
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if(!$user || !$user->id) {
            $user = $request['user'];
        }
        $company_id = $user->mst_company_id;
        $filter             = $request->get('filter', null);
        $where              = [];
        $whereContact       = [];
        $where_arg          = [];
        $where_argContact   = [];
        $where[] = '1=1';
        $whereContact[] = '1=1';
        // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応
        if($filter || $filter === '0'){
            $where[] = '(email like ? OR family_name like ? OR given_name like ? )';
            $where_arg[] = "%$filter%";
            $where_arg[] = "%$filter%";
            $where_arg[] = "%$filter%";

            $whereContact[]     = '(email like ? OR name like ?)';
            $where_argContact[] = "%$filter%";
            $where_argContact[] = "%$filter%";
        }

        $users = DB::table('mst_user')
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->where('mst_company_id', $company_id)
            ->where('state_flg',AppUtils::STATE_VALID)
            ->whereIn('option_flg',[AppUtils::USER_NORMAL,AppUtils::USER_RECEIVE])
            ->select('given_name','family_name','email')
            ->get()->toArray();
        $userEmails = [];

        foreach ($users as $dbUser){
            $userEmails[] = $dbUser->email;
        }

        $contacts = DB::table('address')
            ->where('type', ContactUtils::TYPE_PERSONAL)
            ->whereRaw(implode(" AND ", $whereContact), $where_argContact)
            ->where('mst_user_id', $user->id)
            ->select('name','email');
        if (count($userEmails)){
            $contacts->whereNotIn('email', $userEmails);
        }
        $contacts = $contacts->get()->toArray();

        $contacts = array_merge($users, $contacts);
        if(!empty($contacts)){
            $contacts = array_values(array_column($contacts,null,'email'));
        }

        return $this->sendResponse($contacts, 'ユーザー一覧の取得処理に成功しました。');
    }

    /**
     * Store a newly created User in storage.
     * POST /users
     *
     * @param CreateUserAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUserAPIRequest $request)
    {
        $input = $request->all();

        $user = $this->userRepository->create($input);

        return $this->sendResponse($user->toArray(), 'ユーザー一覧の作成処理に成功しました。');
    }

    /**
     * Display the specified User.
     * GET|HEAD /users/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var User $user */
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            return $this->sendError('User not found');
        }

        return $this->sendResponse($user->toArray(), 'ユーザー一覧の取得処理に成功しました。');
    }

    /**
     * Update the specified User in storage.
     * PUT/PATCH /users/{id}
     *
     * @param int $id
     * @param UpdateUserAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateUserAPIRequest $request)
    {
        $input = $request->all();

        /** @var User $user */
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            return $this->sendError('User not found');
        }

        $user = $this->userRepository->update($input, $id);

        return $this->sendResponse($user->toArray(), 'ユーザー一覧の更新処理に成功しました。');
    }

    /**
     * Remove the specified User from storage.
     * DELETE /users/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var User $user */
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            return $this->sendError('ユーザーが見つかりません。');
        }

        $user->delete();

        return $this->sendSuccess('ユーザー一覧の削除処理に成功しました。');
    }

    public function getStampsByHash(Request $request) {
        try {
            $currentCircularUser = $request['current_circular_user'];
            if(!$currentCircularUser || $currentCircularUser->mst_company_id === null) {
              //  return $this->sendError('権限がありません。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
                return $this->sendResponse([], 'ユーザー印面の取得処理に成功しました。');
            }
            $arrStamp = [];
            if (!$request['is_external']){
                if ($currentCircularUser->edition_flg == config('app.edition_flg')){
                    if ($currentCircularUser->env_flg == config('app.server_env') && $currentCircularUser->server_flg == config('app.server_flg')){
                        $user = DB::table('mst_user')->where('email', $currentCircularUser->email)
                            ->where('state_flg', AppUtils::STATE_VALID)
                            ->first();
                        if(isset($request['date']) && $request['date']) {
                            $date = new \DateTime($request['date']);
                        }else{
                            $date = new \DateTime();
                        }

                        // 有効ユーザ判定追加
                        if (!$user){
                            return $this->sendError(__('message.false.invalidUser'), \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
                        }
                        $arrStamp = $this->getUserStamps($user, $date);
                    }else{
                        $client = EnvApiUtils::getAuthorizeClient($currentCircularUser->env_flg,$currentCircularUser->server_flg);

                        if (!$client){
                            //TODO message
                            Log::error('Cannot connect to Env Api for get user stamp');
                        }
                        $response = $client->get("getStamps",[
                            RequestOptions::JSON => ['email' => $currentCircularUser->email, 'stamp_date' => isset($request['date'])?$request['date']:null, 'unit' => 'mm']
                        ]);
                        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                            Log::error('Cannot get user stamp');
                            Log::error($response->getBody());
                            //  throw new \Exception($response->getBody());
                        }else{
                            $result = json_decode($response->getBody());
                            $arrStamp = $result->stamps;
                        }
                    }
                }else{
                    $circular = DB::table('circular')->select('id', 'edition_flg', 'env_flg', 'server_flg')->where('id', $request['current_circular'])->first();
                    $result = EnvApiUtils::getEditionAuthorizeClient($currentCircularUser->env_flg, $circular, $currentCircularUser);

                    if (!$result['status']){
                        //TODO message
                        Log::error('Cannot connect to Edition Api: '.$result['message']);
                    }else{
                        $editionClient = $result['client'];

                        $response = $editionClient->post("getStamps",[
                            RequestOptions::JSON => ['apikey' =>$result['token'], 'email' => $currentCircularUser->email, 'stamp_date' => isset($request['date'])?$request['date']:new Date()]
                        ]);
                        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                            Log::error('Cannot get Stamp from Edition API');
                            Log::error($response->getBody());
                        }else{
                            $result = json_decode((string) $response->getBody());
                            if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED){
                                $stamps = json_decode($response->getBody());
                                $stamps = $stamps->result;
                                foreach ($stamps as $item){
                                    $arrStamp[] = ['sid' => 0, 'width' => $item->image->width*AppUtils::PX_TO_MICROMET, 'height'=>$item->image->height*AppUtils::PX_TO_MICROMET, 'serial'=> $item->unique,'stamp_image'=> $item->image->binary, 'time_stamp_permission' => 0];
                                }
                            }else{
                                Log::error('Cannot get Stamp from Edition API');
                                Log::error($response->getBody());
                            }
                        }
                    }
                }
            }

            return $this->sendResponse($arrStamp, 'ユーザー印面の取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getStamps(Request $request) {
        try {
            $email = $request['email'];
            $unit = isset($request['unit'])?$request['unit']:'px';

            $unitConvert = ($unit == 'px')?AppUtils::MICROMET_TO_PX:1;

            $user = DB::table('mst_user')->where('email', $email)
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->first();

            // 印面リスト初期デフォルト選択
            $userInfo = DB::table('mst_user_info')->where('mst_user_id', $user->id)
                ->first();

            if(!$user) {
                return $this->sendError('権限がありません。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            if(isset($request['stamp_date']) && $request['stamp_date']) {
                $date = new \DateTime($request['stamp_date']);
            }else{
                $date = new \DateTime();
            }

            $arrStamp = $this->getUserStamps($user, $date);

            $base64Stamps = [];
            foreach ($arrStamp as $stamp){
                $base64Stamp = [];

                $base64Stamp['sid'] = $stamp->id;
                $base64Stamp['stamp_image'] = $stamp->stamp_image;
                if (isset($stamp->serial)){
                    $base64Stamp['serial'] = $stamp->serial;
                }else{
                    $base64Stamp['serial'] = '';
                }
                $base64Stamp['width'] = $stamp->width*$unitConvert;
                $base64Stamp['height'] = $stamp->height*$unitConvert;
                $base64Stamp['stamp_division'] = $stamp->stamp_division;
                $base64Stamp['time_stamp_permission'] = $stamp->time_stamp_permission;
                $base64Stamps[] = $base64Stamp;
            }

            return ['status' =>\Illuminate\Http\Response::HTTP_OK, 'message' =>'ユーザー印面の取得処理に成功しました。', 'stamps' => $base64Stamps, 'defaultStampId' => $userInfo->last_stamp_id];

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return ['status' =>\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR, 'message' =>$ex->getMessage(), 'stamps' => []];
        }
    }

	function updateDefaultStampByOperation(Request $request)
	{
		$email = $request['email'];
		$stamp_id = $request['stamp_id'];

		try {
			$user = DB::table('mst_user')->where('email', $email)->where('state_flg', AppUtils::STATE_VALID)->first();
			if ($user){
				DB::table('mst_user_info')->where('mst_user_id', $user->id)->update(['last_stamp_id' => $stamp_id]);
			}
		} catch (\Exception $ex) {
			Log::error($ex->getMessage() . $ex->getTraceAsString());
			return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
		}
		return $this->sendSuccess('デフォルト印面の更新処理に成功しました。');
	}

    private function getUserStamps($user, $date){

        $dstamp_style = DB::table('mst_company')->where('id', $user->mst_company_id)
            ->select('dstamp_style')->pluck('dstamp_style')->first();
        if(!$dstamp_style)  {
            $dstamp_style = 'y.m.d';
        }
        $date = \App\Http\Utils\DateJPUtils::convert($date, $dstamp_style);

        $user_stamps = DB::table('mst_assign_stamp')
            ->select(
                'mst_assign_stamp.id',
                'mst_assign_stamp.display_no',
                'mst_assign_stamp.stamp_flg',
                'mst_assign_stamp.time_stamp_permission',
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_name  WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_name  WHEN '.StampUtils::DEPART_STAMP.' THEN CONCAT(department_stamp.face_up1,department_stamp.face_up2,department_stamp.face_down1,department_stamp.face_down2) ELSE mst_stamp.stamp_name END stamp_name'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_division WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_date_flg WHEN '.StampUtils::NORMAL_STAMP.' THEN mst_stamp.stamp_division ELSE null END  stamp_division'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.font WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.font ELSE  mst_stamp.font END font'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_image WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_image WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.stamp_image ELSE mst_stamp.stamp_image END stamp_image'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.width  WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.width WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.width ELSE mst_stamp.width END width'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.height WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.height WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.height ELSE mst_stamp.height END height'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_x WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_x WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_x ELSE mst_stamp.date_x END date_x'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_y WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_y WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_y ELSE mst_stamp.date_y END date_y'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_width WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_width WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_width ELSE mst_stamp.date_width END date_width'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_height WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_height WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_height ELSE mst_stamp.date_height END date_height'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.create_at WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_company_stamp_convenient.create_at WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.create_at ELSE mst_stamp.create_at END create_at'),
                DB::raw('CASE mst_assign_stamp.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.serial WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_company_stamp_convenient.serial WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.serial ELSE mst_stamp.serial END serial'),

                // 日付の色を取得する PAC_5-107 BEGIN
                //DB::raw('IF(mst_assign_stamp.stamp_flg = '.StampUtils::DEPART_STAMP.',department_stamp.color, '.StampUtils::COLOR_RED.') as color')
                // PAC_5-107 END

                // 日付の色が共通印にも設定できるようになったので一緒に取得 PAC_5-1325
                DB::raw('IF(mst_assign_stamp.stamp_flg = '.StampUtils::COMMON_STAMP.',mst_company_stamp.date_color, IF(mst_assign_stamp.stamp_flg = '.StampUtils::DEPART_STAMP.',department_stamp.color, IF(mst_assign_stamp.stamp_flg = '.StampUtils::CONVENIENT_STAMP. ',mst_stamp_convenient.date_color, ' .StampUtils::COLOR_RED.'))) as color')
            )
            ->leftJoin('mst_company_stamp', function(JoinClause $join) {
                $join->on('mst_company_stamp.id', '=', 'mst_assign_stamp.stamp_id');
                $join->where('mst_assign_stamp.stamp_flg', StampUtils::COMMON_STAMP);

            })
            ->leftJoin('mst_company_stamp_convenient', function(JoinClause $join) {
                $join->on('mst_company_stamp_convenient.id', '=', 'mst_assign_stamp.stamp_id');
                $join->where('mst_assign_stamp.stamp_flg', StampUtils::CONVENIENT_STAMP);

            })
            ->leftJoin('mst_stamp_convenient', function(JoinClause $join) {
                $join->on('mst_stamp_convenient.id', '=', 'mst_company_stamp_convenient.mst_stamp_convenient_id');
            })
            ->leftJoin('mst_stamp', function(JoinClause $join) {
                $join->on('mst_stamp.id', '=', 'mst_assign_stamp.stamp_id');
                $join->where('mst_assign_stamp.stamp_flg', StampUtils::NORMAL_STAMP);
            })
            ->leftJoin('department_stamp', function(JoinClause $join) {
                $join->on('department_stamp.id', '=', 'mst_assign_stamp.stamp_id');
                $join->where('mst_assign_stamp.stamp_flg', StampUtils::DEPART_STAMP);
            })
            // TODO department stamp state
            ->where('mst_assign_stamp.mst_user_id', $user->id)
            ->where('mst_assign_stamp.state_flg', AppUtils::STATE_VALID)
            ->where(DB::raw('IFNULL(mst_company_stamp.del_flg, 0)'),'!=', 1)
            ->orderBy('mst_assign_stamp.display_no')
            ->get();

        $arrStamp = $user_stamps->toArray();

        foreach ($arrStamp as $key => $stamp){
            $arrStamp[$key]->stamp_image = StampUtils::processStampImage($stamp, $date);
        }
        return $arrStamp;
    }

    public function myStamps(Request $request) {
        try {
            $user = $request->user();
            if($request['usingHash']) {
                $user = $request['user'];
            }
            //$user = $request->user();
            $date = new \DateTime($request['date']);
            if(!$user) {
                return $this->sendError('権限がありません。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }

            if(!$request['date']) {
                $date = new \DateTime();
            }

            $arrStamp = $this->getUserStamps($user, $date);

            return $this->sendResponse($arrStamp, 'ユーザー印面の取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateAssignStampsOrder(Request $request) {
        try {
            $user = $request->user();
            if($request['usingHash']) {
                $user = $request['user'];
            }
            $stamps = $request['stamps'];
            DB::beginTransaction();

            foreach ($stamps as $stamp) {
                DB::table('mst_assign_stamp')->where('id', $stamp['db_id'])->update([
                    'display_no'=> $stamp['display_no'],
                    'update_user'=> $user->email,
                    'update_at' => Carbon::now()
                ]);
            }

            DB::commit();
            return $this->sendSuccess('利用可能印面の表示順更新に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('利用可能印面の表示順更新に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 本環境部署取得
     * @param Request $request
     * @return mixed
     */
    public function getUsersDepartments(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || !$user->id) {
                $user = $request['user'];
            }
            $company_id = $user->mst_company_id;

            if (!$company_id) {
                return $this->sendResponse([], 'ユーザー部署の取得処理に成功しました。');
            }

            $filter = $request->get('filter', null);
            $option = $request->get('option','0');
            if ($option == '1'){
                $kind = [AppUtils::USER_NORMAL,AppUtils::USER_OPTION,AppUtils::USER_RECEIVE];
                $listDepartmentTree = $this->getMyDepartments($company_id, $filter, $kind);
            }else{
                $listDepartmentTree = $this->getMyDepartments($company_id, $filter);
            }
            
            return $this->sendResponse($listDepartmentTree, 'ユーザー部署の取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * hash値より、他の環境部署取得
     * @param Request $request
     * @return mixed
     */
    public function getDepartmentsByHash(Request $request)
    {
        try {
            $currentCircularUser = $request['current_circular_user'];
            if (!$currentCircularUser || $currentCircularUser->mst_company_id === null) {
                return $this->sendResponse([], 'ユーザー部署の取得処理に成功しました。');
            }

            $filter = $request->get('filter', null);
            $listDepartmentTree = [];
            if ($currentCircularUser->edition_flg == config('app.edition_flg')) {
                //新エディション
                if ($currentCircularUser->env_flg == config('app.server_env') && $currentCircularUser->server_flg == config('app.server_flg')) {
                    //本環境の場合、DBから取得
                    $listDepartmentTree = $this->getMyDepartments($currentCircularUser->mst_company_id, $filter);
                } else {
                    //他の環境の場合、APIから取得
                    $client = EnvApiUtils::getAuthorizeClient($currentCircularUser->env_flg, $currentCircularUser->server_flg);

                    if (!$client) {
                        Log::error('他の環境からユーザー部署取得時に、API接続失敗しました。');
                        return $this->sendResponse([], 'ユーザー部署の取得処理に成功しました。');
                    }
                    $response = $client->get("getCurrentDepartments", [
                        RequestOptions::JSON => ['email' => $currentCircularUser->email, 'filter' => $filter]
                    ]);
                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                        Log::error('部署取得失敗しました。');
                        Log::error($response->getBody());
                    } else {
                        $result = json_decode($response->getBody());
                        $listDepartmentTree = $result->data;
                    }
                }
            } else {
                //todo 現行の場合、APIから取得
            }
            return $this->sendResponse($listDepartmentTree, 'ユーザー部署の取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 環境を跨いだ場合、現在の環境の部署取得
     * @param Request $request
     * @return mixed
     */
    public function getCurrentDepartments(Request $request)
    {
        try {
            $email = $request['email'];
            $filter = $request['filter'];
            $user = DB::table('mst_user')->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
            $listDepartmentTree = $this->getMyDepartments($user ? $user->mst_company_id : 0, $filter);

            return $this->sendResponse($listDepartmentTree, 'ユーザー部署の取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 本環境部署取得共通メソッド
     * @param $company_id
     * @param $filter
     * @return mixed
     */
    private function getMyDepartments(
        $company_id, 
        $filter,
        $kind = [AppUtils::USER_NORMAL,AppUtils::USER_RECEIVE])
    {
        // 部署なし利用者有無確認（三つ全部指定なし）
        $listUserNoGroup = DB::table('mst_user')
            ->Join('mst_user_info', function ($query) {
                $query->on('mst_user.id', 'mst_user_info.mst_user_id')
                    ->whereNull('mst_user_info.mst_department_id')
                    ->whereNull('mst_user_info.mst_department_id_1')
                    ->whereNull('mst_user_info.mst_department_id_2');
            })
            ->where('mst_user.mst_company_id', $company_id)
            ->where('mst_user.state_flg', AppUtils::STATE_VALID)
            ->whereIn('mst_user.option_flg', $kind)
            ->get();

        // 部署リスト
        if (count($listUserNoGroup)) {
            // 部署なし利用者あり

            // 登録済み全部署（漢字数字並び順調整）
            $listDepartment1 = DB::table('mst_department')
                ->select('id','parent_id' , 'department_name as name' , 'department_name as sort_name')
                ->where('mst_company_id',$company_id)
                ->where('state',AppUtils::STATE_VALID)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();

            // 「グループなし」部署
            $listDepartment2 = DB::table(DB::raw('(SELECT null as id,0 as parent_id,\'グループなし\' as name) a'))
                ->get()->keyBy('id')->toArray();

            // 利用者側表示用部署（グループなしを一番後ろに）
            $listDepartment = array_merge($listDepartment1, $listDepartment2);

        }else{
            // 「グループなし」部署なし、登録済み全部署のみ（漢字数字並び順調整）
            $listDepartment = DB::table('mst_department')
                ->select('id', 'parent_id', 'department_name as name' , 'department_name as sort_name')
                ->where('mst_company_id', $company_id)
                ->where('state', AppUtils::STATE_VALID)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();
        }

        if (count($listDepartment)) {
            $where = ["mst_user.mst_company_id = " . $company_id];
            //　画面指定検索条件
            $where_arg = [];
            if ($filter) {
                $where[] = '(mst_user.email like ? OR mst_user.family_name like ? OR mst_user.given_name like ? )';
                $where_arg[] = "%$filter%";
                $where_arg[] = "%$filter%";
                $where_arg[] = "%$filter%";
            }

            // 利用者リスト
            $mstUsers = DB::table('mst_user')
                ->leftJoin('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                ->leftJoin('mst_position', 'mst_user_info.mst_position_id', '=', 'mst_position.id')
                ->select('mst_user.id'
                    , 'mst_user.email'
                    , 'mst_user.family_name'
                    , 'mst_user.given_name'
                    , 'mst_user_info.mst_department_id'
                    , 'mst_user_info.mst_department_id_1'
                    , 'mst_user_info.mst_department_id_2'
                    , 'mst_position.position_name'
                    , 'mst_user_info.phone_number'
                )
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('state_flg', AppUtils::STATE_VALID)
                ->whereIn('option_flg', $kind)
                ->orderBy('mst_user.email', 'asc')
                ->get();

            // 利用者リストを部署リストに埋め込む
            if (count($mstUsers)) {
                $arrUsers = [];
                foreach ($mstUsers as $_user) {
                    // 部署１
                    if($_user->mst_department_id){
                        $arrUsers[$_user->mst_department_id][] = $_user;
                    }
                    // 部署２（同部署異役職場合、一つしか表示しない）
                    if($_user->mst_department_id_1
                        && $_user->mst_department_id_1 != $_user->mst_department_id){
                        $arrUsers[$_user->mst_department_id_1][] = $_user;
                    }
                    // 部署３（同部署異役職場合、一つしか表示しない）
                    if($_user->mst_department_id_2
                        && $_user->mst_department_id_2 != $_user->mst_department_id
                        && $_user->mst_department_id_2 != $_user->mst_department_id_1){
                        $arrUsers[$_user->mst_department_id_2][] = $_user;
                    }
                    // グループなし（いずれの部署も指定なし）
                    if(!$_user->mst_department_id && !$_user->mst_department_id_1 && !$_user->mst_department_id_2){
                        $arrUsers[$_user->mst_department_id][] = $_user;
                    }
                }
            }

            foreach ($listDepartment as $department) {
                $department->users = isset($arrUsers[$department->id]) ? $arrUsers[$department->id] : [];
            }

            // リストを部署階層考慮追加
            $listDepartmentTree = CommonUtils::arrToTree($listDepartment);

            return $listDepartmentTree;
        }
    }

    function myInfo(Request $request){
        $user = $request->user();
        try {
            $info = DB::table('mst_user_info')->select("mst_user.shift_flg","mst_user_info.*")
                ->leftjoin("mst_user","mst_user.id","mst_user_info.mst_user_id")
                ->where('mst_user_info.mst_user_id',$user->id)
                ->first();
            $user_company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            $mst_constrains = DB::table('mst_constraints')->select('max_document_size','max_viwer_count','max_attachment_size','file_mail_size_single','file_mail_size_total','file_mail_count')
                ->where('mst_company_id', $user->mst_company_id)->first();
            $info->mst_department = '';
            $info->mst_position = '';
            if($info->mst_department_id){
                $info->mst_department = DB::table('mst_department')
                    ->where('id',$info->mst_department_id)
                    ->value('department_name');
            }
            if($info->mst_position_id){
                $info->mst_position = DB::table('mst_position')
                    ->where('id',$info->mst_position_id)
                    ->value('position_name');
            }
            $info->max_document_size = $mst_constrains->max_document_size;
            $info->max_viwer_count = $mst_constrains->max_viwer_count;
            $info->login_type = $user_company->login_type;
            // PAC_5-2359 show long_term_storage_option_flg
            $info->long_term_storage_option_flg = $user_company->long_term_storage_option_flg;
            // PAC_5-2318  add field long_term_storage_delete_flg 文書の削除
            $info->long_term_storage_delete_flg = $user_company->long_term_storage_delete_flg;
            // PAC_5-3455  add field long_term_storage_move_flg 文書の移動
            $info->long_term_storage_move_flg = $user_company->long_term_storage_move_flg;
            // PAC_5-2741 監査用アカウントで文書の表示やダウンロードを実施するとエラーになると START
            if(isset($user->account_name)){
                $objFindAudit = DB::table("mst_audit")->where("email", $user->email)
                    ->where("state_flg", 1)->first();
                $info->long_term_storage_delete_flg = !empty($objFindAudit) ? 0 : $info->long_term_storage_delete_flg;
            }
            // PAC_5-2741 監査用アカウントで文書の表示やダウンロードを実施するとエラーになると END
            $infoCheck['addressbook_only_flag'] = $user_company->addressbook_only_flag;
            $info->max_attachment_size = $mst_constrains->max_attachment_size;
            $info->file_mail_size_single = $mst_constrains->file_mail_size_single;
            $info->file_mail_size_total = $mst_constrains->file_mail_size_total;
            $info->file_mail_count = $mst_constrains->file_mail_count;
            $infoCheck['updated_notification_email_flg'] = $user_company->updated_notification_email_flg;
            $infoCheck['view_notification_email_flg'] = $user_company->view_notification_email_flg;
            $infoCheck['enable_email'] = $user_company->enable_email;
            unset($info->user_profile_data);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['info' => $info, 'infoCheck' =>  $infoCheck], 'ユーザー情報の取得処理に成功しました。');
    }
    /**
     * Get UserInfo data (base64 - image)
     * @param Request $request
     *
     * @return mixed
     */
    function getUserInfo (Request $request)
    {

        try {
            $userId = $request->user()->id;
            $userInfo = DB::table('mst_user_info')->select('user_profile_data')->where('mst_user_id', $userId)->first();
            if (isset($userInfo)) {
                return $this->sendResponse($userInfo, 'プロファイル画像を取得のが成功になった。');
            } else {
                Log::error('UserAPIController@getUserInfo: Get user_info error');
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $ex){
            Log::error('UserAPIController@getUserInfo:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);}


    }

    function updateMyInfo(Request $request){
        $user = $request->user();
        $info = $request->get('info');

        if(!isset($info['page_display_first']) || empty($info['page_display_first'])) {
            $info['page_display_first'] = "ポータル";
        }

        if(!isset($info['circular_info_first']) || empty($info['circular_info_first'])) {
            $info['circular_info_first'] = "印鑑";
        }

        $validator = Validator::make($info, UserInfo::$rules);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try{
            unset($info['id']);
            unset($info['shift_flg']);
            unset($info['mst_user_id']);
            unset($info['mst_department_id']);
            unset($info['mst_position_id']);
            unset($info['mfa_type']);
            unset($info['max_document_size']);
            unset($info['max_viwer_count']);
            unset($info['user_profile_data']);
            unset($info['max_attachment_size']);
            // PAC_5-1599 追加部署と役職 Start
            unset($info['mst_department_id_1']);
            unset($info['mst_position_id_1']);
            unset($info['mst_department_id_2']);
            unset($info['mst_position_id_2']);
            // PAC_5-1599 End
            /*PAC_5-2318 S*/
            unset($info['long_term_storage_delete_flg']);
            /*PAC_5-2318 S*/
            unset($info['login_type']);
            unset($info['long_term_storage_delete_flg']);
            unset($info['long_term_storage_option_flg']);
            unset($info['file_mail_size_single']);
            unset($info['file_mail_size_total']);
            unset($info['file_mail_count']);
            /*PAC_5-3018 S*/
            unset($info['mst_department']);
            unset($info['mst_position']);
            unset($info['long_term_storage_move_flg']);
            /*PAC_5-3018 E*/
            DB::table('mst_user_info')->where('mst_user_id',$user->id)->update($info);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendSuccess('ユーザー情報の更新処理に成功しました。');
    }

    function updateTextSetting(Request $request)
    {
        try {
            $user = $request->user();
            $edition_flg = config('app.edition_flg');
            $env_flg = config('app.server_env');
            $server_flg = config('app.server_flg');
            $info = $request->get('info');
            if ($request['usingHash']) {
                $user = $request['current_circular_user'];
                $user_id = $user->mst_user_id;
                $edition_flg = $request['current_circular_user']->edition_flg;
                $env_flg = $request['current_circular_user']->env_flg;
                $server_flg = $request['current_circular_user']->server_flg;
            }else{
                $user_id = $request->has('user_id') ? $request->get('user_id') : $user->id;
            }

            if ($edition_flg == 1) {
                if ($env_flg == config('app.server_env') && $server_flg == config('app.server_flg')) {
                    DB::table('mst_user_info')->where('mst_user_id', $user_id)->update($info);
                } else {
                    // api 呼出
                    $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
                    if (!$envClient) {
                        return response()->json(['status' => false, 'message' => ['Cannot connect to Env Api']]);
                    }
                    $response = $envClient->post("updateTextSettings", [
                        RequestOptions::JSON => [
                            'info' => $info,
                            'user_id' => $user_id
                        ]
                    ]);
                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                        Log::error('テキスト設定情報更新に失敗しました。');
                        Log::error($response->getBody());
                        throw new \Exception('ユーザーテキスト情報の更新処理に失敗しました。');
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendSuccess('ユーザーテキスト情報の更新処理に成功しました。');
    }

    function setDefaultStampId(Request $request) {
        $user = $request['current_circular_user'];
        $stamp_id = $request->get('stamp_id');
        $default_rotate_angle = $request->get('default_rotate_angle');
        $default_opacity = $request->get('default_opacity');

        try{
            if (($stamp_id || !is_null($default_rotate_angle) || !is_null($default_opacity)) && $request['current_edition_flg'] == config('app.edition_flg')){
                if($request['current_env_flg'] != config('app.server_env') || $request['current_server_flg'] != config('app.server_flg')) {
                    $client = EnvApiUtils::getAuthorizeClient($request['current_env_flg'],$request['current_server_flg'] );

                    if (!$client){
                        //TODO message
                        Log::error('Cannot connect to Env Api for default stamp');
                    }
                    $client->post("default-stamp",[
                        RequestOptions::JSON => ['stamp_id' => $stamp_id, 'default_rotate_angle' => $default_rotate_angle ,'default_opacity' => $default_opacity, 'email' => $user->email]
                    ]);
                } else {
                    if($stamp_id){
                        $updateInfo['last_stamp_id'] = $stamp_id;
                    }
                    if(!is_null($default_rotate_angle)){
                        $updateInfo['default_rotate_angle'] = $default_rotate_angle;
                    }
                    if(!is_null($default_opacity)){
                        $updateInfo['default_opacity'] = $default_opacity;
                    }

                    DB::table('mst_user_info')->where('mst_user_id', $user->mst_user_id)->update($updateInfo);
                }
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendSuccess('ユーザー情報の更新処理に成功しました。');
    }

    function storeTransferDefaultStamp(Request $request) {
        $email = $request['email'];
        $stamp_id = $request->get('stamp_id');
        $default_rotate_angle = $request->get('default_rotate_angle');
        $default_opacity = $request->get('default_opacity');

        try{
            if (($stamp_id || $default_rotate_angle || $default_opacity) && $email){
                $userInfo = DB::table('mst_user_info')
                    ->join('mst_user', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                    ->where('mst_user.email', $email)
                    ->where('mst_user.state_flg', 1)
                    ->select('mst_user_info.*')
                    ->first();

                if($userInfo) {
                    if($stamp_id){
                        $updateInfo['last_stamp_id'] = $stamp_id;
                    }
                    if($default_rotate_angle){
                        $updateInfo['default_rotate_angle'] = $default_rotate_angle;
                    }
                    if($default_opacity){
                        $updateInfo['default_opacity'] = $default_opacity;
                    }

                    DB::table('mst_user_info')->where('id', $userInfo->id)->update($updateInfo);
                }
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendSuccess('ユーザー情報の更新処理に成功しました。');
    }

    function updatePassword(Request $request){
        $user = $request->user();

        $policy = DB::table($this->table_policy)->where('mst_company_id',$user->mst_company_id)->first();

        $params = $request->all();
        $validator = Validator::make($params, [
            'password' => 'required|min:'.$policy->min_length.'|max:32|confirmed',
            'password_confirmation' => 'required|min:'.$policy->min_length.'|max:32',
        ]);

        if ($validator->fails()) {
            return $this->sendError(\implode('<br />', $validator->messages()->all()));
        }

        // check password same last time    passwordPolicy.enable_password
        if($policy->enable_password == 0){
            if(Hash::check($params['password'], $user->password)){
                return response()->json([
                    'message' => '前回と同じパスワードは使用できません。',
                    'status' => 409
                ], 409);
            }
        }
         // check use email as password    passwordPolicy.set_mail_as_password
         if($policy->set_mail_as_password == 1){
             $strTempUserName = explode('@',strtolower($user->email));
             $strTempPassword = strtolower($params['password']);
             if ($strTempPassword == strtolower($user->email) || $strTempUserName[0] == $strTempPassword) {
                return response()->json([
                    'message' => 'ユーザＩＤと同一のパスワードを禁止する',
                    'status' => 409
                ], 409);
            }
        }

            $pass_status = true;

        /*PAC_5-2848 S*/
        $message = 'パスワードは、文字と数字を含める必要があります。';
        $regex = '/^(?=.*[0-9])(?=.*[a-zA-Z])/';
        if(preg_match($regex,$params['password'])){
            for($i=0; $i<strlen($params['password']); $i++){
                if(ord($params['password'][$i]) > 126){
                    $pass_status = false;
                    break;
                }
            }
        }else $pass_status = false;
        if(!$pass_status){
            return response()->json([
                'message' =>  $message,
                'status' => 409
            ], 409);
        }
        /*PAC_5-2848 E*/
        if($policy->character_type_limit == 1){
            $message = 'パスワードポリシーに反しています。英大文字、英小文字、数字、記号の内、3種類以上入れてください。';
            $regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])|^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;\'\\\\[\]])|^(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;\'\\\\[\]])|^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;\'\\\\[\]])/';
            if(preg_match($regex,$params['password'])){
                for($i=0; $i<strlen($params['password']); $i++){
                    if(ord($params['password'][$i]) > 126){
                        $pass_status = false;
                        break;
                    }
                }
            }else $pass_status = false;
            if(!$pass_status){
                return response()->json([
                    'message' =>  $message,
                    'status' => 409
                ], 409);
            }
        }

        try{
            $user->update([
                'password' => Hash::make($params['password']),
            ]);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendSuccess('ユーザーのパスワードの更新処理に成功しました。');
    }

    /**
     * 統合へメールをチェック
     * @param Request $request
     * @return mixed
     */
    function checkEmail(Request $request)
    {
        try {
            $email  = $request->get('email', null);

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }

            $response = $client->post("users/checkEmail",[
                RequestOptions::JSON => ['email' => $email ]
            ]);

            // 結果を判断
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $result = json_decode((string) $response->getBody());
            } else {
                return $this->sendError('ユーザーが見つかりません。');
            }

            // データがない時
            if (!$result->data) {
                return $this->sendError('ユーザーが見つかりません。');
            }

            return $this->sendResponse($result->data, 'Retrieved successfully');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDepartment(Request $request)
    {
        $user = $request->user();

        if(!$user || !$user->id) {
            $user = $request['user'];
        }
        $company_id = $user->mst_company_id;

        if(!$company_id) {
            return $this->sendResponse([], 'ユーザー部署の取得処理に成功しました。');
        }

        $listDepartment = DB::table('mst_department')
            ->select('id','parent_id' , 'department_name as name')
            ->where('mst_company_id',$company_id)
            ->get()->keyBy('id');

        $listDepartmentTree = AppUtils::arrToTree($listDepartment);

        $listDepartmentTree = AppUtils::treeToArr($listDepartmentTree);

        $mapItems = [];
        foreach($listDepartmentTree as $item){
            $mapItems[$item['id']] = $item;
        }

        $departmentNames = [];

        foreach($listDepartmentTree as $key => $item){
            if(is_array($item)){
                if(isset($item['level']) AND $item['level'] > 1){
                    $parentId = $item['parent_id'];
                    $text['name'] = $item['text'];
                    while ($parentId){
                        if (key_exists($parentId, $mapItems)){
                            $parentItem = $mapItems[$parentId];
                            $text['name'] = ($parentItem['text'].' ＞ '.$text['name']);
                            $parentId = $parentItem['parent_id'];
                        }else{
                            break;
                        }
                    }
                }else {
                    $text['name'] = $item['text'];
                }
                $text['id'] = $item['id'];
            }else{
                // $val =  $key;
                $text['name'] =  $item;
                $text['id'] =  $key;
            }

            array_push($departmentNames,$text);

        }
        return $this->sendResponse($departmentNames,'get department true');

    }

    public function updateOperationNotice(Request $request){
        $login_user = $request->user();
        if(isset($request['operationNotice']) && $request['operationNotice']){
            DB::table('mst_user_info')
            ->where('mst_user_id',$login_user->id)
            ->update(['operation_notice_flg' => CircularUserUtils::DEFAULT_OPERATION_NOTICE_FLG]);
        }
        return $this->sendSuccess('通知メールを送信しました。');
    }

    /**
     * メールテンプレート取得
     * @param Request $request
     * @return mixed
     */
    public function getTemplates(Request $request){
        try {
            $mst_user_id = $request->user()->id;

            $template1 = DB::table('mst_user_info')->where('mst_user_id', $request->user()->id)->value('template1');
            $template2 = DB::table('mst_user_info')->where('mst_user_id', $request->user()->id)->value('template2');
            $template3 = DB::table('mst_user_info')->where('mst_user_id', $request->user()->id)->value('template3');

            return $this->sendResponse([
                'template1' => $template1,
                'template2' => $template2,
                'template3' => $template3,
            ], 'テンプレートの取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * メールテンプレート更新
     * @param Request $request
     * @return Response
     */
    public function updateTemplates(Request $request){
        try {
            $mst_user_id = $request->user()->id;

            if (!$request->has('template1') && !$request->has('template2') && !$request->has('template3')){
                return $this->sendError('いずれかのテンプレートの値を設定してください', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            // バリデーション
            $this->validate($request, [
                'template1' => [
                    'max:256',
                ],
                'template2' => [
                    'max:256',
                ],
                'template3' => [
                    'max:256',
                ],
            ]);

            $templates = array();
            if ($request->has('template1')) {
                $templates['template1'] = $request->input('template1');
            }
            if ($request->has('template2')) {
                $templates['template2'] = $request->input('template2');
            }
            if ($request->has('template3')) {
                $templates['template3'] = $request->input('template3');
            }
            DB::table('mst_user_info')->where('mst_user_id', $mst_user_id)->update($templates);

            return $this->sendSuccess('テンプレートを更新しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ユーザ有効チェック
     *
     * @param Request $request
     * @return mixed
     */
    public function checkUser(Request $request)
    {
        try {
            // ユーザメール
            $email = $request->email;
            // ユーザ会社ID
            $mst_company_id = $request->mst_company_id;

            return $this->sendResponse(['userValid' => UserApiUtils::checkUserValid($email, $mst_company_id)], __('message.success.userCheck'));
        } catch (\Exception $ex) {
            Log::error(__('message.false.userCheck', ['email' => $email]));
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function myCompanyStamps(Request $request) {
        try {
            $user = $request->user();
            if($request['usingHash']) {
                $user = $request['user'];
            }
            //$user = $request->user();
            $date = new \DateTime($request['date']);
            if(!$user) {
                return $this->sendError('権限がありません。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }

            if(!$request['date']) {
                $date = new \DateTime();
            }

            $dstamp_style = DB::table('mst_company')->where('id', $user->mst_company_id)
                ->select('dstamp_style')->pluck('dstamp_style')->first();
            if(!$dstamp_style)  {
                $dstamp_style = 'y.m.d';
            }
            $date = \App\Http\Utils\DateJPUtils::convert($date, $dstamp_style);

            $user_stamps = DB::table('mst_assign_stamp')
                ->select(
                    'mst_company_stamp.id',
                    'mst_assign_stamp.display_no',
                    'mst_assign_stamp.stamp_flg',
                    'mst_assign_stamp.time_stamp_permission',
                    'mst_company_stamp.stamp_name',
                    'mst_company_stamp.stamp_division',
                    'mst_company_stamp.font',
                    'mst_company_stamp.stamp_image',
                    'mst_company_stamp.width',
                    'mst_company_stamp.height',
                    'mst_company_stamp.date_x',
                    'mst_company_stamp.date_y',
                    'mst_company_stamp.date_width',
                    'mst_company_stamp.date_height',
                    'mst_company_stamp.create_at',
                    'mst_company_stamp.serial',

                    // 日付の色を取得する PAC_5-107 BEGIN
                    DB::raw(StampUtils::COLOR_RED.' as color')
                // PAC_5-107 END
                )
                ->join('mst_company_stamp', function(JoinClause $join) {
                    $join->on('mst_company_stamp.id', '=', 'mst_assign_stamp.stamp_id');
                    $join->where('mst_assign_stamp.stamp_flg', StampUtils::COMMON_STAMP);

                })
                ->where('mst_assign_stamp.mst_user_id', $user->id)
                ->where('mst_assign_stamp.state_flg', AppUtils::STATE_VALID)
                ->where(DB::raw('IFNULL(mst_company_stamp.del_flg, 0)'),'!=', 1)
                ->orderBy('mst_assign_stamp.display_no')
                ->get();

            $arrStamp = $user_stamps->toArray();

            foreach ($arrStamp as $key => $stamp){
                $arrStamp[$key]->stamp_image = StampUtils::processStampImage($stamp, $date);
            }

            return $this->sendResponse($arrStamp, 'ユーザー印面の取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
