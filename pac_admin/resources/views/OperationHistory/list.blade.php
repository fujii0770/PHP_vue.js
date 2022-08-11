<div ng-controller="ListController">
<?php
$today = date("Y-m-d H:i:s");
$lastmonth = strtotime('-4 week ' . $today);
?>

    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-2">
                    <label for="select_month" class="control-label">対象期間<span style="color: red">*</span></label>
                    <input type="month" required="" name="select_month" ng-focus="timeFocus();"  value="{{ Request::get('select_month', '') }}" class="form-control" placeholder="対象期間" id="select_month">
                </div>
                <div class="col-lg-9"></div>
            </div>

            <div class="row">
                <div class="col-lg-1"></div>

                <div class="col-lg-2 form-group">
                    <label class="control-label" >{{ $user_title }}</label>
                    {!! \App\Http\Utils\CommonUtils::buildSelectHistory($listUser, 'user', Request::get('user', ''),'',
                    ['class'=> 'form-control select2'] ,$user_title) !!}
                </div>
                @if ($long_term_storage_flg && $type == 'user')
                    <div class="col-lg-2 form-group">
                        <label class="control-label" >監査用アカウント</label>
                        {!! \App\Http\Utils\CommonUtils::buildSelectHistory($listAuditUser, 'audit_user', Request::get('audit_user', ''),'',
                        ['class'=> 'form-control select2'] ,'監査用アカウント') !!}
                    </div>
                @endif

                <!-- 利用者の場合、接続先を表示する PAC_5-163 BEGIN -->
                @if ($type == 'user' || $type == 'admin')
                    <div class="col-lg-2 form-group">
                        <label class="control-label" >操作</label>
                        {!! \App\Http\Utils\CommonUtils::buildSelect($arrOperation_info, 'type', Request::get('type', ''),'',
                        ['class'=> 'form-control select2']) !!}
                    </div>
                @else
                    <div class="col-lg-2 form-group">
                        <label class="control-label" >操作画面</label>
                        {!! \App\Http\Utils\CommonUtils::buildSelect($arrDisplay, 'screen', Request::get('screen', ''),'',
                        ['class'=> 'form-control select2']) !!}
                    </div>
                    <div class="col-lg-2 form-group">
                        <label class="control-label" >操作カテゴリ</label>
                        {!! \App\Http\Utils\CommonUtils::buildSelect($arrOperation_info, 'type', Request::get('type', ''),'',
                        ['class'=> 'form-control select2']) !!}
                    </div>

                    <div class="col-lg-2 form-group">
                        <label class="control-label" >結果</label>
                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\OperationsHistoryUtils::STATUS, 'status', Request::get('status', ''),'',
                            ['class'=> 'form-control select2']) !!}
                    </div>
                @endif
                <!-- PAC_5-163 END -->
                <div class="col-lg-3 text-right text-left-lg padding-top-20">
                    <button class="btn btn-primary mb-1" id="search"><i class="fas fa-search" ></i> 検索</button>
                    <button type="button" class="btn btn-warning mb-1" ng-click="downloadCsv()"><i class="fas fa-download" ></i> CSV出力</button>
                    <input type="hidden" class="action" name="action" value="search" />
                </div>
            </div>
        </div>

        <div class="message message-list mt-3"></div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <label class="col-lg-6 col-md-4 "  style="float:left" >
                            <span style="line-height: 27px">表示件数：</span>
                            <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                            </select>
                    </label>
                </div>

                <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>
                           <!-- 利用者の場合、接続先を表示する PAC_5-163 BEGIN -->
                            @if ($type == 'user' || $type == 'admin' )
                                <th class="title sort" scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('日時', 'time', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'user', $orderBy, $orderDir) !!}
                                </th>
                                @if ($type == 'admin')
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'adminDepartment', $orderBy, $orderDir) !!}
                                    </th>
                                @endif
                                @if ($type == 'user')
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'userDepartment', $orderBy, $orderDir) !!}
                                    </th>
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('役職', 'position', $orderBy, $orderDir) !!}
                                    </th>
                                    {{--PAC_5-2098 Start--}}
                                    @if(isset($multiple_department_position_flg) && $multiple_department_position_flg === 1)
                                    {{--PAC_5-1599 追加部署と役職 Start--}}
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('部署2', 'department_1', $orderBy, $orderDir) !!}
                                    </th>
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('役職2', 'position_1', $orderBy, $orderDir) !!}
                                    </th>
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('部署3', 'department_2', $orderBy, $orderDir) !!}
                                    </th>
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('役職3', 'position_2', $orderBy, $orderDir) !!}
                                    </th>
                                    {{--PAC_5-1599 End--}}
                                    @endif
                                    {{--PAC_5-2098 End--}}
                                @endif
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('操作', 'type', $orderBy, $orderDir) !!}
                                </th>
                                @if ($type == 'user')
                                    <th scope="col"  class="sort">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('接続元', 'ipAddress', $orderBy, $orderDir) !!}
                                    </th>
                                @endif
                            @else
                                <th class="title sort" scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('操作日時', 'time', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn($user_title, 'user', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('操作画面', 'screen', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('操作カテゴリ', 'type', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('結果', 'status', $orderBy, $orderDir) !!}
                                </th>
                            @endif
                           <!-- PAC_5-163 END -->
                        </tr>
                    </thead>
                    <tbody>
                        @if ($arrHistory)
                            @foreach ($arrHistory as $i => $item)
                                <tr name="history-detail" id="history-detail-{{ $item->id }}" ng-click="showDetail({{ $item->id }},'{{$item->detail_info}}')">
                                    <td class="title">{{ $item->create_at }}</td>

                                    <!-- 利用者の場合、接続先を表示する PAC_5-163 BEGIN -->
                                    @if ($type == 'user' || $type == 'admin' )
                                        <td class="title">{{ $item->email }}</td>
                                        <td class="title">{{ $item->user_name }}</td>
                                        <!-- 部署の情報を取得する-->
                                        @if ($type == 'user')
                                            <td class="title">
                                                @isset($listDepartmentDetail[$item->department_id]['text'])
                                                    {{ $listDepartmentDetail[$item->department_id]['text']  }}
                                                @endisset
                                            </td>
                                        @elseif($type == 'admin')
                                            <td class="title">
                                                @isset($item->department_name)
                                                    {{ $item->department_name }}
                                                @endisset
                                            </td>
                                        @endif

                                        @if ($type == 'user')
                                            <td class="title">{{ $item->position_name }}</td>
                                        @endif
                                        {{--PAC_5-1599 追加部署と役職 Start--}}
                                        {{--PAC_5-2098 Start--}}
                                        @if ($type == 'user' && isset($multiple_department_position_flg) && $multiple_department_position_flg === 1)
                                            <td class="title">
                                                @isset($listDepartmentDetail[$item->mst_department_id_1]['text'])
                                                    {{ $listDepartmentDetail[$item->mst_department_id_1]['text']  }}
                                                @endisset
                                            </td>
                                            <td class="title">{{ $item->position_name_1 }}</td>
                                            <td class="title">
                                                @isset($listDepartmentDetail[$item->mst_department_id_2]['text'])
                                                    {{ $listDepartmentDetail[$item->mst_department_id_2]['text']  }}
                                                @endisset
                                            </td>
                                            <td class="title">{{ $item->position_name_2 }}</td>
                                        @endif
                                        {{--PAC_5-1599 End--}}
                                        <td class="title">{{ array_key_exists($item->mst_operation_id,$arrOperation_info) ? $arrOperation_info[$item->mst_operation_id] : ''}}</td>
                                        @if ($type == 'user')
                                            <td class="title">{{ $item->ip_address }}</td>
                                        @endif
                                    @else
                                        <td class="title">{{ $item->user_name }} &lt;{{ $item->email }}&gt;</td>
                                        <td class="title">{{ $arrDisplay[$item->mst_display_id] }}</td>
                                        <td class="title">{{ array_key_exists($item->mst_operation_id,$arrOperation_info) ? $arrOperation_info[$item->mst_operation_id] : ''}}</td>
                                        <td class="title">{{ \App\Http\Utils\OperationsHistoryUtils::STATUS[$item->result] }}</td>
                                    @endif
                                    <!-- PAC_5-163 END -->
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                @if ($arrHistory)
                    @include('layouts.table_footer',['data' => $arrHistory])
                @endif
            </div>
        </div>
        <div class="card mt-3" ng-if="showDetailId">
            <div class="card-header">詳細</div>
            <div class="card-body">
                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                    <thead>
                    <tr>
                        <th scope="col" class="w-25">項目</th>
                        <th scope="col" class="w-75">内容</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="">
                        <td class="w-25">詳細内容</td>
                        <td class="w-75" ><div ng-bind-html="showDetailMessage"></div></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
        <input type="hidden" name="page" value="1">
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){

                $scope.showDetailId = 0;
                $scope.showDetailMessage = "";

                document.adminForm.oldSubmit=document.adminForm.submit
                document.adminForm.submit=function () {
                    $rootScope.$emit("showLoading");
                    $rootScope.$apply()
                    document.adminForm.oldSubmit()
                 }
                document.adminForm.onsubmit=function () {
                    $rootScope.$emit("showLoading");
                    $rootScope.$apply()
                }
                $('a.page-link').click(function (){
                    $rootScope.$emit("showLoading");
                    $rootScope.$apply()
                })
                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditHistory",{id:id});
                };
                $scope.timeFocus = function () {
                    if(GetIEVersion() > 0 ){
                        if ($('.monthpicker')) {
                            $(".monthpicker").remove();
                        }
                        var y;
                        if ($('#select_month').val()) {
                            var _time = $('#select_month').val().split('-');
                            y = _time[0];
                        }
                        $('#select_month').monthpicker({
                            selectYears: y ? y : '',
                            onMonthSelect: function (m, y) {
                                m = (parseInt(m) + 1);
                                if (m.toString().length == 1) {
                                    m = '0' + m;
                                }
                                //$('#select_month').val(y + '-' + m);
                                //if($('#select_month').val()){
                                //    document.getElementById("search").removeAttribute("disabled");
                                //}else{
                                //    document.getElementById("search").setAttribute("disabled", true);
                                //}
                            }
                        });
                    }

                }


                $scope.showDetail = function(id,info){

                    let historylist = document.getElementsByName('history-detail');
                    for(var i=0;i<historylist.length;i++){
                        historylist[i].style.backgroundColor = "";
                    }
                    let history = document.getElementById('history-detail-'+id);
                    history.style.backgroundColor = 'rgba(0,0,0,.05)';

                    $scope.showDetailId = id;
                    $scope.showDetailMessage = info;

                };

                $scope.downloadCsv = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'操作履歴データを出力します。実行しますか？',
                            btnSuccess:'はい',
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_request,
                                                {
                                                    company_id: $scope.company_id,
                                                    limit: "{{$limit}}",
                                                    user: document.getElementById("user").value,
                                                    @if ($long_term_storage_flg && $type == 'user')
                                                    audit_user: document.getElementById("audit_user").value,
                                                    @endif
                                                    orderBy: "{{$orderBy}}",
                                                    orderDir: "{{ Request::get('orderDir','DESC') }}",
                                                    screen: document.getElementById("type").value,
                                                    status: document.getElementById("status") ? document.getElementById("status").value : null,
                                                    select_month: document.getElementById("select_month").value,
                                                    type: "{{$type}}",
                                                }
                                            )
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
            });

        }else{
            throw new Error("Something error init Angular.");
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
            $('#user').change(function (){
                if($(this).val()!=''){
                    $('#audit_user').val(null).trigger("change")
                }
            });
            $('#audit_user').change(function (){
                if($(this).val()!=''){
                    $('#user').val(null).trigger("change")
                }
            });
            searhSwitch();
        });


        document.getElementById("select_month").onchange = function() {
            searhSwitch();
        };
        //function searhSwitch(){
        //    const select_month  = document.getElementById("select_month")
        //    if($('#select_month').val()){
        //        document.getElementById("search").removeAttribute("disabled");
        //    }else{
        //        document.getElementById("search").setAttribute("disabled", true);
        //    }
        //};
        //PAC_5-2434 操作履歴の対象期間にデフォルトの値を設定する対応
        window.onload = function () {
                    var select_month = new Date();
                    select_month.setDate(select_month.getDate());
                    var yyyy = select_month.getFullYear();
                    var mm = ("0" + (select_month.getMonth() + 1)).slice(-2);
                    var dd = ("0" + select_month.getDate()).slice(-2);
                    if(!document.getElementById("select_month").value) {
                        document.getElementById("select_month").value = yyyy + '-' + mm;
                    }
                };
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
