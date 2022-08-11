
 <div class="card">
    <div class="card-header">設定項目</div>
    <div class="card-body">
        <div class="form-group">
            <div class="row">
                <label for="department_stamp_flg" class="col-md-3 col-sm-3 col-12 text-right">部署名入り日付印</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.department_stamp_flg" id="department_stamp_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="department_stamp_flg">有効にする</label>
                </div>
                <label for="template_route_flg" class="col-md-3 col-sm-3 col-12 text-right">承認ルート</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.template_route_flg" id="template_route_flg" ng-true-value="1" ng-false-value="0" ng-click="changeTemplateRouteFlg()"/>
                    <label for="template_route_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="rotate_angle_flg" class="col-md-3 col-sm-3 col-12 text-right pl-0">おじぎ印</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.rotate_angle_flg" id="rotate_angle_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="rotate_angle_flg">有効にする</label>
                </div>
                <label for="phone_app_flg" class="col-md-3 col-sm-3 col-12 text-right">携帯アプリ</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.phone_app_flg" id="phone_app_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="phone_app_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="attachment_flg" class="col-md-3 col-sm-3 col-12 text-right">添付ファイル機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.attachment_flg" id="attachment_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="attachment_flg">有効にする</label>
                </div>
                <label for="portal_flg" class="col-md-3 col-sm-3 col-12 text-right">ポータル機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.portal_flg" id="portal_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="portal_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="convenient_flg" class="col-md-3 col-sm-3 col-12 text-right">便利印</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.convenient_flg" id="convenient_flg" ng-true-value="1" ng-false-value="0" ng-click="changeConvenientFlg()"/>
                    <label for="convenient_flg">有効にする</label>
                </div>
                <label for="usage_flg" class="col-md-3 col-sm-3 col-12 text-right">利用状況</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.usage_flg" id="usage_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="usage_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="convenient_upper_limit" class="col-md-3 col-sm-3 col-12 text-right">便利印契約数</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="number" class="form-control" ng-model="item.convenient_upper_limit" id="convenient_upper_limit" placeholder="0" min="0" ng-disabled="item.convenient_flg==0"/>
                </div>
                <label for="sticky_note_flg" class="col-md-3 col-sm-3 col-12 text-right">付箋機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.sticky_note_flg" id="sticky_note_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="sticky_note_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="default_stamp_flg" class="col-md-3 col-sm-3 col-12 text-right">デフォルト印</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.default_stamp_flg" id="default_stamp_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="default_stamp_flg">有効にする</label>
                </div>
                <label for="confidential_flg" class="col-md-3 col-sm-3 col-12 text-right">社外秘</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.confidential_flg" id="confidential_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="confidential_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="esigned_flg" class="col-md-3 col-sm-3 col-12 text-right">PDFへの電子署名付加</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.esigned_flg" id="esigned_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="esigned_flg">有効にする</label>
                </div>
                <label for="ip_restriction_flg" class="col-md-3 col-sm-3 col-12 text-right">接続IP制限</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.ip_restriction_flg" id="ip_restriction_flg" ng-true-value="1" ng-change="ipFlgCheck()" ng-false-value="0"/>
                    <label for="ip_restriction_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="signature_flg" class="col-md-3 col-sm-3 col-12 text-right">電子証明書設定</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.signature_flg" id="signature_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="signature_flg">有効にする</label>
                </div>
                <label for="permit_unregistered_ip_flg" class="col-md-3 col-sm-3 col-12 text-right">登録外IPのログイン許可</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.permit_unregistered_ip_flg" id="permit_unregistered_ip_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.ip_restriction_flg==0"/>
                    <label for="permit_unregistered_ip_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="stamp_flg" class="col-md-3 col-sm-3 col-12 text-right">タイムスタンプ付署名</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.stamp_flg" id="stamp_flg" ng-true-value="1" ng-false-value="0" ng-click="changeStampFlg()"/>
                    <label for="stamp_flg">有効</label>
                </div>
                <label for="repage_preview_flg" class="col-md-3 col-sm-3 col-12 text-right pl-0">改ページ調整プレビュー</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.repage_preview_flg" id="repage_preview_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="repage_preview_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="timestamps_count" class="col-md-3 col-sm-3 col-12 text-right">タイムスタンプ契約（回）</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="number" class="form-control" ng-model="item.timestamps_count" id="timestamps_count" placeholder="0" min="0" ng-disabled="item.stamp_flg==0"/>
                </div>
                <label for="box_enabled" class="col-md-3 col-sm-3 col-12 text-right">外部連携</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.box_enabled" id="box_enabled" ng-true-value="1" ng-false-value="0"/>
                    <label for="box_enabled">Box</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="time_stamp_issuing_count" class="col-md-3 col-sm-3 col-12 text-right">タイムスタンプ発行を自社でカウント</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.time_stamp_issuing_count" id="time_stamp_issuing_count" ng-true-value="1" ng-false-value="0" ng-disabled="item.stamp_flg==0"/>
                    <label for="time_stamp_issuing_count">有効にする</label>
                </div>
                <label for="mfa_flg" class="col-md-3 col-sm-3 col-12 text-right">多要素認証</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.mfa_flg" id="mfa_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="mfa_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="long_term_storage_flg" class="col-md-3 col-sm-3 col-12 text-right">文書長期保管</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.long_term_storage_flg" id="long_term_storage_flg" ng-true-value="1" ng-false-value="0" ng-click="changeLongTermStorageFlg()"/>
                    <label for="long_term_storage_flg">有効にする</label>
                </div>
                <label for="template_flg" class="col-md-3 col-sm-3 col-12 text-right">テンプレート機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.template_flg" id="template_flg" ng-true-value="1" ng-false-value="0" ng-click="changeTemplateFlg()"/>
                    <label for="template_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="long_term_storage_option_flg" class="col-md-3 col-sm-3 col-12 text-right">長期保管文書検索</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.long_term_storage_option_flg" id="long_term_storage_option_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.long_term_storage_flg==0"/>
                    <label for="long_term_storage_option_flg">有効にする</label>
                </div>
                <label for="template_search_flg" class="col-md-3 col-sm-3 col-12 text-right">テンプレート検索機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.template_search_flg" id="template_search_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.template_flg==0"/>
                    <label for="template_search_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="long_term_folder_flg" class="col-md-3 col-sm-3 col-12 text-right">長期保管フォルダ管理</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.long_term_folder_flg" id="long_term_folder_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.long_term_storage_flg==0"/>
                    <label for="long_term_folder_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="max_usable_capacity" class="col-md-3 col-sm-3 col-12 text-right">長期保管使用容量(GB)</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="number" class="form-control" ng-model="item.max_usable_capacity" id="max_usable_capacity" ng-disabled="item.long_term_storage_flg==0" placeholder="0" min="0"/>
                </div>
                <label for="template_csv_flg" class="col-md-3 col-sm-3 col-12 text-right">テンプレートcsv出力機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.template_csv_flg" id="template_csv_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.template_flg==0"/>
                    <label for="template_csv_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="hr_flg" class="col-md-3 col-sm-3 col-12 text-right">HR機能の使用許可</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.hr_flg" id="hr_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="hr_flg">有効にする</label>
                </div>
                <label for="template_edit_flg" class="col-md-3 col-sm-3 col-12 text-right">テンプレート編集機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.template_edit_flg" id="template_edit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.template_flg==0"/>
                    <label for="template_edit_flg">有効にする</label>
                </div>
                <label for="multiple_department_position_flg" class="col-md-3 col-sm-3 col-12 text-right">部署・役職複数登録</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.multiple_department_position_flg" id="multiple_department_position_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="multiple_department_position_flg">有効にする</label>
                </div>
                <label for="template_approval_route_flg" class="col-md-3 col-sm-3 col-12 text-right">テンプレート承認ルート</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.template_approval_route_flg" id="template_approval_route_flg" ng-true-value="1" ng-false-value="0" ng-disabled="item.template_flg==0" ng-click="changeTemplateApprovalRouteFlg()"/>
                    <label for="template_approval_route_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="option_user_flg" class="col-md-3 col-sm-3 col-12 text-right">グループウェア専用利用者</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.option_user_flg" id="option_user_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="option_user_flg">有効にする</label>
                </div>
                <label for="user_plan_flg" class="col-md-3 col-sm-3 col-12 text-right">合議機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.user_plan_flg" id="user_plan_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="user_plan_flg">有効にする</label>
                </div>
            </div>

        </div>
        <div class="form-group">
            <div class="row">
                <label for="receive_user_flg" class="col-md-3 col-sm-3 col-12 text-right">受信専用利用者</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.receive_user_flg" id="receive_user_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="receive_user_flg">有効にする</label>
                </div>

                <label for="skip_flg" class="col-md-3 col-sm-3 col-12 text-right">スキップ機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.skip_flg" id="skip_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="skip_flg">有効にする</label>
                </div>

            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="form_user_flg" class="col-md-3 col-sm-3 col-12 text-right">帳票専用利用企業</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.form_user_flg" id="form_user_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="form_user_flg">有効にする</label>
                </div>
                <label for="frm_srv_flg" class="col-md-3 col-sm-3 col-12 text-right">ササッと明細の使用許可</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.frm_srv_flg" id="frm_srv_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="frm_srv_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="expense_flg">
            <div class="row">
                <label for="expense_flg" class="col-md-3 col-sm-3 col-12 text-right">経費精算機能の使用許可</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.expense_flg" id="expense_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="expense_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="bizcard_flg" class="col-md-3 col-sm-3 col-12 text-right">名刺機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.bizcard_flg" id="bizcard_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="bizcard_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="local_stamp_flg" class="col-md-3 col-sm-3 col-12 text-right">Office捺印</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.local_stamp_flg" id="local_stamp_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="local_stamp_flg">有効にする</label>
                </div>

                <label for="with_box_flg" class="col-md-3 col-sm-3 col-12 text-right">box捺印</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.with_box_flg" id="with_box_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="with_box_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="dispatch_flg" class="col-md-3 col-sm-3 col-12 text-right">派遣機能</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.dispatch_flg" id="dispatch_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="dispatch_flg">有効にする</label>
                </div>

                <label for="with_box_flg" class="col-md-3 col-sm-3 col-12 text-right">勤怠システム</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.attendance_system_flg" id="attendance_system_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="attendance_system_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="circular_list_csv" class="col-md-3 col-sm-3 col-12 text-right">回覧一覧CSV出力</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.circular_list_csv" id="circular_list_csv" ng-true-value="1" ng-false-value="0" />
                    <label for="circular_list_csv">有効にする</label>
                </div>
                <label for="without_email_flg" class="col-md-3 col-sm-3 col-12 text-right">メールアドレス無し</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.without_email_flg" id="without_email_flg" ng-true-value="1" ng-false-value="0"/>
                    <label for="without_email_flg">有効にする</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="is_together_send" class="col-md-3 col-sm-3 col-12 text-right">一斉送信</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.is_together_send" id="is_together_send" ng-true-value="1" ng-false-value="0" />
                    <label for="is_together_send">有効にする</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="enable_any_address_flg" class="col-md-3 col-sm-3 col-12 text-right">承認ルートのみに制限</label>
                <div class="col-md-3 col-sm-3 col-12">
                    <input type="checkbox" ng-model="item.enable_any_address_flg" id="enable_any_address_flg" ng-true-value="1" ng-false-value="0" />
                    <label for="enable_any_address_flg">有効にする</label>
                </div>
            </div>
        </div>
    </div>
 </div>
 <div class="card">
     <div class="card-header">その他設定項目
         <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickOtherSettings()"
            href="#"><i id="other_settings" class="fas fa-angle-down"></i></a>
     </div>
     <div class="card-body" ng-show="show_other_settings">
         <div class="form-group">
             <div class="row">
                 <label for="enable_email" class="col-md-3 col-sm-3 col-12 text-right">メール(企業)</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.enable_email" id="enable_email" ng-true-value="1" ng-false-value="0"/>
                     <label for="enable_email">有効にする</label>
                 </div>
                 </div>
             </div>
         <div class="form-group">
             <div class="row">
                 <label for="email_format" class="col-md-3 col-sm-3 col-12 text-right">メールフォーマット</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <div>
                         <input type="radio" ng-model="item.email_format" id="email_format-1" ng-value="1"/>
                         <label for="email_format-1">HTML</label>
                     </div>
                     <div>
                         <input type="radio" ng-model="item.email_format" id="email_format-0" ng-value="0"/>
                         <label for="email_format-0">テキスト</label>
                     </div>
                 </div>
                 <label for="received_only_flg" class="col-md-3 col-sm-3 col-12 text-right pl-0">受信のみ</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.received_only_flg" id="received_only_flg" ng-true-value="1" ng-false-value="0"/>
                     <label for="received_only_flg">有効にする</label>
                 </div>
             </div>
         </div>

         <div class="form-group">
             <div class="row">
                 <label for="pdf_annotation_flg" class="col-md-3 col-sm-3 col-12 text-right">捺印情報表示(PDF)</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.pdf_annotation_flg" id="pdf_annotation_flg" ng-true-value="1" ng-false-value="0"/>
                     <label for="pdf_annotation_flg">有効にする</label>
                 </div>
                 <label for="addressbook_only_flag" class="col-md-3 col-sm-3 col-12 text-right">送信先の制限</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.addressbook_only_flag" id="addressbook_only_flag" ng-true-value="1" ng-false-value="0"/>
                     <label for="addressbook_only_flag">有効にする</label>
                 </div>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="view_notification_email_flg" class="col-md-3 col-sm-3 col-12 text-right">閲覧通知メール設定</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.view_notification_email_flg" id="view_notification_email_flg" ng-true-value="1" ng-false-value="0"/>
                     <label for="view_notification_email_flg">有効にする</label>
                 </div>
                 <label for="guest_company_flg" class="col-md-3 col-sm-3 col-12 text-right pl-0">ゲスト企業（富士通専用）</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.guest_company_flg" id="guest_company_flg" ng-true-value="1" ng-false-value="0" ng-disabled='item.id && item.guest_company_flg == 0'/>
                     <label for="guest_company_flg">有効にする</label>
                 </div>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="updated_notification_email_flg" class="col-md-3 col-sm-3 col-12 text-right">更新通知メール設定</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.updated_notification_email_flg" id="updated_notification_email_flg" ng-true-value="1" ng-false-value="0"/>
                     <label for="updated_notification_email_flg">有効にする</label>
                 </div>

                 <label for="is_show_current_company_stamp" class="col-md-3 col-sm-3 col-12 text-right">自社のみの回覧履歴を付ける</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.is_show_current_company_stamp" id="is_show_current_company_stamp" ng-true-value="1" ng-false-value="0"/>
                     <label for="is_show_current_company_stamp">表示する</label>
                 </div>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="enable_email_thumbnail" class="col-md-3 col-sm-3 col-12 text-right">メール内の文書のサムネイル表示</label>
                 <div class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.enable_email_thumbnail" id="enable_email_thumbnail" ng-true-value="1" ng-false-value="0"/>
                     <label for="enable_email_thumbnail">有効にする</label>
                 </div>
                 <label ng-if="{{!config('app.pac_app_env')?1:0}}" for="receive_plan_flg" class="col-md-3 col-sm-3 col-12 text-right">受信専用プラン</label>
                 <div ng-if="{{!config('app.pac_app_env')?1:0}}" class="col-md-3 col-sm-3 col-12">
                     <input type="checkbox" ng-model="item.receive_plan_flg" id="receive_plan_flg" ng-true-value="1" ng-false-value="0"/>
                     <label for="receive_plan_flg">有効にする</label>
                 </div>
             </div>
         </div>
     </div>

 </div>
 <div class="card mt-3">
     <div class="card-header">グループウェア機能設定
         <a class="dropdown-item" style="display: inline-block;width: 0px;float:right;background:rgba(0,0,0,0) !important;color:#000 !important;" ng-click="clickGWSettings()"
            href="#"><i id="GW_settings" class="fas fa-angle-down"></i></a>
     </div>
     <div class="card-body" ng-show="show_gw_settings">
         <div class="form-group">
             <div class="row">
                 <label for="board_flg" class="col-md-3 col-sm-3 col-12 text-left">掲示板</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.board_flg" id="board_flg" ng-true-value="1" ng-false-value="0" ng-change="boardFlgCheck()"/>
                     <label for="board_flg">有効にする</label>
                 </div>
             </div>
         </div>

         <div class="form-group">
             <div class="row">
                 <label for="board_flg" class="col-md-3 col-sm-3 col-12 text-left">サポート掲示板</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.faq_board_flg" id="faq_board_flg" ng-true-value="1" ng-false-value="0" ng-change="faqBoardFlgCheck()"/>
                     <label for="faq_board_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.faq_board_limit_flg" id="faq_board_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.faq_board_flg" ng-change="item.faq_board_limit_flg!=0?item.faq_board_buy_count=0:''"/>
                     <label for="faq_board_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.faq_board_buy_count" id="faq_board_buy_count"  placeholder="0" ng-readonly="readonly"  ng-disabled="!item.faq_board_flg || item.faq_board_limit_flg "/>
                 </div>
                 <label for="faq_board_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
         </div>

         <div class="form-group">
             <div class="row">
                 <label for="scheduler_flg" class="col-md-3 col-sm-3 col-12 text-left">スケジューラー</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.scheduler_flg" id="scheduler_flg" ng-true-value="1" ng-false-value="0" ng-change="gwFlgCheck()"/>
                     <label for="scheduler_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.scheduler_limit_flg" id="scheduler_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.scheduler_flg" ng-click="schedulerLimitFlgCheck()" ng-change="item.scheduler_limit_flg!=0?item.scheduler_buy_count=0:''"/>
                     <label for="scheduler_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.scheduler_buy_count" id="scheduler_buy_count"  placeholder="0" ng-readonly="readonly"  ng-disabled="!item.scheduler_flg || item.scheduler_limit_flg "/>
                 </div>
                 <label for="scheduler_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="shared_scheduler_flg" class="col-md-3 col-sm-3 col-12 text-left">グループスケジューラ</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.shared_scheduler_flg" id="shared_scheduler_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.scheduler_flg" ng-change="sharedSchedulerFlgCheck()"/>
                     <label for="shared_scheduler_flg">有効にする</label>
                 </div>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="caldav_flg" class="col-md-3 col-sm-3 col-12 text-left">カレンダー連携</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.caldav_flg" id="caldav_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.scheduler_flg" ng-change="caldavFlgCheck()"/>
                     <label for="caldav_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.caldav_limit_flg" id="caldav_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.scheduler_flg || !item.caldav_flg" ng-click="caldavLimitFlgCheck()" ng-change="item.caldav_limit_flg!=0?item.caldav_buy_count=0:''"/>
                     <label for="caldav_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.caldav_buy_count" id="caldav_buy_count"  placeholder="0" ng-readonly="readonly" ng-disabled="!item.scheduler_flg || !item.caldav_flg || item.caldav_limit_flg"/>
                 </div>
                 <label for="caldav_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <div class="col-md-3 col-sm-3 col-12"></div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.google_flg" id="google_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.caldav_flg"/>
                     <label for="google_flg">Google連携</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8" style="padding-right: 0px">
                     <input type="checkbox" ng-model="item.outlook_flg" id="outlook_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.caldav_flg"/>
                     <label for="outlook_flg">Outlook連携</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.apple_flg" id="apple_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.caldav_flg"/>
                     <label for="apple_flg">Apple連携</label>
                 </div>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="file_mail_flg" class="col-md-3 col-sm-3 col-12 text-left">ファイルメール便</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.file_mail_flg" id="file_mail_flg" ng-true-value="1" ng-false-value="0" ng-change="fileMailFlgCheck()"/>
                     <label for="file_mail_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.file_mail_limit_flg" id="file_mail_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.file_mail_flg" ng-change="item.file_mail_limit_flg!=0?item.file_mail_buy_count=0:''"/>
                     <label for="file_mail_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.file_mail_buy_count" id="file_mail_buy_count"  placeholder="0" ng-readonly="readonly"  ng-disabled="!item.file_mail_flg || item.file_mail_limit_flg "/>
                 </div>
                 <label for="file_mail_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
             <div class="row">
                 <label for="file_mail_flg" class="col-md-3 col-sm-3 col-12 text-left"></label>
                 <div class="col-md-4 col-sm-4 col-8">
                     <input type="checkbox" ng-model="item.file_mail_extend_flg" id="file_mail_extend_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.file_mail_flg" />
                     <label for="file_mail_extend_flg">送信履歴保持延長</label>
                 </div>
             </div>
         </div>

         <div class="form-group">
             <div class="row">
                 <label for="attendance_flg" class="col-md-3 col-sm-3 col-12 text-left">タイムカード</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.attendance_flg" id="attendance_flg" ng-true-value="1" ng-false-value="0"  ng-change="attendanceFlgCheck()"/>
                     <label for="attendance_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.attendance_limit_flg" id="attendance_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.attendance_flg"  ng-change="item.attendance_limit_flg!=0?item.attendance_buy_count=0:''"/>
                     <label for="attendance_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.attendance_buy_count" id="attendance_buy_count"  placeholder="0" ng-readonly="readonly" ng-disabled="!item.attendance_flg || item.attendance_limit_flg"/>
                 </div>
                 <label for="attendance_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
         </div>
         <div class="form-group">
             <div class="row">
                 <label for="to_do_list_flg" class="col-md-3 col-sm-3 col-12 text-left">ToDoリスト</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.to_do_list_flg" id="to_do_list_flg" ng-true-value="1" ng-false-value="0"  ng-change="toDoListFlgCheck()"/>
                     <label for="to_do_list_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.to_do_list_limit_flg" id="to_do_list_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.to_do_list_flg"  ng-change="item.to_do_list_limit_flg!=0?item.to_do_list_buy_count=0:''"/>
                     <label for="to_do_list_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.to_do_list_buy_count" id="to_do_list_buy_count"  placeholder="0" ng-readonly="readonly" ng-disabled="!item.to_do_list_flg || item.to_do_list_limit_flg"/>
                 </div>
                 <label for="to_do_list_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
         </div>

         <div class="form-group">
             <div class="row">
                 <label for="address_list_flg" class="col-md-3 col-sm-3 col-12 text-left">利用者名簿</label>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.address_list_flg" id="address_list_flg" ng-true-value="1" ng-false-value="0"  ng-change="addressListFlgCheck()"/>
                     <label for="address_list_flg">有効にする</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="checkbox" ng-model="item.address_list_limit_flg" id="address_list_limit_flg" ng-true-value="1" ng-false-value="0" ng-disabled="!item.address_list_flg"  ng-change="item.address_list_limit_flg!=0?item.address_list_buy_count=0:''"/>
                     <label for="address_list_limit_flg">無制限</label>
                 </div>
                 <div class="col-md-2 col-sm-2 col-8">
                     <input type="number" class="form-control" ng-model="item.address_list_buy_count" id="address_list_buy_count"  placeholder="0" ng-readonly="readonly" ng-disabled="!item.address_list_flg || item.address_list_limit_flg"/>
                 </div>
                 <label for="address_list_buy_count" class="col-md-2 col-sm-2 col-8 text-left">購入数</label>
             </div>
         </div>
     </div>
 </div>
