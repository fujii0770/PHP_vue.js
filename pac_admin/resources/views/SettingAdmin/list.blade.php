<div ng-controller="ListSettingAdminController">
    @if($allow_create)
        <div class="text-right">
            <div class="btn btn-success" ng-click="addNew()"><span class="fas fa-plus-circle"></span> 登録</div>
        </div>
    @endif

    <form action="" name="adminForm">
        <div class="card mt-3">
            <div class="card-header">管理者一覧</div>
            <div class="card-body">
                <!--PAC_5-350-->
                <!--
                <div class="table-head">
                    <div class="form-group">
                        <div class="row">
                            <label class="col-6 col-md-2 col-xl-1 control-label" >表示件数</label>
                            <div class="col-6 col-md-2">
                                {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                -->
                <span class="clear"></span>
                <div class="col-6">
                    <label class="d-flex"><span style="line-height: 27px">表示件数：</span>
                        <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                            <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                        </select>
                    </label>
                </div>

                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'given_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'department_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('電話番号', 'phone_number', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'state_flg', $orderBy, $orderDir) !!}
                            </th>
                            @can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE)
                                <th scope="col" width="70px"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @if($user->state_flg == 1)
                                <tr class="row-{{ $user['id'] }} row-edit" >
                            @else
                                <tr class="row-{{ $user['id'] }} row-edit row-disabled" >
                            @endif
                                <td class="title" ng-click="editRecord({{ $user['id'] }})">{{ $user->email }}</td>
                                <td ng-click="editRecord({{ $user['id'] }})">{{ $user->family_name . " ".$user->given_name }}</td>
                                <td ng-click="editRecord({{ $user['id'] }})">{{ $user->department_name }}</td>
                                <td ng-click="editRecord({{ $user['id'] }})">{{ $user->phone_number }}</td>
                                <td ng-click="editRecord({{ $user['id'] }})">{{ \App\Http\Utils\AppUtils::ADMIN_STATE_FLG[$user->state_flg]}}</td>
                                @can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE)
                                    @if($allow_update AND $user->role_flg!=\App\Http\Utils\AppUtils::ADMIN_MANAGER_ROLE_FLG AND $user['id'] != Auth::user()->id)
                                        <td class="text-center col-action">
                                            <div class="btn btn-primary btn-sm" ng-click="editPermission({{ $user['id'] }})">権限</div>
                                        </td>
                                    @else
                                        <td class="text-center col-action"></td>
                                    @endif
                                @endcan
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                @include('layouts.table_footer',['data' => $users])
            </div>
            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
            <input type="hidden" name="page" value="{{Request::get('page',1)}}">
        </div>
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListSettingAdminController', function($scope, $rootScope, $http){
                $rootScope.info = {};
                $rootScope.id = 0;
                $rootScope.readonly = false, $rootScope.readonlyState = false, $rootScope.readonlyEmailBtn;
                $rootScope.allPermission = null;
                $rootScope.detailPermisson = null;
                $rootScope.detailPermissonEmail = null;

                $scope.addNew = function(){
                    $rootScope.id = 0;
                    $rootScope.info = {id:0, email:"", given_name:"", family_name:"", phone_number: "", state_flg: 1, sendEmail: true, email_auth_flg: "0", email_auth_dest_flg: "0"};
                    hideMessages();
                    hasChange = false;
                    if(allow_create) $rootScope.readonly = false;
                    if(allow_create) $rootScope.readonlyState = false;
                    else $rootScope.readonly = true;
                    $("#modalDetailItem").modal();
                };

                $scope.editRecord = function(id){
                    $rootScope.id = id;
                    $rootScope.info.id = id;
                    hideMessages();
                    hasChange = false;
                    if(allow_update) $rootScope.readonly = false;
                    else $rootScope.readonly = true;
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax +"/"+ id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $rootScope.info = event.data.info;
                            $rootScope.info.state_flg = ""+$rootScope.info.state_flg;
                            $rootScope.info.email_auth_flg = ""+$rootScope.info.email_auth_flg;
                            $rootScope.info.email_auth_dest_flg = ""+$rootScope.info.email_auth_dest_flg;
                            $rootScope.readonlyState = (currentUserId == $rootScope.info.id);
                            $rootScope.readonlyEmailBtn = $rootScope.info.state_flg != "1";
                        }

                    });
                    $("#modalDetailItem").modal();
                 };

                 $scope.editPermission = function(id){
                    hideMessages();
                    $rootScope.id = id;
                    $rootScope.detailPermisson = null;
                    $rootScope.detailPermissonEmail = null;
                     if($rootScope.allPermission == null){
                        $http.get(link_permission)
                            .then(function(event) {
                                if(event.data.status == false){
                                    $("#modalGrantPermission .message").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $rootScope.allPermission = event.data.item;
                                }
                            });
                     }

                     $rootScope.$emit("showLoading");
                     $http.get(link_user_permission +"/"+ id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalGrantPermission .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $rootScope.detailPermisson = event.data.items;
                                $rootScope.detailPermissonEmail = event.data.email;
                            }
                            $("#modalGrantPermission").modal();
                        });
                 };
            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $('select[name="limit"]').change(function () {
            var value = $(this).val();
            $('input[name="page"]').val('1');
            document.adminForm.submit();
        });


        $("#modalDetailItem").on('hide.bs.modal', function () {
             if(hasChange){
                 location.reload();
             }
        });
    </script>
@endpush
