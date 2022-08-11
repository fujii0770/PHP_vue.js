<div ng-controller="DetailSettingAdminController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="id < 0">IPアドレス登録</h4>
                    <h4 class="modal-title" ng-show="id >= 0">IPアドレス更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message"></div>
                    {!! \App\Http\Utils\CommonUtils::showFormField('name','名称','','text', false,
                            [ 'placeholder' =>'', 'ng-model' =>'info.name', 'ng-readonly'=>"readonly", 'id'=>'name' ]) !!}
                    {!! \App\Http\Utils\CommonUtils::showFormField('ip_address','IPアドレス','','text', true,
                            [ 'placeholder' =>'123.123.123.123', 'ng-model' =>'info.ip_address', 'ng-readonly'=>"readonly", 'id'=>'ip_address', 'pattern'=>"(^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])((\/\d)|(\/[1-2]\d)|(\/3[0-2]))?$)|(^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|(\*))\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|(\*))$)", 'maxlength' => 18]) !!}
                </div>

                <!-- Modal footer -->

                <!-- PAC_5-955 登録権限＆削除権限の追加 -->
                <div class="modal-footer">
                    @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_CREATE])
                    <ng-template ng-show="id < 0">
                        <button type="submit" class="btn btn-success" ng-click="save()">
                            <i class="far fa-envelope"></i> 登録
                        </button>
                    </ng-template>
                    @endcanany
                    <ng-template ng-show="id >= 0">
                    @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_UPDATE])
                        <button type="submit" class="btn btn-success" ng-click="save()">
                            <i class="far fa-save"></i> 更新
                        </button>
                    </ng-template>
                    @endcanany
                    @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_DELETE])
                    <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="id >= 0">
                        <i class="fas fa-trash-alt"></i> 削除
                    </button>
                    @endcanany
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> 閉じる
                    </button>
                </div>
                <!-- PAC_5-955 終了 -->

                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailSettingAdminController', function($scope, $rootScope, $http) {
                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                            }
                        }

                        if($rootScope.id >= 0)
                            $http.put(link_ajax + "/"+$rootScope.id, $rootScope.info).then(saveSuccess);
                        else $http.post(link_ajax, $rootScope.info).then(saveSuccess);
                    }
                };

                $scope.remove = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'IPアドレスを削除します。よろしいですか？',
                            btnDanger:'はい',
                            databack: $rootScope.id,
                            callDanger: function(id){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + $rootScope.id, { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'warning', 10000));
                                            hasChange = true;
                                            $("#modalDetailItem").modal('hide');
                                        }
                                    });
                            }
                        });
                };
            })
        }
    </script>
@endpush
