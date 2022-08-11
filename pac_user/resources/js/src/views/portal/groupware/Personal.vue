<template>
    <div id="portal-groupware-personal">
        <groupware-page ref ="" :urlGroupware="urlPersonal" ></groupware-page>
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
                urlPersonal: `${config.GROUPWARE_URL}/embed/profile`,
            }
        }
    }

</script>
<style lang="scss">
    .iframe-groupware{
        height: calc(100vh - 87px);
    }
</style>