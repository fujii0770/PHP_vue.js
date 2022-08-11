<div ng-controller="DetailFormExpenseController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem2">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">経費申請詳細</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info2"></div>

                        <div class="card mt-3">
                            <div class="card-header">経費申請詳細</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">用途：　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p id="wtsm_name"><% item.wtsm_name %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">支払日：　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p id="expected_pay_date"><% item.expected_pay_date %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">金額：　　　</label>
                                            <div class="col-md-3 col-sm-3 col-3 disable">
                                                <p id="amount"><% item.amount %>&nbsp;円</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">人数：　　　</label>
                                            <div class="col-md-2 col-sm-2 col-2 disable">
                                                <p id="numof_ppl"><% item.numof_ppl %>&nbsp;名</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10 text-right-lg">詳細：　　　</label>
                                            <div class="col-md-9 col-sm-9 col-26 disable">
                                                <p id="remarks"><% item.remarks %></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-md-2 col-sm-2 col-10">添付ファイル:</label>
                                            <ul class="app_box" ng-show="items2!=''">
                                                <li ng-repeat="(key, item2) in items2 track by $index"><A href=""ng-click="download2(item2.id)"><% item2.original_file_name %></A></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <div class="col-lg-7">
                                <button type="button" class="btn btn-default" ng-click="cancelModal()">
                                    <i class="fas fa-times-circle"></i> 閉じる
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

@push('styles_after')
    <style>
        .disable{
            opacity: 0.75;
            user-select: none;
        }
        .p_style{
            padding-top:4px;
            text-align: right;
        }
    </style>
@endpush
@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailFormExpenseController', function ($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.items2 = {};
                $scope.edit_flg = false;
                $scope.itemRequest = {id: 0};

                $rootScope.$on("openAppDetail2", function(event,data){
                    $scope.edit_flg = false;
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    $scope.item.eps_app_item_no = data.eps_app_item_no;
                    hideMessages();
                    $http.post(link_ajax_detail2 + "/" + $scope.item.id,$scope.item)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem2 .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.items2 = event.data.item2;
                            }
                        });
                    $("#modalDetailItem2").modal();
                });

                $scope.download2 = function(id){
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
                                    $(".message-info2").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-info2").append(showMessages(event.data.message, 'success', 10000));

                                }
                            });
                        }
                    });
                };

                $scope.cancelModal = function(){
                    // $rootScope.$emit("openDetailsAttendance",1);
                    $("#modalDetailItem").modal('show');
                    $("#modalDetailItem2").modal('hide');
                }
                $scope.download = function(id){
                        $rootScope.$emit("showMocalConfirm",
                        {
                            title:'添付ファイル予約',
                            message:'添付ファイルのダウンロード予約をします。',
                            btnSuccess:'はい',
                            size:'lg',
                            callSuccess: function(inputData){
                                $http.post(link_ajax+"/reserve", {cids: [id]})
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $("#modalDetailItem2 .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        $("#modalDetailItem2 .message-info").append(showMessages(event.data.message, 'success', 10000));

                                    }
                                });
                            }
                        });
                };

            })
        }
    </script>
@endpush
