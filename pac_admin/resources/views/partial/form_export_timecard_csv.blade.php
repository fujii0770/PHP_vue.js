<form class="form_check" action="" method="" onsubmit="return false;">
    <div class="modal modal-detail-admin" id="modelCSV_OutputList" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="text-right modal-title">勤務情報CSV出力</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="message mt-3 mx-3"></div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="mb-3">
                        <span>出力する項目を選択してください。</span>
                    </div>
                    <div class="m-3 border-cell p-3 popup-csv-content-height">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <span>ユーザ名</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 pt-3">
                                    <span>メールアドレス</span>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="work_date" class="col-7">業務日(yyyymmdd)</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="work_date"
                                           ng-model="outputList.work_date" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="work_start_time" class="col-7">出勤時間 (yyyymmdd hh:nn)</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="work_start_time"
                                           ng-model="outputList.work_start_time" ng-true-value="1"
                                           ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="work_end_time" class="col-7">退勤時間(yyyymmdd hh:nn)</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="work_end_time"
                                           ng-model="outputList.work_end_time" ng-true-value="1"
                                           ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="break_time" class="col-7">休憩時間(nn)</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="break_time"
                                           ng-model="outputList.break_time" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="working_time" class="col-7">稼働時間</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="working_time"
                                           ng-model="outputList.working_time" ng-true-value="1"
                                           ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="overtime" class="col-7">残業時間</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="overtime"
                                           ng-model="outputList.overtime" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="late_flg" class="col-7">遅刻フラグ</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="late_flg"
                                           ng-model="outputList.late_flg" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <label for="earlyleave_flg" class="col-7">早退フラグ</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="earlyleave_flg"
                                           ng-model="outputList.earlyleave_flg" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>
                            <div class="row pt-3">
                                <label for="paid_vacation_flg" class="col-7">有給フラグ</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="paid_vacation_flg"
                                           ng-model="outputList.paid_vacation_flg" ng-true-value="1"
                                           ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>
                            <div class="row pt-3">
                                <label for="sp_vacation_flg" class="col-7">特休フラグ</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="sp_vacation_flg"
                                           ng-model="outputList.sp_vacation_flg" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>
                            <div class="row pt-3">
                                <label for="day_off_flg" class="col-7">代休フラグ</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="day_off_flg"
                                           ng-model="outputList.day_off_flg" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>
                            <div class="row pt-3">
                                <label for="memo" class="col-7">備考</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="memo"
                                           ng-model="outputList.memo" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>
                            <div class="row pt-3">
                                <label for="work_detail" class="col-7">作業内容</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="work_detail"
                                           ng-model="outputList.work_detail" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>
                            <div class="row pt-3">
                                <label for="admin_memo" class="col-7">管理者コメント</label>
                                <div class="col-5 mt-1">
                                    <input type="checkbox" id="admin_memo"
                                           ng-model="outputList.admin_memo" ng-true-value="1" ng-false-value="0"
                                           style="width: 18px; height: 18px"/>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" ng-click="exportCSV()">CSV出力</button>
                    <button type="button" class="btn btn-default" ng-click="cancelExportCSV()" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> 閉じる
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
<style>
    .border-cell {
        border: 1px solid rgba(31, 116, 255, 1);
    }

    .popup-csv-content-height {
        height: 35em;
        overflow-y: scroll;
    }
</style>
