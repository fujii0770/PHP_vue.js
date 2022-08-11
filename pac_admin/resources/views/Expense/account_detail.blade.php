<div ng-controller="DetailFormExpenseController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!edit_flg">勘定科目詳細</h4>
                        <h4 class="modal-title" ng-if="edit_flg">勘定科目編集</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!edit_flg">勘定科目詳細</div>
                            <div class="card-header" ng-if="edit_flg">勘定科目編集</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">名称 </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p><% item.account_name %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="account_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">名称 <span style="color: red">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="remarks">必須、重複不可、20文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" required autocomplete="off" class="form-control" ng-model="item.account_name" id="account_name" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="remarks" class="col-md-2 col-sm-2 col-10 text-right-lg">備考 </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="remarks"><% item.remarks %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="remarks">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg">
                                            <div class="col-md-2 col-sm-2 col-11 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.remarks" id="remarks" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label for="remarks" class="col-md-2 col-sm-2 col-10 text-right-lg">作成 </label>
                                            <div class="col-md-8 col-sm-8 col-24 disable">
                                              <p id="create_at"><% item.create_at %> <% item.create_user %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label for="remarks" class="col-md-2 col-sm-2 col-10 text-right-lg">編集 </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                              <p id="update_at"><% item.update_at %> <% item.update_user %></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer"  ng-if="!add_flg">
                            <div class="col-lg-7">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <i class="fas fa-times-circle"></i> 閉じる
                                </button>
                            </div>
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE])
                                <button type="button" class="btn btn-success" ng-click="moveAccount()"
                                        ng-if="!edit_flg">
                                    <i class="far fa-save"></i> 編集
                                </button>
                                <button type="button" class="btn btn-success" ng-click="saveAccount()"
                                        ng-if="edit_flg"> 登録
                                </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE])
                                <button type="button" class="btn btn-danger" ng-click="deleteDetail()"><i class="fas fa-trash-alt"></i> 削除</button>
                            @endcanany
                        </div>

                        <div class="modal-footer" ng-if="add_flg">
                            <div class="col-lg-7">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <i class="fas fa-times-circle"></i> 閉じる
                                </button>
                            </div>
                            <button type="button" class="btn btn-success" ng-click="saveAccount()">
                                 登録
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
        .disable{
            opacity: 0.75;
            user-select: none;
        }
    </style>
@endpush
@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailFormExpenseController', function ($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.edit_flg = false;
                $scope.add_flg = false;

                $rootScope.$on("openUserDetailsExpMAccount", function(event,data){
                    $scope.edit_flg = false;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.account_name = data.account_name;
                    if(!$scope.item.account_name){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.account_name )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                            }
                        });
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openUserDetailsExpMAccountFromDetail", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.account_name = data.account_name;
                    if(!$scope.item.account_name){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.account_name )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openUserDetailsExpMAccountAdd", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg = true;
                    hasChange = false;
                    $scope.item.account_name = "";
                    $scope.item.remarks = "";
                    $scope.item.deleted_at = "";
                    $scope.item.create_user = "";
                    $scope.item.create_at = "";
                    $scope.item.update_user = "";
                    $scope.item.update_at = "";
                    $("#modalDetailItem").modal();
                });

                $scope.moveAccount = function(){
                    $scope.edit_flg = true;
                    $scope.add_flg = false;
                    hasChange = true;
                    account_name = $scope.item.account_name;
                    $rootScope.$emit("openUserDetailsExpMAccountFromDetail",{account_name:account_name});
                };

                $scope.saveAccount = function(){
                    $rootScope.$emit("showLoading");
                    let actionType = 'update';
                    if ( $scope.add_flg) {
                         actionType = 'create';
                    }
                    if(actionType=='update'){//更新
                        $http.post(link_ajax + "/" + $scope.item.account_name,
                              {item: $scope.item}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.item = event.data.item;
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                        
                    }else{//登録
                        $http.put(link_ajax,
                              {item: $scope.item}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                if(!$scope.item.account_name){
                                    $scope.item.account_name = event.data.account_name;
                                }
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                location.reload();
                            }
                        });
                    }
                    hasChange = true;
                };

                $scope.deleteDetail = function () {
                    event.preventDefault();
                    var cids = [];

                    cids.push( $scope.item.account_name );

                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '勘定科目を削除します。',
                                    btnSuccess:'はい',
                                    callSuccess: function(){
                                        $rootScope.$emit("showLoading");
                                        $http.post(link_bulk_usage, {cids: cids})
                                            .then(function (event) {
                                                $rootScope.$emit("hideLoading");
                                                if (event.data.status == false) {
                                                    $(".message-info").append(showMessages(event.data.message, 'danger', 10000));
                                                } else {
                                                    $(".message-info").append(showMessages(event.data.message, 'success', 10000));
                                                    location.reload();
                                                }
                                            });
                                    }
                                });
                };


            })
        }
    </script>
@endpush
