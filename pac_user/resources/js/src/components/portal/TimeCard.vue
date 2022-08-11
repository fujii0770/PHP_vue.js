<template>
    <div id="timeCardDiv" class="comp-portal-favorite">
        <vx-card class="mb-4">
            <HeaderComponent title="タイムカード" :url-groupware="urlGroupware"
                             @hiddenAppPortal="$emit('hiddenAppPortal')"></HeaderComponent>

            <vs-row>
                <div style="width: 100%; height: 417px">
                    <vs-row style="text-align: center; font-size: 25px; color: #222; margin-top: 15px">
                        <vs-col>
                            {{ currentDate }}{{ currentWeek }}
                        </vs-col>
                    </vs-row>

                    <vs-row :class="currentTimeClass" id="currentTime">
                        <vs-col class="overflow-auto font-weight-bolder">
                            {{ currentTime }}
                        </vs-col>
                    </vs-row>

                    <vs-row style="text-align: center;margin-top: 20px; color: #222">
                        <vs-col>
                            <div>最終打刻時間</div>
                            <div style="line-height: 30px">{{ lastPunchedDate }}{{ lastPunchedWeek }}</div>
                            <div>{{ lastPunchedTime }}</div>
                        </vs-col>
                    </vs-row>

                    <vs-col :class="cardBtnsDivClass">
                        <div>
                            <vs-button :class="cardBtnsClass" color="primary" @click.stop="punchCard(1)"
                                       :disabled="startBtnDisabled">出勤
                            </vs-button>
                            <span :class="btnSpaceClass"></span>
                            <vs-button :class="cardBtnsClass" color="danger" @click.stop="punchCard(2)"
                                       :disabled="endBtnDisabled">退勤
                            </vs-button>
                        </div>
                    </vs-col>
                </div>
            </vs-row>
        </vx-card>
    </div>
</template>

<script>
import {mapState, mapActions} from "vuex";
import HeaderComponent from "./HeaderComponent";
import CircularComponent from "./CircularComponent";
import config from "../../app.config";
import Axios from "axios";

export default {
    components: {
        HeaderComponent,
        CircularComponent,
    },
    props: [],
    data() {
        return {
            currentDate: '',
            currentTime: '',
            currentWeek: '',
            lastPunchedDate: '',
            lastPunchedTime: '',
            lastPunchedWeek: '',
            startBtnDisabled: false,
            endBtnDisabled: false,
            weekIndex: [
                '日', '月', '火', '水', '木', '金', '土'
            ],
            urlGroupware: '/groupware/time-card',
            currentTimeClass: '',
            cardBtnsClass: '',
            cardBtnsDivClass: '',
            btnSpaceClass: '',
            currentNum: 0,
            myCompany: null,
        };
    },
    watch: {
        listMyPages: {
            handler(val) {
                this.cardWidthChanged()
            },
            deep: true
        }
    },

    computed: {
        ...mapState({
            listMyPages: state => state.portal.listMyPages,
        }),
    },
    methods: {
        ...mapActions({
            timeCardStore: "portal/timeCardStore",
            lastPunched: "portal/lastPunched",
        }),

        timeInit: function () {
            let myDate = new Date;
            this.currentDate = myDate.toLocaleDateString()
            this.currentTime = myDate.toLocaleTimeString('japanese', {hour12: false})
            this.currentWeek = '(' + this.weekIndex[myDate.getDay()] + ')'
        },

        punchCard: async function (type) {
            this.buttonStatusChange()
            await this.timeCardStore(this.currentNum).then(
                response => {
                    if (response != false) {
                        this.buttonStatusChange(response.data.lastPunchedType)
                        this.lastPunchedDate = response.data.lastPunchedTime.format_date
                        this.lastPunchedTime = response.data.lastPunchedTime.format_time
                        this.lastPunchedWeek = ' (' + response.data.lastPunchedTime.format_week + ')'
                        this.currentNum = response.data.currentNum
                    }
                },
                error => {
                    console.log(error)
                }
            )
        },

        lastPunchedData: async function () {
            await this.lastPunched().then(
                response => {
                    if (response.data != false) {
                        if (response.data.todayPunched == true) {
                            this.buttonStatusChange(response.data.lastPunchedType)
                        } else {
                            this.buttonStatusChange(2)
                        }
                        this.lastPunchedDate = response.data.lastPunchedTime.format_date
                        this.lastPunchedTime = response.data.lastPunchedTime.format_time
                        this.lastPunchedWeek = ' (' + response.data.lastPunchedTime.format_week + ')'
                        this.currentNum = response.data.currentNum
                    } else {
                        // 履歴なし、出勤しか打刻できない
                        this.endBtnDisabled = true
                        this.startBtnDisabled = false
                        this.lastPunchedTime = '--:--'
                    }
                },
                error => {
                    console.log(error)
                }
            )
        },

        buttonStatusChange: function (type) {
            if (type % 2 == 0) {
                this.endBtnDisabled = true
                this.startBtnDisabled = false
            } else if (type % 2 == 1) {
                this.endBtnDisabled = false
                this.startBtnDisabled = true
            } else {
                this.endBtnDisabled = true
                this.startBtnDisabled = true
            }
        },

        onTimeCardShow: function () {
            this.$router.push('/groupware/time-card')
        },

        cardWidthChanged: function () {
            let timeCardDiv = document.getElementById('timeCardDiv').clientWidth
            if (timeCardDiv < 500) {
                this.currentTimeClass = 'currentTimeClass'
                this.cardBtnsClass = 'cardBtnsClass'
                this.cardBtnsDivClass = 'cardBtnsDivClass'
                this.btnSpaceClass = 'btnSpaceClass'
            } else {
                this.currentTimeClass = 'currentTimeClassBig'
                this.cardBtnsClass = 'cardBtnsClassBig'
                this.cardBtnsDivClass = 'cardBtnsDivClassBig'
                this.btnSpaceClass = 'btnSpaceClassBig'
            }
        }
    },

    mounted() {
        let _this = this
        _this.cardWidthChanged()
        this.timer = setInterval(() => {
            this.currentTime = new Date().toLocaleTimeString('japanese', {hour12: false})
        }, 1000)
    },

    async created() {
        this.timeInit()
        this.lastPunchedData()
        this.myCompany = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany ? this.$store.state.groupware.myCompany : []
        this.urlGroupware = this.myCompany && this.myCompany.attendance_system_flg == 1 ? 'https://swk.shachihata.com/swk' : '/groupware/time-card';
    },

    beforeDestroy() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }
}
</script>


<style lang="scss">
.currentTimeClass {
    width: 100%;
    text-align: center;
    font-size: 60px;
    margin-top: 18px;
    color: #222;
}

.currentTimeClassBig {
    width: 100%;
    text-align: center;
    font-size: 100px;
    margin-top: 18px;
    color: #222;
}

.cardBtnsClassBig {
    font-size: 30px;
    padding: 0.95rem 2.8rem;
}

.cardBtnsClass {
    font-size: 18px !important;
    padding: 10px 20px !important;
}

.cardBtnsDivClassBig {
    margin-top: 20px;
    text-align: center;
}

.cardBtnsDivClass {
    margin-top: 70px;
    text-align: center;
}

.btnSpaceClass {
    display: inline-block;
    width: 15px;
}

.btnSpaceClassBig {
    display: inline-block;
    width: 20%;
}
</style>
