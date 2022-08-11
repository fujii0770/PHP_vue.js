<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use App\Http\Utils\DepartmentUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Utils\CommonUtils;

class User extends Model
{
    protected $table = 'mst_user';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id','login_id', 'system_id','family_name','given_name','email', 'state_flg', 'create_user', 'update_user',
        'amount','password_change_date','hr_user_flg','create_user','update_user','invalid_at', 'notification_email', 'option_flg', 'reference', 'hr_admin_flg','without_email_flg'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password' ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id = "", $checkEmailUniq = true, $isOption = false, $without_email = false)
    {
        $rules = [
            'given_name' => 'required|max:64',
            'family_name' => 'required|max:64',
            'state_flg' => 'required|numeric',
        ];

        if (!$isOption) {
            if ($checkEmailUniq) {
                $rules['email'] = ['required',
                    'max:256',
                    "regex:/^([\\-\\+\\.\\!\\#\\$\\%\\&\\'\\*\\/\\=\\?\\^\\_\\`\\{\\|\\}\\~\\.\w+]+)@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",
                    Rule::unique($this->table)->where(function ($query) {
                        return $query->where('state_flg', '!=', AppUtils::STATE_DELETE);
                    })->ignore($id),
                ];
            } else {
                $rules['email'] = [
                    'required',
                    'max:256',
                    "regex:/^([\\-\\+\\.\\!\\#\\$\\%\\&\\'\\*\\/\\=\\?\\^\\_\\`\\{\\|\\}\\~\\.\w+]+)@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",
                ];
            }
        } else {
            if ($checkEmailUniq) {
                $rules['email'] = ['required',
                    'max:256',
                    "regex:/^[0-9a-zA-Z\=\+\-\^\$\*\.\[\]\{\}\(\)\?\"\!\@\%\&\/\\\>\<\'\:\;\|\_\~]{6,}+$/",
                    Rule::unique($this->table)->where(function ($query) {
                        return $query->where('state_flg', '!=', AppUtils::STATE_DELETE);
                    })->ignore($id),
                ];
            } else {
                $rules['email'] = [
                    'required',
                    'max:256',
                    "regex:/^[0-9a-zA-Z\=\+\-\^\$\*\.\[\]\{\}\(\)\?\"\!\@\%\&\/\\\>\<\'\:\;\|\_\~]{6,}+$/",
                ];
            }
            if (!$without_email) {
                $rules['notification_email'] = [
                    'required',
                    'max:256',
                    "regex:/^([\\-\\+\\.\\!\\#\\$\\%\\&\\'\\*\\/\\=\\?\\^\\_\\`\\{\\|\\}\\~\\.\w+]+)@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",
                ];
            }
        }

        return $rules;
    }

    public function info()
    {
        return $this->hasOne('App\Models\UserInfo','mst_user_id');
    }

     public function getStamps($id = 0){
         if(!$id) $id = $this->id;
         if(!$id) return null;

        $stamps = [];

        // get stamp company
        $stampCompany = AssignStamp::where(['stamp_flg'=>1,'mst_user_id'=>$id,'state_flg'=>AppUtils::STATE_VALID])
            ->select('id as assign_id','stamp_id','stamp_flg')
			->with('stampCompany')
			->with('stampAdmin')
            ->with('stampGroup')
			->get();

        // get stamp master
        $stampMaster = AssignStamp::where(['stamp_flg'=>0,'mst_user_id'=>$id,'state_flg'=>AppUtils::STATE_VALID])
            ->select('id as assign_id','stamp_id','stamp_flg')
            ->with('stampMaster')->get();

        // get stamp department
        $stampDepartment = AssignStamp::where(['stamp_flg'=>'2','mst_user_id'=>$id])
            ->where('state_flg',AppUtils::STATE_VALID)
            ->select('id as assign_id','stamp_id','stamp_flg','state_flg')
            ->with('stampDepartment')->get();

         // get stamp department
         $stampWaitDepartment = AssignStamp::where(['stamp_flg'=>'2','mst_user_id'=>$id])
             ->where('state_flg',AppUtils::STATE_WAIT_ACTIVE)
             ->select('id as assign_id','stamp_id','stamp_flg','state_flg')
             ->with('stampDepartment')->get();

         $convenientStamp = AssignStamp::where(['mst_user_id'=>$id,'stamp_flg'=>3,'state_flg'=>AppUtils::STATE_VALID])
             ->join('mst_company_stamp_convenient',function ($query){
                 $query->on('mst_assign_stamp.stamp_id','mst_company_stamp_convenient.id');
             }) ->join('mst_stamp_convenient',function ($subQuery){
                 $subQuery->on('mst_company_stamp_convenient.mst_stamp_convenient_id','mst_stamp_convenient.id');
             })->select('mst_assign_stamp.id as assign_id' ,
                 'mst_assign_stamp.stamp_flg','mst_assign_stamp.stamp_id','stamp_name',
                 'stamp_image','stamp_division','mst_company_stamp_convenient.id','stamp_date_flg','date_color','date_width','date_height','date_x','date_y')
             ->get();

        // return $stamps;
        return \compact('stampCompany','stampMaster','stampDepartment','stampWaitDepartment','convenientStamp');
     }

     public function getFullName()
     {
         return implode(' ', [$this->family_name, $this->given_name]);
     }

     public function getList($mst_company_id = 0, $option_flg, $getStampInfo = false, $limit = null, $searchData = [],$FolderUserId = null){
        $email      = request('email','');
        $name       = request('name','');
        $department = request('department','');
        $departmentChild = request('departmentChild','');
        $position   = request('position','');
        $state      = trim(request('state',''));
        $orderBy    = request('orderBy') ? request('orderBy') : 'id';
        $orderDir   = request('orderDir') ? request('orderDir'): 'desc';
        $notification_email = request('notification_email', '');
        $reference = request('reference', '');

        $where      = ['mst_company_id = '. intval($mst_company_id)];
        if(is_array($option_flg)) {
            $where[] = 'option_flg in ('. implode(',', $option_flg).')';
            $where_arg = [];
        } else {
            $where[]      = 'option_flg = '. $option_flg;
            $where_arg = [];
        }
        if($email){
            $where[]        = 'email like ?';
            $where_arg[]    = "%$email%";
        }
         if ($notification_email) {
             $where[] = 'notification_email like ?';
             $where_arg[] = "%$notification_email%";
         }
         if ($reference) {
             $where[] = 'reference like ?';
             $where_arg[] = "%$reference%";
         }

        if($name){
            $where[]        = '(CONCAT(family_name, given_name) like ?)';
            $where_arg[]    = "%$name%";
        }

        if(!CommonUtils::isNullOrEmpty($FolderUserId)){
            $where[] = "id in (".$FolderUserId.")";
        }

        if($state !== ''){
            if ($state == 0){
                $where[]    = "state_flg in (0,9) ";
            }else{
                $where[]    = "state_flg = ". intval($state);
            }
        }else $where[]  = "state_flg != ". AppUtils::STATE_DELETE;
        $_orderBy = array_search($orderBy,['stampDate','stampCommon','stampName','stampConvenient']) === false?$orderBy:'id';
        // PAC_5-2098 Start
        $company = Company::where('id', (int)$mst_company_id)->first();
        // PAC_5-2098 End
        if($limit){
            if(array_search($orderBy,['stampDate','stampCommon','stampName','stampConvenient']) === false){
                $users = $this->_getUserByNomal($where, $where_arg, $_orderBy, $orderDir, $limit, $company, $searchData);
            }else{
                $users = $this->_getUserOrderStamp($where, $where_arg, $orderBy, $orderDir, $limit, $company, $searchData);
            }
        }else{
            Log::debug('getList_nolimit');

            $departmentIds = [];
            if ($departmentChild && $department) {
                $departmentList = DB::table('mst_department')
                    ->select('id', 'parent_id')
                    ->where('mst_company_id', $mst_company_id)
                    ->where('state', 1)
                    ->get()
                    ->toArray();
                $departmentIds = [];
                DepartmentUtils::getDepartmentChildIds($departmentList, $department, $departmentIds);
            }
            // PAC_5-2098 Start
            $users = $this->whereHas('info', function (Builder $query) use ($department, $position, $company, $departmentChild, $departmentIds) {
                if ($company->multiple_department_position_flg === 1) {
                    // PAC_5-1599 追加部署と役職 Start
                    if($department) $query->where(function($query) use ($department, $departmentChild, $departmentIds) {
                        if ($departmentChild) {
                            $query->orWhereIn('mst_department_id', $departmentIds)
                                ->orWhereIn('mst_department_id_1', $departmentIds)
                                ->orWhereIn('mst_department_id_2', $departmentIds);
                        } else {
                            $query->orWhere('mst_department_id', $department)
                                ->orWhere('mst_department_id_1', $department)
                                ->orWhere('mst_department_id_2', $department);
                        }
                        
                    });
                    if($position) $query->where(function($query) use ($position) {
                        $query->orWhere('mst_position_id', $position)
                            ->orWhere('mst_position_id_1', $position)
                            ->orWhere('mst_position_id_2', $position);
                    });
                    // PAC_5-1599 End
                } else {
                    if($department) {
                        if ($departmentChild) {
                            $query->whereIn('mst_department_id', $departmentIds);
                        } else {
                            $query->where('mst_department_id', $department);
                        }
                    }
                    if($position) $query->where('mst_position_id', $position);
                }
            })
            // PAC_5-2098 End
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($_orderBy,$orderDir)->get();
        }

        return $users;
    }

    protected function _getUserByNomal($where, $where_arg, $_orderBy, $orderDir, $limit, $company, $searchData){
        $department = request('department','');
        $position   = request('position','');
        $departmentChild = request('departmentChild','');
        
        $departmentIds = [];
        if ($departmentChild && $department) {
            $departmentList = DB::table('mst_department')
                ->select('id', 'parent_id')
                ->where('mst_company_id', $company->id)
                ->where('state', 1)
                ->get()
                ->toArray();
            $departmentIds = [];
            DepartmentUtils::getDepartmentChildIds($departmentList, $department, $departmentIds);
        }
        // PAC_5-2098 Start
        $userQuery = $this->whereHas('info', function (Builder $query) use ($department, $position, $company, $departmentChild, $departmentIds) {
            if ($company->multiple_department_position_flg === 1) {
                // PAC_5-1599 追加部署と役職 Start
                if ($department) $query->where(function ($query) use ($department, $departmentChild, $departmentIds) {
                    if ($departmentChild) {
                        $query->orWhereIn('mst_department_id', $departmentIds)
                            ->orWhereIn('mst_department_id_1', $departmentIds)
                            ->orWhereIn('mst_department_id_2', $departmentIds);
                    } else {
                        $query->orWhere('mst_department_id', $department)
                            ->orWhere('mst_department_id_1', $department)
                            ->orWhere('mst_department_id_2', $department);
                    }
                });
                if ($position) $query->where(function ($query) use ($position) {
                    $query->orWhere('mst_position_id', $position)
                        ->orWhere('mst_position_id_1', $position)
                        ->orWhere('mst_position_id_2', $position);
                });
                // PAC_5-1599 End
            } else {
                if ($department) {
                    if ($departmentChild) {
                        $query->whereIn('mst_department_id', $departmentIds);
                    } else {
                        $query->where('mst_department_id', $department);
                    }
                }
                if ($position) $query->where('mst_position_id', $position);
            }
        })
        ->whereRaw(implode(" AND ", $where), $where_arg)
        ->orderBy($_orderBy,$orderDir);

        if(count($searchData)) {
            $users = $userQuery->whereHas('timeCards', function($q) use($searchData) {
                $q->whereBetween('punched_at', [$searchData['startTime'], $searchData['endTime']]);
            })->paginate($limit)->appends(request()->except('_token'));
        } else {
            $users = $userQuery->paginate($limit)->appends(request()->except('_token'));
        }

        // get stamp
        if(count($users)){
            list($users, $usersByID) = $this->_processCountStamp($users);
        }
        return $users;
    }

    protected function _getUserOrderStamp($where, $where_arg, $orderBy, $orderDir, $limit, $company, $searchData = []){
        $department = request('department','');
        $position   = request('position','');
        $departmentChild = request('departmentChild','');
    
        $departmentIds = [];
        if ($departmentChild && $department) {
            $departmentList = DB::table('mst_department')
                ->select('id', 'parent_id')
                ->where('mst_company_id', $company->id)
                ->where('state', 1)
                ->get()
                ->toArray();
            $departmentIds = [];
            DepartmentUtils::getDepartmentChildIds($departmentList, $department, $departmentIds);
        }
        // PAC_5-2098 Start
        $users = $this->whereHas('info', function (Builder $query) use ($department, $position, $company, $departmentChild, $departmentIds) {
            if ($company->multiple_department_position_flg === 1) {
                // PAC_5-1599 追加部署と役職 Start
                if ($department) $query->where(function ($query) use ($department, $departmentChild, $departmentIds) {
                    if ($departmentChild) {
                        $query->orWhereIn('mst_department_id', $departmentIds)
                            ->orWhereIn('mst_department_id_1', $departmentIds)
                            ->orWhereIn('mst_department_id_2', $departmentIds);
                    } else {
                        $query->orWhere('mst_department_id', $department)
                            ->orWhere('mst_department_id_1', $department)
                            ->orWhere('mst_department_id_2', $department);
                    }
                });
                if ($position) $query->where(function ($query) use ($position) {
                    $query->orWhere('mst_position_id', $position)
                        ->orWhere('mst_position_id_1', $position)
                        ->orWhere('mst_position_id_2', $position);
                });
                // PAC_5-1599 End
            } else {
                if ($department) {
                    if ($departmentChild) {
                        $query->whereIn('mst_department_id', $departmentIds);
                    } else {
                        $query->where('mst_department_id', $department);
                    }
                }
                if ($position) $query->where('mst_position_id', $position);
            }
        })
        // PAC_5-2098 End
        ->whereRaw(implode(" AND ", $where), $where_arg)->select('id')->get();

        $totalUser = count($users);
        // get stamp
        if($totalUser){
            list($users, $usersByID) = $this->_processCountStamp($users);

            // sort
            if(strtolower($orderDir) == 'asc')
                    $users = $users->sortBy($orderBy);
            else    $users =  $users->sortByDesc($orderBy);

            // slice
            $page   = intval(request('page'));
            $page   = $page > 0?$page:1;
            $start  = ($page - 1) * $limit;
            $users  = $users->slice($start, $limit);

            $listUserID = [];
            foreach($users as $user){
                $listUserID[] = $user->id;
            }

            // get info
            // PAC_5-2098 Start
            $users = $this->whereHas('info', function (Builder $query) use ($department, $position, $company, $departmentChild, $departmentIds) {
                if ($company->multiple_department_position_flg === 1) {
                    // PAC_5-1599 追加部署と役職 Start
                    if ($department) $query->where(function ($query) use ($department, $departmentChild, $departmentIds) {
                        if ($departmentChild) {
                            $query->orWhereIn('mst_department_id', $departmentIds)
                                ->orWhereIn('mst_department_id_1', $departmentIds)
                                ->orWhereIn('mst_department_id_2', $departmentIds);
                        } else {
                            $query->orWhere('mst_department_id', $department)
                                ->orWhere('mst_department_id_1', $department)
                                ->orWhere('mst_department_id_2', $department);
                        }
                    });
                    if ($position) $query->where(function ($query) use ($position) {
                        $query->orWhere('mst_position_id', $position)
                            ->orWhere('mst_position_id_1', $position)
                            ->orWhere('mst_position_id_2', $position);
                    });
                    // PAC_5-1599 End
                } else {
                    if ($department) {
                        if ($departmentChild) {
                            $query->whereIn('mst_department_id', $departmentIds);
                        } else {
                            $query->where('mst_department_id', $department);
                        }
                    }
                    if ($position) $query->where('mst_position_id', $position);
                }
            })
            // PAC_5-2098 End
            ->whereIn('id', $listUserID)->get();

            // assign value from usersByID to users
            foreach($users as $user){
                $user->stampCommon      = $usersByID[$user->id]['stampCommon'];
                $user->stampName        = $usersByID[$user->id]['stampName'];
                $user->stampDate        = $usersByID[$user->id]['stampDate'];
                $user->stampConvenient  = $usersByID[$user->id]['stampConvenient'];
            }
            if(strtolower($orderDir) == 'asc')
                    $users = $users->sortBy($orderBy);
            else    $users =  $users->sortByDesc($orderBy);

            // make paginate
            $users = new LengthAwarePaginator($users, $totalUser,$limit, request('page'), [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]);
        }
        return $users;
    }

    protected function _processCountStamp($users){
        $listUserID = [];
        $usersByID  = [];
        foreach($users as $user){
            $listUserID[] = $user->id;
            $user->stampCommon  = 0;
            $user->stampName    = 0;
            $user->stampDate    = 0;
            $user->stampConvenient    = 0;
            $usersByID[$user->id] = $user->toArray();
        }
        // get listStampAssign for all user in list
        $listStampAssign = AssignStamp::whereIn('mst_user_id', $listUserID)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->select('mst_user_id','stamp_id', 'stamp_flg')->get()->toArray();
        $listStampMasterID = [];
        foreach($listStampAssign as $stampAssign){
            $_user_id       = $stampAssign['mst_user_id'];
            $_stamp_id      = $stampAssign['stamp_id'];
            $_stamp_flg     = $stampAssign['stamp_flg'];
            if($_stamp_flg == 0){
                $listStampMasterID[] = $_stamp_id;
            }
            if($_stamp_flg == 1){
                // company stamp
                $usersByID[$_user_id]['stampCommon'] ++;
            }
            if($_stamp_flg == 3){
                $usersByID[$_user_id]['stampConvenient'] ++;
            }
            $usersByID[$_user_id]['stampsAssign'][$_stamp_flg][] = $_stamp_id;
        }
        // get listStampsMaster for all user in list
        $listStampsMaster = Stamp::whereIn('id', $listStampMasterID)
                        ->select('id','stamp_division')->get()->pluck('stamp_division', 'id')->toArray();

        // process
        foreach($usersByID as $id => &$_user){
            if(!isset($_user['stampsAssign'])) continue;

            $stampsAssign = $_user['stampsAssign'];
            if(isset($stampsAssign[0]) AND count($stampsAssign[0])){
                // master stamp
                foreach($stampsAssign[0] as $_stamp_id){
                    if($listStampsMaster[$_stamp_id] == 0) $_user['stampName'] ++;
                    else $_user['stampDate'] ++;
                }
            }

            if(isset($stampsAssign[2]) AND count($stampsAssign[2])){
                // department stamp
                foreach($stampsAssign[2] as $_stamp_id){
                    $_user['stampDate'] ++;
                }
            }
        }

        // assign value from usersByID to users
        foreach($users as $user){
            $user->stampCommon      = $usersByID[$user->id]['stampCommon'];
            $user->stampName        = $usersByID[$user->id]['stampName'];
            $user->stampDate        = $usersByID[$user->id]['stampDate'];
            $user->stampConvenient  = $usersByID[$user->id]['stampConvenient'];
        }
        return [$users, $usersByID];
    }

    public function timeCards()
    {
        return $this->hasMany('App\Models\NewTimeCard', 'mst_user_id');
    }

    public function getUsersByDepartments($mst_company_id)
    {
        $departments = \Illuminate\Support\Facades\DB::table('mst_department')
            ->where('mst_company_id', $mst_company_id)
            ->where('state', 1)
            ->select(['id', 'parent_id as pId', 'department_name as name', DB::raw("'./images/folder.svg' as icon")])
            ->get();
        $department_id_max = $departments->max('id');
        $no_department_item = new \stdClass();
        $no_department_item->id = $department_id_max;
        $no_department_item->pId = 0;
        $no_department_item->name = 'グループなし';
        $no_department_item->icon = './images/folder.svg';
        $departments[] = $no_department_item;
        $users = DB::table('mst_user as U')
            ->join('mst_user_info as I', 'U.id', 'I.mst_user_id')
            ->where('mst_company_id', $mst_company_id)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->where('option_flg', AppUtils::USER_NORMAL)
            ->selectRaw('CONCAT(family_name," ",given_name,"    ",email) as name, mst_department_id, mst_department_id_1, mst_department_id_2,email')
            ->get()
            ->toArray();
        foreach ($users as $user) {
            $no_department = true;
            $user_departments = [$user->mst_department_id, $user->mst_department_id_1, $user->mst_department_id_2];
            foreach ($user_departments as $user_department) {
                if ($user_department) {
                    $department = $departments->firstWhere('id', $user_department);
                    $no_department = false;
                    $user_department_item = new \stdClass();
                    $department_id_max++;
                    $user_department_item->id = $department_id_max;
                    $user_department_item->pId = $department->id;
                    $user_department_item->name = $user->name;
                    $user_department_item->type = true;
                    $user_department_item->email = $user->email;
                    $departments[] = $user_department_item;
                }
            }
            // グループなし
            if ($no_department) {
                $user_department_item = new \stdClass();
                $department_id_max++;
                $user_department_item->id = $department_id_max;
                $user_department_item->pId = $no_department_item->id;
                $user_department_item->name = $user->name;
                $user_department_item->type = true;
                $user_department_item->email = $user->email;
                $departments[] = $user_department_item;
            }
        }

        return $departments;
    }

    /**
     * 1ヶ月以内にユーザーが使用するファイル容量
     * @param $month
     * @param $doc_start_time
     * @param $doc_end_time
     * @return Collection
     */
    public function getUsageSituation($month, $doc_start_time, $doc_end_time): Collection
    {
        return DB::table('mst_user as mu')
            ->join("circular_document$month as doc",'doc.create_user_id','mu.id')
            ->join("circular$month as c",'c.id','doc.circular_id')
            ->whereNotIn('c.circular_status',array_keys(AppUtils::CIRCULAR_SAVED_STATUS))
            ->where('mu.state_flg',AppUtils::STATE_VALID)
            ->where('doc.origin_edition_flg',config('app.pac_contract_app'))
            ->where('doc.origin_env_flg',config('app.pac_app_env'))
            ->where('doc.origin_server_flg',config('app.pac_contract_server'))
            ->where('doc.create_at','>=',$doc_start_time)
            ->where('doc.create_at','<',$doc_end_time)
            ->groupBy('mu.id','mu.email')
            ->orderBy('datasize','desc')
            ->selectRaw('mu.id as mst_user_id,mu.email,SUM(doc.file_size) as datasize,mu.mst_company_id')
            ->get();
    }


}
