<div ng-controller="TalkController">
    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="GET">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-3 form-group">
                    <label class="control-label" >ササッとTalk利用状況</label>
                    {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_SELECT_SEARCH, 'status', Request::get('status', ''),'',['class'=> 'form-control']) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('chat_user_name','ササッとTalkユーザー名',Request::get('chat_user_name', ''),'text', false,
                        [ 'placeholder' =>'ササッとTalkユーザー名', 'id'=>'chat_user_name' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('email_option','通知先メールアドレス',Request::get('email_option', ''),'text', false,
                        [ 'placeholder' =>'通知先メールアドレス', 'id'=>'email_option' ]) !!}
                </div>
                <div class="col-lg-3"></div>
            </div>

            <div class="row">
                <div class="col-lg-3 form-group">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('email','ユーザーID',Request::get('email', ''),'text', false,
                                        [ 'placeholder' =>'ユーザーID', 'id'=>'email' ]) !!}
                </div>

                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('username','氏名',Request::get('username', ''),'text', false,
                    [ 'placeholder' =>'氏名', 'id'=>'username' ]) !!}
                </div>
                <div class="col-lg-3"></div>
                <div class="col-lg-3 text-center padding-top-20" style="padding-right:39px">
                    <button id="btnSearchAction"  class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                </div>
            </div>

            <div class="message message-list mt-3"></div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_CREATE])
                                    <button type="button" class="btn btn-success m-0" ng-click="bulkUsage(1)">一括利用登録</button>
                                    @endcanany
                                    @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_UPDATE])
                                        <button type="button" class="btn btn-warning m-0" ng-click="bulkUsage(2)">一括利用停止</button>
                                    @endcanany
                                    @canany([PermissionUtils::PERMISSION_TALK_USER_SETTING_DELETE])
                                    <button type="button" class="btn btn-danger m-0" ng-click="bulkUsage(0)">一括利用解除</button>
                                    @endcanany
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                    <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>

                            </th>
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('ササッとTalk利用状況', 'status', $orderBy, $orderDir) !!}
                            </th>
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('ユーザーID', 'email', $orderBy, $orderDir) !!}
                            </th>
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('ササッとTalkユーザー名', 'chat_user_name', $orderBy, $orderDir) !!}
                            </th>
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('通知先メールアドレス', 'email_option', $orderBy, $orderDir) !!}
                            </th>
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'username', $orderBy, $orderDir) !!}
                            </th>

                        </tr>
                        </thead>

                        <tbody>
                            @if($resultData)
                                @foreach($resultData as $i => $item)
                                    <tr class="row-{{ $item->id }} row-edit" ng-class="{ edit: id == {{ $item->id }} }">

                                        <td class="title">
                                            <input type="checkbox" value="{{ $item->id }}"
                                                   name="cids[]" class="cid" onClick="isChecked(this.checked)"
                                                   ng-click="toogleCheck({{ $item->id }})"/>
                                        </td>
                                        <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ isset($item->status) ? \App\Http\Utils\AppUtils::CHAT_SERVER_USER_STATUS_MAP_DATA[$item->status] : '未利用' }}</td>
                                        <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ $item->email }}</td>
                                        <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ isset($item->chat_user_name) ? $item->chat_user_name : '未登録'}}</td>
                                        <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ $item->email_option }}</td>
                                        <td ng-dblclick="detailsRecord({{ $item->id}})" class="title">{{ $item->username }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    @if ($resultData && count($resultData) > 0)
                        @include('layouts.table_footer',['data' => $resultData])
                    @else
                        <div class="mt-3">0 件中 0 件から 0 件までを表示</div>
                    @endif
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="action" value="">
            </div>

        </div>
    </form>
</div>

@push('scripts')

    <script>
        if(appPacAdmin){
            appPacAdmin.controller('TalkController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.timeoutReload = 3000;
                $scope.cids = {!! json_encode($resultData->pluck('id')) !!};


                if($('.row-edit input:checkbox:checked').length == $('.row-edit input:checkbox').length && $('.row-edit input:checkbox:checked').length != 0){
                    $scope.isCheckAll = true;
                }else{
                    $scope.isCheckAll = false;
                }

                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                };
                $scope.toogleCheck = function (id) {
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    } else {
                        $scope.selected.push(id);
                    }

                    if($('.row-edit input:checkbox:checked').length == $('.row-edit input:checkbox').length && $('.row-edit input:checkbox:checked').length != 0){
                        $scope.isCheckAll = true;
                    }else{
                        $scope.isCheckAll = false;
                    }
                };

                $scope.bulkUsage = function (type) {

                    event.preventDefault();
                    var cids = [];
                    var cidsoff = [];
                    for (var i = 0; i < $(".cid").length; i++) {
                        var hit = false;
                        for (var i2 = 0; i2 < $(".cid:checked").length; i2++) {
                            if ($(".cid")[i].value == $(".cid:checked")[i2].value) {
                                cids.push($(".cid")[i].value);
                                hit = true;
                                break;
                            }
                        }
                        if (!hit) {
                            cidsoff.push($(".cid")[i].value);
                        }
                    }
                    if ($('.row-edit input:checkbox:checked').length == 0) {
                        if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_REGISTER }}) {
                            $(".message").append(showMessages(['一括利用登録ボタン押下時は、少なくともチェックボックスを一つ選択してください。'], 'danger', 10000));
                        } else if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_CANCEL }}) {
                            $(".message").append(showMessages(['一括利用解除ボタン押下時は、少なくともチェックボックスを一つ選択してください。'], 'danger', 10000));
                        } else if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_STOP }}) {
                            $(".message").append(showMessages(['一括利用停止ボタン押下時は、少なくともチェックボックスを一つ選択してください。'], 'danger', 10000));
                        }
                    } else {
                        if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_REGISTER }}) {
                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '選択されたユーザーのササッとTalk利用者情報を登録します。',
                                    btnSuccess:'はい',
                                    callSuccess: function(){
                                        $rootScope.$emit("showLoading");
                                        $http.post(link_bulk_usage, {
                                            cids: cids,
                                            action: '{{ \App\Http\Utils\ChatUtils::ACTION_MULTIPLE_REGISTER }}',
                                            actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_REGISTER }}',
                                        }).then(function (event) {
                                                $rootScope.$emit("hideLoading");
                                                if (event.data.status == false) {
                                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                                } else {
                                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                                    $scope.reloadPage()
                                                }
                                            });
                                    }
                                });
                        } else if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_CANCEL }}) {
                            $rootScope.$emit("showMocalConfirm", {
                                title: '選択されたユーザーのササッとTalk利用者情報を削除します',
                                btnDanger:'はい',
                                callDanger: function(){
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_bulk_usage, {
                                        cids: cids,
                                        action: '{{ \App\Http\Utils\ChatUtils::ACTION_MULTIPLE_DELETE }}',
                                        actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_DELETE }}',
                                    }).then(function (event) {
                                            $rootScope.$emit("hideLoading");
                                            if (event.data.status == false) {
                                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                            } else {
                                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                                $scope.reloadPage()
                                            }
                                        });
                                }
                            });
                        } else if (type == {{ \App\Http\Utils\ChatUtils::ACTION_TYPE_STOP }}) {
                            $rootScope.$emit("showMocalConfirm", {
                                title: '選択されたユーザーのササッとTalk利用者情報を停止します。',
                                btnWarning:'はい',
                                callWarning: function(){
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_bulk_usage, {
                                        cids: cids,
                                        action: '{{ \App\Http\Utils\ChatUtils::ACTION_MULTIPLE_STOP }}',
                                        actionType: '{{ \App\Http\Utils\ChatUtils::ACTION_GROUP_STOP }}',
                                    }).then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                            $scope.reloadPage()

                                        }
                                    });

                                }
                            });
                        }
                    }
                };

                $scope.detailsRecord = function (id) {
                    $rootScope.$emit("openUserDetailsTalk",{id:id});
                };

                $scope.reloadPage = function () {
                    setTimeout(function (){
                        location.reload();
                    }, $scope.timeoutReload)
                }
            });

        } else{
            throw new Error("Something error init Angular.");
        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
            if(hasChange){
                document.adminForm.action.value = '';
                document.adminForm.submit();
            }
        });

        $("#btnSearchAction").on('click', function () {
            document.adminForm.action.value = 'search';
        });
    </script>
@endpush
