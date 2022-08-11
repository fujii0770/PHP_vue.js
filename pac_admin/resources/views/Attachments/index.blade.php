@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('attachments') }}";
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
                        <div class="form-group">
                            <div class="row">
                               <label for="name" class="col-md-3 control-label">アップロード日</label>
                               <div class="col-md-4">
                                     <input type="date" name="create_fromdate" value="{{ Request::get('create_fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="create_fromdate">
                               </div>
                               <label for="name" class="col-md-1 control-label">~</label>
                               <div class="col-md-4">
                                    <input type="date" name="create_todate" value="{{ Request::get('create_todate', '')}}" class="form-control" placeholder="yyyy/mm/dd" id="create_todate">
                               </div>
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
                                {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control  select2']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1"></div>
                </div>
                
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-2 text-right">
                        <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                    </div>
                    <div class="col-lg-1"></div>
                </div>
            
                <div class="message message-list mt-3"></div>
                <div class="card mt-3">
                    <div class="card-header">添付ファイル一覧</div>
                        <div class="card-body">
                            <div class="table-head">
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-lg-6 col-md-2 control-label" ></label>
                                        <div class="col-lg-6 col-md-2 text-right">
                                            <button type="button" class="btn btn-warning mb-1" ng-disabled="selected.length==0" ng-click="download()"><i class="fas fa-download"></i> ダウンロード</button>
                                            @can(\App\Http\Utils\PermissionUtils::PERMISSION_ATTACHMENTS_SETTING_DELETE)
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
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('ユーザー名', 'A.name', $orderBy, $orderDir) !!}
                                        </th>
                                        <th scope="col" class="sort">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('ファイル名', 'A.file_name', $orderBy, $orderDir) !!}
                                        </th>                                     
                                        <th scope="col" class="sort" style="width: 200px">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('件名', 'A.title', $orderBy, $orderDir) !!}
                                        </th>
                                        <th scope="col" class="sort" style="width: 180px">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('アップロード日', 'A.create_at', $orderBy, $orderDir) !!}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemAttachments as $i => $item)
                                        <tr class="">
                                            <td class="title" >
                                                <input type="checkbox" ng-model="{{'selected_id'.$item->id}}" ng-value="{{ $item->id }}" ng-change="toogleCheck({{ $item->id }})"
                                                    name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                            </td>
                                            <td class="title">{{ $item->name }}&lt;{{ $item->create_user }}&gt;</td>
                                            <td>{{ $item->file_name }}</td>
                                            <td>{{ $item->title }}</td>
                                            <td>{{ date("Y/m/d H:i:s", strtotime($item->create_at)) }}</td>
                                        </tr>
                                    @endforeach
                                    
                                </tbody>
                            </table>            
                            @include('layouts.table_footer',['data' => $itemAttachments])
                        </div>
                        <% boxchecked %>
                        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="action" value="">
                        <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
                    </div>
                </div>
            </form>
    </div>
   
@endsection

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($itemAttachments->pluck('id')) !!};
                $scope.items = {!! json_encode($itemAttachments) !!};
                $scope.selected_all=false;
                @foreach($itemAttachments as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                $scope.download = function(){
                    if ($scope.selected.length > 1){
                        $(".message-list").append(showMessages(['1件ずつダウンロードしてください。'], 'danger', 10000));
                    }else{
                        $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'選択添付ファイル予約',
                            message:'選択した添付ファイルのダウンロード予約をします。',
                            btnSuccess:'はい',
                            size:'lg',
                            callSuccess: function(inputData){
                                $http.post(link_ajax+"/download", {cids: $scope.selected})
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
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
                 };

                 $scope.delete = function(){
                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'選択された添付ファイルを削除します。よろしいですか？',
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
    </style>
@endpush