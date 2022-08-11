<?php

namespace App\Http\Utils;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ApplicationAuthUtils
{
    const APPLICATION_IDS = [AppUtils::GW_APPLICATION_ID_BOARD, AppUtils::GW_APPLICATION_ID_TIME_CARD, AppUtils::GW_APPLICATION_ID_FILE_MAIL];
    const APPLICATION_ROLE_IS_DEFAULT = 1;
    /**
     * @param $role_id
     * @param $company_id
     * @return array
     */
    public static function getCompanyAppUserDetail($role_id, $company_id)
    {
        $role = DB::table('app_role')
            ->where('id', $role_id)
            ->where(function (Builder $query) use ($company_id) {
                $query->where('mst_company_id', $company_id)
                    ->orWhere('is_default', ApplicationAuthUtils::APPLICATION_ROLE_IS_DEFAULT);
            })
            ->first();

        $mst_app_function_data = [];
        $mst_app_function_list = DB::table('mst_app_function_management')
            ->select('mst_app_function_management.id', 'mst_app_function.function_name', DB::raw('mst_app_function.id as function_id'))
            ->leftjoin('mst_app_function', 'mst_app_function.id', '=', 'mst_app_function_management.mst_app_function_id')
            ->where('mst_app_function_management.mst_application_id', $role->mst_application_id)
            ->get()->toArray();

        if (!empty($mst_app_function_list)) {
            foreach ($mst_app_function_list as $item) {
                $sub_query = DB::raw('select mst_access_privilege_id from app_role_detail where app_role_id = ' . $role->id);
                $mst_access_privileges_list = DB::table('mst_access_privileges')
                    ->select('mst_access_privileges.id', 'mst_access_privileges.privilege_code', 'mst_access_privileges.privilege_content', DB::raw('app_role_detail.mst_access_privilege_id as is_auth'))
                    ->where('mst_access_privileges.mst_app_function_id', $item->function_id)
                    ->leftjoin(DB::raw("({$sub_query}) as app_role_detail"), 'mst_access_privileges.id', '=', 'app_role_detail.mst_access_privilege_id')
                    ->get()->toArray();

                $mst_access_privileges_data = [];
                if (!empty($mst_access_privileges_list)) {
                    foreach ($mst_access_privileges_list as $privilege) {
                        $mst_access_privileges_data[] = [
                            'id' => $privilege->id,
                            'privilegeCode' => $privilege->privilege_code,
                            'privilegeContent' => $privilege->privilege_content,
                            'isAuth' => !empty($privilege->is_auth)
                        ];
                    }
                }

                $mst_app_function_data[] = [
                    'id' => $item->id,
                    'functionName' => $item->function_name,
                    'mstAccessPrivilegesList' => $mst_access_privileges_data
                ];
                unset($sub_query, $mst_access_privileges_list, $mst_access_privileges_data);
            }
            unset($mst_app_function_list);
        }

        return $mst_app_function_data;
    }
    public static function getUserRole($user_id,$company_id){
        $auth = [];
        $defaultRoles = DB::table('app_role')
            ->where('is_default', ApplicationAuthUtils::APPLICATION_ROLE_IS_DEFAULT)
            ->select([
                'id',
                'mst_application_id'
            ])
            ->get()
            ->keyBy('mst_application_id');

        $user_roles = DB::table('app_role')
            ->join('app_role_users', 'app_role_users.app_role_id', '=', 'app_role.id')
            ->where('app_role_users.mst_user_id', $user_id)
            ->select([
                'app_role.id as id',
                'app_role.mst_application_id as mst_application_id'
            ])
            ->get()
            ->keyBy('mst_application_id');

        DB::table('mst_application')
            ->leftJoinSub(function ($query) use ($company_id, $user_id) {
                $query->from('mst_application_companies')
                    ->where('mst_application_companies.mst_company_id', $company_id);
            }, 'mst_application_companies', 'mst_application.id', '=', 'mst_application_companies.mst_application_id')
            ->leftJoinSub(function ($query) use ($user_id) {
                $query->from('mst_application_users')
                    ->where('mst_application_users.mst_user_id', $user_id);
            }, 'mst_application_users', 'mst_application_users.mst_application_id', '=', 'mst_application.id')
            ->select([
                'mst_application.id',
                'mst_application.app_name',
                'mst_application_companies.id as company_is_auth',
                'mst_application_users.id as user_is_auth',
            ])
            ->get()
            ->each(function ($item) use ($company_id, $user_id, $defaultRoles, &$auth,$user_roles) {
                $arr = [];
                $arr['id'] = $item->id;
                $arr['appName'] = $item->app_name;
                $arr['isAuth'] = false;
                if ($item->id == AppUtils::GW_APPLICATION_ID_BOARD && $item->company_is_auth) {
                    $arr['isAuth'] = true;
                } elseif ($item->company_is_auth && $item->user_is_auth) {
                    $arr['isAuth'] = true;
                }
                if (isset($defaultRoles[$item->id]) && $arr['isAuth'] == true){
                    $role=isset($user_roles[$item->id])?$user_roles[$item->id]:$defaultRoles[$item->id];
                    $arr['auth']=ApplicationAuthUtils::getCompanyAppUserDetail($role->id,$company_id);
                }
                $auth[] = $arr;
            });
        return $auth;
    }
}