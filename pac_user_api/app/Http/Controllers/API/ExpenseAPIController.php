<?php

namespace App\Http\Controllers\API;

use App\Jobs\SendAllUserCircular;
use App\Http\Requests\API\ClearCircularUserRequest;
use App\Http\Requests\API\CreateChildCircularUserAPIRequest;
use App\Http\Requests\API\CreateCircularUserAPIRequest;
use App\Http\Requests\API\SendBackRequest;
use App\Http\Requests\API\SendNotifyContinueRequest;
use App\Http\Requests\API\UpdateCircularUserAPIRequest;
use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Requests\API\UpdateMultipleCircularUserAPIRequest;
use App\Http\Requests\API\UpdateTransferredCircularUserAPIRequest;
use App\Http\Requests\API\UpdateTransferredStatusAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use App\Http\Delegate\EnvApiDelegate;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\SpecialApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\ExpenseUtils;
use App\Mail\SendAccessCodeNoticeMail;
use App\Mail\SendCircularUserMail;
use App\Mail\SendMailInitPassword;
use App\Mail\SendCircularPullBackMail;
use App\Models\Circular;
use App\Models\CircularUser;
use App\Jobs\SendNotification;
use App\Jobs\PushNotify;
use App\Models\User;
use App\Repositories\CircularUserRepository;
use App\Repositories\CompanyRepository;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use Response;
use Image;
use App\Http\Utils\MailUtils;
use Symfony\Component\VarDumper\Cloner\Data;
use App\Models\CircularUserRoutes;
use App\Http\Utils\TemplateRouteUtils;

/**
 * Class CircularUserController
 * @package App\Http\Controllers\API
 */

class ExpenseAPIController extends AppBaseController
{
    /** @var  CircularUserRepository */
    private $circularUserRepository;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    public function __construct(CircularUserRepository $circularUserRepo, CompanyRepository $companyRepository)
    {
        $this->circularUserRepository = $circularUserRepo;
        $this->companyRepository = $companyRepository;
    }

    /**
     * 受信一覧リスト画面初期化
     *
     * @param SearchCircularUserAPIRequest $request
     * @return mixed
     */
    public function indexReceived(SearchCircularUserAPIRequest $request){
        $user       = $request->user();
        $filename   = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $userName   = $request->get('userName');
        $userEmail  = $request->get('userEmail');
        $fromdate   = $request->get('fromdate');
        $todate     = $request->get('todate');
        $sender     = $request->get('sender');
        $status     = $request->get('status', false);
        $page       = $request->get('page', 1);
        $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy    = $request->get('orderBy', "update_at");
        $orderDir   = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword   = CircularDocumentUtils::charactersReplace($request->get('keyword'));

        $arrOrder   = ['title' => 'title','A.email' => 'A.email', 'update_at' => 'update_at',
            'U.circular_status' => 'U.circular_status'];
        $orderBy = [isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at'];

        $where = [];
        $where_arg = [];

        if($filename){
            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and D.file_names like ?))';
            $where_arg[]    = "%$filename%";
            $where_arg[]    = "%$filename%";
        }
        if($userName){
            $where[]        = '(A.name like ? )';
            $where_arg[]    = "%$userName%";
        }
        if($userEmail){
            $where[]        = 'A.email like ?';
            $where_arg[]    = "%$userEmail%";
        }
        if($fromdate){
            $where[]        = 'U.received_date >= ?';
            $where_arg[]    = date($fromdate).' 00:00:00';
        }
        if($todate){
            $where[]        = 'U.received_date <= ?';
            $where_arg[]    = date($todate).' 23:59:59';
        }
        if ($sender) {
            $sender_flg = str_split($sender);
            $where[]        = 'C.edition_flg = ?';
            $where_arg[]    = $sender_flg[0];
            $where[]        = 'C.env_flg = ?';
            $where_arg[]    = $sender_flg[1];
            $where[]        = 'C.server_flg = ?';
            $where_arg[]    = $sender_flg[2];
        }
        if ($keyword) {
            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and D.file_names like ?) OR A.email like ? OR A.name like ?)';
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
        }

        try{
            // PAC_5-2114 Start
            // 統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to ID App');
            }

            $id_app_user_id = 0;
            $response = $client->post("users/checkEmail", [
                RequestOptions::JSON => ['email' => $user->email]
            ]);
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $resData = json_decode((string) $response->getBody());
                if(!empty($resData) && !empty($resData->data)){
                    $id_app_users = $resData->data;
                    // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                    foreach ($id_app_users as $id_app_user) {
                        if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                            $id_app_user_id = $id_app_user->id;
                            break;
                        }
                    }
                }
            }
            //PAC_5-2114 End
            $query_sub = DB::table('circular as C')
                ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
                ->select(DB::raw('C.id, U.parent_send_order, U.receiver_title as file_names, MAX(U.child_send_order) as child_send_order,U.circular_id'))
                // PAC_5-2114 Start
                ->where(function($query) use ($user, $id_app_user_id){
                    $query->where('U.email', $user->email)
                        ->where(function ($query) use ($user, $id_app_user_id) {
                            if ($id_app_user_id !== 0) {
                                $query->where('U.mst_user_id', $user->id)
                                    ->orWhere('U.mst_user_id', $id_app_user_id);
                            } else {
                                $query->where('U.mst_user_id', $user->id);
                            }
                        })
                        ->where('U.edition_flg', config('app.edition_flg'))
                        ->where('U.env_flg', config('app.server_env'))
                        ->where('U.server_flg', config('app.server_flg'));
                })
                // PAC_5-2114 End
                ->groupBy(['C.id', 'U.parent_send_order','U.circular_id','U.receiver_title']);

            $data_query = DB::table('circular as C')
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->join('circular_user as U', function($join){
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('D.parent_send_order','=','U.parent_send_order');
                })
                //請求書関連のみ抽出対象とする
                ->join('eps_t_app as T', 'C.id', '=', 'T.circular_id')
                ->leftjoin('circular_user as A', function($join){
                    $join->on('A.circular_id', '=', 'C.id');
                    $join->on('A.parent_send_order','=',DB::raw("0"));
                    $join->on('A.child_send_order','=',DB::raw("0"));
                })
                ->select(DB::raw('C.id, C.special_site_flg, U.plan_id, U.received_date as update_at, C.update_at as upd_at, C.re_notification_day, C.circular_status status, U.circular_status,U.parent_send_order,U.child_send_order,U.title as subject, D.file_names, IF(U.title IS NULL or trim(U.title) = \'\', D.file_names, U.title) as title, CONCAT(A.name, \' &lt;\',A.email, \'&gt;\') as email, CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender, A.name,U.is_skip'));
            if (count($where)){
                $data_query->whereRaw(implode(" AND ", $where), $where_arg);
            }
            $data_query->where(function($query)use ($user, $status){
                $query->where(function($query1) use ($user, $status){
                    $query1->where('U.email', $user->email);
                        if(!$status){
                            $query1->where(function($query2) {
                                $query2->where(function($query3) {
                                    $query3->where('U.parent_send_order',0);
                                    $query3->where('U.child_send_order',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS]);
                                    });
                                $query2->orWhere(function($query3) {
                                    $query3->where('U.child_send_order','>',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS,
                                            CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                                            CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::NODE_COMPLETED_STATUS]);
                                    });
                                })
                                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 1){
                            $query1->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 2){
                            $query1->where(function($query2) {
                                $query2->where(function($query3) {
                                    $query3->where('U.parent_send_order',0);
                                    $query3->where('U.child_send_order',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
                                });
                                $query2->orWhere(function($query3) {
                                    $query3->where('U.child_send_order','>',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
                                });
                            })
                            ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 3){
                            $query1->where('U.child_send_order','>',0);
                            // PAC_5-2375 START 承認を選択した時は、承認（捺印あり）、承認（捺印なし）を表示
                            $query1->whereIn('U.circular_status', [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS]);
                            // PAC_5-2375 END
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 4){
                            $query1->where('U.child_send_order','>',0);
                            $query1->where('U.circular_status', CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS);
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 6){
                            $query1->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                        }else if($status == 5){
                            // PAC_5-2375 START 差戻しを選択した時は、差戻し（既読）、差戻し（未読）を表示
                            $query1->whereIn('U.circular_status', [CircularUserUtils::READ_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS])
                                ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                            // PAC_5-2375 END
                        }else if($status == 7){
							// 差戻し依頼 PAC_5-508 回覧状況に差戻し依頼を追加 引戻しは、下書き一覧に入るため、回覧状況から削除
                            $query1->where('U.circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK);
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                            // PAC_5-2375 START 差戻し依頼を選択した時は、差戻し依頼、差戻し依頼（既読）、差戻し依頼（未読）を表示
                            $query1->orWhere(function ($query2) {
                                $query2->where(function($query3) {
                                    $query3->where('U.parent_send_order',0);
                                    $query3->where('U.child_send_order',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
                                });
                                $query2->orWhere(function($query3) {
                                    $query3->where('U.child_send_order','>',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
                                });
                            })->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('circular_user as U1')
                                    ->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
                                    ->whereRaw('U.circular_id = U1.circular_id');
                            });
                            // 差戻し依頼(未読)
                            $query1->orWhere(function ($query2) {
                                $query2->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                    ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS)
                                    ->whereExists(function ($query) {
                                        $query->select(DB::raw(1))
                                            ->from('circular_user as U1')
                                            ->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
                                            ->whereRaw('U.circular_id = U1.circular_id');
                                    });
                            });
                            // PAC_5-2375 END
                        }else if($status == 11){
							// 差戻し依頼(既読)
							$query1->where(function($query2) {
								$query2->where(function($query3) {
									$query3->where('U.parent_send_order',0);
									$query3->where('U.child_send_order',0);
									$query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
								});
								$query2->orWhere(function($query3) {
									$query3->where('U.child_send_order','>',0);
									$query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
								});
							})
								->whereExists(function ($query) {
									$query->select(DB::raw(1))
										->from('circular_user as U1')
										->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
										->whereRaw('U.circular_id = U1.circular_id');
								})
								->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS]);
						}else if($status == 12){
							// 差戻し依頼(未読)
							$query1->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
								->where('C.circular_status', CircularUtils::CIRCULATING_STATUS)
								->whereExists(function ($query) {
									$query->select(DB::raw(1))
										->from('circular_user as U1')
										->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
										->whereRaw('U.circular_id = U1.circular_id');
								});
						}
                        /*PAC_5-2250 S*/
                        else if($status == 14) {
                            $query1->where('U.child_send_order', '>', 0);
                            $query1->where('U.circular_status', CircularUserUtils::NODE_COMPLETED_STATUS);
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }
                        /*PAC_5-2250 E*/
                        else if(strlen($status) > 1){
                            // support search multiple search
                            $arrStatus = str_split($status);
                        $query1->where(function($query2) use ($arrStatus){
                            foreach ($arrStatus as $s){
                                if($s == 1){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                            ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
                                    });
                                }else if($s == 2){
                                    $query2->orWhere(function($query3){
                                        $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                                            ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
                                    });
                                }else if($s == 3){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.child_send_order','>',0);
                                        $query3->where('U.circular_status', CircularUserUtils::APPROVED_WITH_STAMP_STATUS);
                                        $query3->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                                    });

                                }else if($s == 4){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.child_send_order','>',0);
                                        $query3->where('U.circular_status', CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS);
                                        $query3->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                                    });
                                }else if($s == 6){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                            ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                                    });
                                }else if($s == 5){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::READ_STATUS)
                                            ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                                    });
                                }else if($s == 7){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK);
                                        $query3->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                                    });
                                }else if($s == 8){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::PULL_BACK_TO_USER_STATUS)
                                            ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
                                    });
                                }
                            }
                        });
                    }
                });
                    });
            foreach ($orderBy as $order) {
                $data_query = $data_query->orderBy($order, $orderDir)->orderBy('U.circular_status');
            }
            $data = $data_query->paginate($limit)->appends(request()->input());

            $num_unread = DB::table('circular as C')
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->join('circular_user as U', function($join){
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('D.parent_send_order','=','U.parent_send_order');
                })
                ->join('eps_t_app as T', 'C.id', '=', 'T.circular_id')
                ->where("U.email", $user->email)
                ->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                ->count();
            if(!$data->isEmpty()){
                $listCircular_id = $data->pluck('id')->all();
                $listUserSend = DB::table('circular_user')
                    ->whereIn('circular_id', $listCircular_id)
                    ->get();

                foreach($data as $item){
                    $circularUsers = $listUserSend->filter(function ($value) use ($item){
                        return $value->circular_id == $item->id;
                    });
                    // PAC_5-634 自身のメールアドレスを宛先に追加して申請後、受信一覧で同じ文書名が連続して表示される, process file name
                    $fileNames = explode(CircularUserUtils::SEPERATOR, $item->file_names);
                    if (!trim($item->subject)){
                        $item->title = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames),0,100) : reset($fileNames);
                    }

                    $item->showBtnBack = true;
                    $item->showBtnRequestSendBack = true;
                    $item->hasRequestSendBack = false;
					$item->hasOperationNotice = false; // 閲覧ユーザー確認フラグ
                    if($circularUsers->some(function($value){ return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->hasRequestSendBack = true;
                    }

                    // PAC_5-263 閲覧ユーザーの場合、差戻し依頼ボタン表示しない
					if(!$circularUsers->some(function($value) use ($user){ return $value->email == $user->email;})) {
						$item->hasOperationNotice = true;
					}

                    // check if there is any external user or current edition user in circular
                    if($circularUsers->some(function($value){ return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null || $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    // check if there is any the working user
                    $currentCircularUser = $circularUsers->first(function ($value) use ($item) {
                        return $value->circular_id == $item->id && ($value->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS || $value->circular_status == CircularUserUtils::READ_STATUS || $value->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS|| $value->circular_status == CircularUserUtils::REVIEWING_STATUS);
                    });
                    if(!$currentCircularUser) {
                        // there is not any the working user
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    // check if the current user is working user
                    if($item->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS OR $item->circular_status == CircularUserUtils::READ_STATUS OR $item->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS OR $item->circular_status == CircularUserUtils::REVIEWING_STATUS){
                        $item->showBtnBack = false;
                    }else if($item->circular_status == CircularUserUtils::APPROVED_WITH_STAMP_STATUS || $item->circular_status == CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS ){
                        // check if the company of current user is as same as the working's company
                        $item->showBtnBack = $item->parent_send_order == $currentCircularUser->parent_send_order;
                    }
                    /*PAC_5-2250 S*/
                    if($item->circular_status == CircularUserUtils::NODE_COMPLETED_STATUS && in_array($item->status,[CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])) {
                        $item->showBtnBack = false;
                    }
                    /*PAC_5-2250 E*/
                    /*PAC_5-1698 S*/
                    if(in_array($item->circular_status , [CircularUserUtils::APPROVED_WITH_STAMP_STATUS,CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS]) && $item->plan_id > 0 && $circularUsers->some(function($circular_user) use ($item) {
                           return $circular_user->plan_id == $item->plan_id && $circular_user->circular_status == CircularUserUtils::REVIEWING_STATUS;
                    })) {
                        $item->showBtnBack = false;
                    }
                    /*PAC_5-1698 E*/
                    if(($item->parent_send_order == 0 && $item->child_send_order > 0) || ($item->parent_send_order > 0 && $item->child_send_order > 1)) {
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    if($item->parent_send_order >= $currentCircularUser->parent_send_order) {
                        $item->showBtnRequestSendBack = false;
                    }
                }
            }

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['num_unread' => $num_unread, 'data' => $data], __('message.success.data_get', ['attribute'=>'受信文書']));
    }

}
