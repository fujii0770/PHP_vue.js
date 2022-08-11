<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;


/**
 * 帳票インポートジョブ
 */
class FormIssuanceFiles {

	private $company_id;
	private $imp_mgr_id;
	private $template_id;

	private $dir = null;
	const STORAGE_DISK_NAME = "s3";

	/**
	 * @param $company_id 会社ID
	 * @param $template_id テンプレートID
	 * @param $imp_mgr_id インポートID
	 */
	public function __construct($company_id, $template_id, $imp_mgr_id = null) {
		$this->company_id = $company_id;
		$this->imp_mgr_id = $imp_mgr_id;
		$this->template_id = $template_id;
	}

	/**
	 * インポートIDを設定します。
	 */
	public function set_imp_mgr_id($imp_mgr_id) {
		$this->imp_mgr_id = $imp_mgr_id;
	}

	private function get_imp_mgr_id() {
		$id = $this->imp_mgr_id;
		if (is_null($id)) {
			throw new \Exception("frm_imp_mgr.id is null.");
		}
		return $id;
	}

	/**
	 * 保管先を取得します。
	 * 
	 * @return \Illuminate\Contracts\Filesystem\Filesystem
	 */
	public function get_main_storage() {
		return Storage::disk(self::STORAGE_DISK_NAME);
	}

	/**
	 * 一時作業用ログファイルパスを取得します。
	 */
	public function create_temp_log_path() {
		// $logdir = storage_path("logs");
		$logdir = $this->get_work_dir();
		$logpre = "frm_imp_".$this->imp_mgr_id."_";
		$tmp = tempnam($logdir, $logpre);
		$ret = $tmp.".log";
		rename($tmp, $ret);
		chmod($ret, 0644); // rw-r--r--
		return $ret;
	}

	private $original_template_filename;
	private $storage_template_filename;
	private $original_data_filename;
	/**
	 * テンプレートファイルのファイル名を設定します。（オリジナルファイル名）
	 */
	public function set_template_filename($filename) {
		$this->original_template_filename = $filename;
	}

	/**
	 * インポートデータファイル(CSV等)のファイル名を設定します。（オリジナルファイル名）
	 */
	public function set_data_filename($filename) {
		$this->original_data_filename = $filename;
	}

	/**
	 * 保管先のテンプレートファイル名を設定します。
	 */
	public function set_storage_template_filename($filename) {
		$this->storage_template_filename = $filename;
	}

	/**
	 * テンプレートファイルを取得します。
	 */
	public function get_form_template_file() {
		$path = $this->get_template_storage_path();
		return $this->get_main_storage()->get($path);
	}

	/**
	 * インポートファイルの読み込みストリームを取得します。
	 */
	public function read_import_file() {
		$path = $this->get_import_data_storage_path();
		return $this->get_main_storage()->readStream($path);
	}

	/**
	 * ローカルで作成したログファイルを、所定の保管先へコピーします。
	 */
	public function copy_to_storage_import_log($logfp) {
		fseek($logfp, 0);
		$lpath = $this->get_import_log_storage_path();
		$this->get_main_storage()->writeStream($lpath, $logfp);
	}

	/**
	 * ファイルの保存先ディレクトリパスを取得します。
	 */
	public function get_storage_dir() {
		if ($this->dir == null) {
			$this->dir = self::make_storage_path(
				config('app.s3_imprintservice_root_folder'), //"imprintservice",
				config('app.s3_form_template_folder'), // form_template
				config('app.server_env'),
				config('app.edition_flg'),
				config('app.server_flg'),
				$this->company_id
			);
		}
		return $this->dir;
	}

	/**
	 * テンプレートファイルの保存先ディレクトリパスを取得します.
	 */
	public function get_template_storage_dir() {
		return self::make_storage_path(
				$this->get_storage_dir(),
				config('app.s3_form_template_folder_type'));
	}

	/**
	 * インポートファイルの保存先ディレクトリパスを取得します.
	 */
	public function get_import_storage_dir() {
		return self::make_storage_path(
			$this->get_storage_dir(),
			config('app.s3_form_import_folder_type'));
	}

	/**
	 * テンプレートファイルの保存先ファイルパスを取得します.
	 */
	public function get_template_storage_path() {
		return self::make_storage_path(
			$this->get_template_storage_dir(),
			$this->get_template_storage_filename()
		);
	}

	/**
	 * テンプレートファイルの保存先ファイル名を取得します.
	 */
	public function get_template_storage_filename() {
		if ($this->storage_template_filename == null) {
			$this->make_template_storage_filename();
		}
		return $this->storage_template_filename;
	}

	/**
	 * テンプレートファイルの保存先ファイル名を作成します.
	 */
	public function make_template_storage_filename() {
		$ret = "form_template_".$this->template_id;
		if (!is_null($this->original_template_filename)) {
			$pi = pathinfo($this->original_template_filename);
			//$pi["filename"]
			$ex = $pi["extension"];
			if (is_string($ex)) {
				$ret .= ".".$ex;
			}
		}
		$this->storage_template_filename = $ret;
	}

	/**
	 * インポートファイルのパスを取得します。
	 */
	public function get_import_storage_path($prefix = "") {
		$id = $this->get_imp_mgr_id();
		
		return self::make_storage_path(
			$this->get_import_storage_dir(),
			"form_imp_".$id.$prefix
		);
	}

	/**
	 * インポート処理で出力したログファイルのパスを取得します。
	 */
	public function get_import_log_storage_path() {
		return $this->get_import_storage_path(".log");
	}

	/**
	 * インポートデータファイルのストレージ上のパスを取得します。
	 */
	public function get_import_data_storage_path() {
		$ex = "";
		if (!is_null($this->original_data_filename)) {
			$pi = pathinfo($this->original_data_filename);
			$ex = $pi["extension"];
			if (is_string($ex)) {
				$ex = ".".$ex;
			}
		}
		return $this->get_import_storage_path($ex);
	}

	private static function make_storage_path(...$dirs) {
		$path = "";
		foreach($dirs as $dir) {
			$path .= "/".$dir;
		}
		if (strlen($path) > 0) {
			$path = substr($path, 1);
		}
		return $path;
	}

	/**
	 * 作業用ディレクトリを取得します。（存在しない場合は作成します）
	 */
	public function get_work_dir() {
		$dir = storage_path('work');
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		return $dir;
	}

	/**
	 * ストレージにあるテンプレートファイルを作業用ディレクトリへコピーします.
	 * 
	 * @return string 作業用テンプレートファイル
	 */
	public function copy_to_work_template() {
		$pre = "form_work_tempalte_".$this->imp_mgr_id."_";
		$dir = $this->get_work_dir();
		$dest = tempnam($dir, $pre);
		$file = $this->get_form_template_file();
		if (file_put_contents($dest, $file) === false) {
			$dest = false;
		}
		return $dest;
	}


	/**
	 * テンプレートにデータを埋め込む際に使用する一時作業用ファイルを作成します。
	 * 
	 * @return string 一時ファイルの絶対パス
	 */
	public function create_work_work_filepath($data_id) {
		$pre = "form_work_work_".$this->imp_mgr_id."_".$data_id."_";
		$dir = $this->get_work_dir();
		return tempnam($dir, $pre);
	}

	/**
	 * 一時作業用PDFファイルのパスを作成します。
	 * 
	 * @return string 一時ファイルの絶対パス
	 */
	public function create_work_pdf_filepath($data_id) {
		$pre = "form_work_pdf_".$this->imp_mgr_id."_".$data_id."_";
		$dir = $this->get_work_dir();
		$tmp = tempnam($dir, $pre);
		$ret = $tmp.".pdf";
		rename($tmp, $ret);
		return $ret;
	}

}
