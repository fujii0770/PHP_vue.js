<?php

namespace App\Http\Controllers;

use App\Http\Utils\PermissionUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\BizcardGroupUtils;
use App\Http\Utils\DepartmentUtils;
use App\Models\Bizcard;
use App\Models\BizcardManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BizcardController extends AdminController
{
    const DEFAULT_GET_BIZCARD_LIMIT = 10;       // 名刺画像取得数のデフォルト値
    const DISPLAY_TYPE = [                      // 名刺の公開種別
        'COMPANY' => 0,
        'DEPARTMENT' => 1,
        'PERSONAL' => 2,
        'GROUP' => 3
    ];
    const DEL_FLG = [                           // 削除フラグの設定
        'NO' => 0,
        'YES' => 1
    ];
    const EXTENSIONS = [                        // MIMEタイプをキーとした拡張子の配列
        'image/gif' => '.gif',
        'image/jpeg' => '.jpg',
        'image/png' => '.png'
    ];
    const DATA_URL_PREFIX = [                   // 画像表示用にbase64文字列の前に連結する文字列
        'jpeg' => 'data:image/jpeg;base64,',
        'png' => 'data:image/png;base64,',
        'gif' => 'data:image/gif;base64,'
    ];
    var $thumbnail_dir = '';                    // 名刺画像のサムネイル保存先
    const MAX_VERSION_NUM = 10;                 // バージョンの最大保存件数

    public function __construct()
    {
        $this->thumbnail_dir = 'bizcard/' . config('app.pac_app_env') . config('app.pac_contract_app') . config('app.pac_contract_server') . '/thumbnail/';
        parent::__construct();
    }
    
    /**
     * 自分の会社の名刺一覧を取得
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        try {
            $user = $request->user();
            if(!$user->can(PermissionUtils::PERMISSION_BIZ_CARDS_VIEW)){
                $this->raiseWarning(__('message.warning.not_permission_access'));
                return redirect()->route('home');
            }
            $action = $request->get('action', '');
            $bizcards = [];
            if ($action != "") {
                Log::debug('Get Bizcards: start');
                // 取得件数がパラメータで指定されていれば設定。指定されていなければデフォルト値を設定
                $limit = $request->filled('limit') ? $request->input('limit') : self::DEFAULT_GET_BIZCARD_LIMIT;
                // 削除フラグ、公開種別の設定を取得
                $del_flg_setting = $request->input('del_flg_setting');
                $display_type = $request->input('display_type');
                // 自分の会社のレコードを名刺管理テーブルから取得。パラメータに削除フラグが指定されていない場合は全部の名刺を取得
                $bizcardManageQuery = BizcardManage::query();
                $bizcardManageQuery->where('mst_company_id', $user->mst_company_id);
                // 削除フラグで絞り込み
                if ($del_flg_setting === strval(self::DEL_FLG['NO'])) {
                    // 削除フラグOff指定の場合
                    $bizcardManageQuery->where('del_flg', self::DEL_FLG['NO']);
                } else if ($del_flg_setting === strval(self::DEL_FLG['YES'])) {
                    // 削除フラグOn指定の場合
                    $bizcardManageQuery->where('del_flg', self::DEL_FLG['YES']);
                }
                // 公開種別で絞り込み
                if ($display_type === strval(self::DISPLAY_TYPE['COMPANY'])) {
                    // 会社
                    $bizcardManageQuery->where('display_type', self::DISPLAY_TYPE['COMPANY']);
                } else if ($display_type === strval(self::DISPLAY_TYPE['DEPARTMENT'])) {
                    // 部署
                    $bizcardManageQuery->where('display_type', self::DISPLAY_TYPE['DEPARTMENT']);
                } else if ($display_type === strval(self::DISPLAY_TYPE['PERSONAL'])) {
                    // 個人
                    $bizcardManageQuery->where('display_type', self::DISPLAY_TYPE['PERSONAL']);
                } else if ($display_type === strval(self::DISPLAY_TYPE['GROUP'])) {
                    // グループ
                    $bizcardManageQuery->where('display_type', self::DISPLAY_TYPE['GROUP']);
                }
                // bizcardテーブルのIDを取得
                $bizcardManages = $bizcardManageQuery->get()->toArray();
                $bizcardIds = array_map(function ($bizcardManage) {
                    return $bizcardManage['version'];
                }, $bizcardManages);
                // bizcardテーブルのレコードを取得
                $bizcardQuery = Bizcard::query();
                $bizcardQuery->whereIn('id', $bizcardIds);
                // フィルターが設定されていれば絞り込む
                if ($request->filled('filter')) {
                    // LIKE演算子のワイルドカード文字をエスケープ
                    $filter = str_replace(['%', '_'], ['\%', '\_'], $request->input('filter'));
                    Log::debug('filter: ' . $filter);
                    $bizcardQuery->where(function ($query) use ($filter) {
                        $query->where('name', 'LIKE', "%{$filter}%")
                        ->orwhere('company_name', 'LIKE', "%{$filter}%")
                        ->orwhere('address', 'LIKE', "%{$filter}%")
                        ->orwhere('email', 'LIKE', "%{$filter}%")
                        ->orwhere('department', 'LIKE', "%{$filter}%")
                        ->orwhere('position', 'LIKE', "%{$filter}%");
                    });
                }
                $bizcardQuery->orderBy('bizcard_id', 'ASC');
                // 名刺画像の取得開始位置と取得数に従い結果を制限する
                $bizcards = $bizcardQuery->paginate($limit)->appends(request()->input());
                foreach ($bizcards as &$bizcardData) {
                    $id = $bizcardData["id"];
                    $fileName = basename($bizcardData["path"]);
                    $thumbnailPath = str_replace($fileName, 'thumbnail/'. $fileName, $bizcardData["path"]);
    
                    // 名刺のサムネイル画像を取得
                    $thumbnail = Storage::disk('s3')->get($thumbnailPath);
    
                    // 対応するbizcard_manageテーブルのレコードを検索
                    $bizcardManage = BizcardManage::find($bizcardData["bizcard_id"]);
    
                    // base64文字列をデータURLの形式に変換
                    $base64 = base64_encode($thumbnail);
                    switch ($base64{0}) {
                        case '/':
                            // jpeg
                            $base64 = self::DATA_URL_PREFIX['jpeg'] . $base64;
                            break;
                        case 'i':
                            // png
                            $base64 = self::DATA_URL_PREFIX['png'] . $base64;
                            break;
                        case 'R':
                            // gif
                            $base64 = self::DATA_URL_PREFIX['gif'] . $base64;
                            break;
                    }
                    $bizcardData["bizcard"] = $base64;
                    $bizcardData["display_type"] = $bizcardManage->display_type;
                    $bizcardData["display_target"] = $bizcardManage->display_target;
                    $bizcardData["del_flg"] = $bizcardManage->del_flg;
                    unset($bizcardData["path"]);
                }
                unset($bizcardData);
            }
            $this->assign('bizcards', $bizcards);
            $this->setMetaTitle("名刺一覧");

            $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
            $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
            $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

            // 部署リストを取得
            $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
            $this->assign('listDepartmentTree', $listDepartmentTree);

            // グループ表示用リストを取得
            $listGroupTree = BizcardGroupUtils::getGroupTree($user->mst_company_id);
            $this->assign('listGroupTree', $listGroupTree);

            // 利用者リストを取得
            $userArray = [];
            $companyUsers = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->whereNull('delete_at')->get();
            foreach ($companyUsers as $companyUser) {
                $userArray[$companyUser->id] = $companyUser->family_name . ' ' . $companyUser->given_name;
            }
            $this->assign('userArray', $userArray);
            $this->assign('DISPLAY_TYPE', self::DISPLAY_TYPE);

            return $this->render('Bizcards.index');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /**
     * 指定されたIDの名刺情報(公開対象、公開対象編集可否、削除フラグのOn/Off)を返す
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show (Request $request, $id) {
        try {
            $user = $request->user();
            if(!$user->can(PermissionUtils::PERMISSION_BIZ_CARDS_VIEW)){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            Log::debug('Get Bizcard by ID: start');
            $bizcardManage = BizcardManage::find($id);
            // 名刺が見つからなければnullを返す
            if ($bizcardManage == null) {
                return response()->json([
                    'status' => true, 
                    'bizcard' => null,
                    'message' => [__('message.false.bizcard.no_data')]
                ]);
            }
            // 自分の会社の名刺でなければエラー
            $user = $request->user();
            if ($bizcardManage->mst_company_id != $user->mst_company_id) {
                return response()->json([
                    'status' => false,
                    'bizcard' => null,
                    'message' => [__('message.not_permission_access')]
                ]);
            }
            // 公開対象の編集可否(いずれかのユーザの「自分の名刺」に設定されている場合は編集不可)
            $display_editable = true;
            $userInfo = DB::table('mst_user_info')->where('bizcard_id', $id)->first();
            if ($userInfo) {
                $display_editable = false;
            }

            // 公開対象と削除フラグのOn/Offを返す
            return response()->json([
                'status' => true, 
                'bizcard' => [
                    'display_type' => $bizcardManage->display_type,
                    'display_target' => $bizcardManage->display_target,
                    'display_editable' => $display_editable,
                    'del_flg' => $bizcardManage->del_flg
                ],
                'message' => ['']
            ]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json([
                'status' => false,
                'bizcard' => null,
                'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]
            ]);
        }
    }

    /**
     * 名刺を物理削除
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deletes (Request $request) {
        try {
            $user = $request->user();
            if(!$user->can(PermissionUtils::PERMISSION_BIZ_CARDS_DELETE)){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            Log::debug('bizcard delete request parameter: ' . json_encode($request->all()));
            // 削除対象名刺IDの入力チェック
            $this->validate($request, [
                'bizcard_ids' => [
                    'required',
                ]
            ]);
            $bizcard_ids = $request->input('bizcard_ids');
            // 削除対象の名刺管理テーブルのレコードを取得
            $bizcardManages = BizcardManage::whereIn('id', $bizcard_ids)->get();
            // 自分の会社の名刺でなければエラー
            $user = $request->user();
            foreach ($bizcardManages as $bizcardManage) {
                if ($bizcardManage->mst_company_id != $user->mst_company_id) {
                    return response()->json(['status' => false, 'message' => [__('message.not_permission_access')]]);
                }
            }
            // 削除処理実行
            foreach ($bizcardManages as $bizcardManage) {
                DB::beginTransaction();
                // 削除する名刺をいずれかのユーザが自分の名刺に設定している場合、設定解除
                DB::table('mst_user_info')->where('bizcard_id', $bizcardManage->id)->update(['bizcard_id' => null]);
                // 削除対象の名刺データを取得
                $bizcards = Bizcard::where('bizcard_id', $bizcardManage->id)->get();
                foreach ($bizcards as $bizcard) {
                    $fileName = basename($bizcard->path);
                    $thumbnailPath = str_replace($fileName, 'thumbnail/' . $fileName, $bizcard->path);
                    // 保存した名刺画像、サムネイル、DB上のデータを削除
                    $bizcard->delete();
                    Storage::disk('s3')->delete($bizcard->path);
                    Storage::disk('s3')->delete($thumbnailPath);
                }
                $bizcardManage->delete();
                DB::commit();
            }
            return response()->json(['status' => true, 'message' => [__('message.success.bizcard.delete_bizcard')]]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /**
     * 指定されたIDの名刺情報(公開対象、削除フラグのOn/Off)を更新
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        try {
            $user = $request->user();
            if(!$user->can(PermissionUtils::PERMISSION_BIZ_CARDS_UPDATE)){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            Log::debug('bizcard delete update parameter: ' . json_encode($request->all()));
            $bizcardManage = BizcardManage::find($id);
            // 指定されたIDの名刺が存在しない場合はエラー
            if ($bizcardManage == null) {
                return response()->json([
                    'status' => false,
                    'message' => [__('message.false.bizcard.no_data')]
                ]);
            }
            // 自分の会社の名刺でなければエラー
            $user = $request->user();
            if ($bizcardManage->mst_company_id != $user->mst_company_id) {
                return response()->json([
                    'status' => false,
                    'message' => [__('message.not_permission_access')]
                ]);
            }
            // 更新対象の名刺データの最新バージョンを取得
            $bizcard = Bizcard::find($bizcardManage->version);
            // $bizcard が無ければエラー
            if ($bizcard == null) {
                return response()->json([
                    'status' => false,
                    'message' => [__('message.false.bizcard.no_data')]
                ]);
            }

            // パラメータに削除フラグが設定されている場合、値をチェックして更新
            if ($request->filled('del_flg')) {
                $del_flg = $request->input('del_flg');
                // 値が不正ならエラー
                if (array_search($del_flg, self::DEL_FLG) === false) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => [__('message.false.bizcard.invalid_del_flg')]
                    ]);
                }
                $bizcardManage->fill(['del_flg' => $del_flg]);
            }
            // ユーザの「自分の名刺」に設定されていない かつ 公開範囲が指定されている場合、値をチェックして更新
            $userInfo = DB::table('mst_user_info')->where('bizcard_id', $id)->first();
            if (!$userInfo && $request->filled('display_type')) {
                $display_type = $request->input('display_type');
                // 値が不正ならエラー
                if (array_search($display_type, self::DISPLAY_TYPE) === false) {
                    return response()->json([
                        'status' => false,
                        'message' => [__('message.false.bizcard.invalid_display_type')]
                    ]);
                }
                $input_display_target = $request->input('display_target');
                if ($display_type != self::DISPLAY_TYPE['COMPANY']) {
                    $targetName = ($display_type == self::DISPLAY_TYPE['DEPARTMENT'] ? '部署' : '利用者');
                    // 公開種別が「会社」以外の場合、公開対象が配列でない or 空の場合はエラー
                    if (!$input_display_target || !is_array($input_display_target)) {
                        // 配列でない or 空の場合はエラー
                        return response()->json([
                            'status' => false,
                            'message' => [__('message.false.bizcard.empty_target', ['attribute' => $targetName])]
                        ]);
                    }
                }
                switch ($display_type) {
                    case self::DISPLAY_TYPE['DEPARTMENT']:
                        foreach ($input_display_target as $key => $displayDeptId) {
                            // 対象のIDが入力値として正しくなければ配列から削除
                            if (!$this->checkTargetId($displayDeptId, 'mst_department', $user->mst_company_id)) {
                                unset($input_display_target[$key]);
                            }
                        }
                        if (count($input_display_target) === 0) {
                            // 対象部署がなければエラー
                            Log::debug('部署：公開対象部署数0');
                            return response()->json([
                                'status' => false,
                                'message' => [__('message.false.bizcard.invalid_display_target')]
                            ]);
                        }
                        break;
                    case self::DISPLAY_TYPE['PERSONAL']:
                        if (!$this->checkTargetId($input_display_target[0], 'mst_user', $user->mst_company_id)) {
                            // 対象のIDが入力値として正しくなければエラー
                            Log::debug('個人：公開対象利用者入力エラー');
                            return response()->json([
                                'status' => false,
                                'message' => [__('message.false.bizcard.invalid_display_target')]
                            ]);
                        }
                        break;
                    case self::DISPLAY_TYPE['GROUP']:
                        foreach ($input_display_target as $key => $userId) {
                            // 対象のIDが入力値として正しくなければ配列から削除
                            if (!$this->checkTargetId($userId, 'mst_user', $user->mst_company_id)) {
                                unset($input_display_target[$key]);
                            }
                        }
                        if (count($input_display_target) === 0) {
                            // 対象利用者がなければエラー
                            Log::debug('グループ：公開対象利用者数0');
                            return response()->json([
                                'status' => false,
                                'message' => [__('message.false.bizcard.invalid_display_target')]
                            ]);
                        }
                        break;
                }
                // 公開種別と公開対象を更新
                $bizcardManage->display_type = $display_type;
                if (is_array($input_display_target)) {
                    $bizcardManage->display_target = implode(',', array_values(array_unique($input_display_target)));
                } else {
                    $bizcardManage->display_target = '';
                }
            }
            // 名刺データの新バージョンを追加
            DB::beginTransaction();
            $newBizcard = $bizcard->replicate();
            $newBizcard->fill(['update_user' => $user->email]);
            // DBのユニーク制約に引っかからないよう、一旦元の名刺画像パス+タイムスタンプに変更
            $newBizcard->forceFill(['path' => $bizcard->path . time()]);
            $newBizcard->save();
            // 名刺画像パスを生成
            $mime_type = Storage::disk('s3')->mimeType($bizcard->path);
            $filePath = '';
            $fileId = strstr($bizcard->path, '_');
            if ($fileId !== false) {
                // 旧データの画像名にID部分が含まれている場合、新データのIDに置き換えて新しい画像名とする
                $filePath = str_replace($fileId, '_' . $newBizcard->id, $bizcard->path) . self::EXTENSIONS[$mime_type];
            } else {
                // 旧データの画像名にID部分が含まれていない場合、新データのIDを後ろに追加して新しい画像名とする
                $filePath = $bizcard->path . '_' . $newBizcard->id . self::EXTENSIONS[$mime_type];
            }
            $filePath = str_replace(strstr($bizcard->path, '_'), '_' . $newBizcard->id, $bizcard->path) . self::EXTENSIONS[$mime_type];
            $fileName = basename($filePath);
            $newBizcard->forceFill(['path' => $filePath])->save();
            // 旧データの画像とサムネイル画像をコピー
            Storage::disk('s3')->copy($bizcard->path, $filePath);
            $oldFileName = basename($bizcard->path);
            $oldThumbnailPath = str_replace($oldFileName, 'thumbnail/' . $oldFileName, $bizcard->path);
            Storage::disk('s3')->copy($oldThumbnailPath, $this->thumbnail_dir . $fileName);
            // 名刺管理テーブルの公開対象バージョンを更新
            $bizcardManage->version = $newBizcard->id;
            $bizcardManage->update_user = $user->email;
            $bizcardManage->save();
            // バージョン数が最大保存件数を超えた場合、一番古いバージョンを削除
            if (self::MAX_VERSION_NUM < Bizcard::where('bizcard_id', $bizcardManage->id)->count()) {
                $oldestVersion = Bizcard::where('bizcard_id', $bizcardManage->id)->oldest()->first();
                $oldestFileName = basename($oldestVersion->path);
                $oldestThumbnailPath = str_replace($oldestFileName, 'thumbnail/' . $oldestFileName, $oldestVersion->path);
                // 保存した名刺画像、サムネイル、DB上のデータを削除
                $oldestVersion->delete();
                Storage::disk('s3')->delete($oldestVersion->path);
                Storage::disk('s3')->delete($oldestThumbnailPath);
            }
            DB::commit();
            return response()->json(['status' => true, 'message' => [__('message.success.bizcard.update_bizcard')]]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

    /**
     * 指定されたIDが入力値として正しいか判定する
     * @param $id 判定対象のID
     * @param $tableName 検索対象のテーブル名
     * @param $mst_company_id 会社ID
     */
    private function checkTargetId ($id, $tableName, $mst_company_id) {
        // 数字かどうか判定
        if (!is_numeric($id)) {
            return false;
        }
        $target = DB::table($tableName)->where('id', $id)->first();
        if (!$target || $target->mst_company_id !== $mst_company_id) {
            return false;
        }
        return true;
    }
}
