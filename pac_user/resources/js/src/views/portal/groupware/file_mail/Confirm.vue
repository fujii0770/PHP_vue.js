<template>
  <div id="portal-file-mail-confirm">
      <top-menu></top-menu>

      <div style="display: flex;">
<!--      <div style="background-color: white; margin-left: -8px;" class="mr-2">-->
<!--        <menu-left></menu-left>-->
<!--      </div>-->

      <div class="pr-0" style="flex-grow: 1">
        <div>
          <vs-row class="mt-8" style="margin-bottom: 20px">
            <div class="vx-col w-100 pl-2">
              <span class="text-danger text-small">送信内容の各項目をチェックしてください</span>
            </div>
          </vs-row>

          <div class="row_confirm">
            <div class="left">宛先</div>
            <div class="right">
              <template v-for="(item, index) in selectMailUsers">
                <div class="right-item" :class="index==selectMailUsers.length-1?'':'no-border-bottom'" :key="index">
                  <div class="right-item-chk">
                    <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                  </div>
                  <div class="right-item-val">{{ item.email }}</div>
                </div>
              </template>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">件名</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-chk">
                  <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                </div>
                <div class="right-item-val">{{ title ? title : '&nbsp;' }}</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">メッセージ</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-chk">
                  <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                </div>
                <div class="right-item-val pre-text">{{ message ? message : '&nbsp;' }}</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">ファイル</div>
            <div class="right">
              <template v-for="(item, index) in mailFiles">
                <div class="right-item" :class="index==mailFiles.length-1?'':'no-border-bottom'" :key="index">
                  <div class="right-item-chk">
                    <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                  </div>
                  <div class="right-item-val">{{ item.file_name }}</div>
                </div>
              </template>
            </div>
          </div>
          <div class="row_confirm ">
            <div class="left">セキュリティコード</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-chk">
                  <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                </div>
                <div class="right-item-val">{{ accessCode }}</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left">ダウンロード可能期間</div>
            <div class="right">
              <div class="right-item">
                <div class="right-item-chk">
                  <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                </div>
                <div class="right-item-val">{{expire_day ? expire_day : 0}} 日 {{ expire }} 時間</div>
              </div>
            </div>
          </div>
          <div class="row_confirm">
            <div class="left has-border-bottom">ダウンロード可能回数</div>
            <div class="right">
              <div class="right-item has-border-bottom">
                <div class="right-item-chk">
                  <vs-checkbox class="check-box-mod" @click="onRowCheckboxClick()"/>
                </div>
                <div class="right-item-val">{{ count ? count : '無制限' }}</div>
              </div>
            </div>
          </div>

          <div style="margin-top: 15px">
            <vs-row>
              <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="6" vs-xs="12">
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#fff;" color="#22AD38" type="filled"
                           v-on:click="fileSend" :disabled="showPopupError"> 送信
                </vs-button>
              </vs-col>
              <vs-col vs-type="flex" vs-align="flex-end" vs-lg="6" vs-xs="12">
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#000;border:1px solid #dcdcdc;" color="#fff"
                           type="filled" v-on:click="returnApplication"> 戻る
                </vs-button>
              </vs-col>
            </vs-row>
          </div>
        </div>
        <vs-popup classContent="popup-example" title="メッセージ" :active.sync="showNoticeMessage">
          <vs-row>
            <p>送信ボタンを押してから10分間は相手に送信されません。</p>
          </vs-row>
          <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="refreshPage"> 閉じる</vs-button>
          </vs-row>
        </vs-popup>
      </div>
    </div>
  </div>
</template>

<script>

import {mapState, mapActions} from "vuex";
// import MenuLeft from "../../../../components/portal/MenuLeft";
import TopMenu from "../../../../components/portal/TopMenu";

export default {
  components: {
      TopMenu
  },
  data() {
    return {
      showPopupError: true,
      showNoticeMessage: false,
    }
  },
  computed: {
    ...mapState({
      selectMailUsers: state => state.fileMail.selectMailUsers, //宛先
      mailId: state => state.fileMail.mailId, //メールフID
      mailFiles: state => state.fileMail.mailFiles, //ファイルリスト
      title: state => state.fileMail.title, //件名
      message: state => state.fileMail.message, //メッセージ
      accessCode: state => state.fileMail.accessCode, //セキュリティコード
      count: state => state.fileMail.count, //ダウンロード最大回数
      expire_day: state => state.fileMail.expire_day, //ダウンロード有効期限 日
      expire: state => state.fileMail.expire, //ダウンロード有効期限 時
      addToContactsFlg: state => state.fileMail.addToContactsFlg, //アドレス帳に追加
    }),
  },
  methods: {
    ...mapActions({
      addLogOperation: "logOperation/addLog",
      mailFilesSend: "fileMail/mailFilesSend",
    }),
    //送信
    fileSend: async function () {

      let data = await this.mailFilesSend({
        id: this.mailId, emails: this.selectMailUsers, title: this.title, message: this.message, files: this.mailFiles,
        accessCode: this.accessCode,expire_day: this.expire_day, expire: this.expire, count: this.count, addToContactsFlg: this.addToContactsFlg,
      });
      let file_names = '';
      if(this.mailFiles){
        file_names = this.mailFiles.map(function(e){
          return e.file_name
        }).join(',');
      }
      let emails = '';
      if(this.selectMailUsers){
        emails = this.selectMailUsers.map(function(e){
          return e.email
        }).join(',');
      }
      if (data == true) {
        this.addLogOperation({action: 'mail-file-send-display', result: 0, params:{file_name: file_names, email: emails}});
        this.showNoticeMessage = true;
      }else{
        this.addLogOperation({action: 'mail-file-send-display', result: 1, params:{file_name: file_names, email: emails}});
      }
    },
    onRowCheckboxClick:function (){
      let items = document.getElementsByClassName("check-box-mod");
      for (var i = 0; i < items.length; i++) {
        if (items[i].children[0].checked == false) {
          this.showPopupError = true;
          return;
        }
      }
      this.showPopupError = false;
    },
    returnApplication: function () {
      this.$router.push('/groupware/file_mail/application?back=true');
    },
    refreshPage: function (){
      this.showNoticeMessage = false;
    },
  },
  async mounted() {

  },
  watch: {
    "showPopupError": function () {
      let items = document.getElementsByClassName("check-box-mod");

      for (var i = 0; i < items.length; i++) {
        if (items[i].children[0].checked == false) {
          this.showPopupError = true;
          return;
        }
      }
    },
    'showNoticeMessage': function () {
      if(!this.showNoticeMessage){
        let root = this;
        setTimeout(function () {
          root.$router.push('/groupware/file_mail/list');
        },500)
      }
    },
  },
  async created() {
    this.$nextTick(()=>{
      let popups = document.getElementsByClassName('vs-component con-vs-popup vs-popup-primary');
      for (let i = 0;i < popups.length;i ++){
        let div = document.createElement('div');
        div.style.width = '100%';
        div.style.height = '100%';
        div.style.position = 'fixed';
        div.style.left = 0;
        div.style.top = 0;
        div.style.zIndex = 50;
        popups[i].appendChild(div);
      }
    });
  }
}

</script>
<style lang="scss" scoped>
.iframe-groupware {
  height: calc(100vh - 87px);
}

table, table tr th, table tr td {
  width: 100%;
  border: 1px solid #999999;
  border-spacing: 0;
}

.div {
  border: 1px solid #999999;
  border-spacing: 0;
}

.row_confirm {
  margin: 0 auto;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
  width: 80vw;

  .left {
    width: 30vw;
    border-top: 1px solid #999;
    border-left: 1px solid #999;
  }

  .has-border-bottom {
    border-bottom: 1px solid #999;
  }

  .right {
    width: 50vw;

    .right-item {
      display: flex;
      align-items: center;
      justify-content: stretch;
      border-top: 1px solid #999;
      border-left: 1px solid #999;
      border-right: 1px solid #999;
      //height: 30px;

      .right-item-chk {
        width: 40px;
        //border-right: 1px solid #999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 3px;
        //height: 30px;
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
