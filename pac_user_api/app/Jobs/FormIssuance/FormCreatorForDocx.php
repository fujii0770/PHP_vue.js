<?php

namespace App\Jobs\FormIssuance;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord;

/**
 * Wordのテンプレートファイルから帳票を作成するクラス
 */
class FormCreatorForDocx extends FormCreator {


    /**
     * 
     */
    protected function create_form(array $placeholders, $template_path, $values, $outpath) {

        if (ob_get_length() == 0 ) {
            ob_start();
        }

        $templateProcessor = new PhpWord\TemplateProcessor($template_path);
        foreach ($placeholders as $placeholder) {
            if ($placeholder->additional_flg !== 0) {
                continue;
            }
            $pname = $placeholder->placeholder_name;
            $templateProcessor->setValue($pname, $values[$pname]);
        }

        $templateProcessor->saveAs($outpath);
        ob_end_clean(); //バッファ消去

    }


}
