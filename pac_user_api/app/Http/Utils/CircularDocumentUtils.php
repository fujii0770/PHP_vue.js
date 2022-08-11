<?php namespace App\Http\Utils;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class CircularDocumentUtils
{
    public static function charactersReplace($fileName)
    {
        $standardCharacter = array("が", "ぎ", "ぐ", "げ", "ご", "ざ", "じ", "ず", "ぜ", "ぞ", "だ", "ぢ", "づ", "で", "ど", "ば", "び", "ぶ", "べ", "ぼ", "ぱ", "ぴ", "ぷ", "ぺ", "ぽ", "ガ", "ギ", "グ", "ゲ", "ゴ", "ザ", "ジ", "ズ", "ゼ", "ゾ", "ダ", "ヂ", "ヅ", "デ", "ド", "バ", "ビ", "ブ", "ベ", "ボ", "パ", "ピ", "プ", "ペ", "ポ");
        $realCharacter = array("が", "ぎ", "ぐ", "げ", "ご", "ざ", "じ", "ず", "ぜ", "ぞ", "だ", "ぢ", "づ", "で", "ど", "ば", "び", "ぶ", "べ", "ぼ", "ぱ", "ぴ", "ぷ", "ぺ", "ぽ", "ガ", "ギ", "グ", "ゲ", "ゴ", "ザ", "ジ", "ズ", "ゼ", "ゾ", "ダ", "ヂ", "ヅ", "デ", "ド", "バ", "ビ", "ブ", "ベ", "ボ", "パ", "ピ", "プ", "ペ", "ポ");
        $realFileName = str_replace($realCharacter, $standardCharacter, $fileName);

        return $realFileName;
    }

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
                foreach ($histories as $key => $history) {
                    $histories[$key]->acceptors = [];
                    // 申請
                    if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 0) {
                        if($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    } else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 1){
                        $histories[$key]->acceptors[] = $history;
                        $save_key = $key;
                    }else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 0){
                        // 承認
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0){
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
                            if (!empty($history->acceptor_email)) {
                                $histories[$key]->acceptors[] = $history;
                            } else {
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
                    } else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS) {
                        // 差戻し
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    } else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS) {
                        // 引戻し/ 差戻し依頼/ 差戻し依頼承認
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
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

    /**
     * @param int $document_id 文書ID
     * @param int $mst_company_id 現在回覧者の会社ID
     * @param int $edition_flg 現在回覧者のエディションフラグ
     * @param int $env_flg 環現在回覧者の境フラグ
     * @param int $server_flg 現在回覧者のサーバフラグ
     * @param string $finishedDate 完了年月
     * @param bool $show_flag 履歴：自社のみ(true);すべて(false)
     * @return false|string
     */
    public static function getPDFHistory(int $document_id, int $mst_company_id, int $edition_flg, int $env_flg, int $server_flg, string $finishedDate, bool $show_flag)
    {
        $circular_document = DB::table("circular_document$finishedDate")->where('id', $document_id)->first();

        if (!$circular_document || !$circular_document->id) {
            return false;
        }

        $circular_id = $circular_document->circular_id;

        $circular = DB::table("circular$finishedDate")->where('id', $circular_id)->first();

        if (!$circular) {
            return false;
        }

        $circular_users = DB::table("circular_user$finishedDate")
            ->select('name', 'email')
            ->where('circular_id', $circular_id)
            ->get()
            ->toArray();

        // 社内すべてparent_send_order取得
        $my_company_users = DB::table("circular_user$finishedDate")
            ->where('circular_id', $circular_id)
            ->where('mst_company_id', $mst_company_id)
            ->where('edition_flg', $edition_flg)
            ->where('env_flg', $env_flg)
            ->where('server_flg', $server_flg)
            ->pluck('parent_send_order', 'email')
            ->toArray();

        $parent_send_orders = array_values($my_company_users);
        $emails = array_keys($my_company_users);

        // 履歴取得
        //Append pdf last page
        $histories = DB::table('circular_operation_history as H')
            ->select('H.*', 'C.text', 'S.stamp_image')
            ->leftJoin('document_comment_info as C', function ($query) use ($document_id, $parent_send_orders) {
                $query->on('H.id', '=', 'C.circular_operation_id')
                    ->where('C.circular_document_id', $document_id)
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
            ->Where(function ($query) use ($document_id, $parent_send_orders, $show_flag) {
                $query->whereNull('H.circular_document_id')
                    ->orWhere(function ($query) use ($document_id) {
                        $query->where('H.circular_document_id', $document_id)
                            ->whereIn('H.circular_status', [CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS, CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS]);
                    })
                    ->orWhere(function ($query) use ($document_id, $parent_send_orders, $show_flag) {
                        $query->where('H.circular_document_id', $document_id)
                            ->where('H.circular_status', CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS);
                        if ($show_flag) {
                            $query->WhereIn('S.parent_send_order', $parent_send_orders);
                        }
                    });
            })
            ->orderBy('H.create_at')
            ->orderBy('H.circular_status')
            ->orderBy('H.id')
            ->get()
            ->toArray();

        // PAC_5-1039 合議の場合は、申請承認差戻しの二つのノードが一緒になります。
        $prev_operation_email = ""; // 前の方 operation_email
        $prev_circular_status = 0; // 前の方 circular_status
        $save_key = -1; // マージの下付き
        foreach ($histories as $key => $history) {
            $histories[$key]->acceptors = [];
            if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status  && $history->is_skip == 0 ) {
                $histories[$save_key]->acceptors[] = $history;
                unset($histories[$key]);
            } else {
                if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS) {
                    // 承認
                    if (!empty($history->acceptor_email)) {
                        $histories[$key]->acceptors[] = $history;
                    } else {
                        $histories[$key]->acceptors = [];
                    }
                } else {
                    $histories[$key]->acceptors[] = $history;
                }
                if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 1) {
                    $histories[$key]->acceptors[] = $history;
                }
                $save_key = $key;
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

        // 自社のみの場合
        if ($show_flag) {
            // 承認履歴情報に他会社の情報を削除
            foreach ($histories as $key => $history) {
                // 他会社の場合,情報を削除
                if (array_search($history->operation_email, $emails) === false) {
                    unset($histories[$key]);
                }
            }
        } else {
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
        }

        //PAC_5-1398 添付ファイル情報
        if ($show_flag){
            $attachments = DB::table('circular_attachment')
                ->select('create_at','create_user','file_name','name')
                ->where('circular_id',$circular_id)
                ->where('create_company_id',$mst_company_id)
                ->where('edition_flg',$edition_flg)
                ->where('env_flg',$env_flg)
                ->where('server_flg',$server_flg)
                ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                ->get();
        }else{
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

        $data = [];
        $data['circular_document'] = $circular_document;
        $data['histories'] = $histories;
        $data['histories_text_info'] = $histories_text_info;
        $data['circular_users'] = $circular_users;
        $circular = DB::table("circular$finishedDate")->where('id', $circular_id)->first();
        $data['circular_status'] = CircularOperationHistoryUtils::getCircularStatus($circular ? $circular->circular_status : '');
        $data['circular_attachments'] = $attachments;
        $pdf = PDF::loadView('pdf_template.stamp_histories', $data);
        return base64_encode($pdf->output('document.pdf', "S"));
    }

    /**
     * 回覧文書取得(完了のみ)
     * @param int $circular_id 回覧ID
     * @param int $company_id 承認者企業ID
     * @param string $finishedDate 完了年月
     * @param bool $has_history 履歴
     * @param bool $show_flag 履歴：自社のみ(true);すべて(false)
     * @return false|\Illuminate\Support\Collection 文書本体と履歴base64
     */
    public static function getDocumentsDataByCircular(int $circular_id, int $company_id, string $finishedDate, bool $has_history, bool $show_flag)
    {
        $circular = DB::table("circular$finishedDate")->where('id', $circular_id)->first();
        if (!$circular || $circular->edition_flg == 0) {
            return false;
        }
        if ($circular->edition_flg == config('app.edition_flg') && $circular->env_flg == config('app.server_env') && $circular->server_flg == config('app.server_flg')) {
            // 現在の環境
            $origin_circular_id_to_circular_id[$circular_id] = $circular_id; // $circulars_id_to_origin_map[$circular['origin_circular_id']] = $circular['circular_id']
            $circular_data = self::getLocalDocumentsDataByCircular($origin_circular_id_to_circular_id, $company_id, $circular->edition_flg,
                $circular->env_flg, $circular->server_flg, $finishedDate, $has_history, $show_flag);
        } else {
            //他環境文書取得
            $client = EnvApiUtils::getAuthorizeClient($circular->env_flg, $circular->server_flg);
            if (!$client) {
                Log::error(__('message.false.auth_client'));
                return false;
            }

            $response = $client->post("getEnvDocumentsData", [
                RequestOptions::JSON => [
                    'company_id' => $company_id, //承認者企業ID
                    'edition_flg' => config('app.edition_flg'),
                    'env_flg' => config('app.server_env'),
                    'server_flg' => config('app.server_flg'),
                    'circulars' => [
                        [
                            'origin_circular_id' => $circular->origin_circular_id,
                            'circular_id' => $circular->id,
                        ]
                    ],
                    'check_add_stamp_history' => $has_history,
                    'finishedDate' => $finishedDate,
                ]
            ]);

            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::error($response->getBody());
                return false;
            }
            $result = json_decode($response->getBody(), true);

            $circular_data = collect($result['document_data']);

        }

        return $circular_data;

    }

    /**
     * 現在の環境に文書取得(完了のみ)
     * @param array $origin_circular_id_to_circular_id $array[差出環境circular_id] = $circular[現在回覧者の環境のcircular_id];
     * @param int $company_id 現在回覧者の企業ID
     * @param int $edition_flg 現在回覧者のエディションフラグ
     * @param int $env_flg 現在回覧者の環境フラグ
     * @param int $server_flg 現在回覧者のサーバフラグ
     * @param string $finishedDate 完了年月
     * @param bool $has_history 履歴PDF追加要否
     * @param bool $show_flag 履歴：自社のみ(true);すべて(false)
     * @return \Illuminate\Support\Collection
     */
    public static function getLocalDocumentsDataByCircular(array $origin_circular_id_to_circular_id, int $company_id, int $edition_flg, int $env_flg, int $server_flg,
                                                           string $finishedDate, bool $has_history, bool $show_flag)
    {
        //todo 完了文書取得
        $circular_docs = DB::table("circular_document$finishedDate")
            ->whereIn('circular_id', array_keys($origin_circular_id_to_circular_id))
            ->whereIn('origin_document_id', ['-1', '0'])
            ->where(function ($query) use ($company_id, $edition_flg, $env_flg, $server_flg) {
                $query->where(function ($query1) {
                    $query1->where('confidential_flg', 0)
                        ->where('origin_document_id', 0);
                });
                $query->orWhere(function ($query1) use ($company_id, $edition_flg, $env_flg, $server_flg) {
                    $query1->where('confidential_flg', 1)
                        ->where('create_company_id', $company_id)
                        ->where('origin_edition_flg', $edition_flg)
                        ->where('origin_env_flg', $env_flg)
                        ->where('origin_server_flg', $server_flg);
                });
            })
            ->select('id', 'circular_id', 'file_name', 'parent_send_order')
            ->get()
            ->keyBy('id');

        //完了一覧クロス環境情報の取得
        $document_data = DB::table("document_data$finishedDate as dd")
            ->join("circular_document$finishedDate as cd", 'cd.id', '=', 'dd.circular_document_id')
            ->whereIn('dd.circular_document_id', $circular_docs->keys())
            ->select('cd.file_name', 'cd.file_size', 'dd.circular_document_id', 'cd.circular_id as origin_circular_id', 'dd.file_data', 'cd.create_user')
            ->get();

        // 件名，最終更新日
        $circular_info = DB::table("circular$finishedDate as c")
            ->join("circular_user$finishedDate as cu", function ($join) {
                $join->on('c.id', 'cu.circular_id')
                    ->on('parent_send_order', DB::raw('0'))
                    ->on('child_send_order', DB::raw('0'));
            })
            ->whereIn('c.id', array_keys($origin_circular_id_to_circular_id))
            ->select('c.id', 'c.final_updated_date', 'c.create_user', 'cu.title')
            ->get()
            ->keyBy('id');

        $history_arr = [];

        if ($has_history) {
            foreach ($circular_docs->keys() as $document_id) {
                $history_arr[$document_id] = self::getPDFHistory($document_id, $company_id, $edition_flg, $env_flg, $server_flg, $finishedDate, $show_flag);
            }
        }

        $document_data->map(function ($data) use ($origin_circular_id_to_circular_id, $circular_info, $has_history, $history_arr) {
            $data->circular_id = $origin_circular_id_to_circular_id[$data->origin_circular_id];
            $data->title = $circular_info[$data->origin_circular_id]->title;
            $data->create_user = $circular_info[$data->origin_circular_id]->create_user;
            $data->circular_update_at = $circular_info[$data->origin_circular_id]->final_updated_date;
            // 履歴
            if ($has_history) {
                $data->append_pdf = $history_arr[$data->circular_document_id];
            }
            return $data;
        });
        return $document_data;
    }

    public static function getLongTermHistory($long_term_id, $mst_company_id, $edition_flg, $env_flg, $server_flg, $check_add_text_history, $file_name,$arrLongTermData,$finishedDate)
    {
        try {
            $long_term_document = DB::table('long_term_document as ld')
                ->where('ld.id', $long_term_id)
                ->leftJoin('long_term_circular as lc', 'ld.circular_id', '=', 'lc.id')
                ->select('ld.*', 'lc.final_updated_date as uat', 'lc.create_at as cat')
                ->first();
            if (is_null($long_term_document) || !$long_term_document->id) {
                return ['status' => false, 'message' => __('message.warning.doc＿not_exist')];
            }
            $document = DB::table('circular_document'.$finishedDate)->where('circular_id', $long_term_document->circular_id)->select('update_at', 'create_at')->first();
            $long_term_document->create_at = optional($document)->create_at ?? $long_term_document->cat;
            $long_term_document->update_at = optional($document)->update_at ?? $long_term_document->uat;
            $circular = DB::table("long_term_circular")
                ->where('id', $long_term_document->circular_id)
                ->first();
            if (is_null($circular) || !$circular->id) {
                return ['status' => false, 'message' => __('message.warning.doc＿not_exist')];
            }
            $circular_users = DB::table("long_term_circular_user")
                ->select('name', 'email', 'id')
                ->where('circular_id', $long_term_document->circular_id)
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
            $arrCEmpty = [];
            foreach ($circular_users as $circular_user) {
                if (!isset($arrCEmpty[$circular_user->id])) {
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

            $type = Storage::disk('s3');

            if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5) {
                $type = Storage::disk('k5');
            }
            $s3path =  config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder') . '/' . config('app.server_env') . '/' . config('app.edition_flg')
                . '/' . config('app.server_flg')  . '/' . $mst_company_id . '/' . $long_term_document->circular_id . '/' . $file_name;

            if ($type->exists($s3path)) {
                $file_content = $type->get($s3path);
                $long_term_document->file_data = $file_content;
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
                    ->leftJoin('long_term_stamp_info as S', function ($query) use($long_term_document_id){
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
                foreach ($histories as $history) {
                    if (!isset($arrEmpty[$history->id])) {
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
                foreach ($histories as $key => $history) {
                    $histories[$key]->acceptors = [];
                    // 申請
                    if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 0) {
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    } else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 1){
                        $histories[$key]->acceptors[] = $history;
                        $save_key = $key;
                    }else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 0) {
                        // 承認
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
                            if (!empty($history->acceptor_email)) {
                                $histories[$key]->acceptors[] = $history;
                            } else {
                                $histories[$key]->acceptors = [];
                            }
                            $save_key = $key;
                        }
                    } else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 1){
                        // 承認
                        if(!empty($history->acceptor_email)){
                            $histories[$key]->acceptors[] = $history;
                        }else{
                            $histories[$key]->acceptors = [];
                        }
                        $save_key = $key;
                    }else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS) {
                        // 差戻し
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        }
                    } else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS ||
                        $history->circular_status == CircularOperationHistoryUtils::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS) {
                        // 引戻し/ 差戻し依頼/ 差戻し依頼承認
                        if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status) {
                            $histories[$save_key]->acceptors[] = $history;
                            unset($histories[$key]);
                        } else {
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
                foreach ($histories_text_info as $text) {
                    if (!isset($arrTEmpty[$text->id])) {
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
            return ['status' => false, 'message' => __('message.false.doc_histories_get', ['attribute' => (!empty($long_term_document) ? $long_term_document->circular_id : '')])];
        }
    }
}