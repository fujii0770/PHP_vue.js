<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">経費仕訳詳細</h4>
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
                                            <label for="t_app_id" class="col-md-3 col-sm-3 col-12 text-right-lg">会社申請番号:</label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="t_app_id"><% item.t_app_id %></p>
                                            </div>
                                            <label for="circular_status" class="col-md-3 col-sm-3 col-12 text-right-lg">状況:</label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="circular_status"><% circularDispStatus[item.circular_status] %></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <label for="purpose_name" class="col-md-3 col-sm-3 col-12 text-right-lg">目的:</label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="purpose_name"><% item.purpose_name %></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-11 col-sm-11 col-11">
                                        <p id="suspay_amt">&nbsp;&nbsp;事前仮払金額:&nbsp;<% item.suspay_amt %>円　ー　精算金額:&nbsp;<% item.eps_amt %>円　＝　過不足金額:&nbsp;<% item.eps_diff %>円</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="suspay_date" class="col-2 text-right-lg">仮払日:</label>
                                    <div class="col-md-3 col-sm-3 col-3">
                                        <p id="suspay_date"><% item.suspay_date %></p>
                                    </div>
                                    <label for="diff_date" class="col-md-3 col-sm-3 col-3 text-right-lg">過不足金精算日:</label>
                                    <div class="col-md-2 col-sm-2 col-2">
                                        <p id="diff_date"><% item.diff_date %></p>
                                    </div>
                                </div>

                                <div class="border" style="padding:1px;"></div>

                                <div class="row">
                                    <div class="col-8">
                                        <div class="row">
                                            <label for="wtsm_name" class="col-3 text-right-lg">用途:</label>
                                            <div class="col-3">
                                                <p id="wtsm_name"><% item.wtsm_name %></p>
                                            </div>
                                            <label for="expected_pay_amt" class="col-2 text-right-lg">金額:</label>
                                            <div class="col-3">
                                                <p id="expected_pay_amt"><% item.expected_pay_amt %></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label for="expected_pay_date" class="col-3 text-right-lg">支払日:</label>
                                            <div class="col-3">
                                                <p id="expected_pay_date"><% item.expected_pay_date %></p>
                                            </div>
                                            <label for="numof_ppl" class="col-2 text-right-lg">人数:</label>
                                            <div class="col-3">
                                                <p id="numof_ppl"><% item.numof_ppl %></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label for="remarks" class="col-3 text-right-lg">詳細:</label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <p id="remarks"><% item.remarks %></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 " >
                                        <div class="row border_style">
                                             <label >添付ファイル:</label>
                                             <ul class="box">
                                                    <li ng-repeat="(key, item2) in items2 track by $index" ><A href="" ng-click="download(item2.id)"><% item2.original_file_name %></A></li>
                                                </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_UPDATE])
                        <div class="card mt-0">
                            <div class="card-header">経費仕訳情報更新</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="row">
                                        <div class="form-group">
                                            <label for="rec_date" class="control-label">計上日 <span style="color: red">*</span></label>
                                            <div>
                                                <input type="date" required class="form-control" id="rec_date" ng-model="item3.rec_date" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <label for="debit_account" class="control-label">借方</label>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label for="debit_account" class="control-label">勘定科目</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listAccount, 'debit_account', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item3.debit_account', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_subaccount" class="control-label">補助科目</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item3.debit_subaccount" id="debit_subaccount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_debit_amount" class="control-label">金額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item3.debit_amount" id="debit_amount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_tax_div" class="control-label">税区分</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::TAX_DIV, 'debit_tax_div', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item3.debit_tax_div', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_tax" class="control-label">税額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item3.debit_tax" id="debit_tax" ng-readonly="false"/>
                                        </div>
                                    </div>

                                    <label for="credit_account" class="control-label">貸方</label>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label for="credit_account" class="control-label">勘定科目</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listAccount, 'credit_account', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item3.credit_account', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_subaccount" class="control-label">補助科目</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item3.credit_subaccount" id="credit_subaccount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_credit_amount" class="control-label">金額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item3.credit_amount" id="credit_amount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_tax_div" class="control-label">税区分</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::TAX_DIV, 'credit_tax_div', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item3.credit_tax_div', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_tax" class="control-label">税額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item3.credit_tax" id="credit_tax" ng-readonly="false"/>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="journal_remarks">摘要</label>
                                        <div>
                                            <input type="text" autocomplete="off" class="form-control" ng-model="item3.remarks" id="journal_remarks" ng-readonly="false"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end btn-save">
                                </div>
                            </div>
                        </div>
                        @endcanany

                        @canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_CREATE])
                        <button type="button" class="btn btn-success" ng-click="add_flg_click()">
                            追加
                        </button>
                        <div class="card mt-0">
                            <div class="card-header" >経費仕訳情報登録</div>
                            <div class="card-body" ng-if="add_flg">
                                <div class="form-group">

                                    <div class="row">
                                        <div class="form-group">
                                            <label for="rec_date" class="control-label">計上日 <span style="color: red">*</span></label>
                                            <div>
                                                <input type="date" required class="form-control" id="rec_date" ng-model="item4.rec_date" ng-readonly="false"/>
                                            </div>
                                        </div>
                                    </div>

                                    <label for="debit_account" class="control-label">借方</label>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label for="debit_account" class="control-label">勘定科目</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listAccount, 'debit_account', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item4.debit_account', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_subaccount" class="control-label">補助科目</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item4.debit_subaccount" id="debit_subaccount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_debit_amount" class="control-label">金額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item4.debit_amount" id="debit_amount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_tax_div" class="control-label">税区分</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::TAX_DIV, 'debit_tax_div', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item4.debit_tax_div', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="debit_tax" class="control-label">税額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item4.debit_tax" id="debit_tax" ng-readonly="false"/>
                                        </div>
                                    </div>

                                    <label for="credit_account" class="control-label">貸方</label>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label for="credit_account" class="control-label">勘定科目</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listAccount, 'credit_account', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item4.credit_account', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_subaccount" class="control-label">補助科目</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item4.credit_subaccount" id="credit_subaccount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_credit_amount" class="control-label">金額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item4.credit_amount" id="credit_amount" ng-readonly="false"/>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_tax_div" class="control-label">税区分</label>
                                            <div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::TAX_DIV, 'credit_tax_div', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item4.credit_tax_div', 'ng-readonly'=>"readonly"]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group col">
                                            <label for="credit_tax" class="control-label">税額</label>
                                                <input type="text" autocomplete="off" class="form-control" ng-model="item4.credit_tax" id="credit_tax" ng-readonly="false"/>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="journal_remarks">摘要</label>
                                        <div>
                                            <input type="text" autocomplete="off" class="form-control" ng-model="item4.remarks" id="journal_remarks" ng-readonly="false"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end btn-save">
                                </div>
                            </div>
                        </div>
                        @endcanany

                        <div class="message message-info"></div>
                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" ng-click="cancelAttendance()">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                            @canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_UPDATE,PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_CREATE])
                            <button type="button" class="btn btn-success" ng-click="saveTJarnal()" ng-if="item3.id">
                                <i class="far fa-save"></i>編集
                            </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_DELETE])
                                <button type="button" class="btn btn-danger" ng-click="deleteDetail()" ng-if="item3.id"><i class="fas fa-trash-alt"></i> 削除</button>
                            @endcanany
                        </div>
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
        .box{
            height: 110px;
            width:  300px;
            overflow-y: auto;
        }
    </style>
@endpush
@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {}; //ヘッダー用
                $scope.items2 = {}; //添付ファイル用
                $scope.item3 = {}; //更新用
                $scope.item4 = {}; //追加用
                $scope.add_flg= 0; //追加覧表示制御
                $scope.option_limit = [10, 50, 100];
                $scope.itemsAdmin = [];
                $scope.itemAdmin = {id: 0};
                $scope.count = 0;
                $scope.currentPage = 0;
                $scope.circularDispStatus = {!! json_encode(\App\Http\Utils\AppUtils::CIRCULAR_DISP_STATUS) !!};

                $rootScope.$on("openDetails", function(event,data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.add_flg= 0
                    hideMessages();

                    $scope.item4 = {};
                    $scope.item.eps_t_app_id      = data.eps_t_app_id;
                    $scope.item.eps_t_app_item_id = data.eps_t_app_item_id;

                    $http.post(link_ajax + "/show/" + data.id, $scope.item)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.item = event.data.item;
                            $scope.items2 = event.data.item2;

                            $scope.item3 = event.data.item3;
                                var arr = $scope.item3.rec_date.split('-') //yyyy-mm-dd形式をyyyy/mm/dd形式に変換
                                $scope.item3.rec_date = new Date(arr[0], arr[1] - 1, arr[2]);
                                if(event.data.item3.debit_tax_div !== null){
                                    $scope.item3.debit_tax_div  = event.data.item3.debit_tax_div.toString();
                                }
                                if(event.data.item3.credit_tax_div !== null){
                                    $scope.item3.credit_tax_div = event.data.item3.credit_tax_div.toString();
                                }
                            $scope.item4.remarks = '申請番号:' + $scope.item3.eps_t_app_id;
                        }
                    });
                    $("#modalDetailItem").modal();
                });

                $scope.detailsRecord = function (eps_t_app_id,eps_t_app_item_id) {
                    $rootScope.$emit("openUserDetail2",{eps_t_app_item_id:eps_t_app_item_id,eps_t_app_id:eps_t_app_id});
                    $("#modalDetailItem").modal('hide');
                };

                $scope.saveTJarnal = function(callSuccess){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");

                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    if(!$scope.item.eps_journal_no){
                                        $scope.item.eps_journal_no = event.data.eps_journal_no;
                                    }
                                    $scope.item3.version = event.data.version;
                                    if(event.data.status == 'warning'){
                                        $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 100000));
                                    }else{
                                        $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                    hasChange = true;
                                    if($scope.add_flg){
                                        location.reload();
                                    }
                                    if(callSuccess) callSuccess();
                                }
                        };
                        //登録
                        var item_copy_add;
                        if($scope.add_flg){
                                item_copy_add = Object.assign({}, $scope.item4);//計上日はpost/putする時に型変換が必要なので、コピー先で変換し、それをpost/putする。変換したものは画面に反映させないため。
                                var date_add = $scope.item4.rec_date;
                                item_copy_add.rec_date = date_add.getFullYear() + '/' + ('0' + (date_add.getMonth() + 1)).slice(-2) + '/' +('0' + date_add.getDate()).slice(-2) ;
                                item_copy_add.eps_t_app_id = $scope.item3.eps_t_app_id ;
                                item_copy_add.eps_t_app_item_id = $scope.item3.eps_t_app_item_id ;
                        }
                        //更新
                        var item_copy = Object.assign({}, $scope.item3);//計上日はpost/putする時に型変換が必要なので、コピー先で変換し、それをpost/putする。変換したものは画面に反映させないため。
                        var date = $scope.item3.rec_date;
                        if(date){
                            item_copy.rec_date = date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' +('0' + date.getDate()).slice(-2) ;
                        }
                        $http.put(link_ajax + "/" + $scope.item3.id, {item: item_copy, item_add: item_copy_add, eps_t_app_id: $scope.item3.eps_t_app_id, eps_t_app_item_id: $scope.item3.eps_t_app_item_id})
                            .then(saveSuccess);
                    }else{
                        $(".form_edit")[0].reportValidity()
                    }
                };
                $scope.deleteDetail = function () {
                    event.preventDefault();
                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '仕訳を削除します。',
                                    btnSuccess:'はい',
                                    callSuccess: function(){
                                        $rootScope.$emit("showLoading");
                                        $http.post(link_ajax + "/delete/" + $scope.item3.id)
                                            .then(function (event) {
                                                $rootScope.$emit("hideLoading");
                                                if (event.data.status == false) {
                                                    $(".message-info").append(showMessages(event.data.message, 'danger', 10000));
                                                } else {
                                                    $(".message-info").append(showMessages(event.data.message, 'success', 10000));
                                                    location.reload();
                                                }
                                            });
                                    }
                                });
                };

                $scope.add_flg_click = function(){
                    $scope.add_flg = !$scope.add_flg;
                };

                $scope.download = function(id){
                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'添付ファイル予約',
                        message:'添付ファイルのダウンロード予約をします。',
                        btnSuccess:'はい',
                        size:'lg',
                        callSuccess: function(inputData){
                            // ダウンロード予約はexpense/t_appを呼ぶ(申請一覧と同じもの)
                            $http.post(link_ajax_reserve + "/reserve", {cid: id})
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
