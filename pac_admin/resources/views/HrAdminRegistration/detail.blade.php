<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!item.hr_info_id">管理スタッフ更新</h4>
                        <h4 class="modal-title" ng-if="item.hr_info_id">管理スタッフ更新</h4>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div ng-controller="ListController">
                            <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminDetileForm" method="POST">
                                @csrf
                                <div class="form-search form-vertical">
                                    <div class="row">

                                        <div class="col-lg-3">
                                            {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('dt_email','メールアドレス',Request::get('dt_email', ''),'text', false, 
                                            [ 'placeholder' =>'メールアドレス', 'id'=>'dt_email' ]) !!}
                                        </div>
                                        <div class="col-lg-3">
                                            {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('dt_username','氏名',Request::get('dt_username', ''),'text', false, 
                                            [ 'placeholder' =>'氏名', 'id'=>'dt_username' ]) !!}
                                        </div>
                                        <div class="col-lg-3 form-group">
                                            <label class="control-label" >部署</label>
                                            {!! \App\Http\Utils\CommonUtils::buildDepartmentSelect($listDepartmentTree, 'dt_department', Request::get('dt_department', ''),'',['class'=> 'form-control']) !!}
                                        </div>
                                        <div class="col-lg-3 form-group">
                                            <label class="control-label" >役職</label>
                                            {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'dt_position', Request::get('dt_position', ''),'',['class'=> 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 form-group">
                                            {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('dt_assignedcompany','配置現場名',Request::get('dt_assignedcompany', ''),'text', false, 
                                            [ 'placeholder' =>'配置現場名', 'id'=>'dt_assignedcompany' ]) !!}
                                        </div>
                                        <div class="col-lg-3"></div>
                                        <div class="col-lg-4"></div>
                                        <div class="col-lg-2 text-right padding-top-20">                                           
                                            <button class="btn btn-primary mb-1" type="submit" ng-click="clickSearch()"><i class="fas fa-search" ></i> 検索</button>
                                        </div>
                                    </div>          
                                </div>

                                <div class="message message-detail-list mt-3"></div>

                                <div ng-if="showSchUsersArea" class="card mt-3">
                                    <div class="card-body">
	                                    <div class="table-head">
	                                        <div class="form-group">
	                                            <div class="row">
	                                                <div class="col-lg-4">
                                                        @canany([PermissionUtils::PERMISSION_HR_ADMIN_SETTING_UPDATE])
                                                            <button class="btn btn-success m-0" ng-click="approvalDetail($event)">一括管理スタッフ更新</button>
                                                        @endcanany
	                                                </div>
	                                            </div>
	                                        </div>
	                                    </div>
                                                      
                                        <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5 searchUsersArea table-sort-client ">
                                            <thead>
                                                <tr>                            
                                                    <th class="title sort" scope="col" data-tablesaw-priority="persist2">
                                                        <input type="checkbox" onClick="checkDetileAll(this.checked)" ng-click="toogleCheckAll()" />
                                                    </th>
                                                    <th scope="col"  class="sort">
                                                        {!! \App\Http\Utils\CommonUtils::showNgSortColumn('氏名', 'dtlst_username') !!}
                                                    </th>
                                                    <th scope="col"  class="sort">
                                                        {!! \App\Http\Utils\CommonUtils::showNgSortColumn('部署', 'dtlst_department') !!}
                                                    </th>
                                                    <th scope="col"  class="sort">
                                                        {!! \App\Http\Utils\CommonUtils::showNgSortColumn('役職', 'dtlst_position') !!}
                                                    </th>
                                                    <th scope="col"  class="sort">
                                                        {!! \App\Http\Utils\CommonUtils::showNgSortColumn('配置現場名', 'dtlst_assignedCompany') !!}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>                                                
                                                <tr ng-repeat="(key, area) in detailUsersItems" class="row-<%area.id%> row-edit" ng-class="{ edit: id == <%area.id%> }"> 
                                                    <td class="title">
                                                        <input type="checkbox"  checked="checked" value="<%area.id%>" name="cidDetiles[]" 
                                                            class="cidDetile" onClick="isChecked(this.checked)" ng-if="area.del_flg == '1'" />
                                                        <input type="checkbox"  value="<%area.id%>" name="cidDetiles[]" 
                                                            class="cidDetile" onClick="isChecked(this.checked)" ng-if="area.del_flg != '1'" />                                                
                                                        <td class="title"><%area.dtlst_username%>(<%area.dtlst_email%>)</td>
                                                        <td class="title"><%area.department_name%></td>
                                                        <td class="title"><%area.position_name%></td>
                                                        <td class="title"><%area.assigned_company%></td>
                                                    </td>
                                                    <td ng-show="false" class="title"><%area.hr_info_id%></td>
                                                    <td ng-show="false" class="title"><%area.id%></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="mt-3" style="text-"><%page_info.total%> 件中  <% page_info.from || '0' %> 件から  <% page_info.to || '0' %> 件までを表示</div>                                
                                        {!! \App\Http\Utils\CommonUtils::showNgPaginate('page_info', 'changeDetailPage') !!}     
                                    </div>
                                    <% boxchecked %>
                                    <input type="hidden" id="dt_orderBy" value="<%page_info.orderBy%>" name="dt_orderBy" />
                                    <input type="hidden" id="dt_orderDir" value="<%page_info.orderDir%>" name="dt_orderDir" /> 
                                </div>
                                <input type="hidden" id="dt_user_id" value="{{ $dt_user_id }}" name="dt_user_id" /> 
                            </form>
                            <!-- Modal footer -->
                            <div class="modal-footer">            
                                <button type="button" class="btn btn-default" data-dismiss="modal" ng-click="cancelAttendance()">
                                    <i class="fas fa-times-circle"></i> 閉じる
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        
        // チェックボックスの全選択／全解除
		function checkDetileAll(isitchecked) {
			if (isitchecked) {
				$(".cidDetile:not([disabled])").prop('checked', true);
			} else {	 
				$(".cidDetile:not([disabled])").prop('checked', false);
			}
			return true;
		}

        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {

                $scope.item = {}; 
                $scope.orderBy_d  = 'create_at';
                $scope.orderDir_d = 'desc';       
                $scope.showSchUsersArea = false;

                // 親画面から呼び出されたときのイベント
                $rootScope.$on("openDetailUsers", function(event,data){
                
                    // 子画面の更新を検知するフラグ
                    hasChange = false;
                    
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;  
                    hideMessages();
                    $("#dt_user_id").val(data.id);  
                    $scope.orderBy_d = 'create_at';
                    $scope.orderDir_d = 'desc';
                    $scope.searchUsersArea();
                    $("#modalDetailItem").modal();
                }); 

                // 検索ボタン押下
                $scope.clickSearch = function(){
                    $scope.orderBy_d = 'create_at';
                    $scope.orderDir_d = 'desc';
                    $scope.searchUsersArea();
                };

                // 検索処理共通
                $scope.searchUsersArea = function(page){                    
                    $scope.showSchUsersArea = true;
                    $rootScope.$emit("showLoading"); 
                    $scope.search_item = $scope.search_d; 
                    $http.post(link_search_users, {
                        items: $scope.search_item,  
                        page:page,
                        dt_orderBy:$scope.orderBy_d,
                        dt_orderDir:$scope.orderDir_d,
                        dt_email:$("#dt_email").val(),
                        dt_username:$("#dt_username").val(),
                        dt_department:$("#dt_department").val(),
                        dt_position:$("#dt_position").val(),
                        dt_assignedcompany:$("#dt_assignedcompany").val(),
                        dt_user_id:$("#dt_user_id").val(), 
                    })
                    .then(function(event) {
                        $scope.detailUsersItems = event.data.items.data;
                        $scope.page_info = event.data.items;
                        $scope.order_info = event.config.data;
                        $rootScope.$emit("hideLoading"); 
                    });

                    // ソートアイコンの制御
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $(".table-sort-client .sort-column." + $scope.orderBy_d).addClass('active');
                    if ($scope.orderDir_d == 'asc'){
                        $(".table-sort-client .sort-column." + $scope.orderBy_d).addClass('active-up');
                    } else {
                        $(".table-sort-client .sort-column." + $scope.orderBy_d).addClass('active-down');
                    }
                };

                // 一括管理スタッフ更新ボタン
                $scope.approvalDetail = function(event){ 
                    event.preventDefault();
                    var cidsDetile = [];
                    var cidsDetileoff = [];

                    var hit = false;
                    //全てのチェックボックスを探索
                    for(var i =0; i < $(".cidDetile").length; i++){
                        var hit = false;
                        //チェックされたチェックボックスを探索
                        for(var i2 =0; i2 < $(".cidDetile:checked").length; i2++){
                            //一致していたら1更新ルート
                            if($(".cidDetile")[i].value == $(".cidDetile:checked")[i2].value){
                                cidsDetile.push($(".cidDetile")[i].value); 
                                hit = true;
                                break;
                            } 
                        }
                        //不一致の場合0更新ルート
                        if (!hit){
                            cidsDetileoff.push($(".cidDetile")[i].value); 
                        }
                    }
                    
                    $rootScope.$emit("showMocalConfirm", {
                        title:'選択されたスタッフを管理対象にします。', 
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax_update_users, {
                                dt_user_id:$("#dt_user_id").val(),
                                cidsDetile: cidsDetile,
                                cidsDetileoff:cidsDetileoff, 
                            })
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-detail-list").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-detail-list").append(showMessages(event.data.message, 'success', 10000));
                                    
                                    location.reload();
                                    
                                    // 子画面で更新があったことを親画面に伝える
                                    hasChange = true; 
                                }                        
                            });
                        }
                    });
                };

                // ソートボタン押下
                $scope.changeSort = function(orderBy){
                    // ソート対象が前回ソート対象のカラムと同じ場合、ソート順を逆転させる
                    if($scope.orderBy_d == orderBy){
                        $scope.orderDir_d = $scope.orderDir_d == 'asc'?'desc':'asc';
                    }else{
                        $scope.orderDir_d = "asc"
                    }
                    $scope.orderBy_d = orderBy; 
                    $scope.searchUsersArea(1); 

                    $("#dt_orderBy").val($scope.orderBy_d);
                    $("#dt_orderDir").val($scope.orderDir_d); 
                };

                // ページング制御
                $scope.range_func = function(n) {
                    return new Array(n);
                };

                // ページング制御
                $scope.changeDetailPage = function(page){
                    if(page == $scope.page_info.current_page || page < 1 || page > $scope.page_info.last_page){
                        return;
                    }
                    $scope.searchUsersArea(page);
                };
            });
        }
    </script>
@endpush

@push('styles_after')
    <style>
        .table-sort-client{ }
        .table-sort-client .sort{ }
        .table-sort-client .sort-column{ }
        .table-sort-client .sort-column .icon{ right: 5px; }
        .table-sort-client .sort-column .icon-up{ display: none; }
        .table-sort-client .sort-column .icon-down{ display: none; }
        .table-sort-client .sort-column.active{ }
        .table-sort-client .sort-column.active .icon{ display: none; }
        .table-sort-client .sort-column.active-up{ }
        .table-sort-client .sort-column.active-up .icon-up{ display: inline-block; }
        .table-sort-client .sort-column.active-down{ }
        .table-sort-client .sort-column.active-down .icon{ display: none; }
        .table-sort-client .sort-column.active-down .icon-down{ display: inline-block; }
    </style>
@endpush
