<?php


namespace App\Chat\Consts;

interface ChatCallbackStatusToContractSite
{
    /**
     * 待機中
     */
    const WAITING = 0;
    /**
     * コールバック済み
     */
    const CALLBACKED = 1;
    /**
     * 失敗
     */
    const FAILED = 9;

}
