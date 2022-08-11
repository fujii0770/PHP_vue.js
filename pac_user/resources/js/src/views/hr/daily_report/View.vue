<template>
    <div id="daily-report">
        <vx-card>
            <div class="m-3 border-cell p-5">
                <vs-row class="inline-block mt-3">
                    <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="6" class="mb-3">
                        <div class="w-full">
                            <label for="date_report" class="vs-input--label">報告日</label>
                            <div class="vs-con-input block-calendar">
                                <flat-pickr class="w-full" :disabled="true" v-model="reportDate" id="date_report" :config="configDate"></flat-pickr>
                            </div>
                        </div>
                    </vs-col>
                    <vs-col vs-type="flex" vs-lg="2" vs-sm="3" vs-xs="6" class="mb-3 pl-2">
                      <div class="w-full">
                        <label for="reporter" class="vs-input--label">報告者</label>
                        <div class="vs-con-input block-calendar">
                          <input class="vs-inputx vs-input--input normal hasValue" readonly v-model="reportUsername" id="reporter"></input>
                        </div>
                      </div>
                    </vs-col>
                    <vs-col vs-lg="8" vs-sm="6" vs-xs="12" vs-type="flex" vs-align="flex-end" vs-justify="flex-end" class="mt-8" >
                        <vs-button :disabled="reportContent.length == 0 || reportContent.length > 512" class="square regiter-btn" color="primary" v-on:click="onReport()">登録</vs-button>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">

                </vs-row>

                <vs-row vs-type="flex block-break">
                        <div class="break" :style="{'background-color': '#'+backgroundColor}"></div>
                </vs-row>
                <vs-row>
                    <span class="vs-input--label">報告内容</span>
                </vs-row>
                <vs-row class="block-daily-content">
                    <vs-textarea class="daily-content" placeholder="" rows="10" v-model="reportContent" />
                </vs-row>
                <span v-if="reportContent.length > 512" style="color:red;">
                  {{ reportContentError }}
                </span>
                <span v-if="reportContent.length == 0" style="color:red;">
                  報告内容は必須です。
                </span>
            </div>
        </vx-card>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxCard from '../../../components/vx-card/VxCard.vue';
import flatPickr from 'vue-flatpickr-component';

import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import router from "../../../router";

export default  {
    components: {
        VxCard,
        flatPickr,
    },
    name: "DailyReport",
    props: [],

    data () {
        return {
            backgroundColor: '',
            reportContentError: '報告内容は512文字以下で入力してください。',
            reportContent: '',
            reportDate: '',
            reportId: '',
            reportUsername:'',
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                maxDate: ''
            },
        }
    },
    async created () {
        const branding = JSON.parse(getLS('branding'));
        let NAVBAR_BACKGROUND = branding && branding.background_color ? branding.background_color : "0984e3";
        this.backgroundColor = NAVBAR_BACKGROUND;
        this.reportId = this.$route.params.id;
        let params = {
          id: this.reportId
        }
        const data = await this.getUserDailyReport(params);
        if (data) {
          this.reportId = data.id;
          this.reportContent = data.daily_report;
          this.reportDate = data.report_date;
          this.reportUsername = data.user_name;
        }else{
          await router.push({ path: `/hr/daily_report_list` });
        }
    },

    computed: {

    },
    methods: {
        ...mapActions({
            getUserDailyReport: "dailyReport/getUserDailyReport",
            updateUserDailyReport: "dailyReport/updateUserDailyReport",
        }),
        onReport: function() {
          let params = {
            id: this.reportId,
            daily_report : this.reportContent,
          }
          this.updateUserDailyReport(params);
        },
    },
}
</script>

<style scoped>
.border-cell {
    border: 1px solid #cdcdcd;
}
.regiter-btn {
    width: 7em;
}
.block-break {
    margin-top: 1em;
    margin-bottom: 2em;
    padding: 3em 0 1em;
}
.break {
    border-radius: 4px;
    height: 3em;
    margin-top: 1em;
    margin-bottom: 2em;
}
.block-calendar {
    margin-top: 0.5em;
}
.daily-content {
    width: 100%;
}
.block-daily-content {
    margin-top: 1em;
}
.inline-block {
    display: inline;
}


</style>
