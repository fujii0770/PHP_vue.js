<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;" ng-keydown="getKeyDownList()"
          onkeydown="if(event.keyCode==13){return false;}">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-show="!item.id">グループウェア専用利用者情報登録</h4>
                        <h4 class="modal-title" ng-show="item.id">グループウェア専用利用者情報更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="card">
                            <div class="card-header">グループウェア専用利用者詳細</div>
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
                                        <label for="given_name" class="col-md-2 col-sm-2 col-12 control-label text-right-lg">ユーザーID
                                            <span style="color: red">*</span></label>
                                        <div class="col-md-8 col-sm-8 col-12">
                                            <div class="input-group">
                                                <input type="text" class="form-control" ng-model="email.name" id="email" ng-disabled="item.id"
                                                       required placeholder="user id" ng-readonly="readonly"
                                                       maxlength="64"/>
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

                                <div class="form-group" ng-if="!item.without_email_flg">
                                    <div class="row">
                                        <label for="given_name" class="col-md-2 col-sm-2 col-12 control-label">通知先メールアドレス
                                            <span style="color: red">*</span></label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.notification_email"
                                                   id="notification_email"
                                                   required placeholder="email" ng-readonly="readonly"
                                                   maxlength="64"/>
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
                                        <label for="given_name" class="col-md-2 col-sm-2 col-12 control-label">氏名
                                            <span style="color: red">*</span></label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="text" class="form-control" ng-model="item.family_name"
                                                   id="family_name"
                                                   required placeholder="姓" ng-readonly="readonly"
                                                   maxlength="64"/>
                                        </div>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="text" class="form-control" ng-model="item.given_name"
                                                   id="given_name"
                                                   required placeholder="名" ng-readonly="readonly"
                                                   maxlength="64"/>
                                        </div>
                                        <label class="col-md-2 col-sm-2 col-5 control-label"
                                               ng-if="item.id">パスワード</label>
                                        <div class="col-md-4 col-sm-4 col-7" ng-if="item.id">
                                            <label for=""
                                                   class="label"><% item.passwordStatus == 0 ? '未設定' : '設定済'
                                                %></label>
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
                                        <label for="postal_code"
                                               class="col-md-2 col-sm-2 col-12 control-label">郵便番号</label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="text" class="form-control" id="postal_code"
                                                   ng-model="item.info.postal_code"
                                                   placeholder="000-0000" ng-readonly="readonly"
                                                   ng-change="getAddress()" maxlength="10"/>
                                        </div>
                                        <label for="postal_code"
                                               class="col-md-8 col-sm-8 col-12 label text-left">
                                            郵便番号(ハイフンあり・なし両方)を入力すると住所が入力されます。</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="address"
                                               class="col-md-2 col-sm-2 col-12 control-label">住所</label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <input type="text" class="form-control" id="address"
                                                   ng-model="item.info.address"
                                                   ng-readonly="readonly" maxlength="128"/>
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
                                        <label for="reference"
                                               class="col-md-2 col-sm-2 col-12 control-label">備考</label>
                                        <div class="col-md-10 col-sm-10 col-12">
                                            <input type="text" class="form-control" id="reference"
                                                   ng-model="item.reference"
                                                   ng-readonly="readonly" maxlength="128"/>
                                        </div>
                                    </div>
                                </div>

                                <div ng-if='item.without_email_flg!=1 && item.company_enable_email==1'>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="enable_email"
                                                   class="col-md-2 col-sm-2 col-12 control-label">メール</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <input type="radio" ng-model="item.info.enable_email"
                                                       id="enable_email-1" ng-value="1"/>
                                                <label for="enable_email-1" class="control-label">有効</label>
                                                <input type="radio" ng-model="item.info.enable_email"
                                                       id="enable_email-0" ng-value="0"/>
                                                <label for="enable_email-0" class="control-label">無効</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if='item.info.enable_email==1'>
                                        <div class="row">
                                            <label for="email_format"
                                                   class="col-md-2 col-sm-2 col-12 control-label">メールフォーマット</label>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <input type="radio" ng-model="item.info.email_format"
                                                       id="email_format-1" ng-value="1"/>
                                                <label for="email_format-1" class="control-label">HTML</label>
                                                <input type="radio" ng-model="item.info.email_format"
                                                       id="email_format-0" ng-value="0"/>
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
                                        <label for="state_flg_1"
                                               class="col-md-2 col-sm-2 col-12 control-label">状態</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <label for="state_flg_1" class="control-label">
                                                <input type="radio" id="state_flg_1" ng-model="item.state_flg"
                                                       ng-readonly="readonly"
                                                       ng-disabled="!item.id"
                                                       name=state_flg value="1"/> 有効
                                            </label>

                                            <label for="state_flg_09">
                                                <input type="radio" ng-model="item.state_flg"
                                                       ng-if="!item.id || !item.passwordStatus"
                                                       ng-disabled="!item.id"
                                                       value="0" id="state_flg_09">
                                                <input type="radio" ng-model="item.state_flg"
                                                       ng-if="item.id && item.passwordStatus"
                                                       ng-disabled="!item.id"
                                                       value="9" id="state_flg_09">
                                                無効</label>

                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="mfa_type_2"
                                               class="col-md-2 col-sm-2 col-12 control-label">多要素認証</label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <label for="mfa_type_2" class="control-label">
                                                <input type="radio" id="mfa_type_2"
                                                       ng-model="item.info.mfa_type"
                                                       ng-readonly="readonly" name="mfa_type" value="2"
                                                       ng-disabled="{{!$company->mfa_flg}}"/> QRコード
                                            </label>
                                            <label for="mfa_type_1" class="control-label"
                                                   ng-if='item.company_enable_email==1 && item.info.enable_email==1 && item.without_email_flg!=1'>
                                                <input type="radio" id="mfa_type_1"
                                                       ng-model="item.info.mfa_type"
                                                       ng-readonly="readonly" name="mfa_type" value="1"
                                                       ng-disabled="{{!$company->mfa_flg}}"/> メール
                                            </label>
                                            <label for="mfa_type_0" class="control-label">
                                                <input type="radio" id="mfa_type_0"
                                                       ng-model="item.info.mfa_type"
                                                       ng-readonly="readonly" name="mfa_type" value="0"
                                                       ng-disabled="{{!$company->mfa_flg}}"/> 無効
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        @canany([PermissionUtils::PERMISSION_OPTION_USERS_CREATE])
                            <ng-template ng-show="!item.id">
                                <button type="submit" class="btn btn-success" ng-click="save()">
                                    <i class="far fa-save"></i> 登録
                                </button>
                            </ng-template>
                        @endcanany
                        @canany([PermissionUtils::PERMISSION_OPTION_USERS_UPDATE])
                            <ng-template ng-show="item.id">
                                <button type="submit" class="btn btn-success" ng-click="save()">
                                    <i class="far fa-save"></i> 更新
                                </button>
                            </ng-template>
                        @endcanany

                        @if($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO)
                            <button type="button" class="btn btn-warning" ng-click="resetPassword(true)"
                                    ng-show="item.id">
                                <i class="far fa-envelope"></i>ログインURL送信
                            </button>
                        @endif
                        @if($company->login_type != \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO ||($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO  && $company->use_mobile_app_flg == \App\Http\Utils\AppUtils::FLG_ENABLE))
{{--                            @if ($company->passreset_type == 0)--}}
                                @canany([\App\Http\Utils\PermissionUtils::PERMISSION_OPTION_USERS_CREATE,\App\Http\Utils\PermissionUtils::PERMISSION_OPTION_USERS_UPDATE])
                                <button type="button" class="btn btn-warning" ng-click="resetPassword()"
                                        ng-show="item.id && item.without_email_flg==0">
                                    <i class="far fa-envelope"></i>
                                    初期パスワード設定
                                </button>
                                 @endcanany
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                                <button type="button" class="btn btn-warning" ng-click="showFormCode()"
                                        id="code_user_id" value="<% item.id %>" ng-show="item.id && item.without_email_flg==1">
                                   <i class="fas fa-key"></i> パスワード設定コード発行
                                </button>

                        @endif

                        @canany([PermissionUtils::PERMISSION_OPTION_USERS_DELETE])
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
</div>

@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('DetailController', function ($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.id = 0;
                $scope.truly_state = 0;
                $scope.notification_email = notification_email;
                // PAC_5-2163  利用者情報更新画面で初期パスワードの通知メールを送るときメールが無効だったらモーダル表示させる
                $scope.currentUserEnableEmail = 0;
                $scope.isResetPassword = null;
                $scope.contract_edition = null;
                $scope.email = {name:'', domain:'' };
                // 部署と役職初期化
                $scope.multiple_department_position_flg = null;
                $scope.department_position = [];
                $scope.position_list = {!! $listPositionObj !!};
                $scope.selected_list = [];

                $rootScope.$on("openNewUser", function (event) {
                    $rootScope.$emit("hideLoading");

                    $scope.readonly = allow_create ? false : true;
                    $scope.id = 0;
                    $scope.item = {
                        id: 0,
                        email: "",
                        notification_email: "",
                        given_name: "",
                        family_name: "",
                        state_flg: "0",
                        info: {
                            id: 0,
                            api_apps: "0",
                            mfa_type: "0",
                            email_auth_dest_flg: "0",
                            enable_email: "1",
                            email_format: {{ $company->email_format }},
                        }
                    };
                    $scope.email = {name:'', domain: "{{ $company->domain[0] }}" };
                    $scope.multiple_department_position_flg = "{{ $company->multiple_department_position_flg }}";
                    $scope.department_position = [];
                    $scope.removeNull();
                    hideMessages();
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });

                $rootScope.$on("openEditUser", function (event, data) {
                    $rootScope.$emit("showLoading");
                    $scope.id = data.id;
                    $scope.item.id = data.id;
                    $scope.department_position = [];
                    hideMessages();
                    hasChange = false;
                    if (allow_update) $scope.readonly = false;
                    else $scope.readonly = true;
                    $http.get(link_ajax + "/" + $scope.id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.item = event.data.item;
                                // 部署と役職
                                $scope.item.info.mst_department_id = "" + $scope.item.info.mst_department_id;
                                $scope.item.info.mst_position_id = "" + $scope.item.info.mst_position_id;
                                $scope.multiple_department_position_flg = "{{ $company->multiple_department_position_flg }}";
                                $scope.item.info.mst_department_id_1 = ""+$scope.item.info.mst_department_id_1;
                                $scope.item.info.mst_department_id_2 = ""+$scope.item.info.mst_department_id_2;
                                $scope.item.info.mst_position_id_1 = ""+$scope.item.info.mst_position_id_1;
                                $scope.item.info.mst_position_id_2 = ""+$scope.item.info.mst_position_id_2;
                                $scope.removeNull();
                                if ($scope.multiple_department_position_flg && $scope.multiple_department_position_flg == 1) {
                                    $scope.initDepartmentPosition();
                                }
                                $scope.departmentPostionChange();
                                $scope.item.info.api_apps = "" + $scope.item.info.api_apps;
                                $scope.item.info.mfa_type = "" + $scope.item.info.mfa_type;
                                $scope.item.info.email_auth_dest_flg = "" + $scope.item.info.email_auth_dest_flg;
                                $scope.item.state_flg = "" + $scope.item.state_flg;
                                $scope.truly_state = $scope.item.state_flg;
                                $scope.item.info.enable_email = $scope.item.user_enable_email
                                $scope.item.info.email_format = $scope.item.user_email_format
                                $scope.notification_email = $scope.item.notification_email;
                                $scope.contract_edition = $scope.item.contract_edition;
                                $scope.email = {name: $scope.item.email.substring(0, $scope.item.email.lastIndexOf('@')), domain: $scope.item.email.substring($scope.item.email.lastIndexOf('@'))};
                                // PAC_5-2163  利用者情報更新画面で初期パスワードの通知メールを送るときメールが無効だったらモーダル表示させる
                                $scope.currentUserEnableEmail = $scope.item.info.enable_email;
                            }
                        });
                    $("#modalDetailItem").modal();
                });

                $scope.save = function (callSuccess) {
                    if ($scope.multiple_department_position_flg && $scope.multiple_department_position_flg == 1) {
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
                                        option_user_save();
                                    },
                                    callCancel: function () {
                                        return false;
                                    }
                                });
                            return;
                        }
                        option_user_save()
                    }
                    function option_user_save(){
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function (event) {
                            $rootScope.$emit("hideLoading");

                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.id = event.data.id;
                                $scope.item.id = event.data.id;
                                $scope.item.info.id = event.data.info_id;
                                $scope.truly_state = $scope.item.state_flg;

                                $http.get(link_ajax + "/" + $scope.id)
                                    .then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $scope.item = event.data.item;
                                            $scope.item.info.mst_department_id = ""+$scope.item.info.mst_department_id;
                                            $scope.item.info.mst_position_id = ""+$scope.item.info.mst_position_id;
                                            $scope.item.info.mst_department_id_1 = ""+$scope.item.info.mst_department_id_1;
                                            $scope.item.info.mst_department_id_2 = ""+$scope.item.info.mst_department_id_2;
                                            $scope.item.info.mst_position_id_1 = ""+$scope.item.info.mst_position_id_1;
                                            $scope.item.info.mst_position_id_2 = ""+$scope.item.info.mst_position_id_2;
                                            $scope.removeNull();
                                            if ($scope.multiple_department_position_flg && $scope.multiple_department_position_flg == 1) {
                                                $scope.initDepartmentPosition();
                                            }
                                            $scope.departmentPostionChange();
                                            // $scope.item.info.api_apps = "" + $scope.item.info.api_apps;
                                            $scope.email = {name: $scope.item.email.substring(0, $scope.item.email.lastIndexOf('@')), domain: $scope.item.email.substring($scope.item.email.lastIndexOf('@') )};
                                            $scope.item.info.mfa_type = "" + $scope.item.info.mfa_type;
                                            $scope.item.state_flg = "" + $scope.item.state_flg;
                                            $scope.truly_state = $scope.item.state_flg;
                                            $scope.contract_edition = $scope.item.contract_edition;
                                            // PAC_5-2163  利用者情報更新画面で初期パスワードの通知メールを送るときメールが無効だったらモーダル表示させる
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
                                .then(saveSuccess);
                        } else {
                            $http.put(link_ajax + "/" + $scope.id, {item: $scope.item})
                                .then(function (event) {
                                    saveSuccess(event);
                                });
                        }
                    }
                };

                $scope.remove = function () {
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '利用者を削除します。よろしいですか？',
                            btnDanger: 'はい',
                            databack: $scope.item.id,
                            callDanger: function (id) {
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/" + id, {})
                                    .then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'warning', 10000));
                                            location.reload();
                                        }
                                    });
                            }
                        });
                };

                $scope.resetPassword = function (sendLoginUrl) {
                    var send_mail = link_reset;
                    if (sendLoginUrl) {
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

                    // check state
                    if ($scope.truly_state == 0 || $scope.truly_state == 9) {
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '初期パスワード通知するためには状態を有効にする必要があります。実行しますか？',
                                btnSuccess: 'はい',
                                size: 'lg',
                                callSuccess: function () {
                                    $scope.item.state_flg = "1";
                                    $rootScope.$emit("showLoading");
                                    $scope.save(function () {
                                        $scope.resetPassword();
                                    });
                                },
                                callCancel: function () {
                                    $scope.isResetPassword = false;
                                }
                            });
                        return;
                    }

                    $scope.isResetPassword = false;
                    $rootScope.$emit("showLoading");

                    $http.post(send_mail, {cids: [$scope.item.id], user_type: 'option_user'})
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                };

                $scope.getKeyDownList = function () {
                    if (event.keyCode == 13) {
                        $scope.save();
                    }
                };

                $scope.showFormCode = function () {
                    // check state
                    if ($scope.truly_state == 0 || $scope.truly_state == 9) {
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: 'パスワード設定コードの設定には状態を有効にする必要があります。実行しますか？',
                                btnSuccess: 'はい',
                                size: 'lg',
                                callSuccess: function () {
                                    $scope.item.state_flg = "1";
                                    $rootScope.$emit("showLoading");
                                    $scope.save(function () {
                                        $scope.showFormCode();
                                    });
                                },
                                callCancel: function () {
                                    $scope.isShowFormCode = false;
                                }
                            });
                        return;
                    }

                    // CSRFトークン設定
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: 'POST', // POST送信
                        url: '{{url('password/getPasswordCodeUser')}}', //送信先URL
                        dataType: 'json', //受け取る変数の形式
                        data: {'code_user_id': document.getElementById("code_user_id").value},
                        beforeSend: function () {
                            $('.loading').removeClass('display-none'); //読み込みグルグル表示
                        }
                    }).done(function (code) {
                        $("#code").text(code);
                        $('.loading').addClass('display-none'); //読み込みグルグル削除
                        console.log("ajax通信に成功しました");
                    }).fail(function () {
                        console.log("ajax通信に失敗しました");
                    });
                    // callback
                    $("#modalPasswordCode").modal();
                };

                $scope.copyToClipboard = function () {
                    // ボタンを押してコードをクリップボードにコピー
                    var code = document.getElementById('code');
                    var copyText = code.textContent;
                    var el = document.createElement('textarea');
                    el.value = copyText;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    // メッセージ表示
                    $(".message-list").append(showMessages(['クリップボードにコピーしました'], 'success', 1000));
                };

                $scope.getAddress = function () {
                    $scope.item.info.address = ""
                    $http({
                        method: 'get',
                        url: 'get-address',
                        params: {zipcode: $scope.item.info.postal_code}
                    }).then(function (event) {
                        let res = event.data.results;
                        if (res != null) {
                            $scope.item.info.address = res[0].address1 + res[0].address2 + res[0].address3
                        }

                    })
                };

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
                };

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
            })
        }
    </script>
@endpush
