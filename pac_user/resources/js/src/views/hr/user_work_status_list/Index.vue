<template>
    <div class="user-work-list">
        <div class="user-work-list-main">
            <vx-card class="mb-4 block-search">
                <div class="search-content">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workTo" class="vs-input--label">勤務日From</label>
                                <div class="vs-con-input">
                                    <VueCtkDateTimePicker
                                    v-model="filter.workFrom"
                                    :format="'YYYY/MM/DD'"
                                    :formatted="'YYYY/MM/DD'" 
                                    :outputFormat="'YYYY/MM/DD'" 
                                    name="workFrom"
                                    id="DateTimeAttendance" 
                                    :no-header="true"
                                    locale="ja" 
                                    :label="''"
                                    :noClearButton="false" 
                                    :button-now-translation="buttonNowTranslation" 
                                    :default-time="defaultTime"
                                    only-date></VueCtkDateTimePicker>
                                </div>
                            </div>
                        </vs-col>
         
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workTo" class="vs-input--label">勤務日To</label>
                                <div class="vs-con-input">
                                    <VueCtkDateTimePicker
                                    v-model="filter.workTo"
                                    :format="'YYYY/MM/DD'"
                                    :formatted="'YYYY/MM/DD'" 
                                    :outputFormat="'YYYY/MM/DD'"
                                    name="workTo"
                                    id="DateTimeAttendance" 
                                    :no-header="true" 
                                    locale="ja" 
                                    :label="''" 
                                    :noClearButton="false" 
                                    :button-now-translation="buttonNowTranslation" 
                                    :default-time="defaultTime"
                                    only-date></VueCtkDateTimePicker>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <vs-input class="inputx w-full" label="利用者名" v-model="filter.userName"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workTo" class="vs-input--label">出勤時刻</label>
                                <div class="vs-con-input">
                                    <v-select
                                        v-model="filter.workStartTimeFlg"
                                        :options="yesNoFlags"
                                        :reduce="options => options.code"
                                        label="label"
                                        :searchable ="false"
                                        return-object>
                                    </v-select>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workTo" class="vs-input--label">退勤時刻</label>
                                <div class="vs-con-input">
                                    <v-select
                                        v-model="filter.workEndTimeFlg"
                                        :options="yesNoFlags"
                                        :reduce="options => options.code"
                                        label="label"
                                        :searchable ="false"
                                        return-object>
                                    </v-select>
                                </div>
                            </div>
                        </vs-col>
                    </vs-row>
                    <div class="search-btn-block">
                        <vs-row class="mt-3">
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="12" class="mb-3 lg:pl-2">
                                <div class="w-full">
                                    <label for="filter_workTo" class="vs-input--label">遅刻</label>
                                    <div class="vs-con-input">
                                        <v-select
                                            v-model="filter.lateFlg"
                                            :options="lateFlagOptions"
                                            :reduce="options => options.code"
                                            label="label"
                                            :searchable ="false"
                                            return-object></v-select>
                                    </div>
                                </div>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="12" class="mb-3 lg:pl-2">
                                <div class="w-full">
                                    <label for="filter_workTo" class="vs-input--label">早退</label>
                                    <div class="vs-con-input">
                                        <v-select
                                            v-model="filter.earlyLeaveFlg"
                                            :options="earlyLeaveFlagOptions"
                                            :reduce="options => options.code"
                                            label="label"
                                            :searchable ="false"
                                            return-object></v-select> 
                                    </div>
                                </div>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="12" class="mb-3 lg:pl-2">
                                <div class="w-full">
                                    <label for="filter_workTo" class="vs-input--label">有給</label>
                                    <div class="vs-con-input">
                                        <v-select
                                            v-model="filter.paidVacationFlg"
                                            :options="paidFlagOptions"
                                            :reduce="options => options.code"
                                            label="label"
                                            :searchable ="false"
                                            return-object></v-select>   
                                    </div>
                                </div>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="12" class="mb-3 lg:pl-2">
                                <div class="w-full">
                                    <label for="filter_workTo" class="vs-input--label">特休</label>
                                    <div class="vs-con-input">
                                        <v-select
                                            v-model="filter.spVacationFlg"
                                            :options="spFlagOptions"
                                            :reduce="options => options.code"
                                            label="label"
                                            :searchable ="false"
                                            return-object></v-select>  
                                    </div>
                                </div>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="12" class="mb-3 lg:pl-2">
                                <div class="w-full">
                                    <label for="filter_workTo" class="vs-input--label">代休</label>
                                    <div class="vs-con-input">
                                        <v-select
                                            v-model="filter.dayOffFlg"
                                            :options="dayFlagOptions"
                                            :reduce="options => options.code"
                                            label="label"
                                            :searchable ="false"
                                            return-object></v-select>
                                    </div>
                                </div>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="12" class="mb-3 pl-2">
                                <div class="w-full">
                                    <label for="filter_workTo" class="vs-input--label"></label>
                                    <div class="vs-con-input col-btn-search">
                                        <vs-button class="square btn-search" color="primary" v-on:click="onSearch(true, true)"><i class="fas fa-search"></i>検索</vs-button>
                                    </div>
                                </div>
                            </vs-col>
                        </vs-row> 
                    </div>
                </div>
            </vx-card>
            <vs-card class="mb-4 block-result">
                <vs-row>
                    <vs-col vs-type="flex" vs-align="flex-start" vs-justify="flex-start" vs-w="8">
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="4">
                        <vs-button class="square"  color="primary" v-on:click="onDetail()" v-bind:disabled="selected.length == 0"  > 詳細表示</vs-button>
                    </vs-col>
                </vs-row>
                <div class="search-result">
                    <div class="search-result-block">
                        <vs-table class="mt-3" noDataText="データがありません。" :data="listData" @sort="handleSort" stripe sst>
                            <template slot="thead">
                                <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                                <vs-th sort-key="work_date" class="min-width-100">勤務日</vs-th>
                                <vs-th sort-key="user_name">氏名</vs-th>
                                <vs-th sort-key="work_start_time_flg">出勤時刻</vs-th>
                                <vs-th sort-key="work_end_time_flg">退勤時刻</vs-th>
                                <vs-th sort-key="late_flg">遅刻</vs-th>
                                <vs-th sort-key="earlyleave_flg">早退</vs-th>
                                <vs-th sort-key="paid_vacation_flg">有給</vs-th>
                                <vs-th sort-key="sp_vacation_flg">特休</vs-th>
                                <vs-th sort-key="day_off_flg">代休</vs-th>
                            </template>
                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <vs-td><vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/></vs-td>
                                    <td> {{getWorkDate(tr.work_date)}}</td>
                                    <td> {{tr.user_name}}</td>
                                    <td> {{getWorkStartTime(getWorkDate(tr.work_date), tr.work_start_time)}}</td>
                                    <td> {{getWorkEndTime(getWorkDate(tr.work_date), tr.work_end_time)}}</td>
                                    <td> {{getLateFlagName(tr.late_flg,lateFlags)}} </td>
                                    <td> {{getEarlyLeaveFlagName(tr.earlyleave_flg,earlyLeaveFlags)}} </td>
                                    <td> {{getPaidVacatioFlagName(tr.paid_vacation_flg,paidFlags)}} </td>
                                    <td> {{getSpVacationFlagName(tr.sp_vacation_flg,spFlags)}} </td>
                                    <td> {{getDayOffFlagName(tr.day_off_flg,dayFlags)}} </td>
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
import VueCtkDateTimePicker from '../../../components/VueCtkDateTimePicker';

import {ja} from 'vuejs-datepicker/dist/locale';

export default {
    components: {
        VxPagination,
        VxCard,
        Datepicker,
        VueCtkDateTimePicker,
    },
    name: "WorkList",
    props: {
      buttonNowTranslation: { type: String, default: null }
    },
    data() {
        return {
            filter: {
                workFrom: this.$moment(new Date()).format('YYYY/MM/DD'),
                workTo: this.$moment(new Date()).format('YYYY/MM/DD'),
                workStartTimeFlg: '',
                workEndTimeFlg: '',
                lateFlg: '',
                earlyLeaveFlg: '',
                paidVacationFlg: '',
                spVacationFlg: '',
                dayOffFlg: '',
            },
            orderBy: "work_date",
            orderDir: "desc",
            ja: ja,
            listData: [],
            yesNoFlags:            [{code: 0, label: ''},{code: 1, label: '入力無し'},{code: 2, label: '入力有り'}],
            lateFlags:             [{code: 0, label: ''},{code: 1, label: '遅刻'}],
            earlyLeaveFlags:       [{code: 0, label: ''},{code: 1, label: '早退'}],
            paidFlags:             [{code: 0, label: ''},{code: 1, label: '有給'},{code: 2, label: '半休'}],
            spFlags:               [{code: 0, label: ''},{code: 1, label: '特休'},{code: 2, label: '半休'}],
            dayFlags:              [{code: 0, label: ''},{code: 1, label: '代休'},{code: 2, label: '半休'}],
            lateFlagOptions:       [{code: 1, label: '遅刻'}],
            earlyLeaveFlagOptions: [{code: 1, label: '早退'}],
            paidFlagOptions:       [{code: 1, label: '有給'},{code: 2, label: '半休'}],
            spFlagOptions:         [{code: 1, label: '特休'},{code: 2, label: '半休'}],
            dayFlagOptions:        [{code: 1, label: '代休'},{code: 2, label: '半休'}],
            pagination: { totalPage:0, currentPage: 1, limit: 12, totalItem: 0, from: 1, to: 12 },
            selectAll: false,
            defaultTime: ''
        };
    },
    methods: {
        ...mapActions({
            getListUserWorkList:   "hr/getHrUserWorkStatusList",
            addLogOperation: "logOperation/addLog",
        }),
        onSearch: async function (resetPaging, watchSet) {
            this.addLogOperation({action: 'hr-user-work-status-list-screen', result: 0});
            this.selectAll = false;
            this.$store.dispatch('updateLoading', true);
            var workingDateFrom = this.filter.workFrom ? this.getDayMonthYearCalendar(this.filter.workFrom) : this.filter.workFrom;
            var workingMonthTo = this.filter.workTo ? this.getDayMonthYearCalendar(this.filter.workTo): this.filter.workTo;
            let isSearchAction = false;
            if (resetPaging) {
                isSearchAction = true;
            }
            let params = {
                limit               : this.pagination.limit,
                page                : resetPaging ? 1: this.pagination.currentPage,
                working_date_from   : workingDateFrom,
                working_date_to     : workingMonthTo,
                user_name           : this.filter.userName,
                work_start_time_flg : this.filter.workStartTimeFlg,
                work_end_time_flg   : this.filter.workEndTimeFlg,
                late_flg            : this.filter.lateFlg,
                earlyleave_flg      : this.filter.earlyLeaveFlg,
                paid_vacation_flg   : this.filter.paidVacationFlg,
                sp_vacation_flg     : this.filter.spVacationFlg,
                day_off_flg         : this.filter.dayOffFlg,
                orderBy             : this.orderBy,
                orderDir            : this.orderDir,
                searchAction        : isSearchAction,
            };
            const data                  = await this.getListUserWorkList(params);
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
        getDayMonthYearCalendar(input) {
            var monthYear = this.$moment(input).format('YYYYMMDD');
            return monthYear;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active ? "DESC" : "ASC";
            this.onSearch(false, true);
        },
        onRowCheckboxClick(tr) {
            tr.selected = !tr.selected
            this.selectAll = this.listData.every(item => item.selected);
        },
        onSelectAll() {
            this.selectAll = !this.selectAll;
			this.listData.map(item=> {item.selected = this.selectAll; return item});
        },
        getLateFlagName(late_flg, lateFlags) {
            if (late_flg == null || late_flg == '0'){
                return "";
            } else {
                return lateFlags[late_flg].label;
            }
        },
        getEarlyLeaveFlagName(earlyleave_flg, earlyLeaveFlags) {
            if (earlyleave_flg == null || earlyleave_flg == '0'){
                return "";
            } else {
                return earlyLeaveFlags[earlyleave_flg].label;
            }
        },
        getPaidVacatioFlagName(paid_vacation_flg, paidFlags) {
            if (paid_vacation_flg == null || paid_vacation_flg == '0'){
                return "";
            } else {
                return paidFlags[paid_vacation_flg].label;
            }
        },
        getSpVacationFlagName(sp_vacation_flg, spFlags) {
            if (sp_vacation_flg == null || sp_vacation_flg == '0'){
                return "";
            } else {
                return spFlags[sp_vacation_flg].label;
            }
        },
        getDayOffFlagName(day_off_flg, dayFlags) {
            if (day_off_flg == null || day_off_flg == '0'){
                return "";
            } else {
                return dayFlags[day_off_flg].label;
            }
        },
        getYesNoFlagName(yes_no_flg, yesNoFlags) {
            if (yes_no_flg == null || yes_no_flg == '0'){
                return "";
            } else {
                return yesNoFlags[yes_no_flg].label;
            }
        },
        getWorkDate(workDate){
            if (workDate != null) {
                return workDate.substring(0, 4) + '/' + workDate.substring(4, 6) + '/' + workDate.substring(6, 8);
            } else {
                return "";
            }
        },
        getWorkStartTime(workDate, workStartTime) {
            if (workStartTime != null && workDate != null) {
                var startTime  = workStartTime.slice(0,10);
                var wkDate     = this.$moment(workDate, 'YYYY年MM月DD日').format('YYYY-MM-DD');
                if(wkDate == startTime) {
                    return this.$moment(workStartTime).format('HH:mm');
                } else {
                    return this.$moment(workStartTime).format('HH:mm (MM/DD)');
                }
            } else {
                return "";
            }
        },
        getWorkEndTime(workDate, workEndTime) {
            if (workEndTime != null && workDate != null) {
                var endTime    = workEndTime.slice(0,10);
                var wkDate     = this.$moment(workDate, 'YYYY年MM月DD日').format('YYYY-MM-DD');
                if(wkDate == endTime) {
                    return this.$moment(workEndTime).format('HH:mm');
                } else { 
                    return this.$moment(workEndTime).format('HH:mm (MM/DD)');
                }
            } else {
                return "";
            }
        },
        onDetail() {
            this.showDetail();
        },
        removeFlexDatepicker(id){
            $('#'+id +' .datetimepicker').removeClass('flex');
            $('#'+id +' .datepicker-button').click(function(){
                $('#'+id +' .datetimepicker').removeClass('flex');
            })

            $('.month-container button.datepicker-day').attr('type', 'button');
        },
        async showDetail() {

            if (this.selected.length != 1) {
                this.$vs.dialog({
                    type:'alert',
                    color: 'primary',
                    title: `詳細表示`,
                    acceptText: 'はい',
                    cancelText: '',
                    text: `詳細ボタン押下時は、チェックボックスを一つ選択してください。`,
                    accept: () => { 
                        /* nothing */
                    }
                });
                return;
            }

            var item = this.selected[0];
            var id = item.mst_user_id;
            var month = item.working_month;
            router.push({ path: `/hr/user_work_detail/${id}/${month}` });
        },
    },
    mounted() {
        this.onSearch(false, true);
        this.removeFlexDatepicker('DateTimeAttendance-wrapper');
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
<style>

.datetimepicker.flex[style*='display: none;']{
  display: none !important;
}
.field-input{
    height: 38px !important;
    min-height: 38px !important;
}
.month-container button.datepicker-day:not(.enable){
  visibility: hidden;
}
#DateTimeAttendance-input {
    padding-bottom: 18px;
}
#filter_workTo {
    padding-bottom: 18px;
}
#filter_workFrom {
    padding-bottom: 18px;
}
.col-btn-search {
    text-align: left;
}
.col-btn-search > .btn-search {
    height: 39px;
}
/* v-select style start */
ul[id*="__listbox"]{
  min-width: 136px !important;
  top: calc(10% - 1px);
}
.vs__dropdown-option{
  padding: 3px 10px 3px 10px !important;
}
.vs__dropdown-option--highlight {
  background: #e6f2fc !important;
  color: #000000 !important;
  padding: 3px 10px 3px 10px !important;
}
.vs__dropdown-menu--highlight { 
  color: #000000 !important;
  padding: 3px 10px 3px 10px !important;
}
.vs__selected-options {
  height:31px;
}
.v-select .vs__dropdown-menu .vs__dropdown-option--highlight {
  color: #000000 !important;
}
</style>

