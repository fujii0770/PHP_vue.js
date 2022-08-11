<?php namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class CircularDocumentUtils
{
    /**
     * PDFデータと操作履歴可能取得
     * @param $circular_id integer 回覧ID
     * @param $id integer 申請書ID
     * @param $mst_company_id integer 会社ID
     * @param $edition_flg integer エディションフラグ
     * @param $env_flg integer 環境フラグ
     * @param $server_flg integer サーバフラグ
     * @param $finishedDate string 完了日時
     * @param $check_add_text_history integer 履歴追加フラフ
     * @return array 履歴情報
     */
    public static function getHistory($circular_id, $id, $mst_company_id, $edition_flg, $env_flg, $server_flg, $finishedDate, $check_add_text_history)
    {
        try {
            $circular_document = DB::table("circular_document$finishedDate as D")
                ->select('D.*', 'DD.file_data')
                ->join("document_data$finishedDate as DD", 'DD.circular_document_id', '=', 'D.id')
                ->where('D.id', $id)->first();

            if (!$circular_document || !$circular_document->id) {
                return ['status' => false, 'message' => __('message.warning.doc＿not_exist')];
            }

            $circular = DB::table("circular$finishedDate")->where('id', $circular_document->circular_id)->first();

            $circular_users = DB::table("circular_user$finishedDate")
                ->select('name', 'email')
                ->where('circular_id', $circular_document->circular_id)
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();

            // 社内すべてparent_send_order取得
            $my_company_users = DB::table("circular_user$finishedDate")
                ->where('circular_id', $circular->id)
                ->where('mst_company_id', $mst_company_id)
                ->where('edition_flg', $edition_flg)
                ->where('env_flg', $env_flg)
                ->where('server_flg', $server_flg)
                ->pluck('parent_send_order', 'email')
                ->toArray();

            $parent_send_orders = array_values($my_company_users);
            $emails = array_keys($my_company_users);

            $circular_document->append_pdf = "";
            $circular_document->file_data = AppUtils::decrypt($circular_document->file_data);

            // 履歴取得
            if ($check_add_text_history) {
                $circular_document_id = $circular_document->id;
                //Append pdf last page
                $histories = DB::table('circular_operation_history as H')
                    ->select('H.*', 'C.text', 'S.stamp_image')
                    ->leftJoin('document_comment_info as C', function ($query) use ($circular_document_id, $parent_send_orders) {
                        $query->on('H.id', '=', 'C.circular_operation_id')
                            ->where('C.circular_document_id', $circular_document_id)
                            ->where(function ($query) use ($parent_send_orders) {
                                $query->where('C.private_flg', CircularOperationHistoryUtils::DOCUMENT_COMMENT_PUBLIC)
                                    ->orWhere(function ($query) use ($parent_send_orders) {
                                        $query->where('C.private_flg', CircularOperationHistoryUtils::DOCUMENT_COMMENT_PRIVATE)
                                            ->whereIn('C.parent_send_order', $parent_send_orders);
                                    });
                            });
                    })
                    ->leftJoin('stamp_info as S', function ($query) use ($circular_id) {
                        $query->on('H.id', '=', 'S.circular_operation_id');
                    })
                    ->where('circular_id', $circular_id)
                    ->Where(function ($query) use ($circular_document_id, $parent_send_orders) {
                        $query->whereNull('H.circular_document_id')
                            ->orWhere(function ($query) use ($circular_document_id) {
                                $query->where('H.circular_document_id', $circular_document_id)
                                    ->whereIn('H.circular_status', [CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS, CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS]);
                            })
                            ->orWhere(function ($query) use ($circular_document_id, $parent_send_orders) {
                                $query->where('H.circular_document_id', $circular_document_id)
                                    ->where('H.circular_status', CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS);
                            });
                    })
                    ->orderBy('H.create_at', 'asc')
                    ->orderBy('H.circular_status', 'asc')
                    ->orderBy('H.id', 'asc')
                    ->get()
                    ->toArray();

                // PAC_5-1039 合議の場合は、申請承認差戻しの二つのノードが一緒になります。
                $prev_operation_email = ""; // 前の方 operation_email
                $prev_circular_status = 0; // 前の方 circular_status
                $save_key = -1; // マージの下付き
                foreach($histories as $key => $history) {
                    $histories[$key]->acceptors = [];
                    // 申請 
                    if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 0){
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 1){
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 0){
                        // 承認
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            if(!empty($history->acceptor_email)){
                                $histories[$key]->acceptors[] = $history;
                            }else{
                                $histories[$key]->acceptors = [];
                            }
                            $save_key = $key;
                        }
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 1){
                        // 承認
                        if(!empty($history->acceptor_email)){
                            $histories[$key]->acceptors[] = $history;
                        }else{
                            $histories[$key]->acceptors = [];
                        }
                        $save_key = $key;
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS){
                        // 差戻し
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS){
                        // 引戻し/ 差戻し依頼/ 差戻し依頼承認
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    }
                    $prev_operation_email = $history->operation_email;
                    $prev_circular_status = $history->circular_status;
                }

                $histories_text_info = DB::table('circular_operation_history')
                    ->select('circular_operation_history.*', 'text_info.text')
                    ->leftJoin('text_info', function ($query) use ($circular_id) {
                        $query->on('circular_operation_history.id', '=', 'text_info.circular_operation_id');
                    })
                    ->where('circular_id', $circular_id)
                    ->where('circular_status', CircularOperationHistoryUtils::CIRCULAR_ADD_TEXT_STATUS)
                    ->Where('circular_operation_history.circular_document_id', $circular_document->id)
                    ->orderBy('circular_operation_history.create_at', 'asc')
                    ->orderBy('circular_operation_history.circular_status', 'asc')
                    ->orderBy('circular_operation_history.id', 'asc')
                    ->get()
                    ->toArray();


                foreach ($histories as $key => $history) {
                    // 操作者メールは本会社以外の場合
                    if (array_search($history->operation_email, $emails) === false) {
                        // 操作者名前を削除
                        $history->operation_name = "";
                    }
                    // 宛先メールは本会社以外の場合
                    if (array_search($history->acceptor_email, $emails) === false) {
                        // 宛先名前を削除
                        $history->acceptor_name = "";
                    }
                }

                // 文書情報に他会社の情報を削除
                foreach ($circular_users as $key => $circular_user) {
                    // 他会社の場合
                    if (array_search($circular_user->email, $emails) === false) {
                        // 名前を削除
                        $circular_user->name = "";
                    }
                }

                // ダウンロード履歴には部署印が表示されません。 白い背景を追加
                foreach ($histories as $key => $history) {
                    if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS && $history->stamp_image) {
                        $history->stamp_image = StampUtils::imageConvert($history->stamp_image);
                    }
                }

                //PAC_5-1398 添付ファイル情報
                $attachments = DB::table('circular_attachment')
                    ->select('create_at','create_user','file_name','name')
                    ->where('circular_id',$circular_id)
                    ->where(function ($query) use($mst_company_id,$edition_flg,$env_flg,$server_flg){
                        $query->where('confidential_flg',CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_FALSE);
                        $query->orWhere(function ($query1) use($mst_company_id,$edition_flg,$env_flg,$server_flg){
                            $query1->where('confidential_flg',CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_TRUE)
                                ->where('create_company_id',$mst_company_id)
                                ->where('edition_flg',$edition_flg)
                                ->where('env_flg',$env_flg)
                                ->where('server_flg',$server_flg);
                        });
                    })
                    ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                    ->get();

                $data = [];
                $data['circular_document'] = $circular_document;
                $data['histories'] = $histories;
                $data['histories_text_info'] = $histories_text_info;
                $data['circular_users'] = $circular_users;
                $data['circular_status'] = CircularOperationHistoryUtils::getCircularStatus($circular ? $circular->circular_status : '');
                $data['circular_attachments'] = $attachments;
                $pdf = PDF::loadView('pdf_template.stamp_histories', $data);
                $append_pdf_str = base64_encode($pdf->output('document.pdf', "S"));

                $circular_document->append_pdf = $append_pdf_str;
            }
            return ['status' => true, 'message' => __('message.success.doc_histories_get'), 'circular_document' => $circular_document];
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => false, 'message' => __('message.false.doc_histories_get', ['attribute' => $circular_id])];
        }
    }
    public static function charactersReplace($fileName)
    {
        $standardCharacter = array("が","ぎ","ぐ","げ","ご","ざ","じ","ず","ぜ","ぞ","だ","ぢ","づ","で","ど","ば","び","ぶ","べ","ぼ","ぱ","ぴ","ぷ","ぺ","ぽ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ");
        $realCharacter = array("が","ぎ","ぐ","げ","ご","ざ","じ","ず","ぜ","ぞ","だ","ぢ","づ","で","ど","ば","び","ぶ","べ","ぼ","ぱ","ぴ","ぷ","ぺ","ぽ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ");
        $realFileName =  str_replace($realCharacter, $standardCharacter, $fileName);

        return $realFileName;
    }
    public static function getLongTermHistory($long_term_id, $mst_company_id, $edition_flg, $env_flg, $server_flg, $check_add_text_history, $file_name,$arrLongTermData,$finishedDate)
    {
        try {
            $long_term_document=DB::table('long_term_document as ld')
                ->where('ld.id',$long_term_id)
                ->leftJoin('long_term_circular as lc','ld.circular_id','=','lc.id')
                ->select('ld.*','lc.final_updated_date as uat','lc.create_at as cat')
                ->first();
            if (is_null($long_term_document) || !$long_term_document->id) {
                return ['status' => false, 'message' => __('message.warning.doc＿not_exist')];
            }
            $document=DB::table('circular_document')->where('circular_id',$long_term_document->circular_id)->select('update_at','create_at')->first();
            $long_term_document->create_at = optional($document)->create_at??$long_term_document->cat;
            $long_term_document->update_at = optional($document)->update_at??$long_term_document->uat;
            $circular = DB::table("long_term_circular")
                ->where('id', $long_term_document->circular_id)
                ->first();
            if (is_null($circular) || !$circular->id) {
                return ['status' => false, 'message' => __('message.warning.doc＿not_exist')];
            }
            $circular_users = DB::table("long_term_circular_user")
                ->select('name', 'email','id')
                ->where('circular_id', $long_term_document->circular_id)
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
            $arrCEmpty = [];
            foreach($circular_users as $circular_user){
                if (!isset($arrCEmpty[$circular_user->id])){
                    $arrCEmpty[$circular_user->id] = $circular_user;
                }
            }
            $circular_users = array_values($arrCEmpty);
            // 社内すべてparent_send_order取得
            $my_company_users = DB::table("long_term_circular_user")
                ->where('circular_id', $long_term_document->circular_id)
                ->where('mst_company_id', $mst_company_id)
                ->where('edition_flg', $edition_flg)
                ->where('env_flg', $env_flg)
                ->where('server_flg', $server_flg)
                ->pluck('parent_send_order', 'email')
                ->toArray();
            $type=Storage::disk('s3');
            if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                $type= Storage::disk('k5');
            }
            $s3path=config('filesystems.prefix_path') . '/' .config('app.s3_storage_root_folder') . '/' . config('app.pac_app_env') . '/' . config('app.pac_contract_app')
                . '/' . config('app.pac_contract_server') . '/' . $mst_company_id.'/'.$long_term_document->circular_id.'/'.$file_name;

            if ( $type->exists($s3path)){
                $file_content = $type->get($s3path);
                $long_term_document->file_data=$file_content;
            }
            $parent_send_orders = array_values($my_company_users);
            $emails = array_keys($my_company_users);
            $long_term_document->append_pdf = "";

            // 履歴取得
            if ($check_add_text_history) {
                $long_term_document_id = $arrLongTermData['document_id'];
                //Append pdf last page
                $histories = DB::table('long_term_circular_operation_history as H')
                    ->select('H.*', 'C.text', 'S.stamp_image')
                    ->leftJoin('long_term_document_comment_info as C', function ($query) use ($long_term_document_id, $parent_send_orders) {
                        $query->on('H.id', '=', 'C.long_term_operation_id')
                            ->where(function ($query) use ($parent_send_orders) {
                                $query->where('C.private_flg', CircularOperationHistoryUtils::DOCUMENT_COMMENT_PUBLIC)
                                    ->orWhere(function ($query) use ($parent_send_orders) {
                                        $query->where('C.private_flg', CircularOperationHistoryUtils::DOCUMENT_COMMENT_PRIVATE)
                                            ->whereIn('C.parent_send_order', $parent_send_orders);
                                    });
                            })->where("C.circular_document_id",'=',$long_term_document_id);
                    })
                    ->leftJoin('long_term_stamp_info as S', function ($query) use ($long_term_document_id) {
                        $query->on('H.id', '=', 'S.long_term_operation_id');
                        $query->where("S.circular_document_id",'=',$long_term_document_id);
                    })
                    ->whereIn('H.circular_document_id', [$long_term_document_id,0])
                    ->where("H.long_term_document_id",$long_term_id)
                    ->where("H.circular_id",$long_term_document->circular_id)
                    ->whereIn('H.circular_status', [1, 2, 4, 10, 11, 12, 13, 14, 15])
                    ->orderBy('H.create_at', 'asc')
                    ->orderBy('H.circular_status', 'asc')
                    ->orderBy('H.id', 'asc')
                    ->get()
                    ->toArray();
                $arrEmpty = [];
                foreach($histories as $history){
                    if (!isset($arrEmpty[$history->id])){
                        $arrEmpty[$history->id] = $history;
                    }
                }
                foreach($arrEmpty as $key => $history){
                    if(in_array($history->circular_status,[1,2]) && !$history->circular_document_id){
                        unset($arrEmpty[$key]);
                    }
                }
                $histories = array_values($arrEmpty);

                // PAC_5-1039 合議の場合は、申請承認差戻しの二つのノードが一緒になります。
                $prev_operation_email = ""; // 前の方 operation_email
                $prev_circular_status = 0; // 前の方 circular_status
                $save_key = -1; // マージの下付き
                foreach($histories as $key => $history) {
                    $histories[$key]->acceptors = [];
                    // 申請
                    if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 0){
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 1){
                        $histories[$key]->acceptors[] = $history;
                        $save_key = $key;
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 0){
                        // 承認
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            if(!empty($history->acceptor_email)){
                                $histories[$key]->acceptors[] = $history;
                            }else{
                                $histories[$key]->acceptors = [];
                            }
                            $save_key = $key;
                        }
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 1){
                        // 承認
                        if(!empty($history->acceptor_email)){
                            $histories[$key]->acceptors[] = $history;
                        }else{
                            $histories[$key]->acceptors = [];
                        }
                        $save_key = $key;
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS){
                        // 差戻し
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS){
                        // 引戻し/ 差戻し依頼/ 差戻し依頼承認
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        }else{
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    }
                    $prev_operation_email = $history->operation_email;
                    $prev_circular_status = $history->circular_status;
                }
                $histories_text_info = DB::table('long_term_circular_operation_history as LH')
                    ->select('LH.*', 'LI.text')
                    ->leftJoin('long_term_text_info as LI', function ($query) use ($long_term_document_id) {
                        $query->on('LH.id', '=', 'LI.circular_operation_id');
                    })
                    ->where('circular_id', $long_term_document->circular_id)
                    ->where('circular_status', CircularOperationHistoryUtils::CIRCULAR_ADD_TEXT_STATUS)
                    ->Where('LH.circular_document_id', $long_term_document_id)
                    ->where("LH.long_term_document_id",$long_term_id)
                    ->orderBy('LH.create_at', 'asc')
                    ->orderBy('LH.circular_status', 'asc')
                    ->orderBy('LH.id', 'asc')
                    ->get()
                    ->toArray();
                $arrTEmpty = [];
                foreach($histories_text_info as $text){
                    if (!isset($arrTEmpty[$text->id])){
                        $arrTEmpty[$text->id] = $text;
                    }
                }
                $histories_text_info = array_values($arrTEmpty);
                foreach ($histories as $key => $history) {
                    // 操作者メールは本会社以外の場合
                    if (array_search($history->operation_email, $emails) === false) {
                        // 操作者名前を削除
                        $history->operation_name = "";
                    }
                    // 宛先メールは本会社以外の場合
                    if (array_search($history->acceptor_email, $emails) === false) {
                        // 宛先名前を削除
                        $history->acceptor_name = "";
                    }
                }
                // 文書情報に他会社の情報を削除
                foreach ($circular_users as $key => $circular_user) {
                    // 他会社の場合
                    if (array_search($circular_user->email, $emails) === false) {
                        // 名前を削除
                        $circular_user->name = "";
                    }
                }
                // ダウンロード履歴には部署印が表示されません。 白い背景を追加
                foreach ($histories as $key => $history) {
                    if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS && $history->stamp_image) {
                        $history->stamp_image = StampUtils::imageConvert($history->stamp_image);
                    }
                }
                $arrAttachment = json_decode($long_term_document->circular_attachment_json);
                $arrAttachment = $arrAttachment ? collect($arrAttachment)->all() : [];
                $data = [];
                $data['circular_document'] = $long_term_document;
                $data['histories'] = $histories;
                $data['histories_text_info'] = $histories_text_info;
                $data['circular_users'] = $circular_users;
                $data['circular_status'] = CircularOperationHistoryUtils::getCircularStatus($circular ? $circular->circular_status : '');
                $data['circular_attachments'] = $arrAttachment;
                $pdf = PDF::loadView('pdf_template.stamp_histories', $data);
                $append_pdf_str = base64_encode($pdf->output('document.pdf', "S"));

                $long_term_document->append_pdf = $append_pdf_str;
            }
            return ['status' => true, 'message' => __('message.success.doc_histories_get'), 'circular_document' => $long_term_document];
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => false, 'message' => __('message.false.doc_histories_get', ['attribute' => $long_term_document->circular_id])];
        }
    }
    public static  function copyCircularDataToLongTerm($circular_id,$finishedDate,$longTermId)
    {
        try {
            $circular = DB::table("circular$finishedDate")
                ->where('id', $circular_id)
                ->first();
            if(!$circular){
                return;
            }
            $circular_user=DB::table("circular_user$finishedDate")->where('circular_id',$circular->id)->get()->toArray();
            $circular_documents=DB::table("circular_document$finishedDate")->where('circular_id',$circular->id)->get()->toArray();
            $circular_operation_history=DB::table('circular_operation_history')->where('circular_id',$circular->id)->get()->toArray();
            if(empty($circular_documents)){
                return;
            }
            $insert_long_term_users=[];
            $insert_long_term_data=[];
            $insert_long_term_document_comment=[];
            $insert_long_term_circular_operation_history=[];
            $insert_long_term_stamp_infos=[];
            $insert_long_term_text_infos=[];
            DB::beginTransaction();
            DB::table('long_term_circular')->insert([
                'id'=>$circular->id,
                'mst_user_id'=>$circular->mst_user_id,
                'access_code_flg'=>$circular->access_code_flg,
                'access_code'=>$circular->access_code,
                'outside_access_code_flg'=>$circular->outside_access_code_flg,
                'outside_access_code'=>$circular->outside_access_code,
                'hide_thumbnail_flg'=>$circular->hide_thumbnail_flg,
                're_notification_day'=>$circular->re_notification_day,
                'circular_status'=>$circular->circular_status,
                'create_at'=>$circular->create_at,
                'create_user'=>$circular->create_user,
                'update_at'=>$circular->update_at,
                'update_user'=>$circular->update_user,
                'address_change_flg'=>$circular->address_change_flg,
                'first_page_data'=>$circular->first_page_data,
                'env_flg'=>$circular->env_flg,
                'edition_flg'=>$circular->edition_flg,
                'server_flg'=>$circular->server_flg,
                'origin_circular_id'=>$circular->origin_circular_id,
                'current_aws_circular_id'=>$circular->current_aws_circular_id,
                'current_k5_circular_id'=>$circular->current_k5_circular_id,
                'applied_date'=>$circular->applied_date,
                'completed_date'=>$circular->completed_date,
                'completed_copy_flg'=>$circular->completed_copy_flg,
                'has_signature'=>$circular->has_signature,
                'final_updated_date'=>$circular->final_updated_date,
                'special_site_flg'=>$circular->special_site_flg,
            ]);
            foreach ($circular_user as $k=>$v){
                $insert_long_term_users[$k]['id']=$v->id;
                $insert_long_term_users[$k]['circular_id']=$v->circular_id;
                $insert_long_term_users[$k]['parent_send_order']=$v->parent_send_order;
                $insert_long_term_users[$k]['env_flg']=$v->env_flg;
                $insert_long_term_users[$k]['edition_flg']=$v->edition_flg;
                $insert_long_term_users[$k]['server_flg']=$v->server_flg;
                $insert_long_term_users[$k]['mst_company_id']=$v->mst_company_id;
                $insert_long_term_users[$k]['email']=$v->email;
                $insert_long_term_users[$k]['name']=$v->name;
                $insert_long_term_users[$k]['title']=$v->title;
                $insert_long_term_users[$k]['text']=$v->text;
                $insert_long_term_users[$k]['circular_status']=$v->circular_status;
                $insert_long_term_users[$k]['create_at']=$v->create_at;
                $insert_long_term_users[$k]['create_user']=$v->create_user;
                $insert_long_term_users[$k]['update_at']=$v->update_at;
                $insert_long_term_users[$k]['update_user']=$v->update_user;
                $insert_long_term_users[$k]['child_send_order']=$v->child_send_order;
                $insert_long_term_users[$k]['del_flg']=$v->del_flg;
                $insert_long_term_users[$k]['mst_user_id']=$v->mst_user_id;
                $insert_long_term_users[$k]['origin_circular_url']=$v->origin_circular_url;
                $insert_long_term_users[$k]['return_flg']=$v->return_flg;
                $insert_long_term_users[$k]['mst_company_name']=$v->mst_company_name;
                $insert_long_term_users[$k]['received_date']=$v->received_date;
                $insert_long_term_users[$k]['sent_date']=$v->sent_date;
                $insert_long_term_users[$k]['sender_name']=$v->sender_name;
                $insert_long_term_users[$k]['sender_email']=$v->sender_email;
                $insert_long_term_users[$k]['receiver_name']=$v->receiver_name;
                $insert_long_term_users[$k]['receiver_email']=$v->receiver_email;
                $insert_long_term_users[$k]['receiver_name_email']=$v->receiver_name_email;
                $insert_long_term_users[$k]['receiver_title']=$v->receiver_title;
                $insert_long_term_users[$k]['stamp_flg']=$v->stamp_flg;
                $insert_long_term_users[$k]['special_site_receive_flg']=$v->special_site_receive_flg;
                $insert_long_term_users[$k]['plan_id']=$v->plan_id;
                $insert_long_term_users[$k]['return_send_back']=$v->return_send_back;
                $insert_long_term_users[$k]['node_flg']=$v->node_flg;
                unset($circular_user[$k]);
            }
            DB::table('long_term_circular_user')->insert($insert_long_term_users);
            foreach ($circular_operation_history as $k=>$v){
                $insert_long_term_circular_operation_history[$k]['id']=$v->id;
                $insert_long_term_circular_operation_history[$k]['long_term_document_id']=$longTermId;
                $insert_long_term_circular_operation_history[$k]['circular_id']=$v->circular_id;
                $insert_long_term_circular_operation_history[$k]['operation_email']=$v->operation_email;
                $insert_long_term_circular_operation_history[$k]['operation_name']=$v->operation_name;
                $insert_long_term_circular_operation_history[$k]['acceptor_email']=$v->acceptor_email;
                $insert_long_term_circular_operation_history[$k]['acceptor_name']=$v->acceptor_name;
                $insert_long_term_circular_operation_history[$k]['circular_status']=$v->circular_status;
                $insert_long_term_circular_operation_history[$k]['create_at']=$v->create_at;
                $insert_long_term_circular_operation_history[$k]['is_skip']=$v->is_skip;
                unset($circular_operation_history[$k]);
            }
            DB::table('long_term_circular_operation_history')->insert($insert_long_term_circular_operation_history);
            unset($insert_long_term_circular_operation_history);
            foreach ($circular_documents as $circular_document){
                $document_comment=DB::table('document_comment_info')->where('circular_document_id',$circular_document->id)->get()->toArray();
                $stamp_info=DB::table('stamp_info')->where('circular_document_id',$circular_document->id)->get()->toArray();
                $text_info=DB::table('text_info')->where('circular_document_id',$circular_document->id)->get()->toArray();

                foreach ($document_comment as $k=>$v){
                    $insert_long_term_document_comment[$k]['id']=$v->id;
                    $insert_long_term_document_comment[$k]['long_term_document_id']=$longTermId;
                    $insert_long_term_document_comment[$k]['long_term_operation_id']=$v->circular_operation_id;
                    $insert_long_term_document_comment[$k]['parent_send_order']=$v->parent_send_order;
                    $insert_long_term_document_comment[$k]['name']=$v->name;
                    $insert_long_term_document_comment[$k]['email']=$v->email;
                    $insert_long_term_document_comment[$k]['text']=$v->text;
                    $insert_long_term_document_comment[$k]['private_flg']=$v->private_flg;
                    $insert_long_term_document_comment[$k]['create_at']=$v->create_at;
                    unset($document_comment[$k]);
                }
                DB::table('long_term_document_comment_info')->insert($insert_long_term_document_comment);
                foreach ($stamp_info as $k=>$v){
                    $insert_long_term_stamp_infos[$k]['id']=$v->id;
                    $insert_long_term_stamp_infos[$k]['long_term_document_id']=$longTermId;
                    $insert_long_term_stamp_infos[$k]['long_term_operation_id']=$v->circular_operation_id;
                    $insert_long_term_stamp_infos[$k]['mst_assign_stamp_id']=$v->mst_assign_stamp_id;
                    $insert_long_term_stamp_infos[$k]['parent_send_order']=$v->parent_send_order;
                    $insert_long_term_stamp_infos[$k]['stamp_image']=$v->stamp_image;
                    $insert_long_term_stamp_infos[$k]['name']=$v->name;
                    $insert_long_term_stamp_infos[$k]['email']=$v->email;
                    $insert_long_term_stamp_infos[$k]['bizcard_id']=$v->bizcard_id;
                    $insert_long_term_stamp_infos[$k]['env_flg']=$v->env_flg;
                    $insert_long_term_stamp_infos[$k]['server_flg']=$v->server_flg;
                    $insert_long_term_stamp_infos[$k]['edition_flg']=$v->edition_flg;
                    $insert_long_term_stamp_infos[$k]['info_id']=$v->info_id;
                    $insert_long_term_stamp_infos[$k]['file_name']=$v->file_name;
                    $insert_long_term_stamp_infos[$k]['create_at']=$v->create_at;
                    $insert_long_term_stamp_infos[$k]['time_stamp_permission']=$v->time_stamp_permission;
                    $insert_long_term_stamp_infos[$k]['serial']=$v->serial;
                    unset($stamp_info[$k]);
                }
                DB::table('long_term_stamp_info')->insert($insert_long_term_stamp_infos);
                foreach ($text_info as $k=>$v){
                    $insert_long_term_text_infos[$k]['id']=$v->id;
                    $insert_long_term_text_infos[$k]['long_term_document_id']=$longTermId;
                    $insert_long_term_text_infos[$k]['circular_operation_id']=$v->circular_operation_id;
                    $insert_long_term_text_infos[$k]['text']=$v->text;
                    $insert_long_term_text_infos[$k]['name']=$v->name;
                    $insert_long_term_text_infos[$k]['email']=$v->email;
                    $insert_long_term_text_infos[$k]['create_at']=$v->create_at;
                    unset($text_info[$k]);
                }
                DB::table('long_term_text_info')->insert($insert_long_term_text_infos);

            }
            unset($insert_long_term_data);
            unset($insert_long_term_text_infos);
            unset($insert_long_term_stamp_infos);
            unset($insert_long_term_document_comment);
            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            Log::error('err',[
                'track'=>$ex->getTraceAsString(),
                'sql'=>$ex->getPrevious()
            ]);
        }

    }
}