<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        @csrf
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">共通印割当</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>

                    <div class="card">
                        <div class="card-header">割当済み共通印</div>
                        <div class="card-body">
                            <div class="form-group" ng-if="item.stamps.stampCompany.length">割当済み共通印検索結果:<% item.stamps.stampCompany.length %>個</div>
                            <div class="stamp-list" ng-if="item.stamps.stampCompany.length">
                                <div class="stamp-item stamp-item-<% stamp.assign_id %>" ng-repeat="(key, stamp) in item.stamps.stampCompany">
                                    <div class="thumb">
                                        <span class="thumb-img">
                                            <img ng-src="data:image/png;base64,<% stamp.stamp_company.stamp_image %>" class="stamp-image" />
                                        </span>
                                        @canany([PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE])
                                            {{-- タイムスタンプ編集ボタン表示：印面グループ操作権限あり + 企業ON ＋ 利用者OFF --}}
                                            <span class="btn btn-edit btn-circle" ng-click="editStamp(stamp.assign_id)" ng-if="stamp.operation && company_stamp_flg && item.info.time_stamp_permission==0">
                                            <i class="far fa-edit"></i>
                                        </span>
                                        <span class="btn btn-warning btn-circle" ng-click="removeStamp(stamp.assign_id)" ng-if="stamp.operation">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                        @endcanany
                                    </div>
                                    <div class="mt-3" style="width: 80px;"><% stamp.stamp_company.stamp_name ? stamp.stamp_company.stamp_name : '名称未設定'%></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="message message-stamp mt-2"></div>

                    <div class="card mt-3" ng-show="item.id">
                        <div class="card-header">共通印選択</div>
                        <div class="card-body">
                            @if(\App\Http\Utils\AppUtils::getStampGroup())
                                <div class="row form-group" ng-if="listGroup.length != 0">
                                    <label for="admin_group_name" class="control-label col-md-3 text-right-lg">グループ</label>
                                    <div class="input-group col-md-6">
                                        <select class="form-control" ng-model="group_id" ng-change="getValue(group_id)">
                                            <option value="99">すべて</option>
                                            <option value="0">グループなし</option>
                                            <option ng-repeat="group in listGroup" ng-value="group.id">
                                                <% group.group_name %>
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                    </div>
                                </div>
                            @endif
                                <div class="row form-group">
                                    <label for="" class="control-label col-md-3 text-right-lg">名称</label>
                                    <div class="input-group col-md-6">
                                        <input type="text" class="form-control" placeholder="名称(部分一致)" ng-keyup="txtNameKeyUp($event)"
                                            ng-model="search_name" ng-disabled="onlyUnsigned">
                                    </div>
                                    <div class="input-group-append col-md-3" ng-click="searchStamp(1, stamp_pagination.limit)">
                                        <span class="input-group-text btn btn-primary"><i class="fas fa-search mr-1"></i> 検索</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="onlyUnsigned" class="control-label"> <input type="checkbox" ng-model="onlyUnsigned" id="onlyUnsigned"  /> 名称未設定の共通印のみを抽出</label>
                                </div>
                                <div class="form-group">
                                    <span ng-if="!showSearch">※印面を検索してください※</span>
                                    <span ng-if="showSearch && arrStamp.length == 0">※該当する印面がありません※</span>
                                    <span ng-if="showSearch && arrStamp.length != 0">共通印検索結果:<% stamp_pagination.total %>個</span>
                                </div>
                            <div class="row my-3" ng-show="arrStamp.length != 0">
                                <div class="col-sm-6 col-12">
                                    表示件数:
                                    <select ng-model="stamp_pagination.limit"
                                            ng-change="searchStamp(1,stamp_pagination.limit)"
                                            ng-options="option for option in option_limit track by option">
                                    </select>
                                </div>
                            </div>
                                <div class="mt-2 mb-2">
                                    <div class="stamp-list" ng-if="arrStamp.length">
                                        <div class="stamp-item stamp-item-<% stamp.id %>" ng-repeat="(key, stamp) in arrStamp">
                                            <div class="thumb">
                                                <span class="thumb-img">
                                                    <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                                                </span>
                                                @canany([PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE])
                                                <span class="btn btn-warning btn-circle" ng-click="registerUserStamp(key, stamp)">
                                                    <i class="fas fa-plus"></i>
                                                </span>
                                                @endcanany
                                            </div>
                                            <div class="mt-3" style="width: 80px;"><% stamp.stamp_name ? stamp.stamp_name : '名称未設定' %></div>
                                        </div>
                                    </div>
                                </div>
                                <div ng-show="arrStamp.length != 0">
                                    <div class="mt-3"><% stamp_pagination.total %> 件中 <% stamp_pagination.from || '0' %> 件から <%stamp_pagination.to || '0'%> 件までを表示</div>
                                    <div class="pagination-center" ng-hide="stamp_pagination.total <= stamp_pagination.limit">
                                        <div class="pagination">
                                            <button ng-disabled="stamp_pagination.currentPage == 1" ng-click="searchStamp(stamp_pagination.currentPage-1, stamp_pagination.limit)">
                                                <i class="fas fa-backward"></i>
                                            </button>
                                            <%stamp_pagination.currentPage%>/<% stamp_pagination.lastPage %>
                                            <button ng-disabled="stamp_pagination.currentPage == stamp_pagination.lastPage" ng-click="searchStamp(stamp_pagination.currentPage+1, stamp_pagination.limit)">
                                                <i class="fas fa-forward"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>


                        </div>
                    </div>
                    <div class="card mt-3" ng-show="item.id && convenient_flg == 1 ">
                        <div class="card-header">便利印選択</div>
                        <div class="card-body">
                            <div class="row form-group">
                                <label for="" class="control-label col-md-3 text-right-lg">ジャンル</label>
                                <div class="input-group col-md-6">
                                    <select class="form-control" ng-model="search_stamp_division">
                                        <option></option>

                                        <option ng-repeat="division in divisionList" ng-value="division.id">
                                            <% division.division_name %>
                                        </option>
                                    </select>
                                </div>
                                <div class="input-group-append col-md-3" ng-click="searchConvenientStamp(1, convenient_stamp_pagination.limit)">
                                    <span class="input-group-text btn btn-primary"><i class="fas fa-search mr-1"></i> 検索</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <span ng-if="!showConvenientSearch">※印面を検索してください※</span>
                                <span ng-if="showConvenientSearch && arrConvenientStamp.length == 0">※該当する印面がありません※</span>
                                <span ng-if="showConvenientSearch && arrConvenientStamp.length != 0">便利印検索結果:<% convenient_stamp_pagination.total %>個</span>
                            </div>
                            <div class="row my-3" ng-show="arrConvenientStamp.length != 0">
                                <div class="col-sm-6 col-12">
                                    表示件数:
                                    <select ng-model="convenient_stamp_pagination.limit"
                                            ng-change="searchConvenientStamp(1,convenient_stamp_pagination.limit)"
                                            ng-options="option for option in option_limit track by option">
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2 mb-2">
                                <div class="stamp-list" ng-if="arrConvenientStamp.length">
                                    <div class="stamp-item stamp-item-<% stamp.id %>" ng-repeat="(key, stamp) in arrConvenientStamp">
                                        <div class="thumb">
                                                <span class="thumb-img">
                                                    <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                                                </span>
                                            @canany([PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE])
                                            <span class="btn btn-warning btn-circle" ng-click="checkConvenientStamp(key, stamp)">
                                                    <i class="fas fa-plus"></i>
                                                </span>
                                            @endcanany
                                        </div>
                                        <div class="mt-3" style="width: 80px;"><% stamp.stamp_name %></div>
                                    </div>
                                </div>
                            </div>
                            <div ng-show="arrConvenientStamp.length != 0">
                                <div class="mt-3"><% convenient_stamp_pagination.total %> 件中 <% convenient_stamp_pagination.from || '0' %> 件から <%convenient_stamp_pagination.to || '0'%> 件までを表示</div>
                                <div class="pagination-center" ng-hide="convenient_stamp_pagination.total <= convenient_stamp_pagination.limit">
                                    <div class="pagination">
                                        <button ng-disabled="convenient_stamp_pagination.currentPage == 1" ng-click="searchConvenientStamp(convenient_stamp_pagination.currentPage-1, convenient_stamp_pagination.limit)">
                                            <i class="fas fa-backward"></i>
                                        </button>
                                        <%convenient_stamp_pagination.currentPage%>/<% convenient_stamp_pagination.lastPage %>
                                        <button ng-disabled="convenient_stamp_pagination.currentPage == convenient_stamp_pagination.lastPage" ng-click="searchConvenientStamp(convenient_stamp_pagination.currentPage+1, convenient_stamp_pagination.limit)">
                                            <i class="fas fa-forward"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                    <div class="message message-info mt-3"></div>
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
    <form class="form_edit_admin" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-admin" id="modalDetailItemStamp" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">タイムスタンプ設定</h4>
                        <button type="button" class="close" ng-click="closeSettingStamp()">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="form-group row">
                            <label for="time_stamp_permission_1" class="control-label col-md-6">タイムスタンプの発行</label>
                            <div class="col-md-6">
                                <label class="label mr-2" for="time_stamp_permission_1">
                                    <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_1" ng-value="1"> 有効
                                </label>
                                <label class="label mr-2" for="time_stamp_permission_0">
                                    <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_0" ng-value="0"> 無効
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE])
                        <button type="submit" class="btn btn-success" ng-model="stamp_id" ng-click="saveTimeStampPermission()">
                            <ng-template><i class="far fa-save"></i> 更新</ng-template>
                        </button>
                        @endcanany
                        <button type="button" class="btn btn-default" ng-click="closeSettingStamp()">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </form>
    <div class="modal modal-add-stamp mt-5  message-info " id="modalAlertConvenient" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-<% convenient_alert.size %>">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <div class="modal-title" ng-if="convenient_alert.title" ng-bind-html="convenient_alert.title"></div>
                </div>
                <!-- Modal body -->
                <div class="modal-body text-left" ng-if="convenient_alert.message" ng-bind-html="convenient_alert.message"></div>
                <div class="modal-footer">
                    <div class="btn btn-default" ng-click="convenientAlertClose()">
                        <i class="fas fa-times-circle"></i> <% convenient_alert.btnClose %>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.id = 0;
                $scope.search_name = "";
                $scope.showSearch = false;
                $scope.keyEdit = false;
                $scope.onlyUnsigned = false;
                $scope.arrStamp = [];
                $scope.arrStampAssigned = [];
                $scope.option_limit = [10, 50, 100];
                $scope.stamp_pagination = {};
                $scope.listGroup = [];
                $scope.group_id = "99";//すべて
                $scope.admin_id = 0;
                $scope.contract_edition = null;
                $scope.company_stamp_flg = null;
                $scope.search_convenient_name = "";
                $scope.search_stamp_division = "";
                $scope.showConvenientSearch = false;
                $scope.arrConvenientStamp = [];
                $scope.arrConvenientStampAssigned = [];
                $scope.convenient_stamp_pagination = {};

                $scope.$on("showModalAlertConvenient", function(event, data){
                    data = data || {};
                    $scope.convenient_alert = {
                        title: data.title || "",
                        message: data.message || "",
                        btnClose: data.btnClose || "閉じる",
                        callBack: data.callBack || null,
                        databack: data.databack || null,
                        size: data.size || "sm"
                    };

                    $("#modalAlertConvenient").modal();
                });

                $scope.convenientAlertClose = function(){
                    if($scope.convenient_alert.callBack) $scope.callBack($scope.convenient_alert.databack);
                    hasChange = false;
                    $("#modalAlertConvenient").modal("hide");
                    hasChange = true;
                }

                $rootScope.$on("openAssignStamp", function(event, data){
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    $scope.search_name = "";
                    $scope.showSearch = false;
                    $scope.keyEdit = false;
                    $scope.arrStamp = [];
                    $scope.group_id = "99";//すべて
                    $scope.admin_id = 0;
                    $scope.search_convenient_name = "";
                    $scope.search_stamp_division = "";
                    $scope.showConvenientSearch = false;
                    $scope.arrConvenientStamp = [];

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
                            $scope.listGroup = event.data.listGroup;
                            $scope.admin_id = event.data.admin_id;
                            $scope.contract_edition = $scope.item.contract_edition;
                            $scope.company_stamp_flg = $scope.item.company_stamp_flg;
                            $scope.convenient_flg = event.data.company.convenient_flg;
                            $scope.divisionList = event.data.divisionList;
                            $scope.StampGroupOperation();
                            $scope.processStampAssigned();
                            $scope.refactorAssignedStamps();

                            if($scope.item.state_flg == 1 && $scope.item.stamp_is_over == 1){
                                $rootScope.$emit("showMocalAlert",
                                {
                                    size:'md',
                                    title:"警告",
                                    message:$scope.item.stamp_is_over_message,
                                });
                            }
                            if($scope.item.state_flg == 1 && $scope.item.convenient_stamp_is_over == 1){
                                $scope.$emit("showModalAlertConvenient",
                                    {
                                        size:'md',
                                        title:"警告",
                                        message:$scope.item.convenient_stamp_is_over_message,
                                    });
                            }
                        }
                    });
                    $("#modalDetailItem").modal();
                });

                $scope.refactorAssignedStamps=function (){
                    const convenientStamp =  $scope.item.stamps.convenientStamp;
                    if(Array.isArray(convenientStamp)){
                        convenientStamp.forEach(function(item){
                            $scope.item.stamps.stampCompany.push({
                                'assign_id':item.assign_id,
                                'stamp_admin':[],
                                'stamp_flg':item.stamp_flg,
                                'stamp_id':item.stamp_id,
                                'operation':true,
                                'stamp_company':{
                                    'stamp_image':item.stamp_image,
                                    'stamp_name':item.stamp_name
                                }
                            })
                        })
                    }
                }

                $scope.txtNameKeyUp = function(event){
                    if(event.keyCode == 13){
                        $scope.searchStamp(1, $scope.stamp_pagination.limit);
                    }
                };
                $scope.searchConvenientStamp = function(page, limit){
                    $scope.showConvenientSearch = true;
                    limit = limit || 10;
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_search_convenient_stamp,{stamp_division:$scope.search_stamp_division,name: $scope.search_convenient_name,is_assigned: $scope.arrConvenientStampAssigned,page: page, limit: limit})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $scope.arrConvenientStamp = [];
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                let paginate = event.data.items;
                                $scope.convenient_stamp_pagination = {currentPage: paginate.current_page,from: paginate.from, to: paginate.to, total: paginate.total, lastPage: paginate.last_page, limit: paginate.per_page};
                                $scope.arrConvenientStamp = paginate.data;
                            }
                        });
                };
                $scope.searchStamp = function(page, limit){
                    $scope.showSearch = true;
                    limit = limit || 10;
                    $rootScope.$emit("showLoading");
                    $http.post(link_search_CompanyStamp,{name: $scope.onlyUnsigned?'名称未設定':$scope.search_name, empty_name:$scope.onlyUnsigned, id_assigned: $scope.arrStampAssigned, page: page, limit: limit, group_id:$scope.group_id})
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        $scope.arrStamp = [];
                        if(event.data.status == false){
                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            let paginate = event.data.items;
                            $scope.stamp_pagination = {currentPage: paginate.current_page,from: paginate.from, to: paginate.to, total: paginate.total, lastPage: paginate.last_page, limit: paginate.per_page};
                            $scope.arrStamp = paginate.data;
                            $scope.StampGroupOperation();
                        }
                    });
                };
                $scope.getValue = function(key){
                    $scope.group_id = key;
                }


                let tempTimeStampPermission = 0;
                $scope.registerUserStamp = function(key, stamp){
                    $rootScope.time_stamp_permission = 1;
                    if($scope.item.info.time_stamp_permission == 0 && $scope.item.company_stamp_flg == 1 && $scope.item.company_time_stamp_permission==0 ){ //PAC_5-2623 Start---End
                        $scope.time_stamp_permission = 0;
                        $("#modalDetailItemStamp").modal();
                    }else{

                        $rootScope.$emit("showLoading");
                        $http.post(link_store_stamp, { stamps: [stamp.id], mst_user_id: $scope.item.id, stamp_flg: 1,time_stamp_permission:$scope.item.info.time_stamp_permission == 0 ? tempTimeStampPermission : $rootScope.time_stamp_permission })
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");

                                if(event.data.status == false){
                                    if(event.data.is_over == 1){
                                        $rootScope.$emit("showMocalAlert",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:event.data.message[0],
                                        });
                                    }else{
                                        $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                                    }
                                }else{
                                    $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'success', 10000));
                                    $scope.arrStamp.splice(key,1);
                                    hasChange = true;

                                    $scope.item.stamps = event.data.stamps;
                                    $scope.StampGroupOperation();
                                    $scope.processStampAssigned();
                                    $scope.refactorAssignedStamps();
                                    if($scope.arrStamp.length <= 1) {
                                        $scope.searchStamp(1, $scope.stamp_pagination.limit);
                                    } else {
                                        $scope.searchStamp($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                                    }
                                }
                            });
                    }
                    $scope.saveTimeStampPermission = function(){
                        $scope.arrStamp.splice(key,1);
                        $rootScope.$emit("showLoading");
                        $http.post(link_store_stamp, { stamps: [stamp.id], mst_user_id: $scope.item.id, stamp_flg: 1,time_stamp_permission: $scope.time_stamp_permission })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'success', 10000));

                                hasChange = true;

                                tempTimeStampPermission = $scope.time_stamp_permission;
                                $scope.item.stamps = event.data.stamps;
                                $scope.StampGroupOperation();
                                $scope.processStampAssigned();
                                if($scope.arrStamp.length <= 1) {
                                    $scope.searchStamp(1, $scope.stamp_pagination.limit);
                                } else {
                                    $scope.searchStamp($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                                }
                                $("#modalDetailItemStamp").modal('hide');
                            }
                        });
                    };
                };

                $scope.checkConvenientStamp = function (key, stamp) {
                    $rootScope.$emit("showLoading");
                    $http.post(link_check_store_convenient_stamp, { stamps: [stamp.id], mst_user_id: $scope.item.id, stamp_flg: 3})
                    .then(function(checkEvent) {
                        $rootScope.$emit("hideLoading");
                        if (checkEvent.data.status == false) {
                            if (checkEvent.data.convenient_stamp_is_over !== undefined && checkEvent.data.convenient_stamp_is_over == 1) {
                                $scope.$emit("showModalAlertConvenient",
                                    {
                                        size: 'md',
                                        title: "警告",
                                        message: checkEvent.data.message[0],
                                    });
                            } else {
                                $("#modalDetailItem .message-stamp").append(showMessages(checkEvent.data.message, 'danger', 10000));
                            }
                        } else {
                            $scope.registerUserConvenientStamp(key, stamp);
                        }
                    });
                }

                $scope.registerUserConvenientStamp = function(key, stamp){
                    $rootScope.time_stamp_permission = 0;

                    if($scope.item.info.time_stamp_permission == 0 && $scope.item.company_stamp_flg == 1 && $scope.item.company_time_stamp_permission==0){
                        $("#modalDetailItemStamp").modal();
                    }else{
                        $rootScope.$emit("showLoading");
                        $http.post(link_store_stamp, { stamps: [stamp.id], mst_user_id: $scope.item.id, stamp_flg: 3,time_stamp_permission: $scope.time_stamp_permission })
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    if(event.data.convenient_stamp_is_over !== undefined && event.data.convenient_stamp_is_over == 1){
                                        $scope.$emit("showModalAlertConvenient",
                                            {
                                                size:'md',
                                                title:"警告",
                                                message:event.data.message[0],
                                            });
                                    } else {
                                        $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                                        hasChange = true;
                                    }
                                }else{
                                    $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'success', 10000));

                                    $scope.arrConvenientStamp.splice(key,1);
                                    hasChange = false;

                                    $scope.item.stamps = event.data.stamps;
                                    $scope.processStampAssigned();
                                    $scope.StampGroupOperation();
                                    if($scope.arrConvenientStamp.length <= 1) {
                                        $scope.searchConvenientStamp(1, $scope.convenient_stamp_pagination.limit);
                                    } else {
                                        $scope.searchConvenientStamp($scope.convenient_stamp_pagination.currentPage, $scope.convenient_stamp_pagination.limit);
                                    }
                                    $scope.refactorAssignedStamps();
                                    hasChange = true;
                                }
                            });
                    }
                    $scope.saveTimeStampPermission = function(){

                        $rootScope.$emit("showLoading");
                        $http.post(link_store_stamp, { stamps: [stamp.id], mst_user_id: $scope.item.id, stamp_flg: 3,time_stamp_permission: $scope.time_stamp_permission })
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    if(event.data.convenient_stamp_is_over !== undefined && event.data.convenient_stamp_is_over == 1){
                                        $scope.$emit("showModalAlertConvenient",
                                            {
                                                size:'md',
                                                title:"警告",
                                                message:event.data.message[0],
                                            });
                                    } else {
                                        $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                                        hasChange = true;
                                    }
                                }else{
                                    $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'success', 10000));

                                    $scope.arrConvenientStamp.splice(key, 1);
                                    hasChange = false;

                                    $scope.item.stamps = event.data.stamps;
                                    $scope.processStampAssigned();

                                    if($scope.arrConvenientStamp.length <= 1) {
                                        $scope.searchConvenientStamp(1, $scope.convenient_stamp_pagination.limit);
                                    } else {
                                        $scope.searchConvenientStamp($scope.convenient_stamp_pagination.currentPage, $scope.convenient_stamp_pagination.limit);
                                    }
                                    $scope.refactorAssignedStamps();
                                    $("#modalDetailItemStamp").modal('hide')
                                    hasChange = true;
                                }
                            });
                    };
                };

                $scope.editStamp = function(assign_id){
                    $rootScope.id = assign_id;
                    $rootScope.time_stamp_permission = 0;
                    hideMessages();
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $http.get(link_show_time_stamp_permission +"/"+ assign_id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItemStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.time_stamp_permission = event.data.stamp_asign.time_stamp_permission;
                            $rootScope.stamp_id = event.data.stamp_asign.id;
                        }

                    });
                    $("#modalDetailItemStamp").modal()
                    $scope.saveTimeStampPermission = function(){
                        $rootScope.$emit("showLoading");
                        $http.post(link_update_time_stamp_permission + "/"+$scope.stamp_id, {time_stamp_permission: $scope.time_stamp_permission})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");

                            hasChange = true;

                            if(event.data.status == false){
                                $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'success', 10000));
                            }
                            $("#modalDetailItemStamp").modal('hide')
                        });
                    };
                };

                $scope.removeStamp = function(assign_id){

                    var updateStatus = false;
                    var updateStatusMessage = '';

                    if($scope.item.state_flg == 1){
                        // 有効時、無効に更新必要かを判定
                        if($scope.contract_edition == 1 || $scope.contract_edition == 2){
                            // Standard(0)+trial(3)以外の場合、氏名印/日付印/共通印/部署印のいずれあれば登録できる
                            if($scope.item.stamps && ($scope.item.stamps.stampMaster.length + $scope.item.stamps.stampDepartment.length + $scope.item.stamps.stampCompany.length) == 1){
                                updateStatus = true;
                            }
                        }
                    }

                    if(updateStatus){
                        updateStatusMessage = '<div class="text-left">※全て削除した場合は利用者を無効にする</div>';
                    }

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'削除確認',
                            message:'<div class="text-left">削除しますか？</div>' + updateStatusMessage + $(".stamp-item-"+assign_id).find('.thumb-img').html(),
                            btnDanger:'削除',
                            databack: assign_id,
                            callDanger: function(assign_id){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_remove_stamp +"/"+ assign_id, { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $scope.item.stamps = event.data.stamps;
                                            $scope.StampGroupOperation();
                                            $scope.processStampAssigned();
                                            $scope.refactorAssignedStamps();

                                            $scope.searchStamp($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                                            if($scope.convenient_flg == 1){
                                                $scope.searchConvenientStamp($scope.convenient_stamp_pagination.currentPage, $scope.convenient_stamp_pagination.limit);
                                            }

                                            $scope.StampGroupOperation();
                                            $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'warning', 10000));
                                            hasChange = true;
                                        }
                                    });
                            }
                        });
                }

                $scope.processStampAssigned = function(){
                    $scope.arrStampAssigned = [];
                    $scope.arrConvenientStampAssigned = [];
                    for(let i = 0; i< $scope.item.stamps.stampCompany.length; i++){
                        let stamp = $scope.item.stamps.stampCompany[i];
                        if(stamp.stamp_flg == 1){
                            $scope.arrStampAssigned.push(stamp.stamp_id);
                        }
                    }
                    if(Array.isArray($scope.item.stamps.convenientStamp)){
                        $scope.item.stamps.convenientStamp.forEach(function(item){
                            $scope.arrConvenientStampAssigned.push(item.stamp_id);
                        })
                    }
                }

                $scope.StampGroupOperation = function(){
                    // assign
                    for(let i in $scope.item.stamps.stampCompany){
                        let stamp = $scope.item.stamps.stampCompany[i];
                        if (stamp.stamp_group != null && stamp.stamp_group.state!= 0){
                            // group あり
                            if (stamp.stamp_admin.length>0){
                                $scope.item.stamps.stampCompany[i].operation = false;

                                for(let j in stamp.stamp_admin){
                                    let relation = stamp.stamp_admin[j];

                                    if(relation['mst_admin_id']==$scope.admin_id){
                                        $scope.item.stamps.stampCompany[i].operation = true;
                                    }
                                }
                            }else{
                                $scope.item.stamps.stampCompany[i].operation = false;
                            }
                        }else{
                            // group なし
                            $scope.item.stamps.stampCompany[i].operation = true;
                        }
                    }

                    // search
                    // for(let i in $scope.arrStamp){
                    //     let stamp = $scope.arrStamp[i];
                    //     if (stamp.group_id != null){
                    //         // group あり
                    //         if (stamp.stamp_admin.length>0){
                    //             $scope.arrStamp[i].operation = false;
                    //
                    //             for(let j in stamp.stamp_admin){
                    //                 let relation = stamp.stamp_admin[j];
                    //
                    //                 if(relation['mst_admin_id']==$scope.admin_id){
                    //                     $scope.arrStamp[i].operation = true;
                    //
                    //                 }
                    //             }
                    //         }else{
                    //             $scope.arrStamp[i].operation = false;
                    //
                    //         }
                    //     }else{
                    //         // group なし
                    //         $scope.arrStamp[i].operation = true;
                    //     }
                    //
                    // }
                    
                    $scope.closeSettingStamp = function (){
                        $("#modalDetailItemStamp").modal('hide')
                    }
                }

            })
        }
    </script>
@endpush
