
<template>
<div class="relative">
  <div class="vx-navbar-wrapper" :class="classObj">
    <vs-navbar class="vx-navbar navbar-custom navbar-skelton" :color="navbarColorLocal">

      <!-- SM - OPEN SIDEBAR BUTTON -->
      <feather-icon class="sm:inline-flex xl:hidden cursor-pointer mr-1" icon="MenuIcon" @click.stop="showSidebar"></feather-icon>

      <div v-if="$route.path.includes('/portal')" style="display: flex;">
        <vs-row v-for="(mypage,index) in listMyPages" :class="[$store.state.portal.currentMyPage == mypage.id  ? 'mypage-active': '']"  :key="index" class="mr-4" style="cursor: pointer; width: auto;">
            <vs-col vs-type="flex">
                <p class="font-semibold mouse-hover mr-1" :style="`color: ${navbarFontColor}`" style="padding: 7px 0 7px 5px;" @click="openOrSettingMyPage(mypage)">{{ mypage.page_name}}</p>
                <feather-icon icon="SettingsIcon" @click="openOrSettingMyPage(mypage)" style="width: 22.5px; padding-right: 5px;" class="navbar-fuzzy-search"></feather-icon>
<!--                <vs-dropdown vs-custom-content class="cursor-pointer">-->
<!--                    <feather-icon icon="SettingsIcon" style="width: 15px; padding-right: 5px;" class="navbar-fuzzy-search"></feather-icon>-->
<!--                    <vs-dropdown-menu class="vx-navbar-dropdown">-->
<!--                        <ul>-->
<!--                            <li-->
<!--                                class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white"-->
<!--                                @click="settingCurrentPage(mypage)">-->
<!--                                <span>設定</span>-->
<!--                            </li>-->
<!--                            &lt;!&ndash; <li-->
<!--                                class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white"-->
<!--                                @click="deleteCurrentPage(mypage.id)">-->
<!--                                <span>削除</span>-->
<!--                            </li> &ndash;&gt;-->
<!--                        </ul>-->
<!--                    </vs-dropdown-menu>-->
<!--                </vs-dropdown>-->
            </vs-col>
        </vs-row>
            <!-- <feather-icon icon="PlusIcon" style="width: 15px;" class="cursor-pointer navbar-fuzzy-search" @click="openSettingNewPage"></feather-icon> -->
      </div>

      <p v-else class="font-semibold" :style="`color: ${navbarFontColor}`">{{ routeTitle }}</p>

        <vs-popup classContent="popup-example" title="ページ設定" :active.sync="activeSettingMyPage" class="setting-page-layout">
            <div >
                <form>
                    <vs-row>
                        <vs-row class="border-bottom">
                            <h5 class="mb-1">配置選択</h5>
                        </vs-row>
                        <vs-row class="m-5" v-if="myPageLayouts.length > 0">
                            <vs-col v-for="(layout,index) in myPageLayouts" :key="index" vs-w="3" vs-type="flex" vs-align="center" vs-justify="space-between" class="px-3" style="flex-flow: column;">
                                <div style="width: 100%;flex-shrink: 0;height: auto;" @click="layoutSrcSelected(layout)">
                                    <img :src="layout.layout_src" alt="layout" class="img-layout" :class="[myPageLayoutSelected.id == layout.id  ? 'img-layout-selected': '']" style="width: 100%;">
                                </div>
                                <div class="page-name">
                                    <template v-if="editPageName && editPageNameId === layout.id">
                                        <input type="text" ref="pageNameInput" v-model.trim="inputPageName" @blur="savePageName(layout)" @keyup.prevent="savePageNameByEnter($event, layout)" onkeypress="if(event.keyCode == 13) return false;" maxlength="10">
                                        <feather-icon icon="CheckIcon" @click.stop="savePageName(layout)"></feather-icon>
                                    </template>
                                    <template v-else>
                                        {{layout.page_name}}
                                        <feather-icon icon="Edit3Icon" @click.stop="editPageName(layout)" v-if="!editPageNameFlg"></feather-icon>
                                    </template>
                                </div>
                            </vs-col>
                        </vs-row>
                    </vs-row>
                    <vs-row class="mt-5">
                        <vs-row class="border-bottom">
                            <h5 class="mb-1">アプリ選択</h5>
                        </vs-row>
                        <vs-row class="m-3 app-select">
                            <vs-col vs-type="flex" vs-lg="6" vs-sm="6" vs-xs="12">
                                <ul class="centerx ml-3">
                                    <li>
                                        <vs-checkbox :value="getCurrentLayoutShow(PORTAL_COMPONENT.FAVORITE)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.FAVORITE, !getCurrentLayoutShow(PORTAL_COMPONENT.FAVORITE))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4"><i class="far fa-star mr-2"></i>{{favoriteTitle}}</vs-checkbox>
                                    </li>
                                    <li v-if="$store.state.groupware.checkCalendarApp">
                                        <vs-checkbox :value="$store.state.groupware.checkCalendarApp && getCurrentLayoutShow(PORTAL_COMPONENT.SCHEDULER)"  @click="setCurrentLayoutShow(PORTAL_COMPONENT.SCHEDULER, !getCurrentLayoutShow(PORTAL_COMPONENT.SCHEDULER))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <img :src="require('@assets/images/pages/portal/calendar.svg')" class="mr-2" style="height:1rem;" />{{calendarTitle}}
                                        </vs-checkbox>
                                    </li>
                                    <li v-if="$store.state.groupware.checkBulletinBoardApp">
                                        <vs-checkbox :value="$store.state.groupware.checkBulletinBoardApp && getCurrentLayoutShow(PORTAL_COMPONENT.BULLETIN_BOARD)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.BULLETIN_BOARD, !getCurrentLayoutShow(PORTAL_COMPONENT.BULLETIN_BOARD))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <img :src="require('@assets/images/pages/portal/board.svg')" class="mr-2" style="height:1rem;" />{{bulletinBoardTitle}}
                                        </vs-checkbox>
                                    </li>
                                    <!--PAC_5-2648 S-->
                                    <li v-if="$store.state.groupware.checkFaqBulletinBoardApp">
                                        <vs-checkbox
                                            :value="$store.state.groupware.checkFaqBulletinBoardApp && getCurrentLayoutShow(PORTAL_COMPONENT.FAQ_BULLETIN_BOARD)"
                                            @click="setCurrentLayoutShow(PORTAL_COMPONENT.FAQ_BULLETIN_BOARD, !getCurrentLayoutShow(PORTAL_COMPONENT.FAQ_BULLETIN_BOARD))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <img :src="require('@assets/images/pages/portal/board.svg')" class="mr-2"
                                               style="height:1rem;"/>{{ faqBulletinBoardTitle }}
                                        </vs-checkbox>
                                    </li>
                                    <!--PAC_5-2648 E-->
                                    <li v-if="$store.state.groupware.checkFileMailApp">
                                      <vs-checkbox :value="$store.state.groupware.checkFileMailApp && getCurrentLayoutShow(PORTAL_COMPONENT.FILE_MAIL)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.FILE_MAIL, !getCurrentLayoutShow(PORTAL_COMPONENT.FILE_MAIL))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                        <img :src="require('@assets/images/pages/portal/folder-mail.svg')" class="mr-2" style="height:1rem;" />{{fileMailTitle}}
                                      </vs-checkbox>
                                    </li>
                                </ul>
                            </vs-col>
                            <vs-col  vs-type="flex" vs-lg="6" vs-sm="6" vs-xs="12">
                                <ul class="centerx ml-3">
                                    <li v-if="!optionFlg">
                                        <vs-checkbox :value="getCurrentLayoutShow(PORTAL_COMPONENT.CIRCULAR)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.CIRCULAR, !getCurrentLayoutShow(PORTAL_COMPONENT.CIRCULAR))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <img :src="require('@assets/images/pages/portal/cloud.svg')" class="mr-2" style="height:1rem;" />{{shachihataCloudTitle}}
                                        </vs-checkbox>
                                    </li>
                                    <li v-if="$store.state.groupware.checkTimeCardApp && this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.attendance_system_flg !== 1">
                                        <vs-checkbox :value="getCurrentLayoutShow(PORTAL_COMPONENT.TIME_CARD)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.TIME_CARD, !getCurrentLayoutShow(PORTAL_COMPONENT.TIME_CARD))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <i class="fas fa-user-clock" style="font-size: 12.5px"></i>
                                            {{timeCardTitle}}
                                        </vs-checkbox>
                                    </li>
                                    <li v-if="$store.state.groupware.checkTimeCardApp && this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.attendance_system_flg === 1">
                                        <vs-checkbox :value="getCurrentLayoutShow(PORTAL_COMPONENT.TIME_CARD_ATTENDANCE)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.TIME_CARD_ATTENDANCE, !getCurrentLayoutShow(PORTAL_COMPONENT.TIME_CARD_ATTENDANCE))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <i class="fas fa-user-clock" style="font-size: 12.5px"></i>
                                            {{timeCardTitle}}
                                        </vs-checkbox>
                                    </li>
                                    <li v-if="isSpecialSiteSend && !optionFlg">
                                        <vs-checkbox :value="getCurrentLayoutShow(PORTAL_COMPONENT.SPECIAL)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.SPECIAL, !getCurrentLayoutShow(PORTAL_COMPONENT.SPECIAL))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <i class="far fa-building mr-2" style="height:1rem;"></i>{{receiveCompanyTitle}}
                                        </vs-checkbox>
                                    </li>
                                    <li v-if="this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.receive_plan_flg == 1 && limit.limit_receive_plan_flg">
                                      <vs-checkbox :value="getCurrentLayoutShow(PORTAL_COMPONENT.RECEIVE_PLAN)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.RECEIVE_PLAN, !getCurrentLayoutShow(PORTAL_COMPONENT.RECEIVE_PLAN))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                        <img :src="require('@assets/images/pages/portal/receive-plan-light.svg')" class="mr-2" style="width: 1rem; height: 1rem;vertical-align: -0.125em;">{{receivePlanTitle}}
                                      </vs-checkbox>
                                    </li>
                                    <li v-if="$store.state.groupware.checkToDoList">
                                        <vs-checkbox :value="$store.state.groupware.checkToDoList && getCurrentLayoutShow(PORTAL_COMPONENT.TO_DO_LIST)" @click="setCurrentLayoutShow(PORTAL_COMPONENT.TO_DO_LIST, !getCurrentLayoutShow(PORTAL_COMPONENT.TO_DO_LIST))" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4">
                                            <img :src="require('@assets/images/pages/portal/icon-to-do-list-light.svg')" class="mr-2" style="width: 1rem; height: 1rem;vertical-align: -0.125em;">{{toDoListTitle}}
                                        </vs-checkbox>
                                    </li>
                                </ul>
                            </vs-col>
                        </vs-row>
                    </vs-row>
                </form>
            </div>

            <vs-row class="mt-5">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <template v-if="currentMyPage && myPageLayoutSelected.id === currentMyPage">
                        <vs-button v-if="!editPortalStatus" :disabled="!this.myPageLayouts || this.myPageLayouts.length < 4" @click="editSetting" color="primary">編集</vs-button>
                        <vs-button v-if="editPortalStatus" @click="saveSetting" color="primary">保存</vs-button>
                        <vs-button v-if="editPortalStatus" @click="cancelSetting" color="primary">キャンセル</vs-button>
                    </template>
                    <template v-else>
                        <vs-button @click="selectSetting" color="primary">適用</vs-button>
                    </template>
                    <vs-button @click="activeSettingMyPage = false" color="dark" type="border">閉じる</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>


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
      <!-- Comment BellIcon For demo -->
      <div style="position: relative" v-if="$store.state.groupware.checkFaqBulletinBoardApp" @click="openFaqBulletinBoard">
                <span class="cursor-pointer navbar-fuzzy-search mr-4 mt-2 feature-icon select-none relative">
                    <img :src="require('@assets/images/pages/portal/faqbbsnoticeicon.png')"    height="45px" style="vertical-align: bottom"/>
                </span>
        <div v-show="$store.state.portal.faqBbsUnreadNoticeCount>0" class=" vs-chip-danger"  style="color:white;border-radius: 50%;position: absolute;line-height: 1.5; top: -8px ;right:7px; text-align: center" :style="{fontSize:$store.state.portal.faqBbsUnreadNoticeCount>99?'12px':'1rem',width:$store.state.portal.faqBbsUnreadNoticeCount>99?'22px':'20px',height:$store.state.portal.faqBbsUnreadNoticeCount>99?'22px':'20px' }">{{$store.state.portal.faqBbsUnreadNoticeCount>99?"99+":$store.state.portal.faqBbsUnreadNoticeCount}}</div>
      </div>
      <div>
        <vs-dropdown v-if="showMenuAvatar" vs-custom-content vs-trigger-click class="cursor-pointer bell-icon-notice">
            <div >
                <span class="cursor-pointer navbar-fuzzy-search mr-4 mt-2 feature-icon select-none relative">
                    <img :src="require('@assets/images/pages/portal/bell.svg')" />
                </span>
                <div v-show="countNoticeUnread && countNoticeUnread > 0 && countNoticeUnread < 100" class="count-notice-unread vs-chip-danger" style="line-height: 1.5;">{{countNoticeUnread}}</div>
                <div v-show="countNoticeUnread && countNoticeUnread > 99" class="count-notice-unread vs-chip-danger count-notice-unread-big" style="line-height: 1.5;">99+</div>
            </div>
            <vs-dropdown-menu class="vx-navbar-dropdown" style="width: 400px;">
                <vs-row class="border-bottom pb-4 mb-2 list-notice-groupware" vs-type="flex" vs-justify="flex-end">
                    <vs-button color="primary" @click="markAllRead">すべて既読</vs-button>
                </vs-row>
                <div style="padding: 0; overflow-y: auto; height: 285px;">
                    <vs-row class="mt-3" v-if="listNoticeGroupware.noticeManagementList.length < 1" vs-justify="center">
                        <h5>データがありません。</h5>
                    </vs-row>
                    <vs-row v-else v-for="(listNotice,index) in listNoticeGroupware.noticeManagementList" :key="index" :style="{fontWeight:((!listNotice.isRead)?'bold':'normal')}">
                        <vs-col vs-w="1" class="notice-groupware-avatar mr-3">
                            <img v-if="listNotice.notice.mstUser.userProfileData"  :src="'data:image/jpeg;base64,' + listNotice.notice.mstUser.userProfileData" style="width: 100%; border-radius: 50%;" alt="avatar user">
                            <span v-else><i style="width: 100%; color: white;" class="fas fa-user" ></i></span>
                        </vs-col>
                        <vs-col vs-w="10">
                            <div style="display: flex; justify-content: space-between;">
                                <span class="mr-2">{{listNotice.notice.mstUser.name}}</span>
                                <span>{{listNotice.notice.createdAt}}</span>
                            </div>
                            <vs-row>
                                <vs-col vs-w="11">
                                    <span class="mouse-hover" v-if="listNotice.notice.link" @click="onClickNotice(listNotice)"><u>{{listNotice.notice.subject}}</u></span>
                                    <span v-else @click="onAroundArrow(listNotice)" class="mouse-hover">{{listNotice.notice.subject}}</span>
                                    <div class="mt-3" v-show="showContentNotice && noticeId === listNotice.id">{{listNotice.notice.contents}}</div>
                                </vs-col>
                                <vs-col v-if="listNotice.notice.contents" vs-w="1">
                                    <span @click="onAroundArrow(listNotice)" class="mouse-hover">
                                        <vs-icon :id="'arrow-'+listNotice.id" class="around_groupware around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                                    </span>
                                </vs-col>
                            </vs-row>
                        </vs-col>
                    </vs-row>
                </div>
                <div v-if="listNoticeGroupware.noticeManagementList.length > 0">
                    <div><div class="mt-3 mb-5">{{ paginationListNoticeGroupware.totalItem }} 件中 {{ paginationListNoticeGroupware.from }} 件から {{ paginationListNoticeGroupware.to }} 件までを表示</div></div>
                    <!-- <vs-pagination :total="paginationListNoticeGroupware.totalPage" v-model.sync="currentPageNotice" :max="5" class="pt-1"></vs-pagination> -->
                    <vx-pagination :totalSmall="paginationListNoticeGroupware.totalPage" :currentPage.sync="currentPageNotice"></vx-pagination>
                </div>
            </vs-dropdown-menu>
        </vs-dropdown>

      </div>
        <feather-icon icon="SettingsIcon" @click="$router.push('/settings')" class="cursor-pointer navbar-fuzzy-search mr-4"></feather-icon>
        <feather-icon icon="HelpCircleIcon" class="cursor-pointer navbar-fuzzy-search mr-4" style="display: none"></feather-icon>
      <!-- USER META -->
      <div class="the-navbar__user-meta flex items-center" v-if="activeUserInfo && activeUserInfo.id">

        <vs-dropdown vs-custom-content vs-trigger-click class="cursor-pointer">
          <div class="con-img ml-3 pt-3 pb-3">
            <div class="text-right leading-tight block">
              <p class="font-semibold" :style="`color: ${navbarFontColor}`">{{ user_displayName }}</p>
            </div>
          </div>
          <vs-dropdown-menu class="vx-navbar-dropdown">
            <ul style="min-width: 14rem">
                <li v-if="checkAcountAdmin" class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white" @click="isLoggedInAdmin">
                    <feather-icon icon="UserCheckIcon" svgClasses="w-4 h-4"/>
                    <span class="ml-2">管理者としてログイン</span>
                </li>
                <li
                        class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white"
                        @click="gotoHelp">
                    <feather-icon icon="InfoIcon" svgClasses="w-4 h-4"/>
                    <span class="ml-2">ヘルプ</span>
                </li>
                <li
                    class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white"
                    @click="logout">
                    <feather-icon icon="LogOutIcon" svgClasses="w-4 h-4"/>
                    <span class="ml-2">ログアウト</span>
                </li>
            </ul>
          </vs-dropdown-menu>
        </vs-dropdown>
<!--        <vs-dropdown vs-custom-content vs-trigger-click ref="userMenuDropdown" class="cursor-pointer" v-show="showMenuAvatar">-->
<!--          <div class="avatar-user" style="width: 40px; max-height: 40px; margin-left: 5px;"></div>-->
<!--          <vs-dropdown-menu class="vx-navbar-dropdown check-menu">-->
<!--            <ul style="min-width: 14rem">-->
<!--              <li class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white"-->
<!--                  @click="gotoGroupware('/groupware/Personal')">-->
<!--                <span class="ml-2">個人設定</span>-->
<!--              </li>-->
<!--            </ul>-->
<!--            <vs-dropdown-group v-if="$store.state.groupware.checkCalendarApp" vs-collapse vs-label="スケジューラ設定">-->
<!--              <ul style="min-width: 14rem">-->
<!--                <li v-if="$store.state.groupware.checkCalendarApp" class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white" @click="gotoGroupware('/groupware/MyGroup')">-->
<!--                  <span class="ml-2">マイグループ設定</span>-->
<!--                </li>-->
<!--                <li class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white" @click="gotoGroupware('/groupware/Notification')">-->
<!--                  <span class="ml-2">通知設定</span>-->
<!--                </li>-->
<!--                <li class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white" v-if="$store.state.groupware.checkCaldavApp" @click="gotoGroupware('/groupware/CaldavSetting')">-->
<!--                  <span class="ml-2">カレンダー連携設定</span>-->
<!--                </li>-->
<!--              </ul>-->
<!--            </vs-dropdown-group>-->
<!--          </vs-dropdown-menu>-->
<!--        </vs-dropdown>-->
<!--        <div v-show="!showMenuAvatar" class="avatar-user" style="width: 40px; max-height: 40px; margin-left: 5px;"></div>-->
      </div>
    </vs-navbar>
  </div>
    <vs-prompt
        @cancel="cancelPromtLogin"
        @accept="login"
        :title="''"
        :accept-text="'ログイン'"
        :cancel-text="'閉じる'"
        :active.sync="popUpLogin"
    >
        <div class=" d-flex flex-column justify-content-between">
            <p>パスワードを入力してください</p>
            <vs-input :type="showPassword? '' : 'password' " v-model="admin_password" class="mt-3"/>
            <vs-checkbox :value="showPassword" @click="toggleShowPassword" class="mt-2 w-50">パスワードを表示</vs-checkbox>
        </div>
    </vs-prompt>
    <form id="loginAdmin" :action="admin_url" method="post" style="display: none">
        <input name="email" v-model="admin_email"/>
        <input name="password" v-model="admin_password"/>
    </form>
</div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxAutoSuggest from '@/components/vx-auto-suggest/VxAutoSuggest.vue';
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import draggable from 'vuedraggable';
import config from "../../../app.config";
import Axios from "axios";
import Vue from 'vue';
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import {PORTAL_COMPONENT} from '../../../enums/portal_component';
import {cloneDeep} from "lodash/lang";

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
            popUpLogin: false,
            showPassword: false,
            admin_password: '',
            admin_email: JSON.parse(getLS('user')).email,
            admin_url: `${config.ADMIN_API_URL}/login`,
            activeSettingMyPage: false,
            page_name_setting:'',
            mypage_id_update: '',
            mypage_layout_id_old: '',
            myPageLayoutSelected: {},
            myPageLayouts: [],
            layout_ap_select: {},
            showContentNotice: false,
            noticeId: 0,
            countNoticeUnread: '',
            listNoticeGroupware: {totalRecord: 0, noticeManagementList: []},
            currentPageNotice: 1,
            paginationListNoticeGroupware:{ totalPage:0, currentPage:0, limit: 10, totalItem:0, from: 1, to: 10 },
            showMenuAvatar: false,
            countUnread: null,
            refreshToken: null,
            favoriteTitle: 'お気に入り',
            calendarTitle: 'スケジューラ',
            bulletinBoardTitle: '掲示板',
            faqBulletinBoardTitle: 'サポート掲示板',
            timeCardTitle: 'タイムカード',
            shachihataCloudTitle: 'シヤチハタクラウド',
            fileMailTitle: 'ファイルメール便',
            optionFlg : JSON.parse(getLS('user')).option_flg,
            receiveCompanyTitle: '受取連携会社',
            isSpecialSiteSend: JSON.parse(getLS('user')).is_special_site_send,
            PORTAL_COMPONENT: PORTAL_COMPONENT,
            inputPageName: '',
            editPageNameFlg: false,
            editPageNameId: null,
            receivePlanTitle:'受信専用プラン',
            toDoListTitle: 'ToDoリスト',
        }
    },
    watch: {
        'currentPageNotice': function (val) {
            this.paginationListNoticeGroupware.currentPage = this.currentPageNotice - 1;
            this.checkOpenDropdownNotice(false);
            $(".around_groupware").addClass("around_return");
            $(".around_groupware").removeClass("around");
            this.showContentNotice = false;
        },
        '$route'() {
            if (this.showBookmarkPagesDropdown) this.showBookmarkPagesDropdown = false
        },
        '$route.path': async function (){
            if(this.$route.path == '/portal'){
                await this.checkAccessToken();
                await this.getMyPages();
                await this.checkUserAppUsageStatus();
                /*await this.checkAppRole();*/
            }else{
                if(this.$route.path.includes('/portal') || this.$route.path.includes('/groupware')){
                    if(this.countUnread){
                        clearInterval(this.countUnread);
                    }
                   await this.getCountUnread();
                }else{
                    clearInterval(this.countUnread);
                }
            }
            
            if((this.$route.path.includes('/portal') ||
                this.$route.path.includes('/groupware')) &&
                (this.$store.state.groupware.checkBulletinBoardApp ||
                    this.$store.state.groupware.checkCalendarApp)){
                this.showMenuAvatar = true;
            }else{
                this.showMenuAvatar = false;
            }
            if (this.$route.path === '/settings') {
                await this.checkAccessToken();
            }
        },
    },
    computed: {
        ...mapState({
            listMyPages: state => state.portal.listMyPages,
            checkCalendarApp: state => state.groupware.checkCalendarApp,
            checkBulletinBoardApp: state => state.groupware.checkBulletinBoardApp,
            currentMyPage:state => state.portal.currentMyPage,
        }),
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
        activeUserInfo() {
          return JSON.parse(getLS('user'));
        },
        user_displayName() {
            return this.activeUserInfo.family_name + ' ' +  this.activeUserInfo.given_name;
        },
        activeUserImg() {
            return this.$store.state.AppActiveUser.photoURL;
        },
        checkAcountAdmin() {
          return JSON.parse(localStorage.getItem('admin'));
        },
        editPortalStatus: {
            get() {
                return this.$store.state.portal.editStatus;
            },
            set(status) {
                this.$store.commit('portal/setEditStatus', status);
            }
        },
        currentLayout: {
            get() {
                return this.$store.state.portal.currentLayout;
            },
            set(layout) {
                this.$store.commit('portal/updateCurrentLayout', layout);
            }
        },
        currentComponent: {
            get() {
                return this.$store.state.portal.currentComponent;
            },
            set(layout) {
                this.$store.commit('portal/updateCurrentComponent', layout);
            }
        },
        getCurrentLayoutShow() {
            return (name) => {
                if (!this.currentMyPage) return false;
                const selectLayout = Object.assign({}, this.myPageLayoutSelected);
                let currentLayout;
                if (this.currentMyPage && (this.myPageLayoutSelected.id === this.currentMyPage || (!selectLayout || !selectLayout.layout))) {
                    currentLayout = cloneDeep(Object.values(this.currentLayout));
                } else {
                    currentLayout = JSON.parse(selectLayout.layout);
                }
                if (currentLayout) {
                    const item = currentLayout.find((item) => {
                        if (item.name === name) {
                            return item;
                        }
                    })
                    return item && item.show ? item.show : false;
                }
            }
        },
        setCurrentLayoutShow() {
            return async (name, show) => {
                if (this.currentMyPage && this.myPageLayoutSelected.id === this.currentMyPage) {
                    const currentLayout = cloneDeep(Object.values(this.currentLayout));
                    if (currentLayout) {
                        const currentComponent = [];
                        currentLayout.map((item) => {
                            if (item.name === name) {
                                item.show = show;
                            }
                            const componentItem = item;
                            this.filterData(componentItem);
                            if (componentItem.available && item.show) {
                                currentComponent.push(componentItem);
                            }
                        })
                        this.currentLayout = Object.values(currentLayout);
                        this.currentComponent = Object.values(currentComponent);
                        if (!this.editPortalStatus) {
                            this.saveSetting(false);
                        }
                    }
                } else {
                    const root = this;
                    const changeLayout = JSON.parse(root.myPageLayoutSelected.layout);
                    changeLayout.map((item) => {
                        if (item.name === name) {
                            item.show = show;
                        }
                    })
                    let myPage = {
                        id                        : root.myPageLayoutSelected.id,
                        mst_mypage_layout_id      : root.myPageLayoutSelected.mst_mypage_layout_id,
                        layout                    : JSON.stringify(changeLayout),
                    };

                    let layout = root.getLayoutLog(root.currentLayout);
                    await root.updateMyPage(myPage).then(
                        response => {
                            if(response.success) {
                                root.myPageLayoutSelected.layout = JSON.stringify(changeLayout);
                                this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 0, params:{mypage_id: myPage.id, display_app: layout}});
                            } else {
                                this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                            }
                        },
                        error => {
                            this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                        }
                    );
                }
            }
        },
        currentMyPage: {
            get() {
                return this.$store.state.portal.currentMyPage;
            }
        },
        limit(){
          return JSON.parse(getLS('limit'))
        },
    },
    methods: {
        ...mapActions({
            getMyPages: "portal/getMyPages",
            getListFavorite: "portal/getListFavorite",
            getAvatarUser: "user/getAvatarUser",
            getMyPageLayout: "portal/getMyPageLayout",
            getUserAppUsageStatus: "groupware/getUserAppUsageStatus",
            getAppRole: "groupware/getAppRole",
            saveMyPage: "portal/saveMyPage",
            updateMyPage: "portal/updateMyPage",
            deleteMyPage: "portal/deleteMyPage",
            getTokenGroupware: "groupware/getTokenGroupware",
            getUnreadNoticeGroupware: "groupware/getUnreadNoticeGroupware",
            getListNoticeGroupware: "groupware/getListNoticeGroupware",
            markAllReadNoticeGroupware: "groupware/markAllReadNoticeGroupware",
            markReadNoticeGroupware: "groupware/markReadNoticeGroupware",
            updateRefreshToken : "groupware/updateRefreshToken",
            addLogOperation: "logOperation/addLog",
            getFaqBbsUnreadNoticeCount: "portal/getFaqBbsUnreadNoticeCount",
        }),
        gotoGroupware(url) {
            $('.vx-navbar-wrapper').click();
            this.$router.push(url);
        },
        moment: function () {
            return this.$moment();
        },
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
        async checkOpenDropdownNotice(addLogHistory){
            var root = this;
            let data = {
                page : this.paginationListNoticeGroupware.currentPage,
                tokenGroupware : this.$cookie.get('accessToken'),
            };
            let list=[]
            if(addLogHistory){
              /*PAC_5-1846 S*/
              let bbsNotice = await Axios.get(`${config.BASE_API_URL}/bbs_notice_list`).then( response => {
                    this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 0});
                    response.data.data.forEach(_=>{
                      _.type='bbs'
                    })
                    return response.data;
                  },
                  error => {
                    this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 1});
                    dispatch("alertError", error, { root: true });
                    return false;
                  });
              /*PAC_5-1846 E*/
              /*PAC_5-1846 S*/
             
              let groupWareNotice = {
                noticeManagementList: []
              }
              let toDoListNotice = {
                  data: []
              }
              if(this.$store.state.groupware.checkToDoList){
                  toDoListNotice = await Axios.get(`${config.BASE_API_URL}/to-do-list/notice/list`).then( response => {
                          this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 0});
                          response.data.data.forEach(_=>{
                              _.type='to_do_list'
                          })
                          return response.data;
                      },
                      error => {
                          this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 1});
                          dispatch("alertError", error, { root: true });
                          return false;
                      });
              }
              if (this.$cookie.get('accessToken')){
                groupWareNotice = await this.getListNoticeGroupware(data).then(
                      response => {
                        this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 0});
                        response.noticeManagementList.forEach(_ => {
                          _.type = 'gw'
                        })
                        return response;
                      },
                      error => {
                        this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 1});
                        //dispatch("alertError", error, {root: true});
                        return {
                          noticeManagementList: []
                        };
                      }
                  );
              }

              /*PAC_5-1846 S*/
              list=bbsNotice.data.concat(groupWareNotice.noticeManagementList,toDoListNotice.data);
              list.sort((a,b)=>{
                return (new Date(b.createdAt)).getTime() - (new Date(a.createdAt)).getTime()
              })
              this.listNoticeGroupware={totalRecord: bbsNotice.data.length + groupWareNotice.noticeManagementList.length + toDoListNotice.data.length, noticeManagementList:list };
              /*PAC_5-1846 E*/
            }else{
              /*PAC_5-1846 S*/
              let bbsNotice = await Axios.get(`${config.BASE_API_URL}/bbs_notice_list`).then(
                  response => {
                    response.data.data.forEach(_=>{
                      _.type='bbs'
                    })
                    return response.data;
                  });

              /*PAC_5-1846 E*/
              let groupWareNotice = {
                noticeManagementList: []
              }
              let toDoListNotice = {
                  data: []
              }
              if(this.$store.state.groupware.checkToDoList){
                  toDoListNotice = await Axios.get(`${config.BASE_API_URL}/to-do-list/notice/list`).then( response => {
                          this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 0});
                          response.data.data.forEach(_=>{
                              _.type='to_do_list'
                          })
                          return response.data;
                      },
                      error => {
                          this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 1});
                          dispatch("alertError", error, { root: true });
                          return false;
                      });
              }
              if (this.$cookie.get('accessToken')) {
                groupWareNotice = await this.getListNoticeGroupware(data).then(response => {
                      this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 0});
                      response.noticeManagementList.forEach(_ => {
                        _.type = 'gw'
                      })
                      return response
                    },
                    error => {
                      this.addLogOperation({action: 'pr1-12-portal-notice-screen', result: 1});
                      //dispatch("alertError", error, {root: true});
                      return {
                        noticeManagementList: []
                      };
                    });
              }
              list=bbsNotice.data.concat(groupWareNotice.noticeManagementList,toDoListNotice.data);
              list.sort((a,b)=>{
                return (new Date(b.createdAt)).getTime() - (new Date(a.createdAt)).getTime()
              })
              this.listNoticeGroupware={totalRecord: bbsNotice.data.length+groupWareNotice.noticeManagementList.length + toDoListNotice.data.length, noticeManagementList: list};
            }

            if(this.listNoticeGroupware){
                var dateNow = this.$moment(new Date());
                this.listNoticeGroupware.noticeManagementList = this.listNoticeGroupware.noticeManagementList.map(function(item) {
                   if(dateNow.diff(item.notice.createdAt, 'years') > 0){
                        item.notice.createdAt = root.$moment(item.notice.createdAt).format('YYYY/MM/DD');
                    }else if(root.$moment(item.createdAt).format('MM-DD-YYYY') < root.$moment(new Date()).format('MM-DD-YYYY')){
                        item.notice.createdAt = root.$moment(item.notice.createdAt).format('MM/DD');
                    }else{
                        item.notice.createdAt = root.$moment(item.notice.createdAt).format('HH:mm');
                    }
                    return item;
                });
            }


            this.paginationListNoticeGroupware.totalItem   = this.listNoticeGroupware.totalRecord;
            this.paginationListNoticeGroupware.totalPage   = Math.ceil(this.paginationListNoticeGroupware.totalItem/this.paginationListNoticeGroupware.limit);
            this.paginationListNoticeGroupware.from        = (this.paginationListNoticeGroupware.currentPage*this.paginationListNoticeGroupware.limit)+1;
            this.paginationListNoticeGroupware.to          = (this.paginationListNoticeGroupware.currentPage >= this.paginationListNoticeGroupware.totalItem/this.paginationListNoticeGroupware.limit - 1) ? this.paginationListNoticeGroupware.totalItem: this.paginationListNoticeGroupware.currentPage*this.paginationListNoticeGroupware.limit+this.paginationListNoticeGroupware.limit;
            this.listNoticeGroupware.noticeManagementList = list.slice(this.paginationListNoticeGroupware.from-1,this.paginationListNoticeGroupware.to)
        },
        markAllRead: async function (event){
          /*PAC_5-1846 S*/
          Axios.put(`${config.BASE_API_URL}/bbs_all_notice_read`)
          /*PAC_5-1846 E*/
          if(this.$store.state.groupware.checkToDoList){
              Axios.post(`${config.BASE_API_URL}/to-do-list/read-all`)
          }
          let groupwareNoticeCount=0;
          if (this.$cookie.get('accessToken')) {
            let tokenGroupware = this.$cookie.get('accessToken');
            await this.markAllReadNoticeGroupware(tokenGroupware).then(
                response => {
                  if (response) {
                    this.addLogOperation({action: 'pr1-13-portal-read-all-notice', result: 0});
                  } else {
                    this.addLogOperation({action: 'pr1-13-portal-read-all-notice', result: 1});
                  }
                },
                error => {
                  this.addLogOperation({action: 'pr1-13-portal-read-all-notice', result: 1});
                }
            );
            await this.getUnreadNoticeGroupware(tokenGroupware).then(countUnread => {
              if(countUnread.status != 200){
                groupwareNoticeCount = 0
                return
              }
              groupwareNoticeCount = countUnread;
            });
          }
            let bbsNoticeCount=0
            await Axios.get(`${config.BASE_API_URL}/bbs_unread_cnt`).then(response=>{
              bbsNoticeCount=response.data
            })

            let toDoListNoticeCount=0
            if (this.$store.state.groupware.checkToDoList) {
                await Axios.get(`${config.BASE_API_URL}/to-do-list/unread/count`).then(response=>{
                    toDoListNoticeCount=response.data
                })
            }
            this.countNoticeUnread = bbsNoticeCount+groupwareNoticeCount + toDoListNoticeCount;
            await this.checkOpenDropdownNotice(false);
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
            await this.setDomainGroupware(null);
            window.location.href = return_url;
            this.$ls.set(`logout`, Date.now());
        },
        async gotoHelp() {
          //PAC_5-1032 BEGIN
          //StandardとBusinessでヘルプのリンク先を変更したい
          var loginUser = JSON.parse(getLS('user'));
          if (loginUser.contract_edition == 0 || loginUser.contract_edition == 3){
            window.open("https://help.dstmp.com/scloud/standard/", "_blank");
          }else if (loginUser.contract_edition == 1 || loginUser.contract_edition == 2){
            window.open("https://help.dstmp.com/scloud/business/", "_blank");
          }
          //PAC_5-1032 END
        },
        outside: function() {
            this.showBookmarkPagesDropdown = false
        },
        isLoggedInAdmin() {
            if( 1 == this.$store.state.setting.withdrawal_caution ) {
                this.$store.commit('home/setCloseCheck', true );
                if( false == window.confirm('行った変更が保存されない可能性があります。') ) {
                    return;
                }
               this.$store.commit('home/setCloseCheck', false );
            }
            this.popUpLogin = true;
        },
        async onClickNotice(listNotice) {
            window.open(listNotice.notice.link, "_blank");
            await this.onAroundArrow(listNotice)
        },
        /*PAC_5-3156 S*/
        async checkUserPacAppUsageStatus(){
            const appRole = await this.getAppRole();
            if (appRole) {
              var faqBulletinBoardApp = await appRole.some(item => {
                if (item.appName === "サポート掲示板") {
                  return item.isAuth;
                }
              });
              this.$store.commit('groupware/updateCheckFaqBulletinBoardApp', faqBulletinBoardApp);
            } else {
              this.$store.commit('groupware/updateCheckFaqBulletinBoardApp', false);
            }
      },
        /*PAC_5-3156 E*/
        async checkUserAppUsageStatus(){
            let tokenGroupware = this.$cookie.get('accessToken');
            const oldCheckFaqBulletinBoardApp = this.$store.state.groupware.checkFaqBulletinBoardApp;
            const oldCheckBulletinBoardApp = this.$store.state.groupware.checkBulletinBoardApp;
            const oldCheckCalendarApp = this.$store.state.groupware.checkCalendarApp;
            const oldCheckTimeCardApp = this.$store.state.groupware.checkTimeCardApp;
            const oldCheckFileMailApp = this.$store.state.groupware.checkFileMailApp;
            const oldCheckCaldavApp = this.$store.state.groupware.checkCaldavApp;
            const oldMyCompany = Object.assign({}, this.$store.state.groupware.myCompany);
            const oldCheckSpecialSend = this.$store.state.special.checkSpecialSend;
            const oldCheckToDoList = this.$store.state.groupware.checkToDoList;
            const oldCheckAddressListApp = this.$store.state.groupware.checkAddressListApp;

            this.$store.commit('special/updateSpecialCircular', this.isSpecialSiteSend);
            if(tokenGroupware){
                const userAppUsageStatus = await this.getUserAppUsageStatus(tokenGroupware);
                if (userAppUsageStatus){

                  var calendarApp = await userAppUsageStatus.some(item=> {
                    if(item.appName === "スケジューラ") {
                      return item.isAuth;
                    }
                  });
                  var caldavApp = await userAppUsageStatus.some(item=> {
                    if(item.appName === "カレンダー連携") {
                      return item.isAuth;
                    }
                  });
                  var sharedScheduler = await userAppUsageStatus.some(item=> {
                      if(item.appName === "グループスケジューラ") {
                          return item.isAuth;
                      }
                  });

                  this.$store.commit('groupware/updateCheckCalendarApp', calendarApp);
                  this.$store.commit('groupware/updateCheckCaldavApp', caldavApp);
                  this.$store.commit('groupware/updateSharedScheduler', sharedScheduler);
                }else{
                  this.$store.commit('groupware/updateCheckCalendarApp', false);
                  this.$store.commit('groupware/updateCheckCaldavApp', false);
                  this.$store.commit('groupware/updateSharedScheduler', false);
                }
            }
          const appRole = await this.getAppRole();
            let boardPermission={
              category_append: 0,
              topics_append: 0}
          if (appRole) {
            var bulletinBoardApp = await appRole.some(item => {
              if (item.appName === "掲示板") {
                if (item.isAuth) {
                  item.auth.forEach(auth => {
                    if (auth.functionName == '掲示板カテゴリ操作') {
                      auth.mstAccessPrivilegesList.forEach(access => {
                        switch (access.privilegeContent) {
                          case '追加':
                            boardPermission.category_append = access.isAuth
                            break;
                        }
                      })
                    }
                    if (auth.functionName == '掲示板トピック操作') {
                      auth.mstAccessPrivilegesList.forEach(access => {
                        switch (access.privilegeContent) {
                          case '追加':
                            boardPermission.topics_append = access.isAuth
                            break;
                        }
                      })
                    }
                  })
                }
                return item.isAuth;
              }
            });
            this.$store.commit('groupware/updateCheckBulletinBoardApp', bulletinBoardApp);
            var fileMailApp = await appRole.some(item => {
              if (item.appName === 'ファイルメール便') {
                return item.isAuth;
              }
            });
            var timeCardApp = await appRole.some(item => {
              if (item.appName === "タイムカード") {
                return item.isAuth;
              }
            });
            var faqBulletinBoardApp = await appRole.some(item => {
              if (item.appName === "サポート掲示板") {
                return item.isAuth;
              }
            });
            var toDoList = await appRole.some(item=> {
              if(item.appName === "ToDoリスト") {
                return item.isAuth;
              }
            });
            var addressListApp = await appRole.some(item=> {
              if(item.appName === "利用者名簿") {
                return item.isAuth;
              }
            });
            this.$store.commit('groupware/updateCheckFaqBulletinBoardApp', faqBulletinBoardApp);
            this.$store.commit('groupware/updateCheckFileMailApp', fileMailApp);
            this.$store.commit('groupware/updateCheckTimeCardApp', timeCardApp);
            this.$store.commit('groupware/updateBoardPermission', boardPermission);
            this.$store.commit('groupware/updateToDoList', toDoList);
            this.$store.commit('groupware/updateCheckAddressListApp', addressListApp);
          } else {
            this.$store.commit('groupware/updateCheckFaqBulletinBoardApp', false);
            this.$store.commit('groupware/updateCheckFileMailApp', false);
            this.$store.commit('groupware/updateCheckTimeCardApp', false);
            this.$store.commit('groupware/updateBoardPermission', boardPermission);
            this.$store.commit('groupware/updateToDoList', false);
            this.$store.commit('groupware/updateCheckAddressListApp', false);
          }


            if (
                oldCheckFaqBulletinBoardApp !== this.$store.state.groupware.checkFaqBulletinBoardApp ||
                oldCheckBulletinBoardApp !== this.$store.state.groupware.checkBulletinBoardApp ||
                oldCheckCalendarApp !== this.$store.state.groupware.checkCalendarApp ||
                oldCheckTimeCardApp !== this.$store.state.groupware.checkTimeCardApp ||
                oldCheckFileMailApp !== this.$store.state.groupware.checkFileMailApp ||
                oldCheckCaldavApp !== this.$store.state.groupware.checkCaldavApp ||
                (!oldMyCompany && this.$store.state.groupware.myCompany
                    || oldMyCompany !== !this.$store.state.groupware.myCompany
                    || oldMyCompany.attendance_system_flg !== this.$store.state.groupware.myCompany.attendance_system_flg
                ) ||
                oldCheckSpecialSend !== this.$store.state.special.checkSpecialSend
                ||  oldCheckToDoList !== this.$store.state.groupware.checkToDoList
                ||  oldCheckAddressListApp !== this.$store.state.groupware.checkAddressListApp
            ) {
                this.$store.commit('portal/updatePermission', true);
            }
        },

        /*async checkAppRole(){
            const appRole = await this.getAppRole();
            if (appRole){
                this.$store.commit('groupware/updateCheckBulletinBoardApp', appRole.board_flg == 1 ? true : false);
            }else{
                this.$store.commit('groupware/updateCheckBulletinBoardApp', false);
            }
        },*/

        async checkAccessToken() {
          let gw_flg = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.gw_flg ? this.$store.state.groupware.myCompany.gw_flg: 0
          if (gw_flg) {
            if(!this.$cookie.get('emailGroupwareAccessToken') || (this.$cookie.get('emailGroupwareAccessToken') && this.$cookie.get('emailGroupwareAccessToken') != this.admin_email)){
                this.$cookie.set('emailGroupwareAccessToken', this.admin_email);
                 await this.setCookiesGroupware();
            }else{
                if(!this.$cookie.get('accessToken')){
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

        async getCountUnread () {
            var root = this;
            let countUnread = {
              status :200,
              data : 0
            }
            if (this.$cookie.get('accessToken')){
              countUnread = await root.getUnreadNoticeGroupware(root.$cookie.get('accessToken'));
            }
            let bbsCountUnread =  await Axios.get(`${config.BASE_API_URL}/bbs_unread_cnt`).then(response=>{
              return response
            })
            let toDoListCountUnread = {
                data:0,
                status :200,
            }
            if (this.$store.state.groupware.checkToDoList) {
                toDoListCountUnread = await Axios.get(`${config.BASE_API_URL}/to-do-list/unread/count`).then(response=>{
                    return response
                })
            }
            if(countUnread.status === 503 && bbsCountUnread.status == 503 && toDoListCountUnread.status == 503){
                clearInterval(root.countUnread);
            }else{
                if (countUnread.status != 200) {
                    countUnread.data = 0
                }
                if (bbsCountUnread.status != 200) {
                    bbsCountUnread.data = 0
                }
                if (toDoListCountUnread.status != 200) {
                    toDoListCountUnread.data = 0;
                }
                root.countNoticeUnread = countUnread.data + bbsCountUnread.data + toDoListCountUnread;
                root.countUnread =  setInterval(async () => {
                  let bbsCountUnread= await Axios.get(`${config.BASE_API_URL}/bbs_unread_cnt`)
                  let countUnread = {
                    status :200,
                    data : 0
                  }
                  if (this.$cookie.get('accessToken')){
                    countUnread = await root.getUnreadNoticeGroupware(root.$cookie.get('accessToken'));
                  }

                  let toDoListCountUnread = {
                      data:0,
                      status :200,
                  }
                  if(this.$store.state.groupware.checkToDoList){
                      toDoListCountUnread =  await Axios.get(`${config.BASE_API_URL}/to-do-list/unread/count`).then(response=>{
                          return response
                      })
                  }
                  if(countUnread.status === 503 && bbsCountUnread.status == 503 && toDoListCountUnread == 503){
                    clearInterval(root.countUnread);
                  }
                  if($('div').hasClass('list-notice-groupware')){
                    await root.checkOpenDropdownNotice(false);
                  }
                  if (countUnread.status != 200) {
                    countUnread.data = 0
                  }
                  if (bbsCountUnread.status != 200) {
                    bbsCountUnread.data = 0
                  }
                  if (toDoListCountUnread.status != 200) {
                    bbsCountUnread.data = 0
                  }
                  root.countNoticeUnread = countUnread.data + bbsCountUnread.data + toDoListCountUnread.data;
                }, 30000);
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

        onAroundArrow : async function(notice){
            if(!notice.isRead){
                let dataNotice = {
                    tokenGroupware : this.$cookie.get('accessToken'),
                    id : notice.id,
                }
              if (notice.type=='bbs') {
                await Axios.put(`${config.BASE_API_URL}/bbs_notice_read/${notice.id}`)
              } else if(notice.type=='to_do_list'){
                  if(this.$store.state.groupware.checkToDoList){
                      await Axios.post(`${config.BASE_API_URL}/to-do-list/read/${notice.id}`)
                  }
              } else {
                if (this.$cookie.get('accessToken')) {
                  await this.markReadNoticeGroupware(dataNotice);
                }
              }
              let groupwareNoticeCount=0;
              if (this.$cookie.get('accessToken')){
                await this.getUnreadNoticeGroupware(dataNotice.tokenGroupware).then(countUnread => {
                  if(countUnread.status != 200){
                    groupwareNoticeCount = 0;
                    return;
                  }
                  groupwareNoticeCount = countUnread;
                });
              }
              let bbsNoticeCount=0
              await Axios.get(`${config.BASE_API_URL}/bbs_unread_cnt`).then(response=>{
                bbsNoticeCount=response.data
              })

              let toDoListNoticeCount=0
              if (this.$store.state.groupware.checkToDoList) {
                  await Axios.get(`${config.BASE_API_URL}/to-do-list/unread/count`).then(response=>{
                      toDoListNoticeCount=response.data
                  })
              }
              this.countNoticeUnread = bbsNoticeCount+groupwareNoticeCount + toDoListNoticeCount;
                await this.checkOpenDropdownNotice(false);
            }
            let obj = document.getElementById("arrow-"+notice.id);
            if(this.noticeId != notice.id){
                $(".around_groupware").removeClass("around");
                $(".around_groupware").addClass("around_return");
                this.showContentNotice = false;
            }
            if(obj && obj.classList){
                if(this.showContentNotice){
                    obj.classList.add("around_return");
                    obj.classList.remove("around");
                }else{
                    obj.classList.add("around");
                    obj.classList.remove("around_return");
                }
            }

            this.showContentNotice = !this.showContentNotice;
            this.noticeId = notice.id;
        },

        async login() {
            const loginUser = JSON.parse(getLS('user'));
            let data = new FormData();
            data.append('email', loginUser.email);
            data.append('password', this.admin_password)
            try {
                const login = await Axios.post(this.admin_url,
                    data,
                    {
                        headers: {'X-Requested-With':'XMLHttpRequest'}
                    }
                );
                if(login && login.status === 203) {
                    this.$vs.notify({
                        color: 'danger',
                        text: 'パスワードが正しくありません',
                        position: 'bottom-left'
                    })
                }
                if(login && login.status === 200) {
                    let result = await Axios.get(`${config.LOCAL_API_URL}/logout`)
                        .then(response => {
                            return response.data ? response.data: [];
                        })
                        .catch(error => {
                            return [];
                        });

                    if (result && Object.prototype.hasOwnProperty.call(result, "redirectUrl")){
                       // return_url = result.redirectUrl;
                    }
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    localStorage.removeItem('expires_time');
                    localStorage.removeItem('loggedInAdmin');
                    localStorage.removeItem('branding');
                    localStorage.removeItem('limit');
                    document.getElementById('loginAdmin').submit();
                }
            } catch (e) {
                this.$vs.notify({
                    color: 'danger',
                    text: 'パスワードが正しくありません',
                    position: 'bottom-left'
                })
            }
            this.admin_password = '';
            this.showPassword = false;
        },
        toggleShowPassword() {
            this.showPassword = !this.showPassword;
        },
        cancelPromtLogin() {
            this.admin_password = '';
            this.showPassword = false;
        },

        // Method for creating dummy notification time
        randomDate({hr, min, sec}) {
          let date = new Date()

          if(hr) date.setHours(date.getHours() - hr)
          if(min) date.setMinutes(date.getMinutes() - min)
          if(sec) date.setSeconds(date.getSeconds() - sec)

          return date
        },
        async openOrSettingMyPage(mypage){
            if(mypage.id != this.$store.state.portal.currentMyPage){
                this.$store.commit('portal/updateCurrentMyPage', mypage.id);
                await this.initMyPageList();
            } else {
                this.settingCurrentPage(mypage);
            }
        },

        // セッティングダイアログが表示するとき実行する
        settingCurrentPage(mypage) {
            let layoutObj = JSON.parse(mypage.layout);
            this.layout_ap_select = Object.assign({}, layoutObj);
            this.activeSettingMyPage = true;
            this.page_name_setting = mypage.page_name;
            this.myPageLayoutSelected.id = mypage.id;
            this.mypage_id_update = mypage.id;
            this.mypage_layout_id_old = JSON.parse(mypage.mst_mypage_layout_id);
            this.addLogOperation({action: 'pr1-02-portal-mypage-setting-display', result: 0, params:{mypage_id: mypage.id}});
        },

        async deleteCurrentPage(mypage_id) {
            this.$vs.dialog({
                type: 'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: `このマイページから削除しますか？`,
                accept: async () => {
                    await this.deleteMyPage(mypage_id);
                    await this.getMyPages();
                }
            });
        },

        saveSetting(closePopUp = true) {
            const root = this;
            this.$validator.validateAll().then(async result => {
                if (result) {
                    const currentLayout = cloneDeep(root.currentLayout);
                    currentLayout.map((item)=> {
                        if (item.name === PORTAL_COMPONENT.TOP_SCREEN) {
                            item.h = 6;
                        }
                    })
                    let myPage = {
                        id: root.myPageLayoutSelected.id,
                        mst_mypage_layout_id      : root.myPageLayoutSelected.mst_mypage_layout_id,
                        layout                    : JSON.stringify(currentLayout),
                        default                   : 1,
                    };
                    let layout = root.getLayoutLog(currentLayout);

                    await root.updateMyPage(myPage).then(
                        response => {
                                if(response.success) {
                                    root.myPageLayoutSelected.layout = JSON.stringify(currentLayout);
                                    this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 0, params:{mypage_id: myPage.id, display_app: layout}});
                                } else {
                                    this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                                }
                        },
                        error => {
                            this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                        }
                    );
                    this.editPortalStatus = false;
                    this.activeSettingMyPage = !closePopUp;
                    await root.getMyPages();
                    this.$store.commit('portal/updateChangeTemplateFlg', true);
                }
            });
        },
        editSetting() {
            this.editPortalStatus = true;
            this.activeSettingMyPage = false;
        },
        async selectSetting() {
            const root = this;
            let myPage = {
                id                        : root.myPageLayoutSelected.id,
                mst_mypage_layout_id      : root.myPageLayoutSelected.mst_mypage_layout_id,
                default                   : 1,
            };

            let layout = root.getLayoutLog(root.currentLayout);
            await root.updateMyPage(myPage).then(
                response => {
                    if(response.success) {
                        this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 0, params:{mypage_id: myPage.id, display_app: layout}});
                    } else {
                        this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                    }
                },
                error => {
                    this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                }
            );
            this.activeSettingMyPage = false;
            this.editPortalStatus = false;
            await root.getMyPages();
            this.$store.commit('portal/updateChangeTemplateFlg', true);
            this.myPageLayouts = await this.getMyPageLayout();
            this.myPageLayouts[0].layout_src = require('@assets/images/pages/portal/page_setting50_50.svg');
            this.myPageLayouts[1].layout_src = require('@assets/images/pages/portal/page_setting25_75.svg');
            this.myPageLayouts[2].layout_src = require('@assets/images/pages/portal/page_setting75_25.svg');
            this.myPageLayouts[3].layout_src = require('@assets/images/pages/portal/page_setting25_50_25.svg');
            const myPageLayouts = this.myPageLayouts;
            this.myPageLayoutSelected = Object.assign({},myPageLayouts.find((item) => {
                if (item.default === 1) {
                    return item;
                }
            }));
        },
        getLayoutLog (layout){
            let layoutSave = [];
            for (let i = 0; i < layout.length; i++) {
                let compoment = layout[i];
                if (compoment.name === PORTAL_COMPONENT.FAVORITE && compoment.show) {
                    layoutSave.push(this.favoriteTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.BULLETIN_BOARD && compoment.show && this.$store.state.groupware.checkBulletinBoardApp) {
                    layoutSave.push(this.bulletinBoardTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.FAQ_BULLETIN_BOARD && compoment.show && this.$store.state.groupware.checkFaqBulletinBoardApp) {
                    layoutSave.push(this.faqBulletinBoardTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.CIRCULAR && compoment.show) {
                    layoutSave.push(this.shachihataCloudTitle);
                }
                if(compoment.name === PORTAL_COMPONENT.TIME_CARD && compoment.show && this.$store.state.groupware.checkTimeCardApp && this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.attendance_system_flg !== 1) {
                    layoutSave.push(this.timeCardTitle);
                }
                if(compoment.name === PORTAL_COMPONENT.TIME_CARD_ATTENDANCE && compoment.show && this.$store.state.groupware.checkTimeCardApp && this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.attendance_system_flg === 1) {
                    layoutSave.push(this.timeCardTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.SCHEDULER && compoment.show && this.$store.state.groupware.checkCalendarApp) {
                    layoutSave.push(this.calendarTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.SPECIAL && compoment.show && this.isSpecialSiteSend) {
                    layoutSave.push(this.receiveCompanyTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.RECEIVE_PLAN && compoment.show && this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.receive_plan_flg && this.limit.limit_receive_plan_flg) {
                    layoutSave.push(this.receivePlanTitle);
                }
                if (compoment.name === PORTAL_COMPONENT.TO_DO_LIST && compoment.show) {
                    layoutSave.push(this.toDoListTitle);
                }
            }
        
            return layoutSave;
        },
        layoutSrcSelected(data) {
            this.myPageLayoutSelected = Object.assign({}, data);
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
        editPageName(layout) {
            this.inputPageName = JSON.parse(JSON.stringify(layout.page_name));
            this.editPageNameId = layout.id;
            this.editPageNameFlg = true;
            const _this = this;
            this.$nextTick(() => {
                _this.$refs.pageNameInput[0].focus();
            })
        },
        async savePageName(layout) {
            const root = this;
            const page_name = root.inputPageName;
            const id = root.editPageNameId;
            if (page_name === '') {
                this.$vs.notify({
                    text: 'テンプレート名をご入力ください。',
                    iconPack: 'feather',
                    icon: 'icon-alert-circle',
                    color: 'warning'
                })
                return false;
            } else if (page_name === layout.page_name) {
                this.pageName = '';
                this.editPageNameId = null;
                this.editPageNameFlg = false;
            } else {
                let myPage = {
                    id: id,
                    mst_mypage_layout_id      : layout.mst_mypage_layout_id,
                    page_name                 : page_name,
                };
                let currentLayout = root.getLayoutLog(root.currentLayout);
    
                await root.updateMyPage(myPage).then(
                    response => {
                        if(response.success) {
                            this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 0, params:{mypage_id: myPage.id, display_app: currentLayout}});
                        } else {
                            this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: currentLayout}});
                        }
                    },
                    error => {
                        this.addLogOperation({action: 'pr1-03-portal-mypage-save-setting', result: 1, params:{mypage_id: myPage.id, display_app: layout}});
                    }
                );
                layout.page_name = page_name;
                if (layout.id === this.currentMyPage) {
                    const listMyPages = cloneDeep(this.listMyPages);
                    listMyPages.map((mypage)=> {
                        if (mypage.id === this.currentMyPage) {
                            mypage.page_name = page_name;
                        }
                    })
                    this.$store.commit('portal/updateChangePageNameFlg', true);
                    this.$store.commit('portal/updateListMyPages', listMyPages);
                }
                this.pageName = '';
                this.editPageNameId = null;
                this.editPageNameFlg = false;
            }
        },
        async savePageNameByEnter(e, layout) {
            if (e.keyCode === 13) {
                await this.savePageName(layout);
                return false;
            }
        },
        async cancelSetting() {
            await this.getMyPages();
            this.editPortalStatus = false;
            this.activeSettingMyPage = false;
            this.$store.commit('portal/updateChangeTemplateFlg', true);
        },
        openFaqBulletinBoard() {
          this.addLogOperation({action: 'pr1-19-portal-single-faq-bulletin-board', result: 0})
          this.$router.push('/groupware/faq_bulletin');
        },
        async initMyPageList() {
          if (this.activeUserInfo && !this.activeUserInfo.isAuditUser) {
            await this.getMyPages();
            this.myPageLayouts = await this.getMyPageLayout();
            this.myPageLayouts[0].layout_src = require('@assets/images/pages/portal/page_setting50_50.svg');
            this.myPageLayouts[1].layout_src = require('@assets/images/pages/portal/page_setting25_75.svg');
            this.myPageLayouts[2].layout_src = require('@assets/images/pages/portal/page_setting75_25.svg');
            this.myPageLayouts[3].layout_src = require('@assets/images/pages/portal/page_setting25_50_25.svg');
            const myPageLayouts = this.myPageLayouts;
            this.myPageLayoutSelected = Object.assign({},myPageLayouts.find((item) => {
              if (item.default === 1) {
                return item;
              }
            }));
          }
        },
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
        draggable,
        VxPagination
    },
    async mounted() {
        var root = this;
        //Refresh token
        var storageDay = this.$moment(window.sessionStorage.getItem('dateSetRefreshToken'));//now
        var dateNow = this.$moment(new Date());
        var tokenRefreshTime = dateNow.diff(storageDay, 'hours');
        var tokenRefreshMinute = dateNow.diff(storageDay, 'minutes');
        var tokenRefreshSeconds = dateNow.diff(storageDay, 'seconds');
        if(tokenRefreshTime > 12 || (tokenRefreshTime == 12 && tokenRefreshMinute < 1 && tokenRefreshSeconds < 1)){
            var refreshTokenByCookie = this.$cookie.get('refreshToken');
            if(refreshTokenByCookie){
                root.updateRefreshToken(refreshTokenByCookie).then(data => {
                    this.updateCookiesGroupware(data);
                });
            }
        }

        if(root.$route.path.includes('/portal') || root.$route.path.includes('/groupware')){
           if(root.countUnread){
                clearInterval(root.countUnread);
           }
           await root.getCountUnread();
        }else{
            clearInterval(root.countUnread);
        }
        $(document).on("click",".bell-icon-notice",function() {
            root.currentPageNotice = 1;
            root.checkOpenDropdownNotice(true);
        });
        let loginUser =JSON.parse(getLS('user'))
        var myCompany = this.$store.state.groupware.myCompany;
        if (myCompany && myCompany.portal_flg && !loginUser.isAuditUser){
              root.getAvatarUser().then(avatar => {
                if(avatar.user_profile_data){
                    $( ".avatar-user" ).append('<img style="width: 100%;" src="data:image/jpeg;base64,'+ avatar.user_profile_data+'" alt="avatar">');
                }else{
                    $(".avatar-user").append('<span><i style="width: 100%;" class="fas fa-user"></i></span>')
                }
              }).catch(error => { return []; });
        }else{
                    $( ".avatar-user" ).hide();
        }
        /*PAC_5-3156 S*/
        await this.getFaqBbsUnreadNoticeCount()
        /*PAC_5-3156 E*/
    },
    async created() {
      this.$store.dispatch('updateLoading', true);
      /*PAC_5-3156 S*/
      let loginUser = JSON.parse(getLS('user'))
      if(!loginUser.isAuditUser){
        await this.checkUserPacAppUsageStatus()
      }
      /*PAC_5-3156 E*/
      if (this.$route.path.includes('/portal')) {
        await this.checkAccessToken();
        await this.checkUserAppUsageStatus();
        /*await this.checkAppRole();*/
        if (this.listMyPages.some(item => item.id === parseInt(this.$route.params.id))) {
          this.$store.commit('portal/updateCurrentMyPage', parseInt(this.$route.params.id));
        }
        if ((this.$store.state.groupware.checkBulletinBoardApp || this.$store.state.groupware.checkCalendarApp)) {
          this.showMenuAvatar = true;
        }
      } else {
        this.showMenuAvatar = false;
      }

      if (this.$route.path.includes('/groupware')) {
        await this.checkUserAppUsageStatus();
        /*await this.checkAppRole();*/
        if ((this.checkBulletinBoardApp || this.checkCalendarApp)) {
          this.showMenuAvatar = true;
        } else {
          this.showMenuAvatar = false;
        }
      }
      await this.initMyPageList();
      this.$store.dispatch('updateLoading', false);
    }
}
</script>
<style lang="scss">
    .vs-input {
        width: 100%;
    }
    .mypage-active{
        background-color:rgba(226, 227, 228, 0.23);
        border-radius: 5px;
    }
    .app-select span.con-slot-label {
        margin-left: 1rem;
    }
    .vs-popup {
        width: 500px !important;
    }
    .detail-popup {
      .vs-popup{
        width: 800px !important;
      }
    }
    .img-layout {
        max-width: 100%;
        border: 5px solid #d1ecff;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }
    .img-layout-selected {
        box-shadow: 0 0 2px 1px #0984e3;
    }
    .notice-groupware-avatar{
        border-radius: 50%;
        width: 35px !important;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #0984e3;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }
    #arrow{
        cursor:pointer;
    }
    .around{
        animation:0.5s around_arrow;
        animation-fill-mode:forwards;
    }
    .around_return{
        animation:0.5s around_arrow_return;
        animation-fill-mode:forwards;
    }
    @keyframes around_arrow{
        0%{ transform:rotate(0);}
        100%{ transform:rotate(180deg); }
    }
    @keyframes around_arrow_return{
        0%{ transform:rotate(180deg);}
        100%{ transform:rotate(0); }
    }
    .app-select
    {
        .vs-checkbox--input:disabled+.vs-checkbox{
            background-color: grey !important;
        }
    }
    .count-notice-unread{
        width: 20px;
        height: 20px;
        color: white;
        border-radius: 50%;
        position: absolute;
        left: 10px;
        bottom: 16px;
    }
    .count-notice-unread-big{
            width: 22px;
            font-size: 12px;
            align-items: center;
            display: flex;
            padding: 0 2px;
    }
    .mouse-hover{
        cursor: pointer;
    }
    .setting-page-layout .vs-popup {
        width: 700px !important;
        .page-name {
            flex-shrink: 0;
            height: auto;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            .feather-icon {
                width: 20px;
                height: 20px;
                vertical-align: middle;
                cursor: pointer;
            }
        }
        .app-select {
            .con-slot-label {
                svg, img {
                    width: 1rem;
                    height: 1rem;
                }
            }
        }
    }
</style>
