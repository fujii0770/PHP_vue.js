<template>
    <div id="template-list-page">
        <vs-card style="margin-bottom: 0">
            <vs-row>
              <vs-col vs-type="flex" vs-lg="3" vs-sm="4" vs-xs="12" class="mb-3 pr-12">
                  <vs-row>
                    <vs-col class="mb-2" vs-type="flex" vs-w="12">
                      <label class="vs-input--label" for="file-name-search">明細テンプレート名</label>
                    </vs-col>
                    <vs-col vs-type="flex" vs-w="12">
                      <input id="file-name-search"
                             class="w-full input-search"
                             maxlength="256"
                             v-model="filter.file_name"/>
                    </vs-col>
                  </vs-row>
                </vs-col>
                <vs-col vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3">
                    <vs-row class="vs-input--label mb-2">明細種別</vs-row>
                    <vs-row class="pt-2 con-checkbox">
                        <vs-checkbox vs-value="other" v-model="filter.other">明細</vs-checkbox>
                        <vs-checkbox vs-value="invoice" v-model="filter.invoice">請求書</vs-checkbox>
                    </vs-row>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3">
                  <vs-row>
                    <vs-col class="mb-2" vs-type="flex" vs-w="12">
                      <label class="vs-input--label" for="template-code-search">明細テンプレートコード</label>
                    </vs-col>
                    <vs-col vs-type="flex" vs-w="12">
                      <input id="template-code-search"
                             class="w-full input-search"
                             maxlength="15"
                             v-model="filter.frm_template_code"/>
                    </vs-col>
                  </vs-row>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="1" class="mb-3"></vs-col>
                <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3">
                  <vs-row>
                    <vs-col class="mb-2" vs-type="flex" vs-w="12">
                      <label class="vs-input--label" for="remarks-search">備考</label>
                    </vs-col>
                    <vs-col vs-type="flex" vs-w="12">
                      <input id="remarks-search"
                             class="w-full input-search"
                             maxlength="100"
                             v-model="filter.remarks"/>
                    </vs-col>
                  </vs-row>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="2" vs-sm="8" vs-xs="12"
                        class="mb-3">
                    <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i>
                        検索
                    </vs-button>
                </vs-col>
            </vs-row>
        </vs-card>

        <vs-card>
          <vs-row class="mt-4" vs-type="flex" vs-align="flex-start" vs-justify="left">
            <div class="upload-wrapper md:w-10/12 mr-2 mb-2">
              <div class="vx-col w-full upload-box" id="dropZoneLarge" @drop="handleFileSelect"
                   @dragleave="handleDragLeave" @dragover="handleDragOver">
                <label class="wrapper" for="uploadFile">
                  <input type="file" ref="uploadFile"
                         accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                         id="uploadFile" v-on:change="onUploadFile"/>
                  <label style="text-align: center; white-space: nowrap;width: 80%;overflow: hidden;text-overflow: ellipsis;" class="text-content-center" for="uploadFile"><strong class="font-size-common">{{fileNames}}</strong></label>
                </label>
              </div>
            </div>
            <vs-button class="square mb-2" color="success" @click="onAdd" :disabled="confirmAdd">
              <i class="far fa-file-alt"></i> 新規登録
            </vs-button>
          </vs-row>
            <vs-table class="mt-3" noDataText="データがありません。" :data="formIssuanceList" @sort="handleSort" stripe sst>
                <template slot="thead">
                    <!-- <vs-th class="width-50"></vs-th> -->
                    <vs-th style="width: 5%;"></vs-th>
                    <vs-th style="width: 15%;" sort-key="frm_template_code">明細テンプレートコード</vs-th>
                    <vs-th style="width: 20%;" sort-key="file_name">明細テンプレート名</vs-th>
                    <vs-th style="width: 8%;" sort-key="frm_type">明細種別</vs-th>
                    <vs-th style="width: 7%;" sort-key="disabled_at">有効</vs-th>
                    <vs-th style="width: 20%;" sort-key="remarks">備考</vs-th>
                    <vs-th style="width: 10%;" sort-key="update_user">最終更新者</vs-th>
                    <vs-th style="width: 15%;" sort-key="update_at">最終更新日時</vs-th>
                </template>

                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                        <td v-if="tr.disabled_at == null" @click="createFrmTemplate(tr)" style="width: 5%;" >
                             <i class="fas fa-edit cursor-hover-pointer"></i>
                        </td>
                        <td v-else></td>
                        <td style="width: 15%;">{{tr.frm_template_code}}</td>
                        <td @click="showDetail(tr.id)" style="width: 20%;"><a href="javascript:void(0)" class="text-decoration-underline">
                          {{tr.file_name.length > 30 ? tr.file_name.substring(0, 30) + '...' : tr.file_name }}</a></td>
                        <td v-if="tr.frm_type == 1" style="width: 8%;">請求書</td>
                        <td v-else style="width: 8%;">明細</td>
                        <td v-if="tr.disabled_at == null" style="width: 7%;">✔</td>
                        <td v-else style="width: 7%;"></td>
                        <td v-if="tr.remarks != null" style="width: 20%;">
                          {{ tr.remarks.length > 30 ?
                          tr.remarks.substring(0, 30) + '...' :
                          tr.remarks }}</td>
                        <td v-else style="width: 20%;"></td>
                        <td style="width: 10%;">{{tr.update_user.length > 15 ? tr.update_user.substring(0, 15) + '...' : tr.update_user}}</td>
                        <td style="width: 15%;">{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                    </vs-tr>
                </template>
            </vs-table>
            <div>
                <div class="mt-3" v-if="pagination.totalItem > 0">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示
                </div>
                <div class="mt-3" v-else>0 件中 0 件から 0 件までを表示</div>
            </div>
            <vs-pagination v-if="pagination.totalItem" :total="pagination.totalPage" v-model="pagination.currentPage"></vs-pagination>
        </vs-card>

        <vs-popup classContent="popup-example" title="明細テンプレートファイルの削除" :active.sync="confirmDelete">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="12">この明細テンプレートを削除しますか。</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="明細テンプレート状態の更新" :active.sync="confirmChangeStatus">
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="12">{{item && item.disabled_at ? 'この明細テンプレートの状態を有効にします。':'この明細テンプレートの状態を無効にします。'}}</vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
              <vs-button @click="changeFrmTemplateStatus" color="success">更新</vs-button>
              <vs-button @click="confirmChangeStatus=false" color="dark" type="border">キャンセル</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="明細テンプレートの登録" :active.sync="noticeEmptyFileDialog">
            <div>※ファイルが選択されていません</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="noticeEmptyFileDialog=false" color="dark" type="border">OK</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="明細テンプレートの登録" :active.sync="detailAfterRegistration">
            <div v-if="newItemRegistered">
                <vs-row class="mb-5">
                    <vs-col vs-type="flex" vs-w="12">以下の明細テンプレートを登録しました。</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">明細テンプレート名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.file_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">明細種別</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.frm_type == 0 ? '明細' : '請求書' }}</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">テンプレートコード</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.frm_template_code }}</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">使用権限</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.frm_template_access_flg == 0 ? '社内' : (newItem.frm_template_access_flg == 1 ? '部署' : '登録者') }}</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">編集権限</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.frm_template_edit_flg == 0 ? '社内' : (newItem.frm_template_edit_flg == 1 ? '部署' : '登録者') }}</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">備考</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.remarks }}</vs-col>
                </vs-row>
                <vs-row class="mt-8">
                    <vs-col vs-type="flex" vs-w="4">登録日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.create_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                </vs-row>
                <vs-row class="mt-2">
                    <vs-col vs-type="flex" vs-w="4">登録者</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItemRegistered.create_user }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="12">※ 続けて明細テンプレートの項目設定、自動捺印設定する場合は設定ボタンを押してください。</vs-col>
                </vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="onSetting(newItemRegistered)" color="success">設定</vs-button>
                    <vs-button @click="detailAfterRegistration=false" color="dark" type="border">ok</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" class="show-detail" title="明細テンプレートの詳細" :active.sync="showItem">
            <div>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">明細テンプレート名</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4" class="text-decoration-underline font-color-link-primary long-name-restyle">
                      <a href="#" @click="downloadTemplate(item.id)">{{ item.file_name }}</a>
                    </vs-col>
                    <vs-col vs-w="2">
                        <vs-button :disabled="item.disabled_at || !check_setting_status" @click="createFrmTemplate(item)" class="align-r" color="dark" type="border">明細作成</vs-button>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      この明細テンプレートを使用した明細入力画面へ移動します。無効の場合は作成できません。
                    </vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">明細種別</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.frm_type == 0 ? '明細' : '請求書' }}</vs-col>
                    <vs-col vs-w="5"></vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">明細テンプレートコード</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.frm_template_code }}</vs-col>
                    <vs-col vs-w="2">
                        <vs-button :disabled="item.disabled_at || !check_setting_status" @click="importFrmTemplate(item)" class="align-r" color="dark" type="border">明細インポート</vs-button>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      複数の明細を一括で作成する画面へ移動します。
                      無効の場合は作成できません。
                    </vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">使用権限</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.frm_template_access_flg == 0 ? '社内' : (item.frm_template_access_flg == 1 ? '部署' : '登録者') }}</vs-col>
                    <vs-col vs-w="5"></vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">編集権限</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.frm_template_edit_flg == 0 ? '社内' : (item.frm_template_edit_flg == 1 ? '部署' : '登録者') }}</vs-col>
                    <vs-col v-if="check_edit_permission" vs-w="2">
                        <vs-button :disabled="item.disabled_at == null" @click="onSetting(item)" class="align-r" color="dark" type="border">設定</vs-button>
                    </vs-col>
                    <vs-col v-if="check_edit_permission" vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      明細テンプレートの項目設定、自動捺印設定、権限や備考の変更を行う画面へ移動します。
                      有効な明細テンプレートは設定の変更はできません。
                    </vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">備考</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4" class="text-content-in-block">{{ item.remarks }}</vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="5"></vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center" >
                    <vs-col class="w-20">有効／無効</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.disabled_at == null? '有効' : '無効' }}</vs-col>
                    <vs-col v-if="check_edit_permission" vs-w="2">
                        <vs-button :disabled="item.disabled_at == null || !check_setting_status" @click="confirmChangeStatus=true" class="align-r" color="dark" type="border">有効にする</vs-button>
                    </vs-col>
                    <vs-col v-if="check_edit_permission" vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      明細テンプレートを有効にします。
                      項目設定がされていない場合は有効にできません。
                    </vs-col>

                </vs-row>
                <vs-row class="mt-16" vs-align="center">
                    <vs-col class="w-20">登録日時</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.create_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                    <vs-col vs-w="5"></vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">登録者</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4" style="word-break: break-all;">{{ item.create_user }}</vs-col>
                    <vs-col v-if="check_edit_permission" vs-w="2">
                        <vs-button :disabled="item.disabled_at != null" @click="confirmChangeStatus=true" class="align-r" color="dark" type="border">無効にする</vs-button>
                    </vs-col>
                    <vs-col v-if="check_edit_permission" vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      明細テンプレートを無効にします。
                      その際、インポート中の処理は中断し、インポート予約はキャンセルされます。
                    </vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">更新日時</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="3">{{ item.update_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                    <vs-col vs-w="5"></vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">更新者</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4" style="word-break: break-all;">{{ item.update_user }}</vs-col>
                    <vs-col v-if="check_edit_permission" vs-w="2">
                        <vs-button :disabled="item.disabled_at == null" @click="confirmDelete=true" class="align-r" color="dark" type="border">削除</vs-button>
                    </vs-col>
                    <vs-col v-if="check_edit_permission" vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      明細テンプレートを削除します。
                      有効な明細テンプレートは削除できません。
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3"></vs-row>
                <div class="template-history-user">
                    <vs-row class="mt-2">
                        <vs-col vs-type="flex" vs-w="5"> インポート待ち／履歴 ({{ request_time}} 更新)</vs-col>
                        <vs-col vs-type="flex" vs-w="6">
                            <a href="#" class="text-decoration-underline" @click="getTemplateUseHistory">最新情報を取得</a>
                        </vs-col>
                    </vs-row>
                    <div>
                        最新１０件分のインポート状況を確認できます。
                    </div>
                    <div class="mt-3" v-if="listFormUseHistory.length == 0">データがありません。</div>
                    <vs-table v-if="listFormUseHistory.length > 0" class="mt-6" noDataText="" :data="listFormUseHistory" sst>
                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <td>{{tr.request_datetime}}</td>
                                <td>{{tr.imp_status == 0 ? '待機中':
                                      (tr.imp_status == 1) || (tr.imp_status == 2 ) ? '実行中':
                                      tr.imp_status == 5 ? '成功':
                                      tr.imp_status == -1 ? '取消':
                                      (tr.imp_status == -11) || (tr.imp_status == -12) ? '中断':
                                      (tr.imp_status == -21) || (tr.imp_status == -22) ? 'エラー':
                                      tr.imp_status == -99 ? '異常終了': ''
                                    }}</td>
                                <td>{{ tr.start_datetime === null ? '' : tr.start_datetime.substring(11, 19) }}</td>
                                <td>{{ tr.start_datetime === null ? '' : '-' }}</td>
                                <td>{{ tr.end_datetime === null ? '' : tr.end_datetime.substring(11, 19)  }}</td>
                                <td>{{tr.request_method == 0 ? '画面':
                                      tr.request_method == 1 ? 'API': ''
                                    }}</td>

                                <td class="width-150"
                                    v-if="tr.imp_status == 0|| tr.imp_status == 1 || tr.imp_status == 2">
                                  {{ tr.imp_filename == null ?
                                  '' : tr.imp_filename.length < 25 ? tr.imp_filename : tr.imp_filename.substring(0, 20) + '...'  }}
                                </td>
                                <td class="width-150 text-decoration-underline"
                                    v-else @click="downloadCSVImport(tr)">
                                  <a href="#">{{ tr.imp_filename == null ?
                                    '' : tr.imp_filename.length < 25 ? tr.imp_filename : tr.imp_filename.substring(0, 20) + '...'  }}</a>
                                </td>

                                <td class="text-center">{{ tr.registered_rows + '/' + tr.imp_rows+ '件'}}</td>
                                <td v-if="tr.imp_status != 0 && tr.imp_status != 1 && tr.imp_status != 2" @click="downloadTemplateLog(tr)" class="text-decoration-underline">
                                  <a href="#">ログ</a></td>
                                <td v-else></td>
                            </vs-tr>
                        </template>
                    </vs-table>


                </div>
            </div>
            <vs-row class="mt-3" vs-type="flex" vs-align="center" vs-justify="center">
                <vs-button @click="showItem=false" color="dark" type="border">閉じる</vs-button>
            </vs-row>
        </vs-popup>
    </div>
</template>


<script>
  import {mapState, mapActions} from "vuex";
  import InfiniteLoading from 'vue-infinite-loading';

  import flatPickr from 'vue-flatpickr-component'
  import 'flatpickr/dist/flatpickr.min.css';
  import {Japanese} from 'flatpickr/dist/l10n/ja.js';

  export default {
    components: {
      InfiniteLoading,
      flatPickr,
    },
    data() {
      return {
        filter: {
          file_name: "",
            invoice: "",
            other: "",
            frm_template_code: "",
            remarks: "",
        },
        formIssuanceList: [],
          item: {},
          showItem: false,
          check_edit_permission: false,
          check_setting_status: false,
          listFormUseHistory: [],
          request_time: '',
        pagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
        orderBy: "",
        orderDir: "",
        confirmDelete: false,
        confirmEnable: false,
        confirmChangeStatus: false,
        confirmAdd: false,
        noticeEmptyFileDialog: false,
        newItem: {
          file_name: '',
          create_at: new Date(),
          // template_create_at: new Date(),
          frm_template_edit_flg: 0,
          frm_template_access_flg: 0,
          frm_type_flg: 1,
          frm_template_code: '',
          remarks: ''
        },
        newItemRegistered:false,
        detailAfterRegistration: false,
        tempFiles: [],
        fileNameDownload: '',
        fileContentDownload: '',
        err_msg:''
      }
    },
    computed: {
      selected() {
        return this.formIssuanceList.filter(item => item.selected);
      },
      fileNames() {
        if(!this.tempFiles || !this.tempFiles.length) return 'ファイル選択';
        return this.tempFiles.map(item => item.name).join(', ');
      }
    },
    methods: {
      ...mapActions({
        search: "formIssuance/getFormIssuances",
        showFormIssuance: "formIssuance/showFormIssuance",
        setFiles: "formIssuance/setFiles",
        uploadFiles: "formIssuance/uploadTemplate",
        deleteFormIssuance: "formIssuance/deletes",
        templateUseHistory: "formIssuance/templateUseHistory",
        updateFormIssuanceStatus: "formIssuance/updateFormIssuanceStatus",
        downloadFile: "formIssuance/getFile",
        downloadFileCSVImport: "formIssuance/getFileCSVImport",
        downloadLogTemplateCSV: "formIssuance/getLogTemplateCSV",
          clearFrmTemplateState: "formIssuance/clearState",
      }),
      onSearch: async function (resetPaging) {
        let queries = {
          file_name: this.filter.file_name,
            invoice: this.filter.invoice,
            other: this.filter.other,
            frm_template_code: this.filter.frm_template_code,
            remarks: this.filter.remarks,
          page: resetPaging ? 1 : this.pagination.currentPage,
          limit: this.pagination.limit,
          orderBy: this.orderBy,
          orderDir: this.orderDir,
          action: resetPaging? 'search' : '',
        };
        const data = await this.search(queries);
        this.formIssuanceList = data.data.map(item => {
          item.selected = false;
          return item
        });
        this.pagination.totalItem = data.total;
        this.pagination.totalPage = data.last_page;
        this.pagination.currentPage = data.current_page;
        this.pagination.limit = data.per_page;
        this.pagination.from = data.from;
        this.pagination.to = data.to;
      },
      showDetail: async function(id) {
          const data = await this.showFormIssuance(id);
          const dataUseHistory = await this.templateUseHistory(id);
          if (data) {
              this.item = data.frm_template;
              this.check_edit_permission = data.check_edit_permission;
              this.check_setting_status = data.check_setting_status;
              this.showItem = true;
          }
          if (dataUseHistory) {
              this.request_time = dataUseHistory.request_time;
              this.listFormUseHistory = dataUseHistory.formUseHistory;
          }
      },
      createFrmTemplate: async function(item) {
        this.showItem = false;
        this.item = item;
        setTimeout(() => {
          this.setFiles([this.item]);
          this.$router.push('/form-issuance/create');
        }, 300);
      },
      importFrmTemplate: function(item) {
        this.showItem = false;
        this.item = item;
        setTimeout(() => {
          this.setFiles([this.item]);
          this.$router.push('/form-issuance/import');
        }, 300);
      },
      settingFrmTemplate: function() {
        this.showItem = false;
        this.$router.push('/form-issuance/setting/' + this.item.id);
      },
      changeFrmTemplateStatus: async function() {
        this.confirmChangeStatus = false;
        await this.updateFormIssuanceStatus({templateId: this.item.id, enable: this.item.disabled_at?true:false, action: this.item.disabled_at?'enableFormTemplate':'disableFormTemplate'});
        await this.onSearch(false);
        var element = this.formIssuanceList.find(element => element.id == this.item.id);
        if (element){
          this.item = element;
        }else{
          this.showItem = false;
        }
      },
      async getTemplateUseHistory() {
        if (Object.prototype.hasOwnProperty.call(this.item, "id")) {
            const dataUseHistory = await this.templateUseHistory(this.item.id)
            if (dataUseHistory) {
                this.request_time = dataUseHistory.request_time;
                this.listFormUseHistory = dataUseHistory.formUseHistory;
            }
        }
      },
      handleSort(key, active) {
        this.orderBy = key;
        this.orderDir = active ? "DESC" : "ASC";
        this.onSearch(false);
      },
      onDelete: async function () {
        await this.deleteFormIssuance({templateId: this.item.id});
        this.confirmDelete = false;
        this.showItem = false;
        await this.onSearch(false);
      },
      onAdd: function() {
        this.confirmAdd = true;
        if(!this.tempFiles.length) {
          this.noticeEmptyFileDialog = true;
          this.confirmAdd = false;
        }else {
          this.newItem = {
            file_name: this.fileNames,
            create_at: new Date(),
            frm_template_edit_flg: 0,
            frm_template_access_flg: 0,
            frm_type_flg: 0,
            frm_template_code: '',
            remarks: ''
          };
          this.onConfirmAdd();
        }
      },
      onConfirmAdd: async function() {
        this.$store.dispatch('updateLoading', true);
        this.err_msg ='';
        // if(!this.tempFiles || !this.tempFiles.length) return;
        const file = this.tempFiles[0];
        const data = {
          file: file,
          frm_template_edit_flg: 0,
          frm_template_access_flg: 0,
          frm_type_flg: 0,
          remarks: '',
          frm_template_code: ''
        }

        this.newItemRegistered = await this.uploadFiles(data);
        // 正常の場合
        if(this.newItemRegistered.id){
          this.onSetting(this.newItemRegistered);
        //エラーの場合
        }else{
          this.err_msg =this.newItemRegistered.err_msg;
          this.confirmAdd = false;
        }
      },
      handleFileSelect: async function (evt) {
        const dropZone = document.getElementById('dropZone');
        dropZone.style.borderColor = '#D1ECFF';
        evt.stopPropagation();
        evt.preventDefault();

        const files = Array.from(evt.dataTransfer.files);
        if(files && files.length) this.tempFiles = [files.shift()];
        //const ret = await this.uploadFiles(files);
      },
      handleDragLeave: function (evt) {
        const dropZone = document.getElementById('dropZone');
        dropZone.style.borderColor = '#D1ECFF';
        evt.stopPropagation();
        evt.preventDefault();
      },
      handleDragOver: function (evt) {
        const dropZone = document.getElementById('dropZone');
        dropZone.style.borderColor = '#55efc4';
        evt.stopPropagation();
        evt.preventDefault();
        evt.dataTransfer.dropEffect = 'copy';
      },
      onUploadFile: async function(e) {
        const files = Array.from(e.target.files);
        this.tempFiles = files;
        //const ret = await this.uploadFiles(files);
      },
      onSetting: function (frmTemplate) {
        this.showItem = false;
        setTimeout(() => {
            this.clearFrmTemplateState();
          this.setFiles([frmTemplate]);
          this.$router.push('/form-issuance/setting/'+frmTemplate.id);
        }, 300);
      },
      async downloadTemplate(templateId) {
        let result = await this.downloadFile(templateId);
        if (result) {
            const content = result.file_data;
            let fileName = result.file_name;
            let fileType = result.file_type;
            let typeDownload = '';
            if (fileType == 'xlsx') {
                typeDownload = 'application/vnd.ms-excel';
            } else {
                typeDownload = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            }
            // Decode base64 using atob method
            let data = window.atob(content);
            let bytes = new Array(data.length);
            for (let i = 0; i < data.length; i++) {
                bytes[i] = data.charCodeAt(i);
            }
            data = new Uint8Array(bytes);
            let anchor = document.createElement('a');
            if (anchor.download !== undefined) {
              let blob = new Blob([data], {type: typeDownload});
              var objectURL = window.URL.createObjectURL(blob);

              anchor.href = objectURL;
              anchor.download = fileName;
            }
            if (navigator.msSaveBlob) { // IE 10+
              anchor.addEventListener("click", function (event) {
                let blob = new Blob([data], {
                  "type": 'text'
                });
                navigator.msSaveBlob(blob, fileName);
              }, false);
            }

            anchor.click();

            URL.revokeObjectURL(objectURL);
        }
      },
      async downloadCSVImport(fileImport) {
        if (fileImport.imp_status != 0 && fileImport.imp_status != 1) {
          let params = {
            templateId: fileImport.frm_template_id,
            csvId: fileImport.id
          };
          const result = await this.downloadFileCSVImport(params);
          if (result) {
            let fileName = result.file_name;
            let content = result.file_data;
            // Decode base64 using atob method
            let data = window.atob(content);
            let bytes = new Array(data.length);
            for (let i = 0; i < data.length; i++) {
              bytes[i] = data.charCodeAt(i);
            }
            data = new Uint8Array(bytes);
            let hiddenElement = document.createElement('a');
            if (hiddenElement.download !== undefined) {
              let blob = new Blob([data], { type: 'text/csv' });
              var url = URL.createObjectURL(blob);
              hiddenElement.href = url;
              hiddenElement.download =  fileName;
            }
            if (navigator.msSaveBlob) { // IE 10+
              hiddenElement.addEventListener("click", function (event) {
                let blob = new Blob([data], {
                  "type": 'text/csv'
                });
                navigator.msSaveBlob(blob, fileName);
              }, false);
            }
            hiddenElement.click();
            URL.revokeObjectURL(url);
          }

        }

      },
      async downloadTemplateLog(fileLog) {
        if (fileLog.imp_status != 0 && fileLog.imp_status != 1) {
          let params = {
            templateId: fileLog.frm_template_id,
            logId: fileLog.id,
            action: 'formIssuanceDownloadLog',
          };
          const result = await this.downloadLogTemplateCSV(params);
          if (result) {
            let fileName = result.file_name;
            let content = result.file_data;
            // Decode base64 using atob method
            let data = window.atob(content);
            let bytes = new Array(data.length);
            for (let i = 0; i < data.length; i++) {
              bytes[i] = data.charCodeAt(i);
            }
            data = new Uint8Array(bytes);

            let hiddenElement = document.createElement('a');
            if (hiddenElement.download !== undefined) {
              let blob = new Blob([data], { type: 'text' });
              var url = URL.createObjectURL(blob);
              hiddenElement.href = url;
              hiddenElement.download =  fileName;
            }
            if (navigator.msSaveBlob) { // IE 10+
              hiddenElement.addEventListener("click", function (event) {
                let blob = new Blob([data], {
                  "type": 'text'
                });
                navigator.msSaveBlob(blob, fileName);
              }, false);
            }
            hiddenElement.click();
            URL.revokeObjectURL(url);
          }

        }
      }
    },
    watch: {
      'pagination.currentPage': function (val) {
        this.onSearch(false);
      }
    },
    mounted() {
      this.onSearch(false);
    }
  }

</script>

<style lang="scss">
.show-detail .vs-popup {
    width: 1000px !important;
}
.show-detail .align-r {
    width: 157px;
}
.text-decoration-underline {
    text-decoration: underline;
}
.font-color-link-primary {
    color: rgba(var(--vs-primary), 1)
}
.long-name-restyle {
    padding-right: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.font-size-information {
  font-size: 13px;
}
.padding-block-information {
  padding-left: 0.5em;
}
.vs-button:not(.vs-radius):not(.includeIconOnly):not(.small):not(.large).vs-button-border {
  padding: 0.679rem 1rem;
}
.input-search {
  height: 35px;
  border-radius: 5px;
  font-size: 16px;
  border: 2px solid rgb(180, 180, 180);
}
.w-20 {
  width: 20% !important;
}
.w-5 {
  width: 5% !important;
}
.vs-table--tbody-table .tr-values.selected {
  cursor: default;
}
.cursor-hover-pointer :hover {
  cursor: pointer !important;
}
.text-content-center {
 margin: auto;
}
.font-size-common {
  font-size: 14px;
}
.text-content-in-block {
  word-wrap: break-word;
  white-space: pre-wrap;
}
</style>
