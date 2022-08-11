<div ng-controller="ListController">
        
    <form action="" name="adminForm" method="GET">
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('name','企業名',Request::get('name', ''),'text', false,
                    [ 'placeholder' =>'企業名(検索条件)', 'id'=>'name' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('domain','ドメイン',Request::get('domain', ''),'text', false,
                    [ 'placeholder' =>'ドメイン(検索条件)', 'id'=>'domain' ]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('family_name','管理者',Request::get('family_name', ''),'text', false,
                    [ 'placeholder' =>'管理者(検索条件)', 'id'=>'family_name' ]) !!}
                </div>

                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('number','共通印申込書番号',Request::get('number', ''),'text', false,
                    [ 'placeholder' =>\App\Http\Utils\CommonUtils::getPdfNumberFirst()."＊＊＊＊＊＊（部分一致）", 'id'=>'number' ]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('remark_message','備考',Request::get('remark_message', ''),'text', false,
                    [ 'placeholder' =>'備考(検索条件)', 'id'=>'remark_message' ]) !!}
                </div>
                <div class="col-lg-8">
                    <div class="text-right mt-4">
                        <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                        <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
                        <input type="hidden" class="action" name="action" value="search" />
                    </div>
                </div>
            </div>
             
                       
        </div>

        <div class="message message-list mt-3"></div>
        @if ($items)
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数: </label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                   {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="clear"></span>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>                            
                                <th class="title sort" scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('企業名', 'company_name', $orderBy, $orderDir) !!}
                                </th>                            
                                                            
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('契約状況', 'state', $orderBy, $orderDir) !!}
                                </th>
                                
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('印面登録数', 'assigned_stamps_count', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('共通印数', 'company_stamps_count', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('登録日', 'create_at', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('更新日', 'update_at', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('備考', 'remark_message', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $i => $item)
                                <tr class="row-{{ $item->id }} row-edit" ng-class="{ edit: id == {{ $item->id }} }"
                                ng-click="editRecord({{ $item->id }})"
                                    >
                                    <td class="title">{{ $item->company_name }}</td>
                                    <td class="title">{{ \App\Http\Utils\AppUtils::getCompanyContractStatus($item->contract_edition, $item->trial_flg, $item->state) }}</td>
                                    <td class="title">{{ $item->assigned_stamps_count }} / {{ $item->upper_limit }}</td>
                                    <td class="title">{{ $item->company_stamps_count }}</td>
                                    <td class="title">{{ $item->create_at }}</td>
                                    <td class="title">{{ $item->update_at }}</td>
                                    <td class="title" title="{{$item->remark_message}}">{{ (mb_strlen($item->remark_message,'utf-8') > 30 ? mb_substr($item->remark_message,0,30,'utf-8').'...' : $item->remark_message) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @include('layouts.table_footer',['data' => $items])
                </div>
            </div>
        @endif
        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
        <input type="hidden" name="page" value="{{Request::get('page',1)}}">
    </form>
</div>


@push('scripts')
    <script>
        var hasChange = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.addNew = function(){
                    $rootScope.$emit("openNewCompany");
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditCompany",{id:id});
                 };
            });

        }else{
            throw new Error("Something error init Angular.");
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