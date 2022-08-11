<template>
    <div id="completed-memo-page">
        <vs-card style="margin-bottom: 0">
            <vs-row class="top-bar">
                <vs-col vs-type="flex" vs-w="9" vs-xs="12" vs-sm="6" vs-align="center" vs-justify="center" class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                    <ul class="breadcrumb">
                        <li><p style="color: #27ae60;"><span class="badge badge-success">1</span> プレビュー・捺印</p></li>
                        <li><p style="color: #0984e3;"><span class="badge badge-primary">2</span> 回覧先設定</p></li>
                        <li><p style="background: #fff"></p></li>
                    </ul>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="3" vs-xs="12" vs-sm="6">
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="primary" type="filled" v-on:click="onBackClick"> 戻る</vs-button>
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="danger" type="filled" v-on:click="onSaveMemoClick"><i class="fas fa-check"></i> 保存</vs-button>
                </vs-col>
            </vs-row>
        </vs-card>
        <div class="vx-row">
            <div class="vx-col mb-4 lg:pr-0 w-full">
                <vx-card :hideLoading="true" class="h-full">
                    <vs-row class="border-bottom pb-4">
                        <h4>宛先、回覧順 <span class="text-danger">*</span></h4>
                    </vs-row>
                    <div class="mail-steps">
                        <div class="mail-list">
                            <p class="mt-2">(全ての回覧後に申請者に戻ります)</p>
                            <template v-if="!isTemplateCircular">
                                <vs-row vs-type="flex" v-for="(formatUser, index) in selectUsers" v-bind:key="formatUser.user.email + index" :index="index">
                                    <vs-col vs-w="12" :class="'item sended ' + (index === 0 ? 'maker ':(formatUser.user.email === selectUsers[0].user.email ? 'me': ''))">
                                        <vs-col vs-w="10">
                                            <span>{{formatUser.user.name}}【{{formatUser.user.email}}】</span>
                                            <!--    <span>{{ arrBtnTitleAccount[formatUser.user.env_flg][formatUser.user.edition_flg] }}</span>-->
                                            <span v-if="index === selectUsers.length - 1" class="final">最終</span>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                            <a class="mr-3" v-if="index === selectUsers.length - 1" href="#"><i class="far fa-flag"></i></a>
                                        </vs-col>
                                    </vs-col>
                                    <vs-col vs-w="12" v-if="currentViewingUser && currentViewingUser.parent_send_order !== 0 && currentViewingUser.parent_send_order == formatUser.user.parent_send_order">
                                        <vs-row :class="'item sended is-child'" vs-type="flex" v-for="(childUser, childIndex) in formatUser.children" v-bind:key="childUser.email + childIndex" :index="childIndex">
                                            <vs-col vs-w="10">
                                                <span>{{childUser.name}}【{{childUser.email}}】</span>
                                                <!--   <span>{{ arrBtnTitleAccount[childUser.env_flg][childUser.edition_flg] }}</span>-->
                                            </vs-col>
                                            <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                <a class="mr-3" v-if="index === selectUsers.length - 1" href="#"> <i class="far fa-flag"></i></a>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                </vs-row>
                            </template>
                            <!-- template route users start -->
                            <vs-row vs-type="flex" v-if="isTemplateCircular">
                                <vs-col vs-w="12" class="item sended maker">
                                    <vs-col vs-w="10">
                                        <span>{{selectUsers[0].user.name}}【{{selectUsers[0].user.email}}】</span>
                                    </vs-col>
                                    <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                    </vs-col>
                                </vs-col>
                            </vs-row>
                            <template v-if="isTemplateCircular">
                                <vs-row vs-type="flex" v-for="(userRoute, index) in templateUserRoutes" :key="index">
                                    <vs-col vs-w="12" class="item sended">
                                        <vs-col vs-w="10">
                                            <template v-for="(user, itemIndex) in userRoute" >
                                                <span :key="itemIndex + user.name">{{user.name}}【{{user.email}}】</span>
                                                <template v-if="itemIndex != userRoute.length - 1">
                                                    <br :key="itemIndex + user.email"/>
                                                </template>
                                            </template>
                                            <span v-if="index === templateUserRoutes.length - 1" class="final">最終</span>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                            <a class="mr-3" v-if="index === templateUserRoutes.length - 1" href="#"><i class="far fa-flag"></i></a>
                                        </vs-col>
                                    </vs-col>
                                </vs-row>
                            </template>
                            <!-- template route users end -->
                        </div>
                    </div>
                </vx-card>
            </div>
            <div class="vx-col w-full mb-0">
                <vx-card class="mb-4">
                    <vs-row class="border-bottom pb-4">
                        <h4>メモ</h4>
                    </vs-row>
                    <vs-row class="mt-6">
                        <vs-textarea placeholder="メモを残せます。" rows="4" v-model="currentViewingUser.memo" />
                    </vs-row>
                </vx-card>
            </div>

        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    import { CIRCULAR } from '../../enums/circular';
    import { CIRCULAR_USER } from '../../enums/circular_user';
    import LiquorTree from 'liquor-tree';
    import { Validator } from 'vee-validate';
    import config from "../../app.config";
    import Axios from "axios";

    const dict = {
        custom: {
            name: {
                required: '* 必須項目です',
            },
            email: {
                required: '* 必須項目です',
                email: "* メールアドレスが正しくありません"
            }
        }
    };
    Validator.localize('ja', dict);

    export default {
        components: {
            [LiquorTree.name]: LiquorTree,
        },
        directives: {

        },
        data() {
            return {
                CIRCULAR: CIRCULAR,
                CIRCULAR_USER: CIRCULAR_USER,
                finishedDate: '',
            }
        },
        computed: {
            ...mapState({
                circular: state => state.home.circular,
                currentViewingUser: state => state.home.currentViewingUser
            }),
            selectUsers: {
                get() {
                    if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                    const circularUsers = this.$store.state.home.circular.users.slice();
                    const formatCircularUsers = [];
                    let formatCircularUser = {};
                    formatCircularUser.user = circularUsers.length ? circularUsers[0] : null;
                    formatCircularUser.children = [];
                    formatCircularUsers.push(...circularUsers.filter(item => {return (this.currentViewingUser && this.currentViewingUser.parent_send_order === 0) ? item.parent_send_order === 0 : item.child_send_order === 0}).map(item => {
                        return {user: item, children: []};
                    }));
                    let old_parent_send_order = circularUsers.length ? circularUsers[0].parent_send_order : null;
                    for(let circularUser of circularUsers) {
                        if(old_parent_send_order !== circularUser.parent_send_order) {
                            if(!formatCircularUser.children) formatCircularUser.children = [];
                            if(formatCircularUser.user.parent_send_order) formatCircularUsers.push(formatCircularUser);
                            old_parent_send_order = circularUser.parent_send_order;
                            formatCircularUser = {};
                            formatCircularUser.children = [];
                        }

                        // 20200512 fix PAC_5-170 違う環境間での回覧で宛先情報が表示されさない
                        if(this.userHashInfo) {
                            if(circularUser.parent_send_order > 0 && circularUser.child_send_order > 1 && this.userHashInfo.mst_company_id == circularUser.mst_company_id && circularUser.env_flg == config.APP_SERVER_ENV && circularUser.edition_flg == config.APP_EDITION_FLV && circularUser.server_flg == config.APP_SERVER_FLG) {
                                formatCircularUser.children.push(circularUser);
                            }
                        }else{
                            const loggedUser = JSON.parse(getLS('user'));
                            if(circularUser.parent_send_order > 0 && circularUser.child_send_order > 1 && loggedUser.mst_company_id == circularUser.mst_company_id && circularUser.env_flg == config.APP_SERVER_ENV && circularUser.edition_flg == config.APP_EDITION_FLV && circularUser.server_flg == config.APP_SERVER_FLG) {
                                formatCircularUser.children.push(circularUser);
                            }
                        }

                        if(circularUser.parent_send_order > 0 && circularUser.child_send_order === 1) {
                            formatCircularUser.user = circularUser;
                        }
                    }
                    if(formatCircularUser.user.parent_send_order) formatCircularUsers.push(formatCircularUser);

                    return formatCircularUsers;
                }
            },
            isTemplateCircular: {
                get() {
                    let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                    let cnt = arrUsers.findIndex(function($item){
                        return (Object.prototype.hasOwnProperty.call($item, "user_routes_id") && $item.user_routes_id != null);
                    });
                    return cnt >= 0 ? true : false;
                },
            },
            templateUserRoutes: {
                get() {
                    let newArrUsers = [];
                    if(this.isTemplateCircular){
                        // 合議の場合、同じ企業、parent_send_order同じです
                        let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                        for(let i = 1;i < arrUsers.length;i ++){
                            let child_send_order = arrUsers[i].child_send_order - 1;
                            if(!Object.prototype.hasOwnProperty.call(newArrUsers, child_send_order)){
                                newArrUsers[child_send_order] = [];
                            }
                            arrUsers[i]['user_routes_name'] = JSON.parse(arrUsers[i].detail).summary;
                            newArrUsers[child_send_order].push(arrUsers[i]);
                        }
                    }
                    return newArrUsers;
                },
            },
        },
        methods: {
            ...mapActions({
                updateViewingUser: "viewingUser/updateViewingUser",
            }),
            onBackClick: async function() {
                this.$router.back()
            },
            async onSaveMemoClick(){
                await this.updateViewingUser(this.currentViewingUser, this.currentViewingUser.id, this.finishedDate);
                this.$router.back()
            },
        },
        async created() {
            this.finishedDate = localStorage.getItem('finishedDate');
            if(!this.$store.state.home.currentViewingUser || !this.$store.state.home.circular) {
                this.$router.push('/');
            }
        }
    }
</script>
