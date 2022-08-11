<?php

namespace App\Jobs\FormIssuance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


/**
 * 請求書データを作成するクラス
 */
class FormInvoiceDataMaker extends FormDataMaker {

    const FORM_COLS = [
            "trading_date", 
            "invoice_no", 
            "invoice_date", 
            "customer_name", 
            "customer_code", 
            "invoice_amt", 
            "payment_date"];

    protected function get_spec_columns() {
        return self::FORM_COLS;
    }
        

    /**
     * @return object
     */
    protected function get_placeholder_col_records($frm_template_id, $company_id) {
        return DB::table("frm_invoice_cols")
        ->where("frm_template_id", $frm_template_id)
        ->where("mst_company_id", $company_id)
        ->select([
            "frm_default_name",
            "to_email_name_imp",
            "to_email_addr_imp",
            "trading_date_col",
            "invoice_no_col",
            "invoice_date_col",
            "customer_name_col",
            "customer_code_col",
            "invoice_amt_col",
            "payment_date_col",
            "frm_imp_cols"
        ])
        ->first();
    }


    // const STR_COLS = [
    //     "invoice_no", 
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

        if ($this->check_and_convert_date($vs, $form_cols, "trading_date", $rownum, $messages) === false) $err++;
        if ($this->check_and_convert_date($vs, $form_cols, "invoice_date", $rownum, $messages) === false) $err++;
        if ($this->check_and_convert_date($vs, $form_cols, "payment_date", $rownum, $messages) === false) $err++;
        if ($this->check_and_convert_amt($vs, $form_cols, "invoice_amt", $rownum, $messages) === false) $err++;

        // foreach (self::STR_COLS as $key) {
        //     $colname = $form_cols->get_column_name($key);
        //     if ($colname !== null && array_key_exists($key, $vs)) {
        //         if ($this->check_length($vs[$key], $colname, $rownum, $messages) === false) $err++;
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

        // "trading_date"
        // "invoice_no"
        // "invoice_date"
        // "customer_name"
        // "customer_code"
        // "invoice_amt"
        // "payment_date"
    }

    protected function flush() {
        $ret  = DB::table('frm_invoice_data')->insert($this->data_list);
        $this->data_list = [];
        return $ret;
    }

	protected function delete_data() {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        $mid = $this->get_mgr()->id;

        DB::table("frm_invoice_data")
            ->where("frm_template_id", $fid)
            ->where("mst_company_id", $cid)
            ->where("frm_imp_mgr_id", $mid)
            ->delete();
    }

    public function get_data_query() {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        $mid = $this->get_mgr()->id;
        return DB::table("frm_invoice_data")
                ->where("mst_company_id", $cid)
                ->where("frm_template_id", $fid)
                ->where("frm_imp_mgr_id", $mid)
                ->orderBy("id");
    }

    public function update_circular_id($data_id, $circular_id) {
        $fid = $this->get_form_template()->id;
        $cid = $this->get_mgr()->mst_company_id;
        DB::table("frm_invoice_data")
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

        DB::table("frm_invoice_data")
            ->where("frm_template_id", $fid)
            ->where("mst_company_id", $cid)
            ->where("frm_imp_mgr_id", $mid)
            ->whereNull("circular_id")
            ->delete();
    }
}
