<template>
    <div class="comp-portal-menu">
        <div class="mb-4 menu">
            <span v-if="$route.path.includes('/portal')" class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/home.svg')" /></span>
            <span v-else class="mb-1 cursor-pointer"  @click="$router.push('/portal')"><img :src="require('@assets/images/pages/portal/home.svg')" /></span>
            <div v-if="this.$store.state.groupware.checkCalendarApp">
              <span v-if="$route.path.includes('/groupware/calendar')" class="mb-1 active-menu cursor-pointer"><img :src="require('@assets/images/pages/portal/calendar.svg')" /></span>
              <span v-else class="mb-1 cursor-pointer" @click="openCalendar"><img :src="require('@assets/images/pages/portal/calendar.svg')" /></span>
            </div>
            <div v-if="this.$store.state.groupware.checkBulletinBoardApp">
              <span class="mb-1 active-menu cursor-pointer" v-if="$route.path.includes('/groupware/bulletin')" ><img :src="require('@assets/images/pages/portal/board.svg')" /></span>
              <span v-else class="mb-1 cursor-pointer" @click="openbulletinBoard"><img :src="require('@assets/images/pages/portal/board.svg')" /></span>
            </div>
            <div v-if="this.$store.state.groupware.checkFileMailApp" style="width: 24px">
              <span class="mb-1 active-menu cursor-pointer" v-if="$route.path.includes('/groupware/file_mail')" ><img :src="require('@assets/images/pages/portal/folder-mail.svg')" /></span>
              <span v-else class="mb-1 cursor-pointer" @click="openFileMail"><img :src="require('@assets/images/pages/portal/folder-mail.svg')" /></span>
            </div>
            <!-- <feather-icon icon="HomeIcon" style="width: 15px;" @click="$router.push('/settings')" class="cursor-pointer navbar-fuzzy-search"></feather-icon> -->
            <div v-if="this.$store.state.groupware.checkTimeCardApp" style="width: 24px">
                <span class="mb-1 active-menu cursor-pointer" v-if="$route.path.includes('/time-card')" style="padding: 0 7px 0 8.4px;position: absolute;left: -1px;"><i class="fas fa-user-clock" style="font-size: 17px"></i></span>
                <span v-else class="mb-1 cursor-pointer" @click="openTimeCard" style="padding: 0 7px 0 8.4px;position: absolute;left: -1px;"><i class="fas fa-user-clock" style="font-size: 17px"></i></span>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    export default {
        props: [],
        mounted() {},
        data() {
            return {
            }
        },
        methods: {
            ...mapActions({
                addLogOperation: "logOperation/addLog",
            }),
          refresh(){
            location.reload();
          },
          openCalendar(){
            this.addLogOperation({action: 'pr1-11-portal-single-calendar-board', result: 0})
            this.$router.push('/groupware/calendar');
          },
          openbulletinBoard(){
            this.addLogOperation({action: 'pr1-10-portal-single-bulletin-board', result: 0})
            this.$router.push('/groupware/bulletin');
          },
          openFileMail(){
            this.addLogOperation({action: 'pr1-18-portal-single-disk-mail-file', result: 0})
            this.$router.push('/groupware/file_mail/application');
          },
          openTimeCard(){
            // this.addLogOperation({action: 'pr1-10-portal-single-bulletin-board', result: 0})
            this.$router.push('/groupware/time-card');
          }
        },
    }
</script>
<style lang="scss">
  .comp-portal-menu {
    z-index: 1;
    position: relative;
  }
</style>

