<template>
    <div class="comp-portal-calendar">
        <div class="iframe-groupware" style="width:100%;">
            <iframe v-if="width > 0 && height > 0"  style="width:100%; height: 100%; border: unset; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); border-radius: 10px;" :width="width + 'px'" :height="height + 'px'" :src="src" allowfullscreen></iframe>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    export default {
        props: {
            height     : { type: Number},
            width      : { type: Number},
            src     : { type: String}
        },
        mounted() {
            this.updateActionLog();
        },
        data() {
            return {
                actionLog: '',
            }
        },
        methods: {
          ...mapActions({
              addLogOperation: "logOperation/addLog",
          }),
          refresh(){
            location.reload();
          },
          updateActionLog() {
              if (this.src.includes('/embed/notification_setting')) {
                  this.actionLog = 'pr1-16-portal-setting-notification';
              }
              if (this.src.includes('/embed/group')) {
                  this.actionLog = 'pr1-15-portal-setting-mygroup';
              }
              if (this.src.includes('/embed/profile')) {
                  this.actionLog = 'pr1-14-portal-setting-personal';
              }
              if (this.actionLog) {
                  this.addLogOperation({action: this.actionLog, result: 0});
              }
          },
        },
    }
</script>