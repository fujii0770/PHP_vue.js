<div ng-controller="DetailFolderPermissionsController">
    <form class="form_edit_folder_permissions" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailFolderPermissionsItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" style="height: 100%;">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">ユーザ権限変更</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-3" style="padding-left: 150px;">検索</div>
                                <div class="col-lg-8">
                                    <input class="form-control" type="text" maxlength="256" placeholder="部署・役職（部分一致）" ng-model="name" id="name" ng-change="searchDepartmentOrPosition()" style="margin-left: 13px;margin-bottom: 15px;">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 col-sm-3 col-1"></div>
                                <div class="col-md-9 col-sm-9 col-12" style="margin-bottom: -10px;padding-left: 29px;">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item" ng-click="boardFlgCheck('department')">
                                            <a class="nav-link"
                                               ng-class="{active: permissions_type == 2 }" ng-model="permissions_type" id="permissions_type-2" ng-value="2"
                                               data-toggle="tab" href="#department">部署</a>
                                        </li>
                                        <li class="nav-item" ng-click="boardFlgCheck('position')">
                                            <a class="nav-link"
                                               ng-class="{active: permissions_type == 1 }" ng-model="permissions_type" id="permissions_type-1" ng-value="1"
                                               data-toggle="tab" href="#position">役職</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" ng-show="permissions_type">
                            <div class="row">
                                <label for="permissions" class="col-md-3 col-sm-3 col-12 control-label">権限付与対象 </label>
                                <div class="col-md-9 col-sm-9 col-12">
                                    <div class="col-md-6 col-sm-6 col-12" ng-show="permissions_type == 1">
                                        <ul class="items tree mt-3" style="overflow-x: auto;white-space: nowrap;height: 100%;width: 150%">
                                            <li class="position_list" ng-repeat="(key, folderPermissions) in positionPermissionData">
                                                <input type="checkbox" value="<% folderPermissions.id %>" class="permissions_cid_position" onClick="isChecked(this.checked)" ng-if="folderPermissions.user_flg == 1" checked/>
                                                <input type="checkbox" value="<% folderPermissions.id %>" class="permissions_cid_position" onClick="isChecked(this.checked)" ng-if="folderPermissions.user_flg != 1"/>
                                                <span><% folderPermissions.position_name %></span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-12" ng-show="permissions_type == 2">
                                        <ul class="items tree mt-3 department-tree" id="sortable_depart"  style="overflow-x: auto;white-space: nowrap;height: 100%;width: 150%">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="save()" ng-disabled="permissions_type == ''">
                                <i class="far fa-save"></i> 登録
                            </button>

                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                        @endcanany
                    </div>

                </div>
            </div>
        </div>

    </form>

</div>

@push('scripts')
    <script type="text/babel">
        if(appPacAdmin){
            appPacAdmin.controller('DetailFolderPermissionsController', function($scope, $rootScope, $http, $compile) {
                $scope.id = '';
                $scope.permissions_type = '';
                $scope.permission_disabled_type = 0;
                $scope.positionPermissionData = [];
                $scope.departmentPermissionsData = [];
                $scope.parent = {};
                $scope.isFirstClick = true;
                $scope.name = '';
                $scope.boardFlgCheck = function(type) {
                    if (type === 'position'){
                        $scope.permissions_type = 1;
                    }else if(type === 'department'){
                        $scope.permissions_type = 2;
                    }
                    // $rootScope.$emit("addFolderPermissions",{id:$scope.id});
                }

                $rootScope.$on("addFolderPermissions", function(event, data){
                    $rootScope.$emit("showLoading");
                    if(data.id != $scope.id){
                        $scope.permissions_type = 2;
                        $scope.isFirstClick = true;
                    }
                    hideMessages();
                    $scope.id = data.id;
                    $http.get(link_ajax_get_parent_permissions + "/" +$scope.id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailFolderPermissionsItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.departmentPermissionsData = event.data.departmentPermissionsData;
                                $scope.positionPermissionData = event.data.positionPermissionData;
                                if ($scope.isFirstClick){
                                    $scope.permissions_type = 2;
                                    $scope.addDepartmentTree();
                                }
                                $scope.isFirstClick = false;
                            }
                        });
                    $("#modalDetailFolderPermissionsItem").modal();
                });

                $scope.searchDepartmentOrPosition = function (){
                    $http.get(link_ajax_get_parent_permissions + "/" +$scope.id + "?name=" + $scope.name)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailFolderPermissionsItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.departmentPermissionsData = event.data.departmentPermissionsData;
                                $scope.positionPermissionData = event.data.positionPermissionData;
                                $scope.addDepartmentTree();
                                $scope.openAllTree($scope.departmentPermissionsData);
                            }
                        });
                }

                $scope.selectRow = function(id){
                    if($scope.selectedDepartmentId == id) {
                        $scope.selectedDepartmentId = null;
                    }
                    else{
                        $scope.selectedDepartmentId = id;
                    }
                };

                $scope.addDepartmentTree = function(){
                    let html = '';
                    let department_data = $scope.departmentPermissionsData;
                    for (let key in  department_data){
                        let department = department_data[key];
                        html += `
                            <li class="tree-node department ${department.id}">
                                <div class="name " data-id="${department.id}" data-longTermFolder="${department.name}" data-parent="${department.parent_id}" ng-class="{selected: selectedDepartmentId == ${department.id}}" ng-click="selectRow(${department.id})">
                                      <span class="arrow-down-${department.id}" ng-click="closeTree(${department.id})"><i class="fas fa-caret-down icon icon-down" style="width: 25px;height: 20px;"></i></span>
                                      <span class="arrow-right-${department.id}" ng-click="openTree(${department.id})"><i class="fas fa-caret-right icon icon-right" style="width: 25px;height: 20px;"></i></span>
                                            <input type="checkbox" value="${department.id}" class="tree-checkbox permissions_cid ${department.id}" data-parent="${department.parent_id}" ng-if="${department.user_flg} == 1" style="margin-right: 3%;margin-left:-3%;" checked/>
                                            <input type="checkbox" value="${department.id}" class="tree-checkbox permissions_cid ${department.id}" data-parent="${department.parent_id}" ng-if="${department.user_flg} != 1" style="margin-right: 3%;margin-left:-3%;"/>
                                    <i class="fas fa-folder-open"></i>
                                   <span>${department.name}</span>
                                </div>
                            <ul class="items">
                        `;
                        if (department.data_child){
                            html += departmentTree(department.data_child);
                            html += `</ul></li>`;
                        }else {
                            html += `</ul></li>`;
                        }
                    }
                    let $html = $compile(html)($scope);
                    $('.department-tree').html($html);
                }

                function departmentTree (child_departments){
                    let html = '';
                    for (let key in child_departments){
                        let department = child_departments[key];
                        html += `
                            <li class="tree-node department ${department.id}">
                                <div class="name " data-id="${department.id}" data-longTermFolder="${department.name}" data-parent="${department.parent_id}" ng-class="{selected: selectedDepartmentId == ${department.id}}" ng-click="selectRow(${department.id})">
                                      <span class="arrow-down-${department.id}" ng-click="closeTree(${department.id})"><i class="fas fa-caret-down icon icon-down" style="width: 25px;height: 20px;"></i></span>
                                      <span class="arrow-right-${department.id}" ng-click="openTree(${department.id})"><i class="fas fa-caret-right icon icon-right" style="width: 25px;height: 20px;"></i></span>
                                            <input type="checkbox" value="${department.id}" class="tree-checkbox permissions_cid ${department.id}" data-parent="${department.parent_id}" ng-if="${department.user_flg} == 1" style="margin-right: 3%;margin-left:-3%;" checked/>
                                            <input type="checkbox" value="${department.id}" class="tree-checkbox permissions_cid ${department.id}" data-parent="${department.parent_id}" ng-if="${department.user_flg} != 1" style="margin-right: 3%;margin-left:-3%"/>
                                    <i class="fas fa-folder-open"></i>
                                   <span>${department.name}</span>
                                </div>
                            <ul class="items">
                        `;

                        if (department.data_child){
                            html += departmentTree(department.data_child);
                            html += `</ul></li>`;
                        }else {
                            html += `</ul></li>`;
                        }

                    }
                    return html;
                }

                $scope.openAllTree = function (department_data){
                    for (let key in  department_data){
                        let department = department_data[key];
                        $scope.openTree(department.id)
                        if (department.data_child){
                            $scope.openAllTree(department.data_child);
                        }
                    }
                }

                $scope.openTree = function (id){
                    if (id === 0){
                        $('.parent.department').addClass('open');
                    }else {
                        $('.tree-node.department' + '.' + id ).addClass('open');
                    }
                    $('.arrow-down-' + id).show();
                    $('.arrow-right-' + id).hide();
                }

                $scope.closeTree = function (id){
                    if (id === 0){
                        $('.parent.department').removeClass('open');
                    }else {
                        $('.tree-node.department' + '.' + id ).removeClass('open');
                    }
                    $('.arrow-down-' + id).hide();
                    $('.arrow-right-' + id).show();
                }

                $scope.save = function(callSuccess){
                    let position_ids = [];
                    let department_ids = [];
                    let choose_type1;
                    let choose_type2;
                    if ($scope.permissions_type === 1){
                        choose_type1 = "";
                    }else if($scope.permissions_type === 2){
                        choose_type2 = "";
                    }
                    for(let i =0; i < $('.permissions_cid_position:checked').length; i++){
                        position_ids.push($('.permissions_cid_position:checked')[i].value);
                    }
                    for(let i =0; i < $('.permissions_cid:checked').length; i++){
                        department_ids.push($('.permissions_cid:checked')[i].value);
                    }
                    if($(".form_edit_folder_permissions")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailFolderPermissionsItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.id = event.data.id;
                                $("#modalDetailFolderPermissionsItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChangeFolder = true;
                                if(callSuccess) callSuccess();
                            }
                        }

                        $http.post(link_ajax_save_folder_permissions, {position_ids:position_ids,department_ids: department_ids,id:$scope.id})
                                .then(saveSuccess);
                    }
                };

            })
        }

        $(document).ready(function() {
            $(document).off('click', '.tree-checkbox').on('click', '.tree-checkbox', function(e) {
                if($(this).prop("checked")){//すべて選択
                    // openChecked(this) //PAC_5-3015 長期保管-  キャビネットのフォルダに権限を付与する場合、子部署の権限が一つの場合、親部署にも権限が入る
                    $(this).parent().parent().find(".tree-checkbox").prop("checked", true);
                }else {//すべて選択しない
                    let parents = $(this).parents().find(".tree-checkbox");
                    let departmentTree = [];
                    for (let key in parents){
                        departmentTree[parents[key].defaultValue] = parents[key].dataset;
                    }
                    //親部署のチェックボックスを閉じる
                    function closeChecked(local_id){
                        for (let id in departmentTree){
                            if (local_id == id){
                                $('.tree-checkbox.' + id).prop("checked", false);
                                closeChecked(departmentTree[id]['parent'])
                            }
                        }
                    }
                    closeChecked($(this)[0].dataset['parent'])
                    $(this).parent().parent().find(".tree-checkbox").prop("checked", false);
                }
            });

            //親部署のチェックボックスを選択
            function openChecked(box){
                let parents = $(box).parents().find(".tree-checkbox");
                let checked = [];
                if ($(box)[0] && $(box)[0].dataset){
                    let localParent = $(box)[0].dataset['parent'];
                    for (let key in parents){
                        if (parents[key].dataset && localParent == parents[key].dataset['parent']){
                            checked[parents[key].dataset['parent']] = !!parents[key].checked
                            if (!checked[parents[key].dataset['parent']]) break;
                        }
                    }
                    for (let id in checked){
                        if (checked[id]){
                            $('.tree-checkbox.'+ id).prop("checked", true);
                            openChecked('.tree-checkbox.'+ id)//親部署のチェックボックスを選択
                        }
                    }
                }
            }


        })
    </script>
@endpush
