<template>
    <div id="time-card-show">
        <vs-card>
            <header
                class="vs_dialog_header_primary"
                style="height: 56px;">
                <div class="v-toolbar__content">
                    <h2 class="text-center">
                        <span class="headline">タイムカード</span>
                    </h2>
                    <vs-spacer></vs-spacer>
<!--                    <button-icon icon-name="close" class="btnicon_c" color="primary"-->
<!--                                 @clickButton="$emit('onTimeCardHide')"></button-icon>-->
                </div>
            </header>

            <vs-row style="padding: 20px; text-align: center">
                <vs-col style="width: 100%">
                    <div style="font-size: 40px">
                        {{ currentDate }}{{ currentWeek }}
                    </div>
                </vs-col>
                <vs-col>
                    <div style="font-size: 100px">
                        {{ currentTime }}
                    </div>
                </vs-col>
                <vs-col>
                    <div>
                        {{ lastPunchedDate }} {{ lastPunchedWeek }}
                    </div>
                    <div>{{ lastPunchedTime }}</div>
                </vs-col>
                <vs-row>

                </vs-row>
            </vs-row>
            <vs-row>
                <div style="padding: 20px;display: flex;width: 100%;">
                    <vs-spacer></vs-spacer>
                    <vs-button color="primary" @click="onSaveClick">投稿</vs-button>
                    <vs-button color="grey-light" class="nprimary" @click="$emit('onAddTopicHide')"
                               style="color: inherit;">キャンセル
                    </vs-button>
                </div>
            </vs-row>
        </vs-card>
    </div>
</template>
<script>
import 'codemirror/lib/codemirror.css' // Editor's Dependency Style
import '@toast-ui/editor/dist/toastui-editor.css' // Editor's Style]
import '@toast-ui/editor/dist/i18n/ja-jp' //
// import 'tui-color-picker/dist/tui-color-picker.css';
// import '@toast-ui/editor-plugin-color-syntax/dist/toastui-editor-plugin-color-syntax.css';
import {Validator} from 'vee-validate';
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';

import {mapState, mapActions, createNamespacedHelpers} from "vuex";

const BreakpointModule = createNamespacedHelpers('helpers/Breakpoint')

export default {
    components: {
        Editor: () => import('@toast-ui/editor'),
        // colorSyntax:() =>import('@toast-ui/editor-plugin-color-syntax'),
        ButtonIcon: () => import('@/components/portal/bbs/ButtonIcon'),
        flatPickr: () => import('vue-flatpickr-component'),
    },
    props: {
        // value: { type: Boolean, default: false },
        // users: { type: Array, default: () => [] },
        // bbsId:{type: Number, default: null},
        // editData:{
        //     addFlg:{type: Boolean, default: false},
        //     value:{},
        // },

        currentDate: {type: String, default: ''},
        currentTime: {type: String, default: ''},
        currentWeek: {type: String, default: ''},
        lastPunchedDate: {type: String, default: ''},
        lastPunchedTime: {type: String, default: ''},
        lastPunchedWeek: {type: String, default: ''},
    },
    data() {
        return {

        }
    },
    mounted() {

    },
    computed: {},
    watch: {
        // showPostDialog(val) {
        //     this.combErrorFlg = false
        //     this.combErrorMessage = ''
        // },
    },
    created() {

    },
    methods: {
        ...mapActions({
            getBbsCategories: "portal/getBbsCategories",
        }),
        async onSaveClick() {
            this.isContent = false
            const validate = await this.$validator.validateAll()
            this.bbsTopic.start_date = this.start_date
            if (this.end_date === '') this.end_date = null
            this.bbsTopic.end_date = this.end_date
            this.bbsTopic.content = window.tuieditor.getHtml()
            if (!this.bbsTopic.content) {
                this.isContent = true
                return
            }
            if (!validate) return

            this.$emit('savePost', {addFlg: this.addFlg, data: this.bbsTopic})

        },
        handleInput(item) {
            if (!item) {
                this.data.category = null
                return
            }
            this.$validator.errors.remove('name')
            if (typeof item === 'string') {
                this.data.category = item
            } else {
                this.data.category = item.name
            }
        },

        getBbsCategory: async function () {
            let infoTopic = {
                allflg: '1'
            };
            let data = await this.getBbsCategories(infoTopic);

            this.bbsCategoryList = data.data;

            if (this.addFlg == false) {
                let catdata = this.bbsCategoryList.find(item => item.bbs_category_id == this.bbsTopic.bbs_category_id);
                if (Object.keys(catdata).length > 0) {
                    this.onChangeBbsCategory(catdata);
                }

            }
        },
        onSearch: async function () {
            var editobj = JSON.parse(JSON.stringify(this.editData));

            this.bbsTopic = editobj.value;
            this.bbsTopic.contentbf = this.bbsTopic.content;
            this.start_date = this.bbsTopic.start_date ? this.bbsTopic.start_date : new Date().toISOString().slice(0, 10);
            this.end_date = this.bbsTopic.end_date ? this.bbsTopic.end_date : null;

        },
        onChangeBbsCategory(val) {

            this.selCategory = val.name;
            this.bbsTopic.bbs_category_id = val.bbs_category_id;

        },
        openCalendarClick: function () {
            this.$refs.calendar.fp.toggle();
        },
        onStartChange(selectedDates, dateStr, instance) {
            this.configs.end.minDate = dateStr
        },
        onEndChange(selectedDates, dateStr, instance) {
            this.configs.start.maxDate = dateStr
        }
    }
}
</script>
<style lang="scss">
</style>
