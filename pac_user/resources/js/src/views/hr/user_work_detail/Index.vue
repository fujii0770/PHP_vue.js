<template>
  <div id="work-info-page">
    <vs-card>
      <vs-row >
        <vs-col vs-type="flex" vs-justify="space-between">
          <div class="vs-con-input">
            <datepicker  :format="DatePickerFormat" :use-utc="true" :language="ja" minimum-view="month" v-model="workMonth" id="workMonth"></datepicker>
          </div>
          <div>
            <span class="user-name">{{ getUserName }}</span>
          </div>
          <div style="padding-right: 15px">
            <vs-button @click="onSelectExport"    class="square" color="success"> CSV出力</vs-button>
            <vs-button @click="onApproval"        class="square" color="success"> 一括承認</vs-button>
            <vs-button @click="onSubmissionState" class="square" color="success"> 差戻し</vs-button>
          </div>
        </vs-col>
      </vs-row>
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
                <span class="vs-input--label">欠勤フラグ</span>
              </vs-col>
              <vs-col vs-w="2" class="pt-3">
                  <vs-checkbox :value="workListSelectExport.absent" @click="workListSelectExport.absent = !workListSelectExport.absent"></vs-checkbox>
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
                <span class="vs-input--label">作業内容</span>
              </vs-col>
              <vs-col vs-w="2" class="pt-3">
                  <vs-checkbox :value="workListSelectExport.workDetail" @click="workListSelectExport.workDetail = !workListSelectExport.workDetail"></vs-checkbox>
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
            <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="exportHrWorkListToCSV" >CSV出力</vs-button>
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelExport">閉じる</vs-button>
        </vs-row>
      </vs-popup>

      <vs-table class="mt-3" noDataText="データがありません。" :data="listSave"  stripe sst>
        <template slot="thead">
          <vs-th class="width-100">日付</vs-th>
          <vs-th class="width-130">出勤</vs-th>
          <vs-th class="width-130">退勤</vs-th>
          <vs-th class="width-130">シフト</vs-th>
          <vs-th class="width-100">休憩時間</vs-th>
          <vs-th class="width-100">稼働時間</vs-th>
          <vs-th class="width-100">休暇等</vs-th>
          <vs-th class="width-100">承認状態</vs-th>
          <vs-th class="min-width-100">作業内容</vs-th>
          <vs-th class="min-width-100">備考</vs-th>
          <vs-th class="width-150 centerEdit">編集</vs-th>
        </template>

        <template slot-scope="{data}">
          <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
            <td >{{tr.work_date | moment("DD日（dd）")}}</td>
            <td v-if="tr.work_start_time != ''">
              <div class="flex flex-col">
                <span>{{tr.work_start_time | moment("HH:mm")}} </span><span v-if="!!tr.start_stamping" class="stamping-text">({{tr.start_stamping | moment("HH:mm")}})</span>
              </div>
            </td>
            <td v-else></td>
            <td v-if="tr.work_end_time != ''">
              <div class="flex flex-col">
                <span>{{tr.work_end_time}}</span><span v-if="!!tr.end_stamping" class="stamping-text">({{tr.end_stamping}})</span>
              </div>
            </td>
            <td v-else></td>
            <td>{{tr.shift_time_label}}</td>
            <td v-if="tr.break_time != ''">{{tr.break_time | moment("HH:mm")}}</td>
            <td v-else></td>
            <td v-if="tr.working_time != ''">{{tr.working_time | moment("HH:mm")}}</td>
            <td v-else></td>
            <td >{{tr.earlyleave_flg}}</td>
            <td >{{tr.approval_state}}</td>
            <td class="memo">{{tr.work_detail}}</td>
            <td class="memo">{{tr.memo}}</td>
            <td class="endEdit"  v-if="tr.buttonStatus == 0" @click="editWorkListDetail(tr)"> <vs-button class="square" color="primary">編集</vs-button></td>
            <td class="endEdit"  v-else-if="tr.buttonStatus == 1"> <vs-button class="square" color="primary" disabled="">編集</vs-button></td>
          </vs-tr>
        </template>
      </vs-table>

      <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
    </vs-card>
    <vs-popup class="popup-dialog" classContent="popup-example"  title="勤務編集" :active.sync="modalEditWorkListDetail">
      <form class="edit-worklist-detail">
        <div class="title-date">
            <vs-row>
                <vs-col>{{workingDaySelected | moment("YYYY年MM月DD日（dd）")}}</vs-col>
            </vs-row>
        </div>
        <div class="worklist-info">
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> シフト出勤区分 </vs-col>
            <vs-col vs-w="8">
              <v-select
                :options="shiftWorkKbnOptions"
                label="label"
                v-model="shiftWorkKbnDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disableShiftWorkKbn"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 休日出勤 </vs-col>
            <vs-col vs-w="8">
              <v-select
                :options="holidayWorkFlgOptions"
                label="label"
                v-model="holidayWorkFlgDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disableHolidayWorkFlg"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 出勤時間 </vs-col>
            <vs-col vs-w="3"><VueCtkDateTimePicker id="DateTimeAttendance" :button-now-translation="buttonNowTranslation" :no-header="true" :outputFormat="'YYYY-MM-DD HH:mm:ss'" :formatted="'MM/DD HH:mm'" :format="'MM/DD HH:mm'" :disabled="disableFieldDialog.disableWorkStartTime" locale="ja" :label="''" v-model="workStartTimeDialog" :noClearButton="true" :default-time="defaultTime"></VueCtkDateTimePicker></vs-col>
            <vs-col vs-w="1">
                <button v-on:click="clearWorkStartTime()" type="button" class="clear_button">削除</button>
            </vs-col>
            <vs-col vs-w="2" class="worklist-detail stamp-title"> 出勤打刻 </vs-col>
            <vs-col vs-w="2"><VueCtkDateTimePicker id="startStamping" :button-now-translation="buttonNowTranslation" :no-header="true" :outputFormat="'YYYY-MM-DD HH:mm:ss'" :formatted="'MM/DD HH:mm'" :format="'MM/DD HH:mm'" :disabled="true" locale="ja" :label="''" v-model="startStampingDialog" :noClearButton="true" :default-time="defaultTime"></VueCtkDateTimePicker></vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
                <span v-if="(titleDateDialog < currentDate) && (workStartTimeDialog === '' && workEndTimeDialog)" style="color:#ff0000;">
                  {{ workStartTimeIsRequired }}
                </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="(titleDateDialog === currentDate) && (workStartTimeDialog === '' && workEndTimeDialog)" style="color:#ff0000;">
                {{ workStartTimeIsRequired }}
              </span>
              </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 退勤時間 </vs-col>
            <vs-col vs-w="3"><VueCtkDateTimePicker id="DateTimeLeave" :button-now-translation="buttonNowTranslation" :no-header="true" :outputFormat="'YYYY-MM-DD HH:mm:ss'" :formatted="'MM/DD HH:mm'" :format="'MM/DD HH:mm'" locale="ja" :disabled="disableFieldDialog.disableWorkEndTime" :label="''"  v-model="workEndTimeDialog" :noClearButton="true" :default-time="defaultTime"></VueCtkDateTimePicker></vs-col>
            <vs-col vs-w="1">
                <button v-on:click="clearWorkEndTime()" type="button" class="clear_button">削除</button>
            </vs-col>
            <vs-col vs-w="2" class="worklist-detail stamp-title"> 退勤打刻 </vs-col>
            <vs-col vs-w="2"><VueCtkDateTimePicker id="endStamping" :button-now-translation="buttonNowTranslation" :no-header="true" :outputFormat="'YYYY-MM-DD HH:mm:ss'" :formatted="'MM/DD HH:mm'" :format="'MM/DD HH:mm'" :disabled="true" locale="ja" :label="''" v-model="endStampingDialog" :noClearButton="true" :default-time="defaultTime"></VueCtkDateTimePicker></vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="(titleDateDialog < currentDate) && (workStartTimeDialog && workEndTimeDialog) && (workStartTimeDialog > workEndTimeDialog)" style="color:#ff0000;">
                {{ workStartTimeSmallerOrEqualWorkEndTime }}
              </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
                <span v-if="(titleDateDialog === currentDate) && (workStartTimeDialog && workEndTimeDialog) && (workStartTimeDialog > workEndTimeDialog)" style="color:#ff0000;">
                  {{ workStartTimeSmallerOrEqualWorkEndTime }}
                </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="(titleDateDialog < currentDate) && (workStartTimeDialog && workEndTimeDialog === '')" style="color:#ff0000;">
                {{ workEndTimeIsRequired }}
              </span>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 合計休憩時間 </vs-col>
            <vs-col vs-w="8">
              <vs-input class="inputx" type="number" min="0" name="break_time" id="break_time" :max="999" :disabled="disableFieldDialog.disableBreakTime" v-model="breakTimeDialog" v-on:keydown.enter.prevent="triggerSaveBtnClickEvent()" />
            </vs-col>
            <vs-col class="mt-3" vs-w="1">（分）</vs-col>
          </vs-row>
          <vs-row>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="breakTimeDialog > 999 || breakTimeDialog < 0 " style="color:#ff0000;">
                  {{ breakTimeErrors.range }}
              </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="breakTimeDialog === '' && vacationFlagDialog == '' " style="color:#ff0000;">
                  {{ breakTimeErrors.range }}
              </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="isNaN(breakTimeDialog) == true" style="color:#ff0000;">
                  {{ breakTimeErrors.range }}
              </span>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 深夜の休憩時間 </vs-col>
            <vs-col vs-w="8">
              <vs-input class="inputx" type="number" min="0" name="midnight_break_time" id="midnight_break_time" :max="999" :disabled="disableFieldDialog.disableMidnightBreakTime || !hasMidnight" v-model="midnightBreakTimeDialog" v-on:keydown.enter.prevent="triggerSaveBtnClickEvent()" />
            </vs-col>
            <vs-col class="mt-3" vs-w="1">（分）</vs-col>
          </vs-row>
          <vs-row>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="midnightBreakTimeDialog > 999 || midnightBreakTimeDialog < 0 " style="color:#ff0000;">
                  {{ midnightBreakTimeErrors.range }}
              </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="midnightBreakTimeDialog === '' && vacationFlagDialog == '' " style="color:#ff0000;">
                  {{ midnightBreakTimeErrors.range }}
              </span>
            </vs-col>
            <vs-col vs-w="3"></vs-col>
            <vs-col vs-w="9">
              <span v-if="isNaN(midnightBreakTimeDialog) == true" style="color:#ff0000;">
                  {{ midnightBreakTimeErrors.range }}
              </span>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 欠勤 </vs-col>
            <vs-col vs-w="8">
              <v-select
                @input="updateAbsentFlg()"
                :options="absentFlagOptions"
                label="label"
                v-model="absentFlagDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disableAbsent"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 早退 </vs-col>
            <vs-col vs-w="8">
              <v-select
                :options="earlyLeaveFlagOptions"
                label="label"
                v-model="earlyLeaveFlagDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disableEarlyLeave"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 遅刻 </vs-col>
            <vs-col vs-w="8">
              <v-select
                :options="lateFlagOptions"
                label="label"
                v-model="lateFlagDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disableLateWork"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 休暇記録等 </vs-col>
            <vs-col vs-w="8">
              <v-select
                @input="updateVacationFlag()"
                :options="vacationFlagOptions"
                label="label"
                v-model="vacationFlagDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disableVacation"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 承認状態 </vs-col>
            <vs-col vs-w="8">
              <v-select
                :options="approvalStateOptions"
                label="label"
                v-model="approvalStateDialog"
                :reduce="options => options.code"
                :disabled="disableFieldDialog.disabledApprovalState"
                :clearable="false" 
                :searchable ="false"
                return-object></v-select>
            </vs-col>
          </vs-row>
           <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 作業内容 </vs-col>
            <vs-col vs-w="8"><vs-textarea vs-justify style="margin-bottom:0px" placeholder="コメント記入欄" rows="2" v-model="workDetailDialog"/></vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 備考 </vs-col>
            <vs-col vs-w="8"><vs-textarea vs-justify style="margin-bottom:0px" placeholder="コメント記入欄" rows="2" v-model="memoDialog"/></vs-col>
          </vs-row>
          <vs-row class="mb-3">
            <vs-col vs-w="3" class="worklist-detail"> 管理者コメント </vs-col>
            <vs-col vs-w="8"><vs-textarea :disabled="disableFieldDialog.disabledAdminMemo" style="margin-bottom:0px" placeholder="コメント記入欄" rows="2" v-model="adminMemoDialog"/></vs-col>
          </vs-row>
        </div>
      </form>
      <vs-row class="mt-3">
        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
          <vs-button @click="onSave(detailIdDialog)" :disabled="isProcessing === true || (breakTimeDialog === '' || isNaN(breakTimeDialog) === true || breakTimeDialog < 0 || breakTimeDialog > 999 || checkWorkEndTime) || (midnightBreakTimeDialog === '' || isNaN(midnightBreakTimeDialog) === true || midnightBreakTimeDialog < 0 || midnightBreakTimeDialog > 999 || checkWorkEndTime)" color="success" class="worklist-detail-update-button">更新</vs-button>
          <vs-button @click="modalEditWorkListDetail=false" color="dark" type="border">閉じる</vs-button>
        </vs-col>
      </vs-row>
    </vs-popup>
  </div>
</template>

<script>
  import { mapState, mapActions } from "vuex";
  import InfiniteLoading from 'vue-infinite-loading';
  import flatPickr from 'vue-flatpickr-component';
  import 'flatpickr/dist/flatpickr.min.css';
  import {Japanese} from 'flatpickr/dist/l10n/ja.js';
  import VxPagination from '@/components/vx-pagination/VxPagination.vue';
  import Axios from "axios";
  import config from "../../../app.config";
  import VxCard from '../../../components/vx-card/VxCard.vue';
  import VueCtkDateTimePicker from '../../../components/VueCtkDateTimePicker'
  import { Validator } from 'vee-validate';
  import Encoding from 'encoding-japanese';
  import Datepicker from 'vuejs-datepicker';
  import {ja} from "vuejs-datepicker/dist/locale";
  import router from "../../../router";
  import moment from 'moment';
  const dict = {
    custom: {
    }
  };
  Validator.localize('ja', dict);
  export default {
    components: {
      InfiniteLoading,
      VxPagination,
      VxCard,
      VueCtkDateTimePicker,
      Datepicker,
    },
    data() {
      return {
        vacationFlagOptions: [
          {code: '',                       label: 'なし'       },
          {code: 'paid_vacation_flg',      label: '有給'       },
          {code: 'sp_vacation_flg',        label: '特休'       },
          {code: 'day_off_flg',            label: '代休'       },
          {code: 'half_paid_vacation_flg', label: '有給（半休）'},
          {code: 'half_sp_vacation_flg',   label: '特休（半休）'},
          {code: 'half_day_off_flg',       label: '代休（半休）'},
        ],
        shiftWorkKbnOptions: [
          {code: 0, label: '通常勤務'},
          {code: 1, label: 'シフト勤務1'},
          {code: 2, label: 'シフト勤務2'},
          {code: 3, label: 'シフト勤務3'},
          {code: 4, label: 'フレックス勤務'}
        ],
        absentFlagOptions: [
          {code: 0, label: 'なし'},
          {code: 1, label: '欠勤'}
        ],
        earlyLeaveFlagOptions: [
          {code: 0, label: 'なし'},
          {code: 1, label: '早退'}
        ],
        lateFlagOptions: [
          {code: 0, label: 'なし'},
          {code: 1, label: '遅刻'}
        ],
        approvalStateOptions: [
          {code: 0, label: '未承認'},
          {code: 1, label: '承認済'},
          {code: 2, label: '修正依頼'}
        ],
        holidayWorkFlgOptions: [
          {code: 0, label: 'なし'},
          {code: 1, label: '休日出勤'},
        ],
        showExportPopup: false,
        workListSelectExport: {
          name: true,
          email: true,
          workDate: true,
          workStartTime: true,
          workEndTime: true,
          breakTime: true,
          workingTime: true,
          overTime: true,
          absent: true,
          lateFlg: true,
          earlyLeave: true,
          paidVacation: true,
          spVacation: true,
          dayOff: true,
          memo: true,
          workDetail: true,
          adminMemo: true
        },
        workMonthExport: '',
        listSave:[],
        modalEditWorkListDetail: false,
        breakTimeErrors: {
          range: '整数で入力してください。（最大999まで）',
        },
        midnightBreakTimeErrors: {
          range: '整数で入力してください。（最大999まで）',
        },
        workStartTimeSmallerOrEqualWorkEndTime: '出勤時間＜退勤時間としてください。（＊）', // startTime <= endTime
        workStartTimeIsRequired: '出勤時間は必須項目です。（＊）', // startTime: required
        workEndTimeIsRequired: '退勤時間は必須項目です。（＊）', // endTime: required
        pagination:{ totalPage:0, currentPage: 1, limit: 10, totalItem:0, from: 1, to: 10 },
        detailWork: {},
        listApprovalStatus: ['未承認', '承認済', '修正依頼'],
        workMonth: this.$moment(this.$route.params.working_month +"02", "YYYYMMDD").toDate(),
        DatePickerFormat: 'yyyy年MM月',
        buttonNowTranslation: '現在',
        defaultTime: '',
        clearButton: true,
        ja: ja,
        workingDaySelected : null,
        disableFieldDialog: {
          disableWorkStartTime: false,
          disableWorkEndTime: false,
          disableBreakTime: false,
          disableMidnightBreakTime: false,
          disableAbsent: false,
          disableEarlyLeave: false,
          disableLateWork: false,
          disableVacation: false,
          disabledApprovalState: true,
          disabledAdminMemo: true,
          disabledAdminWorkDetail: true,
          disableShiftWorkKbn: false,
          disableHolidayWorkFlg: false
        },
        isProcessing: false,
        currentDate: '',
        titleDateDialog: '',
        workStartTimeDialogCompare: '',
        detailIdDialog: '',
        workStartTimeDialog: '',
        workEndTimeDialog: '',
        startStampingDialog: '',
        endStampingDialog: '',
        breakTimeDialog: 0,
        midnightBreakTimeDialog: 0,
        absentFlagDialog: 0,
        earlyLeaveFlagDialog: 0,
        lateFlagDialog: 0,
        holidayWorkFlgDialog: 0,
        vacationFlagDialog: '',
        approvalStateDialog: 0,
        shiftWorkKbnDialog: 0,
        memoDialog: '',
        workDetailDialog: '',
        adminMemoDialog: '',
        checkWorkEndTime: false,
        isSearchWorkDate: false,
        user: null,
      }
    },
    async created() {
      this.currentDate = this.$moment().format("YYYY-MM-DD");
      await this.addLogOperation({action: 'hr-user-work-detail-access-screen', result: 0});
    },
    computed: {
      getUserName() {
        return !!this.user ? this.user.family_name + ' ' + this.user.given_name : '';
      },
      hasMidnight(){
        let format = 'YYYY-MM-DD hh:mm:ss';

        let startTime = moment(this.workStartTimeDialog, format);
        let time22 = moment(startTime.format('YYYY-MM-DD') + ' 22:00:00',format);
        let time05 = moment(startTime.format('YYYY-MM-DD') + ' 05:00:00',format);
        let endTime = moment(this.workEndTimeDialog, format);
        if(endTime < time22 && startTime > time05){
          this.midnightBreakTimeDialog = 0;
        }
        return endTime > time22 || startTime < time05;
      }
    },
    methods: {
      ...mapActions({
        getUser:                        "hr/getUser",
        addLogOperation:                "logOperation/addLog",
        getUserWorkDetail:              "hr/getUserWorkDetail",
        updateUserWorkDetail:           "hr/updateUserWorkDetail",
        exportUserWorkDetailToCSV:      "hr/exportUserWorkDetailToCSV",
        updateUserSubmissionState:      "hr/updateUserSubmissionState",
        getUserWorkDetailByTimecard:    "hr/getUserWorkDetailByTimecard",
        updateUserWorkDetailByTimecard: "hr/updateUserWorkDetailByTimecard",
        createUserWorkDetailByTimecard: "hr/createUserWorkDetailByTimecard",
        getWorkDetailByWorkingMonth:    "hr/getWorkDetailByWorkingMonth",
        getUserHrInfo:                  "hr/getUserHrInfo"
      }),
      removeFlexDatepicker(id){
        $('#'+id +' .datetimepicker').removeClass('flex');
        $('#'+id +' .datepicker-button').click(function(){
          $('#'+id +' .datetimepicker').removeClass('flex');
        })
        $('.month-container button.datepicker-day').attr('type', 'button');
      },
      isTimeNull(trgetValFrom,trgetValTo){
        if (trgetValFrom !== undefined && 
            trgetValFrom !== null && 
            trgetValTo   !== undefined && 
            trgetValTo   !== null) {
          return false;
        } else {
          return true;
        }
      },
      async editWorkListDetail(tr) {
        this.isProcessing            = false; // 更新ボタンを活性化
        this.modalEditWorkListDetail = true;
        this.workingDaySelected      = this.$moment(tr.work_date, "YYYY/MM/DD").format("YYYYMMDD");
        this.titleDateDialog         = this.$moment(this.workingDaySelected).format("YYYY-MM-DD");
        this.defaultTime             = this.titleDateDialog;
        this.shiftWorkKbnOptions     = [];
        if(!tr.id){
          // 新規データ
          this.resetFormDialog(); 
          this.currentDateCheck();

          // HRユーザー情報の勤務時間設定有無
          let isMstHrInfoExistsWorkingTimes = 
            !this.isTimeNull(tr.I_Regulations_work_start_time, tr.I_Regulations_work_end_time) || 
            !this.isTimeNull(tr.I_shift1_start_time,           tr.I_shift1_end_time) ||
            !this.isTimeNull(tr.I_shift2_start_time,           tr.I_shift2_end_time) || 
            !this.isTimeNull(tr.I_shift3_start_time,           tr.I_shift3_end_time);

          // HR就労時間管理の勤務時間設定有無
          let isHrWorkingHoursExistsWorkingTimes = 
            !this.isTimeNull(tr.H_Regulations_work_start_time, tr.H_Regulations_work_end_time) || 
            !this.isTimeNull(tr.H_shift1_start_time,           tr.H_shift1_end_time) ||
            !this.isTimeNull(tr.H_shift2_start_time,           tr.H_shift2_end_time) || 
            !this.isTimeNull(tr.H_shift3_start_time,           tr.H_shift3_end_time);

          // HRユーザー情報の勤務時間設定有り
          if (isMstHrInfoExistsWorkingTimes) {
            // シフト出勤区分ドロップダウンリストの制御
            // シフトフラグが0：通常勤務
            if (tr.shift_flg == 0){
              // シフト出勤区分 0:通常勤務 が選択可能
              this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
              this.shiftWorkKbnDialog = 0; // デフォルト値 通常勤務
            } else {
            // シフトフラグが1：シフト勤務
              // シフト規定出勤時刻と退勤時刻が設定されているもののみ選択可能
              // シフト勤務1,シフト勤務2,シフト勤務3
              if (!this.isTimeNull(tr.I_shift1_start_time, tr.I_shift1_end_time)) {
                this.shiftWorkKbnOptions.push({code: 1 ,label:'シフト勤務1'});
              } 
              if (!this.isTimeNull(tr.I_shift2_start_time, tr.I_shift2_end_time)) {
                this.shiftWorkKbnOptions.push({code: 2 ,label:'シフト勤務2'}); 
              } 
              if (!this.isTimeNull(tr.I_shift3_start_time, tr.I_shift3_end_time)) {
                this.shiftWorkKbnOptions.push({code: 3 ,label:'シフト勤務3'});
              }
              // ドロップダウンリストの最初のインデックスをデフォルト値とする
              if (this.shiftWorkKbnOptions.length > 0) {
                this.shiftWorkKbnDialog = this.shiftWorkKbnOptions[0].code;
              } else {
                // シフト出勤区分 0:通常勤務 が選択可能
                this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
                this.shiftWorkKbnDialog = 0; // デフォルト値 通常勤務
              }
            }
          } else {
          // HRユーザー情報の勤務時間設定無し
            // HR就労時間管理の勤務時間設定有無
            if (isHrWorkingHoursExistsWorkingTimes || 
               (tr.H_work_form_kbn == 2 && (tr.H_regulations_working_hours !== undefined && tr.H_regulations_working_hours !== null))) {
            // HR就労時間管理の勤務時間設定有り
              // HR就労時間管理の業務形態区分の値がフレックスの場合
              if (tr.H_work_form_kbn == 2) {
                this.shiftWorkKbnOptions.push({code: 4 ,label:'フレックス'});
                this.shiftWorkKbnDialog = 4; // デフォルト値 フレックス
              } else {
                // シフトフラグが0：通常勤務
                if (tr.H_work_form_kbn == 0){
                  // シフト出勤区分 0:通常勤務 が選択可能
                  this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
                  this.shiftWorkKbnDialog = 0; // デフォルト値 通常勤務
                } else {
                // シフトフラグが1：シフト勤務
                  // シフト規定出勤時刻と退勤時刻が設定されているもののみ選択可能
                  // シフト勤務1,シフト勤務2,シフト勤務3
                  if (!this.isTimeNull(tr.H_shift1_start_time, tr.H_shift1_end_time)) {
                    this.shiftWorkKbnOptions.push({code: 1 ,label:'シフト勤務1'});
                  } 
                  if (!this.isTimeNull(tr.H_shift2_start_time, tr.H_shift2_end_time)) {
                    this.shiftWorkKbnOptions.push({code: 2 ,label:'シフト勤務2'}); 
                  } 
                  if (!this.isTimeNull(tr.H_shift3_start_time, tr.H_shift3_end_time)) {
                    this.shiftWorkKbnOptions.push({code: 3 ,label:'シフト勤務3'});
                  }
                  // ドロップダウンリストの最初のインデックスをデフォルト値とする
                  if (this.shiftWorkKbnOptions.length > 0) {
                    this.shiftWorkKbnDialog = this.shiftWorkKbnOptions[0].code;
                  } else {
                    // シフト出勤区分 0:通常勤務 が選択可能
                    this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
                    this.shiftWorkKbnDialog = 0; // デフォルト値 通常勤務
                  }
                }
              }
            } else {
            // HR就労時間管理の勤務時間設定無し
              // シフト出勤区分 0:通常勤務 が選択可能
              this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
              this.shiftWorkKbnDialog = 0; // デフォルト値 通常勤務
            }
          }
        } else {
          // 既存データ
          this.detailIdDialog = tr.id;
          let hrTimeCardDetail = await this.getUserWorkDetailByTimecard(this.detailIdDialog);
          let workStartTimeInput = '';
          let workEndTimeInput = '';
          let startStamping = '';
          let endStamping = '';
          // HRユーザー情報の勤務時間設定有無
          let isMstHrInfoExistsTimesInfo = 
            !this.isTimeNull(hrTimeCardDetail['I_Regulations_work_start_time'], hrTimeCardDetail['I_Regulations_work_end_time']) || 
            !this.isTimeNull(hrTimeCardDetail['I_shift1_start_time'],           hrTimeCardDetail['I_shift1_end_time']) ||
            !this.isTimeNull(hrTimeCardDetail['I_shift2_start_time'],           hrTimeCardDetail['I_shift2_end_time']) || 
            !this.isTimeNull(hrTimeCardDetail['I_shift3_start_time'],           hrTimeCardDetail['I_shift3_end_time']);

          // HR就労時間管理の勤務時間設定有無
          let isHrWorkingHoursExistsTimesInfo = 
            !this.isTimeNull(hrTimeCardDetail['H_Regulations_work_start_time'], hrTimeCardDetail['H_Regulations_work_end_time']) || 
            !this.isTimeNull(hrTimeCardDetail['H_shift1_start_time'],           hrTimeCardDetail['H_shift1_end_time']) ||
            !this.isTimeNull(hrTimeCardDetail['H_shift2_start_time'],           hrTimeCardDetail['H_shift2_end_time']) || 
            !this.isTimeNull(hrTimeCardDetail['H_shift3_start_time'],           hrTimeCardDetail['H_shift3_end_time']);

          // HRユーザー情報の勤務時間設定有り
          if (isMstHrInfoExistsTimesInfo) {
            // シフト出勤区分ドロップダウンリストの制御
            // シフトフラグが0：通常勤務
            if (hrTimeCardDetail['shift_flg'] == 0){
              // シフト出勤区分 0:通常勤務 が選択可能
              this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
            } else {
              // シフトフラグが1：シフト勤務
              // シフト規定出勤時刻と退勤時刻が設定されているもののみ選択可能
              // シフト勤務1,シフト勤務2,シフト勤務3
              if (!this.isTimeNull(hrTimeCardDetail['I_shift1_start_time'], hrTimeCardDetail['I_shift1_end_time'])) {
                this.shiftWorkKbnOptions.push({code: 1 ,label:'シフト勤務1'});
              } 
              if (!this.isTimeNull(hrTimeCardDetail['I_shift2_start_time'], hrTimeCardDetail['I_shift2_end_time'])) {
                this.shiftWorkKbnOptions.push({code: 2 ,label:'シフト勤務2'});
              } 
              if (!this.isTimeNull(hrTimeCardDetail['I_shift3_start_time'], hrTimeCardDetail['I_shift3_end_time'])) {
                this.shiftWorkKbnOptions.push({code: 3 ,label:'シフト勤務3'});
              } 
            }
          } else {
          // HRユーザー情報の勤務時間設定無し
            // HR就労時間管理の勤務時間設定有無
            if (isHrWorkingHoursExistsTimesInfo || 
               (hrTimeCardDetail['H_work_form_kbn'] == 2 &&
               (hrTimeCardDetail['H_regulations_working_hours'] !== undefined && 
                hrTimeCardDetail['H_regulations_working_hours'] !== null))) {
              // HR就労時間管理の勤務時間設定有り
              // HR就労時間管理の業務形態区分の値がフレックスの場合
              if (hrTimeCardDetail['H_work_form_kbn'] == 2) {
                this.shiftWorkKbnOptions.push({code: 4 ,label:'フレックス'});
              } else {
                // シフトフラグが0：通常勤務
                if (hrTimeCardDetail['H_work_form_kbn'] == 0){
                  // シフト出勤区分 0:通常勤務 が選択可能
                  this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'});
                } else {
                  // シフトフラグが1：シフト勤務
                  // シフト規定出勤時刻と退勤時刻が設定されているもののみ選択可能
                  // シフト勤務1,シフト勤務2,シフト勤務3
                  if (!this.isTimeNull(hrTimeCardDetail['H_shift1_start_time'], hrTimeCardDetail['H_shift1_end_time'])) {
                    this.shiftWorkKbnOptions.push({code: 1 ,label:'シフト勤務1'});
                  } 
                  if (!this.isTimeNull(hrTimeCardDetail['H_shift2_start_time'], hrTimeCardDetail['H_shift2_end_time'])) {
                    this.shiftWorkKbnOptions.push({code: 2 ,label:'シフト勤務2'});
                  } 
                  if (!this.isTimeNull(hrTimeCardDetail['H_shift3_start_time'], hrTimeCardDetail['H_shift3_end_time'])) {
                    this.shiftWorkKbnOptions.push({code: 3 ,label:'シフト勤務3'});
                  } 
                }
              }
            } else {
              // HR就労時間管理の勤務時間設定有り
              // シフト出勤区分 0:通常勤務 が選択可能
              this.shiftWorkKbnOptions.push({code: 0 ,label:'通常勤務'}); 
            }
          }

          // 勤務詳細のシフト出勤区分をドロップダウンリストのデフォルト値に設定する
          this.shiftWorkKbnDialog = hrTimeCardDetail['shift_work_kbn'];

          if (hrTimeCardDetail['work_start_time']) {
            workStartTimeInput = hrTimeCardDetail['work_start_time'];
          }
          if (hrTimeCardDetail['work_end_time']) {
            workEndTimeInput = hrTimeCardDetail['work_end_time'];
          }
          this.workStartTimeDialog = workStartTimeInput;
          this.workEndTimeDialog = workEndTimeInput;

          if (hrTimeCardDetail['start_stamping']) {
            startStamping = hrTimeCardDetail['start_stamping'];
          }
          if (hrTimeCardDetail['end_stamping']) {
            endStamping = hrTimeCardDetail['end_stamping'];
          }
          this.startStampingDialog = startStamping;
          this.endStampingDialog = endStamping;
          /* workStartTimeParam not null, workEndTimeParam null */

          this.breakTimeDialog = hrTimeCardDetail['break_time'];
          this.midnightBreakTimeDialog = hrTimeCardDetail['midnight_break_time'];
          this.absentFlagDialog = hrTimeCardDetail['absent_flg'];
          this.earlyLeaveFlagDialog = hrTimeCardDetail['earlyleave_flg'];
          this.lateFlagDialog = hrTimeCardDetail['late_flg'];
          this.holidayWorkFlgDialog = hrTimeCardDetail['holiday_work_flg'];

          /* Process vacationFlagDialog */
          if (hrTimeCardDetail['paid_vacation_flg'] === 1) {
            this.vacationFlagDialog = 'paid_vacation_flg';
          } else if (hrTimeCardDetail['paid_vacation_flg'] === 2) {
            this.vacationFlagDialog = 'half_paid_vacation_flg';
          } else if (hrTimeCardDetail['sp_vacation_flg'] === 1) {
            this.vacationFlagDialog = 'sp_vacation_flg';
          } else if (hrTimeCardDetail['sp_vacation_flg'] === 2) {
            this.vacationFlagDialog = 'half_sp_vacation_flg';
          } else if (hrTimeCardDetail['day_off_flg'] === 1) {
            this.vacationFlagDialog = 'day_off_flg';
          } else if (hrTimeCardDetail['day_off_flg'] === 2) {
            this.vacationFlagDialog = 'half_day_off_flg';     
            this.lateFlagDialog = 0;
          } else {
            this.vacationFlagDialog = '';
          }
          this.approvalStateDialog = hrTimeCardDetail['approval_state'];
          this.memoDialog = hrTimeCardDetail['memo'];
          this.workDetailDialog = hrTimeCardDetail['work_detail'];
          this.adminMemoDialog = hrTimeCardDetail['admin_memo'];

          if (hrTimeCardDetail['absent_flg'] == 1){
            // absent case
            this.holidayClearPatan();
            this.disableFormDialog();
            this.disableFieldDialog.disableAbsent = false;
            this.disableFieldDialog.disableVacation = true;
          } else if (this.vacationFlagDialog) { 
            // vacation case
            this.disableFieldDialog.disableAbsent = true;
            this.disableFieldDialog.disableVacation = false;
            if (this.vacationFlagDialog === "paid_vacation_flg" || this.vacationFlagDialog === "sp_vacation_flg" || this.vacationFlagDialog === "day_off_flg") {
              this.holidayClearPatan();
              this.disableFormDialog();
            } else if (this.vacationFlagDialog === "half_paid_vacation_flg" || this.vacationFlagDialog === "half_sp_vacation_flg" || this.vacationFlagDialog === "half_day_off_flg") {
              this.halfVacationFormDialog();
            }  
          } else {
            this.currentDateCheck();
          }
        }
      },
      holidayClearPatan() {
        this.workStartTimeDialog = '';
        this.workEndTimeDialog = '';
        this.breakTimeDialog = 0;
        this.midnightBreakTimeDialog = 0;
        this.earlyLeaveFlagDialog = 0;
        this.lateFlagDialog = 0; 
      },
      halfVacationFormDialog() {
        this.disableFieldDialog.disableWorkStartTime = false;
        this.disableFieldDialog.disableWorkEndTime = false;
        this.disableFieldDialog.disableBreakTime = false;
        this.disableFieldDialog.disableMidnightBreakTime = false;
        this.disableFieldDialog.disableEarlyLeave = true;
        this.disableFieldDialog.disableLateWork = true;
      },
      currentDateCheck() {
        if(this.titleDateDialog > this.currentDate){
          this.disableFormDialog(); // 当日以降
          this.disableFieldDialog.disableAbsent = true;
          this.disableFieldDialog.disableVacation = false;
        } else { 
          this.enableFormDialog();  // 当日以前
          this.disableFieldDialog.disableAbsent = false;
          this.disableFieldDialog.disableVacation = false;
        }
      },
      resetFormDialog () {
        this.detailIdDialog = '';
        this.workStartTimeDialog = '';
        this.workEndTimeDialog = '';
        this.startStampingDialog = '';
        this.endStampingDialog = '';
        this.breakTimeDialog = 0;
        this.midnightBreakTimeDialog = 0;
        this.absentFlagDialog = 0;
        this.earlyLeaveFlagDialog = 0;
        this.lateFlagDialog = 0;
        this.vacationFlagDialog = '';
        this.approvalStateDialog = 0;
        this.memoDialog = '';
        this.workDetailDialog = '';
        this.adminMemoDialog = '';
      },
      disableFormDialog(){
        this.disableFieldDialog.disableWorkStartTime = true;
        this.disableFieldDialog.disableWorkEndTime = true;
        this.disableFieldDialog.disableBreakTime = true;
        this.disableFieldDialog.disableMidnightBreakTime = true;
        this.disableFieldDialog.disableEarlyLeave = true;
        this.disableFieldDialog.disableLateWork = true;
      },
      enableFormDialog(){
        this.disableFieldDialog.disableWorkStartTime = false;
        this.disableFieldDialog.disableWorkEndTime = false;
        this.disableFieldDialog.disableBreakTime = false;
        this.disableFieldDialog.disableMidnightBreakTime = false;
        this.disableFieldDialog.disableEarlyLeave = false;
        this.disableFieldDialog.disableLateWork = false;
      },
      triggerSaveBtnClickEvent() {
        if (!this.isProcessing) {
          $('.worklist-detail-update-button').trigger("click");
        }
      },
      async onSave(id){
        let workStartTimeParam = '';
        let workEndTimeParam = '';
        if (this.vacationFlagDialog === '' || this.vacationFlagDialog === "half_paid_vacation_flg" || this.vacationFlagDialog === "half_sp_vacation_flg" || this.vacationFlagDialog === "half_day_off_flg") {
          if (this.workStartTimeDialog) {
            workStartTimeParam = this.workStartTimeDialog;
          }
          if (this.workEndTimeDialog) {
            workEndTimeParam = this.workEndTimeDialog;
          }
        }
        /* workStartTimeParam = null, workEndTimeParam not null*/
        if (workStartTimeParam === '' && workEndTimeParam) {
          return;
        }
        if (workStartTimeParam && workEndTimeParam === '' && (this.titleDateDialog < this.currentDate)) {
          return;
        }
        /* workStartTimeParam > workEndTimeParam */
        if ( workStartTimeParam && workEndTimeParam && (workStartTimeParam > workEndTimeParam) ) {
          return;
        }

        this.isProcessing = true; // 処理中は更新ボタンを非活性にし、押下できなくする
        let data = {
          users: this.user,
          work_date: this.workingDaySelected,
          shift_work_kbn : this.shiftWorkKbnDialog,
          work_start_time : workStartTimeParam,
          work_end_time : workEndTimeParam,
          break_time : this.breakTimeDialog,
          midnight_break_time : this.midnightBreakTimeDialog,
          absent_flg : this.absentFlagDialog,
          earlyleave_flg : this.earlyLeaveFlagDialog,
          late_flg: this.lateFlagDialog,
          holiday_work_flg: this.holidayWorkFlgDialog,
          memo: this.memoDialog,
          work_detail: this.workDetailDialog,
          admin_memo: this.adminMemoDialog
        }
        if(this.vacationFlagDialog === "paid_vacation_flg"){
          data.vacation_flg = 'paid_vacation_flg';
        }else if(this.vacationFlagDialog === "sp_vacation_flg"){
          data.vacation_flg = 'sp_vacation_flg';
        }else if(this.vacationFlagDialog === "day_off_flg"){
          data.vacation_flg = 'day_off_flg';
        } else if(this.vacationFlagDialog === "half_paid_vacation_flg"){
          data.vacation_flg = 'half_paid_vacation_flg';
        }else if(this.vacationFlagDialog === "half_sp_vacation_flg"){
          data.vacation_flg = 'half_sp_vacation_flg';
        }else if(this.vacationFlagDialog === "half_day_off_flg"){
          data.vacation_flg = 'half_day_off_flg';
        } else {
          data.vacation_flg = '';
        }
        this.$validator.validate().then(async valid => {
          if(valid){
            if(id){
              data.id = id;
              await this.updateUserWorkDetailByTimecard(data);
              this.modalEditWorkListDetail = false;
            }else{
              await this.createUserWorkDetailByTimecard(data);
              this.modalEditWorkListDetail = false;
            }
            // recall api after update
            await this.getWorkListDetail();
            this.isProcessing = false; // 更新ボタンを活性化
          }
        });
      },
      updateVacationFlag() { 
        if (this.vacationFlagDialog) {
          if (this.vacationFlagDialog === "paid_vacation_flg" || this.vacationFlagDialog === "sp_vacation_flg" || this.vacationFlagDialog === "day_off_flg"){
            this.disableFormDialog();
            this.holidayClearPatan();
            this.disableFieldDialog.disableAbsent = true;
            this.absentFlagDialog = 0;
          } else if (this.vacationFlagDialog === "half_paid_vacation_flg" || this.vacationFlagDialog === "half_sp_vacation_flg" || this.vacationFlagDialog === "half_day_off_flg") {
            this.halfVacationFormDialog();
            this.disableFieldDialog.disableAbsent = true;
            this.absentFlagDialog = 0;
            this.earlyLeaveFlagDialog = 0;
            this.lateFlagDialog = 0;
          } else {
            this.currentDateCheck();
          }
        } else {
          if(this.titleDateDialog > this.currentDate){
            // 当日以降
            this.disableFieldDialog.disableAbsent = true;
            this.disableFieldDialog.disableVacation = false;
            this.disableFormDialog();
            this.holidayClearPatan();
          } else { 
            // 当日以前
            if (this.absentFlagDialog == 0){
              this.disableFieldDialog.disableAbsent = false;
              this.enableFormDialog();
            } else if (this.absentFlagDialog == 1) {
              this.disableFieldDialog.disableVacation = true;
              this.disableFormDialog();
              this.holidayClearPatan();
            } else {
              this.disableFieldDialog.disableVacation = true;
              this.disableFormDialog();
            }
          }
        }
      },
      updateAbsentFlg() {
        if (this.absentFlagDialog == 0){
          this.disableFieldDialog.disableVacation = false;
          this.enableFormDialog();
        } else if (this.absentFlagDialog == 1) {
          this.disableFieldDialog.disableVacation = true;
          this.disableFormDialog();
          this.holidayClearPatan();
        } else {
          this.currentDateCheck();
        }
      },
      createWorkListDetail(){
        var working_month = this.$route.params.working_month;
        const myDate = this.$moment(working_month, "YYYYMM").daysInMonth();
        const curMonth = this.$moment(working_month, "YYYYMM").format("YYYY年MM月");
        for (var i = 1; i <= myDate; i++) {
          this.listSave.push({
            id: '',
            work_date : curMonth + i + '日',
            shift_flg : 0,
            shift_work_kbn : 0, 
            I_Regulations_work_start_time: '',
            I_Regulations_work_end_time: '',
            I_shift1_start_time: '',
            I_shift1_end_time: '',
            I_shift2_start_time: '',
            I_shift2_end_time: '',
            I_shift3_start_time: '',
            I_shift3_end_time: '',
            H_Regulations_work_start_time: '',
            H_Regulations_work_end_time: '',
            H_shift1_start_time: '',
            H_shift1_end_time: '',
            H_shift2_start_time: '',
            H_shift2_end_time: '',
            H_shift3_start_time: '',
            H_shift3_end_time: '',
            H_work_form_kbn: '',
            H_regulations_working_hours: '',
            work_start_time: '',
            work_end_time: '',
            break_time: '',
            midnight_break_time: '',
            working_time: '',
            earlyleave_flg: '',
            approval_state: '',
            memo : '',
            work_detail : '',
            buttonStatus: 0,
          });
        }
      },
      async getWorkListDetail(){
        var mst_user_id = this.$route.params.id;
        var working_month = this.$route.params.working_month;
        this.workDetail = await this.getUserWorkDetail({id:mst_user_id, working_month:working_month});
        // シフト勤務情報を取得（シフトフラグ、規定業務開始時刻～シフト3終了時刻、シフト出勤区分）
        this.workUserHrInfo = await this.getUserHrInfo({id:mst_user_id});
        const myDate = this.$moment(working_month, "YYYYMM").daysInMonth();
        var arrFullWorkDetail = {};
        var date = '';
        var countApprovalState = 0;
        for (var j = 0; j < this.workDetail.length; j++) {
          var curDate = this.workDetail[j].work_date.substring(6,8);
          arrFullWorkDetail[curDate] = this.workDetail[j];
          if(this.workDetail[j].approval_state == 1){
            countApprovalState++;
          }
        }
        this.totalWorkingTime = 0;
        for (var i = 1; i <= myDate; i++){
          this.listSave[i-1].work_date = this.$moment(this.listSave[i-1].work_date, "YYYY年MM月DD日");
          if(i<10){
            date = '0' + i;
          } else {
            date = i;
          }
          //シフトフラグ
          this.listSave[i-1].shift_flg                     = this.workUserHrInfo.shift_flg;
          //規定業務開始時刻～シフト3終了時刻
          this.listSave[i-1].I_Regulations_work_start_time = this.workUserHrInfo.I_Regulations_work_start_time;
          this.listSave[i-1].I_Regulations_work_end_time   = this.workUserHrInfo.I_Regulations_work_end_time;
          this.listSave[i-1].I_shift1_start_time           = this.workUserHrInfo.I_shift1_start_time;
          this.listSave[i-1].I_shift1_end_time             = this.workUserHrInfo.I_shift1_end_time;
          this.listSave[i-1].I_shift2_start_time           = this.workUserHrInfo.I_shift2_start_time;
          this.listSave[i-1].I_shift2_end_time             = this.workUserHrInfo.I_shift2_end_time;
          this.listSave[i-1].I_shift3_start_time           = this.workUserHrInfo.I_shift3_start_time;
          this.listSave[i-1].I_shift3_end_time             = this.workUserHrInfo.I_shift3_end_time;
          this.listSave[i-1].H_Regulations_work_start_time = this.workUserHrInfo.H_Regulations_work_start_time;
          this.listSave[i-1].H_Regulations_work_end_time   = this.workUserHrInfo.H_Regulations_work_end_time;
          this.listSave[i-1].H_shift1_start_time           = this.workUserHrInfo.H_shift1_start_time;
          this.listSave[i-1].H_shift1_end_time             = this.workUserHrInfo.H_shift1_end_time;
          this.listSave[i-1].H_shift2_start_time           = this.workUserHrInfo.H_shift2_start_time;
          this.listSave[i-1].H_shift2_end_time             = this.workUserHrInfo.H_shift2_end_time;
          this.listSave[i-1].H_shift3_start_time           = this.workUserHrInfo.H_shift3_start_time;
          this.listSave[i-1].H_shift3_end_time             = this.workUserHrInfo.H_shift3_end_time;
          this.listSave[i-1].H_work_form_kbn               = this.workUserHrInfo.H_work_form_kbn;
          this.listSave[i-1].H_regulations_working_hours   = this.workUserHrInfo.H_regulations_working_hours;
          if(Object.prototype.hasOwnProperty.call(arrFullWorkDetail, date)){
            var breakTime = new Date(0,0);
            //if id exists else id = ''
            this.listSave[i-1].id = arrFullWorkDetail[date].id;
            this.listSave[i-1].shift_work_kbn   = arrFullWorkDetail[date].shift_work_kbn;
            this.listSave[i-1].work_start_time  = arrFullWorkDetail[date].work_start_time;
            this.listSave[i-1].work_end_time    = arrFullWorkDetail[date].work_end_time;
            this.listSave[i-1].start_stamping   = arrFullWorkDetail[date].start_stamping;
            this.listSave[i-1].end_stamping     = arrFullWorkDetail[date].end_stamping;
             // set shjft time column
            let startShiftI;
            let endShiftI;
            let startShiftH;
            let endShiftH;
            if(!!this.listSave[i-1].shift_work_kbn){
              startShiftI   = this.workUserHrInfo[`I_shift${this.listSave[i-1].shift_work_kbn}_start_time`];
              endShiftI     = this.workUserHrInfo[`I_shift${this.listSave[i-1].shift_work_kbn}_end_time`];
              startShiftH   = this.workUserHrInfo[`H_shift${this.listSave[i-1].shift_work_kbn}_start_time`] ?? '';
              endShiftH     = this.workUserHrInfo[`H_shift${this.listSave[i-1].shift_work_kbn}_end_time`] ?? '';

            }else{
              startShiftI   = this.workUserHrInfo.I_Regulations_work_start_time;
              endShiftI     = this.workUserHrInfo.I_Regulations_work_end_time;
              startShiftH   = this.workUserHrInfo.H_Regulations_work_start_time ?? '';
              endShiftH     = this.workUserHrInfo.H_Regulations_work_end_time ?? '';
            }

            let shift_start_label = startShiftI ? startShiftI.substring(0, 5) : startShiftH.substring(0, 5);
            let shift_end_label   =  endShiftI ? endShiftI.substring(0, 5) : endShiftH.substring(0, 5);
            this.listSave[i-1].shift_time_label = (!!shift_start_label && !!shift_end_label ? `${shift_start_label} - ${shift_end_label}` : '') ;
            //check work_end_time
            if(arrFullWorkDetail[date].work_start_time != null && arrFullWorkDetail[date].work_end_time != null){
              var start_time = arrFullWorkDetail[date].work_start_time.slice(0,10);
              var end_time = arrFullWorkDetail[date].work_end_time.slice(0,10);
              this.titleDateDialog = this.$moment(this.listSave[i-1].work_date, "YYYY年MM月DD日").format("YYYY-MM-DD");
              if(this.titleDateDialog == end_time) {
                this.listSave[i-1].work_end_time = this.$moment(arrFullWorkDetail[date].work_end_time).format("HH:mm");
              }
              else  this.listSave[i-1].work_end_time = this.$moment(arrFullWorkDetail[date].work_end_time).format("HH:mm (MM/DD)");
              if(this.titleDateDialog == start_time) {
                this.listSave[i-1].work_start_time = this.$moment(arrFullWorkDetail[date].work_start_time).format("HH:mm");
              }
              else  this.listSave[i-1].work_start_time = this.$moment(arrFullWorkDetail[date].work_start_time).format("HH:mm (MM/DD)");
            }
            // check time stamping
            if(arrFullWorkDetail[date].start_stamping != null && arrFullWorkDetail[date].end_stamping != null){
              var start_time_stamping = arrFullWorkDetail[date].start_stamping.slice(0,10);
              var end_time_stamping = arrFullWorkDetail[date].end_stamping.slice(0,10);
               if(this.titleDateDialog == start_time_stamping) {
                this.listSave[i-1].start_stamping = this.$moment(arrFullWorkDetail[date].start_stamping).format("HH:mm");
              }
              else  this.listSave[i-1].start_stamping = this.$moment(arrFullWorkDetail[date].start_stamping).format("HH:mm (MM/DD)");
              if(this.titleDateDialog == end_time_stamping) {
                this.listSave[i-1].end_stamping = this.$moment(arrFullWorkDetail[date].end_stamping).format("HH:mm");
              }
              else  this.listSave[i-1].end_stamping = this.$moment(arrFullWorkDetail[date].end_stamping).format("HH:mm (MM/DD)");
            }
            this.listSave[i-1].break_time = breakTime.setSeconds(+arrFullWorkDetail[date].break_time * 60);
            //covert working time to HH:mm
            if(arrFullWorkDetail[date].working_time != null){
              if(arrFullWorkDetail[date].working_time != 0){
                var working_time_hour = (arrFullWorkDetail[date].working_time / 60);
                this.totalWorkingTime = this.totalWorkingTime + arrFullWorkDetail[date].working_time;
                var rhours = Math.floor(working_time_hour);
                var minutes = (working_time_hour - rhours) * 60;
                var rminutes = Math.round(minutes);
                if (rhours < 10){
                  rhours = '0' + rhours;
                }
                if (rminutes < 10){
                  rminutes = '0' + rminutes;
                }
                this.listSave[i-1].working_time = rhours + ":" + rminutes;
              }else{this.listSave[i-1].working_time = ''}
            }
            else{this.listSave[i-1].working_time = ''}

            //check column 休暇等
            this.listSave[i-1].earlyleave_flg  = 'なし';
            if(arrFullWorkDetail[date].earlyleave_flg == 1){
              this.listSave[i-1].earlyleave_flg = '早退';
              if(arrFullWorkDetail[date].late_flg == 1){
                this.listSave[i-1].earlyleave_flg = '遅刻-早退';
              }
            }
            if(arrFullWorkDetail[date].late_flg == 1 && arrFullWorkDetail[date].earlyleave_flg == 0){
              this.listSave[i-1].earlyleave_flg = '遅刻';
            }
            if(arrFullWorkDetail[date].paid_vacation_flg == 1){
              this.listSave[i-1].earlyleave_flg = '有給';
              this.listSave[i-1].break_time = '';
              this.listSave[i-1].working_time = '';
            }
            else if(arrFullWorkDetail[date].paid_vacation_flg == 2){
              this.listSave[i-1].earlyleave_flg = '有給（半休）';
            }
            if(arrFullWorkDetail[date].sp_vacation_flg == 1){
              this.listSave[i-1].earlyleave_flg = '特休';
              this.listSave[i-1].break_time = '';
              this.listSave[i-1].working_time = '';
            }
            else if(arrFullWorkDetail[date].sp_vacation_flg == 2){
              this.listSave[i-1].earlyleave_flg = '特休（半休）';
            }
            if(arrFullWorkDetail[date].day_off_flg == 1){
              this.listSave[i-1].earlyleave_flg = '代休';
              this.listSave[i-1].break_time = '';
              this.listSave[i-1].working_time = '';
            }
            else if(arrFullWorkDetail[date].day_off_flg == 2){
              this.listSave[i-1].earlyleave_flg = '代休（半休）';
            }

            if(arrFullWorkDetail[date].day_off_flg == 2 || arrFullWorkDetail[date].sp_vacation_flg == 2 || arrFullWorkDetail[date].paid_vacation_flg == 2){
              if (arrFullWorkDetail[date].work_start_time == null && arrFullWorkDetail[date].work_end_time == null
                  && arrFullWorkDetail[date].working_time == 0 && arrFullWorkDetail[date].break_time == 0) {
                this.listSave[i-1].break_time = '';
                this.listSave[i-1].working_time = '';
              }
            }
            // check column 承認状態
            if(arrFullWorkDetail[date].approval_state == 0){
              this.listSave[i-1].approval_state = this.listApprovalStatus[0];
            }
            else if(arrFullWorkDetail[date].approval_state == 1){
              this.listSave[i-1].approval_state = this.listApprovalStatus[1];
              this.listSave[i-1].buttonStatus = 1;
            }
            else if(arrFullWorkDetail[date].approval_state == 2){
              this.listSave[i-1].approval_state = this.listApprovalStatus[2];
            }
            else this.listSave[i-1].approval_state = '';

            this.listSave[i-1].memo            = arrFullWorkDetail[date].memo;
            this.listSave[i-1].work_detail     = arrFullWorkDetail[date].work_detail;

            //if in normal day: start_time = null & end_time = null & working_time=0 & ... ko hien thi
            if(arrFullWorkDetail[date].work_start_time == null && arrFullWorkDetail[date].work_end_time == null
                && arrFullWorkDetail[date].working_time == 0 && arrFullWorkDetail[date].break_time == 0
                && arrFullWorkDetail[date].paid_vacation_flg == 0 && arrFullWorkDetail[date].sp_vacation_flg ==0 && arrFullWorkDetail[date].day_off_flg == 0 && arrFullWorkDetail[date].absent_flg == 0){
              this.listSave[i-1].working_time = '';
              this.listSave[i-1].earlyleave_flg = '';
              this.listSave[i-1].approval_state = '';
              this.listSave[i-1].break_time = '';
            }
            if(arrFullWorkDetail[date].absent_flg == 1){
              this.listSave[i-1].earlyleave_flg = '欠勤';
              this.listSave[i-1].break_time = '';
              this.listSave[i-1].working_time = '';
            }
          }
        }
      },
      async getWorkMonth () {
        this.workMonthExport = this.$route.params.working_month;
      },
      onSelectExport: async function() {
        await this.addLogOperation({action: 'hr-user-work-detail-open-export-csv', result: 0});
        this.showExportPopup = true;
      },
      exportHrWorkListToCSV: async function() {
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
        if (this.workListSelectExport.workDetail) {
          optionExport.push('work_detail');
        }
        if (this.workListSelectExport.adminMemo) {
          optionExport.push('admin_memo');
        }
        let params = {
          export_work_list_columns : optionExport,
          work_month : this.workMonthExport,
          mst_user_id: this.$route.params.id,
        };
        let workListExport =  await this.exportUserWorkDetailToCSV(params);
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
        this.workListSelectExport.absent = true;
        this.workListSelectExport.lateFlg = true;
        this.workListSelectExport.earlyLeave = true;
        this.workListSelectExport.paidVacation = true;
        this.workListSelectExport.spVacation = true;
        this.workListSelectExport.dayOff = true;
        this.workListSelectExport.memo = true;
        this.workListSelectExport.workDetail = true;
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
      async updateSubmissionState(){
        this.$vs.dialog({
          type:'confirm',
          color: 'primary',
          title: `確認`,
          acceptText: 'はい',
          cancelText: 'いいえ',
          text: `提出しますか？`,
          accept: async ()=> {
            var working_month = this.$route.params.working_month;
            await  Axios.post(`${config.BASE_API_URL}/updateSubmissionState/${working_month}` )
            .then(response => {
              const myDate = this.$moment(working_month, "YYYY-MM").daysInMonth();
              for (var i = 1; i <= myDate; i++) {
                this.listSave[i-1].buttonStatus = 1; 
              }
              this.$vs.notify({
                color:'success',
                text:'提出しました。',
                position: 'bottom-left'
              })
              return response.data ? response.data.data: [];
            })
            .catch(error => {
              error = error.response;
              const message = (error && error.data && error.data.message) || error.statusText;
              return Promise.reject(message);
            });
          },
        });
      },
      //clear work_start_time and clear work_end_time button
      clearWorkStartTime(){
        this.workStartTimeDialog = '';
      },
      clearWorkEndTime(){
        this.workEndTimeDialog = '';
      },
      onApproval: async function() {
        await this.addLogOperation({action: 'hr-user-work-detail-approval', result: 0});
        this.approval();
      }, 
      async approval() {
        this.$vs.dialog({
          type:'confirm',
          color: 'primary',
          title: `確認`,
          acceptText: 'はい',
          cancelText: 'いいえ',
          text: `選択された勤務を承認します。`,
          accept: async ()=> {
            let params = {
              cids : this.getSelectedID(),
              mst_user_id: this.$route.params.id,
              work_month: this.$route.params.working_month,
            };
            await this.updateUserWorkDetail(params)
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
                // recall api after update
                this.getWorkListDetail();
              }
            })
            .catch(error => {
              error = error.response;
              const message = (error && error.data && error.message) || error.statusText;
              return Promise.reject(message);
            });
          },
        });
      },
      onSubmissionState: async function() {
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
              mst_user_id: this.$route.params.id,
              work_month: this.$route.params.working_month,
            }; 
            await this.updateUserSubmissionState(params)
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
              // recall api after update
              this.getWorkListDetail();
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
        this.listSave.forEach((item, stt) => {
          cids.push(item.id)
        });
        return cids;
      },
      async getUserInfo() {
        this.user = await this.getUser(this.$route.params.id);
      }
    },
    watch:{
      workMonth: async function(val) {
        if (val) {
          var curMonth = this.workMonth;
          var id = this.$route.params.id;
          var month = this.$moment(curMonth).format("YYYYMM");
          //this.addLogOperation({action: 'user-work-detail-change-work-date', result: 0});
          await router.push({ path: `/hr/user_work_detail/${id}/${month}` });
        }
      },
      workStartTimeDialog: async function() {
        console.log('Watch workStartTimeDialog');
      }
    },
    mounted() {
    //  this.$moment.locale('ja');
      this.createWorkListDetail();
      this.getUserInfo();
      this.getWorkMonth();
      this.getWorkListDetail();
      this.removeFlexDatepicker('DateTimeAttendance-wrapper');
      this.removeFlexDatepicker('DateTimeLeave-wrapper');
    }
  }
</script>
<style scoped>
  .edit-worklist-detail{
    border: 1px solid rgba(31, 116, 255, 1);
    color: black;
  }
  .title-date{
    border-bottom: 1px solid rgba(31, 116, 255, 1);
    background-color: #c8ebf7;
    font-weight: 700;
    padding: 10px;
  }
  .worklist-info{
    padding: 25px 10px 10px 10px;
    height: 35em;
    overflow-y: scroll;
  }
  .worklist-detail{
    text-align: right;
    padding-right: 20px;
    padding-top: 10px;
  }
  .memo{
    white-space: nowrap;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis !important;
  }
  .border-cell {
    border: 1px solid rgba(31, 116, 255, 1);
  }
  .title-bolder {
    font-weight: bolder;
  }
  .popup-csv-content-height {
    height: 35em;
    overflow-y: scroll;
  }
  th{
    font-size: 1rem !important;
  }
  .centerEdit{
    padding-left: 75px !important;
  }
  .endEdit{
    justify-content: flex-end;
    display: flex;
  }
  .clear_button{
    border: 1px solid rgba(0,0,0,.2);
    cursor: pointer;
    width: 100%;
    height: 100%;
  }
  </style>
<style>
  .datetimepicker.flex[style*='display: none;']{
    display: none !important;
  }
  .month-container button.datepicker-day:not(.enable){
    visibility: hidden;
  }
  .popup-dialog .vs-popup {
    width: 50em !important;
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
    padding: 3px 10px 3px 10px !important;
  }
  .vs__selected-options {
    height:31px;
  }
  .v-select .vs__dropdown-menu .vs__dropdown-option--highlight {
    color: #000000 !important;
  }
  .vs--disabled .vs__dropdown-toggle .vs__selected{
    color: #9c9b9b !important;
  }
  .user-name {
    font-weight: 700;
    vertical-align: middle;
    font-size: 1.2rem;
  }
.width-130{
  width: 130px !important;
}
.min-width-100{
  min-width: 100px;
}
.stamping-text{
  font-size: 12px;
  color: #919191;
}
.stamp-title{
  color: #919191;
}
</style>
