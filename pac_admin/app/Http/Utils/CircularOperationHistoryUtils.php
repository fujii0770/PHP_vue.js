<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Utils;

class CircularOperationHistoryUtils
{
    // circular_status
    const CIRCULAR_CREATE_STATUS = 1; // 作成
    const CIRCULAR_IMPRINT_STATUS = 2; // 捺印
    const CIRCULAR_ADD_TEXT_STATUS = 3; // テキスト追加
    const CIRCULAR_COMMENT_STATUS = 4; // コメント
    const CIRCULAR_APPLY_STATUS = 10; // 申請
    const CIRCULAR_APPROVE_STATUS = 11; // 承認
    const CIRCULAR_SEND_BACK_STATUS = 12; // 差戻し
    const CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS = 13; // 差戻し依頼
    const CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS = 14; // 差戻し承認
    const CIRCULAR_PULL_BACK_TO_USER_STATUS = 15; // 引戻し


    // document_comment_info
    const DOCUMENT_COMMENT_PRIVATE = 0; //社内宛先のみ
    const DOCUMENT_COMMENT_PUBLIC = 1; //社外宛先可

    /**
     * 回覧状態取得（PDF表示用）
     * @param $circular_status
     * @return string
     */
    public static function getCircularStatus($circular_status)
    {
        if ($circular_status == CircularUtils::SAVING_STATUS)
            return '保存中';
        if ($circular_status == CircularUtils::CIRCULATING_STATUS)
            return '回覧中';
        if ($circular_status == CircularUtils::CIRCULAR_COMPLETED_STATUS || $circular_status == CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS)
            return '回覧完了';
        if ($circular_status == CircularUtils::SEND_BACK_STATUS)
            return '差戻し';
        if ($circular_status == CircularUtils::RETRACTION_STATUS)
            return '引戻し';
        if ($circular_status == CircularUtils::DELETE_STATUS)
            return '削除';
        return '';
    }

    /**
     * 回覧者操作取得（PDF表示用）
     * @param $circular_user_status
     * @return string
     */
    public static function getCircularUserStatus($circular_user_status)
    {
        if ($circular_user_status == self::CIRCULAR_CREATE_STATUS)
            return '作成';
        if ($circular_user_status == self::CIRCULAR_IMPRINT_STATUS)
            return '捺印';
        if ($circular_user_status == self::CIRCULAR_APPLY_STATUS)
            return '申請';
        if ($circular_user_status == self::CIRCULAR_APPROVE_STATUS)
            return '承認';
        if ($circular_user_status == self::CIRCULAR_SEND_BACK_STATUS)
            return '差戻し';
        if ($circular_user_status == self::CIRCULAR_COMMENT_STATUS)
            return 'コメント';
        if ($circular_user_status == self::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS)
            return '差戻し依頼';
        if ($circular_user_status == self::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS)
            return '差戻し承認';
        if ($circular_user_status == self::CIRCULAR_PULL_BACK_TO_USER_STATUS)
            return '引戻し';
        return '';
    }

    /**
     * 回覧文書の関連文書取得
     * @param $data
     * @param $document_id
     * @param int $origin_document_id
     * @return mixed
     */
    public static function getOriginDocumentIds($data, $document_id, $origin_document_id = 0)
    {
        $ids[] = $document_id;
        foreach ($data as $key => $value) {
            if ($value->id == $document_id && !in_array($value->origin_document_id, [0, -1, $origin_document_id])) {
                $ids[] = self::getOriginDocumentIds($data, $value->origin_document_id);
            }
        }
        return $ids;
    }
}