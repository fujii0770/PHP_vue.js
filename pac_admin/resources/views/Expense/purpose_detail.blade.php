<div ng-controller="DetailFormExpenseController">
    <form class="form_edit" action="" method="" onsubmit="return false;" name="purposeForm">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!edit_flg">目的詳細</h4>
                        <h4 class="modal-title" ng-if="edit_flg">目的編集</h4>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="card mt-3">
                            <div class="card-header" ng-if="!edit_flg">目的詳細</div>
                            <div class="card-header" ng-if="edit_flg">目的編集</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label for="purpose_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">名称 </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p id="purpose_name"><% item.purpose_name %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="purpose_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">名称<span style="color: red">*</span> </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="remarks">必須、重複不可、20文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input name="name_input" type="text" required autocomplete="off" class="form-control" ng-model="item.purpose_name" id="purpose_name" ng-readonly="false" ng-pattern="/^[０-９0-9a-zA-Zぁ-んーァ-ンヴーｧ-ﾝﾞﾟ\-\u4E00-\u9FFF]*$/"/>
                                                <p ng-show="purposeForm.name_input.$error.pattern">記号の入力は不可です。</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="describe"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">説明 </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="describe"><% item.describe %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="remarks">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.describe" id="describe" ng-readonly="false"/>
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
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE])
                                <button type="button" class="btn btn-success" ng-click="movePurpose()"
                                        ng-if="!edit_flg">
                                    <i class="far fa-save"></i> 編集
                                </button>
                                <button type="button" class="btn btn-success" ng-click="savePurpose()"
                                        ng-if="edit_flg"> 更新
                                </button>
                            @endcanany
                                <button class="btn btn-primary m-0" ng-click="poposedisplay()"> 自動仕分設定</button>
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_DELETE])
                                <button type="button" class="btn btn-danger" ng-click="deleteDetail()"><i class="fas fa-trash-alt"></i> 削除</button>
                            @endcanany
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                        </div>

                        <div class="modal-footer" ng-if="add_flg">
                            <button type="button" class="btn btn-success" ng-click="savePurpose()"  ng-disabled="!purposeForm.name_input.$valid">
                                 登録
                            </button>
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

                $rootScope.$on("openUserDetailsExpMPurpose", function(event,data){
                    $scope.edit_flg = false;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.purpose_name = data.purpose_name;
                    if(!$scope.item.purpose_name){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.purpose_name )
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
                $rootScope.$on("openUserDetailsExpMPurposeFromDetail", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.purpose_name = data.purpose_name;
                    if(!$scope.item.purpose_name){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.purpose_name)
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

                $rootScope.$on("openUserDetailsExpMPurposeAdd", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg = true;
                    hasChange = false;
                    $scope.item.purpose_name = "";
                    $scope.item.describe = "";
                    $scope.item.remarks = "";
                    $scope.item.create_user = "";
                    $scope.item.create_at = "";
                    $scope.item.update_user = "";
                    $scope.item.update_at = "";
                    $("#modalDetailItem").modal();
                });

                $scope.movePurpose = function(){
                    $scope.edit_flg = true;
                    $scope.add_flg = false;
                    hasChange = true;
                    purpose_name = $scope.item.purpose_name;
                    $rootScope.$emit("openUserDetailsExpMPurposeFromDetail",{purpose_name:purpose_name});
                };

                $scope.savePurpose = function(){
                    $rootScope.$emit("showLoading");
                    let actionType = 'update';
                    if ( $scope.add_flg) {
                         actionType = 'create';
                    }
                    if(actionType=='update'){//更新
                        $http.post(link_ajax + "/" + $scope.item.purpose_name,
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
                        $http.put(link_ajax ,
                              {item: $scope.item}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                if(!$scope.item.purpose_name){
                                    $scope.item.purpose_name = event.data.purpose_name;
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

                    cids.push( $scope.item.purpose_name );

                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '目的を削除します。',
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

                    //詳細表示押下時
                    $scope.poposedisplay = function () {
                        // エレメントを作成
                        var ele = document.createElement('input');
                        // データを設定
                        ele.setAttribute('type', 'hidden');
                        ele.setAttribute('name', 'purpose_name');
                        ele.setAttribute('value', $scope.item.purpose_name);
                        // 要素を追加
                        document.adminForm.appendChild(ele);

                        document.adminForm.action = link_ajax_journal;
                        document.adminForm.submit();
                    };

            })
        }
    </script>
@endpush
