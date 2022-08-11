<?php

namespace App\Jobs\FormIssuance;

/**
 * 帳票作成を行うクラスを作成するクラス
 */
class FormCreatorFactory {
	
    /**
     * @return FormCreator
     */
	public static function get($type) {
        $ret = null;
        if ($type === 0) {
            $ret = new FormCreatorForXlsx();
        } else {
            $ret = new FormCreatorForDocx();
        }
        return $ret;
    }
	

}
