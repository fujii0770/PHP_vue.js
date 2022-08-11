@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('circulars') }}";
        var link_ajax_csv = "{{ route('CsvCirculars') }}"
    </script>
    {{--PAC_5-2289 S--}}
    <script src="{{ asset('/js/monthpicker.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/css/monthpicker.css') }}">
    {{--PAC_5-2289 E--}}
@endpush

@section('content')

    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
            @csrf
            <div class="form-search form-horizontal">
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('search','検索文字列	',Request::get('search', ''),'text', false,
                        [ 'placeholder' =>'ファイル名、件名、申請者氏名（部分一致）' ]) !!}
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="row form-group">
                            <label class="col-md-4 control-label" >部署</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control  select2']) !!}
                            </div>
                        </div>
                    </div>
                    {{--PAC_5-1944 回覧一覧の検索条件変更 Start--}}
                    {{--<div class="col-lg-6">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-3 control-label">最終更新日</label>
                                <div class="col-md-4">
                                    <input type="Date" name="update_fromdate" value="{{ Request::get('update_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="update_fromdate">
                                </div>
                                <label for="name" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="update_todate" value="{{ Request::get('update_todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="update_todate">
                                </div>
                            </div>
                        </div>
                    </div>--}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-3 control-label">申請日</label>
                                <div class="col-md-4">
                                    <input type="date" name="create_fromdate" value="{{ Request::get('create_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="create_fromdate" ng-disabled="status==2">
                                </div>
                                <label for="name" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="create_todate" value="{{ Request::get('create_todate', '')}}" class="form-control" placeholder="yyyy/mm/dd" id="create_todate" ng-disabled="status==2">
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--PAC_5-1944 End--}}
                    <div class="col-lg-1"></div>
                </div>

                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                               <label for="name" class="col-md-4 control-label">状態</label>
                               <div class="col-md-8">
                                    {{--PAC_5-1944 回覧一覧の検索条件変更--}}
                                    {!! \App\Http\Utils\CommonUtils::buildSelectNoDefault(\App\Http\Utils\AppUtils::CIRCULAR_LABEL_STATUS, 'status', Request::get('status', 1),['class'=> 'form-control', 'ng-model'=> 'status']) !!}
                               </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-3 control-label">完了日時</label>
                                <div class="col-md-4">
                                    {{--PAC_5-1944 回覧一覧の検索条件変更--}}
                                    {!! \App\Http\Utils\CommonUtils::buildSelectNoDefault(\App\Http\Utils\AppUtils::CIRCULAR_COMPLETED_TIME, 'finished_month', Request::get('finished_month', ''), ['class'=> 'form-control','ng-disabled'=>"status!=2"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4"></div>
                    {{--PAC_5-1944 回覧一覧の検索条件変更 Start--}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-3 control-label"></label>
                                <div class="col-md-4">
                                    <input type="date" name="finished_fromdate" value="{{ Request::get('finished_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="finished_fromdate" ng-disabled="status!=2">
                                </div>
                                <label for="name" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="finished_todate" value="{{ Request::get('finished_todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="finished_todate" ng-disabled="status!=2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4"></div>
                    {{--PAC_5-1944 End--}}
                    <div class="col-lg-2 text-right">
                        <button class="btn btn-primary mb-1" type="submit" ng-click="search()"><i class="fas fa-search"></i> 検索</button>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

            @if ($company->template_flg && $company->template_search_flg)
            <div hidden>
                <div class="row">
                    <label for="name" class="col-md-2 control-label">テンプレート検索</label>
                </div>
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-3 control-label">最終更新日</label>
                                <div class="col-md-4">
                                    <input type="date" name="template_fromdate" value="{{ Request::get('template_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="template_fromdate">
                                </div>
                                <label for="name" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="template_todate" value="{{ Request::get('template_todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="template_todate">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('template_num','数値　',Request::get('template_num', ''),'text', false,
                        [ 'placeholder' =>'数値' ]) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('template_text','文字列　',Request::get('template_text', ''),'text', false,
                        [ 'placeholder' =>'文字列' ]) !!}
                    </div>
                </div>
            </div>
            @endif

                <div class="message message-list mt-3"></div>
                    @if($action == 'search')
                    <div class="card mt-3">
                        <div class="card-header">回覧文書一覧</div>
                            <div class="card-body">
                                <div class="table-head">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-lg-6 col-md-4 "  style="float:left" >
                                                    <span style="line-height: 27px">表示件数：</span>
                                                    <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                        <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                        <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                        <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                                    </select>
                                            </label>

                                            <div class="col-lg-6 col-md-2 text-right">
                                                @if($company->long_term_storage_flg && $status==2)
                                                <button type="button" class="btn btn-primary mb-1" ng-disabled="selected.length==0" ng-click="longTermClick()"> 長期保管</button>
                                                @endif
                                                <span class="dropdown">
                                                    <button type="button" class="btn btn-warning mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ng-disabled="selected.length==0" ><i class="fas fa-download"></i> ダウンロード予約</button>
                                                    <div class="dropdown-menu action dropdown-menu-right"
                                                         ng-hide="selected.length==0" style="width: 200px">
                                                        <span class="dropdown-menu-arrow"></span>
                                                        <span style="padding: 10px;font-size: 14px; width: 80%">
                                                            <input id="check_add_stamp_history" style="margin-right: 5px"
                                                                   class="mb-2 mt-2" type="checkbox"
                                                                   ng-model="check_add_stamp_history"/>
                                                            <label for="check_add_stamp_history">回覧履歴を付ける</label>
                                                        </span>
                                                        <button type="button" class="btn btn-warning btn-block"
                                                                style="width: 90%; margin: auto;"
                                                                ng-click="download()"><i class="fas fa-download"></i> ダウンロード予約</button>
                                                    </div>
                                                </span>
                                                @if ($company->circular_list_csv)
                                                <button type="button" class="btn btn-success mb-1" ng-disabled="{!! count($itemsCircular) !!} === 0" ng-click="downloadCsv()"><i class="fas fa-download" ></i> CSV出力</button>
                                                @endif
                                                @can(\App\Http\Utils\PermissionUtils::PERMISSION_CIRCULATION_LIST_DELETE)
                                                <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="delete()"><i class="fas fa-trash-alt"></i> 削除</button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="clear"></span>

                                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                                    <thead>
                                        <tr>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                <input type="checkbox" ng-model="selected_all" onClick="checkAll(this.checked)" ng-change="toogleCheckAll()" />
                                             </th>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist"  style="table-layout: fixed;width: 400px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('申請者', 'user_name', $orderBy, $orderDir) !!}
                                            </th>
                                            {{-- PAC_5-2213 start を非表示 現在の承認者 --}}
                                            @if($status !=2)
                                            <th scope="col" class="sort" style="table-layout: fixed;width: 400px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('現在の承認者', 'user_names', $orderBy, $orderDir) !!}
                                            </th>
                                            @endif
                                            {{-- PAC_5-2213 end  --}}
                                            <th scope="col" class="sort" style="width: 400px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('ファイル名', 'D.file_names', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" >
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('件名', 'D.title', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" style="width: 180px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('申請日', 'C.applied_date', $orderBy, $orderDir) !!}
                                            </th>
                                            {{-- PAC_5-2213 start 「完了日時」を表示 最終更新日を非表示 --}}
                                            @if($status ==2)
                                                <th scope="col" class="sort" style="width: 180px">
                                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('完了日時', 'C.completed_date', $orderBy, $orderDir) !!}
                                                </th>
                                            @else
                                            <th scope="col" class="sort" style="width: 180px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('最終更新日', 'C.final_updated_date', $orderBy, $orderDir) !!}
                                            </th>
                                            @endif
                                            {{-- PAC_5-2213 end  --}}
                                            <th scope="col" class="sort" style="width: 80px">
                                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'C.circular_status', $orderBy, $orderDir) !!}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($itemsCircular as $i => $item)
                                            <tr class="">
                                                <td class="title">
                                                    <input type="checkbox" ng-model="{{'selected_id'.$item->id}}" ng-value="{{ $item->id }}" ng-change="toogleCheck({{ $item->id }})"
                                                        name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                                </td>
                                                <td class="title">{{ $item->user_name }}&lt;{{ $item->user_email }}&gt;</td>
                                            {{--  PAC_5-2213   「回覧完了」 を非表示 現在の承認者 --}}
                                                @if($status !=2)
                                                        <td ng-bind-html="formatRoute('{{str_replace(',','<br/>',str_replace("'","\'",$item->user_names))}}')"></td>
                                                @endif
                                            {{--  PAC_5-2213 e --}}
                                                <td>{{ $item->file_names}}</td>
                                                <td>{{ $item->title }}</td>
                                                <td>{{ date("Y/m/d H:i:s", strtotime($item->applied_date)) }}</td>
                                                {{--  PAC_5-2213 S  「完了日時」を表示 最終更新日を非表示 --}}
                                                @if($status !=2)
                                                    <td>{{ date("Y/m/d H:i:s", strtotime($item->final_updated_date)) }}</td>
                                                @else
                                                    <td>{{ date("Y/m/d H:i:s", strtotime($item->completed_date)) }}</td>
                                                @endif
                                                {{--  PAC_5-2213 e--}}
                                                <td>{{ \App\Http\Utils\AppUtils::CIRCULAR_STATUS[$item->circular_status] }}
                                                {{ $item->result == 1 ? "[自動保管済]" : "" }}
                                                </td>
                                            </tr>
                                        @empty

                                        @endforelse
                                    </tbody>
                                </table>
                                @include('layouts.table_footer',['data' => $itemsCircular])
                            </div>
                            <% boxchecked %>
                            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
                        </div>
                    </div>
                    @endif
                <input type="hidden" name="statusHidden" value="">
                <input type="hidden" name="finishedDateHidden" value="">
                {{--PAC_5-1944 回覧一覧の検索条件変更 Start--}}
                <input type="hidden" name="finishedMonthHidden" id="finishedMonthHidden" ng-disabled="status!=2"/>
                {{--PAC_5-1944 End--}}
                <input type="hidden" name="action" value="search">
                <input type="hidden" name="searchStatus"  value="">
            </form>
    </div>

@endsection

@push('scripts')
    <script type="text/babel">
        /*function getAddMonthBefore(dt,add){
            add=add||0
            var month = dt.getMonth();
            dt.setMonth(month-add);

            var y = dt.getFullYear();
            var m = ('00' + (dt.getMonth()+1)).slice(-2);
            var d = ('00' + dt.getDate()).slice(-2);
            return (y + '-' + m + '-' + d);
        }*/

        function safeApply($scope, fun) {
          ($scope.$$phase || $scope.$root.$$phase) ? fun() : $scope.$apply(fun);
        }
        var initFinishedMonthHiddenFlg = true;
        if(appPacAdmin){
            var default_stamp_history_flg = {{ $company_limit->default_stamp_history_flg == 1 ? 1: 0 }};
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http, $sce){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.inputMaxLength = 46;
                $scope.items = {!! json_encode($itemsCircular) !!};
                $scope.status = {!! json_encode($status ?? '1') !!};
                $scope.searchStatus = {!! json_encode($status ?? '1') !!};
                $scope.check_add_stamp_history = false;
                $scope.indexes = [];
                $scope.long_term_storage_option_flg={!! json_encode($company->long_term_storage_option_flg ?? '0') !!};
                $scope.company_name = {!! json_encode($company->company_name) !!};
                $scope.long_term_folder_flg = {!! json_encode($company->long_term_folder_flg) !!};
                if({{ $company->long_term_storage_option_flg }}){
                    setLongTermIndex();
                }
                $scope.longTermdatas=[];
                $scope.is_sanitizing = {{ $company->sanitizing_flg }};// PAC_5-2853
                $scope.downloadCsvParam = {
                        search: $('input[name=search]').val(),
                        department: $('select[name=department]').val(),
                        orderDir: $('input[name=orderDir]').val(),
                        orderBy: $('input[name=orderBy]').val(),
                        status: $scope.status,
                        template_fromdate: $('input[name=template_fromdate]').val(),
                        template_todate: $('input[name=template_todate]').val(),
                        template_num: $('input[name=template_num]').val(),
                        template_text: $('input[name=template_text]').val(),
                        statusHidden:  $scope.status,
                        finishedDateHidden: $('input[name=finishedDateHidden]').val(),
                        searchStatus: $('input[name=searchStatus]').val()
                }
                if ($scope.status == 2) {
                    $scope.downloadCsvParam.finished_month = $scope.finishedMonthHidden;
                    $scope.downloadCsvParam.finished_fromdate = $('#finished_fromdate').val();
                    $scope.downloadCsvParam.finished_todate = $('#finished_todate').val();
                    $scope.downloadCsvParam.finishedMonthHidden = $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '';
                } else {
                    $scope.downloadCsvParam.create_fromdate = $('#create_fromdate').val();
                    $scope.downloadCsvParam.create_todate = $('#create_todate').val();
                }
                $scope.keyword = '';
                $scope.folder_id = null;
                $scope.isFirstSave = true;
                $scope.selected_all=false;
                @foreach($itemsCircular as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                document.adminForm.oldSubmit=document.adminForm.submit
                document.adminForm.submit=function () {
                    $rootScope.$emit("showLoading");
                    document.adminForm.statusHidden.value = $("#status").val() ? $("#status").val() : '';
                    document.adminForm.finishedDateHidden.value = $("#finishedDate").val() ? $("#finishedDate").val() : '';
                    // PAC_5-2147 回覧一覧から文書を削除しようとすると読み込み処理がタイムアウトするまで走る続けて削除できない問題対応
                    safeApply($rootScope, function () {document.adminForm.oldSubmit()});
                }
                document.adminForm.onsubmit=function () {
                    $rootScope.$emit("showLoading");
                    // PAC_5-2147 回覧一覧から文書を削除しようとすると読み込み処理がタイムアウトするまで走る続けて削除できない問題対応
                    safeApply($rootScope, function () {document.adminForm.oldSubmit()});
                }
                $('a.page-link').click(function (){
                    $rootScope.$emit("showLoading");
                    // PAC_5-2147 回覧一覧から文書を削除しようとすると読み込み処理がタイムアウトするまで走る続けて削除できない問題対応
                    safeApply($rootScope, function () {document.adminForm.oldSubmit()});
                })
                if({{ count($itemsCircular) }}){
                    $scope.cids = {!! json_encode($itemsCircular->pluck('id')) !!};
                }

                $rootScope.$on("addLongTermIndex",function (event,data){
                    $scope.indexes.push(data)
                });
                $rootScope.$on("removeLongTermIndex",function (event,data){
                    $scope.indexes.splice(data,1)
                });
                $rootScope.$on("intLongTermIndex",function (event,data){
                    setLongTermIndex();
                });
                $rootScope.$on("intKeyword",function (event,data){
                    $scope.keyword=''
                });
                $rootScope.$on("intFolderId",function (event,data){
                    $scope.folder_id = null
                });
                // // 文字解析
                $scope.formatRoute = function (route) {
                    return $sce.trustAsHtml(route);
                }
                $scope.search = function () {
                    document.adminForm.submit();
                };

                $scope.longTermClick = function () {
                    if ($scope.selected.length === 1) {
                        $http.get(link_ajax + '/getLongTermIndexValue?cid='+$scope.selected[0]+'&finishedMonthHidden='+($scope.finishedMonthHidden ? $scope.finishedMonthHidden : ''))
                            .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            $scope.isFirstSave = true;
                            if (event.data.status ) {
                                if(event.data.data && event.data.data.length){
                                    $scope.indexes = event.data.data;
                                    for (let v in $scope.indexes) {
                                        switch ($scope.indexes[v].data_type) {
                                            case 0:
                                                $scope.indexes[v].value=filterNum($scope.indexes[v].num_value.replace(/[^\d.]/g,''));
                                                break;
                                            case 1:
                                                $scope.indexes[v].value= $scope.indexes[v].string_value;
                                                break;
                                            case 2:
                                                $scope.indexes[v].value=new Date($scope.indexes[v].date_value)
                                                break;
                                        }
                                        for(let long in $scope.longTermdatas) {
                                            if ($scope.longTermdatas[long].id == $scope.indexes[v].longterm_index_id) {
                                                $scope.indexes[v].type = $scope.longTermdatas[long].vsInputType;
                                            }
                                        }
                                    }
                                }
                                if(event.data.index.length){
                                    if({{ $company->long_term_storage_option_flg }}){
                                        const longtermIndex = event.data.index;
                                        const fields = Object.keys(longtermIndex).map(function(e) {
                                            return longtermIndex[e]
                                        });
                                        for (let i=0;i<fields.length; i++) {
                                            fields[i].vsInputType = ["number", "text", "date"][fields[i].data_type];
                                        }
                                        $scope.longTermdatas = longtermIndex;
                                    }
                                }
                                if(event.data.keyword){
                                    $scope.keyword=event.data.keyword
                                }
                                if (event.data.long_term_document_id) $scope.isFirstSave = false;
                            } else {
                                setLongTermIndex();
                            }
                            $rootScope.$emit("showLongTermMocalConfirm",
                                {
                                    title: '確認',
                                    btnSuccess: 'はい',
                                    size: 'lg',
                                    inputEnable: 'true',
                                    keyword: $scope.keyword,
                                    message: '選択した文書の長期保管を行います。よろしいですか？',
                                    inputMaxLength: $scope.inputMaxLength,
                                    longTermdatas: $scope.longTermdatas,
                                    indexes: $scope.indexes,
                                    long_term_storage_option_flg: $scope.long_term_storage_option_flg,
                                    long_term_folder_flg: $scope.long_term_folder_flg,
                                    company_name: $scope.company_name,
                                    folder_id: $scope.folder_id,
                                    isFirstSave: $scope.isFirstSave,
                                    callSuccess: function (inputData) {
                                        let data = {
                                            cids: $scope.selected,
                                            keyword: inputData.keyword.val(),
                                            indexes: $scope.indexes,
                                            finishedMonthHidden: $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '',
                                            folder_id: inputData.folderId,
                                        }
                                        let flg = true;
                                        for (let v in $scope.indexes) {
                                            if($scope.indexes[v].data_type === 2 && $scope.indexes[v].value){
                                                $scope.indexes[v].value=new Date($scope.indexes[v].value).toLocaleDateString();
                                            }
                                            if ($scope.indexes[v].data_type === 1 && $scope.indexes[v].value) {
                                                if ($scope.indexes[v].value.length > 128) {

                                                    flg = false
                                                }
                                            }
                                            $scope.indexes[v][$scope.indexes[v].index_name] = $scope.indexes[v].value
                                        }
                                        if (flg) {
                                            $http.post(link_ajax + "/longterm", data).then(function (event) {
                                                $rootScope.$emit("hideLoading");
                                                if (event.data.status == false) {
                                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                                } else {
                                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                                }
                                            });
                                        }else {
                                            $(".message-list").append(showMessages(['長期保管インデックス文字列の長さは128ビット以上に設定できません。'], 'danger', 10000));
                                        }
                                    }
                                });
                        });

                    } else {
                        $rootScope.$emit('showModalAlertConfirm', {
                            title: '確認',
                            btnSuccess: 'はい',
                            size: 'lg',
                            inputEnable: 'true',
                            message: '選択した文書の長期保管を行います。よろしいですか？',
                            callSuccess: function (inputData) {
                                let data = {
                                    cids: $scope.selected,
                                    finishedMonthHidden: $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '',
                                }
                                $http.post(link_ajax + "/longterm", data).then(function (event) {
                                    $rootScope.$emit("hideLoading");
                                    if (event.data.status == false) {
                                        $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                    } else {
                                        $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                });
                            }
                        })
                    }

                }
                function filterNum(num){
                    num= (num+'').replace(/,/g,"");
                    num = num.split(".");
                    var arr = num[0].split("").reverse();
                    var res = [];
                    for (var i = 0, len = arr.length; i < len; i++) {
                        if (i % 3 === 0 && i !== 0) {
                            res.push(",");
                        }
                        res.push(arr[i]);
                    }
                    res.reverse();
                    if (num[1]) {

                        res = res.join("").concat("." + num[1]);
                    } else {
                        res = res.join("");
                    }
                    const regexp=/(?:\.0*|(\.\d+?)0+)$/
                    return res.replace(regexp,'$1')
                }
                $scope.download = function () {
                    if($scope.is_sanitizing){
                        $http.post(link_ajax + "/reserve", {
                            cids: $scope.selected,
                            finishedMonthHidden: $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '',
                            check_add_stamp_history: $scope.check_add_stamp_history
                        }).then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                    }else{
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '選択文書ダウンロード予約',
                                btnSuccess: 'はい',
                                size: 'lg',
                                inputEnable: 'true',
                                inputMaxLength: $scope.inputMaxLength,
                                callSuccess: function (inputData) {
                                    $http.post(link_ajax + "/reserve", {
                                        cids: $scope.selected,
                                        fileName: inputData.val(),
                                        finishedMonthHidden: $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '',
                                        check_add_stamp_history: $scope.check_add_stamp_history
                                    }).then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                                }
                            });
                    }
                };

                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                    $scope.inputMaxLength = 46;
                };

                $scope.toogleCheck = function (id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }

                    // For File Name Length
                    var item_data = $scope.items['data'].filter(function (item, index) {
                        if (item.id == $scope.selected[0]) return true;
                    });
                    if ($scope.selected.length == 1 && item_data[0].file_names.split(',').length == 1) {
                        // // .pdf, .docx, .xlsx
                        var pos = item_data[0].file_names.lastIndexOf('.');
                        $scope.inputMaxLength = 50 - item_data[0].file_names.substr(pos).length;
                    } else {
                        // .zip
                        $scope.inputMaxLength = 46;
                    }
                };

                $scope.delete = function () {
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '選択された回覧を削除します。よろしいですか？',
                            btnDanger: '削除',
                            callDanger: function () {
                                document.adminForm.action.value = 'delete';
                                document.adminForm.submit();
                            }
                        });

                };

                $scope.downloadCsv = function(){
                    var title = 'すべて回覧文書を出力します。<br />実行しますか？';
                    delete $scope.downloadCsvParam.selected_ids;
                    if ($scope.selected && $scope.selected.length > 0) {
                        $scope.downloadCsvParam.selected_ids = $scope.selected;
                        title = '選択された回覧文書を出力します。<br />実行しますか？';
                    }
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: title,
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_csv, $scope.downloadCsvParam)
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $(".message").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        $(".message").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                });
                            }
                        });
                };

                if(default_stamp_history_flg == 1){
                    $scope.check_add_stamp_history = true;
                }

                $('#finished_month').change(function() {
                    let now_date = new Date();
                    let search_date = new Date();

                    let current_year = now_date.getFullYear();
                    let current_month = now_date.getMonth(); // 0-11

                    // 完了年月プルダウン初期化
                    if ($('#finished_month').val() === '') {
                        $('#finished_month').val(0);
                    }

                    let minus_month = $('#finished_month').val();
                    // 検索年&月
                    search_date.setMonth(now_date.getMonth() - minus_month, 1);

                    let min_date = new Date(search_date.getFullYear(), search_date.getMonth(), 1);
                    let max_date = new Date(search_date.getFullYear(), search_date.getMonth() + 1, 0);
                    let finished_fromdate = $("#finished_fromdate").val();
                    let finished_todate = $("#finished_todate").val();
                    let fromdate = finished_fromdate == '' ? new Date(0) : new Date(finished_fromdate);
                    let todate = finished_todate == '' ? new Date(0) : new Date(finished_todate);

                    $("#finished_fromdate").attr({'min':getFullDate(min_date), 'max':getFullDate(max_date)});
                    $("#finished_todate").attr({'min':getFullDate(min_date), 'max':getFullDate(max_date)});
                    if (fromdate.getTime() > max_date.getTime() || todate.getTime() < min_date.getTime()) $("#finished_fromdate").val(getFullDate(min_date));
                    if (todate.getTime() > max_date.getTime() || todate.getTime() < min_date.getTime()) $("#finished_todate").val(getFullDate(max_date));

                    // テーブル用YYYYMM編集
                    if(minus_month == 0){
                        // circular
                        $scope.finishedMonthHidden = '';
                    }else{
                        // circularYYYYMM
                        let display_month = search_date.getMonth() + 1;
                        if(display_month > 9){
                            $scope.finishedMonthHidden = '' + search_date.getFullYear() + display_month;
                        }else{
                            $scope.finishedMonthHidden = '' + search_date.getFullYear() + '0' + display_month;
                        }
                    }
                    $('#finishedMonthHidden').val($scope.finishedMonthHidden);

                    if (initFinishedMonthHiddenFlg) {
                        if ($scope.status == 2) $scope.downloadCsvParam.finishedMonthHidden = $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '';
                    }
                    initFinishedMonthHiddenFlg = false;
                });
                $('#create_fromdate').change(function() {
                    let now_date = new Date();
                    if ($('#create_fromdate').val() === '') $('#create_fromdate').val(getFullDate(new Date(now_date.getFullYear(), now_date.getMonth(), 1)));
                    let create_fromdate = $('#create_fromdate').val();
                    let create_todate = $('#create_todate').val();
                    let min_date = create_fromdate == '' ? now_date : new Date(create_fromdate);
                    let max_date = new Date(min_date.getFullYear(), min_date.getMonth()+1, min_date.getDate() - 1);
                    let todate = create_todate == '' ? new Date(0) : new Date(create_todate);

                    $("#create_todate").attr({'min':getFullDate(min_date), 'max':getFullDate(max_date)});
                    if (todate.getTime() > max_date.getTime() || todate.getTime() < min_date.getTime()) $("#create_todate").val(getFullDate(max_date));
                });

                function getFullDate(targetDate, formate) {
                    var D, y, m, d;
                    formate = formate || 'y-m-d';
                    if (targetDate) {
                        D = new Date(targetDate);
                        y = D.getFullYear();
                        m = D.getMonth() + 1;
                        d = D.getDate();
                    } else {
                        y = fullYear;
                        m = month;
                        d = date;
                    }
                    m = m > 9 ? m : '0' + m;
                    d = d > 9 ? d : '0' + d;
                    return formate.replace('y', y).replace('m', m).replace('d', d);
                }
                function setLongTermIndex(){
                    const longtermIndex = {!! json_encode($longTermIndexName) !!};
                    const fields = Object.keys(longtermIndex).map(function(e) {
                        return longtermIndex[e]
                    }); // workaround: array の場合とそうでない場合の両方に対応するため
                    for (let i=0;i<fields.length;i++) {
                        fields[i].vsInputType = ["number", "text", "date"][fields[i].data_type];
                    }
                    $scope.longTermdatas = longtermIndex;
                    const newArr=JSON.parse(JSON.stringify(longtermIndex));
                    const indexTmp=["取引年月日","金額","取引先"]
                    const index1=[]
                    newArr.forEach(function (item){
                        if(indexTmp.indexOf(item.index_name) !== -1){
                            index1.push({longterm_index_id: item.id, value: "", type:item.data_type==2?'date':'text',index_name:'',data_type:item.data_type})
                        }
                    })
                    $scope.indexes=index1;
                }
            });
        }
        $(document).ready(function() {
            $('#create_fromdate').change();
            $('#finished_month').change();
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
     <script>
    document.oncontextmenu = function () {return false;}
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

        .dropdown:hover>.dropdown-menu {
            display: block;
        }
        .dropdown>.dropdown-toggle:active {
            pointer-events: none;
        }
    </style>
@endpush
