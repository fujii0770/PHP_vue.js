<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-stamp" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 ng-show="!item.id" class="modal-title">無害化回線登録</h4>
                        <h4 ng-show="item.id" class="modal-title">無害化回線更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="row form-group">
                            <label for="name" class="col-sm-4 control-label">回線名 <span style="color: red">*</span></label>
                            <div class="col-md-7 col-sm-5 col-8">
                                <input type="text" required class="form-control" id="sanitizing_line_name" ng-model="item.sanitizing_line_name" maxlength="50">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="name" class="col-sm-4 control-label">無害化ファイル要求上限 <span style="color: red">*</span></label>
                            <div class="col-md-2 col-sm-5 col-4">
                                <input type="number" required class="form-control" id="sanitize_request_limit" ng-model="item.sanitize_request_limit" maxlength="50">
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button ng-show="!item.id" type="button" class="btn btn-success" ng-click="save()">
                            <i class="fas fa-save"></i> 登録
                        </button>
                        <button ng-show="item.id" type="submit" class="btn btn-success" ng-click="save()">
                            <i class="far fa-save"></i> 更新
                        </button>
                        <button ng-show="item.id" type="button" class="btn btn-danger" ng-click="remove()">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};

                $rootScope.$on("openNewLine", function(event){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    hideMessages();
                    $scope.item = {id:0, sanitizing_line_name: '', sanitize_request_limit: 0};
                    $rootScope.$emit("hideLoading");
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openEditLine", function(event, data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    hideMessages();
                    $scope.id = data.id;
                    $http.get(link_ajax + "/" +$scope.id)
                        .then(function(event) {
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $rootScope.$emit("hideLoading");
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $scope.save = function(callSuccess){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                $("#modalDetailItem").modal("hide");
                            }else{
                                $scope.id = event.data.id;
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                $("#modalDetailItem").modal();
                                if(callSuccess) callSuccess();
                                hasChange = true;
                                $("#modalDetailItem").modal("hide");
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

                $scope.remove = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択中の無害化回線を削除しますか？',
                            btnDanger:'はい',
                            databack: $scope.id,
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + $scope.id)
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                            $("#modalDetailItem").modal("hide");
                                        }else{
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                            hasChange = true;
                                            $("#modalDetailItem").modal("hide");
                                        }
                                    });
                            }
                        });
                };

            })
        }
    </script>
@endpush
