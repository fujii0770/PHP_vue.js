<?php

namespace App\Jobs\FormIssuance;

use App\Utils\FormIssuanceUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * 帳票データを作成する抽象クラス
 */
abstract class FormDataMaker 
{
	private $mgr;
	private $form_template;
	private $encode;
	private $flags;
	private $user;

	/**
	 * 初期化処理
	 */
	public function init($mgr, $form_template, $user) {
		$this->mgr = $mgr;
		$this->form_template = $form_template;
		$this->flags = new FormFlagChecker($mgr->id, $mgr->mst_company_id);
		$this->user = $user;
	}

	/**
	 * 帳票インポート管理情報のオブジェクトを取得します。
	 * 
	 * @return object
	 */
	public function get_mgr() {
		return $this->mgr;
	}

	/**
	 * 帳票テンプレート情報のオブジェクトを取得します。
	 * 
	 * @return object
	 */
	public function get_form_template() {
		return $this->form_template;
	}
	

	/**
	 * データの有効性を検証します。
	 * 
	 * @param int $rownum レコード番号
	 * @param FormPlaceholderCols $placeholder_cols 帳票テンプレートプレースホルダー情報
	 * @param FormData $table_col_values 帳票データのオブジェクト
	 * @param array $messages エラーメッセージを格納する配列（参照）
	 * 
	 * @return boolean 
	 */
	abstract protected function validate_data($rownum,
			FormPlaceholderCols $placeholder_cods, FormData $table_col_values, array &$messages);

	// frm_xxx_data に保存
	/**
	 * 帳票データを保存します。
	 * 
	 * @param int $imp_id 帳票インポート管理ID
	 * @param object $frm_template 帳票テンプレート情報のオブジェクト
	 * @param FormData $data 帳票データ
	 */
	abstract protected function save_data($imp_id, $frm_template, FormData $data);

	/**
	 * バッファにたまっている帳票データのを保存します。
	 */
	protected function flush() {
		return true;
	}

	 /**
	  * 帳票データを削除します。<br />
	  * 帳票データのインポート失敗時に実行します。
	  */
	abstract protected function delete_data();

	/**
	 * 処理の最後に不要なデータを削除します。
	 */
	public function sweep() {
		DB::beginTransaction();
		try {
			$this->sweep_data();
			DB::commit();
		} catch(\Exception $e) {
			DB::rollBack();
			throw new FormImportException("Failed to sweep the data.", 0, $e);
		}
	}

	/**
	 * 処理の最後に不要なデータを削除します。
	 */
	abstract protected function sweep_data();

	/**
	 * 帳票データに作成した回覧データのIDを設定します。
	 */
	abstract public function update_circular_id($data_id, $circular_id);

	/**
	 * 帳票データを取得するデータクエリーを取得します。
	 */
	abstract public function get_data_query();


	/**
	 * 文字列の文字コードを変換します。
	 */
	protected function conv_str_encode($str) {
		$ret = $str;
		if ($this->encode != null && $this->encode != "UTF-8") {
			$ret = mb_convert_encoding($str, "UTF-8", $this->encode);
		}
		return $ret;
	}

	/**
	 * 中断／無効の状態を取得します。
	 */
	private function check_flags() {
		$this->flags->check_flags();
	}

	/**
	 * 帳票インポート処理を実行します。
	 * 
	 * @param resource $fp インポートするファイルの参照
	 * @param FormLogger $flog 帳票インポート処理のログを出力するクラスのインスタンス
	 * @param int $limit_record インポート処理の最大件数
	 * @param string $file_encode インポートファイルの文字コード
	 * 
	 * @return int|boolean インポートに成功した場合はその件数、失敗した場合はfalse
	 * 
	 */
	public function import_file($fp, FormLogger $flog, int $limit_record, $file_encode = null) {
		$mgr = $this->get_mgr();
		$imid = $mgr->id;
		$ftpl = $this->get_form_template();

		$frmpcols = $this->get_placeholder_cols();
		$pcols = $frmpcols->get_placeholder_cols();

		
		$flog->write_line("インポートファイルの妥当性チェックを開始します。");

		$this->encode = $file_encode;
		if ($this->encode == null) {
			$this->encode = $this->judge_fileencode($fp);
		}
		$flog->write_line("ファイル文字コード ： $this->encode");


		rewind($fp);
		$flog->write_begin_line("    1 行目 (ヘッダ)");
		$hcols = $this->readLine($fp); // ヘッダ
		$ret = true;
		if (!$this->validate_header($imid, $ftpl, $frmpcols, $hcols, $pcols)) {
			$flog->write(" ...インポート対象の項目が無いため処理を終了します")->write_eol();
            $this->update_mgr_message("インポート対象の項目が無いため処理を終了します");
			$ret = false;
		} else {
			$flog->write(" ... OK")->write_eol();
			$this->check_flags();
			$ret = $this->do_file_import($fp, $hcols, $frmpcols, $flog, $limit_record);
		}

		return $ret;
	}

	/**
	 * ファイルインポート（データ）
	 * 
	 * @param resource $fp インポートファイル
	 * @param array %hcols インポートファイルのヘッダー項目
	 * @param FormLogger $flog
	 * @param int $limit_record 最大件数
	 * 
	 * @return int|boolean インポートに成功した場合はその件数、失敗した場合はfalse
	 */
	private function do_file_import($fp, $hcols, $frmpcols, FormLogger $flog, int $limit_record) {
		$chunksize = 10;
		$err = 0;
		$data = $this->readLine($fp); // データ１件目
		$dlist = [];

		$linenum = 1;
		$rownum = 0;

		$hcount = count($hcols);

		$ret = false;
		try {
			while($data !== false) {
				$linenum++;
				$flog->write_begin_line("    $linenum 行目 ... ");
				if (is_array($data)){
					$cnt = count($data);
					if ($cnt > 0) {
						$rownum++;
						if ($limit_record > 0 && $limit_record < $rownum) {
							$flog->write("インポート可能な最大件数( $limit_record 件)を超えたため処理を中止します。");
							$this->update_mgr_message("インポート可能な最大件数( $limit_record 件)を超えたため処理を中止します。");
							$err++;
							break;
						}

						if ($cnt < $hcount) {
							$data = array_pad($data, $hcount, "");
						} else if ($cnt > $hcount) {
							$data = array_slice($data, 0, $hcount);
						}
						$kv = array_combine($hcols, $data);
						$d = $this->map_placeholder_value($frmpcols, $kv);
						// 妥当性チェック
						$messages = [];
						if ($this->validate_data($linenum, $frmpcols, $d, $messages)) {
							if ($err < 1) {
								$dlist[] = $d;
							}
							$flog->write("OK")->write_eol();
						} else {
							$err++;
							$flog->write("NG")->write_eol();
							$message = '';
							foreach ($messages as $msg) {
								$flog->write_line("        ".$msg);
                                $message = $message.$msg;
							}
                            $this->update_mgr_message($message);
						}
					}

					if ($rownum % $chunksize == 0) {
						$this->check_flags();
						if ($err < 1) {
							$this->save_chunk_data($dlist);
							$dlist = [];
						}
					}
					
				} else {
					$flog->write("Skip")->write_eol();
				}

				$data = $this->readLine($fp);
			}

			if ($err > 0) {
				// コミット済みを削除
				$this->rollback_data();
				$flog->write_eol();
				$flog->write_line("内容に問題があるためインポートに失敗しました。");
                $this->update_mgr_message("内容に問題があるためインポートに失敗しました。");
			} else {
				$this->check_flags();
				if (count($dlist) > 0) {
					$this->save_chunk_data($dlist);
				}
				$flog->write_line("$rownum 件インポートしました。");
				$ret = $rownum;
			}

		} catch (\Exception $e) {
            $this->update_mgr_message("Failed to import the data.");
			$this->rollback_data();
			throw new FormImportException("Failed to import the data.", 0, $e);
		}

		return $ret;

	}

	/**
	 * インポートデータとプレースホルダー等の必要な項目を対応させたインスタンスを取得します。
	 * 
	 * @param FormPlaceholderCols $frmpcols 
	 * @param array $row データ配列
	 * 
	 * @return FormData
	 */
	protected function map_placeholder_value(FormPlaceholderCols $frmpcols, array $row) {
		$frm_plhs = $frmpcols->get_form_placeholders();
		$map_placeholder_cols = $frmpcols->get_placeholder_cols();

		$frm_name = $frmpcols->get_frm_default_name();

		$ph_vals = [];
		if (is_array($map_placeholder_cols)) {
			foreach ($map_placeholder_cols as $placeholder => $col) {
				$val = null;
				if (array_key_exists($col, $row)) {
					$val = $row[$col];
					// if (mb_ereg_match("^\$\{.\}", $placeholder)) {
					// 	$frm_name = str_replace($placeholder, $val, $frm_name);
					// }
				}
				$ph_vals[$placeholder] = $val;
			}
		}

		$frm_val = [];
		foreach ($frm_plhs as $col => $plh) {
			$val = null;
			if (array_key_exists($plh, $ph_vals)) {
				$val = $ph_vals[$plh];
			}
			$frm_val[$col] = $val;
		}

		
		
		$d = new FormData();
		$d->frm_name = FormIssuanceUtils::sanitize_filename($frm_name);
		$d->search_values = $frm_val;
		$d->form_values = $ph_vals;

		$eaddr = $frmpcols->get_to_email_addr_imp();
		if ($eaddr != null && array_key_exists($eaddr, $row)) {
			$d->to_email_addr = $row[$eaddr];
		}
		$ename =  $frmpcols->get_to_email_name_imp();
		if ($ename != null && array_key_exists($ename, $row)) {
			$d->to_email_name = $row[$ename];
		}

		return $d;
	}


	/**
	 * ヘッダの妥当性をチェックします。
	 * インポート対象の項目が１つ以上あればOK。
	 * 
	 * @param int $imid 帳票インポート管理ID
	 * @param object $ftpl 帳票テンプレートオブジェクト
	 * @param FormPlaceholderCols $frmpcols
	 * @param array $hcols インポートファイルのヘッダ項目の配列
	 * @param array $pcols プレースホルダーの配列
	 * 
	 * @return boolean
	 */
	protected function validate_header($imid, $ftpl, FormPlaceholderCols $frmpcols, $hcols, $pcols) {
		$ret = false;
		if (is_array($pcols)) {
			foreach ($pcols as $ph => $col) {
				$ret = in_array($col, $hcols);
				if ($ret) break;
			}
		}
		if (!$ret) {
			$name = $frmpcols->get_to_email_name_imp();
			$addr = $frmpcols->get_to_email_addr_imp();
			$ret = in_array($name, $hcols) && in_array($addr, $hcols);
		}
		return $ret;
	}

	/**
	 * データインポート処理に失敗した際に、データを可能な限り処理前の状態に戻します。
	 * 
	 */
	private function rollback_data() {
		// 帳票データの削除
		DB::beginTransaction();
		try {
			$this->delete_data();
			DB::commit();
		} catch( \Exception $e) {
			DB::rollback();
			throw new FormImportException("Failed to delete data.", 0, $e);
		}

		$p = $this->previous_seq;
		$c = $this->current_seq;

		if ($p > -1 && $c > -1 && $c - $p == $this->fetched_seq_count) {
			$this->reset_seq($this->current_seq, $this->previous_seq);
		}
	}

    /**
     * @param $message
     * @throws FormImportException
     */
	private function update_mgr_message($message) {
        $imid = $this->get_mgr()->id;
        // 保存
        DB::beginTransaction();
        try {
            $imp_mgr = DB::table('frm_imp_mgr')
                ->where('id', $imid)->first();

            $mgr_message = $imp_mgr->download_request_message ? $imp_mgr->download_request_message.'\r\n'.$message : $message;

            DB::table('frm_imp_mgr')
                ->where('id', $imid)
                ->update([
                    'download_request_message' => $mgr_message,
                ]);

            DB::commit();
        } catch( \Exception $e) {
            DB::rollback();
            throw new FormImportException("Failed to update the data.", 0, $e);
        }
    }

	/**
	 * 配列に貯めていたデータの保存を実行します。
	 * 
	 * @param array $dlist データのバッファ（参照）
	 * 
	 * @return int,int 実行前のSEQと実行後のSEQ    
	 */
	private function save_chunk_data(&$dlist) {
		$imid = $this->get_mgr()->id;
		$ftpl = $this->get_form_template();

		$cnt = count($dlist);
		[$curseq, $nowseq] = $this->fetch_seq($cnt);
		$preseq = $curseq;

		// 保存
		DB::beginTransaction();
		try {
			foreach($dlist as $d) {
				$curseq++;

				$d->frm_seq = $curseq;

				$d->mst_company_id = $ftpl->mst_company_id;
				$d->frm_imp_mgr_id = $imid;
				$d->frm_template_id = $ftpl->id;
				$d->frm_template_code = $ftpl->frm_template_code;
				$d->frm_code = $this->make_form_code($ftpl->frm_template_code, $curseq);

				$d->frm_name = $d->frm_name."_".$d->frm_code;

				$this->save_data($imid, $ftpl, $d);
			}
			$this->flush();
			
			DB::commit();
		} catch( \Exception $e) {
			DB::rollback();
			throw new FormImportException("Failed to save the data.", 0, $e);
		}

		return [$preseq, $curseq, $nowseq];
	}

	/**
	 * 帳票コードを取得します。
	 */
	protected function make_form_code($frm_template_code, $frm_seq) {
		$ret = $frm_template_code."-".str_pad(strval($frm_seq), 8, "0", STR_PAD_LEFT);
		return $ret;
	}

	private $previous_seq = -1;
	private $current_seq = -1;
	private $fetched_seq_count = 0;

	/**
	 * SEQを取得し、指定した数を加算した値に更新します。
	 * 
	 * @param int $reqnum 加算する数
	 * @param int $recursives 再帰処理を行う最大深度
	 * 
	 * @return int 現在のSEQ
	 */
	private function fetch_seq($reqnum = 1, $recursives = 3) {

		$ftpl = $this->get_form_template();
		$coid = $ftpl->mst_company_id;
		$ftid = $ftpl->id;

		$recursives--;
		if ($recursives < 0) {
			throw new  \Exception("Failed to get the form serial number. {frm_template_id : $ftid}");
		}
		Log::info("Try to get the form serial number. {frm_template_id : $ftid}");

		$ret = -1;
		$curseq = -1;

		DB::beginTransaction();
		try {
			$seq = 0;
			$upseq = 0;
			$res = DB::table("frm_seqmgr")
				->where("frm_template_id", $ftid)
				->where("mst_company_id", $coid)
				->select("frm_seq")
				->lockForUpdate()
				->first();
			if (!$res) {
				$upseq = $reqnum;
				DB::table("frm_seqmgr")->insert([
					"mst_company_id" => $coid,
					"frm_template_id" => $ftid,
					"frm_seq" => $reqnum,
					"create_at" => date_create(),
            		"create_user" => $this->get_operate_user_name()
				]);
			} else {
				$seq = $res->frm_seq;
				$upseq = $seq + $reqnum;
				$res = DB::table("frm_seqmgr")
					->where("frm_template_id", $ftid)
					->where("mst_company_id", $coid)
					->where("frm_seq", $seq)
					->update(["frm_seq" => $upseq]);
				if ($res != 1) {
					throw new UnmatchedVersionException();
				}
			}
			DB::commit();
			$this->fetched_seq_count += $reqnum;
			Log::info("Updated form serial number. {frm_template_id : $ftid, frm_seq : $seq}");
			$ret = $seq;
			$curseq = $upseq;
		} catch (\Exception $e) {
			DB::rollback();
			if ($recursives < 0) {
				throw new  \Exception("Failed to get the form serial number. {frm_template_id : $ftid}");
			}
			Log::info("Failed to get the form serial number. Retry after 1 second. {frm_template_id : $ftid}"); // info?
			sleep(1); // 1秒待機
			[$ret, $curseq] = $this->fetch_seq($reqnum, $recursives);
		}

		if ($this->previous_seq < 0) {
			$this->previous_seq = $ret;
		}
		$this->current_seq = $curseq;
		
		return [$ret, $curseq];
	}

	/**
	 * データSEQをリセットします。
	 * 
	 * @param int $expected_current_seq 最後に使用したSEQ
	 * @param int $back_to_seq 戻すSEQ
	 */
	private function reset_seq($expected_current_seq, $back_to_seq) {

		$ftpl = $this->get_form_template();
		$coid = $ftpl->mst_company_id;
		$ftid = $ftpl->id;

		DB::beginTransaction();
		try {
			$res = DB::table("frm_seqmgr")
				->where("frm_template_id", $ftid)
				->where("mst_company_id", $coid)
				->where("frm_seq", $expected_current_seq)
				->update(["frm_seq" => $back_to_seq]);
			if ($res != 1) {
				DB::rollback();
				Log::info("Couldn't reset form serial number. {frm_template_id : $ftid, mst_company_id : $coid, frm_seq : $expected_current_seq}");
			} else {
				DB::commit();
				Log::info("Have reset form serial number. {frm_template_id : $ftid, frm_seq : $back_to_seq}");
			}
			
		} catch (\Exception $e) {
			DB::rollback();
			throw new  \Exception("Failed to reset the form serial number. {frm_template_id : $ftid}");
		}
		
	}

	/**
	 * １行取得します。
	 * 
	 * @param resource インポートファイル
	 * 
	 * @return array 
	 */
	private function readLine($fp) {
		$ret = fgets($fp);
		if ($ret !== false) {
			if (trim($ret) !== "") {
				$ret = $this->parseRow($ret);
			} else {
				$ret = null;
			}
		}
		if (is_array($ret)) {
			$mx = count($ret);
			for ($i = 0; $i < $mx; $i++) {
				$v = $ret[$i];
				if (is_string($v)) {
					$ret[$i] = trim($v);
				}
			}
		}
		return $ret;
	}

	/**
	 * 読み込んだ行を分解します.
	 * @param string $line
	 * @return array
	 */
	protected function parseRow($line) {
		$str = $this->conv_str_encode($line);
		return str_getcsv($str);
	}

	const DETECT_ENCODING_LIST = ['UTF-8', 'SJIS', 'ASCII', 'ISO-2022-JP', 'sjis-win', 'EUC-JP'];
	// const DETECT_DEFAULT_ENCODING = 'UTF8';
	const DETECT_SAMPLE_ROWS = 20; // TODO : とりあえず20行

	/**
	 * ファイルの文字コードを取得します.
	 */
	protected function judge_fileencode($fp) {
		$curpoint = ftell($fp);
		$ret = null;
		$jstr = null;

		$mxrows = self::DETECT_SAMPLE_ROWS;
		for ($i = 0; $i < $mxrows; $i++) { 
			$s = fgets($fp);
			if ($s === false) {
				break;
			}
			$jstr .= $s;
		}
		if ($jstr !== null) {
			// $ret = mb_detect_encoding($jstr);
			$ret = mb_detect_encoding($jstr, self::DETECT_ENCODING_LIST);
		}
		fseek($fp, $curpoint);

		return $ret;
	}


	/**
	 * 値がメールアドレスとして妥当かどう検証します。
	 * 
	 */
	protected function check_email($value, $colname, $rownum, array &$messages) {
		$ret = false;
		if ($colname == null || $value === null || trim($value) === "") {
			$ret = null;
		} else {
			$ret = preg_match("/^[^@\s]+@[^@\s]+(\.[^@\s]+)+$/", $value);
			if ($ret) {
				$ret = strtolower($value);
			} else {
				$messages[] = "$colname の値は E-mailアドレス として認識できませんでした。( $value )";
			}
		}
        return $ret;
    }

	protected function check_length($value, $colname, $rownum, array &$messages, $max = 1000) {
		if ($colname === null) {
			return null;
		}
		$ret = false;
		$str = strval($value);
		if ($str === null || trim($str) === "") {
			$ret = null;
		} else {
			$len = mb_strlen($str);
			if ($len > $max) {
				$messages[] = "$colname の値が最大文字数 ( $max 文字 ) を超えています。( $len 文字 )";
			} else {
				$ret = true;
			}
		}
        return $ret;
    }
	
    protected function check_date($value, $colname, $rownum, array &$messages) {
		if ($colname === null) {
			return null;
		}
        $ret = false;
        $td = FormIssuanceUtils::to_date($value);
        if ($td === false) {
            $messages[] = "$colname の値を 日付 として認識できませんでした。( $value )";
        } else {
            $ret = $td;
        }
        return $ret;
    }

    
    const MAX_AMT = 999999999999; // max 12桁
    const MIN_AMT = 0;

    protected function check_amt($value, $colname,  $rownum, array &$messages, $min = self::MIN_AMT, $max = self::MAX_AMT) {
		if ($colname === null) {
			return null;
		}
        $ret = FormIssuanceUtils::to_numeric_str($value);
        if ($ret === false) {
            $messages[] = "$colname の値は 数値 として認識できませんでした。( $value )";
        } else {
            $iamt = intval($ret);
            if ($iamt > $max) {
                $messages[] = "$colname の値が上限値($max)を超えています。( $value )";
				$ret = false;
            } else if ($iamt < $min) {
                $messages[] = "$colname の値が下限値($min)を下回っています。( $value )";
				$ret = false;
            }
        }
        return $ret;
    }


	protected function check_and_convert_date(&$vs, FormPlaceholderCols $form_cols, $key, $rownum, array &$messages) {
        $ret = true;
        $colname = $form_cols->get_column_name($key);
        if ($colname !== null && array_key_exists($key, $vs)) {
            $ret = $this->check_date($vs[$key], $colname, $rownum, $messages);
            if ($ret !== false) {
                $vs[$key] = $ret;
                $ret = true;
            }
        }
        return $ret;
    }


    protected function check_and_convert_amt(&$vs, FormPlaceholderCols $form_cols, $key, $rownum, array &$messages) {
        $ret = true;
        $colname = $form_cols->get_column_name($key);
        if ($colname !== null && array_key_exists($key, $vs)) {
            $ret = $this->check_amt($vs[$key], $colname, $rownum, $messages);
            if ($ret !== false) {
                $vs[$key] = $ret;
                $ret = true;
            }
        }
        return $ret;
    }

	/**
     * @return FormPlaceholderCols
     */
    protected function get_placeholder_cols() {
        $frm_template = $this->get_form_template();
        $ret = null;
        $res = $this->get_placeholder_col_records($frm_template->id, $frm_template->mst_company_id);

        if ($res !== null) {
            $frm_plhs = [];
            foreach ($this->get_spec_columns() as $col) {
                $frm_plhs[$col] = $res->{$col."_col"}; // キー：テーブルの項目名、値：プレースホルダー
            }
            $plh_csv = json_decode($res->frm_imp_cols, true); // キー：プレースホルダー、値：CSV項目名

            $ret = new FormPlaceholderCols(
                $res->frm_default_name, 
                $res->to_email_name_imp, 
                $res->to_email_addr_imp,
                $frm_plhs,
                $plh_csv );
        }

        return $ret;
    }

	protected function get_operate_user_name() {
		$ret = null;
		if (!is_null($this->user)) {
			$ret = $this->user->email;
		}
		return $ret;
	}

	/**
	 * 固有の項目名のリストを取得.
	 */
    protected function get_spec_columns() {
        return [];
    }


    /**
     * @return object
     */
    abstract protected function get_placeholder_col_records($frm_template_id, $company_id);
}
