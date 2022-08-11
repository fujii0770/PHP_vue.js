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
                        <label class="col-md-4 control-label">部署</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control select2']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label">役職</label>
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
                        <label class="col-md-4 control-label"></label>
                        <div class="col-md-8">
                            <input type="month"  name="search_month"  value="{{ Request::get('search_month', now()->format('Y-m')) }}" class="form-control" id="search_month">
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search"></i> 検索</button>
                <input type="hidden" class="action" name="action" value="search"/>
            </div>
        </div>

        {{--        <div class="message message-list mt-3"></div>--}}
        @if($users)
            <div class="card mt-3">
                <div class="card-header">利用者</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <!--PAC_5-350-->
                            <!--
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数</label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                    {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                                </div>
                                -->
                                <!--<div class="col-12 col-md-6 col-xl-10 mb-3 text-right">-->
                                <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                    <label class="d-flex" style="float:left"><span
                                            style="line-height: 27px">表示件数：</span>
                                        <select style="width: 100px" name="limit" aria-controls="dtBasicExample"
                                                class="custom-select custom-select-sm form-control form-control-sm">
                                            <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20
                                            </option>
                                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50
                                            </option>
                                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">
                                                100
                                            </option>
                                        </select>
                                    </label>
                                    <div class="btn btn-warning  mb-1" ng-click="downloadCsv(this)"><i
                                            class="fas fa-download"></i> CSV出力
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>
                    <div class="message message-list mt-3"></div>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5"
                           data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                <input type="checkbox" onClick="checkAll(this.checked)"/>
                            </th>
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col" class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'given_name', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col">部署</th>
                            <th scope="col">役職</th>
                            {{--PAC_5-2098 Start--}}
                            @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                <th scope="col">部署2</th>
                                <th scope="col">役職2</th>
                                <th scope="col">部署3</th>
                                <th scope="col">役職3</th>
                            @endif
                            {{--PAC_5-2098 End--}}
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $i => $user)
                            @if($user->state_flg == 1)
                                <tr class="row-{{ $user['id'] }} row-edit" ng-class="{ edit: id == {{ $user['id'] }} }">
                            @else
                                <tr class="row-{{ $user['id'] }} row-edit row-disabled"
                                    ng-class="{ edit: id == {{ $user['id'] }} }">
                                    @endif
                                    <td class="title">
                                        <input type="checkbox" value="{{ $user['id'] }}" class="cid"
                                               onClick="isChecked(this.checked)"/>
                                    </td>
                                    <td class="title" ng-click="goBook({{ $user['id'] }})">{{ $user->email }}</td>
                                    <td ng-click="goBook({{ $user['id'] }})">{{ $user->family_name . " ".$user->given_name }}</td>
                                    <td ng-click="goBook({{ $user['id'] }})">
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->info->mst_department_id]['text'])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="goBook({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id])
                                            {{ $listPosition[$user->info->mst_position_id]['text'] }}
                                        @endisset
                                    </td>
                                    {{--PAC_5-2098 Start--}}
                                    @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                        {{--PAC_5-1599 追加部署と役職 Start--}}
                                        <td ng-click="goBook({{ $user['id'] }})">
                                            @isset($listDepartmentDetail[$user->info->mst_department_id_1]['text'])
                                                {{ $listDepartmentDetail[$user->info->mst_department_id_1]['text'] }}
                                            @endisset
                                        </td>
                                        <td ng-click="goBook({{ $user['id'] }})">
                                            @isset($listPosition[$user->info->mst_position_id_1])
                                                {{ $listPosition[$user->info->mst_position_id_1]['text'] }}
                                            @endisset
                                        </td>
                                        <td ng-click="goBook({{ $user['id'] }})">
                                            @isset($listDepartmentDetail[$user->info->mst_department_id_2]['text'])
                                                {{ $listDepartmentDetail[$user->info->mst_department_id_2]['text'] }}
                                            @endisset
                                        </td>
                                        <td ng-click="goBook({{ $user['id'] }})">
                                            @isset($listPosition[$user->info->mst_position_id_2])
                                                {{ $listPosition[$user->info->mst_position_id_2]['text'] }}
                                            @endisset
                                        </td>
                                        {{--PAC_5-1599 End--}}
                                    @endif
                                    {{--PAC_5-2098 End--}}
                                </tr>
                                @endforeach

                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $users])
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy"/>
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir"/>
                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;"/>

            </div>
        @endif
    </form>
</div>


@push('scripts')
    <script>




        // angular スタート
        if (appPacAdmin) {
            appPacAdmin.controller('ListController', function ($scope, $rootScope, $http) {

                $rootScope.search = {email: "", name: "", department: "", position: "", status: ""};
                $scope.numChecked = 0;

                // CSVダウンロード開始
                $scope.downloadCsv = function () {
                    let userIds = Array()
                    let title = ''
                    // 選択された利用者のIDを取得する
                    $('.cid').each(function (index, item) {
                        let isChecked = $(item).prop('checked')
                        if (isChecked) {
                            userIds.push($(item).val())
                        }
                    })

                    // チェックされない場合は一覧全員をダウンロードする
                    if (userIds.length < 1) {
                        title = "表示されている利用者データを出力します。<br />実行しますか？"
                        $('.cid').each(function (index, item) {
                            userIds.push($(item).val())
                        })
                    } else {
                        title = "選択されている利用者データを出力します。<br />実行しますか？"
                    }

                    // エラー発生の防ぐ
                    if (userIds.length < 1) {
                        alert('利用者を選択してください');
                        return
                    }
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: title,
                            btnSuccess: 'はい',
                            size: 'normal',
                            callSuccess: function () {
                                $rootScope.$emit("showLoading");
                                $http.post('{{ route('CsvTimeCard') }}',
                                    {
                                        targetMonth: '{{ request()->search_month ? request()->search_month : now()->format('Y-m') }}',
                                        userId: userIds,
                                    }
                                ).then(function (event) {
                                    $rootScope.$emit("hideLoading");
                                    if (event.data.status == false) {
                                        $(".message").append(showMessages(event.data.message, 'danger', 10000));
                                    } else {
                                        $(".message").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                })
                            }
                        });
                };
                $scope.goBook = function (userid) {
                    window.location.href = link_book + '?id=' + userid + '&search_month={{ Request::get('search_month', now()->format('Y-m')) }}'
                };
            })
        } else {
            throw new Error("Something error init Angular.");
        }

        $(document).ready(function () {
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });
            $('form[name="adminForm"]').submit(function (e) {
                $('input[name="page"]').val('1');
            });
            $('.select2').select2({
                placeholder: '',
                allowClear: true,
                "language": {
                    "noResults": function () {
                        return "データがありません";
                    }
                }
            });
        });

        $("#modalDetailItem").on('hide.bs.modal', function () {
            $(".adminlist tr.edit").removeClass('edit');
            if (hasChange) {
                location.reload();
            }
        });
    </script>
@endpush

@push('styles_after')


    <style>

        .form-control {
            height: 38px;
        }

        .select2-container .select2-selection {
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
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }

        .numInputWrapper .numInput {
            margin-left: 52px !important;
        }
    </style>
@endpush
