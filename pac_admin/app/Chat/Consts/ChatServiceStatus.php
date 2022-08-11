<?php


namespace App\Chat\Consts;

interface ChatServiceStatus
{
    /**
     * 未作成
     */
    const NONE = 0;
    /**
     * 起動待ち
     */
    const WAITING_FOR_STARTUP = 1;
    /**
     * 初期化中
     */
    const INITIALIZING = 2;
    /**
     * 実行中
     */
    const RUNNING = 3;

}
