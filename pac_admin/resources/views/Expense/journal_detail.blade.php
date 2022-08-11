<div ng-controller="DetailFormExpenseController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!edit_flg">仕訳詳細</h4>
                        <h4 class="modal-title" ng-if="edit_flg">仕訳編集</h4>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!edit_flg">仕訳詳細</div>
                            <div class="card-header" ng-if="edit_flg">仕訳編集</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">用途　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="wtsm_name"><% item.wtsm_name %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26" ng-if="edit_flg">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listWtsm, 'wtsm_name', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item.wtsm_name', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="wtsm_name_edit"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">用途<span style="color: red" ng-if="edit_flg">*</span>　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="wtsm_name_comment">必須</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listWtsm, 'wtsm_name', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item.wtsm_name', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">勘定科目　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable"  ng-if="!edit_flg">
                                                <p id="account_name"><% item.account_name %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26" ng-if="edit_flg">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listAccount, 'account_name', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item.account_name', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="account_name_edit"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">勘定科目<span style="color: red" ng-if="edit_flg">*</span>　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="account_name_comment">必須</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listAccount, 'account_name', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item.account_name', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">補助科目　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="sub_account_name"><% item.sub_account_name %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="sub_account_name_comment">50文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.sub_account_name" id="sub_account_name_edit" ng-readonly="false" maxlength="50"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="sub_account_name_edit"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">補助科目　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="sub_account_name"><% item.sub_account_name %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="sub_account_name_comment">50文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" required autocomplete="off" class="form-control" ng-model="item.sub_account_name" id="sub_account_name_edit" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="remarks_edit" class="col-md-2 col-sm-2 col-10 text-right-lg">摘要　　　　</label>
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

                                    <div class="form-group" ng-if="!edit_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">金額　　　　</label>
                                            <div class="col-md-3 col-sm-3 col-3 disable" ng-if="!edit_flg">
                                                <label ng-show="!item.criteria_amount" style="display:inline;">なし</label>
                                                <label ng-show="item.criteria_amount" style="display:inline;">あり　金額<% item.criteria_amount_sign_value %><% item.criteria_amount %></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="!edit_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">人数　　　　</label>
                                            <div class="col-md-2 col-sm-2 col-2 disable" ng-if="!edit_flg">
                                                <label ng-show="!item.criteria_people" style="display:inline;">なし</label>
                                                <label ng-show="item.criteria_people" style="display:inline;">あり　<% item.criteria_people %>名以上</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="!edit_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">金額／人数　</label>
                                            <div class="col-md-4 col-sm-4 col-4 disable" ng-if="!edit_flg">
                                                <label ng-show="!item.criteria_amount" style="display:inline;">なし</label>
                                                <label ng-show="item.criteria_amount" style="display:inline;">あり　金額／人数<% item.criteria_amount_people_sign_value %><% item.criteria_amount_people %></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="!edit_flg">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">詳細　　　　</label>
                                            <div class="col-md-4 col-sm-4 col-4 disable" ng-if="!edit_flg">
                                                <label ng-show="!item.criteria_detail" style="display:inline;">なし</label>
                                                <label ng-show="item.criteria_detail" style="display:inline;">あり　<% item.criteria_detail %><% item.criteria_detail_cond_value %></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="edit_flg">
                                        <div style="height:12px;">
                                                <span style="margin-left:8px; 
                                                padding:6px 5px; 
                                                background:white; 
                                                border-radius:5px;">条件
                                                </span>
                                        </div>
                                        <div class="border_style">
                                            <div class="form-group">
                                                <div style="height:12px;">
                                                    <span style="margin-left:8px; 
                                                    padding:6px 5px; 
                                                    background:white; 
                                                    border-radius:5px;">金額　　　　
                                                    <input type="checkbox" ng-model="item.num_amount" id="num_amount" ng-true-value="1" ng-false-value="0"/>
                                                    </span>
                                                    <span style="margin-left:0px; 
                                                    background:white; 
                                                    border-radius:5px">あり　　</span>
                                                </div>
                                                <div class="border_style">
                                                    <div class="row">
                                                        <div class="col-md-2 col-sm-2 col-10 ">
                                                            <p class="p_style">金額</p>
                                                        </div>
                                                        <div class="col-md-2 col-sm-2 col-2">
                                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::INEQUALITY_SIGN, 'criteria_amount_sign', Request::get('criteria_amount_sign', '') ,null,
                                                            ['class'=> 'form-control','required', 'ng-model' =>'item.criteria_amount_sign', 'ng-disabled' =>'!item.num_amount']) !!}
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-3">
                                                            <input type="number" min="0" step="100" autocomplete="off" class="form-control" ng-model="item.criteria_amount" id="criteria_amount" ng-readonly="!item.num_amount"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div style="height:12px;">
                                                    <span style="margin-left:8px; 
                                                    padding:6px 5px; 
                                                    background:white; 
                                                    border-radius:5px;">人数　　　　
                                                    <input type="checkbox" ng-model="item.num_people" id="num_people" ng-true-value="1" ng-false-value="0"/>
                                                    </span>
                                                    <span style="margin-left:0px; 
                                                    background:white; 
                                                    border-radius:5px">あり　　</span>
                                                </div>
                                                <div class="border_style">
                                                    <div class="row">
                                                        <div class="col-md-2 col-sm-2 col-10 ">
                                                        </div>
                                                        <div class="col-md-2 col-sm-2 col-2">
                                                            <input type="number" min="0" autocomplete="off" class="form-control" ng-model="item.criteria_people" id="criteria_people" ng-readonly="!item.num_people"/>
                                                        </div>
                                                        <div class="col-md-2 col-sm-2 col-2 ">
                                                            <p class="p_style" style="text-align:left">名以上</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <div style="height:12px;">
                                                    <span style="margin-left:8px; 
                                                    padding:6px 5px; 
                                                    background:white; 
                                                    border-radius:5px;">金額／人数　
                                                    <input type="checkbox" ng-model="item.num_amount_people" id="num_amount_people" ng-true-value="1" ng-false-value="0"/>
                                                    </span>
                                                    <span style="margin-left:0px; 
                                                    background:white; 
                                                    border-radius:5px">あり　　</span>
                                                </div>
                                                <div class="border_style">
                                                    <div class="row">
                                                        <div class="col-md-2 col-sm-2 col-2 ">
                                                            <p class="p_style">金額／人数</p>
                                                        </div>
                                                        <div class="col-md-2 col-sm-2 col-2">
                                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::INEQUALITY_SIGN, 'criteria_amount_people_sign', Request::get('criteria_amount_people_sign', '') ,null,
                                                            ['class'=> 'form-control','required', 'ng-model' =>'item.criteria_amount_people_sign', 'ng-disabled' =>'!item.num_amount_people']) !!}
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-3">
                                                            <input type="number" min="0" step="100" autocomplete="off" class="form-control" ng-model="item.criteria_amount_people" id="criteria_amount_people" ng-readonly="!item.num_amount_people"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div style="height:12px;">
                                                    <span style="margin-left:8px; 
                                                    padding:6px 5px; 
                                                    background:white; 
                                                    border-radius:5px;">詳細　　　　
                                                    <input type="checkbox" ng-model="item.num_detail" id="num_detail" ng-true-value="1" ng-false-value="0"/>
                                                    </span>
                                                    <span style="margin-left:0px; 
                                                    background:white; 
                                                    border-radius:5px">あり　　</span>
                                                </div>
                                                <div class="border_style">
                                                    <div class="row">
                                                        <div class="col-md-2 col-sm-2 col-2 ">
                                                        </div>
                                                        <div class="col-md-4 col-sm-4 col-4">
                                                            <input type="text" autocomplete="off" class="form-control" ng-model="item.criteria_detail" id="criteria_detail" ng-readonly="!item.num_detail"/>
                                                        </div>
                                                        <div class="col-md-4 col-sm-4 col-4">
                                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::DETAIL_COND, 'criteria_detail_cond', Request::get('criteria_detail_cond', '') ,null,
                                                            ['class'=> 'form-control','required', 'ng-model' =>'item.criteria_detail_cond', 'ng-disabled' =>'!item.num_detail']) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="memo_edit" class="col-md-2 col-sm-2 col-10 text-right-lg">備考　　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg">
                                                <p id="memo"><% item.memo %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg">
                                                <p id="memo_comment">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg">
                                            <div class="col-md-2 col-sm-2 col-11 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.memo" id="memo_edit" ng-readonly="false" maxlength="100"/>
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
                        <div class="message message-info"></div>

                        <!-- Modal footer -->
                        <div class="modal-footer"  ng-if="!add_flg">
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE])
                                <button type="button" class="btn btn-success" ng-click="movePurpose()"
                                        ng-if="!edit_flg">
                                    <i class="far fa-save"></i> 編集
                                </button>
                                <button type="button" class="btn btn-success" ng-click="savePurpose()"
                                        ng-if="edit_flg"> 登録
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
                            <button type="button" class="btn btn-success" ng-click="savePurpose()">
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
        .border_style{
            border:2px solid #dcdcdc; 
            padding:12px 12px 10px; 
            border-radius:5px;
        }
        .p_style{
            padding-top:4px;
            text-align: right;
        }
    </style>
@endpush
@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailFormExpenseController', function ($scope, $rootScope, $http) {
                $scope.purpose_name = {!! json_encode($purpose_name) !!};
                $scope.item = {};
                $scope.edit_flg = false;
                $scope.add_flg = false;

                $rootScope.$on("openUserDetailsExpMWtsm", function(event,data){
                    $scope.edit_flg = false;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    if(!$scope.item.id){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.id )
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
                    $scope.item.id = data.id;
                    if(!$scope.item.id){
                        $scope.edit_flg = true;
                    }
                    hideMessages();

                    $http.get(link_ajax + "/" + $scope.item.id )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                //各項目に値が設定されていたらチェックボックスをオンにする
                                if (event.data.item.criteria_amount) {
                                    $scope.item.num_amount = 1;
                                }
                                if (event.data.item.criteria_people) {
                                    $scope.item.num_people = 1;
                                }
                                if (event.data.item.criteria_amount_people) {
                                    $scope.item.num_amount_people = 1;
                                }
                                if (event.data.item.criteria_detail) {
                                    $scope.item.num_detail = 1;
                                }
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openExpMJournalAdd", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg = true;
                    hasChange = false;
                    $scope.item.purpose_name = $scope.purpose_name;
                    $scope.item.wtsm_name = "";
                    $scope.item.account_name = "";
                    $scope.item.sub_account_name = "";
                    $scope.item.remarks = "";
                    $scope.item.memo = "";
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
                    id = $scope.item.id;
                    $rootScope.$emit("openUserDetailsExpMWtsmFromDetail",{id:id});
                };

                $scope.savePurpose = function(){
                    $rootScope.$emit("showLoading");
                    let actionType = 'update';
                    if ( $scope.add_flg) {
                         actionType = 'create';
                    }

                    //チェックオフの項目は空項目とする。
                    if($scope.item.num_amount == 0){
                        $scope.item.criteria_amount = "";
                    }
                    if($scope.item.num_people == 0){
                        $scope.item.criteria_people = "";
                    }
                    if($scope.item.num_amount_people == 0){
                        $scope.item.criteria_amount_people = "";
                    }
                    if($scope.item.num_detail == 0){
                        $scope.item.criteria_detail = "";
                    }

                    if(actionType=='update'){//更新
                        $http.post(link_ajax + "/" + $scope.item.id,
                              {item: $scope.item}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.item = event.data.item;
                                //各項目に値が設定されていたらチェックボックスをオンにする
                                if (event.data.item.criteria_amount) {
                                    $scope.item.num_amount = 1;
                                }
                                if (event.data.item.criteria_people) {
                                    $scope.item.num_people = 1;
                                }
                                if (event.data.item.criteria_amount_people) {
                                    $scope.item.num_amount_people = 1;
                                }
                                if (event.data.item.criteria_detail) {
                                    $scope.item.num_detail = 1;
                                }
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                        
                    }else{//登録
                        $http.put(link_ajax + "/" + $scope.item.id,
                              {item: $scope.item}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                if(!$scope.item.id){
                                    $scope.item.id = event.data.id;
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

                    cids.push( $scope.item.id );

                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '仕訳を削除します。',
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
