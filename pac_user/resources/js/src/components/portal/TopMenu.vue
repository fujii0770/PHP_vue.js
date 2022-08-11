<template>
  <div>
    <div :class="'comp-portal-announces top-menu ' + (isMobile?'mobile':'')" v-if="!isMobile">
        <div class="vx-col w-full mb-8 sm:mb-0 md:mb-0 lg:mb-0 lg:pr-0">
            <!--            <div class="vs-row notice pt-0 sm:pt-0 md:pt-0 lg:pt-0" vs-justify="left">-->
            <!--                <span class="text-danger">{{urgentNotification}}</span>-->
            <!--            </div>-->
            <vx-card class="mb-4">
                <vs-row class="pt-2 sm:pt-2 md:pt-2 lg:pt-2 resize-text">

                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="$router.push('/portal')">
                        <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/portal')">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/home-strong.svg')"/></span>
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/home-light.svg')"/></span>
                        </vs-row>

                        <vs-row v-if="$route.path.includes('/portal')" vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder">
                            マイページ
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            マイページ
                        </vs-row>
                    </vs-col>
                    <!--PAC_5-3018 S-->
                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="$router.push('/personal/setting')">
                      <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/personal/setting')">
                        <span class="mb-1 active-menu cursor-pointer"><img :src="user_profile_data || require('@assets/images/pages/portal/person-strong.svg')" width="32" height="32"/></span>
                      </vs-row>
                      <vs-row v-else vs-justify="center" vs-align="center">
                        <span class="mb-1 active-menu cursor-pointer"><img :src="user_profile_data || require('@assets/images/pages/portal/person-light.svg')" width="32" height="32"/></span>
                      </vs-row>

                      <vs-row v-if="$route.path.includes('/personal/setting')" vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder">
                        個人設定
                      </vs-row>
                      <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                        個人設定
                      </vs-row>
                    </vs-col>
                    <!--PAC_5-3018 E-->
                    <vs-col v-if="this.$store.state.groupware.checkAddressListApp" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="$router.push('/personal/adrress_list')">
                        <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/personal/adrress_list')">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/address_strong.svg')" width="32" height="32"/></span>
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/address_light.svg')" width="32" height="32"/></span>
                        </vs-row>

                        <vs-row v-if="$route.path.includes('/personal/adrress_list')" vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder">
                            利用者名簿
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            利用者名簿
                        </vs-row>
                    </vs-col>

                    <!--PAC_5-3237 S-->
                    <vs-col v-if="!this.limit.enable_any_address" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openContacts">
                        <vs-row vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/portal_address.svg')"  width="22px" height="32px"/></span>
                        </vs-row>

                        <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            アドレス帳
                        </vs-row>
                    </vs-col>
                    <!--PAC_5-3237 E-->


                    <!-- PAC_5-3129 -->
                    <vs-col v-if="user_type==0 && isMobile" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="$router.push('/')">

                      <vs-row vs-justify="center" vs-align="center" style="height: 37px; line-height: 32px;">
                        <span class="active-menu cursor-pointer"><img :src="require('@assets/images/pages/home/creation_mypage.svg')" width="25" height="25"/></span>
                      </vs-row>
  
                      <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                        新規作成
                      </vs-row>

                    </vs-col>

                    <vs-col v-if="user_type==0 && isMobile" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="$router.push('/received')">

                      <vs-row vs-justify="center" vs-align="center" style="height: 37px; line-height: 32px;">
                        <span class="active-menu cursor-pointer"><img :src="require('@assets/images/pages/home/received_mypage.svg')" width="25" height="25"/></span>
                      </vs-row>
  
                      <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                        受信一覧
                      </vs-row>

                    </vs-col>
                    <!-- End PAC_5-3129 -->

                    <vs-col v-if="this.$store.state.groupware.checkCalendarApp" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openCalendar">
                        <vs-row v-if="$route.path.includes('/groupware/calendar')" vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/Calendar-strong.svg')"/></span>
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/Calendar-light.svg')"/></span>
                        </vs-row>

                        <vs-row v-if="$route.path.includes('/groupware/calendar')" vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder">
                            スケジューラ
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            スケジューラ
                        </vs-row>
                    </vs-col>

                    <vs-col v-if="this.$store.state.groupware.checkBulletinBoardApp" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openbulletinBoard">
                        <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/groupware/bulletin')">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/Clipboard-strong.svg')"/></span>
                        </vs-row>
                        <vs-row vs-justify="center" vs-align="center" v-else>
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/Clipboard-light.svg')"/></span>
                        </vs-row>

                        <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder" v-if="$route.path.includes('/groupware/bulletin')">
                            掲示板
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            掲示板
                        </vs-row>
                    </vs-col>
                  <vs-col v-if="this.$store.state.groupware.checkFaqBulletinBoardApp" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openFaqBulletinBoard">
                    <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/groupware/faq_bulletin')">
                      <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/Clipboard-strong.svg')"/></span>
                    </vs-row>
                    <vs-row vs-justify="center" vs-align="center" v-else>
                      <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/Clipboard-light.svg')"/></span>
                    </vs-row>

                    <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder" v-if="$route.path.includes('/groupware/faq_bulletin')">
                      サポート掲示板
                    </vs-row>
                    <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                      サポート掲示板
                    </vs-row>
                  </vs-col>

                    <vs-col v-if="this.$store.state.groupware.checkFileMailApp" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openFileMail">
                        <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/groupware/file_mail')">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/folder-strong.svg')"/></span>
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/folder-light.svg')"/></span>
                        </vs-row>

                        <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder" v-if="$route.path.includes('/groupware/file_mail')">
                            ファイルメール
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            ファイルメール
                        </vs-row>
                    </vs-col>

                    <vs-col v-if="this.$store.state.groupware.checkTimeCardApp" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openTimeCard">
                        <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/groupware/time-card')">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/icon-user-time-strong.svg')"></span>
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/icon-user-time-light.svg')"></span>
                        </vs-row>


                        <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder" v-if="$route.path.includes('/groupware/time-card')">
                            タイムカード
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            タイムカード
                        </vs-row>
                    </vs-col>
                    <vs-col  class="cursor-pointer" v-if="receive_plan_flg" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openReceivePlan">
                      <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/groupware/receive_plan')">
                        <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/receive-plan-strong.svg')"></span>
                      </vs-row>
                      <vs-row v-else vs-justify="center" vs-align="center">
                        <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/receive-plan-light.svg')"></span>
                      </vs-row>
                      <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder" v-if="$route.path.includes('/groupware/receive_plan')">
                        受信専用プラン
                      </vs-row>
                      <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                        受信専用プラン
                      </vs-row>
                    </vs-col>

                    <vs-col v-if="this.$store.state.groupware.checkToDoList" class="cursor-pointer" vs-justify="center" vs-align="center" style="width: 80px" @click.native="openToDoList">
                        <vs-row vs-justify="center" vs-align="center" v-if="$route.path.includes('/groupware/to-do-list')">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/icon-to-do-list-strong.svg')" width="32" height="32"></span>
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center">
                            <span class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/icon-to-do-list-light.svg')" width="32" height="32"></span>
                        </vs-row>


                        <vs-row vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden; color: #000000; font-weight: bolder" v-if="$route.path.includes('/groupware/to-do-list')">
                            Todoリスト
                        </vs-row>
                        <vs-row v-else vs-justify="center" vs-align="center" style="white-space: nowrap; overflow: hidden">
                            Todoリスト
                        </vs-row>
                    </vs-col>

                </vs-row>
            </vx-card>
        </div>
    </div>


    <!-- Mobile template -->
    <div :class="'comp-portal-announces-mobile ' + (isMobile?'mobile':'')" v-else>

      <!-- Item 1 -->
      <div :class="'item ' + ($route.path.includes('/portal')?'active':'')" @click="$router.push('/portal')">
        <div class="icon">
          <img :src="require('@assets/images/pages/portal/home-strong.svg')" v-if="$route.path.includes('/portal')" alt="マイページ" />
          <img :src="require('@assets/images/pages/portal/home-light.svg')" v-else alt="マイページ" />
        </div>
        <div class="label">マイページ</div>
      </div>

      <!-- Item 2
      <div :class="'item ' + ($route.path.includes('/personal/setting')?'active':'')" @click="$router.push('/personal/setting')">
        <div class="icon">
          <img :src="user_profile_data || require('@assets/images/pages/portal/person-strong.svg')" v-if="$route.path.includes('/personal/setting')" />
          <img :src="user_profile_data || require('@assets/images/pages/portal/person-light.svg')" v-else />
        </div>
        <div class="label">個人設定</div>
      </div>
       -->

      <!-- Item 3 -->
      <div class="item creation" v-if="user_type==0" @click="$router.push('/')">
        <div class="icon">
          <img :src="require('@assets/images/pages/home/creation_mypage.svg')"/>
        </div>
        <div class="label">新規作成</div>
      </div>

      <!-- Item 4 -->
      <div class="item received" v-if="user_type==0" @click="$router.push('/received')">
        <div class="icon">
          <img :src="require('@assets/images/pages/home/received_mypage.svg')" />
        </div>
        <div class="label">受信一覧</div>
      </div>

      <!-- Item 5 -->
      <div :class="'item ' + ($route.path.includes('/groupware/calendar')?'active':'')" v-if="this.$store.state.groupware.checkCalendarApp" @click="openCalendar">
          <div class="icon">
            <img :src="require('@assets/images/pages/portal/Calendar-strong.svg')" v-if="$route.path.includes('/groupware/calendar')" />
            <img :src="require('@assets/images/pages/portal/Calendar-light.svg')" v-else />
          </div>
          <div class="label">スケジューラ</div>
      </div>

      <!-- Item 6 -->
      <div :class="'item ' + ($route.path.includes('/groupware/bulletin')?'active':'')" v-if="this.$store.state.groupware.checkBulletinBoardApp" @click="openbulletinBoard">
        <div class="icon">
          <img :src="require('@assets/images/pages/portal/Clipboard-strong.svg')" v-if="$route.path.includes('/groupware/bulletin')" />
          <img :src="require('@assets/images/pages/portal/Clipboard-light.svg')" else />
        </div>
        <div class="label">掲示板</div>
      </div>

      <!-- Item 7 -->
      <div :class="'item clipboard ' + ($route.path.includes('/groupware/faq_bulletin')?'active':'')" v-if="this.$store.state.groupware.checkFaqBulletinBoardApp" @click="openFaqBulletinBoard">
        <div class="icon">
          <img :src="require('@assets/images/pages/portal/Clipboard-strong.svg')" v-if="$route.path.includes('/groupware/faq_bulletin')" />
          <img :src="require('@assets/images/pages/portal/Clipboard-light.svg')" v-else />
        </div>
        <div class="label">サポート掲示板</div>
      </div>

      <!-- Item 8 
      <div :class="'item ' + ($route.path.includes('/groupware/file_mail')?'active':'')" v-if="this.$store.state.groupware.checkFileMailApp" @click="openFileMail">
        <div class="icon">
          <img :src="require('@assets/images/pages/portal/folder-strong.svg')" v-if="$route.path.includes('/groupware/file_mail')"/>
          <img :src="require('@assets/images/pages/portal/folder-light.svg')" v-else />
        </div>
        <div class="label">ファイルメール</div>
      </div>
      -->

      <!-- Item 9
      <div :class="'item ' + ($route.path.includes('/groupware/time-card')?'active':'')" v-if="this.$store.state.groupware.checkTimeCardApp" @click="openTimeCard">
        <div class="icon">
          <img :src="require('@assets/images/pages/portal/icon-user-time-strong.svg')" v-if="$route.path.includes('/groupware/time-card')" />
          <img :src="require('@assets/images/pages/portal/icon-user-time-light.svg')" v-else />
        </div>
        <div class="label">タイムカード</div>
      </div>
       -->

      <!-- Item 10 -->
      <div :class="'item ' + ($route.path.includes('/groupware/receive_plan')?'active':'')" v-if="receive_plan_flg" @click="openReceivePlan">
        <div class="icon">
          <img :src="require('@assets/images/pages/portal/receive-plan-strong.svg')" v-if="$route.path.includes('/groupware/receive_plan')" />
          <img :src="require('@assets/images/pages/portal/receive-plan-light.svg')" v-else />
        </div>
        <div class="label">受信専用プラン</div>
      </div>

    </div>

  </div>
</template>

<script>
import {mapActions} from "vuex";
import config from "../../app.config";
import Axios from "axios";

export default {
    name: "TopMenu",
    props: {
      isMobile: Boolean
    },
    data() {
        return {
            appWidth: 0.6,
            myCompany: null,
            user_profile_data:null,
            user_type: 0,
            receive_plan_flg: false,
            limit:JSON.parse(getLS('limit'))
        };
    },
    methods: {
        ...mapActions({
            addLogOperation: "logOperation/addLog",
            getAvatarUser:'user/getAvatarUser',
        }),

        openCalendar() {
            this.addLogOperation({action: 'pr1-11-portal-single-calendar-board', result: 0})
            this.$router.push('/groupware/calendar');
        },
        openContacts(){
            this.$store.commit('SET_ACTIVATE_STATE', 'showModalContacts');
        },
        openbulletinBoard() {
            this.addLogOperation({action: 'pr1-10-portal-single-bulletin-board', result: 0})
            this.$router.push('/groupware/bulletin');
        },
        openFaqBulletinBoard() {
          this.addLogOperation({action: 'pr1-19-portal-single-faq-bulletin-board', result: 0})
          this.$router.push('/groupware/faq_bulletin');
        },
        openFileMail() {
            this.$router.push('/groupware/file_mail/application');
        },
        openTimeCard() {
            if(this.myCompany.attendance_system_flg == 1) {
                window.open('https://swk.shachihata.com/swk', '_blank')
            }else {
                this.addLogOperation({action: 'pr1-10-portal-single-bulletin-board', result: 0})
                this.$router.push('/groupware/time-card');
            }
        },
        openReceivePlan() {
          this.addLogOperation({action: 'pr1-22-portal-single-receive-plan', result: 0})
          this.$router.push('/groupware/receive_plan')
        },
        openToDoList() {
            this.addLogOperation({action: 'pr1-24-portal-single-to-do-list', result: 0})
            this.$router.push('/groupware/to-do-list');
        },
      /*PAC_5-3018 S*/
      updateAvatar(){
        this.getAvatarUser().then(res => {
          this.user_profile_data = res.user_profile_data?('data:image/jpeg;base64,'+res.user_profile_data):null
        })
      }
      /*PAC_5-3018 E*/
    },
    computed: {
    },
    created() {
         /*PAC_5-3018 S*/
          this.getAvatarUser().then(res => {
            this.user_profile_data = res.user_profile_data?('data:image/jpeg;base64,'+res.user_profile_data):null
          })
          this.myCompany = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany ? this.$store.state.groupware.myCompany : []
          this.urlGroupware = this.myCompany && this.myCompany.attendance_system_flg == 1 ? 'https://swk.shachihata.com/swk' : '/groupware/time-card';
          let limit = JSON.parse(getLS('limit'))
          if (!this.myCompany  || !this.myCompany .receive_plan_flg){
            this.receive_plan_flg = false
          }else {
            this.receive_plan_flg = this.myCompany.receive_plan_flg && limit.limit_receive_plan_flg
          }
    },
    watch:{
      '$store.state.groupware.myCompany':{
        handler(val){
          let limit = JSON.parse(getLS('limit'))
          if (!val || !val.receive_plan_flg){
            this.receive_plan_flg = false
          }else {
            this.receive_plan_flg = val.receive_plan_flg && limit.limit_receive_plan_flg
          }
        },
        deep:true
      }
    },
    mounted (){
      const loggedUser = JSON.parse(getLS('user'));
      this.user_type = loggedUser.option_flg;
    }
}
</script>

<style scoped>
.actived {
    background-color: #ddd;
}

.not-actived {
    background-color: #fff;
}
</style>
