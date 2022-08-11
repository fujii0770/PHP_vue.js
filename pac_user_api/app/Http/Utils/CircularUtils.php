<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 11/13/19
 * Time: 15:45
 */

namespace App\Http\Utils;


class CircularUtils
{
    //access code flg
    const ACCESS_CODE_VALID = 1;
    const ACCESS_CODE_INVALID = 0;

    //hide thumbnail flg
    const HIDE_THUMBNAIL_VALID = 1;
    const HIDE_THUMBNAIL_INVALID = 0;

    //status
    const SAVING_STATUS = 0; // 保存中(アップロード直後)
	const CIRCULATING_STATUS = 1; // 回覧中（送信直後の状態）
	const CIRCULAR_COMPLETED_STATUS = 2; // 回覧完了（最後の承認者が承認した時点でこの状態になる。）
    const CIRCULAR_COMPLETED_SAVED_STATUS = 3; // 回覧完了(保存済)。依頼者が文書をダウンロードするとこの状態になる。
    const SEND_BACK_STATUS = 4; // 差戻　（差戻直後のみこの状態。差戻後に再度承認を行うと回覧中に戻る。）
	const RETRACTION_STATUS = 5; // 引戻（削除と同様。依頼者の引き戻し）
	const DELETE_STATUS = 9; // 削除（回覧を削除するとこの状態になる。）
    
    const DEL_FLG = 1;

    //confidential
    const CONFIDENTIAL_VALID = 1;
    const CONFIDENTIAL_INVALID = 0;

    //社外用アクセスコード
    const OUTSIDE_ACCESS_CODE_VALID = 1;
    const OUTSIDE_ACCESS_CODE_INVALID = 0;

    // 完了回覧データコピーフラッグ
    const CIRCULAR_COMPLETED_COPY_FLG_FALSE = 0;    // コピーなし
    const CIRCULAR_COMPLETED_COPY_FLG_TRUE = 1;     // コピー済み

    // テキスト追加を許可する
    const TEXT_APPEND_FLG_VALID = 1;
    const TEXT_APPEND_FLG_INVALID = 0;

    // 回覧順変更を許可する
    const ADDRESS_CHANGE_FLG_VALID = 1; // 1：許可する
    const ADDRESS_CHANGE_FLG_INVALID = 0; // 0：許可しない

    // 捺印設定
    const NOT_REQUIRE_PRINT = 0; // 0：必須ではない
    const REQUIRE_PRINT = 1; // 1：必須

    /**
     * @param $email
     * @param $edition
     * @param $env
     * @param $server
     * @param $circular_id
     * @param false $is_move_to_lgwan 他環境からLGWAN遷移用URL作成の場合、trueに設定
     * @return string
     */
    public static function generateApprovalUrl($email, $edition, $env , $server ,$circular_id, $is_move_to_lgwan = false) {
        // LGWAN の Private 環境下で登録される回覧は Public 環境の情報で登録
        if((config('app.app_lgwan_flg') && config('app.circular_approval_lgwan_public_url')) || $is_move_to_lgwan){
            $url = config('app.circular_approval_lgwan_public_url');
        }else{
            $url = config('app.circular_approval_url');
        }
        return $url.AppUtils::encrypt( implode('#', [$email, $circular_id, $edition, $env, $server]),true);
    }

	public static function encryptOutsideAccessCode($outsideAccessCode)
	{
		if (!$outsideAccessCode) {
			return '';
		}
		return '?' . base64_encode(AppUtils::encrypt(implode('=', ['circular_user_id', $outsideAccessCode]), true));
	}

	public static function decryptOutsideAccessCode($outsideAccessCodeHash)
	{
		if (!$outsideAccessCodeHash) {
			return '';
		}
		return AppUtils::decrypt(base64_decode($outsideAccessCodeHash), true);
	}
}