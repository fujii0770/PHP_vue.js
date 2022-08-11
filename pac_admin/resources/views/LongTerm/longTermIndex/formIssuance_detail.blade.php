<div ng-controller="DetailLongTermIndexFormIssuanceController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailFormIssuanceItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="!info.id">明細インデックス情報登録</h4>
                    <h4 class="modal-title" ng-show="info.id">明細インデックス情報更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal" ng-show="!info.id">
                    <div class="message"></div>
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">インデックス名</label>
                            <div class="col-md-8" id="name1">
                            {!! \App\Http\Utils\CommonUtils::buildSelect($frmLongTermIndexName, 'frmLongTermIndexName','','',
                                    ['class'=> 'form-control', 'ng-model' =>'info.temp', 'ng-readonly'=>"readonly",'id'=> 'form-frm']) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <ng-template ng-show="!info.id">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="tempsave()">
                                <i class="far fa-envelope"></i> 登録
                            </button>
                        @endcanany
                    </ng-template>
                    <ng-template ng-show="info.id">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_DELETE])
                            <button type="submit" class="btn btn-danger" ng-click="remove()">
                                <i class="fas fa-trash-alt"></i> 登録解除
                            </button>
                        @endcanany
                    </ng-template>
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
            appPacAdmin.controller('DetailLongTermIndexFormIssuanceController', function($scope, $rootScope, $http) {
                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        // $rootScope.$on("openNewFormIssuanceIndex");
                        var saveSuccess = function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailFormIssuanceItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $rootScope.info.id = event.data.id;
                                $rootScope.info.data_type = "" + event.data.data_type;
                                $("#modalDetailFormIssuanceItem .message").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                            }
                        }

                        if($rootScope.info.id)
                            $http.post(link_ajax + "/update/"+$rootScope.info.id, $rootScope.info).then(saveSuccess);
                        else $http.post(link_ajax, $rootScope.info).then(saveSuccess);
                    }
                };

                $rootScope.$on("openNewFormIssuanceIndex", function(event){
                    $rootScope.id = 0;
                    $rootScope.info = {id:0, index_name:"", data_type:"0"};
                    hideMessages();
                    hasChange = false;
                    $rootScope.readonly = false;
                    $rootScope.readonlyState = false;
                    $("#modalDetailFormIssuanceItem").modal();
                });

                //PAC_5-407 管理者削除機能を追加
                $scope.remove = function(){
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'長期保管インデックスを登録解除します。よろしいですか？',
                                btnDanger:'はい',
                                databack: $scope.info.id,
                                callDanger: function(id){
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_ajax + "/formIssuanceRelease/" + id, { })
                                        .then(function(event) {
                                            $rootScope.$emit("hideLoading");
                                            if(event.data.status == false){
                                                $("#modalDetailFormIssuanceItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                            }else{
                                                $("#modalDetailFormIssuanceItem .message").append(showMessages(event.data.message, 'warning', 10000));
                                                location.reload();
                                            }
                                        });
                                }
                            });
                    };

                //帳票インデックス名登録処理
                $scope.tempsave = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        var $form = document.getElementById('form-frm');
                        console.log($form.value);
                        $http.post(link_ajax + "/formIssuanceValid/" + $form.value, { })
                                        .then(function(event) {
                                            $rootScope.$emit("hideLoading");
                                            if(event.data.status == false){
                                                $("#modalDetailFormIssuanceItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                            }else{
                                                $("#modalDetailFormIssuanceItem .message").append(showMessages(event.data.message, 'warning', 10000));
                                                location.reload();
                                            }
                                        });
                    }
                };
            })
        }
    </script>
@endpush
