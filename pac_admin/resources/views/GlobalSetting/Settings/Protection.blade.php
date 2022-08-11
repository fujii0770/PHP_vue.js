@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_ajax = "{{ route('GlobalSetting.Protection.Store') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div class="GlobalSetting Settings-protection">
        <div ng-controller="SettingsProtectionController">
            <div class="message"></div>
            <div class="card mt-3">
                <div class="card-header">保護設定</div>
                <div class="card-body form-horizontal">
                    <div class="form-group row">
                        <div class="col-md-1"></div>
                        <label for="protection_setting_change_flg" class="text-left-lg col-md-3">以下の項目の変更を申請時に許可する</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="protection_setting_change_flg">
                            <!-- PAC_5-1154 文言の修正 -->
                                <input type="checkbox" ng-model="setting.protection_setting_change_flg" id="protection_setting_change_flg" ng-true-value="1"> 許可する
                            <!-- PAC_5-1154 終了-->
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-1"></div>
                        <label for="destination_change_flg_1" class="text-left-lg col-md-3">　　　・宛先、回覧順の変更
                            <a data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-html="true"
                               data-content="承認者による回覧ルートの宛先の<br/>追加及び回覧順の変更設定"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </label>
                        <div class="col-md-8">
                            <label class="label mr-5" for="destination_change_flg_1">
                                <input type="radio" ng-model="setting.destination_change_flg" id="destination_change_flg_1" ng-value="1"> 許可する
                            </label>
                            <label class="label mr-5" for="destination_change_flg_0">
                                <input type="radio" ng-model="setting.destination_change_flg" id="destination_change_flg_0" ng-value="0"> 許可しない
                            </label>
                        </div>
                    </div>
                    @if($limit->text_append_flg==1)
                    <div class="form-group row">
                        <div class="col-md-1"></div>
                        <label for="text_append_flg_1" class="text-left-lg col-md-3">　　　・テキスト追加の許可
                            <a data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-html="true"
                               data-content="承認者による回覧文書への<br/>テキスト追加機能の利用設定"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </label>
                        <div class="col-md-8">
                            <label class="label mr-5" for="text_append_flg_1">
                                <input type="radio" ng-model="setting.text_append_flg" id="text_append_flg_1" ng-value="1"> 許可する
                            </label>
                            <label class="label mr-5" for="text_append_flg_0">
                                <input type="radio" ng-model="setting.text_append_flg" id="text_append_flg_0" ng-value="0"> 許可しない
                            </label>
                        </div>
                    </div>
                    @endif
                    {{-- mst_company属性がOFFの場合、非表示 --}}
                    <div class="form-group row" ng-show="notification_email_thumbnail == 1">
                        <div class="col-md-1"></div>
                        <label for="enable_email_thumbnail_1" class="text-left-lg col-md-3">　　　・メール内の文書のサムネイル表示
                            <a data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-html="true"
                               data-content="回覧メールから文書を開く際の</br>サムネイル表示設定"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </label>
                        <div class="col-md-8">
                            <label class="label mr-5" for="enable_email_thumbnail_1">
                                <input type="radio" ng-model="setting.enable_email_thumbnail" id="enable_email_thumbnail_1" ng-value="1"> 表示する
                            </label>
                            <label class="label mr-5" for="enable_email_thumbnail_0">
                                <input type="radio" ng-model="setting.enable_email_thumbnail" id="enable_email_thumbnail_0" ng-value="0"> 表示しない
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-1"></div>
                        <label for="access_code_protection_1" class="text-left-lg col-md-3">　　　・アクセスコードで保護
                            <a data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-html="true"
                               data-content="メールから文書を開く際の<br/>アクセスコードの要求設定"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </label>
                        <div class="col-md-8">
                            <label class="label mr-5" for="access_code_protection_1">
                                <input type="radio" ng-model="setting.access_code_protection" id="access_code_protection_1" ng-value="1"> 保護する
                            </label>
                            <label class="label mr-5" for="access_code_protection_0">
                                <input type="radio" ng-model="setting.access_code_protection" id="access_code_protection_0" ng-value="0"> 保護しない
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-1"></div>
                        <label for="require_print_1" class="text-left-lg col-md-3">　　　・捺印設定
                            <a data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-html="true"
                               data-content="承認者による回覧文書への<br/>1回以上の捺印設定"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </label>
                        <div class="col-md-8">
                            <label class="label mr-5" for="require_print_1">
                                <input type="radio" ng-model="setting.require_print" id="require_print_1" disabled ng-value="1"> 必須にする
                            </label>
                            <label class="label mr-5" for="require_print_0" style="margin-left: -14px!important;">
                                <input type="radio" ng-model="setting.require_print" id="require_print_0" disabled ng-value="0"> 必須にしない
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right mt-3">
                @canany([PermissionUtils::PERMISSION_PROTECTION_SETTING_UPDATE])
                <button type="submit" class="btn btn-success" ng-click="save()">
                    <i class="far fa-save"></i> 更新
                </button>
                @endcanany
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('SettingsProtectionController', function($scope, $rootScope, $http){
                @if($protection)
                    $scope.setting = {!! json_encode($protection) !!};
                @else
                    $scope.setting = { protection_setting_change_flg: false, destination_change_flg: '0', enable_email_thumbnail: '0', access_code_protection: '1' ,require_print: 0};
                @endif

                $scope.notification_email_thumbnail = {!! json_encode($company->enable_email_thumbnail) !!}
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
                        });
                };
            });
        }else{
            throw new Error("Something error init Angular.");
        }
        $(function () { $("[data-toggle='popover']").popover(); });
    </script>
@endpush

@push('styles_after')
<style>
    .popover{
        background-color: #000!important;
    }
    .popover-body{
        color: #fff!important;
    }
    .bs-popover-auto[x-placement^=bottom]>.arrow::after,.bs-popover-bottom>.arrow::after{border-bottom-color:#000!important}
</style>
@endpush