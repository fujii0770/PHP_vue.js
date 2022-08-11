<?php

namespace App\Jobs\FormIssuance;

/**
 * 帳票を作成を行う抽象クラス
 */
abstract class FormCreator {

    private $imp_mgr;
    private $form_template;
    private $form_template_filepath;
    private $storage;
    // private $logfp;
    private $placeholders;

    public function init($mgr, $form_template, $form_template_filepath, array $placeholders) {
        $this->imp_mgr = $mgr;
        $this->form_template = $form_template;
        $this->form_template_filepath = $form_template_filepath;
        // $this->logfp = $logfp;
        $this->placeholders = $placeholders;
    }

    public function create($values, $outpath) {
        $jsonstr = $values->frm_data;
        $jvalues = json_decode($jsonstr, true);
        $this->create_form($this->placeholders, $this->form_template_filepath, $jvalues, $outpath);
    }

    public function dispose() {

        $this->imp_mgr = null;
        $this->form_template = null;
        $this->form_template_filepath = null;
        // $this->logfp = $logfp;
        $this->placeholders = null;
    }

    abstract protected function create_form(array $placeholders, $template_path, $values, $outpath);


}
