<!--Todo -->
<template>
    <div id="calculation-page" style="position: relative;">
        <vs-card style="margin-bottom: 0">
            <vs-row vs-type="flex">
                <vs-col vs-type="flex" vs-w="2" vs-sm="6" vs-xs="6">
                    <vs-row>
                        <vs-col vs-type="flex" vs-w="12">
                            <vs-input class="inputx " style="width: 97.66667%"
                                      v-model="filter.form_code" label="書類ID"/>
                        </vs-col>
                    </vs-row>
                </vs-col>

                <vs-col vs-w="2" vs-sm="6" vs-xs="6" class="pl-4">
                    <vs-row class="vs-input--label lable-title">様式の種類</vs-row>
                    <vs-row class="pt-2 con-checkbox">
                        <vs-checkbox vs-value="advance" v-model="filter.advance">申請書</vs-checkbox>
                        <vs-checkbox vs-value="settlement" v-model="filter.settlement">精算書</vs-checkbox>
                    </vs-row>
                </vs-col>
                <vs-col vs-w="2" vs-sm="4" vs-xs="6" class="pl-4">
                    <vs-row>
                        <vs-col vs-type="flex" vs-w="12">
                            <vs-input class="inputx w-full " style="margin-right:12px;" label="様式名"
                                      v-model="filter.form_name"/>
                        </vs-col>
                    </vs-row>
                </vs-col>
                <vs-col vs-w="2" vs-sm="6" vs-xs="12" class="pl-4">
                    <vs-select class="selectExample w-full" id="reviewStatusOther" label="目的"
                               v-model="filter.purpose_name">
                        <vs-select-item value="" text="---"/>
                        <vs-select-item v-for="item in listMPurposeDataSelect"
                                        :key="item.purpose_name"
                                        :value="item.purpose_name" :text="item.purpose_name"/>
                    </vs-select>
                </vs-col>
                <vs-col vs-w="4" vs-sm="4" vs-xs="12" class="pl-4">
                    <vs-row class="vs-input--label lable-title" >状況</vs-row>
                    <vs-row class="pt-2 con-checkbox">
                        <vs-checkbox vs-value="before_circulation" v-model="filter.before_circulation">回覧前</vs-checkbox>
                        <vs-checkbox vs-value="circulating" v-model="filter.circulating">回覧中</vs-checkbox>
                        <vs-checkbox vs-value="approved" v-model="filter.approved">回覧完了</vs-checkbox>
                        <vs-checkbox vs-value="rejected" v-model="filter.rejected">差戻し</vs-checkbox>
                    </vs-row>
                </vs-col>
            </vs-row>
            <vs-row>
                <vs-col vs-type="flex" vs-w="4" class="mb-3">
                    <vs-row>
                        <vs-col class="mb-3 " vs-w="5" vs-type="flex">
                            <div class="w-full">
                                <label for="filter_fromdate" class="vs-input--label">該当期間</label>
                                <div class="vs-con-input" style="position: relative; width:100%">
                                    <flat-pickr class="w-full" v-model="filter.target_period_form"
                                                id="filter_fromdate" :config="completedConfigDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-w="1" style="font-size: 16px;padding-top: 12px;"
                                vs-type="flex"vs-align="center" vs-justify="center">～</vs-col>
                        <vs-col vs-w="5" class="mb-3" vs-type="flex" vs-align="flex-end">
                            <div class="w-full">
                                <div class="vs-con-input" style="position: relative; width:100%">
                                    <flat-pickr class="w-full" v-model="filter.target_period_to"
                                                id="filter_todate" :config="completedConfigDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                    </vs-row>
                </vs-col>
                <vs-col vs-type="flex"
                        vs-w="8"
                        vs-align="center"
                        vs-justify="flex-end">
                    <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i>
                        検索
                    </vs-button>

                </vs-col>
            </vs-row>

            <vs-row class="pt-6" vs-type="flex" vs-justify="flex-end">
                <vs-button
                    class="square mr-4"
                    color="primary"
                    @click="onShowPreparationDialog">
                    申請書作成
                </vs-button>
                <vs-button class="square"
                           color="primary"
                           @click="onShowSettlementDialog">
                    精算書作成
                </vs-button>
            </vs-row>
        </vs-card>
        <vs-card style="margin-bottom: 0">
            <vs-table class="mt-3 custome-event" :data="formList"
                      noDataText="データがありません。" sst @sort="handleSort"
                      stripe>
                <template slot="thead">
                    <!--                    <vs-th class="width-50"><vs-checkbox  @click="" /></vs-th>-->
                    <vs-th sort-key="form_code">書類ID</vs-th>
                    <vs-th sort-key="form_type">申請/精算</vs-th>
                    <vs-th sort-key="form_name">様式名</vs-th>
                    <vs-th sort-key="purpose_name">目的</vs-th>
                    <vs-th sort-key="suspay_amt">仮払金額</vs-th>
                    <vs-th sort-key="eps_amt">精算金額</vs-th>
                    <vs-th sort-key="eps_diff">差額</vs-th>
                    <vs-th sort-key="target_period_from">期間</vs-th>
                    <vs-th sort-key="filing_date">提出日</vs-th>
                    <vs-th sort-key="status">状況</vs-th>
                    <vs-th sort-key="update_at">最終更新日</vs-th>
                </template>

                <template slot-scope="{data}">
                    <vs-tr v-for="(tr, indextr) in data" :key="indextr" @click="moveToScreenOverForm(tr)">
                        <!--                        <vs-td><vs-checkbox  :value="tr.selected" @click="onRowCheckboxClick(tr)" /></vs-td>-->
                        <td @click="moveToScreenOverForm(tr)">{{ tr.form_code }}</td>
                        <td @click="moveToScreenOverForm(tr)">{{ formTypeDisplay[tr.form_type] }}</td>
                        <td @click="moveToScreenOverForm(tr)">{{ tr.form_name }}</td>
                        <td @click="moveToScreenOverForm(tr)">{{ tr.purpose_name }}</td>
                        <td @click="moveToScreenOverForm(tr)">
                            <p v-if="tr.suspay_amt != 0">{{ tr.suspay_amt}}</p>
                            <p v-if="tr.suspay_amt == 0">―</p>
                        </td>
                        <td @click="moveToScreenOverForm(tr)">
                            <p v-if="tr.eps_amt != 0">{{ tr.eps_amt}}</p>
                            <p v-if="tr.eps_amt == 0">―</p>
                        </td>
                        <td @click="moveToScreenOverForm(tr)">
                            <p v-if="tr.eps_amt != 0 && tr.suspay_amt != 0 && tr.eps_diff!=null">{{ tr.eps_diff }}</p>
                            <p v-if="tr.eps_amt == 0 || tr.suspay_amt == 0 || tr.eps_diff==null">―</p>
                        </td>
                        <td @click="moveToScreenOverForm(tr)">
                            <p v-if="tr.target_period_to">
                            {{ tr.target_period_from | moment("YYYY/MM/DD") }} -
                            {{ tr.target_period_to | moment("YYYY/MM/DD") }}
                            </p>
                            <p v-else>
                                {{ tr.target_period_from | moment("YYYY/MM/DD") }}
                            </p>
                        </td>
                        <td @click="moveToScreenOverForm(tr)">{{ tr.filing_date }}</td>
                        <td @click="moveToScreenOverForm(tr)">{{ statusDisplay[tr.status] }}</td>
                        <td @click="moveToScreenOverForm(tr)">{{ tr.update_at | moment("YYYY/MM/DD") }}</td>
                    </vs-tr>
                </template>
            </vs-table>
            <div>
                <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from ? pagination.from : 0 }} 件から {{ pagination.to ? pagination.to : 0 }} 件までを表示
                </div>
            </div>
            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
        </vs-card>

        <vs-popup class="popup-dialog detail-popup"
                  classContent="popup-example"
                  @close="onCloseDialog"
                  :title="titlePopup"
                  :active.sync="showPreparationDialog">
            <div class="mb-3">
                <div class="mb-4" v-if="isFormSettlement">
                    事前申請が不要な精算様式
                </div>

                <div class="mb-6" v-for="(tr, indextr) in listMPurposeDataPopup" :key="indextr">
                    <vs-row class="mb-1">
                        <vs-col vs-w="1"></vs-col>
                        <vs-col vs-w="11">
                            {{ tr.purpose_name }}
                        </vs-col>
                    </vs-row>
                    <vs-row class="mb-1">
                        <vs-col vs-w="2"></vs-col>
                        <vs-col vs-w="10">
                            <router-link :to="{ name: tr.url_name }"
                                         @click.native="moveToScreen(tr)"
                                         custom v-slot="{ href}">
                                <a :href="href"> {{ tr.form_name }} </a>
                            </router-link>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mb-1">
                        <vs-col vs-w="3"></vs-col>
                        <vs-col vs-w="9"> {{ tr.form_describe }}</vs-col>
                    </vs-row>
                </div>

                <vs-row vs-type="flex" vs-justify="flex-end">
                    <vs-button class="square mr-0"
                               color="#bdc3c7"
                               type="filled"
                               @click="onCloseDialog">閉じる
                    </vs-button>
                </vs-row>
            </div>
        </vs-popup>

    </div>
</template>


<script>
import {mapActions} from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import {EXPENSE} from '../../enums/expense';
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import utils from '../../utils/utils';

export default {

    components: {
        VxPagination,
        flatPickr,
    },
    data() {
        return {
            filter: {
                form_code: "",
                advance: "",
                settlement: "",
                form_name: "",
                purpose_name: "",
                before_circulation: "",
                circulating: "",
                approved: "",
                rejected: "",
                target_period_form: "",
                target_period_to: "",

            },

            formList: [],
            listMFormAdvance: [],
            listMFormSettlement: [],
            listMPurposeDataSelect: [],
            formTypeDisplay: {
                0: '',
                1: '申請',
                2: '精算',
            },
            statusDisplay: null,

            pagination: {
                totalPage: 0,
                currentPage: 1,
                limit: 10,
                totalItem: 0,
                from: 0,
                to: 0
            },
            titlePopup: '',
            isFormSettlement: false,
            completedConfigDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                dateFormat: "Y/m/d",
                minDate: "",
                maxDate: ""
            },

            showPreparationDialog: false,

            listMPurposeDataPopup: [],

        }
    },
    computed: {},

    methods: {
        ...mapActions({
            getActuarialData: "expenseSettlement/getActuarialData",
            getMPurposeDataSelect: "expenseSettlement/getMPurposeDataSelect",
            getEpsMPurposeInfo: "expenseSettlement/getEpsMPurposeInfo",
        }),
        async onShowSettlementDialog() {
            this.showPreparationDialog = true
            this.titlePopup = '精算書作成'
            this.isFormSettlement = true
            this.listMPurposeDataPopup = this.listMFormSettlement
        },
        onCloseDialog() {
            if (this.isFormSettlement) {
                this.isFormSettlement = false
            }
            this.showPreparationDialog = false
        },
        async onGetEpsMPurposeInfo() {
            const data = await this.getEpsMPurposeInfo()
            if (data) {
                if (data.m_form_advance) {
                    this.listMFormAdvance = data.m_form_advance.map(item => {
                        item.url_name = utils.getUrlByFormType(item)
                        return item
                    })
                }
                if (data.m_form_settlement) {
                    this.listMFormSettlement = data.m_form_settlement.map(item => {
                        item.url_name = utils.getUrlByFormType(item)
                        return item
                    })
                }
            }
        },

        async onShowPreparationDialog() {
            this.showPreparationDialog = true
            this.titlePopup = '申請書作成'
            this.listMPurposeDataPopup = this.listMFormAdvance

        },
        moveToScreen(data) {
            let formType = null
            if (this.isFormSettlement) {
                formType = EXPENSE.M_FORM_FORM_TYPE_SETTLEMENT
            } else {
                formType = EXPENSE.M_FORM_FORM_TYPE_ADVANCE
            }
            this.showPreparationDialog = false
            let formName = null, formCode = null, purposeName = null
            if (data.form_name) {
                formName = data.form_name
            }
            if (data.form_code) {
                formCode = data.form_code
            }
            if (data.purpose_name) {
                purposeName = data.purpose_name
            }
            this.$store.commit('expenseSettlement/updateFormType', formType)
            this.$store.commit('expenseSettlement/updateCreateForm', true)
            this.$store.commit('expenseSettlement/updateTAppStatus', null)
            this.$store.commit('expenseSettlement/updateFormName', formName)
            this.$store.commit('expenseSettlement/updateFormCode', formCode)
            this.$store.commit('expenseSettlement/updatePurposeName', purposeName)
            this.$store.commit('expenseSettlement/updateViewForm', false)
            this.$store.commit('expenseSettlement/updateEditForm', false)
            this.$store.commit('expenseSettlement/updateBackupData', false)
            this.$store.commit('expenseSettlement/updateCreateFormnAdvanceComplete', false)

        },
        moveToScreenOverForm(data) {
            let formType, tAppStatus
            if (data.form_type) {
                formType = data.form_type
            }
            if (data.hasOwnProperty('status')) {
                tAppStatus = data.status
            }
            let formName = null, formCode = null, purposeName = null
            if (data.form_name) {
                formName = data.form_name
            }
            if (data.form_code) {
                formCode = data.form_code
            }
            if (data.purpose_name) {
                purposeName = data.purpose_name
            }
            this.$store.commit('expenseSettlement/updateFormType', formType)
            this.$store.commit('expenseSettlement/updateCreateForm', false)
            this.$store.commit('expenseSettlement/updateTAppStatus', tAppStatus)
            this.$store.commit('expenseSettlement/updateFormName', formName)
            this.$store.commit('expenseSettlement/updateFormCode', formCode)
            this.$store.commit('expenseSettlement/updatePurposeName', purposeName)
            this.$store.commit('expenseSettlement/updateEditForm', false)
            this.$store.commit('expenseSettlement/updateViewForm', true)
            this.$store.commit('expenseSettlement/updateBackupData', false)
            this.$store.commit('expenseSettlement/updateCreateFormnAdvanceComplete', false)
            let url = utils.getUrlByFormType(data)
            if (data.id) {
                this.$router.push(`/calculation-expense/${url}/${data.id}`);
            }

        },

        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active ? "DESC" : "ASC";
            this.onSearch(false);
        },

        onSearch: async function (resetPaging) {
            let queries = {
                form_code: this.filter.form_code,
                advance: this.filter.advance,
                settlement: this.filter.settlement,
                form_name: this.filter.form_name,
                purpose_name: this.filter.purpose_name,
                before_circulation: this.filter.before_circulation,
                circulating: this.filter.circulating,
                approved: this.filter.approved,
                rejected: this.filter.rejected,
                target_period_form: this.filter.target_period_form,
                target_period_to: this.filter.target_period_to,
                page: resetPaging ? 1 : this.pagination.currentPage,
                limit: this.pagination.limit,
                orderBy: this.orderBy,
                orderDir: this.orderDir,
                action: resetPaging ? 'search' : '',
            };
            const data = await this.getActuarialData(queries);
            this.formList = data.data.map(item => {
                item.range_time = item.target_period_from + ' - ' + item.target_period_to;
                item.eps_diff = utils.formatPrice(item.eps_diff);
                item.suspay_amt = utils.formatPrice(item.suspay_amt);
                item.eps_amt = utils.formatPrice(item.eps_amt);
                return item
            });
            this.pagination.totalItem = data.total;
            this.pagination.totalPage = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit = data.per_page;
            this.pagination.from = data.from;
            this.pagination.to = data.to;
        },

        onClose: function () {
            this.onShowPreparationDialog = false;
        },
        async onGetMPurposeDataSelect() {
            this.listMPurposeDataSelect = await this.getMPurposeDataSelect()
        },

    },

    created() {
        this.statusDisplay = EXPENSE.T_APP_STATUS_DISPLAY
        this.onGetMPurposeDataSelect()
        this.onGetEpsMPurposeInfo()
    },

    watch: {
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        }
    },
    mounted() {
        this.onSearch(false);
    }

}
</script>

<style lang="scss">

#calculation-page {
    .popup-dialog .vs-popup {
        width: 50em !important;
    }
    .con-checkbox {
        height: 44px;
    }

    .lable-title {
        padding-left: 4px !important;
    }

    .title-text-bold {
        font-weight: bold;
    }

    .text-decoration-underline {
        text-decoration: underline;
    }

    .font-color-link-primary {
        color: rgba(var(--vs-primary), 1)
    }

    .vs-con-table td {
        cursor: pointer !important;
    }
}
.flatpickr-calendar {
    position: absolute !important;
    .flatpickr-current-month {
        height: 35px;
        bottom: -10px;
    }
}

</style>
