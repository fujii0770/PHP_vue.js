<?php

namespace App\Jobs\FormIssuance;

/**
 * 回覧データ作成の際の例外
 */
class MakeCircularException extends FormImportException {

    private $rows_made;
	
	public function __construct(string $message = "", int $code = 0, \Throwable $previous = null, int $rows_made) {
        parent::__construct($message, $code, $previous);
        $this->rows_made = $rows_made;
    }

    public function get_rows_made() {
        return $this->rows_made;
    }


}
