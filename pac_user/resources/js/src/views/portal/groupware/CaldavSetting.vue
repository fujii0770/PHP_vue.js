<template>
    <div id="portal-groupware-caldav-setting">
        <groupware-page ref ="" :urlGroupware="urlCaldavSetting" ></groupware-page>
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
              urlCaldavSetting: `${config.GROUPWARE_URL}/embed/caldav_setting`,
            }
        }
    }

</script>
<style lang="scss">
    .iframe-groupware{
        height: calc(100vh - 87px);
    }
</style>
