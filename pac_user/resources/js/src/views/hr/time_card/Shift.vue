<template>
  <div id="time-card">
    <vs-card style="margin: auto;overflow:hidden;font-size: inherit;">
      <div style="max-width: 720px;margin:auto;">
        <vs-col class="border-cell p-5" >
        <span>現在時刻:{{ currentTime }}</span>
        <vs-row class="mt-5">
          <vs-col vs-w="6" class="pr-5 border-first-row vs-xs-12">
            <vs-row style="height: 100%">
              <vs-col vs-w="4" class="text-center p-1 label-right">出勤</vs-col>
              <vs-col vs-w="8" class="text-right p-1" style="color: black">{{ start }}</vs-col>
            </vs-row>
          </vs-col>
          <vs-col vs-w="6" class="pr-5 border-first-row vs-xs-12 border-first-row right_border">
            <vs-row style="height: 100%">
              <vs-col vs-w="4" class="text-center p-1 label-right">退勤</vs-col>
              <vs-col vs-w="8" class="text-right p-1 " style="color: black">{{ end }}</vs-col>
            </vs-row>
          </vs-col>

        </vs-row>

        <vs-row class="right_border bottom_div  mt-3">
          <vs-col vs-w="12" class="border-second-row vs-xs-12">
            <vs-row style="height: 100%">
              <vs-col vs-w="4" class="text-center p-1 ">シフト1</vs-col>
              <vs-col vs-w="4" class="text-center p-1 ">シフト2</vs-col>
              <vs-col vs-w="4" class="text-center p-1 ">シフト3</vs-col>
            </vs-row>
          </vs-col>

        </vs-row>


        <vs-row class="right_border" >
          <vs-col vs-w="12" class="pr-4 border-second-row vs-xs-12">
            <vs-row style="height: 100%">
              <vs-col vs-w="4" class="text-center p-1 ">{{ shift1_start_time }}--{{ shift1_end_time }}</vs-col>
              <vs-col vs-w="4" class="text-center p-1 ">{{ shift2_start_time }}--{{ shift2_end_time }}</vs-col>
              <vs-col vs-w="4" class="text-center p-1 ">{{ shift3_start_time }}--{{ shift3_end_time }}</vs-col>
            </vs-row>
          </vs-col>
        </vs-row>

        <vs-row class="mt-5">
          <vs-checkbox v-model="holiday_work_flg">休日出勤の場合はチェック</vs-checkbox>
        </vs-row>
        <vs-row class="mt-1 tit_line" style="min-height:10em;">
          <vs-col vs-w="6" class="vs-xs-6">
            <vs-row class="pt-1 up_time_but flex-col items-end f-group-btn-mb" vs-type="flex">
              <div vs-w="12"  v-show="atime != false">
                <vs-button :disabled="!oneTimeStartStatus" class="square  m-0"
                           style="color:#fff;" color="#22AD38" v-on:click="UpTime('1')">
                  出勤<br>シフト1
                </vs-button>
              </div>
              <div vs-w="12" v-show="btime != false">
                <vs-button :disabled="!twoTimeStartStatus" class="square  m-0"
                           style="color:#fff;" color="#22AD38" v-on:click="UpTime('2')">
                  出勤<br>シフト2
                </vs-button>
              </div>
              <div vs-w="12" v-show="ctime != false">
                <vs-button :disabled="!threeTimeStartStatus" class="square  m-0"
                           style="color:#fff;" color="#22AD38" v-on:click="UpTime('3')">
                  出勤<br>シフト3
                </vs-button>
              </div>
            </vs-row>
          </vs-col>
          <vs-col vs-w="6" class="vs-xs-6">
            <div class="pt-1 up_time_but items-start flex-col flex f-group-btn-mb">
              <div vs-w="12">
                <vs-button :disabled="!fourTimeStartStatus" class="square m-0" style="color:#fff;" color="#22AD38"
                          v-on:click="OverTime()">
                  退勤
                </vs-button>
              </div>
            </div>
          </vs-col>
        </vs-row>
        <vs-row class="mt-1">
          <vs-col vs-w="12" class="vs-xs-12 my-2" vs-justify="flex-end">
            <h4>休憩 ({{breakNumber}} 回)</h4>
            <vs-col vs-w="12" class="pt-1 bottom_but" vs-type="flex" vs-justify="space-between">
              <vs-button vs-w="4" :disabled="!startBreakTimeStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                         color="#dddddd" style="color:white;border-color:#ffffff;background:#ff972b;font-weight: bold;" v-on:click="onBreakTime('start')">休憩開始
              </vs-button>
              <vs-button vs-w="4" :disabled="!endBreakTimeStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                         color="#dddddd" style="color:white;border-color:#ffffff;background:#ff972b;font-weight: bold;"
                         v-on:click="onBreakTime('end')">休憩終了
              </vs-button>
            </vs-col>
          </vs-col>
        </vs-row>
        <vs-row v-if="hrTimeCardDetail != null"  vs-w="12" class="vs-xs-12 my-2">
          <vs-row v-for="index in 5" :key="index" class="mt-1 ml-4">
            <span v-if="hrTimeCardDetail[`break${index}_start_time`] && hrTimeCardDetail[`break${index}_end_time`]">
              休憩{{index}}回: ({{ getBreakTimeFormat(hrTimeCardDetail[`break${index}_start_time`], hrTimeCardDetail[`break${index}_end_time`]) }})
            </span>
          </vs-row>
        </vs-row>
        <vs-row class="mt-5" vs-type="flex" vs-justify="center">
          <vs-col vs-justify="center" style="width: -1px">
            <vs-button style="color:#fff;min-height: 5em;min-width: 10em;margin: 0;" color="warning" v-on:click="contactMail">勤怠連絡</vs-button>
          </vs-col>
        </vs-row>
        <vs-row class="mt-1">
          <vs-col vs-w="12" class="vs-xs-12 my-2" vs-justify="flex-end">
            <h4>休暇申請</h4>
            <vs-col vs-w="12" class="pt-1 bottom_but" vs-type="flex" vs-justify="space-between">
              <vs-button vs-w="4" :disabled="!paidStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                         color="#dddddd" style="color:black;border-color:#ffffff;" v-on:click="onPaid">有給
              </vs-button>
              <vs-button vs-w="4" :disabled="!specialHolidayStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                         color="#dddddd" style="color:black;border-color:#ffffff;"
                         v-on:click="onSpecialHoliday">特休
              </vs-button>
              <vs-button vs-w="4" :disabled="!substituteHolidayStatus"
                         class="square px-0" color="#dddddd" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                         style="color:black;border-color:#ffffff;" v-on:click="onSubstituteHoliday">代休
              </vs-button>
            </vs-col>
          </vs-col>
        </vs-row>

        <vs-row class="mt-1">
          <vs-col vs-w="12" class="vs-xs-12 bottom_but" style="width:100%;" vs-type="flex" vs-justify="space-between">
            <vs-button :disabled="!halfPaidStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd"
                       style="color:black;border-color:#ffffff" v-on:click="onHalfPaid">有給（半休）
            </vs-button>
            <vs-button :disabled="!halfSpecialHolidayStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                       color="#dddddd" style="color:black;border-color:#ffffff" v-on:click="onHalfSpecialHoliday">特休（半休）
            </vs-button>
            <vs-button :disabled="!halfSubstituteHolidayStatus" class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']"
                       color="#dddddd" style="color:black;border-color:#ffffff" v-on:click="onHalfSubstituteHoliday">
              代休（半休）
            </vs-button>
          </vs-col>
        </vs-row>
         <vs-row class="mt-1">

          <vs-row class="inline-block mt-12">
            <h4> 作業内容</h4>
            <vs-row class="block-daily-content vs-textarea">
              <vs-textarea rows="4" v-model="work_detail" v-validate="'max:500'" name="content"
                           style="margin-bottom: 0"/>
            </vs-row>
            <vs-col vs-lg="12" vs-sm="12" vs-xs="12" vs-type="flex" vs-align="flex-end" vs-justify="flex-end"
                    class="mt-2">
            </vs-col>
          </vs-row>
        </vs-row>
        <vs-row class="mt-1">

          <vs-row class="inline-block mt-12">
            <h4>備考 </h4>
            <vs-row class="block-daily-content vs-textarea">
              <vs-textarea rows="1" v-model="daily_report" v-validate="'max:500'" name="content"
                           style="margin-bottom: 0"/>
            </vs-row>
            <vs-col vs-lg="12" vs-sm="12" vs-xs="12" vs-type="flex" vs-align="flex-end" vs-justify="flex-end"
                    class="mt-2">
            </vs-col>
          </vs-row>
        </vs-row>


        <vs-row v-if="!isMobile" class="mt-1 vs-xs-12" vs-w="12" style="width:100%;">
          <div class="pt-2 vs-xs-12" vs-w="12" v-bind:class="[isMobile ? 'f-btn-bot-group-mb' : 'flex justify-center']" style="width:100%;">
            <vs-button class="square mb-2" color="primary"
                       @click="$router.push('/hr/daily_report')">日報画面へ
            </vs-button>
            <vs-button class="square mb-2" color="primary"
                       @click="$router.push('/hr/work-detail/'+currentMonth)">勤務詳細画面へ
            </vs-button>
            <vs-button class="square mb-2" color="primary"
                       @click="$router.push('/hr/work_list')">勤務一覧画面へ
            </vs-button>
          </div>
        </vs-row>
      </vs-col>
      </div>
    </vs-card>
    <vs-col>
      <HrMailSend :opened="selectMailFlg" @changeSelectMailFlg="changeSelectMailFlg"/>
    </vs-col>
    </div>
</template>


<script>
import {mapActions} from "vuex";
import HrMailSend from "../../../components/hr/hrMailSend";

export default {
  components: {
    HrMailSend
  },

  data() {
    return {
      interval: null,
      currentTime: '',
      start: '',
      daily_id: '',
      shift1_start_time: '',
      shift1_end_time: '',
      shift2_start_time: '',
      shift2_end_time: '',
      shift3_start_time: '',
      shift3_end_time: '',
      end: '',
      vacationState: '',
      status: '未承認',
      startWorkStatus: true,
      leaveWorkStatus: false,
      absentWorkStatus: true,
      earlyLeaveWorkStatus: false,
      paidStatus: true,
      specialHolidayStatus: true,
      substituteHolidayStatus: true,
      halfPaidStatus: true,
      halfSpecialHolidayStatus: true,
      halfSubstituteHolidayStatus: true,
      startBreakTimeStatus: false,
      endBreakTimeStatus: false,
      breakNumber: 1,
      hrInfo: {},
      hrTimeCardDetail: {},
      backgroundColor: '',
      currentMonth: '',
      reportDate: '',
      isChangeReportDate: false,
      daily_report: '',
      work_detail: '',
      oneTimeStartStatus: true,
      twoTimeStartStatus: true,
      threeTimeStartStatus: true,
      fourTimeStartStatus: false,
      atime:false,
      btime:false,
      ctime:false,
      isMobile:false,
      holiday_work_flg: false,
      selectMailFlg: false,
    }
  },

  computed: {

  },

  methods: {
    ...mapActions({
      getHrTimeCardDetail: "hr/getHrTimeCardDetail",
      getWorkTime: "user/getWorkTime",
      getWorkDetailByTimecard: "hr/getWorkDetailByTimecard",
      createWorkDetailByTimecard: "hr/createWorkDetailByTimecard",
      updateWorkDetailByTimecard: "hr/updateWorkDetailByTimecard",
      registerNewTimeCardDetail: "hr/registerNewTimeCardDetail",
      updateLeaveWork: "hr/updateLeaveWork",
      updateBreakWork: "hr/updateBreakWork",
      getHoursTime:'user/getHoursTime',
    }),

    async startWork() {
      this.startWorkStatus = false;
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "startWork"});
      this.start = this.$moment(timeCardDetail.work_start_time).format('HH:mm');
      if (timeCardDetail) {
        if (timeCardDetail.late_flg == 1) {
          this.vacationState = '遅刻';
        }
        if (timeCardDetail.paid_vacation_flg && timeCardDetail.paid_vacation_flg === 2) {
          this.disableVacation();
          this.vacationState = '有給（半休）';
        } else if (timeCardDetail.sp_vacation_flg && timeCardDetail.sp_vacation_flg === 2) {
          this.disableVacation();
          this.vacationState = '特休（半休）';
        } else if (timeCardDetail.day_off_flg && timeCardDetail.day_off_flg === 2) {
          this.disableVacation();
          this.vacationState = '代休（半休）';
        }
        this.leaveWorkStatus = true;
        this.getDailStatus = true;
        this.absentWorkStatus = false;
        this.earlyLeaveWorkStatus = true;
        this.paidStatus = false;
        this.specialHolidayStatus = false;
        this.substituteHolidayStatus = false;
      } else {
        this.startWorkStatus = true;
      }
    },
    async leaveWork() {
      let currentTime = this.$moment().format('YYYY-MM-DD');
      this.reportDate = currentTime;
      this.hrTimeCardDetail = await this.getHrTimeCardDetail();
      this.getWorkTime = await this.getWorkTime();
      let timeCard = {
        "id": this.hrTimeCardDetail.id,
      }
      let timeCardDetail = await this.updateLeaveWork(timeCard);
      if (timeCardDetail) {
        let startDate = this.$moment(this.hrTimeCardDetail.work_start_time).format('MM/DD');
        let endDate = this.$moment(timeCardDetail.work_end_time).format('MM/DD');
        if (startDate < endDate) {
          this.start = this.$moment(this.hrTimeCardDetail.work_start_time).format('HH:mm');
        } else {
          this.start = this.$moment(this.hrTimeCardDetail.work_start_time).format('HH:mm');
        }
        if (this.hrTimeCardDetail.late_flg == 1) {
          this.vacationState = '遅刻';
        }
        this.end = this.$moment(timeCardDetail.work_end_time).format('HH:mm')?this.$moment(timeCardDetail.work_end_time).format('HH:mm'):null;
        if (timeCardDetail.earlyleave_flg == 1) {
          this.vacationState = this.vacationState + ' 早退';
        }
        if (timeCardDetail.paid_vacation_flg === 2) {
          this.vacationState = '有給（半休）';
        } else if (timeCardDetail.sp_vacation_flg === 2) {
          this.vacationState = '特休（半休）';
        } else if (timeCardDetail.day_off_flg === 2) {
          this.vacationState = '代休（半休）';
        }

        this.disableAllButton();
        this.leaveWorkStatus = false;
      }
      if (this.hrTimeCardDetail.approval_state == 1) {
        this.status = '承認済';
      } else if (this.hrTimeCardDetail.approval_state == 2) {
        this.status = '修正依頼';
      } else {
        this.status = '未承認';
      }
    },

    async absentWork() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "absentWork","memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      if (timeCardDetail) {
        this.disableAllButton();
        this.vacationState = '欠勤';
      }
    },

    async onPaid() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onPaid","memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      if (timeCardDetail) {
        this.disableAllButton();
        this.vacationState = '有給';
        this.paidStatus=this.specialHolidayStatus=this.substituteHolidayStatus=false;
        this.fourTimeStartStatus=this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      }
    },

    async onSpecialHoliday() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onSpecialHoliday","memo":this.daily_report, "work_detail":this.work_detail});
      if (timeCardDetail) {
        this.disableAllButton();
        this.vacationState = '特休';
        this.paidStatus=this.specialHolidayStatus=this.substituteHolidayStatus=false;
        this.fourTimeStartStatus=this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      }
    },

    async onSubstituteHoliday() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onSubstituteHoliday","memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      if (timeCardDetail) {
        this.disableAllButton();
        this.vacationState = '代休';
        this.paidStatus=this.specialHolidayStatus=this.substituteHolidayStatus=false;
        this.fourTimeStartStatus=this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      }
    },

    async onHalfPaid() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onHalfPaid","memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      if (timeCardDetail) {
        this.disableVacation();
        this.vacationState = '有給（半休）';
        this.halfPaidStatus=this.halfSpecialHolidayStatus=this.halfSubstituteHolidayStatus=false;
      }
    },

    async onHalfSpecialHoliday() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onHalfSpecialHoliday","memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      if (timeCardDetail) {
        this.disableVacation();
        this.vacationState = '特休（半休）';
        this.halfPaidStatus=this.halfSpecialHolidayStatus=this.halfSubstituteHolidayStatus=false;
      }
    },

    async onHalfSubstituteHoliday() {
      let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onHalfSubstituteHoliday","memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      if (timeCardDetail) {
        this.disableVacation();
        this.vacationState = '代休（半休）';
        this.halfPaidStatus=this.halfSpecialHolidayStatus=this.halfSubstituteHolidayStatus=false;
      }
    },
    async onBreakTime(type){
      let timeCardDetail = await this.updateBreakWork({"id": this.hrTimeCardDetail.id,"type":type,"break_number": this.breakNumber ,"memo":this.daily_report, "work_detail":this.work_detail});
        if(timeCardDetail){
          if(this.breakNumber <= 5){
            this.startBreakTimeStatus = type == 'end';
            this.endBreakTimeStatus = type == 'start';
              if(type == 'end'){
                if(this.breakNumber < 5){
                  this.breakNumber++;
                }else{
                  this.startBreakTimeStatus = false;
                  this.endBreakTimeStatus = false;
                }
              }
          }
          this.hrTimeCardDetail = await this.getHrTimeCardDetail();
        }
    },
    disableVacation() {
      this.absentWorkStatus = false;
      this.earlyLeaveWorkStatus = false;
      this.paidStatus = false;
      this.specialHolidayStatus = false;
      this.substituteHolidayStatus = false;
      this.halfPaidStatus = false;
      this.halfSpecialHolidayStatus = false;
      this.halfSubstituteHolidayStatus = false;
    },

    disableAllButton() {
      this.startWorkStatus = false;
      this.leaveWorkStatus = false;
      this.absentWorkStatus = false;
      this.earlyLeaveWorkStatus = false;
      this.paidStatus = false;
      this.specialHolidayStatus = false;
      this.substituteHolidayStatus = false;
      this.halfPaidStatus = false;
      this.halfSpecialHolidayStatus = false;
      this.halfSubstituteHolidayStatus = false;
      this.startBreakTimeStatus = this.endBreakTimeStatus = false;

    },

    displayTime() {
      this.currentTime = this.$moment(new Date()).format('HH:mm');
    },
    UpTime: async function (kbn) {
      //if
      await this.registerNewTimeCardDetail({"insertType": "startWork","shift_work_kbn":kbn,"memo":this.daily_report, "work_detail":this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
      let askstarttime= this.hrTimeCardDetail = await this.getHrTimeCardDetail();
      this.oneTimeStartStatus = this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      this.start = this.$moment(askstarttime.work_start_time).format('HH:mm');
      this.paidStatus=this.specialHolidayStatus=this.substituteHolidayStatus=false;
      this.fourTimeStartStatus=true;
      this.startBreakTimeStatus = true;
    },
    OverTime: async function () {

      this.oneTimeStartStatus = false;
      this.twoTimeStartStatus = false;
      this.threeTimeStartStatus = false;
      let endworktime = await this.getHrTimeCardDetail();
        endworktime.memo=this.daily_report;
        endworktime.work_detail=this.work_detail;
        endworktime.holiday_work_flg=this.holiday_work_flg;
        await this.updateLeaveWork(endworktime);
        let askendtime=await this.getHrTimeCardDetail();
        let last_endtime=askendtime.work_end_time;
        this.end = this.$moment(last_endtime).format('HH:mm');
        this.start = this.$moment(askendtime.work_start_time).format('HH:mm');
        this.halfPaidStatus=this.halfSpecialHolidayStatus=this.halfSubstituteHolidayStatus=false;
        this.paidStatus=this.specialHolidayStatus=this.substituteHolidayStatus=false;
        this.fourTimeStartStatus = false;
        this.disableAllButton();


    },
    contactMail(){
      this.selectMailFlg = true;
    },
    async changeSelectMailFlg() {
      this.selectMailFlg = false;
    },
    getBreakTimeFormat(startTime, endTime){
      return (this.$moment(this.hrTimeCardDetail.work_date, 'YYYYMMDD').format('MMDD') < this.$moment(startTime).format('MMDD') ? 
                this.$moment(startTime).format('HH:mm (MM/DD)')  :  this.$moment(startTime).format('HH:mm')) +
                '-' + (this.$moment(this.hrTimeCardDetail.work_date, 'YYYYMMDD').format('MMDD') < this.$moment(endTime).format('MMDD') ? 
                this.$moment(endTime).format('HH:mm (MM/DD)') :  this.$moment(endTime).format('HH:mm'));
    }
  },

  watch: {},

  async created() {
    if( /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(navigator.userAgent)) {
      this.isMobile = true;
    }
    this.interval = setInterval(this.displayTime, 1000);
    this.currentMonth = this.$moment(new Date()).format('YYYYMM');
    // eslint-disable-next-line no-undef
    const branding = JSON.parse(getLS('branding'));
    this.backgroundColor = branding && branding.background_color ? branding.background_color : "0984e3";
    this.hrTimeCardDetail=await this.getHrTimeCardDetail();
    if(this.hrTimeCardDetail != null){
      this.hrTimeCardDetail.work_start_time ? this.oneTimeStartStatus=false :this.oneTimeStartStatus=true;
      this.hrTimeCardDetail.work_start_time ? this.twoTimeStartStatus=false :this.twoTimeStartStatus=true;
      this.hrTimeCardDetail.work_start_time ? this.threeTimeStartStatus=false :this.threeTimeStartStatus=true;

      let breakStartStatus = false;
      let breakEndStatus = false;
      for(var i = 1; i <= 5; i++){
        if(this.hrTimeCardDetail[`break${i}_end_time`] != null){
            breakStartStatus = true;
            breakEndStatus = false;
            if(this.breakNumber >= 5){
                breakStartStatus = false;
                breakEndStatus = false;
            }else{
                this.breakNumber++;
            }
        }else{
            if(this.hrTimeCardDetail[`break${i}_start_time`] != null){
                breakStartStatus = false;
                breakEndStatus = true;
            }else{
                breakStartStatus = true;
                breakEndStatus = false;
            }
            break;
        }
      }
      this.startBreakTimeStatus = breakStartStatus;
      this.endBreakTimeStatus = breakEndStatus;
      if(this.hrTimeCardDetail.work_end_time != null){
        this.substituteHolidayStatus=this.specialHolidayStatus=this.paidStatus=this.halfSubstituteHolidayStatus=this.halfSpecialHolidayStatus=this.halfPaidStatus=false;
        this.fourTimeStartStatus = this.startBreakTimeStatus = this.endBreakTimeStatus = false;
      }
      if(this.hrTimeCardDetail.work_start_time != null && this.hrTimeCardDetail.work_end_time == null){
        this.fourTimeStartStatus=true;
      }
    }
    //カスタム時間の取得
    this.getWorkTime = await this.getWorkTime();
    //if work_time != null
    if(this.getWorkTime.info?.working_hours_id >= 1
        &&this.getWorkTime.info?.shift1_start_time== null
        &&this.getWorkTime.info?.shift2_start_time== null
        &&this.getWorkTime.info?.shift3_start_time== null
    ){
      //hr_work_time//&& this.hrTimeCardDetail.data.work_start_time == null
      let hour_work=await this.getHoursTime(this.getWorkTime.info.working_hours_id);
      if(hour_work.info != null){
        if(hour_work.info.shift1_start_time !=null && hour_work.info.shift1_end_time != null){
          this.atime=true;
          this.shift1_start_time=hour_work.info.shift1_start_time.slice(0, 5);
          this.shift1_end_time=hour_work.info.shift1_end_time.slice(0, 5);
          this.oneTimeStartStatus=true;
        }
        if(hour_work.info.shift2_start_time !=null && hour_work.info.shift2_end_time != null){
          this.btime=true;
          this.shift2_start_time=hour_work.info.shift2_start_time.slice(0, 5);
          this.shift2_end_time=hour_work.info.shift2_end_time.slice(0, 5);
          this.twoTimeStartStatus=true;
        }
        if(hour_work.info.shift3_start_time !=null && hour_work.info.shift3_end_time != null){
          this.ctime=true;
          this.shift3_start_time=hour_work.info.shift3_start_time.slice(0, 5);
          this.shift3_end_time=hour_work.info.shift3_end_time.slice(0, 5);
          this.threeTimeStartStatus=true;
        }
        if(this.hrTimeCardDetail != null){
          this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
        }
      }
    }

    if (this.getWorkTime.info) {
      if (this.getWorkTime.info.shift1_start_time) {
        this.shift1_start_time = this.getWorkTime.info.shift1_start_time.slice(0, 5);
        this.atime=this.getWorkTime.info.shift1_start_time == null ? false :true;
      }
      if (this.getWorkTime.info.shift1_end_time) {
        this.shift1_end_time = this.getWorkTime.info.shift1_end_time.slice(0, 5);
      }
      if (this.getWorkTime.info.shift2_start_time) {
        this.shift2_start_time = this.getWorkTime.info.shift2_start_time.slice(0, 5);
        this.btime=this.getWorkTime.info.shift2_start_time == null ? false :true;
      }
      if (this.getWorkTime.info.shift2_end_time) {
        this.shift2_end_time = this.getWorkTime.info.shift2_end_time.slice(0, 5);
      }
      if (this.getWorkTime.info.shift3_start_time) {
        this.shift3_start_time = this.getWorkTime.info.shift3_start_time.slice(0, 5);
        this.ctime=this.getWorkTime.info.shift3_start_time == null ? false :true;
      }
      if (this.getWorkTime.info.shift3_end_time) {
        this.shift3_end_time = this.getWorkTime.info.shift3_end_time.slice(0, 5);
      }
    }
    //timeof start
    this.daily_report = this.hrTimeCardDetail ? this.hrTimeCardDetail.memo : '';
    this.work_detail = this.hrTimeCardDetail ? this.hrTimeCardDetail.work_detail : '';
    this.holiday_work_flg = this.hrTimeCardDetail ? !!this.hrTimeCardDetail.holiday_work_flg : this.holiday_work_flg;
    this.daily_id = this.hrTimeCardDetail ? this.hrTimeCardDetail.id : '';
    if (this.hrTimeCardDetail) {
      if (this.hrTimeCardDetail.absent_flg === 1) {
        this.disableAllButton();
        this.vacationState = '欠勤';
      } else if (this.hrTimeCardDetail.paid_vacation_flg === 1) {
        this.disableAllButton();
        this.vacationState = '有給';
        this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      } else if (this.hrTimeCardDetail.paid_vacation_flg === 2) {
        this.disableVacation();
        this.vacationState = '有給（半休）';
      }
      if (this.hrTimeCardDetail.sp_vacation_flg === 1) {
        this.disableAllButton();
        this.vacationState = '特休';
        this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      } else if (this.hrTimeCardDetail.sp_vacation_flg === 2) {
        this.disableVacation();
        this.vacationState = '特休（半休）';
      }
      if (this.hrTimeCardDetail.day_off_flg === 1) {
        this.disableAllButton();
        this.vacationState = '代休';
        this.oneTimeStartStatus=this.twoTimeStartStatus=this.threeTimeStartStatus=false;
      } else if (this.hrTimeCardDetail.day_off_flg === 2) {
        this.disableVacation();
        this.vacationState = '代休（半休）';
      }
      let hr_time_work=this.hrTimeCardDetail.work_start_time?this.hrTimeCardDetail.work_start_time:null;
      let hr_time_end_work=this.hrTimeCardDetail.work_end_time?this.hrTimeCardDetail.work_end_time:null;
      if (hr_time_work != null) {
        //勤務時間を設定し、勤務時間が空の場合、最大時間は現在まで、false、勤務時間に基づいて計算します。
        if (hr_time_end_work != null) {
          this.end=this.$moment(hr_time_end_work).format('HH:mm')?this.$moment(hr_time_end_work).format('HH:mm'):null;
        }
        this.start = this.$moment(hr_time_work).format('HH:mm');
        this.startWorkStatus = false;
        this.leaveWorkStatus = true;
        this.absentWorkStatus = false;
        this.earlyLeaveWorkStatus = false;
        this.paidStatus = false;
        this.specialHolidayStatus = false;
        this.substituteHolidayStatus = false;
        if (this.hrTimeCardDetail.late_flg) {
          this.vacationState = '遅刻';
        }
      }
      if (this.hrTimeCardDetail.work_end_time) {
        this.end = this.$moment(this.hrTimeCardDetail.work_end_time).format('HH:mm')?this.$moment(this.hrTimeCardDetail.work_end_time).format('HH:mm'):null;
        if (this.hrTimeCardDetail.earlyleave_flg) {
          this.vacationState = this.vacationState + ' 早退';
        }
        this.leaveWorkStatus = false;
      }

      if (this.hrTimeCardDetail.approval_state == 1) {
        this.status = '承認済';
      } else if (this.hrTimeCardDetail.approval_state == 2) {
        this.status = '修正依頼';
      } else {
        this.status = '未承認';
      }
    }
  },
  destroyed() {
    clearInterval(this.interval);
  },
}

</script>

<style scoped>

.break {
  border-radius: 4px;
  height: 15px;
  width: 100%;
}

.title-underline {
  display: inline-block;
}


.border-first-row {
  border-top: 1px solid #cdcdcd;
  border-bottom: 1px solid #cdcdcd;
  border-left: 1px solid #cdcdcd;
}
.bottom_div{
  border-top:1px solid #cdcdcd;
  background:#c8ebf7;
}

.border-second-row {
  border-bottom: 1px solid #cdcdcd;
  border-left: 1px solid #cdcdcd;
}

@media screen and (min-width: 601px) {
  .border-first-row:nth-child(3) {
    border-right: 1px solid #cdcdcd;
  }

  .border-second-row:nth-child(3) {
    border-bottom: none;
  }
}

@media screen and (max-width: 600px) {
  .border-first-row:nth-child(2) {
    border-top: none;
    border-bottom: none;
  }

  .border-first-row, .border-second-row:nth-child(1), .border-second-row:nth-child(2) {
    border-right: 1px solid #cdcdcd;
  }

  .border-second-row:nth-child(3) {
    border-left: none;
    border-bottom: none;
  }
}

.label-right {
  border-right: 1px solid #cdcdcd;
  background-color: #c8ebf7;
  color: black;
}

.up_time_but button {
  min-height: 5em;
  min-width: 10em;
}

.right_but {
  position: relative;
  top: 33%;
}

.foot_but button {
  margin-left: 0% !important;
}
.left_margin{
  margin: 2em auto auto 45% !important;
}
textarea {
  /* min-height: 200px; */
  width: 100%;
  border-radius: 5px;
  border: solid 1px #107fcd;
  padding: 20px;
}

.remarks {
  margin: 0em 2% auto auto;
  width: 100px;
  height: 30px;
}
.border-cell{
  width:97%;
}
.right_border{
  border-right: 1px solid #cdcdcd;
}
.f-btn-mb{
  flex: 1;
  margin: 0.2rem 0.4rem;
}
.f-btn{
  flex: 1;
  margin: 0.5rem 1rem;
}
.f-group-btn-mb div button{
  margin: 0.8rem 1.2rem !important;
}
.f-btn-bot-group-mb{
  display: flex;
  flex-direction: column;
   
}
.f-btn-bot-group-mb button{
  margin: 0.2rem 0;
  flex: 1
}
</style>
