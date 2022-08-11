<div ng-controller="ExpenseUserRegisterController">
    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="GET">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('account_name','名称',Request::get('account_name', ''),'text', false,
                    [ 'placeholder' =>'', 'id'=>'account_name' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('remarks','備考',Request::get('remarks', ''),'text', false,
                    [ 'placeholder' =>'', 'id'=>'remarks' ]) !!}
                </div>
                <div class="col-lg-3 text-left padding-top-20" style="padding-right:39px">
                    <button id="btnSearchAction"  class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                </div>
            </div>

            <div class="message message-list mt-3"></div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_DELETE])
                                        <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="bulkUsage(0)"><i class="fas fa-trash-alt"></i> 削除</button>
                                    @endcanany
                                </div>
                                <div class="col-lg-6 text-right">
                                    @canany([PermissionUtils::PERMISSION_MASTER_EXPENSE_SETTING_CREATE])
                                        <div class="btn btn-success  mb-1" ng-click="addAccount(0)"><i class="fas fa-plus-circle" ></i> 追加</div>
                                    @endcanany
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input ng-if="isCheckAll" type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" checked/>
                                <input ng-if="!isCheckAll" type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>
                            </th>
                            <th scope="col"  class="sort" style="width: 30%">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('名称', 'account_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort" style="width: 70%">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('備考', 'remarks', $orderBy, $orderDir) !!}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @if ($arrHistory)
                            @foreach ($arrHistory as $i => $item)
                               <tr class="row-'{{ $item->account_name }}' row-edit" ng-class="{ edit: account_name == '{{ $item->account_name }}' }">
                                    <td class="title">
                                        <input type="checkbox" value="{{ $item->account_name }}"
                                        name="cids[]" class="cid" onClick="isChecked(this.checked)"
                                        ng-click="toogleCheck('{{ $item->account_name }}')"/> 
                                    </td>
                                    <td ng-click="detailsRecord('{{ $item->account_name}}')" class="title">{{ $item->account_name}}</td>
                                    <td ng-click="detailsRecord('{{ $item->account_name}}')" class="title">{{ $item->remarks }}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                    @if ($arrHistory && $arrHistory[0])
                        @include('layouts.table_footer',['data' => $arrHistory])
                    @else
                        <div class="mt-3">0 件中 0 件から 0 件までを表示</div>
                    @endif
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="action" value="">
            </div>
        </div>

    </form>

</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ExpenseUserRegisterController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($arrHistory->pluck('account_name')) !!};
                var arrHistory = {!! json_encode($arrHistory) !!};
                
                if($('.row-edit input:checkbox:checked').length == $('.row-edit input:checkbox').length && $('.row-edit input:checkbox:checked').length != 0){
                    $scope.isCheckAll = true;
                }else{
                    $scope.isCheckAll = false;
                }

                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                };

                $scope.toogleCheck = function (id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }

                    if($('.row-edit input:checkbox:checked').length == $('.row-edit input:checkbox').length && $('.row-edit input:checkbox:checked').length != 0){
                        $scope.isCheckAll = true;
                    }else{
                        $scope.isCheckAll = false;
                    }
                };

                $scope.bulkUsage = function (type) {
                    event.preventDefault();
                    var cids = [];
                    var cidsoff = [];

                    var hit = false;
                    for (var i = 0; i < $(".cid").length; i++) {
                        var hit = false;
                        for (var i2 = 0; i2 < $(".cid:checked").length; i2++) {
                            if ($(".cid")[i].value == $(".cid:checked")[i2].value) {
                                cids.push( $(".cid")[i].value );
                                hit = true;
                                break;
                            }
                        }
                        if (!hit) {
                            cidsoff.push($(".cid")[i].value);
                        }
                    }
                    if ($('.row-edit input:checkbox:checked').length == 0) {
                            $(".message").append(showMessages(['削除ボタン押下時は、少なくともチェックボックスを一つ選択してください。'], 'danger', 10000));
                    } else {
                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '選択された勘定科目を削除します。',
                                    btnSuccess:'はい',
                                    callSuccess: function(){
                                        $rootScope.$emit("showLoading");
                                        $http.post(link_bulk_usage, {cids: cids})
                                            .then(function (event) {
                                                $rootScope.$emit("hideLoading");
                                                if (event.data.status == false) {
                                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                                } else {
                                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                                    location.reload();
                                                }
                                            });
                                    }
                                });
                    }
                };

                $scope.addAccount = function (account_name) {
                    $rootScope.$emit("openUserDetailsExpMAccountAdd",{account_name:account_name});
                };

                $scope.detailsRecord = function (account_name) {
                    $rootScope.$emit("openUserDetailsExpMAccount",{account_name:account_name});
                };

            });
        }else{
            throw new Error("Something error init Angular.");
        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
            if(hasChange){
                document.adminForm.action.value = '';
                document.adminForm.submit();
            }
        });

        $("#btnSearchAction").on('click', function () {
            document.adminForm.action.value = 'search';
        });
    </script>
@endpush

@push('styles_after')
    <style>
        .select2-container .select2-selection{
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{ height: 36px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered{     line-height: 24px; }

    </style>
@endpush
