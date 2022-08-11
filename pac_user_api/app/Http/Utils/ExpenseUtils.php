<?php
/**
 * Created by
 * User: hopdt
 * Date: 04/19/22
 * Time: 11:46
 */

namespace App\Http\Utils;

use App\Http\Utils\CircularUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseUtils
{
    const EXPENSE_FORM_PLACE_HOLDER_COMMON = [
      '${会社名}', '${部署名}', '${名前}', '${合計}'
    ];

    const EXPENSE_PLACE_HOLDER_DATA_COMPANY_NAME = '${会社名}';
    const EXPENSE_PLACE_HOLDER_DATA_DEPARTMENT_NAME = '${部署名}';
    const EXPENSE_PLACE_HOLDER_DATA_USER_NAME = '${名前}';
    const EXPENSE_PLACE_HOLDER_DATA_TOTAL = '${合計}';

    const EXPENSE_LIST_WTSM_NAME_TRANSPORT = [
        '交通費',
    ];

    const NUMBER_FIELD_TAKE_FOR_M_FORM = 4;

    const EXPENSE_PLACE_HOLDER_DATA_WTSM_NAME = '用途';
    const EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_DATE = '日付';
    const EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_AMT = '金額';
    const EXPENSE_PLACE_HOLDER_DATA_DESCRIBE = '内容';

    const EXPENSE_INPUT_FOR_PLACE_HOLDER_WTSM_NAME = 'wtsm_name';
    const EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_DATE = 'expected_pay_date';
    const EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_AMT = 'expected_pay_amt';
    const EXPENSE_INPUT_FOR_PLACE_HOLDER_DESCRIBE = 'describe';

    const EXPENSE_INPUT_DATA_TYPE_DATE = 0;
    const EXPENSE_INPUT_DATA_TYPE_NUMERIC = 1;
    const EXPENSE_INPUT_DATA_TYPE_TEXT = 2;

    const EXPENSE_CIRCULAR_STATUS=0;

    /**
     *
     * @param $mst_company_id
     * @param $mst_user_id
     * @return string
     */
    public static function localExpensePath($mst_company_id, $mst_user_id): string
    {
        return 'expense/' .config('app.server_env') . '/' . config('app.edition_flg')
            . '/' . config('app.server_flg'). '/' . $mst_company_id . '/' . $mst_user_id . '/';
    }

    public function mapExpenseFormPlaceHolderWithInputData($placeHolders) {
        $result['success'] = false;

        $companyId = 1;
        $departmentName = 'Nono_Config';
        $userName = 'Hopdt';
        $formType = 1;
        $suspayAmt = 1000;
        $epsAmt = 6240;
        if (count($placeHolders)) {
            foreach ($placeHolders as $key => &$item) {
                if (isset($item['template_placeholder_name'])) {
                    switch ($item['template_placeholder_name']) {
                        case '${会社名}':
                            $item['input_data'] = $companyId;
                            break;
                        case '${部署名}':
                            $item['input_data'] = $departmentName;
                            break;
                        case '${名前}':
                            $item['input_data'] = $userName;
                            break;
                        case '${合計} ':
                            if ($formType == AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE) {
                                $inputData = $suspayAmt;
                            } else {
                                $inputData = $epsAmt;
                            }
                            $item['input_data'] = $inputData;
                            break;

                    }
                }
            }
            $result['success'] = true;
            $result['place_holder'] = $placeHolders;
        }


        return $result;
    }

    public static function isDate($date) {
        // e.g. 2020/01/01 is true
        if (preg_match('/^(\d{1,4})\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])$/', $date)) {
            return true;
            // e.g. 2020/01/01 00:00:00 is true
        } else if (preg_match('/^(\d{1,4})\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01]) ([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9]$/', $date)) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    public static function checkExpense($circular_id,$status)
    {
        try{

            $check=DB::table('eps_m_form')
                ->leftjoin('eps_t_app', 'eps_t_app.form_code','eps_m_form.form_code')
                ->where('eps_t_app.circular_id',$circular_id)
                ->first();
            //精算様式はform_type=2
            if($check->form_type==2){

                $department=DB::table('mst_department')
                    ->leftJoin('mst_user', 'mst_user.mst_company_id', '=', 'mst_department.mst_company_id')
                    ->leftJoin('mst_user_info', 'mst_user_info.mst_user_id', '=', 'mst_user.id')
                    ->where('mst_user.id',$check->mst_user_id)
                    ->value('department_name');


                if($status==CircularUtils::CIRCULAR_COMPLETED_STATUS){

                    $items=DB::table('eps_t_app_items')
                    ->Join('eps_t_app', 'eps_t_app.id', '=', 'eps_t_app_items.t_app_id')
                    ->where('eps_t_app.circular_id',$circular_id)
                    ->get();

                    foreach($items as $item){

                        //勘定科目判定
                        $kinds=DB::table('eps_m_journal_config')
                        ->where('mst_company_id',$item->mst_company_id)
                        ->where('wtsm_name',$item->wtsm_name)
                        ->whereIn('purpose_name',[$item->purpose_name,"汎用"])
                        ->get();

                        log::debug($item->wtsm_name);

                        $account="";
                        $sub_account_name="";
                        $no_conditions_flg=true;
                        $people=true;
                        $unit_price=true;
                        $pay_check=true;
                        $detail_check=true;
                        foreach($kinds as $kind){
                           
                            $criteria=json_decode($kind->criteria,true);
                            log::debug($criteria);
                            if($criteria){
                                // 全てtrueスタートでfalseのやつが出ると無しにする。
                                //全部値があるか確認 criteria
                                if ( array_key_exists('amount_sign',$criteria)) {
                                    $pay_check=self::Calculation($criteria["amount_sign"],$criteria["amount"],$item->expected_pay_amt);
                                }

                                if (array_key_exists('amount_people_sign',$criteria)) {
                                    //単価のやつ 金額/人数
                                    $unit_price=self::Calculation($criteria["amount_people_sign"],$criteria["amount_people"],$item->unit_price);
                                }
                                     
                                if(array_key_exists('people',$criteria)){
                                    if($criteria["people"] && ($criteria["people"]<=$item->numof_ppl)){
                                        $people=false;
                                    }
                                }
                                if(array_key_exists('detail_cond',$criteria)){
                                    //detailから判定
                                    $detail_check=self::detail_check($criteria["detail_cond"],$criteria["detail"],$item->remarks);
                                }
                                log::debug("pay_check $pay_check");
                                log::debug("unit_price $unit_price");
                                log::debug("detail_check $detail_check");

                                if($pay_check && $unit_price && $people && $detail_check){
                                    $no_conditions_flg=false;
                                    $account=$kind->account_name;
                                    $sub_account_name=$kind->sub_account_name;
                                    log::debug($account);
                                }
                            }else{
                                if($no_conditions_flg)
                                    $account=$kind->account_name;
                                    $sub_account_name=$kind->sub_account_name;

                            }

                        }
                        //計上部門の設定
                        //貸方の勘定科目どうする
                        $rate=$item->tax+100;
                        $credit_tax=$item->expected_pay_amt/$rate*100*($item->tax)/100;

                        DB::table('eps_t_journal')
                            ->insert([
                                'mst_company_id' => $item->mst_company_id,
                                'eps_t_app_id' => $item->t_app_id,
                                'eps_t_app_item_id' => $item->id,
                                'rec_dept' => $department,
                                'rec_date' => carbon::now(),
                                'credit_account' => "現金",
                                'credit_subaccount' =>"",
                                'credit_amount' => $item->expected_pay_amt,
                                'debit_tax_div' => null,
                                'debit_tax_rate' =>null,
                                'debit_tax' =>null,
                                'debit_account' => $account,
                                'debit_subaccount' => $sub_account_name,
                                'debit_amount' => $item->expected_pay_amt,
                                'credit_tax_div' => null,
                                'credit_tax_rate' =>$item->tax,
                                'credit_tax' =>$credit_tax,
                                'create_at' => Carbon::now(),
                                'create_user' => $item->create_user,
                                'update_at' => Carbon::now(),
                                'update_user' =>$item->create_user,
                            ]);

                    }
                }

            }

            DB::table('eps_t_app')
                    ->where('circular_id',$circular_id)
                    ->update([
                        'update_at' => Carbon::now(),
                        'status' => $status
                    ]);
            
            return true;

        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return false;

        }

    }

    private static function Calculation($operator,$right,$left){
        try{
            $hantei=false;
            switch($operator){
                case 1: 
                    $hantei=(boolean)($right==$left);
                    break;
                case 2:
                    $hantei= (boolean)($right<=$left);
                    break;
                case 3:
                    $hantei= (boolean)($right<$left);
                    break;
                case 4:
                    $hantei= (boolean)($right>=$left);
                    break;
                case 5:
                    $hantei= (boolean)($right>$left);
                default:
                    
                    break;
            }
            
            return $hantei;

        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $hantei=false;
            return $hantei;

        }

       
    }

    private static function detail_check($operator,$right,$left){
        try{
            switch($operator){
                case 1:
                    $hantei= preg_match("/$right/",$left);
                    break;

                case 2:
                    $text_check=explode(",",$right);
                    $hantei=false;
                    foreach($text_check as $check){
                        if(preg_match("/$check/",$left)){
                            $hantei=true;
                            break;
                        }
                    }
                    break;
                    
                case 3:
                    $hantei= false;
                    if($right==$left){
                        $hantei= true;
                    }       
                    break; 
                    
                default:
                    $hantei=false;
                    break;
            }
            return $hantei;

        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $hantei=false;
            return $hantei;

        }

        

    }
}

