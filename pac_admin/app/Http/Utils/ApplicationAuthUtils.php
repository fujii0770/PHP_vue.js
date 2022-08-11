<?php

namespace App\Http\Utils;

use App\Models\AppRole;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use phpseclib\Crypt\DES;
use function Aws\map;

class ApplicationAuthUtils
{
    const APPLICATION_IDS = [AppUtils::GW_APPLICATION_ID_BOARD,
        AppUtils::GW_APPLICATION_ID_TIME_CARD,
        AppUtils::GW_APPLICATION_ID_FILE_MAIL,
        AppUtils::GW_APPLICATION_ID_ADDRESS_LIST];
    const APPLICATION_ROLE_IS_DEFAULT = 1;

    /**
     * 機能登録
     * @param int $company_id
     * @param int $application_id 1:掲示板   2:タイムカード   3:ファイルメール便
     * @param int $limit_flg
     * @param int $buy_count
     * @return bool
     */
    public static function storeCompanySetting(int $company_id, int $application_id, int $limit_flg = null, int $buy_count = null): bool
    {
        $result = DB::table('mst_application_companies')
            ->updateOrInsert([
                'mst_application_id' => $application_id,
                'mst_company_id' => $company_id
            ], [
                'mst_application_id' => $application_id,
                'mst_company_id' => $company_id,
                'is_infinite' => $limit_flg,
                'purchase_count' => $buy_count,
                'updated_at' => Carbon::now()
            ]);
        if (!$result) {
            Log::error("store Company application setting failed: company_id:$company_id application_id:$application_id");
        }
        return $result;
    }

    /**
     * @param int $company_id
     * @return Collection
     */
    public static function getCompanyAppSearch(int $company_id): Collection
    {
        $join_sub = DB::table('mst_application_companies')
            ->where([
                'mst_company_id' => $company_id
            ]);
        return DB::table('mst_application')
            ->leftJoinSub($join_sub, 'ac', function (JoinClause $join) {
                $join->on('mst_application.id', '=', 'ac.mst_application_id');
            })
            ->select(DB::raw("mst_application.id,mst_application.app_code,mst_application.app_name,IF(ac.mst_company_id,1,0) as is_auth,IFNULL(ac.is_infinite,0) as is_infinite,IFNULL(ac.purchase_count,0) as purchase_count"))
            ->get()->each(function ($val) {
                $val->purchase_count = (int)$val->purchase_count;
                $val->is_auth = (int)$val->is_auth;
                $val->is_infinite = (int)$val->is_infinite;
            });
    }

    /**
     * @param int $company_id
     * @param int $application_id
     */
    public static function deleteCompanySetting(int $company_id, int $application_id): void
    {
        DB::table('mst_application_companies')
            ->where([
                'mst_application_id' => $application_id,
                'mst_company_id' => $company_id
            ])
            ->delete();
    }

    /**
     * @param int $company_id
     * @return array
     */
    public static function getCompanySetting(int $company_id): array
    {
        $settings = [];
        self::getCompanyAppSearch($company_id)->each(function ($value) use (&$settings) {
            switch ($value->id) {
                case AppUtils::GW_APPLICATION_ID_BOARD:
                    $settings['board_flg'] = $value->is_auth;
                    break;
                case AppUtils::GW_APPLICATION_ID_FILE_MAIL:
                    $settings['file_mail_flg'] = $value->is_auth;

                    break;
                case AppUtils::GW_APPLICATION_ID_TIME_CARD:
                    $settings['attendance_flg'] = $value->is_auth;
                    break;
                case AppUtils::GW_APPLICATION_ID_FAQ_BOARD:
                    $settings['faq_board_flg'] = $value->is_auth;
                    break;
                case AppUtils::GW_APPLICATION_ID_TO_DO_LIST:
                    $settings['to_do_list_flg'] = $value->is_auth;
                    break;
            }
        });
        return $settings;

    }

    public static function appUsersSearch($company_id, $application_id, $email = '', $department = '', $position = '', $username = '', $isValid = '')
    {
        $search_query[] = ['mst_user.mst_company_id', '=', $company_id];
        $search_query[] = ['mst_user.state_flg', '=', AppUtils::STATE_VALID];
        if ($email != '') {
            $search_query[] = ['mst_user.email', 'like', '%' . $email . '%'];
        }
        if ($username != '') {
            $search_query[] = [DB::raw('CONCAT(mst_user.family_name, mst_user.given_name)'), 'like', '%' . $username . '%'];
        }


        $joinSub = DB::table('mst_application_users')
            ->where('mst_application_id', $application_id);
        return DB::table('mst_user')
            ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
            ->leftJoin('mst_department', 'mst_user_info.mst_department_id', '=', 'mst_department.id')
            ->leftJoin('mst_position', 'mst_user_info.mst_position_id', '=', 'mst_position.id')
            ->leftJoinSub($joinSub, 'au', 'mst_user.id', '=', 'au.mst_user_id')
            ->where($search_query)
            ->whereIn('mst_user.option_flg',[AppUtils::USER_OPTION,AppUtils::USER_NORMAL])
            ->where(function (Builder $query) use ($department, $position) {
                if ($department != '') {
                    $query->where('mst_user_info.mst_department_id', $department);
                }
                if ($position != '') {
                    $query->where('mst_user_info.mst_position_id', $position);
                }
            })
            ->where(function (Builder $query) use ($isValid) {
                if ($isValid != '') {
                    if ($isValid == 0) {
                        $query->whereNull('au.mst_user_id');
                    } elseif ($isValid == 1) {
                        $query->whereNotNull('au.mst_user_id');
                    }
                }
            })
            ->select([
                'mst_user.id',
                'mst_user.email',
                DB::raw('CONCAT(mst_user.family_name, mst_user.given_name) AS name'),
                DB::raw('IF(ISNULL(au.mst_user_id),0,1) as enabled'),
                'mst_department.department_name as department',
                'mst_position.position_name as position',
            ])
            ->get()
            ->map(function ($item) {
                return get_object_vars($item);
            })
            ->toArray();
    }

    /**
     * 会社ユーザグループウエア
     * @param $company_id
     * @param $ids
     * @return array
     */
    public static function getAppUsersStateSearch($company_id, $ids)
    {
        $search_query[] = ['mst_user.mst_company_id', '=', $company_id];
        $search_query[] = ['mst_user.state_flg', '=', AppUtils::STATE_VALID];

        return DB::table('mst_user')
            ->join('mst_application_users as au', 'mst_user.id', '=', 'au.mst_user_id')
            ->where($search_query)
            ->whereIn('mst_user.id', $ids)
            ->where('au.mst_application_id', '!=', AppUtils::GW_APPLICATION_ID_BOARD)
            ->whereNotNull('au.mst_user_id')
            ->select([
                'mst_user.id'
            ])
            ->pluck('mst_user.id')
            ->toArray();
    }

    /**
     * グループウエア
     * @return array
     */
    public static function getAppStateSearch(){
        return DB::table('mst_application')
            ->select([
                'id'
            ])
            ->pluck('id')
            ->toArray();
    }

    public static function appUserUpdate($company_id, $mst_application_id, $mst_user_id): void
    {
        $hasUser = DB::table('mst_user')
            ->where([
                'id' => $mst_user_id,
                'mst_company_id' => $company_id
            ])->exists();
        if ($hasUser) {
            DB::table('mst_application_users')
                ->updateOrInsert([
                    'mst_application_id' => $mst_application_id,
                    'mst_user_id' => $mst_user_id
                ], [
                    'mst_application_id' => $mst_application_id,
                    'mst_user_id' => $mst_user_id
                ]);
        }
    }

    public static function appUserDelete($company_id, $mst_application_id, $mst_user_id): void
    {
        $hasUser = DB::table('mst_user')
            ->where([
                'id' => $mst_user_id,
                'mst_company_id' => $company_id
            ])->exists();
        if ($hasUser) {
            DB::table('mst_application_users')
                ->where([
                    'mst_application_id' => $mst_application_id,
                    'mst_user_id' => $mst_user_id
                ])
                ->delete();
        }
    }

    /**
     * @param $mst_company_id
     * @param $mst_application_id
     */
    public static function getCompanyAppRoleSearch($mst_company_id, $mst_application_id): array
    {
        $roles = DB::table('app_role')
            ->where('mst_application_id', $mst_application_id)
            ->where('mst_company_id', $mst_company_id)
            ->orWhere(function (Builder $query) use ($mst_application_id) {
                $query->where('mst_application_id', $mst_application_id)
                    ->whereNull('mst_company_id')
                    ->where('is_default', ApplicationAuthUtils::APPLICATION_ROLE_IS_DEFAULT);

            })
            ->select(DB::raw('id,name,mst_application_id,is_default as isDefault'))
            ->get()
            ->map(function ($value) {
                return get_object_vars($value);
            });

        $roleList = $roles->toArray();
        $listRole = [];
        $roles->each(function ($role) use (&$listRole) {
            $listRole[$role['id']] = $role['name'];
        });
        return [$roleList, $listRole];
    }

    public static function getCompanyAppUsersSearch($company_id, $application_id, $app_role_id, $email, $department, $position, $username)
    {
        $search_query[] = ['mst_user.mst_company_id', '=', $company_id];
        $search_query[] = ['mst_user.state_flg', '=', AppUtils::STATE_VALID];
        $searchRole=[];
        if ($email != '') {
            $search_query[] = ['mst_user.email', 'like', '%' . $email . '%'];
        }
        if ($username != '') {
            $search_query[] = [DB::raw('CONCAT(mst_user.family_name, mst_user.given_name)'), 'like', '%' . $username . '%'];
        }
//        if ($app_role_id != '') {
//            $search_query[] = ['app_role.id', '=', $app_role_id];
//        }
        
        

        $defaultRole = DB::table('app_role')
            ->where('mst_application_id', $application_id)
            ->where('is_default', self::APPLICATION_ROLE_IS_DEFAULT)
            ->first();
        if ($app_role_id != '') {
            if ($defaultRole->id == $app_role_id){
                $searchRole = function (Builder $query) use ($app_role_id) {
                    $query->where('app_role.id',$app_role_id)
                        ->orWhereNull('app_role.id');
                };
            }else{
                $searchRole[] = ['app_role.id', '=', $app_role_id];
            }
        }
        $joinSub = DB::table('mst_application_users')
            ->where('mst_application_id', $application_id);
        return DB::table('mst_user')
            ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
            ->leftJoin('mst_department', 'mst_user_info.mst_department_id', '=', 'mst_department.id')
            ->leftJoin('mst_position', 'mst_user_info.mst_position_id', '=', 'mst_position.id')
            ->leftJoinSub($joinSub, 'au', 'mst_user.id', '=', 'au.mst_user_id')
            ->leftJoin('app_role_users', 'app_role_users.mst_user_id', '=', 'mst_user.id')
            ->leftJoin('app_role', 'app_role_users.app_role_id', '=', 'app_role.id')
            ->where($search_query)
            ->where($searchRole)
            ->whereIn('mst_user.option_flg',[AppUtils::USER_OPTION,AppUtils::USER_NORMAL])
            ->where(function (Builder $query) use ($department, $position) {
                if ($department != '') {
                    $query->where('mst_user_info.mst_department_id', $department);
                }
                if ($position != '') {
                    $query->where('mst_user_info.mst_position_id', $position);
                }
            })
            ->where(function (Builder $query) use ($application_id) {
                if ($application_id != AppUtils::GW_APPLICATION_ID_BOARD) {
                    $query->whereNotNull('au.mst_user_id');
                }
            })
            ->select([
                'mst_user.id',
                'mst_user.email',
                DB::raw('CONCAT(mst_user.family_name, mst_user.given_name) AS name'),
                DB::raw('IF(ISNULL(au.mst_user_id),0,1) as enabled'),
                'mst_department.department_name as department',
                'mst_position.position_name as position',
                'app_role_users.app_role_id as app_role_users_id',
                'app_role.name as app_role_name',
                'app_role.id as app_role_id'
            ])
            ->get()
            ->map(function ($item) use ($defaultRole) {
                if (is_null($item->app_role_name) && $defaultRole) {
                    $item->app_role_name = $defaultRole->name;
                }
                return get_object_vars($item);
            })
            ->toArray();

    }

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

        $response_role_arry = [
            'id' => $role->id,
            'name' => $role->name,
            'mstCompanyId' => $role->mst_company_id,
            'mstApplicationId' => $role->mst_application_id,
            'isDefault' => $role->is_default == 1,
            'memo' => $role->memo,
            'createdAt' => $role->created_at,
            'updatedAt' => $role->updated_at,
            'mstAppFunctionList' => $mst_app_function_data
        ];
        return $response_role_arry;
    }

    /**
     * @param $company_id
     * @param $application_id
     * @param $role_id
     * @param $user_ids
     */
    public static function updateCompanyAppUser($company_id, $application_id, $role_id, $user_ids)
    {
        if (!empty($user_ids)) {
            $role_user_lists = [];
            //選択しているアプリに含まれるロールを取得
            $role_ids = DB::table('app_role')
                ->where('mst_application_id', '=', $application_id)
                ->where(function (Builder $query) use ($company_id) {
                    $query->where('mst_company_id', $company_id)
                        ->orWhere('is_default', ApplicationAuthUtils::APPLICATION_ROLE_IS_DEFAULT);
                })
                ->pluck('id')->toArray();

            //ロールを設定しているユーザの一覧を取得
            if (!empty($role_ids)) {
                $role_users = DB::table('app_role_users')
                    ->whereIn('app_role_id', $role_ids)
                    ->get()->toArray();
                if (!empty($role_users)) {
                    foreach ($role_users as $user) {
                        $role_user_lists[$user->mst_user_id] = [
                            'id' => $user->id,
                            'app_role_id' => $user->app_role_id
                        ];
                    }
                }
            }

            //チェックがついてるユーザを確認
            foreach ($user_ids as $id) {
                //登録されているロールユーザに情報があり設定されているロールが違っている場合は更新する
                if (isset($role_user_lists[$id])){
                    DB::table('app_role_users')
                        ->where('id',$role_user_lists[$id]['id'])
                        ->update([
                            'app_role_id'=>$role_id,
                            'updated_at' => Carbon::now()
                        ]);
                }else{
                    DB::table('app_role_users')
                        ->insert([
                            'app_role_id'=>$role_id,
                            'mst_user_id'=>$id,
                            'created_at'=>Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                }
            }
        }
    }

    /**
     * @param $mst_company_id
     * @param $memo
     * @param $mst_application_id
     * @param $cids
     * @param $name
     * @return false
     */
    public static function storeCompanyAppDetail($mst_company_id, $memo, $mst_application_id, $cids, $name)
    {
        DB::beginTransaction();
        try {
            $role_id = DB::table('app_role')
                ->insertGetId([
                    'name' => $name,
                    'mst_company_id' => $mst_company_id,
                    'mst_application_id' => $mst_application_id,
                    'memo' => $memo,
                ]);//app_role_detailの登録処理
            if (!empty($cids)) {
                $insert_array = [];
                foreach ($cids as $id) {
                    array_push($insert_array, [
                        'app_role_id' => $role_id,
                        'mst_access_privilege_id' => $id
                    ]);
                }
                DB::table('app_role_detail')
                    ->insert($insert_array);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Role mstApplicationId:' . $mst_application_id);
            return false;
        }
    }

    public static function updateCompanyAppDetail($role_id, $mst_company_id, $memo, $mst_application_id, $cids, $name)
    {
        DB::beginTransaction();
        try {
            DB::table('app_role')
                ->where('id', $role_id)
                ->update([
                    'name' => $name,
                    'mst_company_id' => $mst_company_id,
                    'mst_application_id' => $mst_application_id,
                    'memo' => $memo,
                ]);//app_role_detailの登録処理
            DB::table('app_role_detail')->where('app_role_id', '=', $role_id)->delete();
            if (!empty($cids)) {
                $insert_array = [];
                foreach ($cids as $id) {
                    array_push($insert_array, [
                        'app_role_id' => $role_id,
                        'mst_access_privilege_id' => $id
                    ]);
                }
                DB::table('app_role_detail')
                    ->insert($insert_array);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('update Role mstApplicationId:' . $mst_application_id);
            return false;
        }
    }

    public static function deleteCompanyAppDetail($role_id, $mst_company_id, $mst_application_id)
    {
        DB::beginTransaction();
        try {
            $count = DB::table('app_role')
                ->where('mst_application_id', $mst_application_id)
                ->where('mst_company_id', $mst_company_id)
                ->where('id', $role_id)
                ->where('is_default', '!=', self::APPLICATION_ROLE_IS_DEFAULT)
                ->delete();

            if ($count > 0) {
                DB::table('app_role_detail')
                    ->where('app_role_id', '=', $role_id)
                    ->delete();
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('delete Role mstApplicationId:' . $mst_application_id);
            return false;
        }
    }
}