<?php

namespace App\Utils;

/**
 * 帳票サービス用ユーティリティークラス
 */
class FormIssuanceUtils {

    // 自動操作フラグ（auto_ope_flg）
    const AUTO_OPE_SAVE = 0; // 保存
    const AUTO_OPE_COMPLETE = 1; // 完了保存
    const AUTO_OPE_APPLY = 2; // 自動回覧

    const DATA_TYPE_NUMBER = 0; // 数字
    const DATA_TYPE_TEXT = 1; // 文字
    const DATA_TYPE_DATE = 2; // 日付

	/**
	 * @return App\Utils\FormIssuanceFiles
	 */
	public static function import_files_operator(object $imp_mgr) {
		$ret = new FormIssuanceFiles(
				$imp_mgr->mst_company_id, 
				$imp_mgr->frm_template_id,
				$imp_mgr->id);
		$ret->set_data_filename($imp_mgr->imp_filename);
		return $ret;
	}

	public static function template_files_operator(int $company_id, int $template_id) {
		return new FormIssuanceFiles(
				$company_id, 
				$template_id);
	}

	const DATE_PARSE_PATTERN = "^(明治|明|M|大正|大|T|昭和|昭|S|平成|平|H|令和|令|R|西暦|')?\s*(\d{1,4}|元)\s*[ .\-/年]\s*(\d{1,2})\s*[ .\-/月]\s*(\d{1,2})\s*日?$";	

	/**
	 * 文字列を日付型に変換した値を取得します.
	 * 
	 * @param $str 変換する文字列
	 * @param $required 必須の場合に true を設定
	 * 
	 * @return \DateTime|bool 変換に成功した場合は DateTime型。失敗した場合は false。$required が false で $str が null または空文字の場合は null。
	 */
	public static function to_date(string $str, bool $required = false) {
        $ret = null;
		if ($str === null || trim($str) === "") {
			if ($required) {
				return false;
			} else {
				return null;
			}
		}
        $s = mb_convert_kana($str, "as");

        $matches = [];
        $ret = mb_eregi(self::DATE_PARSE_PATTERN, $s, $matches);
        if ($ret) {
			$g = $matches[1]; // 元号
            $y = $matches[2]; // 年
            $m = $matches[3]; // 月
            $d = $matches[4]; // 日
			
            if ($y === "元") {
                $y = 1;
            }

            $yoff = 0;
            if ($g == null || $g === "西暦") {
				if ($y < 1000) {
					$yoff = 2000;
				}
            } else if ($g === "令和" || $g === "令" || $g === "R") {
                $yoff = 2018;
            } else if ($g === "昭和" || $g === "昭" || $g === "S") {
                $yoff = 1925;
            } else if ($g === "大正" || $g === "大" || $g === "T") {
                $yoff = 1911;
            } else if ($g === "明治" || $g === "明" || $g === "M") {
                $yoff = 1868;
            } else if ($g === "'") {
                $yoff = 2000;
			} else {
                $yoff = 0;
            }


            $y += $yoff;

            if (checkdate($m, $d, $y)) {
                $ret = date_create($y."-".$m."-".$d);
            } else {
                $ret = false;
            }
        }
        return $ret;
    }

	/**
	 * 数値に変換可能な文字列に変換した値を取得します
	 */
    public static function to_numeric_str(string $str, bool $required = false) {
		$ret = null;
		$s = null;
		if (!is_null($str)) {

			$s = mb_convert_kana($str, "as");
			$s = trim($s);
			$s = mb_ereg_replace("[ ,，]", "", $s);
			$s = mb_ereg_replace("[\-ー―－円]+$", "", $s);
			$s = mb_ereg_replace("^[^\d\-ー―－]+", "", $s);

			if ($s === "") {
				$s = null;
			}
		}
		
		if ($required && is_null($s)) {
			$ret = false;
		}
		if ($ret !== false && !is_null($s)) {
			if (is_numeric($s)) {
				$ret = $s;
			} else {
				$ret = false;
			}
		}
		return $ret;
    }

	const FILENAME_FORBIDDEN_CHARS = ["\\", "/", ":", "*", "\"", "<",">", "|", "?"];

	/**
	 * ファイル名として害のある文字を無害な文字に置換します。
	 */
	public static function sanitize_filename($filename, $replace = "_") {
		$r = str_replace(self::FILENAME_FORBIDDEN_CHARS, "", $replace);
		return str_replace(self::FILENAME_FORBIDDEN_CHARS, $r, $filename);
	}
}
