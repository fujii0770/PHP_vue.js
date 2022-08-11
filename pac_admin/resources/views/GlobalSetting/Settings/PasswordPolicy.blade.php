@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('GlobalSetting.PasswordPolicy.Store') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>

        <div class="GlobalSetting Settings-password-policy">
            <div ng-controller="SettingsPasswordPolicyController">
                <div class="message"></div>
                <div class="card mt-3">
                    <div class="card-header">パスワードポリシー設定</div>
                    <div class="card-body form-horizontal">
                        <div class="form-group row">
                            <label for="min_length" class="control-label col-md-3">最小文字数</label>
                            <div class="col-md-3">
                                <input type="number" ng-model="passwordPolicy.min_length" min="4" max="14"
                                class="form-control" id="min_length"  />
                            </div>
                            <div class="col-md-6"><span class="help-message margin-left-20">4～14文字で設定してください</span></div>
                        </div>
                        <div class="form-group row">
                            <label for="validity_period" class="control-label col-md-3">有効期間</label>
                            <div class="col-md-3">
                                <input type="number" ng-model="passwordPolicy.validity_period"  min="0" max="999"
                                class="form-control" id="validity_period"  />
                            </div>
                            <div class="col-md-6"><span class="width2 inline-block">日</span> <span class="help-message">0日設定時、無期限になります</span></div>
                        </div>
                        <div class="row">
                            <label for="enable_password_1" class="control-label col-md-3"><b>前回と同じパスワードの利用</b></label>
                            <div class="col-md-3">
                                <input type="radio" ng-model="passwordPolicy.enable_password" id="enable_password_1" ng-value="1" /> <b>利用できる</b>
                                <input type="radio" ng-model="passwordPolicy.enable_password" id="enable_password_0" class="margin-left-10" ng-value="0" /> <b>利用できない</b>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        <div class="row">
                            <label for="set_mail_as_password_1" class="control-label col-md-3"><b>ユーザＩＤと同一のパスワードを禁止する</b></label>
                            <div class="col-md-3">
                                <input type="radio" ng-model="passwordPolicy.set_mail_as_password" id="set_mail_as_password_1" ng-value="1" /> <b>禁止する</b>
                                <input type="radio" ng-model="passwordPolicy.set_mail_as_password" id="set_mail_as_password_0" class="margin-left-10" ng-value="0" /> <b>禁止しない</b>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        <div class="row">
                            <label for="character_type_limit_1" class="control-label col-md-3" style="white-space: pre-line"><b>以下の中から３種類以上使用
                                    英大文字、英小文字、数字、記号</b></label>
                            <div class="col-md-3">
                                <input type="radio" ng-model="passwordPolicy.character_type_limit" id="character_type_limit_1" ng-value="1" /> <b>必須</b>
                                <input type="radio" ng-model="passwordPolicy.character_type_limit" id="character_type_limit_0" class="margin-left-10" ng-value="0" /> <b>不要</b>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6"></div>
                        </div>

                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">パスワードメールの有効期限</div>
                    <div class="card-body form-horizontal">
                        <div class="form-group row">
                            <label for="password_mail_validity_days" class="control-label col-md-3">有効期間</label>
                            {{--PAC_5-1970 パスワードメールの有効期限を変更する Start--}}
                            <div class="col-md-3">
                                <input type="number" ng-model="passwordPolicy.password_mail_validity_days"  min="1" max="14"
                                id="password_mail_validity_days"  class="form-control" />
                            </div>
                            <div class="col-md-6"><span class="width2 inline-block">日</span> <span class="help-message">1～14で設定してください</span></div>
                            {{--PAC_5-1970 End--}}
                        </div>
                    </div>
                </div>
                @canany([PermissionUtils::PERMISSION_PASSWORD_POLICY_SETTINGS_UPDATE])
                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-success" ng-click="save()">
                        <i class="far fa-save"></i> 更新
                    </button>
                </div>
                @endcanany
            </div>
        </div>

@endsection

@push('scripts')
    <script>


         if(appPacAdmin){
            appPacAdmin.controller('SettingsPasswordPolicyController', function($scope, $rootScope, $http){
                @if($passwordPolicy)
                $scope.passwordPolicy = {!! json_encode($passwordPolicy) !!};
                @else
                    // PAC_5-1970 パスワードメールの有効期限を変更する Start
                    $scope.passwordPolicy = { min_length: 4, validity_period: 1,  enable_password: 1, password_mail_validity_days: 7 , character_type_limit: 0, set_mail_as_password:0 };
                    // PAC_5-1970 End
                @endif


                $scope.save = function(){
                    var actions = [];
                    for(var i=0; i< $(".actions:checked").length; i++){
                        var item = $(".actions:checked")[i];
                        actions.push($(item).val())
                    }

                    hideMessages();
                    $rootScope.$emit("showLoading");

                    $http.post(link_ajax, $scope.passwordPolicy)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $(".message").append(showMessages(event.data.message, 'success', 10000));
                            }
                    });
                }
            })
         }
    </script>
@endpush

@push('styles_after')
    <style>
        .help-message{
            color: #8a6d3b;
            background-color: #fcf8e3;
            border: solid 1px #faebcc;
            padding: 5px;
         }
    </style>
@endpush
