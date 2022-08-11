<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormImportManager extends Model
{
    protected $table = "frm_imp_mgr";

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * 待機中
     */
    const STATUS_INI = 0;
    /**
     * 実行中(Step1)
     */
    const STATUS_EXECUTING = 1;
    /**
     * 実行中(Step2)
     */
    const STATUS_EXICUTING_2 = 2;
    /**
     * 完了
     */
    const STATUS_COMPLETED = 5;

    /**
     * 取消（実行前の取消）
     */
    const STATUS_CANCEL = -1;
    /**
     * 中断（Step1）
     */
    const STATUS_ABORT_1 = -11;
    const STATUS_ABORT_2 = -12;
    /**
     * データエラー
     */
    const STATUS_DATA_ERROR = -21;
    /**
     * 回覧データ作成の失敗による中断。一部作成したデータがある可能性あり
     */
    const STATUS_INCLUDE_ERROR = -22;
    /**
     * 異常終了
     */
    const STATUS_FATAL_ERROR = -99;
}
