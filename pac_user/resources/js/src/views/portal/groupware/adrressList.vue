<template>
  <div id="adrress_list">
    <top-menu ref="topMenu"></top-menu>
    <vs-card class="list-content">
        <ContactTree ref="tree" :treeData="treeData" :opened="loadSuccess" :number="'2'" :mode="'1'" @onNodeClick="showEditorDialog"/>
    </vs-card>
    <vs-popup class="list-content setting-dialog" title="利用者名簿" :active.sync="confirmEdit" style="height: 100%">
     
        <vs-card  v-if="confirmEdit" >
          <vs-row style="padding-top: 0">
              <vs-row  vs-type="center" vs-align="center" vs-justify="center" style="padding: 0">
                  <div class="con-upload">
                      <div class="con-img-upload image_profile_in_db" style="margin: 0;padding: 0">
                          <div class="img-upload" style="margin: 0;padding: 0">
                              <img :src="showInfo.user_profile_data || require('@assets/images/pages/portal/person-strong.svg')" width="150"  height="150"/>
                          </div>
                      </div>
                  </div>
              </vs-row>
            <vs-row vs-w="12">
              <vs-col vs-w="4">
                <span class="label-setting">
                  メールアドレス
                </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.email||'-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              ユーザ名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{(showInfo.family_name||"") + ' ' +(showInfo.given_name||"")}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              会社名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.company_name || '-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              部署名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.department_name||'-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              役職名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.position_name||'-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              郵便番号
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.postal_code}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              住所
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.address}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              電話番号（外線）
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.phone_number}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              電話番号（内線）
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.phone_number_extension}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              電話番号（携帯）
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.phone_number_mobile}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="label-setting">
              FAX番号
            </span>
              </vs-col>
              <vs-col vs-w="8">{{showInfo.fax_number}}</vs-col>
            </vs-row>
            <vs-row vs-justify="flex-end">
              <vs-button color="rgba(0,0,0,.12)" text-color="#000000" type="border" @click="confirmEdit=false">キャンセル</vs-button>
            </vs-row>
          </vs-row>
         
        </vs-card>
    </vs-popup>


         
  </div>
</template>

<script>
import TopMenu from "../../../components/portal/TopMenu";
import ContactTree from '../../../components/contacts/ContactTree';
import { mapState, mapActions } from "vuex";
import config from "../../../app.config";
import Axios from "axios";
import _ from "lodash";
import VsUpload from '@/components/vs-upload/vsUpload.vue';
import {userService} from "../../../services/user.service";
export default {
  name: "AdrressList",
  components: {
      TopMenu,
      VsUpload,
      ContactTree},
  computed:{
      ...mapState({
          companyUsers: state => state.application.companyUsers,
      }),
    loginUser: {
      get() {return JSON.parse(getLS('user'));}
    }
  },
  data() {
    return {
      showInfo: {},
      confirmEdit:false,
        loadSuccess:false,
      headers: {
        "Authorization": "Bearer " + sessionStorage.getItem("token")
      },
        treeData: [],
    }
  },
  methods: {
    ...mapActions({
      addLogOperation: "logOperation/addLog",
      getDepartmentUsersWithOption: "application/getDepartmentUsersWithOption",
    }),
      async showEditorDialog(nodeUserId){
        this.confirmEdit=true
        this.showInfo = await Axios.get(`${config.BASE_API_URL}/getUserInfoById/${nodeUserId}`)
            .then(response => {
                return response.data ? response.data.data : [];
            })
            .catch(error => {
                return [];
            });
        if(this.showInfo.user_profile_data){
            this.showInfo.user_profile_data = 'data:image/jpeg;base64,'+this.showInfo.user_profile_data
        }

    },
      async getEmailTrees(){
          const mapfunc = (item) => {
              let newItem = {};
              if(!item) return null;
              if(!Object.prototype.hasOwnProperty.call(item, "parent_id")) {
                  newItem = {text: item.family_name + ' ' + item.given_name, data: item, isDepartment: false};
                  return newItem;
              }else {
                  let children = [];
                  if(Object.prototype.hasOwnProperty.call(item, "users")) {
                      if (item.users)
                          children.push(...item.users.map(mapfunc));
                  }
                  if(Object.prototype.hasOwnProperty.call(item, "children")) {
                      if(item.children)
                          children.push(...item.children.map(mapfunc));
                  }
                  newItem.text =  item.name;
                  newItem.children =  children;
                  newItem.data =  {isGroup: true};
                  return newItem;
              }

          };
          let departments = [];
          if(this.$store.state.application.departmentUsers) departments = this.$store.state.application.departmentUsers.map(mapfunc);

          return departments;
      },
  },
  async mounted() {

  },
  async created() {
      await this.getDepartmentUsersWithOption({filter: '',option: '1'});
  },
  watch:{
      "treeData": function(newVal, oldVal) {
          this.treeLoaded++;
      },
      "$store.state.application.loadDepartmentUsersSuccess": async function (newVal, oldVal) {
          this.treeData = await this.getEmailTrees();
          this.loadSuccess=true;
      },
  }
}
</script>

<style lang="scss">
.list-content {
  height: calc(100vh - 190px);
  padding-left: 30px;

  .con-vs-card {
    box-shadow: none;
    margin-bottom: 0;
    .vs-card--content{
      margin-bottom: 0;
    }
  }

  .vs-row {
    padding: 8px 0;
  }

  .label-setting {
    font-weight: 700;
    color: #000;
    font-size: 1rem
  }
}
.con-vs-popup.setting-dialog .vs-popup {
  width: 700px!important;
}
</style>