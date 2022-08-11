<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Limit;

class LimitController extends AdminController
{

    private $model;

    private $model_type;

    private $modelPermission;

    //PAC_5-1115 メール認証定数定義
    const LINK_AUTH_FLG_OFF = 0;
    const LINK_AUTH_FLG_ON = 1;
    //PAC_5-1115 END

    public function __construct(Limit $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        $limit = $this->model->where('mst_company_id',$user->mst_company_id)->first();
        $company = DB::table('mst_company')
            ->select('esigned_flg', 'stamp_flg', 'mfa_flg', 'contract_edition','login_type',
            'addressbook_only_flag','enable_any_address_flg','skip_flg', 'with_box_flg','receive_plan_flg')
            ->where('id',$user->mst_company_id)
            ->first();

        // PAC_5-2328 START
        if($company->mfa_flg == AppUtils::MULTI_FACTOR_AUTH_VALID){
            $company->mfa_abled = 1;
        }else{
            $company->mfa_abled = 0;
        }
        // PAC_5-2328 END

        $this->assign('limit', $limit);
        $this->assign('company', $company);
        $this->setMetaTitle("制限設定");
        return $this->render('GlobalSetting.Settings.limit');
    }

    /**
     * Store a DateStamp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $setting = $request->all();

        $validator = Validator::make($request->all(), [
            'mfa_interval_hours' => 'required|numeric|min:1|max:24'
        ]);
        if ($validator->fails()){
            $message = $validator->messages();
            $message_all = $message->all();
            Log::debug($message_all);
            return response()->json(['status' => false,'message' => ["指定時間には1以上24以下の数字を入力してください"]]);
        }
        /*PAC_5-2616 S*/
        $company = DB::table('mst_company')->select('enable_any_address_flg')->where('id',$user->mst_company_id)->first();
        if (!$company->enable_any_address_flg && $setting['enable_any_address'] == AppUtils::STORAGE_ANY_ADDRESS_ROUTES){
            $setting['enable_any_address'] = AppUtils::STORAGE_ANY_ADDRESS_DEFAULT;
        }
        /*PAC_5-2616 E*/
        $limit = $this->model->where('mst_company_id',$user->mst_company_id)->first();
        $link_auth_flg_old = $limit->link_auth_flg;
        if(!$limit){
            $limit = new $this->model;
            $limit->fill($setting);
            $limit->mst_company_id = $user->mst_company_id;
            $limit->create_user = $user->getFullName();
        }else {
            $link_auth_flg_new = $setting['link_auth_flg'];
            $limit->update_user = $user->getFullName();
        }
        $limit->fill($setting);
        $limit->save();
        DB::table('mst_protection')->where('mst_company_id',$user->mst_company_id)
            ->update([
                'require_print' => $limit->require_print,
                'update_user' => $user->getFullName(),
                'update_at' => Carbon::now(),
            ]);
        if ($limit->enable_email_thumbnail === 0) {
            DB::table('mst_protection')->where('mst_company_id',$user->mst_company_id)
                ->update([
                    'enable_email_thumbnail' => 0,
                    'update_user' => $user->getFullName(),
                    'update_at' => Carbon::now(),
                ]);
        }
        //PAC_5-1115 メール認証機能をON→OFFに変更したとき、アクセスコード保護を有効にする
        if($link_auth_flg_old == self::LINK_AUTH_FLG_ON && $link_auth_flg_new == self::LINK_AUTH_FLG_OFF){
            DB::table('mst_protection')->where('mst_company_id',$user->mst_company_id)
                ->update([
                    'access_code_protection' => 1,
                    'update_user' => $user->getFullName(),
                    'update_at' => Carbon::now(),
                ]);
        }
        //PAC_5-1115 END
        return response()->json(['status' => true, 'message' => [__('message.success.save_limit')]]);
    }

}