<div ng-controller="ListLongTermIndexTemplateController">
        <div class="text-right">
            @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_INDEX_SETTING_CREATE])
                <div class="btn btn-success" ng-click="tempaddNew()"><span class="fas fa-plus-circle"></span> 登録</div>
            @endcanany
        </div>

    <form action="" name="adminForm">
        <div class="card mt-3">
            <div class="card-header">テンプレートインデックス一覧</div>
            <div class="card-body">

                <span class="clear"></span>

                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width: 10px">
                                <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" />
                            </th>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('インデックス名', 'index_name', $orderBy, $orderDir) !!}
                            </th>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('データ型', 'data_type', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort" style="width: 180px">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('作成日', 'create_at', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort" style="width: 180px">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('更新日', 'update_at', $orderBy, $orderDir) !!}
                            </th>                                
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($temp_longTermStorage as $i => $item)
                        <tr class="">
                            <td class="title"> 
                                <input type="checkbox" value="{{ $item->id }}" ng-click="toogleCheck({{ $item->id }})"
                                    name="ids[]" class="id" onClick="isChecked(this.checked)" />
                            </td>
                            <td class="title" style="text-align: center" ng-click="editRecord({{ $item->id }})">{{ $item->index_name }}</td>
                            <td class="title" ng-click="editRecord({{ $item->id }})">
                                @if($item->data_type === 0) 
                                    数字型
                                @elseif($item->data_type === 1)
                                    文字型
                                @elseif($item->data_type === 2)
                                    日付型
                                @endif
                            </td>
                            <td ng-click="editRecord({{ $item->id }})">{{ date("Y/m/d H:i:s", strtotime($item->create_at)) }}</td>
                            <td ng-click="editRecord({{ $item->id }})">
                                @if($item->update_at)
                                    {{ date("Y/m/d H:i:s", strtotime($item->update_at)) }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                                    
                    </tbody>
                </table>
                @include('layouts.table_footer',['data' => $temp_longTermStorage])
            </div>
            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
            <input type="hidden" name="page" value="{{Request::get('page',1)}}">
        </div>
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListLongTermIndexTemplateController', function($scope, $rootScope, $http){
                $rootScope.info = {};
                $rootScope.id = 0;
                $rootScope.readonly = false, $rootScope.readonlyState = false, $rootScope.readonlyEmailBtn;
                $rootScope.allPermission = null;
                $rootScope.detailPermisson = null;

                $scope.tempaddNew = function(){
                    $rootScope.$emit("openNewTemplateIndex");
                };

                $scope.editRecord = function(id){
                    $rootScope.id = id;
                    $rootScope.info.id = id;
                    hideMessages();
                    hasChange = false;
                    $rootScope.readonly = false;
                    $rootScope.$emit("showLoading");
                    $http.get(link_show + "/" + id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailTemplateItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $rootScope.info = event.data.info;
                            $rootScope.info.index_name = ""+$rootScope.info.index_name;
                            $rootScope.info.data_type = ""+$rootScope.info.data_type;
                        }

                    });
                    $("#modalDetailTemplateItem").modal();
                 };

            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $("#modalDetailTemplateItem").on('hide.bs.modal', function () {
             if(hasChange){
                 location.reload();
             }
        });
    </script>
@endpush
