<?php

namespace App\Http\Controllers\Shachihata;

use App\CompanyAdmin;
use App\Http\Controllers\AdminController;
use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppSettingConstraint;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\SpecialAppApiUtils;
use App\Http\Utils\StampUtils;
use App\Models\Admin;
use App\Models\AssignStamp;
use App\Models\Authority;
use App\Models\Company;
use App\Models\CompanyStamp;
use App\Models\CompanyStampGroupsRelation;
use App\Models\Constraint;
use App\Models\DepartmentStamp;
use App\Models\SpecialSiteReceiveSendAvailableState;
use App\Models\User;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Session;
use Storage;
use App\Http\Utils\MailUtils;
use Forrest;
use Intervention\Image\Facades\Image;

class CompaniesController extends AdminController
{

    private $model;
    private $modelAuthority;

    private $constraint;
    private $companyAdmin;
    private $companyStamp;
    private $assignStamp;
    private $specialSiteReceiveSendAvailableState;

    public function __construct(Company $model, Constraint $constraint,
        CompanyAdmin $companyAdmin, CompanyStamp $companyStamp, AssignStamp $assignStamp, Authority $modelAuthority, SpecialSiteReceiveSendAvailableState $specialSiteReceiveSendAvailableState)
    {
        parent::__construct();
        $this->model = $model;
        $this->constraint = $constraint;
        $this->companyAdmin = $companyAdmin;
        $this->companyStamp = $companyStamp;
        $this->assignStamp  = $assignStamp;
        $this->modelAuthority  = $modelAuthority;
        $this->specialSiteReceiveSendAvailableState = $specialSiteReceiveSendAvailableState;
    }

    /**
     * Display a listing of the Company
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = \Auth::user();
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $action = $request->get('action', '');
        $items = [];
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $time_regular_at = Carbon::now()->format('Y-m-d');
        if (!array_search($limit, config('app.page_list_limit'))) {
            $limit = config('app.page_limit');
        }

        $name = $request->get('name');
        $domain = $request->get('domain');
        $number = $request->get('number');
        $stringRemarkMessage = $request->get('remark_message');

        $family_name=$request->get('family_name');
        $where = ['1=1'];
        $where_arg = [];
        if ($name) {
            $where[] = 'company_name like ?';
            $where_arg[] = "%$name%";
        }

        if ($domain) {
            $where[] = 'domain like ?';
            $where_arg[] = "%$domain%";
        }

        if ($number) {
            $where[] = 'domain like ?';
            $where_arg[] = "%$domain%";
        }
        if($stringRemarkMessage){
            $where[] = 'remark_message like ?';
            $stringRemarkMessage = str_replace(["%","_"],["\%",'\_'],$stringRemarkMessage);
            $where_arg[] = "%$stringRemarkMessage%";
        }
        if ($action != "") {
            $items = $this->model
                ->orderBy($orderBy, $orderDir)
                ->where('contract_edition_sample_flg',AppUtils::EDITION_SAMPLE_F)//サンプルフラグ企業取得
                ->where(function ($query) use ($number) {
                    if ($number) {
                        $query->whereHas('pdfNumbers', function ($query) use ($number) {
                            if ($number) {
                                $query->where('mst_company_stamp_order_history.pdf_number', 'like', "%$number%");
                            }
                        });
                    }
                })
                ->where(function ($query) use ($family_name) {
                    if($family_name){
                        $query->whereHas('companyAdmins', function ($query) use ($family_name) {
                            $query->whereRaw("CONCAT_WS('', mst_admin.family_name, mst_admin.given_name) = ? ", [$family_name]);
                            $query->orWhere("mst_admin.email",$family_name);
                        });
                    }
                })
                ->withCount(['companyStamps',
                        'assignedStamps' => function (Builder $query) {
                            $query->whereIn('mst_assign_stamp.state_flg', [AppUtils::STATE_VALID, AppUtils::STATE_WAIT_ACTIVE]);
                            $query->where('mst_assign_stamp.stamp_flg','!=',AppUtils::STAMP_FLG_CONVENIENT);
                            $query->where('mst_user.state_flg', AppUtils::STATE_VALID);
                            $query->where('mst_user.option_flg',AppUtils::USER_NORMAL);
                        }]
                )
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->paginate($limit)->appends(request()->input());

            $assignedConvenientStampsCount = DB::table('mst_assign_stamp')
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
                ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id')
                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                ->where('mst_assign_stamp.state_flg', AppUtils::STATE_VALID)
                ->where('mst_user.state_flg', AppUtils::STATE_VALID)
                ->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                ->get()
                ->toArray();

            foreach ($items as $item){
                $item['assigned_convenient_stamps_count'] = count(array_filter($assignedConvenientStampsCount,function ($s) use($item){
                    return $item['id'] == $s->mst_company_id;
                    }));
            }
        }

        //地域リストの取得
        $regionList = DB::table('mst_region')
            ->select(DB::raw('region_name,region_id'))
            ->pluck('region_name','region_id')->toArray();

        //企業無害化ファイル要求上限
        foreach ($items as $item){
            $item['sanitize_request_limit'] = Company::getSanitizingLimit($item->id);
        }
        //無害化回線プルダウン
        $sanitizingList = DB::table('mst_sanitizing_line')
            ->select(DB::raw('sanitizing_line_name,id'))
            ->pluck('sanitizing_line_name','id')
            ->toArray();
        $sanitizingLimit = DB::table('mst_sanitizing_line')
            ->select(DB::raw('id,sanitize_request_limit'))
            ->get()
            ->toArray();

        //契約Edition一覧
        $mst_contract_edition = DB::table('mst_contract_edition')
            ->join('mst_company', 'mst_company.edition_id','mst_contract_edition.id')
            ->where('mst_contract_edition.state_flg', AppUtils::EDITION_T_STATE)
            ->where('mst_company.contract_edition_sample_flg',AppUtils::EDITION_SAMPLE_T)
            ->pluck('mst_contract_edition.contract_edition_name','mst_company.contract_edition')->toArray();


        //企業EditionInfo
        $contract_edition_info = DB::table('mst_contract_edition as me')
            ->select('me.contract_edition_name','info.department_stamp_flg','info.template_route_flg','info.rotate_angle_flg','info.phone_app_flg','info.attachment_flg','info.portal_flg','info.contract_edition'
                ,'info.convenient_flg','info.usage_flg','info.convenient_upper_limit','info.default_stamp_flg','info.confidential_flg','info.esigned_flg','info.ip_restriction_flg','info.signature_flg','info.permit_unregistered_ip_flg'
                ,'info.stamp_flg','info.repage_preview_flg','info.timestamps_count','info.box_enabled','info.time_stamp_issuing_count','info.mfa_flg','info.long_term_storage_flg','info.template_flg','info.long_term_storage_option_flg','info.template_search_flg'
                ,'info.long_term_folder_flg','info.max_usable_capacity','info.template_csv_flg','info.hr_flg','info.template_edit_flg','info.multiple_department_position_flg','info.option_user_flg','info.user_plan_flg','info.receive_user_flg','info.template_approval_route_flg'
                ,'info.skip_flg','info.form_user_flg','info.frm_srv_flg','info.bizcard_flg','info.local_stamp_flg','info.with_box_flg','info.dispatch_flg','info.attendance_system_flg','info.circular_list_csv','info.is_together_send','info.enable_any_address_flg'
                ,'info.sanitizing_flg','info.enable_email','info.email_format','info.received_only_flg','info.pdf_annotation_flg','info.addressbook_only_flag','info.view_notification_email_flg','info.updated_notification_email_flg','info.enable_email_thumbnail'
                ,'me.board_flg','me.scheduler_flg','me.scheduler_limit_flg','me.scheduler_buy_count','me.caldav_flg','me.caldav_limit_flg','me.caldav_buy_count','me.google_flg','me.outlook_flg','me.apple_flg','me.file_mail_flg'
                ,'me.file_mail_flg','me.file_mail_limit_flg','me.file_mail_buy_count','me.file_mail_extend_flg','me.attendance_flg','me.attendance_limit_flg','me.attendance_buy_count','info.is_show_current_company_stamp','me.faq_board_flg','me.faq_board_limit_flg','me.faq_board_buy_count','me.faq_board_flg','me.shared_scheduler_flg'
                ,'me.to_do_list_flg','me.to_do_list_limit_flg','me.to_do_list_buy_count','me.address_list_flg','me.address_list_limit_flg','me.address_list_buy_count')
            ->join('mst_company as info','info.edition_id','me.id')
            ->where('me.state_flg', AppUtils::EDITION_T_STATE)
            ->where('info.contract_edition_sample_flg',AppUtils::EDITION_SAMPLE_T)
            ->orderBy('info.contract_edition')
            ->get()
            ->toArray();

        $this->assign('items', $items);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('regionList', $regionList);
        $this->assign('sanitizingList', $sanitizingList); //PAC_5-2912
        $this->assign('sanitizingLimit', $sanitizingLimit); //PAC_5-2912
        $this->assign('contract_editions', $mst_contract_edition);
        $this->assign('contract_edition_info', $contract_edition_info);
        $this->assign('time_regular_at', $time_regular_at);
        $this->assign('orderDir', strtolower($orderDir) == "asc" ? "desc" : "asc");
        $this->setMetaTitle("企業設定");

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        // PAC_5-1325 色の選択用のライブラリを追加
        $this->addScript('jscolor', asset("/js/libs/jscolor.js"));

        return $this->render('Companies.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = \Auth::user();

        $item = $this->model->find($id);
        if($item){

            if(!$item->specialSiteReceiveSendAvailableState){
                $this->addSpecial($item->id);
                $item = $this->model->find($id);
            }else{
                //地域リストの取得
                $regionNameList = DB::table('mst_region')
                    ->select(DB::raw('region_name,region_id'))
                    ->get();
                foreach ($regionNameList as $region) {
                    if ($item->specialSiteReceiveSendAvailableState->region_name == $region->region_name) {
                        $item->specialSiteReceiveSendAvailableState->region_name = json_encode($region->region_id);
                    }
                }
            }

            $item->constraint;
            if(!$item->constraint){
                $this->addConstrain($item->id);
                $item = $this->model->find($id);
            }

            $item->enable_any_address_flg_org = $item->enable_any_address_flg;
            $item->sanitize_request_limit = Company::getSanitizingLimit($id);
         }

         // 掲示板の設定を取得する
        $item['board_flg'] = $item['board_flg_org'] = 0;
        $item['scheduler_flg'] = $item['scheduler_flg_org'] = 0;
        $item['caldav_flg'] = $item['caldav_flg_org'] = 0;
        $item['file_mail_flg'] = $item['file_mail_flg_org'] = 0;
        $item['faq_board_flg'] = $item['faq_board_flg_org'] = 0;
        $item['attendance_flg'] = $item['attendance_flg_org'] = 0;
        $item['address_list_flg'] = $item['address_list_flg_org'] = 0;
        ApplicationAuthUtils::getCompanyAppSearch($id)->each(function ($setting) use (&$item) {
            switch ($setting->id) {
                case AppUtils::GW_APPLICATION_ID_BOARD:
                    $item['board_flg'] = $setting->is_auth;
                    $item['board_flg_org'] = $setting->is_auth;
                    break;
                case AppUtils::GW_APPLICATION_ID_FILE_MAIL:
                    $item['file_mail_flg'] = $setting->is_auth;
                    $item['file_mail_flg_org'] = $setting->is_auth;
                    $item["file_mail_limit_flg"] = $setting->is_infinite;
                    $item["file_mail_buy_count"] = $setting->purchase_count;
                    break;
                case AppUtils::GW_APPLICATION_ID_TIME_CARD:
                    $item['attendance_flg'] = $setting->is_auth;
                    $item['attendance_flg_org'] = $setting->is_auth;
                    $item["attendance_limit_flg"] = $setting->is_infinite;
                    $item["attendance_buy_count"] = $setting->purchase_count;
                    break;
                case AppUtils::GW_APPLICATION_ID_FAQ_BOARD:
                    $item['faq_board_flg'] = $setting->is_auth;
                    $item['faq_board_flg_org'] = $setting->is_auth;
                    $item["faq_board_limit_flg"] = $setting->is_infinite;
                    $item["faq_board_buy_count"] = $setting->purchase_count;
                    break;
                case AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND:
                    $item['file_mail_extend_flg'] = $setting->is_auth;
                    break;
                case AppUtils::GW_APPLICATION_ID_TO_DO_LIST:
                    $item['to_do_list_flg'] = $setting->is_auth;
                    $item['to_do_list_flg_org'] = $setting->is_auth;
                    $item['to_do_list_limit_flg'] = $setting->is_infinite;
                    $item['to_do_list_buy_count'] = $setting->purchase_count;
                    break;
                case AppUtils::GW_APPLICATION_ID_ADDRESS_LIST:
                    $item['address_list_flg'] = $setting->is_auth;
                    $item['address_list_flg_org'] = $setting->is_auth;
                    $item["address_list_limit_flg"] = $setting->is_infinite;
                    $item["address_list_buy_count"] = $setting->purchase_count;
                    break;
            }
        });

        //☆開始;
        $gw_update_flg = false;
        if(config('app.gw_use')==1 && config('app.gw_domain')){
            //アプリ企業マスタ参照(アプリ名,使用の有無)API呼び出し
            $settingCompanyIds = GwAppApiUtils::getCompanyAppSearch($user->email, $item->id);
            if ($settingCompanyIds){
                foreach ($settingCompanyIds as $settingCompanyId){
                    if ($settingCompanyId['id'] == AppUtils::GW_APPLICATION_ID_SCHEDULE){
                        $item['scheduler_flg'] = $settingCompanyId['isAuth'] ? 1 : 0;
                        $item['scheduler_flg_org'] = $settingCompanyId['isAuth'] ? 1 : 0;
                        $item["scheduler_limit_flg"] = $settingCompanyId['isInfinite'] ? 1 : 0;
                        $item["scheduler_buy_count"] = $settingCompanyId['purchaseCount'] ?: 0;
                    }
                    if ($settingCompanyId['id'] == AppUtils::GW_APPLICATION_ID_CALDAV){
                        $item["caldav_flg"] = $settingCompanyId['isAuth'] ? 1 : 0;
                        $item["caldav_flg_org"] = $settingCompanyId['isAuth'] ? 1 : 0;
                        $item["caldav_limit_flg"]  = $settingCompanyId['isInfinite'] ? 1 : 0;
                        $item["caldav_buy_count"]  = $settingCompanyId['purchaseCount'] ?: 0;
                    }
                    $item["google_flg"]    = isset($settingCompanyIds[3]['isAuth'])&&$settingCompanyIds[3]['isAuth'] ? 1 : 0;
                    $item["outlook_flg"]    = isset($settingCompanyIds[4]['isAuth'])&&$settingCompanyIds[4]['isAuth'] ? 1 : 0;
                    $item["apple_flg"]    = isset($settingCompanyIds[5]['isAuth'])&&$settingCompanyIds[5]['isAuth'] ? 1 : 0;
                    if ($settingCompanyId['id'] == AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE){
                        $item["shared_scheduler_flg"] = $settingCompanyId['isAuth'] ? 1 : 0;
                        $item["shared_scheduler_flg_org"] = $settingCompanyId['isAuth'] ? 1 : 0;
                }
                }
            }else{
                $item['scheduler_flg'] = 0;
                $item['scheduler_flg_org'] = 0;
                $item["scheduler_limit_flg"] = 0;
                $item["scheduler_buy_count"] = 0;
                $item["caldav_flg"] = 0;
                $item["caldav_flg_org"] = 0;
                $item["caldav_limit_flg"] = 0;
                $item["caldav_buy_count"] = 0;
                $item["google_flg"] = 0;
                $item["outlook_flg"] = 0;
                $item["apple_flg"] = 0;
                $item["shared_scheduler_flg"] =0;
                $item["shared_scheduler_flg_org"] =0;
            }

            //アプリ利用制限参照
            $settings_limit = GwAppApiUtils::getCompanyLimit($id);
            if ($settings_limit){
                $item['app_limit_id']                   = $settings_limit['app_limit_id'];
                $item['constraint']['maxBbsCount']      = $settings_limit['maxBbsCount'];
                $item['constraint']['maxScheduleCount'] = $settings_limit['maxScheduleCount'];
                $gw_update_flg = true;
            }else{
                $item['app_limit_id']                   = '';
                $item['constraint']['maxBbsCount']      = AppUtils::MAX_BBS_COUNT;
                $item['constraint']['maxScheduleCount'] = AppUtils::MAX_SCHEDULE_COUNT;
            }
            //☆終了
        }
        $item['trial_period_date'] = $item['contract_edition'] == 3 ? Carbon::parse($item['create_at'])->addDays($item['trial_time'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $item['regular_at'] = $item['contract_edition'] <> 3 ? Carbon::parse($item['regular_at'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $item['talk'] = DB::table('mst_chat')
            ->where('mst_company_id', $item->id)
            ->first();
        if (config("app.pac_app_env")){
            $item['receive_plan_flg'] = 0;
            $item['receive_plan_url'] = "";
        }
        if (!$gw_update_flg && config('app.gw_use')==1 && config('app.gw_domain')){
            Log::error('GWのデータを取得失敗しました。(会社ID：'.$id.')');
            return response()->json(['status' => true, 'item' => $item, 'message'=>[__('message.warning.gw_failed.company')]]);
        }else{
            return response()->json(['status' => true, 'item' => $item]);
        }

    }

    /**
     * Store a Company.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $appSetting = AppSettingConstraint::getAppSettingConstraint();
        $item_info = $request->get('item');
        $validator = Validator::make($item_info, $this->model->rules());
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }
        if($item_info['without_email_flg'] && !isset($item_info['url_domain_id'])){
            return response()->json(['status' => false,'message' => [__('パスワード設定コードを設定する場合「URLドメイン識別」を設定してください。')]]);
        }
        if($item_info['guest_company_flg'] && (!isset($item_info['mst_company_id']) || !isset($item_info['host_company_name'])) ){
            return response()->json(['status' => false,'message' => [__('message.false.create_guest_gompany')]]);
        }

        // gw 再判断
        $gw_use=config('app.gw_use');
        $gw_domin=config('app.gw_domain');
        if($gw_use!=1 || !$gw_domin){
            $item_info['gw_flg'] = 0;
        }

        $item = new $this->model;
        $item->fill($item_info);
        $item->dstamp_style = AppUtils::DSTAMP_STYLE_DEFAULT;
        $item->create_user = $user->getFullName();

        DB::beginTransaction();
        try{

            $item->edition_id = AppUtils::EDITION_ID_COMPANY;
            $item->contract_edition_sample_flg = AppUtils::EDITION_SAMPLE_F;
            if($item_info['contract_edition'] == AppUtils::CONTRACT_EDITION_TRIAL){
                unset($item->regular_at);
            }else{
                $item['regular_at'] = date('Y-m-d H:i:s', strtotime($item['regular_at']));
            }
            $item->save();
            //受信専用プラン
            if (config("app.pac_app_env")){
                $item->receive_plan_flg = 0;
                $item->receive_plan_url = "";
            }
            if ($item->receive_plan_flg === 1){
                $item->receive_plan_url = CommonUtils::getReceivePlanUrl($item->id);
            }else{
                $item->receive_plan_url = "";
            }
            DB::table('password_policy')->insert([
                "mst_company_id" => $item->id,
                "min_length" => AppUtils::MIN_LENGTH_DEFAULT,
                "validity_period" => AppUtils::VALIDITY_PERIOD_DEFAULT,
                "enable_password" => AppUtils::ENABLE_PASSWORD_DEFAULT,
                "password_mail_validity_days" => AppUtils::PASSWORD_MAIL_VALIDITY_DAYS_DEFAULT,
                'create_at'=> \Carbon\Carbon::now(),
                'create_user'=> $user->getFullName(),
            ]);
            DB::table('mst_limit')->insert([
                "mst_company_id" => $item->id,
                'storage_local' => AppUtils::STORAGE_LOCAL_DEFAULT,
                'storage_box' => AppUtils::STORAGE_BOX_DEFAULT,
                'storage_google' => AppUtils::STORAGE_GOOGLE_DEFAULT,
                'storage_dropbox'=> AppUtils::STORAGE_DROPBOX_DEFAULT,
                'storage_onedrive'=> AppUtils::STORAGE_ONEDRIVE_DEFAULT,
                'enable_any_address' => AppUtils::STORAGE_ANY_ADDRESS_DEFAULT,
                'link_auth_flg' => AppUtils::LINK_AUTH_FLG_DEFAULT,
                'enable_email_thumbnail' => AppUtils::ENABLE_EMAIL_THUMBNAIL_DEFAULT,
                'receiver_permission' => AppUtils::RECEIVER_PERMISSION_DEFAULT,
                'environmental_selection_dialog' => AppUtils::ENVIRONMENTAL_SELECTION_DIALOG_DEFAULT,
                "time_stamp_permission" => AppUtils::TIME_STAMP_PERMISSION_DEFAULT,
                'create_at'=> \Carbon\Carbon::now(),
                'create_user'=> $user->getFullName(),
                'text_append_flg' => AppUtils::TEXT_APPEND_FLG_DEFAULT,
                'require_print' => AppUtils::REQUIRE_PRINT_DEFAULT,
                'require_approve_flag' => AppUtils::REQUIRE_APPROVE_DEFAULT,
                'default_stamp_history_flg' => AppUtils::DEFAULT_STAMP_HISTORY_FLG_DEFAULT,
            ]);
            DB::table('mst_protection')->insert([
                'mst_company_id' => $item->id,
                'protection_setting_change_flg' => AppUtils::PROTECTION_SETTING_CHANGE_FLG_DEFAULT,
                'destination_change_flg' => AppUtils::DESTINATION_CHANGE_FLG_DEFAULT,
                'enable_email_thumbnail' => AppUtils::ENABLE_EMAIL_THUMBNAIL_PROTECTION,
                'access_code_protection' => AppUtils::ACCESS_CODE_PROTECTION_DEFAULT,
                'text_append_flg' => AppUtils::TEXT_APPEND_FLG_DEFAULT,
                'create_at' => Carbon::now(),
                'create_user' => $user->getFullName(),
                'require_print' => AppUtils::REQUIRE_PRINT_DEFAULT,
            ]);
            DB::table('mst_long_term_save')->insert([
                'mst_company_id' => $item->id,
                'auto_save' => AppUtils::AUTO_SAVE_DEFAULT,
                'auto_save_days' => AppUtils::AUTO_SAVE_DAYS_DEFAULT,
                'create_user' => $user->getFullName(),
                'create_at' => Carbon::now()
            ]);
            $this->modelAuthority->initDefaultValue($item->id, $user->getFullName());
            if($item->portal_flg){
                $this->modelAuthority->initDefaultValuePortal($item->id, $user->getFullName());
            }
            if($item->hr_flg){
                $this->modelAuthority->initDefaultValueHr($item->id, $user->getFullName());
            }


            if($item->expense_flg){
                $this->modelAuthority->initDefaultValueExpense($item->id, $user->getFullName());
            }
            if ($item->bizcard_flg) {
                $this->modelAuthority->initDefaultValueBizCard($item->id, $user->getFullName());
            }
            if ($item->template_flg) {
                $this->modelAuthority->initDefaultValueTemplate($item->id, $user->getFullName());
            }
            if ($item->template_csv_flg) {
                $this->modelAuthority->initDefaultValueTemplateCsv($item->id, $user->getFullName());
            }
            if ($item->attachment_flg) {
                $this->modelAuthority->initDefaultValueAttachment($item->id, $user->getFullName());
            }

            $insertedConstraints = $this->addConstrain($item->id);
            $insertSpecialSite = $this->addSpecial($item->id);

            if($item_info["board_flg"]){
                //掲示板がチェックされている場合
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_BOARD);
            }

            if ($item_info['file_mail_flg']) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL, $item_info["file_mail_limit_flg"] ? 1 : 0, $item_info["file_mail_buy_count"] ?: 0);
                if(isset($item_info["file_mail_extend_flg"]) && $item_info["file_mail_extend_flg"]){
                    ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND, 1, 0);
                }
            }
            if ($item_info["attendance_flg"]) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_TIME_CARD, $item_info["attendance_limit_flg"] ? 1 : 0, $item_info["attendance_buy_count"] ?: 0);
            }

            if ($item_info["faq_board_flg"]) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD,$item_info["faq_board_limit_flg"] ? 1 : 0, $item_info["faq_board_buy_count"] ?: 0);
            }

            if ($item_info["to_do_list_flg"]) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_TO_DO_LIST,$item_info["to_do_list_limit_flg"] ? 1 : 0,$item_info["to_do_list_buy_count"] ? 1 : 0);
            }

            if ($item_info["address_list_flg"]) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_ADDRESS_LIST, $item_info["address_list_limit_flg"] ? 1 : 0, $item_info["address_list_buy_count"] ?: 0);
            }

            // env GW_USE 1　gwが存在する環境　
            $gw_update_flg = false;
            if($gw_use==1 && $gw_domin){
                //会社情報登録API呼び出し
                $storeCompanyResult = GwAppApiUtils::storeCompany($item->id, $item->company_name, $item->state);
                if ($storeCompanyResult){
                    //アプリ利用制限登録API呼び出し
                    $app_limit_id = GwAppApiUtils::storeCompanyLimit($item->id);
                    if (!$app_limit_id){
                        return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。'] ]);
                    }

                    //アプリ企業マスタ登録　スケジューラ
                    if($item_info["scheduler_flg"]){ //スケジューラがチェックされている場合
                        $storeCompanySettingResult = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_SCHEDULE,$item_info["scheduler_limit_flg"]?1:0,$item_info["scheduler_buy_count"]?:0);
                        if (!$storeCompanySettingResult){
                            return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。']]);
                        }

                        // App company master registration caldav
                        if($item_info["caldav_flg"]){ //If the caldav is checked
                            $store_caldav_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_CALDAV,$item_info["caldav_limit_flg"]?1:0,$item_info["caldav_buy_count"]?:0);
                            if (!$store_caldav_result) {
                                return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。']]);
                            }

                            if(isset($item_info["google_flg"]) && $item_info["google_flg"]){ //If the google is checked
                                $store_google_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_GOOGLE, 1, 0);
                                if (!$store_google_result) {
                                    return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。']]);
                                }
                            }
                            if(isset($item_info["outlook_flg"]) && $item_info["outlook_flg"]){ //If the outlook is checked
                                $store_outlook_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_OUTLOOK, 1, 0);
                                if (!$store_outlook_result) {
                                    return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。']]);
                                }
                            }
                            if(isset($item_info["apple_flg"]) && $item_info["apple_flg"]){ //If the apple is checked
                                $store_apple_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_APPLE, 1, 0);
                                if (!$store_apple_result) {
                                    return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。']]);
                                }
                            }
                        }
                        if($item_info["shared_scheduler_flg"]){
                            $store_shared_scheduler_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE,1,0);
                            if (!$store_shared_scheduler_result) {
                                return response()->json(['status' => false, 'message' => ['企業情報の登録に失敗しました。']]);
                    }
                        }
                    }
                    $gw_update_flg = true;
                }
                //API終了
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        if($gw_use==1 && $gw_domin){
            $insertedConstraints->maxBbsCount = AppUtils::MAX_BBS_COUNT;
            $insertedConstraints->maxScheduleCount = AppUtils::MAX_SCHEDULE_COUNT;
            if ($gw_update_flg){
                return response()->json([
                    'status' => true,
                    'id' => $item->id,
                    'board_flg_org' => $item_info["board_flg"],
                    'scheduler_flg_org' => $item_info["scheduler_flg"],
                    'caldav_flg_org' => $item_info["caldav_flg"],
                    'file_mail_flg_org' =>$item_info['file_mail_flg'],
                    'app_limit_id' => $app_limit_id,
                    'attendance_flg_org' => $item_info["attendance_flg"],
                    'faq_board_flg_org' => $item_info["faq_board_flg"],
                    'address_list_flg_org' => $item_info["address_list_flg"],
                    'constraint' => $insertedConstraints,
                    'special_site_receive_send_available_state' =>$insertSpecialSite,
                    'enable_any_address_flg_org' => $item->enable_any_address_flg,
                    'gw_failed' => false,
                    'shared_scheduler_flg_org' => $item_info["shared_scheduler_flg"],
                    'receive_plan_url'=>$item->receive_plan_flg?$item->receive_plan_url:"",
                    'message' => [__('message.success.create_company')]
                ]);
            }else{
                Log::error('GWのデータを更新失敗しました。(会社ID：'.$item->id.')');
                return response()->json([
                    'status' => true,
                    'id' => $item->id,
                    'board_flg_org' => $item_info["board_flg"],
                    'scheduler_flg_org' => 0,
                    'caldav_flg_org' => 0,
                    'file_mail_flg_org' =>$item_info['file_mail_flg'],
                    'app_limit_id' => "",
                    'attendance_flg_org' => $item_info["attendance_flg"],
                    'faq_board_flg_org' => $item_info["faq_board_flg"],
                    'address_list_flg_org' => $item_info["address_list_flg"],
                    'constraint' => $insertedConstraints,
                    'special_site_receive_send_available_state' =>$insertSpecialSite,
                    'gw_failed' => true,
                    'shared_scheduler_flg_org' => 0,
                    'receive_plan_url'=>$item->receive_plan_flg?$item->receive_plan_url:"",
                    'message' => [__('message.warning.gw_failed.company')]
                ]);
            }
        }else{
            return response()->json(['status' => true, 'id' => $item->id,
                'board_flg_org' => $item_info["board_flg"],
                'file_mail_flg_org' => $item_info['file_mail_flg'],
                'attendance_flg_org' => $item_info["attendance_flg"],
                'faq_board_flg_org' => $item_info["faq_board_flg"],
                'address_list_flg_org' => $item_info["address_list_flg"],
                'constraint' => $insertedConstraints,
                'enable_any_address_flg_org' => $item->enable_any_address_flg,
                'special_site_receive_send_available_state' =>$insertSpecialSite,
                'gw_failed' => false,
                'receive_plan_url'=>$item->receive_plan_flg?$item->receive_plan_url:"",
            'message' => [__('message.success.create_company')]
        ]);

        }
    }

    function update($id, Request $request){
        $user = \Auth::user();

        $item_post  = $request->get('item');
        $talk = $item_post['talk'];
        $item = $this->model->find($id);
        $chat_flg_before = $item->chat_flg;
        $portal_flg_before = $item->portal_flg; //変更前portal_flgの退避
        $hr_flg_before = $item->hr_flg; //変更前hr_flgの退避
        $expense_flg_before = $item->expense_flg; //変更前expense_flgの退避
        $attachment_flg_before = $item->attachment_flg; //変更前attachment_flgの退避
        $bizcard_flg_before = $item->bizcard_flg; //変更前attachment_flgの退避
        $template_flg_before = $item->template_flg; //変更前attachment_flgの退避
        $template_csv_flg_before = $item->template_csv_flg; //変更前attachment_flgの退避

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        if(!isset($item_post['constraint']['mst_company_id'])){
            $item_post['constraint']['mst_company_id'] = $id;
        }

        $constraint = $this->constraint->find($item['constraint']['id']);
        // 特設サイト
        $specialSiteReceiveSendAvailableState = $this->specialSiteReceiveSendAvailableState->find($item_post['special_site_receive_send_available_state']['id']);

        $validator = Validator::make($item_post, $this->model->rules($id));
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $validator_constraint = Validator::make($item_post['constraint'], $this->constraint->rules());
        if ($validator_constraint->fails())
        {
            $message = $validator_constraint->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        if($item_post['without_email_flg'] && !isset($item_post['url_domain_id'])){
            return response()->json(['status' => false,'message' => [__('パスワード設定コードを設定する場合「URLドメイン識別」を設定してください。')]]);
        }

        // トライアル延長回数を設定,画面にトライアル期間より大きい現在の値、トライアル延長回数+1
        if (isset($item_post['trial_time']) && $item_post['trial_time'] > $item->trial_time && date("Y-m-d") > date("Y-m-d", strtotime($item->trial_times_update_at))) {
            $item_post['trial_times'] += 1;
            $item_post['trial_times_update_at'] = \Carbon\Carbon::now();
        }

        if (((int)$item_post['stamp_flg'] === 1 && (int)$item->stamp_flg === 0)
        || ($item_post['timestamps_count'] > 0 && $item_post['timestamps_count'] != $item->timestamps_count)
        ) {
            $item_post['timestamp_notified_flg'] = 0;
        }
        // gw 再判断
        $gw_use=config('app.gw_use');
        $gw_domin=config('app.gw_domain');
        if($gw_use!=1 || !$gw_domin){
            $item_post['gw_flg'] = 0;
        }

        if ($item_post['convenient_flg'] == 0) {
            $item_post['convenient_upper_limit'] = 0;
        }
        $trialToFormal = false;
        if ($item_post['contract_edition'] != AppUtils::CONTRACT_EDITION_TRIAL && $item->contract_edition == AppUtils::CONTRACT_EDITION_TRIAL){
            $trialToFormal = true;
        }
        //PAC_5-2912 S
        if($item_post['sanitizing_flg'] && !$item_post['mst_sanitizing_line_id']){
            return response()->json(['status' => false, 'message' => [__('message.false.sanitizing_line_select')] ]);
        }
        if(!$item_post['mst_sanitizing_line_id']){
            $item_post['mst_sanitizing_line_id'] = 0;
        }
        //PAC_5-2912 E

        $item->fill($item_post);
        $item->update_user = $user->getFullName();

        $constraint->fill($item_post['constraint']);
        $constraint->update_user = $user->getFullName();

        //メールアドレス無しユーザーありの場合、企業設定変更不可
        if (isset($item_post['without_email_flg']) && $item_post['without_email_flg'] == 0) {
            $mst_users_count = DB::table('mst_user')->where('without_email_flg', AppUtils::WITHOUT_EMAIL_T)
                ->where('state_flg', '<>', AppUtils::STATE_DELETE)
                ->where('mst_company_id',$id)
                ->count();
            if($mst_users_count){
                return response()->json(['status' => false,'message' => [__('message.warning.update_without_email_flg')]]);
            }
        }


        // 受取機能 提出機能 のいずれがチェックありの場合
        if($item_post['special_site_receive_send_available_state']['is_special_site_receive_available'] == 1 || $item_post['special_site_receive_send_available_state']['is_special_site_send_available'] == 1){
            if(!$item_post['special_site_receive_send_available_state']['group_name']){
                return response()->json(['status' => false, 'message' => [__('message.false.special_group')] ]);
            }
            if(!isset($item_post['special_site_receive_send_available_state']['region_name']) || !$item_post['special_site_receive_send_available_state']['region_name']){
                return response()->json(['status' => false, 'message' => [__('message.false.special_region')] ]);
            }
            //地域の取得
            $regionSel = DB::table('mst_region')
                ->select('region_name')
                ->where('region_id', $item_post['special_site_receive_send_available_state']['region_name'])
                ->first();
            $specialSiteReceiveSendAvailableState->fill($item_post['special_site_receive_send_available_state']);
            if($regionSel){
                $specialSiteReceiveSendAvailableState->region_name = $regionSel->region_name;
            }else{
                $specialSiteReceiveSendAvailableState->region_name = null;
            }
            $specialSiteReceiveSendAvailableState->update_user = $user->getFullName();
        }

        //受信専用プラン
        if (config("app.pac_app_env")){
            $item->receive_plan_flg = 0;
            $item->receive_plan_url = "";
        }
        if ($item->receive_plan_flg === 1){
            $item->receive_plan_url = CommonUtils::getReceivePlanUrl($id);
        }else{
            $item->receive_plan_url = "";
        }

        DB::beginTransaction();
        try{
            //PAC_5-2663
            $talk_update = false;
            if ($item['chat_flg'] == AppUtils::MST_COMPANY_CHAT_FLG_USING) {
                if ($talk && $talk['id']) {
                    $talk_validate = Validator::make($talk, [
                        'contract_type' => 'required',
                        'user_max_limit' => 'required|numeric|min:1|max:4294967295',
                        'contract_start_date' => 'nullable|date',
                        'contract_end_date' => 'nullable|date',
                        'trial_start_date' => 'nullable|date',
                        'trial_end_date' => 'nullable|date',
                        'storage_max_limit' => 'required|numeric|min:1|max:9999',
                    ]);
                    if ($talk_validate->fails()) {
                        $message = $talk_validate->messages();
                        $message_all = $message->all();
                        return response()->json(['status' => false, 'message' => $message_all]);
                    }
                    if (!isset($talk['contract_end_date']) && $talk['contract_start_date']) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_admin_talk_contract_end_date_e1')]]);
                    }
                    if (!isset($talk['contract_start_date']) && $talk['contract_end_date']) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_admin_talk_contract_start_date_e1')]]);
                    }
                    if (!isset($talk['trial_end_date']) && $talk['trial_start_date'] && $item['chat_trial_flg']) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_admin_talk_trial_end_date_e2')]]);
                    }
                    if (!isset($talk['trial_start_date']) && $talk['trial_end_date'] && $item['chat_trial_flg']) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_admin_talk_trial_start_date_e1')]]);
                    }
                    if ($item['chat_trial_flg'] == 1 && !isset($talk['trial_start_date']) && !isset($talk['trial_end_date'])) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_admin_talk_trial_date')]]);
                    }
                    if (!isset($talk['contract_end_date']) && !$talk['contract_start_date']) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_admin_talk_contract_date')]]);
                    }
                    $talk_update = true;
                }
            }
            if ($talk_update && $item['chat_trial_flg'] == 1) {
                DB::table('mst_chat')
                    ->where('mst_company_id', $item->id)
                    ->update([
                        'contract_type' => $talk['contract_type'],
                        'contract_start_date' => (isset($talk['contract_start_date']) && $talk['contract_start_date']) ? date('Y-m-d H:i:s', strtotime($talk['contract_start_date'])) : null,
                        'contract_end_date' => (isset($talk['contract_end_date']) && $talk['contract_end_date']) ? date('Y-m-d H:i:s', strtotime($talk['contract_end_date'])) : null,
                        'trial_start_date' => ((isset($talk['trial_start_date']) && $talk['trial_start_date']) ? date('Y-m-d H:i:s', strtotime($talk['trial_start_date'])) : null),
                        'trial_end_date' => (isset($talk['trial_end_date']) && $talk['trial_end_date']) ? date('Y-m-d H:i:s', strtotime($talk['trial_end_date'])) : null,
                        'user_max_limit' => $talk['user_max_limit'],
                        'storage_max_limit' => $talk['storage_max_limit'],
                        'update_at' => Carbon::now(),
                        'update_user' => $user->email,
                    ]);
            } elseif ($talk_update && $item['chat_trial_flg'] == 0) {
                DB::table('mst_chat')
                    ->where('mst_company_id', $item->id)
                    ->update([
                        'contract_type' => $talk['contract_type'],
                        'contract_start_date' => (isset($talk['contract_start_date']) && $talk['contract_start_date']) ? date('Y-m-d H:i:s', strtotime($talk['contract_start_date'])) : null,
                        'contract_end_date' => (isset($talk['contract_end_date']) && $talk['contract_end_date']) ? date('Y-m-d H:i:s', strtotime($talk['contract_end_date'])) : null,
                        'user_max_limit' => $talk['user_max_limit'],
                        'storage_max_limit' => $talk['storage_max_limit'],
                        'update_at' => Carbon::now(),
                        'update_user' => $user->email,
                    ]);
            }
            // PAC_5-494 MOD start
            $userInfoIds = DB::table('mst_user_info')
                ->join('mst_user', 'mst_user_info.mst_user_id', '=', 'mst_user.id')
                ->where('mst_user.mst_company_id','=', $item->id)
                ->select('mst_user_info.mst_user_id')
                ->pluck('mst_user_info.mst_user_id')
                ->toArray();
            // PAC_5-494 MOD end
            if ($trialToFormal){
                $applicationUsers = DB::table('mst_user')
                    ->where('mst_company_id', $item->id)
                    ->pluck('id')
                    ->toArray();
                if (count($applicationUsers) > 0) {
                    DB::table('mst_application_users')
                        ->where('mst_application_id', AppUtils::GW_APPLICATION_ID_FAQ_BOARD)
                        ->whereIn('mst_user_id', $applicationUsers)
                        ->delete();
                }
            }
            if ($item_post['contract_edition'] == AppUtils::CONTRACT_EDITION_TRIAL){
                unset($item['regular_at']);
            } else {
                $item['regular_at'] = date('Y-m-d H:i:s', strtotime($item['regular_at']));
            }
            $item->save();
            $constraint->save();

            if ($item_post['enable_any_address_flg'] != $item_post['enable_any_address_flg_org'] && !$item_post['enable_any_address_flg']) {
                // 制限設定.タイムスタンプ発行権限(全ユーザー)を無効に更新
                DB::table('mst_limit')
                    ->where('mst_company_id', $item->id)
                    ->where('enable_any_address', AppUtils::STORAGE_ANY_ADDRESS_ROUTES)
                    ->update(['enable_any_address' => AppUtils::STORAGE_ANY_ADDRESS_DEFAULT]);
            }
            //付箋機能
            if ($item_post['sticky_note_flg'] == 0) {
                DB::table('mst_user_info')
                    ->join('mst_user', 'mst_user.id', 'mst_user_info.mst_user_id')
                    ->where('mst_user.mst_company_id', $id)->update([
                        'mst_user_info.sticky_note_flg' => 0,
                    ]);
            }

            /*PAC_5-2821 S*/
            if ($item_post['skip_flg'] == AppUtils::SKIP_FLG_DEFAULT) {
                // 制限設定.スキップ機能を有効に更新
                DB::table('mst_limit')
                    ->where('mst_company_id', $item->id)
                    ->where('limit_skip_flg', AppUtils::LIMIT_SKIP_FLG_STATUS)
                    ->update(['limit_skip_flg' => AppUtils::LIMIT_SKIP_FLG_DEFAULT]);
            }
            /*PAC_5-2821 E*/

            if ($item_post['with_box_flg'] == 0){
                // 制限設定.利用者のshachihata cloudへのログインを制限する | box捺印へのログインを制御する の制限を無効に更新
                DB::table('mst_limit')
                    ->where('mst_company_id', $item->id)
                    ->where(function ($query){
                        $query->where('with_box_login_flg', 1)
                            ->orWhere('shachihata_login_flg',1);
                    })
                    ->update(['with_box_login_flg' => 0,'shachihata_login_flg' => 0]);
            }
            $specialSiteReceiveSendAvailableState->save();

            //ユーザー状態変更
            if ($item->contract_edition == AppUtils::CONTRACT_EDITION_GW){
                //回覧利用者 無効
                DB::table('mst_user')->where('mst_company_id', $item->id)
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->where('option_flg', AppUtils::USER_NORMAL)
                    ->update(['state_flg' => AppUtils::STATE_INVALID]);
            }

            if($portal_flg_before!=$item_post["portal_flg"]){//portal_flgに変更があった場合
                if($item_post["portal_flg"]){
                    $this->modelAuthority->initDefaultValuePortal($item->id, $user->getFullName());
                }else{
                    $this->modelAuthority->delDefaultValuePortal($item->id);
                }
            }

            if($hr_flg_before!=$item_post["hr_flg"]){//hr_flgに変更があった場合
                if($item_post["hr_flg"]){
                    $this->modelAuthority->initDefaultValueHr($item->id, $user->getFullName());
                }else{
                    $this->modelAuthority->delDefaultValueHr($item->id);
                }
            }

            if($expense_flg_before!=$item_post["expense_flg"]){//expense_flgに変更があった場合
                if($item_post["expense_flg"]){
                    $this->modelAuthority->initDefaultValueExpense($item->id, $user->getFullName());
                }else{
                    $this->modelAuthority->delDefaultValueExpense($item->id);
                }
            }

            // PAC_5-2663
            if ($chat_flg_before != $item_post['chat_flg']) {
                if($item_post['chat_flg']) {
                    $this->modelAuthority->initDefaultValueTalk($item->id, $user->getFullName());
                } else {
                    $this->modelAuthority->delDefaultValueTalk($item->id);
                }
            }
            if ($attachment_flg_before != $item_post['attachment_flg']) {
                if($item_post['attachment_flg']) {
                    $this->modelAuthority->initDefaultValueAttachment($item->id, $user->getFullName());
                } else {
                    $this->modelAuthority->delDefaultValueAttachment($item->id);
                }
            }
            if ($bizcard_flg_before != $item_post['bizcard_flg']) {
                if($item_post['bizcard_flg']) {
                    $this->modelAuthority->initDefaultValueBizCard($item->id, $user->getFullName());
                } else {
                    $this->modelAuthority->delDefaultValueBizCard($item->id);
                }
            }
            if ($template_flg_before != $item_post['template_flg']) {
                if($item_post['template_flg']) {
                    $this->modelAuthority->initDefaultValueTemplate($item->id, $user->getFullName());
                } else {
                    $this->modelAuthority->delDefaultValueTemplate($item->id);
                }
            }
            if ($template_csv_flg_before != $item_post['template_csv_flg']) {
                if($item_post['template_csv_flg']) {
                    $this->modelAuthority->initDefaultValueTemplateCsv($item->id, $user->getFullName());
                } else {
                    $this->modelAuthority->delDefaultValueTemplateCsv($item->id);
                }
            }

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $params = [
                'company_id' => $item->id,
                'system_name' => $item->system_name,
                'company_name'=> $item->company_name,
                'app_env' => config('app.pac_app_env'),
                'contract_server'=> config('app.pac_contract_server'),
            ];

             $result = $client->post('company/update', [
                 RequestOptions::JSON => $params
             ]);
             $response = json_decode((string)$result->getBody());
             if ($result->getStatusCode() != 200) {
                 return response()->json(['status' => false,
                     'message' => [$response->message]
                 ]);
             }

            if (count($userInfoIds)){
                $updateList = array();
                // 閲覧通知メール設定
                if ($item->view_notification_email_flg === 0) {
                    $updateList['browsed_notice_flg'] = 0;
                }
                // 更新通知メール設定
                if ($item->updated_notification_email_flg === 0) {
                    $updateList['update_notice_flg'] = 0;
                }

                // 多要素認証
                if ($item->mfa_flg === 0) {
                    // 配下利用者.多要素認証無効に更新
                    $updateList['mfa_type'] = 0;
                }
                // タイムスタンプ付署名
                if ($item->stamp_flg === 0) {
                    // 配下利用者.タイムスタンプ発行権限無効に更新
                    $updateList['time_stamp_permission'] = 0;

                    // 配下利用者に割当済み印鑑.タイムスタンプ発行権限無効に更新
                    DB::table('mst_assign_stamp')
                        ->whereIn('mst_user_id', $userInfoIds)
                        ->update(['time_stamp_permission' => 0]);
                }
                // おじぎ印
                if ($item->rotate_angle_flg === 0){
                    // 配下利用者.おじぎ印無効に更新、角度０に更新
                    $updateList['rotate_angle_flg'] = 0;
                    $updateList['default_rotate_angle'] = 0;
                }else{
                    // 配下利用者.おじぎ印有効に更新
                    $updateList['rotate_angle_flg'] = 1;
                }
                // 部署・役職複数登録
                if ($item->multiple_department_position_flg === 0) {
                    $updateList['mst_department_id_1'] = null;
                    $updateList['mst_department_id_2'] = null;
                    $updateList['mst_position_id_1'] = null;
                    $updateList['mst_position_id_2'] = null;
                }
                //テンプレート機能
                if ($item->template_flg === 0){
                    // 配下利用者.テンプレート機能無効に更新
                    $updateList['template_flg'] = 0;
                }

                if(count($updateList)){
                    DB::table('mst_user_info')
                        ->whereIn('mst_user_id', $userInfoIds)
                        ->update($updateList);
                }
            }
            $item_post['scheduler_flg'] = empty($item_post['scheduler_flg']) ? 0 : $item_post['scheduler_flg'];
            // グループウェア機能 スケジューラー
            if ( !$item_post['scheduler_flg']){
                $optionUsers = DB::table('mst_user')
                    ->where('mst_company_id','=', $item->id)
                    ->where('state_flg',AppUtils::STATE_VALID)
                    ->where('option_flg','=', AppUtils::USER_OPTION)
                    ->select('id','email','given_name','family_name')
                    ->get()
                    ->keyBy('id');
                $optionUserIds = $optionUsers->keys()->toArray();

                DB::table('mst_user')
                    ->whereIn('id',$optionUserIds)
                    ->where('option_flg',AppUtils::USER_OPTION)
                    ->update(['state_flg' => AppUtils::STATE_INVALID]);
                //ID
                foreach ($optionUsers as $id => $optionUser){
                    $apiUser = [
                        "user_email" => $optionUser->email,
                        "email" => $optionUser->email,
                        "contract_app" => config('app.pac_contract_app'),
                        "app_env" => config('app.pac_app_env'),
                        "contract_server" => config('app.pac_contract_server'),
                        "user_auth" => AppUtils::AUTH_FLG_OPTION,
                        "user_first_name" => $optionUser->given_name,
                        "user_last_name" => $optionUser->family_name,
                        "company_name" => $item->company_name,
                        "company_id" => $item->id,
                        "status" => 1,
                        "system_name" => $item->system_name,
                        "update_user_email" => $user->email,
                    ];
                    $result = $client->put("users", [
                        RequestOptions::JSON => $apiUser
                    ]);
                    if ($result->getStatusCode() != 200) {
                        Log::warning("Call ID App Api to update company user failed. Response Body " . $result->getBody());
                        $response = json_decode((string)$result->getBody());
                        return response()->json(['status' => false,
                            'message' => [$response->message],
                        ]);
                    }
                }
            }

            // タイムスタンプ付署名
            if ($item->stamp_flg === 0) {
                // 制限設定.タイムスタンプ発行権限(全ユーザー)を無効に更新
                DB::table('mst_limit')
                    ->where('mst_company_id', $item->id)
                    ->update(['time_stamp_permission' => 0]);
            }

            // メール内の文書のサムネイル
            if ($item->enable_email_thumbnail === 0) {
                // 保護設定.メール内の文書のサムネイル表示を表示しないに更新
                DB::table('mst_protection')
                    ->where('mst_company_id', $item->id)
                    ->update(['enable_email_thumbnail' => 0]);
            }

            // 会社無効時、rememberToken削除
            if ($item_post['state'] == AppUtils::COMPANY_STATE_INVALID
                || ($item_post['contract_edition'] == AppUtils::CONTRACT_EDITION_TRIAL && !$item_post['trial_flg'])) {
                CommonUtils::rememberTokenClean($id,'company');
            }

            // PAC_5-1545 MOD START
            // 便利印フラグ
            if ($item->convenient_flg === 0) {

                $assignStampIds = DB::table('mst_company_stamp_convenient')
                    ->join('mst_assign_stamp', 'mst_company_stamp_convenient.id', '=', 'mst_assign_stamp.stamp_id')
                    ->where('mst_company_stamp_convenient.mst_company_id','=', $item->id)
                    ->where('mst_company_stamp_convenient.del_flg','=',0)
                    ->where('mst_assign_stamp.stamp_flg','=',3)
                    ->where('mst_assign_stamp.state_flg','=',1)
                    ->select('mst_assign_stamp.id')
                    ->pluck('mst_assign_stamp.id')
                    ->toArray();

                // PAC_5-1770 ▼
                $assignedCompanyStampInfos = DB::table('mst_assign_stamp')
                    ->join('mst_company_stamp_convenient', 'mst_company_stamp_convenient.id', '=', 'mst_assign_stamp.stamp_id')
                    ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                    ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
                    ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id','mst_assign_stamp.stamp_flg','mst_assign_stamp.state_flg',
                        'mst_company.contract_edition','mst_company.company_name','mst_company.system_name')
                    ->where('mst_company_stamp_convenient.mst_company_id','=', $item->id)
                    ->where('mst_company_stamp_convenient.del_flg','=',0)
                    ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                    ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
                    ->get()
                    ->toArray();

                foreach ($assignedCompanyStampInfos as $assignedCompanyStampInfo){
                    $this->updateUserState($assignedCompanyStampInfo, $user);
                }
                // PAC_5-1770 ▲

                // 企業印面マスタデータ削除
                DB::table('mst_company_stamp_convenient')
                    ->where('mst_company_id', $item->id)
                    ->update(['del_flg' => 1,
                        'update_user' =>  $user->getFullName()]);

                // 割当済みの便利印を割当解除
                if($assignStampIds){
                    DB::table('mst_assign_stamp')
                        ->whereIn('id', $assignStampIds)
                        ->update(['state_flg' => AppUtils::STATE_INVALID,
                            'delete_at' => Carbon::now(),
                            'update_at' =>  Carbon::now(),
                            'update_user' =>  $user->getFullName(),
                        ]);
                }
            }
            // PAC_5-1545 MOD END

            // PAC_5-1948 掲示板アプリケーションロールの設定
            if(!$item_post["board_flg_org"] && $item_post["board_flg"]){ //掲示板が未チェック→チェックされた場合

                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_BOARD);

            }

            if($item_post["board_flg_org"] && !$item_post["board_flg"]){ //掲示板がチェック→未チェックされた場合

                ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_BOARD);

            }

            if ($item_post["attendance_flg"]) { //When the caldav is checked
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_TIME_CARD, $item_post["attendance_limit_flg"] ? 1 : 0, $item_post["attendance_buy_count"] ?: 0);
            }
            if ($item_post['file_mail_flg']) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL, $item_post["file_mail_limit_flg"] ? 1 : 0, $item_post["file_mail_buy_count"] ?: 0);
                if(isset($item_post["file_mail_extend_flg"]) && $item_post["file_mail_extend_flg"]){
                    ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND, 1, 0);
                }else{
                    ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND);
                }
            }
            if ($item_post['file_mail_flg_org'] && !$item_post['file_mail_flg']) {
                ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL);
                if(isset($item_post["file_mail_extend_flg"]) && !$item_post["file_mail_extend_flg"]){
                    ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND);
                }
            }
            if ($item_post["attendance_flg_org"] && (!$item_post["attendance_flg"])) {
                ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_TIME_CARD);
            }
            /*PAC_5-2376 E*/
            if ($item_post['faq_board_flg']) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD, $item_post["faq_board_limit_flg"] ? 1 : 0, $item_post["faq_board_buy_count"] ?: 0);
            }
            if ($item_post["faq_board_flg_org"] && (!$item_post["faq_board_flg"])) {
                ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD);
            }
            if ($item_post['to_do_list_flg']) {
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_TO_DO_LIST,$item_post["to_do_list_limit_flg"] ? 1 : 0, $item_post["to_do_list_buy_count"] ?: 0);
            }
            if (!$item_post['to_do_list_flg']) {
                ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_TO_DO_LIST);
            }
            if ($item_post["address_list_flg"]) { //When the caldav is checked
                ApplicationAuthUtils::storeCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_ADDRESS_LIST, $item_post["address_list_limit_flg"] ? 1 : 0, $item_post["address_list_buy_count"] ?: 0);
            }
            if ($item_post["address_list_flg_org"] && (!$item_post["address_list_flg"])) {
                ApplicationAuthUtils::deleteCompanySetting($item->id, AppUtils::GW_APPLICATION_ID_ADDRESS_LIST);
            }

            //API開始
            $gw_update_flg = collect([]);
            if($gw_use==1 && $gw_domin) {
                //会社情報更新API呼び出し
                $update_company_result = GwAppApiUtils::updateCompany($item->id, $item->company_name, $item->state);
                if (!$update_company_result){
                    $gw_update_flg->push(false);
                }else{
                    $gw_update_flg->push(true);
                }
                //アプリ利用制限更新API呼び出し
                if ($item_post['app_limit_id']){
                    $update_company_limit_result = GwAppApiUtils::updateCompanyLimit($item_post['app_limit_id'],$item->id,$item_post['constraint']);
                    if (!$update_company_limit_result){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                }else{
                    //アプリ利用制限登録API
                    $app_limit_id = GwAppApiUtils::storeCompanyLimit($item->id);
                    if (!$app_limit_id){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                }

                //アプリ企業マスタ参照API呼び出し　グループウェア側のcompany_idを取得するため
                $gw_app_schedule_id = "";
                $gw_app_caldav_id = "";
                /*PAC_5-2246 S*/
                $gw_app_time_card_id = "";
                /*PAC_5-2246 E*/
                $gw_app_google_id = "";
                $gw_app_outlook_id = "";
                $gw_app_apple_id = "";
                $gw_app_shared_scheduler_id = "";
                if ($item_post["scheduler_flg_org"] || $item_post["caldav_flg_org"]) { //掲示板またはスケジューラがチェック→未チェックされた場合

                    $settingCompanyIds = GwAppApiUtils::getCompanySettingId($item->id, $item->company_name, $item->state);
                    if (!$settingCompanyIds){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                    $gw_app_schedule_id = $settingCompanyIds['schedule_id'];
                    $gw_app_caldav_id = $settingCompanyIds['caldav_id'];
                    /*PAC_5-2246 S*/
//                    $gw_app_time_card_id = $settingCompanyIds['time_card_id'];
                    /*PAC_5-2246 E*/
//                    $gw_app_file_mail_id = $settingCompanyIds['file_mail_id'];
                    $gw_app_google_id = $settingCompanyIds['google_id'];
                    $gw_app_outlook_id = $settingCompanyIds['outlook_id'];
                    $gw_app_apple_id = $settingCompanyIds['apple_id'];
                    $gw_app_shared_scheduler_id = $settingCompanyIds['shared_scheduler_id'];
                }


                //アプリ企業マスタ登録　スケジューラ
                if ($item_post["scheduler_flg"]) { //チェックされた場合
                    $store_scheduler_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_SCHEDULE,$item_post["scheduler_limit_flg"]?1:0,$item_post["scheduler_buy_count"]?:0);
                    if (!$store_scheduler_result){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                }
                //アプリ企業マスタ登録　共有スケジューラ
                if ($item_post["shared_scheduler_flg"]) { //チェックされた場合
                    $store_shared_scheduler_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE,1,0);
                    if (!$store_shared_scheduler_result){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                }

                //アプリ企業マスタ登録　Caldav
                if($item_post["caldav_flg"]) { //When the caldav is checked
                    $store_caldav_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_CALDAV,$item_post["caldav_limit_flg"]?1:0,$item_post["caldav_buy_count"]?:0);
                    if (!$store_caldav_result) {
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }

                    if(isset($item_post["google_flg"]) && $item_post["google_flg"]){ //If the google is checked
                        $store_google_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_GOOGLE, 1, 0);
                        if (!$store_google_result) {
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    } else if ($gw_app_google_id) {
                        $del_google_result = GwAppApiUtils::deleteCompanySetting($gw_app_google_id);
                        if (!$del_google_result){
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    }

                    if(isset($item_post["outlook_flg"]) && $item_post["outlook_flg"]){ //If the outlook is checked
                        $store_outlook_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_OUTLOOK, 1, 0);
                        if (!$store_outlook_result) {
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    } else if ($gw_app_outlook_id) {
                        $del_outlook_result = GwAppApiUtils::deleteCompanySetting($gw_app_outlook_id);
                        if (!$del_outlook_result){
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    }

                    if(isset($item_post["apple_flg"]) && $item_post["apple_flg"]){ //If the apple is checked
                        $store_apple_result = GwAppApiUtils::storeCompanySetting($item->id,AppUtils::GW_APPLICATION_ID_APPLE, 1, 0);
                        if (!$store_apple_result) {
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    } else if ($gw_app_apple_id) {
                        $del_apple_result = GwAppApiUtils::deleteCompanySetting($gw_app_apple_id);
                        if (!$del_apple_result){
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    }
                }

                //アプリ企業マスタ削除　スケジューラ
                if ($item_post["scheduler_flg_org"] && !$item_post["scheduler_flg"]) { //スケジューラがチェック→未チェックされた場合
                    $del_scheduler_result = GwAppApiUtils::deleteCompanySetting($gw_app_schedule_id);
                    if (!$del_scheduler_result){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                }
                //アプリ企業マスタ削除　共有スケジューラ
                if($item_post["shared_scheduler_flg_org"] && (!$item_post["shared_scheduler_flg"] || !$item_post["scheduler_flg"])) {
                    $del_scheduler_flg_result = GwAppApiUtils::deleteCompanySetting($gw_app_shared_scheduler_id);
                    if (!$del_scheduler_flg_result){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                }
                //アプリ企業マスタ削除　Caldav
                if($item_post["caldav_flg_org"] && (!$item_post["caldav_flg"] || !$item_post["scheduler_flg"])) { //When the caldav is checked → unchecked
                    $del_caldav_result = GwAppApiUtils::deleteCompanySetting($gw_app_caldav_id);
                    if (!$del_caldav_result){
                        $gw_update_flg->push(false);
                    }else{
                        $gw_update_flg->push(true);
                    }
                    if ($gw_app_google_id) {
                        $del_google_result = GwAppApiUtils::deleteCompanySetting($gw_app_google_id);
                        if (!$del_google_result){
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    }
                    if ($gw_app_outlook_id) {
                        $del_outlook_result = GwAppApiUtils::deleteCompanySetting($gw_app_outlook_id);
                        if (!$del_outlook_result){
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    }
                    if ($gw_app_apple_id) {
                        $del_apple_result = GwAppApiUtils::deleteCompanySetting($gw_app_apple_id);
                        if (!$del_apple_result){
                            $gw_update_flg->push(false);
                        }else{
                            $gw_update_flg->push(true);
                        }
                    }
                }
                  //API終了
            }

            //PAC_5-1902
            //利用状況登録・更新
            if($specialSiteReceiveSendAvailableState->group_name != "" && $specialSiteReceiveSendAvailableState->region_name != ""){
                $specialClient = SpecialAppApiUtils::getAuthorizeClient();
                if (!$specialClient) {
                    Log::error('Special Client Get Error ');
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to Special App']
                    ]);
                }
                $upsertReceiveSendInfo = "/sp/api/upsert-receive-send-info";
                $response = $specialClient->post($upsertReceiveSendInfo,
                    [
                        RequestOptions::JSON => [
                            "company_id"=>$item->id,
                            "env_flg"=>config('app.pac_app_env'),
                            "edition_flg"=>config('app.pac_contract_app'),
                            "server_flg"=>config('app.pac_contract_server'),
                            "receive"=>$specialSiteReceiveSendAvailableState->is_special_site_receive_available,
                            "send"=>$specialSiteReceiveSendAvailableState->is_special_site_send_available,
                            "group_name"=>$specialSiteReceiveSendAvailableState->group_name,
                            "region_name"=>$specialSiteReceiveSendAvailableState->region_name,
                        ]
                    ]);
                $response_dencode = json_decode($response->getBody(),true);  //配列へ
                if ($response->getStatusCode() == 200){
                    if($response_dencode['status'] == "error"){
                        Log::error('Upsert Receive Send Info:' .$response_dencode['message']);
                        Log::error($response_dencode);
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => [$response_dencode['message']] ]);
                    }
                } else {
                    Log::error('Api storeBoard companyId:' . $item->id);
                    Log::error($response_dencode);
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
                }
            }

            //入力したトライアル延長期限はSalesforceに連携
            if (config('forrest.credentials.consumerKey') && $item_post['state'] == 1 && $item_post['contract_edition'] == 3 && $item_post['trial_flg'] == 1) {
                Forrest::authenticate();
                $server_flg = 'app' . ((int)config('app.pac_contract_server') + 1);
                $sf_result = Forrest::query("SELECT Id FROM Account WHERE DSTMP_AppServer__c = '$server_flg' AND DSTMP_DomainId__c = '$id'");

                if ($sf_result['totalSize'] == 0) {
                    Log::debug("Salesforce側該当企業存在しません。mst_company_id=$id");
                } elseif ($sf_result['totalSize'] == 1) {
                    $account_id = $sf_result['records'][0]['Id'];
                    Forrest::sobjects("Account/$account_id", [
                        'method' => 'PATCH',
                        'body' => [
                            'Trial_etdtrm__c' => $item_post['trial_period_date']
                            //'DSTMP_ContractEndDate__c' => $item_post['trial_period_date']PAC_5-2574 DSTMP_ContractEndDate__c→Trial_etdtrm__cへ変更
                        ]
                    ]);
                } else {
                    Log::debug("Salesforce側複数企業存在のため、更新しません。mst_company_id=$id");
                }
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        //チェックボックスの初期値を最新化する

        if($gw_use==1 && $gw_domin){
            if ($gw_update_flg->some(function ($item){
                return !$item;
            })){
                Log::error('GWのデータを更新失敗しました。(会社ID：'.$item->id.')');
                return response()->json(['status' => true,
                    'id' => $item->id,
                    'app_limit_id' => "",
                    'board_flg_org' => $item_post["board_flg"],
                    'faq_board_flg_org' => $item_post["faq_board_flg"],
                    'scheduler_flg_org' => 0,
                    'caldav_flg_org' => 0,
                    'attendance_flg_org' => $item_post["attendance_flg"],
                    'enable_any_address_flg_org' => $item_post["enable_any_address_flg"],
                    'address_list_flg_org' => $item_post["address_list_flg"],
                    'gw_failed'=>true,
                    'receive_plan_url'=>$item->receive_plan_flg?$item->receive_plan_url:"",
                    'shared_scheduler_flg_org'=>0,
                    'file_mail_flg_org' => $item_post["file_mail_flg"],
                    'message' => [__('message.warning.gw_failed.company')]
                ]);
            }else{
                return response()->json(['status' => true,
                    'id' => $item->id,
                    'app_limit_id' => $item_post["app_limit_id"],
                    'board_flg_org' => $item_post["board_flg"],
                    'faq_board_flg_org' => $item_post["faq_board_flg"],
                    'scheduler_flg_org' => $item_post["scheduler_flg"],
                    'caldav_flg_org' => $item_post["caldav_flg"],
                    'attendance_flg_org' => $item_post["attendance_flg"],
                    'enable_any_address_flg_org' => $item_post["enable_any_address_flg"],
                    'address_list_flg_org' => $item_post["address_list_flg"],
                    'gw_failed'=>false,
                    'receive_plan_url'=>$item->receive_plan_flg?$item->receive_plan_url:"",
                    'shared_scheduler_flg_org'=>$item_post["shared_scheduler_flg"],
                    'file_mail_flg_org' => $item_post["file_mail_flg"],
                    'message' => [__('message.success.update_company')]
                ]);
            }

        }else{

            return response()->json(['status' => true,
                'id' => $item->id,
                'board_flg_org' => $item_post["board_flg"],
                'file_mail_flg_org' => $item_post['file_mail_flg'],
                'attendance_flg_org' => $item_post["attendance_flg"],
                'faq_board_flg_org' => $item_post["faq_board_flg"],
                'enable_any_address_flg_org' => $item_post["enable_any_address_flg"],
                'address_list_flg_org' => $item_post["address_list_flg"],
                'gw_failed'=>false,
                'receive_plan_url'=>$item->receive_plan_flg?$item->receive_plan_url:"",
                'message' => [__('message.success.update_company')]
                ]);
        }
    }

    private function updateUserState($assignedCompanyStampInfo, $user){
        $User = new User();
        $itemUser = $User->find($assignedCompanyStampInfo->mst_user_id);
        $stamps = $itemUser->getStamps($assignedCompanyStampInfo->mst_user_id);
        // 利用者設定画面＆共通印割当共通ロジック
        // Business以上 + 氏名印または日付印すべて削除された ＋ 共通印割当なし場合
        // 利用者を無効に更新
        $updateFlg = false;
        if($assignedCompanyStampInfo->contract_edition == 1 || $assignedCompanyStampInfo->contract_edition == 2){
            // Business以上
            // 氏名印または日付印 ＋ 共通印　＋　部署印　＝　1
            // 削除後、ゼロ件
            if((count($stamps['stampMaster']) + count($stamps['stampCompany']) + count($stamps['stampDepartment'])) == 1){
                if($assignedCompanyStampInfo->stamp_flg == 2){
                    // 部署印の場合、
                    // 作成中の部署印を削除する場合、なにもしない
                    // バッチ後の部署印を削除する場合、判定要
                    if($assignedCompanyStampInfo->state_flg != 2){
                        if($itemUser->state_flg == 1){
                            $updateFlg = true;
                        }
                    }
                }else{
                    // 部署印以外の場合、
                    if($itemUser->state_flg == 1){
                        $updateFlg = true;
                    }
                }
            }
        }else{
            // Business以上 以外
            // 氏名印または日付印　＝　1 ＋　通常印を削除する場合（共通印数を０になる）
            if(count($stamps['stampMaster']) == 1 && $assignedCompanyStampInfo->stamp_flg == 0){
                if($itemUser->state_flg == 1){
                    $updateFlg = true;
                }
            }
        }

        if($updateFlg){
            // 有効の場合、無効に更新
            if($itemUser->password){
                // パスワード設定済み
                $itemUser->state_flg = 9;
            }else{
                // パスワード未設定
                $itemUser->state_flg = 0;
            }
        }

        if($updateFlg){
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $apiUser = [
                "user_email" => $itemUser->email,
                "email"=> strtolower($itemUser->email),
                "contract_app"=> config('app.pac_contract_app'),
                "app_env"=> config('app.pac_app_env'),
                "contract_server"=> config('app.pac_contract_server'),
                "user_auth"=> AppUtils::AUTH_FLG_USER,
                "user_first_name"=> $itemUser->given_name,
                "user_last_name"=> $itemUser->family_name,
                "company_name"=> $assignedCompanyStampInfo->company_name,
                "company_id"=> $assignedCompanyStampInfo->mst_company_id,
                "status"=> AppUtils::convertState($itemUser->state_flg),
                "system_name"=> $assignedCompanyStampInfo->system_name,
                "update_user_email"=> $user->email,
            ];

            $itemUser->save();

            Log::debug("Call ID App Api to create company user");
            $apiUser['create_user_email'] = $user->email;
            $result = $client->put("users",[
                RequestOptions::JSON => $apiUser
            ]);

            if($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to create company user failed. Response Body ".$result->getBody());
                $response = json_decode((string) $result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message],
                    'errors' => isset($response->errors)?$response->errors:[]
                ]);
            }
        }
    }

    /**
     * List admin in company
     */
    function indexAdmin($company_id, Request $request){
        $user = \Auth::user();

        $page = $request->get('page') ? $request->get('page') : 1;
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $items = $this->companyAdmin
            ->orderBy($orderBy,$orderDir)
            ->where('mst_company_id', $company_id)
            ->where('state_flg','<>', AppUtils::STATE_DELETE)
            ->get();

        return response()->json(['status' => true, 'items' => $items ]);
    }

    /**
     * List stamp in company
     */
    function indexStamp ($company_id, Request $request){
        $user = \Auth::user();
        $per_page = $request->get('limit') ? $request->get('limit') : 10;
        $itemsStamp = $this->companyStamp->where('mst_company_id', $company_id)
						->where('del_flg', 0)
						->with('stampGroup')
						->paginate($per_page);
        // PAC_5-477 共通印登録画面、指定した位置に日付が表示される
        foreach($itemsStamp as $key => $stamp) {
            // 日付印の場合
            if ($stamp->stamp_division !== 0){
                // 日付で表示します
                $stamp->stamp_image = $this->companyStampWithDate($stamp, $company_id);
            }
            $itemsStamp[$key]['background_color'] = $itemsStamp[$key]['date_color'];
            $itemsStamp[$key]['date_color'] = \App\Http\Utils\AppUtils::changeDateColorLists($itemsStamp[$key]);
        }
        // PAC_5-556 グループ全件取得
		$list_group = DB::table('mst_company_stamp_groups')
			->where('mst_company_id','=', $company_id)
			->where('state','=', 1)
			->select(['id','group_name'])
			->get();
        if (count($list_group)){
            // グループ存在する
            // 1:表示する
            $list_group_show = 1;

        }else{
            // グループ存在しない
            // 0:表示しない
            $list_group_show = 0;
        }

        return response()->json(['status' => true, 'itemsStamp' => $itemsStamp, 'list_group_show'=>$list_group_show,'list_group' => $list_group->toArray() ]);
    }

    /**
     * Add Stamp
     */
    function addStamps($company_id, Request $request){
        $user = \Auth::user();
        $items = $request->get('items');
        // 処理が成功した、プレビュー表示用
        $show_stamp_image = "";

        foreach($items as $item_info){
            $item = new $this->companyStamp;
            $fileName = $item_info['filename'];
            if(!str_ends_with($fileName,'.png')){
                $image = Image::make($item_info['stamp_image']);
                $image->encode('png');
                $imageBase64 = (string) $image->encode('data-url');
                $imageBase64 = explode(',', $imageBase64);
                $item_info['stamp_image'] = $imageBase64[1];
            }

            $item->fill($item_info);
//            $item->stamp_name = $item->stamp_name?$item->stamp_name: '';
            $item->stamp_name = !CommonUtils::isNullOrEmpty($item->stamp_name)?$item->stamp_name: '';
            $item->font = AppUtils::STAMP_DEFAULT_LABEL;
            $item->mst_company_id = $company_id;
            $item->del_flg = 0;
            $item->serial = '';
            $item->create_user = $user->getFullName();
            // PAC_5-477 共通印登録画面、指定した位置に日付が表示される
            // 1件追加の場合
            if (count($items) === 1){
                // 日付印の場合
                if($item->stamp_division !== 0){
                    // 日付で表示します
                    $show_stamp_image = $this->companyStampWithDate($item, $company_id);
                }else{
                    $show_stamp_image = $item->stamp_image;
                }
            }
            DB::beginTransaction();
            try{
                $item->save();
                $item->serial = AppUtils::generateStampSerial(AppUtils::STAMP_FLG_COMPANY, $item->id);
                $item->save();
                if($item_info['stamp_group']['group_id']){
                    // グループ指定あり
                    $stamp_group = new CompanyStampGroupsRelation();
                    $stamp_group->stamp_id = $item->id;
                    $stamp_group->group_id = $item_info['stamp_group']['group_id'];
                    $stamp_group->state = 1;
                    $stamp_group->create_user = $user->getFullName();
                    $stamp_group->update_user = $user->getFullName();
                    $stamp_group->save();

                }
                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                Log::error($e->getMessage().$e->getTraceAsString());
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
            }
        }

        if ($request->get('notify', false)){
            try{
                $companyAdmin = DB::table('mst_admin')->where('mst_company_id', $company_id)->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)->first();
                if ($companyAdmin){
                    // 管理者:共通印の一括登録通知
                    MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                        $companyAdmin->email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['COMPANY_STAMP_UPLOAD_ALERT']['CODE'],
                        // パラメータ
                        '',
                        // タイプ
                        AppUtils::MAIL_TYPE_ADMIN,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendMailAssignCompanyStamp.subject'),
                        // メールボディ
                        trans('mail.SendMailAssignCompanyStamp.body')
                    );
                }
            }catch(\Exception $e){
                Log::error($e->getMessage().$e->getTraceAsString());
            }
        }
        return response()->json(['status' => true, 'id' => isset($item)?$item->id:0, 'show_stamp_image' => $show_stamp_image,
                'message' => [__('message.success.create_stamp_company')]
        ]);
    }

    function updateStamp($company_id, $stamp_id, Request $request){
        $user = \Auth::user();
        $item_post  = $request->get('item');
        $item = $this->companyStamp->find($stamp_id);

        // PAC_5-477 原図
        $image_base64 = $item->stamp_image;
        $item->fill($item_post);
        $item->update_user = $user->getFullName();
        $item->stamp_name = $item->stamp_name?$item->stamp_name:'';

        // PAC_5-477 原図保存
        $item->stamp_image = $image_base64;
        // PAC_5-477 日付印の場合
        if($item->stamp_division !== 0){
            // 日付で表示します
            $show_stamp_image = $this->companyStampWithDate($item, $company_id);
        }else{
            $show_stamp_image = $image_base64;
        }
        // PAC_5-477 END
		DB::beginTransaction();
        try{

            if(isset($item_post['stamp_group']) && isset($item_post['stamp_group']['state'])){
                // 既存グループデータあり
                if($item_post['stamp_group']['group_id']){
                    // 指定あり(既存データ更新)
                    // 更新
                    $stamp_group = CompanyStampGroupsRelation::where('stamp_id',$stamp_id)->first();
                    $stamp_group->group_id = $item_post['stamp_group']['group_id'];
                    $stamp_group->state = 1;
                    $stamp_group->update_user = $user->getFullName();
                    $stamp_group->save();
                }else{
                    // 指定なし(stateを0に更新)
                    // 削除
					$stamp_group = CompanyStampGroupsRelation::where('stamp_id',$stamp_id)->first();
					$stamp_group->state = 0;
					$stamp_group->update_user = $user->getFullName();
					$stamp_group->save();
                }
            }else{
                // 既存グループデータなし
                // グループ選択時登録
                if(isset($item_post['stamp_group']['group_id'])){
            		//登録
					$stamp_group = new CompanyStampGroupsRelation();
					$stamp_group->stamp_id = $stamp_id;
					$stamp_group->group_id = $item_post['stamp_group']['group_id'];
					$stamp_group->state = 1;
					$stamp_group->create_user = $user->getFullName();
					$stamp_group->update_user = $user->getFullName();
					$stamp_group->save();
                }
            }

            $item->save();
			DB::commit();
        }catch(\Exception $e){
			DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.update_stamp_company')],
                 'message2' => $e->getMessage() ]);
        }
        return response()->json(['status' => true, 'id' => $item->id, 'show_stamp_image' => $show_stamp_image, 'message' => [__('message.success.update_stamp_company')]
            ]);
    }

    function deleteStamp($company_id, $stamp_id, Request $request){
        $user = \Auth::user();
        $itemStamp =  $this->companyStamp
            ->with('stampGroup')
            ->find($stamp_id);

        if (empty($itemStamp)) {
            return response()->json(['status' => false,
                'message' => [__('message.false.delete_stamp_company')]
            ]);
        }

        DB::beginTransaction();
        try{

            // PAC_5-1770 ▼
            $assignedCompanyStampInfos = DB::table('mst_assign_stamp')
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
                ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id','mst_assign_stamp.stamp_flg','mst_assign_stamp.state_flg',
                'mst_company.contract_edition','mst_company.company_name','mst_company.system_name')
                ->where('mst_assign_stamp.stamp_id',$stamp_id)
                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_COMPANY)
                ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
                ->get()
                ->toArray();

            foreach ($assignedCompanyStampInfos as $assignedCompanyStampInfo) {
                $this->updateUserState($assignedCompanyStampInfo, $user);
            }
            // PAC_5-1770 ▲

            $itemStamp->del_flg=1;
            $itemStamp->save();
            $this->assignStamp->where('stamp_id', $stamp_id)
                    ->where('stamp_flg', AppUtils::STAMP_FLG_COMPANY)
                    ->update(['state_flg' => AppUtils::STATE_DELETE,
                        'delete_at' => Carbon::now(),
                        'update_at' =>  Carbon::now(),
                        'update_user' =>  $user->email,
                    ]);

            if(empty(!$itemStamp->stampGroup)){
                // 既存グループデータあり
                // 削除
                $stamp_group = CompanyStampGroupsRelation::where('stamp_id',$stamp_id)->first();
                $stamp_group->state = 0;
                $stamp_group->update_user = $user->getFullName();
                $stamp_group->save();
            }

            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.delete_stamp_company')]
            ]);
        }
        return response()->json(['status' => true,
                'message' => [__('message.success.delete_stamp_company')]
        ]);
    }

    public function addConstrain($company_id){
        $user = \Auth::user();
        $appSetting = AppSettingConstraint::getAppSettingConstraint();


        $constraint = new $this->constraint;
        $constraint->fill(
            [
                'max_requests' => $appSetting->getSettingRequestsMax(),
                'max_document_size' => $appSetting->getSettingFileSize(),
                //'user_storage_size' => $appSetting->getSettingDiskCapacity(),
                'use_storage_percent' => $appSetting->getSettingStoragePercent(),
                'max_keep_days' => $appSetting->getSettingRetentionDay(),
                'delete_informed_days_ago' => $appSetting->getSettingDeleteDay(),
                'long_term_storage_percent' => $appSetting->getSettingLongTermStoragePercent(),
                'dl_max_keep_days' => $appSetting->getSettingDlMaxKeepDays(),
                'dl_after_proc' => $appSetting->getSettingDlAfterProc(),
                'dl_after_keep_days' => $appSetting->getSettingDlAfterKeepDays(),
                'dl_request_limit' => $appSetting->getSettingDlRequestLimit(),
                'dl_request_limit_per_one_hour' => $appSetting->getSettingDlRequestLimitPerOneHour(),
                'dl_file_total_size_limit' => $appSetting->getSettingDlFileTotalSizeLimit(),
                'max_ip_address_count' => $appSetting->getSettingMaxIpAddressCount(),
                'max_viwer_count' => $appSetting->getSettingMaxViwerCount(),
                'max_attachment_size' => $appSetting->getSettingMaxAttachmentSize(),
                'max_total_attachment_size' => $appSetting->getSettingMaxTotalAttachmentSize(),
                'max_attachment_count' => $appSetting->getSettingMaxAttachmentCount(),
                'sanitize_request_limit' => $appSetting->getSettingSanitizeRequestLimit(),
                'max_frm_document' => $appSetting->getSettingMaxFrmDocument(),
            ]
        );
        $constraint->create_user = $user->getFullName();
        $constraint->mst_company_id = $company_id;
        $constraint->save();
        $constraint->refresh();
        return $constraint;
    }

    public function addSpecial($company_id){
        $user = \Auth::user();

        $specialSiteReceiveSendAvailableState = new $this->specialSiteReceiveSendAvailableState;
        $specialSiteReceiveSendAvailableState->fill(
            [
                'is_special_site_receive_available' => 0,
                'is_special_site_send_available' => 0,
                'region_name' => null,
            ]
        );
        $specialSiteReceiveSendAvailableState->create_user = $user->getFullName();
        $specialSiteReceiveSendAvailableState->company_id = $company_id;
        $specialSiteReceiveSendAvailableState->save();
        return $specialSiteReceiveSendAvailableState;
    }

    public function resetpass($admin_id)
    {
        $item = DB::table('mst_admin')->where('id','=',$admin_id)->first();
        if($item){
            Session::flash('email', $item->email);
            $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_ADMIN, $item->email);
            return response()->json(['status' => true, 'email' => $item->email,
                    'message' => [
                        "指定したメールアドレスに、初期パスワードの通知メールを送信しました。",
                    ]
                ]);
        }else
            return response()->json(['status' => false, 'message' => ['管理者情報更新処理に失敗しました。']]);
    }

    /**
     * Company Stamp with date
     * PAC_5-477追加
     */
    public function companyStampWithDate($stamp, $company_id){
        // show date
        $date = date("Y").'/01/01';
        // date style
        $dstamp_style = DB::table('mst_company')->where('id', $company_id)
            ->select('dstamp_style')->pluck('dstamp_style')->first();
        if(!$dstamp_style)  {
            $dstamp_style = 'y.m.d';
        }
        $date = \App\Http\Utils\DateJPUtils::convert($date, $dstamp_style);
        $date_color = \App\Http\Utils\AppUtils::changeColorToRgbArray($stamp->date_color);
        $img_str = base64_decode($stamp->stamp_image);
        $png_img = imagecreatefromstring($img_str);
        //日付の画像データを作成
        $date_img = imagecreate($stamp->date_width, $stamp->date_height);
        imagecolortransparent($date_img, imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]));
        // font color red
        $fontColor = imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]);
        // font type
        $fontFile = public_path('fonts/arial.ttf');
        // get font size
        for ($size = 50; $size > 5; $size--) {
            $sizearray = imagettfbbox($size, 0, $fontFile, $date);
            $width = $sizearray[2] - $sizearray[6];
            $height = $sizearray[3] - $sizearray[7];
            if ($width <= ($stamp->date_width - 4) && $height <= ($stamp->date_height - 4)) {
                break;
            }
        }
        // image text
        imagettftext(
            $date_img,
            $size + 1,
            0,
            0,
            $stamp->date_height / 1.3,
            $fontColor,
            $fontFile,
            $date
        );

        imagealphablending($png_img, false);
        imagesavealpha($png_img, true);
        imagecopy($png_img, $date_img, $stamp->date_x, $stamp->date_y,  0, 0, $stamp->date_width, $stamp->date_height);

        ob_start();
        imagepng($png_img);
        $contents = ob_get_contents();
        ob_end_clean();

        return base64_encode($contents);
    }

    public function getListCompany(Request $request){
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'company_name' => $request->host_company_name,
            'orderBy' => $request->orderBy ? $request->orderBy : 'company_name',
            'orderDir' => $request->orderDir ? $request->orderDir : 'desc',
        ];

        $result = $client->get('company/search', [
            RequestOptions::JSON => $params
        ]);
        $response = json_decode((string)$result->getBody());
        if ($result->getStatusCode() != 200) {
            return response()->json(['status' => false,
                'message' => [$response->message]
            ]);
        }

        return response()->json(['status' => true, 'listCompany' =>  $response->data]);
    }

    public function uploadSamlMetadata(Request $request) {
        $file = $request->file('file');
        $mst_company_id = $request['mst_company_id'];
        $original_name = $file->getClientOriginalName();

        $xml = new \XMLReader();
        try{
            $xml->open($file->getRealPath());
            $certificate = '';
            $entityId = '';
            $ssoUrl = '';
            $logoutUrl = '';
            while ($xml->read()) {
                if ($xml->nodeType == \XMLReader::ELEMENT) {
                    if ($xml->localName == 'EntityDescriptor') {
                        $entityId = $xml->getAttribute('entityID');
                    }elseif ($xml->localName == 'X509Certificate') {
                        $certificate = preg_replace('~[\r\n]+~', ' ', $xml->readString());
                    }else if ($xml->localName == 'SingleSignOnService') {
                        $ssoUrl = $xml->getAttribute('Location');
                    }else if ($xml->localName == 'SingleLogoutService') {
                        $logoutUrl = $xml->getAttribute('Location');
                    }
                }
            }
            DB::table('mst_company')->where('id', $mst_company_id)->update(['saml_metadata' => json_encode(array('filename' =>  $original_name, 'certificate' => $certificate, 'entityId' => $entityId, 'ssoUrl' => $ssoUrl, 'logoutUrl' => $logoutUrl))]);
            return response()->json(['status' => true, 'filename' =>  $original_name]);
        }catch (\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'filename' =>  $original_name, 'message' => [$e->getMessage()]]);
        }finally {
            $xml->close();
        }
    }

    /**
     * importDepStamps
     * 部署名入り日付印CSVインポート
     * PAC_5-1544追加
     * @param Request $request
     * CSV項目[印面種類	レイアウト	フォント 色	上段1	上段2	下段1	下段2	メールアドレス	管理者メールアドレス タイムスタンプ]
     * @return void
     */
    public function importDepStamps($company_id,Request $request){
        $user = \Auth::user();
        Log::info('Import start company_id:'.$company_id);
        if (!$request->hasFile('file')) {
            //ファイル無し
            return response()->json(['status' => false, 'message' => ['CSV取込に失敗しました。時間をおいて再度お試しください。']]);
        }

        $file = $request->file('file');
        $path = $file->getRealPath();
        $csv_data = array_map('str_getcsv', file($path)); // doc csv
        // code対応 start
        $str = file_get_contents($file);
        $code = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'SJIS'));

        if ($code == 'CP936' || $code == 'SJIS' || $code == 'SJIS-win') {
            $csv_data = CommonUtils::convertCode('SJIS-win', 'UTF-8', $csv_data);
        }

        $total = count($csv_data);

        if(!$total){
            //ファイル内容無し
            return response()->json(['status' => false, 'message' => ['CSVデータ取得処理に失敗しました']]);
        }

        $num_total = 0;
        $arrReason = [];

        DB::beginTransaction();
        foreach($csv_data as $i => $row) {

            $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

            if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                continue;
            }elseif($i == 0){
                // 先頭行
                if(strpos($row[0], $bom) === 0){
                    // 先頭行BOM削除
                    $row[0] = ltrim($row[0], $bom);
                    if(empty($row[0])){
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => ['CSVデータ取得処理に失敗しました']]);
                    }
                }
            }

            $row_data = [
                'shohincd_num' => $row[0],
                'layout'       => $row[1],
                'font_num'     => $row[2],
                'color_num'    => $row[3],
                'face_up1'     => $row[4],
                'face_up2'     => $row[5],
                'face_down1'   => $row[6],
                'face_down2'   => $row[7],
                'user_mail'    => $row[8],
                'admin_mail'   => $row[9],
                'timestamp_flg'=> $row[10],
            ];

            //validation

            $rules = [
                'shohincd_num' => 'required|numeric|regex:/^[1-2]$/',
                'layout'       => 'required|numeric|regex:/^[1-5]$/',
                'font_num'     => 'required|numeric|regex:/^[1-3]$/',
                'color_num'    => 'required|numeric|regex:/^[1-6]$/',
                'face_up1'     => 'nullable|string|max:32',
                'face_up2'     => 'nullable|string|max:32',
                'face_down1'   => 'nullable|string|max:32',
                'face_down2'   => 'nullable|string|max:32',
                'user_mail'    => 'required|string',
                'admin_mail'   => 'required|string',
                'timestamp_flg'=> 'required|numeric|regex:/^[0-1]$/'
            ];

            $validator = Validator::make($row_data, $rules);
            if ($validator->fails())
            {
                $message = $validator->messages();
                $message_all = $message->all();
                $arrReason[] = ($i + 1).':入力形式が正しくありません';
                break;
            }
            $param = [];

            // 印面種類(XGL-15/XGFD-21)
            $param['shohincd'] = AppUtils::STAMP_TYPE_CSV[$row_data['shohincd_num'] - 1];
            // レイアウト
            $param['ptn']          = $row_data['layout'];
            // 書体(鯱旗楷書体W5/鯱旗古印体W5/鯱旗行書体W5)
            $param['font']         = AppUtils::STAMP_FONT_VALUE[$row_data['font_num'] - 1];
            // 色
            $param['color']        = sprintf('%02d', $row_data['color_num']);
            // 枠名（レイアウト毎に固定）(XGL15-E.drw/XG21-E.drw)
            $param['waku']         = isset(AppUtils::STAMP_WAKU[$param['shohincd']])?AppUtils::STAMP_WAKU[$param['shohincd']]:"";
            // 企業用ID　企業認証、企業別サービスを行うためのID
            $param['bizid']        = AppUtils::STAMP_BIZID;
            // 画像のサイズ
            $param['imgsize']      = AppUtils::STAMP_SIZE[$param['shohincd']];
            $param['fname']        = Date("YmdHis");
            // 文字化け判定用文字列（固定)
            $param['garbled']      = AppUtils::STAMP_GARBLED;

            $face_up1 = $face_up2 = $face_down1 = $face_down2 = "";

            $req_face_up1 = $row_data['face_up1'] ? $row_data['face_up1'] : "　";
            $req_face_up2 = $row_data['face_up2'] ? $row_data['face_up2'] : "　";
            $req_face_down1 = $row_data['face_down1'] ? $row_data['face_down1'] : "　";
            $req_face_down2 = $row_data['face_down2'] ? $row_data['face_down2'] : "　";

            //印面種類とレイアウトの組み合わせ確認
            $searchLayout = AppUtils::STAMP_LAYOUT[$param['shohincd']];
            $searchWord = array_search($param['ptn'],AppUtils::STAMP_LAYOUT_CSV);
            if(!in_array($searchWord,array_column($searchLayout,'value'))){
                $arrReason[] = ($i + 1).':印面種類とレイアウトの組み合わせが誤っています';
                break;
            }

            switch($param['ptn']){
                case AppUtils::STAMP_LAYOUT_CSV['E101']:
                    // 上下１行 XGL-15/XGFD-21
                    $face_up1 = $param['item1'] = $req_face_up1;
                    $face_down1 = $param['item2'] = $req_face_down1;
                    $param['ptn'] = 'E101';
                    break;
                case AppUtils::STAMP_LAYOUT_CSV['E0{0}1']:
                    // XGL-15 上下１行（子付き）
                    $face_up1 = $param['item1'] = $req_face_up1;
                    $face_down1 = $param['item2'] = $req_face_down1;
                    $face_down2 = $param['item3'] = $req_face_down2;
                    $length = mb_strlen($param['item2']);
                                    $length = $length>3?3:$length;
                    $param['ptn'] = "E0".$length."1";
                    break;
                case AppUtils::STAMP_LAYOUT_CSV['E102']:
                    // XGFD-21 下２行
                    $face_up1 = $param['item1'] = $req_face_up1;
                    $face_down1 = $param['item2'] = $req_face_down1;
                    $face_down2 = $param['item3'] = $req_face_down2;
                    $param['ptn'] = 'E102';
                    break;
                case AppUtils::STAMP_LAYOUT_CSV['E201']:
                    // XGFD-21 上２行
                    $face_up1 = $param['item1'] = $req_face_up1;
                    $face_up2 = $param['item2'] = $req_face_up2;
                    $face_down1 = $param['item3'] = $req_face_down1;
                    $param['ptn'] = 'E201';
                    break;
                case AppUtils::STAMP_LAYOUT_CSV['E202']:
                    // XGFD-21 上下２行
                    $face_up1 = $param['item1'] = $req_face_up1;
                    $face_up2 = $param['item2'] = $req_face_up2;
                    $face_down1 = $param['item3'] = $req_face_down1;
                    $face_down2 = $param['item4'] = $req_face_down2;
                    $param['ptn'] = 'E202';
                    break;
            }

            $Itemuser   = User::where('email', $row_data['user_mail'])->where('mst_company_id',$company_id)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
            $ItemAdmin  = Admin::where('email', $row_data['admin_mail'])->where('mst_company_id',$company_id)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();

            if(!$Itemuser){
                $num_error++;
                $arrReason[] = ($i + 1).':存在しない利用者を指定しています';
                continue;
            }
            if(!$ItemAdmin){
                $num_error++;
                $arrReason[] = ($i + 1).':存在しない管理者を指定しています';
                continue;
            }

            //department_stamp登録作業
            $client = new Client([  'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'], 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout') ]);
            $stamp = [];
            try{
                $result = $client->post(config('app.department_stamp_api_url'), ['form_params' => $param]);
                if($result->getStatusCode() == 200) {
                    $stamp = (string) $result->getBody();
                    $result = $client->get($stamp);
                    if($result->getStatusCode() == 200) {
                        $img_str = (string) $result->getBody();
                        $im = new \Imagick();
                        $im->readImageBlob($img_str);
                        $im->setImageResolution(72,72);
                        $im->setImageFormat("png");
                        $imgBuff = $im->getimageblob();

                        // ハンコ解像度調整
                        $stamp_image = StampUtils::stampClarity($imgBuff);

                        $stamp = [
                            'pribt_type' => $param['shohincd'],
                            'font' => ($row_data['font_num'] - 1),
                            'stamp_image' => $stamp_image,
                            'layout' => $param['ptn'],
                            'color' => $param['color'],
                            'face_up1' => $face_up1,
                            'face_up2' => $face_up2,
                            'face_down1' => $face_down1,
                            'face_down2' => $face_down2,
                            'width' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                            'height' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                            'real_width' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                            'real_height' => $param['imgsize'] * AppUtils::PX_TO_MICROMET/3,
                            'date_x'      => AppUtils::STAMP_DATE_X[$param['shohincd']],
                            'date_y'      => AppUtils::STAMP_DATE_Y[$param['shohincd']],
                            'date_width'  => AppUtils::STAMP_DATE_WIDTH[$param['shohincd']],
                            'date_height' => AppUtils::STAMP_DATE_HEIGHT[$param['shohincd']],
                            'state' => AppUtils::STATE_VALID,
                            'serial'=>''
                        ];

                        $departmentStamp = new DepartmentStamp();
                        $departmentStamp->fill($stamp);
                        $departmentStamp->save();

                        $departmentStamp->serial = AppUtils::generateStampSerial(AppUtils::STAMP_FLG_DEPARTMENT, $departmentStamp->id);
                        $departmentStamp->save();

                        //mst_assign_stamp登録作業

                        $stamp_id   = $departmentStamp->id;
                        $stamp_flg  = StampUtils::DEPART_STAMP;
                        $time_stamp_permission = $row_data['timestamp_flg'];
                        $state_flg  = AppUtils::STATE_VALID;

                        if(!$stamp_id){
                            $arrReason[] = ($i + 1).':印面作成に失敗しました';
                            break;
                        }
                        $arrInsert = [
                            'stamp_id' => $stamp_id, 'mst_user_id' => $Itemuser->id, 'display_no' => 0, 'state_flg' => $state_flg,
                            'stamp_flg' => $stamp_flg, 'create_user' => $user->getFullName(), 'time_stamp_permission' => $time_stamp_permission,
                            'mst_admin_id' => $ItemAdmin->id];

                        AssignStamp::insert($arrInsert);

                        $num_total++;
                    }else{
                        $arrReason[] = ($i + 1).':印面作成に失敗しました';
                        break;
                    }
                }else {
                    $arrReason[] = ($i + 1).':印面作成に失敗しました';
                    break;
                }
            }catch(\Exception $e){
                Log::error($e->getMessage().$e->getTraceAsString());
                $arrReason[] = ($i + 1).':印面作成に失敗しました';
                break;
            }
        }
        Log::info('Import finish company_id:'.$company_id);

        if(count($arrReason)){
            DB::rollBack();
        }else{
            DB::commit();
        }

        return response()->json(['status' => (count($arrReason) == 0), 'num_total'=>$num_total,'message' => $arrReason]);
    }
}
