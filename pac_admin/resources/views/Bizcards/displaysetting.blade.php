<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;" ng-keydown="getKeyDownList()" onkeydown="if(event.key=='Enter'){return false;}">
        <div class="modal modal-detail" id="modalDisplaySetting" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">名刺公開設定更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="card">
                            <div class="card-header">名刺公開設定</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="user_id" class="col-md-2 col-sm-2 col-2 text-right-lg">ID </label>
                                        <div class="col-md-8 col-sm-8 col-8">
                                            <p id="user_id"><% item.id %></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="display_type_0" class="col-md-2 col-sm-2 col-2 control-label">公開種別</label>
                                        <div class="col-md-10 col-sm-10 col-10">
                                            <label for="display_type_0" class="control-label">
                                                <input type="radio" id="display_type_0" ng-model="item.bizcard.display_type"
                                                    ng-disabled="!item.bizcard.display_editable && item.bizcard.display_type != 0" name="diplay_type" value="0" ng-change="changeDisplayType()" /> 会社　
                                            </label>
                                            <label for="display_type_1" class="control-label">
                                                <input type="radio" id="display_type_1" ng-model="item.bizcard.display_type"
                                                    ng-disabled="!item.bizcard.display_editable && item.bizcard.display_type != 1" name="diplay_type" value="1" ng-change="changeDisplayType()" /> 部署　
                                            </label>
                                            <label for="display_type_2" class="control-label">
                                                <input type="radio" id="display_type_2" ng-model="item.bizcard.display_type"
                                                    ng-disabled="!item.bizcard.display_editable && item.bizcard.display_type != 2" name="diplay_type" value="2" ng-change="changeDisplayType()" /> 個人　
                                            </label>
                                            <label for="display_type_3" class="control-label">
                                                <input type="radio" id="display_type_3" ng-model="item.bizcard.display_type"
                                                    ng-disabled="!item.bizcard.display_editable && item.bizcard.display_type != 3" name="diplay_type" value="3" ng-change="changeDisplayType()" /> グループ　
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row display-target-row hide">
                                        <label for="display_target" ng-if="item.bizcard.display_type == '1' || item.bizcard.display_type == '3'" class="col-md-2 col-sm-2 col-2 control-label">公開対象(複数選択可)</label>
                                        <label for="display_target" ng-if="item.bizcard.display_type == '2'" class="col-md-2 col-sm-2 col-2 control-label">公開対象</label>
                                        <div class="col-md-10 col-sm-10 col-10 display-target hide" data-type="1">
                                            {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'display_target_dept', '', null,
                                                ['class' => 'form-control', 'ng-model' => 'item.bizcard.display_target', 'ng-readonly' => 'readonly',
                                                 'ng-change' => 'onDepartmentChange()', 'multiple' => 'multiple']) !!}
                                        </div>
                                        <div class="col-md-10 col-sm-10 col-10" ng-if="item.bizcard.display_editable && item.bizcard.display_type == '2'" data-type="2">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect($userArray , 'display_target_user', '',
                                                '公開対象の利用者を選択してください',['class' => 'form-control', 'ng-model' => 'item.bizcard.display_target[0]']) !!}
                                        </div>
                                        <div class="col-md-10 col-sm-10 col-10" ng-if="!item.bizcard.display_editable && item.bizcard.display_type == '2'" data-type="2">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect($userArray , 'display_target_user_disable', '',
                                                '',['class' => 'form-control', 'ng-model' => 'item.bizcard.display_target[0]', 'disabled' => 'disabled']) !!}
                                        </div>
                                        <div class="col-md-10 col-sm-10 col-10 display-target list-group hide" data-type="3">
                                            <ul class="items tree mt-3" id="group_list" style="height: 175px; overflow: scroll;">
                                                @foreach ($listGroupTree as $group)
                                                    @include('Bizcards.group_tree_node',['listGroupTree' => $group])
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="del_flg_0" class="col-md-2 col-sm-2 col-2 control-label">状態</label>
                                        <div class="col-md-4 col-sm-4 col-4">
                                            <label for="del_flg_0" class="control-label">
                                                <input type="radio" id="del_flg_0" ng-model="item.bizcard.del_flg" ng-readonly="readonly" name="del_flg" value="0" /> 公開　
                                            </label>
                                            <label for="del_flg_1" class="control-label">
                                                <input type="radio" id="del_flg_1" ng-model="item.bizcard.del_flg" ng-readonly="readonly" name="del_flg" value="1" /> 非公開
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @can(\App\Http\Utils\PermissionUtils::PERMISSION_BIZ_CARDS_UPDATE)
                        <button type="button" class="btn btn-success" ng-click="save()">
                            <i class="far fa-save"></i> 更新
                        </button>
                        @endcan
                        @can(\App\Http\Utils\PermissionUtils::PERMISSION_BIZ_CARDS_DELETE)
                        <button type="button" class="btn btn-danger" ng-click="remove()">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
                        @endcan
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
        const DISPLAY_TYPE = @json($DISPLAY_TYPE);
        if (appPacAdmin) {
            appPacAdmin.controller('DetailController', function ($scope, $rootScope, $http) {
                $scope.item = {};
                $rootScope.$on("openEditBizcard", function (event, data) {
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    hideMessages();
                    $http.get(link_ajax + '/' + data.id)
                    .then(function (event) {
                        $rootScope.$emit("hideLoading");
                        if (event.data.status == false) {
                            $("#modalDisplaySetting .message").append(showMessages(event.data.message, 'danger', 10000));
                        } else {
                            hasChange = false;
                            $scope.item.bizcard = event.data.bizcard;
                            $scope.item.bizcard.display_type = String(event.data.bizcard.display_type);
                            $scope.item.bizcard.display_target = event.data.bizcard.display_target.split(',');
                            $scope.item.bizcard.del_flg = String(event.data.bizcard.del_flg);
                            $scope.item.bizcard.display_editable = event.data.bizcard.display_editable;
                            // 公開対象の表示切り替え
                            switchTargetDisplay(event.data.bizcard.display_type);
                            // 最下層の項目の頭に三角形を表示しないよう制御
                            resetTreeArrow();
                            // 公開種別が「グループ」の場合、グループ選択用データのチェックボックスの状態を初期化
                            if (event.data.bizcard.display_type == DISPLAY_TYPE.GROUP) {
                                initializeGroupCheckbox($scope.item.bizcard.display_target);
                            }
                        }
                    });
                    $("#modalDisplaySetting").modal();
                });

                $scope.onDepartmentChange = function () {
                    console.log($scope.item.bizcard.display_target);
                };

                // 親部署のチェックON/OFFを切り替えた時に、子部署と利用者もON/OFFを切り替える
                $scope.selectGroupItem = function (id) {
                    let isChecked = $('.list-group input[type="checkbox"][id="' + id + '"]').prop('checked');
                    $('.list-group input[type="checkbox"][data-parent="' + id + '"]').each(function(){
                        $(this).prop('checked', isChecked);
                        // 子部署の子部署と利用者もチェックOn/Offを切り替える
                        $scope.selectGroupItem($(this).attr('id'));
                    });
                }

                $scope.save = function(callSuccess){
                    $rootScope.$emit("showLoading");
                    hideMessages();
                    let id = $scope.item.id;
                    let param = {};
                    // 削除フラグの設定
                    param.del_flg = parseInt($scope.item.bizcard.del_flg);
                    // 公開対象が「グループ」の場合、チェックされた利用者をdisplay_targetにセット
                    if ($scope.item.bizcard.display_type == DISPLAY_TYPE.GROUP) {
                        $scope.item.bizcard.display_target = [];
                        $('.list-group input[type="checkbox"]').filter(':checked').each(function () {
                            if ($(this).attr('id').startsWith('user')) {
                                $scope.item.bizcard.display_target.push($(this).attr('id').slice('user'.length));
                            }
                        })
                    }
                    param.display_type = parseInt($scope.item.bizcard.display_type, 10);
                    // 公開対象が「会社」以外の場合、display_targetに数字以外が含まれる場合、削除
                    if (param.display_type != DISPLAY_TYPE.COMPANY) {
                        for (let i = $scope.item.bizcard.display_target.length - 1; i >= 0; i--) {
                            let regexp = new RegExp(/^[0-9]+$/);
                            if (!regexp.test($scope.item.bizcard.display_target[i])) {
                                $scope.item.bizcard.display_target.splice(i, 1);
                            }
                        }
                        // 公開対象が「会社」以外の場合、公開対象が選択されていなければエラー表示
                        if ($scope.item.bizcard.display_target.length == 0) {
                            let targetName = param.display_type == DISPLAY_TYPE.DEPARTMENT ? '部署' : '利用者';
                            $("#modalDisplaySetting .message").append(showMessages(['公開する' + targetName + 'を選択してください'], 'danger', 10000));
                            $rootScope.$emit("hideLoading");
                            return;
                        }
                        // 公開対象をパラメータにセット
                        param.display_target = $scope.item.bizcard.display_target.map(target => parseInt(target, 10));
                    }
                    $http.post(link_ajax + '/' + id, param)
                    .then(function (event) {
                        $rootScope.$emit("hideLoading");
                        if (event.data.status == false) {
                            $("#modalDisplaySetting .message").append(showMessages(event.data.message, 'danger', 10000));
                        } else {
                            hasChange = true;
                            $("#modalDisplaySetting .message-info").append(showMessages(event.data.message, 'success', 10000));
                        }
                    });
                }

                $scope.remove = function () {
                    $rootScope.$emit("showMocalConfirm", {
                        title:'名刺を削除します。よろしいですか？',
                        btnDanger:'はい',
                        databack: $scope.item.id,
                        callDanger: function (id) {
                            $rootScope.$emit("showLoading");
                            $http.post(link_deletes, {bizcard_ids: [id]})
                            .then(function (event) {
                                if(event.data.status == false){
                                    $("#modalDisplaySetting .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $("#modalDisplaySetting .message-info").append(showMessages(event.data.message, 'warning', 10000));
                                    location.reload();
                                }
                            });
                        }
                    });
                };
                // 公開種別変更時の動作
                $scope.changeDisplayType = function () {
                    //選択済み公開対象の選択を解除し、公開対象の表示を切り替える
                    $scope.item.bizcard.display_target = [];
                    initializeGroupCheckbox($scope.item.bizcard.display_target);
                    switchTargetDisplay();
                }
                // 公開種別に応じて公開対象の表示を切り替える
                var switchTargetDisplay = function (_display_target = null) {
                    let selectedDisplayType = _display_target != null ? _display_target : $scope.item.bizcard.display_type;
                    if (selectedDisplayType == DISPLAY_TYPE.COMPANY) {
                        // 公開種別「会社」が選択された場合、公開対象を非表示にする
                        $('.display-target-row').addClass('hide');
                    } else {
                        $('.display-target-row').removeClass('hide');
                    }
                    $('.display-target').each(function(){
                        // 選択された公開種別に応じて、公開対象の表示を切り替える
                        let display_type = $(this).data('type');
                        if (display_type == selectedDisplayType) {
                            $(this).removeClass('hide');
                        } else {
                            $(this).addClass('hide');
                        }
                    });
                }
                // 項目の頭の三角形クリック時に項目を開閉するよう設定
                $(document).on('click', '.arrow', function() {
                    let treeNode = $(this).closest('.tree-node');
                    if (treeNode.hasClass('open')) {
                        treeNode.addClass('open');
                    } else {
                        treeNode.removeClass('open');
                    }
                });
                // 最下層の項目の頭に三角形を表示しないよう制御
                var resetTreeArrow = function () {
                    $('.list-group .tree-node').each(function(){
                        if ($(this).children('.items').children('li').length > 0) {
                            $(this).children('.name').children('.arrow').children().removeClass('hide');
                        }else{
                            $(this).children('.name').children('.arrow').children().addClass('hide');
                        }
                    })
                }
                // グループ選択用データのチェックボックスの状態を初期化
                var initializeGroupCheckbox = function (display_target) {
                    // 一度すべてのチェックを外す
                    $('.list-group input[type="checkbox"]').prop('checked', false);
                    display_target.forEach(targetId => {
                        $('.list-group input[type="checkbox"][id="user' + targetId + '"]').prop('checked', true);
                    })
                }
            });
        }
    </script>
@endpush
<style>
    .hide{
        display: none!important;
    }
</style>
