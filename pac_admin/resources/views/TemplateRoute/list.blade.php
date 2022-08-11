<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('name','名称',Request::get('name', ''),'text', false,
                    [ 'placeholder' =>'承認ルート名称（部分一致）', 'id'=>'name' ]) !!}
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label">役職</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label">部署</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control select2']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label"></label>
                        <div class="col-md-8">
                            <label for="onlyUnsigned" class="control-label">
                                <input type="checkbox" ng-model="onlyUnsigned" id="onlyUnsigned" name="onlyUnsigned"
                                       value="1" ng-true-value="1"/>有効な承認ルートのみ</label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search"></i> 検索</button>
                @can([PermissionUtils::PERMISSION_TEMPLATE_ROUTE_CREATE])
                    <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle"></i> 登録</div>
                    {{--PAC_5-2133 CSV取込--}}
                    <div class="btn btn-success  mb-1" ng-click="upload()"><i class="fas fa-upload" ></i> CSV取込</div>
                @endcan
                <button type="button" class="btn btn-warning mb-1" ng-disabled="{{ $total>0?'false':'true'}}" ng-click="downloadCsv($event)"><i class="fas fa-download" ></i> CSV出力</button>
                <input type="hidden" class="action" name="action" value="search"/>
            </div>
        </div>
        <div class="message message-list mt-3"></div>
        @if($query)
            <div class="card mt-3">
                <div class="card-header">承認ルート一覧</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row" style="margin-left: 0 !important;">
                                <label class="col-lg-6 col-md-2 control-label"></label>
                                @can([PermissionUtils::PERMISSION_TEMPLATE_ROUTE_UPDATE])
                                <div class="col-lg-6 col-md-2 text-right" style="padding-right: 0 !important;">
                                    <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0"
                                            ng-click="delete($event)"><i class="fas fa-trash-alt"></i> 削除
                                    </button>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <span class="clear"></span>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5"
                           data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input type="checkbox" ng-model="selected_all" onClick="checkAll(this.checked)" ng-change="toogleCheckAll()"/>
                            </th>
                            <th class="sort" scope="col" style="width: 22%">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('名称', 'name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort" style="width: 33%">
                                <div class="sort-column no-order">回覧先</div>
                            </th>
                            <th scope="col" class="sort" style="width: 17%">
                                <div class="sort-column no-order">合議設定</div>
                            </th>
                            <th scope="col" class="sort" style="width: 5%">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('有効', 'state', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort" style="width: 23%">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('更新日時', 'update_at', $orderBy, $orderDir) !!}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($query as $i => $route)
                            <tr class="row-{{ $route->id }} row-edit" mouseover="mouseover({{ $route->id }})"
                                ng-class="{ edit: id == {{ $route->id }} }">
                                <td class="title">
                                    <input type="checkbox" class="cid" onClick="isChecked(this.checked)" ng-model="{{'selected_id'.$route->id}}" ng-value="{{ $route->id }}"
                                           ng-change="toogleCheck({{ $route->id }})"/>
                                </td>
                                <td class="row-data" ng-click="editRecord({{ $route->id }})">{{ $route->name }}</td>
                                <td class="row-data" ng-click="editRecord({{ $route->id }})"
                                    ng-bind-html="formatRoute('{{ $route->dep_pos_name }}')"></td>
                                <td class="row-data" ng-click="editRecord({{ $route->id }})"
                                    ng-bind-html="formatRoute('{{ $route->modes }}')"></td>
                                <td class="row-data" ng-click="editRecord({{ $route->id }})">
                                    {{ $route->state }}
                                </td>
                                <td class="row-data" ng-click="editRecord({{ $route->id }})">
                                    {{ $route->update_at }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $query])
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy"/>
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir"/>
                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;"/>
            </div>
        @endif

    </form>
</div>


@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('ListController', function ($scope, $rootScope, $http, $sce) {
                $rootScope.search = {name: "", position: "", department: "", status: ""};
                $scope.routes = {!! json_encode($query) !!};
                $scope.selected = [];
                $scope.numChecked = 0;
                $scope.isCheckAll = false;
                $scope.selected_all=false;
                @foreach($query as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                $scope.onlyUnsigned = {!! $onlyUnsigned !!}

                // 新規登録
                $scope.addNew = function () {
                    $rootScope.$emit("openNewRoute");
                };

                // 編集
                $scope.editRecord = function (id) {
                    $('.row-edit').css("background", "")
                    $('.row-' + id).css("background", "#90ee90")
                    $rootScope.$emit("openEditRoute", {id: id});
                };

                // 文字解析
                $scope.formatRoute = function (route) {
                    return $sce.trustAsHtml(route);
                }

                // PAC_5-2133 CSV取込
                $scope.upload = function(){
                    $("#modalImport").modal();
                    $rootScope.$emit("showModalImport");
                };
                // 全てを選択
                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll) {
                        $scope.routes.data.forEach(function(item){
                            $scope.selected.push(item.id);
                        });
                    } else {
                        $scope.selected = [];
                    }
                };

                // 一つを選択
                $scope.toogleCheck = function (id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }
                };

                // 削除
                $scope.delete = function (event) {
                    event.preventDefault();
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '選択した承認ルートを削除します。よろしいですか？',
                            btnDanger: '削除',
                            callDanger: function () {
                                $rootScope.$emit("showLoading");
                                $http.post(link_deletes, {tids: $scope.selected})
                                    .then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            location.reload();
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                };
                // CSV出力
                $scope.downloadCsv = function(event){
                    event.preventDefault();
                    let param={
                        name:'{{Request::get('name')}}',
                        position:'{{Request::get('position')}}',
                        department:'{{Request::get('department')}}',
                        state:'{{Request::get('onlyUnsigned')}}',
                        ids:$scope.selected,
                    }
                    let message= ''
                    if($scope.selected.length>0){
                        message = '選択された承認ルートを出力します。<br />実行しますか？'
                    }else{
                        message = 'すべて承認ルートを出力します。<br />実行しますか？'
                    }
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:message,
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_export, param)
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
            })
        } else {
            throw new Error("Something error init Angular.");
        }

        $(document).ready(function () {
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

        $("#modalDetailItem").on('hide.bs.modal', function () {
            $(".adminlist tr.edit").removeClass('edit');
            if (hasChange) {
                location.reload();
            }
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

        .ng-binding > label {
            width: 200px;
            margin-bottom: 0;
        }

        .row-data {
            vertical-align: top !important;
        }

        .row-edit:hover {
            background-color: #f0fff0;
        }

        .row-data > label {
            cursor: pointer;
        }

        .no-order {
            cursor: auto;
        }

        .dep-pos-label {
            display: flex;
            flex-wrap: nowrap;
            align-items: flex-start;
        }

        .dep-pos-label > label {
            width: 300px;
            margin-bottom: 0;
        }
    </style>
@endpush