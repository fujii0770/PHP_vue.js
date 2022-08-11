<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET" >
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('sc_company_name','会社名',Request::get('sc_company_name', ''),'text', false,
                    [ 'placeholder' =>'会社名（部分一致）', 'id'=>'sc_company_name', ]) !!}
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-3 control-label">会社登録日</label>
                            <div class="col-md-4">
                                <input type="date" name="scag_fromdate" value="{{ Request::get('scag_fromdate', '') }}" 
                                class="form-control" placeholder="yyyy/mm/dd" id="scag_fromdate" >
                            </div>
                            <label for="name" class="col-md-1 control-label">~</label>
                            <div class="col-md-4">
                                <input type="date" name="scag_todate" value="{{ Request::get('scag_todate', '') }}" 
                                class="form-control" placeholder="yyyy/mm/dd" id="scag_todate" >
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
            <div class="row">
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('sc_department','部署名',Request::get('sc_department', ''),'text', false,
                    [ 'placeholder' =>'部署名（部分一致）', 'id'=>'sc_department', ]) !!}
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-3 control-label">部署登録日</label>
                            <div class="col-md-4">
                                <input type="Date" name="scte_fromdate" value="{{ Request::get('scte_fromdate', '') }}" 
                                class="form-control" placeholder="yyyy/mm/dd" id="scte_fromdate" >
                            </div>
                            <label for="name" class="col-md-1 control-label">~</label>
                            <div class="col-md-4">
                                <input type="date" name="scte_todate" value="{{ Request::get('scte_todate', '') }}" 
                                class="form-control" placeholder="yyyy/mm/dd" id="scte_todate" >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-1"></div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('sc_address','住所',Request::get('sc_address', ''),'text', false,
                    [ 'placeholder' =>'住所（部分一致）', 'id'=>'sc_address',  ]) !!}
                </div>
                <div class="col-lg-6">
                    {!! \App\Http\Utils\CommonUtils::showFormField('sc_phone_no','電話番号',Request::get('sc_phone_no', ''),'text', false,
                    [ 'placeholder' =>'電話番号（部分一致）', 'id'=>'sc_phone_no' ]) !!}
                </div>

            </div>
            <div class="text-right">
                <button class="btn btn-primary mb-1" ><i class="fas fa-search" ></i> 検索</button>
                @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE])
                <div class="btn btn-success  mb-1" ng-click="addAgencyNew()"><i class="fas fa-plus-circle" ></i> 会社登録</div>
                <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 派遣先登録</div>
                @endcanany
                <div class="btn btn-success  mb-1" ng-click="upload()" style="display:none;"><i class="fas fa-upload" ></i> CSV取込</div>    
                <input type="hidden" class="action" name="action" value="search" />

            </div>
        </div>
        <div class="message message-list mt-3"></div>
        <ul class="nav nav-tabs">
            {!! \App\Http\Utils\DispatchUtils::showTabItem('会社一覧', 'company_list') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('派遣先一覧', 'dispatchArea_list') !!}
        </ul>
        <div class="tab-content">
            <div class="tab-pane" id="company_list"
                ng-class="{active: showTab =='company_list', fade: showTab !='company_list' }">
                @if($agency_list)
                <div class="card mt-3">
                    <div class="card-header">会社一覧</div>
                    <div class="card-body">
                        <div class="table-head">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                        <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                            <select style="width: 100px" name="agency_limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                <option {{Request::get('agency_limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                <option {{Request::get('agency_limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                <option {{Request::get('agency_limit') == '100' ? 'selected': ''}} value="100">100</option>
                                            </select>
                                        </label>
                                        @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_DELETE])
                                            <button type="button" class="btn btn-danger" ng-disabled="agency_numChecked==0" ng-click="agencydelete($event)"><i class="fas fa-trash-alt"></i> 削除</button>
                                        @endcanany
                                    </div>
                                </div>
                            </div>

                        </div>
                        <span class="clear"></span>
                        <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" >
                            <thead>
                                <tr>
                                    <th class="title sort check" scope="col" data-tablesaw-priority="persist" >
                                        <input type="checkbox" onClick="checkAll('agency',this.checked)" />
                                    </th>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width:130px;">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('会社名', 'company_name', $agency_orderBy, $agency_orderDir,
                                            'adminForm', 'agency_orderBy', 'agency_orderDir') !!}
                                    </th>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width:130px;">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('事業所名', 'office_name', $agency_orderBy, $agency_orderDir,
                                            'adminForm', 'agency_orderBy', 'agency_orderDir') !!}
                                    </th>
                                    <th scope="col">住所</th>
                                    <th scope="col">ビルなど</th>
                                    <th scope="col">派遣先</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($agency_list as $i => $item)
                                    <tr>
                                        <td class="title">
                                            <input type="checkbox" value="{{ $item->id }}"  class="agency_cid" onClick="isChecked('agency',this.checked)" ng-disabled ="{{$item->department !=''}}" />
                                        </td>
                                        <td ng-click="editAgencyRecord({{ $item->id }})" >{{ $item->company_name }}</td>
                                        <td ng-click="editAgencyRecord({{ $item->id }})">{{ $item->office_name }}</td>
                                        <td ng-click="editAgencyRecord({{ $item->id }})">{{ $item->address1 }}</td>
                                        <td ng-click="editAgencyRecord({{ $item->id }})">{{ $item->address2 }}</td>
                                        <td ng-click="editAgencyRecord({{ $item->id }})">{{ $item->department }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @include('layouts.table_footer',['data' => $agency_list])

                    </div>
                </div>
                @endif
            </div>
            <div class="tab-pane" id="dispatchArea_list"
                ng-class="{active: showTab =='dispatchArea_list', fade: showTab !='dispatchArea_list' }">
                @if($dispatcharea_list)
                <div class="card mt-3">
                    <div class="card-header">派遣先一覧</div>
                    <div class="card-body">
                        <div class="table-head">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                        <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                            <select style="width: 100px" name="dispatcharea_limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                <option {{Request::get('dispatcharea_limit') == '20' ? 'selected': ''}} value="20">20</option>
                                                <option {{Request::get('dispatcharea_limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                <option {{Request::get('dispatcharea_limit') == '100' ? 'selected': ''}} value="100">100</option>
                                            </select>
                                        </label>
                                        @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_DELETE])
                                            <button type="button" class="btn btn-danger" ng-disabled="dispatchaea_numChecked==0" ng-click="dispatchareadelete($event)"><i class="fas fa-trash-alt"></i> 削除</button>
                                        @endcanany
                                    </div>
                                </div>
                            </div>

                        </div>
                        <span class="clear"></span>
                        <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" >
                            <thead>
                                <tr>
                                    <th class="title sort check" scope="col" data-tablesaw-priority="persist" >
                                        <input type="checkbox" onClick="checkAll('dispatcharea',this.checked)" />
                                    </th>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width:130px;">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('会社名', 'company_name', $dispatcharea_orderBy, $dispatcharea_orderDir,
                                            'adminForm', 'dispatcharea_orderBy', 'dispatcharea_orderDir') !!}
                                    </th>
                                    <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width:130px;">
                                        {!! \App\Http\Utils\CommonUtils::showSortColumn('部署名', 'department', $dispatcharea_orderBy, $dispatcharea_orderDir,
                                            'adminForm','dispatcharea_orderBy', 'dispatcharea_orderDir') !!}
                                    </th>
                                    <th scope="col">責任者</th>
                                    <th scope="col">電話番号</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dispatcharea_list as $i => $item)
                                    <tr>
                                        <td class="title">
                                            <input type="checkbox" value="{{ $item['id'] }}"  class="dispatcharea_cid" onClick="isChecked('dispatcharea',this.checked)" />
                                        </td>
                                        <td ng-click="editRecord({{ $item['id'] }})">{{ $item->company_name }}</td>
                                        <td ng-click="editRecord({{ $item['id'] }})">{{ $item->department }}</td>
                                        <td ng-click="editRecord({{ $item['id'] }})">{{ $item->responsible_name }}</td>
                                        <td ng-click="editRecord({{ $item['id'] }})">{{ $item->responsible_phone_no }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @include('layouts.table_footer',['data' => $dispatcharea_list])

                    </div>
                </div>
                @endif

            </div>
        </div>
        <input type="hidden" value="{{ $agency_orderBy }}" name="agency_orderBy" />
        <input type="hidden" value="{{ Request::get('agency_orderDir','DESC') }}" name="agency_orderDir" />
        <input type="hidden" name="agency_page" value="{{Request::get('agency_page',1)}}">
        <input type="text" class="agency_boxchecked" ng-model="agency_numChecked" style="display: none;" />

        <input type="hidden" value="{{ $dispatcharea_orderBy }}" name="dispatcharea_orderBy" />
        <input type="hidden" value="{{ Request::get('dispatcharea_orderDir','DESC') }}" name="dispatcharea_orderDir" />
        <input type="hidden" name="dispatcharea_page" value="{{Request::get('dispatcharea_page',1)}}">
        <input type="text" class="dispatcharea_boxchecked" ng-model="dispatcharea_numChecked" style="display: none;" />
        @if(empty($agency_list))
            <input type="hidden" name="agency_limit" value="20" />
        @endif       
        @if(empty($dispatcharea_list))
            <input type="hidden" name="dispatcharea_limit" value="20" />
        @endif       
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.isShowAgency = true;
                $scope.isShowDispatchArea = true;
                $scope.option_limit = [20,50,100];  
                $scope.showTab = localStorage.getItem('dispatchareatitle.tab');
                if(!$scope.showTab) $scope.showTab = 'company_list';   
                localStorage.setItem('dispatchareatitle.tab', $scope.showTab);
                $scope.showSch ={
                    agency:false,
                    dispatcharea:false,
                };
                $scope.dispatcharea_numChecked = 0;
                $scope.agency_numChecked = 0;
                $scope.orderBy={
                    agency:'create_at',
                    dispatcharea:'create_at'
                }
                $scope.orderDir={
                    agency:'desc',
                    dispatcharea:'desc'
                }
                $scope.onShowTab = function(tab){
                    $scope.showTab = tab;
                    localStorage.setItem('dispatchareatitle.tab', tab);
                }

                $scope.addNew = function(){
                    $rootScope.$emit("openNewDispatchArea");
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditDispatchArea",{id:id});
                };
                $scope.editAgencyRecord = function(id){
                    $rootScope.$emit("openEditAgency",{id:id});
                };

                $scope.addAgencyNew = function(){
                    $rootScope.$emit("openNewAgency");
                };
                $scope.agencydelete = function(event){
                    event.preventDefault();

                    var cids = [];
                    for(var i =0; i < $(".agency_cid:checked").length; i++){
                        cids.push($(".agency_cid:checked")[i].value);
                    }

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択した会社を削除します。よろしいですか？',
                            btnDanger:'削除',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_agency_deletes, { cids: cids})
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

                $scope.dispatchareadelete = function(event){
                    event.preventDefault();

                    var cids = [];
                    for(var i =0; i < $(".dispatcharea_cid:checked").length; i++){
                        cids.push($(".dispatcharea_cid:checked")[i].value);
                    }

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択した派遣先を削除します。よろしいですか？',
                            btnDanger:'削除',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_dispatcharea_deletes, { cids: cids})
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

                $scope.range_func = function(n) {
                    return new Array(n);
                };

                function checkAll(kbn ,isitchecked) {
                    var classname = "."+kbn+"_cid";
                    var boxname = "."+kbn+"_boxchecked";
                    if (isitchecked) {		 
                        $(classname).prop('checked', true);
                    } else {	 
                        $(classname).prop('checked', false);
                    }
                    $(boxname).val($(classname+":checked").length).change();
                    return true;
                }

                function isChecked(kbn,isitchecked) {
                    var classname = "."+kbn+"_cid";
                    var boxname = "."+kbn+"_boxchecked";
                    $(boxname).val($(classname+":checked").length).change();
                }
  
            })
        }else{
            throw new Error("Something error init Angular.");
        }
        $(document).ready(function() {
            $('select[name="agency_limit"]').change(function () {
                var value = $(this).val();
                $('input[name="agency_page"]').val('1');
                document.adminForm.submit();
            });
            $('select[name="dispatcharea_limit"]').change(function () {
                var value = $(this).val();
                $('input[name="dispatcharea_page"]').val('1');
                document.adminForm.submit();
            });
            $('form[name="adminForm"]').submit(function(e){
                $('input[name="page"]').val('1');
            });
        });

        $("#modalDetailItem").on('hide.bs.modal', function () {
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
        .detail_none{
            display:none;
        }
    </style>
@endpush
