<div ng-controller="ListController">
        
    <form action="" name="adminForm" method="GET">
        <div class="form-search form-vertical">
            <div class="col-lg-8"></div>
            <div class="message message-list mt-3"></div>
            <div class="text-right">
                <div class="btn btn-success  mb-1"  ng-click="addNew()" ><i class="fas fa-plus-circle"  ></i> 登録</div>
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        @if ($items)
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数: </label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                   {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="clear"></span>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('選択ID', 'contract_edition', $orderBy, $orderDir) !!}
                                </th>
                                <th class="title sort" scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('契約エディション名', 'contract_edition_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'state_flg', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('備考', 'memo', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $item)
                                <tr class="row-{{ $item->id }} row-edit" ng-class="{ edit: id == {{ $item->id }} }" ng-click="editRecord({{ $item->id }})">
                                    <td class="title">{{ $item->contract_edition }}</td>
                                    <td class="title">{{ $item->contract_edition_name }}</td>
                                    <td class="title">{{ \App\Http\Utils\AppUtils::EDITION_STATE_FLG[$item->state_flg]}}</td>
                                    <td class="title" title="{{$item->memo}}">{{ (mb_strlen($item->memo,'utf-8') > 20 ? mb_substr($item->memo,0,20,'utf-8').'...' : $item->memo) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $items])
                </div>
            </div>
        </div>
        @endif
        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
        <input type="hidden" name="page" value="{{Request::get('page',1)}}">
    </form>
</div>


@push('scripts')
    <script>
        var hasChange = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.addNew = function(){
                    $rootScope.$emit("openNewEdition");
                };
                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditEdition",{id:id});
                 };
            });
        }else{
            throw new Error("Something error init Angular.");
        }
        $("#modalDetailItem").on('hide.bs.modal', function () {
             if(hasChange){
                 location.reload();
             }
        });
    </script>
@endpush
