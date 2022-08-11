<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ExpenseController extends AdminController
{

    private $department;
    private $position;
    private $model_user;

    public function __construct(Department $department, Position $position, User $model_user)
    {
        parent::__construct();
        $this->department = $department;
        $this->position = $position;
        $this->model_user = $model_user;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $action = $request->get('action', '');
        $arrHistory = null;

        // get list user
        $limit = AppUtils::normalizeLimit($request->get('limit'), config('app.page_limit'));
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : '';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $arrOrder = ['user' => 'user_name', 'email' => 'U.email', 'expense_flg' => 'U.expense_flg',
            'adminDepartment' => 'D.department_name', 'position' => 'P.position_name',
        ];

        $filter_email = $request->get('email', '');
        $filter_user = $request->get('username', '');

        //部門リストの取得
        $listDepartment = $this->department
            ->select('id', 'parent_id', 'department_name as name')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('state', 1)
            ->get()->keyBy('id');

        $listDepartmentTree = \App\Http\Utils\CommonUtils::arrToTree($listDepartment);

        $listDepartmentTree = \App\Http\Utils\CommonUtils::treeToArr($listDepartmentTree);

        //役職リストの取得
        $listPosition = $this->position
            ->where('state', 1)
            ->where('mst_company_id', $user->mst_company_id)
            ->pluck('position_name', 'id')->toArray();

        $arrHistory = DB::table('mst_user as U')
            ->orderBy(isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'U.id', $orderDir)
            ->leftJoin('mst_user_info as I', 'U.id', 'I.mst_user_id')
            ->leftJoin('mst_department as D', 'I.mst_department_id', 'D.id')
            ->leftJoin('mst_position as P', 'I.mst_position_id', 'P.id')
            ->select(DB::raw('U.id, U.expense_flg, CONCAT(U.family_name, U.given_name) as user_name,U.email, mst_department_id,D.department_name, mst_position_id, P.position_name'))
            ->where('U.mst_company_id', $user->mst_company_id)
            ->where('U.state_flg',AppUtils::STATE_VALID);

        $where = ['1=1'];
        $where_arg = [];

        if($filter_email) {
            $where[] = 'INSTR(U.email, ?)';
            $where_arg[] = $filter_email;
        }
        if($filter_user) {
            $where[] = 'INSTR(CONCAT(U.family_name,U.given_name), ?)';
            $where_arg[] = $filter_user;
        }
        $arrHistory = $arrHistory->whereRaw(implode(" AND ", $where), $where_arg);

        if ($request->get('department')) {
            $arrHistory->where('D.id', $request->get('department'));
        }
        if ($request->get('position')) {
            $arrHistory->where('P.id', $request->get('position'));
        }
        if (($request->get('expense_flg') || ($request->get('expense_flg') == '0'))) {
            $arrHistory->where('U.expense_flg', $request->get('expense_flg'));
        }
        $arrHistory = $arrHistory ->paginate($limit)->appends(request()->input());

        $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";

        $this->assign('listDepartment', $listDepartment);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);

        $this->assign('arrHistory', $arrHistory);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);


        $this->setMetaTitle("利用ユーザ登録");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Expense/user_index');
    }

    public function bulkUsage(Request $request)
    {
        $user = \Auth::user();
        $ids = $request->get('cids', []);
        $action = $request->get('action', '');

        if ($action == "register") {
            $type = 1;
        } elseif ($action == "cancel") {
            $type = 0;
        }
        if (count($ids)) {
            DB::beginTransaction();
            try {
                if ($type == 1) {
                    DB::table('mst_user')
                        ->where('mst_company_id',$user->mst_company_id)
                        ->whereIn('id', $ids)
                        ->update(['expense_flg' => '1']);
                } else {
                    DB::table('mst_user')
                        ->where('mst_company_id',$user->mst_company_id)
                        ->whereIn('id', $ids)
                        ->update(['expense_flg' => '0']);
                }
                DB::commit();
                if ($type == 1) {
                    return response()->json(['status' => true,'message' => [__('message.success.register_bulk_usage_form_issuance')]]);
                } else {
                    return response()->json(['status' => true,'message' => [__('message.success.cancel_bulk_usage_form_issuance')]]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . $e->getTraceAsString());
                if ($type == 1) {
                    return response()->json(['status' => false, 'message' => 'message.false.register_bulk_usage_form_issuance']);
                } else {
                    return response()->json(['status' => false, 'message' => 'message.false.cancel_bulk_usage_form_issuance']);
                }
            }
        } else {
            if ($type == 1) {
                return response()->json(['status' => false, 'message' => 'message.false.register_bulk_usage_form_issuance']);
            } else {
                return response()->json(['status' => false, 'message' => 'message.false.cancel_bulk_usage_form_issuance']);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $user   = \Auth::user();

        $item = DB::table('mst_user as U')
            ->leftJoin('mst_user_info as I', 'U.id','I.mst_user_id')
            ->leftJoin('mst_department as D', 'I.mst_department_id','D.id')
            ->leftJoin('mst_position as P', 'I.mst_position_id','P.id')
            ->select(DB::raw('U.id, U.expense_flg, CONCAT(U.family_name, U.given_name) as user_name,U.email,D.department_name, P.position_name'))
            ->where('U.id', $id)
            ->where('U.mst_company_id',$user->mst_company_id)
            ->first();

        return response()->json(['status' => true, 'item' => $item ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update($id, Request $request)
    {
        $user   = \Auth::user();

        $expense_flg = $request->get('expense_flg');
        $checkCompany = DB::table('mst_user')
            ->where('mst_company_id',$user->mst_company_id)
            ->where('id', $id)
            ->count();

        if ($checkCompany == 0) {
            return response()->json(['status' => false,'message' => [__('message.false.update_bulk_usage_form_issuance')]]);
        }

        DB::beginTransaction();
        try{
            DB::table('mst_user')
                ->where('mst_company_id',$user->mst_company_id)
                ->where('id', $id)
                ->update(['expense_flg' => $expense_flg]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            if ($expense_flg) {
                return response()->json(['status' => false, 'id' => $id, 'message' => [__('message.false.register_single_form_issuance')]]);
            } else {
                return response()->json(['status' => false, 'id' => $id, 'message' => [__('message.false.update_single_form_issuance')]]);
            }
        }
        if ($expense_flg) {
            return response()->json(['status' => true, 'id' => $id, 'message' => [__('message.success.register_single_form_issuance')]]);
        } else {
            return response()->json(['status' => true, 'id' => $id, 'message' => [__('message.success.update_single_form_issuance')]]);
        }
    }
}
