<template>
    <div class="user-work-list">
        <div class="user-work-list-main">
            <vx-card class="mb-4 block-search">
                <div class="search-content">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workMonth" class="vs-input--label">勤務月</label>
                                <div class="vs-con-input">
                                    <datepicker 
                                        :clear-button="clearButton"  
                                        :format="DatePickerFormat" 
                                        :use-utc="true" 
                                        :language="ja" 
                                        minimum-view="month" 
                                        v-model="filter.workMonth" 
                                        id="DateTimeAttendance">
                                    </datepicker>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2"> 
                            <vs-input class="inputx w-full" label="利用者名" v-model="filter.userName"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="4" vs-sm="3" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_approvalStatus" class="vs-input--label">承認状態</label>
                                <div class="vs-con-input">
                                    <v-select
                                        v-model="filter.approvalStatus"
                                        :options="approvalStatusOptions"
                                        :reduce="options => options.code"
                                        label="label"
                                        :searchable ="false"
                                        return-object>
                                    </v-select> 
                                </div>
                            </div>
                        </vs-col>
                    </vs-row>
                    <vs-row> 
                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workingHour" class="vs-input--label">勤務時間</label>
                                <div class="vs-con-input flexCtrl"> 
                                    <vs-input 
                                        class="dayHourCtrl" 
                                        type="number" 
                                        :min="0" 
                                        :max="999" 
                                        :step="0.01"
                                        maxlength="3" 
                                        v-model="filter.workingHour" 
                                        @input="filter.workingHour=Number.parseFloat(filter.workingHour)"/>
                                    <vs-radio class="dayHourRdo" vs-value="1" vs-name="workingHourType" v-model="filter.workingHourType">以上</vs-radio>
                                    <vs-radio class="dayHourRdo" vs-value="2" vs-name="workingHourType" v-model="filter.workingHourType">未満</vs-radio>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="12" class="mb-3 lg:pl-2">
                            <div class="w-full">
                                <label for="filter_workDay" class="vs-input--label">出勤日数</label>
                                <div class="vs-con-input flexCtrl">
                                    <vs-input 
                                        class="dayHourCtrl" 
                                        type="number" 
                                        :min="0" 
                                        :max="31"
                                        maxlength="2" 
                                        v-model="filter.workDay" 
                                        @input="filter.workDay=Number.parseFloat(filter.workDay).toFixed(0)"/>
                                    <vs-radio class="dayHourRdo" vs-value="1" vs-name="workDayType" v-model="filter.workDayType">以上</vs-radio>
                                    <vs-radio class="dayHourRdo" vs-value="2" vs-name="workDayType" v-model="filter.workDayType">未満</vs-radio>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="12" vs-align="flex-end" vs-justify="flex-end"  class="mb-3 lg:pl-2">
                            <vs-button class="square btn-search" color="primary" v-on:click="onSearchClick"><i class="fas fa-search"></i>検索</vs-button>
                        </vs-col>
                    </vs-row> 
                </div>
            </vx-card>
            <vs-card class="mb-4 block-result">
                <vs-row>
                    <vs-col vs-type="flex" vs-align="flex-start" vs-justify="flex-start" vs-w="8">
                        <vs-button class="square"  color="success" v-on:click="onApproval()"
                            v-bind:disabled="selected.length == 0"  > 一括承認</vs-button> 
                        <vs-button class="square" color="success" @click="onSelectExport"
                            v-bind:disabled="selected.length == 0"  > CSV出力</vs-button>
                        <vs-button class="square" color="success" @click="onSubmissionState()"
                            v-bind:disabled="selected.length == 0"  > 差戻し</vs-button>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="4">
                        <vs-button class="square"  color="primary" v-on:click="onDetail()"
                            v-bind:disabled="selected.length == 0"  > 詳細表示</vs-button>
                    </vs-col>
                </vs-row>
                <div class="search-result">
                    <div class="search-result-block">
                        <vs-table class="mt-3" noDataText="データがありません。" :data="listData" @sort="handleSort" stripe sst>
                            <template slot="thead">
                                <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                                <vs-th sort-key="working_month" class="min-width-100">勤務月 </vs-th>
                                <vs-th sort-key="submission_state">提出状態</vs-th>
                                <vs-th sort-key="user_name">氏名</vs-th>
                                <vs-th sort-key="working_time">勤務時間</vs-th>
                                <vs-th sort-key="working_day_count">出勤日数</vs-th>
                                <vs-th sort-key="approval_date">承認日</vs-th>
                            </template>

                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <vs-td><vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/></vs-td>
                                    <td> {{tr.working_month.substring(0, 4)}}/{{tr.working_month.substring(4, 6)}} </td>
                                    <td> {{submitStatus[tr.submission_state]}} </td>
                                    <td> {{tr.user_name}}</td>
                                    <td> {{tr.working_time}}</td>
                                    <td> {{tr.working_day_count}}</td>
                                    <td> {{tr.approval_date}}</td>
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

            <vs-popup name="export-work-list" :active.sync="showExportPopup" title="勤務情報CSV出力">
                <vs-row class="">
                    <vs-col vs-w="12" vs-type="block">
                        <span class="title-bolder"><p>出力する項目を選択してください。</p></span>
                    </vs-col>
                </vs-row>

                <div class="m-3 border-cell p-5 popup-csv-content-height">
                <form>
                    <vs-row>
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">ユーザ名</span>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">メールアドレス</span>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">業務日(yyyymmdd)</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.workDate" @click="workListSelectExport.workDate = !workListSelectExport.workDate"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">出勤時間(yyyymmdd hh:nn)</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.workStartTime" @click="workListSelectExport.workStartTime = !workListSelectExport.workStartTime"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">退勤時間(yyyymmdd hh:nn)</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.workEndTime" @click="workListSelectExport.workEndTime = !workListSelectExport.workEndTime"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">休憩時間(nn)</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.breakTime" @click="workListSelectExport.breakTime = !workListSelectExport.breakTime"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">稼働時間</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.workingTime" @click="workListSelectExport.workingTime = !workListSelectExport.workingTime"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">残業時間</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.overTime" @click="workListSelectExport.overTime = !workListSelectExport.overTime"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">遅刻フラグ</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.lateFlg" @click="workListSelectExport.lateFlg = !workListSelectExport.lateFlg"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">早退フラグ</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.earlyLeave" @click="workListSelectExport.earlyLeave = !workListSelectExport.earlyLeave"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">有給フラグ</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.paidVacation" @click="workListSelectExport.paidVacation = !workListSelectExport.paidVacation"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">特休フラグ</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.spVacation" @click="workListSelectExport.spVacation = !workListSelectExport.spVacation"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">代休フラグ</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.dayOff" @click="workListSelectExport.dayOff = !workListSelectExport.dayOff"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">備考</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.memo" @click="workListSelectExport.memo = !workListSelectExport.memo"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                    <vs-col vs-w="6" class="text-left pt-3">
                        <span class="vs-input--label">管理者コメント</span>
                    </vs-col>
                    <vs-col vs-w="2" class="pt-3">
                        <vs-checkbox :value="workListSelectExport.adminMemo" @click="workListSelectExport.adminMemo = !workListSelectExport.adminMemo"></vs-checkbox>
                    </vs-col>
                    </vs-row>
                </form>
                </div>
                <vs-row class="pt-6 pr-3" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="exportHrUserWorkListToCSV" >CSV出力</vs-button>
                    <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelExport">閉じる</vs-button>
                </vs-row>
            </vs-popup>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import Axios from "axios";
import config from "../../../app.config";
import VxCard from '../../../components/vx-card/VxCard.vue';
import Datepicker from 'vuejs-datepicker';
import VueCtkDateTimePicker from '../../../components/VueCtkDateTimePicker';
import Encoding from 'encoding-japanese';
import {ja} from 'vuejs-datepicker/dist/locale';
import router from '../../../router';

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
                workMonth: this.$moment(new Date()).format('YYYY-MM'),
                userName: "",
                approvalStatus: "",
                workingHour: "",
                workingHourType: "2",
                workDay: "",
                workDayType: "2"
            },
            defaultTime: '',
            orderBy: "working_month",
            orderDir: "desc",
            DatePickerFormat: 'yyyy/MM',
            clearButton: true,
            ja: ja,
            submitStatus: ['未提出', '提出済'],
            approvalStatusOptions: [{code: 0, label: '未承認'},{code: 1, label: '承認済'},{code: 2, label: '修正依頼'}],
            listData: [],
            pagination: { totalPage:0, currentPage: 1, limit: 12, totalItem: 0, from: 1, to: 12 },
            selectAll: false,
            showExportPopup: false,
            workMonthExport: '',
            workListSelectExport: {
                name: true,
                email: true,
                workDate: true,
                workStartTime: true,
                workEndTime: true,
                breakTime: true,
                workingTime: true,
                overTime: true,
                lateFlg: true,
                earlyLeave: true,
                paidVacation: true,
                spVacation: true,
                dayOff: true,
                memo: true,
                adminMemo: true
            },
        };
    },
    methods: {
        ...mapActions({
            getListUserWorkList:     "hr/getHrUserWorkList",
            updateApprovalState:     "hr/updateHrUserWorkList",
            exportUserWorkListToCSV: "hr/exportUserWorkListToCSV",
            updateSubmissionState:   "hr/updateSubmissionState", 
            addLogOperation:         "logOperation/addLog"
        }),
        async onSearchClick(){
            await this.addLogOperation({action: 'hr-user-work-list-search', result: 0});
            this.onSearch(true, true);
        },
        onSearch: async function (resetPaging, watchSet) {
            //this.addLogOperation({action: 'hr-user-work-status-list-screen', result: 0});
            this.selectAll = false;
            this.$store.dispatch('updateLoading', true);
            var workingMonth = this.filter.workMonth ? this.getMonthYearCalendar(this.filter.workMonth) : this.filter.workMonth;
            let isSearchAction = false;
            if (resetPaging) {
                isSearchAction = true;
            }
            let params = {
                limit              : this.pagination.limit,
                page               : resetPaging ? 1: this.pagination.currentPage,
                working_month      : workingMonth,
                user_name          : this.filter.userName,
                approval_state     : this.filter.approvalStatus,
                working_hour       : this.filter.workingHour,
                working_hour_type  : this.filter.workingHourType,
                workday            : this.filter.workDay,
                workday_type       : this.filter.workDayType,
                orderBy            : this.orderBy,
                orderDir           : this.orderDir,
                searchAction       : isSearchAction,
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
        getMonthYearCalendar(input) {
            var monthYear = this.$moment(input).format('YYYYMM');
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
        },
        onRowCheckboxClick(tr) {
            tr.selected = !tr.selected
            this.selectAll = this.listData.every(item => item.selected);
        },
        onSelectAll() {
            this.selectAll = !this.selectAll;
			this.listData.map(item=> {item.selected = this.selectAll; return item});
        },
        onSelectExport: async function() {
            //this.addLogOperation({action: 'user-work-detail-open-export-csv', result: 0});
            this.showExportPopup = true;
        },
        exportHrUserWorkListToCSV: async function() {
            let optionExport = ['name', 'email'];
            if (this.workListSelectExport.workDate) {
                optionExport.push('work_date');
            }
            if (this.workListSelectExport.workStartTime) {
                optionExport.push('work_start_time');
            }
            if (this.workListSelectExport.workEndTime) {
                optionExport.push('work_end_time');
            }
            if (this.workListSelectExport.breakTime) {
                optionExport.push('break_time');
            }
            if (this.workListSelectExport.workingTime) {
                optionExport.push('working_time');
            }
            if (this.workListSelectExport.overTime) {
                optionExport.push('overtime');
            }
            if (this.workListSelectExport.absent) {
                optionExport.push('absent_flg');
            }
            if (this.workListSelectExport.lateFlg) {
                optionExport.push('late_flg');
            }
            if (this.workListSelectExport.earlyLeave) {
                optionExport.push('earlyleave_flg');
            }
            if (this.workListSelectExport.paidVacation) {
                optionExport.push('paid_vacation_flg');
            }
            if (this.workListSelectExport.spVacation) {
                optionExport.push('sp_vacation_flg');
            }
            if (this.workListSelectExport.dayOff) {
                optionExport.push('day_off_flg');
            }
            if (this.workListSelectExport.memo) {
                optionExport.push('memo');
            }
            if (this.workListSelectExport.adminMemo) {
                optionExport.push('admin_memo');
            }
            let params = {
                export_work_list_columns : optionExport,
                work_month : this.listData.filter(item => item.selected)[0].working_month,
                mst_user_id: this.listData.filter(item => item.selected)[0].mst_user_id,
            };
            let workListExport =  await this.exportUserWorkListToCSV(params);
            let fileName = workListExport.file_name;
            let data = workListExport.time_card;
            let csvContent = '';
            data.forEach(function(infoArray, index) {
                csvContent += infoArray + '\r\n';
            });
            this.downloadFileExport(csvContent, fileName, 'text/csv');
        },
        cancelExport: async function() {
            this.showExportPopup = false;
            this.workListSelectExport.workDate = true;
            this.workListSelectExport.workStartTime = true;
            this.workListSelectExport.workEndTime = true;
            this.workListSelectExport.breakTime = true;
            this.workListSelectExport.workingTime = true;
            this.workListSelectExport.overTime = true;
            this.workListSelectExport.lateFlg = true;
            this.workListSelectExport.earlyLeave = true;
            this.workListSelectExport.paidVacation = true;
            this.workListSelectExport.spVacation = true;
            this.workListSelectExport.dayOff = true;
            this.workListSelectExport.memo = true;
            this.workListSelectExport.adminMemo = true;
        },
        downloadFileExport (data, fileName, mineType) {
            mineType = mineType || "application/octet-stream";
            fileName = fileName + '.csv';
            // Convert data to SJIS
            const str_array = Encoding.stringToCode(data);
            const sjis_array = Encoding.convert(str_array, "SJIS", "UNICODE");
            data = new Uint8Array(sjis_array);

            let hiddenElement = document.createElement('a');
            if (hiddenElement.download !== undefined) {
                let blob = new Blob([data], { type: (mineType + ';charset=Shift_JIS;') });
                let url = URL.createObjectURL(blob);
                hiddenElement.href = url;
                hiddenElement.download =  fileName;
            }
            if (navigator.msSaveBlob) { // IE 10+
                hiddenElement.addEventListener("click", function (event) {
                    let blob = new Blob([data], {
                        "type": mineType + ";charset=Shift_JIS;"
                    });
                    navigator.msSaveBlob(blob, fileName);
                }, false);
            }
            hiddenElement.click(); 
        },
        async onApproval() {
            await this.addLogOperation({action: 'hr-user-work-list-bulk-approval', result: 0}); 
            this.approval();
        },
        async approval() {
            this.$vs.dialog({
                type:'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: `選択されたユーザーの勤務月を承認します`,
                accept: async ()=> {
                    let params = {
                        cids : this.getSelectedID(),
                    };
                    await this.updateApprovalState(params)
                        .then(response => {
                            if (response.status == false) {
                                this.$vs.notify({
                                    color:'danger',
                                    text: response.message,
                                    position: 'bottom-left'
                                });
                            }
                            else {
                                this.$vs.notify({
                                    color:'success',
                                    text: response.message,
                                    position: 'bottom-left'
                                });
                            }
                            this.onSearch(false, true);
                        })
                        .catch(error => {
                            error = error.response;
                            const message = (error && error.data && error.message) || error.statusText;
                            return Promise.reject(message);
                        });
                },
            });
        },
        onSubmissionState() {
            this.submissionState();
        },
        async submissionState(){
            this.$vs.dialog({
                type:'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: `選択されたユーザーの勤務月を差戻します`,
                accept: async ()=> {
                    let params = {
                        cids : this.getSelectedID(),
                    }; 
                    await this.updateSubmissionState(params)
                        .then(response => {
                            if (response.status == false) {
                                this.$vs.notify({
                                    color:'danger',
                                    text: response.message,
                                    position: 'bottom-left'
                                });
                            }
                            else {
                                this.$vs.notify({
                                    color:'success',
                                    text: response.message,
                                    position: 'bottom-left'
                                });
                            }
                            this.onSearch(false, true);
                        })
                        .catch(error => {
                            error = error.response;
                            const message = (error && error.data && error.message) || error.statusText;
                            return Promise.reject(message);
                        });
                },
            });
        },
        getSelectedID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
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
            await this.addLogOperation({action: 'hr-user-work-list-detail', result: 0});
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
    },
    async created() {
        await this.addLogOperation({action: 'hr-user-work-list-access-screen', result: 0});
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
.dayHourCtrl { 
    padding-right: 40px;
    width :40% !important;
}
.flexCtrl {
    display: flex;
}
.dayHourRdo { 
    padding-right: 40px;
} 
.border-cell {
  border: 1px solid rgba(31, 116, 255, 1);
}
.popup-csv-content-height {
    height: 35em;
    overflow-y: scroll;
}
</style>