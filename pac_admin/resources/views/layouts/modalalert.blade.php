<div ng-controller="ModalAlertController">

        <div class="modal modal-add-stamp mt-5  message-info " id="modalAlert" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-<% size %>">
                <div class="modal-content">            
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <div class="modal-title" ng-if="title" ng-bind-html="title"></div>
                    </div>                
                    <!-- Modal body -->
                    <div class="modal-body text-left" ng-if="message" ng-bind-html="message"></div>
                    <div class="modal-footer">
                        <div class="btn btn-default" ng-click="alertClose()">
                            <i class="fas fa-times-circle"></i> <% btnClose %>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      
</div>
    
    
@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        if(appPacAdmin){
            appPacAdmin.controller('ModalAlertController', function($scope, $rootScope){
                $rootScope.$on("showMocalAlert", function(event, data){
                    data = data || {};
                    $scope.title = data.title || "";
                    $scope.message = data.message || "";
                    $scope.btnClose = data.btnClose || "閉じる";
                    $scope.callBack = data.callBack || null;
                    $scope.databack = data.databack || null;
                    $scope.size = data.size || "sm";

                    $("#modalAlert").modal();
                });

                $scope.alertClose = function(){
                    if($scope.callBack) $scope.callBack($scope.databack);
                    $("#modalAlert").modal("hide");
                }
            })
        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush