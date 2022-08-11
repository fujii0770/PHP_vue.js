<?php namespace App\Http\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\Nullable;

/**
 * Created by PhpStorm.
 * User: lul
 * Date: 2/5/2021
 * Time: 17:26 PM
 */
class BoxUtils
{
    const BOX_API_TOKEN = 'box_cloud_api_token_auto_storage';
    const BOX_API_TOKEN_EXPIRE_TIME = 'box_api_token_expire_time_auto_storage';
    const BOX_API_REFRESH_TOKEN = 'box_api_refresh_token_auto_storage';

    const BOX_NAME = [
        '1' => '署名なし履歴なし',
        '2' => '署名なし履歴あり',
        '3' => '署名あり履歴なし',
        '4' => '署名あり履歴あり',
    ];

    const BOX_HISTORY = [
        '1' => false,
        '2' => true,
        '3' => false,
        '4' => true,
    ];

    const BOX_SIGNATURE = [
        '1' => false,
        '2' => false,
        '3' => true,
        '4' => true,
    ];

    // 自動保管結果(0:デフォルト 1:成功 2:失敗)
    const BOX_AUTOMATIC_STORAGE_DEFAULT = 0;
    const BOX_AUTOMATIC_STORAGE_SUCCESS = 1;
    const BOX_AUTOMATIC_STORAGE_FAIL = 2;

    /**
     * api client
     * @return Client
     */
    public static function getApiClient()
    {
        $client = new Client(['base_uri' => Config::get('oauth.box.base_url'), 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,]);
        return $client;
    }

    /**
     * 認証 api client
     * @param bool $isUpload
     * @param null $customHeaders
     * @return bool|Client
     */
    public static function getAuthorizedApiClient($access_token, $isUpload = false, $customHeaders = null, $hasTimeout = true)
    {
        $baseUrl = Config::get('oauth.box.base_url');

        if ($isUpload) {
            $baseUrl = Config::get('oauth.box.upload_url');
        }

        if ($customHeaders && is_array($customHeaders)) {
            $customHeaders['Authorization'] = 'Bearer ' . $access_token;
        } else {
            $customHeaders = ['Authorization' => 'Bearer ' . $access_token];
        }
        if ($hasTimeout) {
            $client = new Client(['base_uri' => $baseUrl, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false, 'allow_redirects' => ['track_redirects' => true],
                'headers' => $customHeaders]);
        } else {
            $client = new Client(['base_uri' => $baseUrl, 'timeout' => config('app.guzzle_timeout'), 'http_errors' => false, 'verify' => false, 'allow_redirects' => ['track_redirects' => true],
                'headers' => $customHeaders]);
        }

        return $client;
    }

    /**
     * トークン更新Client
     * @return Client
     */
    public static function getAccessTokenClient()
    {
        $client = new Client(['base_uri' => Config::get('oauth.box.urlAccessToken'), 'http_errors' => false, 'verify' => false,]);
        return $client;
    }

    /**
     * Boxリフレッシュトークン更新
     * @param $code string Boxのrefresh_token
     * @param $company_id
     * @param bool $isRefreshToken
     * @return false|mixed
     * if get refresh_token success return 'access_token'(BOXのtoken),
     * else return false.
     */
    public static function refreshAccessToken($code, $company_id, $isRefreshToken = false)
    {
        $client = BoxUtils::getAccessTokenClient();
        if ($isRefreshToken) {
            $params = ['form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $code,
                'client_id' => Config::get('oauth.box.clientId'),
                'client_secret' => Config::get('oauth.box.clientSecret'),
            ]];
        } else {
            $params = ['form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => Config::get('oauth.box.clientId'),
                'client_secret' => Config::get('oauth.box.clientSecret'),
            ]];
        }
        $result = $client->post('', $params);
        $statusCode = $result->getStatusCode();
        if ($statusCode == StatusCodeUtils::HTTP_OK) {
            $token = json_decode((string)$result->getBody(), true);
            if (isset($token['access_token']) && $token['access_token']) {
                DB::table('mst_limit')->where('mst_company_id', $company_id)->update(['box_refresh_token' => $token['refresh_token']]);
                return $token['access_token'];
            } else {
                return false;
            }
        } elseif($statusCode == StatusCodeUtils::HTTP_BAD_REQUEST) {
            // BOX自動保管の更新トークン期限切れのお知らせ
            self::sendBoxConnectFailMail($company_id);
            return false;
        }else{
            return false;
        }
    }

    /**
     * BOX自動保存機能利用可否
     * @param $company_id
     * @return bool
     */
    public static function getAutoStorageBoxIsValid($company_id)
    {
        // boxの権限を取得する
        $limit = DB::table('mst_company')
            ->join('mst_limit', 'mst_company.id', 'mst_limit.mst_company_id')
            ->where('mst_company.id', $company_id)
            ->select('mst_company.box_enabled', 'mst_limit.box_enabled_automatic_storage')
            ->first();

        if (!$limit || !$limit->box_enabled || !$limit->box_enabled_automatic_storage) {
            return false;
        }
        return true;
    }

    /**
     * BOX自動保存機能設定取得
     * @param int $company_id
     * @return array|false
     */
    public static function getAutoStorageBoxSettings(int $company_id)
    {
        // boxの権限を取得する
        $settings = [];
        $company_limit = DB::table('mst_company as c')
            ->join('mst_limit as l', 'c.id', 'l.mst_company_id')
            ->where('c.id', $company_id)
            ->select('c.certificate_flg', 'c.certificate_destination', 'l.box_enabled_folder_to_store', 'l.box_auto_save_folder_id', 'l.box_enabled_output_file',
                'l.box_refresh_token', 'l.box_enabled_automatic_delete', 'l.box_max_auto_delete_days')
            ->first();

        if (!$company_limit) {
            return false;
        }

        //保管先フォルダ
        if (!$company_limit->box_enabled_folder_to_store) {
            return false;
        }
        $settings['box_enabled_folder_to_store'] = $company_limit->box_enabled_folder_to_store;

        //保管先フォルダID
        if (!$company_limit->box_auto_save_folder_id) {
            return false;
        }
        $settings['box_auto_save_folder_id'] = $company_limit->box_auto_save_folder_id;

        //Boxリフレッシュトークン
        if (!$company_limit->box_refresh_token) {
            return false;
        }
        $settings['box_refresh_token'] = $company_limit->box_refresh_token;

        //保管先フォルダID
        if (!$company_limit->box_enabled_output_file) {
            return false;
        }
        $settings['hasHistory'] = false;
        $index = 0;
        foreach (explode(',', $company_limit->box_enabled_output_file) as $value) {
            if (!array_key_exists($value, self::BOX_NAME)) {
                return false;
            }
            $settings['file'][$index]['name'] = self::BOX_NAME[$value];
            $settings['file'][$index]['history'] = self::BOX_HISTORY[$value];
            $settings['file'][$index]['signature'] = self::BOX_SIGNATURE[$value];
            $index++;
            if (self::BOX_HISTORY[$value]) {
                $settings['hasHistory'] = true;
            }
        }
        $settings['signatureKeyFile'] = $company_limit->certificate_flg ? $company_limit->certificate_destination : null;
        $settings['signatureKeyPassword'] = $company_limit->certificate_flg ? $company_limit->certificate_pwd : null;

        return $settings;
    }

    /**
     * BOX自動保存文書作成
     * @param array $document 文書
     * @param bool $has_history 履歴追加要否
     * @param bool $has_signature 署名追加要否
     * @param string $str_sign_history BOX文書名perfix
     * @param $signatureKeyFile 署名
     * @param $signatureKeyPassword 署名password
     * @return array|false
     */
    public static function makeBoxDocuments(array $document, bool $has_history, bool $has_signature, string $str_sign_history, $signatureKeyFile, $signatureKeyPassword)
    {
        $box_document = [];
        // 署名なし履歴なしの場合、直接に連携
        if (!$has_history && !$has_signature) {
            $box_document['file_data'] = AppUtils::decrypt($document['file_data']);
            $box_document['file_name'] = self::getBoxFileName($document['create_user'], trim($document['title']), $document['file_name'], $document['circular_document_id'], $str_sign_history);
            return $box_document;
        }

        // その他場合、stamp api 処理追加要
        $stampApiClient = UserApiUtils::getStampApiClient();
        $stamp_api_result = $stampApiClient->post("signatureAndImpress", [
            RequestOptions::JSON => [
                'signature' => $has_signature,
                'data' => [
                    [
                        'circular_document_id' => $document['circular_document_id'],
                        'pdf_data' => AppUtils::decrypt($document['file_data']),
                        'append_pdf_data' => $has_history ? $document['append_pdf'] : null,
                        'stamps' => [],
                        'texts' => [],
                        'usingTas' => 0
                    ]
                ],
                'signatureKeyFile' => $signatureKeyFile,
                'signatureKeyPassword' => $signatureKeyPassword,
            ]
        ]);
        $resData = json_decode((string)$stamp_api_result->getBody());

        if ($stamp_api_result->getStatusCode() == StatusCodeUtils::HTTP_OK && $resData && $resData->data) {
            $box_document['file_data'] = $resData->data[0]->pdf_data;
            $box_document['file_name'] = self::getBoxFileName($document['create_user'], trim($document['title']), $document['file_name'], $document['circular_document_id'], $str_sign_history);
            return $box_document;
        } else {
            Log::debug('makeBoxDocuments failed:' . (string)$stamp_api_result->getBody());
            return false;
        }
    }

    /**
     * box自動保存ファイル名取得
     * @param $email ユーザー名
     * @param $subject 件名
     * @param $file_name ファイル名
     * @param $document_id 文書ID
     * @param $str_sign_history BOX文書名perfix
     * @return array|string|string[]
     */
    public static function getBoxFileName($email, $subject, $file_name, $document_id, $str_sign_history)
    {
        $subject = mb_substr($subject ?: (mb_substr($file_name, 0, strpos($file_name, '.'))), 0, 50);
        $file_name = $email . '_' . $subject . '_' . base64_encode($document_id) . '_' . $str_sign_history . '.pdf';
        return str_replace(AppUtils::FOLDER_INVALID_CHARS, '_', $file_name);
    }

    /**
     * box自動保存フォルダ名取得
     * @param $email ユーザー名
     * @param $subject 件名
     * @param $file_name ファイル名
     * @param $document_id 文書ID
     * @return array|string|string[] {email_subject(file_name)_document_id(encryp)}
     */
    public static function getBoxFolderName($email, $subject, $file_name, $document_id)
    {
        $subject = mb_substr($subject ?: (mb_substr($file_name, 0, strpos($file_name, '.'))), 0, 50);
        $folder_name = $email . '_' . $subject . '_' . base64_encode($document_id);
        return str_replace(AppUtils::FOLDER_INVALID_CHARS, '_', $folder_name);
    }

    /**
     * box自動保存フォルダ作成
     * @param $access_token
     * @param $folder_name
     * @param $parent_id
     * @param $mst_company_id
     * @return false|mixed
     */
    public static function createBoxFolder($access_token,$folder_name, $parent_id,$mst_company_id)
    {
        $client = BoxUtils::getAuthorizedApiClient($access_token, false, ['Content-Type' => 'application/json','Retry-After' => 30]);
        if (!$client) {
            return false;
        }
        $result = $client->post('folders', [
            RequestOptions::JSON => [
                'name' => $folder_name,
                'parent' => [
                    'id' => $parent_id
                ]
            ]
        ]);
        $statusCode = $result->getStatusCode();
        if ($statusCode == StatusCodeUtils::HTTP_BAD_REQUEST){
            self::sendBoxConnectFailMail($mst_company_id);
            return false;
        } elseif ($statusCode == StatusCodeUtils::HTTP_CONFLICT){
            $re_result = $client->get('folders/'.$parent_id . "?limit=1000");
            $resData = json_decode((string)$re_result->getBody());
            if ($re_result->getStatusCode() == StatusCodeUtils::HTTP_OK){
                foreach ($resData->item_collection->entries as $data_item){
                    if($data_item->type == "folder" && $data_item->name == $folder_name){
                        return $data_item;
                    }
                }
                $files_total = $resData->item_collection->total_count;
                $api_limit = $resData->item_collection->limit;
                if($files_total > $api_limit){
                    $max_page = ceil($files_total * 1.0 / $api_limit);
                    for($page = 1; $page < $max_page; $page++){
                        $result = $client->get('folders/'.$parent_id.'?offset='.($api_limit * $page).'&limit='.$api_limit);
                        $resData = json_decode((string)$result->getBody());
                        if ($result->getStatusCode() == StatusCodeUtils::HTTP_OK){
                            foreach ($resData->item_collection->entries as $data_item){
                                if($data_item->type == "folder" && $data_item->name == $folder_name){
                                    return $data_item;
                                }
                            }
                        }else{
                            Log::error('"BOX自動保存：フォルダ取得に失敗しました。' . $result->getBody());
                            return false;
                        }
                    }
                }
                Log::error('"BOX自動保存：フォルダ取得に失敗しました。' . $result->getBody());
                return false;
            }else{
                Log::error('"BOX自動保存：フォルダ取得に失敗しました。' . $result->getBody());
                return false;
            }
        }
        elseif ($statusCode == StatusCodeUtils::HTTP_FORBIDDEN || $statusCode == StatusCodeUtils::HTTP_NOT_FOUND ||
            $statusCode == StatusCodeUtils::HTTP_SERVICE_UNAVAILABLE){
            Log::warning('createBoxFolder statusCode=' . $statusCode);
            Log::warning(json_encode($result->getHeaders()));
            Log::warning(json_encode($result->getBody()));
            return false;
        }elseif ($statusCode != StatusCodeUtils::HTTP_OK && $statusCode != StatusCodeUtils::HTTP_CREATED) {
            Log::alert("BOX自動保存：フォルダの作成に失敗しました。errorCode={$statusCode}; folder_name={$folder_name}; parent_id={$parent_id};");
            return false;
        }
        return json_decode((string)$result->getBody());
    }

    /**
     * BOXに文書自動連携
     * @param $access_token
     * @param $params
     * @param $file
     * @param $mst_company_id
     * @return false|mixed
     */
    public static function autoStorageToBox($access_token, $params, $file,$mst_company_id)
    {
        $client = BoxUtils::getAuthorizedApiClient($access_token,true, ['Content-Type' => 'multipart/form-data'], false);
        if (!$client) {
            return false;
        }
        $result = $client->post('files/content', [
                'multipart' => [
                    [
                        'name' => 'attributes',
                        'contents' => json_encode(['name' => $params['filename'],
                            'parent' => ['id' => $params['folder_id']]]),
                    ],
                    [
                        'name' => 'contents',
                        'contents' => file_get_contents($file),
                        'filename' => $file
                    ],
                ],
            ]
        );
        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == StatusCodeUtils::HTTP_OK or $statusCode == StatusCodeUtils::HTTP_CREATED) {
            return $resData;
        }elseif($statusCode == StatusCodeUtils::HTTP_CONFLICT){
            Log::warning(json_encode($resData));
            return $resData;
        }elseif($statusCode == StatusCodeUtils::HTTP_BAD_REQUEST){
            self::sendBoxConnectFailMail($mst_company_id);
            return false;
        } else {
            Log::error($result->getBody());
            return false;
        }
    }

    /**
     * BOX自動保管の自動更新が失敗した時（回覧完了時と思われる）に以下のメールを企業管理者に送信してほしいです
     * @param $mst_company_id
     */
    public static function sendBoxConnectFailMail($mst_company_id){

        $admin_user = DB::table('mst_admin')
            ->select(DB::raw('CONCAT(family_name,given_name) as admin_name,email'))
            ->where('role_flg', DB::raw(1))
            ->where('state_flg',AppUtils::STATE_VALID)
            ->where('mst_company_id',$mst_company_id)->first();

        if ($admin_user){
            $data['admin_name'] = $admin_user->admin_name; //企業管理者名

            // BOX自動保管の更新トークン期限切れのお知らせ
            MailUtils::InsertMailSendResume(
            // 送信先メールアドレス
                $admin_user->email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['BOX_REFRESH_TOKEN_UPDATE_FAILED']['CODE'],
                // パラメータ
                json_encode($data, JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_ADMIN,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendBoxRefreshTokenUpdateFailedMail.subject'),
                // メールボディ
                trans('mail.SendBoxRefreshTokenUpdateFailedMail.body', $data)
            );
        }
    }
}