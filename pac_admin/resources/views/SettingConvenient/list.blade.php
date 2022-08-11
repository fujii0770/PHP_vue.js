<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET" onsubmit="return false;">

        <div class="message message-list mt-3"></div>
        <div class="card mt-3">
            <div class="card-header">便利印</div>
            <div class="card-body">
                <div class="row form-group">
                    <label for="" class="control-label col-md-1 text-right-lg">ジャンル</label>
                    <div class="input-group col-md-3">
                        <select class="form-control select2" ng-model="search_stamp_division">
                            <option></option>
                            <option ng-repeat="division in divisionList" ng-value="division.id">
                                <% division.division_name %>
                            </option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" ng-click="searchStamp(1, stamp_pagination.limit)">
                            <i class="fas fa-search" ></i> 検索
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalDragStamps">
                            <i class="fas fa-plus-circle"></i> 登録
                        </button>
                    </div>

                    <div class="col-md-7"></div>
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
                            <div class="mt-3" style="width: 80px;"><% stamp.stamp_name %></div>
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


            </div>
        </div>
    </form>
    <form class="form_drag_stamps" action="" method="" onsubmit="return false;">
        <div class="modal modal-drag-stamps" id="modalDragStamps" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">便利印登録</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div>下部領域に便利印ファイルをドラッグ＆ドロップしてください。</div>
                        <div id="stamp-files-area"
                             class="mt-3" style="height:100px;background-color:gainsboro;"></div>


                        <div ng-if="selectedStamps.length" class="mt-3">
                            <div>アップロードファイル</div>

                            <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                                <thead>
                                <tr>
                                    <th class="title" scope="col"  data-tablesaw-priority="persist">No.</th>
                                    <th scope="col" style="width: 200px;text-align: center">アップロードファイル名</th>
                                    <th scope="col" style="text-align: center">ジャンル</th>
                                    <th scope="col" style="text-align: center">名称</th>
                                    <th scope="col" style="width: 70px;text-align: center">削除</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="row- row-edit"
                                    ng-repeat="(indexStamp, stamp) in selectedStamps" >
                                    <td><% indexStamp + 1 %></td>
                                    <td><% stamp.filename %></td>
                                    <td>
                                        <select class="form-control select2" ng-model="stamp.stamp_division" required="1" >
                                            <option></option>
                                            <option ng-repeat="division in divisionList" ng-value="division.id">
                                                <% division.division_name %>
                                            </option>
                                        </select>

                                    </td>
                                    <td><input ng-model="stamp.stamp_name" class="form-control" required></td>

                                    <td style="text-align: center"><div class="btn btn-default btn-sm" ng-click="removeSelectedStamp(indexStamp)">削除</div></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" ng-click="uploadStamps()">
                            <i class="far fa-save"></i> アップロード
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <div class="modal modal-drag-stamps-result" id="modalDragStampsResult" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">便利印登録</h4>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div>
                        <div class="form-group" style="line-height: 30px; border: 1px solid #ddd;">
                            <div class="row">
                                <div class="col-md-3 text-right" style="border-right: 1px solid #ddd; " >
                                    <div style="background: #f5f5f5; ">処理結果</div>
                                </div>
                                <div class="col-md-9"
                                ><% selectedStamps.length %>件の便利印が登録されました。</div>
                            </div>
                        </div>
                        <div class="mt-3">登録された便利印</div>
                        <div class="stamp-list">
                            <div class="stamp-item" ng-repeat="(indexStamp, stamp) in selectedStamps">
                                <div class="thumb">
                                    <span class="thumb-img">
                                        <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                                    </span>
                                </div>
                                <div class="stamp-label" style="margin-top: 15px; width: 80px;"><% stamp.stamp_name?stamp.stamp_name:'名称未設定' %></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" ng-click="closeResultUploadStamp()">
                        <i class="fas fa-times-circle"></i> 閉じる
                    </button>
                </div>

            </div>
        </div>
    </div>


</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.search_name = "";
                $scope.search_stamp_division = "";
                $scope.showSearch = false;
                $scope.keyEdit = false;
                $scope.arrStamp = [];
                $scope.selectedStamps = [];
                $scope.stamp_pagination = {};
                $scope.option_limit = [20, 50, 100];
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

                $scope.searchStamp = function(page, limit){
                    $scope.showSearch = true;
                    limit = limit || 20;
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_search_stamp,{name: $scope.search_name,stamp_division: $scope.search_stamp_division, page: page, limit: limit})
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

                $scope.editStamp = function(key, stamp){
                    $scope.keyEdit = key;
                    $rootScope.$emit("openEditStamp",{stamp:stamp,divisionList:$scope.divisionList});
                };

                $scope.closeResultUploadStamp = function(){
                    $scope.searchStamp($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit)
                    $scope.upload_stamp_status = false;
                    $("#modalDragStampsResult").modal('hide');
                    $scope.selectedStamps = [];

                };

                $scope.stampDrop = function(files){
                    if(files && files.length){
                        $rootScope.$emit("showLoading");
                        for(let i =0; i<files.length; i++){
                            readFileImageAsync(files[i]).then(function(file){
                                $scope.$apply( function(){
                                    $scope.selectedStamps.push({id: 0, stamp_division: '',stamp_date_flg: 0, stamp_image: file.data_image,
                                        width: file.width*85, height: file.height*85, filename: file.name, stamp_name: '',

                                    });
                                });
                            });
                        }
                        $rootScope.$emit("hideLoading");
                        $scope.$apply();
                    }
                };
                $scope.removeSelectedStamp = function(index){
                    $scope.selectedStamps.splice(index,1);
                };

                $scope.uploadStamps = function(){
                    if($(".form_drag_stamps")[0].checkValidity()) {
                        $rootScope.$emit("showLoading");
                        $http.post(link_ajax_upload_stamp, {items: $scope.selectedStamps})
                            .then(function(event){
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $("#modalDragStamps .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $scope.upload_stamp_status = true;
                                    $("#modalDragStamps").modal('hide');
                                    $("#modalDragStampsResult").modal();
                                }

                            });
                    }

                };
            });

            document.getElementById('stamp-files-area').addEventListener('dragover', function(ev){
                ev.preventDefault();
                ev.stopPropagation();
            }, false)

            document.getElementById('stamp-files-area').addEventListener('drop', function(ev){
                ev.preventDefault();
                ev.stopPropagation();
                angular.element(this).scope().stampDrop(ev.dataTransfer.files)
            }, false)

        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush
