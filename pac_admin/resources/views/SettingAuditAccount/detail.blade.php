<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-if="!item.id">新規登録</h4>
                    <h4 class="modal-title" ng-if="item.id">更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>
                    <div class="row form-group">
                        <label for="email" class="col-lg-3 col-md-3 col-sm-2 control-label">メールアドレス <span class="text-danger">*</span></label>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="input-group mb-1">
                                <input type="text" class="form-control" id="email" ng-model="item.email"
                                       ng-readonly="readonly"  placeholder="email@example.com" required>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="account-name" class="col-lg-3 col-md-3 col-sm-2 control-label">名称</label>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="input-group mb-1">
                                <input type="text" class="form-control" id="account-name" ng-model="item.account_name"
                                       ng-readonly="readonly"  placeholder="" required>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="expiration-date" class="col-lg-3 col-md-3 col-sm-2 control-label">有効期限</label>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="input-group mb-1">
                                <input type="date" class="form-control" id="expiration_date" ng-model="item.expiration_date"
                                       ng-readonly="readonly"  placeholder="yyyy/MM/dd">
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="password" class="col-lg-3 col-md-3 col-sm-2 control-label">パスワード</label>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="input-group mb-1">
                                <input type="password" class="form-control" id="password" ng-model="item.password"
                                       ng-readonly="readonly" minlength="{{ $passwordPolicy->min_length }}" maxlength="32" ng-required="!item.id">
                            </div>
                            <span class="error password-error"></span>

                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="password-confirm" class="col-lg-3 col-md-3 col-sm-2 control-label" style="padding-right: 0">パスワード(確認用)</label>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="input-group mb-1">
                                <input type="password" class="form-control" id="password-confirm" ng-model="item.password_confirm"
                                       ng-readonly="readonly" minlength="{{ $passwordPolicy->min_length }}" maxlength="32" ng-show="!show_password">
                                <input type="password" class="form-control" ng-show="show_password" value="" disabled>
                            </div>
                            <span class="error password_confirmation-error" ng-show="!show_password"></span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-lg-3 col-md-3 col-sm-2"></div>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="show_password" ng-model="show_password">
                                <label class="form-check-label" for="show_password">パスワードを表示</label>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-md-3 col-sm-2 control-label">状態</label>
                        <div class="col-lg-8 col-md-8 col-sm-8 col-12">
                            <div class="input-group mb-1 mt-2">
                                <div class="pr-3">
                                    <input type="radio" name="status" id="valid-account" ng-model="item.state_flg" ng-value="1" checked>
                                    <label for="valid-account">
                                        有効
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="status" id="invalid-account" ng-model="item.state_flg" ng-value="9">
                                    <label for="invalid-account">
                                        無効
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    @canany([PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_CREATE, PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_UPDATE])
                    <button type="submit" class="btn btn-success"  ng-click="save()">
                        <ng-template ng-show="!item.id"><i class="fas fa-plus-circle" ></i> 登録のみ</ng-template>
                        <ng-template ng-show="item.id"><i class="far fa-save"></i> 更新</ng-template>
                    </button>

                    @if($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO)
                        <button type="button" class="btn btn-warning" ng-click="resetPassword(true)" ng-show="item.id">
                                <i class="far fa-envelope"></i>ログインURL送信
                        </button>
                    @endif
                    @if($company->login_type != \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO ||($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO  && $company->use_mobile_app_flg == \App\Http\Utils\AppUtils::FLG_ENABLE))
                        <button type="button" class="btn btn-warning" ng-click="resetPassword(false)" ng-show="item.id">
                                <i class="far fa-envelope"></i> 初期パスワード設定
                        </button>
                    @endif
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_DELETE])
                        <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="item.id">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
                    @endcanany
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> 閉じる
                    </button>
                </div>

                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
    <script>

        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.id = 0;
                $scope.truly_state = 0;
                $scope.passwordIsset = false;
                $scope.isResetPassword = null;
                $scope.show_password = false;

              $('#show_password').click(function() {
                  if($(this).is(':checked')) {
                      $('#password').attr('type', 'text');
                      let pwd  = $("#password").val();
                      $scope.checkRegex(pwd, $('#password'), 'パスワードは、文字と数字を含める必要があります。')
                  } else {
                      $('#password').attr('type', 'password');
                     $scope.check_confirm();
                  }
              });

                var errorPassword = false;
                var character_type_limit = {{$passwordPolicy->character_type_limit}};

                $rootScope.$on("openNewAuditUser", function(event){
                    $('.password-error').addClass('hide');
                    $('.password_confirmation-error').addClass('hide');
                    $scope.show_password = false;
                    if(allow_create) $scope.readonly = false;
                    else $scope.readonly = true;
                    $scope.id = 0;
                    $scope.item = {id: 0, email: "", account_name: "", expiration_date: "", state_flg: 1, password_confirm: null, password: null}
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openEditAuditUser", function(event, data){
                    $('.password-error').addClass('hide');
                    $('.password_confirmation-error').addClass('hide');
                    $scope.show_password = false;
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    $scope.item.password_confirm = '';
                    hideMessages();
                    hasChange = false;
                    if(allow_update) $scope.readonly = false;
                    else $scope.readonly = true;

                    $http.get(link_ajax + "/" +$scope.id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.item.expiration_date = event.data.item.expiration_date ? new Date(event.data.item.expiration_date) : null;
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $(document).ready(function() {
                    if (GetIEVersion() > 0){
                        $('#ui-datepicker-div').css('z-index', "1051");
                        $('#expiration_date').css('z-index', "1051");
                    }
                    $('#password').keyup(function () {
                        $('.password-error').addClass('hide');
                        let message = 'パスワードは、文字と数字を含める必要があります。';
                        let pwd  = $("#password").val();
                        let pwd_confirm = $('#password-confirm').val();
                        let err_elem =  $('.password-error');
                        if(pwd) {
                            $scope.checkRegex(pwd, err_elem, message);
                            if(!$scope.show_password) $scope.check_confirm();
                        } else if(!pwd && !pwd_confirm){
                            err_elem.addClass('hide');
                            $('.password_confirmation-error').addClass('hide');
                        } else {
                            err_elem.addClass('hide');
                        }
                    });

                    $('#password-confirm').keyup(function () {
                        $('.password_confirmation-error').addClass('hide');
                        $scope.check_confirm();
                    });

                });
                $scope.check_confirm = function() {
                    let pwd = $("#password").val();
                    let pwd_confirm = $('#password-confirm').val();
                    if(pwd_confirm !== pwd) {
                        $('.password_confirmation-error').removeClass('hide').html('パスワードと確認パスワードが一致しません');
                        errorPassword = true;
                    } else {
                        $('.password_confirmation-error').addClass('hide');
                        errorPassword = false;
                    }
                };
                $scope.checkRegex = function(value, element, message) {
                    if(!validPass(value)){
                        element.removeClass('hide').html(message);
                        errorPassword = true;
                    } else errorPassword = false;
                    if(character_type_limit ==1){
                        let message = 'パスワードポリシーに反しています。英大文字、英小文字、数字、記号の内、3種類以上入れてください。';
                        if(!validPassCharacterType(value)){
                            element.removeClass('hide').html(message);
                            errorPassword = true;
                        } else errorPassword = false;
                    }
                };
                $scope.save = function(callSuccess){
                    if($(".form_edit")[0].checkValidity() && !errorPassword) {

                        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        if(!re.test(String($scope.item.email).toLowerCase())) {
                            return $("#modalDetailItem .message-info").append(showMessages(['メールアカウントが正しくありません。'], 'danger', 10000));
                        }
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.id = event.data.id;
                                $scope.item.id = event.data.id;
                                $scope.truly_state = $scope.item.state_flg;
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                                if(callSuccess) callSuccess();
                            }
                        };
                        let itemCreated = $.extend({}, $scope.item);
                        itemCreated.expiration_date = itemCreated.expiration_date ? $.datepicker.formatDate('yy/mm/dd',itemCreated.expiration_date).toString() : null;
                        if(!$scope.item.id){
                            $http.post(link_ajax, {item:itemCreated})
                                .then(saveSuccess);
                        }else{
                            if(GetIEVersion() > 0) {
                                itemCreated.expiration_date = $('#expiration_date').val()
                            }
                            $http.put(link_ajax + "/" +$scope.id, {item:itemCreated})
                                .then(saveSuccess);
                        }
                    }
                };
                $scope.remove = function(){
                    let cids = [];
                    cids.push($scope.item.id);
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'利用者を削除します。よろしいですか？',
                            btnDanger:'はい',
                            databack: $scope.item.id,
                            callDanger: function(id){
                                $rootScope.$emit("showLoading");
                                $http.post(link_delete_select, { cids: cids})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'warning', 10000));
                                            location.reload();
                                        }
                                    });
                            }
                        });
                };
                $scope.resetPassword = function(sendLoginUrl){
                    var send_mail = link_reset;
                    if(sendLoginUrl){
                        send_mail = link_send_login_url;
                    }
                    $scope.isResetPassword = true;
                    // check state
                    if($scope.truly_state == 0){
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'初期パスワード通知するためには状態を有効にする必要があります。実行しますか？',
                                btnSuccess:'はい',
                                size:'lg',
                                callSuccess: function(){
                                    $scope.item.state_flg = 1;
                                    $rootScope.$emit("showLoading");
                                    $scope.save(function(){
                                        $scope.resetPassword();
                                    });
                                }
                            });
                        return;
                    }

                    $scope.isResetPassword = false;
                    $rootScope.$emit("showLoading");
                    $http.post(send_mail, { cids: [$scope.item.id]})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                };
                function validPass(value){
                    if(/^(?=.*[0-9])(?=.*[a-zA-Z])/.test(value)){
                        for(let i in value){
                            if(value[i].charCodeAt() > 126){
                                return false;
                            }
                        }
                    }else{
                        return false;
                    }
                    return true;
                }

                function validPassCharacterType(value){
                    /*PAC_5-2848 S*/
                    if(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])|^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])|^(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])|^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]])/.test(value)){
                    /*PAC_5-2848 E*/
                        for(let i in value){
                            if(value[i].charCodeAt() > 126){
                                return false;
                            }
                        }
                    }else{
                        return false;
                    }
                    return true;
                }
            })
        }
    </script>
@endpush
