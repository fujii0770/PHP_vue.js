<?php

namespace App\Jobs\FormIssuance;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Excelのテンプレートファイルから帳票を作成するクラス
 */
class FormCreatorForXlsx extends FormCreator {


    /**
     * 
     */
    protected function create_form(array $placeholders, $template_path, $values, $outpath) {

        $reader = new XlsxReader();
        $reader -> setReadDataOnly(false);
        $spreadsheet = $reader->load($template_path);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($placeholders as $placeholder) {
            $addr = $placeholder->cell_address;
            if ($placeholder->additional_flg !== 0 || !$addr) {
                continue;
            }
            
            $cellData = $sheet->getCell($addr)->getValue();
            $key = $placeholder->placeholder_name;
            $v = "";
            if (array_key_exists($key, $values)) {
                $v = $values[$key];
            }
            $newCellData = str_replace($key, $v, $cellData);
            $sheet->setCellValue($addr, $newCellData);
        }

        $writer = new XlsxWriter($spreadsheet);
        $writer->save($outpath);

        $sheet->disconnectCells();
        $spreadsheet->disconnectWorksheets();

    }

}
