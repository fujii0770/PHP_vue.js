<?php

namespace App\Http\Controllers\Admin;

use App\Http\Utils\DepartmentUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Position;
use App\Models\Department;
use App\Models\AssignStamp;
use App\Models\Company;
use App\Http\Utils\AppUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;

class UserAssignStampController extends AdminController
{

    private $model;
    private $userInfo;
    private $department;
    private $position;
    private $assignStamp;
    private $company;

    public function __construct(User $model, UserInfo $userInfo, Department $department, Position $position, 
        AssignStamp $assignStamp, Company $company)
    {
        parent::__construct();
        $this->model = $model;
        $this->userInfo = $userInfo;
        $this->department = $department;
        $this->position = $position;
        $this->assignStamp = $assignStamp;
        $this->company = $company;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }
        
        $action = $request->get('action','');
        
        // get list user
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $users = [];
        if(!array_search($limit, array_merge(config('app.page_list_limit'),[20]))){ 
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        Log::debug('mst_company_id:'.$user->mst_company_id.' action:'.$action.' limit:'.$limit);

        if($action != ""){
            $users = $this->model->getList($user->mst_company_id,AppUtils::USER_NORMAL, true, $limit);
            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        }
        $company = $this->company->where('id', $user->mst_company_id)->first();
        $email_domain_company = [];
        foreach(explode("\r\n", $company->domain) as $domain){
            $email_domain_company[$domain] = ltrim($domain,"@");
        }
        
        $this->assign('email_domain_company', $email_domain_company);
        $this->assign('company', $company);
        $this->assign('users', $users);
        
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        // PAC_5-983 BEGIN
        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
        // PAC_5-983 END

        $this->assign('listDepartmentDetail', $listDepartmentDetail);
        $listPosition = $this->position
                ->select('id' , 'position_name as text' , 'position_name as sort_name')
                ->where('state',1)
                ->where('mst_company_id',$user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);

                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();

        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);
        
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE));

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->setMetaTitle("共通印割当");
         
        return $this->render('UserAssignStamp.index');
    }
 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    public function delete($id)
    {
         
    }
}
