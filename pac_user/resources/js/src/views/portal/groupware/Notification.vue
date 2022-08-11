<template>
    <div id="portal-groupware-notification">
        <groupware-page ref ="" :urlGroupware="urlNotification" ></groupware-page>
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
                urlNotification: `${config.GROUPWARE_URL}/embed/notification_setting`,
            }
        }
    }

</script>
<style lang="scss">
    .iframe-groupware{
        height: calc(100vh - 87px);
    }
</style>