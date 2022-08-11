<template>
    <div id="template-list-page">
        <vs-card style="margin-bottom: 0">
            <vs-row>
              <vs-col vs-type="flex" vs-lg="3" vs-sm="3" vs-xs="12" class="mb-3 pr-2">
                  <vs-row>
                    <vs-col class="mb-2" vs-type="flex" vs-w="12">
                      <label class="vs-input--label" for="file-name-search">明細Expテンプレート名</label>
                    </vs-col>
                    <vs-col vs-type="flex" vs-w="12">
                      <input id="file-name-search"
                             class="w-full input-search"
                             maxlength="256"
                             v-model="filter.file_name"/>
                    </vs-col>
                  </vs-row>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="3" vs-xs="12" class="mb-3 pl-2">
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
                <vs-col vs-type="flex" class="mb-3" vs-lg="4" vs-sm="3" vs-xs="12"></vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="2" vs-sm="3" vs-xs="12"
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
                         accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                         id="uploadFile" v-on:change="onUploadFile"/>
                  <label style="text-align: center; white-space: nowrap;width: 80%;overflow: hidden;text-overflow: ellipsis;" class="text-content-center" for="uploadFile"><strong class="font-size-common">{{fileNames}}</strong></label>
                </label>
              </div>
            </div>
            <vs-button class="square mb-2" color="success" @click="onAdd">
              <i class="far fa-file-alt"></i> 新規登録
            </vs-button>
          </vs-row>
            <vs-table class="mt-3" noDataText="データがありません。" :data="formIssuanceList" @sort="handleSort" stripe sst>
                <template slot="thead">
                    <vs-th style="width: 35%%;" sort-key="file_name">明細Expテンプレート名</vs-th>
                    <vs-th style="width: 35%%;" sort-key="remarks">備考</vs-th>
                    <vs-th style="width: 15%;" sort-key="update_user">最終更新者</vs-th>
                    <vs-th style="width: 15%;" sort-key="update_at">最終更新日時</vs-th>
                </template>

                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                        <td @click="showDetail(tr.id)" style="width: 35%;"><a href="javascript:void(0)" class="text-decoration-underline">
                          {{tr.file_name.length > 50 ? tr.file_name.substring(0, 50) + '...' : tr.file_name }}</a></td>
                        <td v-if="tr.remarks != null" style="width: 35%;">
                          {{ tr.remarks.length > 55 ?
                          tr.remarks.substring(0, 55) + '...' :
                          tr.remarks }}</td>
                        <td v-else style="width: 35%;"></td>
                        <td style="width: 15%;">{{tr.update_user}}</td>
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

        <vs-popup classContent="popup-example" title="明細Expテンプレートを削除します。" :active.sync="confirmDelete">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="12">この明細Expテンプレートを削除しますか。</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="明細Expテンプレートの登録" :active.sync="confirmAdd">
            <div>
                <vs-row>
                    <vs-col vs-type="flex" vs-w="4">明細Expテンプレート名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7" style="word-break: break-all;">{{ newItem.file_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="4">登録日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="7">{{ newItem.create_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                  <vs-col vs-type="block" vs-align="center">
                    <div style="display: flex; align-items: center;">
                      <span>備考</span>
                      <span style="font-size: 11px; color: gray;">（ 100文字まで ）</span>
                    </div>
                    <vs-input class="input w-full" v-model="newItem.remarks" :maxlength="100"/>
                  </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="12">この明細テンプレートを登録します</vs-col>
                </vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="onConfirmAdd" color="success">登録</vs-button>
                    <vs-button @click="confirmAdd=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="明細Expテンプレートの登録" :active.sync="noticeEmptyFileDialog">
            <div>※ファイルが選択されていません</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="noticeEmptyFileDialog=false" color="dark" type="border">OK</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>


        <vs-popup classContent="popup-example" class="show-detail" title="明細Expテンプレートの詳細" :active.sync="showItem">
            <div>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">明細Expテンプレート名</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4" class="text-decoration-underline font-color-link-primary" style="word-break: break-all;">
                      <a href="#" @click="downloadTemplate(item.id)">{{ item.file_name }}</a>
                    </vs-col>
                    <vs-col vs-w="2">
                        <vs-button  @click="confirmDelete=true" class="align-r" color="dark" type="border">削除</vs-button>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="3"
                            class="font-size-information padding-block-information">
                      明細Expテンプレートを削除します。
                    </vs-col>
                </vs-row>
             
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">備考</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4" class="text-content-in-block">{{ item.remarks }}</vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end"
                            vs-justify="flex-end" vs-w="5"></vs-col>
                </vs-row>
             
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">登録日時</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.create_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                    <vs-col vs-w="5"></vs-col>
                </vs-row>
                <vs-row class="mt-2" vs-align="center">
                    <vs-col class="w-20">登録者</vs-col>
                    <vs-col class="w-5">:</vs-col>
                    <vs-col vs-w="4">{{ item.create_user }}</vs-col>
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
                    <vs-col vs-w="4">{{ item.update_user }}</vs-col>
                </vs-row>
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
          remarks: "",
        },
        formIssuanceList: [],
        item: {},
        showItem: false,
        pagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
        orderBy: "",
        orderDir: "",
        confirmDelete: false,
        confirmAdd: false,
        noticeEmptyFileDialog: false,
        newItem: {
          file_name: '',
          create_at: new Date(),
          remarks: '',
          display_order: 0
        },
        tempFiles: []
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
        search: "formIssuance/getListExpTemplate",
        showFormIssuance: "formIssuance/showExpTemplate",
        uploadFiles: "formIssuance/uploadExpTemplate",
        deleteFormIssuance: "formIssuance/deleteExpTemplate",
        downloadFile: "formIssuance/getExpTemplate",
      }),
      onSearch: async function (resetPaging) {
        let queries = {
          file_name: this.filter.file_name,
          remarks: this.filter.remarks,
          page: resetPaging ? 1 : this.pagination.currentPage,
          limit: this.pagination.limit,
          orderBy: this.orderBy,
          orderDir: this.orderDir,
          action: resetPaging? 'searchExportTemplateList' : 'getExportTemplateList',
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
          if (data) {
              this.item = data.frm_template;
              this.showItem = true;
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
        if(!this.tempFiles.length) {
          this.noticeEmptyFileDialog = true;
        }else {
          this.newItem = {
            file_name: this.fileNames,
            create_at: new Date(),
            display_order: 0,
            remarks: ''
          };
          this.confirmAdd = true;
        }
      },
      onConfirmAdd: async function() {
        this.$store.dispatch('updateLoading', true);
        if(!this.tempFiles || !this.tempFiles.length) return;
        const file = this.tempFiles[0];
        const data = {
          file: file,
          display_order: this.newItem.display_order,
          remarks: this.newItem.remarks,
        }

        await this.uploadFiles(data);
       
        this.onSearch(false);
        document.getElementById('uploadFile').value = "";
        this.tempFiles = [];
        this.confirmAdd = false;
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
