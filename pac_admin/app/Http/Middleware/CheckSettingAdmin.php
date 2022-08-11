<?php

namespace App\Http\Middleware;
use App\Http\Utils\PermissionUtils;
use Closure;
use Illuminate\Support\Facades\Auth;
use Request;
use Illuminate\Support\Facades\Log;


class CheckSettingAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::check()){
            $user = Auth::user();
            if (!$user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
                $mapUrlPermissions = [PermissionUtils::PERMISSION_BRANDING_SETTINGS_VIEW => 'global-setting/branding',
                    PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW => 'global-setting/authority',
                    PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW => 'global-setting/password-policy',
                    PermissionUtils::PERMISSION_DATE_STAMP_SETTING_VIEW => 'global-setting/date-stamp',
                    PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW => 'global-setting/company-stamp',
                    PermissionUtils::PERMISSION_LIMIT_SETTING_VIEW => 'global-setting/limit',
                    PermissionUtils::PERMISSION_PROTECTION_SETTING_VIEW => 'global-setting/protection',
                    PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_VIEW => 'global-setting/ip-restriction',
                    PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW => 'global-setting/signature',
                    PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW => 'long-term/long-term-index',
                    PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW => 'long-term/long-term-save',
                    PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW => 'long-term/long-term-folder',
                    PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_VIEW => 'global-setting/master-sync',
                    PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW => 'global-setting/box-enabled-auto-storage',

                    PermissionUtils::PERMISSION_USAGE_SITUATION_VIEW => 'reports/usage',
                    PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW => 'operation-history/admin',
                    PermissionUtils::PERMISSION_USER_HISTORY_VIEW => 'operation-history/user',
                    PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW => 'setting-admin',
                    PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW => 'setting-user/assign-stamp',
                    PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_VIEW => 'setting-address-common',
                    PermissionUtils::PERMISSION_DEPARTMENT_TITLE_VIEW => 'department-title',
                    PermissionUtils::PERMISSION_TEMPLATE_ROUTE_VIEW => 'template-route',
                    PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW => 'setting-user/setting-audit-account',
                    PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_VIEW => 'form-issuance/user-register',
                    PermissionUtils::PERMISSION_ADMIN_EXPENSE_SETTING_VIEW => 'expense/user-register',
                    PermissionUtils::PERMISSION_FRM_INDEX_SETTING_VIEW => 'form-issuance/frm-index',
                    // PAC_14-61 START
                    PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW => 'setting-groupware/colorCategoryList',
                    // PAC_14-61 END
                    PermissionUtils::PERMISSION_ADMIN_TIMECARD_SETTING_VIEW => 'attendance/users',
                    PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW => 'setting-groupware/holiday', // PAC_14-45

                    PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_VIEW => 'special-upload',
                    PermissionUtils::PERMISSION_SPECIAL_SITE_RECEIVE_VIEW => 'special-receive',
                    PermissionUtils::PERMISSION_SPECIAL_SITE_SEND_VIEW => 'special-send',
                ];
                foreach ($mapUrlPermissions as $permission => $url){
                    if (Request::is($url)&& !$user->can($permission)) {
                        return redirect()->route('home');
                    }
                }
                if (Request::is('circulars') && !$user->can([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])) {
                    return redirect()->route('home');
                }
                if (Request::is('circulars/exports') && !$user->can([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])) {
                    return redirect()->route('home');
                }
                if (Request::is('circulars/deletes') && !$user->can([PermissionUtils::PERMISSION_CIRCULATION_LIST_DELETE])) {
                    return redirect()->route('home');
                }
                if (Request::is('circulars-saved') && !$user->can([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])) {
                    return redirect()->route('home');
                }
                if (Request::is('circulars-saved/exports') && !$user->can([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])) {
                    return redirect()->route('home');
                }
                if (Request::is('circulars-saved/deletes') && !$user->can([PermissionUtils::PERMISSION_CIRCULATION_LIST_DELETE])) {
                    return redirect()->route('home');
                }

                if ($request->isMethod('POST')) {
                    $mapUrlPermissions = [PermissionUtils::PERMISSION_BRANDING_SETTINGS_UPDATE => 'global-setting/branding',
                                        PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_UPDATE => 'global-setting/authority',
                                        PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_UPDATE => 'global-setting/password-policy',
                                        PermissionUtils::PERMISSION_DATE_STAMP_SETTING_UPDATE => 'global-setting/date-stamp',
                                        PermissionUtils::PERMISSION_COMMON_MARK_SETTING_UPDATE => 'global-setting/company-stamp',
                                        PermissionUtils::PERMISSION_LIMIT_SETTING_UPDATE => 'global-setting/limit',
                                        PermissionUtils::PERMISSION_PROTECTION_SETTING_UPDATE => 'global-setting/protection',
                                        PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_UPDATE => 'global-setting/signature',
                                        PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_UPDATE => 'global-setting/long-term-save',
                                        PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_UPDATE => 'global-setting/box-enabled-auto-storage',

                                        PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_CREATE => 'setting-admin',
                                        PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_CREATE => 'setting-user/setting-audit-account',
                                        PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_CREATE => 'setting-address-common',
                                        PermissionUtils::PERMISSION_TEMPLATE_ROUTE_CREATE => 'template-route',
                                        PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE => 'department-title',
                                        PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_CREATE => 'global-setting/ip-restriction',

                                        PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE => 'setting-user/assign-stamp',

                                        PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_UPDATE => 'global-setting/master-sync',
                                        PermissionUtils::PERMISSION_ADMIN_EXPENSE_SETTING_UPDATE => 'expense/user-register/bulk_usage',
                                        PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_UPDATE => 'form-issuance/user-register/bulk_usage',
                                        // PAC_14-61 START
                                        PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_CREATE => 'setting-groupware/colorCategory',
                                        PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_DELETE => 'setting-groupware/colorCategory/delete',
                                        // PAC_14-61 END
                                        // PAC_14-45 Start
                                        PermissionUtils::PERMISSION_HOLIDAY_SETTING_CREATE => 'setting-groupware/holiday',
                                        PermissionUtils::PERMISSION_HOLIDAY_SETTING_DELETE => 'setting-groupware/holiday-reset',
                                        // PAC_14-45 End
                                        PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_DELETE => 'expense/m_purpose/bulk-usage',
                                        PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE => 'expense/m_purpose',
                                    ];
                    foreach ($mapUrlPermissions as $permission => $url){
                    if (Request::is($url, "$url/*")&& !$user->can($permission)) {
                                if (Request::is('global-setting/ip-restriction/bulk-update') &&
                                ($user->can([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_UPDATE]) || $user->can([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_DELETE]))) {
                            }else{
                                return redirect()->route('home');
                            }
                        }
                    }
                }

                if ($request->isMethod('PUT')) {
                    $mapUrlPermissions = [
                        PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE => 'setting-admin',
                        PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_UPDATE => 'setting-user/setting-audit-account',
                        PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_UPDATE => 'setting-address-common',
                        PermissionUtils::PERMISSION_DEPARTMENT_TITLE_UPDATE => 'department-title',
                        PermissionUtils::PERMISSION_TEMPLATE_ROUTE_UPDATE => 'template-route',
                        PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_UPDATE => 'global-setting/ip-restriction',
                        PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_UPDATE => 'form-issuance/user-register',
                        // PAC_14-61 START
                        PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_UPDATE => 'setting-groupware/colorCategory/',
                        // PAC_14-61 END
                        PermissionUtils::PERMISSION_HOLIDAY_SETTING_UPDATE => 'setting-groupware/holiday', // PAC_14-45
                        PermissionUtils::PERMISSION_ADMIN_EXPENSE_SETTING_UPDATE => 'expense/user-register',
                        PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_CREATE => 'expense/m_purpose',
                    ];
                    foreach ($mapUrlPermissions as $permission => $url){
                        if (Request::is($url, "$url/*")&& !$user->can($permission)) {
                            return redirect()->route('home');
                        }
                    }
                }

                // PAC_14-45 Start
                if ($request->isMethod('DELETE')) {
                    $mapUrlPermissions = [
                        PermissionUtils::PERMISSION_HOLIDAY_SETTING_DELETE => 'setting-groupware/holiday',
                    ];
                    foreach ($mapUrlPermissions as $permission => $url){
                        if (Request::is($url, "$url/*")&& !$user->can($permission)) {
                            return redirect()->route('home');
                        }
                    }
                }
                // PAC_14-45 End
            }
        }
        return $next($request);
    }
}
