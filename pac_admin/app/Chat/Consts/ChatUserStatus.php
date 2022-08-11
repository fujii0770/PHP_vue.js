<?php


namespace App\Chat\Consts;

interface ChatUserStatus
{
    /**
     * 無効
     */
    const INVALID = 0;
    /**
     * 有効
     */
    const VALID = 1;
    /**
     * 停止
     */
    const STOPPED = 2;
    /**
     * 削除
     */
    const DELETED = 9;
    /**
     * 登録待ち
     */
    const WAITING_TO_REGISTER = 10;
    /**
     * 削除待ち
     */
    const WAITING_TO_DELETE = 11;
    /**
     * 停止待ち
     */
    const WAITING_TO_STOP = 12;
    /**
     * 停止解除待ち
     */
    const WAITING_TO_UNSTOP = 13;
    /**
     * 登録に失敗
     */
    const PROCESSED_REGISTER_FAIL = 90;
    /**
     * 削除に失敗
     */
    const PROCESSED_DELETE_FAIL = 91;
    /**
     * 停止に失敗
     */
    const PROCESSED_TO_STOP = 92;
    /**
     * 停止解除に失敗
     */
    const PROCESSED_TO_UNSTOP = 93;
    /**
     * 停止状態
     */
    const PROCESSED_TO_STOP_STATUS = false;
    /**
     * 停止解除状態
     */
    const PROCESSED_TO_UNSTOP_STATUS = true;

}
