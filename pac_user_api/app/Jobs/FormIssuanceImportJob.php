<?php

namespace App\Jobs;

use App\Jobs\FormIssuance\CircularMaker;
use App\Jobs\FormIssuance\DisabledTemplateException;
use App\Jobs\FormIssuance\CancelRequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use App\Models\FormImportManager;
use App\Jobs\FormIssuance\UnmatchedVersionException;
use App\Jobs\FormIssuance\FormDataMakerFactory;
use App\Jobs\FormIssuance\FormFlagChecker;
use App\Jobs\FormIssuance\FormImportException;
use App\Jobs\FormIssuance\FormLogger;
use App\Jobs\FormIssuance\MakeCircularException;
use App\Utils\FormIssuanceUtils;

/**
 * 帳票インポートジョブ
 */
class FormIssuanceImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;
	private $company_id;
    private $from;
	private $dir = null;

	const CONFIG_FORM_IMP_LIMIT = "app.formissuance_import_limit";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $company_id, $from = null)
    {
        $this->id = $id;
		$this->company_id = $company_id;
        $this->from = $from;
    }

    /* 事前に frm_import_mgr にレコードを作成
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $id = $this->id;
		Log::info("Begin to import Form issuance  ( $id )");
        $from = $this->from;
		// インポート管理レコードの取得
		$mgr = $this->get_import_mgr($id);
		if (!$mgr) {
			Log::debug("Finish importing the Form issuance because there in no valid record. ( $id )");
			return;
		}
		$files = FormIssuanceUtils::import_files_operator($mgr);

		// 一時ログファイルを作成
		$templog = $files->create_temp_log_path();
		$logfp = fopen($templog, "a+");
		$flog = new FormLogger($logfp);

		$flog->set_timing_on(true);
		$flog->write_line("明細インポート");

        // ステータスを実行中に変更
		$mver = $mgr->version;
		$this->update_status_begin($id, $mver++);

		$flog->write_line("処理ID : ".$id);
		$flog->write_line("明細テンプレートID : ".$mgr->frm_template_id);

		$fmaker = null;

		$limit_rec = config(self::CONFIG_FORM_IMP_LIMIT);

		$step = 0;

		try {
			$company = $this->get_company();
			$user = $this->get_user($mgr->mst_user_id);

			$flags = new FormFlagChecker($id, $this->company_id);
			$flags->check_flags();

			// テンプレート情報の取得
			$tpl = $this->get_form_template_info($mgr->frm_template_id);
			$flog->write_line("明細テンプレートコード : ".$tpl->frm_template_code);
			$flog->write_line("インポートファイル名 : ".$mgr->imp_filename);
			$flog->write_eol();

			$fmaker = $this->get_form_maker($mgr, $tpl, $user);

			$flog->write_line("データのインポート処理を開始 (Step 1/2)");
			// インポートファイルの取得
			$fp = null;
			$improws = false;
			try {
				$step = 1;
				$flog->set_indent(1);
				$fp = $files->read_import_file();
				$improws = $fmaker->import_file($fp, $flog, $limit_rec);
			}catch(\Exception $e) {
				Log::error("明細インポート処理（明細データの作成）に失敗しました。id : ". $id);
				throw new FormImportException("Failed to import the file.", 0, $e);
			} finally {
				if ($fp != null) {
					@fclose($fp);
				}
			}
			$flog->set_indent(0);
			$flog->write_line("データのインポート処理を終了");

			if ($improws === false) {
				$this->update_status_end($id, FormImportManager::STATUS_DATA_ERROR, $mver++);
			} else {
				$step = 2;
				$this->update_status($id, FormImportManager::STATUS_EXICUTING_2, $mver++, $improws);
				// 回覧データの作成
				$flog->write_line("明細および回覧データ作成を開始 (Step 2/2)");
				$cmaker = new CircularMaker();
				$cmaker->init($mgr, $company, $user, $tpl, $fmaker, $flog, $from);
				$cmaker->make();

				// ステータスを完了に
				$this->update_status_end($id, FormImportManager::STATUS_COMPLETED, $mver++);

				$flog->set_indent(0);
				$flog->write_line("明細および回覧データ作成を終了");
			}
			
		} catch (CancelRequestException $e) {
			Log::error("Canceled import of Form issuance at user request. ( $id ) ");
			$this->update_status_end($id, $this->get_cancel_status($step), $mver++);
			$flog->set_indent(0);
			$flog->write_eol();
			$flog->write_line("ユーザーの要求により中断しました。");
		
		} catch (DisabledTemplateException $e) {
			$this->update_status_end($id, $this->get_cancel_status($step), $mver++);
			$flog->set_indent(0);
			$flog->write_eol();
			switch($e->getCode()) {
			case DisabledTemplateException::UNMATCHED_VERSION:
				Log::error("Canceled import because the template versions do not match. ( $id ) ");
				$flog->write_line("インポート要求時以降にテンプレートが変更された可能性があるため処理を終了します。");
				break;
			case DisabledTemplateException::NOT_FOUND:
				Log::error("Canceled import because the tempalte was not found. ( $id ) ");
				$flog->write_line("テンプレートが見つからないため処理を終了します。");
				break;
			default:
				Log::error("Canceled import because the template is invalid.( $id ) ");
				$flog->write_line("テンプレートが無効なため処理を終了します。");
					
			}
			
		} catch (MakeCircularException $e) {
			$this->update_status_end($id, FormImportManager::STATUS_INCLUDE_ERROR, $mver++);
			$flog->set_indent(0);
			$flog->write_eol();
			$flog->write_line("回覧データの作成に失敗したため処理を中断します。エラーが発生する前に作成した回覧データは有効です。");
		
		} catch (\Throwable $e) {
			// Log::error("Fail to import Form issuance for fatal error. ( $id ) : ". $e->getMessage());
			$this->update_status_end($id, FormImportManager::STATUS_FATAL_ERROR, $mver++);
			$flog->set_indent(0);
			$flog->write_eol();
			$flog->write_line("予期せぬエラーが発生したため処理を終了します。");
			$flog->set_timing_on(false);
			$emsg = "Fail to import Form issuance for fatal error. \n";
			do {
				$emsg .= sprintf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
				$emx = self::MAX_ERR_TRACE; // エラーログへスタックとレースを吐く最大行数
				$ecnt = 0;
				foreach($e->getTrace() as $ev) {
					if (++$ecnt >= $emx) {
						$emsg .= "...\n";
						break;
					}
					$emsg .= sprintf("%s:%d %s#%s(%s)\n", self::_str($ev, "file"), self::_str($ev, "line"), self::_str($ev, "class")
							, self::_str($ev, "function"), self::_str($ev, "args"));
				}
			} while($e = $e->getPrevious());
			Log::error($emsg);
		} finally {
			if (!is_null($fmaker)) {
				$fmaker->sweep();
			}
		}

		// ログファイルの移動
		$files->copy_to_storage_import_log($logfp);
		fclose($logfp);
		unlink($templog);

		Log::info("End to import Form issuance  ( $id )");
    }

	/**
	 * エラーログへスタックとレースを吐く最大行数
	 */
	private const MAX_ERR_TRACE = 10;

	private static function _str($ary, $key) {
		$ret = "";
		if (is_array($ary) && array_key_exists($key, $ary)) {
			$ret .= self::_to_str($ary[$key]);
		}
		return $ret;
	}

	private static function _to_str($v, $limit = 1) {
		$ret = "";
		if (is_array($v)) {
			if ($limit < 1) {
				$ret .= '[array]';
			} else {
				$ret .= self::_array_to_string($v);
			}
		} else if (is_object($v)) {
			$ret .= get_class($v);
		} else {
			$ret .= (string)$v;
		}
		return $ret;
	}


	private static function _array_to_string($ary, $limit = 1) {
		$ret = "";
		if (is_array($ary)) {
			foreach ($ary as $k=>$v) {
				$ret .= ",(".$k."=>";
				$ret .= self::_to_str($v, --$limit);
				$ret .= ")";
			}
			if (strlen($ret) > 0) {
				$ret = substr($ret, 1);
			}
		}
		
		return "[".$ret."]";
	}

	/**
	 * @return FormIssuance\FormDataMaker
	 */
	protected function get_form_maker($mgr, $tpl, $user) {
		return FormDataMakerFactory::get($mgr, $tpl, $user);
	}

	private function get_cancel_status($step) {
		$s = null;
		switch ($step) {
		case 1:
			$s = FormImportManager::STATUS_ABORT_1;
			break;
		case 2:
			$s = FormImportManager::STATUS_ABORT_2;
			break;
		default:
			$s = FormImportManager::STATUS_CANCEL;
		}
		return $s;
	}



	/**
	 * ステータスを更新します。
	 * 
	 * @param $id ID
	 * @param $status 更新後のステータスコード
	 * @param $version レコードのversion
	 */
	protected function update_status_begin($id, $version) {
		$this->update_imp_mgr($id, FormImportManager::STATUS_EXECUTING, $version, ["start_datetime" => Carbon::now()]);
	}

	/**
	 * 
	 */
	protected function update_status_end($id, $status, $version, $rows = 0) {
		$param = ["end_datetime" => Carbon::now()];
		if ($rows > 0) {
			$param["imp_rows"] = $rows;
		}
		$this->update_imp_mgr($id, $status, $version, $param);
	}

	protected function update_status($id, $status, $version, $rows = 0) {
		$param = [];
		if ($rows > 0) {
			$param["imp_rows"] = $rows;
		}
		$this->update_imp_mgr($id, $status, $version, $param);
	}

	/**
	 * 
	 */
	protected function update_imp_mgr($id, $status, $version, $sets = null) {
		$sss = [
			"imp_status" => $status, 
			"version" => $version + 1
		];
		if (is_array($sets)) {
			$sss = array_merge($sss, $sets);
		}

		DB::beginTransaction();
		try {
			$res = DB::table("frm_imp_mgr")
				->where("id", $id)
				->where("version", $version)
				->update($sss);
			if ($res != 1) {
				throw new UnmatchedVersionException();
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw new FormImportException("Failed to update frm_imp_mgr table.", 0, $e);
		}
		
		// コミット
	}


	protected function get_import_mgr() {
		$ret = DB::table("frm_imp_mgr")
				->where("id", $this->id)
				->where("mst_company_id", $this->company_id)
				->where("imp_status",FormImportManager::STATUS_INI)
				->first();
		return $ret;
	}

	protected function get_form_template_info(int $template_id) {
		$ret = DB::table("frm_template")
				->where("id", $template_id)
				->where("mst_company_id", $this->company_id)
				->first();
		return $ret;
	}

	
    protected function get_company() {
        $company = DB::table('mst_company')->where('id', $this->company_id)->first();
        return $company;
    }

	protected function get_user($user_id) {
		return DB::table("mst_user")
			->where("id", $user_id)
			->where("mst_company_id", $this->company_id)
			->select("id", "family_name","given_name", "email")->first();
	}
}
