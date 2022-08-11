<?php

namespace App\Models;

use App\Http\Utils\CommonUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\LongTermFolderUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\Log;

class LongTermFolder extends Model
{
    protected $table = 'long_term_folder';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id',
        'folder_name',
        'parent_id',
        'tree',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules(){
        return [ ];
    }

    /**
     * フォルダ名の重複チェック
     * @param $mst_company_id
     * @param $parent_id
     * @param $folder_name
     * @return Model|Builder|object|null
     */
    public function isFolderNameRepeated($mst_company_id, $parent_id, $folder_name){
        return $this->where('mst_company_id', $mst_company_id)
            ->where('parent_id', $parent_id)
            ->where('folder_name', $folder_name)
            ->first();
    }

    /**
     * 親フォルダの下にあるすべての権限のユーザー
     * @param $mst_company_id
     * @param $folder_id
     * @param int $type
     * @return mixed
     */
    public function getFolderAuth($mst_company_id,$folder_id,int $type = -1){
        $folderAuth = $this->leftJoin('long_term_folder_auth','long_term_folder.id','=','long_term_folder_auth.long_term_folder_id')
            ->where('long_term_folder.id', $folder_id)
            ->where('long_term_folder.mst_company_id', $mst_company_id)
            ->where(function ($query) use ($type){
                if ($type >= 0)
                    $query->where('auth_kbn',$type);
            })
            ->select('long_term_folder_auth.*')
            ->get();
        return $folderAuth;
    }

    /**
     * フォルダの下のすべてのユーザー権限を取得
     * @param $mst_company_id
     * @return Builder
     */
    public function getFolderPermissionUsers($mst_company_id): Builder
    {
        $email      = request('email','');
        $name       = request('name','');
        $department = request('department','');
        $position   = request('position','');

        $where = ['mst_company_id = ' . intval($mst_company_id)];
        $where_arg = [];
        if ($email) {
            $where[] = 'email like ?';
            $where_arg[] = "%$email%";
        }
        if ($name) {
            $where[] = '(CONCAT(family_name, given_name) like ?)';
            $where_arg[] = "%$name%";
        }
        $departmentIds = [];
        if ($department) {
            $departmentList = DB::table('mst_department')
                ->select('id', 'parent_id')
                ->where('mst_company_id', $mst_company_id)
                ->where('state', 1)
                ->get()
                ->toArray();
            $departmentIds = [];
            DepartmentUtils::getDepartmentChildIds($departmentList, $department, $departmentIds);
        }
        $company = Company::where('id', $mst_company_id)->first();
        $user_query = DB::table('mst_user as mu')
            ->select('mu.id','mu.email','mu.family_name','mu.given_name','mu.state_flg',
                'mui.mst_department_id','mui.mst_department_id_1','mui.mst_department_id_2',
                'mui.mst_position_id','mui.mst_position_id_1','mui.mst_position_id_2')
            ->join('mst_user_info as mui', function ($query) use ($company, $departmentIds, $position) {
                $query->on('mui.mst_user_id', 'mu.id');

                if ($company->multiple_department_position_flg === 1) {
                    if ($departmentIds) {
                        $query->where(function ($query) use ($departmentIds) {
                            $query->orWhereIn('mst_department_id', $departmentIds)
                                ->orWhereIn('mst_department_id_1', $departmentIds)
                                ->orWhereIn('mst_department_id_2', $departmentIds);
                        });
                    }
                    if ($position) {
                        $query->where(function ($query) use ($position) {
                            $query->orWhere('mst_position_id', $position)
                                ->orWhere('mst_position_id_1', $position)
                                ->orWhere('mst_position_id_2', $position);
                        });
                    }
                } else {
                    if ($departmentIds) $query->whereIn('mst_department_id', $departmentIds);
                    if ($position) $query->where('mst_position_id', $position);
                }
            })
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->where('option_flg', AppUtils::USER_NORMAL)
            ->where('state_flg', AppUtils::STATE_VALID);
        return $user_query;
    }

    /**
     * 部署と役職の取得
     * @param $mst_company_id
     * @return Collection
     */
    public function searchPermissions($mst_company_id, $permission_type): Collection
    {
        $name = request('name','');
        if ($permission_type == LongTermFolderUtils::AUTH_KBN_POSITION){
            return DB::table('mst_position')
                ->where('mst_company_id',$mst_company_id)
                ->select([DB::raw('0 as user_flg'), 'id' , 'position_name' , 'position_name as sort_name'])
                ->where(function ($query) use ($name){
                    if ($name) $query->where('position_name','like','%' . $name . '%');
                })
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                });
        }elseif ($permission_type == LongTermFolderUtils::AUTH_KBN_DEPARTMENT){
            if ($name){
                $department_query = DB::table('mst_department')
                    ->where('mst_company_id',$mst_company_id)
                    ->select('tree')
                    ->where(function ($query) use ($name){
                        if ($name) $query->where('department_name','like','%' . $name . '%');
                    })
                    ->get()->implode('tree','');
                $trees = explode(',',$department_query);
                return DB::table('mst_department')
                    ->select([DB::raw('0 as user_flg') ,'id','parent_id' ,'state', 'department_name as name', 'department_name as sort_name' ,'display_no'])
                    ->where('mst_company_id',$mst_company_id)
                    ->get()->filter(function ($item) use($trees){
                        return in_array($item->id,$trees);
                    })
                    ->map(function ($sort_name) {
                        $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                        return $sort_name;
                    })
                    ->sortBy('display_no')
                    ->keyBy('id');

            }else{
                return DB::table('mst_department')
                    ->where('mst_company_id',$mst_company_id)
                    ->select([DB::raw('0 as user_flg') ,'id','parent_id' ,'state', 'department_name as name', 'department_name as sort_name' ,'display_no'])
                    ->get()
                    ->map(function ($sort_name) {
                        $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                        return $sort_name;
                    })
                    ->sortBy('display_no')
                    ->keyBy('id');
            }
        }
    }

    /**
     * 部署または役職を取得したすべてのユーザー
     * @param $mst_company_id
     * @param $company
     * @param $selected_ids array 役職|部署 のID
     * @return Collection
     */
    public function getSavedFolderPermissionUserIds(int $permission_type, $company, array $selected_ids): Collection
    {
        $departmentIds = [];
        $positionIds = [];
        if ($permission_type == LongTermFolderUtils::AUTH_KBN_DEPARTMENT) {
            $departmentIds = $selected_ids;
        } elseif ($permission_type == LongTermFolderUtils::AUTH_KBN_POSITION) {
            $positionIds = $selected_ids;
        }

        return DB::table('mst_user')
            ->select('mui.mst_user_id as id')
            ->join('mst_user_info as mui',function ($query) use ($company, $departmentIds, $positionIds,$permission_type) {
                $query->on('mui.mst_user_id', 'mst_user.id');
                if ($company->multiple_department_position_flg === 1) {
                    if ($permission_type == LongTermFolderUtils::AUTH_KBN_DEPARTMENT) {
                        $query->where(function ($query) use ($departmentIds) {
                            $query->orWhereIn('mst_department_id', $departmentIds)
                                ->orWhereIn('mst_department_id_1', $departmentIds)
                                ->orWhereIn('mst_department_id_2', $departmentIds);
                        });
                    }
                    if ($permission_type == LongTermFolderUtils::AUTH_KBN_POSITION) {
                        $query->where(function ($query) use ($positionIds) {
                            $query->orWhereIn('mst_position_id', $positionIds)
                                ->orWhereIn('mst_position_id_1', $positionIds)
                                ->orWhereIn('mst_position_id_2', $positionIds);
                        });
                    }
                } else {
                    if ($permission_type == LongTermFolderUtils::AUTH_KBN_DEPARTMENT) $query->whereIn('mst_department_id', $departmentIds);
                    if ($permission_type == LongTermFolderUtils::AUTH_KBN_POSITION) $query->whereIn('mst_position_id', $positionIds);
                }
            })
            ->where('mst_company_id', $company->id)
            ->where('option_flg', AppUtils::USER_NORMAL)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->get('id');
    }
}
