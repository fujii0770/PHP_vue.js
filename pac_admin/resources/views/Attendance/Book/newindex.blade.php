@extends('../layouts.main')

@push('styles_before')


    <style>
        table th, table td {
            padding: 6px 10px;
        }

        td {
            text-align: right;
        }
        table th {
            font-size: 18px !important;
        }

        .modal-content {
            width: 100%;
        }

        .modal-dialog {
            max-width: 575px;
            margin: 9.3rem auto;
        }

        #search_month {
            background-color: #FFFFFF !important;
            font-size: initial !important;
            width: 160px;
        }

        .numInput {
            padding-left: 36px !important;
        }
    </style>
@endpush

@section('content')
    <span class="clear"></span>
    <div class="SettingUser">

        <div ng-controller="ListController">

            <div class="card mt-3">
                <div class="card-header">{{$data['username']}}</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">

                                <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                    <div class="btn btn-default" style="float: left;margin-right: 20px"
                                         onclick="location.href='{{ route('attendance.users') }}'">
                                        <i class="fas fa-long-arrow-alt-left"></i> 戻る
                                    </div>

                                    <form action="{{ route('attendance.book') }}" name="adminForm" method="GET">
                                        <input type="hidden" name="id" value="{{ $data['userId'] }}">
                                        <label class="d-flex" style="float:left">
                                            <input type="month"  name="search_month"  value="{{ request()->search_month ? request()->search_month : now()->format('Y-m') }}" class="form-control" id="search_month" oninput="this.form.submit()">
                                        </label>
                                    </form>

                                    <button type="button" class="btn btn-warning btn-block"
                                            style="width: 150px; margin-left: 277px; margin-top: -2px;"
                                            ng-click="downloadCsv()"><i class="fas fa-download"></i> CSV出力
                                    </button>

                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>

                    @if(session('time_update_msg'))
                        <div
                            class='alert {{ session('time_update_msg') == '更新失敗しました' ? 'alert-danger' : 'alert-info' }}'
                            style="margin-bottom: 1rem">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;
                            </button>
                            {{session('time_update_msg')}}
                        </div>
                    @endif

                    <div class="message message-list mt-3"></div>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5"
                           data-tablesaw-mode="swipe" style="width: 100%">
                        <thead>
                        <tr>
                            <th class="title">
                                日付
                            </th>
                            <th scope="col">
                                出勤1
                            </th>
                            <th scope="col">
                                退勤1
                            </th>
                            <th scope="col">
                                出勤2
                            </th>
                            <th scope="col">
                                退勤2
                            </th>
                            <th scope="col">
                                出勤3
                            </th>
                            <th scope="col">
                                退勤3
                            </th>
                            <th scope="col">
                                出勤4
                            </th>
                            <th scope="col">
                                退勤4
                            </th>
                            <th scope="col">
                                出勤5
                            </th>
                            <th scope="col">
                                退勤5
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($data['tableData']) > 0)
                            @foreach ($data['tableData'] as $key=>$val)
                                @php

                                    $date = explode(' ', $key);
                                       $keyForModalId = \Carbon\Carbon::parse($date[0])->format('Y年m月d日');
                                       $formatedDate = $keyForModalId. ' '. $date[1];
                                @endphp
                                <tr onclick="showDetail('modalDetailItem_{{ $keyForModalId }}')">
                                    <td class="title" style="text-align: left">
                                        {{ $key }}
                                    </td>

                                    @for($num = 1; $num <= 5; $num ++)
                                        @if($num % 2 == 0)
                                            <td scope="col">{{ $val->punch_data['start'.$num] }}</td>
                                            <td scope="col">{{ $val->punch_data['end'.$num] }}</td>
                                        @else
                                            <td scope="col">{{ $val->punch_data['start'.$num] }}</td>
                                            <td scope="col">{{ $val->punch_data['end'.$num] }}</td>
                                        @endif
                                    @endfor
                                </tr>

                                <div class="modal modal-detail" id="modalDetailItem_{{ $keyForModalId }}"
                                     data-backdrop="static"
                                     data-keyboard="false">
                                    <div class="modal-dialog ">
                                        <div class="modal-content">
                                            <!-- Modal Header -->
                                            <div class="modal-header" style="padding: 0.5rem 1rem;">
                                                <h4 class="modal-title">打刻編集</h4>
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onClick="closeModal('modalDetailItem_{{ $keyForModalId }}')">
                                                    &times;
                                                </button>
                                            </div>
                                            <!-- Modal body -->
                                            <form action="{{ route('attendance.update', str_replace('/', '-', $key)) }}"
                                                  method="post" id="form_{{ str_replace('/', '-', $key) }}">
                                                <input type="text" name="_token" value="{{csrf_token()}}" style="display: none">
                                                <input type="text" name="userid" value="{{ $data['userId'] }}" style="display: none">
                                                <div class="modal-body form-horizontal">
                                                    <div class="card" style="margin-bottom: 0px !important;">

                                                        <div class="card-header">{{ $formatedDate }}</div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                @for($num = 1; $num <= 5; $num ++)
                                                                    @if($num % 2 == 0)
                                                                        <div class="row">
                                                                            <label for="stime1"
                                                                                   class="col-md-2 col-sm-2 col-12 control-label">出勤{{$num}}</label>
                                                                            <div class="col-md-10 col-sm-10 col-12">
                                                                                <div class="input-group mb-1">
                                                                                    <input type="text"
                                                                                           name="data[type][]"
                                                                                           value="start{{ $num }}"
                                                                                           style="display: none">
                                                                                    <input type="time"
                                                                                           name="data[time][]"
                                                                                           value="{{ $val->punch_data['start'.$num] }}"
                                                                                           class="form-control"
                                                                                           placeholder="hh:mm"/>

                                                                                    <button class="btn btn btn-danger"
                                                                                            type="button"
                                                                                            onclick="tempDelete(this)"><i
                                                                                            class="fas fa-trash-alt"></i> 削除
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <label for="stime1"
                                                                                   class="col-md-2 col-sm-2 col-12 control-label">退勤{{$num}}</label>
                                                                            <div class="col-md-10 col-sm-10 col-12">
                                                                                <div class="input-group mb-1">


                                                                                    <input type="text"
                                                                                           name="data[type][]"
                                                                                           value="end{{$num}}"
                                                                                           style="display: none">

                                                                                    <input type="time"
                                                                                           name="data[time][]"
                                                                                           value="{{ $val->punch_data['end'.$num] }}"
                                                                                           class="form-control"
                                                                                           placeholder="hh:mm"/>
                                                                                    <button class="btn btn btn-danger"
                                                                                            type="button"
                                                                                            onclick="tempDelete(this)"><i
                                                                                            class="fas fa-trash-alt"></i> 削除
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="row">
                                                                            <label for="stime1"
                                                                                   class="col-md-2 col-sm-2 col-12 control-label">出勤{{$num}}</label>
                                                                            <div class="col-md-10 col-sm-10 col-12">
                                                                                <div class="input-group mb-1">
                                                                                    <input type="text"
                                                                                           name="data[type][]"
                                                                                           value="start{{ $num }}"
                                                                                           style="display: none">
                                                                                    <input type="time"
                                                                                           name="data[time][]"
                                                                                           value="{{ $val->punch_data['start'.$num] }}"
                                                                                           class="form-control"
                                                                                           placeholder="hh:mm"/>
                                                                                    <button class="btn btn btn-danger"
                                                                                            type="button"
                                                                                            onclick="tempDelete(this)"><i
                                                                                            class="fas fa-trash-alt"></i> 削除
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <label for="stime1"
                                                                                   class="col-md-2 col-sm-2 col-12 control-label">退勤{{$num}}</label>
                                                                            <div class="col-md-10 col-sm-10 col-12">
                                                                                <div class="input-group mb-1">

                                                                                    <input type="text"
                                                                                           name="data[type][]"
                                                                                           value="end{{ $num }}"
                                                                                           style="display: none">
                                                                                    <input type="time"
                                                                                           name="data[time][]"
                                                                                           value="{{ $val->punch_data['end'.$num] }}"
                                                                                           class="form-control"
                                                                                           placeholder="hh:mm"/>
                                                                                    <button class="btn btn btn-danger"
                                                                                            type="button"
                                                                                            onclick="tempDelete(this)"><i
                                                                                            class="fas fa-trash-alt"></i> 削除
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endfor
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">
                                                                <ng-template><i class="far fa-save"></i> 更新
                                                                </ng-template>
                                                            </button>

                                                            <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal"
                                                                    onclick="closeModal('modalDetailItem_{{ $keyForModalId }}')">
                                                                <i class="fas fa-times-circle"></i> 閉じる
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <tr style="">
                                <td colspan="10" style="text-align: center">データがありません</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection


@push('scripts')
    <script>



        function showDetail(id) {
            $('#' + id).show()
        }

        function closeModal(id) {
            $('#' + id).hide()
        }

        function tempDelete(obj) {
            $(obj).prev().val('')
        }

        var appPacAdmin = initAngularApp()

        if (appPacAdmin) {
            appPacAdmin.controller('ListController', function ($scope, $rootScope, $http, $sce) {
                $scope.downloadCsv = function () {
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '打刻履歴データを出力します。実行しますか？',
                            btnSuccess: 'はい',
                            callSuccess: function () {
                                $rootScope.$emit("showLoading");
                                $http.post('{{ route('CsvTimeCard') }}',
                                    {
                                        targetMonth: '{{ request()->search_month ? request()->search_month : now()->format('Y-m') }}',
                                        userId: {{ $data['userId'] }}
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
                        })
                }
            })
        }
    </script>
@endpush
