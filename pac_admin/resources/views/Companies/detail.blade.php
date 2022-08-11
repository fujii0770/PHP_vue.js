<style>
    input, textarea {
        outline: none;
    }
</style>
<div ng-controller="DetailController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalDetailItem" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!item.id">企業情報登録</h4>
                        <h4 class="modal-title" ng-if="item.id">企業情報更新</h4>
                        <button type="button" class="close" data-dismiss="modal" ng-click="cancelCompany()">&times;
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="d-flex justify-content-end btn-save">
                            <button type="button" class="btn btn-success" ng-click="saveCompany()" ng-if="!item.id">
                                <i class="far fa-save"></i> 登録
                            </button>
                            <button type="button" class="btn btn-info" ng-click="upload()" style="margin-right:5px"
                                    ng-if="item.id">
                                <i class="fas fa-upload"></i> 部署名入り日付印CSV取り込み
                            </button>
                            <button type="button" class="btn btn-success" ng-click="saveCompany()" ng-if="item.id">
                                <i class="far fa-save"></i> 更新
                            </button>
                        </div>
                        <div class="message message-info"></div>

                        <div class="card">
                            <div class="card-header">企業情報設定</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="company_name" class="col-md-3 col-sm-3 col-12 text-right">企業名 <span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.company_name" id="company_name" required placeholder="漢字" ng-readonly="readonly" maxlength="64"/>
                                        </div>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <input type="text" class="form-control" ng-model="item.company_name_kana" id="company_name_kana" required placeholder="カナ" ng-readonly="readonly" maxlength="64"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="domain" class="col-md-3 col-sm-3 col-12 text-right">登録できるドメイン <span style="color: red">*</span></label>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            <textarea type="text" class="form-control" ng-model="item.domain" id="domain" rows="3" required placeholder="@example.com" ng-readonly="readonly"></textarea>
                                        </div>
                                        <div class="col-md-4 col-sm-4 col-12">
                                            申請書内の[管理を行うドメイン]に記載されているドメイン名を @example.com形式で入力します。
                                            複数登録する場合は改行して下さい。
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="upper_limit"
                                               class="col-md-3 col-sm-3 col-12 text-right">ライセンス契約数<span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="number" class="form-control" ng-model="item.upper_limit" id="upper_limit" required placeholder="0" ng-readonly="readonly" min="0"/>
                                        </div>
                                        <div class="col-md-7 col-sm-7 col-12"> 申請書内の[登録印面数]に記載されている数字を入力します。</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="option_contract_count" class="col-md-3 col-sm-3 col-12 text-right">オプション契約数</label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="number" class="form-control" ng-model="item.option_contract_count" id="option_contract_count"
                                                   placeholder="0" ng-readonly="readonly" ng-disabled="item.option_contract_flg ==0"/>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="checkbox" ng-model="item.option_contract_flg" id="option_contract_flg" ng-true-value="1" ng-false-value="0"
                                                   ng-click="changeOptionContractState()" ng-change="item.option_contract_flg==0?item.option_contract_count=0:''"/>
                                            <label for="option_contract_flg">オプション契約</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="add_file_limit"
                                               class="col-md-3 col-sm-3 col-12 text-right">ファイル追加容量</label>
                                        <div class="col-md-2 col-sm-2 col-12">
                                            <input type="number" class="form-control" ng-model="item.add_file_limit" id="add_file_limit" required placeholder="0" ng-readonly="readonly" min="0"/>
                                        </div>
                                        <div class="col-md-1 col-sm-1 col-12 text-left ">
                                            GB
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="state" class="col-md-3 col-sm-3 col-12 text-right">契約状態</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="checkbox" ng-model="item.state" id="state" ng-true-value="1" ng-false-value="0" ng-click="changeState()"/>
                                            <label for="state">有効</label>
                                        </div>
                                        <label class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3">トライアル開始日</label>
                                        <label class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3"><% item.create_at.split(" ")[0] %></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="old_contract_flg"
                                               class="col-md-3 col-sm-3 col-12 text-right">旧契約形態</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="checkbox" ng-model="item.old_contract_flg" ng-true-value="1" ng-false-value="0" id="old_contract_flg"/>
                                            <label for="old_contract_flg">有効</label>
                                        </div>
                                        <label class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3">トライアル延長期限</label>
                                        <div class="col-md-3" ng-show="item.contract_edition==3">
                                            <input type="date" name="trial_period_date" value="{{ Request::get('item.trial_period_date', '') }}" onchange="changeTrialPeriodDate()"
                                                   class="form-control" placeholder="yyyy/mm/dd" id="trial_period_date">
                                            <input type="text" id="trial_create_at" value="" style='display:none'>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="test_company" class="col-md-3 col-sm-3 col-12 text-right">テスト</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="checkbox" ng-model="item.test_company" id="test_company" ng-true-value="1" ng-false-value="0" ng-click="changeTestCompanyState()"/>
                                            <label for="test_company">有効</label>
                                        </div>
                                        <label class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3">トライアル延長回数</label>
                                        <label class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3"><% item.trial_times %> 回</label>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="trial_flg"
                                               class="col-md-3 col-sm-3 col-12 text-right" ng-show="item.contract_edition==3">トライアル状態 </label>
                                        <div class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3">
                                            <input type="checkbox" ng-model="item.trial_flg" id="trial_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.contract_edition != 3 || !item.state"/>
                                            <label for="trial_flg">有効</label>
                                        </div>
                                        <label class="col-md-3 col-sm-3 col-12 text-right" ng-show="item.contract_edition!=3">本契約切替日: </label>
                                        <div class="col-md-3"  ng-show="item.contract_edition!=3">
                                            <input type="date" ng-model="item.regular_at" ng-change=""
                                                   class="form-control" placeholder="yyyy/mm/dd" id="regular_at"/>
                                        </div>
                                        <label for="trial_time" class="col-md-3 col-sm-3 col-12" ng-show="item.contract_edition==3">トライアル期間（日数）</label>
                                        <div class="col-md-2 col-sm-2 col-12" ng-show="item.contract_edition==3">
                                            <input type="text" class="form-control" ng-model="item.trial_time" id="trial_time" onchange="changeTrialTime()"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="contract_edition" class="col-md-3 col-sm-3 col-12 text-right">契約Edition<span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect($contract_editions , 'contract_edition', Request::get('contract_edition', ''),'',['class'=> 'form-control',  'ng-model'=>'item.contract_edition', 'ng-change'=>"changeContractEdition()"]) !!}
                                        </div>
                                        <label for="system_name" class="col-md-2 col-sm-2 col-12 text-right">システム名称<span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="text" class="form-control" ng-model="item.system_name" id="system_name" required ng-readonly="readonly" maxlength="256"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="remark_message" class="col-md-3 col-sm-3 col-12 text-right">備考</label>
                                        <div class="col-md-8 col-sm-9 col-12">
                                            <input type="text" class="form-control" ng-model="item.remark_message" id="remark_message" placeholder="備考" maxlength="64"/>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div>
                        @include('Edition.list_edition')
                        </div>

                        <form action="" name="adminForm" method="GET" onsubmit="return false;">
                            <div class="card" ng-if="item.guest_company_flg">
                                <div class="card-header">ゲスト企業設定</div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="guest_document_application"
                                                   class="col-md-4 col-sm-4 col-12 text-right">ゲスト企業からの文書申請</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="checkbox" ng-model="item.guest_document_application"
                                                       id="guest_document_application" ng-true-value="1"
                                                       ng-false-value="0"/>
                                                <label for="guest_document_application">有効にする</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row form-group" ng-if="!item.id">
                                        <label for="" class="control-label col-md-2 text-right-lg">企業名</label>
                                        <div class="input-group col-md-6">
                                            <input type="text" class="form-control" placeholder="企業名(部分一致)"
                                                   ng-model="search_company_name" id="search_company_name">
                                            <div class="input-group-append"
                                                 ng-click="searchCompanyParent(search_company_name)">
                                                    <span class="input-group-text btn btn-primary"><i
                                                                class="fas fa-search mr-1"></i> 検索</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div ng-if='showSearch && !item.id'>
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                表示件数:
                                                <select ng-model="parent_company_option.limit"
                                                        ng-change="loadParentCompany()"
                                                        ng-options="option for option in option_limit track by option">
                                                </select>
                                            </div>
                                        </div>
                                        <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist margin-top-5">
                                            <thead>
                                            <tr>
                                                <th scope="col" class="sort sort-column company_name"
                                                    ng-click="changeSortParentCompany('company_name')">
                                                    企業名
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col">
                                                    ホスト企業
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr ng-repeat="(indexCompany, company) in arrCompany | startFrom:currentPageCompany*parent_company_option.limit | limitTo:parent_company_option.limit">
                                                <td class="title" ng-click="chooseParentCompany(company)">
                                                    <input type="text" class="custom-input custom-width"
                                                           value="<% company.company_name  + ' (ShachihataCloud' + ((company.app_env==0)?((company.contract_server==0)?'AWS1':'AWS2'):'K5') + ')'  %>">
                                                </td>
                                                <td ng-click="chooseParentCompany(company)">
                                                    <i ng-if="company.company_id == parent_company && company.app_env == parent_env"
                                                       class="fas fa-check"></i>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="mt-3"><% countParentCompany %> 件中
                                            <% currentPageCompany * parent_company_option.limit + 1 %> 件から
                                            <% (currentPageCompany >= countParentCompany / parent_company_option.limit - 1) ? countParentCompany : currentPageCompany * parent_company_option.limit + parent_company_option.limit%>
                                            件までを表示
                                        </div>
                                        <div class="pagination-center" ng-hide="checkCountCompany()">
                                            <div class="pagination">
                                                <button ng-disabled="currentPageCompany == 0"
                                                        ng-click="currentPageCompany=currentPageCompany-1">
                                                    <i class="fas fa-backward"></i>
                                                </button>
                                                <%currentPageCompany + 1
                                                %>/<% Math.ceil(countParentCompany / parent_company_option.limit) %>
                                                <button ng-disabled="currentPageCompany >= countParentCompany/parent_company_option.limit - 1"
                                                        ng-click="currentPageCompany=currentPageCompany+1">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                                    <div ng-if='item.id && item.host_company_name'>
                                        <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5">
                                            <thead>
                                            <tr>
                                                <th class="title" scope="col">
                                                    企業名
                                                </th>
                                                <th scope="col">
                                                    ホスト企業
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="title">
                                                    <input type="text" class="custom-input custom-width"
                                                           value="<% item.host_company_name  + ' (ShachihataCloud' + ((item.host_app_env==0)?((item.host_contract_server==0)?'AWS1':'AWS2'):'K5') + ')' %>">
                                                </td>
                                                <td>
                                                    <i class="fas fa-check"></i>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="card mt-3">
                            <div class="card-header">グループウェア制約条件設定
                                <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickGWConstraintSettings()"
                                   href="#"><i id="GW_constraint_settings" class="fas fa-angle-down"></i></a>
                            </div>
                            <div class="card-body" ng-show="show_gw_constraint_settings">
                                {{--PAC_5-1807 S--}}
                                <div class="card mt-3">
                                    <div class="card-header">掲示板
                                        <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickBbsSettings()"
                                           href="#"><i id="bbs_settings" class="fas fa-angle-down"></i></a>
                                    </div>
                                    <div class="card-body" ng-show="show_bbs_settings">
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="bbs_max_attachment_size"
                                                       class="col-md-3 col-sm-3 col-12 control-label">添付ファイル容量(MB)</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.bbs_max_attachment_size"
                                                           id="bbs_max_attachment_size"
                                                            placeholder="0" maxlength="999999999"/>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-12">添付ファイル機能の1ファイルあたりの最大ファイルサイズ
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="bbs_max_total_attachment_size"
                                                       class="col-md-3 col-sm-3 col-12 control-label">添付ファイル合計容量(GB)</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.bbs_max_total_attachment_size"
                                                           id="bbs_max_total_attachment_size"
                                                            placeholder="0" maxlength="999999999"/>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-12">添付ファイル機能の合計の最大ファイルサイズ
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="bbs_max_attachment_count"
                                                       class="col-md-3 col-sm-3 col-12 control-label">添付ファイル数</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.bbs_max_attachment_count"
                                                           id="bbs_max_attachment_count"
                                                            placeholder="0" maxlength="999999999"/>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-12">添付ファイル機能の最大アップロードファイル数
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{--PAC_5-1807 E--}}
                                <div class="card mt-3">
                                    <div class="card-header">ファイルメール便
                                        <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickFileMailSettings()"
                                           href="#"><i id="file_mail_settings" class="fas fa-angle-down"></i></a>
                            </div>
                                    <div class="card-body" ng-show="show_file_mail_settings">
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="constraint_file_mail_size_single"
                                                       class="col-md-3 col-sm-3 col-12 control-label">ファイル容量(MB)</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.file_mail_size_single"
                                                           id="constraint_file_mail_size_single"
                                                           required placeholder="0" maxlength="999999999"/>
                        </div>
                                                <div class="col-md-6 col-sm-6 col-12">ファイル機能の1ファイルあたりの最大ファイルサイズ
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="constraint_file_mail_size_total"
                                                       class="col-md-3 col-sm-3 col-12 control-label">ファイル合計容量(GB)</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.file_mail_size_total"
                                                           id="constraint_file_mail_size_total"
                                                           required placeholder="0" maxlength="999999999"/>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-12">ファイル機能の合計の最大ファイルサイズ
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="constraint_file_mail_count"
                                                       class="col-md-3 col-sm-3 col-12 control-label">ファイル数</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.file_mail_count"
                                                           id="constraint_file_mail_count"
                                                           required placeholder="0" maxlength="999999999"/>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-12">ファイル機能の最大アップロードファイル数
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="constraint_file_mail_delete_days"
                                                       class="col-md-3 col-sm-3 col-12 control-label">削除日数</label>
                                                <div class="col-md-3 col-sm-3 col-12">
                                                    <input type="number" class="form-control"
                                                           ng-model="item.constraint.file_mail_delete_days"
                                                           id="constraint_file_mail_delete_days"
                                                           required placeholder="0" maxlength="999999999"/>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-12">上限越え・期限切れ後の完全削除日数
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- PAC_5-2912 S -->
                        <div class="card mt-3">
                            <div class="card-header">無害化設定
                                <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickSanitizeSettings()"
                                   href="#"><i id="sanitize_settings" class="fas fa-angle-down"></i></a>
                            </div>
                            <div class="card-body" ng-show="show_sanitize_settings">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="sanitizing_flg" class="col-md-3 col-sm-3 col-12 text-right">無害化</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="checkbox" ng-model="item.sanitizing_flg" id="sanitizing_flg" ng-true-value="1" ng-false-value="0"/>
                                            <label for="sanitizing_flg">有効にする</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-md-3 col-sm-3 col-12 text-right">回線名</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect($sanitizingList , 'sanitizing_line_name', Request::get('sanitizing_line_name', ''),'',['class'=> 'form-control', 'ng-model'=>'item.mst_sanitizing_line_id', 'ng-change'=>'sanitizeRequestLimitChange()']) !!}
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-12">回線種類を設定します</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="constraint_sanitize_request_limit"
                                               class="col-md-3 col-sm-3 col-12 text-right">無害化ファイル要求上限</label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="number" class="form-control"
                                                   ng-model="item.sanitize_request_limit"
                                                   id="constraint_sanitize_request_limit"
                                                   placeholder="0"
                                                   ng-disabled="true"/>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-12">1時間当たりの無害化要求ファイル数</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- PAC_5-2912 E -->

                        <!-- PAC_5-1902 ADD START -->
                        <div ng-if="item.id">
                            <div class="card mt-3">
                                <div class="card-header">特設サイト
                                    <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickSpecialSiteSettings()"
                                       href="#"><i id="special_site_settings" class="fas fa-angle-down"></i></a>
                                </div>
                                <div class="card-body" ng-show="show_special_site_settings">
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="received_flg" class="col-md-3 col-sm-3 col-12 text-right pl-0">受取機能</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="checkbox" ng-model="item.special_site_receive_send_available_state.is_special_site_receive_available" id="receive_flg" ng-true-value="1" ng-false-value="0" ng-click="changeReceiveSend()"/>
                                                <label for="receive_flg">有効にする</label>
                                            </div>

                                            <label for="send_flg" class="col-md-3 col-sm-3 col-12 text-right pl-0">提出機能</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="checkbox" ng-model="item.special_site_receive_send_available_state.is_special_site_send_available" id="send_flg" ng-true-value="1" ng-false-value="0" ng-click="changeReceiveSend()"/>
                                                <label for="send_flg">有効にする</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="remark_message" class="col-md-3 col-sm-3 col-12 control-label" >組織名</label>
                                            <div class="col-md-8 col-sm-9 col-12">
                                                <input type="text" class="form-control" ng-model="item.special_site_receive_send_available_state.group_name" id="group_name" maxlength="20" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="contract_edition" class="col-md-3 col-sm-3 col-12 control-label">地域名</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                {!! \App\Http\Utils\CommonUtils::buildSelect($regionList , 'region_name', Request::get('region_name', ''),'',['class'=> 'form-control',  'ng-model'=>'item.special_site_receive_send_available_state.region_name', 'ng-change'=>""]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- PAC_5-1902 ADD END -->

                        {{-- Setting constraints --}}
                        <div ng-if="item.id">
                            <div class="card mt-3">
                                <div class="card-header">制約条件設定
                                    <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickConstraintSettings()"
                                       href="#"><i id="constraint_settings" class="fas fa-angle-down"></i></a>
                                </div>
                                <div class="card-body" ng-show="show_constraint_settings">
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_max_requests"
                                                   class="col-md-3 col-sm-3 col-12 control-label">送信可能回数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_requests"
                                                       id="constraint_max_requests"
                                                        placeholder="0" maxlength="999999999"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ユーザ一日当たりの最大送信可能回数を設定します(0は無制限)
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_max_document_size"
                                                   class="col-md-3 col-sm-3 col-12 control-label">ファイルサイズ(MB)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_document_size"
                                                       id="constraint_max_document_size"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">一度に回覧可能な最大ファイルサイズを設定します</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_use_storage_percent"
                                                   class="col-md-3 col-sm-3 col-12 control-label">容量通知(%)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.use_storage_percent"
                                                       id="constraint_use_storage_percent"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">通知を行うディスク使用率を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_max_keep_days"
                                                   class="col-md-3 col-sm-3 col-12 control-label">保存日数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_keep_days"
                                                       id="constraint_max_keep_days"
                                                       placeholder="0" maxlength="100"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ファイルの最大保存期間を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_delete_informed_days_ago"
                                                   class="col-md-3 col-sm-3 col-12 control-label">削除予告日数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.delete_informed_days_ago"
                                                       id="constraint_delete_informed_days_ago"
                                                       placeholder="0" maxlength="100"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">保存期間を過ぎたファイルの削除予告を行う事前日数を設定します
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_long_term_storage_percent"
                                                   class="col-md-3 col-sm-3 col-12 control-label">長期保管ディスク容量通知(%)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.long_term_storage_percent"
                                                       id="constraint_long_term_storage_percent"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">通知を行う長期保管ディスク使用率を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_dl_request_limit"
                                                   class="col-md-3 col-sm-3 col-12 control-label">保有可能ダウンロード要求数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.dl_request_limit"
                                                       id="constraint_dl_request_limit"
                                                       placeholder="0"/>
                                            </div>

                                            <div class="col-md-6 col-sm-6 col-12">ダウンロード状況確認一覧に保有出来るダウンロード要求数(0:無制限)</div>
                                        </div>
                                        <div class="row">
                                            <label for="constraint_dl_request_limit_per_one_hour"
                                                   class="col-md-3 col-sm-3 col-12 control-label">一時間当たりのダウンロード要求数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.dl_request_limit_per_one_hour"
                                                       id="constraint_dl_request_limit_per_one_hour"
                                                       placeholder="0"/>
                                            </div>

                                            <div class="col-md-6 col-sm-6 col-12">一時間当たりのダウンロード要求可能な回数数(0:無制限)</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_board_storage_count"
                                                   class="col-md-3 col-sm-3 col-12 control-label">掲示板容量（件）</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.maxBbsCount"
                                                       id="constraint_board_storage_count"/>
                                            </div>

                                            <div class="col-md-6 col-sm-6 col-12">投稿の合計件数を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_scheduler_storage_count"
                                                   class="col-md-3 col-sm-3 col-12 control-label">スケジューラー容量（件）</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.maxScheduleCount"
                                                       id="constraint_scheduler_storage_count"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">予定の合計件数を設定します</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_dl_max_keep_days"
                                                   class="col-md-3 col-sm-3 col-12 control-label">ダウンロード最大保存期間</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.dl_max_keep_days"
                                                       id="constraint_dl_max_keep_days"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ダウンロード要求後の保存期間(単位:日)</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_dl_after_proc"
                                                   class="col-md-3 col-sm-3 col-12 control-label">ダウンロード後削除</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.dl_after_proc"
                                                       id="constraint_dl_after_proc"
                                                       placeholder="0"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ダウンロード後の動作(0:削除、1:保存期間満了後に削除)
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_dl_after_keep_days"
                                                   class="col-md-3 col-sm-3 col-12 control-label">ダウンロード後保存期間</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.dl_after_keep_days"
                                                       id="constraint_dl_after_keep_days"
                                                       placeholder="0"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ダウンロード後の保存期間(単位:日) ※最大保存期間が優先
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_dl_file_total_size_limit"
                                                   class="col-md-3 col-sm-3 col-12 control-label">ダウンロードファイル容量</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.dl_file_total_size_limit"
                                                       id="constraint_dl_file_total_size_limit"
                                                       placeholder="0"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ダウンロード保存総容量(単位:MB)</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.ip_restriction_flg">
                                        <div class="row">
                                            <label for="constraint_max_ip_address_count"
                                                   class="col-md-3 col-sm-3 col-12 control-label">IPアドレス制限登録件数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_ip_address_count"
                                                       id="constraint_max_ip_address_count"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">IPアドレス制限の登録件数上限値を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label for="constraint_max_viwer_count"
                                                   class="col-md-3 col-sm-3 col-12 control-label">閲覧ユーザー登録件数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_viwer_count"
                                                       id="constraint_max_viwer_count"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">閲覧ユーザーの登録件数上限値を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.attachment_flg">
                                        <div class="row">
                                            <label for="constraint_max_attachment_size"
                                                   class="col-md-3 col-sm-3 col-12 control-label">添付ファイル容量(MB)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_attachment_size"
                                                       id="constraint_max_attachment_size"
                                                       placeholder="0"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">添付ファイル機能の1ファイルあたりの最大ファイルサイズ</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.attachment_flg">
                                        <div class="row">
                                            <label for="constraint_max_total_attachment_size"
                                                   class="col-md-3 col-sm-3 col-12 control-label">添付ファイル合計容量(GB)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_total_attachment_size"
                                                       id="constraint_max_total_attachment_size"
                                                       placeholder="1"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">添付ファイル機能の合計の最大ファイルサイズ</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.attachment_flg">
                                        <div class="row">
                                            <label for="constraint_max_attachment_count"
                                                   class="col-md-3 col-sm-3 col-12 control-label">添付ファイル数</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control"
                                                       ng-model="item.constraint.max_attachment_count"
                                                       id="constraint_max_attachment_count"
                                                       placeholder="0"/>
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">添付ファイル機能の最大アップロードファイル数</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.frm_srv_flg">
                                        <div class="row">
                                            <label for="constraint_template_size_limit" class="col-md-3 col-sm-3 col-12 control-label">テンプレートサイズ上限(MB)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control" ng-model="item.constraint.template_size_limit" id="constraint_template_size_limit"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ササッと明細のﾃﾝﾌﾟﾚｰﾄのｻｲｽﾞ上限を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.frm_srv_flg">
                                        <div class="row">
                                            <label for="constraint_exp_template_size_limit" class="col-md-3 col-sm-3 col-12 control-label">Export用ﾃﾝﾌﾟﾚｰﾄｻｲｽﾞ上限(MB)</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control" ng-model="item.constraint.exp_template_size_limit" id="constraint_exp_template_size_limit"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ササッと明細のExport用ﾃﾝﾌﾟﾚｰﾄのｻｲｽﾞ上限を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.frm_srv_flg">
                                        <div class="row">
                                            <label for="constraint_max_template_file" class="col-md-3 col-sm-3 col-12 control-label">登録テンプレート上限</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control" ng-model="item.constraint.max_template_file" id="constraint_max_template_file"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ササッと明細の登録可能テンプレート数を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.frm_srv_flg">
                                        <div class="row">
                                            <label for="constraint_max_exp_template_file" class="col-md-3 col-sm-3 col-12 control-label">Export用登録可能ﾃﾝﾌﾟﾚｰﾄ上限</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control" ng-model="item.constraint.exp_max_template_file" id="constraint_max_exp_template_file"
                                                       placeholder="0" />
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-12">ササッと明細のExport用登録可能ﾃﾝﾌﾟﾚｰﾄ数を設定します</div>
                                        </div>
                                    </div>

                                    <div class="form-group" ng-if="item.frm_srv_flg">
                                        <div class="row">
                                            <label for="constraint_max_frm_document" class="col-md-3 col-sm-3 col-12 control-label">帳票発行文書数上限</label>
                                            <div class="col-md-3 col-sm-3 col-12">
                                                <input type="number" class="form-control" ng-model="item.constraint.max_frm_document" id="constraint_max_frm_document"
                                                       placeholder="0" />
                                </div>
                                            <div class="col-md-6 col-sm-6 col-12">帳票発行機能で発行できる文書の上限数を設定します</div>
                            </div>
                        </div>
                                </div>
                            </div>
                        </div>
{{--                        <div>--}}

{{--                                </div>--}}
{{--                        <div class="card mt-3">--}}
{{--                            <div class="card-header">パスワード設定方式--}}
{{--                                <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickPasswordSettings()"--}}
{{--                                   href="#"><i id="password_settings" class="fas fa-angle-down"></i></a>--}}
{{--                            </div>--}}
{{--                            <div class="card-body" ng-show="show_password_settings">--}}
{{--                                <div class="form-group">--}}
{{--                                    <div class="row">--}}
{{--                                        <label for="passreset_type-0"--}}
{{--                                               class="col-md-3 col-sm-3 col-12 control-label">パスワード設定方式<span--}}
{{--                                                    style="color: red">*</span></label>--}}
{{--                                        <div class="col-md-9 col-sm-9 col-12">--}}
{{--                                            <div>--}}
{{--                                                <input type="radio" ng-model="item.passreset_type"--}}
{{--                                                       id="passreset_type-0" ng-value="0" ng-change="changePassResetType()"/>--}}
{{--                                                <label for="passreset_type-0">メール</label>--}}
{{--                                            </div>--}}
{{--                                            <div>--}}
{{--                                                <input type="radio" ng-model="item.passreset_type"--}}
{{--                                                       id="passreset_type-1" ng-value="1"/>--}}
{{--                                                <label for="passreset_type-1">パスワード設定コード</label>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="form-group">--}}
{{--                                    <div class="row">--}}
{{--                                        <label for="without_email_flg" class="col-md-3 col-sm-3 col-12 text-right">メールアドレス無し</label>--}}
{{--                                        <div class="col-md-3 col-sm-3 col-12">--}}
{{--                                            <input type="checkbox" ng-model="item.without_email_flg" id="without_email_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.passreset_type"/>--}}
{{--                                            <label for="without_email_flg">有効にする</label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                        </div>--}}
                        <div class="card mt-3" ng-if="item.id" >
                            <div class="card-header">ササッとTalk設定
                                <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickTalkSettings()"
                                   href="#"><i id="talk_settings" class="fas fa-angle-down"></i></a>
                            </div>
                            <div class="card-body" ng-show="show_talk_settings">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="chat_flg" class="col-md-3 col-sm-3 col-12 text-left">契約状態</label>
                                        <span style="color: red;margin-left: -6px !important;">*</span>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input type="checkbox" ng-model="item.chat_flg" id="chat_flg" ng-true-value="1"  ng-false-value="0" ng-disabled="!item.chat_flg_disabled"  ng-change="dateReload()"/>
                                            <label for="chat_flg">有効</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="contract_type" class="col-md-3 col-sm-3 col-12 text-left">契約種別</label>
                                        <span style="color: red;margin-left: -6px !important;">*</span>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::CONTRACT_TYPE_LABEL, 'contract_type', Request::get('contract_type', ''),'',['class'=> 'form-control','ng-disabled'=>"!item.chat_flg", 'ng-model'=>'item.talk.contract_type','required','ng-change'=>""]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="contract_start_date" class="col-md-3 col-sm-3 col-12 text-left">契約開始日</label>
                                        <div class="col-md-2 col-sm-2 col-8">

                                            <input type="date" ng-model="item.talk.contract_start_date" id="contract_start_date"  ng-disabled="!item.chat_flg" ng-change=""/>
                                        </div>
                                        <label for="contract_end_date" class="text-left" style="margin-left: 120px !important;">契約終了日</label>
                                        <div class="col-md-2 col-sm-2 col-8">

                                            <input type="date" ng-model="item.talk.contract_end_date" min="<%item.talk.contract_start_date| date:'yyyy-MM-dd'%>"   id="contract_end_date" ng-disabled="!item.chat_flg"  />
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="chat_trial_flg" class="col-md-3 col-sm-3 col-12 text-left">トライアル状態</label>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input type="checkbox" ng-model="item.chat_trial_flg" id="chat_trial_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.chat_flg" ng-change="datetrialReload()"/>
                                            <label for="chat_trial_flg" class="text-left" style="">有効</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="trial_start_date" class="col-md-3 col-sm-3 col-12 text-left">トライアル開始日</label>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input type="date" ng-model="item.talk.trial_start_date" id="trial_start_date" ng-disabled="!item.chat_flg||!item.chat_trial_flg" />
                                        </div>
                                        <label for="trial_end_date" class="text-left" style="margin-left: 79px !important;">トライアル終了日</label>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input type="date" ng-model="item.talk.trial_end_date" id="trial_end_date" min="<%item.talk.trial_start_date | date:'yyyy-MM-dd'%>"   ng-disabled="!item.chat_flg||!item.chat_trial_flg" />
                                        </div>

                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="user_max_limit" class="col-md-3 col-sm-3 col-12 text-left">利用上限人数</label>
                                        <span style="color: red;margin-left: -6px !important;">*</span>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input type="number" ng-model="item.talk.user_max_limit" required min="1" id="user_max_limit" ng-disabled="!item.chat_flg" />

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="domain" class="col-md-3 col-sm-3 col-12 text-left">ドメイン</label>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input type="text" ng-model="item.talk.domain" id="domain" required ng-disabled="true" maxlength="128" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="url" class="col-md-3 col-sm-3 col-12 text-left">接続URL</label>
                                        <div class="col-md-2 col-sm-2 col-8">

                                            <input type="text" ng-model="item.talk.url" id="url" required  ng-disabled="true" />

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="storage_max_limit" class="col-md-3 col-sm-3 col-12 text-left">保管サイズ上限</label>
                                        <span style="color: red;margin-left: -6px !important;">*</span>
                                        <div class="col-md-2 col-sm-2 col-8">
                                            <input style="width: 70%;text-align: center" type="number" ng-model="item.talk.storage_max_limit" id="storage_max_limit" required min="1" ng-disabled="!item.chat_flg" max="9999"  />
                                        </div>
                                        <label style="margin-left: -3%;margin-top: 0.8%">GB</label><span style="margin-left: 10%">※メッセージとアップロードファイル合計</span>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="card mt-3">
                            <div class="card-header">ログイン設定</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="login_type-0" class="col-md-3 col-sm-3 col-12 control-label">ログイン方法<span
                                                    style="color: red">*</span></label>
                                        <div class="col-md-9 col-sm-9 col-12">
                                            <div>
                                                <input type="radio" ng-model="item.login_type" id="login_type-0"
                                                       ng-value="{{\App\Http\Utils\AppUtils::LOGIN_TYPE_NORMAL}}"/>
                                                <label for="login_type-0">メールアドレスとパスワード</label>
                                            </div>
                                            <div>
                                                <input type="radio" ng-model="item.login_type" id="login_type-1"
                                                       ng-value="{{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"/>
                                                <label for="login_type-1">シングルサインオン</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="url_domain_id" class="col-md-3 col-sm-3 col-12 control-label">URLドメイン識別<span
                                                    ng-show="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"
                                                    style="color: red">*</span></label>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <input type="text" class="form-control" ng-model="item.url_domain_id"
                                                   id="url_domain_id" maxlength="20"
                                                   ng-required="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"
                                                   placeholder="shachihata"/>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-12 pl-0 pr-0">
                                            シングルサインオンや専用ログイン画面を使用するとき、 <br/>
                                            企業を識別するための文字列となります。
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="saml_unique" class="col-md-3 col-sm-3 col-12 control-label">SAML識別子<span
                                                    ng-show="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"
                                                    style="color: red">*</span></label>
                                        <div class="col-md-9 col-sm-9 col-12">
                                            <input type="text" class="form-control" ng-model="item.saml_unique"
                                                   maxlength="100" id="saml_unique"
                                                   ng-required="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="saml_metadata" class="col-md-3 col-sm-3 col-12 control-label">SAMLメタデータ<span
                                                    ng-show="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"
                                                    style="color: red">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-12">
                                            <div class="d-inline-flex w-100">
                                                <input type="file" id="selectSamlMetadata" style="display: none"
                                                       onChange="angular.element(this).scope().selectSamlMetadata(event)"
                                                       accept="text/xml">
                                                <input type="text" class="form-control"
                                                       ng-model="item.saml_metadata_file"
                                                       ng-required="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"
                                                       id="saml_metadata" maxlength="20"
                                                       placeholder="メタデータを選択してください"
                                                       style="background: #fff; position: relative;"
                                                       ng-required="item.login_type=={{\App\Http\Utils\AppUtils::LOGIN_TYPE_SSO}}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-12">
                                            <button type="button" id="saml_metadata_button" class="btn btn-success"
                                                    onclick="$('#selectSamlMetadata').click()">
                                                <i class="fas fa-upload"></i> 選択
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">リンク設定</div>
                            <div class="card-body">

                                <div class="form-group">
                                    <div class="row">
                                        <label for="given_name" class="col-md-3 col-sm-3 col-12 control-label">ヘルプURL</label>
                                        <div class="col-md-9 col-sm-9 col-12">
                                            <input type="text" class="form-control" ng-model="item.url_help"
                                                   id="url_help"
                                                   placeholder="https://help.dstmp.com/customer-top/"/>
                                        </div>
                                    </div>
                                </div>

{{--                                <div class="form-group">--}}
{{--                                    <div class="row">--}}
{{--                                        <label for="url_contact" class="col-md-3 col-sm-3 col-12 control-label">問い合わせURL</label>--}}
{{--                                        <div class="col-md-9 col-sm-9 col-12">--}}
{{--                                            <input type="text" class="form-control" ng-model="item.url_contact"--}}
{{--                                                   id="url_contact"--}}
{{--                                                   placeholder="https://estamp.shachihata.co.jp/tou/contact-us.html"/>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

                                <div class="form-group">
                                    <div class="row">
                                        <label for="url_term"
                                               class="col-md-3 col-sm-3 col-12 control-label">会員規約URL</label>
                                        <div class="col-md-9 col-sm-9 col-12">
                                            <input type="text" class="form-control" ng-model="item.url_term"
                                                   id="url_term"
                                                   placeholder="https://estamp.shachihata.co.jp/tou/"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label for="url_policy" class="col-md-3 col-sm-3 col-12 control-label">プライバシーポリシーURL</label>
                                        <div class="col-md-9 col-sm-9 col-12">
                                            <input type="text" class="form-control" ng-model="item.url_policy"
                                                   id="url_policy"
                                                   placeholder="http://www.shachihata.co.jp/policy/index.php"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" ng-if="item.receive_plan_flg && {{!config('app.pac_app_env')?1:0}}">
                                    <div class="row">
                                        <label for="receive_plan_url" class="col-md-3 col-sm-3 col-12 control-label">受信専用プランURL</label>
                                        <div class="col-md-9 col-sm-9 col-12">
                                            <input type="text" class="form-control" readonly ng-model="item.receive_plan_url"
                                                   id="receive_plan_url"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="message message-info mt-3"></div>
                        <div ng-if="item.id">
                            <div class="card mt-3">
                                <div class="card-header">共通印設定</div>
                                <div class="card-body">
                                    <button type="button" class="btn btn-success"
                                            onclick="$('#import_file_single').click()">
                                        <i class="fas fa-plus-circle"></i> 1件追加（通知なし）
                                    </button>
                                    <button type="button" class="btn btn-success ml-5" data-toggle="modal"
                                            data-target="#modalDragStamps">
                                        <i class="fas fa-plus-circle"></i> まとめて追加（通知あり）
                                    </button>
                                    <div class="row my-3">
                                        <div class="col-sm-6 col-12">
                                            表示件数:
                                            <select ng-model="stamp_pagination.limit"
                                                    ng-change="loadStamAdmin(1,stamp_pagination.limit)"
                                                    ng-options="option for option in option_limit track by option">
                                            </select>
                                        </div>
                                    </div>
                                    <input id="import_file_single" type="file" class="hide"
                                           onChange="angular.element(this).scope().SelectFileSingle(event)"
                                           accept="image/*">

                                    <div class="stamp-list">
                                        <div class="stamp-item stamp-item-<% stamp.id %>"
                                             ng-repeat="(indexStamp, stamp) in itemStamps track by stamp.id">
                                            <div class="thumb">
                                                <span class="thumb-img">
                                                    <img ng-src="data:image/png;base64,<% stamp.stamp_image %>"
                                                         class="stamp-image"/>
                                                </span>
                                                <span class="btn btn-danger btn-circle" ng-click="editStamp(stamp)">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </div>
                                            <div class="stamp-label" style="margin-top: 15px; width: 80px;">
                                                <% stamp.stamp_name ? stamp.stamp_name : '名称未設定' %>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3"><% stamp_pagination.total %> 件中
                                        <% stamp_pagination.from || '0' %> 件から <%stamp_pagination.to || '0'%> 件までを表示
                                    </div>
                                    <div class="pagination-center"
                                         ng-hide="stamp_pagination.total <= stamp_pagination.limit">
                                        <div class="pagination">
                                            <li class="page-item pageing-button">
                                                <button ng-disabled="stamp_pagination.currentPage == 1"
                                                        ng-click="loadStamAdmin(1, stamp_pagination.limit)">
                                                    先頭
                                                </button>
                                            </li>
                                            <li class="page-item pageing-button">
                                                <button ng-disabled="stamp_pagination.currentPage == 1"
                                                        ng-click="loadStamAdmin(stamp_pagination.currentPage-1, stamp_pagination.limit)">
                                                    <i class="fas fa-backward"></i>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage - 4 < 1 || stamp_pagination.currentPage < stamp_pagination.lastPage"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage-4, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage - 4%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage - 3 < 1 || stamp_pagination.currentPage < stamp_pagination.lastPage - 1"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage-3, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage - 3%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage - 2 < 1"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage-2, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage - 2%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage - 1 < 1"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage-1, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage - 1%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage <= 0"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage, stamp_pagination.limit)"
                                                        style="color:white; background-color:black;">
                                                    <%stamp_pagination.currentPage%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage + 1 > stamp_pagination.lastPage"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage+1, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage + 1%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage + 2 > stamp_pagination.lastPage"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage+2, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage + 2%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage + 3 > stamp_pagination.lastPage || stamp_pagination.currentPage > 2"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage+3, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage + 3%>
                                                </button>
                                            </li>
                                            <li ng-hide="stamp_pagination.currentPage + 4 > stamp_pagination.lastPage || stamp_pagination.currentPage > 1"
                                                class="page-item pageing-button">
                                                <button ng-click="loadStamAdmin(stamp_pagination.currentPage+4, stamp_pagination.limit)">
                                                    <%stamp_pagination.currentPage + 4%>
                                                </button>
                                            </li>
                                            <li class="page-item pageing-button">
                                                <button ng-disabled="stamp_pagination.currentPage == stamp_pagination.lastPage"
                                                        ng-click="loadStamAdmin(stamp_pagination.currentPage+1, stamp_pagination.limit)">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                            </li>
                                            <li class="page-item pageing-button">
                                                <button ng-disabled="stamp_pagination.currentPage == stamp_pagination.lastPage"
                                                        ng-click="loadStamAdmin(stamp_pagination.lastPage, stamp_pagination.limit)">
                                                    最後
                                                </button>
                                            </li>
                                            <div style="font-size:large; text-align:center; padding-top:3px;">
                                                <%stamp_pagination.currentPage%>/<% stamp_pagination.lastPage %>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">管理者設定</div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                表示件数:
                                                <select ng-model="admin_option.limit"
                                                        ng-change="loadUserAdmin()"
                                                        ng-options="option for option in option_limit track by option">
                                                </select>
                                            </div>
                                            <div class="col-sm-6 col-12 text-right">
                                                <button type="button" class="btn btn-success"
                                                        ng-click="addUserAdmin()">
                                                    <i class="fas fa-plus-circle"></i> 追加
                                                </button>
                                            </div>
                                        </div>
                                        <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist mt-1"
                                               data-tablesaw-mode="swipe">
                                            <thead>
                                            <tr>
                                                <th class="title sort sort-column email" scope="col"
                                                    ng-click="changeSortAdmin('email')"
                                                    data-tablesaw-priority="persist">
                                                    メールアドレス
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" class="sort sort-column given_name"
                                                    ng-click="changeSortAdmin('given_name')">
                                                    氏名
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" class="sort sort-column department_name"
                                                    ng-click="changeSortAdmin('department_name')">
                                                    部署
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" class="sort sort-column phone_number"
                                                    ng-click="changeSortAdmin('phone_number')">
                                                    電話番号
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>
                                                <th scope="col" class="sort sort-column state_flg"
                                                    ng-click="changeSortAdmin('state_flg')">
                                                    状態
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>

                                                <th scope="col" width="70px" class="sort sort-column role_flg"
                                                    ng-click="changeSortAdmin('role_flg')">
                                                    利用責任者
                                                    <i class="icon fas fa-sort"></i>
                                                    <i class="icon icon-up fas fa-caret-up"></i>
                                                    <i class="icon icon-down fas fa-caret-down"></i>
                                                </th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr class="row- row-edit <% item_admin.state_flg != 1 ? 'row-disabled' : '';  %>"
                                                ng-click="editUserAdmin(item_admin.id)"
                                                ng-repeat="(key, item_admin) in itemsAdmin | startFrom:currentPage*admin_option.limit | limitTo:admin_option.limit">
                                                <td class="title"><% item_admin.email %></td>
                                                <td><% item_admin.family_name %> <% item_admin.given_name %></td>
                                                <td><% item_admin.department_name %></td>
                                                <td><% item_admin.phone_number %></td>
                                                <td><% admin_state_flg[item_admin.state_flg] %></td>
                                                <td class="text-center col-action">
                                                    <i class="fas fa-check" ng-if="item_admin.role_flg"></i>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="mt-3"><% count %> 件中 <% currentPage * admin_option.limit + 1 %>
                                            件から
                                            <% (currentPage >= count / admin_option.limit - 1) ? count : currentPage * admin_option.limit + admin_option.limit%>
                                            件までを表示
                                        </div>
                                        <div class="pagination-center" ng-hide="checkCount()">
                                            <div class="pagination">
                                                <button ng-disabled="currentPage == 0"
                                                        ng-click="currentPage=currentPage-1">
                                                    <i class="fas fa-backward"></i>
                                                </button>
                                                <%currentPage + 1 %>/<% Math.ceil(count / admin_option.limit) %>
                                                <button ng-disabled="currentPage >= count/admin_option.limit - 1"
                                                        ng-click="currentPage=currentPage+1">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                                </div>
                            </div>
                        </div>

                        <div class="message message-info mt-3"></div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"
                                ng-click="cancelCompany()">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>

                    </div>

                </div>
            </div>
            </div>
    </form>

    <form class="form_edit_admin" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-admin" id="modalDetailItemAdmin" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!itemAdmin.id">管理者情報登録</h4>
                        <h4 class="modal-title" ng-if="itemAdmin.id">管理者情報更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info-admin"></div>
                        {!! \App\Http\Utils\CommonUtils::showFormField('admin_email','メールアドレス','','email', true,
                            [ 'placeholder' =>'email@example.com', 'ng-model' =>'itemAdmin.email', 'id'=>'admin_email' ]) !!}

                        <div class="form-group">
                            <div class="row">
                                <label for="admin_given_name" class="col-md-4 control-label">氏名 <span
                                            style="color: red">*</span></label>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" required maxlength="128" class="form-control"
                                                   placeholder="姓" ng-model="itemAdmin.family_name"
                                                   id="admin_family_name"/>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" required maxlength="128" class="form-control"
                                                   placeholder="名" ng-model="itemAdmin.given_name"
                                                   id="admin_given_name"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {!! \App\Http\Utils\CommonUtils::showFormField('admin_department_name','部署','','text', false,
                                [ 'placeholder' =>'○○部', 'ng-model' =>'itemAdmin.department_name', 'id'=>'admin_department_name', 'maxlength' => 256]) !!}
                        {!! \App\Http\Utils\CommonUtils::showFormField('admin_phone_number','電話番号','','tel', false,
                                [ 'placeholder' =>'000-0000-00000', 'ng-model' =>'itemAdmin.phone_number', 'id'=>'admin_phone_number', 'pattern'=>"[0-9\-]*", 'maxlength' => 15 ]) !!}

                        <div class="form-group">
                            <div class="row">
                                <label for="admin_role_flg" class="text-right col-4">利用責任者</label>
                                <div class="col-8">
                                    <label for="admin_role_flg" style="padding-left: 20px;">
                                        <input type="checkbox" id="admin_role_flg"
                                               ng-disabled="itemAdmin.state_flg != 1 && itemAdmin.id"
                                               ng-model="itemAdmin.role_flg" ng-true-value="1" ng-false-value="0"
                                               style="margin-left: -20px; margin-right: 5px;"/>
                                        「利用責任者」は各企業1名です。 <br/>
                                        この管理者を「利用責任者」にすると、既存の「利用責任者」は通常の「管理者」に変更されます。
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="state_flg_1" class="text-right col-4">状態</label>
                                <div class="col-8">
                                    <div ng-if="!itemAdmin.id">
                                        <input type="checkbox" id="state_flg_1" ng-model="itemAdmin.sendEmail"
                                               ng-true-value="1" ng-false-value="0"/>
                                        <label for="state_flg_1">今すぐ有効にする(メール通知する)</label>
                                    </div>
                                    <div ng-if="itemAdmin.id">
                                        <input type="radio" ng-model="itemAdmin.state_flg"
                                               ng-disabled="readonly || readonlyState" ng-value="1" id="state_flg_1">
                                        <label for="state_flg_1">有効</label> &nbsp;

                                        <input type="radio" ng-model="itemAdmin.state_flg"
                                               ng-if="!itemAdmin.passwordStatus"
                                               ng-disabled="readonly || readonlyState" ng-value="0" id="state_flg_09">
                                        <input type="radio" ng-model="itemAdmin.state_flg"
                                               ng-if="itemAdmin.passwordStatus"
                                               ng-disabled="readonly || readonlyState" ng-value="9" id="state_flg_09">

                                        <label for="state_flg_09"> 無効</label>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">

                        <!-- ADD PAC_5-437 Start -->
                        <!-- 管理者削除ボタン追加（利用責任者の場合、ボタン表示されません） -->
                        <button type="button" ng-if="itemAdmin.id && !itemAdmin.role_flg" class="btn btn-danger"
                                data-toggle="modal" data-target="#modalDeleteAdminConfirm">
                            <ng-template><i class="fas fa-trash-alt"></i> 削除</ng-template>
                        </button>

                        <!-- ADD PAC_5-437 End -->
                        <button type="submit" class="btn btn-success" ng-click="saveAdmin()">
                            <ng-template ng-show="!itemAdmin.id">
                                <ng-template ng-show="itemAdmin.sendEmail"><i class="far fa-envelope"></i> 登録・通知
                                </ng-template>
                                <ng-template ng-show="!itemAdmin.sendEmail"><i class="fas fa-plus-circle"></i> 登録のみ
                                </ng-template>
                            </ng-template>
                            <ng-template ng-show="itemAdmin.id"><i class="far fa-save"></i> 更新</ng-template>
                        </button>

{{--                        <div ng-if="item.passreset_type == 0">--}}
                            <button type="button" class="btn btn-warning" ng-click="resetPasswordAdmin()"
                                    ng-show="itemAdmin.id" ng-disabled="readonlyEmailBtn">
                                <i class="far fa-envelope"></i> 初期パスワード設定
                            </button>
{{--                        </div>--}}

{{--                        <div ng-if="item.passreset_type == 1">--}}
{{--                            <meta name="csrf-token" content="{{ csrf_token() }}">--}}
{{--                            <button type="button" class="btn btn-warning" ng-click="showFormCode()" id="code_admin_id"--}}
{{--                                    value="<% itemAdmin.id %>" ng-show="itemAdmin.id">--}}
{{--                                <i class="far fa-envelope"></i> 初期パスワード設定--}}
{{--                            </button>--}}
{{--                        </div>--}}

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> 閉じる
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </form>

    <form class="form_edit_stamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-stamp" id="modalDetailItemStamp" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title" ng-if="!itemStamp.id">共通印登録</h4>
                        <h4 class="modal-title" ng-if="itemStamp.id">共通印更新</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="stamp-preview text-center">
                            <img ng-src="data:image/png;base64,<% itemStamp.stamp_image %>"
                                 style="max-height: 400px; max-width: 400px;">
                        </div>
                        <br/>
                        <div class="form-group" ng-if="list_group_show">
                            <div class="row">
                                <label for="admin_group_name" class="col-md-2 text-right control-label">グループ</label>
                                <div class="col-lg-4">
                                    <select class="form-control" ng-model="itemStamp.stamp_group.group_id">
                                        <option value="">指定なし</option>
                                        <option ng-repeat="group in list_group" ng-value="group.id">
                                            <% group.group_name %>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="admin_given_name" class="col-md-2 text-right">名称</label>
                                <div class="col-md-10"><% itemStamp.stamp_name || '名称未設定' %></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="stamp_division" class="col-md-2 text-right">日付設定</label>
                                <div class="col-md-10">
                                    <label for="stamp_division" style="padding-left: 20px;">
                                        <input type="checkbox" id="stamp_division"
                                               ng-model="itemStamp.stamp_division" ng-true-value="1" ng-false-value="0"
                                               style="margin-left: -20px; margin-right: 5px;"/>
                                        日付を含む（日付の印字領域を指定してください）
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="date_dpi" class="mt-3 col-md-2 text-right control-label">dpi</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_dpi" class="mt-3 form-control"
                                           ng-model="itemStamp.date_dpi" ng-disabled="itemStamp.stamp_division == 0"/>
                                </div>
                                <label for="" class="mt-3 col-md-2 text-right control-label"></label>
                                <div class="col-md-4">
                                </div>
                                <label for="date_x" class="mt-3 col-md-2 text-right control-label">左上X座標</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_x" class="mt-3 form-control"
                                           ng-model="itemStamp.date_x" ng-disabled="itemStamp.stamp_division == 0"/>
                                </div>
                                <label for="date_y" class="mt-3 col-md-2 text-right control-label">左上Y座標</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_y" class="mt-3 form-control"
                                           ng-model="itemStamp.date_y" ng-disabled="itemStamp.stamp_division == 0"/>
                                </div>
                                <label for="date_width" class="mt-3 col-md-2 text-right control-label">幅</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_width" class="mt-3 form-control"
                                           ng-model="itemStamp.date_width" ng-disabled="itemStamp.stamp_division == 0"/>
                                </div>
                                <label for="date_height" class="mt-3 col-md-2 text-right control-label">高さ</label>
                                <div class="col-md-4">
                                    <input type="number" required id="date_height" class="mt-3 form-control"
                                           ng-model="itemStamp.date_height"
                                           ng-disabled="itemStamp.stamp_division == 0"/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="date_dpi" class="col-md-2 text-right control-label">日付色</label>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-4">
                                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::COMMON_STAMP_DATE_COLOR , 'date_color', Request::get('date_color', ''),null,['class'=> 'form-control', 'ng-disabled' => 'itemStamp.stamp_division == 0',  'ng-model'=>'itemStamp.date_color', 'ng-change'=>"changeDateColor()"]) !!}
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control jscolor"
                                               ng-model="itemStamp.background_color"
                                               id="itemStamp-background_color" nam="background_color"
                                               ng-style="{'color': '#ffffff','background-color': '#'+itemStamp.background_color}"/>
                                    </div>
                                    <div class="col-md-4"><span class="btn btn-default" ng-click="resetBackground()">初期値に戻す</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        {{-- PAC_5-533 start --}}
                        <button type="submit" class="btn btn-success" ng-click="saveStamp()" ng-if="!itemStamp.id">
                            <i class="far fa-save"></i> 登録
                        </button>
                        <button type="submit" class="btn btn-success" ng-click="saveStamp()" ng-if="itemStamp.id">
                            <i class="far fa-save"></i> 更新
                        </button>
                        {{-- PAC_5-533 end --}}

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times-circle"></i> キャンセル
                        </button>

                        <button type="button" class="btn btn-danger" data-toggle="modal"
                                data-target="#modalDeleteConfirm" ng-if="itemStamp.id">
                            <i class="fas fa-trash-alt"></i> 削除
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <form class="form_drag_stamps" action="" method="" onsubmit="return false;">
        <div class="modal modal-drag-stamps" id="modalDragStamps" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">共通印登録</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div>下部領域に共通印ファイルをドラッグ＆ドロップしてください。</div>
                        <div id="stamp-files-area"
                             class="mt-3" style="height:100px;background-color:gainsboro;"></div>


                        <div ng-if="stampsSelected.length" class="mt-3">
                            <div>アップロードファイル</div>

                            <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist mt-1"
                                   data-tablesaw-mode="swipe">
                                <thead>
                                <tr>
                                    <th class="title" scope="col" data-tablesaw-priority="persist">No.</th>
                                    <th scope="col">アップロードファイル名</th>
                                    <th scope="col">名称</th>
                                    <th scope="col" ng-if="list_group_show" style="width: 25%">グループ</th>
                                    <th scope="col" style="width: 70px">削除</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="row- row-edit"
                                    ng-repeat="(indexStamp, stamp) in stampsSelected">
                                    <td><% indexStamp + 1 %></td>
                                    <td><% stamp.filename %></td>
                                    <td><input ng-model="stamp.stamp_name" class="form-control"></td>
                                    <td ng-if="list_group_show">
                                        <select class="form-control" ng-model="stamp.stamp_group.group_id">
                                            <option value="">指定なし</option>
                                            <option ng-repeat="group in list_group" ng-value="group.id">
                                                <% group.group_name %>
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="btn btn-default btn-sm" ng-click="removeStampSlected(indexStamp)">
                                            削除
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" ng-click="uploadStamps()">
                            <i class="far fa-save"></i> アップロード
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <div class="modal modal-drag-stamps-result" id="modalDragStampsResult" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">共通印登録</h4>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div>
                        <div class="form-group" style="line-height: 30px; border: 1px solid #ddd;">
                            <div class="row">
                                <div class="col-md-3 text-right" style="border-right: 1px solid #ddd; ">
                                    <div style="background: #f5f5f5; ">処理結果</div>
                                </div>
                                <div class="col-md-9"
                                ><% stampsSelected.length %>件の共通印が登録されました。
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">登録された共通印</div>
                        <div class="stamp-list">
                            <div class="stamp-item" ng-repeat="(indexStamp, stamp) in stampsSelected">
                                <div class="thumb">
                                    <span class="thumb-img">
                                        <img ng-src="data:image/png;base64,<% stamp.stamp_image %>"
                                             class="stamp-image"/>
                                    </span>
                                </div>
                                <div class="stamp-label" style="margin-top: 15px; width: 80px;">
                                    <% stamp.stamp_name ? stamp.stamp_name : '名称未設定' %>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" ng-click="closeResultUploadStamp()">
                        <i class="fas fa-times-circle"></i> 閉じる
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteConfirm" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title text-danger">【ご注意ください】</h5>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div style="white-space: pre-line">
                        削除した印鑑はもとに戻せません。
                        利用者に割り当てられている場合、削除と同時に割り当ては解除されます。

                        共通印を削除しますか？
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" ng-click="deleteStamp()">はい</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">いいえ</button>
                </div>

            </div>
        </div>
    </div>

    <!-- ADD PAC_5-437 Start -->
    <!-- 管理者削除 -->
    <div class="modal fade" id="modalDeleteAdminConfirm" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title text-danger">【ご注意ください】</h5>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div style="white-space: pre-line">
                        削除した管理者はもとに戻せません。

                        管理者を削除しますか？
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" ng-click="deleteAdmin()">はい</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">いいえ</button>
                </div>

            </div>
        </div>
    </div>
    <!-- ADD PAC_5-437 End -->

    <form class="form_add_company_parent" action="" method="" onsubmit="return false;">
        <div class="modal modal-add-company-parent" id="modalParentCompany" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">ゲスト企業設定</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="message message-info"></div>
                        <div class="form-group">
                            <div class="row">
                                <label for="companyParentName" class="col-md-3 col-sm-3 col-12 text-right">企業名</label>
                                <div class="col-md-9 col-sm-9 col-12">
                                    <label for="companyParentName"><% checked.company_name + ' (ShachihataCloud' + ((checked.app_env == 0) ? ((checked.contract_server == 0) ? 'AWS1' : 'AWS2') : 'K5') + ')'
                                        %></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="host_company" class="col-md-3 col-sm-3 col-12 text-right">ホスト企業</label>
                                <div class="col-md-9 col-sm-9 col-12">
                                    {{-- <input type="checkbox" id="host_company" ng-checked="checked.company_id" ng-disabled ="checked.company_id"/>--}}
                                    <label for="host_company">ホスト企業は1社のみとなります。</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-success" ng-model="checked"
                                ng-click="saveChooseParentCompany(checked)">
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
    <form class="formUserStamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-add-stamp mt-3 modal-child" id="modalPasswordCode" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">パスワード設定コード</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="" style="text-align:center; font-size:1.2em;"><% itemAdmin.email %></div>
                        <div class="my-2" style="text-align:center;">
                            <span id='code' class="py-1 px-4 font-weight-bold"
                                  style="font-size:1.2em; border: solid 1px #000;"></span>
                            <button style="font-size:2em; border: none; border-radius:18px;"
                                    ng-click="copyToClipboard()"><i class="far fa-clipboard"></i></button>
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
@push('scripts')
    <script src="{{ asset('/js/validity-polyfill.js') }}"></script>
    <script>
        function changeTrialPeriodDate() {
            var time_diff = new Date($('#trial_period_date').val()) - new Date($('#trial_create_at').val().substring(0, 10));
            var days = Math.floor(time_diff / (24 * 3600 * 1000))
            $('#trial_time').val(days);
        }

        function changeTrialTime() {
            var dt = new Date($('#trial_create_at').val().substring(0, 10));
            dt.setDate(dt.getDate() + parseInt($('#trial_time').val()));
            var y = dt.getFullYear();
            var m = ('00' + (dt.getMonth() + 1)).slice(-2);
            var d = ('00' + dt.getDate()).slice(-2);
            $('#trial_period_date').val(y + '-' + m + '-' + d);
        }

        function getAddMonthBefore(dt,add){
            add=add||0
            var month = dt.getMonth();
            dt.setMonth(month-add);

            var y = dt.getFullYear();
            var m = ('00' + (dt.getMonth() + 1)).slice(-2);
            var d = ('00' + dt.getDate()).slice(-2);
            return (y + '-' + m + '-' + d);
        }

        if (appPacAdmin) {
            appPacAdmin.controller('DetailController', function ($scope, $rootScope, $http) {
                $scope.item = {};
                $scope.sso_saml_metadata = null;
                $scope.admin_option = {limit: 10, orderBy: 'id', orderDir: 'desc', mst_company_id: null};
                $scope.admin_state_flg = {!! json_encode(\App\Http\Utils\AppUtils::ADMIN_STATE_FLG) !!};
                $scope.option_limit = [10, 50, 100];
                $scope.results = ['成功', '失敗'];
                $scope.itemsAdmin = [];
                $scope.itemAdmin = {id: 0};
                $scope.admin_readonlyEmailBtn = true;
                $scope.stamp_pagination = {};
                $scope.stampsSelected = [];
                $scope.upload_stamp_status = false;
                $scope.itemStamps = [];
                $scope.itemStamp = {
                    id: 0, stamp_division: 0,
                    date_x: '', date_y: '', date_width: '', date_height: '', date_color: '', background_color: '',
                    stamp_image: '', mst_company_id: 0, width: 0, height: 0,
                    stamp_group: {group_id: ''}
                };
                $scope.default_bg = '';
                $scope.regist_date_color = '';
                $scope.search_company_name = "";
                $scope.arrCompany = [];
                $scope.showSearch = false;
                $scope.checked = {};
                $scope.parent_company = 0;
                $scope.show_other_settings = false;
                $scope.show_gw_settings = false;
                $scope.show_talk_settings = false;
                $scope.show_gw_constraint_settings = false;
                $scope.show_special_site_settings = false;
                $scope.show_sanitize_settings = false; // PAC_5-2912
                $scope.show_constraint_settings = false;
                $scope.show_file_mail_settings = false;
                $scope.show_password_settings = false;
                $scope.parent_company_option = {limit: 10, orderBy: 'id', orderDir: 'desc'};
                $scope.list_group = [];
                $scope.list_group_show = 0;
                /*PAC_5-1807 S*/
                $scope.show_bbs_settings = false;
                /*PAC_5-1807 E*/
                $scope.origin_contract_edition = 3;
                $scope.sanitizingLimit = {!! json_encode($sanitizingLimit) !!};//PAC_5-2912
                $rootScope.$on("openNewCompany", function (event) {
                    $scope.item = {
                        id: 0,
                        login_type: 0,
                        esigned_flg: 1,
                        use_api_flg: 0,
                        department_stamp_flg: 1,
                        enable_email: 1,
                        email_format: 1,
                        state: 1,
                        ip_restriction_flg: 0,
                        permit_unregistered_ip_flg: 0,
                        enable_email_thumbnail: 0,
                        mfa_flg: 0,
                        board_flg: 1,
                        scheduler_flg: 0,
                        stamp_flg: 0,
                        long_term_storage_flg: 0,
                        guest_company_flg: 0,
                        updated_notification_email_flg: 0,
                        time_stamp_issuing_count: 0,
                        view_notification_email_flg: 0,
                        guest_document_application: 0,
                        system_name: '',
                        template_flg: 0,
                        trial_times: 0,
                        box_enabled: 0,
                        portal_flg: 0,
                        template_search_flg: 0,
                        received_only_flg: 0,
                        rotate_angle_flg: 1,
                        template_csv_flg: 0,
                        template_edit_flg: 0,
                        template_approval_route_flg: 0,
                        hr_flg: 0,
                        chat_flg: 0,
                        chat_trial_flg: 0,
                        // passreset_type: 0,
                        without_email_flg: 0,
                        sanitizing_flg: 0,
                        addressbook_only_flag: 0,
                        repage_preview_flg: 0,
                        template_route_flg: 0,
                        pdf_annotation_flg: 0,
                        bizcard_flg: 0,
                        long_term_storage_option_flg: 0,
                        long_term_folder_flg: 0,
                        attachment_flg: 0,
                        auto_save: 0,
                        frm_srv_flg:0,
                        convenient_flg: 0,
                        multiple_department_position_flg: 0,
                        old_contract_flg: 0,
                        option_contract_count: 0,
                        option_contract_flg: 0,
                        timestamps_count: 0,
                        local_stamp_flg:0,
                        with_box_flg: 0,
                        default_stamp_flg: 0,
                        confidential_flg: 0,
                        option_user_flg: 0,
                        time_stamp_assign_flg: 0,
                        dispatch_flg: 0,
                        /*PAC_5-2246 START*/
                        attendance_flg:0,
                        /*PAC_5-2246 END*/
                        file_mail_flg: 0,
                        is_show_current_company_stamp:0,
                       phone_app_flg: 0,
                        usage_flg: 0,
                        signature_flg: 0,
                        max_usable_capacity: 0,
                        scheduler_limit_flg: 0,
                        scheduler_buy_count: 0,
                        caldav_flg: 0,
                        caldav_limit_flg: 0,
                        caldav_buy_count: 0,
                        google_flg: 0,
                        outlook_flg: 0,
                        apple_flg: 0,
                        file_mail_limit_flg: 0,
                        file_mail_buy_count: 0,
                        file_mail_extend_flg: 0,
                        attendance_limit_flg: 0,
                        attendance_buy_count: 0,
                        address_list_flg:0,
                        address_list_limit_flg: 0,
                        address_list_buy_count: 0,
                        expense_flg:0,
                        discussion_flg: 0,
                        constraint:{
                            file_mail_size_single: 500,
                            file_mail_size_total: 5,
                            file_mail_count: 10,
                            file_mail_delete_days: 2,
                            /*PAC_5-1807 S*/
                            bbs_max_attachment_size:500,
                            bbs_max_total_attachment_size:5,
                            bbs_max_attachment_count:10
                            /*PAC_5-1807 E*/
                        },
                        special_site_receive_send_available_state: {
                            id: 0,
                            mst_company_id: 0,
                            is_special_site_receive_available: 0,
                            is_special_site_send_available: 0,
                        },
                        receive_user_flg: 0,
                        /*PAC_5-1698 S*/
                        user_plan_flg:0,
                        /*PAC_5-1698 E*/
                        /*PAC_5-2616 S*/
                        enable_any_address_flg:0,
                        /*PAC_5-2616 E*/
                        circular_list_csv: 0,
                        /* -- PAC_5-2352 START -- */
                        skip_flg: 0,
                        /* -- PAC_5-2352 END -- */
                         //一斉送信マスタフラグ
                        // 2353
                        is_together_send:0,
                        faq_board_flg: 0,//サポート掲示板
                        /*PAC_5-2648 E*/
                        form_user_flg: 0,
                        receive_plan_flg:0,
                        receive_plan_url:'',
                        /*PAC_5-2912 S*/
                        mst_sanitizing_line_id: 0,
                        /*PAC_5-2912 E*/
                        convenient_upper_limit: 0,
                        attendance_system_flg: 0,
                        faq_board_limit_flg: 0,
                        faq_board_buy_count: 0,
                        regular_at: new Date({!! json_encode($time_regular_at)!!}),
                        attendance_system_flg: 0,
                        to_do_list_flg: 0,
                        shared_scheduler_flg: 0,
                    };
                    hideMessages();
                    $("#trial_create_at").val(getAddMonthBefore(new Date()));
                    hasChange = false;
                    $("#modalDetailItem").modal();
                });
                $scope.contract_edition_default = {!! json_encode($contract_edition_info) !!};  //契約Editionドロップダウンメニュー

                $rootScope.$on("openEditCompany", function (event, data) {
                    hasChange = false;
                    $rootScope.$emit("showLoading");
                    $scope.item.id = data.id;
                    hideMessages();

                    $http.get(link_ajax + "/" + data.id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.item = event.data.item;
                                $("#trial_period_date").val(event.data.item.trial_period_date.toString());
                                $("#trial_create_at").val(event.data.item.create_at.toString());
                                $scope.item.regular_at =  new Date(event.data.item.regular_at);
                                $scope.item.talk =  event.data.item.talk;
                                $scope.item.chat_flg_disabled = $scope.item.chat_flg;
                                if ($scope.item.talk) {
                                    if ($scope.item.talk.contract_start_date){
                                    $scope.item.talk.contract_start_date =  new Date( event.data.item.talk.contract_start_date);
                                    $scope.item.talk.contract_start_date_reload = new Date(event.data.item.talk.contract_start_date);
                                    }
                                    if ($scope.item.talk.contract_end_date){
                                    $scope.item.talk.contract_end_date = new Date(event.data.item.talk.contract_end_date);
                                    $scope.item.talk.contract_end_date_reload = new Date(event.data.item.talk.contract_end_date);
                                    }
                                    if ($scope.item.talk.trial_start_date){
                                        $scope.item.talk.trial_start_date = new Date(event.data.item.talk.trial_start_date);
                                        $scope.item.trial_start_date_reload = new Date(event.data.item.talk.trial_start_date);
                                    }
                                    if ($scope.item.talk.trial_end_date){
                                    $scope.item.talk.trial_end_date = new Date(event.data.item.talk.trial_end_date);
                                    $scope.item.trial_end_date_reload = new Date(event.data.item.talk.trial_end_date);
                                    }
                                    if (!$scope.item.talk.contract_start_date && !$scope.item.talk.contract_end_date){
                                        $scope.item.talk.contract_start_date_reload = null;
                                        $scope.item.talk.contract_end_date_reload =null;
                                    }
                                    if (!$scope.item.talk.trial_start_date && !$scope.item.talk.trial_end_date){
                                        $scope.item.trial_start_date_reload = null;
                                        $scope.item.trial_end_date_reload = null;
                                    }
                                    //
                                    $scope.item.talk.contract_type = event.data.item.talk.contract_type.toString();
                                }
                                $scope.item.contract_edition = event.data.item.contract_edition.toString();
                                $scope.origin_contract_edition = event.data.item.contract_edition.toString();
                                //PAC_5-2912 S
                                if($scope.item.mst_sanitizing_line_id){
                                    $scope.item.mst_sanitizing_line_id = $scope.item.mst_sanitizing_line_id.toString();
                                }
                                //PAC_5-2912 E

                                if (event.data.item.saml_metadata) {
                                    $scope.item.saml_metadata_file = JSON.parse(event.data.item.saml_metadata).filename;
                                } else {
                                    $scope.item.saml_metadata_file = '';
                                }
                                /*PAC_5-3056 S*/
                                if(event.data.message){
                                    $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                                }
                                /*PAC_5-3056 E*/
                            }
                        });
                    $scope.loadUserAdmin();
                    $scope.loadStamAdmin($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                    $("#modalDetailItem").modal();
                });

                $scope.uploadSamlMetadata = function () {
                    if ($scope.sso_saml_metadata) {
                        let formdata = new FormData();
                        formdata.append("file", $scope.sso_saml_metadata);
                        formdata.append("mst_company_id", $scope.item.id);
                        $http.post(link_ajax + '/upload-saml-metadata', formdata, {headers: {'Content-Type': undefined}})
                            .then(function (event) {
                                $rootScope.$emit("hideLoading");
                                if (event.data.status == false) {
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }
                            })
                    }
                }
                $scope.selectSamlMetadata = function ($event) {
                    if ($event.target.files.length) {
                        const file = $event.target.files[0];
                        $scope.sso_saml_metadata = file;
                        $scope.item.saml_metadata_file = file.name;
                        $('#saml_metadata').val(file.name);
                    }
                }
                /*PAC_5-2376 S*/
                $scope.boardFlgCheck = function() {
                    if ($scope.item.board_flg == 1 || $scope.item.scheduler_flg == 1) {
                        $scope.item.portal_flg = 1;
                    } else {
                        $scope.item.portal_flg = 0;
                    }
                }
                $scope.dateReload = function() {
                    if ($scope.item.chat_flg == 0 && $scope.item.talk){
                        $scope.item.chat_trial_flg = 0;
                        $scope.item.talk.trial_start_date = $scope.item.trial_start_date_reload;
                        $scope.item.talk.trial_end_date = $scope.item.trial_end_date_reload;
                        $scope.item.talk.contract_end_date = $scope.item.talk.contract_end_date_reload;
                        $scope.item.talk.contract_start_date = $scope.item.talk.contract_start_date_reload;
                    }
                }
                $scope.datetrialReload = function() {
                    if ($scope.item.chat_trial_flg ==0 && $scope.item.talk){
                        $scope.item.talk.trial_start_date = $scope.item.trial_start_date_reload;
                        $scope.item.talk.trial_end_date = $scope.item.trial_end_date_reload;
                    }
                }


                /*PAC_5-2376 E*/
                /*PAC_5-2648 S*/
                $scope.faqBoardFlgCheck = function() {
                    if ($scope.item.faq_board_flg == 1
                        || $scope.item.board_flg == 1
                        || $scope.item.scheduler_flg == 1
                        || $scope.item.attendance_flg == 1) {
                        $scope.item.portal_flg = 1;
                    }else{
                        $scope.item.portal_flg =0;
                    }
                    if(!$scope.item.faq_board_flg){
                        $scope.item.faq_board_limit_flg =0;
                        $scope.item.faq_board_buy_count = 0;
                    }
                }
                /*PAC_5-2648 E*/
                <!-- PAC_14-32 ADD START -->
                $scope.gwFlgCheck = function(){
                    if($scope.item.board_flg == 1
                        || $scope.item.scheduler_flg == 1
                        || $scope.item.attendance_flg == 1
                        || $scope.item.address_list_flg == 1){
                        $scope.item.portal_flg =1;
                    }else{
                        $scope.item.portal_flg =0;
                    }
                    if ($scope.item.scheduler_flg){
                        $scope.item.caldav_flg = 0;
                        $scope.item.scheduler_limit_flg = 0;
                        $scope.item.scheduler_buy_count = 0;
                        $scope.item.caldav_limit_flg = 0;
                        $scope.item.caldav_buy_count = 0;
                        $scope.item.shared_scheduler_flg = 0;
                    }
                    if (!$scope.item.scheduler_flg){
                        $scope.item.caldav_flg = 0;
                        $scope.item.scheduler_limit_flg = 0;
                        $scope.item.scheduler_buy_count = 0;
                        $scope.item.caldav_limit_flg = 0;
                        $scope.item.caldav_buy_count = 0;
                        $scope.item.google_flg = 0;
                        $scope.item.outlook_flg = 0;
                        $scope.item.apple_flg = 0;
                        $scope.item.shared_scheduler_flg = 0;
                    }
                }
                $scope.ipFlgCheck = function () {
                    if ($scope.item.ip_restriction_flg == 0) {
                        $scope.item.permit_unregistered_ip_flg = 0;
                    }
                }
                <!-- PAC_14-32 ADD END -->
                <!-- PAC_5-2298 ADD START -->
                $scope.caldavFlgCheck = function(){
                    if(!$scope.item.caldav_flg){
                        $scope.item.caldav_limit_flg =0;
                        $scope.item.caldav_buy_count = 0;
                        $scope.item.google_flg = 0;
                        $scope.item.outlook_flg = 0;
                        $scope.item.apple_flg = 0;
                    }
                }
                $scope.fileMailFlgCheck = function(){
                    if(!$scope.item.file_mail_flg){
                        $scope.item.file_mail_limit_flg =0;
                        $scope.item.file_mail_buy_count = 0;
                        $scope.item.file_mail_extend_flg = 0;
                    }
                }
                <!-- PAC_5-2298 ADD END -->
                <!-- PAC_5-2246 ADD START -->
                $scope.attendanceFlgCheck = function(){
                    if($scope.item.board_flg == 1 || $scope.item.scheduler_flg == 1 || $scope.item.attendance_flg == 1){
                        $scope.item.portal_flg =1;
                    }else{
                        $scope.item.portal_flg =0;
                    }
                    if(!$scope.item.attendance_flg){
                        $scope.item.attendance_limit_flg =0;
                        $scope.item.attendance_buy_count = 0;
                    }
                }
                $scope.addressListFlgCheck = function(){
                    if($scope.item.board_flg == 1 || $scope.item.scheduler_flg == 1 || $scope.item.address_list_flg == 1){
                        $scope.item.portal_flg =1;
                    }else{
                        $scope.item.portal_flg =0;
                    }
                    if(!$scope.item.address_list_flg){
                        $scope.item.address_list_limit_flg =0;
                        $scope.item.address_list_buy_count = 0;
                    }
                }
                <!-- PAC_5-2246 ADD END -->
                $scope.toDoListFlgCheck = function(){
                    if($scope.item.to_do_list_flg == 1 || $scope.item.to_do_list_flg == 1 || $scope.item.to_do_list_flg == 1){
                        $scope.item.portal_flg =1;
                    }else{
                        $scope.item.portal_flg =0;
                    }
                    if(!$scope.item.to_do_list_flg){
                        $scope.item.to_do_list_limit_flg =0;
                        $scope.item.to_do_list_buy_count = 0;
                    }
                }
                $scope.saveCompany = function (callSuccess) {
                    if($scope.storeCompanyCheck()){
                    if ($(".form_edit")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        <!-- PAC_5-2663 -->
                        if($scope.item.talk){
                            $scope.item.chat_flg_disabled = $scope.item.chat_flg;
                            $scope.item.talk.contract_start_date_reload = $scope.item.talk.contract_start_date;
                            $scope.item.talk.contract_end_date_reload = $scope.item.talk.contract_end_date;
                            $scope.item.trial_end_date_reload = $scope.item.talk.trial_end_date;
                            $scope.item.trial_start_date_reload = $scope.item.talk.trial_start_date;
                        }
                        <!-- PAC_PAC_14-84 GWのいずれかがチェックON -->
                        if ($scope.item.scheduler_flg == 1
                            || $scope.item.caldav_flg == 1
                            || $scope.item.attendance_flg == 1
                            || $scope.item.file_mail_flg == 1
                            || $scope.item.faq_board_flg == 1
                            || $scope.item.to_do_list_flg == 1
                            || $scope.item.address_list_flg == 1) {
                            $scope.item.gw_flg = 1;
                            $scope.item.portal_flg = 1;
                        } else {
                            $scope.item.gw_flg = 0;
                        }
                        if (!$scope.item.scheduler_flg){
                            $scope.item.caldav_flg = 0;
                            $scope.item.scheduler_limit_flg = 0;
                            $scope.item.scheduler_buy_count = 0;
                            $scope.item.caldav_limit_flg = 0;
                            $scope.item.caldav_buy_count = 0;
                            $scope.item.google_flg = 0;
                            $scope.item.outlook_flg = 0;
                            $scope.item.apple_flg = 0;
                        }
                        <!-- PAC_PAC_14-37 ADD END -->
                        <!-- PAC_PAC_5-2298 ADD START -->
                        if(!$scope.item.caldav_flg){
                            $scope.item.caldav_limit_flg =0;
                            $scope.item.caldav_buy_count = 0;
                            $scope.item.google_flg = 0;
                            $scope.item.outlook_flg = 0;
                            $scope.item.apple_flg = 0;
                        }
                        if($scope.item.scheduler_limit_flg){
                            $scope.item.scheduler_buy_count = 0;
                        }
                        if($scope.item.caldav_limit_flg){
                            $scope.item.caldav_buy_count = 0;
                        }
                        <!-- PAC_PAC_5-2298 ADD END -->

                        <!-- PAC_PAC_5-1680 ADD START -->
                        if ($scope.item.long_term_storage_flg == 0) {
                            $scope.item.auto_save = 0;
                        }
                        <!-- PAC_PAC_5-1680 ADD START -->

                        <!-- PAC_PAC_5-2276 ADD START -->
                        if($scope.item.long_term_storage_flg == 0 || $scope.item.long_term_storage_option_flg == 0 || $scope.item.stamp_flg == 0){
                            $scope.item.time_stamp_assign_flg = 0;
                        }
                        <!-- PAC_PAC_5-2276 ADD START -->

                        if(!$scope.item.attendance_system_flg) {
                            $scope.item.attendance_system_flg = 0;
                        }

                        var saveSuccess = function (event) {
                            $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    if(!$scope.item.id){
                                        $scope.item.id = event.data.id;
                                        $scope.item.constraint = event.data.constraint;
                                        $scope.item.special_site_receive_send_available_state = event.data.special_site_receive_send_available_state;
                                    }
                                    $scope.item.app_limit_id  = event.data.app_limit_id;
                                    $scope.item.board_flg_org = event.data.board_flg_org;
                                    $scope.item.scheduler_flg_org = event.data.scheduler_flg_org;
                                    $scope.item.caldav_flg_org = event.data.caldav_flg_org;
                                    $scope.item.attendance_flg_org = event.data.attendance_flg_org;
                                    $scope.item.file_mail_flg_org = event.data.file_mail_flg_org;
                                    /*PAC_5-2246 S*/
                                    $scope.item.enable_any_address_flg_org = event.data.enable_any_address_flg_org;
                                    /*PAC_5-2246 E*/
                                    $scope.item.faq_board_flg_org = event.data.faq_board_flg_org;
                                    $scope.item.address_list_flg_org = event.data.address_list_flg_org;
                                    $scope.item.receive_plan_url = event.data.receive_plan_url;
                                    $scope.item.shared_scheduler_flg_org = event.data.shared_scheduler_flg_org;
                                    $scope.uploadSamlMetadata();
                                    if(event.data.gw_failed){
                                        $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                    hasChange = true;
                                    if(callSuccess) callSuccess();
                                }
                        };

                        if ($('#trial_time').val()) {
                            $scope.item.trial_time = $('#trial_time').val()
                        }
                        // console.log($scope.item);
                        if (!$scope.item.talk || !$scope.item.talk.hasOwnProperty('id')) {
                            $scope.item.talk = null;
                        }
                        if (!$scope.item.id) {
                            $http.post(link_ajax, {item: $scope.item})
                                .then(saveSuccess);
                        } else {
                            $http.put(link_ajax + "/" +$scope.item.id, {item: $scope.item, special: JSON.stringify($scope.item.special_site_receive_send_available_state)})
                                .then(saveSuccess);
                        }
                    } else {
                        $(".form_edit")[0].reportValidity()
                    }
                    }
                };

                $scope.cancelCompany = function () {
                    location.reload();
                };

                $scope.loadStamAdmin = function (page, limit) {
                    $rootScope.$emit("showLoading");
                    limit = limit || 10;
                    $http.get(link_ajax_stamp + "/" + $scope.item.id, {params: {page: page, limit: limit}})
                        .then(function (event) {
                            if (event.data.status == false) {
                                $("#modalDetailItemStamp .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                let paginate = event.data.itemsStamp;
                                $scope.itemStamps = paginate.data;
                                $scope.stamp_pagination = {
                                    currentPage: paginate.current_page,
                                    from: paginate.from,
                                    to: paginate.to,
                                    total: paginate.total,
                                    lastPage: paginate.last_page,
                                    limit: paginate.per_page
                                };
                                $rootScope.$emit("hideLoading");
                                $scope.list_group = event.data.list_group;
                                $scope.list_group_show = event.data.list_group_show;
                                // group state = 0 -> group id clear
                                for (let i in $scope.itemStamps) {
                                    let stamp = $scope.itemStamps[i];
                                    if (stamp.stamp_group != null) {
                                        // group data あり
                                        if (stamp.stamp_group.state == 0) {
                                            $scope.itemStamps[i].stamp_group.group_id = '';

                                        }
                                    }
                                }
                            }
                        });
                };

                $scope.SelectFileSingle = function ($event) {
                    if ($event.target.files.length) {
                        readFileImageAsync($event.target.files[0]).then(function (file) {
                            $scope.$apply(function () {
                                $scope.itemStamp = {
                                    id: 0, stamp_division: 0, date_x: '', date_y: '',
                                    date_width: '', date_height: '', stamp_image: file.data_image, filename: file.name,
                                    width: file.width * 85, height: file.height * 85, stamp_group: {group_id: ''}
                                };
                                $("#modalDetailItemStamp").modal();
                                $("#import_file_single").val('');
                            });
                        });

                    }
                    ;
                };

                $scope.editStamp = function (stamp) {
                    $scope.itemStamp = stamp;
                    $scope.default_bg = stamp.background_color
                    $("#modalDetailItemStamp").modal();
                }
                $scope.chooseParentCompany = function (company) {
                    hideMessages();
                    hasChange = false;
                    if (company) {
                        $scope.checked = company;
                    }
                    $("#modalParentCompany").modal();
                };

                $scope.saveChooseParentCompany = function (company) {
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    hasChange = false;
                    if (company) {
                        $scope.parent_company = company.company_id;
                        $scope.parent_env = company.app_env;
                        $scope.item.mst_company_id = company.company_id;
                        $scope.item.host_app_env = company.app_env;
                        $scope.item.host_contract_server = company.contract_server;
                        $scope.item.host_company_name = company.company_name;
                        $("#modalParentCompany .message").append(showMessages(["ゲスト企業設定を更新しました。"], 'success', 10000));
                    }
                    $("#modalParentCompany").modal('hide');
                    $rootScope.$emit("hideLoading");
                };

                $scope.searchCompanyParent = function (search_company_name) {
                    $scope.showSearch = true;
                    $scope.search_company_name = search_company_name;
                    $rootScope.$emit("showLoading");
                    $http.post(link_list_company, {host_company_name: $scope.search_company_name})
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $scope.arrCompany = [];
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.arrCompany = event.data.listCompany;
                                $scope.countParentCompany = $scope.arrCompany.length;
                            }
                        });
                    $scope.currentPageCompany = 0;
                    $scope.Math = window.Math;
                    $scope.checkCountCompany = function () {
                        if ($scope.countParentCompany >= $scope.parent_company_option.limit) {
                            return false;
                        }
                        return true;
                    };
                };

                $scope.loadParentCompany = function () {
                    $scope.countParentCompany = $scope.arrCompany.length;
                    $scope.currentPageCompany = 0;
                    $scope.Math = window.Math;
                    $scope.checkCountCompany = function () {
                        if ($scope.countParentCompany >= $scope.parent_company_option.limit) {
                            return false;
                        }
                        return true;
                    };
                };

                $scope.changeState = function () {
                    // トライアル状態
                    // 契約状態を無効に切り替えた場合、クリア
                    var item = $("#state");
                    if (!$(item).is(":checked")) {
                        $scope.item.trial_flg = 0;
                    }
                };
                // オプション契約状態変更
                $scope.changeOptionContractState = function () {
                    if ($("#option_contract_flg").is(":checked")) {
                        $("#option_contract_count").attr('required', true);
                        $("#option_contract_count").attr('min', 1);
                    } else {
                        $("#option_contract_count").attr('required', false);
                        $("#option_contract_count").attr('min', 0);
                    }
                };
                $scope.changeTestCompanyState = function () {
                    var item = $("#test_company");
                    if (!$(item).is(":checked")) {
                        $scope.item.test_company = 0;
                    }
                };

                // 1902 START
                $scope.changeReceiveSend = function(){
                    var receive_flg = $("#receive_flg");
                    var send_flg = $("#send_flg");
                    if($(receive_flg).is(":checked") || $(send_flg).is(":checked")){
                        if($scope.item.special_site_receive_send_available_state.group_name == "" || $scope.item.special_site_receive_send_available_state.group_name == undefined){
                            $scope.item.special_site_receive_send_available_state.group_name = $scope.item.company_name;
                        }
                    } else {
                        $scope.item.special_site_receive_send_available_state.group_name = "";
                    }
                }
                // 1902 END

                $scope.changeLongTermStorageFlg = function () {
                    // PAC_5-1675: if contract_edition is 'Business' or 'Business Pro' and LongTermStorageFlg is chosen, auto choose LongTermStorageOptionFlg
                    var item = $("#long_term_storage_flg");
                    if ($scope.item.contract_edition == 2 && $(item).is(":checked")) {
                        $scope.item.max_usable_capacity = 100;
                    }
                    if ($scope.item.contract_edition == 3 && $(item).is(":checked")) {
                        $scope.item.long_term_storage_option_flg = 1;
                        $scope.item.max_usable_capacity = 100;
                    }
                    if (!$(item).is(":checked")) {
                        $scope.item.long_term_storage_option_flg = 0;
                        $scope.item.long_term_folder_flg = 0;
                        $scope.item.max_usable_capacity = 0;
                    }
                };
                $scope.changeTemplateFlg = function () {
                    if (!$("#template_flg").is(":checked")) {
                        $scope.item.template_search_flg = 0;
                        $scope.item.template_csv_flg = 0;
                        $scope.item.template_edit_flg = 0;
                        $scope.item.template_approval_route_flg = 0;
                    }
                };
                $scope.changeStampFlg = function () {
                    if (!$("#stamp_flg").is(":checked")) {
                        $scope.item.timestamps_count = 0;
                        $scope.item.time_stamp_issuing_count = 0;
                    }
                };
                $scope.changeConvenientFlg = function () {
                    if (!$("#convenient_flg").is(":checked")) {
                        $scope.item.convenient_upper_limit = 0;
                    }
                };
                // その他設定項目
                $scope.clickOtherSettings = function () {
                    if ($scope.show_other_settings) {
                        $scope.show_other_settings = false;
                        $("#other_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_other_settings = true;
                        $("#other_settings").attr('class','fas fa-angle-up');
                    }
                };
                // グループウェア機能設定表示/非表示
                $scope.clickGWSettings = function () {
                    if ($scope.show_gw_settings) {
                        $scope.show_gw_settings = false;
                        $("#GW_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_gw_settings = true;
                        $("#GW_settings").attr('class','fas fa-angle-up');
                    }
                };
                $scope.clickTalkSettings = function () {
                    if ($scope.show_talk_settings) {
                        $scope.show_talk_settings = false;
                        $("#talk_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_talk_settings = true;
                        $("#talk_settings").attr('class','fas fa-angle-up');
                    }
                };
                // グループウェア制約条件設定表示/非表示
                $scope.clickGWConstraintSettings = function () {
                    if ($scope.show_gw_constraint_settings) {
                        $scope.show_gw_constraint_settings = false;
                        $("#GW_constraint_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_gw_constraint_settings = true;
                        $("#GW_constraint_settings").attr('class','fas fa-angle-up');
                    }
                };
                // PAC_5-2912 S
                // 無害化設定表示/非表示
                $scope.clickSanitizeSettings = function () {
                    if ($scope.show_sanitize_settings) {
                        $scope.show_sanitize_settings = false;
                        $("#sanitize_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_sanitize_settings = true;
                        $("#sanitize_settings").attr('class','fas fa-angle-up');
                    }
                };
                // PAC_5-2912 E
                // 特設サイト表示/非表示
                $scope.clickSpecialSiteSettings = function () {
                    if ($scope.show_special_site_settings) {
                        $scope.show_special_site_settings = false;
                        $("#special_site_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_special_site_settings = true;
                        $("#special_site_settings").attr('class','fas fa-angle-up');
                    }
                };
                // 制約条件表示/非表示
                $scope.clickConstraintSettings = function () {
                    if ($scope.show_constraint_settings) {
                        $scope.show_constraint_settings = false;
                        $("#constraint_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_constraint_settings = true;
                        $("#constraint_settings").attr('class','fas fa-angle-up');
                    }
                };
                $scope.clickFileMailSettings = function () {
                    if ($scope.show_file_mail_settings) {
                        $scope.show_file_mail_settings = false;
                        $("#file_mail_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_file_mail_settings = true;
                        $("#file_mail_settings").attr('class','fas fa-angle-up');
                    }
                };
                /*PAC_5-1807 S*/
                $scope.clickBbsSettings = function () {
                    if ($scope.show_bbs_settings) {
                        $scope.show_bbs_settings = false;
                        $("#bbs_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_bbs_settings = true;
                        $("#bbs_settings").attr('class','fas fa-angle-up');
                    }
                };
                /*PAC_5-1807 E*/
                // パスワード設定方式表示/非表示
                $scope.clickPasswordSettings = function () {
                    if ($scope.show_password_settings) {
                        $scope.show_password_settings = false;
                        $("#password_settings").attr('class','fas fa-angle-down');
                    }else {
                        $scope.show_password_settings = true;
                        $("#password_settings").attr('class','fas fa-angle-up');
                    }
                };

                //PAC_5-2912 S
                $scope.sanitizeRequestLimitChange = function () {
                    if($scope.item.mst_sanitizing_line_id){
                        $scope.sanitizingLimit.forEach(function(item){
                            if($scope.item.mst_sanitizing_line_id == item.id){
                                $scope.item.sanitize_request_limit = item.sanitize_request_limit;
                            }
                        });
                    }else{
                        $scope.item.sanitize_request_limit = 0;
                    }
                }
                //PAC_5-2012 E

            $scope.changeContractEdition = function(){
                    $scope.item.system_name = 'Shachihata Cloud';
                    $scope.item.trial_flg = 0;

                    let test = $scope.contract_edition_default.filter(function (item){
                        return item.contract_edition == $scope.item.contract_edition;
                    });

                    var extend = function (o, n) {
                        for (var p in o) {
                            if (p != 'id' && n.hasOwnProperty(p))
                                o[p] = n[p];
                        }
                        return o;
                    };

                    $scope.item = extend($scope.item, test[0]);
                    if( typeof $scope.item.contract_edition=='number') {
                        $scope.item.contract_edition += '';

                    }
                };

                $scope.resetBackground = function () {
                    $scope.itemStamp.background_color = $scope.default_bg;
                }

                $scope.changeSortParentCompany = function (orderBy) {
                    if ($scope.parent_company_option.orderBy == orderBy) {
                        $scope.parent_company_option.orderDir = $scope.parent_company_option.orderDir == 'asc' ? 'desc' : 'asc';
                    }
                    $scope.parent_company_option.orderBy = orderBy;
                    $http.post(link_list_company, {
                        host_company_name: $scope.search_company_name,
                        orderBy: $scope.parent_company_option.orderBy,
                        orderDir: $scope.parent_company_option.orderDir
                    })
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $scope.arrCompany = [];
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.arrCompany = event.data.listCompany;
                                $scope.countParentCompany = $scope.arrCompany.length;
                            }
                        });
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $(".table-sort-client .sort-column." + orderBy).addClass('active');
                    if ($scope.parent_company_option.orderDir == 'asc')
                        $(".table-sort-client .sort-column." + orderBy).addClass('active-up');
                    else $(".table-sort-client .sort-column." + orderBy).addClass('active-down');
                };

                $scope.saveStamp = function () {
                    if ($scope.itemStamp.stamp_division) {
                        if (!$(".form_edit_stamp")[0].checkValidity()) {
                            return;
                        }
                    }
                    hideMessages();
                    $rootScope.$emit("showLoading");

                    var saveSuccess = function (event) {
                        $rootScope.$emit("hideLoading");
                        if (event.data.status == false) {
                            $("#modalDetailItemStamp .message-info").append(showMessages(event.data.message, 'danger', 10000));
                        } else {
                            if (!$scope.itemStamp.id) {
                                $scope.itemStamp.id = event.data.id;
                            }
                            $scope.loadStamAdmin($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                            // PAC_5-477 BEGIN
                            // after update, show stamp image
                            $scope.itemStamp.stamp_image = event.data.show_stamp_image;
                            // PAC_5-477 END
                            // $("#modalDetailItemStamp").modal('hide');
                            $("#modalDetailItemStamp .message-info").append(showMessages(event.data.message, 'success', 10000));

                            $scope.itemStamp.date_color = $scope.regist_date_color
                            $scope.default_bg = $scope.itemStamp.background_color
                        }
                    };

                    $scope.regist_date_color = $scope.itemStamp.date_color
                    const updateData = $scope.itemStamp
                    if ($scope.itemStamp.date_color === 'other') {
                        updateData.date_color = $scope.itemStamp.background_color
                    }

                    if (!$scope.itemStamp.id) {
                        $http.post(link_ajax_stamp + "/" + $scope.item.id, {items: [updateData], notify: false})
                            .then(saveSuccess);
                    } else {
                        $http.put(link_ajax_stamp + "/" + $scope.item.id + "/" + $scope.itemStamp.id, {item: updateData})
                            .then(saveSuccess);
                    }
                    $scope.loadStamAdmin();
                };


                $scope.changeDateColor = function () {
                    if ($scope.itemStamp.date_color !== 'other') {
                        $scope.itemStamp.background_color = $scope.itemStamp.date_color != '' ? $scope.itemStamp.date_color : null
                    }
                }

                $scope.deleteStamp = function () {
                    $rootScope.$emit("showLoading");
                    $http.delete(link_ajax_stamp + "/" + $scope.item.id + "/" + $scope.itemStamp.id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDeleteConfirm").modal('hide');
                                $("#modalDetailItemStamp .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $("#modalDeleteConfirm").modal('hide');
                                if ($scope.itemStamps.length <= 1) {
                                    $scope.loadStamAdmin(1, $scope.stamp_pagination.limit);
                                } else {
                                    $scope.loadStamAdmin($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                                }
                                $("#modalDetailItemStamp").modal('hide');
                                $("#modalDetailItem .message-info").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                };

                $scope.stampDragover = function (ev) {
                    ev.preventDefault();
                    console.log('stampDragover');
                    console.log(ev);
                };

                $scope.stampDrop = function (files) {
                    if (files && files.length) {
                        $rootScope.$emit("showLoading");
                        for (var i = 0; i < files.length; i++) {
                            readFileImageAsync(files[i]).then(function (file) {
                                $scope.$apply(function () {
                                    $scope.stampsSelected.push({
                                        id: 0,
                                        stamp_division: 0,
                                        date_x: '',
                                        date_y: '',
                                        date_width: '',
                                        date_height: '',
                                        stamp_image: file.data_image,
                                        width: file.width * 85,
                                        height: file.height * 85,
                                        filename: file.name,
                                        stamp_name: '',
                                        stamp_group: {group_id: ''}
                                    });
                                });
                            });
                        }
                        $rootScope.$emit("hideLoading");
                        $scope.$apply();
                    }
                };

                $scope.removeStampSlected = function (index) {
                    $scope.stampsSelected.splice(index, 1);
                };

                $scope.uploadStamps = function () {
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_stamp + "/" + $scope.item.id, {items: $scope.stampsSelected, notify: true})
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDragStamps .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.upload_stamp_status = true;
                                $("#modalDragStamps").modal('hide');
                                $("#modalDragStampsResult").modal();
                            }

                        });
                };

                $scope.closeResultUploadStamp = function () {
                    $scope.loadStamAdmin($scope.stamp_pagination.currentPage, $scope.stamp_pagination.limit);
                    $scope.upload_stamp_status = false;
                    $("#modalDragStampsResult").modal('hide');
                    $scope.stampsSelected = [];

                };
                // user admin
                $scope.loadUserAdmin = function () {
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_indexadmin + "/" + $scope.item.id, $scope.admin_option)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            $scope.itemsAdmin = event.data.items;
                            $scope.currentPage = 0;
                            $scope.count = $scope.itemsAdmin.length;
                            $scope.Math = window.Math;
                            $scope.checkCount = function () {
                                if ($scope.count >= $scope.admin_option.limit) {
                                    return false;
                                }
                                return true;
                            };
                        });

                };

                $scope.changeSortAdmin = function (orderBy) {
                    if ($scope.admin_option.orderBy == orderBy) {
                        $scope.admin_option.orderDir = $scope.admin_option.orderDir == 'asc' ? 'desc' : 'asc';
                    }
                    $scope.admin_option.orderBy = orderBy;
                    $scope.loadUserAdmin();
                    $(".table-sort-client .sort-column").removeClass('active').removeClass('active-up').removeClass('active-down');
                    $(".table-sort-client .sort-column." + orderBy).addClass('active');
                    if ($scope.admin_option.orderDir == 'asc')
                        $(".table-sort-client .sort-column." + orderBy).addClass('active-up');
                    else $(".table-sort-client .sort-column." + orderBy).addClass('active-down');
                };

                $scope.addUserAdmin = function () {
                    $scope.itemAdmin = {mst_company_id: $scope.item.id, state_flg: 0, sendEmail: 0};
                    $("#modalDetailItemAdmin").modal();
                };

                $scope.editUserAdmin = function (id) {
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax_admin + "/" + id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.itemAdmin = event.data.info;
                                $scope.admin_readonlyEmailBtn = $scope.itemAdmin.state_flg != "1";
                            }
                            $("#modalDetailItemAdmin").modal();
                        });
                };

                <!-- ADD PAC_5-437 Start -->
                <!-- 管理者削除 -->
                $scope.deleteAdmin = function () {
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $scope.itemAdmin.state_flg = "-1";
                    var deleteSuccess = function (event) {
                        $rootScope.$emit("hideLoading");
                        if (event.data.status == false) {
                            $("#modalDeleteAdminConfirm").modal('hide');
                            $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'danger', 10000));
                        } else {
                            $("#modalDeleteAdminConfirm").modal('hide');
                            $scope.itemAdmin.id = event.data.id;
                            $scope.itemAdmin.sendEmail = false;
                            $scope.admin_readonlyEmailBtn = $scope.itemAdmin.state_flg != "1";
                            $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'success', 10000));

                            $("#modalDetailItemAdmin").modal('hide')
                            $scope.loadUserAdmin();
                            hasChange = true;
                        }
                    }
                    $http.delete(link_ajax_admin + "/" + $scope.itemAdmin.id, $scope.itemAdmin).then(deleteSuccess);

                };
                <!-- ADD PAC_5-437 End -->

                $scope.saveAdmin = function () {
                    if ($(".form_edit_admin")[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        var saveSuccess = function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.itemAdmin.id = event.data.id;
                                $scope.itemAdmin.sendEmail = false;
                                $scope.admin_readonlyEmailBtn = $scope.itemAdmin.state_flg != "1";
                                $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'success', 10000));

                                $("#modalDetailItemAdmin").modal('hide')
                                $scope.loadUserAdmin();
                                hasChange = true;
                            }
                        }

                        if ($scope.itemAdmin.id)
                            $http.put(link_ajax_admin + "/" + $scope.itemAdmin.id, $scope.itemAdmin).then(saveSuccess);
                        else $http.post(link_ajax_admin, $scope.itemAdmin).then(saveSuccess);
                    }
                };

                $scope.resetPasswordAdmin = function () {
                    $rootScope.$emit("showLoading");
                    $http.get(link_reset_password + "/" + $scope.itemAdmin.id)
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $("#modalDetailItemAdmin .message").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                };
                // $scope.showFormCode = function () {
                //     // CSRFトークン設定
                //     $.ajaxSetup({
                //         headers: {
                //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //         }
                //     });
                //     $.ajax({
                //         type: 'POST', // POST送信
                //         url: 'password/getPasswordCodeAdmin', //送信先URL
                //         dataType: 'json', //受け取る変数の形式
                //         data: {'code_admin_id': document.getElementById("code_admin_id").value},
                //         beforeSend: function () {
                //             $('.loading').removeClass('display-none'); //読み込みグルグル表示
                //         }
                //     }).done(function (code) {
                //         $("#code").text(code);
                //         $('.loading').addClass('display-none'); //読み込みグルグル削除
                //         console.log("ajax通信に成功しました");
                //     }).fail(function () {
                //         console.log("ajax通信に失敗しました");
                //     });
                //     // callback
                //     $("#modalPasswordCode").modal();
                // };

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
                }
                $scope.upload = function () {
                    $("#modalImport").modal();
                    $rootScope.$emit("showModalImport", {id: $scope.item.id});
                };

                $scope.storeCompanyCheck = function (){
                    if ($scope.item.scheduler_flg &&  $scope.item.scheduler_limit_flg == 0 && $scope.item.scheduler_buy_count == 0 ){
                        $("#modalDetailItem .message-info").append(showMessages(['スケジューラーの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.caldav_flg && $scope.item.caldav_limit_flg == 0 && $scope.item.caldav_buy_count == 0){
                        $("#modalDetailItem .message-info").append(showMessages(['CalDAVの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    /*PAC_5-2246 S*/
                    if ($scope.item.attendance_flg &&  $scope.item.attendance_limit_flg == 0 && $scope.item.attendance_buy_count == 0 ){
                        $("#modalDetailItem .message-info").append(showMessages(['タイムカードの購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    /*PAC_5-2246 E*/
                    if ($scope.item.faq_board_flg &&  $scope.item.faq_board_limit_flg == 0 && $scope.item.faq_board_buy_count == 0 ){
                        $("#modalDetailItem .message-info").append(showMessages(['サポート掲示板の購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.file_mail_flg && $scope.item.file_mail_limit_flg == 0 && $scope.item.file_mail_buy_count == 0){
                        $("#modalDetailItem .message-info").append(showMessages(['ファイルメール便の購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.address_list_flg &&  $scope.item.address_list_limit_flg == 0 && $scope.item.address_list_buy_count == 0 ){
                        $("#modalDetailItem .message-info").append(showMessages(['利用者名簿の購入数は必須です。'], 'danger', 10000));
                        return false;
                    }
                    if ($scope.item.contract_edition != 4 && $scope.item.upper_limit == 0){
                        $("#modalDetailItem .message-info").append(showMessages(['登録可能印面数には1以上の数値を指定してください。'], 'danger', 2000));
                        return false;
                    }
                    if ($scope.item.contract_edition == 4 && $scope.item.upper_limit != 0){
                        $("#modalDetailItem .message-info").append(showMessages(['登録可能印面数には0数値を指定してください。'], 'danger', 2000));
                        return false;
                    }
                    return true;
                };
                $scope.changeTemplateApprovalRouteFlg = function () {
                    if ($("#template_approval_route_flg").is(":checked")) {
                        $scope.item.template_route_flg = 1;
                    }
                };
                $scope.changeTemplateRouteFlg = function () {
                    if (!$("#template_route_flg").is(":checked")) {
                        $scope.item.template_approval_route_flg = 0;
                    }
                };

            })

            appPacAdmin.filter('startFrom', function () {
                return function (input, start) {
                    start = +start; //parse to int
                    return input.slice(start);
                }
            });

            document.getElementById('stamp-files-area').addEventListener('dragover', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
            }, false)

            document.getElementById('stamp-files-area').addEventListener('drop', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                angular.element(this).scope().stampDrop(ev.dataTransfer.files)
            }, false)

            document.getElementById('saml_metadata').addEventListener('keydown', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
            }, false)
        }

    </script>
@endpush
