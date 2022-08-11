<?php


namespace App\Chat\Consts;

interface ChatIdServerReturnCode
{
    /**
     * OK
     */
    const OK = 0;
    /**
     * パラメータが見つかりません
     */
    const REQUIRED_PARAMETER_NOT_FOUND = 1;
    /**
     * データが見つかりません
     */
    const DATA_NOT_FOUND = 2;
    /**
     * サブドメインの書式が不正です
     */
    const CHAT_SUBDOMAIN_INVALID_FORMAT = 1001;
    /**
     * サブドメインは既に使用されています
     */
    const CHAT_SUBDOMAIN_ALREADY_IN_USE = 1002;
    /**
     * 使用可能なドメイングループが見つかりません
     */
    const CHAT_AVAILABLE_DOMAIN_GROUP_NOT_FOUND = 1003;
    /**
     * 使用可能なMongoDB情報が見つかりません
     */
    const CHAT_AVAILABLE_MONGODB_NOT_FOUND = 1004;
    /**
     * 使用可能なコンテナイメージが見つかりません
     */
    const CHAT_CONTAINER_IMAGE_NOT_FOUND = 1005;

}
