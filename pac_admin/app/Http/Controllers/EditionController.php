<?php

namespace App\Http\Controllers;

use App\Http\Utils\PermissionUtils;
use App\Models\Company;
use App\Models\Edition;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;
use App\Http\Utils\AppUtils;
use App\Http\Controllers\AdminController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Session;
use Storage;
use App\Http\Utils\MailUtils;
use Forrest;
use Intervention\Image\Facades\Image;

class EditionController extends AdminController
{
    private $model;
    private $companyinfo;

    public function __construct(Edition $model, Company $companyinfo)
    {
        parent::__construct();
        $this->model = $model;
        $this->companyinfo = $companyinfo;
    }

    /**
     * 契約edition
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $items = [];
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        if (!array_search($limit, config('app.page_list_limit'))) {
            $limit = config('app.page_limit');
        }
        $items = $this->model
            ->select('mst_contract_edition.id','mst_company.edition_id','mst_contract_edition.contract_edition_name','mst_contract_edition.state_flg','mst_contract_edition.memo','mst_company.contract_edition')
            ->join('mst_company','mst_contract_edition.id','=','mst_company.edition_id')
            ->where('mst_contract_edition.state_flg','<>', AppUtils::EDITION_D_STATE)
            ->where('mst_company.contract_edition_sample_flg','=',AppUtils::EDITION_SAMPLE_T)
            ->orderBy($orderBy, $orderDir)
            ->paginate($limit)
            ->appends(request()->input());
        $items_info = $this->companyinfo
            ->where('contract_edition_sample_flg','=',AppUtils::EDITION_SAMPLE_T);

        $this->assign('items', $items);
        $this->assign('items_info', $items_info);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir) == "asc" ? "desc" : "asc");
        $this->setMetaTitle("契約Edition");

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        return $this->render('Edition.index');
    }

    /**
     * show the Edition info.
     *
     * @param int $id
     */
    public function show($id)
    {
        $user = \Auth::user();

        $editionmodel = $this->model
            ->select('mst_company.is_show_current_company_stamp','mst_contract_edition.state_flg','mst_contract_edition.memo','mst_company.edition_id','mst_contract_edition.id','mst_contract_edition.contract_edition_name','mst_company.department_stamp_flg','mst_company.template_route_flg','mst_company.rotate_angle_flg','mst_company.phone_app_flg','mst_company.attachment_flg','mst_company.portal_flg','mst_company.contract_edition'
                ,'mst_company.convenient_flg','mst_company.usage_flg','mst_company.convenient_upper_limit','mst_company.default_stamp_flg','mst_company.confidential_flg','mst_company.esigned_flg','mst_company.ip_restriction_flg','mst_company.signature_flg','mst_company.permit_unregistered_ip_flg'
                ,'mst_company.stamp_flg','mst_company.repage_preview_flg','mst_company.timestamps_count','mst_company.box_enabled','mst_company.time_stamp_issuing_count','mst_company.mfa_flg','mst_company.long_term_storage_flg','mst_company.template_flg','mst_company.long_term_storage_option_flg','mst_company.template_search_flg'
                ,'mst_company.long_term_folder_flg','mst_company.max_usable_capacity','mst_company.template_csv_flg','mst_company.hr_flg','mst_company.template_edit_flg','mst_company.multiple_department_position_flg','mst_company.option_user_flg','mst_company.user_plan_flg','mst_company.receive_user_flg'
                ,'mst_company.skip_flg','mst_company.form_user_flg','mst_company.frm_srv_flg','mst_company.bizcard_flg','mst_company.local_stamp_flg','mst_company.with_box_flg','mst_company.dispatch_flg','mst_company.attendance_system_flg','mst_company.circular_list_csv','mst_company.is_together_send','mst_company.enable_any_address_flg'
                ,'mst_company.sanitizing_flg','mst_company.enable_email','mst_company.email_format','mst_company.received_only_flg','mst_company.pdf_annotation_flg','mst_company.addressbook_only_flag','mst_company.view_notification_email_flg','mst_company.updated_notification_email_flg','mst_company.enable_email_thumbnail','mst_company.template_approval_route_flg'
                ,'mst_contract_edition.board_flg','mst_contract_edition.scheduler_flg','mst_contract_edition.scheduler_limit_flg','mst_contract_edition.scheduler_buy_count','mst_contract_edition.caldav_flg','mst_contract_edition.caldav_limit_flg','mst_company.guest_company_flg'
                ,'mst_contract_edition.caldav_buy_count','mst_contract_edition.google_flg','mst_contract_edition.outlook_flg','mst_contract_edition.apple_flg','mst_contract_edition.file_mail_flg','mst_contract_edition.faq_board_flg'
                ,'mst_contract_edition.file_mail_flg','mst_contract_edition.file_mail_limit_flg','mst_contract_edition.file_mail_buy_count','mst_contract_edition.file_mail_extend_flg','mst_contract_edition.attendance_flg','mst_contract_edition.attendance_limit_flg','mst_contract_edition.attendance_buy_count','mst_contract_edition.faq_board_limit_flg','mst_contract_edition.faq_board_buy_count','mst_contract_edition.shared_scheduler_flg'
                ,'mst_contract_edition.to_do_list_flg','mst_contract_edition.to_do_list_limit_flg','mst_contract_edition.to_do_list_buy_count','mst_contract_edition.address_list_flg','mst_contract_edition.address_list_limit_flg','mst_contract_edition.address_list_buy_count')
            ->join('mst_company','mst_contract_edition.id','=','mst_company.edition_id')
            ->where('mst_contract_edition.id',$id)
            ->where('mst_company.edition_id',$id)->where('mst_company.contract_edition_sample_flg','=',AppUtils::EDITION_SAMPLE_T)
            ->first();

        if(!$editionmodel){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        return response()->json(['status' => true,'item' => $editionmodel]);
    }

    /**
     * Store a Edition.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $item_in = $request->get('item');

        $validator = Validator::make($item_in, $this->model->rules());

        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        // 重複チェック
        $idcheck = DB::table('mst_contract_edition')
            ->join('mst_company', 'mst_contract_edition.id', '=', 'mst_company.edition_id')
            ->where('mst_contract_edition.state_flg','<>', AppUtils::EDITION_D_STATE)
            ->where('mst_company.contract_edition',$item_in['contract_edition'])
            ->first();
        if ($idcheck) {
            return response()->json(['status' => false, 'message' => [__('message.warning.select_id_exist')]]);
        }

        $item_edi = new $this->model;
        $item_edi->fill($item_in);
        $item_edi->create_user = $user->getFullName();


        try{
            DB::beginTransaction();

            $item_edi->save();

            $this->companyinfo
                ->insert([
                "department_stamp_flg" => $item_in["department_stamp_flg"],
                "template_route_flg" => $item_in["template_route_flg"],
                "rotate_angle_flg" => $item_in["rotate_angle_flg"],
                "phone_app_flg" => $item_in["phone_app_flg"],
                "attachment_flg" => $item_in["attachment_flg"],
                "portal_flg" => $item_in["portal_flg"],
                "contract_edition" => $item_in["contract_edition"],
                "convenient_flg" => $item_in["convenient_flg"],
                "usage_flg" => $item_in["usage_flg"],
                "convenient_upper_limit" => $item_in["convenient_upper_limit"],
                "default_stamp_flg" => $item_in["default_stamp_flg"],
                "confidential_flg" => $item_in["confidential_flg"],
                "esigned_flg" => $item_in["esigned_flg"],
                "ip_restriction_flg" => $item_in["ip_restriction_flg"],
                "signature_flg" => $item_in["signature_flg"],
                "permit_unregistered_ip_flg" => $item_in["permit_unregistered_ip_flg"],
                "stamp_flg" => $item_in["stamp_flg"],
                "repage_preview_flg" => $item_in["repage_preview_flg"],
                "timestamps_count" => $item_in["timestamps_count"],
                "box_enabled" => $item_in["box_enabled"],
                "time_stamp_issuing_count" => $item_in["time_stamp_issuing_count"],
                "mfa_flg" => $item_in["mfa_flg"],
                "long_term_storage_flg" => $item_in["long_term_storage_flg"],
                "template_flg" => $item_in["template_flg"],
                "long_term_storage_option_flg" => $item_in["long_term_storage_option_flg"],
                "template_search_flg" => $item_in["template_search_flg"],
                "long_term_folder_flg" => $item_in["long_term_folder_flg"],
                "max_usable_capacity" => $item_in["max_usable_capacity"],
                "template_csv_flg" => $item_in["template_csv_flg"],
                "hr_flg" => $item_in["hr_flg"],
                "template_edit_flg" => $item_in["template_edit_flg"], 
                "template_approval_route_flg" => $item_in["template_approval_route_flg"],
                "multiple_department_position_flg" => $item_in["multiple_department_position_flg"],
                "option_user_flg" => $item_in["option_user_flg"],
                "user_plan_flg" => $item_in["user_plan_flg"],
                "receive_user_flg" => $item_in["receive_user_flg"],
                "skip_flg" => $item_in["skip_flg"],
                "form_user_flg" => $item_in["form_user_flg"],
                "frm_srv_flg" => $item_in["frm_srv_flg"],
                "bizcard_flg" => $item_in["bizcard_flg"],
                "local_stamp_flg" => $item_in["local_stamp_flg"],
                "with_box_flg" => $item_in["with_box_flg"],
                "dispatch_flg" => $item_in["dispatch_flg"],
                "attendance_system_flg" => $item_in["attendance_system_flg"],
                "circular_list_csv" => $item_in["circular_list_csv"],
                "is_together_send" => $item_in["is_together_send"],
                "enable_any_address_flg" => $item_in["enable_any_address_flg"],
                "sanitizing_flg" => $item_in["sanitizing_flg"],
                "enable_email" => $item_in["enable_email"],
                "email_format" => $item_in["email_format"],
                "received_only_flg" => $item_in["received_only_flg"],
                "pdf_annotation_flg" => $item_in["pdf_annotation_flg"],
                "addressbook_only_flag" => $item_in["addressbook_only_flag"],
                "view_notification_email_flg" => $item_in["view_notification_email_flg"],
                "updated_notification_email_flg" => $item_in["updated_notification_email_flg"],
                "enable_email_thumbnail" => $item_in["enable_email_thumbnail"],
                "is_show_current_company_stamp" => $item_in["is_show_current_company_stamp"],
                "guest_company_flg" => $item_in["guest_company_flg"],
                "contract_edition_sample_flg" => AppUtils::EDITION_SAMPLE_T,
                "edition_id" => $item_edi->id,
                "company_name" => '',
                "company_name_kana" => '',
                "domain" => '',
                "dstamp_style" => '',
                "upper_limit" => 0,
                "use_api_flg" => 0,
                "login_type" => 0,
                "state" => 1,
                "create_at"=> \Carbon\Carbon::now(),
                "create_user"=> $user->getFullName(),
            ]);

            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.create_Edition')]]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /**
     * update a Edition.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     */

    function update($id,Request $request)
    {
        $user = \Auth::user();

        $item_in = $request->get('item');

        $validator = Validator::make($item_in, $this->model->rules());

        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        // 重複チェック
        $idcheck = DB::table('mst_contract_edition')
            ->join('mst_company', 'mst_contract_edition.id', '=', 'mst_company.edition_id')
            ->where('mst_contract_edition.id','<>',$item_in['id'])
            ->where('state_flg','<>', AppUtils::EDITION_D_STATE)
            ->where('mst_company.contract_edition',$item_in['contract_edition'])
            ->first();
        if ($idcheck) {
            return response()->json(['status' => false, 'message' => [__('message.warning.select_id_exist')]]);
        }

        $item_info = $this->companyinfo->where('edition_id',$id)->first();
        $item_info->fill($item_in);
        $item_info->update_at = Carbon::now();
        $item_info->update_user = $user->getFullName();

        try{
            DB::beginTransaction();
            $item_info->save();

            $this->model
                ->where('id',$item_info['edition_id'])
                ->update([
                    "contract_edition_name" => $item_in["contract_edition_name"],
                    "memo" => $item_in["memo"],
                    "state_flg" => $item_in["state_flg"],
                    "board_flg" => $item_in["board_flg"],
                    "pdf_annotation_flg" => $item_in["pdf_annotation_flg"],
                    "scheduler_flg" => $item_in["scheduler_flg"],
                    "scheduler_limit_flg" => $item_in["scheduler_limit_flg"],
                    "scheduler_buy_count" => $item_in["scheduler_buy_count"],
                    "caldav_flg" => $item_in["caldav_flg"],
                    "caldav_limit_flg" => $item_in["caldav_limit_flg"],
                    "caldav_buy_count" => $item_in["caldav_buy_count"],
                    "google_flg" => $item_in["google_flg"],
                    "outlook_flg" => $item_in["outlook_flg"],
                    "apple_flg" => $item_in["apple_flg"],
                    "file_mail_flg" => $item_in["file_mail_flg"],
                    "file_mail_limit_flg" => $item_in["file_mail_limit_flg"],
                    "file_mail_buy_count" => $item_in["file_mail_buy_count"],
                    "file_mail_extend_flg" => $item_in["file_mail_extend_flg"],
                    "attendance_flg" => $item_in["attendance_flg"],
                    "attendance_limit_flg" => $item_in["attendance_limit_flg"],
                    "attendance_buy_count" => $item_in["attendance_buy_count"],
                    "faq_board_flg" => $item_in["faq_board_flg"],
                    'update_at' => Carbon::now(),
                    'update_user' => $user->getFullName(),
                    "faq_board_limit_flg"=> $item_in["faq_board_limit_flg"],
                    "faq_board_buy_count"=> $item_in["faq_board_buy_count"],
                    "shared_scheduler_flg"=> $item_in["shared_scheduler_flg"],
                    'to_do_list_flg' => $item_in["to_do_list_flg"],
                    'to_do_list_limit_flg' => $item_in["to_do_list_limit_flg"],
                    'to_do_list_buy_count' => $item_in["to_do_list_buy_count"],
                    'address_list_flg' => $item_in["address_list_flg"],
                    'address_list_limit_flg' => $item_in["address_list_limit_flg"],
                    'address_list_buy_count' => $item_in["address_list_buy_count"],
                ]);


            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.update_Edition')]]);
        }catch(Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }
    /**
     * 契約エディション削除
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = \Auth::user();

        // ０：Standaradパック 削除不可
        if($id == 1){
            return response()->json(['status' => false, 'message' => [__('message.warning.select_id_Standarad')]]);
        }

        $item = $this->model->find($id);
        if(!$item){
            Log::warning("Delete edition not found. id : ".$id);
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        // 更新項目編集
        $item->state_flg = AppUtils::EDITION_D_STATE;
        $item->update_at = Carbon::now();
        $item->update_user = $user->getFullName();

        // データ更新
        try{
            DB::beginTransaction();
            $item->save();

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::warning("Delete edition failed. id : ".$id);
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
        return response()->json(['status' => true]);
    }
}
