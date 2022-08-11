<template>
  <div>
    <div id="portal-index" v-if="!isMobile">
        <grid-layout :layout.sync="currentComponentList"
                     :col-num="colNum"
                     :row-height="rowHeight"
                     :col-width="colWidth"
                     :is-draggable="editStatus"
                     :is-resizable="editStatus"
                     :vertical-compact="verticalCompact"
                     :use-css-transforms="useCssTransforms"
                     :use-style-cursor="false"
                     @layout-updated="layoutUpdatedEvent"
                     @layout-ready="layoutReadyEvent"
        >
            <template v-for="(item, index) in currentComponentList">
                <grid-item v-if="!isMustLoad(item)" 
                           :x="item.x"
                           :y="item.y"
                           :w="item.w"
                           :h="item.h"
                           :i="item.i"
                           :key="item.i"
                           :static="item.static"
                           :is-draggable="itemIsDraggable(item.name)"
                           :is-resizable="itemIsResizable(item.name)"
                           :minW="item.minW"
                           :minH="item.minH"
                           :maxW="item.maxW"
                           :maxH="item.maxH"
                           @container-resized="containerResizedEvent"
                           @resize="resizeEvent"
                           @resized="resizedEvent"
                           @move="moveEvent"
                           @moved="movedEvent"
                >
                    <div v-if="item.resizing" class="mask-layer"></div>
                    <attendance-system v-if="item.name===PORTAL_COMPONENT.TIME_CARD_ATTENDANCE" @hiddenAppPortal="onHiddenAppPortal(index)" :src="urlAttendanceSystem" class="mb-1 attendance-system"></attendance-system>
                    <favorite ref="" v-if="item.name===PORTAL_COMPONENT.FAVORITE && $store.state.portal.currentMyPage" @hiddenAppPortal="onHiddenAppPortal(index)" :class="item.name"></favorite>
                    <time-card v-if="item.name===PORTAL_COMPONENT.TIME_CARD" @hiddenAppPortal="onHiddenAppPortal(index)"></time-card>
                    <vx-card v-if="item.name===PORTAL_COMPONENT.SCHEDULER" :class="item.name">
                        <HeaderComponent title="スケジューラ" :urlGroupware="'/groupware/calendar'" @hiddenAppPortal="onHiddenAppPortal(index)"></HeaderComponent>
                        <iframe-groupware ref ="" :width="item.w" :height="item.h" :src="urlCalendar"></iframe-groupware>
                    </vx-card>
                    <vx-card v-if="item.name===PORTAL_COMPONENT.BULLETIN_BOARD" :class="item.name" :id="[item.name]">
                        <HeaderComponent title="掲示板" urlGroupware="/groupware/bulletin" @hiddenAppPortal="onHiddenAppPortal(index)"></HeaderComponent>
                        <bbs class="mb-4"></bbs>
                    </vx-card>
                    <vx-card v-if="item.name===PORTAL_COMPONENT.FAQ_BULLETIN_BOARD" :class="item.name" :id="[item.name]">
                        <HeaderComponent title="サポート掲示板" urlGroupware="/groupware/faq_bulletin" @hiddenAppPortal="onHiddenAppPortal(index)"></HeaderComponent>
                        <faq-bbs class="mb-4"></faq-bbs>
                    </vx-card>
                    <circular-portal ref="" v-if="item.name===PORTAL_COMPONENT.CIRCULAR" @hiddenAppPortal="onHiddenAppPortal(index)" :class="item.name"></circular-portal>
                    <special-portal ref="" v-if="item.name===PORTAL_COMPONENT.SPECIAL" @hiddenAppPortal="onHiddenAppPortal(index)" :class="item.name"></special-portal>
                    <file-mail-card ref="" v-if="item.name===PORTAL_COMPONENT.FILE_MAIL" @hiddenAppPortal="onHiddenAppPortal(index)" :class="item.name"></file-mail-card>
                  
                    <vx-card v-if="item.name===PORTAL_COMPONENT.RECEIVE_PLAN" :class="item.name" :id="[item.name]">
                      <HeaderComponent title="受信専用プラン" urlGroupware="/groupware/receive_plan" @hiddenAppPortal="onHiddenAppPortal(index)"></HeaderComponent>
                      <receive-plan-component class="mb-4"></receive-plan-component>
                    </vx-card>

                    <vx-card v-if="item.name===PORTAL_COMPONENT.TO_DO_LIST" :class="item.name" :id="[item.name]">
                        <HeaderComponent title="ToDoリスト" urlGroupware="/groupware/to-do-list" @hiddenAppPortal="onHiddenAppPortal(index)"></HeaderComponent>
                        <to-do-list class="mb-4" :isMypage="true"></to-do-list>
                    </vx-card>
                </grid-item>
    
                <grid-item v-if="isMustLoad(item)" v-show="item.hasData" 
                           :x="item.x"
                           :y="item.y"
                           :w="item.w"
                           :h="item.h"
                           :i="item.i"
                           :key="item.i"
                           :static="item.static"
                           :is-draggable="itemIsDraggable(item.name)"
                           :is-resizable="itemIsResizable(item.name)"
                           :minW="item.minW"
                           :minH="item.minH"
                           :maxW="item.maxW"
                           :maxH="item.maxH"
                           @container-resized="containerResizedEvent"
                           @resize="resizeEvent"
                           @resized="resizedEvent"
                           @move="moveEvent"
                           @moved="movedEvent"
                >
                    <div v-if="item.resizing" class="mask-layer"></div>
                    <top-menu v-if="item.name===PORTAL_COMPONENT.TOP_MENU" class="top-menu"></top-menu>
                    <top-screen @dangerNoticeResize="dangerNoticeResize" v-if="item.name===PORTAL_COMPONENT.TOP_SCREEN" ref=""></top-screen>
                    <notification v-if="item.name===PORTAL_COMPONENT.NOTIFICATION"/>
                    <movie @changeHasData="changeHasData" v-if="item.name===PORTAL_COMPONENT.MOVIE"/>
                    <advertisement @changeHasData="changeHasData" v-if="item.name===PORTAL_COMPONENT.ADVERTISEMENT"/>
                    <customize-area @changeHasData="changeHasData" v-if="item.name===PORTAL_COMPONENT.CUSTOMIZE_AREA"/>
                </grid-item>
            </template>
        </grid-layout>
    </div>


    <!-- Mobile page --> 
    <div id="portal-index-mobile" v-else>

        <!-- Label page 
        <div class="portal-index-mobile-label">マイページ</div>
        -->
        
        <!-- Last login -->
        <div class="last-login">前回ログイン日時 {{lastLogin}}</div>

        <!-- Top menu -->
        <top-menu v-if="currentComponentListName.includes(PORTAL_COMPONENT.TOP_MENU)" class="top-menu-mobile" :isMobile="isMobile"></top-menu>

        <!-- Top screen -->
        <top-screen @dangerNoticeResize="dangerNoticeResize" v-if="currentComponentListName.includes(PORTAL_COMPONENT.TOP_SCREEN)" :isMobile="isMobile"></top-screen>

        <!-- BULLETIN -->
        <div v-if="currentComponentListName.includes(PORTAL_COMPONENT.BULLETIN_BOARD)">
          <div class="topic-mobile-label">掲示板</div>
          <bbs :isMypage="true"></bbs>
        </div>

        <!-- Notification -->
        <div v-if="currentComponentListName.includes(PORTAL_COMPONENT.NOTIFICATION)" class="mt-1">
          <div class="notification-mobile-label mb-2">お知らせ・リリース情報</div>
          <notification />
        </div>

    </div>  

  </div>
</template>
<script>
import {mapState, mapActions} from "vuex";
import TopScreen from "../../components/portal/TopScreen";
import Notification from "../../components/portal/Notification";
import Advertisement from "../../components/portal/Advertisement";
import Movie from "../../components/portal/Movie";
import CustomizeArea from  "../../components/portal/CustomizeArea";
import HeaderComponent from "../../components/portal/HeaderComponent";
import {PORTAL_COMPONENT} from '../../enums/portal_component';
import TopMenu from "../../components/portal/TopMenu";
import { GridLayout, GridItem } from "vue-grid-layout"
import config from "../../app.config";
import Bbs from "../../components/portal/Bbs";
import TimeCard from "../../components/portal/TimeCard";
import FileMailCard from "../../components/portal/FileMailCard";
import FaqBbs from "../../components/portal/FaqBbs";
import AttendanceSystem from "../../components/portal/AttendanceSystem";
import Favorite from "../../components/portal/Favorite";
import IframeGroupware from "../../components/portal/IframeGroupware";
import CircularPortal from "../../components/portal/CircularPortal";
import SpecialPortal from "../../components/portal/SpecialPortal";
import ToDoList from "../../components/portal/ToDoList";
import {cloneDeep} from "lodash/lang";
import ReceivePlanComponent from "../../components/portal/ReceivePlanComponent";

export default {
    components: {
        ReceivePlanComponent,
        HeaderComponent,
        TopScreen,
        TopMenu,
        Notification,
        Advertisement,
        Movie,
        CustomizeArea,
        GridLayout,
        GridItem,
        Favorite,
        IframeGroupware,
        CircularPortal,
        Bbs,
        FaqBbs,
        TimeCard,
        FileMailCard,
        SpecialPortal,
        AttendanceSystem,
        ToDoList,
    },
    data() {
        return {
            currentLayout: [],
            currentPage: {},
            actionLog: '',
            PORTAL_COMPONENT: PORTAL_COMPONENT,
            urlCalendar: `${config.GROUPWARE_URL}/embed/calendar`,
            urlAttendanceSystem: 'https://swk.shachihata.com/swk',
            colNum: 32,
            rowHeight: 5,
            colWidth: 50,
            verticalCompact: true,
            useCssTransforms: false,
            needChange: false,
            currentComponentList: [
                {x: 0, y: 0, w: 24, h: 5, i: '0', name: PORTAL_COMPONENT.TOP_MENU, static: false, minW: 16, minH:5, maxW: 32, maxH:5, show: true, available: true, resizing: false, hasData: true, moved: false,},
                {x: 0, y: 5, w: 24, h: 6, i: '1', name: PORTAL_COMPONENT.TOP_SCREEN, static: false, minW: 16, minH:6, maxW: 32, maxH:6, show: true, available: true, resizing: false, hasData: true, moved: false,},
            ],
            currentComponentListName: [],
            windowResizeEvent: null,
            movieKey: 1,
            advertisementKey: 1,
            customizeAreaKey: 1,
            optionFlg : JSON.parse(getLS('user')).option_flg,
            isMobile: false,
            lastLogin: ''
        }
    },
    computed: {
        ...mapState({
            listMyPages: state => state.portal.listMyPages,
            checkCalendarApp: state => state.groupware.checkCalendarApp,
            checkBulletinBoardApp: state => state.groupware.checkBulletinBoardApp,
            checkFaqBulletinBoardApp: state => state.groupware.checkFaqBulletinBoardApp,
            currentMyPage:state => state.portal.currentMyPage,
        }),
        isMustLoad() {
            return (item) => {
                let mst_load = false;
                switch (item.name) {
                    case PORTAL_COMPONENT.TOP_MENU:
                        mst_load = true;
                        break;
                    case PORTAL_COMPONENT.TOP_SCREEN:
                        mst_load = true;
                        break;
                    case PORTAL_COMPONENT.NOTIFICATION:
                        mst_load = true;
                        break;
                    case PORTAL_COMPONENT.MOVIE:
                        mst_load = true;
                        break;
                    case PORTAL_COMPONENT.ADVERTISEMENT:
                        mst_load = true;
                        break;
                    case PORTAL_COMPONENT.CUSTOMIZE_AREA:
                        mst_load = true;
                        break;
                    default:
                        break;
                }
                return mst_load;
            }
        },
        editStatus() {
            return this.$store.state.portal.editStatus;
        },
        currentComponent: {
            get() {
                return this.$store.state.portal.currentComponent;
            },
            set(layout) {
                this.$store.commit('portal/updateCurrentComponent', layout);
            }
        },
        itemIsDraggable() {
            return function (name) {
                if (name === PORTAL_COMPONENT.TOP_MENU || name === PORTAL_COMPONENT.TOP_SCREEN) {
                    return false;
                } else {
                    return this.editStatus;
                }
            }
        },
        itemIsResizable() {
            return function (name) {
                return this.editStatus;
            }
        },
        getComponentByI() {
            return function (i) {
              return this.currentComponentList.find((item)=> {
                return item.i === i
              })
            }
        },
        getTopMenuItem: {
            get () {
                return this.currentComponentList.find((item)=> {
                    return item.name === PORTAL_COMPONENT.TOP_MENU;
                })
            }
        },
        getTopScreenItem: {
            get () {
                return this.currentComponentList.find((item)=> {
                  return item.name === PORTAL_COMPONENT.TOP_SCREEN;
                })
            }
        },
        limit(){
          return JSON.parse(getLS('limit'));
        }
    },
    methods: {
        ...mapActions({
            updateMyPageInBackground: "portal/updateMyPageInBackground",
            getMyPages: "portal/getMyPages",
            updateMyPage: "portal/updateMyPage",
            addLogOperation: "logOperation/addLog",
            getLastLogin: "logOperation/getLastLogin",
        }),
        applyCurrentLayout: function () {
            if (this.currentMyPage) {
                this.currentPage = this.listMyPages.find(item => item.id === parseInt(this.currentMyPage));
                if (!this.currentPage) {
                    this.currentPage = this.listMyPages[0];
                }
                const currentComponentList = [];
                const currentComponentListName = [];
                this.currentLayout = JSON.parse(this.currentPage.layout);
                this.currentLayout.forEach((item) => {
                    const componentItem = item;
                    this.filterData(componentItem);
                    if (componentItem.available && item.show) {
                        currentComponentList.push(componentItem);
                        currentComponentListName.push(componentItem.name);
                    }
                })
                this.currentComponentList = currentComponentList;
                this.currentComponentListName = currentComponentListName;
            }
            this.$store.dispatch('updateLoading', false);
        },
        async onHiddenAppPortal(index) {
            let appSelect = this.currentComponentList[index].name;
            this.actionLog = '';
            if (this.currentComponentList[index].show) {
                const currentLayout = cloneDeep(Object.values(this.currentLayout));
                currentLayout.map((item) => {
                    if (item.name === appSelect) {
                        item.show = false;
                    }
                })
                this.currentLayout = currentLayout;
                this.$store.commit('portal/updateCurrentLayout', currentLayout);
                this.currentComponentList.splice(index, 1);
                switch (appSelect) {
                    case PORTAL_COMPONENT.SCHEDULER:
                        this.actionLog = 'pr1-09-portal-hide-calendar';
                        break;
                    case PORTAL_COMPONENT.BULLETIN_BOARD:
                        this.actionLog = 'pr1-08-portal-hide-bulletin-board';
                        break;
                    case PORTAL_COMPONENT.FAVORITE:
                        this.actionLog = 'pr1-06-portal-hide-favorite';
                        break;
                    case PORTAL_COMPONENT.CIRCULAR:
                        this.actionLog = 'pr1-07-portal-hide-shachihata-cloud';
                        break;
                    case PORTAL_COMPONENT.TIME_CARD:
                        this.actionLog = 'pr1-17-portal-hide-time-card';
                        break;
                    case PORTAL_COMPONENT.TIME_CARD_ATTENDANCE:
                        this.actionLog = 'pr1-17-portal-hide-time-card';
                        break;
                    case PORTAL_COMPONENT.FILE_MAIL:
                        this.actionLog = '';
                        break;
                    case PORTAL_COMPONENT.SPECIAL:
                        this.actionLog = '';
                        break;
                    case PORTAL_COMPONENT.FAQ_BULLETIN_BOARD:
                        this.actionLog = 'pr1-20-portal-hide-faq-bulletin-board';
                        break;
                    case PORTAL_COMPONENT.RECEIVE_PLAN:
                        this.actionLog = 'pr1-21-portal-hide-receive-plan';
                        break;
                    case PORTAL_COMPONENT.TO_DO_LIST:
                      this.actionLog = 'pr1-23-portal-hide-to-do-list';
                        break;
                    default:
                        break;
                }
                if (this.editStatus) return false;
                await this.saveCurrentLayout();
                this.dangerNoticeResize();
            }
        },
        saveCurrentLayout: async function () {
            const currentLayout = this.currentLayout;
            let myPage = {
                id: this.currentPage.id,
                mst_mypage_layout_id: this.currentPage.mst_mypage_layout_id,
                page_name: this.currentPage.page_name,
                layout: JSON.stringify(currentLayout),
            };
            if (this.actionLog === '') {
                this.actionLog = 'pr1-03-portal-mypage-save-setting';
            }
            await this.updateMyPage(myPage).then(
                response => {
                    if (response.success) {
                        this.addLogOperation({action: this.actionLog, result: 0});
                    } else {
                        this.addLogOperation({action: this.actionLog, result: 1});
                    }
                },
                error => {
                    this.addLogOperation({action: this.actionLog, result: 1});
                });
            await this.getMyPages();
        },
        layoutReadyEvent: function(newLayout){

        },
        layoutUpdatedEvent: function(newLayout){
            if (this.currentLayout && this.currentLayout.length > 0) {
                const currentLayout = cloneDeep(Object.values(this.currentLayout));
                currentLayout.map((layout) => {
                    newLayout.find((item) => {
                        if (layout.name === item.name) {
                            layout.x = item.x;
                            layout.y = item.y
                            layout.w = item.w;
                            layout.h = item.h;
                        }
                    })
                    return layout;
                })
                this.currentLayout = currentLayout;
                this.$store.commit('portal/updateCurrentLayout', currentLayout);
            }
        },
        containerResizedEvent: function(i, newH, newW, newHPx, newWPx){
            this.currentComponentList.filter((item)=> {
                if (item.i === i && item.name === PORTAL_COMPONENT.TOP_SCREEN) {
                    this.dangerNoticeResize();
                }
            })
        },
        dangerNoticeResize: function () {
            let height = $('#portal-index .vue-grid-layout .comp-portal-announces:not(.top-menu) > div > div.notice .text-danger').height();
            if (height && height > 0) {
                let rowNum = Math.ceil(height / this.rowHeight / 3) + 6;
                this.currentComponentList.map(function (item) {
                    if (item.name === PORTAL_COMPONENT.TOP_SCREEN) {
                        item.h = rowNum;
                    }
                })
                this.$nextTick(() => {
                    window.dispatchEvent(this.windowResizeEvent);
                })
            }
        },
        filterData(item) {
            let available = true;
            let hasData = false;
            switch (item.name) {
                case PORTAL_COMPONENT.TOP_MENU:
                    hasData = true;
                    break;
                case PORTAL_COMPONENT.TOP_SCREEN:
                    hasData = true;
                    break;
                case PORTAL_COMPONENT.NOTIFICATION:
                    hasData = true;
                    break;
                case PORTAL_COMPONENT.MOVIE:
                    hasData = this.$store.state.portal.hasData[item.name] ? this.$store.state.portal.hasData[item.name] : false;
                    break;
                case PORTAL_COMPONENT.ADVERTISEMENT:
                    hasData = this.$store.state.portal.hasData[item.name] ? this.$store.state.portal.hasData[item.name] : false;
                    break;
                case PORTAL_COMPONENT.CUSTOMIZE_AREA:
                    hasData = this.$store.state.portal.hasData[item.name] ? this.$store.state.portal.hasData[item.name] : false;
                    break;
                case PORTAL_COMPONENT.SCHEDULER:
                    available = this.$store.state.groupware.checkCalendarApp;
                    break;
                case PORTAL_COMPONENT.BULLETIN_BOARD:
                    available = this.$store.state.groupware.checkBulletinBoardApp;
                    break;
                case PORTAL_COMPONENT.FAVORITE:
                    break;
                case PORTAL_COMPONENT.CIRCULAR:
                    available = !this.optionFlg;
                    break;
                case PORTAL_COMPONENT.TIME_CARD:
                    available = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.attendance_system_flg !== 1 && this.$store.state.groupware.checkTimeCardApp;
                    break;
                case PORTAL_COMPONENT.TIME_CARD_ATTENDANCE:
                    available = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.attendance_system_flg === 1 && this.$store.state.groupware.checkTimeCardApp;
                    break;
                case PORTAL_COMPONENT.FILE_MAIL:
                    available = this.$store.state.groupware.checkFileMailApp;
                    break;
                case PORTAL_COMPONENT.SPECIAL:
                    available = this.$store.state.special.checkSpecialSend && !this.optionFlg;
                    break;
                case PORTAL_COMPONENT.FAQ_BULLETIN_BOARD:
                    available = this.$store.state.groupware.checkFaqBulletinBoardApp;
                    break;
                case PORTAL_COMPONENT.RECEIVE_PLAN:
                    available = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.receive_plan_flg == 1 && this.limit.limit_receive_plan_flg
                    break
                case PORTAL_COMPONENT.TO_DO_LIST:
                    available = this.$store.state.groupware.checkToDoList;
                    break
                default:
                    break;
            }
            item.available = available;
            item.resizing = false;
            item.hasData = hasData;
        },
        changeHasData: function (component_name, hasData) {
            this.currentComponentList.forEach((item, index) => {
                if (item.name === component_name) {
                    if (hasData === false) {
                        this.currentComponentList.splice(index, 1);
                    } else {
                        item.hasData = hasData;
                        this.$store.commit('portal/updateHasData', {
                            name: component_name,
                            hasData: hasData,
                        })
                    }
                }
            })
            window.dispatchEvent(this.windowResizeEvent);
        },
        resizeEvent: function(i, newH, newW, newHPx, newWPx) {
            let item = this.getComponentByI(i);
            if (!item) return false;
            item.resizing = true;
            if (item.name === PORTAL_COMPONENT.TOP_SCREEN) {
                let left_w = 0;
                this.currentComponentList.filter((obj)=> {
                    if (obj.y < item.y && obj.x > 0 && (left_w === 0 || left_w > obj.x)) {
                      left_w = obj.x;
                    }
                })
                if (left_w > 0) item.maxW = left_w;
                this.dangerNoticeResize();
                if (item.h > item.maxH) {
                    item.maxH = item.h;
                    item.minH = item.h;
                }
            }
        },
        resizedEvent: function(i, newH, newW, newHPx, newWPx) {
            let item = this.getComponentByI(i);
            if (!item) return false;
            item.resizing = false;
            if (item.name === PORTAL_COMPONENT.TOP_SCREEN) {
                setTimeout(()=>{
                  this.dangerNoticeResize();
                  item.maxW = 32;
                  item.maxH = 6;
                  item.minH = 6;
                }, 50);
            }
        },
        moveEvent: function(i, newH, newW, newHPx, newWPx) {
          let item = this.getComponentByI(i);
          if (!item) return false;
          if (item.name !== PORTAL_COMPONENT.TOP_MENU && item.name !== PORTAL_COMPONENT.TOP_SCREEN) {
            this.getTopMenuItem.static = true;
            this.getTopScreenItem.static = true;
          }
        },
        movedEvent: function(i, newH, newW, newHPx, newWPx) {
          let item = this.getComponentByI(i);
          if (!item) return false;
          if (item.name !== PORTAL_COMPONENT.TOP_MENU && item.name !== PORTAL_COMPONENT.TOP_SCREEN) {
            setTimeout(()=>{
              this.getTopMenuItem.static = false;
              this.getTopScreenItem.static = false;
            }, 50)
          }
        },
    },
    watch: {
        '$store.state.portal.listMyPages': function() {
            if (this.$store.state.portal.changePageNameFlg) {
                this.$store.commit('portal/updateChangePageNameFlg', false);
                return;
            }
            this.applyCurrentLayout();
            this.dangerNoticeResize();
        },
        '$store.state.portal.currentComponent': function () {
            this.currentLayout = this.$store.state.portal.currentLayout;
            this.currentComponentList = cloneDeep(Object.values(this.currentComponent));
            let screen_w = 0,
              screen_y = 0;
            this.currentComponentList.map((item) => {
              if (item.name === PORTAL_COMPONENT.TOP_SCREEN) {
                screen_w = item.w;
                screen_y = item.y;
              }
            })
            this.currentComponentList.map((item) => {
              if (item.y < screen_y && item.x < screen_w) {
                item.y = screen_y;
              }
            })
            this.$nextTick(()=>{
                this.changeHasData('', '');
                this.dangerNoticeResize();
            })
        },
        '$store.state.portal.changeTemplateFlg': function (val) {
            if (val) {
                this.dangerNoticeResize();
                this.$store.commit('portal/updateChangeTemplateFlg', false);
            }
        },
        '$store.state.portal.changePermission': function (val) {
            if (val) {
                this.applyCurrentLayout();
                this.dangerNoticeResize();
                this.$store.commit('portal/updatePermission', false);
            }
        },
    },
    async mounted() {
        if (this.$route.path.includes('/portal') && this.$route.path !== '/portal') {
            this.addLogOperation({action: 'pr1-01-portal-display-screen', result: 0})
        }

        if( this.isMobile ){
          let $this = this;
          this.getLastLogin().then(function (res) {
            if (res && res.create_at) {
                let date = $this.$moment(res.create_at, 'YYYY-MM-DD HH:mm:ss');
                $this.lastLogin = date.format('YYYY/MM/DD HH:mm');
            } else {
                $this.lastLogin = '';
            }
          });
        }
    },
    async created() {

        // Check Mobile
        if (
          /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
            navigator.userAgent
          )
        ) {
          this.isMobile = true;
        }

        this.$store.dispatch('updateLoading', true);
        this.$store.commit('portal/updateHasData', {});
        this.$store.commit('portal/setEditStatus', false);
        this.$store.commit('portal/updateChangePageNameFlg', false);
        this.windowResizeEvent = window.document.createEvent('UIEvents');
        this.windowResizeEvent.initUIEvent('resize', true, true, window, 0);
    },
    async destroyed() {
        this.$store.commit('portal/updateHasData', {});
        this.$store.commit('portal/setEditStatus', false);
        this.$store.commit('portal/updatePermission', false);
        this.$store.commit('portal/updateChangePageNameFlg', false);
        this.$store.dispatch('updateLoading', false);
        this.windowResizeEvent = null;
    }
}
</script>
<style lang="scss">
#portal-index {
    .portal-right {
        height: calc(100vh - 132px);
    }
    
    .vx-card .vx-card__collapsible-content .vx-card__body {
        padding: 10px;
    }
    
    .iframe-groupware {
        height: calc(100vh - 179px);
    }
    .vue-grid-layout {
        margin: -10px;
        .vue-grid-item.vue-resizable {
            border:1px dashed rgb(9, 132, 227);
            .mask-layer {
                position: absolute;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                z-index: 201;
                opacity: 0;
            }
            .vue-resizable-handle {
                z-index: 202;
            }
        }
        .vue-grid-item:not(.vue-resizable) {
            .header-comp:hover {
                cursor: auto !important;
            }
        }
        .vue-grid-item {
            .title-app-portal {
                white-space: nowrap;
            }
            .comp-portal-announces {
                height: 100%;
                > div {
                    height: 100%;
                    display: flex;
                    flex-flow: column;
                    > div.notice {
                        height: auto;
                        flex-grow: 1;
                    }
                    .vx-card {
                        height: auto;
                        flex-shrink: 0;
                        margin: 0!important;
                        .vx-card__body {
                            > div {
                                display: flex;
                                width: 100%;
                                flex-wrap: nowrap;
                                overflow-x: auto;
                                overflow-y: hidden;
                            }
                        }
                    }
                }
            }
            .top-menu {
              height: 100%;
            }
            .comp-portal-announces.top-menu {
                .vx-card {
                    height: 100%!important;
                }
            }
            .comp-portal-announces:not(.top-menu) {
                > div {
                  height: 100%;
                  .vx-card {
                    height: 78px;
                    max-height: 100%;
                    .vx-card__collapsible-content.vs-con-loading__container {
                      height: 100%;
                      .vx-card__body {
                        height: 100%;
                        padding-top: 8px !important;
                        .resize-text {
                          height: 100%;
                        }
                      }
                    }
                  }
                }
            }
            .comp-portal-notification,.comp-portal-movie,.comp-portal-favorite,.circular-portal,.bulletin_board,.scheduler,
            .comp-portal-advertisement,.file-mail-portal,.comp-portal-favorite.attendance-system,.special-portal,
            .receive_plan,.to_do_list,
            .faq_bulletin_board,.comp-portal-customize {
                height: 100%;
                .vx-card {
                    width: 100%;
                    height: 100%;
                    margin: 0!important;
                    .vx-card__collapsible-content.vs-con-loading__container {
                        height: 100%;
                        .vx-card__body {
                            height: 100%;
                        }
                    }
                }
                .vx-card__collapsible-content.vs-con-loading__container {
                    height: 100%;
                    .vx-card__body {
                        height: 100%;
                    }
                }
            }
            .comp-portal-advertisement {
                .header-comp {
                    height: 49px;
                    background: #FFFFFF;
                    position: relative;
                    bottom: -1px;
                    z-index: 1;
                    cursor: move;
                }
                .header-comp + div.vx-card {
                    height: calc(100% - 49px)!important;
                }
            }
            .bulletin_board,.faq_bulletin_board,.comp-portal-favorite,.file-mail-portal,
            .receive_plan,.to_do_list,
            .special-portal,#timeCardDiv,.scheduler {
                .vx-card__body {
                    display: flex;
                    flex-flow: column;
                    .header-comp {
                        height: auto;
                        flex-shrink: 0;
                    }
                }
            }
            .bulletin_board,.faq_bulletin_board {
                overflow-x: auto;
                #bbs {
                    min-width: 410px;
                    flex-grow: 1;
                    height: 0!important;
                    margin: 0!important;
                    .leftpanelwidth {
                        display: flex;
                        flex-flow: column;
                        height: 100%;
                        > div:not(.v-list) {
                            height: auto;
                            flex-shrink: 0;
                        }
                        .v-list {
                            flex-grow: 1;
                            height: 0!important;
                        }
                    }
                    #topiclist,#draftlist,#topicdetail,#expiredtopiclist{
                        height: 100%;
                        > div {
                            height: 100%;
                            display: flex;
                            flex-flow: column;
                            .con-vs-card {
                                height: 100%;
                            }
                            .vs-card--content {
                                height: 100%;
                                display: flex;
                                flex-flow: column;
                                > div:nth-child(1) {
                                    height: auto;
                                    flex-shrink: 0;
                                }
                                > div:nth-child(2):not(.table-topic-width):not(.table-category-width) {
                                    height: auto;
                                    flex-shrink: 0;
                                }
                                .table-topic-width, .table-category-width {
                                    flex-grow: 1;
                                    height: 0!important;
                                }
                                > .vs-component.vs-con-table {
                                    flex-grow: 1;
                                    height: 0!important;
                                }
                            }
                        }
                    }
                    #topicdetail {
                        .vs-card--content {
                            display: block;
                        }
                    }
                    #categorylist {
                        height: 100%;
                        > div {
                            height: 100%;
                            .con-vs-card {
                                height: 100%;
                            }
                            .vs-card--content {
                                height: 100%;
                                > div {
                                    height: 100%;
                                    display: flex;
                                    flex-flow: column;
                                    .category_menubar {
                                        height: auto;
                                        flex-shrink: 0;
                                        overflow: hidden;
                                    }
                                    .category_menubar + div {
                                        flex-wrap: wrap;
                                        overflow: hidden;
                                        height: auto;
                                        flex-shrink: 0;
                                    }
                                    > .vs-component.vs-con-table {
                                        flex-grow: 1;
                                        height: 0!important;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            .comp-portal-movie {
                .movie-box {
                    height: calc(100% - 30px);
                    .movie-item {
                        width: 0 !important;
                        flex-grow: 1;
                        .theme-title {
                            width: 100%;
                        }
                        .title {
                            width: 100%;
                        }
                        iframe {
                            width: 100%;
                            height: calc(100% - 82px);
                        }
                        > div:nth-child(3) {
                            width: 100%;
                            height: calc(100% - 82px);
                        }
                    }
                    .movie-item:nth-child(2) {
                        margin-left: 8px;
                    }
                }
            }
            .scheduler {
                .comp-portal-calendar {
                    flex-grow: 1;
                    height: 0!important;
                    .iframe-groupware {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
            .circular-portal {
                .vx-card__body {
                    display: flex;
                    flex-flow: column;
                    .circular-component {
                        height: calc(100% - 28px - 1.5rem);
                        display: flex;
                        flex-flow: column;
                        .internal-detail img {
                            width: 17px;
                        }
                        .table-favorite-width {
                            max-height: none;
                            height: calc(100% - 86px);
                            .vs-table--content {
                                height: 100%;
                            }
                        }
                        > div:nth-child(2) {
                            height: calc(100% - 51px - 1.5rem);
                        }
                        .con-vs-card {
                            height: 100%;
                            .vs-card--content {
                                height: 100%;
                            }
                        }
                    }
                }
            }
            #timeCardDiv {
                .vx-card__body {
                    > div:nth-child(2) {
                        flex-grow: 1;
                        height: 0!important;
                        overflow: auto;
                        div {
                            height: auto;
                            .cardBtnsDivClassBig {
                                > div {
                                    text-align: center;
                                    display: flex;
                                    flex-flow: wrap;
                                    align-items: center;
                                    justify-content: center;
                                    .btnSpaceClassBig {
                                        display: none;
                                    }
                                    button {
                                        white-space: nowrap;
                                        margin: 10px 10%;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            .comp-portal-favorite:not(.attendance-system) {
                .header-comp + div {
                    flex-grow: 1;
                    height: 0!important;
                    > div {
                        height: 100%!important;
                    }
                }
            }
            .comp-portal-favorite.attendance-system {
                .comp-portal-attendance-system {
                    flex-grow: 1;
                    height: 0!important;
                    > div {
                        height: 100%;
                        iframe {
                            height: 100%!important;
                        }
                    }
                }
            }
            .special-portal {
                .special-component {
                    flex-grow: 1;
                    height: 0!important;
                    overflow: auto;
                    .list-received {
                        .vs-component {
                            max-height: none;
                        }
                    }
                }
            }
            .file-mail-portal {
                .file-mail-card {
                    flex-grow: 1;
                    height: 0!important;
                    > div {
                        height: calc(100% - 1.5rem);
                    }
                }
            }
            .comp-portal-customize {
                .customize-box {
                    height: 100%;
                    .customize-item {
                        min-height: 0;
                        flex-grow: 1;
                        width: auto;
                        height: 100%;
                        margin: 0 1px;
                        .notice-list {
                            min-height: 270px;
                            .notice-item {
                                margin: 25px 10px;
                            }
                        }
                    }
                    .customize-item:nth-child(2) {
                        margin-left: 8px;
                    }
                }
            }
            #receive_plan{
                .receive-plan-content {
                    margin-bottom: 0!important;
                }
            }
            #to_do_list{
                .comp-portal-to-do-list {
                    flex-grow: 1;
                    height: 0;
                    margin-bottom: 0!important;
                    .to-do-group {
                        height: 100%;
                        display: flex;
                        flex-flow: column;
                        .top-action {
                            height: auto;
                            flex-shrink: 0;
                        }
                        button.add-group {
                          flex-shrink: 0;
                          width: 200px;
                        }
                        .vs-con-table {
                            flex-grow: 1;
                            height: auto;
                            overflow-x: auto;
                        }
                    }
                }
            }
        }
        .vue-grid-item.vue-grid-placeholder {
            background: #0984E3;
        }
        .vue-grid-item>.vue-resizable-handle {
            background-size: 15px 15px;
        }
        .vue-grid-item.static {
            position: absolute!important;
        }
    }
}
#portal-index .vue-grid-layout .vue-grid-item .bulletin_board #bbs .leftpanelwidth .v-list, #portal-index .vue-grid-layout .vue-grid-item .faq_bulletin_board #bbs .leftpanelwidth .v-list {
    flex-grow: 1;
    height: auto !important;
}
#portal-index-mobile{
  margin-top: -60px;
  .portal-index-mobile-label, .topic-mobile-label, .notification-mobile-label{
    background: #0984e3;
    color: #fff;
    text-align: center;
    line-height: 40px;
    font-size: 18px;
  }
  .last-login{
    text-align: center;
    line-height: 50px;
    background: #fff;
  }
  .top-screen-mobile{
    .statistics{
      display: inline-block;
      width: 100%;
      padding: 10px 0;
      .cursor-pointer{
        float: left;
        width: 20%;
      }
    }
  }
  .top-menu-mobile{
    margin: 5px 0;
    background: #fff;
    .comp-portal-announces-mobile.mobile{
      display: inline-block;
      text-align: center;
      width: 100%;
    }
    .item{
      display: inline-block;
      text-align: center;
      width: 32%;
      max-width: 150px;
      min-width: 90px;
      margin: 10px 0;
      .icon{
        height: 35px;
        margin-bottom: 5px;
        img{
          height: 32px;
        }
      }
      .label{
        white-space: nowrap;
      }
      &.creation, &.received{
        img{
          position: relative;
          top: 4px;
          height: 25px;
        }
      }
      &.active{
        .label{
          color: #000;
          font-weight: 700;
        }
      }
    }
  }
  #bbs.mobile{
    height: auto;
    background: #fff;
    padding: 0 0 10px 0;
    .table-topic-width{
      height: auto;
    }
    .topic-list-mobile{
      .topic-title{
        font-weight: 500;
      }
      .topic-created{
        text-align: right;
      }
    }
  }
  .comp-portal-notification{
    .list-notification{
      padding: 0;
    }
    table{
      display: block;
      tr{
        display: inline-flex;
        width: 100%;
        td{
          &:first-child{
            width: 80%;
          }
          &:last-child{
            width: 20%;
            span{
              top: 5px;
              position: relative;
            }
          }
          > span {
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            white-space: nowrap;
          }
          >span{
            width: auto;
          }
          .notification-icon{
            margin-right: 10px;
          }
        }
      }
    }
  }
  .vx-card{
    box-shadow: 0 0 0 0;
  }
}
</style>
