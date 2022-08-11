<template>
  <div class="layout--main" :class="[layoutTypeClass, navbarClasses, footerClasses, {'app-page': isAppPage}]">

    <v-nav-menu
      :navMenuItems = "navMenuItems"
      :logo         = "navMenuLogo"
      isPublic       = "true"
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
        :userInfo="userHashInfo"
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
          <div class="menu-list">
            <a href="https://help.dstmp.com/scloud/business/" target="_blank" rel="noopener noreferrer">ヘルプ</a>
          </div>
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
                <router-view @changeRouteTitle="changeRouteTitle" :key="$route.fullPath"></router-view>
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
import navMenuItems        from "@/layouts/components/vertical-nav-menu/navPublicMenuItems.js"
import TheCustomizer       from "@/layouts/components/customizer/TheCustomizer.vue"
import TheNavbarVertical   from '@/layouts/components/navbar/ThePublicNavbarVertical.vue'
import TheFooter           from '@/layouts/components/TheFooter.vue'
import themeConfig         from '@/../themeConfig.js'
import VNavMenu            from '@/layouts/components/vertical-nav-menu/VerticalNavMenu.vue'
import { mapState, mapActions } from "vuex";
import { CIRCULAR } from '../../enums/circular';
import Storage from 'vue-ls';
import cssVars from 'css-vars-ponyfill';

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
      navMenuLogo       : require('@assets/images/logo/logo_blue.png'),
      routerTransition  : themeConfig.routerTransition || 'none',
      routeTitle        : this.$route.meta.title,
      isLogin           : false,
      userHashInfo      : null,
      isMobile          : false
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
          }else {
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
    "$store.state.home.circular": function(){
      if(['save_detail','received_detail','sent_detail','public_approval','public_destination','destination'].includes(this.$route.name.trim())) {
        if(this.$store.state.home.circular && this.$store.state.home.circular.id) {
          if(CIRCULAR.CIRCULAR_COMPLETED_STATUS === this.$store.state.home.circular.circular_status || CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS === this.$store.state.home.circular.circular_status) {
            this.routeTitle = '完了';
            document.title = 'Shachihata Cloud ' + '- ' +'完了';
          }else {
            this.routeTitle = '回覧中';
          }
        }
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
    windowWidth()          { return this.$store.state.windowWidth }
  },
  methods: {
    ...mapActions({
      getUnreadTotal: "circulars/getUnreadTotal",
      getInfoByHash: "user/getInfoByHash",
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
    }
  },
  async created() {
    // Check Mobile
    if (
      /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
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
    const color = this.navbarColor == "#fff" && this.isThemeDark ? "#10163a" : this.navbarColor
    this.updateNavbarColor(color)
    this.setNavMenuVisibility(this.$store.state.mainLayoutType);

    const hash = this.$route.params.hash;
    if(hash) {
      localStorage.setItem('tokenPublic', hash);
      this.$store.commit('home/setUsingPublicHash', true);
      this.userHashInfo = await this.getInfoByHash();
    }

    this.routeTitle = this.$route.meta.title;
    if(['save_detail','received_detail','sent_detail','public_approval','public_destination','destination','sendback'].includes(this.$route.name.trim())) {
      if(this.$store.state.home.circular && this.$store.state.home.circular.id) {
        if(CIRCULAR.CIRCULAR_COMPLETED_STATUS === this.$store.state.home.circular.circular_status || CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS === this.$store.state.home.circular.circular_status) {
          this.routeTitle = '完了';
          document.title = 'Shachihata Cloud ' + '- ' +'完了';
        }else {
          this.routeTitle = '回覧中';
        }
      }
    }
  },
  mounted() {
    if (window.document.documentMode) {
      $('.vs-sidebar--item svg').attr('viewBox', '0 0 8 24');
    }

    $('.sp-navi-btn').click(function () {
      $(this).toggleClass('active');
    })
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
</style>
