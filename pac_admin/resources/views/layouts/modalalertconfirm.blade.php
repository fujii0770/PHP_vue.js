<div ng-controller="ModalAlertConfirmController">

        <div class="modal modal-add-stamp mt-5" id="modalAlertConfirm" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-<% size %>" data-backdrop="static" data-keyboard="false">
                <div class="modal-content">            
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <div class="modal-title" ng-if="title" ng-bind-html="title"></div>
                    </div>                
                    <!-- Modal body -->
                    <div class="modal-body text-center" ng-if="message" ng-bind-html="message"></div>
                    <div class="col"> </div>


                    <div class="modal-footer">
                        <div class="btn btn-default" ng-if="btnCancel" ng-click="confirmCancel()">
                            <i class="fas fa-times-circle"></i> <% btnCancel %>
                        </div>
                        <div class="btn btn-success" ng-if="btnSuccess" ng-click="confirmSuccess()">
                            <i class="fas fa-check"></i> <% btnSuccess %></div>
                        <div class="btn btn-danger" ng-if="btnDanger" ng-click="confirmDanger()">
                            <i class="fas fa-check"></i> <% btnDanger %>
                        </div>
                        <div class="btn btn-warning" ng-if="btnWarning" ng-click="confirmWarning()">
                            <i class="fas fa-check"></i><% btnWarning %>
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
            appPacAdmin.controller('ModalAlertConfirmController', function($scope, $rootScope){
                $rootScope.$on("showModalAlertConfirm", function(event, data){
                    data = data || {};
                    $scope.title = data.title || "";
                    $scope.message = data.message || "";
                    $scope.btnCancel = data.btnCancel || "キャンセル";
                    $scope.btnSuccess = data.btnSuccess || null;
                    $scope.btnDanger = data.btnDanger || null;
                    $scope.btnWarning = data.btnWarning || null;
                    $scope.callCancel = data.callCancel || null;
                    $scope.callSuccess = data.callSuccess || null;
                    $scope.callDanger = data.callDanger || null;
                    $scope.callWarning = data.callWarning || null;
                    $scope.databack = data.databack || null;
                    $scope.size = data.size || "sm";
                    $scope.inputEnable = data.inputEnable || null;
                    $scope.inputMaxLength = data.inputMaxLength || 50;
                    $scope.selectEnable = data.selectEnable || null;
                    $scope.fileContent = data.fileContent || null;
                    $scope.dateContent = data.dateContent || null;
                    $scope.titleItems = data.titleItems || null;
                    $scope.items = data.items || null;

                    $("#modalAlertConfirm").modal();
                });

                $scope.confirmCancel = function(){                    
                    if($scope.callCancel) $scope.callCancel($scope.databack);
                    $("#modalAlertConfirm").modal("hide");
                }

                $scope.confirmSuccess = function(){
                    if($scope.callSuccess) $scope.callSuccess($scope.databack);
                    $("#modalAlertConfirm").modal("hide");
                }

                $scope.confirmDanger = function(){
                    if($scope.callDanger) $scope.callDanger($scope.databack);
                    $("#modalAlertConfirm").modal("hide");
                }

                $scope.confirmWarning = function(){
                    if($scope.callWarning) $scope.callWarning($scope.databack);
                    $("#modalAlertConfirm").modal("hide");
                }
            })
        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush