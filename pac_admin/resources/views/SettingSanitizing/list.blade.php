<div ng-controller="SettingSanitizingController">
    <form name="adminForm" method="GET">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('sanitizing_line_name','回線名',Request::get('sanitizing_line_name', ''),'text', false,
                    [ 'placeholder' =>'回線名（検索条件）', 'id'=>'sanitizing_line_name' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('company_name','使用企業名	',Request::get('company_name', ''),'text', false,
                    [ 'placeholder' =>'使用企業名（検索条件）', 'col' => 3 ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('sanitize_request_limit','無害化ファイル要求上限',Request::get('sanitize_request_limit', ''),'text', false,
                    [ 'placeholder' =>'無害化ファイル要求上限（検索条件）', 'id'=>'sanitize_request_limit' ]) !!}
                </div>
                <div class="col-lg-3 text-right padding-top-20" style="padding-right:5%">
                    <button class="btn btn-primary mb-1" type="submit" ng-click="search()"><i class="fas fa-search" ></i> 検索</button>
                    <button type="button" class="btn btn-success mb-1" ng-click="createLine()"><i class="fas fa-plus-circle"></i> 登録</button>
                    <input type="hidden" class="action" name="action" value="search" />
                </div>

            </div>
            <div class="message message-list mt-3"></div>

            <div class="card mt-3">
                <div class="card-header">無害化回線一覧</div>
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
                                </label>
                            </div>
                        </div>
                    </div>
                    <span class="clear"></span>

                    <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('回線名', 'sanitizing_line_name', $orderBy, $orderDir) !!}
                            </th>
                            <th class="title sort" scope="col" style="width: 40%" data-tablesaw-priority="persist">
                                使用企業名
                            </th>
                            <th class="title sort" scope="col" style="width: 20%" data-tablesaw-priority="persist">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('無害化ファイル要求上限', 'sanitize_request_limit', $orderBy, $orderDir) !!}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($itemsLine as $i => $item)
                            <tr class="">
                                <td class="title" ng-click="editRecord({{ $item['id'] }})">{{ $item['sanitizing_line_name'] }}</td>
                                <td ng-click="editRecord({{ $item['id'] }})">{{ $item['company_names'] }}</td>
                                <td ng-click="editRecord({{ $item['id'] }})">{{ $item['sanitize_request_limit'] }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $itemsLine])
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy"/>
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir"/>
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked"/>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('SettingSanitizingController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($itemsLine->pluck('id')) !!};
                $scope.items = {!! json_encode($itemsLine) !!};

                if($('.row-edit input:checkbox:checked').length == $('.row-edit input:checkbox').length && $('.row-edit input:checkbox:checked').length != 0){
                    $scope.isCheckAll = true;
                }else{
                    $scope.isCheckAll = false;
                }

                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                };

                $scope.toogleCheck = function (id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }

                    if($('.row-edit input:checkbox:checked').length == $('.row-edit input:checkbox').length && $('.row-edit input:checkbox:checked').length != 0){
                        $scope.isCheckAll = true;
                    }else{
                        $scope.isCheckAll = false;
                    }
                };

                $scope.search = function () {
                    document.adminForm.submit();
                };

                $scope.createLine = function () {
                    $rootScope.$emit("openNewLine");
                };

                $scope.editRecord = function (id) {
                    $rootScope.$emit("openEditLine",{id:id});
                };

            });
        }else{
            throw new Error("Something error init Angular.");
        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
            if(hasChange){
                document.adminForm.action.value = '';
                document.adminForm.submit();
            }
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
