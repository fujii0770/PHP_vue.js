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
                                {!! \App\Http\Utils\CommonUtils::showFormField('group_name','組織名	',Request::get('group_name', ''),'text', false,[ 'col' => 4 ]) !!}
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
                            <div class="col-md-12">
                                {!! \App\Http\Utils\CommonUtils::showFormField('request_user_name','申請者	',Request::get('request_user_name', ''),'text', false,[ 'col' => 4 ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 form-group">
                        <div class="row">
                            <label class="col-md-6 control-label">承認状態</label>
                            <div class="col-md-6">
                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::RECEIVE_CODE , 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <label for="update_fromdate" class="col-md-2 control-label">申請日</label>
                                <div class="col-md-4">
                                    <input type="date" name="request_fromdate" value="{{ Request::get('request_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="request_fromdate">
                                </div>
                                <label for="update_todate" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="request_todate" value="{{ Request::get('request_todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="request_todate">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <label for="update_fromdate" class="col-md-2 control-label">更新日</label>
                                <div class="col-md-4">
                                    <input type="date" name="update_fromdate" value="{{ Request::get('update_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="update_fromdate">
                                </div>
                                <label for="update_todate" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="update_todate" value="{{ Request::get('update_todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="update_todate">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <label for="update_fromdate" class="col-md-3 control-label">承認有効期限</label>
                                <div class="col-md-4">
                                    <input type="date" name="available_fromdate" value="{{ Request::get('available_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="available_fromdate">
                                </div>
                                <label for="update_todate" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="available_todate" value="{{ Request::get('available_todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="available_todate">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="text-right">
                    <button class="btn btn-primary mb-1"><i class="fas fa-search"></i> 検索</button>
                    @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_RECEIVE_UPDATE])
                      <button type="button" class="btn btn-success mb-1" ng-disabled="selected.length==0" ng-click="showFormReceive()"><i class="fas fa-plus-circle"></i> 承認</button>
                      <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="showFormReceiveCln()"><i class="fas fa-times-circle"></i> 承認解除</button>
                    @endcanany
                </div>

                <div class="message message-list mt-3"></div>
                    <div class="card mt-3">
                        <div class="card-header">連携承認一覧</div>
                            <div class="card-body">
                                <div class="table-head">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-lg-6 col-md-4 "  style="float:left" >
                                                <label class="d-flex" style="float:left"><span style="line-height: 27px">表示件数：</span>
                                                    <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                        <option {{Request::get('limit') == '10' ? 'selected': ''}} value="10">10</option>
                                                        <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                        <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                    </select>
                                                </label>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <span class="clear"></span>

                                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                                    <thead>
                                        <tr>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" />
                                             </th>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('組織名', 'rs.group_name', $orderBy, $orderDir) !!}
                                            </th>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('地域名', 'rs.region_name', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('申請者', 'cc.request_user_id', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" style="width: 150px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('申請日', 'cc.request_at', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" style="width: 150px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('更新日', 'cc.update_at', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" style="width: 150px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('承認有効期限', 'cc.approval_period', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" style="width: 150px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('承認状態', 'state', $orderBy, $orderDir) !!}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itemsReceive as $i => $item)
                                            <tr class="">
                                                <td class="title">
                                                    <input type="checkbox" value="{{ $item['company_id'] }}" ng-click="toogleCheck({{ $item['company_id'] }})"
                                                        name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                                </td>
                                                <td class="title" ng-click="editRecord({{ $item['company_id'] }})">{{ $item['group_name'] }}</td>
                                                <td ng-click="editRecord({{ $item['company_id'] }})">{{ $item['region_name'] }}</td>
                                                <td ng-click="editRecord({{ $item['company_id'] }})">{{ $item['request_user_name'] }}</td>
                                                <td ng-click="editRecord({{ $item['company_id'] }})">{{ date("Y/m/d", strtotime($item['request_at'])) }}</td>
                                                <td ng-click="editRecord({{ $item['company_id'] }})">{{ date("Y/m/d", strtotime($item['update_at'])) }}</td>
                                                <td ng-click="editRecord({{ $item['company_id'] }})">{{ $item['state'] == 2 && $item['approval_period'] == "" ? '無期限' :
                                                   ($item['approval_period'] == "" ? '':date("Y/m/d", strtotime($item['approval_period']))) }}</td>
                                                <td ng-click="editRecord({{ $item['company_id'] }})">{{ \App\Http\Utils\AppUtils::RECEIVE_CODE[$item['state']] }}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                                @include('layouts.table_footer',['data' => $itemsReceive])
                            </div>
                            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
                        </div>
                    </div>
                <input type="hidden" name="action" value="search">
            </form>

        <form class="formReceive" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalReceive" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">連携承認</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message"><div class="text-left">以下の組織を承認します。</div><br /><div class="text-left">よろしいですか？</div></div>
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
                                    <tr ng-repeat="(id, receive) in receiveStamp" >
                                        <td class="title" style="text-align: center" value="group_name"><% receive.group_name %></td>
                                        <td class="title" style="text-align: center" value="region_name"><% receive.region_name %></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row form-group" style="margin-top: 20px">
                                <div class="col-md-5 col-sm-6 col-12">
                                </div>
                                <label for="name" class="col-sm-3 control-label">承認有効期限</label>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <input type="date" class="form-control" id="approval_period_all" ng-model="approval_period_all" placeholder="yyyy/MM/dd">
                                </div>
                                <label for="name" class="col-sm-1 control-label">まで</label>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" ng-click="receiveGroup({{\App\Http\Utils\AppUtils::RECEIVE_APP}})">連携承認</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">いいえ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form class="formReceiveCln" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalReceiveCln" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">連携解除承認</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message"><div class="text-left">以下の組織の承認を解除します。</div><br /><div class="text-left">よろしいですか？</div></div>
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
                                <tr ng-repeat="(id, receive) in receiveStamp" >
                                    <td class="title" style="text-align: center" value="group_name"><% receive.group_name %></td>
                                    <td class="title" style="text-align: center" value="region_name"><% receive.region_name %></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" ng-click="receiveGroup({{\App\Http\Utils\AppUtils::RECEIVE_CLN}})">承認解除</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">いいえ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form class="receiveUpdate" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalReceiveUpt" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">公開設定</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message"></div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">承認状態</label>
                                <div class="col-md-6 col-sm-6 col-12">
                                    <label class="control-label">承認する</label>
                                    <input type="checkbox" ng-model="receiveUpt.state" id="receive_flg" ng-true-value="2" ng-false-value="1"/>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">承認有効期限</label>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <input type="date" class="form-control" id="approval_period" ng-model="receiveUpt.approval_period" placeholder="yyyy/MM/dd">
                                </div>
                                <label class="control-label">まで</label>
                            </div>
                            <div class="modal-footer">
                                @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_RECEIVE_UPDATE])
                                    <button type="button" class="btn btn-success" ng-click="receiveGroup({{\App\Http\Utils\AppUtils::RECEIVE_UPD}})">更新</button>
                                @endcanany
                                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
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
        function getAddMonthBefore(dt,add = 0){
            var month = dt.getMonth();
            dt.setMonth(month-add);

            var y = dt.getFullYear();
            var m = ('00' + (dt.getMonth()+1)).slice(-2);
            var d = ('00' + dt.getDate()).slice(-2);
            return (y + '-' + m + '-' + d);
        }

        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.receiveStamp = [];
                $scope.approval_period_default = new Date({!! json_encode($approval_period_default) !!});
                $scope.receiveUpt = {id: "", state: "", approval_period: ""};
                $scope.items = {!! json_encode($itemsReceive) !!};
                if({{ count($itemsReceive) }}){
                    $scope.cids = {!! json_encode($itemsReceive->pluck('company_id')) !!};
                }

                 $scope.toogleCheckAll = function(){
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                 };

                 $scope.toogleCheck = function(id){
                     // item.checked = true;
                     var idx = $scope.selected.indexOf(id);
                     if (idx > -1) {
                         $scope.selected.splice(idx, 1);
                     }else{
                         $scope.selected.push(id);
                     }
                 };

                $scope.showFormReceive = function(){
                    $rootScope.$emit("hideLoading");
                    $scope.approval_period_all = null;
                    const convenientStamp =  $scope.items.data;
                    const ids = $scope.selected;
                    let chkFlg = true;
                    let msg = '';
                    $scope.receiveStamp = [];
                    if(Array.isArray(convenientStamp)){
                        convenientStamp.forEach(item=>{
                            if(Array.isArray(ids)){
                                ids.forEach(id=>{
                                    if(item.company_id == id){
                                        if(item.state == 2) {
                                            chkFlg = false;
                                            msg = '未承認組織を選択してください。';
                                        }
                                        $scope.receiveStamp.push({
                                            'id':id,
                                            'group_name':item.group_name,
                                            'region_name':item.region_name,
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
                    $("#modalReceive").modal();
                };

                $scope.showFormReceiveCln = function(){
                    $rootScope.$emit("hideLoading");
                    const convenientStamp =  $scope.items.data;
                    const ids = $scope.selected;
                    let chkFlg = true;
                    let msg = '';
                    $scope.receiveStamp = [];
                    if(Array.isArray(convenientStamp)){
                        convenientStamp.forEach(item=>{
                            if(Array.isArray(ids)){
                                ids.forEach(id=>{
                                    if(item.company_id == id){
                                        if(item.state == 1) {
                                            chkFlg = false;
                                            msg = '承認組織を選択してください。';
                                        }
                                        $scope.receiveStamp.push({
                                            'id':id,
                                            'group_name':item.group_name,
                                            'region_name':item.region_name,
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
                    $("#modalReceiveCln").modal();
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("hideLoading");
                    const convenientStamp =  $scope.items.data;
                    if(Array.isArray(convenientStamp)){
                        convenientStamp.forEach(item=>{
                          if(item.company_id == id){
                              $scope.receiveUpt = {id: item.company_id, state: item.state, approval_period: new Date(item.state == 1 && item.approval_period =='' ? $scope.approval_period_default : item.approval_period)};
                          }
                        })
                    }
                    $("#modalReceiveUpt").modal();
                };

                $scope.receiveGroup = function($kbn){
                    if($scope.selected.length && $kbn != {{\App\Http\Utils\AppUtils::RECEIVE_UPD}}){
                        $rootScope.$emit("showLoading");
                        $http.put(link_update, {ids: $scope.selected,　approval_period_all: $("#approval_period_all").val(), kbn: $kbn})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 20000));
                                }else{
                                    location.reload();
                                    $(".message-list").append(showMessages(event.data.message, 'success', 20000));
                                }
                            });
                    }else if($kbn == {{\App\Http\Utils\AppUtils::RECEIVE_UPD}}){
                        $rootScope.$emit("showLoading");
                        $http.put(link_update, {id: $scope.receiveUpt.id, state: $scope.receiveUpt.state, approval_period: $("#approval_period").val(), kbn: $kbn})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 20000));
                                }else{
                                    location.reload();
                                    $(".message-list").append(showMessages(event.data.message, 'success', 20000));
                                }
                            });
                    }
                };
            });
        }

        $(document).ready(function() {
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });

            $('.select2').select2({
                placeholder: '',
                allowClear: true,
                "language": {
                    "noResults": function(){
                        return "データがありません";
                    }
                }
            });
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
