<div ng-controller="ListController" class="list-view">

    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
        @csrf
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-lg-4 control-label">アプリ</label>
                            <div class="col-md-8">
                                <select ng-model="select_app_id" ng-options="app.id as app.appName for app in applist" ng-change="app_search()" class="form-control"></select>
                                <input type="hidden" name="filter_app" value="" class="form-control"  id="filter_app" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-lg-4 control-label">ロール</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect($listrole, 'filter_role', Request::get('filter_role', ''),'',['class'=> 'form-control','ng-blur'=>'role_blur()']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'text', false,
                    [ 'placeholder' =>'メールアドレス(部分一致)' ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('username','氏名',Request::get('username', ''),'text', false,
                    [ 'placeholder' =>'氏名(部分一致)' ]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">部署</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">役職</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9"></div>
                <div class="col-lg-2 text-right text-left-lg padding-top-20">
                        <button name="search_button" ng-click="search_click()" class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                        <input type="hidden" class="action" name="action" value="search" />
                </div>

            </div>
        </div>

        <div class="message message-list mt-3"></div>

        <div class="card mt-3">
            <div class="card-header">利用者一覧</div>
            <div class="card-body">
	            <div class="table-head">
                    @if (count($listapp)>0)
	                <div class="form-group">
	                    <div class="row">
                            <label class="control-label text-right-lg" >　　　ロール</label>
                            <div class="col-lg-3 form-group">
                                {!! \App\Http\Utils\CommonUtils::buildSelect($listrole, 'select_role', Request::get('role2', ''),'',['class'=> 'form-control','ng-model'=>'select_role_id']) !!}
                            </div>
                            <div class="col-lg-5">
                                @canany([PermissionUtils::PERMISSION_APP_ROLE_SETTING_CREATE])
                                <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> ロールの作成</div>
                                @endcanany
                                <button class="btn btn-success  mb-1" ng-click="editRecord(); $event.preventDefault();" ng-disabled="!select_role_id"><i class="fas fa-undo" ></i> ロール内容の更新</button>
	                        </div>
                            <div class="col-lg-2">
                                @canany([PermissionUtils::PERMISSION_APP_ROLE_SETTING_UPDATE])
                                <button class="btn btn-success m-0" ng-click="approval($event)" ng-disabled="!select_role_id || !isTargetUserExist"><i class="far fa-save"></i> 更新</button>
                                @endcanany
	                        </div>
	                    </div>
	                </div>
                    @endif
	            </div>

                <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"
                                       ng-model="allUsersChecked" ng-change="on_all_user_select_change()"/>
                                </th>
                                <th class="title sort" scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ロール', 'role', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'username', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'adminDepartment', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('役職', 'position', $orderBy, $orderDir) !!}
                                </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($listuser)
                            @foreach ($listuser as $i => $item)
                                <tr class="row-{{ $item['app_role_users_id'] }} row-edit" ng-class="{ edit: id == {{ $item['app_role_users_id'] }} }" >
                                        <td class="title">
                                            <input type="checkbox"  value="{{ $item['app_role_users_id'] }}"
                                                   ng-model="users[{{ $i }}].selected" ng-change="on_user_select_change()"
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                        </td>
                                        <td class="title">{{ $item['app_role_name'] }}</td>
                                        <td class="title">{{ $item['email'] }}</td>
                                        <td class="title">{{ $item['user_name'] }}</td>
                                        <td class="title">{{ $item['department_name'] }}</td>
                                        <td class="title">{{ $item['position_name'] }}</td>
                                        <td ng-show="false" class="title">{{ $item['app_role_users_id'] }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                @if ($listuser)
                    @include('layouts.table_footer',['data' => $listuser])
                @endif
            </div>
        </div>
        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
        <input type="hidden" name="page" value="1">
        <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){

            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                document.getElementById("filter_app").value = $scope.select_app_id;

                $scope.select_app_id = {!! $filter_app !!};
                document.getElementById("filter_app").value = {!! $filter_app !!}; //検索ボタン押下時にもappidを認識させるため

                $scope.applist = {!! json_encode($listapp) !!};
                //applistのデータ構造は以下の通り
                // $scope.applist = [
                //                 {id:1,appName:"けいじばん"},
                //                 {id:2,appName:"かれんだー"}
                //                 ];

                // ユーザー一覧
                $scope.allUsersChecked = false;
                var users = {!! json_encode($listuser) !!};
                users = users.data;
                $scope.users = [];
                for(var i = 0; i < users.length; i++){
                    $scope.users.push({
                        selected: false
                    });
                }

                //一括更新ボタン
                $scope.approval = function(event){
                    event.preventDefault();
                    var cids = [];
                    var cidsoff = [];

                    var hit = false;
                    //全てのチェックボックスを探索
                    for(var i =0; i < $(".cid").length; i++){
                      var hit = false;
                      //チェックされたチェックボックスを探索
                      for(var i2 =0; i2 < $(".cid:checked").length; i2++){
                        //一致していたら1更新ルート
                        if($(".cid")[i].value == $(".cid:checked")[i2].value){
                          cids.push($(".cid")[i].value);
                          hit = true;
                          break;
                        }
                      }
                        //不一致の場合0更新ルート
                        if (!hit){
                        cidsoff.push($(".cid")[i].value);
                      }
                    }

                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'選択されたユーザーのロールを変更します。',
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            //select_role_idには選択されたロールidが格納されている。cidsは選択されたチェックボックスの配列
                            $http.put(link_ajax_update, { select_role_id:$scope.select_role_id,cids: cids,appid: $scope.select_app_id})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");　
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    location.reload();
                                }
                            });
                        }
                    });
                };

                $scope.addNew = function(){
                    const roleList = {!! json_encode($roleList) !!}
                    let defaultRole = null
                    for(let key in roleList){
                        if(roleList[key].isDefault){
                            defaultRole = roleList[key]
                            break
                        }
                    }
                    if(!defaultRole){
                        console.error('基本ロールが見つかりません')
                        return
                    }

                    //基本ロールIDを渡す
                    $rootScope.$emit("openDetail",{id:defaultRole.id, isNew:1, appId: $scope.select_app_id});
                };

                $scope.editRecord = function(){
                    var num = adminForm.select_role.selectedIndex;
                    var selected_value = adminForm.select_role.options[num].value;
                    //選択されたロールIDを渡す
                    $rootScope.$emit("openDetail",{id:selected_value,isNew:0, appId: $scope.select_app_id});
                 };

                $scope.role_blur = function(){
                    //下記、動作していない
                    let buttonElement =document.getElementById("search_button") ;
                    buttonElement.disabled = false;
                };
                $scope.search_click = function(){
                    document.getElementById("filter_app").value = $scope.select_app_id;
                    document.adminForm.submit();
                };
                //アプリセレクト時
                $scope.app_search = function(){
                    document.getElementById("filter_app").value = $scope.select_app_id;
                    document.adminForm.submit();
                };

                $scope.isTargetUserExist = false;
                $scope.on_all_user_select_change = function(){
                    $scope.isTargetUserExist = $scope.allUsersChecked;
                    for(var i = 0; i < $scope.users.length; i++){
                        $scope.users[i].selected = $scope.allUsersChecked;
                    }
                    $scope.on_user_select_change();
                };
                $scope.on_user_select_change = function(){
                    $scope.isTargetUserExist = false;
                    for(var i = 0; i < $scope.users.length; i++){
                        if($scope.users[i].selected){
                            $scope.isTargetUserExist = true;
                            break;
                        }
                    }
                };
            });
        }
        $("#modalDetailItem").on('hide.bs.modal', function () {
             if(hasChange){
                location.reload();
             }
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
</style>
@endpush
