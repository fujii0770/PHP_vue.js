<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET">
        <div class="message message-list mt-3"></div>
            <div class="card mt-3">
                <div class="card-header">{{$data['username']}}</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">

                                <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                    <div class="btn btn-default" style="float: left;margin-right: 20px" onclick="window.history.back()">
                                        <i class="fas fa-long-arrow-alt-left"></i> 戻る
                                    </div>
                                    <label class="d-flex" style="float:left" >
                                        <input type="month" name="working_month_start"
                                               value="{{ Request::get('working_month_start', '') }}" class="form-control"
                                               placeholder="yyyy/mm" id="working_month_start"/>
                                    </label>
                                    <div class="btn btn-warning  mb-1" ng-click="downloadCsv()"><i class="fas fa-download" ></i> CSV出力</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe" style="width: 100%">
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
                            @foreach ($data['tableData'] as $key=>$val)
                                <tr ng-click="editRecord('{{$num}}')">
                                    <td class="title">
                                        {{ $key }}
                                    </td>
                                    @foreach($val as $m=>$n)
                                    <td scope="col">
                                        {{ $n['format_time'] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.downloadCsv = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'表示されている利用者データを出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_csv, { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $(".message").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                };
                $scope.editRecord = function(date){
                    $rootScope.$emit("openEdit",{id:date});
                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }

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
