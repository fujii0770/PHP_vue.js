<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-stamp" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">印面情報更新</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-info"></div>

                    <div class="item-stamp">
                        <div class="preview-stamp text-center">
                            <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                        </div>
                        <div class="row form-group" style="margin-top: 10px">
                            <label for="" class="control-label col-md-3 text-right-lg">名称</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" required ng-model="stamp.stamp_name" maxlength="32" required/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="" class="control-label col-md-3 text-right-lg">ジャンル</label>
                            <div class="col-md-9">
                                <select class="form-control select2" ng-model="stamp.stamp_division" required="1" >
                                    <option></option>
                                    <option ng-repeat="division in divisionList" ng-value="division.id">
                                        <% division.division_name %>
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="stamp_date_flg" class="control-label col-md-3 text-right-lg">日付設定</label>
                                <div class="col-md-9">
                                    <label for="stamp_date_flg" style="padding-left: 20px;">
                                        <input type="checkbox" id="stamp_date_flg"
                                               ng-model="stamp.stamp_date_flg" ng-true-value="1" ng-false-value="0"
                                               style="margin-left: -20px; margin-right: 5px;margin-top: 12px;" />
                                        日付を含む（日付の印字領域を指定してください）
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="date_dpi" class="mt-3 control-label col-md-2 text-right-lg">dpi</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_dpi" class="mt-3 form-control"
                                           ng-model="stamp.date_dpi" ng-disabled="stamp.stamp_division == 0" />
                                </div>
                                <label for="" class="mt-3 col-md-2 text-right control-label"></label>
                                <div class="col-md-4">
                                </div>
                            </div>
                            <div class="row">
                                <label for="date_x" class="mt-3 col-md-2 text-right control-label">左上X座標</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_x" class="mt-3 form-control"
                                           ng-model="stamp.date_x" ng-disabled="stamp.stamp_division == 0" />
                                </div>
                                <label for="date_y" class="mt-3 col-md-2 text-right control-label">左上Y座標</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_y" class="mt-3 form-control"
                                           ng-model="stamp.date_y" ng-disabled="stamp.stamp_division == 0" />
                                </div>
                            </div>
                            <div class="row">
                                <label for="date_width" class="mt-3 col-md-2 text-right control-label">幅</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_width" class="mt-3 form-control"
                                           ng-model="stamp.date_width" ng-disabled="stamp.stamp_division == 0" />
                                </div>
                                <label for="date_height" class="mt-3 col-md-2 text-right control-label">高さ</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_height" class="mt-3 form-control"
                                           ng-model="stamp.date_height" ng-disabled="stamp.stamp_division == 0" />
                                </div>
                            </div>

                        </div>
                        <div class="form-group row">
                            <label for="date_dpi" class="col-md-2 text-right control-label">日付色</label>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-4">
                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::COMMON_STAMP_DATE_COLOR , 'date_color', Request::get('date_color', ''),null,['class'=> 'form-control', 'ng-disabled' => 'stamp.stamp_date_flg == 0',  'ng-model'=>'stamp.date_color', 'ng-change'=>"changeDateColor()"]) !!}
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control jscolor" ng-model="stamp.background_color"
                                               id="stamp-background_color" name="background_color"
                                               ng-style="{'color': '#ffffff','background-color': '#'+stamp.background_color}" />
                                    </div>
                                    <div class="col-md-4"><span class="btn btn-default" ng-click="resetBackground()">初期値に戻す</span></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" ng-click="save()">
                        <i class="far fa-save"></i> 更新
                    </button>

                    <button type="button" class="btn btn-danger" ng-click="remove()">
                        <i class="fas fa-trash-alt"></i> 削除
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
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.stamp = {};
                $scope.default_bg = '';

                $rootScope.$on("openEditStamp", function(event, data){
                    hideMessages();
                    $scope.stamp = JSON.parse(JSON.stringify(data.stamp));
                    $scope.divisionList = data.divisionList;
                    $scope.default_bg = $scope.stamp.date_color;
                    $("#modalDetailItem").modal();
                });

                $scope.save = function(){
                    if($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        let dataColor = $scope.stamp.date_color;
                        if ($scope.stamp.date_color === 'other') {
                            dataColor = $scope.stamp.background_color
                        }
                        $http.post(link_ajax, {id: $scope.stamp.id,stamp_name: $scope.stamp.stamp_name,stamp_division: $scope.stamp.stamp_division,stamp_date_flg: $scope.stamp.stamp_date_flg,date_dpi : $scope.stamp.date_dpi,date_x : $scope.stamp.date_x,date_y : $scope.stamp.date_y,date_width : $scope.stamp.date_width,date_height : $scope.stamp.date_height,date_color : dataColor })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                $rootScope.$emit("openUpdateStamp",{stamp_name:$scope.stamp.stamp_name});
                                $scope.default_bg = $scope.stamp.background_color;
                                $scope.stamp.stamp_image = event.data.show_stamp_image;
                            }
                        });
                    }
                };

                $scope.remove = function(){
                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'削除確認',
                        message:'<div class="text-left">削除した印鑑は元に戻せません。</div>'
                                    + '<div class="text-left">利用者に割り当てられている場合、削除と同時に割り当ては解除されます。</div>'
                                    + '<div class="text-left">便利印を削除しますか？</div>'
                                    ,
                        btnDanger:'削除',
                        databack: $scope.stamp.id,
                        callDanger: function(id){
                            $rootScope.$emit("showLoading");
                            $http.delete(link_ajax +"/"+ id, { })
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        $(".message-list").append(showMessages(event.data.message, 'warning', 10000));
                                        $rootScope.$emit("openRemoveStamp",{});
                                        $("#modalDetailItem").modal('hide');
                                    }
                                });
                        }
                    });

                };

                $scope.changeDateColor = function(){
                    if ($scope.stamp.date_color !== 'other') {
                        $scope.stamp.background_color = $scope.stamp.date_color != '' ? $scope.stamp.date_color : null
                    }
                };

                $scope.resetBackground = function(){
                    $scope.stamp.background_color = $scope.default_bg;
                };

            })
        }
    </script>
@endpush
