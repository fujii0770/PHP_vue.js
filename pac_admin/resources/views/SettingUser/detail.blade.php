<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;" ng-keydown="getKeyDownList()"  onkeydown="if(event.keyCode==13){return false;}">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!item.id">利用者情報登録</h4>
                        <h4 class="modal-title" ng-show="item.id">利用者情報更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>

                        <div class="card">
                            <div class="card-header">利用者詳細</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row" ng-if="item.id">
                                        <label for="user_id" class="col-md-2 col-sm-2 col-12 text-right-lg">ID </label>
                                        <div class="col-md-8 col-sm-8 col-12">
                                            <p id="user_id"><% item.id %></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="email" class="col-md-2 col-sm-2 col-12 text-right-lg">メールアドレス <span style="color: red" ng-show="!item.id">*</span></label>
                                        <div class="col-md-8 col-sm-8 col-12">
                                            <div class="input-group">
                                                <input type="text" class="form-control" ng-model="email.name" id="email" ng-disabled="item.id"
                                                       required placeholder="email" ng-readonly="readonly" maxlength="256" />
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">@</span>
                                                </div>
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($email_domain_company , 'emaildomain', '',
                                                        'ドメインを選択してください',['class'=> 'form-control', 'ng-model' => "email.domain",'ng-disabled'=> 'item.id','ng-show'=> '!item.id', 'required' => true,'minlength' => 1]) !!}
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($company_domain_include_without_email , 'emaildomain', '',
                                                        'ドメインを選択してください',['class'=> 'form-control', 'ng-model' => "email.domain",'ng-disabled'=> 'item.id','ng-show'=> 'item.id', 'required' => true,'minlength' => 1]) !!}

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" ng-show="{{$company->without_email_flg}}">
                                    <div class="row">
                                        <label for="without_email_flg" class="col-md-2 col-sm-2 col-12 control-label">メールアドレス無し</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="checkbox" ng-model="item.without_email_flg" id="without_email_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.id > 0" ng-change="change()"/>
                                            <label for="without_email_flg" class="control-label">有効にする</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="given_name" class="col-md-2 col-sm-2 col-12 control-label">氏名 <span style="color: red">*</span></label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="text" class="form-control" ng-model="item.family_name" id="family_name"
                                                   required placeholder="姓" ng-readonly="readonly" maxlength="64" />
                                        </div>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="text"  class="form-control" ng-model="item.given_name" id="given_name"
                                                   required placeholder="名" ng-readonly="readonly"  maxlength="64" />
                                        </div>
                                        <label class="col-md-2 col-sm-2 col-5 control-label" ng-if="item.id">パスワード</label>
                                        <div class="col-md-4 col-sm-4 col-7" ng-if="item.id">
                                            <label for="" class="label"><% item.passwordStatus==0?'未設定':'設定済' %></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {{--PAC_5-1599 追加部署と役職 Start--}}
                                    @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                    <div class="row">
                                        <div class="col-md-2 col-sm-2"></div>
                                        <div class="col-md-3 col-sm-3 col-5">
                                            <div class="row">
                                                <label for="mst_department_id" class="col-md-12 col-sm-12 col-12 control-label" style="text-align: left;">部署</label>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12 col-12">
                                                    {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'mst_department_id', '','',
                                                        ['class'=> 'form-control', 'ng-model' =>'item.info.mst_department_id', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-5">
                                            <div class="row">
                                                <label for="mst_position_id" class="col-md-12 col-sm-12 col-12 control-label" style="text-align: left;">役職</label>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12 col-12">
                                                    {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'mst_position_id', '','',
                                                        ['class'=> 'form-control', 'ng-model' =>'item.info.mst_position_id', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-2 col-2">
                                            <div class="row">
                                                <label class="col-md-12 col-sm-12 control-label">&nbsp;</label>
                                            </div>
                                            <div class="row form-control border-0" ng-if="department_position.length < 2">
                                                <div class="col-md-12 col-sm-12 col-12">
                                                    <div v-tooltip.top-center="'追加'" ng-click="addDepartmentPosition()" style="cursor: pointer;"> <i class="fas fa-plus"></i></div>
                                                </div>
                                            </div>
                                            <div class="row form-control border-0 col-md-12 col-sm-12" ng-if="department_position.length >= 2"></div>
                                        </div>
                                    </div>
                                    <div class="row margin-top-5" ng-repeat="(index,department) in department_position track by $index">
                                        <div class="col-md-2 col-sm-2"></div>
                                        <div class="col-md-3 col-sm-3 col-5">
                                            <div class="row">
                                                <label for="mst_department_id_<%(index+1)%>" class="col-md-12 col-sm-12 col-12 control-label" style="text-align: left;">部署<% (index+2) %></label>
                                            </div>
                                            <div class="row">
                                                <div ng-if="index===0" class="col-md-12 col-sm-12 col-12">
                                                    {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'mst_department_id_1', '','',
                                                        ['class'=> 'form-control', 'ng-model' =>'item.info.mst_department_id_1', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                                </div>
                                                <div ng-if="index===1" class="col-md-12 col-sm-12 col-12">
                                                    {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'mst_department_id_2', '','',
                                                        ['class'=> 'form-control', 'ng-model' =>'item.info.mst_department_id_2', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-5">
                                            <div class="row">
                                                <label for="mst_position_id_<%(index+1)%>" class="col-md-12 col-sm-12 col-12 control-label" style="text-align: left;">役職<% (index+2) %></label>
                                            </div>
                                            <div class="row">
                                                <div ng-if="index===0" class="col-md-12 col-sm-12 col-12">
                                                    {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'mst_position_id_1', '','',
                                                        ['class'=> 'form-control', 'ng-model' =>'item.info.mst_position_id_1', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                                </div>
                                                <div ng-if="index===1" class="col-md-12 col-sm-12 col-12">
                                                    {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'mst_position_id_2', '','',
                                                        ['class'=> 'form-control', 'ng-model' =>'item.info.mst_position_id_2', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-sm-2 col-2">
                                            <div class="row">
                                                <label class="col-md-12 col-sm-12 control-label">&nbsp;</label>
                                            </div>
                                            <div class="row form-control border-0">
                                                <div class="col-md-12 col-sm-12 col-12">
                                                    <div v-tooltip.top-center="'削除'" ng-click="delDepartmentPosition(index)" style="cursor: pointer;"> <i class="fas fa-minus"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <div class="row">
                                            <label for="mst_department_id" class="col-md-2 col-sm-2 col-12 control-label">部署</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'mst_department_id', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item.info.mst_department_id', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                            </div>
                                            <label for="mst_position_id" class="col-md-2 col-sm-2 col-12 control-label">役職</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'mst_position_id', '','',
                                                    ['class'=> 'form-control', 'ng-model' =>'item.info.mst_position_id', 'ng-readonly'=>"readonly", 'ng-change'=>'departmentPostionChange()']) !!}
                                            </div>
                                        </div>
                                    @endif
                                    {{--PAC_5-1599 End--}}
                                </div>


                                <div class="form-group">
                                    <div class="row">
                                        <label for="postal_code" class="col-md-2 col-sm-2 col-12 control-label">郵便番号</label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="text" class="form-control" id="postal_code" ng-model="item.info.postal_code"
                                                   placeholder="000-0000" ng-readonly="readonly" ng-change="getAddress()"  maxlength="10" />
                                        </div>
                                        <label for="postal_code" class="col-md-8 col-sm-8 col-12 label text-left"> 郵便番号(ハイフンあり・なし両方)を入力すると住所が入力されます。</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="address" class="col-md-2 col-sm-2 col-12 control-label">住所</label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <input type="text" class="form-control" id="address" ng-model="item.info.address"
                                                   ng-readonly="readonly"  maxlength="128" />
                                        </div>
                                    </div>
                                </div>
                                
                                {{--PAC_5-3018 S--}}
                                <div class="form-group">
                                    <div class="row">
                                        <label for="phone_number" class="col-md-2 col-sm-2 col-12 control-label">電話番号（外線）</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.info.phone_number" id="phone_number"
                                                   placeholder="000-0000-00000" ng-readonly="readonly" maxlength="15" />
                                        </div>

                                        <label for="phone_number_extension" class="col-md-2 col-sm-2 col-12 control-label">電話番号（内線）</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.info.phone_number_extension" id="phone_number_extension"
                                                   placeholder="000-0000-00000"  ng-readonly="readonly"  maxlength="15" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="phone_number_mobile" class="col-md-2 col-sm-2 col-12 control-label">電話番号（携帯）</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.info.phone_number_mobile" id="phone_number_mobile"
                                                   placeholder="000-0000-00000" ng-readonly="readonly" maxlength="15" />
                                        </div>

                                        <label for="fax_number" class="col-md-2 col-sm-2 col-12 control-label">FAX番号</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.info.fax_number" id="fax_number"
                                                   placeholder="000-0000-00000"  ng-readonly="readonly"  maxlength="15" />
                                        </div>
                                    </div>
                                </div>
                                {{--PAC_5-3018 E--}}

                                <div class="form-group">
                                    <div class="row">
                                        <label for="date_stamp_config_1" class="col-md-2 col-sm-2 col-12 control-label">日付印の日付</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <label for="date_stamp_config_1" class="control-label">
                                                <input type="radio" id="date_stamp_config_1" ng-model="item.info.date_stamp_config"
                                                       ng-readonly="readonly" name=date_stamp_config value="1" /> 任意の日付　
                                            </label>

                                            <label for="date_stamp_config_0" class="control-label">
                                                <input type="radio" id="date_stamp_config_0" ng-model="item.info.date_stamp_config"
                                                       ng-readonly="readonly" name=date_stamp_config value="0" /> 当日のみ
                                            </label>

                                        </div>
                                    </div>
                                </div>

                                <div ng-if='item.company_enable_email==1 && item.without_email_flg!=1'>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="enable_email" class="col-md-2 col-sm-2 col-12 control-label" >メール</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <input type="radio" ng-model="item.info.enable_email" id="enable_email-1" ng-value="1" />
                                                <label for="enable_email-1" class="control-label">有効</label>
                                                <input type="radio" ng-model="item.info.enable_email" id="enable_email-0" ng-value="0" />
                                                <label for="enable_email-0" class="control-label">無効</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if='item.info.enable_email==1'>
                                        <div class="row">
                                            <label for="email_format" class="col-md-2 col-sm-2 col-12 control-label" >メールフォーマット</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <input type="radio" ng-model="item.info.email_format" id="email_format-1" ng-value="1" />
                                                <label for="email_format-1" class="control-label">HTML</label>
                                                <input type="radio" ng-model="item.info.email_format" id="email_format-0" ng-value="0" />
                                                <label for="email_format-0" class="control-label">テキスト</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="row">
                                        <!-- 活性/非活性： -->
                                        <!-- !item.id：新規時非活性 -->
                                        <!-- 印鑑を全削除する時、無効に更新するが、有効にする可能 -->
                                        <label for="state_flg_1" class="col-md-2 col-sm-2 col-12 control-label">状態</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <label for="state_flg_1" class="control-label">
                                                <input type="radio" id="state_flg_1" ng-model="item.state_flg"
                                                       ng-readonly="readonly" ng-disabled="item.stamps.stampMaster.length == 0 && (default_stamp_flg == 0 || (default_stamp_flg == 1 && (item.stamps.stampCompany.length + item.stamps.stampDepartment.length) == 0)) || !item.id" name=state_flg value="1" /> 有効
                                            </label>

                                            <label for="state_flg_09">
                                                <input type="radio" ng-model="item.state_flg" ng-if="!item.id || !item.passwordStatus"
                                                       ng-disabled="item.stamps.stampMaster.length == 0 && (default_stamp_flg == 0 || (default_stamp_flg == 1 && (item.stamps.stampCompany.length + item.stamps.stampDepartment.length) == 0)) || !item.id"  value="0" id="state_flg_09">
                                                <input type="radio" ng-model="item.state_flg"  ng-if="item.id && item.passwordStatus"
                                                       ng-disabled="item.stamps.stampMaster.length == 0 && (default_stamp_flg == 0 || (default_stamp_flg == 1 && (item.stamps.stampCompany.length + item.stamps.stampDepartment.length) == 0)) || !item.id" value="9" id="state_flg_09">
                                                無効</label>

                                        </div>
                                    </div>
                                </div>

{{--                                @if($company->mfa_flg)--}}
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="mfa_type_2" class="col-md-2 col-sm-2 col-12 control-label">多要素認証</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <label for="mfa_type_2" class="control-label">
                                                    <input type="radio" id="mfa_type_2" ng-model="item.info.mfa_type"
                                                           ng-readonly="readonly" name="mfa_type" value="2" ng-disabled="{{!$company->mfa_flg}}" /> QRコード
                                                </label>
                                                <label for="mfa_type_1" class="control-label" ng-if='item.company_enable_email==1 && item.info.enable_email==1 && item.without_email_flg!=1'>
                                                    <input type="radio" id="mfa_type_1" ng-model="item.info.mfa_type"
                                                           ng-readonly="readonly" name="mfa_type" value="1" ng-disabled="{{!$company->mfa_flg}}" /> メール
                                                </label>
                                                <label for="mfa_type_0" class="control-label">
                                                    <input type="radio" id="mfa_type_0" ng-model="item.info.mfa_type"
                                                           ng-readonly="readonly" name="mfa_type" value="0" ng-disabled="{{!$company->mfa_flg}}" /> 無効
                                                </label>
                                            </div>

                                            <label for="email_auth_dest_flg_0" class="col-md-2 col-sm-2 col-12 control-label" ng-if='item.without_email_flg!=1'>認証コード送信先</label>
                                            <div class="col-md-4 col-sm-4 col-12" ng-if='item.without_email_flg!=1'>
                                                <label for="email_auth_dest_flg_0" class="control-label">
                                                    <input type="radio" id="email_auth_dest_flg_0" ng-model="item.info.email_auth_dest_flg"
                                                           ng-readonly="readonly" name="email_auth_dest_flg" value="0" ng-disabled="item.info.mfa_type!=1" /> 登録メールアドレス
                                                </label>
                                                <label for="email_auth_dest_flg_1" class="control-label">
                                                    <input type="radio" id="email_auth_dest_flg_1" ng-model="item.info.email_auth_dest_flg"
                                                           ng-readonly="readonly" name="email_auth_dest_flg" value="1" ng-disabled="item.info.mfa_type!=1" /> その他
                                                </label>
                                            </label>
                                            <input type="text" class="form-control" ng-model="item.info.auth_email"
                                                   placeholder="email@example.com"  ng-readonly="readonly"  maxlength="256" ng-disabled="item.info.email_auth_dest_flg==0 || item.info.mfa_type!=1" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="time_stamp_permission_01" class="col-md-2 col-sm-2 col-12 control-label pl-0">タイムスタンプ発行権限</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <label for="time_stamp_permission_01" class="control-label">
                                                <input type="radio" id="time_stamp_permission_01" ng-model="item.info.time_stamp_permission"
                                                       ng-readonly="readonly" name="time_stamp_permission" value="1" ng-disabled="{{!$company->stamp_flg}}" /> 有効
                                            </label>
                                            <label for="time_stamp_permission_00" class="control-label">
                                                <input type="radio" id="time_stamp_permission_00" ng-model="item.info.time_stamp_permission"
                                                       ng-readonly="readonly" name="time_stamp_permission" value="0" ng-disabled="{{!$company->stamp_flg}}" /> 無効
                                            </label>

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-2 col-sm-2 col-12 control-label">共通印</label>
                                        <label class="col-md-10 col-sm-10 col-12 control-label text-left" ng-show="!item.stamps.stampCompany.length && !item.stamps.stampCompany.length">※割当て済みの共通印がありません※
                                        </label>
                                        <div class="stamp-list" ng-if="item.stamps.stampCompany.length || item.stamps.stampCompany.length">
                                            <div class="stamp-item stamp-item-<% stamp.assign_id %>" ng-repeat="(key, stamp) in item.stamps.stampCompany">
                                                <div class="thumb">
                                            <span class="thumb-img">
                                                <img ng-src="data:image/png;base64,<% stamp.stamp_company.stamp_image %>" class="stamp-image" />
                                            </span>

                                                </div>
                                                <div class="mt-3" style="width: 80px;"><% stamp.stamp_company.stamp_name ? stamp.stamp_company.stamp_name : '名称未設定'%></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($company->template_flg)
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="state_flg_1" class="col-md-2 col-sm-2 col-12 control-label">テンプレート機能</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <label for="template_flg_1" class="control-label">
                                                    <input type="radio" id="template_flg_1"
                                                           ng-model="item.info.template_flg"
                                                           ng-readonly="readonly" name=template_flg value="1"/> ON　
                                                </label>

                                                <label for="template_flg_0" class="control-label">
                                                    <input type="radio" id="template_flg_0"
                                                           ng-model="item.info.template_flg"
                                                           ng-readonly="readonly" name=template_flg value="0"/> OFF
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <!-- PAC_5-1264 おじぎ印ラジオボタン追加　▼ -->
                                <div class="form-group">
                                    <div class="row">
                                        <label for="rotate_angle_flg" class="col-md-2 col-sm-2 col-12 control-label">おじぎ印</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <label for="rotate_angle_flg_1" class="control-label">
                                                <input type="radio" id="rotate_angle_flg_1" ng-model="item.info.rotate_angle_flg"
                                                       ng-readonly="readonly" name=rotate_angle_flg value="1" ng-disabled="{{!$company->rotate_angle_flg}}" /> ON　
                                            </label>
                                            <label for="rotate_angle_flg_0" class="control-label">
                                                <input type="radio" id="rotate_angle_flg_0" ng-model="item.info.rotate_angle_flg"
                                                       ng-readonly="readonly" name=rotate_angle_flg value="0" ng-disabled="{{!$company->rotate_angle_flg}}" /> OFF
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- PAC_5-1264 おじぎ印ラジオボタン追加　▲ -->
                                @if ($company->sticky_note_flg)
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="sticky_note_flg" class="col-md-2 col-sm-2 col-12 control-label">付箋機能</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <label for="sticky_note_flg_1" class="control-label">
                                                    <input type="radio" id="sticky_note_flg_1"
                                                           ng-model="item.info.sticky_note_flg"
                                                           ng-readonly="readonly" name=sticky_note_flg value="1"
                                                           ng-disabled="!item.stamps.stampMaster.length && !item.stamps.stampDepartment.length && !item.stamps.stampWaitDepartment.length"/>
                                                    ON　
                                                </label>
                                                <label for="sticky_note_flg_0" class="control-label">
                                                    <input type="radio" id="sticky_note_flg_0"
                                                           ng-model="item.info.sticky_note_flg"
                                                           ng-readonly="readonly" name=sticky_note_flg value="0"
                                                           ng-disabled="!item.stamps.stampMaster.length && !item.stamps.stampDepartment.length && !item.stamps.stampWaitDepartment.length"/>
                                                    OFF
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="message message-stamp mt-2"></div>

                        <div class="card mt-3" ng-show="item.id">
                            <div class="card-header">登録済印面</div>
                            <div class="card-body">
                                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])
                                <div class="text-right">
                                    <!-- 活性/非活性： -->
                                    <!-- trial(3)、Standard(0)：氏名印の登録がない場合、部署名入り日付印登録不可 -->
                                    <!-- Business以上：氏名印の登録がない場合でも、部署名入り日付印登録可能 -->
                                    <button type="button" class="btn btn-success" ng-if="department_stamp_flg" ng-click="showFormDepartmentStamp()"
                                            ng-disabled="count_stamp_date === 0 && count_stamp_name === 0 && (contract_edition == 0 || contract_edition == 3)"><i class="fas fa-plus-circle" ></i> 部署名入り日付印</button>
                                    <div class="btn btn-success" ng-click="showFormUserStamp()"><i class="fas fa-plus-circle" ></i> 追加</div>
                                </div>
                                @endcanany

                                <div class="stamps">
                                    <div class="col-md-12 col-sm-12 col-12">登録されている氏名印一覧</div>
                                    <div ng-show="!item.stamps.stampMaster.length && !item.stamps.stampDepartment.length && !item.stamps.stampWaitDepartment.length">※印面は登録されていません※</div>
                                    <div class="stamp-list" ng-if="item.stamps.stampMaster.length || item.stamps.stampDepartment.length || item.stamps.stampWaitDepartment.length">
                                        <div class="stamp-item stamp-item-<% stamp.assign_id %>" ng-repeat="(key, stamp) in item.stamps.stampMaster">
                                            <div class="thumb">
                                            <span class="thumb-img">
                                                <img ng-src="data:image/png;base64,<% stamp.stamp_master.stamp_image %>" class="stamp-image" />
                                            </span>
                                                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])
                                                {{-- タイムスタンプ編集ボタン表示：企業ON＋利用者OFF --}}
                                                <button class="btn btn-edit btn-circle" ng-click="editStamp(stamp.assign_id)" ng-if="company_stamp_flg && item.info.time_stamp_permission==0">
                                                    <i class="far fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-circle" ng-click="removeStamp(stamp.assign_id,0)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endcanany
                                            </div>
                                        </div>

                                        <div class="stamp-item stamp-item-<% stamp.assign_id %>" ng-repeat="(key, stamp) in item.stamps.stampDepartment">
                                            <div class="thumb">
                                            <span class="thumb-img">
                                                <img ng-if="stamp.state_flg === 2" class="stamp-image" src="{{ asset('images/stamp_wait.png') }}" ng-click="showStampInfo(stamp)" />
                                                <img ng-if="stamp.state_flg !== 2" ng-src="data:image/png;base64,<% stamp.stamp_department.stamp_image %>" class="stamp-image" />
                                            </span>
                                                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])
                                                    {{-- タイムスタンプ編集ボタン表示：企業ON＋利用者OFF --}}
                                                <span class="btn btn-edit btn-circle" ng-click="editStamp(stamp.assign_id)" ng-if="company_stamp_flg && item.info.time_stamp_permission==0">
                                                <i class="far fa-edit"></i>
                                            </span>
                                                <span class="btn btn-danger btn-circle" ng-click="removeStamp(stamp.assign_id,2)">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                                @endcanany
                                            </div>
                                        </div>

                                        <div class="stamp-item stamp-item-<% stamp.assign_id %>" ng-repeat="(key, stamp) in item.stamps.stampWaitDepartment">
                                            <div class="thumb">
                                            <span class="thumb-img">
                                                <img ng-if="stamp.state_flg === 2" class="stamp-image" src="{{ asset('images/stamp_wait.png') }}" ng-click="showStampInfo(stamp)" />
                                                <img ng-if="stamp.state_flg !== 2" ng-src="data:image/png;base64,<% stamp.stamp_department.stamp_image %>" class="stamp-image" />
                                            </span>
                                                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])
                                                    {{-- タイムスタンプ編集ボタン表示：企業ON＋利用者OFF --}}
                                                    <span class="btn btn-edit btn-circle" ng-click="editStamp(stamp.assign_id)" ng-if="company_stamp_flg && item.info.time_stamp_permission==0">
                                                <i class="far fa-edit"></i>
                                            </span>
                                                    <span class="btn btn-danger btn-circle" ng-click="removeStamp(stamp.assign_id,3)">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                                @endcanany
                                            </div>
                                        </div>

                                        <div class="stamp-item" ng-if="item.info.sticky_note_flg == 1" >
                                            <span class="thumb-img">
                                                <img src="/admin/images/sticky_icon.svg" class="stamp-image" width="78px" height="78px"/>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="message message-info mt-3"></div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                          @canany([PermissionUtils::PERMISSION_USER_SETTINGS_CREATE])
                             <ng-template ng-show="!item.id">
                            <button type="submit" class="btn btn-success" ng-click="save()">
                                     <i class="far fa-save"></i> 登録
                                 </button>
                             </ng-template>
                                @endcanany
                                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])
                              <ng-template ng-show="item.id">
                                  <button type="submit" class="btn btn-success" ng-click="save()">
                                      <i class="far fa-save"></i> 更新
                            </button>
                              </ng-template>
                        @endcanany

                        @if($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO)
                          <button type="button" class="btn btn-warning" ng-click="resetPassword(true)" ng-show="item.id">
                              <i class="far fa-envelope"></i>ログインURL送信
                          </button>
                        @endif
                        @if($company->login_type != \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO ||
                            ($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO  && $company->use_mobile_app_flg == \App\Http\Utils\AppUtils::FLG_ENABLE))
                        @canany([\App\Http\Utils\PermissionUtils::PERMISSION_USER_SETTINGS_CREATE,\App\Http\Utils\PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])
                              <button type="button" class="btn btn-warning" ng-click="resetPassword()" ng-show="item.id && !item.without_email_flg==1">
                                  <i class="far fa-envelope"></i>
                                  初期パスワード設定
                              </button>
                        @endcanany
{{--                          @if ($company->passreset_type == 1 )--}}
                              <meta name="csrf-token" content="{{ csrf_token() }}">
                              <button type="button" class="btn btn-warning" ng-click="showFormCode()" id="code_user_id" value="<% item.id %>" ng-show="item.id && item.without_email_flg==1">
                                  <i class="fas fa-key"></i> パスワード設定コード発行
                              </button>
{{--                          @endif--}}

                        @endif

                        @canany([PermissionUtils::PERMISSION_USER_SETTINGS_DELETE])
                        <button type="button" class="btn btn-danger" ng-click="remove()" ng-show="item.id">
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

    <form class="form_edit_admin" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-admin" id="modalDetailItemStamp" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">タイムスタンプ設定</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="form-group row">
                            <label for="time_stamp_permission_11" class="control-label col-md-6">タイムスタンプの発行</label>
                            <div class="col-md-6">
                                <label class="label mr-2" for="time_stamp_permission_11">
                                    <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_11" ng-value="1"> 有効
                                </label>
                                <label class="label mr-2" for="time_stamp_permission_10">
                                    <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_10" ng-value="0"> 無効
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-success" ng-model="stamp_id" ng-click="saveTimeStampPermission(stamp_id)">
                            <ng-template><i class="far fa-save"></i> 更新</ng-template>
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </form>

    <form class="formDepartmentStamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-date-stamp mt-3 modal-child" id="modalDepartmentStamp" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">部署名入り日付印登録</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message"></div>

                        <div class="form-horizontal">
                            <div class="row">
                                <div class="col-sm-3 preview-stamp">
                                    <div class="sapo"><% preview_text %></div>
                                    <div class="thumb" ng-if="departmentStamp.stamp">
                                        <img ng-src="data:image/png;base64,<% departmentStamp.stamp.stamp_image %>"  />
                                    </div>
                                </div>
                                <div class="col-sm-9 stamp-param">
                                    <div class="form-group row">
                                        <label for="stamp_type" class="col-sm-5 col-12 control-label">印面種類</label>
                                        <div class="col-sm-7 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STAMP_TYPE, 'stamp_type', '',null,
                                                ['class'=> 'form-control', 'ng-model' =>'departmentStamp.stamp_type', 'ng-readonly'=>"readonly",'ng-change' => 'changeStampType()']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="stamp_layout" class="col-sm-5 col-12 control-label">レイアウト</label>
                                        <div class="col-sm-7 col-12">
                                            <ul ng-init="stamp_layout = {{ json_encode(\App\Http\Utils\AppUtils::STAMP_LAYOUT) }}"></ul>
                                            <select name="stamp_layout" id="stamp_layout" class="form-control"
                                                    ng-model="departmentStamp.stamp_layout" ng-change="changeStampLayout()"
                                                    ng-options="item.value as item.text for item in stamp_layout[departmentStamp.stamp_type]"
                                            >
                                            </select>
                                        </div>
                                    </div>

                                    <div class="faces">
                                        <div ng-if="departmentStamp.stamp_layout == 'E101'">
                                            <div class="form-group row">
                                                <label for="face_up" class="col-sm-5 col-12 control-label" style="line-height: 20px;">上段<br />最大8文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"  maxlength="8"
                                                           ng-model="departmentStamp.face_up" ng-trim="false" ng-readonly="readonly" id="face_up" />
                                                </div>
                                            </div>
                                            <div class="form-group row row-face_down">
                                                <label for="face_down" class="col-sm-5 col-12 control-label"  style="line-height: 20px;">下段  <br />最大8文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="8"
                                                           ng-model="departmentStamp.face_down" ng-trim="false" ng-readonly="readonly" id="face_down" />
                                                </div>
                                            </div>
                                        </div>

                                        <div ng-if="departmentStamp.stamp_layout == 'E0{0}1'">
                                            <div class="form-group row">
                                                <label for="face_up" class="col-sm-5 col-12 control-label" style="line-height: 20px;">上段<br />最大8文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"  maxlength="8"
                                                           ng-model="departmentStamp.face_up" ng-trim="false" ng-readonly="readonly" id="face_up" />
                                                </div>
                                            </div>
                                            <div class="form-group row row-face_down">
                                                <label for="face_down" class="col-sm-5 col-12 control-label"  style="line-height: 20px;">
                                                    下段  <br />最大4文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="4"
                                                           ng-model="departmentStamp.face_down"
                                                           ng-trim="false"
                                                           ng-readonly="readonly" id="face_down" />
                                                </div>
                                            </div>
                                            <div class="form-group row row-face_down1">
                                                <label for="face_down" class="col-sm-5 col-12 control-label"  style="line-height: 20px;">
                                                    下段小さい文字  <br />最大1文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="1"
                                                           ng-model="departmentStamp.face_down1"
                                                           ng-trim="false"
                                                           ng-readonly="readonly" id="face_down1" />
                                                </div>
                                            </div>
                                        </div>

                                        <div ng-if="departmentStamp.stamp_layout == 'E102'">
                                            <div class="form-group row">
                                                <label for="face_up1" class="col-sm-5 col-12 control-label" style="line-height: 20px;">上段(１行目)<br />最大7文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="7"
                                                           ng-model="departmentStamp.face_up1" ng-trim="false" ng-readonly="readonly" id="face_up1" />
                                                </div>
                                            </div>
                                        </div>

                                        <div ng-if="departmentStamp.stamp_layout == 'E201' || departmentStamp.stamp_layout == 'E202'">
                                            <div class="form-group row">
                                                <label for="face_up1" class="col-sm-5 col-12 control-label" style="line-height: 20px;">上段(１行目)<br />最大5文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="5"
                                                           ng-model="departmentStamp.face_up1" ng-trim="false" ng-readonly="readonly" id="face_up1" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="face_up2" class="col-sm-5 col-12 control-label" style="line-height: 20px;">上段(２行目)<br />最大9文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="9"
                                                           ng-model="departmentStamp.face_up2" ng-trim="false" ng-readonly="readonly" id="face_up2" />
                                                </div>
                                            </div>
                                        </div>

                                        <div ng-if="departmentStamp.stamp_layout == 'E201'">
                                            <div class="form-group row">
                                                <label for="face_down1" class="col-sm-5 col-12 control-label" style="line-height: 20px;">下段(１行目)<br />最大7文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control" maxlength="7"
                                                           ng-model="departmentStamp.face_down1" ng-trim="false" ng-readonly="readonly" id="face_down1" />
                                                </div>
                                            </div>
                                        </div>
                                        <div ng-if="departmentStamp.stamp_layout == 'E102' || departmentStamp.stamp_layout == 'E202'">
                                            <div class="form-group row">
                                                <label for="face_down1" class="col-sm-5 col-12 control-label" style="line-height: 20px;">下段(１行目)<br />最大9文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"  maxlength="9"
                                                           ng-model="departmentStamp.face_down1" ng-trim="false" ng-readonly="readonly" id="face_down1" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="face_down2" class="col-sm-5 col-12 control-label" style="line-height: 20px;">下段(２行目)<br />最大5文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"  maxlength="5"
                                                           ng-model="departmentStamp.face_down2" ng-trim="false" ng-readonly="readonly" id="face_down2" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label for="font" class="col-sm-5 col-12 control-label">書体</label>
                                        <div class="col-sm-7 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STAMP_FONT_LABEL, 'font', '',null,
                                                ['class'=> 'form-control', 'ng-model' =>'departmentStamp.font', 'ng-readonly'=>"readonly"]) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="color" class="col-sm-5 col-12 control-label">色</label>
                                        <div class="col-sm-7 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STAMP_COLOR, 'color', '',null,
                                                ['class'=> 'form-control', 'ng-model' =>'departmentStamp.color', 'ng-readonly'=>"readonly"]) !!}
                                        </div>
                                    </div>
                                    <div class="ml-5 mt-2">印面が使用されている場合のタイムスタンプの設定を選択してください。</div>
                                    <div class="form-group row">
                                        <label for="time_stamp_permission_21" class="control-label col-md-4">タイムスタンプの発行</label>
                                        <div class="col-md-8">
                                            <label class="label mr-2" for="time_stamp_permission_21">
                                                <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_21" ng-value="1" ng-disabled="item.info.time_stamp_permission!=0"> 有効
                                            </label>
                                            <label class="label mr-2" for="time_stamp_permission_20">
                                                <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_20" ng-value="0" ng-disabled="item.info.time_stamp_permission!=0"> 無効
                                            </label>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-info" ng-click="getDepartmentStamp()">プレビュー表示</button>
                                        <button type="button" class="btn btn-default" ng-click="resetDepartmentStamp()">クリア</button>
                                    </div>
                                </div>
                            </div>
                            <div class="notice mt-3">※ここで作成できない種類の日付印をご希望の場合は、共通印としてお申し込みください。</div>
                            <div class="download-pdf anchor mt-1">
                                <a href="{{ action('GlobalSetting\CompanyStampController@index') }}"><i class="fas fa-download"></i>  共通印設定</a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" ng-click="applyDepartmentStamp()" ng-disabled="!departmentStamp.stamp">申し込む</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ▼ PAC_5-821 部署名入り日付印の登録タイミングを、現行と同様に10時まで待って登録にする start ▼ --}}
    <form class="formDepartmentStamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-date-stamp mt-3 modal-child" id="modalDepartmentStampInfo" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" style="width: 600px">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">受付内容</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="form-horizontal">
                            <div class="row">
                                <div class="col-sm-3 preview-stamp">
                                    <div class="sapo" style="width: 200px"><% preview_text %></div>
                                    <br/>
                                    <div class="thumb" ng-if="departmentStamp.stamp">
                                        <img ng-src="data:image/png;base64,<% departmentStamp.stamp.stamp_image %>"/>
                                    </div>
                                </div>
                                <div class="col-sm-9 stamp-param">
                                    <div class="form-group row">
                                        <label for="stamp_type" class="col-sm-5 col-12 control-label">印面種類</label>
                                        <div class="col-sm-7 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STAMP_TYPE, 'stamp_type', '',null,
                                                ['class'=> 'form-control', 'ng-model' =>'departmentStamp.stamp_type', 'disabled'=>"disabled"]) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="stamp_layout" class="col-sm-5 col-12 control-label">レイアウト</label>
                                        <div class="col-sm-7 col-12">
                                            <ul ng-init="stamp_layout = {{ json_encode(\App\Http\Utils\AppUtils::STAMP_LAYOUT) }}"></ul>
                                            <select name="stamp_layout" id="stamp_layout" class="form-control"
                                                    ng-model="departmentStamp.stamp_layout"
                                                    ng-options="item.value as item.text for item in stamp_layout[departmentStamp.stamp_type]"
                                                    disabled>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="faces">
                                        <div ng-if="departmentStamp.stamp_layout == 'E101' || departmentStamp.stamp_layout == 'E0{0}1'">
                                            <div class="form-group row">
                                                <label for="face_up" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">上段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_up1" disabled/>
                                                </div>
                                            </div>
                                            <div class="form-group row row-face_down">
                                                <label for="face_down" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">下段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_down1" disabled/>
                                                </div>
                                            </div>
                                            <div class="form-group row row-face_down1" ng-if="departmentStamp.stamp_layout == 'E0{0}1'">
                                                <label for="face_down" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">下段小さい文字</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_down2" disabled/>
                                                </div>
                                            </div>
                                        </div>
                                        <div ng-if="departmentStamp.stamp_layout == 'E102'">
                                            <div class="form-group row">
                                                <label for="face_up1" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">上段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_up1" disabled/>
                                                </div>
                                            </div>
                                        </div>
                                        <div ng-if="departmentStamp.stamp_layout == 'E201' || departmentStamp.stamp_layout == 'E202'">
                                            <div class="form-group row">
                                                <label for="face_up1" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">上段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_up1" disabled/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="face_up2" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">上段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_up2" disabled/>
                                                </div>
                                            </div>
                                        </div>
                                        <div ng-if="departmentStamp.stamp_layout == 'E201'">
                                            <div class="form-group row">
                                                <label for="face_down1" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">下段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_down1" disabled/>
                                                </div>
                                            </div>
                                        </div>
                                        <div ng-if="departmentStamp.stamp_layout == 'E102' || departmentStamp.stamp_layout == 'E202'">
                                            <div class="form-group row">
                                                <label for="face_down1" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">下段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_down1" disabled/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="face_down2" class="col-sm-5 col-12 control-label"
                                                       style="line-height: 20px;">下段</label>
                                                <div class="col-sm-7 col-12">
                                                    <input type="text" class="form-control"
                                                           ng-model="departmentStamp.face_down2" disabled/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="font" class="col-sm-5 col-12 control-label">書体</label>
                                        <div class="col-sm-7 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STAMP_FONT_LABEL, 'font', '',null,
                                                ['class'=> 'form-control', 'ng-model' =>'departmentStamp.font', 'disabled'=>"disabled"]) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="color" class="col-sm-5 col-12 control-label">色</label>
                                        <div class="col-sm-7 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STAMP_COLOR, 'color', '',null,
                                                ['class'=> 'form-control', 'ng-model' =>'departmentStamp.color', 'disabled'=>"disabled"]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- ▲ PAC_5-821 部署名入り日付印の登録タイミングを、現行と同様に10時まで待って登録にする  end  ▲ --}}

    <form class="formUserStamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-add-stamp mt-3 modal-child" id="modalUserStamp" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">印面登録</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message"></div>
                        <div class="row form-group">
                            <label for="name" class="col-sm-2 control-label">利用者名</label>
                            <div class="col-md-6 col-sm-6 col-12">
                                <div class="input-group mb-1">
                                    <input type="text" class="form-control" id="name" ng-model="userStamp.name"
                                           ng-readonly="readonly"  placeholder="姓（印面内容）を入力してください">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit" ng-click="searchUserStamp()"><i class="fas fa-search mr-1"></i> 検索</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                印面に表示する氏名を入力してください
                            </div>
                        </div>
                        <div class="form-group search-stamp">
                            <div class="ml-5">印面を選択して下さい</div>
                            <div class="row">
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-md-7 col-sm-7 col-12">
                                    <div style="border: solid 1px #d3d3d3; padding: 10px;" ng-if="userStamp.items.length">
                                        <ul class="stamp-list row" >
                                            <li class="item col-md-4" ng-repeat="(key, stamp) in userStamp.items">
                                                <div class="thumb" ng-class="{selected: userStamp.selected.indexOf(stamp.id) !== -1}" ng-click="selectedStamp(stamp.id)" >
                                                    <img ng-src="data:image/png;base64,<% stamp.stamp_image %>" class="stamp-image" />
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-3"></div>
                            </div>
                            <div ng-show="{{$company->stamp_flg}}">
                                <div class="ml-5 mt-2" ng-if="userStamp.items.length">印面が使用されている場合のタイムスタンプの設定を選択してください。</div>
                                <div class="form-group row" ng-if="userStamp.items.length">
                                    <label for="time_stamp_permission_31" class="control-label col-md-4">タイムスタンプの発行</label>
                                    <div class="col-md-8">
                                        <label class="label mr-2" for="time_stamp_permission_31">
                                            <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_31" ng-value="1" ng-disabled="item.info.time_stamp_permission!=0"> 有効
                                        </label>
                                        <label class="label mr-2" for="time_stamp_permission_30">
                                            <input type="radio" ng-model="time_stamp_permission" id="time_stamp_permission_30" ng-value="0" ng-disabled="item.info.time_stamp_permission!=0"> 無効
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" ng-click="registerUserStamp()" ng-disabled="userStamp.selected.length == 0">登録</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form class="formUserStamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-add-stamp mt-3 modal-child" id="modalPasswordCode" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">パスワード設定コード</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="message message-list mt-3"></div>
                        <div class="" style="text-align:center; font-size:1.2em;"><% email.name %><% email.domain %></div>
                        <div class="my-2" style="text-align:center;">
                            <span id='code' class="py-1 px-4 font-weight-bold" style="font-size:1.2em; border: solid 1px #000;"></span>
                            <button style="font-size:2em; border: none; border-radius:18px;margin-left: 10px;" ng-click="copyToClipboard()"><i class="far fa-clipboard"></i></button>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="modal modal-add-stamp mt-5  message-info " id="modalAlertConvenient" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-<% convenient_alert.size %>">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <div class="modal-title" ng-if="convenient_alert.title" ng-bind-html="convenient_alert.title"></div>
</div>
                <!-- Modal body -->
                <div class="modal-body text-left" ng-if="convenient_alert.message" ng-bind-html="convenient_alert.message"></div>
                <div class="modal-footer">
                    <div class="btn btn-default" ng-click="convenientAlertClose()">
                        <i class="fas fa-times-circle"></i> <% convenient_alert.btnClose %>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('DetailController', function($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.departmentStamp = {stamp: null};
                $scope.userStamp = {name: "", items:[], selected:[]};
                $scope.id = 0;
                $scope.truly_state = 0;
                // PAC_5-2163  利用者情報更新画面で初期パスワード設定を送るときメールが無効だったらモーダル表示させる
                $scope.currentUserEnableEmail = 0;
                $scope.email = {name:'', domain:'' };
                $scope.isResetPassword = null;
                $scope.stamp_name = "";
                $scope.count_stamp_date = 0;
                $scope.count_stamp_date =0;
                $scope.contract_edition = null;
                $scope.default_stamp_flg = 0;
                $scope.company_stamp_flg = null;
                // PAC_5-1599 部署と役職初期化 Start
                $scope.multiple_department_position_flg = null;
                $scope.department_position = [];
                $scope.position_list = {!! $listPositionObj !!};
                $scope.selected_list = [];
                $scope.sticky_note_flg = 0;
                // PAC_5-1599 End

                $scope.$on("showModalAlertConvenient", function(event, data){
                    data = data || {};
                    $scope.convenient_alert = {
                        title: data.title || "",
                        message: data.message || "",
                        btnClose: data.btnClose || "閉じる",
                        callBack: data.callBack || null,
                        databack: data.databack || null,
                        size: data.size || "sm"
                    };

                    $("#modalAlertConvenient").modal();
                });

                $scope.convenientAlertClose = function(){
                    if($scope.convenient_alert.callBack) $scope.callBack($scope.convenient_alert.databack);
                    hasChange = false;
                    $("#modalAlertConvenient").modal("hide");
                    hasChange = true;
                }

                $rootScope.$on("openNewUser", function(event){
                    $http.get(link_get_stamp_over_status, {params:{select_type : 2}})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDepartmentStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                if(event.data.is_over == 1){
                                    $rootScope.$emit("showMocalAlert",
                                    {
                                        size:'md',
                                        title:"警告",
                                        message:event.data.message,
                                    });
                                    return false;
                                }
                            }
                        });

                    if(allow_create) $scope.readonly = false;
                    else $scope.readonly = true;
                    $scope.id = 0;
                    $scope.item = {id:0, email:"", given_name:"", family_name:"", state_flg: "0", company_enable_email: "1", info:{id: 0, date_stamp_config: "1",
                            api_apps: "0", mfa_type: "0", email_auth_dest_flg: "0", time_stamp_permission: "0" , enable_email: "1", email_format: {{ $company->email_format }}, template_flg: "1",
                            rotate_angle_flg: "{{ $company->rotate_angle_flg }}", sticky_note_flg: "0"}};
                    $scope.email = {name:'', domain: "{{ $company->domain[0] }}" };
                    $scope.company_stamp_flg = "{{ $company->stamp_flg }}";
                    // PAC_5-1599 部署と役職初期化 Start
                    $scope.multiple_department_position_flg = "{{ $company->multiple_department_position_flg }}";
                    $scope.department_position = [];
                    $scope.removeNull();
                    // PAC_5-1599 End
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                    console.log($scope.item.info);
                });

                $rootScope.$on("openEditUser", function(event, data){
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    // PAC_5-1599 部署と役職初期化
                    $scope.department_position = [];
                    hideMessages();
                    hasChange = false;
                    if(allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    $http.get(link_ajax + "/" +$scope.id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(!event.data.status){
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.item = event.data.item;
                                $scope.item.info.mst_department_id = ""+$scope.item.info.mst_department_id;
                                $scope.item.info.mst_position_id = ""+$scope.item.info.mst_position_id;
                                // PAC_5-1599 追加部署と役職 Start
                                $scope.multiple_department_position_flg = "{{ $company->multiple_department_position_flg }}";
                                $scope.item.info.mst_department_id_1 = ""+$scope.item.info.mst_department_id_1;
                                $scope.item.info.mst_department_id_2 = ""+$scope.item.info.mst_department_id_2;
                                $scope.item.info.mst_position_id_1 = ""+$scope.item.info.mst_position_id_1;
                                $scope.item.info.mst_position_id_2 = ""+$scope.item.info.mst_position_id_2;
                                $scope.removeNull();
                                if ($scope.multiple_department_position_flg && $scope.multiple_department_position_flg) {
                                    $scope.initDepartmentPosition();
                                }
                                $scope.departmentPostionChange();
                                // PAC_5-1599 End

                                $scope.item.info.date_stamp_config = ""+$scope.item.info.date_stamp_config;
                                $scope.item.info.api_apps = ""+$scope.item.info.api_apps;
                                $scope.item.info.mfa_type = ""+$scope.item.info.mfa_type;
                                $scope.item.info.time_stamp_permission = ""+$scope.item.info.time_stamp_permission;
                                $scope.item.info.email_auth_dest_flg = ""+$scope.item.info.email_auth_dest_flg;
                                $scope.item.info.sticky_note_flg = ""+$scope.item.info.sticky_note_flg;
                                $scope.item.state_flg = ""+$scope.item.state_flg;
                                $scope.truly_state = $scope.item.state_flg;
                                $scope.userStamp.name = $scope.item.family_name;
                                $scope.item.info.enable_email = $scope.item.user_enable_email
                                $scope.item.info.email_format = $scope.item.user_email_format
                                $scope.email = {name: $scope.item.email.substring(0, $scope.item.email.indexOf('@')), domain: $scope.item.email.substring($scope.item.email.indexOf('@'))};
                                $scope.department_stamp_flg = $scope.item.department_stamp_flg;
                                $scope.contract_edition = $scope.item.contract_edition;
                                $scope.default_stamp_flg = $scope.item.default_stamp_flg;
                                $scope.company_stamp_flg = $scope.item.company_stamp_flg;
                                $scope.sticky_note_flg = $scope.item.info.sticky_note_flg;
                                if($scope.item.state_flg == 1 && $scope.item.stamp_is_over == 1){
                                    $scope.stamp_is_over = 1;
                                    $rootScope.$emit("showMocalAlert",
                                    {
                                        size:'md',
                                        title:"警告",
                                        message:$scope.item.stamp_is_over_message,
                                    });
                                }

                                $scope.count_stamp_date = $scope.item.stamps.stampMaster.filter(function(item) { return item.stamp_master.stamp_division === 1}).length;
                                $scope.count_stamp_name = $scope.item.stamps.stampMaster.filter(function(item) { return item.stamp_master.stamp_division === 0 }).length;
                                $scope.item.info.template_flg = ""+$scope.item.info.template_flg;
                                $scope.item.info.rotate_angle_flg = ""+$scope.item.info.rotate_angle_flg;
                                // PAC_5-2163  利用者情報更新画面で初期パスワード設定を送るときメールが無効だったらモーダル表示させる
                                $scope.currentUserEnableEmail = $scope.item.info.enable_email;
                            }
                        });
                    $("#modalDetailItem").modal();
                });
                $scope.save = function(callSuccess){
                    // PAC_5-1599 部署と役職 Start
                    if ($scope.multiple_department_position_flg && $scope.multiple_department_position_flg) {
                        $scope.initDepartmentPosition();
                        if (!$scope.checkForm()) return false;
                    }
                    if ($(".form_edit")[0].checkValidity()) {
                        if (!$scope.item.id && $scope.item.without_email_flg) {
                            $rootScope.$emit("showMocalConfirm",
                                {
                                    title: '登録するメールアドレスは架空のメールアドレスですか？',
                                    btnSuccess: 'はい',
                                    size: 'lg',
                                    callSuccess: function () {
                                        user_save();
                                    },
                                    callCancel: function () {
                                        return false;
                                    }
                                });
                            return;
                        }
                        user_save();
                    }


                    //ユーザー登録更新
                    function user_save() {

                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function (event) {
                            $rootScope.$emit("hideLoading");

                            if (!event.data.status) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.id = event.data.id;
                                $scope.item.id = event.data.id;
                                $scope.item.info.id = event.data.info_id;
                                $scope.truly_state = $scope.item.state_flg;

                                $http.get(link_ajax + "/" + $scope.id)
                                    .then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (!event.data.status) {
                                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $scope.item = event.data.item;
                                            $scope.item.info.mst_department_id = "" + $scope.item.info.mst_department_id;
                                            $scope.item.info.mst_position_id = "" + $scope.item.info.mst_position_id;
                                            // PAC_5-1599 追加部署と役職 Start
                                            $scope.item.info.mst_department_id_1 = "" + $scope.item.info.mst_department_id_1;
                                            $scope.item.info.mst_department_id_2 = "" + $scope.item.info.mst_department_id_2;
                                            $scope.item.info.mst_position_id_1 = "" + $scope.item.info.mst_position_id_1;
                                            $scope.item.info.mst_position_id_2 = "" + $scope.item.info.mst_position_id_2;
                                            $scope.removeNull();
                                            if ($scope.multiple_department_position_flg && $scope.multiple_department_position_flg) {
                                                $scope.initDepartmentPosition();
                                            }
                                            $scope.departmentPostionChange();
                                            // PAC_5-1599 End

                                            $scope.item.info.date_stamp_config = "" + $scope.item.info.date_stamp_config;
                                            $scope.item.info.api_apps = "" + $scope.item.info.api_apps;
                                            $scope.item.info.mfa_type = "" + $scope.item.info.mfa_type;
                                            $scope.item.info.time_stamp_permission = "" + $scope.item.info.time_stamp_permission;
                                            $scope.item.info.email_auth_dest_flg = "" + $scope.item.info.email_auth_dest_flg;
                                            $scope.item.state_flg = "" + $scope.item.state_flg;
                                            $scope.truly_state = $scope.item.state_flg;
                                            $scope.userStamp.name = $scope.item.family_name;
                                            $scope.email = {
                                                name: $scope.item.email.substring(0, $scope.item.email.indexOf('@')),
                                                domain: $scope.item.email.substring($scope.item.email.indexOf('@'))
                                            };
                                            $scope.department_stamp_flg = $scope.item.department_stamp_flg;
                                            $scope.contract_edition = $scope.item.contract_edition;
                                            $scope.count_stamp_date = $scope.item.stamps.stampMaster.filter(function (item) {
                                                return item.stamp_master.stamp_division === 1
                                            }).length;
                                            $scope.count_stamp_name = $scope.item.stamps.stampMaster.filter(function (item) {
                                                return item.stamp_master.stamp_division === 0
                                            }).length;
                                            $scope.item.info.template_flg = "" + $scope.item.info.template_flg;
                                            // おじぎ印取得
                                            $scope.item.info.rotate_angle_flg = "" + $scope.item.info.rotate_angle_flg;
                                            $scope.item.info.sticky_note_flg = ""+$scope.item.info.sticky_note_flg;
                                            // PAC_5-2163  利用者情報更新画面で初期パスワード設定を送るときメールが無効だったらモーダル表示させる
                                            $scope.currentUserEnableEmail = $scope.item.info.enable_email;
                                        }
                                    });

                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                hasChange = true;
                                if (callSuccess) callSuccess();
                            }
                        }
                        $scope.item.email = $scope.email.name + $scope.email.domain;
                        if (!$scope.item.id) {
                            $http.post(link_ajax, {item: $scope.item})
                                .then(function (event) {
                                    if (event.data.status == false && event.data.form_user_over == 1) {
                                        $scope.$emit("showModalAlertConvenient",
                                            {
                                                size: 'md',
                                                title: "警告",
                                                message: event.data.message,
                                            });
                                    }
                                    saveSuccess(event);
                                });
                        } else {
                            $http.put(link_ajax + "/" + $scope.id, {item: $scope.item})
                                .then(function (event) {
                                    if (event.data.status == false && event.data.is_over == 1) {
                                        $scope.stamp_is_over = 1;
                                        $rootScope.$emit("showMocalAlert",
                                            {
                                                size: 'md',
                                                title: "警告",
                                                message: event.data.message.stamp_over,
                                            });
                                    }
                                    if (event.data.status == false && event.data.convenient_stamp_is_over == 1) {
                                        $scope.$emit("showModalAlertConvenient",
                                            {
                                                size: 'md',
                                                title: "警告",
                                                message: event.data.message.convenient_stamp_over,
                                            });
                                    }
                                    if (event.data.status == false && event.data.form_user_over == 1) {
                                        $scope.$emit("showModalAlertConvenient",
                                            {
                                                size: 'md',
                                                title: "警告",
                                                message: event.data.message,
                                            });
                                    }
                                    if (event.data.is_over == 1 || event.data.convenient_stamp_is_over == 1) {
                                        $rootScope.$emit("hideLoading");
                                        $scope.item.state_flg = 0;
                                        return ;
                                    }
                                    ;
                                    saveSuccess(event);
                                });
                        }
                    };
                };

                $scope.remove = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'利用者を削除します。よろしいですか？',
                            btnDanger:'はい',
                            databack: $scope.item.id,
                            callDanger: function(id){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + id, { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'warning', 10000));
                                            location.reload();
                                        }
                                    });
                            }
                        });
                };

                $scope.resetPassword = function(sendLoginUrl){
                    var send_mail = link_reset;
                    if(sendLoginUrl){
                        send_mail = link_send_login_url;
                    }
                    if($scope.currentUserEnableEmail == 0){
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'メールの設定を有効にしないと初期パスワードの通知メールが届きません。有効にしますか？',
                                btnSuccess:'はい',
                                size:'lg',
                                callSuccess: function(){
                                    $scope.item.info.enable_email = "1";
                                    $scope.currentUserEnableEmail = "1";
                                    $rootScope.$emit("showLoading");
                                    $scope.save(function(){
                                        $scope.resetPassword();
                                    });
                                },
                                callCancel: function(){
                                    $scope.isResetPassword = false;
                                }
                            });
                        return;
                    }
                    $scope.isResetPassword = true;
                    // check stamps
                    var applyStamp = false;
                    // 氏名印0件
                    if($scope.item.stamps && $scope.item.stamps.stampMaster.length == 0){
                        //デフォルト印OFFの場合
                        if($scope.default_stamp_flg == 0){
                            applyStamp = true;
                        }else{
                            // デフォルト印ONの場合、共通印のみだけでも登録できる
                            // また、デフォルト印OFF以外の場合、部署名入り日付印も日付印と見なし、部署名入り日付印だけでもいい
                            if($scope.item.stamps.stampCompany.length==0 && $scope.item.stamps.stampDepartment.length==0){
                                applyStamp = true;
                            }
                        }
                    }
                    $http.get(link_find_user_stamp_ok_status, {params:{mst_user_id :$scope.item.id}})
                        .then(function(event) {
                            if(event.data.status == false){
                                $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                                if (event.data.form_user_over == 1){
                                    $rootScope.$emit("showMocalAlert",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:'5人以上のユーザの登録は行えません。',
                                        });
                                }
                            }else{
                                if(event.data.is_over == 1){
                                    $rootScope.$emit("showMocalAlert",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:event.data.message.stamp_over,
                                        });
                                }
                                if(event.data.convenient_stamp_is_over == 1){
                                    $scope.$emit("showModalAlertConvenient",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:event.data.message.convenient_stamp_over,
                                        });
                                }
                                if (event.data.is_over == 1 || event.data.convenient_stamp_is_over == 1) {
                                    $scope.isResetPassword = false;
                                    return false;
                                };

                                if(applyStamp){
                                    $rootScope.$emit("showMocalConfirm",
                                        {
                                            title:'初期パスワード通知するためには、印面を登録する必要があります。',
                                            btnSuccess:'はい',
                                            size:'lg',
                                            callSuccess: $scope.showFormUserStamp
                                        });
                                    return;
                                }

                                // check state
                                if($scope.truly_state == 0 || $scope.truly_state == 9){
                                    $rootScope.$emit("showMocalConfirm",
                                        {
                                            title:'初期パスワード通知するためには状態を有効にする必要があります。実行しますか？',
                                            btnSuccess:'はい',
                                            size:'lg',
                                            callSuccess: function(){
                                                $scope.item.state_flg = "1";
                                                $rootScope.$emit("showLoading");
                                                $scope.save(function(){
                                                    $scope.resetPassword();
                                                });
                                            },
                                            callCancel: function(){
                                                $scope.isResetPassword = false;
                                            }
                                        });
                                    return;
                                }

                                $rootScope.$emit("showMocalConfirm",
                                    {
                                        title:'指定したメールアドレスに、初期パスワードの通知メールを送信します。実行しますか？',
                                        btnSuccess:'はい',
                                        size:'lg',
                                        callSuccess: function(){
                                            $scope.isResetPassword = false;
                                            $rootScope.$emit("showLoading");
                                            $http.post(send_mail, { cids: [$scope.item.id], user_type: 'normal_user'})
                                            .then(function(event) {
                                                $rootScope.$emit("hideLoading");
                                                if(event.data.status == false){
                                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                                }else{
                                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                                }
                                            });
                                        },
                                        callCancel: function(){
                                            $scope.isResetPassword = false;
                                        }
                                    });
                            }
                        });
                };

                $scope.getKeyDownList=function(){
                    if(event.keyCode==13)
                    {
                        $scope.save();
                    }
                };

                $scope.removeStamp = function(assign_id,stamp_flg){

                    var updateStatus = false;
                    var updateStatusMessage = '';

                    if($scope.truly_state == 1){
                        // 有効時、無効に更新必要かを判定
                        if($scope.default_stamp_flg == 0){
                            // デフォルト印OFFの場合、氏名印/日付印必須、氏名印/日付印削除時判定要
                            if(stamp_flg == 0 && ($scope.item.stamps && $scope.item.stamps.stampMaster.length == 1)){
                                updateStatus = true;
                            }
                        }else{
                            // Standard(0)+trial(3)以外の場合、氏名印/日付印/共通印/部署印のいずれあれば登録できる
                            if(stamp_flg == 0 || stamp_flg == 2){
                                // 氏名印/日付印、または有効の部署印を削除時判定
                                if($scope.item.stamps && ($scope.item.stamps.stampMaster.length + $scope.item.stamps.stampDepartment.length + $scope.item.stamps.stampCompany.length) == 1){
                                    updateStatus = true;
                                }
                            }
                        }
                    }

                    if(updateStatus){
                        updateStatusMessage = '<div class="text-left">※全て削除した場合は利用者を無効にする</div>';
                    }

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'削除確認',
                            message:'<div class="text-left">削除しますか？</div>' + updateStatusMessage + $(".stamp-item-"+assign_id).find('.thumb-img').html(),
                            btnDanger:'削除',
                            databack: assign_id,
                            callDanger: function(assign_id){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_remove_stamp +"/"+ assign_id, {})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $scope.item.stamps = event.data.stamps;
                                            $scope.count_stamp_date = $scope.item.stamps.stampMaster.filter(function(item) { return item.stamp_master.stamp_division === 1}).length;
                                            $scope.count_stamp_name = $scope.item.stamps.stampMaster.filter(function(item) { return item.stamp_master.stamp_division === 0 }).length;
                                            $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'warning', 10000));
                                            hasChange = true;

                                            $scope.item.state_flg = ""+event.data.item.state_flg;
                                            $scope.truly_state = $scope.item.state_flg;
                                        }
                                    });
                            }
                        });
                }

                $scope.editStamp = function(assign_id){
                    $scope.time_stamp_permission = 0;
                    hideMessages();
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $http.get(link_show_time_stamp_permission +"/"+ assign_id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItemStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.time_stamp_permission = event.data.stamp_asign.time_stamp_permission;
                                $scope.stamp_id = event.data.stamp_asign.id;
                            }

                        });
                    $("#modalDetailItemStamp").modal();
                };

                $scope.saveTimeStampPermission = function(){
                    $rootScope.$emit("showLoading");
                    $http.post(link_update_time_stamp_permission + "/"+$scope.stamp_id, {time_stamp_permission: $scope.time_stamp_permission})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDetailItemStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItemStamp .message").append(showMessages(event.data.message, 'success', 10000));
                                console.log(event.data);
                            }
                        });
                };

                $scope.showFormDepartmentStamp = function(){
                    $http.get(link_get_stamp_over_status, {params:{select_type : 1}})
                        .then(function(event) {
                            if(event.data.status == false){
                                $("#modalDetailItem .message-stamp").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                if(event.data.is_over == 1  && $scope.item.state_flg == 1){
                                    $rootScope.$emit("showMocalAlert",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:event.data.message,
                                        });
                                    return false;
                                }
                                $rootScope.time_stamp_permission = 0;
                                $scope.resetDepartmentStamp();
                                $('#face_down').tooltip('show');
                                $("#modalDepartmentStamp").modal();
                            }
                        });
                };

                $scope.resetDepartmentStamp = function(){
                    $scope.departmentStamp = {stamp_type: 'XGL-15',stamp_layout:'E101',face_up:$scope.item.family_name,face_up1:'', face_up2:'',
                        face_down:'',face_down1:'',face_down2:'' ,font:'0', color: '02', preview: '', stamp: null};
                    $scope.preview_text = '上下１行';
                };

                $scope.changeStampType = function(){
                    $scope.preview_text = '上下１行';
                    $scope.departmentStamp.stamp_layout = 'E101';
                };

                $scope.changeStampLayout = function(){
                    $scope.preview_text = $("#stamp_layout option:selected").text();
                };

                $scope.getDepartmentStamp = function(){
                    if($(".formDepartmentStamp")[0].checkValidity()) {
                        $rootScope.$emit("showLoading");
                        console.log($scope.departmentStamp);
                        $http.post(link_getDepartmentStamp, {info: $scope.departmentStamp})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $("#modalDepartmentStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $scope.departmentStamp.stamp = event.data.stamp;
                                }
                            });
                    }
                };

                $scope.applyDepartmentStamp = function(){
                    $rootScope.$emit("showLoading");
                    $http.post(link_store_stamp, {stamps: [$scope.departmentStamp.stamp.id], mst_user_id: $scope.item.id, stamp_flg: 2 , time_stamp_permission: $scope.time_stamp_permission, state_flg: 2})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                if(event.data.is_over == 1){
                                    $rootScope.$emit("showMocalAlert",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:event.data.message[0],
                                        });
                                    return false;
                                }
                                $("#modalDepartmentStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $("#modalDetailItem .message-stamp").append(showMessages(['印面を登録しました。'], 'success', 10000));
                                $scope.item.stamps = event.data.stamps;
                                $("#modalDepartmentStamp").modal('hide');
                            }
                        });
                };

                $scope.showFormUserStamp = function(){
                     $http.get(link_get_stamp_over_status, {params:{select_type : 1}})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDepartmentStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                if(event.data.is_over == 1 && $scope.item.state_flg == 1){
                                    $rootScope.$emit("showMocalAlert",
                                    {
                                        size:'md',
                                        title:"警告",
                                        message:event.data.message,
                                    });
                                    return false;
                                }
                                $rootScope.time_stamp_permission = 0;
                                $scope.userStamp = {name: $scope.item.family_name, items:[], selected:[]};
                                $("#modalUserStamp").modal();
                            }
                        });
                };

                $scope.findUserStamp = function(){

                };

                $scope.searchUserStamp = function(){
                    if($scope.userStamp.name.trim() == ""){
                        $("#modalUserStamp .message").append(showMessages(["* 必須項目です"], 'danger', 10000));
                        return;
                    }

                    $scope.stamp_name = $scope.userStamp.name;
                    $rootScope.$emit("showLoading");
                    $http.post(link_searchstamp, {name: $scope.userStamp.name})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalUserStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.userStamp.items = event.data.items;
                            }
                        });

                };
                $scope.selectedStamp = function(stamp_id){
                    let found = $scope.userStamp.selected.indexOf(stamp_id);
                    if(found !== -1){
                        $scope.userStamp.selected.splice(found,1);
                    }else{
                        $scope.userStamp.selected.push(stamp_id);
                    }

                };

                $scope.registerUserStamp = function(){
                    if($scope.userStamp.selected.length){
                        $rootScope.$emit("showLoading");
                        $http.post(link_store_stamp, {stamps: $scope.userStamp.selected, mst_user_id: $scope.item.id, stamp_flg: 0,  time_stamp_permission: $scope.time_stamp_permission})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");

                                if(event.data.is_over == 1){
                                        $scope.stamp_is_over = 1
                                }
                                if(event.data.status == false){
                                    if($scope.stamp_is_over == 1){
                                        $rootScope.$emit("showMocalAlert",
                                        {
                                            size:'md',
                                            title:"警告",
                                            message:event.data.message[0],
                                        });
                                        return ;
                                    }
                                    $("#modalUserStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $("#modalDetailItem .message-stamp").append(showMessages(['印面を登録しました。'], 'success', 10000));
                                    $scope.item.stamps = event.data.stamps;
                                    $scope.count_stamp_date = $scope.item.stamps.stampMaster.filter(function(item) { return item.stamp_master.stamp_division === 1}).length;
                                    $scope.count_stamp_name = $scope.item.stamps.stampMaster.filter(function(item) { return item.stamp_master.stamp_division === 0 }).length;
                                    $("#modalUserStamp").modal('hide');


                                    $scope.item.state_flg = ""+event.data.item.state_flg;
                                    $scope.truly_state = $scope.item.state_flg;

                                    if($scope.isResetPassword){
                                        // for case click reset password but has'n stamp
                                        $scope.resetPassword();
                                    }

                                }
                            });
                    }
                };
                $scope.showStampInfo = function(stamp){
                    $rootScope.$emit("showLoading");
                    // 部署捺印が生成されたときに情報を取得します
                    $http.post(link_getDepartmentStampInfo, {info: stamp.stamp_department.id})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $("#modalDepartmentStamp .message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                // $scope.departmentStampの情報を設定
                                $scope.departmentStamp = {stamp_type: event.data.stamp.pribt_type,
                                    stamp_layout: event.data.stamp.layout, face_up1: event.data.stamp.face_up1, face_up2: event.data.stamp.face_up2,
                                    face_down1: event.data.stamp.face_down1, face_down2: event.data.stamp.face_down2,
                                    font: event.data.stamp.font, color: event.data.stamp.color, preview: '',
                                    stamp: event.data.stamp};
                                // $scope.preview_textの内容を設定
                                switch(event.data.stamp.layout){
                                    case "E101":
                                        $scope.preview_text = "上下１行";
                                        break;
                                    case 'E011':
                                    case 'E021':
                                    case 'E031':
                                        $scope.departmentStamp.stamp_layout = "E0{0}1";
                                        $scope.preview_text = "上下１行（子付き）";
                                        break;
                                    case 'E102':
                                        $scope.preview_text = "下２行";
                                        break;
                                    case 'E201':
                                        $scope.preview_text = "上２行";
                                        break;
                                    case 'E202':
                                        $scope.preview_text = "上下２行";
                                        break;
                                }
                            }
                        });
                    $("#modalDepartmentStampInfo").modal();
                };

                $scope.showFormCode = function(){

                    $scope.isShowFormCode = true;
                    // check stamps
                    var applyStamp = false;
                    if($scope.item.stamps && $scope.item.stamps.stampMaster.length == 0){
                        // 氏名印0件
                        if($scope.default_stamp_flg == 0){
                            // デフォルト印OFFの場合、氏名印必須
                            applyStamp = true;
                        }else{
                            // デフォルト印ON以外の場合、共通印のみだけでも登録できる
                            // また、デフォルト印OFF以外の場合、部署名入り日付印も日付印と見なし、部署名入り日付印だけでもいい
                            if($scope.item.stamps.stampCompany.length==0 && $scope.item.stamps.stampDepartment.length==0){
                                applyStamp = true;
                            }
                        }
                    }
                    if(applyStamp){
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'パスワード設定コードの設定には、印面を登録する必要があります。',
                                btnSuccess:'はい',
                                size:'lg',
                                callSuccess: $scope.showFormUserStamp
                            });
                        return;
                    }

                    // check state
                    if($scope.truly_state == 0 || $scope.truly_state == 9){
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title:'パスワード設定コードの設定には状態を有効にする必要があります。実行しますか？',
                                btnSuccess:'はい',
                                size:'lg',
                                callSuccess: function(){
                                    $scope.item.state_flg = "1";
                                    $rootScope.$emit("showLoading");
                                    $scope.save(function(){
                                        $scope.showFormCode();
                                    });
                                },
                                callCancel: function(){
                                    $scope.isShowFormCode = false;
                                }
                            });
                        return;
                    }

                    $scope.userStamp = {name: $scope.item.family_name, items:[], selected:[]};
                    // CSRFトークン設定
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type:'POST', // POST送信
                        url:'password/getPasswordCodeUser', //送信先URL
                        dataType:'json', //受け取る変数の形式
                        data: {'code_user_id': document.getElementById("code_user_id").value},
                        beforeSend: function () {
                            $('.loading').removeClass('display-none'); //読み込みグルグル表示
                        }
                    }).done(function (code){
                        $("#code").text(code);
                        $('.loading').addClass('display-none'); //読み込みグルグル削除
                        console.log("ajax通信に成功しました");
                    }).fail(function(){
                        console.log("ajax通信に失敗しました");
                    });
                    // callback
                    $("#modalPasswordCode").modal();
                };
                $scope.copyToClipboard = function(){
                    // ボタンを押してコードをクリップボードにコピー
                    var code = document.getElementById('code');
                    var copyText = code.textContent;
                    var el = document.createElement('textarea');
                    el.value = copyText;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    $(".message-list").append(showMessages(['クリップボードにコピーしました'], 'success', 1000));
                };
                $scope.getAddress=function(){
                    $scope.item.info.address=""
                    $http({
                        method:'get',
                        url:'setting-user/get-address',
                        params:{zipcode:$scope.item.info.postal_code}
                    }).then(function (event){
                        let res=event.data.results;
                        if (res!=null){
                            $scope.item.info.address=res[0].address1+res[0].address2+res[0].address3
                        }

                    })
                }
                // PAC_5-1599 追加部署と役職 Start
                $scope.addDepartmentPosition=function(){
                    if ($scope.department_position.length < 2) {
                        $scope.department_position.push({});
                    }
                };
                $scope.delDepartmentPosition=function(index){
                    if ($scope.item.info['mst_department_id_' + (index+2)] || $scope.item.info['mst_position_id_' + (index+2)]) {
                        $scope.item.info['mst_department_id_' + (index+1)] = ""+$scope.item.info['mst_department_id_' + (index+2)];
                        $scope.item.info['mst_position_id_' + (index+1)] = ""+$scope.item.info['mst_position_id_' + (index+2)];
                        $scope.item.info['mst_department_id_' + (index+2)] = null;
                        $scope.item.info['mst_position_id_' + (index+2)] = null;
                    } else {
                        $scope.item.info['mst_department_id_' + (index+1)] = null;
                        $scope.item.info['mst_position_id_' + (index+1)] = null;
                    }
                    $scope.department_position.splice(index, 1);
                };
                // init Select option
                $scope.departmentPostionChange = function () {
                    let selected_list = [
                        JSON.parse(JSON.stringify($scope.position_list)),
                        JSON.parse(JSON.stringify($scope.position_list)),
                        JSON.parse(JSON.stringify($scope.position_list))
                    ];
                    angular.element('#mst_position_id').find('option').show();
                    angular.element('#mst_position_id_1').find('option').show();
                    angular.element('#mst_position_id_2').find('option').show();

                    for (let i = 0; i <= 2; i++) {
                        let index = i == 0 ? '' : '_' + i;
                        let key = '';
                        let value = '';

                        if ($scope.item.info['mst_department_id' + index] || $scope.item.info['mst_position_id' + index]) {
                            key = isNaN(parseInt($scope.item.info['mst_department_id' + index])) ? 0 : parseInt($scope.item.info['mst_department_id' + index]);
                            value = isNaN(parseInt($scope.item.info['mst_position_id' + index])) ? 0 : parseInt($scope.item.info['mst_position_id' + index]);

                            if (key > 0 && value > 0) {
                                for (let l in selected_list) {
                                    let l_index = l == 0 ? '' : '_' + l;
                                    let l_key = isNaN(parseInt($scope.item.info['mst_department_id' + l_index])) ? 0 : parseInt($scope.item.info['mst_department_id' + l_index]);
                                    let l_value = isNaN(parseInt($scope.item.info['mst_position_id' + l_index])) ? 0 : parseInt($scope.item.info['mst_position_id' + l_index]);

                                    if (l != i && key == l_key) {
                                        for (let k in selected_list[l]) {
                                            if (parseInt(selected_list[l][k].id) == value && parseInt(l_value) != value) {
                                                setTimeout(function () {
                                                    $scope.$apply(function() {
                                                        angular.element('#mst_position_id' + l_index).find('option[value=' + selected_list[l][k].id + ']').hide();
                                                    });
                                                },50);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                };
                // init Select
                $scope.initDepartmentPosition=function() {
                    $scope.department_position = [];

                    for (let i = 0; i <= 2; i++) {
                        let index = i == 0 ? '' : '_' + i;
                        let next_index = '_' + (i + 1);
                        let isNaNDepartment = isNaN(parseInt($scope.item.info['mst_department_id' + index]));
                        let isNaNPosition = isNaN(parseInt($scope.item.info['mst_position_id' + index]));
                        let isNaNDepartmentNext = isNaN(parseInt($scope.item.info['mst_department_id' + next_index]));
                        let isNaNPositionNext = isNaN(parseInt($scope.item.info['mst_position_id' + next_index]));

                        if (isNaNDepartment && isNaNPosition) {
                            if (!isNaNDepartmentNext || !isNaNPositionNext) {
                                $scope.item.info['mst_department_id' + index] = ""+$scope.item.info['mst_department_id' + next_index];
                                $scope.item.info['mst_position_id' + index] = ""+$scope.item.info['mst_position_id' + next_index];
                                $scope.item.info['mst_department_id' + next_index] = null;
                                $scope.item.info['mst_position_id' + next_index] = null;
                                if (i > 0) $scope.department_position.push({});
                            } else {
                                $scope.item.info['mst_department_id' + index] = null;
                                $scope.item.info['mst_position_id' + index] = null;
                            }
                        } else {
                            if (i > 0) $scope.department_position.push({});
                        }
                    }
                }
                $scope.checkForm = function () {
                    let department_id = parseInt($scope.item.info.mst_department_id);
                    let department_id_1 = parseInt($scope.item.info.mst_department_id_1);
                    let department_id_2 = parseInt($scope.item.info.mst_department_id_2);
                    let position_id = parseInt($scope.item.info.mst_position_id);
                    let position_id_1 = parseInt($scope.item.info.mst_position_id_1);
                    let position_id_2 = parseInt($scope.item.info.mst_position_id_2);

                    if (((!isNaN(department_id) && !isNaN(position_id) && ((department_id == department_id_1 && position_id == position_id_1)
                        || (department_id == department_id_2 && position_id == position_id_2)))
                        || (!isNaN(department_id_1) && !isNaN(position_id_1) && department_id_1 == department_id_2 && position_id_1 == position_id_2))
                    ) {
                        $("#modalDetailItem .message-info").append(showMessages(['同じ部門と役職の組み合わせを追加することはできません'], 'danger', 10000));
                        return false;
                    }
                    return true;
                };
                $scope.removeNull = function () {
                    for (let i = 0; i <= 2; i++) {
                        let index = i == 0 ? '' : '_' + i;
                        let mst_department_id = $scope.item.info['mst_department_id'  +  index];
                        let mst_position_id = $scope.item.info['mst_position_id'  +  index];
                        if (mst_department_id === undefined || mst_department_id === null || mst_department_id === 'null') $scope.item.info['mst_department_id'  +  index] = '';
                        if (mst_position_id === undefined || mst_position_id === null || mst_position_id === 'null') $scope.item.info['mst_position_id'  +  index] = '';
                    }
                };
                // PAC_5-1599 追加部署と役職 End
            })
        }
    </script>
@endpush
