

<template>
  <div class="parentx">

    <vs-sidebar
      class="v-nav-menu items-no-padding"
      v-model="isVerticalNavMenuActive"
      ref="verticalNavMenu"
      default-index="-1"
      :click-not-close="clickNotClose"
      :reduce-not-rebound="reduceNotRebound"
      :parent="parent"
      :hiddenBackground="clickNotClose"
      :reduce="reduce"
      v-hammer:swipe.left="onSwipeLeft">

      <div @mouseenter="mouseEnter" @mouseleave="mouseLeave">

        <!-- Header -->
        <div class="header-sidebar flex items-start justify-between" slot="header">
          <!-- Logo -->
          <router-link tag="div" class="vx-logo cursor-pointer flex items-center" v-if="!isPublic && loginUser && !loginUser.isAuditUser && (!loginUser.isGuestCompany || loginUser.guestCanSubscribeCircular) && !loginUser.option_flg && !loginUser.form_user_flg" to="/">
            <img :src="logo" alt="logo" class="w-10 mr-4" v-if="logo">
            <p class="vx-logo-text" v-show="isMouseEnter || !reduce" v-if="title">{{ title }} <br/>{{app_env==1?loginUser.system_name:''}}</p>
          </router-link>

          <router-link tag="div" class="vx-logo cursor-pointer flex items-center" v-if="!isPublic && loginUser && !loginUser.isAuditUser && loginUser.isGuestCompany && !loginUser.guestCanSubscribeCircular && !loginUser.option_flg && !loginUser.form_user_flg" to="/received">
            <img :src="logo" alt="logo" class="w-10 mr-4" v-if="logo">
            <p class="vx-logo-text" v-show="isMouseEnter || !reduce" v-if="title">{{ title }} <br/>{{app_env==1?loginUser.system_name:''}}</p>
          </router-link>

          <router-link tag="div" class="vx-logo cursor-pointer flex items-center" v-if="!isPublic && loginUser && loginUser.isAuditUser && !loginUser.option_flg && !loginUser.form_user_flg" to="/document-search">
            <img :src="logo" alt="logo" class="w-10 mr-4" v-if="logo">
            <p class="vx-logo-text" v-show="isMouseEnter || !reduce" v-if="title">{{ title }} <br/>{{app_env==1?loginUser.system_name:''}}</p>
          </router-link>

          <router-link tag="div" class="vx-logo cursor-pointer flex items-center" v-if="!isPublic && loginUser && (loginUser.option_flg == 1 || loginUser.form_user_flg)" to="/portal">
            <img :src="logo" alt="logo" class="w-10 mr-4" v-if="logo">
            <p class="vx-logo-text" v-show="isMouseEnter || !reduce" v-if="title">{{ title }} <br/>{{app_env==1?loginUser.system_name:''}}</p>
          </router-link>

          <router-link tag="div" class="vx-logo cursor-pointer flex items-center" v-if="!isPublic && loginUser && loginUser.option_flg == 2 && !loginUser.form_user_flg" to="/received">
            <img :src="logo" alt="logo" class="w-10 mr-4" v-if="logo">
            <p class="vx-logo-text" v-show="isMouseEnter || !reduce" v-if="title">{{ title }} <br/>{{app_env==1?loginUser.system_name:''}}</p>
          </router-link>

         <div tag="div" class="vx-logo cursor-pointer flex items-center" v-if="isPublic=='true'" v-on:click="logout">
            <img :src="logo" alt="logo" class="w-10 mr-4" v-if="logo">
            <p class="vx-logo-text" v-show="isMouseEnter || !reduce" v-if="title">{{ title }} <br/>{{ loginUser && app_env==1 ? loginUser.system_name : '' }}</p>
         </div>
          <!-- /Logo -->

          <!-- Menu Buttons -->
          <div>
            <!-- Close Button -->
            <template v-if="showCloseButton">
              <feather-icon icon="XIcon" class="m-0 cursor-pointer" @click="$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', false)" />
            </template>

            <!-- Toggle Buttons -->
            <template v-else-if="!showCloseButton && !verticalNavMenuItemsMin">
                <div
                    id="btnVNavMenuMinToggler"
                    @click="toggleReduce(!reduce)"
                    class="mt-1 cursor-pointer mr-0"
                >
                    <svg v-if="reduce" xmlns="http://www.w3.org/2000/svg" id="Capa_1" enable-background="new 0 0 30 30" height="20px" viewBox="0 0 512 512" width="20px"><g><g><path d="m330.274 0-10.607 10.607c-24.914 24.914-28.585 63.132-11.047 91.987l-107.305 72.504-1.856-1.856c-40.939-40.939-107.553-40.94-148.492 0l-10.607 10.606 133.289 133.289-173.649 173.65 21.213 21.213 173.649-173.65 133.29 133.29 10.607-10.607c40.94-40.94 40.939-107.553 0-148.492l-1.856-1.856 72.504-107.305c28.855 17.539 67.073 13.868 91.987-11.047l10.606-10.606zm-3.187 428.148-243.235-243.235c29.104-19.248 68.783-16.069 94.394 9.541l139.3 139.3c25.61 25.611 28.789 65.29 9.541 94.394zm-11.791-139.07-92.374-92.374 105.496-71.281 58.159 58.159zm101.245-117.958-75.66-75.66c-13.828-13.828-16.758-34.491-8.789-51.216l135.665 135.665c-16.725 7.969-37.388 5.039-51.216-8.789z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#0984E3"/></g></g> </svg>
                    <svg v-if="!reduce" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20px" height="20px" viewBox="0 0 20 20" version="1.1">
                        <g id="surface1">
                            <path style=" stroke:none;fill-rule:nonzero;fill:rgb(3.529412%,51.764706%,89.019608%);fill-opacity:1;" d="M 12.902344 0 L 12.488281 0.414062 C 11.515625 1.386719 11.371094 2.878906 12.054688 4.007812 L 7.863281 6.839844 L 7.792969 6.765625 C 6.191406 5.167969 3.589844 5.167969 1.992188 6.765625 L 1.578125 7.179688 L 6.78125 12.386719 L 0 19.171875 L 0.828125 20 L 7.613281 13.21875 L 12.820312 18.421875 L 13.234375 18.007812 C 14.832031 16.410156 14.832031 13.808594 13.234375 12.207031 L 13.160156 12.136719 L 15.992188 7.945312 C 17.121094 8.628906 18.613281 8.484375 19.585938 7.511719 L 20 7.097656 Z M 12.777344 16.722656 L 3.277344 7.222656 C 4.414062 6.472656 5.960938 6.59375 6.960938 7.597656 L 12.402344 13.039062 C 13.40625 14.039062 13.527344 15.585938 12.777344 16.722656 Z M 12.316406 11.292969 L 8.707031 7.683594 L 12.828125 4.898438 L 15.101562 7.171875 Z M 16.269531 6.683594 L 13.316406 3.730469 C 12.777344 3.1875 12.660156 2.382812 12.972656 1.726562 L 18.273438 7.027344 C 17.617188 7.339844 16.8125 7.222656 16.269531 6.683594 Z M 16.269531 6.683594 "/>
                            <path style="fill:none;stroke-width:32.5;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(3.529412%,51.764706%,89.019608%);stroke-opacity:1;stroke-miterlimit:4;" d="M 18.5 18.5 L 493.5 494.5 " transform="matrix(0.0390625,0,0,0.0390625,0,0)"/>
                        </g>
                    </svg>

                </div>
            </template>
          </div>
          <!-- /Menu Toggle Buttons -->
        </div>
        <!-- /Header -->

        <!-- Header Shadow -->
        <div class="shadow-bottom" v-show="showShadowBottom" />

        <!-- Menu Items -->
        <VuePerfectScrollbar ref="verticalNavMenuPs" class="scroll-area-v-nav-menu pt-2" :settings="settings" @ps-scroll-y="psSectionScroll">
          <template v-for="(item, index) in menuItemsUpdated">

            <!-- Group Header -->
            <span v-if="item.header && !verticalNavMenuItemsMin" class="navigation-header truncate" :key="`header-${index}`">
              {{ item.header }}
            </span>
            <!-- /Group Header -->

            <template v-else-if="!item.header">

              <!-- Nav-Item -->
              <div class="vs-sidebar--item" v-if="item.changeState && !received_only_flg" :key="`btn-${index}`">
                <a href="#" v-on:click="changeState(item.changeState)">
                  <img style="width:18px;height:18px;" :src="require('@assets/images/pages/home/address.svg')">
                  <span style="margin-left:14px;" v-show="!verticalNavMenuItemsMin" class="truncate">{{ item.name }}</span>
                </a>
              </div>
              <div class="vs-sidebar--item" v-else-if="item.isLogout" :key="`btn-${index}`" style="padding: 0">
                <div class=" logout" :class="{'logout-width':!reduce}">
                  <a href="#" v-on:click="logoutNav()">
                    <img style="width:18px;height:18px;" :src="require('@assets/images/pages/home/logout.svg')">
                    <span style="margin-left:14px;" v-show="!verticalNavMenuItemsMin" class="truncate">{{ item.name }}</span>
                  </a>
                </div>
                  
              </div>

              <v-nav-menu-item
                v-else-if="(!received_only_flg || item.isReceivedOnly) && !item.submenu"
                :key="`item-${index}`"
                :index="index"
                :to="item.slug !== 'external' ? item.url : null"
                :href="item.slug === 'external' ? item.url : null"
                :icon="item.icon" :target="item.target"
                :isDisabled="item.isDisabled"
                :slug="item.slug"
                :customIcon="item.customIcon">
                  <span :style="item.customIcon?'margin-left:14px;':''" v-show="!verticalNavMenuItemsMin" class="truncate">{{ item.name }}</span>
                  <vs-chip class="ml-auto" :color="item.tagColor" v-if="item.tag && (isMouseEnter || !reduce)">{{ item.tag }}</vs-chip>
                  <vs-chip v-if="item.slug === 'received' && $store.state.circulars.unread" class="ml-auto inbox-unread-num" color="danger" :key="$store.state.circulars.unread">{{$store.state.circulars.unread}}</vs-chip>
                  <vs-chip v-if="item.slug === 'portal' && $store.state.notice.unread" class="ml-auto inbox-unread-num" color="danger">{{$store.state.notice.unread}}</vs-chip>
              </v-nav-menu-item>

              <!-- Nav-Group -->
              <template v-else>
                <v-nav-menu-group
                  v-if="!received_only_flg && item.submenu"
                  :key="`group-${index}`"
                  :openHover="openGroupHover"
                  :group="item"
                  :groupIndex="index"
                  :icon="item.icon"
                  :isDisabled="item.isDisabled"
                  :customIcon="item.customIcon"
                  :open="isGroupActive(item)" />
              </template>
              <!-- /Nav-Group -->
            </template>
          </template>
        </VuePerfectScrollbar>
        <!-- /Menu Items -->
      </div>
    </vs-sidebar>
    <!-- Swipe Gesture -->
    <div
      v-if="!isVerticalNavMenuActive"
      class="v-nav-menu-swipe-area"
      v-hammer:swipe.right="onSwipeAreaSwipeRight" />
    <!-- /Swipe Gesture -->
  </div>
</template>


<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import VNavMenuGroup from './VerticalNavMenuGroup.vue'
import VNavMenuItem from './VerticalNavMenuItem.vue'
import config from "../../../app.config";
import Axios from "axios";

export default {
  name: 'v-nav-menu',
  components: {
    VNavMenuGroup,
    VNavMenuItem,
    VuePerfectScrollbar,
  },
  props: {
    logo:             { type: String },
    isPublic:          { type: String, default: '' },
    openGroupHover:   { type: Boolean, default: false },
    parent:           { type: String },
    reduceNotRebound: { type: Boolean, default: true },
    navMenuItems:     { type: Array,   required: true },
    title:            { type: String },
  },
  data: () => ({
    clickNotClose       : false, // disable close navMenu on outside click
    isMouseEnter        : false,
    reduce              : false, // determines if navMenu is reduce  アイコン表示か名称表示かを切り替える
    showCloseButton     : false, // show close button in smaller devices
    settings            : {      // perfectScrollbar settings
      maxScrollbarLength: 60,
      wheelSpeed        : 1,
      swipeEasing       : true
    },
    showShadowBottom    : false,
    login_aws_url: `${config.AWS_API_URL}`,
    login_k5_url: `${config.K5_API_URL}`,
    app_env: `${config.APP_SERVER_ENV}`,

    isDisabledSide   : false,

  }),
  computed: {
    isGroupActive() {
      return (item) => {
        const path        = this.$route.fullPath
        const routeParent = this.$route.meta ? this.$route.meta.parent : undefined
        let open          = false

        let func = (item) => {
          if (item.submenu) {
            item.submenu.forEach((item) => {
              if (item.url && (path === item.url || routeParent === item.slug)) { open = true }
              else if (item.submenu) { func(item) }
            })
          }
        }
        func(item)
        return open
      }
    },
    menuItemsUpdated() {
      let clone = this.navMenuItems.slice();

      if(!this.loginUser || !this.loginUser.id) {
        clone = clone.filter(item => !item.loginRequired);
      }

        if(!this.loginUser || !this.loginUser.hr_user_flg) {
            clone = clone.filter(item => !item.hr_flgRequired);
        }

        if(!this.loginUser || !this.loginUser.hr_admin_flg) {
            clone = clone.filter(item => !item.hr_admin_flgRequired);
        }

        if(!this.loginUser || !this.loginUser.frm_srv_user_flg) {
            clone = clone.filter(item => !item.frm_srv_flgRequired);
        }
        if(!this.loginUser || !this.loginUser.expense_flg) {
            clone = clone.filter(item => !item.expense_flgRequired);
        }
        for(let [index, item] of this.navMenuItems.entries()) {
        if (item.header && item.items.length && (index || 1)) {
          let i = clone.findIndex(ix => ix.header === item.header)
          for(let [subIndex, subItem] of item.items.entries()) {
            clone.splice(i + 1 + subIndex, 0, subItem)
          }
        }

        for(let [index, item] of this.navMenuItems.entries()) {
          if (!item.submenu) {
            continue;
          }

          let cloneSub = item.submenu.slice();
          if(!this.loginUser || !this.loginUser.id) {
            cloneSub = cloneSub.filter(item => !item.loginRequired);
          }

          if(!this.loginUser || !this.loginUser.hr_user_flg) {
            cloneSub = cloneSub.filter(item => !item.hr_flgRequired);
          }

          if(!this.loginUser || !this.loginUser.hr_admin_flg) {
            cloneSub = cloneSub.filter(item => !item.hr_admin_flgRequired);
          }

          if(!this.loginUser || !this.loginUser.frm_srv_user_flg) {
            cloneSub = cloneSub.filter(item => !item.frm_srv_flgRequired);
          }
            if(!this.loginUser || !this.loginUser.expense_flg) {
                cloneSub = cloneSub.filter(item => !item.expense_flgRequired);
            }

          item.submenu = cloneSub;
        }

      }
      return clone
    },
    isVerticalNavMenuActive: {
      get()    { return this.$store.state.isVerticalNavMenuActive },
      set(val) { this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', val) }
    },
    layoutType() { return this.$store.state.mainLayoutType },
    reduceButton: {
      get()    { return this.$store.state.reduceButton },
      set(val) { this.$store.commit('TOGGLE_REDUCE_BUTTON', val) }
    },
    isVerticalNavMenuReduced()   { return Boolean(this.reduce && this.reduceButton) },
    verticalNavMenuItemsMin() { return this.$store.state.verticalNavMenuItemsMin },
    windowWidth()     { return this.$store.state.windowWidth },
    loginUser() {return JSON.parse(getLS('user'));},
    received_only_flg (){
       if (this.isPublic=='true'){
              return null
          }else{
              return JSON.parse(getLS('user')).received_only_flg
          }
    },
  },
  watch: {
    '$route'() {
      if (this.isVerticalNavMenuActive && this.showCloseButton) this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', false)
    },
    reduce(val) {
      if(this.isDisabledSide){
        return  //PAC_5-594 サイドバー非表示の場合処理終了
      }
        const verticalNavMenuWidth = val ? "reduced" : "default"
        this.$store.dispatch('updateVerticalNavMenuWidth', verticalNavMenuWidth)

        setTimeout(function() {
          window.dispatchEvent(new Event('resize'))
        }, 100)
    },
    layoutType()   { this.setVerticalNavMenuWidth() },
    reduceButton() { this.setVerticalNavMenuWidth() },
    windowWidth()  { this.setVerticalNavMenuWidth() }
  },
  methods: {
    // handleWindowResize(event) {
    //   this.windowWidth = event.currentTarget.innerWidth;
    //   this.setVerticalNavMenuWidth()
    // },
    onSwipeLeft() {
      if (this.isVerticalNavMenuActive && this.showCloseButton) this.isVerticalNavMenuActive = false
    },
    onSwipeAreaSwipeRight() {
      if (!this.isVerticalNavMenuActive && this.showCloseButton) this.isVerticalNavMenuActive = true
    },
    psSectionScroll() {
      this.showShadowBottom = this.$refs.verticalNavMenuPs.$el.scrollTop > 0 ? true : false
    },
    mouseEnter() {
      if (this.reduce) this.$store.commit('UPDATE_VERTICAL_NAV_MENU_ITEMS_MIN', false)
      this.isMouseEnter = true
    },
    mouseLeave() {
      if (this.reduce) this.$store.commit('UPDATE_VERTICAL_NAV_MENU_ITEMS_MIN', true)
      this.isMouseEnter = false;
    },
    changeState(stateName){
      this.$store.commit('SET_ACTIVATE_STATE', stateName);
    },
    setVerticalNavMenuWidth() {

      if(this.windowWidth > 1200) {
        this.isDisabledSide = false;
        if(this.layoutType === 'vertical') {
          // Set reduce
          this.reduce = this.reduceButton ? true : false;

          // Open NavMenu
          this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', true)

          // Set Menu Items Only Icon Mode
          const verticalNavMenuItemsMin = (this.reduceButton && !this.isMouseEnter) ? true : false
          this.$store.commit('UPDATE_VERTICAL_NAV_MENU_ITEMS_MIN', verticalNavMenuItemsMin)

          // Menu Action buttons
          this.clickNotClose   = true
          this.showCloseButton = false

          const verticalNavMenuWidth   = this.isVerticalNavMenuReduced ? "reduced" : "default"
          this.$store.dispatch('updateVerticalNavMenuWidth', verticalNavMenuWidth)

          return
        }
      }else{
        // Close NavMenu
        this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', false)
        this.isDisabledSide = true;

      // Reduce button
      if (this.reduceButton) this.reduce = false

        // Menu Action buttons
        this.showCloseButton = true
        this.clickNotClose   = false

        // Update NavMenu Width
        this.$store.dispatch('updateVerticalNavMenuWidth', 'no-nav-menu')

        // Remove Only Icon in Menu
        this.$store.commit('UPDATE_VERTICAL_NAV_MENU_ITEMS_MIN', false)
      }
    },
    toggleReduce(val) {
      this.reduceButton = val
      this.setVerticalNavMenuWidth()
    },
    async logout() {
      const envFlg = parseInt(localStorage.getItem('envFlg'));
      if(envFlg){
        window.location.href = this.login_k5_url;
      }else{
        window.location.href = this.login_aws_url;
      }

    },
    async logoutNav() {
	      if( 1 == this.$store.state.setting.withdrawal_caution ) {
          this.$store.commit('home/setCloseCheck', true );
          if( false == window.confirm('行った変更が保存されない可能性があります。') ) {
            return;
          }
          this.$store.commit('home/setCloseCheck', false );
        }
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
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('expires_time');
        localStorage.removeItem('loggedInAdmin');
        localStorage.removeItem('branding');
        localStorage.removeItem('limit');
        await this.setDomainGroupware();
        window.location.href = return_url;
        this.$ls.set(`logout`, Date.now());
    },

      setDomainGroupware(){
          let domain = config.GROUPWARE_DOMAIN;
          this.$cookie.set('accessToken', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
          this.$cookie.set('refreshToken', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
          this.$cookie.set('userRoles', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
          this.$cookie.set('userProfile', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
          this.$cookie.set('dateGetToken', '', {timeExpires : this.$moment().subtract(1, 'days'), SameSite:"None", Secure:false, domain:domain});
      },
  },
  mounted() {
    this.setVerticalNavMenuWidth()
  },
  updated: function () {
    this.$nextTick(function () {
        if (window.document.documentMode) {
            $('.vs-sidebar--item svg').attr('viewBox', '0 0 8 24');
        }
    })
  }
}

</script>


<style lang="scss">
@import "@sass/vuexy/components/verticalNavMenu.scss";
.logout-width{
  width: 260px!important;
}
</style>
