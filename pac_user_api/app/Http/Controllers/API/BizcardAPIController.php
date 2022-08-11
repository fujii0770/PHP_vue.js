<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Http\Utils\AppUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Models\Bizcard;
use App\Models\BizcardManage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Response;
use Intervention\Image\Facades\Image;
use File;

// 名刺情報を扱うクラス
class BizcardAPIController extends AppBaseController
{

    var $model = null;
    const DEFAULT_GET_BIZCARD_LIMIT = 100;      // 名刺画像取得数のデフォルト値
    const BIZCARD_THUMBNAIL_WIDTH = 500;        // 名刺画像のサムネイルの横幅
    var $bizcard_img_dir = '';                  // 名刺画像の保存先
    var $thumbnail_dir = '';                    // 名刺画像のサムネイル保存先
    const EXTENSIONS = [                        // MIMEタイプをキーとした拡張子の配列
        'image/gif' => '.gif',
        'image/jpeg' => '.jpg',
        'image/png' => '.png'
    ];
    const DISPLAY_TYPE = [                      // 名刺の公開種別
        'COMPANY' => 0,
        'DEPARTMENT' => 1,
        'PERSONAL' => 2,
        'GROUP' => 3
    ];
    const SAVE_TYPE = [                         // 名刺の保存種別
        'OVERWRITE' => 0,
        'SAVE_AS' => 1
    ];
    const MAX_VERSION_NUM = 10;                 // バージョンの最大保存件数
    const MAX_IMAGE_NUM = 100;                  // 一括登録可能な画像の最大件数
    const CSV_COLUMN_NUM = 16;                  // 一括登録で受け入れるCSVのカラム数

    public function __construct(Bizcard $bizcard)
    {
        $this->model = $bizcard;
        $this->bizcard_img_dir = config('filesystems.prefix_path') . '/' . 'bizcard/' . config('app.server_env') . config('app.edition_flg') . config('app.server_flg') . '/';
        $this->thumbnail_dir = config('filesystems.prefix_path') . '/' . 'bizcard/' . config('app.server_env') . config('app.edition_flg') . config('app.server_flg') . '/thumbnail/';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 名刺取得
        try {
            Log::debug('Get Bizcards: start');
            // 自分の会社IDと部署IDを取得
            $user = $request->user();
            $mst_user_id = $user->id;
            $mst_company_id = DB::table('mst_user')->where('id', $mst_user_id)->value('mst_company_id');
            $mst_department_id = DB::table('mst_user_info')->where('mst_user_id', $mst_user_id)->value('mst_department_id');

            // 自分の名刺ID
            $my_bizcard_id = DB::table('mst_user_info')->where('mst_user_id', $request->user()->id)->value('bizcard_id');
            Log::debug('My bizcard ID: ' . $my_bizcard_id);

            // 取得開始位置と取得件数がパラメータで指定されていれば設定。指定されていなければデフォルト値を設定
            $offset = $request->filled('offset') ? $request->input('offset') : 0;
            $limit = $request->filled('limit') ? $request->input('limit') : self::DEFAULT_GET_BIZCARD_LIMIT;

            // 自分の会社のレコードを名刺管理テーブルから取得
            $myCompanyBizcards = BizcardManage::where('mst_company_id', $mst_company_id)
                                              ->where('del_flg', 0)->get();
            // 表示対象の名刺IDと公開設定を配列に保存
            // キー：表示対象の名刺ID　値：公開設定(公開種別・対象)　とする
            $displayBizcards = [];
            foreach ($myCompanyBizcards as $myCompanyBizcard) {
                if ($myCompanyBizcard->id == $my_bizcard_id) {
                    // 自分の名刺は一覧から省く
                    continue;
                }
                if (!$this->checkDisplayTarget($mst_user_id, $myCompanyBizcard)) {
                    // 公開対象に含まれていなければ非表示
                    continue;
                }
                $display_target = explode(',', $myCompanyBizcard->display_target);
                if (count($display_target) == 1 && $display_target[0] === "") {
                    // targetが空の場合
                    $display_target = [];
                } else {
                    $display_target = array_map(function ($id) {
                        return intval($id);
                    }, $display_target);
                }
                $displayBizcards[$myCompanyBizcard->version] = array(
                    'type' => $myCompanyBizcard->display_type,
                    'target' => $display_target,
                );
            }
            Log::debug('公開対象名刺リスト：' . json_encode(array_keys($displayBizcards)));
            $bizcardQuery = Bizcard::query();
            $bizcardQuery->whereIn('id', array_keys($displayBizcards));
            // フィルターが設定されていれば絞り込む
            if ($request->filled('filter')) {
                // LIKE演算子のワイルドカード文字をエスケープ
                $filter = str_replace(['%', '_'], ['\%', '\_'], $request->input('filter'));

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

            // 取得結果の総件数を保持しておく
            $totalBizcardNum = $bizcardQuery->count();

            // 名刺画像の取得開始位置と取得数に従い結果を制限する
            $bizcardArray = $bizcardQuery->offset($offset)->limit($limit)->get()->toArray();

            $bizcardImgArray = array();

            foreach ($bizcardArray as $bizcardData) {
                $id = $bizcardData["id"];
                $fileName = basename($bizcardData["path"]);
                $thumbnailPath = str_replace($fileName, 'thumbnail/'. $fileName, $bizcardData["path"]);

                // 名刺のサムネイル画像を取得
                $thumbnail = Storage::disk('s3')->get($thumbnailPath);

                // 対応する名刺管理テーブルのレコードを取得
                $bizcardManage = $myCompanyBizcards->first(function ($item) use ($bizcardData) {
                    return $item['id'] == $bizcardData["bizcard_id"];
                });

                $bizcardImgArray[] = array(
                    'id' => $bizcardData["id"],
                    'bizcard_id' => $bizcardData["bizcard_id"],
                    'name' => $bizcardData["name"],
                    'name_kana' => $bizcardData["name_kana"],
                    'name_romaji' => $bizcardData["name_romaji"],
                    'company_name' => $bizcardData["company_name"],
                    'company_kana' => $bizcardData["company_kana"],
                    'phone_number' => $bizcardData["phone_number"],
                    'address' => $bizcardData["address"],
                    'address_name' => $bizcardData["address_name"],
                    'postal_code' => $bizcardData["postal_code"],
                    'address_en' => $bizcardData["address_en"],
                    'email' => $bizcardData["email"],
                    'department' => $bizcardData["department"],
                    'position' => $bizcardData["position"],
                    'person_title' => $bizcardData["person_title"],
                    'url' => $bizcardData["url"],
                    'created_at' => Carbon::parse($bizcardManage->created_at)->toDateTimeString(),
                    'create_user' => $bizcardManage->create_user,
                    'updated_at' => Carbon::parse($bizcardManage->updated_at)->toDateTimeString(),
                    'update_user' => $bizcardManage->update_user,
                    'bizcard' => base64_encode($thumbnail),
                    'display_type' => $displayBizcards[$id]['type'],
                    'display_target' => $displayBizcards[$id]['target']
                );
            }

            // base64エンコード文字列内のスラッシュをエスケープしないようオプション指定
            return response()->json([
                'data' => [
                    'result_code' => 0,
                    'error_code' => "",
                    'total_bizcard_num' => $totalBizcardNum,
                    'bizcardArray' => $bizcardImgArray,
                ],
            ], StatusCodeUtils::HTTP_OK, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
                'result_code' => 1,
                'error_code' => "E000000",
                'result_message' => '名刺画像取得処理に失敗しました。'
            ], 400);
        }
    }

    // 自分が名刺の公開対象に含まれているかチェック
    private function checkDisplayTarget ($mst_user_id, $bizcardManage) {
        $mst_company_id = DB::table('mst_user')->where('id', $mst_user_id)->value('mst_company_id');
        $mst_department_id = DB::table('mst_user_info')->where('mst_user_id', $mst_user_id)->value('mst_department_id');
        if ($bizcardManage->mst_company_id != $mst_company_id) {
            return false;
        } else if ($bizcardManage->display_type == self::DISPLAY_TYPE['DEPARTMENT']) {
            // 公開種別が「部署」の場合、自分の部署が公開対象に含まれているかチェック
            $targetDept = explode(',', $bizcardManage->display_target);
            // 公開対象部署の子部署も公開対象なので、子部署を取得する
            $parentDeptIds = $targetDept;
            $childrenDepts = DB::table('mst_department')->whereIn('parent_id', $parentDeptIds)->get()->toArray();
            while ($childrenDepts) {
                $parentDeptIds = array_map(function ($childrenDept) {
                    return $childrenDept->id;
                }, $childrenDepts);
                $targetDept = array_merge($targetDept, $parentDeptIds);
                $childrenDepts = DB::table('mst_department')->whereIn('parent_id', $parentDeptIds)->get()->toArray();
            }
            if (!in_array($mst_department_id, $targetDept)) {
                return false;
            }
        } else if ($bizcardManage->display_type == self::DISPLAY_TYPE['PERSONAL']
                    && $bizcardManage->display_target != $mst_user_id) {
            // 公開種別が「個人」の場合、公開対象が自分かチェック
            return false;
        } else if ($bizcardManage->display_type == self::DISPLAY_TYPE['GROUP']) {
            // 公開種別が「グループ」の場合、自分が公開対象に含まれているかチェック
            $targetUser = explode(',', $bizcardManage->display_target);
            if (!in_array($mst_user_id, $targetUser)) {
                return false;
            }
        }
        return true;
    }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 名刺登録
        try {
            Log::debug("名刺登録リクエストパラメータ: " . json_encode($request->except(['bizcard', 'biz_card_image'])));
            // リクエストからパラメータ取得
            $user = $request->user();
            $mst_user_id = $user->id;
            $display_type = $request->filled('display_type') ? $request->input('display_type') : self::DISPLAY_TYPE['PERSONAL'];
            $display_target = '';
            $my_bizcard = false;    // 自分の名刺かどうか
            $myBizcardManage = null;

            if ($request->filled('my_bizcard')) {
                $my_bizcard = (string)$request->input('my_bizcard');
            }

            // 名刺画像ファイルのバリデーション
            $this->validate($request, [
                'biz_card_image' => [
                    'required',
                ]
            ]);

            if ($my_bizcard && $my_bizcard != 'false') {
                // 自分の名刺を登録する場合、公開対象を自分のIDに設定
                $display_type = self::DISPLAY_TYPE['PERSONAL'];
                $display_target = strval($mst_user_id);
                // 削除済みの自分の名刺がある場合、対応する名刺管理テーブルのレコードを保存する
                $myUserInfo = DB::table('mst_user_info')->where('mst_user_id', $mst_user_id)->first();
                $myBizcardManage = BizcardManage::find($myUserInfo->bizcard_id);
            } else if (array_search($display_type, self::DISPLAY_TYPE) !== false) {
                // 公開種別が正しく設定されている場合、公開対象を設定
                $targetSettingResult = $this->setTargetUser($display_type, $mst_user_id, $request->input('display_target'));
                if ($targetSettingResult['target'] === null) {
                    // 公開対象の設定失敗時はエラー
                    return $this->sendApiError([
                        'result_code' => 1,
                        'error_code' => "E000000",
                        'result_message' => $targetSettingResult['message']
                    ], 400);
                }
                $display_target = $targetSettingResult['target'];
            } else {
                // 公開種別が不正の場合はエラー
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => '名刺登録に失敗しました。'
                ], 400);
            }

            $bizcard = new Bizcard();
            $bizcard->fill($request->all());
            $bizcard->forceFill([
                'bizcard_id' => $myBizcardManage != null ? $myBizcardManage->id : 0,
                'mst_user_id' => $mst_user_id,
                'path' => '',
                'create_user' => $user->email,
            ]);
            $bizcard->save();
            $bizcard_id = $bizcard->id;

            // リクエストから画像を取り出し、デコード
            $bizcardImg = base64_decode($request->input('biz_card_image'));
            $mime_type = finfo_buffer(finfo_open(), $bizcardImg, FILEINFO_MIME_TYPE);
            // ファイル名を指定して画像をS3に保存
            $fileName = $mst_user_id . '_' . $bizcard_id . self::EXTENSIONS[$mime_type];
            $this->saveBizcardImageToS3($fileName, $bizcardImg, $this->bizcard_img_dir . $fileName, $this->thumbnail_dir . $fileName);
            $bizcard->forceFill(['path' => $this->bizcard_img_dir . $fileName])->save();

            if ($myBizcardManage != null) {
                Log::debug('自分の名刺を再登録');
                // 自分の名刺を再登録する場合、既存の名刺管理テーブルのレコードを更新
                $myBizcardManage->version = $bizcard_id;
                $myBizcardManage->display_type = $display_type;
                $myBizcardManage->display_target = $display_target;
                // 削除フラグをOFFに戻す
                $myBizcardManage->del_flg = 0;
                $myBizcardManage->save();
                // リンクページURLを保存
                // 予約文字を含まないようURLエンコードする
                $link_page_url = config('app.bizcard_show_url') . rawurlencode(AppUtils::encrypt($myBizcardManage->id ,true));
                $myBizcardManage->fill(['update_user' => $user->email])->save();
                $bizcard->forceFill(['link_page_url' => $link_page_url])->save();
                // バージョン数が最大保存件数を超えた場合、一番古いバージョンを削除
                if (self::MAX_VERSION_NUM < $this->model->where('bizcard_id', $myBizcardManage->id)->count()) {
                    $oldestVersion = $this->model->where('bizcard_id', $myBizcardManage->id)->oldest()->first();
                    $oldestFileName = basename($oldestVersion->path);
                    $oldestThumbnailPath = str_replace($oldestFileName, 'thumbnail/' . $oldestFileName, $oldestVersion->path);
                    // 保存した名刺画像、サムネイル、DB上のデータを削除
                    $oldestVersion->delete();
                    Storage::disk('s3')->delete($oldestVersion->path);
                    Storage::disk('s3')->delete($oldestThumbnailPath);
                }
            } else {
                // 自分の名刺の再登録でない場合、名刺管理テーブルに情報を保存
                $bizcardManage = new BizcardManage();
                $bizcardManage->mst_company_id = DB::table('mst_user')->where('id', $mst_user_id)->value('mst_company_id');
                $bizcardManage->version = $bizcard_id;
                $bizcardManage->display_type = $display_type;
                $bizcardManage->display_target = $display_target;
                $bizcardManage->create_user = $user->email;
                $bizcardManage->save();
                $bizcardManageId = $bizcardManage->id;
                // リンクページURLと名刺管理IDを保存
                // 予約文字を含まないようURLエンコードする
                $link_page_url = config('app.bizcard_show_url') . rawurlencode(AppUtils::encrypt($bizcardManageId ,true));
                $bizcard->forceFill(['link_page_url' => $link_page_url, 'bizcard_id' => $bizcardManageId])->save();
                // 自分の名刺を初めて登録する場合
                if ($my_bizcard && $my_bizcard != 'false') {
                    Log::debug('自分の名刺を初回登録');
                    DB::table('mst_user_info')->where('mst_user_id',$user->id)->update(['bizcard_id' => $bizcardManageId]);
                }
            }

            return $this->sendResponse([
                'result_code' => 0,
                'bizcard_id' => $bizcard_id,
                'error_code' => "",
            ], '名刺を登録しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'bizcard_id' => null,
              'error_code' => "E000000",
              'result_message' => '名刺登録に失敗しました。'
            ], 400);
        }
    }

    // 公開対象ユーザを設定する
    private function setTargetUser ($display_type, $mst_user_id, $display_target) {
        if ($display_type == self::DISPLAY_TYPE['PERSONAL']) {
            // 公開種別が「個人」の場合、公開対象は自分のID
            return [
                'target' => strval($mst_user_id),
                'message' => null
            ];
        } else if ($display_type == self::DISPLAY_TYPE['COMPANY']) {
            // 公開種別が「会社」の場合、公開対象は空
            return [
                'target' => '',
                'message' => null
            ];
        } else if ($display_type == self::DISPLAY_TYPE['DEPARTMENT']) {
            // 公開種別が「部署」の場合、公開対象の形式チェック
            if (!$display_target || !is_array($display_target) || !array_key_exists('dept', $display_target)) {
                // 配列でない or 空 or 「公開対象部署」が無い場合はnullを返す
                if (!$display_target || !is_array($display_target)) {
                    Log::debug('部署：display_target 指定なし');
                } else {
                    Log::debug('部署：公開対象部署なし');
                }
                return [
                    'target' => null,
                    'message' => '公開する部署を設定してください。'
                ];
            }
            foreach ($display_target['dept'] as $key => $displayDeptId) {
                // 数字以外なら配列から削除
                if (!is_numeric($displayDeptId)) {
                    unset($display_target['dept'][$key]);
                    continue;
                }
                // 存在しない部署 or 自分の会社の部署でなければ配列から削除
                $targetDept = DB::table('mst_department')->where('id', $displayDeptId)->first();
                $mst_company_id = DB::table('mst_user')->where('id', $mst_user_id)->value('mst_company_id');
                if (!$targetDept || $targetDept->mst_company_id !== $mst_company_id) {
                    Log::debug('自分の会社の部署ではない: ' . $displayDeptId);
                    unset($display_target['dept'][$key]);
                }
            }
            if (count($display_target['dept']) === 0) {
                // 対象部署がなければnullを返す
                Log::debug('部署：公開対象部署数0');
                return [
                    'target' => null,
                    'message' => '公開する部署を設定してください。'
                ];
            }
            return [
                'target' => implode(',', array_values(array_unique($display_target['dept']))),
                'message' => ''
            ];
        } else if ($display_type == self::DISPLAY_TYPE['GROUP']) {
            // 公開種別が「グループ」の場合、公開対象ユーザを設定
            $display = $this->setTargetUserGroup($display_target, $mst_user_id);
            if ($display === null) {
                // 対象ユーザが居なければnullを返す
                Log::debug('グループ：公開対象ユーザ数0');
                return [
                    'target' => null,
                    'message' => '公開するユーザを設定してください。'
                ];
            }
            return [
                'target' => $display,
                'message' => ''
            ];
        }
    }

    // 公開種別が「グループ」の場合に、公開対象ユーザを設定する
    private function setTargetUserGroup ($displayTarget, $mst_user_id) {
        // 公開対象の形式チェック
        if (!$displayTarget || !is_array($displayTarget)
            || (!array_key_exists('dept', $displayTarget) && !array_key_exists('user', $displayTarget))) {
            // 配列でない or 空 or 「公開対象部署」と「公開対象ユーザ」が両方無い場合はnullを返す
            if (!$displayTarget || !is_array($displayTarget)) {
                Log::debug('グループ：display_target 指定なし');
            } else {
                Log::debug('グループ：公開対象部署・ユーザ指定なし');
            }
            return null;
        }
        // 公開対象ユーザを設定
        $targetUsersId = [];
        if (array_key_exists('dept', $displayTarget) && is_array($displayTarget['dept'])) {
            // 公開対象部署あり
            $displayTargetDept = $displayTarget['dept'];
            // 公開対象部署の子部署も公開対象なので、子部署を取得する
            $parentDeptIds = $displayTargetDept;
            $childrenDepts = DB::table('mst_department')->whereIn('parent_id', $parentDeptIds)->get()->toArray();
            while ($childrenDepts) {
                $parentDeptIds = array_map(function ($childrenDept) {
                    return $childrenDept->id;
                }, $childrenDepts);
                $displayTargetDept = array_merge($displayTargetDept, $parentDeptIds);
                $childrenDepts = DB::table('mst_department')->whereIn('parent_id', $parentDeptIds)->get()->toArray();
            }
            // 公開対象の部署に所属するユーザのIDを取得
            $targetUsers = DB::table('mst_user_info')->whereIn('mst_department_id', $displayTargetDept)->get()->toArray();
            $targetUsersId = array_map(function ($user) {
                return $user->mst_user_id;
            }, $targetUsers);
        }
        if (array_key_exists('user', $displayTarget) && is_array($displayTarget['user'])) {
            // 公開対象ユーザあり
            $displayTargetUser = $displayTarget['user'];
            foreach ($displayTargetUser as $key => $displayUserId) {
                // 数字以外なら配列から削除
                if (!is_numeric($displayUserId)) {
                    unset($displayTargetUser[$key]);
                    continue;
                }
            }
            $targetUsersId = array_merge($targetUsersId, array_values($displayTargetUser));
        }
        // 存在しないユーザ or 自分の会社のユーザでなければ配列から削除
        foreach ($targetUsersId as $key => $id) {
            $targetUser = DB::table('mst_user')->where('id', $id)->first();
            $mst_company_id = DB::table('mst_user')->where('id', $mst_user_id)->value('mst_company_id');
            if (!$targetUser || $targetUser->mst_company_id !== $mst_company_id) {
                Log::debug('自分の会社のユーザではない: ' . $id);
                unset($targetUsersId[$key]);
            }
        }
        if (count($targetUsersId) === 0) {
            // 対象ユーザが居なければnullを返す
            return null;
        }
        return implode(',', array_values(array_unique($targetUsersId)));
    }

    /**
     * 指定されたIDの名刺情報を返すために環境に応じたAPIを呼び出す
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // クロス環境判定
        $env_flg = $request->filled('env_flg') ? $request->input('env_flg') : config('app.server_env');
        $server_flg = $request->filled('server_flg') ? $request->input('server_flg') : config('app.server_flg');
        $edition_flg = $request->filled('edition_flg') ? $request->input('edition_flg') : config('app.edition_flg');

        if ((($env_flg != config('app.server_env')) || ($server_flg != config('app.server_flg'))) && $edition_flg != 0) {
            Log::debug('Get envClient');
            // 他環境の場合、他環境のapiを呼び出す。
            $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
            if (!$envClient){
                throw new \Exception('Cannot connect to other server Api');
            }

            $response = $envClient->get('bizcard/showBizcardById/' . $id, [
                'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                }
            ]);
            Log::debug('Request URL: ' . $url);
            if (!$response) {
                return $this->sendError('名刺画像取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                Log::debug('getStatusCode：'. $response->getStatusCode());
                Log::error($response->getBody());
                throw new \Exception('Cannot get bizcard');
            }
            $resData = json_decode((string)$response->getBody());
            // base64エンコード文字列内のスラッシュをエスケープしないようオプション指定
            return response()->json([
                'data' => $resData->data,
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } else {
            return $this->showBizcardById($id);
        }
    }

    public function showBizcardById($id) {
        Log::debug('showBizcardById');
        // 指定されたidの名刺を取得
        try {
            $bizcardManage = BizcardManage::find($id);
            // 名刺が見つからない時または削除フラグがONの時は名刺データにnullを入れて返す
            if ($bizcardManage == null || $bizcardManage->del_flg !== 0) {
                return response()->json([
                    'data' => [
                        'result_code' => 0,
                        'error_code' => "",
                        'bizcard' => null,
                    ],
                ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            }
            $version = $bizcardManage->version;
            $bizcard = $this->model->find($version);

            $fileName = basename($bizcard["path"]);

            $thumbnailPath = str_replace($fileName, 'thumbnail/'. $fileName, $bizcard["path"]);

            // 名刺のサムネイル画像を取得
            $thumbnail = Storage::disk('s3')->get($thumbnailPath);
            $base64 = base64_encode($thumbnail);

            $bizcardData = array(
                'id' => $bizcard["id"],
                'bizcard_id' => $bizcard["bizcard_id"],
                'name' => $bizcard["name"],
                'name_kana' => $bizcard["name_kana"],
                'name_romaji' => $bizcard["name_romaji"],
                'company_name' => $bizcard["company_name"],
                'company_kana' => $bizcard["company_kana"],
                'phone_number' => $bizcard["phone_number"],
                'address' => $bizcard["address"],
                'address_name' => $bizcard["address_name"],
                'postal_code' => $bizcard["postal_code"],
                'address_en' => $bizcard["address_en"],
                'email' => $bizcard["email"],
                'department' => $bizcard["department"],
                'position' => $bizcard["position"],
                'person_title' => $bizcard["person_title"],
                'url' => $bizcard["url"],
                'created_at' => Carbon::parse($bizcardManage->created_at)->toDateTimeString(),
                'create_user' => $bizcardManage->create_user,
                'updated_at' => Carbon::parse($bizcardManage->updated_at)->toDateTimeString(),
                'update_user' => $bizcardManage->update_user,
                'bizcard' => $base64,
            );

            // base64エンコード文字列内のスラッシュをエスケープしないようオプション指定
            return response()->json([
                'data' => [
                    'result_code' => 0,
                    'error_code' => "",
                    'bizcard' => $bizcardData,
                ],
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
                'result_code' => 1,
                'error_code' => "E000000",
                'result_message' => '名刺画像取得処理に失敗しました。'
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // NOP
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            Log::debug("名刺更新リクエストパラメータ: " . json_encode($request->except(['bizcard', 'biz_card_image'])));
            // 更新対象の名刺管理テーブルのレコードを取得
            $bizcardManage = BizcardManage::find($id);
            // 見つからない時または削除フラグがONの時はエラーを返す。
            if ($bizcardManage == null || $bizcardManage->del_flg !== 0) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000001",
                    'result_message' => '名刺が見つかりません。'
                  ], 404);
            }

            // 更新可能な(自分に公開されている)名刺かチェック
            $user = $request->user();
            $mst_company_id = DB::table('mst_user')->where('id', $user->id)->value('mst_company_id');
            $mst_department_id = DB::table('mst_user_info')->where('mst_user_id', $user->id)->value('mst_department_id');
            if (!$this->checkDisplayTarget($user->id, $bizcardManage)) {
                Log::debug('ログインユーザに公開されていない名刺のため更新不可');
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'この名刺は更新できません。'
                ], 400);
            }

            // 更新対象の名刺データの最新バージョンを取得
            $bizcard = Bizcard::find($bizcardManage->version);
            // $bizcard が無ければエラーを返す。
            if ($bizcard == null) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000001",
                    'result_message' => '名刺が見つかりません。'
                ], 404);
            }

            // mst_user_infoテーブルのbizcard_idに紐づいた名刺でない かつ 公開種別の入力がある場合、公開設定を設定
            if (!DB::table('mst_user_info')->where('bizcard_id', $bizcardManage->id)->first() && $request->filled('display_type')) {
                Log::debug('名刺更新: 公開対象を設定');
                $bizcardManage->display_type = $request->input('display_type');
                if (array_search($bizcardManage->display_type, self::DISPLAY_TYPE) !== false) {
                    // 公開種別が正しく設定されている場合、公開対象を設定
                    $targetSettingResult = $this->setTargetUser($bizcardManage->display_type, $user->id, $request->input('display_target'));
                    if ($targetSettingResult['target'] === null) {
                        // 公開対象の設定失敗時はエラー
                        return $this->sendApiError([
                            'result_code' => 1,
                            'error_code' => "E000000",
                            'result_message' => $targetSettingResult['message']
                        ], 400);
                    }
                    $bizcardManage->display_target = $targetSettingResult['target'];
                } else {
                    // 公開種別が不正の場合はエラー
                    return $this->sendApiError([
                        'result_code' => 1,
                        'error_code' => "E000000",
                        'result_message' => '更新に失敗しました。'
                    ], 400);
                }
            }

            // 「上書き保存」か「別名保存」かを判定
            if (!$request->filled('save_type') || $request->input('save_type') == self::SAVE_TYPE['OVERWRITE']) {
                // 未指定 or 「上書き保存」の場合
                // パラメータに画像が存在する場合はデコードして保存
                if ($request->filled('biz_card_image')) {
                    $bizcardImg = base64_decode($request->input('biz_card_image'));
                    $fileName = basename($bizcard->path);
                    $thumbnailPath = str_replace($fileName, 'thumbnail/' . $fileName, $bizcard->path);
                    $this->saveBizcardImageToS3($fileName, $bizcardImg, $bizcard->path, $thumbnailPath);
                }

                // リクエストからパラメータを取得して更新
                $bizcard->fill($request->all());
                $bizcard->fill(['update_user' => $user->email])->save();
            } else if ($request->input('save_type') == self::SAVE_TYPE['SAVE_AS']){
                // 「別名保存」の場合
                $newBizcard = new Bizcard();
                $newBizcard->fill($request->all());
                $newBizcard->forceFill([
                    'bizcard_id' => $bizcardManage->id,
                    'mst_user_id' => $user->id,
                    'path' => '',
                    'link_page_url' => $bizcard->link_page_url,
                    'create_user' => $bizcard->create_user,
                    'update_user' => $user->email,
                ]);
                $newBizcard->save();
                if ($request->filled('biz_card_image')) {
                    // パラメータに画像が存在する場合は画像を取り出し、デコード
                    $bizcardImg = base64_decode($request->input('biz_card_image'));
                    $mime_type = finfo_buffer(finfo_open(), $bizcardImg, FILEINFO_MIME_TYPE);
                    // 名刺画像パスを生成し画像を保存
                    $fileName = $user->id . '_' . $newBizcard->id . self::EXTENSIONS[$mime_type];
                    $newBizcard->forceFill(['path' => $this->bizcard_img_dir . $fileName])->save();
                    $this->saveBizcardImageToS3($fileName, $bizcardImg, $this->bizcard_img_dir . $fileName, $this->thumbnail_dir . $fileName);
                } else {
                    // パラメータに画像が存在しない場合
                    // 名刺画像パスを生成
                    $mime_type = Storage::disk('s3')->mimeType($bizcard->path);
                    $fileName = $user->id . '_' . $newBizcard->id . self::EXTENSIONS[$mime_type];
                    $newBizcard->forceFill(['path' => $this->bizcard_img_dir . $fileName])->save();
                    // 旧データの画像とサムネイル画像をコピー
                    Storage::disk('s3')->copy($bizcard->path, $this->bizcard_img_dir . $fileName);
                    $oldThumbnailPath = str_replace($fileName, 'thumbnail/' . $fileName, $bizcard->path);
                    Storage::disk('s3')->copy($oldThumbnailPath, $this->thumbnail_dir . $fileName);
                }
                // 名刺管理テーブルの公開対象バージョンを更新
                $bizcardManage->version = $newBizcard->id;
                // バージョン数が最大保存件数を超えた場合、一番古いバージョンを削除
                if (self::MAX_VERSION_NUM < $this->model->where('bizcard_id', $bizcardManage->id)->count()) {
                    $oldestVersion = $this->model->where('bizcard_id', $bizcardManage->id)->oldest()->first();
                    $oldestFileName = basename($oldestVersion->path);
                    $oldestThumbnailPath = str_replace($oldestFileName, 'thumbnail/' . $oldestFileName, $oldestVersion->path);
                    // 保存した名刺画像、サムネイル、DB上のデータを削除
                    $oldestVersion->delete();
                    Storage::disk('s3')->delete($oldestVersion->path);
                    Storage::disk('s3')->delete($oldestThumbnailPath);
                }
            } else {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => '更新に失敗しました。'
                ], 500);
            }
            $bizcardManage->update_user = $user->email;
            $bizcardManage->save();

            return $this->sendResponse([
                'result_code' => 0,
                'error_code' => "",
            ], '更新に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => '更新に失敗しました。'
            ], 500);
        }
    }

    private function saveBizcardImageToS3($fileName, $bizcardImg, $path, $thumbnailPath) {
        // サムネイル画像を一時的に保存するディレクトリ
        $tmpPath = storage_path('app/storage/img/tmp/');

        // 名刺画像・サムネイル画像保存用ディレクトリがなければ作成
        if (!Storage::disk('s3')->exists($this->thumbnail_dir)) {
            Storage::disk('s3')->makeDirectory($this->thumbnail_dir);
        }
        // サムネイル画像一時保存用ディレクトリがなければ作成
        if (!File::exists($tmpPath)) {
            mkdir($tmpPath, 0777, true);
            chmod($tmpPath, 0777);
        }
        // ファイル名を指定してS3に保存
        Storage::disk('s3')->put($path, $bizcardImg);

        // アスペクト比を維持し画像サイズを縮小
        Image::make($bizcardImg)->resize(self::BIZCARD_THUMBNAIL_WIDTH, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save($tmpPath . $fileName);

        $thumbnail = Storage::get('storage/img/tmp/' . $fileName);
        // サムネイル画像をS3に保存
        Storage::disk('s3')->put($thumbnailPath, $thumbnail);

        // 一時保存したファイルを削除
        Storage::delete('storage/img/tmp/' . $fileName);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            // 削除対象の名刺データを取得
            $bizcardManage = BizcardManage::find($id);

            // 見つからない時または削除フラグがONの時はエラーを返す。
            if ($bizcardManage == null || $bizcardManage->del_flg !== 0) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000001",
                    'result_message' => '名刺が見つかりません。'
                  ], 404);
            }
            // 削除フラグをON
            $bizcardManage->fill(['del_flg' => 1])->save();

            return $this->sendResponse([
                'result_code' => 0,
                'error_code' => "",
            ], '名刺削除に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => '名刺削除に失敗しました。'
            ], 400);
        }
    }
    
    /**
     * 自分の名刺データを返す
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getMyBizcard(Request $request) {
        $bizcard_id = DB::table('mst_user_info')->where('mst_user_id', $request->user()->id)->value('bizcard_id');

        // 自分の名刺が登録されていない場合は名刺データにnullを入れて返す
        if ($bizcard_id == null) {
            return response()->json([
                'data' => [
                    'result_code' => 0,
                    'error_code' => "",
                    'bizcard' => null,
                ],
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } else {
            return $this->showBizcardById($bizcard_id);
        }
    }

    /**
     * 指定されたIDの名刺のリンクページURLを返す
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getLinkPageURL($id)
    {
        try {
            $bizcardManage = BizcardManage::find($id);
            // 見つからない時または削除フラグがONの時はエラーを返す
            if ($bizcardManage == null || $bizcardManage->del_flg !== 0) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000001",
                    'result_message' => '名刺が見つかりません。'
                  ], 404);
            }
            $data = $this->model->find($bizcardManage->version, ['link_page_url']);
            if ($data['link_page_url'] == null) {
                // リンクページURLがnullの場合は生成し保存
                $bizcard = Bizcard::find($bizcardManage->version);
                $link_page_url = config('app.bizcard_show_url') . rawurlencode(AppUtils::encrypt($bizcard->bizcard_id ,true));
                $bizcard->forceFill(['link_page_url' => $link_page_url])->save();
                $data['link_page_url'] = $link_page_url;
            }
            return $this->sendResponse([
                'result_code' => 0,
                'link_page_url' => $data['link_page_url'],
                'error_code' => "",
            ], 'リンクページURL取得に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendApiError([
                'result_code' => 1,
                'error_code' => "E000001",
                'result_message' => 'リンクページURL取得に失敗しました。'
            ], 500);
        }
    }

    /**
     * 指定されたリンクページURLの名刺を返す
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showByLinkPageURL(Request $request)
    {
        // リンクページURLに対応する名刺を取得
        try {
            // link_page_urlが指定されていない場合はnullを返す
            if (!$request->filled('link_page_url')) {
                Log::debug('parameter(link page url) is empty.');
                return response()->json([
                    'data' => [
                        'result_code' => 0,
                        'error_code' => "",
                        'bizcard' => null,
                    ],
                ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            }

            // パラメータをURLエンコードして検索
            $link_page_url = $request->input('link_page_url');
            $encodeTargetStr = substr($link_page_url, strlen(config('app.bizcard_show_url')));
            Log::debug('encode target: ' . $encodeTargetStr);
            $search = config('app.bizcard_show_url') . rawurlencode($encodeTargetStr);
            $bizcard = $this->model->where('link_page_url', $search)->latest()->first();

            // 該当する名刺が無い場合はnullを返す
            if ($bizcard == null) {
                Log::debug('No bizcard. parameter: ' . $search);
                return response()->json([
                    'data' => [
                        'result_code' => 0,
                        'error_code' => "",
                        'bizcard' => null,
                    ],
                ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            }

            // 対応する名刺管理テーブルのレコードを取得し、削除フラグがONの時はnullを返す
            $bizcardManage = BizcardManage::find($bizcard->bizcard_id);
            if ($bizcardManage == null || $bizcardManage->del_flg !== 0) {
                return response()->json([
                    'data' => [
                        'result_code' => 0,
                        'error_code' => "",
                        'bizcard' => null,
                    ],
                ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            }

            // 名刺画像を取得
            $bizcardImg = Storage::disk('s3')->get($bizcard["path"]);
            $base64 = base64_encode($bizcardImg);

            $bizcardData = array(
                'bizcard_id' => $bizcard["id"],
                'name' => $bizcard["name"],
                'company_name' => $bizcard["company_name"],
                'phone_number' => $bizcard["phone_number"],
                'address' => $bizcard["address"],
                'email' => $bizcard["email"],
                'department' => $bizcard["department"],
                'position' => $bizcard["position"],
                'bizcard' => $base64,
            );

            // base64エンコード文字列内のスラッシュをエスケープしないようオプション指定
            return response()->json([
                'data' => [
                    'result_code' => 0,
                    'error_code' => "",
                    'bizcard' => $bizcardData,
                ],
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
                'result_code' => 1,
                'error_code' => "E000000",
                'result_message' => '名刺画像取得処理に失敗しました。'
            ], 400);
        }
    }

    /**
     * 指定された名刺の全バージョンを返す
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getAllVersions(Request $request, $id)
    {
        try {
            $bizcardManage = BizcardManage::find($id);
            // 見つからない時または削除フラグがONの時はエラーを返す
            if ($bizcardManage == null || $bizcardManage->del_flg !== 0) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000001",
                    'result_message' => '名刺が見つかりません。'
                ], 404);
            }
            // 自分に公開されている名刺かチェック
            $user = $request->user();
            if (!$this->checkDisplayTarget($user->id, $bizcardManage)) {
                Log::debug('ログインユーザに公開されていない名刺のためバージョン取得不可');
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000001",
                    'result_message' => '名刺が見つかりません。'
                ], 404);
            }

            $versions = $this->model->where('bizcard_id', $id)->oldest()->get();
            $versionArray = [];

            foreach ($versions as $version) {
                $fileName = basename($version->path);
                $thumbnailPath = str_replace($fileName, 'thumbnail/'. $fileName, $version->path);

                // 名刺のサムネイル画像を取得
                $thumbnail = Storage::disk('s3')->get($thumbnailPath);

                $versionArray[] = array(
                    'id' => $version->id,
                    'name' => $version->name,
                    'name_kana' => $version->name_kana,
                    'name_romaji' => $version->name_romaji,
                    'company_name' => $version->company_name,
                    'company_kana' => $version->company_kana,
                    'phone_number' => $version->phone_number,
                    'address' => $version->address,
                    'address_name' => $version->address_name,
                    'postal_code' => $version->postal_code,
                    'address_en' => $version->address_en,
                    'email' => $version->email,
                    'department' => $version->department,
                    'position' => $version->position,
                    'person_title' => $version->person_title,
                    'url' => $version->url,
                    'created_at' => Carbon::parse($version->created_at)->toDateTimeString(),
                    'create_user' => $version->create_user,
                    'updated_at' => Carbon::parse($version->updated_at)->toDateTimeString(),
                    'update_user' => $version->update_user,
                    'bizcard' => base64_encode($thumbnail),
                );
            }
            // base64エンコード文字列内のスラッシュをエスケープしないようオプション指定
            return response()->json([
                'data' => [
                    'result_code' => 0,
                    'error_code' => "",
                    'versions' => $versionArray,
                ],
            ], 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
                'result_code' => 1,
                'error_code' => "E000000",
                'result_message' => 'バージョン取得処理に失敗しました。'
            ], 400);
        }
    }

    /**
     * OCRサーバと通信し、アップロードされた名刺画像をOCR処理する
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function scan(Request $request) {
        try {
            // 画像ファイル(base64文字列)のバリデーション
            // JPEGのみ受け付けるので最初の文字は'/'のみ可
            $this->validate($request, [
                'file' => [
                    'required', 'starts_with:/'
                ]
            ]);
            $file = base64_decode($request->input('file'));

            // 画像を一時的に保存するディレクトリ
            $tmpPath = storage_path('app/storage/img/tmp/');

            // 画像一時保存用ディレクトリがなければ作成
            if (!File::exists($tmpPath)) {
                mkdir($tmpPath, 0777, true);
                chmod($tmpPath, 0777);
            }

            // 画像を一時保存
            $user = $request->user();
            $fileName = $user->id . '_' . strtotime('now') . '.jpg';
            Storage::put('storage/img/tmp/'. $fileName, $file);

            if (mime_content_type($tmpPath . $fileName) != 'image/jpeg') {
                // MIMEタイプをチェックし、送信されたファイルがJPEGでなければエラー
                Log::error('JPEGではないファイルを受信');

                // 一時保存したファイルを削除
                Storage::delete('storage/img/tmp/' . $fileName);
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'OCR処理に失敗しました。',
                    'items' => null
                  ], 500);
            }

            // OCRサーバに画像を送信
            $api_host = rtrim(config('app.ocr_api_host'), "/");
            $client = new Client(['base_uri' => $api_host, 'timeout' => config('app.guzzle_timeout'),
                                  'connect_timeout' => config('app.guzzle_connect_timeout')]);
            // 通信時間計測開始
            $ocrStart = microtime(true);
            $result = $client->request('POST', "api/meishi-ocr/scan", array(
                'multipart' => array(
                    array(
                        'name' => 'file',
                        'contents' => fopen($tmpPath . $fileName, 'r'),
                    )
                )
            ));

            // 通信時間計測終了
            $ocrEnd = microtime(true);
            $time = $ocrEnd - $ocrStart;
            Log::debug('OCRサーバとの通信時間：' . $time . '秒');

            // 一時保存したファイルを削除
            Storage::delete('storage/img/tmp/' . $fileName);

            if ($result->getStatusCode() == 200) {
                $data = json_decode($result->getBody());
                return $this->sendResponse([
                    'result_code' => 0,
                    'error_code' => "",
                    'items' => $data->items,
                ], 'OCR処理に成功しました。');
            } else {
                Log::debug('OCR Response Status: ' . $result->getStatusCode());
                $detail = json_decode($result->getBody());
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => (isset($detail->type) ? $detail->type : "") . ' ' . (isset($detail->code) ? $detail->code : "E000000"),
                    'result_message' => 'OCR処理に失敗しました。',
                    'items' => null
                  ], $result->getStatusCode());
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => 'OCR処理に失敗しました。',
              'items' => null
            ], 500);
        }
    }

    /**
     * OCRサーバと通信し、アップロードされた画像から名刺画像を切り出す
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detectCard(Request $request) {
        $tmpPath = '';
        $fileName = '';
        try {
            // 画像ファイル(base64文字列)のバリデーション
            // JPEGのみ受け付けるので最初の文字は'/'のみ可
            $this->validate($request, [
                'file' => [
                    'required', 'starts_with:/'
                ]
            ]);
            $file = base64_decode($request->input('file'));

            // 画像を一時的に保存するディレクトリ
            $tmpPath = storage_path('app/storage/img/tmp/');

            // 画像一時保存用ディレクトリがなければ作成
            if (!File::exists($tmpPath)) {
                mkdir($tmpPath, 0777, true);
                chmod($tmpPath, 0777);
            }

            // 画像を一時保存
            $user = $request->user();
            $fileName = $user->id . '_' . strtotime('now') . '.jpg';
            Storage::put('storage/img/tmp/'. $fileName, $file);

            if (mime_content_type($tmpPath . $fileName) != 'image/jpeg') {
                // MIMEタイプをチェックし、送信されたファイルがJPEGでなければエラー
                Log::error('JPEGではないファイルを受信');

                // 一時保存したファイルを削除
                Storage::delete('storage/img/tmp/' . $fileName);
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'OCR処理に失敗しました。',
                    'items' => null
                  ], 500);
            }

            // OCRサーバに画像を送信
            $api_host = rtrim(config('app.ocr_api_host'), "/");
            $client = new Client(['base_uri' => $api_host, 'timeout' => config('app.guzzle_timeout'),
                                  'connect_timeout' => config('app.guzzle_connect_timeout')]);
            $result = $client->request('POST', "api/meishi-ocr/detect_card", array(
                'multipart' => array(
                    array(
                        'name' => 'file',
                        'contents' => fopen($tmpPath . $fileName, 'r'),
                    ),
                    array(
                        'name' => 'correct_color',
                        'contents' => 1,
                    )
                )
            ));

            if ($result->getStatusCode() == 200) {
                Log::debug('OCR Response Status: ' . $result->getStatusCode());
                $imgFile = $result->getBody()->getContents();
                return $this->sendResponse([
                    'result_code' => 0,
                    'error_code' => "",
                    'items' => base64_encode($imgFile),
                ], 'OCR処理に成功しました。');
            } else if ($result->getStatusCode() == 204) {
                Log::debug('OCR Response Status: ' . $result->getStatusCode());
                return $this->sendResponse([
                    'result_code' => 1,
                    'error_code' => "",
                    'items' => null,
                ], '画像がありませんでした。');
            } else {
                Log::debug('OCR Response Status: ' . $result->getStatusCode());
                $detail = json_decode($result->getBody());
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => (isset($detail->type) ? $detail->type : "") . ' ' . (isset($detail->code) ? $detail->code : "E000000"),
                    'result_message' => 'OCR処理に失敗しました。',
                    'items' => null
                  ], $result->getStatusCode());
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => 'OCR処理に失敗しました。',
              'items' => null
            ], 500);
        } finally {
            if(file_exists($tmpPath . $fileName)){
                // 一時保存したファイルを削除
                Storage::delete('storage/img/tmp/' . $fileName);
            }            
        }
    }

    /**
     * 画像加工の処理選択パターンとコマンド情報を返す
     */
    public function getImageProcessingDefinition() {
        try {
            $rtn =$this->getImageProcessingDefinitionFromORC();

            if ($rtn['statusCode'] == 200) {
                // 成功時
                $patterns = $rtn['patterns'];
                return $this->sendResponse([
                    'result_code' => 0,
                    'error_code' => "",
                    'patterns' => $patterns,
                ], '画像加工コマンド情報を取得しました。');
            } else {
                // 失敗時
                $detail = $rtn['patterns'];
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => (isset($detail->type) ? $detail->type : "") . ' ' . (isset($detail->code) ? $detail->code : "E000000"),
                    'result_message' => '画像加工コマンドの取得に失敗しました。',
                    'items' => null
                  ], $detail['statusCode']);
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => '画像加工コマンドの取得に失敗しました。',
              'items' => null
            ], 500);
        }
    }

    /**
     * OCRサーバと通信し、画像の加工パターンとコマンドを取得する
     * @return mixed
     */
    private function getImageProcessingDefinitionFromORC() {
        $api_host = rtrim(config('app.ocr_api_host'), "/");
        $client = new Client(['base_uri' => $api_host, 'timeout' => config('app.guzzle_timeout'),
                                'connect_timeout' => config('app.guzzle_connect_timeout')]);
        $result = $client->request('GET', "api/meishi-ocr/image_processing_definition");

        return [
            'statusCode' => $result->getStatusCode(),
            'patterns' => json_decode($result->getBody())
        ];
    }

    /**
     * 入力画像を加工し返す
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processImageUsePatternDefault(Request $request) {
        $tmpPath = '';
        $fileName = '';
        try {
            $this->validate($request, [
                'file' => [
                    'required', 'starts_with:/'
                ]
            ]);
            $user = $request->user();
            $file = base64_decode($request->input('file'));

            $checkImageResult = $this->checkImageData($file, $user);
            $tmpPath = $checkImageResult['tmpPath'];
            $fileName = $checkImageResult['fileName'];

            if (!$checkImageResult['status']) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'ファイル形式がJPEGではありません。',
                    'items' => null
                    ], 500);
            }

            // リクエストパターンが無ければ返す
            $patternName = $request->input('pattern_name');
            if (empty($patternName)){
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'pattern_nameが設定されていません。',
                    'items' => null
                    ], 500);
            }
            
            // リクエストの加工コマンドの有無をチェック
            $rtn = $this->getImageProcessingDefinitionFromORC();
            
            if ($rtn['statusCode'] == 200) {
                $isPatternExist = false;
                foreach ($rtn['patterns'] as $key => $pattern) {
                    if ($key == 'patterns') {
                        foreach ($pattern as $pattern_info){
                            // objectのpatternsのnameを比較
                            if ($patternName == $pattern_info->name) {
                                $isPatternExist = true;
                                break;
                            }
                       }
                    }
                }
                // リクエストの画像加工パターンが存在しない場合
                if(!$isPatternExist){
                    return $this->sendApiError([
                        'result_code' => 1,
                        'error_code' => "E000000",
                        'result_message' => 'pattern_nameが正しくありません。',
                        'items' => null
                        ], 500);
                }
            } else {
                // 取得失敗時
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'pattern_nameが取得できません。',
                    'items' => null
                    ], 500);
            }

            // OCサーバに画像とパターンを送信
            $api_host = rtrim(config('app.ocr_api_host'), "/");
            $client = new Client(['base_uri' => $api_host, 'timeout' => config('app.guzzle_timeout'),
                                    'connect_timeout' => config('app.guzzle_connect_timeout')]);
            $result = $client->request('POST', "api/meishi-ocr/process_image_use_pattern_default", array(
                'multipart' => array(
                    array(
                        'name' => 'file',
                        'contents' => fopen($tmpPath . $fileName, 'r'),
                    ),
                    array(
                        'name' => 'pattern_name',
                        'contents' => $request['pattern_name'],
                    )
                )
            ));
    
            if ($result->getStatusCode() == 200) {
                // 成功時
                Log::debug('OCR Response Status: ' . $result->getStatusCode());
                $imgFile = $result->getBody()->getContents();
                return $this->sendResponse([
                    'result_code' => 0,
                    'error_code' => "",
                    'items' => base64_encode($imgFile),
                ], 'OCR処理に成功しました。');
            } else {
                // 失敗時
                Log::debug('OCR Response Status: ' . $result->getStatusCode());
                $detail = json_decode($result->getBody());
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => (isset($detail->type) ? $detail->type : "") . ' ' . (isset($detail->code) ? $detail->code : "E000000"),
                    'result_message' => '画像がありませんでした。',
                    'items' => null
                  ], $result->getStatusCode());
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => 'OCR処理に失敗しました。',
              'items' => null
            ], 500);
        } finally {
            if(file_exists($tmpPath . $fileName)){
                // 一時保存したファイルを削除
                Storage::delete('storage/img/tmp/' . $fileName);
            }            
        }
    }

    public function checkImageData($file, $user) {
        $tmpPath = '';
        $fileName = '';
        // 画像ファイル(base64文字列)のバリデーション
        // JPEGのみ受け付けるので最初の文字は'/'のみ可
        // 画像を一時的に保存するディレクトリ
        $tmpPath = storage_path('app/storage/img/tmp/');

        // 画像一時保存用ディレクトリがなければ作成
        if (!File::exists($tmpPath)) {
            mkdir($tmpPath, 0777, true);
            chmod($tmpPath, 0777);
        }

        // 画像を一時保存
        $fileName = $user->id . '_' . strtotime('now') . '.jpg';
        Storage::put('storage/img/tmp/'. $fileName, $file);

        if (mime_content_type($tmpPath . $fileName) != 'image/jpeg') {
            // MIMEタイプをチェックし、送信されたファイルがJPEGでなければエラー
            Log::error('JPEGではないファイルを受信');

            // 一時保存したファイルを削除
            Storage::delete('storage/img/tmp/' . $fileName);
            // [$tmpPath,$fileName]と同じ形でreturn tmp,fileは返すように
            return [
                'status' => false,
                'tmpPath' => $tmpPath,
                'fileName' => $fileName
            ];
        }

        return [
            'status' => true,
            'tmpPath' => $tmpPath,
            'fileName' => $fileName
        ];
    }
    /**
     * Zipファイルの解凍と一時保存を行う
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptZip(Request $request) {
        Log::debug('acceptZip start');
        // ユーザ情報取得
        $mst_user_id = $request->user()->id;
        // 一時保存先ディレクトリ名設定
        $zip_upload_time = (string)strtotime('now');
        $dirBaseName = $mst_user_id . '_' . $zip_upload_time;
        $tmpPath = storage_path('app/storage/img/tmp/' . $dirBaseName);
        $zipFileName = $mst_user_id . '_' . $zip_upload_time . '.zip';
        try {
            // バリデーション
            $this->validate($request, [
                'file' => [
                    'required',
                    'mimetypes:application/zip,application/x-zip-compressed,multipart/x-zip',
                    'file',
                    'max:2048',
                ]
            ]);
            // zip解凍先ディレクトリを作成
            if (!File::exists($tmpPath)) {
                mkdir($tmpPath, 0777, true);
                chmod($tmpPath, 0777);
            }

            // zipを一時保存
            $zipFile = $request->file('file');
            Storage::putFileAs('storage/img/tmp/' . $dirBaseName . '/', $zipFile, $zipFileName);

            // locale設定
            if (setlocale(LC_ALL, 'ja_JP.UTF-8') === false) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'Zipファイルアップロードに失敗しました。',
                    'zip_upload_time' => null
                ], 500);
            }
            // zip解凍
            $zip = new \ZipArchive();
            if ($zip->open($tmpPath . '/' . $zipFileName)) {
                Log::debug('BizcardAPIController.acceptZip: zip解凍');
                $i = 0;
                while ($zip->statIndex($i)) {
                    // ファイル名の生データを取得
                    $rawName = $zip->getNameIndex($i, \ZipArchive::FL_ENC_RAW);
                    // windowsで保存された日本語のファイル名が文字化けしないよう変換
                    $convertName = mb_convert_encoding($rawName, 'UTF-8', 'CP932');
                    // zip内部のディレクトリにファイルが含まれる場合は無視（直下のファイルのみ対象とする）
                    if (strpos($convertName, '/') === false) {
                        // zipに含まれる日本語のファイル名をUTF-8に変換し、解凍
                        $zipEntry = $zip->statIndex($i);
                        $zip->renameName($zipEntry['name'], $convertName);
                        $zip->extractTo($tmpPath, $convertName);
                    }
                    $i++;
                }
                $zip->close();
            } else {
                Log::debug('BizcardAPIController.acceptZip: zip解凍失敗');
                // 一時保存したzipを削除
                if (File::exists($tmpPath . '/' . $zipFileName)) {
                    Storage::delete('storage/img/tmp/' . $dirBaseName . '/' . $zipFileName);
                }
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => 'Zipファイルアップロードに失敗しました。',
                    'zip_upload_time' => null
                ], 500);
            }
            // 一時保存したzipを削除
            Storage::delete('storage/img/tmp/' . $dirBaseName . '/' . $zipFileName);
            // 解凍後のファイル名を取得
            $files = glob($tmpPath . '/*.*');
            $dirs = Storage::directories('storage/img/tmp/' . $dirBaseName);
            foreach ($dirs as $dir) {
                // ディレクトリは削除
                Storage::deleteDirectory($dir);
            }
            // ファイルのチェック
            foreach ($files as $index => $file) {
                $fileName = basename($file);
                // jpgまたはpngファイルでなければファイル削除
                $mime_type = mime_content_type($file);
                if ($mime_type !== 'image/jpeg' && $mime_type !== 'image/png') {
                    Storage::delete('storage/img/tmp/' . $dirBaseName . '/' . $fileName);
                    unset($files[$index]);
                    continue;
                }
            }
            unset($file);
            Log::debug($files);

            // 有効なファイルが1件もなければエラー
            $fileNum = count($files);
            if ($fileNum === 0) {
                // 一時保存用ディレクトリを削除
                Storage::deleteDirectory('storage/img/tmp/' . $dirBaseName);
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => '有効な画像がありません。',
                    'zip_upload_time' => null
                  ], 400);
            }
            // ファイル数が上限を超えている場合、上限まで削除
            if (self::MAX_IMAGE_NUM < $fileNum) {
                $reverseFiles = array_reverse($files);
                foreach ($reverseFiles as $file) {
                    $fileName = basename($file);
                    Storage::delete('storage/img/tmp/' . $dirBaseName . '/' . $fileName);
                    if (--$fileNum <= self::MAX_IMAGE_NUM) {
                        break;
                    }
                }
            }
            return $this->sendResponse([
                'result_code' => 0,
                'error_code' => "",
                'zip_upload_time' => $zip_upload_time,
            ], 'Zipファイルアップロードに成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            // 一時保存したzipを削除
            if (File::exists($tmpPath)) {
                Storage::deleteDirectory('storage/img/tmp/' . $dirBaseName);
            }
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => 'Zipファイルアップロードに失敗しました。',
              'zip_upload_time' => null
            ], 500);
        }
    }

    /**
     * アップロードされたZIPファイルの中身を削除する
     * @param  \Illuminate\Http\Request $request
     * @param  int  $zip_upload_time
     * @return \Illuminate\Http\Response
     */
    public function deleteZipContents(Request $request, $zip_upload_time) {
        try {
            // 消去するファイルの保存先ディレクトリを削除
            Storage::deleteDirectory('storage/img/tmp/' . $request->user()->id . '_' . $zip_upload_time);
            return $this->sendResponse([
                'result_code' => 0,
                'error_code' => "",
            ], 'Zipファイル削除に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => 'Zipファイル削除に失敗しました。',
            ], 500);
        }
    }

    /**
     * CSVファイルの読取を行う
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptCsv(Request $request) {
        // ユーザ情報取得
        $mst_user_id = $request->user()->id;
        // 一時保存先ディレクトリ名設定
        $csv_upload_time = (string)strtotime('now');
        $dirBaseName = $mst_user_id . '_' . $csv_upload_time;
        $tmpPath = storage_path('app/storage/csv/tmp/' . $dirBaseName);
        $csvFileName = $mst_user_id . '_' . $csv_upload_time . '.csv';
        try {
            // バリデーション
            $this->validate($request, [
                'file' => [
                    'required', 'mimes:csv,txt', 'mimetypes:text/plain', 'file',
                ]
            ]);
            // CSV一時保存先ディレクトリを作成
            if (!File::exists($tmpPath)) {
                mkdir($tmpPath, 0777, true);
                chmod($tmpPath, 0777);
            }

            // CSVを一時保存
            $csvFile = $request->file('file');
            Storage::putFileAs('storage/csv/tmp/' . $dirBaseName . '/', $csvFile, $csvFileName);

            // 文字コードがSJISならUTF-8に変換
            $fileContents = file($tmpPath . '/' . $csvFileName, FILE_SKIP_EMPTY_LINES);
            foreach ($fileContents as $key => $line) {
                $detectedEncode = mb_detect_encoding($line, 'ASCII, UTF-8, SJIS-win');
                if ($detectedEncode == 'SJIS-win') {
                    $fileContents[$key] = mb_convert_encoding($line, 'UTF-8', 'CP932');
                }
            }
            // 一時保存したCSVを削除
            Storage::deleteDirectory('storage/csv/tmp/' . $dirBaseName);

            // CSV読取
            $csvData = array_map('str_getcsv', $fileContents);
            // カラムが不足しているデータを取り除く
            foreach ($csvData as $key => $data) {
                if (count($data) < self::CSV_COLUMN_NUM) {
                    unset($csvData[$key]);
                }
            }
            // インデックスを振りなおす
            $csvData = array_values($csvData);
            // 正常なデータが1件もなければエラー
            if (count($csvData) === 0) {
                return $this->sendApiError([
                    'result_code' => 1,
                    'error_code' => "E000000",
                    'result_message' => '有効なデータがありません。',
                    'csv_data' => null,
                ], 500);
            }
            return $this->sendResponse([
                'result_code' => 0,
                'error_code' => "",
                'csv_data' => $csvData,
            ], 'CSVファイルアップロードに成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            // 一時保存したCSVを削除
            if (File::exists($tmpPath)) {
                Storage::deleteDirectory('storage/csv/tmp/' . $dirBaseName);
            }
            return $this->sendApiError([
                'result_code' => 1,
                'error_code' => "E000000",
                'result_message' => 'CSVファイルアップロードに失敗しました。',
                'csv_data' => null,
            ], 500);
        }
    }

    /**
     * 名刺の一括登録を行う
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function multipleRegister(Request $request) {
        try {
            Log::debug('multipleRegister Request Parameter: ' . json_encode($request->all()));
            // バリデーション
            $this->validate($request, [
                'zip_upload_time' => [
                    'required',
                ]
            ]);
            // 登録するファイル名を取得
            $zip_upload_time = $request->input('zip_upload_time');
            $dirBaseName = $request->user()->id . '_' . $zip_upload_time;
            $files = Storage::files('storage/img/tmp/' . $dirBaseName);

            // 名刺データがパラメータに含まれる場合は取得
            $bizcard_data = [];
            if ($request->filled('bizcard_data')) {
                $bizcard_data = $request->input('bizcard_data');
            }
            foreach ($files as $file) {
                $param = [];
                // 画像をbase64文字列に変換し、リクエストパラメータに追加
                $image = Storage::get($file);
                $param['biz_card_image'] = base64_encode($image);
                // 画像と紐づける名刺データを検索
                $fileName = basename($file);
                $key = array_search($fileName, array_column($bizcard_data, 'file_name'));
                if ($key !== false) {
                    // 名刺データがあれば、パラメータにデータを追加
                    $data = $bizcard_data[$key];
                    unset($data['file_name']);
                    $param = $param + $data;
                }
                $request->replace($param);
                $this->store($request);
            }
            // 登録後、アップロードされたzipの中身を削除
            Storage::deleteDirectory('storage/img/tmp/' . $dirBaseName);

            return $this->sendResponse([
                'result_code' => 0,
                'error_code' => "",
            ], count($files) . '件の名刺を登録しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError([
              'result_code' => 1,
              'error_code' => "E000000",
              'result_message' => '名刺登録に失敗しました。',
            ], 500);
        }
    }
}
