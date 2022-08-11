<template>
    <div id="portal-groupware-calendar" class="mobile m-t--50">
        <groupware-page ref ="" :urlGroupware="urlCalendar" ></groupware-page>
    </div>
</template>
<script>
    import { mapState, mapActions } from "vuex";
    import config from "../../../app.config";
    import GroupwarePage from "../../../components/portal/GroupwarePage"
    export default {
        components: {
            GroupwarePage
        },
        data() {
            return {
                urlCalendar: `${config.GROUPWARE_URL}/embed/calendar`,
            }
        }
    }

</script>
<style lang="scss">
    .iframe-groupware{
        height: calc(100vh - 87px);
    }
    #portal-groupware-calendar.mobile{
      width: 100%;
    }
    @media (max-width: 480px){
      #portal-groupware-calendar.mobile {
        padding: 0 1.2rem;
      }
    }
    @media (max-width: 600px) {
      #portal-groupware-calendar.m-t--50{
        margin-top: -50px!important;
      }
    }
</style>
