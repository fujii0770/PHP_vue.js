<template>
  <div class="layout--main" :class="[layoutTypeClass, navbarClasses, footerClasses, {'app-page': isAppPage}]">

    <v-nav-menu
      v-if="!isMobile"
      :navMenuItems = "navMenuItems"
      :logo         = "navMenuLogo"
      title         = "Shachihata Cloud"
      parent        = ".layout--main" />

    <div id="content-area" :class="[contentAreaClass, {'show-overlay': bodyOverlay}]">
      <div id="content-overlay" />

    <!-- Navbar -->


    <template>
      <div :style="isMobile?'display: none;':''">
        <the-navbar-vertical
          :routeTitle="routeTitle"
          :navbarColor="navbarColor"
          :navbarFontColor="navbarFontColor"
          :class="[
            {'text-white' : true},
            {'text-base'  : true}
          ]" />
      </div>
    </template>
    <!-- /Navbar -->
      <div :class="'sp-navi '+(isMobile?'mobile':'')">
        <img class="logo" src="@assets/images/logo/sp-logo.png" alt="Logo">
        <div class="sp-navi-btn">&nbsp;</div>
        <div class="sp-navi-menu">
          <img src="@assets/images/logo/sp-logo-blue.png" alt="">
          <div class="menu-list" v-if="userOption!=2">
            <a href="/app/portal">マイページ</a>
            <a href="/app/create" title="新規作成" v-if="myCompany && myCompany.received_only_flg!=1 && !formUserFlg">新規作成</a>
            <a href="/app/received">受信一覧</a>
            <a href="/app/sent">送信一覧</a>
            <a href="/app/completed">完了一覧</a>
            <a href="/app/viewing">閲覧一覧</a>
            <a href="/app/saved" v-if="!formUserFlg">下書き一覧</a>

            <!--
            <vs-collapse  v-if="myCompany && myCompany.received_only_flg!=1" style="cursor: none;">
              <vs-collapse-item>
                <span slot="header">
                  グループウェア
                </span>
                <a href="/app/groupware/bulletin">掲示板</a>
              </vs-collapse-item>
            </vs-collapse>
            -->

            <vs-collapse style="cursor: none;">
              <vs-collapse-item>
                <span slot="header">
                  HR
                </span>
                <a v-bind:href="isShift ? '/app/hr/time_card_shift' : '/app/hr/time_card'">{{isShift ? 'タイムカード（シフト）' : 'タイムカード'}}</a>
                <a href="/app/hr/mail_setting">設定</a>
              </vs-collapse-item>
            </vs-collapse>
            <a href="https://help.dstmp.com/scloud/business/" target="_blank" rel="noopener noreferrer">ヘルプ</a>
          </div>
          <div class="menu-list" v-else >
              <a v-for="menu in navMenuItemsFilter" :href="'/app/'+menu.slug" :key="menu.slug">{{menu.name}}</a>
          </div>
          <a href="#" class="logout" v-on:click="logoutMobile()">ログアウト</a>
        </div>
      </div>

      <div class="content-wrapper" v-if="isLogin" >

        <div class="router-view">
          <div class="router-content">

            <transition :name="routerTransition">

              <div v-if="$route.meta.breadcrumb || $route.meta.pageTitle" class="router-header flex flex-wrap items-center mb-6">
                <div
                  class="content-area__heading"
                  :class="{'pr-4 border-0 md:border-r border-solid border-grey-light' : $route.meta.breadcrumb}">
                  <h2 class="mb-1">{{ routeTitle }}</h2>
                </div>

                <!-- BREADCRUMB -->
                <vx-breadcrumb class="ml-4 md:block hidden" v-if="$route.meta.breadcrumb" :route="$route" />

                <!-- DROPDOWN -->

              </div>
            </transition>

            <div class="content-area__content">

              <transition :name="routerTransition" mode="out-in">
                <keep-alive :include="['received_list','completed_list','viewing_list']" max="10">
                    <router-view @changeRouteTitle="changeRouteTitle" :key="$route.fullPath+reloadKey"></router-view>
                </keep-alive>
              </transition>
            </div>
          </div>
        </div>
      </div>
      <modal-contacts />

      <div class="sp-footer">
        Shachihata Cloud
      </div>
      <!--<the-footer />-->
    </div>
  </div>
</template>


<script>
import modalContacts       from '@/components/contacts/modalContacts.vue'
import HNavMenu            from "@/layouts/components/horizontal-nav-menu/HorizontalNavMenu.vue"
import navMenuItems        from "@/layouts/components/vertical-nav-menu/navMenuItems.js"
import navAuditItem        from "@/layouts/components/vertical-nav-menu/navAuditItem.js"
import navOptionItem       from "@/layouts/components/vertical-nav-menu/navOptionItem.js"
import navReceiveItem      from "@/layouts/components/vertical-nav-menu/navReceiveItem.js"
import navGuestCoItem      from "@/layouts/components/vertical-nav-menu/navGuestCompanyItem.js"
import TheCustomizer       from "@/layouts/components/customizer/TheCustomizer.vue"
import TheNavbarVertical   from '@/layouts/components/navbar/TheNavbarVertical.vue'
import TheFooter           from '@/layouts/components/TheFooter.vue'
import themeConfig         from '@/../themeConfig.js'
import VNavMenu            from '@/layouts/components/vertical-nav-menu/VerticalNavMenu.vue'
import { mapState, mapActions } from "vuex";
import { CIRCULAR } from '../../enums/circular';
import Storage from 'vue-ls';
import cssVars from 'css-vars-ponyfill';
import config from "../../app.config";
import Axios from "axios";

function hexToRgb(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
}

export default {
  components: {
    HNavMenu,
    TheCustomizer,
    TheFooter,
    TheNavbarVertical,
    VNavMenu,
    modalContacts
  },
  data() {
    return {
      CIRCULAR: CIRCULAR,
      disableCustomizer : themeConfig.disableCustomizer,
      disableThemeTour  : themeConfig.disableThemeTour,
      footerType        : themeConfig.footerType  || 'static',
      isNavbarDark      : false,
      navbarColor       : themeConfig.navbarColor || '#fff',
      navbarFontColor   : themeConfig.navbarFontColor || '#fff',
      navbarType        : themeConfig.navbarType  || 'floating',
      navMenuItems      : navMenuItems,
      navMenuLogo       : themeConfig.navbarIcon || require('@assets/images/logo/logo_blue.png'),
      routerTransition  : themeConfig.routerTransition || 'none',
      routeTitle        : this.$route.meta.title,
      isLogin           : false,
      myCompany         : null,
      myInfo            : null,
      reloadKey:1,
      userOption: 0,
      isMobile: false,
      formUserFlg: 0,
      isShift: false
    }
  },
  watch: {
    "$route"() {
      this.routeTitle = this.$route.meta.title;
      if(['save_detail','received_detail','sent_detail','public_approval','public_destination','destination'].includes(this.$route.name.trim())) {
        if(this.$store.state.home.circular && this.$store.state.home.circular.id) {
          if(CIRCULAR.CIRCULAR_COMPLETED_STATUS === this.$store.state.home.circular.circular_status || CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS === this.$store.state.home.circular.circular_status) {
            this.routeTitle = '完了';
            document.title = 'Shachihata Cloud ' + '- ' +'完了';
          }else if (CIRCULAR.SAVING_STATUS === this.$store.state.home.circular.circular_status) {
              this.routeTitle = '新規作成';
              document.title = 'Shachihata Cloud ' + '- ' +'新規作成';
          }else{
              this.routeTitle = '回覧中';
          }
        }
      }
    },
    isThemeDark: function(val) {
      const color = this.navbarColor == "#fff" && val ? "#10163a" : "#fff"
      this.updateNavbarColor(color)
    },
    "$store.state.mainLayoutType": function(val) {
      this.setNavMenuVisibility(val)
      this.disableThemeTour = true
    },
    windowWidth: function(val) {
      if(val < 1200) this.disableThemeTour = true
    },
    verticalNavMenuWidth() {
      this.disableThemeTour = true
    },
    "$store.state.loading": function(val) {
        //if (window.document.documentMode) return;
        if(val) this.$vs.loading({type:'sound'});
        else this.$vs.loading.close();
    },
    "$store.state.notification.status": function(val) {
      if(val) {
        this.$vs.notify({
          position: 'bottom-left',
          title: '',
          text: this.$store.state.notification.message,
          color: this.$store.state.notification.type
        });
        this.$store.commit("ALERT_CLEAR")
      }
    },
  },
  computed: {
    bodyOverlay() { return this.$store.state.bodyOverlay },
    contentAreaClass() {
      if(this.mainLayoutType === "vertical") {
        if      (this.verticalNavMenuWidth == "default") return "content-area-reduced"
        else if (this.verticalNavMenuWidth == "reduced") return "content-area-lg"
        else return ""
      }
      // else if(this.mainLayoutType === "boxed") return "content-area-reduced"
      else return "content-area-full"
    },
    footerClasses() {
      return {
        'footer-hidden': this.footerType == 'hidden',
        'footer-sticky': this.footerType == 'sticky',
        'footer-static': this.footerType == 'static',
      }
    },
    isAppPage() {
      return this.$route.path.includes('/apps/') ? true : false
    },
    isThemeDark()     { return this.$store.state.theme == 'dark' },
    layoutTypeClass() { return `main-${this.mainLayoutType}` },
    mainLayoutType()  { return this.$store.state.mainLayoutType  },
    navbarClasses()   {
      return {
        'navbar-hidden'   : this.navbarType == 'hidden',
        'navbar-sticky'   : this.navbarType == 'sticky',
        'navbar-static'   : this.navbarType == 'static',
        'navbar-floating' : this.navbarType == 'floating',
      }
    },
    verticalNavMenuWidth() { return this.$store.state.verticalNavMenuWidth },
    windowWidth()          { return this.$store.state.windowWidth },
    navMenuItemsFilter () {
        return this.navMenuItems.filter(menu => menu.slug && menu.slug!='download')
    },
  },
  methods: {
    ...mapActions({
      getUnreadTotal: "circulars/getUnreadTotal",
      getUnreadNoticeTotal: "notice/getUnreadNoticeTotal",
      getUserAppUsageStatus: "groupware/getUserAppUsageStatus",
      getTokenGroupware: "groupware/getTokenGroupware",
    }),
    changeRouteTitle(title) {
      this.routeTitle = title
    },
    updateNavbar(val) {
      if (val == "static") this.updateNavbarColor("#fff")
      this.navbarType = val
    },
    updateNavbarColor(val) {
      this.navbarColor = val
      if (val == "#fff") this.isNavbarDark = false
      else this.isNavbarDark = true
    },
    updateFooter(val) {
      this.footerType = val
    },
    updateRouterTransition(val) {
      this.routerTransition = val
    },
    setNavMenuVisibility(layoutType) {
      if((layoutType === 'horizontal' && this.windowWidth >= 1200) || (layoutType === "vertical" && this.windowWidth < 1200)) {
        this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', false)
        this.$store.dispatch('updateVerticalNavMenuWidth', 'no-nav-menu')
      }
      else {
        this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', true)
      }
    },
    checkLogin(){
        this.isLogin = true;
      let isExpires = false;
       var access_token = sessionStorage.getItem('token');
        var user_info = getLS('user');
        var expires_time = localStorage.getItem('expires_time');
        var return_url = localStorage.getItem('return_url');

        if (access_token && expires_time && user_info) {
            if (expires_time > new Date().getTime()) { // ok
                this.user_info = JSON.parse(user_info);
                auth.login(access_token, this.user_info, expires_time);
                this.isLogin = true;
            }else isExpires = true;
        }

        if(!this.isLogin){
          auth.logout();
          if(return_url){
            localStorage.removeItem('return_url');
            if(isExpires){
              location.href = return_url+"?status=205&message=Token Expires";
            }else{
              location.href = return_url+"?status=401&message=Unauthorized";
            }
          }else{
            this.$toast.error('Unauthorized or Token Expires', 'Error', { position: "bottomCenter" });
          }
        }
    },
    async logoutMobile() {
      let result = await Axios.get(`${config.LOCAL_API_URL}/logout`)
          .then(response => {
              return response.data ? response.data: [];
          })
          .catch(error => {
              return [];
          });

      let return_url = localStorage.getItem('return_url');
      if (result && Object.prototype.hasOwnProperty.call(result, "redirectUrl")){
          return_url = result.redirectUrl;
      }

      sessionStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('expires_time');
      localStorage.removeItem('loggedInAdmin');
      localStorage.removeItem('branding');
      localStorage.removeItem('limit');
      window.location.href = return_url;
      this.$ls.set(`logout`, Date.now());
    },
    async checkAccessToken() {
      let gw_flg = this.myCompany &&  this.myCompany.gw_flg ? this.myCompany.gw_flg : 0;
      if (gw_flg) {
        if (!this.$cookie.get('emailGroupwareAccessToken') || (this.$cookie.get('emailGroupwareAccessToken') && this.$cookie.get('emailGroupwareAccessToken') != this.admin_email)) {
          this.$cookie.set('emailGroupwareAccessToken', this.admin_email);
          await this.setCookiesGroupware();
        } else {
          if (!this.$cookie.get('accessToken')) {
            await this.setCookiesGroupware();
          }
        }
      }
    },
    async setCookiesGroupware(){
      let data = {
        editionFlg : config.APP_EDITION_FLV,
        envFlg : config.APP_SERVER_ENV,
        serverFlg : config.APP_SERVER_FLG,
        token : sessionStorage.getItem('token'),
      }
      let tokenGroupware =  await this.getTokenGroupware(data).then( async cookie => {
        if(cookie == false){
          let dataToken = {
            'accessToken' : '',
            'refreshToken': '',
            'userRoles' : '',
            'userProfile' : '',
            'dateGetToken' : ''
          };
          await this.setDomainGroupware(dataToken);
        }
        return cookie;
      });
      if(tokenGroupware){
        await this.updateCookiesGroupware(tokenGroupware);
      }
    },
    setDomainGroupware(data){
      let domain = config.GROUPWARE_DOMAIN;
      if(data){
        let newCookieData = {};
        if(data.userProfile){
          let currentUserProfile = JSON.parse(data.userProfile);
          // key => val
          newCookieData.id = currentUserProfile.id;
          newCookieData.email = currentUserProfile.email;
          newCookieData.name = currentUserProfile.name;
          newCookieData.optionFlg = currentUserProfile.optionFlg;
          newCookieData.portalId = currentUserProfile.portalId;
          newCookieData.stateFlg = currentUserProfile.stateFlg;
          // key => {key=>val}
          newCookieData.mstColor = currentUserProfile.mstColor;
          delete newCookieData.mstColor.createdAt;
          delete newCookieData.mstColor.updatedAt;

          newCookieData.mstCompany = currentUserProfile.mstCompany;
          delete newCookieData.mstCompany.block;
          delete newCookieData.mstCompany.building;
          delete newCookieData.mstCompany.city;
          delete newCookieData.mstCompany.createdAt;
          delete newCookieData.mstCompany.faxNumber;
          delete newCookieData.mstCompany.phoneNumber;
          delete newCookieData.mstCompany.postalCode;
          delete newCookieData.mstCompany.updatedAt;
        }
        newCookieData = JSON.stringify(newCookieData);
        this.$cookie.set('accessToken', data.accessToken, {SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('refreshToken', data.refreshToken, {SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('userRoles', data.userRoles, {SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('userProfile', newCookieData, {SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('dateGetToken', data.dateGetToken, {SameSite:"None", Secure:false, domain:domain});
      }else{
        this.$cookie.set('accessToken', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('refreshToken', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('userRoles', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('userProfile', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
        this.$cookie.set('dateGetToken', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
      }

    },
    async updateCookiesGroupware(data){
      window.sessionStorage.setItem('dateSetRefreshToken', new Date());
      let dataToken = {
        'accessToken' : data.accessToken,
        'refreshToken': data.refreshToken,
        'userRoles' : data.roles
      };
      const userProfileGroupware =  await Axios.get(`${config.GROUPWARE_API_URL}/mst-user/`+data.userId,
        {
          headers: {'Authorization': 'Bearer ' + data.accessToken}
        }
      );
      if(userProfileGroupware.data.caldavPassword){
        delete userProfileGroupware.data.caldavPassword
      }
      if (userProfileGroupware.data.mstCompany)
        delete userProfileGroupware.data.mstCompany.mstPrefecture
      if (userProfileGroupware.data.mstDepartment)
        delete userProfileGroupware.data.mstDepartment.mstCompany
      if (userProfileGroupware.data.mstPosition) delete userProfileGroupware.data.mstPosition.mstCompany
      if (userProfileGroupware.data.userProfileData) delete userProfileGroupware.data.userProfileData
      dataToken.userProfile =  JSON.stringify(userProfileGroupware.data);
      dataToken.dateGetToken =  this.$moment(new Date()).format('YYYY-MM-DD HH:mm:ss');

      await this.setDomainGroupware(dataToken);
      //RefreshToken after 12 house
      var root = this;
      root.refreshToken =  setInterval(() => {
        root.updateRefreshToken(root.$cookie.get('refreshToken')).then(data => {
          this.updateCookiesGroupware(data);
        });
      }, 12*60*60*1000);
    },
    async checkUserAppUsageStatus() {
      let tokenGroupware = this.$cookie.get('accessToken');
      if (tokenGroupware) {
        const userAppUsageStatus = await this.getUserAppUsageStatus(tokenGroupware);
        if (userAppUsageStatus) {
          /*
          var bulletinBoardApp = await userAppUsageStatus.some(item=> {
            if(item.appName === "掲示板") {
              return item.isAuth;
            }
          });
          this.$store.commit('groupware/updateCheckBulletinBoardApp', bulletinBoardApp);
          */
          var calendarApp = await userAppUsageStatus.some(item => {
            if (item.appName === "スケジューラ") {
              return item.isAuth;
            }
          });
          var caldavApp = await userAppUsageStatus.some(item => {
            if (item.appName === "カレンダー連携") {
              return item.isAuth;
            }
          });

          //   var fileMailApp = await userAppUsageStatus.some(item=> {
          //     if(item.appName === 'ファイルメール便') {
          //       return item.isAuth;
          //     }
          //   });
          //
          // var timeCardApp = await userAppUsageStatus.some(item=> {
          //     if(item.appName === "タイムカード") {
          //         return item.isAuth;
          //     }
          // });
          this.$store.commit('groupware/updateCheckCalendarApp', calendarApp);
          this.$store.commit('groupware/updateCheckCaldavApp', caldavApp);
          // ファイルメール便フラグ
          // this.$store.commit('groupware/updateCheckFileMailApp', fileMailApp);
          // this.$store.commit('groupware/updateCheckTimeCardApp', timeCardApp);
        } else {
          // this.$store.commit('groupware/updateCheckBulletinBoardApp', false);
          this.$store.commit('groupware/updateCheckCalendarApp', false);
          this.$store.commit('groupware/updateCheckCaldavApp', false);
          // this.$store.commit('groupware/updateCheckFileMailApp', false);
          // this.$store.commit('groupware/updateCheckTimeCardApp', false);
        }
      }
    },
  },
  async created() {

    // Checkmobile
    if (
      /Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      )
    ) {
      this.isMobile = true
    }

    if (window.document.documentMode) {
      const branding = JSON.parse(getLS('branding'));
      let color = "31,116,255";
      // if(branding) {
      //   const colorHex = `#${branding.background_color}`;
      //   const rgb = hexToRgb(colorHex);
      //   if(rgb) color = `${rgb.r},${rgb.g}, ${rgb.b}`;
      // }
      cssVars({
        variables: {
          "--vs-primary": color
        }
      });

    }
    this.checkLogin();
    await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
        .then(response => {
            const nowTime = new Date().getTime();
            this.myCompany = response.data ? response.data.data: []
            if(this.myCompany){
                this.myCompany.currentTime = nowTime;
            }
            this.$store.commit('groupware/updateMyCompany', this.myCompany);
        })
        .catch(error => { return []; });
    let loginUser =JSON.parse(getLS('user'));
    this.isShift = !!loginUser.shift_flg;
    if (!loginUser.isAuditUser) {
      this.myInfo = await Axios.get(`${config.BASE_API_URL}/myinfo`)
          .then(response => {
              return response.data ? response.data.data : [];
          })
          .catch(error => {
              return [];
          });
    }else{
        this.myInfo =null
    }
    if(this.navMenuItems){
      // kill  menu
       for( var i=0; i< this.navMenuItems.length; i++){
         if (this.navMenuItems[i].slug == 'work'){
             for(var d=0; d<this.navMenuItems[i].submenu.length;d++){
                  if((this.myInfo.info.shift_flg === 1) && (this.navMenuItems[i].submenu[d].slug == 'time_card')){
                        (this.navMenuItems[i].submenu).splice(d,1);
                        break;
                  }
                   if((this.myInfo.info.shift_flg === 0) && (this.navMenuItems[i].submenu[d].slug == 'time_card_shift')){
                         (this.navMenuItems[i].submenu).splice(d,1);
                         break;
                 }
             }
           }
       }
    }
    if (this.myCompany && !this.myCompany.long_term_storage_flg){
        for(var i = 0; i< navMenuItems.length; i++){
            if (navMenuItems[i].slug == 'document'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
    }
    if (this.myCompany && this.myCompany.addressbook_only_flag){
        for(var i = 0; i< navMenuItems.length; i++){
            if (navMenuItems[i].slug == 'Book'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
    }
    if ((this.myCompany && !this.myCompany.template_flg) || (this.myInfo && !this.myInfo.info.template_flg)){
        for(var i = 0; i< navMenuItems.length; i++){
            if (navMenuItems[i].slug == 'template'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
    }
    if (this.myCompany && !this.myCompany.portal_flg){
        for(var i = 0; i< navMenuItems.length; i++){
            if (navMenuItems[i].slug == 'portal'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
    }
    if ((this.myCompany && !this.myCompany.template_flg) || (this.myCompany && !this.myCompany.template_csv_flg) || (this.myInfo && !this.myInfo.info.template_flg)){
        for(var i = 0; i< navMenuItems.length; i++){
            if (navMenuItems[i].slug == 'templatecsv'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
    }
    if ((this.myCompany && !this.myCompany.expense_flg)){
        for(var i = 0; i< navMenuItems.length; i++) {
            if (navMenuItems[i].slug == 'calculation-expense'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
    }
    //if mst_company.hr_flg == 1 show submenu 出退勤管理 else hide submenu
    if (this.myCompany.hr_flg === 0){
      for(var i = 0; i< this.navMenuItems.length; i++){
        if (Object.prototype.hasOwnProperty.call(this.navMenuItems[i], "hr_flgRequired") && this.navMenuItems[i].hr_flgRequired){
          this.navMenuItems.splice(i, 1);
        }
      }
    }
    //if mst_company.frm_srv_flg == 1 show submenu 帳票メニュー else hide submenu
    if (this.myCompany.frm_srv_flg === 0){
      for(var i = 0; i< this.navMenuItems.length; i++){
        if (Object.prototype.hasOwnProperty.call(this.navMenuItems[i], "frm_srv_flgRequired") && this.navMenuItems[i].frm_srv_flgRequired){
          this.navMenuItems.splice(i, 1);
        }
      }
    }
    if (this.myCompany && !this.myCompany.bizcard_flg){
        for(var i = 0; i< navMenuItems.length; i++){
            if (navMenuItems[i].slug == 'bizcard'){
                navMenuItems.splice(i, 1);
                break;
            }
        }
        for(var i = 0; i< navGuestCoItem.length; i++){
            if (navGuestCoItem[i].slug == 'bizcard'){
                navGuestCoItem.splice(i, 1);
                break;
            }
        }
    }
    if (this.myCompany && this.myCompany.form_user_flg){
      for(var i = 0; i< navMenuItems.length; i++){
        if (navMenuItems[i].slug == 'creation' || navMenuItems[i].slug == 'saved'){
          navMenuItems.splice(i, 1);
        }
      }
    }
    const color = this.navbarColor == "#fff" && this.isThemeDark ? "#10163a" : this.navbarColor
    this.updateNavbarColor(color)
    this.setNavMenuVisibility(this.$store.state.mainLayoutType)
    localStorage.removeItem('tokenPublic');
    this.$store.commit('home/setUsingPublicHash', false);
    this.routeTitle = this.$route.meta.title;
    if(['save_detail','received_detail','sent_detail','public_approval','public_destination','destination','sendback'].includes(this.$route.name.trim())) {
      if(this.$store.state.home.circular && this.$store.state.home.circular.id) {
        if(CIRCULAR.CIRCULAR_COMPLETED_STATUS === this.$store.state.home.circular.circular_status || CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS === this.$store.state.home.circular.circular_status) {
          this.routeTitle = '完了';
        }else {
          this.routeTitle = '回覧中';
        }
      }
    }
    if (!this.isMobile) {
      await this.checkAccessToken();
      await this.checkUserAppUsageStatus();
    }
  },
  async mounted() {
    if (this.isMobile) {
      await this.checkAccessToken();
      await this.checkUserAppUsageStatus();
      // const calendarMenu = $('.vs-collapse-item--content .con-content--item.calendar-menu');
      // if(this.$store.state.groupware.checkCalendarApp) {
      //   if (!calendarMenu || calendarMenu.length === 0) {
      //     $('.vs-collapse-item--content').append("<div class=\"con-content--item calendar-menu\"><a href=\"/app/groupware/calendar\">スケジューラ</a></div>")
      //   }
      // } else {
      //     calendarMenu.remove();
      // }
    }
    if (window.document.documentMode) {
      $('.vs-sidebar--item svg').attr('viewBox', '0 0 8 24');
    }
    const loggedUser = JSON.parse(getLS('user'));
    this.userOption = loggedUser.option_flg;
    this.formUserFlg = loggedUser.form_user_flg;
    if(loggedUser && loggedUser.id) {
        if(loggedUser.isAuditUser){
            // this.$router.push('/document-search');
            this.navMenuItems = navAuditItem;
        }else if(loggedUser.isGuestCompany && !loggedUser.guestCanSubscribeCircular) {
            this.$router.push('/received');
            this.navMenuItems = navGuestCoItem;
        }else if (this.userOption === 1){
            this.navMenuItems = navOptionItem;
        }else if (this.userOption === 2){
            this.navMenuItems = navReceiveItem;
            //if loggedUser.hr_user_flg == 1 show submenu 出退勤管理 else hide submenu
            if (loggedUser.hr_user_flg == 0){
              for(var i = 0; i< this.navMenuItems.length; i++){
                if (Object.prototype.hasOwnProperty.call(this.navMenuItems[i], "hr_flgRequired") && this.navMenuItems[i].hr_flgRequired){
                  this.navMenuItems.splice(i, 1);
                }
              }
            }
        }
        if(!loggedUser.isAuditUser) {
          this.getUnreadTotal();
          this.getUnreadNoticeTotal();
          setInterval(() => {
            this.getUnreadTotal();
            this.getUnreadNoticeTotal();
          }, 1200000);
        }
    }

    $('.sp-navi-btn').click(function () {
      $(this).toggleClass('active');
      sessionStorage.removeItem("firstlogin");
      $('#content-area .content-wrapper').toggleClass('hide')
    })

    if( sessionStorage.getItem('firstlogin') == 1 && this.isMobile) {
      $('.sp-navi-btn').trigger('click');
    }
  },
  beforeRouteUpdate(to,from,next){
        let pageArray=['completed_list','viewing_list','received_list']
        if(pageArray.indexOf(to.name)!=-1){
            if(from.meta.isKeep){
                to.meta.back=true
                delete from.meta.isKeep
                if(from.meta.keepReading){
                    to.meta.keepReading=true
                    delete from.meta.keepReading
                }
            }else {
                this.reloadKey++
            }

        }
        next()
    }
}

</script>

<style lang="scss">
  .sp-navi.mobile{
    display: flex;
    z-index: 999;
    align-items: center;
    justify-content: center;
    position: fixed;
    height: 10vw;
    max-height: 60px;
    width: 100vw;
    background: #0984e3;
    left: 0;
    top: 0;
    text-align: center;

    .logo{
      max-width: 150px;
    }

    .sp-navi-btn{
      max-height: 60px;

      &:before, &:after{
        max-width: 50px;
      }
    }
    .menu-list {
        .vs-collapse-item--content {
            > .con-content--item {
                padding: 0!important;
                > a {
                    display: block;
                    position: relative;
                    font-size: 3.2vw;
                    padding: 4vw 0;
                    padding-left: 9.6vw;
                    color: #000;
                    text-align: left;
                }
            }
        }
        > a{
          padding-left: 0;
          text-align: center;

          &:before{
            display: none;
          }
        }
    }
    .logout{
      padding-left: 0;
      text-align: center;
    }
  }
  @media(min-width: 481px){
    .sp-navi.mobile{
      .sp-navi-menu{
        padding-top: 7vw;
        overflow: auto;
        padding-bottom: 5vw;
      }
      .sp-navi-btn{

        &:before{
          transform: translate(0, -1vw) rotate(0);
        }
        &:after{
          transform: translate(0, 1vw) rotate(0);
        }

        &.active{
          &:before{
            transform: translate(0, 0) rotate(-135deg);
          }
          &:after{
            transform: translate(0, 0) rotate(135deg);
          }
        }
      }
    }
  }
  .vs-collapse-item{
    &.open-item{
      background: #ebf2fb;
      header span{
        font-weight: bold;
      }
    }
  }
  .sp-navi{
    &.mobile .menu-list .vs-collapse-item--content > .con-content--item > a{
      padding-left: 0;
      text-align: center;
    }
    .sp-navi-menu .vs-collapse{
      text-align: center;
      .vs-collapse-item header{
        padding-left: 0;
      }
    } 
  }
</style>

