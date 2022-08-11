<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">経費申請詳細</h4>
                        <button type="button" class="close" data-dismiss="modal" ng-click="cancelAttendance()">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header"><% item.form_name %></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="row">
                                                <div class="col-md-8 col-sm-8 col-24">
                                                    <p id="eps_app_no">&nbsp;&nbsp;申請ID:&nbsp;<% item.id %>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;状況:&nbsp;<% circularDispStatus[item.circular_status] %></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">
                                                </div>
                                                <div class="col-md-8 col-sm-8 col-24">
                                                    <p id="purpose_name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;目的:&nbsp;<% item.purpose_name %></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">
                                                </div>
                                                <div class="col-md-9 col-sm-9 col-10">
                                                    <p id="department_name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;所属:&nbsp;<% item.department_name %>&nbsp;&nbsp;&nbsp;氏名:&nbsp;<% item.user_name %>&nbsp;&nbsp;&nbsp;ID:&nbsp;<% item.mst_user_id %></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">
                                                    </div>
                                                <div class="col-md-8 col-sm-8 col-24">
                                                    <p id="user_name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;期間:&nbsp;<% item.target_period_from %>　～　<% item.target_period_to %></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">
                                                </div>
                                                <div class="col-md-8 col-sm-8 col-24">
                                                    <p id="form_dtl">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;詳細:&nbsp;<% item.form_dtl %></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-4 " >
                                            <div class="row border_style">
                                                <label >添付ファイル:</label>
                                                <ul class="app_box" ng-show="items2!=''">
                                                    <li ng-repeat="(key, item2) in items2 track by $index" ng-click="download(item2.id)"><a href=""><% item2.original_file_name %></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-11 col-sm-11 col-11">
                                            <p id="suspay_amt">&nbsp;&nbsp;事前仮払金額:&nbsp;<% item.suspay_amt %>円　ー　<b>精算金額:&nbsp;<% item.eps_amt %>円</b>　＝　過不足金額:&nbsp;<% item.eps_diff %>円</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">
                                            <p id="suspay_date">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;仮払日:&nbsp;<% item.suspay_date %></p>
                                        </div>
                                        <div class="col-2">
                                        </div>
                                        <div class="col-4">
                                            <p id="diff_date">過不足金精算日:&nbsp;<% item.diff_date %></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-3">
                                <div class="card-header">明細</div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                表示件数:
                                                <select ng-model="admin_option.limit"
                                                        ng-change="loadUserAdmin(item.id)"
                                                        ng-options="option for option in option_limit track by option">
                                                </select>
                                            </div>
                                        </div>
                                        <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist mt-1"
                                               data-tablesaw-mode="swipe">
                                            <thead>
                                            <tr>
                                                <th class="sort sort-column wtsm_name" scope="col"  width="140px"
                                                    ng-click="changeSortAdmin('wtsm_name')"
                                                    >
                                                    用途
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" width="140px" class="sort sort-column expected_pay_date"
                                                    ng-click="changeSortAdmin('expected_pay_date')">
                                                    支払日
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" class="sort sort-column remarks"
                                                    ng-click="changeSortAdmin('remarks')">
                                                    概要
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" class="sort sort-column unit_price"
                                                    ng-click="changeSortAdmin('unit_price')">
                                                    金額
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>

                                                <th scope="col" width="80px" class="sort sort-column state_flg"
                                                    ng-click="changeSortAdmin('state_flg')">
                                                    領収書/証憑
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="row- row-edit"
                                                ng-click="detailsRecord(item_admin.id,item_admin.t_app_id)"
                                                ng-repeat="(key, item_admin) in itemsAdmin | startFrom:currentPage*admin_option.limit | limitTo:admin_option.limit">
                                                <td><% item_admin.wtsm_name %></td>
                                                <td><% item_admin.expected_pay_date %></td>
                                                <td><% item_admin.remarks %></td>
                                                <td><% item_admin.amount%></td>
                                                <td class="text-center col-action">
                                                    <i class="fas fa-check" ng-if="item_admin.submit_method == 1 || item_admin.submit_method == 2"></i>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="mt-3"><% count %> 件中 <% currentPage * admin_option.limit + 1 %>
                                            件から
                                            <% (currentPage >= count / admin_option.limit - 1) ? count : currentPage * admin_option.limit + admin_option.limit%>
                                            件までを表示
                                        </div>
                                        <div class="pagination-center" ng-hide="checkCount()">
                                            <div class="pagination">
                                                <button ng-disabled="currentPage == 0"
                                                        ng-click="currentPage=currentPage-1">
                                                    <i class="fas fa-backward"></i>
                                                </button>
                                                <%currentPage + 1 %>/<% Math.ceil(count / admin_option.limit) %>
                                                <button ng-disabled="currentPage >= count/admin_option.limit - 1"
                                                        ng-click="currentPage=currentPage+1">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                                </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" ng-click="cancelAttendance()">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>

                        </div>
                    </div>

                </div>
            </div>
    </form>
</div>
@push('styles_after')
    <style>
        .border_style{
            border:2px solid #dcdcdc;
            padding:12px 12px 10px;
            border-radius:5px;
        }
        .app_box{
            height: 109px;
            width:  300px;
            overflow-y: auto;
        }
    </style>
@endpush
@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.items2 = {};
                $scope.option_limit = [10, 50, 100];
                $scope.admin_option = {limit: 10, orderBy: 'id', orderDir: 'desc', mst_company_id: null};
                $scope.itemsAdmin = [];
                $scope.itemAdmin = {id: 0};
                $scope.count = 0;
                $scope.currentPage = 0;
                $scope.circularDispStatus = {!! json_encode(\App\Http\Utils\AppUtils::CIRCULAR_DISP_STATUS) !!};

                $rootScope.$on("openDetailsAttendance", function(event,data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    hideMessages();

                    $http.post(link_ajax + "/detail1/" + data)
                    // $http.get(link_ajax + "/" + data)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.item = event.data.item;
                            $scope.items2 = event.data.item2;
                        }
                    });
                    $scope.loadIndexDetail(data);
                    $("#modalDetailItem").modal();
                });

                $scope.changeSortAdmin = function (orderBy) {
                    if ($scope.admin_option.orderBy == orderBy) {
                        $scope.admin_option.orderDir = $scope.admin_option.orderDir == 'asc' ? 'desc' : 'asc';
                    }
                    $scope.admin_option.orderBy = orderBy;
                    $scope.loadUserAdmin($scope.item.eps_app_no);
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $(".table-sort-client .sort-column." + orderBy).addClass('active');
                    if ($scope.admin_option.orderDir == 'asc')
                        $(".table-sort-client .sort-column." + orderBy).addClass('active-up');
                    else $(".table-sort-client .sort-column." + orderBy).addClass('active-down');
                };

                // 明細
                $scope.loadIndexDetail = function (eps_app_no) {
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax + "/indexdetail/" + eps_app_no, $scope.admin_option)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            $scope.itemsAdmin = event.data.items;
                            $scope.currentPage = 0;
                            $scope.count = $scope.itemsAdmin.length;
                            $scope.Math = window.Math;
                            $scope.checkCount = function () {
                                if ($scope.count >= $scope.admin_option.limit) {
                                    return false;
                                }
                                return true;
                            };
                        });
                };
                $scope.detailsRecord = function (id,eps_app_item_no) {
                    $rootScope.$emit("openAppDetail2",{id:id});
                    $("#modalDetailItem").modal('hide');
                };

                $scope.download = function(id){
                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'添付ファイル予約',
                        message:'添付ファイルのダウンロード予約をします。',
                        btnSuccess:'はい',
                        size:'lg',
                        callSuccess: function(inputData){
                            $http.post(link_ajax + "/reserve", {cid: id})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-info").append(showMessages(event.data.message, 'success', 10000));

                                }
                            });
                        }
                    });
                };
            });
            appPacAdmin.filter('startFrom', function () {
                return function (input, start) {
                    start = +start; //parse to int
                    return input.slice(start);
                }
            });

        }
    </script>
@endpush
