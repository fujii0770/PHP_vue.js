<?php

namespace App\Jobs\FormIssuance;

/**
 * 帳票インポートの例外
 */
class FormImportException extends \Exception {

	public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
}
