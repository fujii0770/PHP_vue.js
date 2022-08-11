<div ng-controller="IssuanceUserRegisterController">
    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="GET">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('email','メールアドレス',Request::get('email', ''),'text', false,
                    [ 'placeholder' =>'メールアドレス', 'id'=>'email' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('username','氏名',Request::get('username', ''),'text', false,
                    [ 'placeholder' =>'氏名', 'id'=>'username' ]) !!}
                </div>

                <div class="col-lg-3 form-group">
                    <label class="control-label" >部署</label>
                    {!! \App\Http\Utils\CommonUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control']) !!}
                </div>
                <div class="col-lg-2 form-group">
                    <label class="control-label" >役職</label>
                    {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-2 form-group">
                    <label class="control-label" >サービス利用</label>
                    {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::FRM_SRV_USER_FLG, 'frmSrvUserFlg', Request::get('frmSrvUserFlg', ''),'',['class'=> 'form-control']) !!}
                </div>

                <div class="col-lg-4"></div>
                <div class="col-lg-6 text-right padding-top-20" style="padding-right:39px">
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
                                    @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_UPDATE])
                                        <button type="button" class="btn btn-success m-0" ng-click="bulkUsage(1)">一括利用登録</button>
                                    @endcanany
                                    @canany([PermissionUtils::PERMISSION_ADMIN_ISSUANCE_SETTING_UPDATE])
                                        <button type="button" class="btn btn-danger m-0" ng-click="bulkUsage(0)">一括利用解除</button>
                                    @endcanany
                                </div>
                                <div class="col-lg-6 text-right">
                                    <button class="btn btn-primary m-0" ng-click="detaildisplay()">詳細表示</button>
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
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('サービス利用', 'frmSrvUserFlg', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名（メールアドレス）', 'username', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'adminDepartment', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('役職', 'position', $orderBy, $orderDir) !!}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @if ($arrHistory)
                            @foreach ($arrHistory as $i => $item)
                                <tr class="row-{{ $item->id }} row-edit" ng-class="{ edit: id == {{ $item->id }} }">
                                    <td class="title">
                                        @if($item->frm_srv_user_flg)
                                        <input type="checkbox" value="{{ $item->id }}"
                                               name="cids[]" class="cid" onClick="isChecked(this.checked)"
                                               ng-click="toogleCheck({{ $item->id }})" checked/>                                            
                                        @else
                                            <input type="checkbox" value="{{ $item->id }}"
                                            name="cids[]" class="cid" onClick="isChecked(this.checked)"
                                            ng-click="toogleCheck({{ $item->id }})"/> 
                                        @endif
                                    </td>
                                    <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ \App\Http\Utils\AppUtils::FRM_SRV_USER_FLG[$item->frm_srv_user_flg] }}</td>
                                    <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ $item->user_name.'('.$item->email.')' }}</td>
                                    <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ $item->department_name }}</td>
                                    <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ $item->position_name }}</td>
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
            appPacAdmin.controller('IssuanceUserRegisterController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($arrHistory->pluck('id')) !!};
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
                                cids.push($(".cid")[i].value);
                                hit = true;
                                break;
                            }
                        }
                        if (!hit) {
                            cidsoff.push($(".cid")[i].value);
                        }
                    }
                    if ($('.row-edit input:checkbox:checked').length == 0) {
                        if (type == 1) {
                            $(".message").append(showMessages(['一括利用登録ボタン押下時は、少なくともチェックボックスを一つ選択してください。'], 'danger', 10000));
                        } else {
                            $(".message").append(showMessages(['一括利用解除ボタン押下時は、少なくともチェックボックスを一つ選択してください。'], 'danger', 10000));
                        }
                    } else {
                        if (type == 1) {
                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '選択されたユーザーを利用対象ユーザにします。',
                                    btnSuccess:'はい',
                                    callSuccess: function(){
                                        $rootScope.$emit("showLoading");
                                        $http.post(link_bulk_usage, {cids: cids, action: 'register'})
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
                        } else {
                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '選択されたユーザーを未利用対象ユーザにします。',
                                    btnSuccess:'はい',
                                    callSuccess: function(){
                                        $rootScope.$emit("showLoading");
                                        $http.post(link_bulk_usage, {cids: cids, action: 'cancel'})
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
                    }
                };

                $scope.detaildisplay = function () {
                    event.preventDefault();
                    if ($('.row-edit input:checkbox:checked').length != 1) {
                        $(".message-list").append(showMessages(['詳細ボタン押下時は、チェックボックスを一つ選択してください。'], 'danger', 10000));
                        return;
                    } else {
                        let id = $('.row-edit input:checkbox:checked').attr('value');
                        $rootScope.$emit("openUserDetailsFormIssuance",{id:id});
                    }
                };

                $scope.detailsRecord = function (id) {
                    $rootScope.$emit("openUserDetailsFormIssuance",{id:id});
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