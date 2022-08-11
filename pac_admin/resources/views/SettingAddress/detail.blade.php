<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-show="!item.id">新規登録</h4>
                    <h4 class="modal-title" ng-show="item.id">共通アドレス帳更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>

                    <div class="card">
                        <div class="card-header">共通アドレス帳詳細</div>
                        <div class="card-body">

                            <div class="mb-2">
                                {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'email', false, 
                                [ 'placeholder' =>'email@example.com','ng-model' =>'item.email', 'id'=>'email' ]) !!}
                            </div>                
                            <div class="mb-2">
                                {!! \App\Http\Utils\CommonUtils::showFormField('name','氏名',Request::get('name', ''),'text', false, 
                                [ 'placeholder' =>'氏名', 'ng-model' =>'item.name', 'id'=>'name' ]) !!}
                            </div>
                            <div class="mb-2">
                                {!! \App\Http\Utils\CommonUtils::showFormField('company_name','会社名',Request::get('company_name', ''),'text', false, 
                                [ 'placeholder' =>'(株)○○○', 'ng-model' =>'item.company_name','id'=>'company_name' ]) !!}
                            </div>                
                            <div class="mb-2">
                                {!! \App\Http\Utils\CommonUtils::showFormField('position_name','役職',Request::get('position_name', ''),'text', false, 
                                [ 'placeholder' =>'役職名', 'ng-model' =>'item.position_name','id'=>'position_name' ]) !!}
                            </div>
                        </div>        
                    </div>
                </div>
            
                <!-- Modal footer -->
                <div class="modal-footer">
                    @can([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_CREATE])
                        <button type="submit" class="btn btn-success" ng-click="save()" ng-show="!info.id && !item.id">
                            <i class="far fa-save"></i> 登録
                        </button>
                    @endcan
                    @can([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_UPDATE])
                        <button type="submit" class="btn btn-success" ng-click="save()" ng-show="!info.id && item.id">
                            <i class="far fa-save"></i> 更新
                        </button>
                    @endcan
                    @can([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_DELETE])
                        <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="item.id">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
                    @endcan

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
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.departmentStamp = {stamp: null};
                $scope.userStamp = {name: "利用者", items:[], selected:[]};
                $scope.id = 0;
                $scope.readonly = !allow_create;
                $scope.isResetPassword = null;
                $scope.stamp_name = "";

                $rootScope.$on("openNewUser", function(event){
                    $scope.id = 0;
                    $scope.item = {id:0, email:"", name:"", company_name:"", position_name: ""};
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openEditUser", function(event, data){
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    hideMessages();
                    hasChange = false;
                    if(allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    $http.get(link_ajax + "/" +$scope.id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.item = event.data.item;
                           }
                    });
                    $("#modalDetailItem").modal();
                });

                $scope.save = function(callSuccess){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){
                            console.log(event.data);
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
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

                $scope.remove = function(){ 
                    $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'削除しますか？', 
                            btnDanger:'はい',
                            databack: $scope.item.id,
                            callDanger: function(id){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + id, { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'warning', 10000));
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