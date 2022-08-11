<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\StampUtils;
use App\Models\Company;
use App\Models\CompanyStampConvenient;
use App\Models\CompanyStampGroups;
use App\Models\StampConvenientDivision;
use Carbon\Carbon;
use Defuse\Crypto\File;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\CompanyStamp;
use App\Models\CompanyStampGroupsAdmin;
use Auth;
use Illuminate\Support\Str;

class CompanyStampController extends AdminController
{

    private $model;

    private $model_type;

    private $modelPermission;

    private $companyStampConvenient;

    public function __construct(CompanyStamp $model,CompanyStampConvenient $companyStampConvenient)
    {
        parent::__construct();
        $this->model = $model;
        $this->companyStampConvenient = $companyStampConvenient;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        $listGroup = CompanyStampGroups::
        join('mst_company_stamp_groups_admin',function($query)use($user){
            $query->on('mst_company_stamp_groups.id','mst_company_stamp_groups_admin.group_id')
                ->where('mst_company_stamp_groups_admin.mst_admin_id',$user->id);
        })
            ->where('mst_company_id','=', $user->mst_company_id)
            ->select(['mst_company_stamp_groups.id as id','mst_company_stamp_groups.group_name as group_name'])
            ->get();

        // mst_company.contract_edition取得、共通印申請書ダウンロードリンク活性化制御用
        $company = Company::where('id', $user->mst_company_id)->first();

        $divisionList = StampConvenientDivision::where('del_flg',0)->get();
        // PAC_5-2332 add S
        $one='';
        foreach ($divisionList as $key=>$val){
            if($val->id==4){
                $one=$val;
                unset($divisionList[$key]);
                $divisionList[]=$one;
            }
        }
        // PAC_5-2332 E
        $this->assign('divisionList', $divisionList);
        $this->assign('contract_edition', $company->contract_edition);
        $this->assign('listGroup', $listGroup);
        $this->assign('convenient_flg', $company->convenient_flg);
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_COMMON_MARK_SETTING_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_COMMON_MARK_SETTING_UPDATE));

        // PAC_5-2018 START
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        //$this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        // PAC_5-2018 END

        // PAC_5-1325 色の選択用のライブラリを追加
        $this->addScript('jscolor', asset("/js/libs/jscolor.js"));

        $this->setMetaTitle("共通印設定");
        return $this->render('GlobalSetting.CompanyStamp.index');
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function searchConvenientStamps(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        if(!$this->addCompanyConvenientStamp()){
            response()->json(['status' => false]);
        }
        $name = $request->get('name','');
        $stamp_division = $request->get('stamp_division','');
        $is_assigned = $request->get('is_assigned', []);
        $per_page = $request->get('limit') ? $request->get('limit') : 10;

        $query = $this->companyStampConvenient
            ->join('mst_stamp_convenient',function ($query) use ($name,$stamp_division){
                $query->on('mst_stamp_convenient.id','mst_company_stamp_convenient.mst_stamp_convenient_id')
                    ->where('mst_stamp_convenient.del_flg','=',0);
                if($name){
                    $query->where('mst_stamp_convenient.stamp_name','like','%'.$name.'%');
                }
                if($stamp_division){
                    $query->where('mst_stamp_convenient.stamp_division',$stamp_division);
                }
            });
        $items = $query->where('mst_company_stamp_convenient.del_flg', 0)
            ->where('mst_company_stamp_convenient.mst_company_id', $user->mst_company_id)
            ->whereNotIn('mst_company_stamp_convenient.id', $is_assigned)
            ->select('mst_company_stamp_convenient.id','mst_stamp_convenient.stamp_name','mst_stamp_convenient.stamp_image','mst_stamp_convenient.stamp_date_flg','mst_stamp_convenient.date_color',
                'mst_stamp_convenient.date_width','mst_stamp_convenient.date_height','mst_stamp_convenient.date_x','mst_stamp_convenient.date_y')
            ->paginate($per_page);
        foreach ($items as $item){
            if ($item->stamp_date_flg != 0 ){
                $item->stamp_image = StampUtils::companyStampWithDate($item,$user->mst_company_id);
            }
        }

        return response()->json(['status' => true, 'items' => $items]);
    }

    protected function addCompanyConvenientStamp(){
        $user = \Auth::user();

        $stampConvenientIds = DB::table('mst_stamp_convenient')
            ->leftjoin('mst_company_stamp_convenient',function ($query) use($user){
                $query->on('mst_stamp_convenient.id','mst_company_stamp_convenient.mst_stamp_convenient_id')
                    ->where('mst_company_stamp_convenient.del_flg',0)
                    ->where('mst_company_stamp_convenient.mst_company_id',$user->mst_company_id);
            })
            ->where('mst_stamp_convenient.del_flg',0)
            ->wherenull('mst_company_stamp_convenient.id')
            ->select('mst_stamp_convenient.id')
            ->pluck('mst_stamp_convenient.id')->toArray();

        if(count($stampConvenientIds) > 0){
            DB::beginTransaction();
            try{
                foreach ($stampConvenientIds as $key => $id){
                    $item = new $this->companyStampConvenient;
                    $item->mst_stamp_convenient_id = $id;
                    $item->mst_company_id = $user->mst_company_id;
                    $item->del_flg = 0;
                    $item->create_user = $user->getFullName();
                    $item->update_user = $user->getFullName();
                    $item->save();
                    $item->serial = AppUtils::generateStampSerial(AppUtils::STAMP_FLG_CONVENIENT, $item->id);
                    $item->save();
                }

                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                Log::error($e->getMessage().$e->getTraceAsString());
                return false ;
            }
        }


        return true;
    }

    public function search(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW)){
            $this->raiseWarning(__('message.warning.not_permission_access'));
            return redirect()->route('home');
        }

        $emptyName = $request->get('empty_name',false);
        $name = $request->get('name','');
        $group_id = $request->get('group_id','');
        $where = ['mst_company_id = '.$user->mst_company_id];
        $where_arg = [];
        $per_page = $request->get('limit') ? $request->get('limit') : 10;
        $id_assigned = $request->get('id_assigned', []);
        if ($emptyName){
            $where[] = '(stamp_name is null or trim(stamp_name) = \'\')';
        }else{
            if($name) {
                $where[] = 'stamp_name like ?';
                $where_arg[] = "%$name%";
            }
            //PAC_5-2658 Start
            /*}else{
                $where[] = 'stamp_name <> ?';
                $where_arg[] = "名称未設定";
            }*/
            //PAC_5-2658 End
        }

        if (\App\Http\Utils\AppUtils::getStampGroup()){

            if($group_id=="99"){
                // すべて
            $items = $this->model
                ->whereRaw(implode(" AND ", $where), $where_arg)->where('del_flg', 0)->whereNotIn('id', $id_assigned)->paginate($per_page);
            }elseif($group_id=="0"){
                // グループなし
                $where[] = 'mst_company_stamp_groups_relation.state is null';

            }else{
                // 指定したグループ
                $where[] = 'mst_company_stamp_groups_relation.group_id = ?';
                $where_arg[] = $group_id;

            }
        }

        $admin_groups = CompanyStampGroupsAdmin::where('mst_admin_id',$user->id)->select('group_id');

        $items = $this->model
            ->leftjoin('mst_company_stamp_groups_relation',function ($query){
                $query->on('mst_company_stamp_groups_relation.stamp_id','mst_company_stamp.id')
                    ->where('mst_company_stamp_groups_relation.state',1);
            })
            ->where(function ($query) use ($admin_groups){
                $query->wherenull('mst_company_stamp_groups_relation.group_id')
                    ->orWherein('mst_company_stamp_groups_relation.group_id',$admin_groups);
            })
            //共通シールは分割されていますか
            ->whereRaw(implode(" AND ", $where), $where_arg)->where('del_flg', 0)->whereNotIn('id',$id_assigned)->paginate($per_page);
        //PAC_5-620BEGIN
        //管理者で共通印を確認すると日付が反映されていない（利用者で見ると反映されている）
        foreach ($items as $ky => $item){
            if ($item->stamp_division !== 0){
                // 共通スタンプに日付が表示されます
                $item->stamp_image = StampUtils::companyStampWithDate($item, $user->mst_company_id);
            }
        }
        //PAC_5-620END
        return response()->json(['status' => true, 'items' => $items]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $id = intval($request->get('id'));

        $item = $this->model->find($id);
        if(!$item){
            return response()->json(['status' => false, 'message' => [__('message.false.save_company_stamp')] ]);
        }

        if($item->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item->stamp_name = trim($request->get('stamp_name'));

        $validator = Validator::make($item->toArray(), $this->model->rules($id));

        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        DB::beginTransaction();
        try{
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['status' => false, 'message' => [__('message.false.save_company_stamp')] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.save_company_stamp')]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();

        $item = $this->model->select('id', 'email', 'state_flg', 'phone_number', 'given_name','family_name', 'department_name','mst_company_id')
                            ->where('mst_company_id', $user->mst_company_id)
                            ->find($id);
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'info' => $item]);
    }

    public function destroy($id)
    {
        $user = \Auth::user();

        if(!$user->can(PermissionUtils::PERMISSION_COMMON_MARK_SETTING_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item = $this->model->find($id);
        if(!$item){
            return response()->json(['status' => false, 'message' => [__('message.false.delete_company_stamp')] ]);
        }

        if($item->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        DB::beginTransaction();
        try{
            DB::table('mst_assign_stamp')
                ->where('stamp_id','=',$id)
                ->where('stamp_flg','=',AppUtils::STAMP_FLG_COMPANY)
                ->update(['state_flg' => AppUtils::STATE_DELETE,
                    'delete_at' => Carbon::now(),
                    'update_at' =>  Carbon::now(),
                    'update_user' =>  $user->email,
                    ]);
            $item->del_flg = 1;
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['status' => false, 'message' => [__('message.false.delete_company_stamp')] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.delete_company_stamp')]]);
    }

    /**
     * 共通印申請書ダウンロード
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function download(Request $request)
    {
        try {
            $texts = [];
            $files = [];
            $fontFamily = 'MS Mincho';
            $unique =CommonUtils::getPdfNumberFirst();
            $unique .= strtoupper(substr(str_replace('-', '', Str::uuid()->toString()), 0, 12));

            $item = DB::table('mst_company_stamp_order_history')->where('create_at', '>=', Carbon::today())->orderBy('create_at', 'desc')->first();
            if ($item) {
                $number = (int)(substr($item->pdf_number, -4)) + 1;
                $unique .= sprintf("%04d", $number);
            } else {
                $unique .= '0001';
            }
            //PDF番号
            $today = Carbon::today()->format('Ymd')." code:";
            array_push($texts, ['page' => 1,
                'text' => "$today$unique",
                'x_axis' => 120,
                'y_axis' => 28,
                'fontSize' => 4,
                'fontFamily' => $fontFamily]);
            //会社名
            $user = \Auth::user();
            $company = Company::find($user->mst_company_id);

            //PAC_5-1411 トライアル企業の場合申請文書ダウンロード不可
            if($company->contract_edition == AppUtils::CONTRACT_EDITION_TRIAL){
                return response()->json(['status' => false, 'message' => 'トライアル企業は利用できません', 'data' => null], 400);
            }

            array_push($texts, ['page' => 1,
                'text' => $company ? $company->company_name : '',
                'x_axis' => 39,
                'y_axis' => 46,
                'fontSize' => 5,
                'fontFamily' => $fontFamily]);
            //部署名
            array_push($texts, ['page' => 1,
                'text' => $user ? $user->department_name : '',
                'x_axis' => 39,
                'y_axis' => 57,
                'fontSize' => 5,
                'fontFamily' => $fontFamily]);
            //管理者名
            array_push($texts, ['page' => 1,
                'text' => $user ? "$user->family_name $user->given_name" : "",
                'x_axis' => 39,
                'y_axis' => 66,
                'fontSize' => 5,
                'fontFamily' => 'MS Mincho']);
            //TEL
            array_push($texts, ['page' => 1,
                'text' => $user ? "$user->phone_number" : "",
                'x_axis' => 59,
                'y_axis' => 76,
                'fontSize' => 4,
                'fontFamily' => $fontFamily]);
            //Mail
            array_push($texts, ['page' => 1,
                'text' => $user ? "$user->email" : "",
                'x_axis' => 122,
                'y_axis' => 76,
                'fontSize' => 4,
                'fontFamily' => $fontFamily]);

            $pdf = base64_encode(file_get_contents(public_path() . '/filedownload/dstmp_reg_application.pdf'));

            array_push($files, ['pdf_data' => $pdf, 'texts' => $texts]);

            $stampClient = AppUtils::getStampApiClient();
            $result = $stampClient->post("signatureAndImpress", [
                RequestOptions::JSON => [
                    'data' => $files,
                ]
            ]);
            $resData = json_decode((string)$result->getBody());
            if ($result->getStatusCode() == 200) {
                if ($resData && $resData->data) {
                    DB::table('mst_company_stamp_order_history')->insert([
                        'pdf_number' => $unique,
                        'mst_admin_id' => $user->id,
                        'create_at' => Carbon::now(),
                    ]);
                    return response()->json(['status' => true, 'fileName' => 'dstmp_reg_application.pdf',
                        'file_data' => $resData->data[0]->pdf_data,
                        'message' => ['共通印申請書ダウンロード処理に成功しました。']]);
                }
            }
            Log::error('共通印申請書ダウンロード処理に失敗しました: ' . $result->getBody());
            return response()->json(['status' => false, 'message' => '共通印申請書ダウンロード処理に失敗しました', 'data' => null], 500);
        } catch (\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => '共通印申請書ダウンロード処理に失敗しました', 'data' => null], 500);
        }
    }

    /**
     * PAC_5-2018 START
     * find current company global stamp use users info
     * @param $intCompanyStampID
     * @param Request $request
     * @return mixed
     */
    public function getUsersGlobalCompanyStamp(Request $request){
        $user = \Auth::user();

        $intCompanyStampID = $request->input("company_stamp_id");
        $intLimit = $request->input("limit",AppUtils::FIND_CURRENT_COMPANY_STAMP_USE_USERS_LIMIT);

        $strOrderBy = $request->input("orderBy",'mst_user.email');
        $strOrderDir = $request->input("orderDir","asc");

        $arrOrderBy = [
            'email' => 'mst_user.email',
            'given_name' => 'mst_user.family_name',
            "department_name" => 'mst_department.department_name',
        ];
        $arrOrderDir = ['asc','desc'];

        $strOrderBy = $arrOrderBy[$strOrderBy] ?? $arrOrderBy['email'];
        $strOrderDir = in_array($strOrderDir,$arrOrderDir)? $strOrderDir : "asc";

        if(empty($user) || !$intCompanyStampID){
            return response()->json(['status' => true, 'data' => []]);
        }
        $arrUserStampInfo = DB::table("mst_assign_stamp")->join("mst_company_stamp",function($join) use ($user){
            $join->on("mst_company_stamp.id",'=','mst_assign_stamp.stamp_id');
            $join->where("mst_assign_stamp.stamp_flg",'=',AppUtils::STAMP_FLG_COMPANY);
            $join->where("mst_company_stamp.mst_company_id",'=',$user->mst_company_id);
            $join->where("mst_company_stamp.del_flg",0);
            $join->where("mst_assign_stamp.state_flg",'=',1);
        })->join("mst_user_info",function($join){
            $join->on("mst_assign_stamp.mst_user_id",'=','mst_user_info.mst_user_id');
        })->join("mst_user",function($join){
            $join->on("mst_user.id",'=','mst_user_info.mst_user_id');
            $join->where("mst_user.state_flg",AppUtils::STATE_VALID);
        })->leftJoin("mst_department",function($join){
            $join->on("mst_user_info.mst_department_id",'=','mst_department.id');
        })
        ->where("mst_company_stamp.del_flg",0)
        ->where("mst_company_stamp.id",$intCompanyStampID)
        ->select(
            "mst_assign_stamp.*","mst_department.department_name as departmentName",
            DB::raw("CONCAT(family_name,' ',given_name) as fullName"),
            "mst_user_info.mst_department_id","mst_user.family_name","mst_user.given_name",
            'mst_user.email',"mst_user_info.phone_number"
        )->orderBy($strOrderBy,$strOrderDir);
        if($arrOrderBy == $arrOrderBy['given_name']){
            $arrUserStampInfo->orderBy("mst_user.given_name",$strOrderDir);
        }

        $arrUserStampInfo = $arrUserStampInfo->paginate($intLimit);

        if($arrUserStampInfo->isEmpty()){
            return response()->json(['status' => true, 'data' => []]);
        }
        return response()->json(['status' => true, 'data' => $arrUserStampInfo]);
    }
}
