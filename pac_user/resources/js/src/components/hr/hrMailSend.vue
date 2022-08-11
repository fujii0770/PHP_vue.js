<template>
  <div>
    <vs-popup classContent="popup-example" title="ヒント" :active.sync="contactSettingFlg">
      <div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-w="12" >連絡先は未設定ですので、</vs-col>
          <vs-col vs-type="flex" vs-w="12" >設定ページに行って設定してください。</vs-col>
        </vs-row>
      </div>
      <vs-row class="mt-6">
        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
          <vs-button @click="goToMailSetting" color="primary">設定画面へ</vs-button>
          <vs-button @click="contactSettingFlg=false" color="dark" type="border">キャンセル</vs-button>
        </vs-col>
      </vs-row>
    </vs-popup>
    <vs-popup class="detail-popup" title="送信" :active.sync="hrMailSettingFlg">
      <div class="vx-col w-full mb-4">
        <span v-if="sendEmailMsg != ''" style="color:red">{{sendEmailMsg}}</span>
        <vs-row class="mt-1">
          <vs-col clsss="left"  style="width: 20%">
            宛先選択
          </vs-col>
          <vs-col clsss="right" style="width: 80%;">
            <table class="table-border">
              <tr>
                <td class="td-border"><vs-checkbox class="check-box-mod" :value="AllCheckFlg" @click="onCheckboxAllClick()"/></td>
                <td class="td-border"><div class="right-item-val">氏名</div></td>
              </tr>
              <template v-for="(item, index) in emailList">
                <tr>
                  <td class="td-border"><vs-checkbox class="check-box-mod" :value="item.selected" @click="onRowCheckboxClick(item)"/></td>
                  <td class="td-border"><div class="right-item-val">{{ item.name + '(' + item.email + ')' }}</div></td>
                </tr>
              </template>
            </table>
          </vs-col>
        </vs-row>
        <vs-row class="mt-3">
          <vs-col clsss="left"  style="width: 20%">
            文面選択
          </vs-col>
          <vs-col clsss="right" style="width: 25%;">
            <vs-select class="selectText w-full" v-model="text_index" style="width: 40%" @change="onChangeText(text_index+1)">
              <vs-select-item :key="index" :value="index" :text="item" v-for="(item,index) in textList" />
            </vs-select>
          </vs-col>
        </vs-row>
        <vs-row class="mt-3">
          <vs-col clsss="left"  style="width: 20%">
            文面
          </vs-col>
          <vs-col clsss="right" style="width: 70%;">
            <vs-textarea class="mail-content" placeholder="" rows="10" v-model="text" />
          </vs-col>
        </vs-row>
        <vs-row class="mt-3">
          <vs-col clsss="left"  style="width: 20%">
            署名
          </vs-col>
          <vs-col clsss="right" style="width: 80%;">
            <label for="w_signature1">
              <input type="radio" id="w_signature1" name="w_signature" :value="1"
                     v-model="signatureFlg">
              使用する
            </label>
            <label for="w_signature0" style="padding-left: 10%;">
              <input type="radio" id="w_signature0" name="w_signature" :value="0"
                     v-model="signatureFlg">
              使用しない
            </label>
          </vs-col>
        </vs-row>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-w="12" vs-align="center" vs-justify="center">
            <vs-button @click="mailLayoutSelect" color="primary">送信</vs-button>
            <vs-button @click="hrMailSettingFlg=false" color="dark" type="border">キャンセル</vs-button>
          </vs-col>
        </vs-row>
      </div>
    </vs-popup>
    <vs-popup class="detail-popup" title="送信詳細" :active.sync="hrMailLayoutSelectFlg">
      <table class="mt-3 table-border" style="width: 100%;">
        <th class="td-border">項目</th>
        <th class="td-border">内容</th>
        <tr>
          <td class="td-border">宛先</td>
          <td class="td-border">{{mailInfo.emails}}</td>
        </tr>
        <tr>
          <td class="td-border">件名</td>
          <td class="td-border">[Shachihata Cloud] 勤怠連絡</td>
        </tr>
        <tr>
          <td class="td-border">本文</td>
          <td class="td-border" style="white-space: pre-wrap;">{{mailText}}</td>
        </tr>
      </table>
      <vs-row class="mt-3">
        <vs-col vs-type="flex" vs-w="12" vs-align="center" vs-justify="center">
          <vs-button @click="mailSend" color="primary">送信</vs-button>
          <vs-button @click="goBackHrMailSetting" color="dark" type="border">キャンセル</vs-button>
        </vs-col>
      </vs-row>
    </vs-popup>
  </div>
</template>

<script>
import {mapActions} from "vuex";

export default {
  props: {
    opened: {type: Boolean, default: false},
  },
  data() {
    return {
      contactSettingFlg: false,
      hrMailSetting: {},
      emailList: [],
      hrMailSettingFlg: false,
      textList: ['文面1','文面2','文面3'],
      text_index: 0,
      text: '',
      signatureFlg: '',
      mailInfo: {},
      hrMailLayoutSelectFlg: false,
      sendEmailMsg:'',
      mailText: '',
      AllCheckFlg: false,
    }
  },
  methods: {
    ...mapActions({
      searchHrMailSetting: "hr/getHrMailSetting",
      hrMailSend: "hr/hrMailSend",
    }),
    async contactMail(){
      this.$emit('changeSelectMailFlg');
      this.text_index= 0;
      this.signatureFlg= '';
      this.emailList.forEach(email_info => {
        email_info.selected = false;
      });
      if(this.emailList.length == 0){
        this.contactSettingFlg = true
      }else{
        this.AllCheckFlg = false;
        this.text = this.hrMailSetting['text_1'];
        this.hrMailSettingFlg = true
      }
    },
    goBackHrMailSetting(){
      this.hrMailLayoutSelectFlg = false;
      this.hrMailSettingFlg = true;
    },
    //送信
    mailSend: async function () {
      this.hrMailLayoutSelectFlg = false;
      await this.hrMailSend(this.mailInfo);
    },
    goToMailSetting() {
      this.contactSettingFlg = false;
      let root = this;
      setTimeout(function () {
        root.$router.push('/hr/mail_setting');
      },500)
    },
    onCheckboxAllClick: function () {
      this.AllCheckFlg = !this.AllCheckFlg;
      this.emailList.forEach(email_info => {
        email_info.selected = this.AllCheckFlg;
      });
    },
    onRowCheckboxClick: function (item) {
      item.selected = !item.selected
    },
    onChangeText: function (index) {
      this.text = this.hrMailSetting['text_'+index];
    },
    mailLayoutSelect(){
      this.sendEmailMsg = '';
      let sendEmail = [];
      this.emailList.forEach(email_info => {
        if(email_info.selected == true){
          sendEmail.push(email_info.email);
        }
      });
      if(sendEmail.length == 0){
        this.sendEmailMsg = '宛先を選択してください';
      }else if(this.text.length == 0){
        this.sendEmailMsg = '文面を入力してください';
      }else if(this.text.length > 300){
        this.sendEmailMsg = '文面は300文字以下で入力してください';
      }else if(this.signatureFlg === ''){
        this.sendEmailMsg = '署名の使用状況を選択してください';
      }else{
        this.hrMailSettingFlg = false;
        this.mailInfo = {
          emails: sendEmail.toString(),
          text:this.text,
          signature:this.signatureFlg ? this.hrMailSetting['signature']:''
        }
        this.mailText = '[送信者]:'+ this.hrMailSetting.name + "\n" +
            '[送信者メールアドレス]:'+ this.hrMailSetting.email + "\n\n" +this.mailInfo.text + "\n\n" +this.mailInfo.signature;
        this.hrMailLayoutSelectFlg = true;
      }
    },
    async created() {
    },
  },
  watch: {
    opened: async function(val) {
      if(val) {
        this.hrMailSetting = await this.searchHrMailSetting();
        this.emailList = [];
        if(this.hrMailSetting){
          for(let index = 1; index<= 5; index++){
            if(this.hrMailSetting['mail_address_'+index]){
              let obj = {};
              obj['id'] = index;
              obj['email'] = this.hrMailSetting['mail_address_'+index];
              obj['name'] = this.hrMailSetting['name_'+index];
              obj['selected'] = false;
              this.emailList.push(obj);
            }
          }
        }
        await this.contactMail();
      }
    },
  },
  name: "hrMailSend"
}
</script>

<style scoped>
.left{
  width: 20%
}
.right{
  width: 80%
}
.table-border{
  border:1px solid #999;
  border-collapse: collapse;

}
.td-border{
  border: 1px solid #999;
  padding: 3px;
}
</style>