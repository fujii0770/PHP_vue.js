<template>
    <div id="portal-groupware-my-group">
        <groupware-page ref ="" :urlGroupware="urlMyGroup" ></groupware-page>
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
                urlMyGroup: `${config.GROUPWARE_URL}/embed/group`,
            }
        }
    }

</script>
<style lang="scss">
    .iframe-groupware{
        height: calc(100vh - 87px);
    }
</style>