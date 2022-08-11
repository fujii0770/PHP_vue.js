<div ng-controller="DetailController">

  <form class="form_edit" action="" method="" onsubmit="return false;" >
    <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
          <div class="modal-header">
            <h4 class="modal-title" ng-show="!dispatchhr_id && setting">人材情報設定</h4>
            <h4 class="modal-title" ng-show="!dispatchhr_id && !setting">人材情報登録</h4>
            <h4 class="modal-title" ng-show="dispatchhr_id && !setting">人材情報更新</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <ul class="nav nav-tabs">
            {!! \App\Http\Utils\DispatchUtils::showTabItem('基本情報', 'basic') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('希望条件', 'desiredcondition', 'disabled: dispatchhr_id == 0 && !setting') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('実務経験', 'workexperience', 'disabled: dispatchhr_id == 0 && !setting') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('資格ほか', 'qualification', 'disabled: dispatchhr_id == 0 && !setting') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('職務経歴', 'jobcareer', 'disabled: dispatchhr_id == 0 && !setting') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('スキル', 'skill', 'disabled: dispatchhr_id == 0 && !setting') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('管理情報', 'managementinfo', 'disabled: dispatchhr_id == 0 && !setting') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('職歴と予定','workhistory', 'disabled: true') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('給与情報', 'salaryinfo', 'disabled: true') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('社会保険', 'socialinsurance', 'disabled: true') !!}
            {!! \App\Http\Utils\DispatchUtils::showTabItem('税金関連', 'taxrelated', 'disabled: true') !!}
          </ul>

          <div class="modal-body form-horizontal">
            <div class="message message-info"></div>
            <div class="tab-content">
              <div class="tab-pane" id="basic"
                ng-class="{active: showTab =='basic', fade: showTab !='basic' }">
                <div class="card">
                  <div class="card-body" id="basic">

                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '名前', 'name', true ,
                      [ 'id'=>'name', 'ng-model'=>'dispatchhr.name', 'placeholder' =>'名前', 'maxlength' => '128' ],0) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'ふりがな', 'furigana', true ,
                      [ 'id'=>'furigana', 'ng-model'=>'dispatchhr.furigana', 'placeholder' =>'ふりがな', 'maxlength' => '128' ],0) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('登録区分', 'regist_kbn' , false , $code_registkbn,
                      [ 'name' => 'regist_kbn', 'ng-model' => 'dispatchhr.regist_kbn' ],0) !!}


                    <div class="form-group">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('性別　生年月日', [], 0) !!}
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('性別', ['cols'=>1]) !!}
                        {!! \App\Http\Utils\DispatchUtils::showSelect(['cols'=>1], $code_gender,
                          ['id'=>'gender_type', 'ng-model'=>'dispatchhr.gender_type']); !!}
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('生年月日', ['cols'=>1]) !!}
                        {!! \App\Http\Utils\DispatchUtils::showDate(['cols'=>2],
                          [ 'id'=>'birthdate', 'ng-model'=>'dispatchhr.birthdate', 'placeholder'=>'yyyy/MM/dd']) !!}
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('年齢', ['cols'=>1]) !!}
                        {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'age', ['cols'=>1, 'textalign'=>'text-right'],
                          [ 'id'=>'age', 'ng-model'=>'dispatchhr.age', 'placeholder' =>'99', 'maxlength' => '3' ]) !!}
                      </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('連絡先', [], 0) !!}
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('電話番号', ['cols'=>1]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'phone_no', ['cols'=>2],
                                [ 'id'=>'phone_no', 'ng-model'=>'dispatchhr.phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('携帯番号', ['cols'=>1]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'mobile_phone_no', ['cols'=>2],
                                [ 'id'=>'mobile_phone_no', 'ng-model'=>'dispatchhr.mobile_phone_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('FAX番号', ['cols'=>1]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('text', 'fax_no', ['cols'=>2],
                                [ 'id'=>'fax_no', 'ng-model'=>'dispatchhr.fax_no', 'placeholder' =>'000-0000-0000', 'maxlength' => '15' ]) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('メールアドレス', [], 0) !!}
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('メイン', ['cols'=>1]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('email', 'email', ['cols'=>5],
                                [ 'id'=>'email', 'ng-model'=>'dispatchhr.email', 'placeholder' =>'email@example.com', 'maxlength' => '256' ]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showCheckBoxOnly('mail_send_flg', 'dispatchhr.mail_send_flg', ['cols'=>2],
                                '送信先に指定', []) !!}
                        </div>
                        <div class="row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('携帯', ['cols'=>1]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showInputText('mobile_email', 'mobile_email', ['cols'=>5],
                                [ 'id'=>'mobile_email', 'ng-model'=>'dispatchhr.mobile_email', 'placeholder' =>'email@example.com', 'maxlength' => '256' ]) !!}
                            {!! \App\Http\Utils\DispatchUtils::showCheckBoxOnly('mobile_mail_send_flg', 'dispatchhr.mobile_mail_send_flg', ['cols'=>2],
                                '送信先に指定', []) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            {!! \App\Http\Utils\DispatchUtils::setTitleLabel('希望連絡方法', [], 0) !!}
                            {!! \App\Http\Utils\DispatchUtils::showCheckBoxModel('contact_method', [], $code_contact, []) !!}
                        </div>
                    </div>
                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '最寄駅', 'nearest_station', false ,
                      [ 'id'=>'nearest_station', 'ng-model'=>'dispatchhr.nearest_station', 'placeholder' =>'最寄駅', 'maxlength' => '64' ], 0) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailPostal('郵便番号', 'postal_code', false ,
                      [ 'id'=>'postal_code', 'ng-model'=>'dispatchhr.postal_code', 'placeholder' =>'000-0000', 'maxlength' => '10', 'ng-change'=>'getAddress()' ], 0) !!}
                                        
                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '住所', 'address1', false ,
                      [ 'id'=>'address1', 'ng-model'=>'dispatchhr.address1', 'placeholder' =>'住所', 'maxlength' => '128' ], 0) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', 'マンション等', 'address2', false ,
                      [ 'id'=>'address2', 'ng-model'=>'dispatchhr.address2', 'placeholder' =>'マンション等', 'maxlength' => '128' ], 0) !!}

                    <div class="form-group" ng-if="dispsetting.set_1">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('現在の就業状況', [], 1) !!}
                        {!! \App\Http\Utils\DispatchUtils::showRadio('scitem_1' , [] , $code_employment,
                          [ 'name' => 'scitem_1', 'ng-model' => 'dispatchhr.scitem_1']) !!}
                      </div>
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('') !!}
                        {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>8],
                          ['id'=>'scitem_2', 'ng-model'=>'dispatchhr.scitem_2', 'rows'=>'2', 'placeholder' =>'その他', 'maxlength' => '256']); !!}
                      </div>
                    </div>
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('就業状況区分', 'scitem_3' , false , $code_employmentkbn,
                     [ 'name' => 'scitem_3', 'ng-model' => 'dispatchhr.scitem_3' ], 2) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('有期雇用契約雇入れ日', false ,
                      [ 'id'=>'scitem_4', 'ng-model'=>'dispatchhr.scitem_4', 'placeholder'=>'yyyy/MM/dd'], 3) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('無期雇用転換日', false ,
                      [ 'id'=>'scitem_5', 'ng-model'=>'dispatchhr.scitem_5', 'placeholder'=>'yyyy/MM/dd'], 4) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('就業可能日', false ,
                      [ 'id'=>'scitem_6', 'ng-model'=>'dispatchhr.scitem_6', 'placeholder'=>'yyyy/MM/dd'], 5) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('契約日(帳票用)', false ,
                      [ 'id'=>'scitem_7', 'ng-model'=>'dispatchhr.scitem_7', 'placeholder'=>'yyyy/MM/dd'], 6) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailLabel('派遣開始日',
                      [ 'id'=>'scitem_8', 'ng-bind'=>'dispatchhr.scitem_8'], 7) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('派遣終了日', false ,
                      [ 'id'=>'scitem_9', 'ng-model'=>'dispatchhr.scitem_9', 'placeholder'=>'yyyy/MM/dd'], 8) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '派遣終了理由', 'scitem_10', false ,
                      [ 'id'=>'scitem_10', 'ng-model'=>'dispatchhr.scitem_10', 'placeholder' =>'派遣終了理由', 'maxlength' => '128' ], 9) !!}
                      
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('雇用安定措置',false,
                      ['id'=>'scitem_11', 'ng-model'=>'dispatchhr.scitem_11', 'placeholder' =>'雇用安定措置', 'maxlength' => '256', 'rows'=>'2'], 10); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('教育訓練の状況',false,
                      ['id'=>'scitem_12', 'ng-model'=>'dispatchhr.scitem_12', 'placeholder' =>'雇用安定措置', 'maxlength' => '256', 'rows'=>'2'], 11); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('キャリアコンサルティングの実施状況',false,
                      ['id'=>'scitem_13', 'ng-model'=>'dispatchhr.scitem_13', 'placeholder' =>'雇用安定措置', 'maxlength' => '256', 'rows'=>'2'], 12); !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('入社日', false ,
                      [ 'id'=>'scitem_14', 'ng-model'=>'dispatchhr.scitem_14', 'placeholder'=>'yyyy/MM/dd'], 13) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('昇給月', false ,
                      [ 'id'=>'scitem_15', 'ng-model'=>'dispatchhr.scitem_15', 'placeholder'=>'yyyy/MM/dd'], 14) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('一時面接',false,
                      ['id'=>'scitem_16', 'ng-model'=>'dispatchhr.scitem_16', 'placeholder' =>'雇用安定措置', 'maxlength' => '256', 'rows'=>'2'], 15); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('二次面接',false,
                      ['id'=>'scitem_17', 'ng-model'=>'dispatchhr.scitem_17', 'placeholder' =>'雇用安定措置', 'maxlength' => '256', 'rows'=>'2'], 16); !!}
                    <div class="form-group" ng-if="dispsetting.set_17">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('研修①一般常識', [], 17) !!}
                        {!! \App\Http\Utils\DispatchUtils::showRadio('scitem_18' , ['cols'=>2] , $code_attendance,
                          [ 'name' => 'scitem_18', 'ng-model' => 'dispatchhr.scitem_18']) !!}
                        {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>6],
                          ['id'=>'scitem_19', 'ng-model'=>'dispatchhr.scitem_19', 'rows'=>'2', 'placeholder' =>'コメント', 'maxlength' => '256']); !!}
                        </div>
                    </div>
                    <div class="form-group" ng-if="dispsetting.set_18">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('研修②接客の基本', [], 18) !!}
                        {!! \App\Http\Utils\DispatchUtils::showRadio('scitem_20' , ['cols'=>2] , $code_attendance,
                          [ 'name' => 'scitem_20', 'ng-model' => 'dispatchhr.scitem_20']) !!}
                        {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>6],
                          ['id'=>'scitem_21', 'ng-model'=>'dispatchhr.scitem_21', 'rows'=>'2', 'placeholder' =>'コメント', 'maxlength' => '256']); !!}
                      </div>
                    </div>
                    <div class="form-group" ng-if="dispsetting.set_19">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('研修③異文化理解', [], 19) !!}
                        {!! \App\Http\Utils\DispatchUtils::showRadio('scitem_22' , ['cols'=>2] , $code_attendance,
                          [ 'name' => 'scitem_22', 'ng-model' => 'dispatchhr.scitem_22']) !!}
                        {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>6],
                          ['id'=>'scitem_23', 'ng-model'=>'dispatchhr.scitem_23', 'rows'=>'2', 'placeholder' =>'コメント', 'maxlength' => '256']); !!}
                      </div>
                    </div>
                    <div class="form-group" ng-if="dispsetting.set_20">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('研修④英語', [], 20) !!}
                        {!! \App\Http\Utils\DispatchUtils::showRadio('scitem_24' , ['cols'=>2] , $code_attendance,
                          [ 'name' => 'scitem_24', 'ng-model' => 'dispatchhr.scitem_24']) !!}
                        {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>6],
                          ['id'=>'scitem_25', 'ng-model'=>'dispatchhr.scitem_25', 'rows'=>'2', 'placeholder' =>'コメント', 'maxlength' => '256']); !!}
                      </div>
                    </div>
                    <div class="form-group" ng-if="dispsetting.set_21">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('研修⑤メイク', [], 21) !!}
                        {!! \App\Http\Utils\DispatchUtils::showRadio('scitem_26' , ['cols'=>2] , $code_attendance,
                          [ 'name' => 'scitem_26', 'ng-model' => 'dispatchhr.scitem_26']) !!}
                        {!! \App\Http\Utils\DispatchUtils::showTextArea(['cols'=>6],
                          ['id'=>'scitem_27', 'ng-model'=>'dispatchhr.scitem_27', 'rows'=>'2', 'placeholder' =>'コメント', 'maxlength' => '256']); !!}
                      </div>
                    </div>
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('面接対策①',false,
                      ['id'=>'scitem_28', 'ng-model'=>'dispatchhr.scitem_28', 'placeholder' =>'面接対策①', 'maxlength' => '256', 'rows'=>'2'], 22); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('面接対策②',false,
                      ['id'=>'scitem_29', 'ng-model'=>'dispatchhr.scitem_29', 'placeholder' =>'面接対策②', 'maxlength' => '256', 'rows'=>'2'], 23); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('キャリアカウンセリング',false,
                      ['id'=>'scitem_30', 'ng-model'=>'dispatchhr.scitem_30', 'placeholder' =>'キャリアカウンセリング', 'maxlength' => '256', 'rows'=>'2'], 24); !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('確認事項読み合わせ', 'scitem_31' , false , $code_finished,
                      [ 'name' => 'scitem_31', 'ng-model' => 'dispatchhr.scitem_31' ], 25) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailDate('退職日', false ,
                      [ 'id'=>'scitem_32', 'ng-model'=>'dispatchhr.scitem_32', 'placeholder'=>'yyyy/MM/dd'], 26) !!}
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="desiredcondition"
                ng-class="{active: showTab =='desiredcondition', fade: showTab !='desiredcondition' }">
                <div class="card">
                  <div class="card-body" id="desiredcondition">
                    {!! \App\Http\Utils\DispatchUtils::showDetailCheckBox('勤務地', 'scitem_33' , false , $code_worklocation, 'dispatchhr.scitem_33', [], 27); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailCheckBox('就業形態', 'scitem_34' , false , $code_employmentform, 'dispatchhr.scitem_34', [], 28); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailCheckBox('希望金額', 'scitem_35' , false , $code_desiredamount, 'dispatchhr.scitem_35', [], 29); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailCheckBoxVerticalCols('希望職種', 'scitem_36' ,false , $code_desiredjob, 'dispatchhr.scitem_36', [], [], 'checkitem', 30); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('希望職種その他',false,
                      ['id'=>'scitem_37', 'ng-model'=>'dispatchhr.scitem_37', 'placeholder' =>'希望職種その他', 'maxlength' => '256', 'rows'=>'2'], 31); !!}
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="workexperience"
                ng-class="{active: showTab =='workexperience', fade: showTab !='workexperience' }">
                <div class="card">
                  <div class="card-body" id="workexperience">

                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('販売経験', 'scitem_38' , false , $code_yearsofexperience,
                      [ 'name' => 'scitem_38', 'ng-model' => 'dispatchhr.scitem_38' ], 32) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('通訳経験', 'scitem_39' , false , $code_yearsofexperience,
                      [ 'name' => 'scitem_39', 'ng-model' => 'dispatchhr.scitem_39' ], 33) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailCheckBox('経験職種', 'scitem_40' , false , $code_experiencedjob, 'dispatchhr.scitem_40', [], 34); !!}

                  </div>
                </div>

                <div class="form-group">
                  <div class="row">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="qualification"
                ng-class="{active: showTab =='qualification', fade: showTab !='qualification' }">
                <div class="card">
                  <div class="card-body" id="qualification">

                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('その他資格',false,
                      ['id'=>'scitem_41', 'ng-model'=>'dispatchhr.scitem_41', 'placeholder' =>'その他資格', 'maxlength' => '256', 'rows'=>'2'], 35); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('語学',false,
                      ['id'=>'scitem_42', 'ng-model'=>'dispatchhr.scitem_42', 'placeholder' =>'語学', 'maxlength' => '256', 'rows'=>'2'], 36); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('接客の知識',false,
                      ['id'=>'scitem_43', 'ng-model'=>'dispatchhr.scitem_43', 'placeholder' =>'接客の知識', 'maxlength' => '256', 'rows'=>'2'], 37); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('コミュニケーション',false,
                      ['id'=>'scitem_44', 'ng-model'=>'dispatchhr.scitem_44', 'placeholder' =>'コミュニケーション', 'maxlength' => '256', 'rows'=>'2'], 38); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('規則',false,
                      ['id'=>'scitem_45', 'ng-model'=>'dispatchhr.scitem_45', 'placeholder' =>'規則', 'maxlength' => '256', 'rows'=>'2'], 39); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('接客の技術',false,
                      ['id'=>'scitem_46', 'ng-model'=>'dispatchhr.scitem_46', 'placeholder' =>'接客の技術', 'maxlength' => '256', 'rows'=>'2'], 40); !!}
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="jobcareer"
                ng-class="{active: showTab =='jobcareer', fade: showTab !='jobcareer' }">
                <div class="card">
                  <div class="card-body" id="jobcareer">
                  <div class="text-right">

                        @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_DELETE])
                        　　<button type="button"  class="btn btn-danger" ng-click="deleteJobcareer()" ng-disabled="job_numChecked==0" ng-if="!readonly"><i class="fas fa-trash-alt" ></i> 削除</button>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE])
                        　　<div class="btn btn-success" ng-click="addNewJobcareer()" ng-if="!readonly"><i class="fas fa-plus-circle" ></i> 登録</div>
                        @endcanany
                    </div> 
                    <span class="clear"></span>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5">
                        <thead>
                            <tr>
                              <th class="title sort check" scope="col" data-tablesaw-priority="persist" >
                                <input type="checkbox" onClick="checkAll('job', this.checked)" ng-disabled="setting"/>
                              </th>
                              <th scope="col">開始年月</th>
                              <th scope="col">終了年月</th>
                              <th scope="col">会社と部署</th>
                              <th scope="col">業種</th>
                              <th scope="col">就業形態</th>
                              <th scope="col">業務内容</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="(key, item) in jobcareer_list">
                              <td class="title">
                                <input type="checkbox" value="<% item.id %>"  class="job_cid" onClick="isChecked('job', this.checked)" />
                              </td>
                              <td ng-click="editJobcareer(item)"><% item.work_startym %></td>
                              <td ng-click="editJobcareer(item)"><% item.work_toym %></td>
                              <td ng-click="editJobcareer(item)"><% item.company_department %></td>
                              <td ng-click="editJobcareer(item)"><% item.industry %></td>
                              <td ng-click="editJobcareer(item)"><% item.employment %></td>
                              <td ng-click="editJobcareer(item)"><% item.business_content %></td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="text" class="job_boxchecked" ng-model="job_numChecked" style="display: none;" />

                  </div>
                </div>
              </div>
              <div class="tab-pane" id="skill"
                ng-class="{active: showTab =='skill', fade: showTab !='skill' }">
                <div class="card">
                  <div class="card-body" id="skill">
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadioVertical('基本態度', 'scitem_47' , false , $code_basicmanner, $code_abcstages,
                      [ ], 41) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadioVertical('勤務態度', 'scitem_48' , false , $code_workmanner, $code_abcstages,
                      [ ], 42) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadioVertical('チームワーク　協調性', 'scitem_49' , false , $code_teamwork, $code_abcstages,
                      [ ], 43) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadioVertical('コミュニケーション　異文化適応力', 'scitem_50' , false , $code_communication, $code_abcstages,
                      [ ], 44) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadioVertical('組織運営への協力度　参画度、理解度', 'scitem_51' , false , $code_cooperation, $code_abcstages,
                      [ ], 45) !!}

                  </div>
                </div>
              </div>
              <div class="tab-pane" id="managementinfo"
                ng-class="{active: showTab =='managementinfo', fade: showTab !='managementinfo' }">
                <div class="card">
                  <div class="card-body" id="managementinfo">
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('基本的態度', 'scitem_52' , false , $code_5stages,
                      [ 'name' => 'scitem_52', 'ng-model' => 'dispatchhr.scitem_52' ], 46) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('勤務態度', 'scitem_53' , false , $code_5stages,
                      [ 'name' => 'scitem_53', 'ng-model' => 'dispatchhr.scitem_53' ], 47) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('チームワーク', 'scitem_54' , false , $code_5stages,
                      [ 'name' => 'scitem_54', 'ng-model' => 'dispatchhr.scitem_54' ], 48) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('コミュニケーション/異文化適応力', 'scitem_55' , false , $code_5stages,
                      [ 'name' => 'scitem_55', 'ng-model' => 'dispatchhr.scitem_55' ], 49) !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailRadio('組織運営への協力度/参画度、理解度', 'scitem_56' , false , $code_5stages,
                      [ 'name' => 'scitem_56', 'ng-model' => 'dispatchhr.scitem_56' ], 50) !!}

                    {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('コメント',false,
                      ['id'=>'scitem_57', 'ng-model'=>'dispatchhr.scitem_57', 'placeholder' =>'コメント', 'maxlength' => '256', 'rows'=>'2'], 51); !!}
                    {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '担当者', 'scitem_58', false ,
                      [ 'id'=>'scitem_58', 'ng-model'=>'dispatchhr.scitem_58', 'placeholder' =>'担当者', 'maxlength' => '128' ], 52) !!}

                    <div class="form-group" ng-if="dispsetting.set_53">
                      <div class="row">
                        {!! \App\Http\Utils\DispatchUtils::setTitleLabel('個人情報出力',[],53) !!}
                        {!! \App\Http\Utils\DispatchUtils::showCheckBoxOnly('scitem_59', 'dispatchhr.scitem_59', ['cols'=>8],
                          '紹介表に個人情報（名前、連絡先、住所など）を出力する。', []) !!}
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div class="tab-pane" id="workhistory"
                ng-class="{active: showTab =='workhistory', fade: showTab !='workhistory' }">
                <div class="card">
                  <div class="card-body" id="workhistory">

                  </div>
                </div>
              </div>
              <div class="tab-pane" id="salaryinfo"
                ng-class="{active: showTab =='salaryinfo', fade: showTab !='salaryinfo' }">
                <div class="card">
                  <div class="card-body" id="salaryinfo">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="socialinsurance"
                ng-class="{active: showTab =='socialinsurance', fade: showTab !='socialinsurance' }">
                <div class="card">
                  <div class="card-body" id="socialinsurance">

                  </div>
                </div>
              </div>
              <div class="tab-pane" id="taxrelated"
                ng-class="{active: showTab =='taxrelated', fade: showTab !='taxrelated' }">
                <div class="card">
                  <div class="card-body" id="taxrelated">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal footer -->
          <div class="modal-footer">
            <ng-template ng-show="!dispatchhr_id && setting">
              @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE])
              <button type="button" class="btn btn-success" ng-click="savesetting()">
                <i class="far fa-save"></i> 登録
              </button>
              @endcanany
            </ng-template>
            <ng-template ng-show="!dispatchhr_id && !setting">
              @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE])
              <button type="submit" class="btn btn-success" ng-click="save()">
                <i class="far fa-save"></i> 登録
              </button>
              @endcanany
            </ng-template>
            <ng-template ng-show="dispatchhr_id && !setting">
              @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_UPDATE])
              <button type="submit" class="btn btn-success" ng-click="save()">
                <i class="far fa-save"></i> 更新
              </button>
              @endcanany
            </ng-template>

            @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_DELETE])
            <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="dispatchhr_id">
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
  <form class="form_edit_jobcareer" action="" method="" onsubmit="return false;">
        <div class="modal modal-child" id="modalDetailJobcareer" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!jobcareer_id">職歴登録</h4>
                        <h4 class="modal-title" ng-show="jobcareer_id">職歴更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        {!! \App\Http\Utils\DispatchUtils::showDetailMonthFromTo('期間', true ,
                          [ 'id'=>'work_startym', 'ng-model'=>'jobcareer.work_startym', 'placeholder'=>'yyyy/MM'],
                          [ 'id'=>'work_toym', 'ng-model'=>'jobcareer.work_toym', 'placeholder'=>'yyyy/MM']) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '会社と部署', 'company_department', true ,
                          [ 'id'=>'company_department', 'ng-model'=>'jobcareer.company_department', 'placeholder' =>'会社と部署', 'maxlength' => '256' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '業種', 'industry', true ,
                            [ 'id'=>'industry', 'ng-model'=>'jobcareer.industry', 'placeholder' =>'業種', 'maxlength' => '128' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailRadio('就業形態', 'employment' , true , $code_employment,
                          [ 'name' => 'employment', 'ng-model' => 'jobcareer.employment' ]) !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailTextArea('業務内容',true,
                          ['id'=>'business_content', 'ng-model'=>'jobcareer.business_content', 'placeholder' =>'業務内容', 'maxlength' => '512', 'rows'=>'5']); !!}

                        {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '給与', 'salary', false ,
                          [ 'id'=>'salary', 'ng-model'=>'jobcareer.salary', 'placeholder' =>'給与', 'maxlength' => '128' ]) !!}

                          {!! \App\Http\Utils\DispatchUtils::showDetailInputText('text', '退職理由', 'retirement_reason', false ,
                          [ 'id'=>'retirement_reason', 'ng-model'=>'jobcareer.retirement_reason', 'placeholder' =>'退職理由', 'maxlength' => '128' ]) !!}

                    </div>

                    <div class="modal-footer">
                        <ng-template ng-show="!jobcareer_id">
                            @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_CREATE])
                            <button type="submit" class="btn btn-success" ng-click="saveJobcareer()">
                                <ng-template><i class="far fa-save"></i> 登録</ng-template>
                            </button>
                            @endcanany
                        </ng-template>
                        <ng-template ng-show="jobcareer_id">
                            @canany([PermissionUtils::PERMISSION_DISPATCHHR_SETTING_UPDATE])
                            <button type="submit" class="btn btn-success" ng-click="saveJobcareer()">
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
</div>

@push('scripts')
  <script>
    if(appPacAdmin){
      appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) { 
        $scope.showTab = localStorage.getItem('dispatchhrtitle.tab');
        if(!$scope.showTab) $scope.showTab = 'basic';
        localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
        $scope.dispsetting = [];
        $scope.settinginfo = {!! json_encode($settinginfo) !!};
        $scope.option_limit = [20,50,100];
        $scope.dispatchhr = {};
        $scope.setting = false;
        $scope.jobcareer=[];
        $scope.dispatchhr_id = 0;
        $scope.jobcareer_id = 0;
        $scope.job_numChecked = 0;
        setDispSetting();
        $scope.onShowTab = function(tab){
          $isdisabled = $('#link_'+tab).hasClass('disabled');
          if ($isdisabled){
            $scope.showTab = localStorage.getItem('dispatchhrtitle.tab');
          }else{
            $scope.showTab = tab;
          }
          localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
        }

        $rootScope.$on("openNewDispatchHR", function(event){
          $scope.setting = false;
          setDispSetting();
          $scope.dispatchhr_id = 0;
          $scope.showTab = 'basic';
          localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
          setDefaultDispatchHR();
          if(allow_create) $scope.readonly = false;
          else $scope.readonly = true;
          const settinginfo = {!! json_encode($settinginfo) !!};

          hideMessages();
          hasChange = false;
          $("#modalDetailItem").modal();
        });
        $rootScope.$on("openSettingDispatchHR", function(event){
          $scope.setting = true;
          setDispSetting();
          $scope.dispatchhr_id = 0;
          $scope.showTab = 'basic';
          localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
          setDefaultDispatchHR();
          $scope.readonly = true;
          hideMessages();
          hasChange = false;
          $("#modalDetailItem").modal();
        });
        $rootScope.$on("openEditDispatchHR", function(event, data){
          $scope.setting = false;
          setDispSetting();
          $scope.dispatchhr_id = data.id;
          $scope.showTab = 'basic';
          localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
          if(allow_update) $scope.readonly = false;
          else $scope.readonly = true;
          hideMessages();
          hasChange = false;
          $http.post(link_geteditdata, {
            id: $scope.dispatchhr_id,
            })
          .then(function(event) {
              $rootScope.$emit("hideLoading");
              if(event.data.status == false){
                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
              }else{
                $scope.dispatchhr = event.data.dispatchhr;
                $scope.jobcareer_list = event.data.jobcareer_list;
                setEditDateFormat();

              }
            });
          $("#modalDetailItem").modal();
        });
        $scope.addNewJobcareer = function(){
          $scope.jobcareer_id = 0;
          if(allow_create) $scope.readonly = false;
          else $scope.readonly = true;
          $scope.jobcareer = setJobcareerDefault();

          hideMessages();
          hasChange = false;
          $("#modalDetailJobcareer").modal();
        };
        $rootScope.editJobcareer = function(data){
          $rootScope.$emit("showLoading");
          $scope.jobcareer_id = data.id;
          if(allow_update) $scope.readonly = false;
          else $scope.readonly = true;
          hideMessages();
          hasChange = false;
          $http.post(link_geteditjobcareer, {
              id: $scope.jobcareer_id,
              })
              .then(function(event) {
                  $rootScope.$emit("hideLoading");
                  if(event.data.status == false){
                      $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                  }else{
                      $scope.jobcareer = event.data.jobcareer;
                      setEditDateJobFormat();
                  }
              });
          $("#modalDetailJobcareer").modal();
        };
        $scope.saveJobcareer = function(callSuccess){

          if($(".form_edit_jobcareer")[0].checkValidity()) {
            hideMessages();
            $rootScope.$emit("showLoading");
            var saveSuccess = function(event){                          
              $rootScope.$emit("hideLoading");
              if(event.data.status == false){
                $("#modalDetailJobcareer .message-info").append(showMessages(event.data.message, 'danger', 10000));
              }else{   
                $("#modalDetailJobcareer .message-info").append(showMessages(event.data.message, 'success', 10000));
                hasChange = true;
                if(callSuccess) callSuccess();                           
                $("#modalDetailJobcareer").modal('hide');
                $scope.jobcareer_list = event.data.jobcareer_list;
              }
            };
            $http.post(link_savejobcareer, {item: $scope.jobcareer})
              .then(saveSuccess);
          }

        };
        $scope.deleteJobcareer = function(event){

          var cids = [];
          for(var i =0; i < $(".job_cid:checked").length; i++){
              cids.push($(".job_cid:checked")[i].value);
          }
          $rootScope.$emit("showMocalConfirm",
              {
                  title:'選択した職歴を削除します。よろしいですか？',
                  btnDanger:'削除',
                  callDanger: function(){
                      $rootScope.$emit("showLoading");
                      $http.post(link_deletejobcareer, { cids: cids, dispatchhrid:$scope.dispatchhr_id})
                          .then(function(event) {
                              $rootScope.$emit("hideLoading");
                              if(event.data.status == false){
                                  $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                              }else{
                                  $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                  $scope.jobcareer_list = event.data.jobcareer_list;
                                }
                          });
                  }
              });

      };


        $scope.save = function(callSuccess){
          $scope.dispatchhr.id = $scope.dispatchhr_id;
          $scope.showTab = 'basic';
          localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
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

            };
            $http.post(link_save, {item: $scope.dispatchhr})
              .then(saveSuccess);
          }

        };
        $scope.remove = function(){
          var cids = [];
          cids.push($scope.dispatchhr_id);

          $rootScope.$emit("showMocalConfirm",
            {
              title:'人材情報を削除します。よろしいですか？',
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
        $scope.savesetting = function(callSuccess){
        
          $scope.showTab = 'basic';
          localStorage.setItem('dispatchhrtitle.tab', $scope.showTab);
          var cids = [];
          for(var i =0; i < $(".cid:checked").length; i++){
              cids.push($(".cid:checked")[i].value);
          }
                  
          $rootScope.$emit("showLoading");
          $http.post(link_savesetting, { cids: cids})
            .then(function(event) {
              $rootScope.$emit("hideLoading");
              if(event.data.status == false){
                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
              }else{
                location.reload();
                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
              }
            });

        };
        $scope.getAddress=function(){
          $scope.dispatchhr.address1=""
          $http({
            method:'get',
            url:'setting-user/get-address',
            params:{zipcode:$scope.dispatchhr.postal_code}
          }).then(function (event){
            let res=event.data.results;
            if (res!=null){
                $scope.dispatchhr.address1=res[0].address1+res[0].address2+res[0].address3
            }

          })
        }
        $scope.range_func = function(n) {
          return new Array(n);
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
          const item = [];
          $code_data.forEach(function (value, index) {
              const work = {};
              work['checked']= 0;
              work['id']= value.id;
              item.push(work);
          });
          return item;
        }
        function setDefaultDispatchHR(){
          
          const scitem_33 = setDefaultCheckbox({!! json_encode($code_worklocation) !!});
          const scitem_34 = setDefaultCheckbox({!! json_encode($code_employmentform) !!});
          const scitem_35 = setDefaultCheckbox({!! json_encode($code_worklocation) !!});
          const scitem_36 = setDefaultCheckbox({!! json_encode($code_desiredjob) !!});
          const scitem_40 = setDefaultCheckbox({!! json_encode($code_experiencedjob) !!});
          const scitem_47 = setDefaultRadio({!! json_encode($code_basicmanner) !!});
          const scitem_48 = setDefaultRadio({!! json_encode($code_worklocation) !!});
          const scitem_49 = setDefaultRadio({!! json_encode($code_teamwork) !!});
          const scitem_50 = setDefaultRadio({!! json_encode($code_communication) !!});
          const scitem_51 = setDefaultRadio({!! json_encode($code_cooperation) !!});

          $scope.dispatchhr = {
            // scitem_33:scitem_33,
            // scitem_34:scitem_34,
            // scitem_35:scitem_35,
            // scitem_36:scitem_36,
            // scitem_40:scitem_40,
            // scitem_47:scitem_47,
            // scitem_48:scitem_48,
            // scitem_49:scitem_49,
            // scitem_50:scitem_50,
            // scitem_51:scitem_51,
          };
        } 

        function setEditDateFormat(){
          if ($scope.dispatchhr.birthdate)$scope.dispatchhr.birthdate = new Date($scope.dispatchhr.birthdate);
          if ($scope.dispatchhr.scitem_4)$scope.dispatchhr.scitem_4 = new Date($scope.dispatchhr.scitem_4);
          if ($scope.dispatchhr.scitem_5)$scope.dispatchhr.scitem_5 = new Date($scope.dispatchhr.scitem_5);
          if ($scope.dispatchhr.scitem_6)$scope.dispatchhr.scitem_6 = new Date($scope.dispatchhr.scitem_6);
          if ($scope.dispatchhr.scitem_7)$scope.dispatchhr.scitem_7 = new Date($scope.dispatchhr.scitem_7);
          if ($scope.dispatchhr.scitem_9)$scope.dispatchhr.scitem_9 = new Date($scope.dispatchhr.scitem_9);
          if ($scope.dispatchhr.scitem_14)$scope.dispatchhr.scitem_14 = new Date($scope.dispatchhr.scitem_14);
          if ($scope.dispatchhr.scitem_15)$scope.dispatchhr.scitem_15 = new Date($scope.dispatchhr.scitem_15);
          if ($scope.dispatchhr.scitem_32)$scope.dispatchhr.scitem_32 = new Date($scope.dispatchhr.scitem_32);

        }
        function setEditDateJobFormat(){
          if ($scope.jobcareer.work_startym)$scope.jobcareer.work_startym = new Date($scope.jobcareer.work_startym);
          if ($scope.jobcareer.work_toym)$scope.jobcareer.work_toym = new Date($scope.jobcareer.work_toym);

        }

        function setDispSetting(){
            $scope.dispsetting = [];
            const settingdata = [];
            if ($scope.setting){
                $scope.dispsetting = {!! json_encode($templateinfo) !!};
            }else{
                $scope.dispsetting = {!! json_encode($settinginfo) !!};
            }
        }
        function setJobcareerDefault(){
            var employment = 0;
            const code_employment = {!! json_encode($code_employment) !!}; 
            code_employment.forEach(function (value, index) {
                if(index===0) employment = value.id;
            });
            return {
                id:0, 
                dispatchhr_id:$scope.dispatchhr_id, 
                work_startym:"", 
                work_toym:"", 
                company_department:"", 
                industry:"",
                employment:employment,
                business_content:"",
                salary:"",
                retirement_reason:"",
            };
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
    .checkitem{
      width: 50%;
      float: left;
    }
      
  </style>
@endpush