<div ng-controller="ListLongTermFolderController">
    <form class="form_folder" action="" name="adminForm" method="GET">
        <div class="row">
        <div class="col-lg-3 folder_card {!! Session::get('isNavMenuActive') == 1 ? 'active-folder' : '' !!}">
            <ul class="items tree mt-3" id="sortable_depart" style="overflow-x: auto;white-space: nowrap;height: 100%;">
                <li class="tree-node parent">
                    <div class="name " data-id="0" data-longTermFolder="{{ $company->company_name }}" data-parent="NULL" ng-class="{selected: selectedID == 0}" ng-click="selectRow(0)">
                        <span class="arrow">
                            <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i>
                        </span>
                        <i class="far fa-folder"></i>
                        {{ $company->company_name }}
                    </div>
                    <ul class="items">
                    @foreach ($itemsFolder as $item)
                        @include('LongTerm.longTermFolder.folder_tree_node',['itemFolder' => $item])
                    @endforeach
                    </ul>
                </li>
            </ul>
            <input type="hidden" name="folderId" ng-value="selectedID">
        </div>
        <div class="col-lg-9 main_card" style="margin-top: 15px;">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'text', false,
                    [ 'placeholder' =>'メールアドレス（部分一致）', 'id'=>'email' ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('name','氏名',Request::get('name', ''),'text', false,
                    [ 'placeholder' =>'氏名（部分一致）', 'id'=>'name' ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >部署</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control select2']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >役職</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <input type="hidden" name="departmentChild" value="true">
            <div class="text-right">
                <button class="btn btn-primary mb-1" ng-disabled="selectedID == null"><i class="fas fa-search" ></i> 検索</button>
                @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_CREATE])
                    <button type="button" class="btn btn-success  mb-1" ng-click="addNewFolder()" ng-disabled="selectedID == null"><i class="fas fa-plus-circle" ></i> フォルダ登録</button>
                @endcanany
                @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_UPDATE])
                    <button type="button" class="btn btn-success  mb-1" ng-click="editFolderName()" ng-disabled="!selectedID || selectedID == 0"><i class="fas fa-edit" ></i> フォルダ名称変更</button>
                @endcanany
                @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_DELETE])
                    <button type="button" class="btn btn-danger mb-1" ng-click="deleteFolder()" ng-disabled="!selectedID || selectedID == 0"><i class="fas fa-trash-alt"></i> フォルダ削除</button>
                @endcanany
{{--                @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_UPDATE])--}}
{{--                    <button type="button" class="btn btn-success mb-1" ng-click="AddFolderPermissions()" ng-disabled="!selectedID || selectedID == 0"><i class="fas fa-edit"></i> ユーザ権限変更</button>--}}
{{--                @endcanany--}}
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        </div>

            <div class="message message-list mt-3"></div>
            <div>
                <label class="d-flex"><span style="line-height: 27px">表示件数：</span>
                    <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                        <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                        <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                        <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                    </select>
                </label>
            </div>
        @if($permission_users || $permission_not_users)
            <div class="card mt-3">
                <div class="card-header">権限あり利用者</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-md-6 col-xl-12 text-right">
                                    @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_UPDATE])
                                        <button type="button" class="btn btn-danger mb-1" ng-click="deleteUserFromFolder()" ng-disabled=" !selectedID || selectedID == 0 || isDel == 0"><i class="fas fa-trash-alt"></i>削除</button>
                                    @endcanany
                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input type="checkbox" onClick="checkAll(this.checked)"  ng-click="CheckAllPermissionUser()"/>
                            </th>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">メールアドレス</th>
                            <th scope="col"  class="sort">氏名</th>
                            <th scope="col">部署</th>
                            <th scope="col">役職</th>
                            {{--PAC_5-2098 Start--}}
                            @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                <th scope="col">部署2</th>
                                <th scope="col">役職2</th>
                                <th scope="col">部署3</th>
                                <th scope="col">役職3</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($permission_users as $i => $user)
                                <tr class="row-{{ $user->id }} row-edit" ng-class="{ edit: id == {{ $user->id }} }">
                                    <td class="title">
                                        <input type="checkbox" value="{{ $user->id }}"  class="permission_user_id" onClick="isChecked(this.checked)" ng-click="checkPermissionUser()"/>
                                    </td>
                                    <td class="title" >{{ $user->email }}</td>
                                    <td >{{ $user->family_name . " ".$user->given_name }}</td>
                                    <td >
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->mst_department_id]['text'])
                                            {{ $listDepartmentDetail[$user->mst_department_id]['text'] }}
                                        @endisset
                                    </td>
                                    <td >
                                        @isset($listPosition[$user->mst_position_id])
                                            {{ $listPosition[$user->mst_position_id]['text'] }}
                                        @endisset
                                    </td>
                                    {{--PAC_5-2098 Start--}}
                                    @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                        {{--PAC_5-1599 追加部署と役職 Start--}}
                                        <td >
                                            @isset($listDepartmentDetail[$user->mst_department_id_1]['text'])
                                                {{ $listDepartmentDetail[$user->mst_department_id_1]['text'] }}
                                            @endisset
                                        </td>
                                        <td >
                                            @isset($listPosition[$user->mst_position_id_1])
                                                {{ $listPosition[$user->mst_position_id_1]['text'] }}
                                            @endisset
                                        </td>
                                        <td >
                                            @isset($listDepartmentDetail[$user->mst_department_id_2]['text'])
                                                {{ $listDepartmentDetail[$user->mst_department_id_2]['text'] }}
                                            @endisset
                                        </td>
                                        <td >
                                            @isset($listPosition[$user->mst_position_id_2])
                                                {{ $listPosition[$user->mst_position_id_2]['text'] }}
                                            @endisset
                                        </td>
                                        {{--PAC_5-1599 End--}}
                                    @endif
                                </tr>
                                @endforeach

                        </tbody>
                    </table>
                    @if($permission_users)
                        @include('layouts.table_footer',['data' => $permission_users])
                    @endif
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;" />

            </div>

            <div class="card mt-3">
                <div class="card-header">権限なし利用者</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-md-6 col-xl-12 text-right">
                                    @canany([PermissionUtils::PERMISSION_LONG_TERM_FOLDER_UPDATE])
                                        <button type="button" class="btn btn-success mb-1" ng-click="addUserToFolder()" ng-disabled=" !selectedID || selectedID == 0 || isAdd == 0"><i class="fas fa-edit" ></i>追加</button>
                                    @endcanany
                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input type="checkbox" onClick="checkAll(this.checked)" ng-click="CheckAllNotPermissionUser()"/>
                            </th>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">メールアドレス</th>
                            <th scope="col"  class="sort">氏名</th>
                            <th scope="col">部署</th>
                            <th scope="col">役職</th>
{{--                            PAC_5-2098 Start--}}
                            @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                <th scope="col">部署2</th>
                                <th scope="col">役職2</th>
                                <th scope="col">部署3</th>
                                <th scope="col">役職3</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($permission_not_users as $i => $user)
                                <tr class="row-{{ $user->id }} row-edit" ng-class="{ edit: id == {{ $user->id }} }">
                                    <td class="title">
                                        <input type="checkbox" value="{{ $user->id }}"  class="permission_not_user_id" onClick="isChecked(this.checked)" ng-click="checkNotPermissionUser()"/>
                                    </td>
                                    <td class="title" >{{ $user->email }}</td>
                                    <td >{{ $user->family_name . " ".$user->given_name }}</td>
                                    <td >
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->mst_department_id]['text'])
                                            {{ $listDepartmentDetail[$user->mst_department_id]['text'] }}
                                        @endisset
                                    </td>
                                    <td >
                                        @isset($listPosition[$user->mst_position_id])
                                            {{ $listPosition[$user->mst_position_id]['text'] }}
                                        @endisset
                                    </td>
{{--                                    PAC_5-2098 Start--}}
                                    @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
{{--                                        PAC_5-1599 追加部署と役職 Start--}}
                                        <td >
                                            @isset($listDepartmentDetail[$user->mst_department_id_1]['text'])
                                                {{ $listDepartmentDetail[$user->mst_department_id_1]['text'] }}
                                            @endisset
                                        </td>
                                        <td >
                                            @isset($listPosition[$user->mst_position_id_1])
                                                {{ $listPosition[$user->mst_position_id_1]['text'] }}
                                            @endisset
                                        </td>
                                        <td >
                                            @isset($listDepartmentDetail[$user->mst_department_id_2]['text'])
                                                {{ $listDepartmentDetail[$user->mst_department_id_2]['text'] }}
                                            @endisset
                                        </td>
                                        <td >
                                            @isset($listPosition[$user->mst_position_id_2])
                                                {{ $listPosition[$user->mst_position_id_2]['text'] }}
                                            @endisset
                                        </td>
{{--                                        PAC_5-1599 End--}}
                                    @endif
                                </tr>
                                @endforeach

                        </tbody>
                    </table>
                    @if($permission_not_users)
                        @include('layouts.table_footer2',['data' => $permission_not_users])
                    @endif
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page2" value="{{Request::get('page2',1)}}" />
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;" />

            </div>
        @endif
        </div>
        </div>
    </form>
</div>


@push('scripts')
    <script>
        var hasChangeFolder = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListLongTermFolderController', function($scope, $rootScope, $http){

                $rootScope.search = {email:"", name:"", department: "",position:"",status:"",departmentChild:true};
                $scope.numChecked = 0;
                // PAC_5-2163  利用者情報更新画面でパスワード設定依頼を送るときメールが無効だったらモーダル表示させる
                $scope.checkUsersEmailEnable = 0;
                $scope.checkUsersStatusEnable = 0;
                $scope.checkUsersHasStamp = 0;
                $scope.objUsersStatus = null;
                $scope.folder = {!! json_encode($folder) !!};
                $scope.isCheckAllPermissionUser = false;
                $scope.isCheckAllNotPermissionUser = false;
                $scope.isAdd = false;
                $scope.isDel = false;
                $scope.hasSelectedID = {!! $hasSelectedID !!}
                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                };

                //親ファイルの取得
                $scope.openFolderNode = function (folder_id){
                    let className = $('.' + folder_id)[0].className;
                    $('.' + folder_id)[0].className = className + ' open';
                }
                //選択したフォルダを選択
                $scope.showSelectedFloder = function () {
                    if ($scope.folder['folder_id']) {
                        if ($scope.folder['parent_folder_id']) {
                            let className = $('.parent')[0].className;
                            $('.parent')[0].className = className + ' open';
                            for (let i = 0; i < $scope.folder['parent_folder_id'].length; i++) {
                                $scope.openFolderNode($scope.folder['parent_folder_id'][i]);
                            }
                        }
                        $scope.selectRow($scope.folder['folder_id']);
                    } else {
                        $scope.selectRow(0);
                    }
                }

                $scope.showSelectedFloder();

                $scope.addNewFolder = function(){
                    $rootScope.$emit("openNewFolder",{parent_id:$scope.selectedID});
                };

                $scope.editFolderName = function(){
                    $rootScope.$emit("openEditFolder",{id:$scope.selectedID});
                };

                $scope.AddFolderPermissions = function(){
                    $rootScope.$emit("addFolderPermissions",{id:$scope.selectedID});
                };

                $scope.deleteFolder = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択中のフォルダを削除しますか？',
                            btnDanger:'はい',
                            databack: $scope.selectedID,
                            callDanger: function(selectedID){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + selectedID)
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $('input[name="folderId"]').val(event.data.show_folder_id);
                                            document.adminForm.submit();
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                };

                $("#modalDetailFolderItem").on('hide.bs.modal', function () {
                    if(hasChangeFolder){
                        document.adminForm.submit();
                    }
                });

                $("#modalDetailFolderPermissionsItem").on('hide.bs.modal', function () {
                    if(hasChangeFolder){
                        document.adminForm.submit();
                    }
                });

                $scope.addUserToFolder = function () {
                    let no_permission_count = {{ $permission_not_users->count()}} ;
                    let page2 = {{ Request::get('page2',1) }};
                    let limit = $('select[name="limit"]').val();
                    let select_count = $(".permission_not_user_id:checked").length;
                    if (no_permission_count - select_count == 0){
                        $('input[name="page2"]').val(page2 - 1);
                    }
                    let user_ids = [];
                    for(let i =0; i < $(".permission_not_user_id:checked").length; i++){
                        user_ids.push($(".permission_not_user_id:checked")[i].value);
                    }
                    if (user_ids.length > 0){
                        $http.post(link_ajax_add_users_to_folder_permissions,{user_ids: user_ids,folder_id: $scope.hasSelectedID})
                            .then(function(checkEvent) {
                                $rootScope.$emit("hideLoading");
                                if(checkEvent.data.status == false){
                                    $(".message-list").append(showMessages(checkEvent.data.message, 'danger', 10000));
                                }else {
                                    document.adminForm.submit();
                                    $(".message-list").append(showMessages(checkEvent.data.message, 'success', 10000));
                                }
                                document.adminForm.submit();
                            });
                    }
                }

                $scope.deleteUserFromFolder = function () {
                    let permission_count = {{ $permission_users->count()}} ;
                    let page = {{ Request::get('page',1) }};
                    let limit = $('select[name="limit"]').val();
                    let select_count = $(".permission_user_id:checked").length;
                    if (permission_count - select_count == 0){
                        $('input[name="page"]').val(page - 1);
                    }
                    let user_ids = [];
                    for(let i =0; i < $(".permission_user_id:checked").length; i++){
                        user_ids.push($(".permission_user_id:checked")[i].value);
                    }
                    if (user_ids.length > 0 ){
                        $http.post(link_ajax_delete_users_from_folder_permissions,{user_ids: user_ids,folder_id: $scope.hasSelectedID})
                            .then(function(checkEvent) {
                                $rootScope.$emit("hideLoading");
                                if(checkEvent.data.status == false){
                                    $(".message-list").append(showMessages(checkEvent.data.message, 'danger', 10000));
                                }else {
                                    document.adminForm.submit();
                                    $(".message-list").append(showMessages(checkEvent.data.message, 'success', 10000));
                                }
                            });
                    }
                }
                $scope.CheckAllPermissionUser = function (){
                    $scope.isCheckAllPermissionUser = !$scope.isCheckAllPermissionUser;
                    if ($scope.isCheckAllPermissionUser){
                        let element = $(".permission_user_id");
                        for(let i = 0; i < element.length; i++){
                            element[i].checked = true;
                        }
                        $scope.isDel = true;
                    }else {
                        let element = $(".permission_user_id");
                        for(let i = 0; i < element.length; i++){
                            element[i].checked = false;
                        }
                        $scope.isDel = false;
                    }
                }

                $scope.CheckAllNotPermissionUser = function (){
                    $scope.isCheckAllNotPermissionUser = !$scope.isCheckAllNotPermissionUser;
                    if ($scope.isCheckAllNotPermissionUser){
                        let element = $(".permission_not_user_id");
                        for(let i = 0; i < element.length; i++){
                            element[i].checked = true;
                        }
                        $scope.isAdd = true;
                    }else {
                        let element = $(".permission_not_user_id");
                        for(let i = 0; i < element.length; i++){
                            element[i].checked = false;
                        }
                        $scope.isAdd = true;
                    }
                }

                $scope.checkPermissionUser = function () {
                       if ($(".permission_user_id:checked").length > 0 ){
                           $scope.isDel = true;
                       }else {
                           $scope.isDel = false;
                       }
                }

                $scope.checkNotPermissionUser = function () {
                    if ($(".permission_not_user_id:checked").length > 0 ){
                        $scope.isAdd = true;
                    }else {
                        $scope.isAdd = false;
                    }
                }
            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $(document).ready(function() {
            displayWindowSize();
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });
            $('form[name="adminForm"]').submit(function(e){
                $('input[name="page"]').val('1');
            });
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


        $(window).resize(function (){
            displayWindowSize();
        });

        function displayWindowSize(){
            if (document.body.clientWidth < 975) {
                $('.folder_card').removeClass('folder-height-big').addClass('folder-height-sm');
                $('.main_card').removeClass('card-big').addClass('card-sm');
            }else {
                $('.folder_card').removeClass('folder-height-sm').addClass('folder-height-big');
                $('.main_card').removeClass('card-sm').addClass('card-big');
            }
        }
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

        .folder-height-big{
            position: fixed;
            height: 80%;
            padding-right: 30px;
        }
        .folder-height-sm{
            height: 10%;
            padding-bottom: 20px;
        }
        .active-folder{
            width: 22%;
        }
        .card-big{
            margin-left: 25%;
            padding-right: 20px;
        }
        .card-sm{
            padding-left: 20px;
            padding-right: 20px;
        }
        .name:hover{
            background-color: #e7f4f9;
        }
    </style>
@endpush

