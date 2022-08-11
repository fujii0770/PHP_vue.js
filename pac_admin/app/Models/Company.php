<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;

class Company extends Model
{
    protected $table = 'mst_company';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_name', 'company_name_kana',
        'dstamp_style', 'domain', 'upper_limit', 'stamp_flg',
        'esigned_flg', 'use_api_flg', 'department_stamp_flg', 'trial_flg',
        'login_type', 'url_domain_id', 'saml_unique', 'saml_metadata','saml_logout',
        'url_help', 'url_contact', 'url_term', 'url_policy',
        'ip_restriction_flg', 'permit_unregistered_ip_flg', 'mfa_flg', 'enable_email_thumbnail',
        'state', 'create_user', 'update_user', 'long_term_storage_flg', 'max_usable_capacity',
        'view_notification_email_flg', 'updated_notification_email_flg', 'guest_company_flg',
        'guest_document_application', 'host_app_env', 'host_contract_server', 'mst_company_id', 'host_company_name', 'contract_edition',
        'system_name', 'time_stamp_issuing_count', 'template_flg','trial_time', 'signature_flg', 'trial_times', 'phone_app_flg', 'trial_times_update_at', 'template_search_flg',
        'enable_email', 'email_format','box_enabled','portal_flg','received_only_flg','rotate_angle_flg','template_csv_flg','template_edit_flg', 'template_route_flg','attachment_flg','test_company','add_file_limit',
        'hr_flg','sanitizing_flg','addressbook_only_flag','gw_flg','repage_preview_flg','pdf_annotation_flg', 'bizcard_flg', 'long_term_storage_option_flg','remark_message','usage_flg','auto_save','convenient_flg','local_stamp_flg'

        ,'multiple_department_position_flg','timestamps_count','option_contract_flg','option_contract_count','old_contract_flg','default_stamp_flg','confidential_flg'
        ,'frm_srv_flg','option_user_flg','with_box_flg','time_stamp_assign_flg','long_term_storage_delete_flg','dispatch_flg','timestamp_notified_flg','receive_user_flg','convenient_upper_limit',"skip_flg"
        /*PAC_5-1698 S*/
        ,'user_plan_flg','auto_save_num','long_term_folder_flg','long_term_default_folder_id','form_user_flg'
        /*PAC_5-1698 E*/
        ,'circular_list_csv'

        /*PAC_5-2353 S*/
        ,'is_together_send'
        /*PAC_5-2353 E*/
        /*PAC_5-2616 S*/
        ,'enable_any_address_flg'
        /*PAC_5-2616 E*/

        /*PAC_5-2758 S*/
        ,'is_show_current_company_stamp'
        /*PAC_5-2758 S*/
        ,'expense_flg'
        ,'attendance_system_flg'
        /*PAC_5-3028 S*/
        ,'contract_edition_sample_flg'
        ,'edition_id'
        ,'chat_flg', 'chat_trial_flg','without_email_flg'
        /* PAC_5-2663 */
        , 'receive_plan_flg','sticky_note_flg'
        ,'regular_at'
        /* PAC_5-2912 */
        ,'mst_sanitizing_line_id'
        /* PAC_5-3455 */
        ,'long_term_storage_move_flg'
        ,'template_approval_route_flg'
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
    protected $casts = [];

    protected $attributes = [
        'permit_unregistered_ip_flg' => 0,
    ];

    public function rules($id = 'null'){
        return [
            'company_name' => 'required|max:256',
            'company_name_kana' => 'required|max:256',
            'domain' => 'required|starts_with:@,＠',
            'upper_limit' => 'required|numeric|min:0',
            'esigned_flg' => 'required|numeric',
            'use_api_flg' => 'required|numeric|max:128',
            'department_stamp_flg' => 'required|numeric|max:128',
            'login_type' => 'required|numeric|max:128',
            'url_domain_id' => 'nullable|max:20|regex:/^[a-zA-Z0-9]*$/|unique:mst_company,url_domain_id,'.$id.',id,login_type,1,state,1',
            'saml_unique' => 'nullable|max:100',
            'saml_metadata' => 'nullable|string',
            'url_help' => 'nullable|string|max:2048',
            'url_contact' => 'nullable|string|max:2048',
            'url_term' => 'nullable|string|max:2048',
            'url_policy' => 'nullable|string|max:2048',
            'state' => 'required|numeric',
            'ip_restriction_flg' => 'required|numeric',
            'permit_unregistered_ip_flg' => 'sometimes|required|numeric',
            'mfa_flg' => 'required|numeric',
            'max_usable_capacity' => 'exclude_unless:long_term_storage_flg,1|numeric|min:1',
            'long_term_storage_flg' => 'required|numeric',
            'long_term_storage_option_flg' => 'required|numeric',
            'long_term_folder_flg' => 'required|numeric',
            'guest_company_flg' => 'required|numeric|max:1',
            'guest_document_application' => 'required|numeric|max:1',
            'host_company_name' => 'nullable|string|max:256',
            'host_app_env' => 'nullable|numeric',
            'host_contract_server' => 'nullable|numeric',
            'view_notification_email_flg' => 'numeric|max:1',
            'updated_notification_email_flg' => 'numeric|max:1',
            'time_stamp_issuing_count' => 'required|numeric|max:1',
            'system_name' => 'required|string|max:256',
            'contract_edition' => 'required|numeric',
            'template_flg' => 'required|numeric',
            'trial_flg' => 'required|numeric',
            'trial_time' => 'numeric',
            'trial_times' => 'numeric',
            'signature_flg' => 'required|numeric',
            'phone_app_flg' => 'required|numeric',
            'enable_email_thumbnail' => 'required|numeric',
            'enable_email' => 'required|numeric|min:0|max:1',
            'email_format' => 'required|numeric|min:0|max:1',
            'box_enabled' => 'numeric',
            'portal_flg' => 'required|numeric',
            'gw_flg' => 'required|numeric',
            'received_only_flg' => 'required|numeric',
            'rotate_angle_flg' => 'required|numeric',
            'template_search_flg' => 'required|numeric',
            'template_csv_flg' => 'required|numeric',
            'template_edit_flg' => 'required|numeric',
            'template_route_flg' => 'required|numeric',
            'hr_flg' => 'required|numeric',
            //'passreset_type' => 'numeric',
            'sanitizing_flg' => 'required|numeric',
            'addressbook_only_flag' => 'required|numeric',
            'repage_preview_flg' => 'required|numeric',
            'bizcard_flg' => 'required|numeric',
            'remark_message' => 'max:64',
            'attachment_flg' => 'required|numeric',
            'auto_save' => 'required|numeric',
            'test_company' => 'numeric',
            'add_file_limit' => 'required|numeric|min:0',
            'user_plan_flg' => 'required|numeric|max:1',
            'frm_srv_flg' =>'required|numeric',
            'option_user_flg' =>'required|numeric',
            'local_stamp_flg' => 'required|numeric',
            'with_box_flg' => 'required|numeric',
            'timestamps_count' => 'required|numeric|min:0',
            'option_contract_flg' => 'required|numeric|min:0',
            'option_contract_count' => 'required|numeric|min:0',
            'old_contract_flg' => 'required|numeric|min:0',
            'default_stamp_flg' => 'required|numeric|min:0',
            'confidential_flg' => 'required|numeric|min:0',
            /*PAC_5-2246 START*/
            'attendance_flg' => 'numeric|min:0|max:1',
            /*PAC_5-2246 END*/
            'time_stamp_assign_flg' => 'required|numeric',
            'dispatch_flg' => 'required|numeric',
            'timestamp_notified_flg' => 'numeric|min:0|max:1',
            'receive_user_flg' => 'required|numeric',
            'convenient_upper_limit' => 'numeric|min:0',
            'circular_list_csv' => 'numeric|min:0|max:1',
            'skip_flg' => "required|numeric",
            'is_together_send' => 'numeric|min:0',
            /*PAC_5-2616 S*/
            'enable_any_address_flg' => 'numeric|min:0|max:1',
            /*PAC_5-2616 E*/
            'form_user_flg' => 'numeric|min:0|max:1',
            /*PAC_5-2758 S*/
            'is_show_current_company_stamp' => 'numeric|min:0|max:1',
            /*PAC_5-2758 S*/
            'receive_plan_flg' => 'numeric|min:0|max:1',
            'to_do_list_flg' => 'nullable|numeric|min:0|max:1',
            'to_do_list_limit_flg' => 'nullable|numeric|min:0|max:1',
            'to_do_list_buy_count' => 'exclude_unless:to_do_list_flg,1|exclude_unless:to_do_list_limit_flg,0|numeric|min:1',
            'template_approval_route_flg' => 'required|numeric',
            'expense_flg' =>'required|numeric',
            'shared_scheduler_flg' => 'numeric|min:0|max:1',
        ];
    }

    public function constraint()
    {
        return $this->hasOne('App\Models\Constraint', 'mst_company_id');
    }

    public function specialSiteReceiveSendAvailableState()
    {
        return $this->hasOne('App\Models\SpecialSiteReceiveSendAvailableState', 'company_id');
    }

    public function companyStamps()
    {
        return $this->hasMany('App\Models\CompanyStamp', 'mst_company_id')->where('del_flg', '=', 0);
    }

    public function companyUsers()
    {
        return $this->hasMany('App\Models\User', 'mst_company_id')->where('state_flg', '=', AppUtils::STATE_VALID);
    }

    public function companyAdmins()
    {
        return $this->hasMany('App\Models\Admin', 'mst_company_id')->where('state_flg', '<>', AppUtils::STATE_DELETE);
    }

    public function assignedStamps()
    {
        return $this->hasManyThrough('App\Models\AssignStamp', 'App\Models\User', 'mst_company_id', 'mst_user_id', 'id');
    }

    public function assignStamps()
    {
        return $this->hasMany('App\Models\AssignStamp', 'stamp_id')->where('state_flg', '=', AppUtils::STATE_VALID);
    }

    public function pdfNumbers()
    {
        return $this->hasManyThrough('App\Models\CompanyStampOrderHistory', 'App\Models\Admin','mst_company_id','mst_admin_id','id');
    }

    public static function getCompanyStampCount($intCompanyID){
        $intCompanyCount = DB::table("mst_assign_stamp")
                                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                                ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
                                ->where("mst_company.id",$intCompanyID)
                                ->whereIn('mst_assign_stamp.stamp_flg',[AppUtils::STAMP_FLG_NORMAL,AppUtils::STAMP_FLG_COMPANY,AppUtils::STAMP_FLG_DEPARTMENT])
                                ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID,AppUtils::STATE_WAIT_ACTIVE])
                                ->where('mst_user.state_flg',AppUtils::STATE_VALID)
                                ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
                                ->count();

        return $intCompanyCount ? $intCompanyCount : 0;
    }

    public static function getCompanyConvenientStampCount($intCompanyID){
        $companyConvenientStampCount = DB::table("mst_assign_stamp")
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
            ->where("mst_company.id",$intCompanyID)
            ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
            ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
            ->count();
        return $companyConvenientStampCount ? $companyConvenientStampCount : 0;
    }

    public static function stampIsOverByCount($intCompanyID,$intCompanyStampLimitNum = 0){
        if(!$intCompanyStampLimitNum){
            $intCompanyStampLimitNum = self::getCompanyStampLimit($intCompanyID);
        }
        $intCompanyStampCount = self::getCompanyStampCount($intCompanyID);
        return $intCompanyStampCount > $intCompanyStampLimitNum ? 1 : 0;
    }

    public static function getCompanyStampLimit($intCompanyID){
        $intCompanyStampLimitNum = DB::table("mst_company")->where("id",$intCompanyID)->first('upper_limit')->upper_limit;
        return $intCompanyStampLimitNum;
    }

    public static function getCompanyConvenientStampLimit($intCompanyID) {
        $companyConvenientStampLimitNum = DB::table("mst_company")->where("id",$intCompanyID)->first('convenient_upper_limit')->convenient_upper_limit;
        return $companyConvenientStampLimitNum;
    }

    /**
     * greater than or equal to (GE)
     * @param $intCompanyID
     * @return int
     */
    public static function getGEByCompanyLimitAndUserCount($intCompanyID){
        $arrCount = self::getCompanyStampLimitAndUserStampCount($intCompanyID);
        return $arrCount['intUserStampCount'] >= $arrCount['intCompanyStampLimit'] ? 1 : 0;
    }

    /**
     * greater than
     * @param $intCompanyID
     * @return int
     */
    public static function getGreaterThanByCompanyLimitAndUserCount($intCompanyID){
        $arrCount = self::getCompanyStampLimitAndUserStampCount($intCompanyID);
        return $arrCount['intUserStampCount'] > $arrCount['intCompanyStampLimit'] ? 1 : 0;
    }

    /**
     * get users stamp count and company stamp limit
     * @param $intCompanyID company_id
     * @return array
     */
    public static function getCompanyStampLimitAndUserStampCount($intCompanyID){
        // all Effective User stamp count
        $intUserStampCount = self::getCompanyStampCount($intCompanyID);
        // company stamp magnitude setting
        $intCompanyStampLimit = self::getCompanyStampLimit($intCompanyID);
        return [
            'intUserStampCount' => $intUserStampCount,
            'intCompanyStampLimit' => $intCompanyStampLimit,
        ];
    }

    /**
     * get users convenient stamp count and company convenient stamp limit
     * @param $intCompanyID
     * @return array
     */
    public static function getCompanyConvenientStampLimitCount($intCompanyID){
        // all Effective User stamp count
        $userConvenientStampCount = self::getCompanyConvenientStampCount($intCompanyID);
        // company stamp magnitude setting
        $companyConvenientStampLimit = self::getCompanyConvenientStampLimit($intCompanyID);
        return [
            'userConvenientStampCount' => $userConvenientStampCount,
            'companyConvenientStampLimit' => $companyConvenientStampLimit,
        ];
    }

    /**
     * 有効利用者の共通印割当
     * @param $mst_company_id int 会社のID
     * @return int
     */
    public static function getValidCommonStampsCount(int $mst_company_id): int
    {
        $commonStampCount = DB::table("mst_assign_stamp")
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
            ->where("mst_company.id",$mst_company_id)
            ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_COMPANY)
            ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
            ->count();
        Log::debug($commonStampCount);
        return $commonStampCount ?: 0;
    }

    /**
     * 有効利用者の部署名入日付印割当
     * @param $mst_company_id int 会社のID
     * @return int
     */
    public static function getValidDepartmentStampCount(int $mst_company_id): int
    {
        $departmentStampCount = DB::table("mst_assign_stamp")
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
            ->where("mst_company.id",$mst_company_id)
            ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_DEPARTMENT)
            ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID,AppUtils::STATE_WAIT_ACTIVE])
            ->where('mst_user.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
            ->count();
        return $departmentStampCount ?: 0;
    }

    /**
     * 有効利用者の氏名印割当
     * @param $mst_company_id int 会社のID
     * @return int
     */
    public static function getNameStampCount(int $mst_company_id): int
    {
        $nameStampCount = DB::table("mst_assign_stamp")
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
            ->join('mst_stamp','mst_assign_stamp.stamp_id','=','mst_stamp.id')
            ->where("mst_company.id",$mst_company_id)
            ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_NORMAL)
            ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID,AppUtils::STATE_WAIT_ACTIVE])
            ->where('mst_user.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
            ->where('mst_stamp.stamp_division',AppUtils::STAMP_DIVISION_NAME)
            ->count();
        return $nameStampCount ?: 0;
    }

    /**
     * 有効利用者の日付印割当
     * @param $mst_company_id int 会社のID
     * @return int
     */
    public static function getDateStampCount(int $mst_company_id): int
    {
        $dateStampCount = DB::table("mst_assign_stamp")
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->join("mst_company","mst_user.mst_company_id","=","mst_company.id")
            ->join('mst_stamp','mst_assign_stamp.stamp_id','=','mst_stamp.id')
            ->where("mst_company.id",$mst_company_id)
            ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_NORMAL)
            ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID,AppUtils::STATE_WAIT_ACTIVE])
            ->where('mst_user.state_flg',AppUtils::STATE_VALID)
            ->where('mst_user.option_flg',AppUtils::USER_NORMAL)
            ->where('mst_stamp.stamp_division',AppUtils::STAMP_DIVISION_DATE)
            ->count();
        return $dateStampCount ?: 0;
    }

    /**
     * 無害化要求ファイル数
     * @param int $mst_company_id
     * @return int
     */
    public static function getSanitizingLimit(int $mst_company_id): int
    {
        $sanitizingLimit = DB::table("mst_sanitizing_line")
            ->join("mst_company","mst_company.mst_sanitizing_line_id","=","mst_sanitizing_line.id")
            ->where("mst_company.id",$mst_company_id)
            ->first('mst_sanitizing_line.sanitize_request_limit');
        return $sanitizingLimit ? $sanitizingLimit->sanitize_request_limit : 0;
    }
}
