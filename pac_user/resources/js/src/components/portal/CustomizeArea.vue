<template>
    <div class="comp-portal-customize">
        <vx-card class="border-8 border-white border-solid" v-if="countCustomize > 0">
            <div class="customize-box">
                <div class="customize-item" v-if="pagination1.total && pagination1.total > 0">
                    <notification v-if="customize1.type == 1"
                                  v-model="pagination1"
                                  :notices="customize1.list"
                                  :area_title="customize1.area_title"
                    ></notification>
                </div>
 
                <div class="customize-item" v-if="pagination2.total && pagination2.total > 0">
                    <notification v-if="customize2.type == 1"
                                  v-model="pagination2"
                                  :notices="customize2.list"
                                  :area_title="customize2.area_title"
                    ></notification>
                </div>
            </div>
        </vx-card>
    </div>
</template>

<script>
import {mapState, mapActions} from "vuex";
import {PORTAL_COMPONENT} from '../../enums/portal_component';

export default {
    components: {
        Notification: () => import('@/components/portal/customize_area/Notification'),
    },
    name: "CustomizeArea",
    props: [],
    async mounted() {
        await this.onSearch(1);
        await this.onSearch(2);
        if (this.countCustomize > 0) {
            this.$emit('changeHasData', PORTAL_COMPONENT.CUSTOMIZE_AREA, true)
        } else {
            this.$emit('changeHasData', PORTAL_COMPONENT.CUSTOMIZE_AREA, false)
        }
    },
    data() {
        return {
            customize1: {area_title: '', list: [], type: 0,},
            customize2: {area_title: '', list: [], type: 0,},
            pagination1: {totalPage: 0, currentPage: 1, limit: 5, total: 0, from: 1, to: 5},
            pagination2: {totalPage: 0, currentPage: 1, limit: 5, total: 0, from: 1, to: 5},
        };
    },
    methods: {
        ...mapActions({
            getCustomizeAreaList: "customizeArea/getCustomizeAreaList",
        }),
        onSearch: async function (location_type) {
            if (location_type === 1) {
                let param = {
                    limit: this.pagination1.limit,
                    page: this.pagination1.currentPage,
                    location_type: location_type,
                };
                const data = await this.getCustomizeAreaList(param);
                if (data) {
                    this.customize1.list = data.data;
                    this.customize1.area_title = data.area_title;
                    this.customize1.type = data.type;
                    this.pagination1.total = data.total;
                    if (this.pagination1.totalPage !== data.last_page
                        && this.pagination1.currentPage > data.last_page
                        && data.last_page > 0
                    ) {
                        this.pagination1.currentPage = data.last_page;
                    }
                    this.pagination1.totalPage = data.last_page;
                    this.pagination1.limit = data.per_page;
                    this.pagination1.from = data.from;
                    this.pagination1.to = data.to;
                }
            }
            if (location_type === 2) {
                let param = {
                    limit: this.pagination2.limit,
                    page: this.pagination2.currentPage,
                    location_type: location_type,
                };
                const data = await this.getCustomizeAreaList(param);
                if (data) {
                    this.customize2.list = data.data;
                    this.customize2.area_title = data.area_title;
                    this.customize2.type = data.type;
                    this.pagination2.total = data.total;
                    if (this.pagination2.totalPage !== data.last_page
                        && this.pagination2.currentPage > data.last_page
                        && data.last_page > 0
                    ) {
                        this.pagination2.currentPage = data.last_page;
                    }
                    this.pagination2.totalPage = data.last_page;
                    this.pagination2.limit = data.per_page;
                    this.pagination2.from = data.from;
                    this.pagination2.to = data.to;
                }
            }
        },
    },
    computed: {
        countCustomize: {
            get() {
                let countCustomize1 = this.pagination1.total || 0;
                let countCustomize2 = this.pagination2.total || 0;
                countCustomize1 = isNaN(parseInt(countCustomize1)) ? 0 : parseInt(countCustomize1);
                countCustomize2 = isNaN(parseInt(countCustomize2)) ? 0 : parseInt(countCustomize2);
                return countCustomize1 + countCustomize2;
            }
        },
    },
    watch: {
        'pagination1.currentPage': function (val) {
            this.onSearch(1);
        },
        'pagination2.currentPage': function (val) {
            this.onSearch(2);
        },
    },
}
</script>

<style scoped>

</style>
