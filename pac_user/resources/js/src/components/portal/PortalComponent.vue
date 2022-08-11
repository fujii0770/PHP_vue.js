<template>
  <span>
    <attendance-system v-if="currentLayout.show_time_card && componentType == PORTAL_COMPONENT.TIME_CARD && $store.state.groupware.myCompany.attendance_system_flg == 1 && $store.state.groupware.checkTimeCardApp" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])" :src="urlAttendanceSystem" class="mb-1 attendance-system"></attendance-system>
    <favorite ref ="" v-if="componentType == PORTAL_COMPONENT.FAVORITE && currentLayout.show_favorite" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])" :class="['mb-1', componentType]"></favorite>
    <time-card v-if="currentLayout.show_time_card && componentType == PORTAL_COMPONENT.TIME_CARD && $store.state.groupware.myCompany.attendance_system_flg != 1 && $store.state.groupware.checkTimeCardApp" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])"></time-card>
    <vx-card v-if="componentType == PORTAL_COMPONENT.SCHEDULER && currentLayout.show_scheduler && $store.state.groupware.checkCalendarApp" :class="['mb-1', componentType]">
      <HeaderComponent title="スケジューラ" :urlGroupware="'/groupware/calendar'" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])"></HeaderComponent>
      <iframe-groupware ref ="" :width="widthCalendar" :height="heightCalendar" :src="urlCalendar + '?width='+widthCalendar+'&'+'height='+heightCalendar"></iframe-groupware>
    </vx-card>
    <vx-card v-if="componentType == PORTAL_COMPONENT.BULLETIN_BOARD && currentLayout.show_bulletin_board && $store.state.groupware.checkBulletinBoardApp" :class="['mb-1', componentType]" :id="[componentType]">
      <HeaderComponent title="掲示板" urlGroupware="/groupware/bulletin" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])"></HeaderComponent>
      <bbs class="mb-4"></bbs>
      </vx-card>
    <vx-card v-if="componentType == PORTAL_COMPONENT.FAQ_BULLETIN_BOARD && currentLayout.show_faq_bulletin_board && $store.state.groupware.checkFaqBulletinBoardApp" :class="['mb-1', componentType]" :id="[componentType]">
      <HeaderComponent title="サポート掲示板" urlGroupware="/groupware/faq_bulletin" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])"></HeaderComponent>
      <faq-bbs class="mb-4"></faq-bbs>
      </vx-card>
    <circular-portal ref="" v-if="componentType == PORTAL_COMPONENT.CIRCULAR && currentLayout.show_circular" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])" :class="['mb-1', componentType]"></circular-portal>
    <special-portal ref="" v-if="componentType == PORTAL_COMPONENT.SPECIAL && currentLayout.show_special && $store.state.special.checkSpecialSend" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])" :class="['mb-1', componentType]"></special-portal>
    <file-mail-card ref="" v-if="componentType == PORTAL_COMPONENT.FILE_MAIL && currentLayout.show_file_mail && $store.state.groupware.checkFileMailApp" @hiddenAppPortal="$emit('hiddenAppPortal', [componentType])" :class="['mb-1', componentType]"></file-mail-card>
  </span>
</template>
<script>
import {PORTAL_COMPONENT} from '../../enums/portal_component';
import Favorite from "../../components/portal/Favorite";
import IframeGroupware from "../../components/portal/IframeGroupware";
import CircularPortal from "../../components/portal/CircularPortal";
import HeaderComponent from "../../components/portal/HeaderComponent";
import SpecialPortal from "../../components/portal/SpecialPortal";
import config from "../../app.config";
import Bbs from "../../components/portal/Bbs";
import TimeCard from "./TimeCard";
import FileMailCard from "../../components/portal/FileMailCard";
import FaqBbs from "./FaqBbs";
import AttendanceSystem from "./AttendanceSystem";

export default {
    components: {
      FaqBbs,
      TimeCard,
      HeaderComponent,
      Favorite,
      IframeGroupware,
      CircularPortal,
      Bbs,
      FileMailCard,
      SpecialPortal,
      AttendanceSystem
    },
    name: "PortalComponent",
    props: {
      componentType: {
        default: '',
        type: String
      },
      currentLayout : {
        default: null,
        type: Object
      },
    },
    async mounted() {
      this.heightCalendar = $(".scheduler .iframe-groupware").outerHeight();
      this.widthCalendar = $(".scheduler .iframe-groupware").outerWidth();
      this.heightBBS = $("#bulletin_board").outerHeight();
      this.widthBBS = $("#bulletin_board").outerWidth();
    },
    data() {
      return {
        PORTAL_COMPONENT: PORTAL_COMPONENT,
        heightCalendar: 0,
        widthCalendar: 0,
        heightBBS: 0,
        widthBBS: 0,
        urlCalendar: `${config.GROUPWARE_URL}/embed/calendar`,
        urlAttendanceSystem: 'https://swk.shachihata.com/swk',
      }
    },
}
</script>
