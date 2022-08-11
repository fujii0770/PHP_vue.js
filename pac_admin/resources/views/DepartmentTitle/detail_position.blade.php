<div ng-controller="DetailPositionController">
    <form class="form_edit_position" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailPositionItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="!item.id">役職登録</h4>
                    <h4 class="modal-title" ng-show="item.id">名称変更</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>
                    
                    <div class="form-group">
                        <div class="row">
                            <label for="position_name" class="col-md-3 col-sm-3 col-12 control-label">役職名 <span style="color: red">*</span></label>
                            <div class="col-md-9 col-sm-9 col-12">
                                <input type="text" class="form-control" ng-model="item.position_name" id="position_name"
                                     placeholder="役職名" maxlength="255" required />
                            </div>
                        </div>
                    </div> 
                    
                     
                </div>
            
                <!-- Modal footer -->
                <div class="modal-footer">
                    @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE, PermissionUtils::PERMISSION_DEPARTMENT_TITLE_UPDATE])
                    <button type="submit" class="btn btn-success" ng-click="save()" ng-disabled="item.position_name == ''">
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
            appPacAdmin.controller('DetailPositionController', function($scope, $rootScope, $http) {
                $scope.type = 'position';
                $scope.item = {};
                $scope.parent = {};
                
                $rootScope.$on("openNewPosition", function(event){
                    $scope.id = 0;
                    $scope.item = {id:0, parent_id: 0, department_name: ''};
                    $scope.parent = {};
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailPositionItem").modal();
                });

                $rootScope.$on("openEditPosition", function(event, data){
                    $rootScope.$emit("showLoading");
                    
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    hideMessages();
                    hasChange = false;
                    $http.get(link_ajax + "/" +$scope.id+"?type="+$scope.type)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailPositionItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.item = event.data.item;
                        }
                    });
                    $("#modalDetailPositionItem").modal();
                });
              
                $scope.save = function(callSuccess){
                    if($(".form_edit_position")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailPositionItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.id = event.data.id;                                
                                $("#modalDetailPositionItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChangePosition = true;
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