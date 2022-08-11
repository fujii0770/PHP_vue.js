<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */
namespace App\Http\Utils;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AppSettingConstraint
{
    private $settingRequestsMax;
    private $settingFileSize;
    //private $settingDiskCapacity;
    private $settingStoragePercent;
    private $settingRetentionDay;
    private $settingDeleteDay;
    private $settingLongTermStoragePercent;
    private $settingDlMaxKeepDays;
    private $settingDlAfterProc;
    private $settingDlAfterKeepDays;
    private $settingDlRequestLimit;
    private $settingDlRequestLimitPerOneHour;
    private $settingDlFileTotalSizeLimit;
    private $settingMaxIpAddressCount;
    private $settingMaxViwerCount;
    private $settingMaxAttachmentSize;
    private $settingMaxTotalAttachmentSize;
    private $settingMaxAttachmentCount;
    private $settingSanitizeRequestLimit;
    private $settingMaxFrmDocument;

    public function __construct()
    {
        $this->settingRequestsMax = config('app.constraints_max_requests');
        $this->settingFileSize = config('app.constraints_max_doccument_size');
        //$this->settingDiskCapacity = config('app.constraints_user_storage_size');
        $this->settingStoragePercent = config('app.constraints_use_storage_percent');
        $this->settingRetentionDay = config('app.constraints_max_keep_day');
        $this->settingDeleteDay = config('app.constraints_delete_informed_day');
        $this->settingLongTermStoragePercent = config('app.constraints_long_term_storage_percent');
        $this->settingDlMaxKeepDays = config('app.constraints_dl_max_keep_days');
        $this->settingDlAfterProc = config('app.constraints_dl_after_proc');
        $this->settingDlAfterKeepDays = config('app.constraints_dl_after_keep_days');
        $this->settingDlRequestLimit = config('app.constraints_dl_request_limit');
        $this->settingDlRequestLimitPerOneHour = config('app.constraints_dl_request_limit_per_one_hour');
        $this->settingDlFileTotalSizeLimit = config('app.constraints_dl_file_total_size_limit');
        $this->settingMaxIpAddressCount = config('app.constraints_max_ip_address_count');
        $this->settingMaxViwerCount = config('app.constraints_max_viwer_count');
        $this->settingMaxAttachmentSize = config('app.constraints_max_attachment_size');
        $this->settingMaxTotalAttachmentSize = config('app.constraints_max_total_attachment_size');
        $this->settingMaxAttachmentCount = config('app.constraints_max_attachment_count');
        $this->settingSanitizeRequestLimit = config('app.constraints_sanitize_request_limit');
        $this->settingMaxFrmDocument = config('app.constraints_max_frm_document');
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingRequestsMax()
    {
        return $this->settingRequestsMax;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingFileSize()
    {
        return $this->settingFileSize;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    //public function getSettingDiskCapacity()
    //{
    //    return $this->settingDiskCapacity;
    //}

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingStoragePercent()
    {
        return $this->settingStoragePercent;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingRetentionDay()
    {
        return $this->settingRetentionDay;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDeleteDay()
    {
        return $this->settingDeleteDay;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingLongTermStoragePercent()
    {
        return $this->settingLongTermStoragePercent;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDlMaxKeepDays()
    {
        return $this->settingDlMaxKeepDays;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDlAfterProc()
    {
        return $this->settingDlAfterProc;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDlAfterKeepDays()
    {
        return $this->settingDlAfterKeepDays;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDlRequestLimit()
    {
        return $this->settingDlRequestLimit;
    }
    
    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDlRequestLimitPerOneHour()
    {
        return $this->settingDlRequestLimitPerOneHour;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingDlFileTotalSizeLimit()
    {
        return $this->settingDlFileTotalSizeLimit;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingMaxIpAddressCount()
    {
        return $this->settingMaxIpAddressCount;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingMaxViwerCount()
    {
        return $this->settingMaxViwerCount;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingMaxAttachmentSize()
    {
        return $this->settingMaxAttachmentSize;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingMaxTotalAttachmentSize()
    {
        return $this->settingMaxTotalAttachmentSize;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingMaxAttachmentCount()
    {
        return $this->settingMaxAttachmentCount;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingSanitizeRequestLimit()
    {
        return $this->settingSanitizeRequestLimit;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getSettingMaxFrmDocument()
    {
        return $this->settingMaxFrmDocument;
    }


    public function updateSetting($settingRequestsMax, $settingFileSize, $settingStoragePercent, $settingRetentionDay, $settingDeleteDay, $settingLongTermStoragePercent, $settingDlMaxKeepDays, $settingDlAfterProc, $settingDlAfterKeepDays, $settingDlRequestLimit, $settingDlRequestLimitPerOneHour, $settingDlFileTotalSizeLimit,$settingMaxIpAddressCount,$settingMaxViwerCount,$settingMaxAttachmentSize,$settingMaxTotalAttachmentSize,$settingMaxAttachmentCount, $settingSanitizeRequestLimit, $settingMaxFrmDocument){
        $this->settingRequestsMax = $settingRequestsMax;
        $this->settingFileSize = $settingFileSize;
        //$this->settingDiskCapacity = $settingDiskCapacity;
        $this->settingStoragePercent = $settingStoragePercent;
        $this->settingRetentionDay = $settingRetentionDay;
        $this->settingDeleteDay = $settingDeleteDay;
        $this->settingLongTermStoragePercent = $settingLongTermStoragePercent;
        $this->settingDlMaxKeepDays = $settingDlMaxKeepDays;
        $this->settingDlAfterProc = $settingDlAfterProc;
        $this->settingDlAfterKeepDays = $settingDlAfterKeepDays;
        $this->settingDlRequestLimit = $settingDlRequestLimit;
        $this->settingDlRequestLimitPerOneHour = $settingDlRequestLimitPerOneHour;
        $this->settingDlFileTotalSizeLimit = $settingDlFileTotalSizeLimit;
        $this->settingMaxIpAddressCount = $settingMaxIpAddressCount;
        $this->settingMaxViwerCount = $settingMaxViwerCount;
        $this->settingSanitizeRequestLimit = $settingSanitizeRequestLimit;
        $this->settingMaxFrmDocument = $settingMaxFrmDocument;
        $this->settingMaxAttachmentSize = $settingMaxAttachmentSize;
        $this->settingMaxTotalAttachmentSize = $settingMaxTotalAttachmentSize;
        $this->settingMaxAttachmentCount = $settingMaxAttachmentCount;
        Cache::put('appSetting', $this);

        $path = base_path('.env');
        $env_content = file_get_contents($path);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_REQUESTS', 'app.constraints_max_requests', $settingRequestsMax, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_DOCUMENT_SIZE', 'app.constraints_max_doccument_size', $settingFileSize, $env_content);
        //$env_content = $this->updateConfig('CONSTRAINTS_USER_STORAGE_SIZE', 'app.constraints_user_storage_size', $settingDiskCapacity, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_USE_STORAGE_PERCENT', 'app.constraints_use_storage_percent', $settingStoragePercent, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_KEEP_DAYS', 'app.constraints_max_keep_day', $settingRetentionDay, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DELETE_INFORMED_DAY', 'app.constraints_delete_informed_day', $settingDeleteDay, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_LONG_TERM_STORAGE_PERCENT', 'app.constraints_long_term_storage_percent', $settingLongTermStoragePercent, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DL_MAX_KEEP_DAYS', 'app.constraints_dl_max_keep_days', $settingDlMaxKeepDays, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DL_MAX_AFTER_PROC', 'app.constraints_dl_after_proc', $settingDlAfterProc, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DL_AFTER_KEEP_DAYS', 'app.constraints_dl_after_keep_days', $settingDlAfterKeepDays, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DL_REQUEST_LIMIT', 'app.constraints_dl_request_limit', $settingDlRequestLimit, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DL_REQUEST_LIMIT_PER_ONE_HOUR', 'app.constraints_dl_request_limit_per_one_hour', $settingDlRequestLimitPerOneHour, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_DL_FILE_TOTAL_SIZE_LIMIT', 'app.constraints_dl_file_total_size_limit', $settingDlFileTotalSizeLimit, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_IP_ADDRESS_COUNT', 'app.constraints_max_ip_address_count', $settingMaxIpAddressCount, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_VIWER_COUNT', 'app.constraints_max_viwer_count', $settingMaxViwerCount, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_ATTACHMENT_SIZE', 'app.constraints_max_attachment_size', $settingMaxAttachmentSize, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_TOTAL_ATTACHMENT_SIZE', 'app.constraints_max_total_attachment_size', $settingMaxTotalAttachmentSize, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_ATTACHMENT_COUNT', 'app.constraints_max_attachment_count', $settingMaxAttachmentCount, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_SANITIZE_REQUEST_LIMIT', 'app.constraints_sanitize_request_limit', $settingSanitizeRequestLimit, $env_content);
        $env_content = $this->updateConfig('CONSTRAINTS_MAX_FRM_DOCUMENT', 'app.constraints_max_frm_document', $settingMaxFrmDocument, $env_content);
        file_put_contents($path, $env_content);
    }

    private function updateConfig($nameEnv, $nameSetting, $value, $env_content){
        Config::set($nameSetting, $value);
        return preg_replace("/^$nameEnv\s*=.*?$/ism", "$nameEnv=$value",$env_content);
    }

    public static function getAppSettingConstraint(){
        if (!Cache::has('appSetting')){
            $appSetting = new AppSettingConstraint();
            Cache::forever('appSetting', $appSetting);
        }
        return Cache::get('appSetting');
    }
}