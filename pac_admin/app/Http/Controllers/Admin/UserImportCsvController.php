<?php
/**
 * Created by PhpStorm.
 * User: lul
 * Date: 2020/12/18
 * Time: 10:15
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils; // PAC_5-2133
use App\Models\CsvImportList;
use App\Models\CsvImportDetail;
use Illuminate\Http\Request;

class UserImportCsvController extends AdminController
{
    private $import_csv_list;
    private $import_csv_detail;

    public function __construct(CsvImportList $import_csv_list, CsvImportDetail $import_csv_detail)
    {
        parent::__construct();
        $this->import_csv_list = $import_csv_list;
        $this->import_csv_detail = $import_csv_detail;
    }

    /**
     * csv取込履歴一覧
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request){
        $user = \Auth::user();

        // get list import csv
        // set limit to 50 for UserSetting page
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        if(!array_search($limit, config('app.page_list_limit'))){
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        $where      = ['company_id = '. intval($user->mst_company_id)];
        $where_arg  = [];

        $from_date  = request('from_date','');
        $to_date    = request('to_date','');
        // PAC_5-2133 Start
        $import_type = $request->get('type', '1');
        if($import_type == 1){
            $where[] = "(import_type = " . (int)$import_type . ' OR import_type = ' . AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER . ')';
        }elseif($import_type == 3){
            $where[] = "(import_type = " . (int)$import_type . ' OR import_type = ' . AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_OPTION_USER . ')';
        }elseif($import_type == 4){
            $where[] = "(import_type = " . (int)$import_type . ' OR import_type = ' . AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_RECEIVE_USER . ')';
        }else{
            $where[] = "import_type = " . (int)$import_type;
        }

        //PAC_5-2334 Start
        $strGoBackURL = isset(AppUtils::STATE_IMPORT_CSV_BACK_URL[$import_type]) ? AppUtils::STATE_IMPORT_CSV_BACK_URL[$import_type]  : '';
        //PAC_5-2334 End
        $import_type = isset(AppUtils::STATE_IMPORT_CSV_TYPE[$import_type]) ? '(' . AppUtils::STATE_IMPORT_CSV_TYPE[$import_type] . ')' : '';
        // PAC_5-2133 End

        if($from_date){
            $where[]        = 'create_at >= ?';
            $where_arg[]    = "$from_date 00:00:00";
        }

        if($to_date){
            $where[]        = 'create_at <= ?';
            $where_arg[]    = "$to_date 23:59:59";
        }

        $import_csv_list = $this->import_csv_list->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit)
            ->appends(request()->input());

        $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        $this->assign('import_csv_list', $import_csv_list);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('strGoBackURL', $strGoBackURL);//PAC_5-2334

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->assign('import_type', $import_type); // PAC_5-2133 Start
        $this->setMetaTitle("CSV取込履歴一覧$import_type");
        return $this->render('ImportHistory.index');
    }

    /**
     * csv取込履歴詳細
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request){
        $import_detail = CsvImportList::where('id', $id)
            ->first();

        $error_rows = CsvImportDetail::where('list_id', $id)
            ->get('row_id');
        // set limit to 50 for UserSetting page
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        if(!array_search($limit, config('app.page_list_limit'))){
            $limit = config('app.page_limit');
        }
        $failed_rows = ""; // 失敗した行目
        foreach ($error_rows as $error) {
            if ($failed_rows == "") {
                $failed_rows .= $error['row_id'];
            } else {
                $failed_rows = $failed_rows . ',' . $error['row_id'];
            }
        }

        $error_detail = CsvImportDetail::where('list_id', $id)
            ->paginate($limit)
            ->appends(request()->input())
            ->toArray();

        return response()->json(['status' => true, 'import_detail' => $import_detail, 'error_detail' => $error_detail, 'failed_rows' => $failed_rows]);
    }
}