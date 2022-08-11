@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('circulars-saved') }}";
    </script>
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
                    <div class="col-lg-6">
                        <div class="row form-group">
                            <label for="document_id" class="col-md-3 control-label">文書ID</label>
                                <div class="col-md-5">
                                    <input name="document_id" value="{{ Request::get('document_id', '') }}" class="form-control" placeholder="文書ID" id="document_id">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="row form-group">
                            <label class="col-md-4 control-label" >部署</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control select2']) !!}
                            </div>
                        </div>
                    </div>                
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="row">
                               <label for="update_fromdate" class="col-md-3 control-label">保存日</label>
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
                    <div class="col-lg-1"></div>
                </div>
                
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                               <label for="name" class="col-md-4 control-label">状態</label>
                               <div class="col-md-8">
                                    {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CIRCULAR_SAVED_STATUS, 'status', Request::get('status', ''),'',['class'=> 'form-control']) !!}
                               </div>
                            </div>
                        </div>
                    </div>                
                    <div class="col-lg-4"></div>
                    <div class="col-lg-2 text-right">
                        <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                    </div>
                    <div class="col-lg-1"></div>
                </div>
            
                <div class="message message-list mt-3"></div>
                @if($itemsCircular->count())
                    <div class="card mt-3">
                        <div class="card-header">保存文書一覧</div>
                            <div class="card-body">
                                <div class="table-head">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-lg-6 col-md-4 "  style="float:left" >
                                                    <span style="line-height: 27px">表示件数：</span>
                                                    <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                        <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                        <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
    {{--                                                    <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>--}}
                                                    </select>
                                            </label>

                                            <div class="col-lg-6 col-md-2 text-right">
                                                <button type="button" class="btn btn-warning mb-1" ng-disabled="selected.length==0" ng-click="download()"><i class="fas fa-download"></i> ダウンロード予約</button>
                                                @can(\App\Http\Utils\PermissionUtils::PERMISSION_CIRCULARS_SAVED_DELETE)
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
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('文書ID', 'C.id', $orderBy, $orderDir) !!}
                                            </th>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('申請者', 'user_name', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('ファイル名', 'D.file_names', $orderBy, $orderDir) !!}
                                            </th>                                     
                                            <th scope="col" class="sort" style="width: 200px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('件名', 'D.title', $orderBy, $orderDir) !!}
                                            </th>
    {{--                                        <th scope="col" class="sort" style="width: 180px">--}}
    {{--                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('申請日', 'C.applied_date', $orderBy, $orderDir) !!}--}}
    {{--                                        </th>--}}
                                            <th scope="col" class="sort" style="width: 180px">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('最終更新日', 'C.final_updated_date', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort" style="width: 70px">
                                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'C.circular_status', $orderBy, $orderDir) !!}
                                            </th>                                
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itemsCircular as $i => $item)
                                            <tr class="">
                                                <td class="title"> 
                                                    <input type="checkbox" ng-model="{{'selected_id'.$item->id}}"  ng-value="{{ $item->id }}" ng-change="toogleCheck({{ $item->id }})"
                                                        name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                                </td>
                                                <td class="title" style="text-align: center">{{ $item->id }}</td>
                                                <td class="title">{{ $item->user_name }}&lt;{{ $item->user_email }}&gt;</td>
                                                <td>{{ $item->file_names }}</td>
                                                <td>{{ $item->title }}</td>
    {{--                                            <td>{{ date("Y/m/d H:i:s", strtotime($item->applied_date)) }}</td>--}}
                                            <td>{{ date("Y/m/d H:i:s", strtotime($item->final_updated_date)) }}</td>
                                            @if($item->circular_status == 0)
                                                <td class="circular-keep">{{ \App\Http\Utils\AppUtils::CIRCULAR_SAVED_STATUS[$item->circular_status] }}</td>
                                            @endif
                                            @if($item->circular_status == 5)
                                                <td class="circular-pullback">{{ \App\Http\Utils\AppUtils::CIRCULAR_SAVED_STATUS[$item->circular_status] }}</td>
                                            @endif
                                            @if($item->circular_status == 9)
                                                <td class="circular-delete">{{ \App\Http\Utils\AppUtils::CIRCULAR_SAVED_STATUS[$item->circular_status] }}</td>
                                            @endif
                                            </tr>
                                        @endforeach

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
                <input type="hidden" name="action" value="search">
            </form>
    </div>
   
@endsection

@push('scripts')
    <script>
        function getAddMonthBefore(dt,add){
            add=add||0
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
                $scope.items = {!! json_encode($itemsCircular) !!};
                if({{ count($itemsCircular) }}){
                    $scope.cids = {!! json_encode($itemsCircular->pluck('id')) !!};
                } else{
                    $("#update_fromdate").val(getAddMonthBefore(new Date(),1));
                    $("#update_todate").val(getAddMonthBefore(new Date()));
                }
                $scope.is_sanitizing = {{ $company->sanitizing_flg }};// PAC_5-2853
                $scope.selected_all=false;
                @foreach($itemsCircular as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                $scope.download = function(){
                    if($scope.is_sanitizing){
                        $http.post(link_ajax + "/reserve", {
                            cids: $scope.selected
                        })
                            .then(function (event) {
                                $rootScope.$emit("hideLoading");
                                if (event.data.status == false) {
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                } else {
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));

                                }
                            });
                    }else {
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
                                        fileName: inputData.val()
                                    })
                                        .then(function (event) {
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

                 $scope.toogleCheckAll = function(){ 
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                    $scope.inputMaxLength = 46;
                 };

                 $scope.toogleCheck = function(id){ 
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    }else{
                        $scope.selected.push(id);
                    }

                    // For File Name Length
                    var item_data = $scope.items['data'].filter(function(item, index){
                        if (item.id == $scope.selected[0]) return true;
                    });
                    if($scope.selected.length == 1 && item_data[0].file_names.split(',').length == 1){
                        // // .pdf, .docx, .xlsx
                        var pos = item_data[0].file_names.lastIndexOf('.');
                        $scope.inputMaxLength = 50 - item_data[0].file_names.substr(pos).length;
                    }else{
                        // .zip
                        $scope.inputMaxLength = 46;
                    }
                 };

                 $scope.delete = function(){
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択された回覧を削除します。よろしいですか？', 
                        btnDanger:'削除',
                        callDanger: function(){
                            document.adminForm.action.value = 'delete';
                            document.adminForm.submit();
                        }
                    });
                    
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
            $('[data-toggle="tooltip"]').tooltip();
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

        .tooltip-inner{
            max-width: 100%;
        }
    </style>
@endpush