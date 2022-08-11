<template>
    <div class="circular-component">
        <vs-row class="mt-3">
            <vs-col vs-w="4">
                <div class="internal-detail mouse-hover" @click ="onClickCreateDocument" v-if="allowedUploadDocuments">
                    <img src="@assets/images/pages/home/creation.svg" alt="icon create document">
                    <span>新規作成</span>
                </div>
                <div class="internal-detail mouse-hover" @click="showReceivedList = !showReceivedList" :class="showReceivedList?'border-rm-bottom':''">
                    <img src="@assets/images/pages/home/received.svg" alt="icon received document">
                    <span>受信一覧</span>
                </div>
            </vs-col>
            <vs-col v-if="isShachihataImage && logoSrcFavoriteInternal" vs-w="8" vs-align="center" vs-type="flex" vs-justify="flex-end" class="p">
                <img class="shachihata_image_size" :src="'data:image/jpeg;base64,' + logoSrcFavoriteInternal" alt="Logo service">
            </vs-col>
        </vs-row>
        <vs-row v-if="showReceivedList">
            <vs-card class = "list-received">
                <vs-table class="mt-3 table-favorite-width"
                          :data="listDataReceived"
                          noDataText="データがありません。"
                          sst stripe
                          @sort="handleSort"
                          @selected="onShowReading">
                    <template slot="thead">
                        <vs-th sort-key="title"
                               class="tex-list-received pr-3">文書名 </vs-th>

                        <vs-th sort-key="update_at"
                               class="tex-list-received width-date">受信日時</vs-th>
                    </template>

                    <template slot-scope="{data}">
                        <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                            <vs-td class="tex-list-received pr-3">
                                {{tr.title}}
                            </vs-td>
                            <vs-td class="tex-list-received width-date">{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</vs-td>
                        </vs-tr>
                    </template>
                </vs-table>
                <div><div class="mt-3 mb-5 whitespace-no-wrap">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
            </vs-card>

        </vs-row>

    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import { CIRCULAR_USER } from '../../enums/circular_user';

export default {
    components: {
        VxPagination,
    },
    name: "CircularComponent",
    props: {
        isShachihataImage: {
            type: Boolean,
        },
        logoSrcFavoriteInternal: {

        },
        issetFavoriteInternal: {

        },
        onSearchAfterSaveFavorite: {

        }

    },
    beforeCreate() {
    },
    async created() {
        if((this.loginUser.isGuestCompany && !this.loginUser.guestCanSubscribeCircular) || this.loginUser.form_user_flg){
            this.allowedUploadDocuments = false;
        }
    },
    beforeMount() {
    },
    mounted() {
        this.CIRCULAR_USER = this.circularUser;
        if(this.issetFavoriteInternal){
            this.onSearch(false);

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
            CIRCULAR_USER: CIRCULAR_USER,
            listFavoriteExternal: [],
            showReceivedList: true,
            listDataReceived: [],
            pagination: {
                totalPage: 0,
                currentPage: 1,
                limit: 10,
                totalItem: 0,
                from: 1,
                to: 10
            },
            status: "",
            filename: "",
            userName: "",
            userEmail: "",
            orderBy: "update_at",
            orderDir: "desc",
            fromdate: "",
            todate: "",
            sender: "",
            allowedUploadDocuments: true,
            loginUser: JSON.parse(getLS('user')),
        }
    },
    methods: {
        ...mapActions({
            getListServiceInternal: "portal/getListServiceInternal",
            search: "circulars/getListReceived",
            getOriginCircularUrl: "circulars/getOriginCircularUrl",
        }),
        async onSearch (resetPaging) {
            let info = {
                status     : this.status,
                filename   : this.filename,
                userName   : this.userName,
                userEmail  : this.userEmail,
                fromdate   : this.fromdate,
                todate     : this.todate,
                sender     : this.sender,
                page       : resetPaging ? 1 : this.pagination.currentPage,
                limit      : this.pagination.limit,
                orderBy    : this.orderBy,
                orderDir   : this.orderDir,
            };
            let data = await this.search(info);
            this.num_unread             = data.num_unread;
            data                        = data.data;
            this.listDataReceived       = data.data;
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
        },
        async onShowReading(tr){
            var getOrigin =  await this.getOriginCircularUrl(tr.id);
            if(!getOrigin.originCircularUrl && getOrigin.hasRequestSendBack){
                this.$router.push('/received-approval-sendback/'+tr.id);
            }else if(!getOrigin.originCircularUrl && !getOrigin.hasRequestSendBack){
                if(tr.circular_status == CIRCULAR_USER.REVIEWING_STATUS){
                    this.$router.push('/received-reviewing/'+tr.id);
                }else if(tr.circular_status == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || tr.circular_status == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS){
                    this.$router.push('/received-view/'+tr.id);
                }else{
                    this.$router.push('/received/'+tr.id);
                }
            }else if(getOrigin.originCircularUrl){
                this.openWindow(getOrigin.originCircularUrl)
            }
        },
        onClickCreateDocument() {
            this.$router.push('/');
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active? "DESC" : "ASC";
            this.onSearch(false);
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
        onSearchAfterSaveFavorite: function (newValue, oldValue) {
            if (newValue === true || newValue === false) {
                this.onSearch(false);
            }
        }
    }
}

</script>

<style scoped>


</style>
