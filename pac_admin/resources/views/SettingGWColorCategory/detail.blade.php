<div ng-controller="DetailSettingFacilityController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!info.id"> スケジュール種別登録</h4>
                        <h4 class="modal-title" ng-show="info.id"> スケジュール種別更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message"></div>
                        <div class="form-group">
                            <div class="row">
                                <label for="given_name" class="col-md-4 control-label">種別名 <span
                                        style="color: red">*</span></label>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" required maxlength="45" class="form-control"
                                                   ng-model="info.typeName" id="typeName"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="given_name" class="col-md-4 control-label">色 <span
                                        style="color: red">*</span></label>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-10">
                                            @foreach ($allColor as $color)
                                                <div class="circle-box">
                                                    <div id="currentSelect_{{$color['id']}}"
                                                         ng-click="selectCurrentColor('{{$color['id']}}')" class="circle"
                                                         style="background-color: {{$color['code']}}"></div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <input type="hidden" id="selectColor" value=""/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_CREATE])
                            <ng-template ng-show="!info.id">
                                <button type="submit" class="btn btn-success" ng-click="save()">
                                    <ng-template ng-show="!info.sendEmail"><i class="fas fa-plus-circle"></i> 登録
                                    </ng-template>
                                </button>
                            </ng-template>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_UPDATE])
                            <ng-template ng-show="info.id">
                                <button type="submit" class="btn btn-success" ng-click="save()">
                                    <i class="far fa-save"></i> 更新
                                </button>
                            </ng-template>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_DELETE])
                            <ng-template ng-show="info.id">
                                <button type="submit" class="btn btn-danger" ng-click="deleteCurrentColorType()">
                                    <i class="far fa-save"></i> 削除
                                </button>
                            </ng-template>
                        @endcanany
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
            appPacAdmin.controller('DetailSettingFacilityController', function ($scope, $rootScope, $http) {
                $scope.info = {};
                $scope.id = 0;
                $scope.mstColorId = '';

                var startFlg = false;

                $rootScope.$on("openNewFacility", function (event) {
                    $scope.id = 0;
                    $scope.info = {id: 0, typeName: '', mstColorId: 0};
                    $('.circle').each(function (index, element) {
                        $(this).removeClass('yesCircle');
                    });
                    $('#selectColor').val(0);
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openEditFacility", function (event, data) {
                    $scope.id = 0;
                    $scope.info = {id: 0, typeName: '', mstColorId: 0};
                    $('.circle').each(function (index, element) {
                        $(this).removeClass('yesCircle');
                    });
                    $('#selectColor').val(0);
                    if(startFlg){
                        return ;
                    }
                    startFlg = true;
                    $http.post(link_ajax_show, {
                        id: data.id
                    })
                        .then(function (event) {
                            startFlg = false;
                            $rootScope.$emit("hideLoading");
                            if (event.data.status != true) {
                                $("#modalDetailItem .message").append(showMessages(['種別の登録に失敗しました。'], 'danger', 10000));
                                return;
                            }
                            $scope.id = event.data.data.id;
                            $scope.typeName = event.data.data.typeName;
                            $scope.mstColorId = event.data.data.mstColorId;
                            $scope.info = {
                                id: event.data.data.id,
                                typeName: event.data.data.typeName,
                                mstColorId: event.data.data.mstColorId
                            };
                            $('.circle').each(function (index, element) {
                                let colorid=$(this).prop('id').replace('currentSelect_','')
                                if (colorid==event.data.data.mstColorId)
                                    $(this).addClass('yesCircle');
                            });
                            hideMessages();
                            $("#modalDetailItem").modal();
                            hasChange = false;
                        });
                });
                // change select color
                $scope.selectCurrentColor = function (id) {
                    $('.circle').each(function (index, element) {
                        $(this).removeClass('yesCircle');
                    });
                    $('#currentSelect_' + id).addClass('yesCircle');
                    $scope.info.mstColorId = id;
                }

                $scope.save = function () {
                    if ($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        if(!$scope.info.mstColorId){
                            $("#modalDetailItem .message").append(showMessages(['色を選択してください。'], 'danger', 10000));
                            return ;
                        }
                        $rootScope.$emit("showLoading");
                        if (!$scope.info.id) {
                            $http.post(link_ajax_create, {
                                typeName: $scope.info.typeName,
                                mstColorId: $scope.info.mstColorId,
                            })
                                .then(function (event) {
                                    $rootScope.$emit("hideLoading");
                                    if (event.data.status != true) {
                                        $("#modalDetailItem .message").append(showMessages(['種別の登録は失敗しました。'], 'danger', 10000));
                                    } else {
                                        $("#modalDetailItem .message").append(showMessages(['種別を登録しました。'], 'success', 10000));
                                        hasChangeFacility = true;
                                        setTimeout(function() {
                                            location.reload();
                                        }, 800);
                                    }
                                });
                        } else {
                            $http.put(link_ajax_update, {
                                id: $scope.info.id,
                                typeName: $scope.info.typeName,
                                mstColorId: $scope.info.mstColorId
                            })
                                .then(function (event) {
                                    $rootScope.$emit("hideLoading");
                                    if (event.data.status != true) {
                                        $("#modalDetailItem .message").append(showMessages(['種別の更新は失敗しました。'], 'danger', 10000));
                                    } else {
                                        $("#modalDetailItem .message").append(showMessages(['種別を更新しました。'], 'success', 10000));
                                        hasChangeFacility = true;
                                        setTimeout(function() {
                                            location.reload();
                                        }, 800);
                                    }
                                });
                        }
                    }
                };
                $scope.deleteCurrentColorType = function (id) {
                    if ($(".form_edit")[0].checkValidity()) {
                        hideMessages();

                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '種別を削除します。よろしいですか？',
                                btnDanger: 'はい',
                                callDanger: function () {
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_ajax_delete, {
                                        id: $scope.info.id
                                    })
                                        .then(function (event) {
                                            $rootScope.$emit("hideLoading");
                                            if (event.data.status != true) {
                                                $("#modalDetailItem .message").append(showMessages(['種別の削除は失敗しました。'], 'danger', 10000));
                                            } else {
                                                $("#modalDetailItem .message").append(showMessages(['種別を削除しました。'], 'success', 10000));
                                                hasChangeFacility = true;
                                                setTimeout(function() {
                                                    location.reload();
                                                }, 1800);
                                            }
                                        });
                                }
                            });
                    }
                };
            });
        }
    </script>
@endpush

