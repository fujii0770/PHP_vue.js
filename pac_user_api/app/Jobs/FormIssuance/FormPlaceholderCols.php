<?php

namespace App\Jobs\FormIssuance;

/**
 * 帳票のプレースホルダーや項目名を管理するクラス
 */
class FormPlaceholderCols {

    private $frm_default_name;
    private $to_email_name_imp;
    private $to_email_addr_imp;
    private $form_placeholders;
    private $placeholder_cols;

    public function __construct ( $frm_default_name, $to_email_name_imp, $to_email_addr_imp, array $form_placeholders, array $placeholder_cols = null) {

            $this->frm_default_name = $frm_default_name;
            $this->to_email_addr_imp = $to_email_addr_imp;
            $this->to_email_name_imp = $to_email_name_imp;
            $this->form_placeholders = $form_placeholders;
            $this->placeholder_cols = $placeholder_cols;
    }

    public function get_frm_default_name() {
        return $this->frm_default_name;
    }

    public function get_to_email_name_imp() {
        return $this->to_email_name_imp;
    }

    public function get_to_email_addr_imp() {
        return $this->to_email_addr_imp;
    }

    public function get_form_placeholders() {
        return $this->form_placeholders;
    }

    public function get_placeholder_cols() {
        return $this->placeholder_cols;
    }

    public function get_column_name($key) {
        $ret = null;
        $holders = $this->get_form_placeholders();
        if (is_array($holders) && array_key_exists($key, $holders)) {
            $pholder = $holders[$key];
            $cols = $this->get_placeholder_cols();
            if (is_array($cols) && array_key_exists($pholder, $cols)) {
                $ret = $cols[$pholder];
            }
        }
        return $ret;
    }


}
