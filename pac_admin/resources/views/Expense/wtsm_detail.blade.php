<div ng-controller="DetailFormExpenseController">
    <form class="form_edit" action="" method="" onsubmit="return false;" name="wtsmForm">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!edit_flg">用途詳細</h4>
                        <h4 class="modal-title" ng-if="edit_flg">用途編集</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!edit_flg">用途詳細</div>
                            <div class="card-header" ng-if="edit_flg">用途編集</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">名称　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p id="wtsm_name"><% item.wtsm_name %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="wtsm_name_edit"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">名称<span style="color: red" ng-if="edit_flg">*</span>　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="wtsm_name_comment">必須、重複不可、20文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input name="name_input" type="text" required autocomplete="off" class="form-control" ng-model="item.wtsm_name" id="wtsm_name_edit" ng-readonly="false" ng-pattern="/^[０-９0-9a-zA-Zぁ-んーァ-ンヴーｧ-ﾝﾞﾟ\-\u4E00-\u9FFF]*$/"/>
                                                <p ng-show="wtsmForm.name_input.$error.pattern">記号の入力は不可です。</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="wtsm_describe_edit"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">説明　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="wtsm_describe"><% item.wtsm_describe %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="wtsm_describe_comment">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.wtsm_describe" id="wtsm_describe_edit" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!edit_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">人数入力　　</label>
                                            <div class="col-md-2 col-sm-2 col-2" ng-if="!edit_flg">
                                                <label ng-show="item.num_people_option == 0" class="disable">なし</label>
                                                <label ng-show="item.num_people_option == 1" class="disable">あり　　任意</label>
                                                <label ng-show="item.num_people_option == 2" class="disable">あり　　必須</label>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="num_people_describe_comment">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg"></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="num_people_describe">説明：<% item.num_people_describe %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="edit_flg">
                                        <div style="height:12px;">
                                            <span style="margin-left:8px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:5px;">人数入力　　
                                            <input type="checkbox" ng-model="item.num_people" id="num_people" ng-true-value="1" ng-false-value="0"/>
                                            </span>
                                            <span style="margin-left:0px; 
                                            background:white; 
                                            border-radius:5px">あり　　</span>
                                            <span style="margin-left:10px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:10px;">入力可能範囲：1-99,999</span>
                                        </div>
                                        <div style="border:2px solid #000066; 
                                        padding:12px 12px 10px; 
                                        font-size:1em;border-radius:5px;">
                                            <input type="checkbox" ng-model="item.num_people_require" id="num_people_require" ng-true-value="1" ng-false-value="0" ng-disabled="!item.num_people"/>
                                            <span style="margin-left:0px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:5px;">必須</span>
                                            <div class="row">
                                                <div class="col-md-1 col-sm-1 col-1 ">
                                                    <p>説明</p>
                                                </div>
                                                <div class="col-md-9 col-sm-9 col-26">
                                                    <input type="text" autocomplete="off" class="form-control" ng-model="item.num_people_describe" id="num_people_describe_edit" ng-readonly="!item.num_people"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group" ng-if="!edit_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">詳細入力　　</label>
                                            <div class="col-md-2 col-sm-2 col-2" ng-if="!edit_flg">
                                                <label ng-show="item.detail_option == 0" class="disable">なし</label>
                                                <label ng-show="item.detail_option == 1" class="disable">あり　　任意</label>
                                                <label ng-show="item.detail_option == 2" class="disable">あり　　必須</label>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="wtsm_describe_comment">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg"></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="wtsm_detail_describe">説明：<% item.detail_describe %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="edit_flg">
                                        <div style="height:12px;">
                                            <span style="margin-left:8px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:5px;">詳細入力　　
                                            <input type="checkbox" ng-model="item.detail" id="detail" ng-true-value="1" ng-false-value="0""/>
                                            </span>
                                            <span style="margin-left:0px; 
                                            background:white; 
                                            border-radius:5px">あり　　</span>
                                            <span style="margin-left:10px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:10px;">入力可能文字数：1,000文字まで</span>
                                        </div>
                                        <div style="border:2px solid #000066; 
                                        padding:12px 12px 10px; 
                                        font-size:1em;border-radius:5px;">
                                            <input type="checkbox" ng-model="item.detail_require" id="num_detail_require" ng-true-value="1" ng-false-value="0" ng-disabled="!item.detail""/>
                                            <span style="margin-left:0px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:5px;">必須</span>
                                            <div class="row">
                                                <div class="col-md-1 col-sm-1 col-1 ">
                                                    <p>説明</p>
                                                </div>
                                                <div class="col-md-9 col-sm-9 col-26">
                                                    <input type="text" autocomplete="off" class="form-control" ng-model="item.detail_describe" id="detail_describe_edit" ng-readonly="!item.detail"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">消費税　　　</label>
                                            <div class="col-md-2 col-sm-2 col-2" ng-if="!edit_flg">
                                                <label ng-show="item.tax_option == 0" class="disable">対象外</label>
                                                <label ng-show="item.tax_option == 1" class="disable">対象</label>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-10" ng-if="edit_flg">
                                                    <input type="radio" ng-model="item.tax_option" id="enable_tax_1" ng-value="1" /> 対象
                                                    <input type="radio" ng-model="item.tax_option" id="enable_tax_0" class="margin-left-10" ng-value="0" /> 対象外
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">領収書・証憑 </label>
                                            <div class="col-md-2 col-sm-2 col-2" ng-if="!edit_flg">
                                                <label ng-show="item.voucher_option == 0" class="disable">不要</label>
                                                <label ng-show="item.voucher_option == 1" class="disable">必要</label>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-10" ng-if="edit_flg">
                                                    <input type="radio" ng-model="item.voucher_option" id="enable_voucher_1" ng-value="1" /> 必要
                                                    <input type="radio" ng-model="item.voucher_option" id="enable_voucher_0" class="margin-left-10" ng-value="0" /> 不要
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="remarks_edit" class="col-md-2 col-sm-2 col-10 text-right-lg">備考　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="remarks"><% item.remarks %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="remarks_comment">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg">
                                            <div class="col-md-2 col-sm-2 col-11 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.remarks" id="remarks_edit" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label for="create_at" class="col-md-2 col-sm-2 col-10 text-right-lg">作成　　　　</label>
                                            <div class="col-md-8 col-sm-8 col-24 disable">
                                              <p id="create_at"><% item.create_at %> <% item.create_user %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label for="update_at" class="col-md-2 col-sm-2 col-10 text-right-lg">編集　　　　</label>
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
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE])
                                <button type="button" class="btn btn-danger" ng-click="deleteDetail()"><i class="fas fa-trash-alt"></i> 削除</button>
                            @endcanany
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                        </div>

                        <div class="modal-footer" ng-if="add_flg">
                            <button type="button" class="btn btn-success" ng-click="savePurpose()" ng-disabled="!wtsmForm.name_input.$valid">
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

                $rootScope.$on("openUserDetailsExpMWtsm", function(event,data){
                    $scope.edit_flg = false;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.wtsm_name = data.wtsm_name;
                    if(!$scope.item.wtsm_name){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.wtsm_name )
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
                $rootScope.$on("openUserDetailsExpMWtsmFromDetail", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.wtsm_name = data.wtsm_name;
                    if(!$scope.item.wtsm_name){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.wtsm_name )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                if (event.data.item.num_people_option > 0) {
                                    $scope.item.num_people = 1;
                                }
                                if (event.data.item.num_people_option == 2) {
                                    $scope.item.num_people_require = 1;
                                }
                                if (event.data.item.detail_option > 0) {
                                    $scope.item.detail = 1;
                                }
                                if (event.data.item.detail_option == 2) {
                                    $scope.item.detail_require = 1;
                                }
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openUserDetailsExpMWtsmAdd", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg = true;
                    hasChange = false;
                    $scope.item.wtsm_name = "";
                    $scope.item.wtsm_describe = "";
                    $scope.item.num_people_option = "0";
                    $scope.item.num_people_describe = "";
                    $scope.item.detail_option = "0";
                    $scope.item.detail_describe = "";
                    $scope.item.tax_option = 1;
                    $scope.item.voucher_option = 0;
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
                    wtsm_name = $scope.item.wtsm_name;
                    $rootScope.$emit("openUserDetailsExpMWtsmFromDetail",{wtsm_name:wtsm_name});
                };

                $scope.savePurpose = function(){
                    $rootScope.$emit("showLoading");
                    let actionType = 'update';
                    if ( $scope.add_flg) {
                         actionType = 'create';
                    }

                    if($scope.item.num_people == 1){
                        if($scope.item.num_people_require == 1){
                            $scope.item.num_people_option = 2;
                        } else {
                            $scope.item.num_people_option = 1;
                        }
                    } else {
                        $scope.item.num_people_option = 0;
                    }
                    if($scope.item.detail == 1){
                        if($scope.item.detail_require == 1){
                            $scope.item.detail_option = 2;
                        } else {
                            $scope.item.detail_option = 1;
                        }
                    } else {
                        $scope.item.detail_option = 0;
                    }
                    if(actionType=='update'){//更新
                        $http.post(link_ajax + "/" + $scope.item.wtsm_name,
                              {item: $scope.item}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.item = event.data.item;
                                if (event.data.item.num_people_option > 0) {
                                    $scope.item.num_people = 1;
                                }
                                if (event.data.item.num_people_option == 2) {
                                    $scope.item.num_people_require = 1;
                                }
                                if (event.data.item.detail_option > 0) {
                                    $scope.item.detail = 1;
                                }
                                if (event.data.item.detail_option == 2) {
                                    $scope.item.detail_require = 1;
                                }
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
                                if(!$scope.item.wtsm_name){
                                    $scope.item.wtsm_name = event.data.wtsm_name;
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

                    cids.push( $scope.item.wtsm_name );

                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '用途を削除します。',
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
