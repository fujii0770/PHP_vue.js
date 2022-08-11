<template>
  <div id="portal-file-mail-application">
      <top-menu></top-menu>
    <div style="display: flex;">
<!--      <div style="background-color: white; margin-left: -8px;" class="mr-2">-->
<!--        <menu-left></menu-left>-->
<!--      </div>-->

      <div class="pr-3" style="flex-grow: 1">
        <div>
          <div style="margin-bottom: 15px">
            <vs-row>
              <vs-col vs-type="flex" vs-lg="6" vs-xs="12">
              </vs-col>
              <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="6" vs-xs="12">
                <vs-button class="square" color="primary" @click="fileSendList()"> ファイル送信一覧</vs-button>
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#fff;" color="#22AD38" type="filled"
                           v-on:click="fileSendConfirm"> 送信
                </vs-button>
              </vs-col>
            </vs-row>
          </div>
          <!--  宛先  -->
          <div class="vx-row">
            <div class="vx-col w-full mb-base lg:pr-0">
              <vx-card :hideLoading="true">
                <div slot="no-body">
                </div>
                <vs-row class="border-bottom pb-4">
                  <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12">
                    <h4>宛先<span class="text-danger"></span></h4>
                  </vs-col>
                  <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="6" vs-xs="12">
                    <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12" style="padding-left: 40%">
                              <span @click="onDepartmentUsersSelect()" style="cursor:pointer;">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                       stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open " style="color: #000000;">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                  </svg>
                              </span>
                    </vs-col>
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto ipad_size" color="danger" type="filled" v-on:click="clearMailUsers">全て削除</vs-button>
                  </vs-col>
                </vs-row>
                <!--  アドレス帳  -->
                <vs-popup class="application-page-dialog" title="" :active.sync="confirmEdit">
                  <div class="vx-col w-full  mb-4">
                    <vx-card class="h-full">
                      <vs-row>
                        <vs-col vs-w="12">
                          <vs-tabs>
                            <vs-tab @click="showTree = true" label="アドレス帳">

                            </vs-tab>
                          </vs-tabs>
                        </vs-col>
                        <vs-col vs-w="12">
                          <ContactTree :opened="confirmEdit" :editorShowFlg="false" v-show="showTree" :treeData="treeData" @onTreeAddToStepClick="onTreeAddToStepClick"/>
                        </vs-col>
                      </vs-row>
                    </vx-card>
                  </div>
                </vs-popup>
                <vs-popup class="application-page-dialog" title="" :active.sync="confirmDiskTemplateEdit">
                  <div slot="header"><h4>コメント設定</h4></div>
                  送信時のコメントに追加できる定型文を設定します。
                  <br>
                  全角半角問わず、20文字まで入力できます。

                  <vs-row class="mt-5">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment1" />
                  </vs-row>
                  <vs-row class="mt-3">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment2"/>
                  </vs-row>
                  <vs-row class="mt-3">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment3"/>
                  </vs-row>
                  <vs-row class="mt-3">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment4"/>
                  </vs-row>
                  <vs-row class="mt-3">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment5"/>
                  </vs-row>
                  <vs-row class="mt-3">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment6"/>
                  </vs-row>
                  <vs-row class="mt-3">
                    <vs-input class="inputx w-full" v-model = "diskTemplate.comment7"/>
                  </vs-row>
                  <div class="text-right mt-3">
                    <vs-button class="square" color="#dddddd" style="color:black;border-color:#ffffff;width: 23%" @click="onResetDiskTemplate"><i class="fas fa-sync-alt"></i> 初期値に戻す</vs-button>
                  </div>
                  <div class="text-right mt-3">
                    <vs-button class="square" color="primary" @click="onUpdateDiskMailInfo" style="width: 23%" ><i class="fas fa-save"></i> 設定を保存する</vs-button>
                  </div>
                </vs-popup>
                <!--  宛先表示/追加  -->
                <div class="mail-steps">
                  <div class="mail-view-list">
                    <draggable v-model="selectMailUsers">
                      <vs-row class="item me item-user-view" vs-type="flex" v-for="(user, index) in selectMailUsers" v-bind:key="user.email + index" :index="index">
                        <vs-col>
                          <span>{{ user.email }}</span>
                          <a href.prevent v-on:click="deleteMailUser(user.email)" class="text-danger" style="padding-left: 10px;" ><i class="fas fa-times"></i></a>
                        </vs-col>
                      </vs-row>
                    </draggable>
                  </div>
                  <div class="mail-form">
                    <form onsubmit="return false;">
                      <vs-row class="mt-8">
                        <div class="vx-col w-100 pl-2">
                          <vs-input v-model="emailViewSuggestModel" placeholder="メールアドレス" @keyup="onKeyUp"></vs-input>
                          <span class="text-danger text-sm" v-show="emailViewSuggestValidateMsg">{{ emailViewSuggestValidateMsg }}</span>
                        </div>
                      </vs-row>
                      <vs-row class="mt-4">
                        <vs-checkbox :value="addToContactsFlg" v-on:click="addToContactsFlg = !addToContactsFlg">アドレス帳に追加</vs-checkbox>
                        <vs-col vs-type="flex" vs-w="12" vs-xs="12" vs-justify="flex-end" vs-align="flex-end">
                          <vs-button @click.prevent="addMailUsers" class="square mr-0" color="primary" type="filled"> 追加</vs-button>
                        </vs-col>
                      </vs-row>
                    </form>
                  </div>
                </div>
                <div slot="no-body">
                </div>
                <br>
              </vx-card>
            </div>
          </div>
          <!--  件名・メッセージ  -->
          <div class="vx-row">
            <div class="vx-col w-full mb-base lg:pr-0">
              <vx-card class="mb-4">
                <vs-row class="border-bottom pb-4">
                  <h4>件名・メッセージ</h4>
                  <feather-icon icon="SettingsIcon" @click="onDiskTemplateSelect()" class="cursor-pointer navbar-fuzzy-search mr-4" style="color: black;width: 18px;height: 23px;margin-left: 18px;"></feather-icon>
                </vs-row>
                <vs-row class="mb-4 mt-6">
                  <vs-input class="inputx w-full" placeholder="件名をつけて送信できます。" v-validate="'max:50'" name="subject" v-model="title"/>
                  <span class="text-danger text-sm" v-show="errors.has('subject')">{{ errors.first('subject') }}</span>
                </vs-row>
                <vs-row>
                  <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="message"/>
                </vs-row>
                <vs-row class="mb-6">
                  <div class="w-full">
                    <v-select :options="fileDiskTemplateOptions" :clearable="false" :searchable ="false" :value="selectedComment" @input="onChangeDiskTemplate" />
                  </div>
                </vs-row>
              </vx-card>
            </div>
          </div>
          <!--  アップロード  -->
          <div class="vx-row">
            <div class="vx-col w-full mb-base lg:pr-0">
              <vx-card class="mb-4">
                <vs-row class="border-bottom pb-4">
                  <h4>アップロード</h4>
                </vs-row>
                <vs-row class="pdf-content" style="height:20%;padding-top: 3%;border: 0px;">
                  <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="upload-wrapper">
                    <div class="vx-col w-full md:w-5/6 upload-box" style="height: 100px; border-radius: 10px;border: 3px dashed #d1ecff;padding-top: 30px;padding-bottom: 30px;" id="dropZone1" @drop="handleFileSelect" @dragleave="handleDragLeave" @dragover="handleDragOver">
                      <label class="wrapper" style="padding: 0px 0px 0px;" for="onUploadMailFiles">
                        <input type="file" ref="onUploadMailFiles" multiple accept="*/*" id="onUploadMailFiles" v-on:change="onUploadMailFiles"/>
                        <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                          <label for="onUploadMailFiles" class="pb-5"><strong>クリックしてファイルを選択してください</strong></label>
                        </vs-row>
                      </label>
                    </div>
                    <vs-divider color="primary" style="font-size:1.5rem;padding-top: 15px;">クラウドストレージからファイルを選択</vs-divider>
                    <vs-row
                        vs-align="center"
                        vs-type="flex" vs-justify="center" vs-w="12">
                      <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box')" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                      <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive')" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <span class="download-item-text">OneDrive</span></vs-button>
                      <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google')" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                      <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox')" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="Dropbox"> <span class="download-item-text">Dropbox</span></vs-button>
                    </vs-row>
                  </vs-row>
                </vs-row>
                <vs-row class="mb-2 pb-4 pt-4 ">
                  <vs-list class="vx-list mb-2 pb-6 " style="padding-left: 15%">
                    <div style="padding-left: 10%" v-for="(file, index) in mailFileUploads" v-bind:key="file.file_name + index" :index="index">
                      <vs-progress v-if="file.loading" indeterminate color="success"></vs-progress>
                      <vs-row class="mb-3">
                        <vs-col vs-w="5">
                          {{ index + 1 }}.<a href="#" class="link" style="text-decoration:none;color:#000000;word-wrap:break-word;">&nbsp;{{ file.file_name }}&nbsp;&nbsp;&nbsp;</a>
                          <a href="#" v-on:click="onDeleteMailFile(index)"><i color="#000" class="fas fa-trash-alt" aria-hidden="true"></i></a>
                        </vs-col>
                        <vs-col vs-w="1"></vs-col>
                      </vs-row>
                    </div>
                  </vs-list>
                </vs-row>
              </vx-card>
            </div>
          </div>
          <!--  保護設定  -->
          <div class="vx-row">
            <div class="vx-col w-full mb-base lg:pr-0">
              <vx-card class="mb-4">
                <vs-row class="border-bottom pb-4">
                  <h4>保護設定</h4>
                </vs-row>
                <div class="mb-4 mt-6">
                  <vs-row>
                    <vs-col vs-w="6" vs-xs="12" class="mb-2" vs-align="center">セキュリティコード</vs-col>
                    <vs-col vs-w="2" vs-xs="12" vs-align="center">
                      <vs-input class="inputx w-full security-code" minlength="6" oninput="if (value.length > 60) value = value.slice(0,60)" name="subject" v-model="accessCode"/>
                    </vs-col>
                    <vs-col vs-w="2" vs-xs="12" vs-align="left">
                      <vs-button color="primary" v-on:click="generateAccessCode" style="border-radius: 0px 6px 6px 0px;padding-bottom: 7px;"><i class="fas fa-sync-alt"></i></vs-button>
                    </vs-col>
                  </vs-row>
                  <vs-row style="padding: 10px 0;">
                    <vs-col style="width: 30%" vs-align="center">ダウンロード可能期間</vs-col>
                    <vs-col vs-w="2" vs-xs="12" vs-align="center">
                      <vs-input type="number" v-model="expire_day" @input="expire_day=expire_day.replace(/[^\d]+/g,'')"/>
                    </vs-col>
                    <vs-col style="width: 3.33%" class="text-left-lg pt-3 pr-3">&nbsp;&nbsp;日</vs-col>
                    <vs-col vs-w="2" vs-xs="12" vs-align="center">
                      <vs-input type="number" v-model="expire" @input="expire=expire.replace(/[^\d]+/g,'')"/>
                    </vs-col>
                    <vs-col vs-w="2" vs-xs="12" vs-align="left" class="text-left-lg pt-3 pr-3">&nbsp;&nbsp;時間</vs-col>
                  </vs-row>
                  <vs-row>
                    <vs-col vs-w="6" vs-xs="12" class="mb-2" vs-align="center">ダウンロード可能回数</vs-col>
                    <vs-col vs-w="2" vs-xs="12" vs-align="center">
                      <vs-input type="number" v-model="count" oninput="if (value.length > 10) value = value.slice(0,10)" @input="count=count.replace(/[^\d]+/g,'')" />
                    </vs-col>
                    <vs-col vs-w="2" class="mb-2 text-left-lg pt-3 pr-3">&nbsp;&nbsp;回</vs-col>
                  </vs-row>
                </div>
              </vx-card>
            </div>
          </div>
          <div style="margin-bottom: 15px">
            <vs-row>
              <vs-col vs-type="flex" vs-lg="6" vs-xs="12">
              </vs-col>
              <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="6" vs-xs="12">
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#fff;" color="#22AD38" type="filled"
                           v-on:click="fileSendConfirm"> 送信
                </vs-button>
              </vs-col>
            </vs-row>
          </div>

        </div>
        <vs-popup class="holamundo" title="メッセージ" :active.sync="showPopupError">
          <vs-row>
            <p v-html="showPopupErrorMsg"></p>
          </vs-row>
          <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupError=false"> 閉じる</vs-button>
          </vs-row>
        </vs-popup>
      </div>
    </div>
    <modal name="upload-from-external-modal"
           :pivot-y="0.2"
           :width="700"
           :classes="['v--modal', 'upload-from-external-modal', 'p-6']"
           :height="'auto'"
           @opened="onUploadFromCloudModalOpened"
           :clickToClose="false">
      <vs-row>
        <vs-col vs-w="12" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
          <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="$modal.hide('upload-from-external-modal')"><i class="fas fa-times"></i></vs-button>
        </vs-col>
      </vs-row>
      <vs-row>
        <vs-col vs-w="12" vs-type="block">
          <img style="height: 40px" :src="cloudLogo" alt="Box">
          <p><strong>{{ cloudName }}からファイルアップロード</strong></p>
        </vs-col>
      </vs-row>
      <vs-row class="mb-3 pt-3">
        <vs-col vs-w="12" vs-type="flex" vs-justify="flex-start" vs-align="center" class="breadcrumb-container">
          <vs-breadcrumb>
            <li v-for="(item, index) in breadcrumbItems" v-bind:key="item.id + index" :index="index">
              <a href="#" v-if="!item.active" v-on:click="onUploadFromCloudBreadcrumbClick(item.id)">{{ decodeURIComponent(item.title) }} <span v-if="!item.active" class="vs-breadcrum--separator">/</span></a>
              <p v-if="item.active">{{ decodeURIComponent(item.title) }}</p>
            </li>
          </vs-breadcrumb>
        </vs-col>
        <vs-col vs-w="12" class="files pt-3 pb-3 vs-con-loading__container " id="itemsCloudToUpload">
          <vs-list class="item-list">
            <vs-list-item v-for="(file, index) in cloudFileItems" v-bind:key="file.id + index" :index="index">
              <img v-on:click="onUploadFromCloudFolderClick(file)" v-if="file.type === 'folder'" style="height: 25px" :src="require('@assets/images/folder.svg')">
              <img v-if="file.type === 'pdf'" style="height: 25px" :src="require('@assets/images/pdf.png')">
              <img v-else-if="excelSupportExtensions.includes(file.type)" style="height: 25px" :src="require('@assets/images/excel.svg')">
              <img v-else-if="wordSupportExtensions.includes(file.type)" style="height: 25px" :src="require('@assets/images/word.svg')">
              <img v-else-if="file.type !== 'folder'" style="height: 25px" :src="require('@assets/images/unresolve_file.svg')">
              <a @click="onUploadFromCloudFolderClick(file)" v-if="file.type === 'folder'" href="#">{{ file.filename }}</a>
              <a v-if="file.type !== 'folder'" href="#" @dblclick="onUploadAttachmentFromCloud(file.id, file.filename)" :class="file.id === file_id_selected_from_cloud ? 'vs-file-item-selected' : '' " @click="addToFileUpload(file.id, file.filename)">{{ file.filename }}</a>
            </vs-list-item>
          </vs-list>
        </vs-col>
      </vs-row>
      <vs-row class="mt-3 pt-6" vs-type="flex" style="border-top: 1px solid #cdcdcd">
        <vs-col vs-w="3" vs-type="flex" vs-justify="flex-end" vs-align="center" class="pr-6"><label><strong>ファイル名:</strong></label></vs-col>
        <vs-col vs-w="9" vs-type="flex" vs-justify="flex-start" vs-align="center">
          <vs-input class="inputx w-full" v-model="filename_selected_from_cloud"/>
        </vs-col>
      </vs-row>
      <vs-row class="pt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button class="square mr-2" color="success" type="filled" v-on:click="onUploadAttachmentFromCloud(file_id_selected_from_cloud, filename_selected_from_cloud)" :disabled="!file_id_selected_from_cloud">開く</vs-button>
        <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="$modal.hide('upload-from-external-modal')">キャンセル</vs-button>
      </vs-row>
    </modal>
    <modal name="over-file-size-modal"
           :pivot-y="0.2"
           :width="400"
           :classes="['v--modal', 'sync-operation-modal', 'p-4']"
           :height="'auto'"
           :clickToClose="false">
      <vs-row>
        <vs-col vs-w="12" vs-type="block">
          <p>{{max_attachment_size}}MB以上はアップロードできない</p>
        </vs-col>
      </vs-row>
      <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onOverFileSizeClick" > 閉じる</vs-button>
      </vs-row>
    </modal>
  </div>
</template>

<script>
import TopMenu from "../../../../components/portal/TopMenu";
import {mapActions, mapState} from "vuex";
import draggable from 'vuedraggable';
import 'flatpickr/dist/flatpickr.min.css';
import ContactTree from '../../../../components/contacts/ContactTree';
import {Validator} from 'vee-validate'
import config from "../../../../app.config";
import Utils from "../../../../utils/utils";

const dict = {
  custom: {
    subject: {
      max: "50文字以上は入力できません。"
    }
  }
};
Validator.localize('ja', dict)

export default {
  components: {
    draggable,
    ContactTree,
    TopMenu
  },
  data() {
    return {
      showTree: true,
      title: '', //件名
      message: '', //メッセージ
      settingLimit: '', //企業設定
      accessCode: '', //セキュリティコード
      expire_day: 2, //ダウンロード有効期限 日
      expire: 0, //ダウンロード有効期限 時
      count: 10, //ダウンロード最大回数
      treeData: [],
      emailViewSuggestModel: '', //宛先入力欄入力値
      emailViewSuggestValidateMsg: '', //宛先エラーメッセージ
      userInfo: null, //ユーザー情報
      mailFileUploads: [], //アップロードしたファイル
      // disk_mail_id: 0, //申請ID
      confirmEdit: false,
      showPopupError: false,
      showPopupErrorMsg: '',
      file_id_selected_from_cloud: 0,
      filename_selected_from_cloud: '',
      cloudLogo: null,
      cloudName: null,
      breadcrumbItems: [],
      excelSupportExtensions: ['xls', 'xlt', 'xlm', 'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xla', 'xlam', 'xll', 'xlw'],
      wordSupportExtensions: ['doc', 'dot', 'wbk', 'docx', 'docm', 'dotx', 'dotm', 'docb'],
      addToContactsFlg: 0,
      fileDiskTemplateOptions: [],
      info: {},
      selectedComment: '',
      confirmDiskTemplateEdit: false,
      diskTemplate: {},
      cloudFileItems:[],
    }
  },
  computed: {
    ...mapState({
      selectMailUsers: state => state.fileMail.selectMailUsers, //宛先
      mailId: state => state.fileMail.mailId, //メールフID
      mailFiles: state => state.fileMail.mailFiles, //ファイルリスト
      mailTitle: state => state.fileMail.title, //件名
      mailMessage: state => state.fileMail.message, //メッセージ
      mailAccessCode: state => state.fileMail.accessCode, //セキュリティコード
      mailCount: state => state.fileMail.count, //ダウンロード最大回数
      mailExpire: state => state.fileMail.expire, //ダウンロード有効期限
      mailExpireDay: state => state.fileMail.expire_day, //ダウンロード有効期限
      mailContactsFlg: state => state.fileMail.addToContactsFlg, //アドレス帳に追加
    }),
    loginUser: {
      get() {
        if (this.$store.state.home.usingPublicHash) return {};
        return JSON.parse(getLS('user'));
      }
    },
    currentCloudDrive: {
      get() {
        return this.$store.state.cloud.drive
      },
      set(value) {
        this.$store.commit('cloud/setDrive', value);
      }
    },
  },
  methods: {
    ...mapActions({
      getDepartmentUsers: "application/getDepartmentUsers",
      getListContact: "contacts/getListContact",
      addLogOperation: "logOperation/addLog",
      getContact: "contacts/getContact",
      updateContact: "contacts/updateContact",
      deleteContact: "contacts/deleteContact",
      getLimit: "setting/getLimit",
      getMyInfo: "user/getMyInfo",
      mailFilesUpload: "fileMail/mailFilesUpload",
      mailFilesDelete: "fileMail/mailFilesDelete",
      getCloudItems: "cloud/getItems",
      downloadCloudMailFile: "cloud/downloadCloudMailFile",
      getMyDiskMailInfo: "fileMail/getMyDiskMailInfo",
      updateDiskMailInfo: "fileMail/updateDiskMailInfo",
    }),


    // アドレス帳を押下
    async onDepartmentUsersSelect() {
      await this.getDepartmentUsers({filter: ''});
    },
    async getEmailTrees() {
      const mapfunc = (item) => {
        let newItem = {};
        if (!item) return null;
        if (!Object.prototype.hasOwnProperty.call(item, "parent_id")) {
          newItem = {text: item.family_name + ' ' + item.given_name, data: item, isDepartment: false};
          return newItem;
        } else {
          let children = [];
          // PAC_5-2155  アドレス帳の表示方法の変更
          if (Object.prototype.hasOwnProperty.call(item, "users")) {
            if (item.users)
              children.push(...item.users.map(mapfunc));
          }
          if (Object.prototype.hasOwnProperty.call(item, "children")) {
            if (item.children)
              children.push(...item.children.map(mapfunc));
          }
          newItem.text = item.name;
          newItem.children = children;
          newItem.data = {isGroup: true};
          return newItem;
        }

      };
      let departments = [];
      if (this.$store.state.application.departmentUsers) departments = this.$store.state.application.departmentUsers.map(mapfunc);

      const processContact = (contacts) => {
        let groups = {};
        var id = 0;
        contacts.forEach((contact) => {
          if (!contact.group_name) contact.group_name = 'グループなし';
          if (!groups[contact.group_name]) groups[contact.group_name] = [];
          contact.family_name = contact.name;
          contact.given_name = "";
          groups[contact.group_name].push({
            id: id,
            text: contact.name, data: contact
          });
          id++;
        });
        contacts = [];
        for (let group_name in groups) {
          contacts.push({text: group_name, children: groups[group_name], data: {isGroup: true}});
        }
        return contacts;
      }

      let listContact = "";
      if (!this.checkShowAddress) {
        listContact = await this.getListContact({filter: '', type: 0});
        listContact = processContact(listContact);
        if (!listContact) return false;
      }

      let listContactCommon = await this.getListContact({filter: '', type: 1});
      if (!listContactCommon) return false;

      listContactCommon = processContact(listContactCommon);

      let arrAddressTree = null;

      if (listContact) {
        arrAddressTree = [
          {text: '個人', children: listContact, data: {isGroup: true}},
          {text: '共通', children: listContactCommon, data: {isGroup: true}},
          {text: '部署', children: departments, data: {isGroup: true}},
        ];
      } else {
        arrAddressTree = [
          {text: '共通', children: listContactCommon, data: {isGroup: true}},
          {text: '部署', children: departments, data: {isGroup: true}},
        ];
      }

      return arrAddressTree;
    },
    // セキュリティコード生成
    generateAccessCode: function () {
      var text = "";
      var possible = "abcdefghijkmnpqrstuvwxyz0123456789@#%*?<>";
      for (var i = 0; i < 10; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }

      this.accessCode = text;
      return this.accessCode;
    },
    // ファイルメール便宛先追加
    async addMailUsers() {
      this.emailViewSuggestValidateMsg = '';
      if (!this.emailViewSuggestModel) {
        this.emailViewSuggestValidateMsg = '必須項目です';
        return;
      }
      const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
      if (this.emailViewSuggestModel.match(mailPattern) === null) {
        this.emailViewSuggestValidateMsg = 'メールアドレスが正しくありません';
        return;
      }
      const user = {
        email: this.emailViewSuggestModel,
      };
      if (user.email == this.loginUser.email) {
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `申請者は登録できません`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
        return;
      } else if (this.selectMailUsers.find((v) => v.email === user.email)) {
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `既に登録されています`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
        return;
      }
      this.$store.commit('fileMail/addMailUsers', user);
      this.clearViewSuggestionInput();
    },
    // ファイルメール便宛先追加後、入力欄の値クリア
    clearViewSuggestionInput() {
      this.emailViewSuggestModel = '';
    },
    //宛先削除
    deleteMailUser: function (email) {
      const tmpSelectUsers = this.selectMailUsers.slice();
      tmpSelectUsers.splice(this.selectMailUsers.findIndex((item => item.email === email)), 1);
      this.$store.commit('fileMail/deleteMailUsers', tmpSelectUsers);
    },
    //すべて削除
    clearMailUsers: function () {
      this.$store.commit('fileMail/deleteMailUsers', []);
    },
    // ファイルメール便ファイルアップロード
    onUploadMailFiles: async function (e) {
      const files = Array.from(e.target.files);
      let isUpload = false;
      files.forEach(file => {
        if (file.size > this.userInfo.file_mail_size_single * 1024 * 1024) {
          isUpload = true;
        }
      });
      if (isUpload) {
        this.$modal.show('over-file-size-modal');
        return;
      }
      const iterable = async () => {
        if (files.length > 0) {
          const file = files.shift();
          const ret = await this.mailFilesUpload({file: file, disk_mail_id: this.mailId, file_mail_size_single: this.userInfo.file_mail_size_single});
          if (ret) {
            this.$store.commit('fileMail/updateMailId', ret.disk_mail_id);
            this.mailFileUploads.push(ret.disk_mail_file);
          }
          await iterable();
        }
      };
      await iterable();
    },
    // ストレージアップロード
    onUploadFromExternalClick: function (drive) {
      this.currentCloudDrive = drive;
      this.filename_selected_from_cloud = '';
      this.is_download_external = false;
      if (this.$ls.get(drive + 'AccessToken')) {
        this.$modal.show('upload-from-external-modal');
        if (drive === 'box') {
          this.cloudLogo = require('@assets/images/box.svg');
          this.cloudName = 'Box';
        }
        if (drive === 'onedrive') {
          this.cloudLogo = require('@assets/images/onedrive.svg');
          this.cloudName = 'OneDrive';
        }
        if (drive === 'google') {
          this.cloudLogo = require('@assets/images/google-drive.png');
          this.cloudName = 'Google Drive';
        }
        if (drive === 'dropbox') {
          this.cloudLogo = require('@assets/images/dropbox.svg');
          this.cloudName = 'Dropbox';
        }
      } else {
        window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + drive, '_blank');
      }
    },
    onUploadFromCloudModalOpened: async function () {
      this.$vs.loading({
        container: '#itemsCloudToUpload',
        scale: 0.6
      });
      this.file_id_selected_from_cloud = 0;
      this.filename_selected_from_cloud = '';
      const ret = await this.getCloudItems(0);
      this.onGetCloudItemsToSystemUpload(ret);
    },
    onUploadFromCloudBreadcrumbClick: async function (folder_id) {
      this.$vs.loading({
        container: '#itemsCloudToUpload',
        scale: 0.6
      });
      const ret = await this.getCloudItems(folder_id);
      this.onGetCloudItemsToSystemUpload(ret);
    },
    onGetCloudItemsToSystemUpload: function (ret) {
      this.$vs.loading.close('#itemsCloudToUpload > .con-vs-loading');
      if (ret.statusCode === 401) {
        this.$modal.hide('upload-from-external-modal');
        this.$ls.remove('boxAccessToken');
        window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + this.currentCloudDrive, '_blank');
      }
      if (ret.statusCode === 200 && ret.data) {
        this.cloudFileItems = ret.data.item_collection.entries.filter(item => {
          let suffix = item.name.toLowerCase().split('.').slice(-1)[0];
          const suffixArr = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
          return item.type === 'folder' || (item.type === 'file' && suffixArr.indexOf(suffix) > -1)
        }).map(item => {
          let item_type = item.type === 'folder' ? 'folder' : item.name.match('[^.]*$')[0];
          return {id: item.id, type: item_type, filename: item.name}
        });
        this.breadcrumbItems = ret.data.path_collection.entries.map(item => {
          return {id: item.id, title: item.id === '0' ? 'ルート' : item.name}
        });
        this.currentCloudFolderId = ret.data.id;
        this.breadcrumbItems.push({id: ret.data.id, title: ret.data.id === '0' ? 'ルート' : ret.data.name, active: true});
      }
    },
    onUploadFromCloudFolderClick: async function (item) {
      if (item.type !== 'folder') return;
      this.$vs.loading({
        container: '#itemsCloudToUpload',
        scale: 0.6
      });
      const ret = await this.getCloudItems(item.id);
      this.onGetCloudItemsToSystemUpload(ret);
    },
    onUploadAttachmentFromCloud: async function (fileId, filename) {
      let file_data = {
        file_id: encodeURIComponent(fileId),
        filename: encodeURIComponent(filename),
        disk_mail_id: encodeURIComponent(this.mailId),
        file_mail_size_single: encodeURIComponent(this.userInfo.file_mail_size_single),
      };
      this.$vs.loading({
        container: '#itemsCloudToUpload',
        scale: 0.6
      });
      this.$modal.hide('upload-from-external-modal');
      const iterable = async () => {
        const ret = await this.downloadCloudMailFile(file_data); //ファイルをサーバにアップロードして保存します
        if (ret) {
          this.mailFileUploads.push(ret.data.disk_mail_file);
          this.$store.commit('fileMail/updateMailId', ret.data.disk_mail_id);
          this.addLogOperation({action: 'r01-upload', result: 0, params: {filename: file_data.filename}});
        } else {
          this.addLogOperation({action: 'r01-upload', result: 1, params: {filename: file_data.filename}});
        }
      };
      await iterable();
    },
    addToFileUpload(fileId, filename) {
      this.file_id_selected_from_cloud = this.file_id_selected_from_cloud === fileId ? '' : fileId;
      this.filename_selected_from_cloud = this.filename_selected_from_cloud === filename ? '' : filename;
    },
    //ファイル削除
    onDeleteMailFile: async function (index) {
      await this.mailFilesDelete(this.mailFileUploads[index].file_id);
      this.mailFileUploads.splice(index, 1);
    },
    fileSendList: function () {
      this.$router.push('/groupware/file_mail/list');
    },
    fileSendConfirm: function () {
      if (this.selectMailUsers.length < 1) {
        this.showPopupError = true;
        this.showPopupErrorMsg = '宛先を省略することはできません。';
        return;
      } else if (this.title.length > 50) {
        this.showPopupError = true;
        this.showPopupErrorMsg = '件名が50文字超えています。';
        return;
      } else if (this.mailFileUploads.length < 1) {
        this.showPopupError = true;
        this.showPopupErrorMsg = 'アップロードファイルを省略することはできません。';
        return;
      } else if (this.accessCode.length < 6) {
        this.showPopupError = true;
        this.showPopupErrorMsg = 'セキュリティコードが6桁以上を入力してください。';
        return;
      } else if (this.expire_day > 30|| this.expire_day < 0) {
        this.showPopupError = true;
        this.showPopupErrorMsg = "ダウンロード可能期間の入力は必須です。<br>" +
            '入力可能な最大値は30日24時間です。';
        return;
      } else if (this.expire > 24 || this.expire < 0) {
        this.showPopupError = true;
        this.showPopupErrorMsg = "ダウンロード可能期間の入力は必須です。<br>" +
            '入力可能な最大値は30日24時間です。';
        return;
      } else if (this.expire_day == 0 && this.expire == 0) {
        this.showPopupError = true;
        this.showPopupErrorMsg = "ダウンロード可能期間の入力は必須です。<br>" +
            '入力可能な最大値は30日24時間です。';
        return;
      } else if (this.count == 0 && this.count != '') {
        this.showPopupError = true;
        this.showPopupErrorMsg = 'ダウンロード可能回数には1以上の数字を入力してください。';
        return;
      }
      this.$router.push('/groupware/file_mail/confirm');
    },
    onTreeAddToStepClick: function (userChecked) {
      $(".application-page-dialog .vs-popup--close").click();
      for (var i = 0; i < userChecked.length; i++) {
        let user = userChecked[i];
        const user_email = {
          'email': user.email
        };
        if (user.email == this.loginUser.email) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `申請者は登録できません${user.email}`,
          });
        } else if (this.selectMailUsers.find((v) => v.email === user.email)) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `既に登録されています${user.email}`,
          });
        } else {
          this.$store.commit('fileMail/addMailUsers', user_email);
        }
      }
    },
    onOverFileSizeClick: async function(){
      await this.$modal.hide('over-file-size-modal');
    },
    handleFileSelect: async function(evt) {
      const dropZone = document.getElementById('dropZone1');
      dropZone.style.borderColor = '#D1ECFF';
      evt.stopPropagation();
      evt.preventDefault();
      const files = Array.from(evt.dataTransfer.files);
      let isUpload = false;
      files.forEach(file => {
        if (file.size > this.userInfo.file_mail_size_single * 1024 * 1024) {
          isUpload = true;
        }
      });
      if (isUpload) {
        this.$modal.show('over-file-size-modal');
        return;
      }
      const iterable = async () => {
        if (files.length > 0) {
          const file = files.shift();
          const ret = await this.mailFilesUpload({file: file, disk_mail_id: this.mailId, file_mail_size_single: this.userInfo.file_mail_size_single});
          if (ret) {
            this.$store.commit('fileMail/updateMailId', ret.disk_mail_id);
            this.mailFileUploads.push(ret.disk_mail_file);
          }
          await iterable();
        }
      };
      await iterable();
    },
    handleDragLeave: function(evt) {
      const dropZone = document.getElementById('dropZone1');
      dropZone.style.borderColor = '#D1ECFF';
      evt.stopPropagation();
      evt.preventDefault();
    },
    handleDragOver: function(evt) {
      const dropZone = document.getElementById('dropZone1');
      dropZone.style.borderColor = '#55efc4';
      evt.stopPropagation();
      evt.preventDefault();
      evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
    },
    onKeyUp(event) {
      if (event.keyCode === 13) this.addMailUsers();
    },
    onChangeDiskTemplate(value) {
      this.selectedComment = value;
      if (this.message == null){
        this.message = '';
      }
      this.message = this.message.concat(value);
      this.selectedComment = value +' ';
    },
    async onDiskTemplateSelect() {
      this.confirmDiskTemplateEdit = true;
      this.info = await this.getMyDiskMailInfo();
      this.diskTemplate = {
        comment1: this.info.comment1,
        comment2: this.info.comment2,
        comment3: this.info.comment3,
        comment4: this.info.comment4,
        comment5: this.info.comment5,
        comment6: this.info.comment6,
        comment7: this.info.comment7,
      };
    },
    onResetDiskTemplate() {
      this.diskTemplate.comment1 = '確認をお願いします。';
      this.diskTemplate.comment2 = 'ご確認をお願い致します。';
      this.diskTemplate.comment3 = '至急確認をお願いします。';
      this.diskTemplate.comment4 = '至急ご確認をお願い致します。';
      this.diskTemplate.comment5 = 'ご確認の程よろしくお願い申し上げます。';
      this.diskTemplate.comment6 = '';
      this.diskTemplate.comment7 = '';
    },
    async onUpdateDiskMailInfo() {
      this.info.comment1 = this.diskTemplate.comment1;
      this.info.comment2 = this.diskTemplate.comment2;
      this.info.comment3 = this.diskTemplate.comment3;
      this.info.comment4 = this.diskTemplate.comment4;
      this.info.comment5 = this.diskTemplate.comment5;
      this.info.comment6 = this.diskTemplate.comment6;
      this.info.comment7 = this.diskTemplate.comment7;
      await this.updateDiskMailInfo(this.info);
      this.confirmDiskTemplateEdit = false;
      this.info = await this.getMyDiskMailInfo();
      this.fileDiskTemplateOptions =  Utils.setEmailTemplateOptions(this.info);
    },
  },
  async mounted() {
    this.info = await this.getMyDiskMailInfo();
    this.fileDiskTemplateOptions =  Utils.setEmailTemplateOptions(this.info);
    this.diskTemplate = {
      comment1: this.info.comment1,
      comment2: this.info.comment2,
      comment3: this.info.comment3,
      comment4: this.info.comment4,
      comment5: this.info.comment5,
      comment6: this.info.comment6,
      comment7: this.info.comment7,
    };
  },
  watch: {
    "treeData": function () {
      this.treeLoaded++;
    },
    "$store.state.application.loadDepartmentUsersSuccess": async function () {
      this.treeData = await this.getEmailTrees();
      // this.addLogOperation({action: 'r08-display-contacts', result: 0});
      this.confirmEdit = true;
    },
    "title": function () {
      this.$store.commit('fileMail/updateMailTitle', this.title);
    },
    "message": function () {
      this.$store.commit('fileMail/updateMailComment', this.message);
    },
    "expire_day": function () {
      this.$store.commit('fileMail/updateMailExpireDay', this.expire_day);
    },
    "expire": function () {
      this.$store.commit('fileMail/updateMailExpire', this.expire);
    },
    "count": function () {
      this.$store.commit('fileMail/updateMailCount', this.count);
    },
    "accessCode": function () {
      this.accessCode = this.accessCode.replace(/[^a-zA-Z0-9@#%*?<>]/, '');
      this.$store.commit('fileMail/updateMailAccessCode', this.accessCode);
    },
    "mailFileUploads": function () {
      this.$store.commit('fileMail/updateMailFiles', this.mailFileUploads);
    },
    "addToContactsFlg": function () {
      this.$store.commit('fileMail/updateMailContactsFlg', this.addToContactsFlg);
    },
  },
  async created() {
    if (this.$route.query && this.$route.query.back == 'true') {
      this.title = this.mailTitle;
      this.message = this.mailMessage;
      this.accessCode = this.mailAccessCode;
      this.expire_day = this.mailExpireDay;
      this.expire = this.mailExpire;
      this.count = this.mailCount;
      this.mailFileUploads = this.mailFiles
      this.addToContactsFlg = this.mailContactsFlg
      // this.disk_mail_id = this.mailId
    } else {
      this.clearMailUsers();
      this.$store.commit('fileMail/updateMailTitle', '');
      this.$store.commit('fileMail/updateMailComment', '');
      this.$store.commit('fileMail/updateMailAccessCode', this.generateAccessCode());
      this.$store.commit('fileMail/updateMailCount', 10);
      this.$store.commit('fileMail/updateMailExpireDay', 2);
      this.$store.commit('fileMail/updateMailExpire', 0);
      this.$store.commit('fileMail/updateMailId', 0);
      this.$store.commit('fileMail/updateMailFiles', []);
      this.$store.commit('fileMail/updateMailContactsFlg', 0);
    }
    this.settingLimit = null;
    const promises = [];
    promises.push(
        (async () => {
          this.userInfo = await this.getMyInfo();
        })(),
        (async () => {
          this.settingLimit = null;
          if (!Object.prototype.hasOwnProperty.call(this.loginUser, "isAuditUser") || !this.loginUser.isAuditUser) {
            this.settingLimit = await this.getLimit();
          }
          if (this.settingLimit == null) {
            this.settingLimit = {};
          }
        })(),
    );
  }
}

</script>
<style lang="scss">
.iframe-groupware {
  height: calc(100vh - 87px);
}

.upload-wrapper {
  height: calc(100% - 50px);

  .upload-box {
    border-radius: 10px;
    border: 3px dashed #D1ECFF;

    label.wrapper {
      padding: 60px 15px 80px;
      float: left;
      width: 100%;
      height: 100%;
    }

    label[for="uploadFile"] {
      cursor: pointer;
    }

    img.file {
      width: 32px;
      margin-right: 1rem;
    }

    img.cloud {
      width: 64px;
    }

    input[type="file"] {
      display: none;
    }
  }
}

.upload-from-external-modal {
  .breadcrumb-container {
    background: #00aef124;
  }

  .vs-list--slot {
    margin-left: 0;
    margin-right: auto;
    display: flex;
    align-items: center;
    width: 100%;

    img {
      margin-right: 1rem;
    }

    a {
      width: 100%;
    }
  }

  .vs-list--item:hover {
    background: #00aef112;
  }

  #itemsCloudToUpload {
    min-height: 200px;
    max-height: 500px;
    overflow: auto;
  }

  .vs-file-item-selected {
    background: #4287f5;
    color: #fff;
  }
}
.security-code {
  .vs-input--input{
    border-radius: 0;
  }
}
/*.v--modal-box {
  position: absolute !important;
  left: calc(50% - 150px) !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
}*/

.v--modal-overlay {
  z-index: 52001;
}
</style>
