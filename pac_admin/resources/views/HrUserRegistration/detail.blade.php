<style>
    .fallbackTimePickerEnd{
        display:none;
    }
    .float_left_input{
        width:30% !important;
        display: unset;
    }
</style>
<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!item.hr_info_id">利用ユーザ詳細情報登録</h4>
                        <h4 class="modal-title" ng-if="item.hr_info_id">利用ユーザ詳細情報更新</h4>
                    </div>
                
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">
                            @canany([PermissionUtils::PERMISSION_HR_USER_SETTING_CREATE])
                            <button type="button" class="btn btn-success" ng-click="saveHrUser()" ng-if="!item.hr_info_id">
                                <i class="far fa-save"></i>登録
                            </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_HR_USER_SETTING_UPDATE])
                            <button type="button" class="btn btn-success" ng-click="saveHrUser()" ng-if="item.hr_info_id">
                                <i class="far fa-save"></i>更新
                            </button>
                            @endcanany
                        </div>
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!item.hr_info_id">利用ユーザ詳細情報登録</div>
                            <div class="card-header" ng-if="item.hr_info_id">利用ユーザ詳細情報更新</div>
                                <div class="card-body">
                                    <div class="form-group">

                                        <div class="form-group">
                                            <div class="row">
                                                <label for="user_name" class="col-md-3 col-sm-3 col-12 text-right-lg">氏名 </label>
                                                <div class="col-md-8 col-sm-8 col-24">
                                                    <p id="user_name"><% item.user_name %></p>
                                                </div>
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
                                                    <p id="department_name"><% item.department_name %></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                 <label for="position_name" class="col-md-3 col-sm-3 col-12 text-right-lg">役職 </label>
                                                 <div class="col-md-8 col-sm-8 col-24">
                                                     <p id="position_name"><% item.position_name %></p>
                                                 </div>
                                             </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label for="hr_user_flg" class="col-md-3 col-sm-3 col-24 control-label">HR利用</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::HR_USER_FLG, 'hr_user_flg', Request::get('hrUserFlg', '') ,null,
                                                    ['class'=> 'form-control','required', 'ng-model' =>'item.hr_user_flg']) !!}
                                                </div>
                                            </div>
                                        </div>
  
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="assigned_company" class="col-md-3 col-sm-3 col-12 control-label">配属現場名 <span style="color: red">*</span></label>
                                                <div class="col-md-8 col-sm-4 col-24">
                                                <input type="text" required autocomplete="off" class="form-control" ng-model="item.assigned_company" id="assigned_company" ng-readonly="false"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="assigned_company" class="col-md-3 col-sm-3 col-12 control-label">就労時間設定 </label>
                                                <div class="col-md-8 col-sm-4 col-24">
                                                    <select ng-model="item.workselect" class="form-control">
                                                        <option value=""></option>
                                                        <option ng-selected="item.send"  ng-repeat="(key,item) in working_hours_id" ng-value="<% key %>"
                                                           class="work_class<% key %>" ><% item %></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="Regulations_work_start_time" class="col-md-3 col-sm-3 col-12 control-label">規定業務時刻 </label>
                                                <div class="col-md-8 col-sm-8 col-12 flex items-center">
                                                    <div class="float_left_input">
                                                        <input type="time"  class="form-control" id="start_time" ng-model="item.Regulations_work_start_time" id="Regulations_work_start_time" ng-readonly="work_a"/>
                                                        <div class="fallbackTimePickerStart" >
                                                            <span>
                                                                <select id="start_hour" name="start_hour">
                                                                </select>
                                                                <label for="start_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="start_minute" name="start_minute">
                                                                </select>
                                                                <label for="start_minute">分</label>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    &nbsp;~&nbsp;
                                                    <div class="float_left_input">
                                                        <input type="time"  class="form-control" id="end_time" ng-model="item.Regulations_work_end_time" id="Regulations_work_end_time" ng-readonly="work_a"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="end_hour" name="end_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="end_minute" name="end_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label for="shift1_start_time" class="col-md-3 col-sm-3 col-12 control-label">シフト1時刻 </label>
                                                <div class="col-md-8 col-sm-8 col-12 flex items-center">
                                                    <div class="float_left_input">
                                                        <input type="time"  class="form-control" id="shift1_start_time" ng-model="item.shift1_start_time" ng-readonly="work_b"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="shift1_start_time_hour" name="shift1_start_time_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="shift1_start_time_minute" name="shift1_start_time_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>

                                                            </span>
                                                        </div>
                                                    </div>
                                                    &nbsp;~&nbsp;
                                                    <div class="float_left_input">
                                                        <input type="time"  class="form-control" id="shift1_end_time" ng-model="item.shift1_end_time"  ng-readonly="work_b"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="shift1_end_time_hour" name="shift1_end_time_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="shift1_end_time_minute" name="shift1_end_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>

                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label for="shift2_start_time" class="col-md-3 col-sm-3 col-12 control-label">シフト2時刻 </label>
                                                <div class="col-md-8 col-sm-8 col-12 flex items-center">
                                                    <div class="float_left_input">
                                                        <input type="time"  class="form-control" id="shift2_start_time" ng-model="item.shift2_start_time"  ng-readonly="work_b"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="shift2_start_time_hour" name="shift2_start_time_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="shift2_start_time_minute" name="shift2_start_time_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>

                                                            </span>
                                                        </div>
                                                    </div>
                                                    &nbsp;~&nbsp;
                                                    <div class="float_left_input">
                                                        <input type="time" class="form-control" id="shift2_end_time" ng-model="item.shift2_end_time"  ng-readonly="work_b"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="shift2_end_time_hour" name="shift2_end_time_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="shift2_end_time_minute" name="shift2_end_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>

                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="shift3_start_time" class="col-md-3 col-sm-3 col-12 control-label">シフト3時刻 </label>
                                                <div class="col-md-8 col-sm-8 col-12 flex items-center">
                                                    <div class="float_left_input">      
                                                        <input type="time"  class="form-control" id="shift3_start_time" ng-model="item.shift3_start_time"  ng-readonly="work_b"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="shift3_start_time_hour" name="shift3_start_time_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="shift3_start_time_minute" name="shift3_start_time_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>

                                                            </span>
                                                        </div>
                                                    </div>
                                                    &nbsp;~&nbsp;
                                                    <div class="float_left_input">
                                                        <input type="time"  class="form-control" id="shift3_end_time" ng-model="item.shift3_end_time"  ng-readonly="work_b"/>
                                                        <div class="fallbackTimePickerEnd" >
                                                            <span>
                                                                <select id="shift3_end_time_hour" name="shift3_end_time_hour">
                                                                </select>
                                                                <label for="end_hour">時</label>
                                                            </span>
                                                            <span>
                                                                <select id="shift3_end_time_minute" name="shift3_end_minute">
                                                                </select>
                                                                <label for="end_minute">分</label>

                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="Overtime_unit" class="col-md-3 col-sm-3 col-12 control-label">残業発生時間単位 <span style="color: red">*</span></label>
                                                <div class="col-md-2 col-sm-4 col-2">
                                                    <input type="text" required="" class="form-control" ng-model="item.Overtime_unit" id="Overtime_unit" ng-readonly="false">
                                                </div>
                                                <label for="Overtime_unit" required="" class="col-md-2 col-sm-2 col-2 control-label">分単位</label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label for="break_time" class="col-md-3 col-sm-3 col-12 control-label">休憩時間 <span style="color: red">*</span></label>
                                                <div class="col-md-2 col-sm-4 col-2">
                                                    <input type="text" required class="form-control" ng-model="item.break_time" id="break_time" ng-readonly="false"/>
                                                </div>
                                                <label for="break_time" class="col-md-2 col-sm-2 col-2 control-label">分単位</label>
                                            </div>
                                        </div>
                                    </div>
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
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};
                //work_input
                $scope.work_a=false;
                $scope.work_b=false;
                //IE対応start
                $scope.IeFlg = 0;
                var fallbackPickerStart = document.querySelector('.fallbackTimePickerStart');
                var fallbackPickerEnd = document.querySelector('.fallbackTimePickerEnd');

                var hourSelectStart = document.querySelector('#start_hour');
                var minuteSelectStart = document.querySelector('#start_minute');
                var hourSelectEnd = document.querySelector('#end_hour');
                var minuteSelectEnd = document.querySelector('#end_minute');

                // 最初はフォールバックを非表示にする
                fallbackPickerStart.style.display = 'none';
                fallbackPickerEnd.style.display = 'none';

                // 新しい日付入力が文字列入力にフォールバックされるかどうか
                var test = document.createElement('input');

                try {
                    test.type = 'time';
                } catch (e) {
                }
                // もし文字列入力になるならば、 if() {} ブロックの中のコードを実行する
                if(test.type === 'text') {
                    $scope.IeFlg = 1;
                    // ネイティブの日付選択を隠してフォールバック版を表示
                    document.getElementById("start_time").required = false;
                    document.getElementById("end_time").required = false;
                    document.getElementById("start_time").style.display ="none";
                    document.getElementById("end_time").style.display ="none";
                    fallbackPickerStart.style.display = 'block';
                    fallbackPickerEnd.style.display = 'block';

                    // 時と分を動的に生成する
                    populateHours();
                    populateMinutes();
                }
                function populateHours() {
                    // 時刻の <select> に営業時間の6時間分を生成する
                    for(var i = 0; i <= 23; i++) {
                        var option = document.createElement('option');
                        var option2 = document.createElement('option');
                        option.textContent = (i < 10) ? ("0" + i) : i;
                        option2.textContent = (i < 10) ? ("0" + i) : i;
                        hourSelectStart.appendChild(option);
                        hourSelectEnd.appendChild(option2);
                    }
                }
                function populateMinutes() {
                    // 分の <select> に1時間内の60分を生成する
                    for(var i = 0; i <= 59; i++) {
                        var option = document.createElement('option');
                        var option2 = document.createElement('option');
                        option.textContent = (i < 10) ? ("0" + i) : i;
                        option2.textContent = (i < 10) ? ("0" + i) : i;
                        minuteSelectStart.appendChild(option);
                        minuteSelectEnd.appendChild(option2);
                    }
                }
                //IE対応end
 
                $rootScope.$on("openDetailsHrUserReg", function(event,data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;  
                    hideMessages();

                    $http.get(link_ajax + "/" + data.id )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.working_hours_id=event.data.work;
                                $scope.item.working_hours_id=event.data.item.working_hours_id;
                                if($scope.item.working_hours_id != null){
                                    var sel=$scope.item.working_hours_id;
                                    $scope.item.workselect=sel;
                                    $scope.item.send=true;
                                }

                                //  Dateに変える dbがdatetime型の場合
                                //$scope.item.Regulations_work_start_time = new Date($scope.item.Regulations_work_start_time);
                                //$scope.item.Regulations_work_end_time = new Date($scope.item.Regulations_work_end_time);
                                //  Dateに変える dbがtime型の場合
                                if($scope.IeFlg==1) {//IEの場合
                                    document.getElementById("start_hour").selectedIndex =0; //モーダル閉じる→モーダル開くの考慮
                                    document.getElementById("start_minute").selectedIndex =0;
                                    document.getElementById("end_hour").selectedIndex =0;
                                    document.getElementById("end_minute").selectedIndex =0;
                                    document.getElementById("start_hour").selectedIndex =$scope.item.Regulations_work_start_time.substring(0, 2);
                                    document.getElementById("start_minute").selectedIndex =$scope.item.Regulations_work_start_time.substring(3, 5);

                                    document.getElementById("end_hour").selectedIndex =$scope.item.Regulations_work_end_time.substring(0, 2);
                                    document.getElementById("end_minute").selectedIndex =$scope.item.Regulations_work_end_time.substring(3, 5);



                                }
                                $scope.item.Regulations_work_start_time = new Date("2021/01/01 " + $scope.item.Regulations_work_start_time);
                                $scope.item.Regulations_work_end_time = new Date("2021/01/01 " + $scope.item.Regulations_work_end_time);

                                $scope.item.shift1_start_time = new Date("2021/01/01 " + $scope.item.shift1_start_time);
                                $scope.item.shift1_end_time = new Date("2021/01/01 " + $scope.item.shift1_end_time);
                                $scope.item.shift2_start_time = new Date("2021/01/01 " + $scope.item.shift2_start_time);
                                $scope.item.shift2_end_time = new Date("2021/01/01 " + $scope.item.shift2_end_time);
                                $scope.item.shift3_start_time = new Date("2021/01/01 " + $scope.item.shift3_start_time);
                                $scope.item.shift3_end_time = new Date("2021/01/01 " + $scope.item.shift3_end_time);
                                $scope.item.hr_user_flg = event.data.item.hr_user_flg.toString();
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $scope.$watch('item',  function(formValue) {
                    let checkDate = (value) => (value instanceof Date && isFinite(value));
                    if(checkDate(formValue.Regulations_work_start_time) || checkDate(formValue.Regulations_work_end_time)){
                        //reset elements
                        for(let i = 1; i <= 3 ; i++){
                            $scope.item[`#shift${i}_start_time`]   = null;
                            $scope.item[`#shift${i}_end_time`]     = null;
                            let $sel = $(`#shift${i}_start_time`);
                            let $eel = $(`#shift${i}_end_time`);
                            $sel.wrap('<form>').closest('form').get(0).reset();
                            $sel.unwrap();
                            $eel.wrap('<form>').closest('form').get(0).reset();
                            $eel.unwrap();
                        }
                        $scope.work_a = false;
                        $scope.work_b = true;
                    }else if(checkDate(formValue.shift1_start_time)
                        ||  checkDate(formValue.shift1_end_time)
                        ||  checkDate(formValue.shift2_start_time)
                        ||  checkDate(formValue.shift2_end_time)
                        ||  checkDate(formValue.shift3_start_time)
                        ||  checkDate(formValue.shift3_end_time)){
                        $scope.item.Regulations_work_start_time = null;
                        $scope.item.Regulations_work_end_time   = null;
                        //reset elements
                        let $sel = $('#start_time');
                        let $eel = $('#end_time');
                        $sel.wrap('<form>').closest('form').get(0).reset();
                        $sel.unwrap();
                        $eel.wrap('<form>').closest('form').get(0).reset();
                        $eel.unwrap();
                        $scope.work_a = true;
                        $scope.work_b = false;
                    }else{
                        $scope.work_a = false;
                        $scope.work_b = false;
                    }
                }, true);
                $scope.saveHrUser = function(callSuccess){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        $scope.item.working_hours_id=$("#work").val();

                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    if(!$scope.item.hr_info_id){
                                        $scope.item.hr_info_id = event.data.hr_info_id;
                                    }
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                    hasChange = true;
                                    if(callSuccess) callSuccess();
                                }
                        };
                        //登録
                        if(!$scope.item.hr_info_id){
                            if($scope.IeFlg==1){//IEの場合
                                $scope.item.Regulations_work_start_time = document.getElementById("start_hour").value + ':' + document.getElementById("start_minute").value + ':00';
                                $scope.item.Regulations_work_end_time   = document.getElementById("end_hour").value + ':' + document.getElementById("end_minute").value + ':00';
                                $http.post(link_hr_store, {item: $scope.item})
                                .then(saveSuccess);
                            }else{
                                var item_copy = Object.assign({}, $scope.item);//開始時間と終了時間はpost/putする時に型変換が必要なので、コピー先で変換し、それをpost/putする。変換したものは画面に反映させないため。

                                var date = $scope.item.Regulations_work_start_time
                                item_copy.Regulations_work_start_time =   ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)
                                
                                date = $scope.item.Regulations_work_end_time
                                item_copy.Regulations_work_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                date = $scope.item.shift1_start_time
                                item_copy.shift1_start_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                date = $scope.item.shift1_end_time
                                item_copy.shift1_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                date = $scope.item.shift2_start_time
                                item_copy.shift2_start_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                date = $scope.item.shift2_end_time
                                item_copy.shift2_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                date = $scope.item.shift3_start_time
                                item_copy.shift3_start_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                date = $scope.item.shift3_end_time
                                item_copy.shift3_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2);


                                $http.post(link_hr_store, {item: item_copy})
                                    .then(saveSuccess);
                            }

                        //更新
                        }else{
                            if($scope.IeFlg==1){//IEの場合
                                $scope.item.Regulations_work_start_time = document.getElementById("start_hour").value + ':' + document.getElementById("start_minute").value + ':00';
                                $scope.item.Regulations_work_end_time   = document.getElementById("end_hour").value + ':' + document.getElementById("end_minute").value + ':00';

                                $scope.item.shift1_start_time   = document.getElementById("shift1_start_time_hour").value + ':' + document.getElementById("shift1_start_time_minute").value + ':00';
                                $scope.item.shift1_end_time   = document.getElementById("shift1_end_time_hour").value + ':' + document.getElementById("shift1_end_time_minute").value + ':00';
                                $scope.item.shift2_start_time   = document.getElementById("shift2_start_time_hour").value + ':' + document.getElementById("shift2_start_time_minute").value + ':00';
                                $scope.item.shift2_end_time   = document.getElementById("shift2_end_time_hour").value + ':' + document.getElementById("shift2_end_time_minute").value + ':00';
                                $scope.item.shift3_start_time   = document.getElementById("shift3_start_time_hour").value + ':' + document.getElementById("shift3_start_time_minute").value + ':00';
                                $scope.item.shift3_end_time   = document.getElementById("shift3_end_time_hour").value + ':' + document.getElementById("shift3_end_time_minute").value + ':00';

                                $http.put(link_ajax + "/" + $scope.item.hr_info_id, {item: $scope.item})
                                    .then(saveSuccess);
                            }else{
                                var item_copy = Object.assign({}, $scope.item);//開始時間と終了時間はpost/putする時に型変換が必要なので、コピー先で変換し、それをpost/putする。変換したものは画面に反映させないため。

                                var date = $scope.item.Regulations_work_start_time;
                                var date_one_start_time = $scope.item.shift1_start_time;
                                var date_one_end_time = $scope.item.shift1_end_time;
                                var date_two_start_time = $scope.item.shift2_start_time;
                                var date_two_end_time = $scope.item.shift2_end_time;
                                var date_three_start_time = $scope.item.shift3_start_time;
                                var date_three_end_time = $scope.item.shift3_end_time;
                                //DBがdatetime型の場合
                                //item_copy.Regulations_work_start_time = date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' +('0' + date.getDate()).slice(-2) + ' ' +  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)
                                //DBがtime型の場合
                                item_copy.Regulations_work_start_time =  date ?  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2) : null;

                                //DBがdatetime型の場合
                                date = $scope.item.Regulations_work_end_time //20220304报错注释
                                //     //item_copy.Regulations_work_end_time = date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' +('0' + date.getDate()).slice(-2) + ' ' +  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)
                                //     //DBがtime型の場合
                                // //    date = $scope.item.Regulations_work_end_time;
                                //
                                item_copy.Regulations_work_end_time =   date ?  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2) :null;
                                //
                                item_copy.shift1_start_time=date_one_start_time ? ('0' + date_one_start_time.getHours()).slice(-2) + ':' + ('0' + date_one_start_time.getMinutes()).slice(-2) + ':' + ('0' + date_one_start_time.getSeconds()).slice(-2):null;
                                item_copy.shift1_end_time=date_one_end_time ? ('0' + date_one_end_time.getHours()).slice(-2) + ':' + ('0' + date_one_end_time.getMinutes()).slice(-2) + ':' + ('0' + date_one_end_time.getSeconds()).slice(-2):null;
                                item_copy.shift2_start_time=date_two_start_time ? ('0' + date_two_start_time.getHours()).slice(-2) + ':' + ('0' + date_two_start_time.getMinutes()).slice(-2) + ':' + ('0' + date_two_start_time.getSeconds()).slice(-2):null;
                                item_copy.shift2_end_time=date_two_end_time ? ('0' + date_two_end_time.getHours()).slice(-2) + ':' + ('0' + date_two_end_time.getMinutes()).slice(-2) + ':' + ('0' + date_two_end_time.getSeconds()).slice(-2):null;
                                item_copy.shift3_start_time=date_three_start_time ? ('0' + date_three_start_time.getHours()).slice(-2) + ':' + ('0' + date_three_start_time.getMinutes()).slice(-2) + ':' + ('0' + date_three_start_time.getSeconds()).slice(-2):null;
                                item_copy.shift3_end_time=date_three_end_time ? ('0' + date_three_end_time.getHours()).slice(-2) + ':' + ('0' + date_three_end_time.getMinutes()).slice(-2) + ':' + ('0' + date_three_end_time.getSeconds()).slice(-2):null;

                                $http.put(link_ajax + "/" + $scope.item.hr_info_id, {item: item_copy})
                                    .then(saveSuccess);
                            }
                        }
                    }else{
                        $(".form_edit")[0].reportValidity()
                    }
                };

            });
        }
    </script>
@endpush