<template>
    <div id="portal-groupware-calendar">
        <top-menu v-if="!isMobile"></top-menu>
        <vs-row>
            <div style="width:100%">
                <iframe-groupware ref ="" :width="widthCalendar" :height="heightCalendar" :src="urlGroupware + '?width='+widthCalendar+'&'+'height='+heightCalendar"></iframe-groupware>
            </div>
        </vs-row>
    </div>
</template>
<script>
    import { mapState, mapActions } from "vuex";
    import config from "../../app.config";
    import IframeGroupware from "./IframeGroupware"
    import TopMenu from "./TopMenu";

    export default {
        props: {
            urlGroupware     : { type: String}
        },
        components: {
            IframeGroupware,
            TopMenu
        },
        data() {
            return {
                heightCalendar: 0,
                widthCalendar: 0,
                isMobile: false
            }
        },
        computed: {

        },
        methods: {

        },

        created() {
            if (
                /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
                  navigator.userAgent)
            ) {
              this.isMobile = true
            }
        },
        mounted() {
            this.heightCalendar = $(".iframe-groupware").outerHeight();
            this.widthCalendar = $(".iframe-groupware").outerWidth();
        }
    }

</script>
