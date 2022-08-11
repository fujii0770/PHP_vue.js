<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">勤務詳細</h4>
                        <button type="button" class="close" data-dismiss="modal" ng-click="cancelAttendance()">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">
                            @canany([PermissionUtils::PERMISSION_SCHEDULE_LIST_SETTING_UPDATE])
                            <button type="button" class="btn btn-success" ng-click="saveAttendance()">
                                <i class="far fa-save"></i> 更新
                            </button>
                            @endcanany
                        </div>
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header"><% item.work_date_str %></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="user_name" class="col-md-3 col-sm-3 col-12 text-right-lg">氏名 </label>
                                                <div class="col-md-8 col-sm-8 col-24">
                                                    <p id="user_name"><% item.user_name %></p>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="email" class="col-md-3 col-sm-3 col-12 text-right-lg">メールアドレス </label>
                                                    <div class="col-md-8 col-sm-8 col-24">
                                                        <p id="email"><% item.email %></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="department_name" class="col-md-3 col-sm-3 col-12 text-right-lg">部署 </label>
                                                    <div class="col-md-8 col-sm-8 col-24">
                                                        <p id="email"><% item.department_name %></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="position_name" class="col-md-3 col-sm-3 col-12 text-right-lg">役職 </label>
                                                    <div class="col-md-8 col-sm-8 col-24">
                                                        <p id="email"><% item.position_name %></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="work_start_time" class="col-md-3 col-sm-3 col-12 control-label">出勤時間</label>
                                                    <div class="col-md-4 col-sm-4 col-12">
                                                        <input type="time" class="form-control" ng-model="item.work_start_time" id="work_start_time" ng-readonly="false"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="work_end_time" class="col-md-3 col-sm-3 col-12 control-label">退勤時間</label>
                                                    <div class="col-md-4 col-sm-4 col-12">
                                                        <input type="time" class="form-control" ng-model="item.work_end_time" id="work_end_time" ng-readonly="false"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="earlyleave_flg" class="col-md-3 col-sm-3 col-24 control-label">早退</label>
                                                    <div class="col-md-3 col-sm-3 col-12">
                                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::EARLYLEAVE_FLG, 'earlyleave_flg', Request::get('earlyleave_flg', '') ,null,
                                                        ['class'=> 'form-control', 'ng-model' =>'item.earlyleave_flg']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="paid_vacation_flg" class="col-md-3 col-sm-3 col-24 control-label">有給</label>
                                                    <div class="col-md-3 col-sm-3 col-12">
                                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::PAID_VACATION_FLG, 'paid_vacation_flg', Request::get('paid_vacation_flg', '') ,null,
                                                        ['class'=> 'form-control', 'ng-model' =>'item.paid_vacation_flg']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="sp_vacation_flg" class="col-md-3 col-sm-3 col-24 control-label">特休</label>
                                                    <div class="col-md-3 col-sm-3 col-12">
                                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::SP_VACATION_FLG, 'sp_vacation_flg', Request::get('sp_vacation_flg', '') ,null,
                                                        ['class'=> 'form-control', 'ng-model' =>'item.sp_vacation_flg']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="day_off_flg" class="col-md-3 col-sm-3 col-24 control-label">代休</label>
                                                    <div class="col-md-3 col-sm-3 col-12">
                                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::DAY_OFF_FLG, 'day_off_flg', Request::get('day_off_flg', '') ,null,
                                                        ['class'=> 'form-control', 'ng-model' =>'item.day_off_flg']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="approval_state" class="col-md-3 col-sm-3 col-24 control-label">承認</label>
                                                    <div class="col-md-3 col-sm-3 col-12">
                                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::APPROVAL_STATE, 'approval_state', Request::get('approval_state', '') ,null,
                                                        ['class'=> 'form-control', 'ng-model' =>'item.approval_state']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="work_detail" class="col-md-3 col-sm-3 col-12 control-label">作業内容</label>
                                                    <div class="col-md-8 col-sm-4 col-12">
                                                       <textarea type="text" class="form-control" ng-model="item.work_detail" id="work_detail" rows="3" ng-readonly="false"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="memo" class="col-md-3 col-sm-3 col-12 control-label">備考</label>
                                                    <div class="col-md-8 col-sm-4 col-12">
                                                       <textarea type="text" class="form-control" ng-model="item.memo" id="memo" rows="3" ng-readonly="false"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <label for="admin_memo" class="col-md-3 col-sm-3 col-12 control-label">管理者コメント</label>
                                                    <div class="col-md-8 col-sm-4 col-12">
                                                        <textarea type="text" class="form-control" ng-model="item.admin_memo" id="admin_memo" rows="3" ng-readonly="false"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="message message-info mt-3"></div>

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

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};

                $rootScope.$on("openDetailsAttendance", function(event,data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    hideMessages();

                    $http.get(link_ajax + "/" + data)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.item = event.data.item;

                            //  こんなのもっと簡単にできないの？
                            let str = event.data.item.work_date;
                            let dt = new Date(parseInt(str.substr(0,4)), parseInt(str.substr(4,2)) -1, parseInt(str.substr(6,2)));
                            $scope.item.work_date_str = (dt.getFullYear() + '年' +('0' + (dt.getMonth()+1)).slice(-2)+ '月' +  ('0' + dt.getDate()).slice(-2)+ '日');

                            //  Dateに変える（秒いらないよね？）
                            $scope.item.work_start_time = new Date($scope.item.work_start_time);
                            $scope.item.work_start_time.setUTCSeconds(0);
                            $scope.item.work_end_time = new Date($scope.item.work_end_time);
                            $scope.item.work_end_time.setUTCSeconds(0);

                            //  buildSelectが文字列期待してるから？
                            $scope.item.earlyleave_flg = event.data.item.earlyleave_flg.toString();
                            $scope.item.paid_vacation_flg = event.data.item.paid_vacation_flg.toString();
                            $scope.item.sp_vacation_flg = event.data.item.sp_vacation_flg.toString();
                            $scope.item.day_off_flg = event.data.item.day_off_flg.toString();
                            $scope.item.approval_state = event.data.item.approval_state.toString();
                        }
                    });
                    $("#modalDetailItem").modal();
                });

                $scope.saveAttendance = function(callSuccess){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");

                        var saveSuccess = function(event){
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
                        $http.put(link_ajax + "/" + $scope.item.id, {item: $scope.item}).then(saveSuccess);
                    }else{
                        $(".form_edit")[0].reportValidity()
                    }
                };

            });
        }
    </script>
@endpush
