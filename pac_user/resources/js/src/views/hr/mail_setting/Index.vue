<template>
    <div id="daily-report">
        <vs-row class="top-bar">
            <vs-col  vs-type="flex" vs-align="flex-end" vs-justify="flex-end">
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="border:1px solid #dcdcdc;margin:10px" color="primary" type="filled" v-show="!mailSettingFlg" @click="onSaveHrMailSetting()">登録</vs-button>
                <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="border:1px solid #dcdcdc;margin:10px" color="primary" type="filled" v-show="mailSettingFlg" @click="onSaveHrMailSetting()">更新</vs-button>
            </vs-col>
        </vs-row>
        <vx-card>
            <vs-row class="mt-1">
                <span class="vs-input--label" style="font-size: 13px;">連絡先</span>
            </vs-row>
            <vs-table class="mt-3 table-favorite-width"
                      :data="emailList"
                      noDataText="データがありません。"
                      sst stripe>
                <template slot="thead">
                    <vs-th class=" pr-6">メールアドレス</vs-th>
                    <vs-th class=" pr-6">氏名</vs-th>
                    <vs-th class=" pr-1"></vs-th>
              </template>

              <template  slot-scope="{data}">
                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                  <td class=" pr-6">{{tr.email}}</td>
                  <td class=" pr-6">{{tr.name}}</td>
                  <td class=" pr-1" style="text-align:center">
                    <vs-button class="square" color="danger"
                               @click="deleteContact(indextr, tr.email)">
                      削除
                    </vs-button>
                  </td>
                </vs-tr>
              </template>
            </vs-table>
            <vs-row class="mb-4 mt-4">
              <vs-col class="w-1/2 pr-2">
                <vs-input class="inputx" type="email" placeholder="メールアドレス" required name="email" v-model="addContactEmail"/>
                <span class="text-danger text-sm" v-show="editContactEmailMsg">{{ editContactEmailMsg }}</span>
              </vs-col>
              <vs-col class="w-1/2 pr-2">
                <vs-input class="inputx" type="name" placeholder="氏名" name="name" v-model="addContactName"/>
                <span class="text-danger text-sm" v-show="editContactNameMsg">{{ editContactNameMsg }}</span>
              </vs-col>
            </vs-row>
            <vs-row class="mb-4 mt-4" vs-align="flex-end" vs-justify="flex-end">
                <vs-button class="square mr-0"  color="primary" type="filled" @click="addNewContact" > 追加</vs-button>
            </vs-row>
        </vx-card>
        <vx-card class="mt-8">
            <div class="m-3 border-cell p-5">
                <vs-row>
                    <span class="vs-input--label">文面登録１</span>
                </vs-row>
                <vs-row class="block-mail-content">
                    <vs-textarea class="mail-content" placeholder="" rows="10" v-model="text_1" />
                </vs-row>

                <vs-row>
                    <span class="vs-input--label">文面登録２</span>
                </vs-row>
                <vs-row class="block-mail-content">
                    <vs-textarea class="mail-content" placeholder="" rows="10" v-model="text_2" />
                </vs-row>

                <vs-row>
                    <span class="vs-input--label">文面登録３</span>
                </vs-row>
                <vs-row class="block-mail-content">
                    <vs-textarea class="mail-content" placeholder="" rows="10" v-model="text_3" />
                </vs-row>

                <vs-row>
                    <span class="vs-input--label">署名</span>
                </vs-row>
                <vs-row class="block-mail-content">
                    <vs-textarea class="mail-content" placeholder="" rows="10" v-model="signature" />
                </vs-row>
            </div>
        </vx-card>
        <vs-popup classContent="popup-example" title="連絡先削除" :active.sync="confirmDelete">
          <div>
            <vs-row class="mt-3">
              <vs-col vs-type="flex" vs-w="3">メールアドレス</vs-col>
              <vs-col vs-type="flex" vs-w="1">:</vs-col>
              <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ this.selectedEmail }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
              <vs-col vs-type="flex" vs-w="12">この連絡先を削除します。</vs-col>
            </vs-row>
          </div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
              <vs-button @click="onDelete(selectedId)" color="danger">削除</vs-button>
              <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxCard from '../../../components/vx-card/VxCard.vue';

export default  {
    components: {
    },
    name: "hrMailSetting",
    props: [],

    data () {
        return {
            emailList:[],
            hrMailSetting:{},
            confirmDelete: false,
            selectedMailIndex: '',
            selectedEmail: '',
            addContactEmail:'',
            addContactName:'',
            editContactEmailMsg:'',
            editContactNameMsg:'',
            text_1:'',
            text_2:'',
            text_3:'',
            signature:'',
            mailSettingFlg: false,
        }
    },
    mounted() {
    },
    async created () {
      this.hrMailSetting = await this.searchHrMailSetting();
      if(this.hrMailSetting){
        this.mailSettingFlg = true;
        for(let index = 1; index<= 5; index++){
          if(this.hrMailSetting['mail_address_'+index]){
            this.emailList.push({email:this.hrMailSetting['mail_address_'+index] ,name:this.hrMailSetting['name_'+index]})
          }
        }
        this.text_1 = this.hrMailSetting['text_1'];
        this.text_2 = this.hrMailSetting['text_2'];
        this.text_3 = this.hrMailSetting['text_3'];
        this.signature = this.hrMailSetting['signature'];
      }
    },

    computed: {
      selectedId() {
        return this.selectedMailIndex;
      },
    },
    methods: {
      ...mapActions({
        searchHrMailSetting: "hr/getHrMailSetting",
        updateHrMailSetting: "hr/updateHrMailSetting",
      }),
      deleteContact(id,mail) {
        this.selectedMailIndex = id;
        this.selectedEmail = mail;
        this.confirmDelete = true;
      },
      onDelete: async function (index) {
        this.confirmDelete = false;
        this.emailList.splice(index, 1);
      },
      async addNewContact() {
        let editContactEmailFlg = this.onCheckContactEmail();
        let editContactNameFlg = this.onCheckContactName();
        if(!editContactEmailFlg || !editContactNameFlg){
          return ;
        }else if(this.emailList.length >= 5){
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `連絡先は最大5件まで。`,
            accept: () => {}
          });
          return ;
        }else{
          this.emailList.push({email:this.addContactEmail ,name:this.addContactName})
          this.addContactEmail = '';
          this.addContactName = '';
        }
      },
      onSaveHrMailSetting: async function() {
        if(this.emailList.length == 0){
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `連絡先は最小1件まで。`,
            accept: () => {}
          });
          return;
        }else if(this.text_1.length > 300){
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: '文面1は300文字以下で入力してください。',
            accept: () => {}
          });
          return;
        }else if(this.text_2.length > 300){
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: '文面2は300文字以下で入力してください。',
            accept: () => {}
          });
          return;
        }else if(this.text_3.length > 300){
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: '文面3は300文字以下で入力してください。',
            accept: () => {}
          });
          return;
        }else if(this.signature.length > 300){
            this.$vs.dialog({
              type: 'alert',
              color: 'danger',
              title: `確認`,
              acceptText: '閉じる',
              text: `署名は300文字以下で入力してください。`,
              accept: () => {}
            });
            return;
        }
        let newMailSetting = {
          emailList : this.emailList,
          text1     : this.text_1,
          text2     : this.text_2,
          text3     : this.text_3,
          signature : this.signature,
        };
        let data = await this.updateHrMailSetting(newMailSetting);
        if(data){
          this.mailSettingFlg = true;
        }
      },
      onCheckContactEmail: function() {
        if(this.addContactEmail === '') {
          this.editContactEmailMsg = '必須項目です';
          return false;
        }else if(this.addContactEmail.length > 256){
          this.editContactEmailMsg = '256文字以上は入力できません。';
          return false;
        }else{
          const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
          if(this.addContactEmail.match(mailPattern) === null) {
            this.editContactEmailMsg = 'メールアドレスが正しくありません';
            return false;
          }else{
            let emailCheckFlg = false;
            this.emailList.forEach(emaiInfo => {
              if(emaiInfo['email'] == this.addContactEmail){
                this.editContactEmailMsg = '連絡先メールアドレスは既に存在します。';
                emailCheckFlg = true;
              }
            });
            if(emailCheckFlg){
              return false;
            }else{
              this.editContactEmailMsg = '';
              return true;
            }
          }
        }
      },
      onCheckContactName: function() {
        if(this.addContactName === '') {
          this.editContactNameMsg = '必須項目です';
          return false;
        }else if(this.addContactName.length > 128){
          this.editContactNameMsg = '128文字以上は入力できません。';
          return false;
        }else{
          this.editContactNameMsg = '';
          return true;
        }
      },

    },
    watch: {
       
    },
}
</script>

<style scoped>
.border-cell {
    border: 1px solid #cdcdcd;
}
.mail-content {
    width: 90%;
}
.block-mail-content {
    margin-top: 1em;
}

</style>
