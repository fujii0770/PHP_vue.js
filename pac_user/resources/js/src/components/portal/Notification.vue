<template>
    <div class="comp-portal-notification">
        <vx-card  :key="forceRender">
            <vs-tabs v-model="tab_notice">
                <vs-tab :label=" !countNoticeUnread ? 'お知らせ' : 'お知らせ('+countNoticeUnread+')'" class="list-notification">
                    <vs-table class="" :data="listDataUser" noDataText="データがありません。" stripe @selected="onShowReading">
                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" :style="{fontWeight:((!tr.read_flg)?'bold':'normal')}">
                                <td class="w-310 h-45" :title="tr.subject">
                                  <span>  
                                    <span class="notification-icon" v-if="tr.icon_title" :style="'color:' + tr.icon_color + ';background-color:' + tr.icon_bg_color + ';'">{{tr.icon_title}}</span>
                                    <span class="notification-subject">{{tr.subject}}</span>
                                  </span>
                                </td>
                                <td class="w-40 h-45" :title="tr.subject">
                                    <span>{{tr.create_at | moment("MM/DD")}}</span>
                                </td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <vx-pagination :totalSmall="pagination.totalPage" :currentPage.sync="pagination.currentPage" class="pt-5"></vx-pagination>
                </vs-tab>
                <!--                <vs-tab disabled label="Disabled">-->
                <vs-tab   :label=" !countNoticeSystemUnread ? 'リリース情報' : 'リリース情報('+countNoticeSystemUnread+')'"  class="list-notification">
                    <vs-table class="" :data="listDataSystem" noDataText="データがありません。" stripe @selected="onShowReading">
                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" :style="{fontWeight:((!tr.read_flg)?'bold':'normal')}">
                                <td class="w-310 h-45" :title="tr.subject">
                                  <span>
                                    <span class="notification-icon" v-if="tr.icon_title" :style="'color:' + tr.icon_color + ';background-color:' + tr.icon_bg_color + ';'">{{tr.icon_title}}</span>
                                    <span class="notification-subject">{{tr.subject}}</span>
                                  </span>
                                </td>
                                <td class="w-40 h-45" :title="tr.subject">
                                    <span>{{tr.create_at | moment("MM/DD")}}</span>
                                </td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <vx-pagination :totalSmall="paginationSystem.totalPage" :currentPage.sync="paginationSystem.currentPage" class="pt-5"></vx-pagination>
                </vs-tab>
            </vs-tabs>
        </vx-card>

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
                    <vs-button @click="updateRead" color="dark" type="border">閉じる</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    components: {
        VxPagination,
    },
    name: "Notification",
    props: [],
    mounted() {
        this.onSearch();
    },
    data() {
        return {
            listDataUser:[],
            listDataSystem:[],
            pagination:{ totalPage:0, currentPage:1, limit: 5, totalItem:0, from: 1, to: 5 },
            paginationSystem:{ totalPage:0, currentPage:1, limit: 5, totalItem:0, from: 1, to: 5 },
            activeModal: false,
            selected: {},
            countNoticeUnread: 0,
            countNoticeSystemUnread: 0,
            forceRender:0,
            tab_notice:0,
        };
    },
    methods: {
        ...mapActions({
            getListNotification: "notice/getListNotification",
            updateReadNotification: "notice/updateReadNotification",
            getUnreadNoticeTotal: "notice/getUnreadNoticeTotal",
        }),
        onSearch: async function (type = '') {
            if (type === '' || type === 'user') {
                let infoUser  = {
                    limit      : this.pagination.limit,
                    page       : this.pagination.currentPage,
                    type       : 1,
                };
                const data = await this.getListNotification(infoUser);

                this.listDataUser           = data.notices.data;
                this.pagination.totalItem   = data.notices.total;
                if (this.pagination.totalPage !== data.notices.last_page
                    && this.pagination.currentPage > data.notices.last_page
                    && data.notices.last_page > 0
                ){
                    this.pagination.currentPage = data.notices.last_page;
                }
                this.pagination.totalPage   = data.notices.last_page;
                this.pagination.limit       = data.notices.per_page;
                this.pagination.from        = data.notices.from;
                this.pagination.to          = data.notices.to;
                this.countNoticeUnread      = data.countNoticeUnread;
            }

            if (type === '' || type === 'system') {
                let infoSystem = {
                    limit: this.paginationSystem.limit,
                    page: this.paginationSystem.currentPage,
                    type: 0,
                };
                const dataSystem = await this.getListNotification(infoSystem);
                this.listDataSystem = dataSystem.notices.data;
                this.paginationSystem.totalItem = dataSystem.notices.total;
                if (this.paginationSystem.totalPage !== dataSystem.notices.last_page
                    && this.paginationSystem.currentPage > dataSystem.notices.last_page
                    && dataSystem.notices.last_page > 0
                ){
                    this.paginationSystem.currentPage = dataSystem.notices.last_page;
                }
                this.paginationSystem.totalPage = dataSystem.notices.last_page;
                this.paginationSystem.limit = dataSystem.notices.per_page;
                this.paginationSystem.from = dataSystem.notices.from;
                this.paginationSystem.to = dataSystem.notices.to;
                this.countNoticeSystemUnread = dataSystem.countNoticeUnread;
            }
        },

        onShowReading(data) {
            this.selected = Object.assign({}, data)
            const regEx= new RegExp('<[^>]+>','g');
            if (data.contents && !regEx.test(data.contents)){
              this.selected.contents = data.contents.replace(/\n/g,"<br>");
            }
            this.activeModal = true;
            if (!data.read_flg) {
                let noticeData = {
                    mst_notice_management_id      : data.id,
                    read_flg                      : 1,
                };
                this.updateReadNotification(noticeData);
                if(this.tab_notice == 1 && this.countNoticeSystemUnread > 0){
                    this.tab_notice = 1;
                    this.countNoticeSystemUnread--;
                }            
                
                if(this.tab_notice == 0 && this.countNoticeUnread > 0){
                    this.tab_notice = 0;
                    this.countNoticeUnread--;
                }
                //同時だと件数が減ってなかったので1秒遅延させて実行
                this.$nextTick(() => {
                    window.setTimeout(() => {
                        this.getUnreadNoticeTotal();
                    }, 1000)
                });
            }
            data.read_flg = 1;
        },

        updateRead() {
            this.activeModal = false;
          //  this.selected = {};
        }
    },
    computed: {},
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch('user');
        },
        'paginationSystem.currentPage': function (val) {
            this.onSearch('system');
        },
        'countNoticeUnread': function (val) {
              this.forceRender += 1;
        },
        'countNoticeSystemUnread': function (val) {
              this.forceRender += 1;
        },
    },
}
</script>

<style>
    .vs-con-table .vs-con-tbody {
        overflow: hidden !important;
    }

    .vs-con-table .vs-con-tbody .vs-table--tbody-table .tr-values .vs-table--td {
        padding: 8px 15px;
    }
    .comp-portal-notification span.notification-icon {
      /*PAC_5-2802 S*/
      /*width: unset;*/
      width: 78px;
      height: 29px;
      line-height: 19px;
      padding: 5px 1px;
      font-size: 0.86rem;
      /*vertical-align: middle;*/
      /*PAC_5-2802 E*/
      text-align: center;
      border-radius: 5px;
      display: inline-block;
    }
</style>
