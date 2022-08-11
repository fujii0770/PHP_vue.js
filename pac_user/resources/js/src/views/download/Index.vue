<template>
    <div>
        <div id="sends-page" style="position: relative;">
            <vs-row>
                <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5"
                        style="transition: width .2s;">
                    <vs-card>
                        <vs-table class="mt-3 custome-event" :data="downloadRequestListData" noDataText="データがありません。"
                                  sst @sort="handleSort" stripe>
                            <template slot="thead">
                                <vs-th sort-key="file_names" class="max-width-200">文書名</vs-th>
                                <vs-th sort-key="R.contents_create_at">作成日</vs-th>
                                <vs-th sort-key="download_period">ダウンロード期限</vs-th>
                                <vs-th sort-key="state">ダウンロード</vs-th>
                                <vs-th sort-key="state">削除</vs-th>
                            </template>

                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <td class="max-width-200">{{tr.file_name}}</td>
                                    <td>{{tr.contents_create_at | moment("YYYY/MM/DD HH:mm")}}</td>
                                    <td>{{tr.download_period | moment("YYYY/MM/DD HH:mm")}}</td>
                                    <td>
                                        <div v-if="tr.state==0">処理待ち</div>
                                        <div v-else-if="tr.state==1">作成中</div>
                                        <div v-else-if="tr.state==11">無害化待ち</div>
                                        <div v-else-if="tr.state==12">無害化中</div>
                                        <div v-else-if="tr.state==13">データ取得中</div>
                                        <!-- ダウンロードボタンを表示すれば -->
                                        <div v-else-if="(tr.state==2 || tr.state==3 || tr.state==4) && tr.sanitizing_state==0">
                                            <!--<vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary" @click="onDownload(tr.id)">
                                                ダウンロード</vs-button>-->
                                            <vs-dropdown :vs-trigger-click="is_ipad"
                                                    v-if="settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox">
                                                <vs-button id="button5"
                                                           class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0"
                                                           color="primary" type="filled"><i class="fas fa-download"></i>
                                                    ダウンロード
                                                </vs-button>
                                                <vs-dropdown-menu>
                                                    <vs-dropdown-item v-if="settingLimit.storage_local">
                                                        <!--   PAC_5-1092 ファイル情報を取得する                           -->
                                                        <vs-button v-on:click="onDownload(tr.id)" color="primary"
                                                                   class="w-full download-item" type="filled"><i
                                                                class="fas fa-download"></i> ローカル
                                                        </vs-button>
                                                    </vs-dropdown-item>
                                                    <vs-dropdown-item v-if="settingLimit.storage_box">
                                                        <vs-button color="primary"
                                                                   v-on:click="downloadToCloud('box',tr.id)"
                                                                   class="w-full download-item" type="border"><img
                                                                class="download-icon"
                                                                :src="require('@assets/images/box.svg')" alt="Box">
                                                            <span class="download-item-text">Box</span></vs-button>
                                                    </vs-dropdown-item>
                                                    <vs-dropdown-item v-if="settingLimit.storage_onedrive">
                                                        <vs-button color="primary"
                                                                   v-on:click="downloadToCloud('onedrive',tr.id)"
                                                                   class="w-full download-item" type="border"><img
                                                                class="download-icon"
                                                                :src="require('@assets/images/onedrive.svg')"
                                                                alt="OneDrive">
                                                            <p>OneDrive</p></vs-button>
                                                    </vs-dropdown-item>
                                                    <vs-dropdown-item v-if="settingLimit.storage_google">
                                                        <vs-button color="primary"
                                                                   v-on:click="downloadToCloud('google',tr.id)"
                                                                   class="w-full download-item" type="border"><img
                                                                class="download-icon"
                                                                :src="require('@assets/images/google-drive.png')"
                                                                alt="Google Drive"> <span class="download-item-text">Google Drive</span>
                                                        </vs-button>
                                                    </vs-dropdown-item>
                                                    <vs-dropdown-item v-if="settingLimit.storage_dropbox">
                                                        <vs-button color="primary"
                                                                   v-on:click="downloadToCloud('dropbox',tr.id)"
                                                                   class="w-full download-item" type="border"><img
                                                                class="download-icon"
                                                                :src="require('@assets/images/dropbox.svg')" alt="pdf">
                                                            <span class="download-item-text">Dropbox</span></vs-button>
                                                    </vs-dropdown-item>
                                                </vs-dropdown-menu>
                                            </vs-dropdown>


                                        </div>
                                        <!-- 無害化処理ボタンを表示すれば -->
                                        <div v-else-if="(tr.state==2 || tr.state==3 || tr.state==4) && tr.sanitizing_state==1">
                                            <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="warning"  @click="sanitizingWaitUpdate(tr.id)"
                                                       ><i class="fas fa-bell"></i> 無害化処理</vs-button>
                                        </div>
                                        <!-- 無害化状態が2：無害化待ち -->
                                        <div v-else-if="(tr.state==2 || tr.state==3 || tr.state==4) && tr.sanitizing_state==2">無害化待ち</div>
                                        <div v-else-if="tr.state==10">期限終了</div>
                                        <div v-else>
                                            <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="dark"
                                                       @click="showDialogReRequest(tr.id, tr.file_name)">
                                                失敗
                                            </vs-button>
                                        </div>
                                    </td>
                                    <td>
                                        <vs-button class="square" color="danger"
                                                   @click="showDialogDelete(tr.id, tr.file_name)">
                                            削除
                                        </vs-button>
                                    </td>
                                </vs-tr>
                            </template>
                        </vs-table>
                        <div>
                            <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to
                                }} 件までを表示
                            </div>
                        </div>
                        <vs-pagination :total="pagination.totalPage" v-model="pagination.currentPage"></vs-pagination>
                    </vs-card>
                </vs-col>
            </vs-row>

            <vs-popup classContent="popup-example" title="回覧の削除" :active.sync="confirmDelete">
                <div>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ this.selectedName }}</vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="12">この文書を削除します。</vs-col>
                    </vs-row>
                </div>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                        <vs-button @click="onDelete(selectedId)" color="danger">削除</vs-button>
                        <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                    </vs-col>
                </vs-row>
            </vs-popup>

            <vs-popup classContent="popup-example" title="再度ダウンロード要求" :active.sync="confirmReRequest">
                <div>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ this.selectedName }}</vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="12">再度ダウンロード要求をいたしますか？</vs-col>
                    </vs-row>
                </div>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                        <vs-button @click="onReRequest(selectedId)" color="danger">ダウンロード予約</vs-button>
                        <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                    </vs-col>
                </vs-row>
            </vs-popup>

            <!-- PAC_5-3116 S -->
            <modal name="cloud-upload-modal"
                   :pivot-y="0.2"
                   :classes="['v--modal', 'cloud-upload-modal', 'p-6']"
                   :min-width="200"
                   :min-height="200"
                   :scrollable="true"
                   :reset="true"
                   width="40%"
                   height="auto"
                   @opened="onCloudModalOpened"
                   :clickToClose="false">
            <!-- PAC_5-3116 E -->
                <vs-row>
                    <vs-col vs-w="12" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
                        <vs-button radius color="danger" type="flat"
                                   style="font-size: 18px;position: absolute;top: 10px;right: 0;"
                                   v-on:click="closeCloud"><i class="fas fa-times"></i>
                        </vs-button>
                    </vs-col>
                </vs-row>
                <vs-row>
                    <vs-col vs-w="12" vs-type="block">
                        <img style="height: 40px" :src="cloudLogo" alt="Box">
                        <p><strong>{{cloudName}}にファイル保存</strong></p>
                    </vs-col>
                </vs-row>
                <vs-row class="mb-3 pt-3">
                    <vs-col vs-w="12" vs-type="flex" vs-justify="flex-start" vs-align="center"
                            class="breadcrumb-container">
                        <vs-breadcrumb>
                            <li v-for="(item, index) in breadcrumbItems" v-bind:key="item.id + index" :index="index">
                                <a href="#" v-if="!item.active" v-on:click="onBreadcrumbItemClick(item.id)">{{item.title}}
                                    <span v-if="!item.active" class="vs-breadcrum--separator">/</span></a>
                                <p v-if="item.active">{{item.title}}</p>
                            </li>
                        </vs-breadcrumb>
                    </vs-col>
                    <vs-col vs-w="12" class="files pt-3 pb-3 vs-con-loading__container" id="cloudItems">
                        <vs-list>
                            <vs-list-item v-for="(file, index) in cloudFileItems" v-bind:key="file.id + index"
                                          :index="index">
                                <img v-on:click="onCloudItemClick(file)" v-if="file.type === 'folder'"
                                     style="height: 25px" :src="require('@assets/images/folder.svg')">
                                <img v-if="file.type === 'pdf'" style="height: 25px"
                                     :src="require('@assets/images/pdf.png')">
                                <a v-on:click="onCloudItemClick(file)" v-if="file.type === 'folder'" href="#">{{file.filename}}</a>
                                <p v-if="file.type === 'pdf'" href="#">{{file.filename}}</p>
                            </vs-list-item>
                        </vs-list>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3 pt-6" vs-type="flex" style="border-top: 1px solid #cdcdcd">
                    <vs-col vs-w="3" vs-type="flex" vs-justify="flex-end" vs-align="center" class="pr-6"><label><strong>ファイル名:</strong></label>
                    </vs-col>
                    <vs-col vs-w="9" vs-type="flex" vs-justify="flex-start" vs-align="center">
                        <vs-input class="inputx w-full" placeholder="ファイル名" v-model="filename_upload"/>
                    </vs-col>
                </vs-row>
                <vs-row class="pt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2" color="success" type="filled" v-on:click="onUploadCheck()"
                               :disabled="!filename_upload"> ファイル保存
                    </vs-button>
                    <vs-button class="square mr-0" color="#bdc3c7" type="filled"
                               v-on:click="closeCloud"> キャンセル
                    </vs-button>
                </vs-row>
            </modal>
            
            <modal name="updatecheck-doc-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="8" vs-type="flex" vs-align="center">
                    <h2 class="mb-2 pb-2" style="font-size: 18px;">保存先の確認</h2>
                </vs-col>
                <vs-col vs-w="4" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
                    <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="cancelConfirmUpdate"> <i class="fas fa-times"></i></vs-button>
                </vs-col>
            </vs-row>
            <vs-row class="mb-3 pt-3" style="border-top: 1px solid #cdcdcd">
                <p class="mb-4">同名のファイルが存在します。<br>
                <p>上書き保存しますか？</p>
            </vs-row>
            <vs-row class="pt-3" vs-type="flex" vs-justify="flex-end" vs-align="center" style="border-top: 1px solid #cdcdcd">
                <vs-button class="square mr-2" color="success" type="filled" v-on:click="onUploadToCloudClick(true)"> 上書き保存</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelConfirmUpdate"> キャンセル</vs-button>
            </vs-row>
        </modal>
        </div>

        <!-- 5-277 mobile html -->
        <div id="sends-page-mobile" style="position: relative;">
            <vs-row>
                <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5"
                        style="transition: width .2s;">
                    <div class="sends-mobile-title"><h3>ダウンロード状況確認一覧</h3></div>
                    <vs-card>
                        <vs-table class="mt-3 custome-event" :data="downloadRequestListData" noDataText="データがありません。"
                                  sst @sort="handleSort" stripe>
                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <td class="max-width-150">
                                        <div class="show-list">{{tr.file_name}}</div>
                                    </td>
                                    <td>
                                        <div v-if="tr.state==0">処理待ち</div>
                                        <div v-else-if="tr.state==1">作成中</div>
                                        <div v-else-if="tr.state==2 || tr.state==3 || tr.state==4"
                                             @click="onDownload(tr.id)"><i class="fas fa-download"></i></div>
                                        <div v-else-if="tr.state==9">期限終了</div>
                                        <div v-else-if="tr.state==11">無害化待ち</div>
                                        <div v-else-if="tr.state==12">無害化中</div>
                                        <div v-else-if="tr.state==13">データ取得中</div>
                                        <div v-else>
                                            <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="dark"
                                                       @click="showDialogReRequest(tr.id, tr.file_name)">
                                                処理失敗
                                            </vs-button>
                                        </div>
                                    </td>
                                    <td>
                                        <vs-button class="square" color="danger"
                                                   @click="showDialogDelete(tr.id, tr.file_name)">
                                            削除
                                        </vs-button>
                                    </td>
                                </vs-tr>
                            </template>
                        </vs-table>
                        <div>
                            <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from ? pagination.from : 0 }}
                                件から {{ pagination.to ? pagination.to : 0}} 件までを表示
                            </div>
                        </div>
                        <vs-pagination :total="pagination.totalPage" v-model="pagination.currentPage"></vs-pagination>
                    </vs-card>
                </vs-col>
            </vs-row>
        </div>
    </div>
</template>


<script>
    import {mapState, mapActions} from "vuex";
    import InfiniteLoading from 'vue-infinite-loading';

    import flatPickr from 'vue-flatpickr-component'
    import 'flatpickr/dist/flatpickr.min.css';
    import {Japanese} from 'flatpickr/dist/l10n/ja.js';
    import config from "../../app.config";
    import Axios from "axios";
    import circularsService from "../../services/circulars.service";
    import {CIRCULAR} from "../../enums/circular";
    import homeService from "../../services/home.service";
    import {cloudService} from "../../services/cloud.service";

    export default {
        components: {
            InfiniteLoading,
            flatPickr,
        },
        data() {
            return {
                filter: {
                    id: "",
                    kind: "",
                    name: "",
                    destEnv: "",
                    fromdate: "",
                    todate: "",
                    state: "",
                },
                selectAll: false,
                listData: [],
                pagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
                orderBy: "update_at",
                orderDir: "desc",
                configDate: {
                    locale: Japanese,
                    wrap: true,
                    defaultHour: 0
                },
                confirmDelete: false,
                confirmReRequest: false,
                confirmSaveLongTerm: false,
                showReading: false,
                options_status: ['保存中', '回覧中', '回覧完了', '回覧完了(保存済)', '差戻し', '引戻', '', '', '', '削除'],
                recived_status: ['未通知', '通知済/未読', '既読', '承認(捺印あり)', '承認(捺印なし)', '差戻し', '差戻し(未読)'],
                circular_kind: ['受信', '送信'],
                //    env: {'00':'スタンダードAWS', '01':'スタンダードK5', '10':'プロフェッショナルAWS', '11':'プロフェッショナルK5'},
                //    env: {'00':'Corporate1', '01':'Corporate2', '10':'Business Pro1', '11':'Business Pro2'},
                out_status: ['-', '未読', '処理済'],
                itemPull: {},
                itemReading: {},
                itemReadingDetail: {circular: {}, userSend: {}, userReceives: [{}]},
                click: 0,
                time: null,
                searchAreaFlg: false,
                canStoreCircular: false,
                keywords: '',
                selectedFileName: '',
                selectedFileId: '',
                reloadInterval: 600,
                reloadTime: 0,
                settingLimit: {},
                filename_upload: '',
                cloudLogo: null,
                cloudName: null,
                cloudFileItems: [],
                cloudID: 0,
                downloadID: 0,
                breadcrumbItems: [],
                currentCloudFolderId: 0,
                dataBlob: '',
                input: {
                    ids: [],
                    filename: "",
                },
                // PAC_5-1216対応 ▼
                saveId: null,//上書きファイルID
                // PAC_5-1216対応 ▲
                is_ipad: false,
            }
        },
        methods: {
            ...mapActions({
                search: "circulars/getDownloadRequest",
                getDetailCircularUser: "circulars/getDetailCircularUser",
                getOriginCircularUrl: "circulars/getOriginCircularUrl",
                deleteDownloadRequest: "circulars/deleteDownloadRequest",
                downloadDownloadRequestData: "circulars/downloadDownloadRequestData",
                reRequestDownload: "circulars/reRequestDownload",
                sanitizingUpdate: "circulars/sanitizingUpdate",//PAC_5-2874
                getLimit: "setting/getLimit",
                getCloudItems: "cloud/getItems",
                updateCircularStatus: "circulars/updateCircularStatus",
            }),
            onSearch: async function (resetPaging) {
                this.selectAll = false;
                let info = {
                    kind: this.filter.kind,
                    filename: this.filter.filename,
                    senderName: this.filter.senderName,
                    senderEmail: this.filter.senderEmail,
                    destEnv: this.filter.destEnv,
                    fromdate: this.filter.fromdate,
                    todate: this.filter.todate,
                    receiverName: this.filter.receiverName,
                    receiverEmail: this.filter.receiverEmail,
                    page: resetPaging ? 1 : this.pagination.currentPage,
                    limit: this.pagination.limit,
                    orderBy: this.orderBy,
                    orderDir: this.orderDir,
                };
                var data = await this.search(info);
                this.listData = data.data;
                this.pagination.totalItem = data.length;
                this.pagination.totalPage = data.last_page;
                this.pagination.currentPage = data.current_page;
                this.pagination.limit = data.per_page;
                this.pagination.from = data.from;
                this.pagination.to = data.to;
            },
            showDialogDelete(id, fileName) {
                this.selectedFileId = id;
                this.selectedFileName = fileName;
                this.confirmDelete = true;
            },
            onDelete: async function (id) {
                this.confirmDelete = false;
                await this.deleteDownloadRequest({id: id, isCloud: false});
                this.onSearch(false);
            },
            onDownload: async function (id) {
                this.updateCircularStatus(id);
                await this.downloadDownloadRequestData({id: id ,isCloud:false });
                this.onSearch(false);
            },
            showDialogReRequest(id, fileName) {
                this.selectedFileId = id;
                this.selectedFileName = fileName;
                this.confirmReRequest = true;
            },
            onReRequest: async function (id) {
                this.confirmReRequest = false;
                await this.reRequestDownload(id);
                this.onSearch(false);
            },
            // PAC_5-2874 S
            sanitizingWaitUpdate: async function (id){
                await this.sanitizingUpdate(id);
                this.onSearch(false);
            },
            // PAC_5-2874 E
            async onShowReading(tr) {
                this.click++

                if (this.click == 1) {
                    var root = this;
                    var time = setTimeout(async function () {
                        root.click = 0;
                        root.itemReading = tr;
                        root.$store.dispatch('updateLoading', true);
                        root.itemReadingDetail = await root.getDetailCircularUser(tr.id);
                        root.showReading = true;
                        root.$store.dispatch('updateLoading', false);
                    }, 300)
                } else {
                    clearTimeout(time);
                    this.click = 0;
                    var getOrigin = await this.getOriginCircularUrl(tr.id);
                    if (getOrigin.originCircularUrl == null) {
                        this.$router.push('/completed/' + tr.id);
                    } else {
                        this.openWindow(getOrigin.originCircularUrl);
                    }
                }
            },
            handleSort(key, active) {
                this.orderBy = key;
                this.orderDir = active ? "DESC" : "ASC";
                this.onSearch(false);
            },
            openWindow(url) {
                window.open(url, '_blank');
            },
            onAroundArrow: function () {
                let obj = document.getElementById("arrow");
                if (this.searchAreaFlg) {
                    obj.classList.add("around_return");
                    obj.classList.remove("around");
                } else {
                    obj.classList.add("around");
                    obj.classList.remove("around_return");
                }
                this.searchAreaFlg = !this.searchAreaFlg;
            },
            onAroundArrowMobile: function () {
                let obj = document.getElementById("arrow_mobile");
                if (this.searchAreaFlg) {
                    obj.classList.add("around_return");
                    obj.classList.remove("around");
                } else {
                    obj.classList.add("around");
                    obj.classList.remove("around_return");
                }
                this.searchAreaFlg = !this.searchAreaFlg;
            },
            async onShowReadingMobile(tr) {
                var getOrigin = await this.getOriginCircularUrl(tr.id);
                if (getOrigin.originCircularUrl == null) {
                    this.$router.push('/completed/' + tr.id);
                } else {
                    this.openWindow(getOrigin.originCircularUrl);
                }
            },
            async downloadToCloud(drive, id) {
                this.downloadID = id;
                this.currentCloudDrive = drive;
                this.cloudID = 0;
                if (this.$ls.get(drive + 'AccessToken')) {
                    let filenameUpload = '';
                    let isCloud = true;
                    await circularsService.downloadDownloadRequestData({id: id,isCloud: isCloud}).then(
                        response => {
                            if (response && response.data) {
                                const data = response.data;
                                filenameUpload = data.fileName;
                                if (data.fileName && data.file_data) {
                                    const byteString = Base64.atob(data.file_data);
                                    const ab = new ArrayBuffer(byteString.length);
                                    const ia = new Uint8Array(ab);
                                    for (let i = 0; i < byteString.length; i++) {
                                        ia[i] = byteString.charCodeAt(i);
                                    }
                                    this.dataBlob = new Blob([ab]);
                                }
                            }
                        },
                        error => {                          
                            dispatch("alertError", error, {root: true});
                            return Promise.reject(false);
                        }
                    );

                    this.$modal.show('cloud-upload-modal');
                    this.filename_upload = filenameUpload;
                    this.cloudID = id;

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
            onGetBoxItemsDone: function (ret) {
                this.$vs.loading.close('#cloudItems > .con-vs-loading');
                if (ret.statusCode === 401) {
                    this.closeCloud;
                    this.$ls.remove('boxAccessToken');
                    window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + this.currentCloudDrive, '_blank');
                }
                if (ret.statusCode === 200 && ret.data) {
                    this.cloudFileItems = ret.data.item_collection.entries.filter(item => {
                        return item.type === 'folder' || (item.type === 'file' && item.name.includes('.pdf'))
                    }).map(item => {
                        return {id: item.id, type: item.type === 'folder' ? 'folder' : 'pdf', filename: item.name}
                    });
                    this.breadcrumbItems = ret.data.path_collection.entries.map(item => {
                        return {id: item.id, title: item.id === '0' ? 'ルート' : item.name}
                    })
                    this.currentCloudFolderId = ret.data.id;
                    this.breadcrumbItems.push({
                        id: ret.data.id,
                        title: ret.data.id === '0' ? 'ルート' : ret.data.name,
                        active: true
                    });
                }
            },
            onBreadcrumbItemClick: async function (folder_id) {
                this.$vs.loading({
                    container: '#cloudItems',
                    scale: 0.6
                });
                const ret = await this.getCloudItems(folder_id);
                this.onGetBoxItemsDone(ret);
            },
            onCloudModalOpened: async function () {
                this.$vs.loading({
                    container: '#cloudItems',
                    scale: 0.6
                });
                /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
                this.$store.commit('home/updateCloudBoxFlg',true);
                /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
                const ret = await this.getCloudItems(0);
                this.onGetBoxItemsDone(ret);

            },
            onCloudItemClick: async function (item) {
                if (item.type !== 'folder') return;
                this.$vs.loading({
                    container: '#cloudItems',
                    scale: 0.6
                });
                const ret = await this.getCloudItems(item.id);
                this.onGetBoxItemsDone(ret);
            },
            //PAC_5-1216 Start boxファイル上書き保存
            cancelConfirmUpdate: function() {
              this.$modal.hide('updatecheck-doc-modal');
            },
            onUploadCheck:function(){
              this.saveId = null;
              var isUpdate = false;
                this.cloudFileItems.forEach(function(File){
                  if(this.filename_upload == File.filename){
                    this.saveId = File.id;
                    isUpdate = true;
                  }
                },this);

              if(isUpdate && this.currentCloudDrive == 'box'){
                  this.$modal.show('updatecheck-doc-modal');
                }else{
                  this.onUploadToCloudClick(false);
                }
            },
            onUploadToCloudClick: async function (confirm) {
                if(confirm){
                    this.$modal.hide('updatecheck-doc-modal');
                }
                let data = {
                    drive: this.currentCloudDrive,
                    folder_id: this.currentCloudFolderId,
                    filename: this.filename_upload,
                }
                if(this.saveId){
                    data["file_id"] = this.saveId;
                }
              //PAC_5-1216 END
                this.$vs.loading({
                    container: '#cloudItems',
                    scale: 0.6
                });
                const uploadRet = await this.uploadToCloud(data);
                if (uploadRet) {
                    const ret = await this.getCloudItems(this.currentCloudFolderId);
                    this.onGetBoxItemsDone(ret);
                }
            },
            uploadToCloud(data) {
                this.updateCircularStatus(this.cloudID);
                return cloudService.upload(data.drive, data.folder_id, data.filename, this.dataBlob, data.file_id).then(
                    resp => {
                        this.$store.dispatch("alertSuccess", resp.message, {root: true});
                        this.deleteDownloadRequest({id: this.cloudID, isCloud: true});
                        this.closeCloud();
                        return Promise.resolve(true);
                    },
                    error => {
                        this.$store.dispatch("alertError", error, {root: true});
                        return Promise.resolve(false);
                    }
                )
            },
            closeCloud(){
                this.$modal.hide('cloud-upload-modal');
                /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
                this.$store.commit('home/updateCloudBoxFlg',false);
                /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
                window.location.reload();
            }
        },
        computed: {
            downloadRequestListData: function () {
                let data = [];
                this.listData.forEach((item) => {
                    /*if (item.circular_kind == 0) {
                        item.kind += '（'+ this.env[item.sender_env] + '）';
                    }*/
                    data.push(item);
                });
                return data;
            },
            selectedName() {
                return this.selectedFileName;
            },
            selectedId() {
                return this.selectedFileId;
            },
            isEmtyItemReading() {
                for (let i in this.itemReading) return false;
                return true
            },
            currentCloudDrive: {
                get() {
                    return this.$store.state.cloud.drive
                },
                set(value) {
                    this.$store.commit('cloud/setDrive', value);
                }
            },
            ...mapState({
                fileSelected: state => state.home.fileSelected,
            }),
        },
        watch: {
            'pagination.currentPage': function (val) {
                this.onSearch(false);
            },
            'reloadTime': function (t) {
                if (t > this.reloadInterval) {
                    this.reloadTime = 0;
                    this.onSearch(false);
                }
          },

          /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
          "$store.state.home.cloudBoxFlg":function (value){
              if(value){
                document.body.style.setProperty('overflow-y','hidden','important');
              }else{
                document.body.style.setProperty('overflow-y','auto','important');
              }
          },
          /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/

        },
        mounted() {
            this.onSearch(false);
            this.$ls.on('boxAccessToken', (value) => {
                if (value) {
                    this.downloadToCloud("box",this.downloadID);
                }
            });
            this.$ls.on('onedriveAccessToken', (value) => {
                if (value) {
                    this.downloadToCloud("onedrive",this.downloadID);
                }
            });
            this.$ls.on('googleAccessToken', (value) => {
                if (value) {
                    this.downloadToCloud("google",this.downloadID);
                }
            });
            this.$ls.on('dropboxAccessToken', (value) => {
                if (value) {
                    this.downloadToCloud("dropbox",this.downloadID);
                }
            });
            /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
            this.$store.commit('home/updateCloudBoxFlg',false);
            /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        },
        async created() {
            var company = await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                .then(response => {
                    return response.data ? response.data.data : [];
                })
                .catch(error => {
                    return [];
                });
            this.canStoreCircular = company && company.long_term_storage_flg;

            // リロード設定
            setInterval(() => {
                this.reloadTime++
            }, 1000);

            this.settingLimit = await this.getLimit();
            if (this.settingLimit == null) {
                this.settingLimit = {};
            }

            this.is_ipad = /(iPad)/i.test(navigator.userAgent);
            /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
            this.$store.commit('home/updateCloudBoxFlg',false);
            /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        },
      beforeDestroy() {
          /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
          this.$store.commit('home/updateCloudBoxFlg',false);
          /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
      },
    }

</script>

<style lang="stylus">
    .detail {
        .label {
            background: #b3e5fb;
            padding: 3px;
        }
        .info {
            padding: 3px 3px 3px 5px;
        }
    }
    @media only screen and (min-width: 901px) {
        .vs-lg-2 {
            width: 20% !important;
        };
    }

    #arrow {
        cursor: pointer;
    }

    .around {
        animation: 0.5s around_arrow;
        animation-fill-mode: forwards;
    }

    .around_return {
        animation: 0.5s around_arrow_return;
        animation-fill-mode: forwards;
    }

    @keyframes around_arrow {
        0% {
            transform: rotate(0);
        }
        100% {
            transform: rotate(180deg);
        }
    }

    @keyframes around_arrow_return {
        0% {
            transform: rotate(180deg);
        }
        100% {
            transform: rotate(0);
        }
    }

    .breadcrumb-container {
        background: rgba(0, 174, 241, .1411764705882353);
    }

    #cloudItems {
        min-height: 200px;
        max-height: 500px;
        overflow: auto;
    }

    .vs-list--slot {
        margin-left: 0;
        margin-right: auto;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .vs-list--slot img {
        margin-right: 1rem;
    }
</style>
<style lang="scss" scoped>
  div.v--modal-overlay {
    z-index: 52001;
  }
</style>
