<div ng-controller="DetailController">

    <form class="form_edit" action="" method="" onsubmit="return false;" >


        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!contract_id">契約情報登録</h4>
                        <h4 class="modal-title" ng-show="contract_id">契約情報更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <ul class="nav nav-tabs">
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('管理用', 'detailAdmin') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('派遣先', 'dispatchArea') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('スタッフ', 'staff') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('派遣元', 'dispatchSource') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('計算項目', 'calculation') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('派遣条件', 'conditions') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('契約帳票', 'sheet') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTabItem('管理台帳', 'ledger') !!}
                    </ul>

                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="tab-content">
                            <div class="tab-pane" id="detailAdmin"
                                ng-class="{active: showTab =='detailAdmin', fade: showTab !='detailAdmin' }">
                                <div class="card">
                                    <div class="card-body" id="detailAdmin">
                                        {!! \App\Http\Utils\DispatchUtils::showDetailDateFromTo('派遣期間', true ,
                                            [ 'id'=>'contract_fromdate', 'ng-model'=>'contract.contract_fromdate', 'placeholder'=>'yyyy/MM/dd'],
                                            [ 'id'=>'contract_todate', 'ng-model'=>'contract.contract_todate', 'placeholder'=>'yyyy/MM/dd']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailSelect('紹介派遣か否かの別', false, $code_intro ,
                                            [ 'id'=>'intro_type', 'ng-model'=>'contract.intro_type']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailSelect('期間の定め', false, $code_period ,
                                            [ 'id'=>'period_type', 'ng-model'=>'contract.period_type']) !!}
                                            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailSelect('契約更新の有無', false, $code_update ,
                                            [ 'id'=>'contractupdate_type', 'ng-model'=>'contract.contractupdate_type']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('契約の更新の判断基準',false,
                                            ['id'=>'update_judgment', 'ng-model'=>'contract.update_judgment', 'placeholder' =>'契約の更新の判断基準', 'maxlength' => '256', 'rows'=>'2']); !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailDate('契約日(帳票用)', false ,
                                            [ 'id'=>'contract_date_sheet', 'ng-model'=>'contract.contract_date_sheet', 'placeholder'=>'yyyy/MM/dd']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailDateFromTo('基本契約期間', false ,
                                            [ 'id'=>'basiccontract_fromdate', 'ng-model'=>'contract.basiccontract_fromdate', 'placeholder'=>'yyyy/MM/dd'],
                                            [ 'id'=>'basiccontract_todate', 'ng-model'=>'contract.basiccontract_todate', 'placeholder'=>'yyyy/MM/dd']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('コメント',false,
                                            ['id'=>'comment', 'ng-model'=>'contract.comment', 'placeholder' =>'コメント', 'maxlength' => '256', 'rows'=>'2']); !!}

                                        </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="dispatchArea"
                                ng-class="{active: showTab =='dispatchArea', fade: showTab !='dispatchArea' }">
                                <div class="card">
                                    <div class="card-body" id="dispatchArea">
                                        <div class="text-right">
                                            @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE], [PermissionUtils::PERMISSION_CONTRACT_SETTING_UPDATE])
                                        　　<div class="btn btn-success" ng-click="selectDispatchArea()"><i class="fas fa-plus-circle" ></i> 選択</div>
                                            @endcanany
                                        </div>     
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣先名', 'dispatcharea_name', false ,
                                            [ 'id'=>'dispatcharea_name', 'ng-model'=>'contract.dispatcharea_name', 'placeholder' =>'派遣先名', 'maxlength' => '128' ]) !!}
                                    
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '事業所名', 'office_name', false ,
                                            [ 'id'=>'office_name', 'ng-model'=>'contract.office_name', 'placeholder' =>'事業所名', 'maxlength' => '128' ]) !!}
                                    
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣先事業所住所', 'office_address', false ,
                                            [ 'id'=>'office_address', 'ng-model'=>'contract.office_address', 'placeholder' =>'派遣先事業所住所', 'maxlength' => '256' ]) !!}
                                    
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('部署名') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'department', ['cols'=>4],
                                                    [ 'id'=>'department', 'ng-model'=>'contract.department', 'placeholder' =>'部署名', 'maxlength' => '128' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('部署長役職') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'position', ['cols'=>2],
                                                    [ 'id'=>'position', 'ng-model'=>'contract.position', 'placeholder' =>'部署長役職', 'maxlength' => '128' ]) !!}
                                            </div>
                                        </div>                            
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('就業先所在地') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'employment_address', ['cols'=>5],
                                                    [ 'id'=>'employment_address', 'ng-model'=>'contract.employment_address', 'placeholder' =>'就業先所在地', 'maxlength' => '256' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('TEL', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'employment_phone_no', ['cols'=>2],
                                                    [ 'id'=>'employment_phone_no', 'ng-model'=>'contract.employment_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('責任者') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'responsible_name', ['cols'=>5],
                                                    [ 'id'=>'responsible_name', 'ng-model'=>'contract.responsible_name', 'placeholder' =>'責任者', 'maxlength' => '128' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('TEL', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'responsible_phone_no', ['cols'=>2],
                                                    [ 'id'=>'responsible_phone_no', 'ng-model'=>'contract.responsible_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('指揮命令者') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'commander_name', ['cols'=>5],
                                                    [ 'id'=>'commander_name', 'ng-model'=>'contract.commander_name', 'placeholder' =>'指揮命令者', 'maxlength' => '128' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('TEL', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'commander_phone_no', ['cols'=>2],
                                                    [ 'id'=>'commander_phone_no', 'ng-model'=>'contract.commander_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('苦情処理の申出先') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'troubles_name', ['cols'=>5],
                                                    [ 'id'=>'troubles_name', 'ng-model'=>'contract.troubles_name', 'placeholder' =>'苦情処理の申出先', 'maxlength' => '128' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('TEL', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'troubles_phone_no', ['cols'=>2],
                                                    [ 'id'=>'troubles_phone_no', 'ng-model'=>'contract.troubles_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            </div>
                                        </div>

                                        {!! \App\Http\Utils\DispatchUtils::showDetailRadio('円端数処理', 'fraction' , false , $code_fraction,
                                            [ 'name' => 'fraction_type', 'ng-model' => 'contract.fraction_type' ]) !!}
                                    
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="staff"
                                ng-class="{active: showTab =='staff', fade: showTab !='staff' }">
                                <div class="card">

                                    <div class="card-body" id="staff">
                                        <div class="text-right">
                                            @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE], [PermissionUtils::PERMISSION_CONTRACT_SETTING_UPDATE])
                                        　　<div class="btn btn-success" ng-click="selectUser()"><i class="fas fa-plus-circle" ></i> 選択</div>
                                            @endcanany
                                        </div> 
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('氏名など') !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('人材登録番号') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'register_no', ['cols'=>2],
                                                    [ 'id'=>'register_no', 'ng-model'=>'contract.register_no', 'placeholder' =>'00000000000', 'maxlength' => '10' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('氏名',['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'name', ['cols'=>2],
                                                    [ 'id'=>'name', 'ng-model'=>'contract.name', 'placeholder' =>'氏名', 'maxlength' => '128' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('性別', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>2], $code_gender,
                                                    ['id'=>'gender_type', 'ng-model'=>'contract.gender_type']); !!}

                                            </div>
                                            <div class="row">
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('年齢') !!}
                                            {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'age', ['cols'=>1],
                                                [ 'id'=>'age', 'ng-model'=>'contract.age', 'placeholder' =>'99', 'maxlength' => '3' ]) !!}

                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('派遣人員') !!}
                                            {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'personnel', ['cols'=>2],
                                                [ 'id'=>'personnel', 'ng-model'=>'contract.personnel', 'placeholder' =>'00000/00000', 'maxlength' => '64' ]) !!}
                                            </div>                                        
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('社会保険') !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('加入日') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                    [ 'id'=>'socialinsurance_fromdate', 'ng-model'=>'contract.socialinsurance_fromdate', 'placeholder'=>'yyyy/MM/dd']) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('喪失日', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                    [ 'id'=>'socialinsurance_todate', 'ng-model'=>'contract.socialinsurance_todate', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('雇用保険') !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('加入日') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                    [ 'id'=>'employmentinsurance_fromdate', 'ng-model'=>'contract.employmentinsurance_fromdate', 'placeholder'=>'yyyy/MM/dd']) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('喪失日', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                    [ 'id'=>'employmentinsurance_todate', 'ng-model'=>'contract.employmentinsurance_todate', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            </div>
                                        </div>  
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('有期雇用') !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('契約雇入れ日') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                    [ 'id'=>'employmentcontract_date', 'ng-model'=>'contract.employmentcontract_date', 'placeholder'=>'yyyy/MM/dd']) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('転換日', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                    [ 'id'=>'employmentconversion_date', 'ng-model'=>'contract.employmentconversion_date', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            </div>
                                        </div>                                 
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('被保険者資格取得の有無',false,
                                            ['id'=>'insured_certification', 'ng-model'=>'contract.insured_certification', 'placeholder' =>'被保険者資格取得の有無', 'maxlength' => '256', 'rows'=>'2']); !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('雇用安定措置(管理台帳)',false,
                                            ['id'=>'employment_stabilization', 'ng-model'=>'contract.employment_stabilization', 'placeholder' =>'雇用安定措置(管理台帳)', 'maxlength' => '256', 'rows'=>'2']); !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('教育訓練の状況(管理台帳)',false,
                                            ['id'=>'education', 'ng-model'=>'contract.education', 'placeholder' =>'教育訓練の状況(管理台帳)', 'maxlength' => '256', 'rows'=>'2']); !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('キャリアコンサルティングの実施状況(管理台帳)',false,
                                            ['id'=>'career_consulting', 'ng-model'=>'contract.career_consulting', 'placeholder' =>'キャリアコンサルティングの実施状況(管理台帳)', 'maxlength' => '256', 'rows'=>'2']); !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('派遣先での教育訓練の状況(管理台帳)',false,
                                            ['id'=>'dispatch_education', 'ng-model'=>'contract.dispatch_education', 'placeholder' =>'派遣先での教育訓練の状況(管理台帳)', 'maxlength' => '256', 'rows'=>'2']); !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('苦情処理状況(管理台帳)',false,
                                            ['id'=>'troubles_ledger', 'ng-model'=>'contract.troubles_ledger', 'placeholder' =>'苦情処理状況(管理台帳)', 'maxlength' => '256', 'rows'=>'2']); !!}

                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="dispatchSource"
                                ng-class="{active: showTab =='dispatchSource', fade: showTab !='dispatchSource' }">
                                <div class="card">
                                    <div class="card-body" id="dispatchSource">
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '名称', 'dispatchsource_name', false ,
                                            [ 'id'=>'dispatchsource_name', 'ng-model'=>'contract.dispatchsource_name', 'placeholder' =>'名称', 'maxlength' => '128' ]) !!}
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('所在地') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'source_address', ['cols'=>5],
                                                    [ 'id'=>'source_address', 'ng-model'=>'contract.source_address', 'placeholder' =>'所在地', 'maxlength' => '256' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('TEL', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'source_phone_no', ['cols'=>2],
                                                    [ 'id'=>'source_phone_no', 'ng-model'=>'contract.source_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('責任者(帳票用)') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'source_responsible_name', ['cols'=>5],
                                                    [ 'id'=>'source_responsible_name', 'ng-model'=>'contract.source_responsible_name', 'placeholder' =>'責任者(帳票用)', 'maxlength' => '128' ]) !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('TEL', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'source_responsible_phone_no', ['cols'=>2],
                                                    [ 'id'=>'source_responsible_phone_no', 'ng-model'=>'contract.source_responsible_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                                            </div>
                                        </div>                                
            
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '事業許可番号', 'business_permit_no', false ,
                                            [ 'id'=>'business_permit_no', 'ng-model'=>'contract.business_permit_no', 'placeholder' =>'000000000000000', 'maxlength' => '15' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '苦情処理の申出先(帳票用)', 'troubles_sheet', false ,
                                            [ 'id'=>'troubles_sheet', 'ng-model'=>'contract.troubles_sheet', 'placeholder' =>'苦情処理の申出先(帳票用)', 'maxlength' => '128' ]) !!}

                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="calculation"
                                ng-class="{active: showTab =='calculation', fade: showTab !='calculation' }">
                                <div class="card">
                                    <div class="card-body" id="calculation">
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('勤務時間表設定値') !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('出社', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','attendance_time', ['cols'=>1],
                                                    ['id'=>'attendance_time', 'ng-model'=>'contract.attendance_time', 'placeholder' =>'99:99', 'maxlength' => '5']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('退社',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'leave_time', ['cols'=>1],
                                                    ['id'=>'leave_time', 'ng-model'=>'contract.leave_time', 'placeholder' =>'99:99', 'maxlength' => '5']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('休憩',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'break_time', ['cols'=>1],
                                                    ['id'=>'break_time', 'ng-model'=>'contract.break_time', 'placeholder' =>'99:99', 'maxlength' => '5']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('所定',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','predetermined_time', ['cols'=>1],
                                                    ['id'=>'predetermined_time', 'ng-model'=>'contract.predetermined_time', 'placeholder' =>'99:99', 'maxlength' => '5']); !!}
                                        </div>
                                        <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('残業基準',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','overtime', ['cols'=>1],
                                                    ['id'=>'overtime', 'ng-model'=>'contract.overtime', 'placeholder' =>'99:99', 'maxlength' => '5']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('有給日時間数',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','paid_time', ['cols'=>1],
                                                    ['id'=>'paid_time', 'ng-model'=>'contract.paid_time', 'placeholder' =>'99:99', 'maxlength' => '5']); !!}
                                            </div>
                                            {!! \App\Http\Utils\DispatchUtils::showDetailCheckBox('出勤曜日', 'week' , false , $code_week, 'contract.workdays', []); !!}
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('時間外労働制限') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('月最大時間外労働') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>2], $code_maxworkmonth,
                                                    ['id'=>'mmax_overtime', 'ng-model'=>'contract.mmax_overtime']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('最大週時間外労働') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>2], $code_maxworkweek,
                                                    ['id'=>'wmax_overtime', 'ng-model'=>'contract.wmax_overtime']); !!}
                                            </div>
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('最大週勤務日数') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>2], $code_maxweek,
                                                    ['id'=>'wmax_workday', 'ng-model'=>'contract.wmax_workday']); !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('請求・給与反映項目') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('請求計算締日') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>2], $code_deadlineprice,
                                                    ['id'=>'request_deadline', 'ng-model'=>'contract.request_deadline']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('給与計算締日') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>2], $code_deadlinewage,
                                                    ['id'=>'salary_deadline', 'ng-model'=>'contract.salary_deadline']); !!}
                                            </div>
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('時間丸め単位') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_round,
                                                    ['id'=>'time_round_unit', 'ng-model'=>'contract.time_round_unit']); !!}
                                            </div>
                                        </div> 
                                        
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('料金') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showRadio('pricetimeflat' , [] , $code_timeflat,
                                                    [ 'name' => 'price_timeflat_type', 'ng-model' => 'contract.price_timeflat_type']) !!}
                                            </div>
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('所定時間', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_predetermined_time', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_predetermined_time', 'ng-model'=>'contract.price_predetermined_time', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('時間外',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_overtime', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_overtime', 'ng-model'=>'contract.price_overtime', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('休日出勤',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_holiday', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_holiday', 'ng-model'=>'contract.price_holiday', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                  
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('法定休日出勤',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_legal_holiday', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_legal_holiday', 'ng-model'=>'contract.price_legal_holiday', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                    {!! \App\Http\Utils\DispatchUtils::setTitleLabel('法定内深夜',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_legal_midnight', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_legal_midnight', 'ng-model'=>'contract.price_legal_midnight', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                    {!! \App\Http\Utils\DispatchUtils::setTitleLabel('法定休日深夜',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_price_legal_holiday_midnight', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_price_legal_holiday_midnight', 'ng-model'=>'contract.price_price_legal_holiday_midnight', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('その他',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_other', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_other', 'ng-model'=>'contract.price_other', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                            </div>

                                            <div  class="row">
                                                <div class="input-group">
                                                    {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                    <label for="price_month_overtime_flg" class="control-label"><input type="checkbox" id="price_month_overtime_flg" ng-model="contract.price_month_overtime_flg.checked" ng-disabled="readonly" />１か月の時間外労働による割増料金を適用する。　</label>
                                                </div>
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('月60時間超過',  ['cols'=>2]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_month60', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_month60', 'ng-model'=>'contract.price_month60', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('月45時間超過',  ['cols'=>2]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','price_month45', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'price_month45', 'ng-model'=>'contract.price_month45', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                            </div>


                                        </div> 
                                        <div class="form-group">
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('賃金') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showRadio('wagetimeflat' , [] , $code_timeflat,
                                                    [ 'name' => 'wage_timeflat_type', 'ng-model' => 'contract.wage_timeflat_type' ]) !!}
                                            </div>
                                            <div class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('所定時間', ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_predetermined_time', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_predetermined_time', 'ng-model'=>'contract.wage_predetermined_time', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('時間外',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_overtime', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_overtime', 'ng-model'=>'contract.wage_overtime', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('休日出勤',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_holiday', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_holiday', 'ng-model'=>'contract.wage_holiday', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                  
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('法定休日出勤',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_legal_holiday', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_legal_holiday', 'ng-model'=>'contract.wage_legal_holiday', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                    {!! \App\Http\Utils\DispatchUtils::setTitleLabel('法定内深夜',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_legal_midnight', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_legal_midnight', 'ng-model'=>'contract.wage_legal_midnight', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                    {!! \App\Http\Utils\DispatchUtils::setTitleLabel('法定休日深夜',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_legal_holiday_midnight', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_legal_holiday_midnight', 'ng-model'=>'contract.wage_legal_holiday_midnight', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('その他',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_other', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_other', 'ng-model'=>'contract.wage_other', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                            </div>

                                            <div  class="row">
                                                <div class="input-group">
                                                    {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                    <label for="wage_month_overtime_flg" class="control-label"><input type="checkbox" id="wage_month_overtime_flg" ng-model="contract.wage_month_overtime_flg.checked" ng-disabled="readonly"/>１か月の時間外労働による割増料金を適用する。　</label>
                                                </div>
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('月60時間超過',  ['cols'=>2]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_month60', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_month60', 'ng-model'=>'contract.wage_month60', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('月45時間超過',  ['cols'=>2]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','wage_month45', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'wage_month45', 'ng-model'=>'contract.wage_month45', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                            </div>

                                        </div> 

                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane" id="conditions"
                                ng-class="{active: showTab =='conditions', fade: showTab !='conditions' }">
                                <div class="card">

                                    <div class="card-body" id="conditions">
                                        <div class="form-group">
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('就業時間など（帳票用）') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('就業形態',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>3],
                                                    ['id'=>'con_working_style', 'ng-model'=>'contract.con_working_style', 'rows'=>'3', 'placeholder' =>'就業形態', 'maxlength' => '256']); !!}

                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('就業時間',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>3],
                                                    ['id'=>'con_working_hours', 'ng-model'=>'contract.con_working_hours', 'rows'=>'3', 'placeholder' =>'就業時間', 'maxlength' => '256']); !!}

                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('休憩時間',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>3],
                                                    ['id'=>'con_break_time', 'ng-model'=>'contract.con_break_time', 'rows'=>'3', 'placeholder' =>'休憩時間', 'maxlength' => '256']); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('契約時間',  ['cols'=>1]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>3],
                                                    ['id'=>'con_contract_time', 'ng-model'=>'contract.con_contract_time', 'rows'=>'3', 'placeholder' =>'契約時間', 'maxlength' => '256']); !!}

                                            </div>
                                            

                                        </div>
                                        <div class="form-group">
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('業務内容') !!}
                                                {!! \App\Http\Utils\DispatchUtils::showCheckBoxVertical('business', 'contract.businesses', ['cols'=>5], $code_business); !!}
                                                
                                            </div>
                                        </div>
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('業務内容(詳細その他)',false,
                                            ['id'=>'business_other', 'ng-model'=>'contract.business_other', 'placeholder' =>'就業形態', 'maxlength' => '256', 'rows'=>'2']); !!}

                                        <div class="form-group">
                                            <div  class="row">
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('組織単位抵触日') !!}
                                            {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                [ 'id'=>'organization_date', 'ng-model'=>'contract.organization_date', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('帳票用',['cols'=>1]) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                [ 'id'=>'organization_date_sheet', 'ng-model'=>'contract.organization_date_sheet', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div  class="row">
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('事業所抵触日') !!}
                                            {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                [ 'id'=>'office_date', 'ng-model'=>'contract.office_date', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('帳票用',['cols'=>1]) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>3],
                                                [ 'id'=>'office_date_sheet', 'ng-model'=>'contract.office_date_sheet', 'placeholder'=>'yyyy/MM/dd']) !!}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('派遣期間制限について') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('無期雇用労働者または60歳以上ものに限定するか否かの別',['cols'=>3]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>5], $code_indefinite,
                                                    ['id'=>'dispatchperiod_type', 'ng-model'=>'contract.dispatchperiod_type']); !!}
                                            </div>
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('雇用期間の定め有無',['cols'=>3]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_yesno,
                                                    ['id'=>'employmentperiod_type', 'ng-model'=>'contract.employmentperiod_type']); !!}

                                            </div>
                                        </div>
                                        {!! \App\Http\Utils\DispatchUtils::showDetailDateFromTo('有期雇用派遣労働者－期間', false ,
                                            [ 'id'=>'employmentworker_fromdate', 'ng-model'=>'contract.employmentworker_fromdate', 'placeholder'=>'yyyy/MM/dd'],
                                            [ 'id'=>'employmentworker_todate', 'ng-model'=>'contract.employmentworker_todate', 'placeholder'=>'yyyy/MM/dd']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailSelect('無期雇用派遣労働者－理由', false, $code_indefinitereason ,
                                            [ 'id'=>'employmentworker_reason_type', 'ng-model'=>'contract.employmentworker_reason_type']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailSelect('無期雇用派遣労働者－詳細', false, $code_indefinitedetail ,
                                            [ 'id'=>'employmentworker_detail_type', 'ng-model'=>'contract.employmentworker_detail_type']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailDateFromTo('有期プロジェクト業務－期間', false ,
                                            [ 'id'=>'project_fromdate', 'ng-model'=>'contract.project_fromdate', 'placeholder'=>'yyyy/MM/dd'],
                                            [ 'id'=>'project_todate', 'ng-model'=>'contract.project_todate', 'placeholder'=>'yyyy/MM/dd']) !!}
	
                                        <div class="form-group">
                                            <div  class="row">
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('日数限定業務') !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('１か月間に行われる日数',  ['cols'=>2]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','monthly_days', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'monthly_days', 'ng-model'=>'contract.monthly_days', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}
                                                {!! \App\Http\Utils\DispatchUtils::setTitleLabel('派遣先の通常の労働者の１か月間の所定労働日数',  ['cols'=>2]) !!}
                                                {!! \App\Http\Utils\DispatchUtils::showInputText('text','monthly_workday', ['cols'=>2, 'textalign'=>'text-right'],
                                                    ['id'=>'monthly_workday', 'ng-model'=>'contract.monthly_workday', 'placeholder' =>'999', 'maxlength' => '3'], '/h'); !!}

                                            </div>
                                        </div>
	
	
                                        {!! \App\Http\Utils\DispatchUtils::showDetailDateFromTo('産前・産後・育児・介護休業業務－期間', false ,
                                            [ 'id'=>'closedwork_fromdate', 'ng-model'=>'contract.closedwork_fromdate', 'placeholder'=>'yyyy/MM/dd'],
                                            [ 'id'=>'closedwork_todate', 'ng-model'=>'contract.closedwork_todate', 'placeholder'=>'yyyy/MM/dd']) !!}

	
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '代替要員が必要となる業務労働者名','worker_name', false ,
                                            [ 'id'=>'worker_name', 'ng-model'=>'contract.worker_name', 'placeholder' =>'代替要員が必要となる業務労働者名', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '就業日の詳細', 'workday_detail', false ,
                                            [ 'id'=>'workday_detail', 'ng-model'=>'contract.workday_detail', 'placeholder' =>'就業日の詳細', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '就業日','workday', false ,
                                            [ 'id'=>'workday', 'ng-model'=>'contract.workday', 'placeholder' =>'就業日', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '料金支払条件','payment_terms', false ,
                                            [ 'id'=>'payment_terms', 'ng-model'=>'contract.payment_terms', 'placeholder' =>'料金支払条件', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '賃金支払条件','wage_payment_terms', false ,
                                            [ 'id'=>'wage_payment_terms', 'ng-model'=>'contract.wage_payment_terms', 'placeholder' =>'賃金支払条件', 'maxlength' => '256' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('派遣先契約内容その他（派遣契約書)', false ,
                                            [ 'id'=>'contract_other', 'ng-model'=>'contract.contract_other', 'placeholder' =>'派遣先契約内容その他（派遣契約書)', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('スタッフ契約内容その他（雇入通知書)	', false ,
                                            [ 'id'=>'staff_other', 'ng-model'=>'contract.staff_other', 'placeholder' =>'スタッフ契約内容その他（雇入通知書)', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('時間外労働', false ,
                                            [ 'id'=>'overtime_terms', 'ng-model'=>'contract.overtime_terms', 'placeholder' =>'時間外労働', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('休日労働', false ,
                                            [ 'id'=>'holydaywork_terms', 'ng-model'=>'contract.holydaywork_terms', 'placeholder' =>'休日労働', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '休日','holyday_terms', false ,
                                            [ 'id'=>'holyday_terms', 'ng-model'=>'contract.holyday_terms', 'placeholder' =>'休日', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('安全・衛生', false ,
                                            [ 'id'=>'health_safety_terms', 'ng-model'=>'contract.health_safety_terms', 'placeholder' =>'安全・衛生', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('契約解除の措置（派遣契約書)', false ,
                                            [ 'id'=>'contract_cancel_dispatch', 'ng-model'=>'contract.contract_cancel_dispatch', 'placeholder' =>'契約解除の措置（派遣契約書)', 'maxlength' => '512', 'rows'=>'2']) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('契約解除の措置（雇入通知書)	', false ,
                                            [ 'id'=>'contract_cancel_employment', 'ng-model'=>'contract.contract_cancel_employment', 'placeholder' =>'契約解除の措置（雇入通知書)', 'maxlength' => '256', 'rows'=>'2']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '退職に関する事項','retirement', false ,
                                            [ 'id'=>'retirement', 'ng-model'=>'contract.retirement', 'placeholder' =>'退職に関する事項', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '福利厚生等','welfare', false ,
                                            [ 'id'=>'welfare', 'ng-model'=>'contract.welfare', 'placeholder' =>'福利厚生等', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('苦情処理事項（派遣契約書)', false ,
                                            [ 'id'=>'troubles_dispatch', 'ng-model'=>'contract.troubles_dispatch', 'placeholder' =>'苦情処理事項（派遣契約書)', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('苦情処理事項（雇入通知書)', false ,
                                            [ 'id'=>'troubles_employment', 'ng-model'=>'contract.troubles_employment', 'placeholder' =>'苦情処理事項（雇入通知書)', 'maxlength' => '256', 'rows'=>'2']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣料金の明示','dispatch_fee', false ,
                                            [ 'id'=>'dispatch_fee', 'ng-model'=>'contract.dispatch_fee', 'placeholder' =>'派遣料金の明示', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('派遣元36協定', false ,
                                            [ 'id'=>'dispatch36', 'ng-model'=>'contract.dispatch36', 'placeholder' =>'派遣元36協定', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('派遣先が派遣労働者を雇用する場合の紛争措置', false ,
                                            [ 'id'=>'dispute_measures', 'ng-model'=>'contract.dispute_measures', 'placeholder' =>'派遣先が派遣労働者を雇用する場合の紛争措置', 'maxlength' => '256', 'rows'=>'2']) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('労働契約みなし制度について', false ,
                                            [ 'id'=>'contract_deemed', 'ng-model'=>'contract.contract_deemed', 'placeholder' =>'労働契約みなし制度について', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '有効期間の日程','validity_period', false ,
                                            [ 'id'=>'validity_period', 'ng-model'=>'contract.validity_period', 'placeholder' =>'有効期間の日程', 'maxlength' => '256' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('特別条項', false ,
                                            [ 'id'=>'special_provisions', 'ng-model'=>'contract.special_provisions', 'placeholder' =>'特別条項', 'maxlength' => '256', 'rows'=>'2']) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('備考', false ,
                                            [ 'id'=>'remarks', 'ng-model'=>'contract.remarks', 'placeholder' =>'備考', 'maxlength' => '256', 'rows'=>'2']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="sheet"
                                ng-class="{active: showTab =='sheet', fade: showTab !='sheet' }">
                                <div class="card">
                                    <div class="card-body" id="sheet">
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '契約期間','contract_period', false ,
                                            [ 'id'=>'contract_period', 'ng-model'=>'contract.contract_period', 'placeholder' =>'契約期間', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '業務内容','business_content', false ,
                                            [ 'id'=>'business_content', 'ng-model'=>'contract.business_content', 'placeholder' =>'業務内容', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '就業場所','work_place', false ,
                                            [ 'id'=>'work_place', 'ng-model'=>'contract.work_place', 'placeholder' =>'就業場所', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '休憩時間','sh_breaktime', false ,
                                            [ 'id'=>'sh_breaktime', 'ng-model'=>'contract.sh_breaktime', 'placeholder' =>'休憩時間', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '所定時間外労働','sh_overwork', false ,
                                            [ 'id'=>'sh_overwork', 'ng-model'=>'contract.sh_overwork', 'placeholder' =>'所定時間外労働', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '休日','sh_holyday', false ,
                                            [ 'id'=>'sh_holyday', 'ng-model'=>'contract.sh_holyday', 'placeholder' =>'休日', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '休暇','sh_timeoff', false ,
                                            [ 'id'=>'sh_timeoff', 'ng-model'=>'contract.sh_timeoff', 'placeholder' =>'休暇', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '賃金','sh_wage', false ,
                                            [ 'id'=>'sh_wage', 'ng-model'=>'contract.sh_wage', 'placeholder' =>'賃金', 'maxlength' => '128' ]) !!}
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '割増賃金率','extra_wage_rate', false ,
                                            [ 'id'=>'extra_wage_rate', 'ng-model'=>'contract.extra_wage_rate', 'placeholder' =>'割増賃金率', 'maxlength' => '128' ]) !!}
                                        <div class="form-group">
                                            <div  class="row">
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('社会保険の加入状況') !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('厚生年金',['cols'=>1]) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_yesno,
                                                ['id'=>'welfarepension_type', 'ng-model'=>'contract.welfarepension_type']); !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('健康保険',['cols'=>1]) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_yesno,
                                                ['id'=>'healthinsurance_type', 'ng-model'=>'contract.healthinsurance_type']); !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('雇用保険',['cols'=>1]) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_yesno,
                                                ['id'=>'employmentinsurance_type', 'ng-model'=>'contract.employmentinsurance_type']); !!}
                                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('労災保険',['cols'=>1]) !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_yesno,
                                                ['id'=>'accidentinsurance_type', 'ng-model'=>'contract.accidentinsurance_type']); !!}
                                            </div>
                                        </div>
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'その他','sheet_other', false ,
                                            [ 'id'=>'sheet_other', 'ng-model'=>'contract.sheet_other', 'placeholder' =>'その他', 'maxlength' => '128' ]) !!}                                    
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="ledger"
                                ng-class="{active: showTab =='ledger', fade: showTab !='ledger' }">
                                <div class="card">
                                    <div class="card-body" id="ledger">
                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '紹介予定派遣である旨','introduction_dispatching', false ,
                                            [ 'id'=>'introduction_dispatching', 'ng-model'=>'contract.introduction_dispatching', 'placeholder' =>'紹介予定派遣である旨', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '求人・求職の意思確認等の職業紹介の時期及び内容','offer_purpose', false ,
                                            [ 'id'=>'offer_purpose', 'ng-model'=>'contract.offer_purpose', 'placeholder' =>'求人・求職の意思確認等の職業紹介の時期及び内容', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '採否結果','result', false ,
                                            [ 'id'=>'result', 'ng-model'=>'contract.result', 'placeholder' =>'採否結果', 'maxlength' => '128' ]) !!}

                                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('採否結果の理由', false ,
                                            ['id'=>'result_reason', 'ng-model'=>'contract.result_reason', 'placeholder' =>'採否結果の理由', 'maxlength' => '256', 'rows'=>'2']); !!}

                                    </div>
                                </div>
                            </div>                                                                                                                                                                        
                        </div> 
                    </div>
  

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <ng-template ng-show="!contract_id">
                            @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                <i class="far fa-save"></i> 登録
                            </button>
                            @endcanany
                        </ng-template>
                        <ng-template ng-show="contract_id">
                            @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                <i class="far fa-save"></i> 更新
                            </button>
                            @endcanany
                        </ng-template>

                        @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_DELETE])
                        <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="contract_id">
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

    <form class="form_period" action="" method="" onsubmit="return false;" >
        <div class="modal modal-detail" id="modalPeriodItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" >契約情報契約期間更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="card">
                            <div class="card-body" id="detailPeriod">
                                {!! \App\Http\Utils\DispatchUtils::showDetailLabel('派遣先名',
                                    [ 'id'=>'pe_dispatcharea_name', 'ng-bind'=>'contract_pe.dispatcharea_name']) !!}

                                {!! \App\Http\Utils\DispatchUtils::showDetailLabel('スタッフ名',
                                    [ 'id'=>'pe_staff_name', 'ng-bind'=>'contract_pe.name']) !!}

                                {!! \App\Http\Utils\DispatchUtils::showDetailDateFromTo('派遣期間', true ,
                                    [ 'id'=>'pe_contract_fromdate', 'ng-model'=>'contract_pe.contract_fromdate', 'placeholder'=>'yyyy/MM/dd'],
                                    [ 'id'=>'pe_contract_todate', 'ng-model'=>'contract_pe.contract_todate', 'placeholder'=>'yyyy/MM/dd']) !!}
                            </div>
                        </div>
                    </div>
  

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_CONTRACT_SETTING_CREATE])
                        <button type="submit" class="btn btn-success" ng-click="period()">
                            <i class="far fa-save"></i> 登録
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

    <form class="form_select_dispatcharea" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail modal-child" id="modalSelectDispatchArea" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">派遣先選択</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="form-group row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('派遣先名', ['cols'=>3]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('text','sc_dispatchareaname', ['cols'=>9],
                            ['id'=>'sc_dispatchareaname', 'ng-model'=>'search_d.sc_dispatchareaname', 'placeholder' =>'派遣先名（部分一致）', 'maxlength' => '256']); !!}
                        </div>
                        <div class="text-right">
                            <button class="btn btn-primary mb-1" type="submit" ng-click="clickSearch_d()"><i class="fas fa-search" ></i> 検索</button>
                        </div>

                        <div ng-if="showSchDispatchArea" class="card mt-3">
                            <div class="card-header">派遣先一覧</div>
                            <div class="card-body">
                                <span class="clear"></span>
                                {!! \App\Http\Utils\DispatchUtils::showDispNumber(
                                            [ 'ng-model' =>'sch_d_paginate.per_page', 'ng-change'=>'searchDispatchArea(1,sch_d_paginate.per_page)', 'ng-options'=>'option for option in option_limit track by option']) !!}
                    
                                <table class="tablesaw-list searchDispatchArea table-sort-client tablesaw table-bordered adminlist margin-top-5">
                                    <thead>
                                        <tr>
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('派遣先名', 'company_name', true, 'searchDispatchArea') !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('事業所名', 'office_name', true, 'searchDispatchArea') !!}
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('部署名', 'department', true, 'searchDispatchArea') !!}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="(key, area) in sch_dispatchareas">
                                            <td ng-click="selectedDispatchArea(area)"><% area.company_name %></td>
                                            <td ng-click="selectedDispatchArea(area)"><% area.office_name  %></td>
                                            <td ng-click="selectedDispatchArea(area)" style="width:95px;"><% area.department  %></td>
                                        </tr>
                                    </tbody>
                                </table>
                                {!! \App\Http\Utils\DispatchUtils::showPaginate('sch_d_paginate', 'changeDetailPage_d') !!}                    

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
    
    <form class="form_select_user" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail modal-child" id="modalSelectUser" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">スタッフ選択</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="form-group row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('氏名', ['cols'=>3]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('text','sc_name', ['cols'=>9],
                            ['id'=>'sc_name', 'ng-model'=>'search_u.sc_name', 'placeholder' =>'氏名（部分一致）', 'maxlength' => '256']); !!}
                        </div>

                        <div class="text-right">
                            <button class="btn btn-primary mb-1" type="submit" ng-click="clickSearch_u()"><i class="fas fa-search" ></i> 検索</button>
                        </div>

                        <div ng-if="showSchUser" class="card mt-3">
                            <div class="card-header">スタッフ一覧</div>
                            <div class="card-body">
                                <span class="clear"></span>
                                {!! \App\Http\Utils\DispatchUtils::showDispNumber(
                                    [ 'ng-model' =>'sch_u_paginate.per_page', 'ng-change'=>'searchUser(1,sch_u_paginate.per_page)', 'ng-options'=>'option for option in option_limit track by option']) !!}                    
                                <table class="tablesaw-list searchUser table-sort-client tablesaw table-bordered adminlist margin-top-5">
                                    <thead>
                                        <tr>
                                            {!! \App\Http\Utils\DispatchUtils::showSortColumn('氏名', 'name', true, 'searchUser') !!}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="(key, item) in sch_users">
                                            <td ng-click="selectedUser(item)"><% item.name %></td>
                                        </tr>
                                    </tbody>
                                </table>
                                {!! \App\Http\Utils\DispatchUtils::showPaginate('sch_u_paginate', 'changeDetailPage_u') !!}                    
                               
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
                $scope.showTab = localStorage.getItem('contracttitle.tab');
                if(!$scope.showTab) $scope.showTab = 'detailAdmin';
                localStorage.setItem('contracttitle.tab', $scope.showTab);
                $scope.option_limit = [20,50,100];
                $scope.contract = {};
                $scope.search_d = {
                    sc_dispatcharea_name:"", 
                };
                $scope.search_u = {
                    sc_name:"", 
                };                
                $scope.search_d_bk = {
                    sc_dispatcharea_name:"", 
                };
                $scope.search_u_bk = {
                    sc_name:"", 
                };
                $scope.sch_d_paginate = {
                    start_page:1,
                    end_page:10,
                    total:0,
                    current_page:0,
                    per_page:20,
                    last_page:0,
                    from:0,
                    to:0,
                };
                $scope.sch_u_paginate = {
                    start_page:1,
                    end_page:10,
                    total:0,
                    current_page:0,
                    per_page:20,
                    last_page:0,
                    from:0,
                    to:0,
                };
                $scope.sch_dispatchareas={};
                $scope.sch_users={};
                $scope.showSchDispatchArea = false;
                $scope.showSchUser = false;
                $scope.orderBy_d = 'create_at';
                $scope.orderDir_d = 'desc';
                $scope.orderBy_u = 'create_at';
                $scope.orderDir_u = 'desc';
                $scope.onShowTab = function(tab){
                    $scope.showTab = tab;
                    localStorage.setItem('contracttitle.tab', tab);
                }

                $rootScope.$on("openNewContract", function(event){
                    $scope.contract_id = 0;
                    $scope.showTab = 'detailAdmin';
                    setDefaultContract();
                    if(allow_create) $scope.readonly = false;
                    else $scope.readonly = true;
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openEditContract", function(event, data){

                    $scope.contract_id = data.id;
                    $scope.showTab = 'detailAdmin';
                    if(allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    hideMessages();
                    hasChange = false;
                    $http.post(link_geteditdata, {
                        id: $scope.contract_id,
                        })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.contract = event.data.contract;
                                setEditDateFormat();
                                
                            }
                        });
                    $("#modalDetailItem").modal();
                });
                $rootScope.$on("openPeriodContract", function(event, $data){

                    setDefaultContract();
                    if(allow_create) $scope.readonly = false;
                    else $scope.readonly = true;
                    hideMessages();
                    hasChange = false;
                    $http.post(link_geteditdata, {
                        id: $data.id,
                        })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalPeriodItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.contract_pe = event.data.contract;
                                $scope.contract_pe.id = 0;
                                if ($scope.contract_pe.contract_fromdate)$scope.contract_pe.contract_fromdate = new Date($scope.contract_pe.contract_fromdate);                              
                                if ($scope.contract_pe.contract_todate)$scope.contract_pe.contract_todate = new Date($scope.contract_pe.contract_todate);                              
                               
                            }
                        });
                    $("#modalPeriodItem").modal();
                });
                $scope.period = function(callSuccess){
                    if($(".form_period")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function(event){

                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalPeriodItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            }else{   
                                $("#modalPeriodItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                                if(callSuccess) callSuccess();
                                location.reload();
                                $("#modalPeriodItem").modal('hide');

                            }
                        };
                        $http.post(link_save, {item: $scope.contract_pe})
                                .then(saveSuccess);
                    }

                };
                $scope.save = function(callSuccess){
                    $scope.showTab = 'detailAdmin';                    
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
                                location.reload();
                                $("#modalDetailItem").modal('hide');
                            }
                        };
                        $http.post(link_save, {item: $scope.contract})
                            .then(saveSuccess);
                    }

                };
                $scope.remove = function(){
                    var cids = [];
                    cids.push($scope.contract_id);

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'契約を削除します。よろしいですか？',
                            btnDanger:'削除',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_deletes, { cids: cids})
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
                $scope.getAddress=function(){
                    $scope.contract.address1=""
                    $http({
                        method:'get',
                        url:'setting-user/get-address',
                        params:{zipcode:$scope.contract.postal_code}
                    }).then(function (event){
                        let res=event.data.results;
                        if (res!=null){
                            $scope.contract.address1=res[0].address1+res[0].address2+res[0].address3
                        }

                    })
                }
                $scope.range_func = function(n) {
                    return new Array(n);
                };

                $scope.selectDispatchArea = function(){
                    hideMessages();
                    hasChange = false;
                    $("#modalSelectDispatchArea").modal();
                };
                $scope.selectUser = function(){
                    hideMessages();
                    hasChange = false;
                    $("#modalSelectUser").modal();
                };
                $scope.changeDetailPage_d = function(page){

                    if(page == $scope.sch_d_paginate.current_page || page < 1 || page > $scope.sch_d_paginate.last_page){
                        return;
                    }
                    $scope.searchDispatchArea(page);
                };
                $scope.clickSearch_d = function(page, limit){
                    $scope.orderBy_d = 'create_at';
                    $scope.orderDir_d = 'desc';
                    $scope.searchDispatchArea();
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');

                };
                $scope.changeDetailPage_u = function(page){

                    if(page == $scope.sch_u_paginate.current_page || page < 1 || page > $scope.sch_u_paginate.last_page){
                        return;
                    }
                    $scope.searchUser(page);
                };
                $scope.clickSearch_u = function(page, limit){
                    $scope.orderBy_u = 'create_at';
                    $scope.orderDir_u = 'desc';
                    $scope.searchUser();
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');

                };

                $scope.searchDispatchArea = function(page, limit){                    
                    $scope.showSchDispatchArea = true;
                    $rootScope.$emit("showLoading");
                    var $limit = 20;
                    if (limit) $limit = limit; 
                    $scope.search_item = $scope.search_d;
                    if (!page){
                        $scope.search_d_bk = JSON.parse(JSON.stringify($scope.search_d));
                    }else{
                        $scope.search_item = $scope.search_d_bk;
                    }
                    $http.post(link_dispatcharea_search, {
                        items: $scope.search_item,
                        page:page,
                        limit:$limit,
                        orderBy:$scope.orderBy_d,
                        orderDir:$scope.orderDir_d,
                    })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalSelectDispatchArea .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                             
                                $scope.sch_dispatchareas = event.data.items.data;
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
                                $scope.sch_d_paginate ={
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
                $scope.selectedDispatchArea = function(data){
                    $rootScope.$emit("showLoading");
                    $scope.contract.dispatcharea_name = data.company_name;
                    $scope.contract.office_name = data.office_name;
                    $scope.contract.office_address = data.office_address;
                    $scope.contract.department = data.department;
                    $scope.contract.position = data.position;
                    $scope.contract.employment_address = data.employment_address;
                    $scope.contract.employment_phone_no = data.employment_phone_no;
                    $scope.contract.responsible_name = data.responsible_name;
                    $scope.contract.responsible_phone_no = data.responsible_phone_no;
                    $scope.contract.commander_name = data.commander_name;
                    $scope.contract.commander_phone_no = data.commander_phone_no;
                    $scope.contract.troubles_name = data.troubles_name;
                    $scope.contract.troubles_phone_no = data.troubles_phone_no;
                    $scope.contract.fraction_type = data.fraction_type;
                    $rootScope.$emit("hideLoading");
                    $("#modalSelectDispatchArea").modal('hide');
                };
                $scope.searchUser = function(page, limit){                    
                    $scope.showSchUser = true;
                    $rootScope.$emit("showLoading");
                    var $limit = 20;
                    if (limit) $limit = limit; 
                    $scope.search_item = $scope.search_u;
                    if (!page){
                        $scope.search_u_bk = JSON.parse(JSON.stringify($scope.search_u));
                    }else{
                        $scope.search_item = $scope.search_u_bk;
                    }
                    $http.post(link_user_search, {
                        items: $scope.search_item,
                        page:page,
                        limit:$limit,
                        orderBy:$scope.orderBy_u,
                        orderDir:$scope.orderDir_u,
                    })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalSelectUser .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{

                                $scope.sch_users = event.data.items.data;
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
                                $scope.sch_u_paginate ={
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
                $scope.selectedUser = function(data){
                    $rootScope.$emit("showLoading");
                    $scope.contract.name = data.name;

                    $rootScope.$emit("hideLoading");
                    $("#modalSelectUser").modal('hide');
                };
                $scope.changeSort = function(orderBy, addId){
                    $orderDir = "asc";
                    switch(addId){
                        case "searchDispatchArea":
                            if($scope.orderBy_d == orderBy){
                                $scope.orderDir_d = $scope.orderDir_d == 'asc'?'desc':'asc';
                            }else{
                                $scope.orderDir_d = "asc"
                            }
                            $scope.orderBy_d = orderBy;
                            $orderDir = $scope.orderDir_d;
                            $scope.searchDispatchArea(1);
                            break;
                        case "searchUser":
                            if($scope.orderBy_u == orderBy){
                                $scope.orderDir_u  = $scope.orderDir_u  == 'asc'?'desc':'asc';
                            }else{
                                $scope.orderDir_u = "asc"
                            }
                            $scope.orderBy_u  = orderBy;
                            $orderDir = $scope.orderDir_u ;
                            $scope.searchUser(1);
                            break;
                    }



                    $("."+addId+".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $("."+addId+".table-sort-client .sort-column."+orderBy).addClass('active');
                    if($orderDir == 'asc')
                        $("."+addId+".table-sort-client .sort-column."+orderBy).addClass('active-up');
                    else $("."+addId+".table-sort-client .sort-column."+orderBy).addClass('active-down');
                };
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
                function setDefaultChar($code_data){
                    let item = {};
                    $code_data.forEach(function (value, index) {
                        item[value.id] = value.name;
                    });   
                    return item;
                }
                function setDefaultContract(){
                    const workdays = setDefaultCheckbox({!! json_encode($code_week) !!});
                    const businesses = setDefaultCheckbox({!! json_encode($code_business) !!});
                    const fraction = setDefaultRadio({!! json_encode($code_fraction) !!});
                    const timeflat = setDefaultRadio({!! json_encode($code_timeflat) !!});
                    const defchar = setDefaultChar({!! json_encode($code_defaultchar) !!});
                    $scope.contract = {
                        id:0,
                        businesses:businesses,
                        workdays:workdays,
                        fraction_type:fraction,
                        price_timeflat_type:timeflat,
                        wage_timeflat_type:timeflat,
                        update_judgment:defchar[281],
                        con_working_style:defchar[282],
                        overtime_terms:defchar[283],
                        health_safety_terms:defchar[284],
                        contract_cancel_dispatch:defchar[285],
                        contract_cancel_employment:defchar[286],
                        troubles_dispatch:defchar[287],
                        troubles_employment:defchar[288],
                        dispute_measures:defchar[289],
                        contract_deemed:defchar[290],
                    };

                } 

                function setEditDateFormat(){
                    if ($scope.contract.contract_fromdate)$scope.contract.contract_fromdate = new Date($scope.contract.contract_fromdate);                              
                    if ($scope.contract.contract_todate)$scope.contract.contract_todate = new Date($scope.contract.contract_todate);                              
                    if ($scope.contract.contract_date_sheet)$scope.contract.contract_date_sheet = new Date($scope.contract.contract_date_sheet);                              
                    if ($scope.contract.basiccontract_fromdate)$scope.contract.basiccontract_fromdate = new Date($scope.contract.basiccontract_fromdate);                              
                    if ($scope.contract.basiccontract_todate)$scope.contract.basiccontract_todate = new Date($scope.contract.basiccontract_todate);                              
                    if ($scope.contract.socialinsurance_fromdate)$scope.contract.socialinsurance_fromdate = new Date($scope.contract.socialinsurance_fromdate);                              
                    if ($scope.contract.socialinsurance_todate)$scope.contract.socialinsurance_todate = new Date($scope.contract.socialinsurance_todate);                              
                    if ($scope.contract.employmentinsurance_fromdate)$scope.contract.employmentinsurance_fromdate = new Date($scope.contract.employmentinsurance_fromdate);                              
                    if ($scope.contract.employmentinsurance_todate)$scope.contract.employmentinsurance_todate = new Date($scope.contract.employmentinsurance_todate);                              
                    if ($scope.contract.employmentcontract_date)$scope.contract.employmentcontract_date = new Date($scope.contract.employmentcontract_date);                              
                    if ($scope.contract.employmentconversion_date)$scope.contract.employmentconversion_date = new Date($scope.contract.employmentconversion_date);                              
                    if ($scope.contract.organization_date)$scope.contract.organization_date = new Date($scope.contract.organization_date);                              
                    if ($scope.contract.organization_date_sheet)$scope.contract.organization_date_sheet = new Date($scope.contract.organization_date_sheet);                              
                    if ($scope.contract.office_date)$scope.contract.office_date = new Date($scope.contract.office_date);                              
                    if ($scope.contract.office_date_sheet)$scope.contract.office_date_sheet = new Date($scope.contract.office_date_sheet);                              
                    if ($scope.contract.employmentworker_fromdate)$scope.contract.employmentworker_fromdate = new Date($scope.contract.employmentworker_fromdate);                              
                    if ($scope.contract.employmentworker_todate)$scope.contract.employmentworker_todate = new Date($scope.contract.employmentworker_todate);                              
                    if ($scope.contract.project_fromdate)$scope.contract.project_fromdate = new Date($scope.contract.project_fromdate);                              
                    if ($scope.contract.project_todate)$scope.contract.project_todate= new Date($scope.contract.project_todate);                              
                    if ($scope.contract.closedwork_fromdate)$scope.contract.closedwork_fromdate = new Date($scope.contract.closedwork_fromdate);                              
                    if ($scope.contract.closedwork_todate)$scope.contract.closedwork_todate = new Date($scope.contract.closedwork_todate);                              

                }
            })
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
        .checkboxlist{
        height:90px;
        overflow-y:auto;
        border:1px solid rgb(206, 212, 218);
        padding-left:5px;"
        }
        .control-label.lineheight20{
            line-height:20px;
        }
        .searchDispatchArea.table-sort-client{ }
        .searchDispatchArea.table-sort-client .sort{ }
        .searchDispatchArea.table-sort-client .sort-column{ }
        .searchDispatchArea.table-sort-client .sort-column .icon{ right: 5px; }
        .searchDispatchArea.table-sort-client .sort-column .icon-up{ display: none; }
        .searchDispatchArea.table-sort-client .sort-column .icon-down{ display: none; }
        .searchDispatchArea.table-sort-client .sort-column.active{ }
        .searchDispatchArea.table-sort-client .sort-column.active .icon{ display: none; }
        .searchDispatchArea.table-sort-client .sort-column.active-up{ }
        .searchDispatchArea.table-sort-client .sort-column.active-up .icon-up{ display: inline-block; }
        .searchDispatchArea.table-sort-client .sort-column.active-down{ }
        .searchDispatchArea.table-sort-client .sort-column.active-down .icon{ display: none; }
        .searchDispatchArea.table-sort-client .sort-column.active-down .icon-down{ display: inline-block; }

        .searchUser.table-sort-client{ }
        .searchUser.table-sort-client .sort{ }
        .searchUser.table-sort-client .sort-column{ }
        .searchUser.table-sort-client .sort-column .icon{ right: 5px; }
        .searchUser.table-sort-client .sort-column .icon-up{ display: none; }
        .searchUser.table-sort-client .sort-column .icon-down{ display: none; }
        .searchUser.table-sort-client .sort-column.active{ }
        .searchUser.table-sort-client .sort-column.active .icon{ display: none; }
        .searchUser.table-sort-client .sort-column.active-up{ }
        .searchUser.table-sort-client .sort-column.active-up .icon-up{ display: inline-block; }
        .searchUser.table-sort-client .sort-column.active-down{ }
        .searchUser.table-sort-client .sort-column.active-down .icon{ display: none; }
        .searchUser.table-sort-client .sort-column.active-down .icon-down{ display: inline-block; }        
    </style>
@endpush