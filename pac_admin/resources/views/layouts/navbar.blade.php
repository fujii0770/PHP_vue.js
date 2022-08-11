@hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
<nav class="navbar">
@else
<nav class="navbar" ng-controller="navbar">
@endhasrole
    <!-- Navbar links -->
    <ul class="navbar-nav flex-column">
        @hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
            <li class="nav-item {{Request::is('reports/usage')?'active':''}}">
                <a href="{{Route('Reports.Usage.Show')}}" class="nav-link"><span class="icon"  style="{{(session('stamp_is_over') == 1 ? 'background:#f00;border-radius:90%;width:8px;height:8px;' :'')}}"><i class="fas fa-chalkboard-teacher" ></i></span><span class="text">利用状況</span></a>
            </li>
            <li class="nav-item {{Request::is('mail-send-resume')?'active':''}}">
                <a href="{{url('mail-send-resume')}}" class="nav-link"><span class="icon"><i class="fas fa-envelope"></i></span><span class="text">送信状況</span></a>
            </li>

            <li class="nav-item {{Request::is('companies')?'active':''}}">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2"><span class="icon">🔧</span><span class="text">基本設定</span></a>
                <ul id="sub-menu-2" class="collapse sub-menu">
                    <li class="item-sub {{Request::is('companies')?'active':''}}"><a class="nav-link" href="{{url('companies')}}">企業設定</a></li>
                    <li class="item-sub {{Request::is('setting/constraint')?'active':''}}"><a class="nav-link" href="{{route('SettingConstraint')}}">制約条件設定</a></li>
                    <li class="item-sub {{Request::is('setting/convenient')?'active':''}}"><a href="{{url('setting/convenient')}}" class="nav-link">便利印設定</a></li>
                    <!-- PAC_5-2912 -->
                    <li class="item-sub {{Request::is('setting/sanitizing')?'active':''}}"><a href="{{url('setting/sanitizing')}}" class="nav-link">無害化回線設定</a></li>
                </ul>
            </li>

            <li class="nav-item {{Request::is('login-layout-setting')?'active':''}}">
                <a href="{{url('login-layout-setting')}}" class="nav-link"><span class="icon"><i class="fas fa-desktop"></i></span><span class="text">ログイン画面設定</span></a>
            </li>
            <li class="nav-item {{Request::is('edition')?'active':''}}">
                <a href="{{url('edition')}}" class="nav-link"><span class="icon"><i class="fas fa-list-ol"></i></span><span class="text">契約Edition</span></a>
            </li>
{{--        PAC_5_2663 -- next step --}}
{{--            <li class="nav-item {{Request::is('talk')?'active':''}}">--}}
{{--                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-3"><span class="icon">💭</span><span class="text">ササッとTalk</span></a>--}}
{{--                <ul id="sub-menu-3" class="collapse sub-menu">--}}
{{--                    <li class="item-sub {{Request::is('talk/management-server')?'active':''}}"><a class="nav-link" href="{{url('talk/management-server')}}">サーバ情報</a></li>--}}
{{--                    <li class="item-sub {{Request::is('talk/management-tenant')?'active':''}}"><a class="nav-link" href="{{url('talk/management-tenant')}}">テナント情報</a></li>--}}
{{--                </ul>--}}
{{--            </li>--}}
        @else
            @php
                $loggerCompany = \App\Http\Utils\AppUtils::getLoggedCompany(\App\Http\Utils\GwAppApiUtils::SCHEDULE_FLG);
                $CompanyStampGroup = \App\Http\Utils\AppUtils::getStampGroup();
                $specialSiteReceiveSendAvailableState = \App\Http\Utils\AppUtils::getSpecial();
            @endphp

            @can(PermissionUtils::PERMISSION_USAGE_SITUATION_VIEW)
                <li class="nav-item {{Request::is('reports/usage')?'active':''}}">
                    <a href="{{Route('Reports.Usage.Show')}}" class="nav-link"><span class="icon" style="{{(session('stamp_is_over') == 1 ? 'background:#f00;border-radius:90%;width:8px;height:8px;' :'')}}"><i class="fas fa-chalkboard-teacher"></i></span><span class="text">利用状況</span></a>
                </li>
            @endcan
            @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW, PermissionUtils::PERMISSION_USER_HISTORY_VIEW])
                <li class="nav-item {{ Request::is('operation-history/*') ?'active':''}}">
                    <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-1"><span class="icon"><i class="fas fa-history"></i></span><span class="text">操作履歴</span></a>
                    <ul id="sub-menu-1" class="collapse sub-menu {{ Request::is('operation-history/*') ?'show':''}}">
                        @can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)
                            <li class="item-sub {{Request::is('operation-history/admin')?'active':''}}"><a class="nav-link" href="{{url('operation-history',['type' => 'admin'])}}">管理者操作履歴</a></li>
                        @endcan
                        @can(PermissionUtils::PERMISSION_USER_HISTORY_VIEW)
                            <li class="item-sub {{Request::is('operation-history/user')?'active':''}}"><a class="nav-link" href="{{url('operation-history',['type' => 'user'])}}">利用者操作履歴</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany
            @canany([PermissionUtils::PERMISSION_BRANDING_SETTINGS_VIEW, PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW, PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW,
                PermissionUtils::PERMISSION_DATE_STAMP_SETTING_VIEW, PermissionUtils::PERMISSION_LIMIT_SETTING_VIEW,PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW, PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_VIEW,
                PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW, PermissionUtils::PERMISSION_PROTECTION_SETTING_VIEW, PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW])
                <li ng-show="flg==2" class="nav-item {{ Request::is('global-setting/*') ?'active':''}}">
                    <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2"><span class="icon"><i class="fas fa-cogs"></i></span><span class="text">全体設定</span></a>
                    <ul id="sub-menu-2" class="collapse sub-menu {{ Request::is('global-setting/*') ?'show':''}}">
                        @canany([PermissionUtils::PERMISSION_BRANDING_SETTINGS_VIEW])
                            <li class="item-sub {{Request::is('global-setting/branding')?'active':''}}"><a class="nav-link" href="{{Route('GlobalSetting.Branding')}}">ブランディング設定</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/authority')?'active':''}}"><a class="nav-link" href="{{Route('GlobalSetting.Authority')}}">管理者権限初期値設定</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW])
                            <li class="item-sub {{Request::is('global-setting/password-policy')?'active':''}}"><a class="nav-link" href="{{Route('GlobalSetting.PasswordPolicy')}}">パスワードポリシー設定</a></li>
                        @endcanany
                        @if($loggerCompany->contract_edition != 4)
                        @canany([PermissionUtils::PERMISSION_DATE_STAMP_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/date-stamp')?'active':''}}"><a href="{{url('global-setting/date-stamp')}}" class="nav-link">日付印設定</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/company-stamp')?'active':''}}"><a href="{{url('global-setting/company-stamp')}}" class="nav-link">共通印設定</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_LIMIT_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/limit')?'active':''}}"><a href="{{url('global-setting/limit')}}" class="nav-link">制限設定</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_PROTECTION_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/protection')?'active':''}}"><a href="{{url('global-setting/protection')}}" class="nav-link">保護設定</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/ip-restriction')?'active':''}}"><a href="{{url('global-setting/ip-restriction')}}" class="nav-link">接続IP制限設定</a></li>
                        @endcanany
                        @if($loggerCompany->signature_flg)
                        @canany([PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW])
                            <li class="item-sub {{Request::is('global-setting/signature')?'active':''}}"><a href="{{url('global-setting/signature')}}" class="nav-link">電子証明書設定</a></li>
                        @endcanany
                        @endif
                        @if($loggerCompany->box_enabled)
                            @canany([PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/box-enabled-auto-storage')?'active':''}}"><a href="{{url('global-setting/box-enabled-auto-storage')}}" class="nav-link">BOX自動保管</a></li>
                            @endcanany
                        @endif
                        @endif
                        </ul>
                </li>
            @endcanany
            @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW, PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_VIEW])
                <li ng-show="flg==2" class="nav-item {{(Request::is('setting-admin') OR Request::is('setting-stamp-group')) ?'active':''}}">
                    <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-4"><span class="icon"><i class="fas fa-user-shield"></i></span><span class="text">管理者設定</span></a>
                    <ul id="sub-menu-4" class="collapse sub-menu {{ Request::is('setting-admin*') ?'show':''}}">
                        @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW])
                            <li class="item-sub {{Request::is('setting-admin')?'active':''}}"><a class="nav-link" href="{{url('setting-admin')}}">管理者設定</a></li>
                        @endcanany
                        @if($CompanyStampGroup)
                                @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_VIEW])
                                <li class="item-sub {{Request::is('setting-admin/setting-stamp-group')?'active':''}}"><a class="nav-link" href="{{url('setting-admin/setting-stamp-group')}}">共通印グループ管理者割当</a></li>
                                @endcanany
                        @endif
                    </ul>
                </li>
{{--                <li class="nav-item {{Request::is('setting-admin')?'active':''}}"><a href="{{url('setting-admin')}}" class="nav-link"><span class="icon"><i class="fas fa-user-shield"></i></span><span class="text">管理者設定</span></a></li>--}}
            @endcanany
            @canany([PermissionUtils::PERMISSION_USER_SETTINGS_VIEW, PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW, PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW,PermissionUtils::PERMISSION_OPTION_USERS_VIEW, PermissionUtils::PERMISSION_RECEIVE_USERS_VIEW])
                <li ng-show="flg==2" class="nav-item {{(Request::is('setting-user*') OR Request::is('user-setting-stamp')) ?'active':''}}">
                    <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-3"><span class="icon"><i class="fas fa-user-cog"></i></span><span class="text">利用者設定</span></a>
                    <ul id="sub-menu-3" class="collapse sub-menu {{ Request::is('setting-user*') ?'show':''}}">
                        @if($loggerCompany->contract_edition != 4)
                        @canany([PermissionUtils::PERMISSION_USER_SETTINGS_VIEW])
                            <li class="item-sub {{Request::is('setting-user')?'active':''}}"><a class="nav-link" href="{{url('setting-user')}}">利用者設定</a></li>
                        @endcanany
                        @endif
                        @if($loggerCompany->option_user_flg)
                        @canany([PermissionUtils::PERMISSION_OPTION_USERS_VIEW])
                            <li class="item-sub {{Request::is('setting-user/option-user')?'active':''}}"><a class="nav-link" href="{{url('setting-user/option-user')}}">グループウェア専用利用者</a></li>
                        @endcanany
                        @endif
                        @if($loggerCompany->receive_user_flg)
                        @canany([PermissionUtils::PERMISSION_RECEIVE_USERS_VIEW])
                             <li class="item-sub {{Request::is('setting-user/receive-user')?'active':''}}"><a class="nav-link" href="{{url('setting-user/receive-user')}}">受信専用利用者</a></li>
                        @endcanany
                        @endif
                        @if($loggerCompany->contract_edition != 4)
                        @canany([PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW])
                            <li class="item-sub {{Request::is('setting-user/assign-stamp')?'active':''}}"><a class="nav-link" href="{{url('setting-user/assign-stamp')}}">共通印割当</a></li>
                        @endcanany
                        @endif
                        @if($loggerCompany->long_term_storage_flg)
                        @canany([PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW])
                            <li class="item-sub {{Request::is('setting-user/setting-audit-account')?'active':''}}"><a class="nav-link" href="{{url('setting-user/setting-audit-account')}}">監査用アカウント設定</a></li>
                        @endcanany
                        @endif
                    </ul>
                </li>
            @endcanany
            @if(!$loggerCompany->addressbook_only_flag)
            @canany([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_VIEW])
                <li ng-show="flg==2" class="nav-item {{Request::is('setting-address-common')?'active':''}} "><a class="nav-link" href="{{url('setting-address-common')}}" class="nav-link"><span class="icon"><i class="fas fa-address-book"></i></span><span class="text">共通アドレス帳</span></a></li>
            @endcanany
            @endif
           {{-- @canany([PermissionUtils::PERMISSION_APPROVAL_ROUTE_VIEW, PermissionUtils::PERMISSION_APPROVAL_ROUTE_CREATE, PermissionUtils::PERMISSION_APPROVAL_ROUTE_UPDATE])
                <li class="nav-item"><a class="nav-link" href="" class="nav-link"><span class="icon"><i class="fas fa-route"></i></span><span class="text">承認ルート</span></a></li>
            @endcanany--}}
            @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_VIEW])
                {{--部署・役職--}}
                <li ng-show="flg==2" class="nav-item {{Request::is('department-title')?'active':''}}"><a class="nav-link" href="{{url('department-title')}}" class="nav-link"><span class="icon"><i class="fas fa-folder-open"></i></span><span class="text">部署・役職</span></a></li>
            @endcanany
            @if($loggerCompany->template_route_flg)
                @canany([PermissionUtils::PERMISSION_TEMPLATE_ROUTE_VIEW])
                    {{--承認ルート--}}
                    <li ng-show="flg==2" class="nav-item {{Request::is('template-route')?'active':''}}"><a class="nav-link" href="{{url('template-route')}}" class="nav-link"><span class="icon"><i class="fas fa-retweet"></i></span><span class="text">承認ルート</span></a></li>
                @endcanany
            @endif
            @if($loggerCompany->contract_edition != 4)
            @canany([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                {{--回覧一覧--}}
                <li ng-show="flg==2" class="nav-item {{Request::is('circulars')?'active':''}}"><a class="nav-link" href="{{route('Circulars.Index')}}" class="nav-link"><span class="icon"><i class="fas fa-list-alt"></i></span><span class="text">回覧一覧</span></a></li>
                {{--添付ファイル一覧--}}
                @if($loggerCompany->attachment_flg)
                    @canany([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                        <li ng-show="flg==2" class="nav-item {{Request::is('attachments')?'active':''}}"><a class="nav-link" href="{{route('Attachments.Index')}}" class="nav-link"><span class="icon"><i class="fas fa-list-ul"></i></span><span class="text">添付ファイル一覧</span></a></li>
                    @endcanany
                @endif
                {{--保存文書一覧--}}
                @canany([PermissionUtils::PERMISSION_CIRCULARS_SAVED_VIEW])
                    <li ng-show="flg==2" class="nav-item {{Request::is('circulars-saved')?'active':''}}"><a class="nav-link" href="{{route('Circulars.Saved')}}" class="nav-link"><span class="icon"><i class="far fa-save"></i></span><span class="text">保存文書一覧</span></a></li>
                @endcanany
            @endcanany
            @endif
            @if($loggerCompany->long_term_storage_flg)
            @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW, PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW, PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW, PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW])
                <li ng-show="flg==2" class="nav-item {{(Request::is('circulars-long-term') OR Request::is('long-term/*') ) ?'active':''}}">
                    <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-8"><span class="icon"><i class="fas fa-archive"></i></span><span class="text">長期保管</span></a>
                    <ul id="sub-menu-8" class="collapse sub-menu {{ (Request::is('circulars-long-term') OR Request::is('long-term/*') )?'show':''}}">
                        @if($loggerCompany->long_term_storage_flg)
                            @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW])
                                <li class="item-sub {{Request::is('long-term/long-term-save')?'active':''}}"><a href="{{url('long-term/long-term-save')}}" class="nav-link">長期保管設定</a></li>
                            @endcanany
                        @endif
                        @if($loggerCompany->long_term_storage_flg && $loggerCompany->long_term_storage_option_flg)
                            @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW])
                                <li class="item-sub {{Request::is('long-term/long-term-index')?'active':''}}"><a href="{{url('long-term/long-term-index')}}" class="nav-link">長期保管インデックス設定</a></li>
                            @endcanany
                        @endif
                        @if($loggerCompany->long_term_storage_flg && $loggerCompany->long_term_folder_flg)
                            @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW])
                                <li class="item-sub {{Request::is('long-term/long-term-folder')?'active':''}}"><a href="{{url('long-term/long-term-folder')}}" class="nav-link">長期保管フォルダ管理</a></li>
                            @endcanany
                        @endif
                        @if($loggerCompany->long_term_storage_flg && $loggerCompany->contract_edition != 4)
                            @canany([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                                <li class="item-sub {{Request::is('circulars-long-term')?'active':''}}"><a href="{{route('Circulars.LongTerm')}}" class="nav-link" >長期保管文書一覧</a></li>
                            @endcanany
                        @endif
                    </ul>
                </li>
            @endcanany
            @endif
                {{--名刺一覧--}}
                @if($loggerCompany->bizcard_flg)
                    @canany([PermissionUtils::PERMISSION_BIZ_CARDS_VIEW])
                        <li ng-show="flg==2" class="nav-item {{Request::is('bizcards')?'active':''}}"><a class="nav-link" href="{{route('Bizcards.Index')}}" class="nav-link"><span class="icon"><i class="fas fa-id-card"></i></span><span class="text">名刺一覧</span></a></li>
                    @endcanany
                @endif
                {{--PAC_5-1769 回覧一覧の参照権限がないユーザーはCSV出力ボタンは押せるがダウンロード状況確認がメニューに表示されないためダウンロードできない--}}
                <li ng-show="flg==2" class="nav-item {{Request::is('circulars-downloadlist')?'active':''}}"><a class="nav-link" href="{{route('Circulars.DownloadList')}}" class="nav-link"><span class="icon"><i class="fas fa-cloud-download-alt"></i></span><span class="text">ダウンロード状況確認</span></a></li>
                {{--PAC_5-2799 S--}}
                {{--回覧完了テンプレート一覧--}}
                @if($loggerCompany->template_flg && $loggerCompany->template_csv_flg)
                @canany([PermissionUtils::PERMISSION_TEMPLATE_CSV_VIEW])
                    <li ng-show="flg==2" class="nav-item {{Request::is('Template-csv')?'active':''}}"><a class="nav-link" href="{{url('Template-csv')}}" class="nav-link"><span class="icon"><i class="fas fa-download"></i></span><span class="text">回覧完了テンプレート一覧</span></a></li>
                @endcanany
                @endif
                {{--PAC_5-2799 E--}}
                {{--パスワード変更--}}
                <li ng-show="flg==2" class="nav-item {{Request::is('setting-admin/change-password')?'active':''}}"><a class="nav-link" href="{{url('/setting-admin/change-password')}}" class="nav-link"><span class="icon"><i class="fas fa-lock"></i></span><span class="text">パスワード変更</span></a></li>
                @if($loggerCompany->template_flg)
                {{--テンプレート--}}
                @canany([PermissionUtils::PERMISSION_TEMPLATE_INDEX_VIEW])
                    <li ng-show="flg==2" class="nav-item {{Request::is('template-index')?'active':''}}"><a class="nav-link" href="{{url('template-index')}}" class="nav-link"><span class="icon"><i class="fas fa-file-alt"></i></span><span class="text">テンプレート</span></a></li>
                @endcanany
                @endif
            {{--  PAC_14-37 MOD START --}}
            {{--@if($loggerCompany->portal_flg)--}}
            @if($loggerCompany->portal_flg && ($loggerCompany->gw_flg || $loggerCompany->board_flg))
{{--            --}}{{--  PAC_14-37 MOD END --}}
                 @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW, PermissionUtils::PERMISSION_APP_ROLE_SETTING_VIEW, PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW,PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_VIEW,PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW, PermissionUtils::PERMISSION_ADMIN_TIMECARD_SETTING_VIEW,PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])

                    <li ng-show="flg==2" class="nav-item {{(Request::is('setting-groupware/*') OR Request::is('attendance/*')) ?'active':''}}">
                        <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-5"><span class="icon"><i class="fas fa-wrench"></i></span><span class="text">グループウェア設定</span></a>
                        <ul id="sub-menu-5" class="collapse sub-menu {{ (Request::is('setting-groupware/*') OR Request::is('attendance/users')) ?'show':''}}">
                            @if($loggerCompany->gw_flg)
                            @canany([PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_VIEW])
                                <li class="item-sub {{Request::is('setting-groupware/master-sync')?'active':''}}"><a href="{{url('setting-groupware/master-sync')}}" class="nav-link">マスタ同期設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW])
                                <li class="item-sub {{Request::is('setting-groupware/app-use')?'active':''}}"><a href="{{url('setting-groupware/app-use')}}" class="nav-link">アプリ利用設定</a></li>
                            @endcanany
                            @endif
                            @canany([PermissionUtils::PERMISSION_APP_ROLE_SETTING_VIEW])
                                <li class="item-sub {{Request::is('setting-groupware/app-role')?'active':''}}"><a href="{{url('setting-groupware/app-role')}}" class="nav-link">アプリロール設定</a></li>
                            @endcanany
                            @if($loggerCompany->scheduler_flg ==1)
                            @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW,PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW,PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW,PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])
                                <li class="nav-item" style="margin-left: -15px;">
                                    <a class="nav-link white-text" href="#" data-toggle="collapse" aria-expanded="{{(Request::is('setting-groupware/facility') OR Request::is('setting-groupware/holiday') OR Request::is('setting-groupware/colorCategoryList') OR Request::is('setting-groupware/show-schedule')) ? 'true' : 'false'}}" data-target="#sub-menu-7"><span class="text">スケジューラ設定</span></a>
                                    <ul id="sub-menu-7" class="collapse sub-menu {{ (Request::is('setting-groupware/facility') OR Request::is('setting-groupware/holiday')  OR Request::is('setting-groupware/colorCategoryList') OR Request::is('setting-groupware/show-schedule')) ?'show':''}}">
                                        @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW])
                                            <li class="item-sub {{Request::is('setting-groupware/show-schedule')?'active':''}}"><a href="{{url('setting-groupware/show-schedule')}}" class="nav-link">スケジューラ制限設定</a></li>
                                        @endcanany
                                        @canany([PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW])
                                            <li class="item-sub {{Request::is('setting-groupware/colorCategoryList')?'active':''}}"><a class="nav-link" href="{{url('setting-groupware/colorCategoryList')}}">カテゴリ設定</a></li>
                                        @endcanany
                                        @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])
                                            <li class="item-sub {{Request::is('setting-groupware/holiday')?'active':''}}"><a class="nav-link" href="{{url('setting-groupware/holiday')}}">休日設定</a></li>
                                        @endcanany
                                        @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW])
                                            <li class="item-sub {{Request::is('setting-groupware/facility')?'active':''}}"><a class="nav-link" href="{{url('setting-groupware/facility')}}">設備</a></li>
                                        @endcanany
                                    </ul>
                                </li>
                            @endcanany
                            @endif
                            {{--PAC_5-2246 STARAT--}}
                            @if($loggerCompany->attendance_flg ==1)
                            @canany([PermissionUtils::PERMISSION_ADMIN_TIMECARD_SETTING_VIEW])
                                  <li class="item-sub {{Request::is('attendance/users')?'active':''}}"><a href="{{url('attendance/users')}}" class="nav-link">タイムカード</a></li>
                            @endcanany
                            @endif
                            {{--PAC_5-2246 END--}}
                        </ul>
                    </li>
                 @endcanany
            @endif


            {{--PAC_5-1902--}}
            @if($specialSiteReceiveSendAvailableState && ($specialSiteReceiveSendAvailableState->is_special_site_receive_available || $specialSiteReceiveSendAvailableState->is_special_site_send_available))
            @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_VIEW, PermissionUtils::PERMISSION_SPECIAL_SITE_RECEIVE_VIEW, PermissionUtils::PERMISSION_SPECIAL_SITE_SEND_VIEW])
            <li class="nav-item {{(Request::is('special-upload') OR Request::is('special-receive') OR Request::is('special-send'))  ?'active':''}}">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2-2"><span class="icon"><i class="fas fa-cog"></i></span><span class="text">特設サイト</span></a>
                <ul id="sub-menu-2-2" class="collapse sub-menu {{ (Request::is('special-upload') OR Request::is('special-receive') OR Request::is('special-send')) ?'show':''}}">

                    @if($specialSiteReceiveSendAvailableState->is_special_site_receive_available == 1)
                        @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_VIEW])
                        <li class="item-sub {{Request::is('special-upload')?'active':''}}"><a class="nav-link" href="{{route('SpecialUpload.Index')}}">文書登録</a></li>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_RECEIVE_VIEW])
                        <li class="item-sub {{Request::is('special-receive')?'active':''}}"><a class="nav-link" href="{{route('SpecialReceive.Index')}}">連携承認</a></li>
                        @endcanany
                    @endif
                    @if($specialSiteReceiveSendAvailableState->is_special_site_send_available == 1)
                        @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_SEND_VIEW])
                        <li class="item-sub {{Request::is('special-send')?'active':''}}"><a class="nav-link" href="{{route('SpecialSend.Index')}}">連携申請</a></li>
                        @endcanany
                    @endif
                </ul>
            </li>
            @endcanany
            @endif

            @if(!$loggerCompany == NULL && $loggerCompany->hr_flg)
            @canany([PermissionUtils::PERMISSION_HR_USER_SETTING_VIEW,PermissionUtils::PERMISSION_HR_ADMIN_SETTING_VIEW,PermissionUtils::PERMISSION_HR_WORKING_HOUR_VIEW])
            <li ng-show="flg==2" class="nav-item {{(Request::is('companies') OR Request::is('hr-user-reg') OR Request::is('hr-admin-reg') OR Request::is('hr-working-hours'))?'active':''}}">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-6"><span class="icon">📅</span><span class="text">出退勤管理</span></a>
                <ul id="sub-menu-6" class="collapse sub-menu {{(Request::is('companies') OR Request::is('hr-user-reg') OR Request::is('hr-admin-reg') OR Request::is('hr-working-hours'))?'show':''}}">
                    @canany([PermissionUtils::PERMISSION_HR_USER_SETTING_VIEW])
                    <li class="item-sub {{Request::is('hr-user-reg')?'active':''}}"><a class="nav-link" href="{{url('hr-user-reg')}}">利用ユーザ登録</a></li>
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_HR_ADMIN_SETTING_VIEW])
                    <li class="item-sub {{Request::is('hr-admin-reg')?'active':''}}"><a class="nav-link" href="{{url('hr-admin-reg')}}">管理ユーザ登録</a></li>
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_HR_WORKING_HOUR_VIEW])
                    <li class="item-sub {{Request::is('hr-working-hours')?'active':''}}"><a class="nav-link" href="{{url('hr-working-hours')}}">就労時間管理</a></li>
                    @endcanany
                </ul>
            </li>
            @endcanany
            @endif

{{--        PAC_5-2663--}}
            @if($loggerCompany->chat_flg)
                @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_VIEW])
                    <li ng-show="flg==2" class="nav-item {{Request::is('chat')?'active':''}}">
                        <a class="nav-link" href="#"  data-toggle="collapse" data-target="#sub-menu-chat"><span class="icon"><i class="fas fa-comment"></i></span><span class="text">ササッとTalk設定</span></a>
                        <ul id="sub-menu-chat" class="collapse sub-menu {{ Request::is('chat/*') ?'show':''}}">
                            <li class="item-sub {{Request::is('chat/management-user')?'active':''}}"><a class="nav-link" href="{{url('chat/management-user')}}">ササッとTalk利用者設定</a></li>
                        </ul>
                    </li>
                @endcanany
            @endif

            {{--PAC_5-2799 S--}}
            {{--派遣機能--}}
            @if($loggerCompany->dispatch_flg)
            @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW], [PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW], [PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW])
            <li ng-show="flg==2" class="nav-item {{(Request::is('dispatcharea') OR Request::is('contract') OR Request::is('dispatchhr'))?'active':''}}">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-7"><span class="icon"><i class="fas fa-project-diagram"></i></span><span class="text">派遣管理</span></a>
                <ul id="sub-menu-7" class="collapse sub-menu {{(Request::is('dispatcharea') OR Request::is('contract') OR Request::is('dispatchhr'))?'show':''}}">
                    @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW])
                    <li class="item-sub {{Request::is('dispatcharea')?'active':''}}"><a class="nav-link" href="{{url('dispatcharea')}}">派遣先管理</a></li>
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW])
                    <li class="item-sub {{Request::is('contract')?'active':''}}"><a class="nav-link" href="{{url('contract')}}">契約管理</a></li>
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW])
                    <li class="item-sub {{Request::is('dispatchhr')?'active':''}}"><a class="nav-link" href="{{url('dispatchhr')}}">人材管理</a></li>
                    @endcanany
                </ul>
            </li>
            @endcanany
            @endif
            {{--PAC_5-2799 E--}}
            @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW, PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_VIEW,PermissionUtils::PERMISSION_USER_SETTINGS_VIEW,
                    PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW, PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW,PermissionUtils::PERMISSION_DEPARTMENT_TITLE_VIEW,
                    PermissionUtils::PERMISSION_TEMPLATE_ROUTE_VIEW, PermissionUtils::PERMISSION_OPTION_USERS_VIEW])
            <li ng-show="flg==1" class="nav-item {{ (Request::is('setting-admin') OR Request::is('setting-stamp-group') OR Request::is('setting-admin/setting-stamp-group') OR Request::is('setting-user*') OR Request::is('user-setting-stamp') OR Request::is('department-title') OR Request::is('template-route') OR Request::is('setting-user/option-user')) ?'active':''}}">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2-1"><span class="icon"><i class="fas fa-cog"></i></span><span class="text">基本設定</span></a>
                <ul id="sub-menu-2-1" class="collapse sub-menu {{ (Request::is('setting-admin') OR Request::is('setting-stamp-group') OR Request::is('setting-admin/setting-stamp-group') OR Request::is('setting-user*') OR Request::is('user-setting-stamp') OR Request::is('department-title') OR Request::is('template-route') OR Request::is('setting-user/option-user')) ?'show':''}}">
                @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW, PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_VIEW])
                    <li class="nav-item {{(Request::is('setting-admin') OR Request::is('setting-stamp-group') OR Request::is('setting-admin/setting-stamp-group')) ?'active':''}}">
                        <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2-1-2"><span class="icon"><i class="fas fa-user-shield"></i></span><span class="text">管理者設定</span></a>
                        <ul id="sub-menu-2-1-2" class="collapse sub-menu {{ (Request::is('setting-admin') OR Request::is('setting-admin/setting-stamp-group')) ?'show':''}}">
                            @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_VIEW])
                                <li class="item-sub {{Request::is('setting-admin')?'active':''}}"><a class="nav-link" href="{{url('setting-admin')}}">管理者設定</a></li>
                            @endcanany
                            @if($CompanyStampGroup)
                                @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('setting-admin/setting-stamp-group')?'active':''}}"><a class="nav-link" href="{{url('setting-admin/setting-stamp-group')}}">共通印グループ管理者割当</a></li>
                                @endcanany
                            @endif
                </ul>
            </li>
                    {{--                <li class="nav-item {{Request::is('setting-admin')?'active':''}}"><a href="{{url('setting-admin')}}" class="nav-link"><span class="icon"><i class="fas fa-user-shield"></i></span><span class="text">管理者設定</span></a></li>--}}
            @endcanany
                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_VIEW, PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW, PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW, PermissionUtils::PERMISSION_OPTION_USERS_VIEW,PermissionUtils::PERMISSION_RECEIVE_USERS_VIEW])
                    <li class="nav-item {{(Request::is('setting-user*') OR Request::is('user-setting-stamp') OR Request::is('setting-user/option-user')) ?'active':''}}">
                        <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2-1-3"><span class="icon"><i class="fas fa-user-cog"></i></span><span class="text">利用者設定</span></a>
                        <ul id="sub-menu-2-1-3" class="collapse sub-menu {{ Request::is('setting-user*') ?'show':''}}">
                            @if($loggerCompany->contract_edition != 4)
                            @canany([PermissionUtils::PERMISSION_USER_SETTINGS_VIEW])
                                <li class="item-sub {{Request::is('setting-user')?'active':''}}"><a class="nav-link" href="{{url('setting-user')}}">利用者設定</a></li>
                            @endcanany
                            @endif
                            @if($loggerCompany->option_user_flg)
                                 @canany([PermissionUtils::PERMISSION_OPTION_USERS_VIEW])
                                        <li class="item-sub {{Request::is('setting-user/option-user')?'active':''}}"><a class="nav-link" href="{{url('setting-user/option-user')}}">グループウェア専用利用者</a></li>
                                    @endcanany
                            @endif
                            @if($loggerCompany->receive_user_flg)
                                 @canany([PermissionUtils::PERMISSION_RECEIVE_USERS_VIEW])
                                    <li class="item-sub {{Request::is('setting-user/receive-user')?'active':''}}"><a class="nav-link" href="{{url('setting-user/receive-user')}}">受信専用利用者</a></li>
                                 @endcanany
                            @endif
                            @if($loggerCompany->contract_edition != 4)
                            @canany([PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_VIEW])
                                <li class="item-sub {{Request::is('setting-user/assign-stamp')?'active':''}}"><a class="nav-link" href="{{url('setting-user/assign-stamp')}}">共通印割当</a></li>
                            @endcanany
                            @endif
                            @if($loggerCompany->long_term_storage_flg)
                                @canany([PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('setting-user/setting-audit-account')?'active':''}}"><a class="nav-link" href="{{url('setting-user/setting-audit-account')}}">監査用アカウント設定</a></li>
                                @endcanany
                            @endif
                        </ul>
                    </li>
                @endcanany
                @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_VIEW])
                    {{--部署・役職--}}
                    <li class="nav-item {{Request::is('department-title')?'active':''}}"><a class="nav-link" href="{{url('department-title')}}" class="nav-link"><span class="icon"><i class="fas fa-folder-open"></i></span><span class="text">部署・役職</span></a></li>
                @endcanany
                @if($loggerCompany->template_route_flg)
                    @canany([PermissionUtils::PERMISSION_TEMPLATE_ROUTE_VIEW])
                        {{--承認ルート--}}
                        <li class="nav-item {{Request::is('template-route')?'active':''}}"><a class="nav-link" href="{{url('template-route')}}" class="nav-link"><span class="icon"><i class="fas fa-retweet"></i></span><span class="text">承認ルート</span></a></li>
                    @endcanany
                @endif
            </ul>
            </li>
            @endcanany

            @canany([PermissionUtils::PERMISSION_BRANDING_SETTINGS_VIEW, PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW, PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW,
                    PermissionUtils::PERMISSION_DATE_STAMP_SETTING_VIEW, PermissionUtils::PERMISSION_LIMIT_SETTING_VIEW,PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW, PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_VIEW,
                    PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW, PermissionUtils::PERMISSION_PROTECTION_SETTING_VIEW, PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW,PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW,PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_VIEW,
                    PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW, PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW, PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW, PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_VIEW,PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW,PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW,PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW])
            <li ng-show="flg==1" class="nav-item {{ (Request::is('global-setting/*') OR Request::is('circulars') OR Request::is('attachments') OR Request::is('circulars-saved') OR Request::is('circulars-long-term') OR Request::is('circulars-downloadlist') OR Request::is('setting-address-common') OR Request::is('template-index') OR Request::is('long-term/*') OR Request::is('form-issuance/*') OR Request::is('dispatcharea') OR Request::is('contract') OR Request::is('dispatchhr') OR Request::is('Template-csv')) ?'active':''}}">
            <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-3-1"><span class="icon"><i class="fas fa-ellipsis-h"></i></span><span class="text">機能情報</span></a>
            <ul id="sub-menu-3-1" class="collapse sub-menu {{ (Request::is('global-setting/*') OR Request::is('circulars') OR Request::is('attachments') OR Request::is('circulars-saved') OR Request::is('circulars-long-term') OR Request::is('circulars-downloadlist') OR Request::is('setting-address-common') OR Request::is('template-index') OR Request::is('long-term/*') OR Request::is('form-issuance/*') OR Request::is('dispatcharea') OR Request::is('contract') OR Request::is('dispatchhr') OR Request::is('Template-csv')) ?'show':''}}">
                @canany([PermissionUtils::PERMISSION_BRANDING_SETTINGS_VIEW, PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW, PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW,
                      PermissionUtils::PERMISSION_DATE_STAMP_SETTING_VIEW, PermissionUtils::PERMISSION_LIMIT_SETTING_VIEW,PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW, PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_VIEW,
                      PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW, PermissionUtils::PERMISSION_PROTECTION_SETTING_VIEW, PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW])
                    <li class="nav-item {{ Request::is('global-setting/*') ?'active':''}}">
                        <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2-1-1"><span class="icon"><i class="fas fa-cogs"></i></span><span class="text">全体設定</span></a>
                        <ul id="sub-menu-2-1-1" class="collapse sub-menu {{ Request::is('global-setting/*') ?'show':''}}">
                            @canany([PermissionUtils::PERMISSION_BRANDING_SETTINGS_VIEW])
                                <li class="item-sub {{Request::is('global-setting/branding')?'active':''}}"><a class="nav-link" href="{{Route('GlobalSetting.Branding')}}">ブランディング設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/authority')?'active':''}}"><a class="nav-link" href="{{Route('GlobalSetting.Authority')}}">管理者権限初期値設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_VIEW])
                                <li class="item-sub {{Request::is('global-setting/password-policy')?'active':''}}"><a class="nav-link" href="{{Route('GlobalSetting.PasswordPolicy')}}">パスワードポリシー設定</a></li>
                            @endcanany
                            @if($loggerCompany->contract_edition != 4)
                            @canany([PermissionUtils::PERMISSION_DATE_STAMP_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/date-stamp')?'active':''}}"><a href="{{url('global-setting/date-stamp')}}" class="nav-link">日付印設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_COMMON_MARK_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/company-stamp')?'active':''}}"><a href="{{url('global-setting/company-stamp')}}" class="nav-link">共通印設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_LIMIT_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/limit')?'active':''}}"><a href="{{url('global-setting/limit')}}" class="nav-link">制限設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_PROTECTION_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/protection')?'active':''}}"><a href="{{url('global-setting/protection')}}" class="nav-link">保護設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/ip-restriction')?'active':''}}"><a href="{{url('global-setting/ip-restriction')}}" class="nav-link">接続IP制限設定</a></li>
                            @endcanany
                            @if($loggerCompany->signature_flg)
                            @canany([PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_VIEW])
                                <li class="item-sub {{Request::is('global-setting/signature')?'active':''}}"><a href="{{url('global-setting/signature')}}" class="nav-link">電子証明書設定</a></li>
                            @endcanany
                            @endif
                            @if($loggerCompany->box_enabled)
                                @canany([PermissionUtils::PERMISSION_BOX_ENABLED_AUTO_STORAGE_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('global-setting/box-enabled-auto-storage')?'active':''}}"><a href="{{url('global-setting/box-enabled-auto-storage')}}" class="nav-link">BOX自動保管</a></li>
                                @endcanany
                            @endif
                            @endif
                        </ul>
                    </li>
                @endcanany
                @if($loggerCompany->contract_edition != 4)
                @canany([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                    {{--回覧一覧--}}
                    <li class="nav-item {{Request::is('circulars')?'active':''}}"><a class="nav-link" href="{{route('Circulars.Index')}}" class="nav-link"><span class="icon"><i class="fas fa-list-alt"></i></span><span class="text">回覧一覧</span></a></li>
                    {{--添付ファイル一覧--}}
                    @if($loggerCompany->attachment_flg)
                        @canany([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                            <li class="nav-item {{Request::is('attachments')?'active':''}}"><a class="nav-link" href="{{route('Attachments.Index')}}" class="nav-link"><span class="icon"><i class="fas fa-list-ul"></i></span><span class="text">添付ファイル一覧</span></a></li>
                        @endcanany
                    @endif
                    {{--保存文書一覧--}}
                    @canany([PermissionUtils::PERMISSION_CIRCULARS_SAVED_VIEW])
                        <li class="nav-item {{Request::is('circulars-saved')?'active':''}}"><a class="nav-link" href="{{route('Circulars.Saved')}}" class="nav-link"><span class="icon"><i class="far fa-save"></i></span><span class="text">保存文書一覧</span></a></li>
                    @endcanany
                @endcanany
                @endif
                    @if($loggerCompany->long_term_storage_flg)
                    @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW, PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW, PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW, PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                        <li class="nav-item {{(Request::is('circulars-long-term') OR Request::is('long-term/*') ) ?'active':''}}">
                            <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-2-2-1"><span class="icon"><i class="fas fa-archive"></i></span><span class="text">長期保管</span></a>
                            <ul id="sub-menu-2-2-1" class="collapse sub-menu {{ (Request::is('circulars-long-term') OR Request::is('long-term/*'))?'show':''}}">
                                @if($loggerCompany->long_term_storage_flg)
                                    @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_VIEW])
                                        <li class="item-sub {{Request::is('long-term/long-term-save')?'active':''}}"><a href="{{url('long-term/long-term-save')}}" class="nav-link">長期保管設定</a></li>
                                    @endcanany
                                @endif
                                @if($loggerCompany->long_term_storage_flg && $loggerCompany->long_term_storage_option_flg)
                                    @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_VIEW])
                                        <li class="item-sub {{Request::is('long-term/long-term-index')?'active':''}}"><a href="{{url('long-term/long-term-index')}}" class="nav-link">長期保管インデックス設定</a></li>
                                    @endcanany
                                @endif
                                @if($loggerCompany->long_term_storage_flg && $loggerCompany->long_term_folder_flg)
                                    @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_VIEW])
                                        <li class="item-sub {{Request::is('long-term/long-term-folder')?'active':''}}"><a href="{{url('long-term/long-term-folder')}}" class="nav-link">長期保管フォルダ管理</a></li>
                                    @endcanany
                                @endif
                                @if($loggerCompany->long_term_storage_flg && $loggerCompany->contract_edition != 4)
                                    @canany([PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW])
                                    <li class="item-sub {{Request::is('circulars-long-term')?'active':''}}"><a href="{{route('Circulars.LongTerm')}}" class="nav-link" >長期保管文書一覧</a></li>
                                    @endcanany
                                @endif
                            </ul>
                        </li>
                    @endcanany
                    @endif
                {{--名刺一覧--}}
                @if($loggerCompany->bizcard_flg && $loggerCompany->contract_edition != 4)
                    @canany([PermissionUtils::PERMISSION_BIZ_CARDS_VIEW])
                        <li class="nav-item {{Request::is('bizcards')?'active':''}}"><a class="nav-link" href="{{route('Bizcards.Index')}}" class="nav-link"><span class="icon"><i class="fas fa-id-card"></i></span><span class="text">名刺一覧</span></a></li>
                    @endcanany
                @endif
                <li class="nav-item {{Request::is('circulars-downloadlist')?'active':''}}"><a class="nav-link" href="{{route('Circulars.DownloadList')}}" class="nav-link"><span class="icon"><i class="fas fa-cloud-download-alt"></i></span><span class="text">ダウンロード状況確認</span></a></li>
                @canany([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_VIEW])
                    <li class="nav-item {{Request::is('setting-address-common')?'active':''}}"><a class="nav-link" href="{{url('setting-address-common')}}" class="nav-link"><span class="icon"><i class="fas fa-address-book"></i></span><span class="text">共通アドレス帳</span></a></li>
                @endcanany
                {{--PAC_5-2711 S--}}
                @if($loggerCompany->template_flg)
                    {{--テンプレート--}}
                    @canany([PermissionUtils::PERMISSION_TEMPLATE_INDEX_VIEW])
                        <li ng-show="flg==1" class="nav-item {{Request::is('template-index')?'active':''}}"><a class="nav-link" href="{{url('template-index')}}" class="nav-link"><span class="icon"><i class="fas fa-file-alt"></i></span><span class="text">テンプレート</span></a></li>
                    @endcanany
                @endif
                {{--PAC_5-2711 E--}}
                {{--PAC_5-2799 S--}}
                {{--回覧完了テンプレート一覧--}}
                @if($loggerCompany->template_flg && $loggerCompany->template_csv_flg)
                    @canany([PermissionUtils::PERMISSION_TEMPLATE_CSV_VIEW])
                        <li class="nav-item {{Request::is('Template-csv')?'active':''}}"><a class="nav-link" href="{{url('Template-csv')}}" class="nav-link"><span class="icon"><i class="fas fa-download"></i></span><span class="text">回覧完了テンプレート一覧</span></a></li>
                    @endcanany
                @endif
                {{--派遣機能--}}
                @if($loggerCompany->dispatch_flg)
                    @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW], [PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW], [PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW])
                        <li class="nav-item {{(Request::is('dispatcharea') OR Request::is('contract') OR Request::is('dispatchhr'))?'active':''}}">
                            <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-7"><span class="icon"><i class="fas fa-project-diagram"></i></span><span class="text">派遣管理</span></a>
                            <ul id="sub-menu-7" class="collapse sub-menu {{(Request::is('dispatcharea') OR Request::is('contract') OR Request::is('dispatchhr'))?'show':''}}">
                                @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('dispatcharea')?'active':''}}"><a class="nav-link" href="{{url('dispatcharea')}}">派遣先管理</a></li>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('contract')?'active':''}}"><a class="nav-link" href="{{url('contract')}}">契約管理</a></li>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('dispatchhr')?'active':''}}"><a class="nav-link" href="{{url('dispatchhr')}}">人材管理</a></li>
                                @endcanany
                            </ul>
                        </li>
                    @endcanany
                @endif
                {{--ササッと明細--}}
                @if($loggerCompany->frm_srv_flg)
                    @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_VIEW])
                        <li class="nav-item {{ Request::is('form-issuance/*') ?'active':''}}">
                            <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-form-issuance"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span class="text">ササッと明細</span></a>
                            <ul id="sub-menu-form-issuance" class="collapse sub-menu {{ Request::is('form-issuance/*') ?'show':''}}">
                                <li class="item-sub {{Request::is('form-issuance/user-register')?'active':''}}"><a class="nav-link" href="{{url('form-issuance/user-register')}}">利用ユーザ登録</a></li>
                            </ul>
                        </li>
                    @endcanany
                @endif
                {{--PAC_5-2799 E--}}
            </ul>
        </li>
            @endcanany
            @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW, PermissionUtils::PERMISSION_APP_ROLE_SETTING_VIEW, PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW,PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_VIEW,
                    PermissionUtils::PERMISSION_HR_USER_SETTING_VIEW,PermissionUtils::PERMISSION_ADMIN_TIMECARD_SETTING_VIEW,PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])
            <li ng-show="flg==1" class="nav-item {{ (Request::is('setting-groupware/*') OR Request::is('setting-groupware/master-sync') OR Request::is('setting-groupware/app-use') OR Request::is('setting-groupware/app-role') OR Request::is('setting-groupware/facility')  OR  Request::is('setting-groupware/colorCategoryList') OR Request::is('companies') OR Request::is('hr-user-reg') OR Request::is('hr-admin-reg') OR Request::is('setting-admin/change-password') OR Request::is('setting-groupware/show-schedule') OR Request::is('setting-groupware/holiday')) ?'active':''}}">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-4-1"><span class="icon"><i class="fas fa-user"></i></span><span class="text">マイページ設定</span></a>
                <ul id="sub-menu-4-1" class="collapse sub-menu {{ (Request::is('setting-groupware/*') OR Request::is('setting-groupware/master-sync') OR Request::is('setting-groupware/app-use') OR Request::is('setting-groupware/app-role') OR Request::is('setting-groupware/facility')  OR  Request::is('setting-groupware/colorCategoryList') OR Request::is('companies') OR Request::is('hr-user-reg') OR Request::is('hr-admin-reg') OR Request::is('hr-working-hours') OR Request::is('setting-admin/change-password') OR Request::is('setting-groupware/show-schedule') OR Request::is('setting-groupware/holiday')) ?'show':''}}">
                @if($loggerCompany->portal_flg && ($loggerCompany->gw_flg || $loggerCompany->board_flg))
                    {{--  PAC_14-37 MOD END --}}
                    @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW, PermissionUtils::PERMISSION_APP_ROLE_SETTING_VIEW, PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW,PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_VIEW,PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW,PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])
                        <li class="nav-item {{(Request::is('setting-groupware/*') OR Request::is('setting-groupware/master-sync') OR Request::is('setting-groupware/app-use') OR Request::is('setting-groupware/app-role') OR Request::is('setting-groupware/facility')  OR  Request::is('setting-groupware/colorCategoryList') OR Request::is('setting-groupware/show-schedule') OR Request::is('attendance/user') OR Request::is('setting-groupware/holiday')) ? 'active' :''}}">
                            <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-4-1-1"><span class="icon"><i class="fas fa-wrench"></i></span><span class="text">グループウェア設定</span></a>
                            <ul id="sub-menu-4-1-1" class="collapse sub-menu {{ (Request::is('setting-groupware/*') OR Request::is('setting-groupware/user') OR Request::is('setting-groupware/master-sync') OR Request::is('setting-groupware/app-use') OR Request::is('setting-groupware/app-role') OR Request::is('setting-groupware/facility')  OR  Request::is('setting-groupware/colorCategoryList') OR Request::is('setting-groupware/show-schedule') OR Request::is('attendance/users') OR Request::is('setting-groupware/holiday')) ?'show':''}}">
                                @canany([PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('setting-groupware/master-sync')?'active':''}}"><a href="{{url('setting-groupware/master-sync')}}" class="nav-link">マスタ同期設定</a></li>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('setting-groupware/app-use')?'active':''}}"><a href="{{url('setting-groupware/app-use')}}" class="nav-link">アプリ利用設定</a></li>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_APP_ROLE_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('setting-groupware/app-role')?'active':''}}"><a href="{{url('setting-groupware/app-role')}}" class="nav-link">アプリロール設定</a></li>
                                @endcanany
                                @if($loggerCompany->scheduler_flg)
                                @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW,PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW,PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW,PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])
                                    <li class="nav-item">
                                        <a class="nav-link padding-left-0 padding-right-0" href="#" data-toggle="collapse" aria-expanded="{{(Request::is('setting-groupware/facility') OR Request::is('setting-groupware/holiday') OR  Request::is('setting-groupware/colorCategoryList') OR Request::is('setting-groupware/show-schedule')) ? 'true' : 'false'}}" data-target="#sub-menu-4-1-1-1"><span class="text">スケジューラ設定</span></a>
                                        <ul id="sub-menu-4-1-1-1" class="collapse sub-menu {{ (Request::is('setting-groupware/facility') OR Request::is('setting-groupware/holiday')  OR Request::is('setting-groupware/colorCategoryList') OR Request::is('setting-groupware/show-schedule') OR Request::is('attendance/user')) ?'show':''}}">
                                            @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW])
                                                <li class="item-sub {{Request::is('setting-groupware/show-schedule')?'active':''}}"><a href="{{url('setting-groupware/show-schedule')}}" class="nav-link">スケジューラ制限設定</a></li>
                                            @endcanany
                                            @canany([PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_VIEW])
                                                <li class="item-sub {{Request::is('setting-groupware/colorCategoryList')?'active':''}}"><a class="nav-link" href="{{url('setting-groupware/colorCategoryList')}}">カテゴリ設定</a></li>
                                            @endcanany
                                            @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW])
                                                <li class="item-sub {{Request::is('setting-groupware/holiday')?'active':''}}"><a class="nav-link" href="{{url('setting-groupware/holiday')}}">休日設定</a></li>
                                            @endcanany
                                            @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_VIEW])
                                                <li class="item-sub {{Request::is('setting-groupware/facility')?'active':''}}"><a class="nav-link" href="{{url('setting-groupware/facility')}}">設備</a></li>
                                            @endcanany
                                        </ul>
                                    </li>
                                @endcanany
                                @endif
                                {{--PAC_5-2246 STARAT--}}
                                @if($loggerCompany->attendance_flg ==1)
                                @canany([PermissionUtils::PERMISSION_ADMIN_TIMECARD_SETTING_VIEW])
                                     <li class="item-sub {{Request::is('attendance/users')?'active':''}}"><a href="{{url('attendance/users')}}" class="nav-link">タイムカード</a></li>
                                @endcanany
                                @endif
                                {{--PAC_5-2246 END--}}
                            </ul>
                        </li>
                    @endcanany
                @endif
                @if(!$loggerCompany == NULL && $loggerCompany->hr_flg)
                    @canany([PermissionUtils::PERMISSION_HR_ADMIN_SETTING_VIEW,PermissionUtils::PERMISSION_HR_USER_SETTING_VIEW,PermissionUtils::PERMISSION_HR_WORKING_HOUR_VIEW])
                        <li class="nav-item {{(Request::is('companies') OR Request::is('hr-user-reg') OR Request::is('hr-admin-reg') OR Request::is('hr-working-hours'))?'active':''}}">
                            <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-4-1-2"><span class="icon">📅</span><span class="text">出退勤管理</span></a>
                            <ul id="sub-menu-4-1-2" class="collapse sub-menu {{(Request::is('companies') OR Request::is('hr-user-reg') OR Request::is('hr-admin-reg') OR Request::is('hr-working-hours'))?'show':''}}">
                                @canany([PermissionUtils::PERMISSION_HR_USER_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('hr-user-reg')?'active':''}}"><a class="nav-link" href="{{url('hr-user-reg')}}">利用ユーザ登録</a></li>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_HR_ADMIN_SETTING_VIEW])
                                    <li class="item-sub {{Request::is('hr-admin-reg')?'active':''}}"><a class="nav-link" href="{{url('hr-admin-reg')}}">管理ユーザ登録</a></li>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_HR_WORKING_HOUR_VIEW])
                                    <li class="item-sub {{Request::is('hr-working-hour')?'active':''}}"><a class="nav-link" href="{{url('hr-working-hour')}}">就労時間管理</a></li>
                                @endcanany
                            </ul>
                        </li>
                    @endcanany
                @endif
                <li class="nav-item {{Request::is('setting-admin/change-password')?'active':''}}"><a class="nav-link" href="{{url('/setting-admin/change-password')}}" class="nav-link"><span class="icon"><i class="fas fa-lock"></i></span><span class="text">パスワード変更</span></a></li>
            </ul>
            </li>
            @endcanany
            {{-- PAC_5-2799 S --}}
            @if($loggerCompany->frm_srv_flg)
                @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_VIEW,PermissionUtils::PERMISSION_FRM_INDEX_SETTING_VIEW])
                    <li ng-show="flg==2" class="nav-item {{ Request::is('form-issuance/*') ?'active':''}}">
                        <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-form-issuance"><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span><span class="text">ササッと明細</span></a>
                        <ul id="sub-menu-form-issuance" class="collapse sub-menu {{ Request::is('form-issuance/*') ?'show':''}}">
                            @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_VIEW])
                                <li class="item-sub {{Request::is('form-issuance/user-register')?'active':''}}"><a class="nav-link" href="{{url('form-issuance/user-register')}}">利用ユーザ登録</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_FRM_INDEX_SETTING_VIEW])
                                <li class="item-sub {{Request::is('form-issuance/frm-index')?'active':''}}"><a class="nav-link" href="{{url('form-issuance/frm-index')}}">明細項目設定</a></li>
                            @endcanany
                        </ul>
                    </li>
                @endcanany
            @endif
            @if($loggerCompany->expense_flg)
                @canany([PermissionUtils::PERMISSION_ADMIN_EXPENSE_SETTING_VIEW,PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_VIEW,PermissionUtils::PERMISSION_STYLE_EXPENSE_SETTING_VIEW])
                    <li class="nav-item {{ Request::is('expense/*') ?'active':''}}">
                        <a class="nav-link" href="#" data-toggle="collapse" data-target="#sub-menu-expense"><span class="icon"><i class="far fa-folder-open"></i></span><span class="text">経費精算</span></a>
                        <ul id="sub-menu-expense" class="collapse sub-menu {{ Request::is('expense/*') ?'show':''}}">
                            @canany([PermissionUtils::PERMISSION_ADMIN_EXPENSE_SETTING_VIEW])
                               <li class="item-sub {{Request::is('expense/user-register')?'active':''}}"><a class="nav-link" href="{{url('expense/user-register')}}">利用ユーザ登録</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_VIEW])
                               <li class="item-sub {{Request::is('expense/m_purpose')?'active':''}}"><a class="nav-link" href="{{url('expense/m_purpose')}}">目的管理</a></li>
                               <li class="item-sub {{Request::is('expense/m_wtsm')?'active':''}}"><a class="nav-link" href="{{url('expense/m_wtsm')}}">用途管理</a></li>
                               <li class="item-sub {{Request::is('expense/m_account')?'active':''}}"><a class="nav-link" href="{{url('expense/m_account')}}">勘定科目管理</a></li>
                               <li class="item-sub {{Request::is('expense/m_journal_config')?'active':''}}"><a class="nav-link" href="{{url('expense/m_journal_config')}}">仕分設定</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_STYLE_EXPENSE_SETTING_VIEW])
                               <li class="item-sub {{Request::is('expense/m_form_adv')?'active':''}}"><a class="nav-link" href="{{url('expense/m_form_adv')}}">事前申請様式一覧</a></li>
                               <li class="item-sub {{Request::is('expense/m_form_exp')?'active':''}}"><a class="nav-link" href="{{url('expense/m_form_exp')}}">精算申請様式一覧</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_EXPENSE_APP_LIST_VIEW])
                               <li class="item-sub {{Request::is('expense/t_app')?'active':''}}"><a class="nav-link" href="{{url('expense/t_app')}}">経費申請一覧</a></li>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_VIEW])
                               <li class="item-sub {{Request::is('expense/t_journal')?'active':''}}"><a class="nav-link" href="{{url('expense/t_journal')}}">経費仕訳一覧</a></li>
                            @endcanany
                        </ul>
                    </li>
                @endcanany
            @endif
            {{-- PAC_5-2799 E --}}

        @endhasrole
</ul>

</nav>
{{-- PAC_5-520 START --}}
<li class="nav-item logout"><a class="nav-link" href="{{url('/logout')}}" class="nav-link"><span class="icon"><i class="fas fa-power-off"></i></span><span class="text">ログアウト</span></a>
    @hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
    @else
    <div class="custom-control custom-switch">
        <input  id="customSwitch1"  type="checkbox" class="custom-control-input" >
        <label class="custom-control-label" for="customSwitch1"></label>
    </div>
    @endhasrole
</li>
{{-- PAC_5-520 END --}}
{{--PAC_5-1904--}}
@hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
@else
@push("scripts")
    <script>
     var link_update_menu_state = "{{ action('Admin\SettingAdminController@updateMenuStateFlg') }}";
     var appPacAdmin = initAngularApp();
    $('.sidebar .sidebar-content .navbar .navbar-nav .nav-item .nav-link').each(function(){
        if($(this).attr('data-toggle')=='collapse'){$(this).append('<span class="bar-down-icon"><i class="fas fa-chevron-down"></i></span>')}
    })
    if(appPacAdmin){
        appPacAdmin.controller('navbar',function ($scope,$http){
            $scope.flg={{ Auth::user()->getReallyMenuStateFlg() }};
            $scope.title=function (){
                if ($scope.flg==2){
                    $('.custom-switch').prop('title','簡易表示に切り替え')
                }else{
                    $('.custom-switch').prop('title','通常表示に切り替え')
                }
            }
            $('#customSwitch1').prop('checked',$scope.flg==1?true:false)
            $scope.title()
            $('#customSwitch1').change(function (){
                 $scope.flg=$(this).prop('checked')?1:2;
                $scope.title()
                $http({
                    method:'POST',
                    url:link_update_menu_state,
                    data:{
                        menu_state_flg:$scope.flg
                    }
                })
                 $scope.$apply();
                 if ($scope.flg==1){
                     $(function (){
                         $('.sidebar .navbar .sub-menu').css({marginLeft:'30px'})
                         $('.sidebar .navbar .sub-menu .sub-menu').css({marginLeft:'40px'})
                     })
                 }else{
                     $('.sidebar .navbar .sub-menu').css({marginLeft:'50px'})
                 }
             })

            if ($scope.flg==1){
                $(function (){
                    $('.sidebar .navbar .sub-menu').css({marginLeft:'30px'})
                    $('.sidebar .navbar .sub-menu .sub-menu').css({marginLeft:'40px'})
                })
            }

        })
    }
    </script>
@endpush
@endhasrole
{{--PAC_5-1904--}}
