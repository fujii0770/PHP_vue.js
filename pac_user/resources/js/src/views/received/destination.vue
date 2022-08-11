<template>
    <div>
        <div id="circular-destination-page" :class="isMobile?'mobile':''">
            <div style="margin-bottom: 15px">
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
                        <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                   style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled"
                                   v-on:click="onBackClick"> 戻る
                        </vs-button>
                        <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                   style="color:#fff;" color="#22AD38" type="filled"
                                   v-on:click="onApprovalReviewing"><span><img
                                :src="require('@assets/images/pages/home/admit.svg')"
                                style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 承認
                        </vs-button>
                    </vs-col>
                </vs-row>
            </div>
            <div class="vx-row">
                <div :class="'vx-col mb-4 ' + ((circularUserLastSend.parent_send_order != 0 && circularUserLastSend.child_send_order == 1)? 'w-full ': 'w-full lg:pr-4')">
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
                              <v-select :options="emailTemplateOptions" :clearable="false" :searchable ="false" :value="selectedComment" @input="onChangeEmailTemplate" />
                            </div>
                        </vs-row>
                        <vs-row>※メッセージは次の回覧者への送信メールに記載されます。<br/>
                            　また、プレビュー画面「コメント」タブの「社内宛」に表示されます。
                        </vs-row>
                    </vx-card>
                </div>
                <div :class="'vx-col mb-4 lg:pr-0 ' + (allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ? 'w-full': 'w-full lg:pr-4')">
                    <vx-card :hideLoading="true" class="h-full">
                        <vs-row class="border-bottom pb-4">
                            <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center"
                                    vs-w="6" vs-xs="12">
                                <h4>宛先、回覧順 <span class="text-danger">*</span></h4>
                            </vs-col>
                        </vs-row>
                        <div class="mail-steps">
                            <div :class="[allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ?
                            'mail-list' : 'mailListViewOnly', (!selectUsersDisplay || selectUsersDisplay.length == 1 || !selectUsers[0].return_flg)?'not-return':'']">
                                <p class="mt-2">(全ての回覧後に申請者に戻ります)</p>
                                <vs-row v-if="selectUsersDisplay && selectUsers.length > 0" vs-type="flex"
                                        :class="['group applicant not-return']">
                                    <vs-col vs-w="6">
                                        <vs-row vs-type="flex" vs-justify="center">
                                            <vs-col vs-w="10" class="item me" vs-type="flex" vs-align="flex-start">
                                                <span>{{selectUsers[0].name}}</span>
                                                <span>【{{selectUsers[0].email}}】</span>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                    <vs-col vs-w="6" class="child1st-block">
                                        <vs-row vs-justify="center" vs-type="flex"
                                                v-for="(user, index) in showSelectUsersDisplay(selectUsersDisplay[0])"
                                                :key="user.email + index + changeTimes"
                                                >
                                            <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!user.plan_users || user.plan_users<=0"
                                                    :class="['item child-order item-draggable', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '',  (index + 1 === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']">
                                                <span>{{index + 1}} - {{user.name || '社員'}}</span>
                                                <span>【{{user.email}}】</span>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                      class="final"> 最終</span>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users && user.plan_users.length>0"
                                                :class="['item child-order ', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '',  (index + 1 === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <span :key="index + user.name">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2)"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <a class="currentUser-flg" :key="index + user.email"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                        </vs-row>
                                        <vs-row vs-type="flex" vs-justify="center">
                                            <vs-col vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1 && (selectUsersDisplay[0][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                    class="item child-order me">
                                                <span>{{selectUsers[0].name}}</span>
                                                <span>【{{selectUsers[0].email}}】</span>
                                                <a class="currentUser-flg" :key="selectUsersDisplay[0][0].id + selectUsersDisplay[0][0].email"
                                                   v-if="circularUserLastSend &&  circularUserLastSend.id === selectUsersDisplay[0][0].id"
                                                   href="#"><i class="far fa-flag"></i></a>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                </vs-row>
                                <template v-if="!specialCircularFlg || specialCircularReceiveFlg">
                                    <vs-row
                                            v-for="(group, idx) in (showSelectUsersDisplay(selectUsersDisplay))"
                                            vs-type="flex"
                                            vs-align="space-around"
                                            :class="['group parent-block not-return', (selectUsersDisplay[idx + 1].length > 2) ? 'return-flg': '']"
                                            :key="group+''+idx"
                                            >
                                        <vs-col vs-w="6">
                                            <vs-row
                                                    vs-type="flex"
                                                    vs-justify="space-around"
                                                    :key="selectUsersDisplay[idx + 1][0].email + '0'">
                                                <vs-col vs-w="10"
                                                        :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                        vs-type="flex"
                                                        v-if="!selectUsersDisplay[idx + 1][0].plan_users ||selectUsersDisplay[idx + 1][0].plan_users.length<=0"
                                                        vs-align="flex-start">
                                                    <span>{{selectUsersDisplay[idx + 1][0].name}} - {{selectUsersDisplay[idx + 1][0].mst_company_name}}</span>
                                                    <span>【{{selectUsersDisplay[idx + 1][0].email}}】</span>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1 && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                        class="final"> 最終</span>
                                                    <a class="currentUser-flg"
                                                    v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx + 1][0].id && selectUsersDisplay[idx + 1][0].circular_status!==10"
                                                    href="#"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="selectUsersDisplay[idx + 1][0].plan_users && selectUsersDisplay[idx + 1][0].plan_users.length>0"
                                                    :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-green': ((idx + 1) % 3 == 2 ? 'bg-green' : 'bg-green')]">
                                                    <span>合議 ({{selectUsersDisplay[idx + 1][0].plan_mode==1?"全員必須":selectUsersDisplay[idx + 1][0].plan_score+"人"}})</span>
                                                    <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                        <span :key="index + user.name">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                    </template>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1"
                                                        class="final"> 最終</span>
                                                    <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                        <a class="currentUser-flg" :key="index + user.email"
                                                        v-if="circularUserLastSend && circularUserLastSend.id === user.id && user.circular_status!==10" 
                                                        href="#"><i class="far fa-flag"></i></a>
                                                    </template>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                        </vs-col>
                                        <vs-col vs-w="6" class="child-block">
                                            <vs-row
                                                    v-for="(user, index) in showSelectUsersDisplay(selectUsersDisplay[idx + 1])"
                                                    vs-type="flex"
                                                    vs-justify="center"
                                                    :key="user.email + index + changeTimes">
                                                <vs-col vs-w="10"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        v-if="!user.plan_users || user.plan_users.length<=0"
                                                        :class="['item child-order item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'last': '', (index + 1 === 1 && selectUsersDisplay[idx + 1].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']">
                                                    <span>{{user.name || '社員'}} - {{ user.mst_company_name}}</span>
                                                    <span>【{{user.email}}】</span>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                        class="final"> 最終</span>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="user.plan_users && user.plan_users.length>0"
                                                    :class="['item child-order ', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '',  (index + 1 === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                    <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                    <template v-for="(user, index) in user.plan_users">
                                                        <span :key="index + user.name">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                    </template>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2)"
                                                        class="final"> 最終</span>
                                                    <template v-for="(user, index) in user.plan_users">
                                                        <a class="currentUser-flg" :key="index + user.email"
                                                        v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                        href="#"><i class="far fa-flag"></i></a>
                                                    </template>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                            <vs-row
                                                    vs-type="flex"
                                                    vs-justify="center">
                                                <vs-col vs-w="10"
                                                        :class="['item child-order', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        v-if="!selectUsersDisplay[idx + 1][0].plan_users && !isNotReturnCircular  && selectUsersDisplay[idx + 1].length > 1 && (selectUsersDisplay[idx + 1][0].parent_send_order == circularUserLastSend.parent_send_order)">
                                                    <span>{{selectUsersDisplay[idx + 1][0].name}} - {{selectUsersDisplay[idx + 1][0].mst_company_name}}</span>
                                                    <span>【{{selectUsersDisplay[idx + 1][0].email}}】</span>
                                                  <a class="currentUser-flg" 
                                                     v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx + 1][0].id && selectUsersDisplay[idx + 1][0].circular_status===10"
                                                     href="#"><i class="far fa-flag"></i></a>
                                                </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="selectUsersDisplay[idx + 1][0].plan_users && !isNotReturnCircular &&selectUsersDisplay[idx + 1][0].plan_users.length>0  && (selectUsersDisplay[idx + 1][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                :class="['item child-order ', 'bg-green']"  >
                                                <span>合議 ({{selectUsersDisplay[idx + 1][0].plan_mode==1?"全員必須":selectUsersDisplay[idx + 1][0].plan_score+"人"}})</span>
                                                <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                <span :key="index + user.name">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                  <a class="currentUser-flg" :key="index + user.email"
                                                     v-if="circularUserLastSend && circularUserLastSend.id === user.id && user.circular_status===10"
                                                     href="#"><i class="far fa-flag"></i></a>
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                            </vs-row>
                                            <div :class="['full-width', 'child-range-'+idx + 1]"
                                                v-if="selectUsersDisplay[idx + 1].length > 1 && (selectUsersDisplay[idx + 1][0].parent_send_order != circularUserLastSend.parent_send_order)">
                                                <vs-row
                                                        v-for="(user, index) in showSelectUsersDisplay(selectUsersDisplay[idx + 1])"
                                                        vs-type="flex"
                                                        vs-justify="space-around"
                                                        :key="user.email + index + changeTimes">
                                                    <vs-col vs-w="10"
                                                            vs-type="flex"
                                                            vs-align="flex-start"
                                                            :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'last': '', (index + 1 === 1 && selectUsersDisplay[idx + 1].length > 2) ? 'return-flg': '']">
                                                        <span>{{user.name || '社員'}} - {{ ((loginUser && loginUser.email === selectUsers[0].email) || (userHashInfo && userHashInfo.email === selectUsers[0].email))  ? user.mst_company_name : index + 1}}</span>
                                                        <span>【{{user.email}}】</span>
                                                        <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                            class="final"> 最終</span>
                                                    </vs-col>
                                                </vs-row>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                </template>
                              <!-- 特設サイト受取側組織名表示 start -->
                              <vs-row
                                  vs-type="flex"
                                  vs-align="space-around"
                                  v-show="specialCircularFlg && !specialCircularReceiveFlg"
                                  class='group parent-block'>
                                <vs-col vs-w="6">
                                  <vs-row
                                      vs-type="flex"
                                      vs-justify="space-around">
                                    <vs-col vs-w="10"
                                            :class="['item item-draggable', 'bg-orange']"
                                            vs-type="flex"
                                            vs-align="flex-start">
                                      <div draggable
                                           class="dropable-item h-full w-full">
                                        <div>{{ groupName }}</div>
                                        <div></div>
                                        <span class="final"> 最終</span>
                                      </div>
                                    </vs-col>
                                  </vs-row>
                                </vs-col>
                              </vs-row>
                              <!-- 特設サイト受取側組織名表示 end -->
                            </div>
                        </div>
                    </vx-card>
                </div>
                <div class="vx-row w-full lg:pl-4 lg:pr-0">
                    <div class="vx-col w-full lg:w-1/2 mb-4 lg:pr-0">
                        <vx-card class="h-full">
                            <vs-row class="border-bottom pb-4">
                                <h4>再通知設定</h4>
                            </vs-row>
                            <vs-row class="mb-4 mt-6">
                                <label>（再通知設定なし）</label>
                            </vs-row>

                        </vx-card>
                    </div>
                    <div class="vx-col w-full lg:w-1/2 mb-4 lg:pr-0">
                        <vx-card class="">
                            <vs-row class="border-bottom pb-4">
                                <h4>アクセスコード</h4>
                                <h4>（承認時に次の宛先に通知メールが送信されます。）</h4>
                            </vs-row>
                            <vs-row class="mb-4 mt-6">
                                <vs-col vs-w="6" vs-xs="12" vs-align="center">
                                    <vx-input-group class="w-full mb-0">
                                        <vs-input v-model="outsideAccessCode" maxlength="6" :disabled="true"/>
                                        <template slot="append">
                                            <div class="append-text btn-addon">
                                                <vs-button color="primary" :disabled="true"><i class="fas fa-sync-alt"></i></vs-button>
                                            </div>
                                        </template>
                                    </vx-input-group>
                                </vs-col>
                            </vs-row>
                        </vx-card>
                    </div>
                </div>
            </div>
            <div style="margin-bottom: 15px">
                <vs-row class="top-bar">
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-sm="6">
                        <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                   style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled"
                                   v-on:click="onBackClick"> 戻る
                        </vs-button>
                        <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                   style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState"
                                   v-on:click="onApprovalReviewing"><span><img
                                :src="require('@assets/images/pages/home/admit.svg')"
                                style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 承認
                        </vs-button>
                    </vs-col>
                </vs-row>
            </div>
            <modal name="operation-notice-modal"
                   :pivot-y="0.2"
                   :width="400"
                   :classes="['v--modal', 'operation-notice-modal', 'p-4']"
                   :height="'auto'"
                   :clickToClose="false">
                <vs-row>
                    <vs-col vs-w="12" vs-type="block">
                        <p>承認が完了しました</p>
                    </vs-col>
                </vs-row>
                <vs-row v-if="!$store.state.home.usingPublicHash" class="mp-3 pt-6" vs-type="flex"
                        style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px">
                    <vs-checkbox :value="operationNotice" v-on:click="operationNotice = !operationNotice">次回から表示しない。
                    </vs-checkbox>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled"
                               v-on:click="onUpdateOperationNoticeClick"> 閉じる
                    </vs-button>
                </vs-row>
            </modal>
        </div>

        <!-- 5-277 mobile html -->
        <div id="circular-destination-page-mobile" :class="isMobile?'mobile':''">
            <div style="width:100%;">
                <span @click="onBackClick"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
                <div style="display:inline;position:relative;top:-10px;">プレビュー・捺印</div>
            </div>
            <div class="vx-row">
                <vs-col vs-align="left" vs-justify="left" class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                    <ul class="breadcrumb">
                        <li><p><span class="badge">1</span> プレビュー・捺印</p></li>
                        <li><p><span class="badge">2</span> 回覧先設定</p></li>
                    </ul>
                </vs-col>
            </div>
            <div class="vx-row">
                <div :class="'vx-col mb-4 lg:pr-0 ' + (allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ? 'w-full lg:w-1/2 ': 'w-full lg:pr-4')">
                    <div><h4>宛先・回覧順</h4></div>
                    <div><p class="mt-2 mb-2">全ての回覧後に申請者に戻ります</p></div>
                    <vx-card :hideLoading="true">
                        <div class="mail-steps">
                            <div :class="[allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ?
                            'mail-list' : 'mailListViewOnly', (!selectUsersDisplay || selectUsersDisplay.length == 1 || !selectUsers[0].return_flg)?'not-return':'']">
                                <vs-row v-if="selectUsersDisplay && selectUsers.length > 0" vs-type="flex"
                                        :class="['group applicant not-return']">
                                    <vs-col vs-w="6" vs-xs="12">
                                        <vs-row vs-type="flex" vs-justify="center">
                                            <vs-col vs-w="10" vs-xs="12" class="item me" vs-type="flex" vs-align="flex-start">
                                                <div class="name">{{selectUsers[0].name}}</div>
                                                <div class="email">【{{selectUsers[0].email}}】</div>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                    <vs-col vs-w="6" vs-xs="12" class="child1st-block">
                                        <vs-row vs-justify="center" vs-type="flex"
                                                v-for="(user, index) in showSelectUsersDisplay(selectUsersDisplay[0])"
                                                :key="user.email + index + changeTimes"
                                                >
                                            <vs-col
                                                    vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!user.plan_users || user.plan_users<=0"
                                                    :class="['item child-order item-draggable', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '',  (index + 1 === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']">
                                                <div class="name">{{index + 1}} - {{user.name || '社員'}}</div>
                                                <div class="email">【{{user.email}}】</div>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                      class="final"> 最終</span>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10" vs-xs="12"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users && user.plan_users.length>0"
                                                :class="['item child-order ', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '',  (index + 1 === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <div class="name" :key="index + user.name">{{user.name || '社員'}} </div>
                                                    <div class="email">【{{user.email}}】</div>
                                                </template>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2)"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <a class="currentUser-flg" :key="index + user.email"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                        </vs-row>
                                        <vs-row vs-type="flex" vs-justify="center">
                                            <vs-col vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1 && (selectUsersDisplay[0][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                    class="item child-order me">
                                                <div class="name">{{selectUsers[0].name}}</div>
                                                <div class="email">【{{selectUsers[0].email}}】</div>
                                                <a class="currentUser-flg" :key="selectUsersDisplay[0][0].id + selectUsersDisplay[0][0].email"
                                                   v-if="circularUserLastSend &&  circularUserLastSend.id === selectUsersDisplay[0][0].id"
                                                   href="#"><i class="far fa-flag"></i></a>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                </vs-row>
                                <template v-if="!specialCircularFlg || specialCircularReceiveFlg">
                                    <vs-row
                                            v-for="(group, idx) in (showSelectUsersDisplay(selectUsersDisplay))"
                                            vs-type="flex"
                                            vs-align="space-around"
                                            :class="['group parent-block not-return', (selectUsersDisplay[idx + 1].length > 2) ? 'return-flg': '']"
                                            :key="group+''+idx"
                                            >
                                        <vs-col vs-w="6" vs-xs="12">
                                            <vs-row
                                                    vs-type="flex"
                                                    vs-justify="space-around"
                                                    :key="selectUsersDisplay[idx + 1][0].email + '0'">
                                                <vs-col vs-w="10" vs-xs="12"
                                                        :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                        vs-type="flex"
                                                        v-if="!selectUsersDisplay[idx + 1][0].plan_users ||selectUsersDisplay[idx + 1][0].plan_users.length<=0"
                                                        vs-align="flex-start">
                                                    <div class="name">{{selectUsersDisplay[idx + 1][0].name}} - {{selectUsersDisplay[idx + 1][0].mst_company_name}}</div>
                                                    <div class="email">【{{selectUsersDisplay[idx + 1][0].email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1 && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                        class="final"> 最終</span>
                                                    <a class="currentUser-flg"
                                                    v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx + 1][0].id && selectUsersDisplay[idx + 1][0].circular_status!==10"
                                                    href="#"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="selectUsersDisplay[idx + 1][0].plan_users && selectUsersDisplay[idx + 1][0].plan_users.length>0"
                                                    :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-green': ((idx + 1) % 3 == 2 ? 'bg-green' : 'bg-green')]">
                                                    <span>合議 ({{selectUsersDisplay[idx + 1][0].plan_mode==1?"全員必須":selectUsersDisplay[idx + 1][0].plan_score+"人"}})</span>
                                                    <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                        <div class="name" :key="index + user.name">{{user.name || '社員'}} </div>
                                                        <div class="email">【{{user.email}}】</div>
                                                    </template>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1"
                                                        class="final"> 最終</span>
                                                    <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                        <a class="currentUser-flg" :key="index + user.email"
                                                        v-if="circularUserLastSend && circularUserLastSend.id === user.id && user.circular_status!==10" 
                                                        href="#"><i class="far fa-flag"></i></a>
                                                    </template>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                        </vs-col>
                                        <vs-col vs-w="6" vs-xs="12" class="child-block">
                                            <vs-row
                                                    v-for="(user, index) in showSelectUsersDisplay(selectUsersDisplay[idx + 1])"
                                                    vs-type="flex"
                                                    vs-justify="center"
                                                    :key="user.email + index + changeTimes">
                                                <vs-col vs-w="10" vs-xs="12"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        v-if="!user.plan_users || user.plan_users.length<=0"
                                                        :class="['item child-order item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'last': '', (index + 1 === 1 && selectUsersDisplay[idx + 1].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']">
                                                    <div class="name">{{user.name || '社員'}} - {{ user.mst_company_name}}</div>
                                                    <div class="email">【{{user.email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                        class="final"> 最終</span>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="user.plan_users && user.plan_users.length>0"
                                                    :class="['item child-order ', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '',  (index + 1 === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                    <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                    <template v-for="(user, index) in user.plan_users">
                                                        <div class="name" :key="index + user.name">{{user.name || '社員'}} </div>
                                                        <div class="email">【{{user.email}}】</div>
                                                    </template>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2)"
                                                        class="final"> 最終</span>
                                                    <template v-for="(user, index) in user.plan_users">
                                                        <a class="currentUser-flg" :key="index + user.email"
                                                        v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                        href="#"><i class="far fa-flag"></i></a>
                                                    </template>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                            <vs-row
                                                    vs-type="flex"
                                                    vs-justify="center">
                                                <vs-col vs-w="10" vs-xs="12"
                                                        :class="['item child-order', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        v-if="!selectUsersDisplay[idx + 1][0].plan_users && !isNotReturnCircular  && selectUsersDisplay[idx + 1].length > 1 && (selectUsersDisplay[idx + 1][0].parent_send_order == circularUserLastSend.parent_send_order)">
                                                    <div class="name">{{selectUsersDisplay[idx + 1][0].name}} - {{selectUsersDisplay[idx + 1][0].mst_company_name}}</div>
                                                    <div class="email">【{{selectUsersDisplay[idx + 1][0].email}}】</div>
                                                  <a class="currentUser-flg" 
                                                     v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx + 1][0].id && selectUsersDisplay[idx + 1][0].circular_status===10"
                                                     href="#"><i class="far fa-flag"></i></a>
                                                </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10" vs-xs="12"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="selectUsersDisplay[idx + 1][0].plan_users && !isNotReturnCircular &&selectUsersDisplay[idx + 1][0].plan_users.length>0  && (selectUsersDisplay[idx + 1][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                :class="['item child-order ', 'bg-green']"  >
                                                <span>合議 ({{selectUsersDisplay[idx + 1][0].plan_mode==1?"全員必須":selectUsersDisplay[idx + 1][0].plan_score+"人"}})</span>
                                                <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                <span :key="index + user.name">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <template v-for="(user, index) in selectUsersDisplay[idx + 1][0].plan_users">
                                                  <a class="currentUser-flg" :key="index + user.email"
                                                     v-if="circularUserLastSend && circularUserLastSend.id === user.id && user.circular_status===10"
                                                     href="#"><i class="far fa-flag"></i></a>
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                            </vs-row>
                                            <div :class="['full-width', 'child-range-'+idx + 1]"
                                                v-if="selectUsersDisplay[idx + 1].length > 1 && (selectUsersDisplay[idx + 1][0].parent_send_order != circularUserLastSend.parent_send_order)">
                                                <vs-row
                                                        v-for="(user, index) in showSelectUsersDisplay(selectUsersDisplay[idx + 1])"
                                                        vs-type="flex"
                                                        vs-justify="space-around"
                                                        :key="user.email + index + changeTimes">
                                                    <vs-col vs-w="10" vs-xs="12"
                                                            vs-type="flex"
                                                            vs-align="flex-start"
                                                            :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'last': '', (index + 1 === 1 && selectUsersDisplay[idx + 1].length > 2) ? 'return-flg': '']">
                                                        <div class="name">{{user.name || '社員'}} - {{ ((loginUser && loginUser.email === selectUsers[0].email) || (userHashInfo && userHashInfo.email === selectUsers[0].email))  ? user.mst_company_name : index + 1}}</div>
                                                        <div class="email">【{{user.email}}】</div>
                                                        <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)"
                                                            class="final"> 最終</span>
                                                    </vs-col>
                                                </vs-row>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                </template>
                              <!-- 特設サイト受取側組織名表示 start -->
                              <vs-row
                                  vs-type="flex"
                                  vs-align="space-around"
                                  v-show="specialCircularFlg && !specialCircularReceiveFlg"
                                  class='group parent-block'>
                                <vs-col vs-w="6" vs-xs="12">
                                  <vs-row
                                      vs-type="flex"
                                      vs-justify="space-around">
                                    <vs-col vs-w="10" vs-xs="12"
                                            :class="['item item-draggable', 'bg-orange']"
                                            vs-type="flex"
                                            vs-align="flex-start">
                                      <div draggable
                                           class="dropable-item h-full w-full">
                                        <div>{{ groupName }}</div>
                                        <div></div>
                                        <span class="final"> 最終</span>
                                      </div>
                                    </vs-col>
                                  </vs-row>
                                </vs-col>
                              </vs-row>
                              <!-- 特設サイト受取側組織名表示 end -->
                            </div>
                        </div>
                    </vx-card>
                    <div class="confirm_box" style="display: none;">
                        <div><img :src="require('@assets/images/mobile/confirm_white_big.svg')"></div>
                        <div>承認しました</div>
                    </div>
                    <modal name="operation-notice-modal"
                           :pivot-y="0.2"
                           :width="300"
                           :classes="['v--modal', 'operation-notice-modal', 'p-4']"
                           :height="'auto'"
                           :clickToClose="false">
                        <vs-row>
                            <vs-col vs-w="12" vs-type="block">
                                <p>承認が完了しました</p>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="!$store.state.home.usingPublicHash" class="mp-3 pt-6" vs-type="flex"
                                style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px">
                            <vs-checkbox :value="operationNotice" v-on:click="operationNotice = !operationNotice">
                                次回から表示しない。
                            </vs-checkbox>
                        </vs-row>
                        <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                            <vs-button class="square mr-2 " color="primary" type="filled"
                                       v-on:click="onUpdateOperationNoticeClick"> 閉じる
                            </vs-button>
                        </vs-row>
                    </modal>
                </div>
            </div>

            <div class="action_mobile">

              <div class="action_back" v-on:click="onBackClick">
                <div class="icon"><img :src="require('@assets/images/mobile/back_white.svg')"></div>
                <div class="label">戻る</div>
              </div>

              <div class="action_approval" v-on:click="onApprovalReviewing">
                <div class="icon"><img :src="require('@assets/images/mobile/confirm_white.svg')"></div>
                <div class="label">承認</div>
              </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapActions} from "vuex";
    import {CIRCULAR} from '../../enums/circular';
    import {CIRCULAR_USER} from '../../enums/circular_user';
    import draggable from 'vuedraggable';

    import config from "../../app.config";

    import VueSuggestion from 'vue-suggestion'

    import ContactTree from '../../components/contacts/ContactTree';
    import Utils from '../../utils/utils';
    import Axios from "axios";
    export default {
        components: {
            //[LiquorTree.name]: LiquorTree,
            draggable,
            VueSuggestion,
            ContactTree
        },
        directives: {},
        data() {
            return {
                CIRCULAR: CIRCULAR,
                CIRCULAR_USER: CIRCULAR_USER,
                info: {},
                emailTemplateOptions: [],
                optionSelected: '',
                emailTitle: '',
                emailContent: '',
                commentTitle: '',
                departmentUserFilter: '',
                suggestDisabled: false,
                usernameSelect: '',
                emailSelect: '',
                userSuggestSelect: null,
                treeLoaded: 0,
                treeData: [],
                treeFilterOptions: {
                    multiple: false,
                    checkbox: true,
                    filter: {
                        matcher(query, node) {
                            return new RegExp(query, 'i').test(node.data.family_name) || new RegExp(query, 'i').test(node.data.given_name) || new RegExp(query, 'i').test(node.data.email)
                        },
                        emptyText: '何も見つかりません'
                    }
                },
                usersChange: false,
                childUsersChange: false,
                userSuggestions: [],
                emailSuggestions: [],
                userSuggestModel: '',
                emailSuggestModel: '',
                emailSuggestValidateMsg: '',
                formatSelectUsers: [],
                oldFormatSelectUsers: [],
                checkShowAddress: true,
                userHashInfo: null,
                arrFavorite: [],
                checkShowButtonApply: false,
                dragOptions: {
                    name: 'user',
                    pull: true,
                    put: true
                },
                loginUser: JSON.parse(getLS('user')),
                previousRoute: null,
                userViewSuggestions: [],
                userViewSuggestModel: '',
                emailViewSuggestions: [],
                emailViewSuggestModel: '',
                userViewnameSelect: '',
                emailViewSelect: '',
                suggestViewDisabled: false,
                userViewSuggestSelect: null,
                emailViewSuggestValidateMsg: '',
                isNotReturnCircular: false,
                selectUsersDisplay: [],
                searchAreaFlg: false,
                confirmEdit: false,
                outsideAccessCode: null,
                operationNotice: false,
                checkShowTitle: false, //件名表示フラグ
                showPopupEditContacts: false,
                editContact: {},
                confirmDelete: false,
                confirmDuplicateEmail: false,
                listCheckEmailContact: [],
                clickState: false, //二重チェック用
                showTree: true,
                changeTimes: 0,
                paramId: null,
                selectedComment: '',
                specialCircularReceiveFlg: false,//回覧ユーザーが特設サイトの受取側ですか
                circularUserLastSendIdIsSpecial: false,//現在未操作のユーザは特設サイトの受取側ですか
                specialCircularFlg:false,//特設サイト回覧
                specialButtonDisableFlg: false,//特設サイト申請画面、ボタン非アクティブ
                groupName: '',//特設サイト受取側組織名
                isMobile: false,
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
                checkSentCircular: state => state.home.checkSentCircular,
                selectUserView: state => state.application.selectUserView,
                checkOperationNotice: state => state.application.checkOperationNotice,
            }),
            circularUserLastSend() {
                if (!this.circular || !this.circular.users) {
                    return null;
                }
                const circular_user = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS);
                if (!circular_user) return null;
                this.$store.commit('home/updateCurrentParentSendOrder', circular_user ? circular_user.parent_send_order : 0);
                return circular_user;
            },
            allowAddDestination: {
                get() {
                    if (!this.circular) return false;
                    return (this.circular.address_change_flg || (this.circularUserLastSend.parent_send_order > 0));
                }
            },
            selectUsers: {
                get() {
                    if (!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                    if (!this.circularUserLastSend) return [];
                    return this.$store.state.home.circular.users.filter(item => {
                        return (item.parent_send_order === 0 && item.child_send_order === 0) || (this.circularUserLastSend.parent_send_order === item.parent_send_order) || (item.parent_send_order !== 0 && item.child_send_order === 1);
                    });

                },
                set(value) {
                    this.updateCircularUsers(value)
                }
            },
            selectUserView: {
                get() {
                    return this.$store.state.application.selectUserView
                },
            },
        },
        methods: {
            ...mapActions({
                getDepartmentUsers: "application/getDepartmentUsers",
                updateCircularUsers: "home/updateCircularUsers",
                sendNotifyContinue: "application/sendNotifyContinue",
                addLogOperation: "logOperation/addLog",
                getInfoByHash: "user/getInfoByHash",
                getListFavorite: "favorite/getList",
                getMyInfo: "user/getMyInfo",
                updateOperationNotice: "application/updateOperationNotice",
            }),
            showSelectUsersDisplay(items) {
                return typeof items != "undefined" ? items.filter((item , index) => index > 0) : [];
            },
            setUsernameSuggestLabel(item) {
                return item.name;
            },
            setEmailSuggestLabel(item) {
                return item.email;
            },
            onChangeEmailTemplate(value) {
                this.selectedComment = value;
                if (this.emailContent == null) {
                    this.emailContent = '';
                }
                this.emailContent = this.emailContent.concat(value);
                this.selectedComment = value +' '
            },
            onBackClick: async function () {
                // 二重チェック追加
                this.clickState = true;
                this.$router.push('/received-reviewing/'+this.paramId);
                this.clickState = false;
            },
            onUpdateOperationNoticeClick: async function () {
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
                    this.updateOperationNotice({operationNotice: this.operationNotice}).then(ret => {
                        if (ret) {
                            this.$router.push('/received');
                        }
                    });
                }
            },
            onApprovalReviewing: async function () {
                // 二重チェック
                this.clickState = true;
                // 最初回覧文書取得
                let circular_document_id = 0;
                this.$store.state.home.files.forEach(function (item){
                    if(circular_document_id == 0 || circular_document_id > item.circular_document_id){
                        circular_document_id = item.circular_document_id;
                    }
                });
                const  data = {
                    add_stamp: false,
                    text: this.emailContent,
                    circular_document_id: circular_document_id,
                    sender_id: this.circularUserLastSend ? this.circularUserLastSend.id: null,
                };
                this.sendNotifyContinue(data).then(ret => {
                    if (ret) {

                        if( this.isMobile ) {

                          let $notifySuccess = $(`
                                          <div id="send_success">
                                              <div class="background"></div>
                                              <div class="info">
                                                <div class="icon"><i style="font-size: 100px;" class="fa fa-check"></i></div><div class="text">承認しました。</div>
                                              </div>
                                          </div>
                                        `);

                          $notifySuccess.appendTo('body');

                          setTimeout( ()=> {
                            $notifySuccess.remove();
                            
                            if (this.$store.state.home.usingPublicHash) {
                              if (window.opener) {
                                  window.close();
                              } else {
                                  window.location.href = config.LOCAL_API_URL;
                              }
                            } else {
                                this.$router.push('/received');
                            }
                          }, 2000);


                        } else {
                          if (this.$store.state.home.usingPublicHash) {
                            if (window.opener) {
                                window.close();
                            } else {
                                window.location.href = config.LOCAL_API_URL;
                            }
                          } else {
                              this.$router.push('/received');
                          }
                        }


                        


                    }
                });
            },
            loadFormatSelectUsers: function () {
                if (!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                if (!this.circularUserLastSend) return [];
                const circularUsers = this.$store.state.home.circular.users.slice();
                const formatCircularUsers = [];
                let formatCircularUser = {};
                formatCircularUser.user = circularUsers.length ? circularUsers[0] : null;
                formatCircularUser.children = [];
                formatCircularUsers.push(...circularUsers.filter(item => {
                    return this.circularUserLastSend.parent_send_order === 0 ? item.parent_send_order === 0 : item.child_send_order === 0
                }).map(item => {
                    return {user: item, children: []};
                }));
                let old_parent_send_order = circularUsers.length ? circularUsers[0].parent_send_order : null;
                for (let circularUser of circularUsers) {
                    if (old_parent_send_order !== circularUser.parent_send_order) {
                        if (!formatCircularUser.children) formatCircularUser.children = [];
                        if (formatCircularUser.user.parent_send_order) formatCircularUsers.push(formatCircularUser);
                        old_parent_send_order = circularUser.parent_send_order;
                        formatCircularUser = {};
                        formatCircularUser.children = [];
                    }

                    // 20200512 fix PAC_5-170 違う環境間での回覧で宛先情報が表示されさない
                    if (this.userHashInfo) {
                        if (circularUser.parent_send_order > 0 && circularUser.child_send_order > 1 && this.userHashInfo.mst_company_id == circularUser.mst_company_id && circularUser.env_flg == config.APP_SERVER_ENV && circularUser.edition_flg == config.APP_EDITION_FLV && circularUser.server_flg == config.APP_SERVER_FLG) {
                            formatCircularUser.children.push(circularUser);
                        }
                    } else {
                        const loggedUser = JSON.parse(getLS('user'));
                        if (circularUser.parent_send_order > 0 && circularUser.child_send_order > 1 && loggedUser.mst_company_id == circularUser.mst_company_id && circularUser.env_flg == config.APP_SERVER_ENV && circularUser.edition_flg == config.APP_EDITION_FLV && circularUser.server_flg == config.APP_SERVER_FLG) {
                            formatCircularUser.children.push(circularUser);
                        }
                    }

                    if (circularUser.parent_send_order > 0 && circularUser.child_send_order === 1) {
                        formatCircularUser.user = circularUser;
                    }
                }
                if (formatCircularUser.user.parent_send_order) formatCircularUsers.push(formatCircularUser);
                return formatCircularUsers;
            },
            async buildSelectUsersDisplay() {
                let plan_list={}
                if(!this.$store.state.home.usingPublicHash){
                    plan_list = (await Axios.get(`${config.BASE_API_URL}/circulars/${this.circular.id}/get_plan`)).data.data
                }else{
                    plan_list = (await Axios.get(`${config.BASE_API_URL}/public/circulars/${this.circular.id}/get_plan_by_hash`,{data:{nowait: true,usingHash: this.$store.state.home.usingPublicHash}})).data.data
                }
                this.selectUsersDisplay.length = 0;
                this.changeTimes++;
                if (this.selectUsers) {
                    let arrSelectUsers = Object.assign([],this.selectUsers);
                    let planUserArray=[]
                    let newSelectUser = arrSelectUsers.reduce((accumulator, user) => {
                        accumulator[user['parent_send_order']] = accumulator[user['parent_send_order']] || [];
                        accumulator[user['parent_send_order']].push(user);
                        return accumulator
                    }, []);
                    newSelectUser.forEach(select=>{
                        select.forEach(user=>{
                            if (user.plan_id>0){
                                if(!planUserArray[user.plan_id]){
                                    let obj = Object.assign({},user)
                                    obj.is_plan = true
                                    obj.plan_users = []
                                    obj.plan_add = false
                                    obj.plan_mode = plan_list[user.plan_id] ? plan_list[user.plan_id].mode : ""
                                    obj.plan_score=plan_list[user.plan_id] ? plan_list[user.plan_id].score : ""
                                    planUserArray[user.plan_id]=obj
                                }
                                planUserArray[user.plan_id].plan_users.push(Object.assign({},user))
                            }
                        })
                    })
                    newSelectUser.forEach((n,i)=>{
                        newSelectUser[i]=n.map(user=>{
                            if (user.plan_id>0){
                                if (!planUserArray[user.plan_id].plan_add){
                                    planUserArray[user.plan_id].plan_add=true
                                    return planUserArray[user.plan_id]
                                }
                            }else {
                                return user
                            }
                            return null
                        }).filter(user=>{
                            return user!=null
                        })
                    })
                    if (planUserArray.length>0){
                        this.is_plan=true
                    }
                    if (Array.isArray(newSelectUser)) {
                        newSelectUser = newSelectUser.filter(function (el) {
                            return el != null;
                        });
                        this.selectUsersDisplay.push.apply(this.selectUsersDisplay, newSelectUser);
                    } else if (newSelectUser) {
                        this.selectUsersDisplay.push.apply(this.selectUsersDisplay, [newSelectUser]);
                    }
                }
            },
        },
        async mounted() {
            if (!this.$store.state.home.usingPublicHash) {
                this.info = await this.getMyInfo();
                this.emailTemplateOptions =  Utils.setEmailTemplateOptions(this.info);
            }
        },
        watch: {
            "$store.state.application.selectUserChange": function (newVal, oldVal) {
                this.usersChange = !this.usersChange;
                this.buildSelectUsersDisplay();
            },
            "$store.state.home.selectUserChange": function (newVal, oldVal) {
                this.usersChange = !this.usersChange;
                this.buildSelectUsersDisplay();
            },
            outsideAccessCode: function () {
                this.outsideAccessCode = this.outsideAccessCode.replace(/[\W]/g, '');
            },
        },
        async created() {

            // Check Mobile
            if (
              /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
                navigator.userAgent
              )
            ) {
              this.isMobile = true;
            }


            var limit = getLS("limit");
            limit = JSON.parse(limit);
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            this.$store.commit('application/updateListUserView', []);
            this.paramId = this.$route.params.id;
            if (limit && limit.enable_any_address == 1) {
                this.checkShowAddress = true;
            } else {
                this.checkShowAddress = false;
            }
            this.addLogOperation({action: 'r11-display', result: 0});
            if (!this.$store.state.home.fileSelected || !this.$store.state.home.circular || this.$store.state.home.circular.circular_status === this.CIRCULAR.SAVING_STATUS) {
                //this.$router.push('/');
            }
            this.arrFavorite = await this.getListFavorite();
            const hash = this.$route.params.hash;
            if (hash) {
                localStorage.setItem('tokenPublic', hash);
                this.$store.commit('home/setUsingPublicHash', true);

                this.userHashInfo = await this.getInfoByHash();
                this.emailTemplateOptions = Utils.setEmailTemplateOptions(this.userHashInfo);
                localStorage.setItem('envFlg', this.userHashInfo.current_env_flg);
            }
            // 申請者だけ、件名変更クリア表示
            if ((hash && this.$store.state.home.circular.create_user == this.userHashInfo.email) ||
                (!hash && this.$store.state.home.circular.create_user == this.loginUser.email)) {
                this.checkShowTitle = true;
                this.commentTitle = this.$store.state.home.title;
            }
            if (!hash || (hash && this.userHashInfo && !this.userHashInfo.is_external)) {
                await this.getDepartmentUsers({filter: ''});
            }
            if (this.$store.state.home.circular) {
                if (this.$store.state.home.circular.circular_status === CIRCULAR.SEND_BACK_STATUS) {
                    this.checkShowButtonApply = true;
                } else {
                    this.checkShowButtonApply = false;
                }
            }
            this.formatSelectUsers = this.loadFormatSelectUsers();
            if (this.selectUsers && this.selectUsers.length > 0) {
                let loginUser = this.selectUsers.find(user => ((!hash && user.email === this.loginUser.email) || (hash && user.email === this.userHashInfo.email)));
                if (loginUser) {
                    let applicant = this.selectUsers.find(user => user.parent_send_order === loginUser.parent_send_order && ((user.parent_send_order === 0 && user.child_send_order === 0) || (user.child_send_order === 1)));
                    if (applicant) {
                        this.isNotReturnCircular = (applicant.return_flg == 0);
                    }
                }
            }
            // 特設サイト
            this.specialCircularFlg = this.circular && this.circular.special_site_flg;
            this.groupName = this.circular.special_site_group_name;
            if(this.specialCircularFlg){
              this.circular.users.forEach(item => {
                if(item.email == this.loginUser.email && item.mst_company_id == this.loginUser.mst_company_id && item.special_site_receive_flg == 1){
                  this.specialCircularReceiveFlg = true;
                }
                if(item.special_site_receive_flg == 1 && item.id == this.circularUserLastSend){
                  this.circularUserLastSendIdIsSpecial = true;
                }
              });
            }
            this.buildSelectUsersDisplay();
            this.$nextTick(() => {
                let popups = document.getElementsByClassName('vs-component con-vs-popup vs-popup-primary');
                for (let i = 0; i < popups.length; i++) {
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
        },
        beforeDestroy() {
          $('#send_success').remove();
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

<style lang="scss" scoped>
  #circular-destination-page.mobile{
    display: none;
  }

  #circular-destination-page-mobile.mobile {
    display: block;
    position: relative;
    padding: 0 1.2rem;
    overflow: hidden !important;

    .breadcrumb {
      display: inline-block;
      width: 100%;

      li {
        float: left;
        width: 50%;
        border-top: 1px solid #dcdcdc;
        border-bottom: 1px solid #dcdcdc;

        &:first-child p::before{
          content: " ";
          display: block;
          width: 0;
          height: 0;
          border-top: 21px solid transparent;
          border-bottom: 21px solid transparent;
          border-left: 13px solid #bcbcbc;
          position: absolute;
          top: 50%;
          margin-top: -21px;
          margin-left: 1px;
          left: 100%;
          z-index: 1;
        }

        &:first-child p::after{
          content: " ";
          display: block;
          width: 0;
          height: 0;
          border-top: 21px solid transparent;
          border-bottom: 21px solid transparent;
          border-left: 13px solid white;
          position: absolute;
          top: 50%;
          margin-top: -21px;
          left: 100%;
          z-index: 2;
        }

        p{
          text-decoration: none;
          padding: 10px 0 10px 40px;
          position: relative;
          display: block;
          float: left;
          font-size: 14px;
          width: 100%;
        }

        &:first-child p {
          &:after{
            border-left: 13px solid #f8f8f8 !important;
          }
        }
      }
    }

    .action_mobile{
      margin-top: 30px;
      text-align: center;
      display: inline-block;
      width: 100%;

      .action_back, .action_approval{
        width: 48%;
        background: #0984e3;
        padding: 0.75rem 0;
        border-radius: 5px;
        color: #fff;
        float: left;

        img{
          width: 15px;
        }
      }

      .action_back{
        margin-right: 4%;
      }

    }

    .mail-steps{
      padding: 20px 20px 0 20px;

      .group {

        .bg-orange{
          background: #ffdab9 !important;
        }

        .bg-pinkle{
          background: #ffb6c1 !important;
        }

        .item {
          border-radius: 5px;
          background: #C8EFC8;
          position: relative;
          margin-bottom: 20px;
          padding: 10px;
          display: block !important;
          height: auto;
          line-height: normal;

          &.me{
            background: #d1ecff;
          }

          .name{
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 90%;
          }
          .email{
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 95%;
          }

          .remove-flag, .currentUser-flg {
            position: absolute;
            right: 10px;
            top: 5px;
            width: 5%;
            line-height: 25px;
            text-align: center;
          }

          &:not(.me)::before{
            content: "";
            position: absolute;
            left: calc(50% - 7px);
            top: -35px;
            width: 15px;
            height: 50px;
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            transform: rotate(0deg);
          }

          &.first{
            &:before{
              content: "";
              position: absolute;
              left: -35px;
              top: 3px;
              width: 15px;
              height: 40px;
              background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
              background-size: contain;
              background-position: center;
              background-repeat: no-repeat;
              transform: rotate(-90deg);
              transition: transform 0.5s ease-in;
            }
          }

          &.child-order:not(.first)::before{
            content: "";
            position: absolute;
            left: calc(50% - 7px);
            top: -35px;
            width: 15px;
            height: 50px;
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
          }
        }
      }
      .item-div{
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      .final{
        position: absolute;
        top: 7px;
        right: 30px;
        padding: 1px 3px;
        border: 1px solid #0a84e3;
        color: #0a84e3;
        border-radius: 5px 5px 5px 5px;
      }
    }

  }

  #send_success{

    .background{
      background: rgba(0, 0, 0, 0.3);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 999;
    }

    .info{
      position: fixed;
      width: 250px;
      height: 250px;
      top: calc( 30% );
      left: calc( 50% - 125px );
      background: #0984e3;
      border-radius: 50%;
      z-index: 9999;
      text-align: center;

      .icon{
        text-align: center;
        color: #fff;
        width: 100px;
        margin-left: calc( 50% - 42px );
        padding-top: calc( 50% - 90px );
      }
      .text{
        color: #fff;
        padding-top: 10px;
        font-size: 25px;
        margin-left: 20px;
      }
    }
  }

  @media (min-width: 601px) {

    #circular-destination-page-mobile.mobile {
      .action_mobile{
        max-width: 400px;
        margin-left: calc(50% - 200px);

        .action_back, .action_approval{
          padding: 1.2rem 0;

          .label{
            font-size: 18px;
          }
          .icon img{
            width: auto;
            height: 18px;
          }
        }
      }
    }

  }

  
  @media (max-width: 600px) {
    #circular-destination-page-mobile.mobile {
      .mail-steps {
        .group {
          .item.first:before{
            top: -35px;
            left: calc(50% - 7px);
            transform: rotate(0deg);
            height: 50px;
          }
        }
      }
    }
  }

</style>