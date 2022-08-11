<template>
    <div id="bulletin_board">
        <top-menu></top-menu>
        <vs-row>
<!--            <div class="mr-2 leftpanel">-->
<!--                <menu-left></menu-left>-->
<!--            </div>-->
            <div class="bbs" style="width: 100%">
                <div id="bbs" class="containar">
                    <vs-row style="text-align: center">
                        <vs-col
                            style="width: 100%; margin-top: 20px;letter-spacing: 1px;font-size: 50px; height: 25px;color:#222">
                            {{ currentDate }}{{ currentWeek }}
                        </vs-col>
                        <vs-col style="font-size: 170px; height: 140px;letter-spacing: 1px;color:#222;margin-top: -90px">
                            {{ currentTime }}
                        </vs-col>
                        <vs-col style="font-size: 20px; height: 20px;letter-spacing: 0.5px;color:#222;margin-top: -40px">
                            <div>最終打刻時間</div>
                            <div style="line-height: 50px">{{ lastPunchedDate }} {{ lastPunchedWeek }}</div>
                            <div>{{ lastPunchedTime }}</div>
                        </vs-col>
                        <vs-row style="height: 120px; position: relative;margin-top: -100px">
                            <vs-col>
                                <vs-button class="square list-btn" color="default" @click="showDetails">
                                    打刻一覧
                                </vs-button>
                                <div style="text-align: center;">
                                    <vs-button class="square start-btn" color="primary"
                                               v-bind:disabled="startBtnDisabled" @click="punchCard(1)">出勤
                                    </vs-button>
                                    <span style="display:inline-block; width: 10%"></span>
                                    <vs-button class="square end-btn" color="danger" v-bind:disabled="endBtnDisabled"
                                               @click="punchCard(2)">退勤
                                    </vs-button>
                                </div>
                            </vs-col>
                        </vs-row>
                    </vs-row>
                </div>
            </div>
        </vs-row>
    </div>
</template>
<script>
import {mapState, mapActions} from "vuex";
import TopMenu from "../../../components/portal/TopMenu";

export default {
    components: {
        TopMenu
    },
    data() {
        return {
            currentDate: '',
            currentTime: '',
            currentWeek: '',
            lastPunchedDate: '',
            lastPunchedTime: '',
            lastPunchedWeek: '',
            endBtnDisabled: false,
            startBtnDisabled: false,
            weekIndex: [
                '日', '月', '火', '水', '木', '金', '土'
            ],
            currentNum: 0  // テーブルに挿入する時はインクリメントする
        }
    },
    computed: {},
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
            this.buttonStatusChange() // 制御しなければ、二重打刻できてしまう
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
                }
            )
        },

        showDetails: function () {
            this.$router.push('/groupware/time-card/detail')
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
    },

    created() {
        this.timeInit()
        this.lastPunchedData()
    },
    mounted() {
        this.timer = setInterval(() => {
            this.currentTime = new Date().toLocaleTimeString('japanese', {hour12: false})
        }, 1000)
    },

    beforeDestroy() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }
}

</script>
<style lang="scss">
.iframe-groupware {
    height: calc(100vh - 87px);
}

.start-btn, .end-btn {
    padding: 1.1rem 5rem !important;
    font-size: 45px !important;
}

.list-btn {
    left: -42% !important;
    top: 97px !important;
    padding: 2.45rem 3.1rem !important;
    background-color: #bbb !important;
    color: #222;
    font-size: 20px;
}

</style>
