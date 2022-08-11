<div ng-controller="DetailDepartmentController">
    <form class="form_edit_department" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailDepartmentItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="!item.id">部署登録</h4>
                    <h4 class="modal-title" ng-show="item.id">名称変更</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>

                    <div class="form-group" ng-if="!item.id">
                        <div class="row">
                            <label for="parent_id" class="col-md-3 col-sm-3 col-12 control-label">親部署</label>
                            <div class="col-md-9 col-sm-9 col-12">
                                <select name="" id="parent_id" ng-model="item.parent_id"  class="form-control">
                                    <option ng-value="0"></option>
                                    <option ng-repeat="option in options_department" ng-value="option.id"><% repeatLevel(option.text, option.level, option)  %></option>
                                </select>
                            </div>
                        </div>
                    </div> 
                    
                    <div class="form-group">
                        <div class="row">
                            <label for="department_name" class="col-md-3 col-sm-3 col-12 control-label">部署名 <span style="color: red">*</span></label>
                            <div class="col-md-9 col-sm-9 col-12">
                                <input type="text" class="form-control" ng-model="item.department_name" id="department_name"
                                     placeholder="部署名" maxlength="255" required />
                            </div>
                        </div>
                    </div> 
                    
                     
                </div>
            
                <!-- Modal footer -->
                <div class="modal-footer">
                    @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE, PermissionUtils::PERMISSION_DEPARTMENT_TITLE_UPDATE])
                    <button type="submit" class="btn btn-success" ng-click="save()" ng-disabled="item.department_name == ''">
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
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailDepartmentController', function($scope, $rootScope, $http) {
                $scope.type = 'department';
                $scope.item = {};
                $scope.parent = {};
                $scope.options_department = {!! json_encode(\App\Http\Utils\CommonUtils::treeToArr($itemsDepartment,1 , 'name')) !!};

                var mapDepartment = [];
                for(var i =0; i<$scope.options_department.length;i++){
                    mapDepartment[$scope.options_department[i].id] = $scope.options_department[i];
                }
                $rootScope.$on("openNewDepartment", function(event){
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax_get_department).then(function (event){
                            $rootScope.$emit("hideLoading");
                        $scope.options_department=event.data.data
                        })
                    $scope.id = 0;
                    $scope.item = {id:0, parent_id: 0, department_name: ''};
                    $scope.parent = {};
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailDepartmentItem").modal();
                });

                $rootScope.$on("openEditDepartment", function(event, data){
                    $rootScope.$emit("showLoading");
                    
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    hideMessages();
                    hasChange = false;
                    $http.get(link_ajax + "/" +$scope.id+"?type="+$scope.type)
                    .then(function(event) {
                        if(event.data.status == false){
                            $("#modalDetailDepartmentItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.item = event.data.item;
                            $http.get(link_ajax_get_department).then(function (event){
                                $rootScope.$emit("hideLoading");
                                $scope.options_department=event.data.data
                            })
                        }
                    });
                    $("#modalDetailDepartmentItem").modal();
                });

                $scope.repeatLevel = function(text, level, department){
                    var text2 = text;
                    var parentId = department.parent_id;
                    while(parentId > 0){
                        if(typeof mapDepartment[parentId] === 'undefined') {
                            break;
                        }else{
                            var parentDepartment = mapDepartment[parentId];
                            text2 = (parentDepartment.text + '＞' + text2);
                            parentId = parentDepartment.parent_id;
                        }
                    }
                    return text2;
                };

                $scope.save = function(callSuccess){
                    if($(".form_edit_department")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailDepartmentItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.id = event.data.id;                                
                                $("#modalDetailDepartmentItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChangeDepartment = true;
                                if(callSuccess) callSuccess();
                            }
                        }
                        
                        if(!$scope.item.id){
                            $http.post(link_ajax, {item: $scope.item, type: $scope.type}) 
                            .then(saveSuccess);
                        }else{
                            $http.put(link_ajax + "/" +$scope.id, {item: $scope.item, type: $scope.type}) 
                                .then(saveSuccess);
                        }
                    }
                };
                
            })
        }
    </script>
@endpush