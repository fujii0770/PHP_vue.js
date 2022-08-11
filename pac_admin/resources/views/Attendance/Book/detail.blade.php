<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;" ng-keydown="getKeyDownList()"
          onkeydown="if(event.keyCode==13){return false;}">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog ">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">打刻編集</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card">
                            <div class="card-header">2021年10月02日（土）</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="stime1" class="col-md-2 col-sm-2 col-12 control-label">出勤1 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="stime1"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="etime1" class="col-md-2 col-sm-2 col-12 control-label">退勤1 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="etime1"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="stime2" class="col-md-2 col-sm-2 col-12 control-label">出勤2 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="stime2"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="etime2" class="col-md-2 col-sm-2 col-12 control-label">退勤2 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="etime2"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="stime3" class="col-md-2 col-sm-2 col-12 control-label">出勤3 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="stime3"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="etime3" class="col-md-2 col-sm-2 col-12 control-label">退勤3 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="etime3"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="stime4" class="col-md-2 col-sm-2 col-12 control-label">出勤4 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="stime4"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="etime4" class="col-md-2 col-sm-2 col-12 control-label">退勤4 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="etime4"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="stime5" class="col-md-2 col-sm-2 col-12 control-label">出勤5 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="stime5"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="etime6" class="col-md-2 col-sm-2 col-12 control-label">退勤5 </label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <div class="input-group mb-1">
                                                <input type="time" name="working_month_start"
                                                       value="" class="form-control"
                                                       placeholder="mm:ss" id="etime6"/>
                                                <div class="input-group-append">
                                                    <button class="btn btn btn-danger" type="submit"><i
                                                            class="fas fa-trash-alt"></i> 削除
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <ng-template><i class="far fa-save"></i> 更新</ng-template>
                        </button>

                        <button type="button" class="btn btn-default" data-dismiss="modal">
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
        if (appPacAdmin) {
            appPacAdmin.controller('DetailController', function ($scope, $rootScope, $http) {

                hasChange = false;


                $rootScope.$on("openEdit", function (event, data) {
                    $rootScope.$emit("showLoading");
                    $rootScope.$emit("hideLoading");
                    $("#modalDetailItem").modal();
                });

                $scope.save = function (callSuccess) {
                    $rootScope.$emit("showLoading");

                    $rootScope.$emit("hideLoading")


                };


            })
        }
    </script>
@endpush
