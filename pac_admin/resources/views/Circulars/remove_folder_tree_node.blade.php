@php
    $user = \Auth::user();
    if ($user->mst_company_id){
        $itemsFolder = App\Http\Utils\LongTermFolderUtils::getLongTermFolderTree($user->mst_company_id);
    }else{
        $itemsFolder = null;
    }
@endphp
<div ng-controller="RemoveFolderTreeConfirmController">

    <div class="modal modal-add-stamp mt-5" id="RemoveFolderTreeConfirm" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-<% size %>" data-backdrop="static" data-keyboard="false">
            <form class="modal-content  p-4">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title control-label">フォルダを選択</h4>
                </div>
                <!-- Modal body -->
                <div class="modal-body  " ng-if="message" ng-bind-html="message"></div>
                <div class="col"> </div>
                @csrf
                <div class="message message-info"></div>
                @if($itemsFolder)
                    <div class="col-md-12">
                        <ul class="items tree mt-3" id="sortable_depart" style="overflow-x: auto;">
                            <li class="tree-node parent">
                                <div class="name " data-id="0" data-longTermFolder="company_name" data-parent="NULL" ng-class="{selected: selectedID == 0}" ng-click="selectRow(0)">
                                            <span class="arrow">
                                                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i>
                                            </span>
                                    <i class="far fa-folder"></i>
                                    <% company_name %>
                                </div>
                                <ul class="items">
                                    @foreach ($itemsFolder as $item)
                                        @include('Circulars.folder_tree_node',['itemFolder' => $item])
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div>
                @endif

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
            </form>
        </div>
    </div>
</div>

</div>


@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        if(appPacAdmin){
            appPacAdmin.controller('RemoveFolderTreeConfirmController', function($scope, $rootScope,$timeout){
                $rootScope.$on("showRemoveFolderTreeConfirm", function(event, data){
                    data = data || {};
                    $scope.title = data.title || "";
                    $scope.message = data.message || "";
                    $scope.btnCancel = data.btnCancel || "キャンセル";
                    $scope.btnAdd = data.btnAdd || '　＋　';
                    $scope.btnSuccess = data.btnSuccess || null;
                    $scope.btnDanger = data.btnDanger || null;
                    $scope.btnWarning = data.btnWarning || null;
                    $scope.callCancel = data.callCancel || null;
                    $scope.callSuccess = data.callSuccess || null;
                    $scope.callDanger = data.callDanger || null;
                    $scope.callWarning = data.callWarning || null;
                    $scope.databack = data.databack || null;
                    $scope.size = data.size || "sm";
                    $scope.company_name = data.company_name || '企業名';
                    $scope.selectedID = 0;
                    $("#RemoveFolderTreeConfirm").modal();
                });

                $scope.confirmCancel = function(){
                    if($scope.callCancel) $scope.callCancel($scope.databack);
                    $("#RemoveFolderTreeConfirm").modal("hide");
                    $scope.selectRow(0);
                }
                $scope.confirmSuccess = function(){
                    if ($scope.selectedID == null){
                        $("#RemoveFolderTreeConfirm .message-info").append(showMessages(['フォルダを選択してください。'], 'danger', 10000));
                        return;
                    }
                    $scope.databack = { 'folder_id': $scope.selectedID};
                    if($scope.callSuccess) $scope.callSuccess($scope.databack);
                    $("#RemoveFolderTreeConfirm").modal("hide");
                    $scope.selectRow(0);
                }

                $scope.confirmDanger = function(){
                    if($scope.callDanger) $scope.callDanger($scope.databack);
                    $("#RemoveFolderTreeConfirm").modal("hide");
                    $scope.selectRow(0);
                }

                $scope.confirmWarning = function(){
                    if($scope.callWarning) $scope.callWarning($scope.databack);
                    $("#RemoveFolderTreeConfirm").modal("hide");
                    $scope.selectRow(0);
                }

                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush

@push('styles_after')
    <style>
        .name{
            background-color: #ffffff;
        }
        .name.selected{
            background-color: #beebff;
        }

    </style>
@endpush