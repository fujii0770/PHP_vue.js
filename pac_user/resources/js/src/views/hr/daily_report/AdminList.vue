<template>
    <div class="report-list">
        <div class="report-list-main">
            <vx-card class="mb-4 block-search">
                <div class="search-content">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_reportMonth" class="vs-input--label">報告月</label>
                                <div class="vs-con-input inline-calenda">
                                    <datepicker :clear-button="clearButton"  :format="DatePickerFormat" :use-utc="true" :language="ja" minimum-view="month" v-model="filter.reportMonth" id="filter_reportMonth"></datepicker>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="報告者" v-model="filter.userName"/>
                        </vs-col>
                    </vs-row>
                    <div class="search-btn-block">
                        <vs-row class="mt-3">
                            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                                <vs-button class="square btn-search" color="primary" v-on:click="onSearch(true, true)"><i class="fas fa-search"></i>検索</vs-button>
                            </vs-col>
                        </vs-row>
                    </div>
                </div>
            </vx-card>
            <vs-card class="mb-4 block-result">
                <vs-row>
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                        <vs-button class="square"  color="primary" v-on:click="gotoNewDailyReport()"> 登録画面へ</vs-button>
                    </vs-col>
                </vs-row>
                <div class="search-result">
                    <div class="search-result-block">
                        <vs-table class="mt-3" noDataText="データがありません。" :data="listData" @sort="handleSort" stripe sst>
                            <template slot="thead">
                                <vs-th sort-key="report_date" class="min-width-100">勤務日 </vs-th>
                                <vs-th sort-key="user_name">氏名</vs-th>
                                <vs-th sort-key="daily_report">報告内容 </vs-th>
                            </template>

                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <td v-on:dblclick="reportDetail(tr)"> {{ $moment(tr.report_date).format('MM/DD')}} </td>
                                    <td v-on:dblclick="reportDetail(tr)"> {{tr.user_name}} </td>
                                    <td v-on:dblclick="reportDetail(tr)"> {{ tr.daily_report.substring(0, 70) }} </td>
                                </vs-tr>
                            </template>
                        </vs-table>
                    </div>

                    <div class="padding-content pagination-block">
                        <div>
                            <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from ? pagination.from : 0 }} 件から {{ pagination.to ? pagination.to : 0 }} 件までを表示</div>
                        </div>
                        <div>
                            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                        </div>
                    </div>
                </div>
            </vs-card>
        </div>
    </div>
</template>

<script>

import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import router from '../../../router';
import VxCard from '../../../components/vx-card/VxCard.vue';
import Datepicker from 'vuejs-datepicker';
import {ja} from 'vuejs-datepicker/dist/locale';

export default {
    components: {
        VxPagination,
        VxCard,
        Datepicker,
    },
    name: "DailyReportList",
    props: [],
    data() {
        return {
            filter: {
              reportMonth: "",
              userName: '',
            },
            orderBy: "report_date",
            orderDir: "desc",
            DatePickerFormat: 'yyyy/MM',
            clearButton: true,
            listData: [],
            ja: ja,
            pagination: { totalPage:0, currentPage: 1, limit: 12, totalItem: 0, from: 1, to: 12 },
        };
    },
    methods: {
        ...mapActions({
            getDailyReportList:   "hr/getHrDailyReportList",
        }),
        onSearch: async function (resetPaging, watchSet) {
            this.selectAll = false;
            this.$store.dispatch('updateLoading', true);
            var reportMonth = this.filter.reportMonth ? this.getMonthYearCalendar(this.filter.reportMonth) : this.filter.reportMonth;
            let isSearchAction = false;
            if (resetPaging) {
                isSearchAction = true;
            }
            let params = {
                limit              : this.pagination.limit,
                page               : resetPaging ? 1: this.pagination.currentPage,
                report_month       : reportMonth,
                orderBy            : this.orderBy,
                orderDir           : this.orderDir,
                searchAction       : isSearchAction,
                user_name          : this.filter.userName,
            };
            const data                  = await this.getDailyReportList(params);
            this.listData               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            if (watchSet) {
                this.pagination.currentPage = data.current_page;
            }
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
            this.$store.dispatch('updateLoading', false);

        },
        getMonthYearCalendar(input) {
            var monthYear = this.$moment(input).format('YYYYMM');
            return monthYear;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active ? "DESC" : "ASC";
            this.onSearch(false, true);
        },
        reportDetail(tr) {
            router.push({ path: `/hr/daily_report_list/${tr.id}`});
        },
        gotoNewDailyReport() {
          router.push({ path: `/hr/daily_report` });
        },
    },
    mounted() {
        this.onSearch(false, true);
    },
    computed: {
        selected() {
            return this.listData.filter(item => item.selected);
        }
    },
    watch: {
        'pagination.currentPage': async function (val) {
            await this.onSearch(false, false);
            console.log('pagination.currentPage');
        },
    }
}
</script>
