<div ng-controller="DetailFormIssuanceController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!item.frm_id">利用ユーザ詳細情報登録</h4>
                        <h4 class="modal-title" ng-if="item.frm_id">利用ユーザ詳細情報更新</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">
                            @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_CREATE])
                                <button type="button" class="btn btn-success" ng-click="saveUserInfo(create)"
                                        ng-if="!item.frm_id">
                                    <i class="far fa-save"></i>登録
                                </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_UPDATE])
                                <button type="button" class="btn btn-success" ng-click="saveUserInfo(update)"
                                        ng-if="item.frm_id">
                                    <i class="far fa-save"></i>更新
                                </button>
                            @endcanany
                        </div>
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!item.frm_id">利用ユーザ詳細情報登録</div>
                            <div class="card-header" ng-if="item.frm_id">利用ユーザ詳細情報更新</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="user_name"
                                                   class="col-md-3 col-sm-3 col-12 text-right-lg">氏名 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="user_name"><% item.user_name %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="email"
                                                   class="col-md-3 col-sm-3 col-12 text-right-lg">メールアドレス </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="email"><% item.email %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="department_name" class="col-md-3 col-sm-3 col-12 text-right-lg">部署 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="department_name"><% item.department_name %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="position_name"
                                                   class="col-md-3 col-sm-3 col-12 text-right-lg">役職 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="position_name"><% item.position_name %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="frmSrvUserFlg"
                                                   class="col-md-3 col-sm-3 col-24 control-label">ササッと明細利用</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::FRM_SRV_USER_FLG, 'frmSrvUserFlg', Request::get('frmSrvUserFlg', '') ,null,
                                                ['class'=> 'form-control','required', 'ng-model' =>'item.frm_srv_user_flg']) !!}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
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
            appPacAdmin.controller('DetailFormIssuanceController', function ($scope, $rootScope, $http) {
                $scope.item = {};

                $rootScope.$on("openUserDetailsFormIssuance", function(event,data){
                    hasChange = false;
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
                                $scope.item.frm_srv_user_flg = event.data.item.frm_srv_user_flg.toString();
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $scope.saveUserInfo = function(){
                    $rootScope.$emit("showLoading");
                    let actionType = 'create';
                    if ( $scope.item.frm_id) {
                        actionType = 'update';
                    }
                    $http.post(link_ajax + "/" + $scope.item.id,
                        {
                            frm_srv_user_flg: $scope.item.frm_srv_user_flg,
                            action: actionType,
                        }).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                if(!$scope.item.frm_id){
                                    $scope.item.frm_id = event.data.frm_id;
                                }
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                    hasChange = true;
                };
            })
        }
    </script>
@endpush
