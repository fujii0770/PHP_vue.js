<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">印面名称更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>

                    <div class="item-stamp">
                        <div class="preview-stamp text-center">
                            <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                        </div>
                        <div class="row form-group">
                            <label for="" class="control-label col-md-2 text-right-lg">名称</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" required ng-model="stamp.stamp_name" maxlength="255" />
                            </div>
                        </div>
                    </div>
                    <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist mt-1"
                           data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort sort-column email" scope="col" style="width: 30%;"
                                ng-click="changeSortAdmin('email')"
                                data-tablesaw-priority="persist">
                                メールアドレス
                                <i class="icon fas fa-sort"></i>
                                <i class="icon icon-up fas fa-caret-up"></i>
                                <i class="icon icon-down fas fa-caret-down"></i>
                            </th>
                            <th scope="col" class="sort sort-column given_name"
                                ng-click="changeSortAdmin('given_name')">
                                氏名
                                <i class="icon fas fa-sort"></i>
                                <i class="icon icon-up fas fa-caret-up"></i>
                                <i class="icon icon-down fas fa-caret-down"></i>
                            </th>
                            <th scope="col" class="sort sort-column department_name"
                                ng-click="changeSortAdmin('department_name')">
                                部署
                                <i class="icon fas fa-sort"></i>
                                <i class="icon icon-up fas fa-caret-up"></i>
                                <i class="icon icon-down fas fa-caret-down"></i>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="record"
                            ng-repeat="(index, item) in  userCompanyStampData"
                            ng-class="{'selected': selectedID == changeSelectRow }"
                            ng-click="changeSelectRow(index)"
                        >
                            <td title="<% item.email %>"><% item.email %></td>
                            <td  title="<% item.fullName %>"><% item.fullName %></td>
                            <td title="<% item.departmentName %>"><% item.departmentName %></td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="mt-3"><% dataCount %> 件中 <% dataFrom %> 件から <% dataTo %> 件までを表示</div>
                    <div class="text-center import-detail-pager">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" ng-class="{'disabled': currentPage <= 1}"
                                    :aria-disabled="{ currentPage <= 1 }" ng-click="changeDetailPage(companyStampID,currentPage - 1)"
                                    aria-label="« 前へ">
                                    <span class="page-link" aria-hidden="true">‹</span>
                                </li>

                                <li class="page-item" ng-class="{'active' : page_index+1 == currentPage }"
                                    ng-repeat="(page_index, item) in range_func(lastPage) "
                                    ng-click="changeDetailPage(companyStampID,page_index + 1)">
                                    <a class="page-link" ng-bind-html="page_index + 1"></a>
                                </li>

                                <li class="page-item" ng-class="{'disabled': currentPage >= lastPage }"
                                    :aria-disabled="{ currentPage >= lastPage }"
                                    ng-click="changeDetailPage(companyStampID,currentPage + 1)">
                                    <a class="page-link" rel="next" aria-label="次へ »">›</a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                </div>



                <!-- Modal footer -->
                <div class="modal-footer">
                    @canany([PermissionUtils::PERMISSION_COMMON_MARK_SETTING_UPDATE])
                    <button type="submit" class="btn btn-success" ng-click="save()">
                        <i class="far fa-save"></i> 更新
                    </button>
                    @endcanany

                    @canany([PermissionUtils::PERMISSION_COMMON_MARK_SETTING_DELETE])
                    <button type="button" class="btn btn-danger" ng-click="remove()">
                        <i class="fas fa-trash-alt"></i> 削除
                    </button>
                    @endcanany

                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> 閉じる
                    </button>

                </div>

                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.stamp = {};
                $scope.range_func = function(n) {
                    let arr=[]
                    for(let i=0;i<n;i++){
                        arr.push(i)
                    }
                    return arr;
                };
                $scope.id = 0;
                $scope.userCompanyStampData = [];
                $scope.selectedID = null;

                $scope.resultData = [];

                $scope.currentPage = 1;
                $scope.lastPage = 1;
                $scope.dataCount = 0;
                $scope.dataFrom = 0;
                $scope.dataTo = 0;
                $scope.user_state_flg = {!! json_encode(\App\Http\Utils\AppUtils::STATE_USER) !!};

                $scope.pageLinkData = [];

                $scope.orderBy = "asc";
                $scope.orderDir = "email";

                $scope.changeSortAdmin = function (orderBy) {
                    if ($scope.orderBy == orderBy) {
                        $scope.orderDir = $scope.orderDir == 'asc' ? 'desc' : 'asc';
                    }
                    $scope.orderBy = orderBy;
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $(".table-sort-client .sort-column." + orderBy).addClass('active');
                    if ($scope.orderDir == 'asc')
                        $(".table-sort-client .sort-column." + orderBy).addClass('active-up');
                    else $(".table-sort-client .sort-column." + orderBy).addClass('active-down');
                    $scope.changeDetailPage($scope.stamp.id,1);
                };

                $scope.changeSelectRow = function(index){
                    $scope.selectedID = index;
                };
                $scope.changeDetailPage = function(id,page){
                    if(page == $(".currentPage").val() || page < 1 || page > $(".lastPage").val()){
                        return;
                    }
                    if(page < 1 || page > $scope.lastPage ){
                        return;
                    }
                    $scope.companyStampID = id;
                    var url = "{{url('global-setting/company-stamp/getUsersAssign')}}"
                    $http.post(url, {
                        company_stamp_id: id,
                        page:page,
                        orderBy: $scope.orderBy,
                        orderDir: $scope.orderDir
                    })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.resultData = event.data.data;
                                $scope.currentPage = $scope.resultData.current_page ? $scope.resultData.current_page : 1;
                                $scope.lastPage = $scope.resultData.last_page ? $scope.resultData.last_page :1;
                                $scope.userCompanyStampData = $scope.resultData.data;
                                $scope.dataCount = $scope.resultData.total ? $scope.resultData.total : 0;
                                $scope.dataFrom = $scope.resultData.from ? $scope.resultData.from : 0;
                                $scope.dataTo = $scope.resultData.to ? $scope.resultData.to : 0;
                                $scope.pageLinkData = $scope.range_func($scope.lastPage);

                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                $rootScope.$emit("openUpdateStamp",{stamp_name:$scope.stamp.stamp_name});
                            }
                        });
                    $("#modalDetailItem").modal();
                };
                $rootScope.$on("openEditStamp", function(event, data){
                    hideMessages();
                    $scope.stamp = JSON.parse(JSON.stringify(data.stamp));
                    $scope.id = 0;
                    $scope.userCompanyStampData = [];
                    $scope.selectedID = null;
                    $scope.resultData = [];
                    $scope.currentPage = 1;
                    $scope.lastPage = 1;
                    $scope.dataCount = 0;
                    $scope.dataFrom = 0;
                    $scope.dataTo = 0;
                    $scope.orderBy = "asc";
                    $scope.orderDir = "email";
                    $scope.pageLinkData = [];
                    $scope.changeDetailPage($scope.stamp.id,1);
                    $("#modalDetailItem").modal();
                });

                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();

                        $rootScope.$emit("showLoading");
                        $http.post(link_ajax, {id: $scope.stamp.id,stamp_name: $scope.stamp.stamp_name})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                $rootScope.$emit("openUpdateStamp",{stamp_name:$scope.stamp.stamp_name});
                            }
                        });
                    }
                };

                $scope.remove = function(){

                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'削除確認',
                        message:'<div class="text-left">削除した印鑑は元に戻せません。</div>'
                                    + '<div class="text-left">利用者に割り当てられている場合、削除と同時に割り当ては解除されます。</div>'
                                    + '<div class="text-left">共通印を削除しますか？</div>'
                                    ,
                        btnDanger:'削除',
                        databack: $scope.stamp.id,
                        callDanger: function(id){
                            $rootScope.$emit("showLoading");
                            $http.delete(link_ajax +"/"+ id, {params: {id: $scope.stamp.id,stamp_name: $scope.stamp.stamp_name}})
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        $(".message-list").append(showMessages(event.data.message, 'warning', 10000));
                                        $rootScope.$emit("openRemoveStamp",{});
                                        $("#modalDetailItem").modal('hide');
                                    }
                                });
                        }
                    });

                };

            })
        }
    </script>
@endpush


@push('styles_after')
    <style>
        .table-sort-client{ }
        .table-sort-client .sort{ }
        .table-sort-client .sort-column{ }
        .table-sort-client .sort-column .icon{ right: 5px; }
        .table-sort-client .sort-column .icon-up{ display: none; }
        .table-sort-client .sort-column .icon-down{ display: none; }
        .table-sort-client .sort-column.active{ }
        .table-sort-client .sort-column.active .icon{ display: none; }
        .table-sort-client .sort-column.active-up{ }
        .table-sort-client .sort-column.active-up .icon-up{ display: inline-block; }
        .table-sort-client .sort-column.active-down{ }
        .table-sort-client .sort-column.active-down .icon{ display: none; }
        .table-sort-client .sort-column.active-down .icon-down{ display: inline-block; }
        .tablesaw {
            width: 100%;
            max-width: 100%;
            empty-cells: show;
            border-collapse: collapse;
            border: 0;
            padding: 0;
            table-layout: fixed;
        }
        .tablesaw th, .tablesaw td {
            word-break: break-all;
        }
    </style>
@endpush
