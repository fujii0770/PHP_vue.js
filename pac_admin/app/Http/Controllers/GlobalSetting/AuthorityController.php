<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Models\Authority;
use App\Models\Permission;
use DB;
use Illuminate\Http\Request;

class AuthorityController extends AdminController
{

    private $model;

    private $permission;


    public function __construct(Authority $model, Permission $permission)
    {
        parent::__construct();
        $this->model        = $model;
        $this->permission  = $permission;
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show(Request $request)
    {
        $user = \Auth::user();

        $arrPermission  = $this->permission->getListMaster();
        $loggerCompany = AppUtils::getLoggedCompany(GwAppApiUtils::SCHEDULE_FLG);

        if(!$loggerCompany->stamp_flg){
            unset($arrPermission['全体設定']['タイムスタンプ設定']);
        }

        if(!$loggerCompany->signature_flg){
            unset($arrPermission['全体設定']['電子証明書設定']);
        }

        if(!$loggerCompany->expense_flg){
            unset($arrPermission['全体設定']['経費精算設定']);
        }

        if(!$loggerCompany->long_term_storage_flg || !$loggerCompany->long_term_storage_option_flg){
            unset($arrPermission['長期保管']['長期保管インデックス設定']);
        }

        if(!$loggerCompany->long_term_storage_flg || !$loggerCompany->long_term_folder_flg){
            unset($arrPermission['長期保管']['長期保管フォルダ管理']);
        }

        if(!$loggerCompany->long_term_storage_flg){
            unset($arrPermission['利用者設定']['監査用アカウント設定']);
            unset($arrPermission['長期保管']['長期保管設定']);
        }

        if (!$loggerCompany->receive_user_flg){
            unset($arrPermission['利用者設定']['受信専用利用者']);
        }

        if (!$loggerCompany->template_route_flg){
            unset($arrPermission['機能設定']['承認ルート']);
        }
        // box外部連携 無効の場合
        if(!$loggerCompany->box_enabled){
            unset($arrPermission['全体設定']['外部連携']);
        }

        if (!$loggerCompany->option_user_flg){
            unset($arrPermission['利用者設定']['グループウェア専用利用者']);
        }

        if (!$loggerCompany->dispatch_flg){
            unset($arrPermission['派遣管理']);
        }
        if (!$loggerCompany->chat_flg) {
            unset($arrPermission['ササッとTalk設定']);
        }

        $CompanyStampGroup = AppUtils::getStampGroup();
        if (!$CompanyStampGroup){
            unset($arrPermission['管理者設定']['共通印グループ管理者割当']);
        }
        /*PAC_5-2246 S*/
        if (!$loggerCompany->attendance_flg){
            unset($arrPermission['グループウェア設定']['タイムカード設定']);
        }

        if (!$loggerCompany->gw_flg){
            unset($arrPermission['グループウェア設定']['マスタ同期設定']);
            unset($arrPermission['グループウェア設定']['アプリ利用設定']);
        }

        /*PAC_5-2246 E*/
        $arrAuthority   = $this->model->where('mst_company_id',$user->mst_company_id)->get()->keyBy('code');
        if(!count($arrAuthority)){
            $this->model->initDefaultValue($user->mst_company_id, $user->getFullName());
            $arrAuthority   = $this->model->where('mst_company_id',$user->mst_company_id)->get()->keyBy('code');
        }
        if ($loggerCompany->contract_edition == AppUtils::CONTRACT_EDITION_GW){
            unset($arrPermission['全体設定']['日付印設定']);
            unset($arrPermission['全体設定']['共通印設定']);
            unset($arrPermission['全体設定']['制限設定']);
            unset($arrPermission['全体設定']['接続IP制限設定']);
            unset($arrPermission['全体設定']['保護設定']);
            unset($arrPermission['利用者設定']['利用者設定']);
            unset($arrPermission['利用者設定']['共通印割当']);
            unset($arrPermission['機能設定']['回覧一覧']);
            unset($arrPermission['長期保管']);
            unset($arrPermission['機能設定']['保存文書一覧']);
        }
        if(!$loggerCompany->attachment_flg){
            unset($arrPermission['機能設定']['添付ファイル一覧']);
        }
        if(!$loggerCompany->bizcard_flg){
            unset($arrPermission['機能設定']['名刺一覧']);
        }
        if(!$loggerCompany->template_flg){
            unset($arrPermission['機能設定']['テンプレート']);
        }
        if(!$loggerCompany->template_csv_flg){
            unset($arrPermission['機能設定']['回覧完了テンプレート一覧']);
        }
        $this->assign('arrPermission', $arrPermission);
        $this->assign('arrAuthority', $arrAuthority);
        $this->assign('companyEdition', $loggerCompany->contract_edition);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        $this->setMetaTitle("管理者権限初期値設定");
        return $this->render('GlobalSetting.Settings.authority');
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
        $actions = $request->get('actions');

        foreach($actions as $id => $values){
            $authority = $this->model->where('mst_company_id',$user->mst_company_id)->find($id);
            if(!$authority){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            if($authority->read_authority != 0){
                $authority->read_authority = isset($values['view'])?$values['view']:0;
            }
            if($authority->create_authority != 0){
                $authority->create_authority = isset($values['create'])?$values['create']:0;
            }
            if($authority->update_authority != 0){
                $authority->update_authority = isset($values['update'])?$values['update']:0;
            }
            if($authority->delete_authority != 0){
                $authority->delete_authority = isset($values['delete'])?$values['delete']:0;
            }

            $authority->save();
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_setting_authority')]]);
    }

}
