<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Models\Permission;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionsController extends AdminController
{

    private $model;

    public function __construct(Permission $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Return list Permission for master
     *
     * @return JsonResponse
     */
    public function getListMaster(Request $request)
    {
        $arrPermission  = $this->model->getListMaster();

        $loggerCompany = AppUtils::getLoggedCompany(GwAppApiUtils::SCHEDULE_FLG);

        if(!$loggerCompany->stamp_flg){
            unset($arrPermission['全体設定']['タイムスタンプ設定']);
        }

        if(!$loggerCompany->signature_flg){
            unset($arrPermission['全体設定']['電子証明書設定']);
        }

        if(!$loggerCompany->long_term_storage_flg || !$loggerCompany->long_term_storage_option_flg){
            unset($arrPermission['全体設定']['長期保管インデックス設定']);
        }

        if(!$loggerCompany->long_term_storage_flg || !$loggerCompany->long_term_folder_flg){
            unset($arrPermission['全体設定']['長期保管フォルダ管理']);
        }

        if(!$loggerCompany->long_term_storage_flg){
            unset($arrPermission['利用者設定']['監査用アカウント設定']);
            unset($arrPermission['全体設定']['長期保管設定']);
        }

        // box外部連携 無効の場合
        if(!$loggerCompany->box_enabled){
            unset($arrPermission['全体設定']['外部連携']);
        }

        if (!$loggerCompany->option_user_flg){
            unset($arrPermission['利用者設定']['グループウェア専用利用者']);
        }

        if (!$loggerCompany->receive_user_flg){
            unset($arrPermission['利用者設定']['受信専用利用者']);
        }

        if (!$loggerCompany->template_route_flg){
            unset($arrPermission['機能設定']['承認ルート']);
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

        if ($loggerCompany->contract_edition == AppUtils::CONTRACT_EDITION_GW){
            unset($arrPermission['全体設定']['日付印設定']);
            unset($arrPermission['全体設定']['共通印設定']);
            unset($arrPermission['全体設定']['制限設定']);
            unset($arrPermission['全体設定']['接続IP制限設定']);
            unset($arrPermission['全体設定']['保護設定']);
            unset($arrPermission['利用者設定']['利用者設定']);
            unset($arrPermission['利用者設定']['共通印割当']);
            unset($arrPermission['機能設定']['共通アドレス帳']);
            unset($arrPermission['機能設定']['回覧一覧']);
            unset($arrPermission['機能設定']['保存文書一覧']);
        }
        /*PAC_5-2246 E*/
        if(!$loggerCompany->special_receive_flg){
            unset($arrPermission['特設サイト']['文書登録']);
            unset($arrPermission['特設サイト']['連携承認']);
        }
        if(!$loggerCompany->special_send_flg){
            unset($arrPermission['特設サイト']['連携申請']);
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
        return response()->json(['status' => true, 'item' => $arrPermission]);
    }

}
