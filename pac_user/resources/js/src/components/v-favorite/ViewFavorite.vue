<template>
  <div class="vx-col w-full mb-base">
    <vx-card class="h-full">
      <vs-row style="align-items:baseline;margin-bottom: 10px">
        <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="6">
          <vx-input-group class="w-full mb-0">
            <vs-input v-model="searchFavorite" />
            <template slot="append">
              <div class="append-text btn-addon">
                <vs-button color="primary" @click="onSearchFavorite()"><i class="fas fa-search"></i></vs-button>
              </div>
            </template>
          </vx-input-group>
        </vs-col>
      </vs-row>
      <div class="favorite_dialog">
        <draggable v-model="arrFavorite" @change="onSortFavorite">
          <transition-group>
            <div v-for="(itemFavorite, indexFavorite) in arrFavorite" :key="'favorites-'+indexFavorite" class="item"
                 @click="changeTableShow(indexFavorite)">
              <div class="mt-0" style="margin-top:5px;line-height:2.5rem;border-bottom: none;margin-bottom: -1px;">
                {{ indexFavorite + 1 }}.{{ itemFavorite[0].favorite_name ? itemFavorite[0].favorite_name : '名称未設定' }}
              </div>
              <div class="mt-0" style="display: flex;justify-content: space-between;border-top:none">
                <div style="width: 60px;">
                  <vs-button class="vs-button_dialog square action action_dialog" color="primary" @click="addUserView(itemFavorite)">追加</vs-button>
                </div>
                <ul class="like_addrs" v-if="tableshow.hasOwnProperty(indexFavorite)">
                  <li v-for="(uval,uindex) in itemFavorite" :key="uindex" style="margin:5px auto;">
                    <div class="like_addrs_content" style="padding: 2px 10px;">
                      <span>
                      ■ - {{ uval.name }} [{{ uval.email }}]
                    </span>
                    </div>
                  </li>
                  <div class="triangle"></div>
                </ul>
                <div style="flex-grow: 1;" vs-type="flex" v-if="!tableshow.hasOwnProperty(indexFavorite)">
                  <template v-for="(itemUser, index) in itemFavorite">
                    <template v-if="itemFavorite.length<=6 || (itemFavorite.length > 6 && index<5) ">
                      <div class="name" :key="'favorite-'+index+'-name'">
                        {{ itemUser.name.split(" ").map((w) => w[0]).join(" ") }}
                      </div>
                    </template>
                  </template>
                  <template v-if="itemFavorite.length>6">
                    <div class="name">...</div>
                  </template>
                </div>
                <div style="width: 30px; text-align: right; line-height: 12px;">
                  <a href="#" class="text-danger" @click="onRemoveFavorite(itemFavorite[0].favorite_no)"><i
                      class="fas fa-times"></i></a>
                </div>
              </div>
            </div>
          </transition-group>
        </draggable>
      </div>
    </vx-card>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import config from "../../app.config";
import Axios from "axios";

import draggable from 'vuedraggable';

export default {
  components: {
    //[LiquorTree.name]: LiquorTree,
    draggable
  },
  props: {
    opened:{type:Boolean,default:false},
    selectUserView:{type:Array,default:()=>[]},
    maxViewer:{type:Number,default:1},
    selectUsers:{type:Array,default:()=>[]},
    loginUser:{type:Object,default:()=>{}},
  },
  data() {
    return {
      searchFavorite: '',
      tableshow: {},
      arrFavorite:[],
    }
  },
  watch: {
    opened: async function (val) {
      if (val) {
        await this.onSearchFavorite();
      }
    }
  },
  methods: {
    ...mapActions({
      getListFavorite     : "favorite/getList",
      sortFavorite        : "favorite/updateSort",
      removeFavorite      : "favorite/removeView",
    }),
    changeTableShow(index) {
      if (this.tableshow[index]) {
        delete this.$delete(this.tableshow, index)
      } else {
        this.$set(this.tableshow, index, 1);
      }
    },
    async onSearchFavorite() {
      let res = await this.getListFavorite({favorite_name: this.searchFavorite,favorite_flg: 1});
      this.arrFavorite = res
      return this.arrFavorite;
    },
    async onRemoveFavorite(favorite_no){
      await this.removeFavorite({favorite_no: favorite_no,favorite_flg: 1});
      this.arrFavorite = await this.onSearchFavorite();
    },
    onSortFavorite(){
      let arrSort = this.arrFavorite.map((favotites) => {
        let ids=[];
        favotites.map((favotite) => {
          ids.push(favotite.id);
        });
        return ids;
      });
      this.sortFavorite({'sorts': arrSort}).then(()=>{
        this.onSearchFavorite();
      });
      
    },
    add(v,mailPattern) {
      return new Promise(async resolve => {
        if (this.selectUserView.length >= this.maxViewer) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `閲覧ユーザーに設定できるのは` + this.maxViewer + '名までです',
            accept: () => {
              resolve(false)
            }
          });
          return;
        }
        var emailViewSelect = v.email;
        var userViewnameSelect = v.name;

        if (emailViewSelect.match(mailPattern) === null) {
          this.emailViewSuggestValidateMsg = 'メールアドレスが正しくありません';
          return;
        }

        var result = await Axios.get(`${config.BASE_API_URL}/userView/checkemail/${emailViewSelect}`, {data: {}})
            .then(response => {
              return response.data ? response.data.data : [];
            })
            .catch(error => {
              return [];
            });
        const user = {
          // circular_id : this.circular.id,
          parent_send_order: this.selectUsers[0].parent_send_order,
          mst_user_id: result ? result.id : '',
          memo: "",
          del_flg: 0,
          create_user: this.selectUsers[0].email,
          update_user: this.selectUsers[0].email,
          email: emailViewSelect,
          name: userViewnameSelect ? userViewnameSelect : (result ? (result.family_name ? result.family_name + ' ' + result.given_name : '社員') : '社員'),
          company_id: result ? (result.mst_company_id ? result.mst_company_id : result.company_id) : '',
          user_auth: result ? result.user_auth : 0,
          option_flg: result ? (result.option_flg ? result.option_flg : 0) : 0,
        };
        var flg;
        if (user.company_id == null || (user.company_id != null && user.company_id != this.loginUser.mst_company_id)) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `別企業のユーザーは設定できません`,
            accept: () => {
              resolve(true);
              // this.clearViewSuggestionInput();
            }
          });
        } else if (user.email == this.selectUsers[0].email) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `申請者は登録できません`,
            accept: () => {
              resolve(true);
            }
          });
        } else if (this.selectUserView.find((v) => v.email === user.email)) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `既に登録されています`,
            accept: () => {
              resolve(true);
            }
          });
        } else if (this.selectUsers.find((v) => v.email === user.email && v.edition_flg == this.selectUsers[0].edition_flg && v.env_flg == this.selectUsers[0].env_flg && v.server_flg == this.selectUsers[0].server_flg)) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `承認者は登録できません。`,
            accept: () => {
              resolve(true);
            }
          });
        } else if (user.user_auth == 5 || user.option_flg == 2) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `受信専用利用者は閲覧ユーザーとして追加できないです。`,
            accept: () => {
              resolve(true);
            }
          });
        } else {
          this.$store.commit('application/addUserView', user);
          resolve(true);
        }
      })  
    },
    async addUserView(itemFavorite){

      $(".application-page-dialog .vs-popup--close").click();

      let data = {favorite: itemFavorite, usingHash: this.$store.state.home.usingPublicHash};
      let resultCheck = await Axios.post(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public' : ''}/user/checkFavoriteUserStatus`, data)
          .then(response => {
            if (response.data.status == false) {
              this.$vs.dialog({
                type: 'alert',
                color: 'danger',
                title: `確認`,
                acceptText: '閉じる',
                text: '削除または無効の利用者が存在しています。お気に入りを再設定してください。',
              });
            }
            return Promise.resolve(response.data);
          })
          .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
          });
      if(resultCheck.status == false){
        return ;
      }

      this.emailViewSuggestValidateMsg ='';
      const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
      for (const item of itemFavorite) {
        let status = await this.add(item,mailPattern)
        if (!status){
          break;
        }
      }
       
    },
  }
}
</script>

<style scoped>

</style>