@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('GlobalSetting.Limit.Store') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div class="GlobalSetting Settings-limit">
        <div ng-controller="SettingsLimitController">
            <div class="message"></div>
            <div class="card mt-3">
                <div class="card-header">制限設定</div>
                <div class="card-body form-horizontal">
                    <div class="form-group row">
                        <label for="storage_local" class="control-label col-md-4">使用するストレージ</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="storage_local">
                                <input type="checkbox" ng-model="setting.storage_local" id="storage_local" ng-true-value="1"> ローカル
                            </label>
                            <label class="label mr-2" for="storage_box">
                                <input type="checkbox" ng-model="setting.storage_box" id="storage_box" ng-true-value="1"> Box
                            </label>
                            <label class="label mr-2" for="storage_onedrive">
                                <input type="checkbox" ng-model="setting.storage_onedrive" id="storage_onedrive" ng-true-value="1"> OneDrive
                            </label>
                            <label class="label mr-2" for="storage_google">
                                <input type="checkbox" ng-model="setting.storage_google" id="storage_google" ng-true-value="1"> Google Drive
                            </label>
                            <label class="label mr-2" for="storage_dropbox">
                                <input type="checkbox" ng-model="setting.storage_dropbox" id="storage_dropbox" ng-true-value="1"> Dropbox
                            </label>
                        </div>
                    </div>
                    <div class="form-group row" ng-if="addressbook_only_flag == 0">
                        <label for="enable_any_address_1" class="control-label col-md-4">送信先の制限</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="enable_any_address_1">
                                <input type="radio" ng-model="setting.enable_any_address" id="enable_any_address_1" ng-value="1"> 共通アドレス帳と管理者が登録した利用者のアドレスのみに制限する
                            </label>
                            {{--PAC_5-2616 S--}}
                            @if($company->enable_any_address_flg)
                            <label class="label mr-2" for="enable_any_address_2">
                                <input type="radio" ng-model="setting.enable_any_address" id="enable_any_address_2" ng-value="2"> 承認ルートのみに制限する
                            </label>
                            @endif
                            {{--PAC_5-2616 E--}}
                            <label class="label mr-2" for="enable_any_address_0">
                                <input type="radio" ng-model="setting.enable_any_address" id="enable_any_address_0" ng-value="0"> 制限しない
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="link_auth_flg_1" class="control-label col-md-4">通知メールから文書表示する際の認証機能</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="link_auth_flg_1">
                                <input type="radio" ng-model="setting.link_auth_flg" id="link_auth_flg_1" ng-value="1"> 必要
                            </label>
                            <label class="label mr-2" for="link_auth_flg_0">
                                <input type="radio" ng-model="setting.link_auth_flg" id="link_auth_flg_0" ng-value="0"> 不要
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="mfa_login_timing_flg_0" class="control-label col-md-4">多要素認証のログイン要求</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="mfa_login_timing_flg_0">
                                <input type="radio" ng-model="setting.mfa_login_timing_flg" id="mfa_login_timing_flg_0" ng-value="0" ng-disabled="mfa_abled==0"> 毎回
                            </label>
                            <label class="label mr-2" for="mfa_login_timing_flg_1">
                                <input type="radio" ng-model="setting.mfa_login_timing_flg" id="mfa_login_timing_flg_1" ng-value="1" ng-disabled="mfa_abled==0"> 指定時間毎
                            </label>
                            <label class="label mr-2" for="mfa_interval_hours">
                                <input type="number" class="form-control" ng-model="setting.mfa_interval_hours" id="mfa_interval_hours"
                                    required ng-disabled="mfa_abled==0 || setting.mfa_login_timing_flg == 0"/>
                            </label>
                            <label class="label mr-2">
                                時間
                            </label>
                        </div>
                    </div>
                    {{-- PAC_5-530 --}}
{{--                    <div class="form-group row">--}}
{{--                        <label for="enable_email_thumbnail" class="control-label col-md-4">通知メール内文書のサムネイル表示機能</label>--}}
{{--                        <div class="col-md-8">--}}
{{--                            <label class="label mr-2" for="enable_email_thumbnail">--}}
{{--                                <input type="checkbox" ng-model="setting.enable_email_thumbnail" id="enable_email_thumbnail" ng-true-value="1"> 利用可能--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    {{-- PAC_5-530 --}}

                    <div class="form-group row">
                        <label class="control-label col-md-4">PDFへの電子署名付加</label>
                        <div class="col-md-8">
                            <label ng-show="addSignature == 1" class="label mr-2">利用する</label>
                            <label ng-show="addSignature == 0" class="label mr-2">利用しない</label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-md-4">タイムスタンプ</label>
                        <div class="col-md-8">
                            <label ng-show="addTimeStamp == 1" class="label mr-2">利用可</label>
                            <label ng-show="addTimeStamp == 0" class="label mr-2">利用不可</label>
                        </div>
                    </div>
                    <!-- PAC_5-573 ADD START -->
                    <div class="form-group row">
                        <label for="time_stamp_permission_1" class="control-label col-md-4">タイムスタンプ発行権限(全ユーザー)</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="time_stamp_permission_1">
                                <input type="radio" ng-model="setting.time_stamp_permission" id="time_stamp_permission_1" ng-value="1" ng-disabled="addTimeStamp==0"> 有効
                            </label>
                            <label class="label mr-2" for="time_stamp_permission_0">
                                <input type="radio" ng-model="setting.time_stamp_permission" id="time_stamp_permission_0" ng-value="0" ng-disabled="addTimeStamp==0"> 無効
                            </label>
                        </div>
                    </div>
                    <!-- PAC_5-573 ADD END -->
                    <div class="form-group row">
                        <label for="receiver_permission_1" class="control-label col-md-4">回覧途中の受取人による文書の追加</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="receiver_permission_1">
                                <input type="radio" ng-model="setting.receiver_permission" id="receiver_permission_1" ng-value="1"> 可
                            </label>
                            <label class="label mr-2" for="receiver_permission_0">
                                <input type="radio" ng-model="setting.receiver_permission" id="receiver_permission_0" ng-value="0"> 不可
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label  for="enable_env_selection_dialog" class="control-label col-md-4">環境選択ダイアログの表示</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="enable_env_selection_dialog">
                                <input type="radio" ng-model="setting.environmental_selection_dialog" id="enable_env_selection_dialog" ng-value="1"> 表示する
                            </label>
                            <label class="label mr-2" for="disable_env_selection_dialog">
                                <input type="radio" ng-model="setting.environmental_selection_dialog" id="disable_env_selection_dialog" ng-value="0"> 表示しない
                            </label>
                        </div>
                    </div>
                    <div class="form-group row" ng-if='login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}'>
                        <label  for="use_mobile_app_flg" class="control-label col-md-4">パスワード設定</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="use_mobile_app-1">
                                <input type="radio" ng-model="setting.use_mobile_app_flg" id="use_mobile_app-1" ng-value="1"> 可
                            </label>
                            <label class="label mr-2" for="use_mobile_app-0">
                                <input type="radio" ng-model="setting.use_mobile_app_flg" id="use_mobile_app-0" ng-value="0"> 不可
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label  for="text_append_flg-1" class="control-label col-md-4">テキスト追加</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="text_append_flg-1">
                                <input type="radio" ng-model="setting.text_append_flg" id="text_append_flg-1" ng-value="1"> 許可する
                            </label>
                            <label class="label mr-2" for="text_append_flg-0">
                                <input type="radio" ng-model="setting.text_append_flg" id="text_append_flg-0" ng-value="0"> 許可しない
                            </label>
                        </div>
                    </div>
                    {{--PAC_5-1576 S--}}
                    <div class="form-group row">
                        <label  for="require_print-1" class="control-label col-md-4">捺印必須</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="require_print-1">
                                <input type="radio" ng-model="setting.require_print" id="require_print-1" ng-value="1"> 有効　
                            </label>
                            <label class="label mr-2" for="require_print-0">
                                <input type="radio" ng-model="setting.require_print" id="require_print-0" ng-value="0"> 無効
                            </label>
                        </div>
                    </div>
                    {{--PAC_5-1576 E--}}
                    {{--PAC_5-2821 S--}}
                    @if($company->skip_flg)
                    <div class="form-group row">
                        <label  for="skip_flg-1" class="control-label col-md-4">スキップ機能</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="limit_skip_flg-1">
                                <input type="radio" ng-model="setting.limit_skip_flg" id="limit_skip_flg-1" ng-value="1"> 有効　
                            </label>
                            <label class="label mr-2" for="limit_skip_flg-0">
                                <input type="radio" ng-model="setting.limit_skip_flg" id="limit_skip_flg-0" ng-value="0"> 無効
                            </label>
                        </div>
                    </div>
                    @endif
                    {{--PAC_5-2821 E--}}
                    {{--PAC_5-2705 S--}}
                    <div class="form-group row">
                        <label  for="require_approve_flag-1" class="control-label col-md-4">最終承認者から直接社外に送る</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="require_approve_flag-1">
                                <input type="radio" ng-model="setting.require_approve_flag" id="require_approve_flag-1" ng-value="1"> 有効　
                            </label>
                            <label class="label mr-2" for="require_approve_flag-0">
                                <input type="radio" ng-model="setting.require_approve_flag" id="require_approve_flag-0" ng-value="0"> 無効
                            </label>
                        </div>
                    </div>
                    {{--PAC_5-2705 E--}}

                    <div class="form-group row">
                        <label  for="default_stamp_history_flg-1" class="control-label col-md-4">回覧履歴を付ける</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="default_stamp_history_flg-1">
                                <input type="radio" ng-model="setting.default_stamp_history_flg" id="default_stamp_history_flg-1" ng-value="1"> 有効　
                            </label>
                            <label class="label mr-2" for="default_stamp_history_flg-0">
                                <input type="radio" ng-model="setting.default_stamp_history_flg" id="default_stamp_history_flg-0" ng-value="0"> 無効
                            </label>
                        </div>
                    </div>

                    @if($company->with_box_flg)
                    <div class="form-group row">
                        <label  for="shachihata_login_flg-1" class="control-label col-md-4">shachihata cloud利用者ログインの制限</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="shachihata_login_flg-1">
                                <input type="radio" ng-model="setting.shachihata_login_flg" id="shachihata_login_flg-1" ng-value="1"> 利用者のshachihata cloudへのログインを制限する　
                            </label>
                            <label class="label mr-2" for="shachihata_login_flg-0">
                                <input type="radio" ng-model="setting.shachihata_login_flg" id="shachihata_login_flg-0" ng-value="0"> 制限しない
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label  for="with_box_login_flg-1" class="control-label col-md-4">box捺印利用者ログインの制御</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="with_box_login_flg-1">
                                <input type="radio" ng-model="setting.with_box_login_flg" id="with_box_login_flg-1" ng-value="1"> box捺印へのログインを制御する　
                            </label>
                            <label class="label mr-2" for="with_box_login_flg-0">
                                <input type="radio" ng-model="setting.with_box_login_flg" id="with_box_login_flg-0" ng-value="0"> 制御しない
                            </label>
                        </div>
                    </div>
                    @endif
                    @if($company->receive_plan_flg)
                    <div class="form-group row">
                        <label  for="limit_receive_plan_flg-1" class="control-label col-md-4">利用者のマイページに受信専用プランの招待URLを表示</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="limit_receive_plan_flg-1">
                                <input type="radio" ng-model="setting.limit_receive_plan_flg" id="limit_receive_plan_flg-1" ng-value="1"> 表示する　
                            </label>
                            <label class="label mr-2" for="limit_receive_plan_flg-0">
                                <input type="radio" ng-model="setting.limit_receive_plan_flg" id="limit_receive_plan_flg-0" ng-value="0"> 表示しない
                            </label>
                        </div>
                    </div>
                    @endif  
                </div>
            </div>
            @canany([PermissionUtils::PERMISSION_LIMIT_SETTING_UPDATE])
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-success" ng-click="save()">
                    <i class="far fa-save"></i> 更新
                </button>
            </div>
            <br />
            @endcanany
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('SettingsLimitController', function($scope, $rootScope, $http){
                $scope.default_use_mobile_app_flg = {!! json_encode(\App\Http\Utils\AppUtils::FLG_DISABLE) !!};
                $scope.mfa_abled = 0;
                @if($limit)
                    $scope.setting = {!! json_encode($limit) !!};
                    $scope.addSignature = {!! json_encode($company->esigned_flg) !!};
                    $scope.addTimeStamp = {!! json_encode($company->stamp_flg) !!};
                    $scope.mfa_abled = {!! json_encode($company->mfa_abled) !!};
                    $scope.addressbook_only_flag = {!! json_encode($company->addressbook_only_flag) !!};
                    $scope.login_type = {!! json_encode($company->login_type) !!}
                @else
                    $scope.setting = { storage_local: false, storage_box: false, storage_google: false, storage_dropbox: false, storage_onedrive: false,
                            enable_any_address: '0', link_auth_flg: '0', enable_email_thumbnail: false, receiver_permission: '0',
                            environmental_selection_dialog: '0', time_stamp_permission: '0',mfa_login_timing_flg:'1',mfa_interval_hours:'12',use_mobile_app_flg: $scope.default_use_mobile_app_flg,
                            text_append_flg: '0', require_print_flg: '0', require_approve_flag: '0',default_stamp_history_flg: '0',shachihata_login_flg: '0',with_box_login_flg: '0',limit_receive_plan_flg: '0'
                        };
                    $scope.addSignature = 0;
                    $scope.addTimeStamp = 0;
                    $scope.mfa_abled = 0;
                    $scope.login_type = 0;

                @endif

                /* PAC_5-2328 S*/
                @if($company && $company->mfa_abled)
                    $scope.mfa_abled = {!! json_encode($company->mfa_abled) !!};
                @endif
                /* PAC_5-2328 E*/

                /*PAC_5-2100 S*/
                $scope.link_auth_flg_old = $scope.setting.link_auth_flg
                /*PAC_5-2100 E*/
                $scope.changeFormat = function(){
                    let date = new Date();
                    $scope.date
                    $scope.showDateFormat();
                }

                $scope.save = function(){
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax, $scope.setting)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $(".message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $(".message").append(showMessages(event.data.message, 'success', 10000));
                        }
                        /*PAC_5-2100 S*/
                        if ($scope.link_auth_flg_old ==1 && $scope.setting.link_auth_flg == 0){
                            $rootScope.$emit("showMocalAlert",
                                {
                                    size:'md',
                                    title:"メッセージ",
                                    message:'保護設定の「アクセスコードで保護」の状態が保護するになります。',
                                });
                        }
                        $scope.link_auth_flg_old=$scope.setting.link_auth_flg
                        /*PAC_5-2100 E*/
                    });
                };
            });

        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush
