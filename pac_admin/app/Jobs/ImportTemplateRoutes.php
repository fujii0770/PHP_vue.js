<?php

namespace App\Jobs;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CsvUtils;
use App\Http\Utils\TemplateRouteUtils;
use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;
use App\Models\CsvImportDetail;
use App\Models\CsvImportList;
use App\Models\Company;
use App\Models\Position;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportTemplateRoutes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    /**
     * ImportUsers constructor.
     * @param $id
     */
    public function __construct($id)
    {
        //
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $csv_import_list = CsvImportList::where('id', $this->id)->first();
        try {
            $csv_data = json_decode($csv_import_list->file_data);
            $user = Admin::where('id', $csv_import_list->user_id)->first();
            $create_user_name = $user->family_name . ' ' . $user->given_name;
            $company = Company::where('id', $csv_import_list->company_id)->first();
            $listPosition = Position::where('state', 1)->where('mst_company_id', $user->mst_company_id)->pluck('id', 'position_name')->toArray();

            $total = count($csv_data);
            $num_error = 0;// 失敗件数
            $num_normal = 0;// 成功件数
            $arrErrorMsg = []; //CSV取込失敗メッセージ
            $count_num = 0;
            if ($total) {
                $template_routes = [];
                $routes = [];
                $update_template_routes = [];
                foreach ($csv_data as $i => $row) {
                    if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) === 0)) {
                        continue;
                    }
                    $count_num++;
                    if (count($row) < 6) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => '行が正しくありません。', 'series' => ''];
                        continue;
                    }
                    foreach ($row as &$value) {
                        $value = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $value));
                    }
                    unset($value);
                    $is_update = false;
                    $row[0] = AppUtils::utf8_filter($row[0]);
                    if ($row[0]>0 ){
                        $hasRoute = DB::table('circular_user_templates')
                            ->where('id', $row[0])
                            ->where('state','!=',TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES)
                            ->where('mst_company_id', $user->mst_company_id)
                            ->exists();

                        if (!$hasRoute){
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => '同じIDの承認ルートを見つかりません。', 'series' => CsvUtils::getSeriesByIndex(0)];
                            continue;
                        }
                        $is_update = true;
                    }
                    $route_name = $row[1];
                    if ($route_name === '') {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => '名称を設定してください。', 'series' => CsvUtils::getSeriesByIndex(1)];
                        continue;
                    }
                    if (mb_strlen($route_name, 'UTF-8') > 25) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => '名称が25文字を超えています。', 'series' => CsvUtils::getSeriesByIndex(1)];
                        continue;
                    }
                    $state = $row[2];
                    if ($state === '') {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => '状態を０：無効、１：有効に設定してください。', 'series' => CsvUtils::getSeriesByIndex(2)];
                        continue;
                    }
                    if ($state != 0 && $state != 1) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => '状態を０：無効、１：有効に設定してください。', 'series' => CsvUtils::getSeriesByIndex(2)];
                        continue;
                    }
                    if (!$is_update){
                        $template_routes[$i] = array(
                            'mst_company_id' => $user->mst_company_id,
                            'name' => $route_name,
                            'state' => $state,
                            'create_at' => Carbon::now(),
                            'create_user' => $create_user_name,
                            'update_at' => Carbon::now(),
                            'update_user' => $create_user_name,
                        );
                    }else{
                        $update_template_routes[$i] = [
                            'id' => $row[0],
                            'data' => [
                                'mst_company_id' => $user->mst_company_id,
                                'name' => $route_name,
                                'state' => $state,
                                'update_at' => Carbon::now(),
                                'update_user' => $create_user_name,
                            ]
                        ];
                    }
                    $routes[$i] = [];
    
                    $index = 3;
                    $error_msg = [];
                    $child_send_order = 1;
                    $series = '';
                    while (((isset($row[$index]) && $row[$index] !== '')
                            || (isset($row[$index+1]) && $row[$index+1] !== '')
                            || (isset($row[$index+2]) && $row[$index+2] !== '')
                            || (isset($row[$index+3]) && $row[$index+3] !== '')
                        )|| $index === 3) {
                        // 部署
                        $department = $row[$index] ?? '';
                        $series = CsvUtils::getSeriesByIndex($index);
                        if ($department !== '') {
                            $obj_depart = new Department();
                            $departmentId = $obj_depart->detectFromName(explode(\App\Http\Utils\AppUtils::SPERATOR_SPLIT, $department), $user->mst_company_id);
                            if (!$departmentId) {
                                $error_msg = __('message.not_detected', ['attribute' => $department]);
                                break;
                            }
                        } else {
                            $error_msg = '部署を設定してください。';
                            break;
                        }

                        // 役職
                        $position = $row[$index + 1] ?? '';
                        $series = CsvUtils::getSeriesByIndex($index+1);
                        if ($position !== '') {
                            $positionId = $listPosition[$position] ?? false;
                            if (!$positionId) {
                                $error_msg = __('message.not_detected', ['attribute' => $position]);
                                break;
                            }
                        } else {
                            $error_msg = '役職を設定してください。';
                            break;
                        }

                        // 合議方法
                        $mode = $row[$index + 2] ?? '';
                        $series = CsvUtils::getSeriesByIndex($index+2);
                        if ($mode === '') {
                            $error_msg = '合議方法を設定してください。';
                            break;
                        }
                        if ($mode != 1 && $mode != 2) {
                            $error_msg = '正しい合議方法を設定してください。';
                            break;
                        }
                        $mode = $mode == 2 ? 3 : 1;

                        // 合議人数
                        $option = 0;
                        $multiple_department_position_flg = isset($company->multiple_department_position_flg) ? $company->multiple_department_position_flg : 0;
                        $query = DB::table('mst_user as U')
                            ->join('mst_user_info as UI', 'UI.mst_user_id', 'U.id')
                            ->where('U.mst_company_id', $user->mst_company_id)
                            ->where('U.option_flg', AppUtils::USER_NORMAL)
                            ->where('U.state_flg', AppUtils::STATE_VALID);
                        if ($multiple_department_position_flg === 1) {
                            $userNum = $query->where(function ($query) use ($departmentId) {
                                $query->orWhere('UI.mst_department_id', $departmentId)
                                    ->orWhere('UI.mst_department_id_1', $departmentId)
                                    ->orWhere('UI.mst_department_id_2', $departmentId);
                            })
                                ->where(function ($query) use ($positionId) {
                                    $query->orWhere('UI.mst_position_id', $positionId)
                                        ->orWhere('UI.mst_position_id_1', $positionId)
                                        ->orWhere('UI.mst_position_id_2', $positionId);
                                })
                                ->count();
                        } else {
                            $userNum = $query->where('UI.mst_department_id', $departmentId)
                                ->where('UI.mst_position_id', $positionId)
                                ->count();
                        }
                        if ($mode === TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN) {
                            $option = $row[$index+3] ?? '';
                            $series = CsvUtils::getSeriesByIndex($index+3);
                            if ($option === '' || !is_numeric($option)) {
                                $error_msg = '合議人数を設定してください。';
                                break;
                            }
                            if ($option > $userNum || $option == 0) {
                                $error_msg = '合議人数を確認してください。';
                                break;
                            }
                        } else {
                            if (!$userNum) {
                                $error_msg = '指定人数は総人数を超えました。';
                                break;
                            }
                        }

                        $routes[$i][] = array(
                            'mst_position_id' => $positionId,
                            'mst_department_id' => $departmentId,
                            'child_send_order' => $child_send_order,
                            'mode' => $mode,
                            'option' => $option,
                            'wait' => TemplateRouteUtils::MODE_WAIT[$mode],
                            'create_at' => Carbon::now(),
                            'create_user' => $create_user_name,
                        );
                        $child_send_order++;
                        $index += 4;
                    }

                    if (!empty($error_msg)) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => '', 'comment' => $error_msg, 'series' => $series];
                        unset($template_routes[$i]);
                        unset($update_template_routes[$i]);
                        continue;
                    }
                }

                if ($num_error !== $count_num) {
                    DB::beginTransaction();
                    $err_key = 0;
                    try {
                        foreach ($template_routes as $key => $template_route) {
                            $err_key = $key;
                            $template_id = DB::table('circular_user_templates')->insertGetId($template_route);
                            foreach ($routes[$key] as $route) {
                                $route['template'] = $template_id;
                                DB::table('circular_user_template_routes')->insert($route);
                            }
                            $num_normal++;
                        }
                        foreach ($update_template_routes as $key => $template_route) {
                            $err_key = $key;
                            DB::table('circular_user_templates')
                                ->where('id', $template_route['id'])
                                ->where('mst_company_id', $user->mst_company_id)
                                ->update($template_route['data']);
                            DB::table('circular_user_template_routes')
                                ->where('template', $template_route['id'])
                                ->delete();
                            foreach ($routes[$key] as $route) {
                                $route['template'] = $template_route['id'];
                                DB::table('circular_user_template_routes')->insert($route);
                            }
                            $num_normal++;
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::channel('import-csv-daily')->error("(list id:" . $csv_import_list->id . ")" . $e->getMessage() . $e->getTraceAsString());
                        $num_error++;
                        $arrErrorMsg[] = ['row' => (int)$err_key + 1, 'email' => '', 'comment' => $e->getMessage()];
                    }
                }

                // csv取込履歴追加
                $csv_import_list->success_num = $num_normal;
                $csv_import_list->failed_num = $num_error;
                $csv_import_list->total_num = $count_num;
                $csv_import_list->result = count($arrErrorMsg) == 0 ? 1 : 0; // 0:failed;1:success
                $csv_import_list->update_at = Carbon::now();
                $csv_import_list->save();

                $failed_rows = ""; // 失敗した行目
                // csv取込履歴詳細
                foreach ($arrErrorMsg as $error) {
                    if ($failed_rows == "") {
                        $failed_rows .= $error['row'];
                    } else {
                        $failed_rows = $failed_rows . ',' . $error['row'];
                    }
                    $csv_import_detail = new CsvImportDetail();
                    $csv_import_detail->list_id = $csv_import_list->id; // CSVリストのID
                    $csv_import_detail->row_id = $error['row']; // CSV行目
                    $csv_import_detail->email = $error['email']; // メールアドレス
                    $csv_import_detail->comment = (!empty($error['series']) ? $error['series'] . '列： ' : '') . $error['comment']; // コメント
                    $csv_import_detail->create_at = date("Y-m-d H:i:s");
                    $csv_import_detail->save();
                }
            }
        } catch (\Exception $e) {
            Log::channel('import-csv-daily')->error("(list id:" . $csv_import_list->id . ")" . $e->getMessage() . $e->getTraceAsString());
            $csv_import_list->result = 0; // 0:failed;1:success
            $csv_import_list->update_at = Carbon::now();
            $csv_import_list->save();
        }
    }
}