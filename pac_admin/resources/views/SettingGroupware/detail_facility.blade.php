<div ng-controller="DetailSettingFacilityController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!info.id"> 設備登録</h4>
                        <h4 class="modal-title" ng-show="info.id"> 名称変更</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message"></div>
                        <div class="form-group">
                            <div class="row">
                                <label for="given_name" class="col-md-4 control-label">設備名 <span style="color: red">*</span></label>
                                
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" required maxlength="45" class="form-control" ng-model="info.name" id="name" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_CREATE])
                        <ng-template ng-show="!info.id">
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                <ng-template ng-show="!info.sendEmail"><i class="fas fa-plus-circle"></i> 登録</ng-template>
                            </button>
                        </ng-template>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_UPDATE])
                        <ng-template ng-show="info.id">
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                <i class="far fa-save"></i> 更新
                            </button>
                        </ng-template>
                        @endcanany
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> 閉じる
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
            appPacAdmin.controller('DetailSettingFacilityController', function($scope, $rootScope, $http){
                $scope.info = {};
                
                $rootScope.$on("openNewFacility", function(event){
                    $scope.id = 0;
                    $scope.info = {id:0, name:""};
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openEditFacility", function(event, data){
                    $scope.id = data.id;
                    $scope.name = data.name;

                    $scope.info = {id:data.id, name:data.name};
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });
              
                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");

                        if (!$scope.info.id) {
                            $http.post(link_ajax_domain + link_ajax, {"adminRequest": {"portalCompanyId":{{$portalCompanyId}}, "portalEmail":"{{$portalEmail}}", "editionFlg":"{{$editionFlg}}", "envFlg":"{{$envFlg}}", "serverFlg":"{{$serverFlg}}"}, "name":$scope.info.name, "mstCompanyId":{{$mstCompanyId}} })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.status != 200){
                                            $("#modalDetailItem .message").append(showMessages(['設備の登録に失敗しました。'], 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message").append(showMessages(['設備を登録しました。'], 'success', 10000));
                                            hasChangeFacility = true;
                                        }
                                    });
                        } else {
                            $http.put(link_ajax_domain + link_ajax + "/" + $scope.info.id, {"adminRequest": {"portalCompanyId":{{$portalCompanyId}}, "portalEmail":"{{$portalEmail}}", "editionFlg":"{{$editionFlg}}", "envFlg":"{{$envFlg}}", "serverFlg":"{{$serverFlg}}"},"name":$scope.info.name})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.status != 200){
                                            $("#modalDetailItem .message").append(showMessages(['設備名の更新に失敗しました。'], 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message").append(showMessages(['設備名を更新しました。'], 'success', 10000));
                                            hasChangeFacility = true;
                                        }
                                    });
                        }
                    }
                };
            });
        }
    </script>
@endpush
