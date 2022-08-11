<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\PermissionUtils;
use App\Models\AssignStamp;
use App\Models\Company;
use App\Models\Department;
use App\Models\DepartmentStamp;
use App\Models\NewTimeCard;
use App\Models\Position;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserInfo;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Session;
use Illuminate\Support\Facades\DB;

class AttendanceController extends AdminController
{

    private $model;
    private $userInfo;
    private $department;
    private $position;
    private $assignStamp;
    private $company;
    private $stamp;
    private $departmentStamp;
    protected $week = ['日', '月', '火', '水', '木', '金', '土'];

    public function __construct(User $model, UserInfo $userInfo, Department $department, Position $position,
                                AssignStamp $assignStamp, Company $company, Stamp $stamp, DepartmentStamp $departmentStamp)
    {
        parent::__construct();
        $this->model = $model;
        $this->userInfo = $userInfo;
        $this->department = $department;
        $this->position = $position;
        $this->assignStamp = $assignStamp;
        $this->company = $company;
        $this->stamp = $stamp;
        $this->departmentStamp = $departmentStamp;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        if ($request->search_month) {
            $editedMonth = str_replace(['年', '月'], ['-', ''], $request->search_month);
            $month = Carbon::parse($editedMonth);
        } else {
            $month = now();
        }
        $searchMonthData = [
            'startTime' => $month->startOfMonth()->toDateTimeString(),
            'endTime' => $month->endOfMonth()->toDateTimeString()
        ];

        $user = \Auth::user();
        
        // 無害化処理設定時はCSVダウンロード無効化するためのフラグ TODO 非同期化と無害化
        $sanitizing_flg = Company::where('id', $user->mst_company_id)
            ->first()->sanitizing_flg;
        $action = $request->get('action', '');
        // get list user
        // set limit to 50 for UserSetting page
        $limit = $request->get('limit') ? $request->get('limit') : 50;//config('app.page_limit');
        $users = [];
        if (!array_search($limit, array_merge(config('app.page_list_limit'), [20]))) {
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';

        if ($action != "") {
            if ($action == "export")
                $users = $this->model->getList($user->mst_company_id, [AppUtils::USER_NORMAL, AppUtils::USER_OPTION],false, $limit, $searchMonthData);
            else $users = $this->model->getList($user->mst_company_id, [AppUtils::USER_NORMAL, AppUtils::USER_OPTION],false, $limit, $searchMonthData);
            $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";
        }
        $company = DB::table('mst_company')
            ->leftJoin('mst_limit', 'mst_company.id', '=', 'mst_limit.mst_company_id')
            ->select('mst_company.*', 'mst_limit.use_mobile_app_flg')
            ->where('mst_company.id', $user->mst_company_id)
            ->first();
        $company->domain = explode("\r\n", $company->domain);
        if (count($company->domain) == 1) {
            $company->domain = explode("\n", $company->domain[0]);
        }
        $email_domain_company = [];
        foreach ($company->domain as $domain) {
            $email_domain_company[$domain] = ltrim($domain, "@");
        }

        $this->assign('email_domain_company', $email_domain_company);
        $this->assign('users', $users);

        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

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

        // PAC_5-983 BEGIN
        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
        // PAC_5-983 END

        $this->assign('listDepartmentDetail', $listDepartmentDetail);
        $this->assign('listDepartmentTree', $listDepartmentTree);
        $this->assign('listPosition', $listPosition);
        // PAC_5-1599 追加部署と役職 Start
        $this->assign('listPositionObj', json_encode($listPosition, JSON_FORCE_OBJECT));
        // PAC_5-1599 End
        $this->assign('company', $company);
        $this->assign('sanitizing_flg', $sanitizing_flg);

        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE));

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        $this->setMetaTitle("タイムカード");

        if ($action == 'export') {
            return $this->render('Attendance.csv');
        } else {
            return $this->render('Attendance.index');
        }
    }

    public function book(Request $request)
    {
        $user = \Auth::user();
        $id = $request->get('id', 0);

        $showUser = DB::table('mst_user')
            ->where('id', '=', $id)
            ->where('mst_company_id', '=', $user->mst_company_id)
            ->first();

        $timeCards = collect();
        if ($showUser) {
            if ($request->search_month) {
                $editedMonth = str_replace(['年', '月'], ['-', ''], $request->search_month);
                $date = Carbon::parse($editedMonth);
                $startDate = $date->toDateTimeString();
                $endDate = $date->endOfMonth()->toDateTimeString();
            } else {
                $date = now();
                $startDate = $date->startOfMonth()->toDateTimeString();
                $endDate = now()->toDateTimeString();
            }

            $timeCards = NewTimeCard::where('mst_user_id', $id)
                ->whereBetween('punched_at', [$startDate, $endDate])
                ->orderBy('punched_at', 'desc')
                ->get();

            $tempArr = [];
            // 月の日数を取得する
            $days = $date->daysInMonth;
            for ($i = 1; $i <= $days; $i++) {
                $tempStr = $date->format('Y-m') . '-' . str_pad($i, 2, 0, STR_PAD_LEFT);
                array_push($tempArr, str_replace('-', '/', $tempStr));
            }

            $tempTimeCard = new NewTimeCard();
            if ($timeCards->isNotEmpty()) {
                $timeCards = $timeCards->groupBy(function ($item, $key) {
                    return $item->punched_date;
                });
                foreach ($tempArr as &$v) {
                    if (!isset($timeCards[$v])) {
                        $tempTimeCard->punch_data = NewTimeCard::PUNCH_DATA;
                        $timeCards[$v] = $tempTimeCard;
                    } else {
                        $timeCards[$v][0]->punch_data = $timeCards[$v][0]->getEveryPunchedTime();
                        $timeCards[$v] = $timeCards[$v][0];
                    }
                    $vAddWeek = NewTimeCard::WEEK[Carbon::parse(str_replace('/', '-', $v))->dayOfWeek];

                    $timeCards[$v . ' (' . $vAddWeek . ')'] = $timeCards[$v];
                    unset($timeCards[$v]);
                }
            } else {
                foreach ($tempArr as &$v) {
                    $tempTimeCard->punch_data = NewTimeCard::PUNCH_DATA;
                    $timeCards[$v] = $tempTimeCard;
                    $vAddWeek = NewTimeCard::WEEK[Carbon::parse(str_replace('/', '-', $v))->dayOfWeek];
                    $timeCards[$v . ' (' . $vAddWeek . ')'] = $timeCards[$v];
                    unset($timeCards[$v]);
                }
            }
        }

        $data = [
            'username' => $showUser->family_name . $showUser->given_name,
            'userId' => $showUser->id,
            'tableData' => $timeCards,
        ];

        $this->setMetaTitle("打刻一覧");
        $this->assign('data', $data);
        return $this->render("Attendance.Book.newindex");
    }

    public function update(Request $request, $date)
    {
        $date = explode(' ', $date)[0];
        $formData = $request->data;
        $backDate = substr($date, 0, strrpos($date, '-'));
        $timeCard = NewTimeCard::where('mst_user_id', $request->userid)
            ->whereBetween('punched_at', [$date, $date . ' 23:59:59'])
            ->first();

        $punchDataAndNum = $this->punchDataInitForCreateOrUpdate($formData['time']);
        try {
            if($timeCard) {
                $timeCard->update(['punch_data' => $punchDataAndNum['punch_data'], 'num_flg' => $punchDataAndNum['num_flg']]);
            } else {
                $arr = [
                    'punch_data' => $punchDataAndNum['punch_data'],
                    'mst_user_id' => $request->userid,
                    'punched_at' => $date,
                    'num_flg' => $punchDataAndNum['num_flg']
                ];
                NewTimeCard::create($arr);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            return redirect()->route('attendance.book', ['search' => $backDate, 'id' => $request->userid])->with(['time_update_msg' => '更新失敗しました']);
        }

        return redirect()->route('attendance.book', ['search_month' => $backDate, 'id' => $request->userid])->with(['time_update_msg' => '更新成功しました']);
    }

    public function punchDataInitForCreateOrUpdate($times)
    {
        $punchData = [
            'start1' => $times[0],
            'end1' => $times[1],
            'start2' => $times[2],
            'end2' => $times[3],
            'start3' => $times[4],
            'end3' => $times[5],
            'start4' => $times[6],
            'end4' => $times[7],
            'start5' => $times[8],
            'end5' => $times[9],
        ];

        $num_flg = 0;
        foreach($times as $key => $val) {
            if($val) {
                $num_flg = $key+1;
            }
        }

        return ['punch_data' => $punchData, 'num_flg' => $num_flg];
    }

}
