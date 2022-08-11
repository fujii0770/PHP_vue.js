<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCircularDocumentAPIRequest;
use App\Http\Requests\API\SearchCircularAPIRequest;
use App\Http\Requests\API\UpdateCircularDocumentAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\StampUtils;
use App\Models\CircularDocument;
use App\Repositories\CircularDocumentRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Response;
use Session;
use PDF;
use GuzzleHttp\RequestOptions;
use App\Http\Utils\StatusCodeUtils;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Class CircularDocumentController
 * @package App\Http\Controllers\API
 */

class CircularDocumentAPIController extends AppBaseController
{
    /** @var  CircularDocumentRepository */
    private $circularDocumentRepository;

    public function __construct(CircularDocumentRepository $circularDocumentRepo)
    {
        $this->circularDocumentRepository = $circularDocumentRepo;
    }

    /**
     * 下書き一覧画面初期化
     *
     * @param SearchCircularAPIRequest $request
     * @return mixed
     */
    public function index(SearchCircularAPIRequest $request){
        $user       = $request->user();

        $id         = \intval($request->get('id'));
        $filename =  CircularDocumentUtils::charactersReplace($request->get('filename'));
        $fromdate   = $request->get('fromdate');
        $todate     = $request->get('todate');
        $page       = $request->get('page', 1);
        $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy    = $request->get('orderBy', "C.final_updated_date");
        $orderDir   = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword =  CircularDocumentUtils::charactersReplace($request->get('keyword'));

        $arrOrder   = ['C.id' => 'C.id','file_names' => 'file_names', 'C.final_updated_date' => 'C.final_updated_date'];
        $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'C.final_updated_date';

        $where = ["C.mst_user_id = $user->id AND C.circular_status in (".CircularUtils::RETRACTION_STATUS.",".CircularUtils::SAVING_STATUS.")"];

        //$where = ["C.mst_user_id = $user->id"];
        $where_arg = [];

        if($filename){
            $where[]        = '(CU.title like ? OR ((CU.title IS NULL OR trim(CU.title)=\'\') and CD.file_name like ?))';
            $where_arg[]    = "%$filename%";
            $where_arg[]    = "%$filename%";
        }
        if($id){
            $where[]        = 'C.id = ?';
            $where_arg[]    = $id;
        }
        if($fromdate){
            $where[]        = 'C.final_updated_date >= ?';
            $where_arg[]    = date($fromdate).' 00:00:00';
        }
        if($todate){
            $where[]        = 'C.final_updated_date <= ?';
            $where_arg[]    = date($todate).' 23:59:59';
        }

        if($keyword){
            $where[]        = '(CU.title like ? OR ((CU.title IS NULL OR trim(CU.title)=\'\') and CD.file_name like ?))';
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
        }

        try{

            $data = DB::table('circular as C')
                ->join('circular_document as CD', function($join) use ($user){
                    $join->on('C.id', '=', 'CD.circular_id');
                    $join->on(function($condition) use ($user){
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function($condition) use ($user){
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('CD.parent_send_order', DB::raw('0'));
                        });
                    });
                })
                ->leftjoin('circular_user as CU', function($join)
                {
                    $join->on('C.id', '=', 'CU.circular_id');
                    $join->on('CU.parent_send_order', '=', DB::raw('0'));
                    $join->on('CU.child_send_order', '=', DB::raw('0'));

                })
                ->select(DB::raw('C.id, C.update_at,C.final_updated_date, CU.title, IF(CU.title IS NULL or trim(CU.title) = \'\', GROUP_CONCAT(CD.file_name  ORDER BY CD.id ASC SEPARATOR \', \'), CU.title) as file_names'))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('C.edition_flg', config('app.edition_flg'))
                ->where('C.env_flg', config('app.server_env'))
                ->where('C.server_flg', config('app.server_flg'))
                ->orderBy($orderBy,$orderDir)
                ->groupBy(['C.id','C.final_updated_date','CU.title'])
                ->paginate($limit)->appends(request()->input());

            // 件名設定
            foreach ($data as $item) {
                if (!$item->title || trim($item->title,' ') == '') {
                    $fileNames = explode(', ', $item->file_names);
                    $item->file_names = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames),0,100) : reset($fileNames);
                }
            }

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($data,'文書保存処理に成功しました。');
    }

    /**
     * Remove the specified CircularDocument from storage.
     * DELETE /circularDocuments/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($circular_id,$id,Request $request)
    {
        try{
            DB::beginTransaction();

            DB::table('text_info')->where('circular_document_id', $id)->delete();
            DB::table('document_data')->where('circular_document_id', $id)->delete();
            DB::table('circular_document')->where('id', $id)->delete();
            DB::table('sticky_notes')->where('document_id', $id)->delete();
            $count = DB::table('circular_document')->where('circular_id', $circular_id)->count();
            if($count <= 0) {
                //PAC_5-1398 回覧中のすべての添付ファイルを削除します。
                CircularAttachmentUtils::deleteAbsoluteAttachments(array($circular_id));
                DB::table('viewing_user')->where('circular_id', $circular_id)->delete();
                DB::table('circular_user')->where('circular_id', $circular_id)->delete();
                DB::table('guest_user')->where('circular_id', $circular_id)->delete();
                DB::table('circular')->where('id', $circular_id)->delete();
            }
            DB::commit();
            return $this->sendSuccess('文書削除処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('文書削除処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

	public function rename($circular_id,$id,Request $request)
	{
		try{
			$file_name = $request->get('file_name');
			DB::table('circular_document')->where('id', $id)->update(['file_name'=>$file_name, 'update_at'=>Carbon::now()]);

			return $this->sendSuccess('文書名変更処理に成功しました。');

		}catch (\Exception $ex) {
			DB::rollBack();
			Log::error($ex->getMessage().$ex->getTraceAsString());
			return $this->sendError('文書名変更処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
		}

	}

    /**
     *
     * @param $circular_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateList($circular_id, Request $request)
    {
        try {
            if ($request['usingHash']) {
                $user = $request['user'];
                $user_name = $request['current_name'];
                if ($request['current_circular_user']) {
                    $mst_company_id = $request['current_circular_user']->mst_company_id;
                    $edition_flg = $request['current_circular_user']->edition_flg;
                    $env_flg = $request['current_circular_user']->env_flg;
                    $server_flg = $request['current_circular_user']->server_flg;
                }
                if ($request['current_viewing_user']) {
                    $mst_company_id = $request['current_viewing_user']->mst_company_id;
                    $edition_flg = $request['current_edition_flg'];
                    $env_flg = $request['current_env_flg'];
                    $server_flg = $request['current_server_flg'];
                }
            } else {
                $user = $request->user();
                $user_name = $user->getFullName();
                $mst_company_id = $user->mst_company_id;
                $edition_flg = config('app.edition_flg');
                $env_flg = config('app.server_env');
                $server_flg = config('app.server_flg');
            }

            $stamp_infos = $request['stamp_infos'];
            $text_infos = $request['text_infos'];
            $comment_infos = $request['comment_infos'];
            $sticky_notes = $request['sticky_notes'];
            $active_circular_document_id = $request['active_id'];

            //実行時間を取得
            $run_time = $request['run_time'];

            DB::beginTransaction();

            $circular = DB::table('circular')->where('id', $circular_id)->first();
            Session::flash('circular_status', $circular->circular_status);
            if (isset($request['update_at']) && $request['update_at'] != "") {
                if ($request['first_page_data']) {
                    $current_circular_user = DB::table('circular_user')->where('circular_id', $circular_id)
                        ->where('email', $user->email)->first();
                    if (isset($request['hasSignature'])) {
                        $count = DB::table('circular')->where('id', $circular_id)
                            ->where('update_at', $request['update_at'])
                            ->update([
                                'first_page_data' => AppUtils::encrypt($request['first_page_data']),
                                'has_signature' => $request['hasSignature'],
                                'update_at' => Carbon::now(),
                                'final_updated_date' => Carbon::now(),
                            ]);
                    } else {
                        $count = DB::table('circular')->where('id', $circular_id)
                            ->where('update_at', $request['update_at'])
                            ->update([
                                'first_page_data' => AppUtils::encrypt($request['first_page_data']),
                                'update_at' => Carbon::now(),
                                'final_updated_date' => Carbon::now(),
                            ]);
                    }
                } else {
                    if (isset($request['hasSignature'])) {
                        $count = DB::table('circular')->where('id', $circular_id)
                            //->where('update_at', $request['update_at'])
                            ->update([
                                'has_signature' => $request['hasSignature'],
                                'update_at' => Carbon::now(),
                                'final_updated_date' => Carbon::now(),
                            ]);
                    } else {
                        $count = DB::table('circular')->where('id', $circular_id)
                            ->where('update_at', $request['update_at'])
                            ->update([
                                'update_at' => $run_time,
                                'final_updated_date' => Carbon::now(),
                            ]);
                    }
                }
                if ($count <= 0) {
                    DB::rollback();
                    return $this->sendError('文書更新処理に失敗しました。', StatusCodeUtils::HTTP_NOT_ACCEPTABLE);
                }
            } else {
                if ($request['first_page_data']) {
                    $current_circular_user = DB::table('circular_user')->where('circular_id', $circular_id)
                        ->where('email', $user->email)->first();
                    if (isset($request['hasSignature'])) {
                        DB::table('circular')->where('id', $circular_id)
                            ->update([
                                'first_page_data' => AppUtils::encrypt($request['first_page_data']),
                                'has_signature' => $request['hasSignature'],
                                'update_at' => Carbon::now(),
                                'final_updated_date' => Carbon::now(),
                            ]);
                    } else {
                        DB::table('circular')->where('id', $circular_id)
                            ->update([
                                'first_page_data' => AppUtils::encrypt($request['first_page_data']),
                                'update_at' => Carbon::now(),
                                'final_updated_date' => Carbon::now(),
                            ]);
                    }
                } else {
                    if (isset($request['hasSignature'])) {
                        DB::table('circular')->where('id', $circular_id)
                            ->update([
                                'has_signature' => $request['hasSignature'],
                                'update_at' => $run_time,
                                'final_updated_date' => Carbon::now(),
                            ]);
                    } else {
                        DB::table('circular')->where('id', $circular_id)
                            ->update([
                                'update_at' => $run_time,
                                'final_updated_date' => Carbon::now(),
                            ]);
                    }
                }
            }

            $insertStampInfo = [];
            $infoStamp = [];
            $infoText = [];
            $infoComment = [];
            $assign_stamp_infos = [];
            $circular_title = DB::table('circular_user')
                ->select('title')
                ->where('circular_id',$circular_id)
                ->first();

            $strCachePre = "stamp_cache_";
            //PAC_5-330 ダウンロード時に捺印履歴を保存しない →PAC_5-1036 ダウンロードしたPDFで404エラーが発生したため修正
            //if (!$request['notSaveStamps']) {
                foreach ($stamp_infos as $stamp_info) {
                    $assign_stamp_id = $stamp_info['sid'];
                    unset($stamp_info['sid']);
                    $check = DB::table('stamp_info')->where('info_id', $stamp_info['info_id'])->count();
                    $arrTempStampInfoCache = Cache::store('database')->get($strCachePre.$stamp_info['file_stampMd5']);
                    $boolStampFlg = $stamp_info['repeated'];
                    unset($stamp_info['repeated']);
                    if(!empty($strTempStampInfoCache) && json_decode($arrTempStampInfoCache,true)){
                        $arrTempStampInfoCache = json_decode($arrTempStampInfoCache,true);
                        DB::table("stamp_info")->where("id",$strTempStampInfoCache)->where('create_at',$arrTempStampInfoCache['run_time'])
                            ->update([
                            'info_id' => $arrTempStampInfoCache['id']
                        ]);
                    }else if($check <= 0){
                        // PAC_5-539 承認履歴情報登録
                        $circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
                            'circular_id' => $circular_id,
                            'circular_document_id' => $stamp_info['circular_document_id'],
                            'operation_email' => $user->email,
                            'operation_name' => $user_name,
                            'acceptor_email' => '',
                            'acceptor_name' => '',
                            'circular_status' => CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS,
                            'create_at' => $run_time,
                        ]);
                        $stamp_info['create_at'] = $run_time;
                        $infoStamp[] = [
                            'stamp_flg' => $stamp_info['stamp_flg'],
                        ];
                        $stamp_info['mst_assign_stamp_id'] = $stamp_info['id'];
                        unset($stamp_info['id']);
                        unset($stamp_info['stamp_flg']);
                        $stamp_info['circular_operation_id'] = $circular_operation_history_id;
                        $insertStampInfo[] = $stamp_info;
                    }

                    // assign_stamp_idが設定されたの場合
                    if ($assign_stamp_id && !$boolStampFlg) {
                        $assign_stamp_info = [
                            'assign_stamp_id' => $assign_stamp_id,
                            'circular_id' => $circular_id,
                            'name' => $stamp_info['name'],
                            'email' => $stamp_info['email'],
                            'serial' => $stamp_info['serial'],
                            'file_name' => $stamp_info['file_name'],
                            'circular_title' => $circular_title->title,
                        ];
                        $assign_stamp_infos[] = $assign_stamp_info;
                    }
                }

                Session::flash('infoStamp', $infoStamp);
                if (count($insertStampInfo)) {
                    foreach($insertStampInfo as $insertStampInfoKey => $insertStampInfoVal){
                        $strTempStampInfoKey = $insertStampInfoVal['file_stampMd5'];
                        unset($insertStampInfoVal['file_stampMd5']);
                        $intInfoID = DB::table('stamp_info')->insertGetId($insertStampInfoVal);
                        Cache::store('database')->put($strCachePre.$strTempStampInfoKey,json_encode([
                            'id' => $intInfoID,
                            'run_time' => $run_time,
                        ]),3600);
                    }
                }

                // 環境をまたぐの場合、自分環境にassign_stamp情報を追加
                if ($edition_flg == 1 && isset($request['usingHash']) && $request['usingHash'] && count($assign_stamp_infos)) {
                    $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
                    if (!$envClient) {
                        return response()->json(['status' => false, 'message' => ['Cannot connect to Env Api']]);
                    }
                    $envClient->post("assign_stamp_info/addAssignStampInfo", [
                        RequestOptions::JSON => [
                            'assign_stamp_infos' => $assign_stamp_infos
                        ]
                    ]);
                }

                // PAC_5-368 document_comment_info 作成
                foreach ($comment_infos as $key => $comment_info) {
                    // 0入力対応
                    if (!$comment_info['text'] && $comment_info['text'] != '0') {
                        unset($comment_infos[$key]);
                        continue;
                    }
                    // PAC_5-330 テキスト追加履歴
                    $circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
                        'circular_id' => $circular_id,
                        'circular_document_id' => $comment_info['circular_document_id'],
                        'operation_email' => $user->email,
                        'operation_name' => $user_name,
                        'acceptor_email' => '',
                        'acceptor_name' => '',
                        'circular_status' => CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS,
                        'create_at' => $run_time,
                    ]);

                    $comment_infos[$key]['create_at'] = $run_time;

                    $infoComment[] = [
                        'document_id' => $comment_info['circular_document_id'],
                        'text' => $comment_info['text']
                    ];

                    $comment_infos[$key]['circular_operation_id'] = $circular_operation_history_id;
                }
                DB::table('document_comment_info')->insert($comment_infos);
                Session::flash('infoComment', $infoComment);

                foreach ($text_infos as $key => $text_info) {
                    // PAC_5-330 テキスト追加履歴
                    $circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
                        'circular_id' => $circular_id,
                        'circular_document_id' => $text_infos[$key]['circular_document_id'],
                        'operation_email' => $user->email,
                        'operation_name' => $user_name,
                        'acceptor_email' => '',
                        'acceptor_name' => '',
                        'circular_status' => CircularOperationHistoryUtils::CIRCULAR_ADD_TEXT_STATUS,
                        'create_at' => $run_time,
                    ]);

                    $text_infos[$key]['create_at'] = $run_time;
                    $infoText[] = [
                        'text' => $text_info['text']
                    ];

                    $text_infos[$key]['circular_operation_id'] = $circular_operation_history_id;
                }
                DB::table('text_info')->insert($text_infos);
                Session::flash('infoText', $infoText);

            $sticky_notes_insert = [];
            foreach ($sticky_notes as $sticky_note) {
                if ($sticky_note['id']) {
                    //author以外の回覧者の場合、[表示\非表示]変更だけ
                    $stick = [
                        'removed_flg' => $sticky_note['removed_flg'],
                    ];
                    if ($sticky_note['operator_email'] == $user->email && $sticky_note['edition_flg'] == $edition_flg && $sticky_note['env_flg'] == $edition_flg && $sticky_note['server_flg'] == $edition_flg) {
                        $stick['note_format'] = $sticky_note['note_format'];
                        $stick['deleted_flg'] = $sticky_note['deleted_flg'];
                        $stick['page_num'] = $sticky_note['page_num'];
                        $stick['top'] = $sticky_note['top'];
                        $stick['left'] = $sticky_note['left'];
                        $stick['note_text'] = $sticky_note['note_text'];
                    }
                    DB::table('sticky_notes')->where('id', $sticky_note['id'])->update($stick);
                } else {
                    unset($sticky_note['id']);
                    $sticky_notes_insert[] = $sticky_note;
                }
            }
            if (count($sticky_notes_insert)) {
                DB::table('sticky_notes')->insert($sticky_notes_insert);
            }

            //}

            $documents = $request['documents'];

            $active_pdf_data = '';
            $active_document = null;

            if ($active_circular_document_id) {
                $active_document = DB::table('circular_document')
                    ->where('id', $active_circular_document_id)
                    ->first();
            }

            foreach ($documents as $document) {
                if ($active_circular_document_id == $document['circular_document_id']) {
                    $active_pdf_data = $document['pdf_data'];
                }
                DB::table('circular_document')->where('id', $document['circular_document_id'])
                    ->update([
                    'confidential_flg' => $document['confidential_flg'] ? $document['confidential_flg'] : 0,
                    'file_size' => AppUtils::getFileSize($document['pdf_data']),
                    'update_at' => $run_time,
                    'update_user' => $user->email,
                ]);
                DB::table('document_data')->where('circular_document_id', $document['circular_document_id'])->update([
                    'file_data' => AppUtils::encrypt($document['pdf_data']),
                    'update_at' => $run_time,
                    'update_user' => $user->email,
                ]);
            }

            DB::commit();

            $append_pdf_str = null;
            if ($circular && $circular->id && $request['downloadable']) {
                // 回覧状態が「保存中」
                if ($circular->circular_status == CircularUtils::SAVING_STATUS) {
                    $data = [];
                    $data['document'] = $active_document;
                    $data['circular_link'] = config('app.old_app_url') . '?return_url=' . urlencode(config('app.new_app_url') . '/saves/' . $circular_id);
                    $pdf = PDF::loadView('pdf_template.circular_last_page', $data);
                    $append_pdf_str = base64_encode($pdf->output('document.pdf', "S"));
                } else if ($request['check_add_text_history'] || $request['check_add_stamp_history']) {
                    $circular_users = DB::table('circular_user')
                        ->select('circular_user.name', 'circular_user.email')
                        ->where('circular_id', $circular_id)
                        ->orderBy('id', 'asc')
                        ->get()
                        ->toArray();

                    // PAC_5-330 自社のみの捺印履歴
                    $show_flag = $request['check_add_text_history'];
                    $active_document_id = $active_document ? $active_document->id : 0;

                    // 社内すべてparent_send_order取得
                    $my_company_users = DB::table('circular_user')
                        ->where('circular_id', $circular->id)
                        ->where('mst_company_id', $mst_company_id)
                        ->where('edition_flg', $edition_flg)
                        ->where('env_flg', $env_flg)
                        ->where('server_flg', $server_flg)
                        ->pluck('parent_send_order', 'email')
                        ->toArray();

                    $parent_send_orders = array_values($my_company_users);
                    $emails = array_keys($my_company_users);

                    $histories = DB::table('circular_operation_history as H')
                        ->select('H.*', 'C.text', 'S.stamp_image')
                        ->leftJoin('document_comment_info as C', function ($query) use ($active_document_id, $parent_send_orders) {
                            $query->on('H.id', '=', 'C.circular_operation_id')
                                ->where('C.circular_document_id', $active_document_id)
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
                        ->Where(function ($query) use ($active_document_id, $show_flag, $parent_send_orders) {
                            $query->whereNull('H.circular_document_id')
                                ->orWhere(function ($query) use ($active_document_id) {
                                    $query->where('H.circular_document_id', $active_document_id)
                                        ->whereIn('H.circular_status', [CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS, CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS]);
                                })
                                ->orWhere(function ($query) use ($active_document_id, $show_flag, $parent_send_orders) {
                                    $query->where('H.circular_document_id', $active_document_id)
                                        ->where('H.circular_status', CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS);
                                    if ($show_flag) {
                                        $query->WhereIn('S.parent_send_order', $parent_send_orders);
                                    }
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
                        if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 0) {
                            if ($history->operation_email == $prev_operation_email && $history->circular_status == $prev_circular_status && $history->is_skip == 0) {
                                $histories[$save_key]->acceptors[] = $history;
                                unset($histories[$key]);
                            } else {
                                $histories[$key]->acceptors[] = $history;
                                $save_key = $key;
                            }
                        }else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS && $history->is_skip == 1){
                            $histories[$key]->acceptors[] = $history;
                            $save_key = $key;
                        } else if ($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS && $history->is_skip == 0) {
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

                    // PAC_5-330 テキスト追加履歴
                    $histories_text_info = DB::table('circular_operation_history')
                        ->select('circular_operation_history.*', 'text_info.text')
                        ->leftJoin('text_info', function ($query) use ($circular_id) {
                            $query->on('circular_operation_history.id', '=', 'text_info.circular_operation_id');
                        })
                        ->where('circular_id', $circular_id)
                        ->where('circular_status', CircularOperationHistoryUtils::CIRCULAR_ADD_TEXT_STATUS)
                        ->Where('circular_operation_history.circular_document_id', $active_document->id)
                        ->orderBy('circular_operation_history.create_at', 'asc')
                        ->orderBy('circular_operation_history.circular_status', 'asc')
                        ->orderBy('circular_operation_history.id', 'asc')
                        ->get()
                        ->toArray();

                    /* ▼ PAC_5-790 捺印履歴の表示内容変更 start ▼ */
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

                    // 文書情報に他会社の情報を削除
                    foreach ($circular_users as $key => $circular_user) {
                        // 他会社の場合
                        if (array_search($circular_user->email, $emails) === false) {
                            // 名前を削除
                            $circular_user->name = "";
                        }
                    }
                    /* ▲ PAC_5-790 捺印履歴の表示内容変更  end  ▲ */

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
                    $data['circular_document'] = $active_document;
                    $data['histories'] = $histories;
                    $data['histories_text_info'] = $histories_text_info;
                    $data['circular_users'] = $circular_users;
                    $data['circular_status'] = CircularOperationHistoryUtils::getCircularStatus($circular ? $circular->circular_status : '');
                    $data['circular_attachments'] = $attachments;

                    $pdf = PDF::loadView('pdf_template.stamp_histories', $data);
                    $append_pdf_str = base64_encode($pdf->output('document.pdf', "S"));
                }
            }
            $update_at = DB::table('circular')->where('id', $circular_id)->value('update_at');
            return $this->sendResponse(['pdf_data' => $active_pdf_data, 'append_data' => $append_pdf_str, 'update_at' => $update_at,
                'circular_status' => $circular->circular_status], '文書更新処理に成功しました。');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('文書更新処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Undocumented function
     *
     * @param $circular_id
     * @param $id
     * @param Request $request
     */
    public function replacePdf($circular_id, $id, Request $request)
    {
        try {
            $pdf_data = $request['pdf_data'];
            $user_email = $request['user_email'];
            $document_data_update_at = $request['document_data_update_at'];

            DB::beginTransaction();

            // チェック処理を行う
            // 更新されていないかチェックを行う
            // ユーザーと更新日時が一致しているか確認する
            $lastUpdate = DB::table('document_data')->where('circular_document_id', $id)->select('update_at', 'update_user')->first();
            if ($document_data_update_at !== $lastUpdate->update_at || $user_email !== $lastUpdate->update_user) {
                Log::error('PDF変更処理に失敗しました。ファイルが更新されている可能性があります。circular_document_id:' . $id);
                return $this->sendError('PDF変更処理に失敗しました。ファイルが更新されている可能性があります。', \Illuminate\Http\Response::HTTP_PRECONDITION_FAILED);
            }

            // 更新ユーザーが同じことをチェック済みなので、update_userは更新しない
            DB::table('document_data')->where('circular_document_id', $id)->update([
                'file_data' => AppUtils::encrypt($pdf_data),
                'update_at' => Carbon::now()
            ]);
            $updateTime = DB::table('document_data')->where('circular_document_id', $id)->select('update_at')->first()->update_at;
            DB::commit();
            
            return $this->sendResponse(['document_data_update_at' => $updateTime], 'PDF変更処理に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('PDF変更処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllDocument($circular_id){
        $circularDocumentAll = DB::table('circular_document')
                                ->select('circular_document.*', 'document_data.file_data')
                                ->join('document_data', 'document_data.circular_document_id', '=', 'circular_document.id')
                                ->where('circular_document.circular_id', $circular_id)
                                ->get();
        foreach ($circularDocumentAll as $key => $document) {
            $circularDocumentAll[$key]->file_data = AppUtils::decrypt($circularDocumentAll[$key]->file_data);
        }
        return $this->sendResponse($circularDocumentAll,'文書取得処理に成功しました。4');
    }

    /**
     * PDFデータと操作履歴可能取得
     * @param $circular_id
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function show($circular_id, $id, Request $request)
    {
        try {
            // 回覧完了日時
            if (isset($request['finishedDate']) && $request['finishedDate']) {  // 完了一覧、今月以外
                // 回覧完了日時
                $finishedDateKey = $request->get('finishedDate');
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            } else {    // 完了一覧以外
                $finishedDate = '';
            }
            if ($request['usingHash']) {
                if ($request['current_circular_user']) {
                    $mst_company_id = $request['current_circular_user']->mst_company_id;
                    $edition_flg = $request['current_circular_user']->edition_flg;
                    $env_flg = $request['current_circular_user']->env_flg;
                    $server_flg = $request['current_circular_user']->server_flg;
                }
                if ($request['current_viewing_user']) {
                    $mst_company_id = $request['current_viewing_user']->mst_company_id;
                    $edition_flg = $request['current_edition_flg'];
                    $env_flg = $request['current_env_flg'];
                    $server_flg = $request['current_server_flg'];
                }
            } else {
                $user = $request->user();
                $mst_company_id = $user->mst_company_id;
                $edition_flg = config('app.edition_flg');
                $env_flg = config('app.server_env');
                $server_flg = config('app.server_flg');
            }
            $circular_document = DB::table("circular_document$finishedDate as D")
                ->select('D.*', 'DD.file_data')
                ->join("document_data$finishedDate as DD", 'DD.circular_document_id', '=', 'D.id')
                ->where('D.id', $id)->first();

            if (!$circular_document || !$circular_document->id) {
                log::debug('finishedDate:'.$finishedDate.', circular_document_id:'.$id);
                return $this->sendError('Circular Document not found', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $circular = DB::table("circular$finishedDate")->where('id', $circular_document->circular_id)->first();

            $circular_users = DB::table("circular_user$finishedDate")
                ->select('name', 'email')
                ->where('circular_id', $circular_document->circular_id)
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();

            // PAC_5-330 自社のみの捺印履歴
            $show_flag = $request['check_add_text_history'];
            $circular_document_id = $circular_document ? $circular_document->id : 0;

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
                ->Where(function ($query) use ($circular_document_id, $show_flag, $parent_send_orders) {
                    $query->whereNull('H.circular_document_id')
                        ->orWhere(function ($query) use ($circular_document_id) {
                            $query->where('H.circular_document_id', $circular_document_id)
                                ->whereIn('H.circular_status', [CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS, CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS]);
                        })
                        ->orWhere(function ($query) use ($circular_document_id, $show_flag, $parent_send_orders) {
                            $query->where('H.circular_document_id', $circular_document_id)
                                ->where('H.circular_status', CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS);
                            if ($show_flag) {
                                $query->WhereIn('S.parent_send_order', $parent_send_orders);
                            }
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
                } else if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS ||
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

            // PAC_5-330 テキスト追加履歴
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

            /* ▼ PAC_5-790 捺印履歴の表示内容変更 start ▼ */
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

            // 文書情報に他会社の情報を削除
            foreach ($circular_users as $key => $circular_user) {
                // 他会社の場合
                if (array_search($circular_user->email, $emails) === false) {
                    // 名前を削除
                    $circular_user->name = "";
                }
            }
            /* ▲ PAC_5-790 捺印履歴の表示内容変更  end  ▲ */

            // ダウンロード履歴には部署印が表示されません。 白い背景を追加
            foreach ($histories as $key => $history) {
                if($history->circular_status == CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS && $history->stamp_image){
                    $histories[$key]->stamp_image = StampUtils::imageConvert($history->stamp_image);
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
            $circular_document->file_data = AppUtils::decrypt($circular_document->file_data);

            return $this->sendResponse($circular_document, '文書取得処理に成功しました。');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('文書取得処理に失敗しました。' . $ex->getMessage(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkHasSignatureSaveFile($circular_id, Request $request){
        $circular = DB::table('circular')
            ->select('has_signature', 'circular_status')
            ->where('id', $circular_id)
            ->first();
        $hasSignature = false;
        $circularStatus = CircularUtils::SAVING_STATUS;
        if($circular){
            $hasSignature = $circular->has_signature;
            $circularStatus = $circular->circular_status;
        }
        return $this->sendResponse(['hasSignature'=>$hasSignature, 'circularStatus' => $circularStatus],'');
    }

    public function checkUsingTasSaveFile($circular_id, Request $request){
        try {
            $circularDocuments = [];
            $circularDocumentIds = $request['circular_document_ids'];
            $issueCountUser = null;

            //check final approval
            $countWorkingUser = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->whereIn('circular_status',[CircularUserUtils::NOT_NOTIFY_STATUS,
                    CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                    CircularUserUtils::READ_STATUS,
                    CircularUserUtils::SEND_BACK_STATUS,
                    CircularUserUtils::SUBMIT_REQUEST_SEND_BACK,
                    CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                    CircularUserUtils::REVIEWING_STATUS])
                ->count();
            $circular = DB::table('circular')
                ->where('id', $circular_id)
                ->select('special_site_flg')
                ->first();

            $isFinalApproval = false;
            if($countWorkingUser == 1){
                $circularUserQuery = DB::table('circular_user')
                    ->select('circular_id','id','mst_company_id','mst_user_id','parent_send_order','child_send_order','email','env_flg','edition_flg','server_flg')
                    ->where('circular_id', $circular_id)
                    ->where('edition_flg', config('app.edition_flg'))
                    ->orderBy('parent_send_order')
                    ->orderBy('child_send_order');
                $allCircularUsers = $circularUserQuery->get();

                $isFinalApproval = true;
                if($allCircularUsers[0]->parent_send_order == 0){
                    $senderCompany = DB::table('mst_company')
                        ->where('id', $allCircularUsers[0]->mst_company_id)
                        ->first();
                }else{
                    $senderCompany = null;
                }

                $arrCompaniesSameEnv = [];
                $arrUsersSameEnv = [];
                $arrCompaniesOtherEnv = [];
                $arrUsersOtherEnv = [];
                foreach($allCircularUsers as $value){
                    if ($value->env_flg == config('app.server_env') && $value->server_flg == config('app.server_flg')){
                        $arrCompaniesSameEnv[] = $value->mst_company_id;
                        $arrUsersSameEnv[] = $value->mst_user_id;
                    }else{
                        $arrCompaniesOtherEnv[$value->env_flg.$value->server_flg][] = $value->mst_company_id;
                        $arrUsersOtherEnv[$value->env_flg.$value->server_flg][] = $value->email;
                    }
                }

                $mstTimeStamps = DB::table('mst_limit')
                    ->whereIn('mst_company_id', $arrCompaniesSameEnv)
                    ->where('time_stamp_permission', AppUtils::STATE_VALID)
                    ->get()->keyBy('mst_company_id')->toArray();

                $mstUserInfos = DB::table('mst_user_info')
                    ->whereIn('mst_user_id', $arrUsersSameEnv)
                    ->where('time_stamp_permission', AppUtils::STATE_VALID)
                    ->get()->keyBy('mst_user_id')->toArray();

                $mstStampInfos = DB::table('stamp_info')
                    ->select(DB::raw('CONCAT_WS("-", email, circular_document_id) as email_document'))
                    ->whereIn('circular_document_id',$circularDocumentIds)
                    ->where('time_stamp_permission', AppUtils::STATE_VALID)
                    ->get()->keyBy('email_document')->toArray();

                $timeStampIssueCounts = DB::table('mst_company')
                    ->select('id')
                    ->whereIn('id', $arrCompaniesSameEnv)
                    ->where('time_stamp_issuing_count', AppUtils::STATE_VALID)
                    ->where('stamp_flg', AppUtils::STATE_VALID)
                    ->get()->keyBy('id')->toArray();

                $mstTimeStampOtherEnvs = [];
                $mstUserInfoOtherEnvs = [];
                $timeStampIssueCountOtherEnvs = [];

                if (count($arrCompaniesOtherEnv)){

                    foreach ($arrCompaniesOtherEnv as $key => $value){

                        $env = substr($key,0,1);
                        $server = substr($key,1,strlen($key)-1);

                        $envClient = EnvApiUtils::getAuthorizeClient($env,$server);
                        if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                        $response = $envClient->get("getTimestamps?ids=".implode(',', $arrCompaniesOtherEnv[$key]),[]);
                        if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $envTimeStamps = json_decode($response->getBody())->data;
                            if ($envTimeStamps){
                                foreach ($envTimeStamps as $envTimeStamp){
                                    if ($envTimeStamp->time_stamp_permission == AppUtils::STATE_VALID){
                                        $mstTimeStampOtherEnvs[$key][] = $envTimeStamp->mst_company_id;
                                    }
                                    if ($envTimeStamp->time_stamp_issuing_count == AppUtils::STATE_VALID && $envTimeStamp->stamp_flg == AppUtils::STATE_VALID ){
                                        $timeStampIssueCountOtherEnvs[$key][] = $envTimeStamp->mst_company_id;
                                    }
                                }
                            }
                        }else{
                            Log::warning('checkUsingTasSaveFile: Cannot getTimestamps from other env');
                            Log::warning($response->getBody());
                        }

                        $response = $envClient->get("getUserInfos?ids=".urlencode(implode(',', $arrUsersOtherEnv[$key])),[]);
                        if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $envUserInfos = json_decode($response->getBody())->data;
                            if ($envUserInfos){
                                foreach ($envUserInfos as $envUserInfo){
                                    if ($envUserInfo->time_stamp_permission == AppUtils::STATE_VALID){
                                        $mstUserInfoOtherEnvs[$key][] = $envUserInfo->email;
                                    }
                                }
                            }
                        }else{
                            Log::warning('checkUsingTasSaveFile: Cannot getUserInfos from other env');
                            Log::warning($response->getBody());
                        }
                    }
                }
                foreach($allCircularUsers as $circularUser){
                    if ($circularUser->env_flg == config('app.server_env') && $circularUser->server_flg == config('app.server_flg')){
                        if (key_exists($circularUser->mst_company_id, $timeStampIssueCounts)){
                            $issueCountUser = $circularUser;
                            break;
                        }
                    }else{
                        if (isset($timeStampIssueCountOtherEnvs[$circularUser->env_flg.$circularUser->server_flg]) && key_exists($circularUser->mst_company_id, $timeStampIssueCountOtherEnvs[$circularUser->env_flg.$circularUser->server_flg])){
                            $issueCountUser = $circularUser;
                            break;
                        }
                    }
                }

                foreach($allCircularUsers as $circularUser){
                    if($circular->special_site_flg != 1 && $senderCompany->stamp_flg != AppUtils::STATE_VALID && $circularUser->parent_send_order == 0){
                        continue;
                    }
                    if ($circularUser->env_flg == config('app.server_env') && $circularUser->server_flg == config('app.server_flg')){
                        //check level company
                        if(key_exists($circularUser->mst_company_id, $mstTimeStamps)){
                            foreach($circularDocumentIds as $index => $circular_document_id){
                                $circularDocuments[$index] = ['usingtas'=>true, 'user_add_stamp'=>$circularUser];
                                unset($circularDocumentIds[$index]);
                            }
                            break;
                        }else{
                            //check level user
                            if(key_exists($circularUser->mst_user_id, $mstUserInfos)){
                                foreach($circularDocumentIds as $index => $circular_document_id){
                                    $circularDocuments[$index] = ['usingtas'=>true, 'user_add_stamp'=>$circularUser];
                                    unset($circularDocumentIds[$index]);
                                }
                                break;
                            }
                        }
                    }else{
                        //check level company
                        if(isset($mstTimeStampOtherEnvs[$circularUser->env_flg.$circularUser->server_flg]) && in_array($circularUser->mst_company_id, $mstTimeStampOtherEnvs[$circularUser->env_flg.$circularUser->server_flg])){
                            foreach($circularDocumentIds as $index => $circular_document_id){
                                $circularDocuments[$index] = ['usingtas'=>true, 'user_add_stamp'=>$circularUser];
                                unset($circularDocumentIds[$index]);
                            }
                            break;
                        }else{
                            //check level user
                            if(isset($mstUserInfoOtherEnvs[$circularUser->env_flg.$circularUser->server_flg]) && in_array($circularUser->email, $mstUserInfoOtherEnvs[$circularUser->env_flg.$circularUser->server_flg])){
                                foreach($circularDocumentIds as $index => $circular_document_id){
                                    $circularDocuments[$index] = ['usingtas'=>true, 'user_add_stamp'=>$circularUser];
                                    unset($circularDocumentIds[$index]);
                                }
                                break;
                            }
                        }
                    }
                    //check level stamp
                    foreach($circularDocumentIds as $index => $circular_document_id){
                        $key = $circularUser->email.'-'.$index;
                        if (key_exists($key, $mstStampInfos)){
                            $circularDocuments[$index] = ['usingtas'=>true, 'user_add_stamp'=>$circularUser];
                            unset($circularDocumentIds[$index]);
                        }
                    }
                }

                foreach($circularDocumentIds as $index=>$circular_document_id){
                    $circularDocuments[$index] = ['usingtas'=>false, 'user_add_stamp'=>null];
                }

                if ($issueCountUser){
                    // set issue count user
                    foreach($circularDocuments as $key => $circularDocument){
                        if ($circularDocuments[$key]['usingtas']){
                            $circularDocuments[$key]['user_add_stamp'] = $issueCountUser;
                        }
                    }
                }
            }else{
                foreach($circularDocumentIds as $key=>$value){
                    $circularDocuments[$key] = ['usingtas'=>false, 'user_add_stamp'=>null];
                }
            }
            return $this->sendResponse(['final_approval'=>$isFinalApproval,'circular_documents'=>$circularDocuments,'issuing_count'=>$issueCountUser],'');
        } catch (\Exception $ex) {
            Log::error('文書チェック処理に失敗しました。circular_id:'.$circular_id.', circularDocumentIds:'.implode(', ',$circularDocumentIds));
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('文書チェック処理に失敗しました。' . $ex->getMessage(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkUsingTasDownloadFile($circular_id, Request $request)
    {
        try {
            $user = null;
            $checkUsingTas = false;

            // 回覧完了日時
            if (isset($request['finishedDate']) && $request['finishedDate']) {  // 完了一覧、今月以外
                // 回覧完了日時
                $finishedDateKey = $request->get('finishedDate');
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            } else {    // 完了一覧以外
                $finishedDate = '';
            }

            $circular = DB::table("circular$finishedDate")
                ->where('id', $circular_id)
                ->whereIn('circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                ->count();

            if ($circular) {
                if (isset($request['usingHash']) && $request['usingHash']) {
                    if (!$request['is_external'] && $request['current_edition_flg'] == config('app.edition_flg')) {
//                    $appEnv = config('app.server_env');
                        if ($request['current_env_flg'] == config('app.server_env') && $request['current_server_flg'] == config('app.server_flg')) {
                            $user = $request['user'];
                        } else {
                            $envUser = null;
                            if (isset($request['current_circular_user']) && $request['current_circular_user']) {
                                $envUser = $request['current_circular_user'];
                            } elseif (isset($request['current_viewing_user']) && $request['current_viewing_user']) {
                                $envUser = $request['current_viewing_user'];
                            }
                            if ($envUser) {
                                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                                if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                                $response = $envClient->get("getCompany/$envUser->mst_company_id", []);
                                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                    //check stamps flg company
                                    $envCompany = json_decode($response->getBody())->data;
                                    if ($envCompany && $envCompany->stamp_flg && $envCompany->esigned_flg) {
                                        $response = $envClient->get("getTimestamp/$envUser->mst_company_id", []);
                                        if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                            //check level company
                                            $envTimestamp = json_decode($response->getBody())->data;
                                            if ($envTimestamp->time_stamp_permission) {
                                                $checkUsingTas = true;
                                            } else {
                                                $response = $envClient->get("getUserInfo/" . $request['current_email'], []);
                                                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                                    $envUserinfo = json_decode($response->getBody())->data;
                                                    //check level user
                                                    if ($envUserinfo && $envUserinfo->time_stamp_permission) {
                                                        $checkUsingTas = true;
                                                    } else {
                                                        //check level stamp
                                                        $check_add_stamps = DB::table('stamp_info')
                                                            ->where('circular_document_id', $request['circular_document_id'])
                                                            ->where('time_stamp_permission', AppUtils::STATE_VALID)
                                                            ->count();
                                                        if ($check_add_stamps) {
                                                            $checkUsingTas = true;
                                                        }
                                                    }
                                                } else {
                                                    Log::warning('CheckUsingTasDownloadFile: Cannot getUserInfo from other env');
                                                    Log::warning($response->getBody());
                                                }
                                            }
                                        } else {
                                            Log::warning('CheckUsingTasDownloadFile: Cannot getTimestamp from other env');
                                            Log::warning($response->getBody());
                                        }
                                    }
                                } else {
                                    Log::warning('CheckUsingTasDownloadFile: Cannot getCompany from other env');
                                    Log::warning($response->getBody());
                                }
                            }
                        }
                    }
                } else {
                    $user = $request->user();
                }

                if ($user) {
                    //check stamps flg company
                    $checkStampFlgCompany = DB::table('mst_company')
                        ->select('id')
                        ->where('id', $user->mst_company_id)
                        ->where(function ($query) {
                            $query->where('stamp_flg', 0)
                                ->orWhere('esigned_flg', 0);
                        })
                        ->count();
                    if ($checkStampFlgCompany) {
                        $checkUsingTas = false;
                    } else {
                        //check level company
                        $checkLevelCompany = DB::table('mst_limit')
                            ->select('mst_company_id')
                            ->where('mst_company_id', $user->mst_company_id)
                            ->where('time_stamp_permission', 1)
                            ->count();
                        if ($checkLevelCompany) {
                            $checkUsingTas = true;
                        } else {
                            //check level user
                            $checkLevelUser = DB::table('mst_user_info')
                                ->select('mst_user_id')
                                ->where('mst_user_id', $user->id)
                                ->where('time_stamp_permission', 1)
                                ->count();
                            if ($checkLevelUser) {
                                $checkUsingTas = true;
                            } else {
                                //check level stamp
                                $check_add_stamps = DB::table('stamp_info')
                                    ->where('circular_document_id', $request['circular_document_id'])
                                    ->where('time_stamp_permission', AppUtils::STATE_VALID)
                                    ->count();
                                if ($check_add_stamps) {
                                    $checkUsingTas = true;
                                }
                            }
                        }
                    }
                }
            }

            return $this->sendResponse($checkUsingTas, 'check usingtas true');
        } catch (\Exception $ex) {
            Log::error('文書ダウンロードチェック処理に失敗しました。circular_id:' . $circular_id);
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('文書ダウンロードチェック処理に失敗しました。。' . $ex->getMessage(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkUsingTasDownloadFileNoAddHistory($circular_id, Request $request)
    {
        try {
            $user = null;
            $checkUsingTas = false;
            // 回覧完了日時
            if (isset($request['finishedDate']) && $request['finishedDate']) {  // 完了一覧、今月以外
                // 回覧完了日時
                $finishedDateKey = $request->get('finishedDate');
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            } else {    // 完了一覧以外
                $finishedDate = '';
            }
            $circular = DB::table("circular$finishedDate")
                ->where('id', $circular_id)
                ->whereIn('circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                ->count();

            if ($circular) {
                if (isset($request['usingHash']) && $request['usingHash']) {
                    if (!$request['is_external'] && $request['current_edition_flg'] == config('app.edition_flg')) {
//                    $appEnv = config('app.server_env');
                        if ($request['current_env_flg'] == config('app.server_env') && $request['current_server_flg'] == config('app.server_flg')) {
                            $user = $request['user'];
                        } else {
                            $envUser = null;
                            if (isset($request['current_circular_user']) && $request['current_circular_user']) {
                                $envUser = $request['current_circular_user'];
                            } elseif (isset($request['current_viewing_user']) && $request['current_viewing_user']) {
                                $envUser = $request['current_viewing_user'];
                            }
                            if ($envUser) {
                                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                                if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                                $response = $envClient->get("getCompany/$envUser->mst_company_id", []);
                                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                    //check stamps flg company
                                    $envCompany = json_decode($response->getBody())->data;
                                    if ($envCompany && $envCompany->stamp_flg && $envCompany->esigned_flg) {
                                        //check time_stamp_info
                                        $hasTimeStamp = DB::table('time_stamp_info')
                                            ->where('circular_document_id', $request['circular_document_id'])
                                            ->count();
                                        if ($hasTimeStamp) {
                                            $checkUsingTas = false;
                                        } else {
                                            //check level company
                                            $response = $envClient->get("getTimestamp/$envUser->mst_company_id", []);
                                            if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                                //check level company
                                                $envTimestamp = json_decode($response->getBody())->data;
                                                if ($envTimestamp->time_stamp_permission) {
                                                    $checkUsingTas = true;
                                                } else {
                                                    $response = $envClient->get("getUserInfo/" . $request['current_email'], []);
                                                    if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                                        $envUserinfo = json_decode($response->getBody())->data;
                                                        //check level user
                                                        if ($envUserinfo && $envUserinfo->time_stamp_permission) {
                                                            $checkUsingTas = true;
                                                        } else {
                                                            //check level stamp
                                                            $check_add_stamps = DB::table('stamp_info')
                                                                ->where('circular_document_id', $request['circular_document_id'])
                                                                ->where('time_stamp_permission', AppUtils::STATE_VALID)
                                                                ->count();
                                                            if ($check_add_stamps) {
                                                                $checkUsingTas = true;
                                                            }
                                                        }
                                                    } else {
                                                        Log::warning('CheckUsingTasDownloadFile: Cannot getUserInfo from other env');
                                                        Log::warning($response->getBody());
                                                    }
                                                }
                                            } else {
                                                Log::warning('checkUsingTasDownloadFileNoAddHistory: Cannot getTimestamp from other env');
                                                Log::warning($response->getBody());
                                            }
                                        }
                                    }
                                } else {
                                    Log::warning('checkUsingTasDownloadFileNoAddHistory: Cannot getCompany from other env');
                                    Log::warning($response->getBody());
                                }
                            }
                        }
                    }
                } else {
                    $user = $request->user();
                }

                if ($user) {
                    //check stamps flg company
                    $checkStampFlgCompany = DB::table('mst_company')
                        ->select('id')
                        ->where('id', $user->mst_company_id)
                        ->where(function ($query) {
                            $query->where('stamp_flg', 0)
                                ->orWhere('esigned_flg', 0);
                        })
                        ->count();
                    if ($checkStampFlgCompany) {
                        $checkUsingTas = false;
                    } else {
                        //check time_stamp_info
                        $hasTimeStamp = DB::table('time_stamp_info')
                            ->where('circular_document_id', $request['circular_document_id'])
                            ->count();
                        if ($hasTimeStamp) {
                            $checkUsingTas = false;
                        } else {
                            //check level company
                            $checkLevelCompany = DB::table('mst_limit')
                                ->select('mst_company_id')
                                ->where('mst_company_id', $user->mst_company_id)
                                ->where('time_stamp_permission', 1)
                                ->count();
                            if ($checkLevelCompany) {
                                $checkUsingTas = true;
                            } else {
                                //check level user
                                $checkLevelUser = DB::table('mst_user_info')
                                    ->select('mst_user_id')
                                    ->where('mst_user_id', $user->id)
                                    ->where('time_stamp_permission', 1)
                                    ->count();
                                if ($checkLevelUser) {
                                    $checkUsingTas = true;
                                } else {
                                    //check level stamp
                                    $check_add_stamps = DB::table('stamp_info')
                                        ->where('circular_document_id', $request['circular_document_id'])
                                        ->where('time_stamp_permission', AppUtils::STATE_VALID)
                                        ->count();
                                    if ($check_add_stamps) {
                                        $checkUsingTas = true;
                                    } else {
                                        $checkUsingTas = false;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $this->sendResponse($checkUsingTas, '');
        } catch (\Exception $ex) {
            Log::error('文書ダウンロードチェック処理に失敗しました。circular_id:'.$circular_id);
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('文書ダウンロードチェック処理に失敗しました。。' . $ex->getMessage(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findOtherAttachmentInfo(Request $request)
    {
        // Current environment circular_id
        $intCircularID = $request['circular_id'];
        // Current environment user email
        $strCurrentEmail =  $request['user_email'];
        // other environment user
        $strOtherServerUserEmail = $request['opposite_user_mail'];
        // timedate
        $strFinishedDate = $request['finishedDate'];
        // Parameter error
        if (!$intCircularID || !$strCurrentEmail || !$strOtherServerUserEmail) {
            return $this->sendError('パラメータエラー。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        // Get user company ID
        $intOtherCompanyID = DB::table("circular_user$strFinishedDate")
            ->where("circular_id",$intCircularID)
            ->where("email",$strOtherServerUserEmail)
            ->value("mst_company_id");
        try {
            $obAttachment = DB::table("circular_attachment")
                ->where("status", CircularAttachmentUtils::ATTACHMENT_CHECK_SUCCESS_STATUS)
                ->where("circular_id", $intCircularID)
                ->where(function($query) use ($intOtherCompanyID){
                        // Get Other company created attachment
                        $query->where(function($query1) use($intOtherCompanyID){
                            $query1->where('confidential_flg',CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_FALSE);
                            $query1->whereNotIn('create_company_id',[$intOtherCompanyID]);
                        });
                        // Get  My company created attachment
                        $query->orWhere(function($query2) use($intOtherCompanyID){
                            $query2->where('create_company_id',$intOtherCompanyID);
                        });
                })
                ->get()->toArray();
            // Get Different environment OSS
            foreach($obAttachment as $key => $item){
                if (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5) {
                    $file_data = chunk_split(base64_encode(Storage::disk('k5')->get($item->server_url)));
                } else {
                    $file_data = chunk_split(base64_encode(Storage::disk('s3')->get($item->server_url)));
                }
                $obAttachment[$key]->file_data = $file_data;
            }
            return $this->sendResponse($obAttachment, '');
        } catch (\Exception $ex) {
            return $this->sendError('添付ファイル情報の取得に失敗しました。' . $ex->getMessage() . $ex->getLine(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getFirstPageData(Request $request)
    {
        $circularId = $request->get('circular_id');
        if (!$circularId){
            return $this->sendResponse([],'');
        }
        try{
            $circular = DB::table('circular')->where('id', $circularId)->select('first_page_data')->first();
            if ($circular){
                return $this->sendResponse($circular,'');
            }else{
                return $this->sendResponse([],'');
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendResponse([],'');;
        }
    }
}
