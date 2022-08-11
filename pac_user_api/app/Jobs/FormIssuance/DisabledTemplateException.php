<?php

namespace App\Jobs\FormIssuance;

/**
 * テンプレートが無効になった際に発生する例外
 */
class DisabledTemplateException extends FormImportException {
	const DISABLED = 0;
    const UNMATCHED_VERSION = 1;
    const NOT_FOUND = 2;
    

    public function __construct(int $code = self::DISABLED, string $message = "", \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
