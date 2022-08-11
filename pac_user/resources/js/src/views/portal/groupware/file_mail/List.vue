<template>
  <div id="portal-file-mail-list">
      <top-menu></top-menu>

    <div style="display: flex;">
<!--      <div style="background-color: white; margin-left: -8px;" class="mr-2">-->
<!--        <menu-left></menu-left>-->
<!--      </div>-->

        <div class="pr-0" style="flex-grow: 1">
        <div>
          <div style="margin-bottom: 15px">
            <vs-row>
              <vs-col vs-type="flex" vs-align="flex-start" vs-justify="flex-start" vs-lg="6" vs-xs="12">
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled"
                           v-on:click="returnApplication"> 戻る
                </vs-button>
              </vs-col>
            </vs-row>
            <vs-table class="mt-3" style="border: 2px solid #999" :data="listData" noDataText="データがありません。" sst stripe>
              <template slot="thead">
                <vs-th>宛先</vs-th>
                <vs-th>件名</vs-th>
                <vs-th>ファイル名</vs-th>
                <vs-th>セキュリティコード</vs-th>
                <vs-th>送信日時</vs-th>
                <vs-th>ダウンロード期限</vs-th>
                <vs-th>残回数</vs-th>
                <vs-th></vs-th>
                <vs-th>削除</vs-th>
              </template>
              <template slot-scope="{data}">
                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                  <td @click="onShowReading(tr)" class="table-td" v-html="tr.receiver_email.replace(/,/g,'</br>')"></td>
                  <td @click="onShowReading(tr)" class="table-td">{{ tr.title }}</td>
                  <td @click="onShowReading(tr)" class="table-td" v-html="tr.file_names.replace(/,/g,'</br>')"></td>
                  <td @click="onShowReading(tr)" class="table-td">{{ tr.access_code }}</td>
                  <td @click="onShowReading(tr)">{{ tr.send_date ? tr.send_date : '送信待ち' }}</td>
                  <td @click="onShowReading(tr)">{{ tr.expiration_date | moment("YYYY/MM/DD HH:mm") }}</td>
                  <td @click="onShowReading(tr)">{{ tr.count === -1 ? '無制限' : tr.count }}</td>
                  <td @click="onShowReading(tr)">{{ mail_state[tr.state] }}</td>
                  <td>
                    <vs-button class="square" color="danger" @click="showDialogDelete(tr.id,tr.title,tr.file_names)"> 削除</vs-button>
                  </td>
                </vs-tr>
              </template>
            </vs-table>
            <div>
              <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div>
            </div>
            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
          </div>
        </div>
      </div>
      <vs-popup class="holamundo" title="メールの削除" :active.sync="confirmDelete">
        <div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="3">件名</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ this.selectedFileName }}</vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="12">このファイルを削除します。</vs-col>
          </vs-row>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
            <vs-button @click="onDelete(selectedId)" color="danger">削除</vs-button>
            <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>

      <vs-popup class="detail-popup" title="送信内容詳細" :active.sync="confirmShow" >
        <div style="flex-grow: 1">
          <div class="row_confirm">
            <div class="left">宛先</div>
            <div class="right">
              <template v-for="(item, index) in showItem.emails">
                <div class="right-item" :class="index==showItem.emails.length-1?'':'no-border-bottom'" :key="index">
                  <div class="right-item-val" v-model="item.email">{{ item }}</div>
                </div>
              </template>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">件名</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-val" v-model="showItem.title">{{ this.showItem.title ? this.showItem.title : '&nbsp;' }}</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">メッセージ</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-val pre-text" v-model="showItem.message">{{ this.showItem.message ? this.showItem.message : '&nbsp;' }}</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">ファイル</div>
            <div class="right">
              <template v-for="(item, index) in showItem.file_names">
                <div class="right-item" :class="index==showItem.file_names.length-1?'':'no-border-bottom'" :key="index">
                  <div class="right-item-val">{{ item.file_name }} <a href="#" v-if="showItem.canDownload" @click="onDownloadDiskMail(item.id)" style="color: black;margin-left: 6px;"><i class="fa fa-download" aria-hidden="true"></i></a></div>
                </div>
              </template>
            </div>
          </div>
          <div class="row_confirm ">
            <div class="left">セキュリティコード</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-val">{{ this.showItem.access_code }}</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">ダウンロード可能期間</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-val">{{this.showItem.expire_day ? this.showItem.expire_day : 0 }} 日 {{ this.showItem.expire }} 時間</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left has-border-bottom">ダウンロード可能回数</div>
            <div class="right">
              <div class="right-item has-border-bottom">
                <div class="right-item-val">{{ this.showItem.download_limit  === -1 ? '無制限' : this.showItem.download_limit}}</div>
              </div>
            </div>
          </div>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
            <vs-button color="primary" type="filled" v-on:click="fileSendAgain(showItem)">再送信</vs-button>
            <vs-button @click="confirmShow=false" color="dark" type="border">閉じる</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>
      <vs-popup classContent="popup-example" title="メッセージ" :active.sync="showAgainNoticeMessage">
        <vs-row>
          <p>送信ボタンを押してから10分間は相手に送信されません。</p>
        </vs-row>
        <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
          <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="refreshPage"> 閉じる</vs-button>
        </vs-row>
      </vs-popup>
    </div>
  </div>
</template>

<script>
import {mapActions} from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
// import MenuLeft from "../../../../components/portal/MenuLeft";
import TopMenu from "../../../../components/portal/TopMenu";

export default {
  components: {
    // MenuLeft,
    VxPagination,
    TopMenu
  },
  data() {
    return {
      listData: [], //一覧
      pagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
      confirmDelete: false,
      selectedFileName: '', //削除ポップアップファイル名
      fromPath: '',
      mail_state: ['', '上限超え', '期限切れ'],
      confirmShow: false,
      showItem:[],
      showAgainNoticeMessage: false,
    }
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      vm.fromPath = from.name;
    });
  },
  computed: {},
  methods: {
    ...mapActions({
      addLogOperation: "logOperation/addLog",
      getMailFileList: "fileMail/getMailFileList",
      deleteMailItem: "fileMail/deleteMailItem",
      getDiskMailItem: "fileMail/getDiskMailItem",
      downloadDiskMailItem: "fileMail/downloadDiskMailItem",
      mailFilesSendAgain: "fileMail/mailFilesSendAgain",
    }),
    returnApplication: function () {
      this.$router.push('/groupware/file_mail/application' + (this.fromPath == 'file_mail_application' ? '?back=true' : ''));
    },
    onSearch: async function (resetPaging) {
      this.selectAll = false;
      let info = {
        page: resetPaging ? 1 : this.pagination.currentPage,
        limit: this.pagination.limit,
      };
      var data = await this.getMailFileList(info);
      this.listData = data.data;
      this.pagination.totalItem = data.total;
      this.pagination.totalPage = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.limit = data.per_page;
      this.pagination.from = data.from;
      this.pagination.to = data.to;
    },
    showDialogDelete: function (id, title, file_names) {
      this.selectedFileId = id;
      this.selectedFileName = title ? title : file_names;
      this.confirmDelete = true;
    },
    onDelete: async function () {
      let items = [];
      items.push(this.selectedFileId);
      await this.deleteMailItem(items);
      this.onSearch(false);
      this.confirmDelete = false;
    },
    onShowReading: async function (tr) {
      this.showItem = await this.getDiskMailItem(tr.id);
      this.confirmShow = true;
    },
    onDownloadDiskMail: async function (disk_mail_id) {
      await this.downloadDiskMailItem(disk_mail_id)
    },
    //再送信
    fileSendAgain: async function (showItem) {
      let data = await this.mailFilesSendAgain(showItem);
      let file_names = '';
      if(showItem.file_names){
        file_names = showItem.file_names.map(function(e){
          return e.file_name
        }).join(',');
      }
      let emails = '';
      if(showItem.emails){
        emails = showItem.emails.map(function(e){
          return e
        }).join(',');
      }
      if (data == true) {
        this.addLogOperation({action: 'mail-file-send-display', result: 0, params:{file_name: file_names, email: emails}});
        this.confirmShow=false;
        this.showAgainNoticeMessage = true;
      }else{
        this.addLogOperation({action: 'mail-file-send-display', result: 1, params:{file_name: file_names, email: emails}});
      }
    },
    refreshPage: function (){
      this.showAgainNoticeMessage = false;
    },
  },
  mounted() {
    this.$forceUpdate()
  },
  watch: {
    'pagination.currentPage': function () {
      this.onSearch(false);
    },
    'showAgainNoticeMessage': function () {
      if(!this.showAgainNoticeMessage){
        this.onSearch(false);
      }
    },
  },
  async created() {
    this.onSearch(true);
  }
}

</script>
<style lang="scss" scoped>
.iframe-groupware {
  height: calc(100vh - 87px);
}

.table-td {
  max-width: 200px;
  word-wrap: break-word;
}
.row_confirm {
  margin: 0 auto;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;

  .left {
    width: 40%;
    border-top: 1px solid #999;
    border-left: 1px solid #999;
  }
  .has-border-bottom {
    border-bottom: 1px solid #999;
  }
  .right {
    width: 60%;

    .right-item {
      display: flex;
      align-items: center;
      justify-content: stretch;
      border-top: 1px solid #999;
      border-left: 1px solid #999;
      border-right: 1px solid #999;

      .right-item-chk {
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 3px;
      }

      .right-item-val {
        padding: 5px;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        border-left: 1px solid #999;
      }
      .pre-text{
        white-space: pre-wrap;
      }
    }
  }
}
</style>
