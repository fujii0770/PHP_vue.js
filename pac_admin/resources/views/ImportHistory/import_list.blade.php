<div ng-controller="ImportListController">

    <form action="" name="importForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-2">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('from_date','対象期間From',Request::get('from_date', ''),'date', false,
                    [ 'placeholder' =>'対象期間From', 'id'=>'from_date' ]) !!}
                </div>
                <div class="col-lg-2">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('to_date','対象期間To',Request::get('to_date', ''),'date', false,
                    [ 'placeholder' =>'対象期間To', 'id'=>'to_date' ]) !!}
                </div>
                <div class="col-lg-7"></div>
            </div>
            <div class="text-right">
                <div class="btn btn-default mb-1" 
                     onclick="location.href='{{ url($strGoBackURL) }}'">
                    <i class="fas fa-long-arrow-alt-left"></i> 戻る
                </div>
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        @if($import_csv_list)
            <div class="card mt-3">
                {{--PAC_5-2133 CSV取込--}}
                <div class="card-header">CSV取込履歴{!! $import_type !!}</div>
                <div class="card-body">
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" style="table-layout:fixed;" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th class="title sort width-50" scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ファイル名', 'name', $orderBy, $orderDir, 'importForm') !!}
                                </th>
                                <th scope="col"  class="sort width-20">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('取込日時', 'create_at', $orderBy, $orderDir, 'importForm') !!}
                                </th>
                                <th scope="col"  class="sort width-15">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('件数', 'total_num', $orderBy, $orderDir, 'importForm') !!}
                                </th>
                                <th scope="col"  class="sort width-15">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'result', $orderBy, $orderDir, 'importForm') !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($import_csv_list as $i => $import)
                            <tr class="row-{{ $import['id'] }} row-edit" ng-class="{ edit: id == {{ $import['id'] }} }">
                                <td style="word-wrap:break-word;" ng-click="showDetail({{ $import['id'] }}, {{ $import['result'] }})">{{ $import->name }}</td>
                                <td style="word-wrap:break-word;" ng-click="showDetail({{ $import['id'] }}, {{ $import['result'] }})">{{ $import->create_at }}</td>
                                <td style="word-wrap:break-word;" ng-click="showDetail({{ $import['id'] }}, {{ $import['result'] }})">{{ $import->total_num }}</td>
                                <td style="word-wrap:break-word;" ng-click="showDetail({{ $import['id'] }}, {{ $import['result'] }})">{{ \App\Http\Utils\AppUtils::STATE_IMPORT_CSV[$import->result] }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $import_csv_list])
                </div>
                {{--PAC_5-2133 CSV取込--}}
                <input type="hidden" value="{!! Request::get('type', 1) !!}" name="type" />
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
            </div>
        @endif
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ImportListController', function($scope, $rootScope, $http){

                $rootScope.search = {from_date:"", to_date:""};
                $scope.numChecked = 0;

                $scope.showDetail = function(id, status){
                    if(status !== 2){
                        $rootScope.$emit("openDetailImportHistory",{id:id});
                    }
                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush
