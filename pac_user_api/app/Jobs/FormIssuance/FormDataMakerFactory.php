<?php

namespace App\Jobs\FormIssuance;

/**
 * 帳票データを作成するクラスを作成するクラス
 */
class FormDataMakerFactory
{
	
    /**
     * @return \App\Jobs\FormIssuance\FormDataMaker
     */
	public static function get($mgr, $form_template, $user) {
        $ret = null;
        switch($form_template->frm_type) {
            case 1 :
                $ret = new FormInvoiceDataMaker();
                break;
            default :
                $ret = new FormOthersDataMaker();
        }
        if ($ret != null) {
            $ret->init($mgr, $form_template, $user);
        }
        return $ret;
    }
	

}
