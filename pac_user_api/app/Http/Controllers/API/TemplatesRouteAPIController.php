<?php

namespace App\Http\Controllers\API;

use App\Http\Utils\AppUtils;
use App\Http\Utils\DepartmentUtils;
use App\Models\CircularUserTemplates;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Response;
use App\Http\Utils\TemplateRouteUtils;

/**
 * Class FavoriteAPIController
 * @package App\Http\Controllers\API
 */
class TemplatesRouteAPIController extends AppBaseController
{
    var $model = null;

    public function __construct(CircularUserTemplates $templates)
    {
        $this->model = $templates;
    }

    /**
     * Display a listing of the Templates.
     * GET /templates
     *
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || !$user->id) {
                $user = $request['user'];
            }
            // 検索名称
            $templateRouteName = $request->get('templateRouteName');
            $withMe = $request['template_route_flg'];
            // PAC_5-2098 Start
            $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;
            // PAC_5-2098 End
            $arrTemplates = $this->model->join('circular_user_template_routes', 'circular_user_templates.id', '=', 'circular_user_template_routes.template')
                ->join('mst_position', function ($query) {
                    $query->on('circular_user_template_routes.mst_position_id', '=', 'mst_position.id');
                    $query->where('mst_position.state', 1);
                })
                ->join('mst_department', function ($query) {
                    $query->on('circular_user_template_routes.mst_department_id', '=', 'mst_department.id');
                    $query->where('mst_department.state', 1);
                })
                ->leftjoin('mst_user_info', function ($query) use ($multiple_department_position_flg){
                    // PAC_5-2098 Start
                    if ($multiple_department_position_flg === 1) {
                        // PAC_5-1599 追加部署と役職 Start
                        $query->on(function($query) {
                            $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id')
                                ->on('mst_department.id', '=', 'mst_user_info.mst_department_id');
                        })->orOn(function($query) {
                            $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id_1')
                                ->on('mst_department.id', '=', 'mst_user_info.mst_department_id_1');
                        })->orOn(function($query) {
                            $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id_2')
                                ->on('mst_department.id', '=', 'mst_user_info.mst_department_id_2');
                        });
                        // PAC_5-1599 End
                    } else {
                        $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id')
                              ->on('mst_department.id', '=', 'mst_user_info.mst_department_id');
                    }
                    // PAC_5-2098 End
                })
                ->join('mst_user', function ($query) use ($user){
                    $query->on('mst_user_info.mst_user_id', '=', 'mst_user.id');
                    $query->where('mst_user.state_flg', 1);
                    $query->where('mst_user.mst_company_id', $user->mst_company_id);
                })
                ->where('circular_user_templates.mst_company_id', $user->mst_company_id)
                ->where('circular_user_templates.state', AppUtils::TEMPLATE_VALID);

            // 名称が入力時
            if ($templateRouteName) {
                $arrTemplates = $arrTemplates->whereRaw("circular_user_templates.name like '%$templateRouteName%'");
            }

            $arrTemplates = $arrTemplates->select(DB::raw('circular_user_templates.id, circular_user_template_routes.id route_id, circular_user_templates.name, mst_position.position_name,
                mst_department.department_name,mst_department.id as department_id,circular_user_template_routes.child_send_order,
                circular_user_template_routes.mode, circular_user_template_routes.option, circular_user_template_routes.wait,
                    mst_user.family_name, mst_user.given_name, mst_user.id as user_id, mst_user.email, mst_user_info.id as user_info_id'))
                ->orderBy('circular_user_templates.id', 'asc')
                ->orderBy('circular_user_template_routes.child_send_order', 'asc')
                ->get();

            $arrTemplateInfo = [];
            foreach ($arrTemplates as $template) {
                // template info
                if (!key_exists($template->id, $arrTemplateInfo)) {
                    $arrTemplateInfo[$template->id]["name"] = $template->name;
                    $arrTemplateInfo[$template->id]["template_id"] = $template->id;
                    $arrTemplateInfo[$template->id]["template_rotes"] = [];
                    $arrTemplateInfo[$template->id]["last_route_id"] = 0;
                }
                // template route
                if (!key_exists($template->route_id, $arrTemplateInfo[$template->id]["template_rotes"])) {
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["position_name"] = $template->position_name;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["department_name"] = $template->department_name;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["department_id"] = $template->department_id;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["mode"] = $template->mode;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["option"] = $template->option;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["wait"] = $template->wait;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["route_id"] = $template->route_id;
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["users"] = [];
                    //$arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["template_valid"] = false;
                }
                // template route user
                if ($template->user_info_id) {
                    $arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["users"][] = ["id"=>$template->user_id,"family_name" => $template->family_name, "given_name" => $template->given_name, "email" => $template->email];
                    //$arrTemplateInfo[$template->id]["template_rotes"][$template->route_id]["template_valid"] = true;
                }
            }
            $related_me_flg = true;

            if ($withMe) {
                // PAC_5-1599 追加部署と役職 Start
                $user_department_ids = DB::table('mst_user_info')->where('mst_user_id', $user->id)->first(['mst_department_id','mst_department_id_1','mst_department_id_2']);
                // PAC_5-1599 End
                $mst_department_ids = DB::table('mst_department')->where('mst_company_id', $user->mst_company_id)->where('state', AppUtils::DEPARTMENT_STATE_VALID)->select('id','parent_id')->get();
                $related_ids = [];

                // PAC_5-1599 追加部署と役職 Start
                $search_id = $user_department_ids->mst_department_id;
                // PAC_5-2098 Start
                $related_ids[] = $user_department_ids->mst_department_id;
                
                if ($multiple_department_position_flg === 1) {
                    $search_id_1 = $user_department_ids->mst_department_id_1;
                    $search_id_2 = $user_department_ids->mst_department_id_2;
                    
                    $related_ids[] = $user_department_ids->mst_department_id_1;
                    $related_ids[] = $user_department_ids->mst_department_id_2;
                }
                // PAC_5-2098 End
                // PAC_5-1599 End
                $array = $mst_department_ids->toArray();

                while (true) {
                    $value = array_filter($array,function($row)use($search_id) {
                                            return $row->id === $search_id;
                                            });
                    $value = array_values($value);
                    if(!count($value) || $value[0]->parent_id === 0){
                        break;
                    }else{
                        $related_ids[] = $value[0]->parent_id;
                        $search_id = $value[0]->parent_id;
                    }
                }
                // PAC_5-2098 Start
                if ($multiple_department_position_flg === 1) {
                    // PAC_5-1599 追加部署と役職 Start
                    while (true) {
                        $value = array_filter($array, function ($row) use ($search_id_1) {
                            return $row->id === $search_id_1;
                        });
                        $value = array_values($value);
                        if (!count($value) || $value[0]->parent_id === 0) {
                            break;
                        } else {
                            $related_ids[] = $value[0]->parent_id;
                            $search_id_1 = $value[0]->parent_id;
                        }
                    }
    
                    while (true) {
                        $value = array_filter($array, function ($row) use ($search_id_2) {
                            return $row->id === $search_id_2;
                        });
                        $value = array_values($value);
                        if (!count($value) || $value[0]->parent_id === 0) {
                            break;
                        } else {
                            $related_ids[] = $value[0]->parent_id;
                            $search_id_2 = $value[0]->parent_id;
                        }
                    }
                    // PAC_5-1599 End
                }
                // PAC_5-2098 End
            }
            
            // 下付き変換 $template->route_id => 0,1,...
            foreach ($arrTemplateInfo as $key => $templateInfo) {
                $template_rotes = [];
                $template_valid = false;
                if ($withMe) {
                    $related_me_flg = false;
                }
                foreach ($templateInfo["template_rotes"] as $template_rote) {
                    // 設定有効無効
                    $template_rote["template_route_valid"] = false;
                    if ($template_rote["mode"] == TemplateRouteUtils::TEMPLATE_MODE_ALL_MUST) {
                        if (count($template_rote["users"]) > 0) {
                            $template_rote["template_route_valid"] = true;
                            $template_valid = true;
                        } else {
                            $template_rote["template_route_valid"] = false;
                        }
                    } else if ($template_rote["mode"] == TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN) {
                        if (count($template_rote["users"]) >= $template_rote["option"]) {
                            $template_rote["template_route_valid"] = true;
                            $template_valid = true;
                        } else {
                            $template_rote["template_route_valid"] = false;
                        }
                    }
                    $template_rotes[] = $template_rote;
                    // 自分関係承認ルート表示のみ
                    if ($withMe) {
                        if (in_array($template_rote["department_id"],$related_ids)) {
                            $related_me_flg = true;
                        }
                    }
                }
                if (!$related_me_flg) {
                    unset($arrTemplateInfo[$key]);
                    continue;
                }
                $arrTemplateInfo[$key]["template_rotes"] = $template_rotes;
                $arrTemplateInfo[$key]["template_valid"] = $template_valid;
            }
            return $this->sendResponse($arrTemplateInfo, 'Template list get successfully');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('テンプレートルートの取得が失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
        
    /**
     * 承認ルート一覧
     *
     * Display a listing of the resource.
     * @param Request $request
     * @return Response
     */
    public function getTemplateRouteList(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || !$user->id) {
                $user = $request['user'];
            }
            // 全て部署
            $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
            $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
            // 全て役職
            $listPosition = DB::table('mst_position')
                ->select('id', 'position_name as text', 'position_name as sort_name')
                ->where('state', 1)
                ->where('mst_company_id', $user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();

            // ソート条件
            $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderDir   =  AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
            $query = [];

            // ルートに情報を取得
            $query_route = DB::table('circular_user_template_routes as r')
                ->select(DB::raw('r.template, GROUP_CONCAT(r.mst_department_id ORDER BY r.child_send_order ASC SEPARATOR \';\') as dep_pos_name,
                 GROUP_CONCAT(r.mst_position_id ORDER BY r.child_send_order ASC SEPARATOR \';\') as mst_position_id,
                 GROUP_CONCAT(r.mode ORDER BY r.child_send_order ASC SEPARATOR \';\') as mode,
                 GROUP_CONCAT(r.option ORDER BY r.child_send_order ASC SEPARATOR \';\') as options'));
            $query_route = $query_route->groupBy('r.template');

            $query = DB::table('circular_user_templates as T')
                ->select(['T.id', 'T.name', 'T.state', 'T.update_at', 'R.mode', 'R.options', 'R.dep_pos_name', 'R.mst_position_id'])
                ->joinSub($query_route, 'R', function ($join) {
                    $join->on('R.template', '=', 'T.id');
                });
            // 名前によるファジークエリ
            $templateRouteName = $request->get('templateRouteName');
            if ($templateRouteName) {
                $query = $query->where('T.name', 'like', '%' . $templateRouteName . '%');
            }
            $routeId = $request->get('routeId');
            if(!empty($routeId)){
                $query = $query->where('T.id', '=', $routeId);
                $query = $query->where('T.mst_company_id', $user->mst_company_id)
                    ->where('T.state', '!=', TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES)
                    ->get();
            }else{
                // 有効な検索
                $query = $query->where('T.state', 1);
                $query = $query->where('T.mst_company_id', $user->mst_company_id)
                    ->where('T.state', '!=', TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES)
                    ->orderBy('T.name', $orderDir)->paginate($limit)->appends(request()->input());
            }

            // 「回覧先」と「合議設定」を設定
            foreach ($query as $item) {
                $dep_pos_name = '';
                $mode_option = '';

                $deps = explode(';', $item->dep_pos_name);
                $poss = explode(';', $item->mst_position_id);

                // 「回覧先」設定
                $index = 1;
                foreach ($deps as $key => $dep) {
                    $dep_name = '';
                    $pos_name = '';
                    // 部署名設定
                    foreach ($listDepartmentDetail as $departmentDetail) {
                        if ((int)$dep == $departmentDetail['id']) {
                            $dep_name = $departmentDetail['text'];
                            break;
                        }
                    }
                    // 役職名設定
                    foreach ($listPosition as $position) {
                        if ((int)$poss[$key] == $position->id) {
                            $pos_name = $position->text;
                            break;
                        }
                    }
                    $dep_pos_name .= '<div class="dep-pos-label"><label>#' . $index . ' ' . $dep_name . '</label><span>' . $pos_name . '</span></div>';
                    $index++;
                }
                $item->dep_pos_name = $dep_pos_name;

                //「合議設定」を設定
                $modes = explode(';', $item->mode);
                $options = explode(';', $item->options);
                foreach ($modes as $key => $mode) {
                    if (!strcasecmp($mode, '1')) {
                        $mode_option .= '全員必須<br/>';
                    } else {
                        $mode_option .= '人員指定　' . $options[$key] . '人<br/>';
                    }
                }
                $item->modes = $mode_option;
            }
            return $this->sendResponse($query, 'Template list get successfully');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('テンプレートルートの取得が失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
