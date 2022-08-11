<div ng-controller="ListController">
    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
        @csrf
        <div class="form-search form-vertical">
            <div class="row">
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('email','メールアドレス',Request::get('email', ''),'text', false, 
                    [ 'placeholder' =>'メールアドレス', 'id'=>'email' ]) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('username','氏名',Request::get('username', ''),'text', false, 
                    [ 'placeholder' =>'氏名', 'id'=>'username' ]) !!}
                </div>
                <div class="col-lg-3 form-group">
                    <label class="control-label" >部署</label>
                    {!! \App\Http\Utils\CommonUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control']) !!}
                </div>
                <div class="col-lg-2 form-group">
                    <label class="control-label" >役職</label>
                    {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                </div>
            </div>
             
            <div class="row">
                <div class="col-lg-3 form-group">
                    <label class="control-label" >管理</label>
                    {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::HR_ADMIN_FLG, 'hrAdminFlg', Request::get('hrAdminFlg', ''),'',['class'=> 'form-control']) !!}
                </div>
                <div class="col-lg-3">
                    {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('assignedcompany','配置現場名',Request::get('assignedcompany', ''),'text', false, 
                    [ 'placeholder' =>'配置現場名', 'id'=>'assignedcompany' ]) !!}
                </div>
                <div class="col-lg-4"></div>
                <div class="col-lg-2 text-right text-left-lg padding-top-20">
                    <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                    <input type="hidden" class="action" name="action" value="search" />
                </div>
            </div>            
        </div>

        <div class="message message-list mt-3"></div>

        <div class="card mt-3">
            <div class="card-body">
	            <div class="table-head">
	                <div class="form-group">
	                    <div class="row">
	                        <div class="col-lg-4">
                                @canany([PermissionUtils::PERMISSION_HR_ADMIN_SETTING_UPDATE])
                                <button class="btn btn-success m-0" ng-click="approval($event)">一括管理登録</button>
                                @endcanany
	                        </div>
	                    </div>
	                </div>
	            </div>

                <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>                            
                            <th class="title sort" scope="col" data-tablesaw-priority="persist">
                            <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()" />
                            </th>
                            <th class="title sort" scope="col">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('管理', 'hrAdminFlg', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'username', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'department', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('役職', 'position', $orderBy, $orderDir) !!}
                            </th>
                            <th scope="col"  class="sort">
                                {!! \App\Http\Utils\CommonUtils::showSortColumn('配置現場名', 'assignedCompany', $orderBy, $orderDir) !!}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($arrHistory)
                            @foreach ($arrHistory as $i => $item)
                                <tr class="row-{{ $item->id }} row-edit" ng-class="{ edit: id == {{ $item->id }} }" >
                                    @if (empty($item->hr_info_id))
                                        <td class="title"> 
                                            @if ($item->hr_admin_flg ==1)
                                            <input type="checkbox"  checked="checked" value="{{ $item->id }}" 
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                            @else
                                            <input type="checkbox" value="{{ $item->id }}"
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                            @endif
                                        </td>
                                        <td ng-click="detailsRecord(0,{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ \App\Http\Utils\AppUtils::HR_ADMIN_FLG[$item->hr_admin_flg] }}</td>
                                        <td ng-click="detailsRecord(0,{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->user_name.'('.$item->email.')' }}</td>
                                        <td ng-click="detailsRecord(0,{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->department_name }}</td>
                                        <td ng-click="detailsRecord(0,{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->position_name }}</td>
                                        <td ng-click="detailsRecord(0,{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->assigned_company }}</td> 
                                    @else
                                        <td class="title"> 
                                            @if ($item->hr_admin_flg ==1)
                                            <input type="checkbox"  checked="checked" value="{{ $item->id }}" 
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                            @else
                                            <input type="checkbox" value="{{ $item->id }}"
                                                name="cids[]" class="cid" onClick="isChecked(this.checked)" />
                                            @endif
                                        </td>
                                        <td ng-click="detailsRecord({{ $item->hr_info_id}},{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ \App\Http\Utils\AppUtils::HR_ADMIN_FLG[$item->hr_admin_flg] }}</td>
                                        <td ng-click="detailsRecord({{ $item->hr_info_id}},{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->user_name.'('.$item->email.')' }}</td>
                                        <td ng-click="detailsRecord({{ $item->hr_info_id}},{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->department_name }}</td>
                                        <td ng-click="detailsRecord({{ $item->hr_info_id}},{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->position_name }}</td>
                                        <td ng-click="detailsRecord({{ $item->hr_info_id}},{{ $item->id}},{{ $item->hr_admin_flg}})" class="title">{{ $item->assigned_company }}</td>

                                    @endif
                                    <td ng-show="false" class="title">{{ $item->hr_info_id }}</td>
                                    <td ng-show="false" class="title">{{ $item->id }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                @if ($arrHistory)
                    @include('layouts.table_footer',['data' => $arrHistory])
                @endif
            </div>
        </div>
        <% boxchecked %>
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

                    $rootScope.$emit("showMocalConfirm", {
                        title:'選択されたユーザーを管理対象ユーザーにします。', 
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax_update_hradminflg, { cidsoff:cidsoff,cids: cids })
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

                $scope.detailsRecord = function(hr_info_id,id,hr_admin_flg){
                    // 子画面を表示する（但し、管理ユーザのみ）
                    if (hr_admin_flg == '{{ \App\Http\Utils\AppUtils::HR_ADMIN}}') 
                    {
                        $rootScope.$emit("openDetailUsers",{hr_info_id:hr_info_id,id:id});
                    }
                };
            });
        }
        $("#modalDetailItem").on('hide.bs.modal', function () {
             // 子画面で変更があった場合はリロードを実施
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
