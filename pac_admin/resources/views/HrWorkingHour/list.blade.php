<div ng-controller="ListController">
    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('definition_name','定義名称',Request::get('definition_name', ''),'text', false,
                    [ 'placeholder' =>'定義名称', 'id'=>'definition_name' ]) !!}
                </div>
                <div class="col-lg-3">
                    <label for="hr_working_hours" style="margin-bottom:0px;">勤務形態</label>
                   {!! \App\Http\Utils\CommonUtils::buildSelect(['通常勤務','シフト勤務','フレックス勤務'], 'work_form_kbn',Request::post('work_form_kbn', ''),'',['class'=> 'form-control']) !!}
                </div>


            </div>


            <div class="text-right mt-4">
                    <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                @canany([PermissionUtils::PERMISSION_HR_WORKING_HOUR_CREATE])
                <div class="btn btn-success  mb-1" ng-click="detailsRecord()"><i class="fas fa-plus-circle" ></i> 登録</div>
                @endcanany
             </div>

        </div>

        <div class="message message-list mt-3"></div>

        <div class="card mt-3">
            <div class="card-body">


                <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                    <thead>
                    <tr>
                        <th class="title sort" scope="col" data-tablesaw-priority="persist">
                            <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" />
                        </th>
                        <td class="title">
                            定義名称
                        </td>
                        <td class="title">
                            勤務形態
                        </td>

                    </tr>
                    </thead>
                    <tbody>
                    @if ($arrHistory)
                        @foreach ($arrHistory as $i => $item)
                            <tr class="row-{{ $item->id }} row-edit" ng-class="{ edit: id == {{ $item->id }} }" >

                                    <td class="title">
                                            <input type="checkbox"  checked="checked" value="{{ $item->id }}"
                                                   name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                    </td>

                                <td ng-click="upinfo({{ $item->id}})" class="title">
                                    {{ $item -> definition_name }}
                                </td>

                                <td ng-click="upinfo({{ $item->id}})" class="title">
                                    {{ \App\Http\Utils\AppUtils::HR_WORKING_HOURS[$item->work_form_kbn] }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>

            </div>
        </div>
        @include('layouts.table_footer',['data' => $arrHistory])
        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
        <input type="hidden" value="{{ Request::get('dt_orderDir','DESC') }}" name="orderDir" />
        <input type="hidden" name="page" value="1">
        <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.isCheckAll = false;
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
                            title:'就労時間管理更新。',
                            btnSuccess:'はい',
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_store, { cidsoff:cidsoff,cids: cids })
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

                $scope.detailsRecord = function(){
                    $("#modalDetailItem").modal();
                    $rootScope.$emit("openNewUser");
                };
                $scope.upinfo=function(id){
                    $rootScope.$emit("openDetailsHrUserReg",{id:id});
                }
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
        label{
            cursor: pointer;
        }
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
