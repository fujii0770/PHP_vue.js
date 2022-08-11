@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_ajax = "{{ route('Holiday.Index') }}",
            link_ajax_store = "{{ route('Holiday.Store') }}",
            link_ajax_destory="{{ route('Holiday.Destroy') }}",
            link_ajax_reset="{{ route('Holiday.Reset') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div ng-controller="HolidayController" class="list-holiday">

        <div class="message message-list"></div>

        <form action="" id="holiday_search" name="adminForm" method="GET">
            <div class="form-search form-horizontal">
                <div class="row mt3">
                    <div class="col-lg-4 mt-3 row">
                        <label class="col-lg-3 control-label year-label" for="year">年</label>
                        <div class="col-md-5">
                            <select class="form-control" name="year" id="year" ng-model="year" ng-change="searchHoliday()" ng-options="year for year in years track by year"></select>
                        </div>
                    </div>
                    <div class="col-lg-4"></div>

                    <div class="col-lg-4 text-right mt-3">
                        @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_CREATE])
                            <button type="button" class="btn btn-success mb-1"
                                    ng-click="addHolidayModal()"><i class="fas fa-plus-circle" ></i> 登録</button>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_DELETE])
                            <button type="button" class="btn btn-danger mb-1" ng-disabled="numChecked==0"
                                    ng-click="deleteHoliday()"><i class="fas fa-trash-alt"></i> 削除
                            </button>
                            <button type="button" class="btn btn-warning mb-1"
                                    ng-click="resetHoliday()"><i class="fas fa-recycle"></i> 初期化</button>
                        @endcanany
                    </div>
                </div>
            </div>
        </form>

        <div class="card mt-3">
            <span class="clear"></span>
            <table class="tablesaw-list tablesaw table-bordered adminlist" data-tablesaw-mode="swipe">
                <thead>
                <tr>
                    <th class="title sort" scope="col" data-tablesaw-priority="persist">
                        <input type="checkbox" onClick="checkAll(this.checked)"/>
                    </th>
                    <th class="title sort" scope="col" data-tablesaw-priority="persist">日付</th>
                    <th scope="col">祝日名</th>
                </tr>
                </thead>
                <tbody>
                <tr ng-repeat="holiday in holidays">
                    <td class="title">
                        <input type="checkbox" value="<% holiday.id %>" datatype="<% holiday.type %>" dataname="<% holiday.name %>" class="cid" onClick="isChecked(this.checked)"/>
                    </td>
                    <td ng-if="holiday.type===1" class="pointer" scope="row" ng-click="updateHolidayModal(holiday)" ng-bind="holiday.month+'月'+holiday.day+'日'"></td>
                    <td ng-if="holiday.type===1" class="pointer" ng-click="updateHolidayModal(holiday)" ng-bind="holiday.name"></td>
                    <td ng-if="holiday.type===0" scope="row" ng-bind="holiday.month+'月'+holiday.day+'日'"></td>
                    <td ng-if="holiday.type===0" ng-bind="holiday.name"></td>
                </tr>
                </tbody>
            </table>
            @include('layouts.table_footer',['data' => $pageHolidays])
            <input type="hidden" name="page" value="{{Request::get('page',1)}}">
            <input type="hidden" name="page" value="{{Request::get('limit',1)}}">
            <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;"/>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="holidayModal" tabindex="-1" role="dialog" aria-labelledby="holidayModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="" method="POST" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title">休日設定</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="message message-info"></div>
                            <div class="form-group row">
                                <label for="date" class="col-sm-2 col-form-label">日付</label>
                                <div class="col-sm-10">
                                    <input type="date" class="form-control" id="date"
                                           ng-model="updateTarget.date" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">祝日名</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="name" ng-model="updateTarget.name"
                                           required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="name" class="col-sm-2 col-form-label">色</label>
                                <div class="col-sm-10">
                                    <input type="color" class="form-control check-color" name="color" ng-model="updateTarget.color">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" ng-if="updateTarget.id==null">
                            <button type="button" class="btn btn-success" ng-click="storeHoliday()">
                                <i class="far fa-save"></i> 登録
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i
                                        class="fas fa-times-circle"></i> キャンセル
                            </button>
                        </div>
                        <div class="modal-footer" ng-if="updateTarget.id!=null">
                            @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_UPDATE])
                            <button type="button" class="btn btn-success" ng-click="updateHoliday()">
                                <i class="far fa-save"></i> 更新
                            </button>
                            @endcanany
                            @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_DELETE])
                            <button type="button" class="btn btn-danger" ng-click="destroyHoliday()">
                                <i class="fas fa-trash-alt"></i> 削除
                            </button>
                            @endcanany
                        </div>
                        <input type="hidden" name="id" ng-model="updateTarget.id">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var failureMessage = '{{$failureMessage}}';
        var allHolidays = {!! json_encode($holidays) !!};

        var resetAllHolidays = function () {
            allHolidays.sort(function (a, b) {
                return a.date >= b.date ? 1 : -1;
            });

            for (var i = 0; i < allHolidays.length; i++) {
                var holiday = allHolidays[i];
                holiday.year = holiday.date.substring(0, 4);
                holiday.month = parseInt(holiday.date.substring(5, 7), 10);
                holiday.day = parseInt(holiday.date.substring(8, 10), 10);
            }
        };
        resetAllHolidays();

        if (failureMessage) {
            $(".message-list").append(showMessages([failureMessage], 'danger', 10000));
        }

        if (appPacAdmin) {
            appPacAdmin.controller('HolidayController', function ($scope, $rootScope, $http) {
                $scope.year = {{$year}};
                $scope.holidays = allHolidays;

                $scope.updateTarget = {
                    id: null,
                    date: '',
                    name: '',
                    color: '#FBF6F2',
                };
                $scope.destroyHolidayName = '';
                $scope.numChecked = 0;

                $scope.years = [];
                for (var i = {{$min_year}}; i <= {{$max_year}}; i++) {
                    $scope.years.push(i);
                }

                $scope.searchHoliday = function () {
                    $('#holiday_search').submit();
                }

                $scope.addHolidayModal = function () {
                    $("#holidayModal").modal();
                    var now = new Date();
                    $scope.updateTarget = {
                        id: null,
                        date: new Date($scope.year + '-' + (now.getMonth() + 1) + '-' + now.getDate()),
                        name: '',
                        color: '#FBF6F2',
                    };
                };

                $scope.storeHoliday = function () {
                    if (!$scope.updateTarget.date) {
                        $(".message-info").append(showMessages(['日付を選択してください'], 'danger', 10000));
                        return;
                    }
                    if (!$scope.updateTarget.name) {
                        $(".message-info").append(showMessages(['祝日名を入力してください'], 'danger', 10000));
                        return;
                    }
                    var date = $scope.updateTarget.date;
                    var date_str = date.getFullYear() + '-' + zeroPad(date.getMonth() + 1, 2) + '-' + zeroPad(date.getDate(), 2);

                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_store, {
                        date: date_str,
                        name: $scope.updateTarget.name,
                        color: $scope.updateTarget.color
                    })
                    .then(function (event) {
                        if (event.data.status) {
                            $(".message-info").append(showMessages(event.data.message, 'success', 10000));
                            location.reload();
                        } else {
                            $(".message-info").append(showMessages(event.data.message, 'danger', 10000));
                        }
                    })
                    .catch(function () {
                        $(".message-info").append(showMessages(['休日の追加に失敗しました。'], 'danger', 10000));
                    })
                    .finally(function () {
                        $rootScope.$emit("hideLoading");
                    });
                };

                @canany([PermissionUtils::PERMISSION_HOLIDAY_SETTING_UPDATE])
                $scope.updateHolidayModal = function (holiday) {
                    $scope.destroyHolidayName = holiday.name;
                    $scope.updateTarget = {
                        id: holiday.id,
                        date: new Date(holiday.date),
                        name: holiday.name,
                        color: holiday.color,
                    };
                    $("#holidayModal").modal();
                };
                @endcanany

                $scope.updateHoliday = function () {
                    if (!$scope.updateTarget.date) {
                        $(".message-info").append(showMessages(['日付を選択してください'], 'danger', 10000));
                        return;
                    }
                    if (!$scope.updateTarget.name) {
                        $(".message-info").append(showMessages(['祝日名を入力してください'], 'danger', 10000));
                        return;
                    }
                    var date = $scope.updateTarget.date;
                    var month = date.getMonth() + 1;
                    var day = date.getDate();
                    month = month < 10 ? 0 + '' + month : month;
                    day = day < 10 ? 0 + '' + day : day;
                    var date_str = date.getFullYear() + '-' + month + '-' + day;

                    $rootScope.$emit("showLoading");
                    $http.put('holiday/' + $scope.updateTarget.id, {
                        date: date_str,
                        name: $scope.updateTarget.name,
                        color: $scope.updateTarget.color
                    })
                    .then(function (event) {
                        if (event.data.status) {
                            $(".message-info").append(showMessages(event.data.message, 'success', 10000));
                            location.reload();
                        } else {
                            $(".message-info").append(showMessages(event.data.message, 'danger', 10000));
                        }
                    })
                    .catch(function () {
                        $(".message-info").append(showMessages(['休日の変更に失敗しました。'], 'danger', 10000));
                    })
                    .finally(function () {
                        $rootScope.$emit("hideLoading");
                    });
                };

                $scope.deleteHoliday = function () {
                    var customHolidayIdList = [],
                        japaneseHolidayIdList = [],
                        holiday_name = [];
                    for(var i =0; i < $(".cid:checked").length; i++){
                        var type = $($(".cid:checked")[i]).attr('datatype');
                        if (type == 0) {
                            japaneseHolidayIdList.push($(".cid:checked")[i].value);
                        } else {
                            customHolidayIdList.push($(".cid:checked")[i].value);
                        }
                        holiday_name.push($($(".cid:checked")[i]).attr('dataname'));
                    }
                    $rootScope.$emit("showMocalConfirm", {
                        title: holiday_name.join('、') + 'を削除してもよろしいでしょうか？',
                        btnDanger: 'はい',
                        callDanger: function () {
                            $rootScope.$emit("showLoading");
                            $http.delete(link_ajax_destory, {params: {'customHolidayIdList': customHolidayIdList.toString(), 'japaneseHolidayIdList': japaneseHolidayIdList.toString(), year: $scope.year}})
                            .then(function (event) {
                                if (event.data.status) {
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    location.reload();
                                } else {
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }
                            })
                            .catch(function () {
                                $(".message-list").append(showMessages(['休日の削除に失敗しました。'], 'danger', 10000));
                            })
                            .finally(function () {
                                $rootScope.$emit("hideLoading");
                            });
                        }
                    });
                };

                $scope.destroyHoliday = function () {
                    var customHolidayIdList = [$scope.updateTarget.id];
                    $rootScope.$emit("showMocalConfirm", {
                        title: $scope.destroyHolidayName + 'を削除してもよろしいでしょうか？',
                        btnDanger: 'はい',
                        callDanger: function () {
                            $rootScope.$emit("showLoading");
                            $http.delete(link_ajax_destory, {params: {'customHolidayIdList': customHolidayIdList.toString(), year: $scope.year}})
                            .then(function (event) {
                                if (event.data.status) {
                                    $(".message-info").append(showMessages(event.data.message, 'success', 10000));
                                    location.reload();
                                } else {
                                    $(".message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }
                            })
                            .catch(function () {
                                $(".message-info").append(showMessages(['休日の削除に失敗しました。'], 'danger', 10000));
                            })
                            .finally(function () {
                                $rootScope.$emit("hideLoading");
                            });
                        }
                    });
                };

                $scope.resetHoliday = function (holiday) {
                    $rootScope.$emit("showMocalConfirm", {
                        title: '初期化してもよろしいですか？',
                        btnDanger: 'はい',
                        callDanger: function () {
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax_reset, {
                                year: $scope.year
                            })
                            .then(function (event) {
                                if (event.data.status) {
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    location.reload();
                                } else {
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }
                            })
                            .catch(function () {
                                $(".message-list").append(showMessages(['休日の初期化に失敗しました。'], 'danger', 10000));
                            })
                            .finally(function () {
                                $rootScope.$emit("hideLoading");
                            });
                        }
                    });
                };
            });
        } else {
            throw new Error("Something error init Angular.");
        }
        function zeroPad(num, count) {
            return ('0' + num).slice(-count);
        }
    </script>
@endpush

@push('styles_after')
    <style>
        input.check-color {
            width: 50%;
            padding: 0;
        }
        .pointer {cursor: pointer;background-color: #FBF6F2;}
        .year-label {font-weight: 500;line-height: 38px;}
</style>
@endpush
