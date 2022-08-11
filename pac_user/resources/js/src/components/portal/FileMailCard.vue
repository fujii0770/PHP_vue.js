<template>
  <div class="file-mail-portal">
    <vx-card class="mb-4">
      <HeaderComponent title="ファイルメール便" @hiddenAppPortal="$emit('hiddenAppPortal')" urlGroupware="/groupware/file_mail/application"></HeaderComponent>
      <div class="file-mail-card">
        <vs-button class="square" color="danger" v-on:click="deleteDialog = true" v-bind:disabled="this.selectIds.length < 1"><i class="far fa-trash-alt"></i> 削除
        </vs-button>
        <vs-row>
          <vs-card class="list-received">
            <vs-table class="mt-3 table-favorite-width"
                      :data="listDataReceived"
                      noDataText="データがありません。"
                      sst stripe>
              <template slot="thead">
                <vs-th class="tex-list-received pr-1"></vs-th>
                <vs-th class="tex-list-received pr-6">宛先</vs-th>
                <vs-th class="tex-list-received pr-6">件名</vs-th>
                <vs-th class="tex-list-received pr-6">ファイル名</vs-th>
                <vs-th class="tex-list-received pr-3">セキュリティコード</vs-th>
                <vs-th class="tex-list-received width-date">送信日時</vs-th>
                <vs-th class="tex-list-received width-date">期限</vs-th>
                <vs-th class="tex-list-received pr-3">残回数</vs-th>
                <vs-th class="tex-list-received pr-3"></vs-th>
              </template>

              <template slot-scope="{data}">
                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                  <vs-td>
                    <vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/>
                  </vs-td>
                  <td @click="onShowReading(tr)" class="tex-list-received pr-6" v-html="tr.receiver_email.replace(/,/g,'</br>')"></td>
                  <td @click="onShowReading(tr)" class="tex-list-received pr-6">{{ tr.title }}</td>
                  <td @click="onShowReading(tr)" class="tex-list-received pr-6" v-html="tr.file_names.replace(/,/g,'</br>')"></td>
                  <td @click="onShowReading(tr)" class="tex-list-received pr-3">{{ tr.access_code }}</td>
                  <td @click="onShowReading(tr)" class="tex-list-received width-date">{{ tr.send_date ? tr.send_date : '送信待ち' }}</td>
                  <td @click="onShowReading(tr)" class="tex-list-received width-date">{{ tr.expiration_date | moment("YYYY/MM/DD HH:mm") }}</td>
                  <td @click="onShowReading(tr)" class="tex-list-received pr-3">{{ tr.count === -1 ? '無制限' : tr.count }}</td>
                  <td @click="onShowReading(tr)" class="tex-list-received pr-3">{{ mail_state[tr.state] }}</td>
                </vs-tr>
              </template>
            </vs-table>
            <div>
              <div class="mt-3 mb-5">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div>
            </div>
            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
          </vs-card>
        </vs-row>
      </div>
    </vx-card>
    <vs-popup title="確認" :active.sync="deleteDialog">
      <div class="mb-0">{{ deleteCount }}件のファイルを削除します。
      </div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button @click="doDelete" color="danger">OK</vs-button>
        <vs-button @click="deleteDialog = false" color="dark" type="border">キャンセル</vs-button>
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
          <vs-button color="dark" type="border" v-on:click="fileSendAgain(showItem)">再送信</vs-button>
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
</template>

<script>
import {mapActions} from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import HeaderComponent from "./HeaderComponent";

export default {
  components: {
    VxPagination,
    HeaderComponent,
  },
  props: [],
  beforeCreate() {
  },
  created() {
  },
  beforeMount() {
  },
  mounted() {
    this.onSearch(false);
  },
  beforeUpdate() {
  },
  update() {
  },
  beforeDestroy() {
  },
  destroyed() {
  },

  data() {
    return {
      listDataReceived: [],
      pagination: {
        totalPage: 0,
        currentPage: 1,
        limit: 10,
        totalItem: 0,
        from: 1,
        to: 10
      },
      status: "",
      filename: "",
      userName: "",
      userEmail: "",
      orderBy: "update_at",
      orderDir: "desc",
      fromdate: "",
      todate: "",
      sender: "",
      mail_state: ['', '上限超え', '期限切れ'],
      checkboxSelected: [],
      selectIds: [],
      deleteDialog: false,
      deleteCount: 0,
      confirmShow: false,
      showItem:[],
      showAgainNoticeMessage: false,
    }
  },
  methods: {
    ...mapActions({
      getMailFileList: "fileMail/getMailFileList",
      deleteMailItem: "fileMail/deleteMailItem",
      getDiskMailItem: "fileMail/getDiskMailItem",
      downloadDiskMailItem: "fileMail/downloadDiskMailItem",
      mailFilesSendAgain: "fileMail/mailFilesSendAgain",
      addLogOperation: "logOperation/addLog",
    }),
    async onSearch(resetPaging) {
      let info = {
        status: this.status,
        filename: this.filename,
        userName: this.userName,
        userEmail: this.userEmail,
        fromdate: this.fromdate,
        todate: this.todate,
        sender: this.sender,
        page: resetPaging ? 1 : this.pagination.currentPage,
        limit: this.pagination.limit,
        orderBy: this.orderBy,
        orderDir: this.orderDir,
      };
      let data = await this.getMailFileList(info);
      this.num_unread = data.num_unread;
      // data                        = data.data;
      this.listDataReceived = data.data;
      this.pagination.totalItem = data.total;
      this.pagination.totalPage = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.limit = data.per_page;
      this.pagination.from = data.from;
      this.pagination.to = data.to;
    },
    onRowCheckboxClick(tr) {
      tr.selected = !tr.selected;
      let cids = [];
      let selectedItems = this.listDataReceived.filter(item => item.selected);
      selectedItems.forEach((item) => {
        cids.push(item.id)
      });
      this.selectIds = cids;
      this.deleteCount = cids.length;
    },
    doDelete: async function () {
      await this.deleteMailItem(this.selectIds);
      this.onSearch(true);
      this.deleteDialog = false;
      this.selectIds = [];
      this.deleteCount = 0;
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
  computed: {},
  watch: {
    'pagination.currentPage': function () {
      this.onSearch(false);
    },
    'showAgainNoticeMessage': function () {
      if(!this.showAgainNoticeMessage){
        this.onSearch(false);
      }
    },
  }
}
</script>

<style lang="scss" scoped>
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
