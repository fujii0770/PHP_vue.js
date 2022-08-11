@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('expense-index') }}";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
            @csrf
            <div class="form-search form-horizontal">
                <div class="row">
                    <div class="col-lg-3">
                        {!! \App\Http\Utils\CommonUtils::showFormField('form_code','様式コード',Request::get('form_code', ''),'text', false,
                        [ 'placeholder' =>'様式コード（部分一致）' ]) !!}
                    </div>
                    <div class="col-lg-3">
                        {!! \App\Http\Utils\CommonUtils::showFormField('form_code','様式名',Request::get('form_code', ''),'text', false,
                        [ 'placeholder' =>'様式名（部分一致）' ]) !!}
                    </div>
                    <div class="col-lg-3">
                        {!! \App\Http\Utils\CommonUtils::showFormField('form_code','説明',Request::get('form_code', ''),'text', false,
                        [ 'placeholder' =>'説明（部分一致）' ]) !!}
                    </div>
                    
                    
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
                    <div class="card-header">様式一覧</div>

                    <div class="card-body">
                        <div class="table-head" style="padding:10px">
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
                                    <table class="" style="margin: 0 0 0 auto;">
                                        <td id="drop-zone" style="padding: 0px; width: 200px; margin-left : 270px;">
                                            <label id="uplabel" 
                                                   for="upfile" 
                                                   class="filelabel" style="text-align:center;"><strong ng-bind="upfilename" style="font-size: 13px;"></strong></label>
                                            <input type="file" file-model="file_upload" id="upfile" style="display: none;">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-warning mb-1" ng-click="upload()"><i class="fas fa-download"></i> 新規登録</button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger mb-1" ng-click="delete()" ng-disabled="selected.length==0"><i class="fas fa-trash-alt"></i> 削除</button>
                                        </td>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <span class="clear"></span>
                            
                        <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                            <thead style='height: 40px;'>
                            <tr>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                    <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" style="margin-left : 7px"/>
                                </th>
                                <th scope="col" class="sort" style="width: 500px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('様式コード', 'LTD.form_code', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('様式名', 'LTD.form_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('説明', 'LTD.remarks', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($expense_file as $i => $item)
                                <tr  style='height: 35px;'>
                                    <td class="title" style='height: 30px;width:30px;'>
                                        <input type="checkbox" value="{{ $item->id }}" ng-click="toogleCheck({{ $item->id }})"
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" style="margin-left : 7px" />
                                    </td>
                                    <td>{{ $item->form_code}}</td>
                                    <td>{{ $item->form_name }}</td>
                                    <td>{{ $item->validity_period_from }}-{{validity_period_to}}</td>
                                    <td>{{ $item->remarks}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                                <table class="" style="margin: 0 0 0 auto;">
                                        <td id="dropZone_footer" style="padding: 0px; width: 1200px; margin-left : 270px;">
                                            <label id="uplabel" 
                                                   for="upfile" 
                                                   class="filelabel2" style="text-align:center;"><strong ng-bind="upfilename"></strong></label>
                                            <input type="file" file-model="file_upload" id="upfile" style="display: none;">
                                        </td>
                                        
                                </table>
                    </div>

                    <% boxchecked %>
                    <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                    <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="automaticOnFlg" value="0">
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
                $scope.file_upload = "";
                $scope.name = 0;
                $scope.upfilename = "ファイル選択";
                $scope.document_access_flg = 0;
                $scope.cids = {!! json_encode($template_file->pluck('id')) !!};
                $scope.isCheckAll = false;
                $scope.selected = [];
                $scope.files = "";
                $scope.filesfoot = "";

                var dropZone = document.getElementById('drop-zone');
                var dropZone_footer = document.getElementById('dropZone_footer');

                dropZone.addEventListener('dragover', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                }, false);

                dropZone_footer.addEventListener('dragover', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                }, false);

                dropZone.addEventListener('dragleave', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                }, false);

                dropZone_footer.addEventListener('dragleave', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                }, false);

                dropZone.addEventListener('drop', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    this.style.background = '#ffffff';
                    $scope.files = e.dataTransfer.files;
                    $scope.file_upload = $scope.files[0];
                    setTimeout(function () {
                        $scope.$apply(function () {
                        console.log('drag-and-drop event');
                        });
                    }, 2)
                }, false);

                dropZone_footer.addEventListener('drop', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    this.style.background = '#ffffff';
                    $scope.filesfoot = e.dataTransfer.files;
                    $scope.file_upload = $scope.filesfoot[0];
                    setTimeout(function () {
                        $scope.$apply(function () {
                        console.log('drag-and-drop event');
                        });
                    }, 2)
                }, false);

                $scope.search = function () {
                    document.adminForm.submit();
                };

                function formatDate(dt) {
                    var y = dt.getFullYear();
                    var m = ('00' + (dt.getMonth()+1)).slice(-2);
                    var d = ('00' + dt.getDate()).slice(-2);
                    return (y + '-' + m + '-' + d + ' '+ dt.getHours() +":"+ dt.getMinutes());
                }
                
                $scope.upload = function () {
                    if($scope.file_upload == "" || $scope.file_upload == undefined){
                        alert("ファイルが選択されていません");
                        return;
                    }

                    let fileData = new FormData();
                    fileData.append('uploadFile',$scope.file_upload);
                    fileData.append('document_access_flg',$scope.document_access_flg);

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '様式の登録',
                            btnSuccess: '登録',
                            size: 'lg',
                            inputMaxLength: $scope.inputMaxLength,
                            selectEnable: 'true',
                            titleItems:'設定　：　',
                            fileContent:'文書名　　：　'.concat($scope.file_upload.name),
                            dateContent:'登録日時　：　'.concat(formatDate(new Date())),
                            callSuccess: function (inputData) {
                                fileData.append('document_access_flg',$scope.document_access_flg);
                                $http.post(link_ajax + "/upload", fileData,{transformRequest: null,headers: {'Content-type':undefined}})
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        document.adminForm.submit();
                                        $(".message-list").append(showMessage(event.data.message, 'danger', 10000));
                                    }else{
                                        document.adminForm.submit();
                                        $(".message-list").append(showMessage(event.data.message, 'success', 10000));
                                    }
                                });
                            }
                        });
                }

                $scope.delete = function(){
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択された様式を削除します。よろしいですか？', 
                        btnDanger:'削除',
                        callDanger: function(){
                            $http.post(link_ajax + "/delete", {cids: $scope.selected})
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        document.adminForm.submit();
                                        $(".message-list").append(showMessage(event.data.message, 'danger', 10000));
                                    }else{
                                        document.adminForm.submit();
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

                $scope.toogleCheck = function (id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }
                };

                $(document).ready(function() {
                    $('select[name="limit"]').change(function () {
                        document.adminForm.submit();
                    });
                });

                $scope.$watch(function(){
                    return $scope.file_upload;
                }, function(newValue, oldValue, scope){
                    if($scope.file_upload==""){
                        $scope.upfilename = "ファイル選択";
                    }else{
                        $scope.upfilename = $scope.file_upload.name;
                    }
                });

            });

            appPacAdmin.directive('fileModel',function($parse){
                return{
                    restrict: 'A',
                    link: function(scope,element,attrs){
                        var model = $parse(attrs.fileModel);
                        element.bind('change',function(){
                            scope.$apply(function(){
                                model.assign(scope,element[0].files[0]);
                            });
                        });
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
            height: calc(1.5em + .75rem + 35px);
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
        .filelabel{border: 3px dashed; border-color :#33CCFF; padding: 10px; width: 250px; height:55px; border-radius: 10px; margin-left:40px;}
        .filelabel2{border: 3px dashed; border-color :#33CCFF; padding: 10px; width: 1200px; height:100px; border-radius: 10px; margin-left:40px;}
        .dragover{border: 3px solid; border-color :#00FF00;}
        .dragleave{border: 3px solid; border-color :#00FF00;}
        .drop{border: 3px solid; border-color :#00FF00;}
    </style>
@endpush