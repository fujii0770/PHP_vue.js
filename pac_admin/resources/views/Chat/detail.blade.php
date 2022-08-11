<div ng-controller="DetailTalkController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="showTitleCreate">ササッとTalk利用者情報登録</h4>
                        <h4 class="modal-title" ng-if="showTitleUpdate">ササッとTalk利用者情報更新</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">

                        </div>

                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-body">

                                <div class="form-group">
                                    <div class="row">
                                        <label for="chat_role_flg" class="text-right col-4">ユーザID</label>
                                        <a href=""
                                           class="padding-left-content-custom default_text_decoration_underline"
                                           id="email"><% item.email %></a>
                                    </div>
                                </div>
                                <div ng-if="fromUpdate" class="custom-from-group">
                                    {!! \App\Http\Utils\CommonUtils::showFormField('chat_user_name','ササッとTalkユーザー名','','text', false,
                                        [ 'placeholder' =>'', 'ng-model' =>'item.chat_user_name', 'required', 'id'=>'chat_user_name', 'maxlength' => 64, 'disabled' => true ]) !!}

                                </div>
                                <div ng-if="!fromUpdate" class="custom-from-group">
                                    {!! \App\Http\Utils\CommonUtils::showFormField('chat_user_name','ササッとTalkユーザー名','','text', false,
                                        [ 'placeholder' =>'', 'ng-model' =>'item.chat_user_name', 'required', 'id'=>'chat_user_name', 'maxlength' => 64 ]) !!}
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label class="text-right col-4">(半角英数字と-._のみ入力可能)</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <label for="email_option" class="text-right col-4">通知先メールアドレス</label>
                                    <a href=""
                                       class="padding-left-content-custom default_text_decoration_underline"
                                       id="email_option"><% item.email_option %></a>
                                </div>

                                <div class="row">
                                    <label for="username" class="text-right col-4">氏名</label>
                                    <p class="padding-left-content-custom" id="username"><% item.username %></p>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="chat_role_flg" class="text-right col-4">ササッとTalkロール設定</label>
                                        <div class="col-8" ng-if="fromUpdate">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CHAT_SERVER_USER_CHAT_ROLE_FLG, 'chat_role_flg', Request::get('chat_role_flg', '') ,null,
                                                ['class'=> 'form-control custom-from-group','required', 'ng-model' =>'item.chat_role_flg', 'disabled' => true]) !!}
                                        </div>
                                        <div class="col-8" ng-if="!fromUpdate">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CHAT_SERVER_USER_CHAT_ROLE_FLG, 'chat_role_flg', Request::get('chat_role_flg', '') ,null,
                                                ['class'=> 'form-control','required', 'ng-model' =>'item.chat_role_flg']) !!}
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="row">
                                        <label for="chat_user_flg" class="text-right col-4">ササッとTalk利用状況</label>
                                        <div class="col-8" ng-if="formCreate || register">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_SELECT_DETAIL_REGISTER, 'status', Request::get('status', '1'), null,
                                                ['class'=> 'form-control','required', 'ng-model' =>'item.status']) !!}
                                        </div>
                                        <div class="col-8" ng-if="fromUpdate">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_SELECT_DETAIL_UPDATE, 'status', Request::get('status', '1'), null,
                                                ['class'=> 'form-control','required', 'ng-model' =>'item.status' , 'disabled' => true]) !!}
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_CREATE])
                            <button type="button" class="btn btn-success" ng-click="bulkUsage(1)"
                                    ng-if="formCreate">
                                <i class="far fa-save"></i>登録
                            </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_UPDATE])
                            <button type="button" class="btn btn-warning" ng-click="bulkUsage(2)"
                                    ng-if="fromUpdate && stopChat">
                                <i class="fas fa-ban"></i>停止
                            </button>
                            <button type="button" class="btn btn-warning" ng-click="bulkUsage(3)"
                                    ng-if="fromUpdate && unstopChat">
                                <i class="fas fa-ban"></i>停止解除
                            </button>

                            @endcan

                            @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_DELETE])
                            <button type="button" class="btn btn-danger" ng-click="bulkUsage(0)"
                                    ng-if="showBtnDelete">
                                <i class="far fa-trash-alt"></i>削除
                            </button>
                            @endcanany
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles_after')
    <style>
    </style>
@endpush

@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailTalkController', function ($scope, $rootScope, $http) {
                $scope.item = {};

                $rootScope.$on("openUserDetailsTalk", function(event,data){
                    hasChange = false;
                    $scope.showTitleCreate = false;
                    $scope.showTitleUpdate = false;
                    $scope.formCreate = false;
                    $scope.register = false;
                    $scope.fromUpdate = false;
                    $scope.stopChat = false;
                    $scope.unstopChat = false;
                    $scope.showBtnDelete = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    hideMessages();

                    $http.get(link_ajax + "/" + data.id )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                // button register
                                if ($scope.item.status == null ||
                                    $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_INVALID }} ||
                                    $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR }}) {
                                    $scope.showTitleCreate = true;
                                    $scope.formCreate = true;
                                    $scope.register = true;

                                    if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR }}) {
                                        // Form register status = 90 => display = 0
                                        $scope.item.status = {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_INVALID }}

                                    }
                                } else {
                                    $scope.showTitleUpdate = true;
                                    $scope.fromUpdate = true;

                                    // check action stop or unstop service

                                    if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_STOPPED }} ||
                                            $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR }}) {
                                        $scope.unstopChat = true;
                                    } else if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_VALID }} ||
                                            $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR }} ||
                                            $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR }}) {
                                        $scope.stopChat = true;
                                    }

                                    // update status display
                                    if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_STOPPED }} ||
                                        $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR }}) {

                                        if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR }}) {
                                            // Form register status = 93 => display = 2
                                            $scope.item.status = {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_STOPPED }};
                                        }
                                    } else if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_VALID }} ||
                                                $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR }} ||
                                                $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR }}) {

                                        if ($scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR }} ||
                                            $scope.item.status == {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR }}) {
                                            // Form register status = 91, 92 => display = 1

                                            $scope.item.status = {{ \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_VALID }}
                                        }
                                    }
                                }

                                // button delete
                                if ($scope.item.status != null) {
                                    $scope.showBtnDelete = true;
                                    $scope.item.status = $scope.item.status.toString();
                                }

                                if ($scope.item.chat_role_flg != null) {
                                    $scope.item.chat_role_flg = $scope.item.chat_role_flg.toString()
                                }

                            }
                        });
                    $("#modalDetailItem").modal();

                });

                $scope.bulkUsage = function (type) {
                    // type = 1: create

                    if(type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_REGISTER }}) {
                        $rootScope.$emit("showLoading");
                        $http.post(link_bulk_usage,
                            {
                                cids: [$scope.item.id],
                                item: $scope.item,
                                isSingleData: true,
                                action: '{{ \App\Http\Utils\ChatUtils::ACTION_SINGLE_REGISTER }}',
                                actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_REGISTER }}',
                            }
                        ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                if ($scope.formCreate) {
                                    $scope.formCreate = false
                                }
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                setTimeout(function (){
                                    $("#modalDetailItem").modal('hide');
                                }, 5000)
                            }
                        });
                        hasChange = true;


                    } else if(type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_CANCEL }}) {
                        // type = 0: delete
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '選択されたユーザーのササッとTalk利用者情報を削除します',
                                btnDanger:'はい',
                                callDanger: function(){
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_bulk_usage,
                                        {
                                            cids: [$scope.item.id],
                                            isSingleData: true,
                                            action: '{{ \App\Http\Utils\ChatUtils::ACTION_SINGLE_DELETE }}',
                                            actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_DELETE }}',
                                        }
                                    ).then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                            setTimeout(function (){
                                                $("#modalDetailItem").modal('hide');
                                            }, 5000)
                                        }
                                    });
                                    hasChange = true;

                                }

                            });


                    } else if(type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_STOP }}) {
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '選択されたユーザーのササッとTalk利用を停止します。',
                                btnWarning:'はい',
                                callWarning: function() {
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_bulk_usage,
                                        {
                                            cids: [$scope.item.id],
                                            item: $scope.item,
                                            isSingleData: true,
                                            action: '{{ \App\Http\Utils\ChatUtils::ACTION_SINGLE_STOP }}',
                                            actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_STOP }}',
                                        }
                                    ).then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $scope.stopChat = false;
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                            setTimeout(function (){
                                                $("#modalDetailItem").modal('hide');
                                            }, 5000)
                                        }
                                    });
                                    hasChange = true;

                                }
                            });

                    } else if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_UNSTOP }}) {
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '選択されたユーザーのササッとTalk利用を停止解除します。',
                                btnWarning: 'はい',
                                callWarning: function () {
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_bulk_usage,
                                        {
                                            cids: [$scope.item.id],
                                            item: $scope.item,
                                            isSingleData: true,
                                            action: '{{ \App\Http\Utils\ChatUtils::ACTION_SINGLE_UNSTOP }}',
                                            actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_UNSTOP }}',
                                        }
                                    ).then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $scope.unstopChat = false;
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                            setTimeout(function (){
                                                $("#modalDetailItem").modal('hide');
                                            }, 5000)
                                        }
                                    });
                                    hasChange = true;

                                }
                            });

                    }

                };
            });
        }
    </script>

@endpush

@push('styles_after')
    <style>
        .padding-left-content-custom {
           padding-left: 1.5em;
        }
        .default_text_decoration_underline {
            text-decoration: underline ;
        }
        .custom-from-group > .form-group {
            margin-bottom: 0;
        }
    </style>
@endpush
