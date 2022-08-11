
<template>
<div class="relative">
  <div class="vx-navbar-wrapper" :class="classObj">
    <vs-navbar class="vx-navbar navbar-custom navbar-skelton" :color="navbarColorLocal">

      <!-- SM - OPEN SIDEBAR BUTTON -->
      <feather-icon class="sm:inline-flex xl:hidden cursor-pointer mr-1" icon="MenuIcon" @click.stop="showSidebar"></feather-icon>

        <p class="font-semibold" :style="`color: ${navbarFontColor}`">{{ routeTitle }}</p>
      <vs-spacer />


      <!-- SEARCHBAR -->
      <div class="search-full-container w-full h-full absolute left-0" :class="{'flex': showFullSearch}" v-show="showFullSearch">
          <vx-auto-suggest
            class="w-full"
            inputClassses="w-full vs-input-no-border vs-input-no-shdow-focus"
            :autoFocus="showFullSearch"
            :data="navbarSearchAndPinList"
            icon="SearchIcon"
            placeholder="Search..."
            ref="navbarSearch"
            @closeSearchbar="showFullSearch = false"
            @selected="selected"
            background-overlay />
          <div class="absolute right-0 h-full z-50">
              <feather-icon icon="XIcon" class="px-4 cursor-pointer h-full close-search-icon" @click="showFullSearch = false"></feather-icon>
          </div>
      </div>
      <!--<feather-icon icon="SearchIcon" @click="showFullSearch = true" class="cursor-pointer navbar-fuzzy-search mr-4"></feather-icon>-->

      <!-- USER META -->
        <div class="the-navbar__user-meta flex items-center" v-if="userInfo">
            <div class="text-right leading-tight block">
                <div class="con-img ml-3 pt-3 pb-3">
                <p class="font-semibold" :style="`color: ${navbarFontColor}`">{{ userInfo.name }}</p>
                </div>
            </div>
        </div>

    </vs-navbar>
  </div>
</div>
</template>

<script>
import VxAutoSuggest from '@/components/vx-auto-suggest/VxAutoSuggest.vue';
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import draggable from 'vuedraggable';
import config from "../../../app.config";
import Axios from "axios";


export default {
    name: "the-navbar",
    props: {
        navbarColor: {
            type: String,
            default: "#fff",
        },
        navbarFontColor: {
          type: String,
          default: "#fff",
        },
        routeTitle: {
          type: String,
          default: ''
        },
        userInfo: {
          type: Object,
          default: null
        }
    },
    data() {
        return {
            navbarSearchAndPinList: this.$store.state.navbarSearchAndPinList,
            searchQuery: '',
            showFullSearch: false,
            settings: { // perfectscrollbar settings
                maxScrollbarLength: 60,
                wheelSpeed: .60,
            },
            autoFocusSearch: false,
            showBookmarkPagesDropdown: false,
        }
    },
    watch: {
        '$route'() {
            if (this.showBookmarkPagesDropdown) this.showBookmarkPagesDropdown = false
        }
    },
    computed: {
        navbarColorLocal() {
          return this.$store.state.theme === "dark" ? "#10163a" : this.navbarColor
        },
        // HELPER
        verticalNavMenuWidth() {
            return this.$store.state.verticalNavMenuWidth
        },
        windowWidth() {
            return this.$store.state.windowWidth
        },

        // NAVBAR STYLE
        classObj() {
            if (this.verticalNavMenuWidth == "default") return "navbar-default"
            else if (this.verticalNavMenuWidth == "reduced") return "navbar-reduced"
            else return "navbar-full"
        },

        // BOOKMARK & SEARCH
        data() {
            return this.$store.state.navbarSearchAndPinList;
        },
        starredPages() {
            return this.$store.state.starredPages;
        },
        starredPagesLimited: {
            get() {
                return this.starredPages.slice(0, 10);
            },
            set(list) {
                this.$store.dispatch('arrangeStarredPagesLimited', list);
            }
        },
        starredPagesMore: {
            get() {
                return this.starredPages.slice(10);
            },
            set(list) {
                this.$store.dispatch('arrangeStarredPagesMore', list);
            }
        },

        // PROFILE
        activeUserImg() {
            return this.$store.state.AppActiveUser.photoURL;
        }
    },
    methods: {
        showSidebar() {
            this.$store.commit('TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE', true);
        },
        selected(item) {
            this.$router.push(item.url).catch(() => {})
            this.showFullSearch = false;
        },
        actionClicked(item) {
            // e.stopPropogation();
            this.$store.dispatch('updateStarredPage', { index: item.index, val: !item.highlightAction });
        },
        showNavbarSearch() {
            this.showFullSearch = true;
        },
        showSearchbar() {
            this.showFullSearch = true;
        },
        elapsedTime(startTime) {
            let x = new Date(startTime);
            let now = new Date();
            var timeDiff = now - x;
            timeDiff /= 1000;

            var seconds = Math.round(timeDiff);
            timeDiff = Math.floor(timeDiff / 60);

            var minutes = Math.round(timeDiff % 60);
            timeDiff = Math.floor(timeDiff / 60);

            var hours = Math.round(timeDiff % 24);
            timeDiff = Math.floor(timeDiff / 24);

            var days = Math.round(timeDiff % 365);
            timeDiff = Math.floor(timeDiff / 365);

            var years = timeDiff;

            if (years > 0) {
                return years + (years > 1 ? ' Years ' : ' Year ') + 'ago';
            } else if (days > 0) {
                return days + (days > 1 ? ' Days ' : ' Day ') + 'ago';
            } else if (hours > 0) {
                return hours + (hours > 1 ? ' Hrs ' : ' Hour ') + 'ago';
            } else if (minutes > 0) {
                return minutes + (minutes > 1 ? ' Mins ' : ' Min ') + 'ago';
            } else if (seconds > 0) {
                return seconds + (seconds > 1 ? ' sec ago' : 'just now');
            }

            return 'Just Now'
        },
        async logout() {
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
          window.location.href = return_url;
          this.$ls.set(`logout`, Date.now());
        },
        outside: function() {
            this.showBookmarkPagesDropdown = false
        },

        // Method for creating dummy notification time
        randomDate({hr, min, sec}) {
          let date = new Date()

          if(hr) date.setHours(date.getHours() - hr)
          if(min) date.setMinutes(date.getMinutes() - min)
          if(sec) date.setSeconds(date.getSeconds() - sec)

          return date
        }
    },
    directives: {
        'click-outside': {
            bind: function(el, binding) {
                const bubble = binding.modifiers.bubble
                const handler = (e) => {
                    if (bubble || (!el.contains(e.target) && el !== e.target)) {
                        binding.value(e)
                    }
                }
                el.__vueClickOutside__ = handler
                document.addEventListener('click', handler)
            },

            unbind: function(el) {
                document.removeEventListener('click', el.__vueClickOutside__)
                el.__vueClickOutside__ = null

            }
        }
    },
    components: {
        VxAutoSuggest,
        VuePerfectScrollbar,
        draggable
    },
}
</script>
