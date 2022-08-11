<?php

namespace App\Jobs\FormIssuance;

/**
 * 帳票データを格納するクラス
 */
class FormData {

    public $frm_name;
    public $to_email_name;
    public $to_email_addr;
    public $search_values;
    public $form_values;

    public $frm_seq;
    public $mst_company_id;
    public $frm_template_id;
    public $frm_template_code;
    public $frm_code;

    public $frm_imp_mgr_id;
}
