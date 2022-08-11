<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUploadUserImageAPIRequest;
use App\Http\Requests\API\UpdateUserInfoAPIRequest;
use App\Http\Utils\AppUtils;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class UserInfoAPIController
 * @package App\Http\Controllers\API
 */

class UserInfoAPIController extends AppBaseController
{
    var $table = 'mst_user_info';
    var $model = null;

    public function __construct(UserInfo $userInfo)
    {
        $this->model = $userInfo;
    }

    /**
     * Display the specified UserInfo.
     * GET|HEAD /getUserInfo/{email}
     *
     * @param $email
     *
     * @return Response
     */
    public function getUserInfo($email)
    {
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▼
        $userInfo = DB::table('mst_user_info')
            ->join('mst_user', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
            ->join('mst_constraints', 'mst_user.mst_company_id', 'mst_constraints.mst_company_id')
            ->join('mst_company', 'mst_user.mst_company_id', 'mst_company.id')
            ->join('mst_limit', 'mst_limit.mst_company_id', 'mst_company.id')
            ->select('mst_user_info.*','mst_constraints.max_document_size','mst_constraints.max_attachment_size','mst_company.received_only_flg',
                'mst_company.rotate_angle_flg','mst_user.state_flg','mst_company.repage_preview_flg','mst_user_info.rotate_angle_flg as rotate_angle_flg_user',
                /*PAC_5-2616 S*/
                'mst_limit.enable_any_address',
                /*PAC_5-2616 E*/
                // PAC_5-1488 クラウドストレージを追加する Start
                'mst_company.long_term_storage_option_flg','mst_limit.storage_local','mst_limit.storage_box','mst_limit.storage_google','mst_limit.storage_dropbox','mst_limit.storage_onedrive',
                /*PAC_5-2705 S*/
                'mst_limit.require_approve_flag')
                /*PAC_5-2705 E*/
                // PAC_5-1488 End
            ->where('mst_user.email', $email)
            ->where('mst_user.state_flg', '!=',AppUtils::STATE_DELETE)
            ->first();
        // PAC_5-1264 かたむけて捺印をユーザー単位で非表示にできるようにする　対応　▲

        return $this->sendResponse($userInfo, '');
    }
    /**
     * Display the specified UserInfo.
     * GET|HEAD /getUserInfoById/{id}
     *
     * @param $id
     *
     * @return Response
     */
    public function getUserInfoById($id)
    {
        $userInfo = DB::table('mst_user')
            ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
            ->join('mst_company', 'mst_user.mst_company_id', 'mst_company.id')
            ->leftjoin('mst_department as dep', 'mst_user_info.mst_department_id', 'dep.id')
            ->leftjoin('mst_department as dep1', 'mst_user_info.mst_department_id_1', 'dep1.id')
            ->leftjoin('mst_department as dep2', 'mst_user_info.mst_department_id_2', 'dep2.id')
            ->leftjoin('mst_position as pos', 'mst_user_info.mst_position_id', 'pos.id')
            ->leftjoin('mst_position as pos1', 'mst_user_info.mst_position_id_1', 'pos1.id')
            ->leftjoin('mst_position as pos2', 'mst_user_info.mst_position_id_2', 'pos2.id')
            ->select(
                'mst_user.email',
                'mst_user.family_name',
                'mst_user.given_name',
                'dep.department_name as department_name',
                'dep1.department_name as department_name_1',
                'dep2.department_name as department_name_2',
                'pos.position_name as position_name',
                'pos1.position_name as position_name_1',
                'pos2.position_name as position_name_2',
                'mst_user_info.postal_code',
                'mst_user_info.address',
                'mst_user_info.phone_number',
                'mst_user_info.phone_number_extension',
                'mst_user_info.phone_number_mobile',
                'mst_user_info.fax_number',
                'mst_user_info.user_profile_data',
                'mst_company.company_name',
                'mst_company.multiple_department_position_flg',
                )
            ->where('mst_user.id', $id)
            ->where('mst_user.state_flg', '!=',AppUtils::STATE_DELETE)
            ->first();

        return $this->sendResponse($userInfo, '');
    }

    /**
     * Display the specified UserInfos.
     * GET|HEAD /getUserInfos
     *
     * @return Response
     */
    public function getUserInfos(Request $request)
    {
        $userInfos = [];

        if (isset($request['ids']) && $request['ids']){
            $ids = explode(',', $request['ids'] );

            $userInfos = DB::table('mst_user_info')->join('mst_user', 'mst_user.id', '=', 'mst_user_info.mst_user_id')->select(['mst_user_info.*', 'mst_user.email'])->whereIn('mst_user.email', $ids)->get();
        }

        return $this->sendResponse($userInfos, '');
    }

    /**
     * Store a newly image UserProfile in storage.
     * PUT /favorite
     *
     * @param CreateUploadUserImageAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function userImage (CreateUploadUserImageAPIRequest $request)
    {
        $input = $request->all();
        $user = $request->user();
        try {
            $image = $input['image'];
            DB::table($this->table)->where('mst_user_id', $user->id)->update(['user_profile_data' => $image]);
            return $this->sendSuccess(['プロファイル画像を更新するのが成功しました。']);
        } catch (Exception $ex) {
            Log::error('UserInfoAPIController@userImage:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getCurrentUserDepartmentInfo(Request $request)
    {

        $user = $request->user();
        try {
            $userInfo = DB::table('mst_user_info')
                ->leftJoin('mst_department', 'mst_department.id', 'mst_user_info.mst_department_id')
                ->join('mst_user','mst_user.id', 'mst_user_info.mst_user_id')
                ->where('mst_user_info.mst_user_id', $user->id)
                // Todo: use db::raw to get full_name
                ->select(
                    "mst_user_info.id",
                    "mst_department.department_name",
                    DB::raw("CONCAT(mst_user.family_name, ' ', mst_user.given_name) as full_name")
                )->first();
            return $this->sendResponse($userInfo, 'getCurrentUserDepartmentInfo success');
        } catch (Exception $ex) {
            Log::error('UserInfoAPIController@getCurrentUserDepartmentInfo:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
