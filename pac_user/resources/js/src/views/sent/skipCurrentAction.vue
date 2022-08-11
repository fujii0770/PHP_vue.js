<template>
    <div>
        <div id="sendback-page">
            <vs-card style="margin-bottom: 0">
                <vs-row class="top-bar">
                    <vs-col vs-type="flex" vs-w="9" vs-xs="12" vs-sm="6" vs-align="center" vs-justify="center"
                            class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                        <ul class="breadcrumb">
                            <li><p style="color: #27ae60;"><span class="badge badge-success">1</span> プレビュー・捺印</p></li>
                            <li><p style="color: #0984e3;"><span class="badge badge-primary">2</span> 回覧先設定</p></li>
                            <li><p style="background: transparent"></p></li>
                        </ul>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="3" vs-xs="12" vs-sm="6">
                        <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" color="primary"
                                   type="filled"
                                   v-on:click="onBackClick"> 戻る
                        </vs-button>
                        <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" color="danger"
                                   type="filled"
                                   :disabled="clickState" v-on:click="onSendClick"><i class="fas fa-check"></i> スキップ
                        </vs-button>
                    </vs-col>
                </vs-row>
            </vs-card>
            <div class="vx-row">
                <div class="vx-col mb-4 lg:pr-0 w-full">
                    <vx-card class="h-full">
                        <vs-row class="border-bottom pb-4">
                            <h4>スキップ <span class="text-danger">*</span></h4>
                        </vs-row>
                        <vs-table class="w-full mt-4" ref="table" :data="selectUsers" v-if="!isTemplateCircular">
                            <template slot="thead">
                                <vs-th>回覧順</vs-th>
                                <vs-th>名前</vs-th>
                                <vs-th>メールアドレス</vs-th>
                            </template>

                            <template slot-scope="{data}">
                                <tbody>
                                <tr :class="(
                                        ((nextCircularUser.parent_send_order == tr.parent_send_order &&  tr.child_send_order < nextCircularUser.child_send_order)
                                        ||
                                        (nextCircularUser.parent_send_order > tr.parent_send_order))? ' disabled' : '') + (
                                        (tr.parent_send_order == circularUserLastSend.parent_send_order && tr.child_send_order == circularUserLastSend.child_send_order + 1)
                                        ||
                                        (
                                            (tr.parent_send_order == circularUserLastSend.parent_send_order + 1 && tr.child_send_order == 1 )
                                            &&
                                            !(selectUsers[indextr - 1].parent_send_order == circularUserLastSend.parent_send_order
                                            &&
                                            (selectUsers[indextr - 1].child_send_order == circularUserLastSend.child_send_order + 1 || selectUsers[indextr - 1].child_send_order >= circularUserLastSend.child_send_order) )
                                        )
                                        ||
                                        (nextCircularUser.parent_send_order == finalCircular.parent_send_order && nextCircularUser.child_send_order == finalCircular.child_send_order && tr.parent_send_order == finalCircular.parent_send_order && tr.child_send_order == finalCircular.child_send_order)
                                        ||
                                        (nextNodeUser && nextNodeUser.parent_send_order == tr.parent_send_order && nextNodeUser.child_send_order == tr.child_send_order)

                                        ? ' row-selected': '')"
                                    :data="tr" :key="indextr" v-for="(tr, indextr) in data"
                                    style="cursor: not-allowed;box-shadow: none;">
                                    <vs-td>
                                        <p class="product-name font-medium truncate">#{{ indextr + 1 }}&nbsp;
                                            <a v-if="tr.parent_send_order == nextCircularUser.parent_send_order && tr.child_send_order == nextCircularUser.child_send_order"
                                               class="ml-1" href="#"> <i
                                                class="far fa-flag"></i></a>
                                            <span v-if="data.length == (indextr+1) "
                                                  style="border: 1px solid #e1ba53;font-size: 10px;color: #e1ba53;background-color: #fff;-webkit-border-radius: 4px;padding: 0 5px;margin-right: 5px;white-space: nowrap;"
                                            >{{
                                                    nextCircularUser.parent_send_order == finalCircular.parent_send_order && nextCircularUser.child_send_order == finalCircular.child_send_order ? "完了" : "最終"
                                                }}</span>
                                        </p>
                                    </vs-td>

                                    <vs-td>
                                        <p class="product-category" v-for="(u,i) in tr.plan_users" :key="i + u.name">{{ u.name }}</p>
                                        <p class="product-category" v-if="!tr.plan_users">{{ tr.name }}</p>
                                    </vs-td>

                                    <vs-td>
                                        <p class="product-category" v-for="(u,i) in tr.plan_users" :key="i + u.email">{{ u.email }}</p>
                                        <p class="product-category" v-if="!tr.plan_users">{{ tr.email }}</p>
                                    </vs-td>

                                </tr>
                                </tbody>
                            </template>
                        </vs-table>
                        <!-- template route users start -->
                        <vs-table class="w-full mail-steps" :data="templateUserRoutesWithApplicant"
                                  v-if="isTemplateCircular">
                            <template slot="thead">
                                <vs-th>回覧順</vs-th>
                                <vs-th>名前</vs-th>
                                <vs-th>メールアドレス</vs-th>
                            </template>

                            <template slot-scope="{data}">
                                <tbody>
                                <template v-for="(trs, indextr) in data">
                                    <tr :class="(
                                                ((nextCircularUser.parent_send_order == trs[trs.length - 1].parent_send_order &&  trs[trs.length - 1].child_send_order < nextCircularUser.child_send_order)
                                                ||
                                                (nextCircularUser.parent_send_order > trs[trs.length - 1].parent_send_order))? ' disabled' : '') + (
                                                (trs[trs.length - 1].parent_send_order == circularUserLastSend.parent_send_order && trs[trs.length - 1].child_send_order == circularUserLastSend.child_send_order + 1)
                                                ||
                                                (trs[trs.length - 1].parent_send_order == circularUserLastSend.parent_send_order + 1 && trs[trs.length - 1].child_send_order == 1)
                                                ||
                                                (
                                                    nextCircularUser.parent_send_order == finalCircular.parent_send_order && nextCircularUser.child_send_order == finalCircular.child_send_order
                                                    &&
                                                    trs[trs.length - 1].parent_send_order == finalCircular.parent_send_order && trs[trs.length - 1].child_send_order == finalCircular.child_send_order
                                                )
                                         ? ' row-selected': '')"
                                        :data="trs" :key="indextr" style="cursor: not-allowed;box-shadow: none;">

                                        <vs-td>
                                            <p class="product-name font-medium truncate">#{{ indextr + 1 }}&nbsp;
                                                <a v-if="trs[0].parent_send_order == nextCircularUser.parent_send_order && trs[0].child_send_order == nextCircularUser.child_send_order"
                                                   class="ml-1" href="#"> <i
                                                    class="far fa-flag"></i></a>
                                                <span v-if="data.length == (indextr+1) "
                                                      style="border: 1px solid #e1ba53;font-size: 10px;color: #e1ba53;background-color: #fff;-webkit-border-radius: 4px;padding: 0 5px;margin-right: 5px;white-space: nowrap;"
                                                >{{
                                                        nextCircularUser.parent_send_order == finalCircular.parent_send_order && nextCircularUser.child_send_order == finalCircular.child_send_order ? "完了" : "最終"
                                                    }}</span>
                                            </p>
                                        </vs-td>

                                        <vs-td>
                                            <template v-for="(tr, index) in trs">
                                                <p class="product-category" :key="index + tr.name">{{ tr.name }}</p>
                                            </template>
                                        </vs-td>

                                        <vs-td>
                                            <template v-for="(tr, index) in trs">
                                                <p class="product-category" :key="index + tr.email">{{ tr.email }}</p>
                                            </template>
                                        </vs-td>
                                    </tr>
                                </template>
                                </tbody>
                            </template>
                        </vs-table>
                        <!-- template route users end -->
                    </vx-card>
                </div>
                <div class="vx-col w-full mb-0">
                    <vx-card class="mb-4">
                        <vs-row class="border-bottom pb-4">
                            <h4>件名・メッセージ</h4>

                        </vs-row>
                        <vs-row class="mt-6">
                            <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="emailContent"/>
                        </vs-row>
                        <vs-row class="mb-6">
                            <!--PAC_5-1413 欄外クリック時にボックスが閉じない　vuesax側のバグの為、vue selectに切り替え-->
                            <div class="w-full">
                                <v-select :options="emailTemplateOptions" :clearable="false" :searchable="false"
                                          :value="selectedComment" @input="onChangeEmailTemplate"/>
                            </div>
                        </vs-row>
                        <!--                    <vs-checkbox :value="addToCommentsFlg" v-on:click="addToCommentsFlg = !addToCommentsFlg">社内のみ閲覧可</vs-checkbox>-->
                        <vs-row>※メッセージは次の回覧者への送信メールに記載されます。<br/>
                            　また、プレビュー画面「コメント」タブの「社内宛」に表示されます。
                        </vs-row>

                    </vx-card>
                </div>

            </div>
            <!-- 操作が失敗しました、一覧から再操作をお願いします。 -->
            <modal name="save-file-fail-notice-modal"
                   :pivot-y="0.2"
                   :width="400"
                   :classes="['v--modal', 'save-file-fail-notice-modal', 'p-4']"
                   :height="'auto'"
                   :clickToClose="false">
                <vs-row>
                    <vs-col vs-w="12" vs-type="block">
                        <p>操作が失敗しました、一覧から再操作をお願いします。また問題がありましたら、管理者へご連絡をお願いします。</p>
                    </vs-col>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled"
                               v-on:click="onSaveFileFailNoticeClick"> 閉じる
                    </vs-button>
                </vs-row>
            </modal>
            <!-- ほかのユーザーのよって操作されました。再度文書を開きなおしてください。 -->
            <modal name="sync-operation-modal"
                   :pivot-y="0.2"
                   :width="400"
                   :classes="['v--modal', 'sync-operation-modal', 'p-4']"
                   :height="'auto'"
                   :clickToClose="false">
                <vs-row>
                    <vs-col vs-w="12" vs-type="block">
                        <p>ほかのユーザーのよって操作されました。再度文書を開きなおしてください。</p>
                    </vs-col>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSyncOperationClick"> 閉じる
                    </vs-button>
                </vs-row>
            </modal>
        </div>

        <!-- 5-277 mobile html -->
        <div id="sendback-page-mobile">
            <div style="width:100%;margin-bottom:10px;">
                <span @click="onBackClick"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
                <div style="display:inline;position:relative;top:-10px;">プレビュー・捺印</div>
            </div>
            <div class="vx-row">
                <vs-col vs-align="left" vs-justify="left" class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                    <ul class="breadcrumb">
                        <li><p><span class="badge">1</span> プレビュー・捺印</p></li>
                        <li><p><span class="badge">2</span> 差戻し先設定</p></li>
                    </ul>
                </vs-col>
            </div>
            <div class="vx-row">
                <div class="vx-col mb-4 lg:pr-0 w-full">
                    <vs-row class="border-bottom pb-4">
                        <h4>差戻し先・回覧順</h4>
                    </vs-row>
                    <vx-card>
                        <vs-table class="w-full mail-steps" :data="selectUsers">
                            <template slot-scope="{data}">
                                <tbody>
                                <tr @click="onRowClick(tr)"
                                    :class="['item ',((((circularUserLastSend.parent_send_order - tr.parent_send_order) == 1 && circularUserLastSend.child_send_order == 1)|| (circularUserLastSend.parent_send_order == tr.parent_send_order && tr.child_send_order < circularUserLastSend.child_send_order)) ? '' : ' disabled') + (tr.id === rowSelected ? ' row-selected': '')]"
                                    :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <vs-td>
                                        <p class="product-category" v-for="(u,i) in tr.plan_users" :key="i + u.name">{{ u.name }}</p>
                                        <p class="product-category" v-if="!tr.plan_users">{{ tr.name }}</p>
                                    </vs-td>

                                    <vs-td>
                                        <p class="product-category" v-for="(u,i) in tr.plan_users" :key="i + u.email">{{ u.email }}</p>
                                        <p class="product-category" v-if="!tr.plan_users">{{ tr.email }}</p>
                                    </vs-td>
                                </tr>
                                </tbody>
                            </template>
                        </vs-table>
                    </vx-card>
                </div>
                <div class="vx-col w-full mb-0">
                    <vs-row class="pb-4 pt-4">
                        <h4>メッセージ</h4>
                    </vs-row>
                    <vs-row>
                        <vs-textarea class="bg-white" placeholder="コメントをつけて送信できます。" rows="4" v-model="emailContent"/>
                    </vs-row>
                </div>
                <div class="vx-col w-full mb-0">
                    <vs-row class="mt-4">
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="3" vs-xs="12" vs-sm="6">
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" color="primary"
                                       type="filled"
                                       v-on:click="onBackClick">
                                <div><img :src="require('@assets/images/mobile/back_white.svg')"></div>
                                <div>戻る</div>
                            </vs-button>
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" color="primary"
                                       type="filled"
                                       v-on:click="onSendClick">
                                <div><img :src="require('@assets/images/mobile/refund_white.svg')"></div>
                                <div>差戻し</div>
                            </vs-button>
                        </vs-col>
                    </vs-row>
                </div>
            </div>
            <div class="sendback_box" style="display: none;">
                <div><img :src="require('@assets/images/mobile/refund_white_big.svg')"></div>
                <div>差戻しました</div>
            </div>
            <!-- 操作が失敗しました、一覧から再操作をお願いします。 -->
            <modal name="save-file-fail-notice-modal"
                   :pivot-y="0.2"
                   :width="300"
                   :classes="['v--modal', 'save-file-fail-notice-modal', 'p-4']"
                   :height="'auto'"
                   :clickToClose="false">
                <vs-row>
                    <vs-col vs-w="12" vs-type="block">
                        <p>操作が失敗しました、一覧から再操作をお願いします。また問題がありましたら、管理者へご連絡をお願いします。</p>
                    </vs-col>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled"
                               v-on:click="onSaveFileFailNoticeClick"> 閉じる
                    </vs-button>
                </vs-row>
            </modal>
            <!-- ほかのユーザーのよって操作されました。再度文書を開きなおしてください。 -->
            <modal name="sync-operation-modal"
                   :pivot-y="0.2"
                   :width="300"
                   :classes="['v--modal', 'sync-operation-modal', 'p-4']"
                   :height="'auto'"
                   :clickToClose="false">
                <vs-row>
                    <vs-col vs-w="12" vs-type="block">
                        <p>ほかのユーザーのよって操作されました。再度文書を開きなおしてください。</p>
                    </vs-col>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSyncOperationClick"> 閉じる
                    </vs-button>
                </vs-row>
            </modal>
        </div>
    </div>
</template>

<script>
import {mapState, mapActions} from "vuex";
import {CIRCULAR} from '../../enums/circular';
import {CIRCULAR_USER} from '../../enums/circular_user';
import LiquorTree from 'liquor-tree';
import {Validator} from 'vee-validate';
import config from "../../app.config";
import Axios from "axios";

import Utils from '../../utils/utils';
import _ from "lodash";

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
    directives: {},
    data() {
        return {
            CIRCULAR: CIRCULAR,
            CIRCULAR_USER: CIRCULAR_USER,
            emailTemplateOptions: [],
            optionSelected: '',
            emailTitle: '',
            emailContent: '',
            rowSelected: 0,
            selected: null,
            previousRoute: null,
            // addToCommentsFlg: false,
            clickState: false,
            selectedComment: '',
            loginUser: JSON.parse(getLS('user')),
            nextCircularUser: null,
            finalCircular: null,
            currentCheckUser: null,
            newAllUser: null,
            newCircularPlans: null,
            nextNodeUser: null,
        }
    },
    beforeRouteEnter(to, from, next) {
        next((vm) => {
            if (from.path != '/') {
                vm.previousRoute = from;
            }
        });
    },
    computed: {
        ...mapState({
            fileSelected: state => state.home.fileSelected,
            circular: state => state.home.circular,
            departmentUsers: state => state.application.departmentUsers,
        }),
        circularUserLastSend() {
            if (!this.circular || !this.circular.users) {
                return null;
            }
            // 合議の場合
            let circular_user = null;
            if (this.loginUser && this.isTemplateCircular) {
                // 終わった場合
                if (this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_STATUS || this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS) {
                    circular_user = null;
                } else {
                    let circular_users = [];
                    // 差戻のcircular_user
                    let circular_user_send_back = this.newAllUser.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                    // 差戻の場合
                    if (circular_user_send_back) {
                        // 差戻 同級のメール 除外する
                        circular_users = this.newAllUser.slice().filter(item => (item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS) && item.child_send_order !== circular_user_send_back.child_send_order);
                    } else {
                        circular_users = this.newAllUser.slice().filter(item => (item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS));
                    }

                    // get 処理中 child_send_order
                    let distinct_child_orders = [];
                    for (let i = 0; i < circular_users.length; i++) {
                        if (!distinct_child_orders.includes(circular_users[i].child_send_order)) {
                            // すべてのユーザ
                            let all_arr = this.newAllUser.slice().filter(item => item.child_send_order === circular_users[i].child_send_order);
                            // 承認のユーザ
                            let approved_arr = this.newAllUser.slice().filter(item => (item.child_send_order === circular_users[i].child_send_order && (item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS)));
                            if (all_arr.length > 1) {
                                // all承認
                                if (approved_arr.length == all_arr.length) {
                                    continue;
                                } else {
                                    // 待つ
                                    if (all_arr[0].wait == 1) {
                                        // 一つのノードが複数存在するからです。 loginUserのemail 選択  && item.email === this.loginUser.email
                                        let cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.loginUser.email);
                                        if (cir !== undefined) {
                                            circular_user = cir;
                                        } else {
                                            circular_user = circular_users[i];
                                        }
                                        break;
                                    } else if (all_arr[0].wait == 0) {
                                        if (approved_arr.length >= all_arr[0].score) {
                                            continue;
                                        } else {
                                            // 一つのノードが複数存在するからです。 loginUserのemail 選択  && item.email === this.loginUser.email
                                            let cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.loginUser.email);
                                            if (cir !== undefined) {
                                                circular_user = cir;
                                            } else {
                                                circular_user = circular_users[i];
                                            }
                                            break;
                                        }
                                    }
                                }
                            } else {
                                circular_user = circular_users[i];
                                break;
                            }
                            distinct_child_orders[distinct_child_orders.length + 1] = circular_users[i].child_send_order;
                        }
                    }
                }
            } else {
                circular_user = this.newAllUser.slice().reverse().find(item => {
                    return item.circular_status == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS
                        || item.circular_status == CIRCULAR_USER.READ_STATUS
                        || item.circular_status == CIRCULAR_USER.PULL_BACK_TO_USER_STATUS
                        || item.circular_status == CIRCULAR_USER.REVIEWING_STATUS
                });
            }
            if (!circular_user) return null;
            this.$store.commit('home/updateCurrentParentSendOrder', circular_user ? circular_user.parent_send_order : 0);
            return circular_user;
        },
        allowAddDestination: {
            get() {
                if (!this.circular) return false;
                return this.circular.address_change_flg;
            }
        },
        selectUsers: {
            get() {
                if (!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                if (!this.circularUserLastSend) return [];
                // TODO review
                let plan = _.cloneDeep(this.newCircularPlans);
                let circularUsers = _.cloneDeep(this.newAllUser)
                circularUsers.forEach(user => {
                    if (plan[user.plan_id] && plan[user.plan_id].id == user.plan_id) {
                        plan[user.plan_id].users = plan[user.plan_id].users || [];
                        plan[user.plan_id].users.push(Object.assign({}, user));
                        plan[user.plan_id].is_add = false;
                    }
                })
                let newCircularUsers = circularUsers.map(user => {
                    if (plan[user.plan_id] && plan[user.plan_id].id == user.plan_id) {
                        if (plan[user.plan_id].is_add) {
                            return null
                        } else {
                            user.plan_mode = plan[user.plan_id].mode
                            user.plan_score = plan[user.plan_id].score
                            user.plan_users = plan[user.plan_id].users
                            plan[user.plan_id].is_add = true
                            return user
                        }
                    } else {
                        return user
                    }
                }).filter(user => {
                    return user != null
                })
                let users = newCircularUsers;

                return users
            },
            set(value) {
                this.updateCircularUsers(value)
            }
        },
        isTemplateCircular: {
            get() {
                let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                let cnt = arrUsers.findIndex(function ($item) {
                    return (Object.prototype.hasOwnProperty.call($item, "user_routes_id") && $item.user_routes_id != null);
                });
                return cnt >= 0 ? true : false;
            },
        },
        templateUserRoutesWithApplicant: {
            get() {
                let newArrUsers = [];
                if (this.isTemplateCircular) {
                    // 合議の場合、同じ企業、parent_send_order同じです
                    let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                    for (let i = 0; i < arrUsers.length; i++) {
                        let child_send_order = arrUsers[i].child_send_order;
                        if (!Object.prototype.hasOwnProperty.call(newArrUsers, child_send_order)) {
                            newArrUsers[child_send_order] = [];
                        }
                        // user_routes_name
                        // if(arrUsers[i].hasOwnProperty("detail")){
                        //     arrUsers[i]['user_routes_name'] = JSON.parse(arrUsers[i].detail).summary;
                        // }
                        newArrUsers[child_send_order].push(arrUsers[i]);
                    }
                }
                return newArrUsers;
            },
            set(value) {
                this.updateCircularUsers(value)
            }
        },
    },
    methods: {
        ...mapActions({
            saveFileAndSignature: "home/saveFileAndSignature",
            sendBack: "application/sendBack",
            addLogOperation: "logOperation/addLog",
            getMyInfo: "user/getMyInfo",
            getInfoByHash: "user/getInfoByHash",
            sendNotifyContinue: "application/sendNotifyContinue",
        }),
        handlerCircularUserSortAndRealUser() {
            //  copy circular plans
            let newCircularPlans = ((this.$store.state.home.circular.plans));

            // copy circular  all user
            this.newAllUser = this.$store.state.home.circular.users;
            if (this.isTemplateCircular) {
                this.newCircularPlans = newCircularPlans;
                return
            }
            let newCircularUsers = _.cloneDeep(this.newAllUser)

            // copy all User  find all first  child_send_order
            let nodeUser = newCircularUsers.filter(item => {
                return (item.child_send_order == 0 && item.parent_send_order == 0) || (item.child_send_order == 1 && item.parent_send_order > 0);
            });

            let arrSameNode = [];
            let newFirstNode = [];
            // retain first node
            for (let i = 0; i < nodeUser.length; i++) {
                if (!newFirstNode[nodeUser[i].parent_send_order]) {
                    newFirstNode[nodeUser[i].parent_send_order] = nodeUser[i];
                }
            }

            /**
             *  parent_send_order ->  current parent_send_order
             *  maxChildNum -> current node's last child max count
             *  child -> [circular->obj]
             *
             */
            for (let i = 0; i < newFirstNode.length; i++) {
                // find the current parent_send_order 's child node
                let newFindChild = newCircularUsers.filter(item => item.parent_send_order == newFirstNode[i].parent_send_order);
                // sort
           
                // Get last child
                let intMaxChild = _.cloneDeep(newFindChild).pop().child_send_order
                // make obj
                let currentObj = new Object();
                currentObj.parent_send_order = newFirstNode[i].parent_send_order;
                currentObj.child = {};
                currentObj.maxChildNum = intMaxChild;
                // loop index
                let findNum = currentObj.parent_send_order == 0 ? 0 : 1;
                // loop count
                let loopNum = intMaxChild + (currentObj.parent_send_order == 0 ? 1 : 0)
                for (let i = 0; i < loopNum; i++) {
                    let tmpCurrentFindNode = newFindChild.filter(item => item.child_send_order == findNum);
                    if (!currentObj.child[findNum]) {
                        currentObj.child[findNum] = [];
                    }
                    currentObj.child[findNum].push(...tmpCurrentFindNode);
                    findNum += 1;

                }
                arrSameNode.push(currentObj);
            }
            // copy all plans wait insert
            newCircularPlans = _.cloneDeep(newCircularPlans);
            for (let i = 0; i < arrSameNode.length; i++) {
                let nodeChild = arrSameNode[i].child;


                let firstNodeData = arrSameNode[i].parent_send_order == 0 ? nodeChild[0] : nodeChild[1];
                let firstNode = _.cloneDeep(firstNodeData).shift();
                if (Array.isArray(firstNode)) {
                    firstNode = firstNode.shift();
                }

                if (firstNode.return_flg == 1 && Object.keys(arrSameNode[i].child).length > 1 && newFirstNode.length > 1) {

                    // the last node
                    let currentLastNode = arrSameNode[i].child[arrSameNode[i].maxChildNum];
                    // the next node
                    let nextNode = arrSameNode[i + 1] ? arrSameNode[i + 1].child[1] : [];
                    if(nextNode && Array.isArray(nextNode)){
                        nextNode = _.cloneDeep(nextNode).shift();
                    }
                    let lastNode = currentLastNode;
                    if(lastNode && Array.isArray(lastNode)){
                        lastNode = _.cloneDeep(lastNode).shift();
                    }

                    let realStatusFlg = CIRCULAR_USER.NOT_NOTIFY_STATUS;

                    if(nextNode){
                        // 1 2 3 4 10 11
                        if(this.in_array(nextNode.circular_status,[CIRCULAR_USER.NOTIFIED_UNREAD_STATUS,CIRCULAR_USER.READ_STATUS,CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS,CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS,CIRCULAR_USER.NODE_COMPLETED_STATUS,CIRCULAR_USER.REVIEWING_STATUS])){
                            realStatusFlg = CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS;
                        }
                        // 5 6 7 8
                        if(this.in_array(nextNode.circular_status,[CIRCULAR_USER.SEND_BACK_STATUS,CIRCULAR_USER.END_OF_REQUEST_SEND_BACK,CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK,CIRCULAR_USER.PULL_BACK_TO_USER_STATUS])){
                            realStatusFlg = CIRCULAR_USER.NOT_NOTIFY_STATUS;
                        }
                    }

                    if(firstNode.circular_status == CIRCULAR_USER.REVIEWING_STATUS){
                        realStatusFlg = CIRCULAR_USER.NOTIFIED_UNREAD_STATUS;
                    }


                    arrSameNode[i].maxChildNum += 1
                    let copyCurrentNode = _.cloneDeep(firstNodeData)

                    if(realStatusFlg == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS){
                        firstNodeData.filter(item =>{
                            item.circular_status = CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS
                        });
                    }

                    copyCurrentNode.forEach(item => {
                        item.circular_status = realStatusFlg;
                        item.child_send_order = arrSameNode[i].maxChildNum
                        if (item.plan_id) {

                            let currentPlan = _.cloneDeep(newCircularPlans[item.plan_id]);
                            item.plan_id = item.plan_id + 1000;
                            currentPlan.id = item.plan_id
                            newCircularPlans[item.plan_id] = currentPlan
                        }
                    });
                    let nextChildIndex = Object.keys(arrSameNode[i].child).length + 1;
                    arrSameNode[i].child[nextChildIndex] = [];
                    arrSameNode[i].child[nextChildIndex].push(...copyCurrentNode);

                }
            }
         

            let newAllUser = [];

            for (let i = 0; i < arrSameNode.length; i++) {
                let currentKeys = Object.keys(arrSameNode[i].child);
                for (let j = 0; j < currentKeys.length; j++) {
                    newAllUser.push(...arrSameNode[i].child[currentKeys[j]]);
                }
            }

            this.newAllUser = newAllUser
            // this.newAllUser = this.newAllUser.sort(function (a, b) {
            //     return parseFloat(a.parent_send_order + '.' + a.child_send_order) - parseFloat(b.parent_send_order + '.' + b.child_send_order)
            // });
            this.newCircularPlans = newCircularPlans;
        },
        in_array(search,array){
            for(var i in array){
                if(array[i]==search){
                    return true;
                }
            }
            return false;
        },
        onChangeEmailTemplate(value) {
            this.selectedComment = value;
            this.emailContent = this.emailContent.concat(value);
            this.selectedComment = value + ' '
            //this.optionSelected = '';
        },
        onBackClick: async function () {
            if (this.previousRoute) {
                this.previousRoute
                this.$router.push(this.previousRoute);
            } else {
                this.$router.back()
            }
        },
        onSendClick: async function () {
            //二重チェック
            this.clickState = true;
            this.$store.commit('home/checkCircularUserNextSend', false);

            // 最初回覧文書取得
            let circular_document_id = 0;
            this.$store.state.home.files.forEach(function (item) {
                if (circular_document_id == 0 || circular_document_id > item.circular_document_id) {
                    circular_document_id = item.circular_document_id;
                }
            });


            const sendNotifyContinue = async () => {
                const data = {
                    userViews: [],
                    title: '',
                    text: this.emailContent,
                    add_stamp: false,
                    sender_id: this.rowSelected ?? null,
                    operationNotice: '',
                    outsideAccessCode: '',
                    circular_document_id: circular_document_id,
                    isTemplateCircular: this.isTemplateCircular,
                    skipCurrentHandler: true,
                    skipCurrentUser: this.circularUserLastSend,
                };

                await this.sendNotifyContinue(data).then(ret => {
                    if (ret) {
                        var self = this;
                        $(".sendback_box").show();
                        setTimeout(function () {
                            if (self.$store.state.home.usingPublicHash) {
                                if (window.opener) {
                                    window.close();
                                } else {
                                    window.location.href = config.LOCAL_API_URL;
                                }
                            } else {
                                self.$route.meta.isKeep = true
                                self.$router.push('/sent');
                            }
                        }, 1000);
                    }
                });

                if (!this.$store.state.home.usingPublicHash && !this.info.operation_notice_flg) {
                    this.$route.meta.isKeep = true
                    this.$router.push('/sent');
                }
            }
            await sendNotifyContinue();
        },
        onSaveFileFailNoticeClick: async function () {
            if (this.$store.state.home.usingPublicHash) {
                if (window.opener) {
                    window.close();
                } else {
                    if (this.userHashInfo) {
                        if (parseInt(this.userHashInfo.current_env_flg)) {
                            window.location.href = config.K5_API_URL;
                        } else {
                            if (parseInt(this.userHashInfo.current_edition_flg)) {
                                window.location.href = config.AWS_API_URL;
                            } else {
                                window.location.href = config.OLD_AWS_API_URL;
                            }
                        }
                    } else {
                        window.location.href = config.LOCAL_API_URL;
                    }
                }
            } else {
                this.$route.meta.isKeep = true
                this.$router.push('/received');
            }
        },
        onSyncOperationClick: async function () {
            if (this.$store.state.home.usingPublicHash) {
                if (window.opener) {
                    window.close();
                } else {
                    if (this.userHashInfo) {
                        if (parseInt(this.userHashInfo.current_env_flg)) {
                            window.location.href = config.K5_API_URL;
                        } else {
                            if (parseInt(this.userHashInfo.current_edition_flg)) {
                                window.location.href = config.AWS_API_URL;
                            } else {
                                window.location.href = config.OLD_AWS_API_URL;
                            }
                        }
                    } else {
                        window.location.href = config.LOCAL_API_URL;
                    }
                }
            } else {
                this.$route.meta.isKeep = true
                this.$router.push('/received');
            }
        },
    },
    async mounted() {
        if (!this.$store.state.home.usingPublicHash) {
            this.info = await this.getMyInfo();
            this.emailTemplateOptions = Utils.setEmailTemplateOptions(this.info);
        }
    },

    async created() {
        if (!this.$store.state.home.circular || this.$store.state.home.circular.circular_status === this.CIRCULAR.SAVING_STATUS) {
            this.onBackClick();
        }
        const hash = this.$route.params.hash;
        if (hash) {
            localStorage.setItem('tokenPublic', hash);
            this.$store.commit('home/setUsingPublicHash', true);
            this.userHashInfo = await this.getInfoByHash();
            this.emailTemplateOptions = Utils.setEmailTemplateOptions(this.userHashInfo);
        }

        if (this.$store.state.home.circular.users && this.$store.state.home.circular.users.length <= 0) {
            this.onBackClick();
        }
        this.handlerCircularUserSortAndRealUser();
        let circularUserFilter = this.newAllUser.slice().filter(item =>
            item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS
            ||
            item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS
        );


        let lastCircularUser = circularUserFilter.pop();


        if (this.newAllUser[0].email != this.loginUser.email) {
            this.onBackClick();
        }

        if (this.newAllUser && this.newAllUser.length > 0) {
            const find = this.newAllUser.filter(item =>
                this.circularUserLastSend && ((
                    item.parent_send_order == this.circularUserLastSend.parent_send_order
                    &&
                    item.child_send_order == this.circularUserLastSend.child_send_order))
                    &&
                    !(item.circular_status == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS)
            );
            this.nextCircularUser = find ? find.pop() : null;
            this.finalCircular = this.newAllUser[this.newAllUser.length - 1];
            this.rowSelected = this.nextCircularUser ? this.nextCircularUser.id : 0;
            this.currentCheckUser = this.newAllUser.filter(item => item.circular_status == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS).pop();


            this.nextNodeUser = this.newAllUser.slice().find(item =>item.child_send_order === this.nextCircularUser.child_send_order + 1 && item.parent_send_order === this.nextCircularUser.parent_send_order);
            if(!this.nextNodeUser){
                this.nextNodeUser = this.newAllUser.slice().find(item =>item.child_send_order == 1  && item.parent_send_order === this.nextCircularUser.parent_send_order + 1);
            }
        }
    }
}
</script>
