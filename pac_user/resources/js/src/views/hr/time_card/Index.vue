<template>
    <div id="time-card">
        <vs-card>
            <div class="m-3 border-cell p-5">
                <vs-row class="mt-3" vs-type="flex" vs-justify="center">
                  <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="flex-start">
                    現在時刻: {{currentTime}}
                  </vs-col>
                </vs-row>
                <vs-row class="pt-5" vs-type="flex" vs-justify="center">
                    <vs-col vs-w="3" class="pr-4 border-cell vs-xs-6 vs-sm-6 vs-md-4">
                        <vs-row style="height: 100%">
                            <vs-col vs-w="4" class="vs-sm-6 text-center p-3 label-right">出勤</vs-col>
                            <vs-col vs-w="8" class="vs-sm-6 text-right p-3" style="color: black">{{start}}</vs-col>
                        </vs-row>
                    </vs-col>
                    <vs-col vs-w="3" class="pr-4 border-cell vs-xs-6 vs-sm-6 vs-md-4">
                        <vs-row style="height: 100%">
                            <vs-col vs-w="4" class="vs-sm-6 text-center p-3 label-right">退勤</vs-col>
                            <vs-col vs-w="8" class="vs-sm-6 text-right p-3" style="color: black">{{end}}</vs-col>
                        </vs-row>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-5"  vs-type="flex" vs-justify="center">
                    <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="flex-start">
                        <vs-checkbox v-model="holiday_work_flg">休日出勤の場合はチェック</vs-checkbox>
                    </vs-col>
                </vs-row>
                <vs-row vs-type="flex" vs-justify="center">
                  <vs-col vs-w="3" class="vs-xs-6  vs-sm-6 vs-md-4 p-4 lg:pt-10 lg:pb-10 lg:pl-20 lg:pr-20" vs-align="center">
                    <vs-button :disabled="!startWorkStatus" class="square w-100 mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" size="large" style="color:#fff; height:100px" color="#22AD38" v-on:click="startWork">出勤</vs-button>
                  </vs-col>
                  <vs-col vs-w="3" class="vs-xs-6  vs-sm-6 vs-md-4 p-4 lg:pt-10 lg:pb-10 lg:pl-20 lg:pr-20" vs-align="center">
                      <vs-button :disabled="!leaveWorkStatus" class="square w-100 mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" size="large" style="color:#fff; height:100px" color="#22AD38" v-on:click="leaveWork">退勤</vs-button>
                  </vs-col>
                </vs-row>
                <vs-row vs-type="flex" vs-justify="center" class="mt-1">
                    <vs-col vs-w="6">
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
                <vs-row vs-type="flex" vs-justify="center" class="mt-1">
                    <vs-row v-if="hrTimeCardDetail != null"  vs-w="6" class="vs-xs-6 my-2">
                        <vs-row v-for="index in 5" :key="index" class="mt-1 ml-4">
                            <span v-if="hrTimeCardDetail[`break${index}_start_time`] && hrTimeCardDetail[`break${index}_end_time`]">
                                休憩{{index}}回: ({{ getBreakTimeFormat(hrTimeCardDetail[`break${index}_start_time`], hrTimeCardDetail[`break${index}_end_time`]) }})
                            </span>
                        </vs-row>
                    </vs-row>
                </vs-row>
                <vs-row vs-type="flex" vs-justify="center">
                  <vs-col vs-w="3" class="vs-xs-6  vs-sm-6 vs-md-4 p-4 lg:pt-10 lg:pb-10 lg:pl-20 lg:pr-20" vs-align="center">
                    <vs-button class="square w-100 mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0 warning" size="large" style="color:#fff; height:80px" color="warning" v-on:click="contactMail">勤怠連絡</vs-button>
                  </vs-col>
                </vs-row>
                <vs-row vs-type="flex" vs-justify="center">
                  <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="flex-start">
                    休暇等記録
                  </vs-col>
                </vs-row>
                <vs-row vs-type="flex" vs-justify="center">
                  <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="space-between">
                      <vs-button :disabled="!paidStatus"  class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd" style="color:black;border-color:#ffffff;" v-on:click="onPaid">有給</vs-button>
                      <vs-button :disabled="!specialHolidayStatus"  class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd" style="color:black;border-color:#ffffff;" v-on:click="onSpecialHoliday">特休</vs-button>
                      <vs-button :disabled="!substituteHolidayStatus"  class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd" style="color:black;border-color:#ffffff;" v-on:click="onSubstituteHoliday">代休</vs-button>
                  </vs-col>
                </vs-row>
                <vs-row class="mt-1" vs-type="flex" vs-justify="center">
                    <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="space-between">
                            <vs-button :disabled="!halfPaidStatus"  class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd" style="color:black;border-color:#ffffff" v-on:click="onHalfPaid">有給（半休）</vs-button>
                            <vs-button :disabled="!halfSpecialHolidayStatus"  class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd" style="color:black;border-color:#ffffff" v-on:click="onHalfSpecialHoliday">特休（半休）</vs-button>
                            <vs-button :disabled="!halfSubstituteHolidayStatus"  class="square px-0" v-bind:class="[isMobile ? 'f-btn-mb' : 'f-btn']" color="#dddddd" style="color:black;border-color:#ffffff" v-on:click="onHalfSubstituteHoliday">代休（半休）</vs-button>
                    </vs-col>
                </vs-row>
              <vs-row vs-type="flex" vs-justify="center">
                <vs-col vs-w="6" class="pt-10 vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="flex-start">
                  作業内容
                </vs-col>
              </vs-row>
              <vs-row vs-type="flex" vs-justify="center">
                <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="center">
                  <vs-textarea rows="4" class="mt-4 w-100 mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" v-model="work_detail" />
                </vs-col>
              </vs-row>
              <vs-row vs-type="flex" vs-justify="center">
                <vs-col vs-w="6" class="pt-10 vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="flex-start">
                  備考
                </vs-col>
              </vs-row>
              <vs-row vs-type="flex" vs-justify="center">
                <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="center">
                  <vs-textarea rows="1" class="mt-4 w-100 mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" v-model="memo" />
                </vs-col>
              </vs-row>
              <vs-row v-if="!isMobile" vs-type="flex" vs-justify="center">
                <vs-col vs-w="6" class="vs-xs-12 vs-sm-12 vs-md-8" vs-type="flex" vs-justify="center">
                  <div class="pt-10 w-full" v-bind:class="[isMobile ? 'f-btn-bot-group-mb' : 'justify-center flex']" >
                    <vs-button class="square mb-2" color="primary" @click="$router.push('/hr/daily_report')">日報画面へ</vs-button>
                    <vs-button class="square mb-2" color="primary" @click="$router.push('/hr/work-detail/'+currentMonth)">勤務詳細画面へ</vs-button>
                    <vs-button class="square mb-2" color="primary" @click="$router.push('/hr/work_list')">勤務一覧画面へ</vs-button>
                  </div>
                </vs-col>
              </vs-row>
            </div>
        </vs-card>
        <vs-col>
          <HrMailSend :opened="selectMailFlg" @changeSelectMailFlg="changeSelectMailFlg"/>
        </vs-col>
    </div>
</template>

<script>
import { mapActions } from "vuex";
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
            memo: '',
            selectMailFlg: false,
            isMobile: false,
            work_detail:'',
            holiday_work_flg: false
        }
    },

    computed: {
    },

    methods: {
        ...mapActions({
            getHrTimeCardDetail: "hr/getHrTimeCardDetail",
            registerNewTimeCardDetail: "hr/registerNewTimeCardDetail",
            updateLeaveWork: "hr/updateLeaveWork",
            getWorkTime: "user/getWorkTime",
            getHoursTime:'user/getHoursTime',
            updateBreakWork: "hr/updateBreakWork",

        }),
        async startWork() {
            this.startWorkStatus = false;
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "startWork", "memo": this.memo,"shift_work_kbn":this.work_kbn, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
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
                this.absentWorkStatus = false;
                this.earlyLeaveWorkStatus = true;
                this.paidStatus = false;
                this.specialHolidayStatus = false;
                this.substituteHolidayStatus = false;
            } else {
                this.startWorkStatus = true;
            }
            this.hrTimeCardDetail = await this.getHrTimeCardDetail();
            this.startBreakTimeStatus = true;

        },

        async leaveWork() {
            let timeCard = {
                "id": this.hrTimeCardDetail.id,
                "memo": this.memo,
                "shift_work_kbn": this.hrTimeCardDetail.shift_work_kbn,
                "work_detail": this.work_detail,
                "holiday_work_flg": this.holiday_work_flg | 0
            }
            let timeCardDetail = await this.updateLeaveWork(timeCard);
            if (timeCardDetail) {
                this.hrTimeCardDetail = await this.getHrTimeCardDetail();
                let startDate = this.$moment(this.hrTimeCardDetail.work_start_time).format('MM/DD');
                let endDate = this.$moment(timeCardDetail.work_end_time).format('MM/DD');
                if (startDate < endDate) {
                    this.start = this.$moment(this.hrTimeCardDetail.work_start_time).format('HH:mm (MM/DD)');
                } else {
                    this.start = this.$moment(this.hrTimeCardDetail.work_start_time).format('HH:mm');
                }
                if (this.hrTimeCardDetail.late_flg == 1) {
                    this.vacationState = '遅刻';
                }
                this.end = this.$moment(timeCardDetail.work_end_time).format('HH:mm');
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
        async onBreakTime(type){
            let timeCardDetail = await this.updateBreakWork({"id": this.hrTimeCardDetail.id,"type":type,"break_number": this.breakNumber ,"memo":this.memo, "work_detail":this.work_detail});
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
        async absentWork() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "absentWork", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableAllButton();
                this.vacationState = '欠勤';
            }
        },

        async onPaid() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onPaid", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableAllButton();
                this.vacationState = '有給';
            }
        },

        async onSpecialHoliday() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onSpecialHoliday", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableAllButton();
                this.vacationState = '特休';
            }
        },

        async onSubstituteHoliday() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onSubstituteHoliday", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableAllButton();
                this.vacationState = '代休';
            }
        },

        async onHalfPaid() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onHalfPaid", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableVacation();
                this.vacationState = '有給（半休）';
            }
        },

        async onHalfSpecialHoliday() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onHalfSpecialHoliday", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableVacation();
                this.vacationState = '特休（半休）';
            }
        },

        async onHalfSubstituteHoliday() {
            let timeCardDetail = await this.registerNewTimeCardDetail({"insertType": "onHalfSubstituteHoliday", "memo": this.memo, "work_detail": this.work_detail, "holiday_work_flg":this.holiday_work_flg | 0});
            if (timeCardDetail) {
                this.disableVacation();
                this.vacationState = '代休（半休）';
            }
        },

        contactMail(){
          this.selectMailFlg = true;
        },
        async changeSelectMailFlg() {
          this.selectMailFlg = false;
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
            this.startBreakTimeStatus = false;
            this.endBreakTimeStatus = false;
        },

        displayTime() {
            this.currentTime = this.$moment(new Date()).format('HH:mm');
        },
        getBreakTimeFormat(startTime, endTime){
            return (this.$moment(this.hrTimeCardDetail.work_date, 'YYYYMMDD').format('MMDD') < this.$moment(startTime).format('MMDD') ? 
                    this.$moment(startTime).format('HH:mm (MM/DD)')  :  this.$moment(startTime).format('HH:mm')) +
                    '-' + (this.$moment(this.hrTimeCardDetail.work_date, 'YYYYMMDD').format('MMDD') < this.$moment(endTime).format('MMDD') ? 
                    this.$moment(endTime).format('HH:mm (MM/DD)') :  this.$moment(endTime).format('HH:mm'));
        }
    },

    watch:{
    },

    mounted() {
    },

    async created() {
        if( /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(navigator.userAgent)) {
            this.isMobile = true;
        }
        this.getWorkTime=await this.getWorkTime();
        if(this.getWorkTime.info?.working_hours_id >= 1){
          let hour_work=await this.getHoursTime(this.getWorkTime.info.working_hours_id);
          if(hour_work?.info?.work_form_kbn == '2'){
             this.work_kbn=4;
          }
          else{
             this.work_kbn=0;
          }
        }

        this.interval = setInterval(this.displayTime, 1000);
        this.currentMonth = this.$moment(new Date()).format('YYYYMM');

        const branding = JSON.parse(getLS('branding'));
        this.backgroundColor = branding && branding.background_color ? branding.background_color : "0984e3";

        this.hrTimeCardDetail = await this.getHrTimeCardDetail();
        let breakStartStatus = false;
        let breakEndStatus = false;
        if (this.hrTimeCardDetail) {
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

            this.memo = this.hrTimeCardDetail.memo;
            this.work_detail = this.hrTimeCardDetail.work_detail;
            this.holiday_work_flg = this.hrTimeCardDetail ? !!this.hrTimeCardDetail.holiday_work_flg : this.holiday_work_flg;

            if (this.hrTimeCardDetail.absent_flg === 1) {
                this.disableAllButton();
                this.vacationState = '欠勤';
            } else if (this.hrTimeCardDetail.paid_vacation_flg === 1) {
                this.disableAllButton();
                this.vacationState = '有給';
            } else if (this.hrTimeCardDetail.paid_vacation_flg === 2) {
                this.disableVacation();
                this.vacationState = '有給（半休）';
            }
            if (this.hrTimeCardDetail.sp_vacation_flg === 1) {
                this.disableAllButton();
                this.vacationState = '特休';
            } else if (this.hrTimeCardDetail.sp_vacation_flg === 2) {
                this.disableVacation();
                this.vacationState = '特休（半休）';
            }
            if (this.hrTimeCardDetail.day_off_flg === 1) {
                this.disableAllButton();
                this.vacationState = '代休';
            } else if (this.hrTimeCardDetail.day_off_flg === 2){
                this.disableVacation();
                this.vacationState = '代休（半休）';
            }
            if (this.hrTimeCardDetail.work_start_time) {
                let currentTime = this.$moment(new Date()).format('MM/DD');
                let startDate = this.$moment(this.hrTimeCardDetail.work_start_time).format('MM/DD');
                if (startDate < currentTime) {
                    this.start = this.$moment(this.hrTimeCardDetail.work_start_time).format('HH:mm (MM/DD)');
                }else {
                    this.start = this.$moment(this.hrTimeCardDetail.work_start_time).format('HH:mm');
                }
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
                this.end = this.$moment(this.hrTimeCardDetail.work_end_time).format('HH:mm');
                if (this.hrTimeCardDetail.earlyleave_flg) {
                    this.vacationState = this.vacationState + ' 早退';
                }
                this.leaveWorkStatus = false;
                this.startBreakTimeStatus = this.endBreakTimeStatus = false;
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

<style>
    .break {
        border-radius: 4px;
        height: 15px;
        width: 100%;
    }
    .title-underline {
        border-top: 1px solid #cdcdcd;
        display: inline-block;
    }
    .border-cell {
        border: 1px solid #cdcdcd;
    }
    .border-first-row {
        border-top: 1px solid #cdcdcd;
        border-bottom: 1px solid #cdcdcd;
        border-left: 1px solid #cdcdcd;
    }
    .border-second-row {
        border-bottom: 1px solid #cdcdcd;
        border-left: 1px solid #cdcdcd;
    }

    @media screen and (min-width: 601px){
        .border-first-row:nth-child(2) {
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
    .vs-card--content {
        font-size: inherit;
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
    .f-btn-mb{
        flex: 1;
        margin: 0.2rem 0.4rem;
    }
    .f-btn{
        flex: 1;
        margin: 0.5rem 1rem;
    }
</style>
