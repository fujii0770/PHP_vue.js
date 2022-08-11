<div ng-controller="DetailLongTermFolderController">
    <form class="form_edit_folder" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailFolderItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!item.id">フォルダ登録</h4>
                        <h4 class="modal-title" ng-show="item.id">名称変更</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="form-group">
                            <div class="row">
                                <label for="folder_name" class="col-md-3 col-sm-3 col-12 control-label">フォルダ名 <span style="color: red">*</span></label>
                                <div class="col-md-9 col-sm-9 col-12">
                                    <input type="text" class="form-control" ng-model="item.folder_name" id="folder_name"
                                           placeholder="フォルダ名" maxlength="255" required />
                                </div>
                            </div>
                        </div>

                        <div class="form-group" ng-if="!item.id">
                            <div class="row">
                                <div class="col-md-2 col-sm-2 col-12">
                                </div>
                                <div class="col-md-9 col-sm-9 col-12">
                                    <input type="checkbox" ng-model="item.inherit_flg" ng-true-value="1" ng-false-value="0" id="inherit_flg"/>
                                    <label for="inherit_flg" class="col-md-10 col-sm-10 col-12" ng-show="item.parent_id == '0'">全社権限設定</label>
                                    <label for="inherit_flg" class="col-md-10 col-sm-10 col-12" ng-show="item.parent_id != '0'">親フォルダの権限設定を引き継ぐ</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_CREATE, PermissionUtils::PERMISSION_LONG_TERM_FOLDER_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="save()" ng-disabled="item.folder_name == ''">
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
            appPacAdmin.controller('DetailLongTermFolderController', function($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.parent = {};

                $rootScope.$on("openNewFolder", function(event, data){
                    $rootScope.$emit("showLoading");
                    hideMessages();
                    $scope.item = {id:0,parent_id: data.parent_id, folder_name: '',inherit_flg:1};
                    $rootScope.$emit("hideLoading");
                    $("#modalDetailFolderItem").modal();
                });

                $rootScope.$on("openEditFolder", function(event, data){
                    $rootScope.$emit("showLoading");
                    hideMessages();
                    $scope.id = data.id;
                    $http.get(link_ajax + "/" +$scope.id)
                        .then(function(event) {
                            if(event.data.status == false){
                                $("#modalDetailFolderItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $rootScope.$emit("hideLoading");
                            }
                        });
                    $("#modalDetailFolderItem").modal();
                });

                $scope.save = function(callSuccess){
                    if($(".form_edit_folder")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailFolderItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.id = event.data.id;
                                $("#modalDetailFolderItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChangeFolder = true;
                                if(callSuccess) callSuccess();
                            }
                        }

                        if(!$scope.item.id){
                            $http.post(link_ajax, {item: $scope.item})
                                .then(saveSuccess);
                        }else{
                            $http.put(link_ajax + "/" +$scope.id, {item: $scope.item})
                                .then(saveSuccess);
                        }
                    }
                };

            })
        }
    </script>
@endpush