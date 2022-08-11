<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;" >
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!dispatcharea_id">派遣先情報登録</h4>
                        <h4 class="modal-title" ng-show="dispatcharea_id">派遣先情報更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <ul class="nav nav-tabs">
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('会社', 'company') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('派遣先', 'dispatchArea') !!}
                    </ul>
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="tab-content">
                            <div class="tab-pane" id="company"
                                ng-class="{active: showTab =='company', fade: showTab !='company' }">

                                <div class="card">
                                    <div class="card-header">会社詳細</div>
                                    <div class="card-body">
                                        <div class="text-right">
                                            @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE], [PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_UPDATE])
                                        　　<div class="btn btn-success" ng-click="selectAgency()" ng-if="!readonly"><i class="fas fa-plus-circle" ></i> 選択</div>
                                            @endcanany
                                        </div> 
                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('会社名', 
                                            ['id'=>'company_name', 'ng-bind'=>'agency.company_name']) !!}   

                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('事業所名', 
                                            ['id'=>'office_name', 'ng-bind'=>'agency.office_name']) !!}   

                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('事業所抵触日', 
                                            ['id'=>'conflict_date', 'ng-bind'=>'agency.conflict_date']) !!}   

                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('郵便番号', 
                                            ['id'=>'al_postal_code', 'ng-bind'=>'agency.postal_code']) !!}   
                                            
                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('住所', 
                                            ['id'=>'al_address1', 'ng-bind'=>'agency.address1']) !!}   
 
                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('ビルなど', 
                                            ['id'=>'al_address2', 'ng-bind'=>'agency.address2']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::dispOnlyLabel('請求先部署', 
                                            ['id'=>'billing_address', 'ng-bind'=>'agency.billing_address']) !!}   

                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="dispatchArea"
                                ng-class="{active: showTab =='dispatchArea', fade: showTab !='dispatchArea' }">

                                <div class="card">
                                    <div class="card-header">派遣先詳細</div>
                                    <div class="card-body">
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '部署名', 'department', true ,
                                            [ 'id'=>'department', 'ng-model'=>'dispatcharea.department', 'placeholder' =>'部署名', 'maxlength' => '128' ]) !!}
            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '部署長役職', 'position', false ,
                                            [ 'id'=>'position', 'ng-model'=>'dispatcharea.position', 'placeholder' =>'部署長役職', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailDate('部署抵触日', false ,
                                            [ 'id'=>'department_date', 'ng-model'=>'dispatcharea.department_date', 'placeholder'=>'yyyy/MM/dd']) !!}
    
                                        {!! \App\Http\Utils\DispatchUtils::showDetailPostal('郵便番号', 'postal_code', false ,
                                            [ 'id'=>'postal_code', 'ng-model'=>'dispatcharea.postal_code', 'placeholder' =>'000-0000', 'maxlength' => '10', 'ng-change'=>'getAddress()' ]) !!}
                                            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '住所', 'address1', false ,
                                            [ 'id'=>'address1', 'ng-model'=>'dispatcharea.address1', 'placeholder' =>'住所', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'ビルなど', 'address2', false ,
                                            [ 'id'=>'address2', 'ng-model'=>'dispatcharea.address2', 'placeholder' =>'ビルなど', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '代表電話番号', 'main_phone_no', false ,
                                            [ 'id'=>'main_phone_no', 'ng-model'=>'dispatcharea.main_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '携帯電話番号', 'mobile_phone_no', false ,
                                            [ 'id'=>'mobile_phone_no', 'ng-model'=>'dispatcharea.mobile_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'FAX番号', 'fax_no', false ,
                                            [ 'id'=>'fax_no', 'ng-model'=>'dispatcharea.fax_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('email', 'メールアドレス', 'email', false ,
                                            [ 'id'=>'email', 'ng-model'=>'dispatcharea.email', 'placeholder' =>'email@example.com', 'maxlength' => '256' ]) !!}
                                            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣先責任者', 'responsible_name', false ,
                                            [ 'id'=>'responsible_name', 'ng-model'=>'dispatcharea.responsible_name', 'placeholder' =>'派遣先責任者', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣先責任者電話番号', 'responsible_phone_no', false ,
                                            [ 'id'=>'responsible_phone_no', 'ng-model'=>'dispatcharea.responsible_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '指揮命令者', 'commander_name', false ,
                                            [ 'id'=>'commander_name', 'ng-model'=>'dispatcharea.commander_name', 'placeholder' =>'指揮命令者', 'maxlength' => '128' ]) !!}
	
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '指揮命令者電話番号', 'commander_phone_no', false ,
                                            [ 'id'=>'commander_phone_no', 'ng-model'=>'dispatcharea.commander_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
			
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '苦情申出先', 'troubles_name', false ,
                                            [ 'id'=>'troubles_name', 'ng-model'=>'dispatcharea.troubles_name', 'placeholder' =>'苦情申出先', 'maxlength' => '128' ]) !!}
	            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '苦情申出先電話番号', 'troubles_phone_no', false ,
                                            [ 'id'=>'troubles_phone_no', 'ng-model'=>'dispatcharea.troubles_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailCheckBoxOtherText('派遣時の休日', 'holiday' , 'holidayothertext', false , $code_holiday, 'dispatcharea.dispatcharea_holiday', [],
                                            'holidayother', 'その他','dispatcharea.dispatcharea_holiday_other',
                                            ['id'=>'holidayothertext', 'ng-model'=>'dispatcharea.dispatcharea_holiday_other.text', 'placeholder'=>'その他', 'maxlength'=>'128']); !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailCheckBoxOtherText('派遣時の福利厚生', 'welfare' , 'welfare_othertext', false , $code_welfare, 'dispatcharea.welfare_kbn', [],
                                            'welfareother', 'その他','dispatcharea.welfare_other',
                                            ['id'=>'welfare_othertext', 'ng-model'=>'dispatcharea.welfare_other.text', 'placeholder'=>'その他', 'maxlength'=>'128']); !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '特別条項', 'separate_clause', false ,
                                            [ 'id'=>'separate_clause', 'ng-model'=>'dispatcharea.separate_clause', 'placeholder' =>'特別条項', 'maxlength' => '256' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '備考', 'remarks', false ,
                                            [ 'id'=>'remarks', 'ng-model'=>'dispatcharea.remarks', 'placeholder' =>'備考', 'maxlength' => '256' ]) !!}
			                            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailRadio('1円未満端数処理', 'fraction' , false , $code_fraction,
                                            [ 'name' => 'fraction', 'ng-model' => 'dispatcharea.fraction_type' ]) !!}                              		
        
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'その他メモ', 'memo', false ,
                                            [ 'id'=>'memo', 'ng-model'=>'dispatcharea.memo', 'placeholder' =>'その他メモ', 'maxlength' => '256' ]) !!}
			                                        
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '担当事業所', 'manager_office_name', false ,
                                            [ 'id'=>'manager_office_name', 'ng-model'=>'dispatcharea.manager_office_name', 'placeholder' =>'担当事業所', 'maxlength' => '256' ]) !!}
			
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '担当者', 'manager_name', false ,
                                            [ 'id'=>'manager_name', 'ng-model'=>'dispatcharea.manager_name', 'placeholder' =>'担当者', 'maxlength' => '128' ]) !!}
		
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '注意事項', 'caution', false ,
                                            [ 'id'=>'caution', 'ng-model'=>'dispatcharea.caution', 'placeholder' =>'注意事項', 'maxlength' => '256' ]) !!}
                                        
                                        {!! \App\Http\Utils\DispatchUtils::showDetailCheckBox('取引ステータス', 'status' , false , $code_status, 'dispatcharea.status_kbn', []); !!}
        	
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣先からの評価', 'evaluation', false ,
                                            [ 'id'=>'evaluation', 'ng-model'=>'dispatcharea.evaluation', 'placeholder' =>'派遣先からの評価', 'maxlength' => '256' ]) !!}
		
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <ng-template ng-show="!dispatcharea_id">
                            @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="saveDispatchArea()" ng-disabled="agency.id == ''">
                                <i class="far fa-save"></i> 登録
                            </button>
                            @endcanany
                        </ng-template>
                        <ng-template ng-show="dispatcharea_id">
                            @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="saveDispatchArea()">
                                <i class="far fa-save"></i> 更新
                            </button>
                            @endcanany
                        </ng-template>

                        @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_DELETE])
                        <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="dispatcharea_id">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
                        @endcanany

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
    <form class="form_edit_agency" action="" method="" onsubmit="return false;">
        <div class="modal modal-child" id="modalDetailAgency" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!agency_id">会社登録</h4>
                        <h4 class="modal-title" ng-show="agency_id">会社更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '会社名', 'ag_company_name', true ,
                            [ 'id'=>'ag_company_name', 'ng-model'=>'agency_regist.company_name', 'placeholder' =>'会社名', 'maxlength' => '128' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '事業者名', 'ag_office_name', false ,
                            [ 'id'=>'ag_office_name', 'ng-model'=>'agency_regist.office_name', 'placeholder' =>'事業者名', 'maxlength' => '128' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailDate('事業所抵触日', false ,
                            [ 'id'=>'ag_conflict_date', 'ng-model'=>'agency_regist.conflict_date', 'placeholder'=>'yyyy/MM/dd']) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailPostal('郵便番号', 'ag_postal_code', false ,
                            [ 'id'=>'ag_postal_code', 'ng-model'=>'agency_regist.postal_code', 'placeholder' =>'000-0000', 'maxlength' => '10', 'ng-change'=>'getAddressAgency()' ]) !!}
                                            
                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '住所', 'ag_address1', false ,
                            [ 'id'=>'ag_address1', 'ng-model'=>'agency_regist.address1', 'placeholder' =>'住所', 'maxlength' => '128' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'ビルなど', 'ag_address2', false ,
                            [ 'id'=>'ag_address2', 'ng-model'=>'agency_regist.address2', 'placeholder' =>'ビルなど', 'maxlength' => '128' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '請求先部署', 'billing_address', false ,
                            [ 'id'=>'billing_address', 'ng-model'=>'agency_regist.billing_address', 'placeholder' =>'請求先部署', 'maxlength' => '10' ]) !!}
                     
                    </div>

                    <div class="modal-footer">
                        <ng-template ng-show="!agency_id">
                            @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="saveAgency()">
                                <ng-template><i class="far fa-save"></i> 登録</ng-template>
                            </button>
                            @endcanany
                        </ng-template>
                        <ng-template ng-show="agency_id">
                            @canany([PermissionUtils::PERMISSION_DISPATCHAREA_SETTING_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="saveAgency()">
                                <ng-template><i class="far fa-save"></i> 更新</ng-template>
                            </button>
                            @endcanany
                        </ng-template>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </form>
    <form class="form_select_agency" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail modal-child" id="modalSelectAgency" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">会社選択</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="form-group row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('会社名', ['cols'=>3]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'scag_company_name', ['cols'=>9],
                            ['placeholder' =>'会社名（部分一致）', 'id'=>'scag_company_name', 'ng-model'=>'search.scag_company_name', 'maxlength' => '256']); !!}
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-md-3 col-sm-3 col-12 text-right-lg">会社登録日</label>
                            <div class="col-md-9 col-sm-9 col-12">
                                <div class="input-group">
                                    <div class="col-md-5" style="padding-left:0px;">
                                        <input type="Date" name="scagsl_fromdate" value="{{ Request::get('scagsl_fromdate', '') }}" 
                                        class="form-control" placeholder="yyyy/mm/dd" id="scagsl_fromdate" ng-model="search.scagsl_fromdate">
                                    </div>
                                    <label for="name" class="col-md-1 control-label">~</label>
                                    <div class="col-md-5" style="padding-left:0px;">
                                        <input type="date" name="scagsl_todate" value="{{ Request::get('scagsl_todate', '') }}" 
                                        class="form-control" placeholder="yyyy/mm/dd" id="scagsl_todate" ng-model="search.scagsl_todate">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-primary mb-1" type="submit" ng-click="clickSearch()"><i class="fas fa-search" ></i> 検索</button>
                        </div>

                        <div ng-if="showSchAgency" class="card mt-3">
                            <div class="card-header">会社一覧</div>
                            <div class="card-body">
                                <span class="clear"></span>
                                {!! \App\Http\Utils\DispatchUtils::showDispNumber(
                                            [ 'ng-model' =>'sch_paginate.per_page', 'ng-change'=>'searchAgency(1,sch_paginate.per_page)', 'ng-options'=>'option for option in option_limit track by option']) !!}                    
                   
                                <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist margin-top-5">
                                    <thead>
                                        <tr>
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('会社名', 'company_name', true) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('事業所名', 'office_name', true) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('登録日', 'create_at', true) !!}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="(key, agency) in sch_agencies">
                                            <td ng-click="selectedAgency(agency)"><% agency.company_name %></td>
                                            <td ng-click="selectedAgency(agency)"><% agency.office_name  %></td>
                                            <td ng-click="selectedAgency(agency)" style="width:95px;"><% agency.disp_create_at  %></td>
                                        </tr>
                                    </tbody>
                                </table>
                                {!! \App\Http\Utils\DispatchUtils::showPaginate('sch_paginate', 'changeDetailPage') !!}                    

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <i class="fas fa-times-circle"></i> 閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {              
                $scope.agency_id = 0;
                $scope.dispatcharea_id = 0;
                $scope.agency = {};
                $scope.option_limit = [20,50,100];
                $scope.sch_agencies={};
                if(!$scope.showTab) $scope.showTab = 'company';

                $scope.search = {
                    scag_company_name:"", 
                    scagsl_todate:"", 
                    scagsl_fromdate: "",
                };
                $scope.search_bk = {
                    scag_company_name:"", 
                    scagsl_todate:"", 
                    scagsl_fromdate: "",
                }
                $scope.sch_paginate = {
                    start_page:1,
                    end_page:10,
                    total:0,
                    current_page:0,
                    per_page:20,
                    last_page:0,
                    from:0,
                    to:0,
                };

                $scope.showSchAgency = false;
                $scope.dispatcharea= {};
                $scope.agency_regist={};
                $scope.orderBy = 'create_at';
                $scope.orderDir = 'desc';
                $rootScope.$on("openNewDispatchArea", function(event){
                    $scope.dispatcharea_id = 0;
                    $scope.showTab = 'company';
                    if(allow_create) $scope.readonly = false;
                    else $scope.readonly = true;
                    $scope.agency = setAgencyDefault();
                    $scope.dispatcharea = setDispatchAreaDefault();
                    $scope.agency_regist = setAgencyRegist();
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openNewAgency", function(event){
                    $scope.agency_id = 0;
                    if(allow_create) $scope.readonly = false;
                    else $scope.readonly = true;
                    $scope.agency = setAgencyDefault();
                    $scope.agency_regist = setAgencyRegist();

                    $scope.editAgency();

                });
                $rootScope.$on("openEditAgency", function(event, data){
                    $scope.agency_id = data.id;
                    if(allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    $scope.agency = setAgencyDefault();
                    $scope.agency_regist = setAgencyRegist();
                    hasChange = false;
                    $http.post(link_agency_search, {
                        id: $scope.agency_id,
                        })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailAgency .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.agency_regist = event.data.agency; 
                                if ($scope.agency_regist.conflict_date)$scope.agency_regist.conflict_date = new Date($scope.agency_regist.conflict_date);                              
                            }
                        });

                    $scope.editAgency();

                });
                $rootScope.$on("openEditDispatchArea", function(event, data){
                    $rootScope.$emit("showLoading");
                    $scope.dispatcharea_id = data.id;
                    if(allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    $scope.showTab = 'company';
                    hideMessages();
                    hasChange = false;
                    $http.post(link_dispatcharea_geteditdata, {
                        id: $scope.dispatcharea_id,
                        })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.agency = event.data.agency;
                                $scope.dispatcharea = event.data.dispatcharea;
                                if ($scope.dispatcharea.department_date)$scope.dispatcharea.department_date = new Date($scope.dispatcharea.department_date);                              
                                
                            }
                        });
                    $("#modalDetailItem").modal();
                });
                $scope.changeDetailPage = function(page){

                    if(page == $scope.sch_paginate.current_page || page < 1 || page > $scope.sch_paginate.last_page){
                        return;
                    }
                    $scope.searchAgency(page);
                };
                $scope.clickSearch = function(page, limit){
                    $scope.orderBy = 'create_at';
                    $scope.orderDir = 'desc';
                    $scope.searchAgency();
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');

                }
                $scope.searchAgency = function(page, limit){                    
                    $scope.showSchAgency = true;
                    $rootScope.$emit("showLoading");
                    var $limit = 20;
                    if (limit) $limit = limit; 
                    setDateParam();
                    $scope.search_item = $scope.search;
                    if (!page){
                        $scope.search_bk = JSON.parse(JSON.stringify($scope.search));
                    }else{
                        $scope.search_item = $scope.search_bk;
                    }
                    $http.post(link_agency_search, {
                        items: $scope.search_item,
                        page:page,
                        limit:$limit,
                        orderBy:$scope.orderBy,
                        orderDir:$scope.orderDir,
                    })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalSelectAgency .message-infoe").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.sch_agencies = event.data.items.data;
                                var current_page = event.data.items.current_page;
                                var last_page = event.data.items.last_page;
                                var start_page = last_page;
                                var end_page = current_page;
                                if (last_page - current_page <= 9) {
                                    if (current_page > 0) {
                                        start_page = 0;
                                    }
                                }else{
                                    if (last_page - 10 > current_page){
                                        start_page = current_page;
                                        last_page = current_page + 9;
                                    }
                                }
                                if (event.data.items.from === null) event.data.items.from = 1;
                                if (event.data.items.to === null) event.data.items.to = 0;
                                $scope.sch_paginate ={
                                    start_page:start_page,
                                    end_page:end_page,
                                    total:event.data.items.total,
                                    current_page:event.data.items.current_page,
                                    per_page:event.data.items.per_page,
                                    last_page:event.data.items.last_page,
                                    from:event.data.items.from,
                                    to:event.data.items.to,
                                    dispnumber:event.data.items.total+' 件中 '+event.data.items.from+' 件から '+event.data.items.to+' 件までを表示',
                                }

                            }
                        });

                };
                $scope.selectedAgency = function(data){
                    $rootScope.$emit("showLoading");
                    $scope.agency = data;
                    $rootScope.$emit("hideLoading");
                    $("#modalSelectAgency").modal('hide');
                };
                $scope.saveAgency = function(callSuccess){
                    if($(".form_edit_agency")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){

                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailAgency .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{   
                                $("#modalDetailAgency .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                                if(callSuccess) callSuccess();
                                $("#modalDetailAgency").modal('hide');
                            }
                        };
                        $http.post(link_agency_save, {item: $scope.agency_regist})
                                .then(saveSuccess);
                        if(!$scope.agency.id){
                        }else{
                        }
                    }else{
                        $(".form_edit_agency")[0].reportValidity()
                    }

                };
                $scope.saveDispatchArea = function(callSuccess){
                    $scope.showTab = 'dispatchArea';
                    if($(".form_edit")[0].checkValidity()) {

                        hideMessages();
                        $rootScope.$emit("showLoading");

                        var saveSuccess = function(event){
                            $rootScope.$emit("hideLoading");

                            if(event.data.status == false){
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                                if(callSuccess) callSuccess();
                                $("#modalDetailItem").modal('hide');
                            }
                        }
                        $scope.dispatcharea.dispatcharea_agency_id = $scope.agency.id;
                       
                        $http.post(link_dispatcharea_save, {item: $scope.dispatcharea})
                                .then(saveSuccess);
                    }

                };

                $scope.remove = function(){
                    var cids = [];
                    cids.push($scope.dispatcharea_id);

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'派遣先を削除します。よろしいですか？',
                            btnDanger:'削除',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_dispatcharea_deletes, { cids: cids})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            location.reload();
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });

                };

                $scope.editAgency = function(){
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailAgency").modal();
                };
                $scope.selectAgency = function(){
                    hideMessages();
                    hasChange = false;
                    $("#modalSelectAgency").modal();
                };
                $scope.detailAgency = function(){

                    hideMessages();
                    hasChange = false;
                    if( $scope.isShowAgency == true ){
                        $("#iconDetailAgency").removeClass('fa-caret-square-up');
                        $("#iconDetailAgency").addClass('fa-caret-square-down');
                        $("#agencydetail").addClass('detail_none');
                    }else{
                        $("#iconDetailAgency").removeClass('fa-caret-square-down');
                        $("#iconDetailAgency").addClass('fa-caret-square-up');
                        $("#agencydetail").removeClass('detail_none');
                    }
                    $scope.isShowAgency = !$scope.isShowAgency;
                };

                $scope.getAddress=function(){
                    $scope.dispatcharea.address1=""
                    $http({
                        method:'get',
                        url:'setting-user/get-address',
                        params:{zipcode:$scope.dispatcharea.postal_code}
                    }).then(function (event){
                        let res=event.data.results;
                        if (res!=null){
                            $scope.dispatcharea.address1=res[0].address1+res[0].address2+res[0].address3
                        }

                    })
                }
                $scope.getAddressAgency=function(){
                    $scope.agency_regist.address1=""
                    $http({
                        method:'get',
                        url:'setting-user/get-address',
                        params:{zipcode:$scope.agency_regist.postal_code}
                    }).then(function (event){
                        let res=event.data.results;
                        if (res!=null){
                            $scope.agency_regist.address1=res[0].address1+res[0].address2+res[0].address3
                        }

                    })
                }
                $scope.range_func = function(n) {
                    return new Array(n);
                };

                $scope.changeSort = function(orderBy, addId=""){

                    if($scope.orderBy == orderBy){
                        $scope.orderDir = $scope.orderDir == 'asc'?'desc':'asc';
                    }
                    $scope.orderBy = orderBy;
                    $scope.searchAgency(1, $scope.sch_paginate.per_page);
                 
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $(".table-sort-client .sort-column."+orderBy).addClass('active');
                    if($scope.orderDir == 'asc')
                        $(".table-sort-client .sort-column."+orderBy).addClass('active-up');
                    else $(".table-sort-client .sort-column."+orderBy).addClass('active-down');

                };

                function setDateParam(){
                    if (!$scope.search.scagsl_fromdate) $scope.search.scagsl_fromdate="";
                    if (!$scope.search.scagsl_todate) $scope.search.scagsl_todate="";
                }
            })
        }
        function setAgencyDefault(){
            return {
                id:0, 
                company_name:"", 
                office_name:"", 
                conflict_date:"", 
                postal_code:"",
                address1:"",
                address2:"",
                billing_address:"",
            };
        }
        function setAgencyRegist(){
            return {
                id:0, 
                company_name:"", 
                office_name:"", 
                conflict_date:"", 
                postal_code:"",
                address1:"",
                address2:"",
                billing_address:"",
            };
        }
        function setDefaultCheckbox($code_data){
            const item = [];
            $code_data.forEach(function (value, index) {
                const work = {};
                work['checked']= false;
                work['id']= value.id;
                item.push(work);
            });
            return item;
        }
        function setDefaultRadio($code_data){
            var item = 0;
            $code_data.forEach(function (value, index) {
                if(index===0) item = value.id;
            });   
            return item;
        }        
        function setDispatchAreaDefault(){
            const holidays = setDefaultCheckbox({!! json_encode($code_holiday) !!});
            const welfares = setDefaultCheckbox({!! json_encode($code_welfare) !!});
            const statuses = setDefaultCheckbox({!! json_encode($code_status) !!});
            const fraction = setDefaultRadio({!! json_encode($code_fraction) !!});
            return {
                id:0,
                dispatcharea_agency_id:"",
                department:"",
                position:"",
                department_date:"",
                postal_code:"",
                address1:"",
                address2:"",
                main_phone_no:"",
                mobile_phone_no:"",
                fax_no:"",
                email:"",
                responsible_name:"",
                responsible_phone_no:"",
                commander_name:"",
                commander_phone_no:"",
                troubles_name:"",
                troubles_phone_no:"",
                dispatcharea_holiday:holidays,
                dispatcharea_holiday_other:{
                    checked:0,
                    text:""
                },
                welfare_kbn:welfares,
                welfare_other:{
                    checked:0,
                    text:""
                },
                separate_clause:"",
                remarks:"",
                fraction_type:fraction,
                memo:"",
                manager_name:"",
                manager_office_name:"",
                caution:"",
                status_kbn:statuses,
                evaluation:""
            };
        }

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
    .detail_none{
        display:none;
        }
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