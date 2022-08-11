<?php

namespace App\Jobs\FormIssuance;


use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\DownloadRequestApiControllerUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\UserApiUtils;
use App\Jobs\PushNotify;
use App\Jobs\SendNotification;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\MailUtils;
use App\Utils\FormIssuanceUtils;
use App\Http\Utils\StampUtils;
use App\Utils\OfficeConvertApiUtils;
use Knox\PopplerPhp\PdfToCairo;
use Knox\PopplerPhp\Constants;
use Illuminate\Support\Facades\File;

/**
 * 回覧データを作成するクラス
 */
class CircularMaker {

    private $imp_mgr;
    private $form_template;
    /**
     * @return FormDataMaker
     */
    private $data_maker;
    private $flog;
    private $company;
    private $user;
    private $flags;
    private $from;
    private $server_env;
    private $edition_flg;
    private $server_flg;

    /**
     * @param
     */
    public function init(object $mgr, object $company, object $user, object $form_template, FormDataMaker $data_maker, FormLogger $flog, string $from = null) {
        $this->imp_mgr = $mgr;
        $this->form_template = $form_template;
        $this->flog = $flog;
        $this->data_maker = $data_maker;
        $this->company = $company;
        $this->user = $user;
        $this->flags = new FormFlagChecker($mgr->id, $mgr->mst_company_id);
        $this->files = FormIssuanceUtils::import_files_operator($mgr);
        $this->files->set_template_filename($form_template->file_name);
        $this->from = $from;
        $this->server_env = config('app.server_env');
        $this->edition_flg = config('app.edition_flg');
        $this->server_flg = config('app.server_flg');
    }

    /**
     *
     */
    private function copy_to_work_template() {
        return $this->files->copy_to_work_template();
    }

    /**
     *
     */
    private function get_work_outpath($form_data) {
         return $this->files->create_work_work_filepath($form_data->id);
    }

    /**
     * @return array
     */
    private function _get_template_placeholders() {
        $frm_template_id = $this->form_template->id;
        $company_id = $this->imp_mgr->mst_company_id;
        return $this->get_template_placeholders($frm_template_id, $company_id);
    }

    /**
     * @return array
     */
    protected function get_template_placeholders($frm_template_id, $company_id) {
        return DB::table("frm_template_placeholder")
            ->where("frm_template_id", $frm_template_id)
            ->where("mst_company_id", $company_id)
            ->select("frm_template_placeholder_name as placeholder_name", "cell_address", "additional_flg")
            ->orderBy("id")
            ->get()
            ->toArray();
    }

    /**
     *
     */
    private function _get_auto_stamps() {
        $frm_template_id = $this->form_template->id;
        $company_id = $this->imp_mgr->mst_company_id;
        return $this->get_auto_stamps($frm_template_id, $company_id);
    }

    /**
     *
     */
    private function get_auto_stamps($frm_template_id, $company_id) {
        return DB::table('frm_template_stamp AS fts')
            ->leftJoin('mst_company_stamp', function($join){
                // 共通印
                $join->where('fts.stamp_flg', StampUtils::COMMON_STAMP);
                $join->where('mst_company_stamp.del_flg', '!=', 1);
                $join->on('fts.stamp_id', 'mst_company_stamp.id');
            })
            ->leftJoin('department_stamp', function($join){
                // 部署名入り印
                $join->where('fts.stamp_flg', StampUtils::DEPART_STAMP);
                $join->on('fts.stamp_id', 'department_stamp.id');
            })
            ->leftJoin('mst_stamp', function($join){
                // 氏名印/日付印
                $join->where('fts.stamp_flg', StampUtils::NORMAL_STAMP);
                $join->on('fts.stamp_id', 'mst_stamp.id');
            })
            ->leftJoin('mst_company_stamp_convenient', function($join) {
                // 便利印
                $join->where('fts.stamp_flg', StampUtils::CONVENIENT_STAMP);
                $join->on('fts.stamp_id', 'mst_company_stamp_convenient.id');
            })
            ->leftJoin('mst_stamp_convenient', function($join) {
                // 便利印(画像)
                $join->on('mst_stamp_convenient.id', 'mst_company_stamp_convenient.mst_stamp_convenient_id');
            })
            ->where("fts.frm_template_id", $frm_template_id)
            ->where("fts.mst_company_id", $company_id)
            ->orderBy("fts.id")
            ->select(
                'fts.id',
                'fts.stamp_top as y_axis',
                'fts.stamp_left as x_axis',
                'fts.stamp_deg as rotateAngle',
                'fts.stamp_page as page',
                'fts.stamp_id as stamp_id',
                'fts.stamp_flg',
                'fts.stamp_deg',
                'fts.stamp_date',
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_name     WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_name     WHEN '.StampUtils::DEPART_STAMP.' THEN CONCAT(department_stamp.face_up1,department_stamp.face_up2,department_stamp.face_down1,department_stamp.face_down2) ELSE mst_stamp.stamp_name END stamp_name'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.font           WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.font ELSE  mst_stamp.font END font'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_image    WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_image    WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.stamp_image ELSE mst_stamp.stamp_image END stamp_image'),
                DB::raw(' (CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.width         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.width          WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.width       ELSE mst_stamp.width       END) / 1000 width'),
                DB::raw(' (CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.height        WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.height         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.height      ELSE mst_stamp.height      END) / 1000 height'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_width     WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_width     WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_width  ELSE mst_stamp.date_width  END date_width'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_height    WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_height    WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_height ELSE mst_stamp.date_height END date_height'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_x         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_x         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_x      ELSE mst_stamp.date_x      END date_x'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_y         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_y         WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.date_y      ELSE mst_stamp.date_y      END date_y'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.date_color     WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.date_color     WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.color       ELSE null                  END color'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.serial         WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_company_stamp_convenient.serial WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.serial      ELSE mst_stamp.serial      END serial'),
                DB::raw( 'CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.stamp_division WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_stamp_convenient.stamp_date_flg WHEN '.StampUtils::NORMAL_STAMP.' THEN mst_stamp.stamp_division     ELSE null                  END stamp_division'),
                DB::raw(' CASE fts.stamp_flg WHEN '.StampUtils::COMMON_STAMP.' THEN mst_company_stamp.create_at WHEN '.StampUtils::CONVENIENT_STAMP.' THEN mst_company_stamp_convenient.create_at WHEN '.StampUtils::DEPART_STAMP.' THEN department_stamp.create_at ELSE mst_stamp.create_at END create_at')
            )
            ->get()
            ->toArray();
    }

    /**
     *
     */
    private function get_data_query() {
        return $this->data_maker->get_data_query();
    }

    /**
     * 回覧文書およびデータを作成します。
     */
    public function make() {
        $ret = 0;
        $flog = $this->flog;
        $cids = [];
        // テンプレート取得
        $placeholders= $this->_get_template_placeholders();
        $work_template_path = $this->copy_to_work_template();
        $stamps = $this->_get_auto_stamps();
        $data_query = $this->get_data_query();

        $form_creator = FormCreatorFactory::get($this->form_template->document_type);
        $form_creator->init($this->imp_mgr, $this->form_template, $work_template_path, $placeholders);

        try {
            $rows = 0;
            foreach ($data_query->cursor() as $data) {
                $this->flags->check_flags();

                $flog->write_begin_line("    ". $data->frm_seq."  ".$data->company_frm_id);

                [$pdf_data, $form_filename, $img] = $this->make_pdf($form_creator, $data, $stamps, $flog);

                DB::beginTransaction();
                try {
                    $flog->write(" -> 回覧データの作成");
                    $circular_id = $this->save_circular($pdf_data, $form_filename, $data, $stamps, $img, $cids);
                    if ($circular_id == -1 || $circular_id == -2 || $circular_id == -3){
                        DB::rollBack();
                        $error_msg = $circular_id == -1 ?  'テンプレート設定に申請者を指定してください。' : 'テンプレート設定に承認者を指定してください。';
                        $this->update_mgr_message($error_msg);
                        throw new MakeCircularException("", 0, null, $ret + 1);
                    }
                    $this->update_data($circular_id, $data);
                    $rows++;
                    $this->update_count($rows);
                    $flog->write("...成功 (")->write_timestamp()->write(") : 文書ID = $circular_id");
                    DB::commit();
                    $ret++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::debug($e->getMessage() . $e->getTraceAsString());
                    $flog->write("...失敗 (")->write_timestamp()->write(") ");
                    throw new MakeCircularException("", 0, $e, $ret);
                }
                $flog->write_eol();
            }

        }finally{
            if (!is_null($work_template_path)) @unlink($work_template_path);
            $form_creator->dispose();
        }

        try{
            // 契約サイトから
            if($this->from) {
                $user = $this->user;
                // 申請者情報
                $user = DB::table('mst_user')
                    ->where('id', $user->id)
                    ->select(['id', 'email', 'mst_company_id',DB::raw('false as auditUser')])
                    ->first();
                // ダウンロード情報を作成
                $fileName = DownloadRequestApiControllerUtils::getDefaultFileName($user, $cids, '', 0, false);
                $download_request = DB::table('frm_imp_mgr')
                    ->where('id', $this->imp_mgr->id)
                    ->select('download_request_code')
                    ->first();
                // ファイル名の入力有り
                $download_request_code = $download_request->download_request_code;
                if ($download_request_code != '') {
                    $file_names = preg_split('/_/', $download_request_code);
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    // No Extension
                    $ext = $ext == "" ? "" : '.' . $ext;
                    $fileName = $file_names[7].'_'.$file_names[8]. $ext;
                }
                $params = array(
                    'cids' => $cids,
                    'fileName' => $fileName,
                    'finishedDate' => '',
                    'stampHistory' => false,
                    'from' => $this->from,
                    'frm_id' => $this->imp_mgr->id
                );
                // ダウンロードJob登録
                $result = DownloadUtils::downloadRequest(
                    $user, 'App\Http\Utils\DownloadRequestApiControllerUtils', 'getCircularsDownloadData', $fileName,
                    $user, $params
                );
                if(!($result === true)){
                    DB::table('frm_imp_mgr')
                        ->where('id', $this->imp_mgr->id)
                        ->update([
                            'download_request_message' => $result
                        ]);
                    $error_msg = 'ダウンロードファイルを作成失敗しました。';
                    $flog->write("...失敗 (")->write_timestamp()->write(") ");
                    $this->update_mgr_message($error_msg);
                }
            }
        } catch(\Exception $e){
            $flog->write("...失敗 (")->write_timestamp()->write(") ");
        }

        return $ret;
    }

    private function update_count($rows) {
        return DB::table("frm_imp_mgr")
            ->where("id", $this->imp_mgr->id)
            ->update(["registered_rows"=>$rows]);
    }

    /**
     * @param $message
     * @throws FormImportException
     */
    private function update_mgr_message($message) {
        $imid = $this->imp_mgr->id;
        // 保存
        DB::beginTransaction();
        try {
            $imp_mgr = DB::table('frm_imp_mgr')
                ->where('id', $imid)->first();

            $mgr_message = $imp_mgr->download_request_message ? $imp_mgr->download_request_message.'\r\n'.$message : $message;

            DB::table('frm_imp_mgr')
                ->where('id', $imid)
                ->update([
                    'download_request_message' => $mgr_message,
                ]);

            DB::commit();
        } catch( \Exception $e) {
            DB::rollback();
            throw new FormImportException("Failed to update the data.", 0, $e);
        }
    }


    /**
     *
     */
    private function make_pdf($form_creator, $data, $stamps, $flog) {
        $pdf_data = null;
        $form_filename = null;
        $work_outpath = null;
        $work_pdf_path = null;
        $img_1st = null;
        try {
            $flog->write(" -> PDFの作成");
            $work_outpath = $this->get_work_outpath($data);
            $form_creator->create($data, $work_outpath);
            $form_filename = $this->create_filename($data);
            $work_pdf_path = $this->to_pdf($work_outpath, $data->id);

            $pdf_data = base64_encode(file_get_contents($work_pdf_path));
            if ($stamps != null && count($stamps) > 0) {
                $pdf_data = $this->stamp_on_pdf($pdf_data, $stamps, $form_filename, null);
                file_put_contents($work_pdf_path, base64_decode($pdf_data));
            }
            $img_1st = $this->get_1st_page_image($work_pdf_path);
            $flog->write("...成功 (")->write_timestamp()->write(")");
        } catch (\Exception $e) {
            $flog->write("...失敗 (")->write_timestamp()->write(") ");
            throw new FormImportException("Failed to make the pdf.", 0, $e);
        } finally {
            if (!is_null($work_pdf_path)) @unlink($work_pdf_path);
            if (!is_null($work_outpath)) @unlink($work_outpath);
        }
        return [$pdf_data, $form_filename, $img_1st];
    }

    /**
     *
     */
    protected function create_filename($data) {
        return $data->frm_name.".pdf";
    }


    /**
     * 作成したファイルをPDFへ変換
     */
    public function to_pdf($filePath, $data_id) {
        $outpath = $this->files->create_work_pdf_filepath($data_id);
        self::tryConvertOfficeToPdf($filePath, $outpath);
        return $outpath;
    }


    /**
     *  作成したPDFに自動捺印
     */
    public function stamp_on_pdf($pdf64data, $stamps, $filename, $circular_document_id) {

        [$appEnv, $appEdition, $appServer] = $this->_envs();

        $user = $this->user;
        $rstamps = [];
        $company = $this->company;
        $appBaseUrl = CircularUserUtils::getEnvAppUrlByEnv($appEnv, $appServer, $appEdition, $company);
        $appBaseUrl = rtrim($appBaseUrl, "/");
        $appBaseUrl = rtrim($appBaseUrl, "/login");
        
        foreach ($stamps as $stamp) {
            // 日付編集
            $date = \App\Http\Utils\DateJPUtils::convert($stamp->stamp_date, $company->dstamp_style?:'y.m.d');
            $stamp->stamp_data = StampUtils::processStampImage($stamp, $date, true);;

            $info_id = hash('SHA256', $appEnv.$appServer.$user->email.$filename.rand().time());
            $stamp->info_id = $info_id;
            $s = get_object_vars($stamp);
            $s["stamp_url"] = $appBaseUrl.'/StampInfo/'.$info_id;
            $rstamps[] = $s;
        }

        $pdfBase64 = chunk_split($pdf64data);
        $file = [
            "circular_document_id" => $circular_document_id,
            "confidencial_flg" => 0,
            "parent_send_order" => 0,
            "usingTags" => false,
            "append_pdf_data" => null,
            "pdf_data" => $pdfBase64,
            "stamps" => $rstamps
        ];

        // $company = $this->company;
        $stampClient = UserApiUtils::getStampApiClient();

        $hasSignature = 0;
        $signatureKeyFile = $company->certificate_flg ? $company->certificate_destination : null;
        $signatureKeyPassword = $company->certificate_flg ? $company->certificate_pwd : null;
        $result = $stampClient->post("signatureAndImpress", [
            RequestOptions::JSON => [
                'signature' => $hasSignature,
                'data' => [$file],
                'signatureKeyFile' => $signatureKeyFile,
                'signatureKeyPassword' => $signatureKeyPassword,
            ]
        ]);
        $resData = json_decode((string)$result->getBody());

        if ($result->getStatusCode() == 200) {
            if ($resData && $resData->data && $resData->data[0]) {
                $pdf_data = $resData->data[0]->pdf_data;
            }
        }
        return $pdf_data;
    }


    /**
     * @param string $pdf_data Base64
     * @param string $file_name
     * @param $data
     * @param $stamps
     * @param $img
     * @return int -1: 回覧の申請者がいない| -2:完了保存: 回覧の承認者がいない| -3:自動回覧: 回覧の承認者がいない
     */
    public function save_circular(string $pdf_data, string $file_name, $data, $stamps, $img, &$cids, $circular_id = 0, $company = null, $user = null, $tpl = null) {
        $user = $this->user ?? $user;
        $company = $this->company ?? $company;
        $now = date_create();
        $form_template = $this->form_template ?? $tpl;
        $this->server_env = config('app.server_env');
        $this->edition_flg = config('app.edition_flg');
        $this->server_flg = config('app.server_flg');


        // circular_author
        $circular_author = DB::table('frm_template_circular_user')
            ->where("frm_template_id", $form_template->id)
            ->where("parent_send_order", 0)
            ->where("child_send_order", 0)
            ->first();

        if(!$circular_author){
            Log::debug('CircularMaker@save_circular: 回覧の申請者がいない。');
            return -1;
        }

        // 回覧IDあれば、画面で明細インポートをクリック
        if(!$circular_id){
            // circular
            $circular_id = $this->insert_circular($user, AppUtils::encrypt($img), $now,$circular_author);
            $cids[] = $circular_id;
            // circular_document
            $circular_document_id = $this->insert_circular_document(
                $circular_id,
                $file_name,
                AppUtils::getFileSize($pdf_data),
                $company,
                $user,
                $now,
                $circular_author
            );
            // document_data
            $this->insert_document_data($circular_document_id, AppUtils::encrypt($pdf_data), $user, $now);

        }else{
            // 回覧IDあれば、画面で明細作成をクリック
            $circular_document_id = DB::table('circular_document')
                ->select('id')
                ->where('circular_id', $circular_id)
                ->pluck('id')
                ->first();
            DB::table('circular_operation_history')
                ->where('circular_id', $circular_id)
                ->delete();
            DB::table('circular')
                ->where("id", $circular_id)
                ->update([
                    'mst_user_id' => $circular_author->mst_user_id,
                ]);
        }

        // circular_operation_history 1:作成
        $this->insert_circular_operation_history(
            $circular_id
            , $circular_document_id
            , $user
            ,CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS
            , $now
            ,$circular_author);
        // 捺印
        if ($stamps != null && count($stamps) > 0) {
            $stamp_info = DB::table('stamp_info')
                ->where('circular_document_id', $circular_document_id)
                ->first();
            // 捺印あり
            $stamp_ope = 1;
            foreach ($stamps as $stamp) {
                // circular_operation_history 2:捺印
                $circular_operation_history_id = $this->insert_circular_operation_history(
                    $circular_id
                    , $circular_document_id
                    , $user
                    ,CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS
                    , $now
                    ,$circular_author);
                if(!$stamp_info){
                    $this->insert_stamp_info($circular_operation_history_id, $circular_document_id, $file_name, $stamp, $user, $now,$circular_author);
                }
            }
            if($stamp_info){
                DB::table('stamp_info')
                    ->where('circular_document_id', $circular_document_id)
                    ->update([
                        'name' => $circular_author->name,
                        'email' => $circular_author->email,
                    ]);
            }
        }else{
            // 捺印なし
            $stamp_ope = 0;
        }

        // コメント
        if($form_template->message){
            // circular_operation_history 4:コメント
            $circular_operation_history_id = $this->insert_circular_operation_history(
                $circular_id
                , $circular_document_id
                , $user
                ,CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS
                , $now
                ,$circular_author);
            $this->insert_document_comment_info($circular_operation_history_id, $circular_document_id, $file_name, $form_template, $user, $now,$circular_author);
        }

        // circular_user + operation_history(申請承認系) -- start
        if($form_template->auto_ope_flg == FormIssuanceUtils::AUTO_OPE_SAVE){
            // 0:保存 下書き一覧へ　申請前
            $this->auto_circular_save($circular_id,$user,$company,$now, $form_template);

        }elseif($form_template->auto_ope_flg == FormIssuanceUtils::AUTO_OPE_COMPLETE){
            // 1:完了保存　回覧完了まで
            $auto_complete_flg = $this->auto_circular_complete($circular_id,$user,$company,$now,$file_name,$stamp_ope, $form_template);
            if (!$auto_complete_flg){
                Log::debug('save_circular@auto_circular_complete(完了保存): 回覧の承認者がいない。');
                return -2;
            }
        }elseif($form_template->auto_ope_flg == FormIssuanceUtils::AUTO_OPE_APPLY){
            // 2:自動回覧　回覧申請まで
            $auto_apply_flg = $this->auto_circular_apply($circular_id,$user,$company,$now,$file_name,$stamp_ope, $form_template);
            if (!$auto_apply_flg){
                Log::debug('save_circular@auto_circular_apply(自動回覧): 回覧の承認者がいない。');
                return -3;
            }
        }



        return $circular_id;
    }

    /**
     *
     */
    protected function insert_circular($user, $img, $now,$circular_author) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        // 回覧順変更許可 address_change_flg
        // テキスト追加許可 text_append_flg
        // サムネイル非表示 hide_thumbnail_flg
        // 捺印設定 require_print
        // アクセス_社内利用 access_code_flg
        // アクセス_社内コード access_code
        // アクセス_社外利用 outside_access_code_flg
        // アクセス_社外コード outside_access_code
        // 再通知日 re_notification_day
        return DB::table('circular')->insertGetId([
            'mst_user_id' => $circular_author->mst_user_id,
            'address_change_flg' => $this->form_template->address_change_flg,
            'text_append_flg' => $this->form_template->text_append_flg,
            'hide_thumbnail_flg' => $this->form_template->hide_thumbnail_flg,
            'require_print' => $this->form_template->require_print,
            'access_code_flg' => $this->form_template->access_code_flg,
            'access_code' => $this->form_template->access_code,
            'outside_access_code_flg' => $this->form_template->outside_access_code_flg,
            'outside_access_code' => $this->form_template->outside_access_code,
            're_notification_day' => $this->form_template->re_notification_day,
            'circular_status' => CircularUtils::SAVING_STATUS,
            'env_flg' => $server_env,
            'edition_flg' => $edition_flg,
            'server_flg' => $server_flg,
            'first_page_data' => $img,
            'create_at' => $now,
            'create_user' => $user->email,
            'update_at' => $now,
            'update_user' => $user->email,
            'final_updated_date' => $now,
            'has_signature' => $this->company->esigned_flg,
        ]);
    }

    /**
     *
     */
    protected function insert_circular_document($circular_id, $file_name, $file_size, $company, $user, $now,$circular_author) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        // 会社跨ぐ考慮なし
        return DB::table('circular_document')->insertGetId([
            'circular_id'=> $circular_id,
            'origin_env_flg' => $server_env,
            'origin_edition_flg' => $edition_flg,
            'origin_server_flg' => $server_flg,
            'create_user_id' => $circular_author->mst_user_id,
            'confidential_flg'=> CircularUtils::CONFIDENTIAL_INVALID,
            'file_name'=> $file_name,
            'create_at' => $now,
            'create_user' => $user->email,
            'update_at' => $now,
            'update_user' => $user->email,
            'file_size' => $file_size,
            'document_no' => 1,
            'origin_document_id' => -1,
            'parent_send_order' => 0,
            'create_company_id' => $company->id,
        ]);
    }

    /**
     *
     */
    protected function insert_document_data($circular_document_id, $encrypted_data, $user, $now) {
        DB::table('document_data')->insert([
            'circular_document_id'=> $circular_document_id,
            'file_data'=> $encrypted_data,
            'create_at' => $now,
            'create_user' => $user->email,
            'update_at' => $now,
            'update_user' => $user->email
        ]);
    }

    /**
     *
     */
    protected function insert_stamp_info($circular_operation_history_id, $circular_document_id, $file_name, $stamp, $user, $now,$circular_author) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

//        $user_name = $this->get_username($user);


        $aid = null;
        $asgn_stamp = $this->get_assign_stamp($circular_author, $stamp->stamp_id);
        if (!is_null($asgn_stamp)) {
            $aid = $asgn_stamp->id;
        }

        $stamp_info = [
            'circular_document_id'=> $circular_document_id,
            'circular_operation_id'=> $circular_operation_history_id,
            'mst_assign_stamp_id'=> $aid,
            'parent_send_order'=> 0,
            'stamp_image'=> $stamp->stamp_data,
            'name' => $circular_author->name,
            'email' => $circular_author->email,
            'env_flg' => $server_env,
            'edition_flg' => $edition_flg,
            'server_flg' => $server_flg,
            'info_id' => $stamp->info_id,
            'file_name' => $file_name,
            'create_at' => $now,
            // 'time_stamp_permission'
            'serial' => $stamp->serial
        ];

        DB::table('stamp_info')->insert($stamp_info);
    }

    private function get_assign_stamp($user, $stamp_id) {
        $ret = DB::table('mst_assign_stamp')
        ->select(
            'mst_assign_stamp.id'
        )
        ->join('mst_company_stamp', function($join) {
            $join->on('mst_assign_stamp.stamp_id', '=', 'mst_company_stamp.id');
        })
        ->where('mst_assign_stamp.stamp_id', $stamp_id)
        ->where('mst_assign_stamp.stamp_flg', StampUtils::COMMON_STAMP)
        ->where('mst_assign_stamp.mst_user_id', $user->mst_user_id)
        ->where('mst_assign_stamp.state_flg', AppUtils::STATE_VALID)
        ->where('mst_company_stamp.del_flg', '!=', 1)
        ->first();
        return $ret;
    }
    /**
     *
     */
    protected function insert_document_comment_info($circular_operation_history_id, $circular_document_id, $file_name, $form_template, $user, $now,$circular_author) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        $document_comment_info = [
            'circular_document_id'=> $circular_document_id,
            'circular_operation_id'=> $circular_operation_history_id,
            'parent_send_order'=> 0,
            'name' => $circular_author->name,
            'email' => $circular_author->email,
            'text' => $form_template->message,
            'private_flg' => 0,
            'create_at' => $now,
        ];

        DB::table('document_comment_info')->insert($document_comment_info);
    }

    /**
     *
     */
    protected function insert_circular_operation_history($circular_id, $circular_document_id, $user, $circular_status, $now,$circular_author) {
        return DB::table('circular_operation_history')->insertGetId([
            'circular_id'=> $circular_id,
            'circular_document_id' => $circular_document_id,
            'operation_email' => $circular_author->email,
            'operation_name' => $circular_author->name,
            'acceptor_email'=> '',
            'acceptor_name'=> '',
            'circular_status'=> $circular_status,
            'create_at' => $now,
        ]);
    }

    /**
     *
     */
    protected function insert_circular_users($circular_id, $data, $user, $company, $now) {
        $this->insert_circular_user($circular_id, $user, 0, 0, $user, $company, $now);
        // $this->insert_circular_user($circular_id, $data->to_name, $data->to_email, 1, 1, $user, $now);
    }

    /**
     *
     */
    protected function insert_circular_user($circular_id, $circular_user, $parent_order, $child_order,$circular_status,$title,$user,$company,$now,$return_flg) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        $name = $this->get_username($circular_user);
        $email = $circular_user->email;

        DB::table('circular_user')->insert([
            'parent_send_order' => $parent_order,
            'child_send_order' => $child_order,
            'circular_status' => $circular_status,
            'return_flg' => $return_flg,
            'env_flg' => $server_env,
            'edition_flg' => $edition_flg,
            'server_flg' => $server_flg,
            'title' => $title,
            'email' => $email,
            'name' => $name,
            'circular_id' => $circular_id,
            'create_at' => $now,
            'received_date' => null,
            'update_at' => $now,
            'create_user' => $user->email,
            'update_user' => $user->email,
            'del_flg' => 0,
            'mst_user_id' => $user->id,
            'mst_company_id' => $company->id,
            'mst_company_name' => $company->company_name
        ]);
    }

    /**
     * 保存、下書き一覧へ
     */
    protected function auto_circular_save($circular_id,$user,$company,$now, $form_template) {

        $circular_users = array();
        $viewing_users = array();

        // テンプレート　設定した宛先
        $template_circular_users = DB::table('frm_template_circular_user')
            ->where("frm_template_id", $form_template->id)
            ->orderBy("parent_send_order")
            ->orderBy("child_send_order")
            ->get()
            ->toArray();

        // ユーザー編集　　状態：0(未通知)
        foreach($template_circular_users as $template_circular_user){
            // ユーザー編集
            $circular_user = [
                'circular_id' => $circular_id,
                'edition_flg' => $template_circular_user->edition_flg,
                'env_flg' => $template_circular_user->env_flg,
                'server_flg' => $template_circular_user->server_flg,
                'parent_send_order' => $template_circular_user->parent_send_order,
                'child_send_order' => $template_circular_user->child_send_order,
                'return_flg' => $template_circular_user->return_flg,
                'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                'email' => $template_circular_user->email,
                'name' => $template_circular_user->name,
                'title' => $form_template->title,
                'received_date' => null,
                'create_at' => $now,
                'create_user' => $user->email,
                'update_at' => $now,
                'update_user' => $user->email,
                'del_flg' => 0,
                'mst_user_id' => $template_circular_user->mst_user_id,
                'mst_company_id' => $template_circular_user->mst_company_id,
                'mst_company_name' => $template_circular_user->mst_company_name
            ];
            $circular_users[] = $circular_user;
        }

        // データ登録
        DB::table('circular_user')->insert($circular_users);

        // 閲覧ユーザー
        $template_viewing_users = DB::table('frm_template_viewing_user')
            ->where("frm_template_id", $form_template->id)
            ->get()
            ->toArray();

        foreach($template_viewing_users as $template_viewing_user){
            $viewing_user = [
                'circular_id' => $circular_id,
                'parent_send_order' => $template_viewing_user->parent_send_order,
                'mst_company_id' => $template_viewing_user->mst_company_id,
                'mst_user_id' => $template_viewing_user->mst_user_id,
                'memo' => '',
                'del_flg' => 0,
                'create_user' => $user->email,
                'create_at' => $now,
                'update_user' => $user->email,
            ];
            $viewing_users[] = $viewing_user;
        }

        DB::table('viewing_user')->insert($viewing_users);

    }

    /**
     * 完了保存、完了一覧へ
     */
    protected function auto_circular_complete($circular_id,$user,$company,$now,$file_name,$stamp_ope, $form_template) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        // circular
        DB::table('circular')
            ->where("id", $circular_id)
            ->update([
                'circular_status' => CircularUtils::CIRCULAR_COMPLETED_STATUS,
                'applied_date' => $now,
                'completed_date' => $now,
            ]);
        $circular = DB::table('circular')
            ->where("id", $circular_id)
            ->first();

        $circular_users = array();
        $viewing_users = array();
        $circular_operation_histories = array();
        $mailDatas = array();
        $noticeMailDatas = array();

        // ■申請者（一番目）
        // 1) 登録あり、return_flgのみ登録値利用
        $template_circular_users = DB::table('frm_template_circular_user')
            ->where("frm_template_id", $form_template->id)
            ->orderBy("parent_send_order")
            ->orderBy("child_send_order")
            ->get()
            ->toArray();

        if(count($template_circular_users) > 1){
            // 最終承認者
            $last_appr_no = count($template_circular_users) - 1;
            $last_appr_parent_o = $template_circular_users[$last_appr_no]->parent_send_order;
            $last_appr_child_o = $template_circular_users[$last_appr_no]->child_send_order;
        }else{
            return false;
        }

        // 回覧申請者情報
        $author_email = $template_circular_users[0]->email;
        $author_name = $template_circular_users[0]->name;

        // ■承認者（二番目以降） 登録値利用　状態：4(承認(捺印なし))
        // 承認操作履歴 TODO
        // 完了メール通知 TODO
        foreach($template_circular_users as $index => $template_circular_user){
            if($template_circular_user->parent_send_order == 0 && $template_circular_user->child_send_order == 0){
                // 申請者
                // ユーザー編集　状態：3(承認(捺印あり)/4(承認(捺印なし)))
                $circular_user = [
                    'circular_id' => $circular_id,
                    'edition_flg' => $template_circular_user->edition_flg,
                    'env_flg' => $template_circular_user->env_flg,
                    'server_flg' => $template_circular_user->server_flg,
                    'parent_send_order' => $template_circular_user->parent_send_order,
                    'child_send_order' => $template_circular_user->child_send_order,
                    'return_flg' => $template_circular_user->return_flg,
                    'circular_status' => $stamp_ope?CircularUserUtils::APPROVED_WITH_STAMP_STATUS:CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                    'email' => $template_circular_user->email,
                    'name' => $template_circular_user->name,
                    'title' => $form_template->title,
                    'received_date' => $now,
                    'sent_date' => $now,
                    'create_at' => $now,
                    'create_user' => $user->email,
                    'update_at' => $now,
                    'update_user' => $user->email,
                    'del_flg' => 0,
                    'mst_user_id' => $template_circular_user->mst_user_id,
                    'mst_company_id' => $template_circular_user->mst_company_id,
                    'mst_company_name' => $template_circular_user->mst_company_name,
                ];
                $circular_users[] = $circular_user;

                // 申請者：文書申請履歴
                $circular_operation_history = [
                    'circular_id' => $circular_id,
                    'operation_email' => $template_circular_user->email,
                    'operation_name' => $template_circular_user->name,
                    'acceptor_email' => $template_circular_users[1]->email,
                    'acceptor_name' => $template_circular_users[1]->name,
                    'circular_status' => CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS,
                    'create_at' => $now,
                ];
                $circular_operation_histories[] = $circular_operation_history;
            }elseif($template_circular_user->parent_send_order == $last_appr_parent_o
                && $template_circular_user->child_send_order == $last_appr_child_o){
                // 最終承認者
                $circular_user = [
                    'circular_id' => $circular_id,
                    'edition_flg' => $template_circular_user->edition_flg,
                    'env_flg' => $template_circular_user->env_flg,
                    'server_flg' => $template_circular_user->server_flg,
                    'parent_send_order' => $template_circular_user->parent_send_order,
                    'child_send_order' => $template_circular_user->child_send_order,
                    'return_flg' => $template_circular_user->return_flg,
                    'circular_status' => CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                    'email' => $template_circular_user->email,
                    'name' => $template_circular_user->name,
                    'title' => $form_template->title,
                    'received_date' => $now,
                    'sent_date' => null,
                    'create_at' => $now,
                    'create_user' => $user->email,
                    'update_at' => $now,
                    'update_user' => $user->email,
                    'del_flg' => 0,
                    'mst_user_id' => $template_circular_user->mst_user_id,
                    'mst_company_id' => $template_circular_user->mst_company_id,
                    'mst_company_name' => $template_circular_user->mst_company_name,
                ];
                $circular_users[] = $circular_user;

                // 承認者：文書承認履歴
                $circular_operation_history = [
                    'circular_id' => $circular_id,
                    'operation_email' => $template_circular_user->email,
                    'operation_name' => $template_circular_user->name,
                    'acceptor_email' => '',
                    'acceptor_name' => '',
                    'circular_status' => CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS,
                    'create_at' => $now,
                ];
                $circular_operation_histories[] = $circular_operation_history;
            }else{
                // ユーザー編集
                $circular_user = [
                    'circular_id' => $circular_id,
                    'edition_flg' => $template_circular_user->edition_flg,
                    'env_flg' => $template_circular_user->env_flg,
                    'server_flg' => $template_circular_user->server_flg,
                    'parent_send_order' => $template_circular_user->parent_send_order,
                    'child_send_order' => $template_circular_user->child_send_order,
                    'return_flg' => $template_circular_user->return_flg,
                    'circular_status' => CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                    'email' => $template_circular_user->email,
                    'name' => $template_circular_user->name,
                    'title' => $form_template->title,
                    'received_date' => $now,
                    'sent_date' => null,
                    'create_at' => $now,
                    'create_user' => $user->email,
                    'update_at' => $now,
                    'update_user' => $user->email,
                    'del_flg' => 0,
                    'mst_user_id' => $template_circular_user->mst_user_id,
                    'mst_company_id' => $template_circular_user->mst_company_id,
                    'mst_company_name' => $template_circular_user->mst_company_name,
                ];
                $circular_users[] = $circular_user;

                // 承認者：文書承認履歴
                $circular_operation_history = [
                    'circular_id' => $circular_id,
                    'operation_email' => $template_circular_user->email,
                    'operation_name' => $template_circular_user->name,
                    'acceptor_email' => $template_circular_users[$index + 1]->email,
                    'acceptor_name' => $template_circular_users[$index + 1]->name,
                    'circular_status' => CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS,
                    'create_at' => $now,
                ];
                $circular_operation_histories[] = $circular_operation_history;
            }
        }

        // データ登録
        DB::table('circular_user')->insert($circular_users);

        // 操作履歴
        DB::table('circular_operation_history')->insert($circular_operation_histories);

        // 回覧文書
        DB::table('circular_document')
            ->where('circular_id', $circular_id)
            ->update(['origin_document_id' => 0]);

        // 閲覧ユーザー
        $template_viewing_users = DB::table('frm_template_viewing_user')
            ->where("frm_template_id", $form_template->id)
            ->get()
            ->toArray();

        foreach($template_viewing_users as $template_viewing_user){
            $viewing_user = [
                'circular_id' => $circular_id,
                'parent_send_order' => $template_viewing_user->parent_send_order,
                'mst_company_id' => $template_viewing_user->mst_company_id,
                'mst_user_id' => $template_viewing_user->mst_user_id,
                'memo' => '',
                'del_flg' => 0,
                'create_user' => $user->email,
                'create_at' => $now,
                'update_user' => $user->email,
            ];
            $viewing_users[] = $viewing_user;
        }

        DB::table('viewing_user')->insert($viewing_users);

        // mails 全回覧者＋閲覧ユーザーへ　完了通知＋アクセス　利用者属性より制御
        $circular_users = DB::table('circular_user')
            ->where("circular_id", $circular_id)
            ->get();

        foreach($circular_users as $circular_user){

            //回覧完了メール（承認者時）
            $mailType = 'completion';
            if($circular_user->parent_send_order == 0 && $circular_user->child_send_order == 0){
                //回覧完了メール（申請者時）
                $mailType = 'completion_sender';
            }
            // 利用者設定値より
            if (CircularUserUtils::checkAllowReceivedEmail($circular_user->email,$mailType,$circular_user->mst_company_id,$server_env,$edition_flg,$server_flg)) {
                // complete mail
                $data = [];
                $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ENDED_NOTIFY']['CODE'];
                $data['body'] = 'mail.circular_has_ended_template.body';
                $data['filenames'] = [$file_name];
                $data['filenamestext'] = $file_name;
                if (!trim($form_template->title)) {
                    $mailTitle =  $file_name;
                } else {
                    $mailTitle = $form_template->title;
                }
                $data['title'] = trans('mail.circular_has_ended_template.subject', ['title' => $mailTitle]);
                $data['receiver_name'] = $circular_user->name;
                $data['creator_name'] = $author_name;
                $data['mail_name'] = $mailTitle;
                $data['author_email'] = $author_email;
                $data['last_updated_email'] = $author_email;
                $data['last_updated_text'] = $form_template->message;
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_id);
                $data['hide_circular_approval_url'] = false;
                $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                $data['send_to'] = $circular_user->email;
                $data['send_to_company'] = $circular_user->mst_company_id;
                $data['parent_send_order'] = $circular_user->parent_send_order;
                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($circular_user);

                $mailDatas[] = $data;

                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){

                    if($circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID
                        && $company->id == $circular_user->mst_company_id
                        && $edition_flg == $circular_user->edition_flg
                        && $server_env == $circular_user->env_flg
                        && $server_flg == $circular_user->server_flg){
                        // 社内回覧の場合
                        $notice_mail_date['title'] = $mailTitle;
                        $notice_mail_date['access_code'] = $circular->access_code;
                        $notice_mail_date['send_to'] = $circular_user->email;
                        $notice_mail_date['send_to_company'] = $circular_user->mst_company_id;
                        $noticeMailDatas[] = $notice_mail_date;

                    }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                        && ($company->id != $circular_user->mst_company_id
                            || $edition_flg != $circular_user->edition_flg
                            || $server_env != $circular_user->env_flg
                            || $server_flg != $circular_user->server_flg)) {
                        // 窓口が社外の場合
                        $notice_mail_date['title'] = $mailTitle;
                        $notice_mail_date['access_code'] = $circular->outside_access_code;
                        $notice_mail_date['send_to'] = $circular_user->email;
                        $notice_mail_date['send_to_company'] = $circular_user->mst_company_id;
                        $noticeMailDatas[] = $notice_mail_date;
                    }
                }
            }
        }

        $viewing_users = DB::table('viewing_user')
            ->join('mst_user', 'mst_user.id', 'viewing_user.mst_user_id')
            ->select('viewing_user.*','mst_user.email','mst_user.family_name','mst_user.given_name')
            ->where("circular_id", $circular_id)
            ->get();

        foreach($viewing_users as $viewing_user){
            if (CircularUserUtils::checkAllowReceivedEmail($viewing_user->email,'completion',$viewing_user->mst_company_id,$server_env,$edition_flg,$server_flg)) {
                $data = [];
                $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ENDED_NOTIFY']['CODE'];
                $data['body'] = 'mail.circular_has_ended_template.body';
                $data['filenames'] = [$file_name];
                $data['filenamestext'] = $file_name;
                if (!trim($form_template->title)) {
                    $mailTitle = $file_name;
                } else {
                    $mailTitle = $form_template->title;
                }
                $data['title'] = trans('mail.circular_has_ended_template.subject', ['title' => $mailTitle]);
                $data['receiver_name'] = $viewing_user->family_name . ' ' . $viewing_user->given_name;
                $data['creator_name'] = $this->get_username($user);
                $data['mail_name'] = $mailTitle;
                $data['author_email'] = $user->email;
                $data['last_updated_email'] = $user->email;
                $data['last_updated_text'] = $form_template->message;
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($viewing_user->email, $edition_flg,$server_env,$server_flg,$circular_id);
                // hide_circular_approval_url false:表示 true:非表示
                // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                $data['hide_circular_approval_url'] = false;
                $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                $data['send_to'] = $viewing_user->email;
                $data['send_to_company'] = $viewing_user->mst_company_id;
                $data['parent_send_order'] = $viewing_user->parent_send_order;
                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv($server_env,$server_flg,$edition_flg, $company);
                $mailDatas[] = $data;

                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                    // 次の回覧者が社内回覧の場合
                    if($circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID
                        && $company->id == $viewing_user->mst_company_id){
                        $notice_mail_date['title'] = $mailTitle;
                        $notice_mail_date['access_code'] = $circular->access_code;
                        $notice_mail_date['send_to'] = $viewing_user->email;
                        $notice_mail_date['send_to_company'] = $viewing_user->mst_company_id;
                        $noticeMailDatas[] = $notice_mail_date;

                    }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                        && ($company->id != $viewing_user->mst_company_id)) {
                        // 窓口が社外の場合
                        $notice_mail_date['title'] = $mailTitle;
                        $notice_mail_date['access_code'] = $circular->outside_access_code;
                        $notice_mail_date['send_to'] = $viewing_user->email;
                        $notice_mail_date['send_to_company'] = $viewing_user->mst_company_id;
                        $noticeMailDatas[] = $notice_mail_date;

                    }
                }
            }
        }

        if (count($mailDatas)){
            try{
                $hantei=1;
                $emails=array();
                $times=0;
                foreach ($mailDatas as $data){
                    $email = $data['send_to'];
                    unset($data['send_to']);
                    $send_to_company = $data['send_to_company'];
                    unset($data['send_to_company']);
                    $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                    unset($data['filenames']);

                    $hantei=1;

                    if($times>0){
                        if(in_array("$email",$emails)){
                            $hantei=0;
                        }
                    }

                    if($hantei==1){

                        $emails[$times]=$email;
                        $times++;
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $email,
                            // メールテンプレート
                            $data['code'],
                            // パラメータ
                            $param,
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.user') . $data['title'],
                            // メールボディ
                            trans($data['body'], $data)
                        );

                    }
                }

                // PAC_5-445 アクセスコードが設定されている場合、アクセスコード通知メール（MAPP0012）を次の宛先に送信する。
                $hantei=1;
                $emails_code=array();
                $times=0;
                if(count($noticeMailDatas)){
                    foreach ($noticeMailDatas as $data){
                        $email = $data['send_to'];
                        unset($data['send_to']);
                        $send_to_company = $data['send_to_company'];
                        unset($data['send_to_company']);

                        $hantei=1;

                        if($times>0){
                            if(in_array("$email",$emails_code)){
                                $hantei=0;
                            }
                        }

                        if($hantei==1){

                            $emails_code[$times]=$email;
                            $times++;
                            //利用者:アクセスコードのお知らせ
                            MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                                $email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($data,JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $data['title']]),
                                // メールボディ
                                trans('mail.SendAccessCodeNoticeMail.body', $data)
                            );
                        }
                    }
                }
            }catch(\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
            }
        }

        // CircularUserAPIController.summaryCircularForCompleted
        $this->summaryCircularForCompleted($circular_id);

        return true;
    }

    /**
     * 自動回覧、送信一覧へ
     */
    protected function auto_circular_apply($circular_id,$user,$company,$now,$file_name,$stamp_ope, $form_template) {
        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        // circular
        DB::table('circular')
            ->where("id", $circular_id)
            ->update([
                'circular_status' => CircularUtils::CIRCULATING_STATUS,
                'applied_date' => $now,
            ]);
        $circular = DB::table('circular')
            ->where("id", $circular_id)
            ->first();

        // title テンプレート設定値
        $title = $form_template->title?:'';

        $circular_users = array();
        $viewing_users = array();
        $circular_operation_histories = array();
        $mailDatas = array();
        $noticeMailDatas = array();

        // ■申請者（一番目）
        // 1) 登録あり、return_flgのみ登録値利用
        $template_circular_users = DB::table('frm_template_circular_user')
            ->where("frm_template_id", $form_template->id)
            ->orderBy("parent_send_order")
            ->orderBy("child_send_order")
            ->get()
            ->toArray();

        if(count($template_circular_users) > 1){
            // 次の承認者
            $apprv_parent_o = $template_circular_users[1]->parent_send_order;
            $apprv_child_o = $template_circular_users[1]->child_send_order;
        }else{
            return false;
        }

        // 回覧申請者情報
        $author_email = $template_circular_users[0]->email;
        $author_name = $template_circular_users[0]->name;

        // ■承認者（二番目以降） 登録値利用　状態：1(通知済/未読) 0(未通知)
        foreach($template_circular_users as $template_circular_user){
            if($template_circular_user->parent_send_order == 0
                && $template_circular_user->child_send_order == 0){
                // 申請者
                // ユーザー編集　状態：3(承認(捺印あり))
                $circular_user = [
                    'circular_id' => $circular_id,
                    'edition_flg' => $template_circular_user->edition_flg,
                    'env_flg' => $template_circular_user->env_flg,
                    'server_flg' => $template_circular_user->server_flg,
                    'parent_send_order' => $template_circular_user->parent_send_order,
                    'child_send_order' => $template_circular_user->child_send_order,
                    'return_flg' => $template_circular_user->return_flg,
                    'circular_status' => $stamp_ope?CircularUserUtils::APPROVED_WITH_STAMP_STATUS:CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                    'email' => $template_circular_user->email,
                    'name' => $template_circular_user->name,
                    'title' => $form_template->title,
                    'received_date' => $now,
                    'sent_date' => $now,
                    'create_at' => $now,
                    'create_user' => $user->email,
                    'update_at' => $now,
                    'update_user' => $user->email,
                    'del_flg' => 0,
                    'mst_user_id' => $template_circular_user->mst_user_id,
                    'mst_company_id' => $template_circular_user->mst_company_id,
                    'mst_company_name' => $template_circular_user->mst_company_name,
                ];
                $circular_users[] = $circular_user;

                // 申請者：文書申請履歴
                $circular_operation_history = [
                    'circular_id' => $circular_id,
                    'operation_email' => $template_circular_user->email,
                    'operation_name' => $template_circular_user->name,
                    'acceptor_email' => $template_circular_users[1]->email,
                    'acceptor_name' => $template_circular_users[1]->name,
                    'circular_status' => CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS,
                    'create_at' => $now,
                ];
                $circular_operation_histories[] = $circular_operation_history;
            }elseif($template_circular_user->parent_send_order == $apprv_parent_o
                && $template_circular_user->child_send_order == $apprv_child_o){
                // 二番目承認者
                $circular_user = [
                    'circular_id' => $circular_id,
                    'edition_flg' => $template_circular_user->edition_flg,
                    'env_flg' => $template_circular_user->env_flg,
                    'server_flg' => $template_circular_user->server_flg,
                    'parent_send_order' => $template_circular_user->parent_send_order,
                    'child_send_order' => $template_circular_user->child_send_order,
                    'return_flg' => $template_circular_user->return_flg,
                    'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                    'email' => $template_circular_user->email,
                    'name' => $template_circular_user->name,
                    'title' => $title,
                    'received_date' => $now,
                    'sent_date' => null,
                    'create_at' => $now,
                    'create_user' => $user->email,
                    'update_at' => $now,
                    'update_user' => $user->email,
                    'del_flg' => 0,
                    'mst_user_id' => $template_circular_user->mst_user_id,
                    'mst_company_id' => $template_circular_user->mst_company_id,
                    'mst_company_name' => $template_circular_user->mst_company_name,
                ];
                $circular_users[] = $circular_user;

            }else{
                // ユーザー編集
                $circular_user = [
                    'circular_id' => $circular_id,
                    'edition_flg' => $template_circular_user->edition_flg,
                    'env_flg' => $template_circular_user->env_flg,
                    'server_flg' => $template_circular_user->server_flg,
                    'parent_send_order' => $template_circular_user->parent_send_order,
                    'child_send_order' => $template_circular_user->child_send_order,
                    'return_flg' => $template_circular_user->return_flg,
                    'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                    'email' => $template_circular_user->email,
                    'name' => $template_circular_user->name,
                    'title' => $title,
                    'received_date' => null,
                    'sent_date' => null,
                    'create_at' => $now,
                    'create_user' => $user->email,
                    'update_at' => $now,
                    'update_user' => $user->email,
                    'del_flg' => 0,
                    'mst_user_id' => $template_circular_user->mst_user_id,
                    'mst_company_id' => $template_circular_user->mst_company_id,
                    'mst_company_name' => $template_circular_user->mst_company_name,
                ];
                $circular_users[] = $circular_user;
            }
        }

        // データ登録
        DB::table('circular_user')->insert($circular_users);

        // 操作履歴
        DB::table('circular_operation_history')->insert($circular_operation_histories);

        // 閲覧ユーザー
        $template_viewing_users = DB::table('frm_template_viewing_user')
            ->where("frm_template_id", $form_template->id)
            ->get()
            ->toArray();

        foreach($template_viewing_users as $template_viewing_user){
            $viewing_user = [
                'circular_id' => $circular_id,
                'parent_send_order' => $template_viewing_user->parent_send_order,
                'mst_company_id' => $template_viewing_user->mst_company_id,
                'mst_user_id' => $template_viewing_user->mst_user_id,
                'memo' => '',
                'del_flg' => 0,
                'create_user' => $user->email,
                'create_at' => $now,
                'update_user' => $user->email,
            ];
            $viewing_users[] = $viewing_user;
        }

        DB::table('viewing_user')->insert($viewing_users);

        // mails 次の承認者へ　承認依頼＋アクセス　利用者属性より制御
        $circular_users = DB::table('circular_user')
            ->where("circular_id", $circular_id)
            ->where("parent_send_order", $apprv_parent_o)
            ->where("child_send_order", $apprv_child_o)
            ->get();

        foreach($circular_users as $circular_user){
            if(CircularUserUtils::checkAllowReceivedEmail($circular_user->email, 'approval',$circular_user->mst_company_id,$circular_user->env_flg,$circular_user->edition_flg,$circular_user->server_flg)) {
                $data = [];
                // hide_thumbnail_flg 0:表示 1:非表示
                if (!$circular->hide_thumbnail_flg) {
                    // thumbnail表示
                    $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                    file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                    $data['image_path'] = $previewPath;
                }else{
                    // thumbnail非表示
                    $data['image_path'] = '';
                }
                if (!trim($form_template->title)) {
                    $title = $file_name;
                }else{
                    $title = trim($form_template->title);
                }

                $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'];
                $data['body'] = 'mail.circular_user_template.body';
                $data['circular_id'] = $circular->id;
                $data['filenames'] = [$file_name];
                $data['filenamestext'] = $file_name;
                $data['title'] = trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $author_name]);
                $data['receiver_name'] = $circular_user->name;
                $data['creator_name'] = $author_name;
                $data['mail_name'] = $title;
                $data['text'] = $form_template->message;
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular->id);
                // hide_circular_approval_url false:表示 true:非表示
                // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                $data['hide_circular_approval_url'] = false;
                if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                    $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                }else{
                    $data['circular_approval_url_text'] = '';
                }

                // check to use SAMl Login URL or not
                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($circular_user);
                $data['send_to'] = $circular_user->email;
                $data['send_to_company'] = $circular_user->mst_company_id;

                $mailDatas[] = $data;

                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                    // 窓口が社内回覧の場合
                    if($company->id == $circular_user->mst_company_id
                        && $edition_flg == $circular_user->edition_flg
                        && $server_env == $circular_user->env_flg
                        && $server_flg == $circular_user->server_flg
                        && $circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID){
                        $notice_mail_date['title'] = $title;
                        $notice_mail_date['access_code'] = $circular->access_code;
                        $notice_mail_date['send_to'] = $circular_user->email;
                        $notice_mail_date['send_to_company'] = $circular_user->mst_company_id;
                        $notice_mail_date['parent_send_order'] = $circular_user->parent_send_order;
                        $noticeMailDatas[] = $notice_mail_date;
                    }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                        && ($company->id != $circular_user->mst_company_id
                            || $edition_flg != $circular_user->edition_flg
                            || $server_env != $circular_user->env_flg
                            || $server_flg != $circular_user->server_flg)) {
                        // 窓口が社外の場合
                        $notice_mail_date['title'] = $title;
                        $notice_mail_date['access_code'] = $circular->outside_access_code;
                        $notice_mail_date['send_to'] = $circular_user->email;
                        $notice_mail_date['send_to_company'] = $circular_user->mst_company_id;
                        $notice_mail_date['parent_send_order'] = $circular_user->parent_send_order;
                        $noticeMailDatas[] = $notice_mail_date;
                    }
                }
            }
        }

        if (count($mailDatas)){
            try{
                $hantei=1;
                $emails=array();
                $times=0;
                foreach ($mailDatas as $data){
                    $email = $data['send_to'];
                    unset($data['send_to']);
                    $send_to_company = $data['send_to_company'];
                    unset($data['send_to_company']);
                    $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                    unset($data['filenames']);

                    $hantei=1;

                    if($times>0){
                        if(in_array("$email",$emails)){
                            $hantei=0;
                        }
                    }

                    if($hantei==1){

                        $emails[$times]=$email;
                        $times++;
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $email,
                            // メールテンプレート
                            $data['code'],
                            // パラメータ
                            $param,
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.user') . $data['title'],
                            // メールボディ
                            trans($data['body'], $data)
                        );

                    }
                }

                // PAC_5-445 アクセスコードが設定されている場合、アクセスコード通知メール（MAPP0012）を次の宛先に送信する。
                $hantei=1;
                $emails_code=array();
                $times=0;
                if(count($noticeMailDatas)){
                    foreach ($noticeMailDatas as $data){
                        $email = $data['send_to'];
                        unset($data['send_to']);
                        $send_to_company = $data['send_to_company'];
                        unset($data['send_to_company']);

                        $hantei=1;

                        if($times>0){
                            if(in_array("$email",$emails_code)){
                                $hantei=0;
                            }
                        }

                        if($hantei==1){

                            $emails_code[$times]=$email;
                            $times++;
                            //利用者:アクセスコードのお知らせ
                            MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                                $email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($data,JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $title]),
                                // メールボディ
                                trans('mail.SendAccessCodeNoticeMail.body', $data)
                            );
                        }
                    }
                }
            }catch(\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
            }
        }
        return true;
    }

    /**
     *
     */
    private function get_username($user) {
        return $user->family_name.' '. $user->given_name;
    }


    /**
     *
     */
    protected function update_data($circular_id, $data) {
        $this->data_maker->update_circular_id($data->id, $circular_id);
    }

    /**
     * Office文書からPDFへの変換を試みる
     * 失敗した場合は例外を投げる
     */
    private static function tryConvertOfficeToPdf(string $filePath, string $outpath): void {
        OfficeConvertApiUtils::convertInstantly($filePath, $outpath);
    }


    /**
     * delte
     */
    public function delete_circular($circular_id) {

        [$server_env, $edition_flg, $server_flg] = $this->_envs();

        // $user = $this->user;
        // $company = $this->company;

        // $parent_send_order = 0;

        $res = DB::table('circular')
            ->where("id", $circular_id)
            ->where("env_flg", $server_env)
            ->where("edition_flg", $edition_flg)
            ->where("server_flg", $server_flg)
            ->where('circular_status', CircularUtils::SAVING_STATUS)
            ->delete();

        if ($res > 0) {
            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where("env_flg", $server_env)
                ->where("edition_flg", $edition_flg)
                ->where("server_flg", $server_flg)
                // ->where('circular_status', CircularUtils::SAVING_STATUS)
                ->delete();

            DB::table('circular_operation_history')
                ->where("circular_document_id", "in", function($query) use ($circular_id) {
                    $query->from("circular_document")->where("circular_id", $circular_id)->select("id");
                })
                // ->where('circular_status', CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS)
                ->delete();

            DB::table('document_data')
                ->where("circular_document_id", "in", function($query) use ($circular_id) {
                    $query->from("circular_document")->where("circular_id", $circular_id)->select("id");
                })
                ->delete();

            DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->where('origin_env_flg', $server_env)
                ->where('origin_edition_flg', $edition_flg)
                ->where('origin_server_flg', $server_flg)
                ->delete();

        }


    }

    /**
     *
     */
    private function _envs() {
        return [$this->server_env, $this->edition_flg, $this->server_flg];
    }

    private function get_1st_page_image($pdf_path) {

        $ph = pathinfo($pdf_path);
        $cairo = new PdfToCairo($pdf_path);
        $cairo->startFromPage(1)->stopAtPage(1);
        $cairo->setRequireOutputDir(true);
        // $cairo->setSubDirRequired(true);
        $cairo->setFlag(Constants::_SINGLE_FILE);
        // $cairo->setOutputSubDir(pathinfo($pdf_path)['filename']);
        $cairo->setOutputFilenamePrefix($ph['filename']);
        $cairo->scalePagesTo(1200);
        $resultGenerateMain = $cairo->generatePNG();

        $image = null;
        $imgFilePath = $ph["dirname"]."/".$ph['filename'].".png";
        if (!$resultGenerateMain) {
            $image = base64_encode(file_get_contents($imgFilePath));
        }
        File::delete($imgFilePath);
        return $image;

    }

    private function summaryCircularForCompleted($completedCircularId){

        $system_edition_flg = config('app.edition_flg');
        $system_env_flg = config('app.server_env');
        $system_server_flg = config('app.server_flg');

        try{
            Log::debug("Start summaryCircularForCompleted for circular $completedCircularId!");
            $circularUsers = DB::table('circular_user')->where('circular_id', $completedCircularId)->select('email', 'title', 'parent_send_order', 'child_send_order', 'mst_company_id', 'mst_user_id')->get();
            $senderUser = DB::table('circular_user')->where('circular_id', $completedCircularId)
                ->where('parent_send_order', 0)->where('child_send_order',0)
                ->select('name', 'email')->first();

            $strSqls = '';
            $countSql = 0;
            foreach ($circularUsers as $circularUser){
                Log::debug("Query receiver for email $circularUser->email in circular $completedCircularId!");
                $receivers = DB::table('circular_user as E')
                    ->select(DB::raw('E.circular_id as id, GROUP_CONCAT(CONCAT(E.name, \' &lt;\',E.email, \'&gt;\') ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \'<br />\') as receiver_name_emails
                                , GROUP_CONCAT(E.name ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \',\') as receiver_names
                                , GROUP_CONCAT(E.email ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \',\') as receiver_emails'))
                    // 宛先に自分自身を設定していた場合の対策としてNOT EXISTS
                    ->where('E.child_send_order', '!=', 0)
                    ->where('E.circular_id', $completedCircularId)
                    ->whereRaw("EXISTS (SELECT M.circular_id from circular_user as M where E.circular_id = M.circular_id
                        AND M.email = '$circularUser->email'
                        AND M.edition_flg = '$system_edition_flg'
                        AND M.env_flg = '$system_env_flg'
                        AND M.server_flg = '$system_server_flg'
                        AND ((E.parent_send_order != 0 AND E.child_send_order = 1) OR (E.parent_send_order = M.parent_send_order)))")
                    //->whereRaw('((E.parent_send_order != 0 AND E.child_send_order = 1) OR (E.parent_send_order = M.parent_send_order))')
                    ->groupBy(['E.circular_id'])->get();
                Log::debug("Finished query receiver for email $circularUser->email in circular $completedCircularId!");

                if ($senderUser){
                    $strSqls.="UPDATE circular_user SET sender_name = '$senderUser->name', sender_email = '$senderUser->email' where email = '$circularUser->email' and circular_id = $completedCircularId;\n";
                    $countSql++;
                }
                foreach ($receivers as $receiver){
                    $strSqls.="UPDATE circular_user SET receiver_name = '$receiver->receiver_names', receiver_email = '$receiver->receiver_emails', receiver_name_email = '$receiver->receiver_name_emails' where email = '$circularUser->email' and circular_id = $completedCircularId;\n";
                    $countSql++;
                }
                if (trim($circularUser->title)){
                    Log::debug("No query for title $circularUser->email in circular $completedCircularId because this email has title already!");
                }else{
                    Log::debug("Query for title $circularUser->email in circular $completedCircularId!");
                    $mstUserId = $circularUser->mst_user_id?:0;
                    $mstCompanyId = $circularUser->mst_company_id?:0;
                    $titles = DB::table('circular as C')
                        ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
                        ->join('circular_document as D', function($join) use ($circularUser, $system_env_flg, $system_server_flg, $system_edition_flg, $mstCompanyId){
                            $join->on('C.id', '=', 'D.circular_id');
                            $join->on(function($condition) use ($circularUser, $system_env_flg, $system_server_flg, $system_edition_flg, $mstCompanyId){
                                $condition->on('confidential_flg', DB::raw('0'));
                                $condition->orOn(function($condition1) use ($circularUser, $system_env_flg, $system_server_flg, $system_edition_flg, $mstCompanyId){
                                    $condition1->on('confidential_flg', DB::raw('1'));
                                    $condition1->on('origin_edition_flg', DB::raw($system_edition_flg));
                                    $condition1->on('origin_env_flg', DB::raw($system_env_flg));
                                    $condition1->on('origin_server_flg', DB::raw($system_server_flg));
                                    $condition1->on('create_company_id', DB::raw($mstCompanyId));
                                });
                            });
                            $join->on(function($condition) use ($circularUser){
                                $condition->on('origin_document_id', DB::raw('0'));
                                $condition->orOn(function($condition1) use ($circularUser){
                                    $condition1->on('D.parent_send_order', 'U.parent_send_order');
                                });
                            });
                        })
                        ->select(DB::raw('GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
                        // 宛先に自分自身を設定していた場合の対策としてNOT EXISTS
                        ->whereRaw("((U.email = '$circularUser->email' AND NOT EXISTS (SELECT * FROM circular_user WHERE circular_id = U.circular_id AND email=U.email AND parent_send_order = 0
                    AND edition_flg = ".$system_edition_flg." AND env_flg = ".$system_env_flg." AND server_flg = ".$system_server_flg."
                    AND child_send_order = 0)) OR (C.mst_user_id = $mstUserId AND U.parent_send_order = 0 AND U.child_send_order = 0))")
                        ->where('U.edition_flg', $system_edition_flg)
                        ->where('U.env_flg', $system_env_flg)
                        ->where('U.server_flg', $system_server_flg)
                        ->where('C.id', $completedCircularId)
                        ->where('U.parent_send_order', $circularUser->parent_send_order)
                        ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS])
                        ->groupBy(['C.id', 'U.parent_send_order'])->get();
                    Log::debug("Finished query title for email $circularUser->email in circular $completedCircularId!");
                    foreach ($titles as $title){
                        $strSqls.="UPDATE circular_user SET receiver_title = '$title->file_names' where email = '$circularUser->email' and parent_send_order = $circularUser->parent_send_order and circular_id = $completedCircularId;\n";
                        $countSql++;
                    }
                }
                if ($countSql > 100){
                    Log::debug('Flush to database in loop!');
                    DB::unprepared($strSqls);
                    $strSqls = '';
                    $countSql = 0;
                }
            }
            if ($countSql){
                Log::debug('Flush to database in loop!');
                DB::unprepared($strSqls);
            }
        }catch (Exception $ex){
            Log::debug('Error in summaryCircularForCompleted for receiver/sender/title!');
            Log::error($ex->getMessage().$ex->getTraceAsString());
        }
    }
}
