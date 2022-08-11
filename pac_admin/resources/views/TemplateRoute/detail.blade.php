<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" style="max-width: 50%;">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!item.id">承認ルート登録</h4>
                        <h4 class="modal-title" ng-show="item.id">承認ルート更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->

                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        {!! \App\Http\Utils\CommonUtils::showFormField('name','名称 ','','name', true,
                                [ 'placeholder' =>'承認ルート', 'ng-model' =>'item.name', 'ng-readonly'=>"readonly", 'id'=>'routeName','col'=>2 ]) !!}
                        <div class="form-group" style="display: flex">
                            <label for="name" class="col-md-2 control-label">回覧先</label>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-warning" ng-click="add()"><i
                                            class="fas fa-plus-circle"></i> 追加
                                </button>
                            </div>
                        </div>

                        <!-- temp -->
                        <div id="template-area-div" class="form-group">
                            <ul id="template-area">
                                <li id="template-elm<% key %>" data-index="<% key %>"
                                    ng-repeat="(key,route) in item.routes">
                                    <div class="row route_row" route="<% route %>">
                                        <label for="admin-group" class="col-xs-12 control-label short-label"> 部署 </label>
                                        <div class="row-input select">
                                            <select ng-model="route.mst_department_id" required="1" id="department_<% key %>" class="form-control select2">
                                                <option></option>
                                                <option ng-value="department.id" ng-repeat="department in listDepartment"><% department.text %></option>
                                            </select>
                                        </div>
                                        <label for="admin-group" class="col-xs-12 control-label short-label"> 役職 </label>
                                        <div class="row-input select">
                                            <select ng-model="route.mst_position_id" id="position_<% key %>" required="1" class="form-control select2">
                                                <option></option>
                                                <option ng-value="position.id" ng-repeat="position in listPosition"><% position.text %></option>
                                            </select>
                                        </div>
                                        <label for="admin-group" class="col-xs-12 control-label short-label"> 合議 </label>
                                        <div class="row-input radio">
                                            <div class="font-normal">
                                                <input type="radio" id="mode_all<% key %>" name="mode<% key %>" required="1" ng-model="route.mode" ng-value="1" ng-click="memberSet('all',key)">
                                                <label for="mode_all<% key %>"> 全員必須 </label><br/>
                                                <input type="radio" id="mode<% key %>" name="mode<% key %>" required="1" ng-model="route.mode" ng-value="3" ng-click="memberSet('',key)">
                                                <label for="mode<% key %>"> 人数指定 </label>
                                            </div>
                                        </div>
                                        <div class="row-input text">
                                            <input type="text" oninput = "value=value.replace(/[^\d]/g,'')" id="option<% key %>" class="form-control agree-number-text" maxlength="2" data-prompt-position="bottomRight" ng-model="route.option" required="<% route.mode == 3 %>" ng-disabled="<% !route.mode || route.mode == 1 %>"><span> 人</span>
                                        </div>
                                    </div>
                                    <button ng-if="item.routes.length > 1" type="button" class="close"
                                            ng-click="remove(key,route.id)" style="font-size: 1rem;height: 10px">×
                                    </button>
                                    <div id="arrow<% key %>"
                                         ng-style="{'display':(item.routes.length > 1 && key < item.routes.length - 1) ? 'block' : 'none'}"
                                         class="arrow template" align="center">
                                        <img class="img_down" src="{{ asset('images/down_arrow.png') }}">
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <label for="admin-enabled" class="col-md-3 col-sm-3 col-xs-12 control-label"
                                   style="display: inline-block">有効</label>
                            <div class="checkbox col-md-8 col-sm-8 col-xs-12" style="display: inline-block">
                                <label><input id="template-state" ng-model="item.state" type="checkbox"
                                              ng-true-value="1" ng-false-value="0">有効にする</label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @can([PermissionUtils::PERMISSION_TEMPLATE_ROUTE_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="save()"
                                    ng-show="!item.id">
                                <i class="far fa-save"></i> 登録
                            </button>
                        @endcan
                        @can([PermissionUtils::PERMISSION_TEMPLATE_ROUTE_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="save()"
                                    ng-show="item.id">
                                <i class="far fa-save"></i> 更新
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
    <script src="{{ asset('/js/libs/Sortable/Sortable.js') }}"></script>
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailController', function ($scope, $rootScope, $http) {
                $scope.item = {
                    routes: []
                };
                $scope.routes = [{}];
                $scope.id = 0;
                $scope.readonly = false;
                $scope.deleteIds = [];
                $scope.listDepartment = {!! json_encode($listDepartmentDetail) !!};
                $scope.listPosition = {!! json_encode($listPosition) !!};

                // 登録
                $rootScope.$on("openNewRoute", function () {
                    hideMessages();
                    $scope.item = {
                        routes: []
                    };
                    $scope.item.routes = [{}];
                    $scope.id = 0;
                    $scope.routes = [{}];
                    $scope.deleteIds = [];
                    $("#modalDetailItem").modal();
                });

                // 更新
                $rootScope.$on("openEditRoute", function (event, data) {
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    $scope.deleteIds = [];
                    hideMessages();
                    if (allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    var promise = $http.get(link_ajax + "/" + $scope.id);

                    return promise.then(function (event) {
                        $rootScope.$emit("hideLoading");
                        if (event.data.status == false) {
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        } else {
                            $scope.item = event.data.item;
                            $scope.routes = $scope.item.routes;
                        }
                        $("#modalDetailItem").modal();
                    });
                });

                /* ノードを追加 */
                $scope.add = function () {
                    $scope.item.routes.push({});
                }
                /* ノードを削除 */
                $scope.remove = function (key, routeId) {
                    $scope.item.routes.splice(key, 1);
                    $scope.deleteIds.push(routeId);
                }

                /* 合議設定 */
                $scope.memberSet = function (type, nodeId) {
                    if (type === 'all') {
                        $("#option" + nodeId).val('');
                        $("#option" + nodeId).attr("disabled", true);
                    } else {
                        $("#option" + nodeId).attr("disabled", false);
                    }
                }
                // 登録と更新時
                $scope.save = function (callSuccess) {
                    if ($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.item = event.data.item;
                                $scope.id = $scope.item.id;
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                $("#modalDetailItem").modal();
                                if (callSuccess) callSuccess();
                            }
                        }

                        /* 属性設定 */
                        let routeArr = document.getElementsByClassName('route_row');
                        let routes = [];
                        for(let i = 0;i<routeArr.length;i++){
                            routes.push(JSON.parse(routeArr[i].getAttribute('route')));
                        }
                        $scope.item.routes = routes;
                        // 新規
                        if (!$scope.item.id) {
                            $http.post(link_ajax, {item: $scope.item})
                                .then(saveSuccess);
                        } else { //更新
                            $http.put(link_ajax + "/" + $scope.id, {item: $scope.item, deleteIds: $scope.deleteIds})
                                .then(saveSuccess);
                        }
                        hasChange = true;
                    }
                };

                // ドラック動作
                var el = document.getElementById('template-area');
                var ops = {
                    animation: 500,
                    sort: true,
                    // ドラック終了
                    onEnd: function (evt) {
                        $('#template-area li:not(:last)').find('.arrow').show();
                        $('#template-area li:last-child').find('.arrow').hide();

                        // ドラック後のソート
                        var arr = sortable.toArray();
                    },
                };
                // 初期化
                var sortable = Sortable.create(el, ops);

            })
        }
    </script>
@endpush
{{--<script>
    //
    let startNode;
    let endNode;
    let startChild;
    let endChild;

    // ドラッグして開始
    function dragStart(event, obj) {
        startNode = obj.parentNode;
        startChild = obj;
    }
    // ドラッグして終了
    function dragEnd(event) {
        // アングラーパラメータを取得
        let appElement = document.querySelector('[ng-controller=DetailController]');
        let $scope = angular.element(appElement).scope();
        let startOrder = JSON.parse(startChild.getAttribute('route')).child_send_order;
        let endOrder = JSON.parse(endChild.getAttribute('route')).child_send_order;

        $scope.$apply(function () {
            $scope.item.routes.forEach(function (route, index) {
                // 从下往上
                if (startOrder > endOrder) {
                    if (route.child_send_order > startOrder || route.child_send_order < endOrder) {
                        return;
                    } else if (route.child_send_order === startOrder) {
                        route.child_send_order = endOrder;
                    } else if (route.child_send_order >= endOrder) {
                        route.child_send_order += 1;
                    }
                } else if (startOrder < endOrder) { // 从上往下
                    if (route.child_send_order < startOrder || route.child_send_order >= endOrder) {
                        return;
                    } else if (route.child_send_order === startOrder) {
                        route.child_send_order = endOrder - 1;
                    } else if (route.child_send_order > startOrder) {
                        route.child_send_order -= 1;
                    }
                }
            })
            $scope.item.routes.sort((a, b) => a.child_send_order - b.child_send_order);
        });
    }

    function dragEnter(event, obj) {
        endNode = obj.parentNode;
        endChild = obj;
    }
</script>--}}
@push('styles_after')
    <style>
        ol, ul {
            margin-top: 0;
            margin-bottom: 10px;
        }

        #template-area-div ul {
            float: left;
            padding: 0;
            width: 100%;
            overflow: hidden;
            border-radius: 6px;
            margin-bottom: 0;
        }

        #template-area-div li {
            display: flex;
            position: relative;
            padding: 12px 6px;
            z-index: 1;
            margin: 0 20px 25px 20px;
        }

        #template-area-div li:after {
            background: #a1cfa1;
            display: block;
            position: absolute;
            height: 100%;
            width: 100%;
            top: 0;
            left: 0;
            content: ' ';
            border-radius: 6px;
            z-index: -1;
        }

        .row {
            width: 100%;
            align-items: center;
            flex-wrap: nowrap;
        }

        .row > .row-input {
            flex: 1 1 auto;
            margin-left: 10px;
        }

        .row-input.select {
            flex: 1 1 20%;
        }

        .row-input.text {
            display: flex;
        }

        .row-input.text span {
            padding: 10px 2px;
        }

        #template-area-div label.short-label {
            width: 60px;
            padding: 7px 5px;
        }

        .agree-number-text {
            max-width: 46px;
        }

        .arrow.template {
            position: absolute;
            left: 0px;
            top: 82px;
            height: 18px;
            width: 100%;
            background-size: contain;
            border: 0;
            z-index: 10000;
        }

        .img_down {
            height: 18px;
            width: 18px;
        }
    </style>
@endpush