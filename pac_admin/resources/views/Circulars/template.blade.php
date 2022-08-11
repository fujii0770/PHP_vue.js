@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('template-index') }}",
        link_ajax_route = "{{ url('template-route') }}";
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
                        {!! \App\Http\Utils\CommonUtils::showFormField('documentName','文書名',Request::get('documentName', ''),'text', false,
                        [ 'placeholder' =>'文書名（部分一致）' ]) !!}
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
                    <div class="card-header">テンプレート保存一覧</div>

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
                                            <input type="file" file-model="file_upload" id="fileUplaod" style="display: none;"  accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                                        </td>
                                        @can(\App\Http\Utils\PermissionUtils::PERMISSION_TEMPLATE_INDEX_CREATE)
                                        <td>
                                            <button type="button" class="btn btn-warning mb-1" ng-click="fileChanged()"><i class="fas fa-download"></i> 新規登録</button>
                                        </td>
                                        @endcan
                                        @can(\App\Http\Utils\PermissionUtils::PERMISSION_TEMPLATE_INDEX_DELETE)
                                        <td>
                                            <button type="button" class="btn btn-danger mb-1" ng-click="delete()" ng-disabled="selected.length==0"><i class="fas fa-trash-alt"></i> 削除</button>
                                        </td>
                                        @endcan
                                    </table>
                                </div>
                            </div>
                        </div>
                        <span class="clear"></span>
                            
                        <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                            <thead style='height: 40px;'>
                            <tr>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                    <input type="checkbox" ng-model="selected_all" onClick="checkAll(this.checked)" ng-change="toogleCheckAll()" style="margin-left : 7px"/>
                                </th>
                                <th scope="col" class="sort" style="width: 500px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('文書名', 'LTD.file_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('登録日時', 'LTD.template_create_at', $orderBy, $orderDir) !!}
                                </th>
                                @if($multiple_department_position_flg == 0)
                                    <th scope="col" class="sort" style="width: 300px">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('公開範囲', 'LTD.document_access_flg', $orderBy, $orderDir) !!}
                                    </th>
                                @else
                                    <th scope="col"  style="width: 300px">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('公開範囲1', 'LTD.document_access_flg', $orderBy, $orderDir) !!}
                                    </th>
                                    <th scope="col"  style="width: 300px">公開範囲2</th>
                                    <th scope="col"  style="width: 300px">公開範囲3</th>
                                @endif    
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('最終更新者', 'LTD.template_create_user', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px" ng-if="template_approval_route_flg">
                                    承認ルート
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('最終更新日時', 'LTD.template_update_at', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($template_file as $i => $item)
                                <tr  style='height: 35px;'>
                                    <td class="title" style='height: 30px;width:30px;'>
                                        <input type="checkbox" ng-model="{{'selected_id'.$item->id}}" ng-value="{{ $item->id }}" ng-change="toogleCheck({{ $item->id }})"
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" style="margin-left : 7px" />
                                    </td>
                                    <td  ng-click="onViewTemplateRoute({{$item->template_route_id??0}},{{$item->id}})" class="title">{{ $item->file_name }}</td>
                                    <td>{{ date("Y/m/d H:i", strtotime($item->template_create_at)) }}</td>
                                    @if($multiple_department_position_flg == 0)
                                        <td ng-click="onViewTemplateRoute({{$item->template_route_id??0}},{{$item->id}})">{{$item->rangeName}}{{ isset($item->displayStr[0]) ? ":".$item->displayStr[0] : ''}}</td>
                                    @else
                                        <td ng-click="onViewTemplateRoute({{$item->template_route_id??0}},{{$item->id}})">{{$item->rangeName}}{{ isset($item->displayStr[0]) ? "：".$item->displayStr[0] : '' }}</td>
                                        <td ng-click="onViewTemplateRoute({{$item->template_route_id??0}},{{$item->id}})">{{ isset($item->displayStr[1]) ?  $item->rangeName."：".$item->displayStr[1] : "" }}</td>
                                        <td ng-click="onViewTemplateRoute({{$item->template_route_id??0}},{{$item->id}})">{{ isset($item->displayStr[2]) ?  $item->rangeName."：".$item->displayStr[2] : "" }}</td>
                                    @endif
                                    <td ng-click="onViewTemplateRoute({{$item->template_route_id??0}},{{$item->id}})">{{ $item->template_create_user }}</td>
                                    <td ng-if="template_approval_route_flg"><span ng-if="{{$item->template_route_id}}" ng-click="onViewTemplateRoute({{$item->template_route_id}},{{$item->id}})">{{$item->route_name}}</span></td>
                                    <td>{{ $item-> template_update_at}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                                <table></table>
                        @include('layouts.table_footer',['data' => $template_file])
                    </div>

                    <% boxchecked %>
                    <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                    <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="automaticOnFlg" value="0">
                    <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />

                </div>
                <div class="card mt-3" ng-if="showDetailId">
                    <div class="card-header">設定ルート：</div>
                    <div class="card-body">
                        <table class="tablesaw-list  mt-1" data-tablesaw-mode="swipe">
                            <tbody>
                            <tr>
                                <td class="w-100" colspan="2" style="padding: 10px; height: 35px;"><% showRouteDetail.name %></td>
                            </tr>
                            <tr>
                                <td class="w-25" style="padding: 10px;">
                                    <div scope="col" ng-bind-html="formatRoute(showRouteDetail.dep_pos_name)"></div>
                                </td>
                                <td class="w-25" style="padding: 10px;">
                                    <div scope="col">
                                        <div class="dep-mode dep-pos-label"><label  ng-bind-html="formatRoute(showRouteDetail.modes)"></label></div>
                                    </div>
                                </td>
                            </tr>
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

        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http, $sce){
                $scope.file_upload = "";
                $scope.name = 0;
                $scope.upfilename = "ファイル選択";
                $scope.document_access_flg = 0;
                $scope.cids = {!! json_encode($template_file->pluck('id')) !!};
                $scope.isCheckAll = false;
                $scope.selected = [];
                $scope.files = "";
                $scope.filesfoot = "";
                $scope.selected_all=false;
                $scope.showDetailId = 0;
                $scope.showRouteDetail = [];
                $scope.template_approval_route_flg ={{($company  && $company->template_approval_route_flg && $company->template_flg && $company->template_route_flg)?1:0}};
                @foreach($template_file as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                $scope.search = function () {
                    document.adminForm.submit();
                };
                $scope.fileChanged = function(){
                    angular.element('#fileUplaod').trigger('click');
                };

                var elem = document.getElementById('fileUplaod');

                elem.addEventListener('change', function(e){
                    $scope.file_upload = elem.files[0];
                    $scope.upload();
                }, true);

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
                            title: '<strong>テンプレートファイルの登録</strong>',
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
                                        // document.adminForm.submit();
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
                        title:'選択された回覧を削除します。よろしいですか？', 
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
                $scope.onViewTemplateRoute = function(route_id,template_id){
                    if(!$scope.template_approval_route_flg){
                        return false;
                    }
                    $scope.showDetailId = route_id && template_id;
                    if($scope.showDetailId){
                        $rootScope.$emit("showLoading");
                        $http.get(link_ajax_route + "/getRouteInfo/"+route_id+"/"+template_id )
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $scope.showDetailId = false;
                                    $(".message-list").append(showMessage(event.data.message, 'danger', 10000));
                                }else{
                                    $scope.showRouteDetail = event.data.item;
                                }
                            });
                    }
                };
                // 文字解析
                $scope.formatRoute = function (route) {
                    return $sce.trustAsHtml(route);
                }

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
         .dep-pos-label {
             display: flex;
             flex-wrap: nowrap;
             align-items: flex-start;
         }

        .dep-pos-label > label {
            width: 80%;
            margin-bottom: 0;
        }
        .dep-pos-label > span {
            width: 100px;
            margin-left: 20px;
        }
        .dep-mode{
            padding-left: 10px;
            border-left: 1px solid #e1d7d7;
        }
    </style>
@endpush