<div ng-controller="DetailFormExpenseController">
    <form class="form_edit" action="" method="" onsubmit="return false;" name="expenseForm">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!edit_flg && !search_flg && !add_flg">フォーマット詳細</h4>
                        <h4 class="modal-title" ng-if="edit_flg && !search_flg || relation_flg && add_flg">フォーマット登録</h4>
                        <h4 class="modal-title" ng-if="search_flg">精算様式関連設定</h4>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>

                    <!-- Modal body --> 
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!edit_flg && !search_flg  && !add_flg">精算フォーマット詳細</div>
                            <div class="card-header" ng-if="edit_flg && !add_flg">精算フォーマット編集</div>
                            <div class="card-header" ng-if="edit_flg && !search_flg && add_flg">精算申請フォーマット登録</div>
                            <div class="card-header" ng-if="search_flg || relation_flg && add_flg">関連精算申請フォーマット登録</div>
                            <div class="card-body">
                                <div class="form-group" ng-if="search_flg && !add_flg">
                                    <div class="row">
                                        <div class="col-md-9 col-sm-9 col-26" style="display: inline-block;">
                                            <input type="text" required autocomplete="off"  ng-model="item.form_code_adv" id="form_code_adv" ng-readonly="false" required maxlength="20" required placeholder="事前申請の様式コードを入力"/>
                                            <button type="button" class="btn btn-success" ng-click="SearchPurpose()"> 検索</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" ng-if="!search_flg  || relation_flg">
                                    <div class="form-group" ng-if="!add_flg">
                                        <div class="row">
                                            <label for="form_code"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">様式コード</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p id="form_code"><% item.form_code %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="add_flg">
                                        <div class="row">
                                            <label for="form_code"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">様式コード<span style="color: red">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="remarks">必須、重複不可、20文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" required autocomplete="off" class="form-control" ng-model="item.form_code" id="form_code" ng-readonly="false" required maxlength="20" required />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="form_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">様式名<span style="color: red">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg || relation_flg">
                                                <p id="remarks">20文字まで</p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg && !add_flg">
                                                <p id="describe"><% item.form_name %></p>
                                            </div>
                                            
                                        </div>
                                        <div class="row" ng-if="edit_flg || relation_flg">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.form_name" id="form_name" ng-readonly="false" required maxlength="20" required/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="form_describe" class="col-md-2 col-sm-2 col-10 text-right-lg">説明</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg && !add_flg">
                                                <p id="form_describe"><% item.form_describe %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg || relation_flg">
                                                <p id="remarks">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg || relation_flg">
                                            <div class="col-md-2 col-sm-2 col-11 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.form_describe" id="form_describe" ng-readonly="false"  maxlength="100"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="form_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">対象合計金額<span style="color: red">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg && !relation_flg" >
                                                <p id="describe"><% item.total_amt_min %>～<% item.total_amt_max %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg || relation_flg">
                                                <input type="number" autocomplete="off" ng-model="item.total_amt_min" id="total_amt_min" ng-readonly="false" min="0" />-<input type="number" autocomplete="off" ng-model="item.total_amt_max" id="total_amt_max" ng-readonly="false" min="0" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="items_max"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">最大明細行数<span style="color: red">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg && !relation_flg">
                                                <p id="describe"><% item.items_max %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg || relation_flg">
                                                <input type="number" autocomplete="off" class="form-control" ng-model="item.items_max" id="items_max" ng-readonly="false" ng-dirty min="0"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="validity_period_from"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">有効期間<span style="color: red">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg && !add_flg">
                                                <p id="time"><% period_from %>～<% period_to %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26"  ng-if="edit_flg || relation_flg">
                                                <input type="date" autocomplete="off" ng-model="item.validity_period_from" id="validity_period_from" ng-readonly="false"/>ー<input type="date" autocomplete="off" ng-model="item.validity_period_to" id="validity_period_to" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!edit_flg && !relation_flg">
                                        <div class="row">
                                            <label for="purpose" class="col-md-2 col-sm-2 col-10 text-right-lg">目的</label>
                                            <div style="display: inline-block;">
                                            <span ng-repeat="pur_data in f_pur">●<% pur_data %></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="edit_flg || relation_flg">
                                        <div style="height:12px;">
                                            <span style="margin-left:8px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:5px;">目的
                                            <span style="margin-left:0px; 
                                            background:white; 
                                            border-radius:5px">1つ以上選択</span>
                                            <span style="margin-left:10px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:10px;"></span>
                                        </div>
                                        <div style="border:2px solid #000066; 
                                        padding:12px 12px 10px; 
                                        font-size:1em;border-radius:5px;">
                                            <div class="col-md-12 col-sm-12 col-12" style="display: inline-block;" ng-if="edit_flg && !add_flg || relation_flg">
                                                <span ng-repeat="pur_data in f_pur" ng-if="edit_flg && !add_flg">
                                                <input type="checkbox" name="purpose[]" ng-click='PurposeCheck(pur_data)' checked /><% pur_data %>
                                                </span>
                                                <span ng-repeat=" wm_data in view_pur" ng-if="relation_flg">●<% wm_data %></span>
                                                <span ng-repeat="pur_data in purpose">
                                                <input type="checkbox" name="purpose[]" ng-click='PurposeCheck(pur_data.purpose_name)' /><% pur_data.purpose_name %>
                                                </span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-12" ng-if="edit_flg && add_flg">
                                            @foreach($purpose as $pur)
                                                <input type="checkbox" name="purpose[]" 
                                                value="{{ $pur->purpose_name }}" ng-click='PurposeCheck("{{$pur->purpose_name}}")'/>
                                              <span  >{{$pur->purpose_name}}</span>
                                            @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="!edit_flg && !relation_flg">
                                        <div class="row">
                                            <label for="wtsm_name" class="col-md-2 col-sm-2 col-10 text-right-lg">用途</label>
                                                <div style="display: inline-block;">
                                                <span ng-repeat=" wm_data in f_wtsm">●<% wm_data %></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="edit_flg || relation_flg">
                                        <div style="height:12px;">
                                            <span style="margin-left:8px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:5px;">用途
                                            <span style="margin-left:0px; 
                                            background:white; 
                                            border-radius:5px">1つ以上選択</span>
                                            <span style="margin-left:10px; 
                                            padding:6px 5px; 
                                            background:white; 
                                            border-radius:10px;"></span>
                                        </div>
                                        <div style="border:2px solid #000066; 
                                        padding:12px 12px 10px; 
                                        font-size:1em;border-radius:5px;">
                                            <div class="col-md-12 col-sm-12 col-12" style="display: inline-block;" ng-if="edit_flg && !add_flg || relation_flg">
                                                <span ng-repeat="wtsm_data in f_wtsm"  ng-if="edit_flg && !add_flg">
                                                <input type="checkbox" name="wtsm[]" ng-click='WtsmCheck(wtsm_data)' checked /><% wtsm_data %>
                                                </span>
                                                <span ng-repeat=" wm_data in view_wm" ng-if="relation_flg">●<% wm_data %></span>
                                                <span ng-repeat="wtsm_data in wtsm">
                                                <input type="checkbox" name="wtsm[]" ng-click='WtsmCheck(wtsm_data.wtsm_name)' /><% wtsm_data.wtsm_name %>
                                                </span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-12" ng-if="edit_flg && add_flg">
                                            @foreach($wtsm as $wt)
                                            <input type="checkbox"  name="wtsm[]" 
                                            value="{{ $wt->wtsm_name }}" ng-click='WtsmCheck("{{ $wt->wtsm_name }}")'/>
                                            <span>{{$wt->wtsm_name}}</span>
                                            @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="remarks"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg"  ng-if="!search_flg">備考</label>
                                                   
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="!edit_flg && !add_flg && !search_flg">
                                                <p id="describe"><% item.remarks %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg || relation_flg">
                                                <p id="remarks">100文字まで</p>
                                            </div>
                                        </div>
                                        <div class="row" ng-if="edit_flg || relation_flg">
                                            <div class="col-md-2 col-sm-2 col-10 ">
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26">
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item.remarks" id="remarks" ng-readonly="false" maxlength="100"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="relation_flg">
                                        <div class="row">
                                            <label for="relation_file_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg">関連様式ファイル名</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" >
                                                <p id="describe"><% item.origin_file_name %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="origin_file_name"
                                                   class="col-md-2 col-sm-2 col-10 text-right-lg" ng-if="!search_flg">様式ファイル<span style="color: red" ng-if="edit_flg && add_flg || relation_flg">*</span></label>
                                            <div class="col-md-9 col-sm-9 col-26 disable" ng-if="edit_flg && !add_flg || !edit_flg &&!add_flg && !search_flg">
                                                <p id="describe"><% item.origin_file_name %></p>
                                            </div>
                                            <div class="col-md-9 col-sm-9 col-26" ng-if="edit_flg && add_flg || relation_flg">
                                            <input type="file" class="form-control" file-model="item.file_upload" id="file_upload" >
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group" ng-if="!add_flg && !search_flg">
                                        <div class="row">
                                            <label for="remarks" class="col-md-2 col-sm-2 col-10 text-right-lg">作成 </label>
                                            <div class="col-md-8 col-sm-8 col-24 disable">
                                              <p id="create_at"><% item.create_at %> <% item.create_user %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="!add_flg && !search_flg">
                                        <div class="row">
                                            <label for="remarks" class="col-md-2 col-sm-2 col-10 text-right-lg">編集 </label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                              <p id="update_at"><% item.update_at %> <% item.update_user %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="message message-info"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer"  ng-if="!add_flg">
                            @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_UPDATE])
                                <button type="button" class="btn btn-success" ng-click="movePurpose()"
                                        ng-if="!edit_flg && !search_flg">
                                    <i class="far fa-save"></i> 編集
                                </button>
                                <button type="button" class="btn btn-success" ng-click="savePurpose()"
                                        ng-if="edit_flg || relation_flg"> 更新
                                </button>
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
    </style>
@endpush
@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailFormExpenseController', function ($scope, $rootScope, $http,$filter) {
                $scope.item = {};
                $scope.edit_flg = false;
                $scope.add_flg = false;
                $scope.search_flg = false;
                $scope.relation_flg = false;
                $scope.pur = [];
                $scope.view_pur=[];
                $scope.wm = [];
                $scope.view_wm =[];
                $scope.f_pur=[];
                $scope.f_wtsm=[];
                
                $rootScope.$on("openUserDetailsExpMPurposeSearch", function(event,data){
                    $scope.search_flg=true;
                    $scope.edit_flg = false;
                    $scope.add_flg  = false;
                    $scope.relation_flg=false;
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openUserDetailsExpMPurpose", function(event,data){
                    $scope.edit_flg = false;
                    $scope.add_flg  = false;
                    $scope.search_flg=false;
                    $scope.relation_flg=false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.form_code = data.form_code;
                    if(!$scope.item.form_code){
                        $scope.edit_flg = true;
                    }
                    hideMessages();
                    $http.get(link_ajax + "/" + $scope.item.form_code )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.f_pur = event.data.form_purpose;
                                $scope.f_wtsm = event.data.form_wtsm;
                                $scope.period_from =$scope.item.validity_period_from;
                                $scope.period_to =$scope.item.validity_period_to;
                                $scope.item.validity_period_from =new Date($scope.item.validity_period_from);
                                $scope.item.validity_period_to =new Date($scope.item.validity_period_to);
                                $scope.purpose = event.data.purpose;
                                $scope.wtsm = event.data.wtsm;
                                $scope.pur=event.data.form_purpose;
                                $scope.wm=event.data.form_wtsm;
                            }
                        });
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openUserDetailsExpMPurposeFromDetail", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg  = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.form_code = data.form_code;
                    if(!$scope.item.form_code){
                        $scope.edit_flg = true;
                    }
                    hideMessages();
                    
                    $http.get(link_ajax + "/" + $scope.item.form_code )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.f_pur = event.data.form_purpose;
                                $scope.f_wtsm = event.data.form_wtsm;
                                $scope.item.validity_period_from =new Date($scope.item.validity_period_from);
                                $scope.item.validity_period_to =new Date($scope.item.validity_period_to);
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openUserDetailsExpMPurposeAdd", function(event,data){
                    $scope.edit_flg = true;
                    $scope.add_flg = true;
                    $scope.search_flg=false;
                    $scope.relation_flg=false;
                    hasChange = false;
                    $scope.item.form_code = "";
                    $scope.item.form_name = "";
                    $scope.item.remarks = "";
                    $scope.item.form_describe= "";
                    $scope.item.total_amt_min = "";
                    $scope.item.total_amt_max = "";
                    $scope.item.items_max = "";
                    $scope.item.validity_period_from = "";
                    $scope.item.validity_period_to = "";
                    $scope.item.create_user = "";
                    $scope.item.create_at = "";
                    $scope.item.update_user = "";
                    $scope.item.update_at = "";
                    $scope.item.file_upload = "";
                    $scope.item.form_code_adv="";
                    $scope.pur=[];
                    $scope.wm=[];
                    $("#modalDetailItem").modal();
                });

                $scope.movePurpose = function(){
                    $scope.edit_flg = true;
                    $scope.add_flg = false;
                    hasChange = true;
                    form_code  = $scope.item.form_code ;
                    $rootScope.$emit("openUserDetailsExpMPurposeFromDetail",{form_code:form_code });
                };

                $scope.savePurpose = function(){
                    let actionType = 'update';
                    if ( $scope.add_flg) {
                         actionType = 'create';
                    }
                    if($scope.relation_flg){
                        $scope.pur=$scope.pur.concat($scope.view_pur);
                        $scope.wm=$scope.wm.concat($scope.view_wm);
                    }

                    if($scope.item.form_code == "" || $scope.item.form_code == undefined){
                        $("#modalDetailItem .message").append(showMessages(["様式コードは必須です"], 'danger', 10000));
                        return;
                    }

                    if($scope.item.form_name == "" || $scope.item.form_name == undefined){
                        $("#modalDetailItem .message").append(showMessages(["様式名は必須です"], 'danger', 10000));
                        return;
                    }

                    if($scope.item.total_amt_min == "" || $scope.item.total_amt_max == ""){
                        $("#modalDetailItem .message-info").append(showMessages(["対象合計金額は1以上で入力してください"], 'danger', 10000));
                        return;
                    }
                  
                    if($scope.item.total_amt_min>$scope.item.total_amt_max){
                        $("#modalDetailItem .message-info").append(showMessages(["対象合計金額を正しく入力してください"], 'danger', 10000));
                        return;
                    }

                    if($scope.item.items_max == "" || $scope.item.items_max == undefined){
                        $("#modalDetailItem .message-info").append(showMessages(["最大明細行数を1以上で入力してください"], 'danger', 10000));
                        return;
                    }


                    if($scope.item.validity_period_from == "" || $scope.item.validity_period_to == ""){
                        $("#modalDetailItem .message-info").append(showMessages(["有効期限を入力してください"], 'danger', 10000));
                        return;
                    }

                    if($filter('date')($scope.item.validity_period_from,"yyyy-MM-dd")>$filter('date')($scope.item.validity_period_to,"yyyy-MM-dd")){
                        $("#modalDetailItem .message-info").append(showMessages(["有効期限を正しく設定してください"], 'danger', 10000));
                        return;
                    }

                    if($scope.pur.length == 0 || $scope.pur == undefined){
                        $("#modalDetailItem .message-info").append(showMessages(["目的を選択してください"], 'danger', 10000));
                        return;
                    }


                    if($scope.wm.length == 0 || $scope.wm == undefined){
                        $("#modalDetailItem .message-info").append(showMessages(["用途を選択してください"], 'danger', 10000));
                        return;
                    }

                    if(!($scope.item.total_amt_min>=0 && $scope.item.total_amt_max>0 && $scope.item.items_max>0)){
                        $("#modalDetailItem .message-info").append(showMessages(["数値は1以上で設定してください"], 'danger', 10000));
                        return;
                    }

                    if($scope.item.form_describe==null){
                        $scope.item.form_describe="";
                    }

                    if($scope.item.remarks==null){
                        $scope.item.remarks="";
                    }

                    let fileData = new FormData();
                    fileData.append('form_code',$scope.item.form_code);
                    fileData.append('form_name',$scope.item.form_name);
                    fileData.append('form_describe',$scope.item.form_describe);
                    fileData.append('items_max',$scope.item.items_max);
                    fileData.append('remarks',$scope.item.remarks);
                    fileData.append('validity_period_from',$filter('date')($scope.item.validity_period_from,"yyyy-MM-dd"));
                    fileData.append('validity_period_to',$filter('date')($scope.item.validity_period_to,"yyyy-MM-dd"));
                    fileData.append('total_amt_min',$scope.item.total_amt_min);
                    fileData.append('total_amt_max',$scope.item.total_amt_max);
                    fileData.append('purpose',$scope.pur);
                    fileData.append('wtsm',$scope.wm);
                    fileData.append('form_code_adv',$scope.item.form_code_adv);
                    console.log("説明確認");
                    console.log($scope.item.form_describe);
                    if(actionType=='update'){//更新
                        $rootScope.$emit("showLoading");
                        $http.post(link_ajax + "/" + "update",
                            fileData,{transformRequest: null,headers: {'Content-type':undefined}}
                            ).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));   
                            }
                        });
                        
                    }else{//登録

                        if($scope.item.file_upload == "" || $scope.item.file_upload == undefined){
                            $("#modalDetailItem .message-info").append(showMessages(["ファイルが選択されていません-"], 'danger', 10000));
                            return;
                        }
                        $rootScope.$emit("showLoading");
                        
                        fileData.append('uploadFile',$scope.item.file_upload);
                        fileData.append('form_code_adv',$scope.item.form_code_adv);
                        $http.post(link_ajax,
                        fileData,{transformRequest: null,headers: {'Content-type':undefined}}).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                
                            } else {
                                if(!$scope.item.form_code ){
                                    $scope.item.form_code  = event.data.form_code ;
                                }
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                location.reload();
                            }
                           
                            
                        });
                    }
                    hasChange = true;

                };
                $scope.SearchPurpose = function(){
                    
                    hasChange = false;
                    $http.post(link_ajax + "/" + "check" ,{form_code_adv:$scope.item.form_code_adv})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.view_pur = event.data.form_purpose;
                                $scope.view_wm = event.data.form_wtsm;
                                //$scope.f_pur = event.data.form_purpose;
                                //$scope.f_wm = event.data.form_wtsm;
                                //$scope.pur=event.data.form_purpose;
                                //$scope.wm=event.data.form_wtsm;
                                $scope.purpose = event.data.purpose;
                                $scope.wtsm = event.data.wtsm;
                                $scope.item.validity_period_from =new Date($scope.item.validity_period_from);
                                $scope.item.validity_period_to =new Date($scope.item.validity_period_to);
                                $scope.item.form_code_adv=$scope.item.form_code;
                                $scope.item.form_code="";
                                $scope.edit_flg = false;
                                $scope.add_flg = true;
                                $scope.search_flg = false;
                                $scope.relation_flg = true;
                                $("#modalDetailItem").modal();
                                                        
                            }
                        });
                    
                };

                $scope.PurposeCheck = function (id) {
                   
                        var idx = $scope.pur.indexOf(id);
                        if (idx > -1) {
                            $scope.pur.splice(idx, 1);
                        } else {
                            $scope.pur.push(id);
                        }
                };

                $scope.WtsmCheck = function (id) {
                   var idx = $scope.wm.indexOf(id);
                   if (idx > -1) {
                       $scope.wm.splice(idx, 1);
                   } else {
                       $scope.wm.push(id);
                   }
                };
        
            });
            appPacAdmin.directive('fileModel',function($parse){
                return{
                    restrict: 'A',
                    link: function(scope,element,attrs){
                        var model = $parse(attrs.fileModel);
                        element.bind('change',function(){
                            scope.$apply(function(){
                                model.assign(scope,element[0].files[0]);
                            });
                        });
                    }
                };
            });
        }
    </script>
@endpush