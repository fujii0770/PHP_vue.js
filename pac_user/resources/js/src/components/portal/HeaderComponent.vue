<template>
  <div class="header-comp">
    <vs-row class="border-bottom pb-4 mb-2" vs-type="flex" vs-justify="space-between">
      <vs-col v-if="title != ''" vs-w="8" vs-align="center" vs-type="flex" vs-justify="flex-start" class="p">
        <h5 v-if="editStatus" class="title-app-portal" :id="title">{{ title }}</h5>
        <h5 v-else @click="gotoGroupwareFull()" class="title-app-portal mouse-hover" :id="title">{{ title }}</h5>
      </vs-col>
      <vs-col v-if="logoShachihataDefault" vs-w="8" vs-align="center" vs-type="flex" vs-justify="flex-start" class="p">
        <img class="image-shachihata-cloud-header"  :src="logoShachihataDefault" alt="Logo service">
      </vs-col>
      <vs-col vs-w="4" vs-align="center" vs-type="flex" vs-justify="flex-end" class="p">
        <vs-dropdown vs-custom-content class="cursor-pointer" vs-trigger-click>
            <span class="cursor-pointer navbar-fuzzy-search mr-4 feather-icon select-none relative">
              <img :src="require('@assets/images/pages/portal/close.svg')" />
            </span>
            <vs-dropdown-menu class="vx-navbar-dropdown">
                <ul>
                    <li
                        class=" d-inline-flex py-2 px-4 cursor-pointer hover:bg-primary hover:text-white"
                        @click="$emit('hiddenAppPortal')" >
                        <span>閉じる</span>
                    </li>
                </ul>
            </vs-dropdown-menu>
        </vs-dropdown>
      </vs-col>
    </vs-row>
  </div>
</template>

<script>
   import { mapState, mapActions } from "vuex";
    export default {
        props: {
          title:{
            default:'',
            type:String
          },
          urlGroupware:{
            default:'',
            type:String
          },
          logoShachihataDefault: {
          }
        },
        data () {
            return {
              actionLog: '',
            }
        },
        methods: {
            ...mapActions({
                addLogOperation: "logOperation/addLog",
            }),

          gotoGroupwareFull() {
            this.actionLog = '';
            if (this.urlGroupware === '/groupware/calendar') {
                this.actionLog = 'pr1-11-portal-single-calendar-board';
            }
            if (this.urlGroupware === '/groupware/bulletin') {
                this.actionLog = 'pr1-10-portal-single-bulletin-board';
            }
            if (this.urlGroupware === '/groupware/receive_plan') {
                this.actionLog = 'pr1-22-portal-single-receive-plan';
            }
            if (this.urlGroupware === '/groupware/to-do-list') {
                this.actionLog = 'pr1-24-portal-single-to-do-list';
            }
            if (this.actionLog) {
                this.addLogOperation({action: this.actionLog, result: 0});
            }
            if(this.urlGroupware == '/groupware/time-card') {
                this.$router.push('/groupware/time-card')
            }
            if(this.urlGroupware == 'https://swk.shachihata.com/swk') {
              window.open('https://swk.shachihata.com/swk', '_blank')
            } else {
              if(this.urlGroupware){
                this.$router.push(this.urlGroupware);
              }
            }
            
          },
        },
        computed: {
            editStatus() {
                return this.$store.state.portal.editStatus;
            }
        },

        watch:{
        },

        async mounted() {
          if(this.title == "掲示板"){
            let bulletin = document.getElementById('掲示板');
            bulletin.classList.add("mouse-hover");
          }
          if(this.title == "スケジューラ"){
            let scheduler = document.getElementById('スケジューラ');
            scheduler.classList.add("mouse-hover");
          }
          if(this.title == 'タイムカード') {
              let timecard = document.getElementById('タイムカード');
              timecard.classList.add("mouse-hover");
          }
        },

        async created() {
        }
    }
</script>
<style scoped>
  .header-comp:hover{
    cursor: move !important;
  }
  .image-shachihata-cloud-header{
    height: 25px !important;
  }
</style>
