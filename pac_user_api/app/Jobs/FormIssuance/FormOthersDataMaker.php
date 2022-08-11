<?php

namespace App\Jobs\FormIssuance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Utils\FormIssuanceUtils;

/**
 * その他帳票のデータを作成するクラス
 */
class FormOthersDataMaker extends FormDataMaker
{
    const FORM_COLS = [
        "reference_date", 
        "customer_name", 
        "customer_code",
        "frm_index1",
        "frm_index2",
        "frm_index3"
    ];

    protected function get_spec_columns() {
        return self::FORM_COLS;
    }


    /**
     * @return object
     */
    protected function get_placeholder_col_records($frm_template_id, $company_id) {
        return DB::table("frm_others_cols")
        ->where("frm_template_id", $frm_template_id)
        ->where("mst_company_id", $company_id)
        ->select([
            "frm_default_name",
            "to_email_name_imp",
            "to_email_addr_imp",
            "reference_date_col",
            "customer_name_col",
            "customer_code_col",
            "frm_imp_cols",
            "frm_index1_col",
            "frm_index2_col",
            "frm_index3_col"
        ])
        ->first();
    }


    // const STR_COLS = [
    //     "customer_name", 
    //     "customer_code"
    // ];

    protected function validate_data($rownum, FormPlaceholderCols $form_cols, FormData $d, array &$messages) {

        $vs = &$d->search_values;

        $err = 0;
        
        $email_col = $form_cols->get_to_email_addr_imp();
        $email_val = $d->to_email_addr;
        if ($this->check_email($email_val, $email_col, $rownum, $messages) === false) $err++;
        if ($this->check_length($email_val, $email_col, $rownum, $messages, 256) === false) $err++;

        if ($this->check_and_convert_date($vs, $form_cols, "reference_date", $rownum, $messages) === false) $err++;

        // foreach (self::STR_COLS as $key) {
        //     $colname = $form_cols->get_column_name($key);
        //     if ($colname !== null && array_key_exists($key, $vs)) {
        //         if (!$this->check_length($vs[$key], $colname, $rownum, $messages)) $err++;
        //     }
        // }

        $fvs = $d->form_values;
        foreach ($form_cols->get_placeholder_cols() as $key=>$colname) {
            if ($colname !== null && array_key_exists($key, $fvs)) {
                if ($this->check_length($fvs[$key], $colname, $rownum, $messages) === false) $err++;
            }
        }


        return $err < 1;
    }


    private $data_list = [];

    // frm_xxx_data に保存
    protected function save_data($imp_id, $frm_template, $d) {
        $vs = $d->search_values;
        $frmpcols  = $this->get_placeholder_cols();
        $frm_plhs = $frmpcols->get_form_placeholders();
        $frm_number_indexs = DB::table('frm_index')
            ->where('mst_company_id',$frm_template->mst_company_id)
            ->where('data_type',FormIssuanceUtils::DATA_TYPE_NUMBER)->get();

        if($frm_number_indexs->count()){
            foreach ($frm_number_indexs as $frm_number_index){
                $number_index = 'frm_index'.$frm_number_index->frm_index_number;
                if($vs && isset($vs[$number_index])){
                    $vs[$number_index] = intval($vs[$number_index]);
                    $d->form_values[$frm_plhs[$number_index]] = number_format($d->form_values[$frm_plhs[$number_index]]);
                }
            }
        }
        $p = [
            "mst_company_id" => $frm_template->mst_company_id,
            "frm_template_id" => $frm_template->id,
            "frm_template_code" => $frm_template->frm_template_code,
            "frm_seq" => $d->frm_seq,
            "frm_name" => $d->frm_name,
            "company_frm_id" => $d->frm_code,
            
            "circular_id" => null,
            "to_name" => $d->to_email_name, 
            "to_email" => $d->to_email_addr,
            
            "frm_data" => json_encode($d->form_values),
            
            "frm_imp_mgr_id" => $imp_id,
            
            "create_at" => Carbon::now(),
            "create_user" => $this->get_operate_user_name(),
            "update_at" => Carbon::now(),
            "update_user" => $this->get_operate_user_name()
        ];

        foreach (self::FORM_COLS as $col) {
            $p[$col] = $vs[$col]; 
        }

        $this->data_list[] = $p;
    }

    protected function flush() {
        $ret  = DB::table('frm_others_data')->insert($this->data_list);
        $this->data_list = [];
        return $ret;
    }

    protected function delete_data() {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        $mid = $this->get_mgr()->id;

        DB::table("frm_others_data")
            ->where("frm_template_id", $fid)
            ->where("mst_company_id", $cid)
            ->where("frm_imp_mgr_id", $mid)
            ->delete();
    }

    public function get_data_query() {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        $mid = $this->get_mgr()->id;
        return DB::table("frm_others_data")
                ->where("mst_company_id", $cid)
                ->where("frm_template_id", $fid)
                ->where("frm_imp_mgr_id", $mid)
                ->orderBy("id");
    }

    public function update_circular_id($data_id, $circular_id) {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        DB::table("frm_others_data")
            ->where("id", $data_id)
            ->where("mst_company_id", $cid)
            ->where("frm_template_id", $fid)
            ->update([
                "circular_id" => $circular_id, 
                "update_at" => date_create(), 
                "update_user" => $this->get_operate_user_name(),
                "version" => DB::raw("version + 1")
            ]);
    }

    protected function sweep_data() {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        $mid = $this->get_mgr()->id;

        DB::table("frm_others_data")
            ->where("frm_template_id", $fid)
            ->where("mst_company_id", $cid)
            ->where("frm_imp_mgr_id", $mid)
            ->where("circular_id", null)
            ->delete();
    }
}
