<div ng-controller="ListSettingAdminStampGroupController">
    <form action="" name="adminForm">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'text', false,
                    [ 'placeholder' =>'メールアドレス（部分一致）', 'id'=>'email' ]) !!}
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >グループ</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect($list_group, 'group', Request::get('group', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            {{-- PAC_5-2045 Start --}}
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('department','部署',Request::get('department', ''),'text', false,
                    [ 'placeholder' =>'部署（検索条件）', 'id'=>'department' ]) !!}
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >状態</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STATE_USER_LABEL , 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            {{-- PAC_5-2045 End --}}
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                @canany([PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_GROUP_SETTING_UPDATE])
                    <div class="btn btn-success mb-1" ng-click="update()"><span class="far fa-save"></span> 更新</div>
                @endcanany
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        <div class="card mt-3">
            <div class="card-header">共通印グループ管理者割当</div>
            <div class="card-body">
{{--                <div class="table-head">--}}
{{--                    <div class="form-group">--}}
{{--                        <div class="row">--}}
{{--                            <label class="col-6 col-md-2 col-xl-1 control-label" >表示件数</label>--}}
{{--                            <div class="col-6 col-md-2">--}}
{{--                                {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
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
                            <th class="title sort" scope="col" data-tablesaw-priority="persist"> {{--data-tablesaw-priority="persist"--}}
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'given_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'department_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('グループ名', 'group_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'state_flg', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort text-center">
                                割当
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @if($user->state_flg == 1)
                                <tr class="row-{{ $user['id'] }} row-edit" >
                            @else
                                <tr class="row-{{ $user['id'] }} row-edit row-disabled" >
                            @endif
                                <td class="title email" name="email[]">{{ $user->email }}</td>
                                <td>{{ $user->family_name . " ".$user->given_name }}</td>
                                <td>{{ $user->department_name }}</td>
                                <td>{{ $user->group_name }}</td>
                                <td>{{ \App\Http\Utils\AppUtils::ADMIN_STATE_FLG[$user->state_flg]}}</td>
                                <td class="text-center">
                                    <input type="checkbox" ng-checked="{{ $user->state }}"
                                           class="cid" name="cid[]" onClick="isChecked(this.checked)"
                                           user_id="{{ $user->mst_admin_id }}" group_id="{{ $user->group_id }}"/>
                                </td>
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
            appPacAdmin.controller('ListSettingAdminStampGroupController', function($scope, $rootScope, $http){
                $rootScope.info = {};
                $rootScope.id = 0;
                $rootScope.readonly = false, $rootScope.readonlyState = false, $rootScope.readonlyEmailBtn;

                $scope.update = function(event){

                    var update_datas = [];
                    for(var i =0; i < $(".cid").length; i++){
                        var update_data = [];
                        if($(".cid")[i].checked){
                            update_data['checked']=true;
                            update_data.push({user_id:$(".cid")[i].getAttribute('user_id'),group_id:$(".cid")[i].getAttribute('group_id'),checked:1})
                        }else{
                            update_data.push({user_id:$(".cid")[i].getAttribute('user_id'),group_id:$(".cid")[i].getAttribute('group_id'),checked:0})
                        }
                        update_datas.push(update_data)
                    }

                    $http.post(link_updates, { update_datas: update_datas})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                location.reload();
                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                            }
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
    </script>
@endpush
