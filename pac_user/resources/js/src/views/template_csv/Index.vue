<template>
    <div>
    <div id="sends-page" style="position: relative;">
        <vs-row>
            <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                <vs-card style="margin-bottom: 0">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-select class="selectExample w-full" id="finishedDate" v-model="filter.finishedDate" label="完了日時">
                                <vs-select-item :key="index" :value="index" :text="date" v-for="(date, index) in finishedTimeList" />
                            </vs-select>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                            <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                        </vs-col>
                    </vs-row>
                </vs-card>

                <vs-card>
                    <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="warning"  @click="showDialogDownload"
                            v-bind:disabled="selected.length == 0"><i class="fas fa-download"></i> CSVダウンロード</vs-button>

                    <vs-table class="mt-3 custome-event" :data="compListData" noDataText="データがありません。"
                        sst @sort="handleSort" stripe >
                        <template slot="thead">
                            <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                            <vs-th sort-key="circular_kind" class="">回覧種類 </vs-th>
                            <vs-th sort-key="file_names" class="max-width-200">文書名 </vs-th>
                            <vs-th sort-key="sender" class="max-width-200">差出人</vs-th>
                            <vs-th sort-key="emails" class="min-width-200">宛先</vs-th>
                        <!--    <vs-th sort-key="dests">送信先環境</vs-th>-->
                            <vs-th sort-key="C.access_code">アクセスコード</vs-th>
                            <vs-th sort-key="update_at">完了日時</vs-th>
                            <vs-th></vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <vs-td><vs-checkbox  :value="tr.selected" @click="onRowCheckboxClick(tr)" /></vs-td>
                                <td>{{tr.kind}}</td>
                                <td class="max-width-200">{{tr.file_names}}</td>
                                <td><div v-html="tr.sender"></div></td>
                                <td class="min-width-200"><div v-html="tr.emails"></div></td>
                                <td> 社内 {{tr.access_code}}<br/> 社外 {{tr.outside_access_code}}</td>
                            <!--    <td @click="onShowReading(tr)"><div v-html="tr.dests"></div></td>-->
                                <td >{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                                <td >{{tr.kind === '送信' && tr.result === 1 ? '[自動保管済]' : ''}}</td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                    <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                </vs-card>
            </vs-col>

            
        </vs-row>

        <vs-popup classContent="popup-example"  title="選択文書情報csvダウンロード" :active.sync="confirmDownload">
            <vs-row>
                <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="input.filename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
            </vs-row>
            <vs-row>
                    <vs-col vs-type="flex" vs-w="1">設定</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-align="center" vs-w="10">
                        <label for="option1" class="mr-2">
                            <input type="radio" id="option1" value="0,2" name="request_flg" v-model="input.contents">
                            テンプレート情報
                        </label>
                        <label for="option2" class="mr-2">
                            <input type="radio" id="option2" value="0" name="request_flg" v-model="input.contents">
                            文書情報
                        </label>
                        <label for="option3" class="mr-2">
                            <input type="radio" id="option3" value="0,1" name="request_flg" v-model="input.contents">
                            回覧情報
                        </label>
                        <label for="option4" class="mr-2">
                            <input type="radio" id="option4" value="0,1,2" name="request_flg" v-model="input.contents">
                            全情報
                        </label>
                    </vs-col>
                </vs-row>
            <vs-row>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button v-bind:disabled="input.contents.length == 0" @click="onDownloadReserve" color="warning">CSV出力</vs-button>
                    <vs-button @click="confirmDownload=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
    </div>
</template>


<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from 'vue-infinite-loading';

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import config from "../../app.config";
import Axios from "axios";
import { DOWNLOAD_TYPE } from '../../enums/download_type';
import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination
    },
    data() {
        return {
            filter: {
                id: "",
                kind: "1",
                name: "",
                destEnv: "",
                finishedDate: "",
            },
            selectAll: false,
            listData:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "",
            orderDir: "",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
            },
            completedConfigDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                minDate: "",
                maxDate: ""
            },            
            confirmDownload: false,
            hasUndownloaded: false,

            showReading: false,
            options_status: ['保存中','回覧中','回覧完了','回覧完了(保存済)','差戻し','引戻','','','','削除'],
            recived_status: ['未通知','通知済/未読','既読','承認(捺印あり)','承認(捺印なし)','差戻し','差戻し(未読)'],
            circular_kind: ['受信', '送信'],
            out_status: ['-','未読','処理済'],
            itemPull: {},
            itemReading: {},
            itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}]},
            click: 0,
            time: null,
            searchAreaFlg:false,
            canUseTemplate:false,
            canStoreCircular:false,
            keywords: '',
            settingLimit:{},
            filename_upload: '',
            breadcrumbItems: [],
            dataBlob: '',
            input:{
                ids: [],
                filename: "",
                contents: [],
                finishedDate:null
            },
            inputMaxLength: 46,
            finishedTimeList: ['当月', '1ヶ月前', '2ヶ月前', '3ヶ月前', '4ヶ月前', '5ヶ月前', '6ヶ月前', '7ヶ月前', '8ヶ月前', '9ヶ月前', '10ヶ月前', '11ヶ月前','12ヵ月以前'],
            DOWNLOAD_TYPE : DOWNLOAD_TYPE,
        }
    },
    methods: {
        ...mapActions({
            search: "template/getListCompleted",
            downloadReserve: "template/csvDownloadreserve",
        }),
        onSearch: async function (resetPaging) {
            this.selectAll = false;
            let info = { kind           : this.filter.kind,  
                        filename       : this.filter.filename,   
                        page           : resetPaging?1:this.pagination.currentPage,
                        limit          : this.pagination.limit,
                        finishedDate    : this.filter.finishedDate,
                        orderBy        : this.orderBy,
                        orderDir       : this.orderDir,
                        };
            var data = await this.search(info);
            
            
            this.listData               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
            this.month                  = this.filter.finishedDate;
        //    this.selected               = [];
        },
        onSelectAll() {
        this.selectAll = !this.selectAll;
        this.listData.map(item=> {item.selected = this.selectAll; return item});
        },       
        showDialogDownload(){
            this.hasUndownloaded = false;
            for (let i in this.selected) {
                if(this.selected[i].circular_status == 2){
                    this.hasUndownloaded = true;
                    break;
                }
            }
            if(this.selected.length == 1 && this.selected[0].file_names.split(',').length == 1){
                // .pdf, .docx, .xlsx
                var pos = this.selected[0].file_names.lastIndexOf('.');
                this.inputMaxLength = 50 - this.selected[0].file_names.substr(pos).length;
            }else{
                // .zip
                this.inputMaxLength = 46;
            }
            this.input.filename = '';
            this.confirmDownload = true;
        },
        onDownloadReserve: async function (){
            this.input.ids = this.getSelectedID();
            this.input.finishedDate = this.filter.finishedDate;
            this.input.download_type = this.DOWNLOAD_TYPE.TEMPLATE_CSV_DOWNLOAD_RESERVE;//回覧完了テンプレート一覧で予約
            this.confirmDownload = false;
            await this.downloadReserve(this.input);
        },
        getSelectedID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        onRowCheckboxClick: function (tr) {
            tr.selected = !tr.selected;
            this.selectAll = this.compListData.every(item => item.selected);
        },
    },
    computed:{
        compListData: function() {
            let data = [];
            this.listData.forEach((item) => {
                item.kind = this.circular_kind[item.circular_kind];
                /*if (item.circular_kind == 0) {
                    item.kind += '（'+ this.env[item.sender_env] + '）';
                }*/
                data.push(item);
            });
            return data;
        },
        selected() {
            return this.listData.filter(item => item.selected);
        },
        isEmtyItemReading() {
            for(let i in this.itemReading) return false;
            return true
        },
    },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        'filter.finishedDate': function () {
            // 完了日時From/Toの日付範囲を設定
            let year = new Date().getFullYear();
            let month = new Date().getMonth() + 1 - this.filter.finishedDate;

            // 月は昨年の時
            if (this.filter.finishedDate >= new Date().getMonth() + 1) {
                year = year - 1;
                month = month + 12;
            }
            // 月の日数
            let lastDay = new Date(year, month, 0).getDate()
            this.filter.fromdate = year + '-' + month + '-01';
            this.filter.todate = year + '-' + month + '-' + lastDay;
            this.completedConfigDate.minDate = year + '-' + month + '-01';
            this.completedConfigDate.maxDate = year + '-' + month + '-' + lastDay;
        },
        'input.finishedDate': function(){
            this.input.finishedDate = this.month;
        } 
    },
    mounted() {
        this.onSearch(false);
    },
    async created() {
        var $this = this;
        Axios.get(`${config.BASE_API_URL}/myinfo`)
        .then(response => {
          const myInfo = response.data ? response.data: [];

        Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
            .then(response => {
                if (response.data.data.template_flg && response.data.data.template_csv_flg == 1){
                    this.canUseTemplate = myInfo.data.info.template_flg;
                }
            })
            .catch(error => { return []; });
        })
        .catch(error => { return []; });

        this.$nextTick(()=>{
            let popups = document.getElementsByClassName('vs-component con-vs-popup vs-popup-primary');
            for (let i = 0;i < popups.length;i ++){
                let div = document.createElement('div');
                div.style.width = '100%';
                div.style.height = '100%';
                div.style.position = 'fixed';
                div.style.left = 0;
                div.style.top = 0;
                div.style.zIndex = 50;
                //div.setAttribute('style','z-index:50');
                popups[i].appendChild(div);
            }
        });
    }
}

</script>

<style lang="stylus">
    .detail{
        .label{ background: #b3e5fb; padding: 3px; }
        .info{  padding: 3px 3px 3px 5px; }
    }
    @media only screen and (min-width: 901px) {
        .vs-lg-2 {
            width: 20%!important;
        }
    }
    #arrow{
        cursor:pointer;
    }
    .around{
        animation:0.5s around_arrow;
        animation-fill-mode:forwards;
    }
    .around_return{
        animation:0.5s around_arrow_return;
        animation-fill-mode:forwards;
    }
    @keyframes around_arrow{
        0%{ transform:rotate(0);}
        100%{ transform:rotate(180deg); }
    }
    @keyframes around_arrow_return{
        0%{ transform:rotate(180deg);}
        100%{ transform:rotate(0); }
    }
</style>