<?php

namespace App\Jobs\FormIssuance;

use Illuminate\Support\Carbon;

/**
 * 帳票インポートの際のログファイルを作成するクラス
 */
class FormLogger {

    private $log_fp;
    private $timing_flag  = true;
    private $indent = "";

    const DEFAULT_INDENT = "    ";

    public function __construct($fp) {
        $this->log_fp = $fp;
    }

    public function set_timing_on($flag) {
        $this->timing_flag = $flag;
    }

    public function set_indent($indent) {
        $ret = "";
        if (is_integer($indent)) {
            for ($i = 0; $i < $indent; $i++) {
                $ret .= self::DEFAULT_INDENT;
            }
        } else if (is_string($indent)){
            $ret = $indent;
        }
        $this->indent = $ret;
    }

    private function get_begin_line() {
        $msg = "";
        if ($this->timing_flag) {
            $msg = $this->get_timestamp();
            $msg .= "  ";
        }
        $msg .= $this->indent;
        return $msg;
    }

    public function write_begin_line($message = null) {
        $this->write($this->get_begin_line())->write($message);
        return $this;
    }

	public function write_line($message = null) {
        $this->write_begin_line($message)->write_eol();
        return $this;
	}

	public function write($msg) {
		if ($this->log_fp !== null) {
			if ($msg === null) {
				$msg = "";
			} if (!is_string($msg)) {
				$msg = strval($msg);
			}
			fwrite($this->log_fp, $msg);
		}
        return $this;
	}

	public function write_eol() {
		$this->write(PHP_EOL);
        return $this;
	}

    public function write_timestamp() {
		$this->write($this->get_timestamp());
        return $this;
	}

    private function get_timestamp() {
        return date('Y-m-d H:i:s');
    }
}