
<div ng-controller="ListPositionController" class="list-position">
    <form action="" name="adminForm">
        <div class="message message-info"></div>
        <div class="text-right mt-3">
            @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE])
                <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
            @endcanany
            @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_UPDATE])
                <button type="button" class="btn btn-success  mb-1" ng-click="editRecord()" ng-disabled="!selectedID"><i class="fas fa-edit" ></i> 名称変更</button>
            @endcanany
            @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE])
                <button type="button" class="btn btn-success mb-1" ng-click="upload()"><i class="fas fa-upload"></i> CSV取込</button>
            @endcanany             
                <button type="button" class="btn btn-warning mb-1" ng-click="downloadCsv()"><i class="fas fa-download"></i> CSV出力</button>
            @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_DELETE])            
                <button type="button" class="btn btn-danger mb-1" ng-click="delete()" ng-disabled="!selectedID"><i class="fas fa-trash-alt"></i> 削除</button>
            @endcanany
        </div>
        <!--PAC_5-2320	役職の表示件数を変更できるようにする　start-->
        <div class="col-12 col-md-6 col-xl-12 mb-3">
            <label class="d-flex"><span style="line-height: 27px">表示件数：</span>
                <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                    <option {{Request::get('limit') == '10' ? 'selected': ''}} value="10">10</option>
                    <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                    <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                </select>
            </label>
        </div>
        <!--PAC_5-2320	役職の表示件数を変更できるようにする　END-->
        <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
            <thead>
                <tr>
                    <th scope="col" class="sort">
                        {!! \App\Http\Utils\CommonUtils::showSortColumn('役職名', 'position_name', 'position_name', $orderDir) !!}
                    </th>
                </tr>
            </thead>
            <tbody id="sortable">
                @foreach ($itemsPosition as $item)
                    <tr data-id="{{ $item->id }}" data-id="{{ $item->display_no }}"  ng-class="{'selected': selectedID == {{ $item->id }}}" ng-click="selectRow({{ $item->id }})" class="record">
                    <td>{{ $item->position_name }}</td>
                    </tr>
                @endforeach
                
            </tbody>
        </table>
        <div class="mt-3" style="text-">{{ $dataCount }} 件中 {{ $dataFrom }} 件から {{ $dataTo }} 件までを表示</div>
        <div class="text-center" ng-if="{{$lastPage}} > 1">
            <nav>
                <ul class="pagination">
                    <li class="page-item" ng-class="{'disabled': {{$currentPage}} <= 1}" :aria-disabled="{ {{$currentPage}} <= 1 }" ng-click="changeDetailPage({{$currentPage}} - 1)" aria-label="« 前へ">
                        <span class="page-link" aria-hidden="true">‹</span>
                    </li>

                    <li class="page-item" ng-class="{'active' : $index+1 == {{$currentPage}} }" ng-repeat="page_index in range_func({{$lastPage}}) track by $index"
                        ng-click="changeDetailPage($index+1)">
                        <a class="page-link" ng-bind-html="$index+1"></a>
                    </li>

                    <li class="page-item" ng-class="{'disabled': {{$currentPage}} >= {{$lastPage}} }" :aria-disabled="{ {{$currentPage}} >= {{$lastPage}} }" ng-click="changeDetailPage({{$currentPage}} + 1)">
                        <a class="page-link" rel="next" aria-label="次へ »">›</a>
                    </li>
                </ul>
            </nav>
        </div>
{{--        @include('layouts.table_footer',['data' => $itemsPosition])--}}
        <input type="hidden" class="action" name="action" value="" />
        <input type="hidden" value="position_name" name="orderBy" />
        <input type="hidden" value="{{ Request::get('orderDir','ASC') }}" class="orderDir" name="orderDir" />
{{--        <input type="hidden" class="page" name="page" value="{{Request::get('page',1)}}">--}}
        <input type="hidden" class="currentPage" name="currentPage" value="{{$currentPage}}">
        <input type="hidden" class="lastPage" name="lastPage" value="{{$lastPage}}">
    </form>
</div>
<style>
    .list-position tr.record:hover{
        background-color: #e7f4f9;
    }
    .list-position tr.record.selected{
        background-color: #beebff;
    }    
    
</style>

@push('scripts')
    <script>
        var hasChangePosition = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListPositionController', function($scope, $rootScope, $http){
                $scope.type = 'position';
                $scope.selectedID = null;

                $scope.range_func = function(n) {
                    return new Array(n);
                };

                $scope.changeDetailPage = function(page){

                    if(page == $(".currentPage").val() || page < 1 || page > $(".lastPage").val()){
                        return;
                    }

                    var orderDir = $(".orderDir").val();
                    var limit = $('select[name=limit] option:selected').val();
                    var url = link_ajax + "?page=" + page + '&limit=' + limit + "&orderDir=" + orderDir;
                    location.href = url;

                };

                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                };
                $scope.addNew = function(){
                    $rootScope.$emit("openNewPosition");
                };

                $scope.editRecord = function(){
                    $rootScope.$emit("openEditPosition",{id:$scope.selectedID});
                 };
                 
                 $scope.delete = function(){    
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択中の役職を削除しますか？', 
                        btnDanger:'はい',
                        databack: $scope.selectedID,
                        callDanger: function(selectedID){
                            $rootScope.$emit("showLoading");
                            $http.delete(link_ajax + "/" + selectedID+"?type="+$scope.type)
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $(".list-"+$scope.type+" .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        location.reload();
                                        $(".list-"+$scope.type+" .record.selected").remove();
                                    }
                                });
                        }
                    });         
                    
                 };

                $scope.upload = function(){
                    $("#modalImport").modal();
                    $rootScope.$emit("showModalImport",{type:'Position'});
                };

                $scope.download = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'全ての役職データを出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            callSuccess: function(){
                                $(".action").val('export-position');
                                document.adminForm.submit();
                                $(".action").val('');
                            }
                        });
                };
                
                $scope.downloadCsv = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'全ての役職データを出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax_position_csv, 
                                                {
                                                    orderDir: "{{$orderDir}}"
                                                })
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

                $scope.updateSort = function(data){
                    $rootScope.$emit("showLoading");
                    $http({
                        method:'POST',
                        url:link_ajax_update_position_sort,
                        data: {
                            sort:data
                        }
                    }).then(function(event) {
                        $rootScope.$emit("hideLoading");
                    });
                }

                var sortPositionArr={
                    old:[],
                    new:[]
                }
                new Sortable($('#sortable').get(0), {
                    animation: 150,
                    group: 'nested',
                    fallbackOnBody: true,
                    ghostClass: 'blue-background-class',

                    onEnd: function (/**Event*/evt) {
                        buildPositionSortArr($('#sortable'),sortPositionArr.new)
                        let res=sortPositionArr.new.filter(function(v){
                            return v.sort!=sortPositionArr.old[v.id].sort
                        })
                        if (res.length>0){
                            $scope.updateSort(res)
                        }
                        sortPositionArr.old=sortPositionArr.new.slice()
                    },
                });
                var buildPositionSortArr=function (ele,arr){
                    ele.children('#sortable tr').each(function(index){
                        let obj=$(this)
                        arr[obj.data('id')]={id:obj.data('id'),sort:index}
                    })
                }
                buildPositionSortArr($('#sortable'),sortPositionArr.old)

            });
        }

        $("#modalDetailPositionItem").on('hide.bs.modal', function () {          
             if(hasChangePosition){
                 location.reload();
             }
        });
        //PAC_5-2320	役職の表示件数を変更できるようにする　start
        $(document).ready(function () {
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });
            $('form[name="adminForm"]').submit(function (e) {
                $('input[name="page"]').val('1');
            });
        });
    </script>
@endpush
@push('styles_after')
    <style>
        .list-position .page-item{
            cursor: pointer;
        }
        .list-position .page-item.active{
            cursor: default;
        }
        .list-position .page-item.disabled{
            cursor: default;
        }
    </style>
@endpush