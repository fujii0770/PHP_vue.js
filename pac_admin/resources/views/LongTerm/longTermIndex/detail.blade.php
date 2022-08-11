<div ng-controller="DetailLongTermIndexController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="!info.id">長期保管インデックス情報登録</h4>
                    <h4 class="modal-title" ng-show="info.id">長期保管インデックス情報更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message"></div>
                    {!! \App\Http\Utils\CommonUtils::showFormField('index_name','インデックス名','','text', false,
                            [ 'placeholder' =>'取引年月日', 'ng-model' =>'info.index_name', 'ng-readonly'=>"readonly", 'id'=>'index_name', 'maxlength' => 256]) !!}
                        
                    

                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">データ型</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::LONGTERM_INDEX_DATA_TYPE, 'data_type','','',
                                    ['class'=> 'form-control', 'ng-model' =>'info.data_type', 'ng-readonly'=>"readonly"]) !!}
                            </div>
                        </div>
                    </div>

                </div>
            
                <!-- Modal footer -->
                <div class="modal-footer">
                    <ng-template ng-show="!info.id">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                <i class="far fa-envelope"></i> 登録
                            </button>
                        @endcanany
                    </ng-template>
                    <ng-template ng-show="info.id">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                <i class="far fa-save"></i> 更新
                            </button>
                        @endcanany
                    </ng-template>
                    <ng-template ng-show="info.id">
                        @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_DELETE])
                            <button type="submit" class="btn btn-danger" ng-click="remove()">
                                <i class="fas fa-trash-alt"></i> 削除
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
            appPacAdmin.controller('DetailLongTermIndexController', function($scope, $rootScope, $http) {
                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $rootScope.info.id = event.data.id;
                                $rootScope.info.data_type = "" + event.data.data_type;
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                            }
                        }

                        if($rootScope.info.id)
                            $http.post(link_ajax + "/update/"+$rootScope.info.id, $rootScope.info).then(saveSuccess);
                        else $http.post(link_ajax, $rootScope.info).then(saveSuccess);
                    }
                };

                $rootScope.$on("openNewIndex", function(event){
                    $rootScope.id = 0;
                    $rootScope.info = {id:0, index_name:"", data_type:"0"};
                    hideMessages();
                    hasChange = false;
                    $rootScope.readonly = false;
                    $rootScope.readonlyState = false;
                    $("#modalDetailItem").modal();
                });

                //PAC_5-407 管理者削除機能を追加
                $scope.remove = function(){
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'長期保管インデックスを削除します。よろしいですか？',
                                btnDanger:'はい',
                                databack: $scope.info.id,
                                callDanger: function(id){
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_ajax + "/delete/" + id, { })
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