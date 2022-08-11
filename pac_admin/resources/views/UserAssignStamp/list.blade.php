<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
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
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >部署</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control  select2']) !!}
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
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >状態</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STATE_USER_LABEL , 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4"></div>
                <div class="col-lg-2"></div>
            </div>
            @if(empty($users))
                        <input type="hidden" name="limit" value="20" />
            @endif
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        @if($users)
            <div class="card mt-3">
                <div class="card-header">利用者一覧</div>
                <div class="card-body">
                    <!--PAC_5-350-->
                    <!--
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数</label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                    {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                                </div>
                                <div class="col-12 col-md-6 col-xl-10 mb-3 text-right"></div>
                            </div>
                        </div>

                    </div>
                    -->
                    <span class="clear"></span>
                    <label class="col-lg-6 col-md-4 "  style="float:left" >
                            <span style="line-height: 27px">表示件数：</span>
                            <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                    <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                    <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                    <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                            </select>
                    </label>


                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'given_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col">部署</th>
                                <th scope="col">役職</th>
                                {{--PAC_5-2098 Start--}}
                                @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                {{--PAC_5-1599 追加部署と役職 Start--}}
                                <th scope="col">部署2</th>
                                <th scope="col">役職2</th>
                                <th scope="col">部署3</th>
                                <th scope="col">役職3</th>
                                {{--PAC_5-1599 End--}}
                                {{--PAC_5-2098 End--}}
                                @endif
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('共通印', 'stampCommon', $orderBy, $orderDir) !!}
                                </th>
                                @if(isset($company->convenient_flg) && $company->convenient_flg == 1)
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('便利印', 'stampConvenient', $orderBy, $orderDir) !!}
                                </th>
                                @endif
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'state_flg', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ﾊﾟｽﾜｰﾄﾞ', 'password', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $i => $user)
                                <tr class="row-{{ $user['id'] }} row-edit" ng-class="{ edit: id == {{ $user['id'] }} }">
                                    <td class="title" ng-click="assignStamp({{ $user['id'] }})">{{ $user->email }}</td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">{{ $user->family_name . " ".$user->given_name }}</td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->info->mst_department_id])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id])
                                            {{ $listPosition[$user->info->mst_position_id]['text'] }}
                                        @endisset
                                    </td>
                                    {{--PAC_5-2098 Start--}}
                                    @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                    {{--PAC_5-1599 追加部署と役職 Start--}}
                                    <td ng-click="assignStamp({{ $user['id'] }})">
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->info->mst_department_id_1])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id_1]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id_1])
                                            {{ $listPosition[$user->info->mst_position_id_1]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->info->mst_department_id_2])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id_2]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id_2])
                                            {{ $listPosition[$user->info->mst_position_id_2]['text'] }}
                                        @endisset
                                    </td>
                                    @endif
                                    {{--PAC_5-1599 End--}}
                                    <td ng-click="assignStamp({{ $user['id'] }})">{{ $user->stampCommon }}</td>
                                    @if(isset($company->convenient_flg) && $company->convenient_flg == 1)
                                    <td ng-click="assignStamp({{ $user['id'] }})">{{ $user->stampConvenient }}</td>
                                    @endif
                                    <td ng-click="assignStamp({{ $user['id'] }})">{{ \App\Http\Utils\AppUtils::STATE_USER[$user->state_flg] }}</td>
                                    <td ng-click="assignStamp({{ $user['id'] }})">{{ $user->password==""?"未設定":"設定済" }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $users])
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;" />

            </div>
        @endif
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){

                $rootScope.search = {email:"", name:"", department: "",position:"",status:""};
                $scope.numChecked = 0;

                $scope.addNew = function(){
                    $rootScope.$emit("openNewUser");
                };

                $scope.assignStamp = function(id){
                    $rootScope.$emit("openAssignStamp",{id:id});
                };

            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $(document).ready(function() {
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

        $("#modalDetailItem").on('hide.bs.modal', function () {
            $(".adminlist tr.edit").removeClass('edit');
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