<?php


namespace App\Http\Utils;


use App\Models\RequestInfo;

class CsvUtils
{
    const STATE_VALID = 1; //有効
    const CREATE_PATH = 'create/';
    const SFTP_UPDATE_PATH = 'upload/';
    const UPDATE_PATH = 'update/';

    public static function environment(){
        return '[app' . (config('app.pac_contract_server') + 1) .']';
    }

    /**
     *
     * @param $request_id
     * @param $addresses
     * @param $csv_path
     */
    public static function sendMail($request_id, $addresses,$csv_path){
        $request_info = RequestInfo::where('id', $request_id)->first();
        $result = $request_info->result; // 実行結果
        $data = [
            'id' => $request_id,
            'command' => $request_info->command,// コマンド
            'request_datetime' => $request_info->request_datetime, // リクエスト受付時間
            'execution_start_datetime' => $request_info->execution_start_datetime,// 実行開始時間
            'execution_end_datetime' => $request_info->execution_end_datetime, // 実行終了時間
            'message1' => $request_info->message,// メッセージ
            'file_path' => $csv_path, // ディレクトリ
        ];
        foreach ($addresses as $address){
            // 実行結果区分
            if ($result == '1') {
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $address,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CSV_SEND_SUCCESS']['CODE'],
                    // パラメータ
                    json_encode($data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    trans('mail.prefix.admin') . CsvUtils::environment() . trans('mail.SendCsvSuccessMail.subject'),
                    // メールボディ
                    trans('mail.SendCsvSuccessMail.body', $data));
            }else{
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $address,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CSV_SEND_FAILED']['CODE'],
                    // パラメータ
                    json_encode($data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    trans('mail.prefix.admin') . CsvUtils::environment() . trans('mail.SendCsvFailedMail.subject'),
                    // メールボディ
                    trans('mail.SendCsvFailedMail.body', $data));
            }
        }
    }
    
    /**
     * PAC_5-2133
     * @param $index
     * @param string $series
     * @return string
     */
    public static function getSeriesByIndex($index)
    {
        $remainder = $index % 26;
        $multiple = (int)($index / 26);
        $series = chr(ord('A') + $remainder);
        if ($multiple > 0) {
            return self::getSeriesByIndex($multiple - 1) . $series;
        }
        return $series;
    }
}