<template>
    <div class="special-component">
        <vs-row>
            <span class="mb-3  sm:pl-2" style="width: 25%">組織名</span>
            <span class="mb-3  sm:pl-2">地域名</span>
        </vs-row>
        <vs-row>
            <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                <vs-input class="inputx w-full" v-model="filter.group_name"/>
            </vs-col>
            <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                <v-select class="w-full" :options="regionList" :clearable="false" :searchable ="false" @input="selectRegionName" :value="filter.region_name"/>
            </vs-col>
            <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
            </vs-col>
        </vs-row>
        <vs-row v-if="showReceivedList">
            <vs-card class = "list-received">
                <vs-table class="mt-3 table-special-width"
                          :data="listDataReceived"
                          noDataText="データがありません。"
                          sst stripe
                          @sort="handleSort"
                          @selected="onShowTemplate">
                    <template slot="thead">
                        <vs-th sort-key="group_name"
                               class="tex-list-received pr-3">組織名 </vs-th>

                        <vs-th sort-key="region_name"
                               class="tex-list-received width-date">地域名</vs-th>
                    </template>

                    <template slot-scope="{data}">
                        <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                            <vs-td style="display: none">
                                {{tr.special_site_company_id}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.env_flg}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.edition_flg}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.server_flg}}
                            </vs-td>
                            <vs-td class="tex-list-received pr-3">
                                {{tr.group_name}}
                            </vs-td>
                            <vs-td class="tex-list-received pr-3">
                                {{tr.region_name}}
                            </vs-td>
                        </vs-tr>
                    </template>
                </vs-table>
                <div><div class="mt-3 mb-5">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
            </vs-card>
        </vs-row>

        <modal name="template-list-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'template-list-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row class="border-bottom pb-4 mb-2">
                <h4>文書一覧</h4>
            </vs-row>

            <div class=" favorite-internal mb-2">
                <vs-row class="mb-3" vs-type="flex">
                    <span>提出したい文書を選択してください。</span>
                </vs-row>
            </div>

            <vs-card class = "list-received">
                <vs-table class="mt-3 table-special-width"
                          :data="listDataTemplate"
                          noDataText="データがありません。"
                          sst stripe
                          @sort="handleSort"
                          @selected="onOpenTemplate">
                    <template slot="thead">
                        <vs-th class="tex-list-received pr-3">文書名 </vs-th>
                        <vs-th class="tex-list-received width-date">登録日</vs-th>
                    </template>

                    <template slot-scope="{data}">
                        <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                            <vs-td style="display: none">
                                {{tr.id}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.env_flg}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.edition_flg}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.server_flg}}
                            </vs-td>
                            <vs-td class="tex-list-received pr-3">
                                {{tr.storage_file_name}}
                            </vs-td>
                            <vs-td style="display: none">
                                {{tr.placeholderData}}
                            </vs-td>
                            <vs-td class="tex-list-received width-date">{{tr.template_create_at | moment("YYYY/MM/DD HH:mm")}}</vs-td>
                            <vs-td style="display: none" class="tex-list-received width-date">{{tr.template_update_at | moment("YYYY/MM/DD HH:mm")}}</vs-td>
                        </vs-tr>
                    </template>
                </vs-table>
                <div><div class="mt-3 mb-5">{{ documentPagination.totalItem }} 件中 {{ documentPagination.from }} 件から {{ documentPagination.to }} 件までを表示</div></div>
                <vx-pagination :total="documentPagination.totalPage" :currentPage.sync="documentPagination.currentPage"></vx-pagination>
            </vs-card>

            <vs-row class="pt-5 pb-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="$modal.hide('template-list-modal')"> キャンセル</vs-button>
            </vs-row>
        </modal>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    components: {
        VxPagination,
    },
    name: "SpecialComponent",
    props: {
        issetSpecialInternal: {

        }
    },
    beforeCreate() {
    },
    created() {
    },
    beforeMount() {
    },
    mounted() {
        if(this.issetSpecialInternal){
            this.onSearch(true);
        }
    },
    beforeUpdate() {
    },
    update() {
    },
    beforeDestroy() {
    },
    destroyed() {
    },

    data() {
        return {
            showReceivedList: true,
            listDataReceived: [],
            listDataTemplate: [],
            filter: {
                group_name: "",
                region_name: "",
            },
            pagination: {
                totalPage: 0,
                currentPage: 1,
                limit: 10,
                totalItem: 0,
                from: 1,
                to: 10
            },
            documentPagination: {
              totalPage: 0,
              currentPage: 1,
              limit: 10,
              totalItem: 0,
              from: 1,
              to: 10
            },
            orderBy: "group_name",
            orderDir: "desc",
            loginUser: JSON.parse(getLS('user')),
            regionList:[''],
            region_id:"",
        }
    },
    methods: {
        ...mapActions({
            getListServiceInternal: "portal/getListServiceInternal",
            search: "special/getListReceived",
            searchTemplate: "special/getListTemplate",
            setFiles: "template/setFiles",
            selectFile: "template/selectFile",
        }),
        async onSearch (resetPaging) {
            let info = {
                group_name  : this.filter.group_name,
                region_name : this.filter.region_name,
                page        : resetPaging ? 1 : this.pagination.currentPage,
                limit       : this.pagination.limit,
                orderBy     : this.orderBy,
                orderDir    : this.orderDir,
            };
            let data = await this.search(info);
            for (let i = 0; i < data.regionList.length; i++) {
                this.regionList.push(data.regionList[i].region_name);
            }
            data                        = data.data;
            this.listDataReceived       = data.data;
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
        },

        async onShowTemplate(tr) {
            this.$modal.show('template-list-modal');
            let info = {
              company_id: tr.special_site_company_id,
              env_flg: tr.env_flg,
              edition_flg: tr.edition_flg,
              server_flg: tr.server_flg,
              page: tr == this.documentPagination.tr ? this.documentPagination.currentPage : 1,
              limit: this.documentPagination.limit,
            }
            let data = await this.searchTemplate(info);
            data                 = data.data;
            this.listDataTemplate = data.data;
            this.documentPagination.totalItem = data.total;
            this.documentPagination.totalPage = data.last_page;
            this.documentPagination.currentPage = data.current_page;
            this.documentPagination.limit = data.per_page;
            this.documentPagination.from = data.from;
            this.documentPagination.to = data.to;
            this.documentPagination.tr = tr;
        },
        async onOpenTemplate(tr) {
            let files = [];
            files.push(tr);
            setTimeout(() => {
                this.setFiles(files);
                this.selectFile(tr);
                this.$router.push('/special/template/update/true');
            }, 300);
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active? "DESC" : "ASC";
            this.onSearch(false);
        },
        selectRegionName(val) {
            this.filter.region_name = val;
        },
    },
    computed: {
        ...mapState({
        }),
    },
    watch: {
      'pagination.currentPage': function (val) {
        this.onSearch(false);
      },
      'documentPagination.currentPage': function (val) {
        this.onShowTemplate(this.documentPagination.tr);
      }
    }
}

</script>

<style scoped>


</style>
