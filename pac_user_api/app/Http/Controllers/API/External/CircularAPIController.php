<?php

namespace App\Http\Controllers\API\External;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Image;
use Response;
use Session;

class CircularAPIController extends AppBaseController
{
    public function getCircularCounts(Request $request)
    {
        $mst_company_id = $request->get('mst_company_id', 0);
        $access_id = $request->get('access_id', '');
        $access_code = $request->get('access_code', '');
        $email = $request->get('email', '');

        try {
            $auth = DB::table('api_authentication')
                ->where('api_name', 'CircularCount')
                ->where('mst_company_id', $mst_company_id)
                ->where('access_id', $access_id)
                ->where('access_code', $access_code)
                ->first();
            if (!$auth) {
                return $this->sendError('API認証失敗しました。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            if ($email) {
                $mst_user = DB::table('mst_user')
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->where('mst_company_id', $mst_company_id)
                    ->where('email', $email)
                    ->first();
                if (!$mst_user) {
                    return $this->sendError('有効利用者ではありません。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
                }
            }

            $mst_user_delete_count = DB::table('mst_user')
                ->where('state_flg', AppUtils::STATE_DELETE)
                ->where('mst_company_id', $mst_company_id)
                ->pluck('id')->toArray();

            $num_sent = DB::table('circular as C')
                ->select('M.email as email', DB::raw('count(*) as num'))
                ->join('circular_user as U', function ($join) {
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('U.parent_send_order', '=', DB::raw('0'));
                    $join->on('U.child_send_order', '=', DB::raw('0'));
                    $join->on('U.del_flg', '=', DB::raw('0'));
                })
                ->join('mst_user as M', function ($join) {
                    $join->on('M.id', '=', 'C.mst_user_id');
                    $join->on('M.state_flg', '<>', DB::raw(AppUtils::STATE_DELETE));
                })
                ->where('M.mst_company_id', $mst_company_id)
                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                ->where('C.edition_flg', config('app.edition_flg'))
                ->where('C.env_flg', config('app.server_env'))
                ->where('C.server_flg', config('app.server_flg'))
                ->where(function ($item) use ($email) {
                    if ($email) {
                        $item->where('M.email', $email);
                    }
                })
                ->groupBy('M.email')
                ->pluck('num', 'email')
                ->toArray();

            $num_saved = DB::table('circular as C')
                ->select('M.email as email', DB::raw('count(*) as num'))
                ->join('mst_user as M', function ($join) {
                    $join->on('M.id', '=', 'C.mst_user_id');
                    $join->on('M.state_flg', '<>', DB::raw(AppUtils::STATE_DELETE));
                })
                ->where('M.mst_company_id', $mst_company_id)
                ->whereIn('C.circular_status', [CircularUtils::SAVING_STATUS, CircularUtils::RETRACTION_STATUS])
                ->where('C.edition_flg', config('app.edition_flg'))
                ->where('C.env_flg', config('app.server_env'))
                ->where('C.server_flg', config('app.server_flg'))
                ->where(function ($item) use ($email) {
                    if ($email) {
                        $item->where('M.email', $email);
                    }
                })
                ->groupBy('M.email')
                ->pluck('num', 'email')
                ->toArray();
            $data_query = DB::table('circular as C')
                ->select('C.id as circular_id', 'U.id AS circular_user_id', 'M.email AS email', 'C.circular_status AS circular_status',
                    'U.circular_status AS circular_user_status')
                ->distinct()
                ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
                ->join('circular_document as D', function ($join) use ($mst_company_id) {
                    $join->on('C.id', '=', 'D.circular_id');
                    $join->on(function ($condition) use ($mst_company_id) {
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function ($condition1) use ($mst_company_id) {
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('origin_edition_flg', DB::raw(config('app.edition_flg')));
                            $condition1->on('origin_env_flg', DB::raw(config('app.server_env')));
                            $condition1->on('origin_server_flg', DB::raw(config('app.server_flg')));
                            $condition1->on('create_company_id', DB::raw($mst_company_id));
                        });
                    });
                    $join->on(function ($condition) {
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn(function ($condition1) {
                            $condition1->on('D.parent_send_order', 'U.parent_send_order');
                        });
                    });
                })
                ->join('mst_user as M', 'M.email', 'U.email')
                ->where(function ($query1) use ($mst_company_id, $mst_user_delete_count) {
                    $query1->where(function ($query2) {
                        $query2->where(function ($query3) {
                            $query3->where('U.parent_send_order', 0);
                            $query3->where('U.child_send_order', 0);
                            $query3->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS,
                                CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS]);
                        });
                        $query2->orWhere(function ($query3) {
                            $query3->where('U.child_send_order', '>', 0);
                            $query3->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS,
                                CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                                CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS]);
                        });
                    });
                    $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                    $query1->where('U.edition_flg', config('app.edition_flg'));
                    $query1->where('U.env_flg', config('app.server_env'));
                    $query1->where('U.server_flg', config('app.server_flg'));
                    $query1->whereNotIn('U.mst_user_id', $mst_user_delete_count);
                });


            $sub_query = DB::table(DB::raw("({$data_query->toSql()}) as sub"))
                ->mergeBindings($data_query)
                ->groupBy('sub.email')
                ->select(DB::raw('sub.email, SUM(sub.circular_status = ' . CircularUtils::SEND_BACK_STATUS . ' AND sub.circular_user_status = ' . CircularUserUtils::READ_STATUS . ' ) as num_return,
                                SUM(sub.circular_status = ' . CircularUtils::SEND_BACK_STATUS . ' AND sub.circular_user_status = ' . CircularUserUtils::NOTIFIED_UNREAD_STATUS . ') as num_unread_return,
                                SUM(sub.circular_status = ' . CircularUtils::CIRCULATING_STATUS . ' AND sub.circular_user_status = ' . CircularUserUtils::NOTIFIED_UNREAD_STATUS . ') as num_unread,
                                SUM(sub.circular_status = ' . CircularUtils::CIRCULATING_STATUS . ' AND (sub.circular_user_status = ' . CircularUserUtils::READ_STATUS . ' OR
                                                                                                   sub.circular_user_status = ' . CircularUserUtils::SUBMIT_REQUEST_SEND_BACK . '  OR
                                                                                                   sub.circular_user_status = ' . CircularUserUtils::REVIEWING_STATUS . '  OR
                                                                                                   sub.circular_user_status = ' . CircularUserUtils::PULL_BACK_TO_USER_STATUS . ')) as num_read,
        	                COUNT(sub.circular_id) as num_untreated'))
                ->get();
            $mst_user_valid = DB::table('mst_user')
                ->where('state_flg', AppUtils::STATE_VALID)
                ->whereIn('option_flg', [AppUtils::USER_NORMAL, AppUtils::USER_RECEIVE])
                ->where('mst_company_id', $mst_company_id)
                ->where(function ($item) use ($email) {
                    if ($email) {
                        $item->where('email', $email);
                    }
                })
                ->pluck('email')->toArray();
            $return = array();
            foreach ($mst_user_valid as $mst_user) {
                $data = array();
                $item = $sub_query->where('email', $mst_user);
                $data['email'] = $mst_user;
                $data['num_received'] = count($item) ? current($item->toArray())->num_untreated : 0; //受信
                $data['num_sent'] = isset($num_sent[$mst_user]) ? $num_sent[$mst_user] : 0; //申請
                $data['num_untreated'] = count($item) ? current($item->toArray())->num_unread + current($item->toArray())->num_read + current($item->toArray())->num_return + current($item->toArray())->num_unread_return : 0; //未処理
                $data['num_send_back'] = count($item) ? current($item->toArray())->num_return + current($item->toArray())->num_unread_return : 0; //差戻
                $data['num_saved'] = isset($num_saved[$mst_user]) ? $num_saved[$mst_user] : 0; //下書き
                $return[] = $data;
            }


            return $this->sendResponse($return, '');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
