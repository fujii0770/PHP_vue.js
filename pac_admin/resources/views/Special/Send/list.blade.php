@section('content')

    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
            @csrf
            <div class="form-search form-horizontal">
                <div class="row">
                    <div class="col-lg-3 form-group">
                        <div class="row">
                            <div class="col-md-12">
                                {!! \App\Http\Utils\CommonUtils::showFormField('group_name','組織名	',Request::get('group_name', ''),'text', false, [ 'col' => 4 ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 form-group">
                        <div class="row">
                            <label class="col-md-6 control-label">地域名</label>
                            <div class="col-md-6">
                                {!! \App\Http\Utils\CommonUtils::buildSelect($regionList, 'region_name', Request::get('region_name', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 form-group">
                        <div class="row">
                            <label class="col-md-6 control-label">申請状態</label>
                            <div class="col-md-6">
                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::SEND_CODE , 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button class="btn btn-primary mb-1"><i class="fas fa-search"></i> 検索</button>
                    @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_SEND_UPDATE])
                        <button type="button" class="btn btn-success mb-1" ng-disabled="selected.length==0" ng-click="showFormSend()"><i class="fas fa-plus-circle"></i> 申請</button>
                        <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="showFormSendCln()"><i class="fas fa-times-circle"></i> 申請取消</button>
                    @endcanany
                </div>

                <div class="message message-list mt-3"></div>
                    <div class="card mt-3">
                        <div class="card-header">連携申請一覧</div>
                        <div class="card-body">
                            <div class="table-head">
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-lg-6 col-md-4 " style="float:left">
                                            <label class="d-flex" style="float:left"><span style="line-height: 27px">表示件数：</span>
                                                <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                    <option {{Request::get('limit') == '10' ? 'selected': ''}} value="10">10</option>
                                                    <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                    <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                </select>
                                            </label>
                                    </div>
                                </div>
                            </div>
                            <span class="clear"></span>

                            <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                                <thead>
                                <tr>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                        <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>
                                    </th>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('組織名', 'rs.group_name', $orderBy, $orderDir) !!}
                                    </th>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('地域名', 'rs.region_name', $orderBy, $orderDir) !!}
                                    </th>
                                    <th scope="col" class="sort" style="width: 150px">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('申請状態', 'cc.state', $orderBy, $orderDir) !!}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($itemsSend as $i => $item)
                                    <tr class="">
                                        <td class="title">
                                            <input type="checkbox" value="{{ $item['company_id'] }}" ng-click="toogleCheck({{ $item['company_id'] }})"
                                                   name="cids[]" class="cid" onClick="isChecked(this.checked)"/>
                                        </td>
                                        <td class="title">{{ $item['group_name'] }}</td>
                                        <td>{{ $item['region_name'] }}</td>
                                        <td>{{ \App\Http\Utils\AppUtils::SEND_CODE[$item['state']] }}</td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                            @include('layouts.table_footer',['data' => $itemsSend])
                        </div>
                        <input type="hidden" value="{{ $orderBy }}" name="orderBy"/>
                        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir"/>
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked"/>
                    </div>
            </div>
            <input type="hidden" name="action" value="search">
        </form>

        <form class="formSend" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalSend" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">連携申請</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message">
                                <div class="text-left">以下の組織に連携申請します。</div>
                                <br/>
                                <div class="text-left">よろしいですか？</div>
                            </div>
                            <table class="tablesaw-list tablesaw table-bordered adminlist mt-1">
                                <thead>
                                <tr>
                                    <th class="title" style="text-align: center">
                                        組織名
                                    </th>
                                    <th class="title" style="text-align: center">
                                        地域名
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr ng-repeat="(id, receive) in receiveStamp">
                                    <td class="title" value="group_name"><% receive.group_name %></td>
                                    <td class="title" style="text-align: center" value="region_name"><% receive.region_name %></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" ng-click="sendGroup({{\App\Http\Utils\AppUtils::SEND_SEND}})">はい</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">いいえ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form class="formSendCln" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalSendCln" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">連携申請取消</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message">
                                <div class="text-left">以下の組織に連携申請を取り消します。</div>
                                <br/>
                                <div class="text-left">よろしいですか？</div>
                            </div>
                            <table class="tablesaw-list tablesaw table-bordered adminlist mt-1">
                                <thead>
                                <tr>
                                    <th class="title" style="text-align: center">
                                        組織名
                                    </th>
                                    <th class="title" style="text-align: center">
                                        地域名
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr ng-repeat="(id, receive) in receiveStamp">
                                    <td class="title" value="group_name"><% receive.group_name %></td>
                                    <td class="title" style="text-align: center" value="region_name"><% receive.region_name %></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" ng-click="sendGroup({{\App\Http\Utils\AppUtils::SEND_CLN}})">申請取消</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">いいえ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        function getAddMonthBefore(dt, add = 0) {
            var month = dt.getMonth();
            dt.setMonth(month - add);

            var y = dt.getFullYear();
            var m = ('00' + (dt.getMonth() + 1)).slice(-2);
            var d = ('00' + dt.getDate()).slice(-2);
            return (y + '-' + m + '-' + d);
        }

        if (appPacAdmin) {
            appPacAdmin.controller('ListController', function ($scope, $rootScope, $http) {
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.receiveStamp = [];
                $scope.state ="";
                $scope.receiveUpt = {id: "", state: "", key_available_period: ""};
                $scope.items = {!! json_encode($itemsSend) !!};
                if ({{ count($itemsSend) }}) {
                    $scope.cids = {!! json_encode($itemsSend->pluck('company_id')) !!};
                } else {
                    // $("#request_fromdate").val(getAddMonthBefore(new Date(),1));
                    // $("#request_todate").val(getAddMonthBefore(new Date()));
                }

                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                };

                $scope.toogleCheck = function (id) {
                    // item.checked = true;
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }
                };

                $scope.showFormSend = function () {
                    $rootScope.$emit("hideLoading");
                    const convenientStamp = $scope.items.data;
                    const ids = $scope.selected;
                    let chkFlg = true;
                    let msg = '';
                    $scope.receiveStamp = [];
                    if (Array.isArray(convenientStamp)) {
                        convenientStamp.forEach(item => {
                            if (Array.isArray(ids)) {
                                ids.forEach(id => {
                                    if (item.company_id == id) {
                                        if (item.state == 1) {
                                            chkFlg = false;
                                            msg = '未申請組織を選択してください。';
                                        }
                                        $scope.receiveStamp.push({
                                            'id': id,
                                            'group_name': item.group_name,
                                            'region_name': item.region_name,
                                        })
                                    }
                                })
                            }
                        })
                    }
                    if (!chkFlg) {
                        $(".message-list").append((showMessages([msg], 'warning', 5000)));
                        return;
                    }
                    $("#modalSend").modal();
                };

                $scope.showFormSendCln = function () {
                    $rootScope.$emit("hideLoading");
                    const convenientStamp = $scope.items.data;
                    const ids = $scope.selected;
                    let chkFlg = true;
                    let msg = '';
                    $scope.receiveStamp = [];
                    if (Array.isArray(convenientStamp)) {
                        convenientStamp.forEach(item => {
                            if (Array.isArray(ids)) {
                                ids.forEach(id => {
                                    if (item.company_id == id) {
                                        if (item.state != 1) {
                                            chkFlg = false;
                                            msg = '申請中組織を選択してください。';
                                        }
                                        $scope.receiveStamp.push({
                                            'id': id,
                                            'group_name': item.group_name,
                                            'region_name': item.region_name,
                                        })
                                    }
                                })
                            }
                        })
                    }
                    if (!chkFlg) {
                        $(".message-list").append((showMessages([msg], 'warning', 5000)));
                        return;
                    }
                    $("#modalSendCln").modal();
                };

                $scope.sendGroup = function ($kbn) {
                    if ($scope.selected.length) {
                        $rootScope.$emit("showLoading");
                        $http.put(link_update, {ids: $scope.selected, kbn: $kbn})
                            .then(function (event) {
                                $rootScope.$emit("hideLoading");
                                if (event.data.status == false) {
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 20000));
                                } else {
                                    location.reload();
                                    $(".message-list").append(showMessages(event.data.message, 'success', 20000));
                                }
                            });
                    }
                };
            });
        }

        $(document).ready(function () {
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });

            $('.select2').select2({
                placeholder: '',
                allowClear: true,
                "language": {
                    "noResults": function () {
                        return "データがありません";
                    }
                }
            });
        });
    </script>
@endpush

@push('styles_after')
    <style>
        .select2-container .select2-selection {
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
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
    </style>
@endpush
