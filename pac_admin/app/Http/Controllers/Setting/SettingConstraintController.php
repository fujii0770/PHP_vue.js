<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Utils\AppSettingConstraint;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController; 
use Config;
use Illuminate\Support\Facades\App;
use Log;
use Illuminate\Support\Facades\Validator;

class SettingConstraintController extends AdminController
{

    public function index()
    {
        $appSetting = AppSettingConstraint::getAppSettingConstraint();
        $envConfig = [
            'requests_max' => $appSetting->getSettingRequestsMax(),
            'file_size' => $appSetting->getSettingFileSize(),
            //'disk_capacity' => $appSetting->getSettingDiskCapacity(),
            'storage_percent' => $appSetting->getSettingStoragePercent(),
            'retention_day' => $appSetting->getSettingRetentionDay(),
            'delete_day' => $appSetting->getSettingDeleteDay(),
            'long_term_storage_percent' => $appSetting->getSettingLongTermStoragePercent(),
            'dl_max_keep_days' => $appSetting->getSettingDlMaxKeepDays(),
            'dl_after_proc' => $appSetting->getSettingDlAfterProc(),
            'dl_after_keep_days' => $appSetting->getSettingDlAfterKeepDays(),
            'dl_request_limit' => $appSetting->getSettingDlRequestLimit(),
            'dl_request_limit_per_one_hour' => $appSetting->getSettingDlRequestLimitPerOneHour(),
            'dl_file_total_size_limit' => $appSetting->getSettingDlFileTotalSizeLimit(),
            'max_ip_address_count' => $appSetting->getSettingMaxIpAddressCount(),
            'max_viwer_count' => $appSetting->getSettingMaxViwerCount(),
            'max_attachment_size' => $appSetting->getSettingMaxAttachmentSize(),
            'max_total_attachment_size' => $appSetting->getSettingMaxTotalAttachmentSize(),
            'max_attachment_count' => $appSetting->getSettingMaxAttachmentCount(),
            'sanitize_request_limit' => $appSetting->getSettingSanitizeRequestLimit(),
            'max_frm_document' => $appSetting->getSettingMaxFrmDocument(),
        ];
        foreach($envConfig as $name => $value){
            $envConfig[$name] = intval($value);
        }
        $this->assign('envConfig', $envConfig);
        $this->setMetaTitle('制約条件設定');
        return $this->render('SettingConstraint.index');
    }

    public function postUpdateSettingCorporate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requests_max' => 'required|integer|numeric|min:0.001|max:99999.999',
            'file_size' => 'required|integer|numeric|min:0.001|max:99999.999',
            //'disk_capacity' => 'required|integer|numeric|min:0.001|max:99999.999',
            'storage_percent' => 'required|integer|numeric|min:1|max:100',
            'retention_day' => 'required|integer|numeric|min:0.001|max:99999.999',
            'delete_day' => 'required|integer|numeric|min:0.001|max:99999.999',
            'long_term_storage_percent' => 'required|integer|numeric|min:1|max:100',
            'dl_max_keep_days' => 'required|integer|numeric|min:0|max:65535',
            'dl_after_proc' => 'required|integer|numeric|min:0|max:1',
            'dl_after_keep_days' => 'required|integer|numeric|min:0|max:65535',
            'dl_request_limit' => 'required|integer|numeric|min:0|max:65535',
            'dl_request_limit_per_one_hour' => 'required|integer|numeric|min:0|max:65535',
            'dl_file_total_size_limit' => 'required|integer|numeric|min:0|max:10485760',
            'max_ip_address_count' => 'required|integer|numeric|min:1',
            'max_viwer_count' => 'required|integer|numeric|min:1',
            'max_attachment_size' => 'required|integer|numeric',
            'max_total_attachment_size' => 'required|integer|numeric',
            'max_attachment_count' => 'required|integer|numeric',
            'sanitize_request_limit' => 'required|integer|numeric|min:0|max:65535',
            'max_frm_document' => 'required|integer|numeric|min:0|max:10485760',
        ]);
        
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }
        $appSetting = AppSettingConstraint::getAppSettingConstraint();
        $appSetting->updateSetting(
            $request->requests_max, 
            $request->file_size, 
            //$request->disk_capacity,
            $request->storage_percent, 
            $request->retention_day, 
            $request->delete_day, 
            $request->long_term_storage_percent, 
            $request->dl_max_keep_days, 
            $request->dl_after_proc, 
            $request->dl_after_keep_days, 
            $request->dl_request_limit,
            $request->dl_request_limit_per_one_hour,
            $request->dl_file_total_size_limit,
            $request->max_ip_address_count,
            $request->max_viwer_count,
            $request->max_attachment_size,
            $request->max_total_attachment_size,
            $request->max_attachment_count,
            $request->sanitize_request_limit,
            $request->max_frm_document,
        );

        return response()->json(['status' => true,
            'message' => [__('企業利用者制限設定を更新しました')]
        ]);
    }
    function updateConfig($nameEnv, $nameSetting, $value){   
        Config::set($nameSetting, $value); 
        $this->env_content = preg_replace(
            "/^$nameEnv\s*=.*?$/ism", "$nameEnv=$value", $this->env_content
        );
                
    }
}
