<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">

            <div class="row">
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('sc_dispatcharea_name','派遣先名',Request::get('sc_dispatcharea_name', ''),'text', false,
                    [ 'id'=>'sc_dispatcharea_name', 'placeholder' =>'派遣先名（部分一致）' ]) !!}
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-3 control-label">契約日</label>
                            <div class="col-md-4">
                                <input type="date" name="scs_fromdate" value="{{ Request::get('scs_fromdate', '') }}" 
                                class="form-control" placeholder="yyyy/mm/dd" id="scs_fromdate" >
                            </div>
                            <label for="name" class="col-md-1 control-label">~</label>
                            <div class="col-md-4">
                                <input type="date" name="scs_todate" value="{{ Request::get('scs_todate', '') }}" 
                                class="form-control" placeholder="yyyy/mm/dd" id="scs_todate" >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE])
                    <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
                    <div class="btn btn-success  mb-1" ng-click="upload()" style="display:none;"><i class="fas fa-upload" ></i> CSV取込</div>
                @endcanany      
                @if(empty($contract_list))
                    <input type="hidden" name="limit" value="20" />
                @endif            
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        @if($contract_list)
            <div class="card mt-3">
                <div class="card-header">契約一覧</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                    <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                        <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                        </select>
                                    </label>
                                    @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_DELETE])
                                        <button type="button" class="btn btn-danger" ng-disabled="numChecked==0" ng-click="delete($event)"><i class="fas fa-trash-alt"></i> 削除</button>
                                    @endcanany
                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th class="title sort check" scope="col" data-tablesaw-priority="persist">
                                   <input type="checkbox" onClick="checkAll(this.checked)" />
                                </th>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width:130px;">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('派遣期間開始', 'contract_fromdate', $orderBy, $orderDir) !!}
                                </th>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width:130px;">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('契約期間終了', 'contract_todate', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('派遣先', 'dispatcharea_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col">スタッフ</th>
                                <th scope="col" style="width:100px;">期間更新</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contract_list as $i => $contract)
                                <tr>
                                    <td class="title">
                                        <input type="checkbox" value="{{ $contract['id'] }}"  class="cid" onClick="isChecked(this.checked)" />
                                    </td>
                                    <td ng-click="editRecord({{ $contract['id'] }})">{{ $contract->disp_contract_fromdate }}</td>
                                    <td ng-click="editRecord({{ $contract['id'] }})">{{ $contract->disp_contract_todate }}</td>
                                    <td ng-click="editRecord({{ $contract['id'] }})">{{ $contract->dispatcharea_name }}</td>
                                    <td ng-click="editRecord({{ $contract['id'] }})">{{ $contract->name }}</td>
                                    <td class="text-center col-action">
                                    @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE])
                                        <div class="btn btn-primary btn-sm" ng-click="periodRecord({{ $contract['id'] }})">更新</div>
                                    @endcanany
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $contract_list])
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

                $rootScope.search = {
                    sc_department:"", 
                    scs_fromdata:"", 
                    scs_todata:"", 
                    sce_fromdata:"", 
                    sce_todata:"", 
                };
                $scope.numChecked = 0;

                $scope.addNew = function(){
                    $rootScope.$emit("openNewContract");
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditContract",{id:id});
                };
                $scope.periodRecord = function(id){
                    $rootScope.$emit("openPeriodContract",{id:id});
                };
                $scope.delete = function(event){
                    event.preventDefault();

                    var cids = [];
                    for(var i =0; i < $(".cid:checked").length; i++){
                        cids.push($(".cid:checked")[i].value);
                    }

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択した契約を削除します。よろしいですか？',
                            btnDanger:'削除',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_deletes, { cids: cids})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            location.reload();
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });

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
        table{
            table-layout: fixed;
        }
        td{
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        th.check{
            width: 35px;
        }
    </style>
@endpush
