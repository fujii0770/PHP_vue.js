<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils; // PAC_5-2133
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\Department;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\TemplateRouteUtils;
use App\Jobs\ImportTemplateRoutes; // PAC_5-2133

/**
 * 承認ルート
 *
 * Class TemplateRouteController
 * @package App\Http\Controllers\Admin
 */
class TemplateRouteController extends AdminController
{
    private $department;
    private $position;


    public function __construct(Department $department, Position $position)
    {
        parent::__construct();
        $this->department = $department;
        $this->position = $position;
    }

    /**
     * 承認ルート一覧画面初期化
     *
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $user = \Auth::user();
            $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
            if (!array_search($limit, config('app.page_list_limit'))) {
                $limit = config('app.page_limit');
            }

            // 全て部署
            $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
            $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
            // 全て役職
            $listPosition = $this->position
                ->select('id', 'position_name as text', 'position_name as sort_name')
                ->where('state', 1)
                ->where('mst_company_id', $user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();

            // ソート条件
            $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
            $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
            $query = [];

            // ルートに情報を取得
            $query_route = DB::table('circular_user_template_routes as r')
                ->select(DB::raw('r.template, GROUP_CONCAT(r.mst_department_id ORDER BY r.child_send_order ASC SEPARATOR \';\') as dep_pos_name,
                 GROUP_CONCAT(r.mst_position_id ORDER BY r.child_send_order ASC SEPARATOR \';\') as mst_position_id,
                 GROUP_CONCAT(r.mode ORDER BY r.child_send_order ASC SEPARATOR \';\') as mode,
                 GROUP_CONCAT(r.option ORDER BY r.child_send_order ASC SEPARATOR \';\') as options'));
            if ($request->department) {
                $query_route = $query_route->whereRaw("
                    (SELECT COUNT(id) FROM circular_user_template_routes AS cutr1 WHERE cutr1.mst_department_id = ? AND cutr1.template = r.template ) >= 1",[$request->department]
                );
            }
            if ($request->position) {
                $query_route = $query_route->whereRaw("
                    (SELECT COUNT(id) FROM circular_user_template_routes AS cutr2 WHERE cutr2.mst_position_id = ?  AND cutr2.template = r.template ) >= 1",[$request->position]
                );
            }
            $query_route = $query_route->groupBy('r.template');

            $query = DB::table('circular_user_templates as T')
                ->select(['T.id', 'T.name', 'T.state', 'T.update_at', 'R.mode', 'R.options', 'R.dep_pos_name', 'R.mst_position_id'])
                ->joinSub($query_route, 'R',function($join){
                    $join->on('R.template', '=', 'T.id');
                });
            // 名前によるファジークエリ
            if ($request->name) {
                $query = $query->where('T.name', 'like', '%' . $request->name . '%');
            }
            // 有効な検索
            if ($request->onlyUnsigned) {
                $query = $query->where('T.state', 1);
            }
            $query = $query->where('T.mst_company_id', $user->mst_company_id)
                ->where('T.state', '!=', TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES)
                ->orderBy('T.' . $orderBy, $orderDir)->paginate($limit)->appends(request()->input());

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
                        if ((int)$poss[$key] == $position['id']) {
                            $pos_name = $position['text'];
                            break;
                        }
                    }
                    $dep_pos_name .= '<div class="dep-pos-label"><label>#' . $index . ' ' . $dep_name . '</label>' . $pos_name . '</div>';
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
                $item->state = $item->state ? '有効' : '無効';
            }

            $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";
            $total = count($query);

            $this->assign('query', $query);
            $this->assign('limit', $limit);
            $this->assign('orderBy', $orderBy);
            $this->assign('orderDir', $orderDir);
            $this->assign('total', $total);
            $this->assign('onlyUnsigned', $request->onlyUnsigned);
            $this->assign('listDepartmentTree', $listDepartmentTree);
            $this->assign('listDepartmentDetail', $listDepartmentDetail);
            $this->assign('listPosition', $listPosition);
            $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_TEMPLATE_ROUTE_CREATE));
            $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_TEMPLATE_ROUTE_UPDATE));
            $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
            $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
            $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
            $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
            $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
            $this->addScript('select2-init', '$(\'.select-2\').select2();', false);
            $this->setMetaTitle("承認ルート");

            return $this->render('TemplateRoute.index');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 更新
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = \Auth::user();
            $item = $request->get('item');
            $deleteIds = $request->get('deleteIds');
            $routes = $item['routes'];
            //人数チェック
            $checkResult = $this->checkPositionPeople($user->mst_company_id, $routes);
            if (!$checkResult->getdata()->status){
                return response()->json(['status' => false, 'message' => $checkResult->getdata()->message]);
            }
            // 内容チェック
            $result = $this->dataCheck($routes);
            if (!$result->getdata()->status) {
                return response()->json(['status' => false, 'message' => $result->getdata()->message]);
            }

            DB::table('circular_user_templates')
                ->where('id', $id)
                ->update([
                    'name' => $item['name'],
                    'state' => isset($item['state']) ? $item['state'] : 0,
                    'update_at' => Carbon::now(),
                    'update_user' => $user->getFullName(),
                ]);

            $child_send_order = 1;
            foreach ($routes as $route) {
                // 操作データ
                $date = ['mst_position_id' => $route['mst_position_id'],
                    'mst_department_id' => $route['mst_department_id'],
                    'child_send_order' => $child_send_order,
                    'mode' => $route['mode'],
                    'option' => strcasecmp($route['mode'], '1') ? $route['option'] : 0,
                    'wait' => TemplateRouteUtils::MODE_WAIT[$route['mode']]];

                // 更新
                if (isset($route['id'])) {
                    $date['update_at'] = Carbon::now();
                    $date['update_user'] = $user->getFullName();
                    DB::table('circular_user_template_routes')
                        ->where('id', $route['id'])
                        ->update($date);
                } else {// 新規
                    $date['template'] = $id;
                    $date['create_at'] = Carbon::now();
                    $date['create_user'] = $user->getFullName();
                    DB::table('circular_user_template_routes')
                        ->insert($date);
                }
                $child_send_order++;
            }
            // 削除処理
            DB::table('circular_user_template_routes')
                ->whereIn('id', $deleteIds)
                ->delete();
            DB::commit();
            return response()->json(['item' => $this->getData($id), 'status' => true, 'message' => [__('message.success.template_route.update_success')]]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.template_route.update_error'), $ex->getMessage()]]);
        }
    }

    /**
     * 新規
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = \Auth::user();
            $item = $request->get('item');
            $routes = $item['routes'];
            //人数チェック
            $checkResult = $this->checkPositionPeople($user->mst_company_id, $routes);
            if (!$checkResult->getdata()->status){
                return response()->json(['status' => false, 'message' => $checkResult->getdata()->message]);
            }
            // 内容チェック
            $result = $this->dataCheck($routes);
            if (!$result->getdata()->status) {
                return response()->json(['status' => false, 'message' => $result->getdata()->message]);
            }

            $template_id = DB::table('circular_user_templates')->insertGetId([
                'mst_company_id' => $user->mst_company_id,
                'name' => $item['name'],
                'state' => isset($item['state']) ? $item['state'] : 0,
                'create_at' => Carbon::now(),
                'create_user' => $user->getFullName(),
                'update_at' => Carbon::now(),
                'update_user' => $user->getFullName(),
            ]);

            $dates = [];
            $child_send_order = 1;
            foreach ($routes as $route) {
                // 人数設定
                if (!strcasecmp($route['mode'], '1')) {
                    $option = 0;
                } else {
                    $option = $route['option'];
                }
                $date = [
                    'template' => $template_id,
                    'mst_position_id' => $route['mst_position_id'],
                    'mst_department_id' => $route['mst_department_id'],
                    'child_send_order' => $child_send_order,
                    'mode' => $route['mode'],
                    'option' => $option,
                    'wait' => TemplateRouteUtils::MODE_WAIT[$route['mode']],
                    'create_at' => Carbon::now(),
                    'create_user' => $user->getFullName(),
                ];
                $child_send_order++;
                $dates[] = $date;
            }
            DB::table('circular_user_template_routes')->insert($dates);
            DB::commit();
            return response()->json(['item' => $this->getData($template_id), 'status' => true, 'message' => [__('message.success.template_route.create_success')]]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.template_route.create_error')]]);
        }
    }

    /**
     * 詳細表示
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            return response()->json(['status' => true, 'item' => $this->getData($id)]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.template_route.show')]]);
        }
    }

    /**
     * 削除
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletes(Request $request)
    {
        try {
            // 承認ルートID取得
            $tids = $request->get('tids');
            if ($tids) {
                DB::table('circular_user_templates')
                    ->whereIn('id', $tids)
                    ->update([
                        'state' => TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES,
                        'update_at' => Carbon::now(),
                        'update_user' => \Auth::user()->getFullName(),
                    ]);
            }
            return response()->json(['status' => true]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.template_route.deletes')]]);
        }
    }

    /**
     * 内容チェック
     *
     * @param $routes
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    private function dataCheck($routes) {
        try {
            // 内容チェック
            foreach ($routes as $route) {
                // 部署選択しないの場合
                if (!isset($route['mst_department_id']) || !$route['mst_department_id']) {
                    return response()->json(['status' => false, 'message' => [__('message.false.template_route.must_select',['attribute' => '部署'])]]);
                }
                // 役職選択しないの場合
                if (!isset($route['mst_position_id']) || !$route['mst_position_id']) {
                    return response()->json(['status' => false, 'message' => [__('message.false.template_route.must_select',['attribute' => '役職'])]]);
                }
                // 合議選択しないの場合
                if (!isset($route['mode']) || !$route['mode']) {
                    return response()->json(['status' => false, 'message' => [__('message.false.template_route.must_select',['attribute' => '合議'])]]);
                } else {
                    // 合議が人数指定、人数設定しないの場合
                    if ($route['mode'] === 3 && !isset($route['option'])) {
                        return response()->json(['status' => false, 'message' => [__('message.false.template_route.must_input',['attribute' => '人数'])]]);
                    }
                }
            }
            return response()->json(['status' => true, 'message' => []]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            throw new \Exception(__('message.false.template_route.data_check_error'));
        }
    }


    /**
     * データ情報を取得(idに従って)
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    private function getData($id) {
        try {
            // 承認ルートを取得
            $item = DB::table('circular_user_templates')
                ->where('id', $id)
                ->first();
            // 承認ルート存在しない
            if (!$item) {
                return response()->json(['status' => false, 'message' => [__('message.false.template_route.no_data')]]);
            }

            // 承認ルート情報詳細を取得
            $routes = DB::table('circular_user_template_routes as t')
                ->select(DB::raw('t.id, t.template, t.mst_position_id, t.mst_department_id, t.child_send_order, t.mode, IF(t.option = 0, \'\', t.option ) as `option`, t.wait'))
                ->where('t.template', $id)
                ->orderBy('t.child_send_order', 'asc')
                ->get()
                ->toArray();
            $item->routes = $routes;
            return $item;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            throw new \Exception(__('message.false.template_route.data_get_error'));
        }
    }

    /**
     * 部署と役職の指定人数をチェックする
     * @param $companyId
     * @param $routes
     * @return \Illuminate\Http\JsonResponse
     */
    private function checkPositionPeople($companyId, $routes){
        $result['status'] = true;
        // PAC_5-2098 Start
        $multiple_department_position_flg = DB::table('mst_company')->where('id', $companyId)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;
        // PAC_5-2098 End
        foreach ($routes as $key => $route){
            // PAC_5-2098 Start
            $query = DB::table('mst_user as U')
                ->join('mst_user_info as UI','UI.mst_user_id','U.id')
                ->where('U.mst_company_id',$companyId)
                ->whereIn('U.option_flg',[AppUtils::USER_NORMAL,AppUtils::USER_RECEIVE])
                ->where('U.state_flg',AppUtils::STATE_VALID);
            if ($multiple_department_position_flg === 1) {
                // PAC_5-1599 追加部署と役職 Start
                $userNum = $query->where(function($query) use ($route) {
                    $query->orWhere('UI.mst_department_id', $route['mst_department_id'])
                        ->orWhere('UI.mst_department_id_1', $route['mst_department_id'])
                        ->orWhere('UI.mst_department_id_2', $route['mst_department_id']);
                })
                    ->where(function($query) use ($route) {
                        $query->orWhere('UI.mst_position_id', $route['mst_position_id'])
                            ->orWhere('UI.mst_position_id_1', $route['mst_position_id'])
                            ->orWhere('UI.mst_position_id_2', $route['mst_position_id']);
                    })
                // PAC_5-1599 End
                ->count();
            } else {
                $userNum = $query->where('UI.mst_department_id', $route['mst_department_id'])
                    ->where('UI.mst_position_id', $route['mst_position_id'])
                    ->count();
            }
            // PAC_5-2098 End
            if ($route['mode'] == TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN){
                if ($route['option'] > $userNum){
                    $result['status'] =  false;
                    $result['key'] = $key + 1;
                    $result['option'] = $route['option'];
                } else if ($route['option'] == 0) {
                    $result['status'] =  false;
                    $result['key'] = $key + 1;
                    $result['option'] = $route['option'];
                }
            }else{
                if (!$userNum){
                    $result['status'] =  false;
                    $result['key'] = $key + 1;
                    $result['option'] = 1;
                }
            }
        }
        if ($result['status']){
            return response()->json(['status' => true, 'message' => []]);
        }else{
            // PAC_5-2235 承認ルートに人数指定チェック時に人数0人で登録できないようにする
            if (isset($result['option']) && $result['option'] == 0) {
                return response()->json(['status' => false, 'message' => [__('message.false.template_route.check_position_people_less',['key' => $result['key']])]]);
            } else {
                return response()->json(['status' => false, 'message' => [__('message.false.template_route.check_position_people_more',['key' => $result['key'],'option' => $result['option']])]]);
            }
        }
    }
    
    /**
     * PAC_5-2133 CSV取込
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        try {
            $user = \Auth::user();
            // 待機中レーコド確認
            $item = DB::table('csv_import_list')
                ->where('company_id', $user->mst_company_id)
                ->where('result', 2)
                ->where('import_type', AppUtils::STATE_IMPORT_CSV_TEMPLATE_ROTE)
                ->first();
            if ($item) {
                return response()->json(['status' => false, 'message' => '現在、CSV取込を行っております。しばらくお待ちください']);
            }
            if (!$request->hasFile('file')) {
                return response()->json(['status' => false, 'message' => 'CSV取込失敗しました。時間をおいて再度お試しください。']);
            }
            
            // ファイル保存
            $file = $request->file('file');
            $file_path = storage_path('import_csv/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $user->mst_company_id . $user->id . time());
            if (!is_dir($file_path)) {
                if (!mkdir($file_path, 0755, true) && !is_dir($file_path)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $file_path));
                }
            }
            copy($file, $file_path . '/' . $file->getClientOriginalName());
            $path = $file_path . '/' . $file->getClientOriginalName();
            
            // data取得
            $csv_data = array_map('str_getcsv', file($path)); // doc csv
            $str = file_get_contents($file);
            $code = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'SJIS'));
            if ($code == 'CP936' || $code == 'SJIS' || $code == 'SJIS-win') {
                $csv_data = CommonUtils::convertCode('SJIS-win', 'UTF-8', $csv_data);
            }
            
            // csv取込履歴追加
            $id = DB::table('csv_import_list')->insertGetId([
                'company_id' => $user->mst_company_id,
                'user_id' => $user->id,
                'name' => $file->getClientOriginalName(),
                'success_num' => 0,
                'failed_num' => 0,
                'total_num' => 0,
                'result' => 2,
                'create_at' => Carbon::now(),
                'file_path' => $path,
                'file_data' => json_encode($csv_data),
                'import_type' => AppUtils::STATE_IMPORT_CSV_TEMPLATE_ROTE,
            ]);
            $this->dispatch(new ImportTemplateRoutes($id));
            return response()->json(['status' => true, 'message' => 'CSV取込を受付しました。']);
        } catch (\Exception $e) {
            Log::channel('import-csv-daily')->error($e->getMessage() . $e->getTraceAsString());
            return response()->json(['status' => false, 'message' => 'CSV取込失敗しました。時間をおいて再度お試しください。']);
        }
    }

    public function getRouteInfo($routeId,$templateId,Request $request)
    {
        try {
            $user = \Auth::user();
            if(!$templateId || !$routeId){
                return response()->json(['status' => false, 'message' => 'テンプレートの承認ルート取得に失敗しました。']);
            }
            // 全て部署
            $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
            $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
            // 全て役職
            $listPosition = $this->position
                ->select('id', 'position_name as text', 'position_name as sort_name')
                ->where('state', 1)
                ->where('mst_company_id', $user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();

            $item = [];

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
                })
                ->join('template_file as TF', 'TF.template_route_id', '=', 'T.id');
            // 有効な検索
            $query = $query->where('T.state', 1);
            $item = $query->where('T.mst_company_id', $user->mst_company_id)
                ->where('T.state', '!=', TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES)
                ->where('T.id', '=', $routeId)
                ->where('TF.id', '=', $templateId)->first();

            // 「回覧先」と「合議設定」を設定
            $dep_pos_name = '';
            $mode_option = '';
            $deps = empty($item->dep_pos_name) ? [] : explode(';', $item->dep_pos_name);
            $poss = empty($item->mst_position_id) ? [] : explode(';', $item->mst_position_id);

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
                    if ((int)$poss[$key] == $position['id']) {
                        $pos_name = $position['text'];
                        break;
                    }
                }
                $dep_pos_name .= '<div class="dep-pos-label"><label>#' . $index . ' ' . $dep_name . '</label><span>' . $pos_name . '</span></div>';
                $index++;
            }
            $item->dep_pos_name = $dep_pos_name;

            //「合議設定」を設定
            $modes = empty($item->mode) ? [] : explode(';', $item->mode);
            $options = empty($item->options) ? [] : explode(';', $item->options);
            foreach ($modes as $key => $mode) {
                if (!strcasecmp($mode, '1')) {
                    $mode_option .= '全員必須<br/>';
                } else {
                    $mode_option .= '人員指定　' . $options[$key] . '人<br/>';
                }
            }
            $item->modes = $mode_option;
            $item->state = $item->state ? '有効' : '無効';
            return response()->json(['status' => true, 'item' => $item]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => '承認ルートの取得に失敗しました。(無効にされた可能性があります)']);
        }
    }
}
