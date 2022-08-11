<div ng-controller="ListSettingAdminController">

    <div class="message message-info mt-3"></div>
    <form action="" name="adminForm">
        @csrf
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormField('IpName','名称',Request::get('IpName', ''),'text', false, 
                    [ 'placeholder' =>'氏名(部分一致)', 'id'=>'IpName' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormField('ip_address','IPアドレス',Request::get('ip_address', ''),'ip_address', false, 
                    [ 'placeholder' =>'IPアドレス(部分一致)', 'id'=>'ip_address' ]) !!}
                </div>  
                <div class="col-lg-2"><button class="btn btn-primary mb-1" ng-click="search()"><i class="fas fa-search" ></i> 検索</button></div>
                <div class="text-right col-lg-2">
                    @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_CREATE,PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_UPDATE,PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_DELETE])
                    <button type="button" class="btn btn-success" ng-click="save()">
                        <i class="far fa-save"></i> 更新
                    </button>
                    @endcanany
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">接続IP制限設定</div>
            <div class="card-body">
                <div class="table-head">
                    <div class="form-group">
                        <div class="row">
                            <label for="permit_unregistered_ip_flg" class="control-label col-md-3">登録外IPのログイン許可</label>
                            <div class="col-md-8">
                                <label class="label mr-2" for="permit_unregistered_ip_flg">
                                    @can([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_UPDATE])
                                    <input type="checkbox" ng-model="permit_unregistered_ip_flg" id="permit_unregistered_ip_flg" ng-true-value="1" ng-false-value="0"> 有効にする
                                    @else
                                    <input type="checkbox" ng-model="permit_unregistered_ip_flg" id="permit_unregistered_ip_flg" ng-disabled="1" ng-true-value="1" ng-false-value="0"> 有効にする
                                    @endcan
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-head">
                    <div class="form-group">
                        <div class="row">
                            <label class="col-lg-6 col-md-2 control-label" ></label>
                            <div class="col-lg-6 col-md-2 text-right">
                                @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_CREATE])
                                <button type="button" class="btn btn-success mb-1" ng-click="addNew()"><i class="fas fa-plus-circle"></i> 登録</button>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_DELETE])
                                <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="delete()"><i class="fas fa-trash-alt"></i> 削除</button>
                                @endcanany
                            </div>
                        </div>
                    </div>
                </div>
                <span class="clear"></span>
                
                <table align="center" class="tablesaw-list tablesaw table-bordered adminlist mt-1 width-65" data-tablesaw-mode="swipe" data-tablesaw-sortable>
                    <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toggleCheckAll()" />
                            </th>
                            <th data-tablesaw-sortable-col scope="col" class="sort width-50">名称
                                <i class="icon fas fa-sort"></i><i class="icon icon-active fas fa-caret-down"></i><i class="icon icon-active fas fa-caret-up"></i>
                            </th>
                            <th data-tablesaw-sortable-col scope="col" class="sort width-50">IPアドレス
                                <i class="icon fas fa-sort"></i><i class="icon icon-active fas fa-caret-down"></i><i class="icon icon-active fas fa-caret-up"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                            <tr class="row-{{ $i }} row-edit">
                                <td class="title">
                                    <input type="checkbox" value="{{ $i }}" ng-click="toggleCheck({{ $i }})"
                                           class="cid" onClick="isChecked(this.checked)" />
                                </td>
                                <td class="title" ng-click="editRecord({{ $i }})">{{ $item['name'] }}</td>
                                <td ng-click="editRecord({{ $i }})">{{ $item['ip_address'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @include('layouts.table_footer',['data' => $items])
            </div>
        </div>
        <div class="text-right mt-3">
            @canany([PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_CREATE,PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_UPDATE,PermissionUtils::PERMISSION_IP_RESTRICTION_SETTING_DELETE])
            <button type="button" class="btn btn-success" ng-click="save()">
                <i class="far fa-save"></i> 更新
            </button>
            @endcanany
        </div>
    </form>
</div>


@push('scripts')
    <script>
        @if (session('message'))
            $(function() {
                $(".message").append(showMessages(["{{ session('message') }}"], 'success', 10000));
            });
        @endif
        @if ($dirty)
            $(function() {
                $(".message").append(showMessages(["リストが変更されました。反映するには更新ボタンを押してください。"], 'info'));
            });
        @endif

        if(appPacAdmin){
            appPacAdmin.controller('ListSettingAdminController', function($scope, $rootScope, $http){
                $rootScope.info = {};
                $rootScope.id = -1;
                $rootScope.search = {IpName:"", ip_address:""};
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($cids) !!};
                $scope.permit_unregistered_ip_flg = {{ $permit_unregistered_ip_flg }};

                $scope.toggleCheckAll = function() {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                };

                $scope.toggleCheck = function(id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }
                };

                $scope.addNew = function() {
                    if ($(".cid").length >= {{ $max_count }}) {
                        $(".message").append(showMessages(['これ以上登録できません。'], 'danger', 10000));
                        return;
                    }
                    $rootScope.id = -1;
                    $rootScope.info = {name: "", ip_address: ""};
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                };

                $scope.editRecord = function(id) {
                    $rootScope.id = id;
                    hideMessages();
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/" + id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $rootScope.info = event.data.info;
                            }

                        });
                    $("#modalDetailItem").modal();
                };

                $scope.save = function() {
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax + "/bulk-update", {permit_unregistered_ip_flg: $scope.permit_unregistered_ip_flg})
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                location.href = link_refresh;
                            }
                        });
                };

                $scope.delete = function() {                
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '選択されたIPアドレスを削除します。よろしいですか？',
                            btnDanger: 'はい',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_delete_select, {selected: $scope.selected})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            location.href = link_refresh;
                                        }
                                    });
                            }
                        });

                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }
        
        $("#modalDetailItem").on('hide.bs.modal', function () {
             if(hasChange){
                 location.href = link_refresh;
             }
        });
    </script>
@endpush
