<template>
    <div class="comp-customize-notification">
        <div class="area-title" >{{area_title}}</div>
        <div class="notice-list">
            <div class="notice-item" @click="showInfo(notice)" v-for="notice in notices">
                <span>{{notice.subject}}</span>
            </div>
        </div>
        <vx-pagination style="zoom: 0.7;font-size: 20px!important;" :totalSmall="pagination.totalPage" :currentPage.sync="pagination.currentPage" class="pt-5 pb-3"></vx-pagination>

        <vs-popup classContent="popup-example" :title="selected.subject" :active.sync="activeModal">
            <vs-row v-if="selected.image !== '' && selected.image !== null && selected.image !== undefined" class="mt-5">
                <vs-col vs-w="6">
                    <span v-html="selected.contents"></span>
                </vs-col>
                <vs-col v-if="selected.url !== '' && selected.url !== null" vs-w="6">
                    <a :href="selected.url" target="_blank">
                        <img :src="`data:image/png;base64, ${selected.image}`" style="width: 100%;" />
                    </a>
                </vs-col>
                <vs-col v-else vs-w="6">
                    <img :src="`data:image/png;base64, ${selected.image}`" style="width: 100%;" />
                </vs-col>
            </vs-row>
            <div v-else-if="selected.url !== '' && selected.url !== null" class="mt-5">
                <span v-html="selected.contents"></span><br />
                <a :href="selected.url" target="_blank" v-html="selected.url"></a>
            </div>
            <div v-else class="mt-5">
                <span v-html="selected.contents"></span>
            </div>
            <vs-row class="mt-5">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="closeInfo" color="dark" type="border">閉じる</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
</template>

<script>
import VxPagination from '@/components/portal/customize_area/VxPagination.vue';

export default {
    components: {
        VxPagination,
    },
    name: "Notification",
    model: {
        prop: 'pagination',
        event: 'change'
    },
    props: {
        pagination: {},
        notices: {},
        area_title: String,
    },
    mounted() {},
    data() {
        return {
            activeModal: false,
            selected: {},
        };
    },
    methods: {
        showInfo(notice) {
            if (notice.content_flg == 1) {
                this.selected = Object.assign({}, notice)
                this.activeModal = true;
            } else if (notice.content_flg == 2) {
                window.open(notice.url, '_blank');
            }
        },
        closeInfo() {
            this.activeModal = false;
        }
    },
    computed: {},
    watch:{
        'pagination.currentPage': function (val) {
            this.$emit('change', this.pagination)
        },
    },
}
</script>

<style scoped>

</style>
