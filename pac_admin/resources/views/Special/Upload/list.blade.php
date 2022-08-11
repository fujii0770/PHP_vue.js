@section('content')

    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="GET">
            @csrf
            <div class="form-search form-horizontal">
                <div class="row" >
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('circular_name','文書名	',Request::get('circular_name', ''),'text', false,
                        [ 'col' => 3 ]) !!}
                    </div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('display_name','表示名	',Request::get('display_name', ''),'text', false,
                        [ 'col' => 3 ]) !!}
                    </div>
                    <div class="col-lg-2"></div>
                    <div class="col-lg-1"></div>
                </div>
                <div class="row" >
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <label for="create_from" class="col-md-3 control-label">登録日時</label>
                                <div class="col-md-4">
                                    <input type="date" name="create_from" value="{{ Request::get('create_from', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="create_from">
                                </div>
                                <label for="create_to" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="create_to" value="{{ Request::get('create_to', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="create_to">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="row">
                                <label for="open_period_from" class="col-md-3 control-label">公開期限</label>
                                <div class="col-md-4">
                                    <input type="date" name="open_period_from" value="{{ Request::get('open_period_from', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="open_period_from">
                                </div>
                                <label for="open_period_to" class="col-md-1 control-label">~</label>
                                <div class="col-md-4">
                                    <input type="date" name="open_period_to" value="{{ Request::get('open_period_to', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="open_period_to">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                    </div>
                    <div class="col-lg-1"></div>
                </div>
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="row">
                            <label class="col-md-3 control-label" >状態</label>
                            <div class="col-md-4">
                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::TEMPLATE_CODE , 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-2"></div>
                    <div class="col-lg-1"></div>
                </div>
                <div class="row" style="margin-top: 20px">
                    <div class="col-lg-1"></div>
                    <div class="upload-wrapper mr-1 mb-1 col-lg-2">
                        <div class="vx-col w-full upload-box" id="dropZone">
                            <label class="wrapper" for="uploadFile">
                                <input type="file" ref="uploadFile" style="opacity: 0;width: 2px"
                                       accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                       id="uploadFile" onChange="angular.element(this).scope().onUploadFile(event)"/>
                                <label for="uploadFile" id="filesName"></label>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-4 text-left">
                        @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_CREATE])
                            <button type="button" class="btn btn-success mb-1" ng-click="showTemplate()"><i class="fas fa-plus-circle"></i> 新規登録</button>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_DELETE])
                            <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="deleteTemplate()"><i class="fas fa-trash-alt"></i> 削除</button>
                        @endcanany
                    </div>
                    <div class="col-lg-2"></div>
                    <div class="col-lg-2">
                        <button class="btn btn-primary mb-1" type="submit" ng-click="search()"><i class="fas fa-search" ></i> 検索</button>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="message-list mt-3"></div>
                    <div class="card mt-3">
                        <div class="card-header">文書一覧</div>
                            <div class="card-body">
                                <div class="table-head">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-lg-6 col-md-4 "  style="float:left" >
                                                    <span style="line-height: 27px">表示件数：</span>
                                                    <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                        <option {{Request::get('limit') == '10' ? 'selected': ''}} value="10">10</option>
                                                        <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                        <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                    </select>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <span class="clear"></span>

                                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                                    <thead>
                                        <tr>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" />
                                             </th>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('文書名', 'template_file_name', $orderBy, $orderDir) !!}
                                            </th>
                                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('表示名', 'tc.display_name', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('登録日時', 'tc.create_at', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('公開期限', 'tc.open_period', $orderBy, $orderDir) !!}
                                            </th>
                                            <th scope="col" class="sort">
                                                {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'is_enable', $orderBy, $orderDir) !!}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itemsUpload as $i => $item)
                                            <tr class="">
                                                <td class="title">
                                                    <input type="checkbox" value="{{ $item['template_file_id'] }}" ng-click="toogleCheck({{ $item['template_file_id'] }})"
                                                        name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                                </td>
                                                <td class="title" style="width: 39%" ng-click="editRecord({{ $item['template_file_id'] }})">{{ $item['template_file_name'] }}</td>
                                                <td style="width: 39%" ng-click="editRecord({{ $item['template_file_id'] }})">{{ $item['display_name'] }}</td>
                                                <td style="width: 8%" ng-click="editRecord({{ $item['template_file_id'] }})">{{ date("Y/m/d", strtotime($item['create_at'])) }}</td>
                                                <td style="width: 8%" ng-click="editRecord({{ $item['template_file_id'] }})">{{ $item['open_period'] == "" ? '無期限' : date("Y/m/d", strtotime($item['open_period'])) }}</td>
                                                <td style="width: 5%" ng-click="editRecord({{ $item['template_file_id'] }})">{{ \App\Http\Utils\AppUtils::TEMPLATE_CODE[$item['is_enable']] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @include('layouts.table_footer',['data' => $itemsUpload])
                            </div>
                            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
                        </div>
                    </div>
                <input type="hidden" name="action" value="search">

            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-11 form-group">
                    <div class="row">
                        <div class="upload-wrapper mr-2 mb-2" style="width: 83.33333%!important;">
                            <div class="vx-col w-full upload-box" style="height: 40px" id="dropZoneLarge">
                                <label class="wrapper" for="uploadFile">
                                    <label for="uploadFile" id="filesNameLarge"></label>
                                </label>
                            </div>
                        </div>
                        @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_CREATE])
                            <div class="btn btn-success  mb-1" ng-click="showTemplate()" style="height: 35px"> 新規登録</div>
                        @endcanany
                    </div>
                </div>
            </div>
            </form>

        <form class="uploadFile1" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalUpload" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">文書登録</h4>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message"></div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">文書名</label>
                                <div class="col-md-8 col-sm-9 col-12">
                                    <input type="text" class="form-control" disabled="disabled" id="fileName" ng-model="uploadUpt.fileName">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">表示名</label>
                                <div class="col-md-8 col-sm-9 col-12">
                                    <input type="text" class="form-control" id="displayName" ng-model="uploadUpt.displayName" maxlength="50">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">登録日</label>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <input type="date" class="form-control" disabled="disabled" id="uploadDate" placeholder="yyyy/MM/dd">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">公開期限</label>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <input type="date" class="form-control" id="koukaiDate" ng-model="uploadUpt.koukaiDate" placeholder="yyyy/MM/dd">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">状態</label>
                                <div class="col-md-8 col-sm-8 col-12">
                                    <label class="control-label">有効にする</label>
                                    <input type="checkbox" ng-model="uploadUpt.state" id="receive_flg" ng-true-value="1" ng-false-value="0"/>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">文書受取後の回覧宛先</label>
                                <div>
                                    <table class="tablesaw-list tablesaw table-bordered adminlist mt-1">
                                        <thead>
                                        <tr>
                                            <th class="title" style="text-align: center;width: 20%">
                                                回覧順
                                            </th>
                                            <th class="title" style="text-align: center;width: 80%">
                                                メールアドレス
                                            </th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr ng-repeat="(id, receiveCircularUser) in uploadUpt.receiveCircularUsers" >
                                            <td class="title" style="text-align: center" value="order">
                                                <input type="text" disabled="disabled" class="form-control" ng-model="receiveCircularUser.order" id="order" />
                                            </td>
                                            <td class="title" style="text-align: center">
                                                <input type="text" class="form-control" ng-model="receiveCircularUser.email" id="email" style="display: inline-block;"
                                                       placeholder="xxx@yyy.com" maxlength="255" ng-click="openDepartmentTree(receiveCircularUser)"/>
                                            </td>
                                            <td><button class="fas red fa-minus-circle" ng-click="userDel(receiveCircularUser.order)"></button></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: center"><button class="fas blue-grey fa-plus-circle" ng-click="userAdd()"></button></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <label style="text-align:right;width:95%">このファイルをテンプレート文書として登録します。</label>
                            </div>
                            <div class="modal-footer">
                                @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_CREATE])
                                    <button type="button" class="btn btn-success" ng-click="upload()">登録</button>
                                @endcanany
                                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form class="templateUpdate" action="" method="" onsubmit="return false;">
            <div class="modal modal-add-stamp mt-3 modal-child" id="modalUploadUpt" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">文書設定</h4>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body form-horizontal">
                            <div class="message"></div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">文書名</label>
                                <div class="col-md-8 col-sm-9 col-12">
                                    <input type="text" class="form-control" disabled="disabled" id="fileName" ng-model="uploadUpt.fileName">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">表示名</label>
                                <div class="col-md-8 col-sm-9 col-12">
                                    <input type="text" class="form-control" id="displayName" ng-model="uploadUpt.displayName" maxlength="50">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">公開期限</label>
                                <div class="col-md-3 col-sm-6 col-12">
                                    <input type="date" class="form-control" id="koukaiDate" ng-model="uploadUpt.koukaiDate" placeholder="yyyy/MM/dd">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">状態</label>
                                <div class="col-md-8 col-sm-8 col-12">
                                    <label class="control-label">有効にする</label>
                                    <input type="checkbox" ng-model="uploadUpt.state" id="receive_flg" ng-true-value="1" ng-false-value="0"/>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label for="name" class="col-sm-2 control-label">文書受取後の回覧宛先</label>
                                <div >
                                    <table class="tablesaw-list tablesaw table-bordered adminlist mt-1">
                                        <thead>
                                        <tr>
                                            <th class="title" style="text-align: center;width: 20%">
                                                回覧順
                                            </th>
                                            <th class="title" style="text-align: center;width: 80%">
                                                メールアドレス
                                            </th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr ng-repeat="(id, receiveCircularUser) in uploadUpt.receiveCircularUsers" >
                                            <td class="title" style="text-align: center" value="order">
                                                <input type="text" disabled="disabled" class="form-control" ng-model="receiveCircularUser.order" id="order" />
                                            </td>
                                            <td class="title" style="text-align: center">
                                                <input type="text" class="form-control" ng-model="receiveCircularUser.email" id="email" style="display: inline-block;"
                                                       placeholder="xxx@yyy.com" maxlength="255" ng-click="openDepartmentTree(receiveCircularUser)"/>
                                            </td>
                                            <td><button class="fas red fa-minus-circle" ng-click="userDel(receiveCircularUser.order)"></button></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: center"><button class="fas blue-grey fa-plus-circle" ng-click="userAdd()"></button></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <label style="text-align:right;width:95%">このファイルをテンプレート文書として登録します。</label>
                            </div>
                            <div class="modal-footer">
                                @canany([PermissionUtils::PERMISSION_SPECIAL_SITE_UPLOAD_UPDATE])
                                    <button type="button" class="btn btn-success" ng-click="templateUpdate()">更新</button>
                                @endcanany
                                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="modal modal-drag-stamps-result" id="modalTreeDepartmentResult" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">回覧宛先</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <input name="select_email" class="select-email">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script type="text/javascript" src="./zTree/zTree_v3/js/jquery.ztree.core.js"></script>
    <script type="text/javascript" src="./zTree/zTree_v3/js/jquery.ztree.exhide.js"></script>
    <script type="text/javascript" src="./zTree/jquery.select.zTree.v1.5.js"></script>
    <link rel="stylesheet" href="./zTree/zTree_v3/css/zTreeStyle/zTreeStyle.css" type="text/css">
    <link rel="stylesheet" href="./zTree/jquery.select.zTree.v1.5.css" type="text/css">
    <script>
        function getAddMonthBefore(dt,add = 0){
            var month = dt.getMonth();
            dt.setMonth(month-add);

            var y = dt.getFullYear();
            var m = ('00' + (dt.getMonth()+1)).slice(-2);
            var d = ('00' + dt.getDate()).slice(-2);
            return (y + '-' + m + '-' + d);
        }

        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.receiveStamp = [];
                $scope.saml_metadata_file = "";
                $scope.uploadUpt = {id: "", fileName: "", displayName: "", koukaiDate: "", state: ""};
                $scope.items = {!! json_encode($itemsUpload) !!};
                $scope.tempFiles = [];
                $scope.filesName = "";
                $scope.receiveCircularUsers = "";
                var company_users = {!! json_encode($company_users) !!};
                // var company_users = function (company_users_with_department){
                //     var users = [];
                //     for
                //     company_users_with_department.filter((item) => {
                //         return item.email != undefined;
                //     });
                // };
                $scope.addressOrder = 0;
                if({{ count($itemsUpload) }}){
                    $scope.cids = {!! json_encode($itemsUpload->pluck('template_file_id')) !!};
                } else{
                    // $("#request_fromdate").val(getAddMonthBefore(new Date(),1));
                    // $("#request_todate").val(getAddMonthBefore(new Date()));
                }

                $('input[name="select_email"]')
                    .selectZTree({
                        data: company_users,
                        width: 250,
                        showSearch: true,
                        selectLevel: -1,
                        initValue: false,
                        closeOnSelect: true,
                        placeholder: "",
                        onReady: function (ele) {
                        },
                        onOpen: function (ele) {
                        },
                        onClose: function (ele) {},
                        onSelected: function (ele, val) {
                            if ($scope.uploadUpt.receiveCircularUsers){
                                $scope.uploadUpt.receiveCircularUsers.forEach(item=>{
                                    if(item.order == $scope.addressOrder){
                                        item.email = val.email;
                                    }
                                });
                                $scope.$apply();
                            }
                            $("#modalTreeDepartmentResult").modal('hide');
                            $('.select-value').val('')
                        }
                    })
                    .on("change", function (e, data) {
                    });

                document.adminForm.submit=function () {
                    $rootScope.$emit("showLoading");
                }
                $scope.search = function () {
                    document.adminForm.submit();
                };

                 $scope.toogleCheckAll = function(){
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                 };

                 $scope.toogleCheck = function(id){
                     // item.checked = true;
                     var idx = $scope.selected.indexOf(id);
                     if (idx > -1) {
                         $scope.selected.splice(idx, 1);
                     }else{
                         $scope.selected.push(id);
                     }
                 };

                $scope.onUploadFile = async function($event) {
                    const files = Array.from($event.target.files);
                    $scope.tempFiles = files;

                    if(!$scope.tempFiles || !$scope.tempFiles.length)
                    {
                        $scope.filesName = 'ファイル選択';
                        $scope.saml_metadata_file = null;
                    }else{
                        $scope.filesName = $scope.tempFiles[0].name;
                        $scope.saml_metadata_file = $scope.filesName;
                    }
                    $("#filesName").text($scope.filesName);
                    $("#filesNameLarge").text($scope.filesName);
                    $('#saml_metadata').val($scope.filesName);
                }

                $scope.showTemplate = function(){
                    if(!$scope.saml_metadata_file || $scope.saml_metadata_file == null) {
                        $(".message-list").append(showMessages(["ファイルを選択してください。"], 'danger', 10000));
                        return ;
                    }
                    const file = $scope.saml_metadata_file;
                    $scope.uploadUpt = {
                        id: "",
                        fileName: file,
                        displayName: file,
                        koukaiDate: "",
                        state: 0,
                        receiveCircularUsers:[
                            {
                                order: 1,
                                email: ''
                            }
                        ]
                    };
                    const koukai = "{{date('Y-m-d')}}";
                    $("#uploadDate").val(koukai);
                    $("#modalUpload").modal();
                };

                $scope.userDel = function(order){
                    let receiveUsers = [];
                    $scope.uploadUpt.receiveCircularUsers.forEach(item=>{
                        if(item.order < order){
                            receiveUsers.push(item);
                        }else if(item.order > order){
                            let itemOrder = item.order - 1;
                            receiveUsers.push({
                               order: itemOrder,
                               email: item.email
                            });
                        }
                    });

                    $scope.uploadUpt.receiveCircularUsers = receiveUsers;
                }

                $scope.userAdd = function(){
                    let order = 0;
                    $scope.uploadUpt.receiveCircularUsers.forEach(item=>{
                        if(item.order > order){
                            order = item.order;
                        }
                    });
                    order++;
                    $scope.uploadUpt.receiveCircularUsers.push({order: order, email: ''});
                }

                $scope.openDepartmentTree = function(user){
                    $scope.addressOrder = user.order;
                    $("#modalTreeDepartmentResult").modal();
                    $('.select-value').click()
                }


                $scope.upload = function(){
                    if(!$scope.saml_metadata_file || $scope.saml_metadata_file == null) {
                        $(".message").append(showMessages(["ファイルを選択してください。"], 'danger', 10000));
                        return ;
                    }
                    if($scope.uploadUpt.displayName == null || $scope.uploadUpt.displayName == ""){
                        $(".message").append(showMessages(["表示名を入力してください。"], 'danger', 10000));
                        return ;
                    }
                    if($scope.uploadUpt.displayName.length > 50 ){
                        $(".message").append(showMessages(["表示名を50文字以下で入力してください。"], 'danger', 10000));
                        return ;
                    }
                    if($scope.uploadUpt.receiveCircularUsers.length == 0 || ($scope.uploadUpt.receiveCircularUsers.length == 1 && $scope.uploadUpt.receiveCircularUsers[0].email == "")){
                        $(".message").append(showMessages(["最低1人回覧宛先を指定してください。"], 'danger', 10000));
                        return ;
                    }
                    $EmailCheckFlg = false;
                    $scope.uploadUpt.receiveCircularUsers.forEach(item => {
                        if (item.email == "") {
                            $EmailCheckFlg = true;
                        } else if (!company_users.find(function (user) {
                            return user.email && user.email == item.email;
                        })) {
                            $EmailCheckFlg = true;
                        }
                    })
                    if($EmailCheckFlg){
                        $(".message").append(showMessages(["社内宛先を指定してください。"], 'danger', 10000));
                        return ;
                    }
                    $rootScope.$emit("showLoading");
                    const fileName = $scope.saml_metadata_file;
                    var fd = new FormData();
                    $kbn = "upload";
                    fd.append('file', $scope.tempFiles[0]);
                    fd.append('fileName', fileName);
                    fd.append('displayName', $scope.uploadUpt.displayName);
                    fd.append('koukaiDate', $("#koukaiDate").val());
                    fd.append('state', $scope.uploadUpt.state);
                    fd.append('kbn', $kbn);
                    fd.append('receiveCircularUsers', JSON.stringify($scope.uploadUpt.receiveCircularUsers));

                    $http.post(link_upload, fd, { headers: { 'Content-Type': undefined }, })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages([event.data.message], 'danger', 10000));
                                // $(".message-list").append(showMessages(event.data.message, 'danger', 20000));
                            }else{
                                location.reload();
                                $(".message-list").append(showMessages(event.data.message, 'success', 20000));
                            }
                        });
                }

                $scope.deleteTemplate = function(){
                    $rootScope.$emit("showLoading");
                    const ids = $scope.selected;

                    $http.post(link_destroy, {ids: ids})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message-list").append(showMessages(event.data.message, 'danger', 20000));
                            }else{
                                location.reload();
                                $(".message-list").append(showMessages(event.data.message, 'success', 20000));
                            }
                        });
                }

                $scope.editRecord = function(id){
                    const convenientStamp =  $scope.items.data;
                    if(Array.isArray(convenientStamp)){
                        convenientStamp.forEach(item=>{
                          if(item.template_file_id == id){
                              var open_period = new Date(item.open_period);
                              $scope.uploadUpt = {
                                  id: item.template_file_id,
                                  fileName: item.template_file_name,
                                  displayName: item.display_name,
                                  koukaiDate: open_period,
                                  state: item.is_enable == -1 ? 1 : item.is_enable,
                                  receiveCircularUsers: item.receive_circular_users
                              };
                              $("#fileName").val(item.template_file_name);
                              $("#koukaiDate").val(open_period);
                          }
                        })
                    }

                    $("#modalUploadUpt").modal();
                };

                $scope.templateUpdate = function($kbn){
                    if($scope.uploadUpt.displayName == null || $scope.uploadUpt.displayName == ""){
                        $(".message").append(showMessages(["表示名を入力してください。"], 'danger', 10000));
                        return ;
                    }
                    if($scope.uploadUpt.receiveCircularUsers.length == 0 || ($scope.uploadUpt.receiveCircularUsers.length == 1 && $scope.uploadUpt.receiveCircularUsers[0].email == "")){
                        $(".message").append(showMessages(["最低1人回覧宛先を指定してください。"], 'danger', 10000));
                        return ;
                    }
                    $EmailCheckFlg = false;
                    $scope.uploadUpt.receiveCircularUsers.forEach(item => {
                        if (item.email == "") {
                            $EmailCheckFlg = true;
                        } else if (!company_users.find(function (user) {
                            return user.email && user.email == item.email;
                        })) {
                            $EmailCheckFlg = true;
                        }
                    })
                    if($EmailCheckFlg){
                        $(".message").append(showMessages(["社内宛先を指定してください。"], 'danger', 10000));
                        return ;
                    }
                    $rootScope.$emit("showLoading");
                    $http.put(link_update, {id: $scope.uploadUpt.id, fileName: $scope.uploadUpt.fileName, display_name: $scope.uploadUpt.displayName, template_update_at: $("#koukaiDate").val(), state: $scope.uploadUpt.state, receiveCircularUsers: JSON.stringify($scope.uploadUpt.receiveCircularUsers), kbn: 'update'})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message-list").append(showMessages(event.data.message, 'danger', 20000));
                            }else{
                                location.reload();
                                $(".message-list").append(showMessages(event.data.message, 'success', 20000));
                            }
                        });
                };

                dropZone.ondrop = function($event){
                    const dropZone = document.getElementById('dropZone');
                    dropZone.style.borderColor = '#D1ECFF';
                    $event.stopPropagation();
                    $event.preventDefault();

                    const files = $event.dataTransfer.files;
                    if(files && files.length) $scope.tempFiles = files;
                    if(!$scope.tempFiles || !$scope.tempFiles.length)
                    {
                        $scope.filesName = 'ファイル選択';
                    }else{
                        $scope.filesName = $scope.tempFiles[0].name;
                    }
                    $scope.saml_metadata_file = $scope.filesName;
                    $('#saml_metadata').val($scope.filesName);
                    $("#filesName").text($scope.filesName);
                    $("#filesNameLarge").text($scope.filesName);
                };
                dropZone.ondragleave = function ($event) {
                    const dropZone = document.getElementById('dropZone');
                    dropZone.style.borderColor = '#D1ECFF';
                    $event.stopPropagation();
                    $event.preventDefault();
                };
                dropZone.ondragover = function ($event) {
                    const dropZone = document.getElementById('dropZone');
                    dropZone.style.borderColor = '#55efc4';
                    $event.stopPropagation();
                    $event.preventDefault();
                    $event.dataTransfer.dropEffect = 'copy';
                };

                dropZoneLarge.ondrop = function($event){
                    const dropZone = document.getElementById('dropZoneLarge');
                    dropZone.style.borderColor = '#D1ECFF';
                    $event.stopPropagation();
                    $event.preventDefault();

                    const files = $event.dataTransfer.files;
                    if(files && files.length) $scope.tempFiles = files;
                    if(!$scope.tempFiles || !$scope.tempFiles.length)
                    {
                        $scope.filesName = 'ファイル選択';
                    }else{
                        $scope.filesName = $scope.tempFiles[0].name;
                    }
                    $scope.saml_metadata_file = $scope.filesName;
                    $('#saml_metadata').val($scope.filesName);
                    $("#filesName").text($scope.filesName);
                    $("#filesNameLarge").text($scope.filesName);
                };
                dropZoneLarge.ondragleave = function ($event) {
                    const dropZone = document.getElementById('dropZoneLarge');
                    dropZone.style.borderColor = '#D1ECFF';
                    $event.stopPropagation();
                    $event.preventDefault();
                };
                dropZoneLarge.ondragover = function ($event) {
                    const dropZone = document.getElementById('dropZoneLarge');
                    dropZone.style.borderColor = '#55efc4';
                    $event.stopPropagation();
                    $event.preventDefault();
                    $event.dataTransfer.dropEffect = 'copy';
                };
            });
        }

        $(document).ready(function() {
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });

            $("#filesName").text("ファイル選択");
            $("#filesNameLarge").text("ファイル選択");
            $('.select2').select2({
                placeholder: '',
                allowClear: true,
                "language": {
                    "noResults": function(){
                        return "データがありません";
                    }
                }
            });
        });
    </script>
@endpush

@push('styles_after')
    <style>
        .select2-container .select2-selection{
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{ height: 36px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered{     line-height: 24px; }
        .upload-wrapper {
            height: 50px;
        }
        .w-full {
            width: 100%!important;
        }
        .upload-wrapper .upload-box {
            height: 50px;
            border-radius: 10px;
            border: 3px dashed #d1ecff;
        }
        .upload-wrapper .upload-box label.wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            padding: 10px 20px;
            align-items: center;
        }
        .upload-wrapper .upload-box label[for="uploadFile"],label[for="uploadFileLarge"] {
            cursor: pointer;
        }
    </style>
@endpush
