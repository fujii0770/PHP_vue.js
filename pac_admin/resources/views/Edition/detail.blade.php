<style>
    input, textarea {
        outline: none;
    }
</style>
<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!item.id">契約Edition登録</h4>
                        <h4 class="modal-title" ng-if="item.id">契約Edition更新</h4>
                        <button type="button" class="close" data-dismiss="modal" ng-click="cancelEdit()">&times;
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">
                            <button type="button" class="btn btn-success" ng-click="saveEdition()" ng-if="!item.id">
                                <i class="far fa-save"></i> 登録
                            </button>
                            <button type="button" class="btn btn-success" ng-click="saveEdition()" ng-if="item.id" style="margin-right:5px">
                                <i class="far fa-save"></i> 更新
                            </button>
                            <button type="submit" class="btn btn-danger" ng-click="remove()" ng-if="item.id">
                                <i class="fas fa-trash-alt"></i> 削除
                            </button>
                        </div>
                        <div class="message message-info"></div>
                        <div class="card">
                            <div class="card-header">契約Edition設定</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label
                                               class="col-md-3 col-sm-3 col-12 text-right">選択ID<span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="number" class="form-control" ng-model="item.contract_edition" id="contract_edition" ng-readonly="item.id" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="contract_edition" class="col-md-3 col-sm-3 col-12 text-right">契約Edition<span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="text" class="form-control" ng-model="item.contract_edition_name" id="contract_edition_name" /></div>
                                        <label for="state_flg" class="col-md-3 col-sm-3 col-12 text-right">契約状態</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="checkbox" ng-model="item.state_flg" id="state_flg" ng-true-value="1" ng-false-value="0" />
                                            <label for="state_flg">有効</label><!--state-->
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="memo" class="col-md-3 col-sm-3 col-12 text-right">備考</label>
                                        <div class="col-md-8 col-sm-9 col-12">
                                            <input type="text" class="form-control" ng-model="item.memo" id="memo" placeholder="備考" maxlength="20"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                        @include('Edition.list_edition')
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" ng-click="cancelEdit()">
                            <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('/js/validity-polyfill.js') }}"></script>
    <script>

        if (appPacAdmin) {
            appPacAdmin.controller('DetailController', function ($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.option_limit = [10, 50, 100];
                $scope.results = ['成功', '失敗'];
                $scope.checked = {};
                $scope.show_other_settings = false;
                $scope.show_gw_settings = false;
                $rootScope.$on("openNewEdition", function (event) {
                    hideMessages();
                    // 新規の場合、Standaradから編集
                    $http.get(link_ajax + "/" + 1)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            $scope.item = event.data.item;
                            $scope.item.id = 0;
                            $scope.item.contract_edition_name = '';
                            $scope.item.memo = '';
                            $scope.item.state_flg = 0;
                            $scope.item.contract_edition = '';

                        });
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openEditEdition", function (event, data) {
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    hideMessages();
                    $http.get(link_ajax + "/" + data.id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");

                            $scope.item_id = event.data.item.id;
                            $scope.item = event.data.item;

                        });
                    $("#modalDetailItem").modal();
                });

                $scope.ipFlgCheck = function () {
                    // 接続IP制限 -> 登録外IPのログイン許可
                    if ($scope.item.ip_restriction_flg == 0) {
                        $scope.item.permit_unregistered_ip_flg = 0;
                    }
                }

                $scope.boardFlgCheck = function(){
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.schedulerFlgCheck = function(){
                    if (!$scope.item.scheduler_flg){
                        // スケジューラー OFF に更新する場合、無制限(カレンダー連携含む)/購入数(カレンダー連携含む)/xxx連携にクリア
                        $scope.item.caldav_flg = 0;
                        $scope.item.scheduler_limit_flg = 0;
                        $scope.item.scheduler_buy_count = 0;
                        $scope.item.caldav_limit_flg = 0;
                        $scope.item.caldav_buy_count = 0;
                        $scope.item.google_flg = 0;
                        $scope.item.outlook_flg = 0;
                        $scope.item.apple_flg = 0;
                        $scope.item.shared_scheduler_flg = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }

                $scope.caldavFlgCheck = function(){
                    // カレンダー連携 OFF 更新する時、無制限/購入数/xxx連携クリア
                    if(!$scope.item.caldav_flg){
                        $scope.item.caldav_limit_flg =0;
                        $scope.item.caldav_buy_count = 0;
                        $scope.item.google_flg = 0;
                        $scope.item.outlook_flg = 0;
                        $scope.item.apple_flg = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.fileMailFlgCheck = function(){
                    // ファイルメール便 OFF 更新する時、無制限/購入数クリア
                    if(!$scope.item.file_mail_flg){
                        $scope.item.file_mail_limit_flg =0;
                        $scope.item.file_mail_buy_count = 0;
                        $scope.item.file_mail_extend_flg = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.attendanceFlgCheck = function(){
                    // タイムカード OFF 更新する時、無制限/購入数クリア
                    if(!$scope.item.attendance_flg){
                        $scope.item.attendance_limit_flg =0;
                        $scope.item.attendance_buy_count = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.faqBoardFlgCheck = function(){
                    // タイムカード OFF 更新する時、無制限/購入数クリア
                    if(!$scope.item.faq_board_flg){
                        $scope.item.faq_board_limit_flg =0;
                        $scope.item.faq_board_buy_count = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.toDoListFlgCheck = function(){
                    // タイムカード OFF 更新する時、無制限/購入数クリア
                    if(!$scope.item.to_do_list_flg){
                        $scope.item.to_do_list_limit_flg =0;
                        $scope.item.to_do_list_buy_count = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.addressListFlgCheck = function(){
                    // タイムカード OFF 更新する時、無制限/購入数クリア
                    if(!$scope.item.address_list_flg){
                        $scope.item.address_list_limit_flg =0;
                        $scope.item.address_list_buy_count = 0;
                    }
                    // マイページ表示制御
                    $scope.portalFlgCheck()
                }
                $scope.portalFlgCheck = function(){
                    // マイページ表示制御
                    if($scope.item.board_flg == 1
                        || $scope.item.scheduler_flg == 1
                        || $scope.item.file_mail_flg == 1
                        || $scope.item.faq_board_flg == 1
                        || $scope.item.to_do_list_flg == 1
                        || $scope.item.attendance_flg == 1
                        || $scope.item.address_list_flg == 1){
                        $scope.item.portal_flg =1;
                    }else{
                        $scope.item.portal_flg =0;
                    }
                }
                $scope.storeCheck = function () {
                    if ($scope.item.scheduler_flg && $scope.item.scheduler_limit_flg == 0 && $scope.item.scheduler_buy_count == 0) {
                        $("#modalDetailItem .message-info").append(showMessages(['スケジューラーの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.caldav_flg && $scope.item.caldav_limit_flg == 0 && $scope.item.caldav_buy_count == 0) {
                        $("#modalDetailItem .message-info").append(showMessages(['CalDAVの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.attendance_flg && $scope.item.attendance_limit_flg == 0 && $scope.item.attendance_buy_count == 0) {
                        $("#modalDetailItem .message-info").append(showMessages(['タイムカードの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.file_mail_flg && $scope.item.file_mail_limit_flg == 0 && $scope.item.file_mail_buy_count == 0) {
                        $("#modalDetailItem .message-info").append(showMessages(['ファイルメール便の購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.faq_board_flg && $scope.item.faq_board_limit_flg == 0 && $scope.item.faq_board_buy_count == 0) {
                        $("#modalDetailItem .message-info").append(showMessages(['サポート掲示板の購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.to_do_list_flg && $scope.item.to_do_list_limit_flg == 0 && ($scope.item.to_do_list_buy_count <= 0 || !(/(^\d*$)/.test($scope.item.to_do_list_buy_count)))) {
                        $("#modalDetailItem .message-info").append(showMessages(['ToDoリストの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.address_list_flg && $scope.item.address_list_limit_flg == 0 && $scope.item.address_list_buy_count == 0) {
                        $("#modalDetailItem .message-info").append(showMessages(['利用者名簿の購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    return true;
                };
                $scope.saveEdition = function (callSuccess) {
                    if($scope.storeCheck()){
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function (event) {
                            $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    if(!$scope.item.id){
                                        $scope.item.id = event.data.id;
                                    }
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                    hasChange = true;
                                    if(callSuccess) callSuccess();
                                }
                        };
                        if (!$scope.item.id) {
                            $http.post(link_ajax, {item: $scope.item})
                                .then(saveSuccess);
                        } else {
                            $http.put(link_ajax + "/" + $scope.item.id, {item: $scope.item})
                                .then(saveSuccess);
                        }
                        $(".form_edit")[0].reportValidity()
                    }
                };
                $scope.clickOtherSettings = function () {
                    if ($scope.show_other_settings) {
                        $scope.show_other_settings = false;
                        $("#other_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_other_settings = true;
                        $("#other_settings").attr('class','fas fa-angle-up');
                    }
                };
                $scope.clickGWSettings = function () {
                    if ($scope.show_gw_settings) {
                        $scope.show_gw_settings = false;
                        $("#GW_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_gw_settings = true;
                        $("#GW_settings").attr('class','fas fa-angle-up');
                    }
                };
                $scope.changeTemplateApprovalRouteFlg = function () {
                    if ($("#template_approval_route_flg").is(":checked")) {
                        $scope.item.template_route_flg = 1;
                    }
                };
                $scope.changeTemplateRouteFlg = function () {
                    if (!$("#template_route_flg").is(":checked")) {
                        $scope.item.template_approval_route_flg = 0;
                    }
                };
                $scope.remove = function () {
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '契約Editionを削除します。よろしいですか？',
                            btnDanger: 'はい',
                            databack: $scope.item.id,
                            callDanger: function (id) {
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + id)
                                    .then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'warning', 10000));
                                            location.reload();
                                        }
                                    });
                            }
                        });
                };
                $scope.cancelEdit = function () {
                    location.reload();
                };
            })
        }
    </script>
@endpush
