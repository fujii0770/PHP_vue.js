<div ng-controller="DetailSignatueController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">電子証明書情報更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">  
                    <div class="message"></div>              
                    <div class="card mb-3">
                        <div class="card-header">電子証明書</div>
                        <div class="card-body d-flex justify-content-between">
                            <span>名称</span>
                            <span><% info.certificate_name %></span> 
                        </div>
                    </div>
                    <label class="label ml-2" for="enable_email_thumbnail">
                        <input type="checkbox" ng-model="info.certificate_flg"  ng-true-value="1" class="mr-2"> 利用する
                        <p class="ml-4 mt-2"> この証明書を「利用する」にすると、既存の証明書は未使用に変更されます。 </p>
                    </label>

                </div>
            
                <!-- Modal footer -->
                <div class="modal-footer">
                    @canany([PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_UPDATE])
                        <button type="submit" class="btn btn-success" ng-click="save()">
                            <ng-template ><i class="far fa-save"></i> 更新</ng-template>
                        </button>
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_DELETE])
                        <button type="button" class="btn btn-danger" ng-if="checkCompany" ng-click="remove()">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
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
            appPacAdmin.controller('DetailSignatueController', function($scope, $rootScope, $http) {
                $scope.save = function(){
                    if( $scope.checkCompany == false){
                        $scope.certificate_flg = ! $scope.info.certificate_flg
                    }else{
                        $scope.certificate_flg = $scope.info.certificate_flg
                    }

                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $http.post(link_update, {certificate_flg:  $scope.certificate_flg})
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'success', 10000));
                            hasChange = true;
                        }
                    });
                };

                $scope.remove = function(){ 
                    $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'電子証明書情報を削除します。実行しますか？', 
                            btnDanger:'はい',
                            databack:  $scope.info.id,
                            callDanger: function(id){
                                $rootScope.$emit("showLoading");
                                $http.post(link_delete , { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'warning', 10000));
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