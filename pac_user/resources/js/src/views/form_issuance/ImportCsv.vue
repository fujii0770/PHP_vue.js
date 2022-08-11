<template>
	<div id="main-home" class="form-issuance-page">
		<div style="margin-bottom: 15px">
        <vs-row class="mb-3">
            <vs-col vs-w="2" vs-align="center" vs-type="flex" vs-justify="center">
                <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-out-container"><vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out"><i class="fas fa-minus"></i> </vs-button></div></vs-col>
                <vs-col vs-w="6" vs-justify="center" vs-align="center"><div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{zoom}}%</label></div></vs-col>
                <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-in-container"><vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in"><i class="fas fa-plus"></i> </vs-button></div></vs-col>
            </vs-col>
            <vs-col vs-w="10" vs-align="flex-end" vs-justify="flex-end" vs-type="flex">
              <vs-button @click="onBack" id="back-text-btn" style="color:#000;border:1px solid #dcdcdc;padding: .75rem 2rem !important;" color="white" type="filled" >戻る</vs-button>
            </vs-col>
        </vs-row>
		</div>
        <vs-card class="work-content template">
            <vs-row>
                <vs-col vs-type="flex" vs-w="8">
                    <div class="pdf-content template" ref="pdfViewer" style="position: relative;">
                        <vs-col vs-type="flex" vs-w="12" vs-align="flex-start" vs-justify="flex-start">
                            <vs-navbar v-model="tabSelected"
                                color="#fff"
                                active-text-color="rgb(9,132,227)"
                                class="filesNav">

                                <vs-navbar-item v-for="(file, index) in filesLessThanMaxTabShow " v-bind:key="index" :index="index" class="document">
                                    <template v-if="index < maxTabShow">
                                       <a v-tooltip.top-center="file.file_name" href="#">
                                           {{file.file_name}}
                                        </a>
                                    </template>
                                </vs-navbar-item>

                                <vs-spacer></vs-spacer>
                                <vs-navbar-item class="more-document" v-if="files.length > maxTabShow">
                                    <vs-dropdown >
                                        <a class="a-icon" href="#" :style="(tabSelected > (maxTabShow -1) ? 'color:#0984e3':'')">
                                            <i class="fas fa-ellipsis-h" style="font-size: 20px"></i>
                                            <vs-icon class="" icon="expand_more"></vs-icon>
                                        </a>
                                        <vs-dropdown-menu>
                                            <vs-dropdown-item v-for="(file, index) in filesMoreThanMaxTabShow " v-bind:key="index" :index="index" :class="'more-document-item '">
                                                <p class="filename" v-tooltip.left-start="file.file_name" :style="'white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin-right: 50px;font-size: 14px;max-width: 185px;min-height:25px' + (index === tabSelected ? 'color:#0984e3':'')">{{file.file_name}}</p>
                                            </vs-dropdown-item>
                                        </vs-dropdown-menu>
                                    </vs-dropdown>
                                </vs-navbar-item>
                            </vs-navbar>
                        </vs-col>
                        <div id="pdfContent" ref="pageWrap" :style="(fileSelected == null ? 'display:none':'')" class="content vs-con-loading__container" v-on:scroll="onHandleScroll">
                            <div id="pageWrap">
                                <div ref="page" v-for="(item, index) in fileImage" class="page page_large" v-bind:key="index" :index="index">
                                    <!-- <img :src="require('@assets/images/sampleTemplate.jpg')" alt="a4" style="width: 100%"> -->
                                    <img :src="'data:image/png;base64,'+item" alt="a4" style="width: 100%">
                                </div>
                            </div>
                        </div>
                    </div>
                </vs-col>
                <vs-col vs-type="flex" vs-w="4" style="transition: width .2s;">
                    <div id="fields" class="tools fields py-2 px-4 vs-con-loading__container">
                        <vs-row class="mt-4" vs-type="flex" vs-align="flex-end" vs-justify="center">
                            <div class="upload-wrapper md:w-10/12 mr-2 mb-2">
                                <div class="vx-col w-full upload-box" id="dropZoneLarge" @drop="handleFileSelect"
                                    @dragleave="handleDragLeave" @dragover="handleDragOver">
                                    <label class="wrapper" for="uploadFile">
                                        <input type="file" ref="uploadFile"
                                            accept=".csv"
                                            id="uploadFile" v-on:change="onUploadFile"/>
                                        <label style="text-align: center; white-space: nowrap;width: 80%;overflow: hidden;text-overflow: ellipsis;" class="text-content-center" for="uploadFile"><strong class="font-size-common">{{fileNames}}</strong></label>
                                    </label>
                                </div>
                            </div>
                        </vs-row>
                        <vs-row vs-type="flex" vs-align="flex-end" vs-justify="flex-end">
                            <span>最大ファイルサイズ 2MByte</span>
                        </vs-row>
                        <div class="footer">
                            <vs-button :disabled="this.importBtn" @click="onImport" id="import-text-btn" color="primary" type="filled">インポート</vs-button>
                        </div>
                    </div>
                </vs-col>
            </vs-row>
        </vs-card>
        <vs-popup classContent="popup-example" title="明細インポートの登録" :active.sync="noticeEmptyFileDialog">
            <div>※ファイルが選択されていません</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="noticeEmptyFileDialog=false" color="dark" type="border">OK</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
        <vs-popup classContent="popup-example" title="明細インポート" :active.sync="importStateDialog">
            <div v-if="csvFormImport">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="4">明細テンプレート名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">
                        <span class="long-name-restyle">{{ this.fileSelected.file_name }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="4">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">
                        {{ csvFormImport.update_at ? csvFormImport.update_at : csvFormImport.create_at | moment("YYYY/MM/DD HH:mm") }}
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col class="long-name-break-down">{{ csvFormImport.imp_filename }} のインポート</vs-col>
                </vs-row>
                <vs-row>
                    <vs-col vs-type="flex" vs-w="6">1. データファイルのアップロード</vs-col>
                    <vs-col vs-type="flex" vs-w="1">...</vs-col>
                    <vs-col vs-type="flex" vs-w="5">完了</vs-col>
                </vs-row>
                <vs-row>
                    <vs-col vs-type="flex" vs-w="6">2. アップロードデータの検証</vs-col>
                    <vs-col vs-type="flex" vs-w="1">...</vs-col>
                    <vs-col v-if="csvFormImport.imp_status === 0" vs-type="flex" vs-w="5">待機中</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === 1" vs-type="flex" vs-w="5">処理中</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === 2 || csvFormImport.imp_status == 5" vs-type="flex" vs-w="5">完了</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -1" vs-type="flex" vs-w="5">取消</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -11" vs-type="flex" vs-w="5">中断</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -12" vs-type="flex" vs-w="5">完了</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -21" vs-type="flex" vs-w="5">エラー</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -22" vs-type="flex" vs-w="5">完了</vs-col>
                </vs-row>
                <vs-row>
                    <vs-col vs-type="flex" vs-w="6">3. 明細データの作成</vs-col>
                    <vs-col vs-type="flex" vs-w="1">...</vs-col>
                    <vs-col v-if="csvFormImport.imp_status === 2" vs-type="flex" vs-w="5">
                        処理中（{{csvFormImport.registered_rows}}/{{csvFormImport.imp_rows}}件)
                    </vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === 5" vs-type="flex" vs-w="5">完了</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -12" vs-type="flex" vs-w="5">中断</vs-col>
                    <vs-col v-else-if="csvFormImport.imp_status === -22" vs-type="flex" vs-w="5">エラーあり</vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status === -1" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"><span style="color:red;">インポート処理が取り消されました。実行ログをご確認ください。</span></vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status === -11" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"><span style="color:red;">インポートを中断しました。実行ログをご確認ください。</span></vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status === -12" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"><span style="color:red;">インポートを中断しました。実行ログをご確認ください。</span></vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status === -21" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"><span style="color:red;">インポートに失敗しました。実行ログをご確認ください。</span></vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status === -22" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"><span style="color:red;">インポートに失敗しました。実行ログをご確認ください。</span></vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status === -99" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"><span style="color:red;">インポート処理が異常終了しました。実行ログをご確認の上、システム管理者またはサポートまでご連絡ください。</span></vs-col>
                </vs-row>
                <vs-row class="mt-5">
                    <vs-col vs-type="flex" vs-align="center" vs-justify="center" vs-w="5">
                        <vs-button v-if="csvFormImport.imp_status < 0 || csvFormImport.imp_status === 5" @click="downloadTemplateLog()"
                                   style="background-color: gray !important;" type="filled">実行ログの取得
                        </vs-button>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="center" vs-justify="center" vs-w="4">
                        <vs-button v-if="csvFormImport.imp_status === 5" @click="moveToFormList()" color="success" type="filled">明細一覧</vs-button>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="center" vs-justify="center" vs-w="3">
                        <vs-button @click="importStateDialog=false; $router.go()" color="dark" type="border">閉じる</vs-button>
                    </vs-col>
                </vs-row>
                <vs-row v-if="csvFormImport.imp_status >= 0 && csvFormImport.imp_status < 5" class="mt-3">
                    <vs-col vs-type="flex" vs-w="12">※ この画面を閉じても処理は実行されます。処理結果は明細テンプレートの一覧から詳細画面を表示してご確認ください。</vs-col>
                </vs-row>
            </div>
        </vs-popup>
	</div>
</template>
<script>
    import { mapState, mapActions } from "vuex";
    import InfiniteLoading from 'vue-infinite-loading';
    import 'flatpickr/dist/flatpickr.min.css';
    import {Japanese} from 'flatpickr/dist/l10n/ja.js';
    import flatPickr from 'vue-flatpickr-component';

    function setZoom(zoom,el, a4Scale = 1) {
        if(!el) {
          return;
        }
        el.style["width"] = (zoom * a4Scale) + '%';
        el.style["margin"] = '0 auto';
    }

    export default {
        components: {
            InfiniteLoading,
            flatPickr,
        },
        directives: {
        },
        data() {
            return {
                pages: [],
                tempFiles: [],
                noticeEmptyFileDialog: false,
                importStateDialog: false,
                importBtn: false,
                currentPageNo: 1,
                maxTabShow: 4,
                tabSelected: 0,
                oldZoom: 100,
                zoom: 100,
                oldTabSelected: null,
                oldDisplayHeight: 0,
                fileImage: [],
                startPage:0,
                totalPage: 0,
                isScrollHandling:false,
                templateSetting: null,
                placeholderData: [],
                configDate: {
                  locale: Japanese,
                  wrap: true,
                  defaultHour: 0
                },
                dateRegex: /^(((明治|明|M|大正|大|T|昭和|昭|S|平成|平|H|令和|令|R|西暦|')\s*(\d{1,2}|元))|(\d{4}|元))\s*[ .\-/年]\s*(((01|03|05|07|08|10|12)\s*[ .\-/月]\s*(0[1-9]|[12][0-9]|3[01]))|((04|06|09|11)\s*[ .\-/月]\s*(0[1-9]|[12][0-9]|30))|((02)\s*[ .\-/月]\s*(0[1-9]|[12][0-9])))\s*日?$/,
                numberRegex: /^\d{1,3}(([,，])?(\d{3})){0,3}$/,
                dataCols: {'reference_date_col': {'type': 'date'},
                            'customer_name_col': {'type': 'string', 'max':1000},
                            'customer_code_col': {'type': 'string', 'max':1000},
                            'trading_date_col': {'type': 'date'},
                            'invoice_no_col': {'type': 'string', 'max':1000},
                            'invoice_date_col': {'type': 'date'},
                            'invoice_amt_col': {'type': 'number', 'max':12},
                            'payment_date_col': {'type': 'date'}},
                csvFormImport: null,
                statusUpload: null
            }
        },
        computed: {
            ...mapState({
                files: state => state.formIssuance.files,
                fileSelected: state => state.formIssuance.fileSelected,
            }),
            fileNames() {
                if(!this.tempFiles || !this.tempFiles.length) return 'ファイル選択';
                return this.tempFiles.map(item => item.name).join(', ');
            },
            filesLessThanMaxTabShow () {
                return this.files.filter((file ,index) => index < this.maxTabShow)
            },
            filesMoreThanMaxTabShow () {
                return this.files.filter((file ,index) => index > this.maxTabShow - 1)
            },

        },
        methods: {
            ...mapActions({
                selectFile: "formIssuance/selectFile",
                convertExcelToImage: "formIssuance/convertExcelToImage",
                addLogOperation: "logOperation/addLog",
                uploadCSVImport: "formIssuance/uploadCSVImport",
                getCSVFormImportUploadStatus: "formIssuance/getCSVFormImportUploadStatus",
                downloadLogTemplateCSV: "formIssuance/getLogTemplateCSV",
                getFormIssuancesIndex: "formIssuance/getFormIssuancesIndex",
            }),
            onZoomOutClick: function () {
                this.zoom = parseInt(this.zoom);
                if(this.zoom > 0) {
                    this.zoom-=10;
                }
                if(this.zoom < 50) {
                    this.zoom = 50;
                }
            },
            onZoomInClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom+= 10;
                if(this.zoom > 200) {
                    this.zoom = 200;
                }
            },
            onHandleScroll: async function (e) {
                if(!this.files  || this.files.length <= 0 || this.startPage == 0) {
                  return;
                }
                //10px is padding of page
                const scrollHeight = e.target.scrollHeight - this.totalPage * 10 - 20;
                //calc page size in browser
                let pageSize = scrollHeight / this.totalPage;
                //scale if zoom less than 100 %
                if(this.zoom < 100) {
                  pageSize = pageSize * (this.zoom / 100);
                }

                if((e.target.clientHeight + e.target.scrollTop) >=  e.target.scrollHeight) {
                    if (!this.isScrollHandling){
                        this.isScrollHandling = true;
                        this.$store.dispatch('updateLoading', true);
                        if (this.startPage >0) {
                            let data = {
                                templateId: this.files[0].id,
                                storageFileName: this.files[0].storage_file_name,
                                page: this.startPage
                            };
                            let pageContent = await this.convertExcelToImage(data);
                            this.startPage =  pageContent.startPage;

                            this.fileImage = this.fileImage.concat(pageContent.arrImage)
                        }
                        this.$store.dispatch('updateLoading', false);
                        this.isScrollHandling = false;
                    }
                }
            },

            onCloseDocumentClick: function(file, index) {
              this.$modal.show('delete-doc-modal');
              this.oldTabSelected = this.tabSelected;
            },
            beforeClose: function (event) {
              if(!event.params || !event.params.close) event.stop();
            },
            onBack: async function() {
                this.$router.push('/form-issuance');
            },
            onImport: async function() {
                this.importBtn = true;
                if(!this.tempFiles.length) {
                    this.noticeEmptyFileDialog = true;
                    this.importBtn = false;
                }else {
                    const file = this.tempFiles[0];
                    const data = {
                        file: file,
                        frm_template_id: this.fileSelected.id,
                        frm_template_version: this.fileSelected.version
                    }
                    const csvId = await this.uploadCSVImport(data);
                    if(csvId){
                        const param = {
                            templateId: this.fileSelected.id,
                            csvId: csvId
                        };
                        let root = this;
                        this.csvFormImport = await this.getCSVFormImportUploadStatus(param);
                        this.importStateDialog = true;

                        if (this.csvFormImport && this.csvFormImport.imp_status !== 5 && this.csvFormImport.imp_status >= 0) {
                            this.statusUpload = setInterval(async () => {
                                try {
                                    root.csvFormImport = await root.getCSVFormImportUploadStatus(param);
                                    if (!this.csvFormImport || root.csvFormImport.imp_status === 5 || root.csvFormImport.imp_status < 0) {
                                        clearInterval(this.statusUpload)
                                    }
                                } catch (e) {
                                    clearInterval(this.statusUpload)
                                }
                            }, 5000);
                        } else {
                            clearInterval(this.statusUpload);
                        }
                    } else {
                        this.importBtn = false;
                        this.tempFiles = []
                    }
                }
            },
            async downloadTemplateLog() {
                console.log('this.csvFormImport')
                console.log(this.csvFormImport)
                let params = {
                    templateId: this.csvFormImport.frm_template_id,
                    logId: this.csvFormImport.id,
                    action: 'formImportDownloadCSV'
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
            },
            moveToFormList: function() {
                this.importStateDialog = false;
                this.$nextTick(() => {
                    this.$router.push('/form-issuance/form-list');
                })
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
            },
        },

        watch: {
            "zoom": function (newVal,oldVal) {
              newVal = parseInt(newVal);
              if(newVal) setZoom(newVal, document.getElementById('pageWrap'), this.maxScale);

            },
            "importStateDialog": function (newVal, oldVal) {
                if (this.csvFormImport && this.csvFormImport.imp_status >= 0 && this.csvFormImport.imp_status !== 5 && !newVal) {
                    this.$router.go();
                }
            },
        },
        async mounted() {

        },
        async created() {
            if(this.files && this.files.length) {
              this.addLogOperation({action: 'frm6-15-form-issuance-import-display', result: 0});
                this.$store.dispatch('updateLoading', true);
                let data = {
                    templateId: this.files[0].id,
                    storageFileName: this.files[0].storage_file_name,
                    page: 0
                };
                let pageContent = await this.convertExcelToImage(data);
                this.$store.dispatch('updateLoading', false);
                if (pageContent){
                  this.startPage =  pageContent.startPage;
                  this.fileImage = this.fileImage.concat(pageContent.arrImage);
                  this.totalPage = pageContent.totalPagesLoaded;
                }
                this.selectFile(this.files[0]);
                const frmIndex = await this.getFormIssuancesIndex();
                const fields = Object.values(frmIndex);
                for (const field of fields) {
                  if(field.data_type == 0){
                    this.dataCols['frm_index'+field.frm_index_number+'_col'] = {'type': 'number', 'max':12};
                  }else if(field.data_type == 1){
                    this.dataCols['frm_index'+field.frm_index_number+'_col'] = {'type': 'string', 'max':1000};
                  }else{
                    this.dataCols['frm_index'+field.frm_index_number+'_col'] = {'type': 'date'};
                  }
                }
            } else {
              this.addLogOperation({action: 'frm6-15-form-issuance-import-display', result: 1});
            }
        },
        destroyed() {
            clearInterval(this.statusUpload);
        },
    }
</script>

<style lang="scss">
 .upload-wrapper {
    height: calc(100% - 50px);
    .upload-box {
      border-radius: 10px;
      border: 3px dashed #D1ECFF;
      label.wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        padding: 10px 20px;
        align-items: center;
      }
      label[for="uploadFile"],label[for="uploadFileLarge"] {
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
.long-name-restyle {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.long-name-break-down {
    max-width: 100%;
    white-space: pre-wrap;
    word-break: break-word;
    padding-bottom: 10px;
}
.font-size-common {
    font-size: 14px;
}
.text-content-center {
    margin: auto;
}
#back-text-btn .vs-button--text {
    font-size: 14px !important;
}
#import-text-btn .vs-button--text {
    font-size: 14px !important;
}
</style>
