<div ng-controller="ImportDetailController">
    <div class="modal modal-add-stamp mt-5 modal-child" id="modalImportDetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">csv取込情報</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-import"></div>
                    <div>
                        <div> <span class="width10 text-left inline-block">取込日時</span>    <% import_detail.create_at %></div>
                        <div> <span class="width10 text-left inline-block">取込状態</span>    <% import_detail.result ? '成功':'失敗' %></div>
                        <br/>
                        <div> <span class="width10 text-left inline-block">総件数</span>    <% import_detail.total_num %>件</div>
                        <div> <span class="width10 text-left inline-block">成功</span>    <% import_detail.success_num %>件</div>
                        <div> <span class="width10 text-left inline-block">失敗</span>    <% import_detail.failed_num %>件</div>
                    </div>
                        <br/>
                    <div ng-if="!import_detail.result && error_detail.total > 0">
                        <div> <span class="width10 text-left inline-block">失敗した行目</span>    <% failed_rows %></div>
                        <br/>
                        <div>
                            <table class="errDetail tablesaw-list tablesaw table-bordered margin-top-5">
                                <tr>
                                    <td>行目</td>
                                    {{--PAC_5-2133 CSV取込--}}
                                    <td ng-if="import_detail.import_type === 1">メールアドレス</td>
                                    <td>状態</td>
                                    <td>コメント</td>
                                </tr>
                                <tr ng-repeat="detail in  error_detail.data">
                                    <td class="text-right" ng-bind="detail.row_id"></td>
                                    {{--PAC_5-2133 CSV取込--}}
                                    <td ng-if="import_detail.import_type === 1" ng-bind="detail.email"></td>
                                    <td>失敗</td>
                                    <td ng-bind="detail.comment"></td>
                                </tr>
                            </table>
                            <div class="mt-3"><% error_detail.total %> 件中 <% error_detail.from %> 件から <% error_detail.to %> 件までを表示</div>
                            <div class="text-center import-detail-pager">
                                <nav>
                                    <ul class="pagination">
                                        <li class="page-item" ng-class="{'disabled': error_detail.current_page <= 1}" :aria-disabled="{error_detail.current_page <= 1}" ng-click="changeDetailPage(error_detail.current_page - 1)" aria-label="« 前へ">
                                            <span class="page-link" aria-hidden="true">‹</span>
                                        </li>

                                        <li class="page-item" ng-class="{'active' : $index+1 == error_detail.current_page}" ng-repeat="page_index in range_func(error_detail.last_page) track by $index"
                                            ng-click="changeDetailPage($index+1)">
                                            <a class="page-link" ng-bind-html="$index+1"></a>
                                        </li>

                                        <li class="page-item" ng-class="{'disabled': error_detail.current_page >= error_detail.last_page}" :aria-disabled="{error_detail.current_page >= error_detail.last_page}" ng-click="changeDetailPage(error_detail.current_page + 1)">
                                            <a class="page-link" rel="next" aria-label="次へ »">›</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ImportDetailController', function($scope, $rootScope, $http) {
                $scope.id = 0;
                $scope.range_func = function(n) {
                    return new Array(n);
                };

                $rootScope.$on("openDetailImportHistory", function(event, data){
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    hideMessages();
                    $http.get(link_ajax + "/" +$scope.id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalImportDetail .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.import_detail = event.data.import_detail;
                                $scope.error_detail = event.data.error_detail;
                                //console.info(event.data.error_detail);
                                $scope.failed_rows = event.data.failed_rows;
                            }
                            $("#modalImportDetail").modal({backdrop: 'static', keyboard: false});
                        });
                });

                $scope.changeDetailPage = function(page){
                    //hideMessages();
                    if(page == $scope.error_detail.current_page || page < 1 || page > $scope.error_detail.last_page){
                        return;
                    }
                    $http.get(link_ajax + "/" +$scope.id + "?page=" + page)
                        .then(function(event) {
                            if(event.data.status == false){
                                $("#modalImportDetail .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.import_detail = event.data.import_detail;
                                $scope.error_detail = event.data.error_detail;
                                $scope.failed_rows = event.data.failed_rows;
                            }
                        });
                };
            })
        }
    </script>
@endpush

<style>
    .errDetail td,tr{
        border: 1px solid black;
    }
    .import-detail-pager .page-item{
        cursor: pointer;
    }
    .import-detail-pager .page-item.active{
        cursor: default;
    }
    .import-detail-pager .page-item.disabled{
        cursor: default;
    }
</style>
