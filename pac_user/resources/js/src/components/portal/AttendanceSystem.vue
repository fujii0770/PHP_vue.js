<template>
    <div class="comp-portal-favorite">
        <vx-card class="mb-4">
            <HeaderComponent title="勤怠システム" :url-groupware="src" @hiddenAppPortal="$emit('hiddenAppPortal')"></HeaderComponent>
            <div class="comp-portal-attendance-system" style="height: 417px">
                <div>
                    <iframe style="width:100%; 
                    height: 417px; 
                    border: unset; 
                    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0); 
                    border-radius: 10px;" 
                    :src="src" 
                    allowfullscreen>
                    </iframe>
                </div>

            </div>
        </vx-card>
    </div>
</template>


<script>
    import HeaderComponent from "./HeaderComponent";
    import { mapState, mapActions } from "vuex";
    export default {
        components: {
            HeaderComponent
        },
        props: {
            height: { type: Number},
            width: { type: Number},
            src: { type: String}
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