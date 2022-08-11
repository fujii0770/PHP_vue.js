<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET" onsubmit="return false;">
        @csrf
        <div class="message message-list mt-3"></div>
        <div class="card mt-3">
            <div class="card-header">共通印</div>
            <div class="card-body">
                @if(\App\Http\Utils\AppUtils::getStampGroup())
                    @if (count($listGroup))
                <div class="row form-group">
                            <label for="admin_group_name" class="control-label col-md-1 text-right-lg">グループ</label>
                            <div class="input-group col-md-3">
                                <select class="form-control" ng-model="group_id" ng-change="getValue(group_id)">
                                    <option value="99">すべて</option>
                                    <option value="0">グループなし</option>
                                    @foreach ($listGroup as $i => $group)
                                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                            </div>
                        </div>
                    @endif
                @endif
                <div class="row form-group">
                    <label for="" class="control-label col-md-1 text-right-lg">名称</label>
                    <div class="input-group col-md-3">
                        <input type="text" class="form-control" placeholder="名称(部分一致)" ng-keyup="txtNameKeyUp($event)"
                            ng-model="search_name" ng-disabled="onlyUnsigned">
                    </div>

                    <div class="input-group-append" ng-click="searchStamp(1, stamp_pagination.limit)">
                        <span class="input-group-text btn btn-primary"><i class="fas fa-search mr-1"></i> 検索</span>
                    </div>
                    <div class="col-md-7"></div>
                </div>
                <div class="form-group">
                    <label for="onlyUnsigned" class="control-label"><input type="checkbox" ng-model="onlyUnsigned" id="onlyUnsigned" /> 名称未設定の共通印のみを抽出</label>
                </div>
                <div class="form-group">
                    <span ng-if="!showSearch">※印面を検索してください※</span>
                    <span ng-if="showSearch && arrStamp.length == 0">※該当する印面がありません※</span>
                </div>
                <div class="row my-3" ng-show="arrStamp.length != 0">
                    <div class="col-sm-6 col-12">
                        表示件数:
                        <select ng-model="stamp_pagination.limit"
                                ng-change="searchStamp(1,stamp_pagination.limit)"
                                ng-options="option for option in option_limit track by option">
                        </select>
                    </div>
                </div>
                <div class="mt-5 mb-5">
                    <div class="stamp-list" ng-if="arrStamp.length">
                        <div class="stamp-item stamp-item-<% stamp.id %>" ng-repeat="(key, stamp) in arrStamp">
                            <div class="thumb">
                                <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                                <span class="btn btn-warning btn-circle" ng-click="editStamp(key, stamp)">
                                    <i class="fas fa-pencil-alt"></i>
                                </span>
                            </div>
                            <div class="mt-3" style="width: 80px;"><% stamp.stamp_name ? stamp.stamp_name : '名称未設定'%></div>
                        </div>
                    </div>
                </div>
                <div ng-show="arrStamp.length != 0" class="mb-5">
                    <div class="mt-3"><% stamp_pagination.total %> 件中 <% stamp_pagination.from || '0' %> 件から <%stamp_pagination.to || '0'%> 件までを表示</div>
                    <div class="pagination-center" ng-hide="stamp_pagination.total <= stamp_pagination.limit">
                        <div class="pagination">
                            <!--PAC_5-1175 ページ表示数が初期化される現象の修正 -->
                            <button ng-disabled="stamp_pagination.currentPage == 1" ng-click="searchStamp(stamp_pagination.currentPage-1, stamp_pagination.limit)">
                                <i class="fas fa-backward"></i>
                            </button>
                            <%stamp_pagination.currentPage%>/<% stamp_pagination.lastPage %>
                            <!--PAC_5-1175 ページ表示数が初期化される現象の修正 -->
                            <button ng-disabled="stamp_pagination.currentPage == stamp_pagination.lastPage" ng-click="searchStamp(stamp_pagination.currentPage+1, stamp_pagination.limit)">
                                <i class="fas fa-forward"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">※共通印を追加する場合、下記のリンクより専用申込用紙をダウンロードしていただき、弊社まで郵送でお送りください<br/>
                    ※トライアルのお客様は共通印はお申込み、ご利用いただけませんのでご了承ください。<br/>※サインは実寸での作成となります。サイズ指定はできませんのでご了承ください。</div>
                <div class="form-group">
                    <!--<a href="{{$contract_edition == 3?'':asset('filedownload/dstmp_reg_application.pdf')}}" class="text-black" style="{{$contract_edition == 3?'color: grey':''}}"  download>
                        <i class="fas fa-download"></i> 共通印申請書ダウンロード
                    </a>-->
                    <a href="#" class="text-black" style="{{$contract_edition == 3?'color: grey; pointer-events: none':''}}" tabindex="{{$contract_edition == 3?'-1':'0'}}" ng-click="download()">
                        <i class="fas fa-download"></i> 共通印申請書ダウンロード
                    </a>
                </div>

            </div>
        </div>
        @if($convenient_flg)
        <div class="card mt-3">
            <div class="card-header">便利印</div>
            <div class="card-body">

                <div class="row form-group">
                    <label for="" class="control-label col-md-1 text-right-lg">ジャンル</label>
                    <div class="input-group col-md-3">
                        <!--                        <input type="text" class="form-control" placeholder="名称(部分一致)" ng-keyup="txtNameKeyUp($event,2)"-->
                        <!--                               ng-model="convenient_search_name">-->
                        <select class="form-control select2" ng-model="search_stamp_division">
                            <option></option>
                            <option ng-repeat="division in divisionList" ng-value="division.id">
                                <% division.division_name %>
                            </option>
                        </select>
                    </div>

                    <div class="input-group-append" ng-click="searchConvenientStamp(1, convenient_stamp_pagination.limit)">
                        <span class="input-group-text btn btn-primary"><i class="fas fa-search mr-1"></i> 検索</span>
                    </div>
                    <div class="col-md-7"></div>
                </div>

                <div class="form-group">
                    <span ng-if="!showConvenientSearch">※印面を検索してください※</span>
                    <span ng-if="showConvenientSearch && arrConvenientStamp.length == 0">※該当する印面がありません※</span>
                </div>
                <div class="row my-3" ng-show="arrConvenientStamp.length != 0">
                    <div class="col-sm-6 col-12">
                        表示件数:
                        <select ng-model="convenient_stamp_pagination.limit"
                                ng-change="searchConvenientStamp(1,convenient_stamp_pagination.limit)"
                                ng-options="option for option in option_limit track by option">
                        </select>
                    </div>
                </div>
                <div class="mt-5 mb-5">
                    <div class="stamp-list" ng-if="arrConvenientStamp.length">
                        <div class="stamp-item stamp-item-<% stamp.id %>" ng-repeat="(key, stamp) in arrConvenientStamp">
                            <div class="thumb">
                                <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                                <!--                                <span class="btn btn-warning btn-circle" ng-click="editStamp(key, stamp)">-->
                                <!--                                    <i class="fas fa-pencil-alt"></i>-->
                                <!--                                </span>-->
                            </div>
                            <div class="mt-3" style="width: 80px;"><% stamp.stamp_name%></div>
                        </div>
                    </div>
                </div>
                <div ng-show="arrConvenientStamp.length != 0" class="mb-5">
                    <div class="mt-3"><% convenient_stamp_pagination.total %> 件中 <% convenient_stamp_pagination.from || '0' %> 件から <%convenient_stamp_pagination.to || '0'%> 件までを表示</div>
                    <div class="pagination-center" ng-hide="convenient_stamp_pagination.total <= convenient_stamp_pagination.limit">
                        <div class="pagination">
                            <!--PAC_5-1175 ページ表示数が初期化される現象の修正 -->
                            <button ng-disabled="convenient_stamp_pagination.currentPage == 1" ng-click="searchConvenientStamp(convenient_stamp_pagination.currentPage-1, convenient_stamp_pagination.limit)">
                                <i class="fas fa-backward"></i>
                            </button>
                            <%convenient_stamp_pagination.currentPage%>/<% convenient_stamp_pagination.lastPage %>
                            <!--PAC_5-1175 ページ表示数が初期化される現象の修正 -->
                            <button ng-disabled="convenient_stamp_pagination.currentPage == convenient_stamp_pagination.lastPage" ng-click="searchConvenientStamp(convenient_stamp_pagination.currentPage+1, convenient_stamp_pagination.limit)">
                                <i class="fas fa-forward"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.search_name = "";
                $scope.showSearch = false;
                $scope.keyEdit = false;
                $scope.onlyUnsigned = false;
                $scope.arrStamp = [];
                $scope.stamp_pagination = {};
                $scope.option_limit = [20, 50, 100];
                $scope.group_id = "99";
                $scope.convenient_search_name = '';
                $scope.search_stamp_division = '';
                $scope.showConvenientSearch = false;
                $scope.arrConvenientStamp = [];
                $scope.convenient_stamp_pagination = {};
                $scope.divisionList = {!! json_encode($divisionList) !!};


                $rootScope.$on("openUpdateStamp", function(event, data){
                    $scope.arrStamp[$scope.keyEdit].stamp_name = data.stamp_name;
                    $scope.searchStamp($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit)
                });
                $rootScope.$on("openRemoveStamp", function(event, data){
                    $scope.arrStamp.splice($scope.keyEdit,1)
                    if($scope.arrStamp.length == 0) {
                        $scope.searchStamp(1, $scope.stamp_pagination.limit);
                    } else {
                        $scope.searchStamp($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit)
                    }

                });

                $scope.txtNameKeyUp = function(event){
                    if(event.keyCode == 13){
                        $scope.searchStamp(1, $scope.stamp_pagination.limit);
                    }
                };
                $scope.searchConvenientStamp = function(page, limit){
                    $scope.showConvenientSearch = true;
                    limit = limit || 20;
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_search_convenient_stamp,{name: $scope.convenient_search_name,stamp_division:$scope.search_stamp_division, page: page, limit: limit})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $scope.arrConvenientStamp = [];
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                let paginate = event.data.items;
                                $scope.convenient_stamp_pagination = {currentPage: paginate.current_page,from: paginate.from, to: paginate.to, total: paginate.total, lastPage: paginate.last_page, limit: paginate.per_page};
                                $scope.arrConvenientStamp = paginate.data;
                            }
                        });
                };
                $scope.searchStamp = function(page, limit){
                    $scope.showSearch = true;
                    limit = limit || 20;
                    $rootScope.$emit("showLoading");
                    $http.post(link_search_CompanyStamp,{name: $scope.onlyUnsigned?'名称未設定':$scope.search_name, empty_name:$scope.onlyUnsigned, page: page, limit: limit, group_id:$scope.group_id})
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $scope.arrStamp = [];
                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            let paginate = event.data.items;
                            $scope.stamp_pagination = {currentPage: paginate.current_page,from: paginate.from, to: paginate.to, total: paginate.total, lastPage: paginate.last_page, limit: paginate.per_page};
                            $scope.arrStamp = paginate.data;
                        }
                    });
                };
                $scope.getValue = function(key){
                    $scope.group_id = key;
                }

                $scope.editStamp = function(key, stamp){
                    $scope.keyEdit = key;
                    $rootScope.$emit("openEditStamp",{stamp:stamp});
                };

                $scope.download = function () {
                    $rootScope.$emit("showLoading");
                    $http.post(link_download_CompanyStamp)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $(".message-list").append(showMessages(event.data.message, 'success', 5000));
                                const byteString = Base64.atob(event.data.file_data);
                                const ab = new ArrayBuffer(byteString.length);
                                const ia = new Uint8Array(ab);
                                for (let i = 0; i < byteString.length; i++) {
                                    ia[i] = byteString.charCodeAt(i);
                                }
                                const dataBlob = new Blob([ab]);
                                downloadFile(dataBlob, event.data.fileName);
                            }
                        });
                }
            });

        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush
