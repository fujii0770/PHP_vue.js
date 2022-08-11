<div ng-controller="DetailSettingAdminController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="!info.id">管理者情報登録</h4>
                    <h4 class="modal-title" ng-show="info.id">管理者情報更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message"></div>
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス','','email', true, 
                            [ 'placeholder' =>'email@example.com', 'ng-model' =>'info.email','ng-disabled'=> 'info.id', 'ng-readonly'=>"readonly", 'id'=>'email' ]) !!}
                        
                    <div class="form-group">
                        <div class="row">
                            <label for="given_name" class="col-md-4 control-label">氏名 <span style="color: red">*</span></label>
                            
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" ng-readonly="readonly" required maxlength="128" class="form-control" placeholder="姓" ng-model="info.family_name" id="family_name" />
                                    </div>
                                    <div class="col-6">
                                        <input type="text" ng-readonly="readonly" required maxlength="128" class="form-control" placeholder="名" ng-model="info.given_name" id="given_name" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {!! \App\Http\Utils\CommonUtils::showFormField('department_name','部署','','text', false,
                            [ 'placeholder' =>'○○部', 'ng-model' =>'info.department_name', 'ng-readonly'=>"readonly", 'id'=>'department_name', 'maxlength' => 256]) !!}
                    {!! \App\Http\Utils\CommonUtils::showFormField('phone_number','電話番号','','tel', false, 
                            [ 'placeholder' =>'000-0000-00000', 'ng-model' =>'info.phone_number', 'ng-readonly'=>"readonly", 'id'=>'phone_number', 'pattern'=>"[0-9\-]*", 'maxlength' => 15 ]) !!}

                    <div class="form-group">
                        <div class="row">
                            <label for="state_flg_1" class="text-right col-4">状態</label>
                            <div class="col-8">
                                <div ng-if="!info.id">                                    
                                    <input type="checkbox" id="state_flg_1" ng-model="info.sendEmail" /> 
                                    <label for="state_flg_1">今すぐ有効にする(メール通知する)</label>
                                </div>
                                <div ng-if="info.id">
                                    <input type="radio" ng-model="info.state_flg"
                                           ng-disabled="readonly || readonlyState"  value="1" id="state_flg_1">
                                    <label for="state_flg_1">有効</label> &nbsp;

                                    <input type="radio" ng-model="info.state_flg" ng-if="!info.passwordStatus"
                                           ng-disabled="readonly || readonlyState"  value="0" id="state_flg_09">
                                    <input type="radio" ng-model="info.state_flg"  ng-if="info.passwordStatus"
                                           ng-disabled="readonly || readonlyState" value="9" id="state_flg_09">

                                    <label for="state_flg_09"> 無効</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div  ng-if='info.companyEnableEmail==1'>
                        <div class="form-group">
                            <div class="row">
                                <label for="enable_email" class="text-right col-md-4">メール</label>
                                <div class="col-md-8">
                                    <input type="checkbox" ng-model="info.enable_email" id="enable_email" ng-true-value="1" ng-false-value="0" />
                                    <label for="enable_email">有効にする</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" ng-if='info.enable_email==1'>
                            <div class="row">
                                <label for="email_format" class="text-right col-md-4" >メールフォーマット</label>
                                <div class="col-md-8">
                                    <input type="radio" ng-model="info.email_format" id="email_format-1" ng-value="1" /> 
                                    <label for="email_format-1">HTML</label> &nbsp;
                                    <input type="radio" ng-model="info.email_format" id="email_format-0" ng-value="0" /> 
                                    <label for="email_format-0">テキスト</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($company->mfa_flg)
                    <div class="form-group">
                        <div class="row">
                            <label for="email_auth_flg_1" class="col-md-4 control-label">メール認証</label>
                            <div class="col-md-8">
                                <input type="radio" id="email_auth_flg_1" ng-model="info.email_auth_flg"
                                       ng-readonly="readonly" name="email_auth_flg" value="1" />
                                <label for="email_auth_flg_1">有効</label> &nbsp;
                                <input type="radio" id="email_auth_flg_0" ng-model="info.email_auth_flg"
                                       ng-readonly="readonly" name="email_auth_flg" value="0" />
                                <label for="email_auth_flg_0">無効</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label for="email_auth_dest_flg_0" class="col-md-4 control-label">認証コード送信先</label>
                            <div class="col-md-8">
                                <label for="email_auth_dest_flg_0" class="control-label">
                                    <input type="radio" id="email_auth_dest_flg_0" ng-model="info.email_auth_dest_flg"
                                               ng-readonly="readonly" name="email_auth_dest_flg" value="0" />
                                    <label for="email_auth_dest_flg_0">登録メールアドレス</label> &nbsp;
                                    <input type="radio" id="email_auth_dest_flg_1" ng-model="info.email_auth_dest_flg"
                                               ng-readonly="readonly" name="email_auth_dest_flg" value="1" />
                                    <label for="email_auth_dest_flg_1">その他</label>
                                </label>
                                <input type="text" class="form-control" ng-model="info.auth_email"
                                       placeholder="email@example.com"  ng-readonly="readonly"  maxlength="256" ng-disabled="info.email_auth_dest_flg==0" />
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            
                <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_CREATE])
                            <ng-template ng-show="!info.id">
                                <button type="submit" class="btn btn-success" ng-click="save()">
                                    <ng-template ng-show="info.sendEmail"><i class="far fa-envelope"></i> 登録・通知</ng-template>
                                    <ng-template ng-show="!info.sendEmail"><i class="fas fa-plus-circle"></i> 登録のみ</ng-template>
                                </button>
                            </ng-template>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE])
                            <ng-template ng-show="info.id">
                                <button type="submit" class="btn btn-success" ng-click="save()">
                                    <i class="far fa-save"></i> 更新
                                </button>
                            </ng-template>
                        @endcanany
{{--                        @if ($company->passreset_type != 1)--}}
                            <button type="button" class="btn btn-warning" ng-click="resetPassword()" ng-show="info.id" ng-disabled="readonlyEmailBtn">
                                <i class="far fa-envelope"></i> 初期パスワード設定
                            </button>
{{--                        @endif--}}

                        @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_DELETE])
                            <ng-template ng-show="info.id">
                                <button type="submit" class="btn btn-danger" ng-click="remove()">
                                    <i class="fas fa-trash-alt"></i> 削除
                                </button>
                            </ng-template>
                        @endcanany
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
    <form class="formUserStamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-add-stamp mt-3 modal-child" id="modalPasswordCode" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
{{--                    <div class="modal-header">--}}
{{--                        <h4 class="modal-title">パスワード設定コード</h4>--}}
{{--                        <button type="button" class="close" data-dismiss="modal">&times;</button>--}}
{{--                    </div>--}}
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="message message-list mt-3"></div>
                        <div class="" style="text-align:center; font-size:1.2em;"><% info.email %></div>
                        <div class="my-2" style="text-align:center;">
                            <span id='code' class="py-1 px-4 font-weight-bold" style="font-size:1.2em; border: solid 1px #000;"></span>
                            <button style="font-size:2em; border: none; border-radius:18px;margin-left: 10px;" ng-click="copyToClipboard()"><i class="far fa-clipboard"></i></button>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailSettingAdminController', function($scope, $rootScope, $http) {
                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $rootScope.info.id = event.data.id;
                                $rootScope.info.sendEmail = false;
                                $rootScope.info.state_flg = "" + event.data.state_flg;
                                $rootScope.readonlyEmailBtn = $rootScope.info.state_flg != "1";
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                            }
                        }

                        if($rootScope.info.id)
                            $http.put(link_ajax + "/"+$rootScope.info.id, $rootScope.info).then(saveSuccess);
                        else $http.post(link_ajax, $rootScope.info).then(saveSuccess);
                    }
                };

                //PAC_5-407 管理者削除機能を追加
                $scope.remove = function(){
                    if($scope.info.state_flg == 1){
                        $("#modalDetailItem .message").append(showMessages(["状態が有効なユーザーは削除できません。"], 'danger', 10000));
                    }else if($scope.info.role_flg == 1){
                        $("#modalDetailItem .message").append(showMessages(["利用責任者は削除できません。"], 'danger', 10000));
                    }else{
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'管理者を削除します。よろしいですか？',
                                btnDanger:'はい',
                                databack: $scope.info.id,
                                callDanger: function(id){
                                    $rootScope.$emit("showLoading");
                                    $http.delete(link_ajax + "/" + id, { })
                                        .then(function(event) {
                                            $rootScope.$emit("hideLoading");
                                            if(event.data.status == false){
                                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                            }else{
                                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'warning', 10000));
                                                location.reload();
                                            }
                                        });
                                }
                            });
                    }
                };
                
                $scope.resetPassword = function(){
                    $rootScope.$emit("showLoading");
                    $http.get(link_reset +"/"+ $rootScope.info.id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'success', 10000));
                        }                        
                    });
                };

                // $scope.showFormCode = function(){
                //     // CSRFトークン設定
                //     $.ajaxSetup({
                //         headers: {
                //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //         }
                //     });
                //     $.ajax({
                //         type:'POST', // POST送信
                //         url:'password/getPasswordCodeAdmin', //送信先URL
                //         dataType:'json', //受け取る変数の形式
                //         data: {'code_admin_id': document.getElementById("code_admin_id").value},
                //         beforeSend: function () {
                //             $('.loading').removeClass('display-none'); //読み込みグルグル表示
                //         }
                //     }).done(function (code){
                //         $("#code").text(code);
                //         $('.loading').addClass('display-none'); //読み込みグルグル削除
                //         console.log("ajax通信に成功しました");
                //     }).fail(function(){
                //         console.log("ajax通信に失敗しました");
                //     });
                //     // callback
                //     $("#modalPasswordCode").modal();
                // };

                $scope.copyToClipboard = function(){
                    // ボタンを押してコードをクリップボードにコピー
                    var code = document.getElementById('code');
                    var copyText = code.textContent;
                    var el = document.createElement('textarea');
                    el.value = copyText;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    $(".message-list").append(showMessages(['クリップボードにコピーしました'], 'success', 1000));
                }
            })
        }
    </script>
@endpush