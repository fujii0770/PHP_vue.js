<style>
    .fallbackTimePickerEnd{
        display:none;
    }
    .float_left_input{
        width:30% !important;
        display: unset;
    }
    .form-horizontal .control-label{
        text-align:left;
    }
</style>
<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!item.id">就労時間登録</h4>
                        <h4 class="modal-title" ng-if="item.id">就労時間更新</h4>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">
                            @canany([PermissionUtils::PERMISSION_HR_WORKING_HOUR_CREATE])
                                <button type="button" class="btn btn-success" ng-click="saveHrUser()" ng-if="!item.id">
                                    <i class="far fa-save"></i>登録
                                </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_HR_WORKING_HOUR_UPDATE])
                                <button type="button" class="btn btn-success" ng-click="saveHrUser()" ng-if="item.id">
                                    <i class="far fa-save"></i>更新
                                </button>
                            @endcanany
                        </div>
                        <div class="message message-info"></div>

                        <div class="card mt-3">
                            <div class="card-header" ng-if="!item.id">就労時間詳細</div>
                            <div class="card-header" ng-if="item.id">就労時間詳細</div>
                            <div class="card-body">
                                <div class="form-group">

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="user_name" class="col-md-3 col-sm-3 col-12 text-right-lg">定義名称 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <input type="text" required autocomplete="off" class="form-control" ng-model="item.definition_name" maxlength="99" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="email" class="col-md-3 col-sm-3 col-12 text-right-lg">勤務形態</label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <input type="radio"  ng-model="work_form_kbn"    value="0"   name="work_form_kbn" class="kbn_a"  ng-click="work_kbn('0')"/>
                                                <label ng-click="work_kbn('0')">
                                                    <span>通常勤務</span>
                                                </label>
                                                <input type="radio" ng-model="work_form_kbn"   value="1" name="work_form_kbn" class="kbn_b" ng-click="work_kbn('1')" />
                                                <label ng-click="work_kbn('1')">
                                                    <span>シフト勤務</span>
                                                </label>
                                                <input type="radio" ng-model="work_form_kbn"   value="2"  name="work_form_kbn" class="kbn_c" ng-click="work_kbn('2')" />
                                                <label ng-click="work_kbn('2')">
                                                    <span>フレックス勤務</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="department_name" class="col-md-3 col-sm-3 col-12 text-right-lg">規定業務時刻 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <input type="time" ng-readonly="work_a" ng-model="item.regulations_work_start_time" class="form-control float_left_input">&nbsp;~&nbsp;
                                                <input type="time" ng-readonly="work_a" ng-model="item.regulations_work_end_time" class="form-control float_left_input">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="position_name" class="col-md-3 col-sm-3 col-12 text-right-lg">シフト1時刻 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <input type="time" ng-readonly="work_b" ng-model="item.shift1_start_time" class="form-control float_left_input">&nbsp;~&nbsp;
                                                <input type="time" ng-readonly="work_b" ng-model="item.shift1_end_time" class="form-control float_left_input">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="position_name" class="col-md-3 col-sm-3 col-12 text-right-lg">シフト2時刻 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <input type="time" ng-readonly="work_b" ng-model="item.shift2_start_time" class="form-control float_left_input">&nbsp;~&nbsp;
                                                <input type="time" ng-readonly="work_b" ng-model="item.shift2_end_time" class="form-control float_left_input">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="position_name" class="col-md-3 col-sm-3 col-12 text-right-lg">シフト3時刻 </label>
                                            <div class="col-md-8 col-sm-8 col-24">
                                                <input type="time" ng-readonly="work_b" ng-model="item.shift3_start_time" class="form-control float_left_input">&nbsp;~&nbsp;
                                                <input type="time" ng-readonly="work_b" ng-model="item.shift3_end_time" class="form-control float_left_input">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="Regulations_work_start_time" class="col-md-3 col-sm-3 col-12 control-label text-right-lg">規定就労時間 </label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <input type="text" ng-readonly="work_c" class="form-control"  ng-model="item.regulations_working_hours" />

                                            </div>
                                            <label for="Overtime_unit" required="" class="col-md-2 col-sm-2 col-2 control-label">時</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="Overtime_unit" class="col-md-3 col-sm-3 col-12 control-label text-right-lg">残業発生時間単位 <span style="color: red">*</span></label>
                                            <div class="col-md-2 col-sm-4 col-2">
                                                <input type="text" required="" class="form-control" ng-model="item.overtime_unit"  ng-readonly="false">
                                            </div>
                                            <label for="Overtime_unit" required="" class="col-md-2 col-sm-2 col-2 control-label">分</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="break_time" class="col-md-3 col-sm-3 col-12 control-label text-right-lg">休憩時間 <span style="color: red">*</span></label>
                                            <div class="col-md-2 col-sm-4 col-2">
                                                <input type="text" required class="form-control" ng-model="item.break_time" id="break_time" ng-readonly="false"/>
                                            </div>
                                            <label for="break_time" class="col-md-2 col-sm-2 col-2 control-label">分</label>
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
                 $scope.work_form_kbn='0';
                 //work_input
                 $scope.work_a=false;
                 $scope.work_b=true;
                 $scope.work_c=true;

                //IE対応start
                $scope.IeFlg = 0;

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
                $rootScope.$on("openNewUser", function(event){
                    $scope.item = {};
                    $scope.work_form_kbn ='0';
                    $scope.work_a=false;
                    $scope.work_b=true;
                    $scope.work_c=true;
                });

                $rootScope.$on("openDetailsHrUserReg", function(event,data){
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    hideMessages();
                    $http.get(link_ajax + "/" + window.btoa(data.id) )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;////(Number($scope.item.regulations_working_hours)/60).toFixed(1);
                                $scope.item.regulations_working_hours=Math.floor((Number($scope.item.regulations_working_hours)/60) * 100) / 100 ;
                                $scope.work_form_kbn=false;

                                switch($scope.item.work_form_kbn){
                                     case 0:
                                     $scope.work_form_kbn='0';
                                     $scope.work_a=false;
                                     $scope.work_b=true;
                                     $scope.work_c=true;
                                     break;
                                     case 1:
                                     $scope.work_form_kbn='1';
                                     $scope.work_a=true;
                                     $scope.work_b=false;
                                     $scope.work_c=true;
                                     break;
                                     case 2:
                                     $scope.work_form_kbn='2';
                                     $scope.work_a=true;
                                     $scope.work_b=true;
                                     $scope.work_c=false;
                                     break;
                                }

                                $(".kbn_cid0").attr("checked","checked");
                                //  Dateに変える dbがdatetime型の場合
                                //$scope.item.Regulations_work_start_time = new Date($scope.item.Regulations_work_start_time);
                                //$scope.item.Regulations_work_end_time = new Date($scope.item.Regulations_work_end_time);
                                //  Dateに変える dbがtime型の場合
                                if($scope.IeFlg==1) {//IEの場合
                                    document.getElementById("start_hour").selectedIndex =0; //モーダル閉じる→モーダル開くの考慮
                                    document.getElementById("start_minute").selectedIndex =0;
                                    document.getElementById("end_hour").selectedIndex =0;
                                    document.getElementById("end_minute").selectedIndex =0;
                                    document.getElementById("start_hour").selectedIndex =$scope.item.regulations_work_start_time.substring(0, 2);
                                    document.getElementById("start_minute").selectedIndex =$scope.item.regulations_work_start_time.substring(3, 5);
                                    document.getElementById("end_hour").selectedIndex =$scope.item.regulations_work_end_time.substring(0, 2);
                                    document.getElementById("end_minute").selectedIndex =$scope.item.regulations_work_end_time.substring(3, 5);
                                }
                                $scope.item.regulations_work_start_time = new Date("2021/01/01 " + $scope.item.regulations_work_start_time);
                                $scope.item.regulations_work_end_time = new Date("2021/01/01 " + $scope.item.regulations_work_end_time);
                                $scope.item.shift1_start_time = new Date("2021/01/01 " + $scope.item.shift1_start_time);
                                $scope.item.shift1_end_time = new Date("2021/01/01 " + $scope.item.shift1_end_time);
                                $scope.item.shift2_start_time = new Date("2021/01/01 " + $scope.item.shift2_start_time);
                                $scope.item.shift2_end_time = new Date("2021/01/01 " + $scope.item.shift2_end_time);
                                $scope.item.shift3_start_time = new Date("2021/01/01 " + $scope.item.shift3_start_time);
                                $scope.item.shift3_end_time = new Date("2021/01/01 " + $scope.item.shift3_end_time);
                            }
                        });
                    $("#modalDetailItem").modal();
                });
                $scope.work_kbn=function(num){
                    $scope.item.work_form_kbn=num;
                    switch(Number(num)){
                        case 0:
                            $scope.work_a=false;
                            $scope.work_b=true;
                            $scope.work_c=true;
                            $scope.item.regulations_working_hours = null;
                            $scope.item.shift1_start_time = null;
                            $scope.item.shift1_end_time = null;
                            $scope.item.shift2_start_time = null;
                            $scope.item.shift2_end_time = null;
                            $scope.item.shift3_start_time = null;
                            $scope.item.shift3_end_time = null;
                            $scope.work_form_kbn='0';
                        break;
                        case 1:
                            $scope.work_a=true;
                            $scope.work_b=false;
                            $scope.work_c=true;
                            $scope.item.regulations_working_hours = null;
                            $scope.item.regulations_work_start_time=null;
                            $scope.item.regulations_work_end_time=null;
                            $scope.work_form_kbn='1';

                        break;
                        case 2:
                            $scope.work_a=true;
                            $scope.work_b=true;
                            $scope.work_c=false;
                            $scope.item.shift1_start_time = null;
                            $scope.item.shift1_end_time = null;
                            $scope.item.shift2_start_time = null;
                            $scope.item.shift2_end_time = null;
                            $scope.item.shift3_start_time = null;
                            $scope.item.shift3_end_time = null;
                            $scope.item.regulations_work_start_time=null;
                            $scope.item.regulations_work_end_time=null;
                            $scope.work_form_kbn='2';
                        break;
                    }
                }

                $scope.saveHrUser = function(callSuccess){
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


                        //登録

                        if(!$scope.item.id){
                            if($scope.IeFlg==1){//IEの場合
                                if($scope.item.work_form_kbn){
                                    $scope.item.work_form_kbn=0;
                                }
                                $scope.item.regulations_work_start_time = document.getElementById("start_hour").value + ':' + document.getElementById("start_minute").value + ':00';
                                $scope.item.regulations_work_end_time   = document.getElementById("end_hour").value + ':' + document.getElementById("end_minute").value + ':00';
                                $http.post(link_hr_insert, {item: $scope.item})
                                     .then(saveSuccess);
                            }else{
                                var item_copy = Object.assign({}, $scope.item);//開始時間と終了時間はpost/putする時に型変換が必要なので、コピー先で変換し、それをpost/putする。変換したものは画面に反映させないため。


                                if($scope.item.regulations_work_start_time){
                                    var date = $scope.item.regulations_work_start_time;
                                    item_copy.regulations_work_start_time =   ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)
                                }
                                if($scope.item.regulations_work_end_time){
                                    var date = $scope.item.regulations_work_end_time;
                                    item_copy.regulations_work_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                }
                                if($scope.item.shift1_start_time){
                                    var date = $scope.item.shift1_start_time;
                                    item_copy.shift1_start_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                }
                                if($scope.item.shift1_end_time){
                                    var date = $scope.item.shift1_end_time;
                                    item_copy.shift1_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                }
                                if($scope.item.shift2_start_time){
                                    var  date = $scope.item.shift2_start_time;
                                    item_copy.shift2_start_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                }
                                if($scope.item.shift2_end_time){
                                    var date = $scope.item.shift2_end_time;
                                    item_copy.shift2_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                }
                                if($scope.item.shift3_start_time){
                                    var  date = $scope.item.shift3_start_time;
                                    item_copy.shift3_start_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)

                                }
                                if($scope.item.shift3_end_time){
                                    var date = $scope.item.shift3_end_time;
                                    item_copy.shift3_end_time = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2);

                                }
                                if(item_copy.work_form_kbn == ''){
                                    item_copy.work_form_kbn=0;
                                }

                                $http.post(link_hr_insert, {item: item_copy})
                                     .then(saveSuccess);
                            }

                            //更新
                        }else{
                            if($scope.IeFlg==1){//IEの場合
                                $scope.item.regulations_work_start_time = document.getElementById("start_hour").value + ':' + document.getElementById("start_minute").value + ':00';
                                $scope.item.regulations_work_end_time   = document.getElementById("end_hour").value + ':' + document.getElementById("end_minute").value + ':00';

                                $scope.item.shift1_start_time   = document.getElementById("shift1_start_time_hour").value + ':' + document.getElementById("shift1_start_time_minute").value + ':00';
                                $scope.item.shift1_end_time   = document.getElementById("shift1_end_time_hour").value + ':' + document.getElementById("shift1_end_time_minute").value + ':00';
                                $scope.item.shift2_start_time   = document.getElementById("shift2_start_time_hour").value + ':' + document.getElementById("shift2_start_time_minute").value + ':00';
                                $scope.item.shift2_end_time   = document.getElementById("shift2_end_time_hour").value + ':' + document.getElementById("shift2_end_time_minute").value + ':00';
                                $scope.item.shift3_start_time   = document.getElementById("shift3_start_time_hour").value + ':' + document.getElementById("shift3_start_time_minute").value + ':00';
                                $scope.item.shift3_end_time   = document.getElementById("shift3_end_time_hour").value + ':' + document.getElementById("shift3_end_time_minute").value + ':00';

                                $http.put(link_hr_store + "/" + window.btoa($scope.item.id), {item: $scope.item})
                                    .then(saveSuccess);
                            }else{
                                var item_copy = Object.assign({}, $scope.item);//開始時間と終了時間はpost/putする時に型変換が必要なので、コピー先で変換し、それをpost/putする。変換したものは画面に反映させないため。

                                var date = $scope.item.regulations_work_start_time;
                                var date_one_start_time = $scope.item.shift1_start_time;
                                var date_one_end_time = $scope.item.shift1_end_time;
                                var date_two_start_time = $scope.item.shift2_start_time;
                                var date_two_end_time = $scope.item.shift2_end_time;
                                var date_three_start_time = $scope.item.shift3_start_time;
                                var date_three_end_time = $scope.item.shift3_end_time;
                                //DBがdatetime型の場合
                                //item_copy.Regulations_work_start_time = date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' +('0' + date.getDate()).slice(-2) + ' ' +  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)
                                //DBがtime型の場合
                                item_copy.regulations_work_start_time =  date ?  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2) : null;

                                //DBがdatetime型の場合
                                date = $scope.item.regulations_work_end_time //20220304报错注释
                                //     //item_copy.Regulations_work_end_time = date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' +('0' + date.getDate()).slice(-2) + ' ' +  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2)
                                //     //DBがtime型の場合
                                // //    date = $scope.item.Regulations_work_end_time;
                                //
                                item_copy.regulations_work_end_time =   date ?  ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + ':' + ('0' + date.getSeconds()).slice(-2) :null;
                                //
                                item_copy.shift1_start_time=date_one_start_time ? ('0' + date_one_start_time.getHours()).slice(-2) + ':' + ('0' + date_one_start_time.getMinutes()).slice(-2) + ':' + ('0' + date_one_start_time.getSeconds()).slice(-2):null;
                                item_copy.shift1_end_time=date_one_end_time ? ('0' + date_one_end_time.getHours()).slice(-2) + ':' + ('0' + date_one_end_time.getMinutes()).slice(-2) + ':' + ('0' + date_one_end_time.getSeconds()).slice(-2):null;
                                item_copy.shift2_start_time=date_two_start_time ? ('0' + date_two_start_time.getHours()).slice(-2) + ':' + ('0' + date_two_start_time.getMinutes()).slice(-2) + ':' + ('0' + date_two_start_time.getSeconds()).slice(-2):null;
                                item_copy.shift2_end_time=date_two_end_time ? ('0' + date_two_end_time.getHours()).slice(-2) + ':' + ('0' + date_two_end_time.getMinutes()).slice(-2) + ':' + ('0' + date_two_end_time.getSeconds()).slice(-2):null;
                                item_copy.shift3_start_time=date_three_start_time ? ('0' + date_three_start_time.getHours()).slice(-2) + ':' + ('0' + date_three_start_time.getMinutes()).slice(-2) + ':' + ('0' + date_three_start_time.getSeconds()).slice(-2):null;
                                item_copy.shift3_end_time=date_three_end_time ? ('0' + date_three_end_time.getHours()).slice(-2) + ':' + ('0' + date_three_end_time.getMinutes()).slice(-2) + ':' + ('0' + date_three_end_time.getSeconds()).slice(-2):null;

                                $http.put(link_hr_store + "/" + window.btoa($scope.item.id), {item: item_copy})
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