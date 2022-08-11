<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">詳細</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="stack">
                        <thead>
                            <tr>                            
                                <th class="title sort" scope="col">項目</th>
                                <th class="title sort" scope="col">内容</th>
                            </tr>
                        </thead>
                        <tbody>                           
                            <tr ng-repeat="(key, value) in item.detail_info">                                 
                                <td class="title"><% key %></td>
                                <td class="title" ng-if="!value.length">※変更なし※</td>
                                <td class="title" ng-if="value.length==2">
                                    【変更前】<% value[0] %> <br />
                                    【変更後】<% value[1] %>
                                </td>
                                <td class="title" ng-if="value.length==1"><% value[0] %></td>
                            </tr>
                            <tr >                                 
                                <td class="title">結果</td>
                                <td class="title"><% results[item.result] %></td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            
                <!-- Modal footer -->
                <div class="modal-footer">
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
                $scope.results = ['成功', '失敗'];

                $rootScope.$on("openEditHistory", function(event, data){
                    hideMessages();
                    $rootScope.$emit("showLoading");

                    $http.get(link_ajax + "/" +data.id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $("#modalDetailItem").modal();
                            }
                        });
                });
            })
        }
    </script>
@endpush