@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('Template-csv') }}";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="GET">
            @csrf
            <div class="form-search form-horizontal">
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('documentName','文書名',Request::get('documentName', ''),'text', false,
                        [ 'placeholder' =>'文書名（部分一致）' ]) !!}
                    </div>
                    <div class="col-lg-6">
                        <div class="row form-group">
                            <label for="document_id" class="col-md-3 control-label">完了日時</label>
                            <div class="col-md-5">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(['当月','1か月前','2か月前','3か月前','4か月前','5か月前','6か月前','7か月前','8か月前','9か月前','10か月前','11か月前','12か月以前'], 'finishedDate','','',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="row" id="row_add" ng-if="isShowLongTermindex=='1'">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-10">
                        <div class="row form-group">
                            <div class="btn btn-success " ng-click="addIndex()">+</div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-2 text-right">
                        <button class="btn btn-primary mb-1" type="submit" ng-click="search()"><i class="fas fa-search"></i> 検索</button>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="message message-list mt-3"></div>
                <div class="card mt-3">
                    <div class="card-header">回覧完了テンプレート一覧</div>

                    <div class="card-body">
                        <div class="table-head">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-6 col-md-2 "  style="float:left" >
                                        <span style="line-height: 27px">表示件数：</span>
                                        <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                        </select>
                                    </label>
                                    <div class="col-lg-6 col-md-2 text-right">
                                        <button type="button" class="btn btn-warning mb-1" ng-disabled="selected.length==0" ng-click="download()"><i class="fas fa-download"></i> ダウンロード</button>
                                        <!-- <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="delete()"><i class="fas fa-trash-alt"></i> 削除</button> -->
                                        <button type="button" class="btn btn-primary mb-1" ng-disabled="selected.length==0" ng-if="isShowAutomaticUpdate=='1'" ng-click="automaticUpdateClick('1')"> 自動更新ON</button>
                                        <button type="button" class="btn btn-primary mb-1" ng-disabled="selected.length==0" ng-if="isShowAutomaticUpdate=='1'" ng-click="automaticUpdateClick('0')"> 自動更新OFF</button>
                                    </div>
                                    <div class="col-lg-6 col-md-2 "  style="float:left">
                                        <input type="radio" ng-model="content" value="0,1,2" ng-checked=true>全情報　
                                        <input type="radio" ng-model="content" value="0,2">テンプレート情報　
                                        <input type="radio" ng-model="content" value="0">文書情報　
                                        <input type="radio" ng-model="content" value="0,1">回覧情報　
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
                                <th scope="col" class="sort" style="width: 150px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('回覧種類', 'TP.title', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('文書名', 'TP.file_size', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('差出人', 'TP.file_size', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('宛先', 'TP.file_size', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 150px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('アクセスコード', 'TP.file_size', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 150px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('完了日時', 'TP.file_size', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                            </thead>
                                <tbody>
                                @foreach ($itemsTemplate as $i => $item)
                                    <tr class="">
                                        <td class="title">
                                            <input type="checkbox" ng-model="{{'selected_id'.$item->id}}" ng-value="{{ $item->id }}" ng-change="toogleCheck({{ $item->id }})"
                                                   name="cids[]" class="cid" />
                                        </td>
                                        <td class="title">送信</td>
                                        <td>{{ $item->file_names }}</td>
                                        <td>{{ $item->sender }}</td>
                                        <td>{{ $item->emails }}</td>
                                        <td>{{ $item->access_code}}</td>
                                        <td>{{ $item->update_at }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                        </table>
                        
                    </div>

                </div>
            </div>
        </form>
    </div>
   
@endsection

@push('scripts')
    <script>
        var month = "当月";
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($itemsTemplate->pluck('id')) !!};
                $scope.inputMaxLength = 46;
                $scope.items = {!! json_encode($itemsTemplate) !!};
                $scope.selected_all=false;
                @foreach($itemsTemplate as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                $scope.search = function () {
                    document.adminForm.submit();
                };

                $scope.download = function () {
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '選択文書ダウンロード',
                            btnSuccess: 'はい',
                            size: 'lg',
                            inputEnable: 'true',
                            inputMaxLength: $scope.inputMaxLength,
                            callSuccess: function (inputData) {
                                $http.post(link_ajax + "/download", {cids: $scope.selected, fileName: inputData.val(),contents:$scope.content})
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $(".message-list").append(showMessage(event.data.message, 'danger', 10000));
                                    }else{
                                        $(".message-list").append(showMessage(event.data.message, 'success', 10000));

                                }
                            });
                            }
                        });
                };

                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
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
            });
        }
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
