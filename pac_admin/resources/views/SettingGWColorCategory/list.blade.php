<div ng-controller="FacilityController" class="list-facility">
    <form action="" name="adminForm">
        <div class="form-search form-horizontal">
            <div class="message"></div>
            <div class="text-right mt-3">
                @canany([PermissionUtils::PERMISSION_COLORCATEGORY_SETTING_CREATE])
                    <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle"></i> 登録</div>
                @endcanany
                {{--            @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_UPDATE])--}}
                {{--                <div class="btn btn-success mb-1" ng-click="reName()"><i class="fas fa-edit" ></i> 名称変更</div>--}}
                {{--            @endcanany--}}
                {{--            @canany([PermissionUtils::PERMISSION_FACILITY_SETTING_DELETE])--}}
                {{--                <button type="button" class="btn btn-danger mb-1" ng-click="deleteFacility()"><i class="fas fa-trash-alt"></i> 削除</button>--}}
                {{--            @endcanany--}}
            </div>
        </div>

        <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe"
               data-tablesaw-mode="swipe" id="tablesaw-4576">
            <thead>
            <tr>
                <th scope="col" class="sort" style="width: 6%;text-align: center">
                    色
                </th>
                <th scope="col" class="sort">
                    {!! \App\Http\Utils\CommonUtils::showSortColumn('種別名', 'facility_name', 'facility_name', $orderDir) !!}
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($arrListData as $info)
                <tr ng-class="{'selected': selectedID == {{ $info['id'] }}}"
                    ng-click="selectRow({id:{{$info['id']}}, name:'{{$info['typeName']}}'})" class="record">
                    <td style="text-align: center">
                        <div class="circle"
                             style="background-color: {{$info['mstColor']['code']}}"></div>
                    </td>
                    <td>{{ $info['typeName'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $dataCount }} 件中 {{ $dataFrom }} 件から {{ $dataTo }} 件までを表示</div>
        <div class="text-center" ng-if="{{$lastPage}} > 1">
            <nav>
                <ul class="pagination">
                    <li class="page-item" ng-class="{'disabled': {{$currentPage}} <= 1}"
                        :aria-disabled="{ {{$currentPage}} <= 1 }" ng-click="changeDetailPage({{$currentPage}} - 1)"
                        aria-label="« 前へ">
                        <span class="page-link" aria-hidden="true">‹</span>
                    </li>

                    <li class="page-item" ng-class="{'active' : $index+1 == {{$currentPage}} }"
                        ng-repeat="page_index in range_func({{$lastPage}}) track by $index"
                        ng-click="changeDetailPage($index+1)">
                        <a class="page-link" ng-bind-html="$index+1"></a>
                    </li>

                    <li class="page-item" ng-class="{'disabled': {{$currentPage}} >= {{$lastPage}} }"
                        :aria-disabled="{ {{$currentPage}} >= {{$lastPage}} }"
                        ng-click="changeDetailPage({{$currentPage}} + 1)">
                        <a class="page-link" rel="next" aria-label="次へ »">›</a>
                    </li>
                </ul>
            </nav>
        </div>

        <input type="hidden" class="action" name="action" value=""/>
        <input type="hidden" value="facility_name" name="orderBy"/>
        <input type="hidden" value="{{ Request::get('orderDir','asc') }}" class="orderDir" name="orderDir"/>
        <input type="hidden" class="currentPage" name="currentPage" value="{{$currentPage}}">
        <input type="hidden" class="lastPage" name="lastPage" value="{{$lastPage}}">
    </form>
</div>

<style>
    .list-facility tr.record:hover {
        background-color: #e7f4f9;
    }

    .list-facility tr.record.selected {
        background-color: #beebff;
    }

</style>

@push('scripts')
    <script>

        if ("{{$success_message}}" != "") {
            $(".list-facility .message").append(showMessages(["{{$success_message}}"], 'success', 10000));
        }
        if ("{{$failure_message}}" != "") {
            $(".list-facility .message").append(showMessages(["{{$failure_message}}"], 'danger', 10000));
        }

        hasChangeFacility = false;
        if (appPacAdmin) {
            appPacAdmin.controller('FacilityController', function ($scope, $rootScope, $http) {

                $scope.range_func = function (n) {
                    return new Array(n);
                };

                $scope.changeDetailPage = function (page) {

                    if (page == $(".currentPage").val() || page < 1 || page > $(".lastPage").val()) {
                        return;
                    }

                    var orderDir = $(".orderDir").val();
                    var url = link_ajax_index + "?page=" + page + "&orderDir=" + orderDir;
                    location.href = url;

                };

                $scope.selectedID = null;

                $scope.selectRow = function (data) {
                    if ($scope.selectedID == data.id) {
                        $scope.selectedID = null;
                        $scope.selectedNAME = null;
                        $('.btn-del-facility').prop('disabled', true);
                    } else {
                        $scope.selectedID = data.id;
                        $scope.selectedNAME = data.name;
                        $('.btn-del-facility').prop('disabled', false);
                        $('input[name="selectID"]').val($scope.selectedID);
                    }

                    $rootScope.$emit("openEditFacility", {id: $scope.selectedID, name: $scope.selectedNAME});
                };

                $('.btn-del-facility').click(function (e) {
                    $('#deleteModal').modal('show');
                });

                $scope.addNew = function () {
                    $rootScope.$emit("openNewFacility");
                };

                $scope.reName = function () {
                    $rootScope.$emit("openEditFacility", {id: $scope.selectedID, name: $scope.selectedNAME});
                };

                $scope.deleteFacility = function () {
                    if ($scope.selectedID) { //選択されている場合のみ実行
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '選択中の設備を削除しますか',
                                btnDanger: 'はい',
                                callDanger: function () {
                                    $rootScope.$emit("showLoading");
                                    $http.delete(link_ajax_delete + "/" + $scope.selectedID)
                                        .then(function (event) {
                                            $rootScope.$emit("hideLoading");
                                            if (event.data.status == false) {
                                                $(".list-facility .message").append(showMessages(event.data.message, 'danger', 10000));
                                            } else {
                                                $(".list-facility .message").append(showMessages(event.data.message, 'success', 10000));
                                                location.reload();
                                            }
                                        });
                                }
                            });
                    }
                };
            });


        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
            if (hasChangeFacility) {
                location.reload();
            }
        });
    </script>
@endpush



@push('styles_after')
    <style>
        .circle-box {
            display: inline-block;
            width: 32px;
            height: 32px;
            position: relative;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .circle {
            display: inline-grid;
            margin-left: 0px;
            width: 32px;
            height: 32px;
            background: #093;
            border-radius: 50%;
            justify-content: center;
            align-items: center;
        }

        .yesCircle {
            display: inline-grid;
            margin-left: 0px;
            width: 32px;
            height: 32px;
            background: #093;
            border-radius: 50%;
            justify-content: center;
            align-items: center;
        }

        .yesCircle::before {
            position: absolute;content: '';width: calc(1.2 * 16px);height: calc(0.7 * 16px);transform: rotate(-45deg);border-left: 3px solid #fff;border-bottom: 3px solid #fff;left: calc(0.4 * 16px);top: calc(0.5 * 16px);
        }
    </style>
@endpush
