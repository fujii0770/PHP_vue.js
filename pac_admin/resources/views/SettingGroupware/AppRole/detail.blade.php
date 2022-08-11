<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="item2.isNew">ロール情報登録</h4>
                        <h4 class="modal-title" ng-if="!item2.isNew">ロール情報更新</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="item2.isNew">ロール情報登録</div>
                            <div class="card-header" ng-if="!item2.isNew">ロール情報更新</div>
                                <div class="card-body">
                                    <div class="form-group">

                                        <div class="form-group">
                                            <div class="row">
                                                <label for="role_name" class="col-md-3 col-sm-3 col-12 text-right-lg">ロール名<span class="text-danger">*</span></label>
                                                <div class="col-md-8 col-sm-4 col-24">
                                                    <input type="text" class="form-control" ng-model="item2.name" id="role_name" ng-readonly="item2.isDefault"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="user_name" class="col-md-3 col-sm-3 col-12 text-right-lg">アクセス権限<span class="text-danger">*</span></label>
                                                <div class="col-md-8 col-sm-4 col-24">

                                                    <!-- 行を繰り返し -->
                                                    <div class="mb-1" ng-repeat="rowitem in item2.mstAppFunctionList">
                                                        <div class="mb-1">
                                                            <label class="mb-0"><% rowitem.functionName %></label>
                                                        </div>
                                                        <!-- 列を繰り返し -->
                                                        <div   class="form-check-inline" ng-repeat="colitem in rowitem.mstAccessPrivilegesList">

                                                            <label ng-if="rowitem.functionCode!=6"><input class="mr-1" type="checkbox" ng-checked="colitem.isAuth" ng-model="colitem.isAuth" ng-change="checkRequired()" ng-click="click_check(rowitem.functionName, colitem.privilegeContent)" ng-disabled="item2.isDefault || !isEditablePrivilege(rowitem.functionName, colitem.privilegeContent)"><% colitem.privilegeContent %></label>
                                                            <label ng-if="rowitem.functionCode==6">
                                                                <input ng-if="colitem.privilegeContent=='可能'" name="abc" class="mr-1"   type="radio" ng-checked="rowitem.mstAccessPrivilegesList[0].isAuth" ng-value="true" ng-model="rowitem.mstAccessPrivilegesList[0].isAuth" ng-change="checkRequired()" ng-click="click_check(rowitem.functionName, colitem.privilegeContent)" ng-disabled="item2.isDefault || !isEditablePrivilege(rowitem.functionName, colitem.privilegeContent)">
                                                                <input ng-if="colitem.privilegeContent=='不可'" name="abc" class="mr-1"   type="radio" ng-checked="rowitem.mstAccessPrivilegesList[0].isAuth"  ng-value="false" ng-model="rowitem.mstAccessPrivilegesList[0].isAuth" ng-change="checkRequired()" ng-click="click_check(rowitem.functionName, colitem.privilegeContent)" ng-disabled="item2.isDefault || !isEditablePrivilege(rowitem.functionName, colitem.privilegeContent)">
                                                                <% colitem.privilegeContent %>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div ng-repeat="rowitem in item2.mstAppFunctionList">
                                                        <div>
                                                            <!-- <% rowitem %> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="memo" class="col-md-3 col-sm-3 col-12 text-right-lg">メモ</label>
                                                <div class="col-md-8 col-sm-4 col-24">
                                                    <textarea type="text" class="form-control"  id="memo" rows="6"
                                                        placeholder="" ng-model="item2.memo" ng-readonly="item2.isDefault"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Modal footer -->
                            <div class="modal-footer">
                                <div class="d-flex justify-content-end btn-save pb-0">
                                    <button type="button" class="btn btn-success" ng-click="saveRole()" ng-if="item2.isNew" ng-disabled="!item2.name || !isAuthChecked">
                                        <i class="far fa-save"></i> 登録
                                    </button>
                                    @canany([PermissionUtils::PERMISSION_APP_ROLE_SETTING_UPDATE])
                                    <button type="button" class="btn btn-success" ng-click="saveRole()" ng-if="!item2.isNew && !item2.isDefault" ng-disabled="!item2.name || !isAuthChecked">
                                        <i class="far fa-save"></i> 更新
                                    </button>
                                    @endcanany
                                </div>
                                @canany([PermissionUtils::PERMISSION_APP_ROLE_SETTING_DELETE])
                                <button type="button" class="btn btn-danger mb-0" ng-disabled="selected.length==0" ng-click="delete()" ng-if="!item2.isNew && !item2.isDefault"><i class="fas fa-trash-alt"></i> 削除</button>
                                @endcanany
                                <button type="button" class="btn btn-default" data-dismiss="modal" ng-click="cancelAttendance()">
                                    <i class="fas fa-times-circle"></i> 閉じる
                                </button>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </form>


</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http, $log) {
                $scope.item = {};
                $scope.item2 = {};
                $checks = [];

                $rootScope.$on("openDetail", function(event,data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    //$scope.item.hr_info_id = data.hr_info_id;  //saveHrUserで使うので退避
                    $scope.item.id = data.id;
                    $scope.item2.isNew = data.isNew;
                    $scope.item2.app_id = data.appId;
                    hideMessages();

                    $scope.isDeletable = true
                    if (!data.isNew) {
                        const appUsers = {!! json_encode($appUsers) !!};
                        for(let i in appUsers){
                            if(data.id == appUsers[i].appRoleId){
                                $scope.isDeletable = false
                                break
                            }
                        }
                    }

                    $http.get(link_ajax + "/" + data.id+ "/" + data.appId )
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            if(data.isNew){
                                event.data.item2.name = ''
                                if(event.data.item2.mstAppFunctionList){

                                    var getDefaultAuth = function(functionName, privilegeName) {
                                        // switch (functionName) {
                                        //     case '他人のスケジュール操作':
                                        //         return ['表示'].includes(privilegeName);
                                        //     case '設備の予約操作':
                                        //         return ['表示', '追加'].includes(privilegeName);
                                        // }
                                        return false;
                                    };

                                    event.data.item2.mstAppFunctionList.forEach(f => {
                                        f.mstAccessPrivilegesList.forEach(p => {
                                            p.isAuth = getDefaultAuth(f.functionName, p.privilegeContent);
                                        })
                                    })
                                }
                                event.data.item2.memo = ''
                                event.data.item2.isDefault = false
                            }
                            $scope.item2 = event.data.item2;
                            $scope.item2.isNew = data.isNew;  // 上書きされるので再設定

                            $scope.checkRequired();
                        }
                    });
                    $("#modalDetailItem").modal();
                });

                $scope.isEditablePrivilege = function(functionName, privilegeName) {
                    switch (functionName) {
                        case '自分のスケジュール操作':
                            switch (privilegeName) {
                                case '表示':
                                    return true;
                                case '追加': //「表示」のチェック状態により活性非活性を切り替える
                                    return $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[0].isAuth;
                                case '編集':
                                case '削除': //「追加」のチェック状態により活性非活性を切り替える
                                    return $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[1].isAuth;
                                default:
                                    return false;
                            }
                        case '他人のスケジュール操作':
                            switch (privilegeName) {
                                case '表示':
                                   return true;
                                case '追加':
                                    return $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[0].isAuth;
                                case '編集':
                                case '削除':
                                    return $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[1].isAuth;
                                default:
                                    return false;
                            }
                    }
                    return true;
                };

                $scope.isAuthChecked = false;
                $scope.checkRequired = function(){
                    $scope.isAuthChecked = false;
                    for(var i = 0; i < $scope.item2.mstAppFunctionList.length; i++) { // 配列の長さ分の繰り返し
                        for(var i2 = 0; i2 < $scope.item2.mstAppFunctionList[i].mstAccessPrivilegesList.length; i2++) {
                            if($scope.item2.mstAppFunctionList[i].mstAccessPrivilegesList[i2].isAuth){ //チェックされた場合
                                $scope.isAuthChecked = true;
                                return;
                            }
                        }
                    }
                };

                $scope.click_check = function(functionName, privilegeName) {
                    switch (functionName) {
                        case '自分のスケジュール操作':
                            switch (privilegeName) {
                                case '表示':  //追加、編集、削除のチェックをはずす
                                    $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[1].isAuth = false;
                                    $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[2].isAuth = false;
                                    $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[3].isAuth = false;
                                case '追加':  //編集、削除のチェックをはずす
                                    $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[2].isAuth = false;
                                    $scope.item2.mstAppFunctionList[0].mstAccessPrivilegesList[3].isAuth = false;
                            }
                        case '他人のスケジュール操作':
                            switch (privilegeName) {
                                case '表示':
                                    $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[1].isAuth = false;
                                    $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[2].isAuth = false;
                                    $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[3].isAuth = false;
                                case '追加':
                                    $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[2].isAuth = false;
                                    $scope.item2.mstAppFunctionList[1].mstAccessPrivilegesList[3].isAuth = false;
                            }
                    }
                };

                $scope.saveRole = function(){
                    var $cids=[];
                    for(var i = 0; i < $scope.item2.mstAppFunctionList.length; i++) { // 配列の長さ分の繰り返し
                        for(var i2 = 0; i2 < $scope.item2.mstAppFunctionList[i].mstAccessPrivilegesList.length; i2++) {
                                if($scope.item2.mstAppFunctionList[i].mstAccessPrivilegesList[i2].isAuth){ //チェックされた場合
                                    $cids.push($scope.item2.mstAppFunctionList[i].mstAccessPrivilegesList[i2].id);
                                }
                            }
                    }
                    // for (var rowitem in $scope.item2.mstAppFunctionList) {
                    //     for(var colitem in rowitem.mstAccessPrivilegesList) { //なぜかこれだとうまくいかない
                    //          if (colitem.isAuth){
                    //              checks.push(colitem.id); //チェックされた場合
                    //          }
                    //      }
                    // }

                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");

                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    if(!$scope.item2.id){
                                        $scope.item2.id = event.data.id;
                                    }
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                    hasChange = true;
                                    if(callSuccess) callSuccess();
                                }
                        };
                        //登録
                        if($scope.item2.isNew){
                            $scope.item2.cids = $cids;
                            $http.post(link_ajax_detail_store, {item: $scope.item2})
                                .then(saveSuccess);
                        //更新
                        }else{
                            $scope.item2.cids = $cids;
                            $http.put(link_ajax_detail_update + "/" + $scope.item2.id, {item: $scope.item2})
                                .then(saveSuccess);
                        }

                    }else{
                        $(".form_edit")[0].reportValidity();
                    }
                };
                $scope.delete = function() {

                    if(!$scope.isDeletable) {
                        alert('利用者が割り当てられているため削除できません。')
                        return
                    }

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: 'ロールをを削除します。よろしいですか？',
                            btnDanger: 'はい',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax_detail_delete + "/" + $scope.item2.id+ "/" + $scope.item2.mstApplicationId, {})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                            hasChange = true;
                                            if(callSuccess) callSuccess();
                                        }
                                    });
                            }
                        });

                };


            });
        }
    </script>
@endpush
