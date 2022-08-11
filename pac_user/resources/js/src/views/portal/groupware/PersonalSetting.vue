<template>
  <div id="personal-setting">
    <top-menu ref="topMenu"></top-menu>
    <vs-card class="setting-content">
      <vs-row>
        <vs-row>
          <vs-icon size="150px" v-if="!this.user_profile_data">personal</vs-icon>
          <img  width="150"  height="150" :src="'data:image/jpeg;base64,'+this.user_profile_data" v-if="this.user_profile_data" />
          <vs-col vs-type="flex" vs-justify="flex-start" vs-align="center" vs-w="6" style="margin-left: 8px">
            <vs-card>
              <div slot="header">
                <h3 style="font-size: 2.15rem">
                  {{(loginUser.family_name||"") + ' ' +(loginUser.given_name||"")}}
                </h3>
              </div>
              <div class="">
                <vs-button color="#e3f2fd" text-color="rgb(0, 0, 0)" @click="showEditorDialog">ユーザ情報を編集する</vs-button>
              </div>
            </vs-card>
          </vs-col>
        </vs-row>
        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              メールアドレス
            </span>
          </vs-col>
          <vs-col vs-w="9">{{loginUser.email||'-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              ユーザ名
            </span>
          </vs-col>
          <vs-col vs-w="9">{{(loginUser.family_name||"") + ' ' +(loginUser.given_name||"")}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              会社名
            </span>
          </vs-col>
          <vs-col vs-w="9">{{loginUser.mst_company_name || '-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              部署名
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.mst_department||'-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              役職名
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.mst_position||'-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              郵便番号
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.postal_code||'-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              住所
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.address || '-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              電話番号（外線）
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.phone_number || '-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              電話番号（内線）
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.phone_number_extension || '-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              電話番号（携帯）
            </span>
          </vs-col>
          <vs-col vs-w="9">{{ info.phone_number_mobile || '-'}}</vs-col>
        </vs-row>

        <vs-row vs-w="12">
          <vs-col vs-w="3">
            <span class="setting-label">
              FAX番号
            </span>
          </vs-col>
          <vs-col vs-w="9">{{info.fax_number || '-'}}</vs-col>
        </vs-row>
      </vs-row>
    </vs-card>
    <vs-popup class="setting-content setting-dialog" title="ユーザ情報編集" :active.sync="confirmEdit" style="height: 100%">
     
        <vs-card  v-if="confirmEdit" >
          <vs-row style="padding-top: 0">
            <vs-row style="padding-top: 0">
              <vs-row  vs-type="center" vs-align="center" vs-justify="center" style="padding: 0">
                <div class="con-upload">
                  <div class="con-img-upload image_profile_in_db" style="margin: 0;padding: 0">
                    <div class="img-upload" style="margin: 0;padding: 0">
                      <button type="button" class="btn-x-file close_img_profile" @click="closeExistedImage">
                        <i translate="translate"
                           class="material-icons notranslate">
                          clear
                        </i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="centerx vs-upload-container">
                  <vs-upload text="クリックして画像を選択してください"
                             accept="image/*"
                             limit="1"
                             ref="upload"
                             :action="uploadUrl"
                             :headers="headers"
                             fileName='image'
                             id="primaryImageUploadId"
                             @on-success="successUpload"
                             @on-error="errorUpload"
                             @on-delete="deleteUpload"
                             :showUploadButton="false"
                  />
                </div>
              </vs-row>
            </vs-row>
            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              メールアドレス
            </span>
              </vs-col>
              <vs-col vs-w="8">{{loginUser.email||'-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              ユーザ名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{(loginUser.family_name||"") + ' ' +(loginUser.given_name||"")}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              会社名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{loginUser.mst_company_name || '-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              部署名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{info.mst_department||'-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              役職名
            </span>
              </vs-col>
              <vs-col vs-w="8">{{info.mst_position||'-'}}</vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              郵便番号
            </span>
              </vs-col>
              <vs-col vs-w="8"><vs-input class="inputx" placeholder="" maxlength="10"  v-model="editInfo.postal_code" /></vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              住所
            </span>
              </vs-col>
              <vs-col vs-w="8"><vs-input class="inputx" placeholder="" maxlength="256"  v-model="editInfo.address" /></vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              電話番号（外線）
            </span>
              </vs-col>
              <vs-col vs-w="8"><vs-input class="inputx" placeholder="" maxlength="15"  v-model="editInfo.phone_number" /></vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              電話番号（内線）
            </span>
              </vs-col>
              <vs-col vs-w="8"><vs-input class="inputx" placeholder="ハイフンまたは半角数字" maxlength="15"  v-model="editInfo.phone_number_extension" /></vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              電話番号（携帯）
            </span>
              </vs-col>
              <vs-col vs-w="8"><vs-input placeholder="例: 09012345678 (ハイフン有りでも可)" maxlength="15" v-model="editInfo.phone_number_mobile" /></vs-col>
            </vs-row>

            <vs-row vs-w="12">
              <vs-col vs-w="4">
            <span class="setting-label">
              FAX番号
            </span>
              </vs-col>
              <vs-col vs-w="8"><vs-input placeholder="" maxlength="15" v-model="editInfo.fax_number" /></vs-col>
            </vs-row>
            <vs-row vs-justify="flex-end">
              <vs-button color="success" type="filled" @click="updateInfo">更新</vs-button>
              <vs-button color="rgba(0,0,0,.12)" text-color="#000000" type="border" @click="confirmEdit=false">キャンセル</vs-button>
            </vs-row>
          </vs-row>
         
        </vs-card>
    </vs-popup>
         
  </div>
</template>

<script>
import TopMenu from "../../../components/portal/TopMenu";
import {mapActions} from "vuex";
import config from "../../../app.config";
import Axios from "axios";
import _ from "lodash";
import VsUpload from '@/components/vs-upload/vsUpload.vue';
import {userService} from "../../../services/user.service";
export default {
  name: "PersonalSetting",
  components: {TopMenu,VsUpload},
  computed:{
    loginUser: {
      get() {return JSON.parse(getLS('user'));}
    }
  },
  data() {
    return {
      info: {},
      user_profile_data:null,
      confirmEdit:false,
      showProfileImageRegister:false,
      uploadUrl: config.LOCAL_API_URL + "/uploadUserImage",
      headers: {
        "Authorization": "Bearer " + sessionStorage.getItem("token")
      },
      editInfo:{}
    }
  },
  methods: {
    ...mapActions({
      getMyInfo: 'user/getMyInfo',
      getAvatarUser:'user/getAvatarUser',
      updateMyInfo:'user/updateMyInfo',
      deleteImageProfile: "user/deleteImageProfile",
      addLogOperation: "logOperation/addLog",
    }),
    closeExistedImage() {
      $('.image_profile_in_db > .img-upload').addClass('removeItem');
      $('.image_profile_in_db').hide();
      $('.vs-upload-container').removeClass('hidden');
      $('#uploadImageProfile').show();
    },
    showEditorDialog(){
      this.confirmEdit=true
      this.editInfo =_.clone(this.info)
      this.$nextTick(()=>{
        let avatar=this.user_profile_data
        if (avatar) {
          $('.vs-upload-container').addClass('hidden');
          let img_dom = document.createElement("IMG");
          img_dom.src = `data:image/jpeg;base64,${avatar}`;
          $(img_dom).addClass('style_img_profile');
          $('.image_profile_in_db .img-upload').append(img_dom);
          $('#uploadImageProfile').hide();
        } else {
          $('.image_profile_in_db').hide();
        }
      })
    },
    async updateInfo() {
      let files = this.$refs.upload.$data.filesx.filter(file => {
        return !file.hasOwnProperty('remove') && file.remove != true
      })
      if (files.length > 0) {
        let formData = new FormData();
        formData.append('image', files[0])
        await Axios.post(this.uploadUrl, formData)
      } else if ($('.image_profile_in_db .img-upload.removeItem').length > 0) {
        let data = {image: ''};
        await this.deleteImageProfile(data);
        this.addLogOperation({action: 'r06-06-setting-update-profile-image', result: 0});
      }
      this.textImg = 'プロファイル画像';
      // update Avatar when no image uploaded
      await userService.updateMyInfo(this.editInfo).then(
          response => {
            this.$store.dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {
            let msg= ""
            if (Array.isArray(error) && error.length>0){
              msg = error[0]
            }else{
              msg =error
            }
            this.$store.dispatch("alertError", msg, { root: true });
            return Promise.reject(false);
          })
      this.$refs.topMenu.updateAvatar()
      await this.updatePersonInfo()
    },
    async successUpload () {
      this.addLogOperation({action: 'r06-06-setting-update-profile-image', result: 0});
    },

    errorUpload(event) {
      this.addLogOperation({action: 'r06-06-setting-update-profile-image', result: 1});
    },

    deleteUpload() {
    },
    async updatePersonInfo() {
      this.$vs.loading({
        type: 'sound'
      })
      this.info = await this.getMyInfo()
      this.user_profile_data = await this.getAvatarUser().then(res => res.user_profile_data)
      this.$vs.loading.close()
    }
  },
  async mounted() {
    this.updatePersonInfo()
  },
  async created() {
    
  },
  watch:{
    
  }
}
</script>

<style lang="scss">
.setting-content {
  height: auto;
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

  .setting-label {
    font-weight: 700;
    color: #000;
    font-size: 1rem
  }
}
.con-vs-popup.setting-dialog .vs-popup {
  width: 700px!important;
}
</style>