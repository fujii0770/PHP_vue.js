<template>
    <div class="work-list">
        <div class="work-list-main">
            <vx-card class="mb-4 block-search">
                <div class="search-content">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workFrom" class="vs-input--label">勤務月From</label>
                                <div class="vs-con-input inline-calenda">
                                    <datepicker :clear-button="clearButton"  :format="DatePickerFormat" :use-utc="true" :language="ja" minimum-view="month" v-model="filter.workFrom" id="filter_workFrom"></datepicker>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workTo" class="vs-input--label">勤務月To</label>
                                <div class="vs-con-input">
                                    <datepicker :clear-button="clearButton"  :format="DatePickerFormat" :language="ja" minimum-view="month" v-model="filter.workTo" id="filter_workTo"></datepicker>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-select class="selectSubmitStatus w-full" v-model="filter.submitStatus" label="提出状態">
                                <vs-select-item value="" text="---"/>
                                <vs-select-item value="0" :text="listSubmitStatus[0]" />
                                <vs-select-item value="1" :text="listSubmitStatus[1]" />
                            </vs-select>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-select class="selectApprovalStatus w-full" v-model="filter.approvalStatus" label="承認状態">
                                <vs-select-item value="" text="---"/>
                                <vs-select-item value="0" :text="listApprovalStatus[0]" />
                                <vs-select-item value="1" :text="listApprovalStatus[1]" />
                                <vs-select-item value="2" :text="listApprovalStatus[2]" />
                            </vs-select>
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
                <div class="search-result">
                    <div class="search-result-block">
                        <vs-table class="mt-3" :data="listData" noDataText="データがありません。"
                                 sst @sort="handleSort" stripe v-on:dblclick="workListDetail">
                            <template slot="thead">
                                <vs-th sort-key="working_month" class="width-150">勤務月 </vs-th>
                                <vs-th class="width-150">提出状態</vs-th>
                                <vs-th class="width-200">提出日</vs-th>
                                <vs-th sort-key="approval_state" class="width-150">承認状態 </vs-th>
                                <vs-th>承認者</vs-th>
                                <vs-th class="width-200">承認日</vs-th>
                            </template>

                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <td v-on:dblclick="workListDetail(tr)"> {{ tr.working_month.substring(0, 4)}}/{{tr.working_month.substring(4, 6)}} </td>
                                    <td v-on:dblclick="workListDetail(tr)"> {{listSubmitStatus[tr.submission_state]}} </td>
                                    <td v-on:dblclick="workListDetail(tr)">{{tr.submission_date ? tr.submission_date.substring(0, 4) + '/' + tr.submission_date.substring(5, 7) + '/' + tr.submission_date.substring(8, 10) : '' }}</td>
                                    <td v-on:dblclick="workListDetail(tr)"> {{ listApprovalStatus[tr.approval_state] }} </td>
                                    <td v-on:dblclick="workListDetail(tr)"> {{tr.approval_user}} </td>
                                    <td v-on:dblclick="workListDetail(tr)"> {{tr.approval_date ? tr.approval_date.substring(0, 4) + '/' + tr.approval_date.substring(5, 7) + '/' + tr.approval_date.substring(8, 10) : '' }}</td>
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
    name: "WorkList",
    props: [],
    data() {
        return {
            filter: {
               workFrom: "",
               workTo: "",
               submitStatus: '',
               approvalStatus: '',
            },
            orderBy: "working_month",
            orderDir: "desc",
            DatePickerFormat: 'yyyy/MM',
            clearButton: true,
            ja: ja,
            listSubmitStatus: ['未提出', '提出済'],
            listApprovalStatus: ['未承認', '承認済', '修正依頼'],
            listData: {},
            pagination: { totalPage:0, currentPage: 1, limit: 12, totalItem: 0, from: 1, to: 12 },
        };
    },
    methods: {
        ...mapActions({
            getListWorkList: "workList/getHrWorkList",
        }),
        onSearch: async function (resetPaging, watchSet) {
            this.$store.dispatch('updateLoading', true);
            var workingMonthFrom = this.filter.workFrom ? this.getMonthYearCalendar(this.filter.workFrom) : this.filter.workFrom;
            var workingMonthTo = this.filter.workTo ? this.getMonthYearCalendar(this.filter.workTo): this.filter.workTo;
            let isSearchAction = false;
            if (resetPaging) {
                isSearchAction = true;
            }
            let params = {
                limit              : this.pagination.limit,
                page               : resetPaging ? 1: this.pagination.currentPage,
                working_month_from : workingMonthFrom ,
                working_month_to   : workingMonthTo,
                submission_state   : this.filter.submitStatus,
                approval_state     : this.filter.approvalStatus,
                orderBy            : this.orderBy,
                orderDir           : this.orderDir,
                searchAction       : isSearchAction,
            };
            const data                  = await this.getListWorkList(params);
            this.listData               = data.data;
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
            var data = String(input);
            data = data.split(' ');
            var month = new Date(data[1] + '-1-01').getMonth() + 1;
            if (month < 10) {
                month = '0' + month;
            }
            var monthYear = data[3] + month;
            return monthYear;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active ? "DESC" : "ASC";
            this.onSearch(false, true);
        },
        workListDetail(tr) {
            var param = tr.working_month;
            router.push({ path: `/hr/work-detail/${param}`});
        }

    },
    mounted() {
        this.onSearch(false, true);
    },
    computed: {},
    watch: {
        'pagination.currentPage': async function (val) {
            await this.onSearch(false, false);
            console.log('pagination.currentPage');
        },
    }
}
</script>
