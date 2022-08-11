<template>
    <div id="template-list-page">
        <vs-card style="margin-bottom: 0">
            <vs-row>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="4" vs-xs="12" class="mb-3">
                    <vs-input class="inputx w-full" label="文書名" v-model="filter.file_name" @change="onSearch(true)"/>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="9" vs-sm="8" vs-xs="12"
                        class="mb-3">
                    <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i>
                        検索
                    </vs-button>
                </vs-col>
            </vs-row>
        </vs-card>

        <vs-card>
            <vs-row vs-type="flex" vs-align="center">
                <vs-button class="square mb-2" color="primary"
                           v-bind:disabled="selected.length == 0" @click="applyClick"><i class="fas fa-pencil-alt"></i> 申請
                </vs-button>
                  <label for="uploadFile">
                      <input type="file" ref="uploadFile"
                              accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                              id="uploadFile" v-on:change="onUploadFile" style="display:none;"/>
                      <label for="uploadFile">
                        <vs-button class="square mb-2" color="success" @click="btnclick">
                            <i class="far fa-file-alt"></i> 新規登録
                        </vs-button>
                      </label>
                  </label>

                <vs-button class="square mb-2" color="danger" v-on:click="confirmDelete=true"
                           v-bind:disabled="selected.length == 0">
                    <i class="far fa-trash-alt"></i> 削除
                </vs-button>
            </vs-row>

            <vs-table class="mt-3" noDataText="データがありません。" :data="listTemplate" @sort="handleSort" stripe sst>
                <template slot="thead">
                    <vs-th class="width-50">
                        <vs-checkbox :value="selectAll" @click="onSelectAll"/>
                    </vs-th>
                    <vs-th sort-key="file_name">文書名</vs-th>
                    <vs-th sort-key="template_create_at">登録日時</vs-th>
                    <vs-th sort-key="template_update_user">最終更新者</vs-th>
                    <vs-th sort-key="template_update_at">最終更新日時</vs-th>
                    <vs-th sort-key="template_route" v-if="template_approval_route_flg">承認ルート</vs-th>
                </template>

                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                        <vs-td>
                            <vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/>
                        </vs-td>
                        <td>{{tr.file_name}}</td>
                        <td>{{tr.template_create_at | moment("YYYY/MM/DD HH:mm")}}</td>
                        <td>{{tr.template_update_user}}</td>
                        <td>{{tr.template_update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                        <td v-if="template_approval_route_flg">
                            <vs-button class="square" color="primary" v-on:click="onViewTemplateRoute(indextr,tr.template_route_id)">設定</vs-button>
                        </td>
                    </vs-tr>
                </template>
            </vs-table>
            <div>
                <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示
                </div>
            </div>
            <vs-pagination v-if="pagination.totalItem" :total="pagination.totalPage" v-model="pagination.currentPage"></vs-pagination>
        </vs-card>

        <vs-popup classContent="popup-example" title="テンプレートファイルの削除" :active.sync="confirmDelete">
            <div v-if="selected.length>1">{{ selected.length }}件の保存文書を削除します。</div>
            <div v-if="selected.length==1">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].file_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" v-if="selected[0].template_update_at">{{ selected[0].template_update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                    <vs-col vs-type="flex" vs-w="8" v-else>{{ selected[0].template_create_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="12">このテンプレートファイルを削除します。</vs-col>
                </vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="テンプレートファイルの申請" :active.sync="confirmEdit">
          <div v-if="selected.length>1">※複数ファイル選択されています。<br/>
            ※申請する際には、1つのファイルを選択してください。</div>
          <div v-if="template_approval_route_flg">
              <div v-if="selected.length == 1 && isNoneTemplateRoute">※承認ルートは無効にされました。</div>
              <div v-if="selected.length == 1 && !isNoneTemplateRoute && isNoneTemplateRouteUser">※承認ルートに無効な回覧先があります。</div>
          </div>
          <div v-if="selected.length==1 && ((template_approval_route_flg && !isNoneTemplateRoute && !isNoneTemplateRouteUser) || !template_approval_route_flg) ">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].file_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" v-if="selected[0].template_update_at">{{ selected[0].template_update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                    <vs-col vs-type="flex" vs-w="8" v-else>{{ selected[0].template_create_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="12"></vs-col>
                </vs-row>
                <vs-row class="mt-3">
                  <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <div v-if="template_edit_checkbox">
                      <input type="checkbox" v-model="template_edit_flg">
                      <label for=""><b style="color: red;">テンプレート編集機能を有効にする</b></label>
                    </div>
                  </vs-col>
                </vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button v-if="selected.length==1 && ((template_approval_route_flg && !isNoneTemplateRoute && !isNoneTemplateRouteUser) || !template_approval_route_flg)" @click="onEdit" color="success">申請</vs-button>
                    <vs-button @click="confirmEdit=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="テンプレートファイルの登録" :active.sync="confirmAdd">
            <div>
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ newItem.file_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">登録日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ newItem.template_create_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">公開範囲</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-align="center" vs-w="8">
                        <label for="option1" class="mr-2">
                            <input type="radio" id="option1" value="0" name="document_access_flg" v-model="newItem.document_access_flg">
                            社内
                        </label>
                        <label for="option2" class="mr-2">
                            <input type="radio" id="option2" value="1" name="document_access_flg" v-model="newItem.document_access_flg">
                            部署
                        </label>
                        <label for="option3" class="mr-2">
                            <input type="radio" id="option3" value="2" name="document_access_flg" v-model="newItem.document_access_flg">
                            個人
                        </label>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="12">このテンプレートファイルを登録します。</vs-col>
                </vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="onConfirmAdd" color="success">登録</vs-button>
                    <vs-button @click="confirmAdd=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example" title="テンプレートファイルの登録" :active.sync="noticeEmptyFileDialog">
            <div>※ファイルが選択されていません</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="noticeEmptyFileDialog=false" color="dark" type="border">OK</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

      <vs-popup class="application-page-dialog" title="設定承認ルート" :active.sync="templateViewRoute" :key="templateData.id">
        <ViewTemplateRoute :opened="templateViewRoute" :routeId="templateRouteId" :templateData="templateData" :key="templateData.id"/>
      </vs-popup>
    </div>
</template>


<script>
  import {mapState, mapActions} from "vuex";
  import InfiniteLoading from 'vue-infinite-loading';

  import flatPickr from 'vue-flatpickr-component'
  import 'flatpickr/dist/flatpickr.min.css';
  import {Japanese} from 'flatpickr/dist/l10n/ja.js';
    import Axios from "axios";
  import config from "../../app.config";
  import ViewTemplateRoute from "../../components/v-template-route/ViewTemplateRoute";

  export default {
    components: {
      InfiniteLoading,
      flatPickr,
      ViewTemplateRoute,
    },
    data() {
      return {
        filter: {
          file_name: "",
        },
        selectAll: false,
        listTemplate: [],
        pagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
        orderBy: "",
        orderDir: "",
        configDate: {
          locale: Japanese,
          wrap: true,
          defaultHour: 0
        },
        confirmDelete: false,
        confirmEdit: false,
        confirmAdd: false,
        noticeEmptyFileDialog: false,
        saveItem: null,
        newItem: {
          file_name: '',
          template_create_at: new Date(),
          document_access_flg: 0
        },
        tempFiles: [],
        template_edit_flg: false,
        template_approval_route_flg:false,
        template_edit_checkbox: false,
        templateViewRoute:false,
        templateRouteId:null,
        templateData:{},
        isNoneTemplateRoute :true,
        isNoneTemplateRouteUser:true,
        selectTemplate: {},
      }
    },
    computed: {
      selected() {
        return this.listTemplate.filter(item => item.selected);
      },
      fileNames() {
        if(!this.tempFiles || !this.tempFiles.length) return 'ファイル選択';
        return this.tempFiles.map(item => item.name).join(', ');
      }
    },
    methods: {
      ...mapActions({
        search: "template/getTemplates",
        postActionMultiple: "template/postActionMultiple",
        uploadFiles: "template/uploadFiles",
        setFiles: "template/setFiles",
        deletes: "template/deletes",
        searchTemplateRoute: "template/getTemplateRoute",
      }),
      onSearch: async function (resetPaging) {
        this.selectAll = false;
        let queries = {
          file_name: this.filter.file_name,
          page: resetPaging ? 1 : this.pagination.currentPage,
          limit: this.pagination.limit,
          orderBy: this.orderBy,
          orderDir: this.orderDir,
        };
        const data = await this.search(queries);
        this.listTemplate = data.data.map(item => {
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
      onSelectAll() {
        this.selectAll = !this.selectAll;
        this.listTemplate.map(item => {
          item.selected = this.selectAll;
          return item
        });
      },
      setTemplateFlg() {
        this.template_edit_flg = !this.template_edit_flg;
      },
      onDelete: async function () {
        await this.deletes({ids: this.selected.map(item => item.id)});
        this.confirmDelete = false;
        this.onSearch(false);
      },
      handleSort(key, active) {
        this.orderBy = key;
        this.orderDir = active ? "DESC" : "ASC";
        this.onSearch(false);
      },
      onEdit: function () {
        this.confirmEdit = false;
        if(this.template_edit_flg){
          this.$store.commit('template/setCirularTemplateEditFlg', true);
        }else{
          this.$store.commit('template/setCirularTemplateEditFlg', false);
        }
        setTimeout(() => {
          this.setFiles(this.selected);
          this.$router.push('/template/update');
        }, 300);

      },
      onAdd: function() {
        if(!this.tempFiles.length) {
          this.noticeEmptyFileDialog = true;
        }
        else {
          this.newItem = {
            file_name: this.fileNames,
            template_create_at: new Date(),
            document_access_flg: 0
          };
          this.confirmAdd = true;
        }
      },
      onConfirmAdd: async function() {
        if(!this.tempFiles || !this.tempFiles.length) return;
        this.confirmAdd = false;
        const file = this.tempFiles[0];
        const data = {
          file: file,
          document_access_flg: this.newItem.document_access_flg
        }
        await this.uploadFiles(data);
        this.onSearch(false);
        document.getElementById('uploadFile').value = "";
        this.tempFiles = [];
      },
      onRowCheckboxClick: function (tr) {
        tr.selected = !tr.selected
        this.selectAll = this.listTemplate.every(item => item.selected);
        this.selectTemplate = this.listTemplate.filter(item => {
          return item.selected;
        });
        if(this.selectTemplate && this.selectTemplate.length == 1) {
          if(!this.selectTemplate[0].template_route_id){
            this.isNoneTemplateRoute = false;
            this.isNoneTemplateRouteUser = false;
          }
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
        if(!this.tempFiles.length) {
          this.noticeEmptyFileDialog = true;
        }
        else {
          this.newItem = {
            file_name: this.fileNames,
            template_create_at: new Date(),
            document_access_flg: 0
          };
          this.confirmAdd = true;
        }
      },
      btnclick() {
        document.getElementById('uploadFile').value = "";
        this.$refs.uploadFile.click();
      },
      async onViewTemplateRoute(index,route_id){
        this.templateRouteId = route_id;
        this.templateData = this.listTemplate[index];
        this.templateViewRoute = true;
      },
      async applyClick(){
        this.confirmEdit = true;
        if(this.template_approval_route_flg && this.selectTemplate && this.selectTemplate.length == 1 && this.selectTemplate[0].template_route_id) {
          let dataConfirm = {
            templateId: this.selectTemplate[0].id,
            templateRouteId: this.selectTemplate[0].template_route_id,
          }
          const result = await this.searchTemplateRoute(dataConfirm);
          if(result){
            this.isNoneTemplateRoute = !result[0];
            this.isNoneTemplateRouteUser = !result[1];
          }
        }
      }
    },
    watch: {
      'pagination.currentPage': function (val) {
        this.onSearch(false);
      },
      templateViewRoute:function(val) {
        if (!val) {
          this.onSearch();
        }
      },
    },
    mounted() {
      this.onSearch(false);
      },
    async created() {
      this.$store.commit('template/setCirularTemplateEditFlg', false);
      this.$store.commit('template/setCircular', null);
      this.$store.commit('template/setTemplateEditFlg', false);
      this.$store.commit('home/setTemplateFlg', true );
      Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
              .then(response => {
                if(response.data.data.template_edit_flg) {
                  this.template_edit_checkbox = true;
                }
                if(response.data.data.template_flg && response.data.data.template_approval_route_flg && response.data.data.template_route_flg) {
                  this.template_approval_route_flg = true;
                }
              })
              .catch(error => {
                  return [];
              });
    }
  }

</script>
