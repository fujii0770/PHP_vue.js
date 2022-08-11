<template>
    <div>
    <div id="circular-destination-page" :class="isMobile?'mobile':''">
        <div style="margin-bottom: 15px">
            <vs-row class="top-bar">
                <vs-col vs-type="flex" vs-w="9" vs-xs="12" vs-sm="6" vs-align="center" vs-justify="center" class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                    <ul class="breadcrumb">
                        <li><p style="color: #27ae60;"><span class="badge badge-success">1</span> プレビュー・捺印</p></li>
                        <li><p style="color: #0984e3;"><span class="badge badge-primary">2</span> 回覧先設定</p></li>
                        <li><p style="background: transparent"></p></li>
                    </ul>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="3" vs-xs="12" vs-sm="6">
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="onBackClick"> 戻る</vs-button>
                    <vs-button v-if="checkShowButtonApply" class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState" v-on:click="onSendClick"><i class="fas fa-check" style="margin-right: 5px;"></i> 申請する</vs-button>
                    <vs-button v-else class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState" v-on:click="onSendClick();"><span><img :src="require('@assets/images/pages/home/admit.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 承認</vs-button>
                </vs-col>
            </vs-row>
        </div>
        <div class="vx-row">
            <!-- PAC_5-511 START -->
            <div :class="'vx-col mb-4 ' + ((circularUserLastSend.parent_send_order != 0 && circularUserLastSend.child_send_order == 1)? 'w-full ': 'w-full lg:pr-4')">
                <vx-card class="mb-4">
                    <vs-row class="border-bottom pb-4">
                        <h4>件名・メッセージ</h4>
                    </vs-row>
                  <vs-row class="mt-6" v-if="checkShowTitle">
                    <vs-input class="inputx w-full" placeholder="件名をつけて送信できます。"  v-validate="'emoji'" name="subject" v-model="commentTitle" @change="replaceEmoji($event)" />
                    <span class="text-danger text-sm" v-show="errors.has('subject')">{{ errors.first('subject') }}</span>
                  </vs-row>
                    <vs-row class="mt-6" style="margin-bottom: 16px">
                        <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="emailContent" v-validate="'max:500'" name="content" style="margin-bottom: 0"/>
                        <span class="text-danger text-sm" v-show="errors.has('content')">{{ errors.first('content') }}</span>
                    </vs-row>
                    <vs-row class="mb-6">
                        <!--PAC_5-1413 欄外クリック時にボックスが閉じない　vuesax側のバグの為、vue selectに切り替え-->
                        <div class="w-full">
                          <v-select :options="emailTemplateOptions" :clearable="false" :searchable ="false" :value="selectedComment" @input="onChangeEmailTemplate" />
                        </div>
                    </vs-row>
                        <!-- <vs-checkbox :value="addToCommentsFlg" v-on:click="addToCommentsFlg = !addToCommentsFlg">社内のみ閲覧可</vs-checkbox>-->
                  <vs-row>※メッセージは次の回覧者への送信メールに記載されます。<br/>
                    　また、プレビュー画面「コメント」タブの「社内宛」に表示されます。</vs-row>
                </vx-card>
            </div>
            <div :class="'vx-col mb-4 lg:pr-0 ' + (allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ? 'w-full': 'w-full lg:pr-4')">
                <vx-card :hideLoading="true" class="h-full">
                    <vs-row class="border-bottom pb-4">
                        <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12">
                            <h4>宛先、回覧順 <span class="text-danger">*</span></h4>
                        </vs-col>
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6" vs-xs="12" v-show="(allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external))) && !isTemplateCircular && enable_any_address!=2">
                            <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12" style="padding-left: 43%">
                                <!--PAC_5-1171 同一システム内で遷移した場合、もしくは別システムの新エディションから遷移した場合はアドレス帳アイコンを表示する -->
                                <!-- <span @click="onRowSelect()" style="cursor:pointer;" v-if="userHashInfo && userHashInfo.current_edition_flg != 0">-->
                                <span @click="onDepartmentUsersSelect()" style="cursor:pointer;" v-if="!userHashInfo ||  (userHashInfo && userHashInfo.current_edition_flg != 0)">
                                  <!--PAC_5-2193 回覧先設定のアドレス帳のマークの表示を大きくする-->
                                  <svg xmlns="http://www.w3.org/2000/svg" width="2.5rem" height="2.5rem" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open "><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                </span>
                                <!-- PAC_5-1171 end -->
                            </vs-col>
                        </vs-col>
                    </vs-row>
                    <div class="mail-steps">
                        <div :class="[allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ? 'mail-list' : 'mailListViewOnly', (!selectUsersDisplay || selectUsersDisplay.length == 1 || !selectUsers[0].return_flg)?'not-return':'']">
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
                                <vs-col vs-w="6" class="child1st-block" v-if="!isTemplateCircular">
                                    <draggable
                                            :list="selectUsersDisplay[0]"
                                            v-if="selectUsersDisplay"
                                            @change="onDragItemChange"
                                            @end="onDragEnd"
                                            animation="300"
                                            group="selectUsers"
                                            :class="['full-width range-0']"
                                            :move="onItemMoving"
                                            swap="true"  >
                                            <template v-for="(user, index) in selectUsersDisplay[0]" >
                                        <vs-row vs-justify="center" vs-type="flex"

                                                 :key="user.email + index + changeTimes"
                                                v-if="index > 0">

                                            <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    :class="['item child-order item-draggable', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']"  v-if="!user.plan_users">
                                                <div class="name">{{index}} - {{user.name || '社員'}}</div>
                                                <div class="email">【{{user.email}}】</div>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)"
                                                      class="final"> 最終</span>
                                                <a class="currentUser-flg"
                                                   v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                   href="#"><i class="far fa-flag"></i></a>
                                                <!-- <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                   href.prevent v-on:click="onRemoveCircularUser(user.id)"
                                                   class="text-danger remove-flag"><i class="fas fa-times"></i></a> -->
                                                <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                   href.prevent v-on:click="onRemoveCircularUser(user.id)"
                                                   class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users"
                                                :class="['item child-order item-draggable', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <span :key="index">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <a class="currentUser-flg" :key="index"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                      <!--           <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                      <!--            href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                      <!--             class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                        </vs-row>
                                        </template>
                                        <vs-row vs-type="flex" vs-justify="center">
                                            <vs-col vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1 &&
                                                    (selectUsersDisplay[0][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                    class="item child-order me">
                                                <span>{{selectUsers[0].name}}</span>
                                                <span>【{{selectUsers[0].email}}】</span>
                                                <a class="currentUser-flg" :key="selectUsersDisplay[0][0].id + selectUsersDisplay[0][0].email"
                                                   v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[0][0].id"
                                                   href="#"><i class="far fa-flag"></i></a>
                                            </vs-col>
                                        </vs-row>
                                    </draggable>
                                </vs-col>
                                <!-- template route users start -->
                                <vs-col vs-w="6" class="child1st-block" v-if="isTemplateCircular">
                                    <div    v-if="templateUserRoutes"
                                            :class="['full-width range-0']"  >
                                        <vs-row vs-justify="center" vs-type="flex"
                                                v-for="(userRoutes, userRoutesIndex) in templateUserRoutes" :key="userRoutesIndex + changeTimes">
                                            <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    :class="['item child-order ', userRoutesIndex === 0 ? 'first' : '', (userRoutesIndex === templateUserRoutes.length-1 && userRoutesIndex >0)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>{{userRoutes[0].user_routes_name}}</span>
                                                <template v-for="(user, index) in userRoutes">
                                                    <span :key="index">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <span v-if="templateUserRoutes.length === userRoutesIndex + 1"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in userRoutes">
                                                    <a class="currentUser-flg" :key="index"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"
                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"
                                                       :key="index"
                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </template>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row vs-type="flex" vs-justify="center">
                                            <vs-col vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1 &&
                                                    (selectUsersDisplay[0][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                    class="item child-order me">
                                                <span>{{selectUsers[0].name}}</span>
                                                <span>【{{selectUsers[0].email}}】</span>
                                            </vs-col>
                                        </vs-row>
                                    </div>
                                </vs-col>
                                <!-- template route users end -->
                            </vs-row>
                            <template v-for="(group, idx) in (selectUsersDisplay)">
                            <vs-row

                                    vs-type="flex"
                                    vs-align="space-around"
                                    :class="['group parent-block not-return', (selectUsersDisplay[idx].length > 2) ? 'return-flg': '']"
                                    :key="group+''+idx"
                                    v-if="idx > 0" >
                                <vs-col vs-w="6">
                                    <vs-row
                                            vs-type="flex"
                                            vs-justify="space-around"
                                            :key="selectUsersDisplay[idx][0].email + '0'" >
                                        <vs-col vs-w="10"
                                                :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                vs-type="flex"
                                                vs-align="flex-start" v-if="(!specialCircularFlg || specialCircularReceiveFlg || !selectUsersDisplay[idx][0].special_site_receive_flg)&&(!selectUsersDisplay[idx][0].plan_users)">                                            <span>{{selectUsersDisplay[idx][0].name}} - {{selectUsersDisplay[idx][0].mst_company_name}}</span>
                                            <span>【{{selectUsersDisplay[idx][0].email}}】</span>
                                            <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                  class="final"> 最終</span>
                                            <a class="currentUser-flg"
                                               v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx][0].id"
                                               href="#"> <i class="far fa-flag"></i></a>
                                        </vs-col>
                                        <vs-col vs-w="10"
                                                :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                vs-type="flex"
                                                vs-align="flex-start" v-if="specialCircularFlg && !specialCircularReceiveFlg && selectUsersDisplay[idx][0].special_site_receive_flg && !selectUsersDisplay[idx][0].plan_users">
                                          <span>{{groupName}}</span>
                                          <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                class="final"> 最終</span>
                                          <a class="currentUser-flg"
                                             v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx][0].id"
                                             href="#"> <i class="far fa-flag"></i></a>
                                        </vs-col>
                                        <!--PAC_5-1698 S-->
                                        <vs-col
                                            vs-w="10"
                                            vs-type="flex"
                                            vs-align="flex-start"
                                            v-if="selectUsersDisplay[idx][0].plan_users"
                                            :class="['item item-draggable', idx % 3 == 1 ? 'bg-green': (idx % 3 == 2 ? 'bg-green' : 'bg-green')]">
                                            <span>合議 ({{selectUsersDisplay[idx][0].plan_mode==1?"全員必須":selectUsersDisplay[idx][0].plan_score+"人"}})</span>
                                            <template v-for="(user, index) in selectUsersDisplay[idx][0].plan_users">
                                                <span :key="index">{{user.name || '社員'}} 【{{user.email}}】</span>
                                            </template>
                                            <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                  class="final"> 最終</span>
                                            <template v-for="(user, index) in selectUsersDisplay[idx][0].plan_users">
                                                <a class="currentUser-flg"
                                                  :key="index"
                                                   v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                   href="#"><i class="far fa-flag"></i></a>
                                                <!--                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                                <!--                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                                <!--                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                            </template>
                                        </vs-col>
                                        <!--PAC_5-1698 E-->
                                        <vs-col vs-w="10">
                                            <vs-checkbox :value="isNotReturnCircular"
                                                         @click="toggleReturnCircular(circularUserLastSend.id)"
                                                         v-if="selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].id == circularUserLastSend.id || (selectUsersDisplay[idx][0].plan_users && selectUsersDisplay[idx][0].plan_users.some(_=>_.id ==circularUserLastSend.id)))">
                                                最終承認者から直接社外に送る
                                            </vs-checkbox>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>
                                <vs-col  vs-w="6" class="child-block" >
                                    <draggable
                                            :list="selectUsersDisplay[idx]"
                                            @change="onDragItemChange"
                                            @end="onDragEnd"
                                            animation="300"
                                            group="selectUsers"
                                            :class="['full-width', 'child-range-'+idx]"
                                            :move="onItemMoving"
                                            swap="true"
                                            v-if="selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].parent_send_order == circularUserLastSend.parent_send_order)">

                                            <template  v-for="(user, index) in selectUsersDisplay[idx]">
                                        <vs-row

                                                v-if="index > 0"
                                                vs-type="flex"
                                                vs-justify="center"
                                                :key="user.email + index + changeTimes" >
                                            <vs-col vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!user.plan_users"
                                                    :class="['item child-order item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index === 1 ? 'first' : '', (index === selectUsersDisplay[idx].length-1 && index >1) ? 'last': '', (index === 1 && selectUsersDisplay[idx].length > 2) ? 'return-flg': '']" >
                                                <span>{{user.name || '社員'}} - {{ user.mst_company_name}}</span>
                                                <span>【{{user.email}}】</span>
                                                <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === (index + 1)"
                                                      class="final"> 最終</span>
                                                <a class="currentUser-flg"
                                                   v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                   href="#"> <i class="far fa-flag"></i></a>
                                                <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                   href.prevent v-on:click="onRemoveCircularUser(user.id)"
                                                   class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users"
                                                :class="['item child-order ', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <span :key="index">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <a class="currentUser-flg"
                                                        :key="index"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                                    <!--                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                                    <!--                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                                    <!--                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                        </vs-row>
                                            </template>
                                        <vs-row
                                                vs-type="flex"
                                                vs-justify="center">
                                            <vs-col vs-w="10"
                                                    :class="['item child-order', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!selectUsersDisplay[idx][0].plan_users && !isNotReturnCircular  && selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].parent_send_order == circularUserLastSend.parent_send_order)">
                                                <span>{{selectUsersDisplay[idx][0].name}} - {{selectUsersDisplay[idx][0].mst_company_name}}</span>
                                                <span>【{{selectUsersDisplay[idx][0].email}}】</span>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="selectUsersDisplay[idx][0].plan_users && !isNotReturnCircular &&selectUsersDisplay[idx][0].plan_users.length>0  && (selectUsersDisplay[idx][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                :class="['item child-order ', 'bg-green']"  >
                                                <span>合議 ({{selectUsersDisplay[idx][0].plan_mode==1?"全員必須":selectUsersDisplay[idx][0].plan_score+"人"}})</span>
                                                <template v-for="(user, index) in selectUsersDisplay[idx][0].plan_users">
                                                    <span :key="index">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                        </vs-row>
                                    </draggable>
                                    <div :class="['full-width', 'child-range-'+idx]"
                                         v-if="selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].parent_send_order != circularUserLastSend.parent_send_order)">
                                        <template  v-for="(user, index) in selectUsersDisplay[idx]">
                                        <vs-row

                                                v-if="index > 0"
                                                vs-type="flex"
                                                vs-justify="space-around"
                                                :key="user.email + index + changeTimes" >
                                            <vs-col vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!user.plan_users"
                                                    :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index === 1 ? 'first' : '', (index === selectUsersDisplay[idx].length-1 && index >1) ? 'last': '', (index === 1 && selectUsersDisplay[idx].length > 2) ? 'return-flg': '']" >
                                                <span>{{user.name || '社員'}} - {{ ((loginUser && loginUser.email === selectUsers[0].email) || (userHashInfo && userHashInfo.email === selectUsers[0].email))  ? user.mst_company_name : index}}</span>
                                                <span>【{{user.email}}】</span>
                                                <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === (index + 1)"
                                                      class="final"> 最終</span>
                                                <a class="currentUser-flg"
                                                   v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                   href="#"> <i class="far fa-flag"></i></a>
                                                <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                   href.prevent v-on:click="onRemoveCircularUser(user.id)"
                                                   class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users"
                                                :class="['item child-order ', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <span :key="index">{{user.name || '社員'}} 【{{user.email}}】</span>
                                                </template>
                                                <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === (index + 1)"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <a class="currentUser-flg"
                                                        :key="index"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                                    <!--                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                                    <!--                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                                    <!--                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                                </template>
                                            </vs-col>
                                            <!--PAC_5-1698 E-->
                                        </vs-row>
                                        </template>
                                    </div>
                                </vs-col>
                            </vs-row>
                            </template>
                        </div>
                        <form v-if="enable_any_address!=2 && !checkShowAddress && allowAddDestination && (!$store.state.home.usingPublicHash  || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) && !isTemplateCircular">
                            <vs-row class="mt-8">
                                <div class="vx-col w-1/2 pr-2">
                                    <vue-suggestion :items="userSuggestions"
                                                    v-model="userSuggestModel"
                                                    placeholder="回覧先の名前"
                                                    :setLabel="setUsernameSuggestLabel"
                                                    :itemTemplate="usernameTemplate"
                                                    :minLen="1"
                                                    :disabled="suggestDisabled"
                                                    @onInputChange="getUsersSuggestionList"
                                                    @onItemSelected="onSuggestSelect"
                                                    @focus="getFocusUsersSuggestionList">
                                    </vue-suggestion>
                                    <!--<vue-simple-suggest placeholder="回覧先の名前" :disabled="suggestDisabled" v-model="usernameSelect" display-attribute="name" value-attribute="email" :list="getUsersSuggestionList" @select="onSuggestSelect"> </vue-simple-suggest>-->
                                </div>
                                <div class="vx-col w-1/2 pl-2">
                                    <vue-suggestion :items="emailSuggestions"
                                                    v-model="emailSuggestModel"
                                                    placeholder="回覧先のメールアドレス"
                                                    :setLabel="setEmailSuggestLabel"
                                                    :itemTemplate="emailTemplate"
                                                    :minLen="1"
                                                    @onInputChange="getEmailsSuggestionList"
                                                    @onItemSelected="onSuggestSelect"
                                                    @focus="getFocusEmailsSuggestionList">
                                    </vue-suggestion>
                                    <span class="text-danger text-sm" v-show="emailSuggestValidateMsg">{{ emailSuggestValidateMsg }}</span>
                                    <!--<vue-simple-suggest  display-attribute="email" value-attribute="email" :list="getUsersSuggestionList" @select="onSuggestSelect">
                                        <vs-input placeholder="回覧先のメールアドレス" v-validate="'required|email'" required name="email" class="inputx w-full"  @input="onEmailSuggestChange" v-model="emailSelect"  />
                                        <span class="text-danger text-sm" v-show="errors.has('email')">{{ errors.first('email') }}</span>
                                    </vue-simple-suggest>-->
                                </div>

                            </vs-row>
                            <vs-row class="mt-4">
                                <vs-col vs-type="flex" vs-w="12"  vs-justify="flex-end" vs-align="center">
                                    <vs-button @click.prevent="submitMailStepForm" class="square mr-0"  color="primary" type="filled"> 追加</vs-button>
                                </vs-col>
                            </vs-row>
                        </form>
                    </div>
                    <div v-if="circularUserLastSend.edition_flg == 1" class="vx-col lg:pr-0 w-full  mb-4">
                        <!--<vx-card :hideLoading="true" class="mb-4">-->
                        <div slot="no-body">

                        </div><br>
                        <vs-row class="border-bottom pb-4">
                            <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12">
                                <h4>閲覧ユーザー設定</h4>
                            </vs-col>
                            <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12" style="padding-left: 47%;">
                              <span @click="onAroundArrow">
                                <vs-icon id="arrow" class="mt-5 around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                              </span>
                            </vs-col>
                        </vs-row>
                        <div class="mail-steps mail-list_dialog" v-show="searchAreaFlg">
                            <div class="mail-view-list">
                                <draggable v-model="getUserView" >
                                    <vs-row class="item-user-view" vs-type="flex" v-for="(user, index) in getUserView" v-bind:key="user.email + index + changeTimes" :index="index">
                                        <vs-col vs-w="10">
                                            <span>{{user.name}}【{{user.email}}】</span>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                        </vs-col>
                                    </vs-row>
                                </draggable>
                                <draggable v-model="selectUserView" >
                                    <vs-row class="item-user-view" vs-type="flex" v-for="(user, index) in selectUserView" v-bind:key="user.email + index + changeTimes" :index="index">
                                        <vs-col vs-w="10">
                                            <span>{{user.name}}【{{user.email}}】</span>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                            <a href.prevent v-on:click="deleteUserView(user.email)" class="text-danger"><i class="fas fa-times"></i></a>
                                        </vs-col>
                                    </vs-row>
                                </draggable>
                            </div>
                            <div class="mail-form">
                                <form>
                                    <vs-row class="mt-8">
                                        <div class="vx-col w-1/2 pr-2">
                                            <vue-suggestion :items="userViewSuggestions"
                                                            v-model="userViewSuggestModel"
                                                            placeholder="閲覧ユーザーの名前"
                                                            :setLabel="setUserViewnameSuggestLabel"
                                                            :itemTemplate="usernameTemplate"
                                                            :minLen="1"
                                                            :disabled="suggestViewDisabled"
                                                            @onInputChange="getUsersViewSuggestionList"
                                                            @onItemSelected="onSuggestViewSelect"
                                                            @focus="getFocusUsersViewSuggestionList">
                                            </vue-suggestion>
                                        </div>
                                        <div class="vx-col w-1/2 pl-2">
                                            <vue-suggestion :items="emailViewSuggestions"
                                                            v-model="emailViewSuggestModel"
                                                            placeholder="閲覧先のメールアドレス"
                                                            :setLabel="setEmailViewSuggestLabel"
                                                            :itemTemplate="emailTemplate"
                                                            :minLen="1"
                                                            @onInputChange="getEmailsViewSuggestionList"
                                                            @onItemSelected="onSuggestViewSelect"
                                                            @focus="getFocusEmailsViewSuggestionList">
                                            </vue-suggestion>
                                            <span class="text-danger text-sm" v-show="emailViewSuggestValidateMsg">{{ emailViewSuggestValidateMsg }}</span>
                                        </div>

                                    </vs-row>
                                    <vs-row class="mt-4">
                                        <vs-col vs-type="flex" vs-w="12" vs-xs="12" vs-justify="flex-end" vs-align="flex-end">
                                            <vs-button @click.prevent="addUserView" class="square mr-0"  color="primary" type="filled" > 追加</vs-button>
                                        </vs-col>
                                    </vs-row>
                                </form>
                            </div>
                        </div>

                        <!--</vx-card>-->
                    </div>
                </vx-card>
            </div>
            <vs-popup class="circular-destination-page-dialog" title=""  :active.sync="confirmEdit" :class="isMobile?'mobile':''">
                <div v-if="allowAddDestination && (!$store.state.home.usingPublicHash  || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) && !isTemplateCircular" class="vx-col w-full  mb-4">
                    <vx-card class="h-full">
                        <vs-row>
                            <vs-col vs-w="12">
                                <vs-tabs>
                                    <vs-tab @click="showTree = true" label="アドレス帳">

                                    </vs-tab>
                                    <vs-tab @click="onFavoriteSelect(),showTree = false" label="お気に入り" v-if="!specialCircularFlg">
                                        <vs-row style="align-items:baseline;margin-bottom: 10px">
                                            <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="12">
                                                <vx-input-group  class="w-full mb-0">
                                                    <vs-input v-model="searchFavorite" />
                                                    <template slot="append">
                                                        <div class="append-text btn-addon">
                                                            <vs-button color="primary" @click="onSearchFavorite()"><i class="fas fa-search"></i></vs-button>
                                                        </div>
                                                    </template>
                                                </vx-input-group>
                                            </vs-col>
                                        </vs-row>
                                        <div class="favorite_dialog">
                                            <draggable v-model="arrFavorite">
                                                <transition-group>
                                                    <div v-for="(itemFavorite, indexFavorite) in arrFavorite" :key="'favorites-'+indexFavorite" class="item" @click="changeTableShow(indexFavorite)" >
                                                        <div class="mt-0" style="margin-top:5px;line-height:2.5rem;border-bottom: none;margin-bottom: -1px;">{{indexFavorite+1}}.{{itemFavorite[0].favorite_name ? itemFavorite[0].favorite_name:'名称未設定'}}</div>
                                                        <div  class="mt-0"  style="display: flex;justify-content: space-between;border-top:none">
                                                            <div style="width: 60px;">
                                                              <template v-if="isMobile">
                                                                <vs-button class="vs-button_dialog square action action_dialog" color="primary" @touchend="onApplyFavorite(itemFavorite)">追加</vs-button>
                                                              </template>
                                                              <template v-else>
                                                                <vs-button class="vs-button_dialog square action action_dialog" color="primary" @click="onApplyFavorite(itemFavorite)">追加</vs-button>
                                                              </template>
                                                            </div>
                                                            <div style="width: 60px;">
                                                              <template v-if="isMobile">
                                                                <vs-button class="vs-button_dialog square action action_dialog"  v-if="!itemFavorite[0].use_plan_flg" color="primary" @touchend="onEditFavorite(itemFavorite,indexFavorite)">編集</vs-button>
                                                              </template>
                                                              <template v-else>
                                                                <vs-button class="vs-button_dialog square action action_dialog"  v-if="!itemFavorite[0].use_plan_flg" color="primary" @click="onEditFavorite(itemFavorite,indexFavorite)">編集</vs-button>
                                                              </template>
                                                            </div>
                                                            <ul class="like_addrs"  v-if="tableshow.hasOwnProperty(indexFavorite)">
                                                                <li v-for="(uval,uindex)  in itemFavorite" :key="uindex">
                                                                    <template v-if="uval.plan_users" >
                                                                        <div class="like_addrs_content" style="border-radius: 5px 5px 0 0"><span>{{uindex + 1}}:</span> 合議</div>
                                                                        <div class="like_addrs_content" :style="{borderRadius:index==(uval.plan_users.length-1)?'0 0 5px 5px':'0'}" v-for="(user,index) in uval.plan_users" :key="index"><span> </span> {{user.name}} [{{user.email}}]</div>
                                                                    </template>
                                                                    <template v-else>
                                                                        <div class="like_addrs_content"><span>{{uindex + 1}}:</span>{{uval.name}} [{{uval.email}}]</div>
                                                                    </template>
                                                                    <div class="triangle"></div>
                                                                </li>
                                                            </ul>
                                                            <div style="flex-grow: 1;" vs-type="flex"  v-if="!tableshow.hasOwnProperty(indexFavorite)">
                                                                <template v-for="(itemUser, index) in itemFavorite">
                                                                    <template v-if="itemFavorite.length<=6 || (itemFavorite.length > 6 && index<5) ">
                                                                        <div class="name" :key="'favorite-'+index+'-name'">{{ itemUser.plan_users?'合議':itemUser.name.split(" ").map((w) => w[0]).join(" ") }}</div>
                                                                        <div class="toright" :key="'favorite-'+index+'-icon'" v-if="index<itemFavorite.length-1"><i class="fas fa-caret-right"></i></div>
                                                                    </template>
                                                                </template>
                                                                <template v-if="itemFavorite.length>6">
                                                                    <div class="name">...</div>
                                                                </template>
                                                            </div>
                                                            <div style="width: 30px; text-align: right; line-height: 12px;">
                                                                <template v-if="isMobile" >
                                                                    <a href="#" class="text-danger" @touchend="onRemoveFavorite(itemFavorite[0].favorite_no)"><i class="fas fa-times"></i></a>
                                                                </template>
                                                                <template v-else>
                                                                    <a href="#" class="text-danger" @click="onRemoveFavorite(itemFavorite[0].favorite_no)"><i class="fas fa-times"></i></a>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </transition-group>
                                            </draggable>
                                        </div>
                                    </vs-tab>
                                </vs-tabs>
                            </vs-col>
                            <vs-col vs-w="12">
                                <ContactTree :opened="confirmEdit" :editorShowFlg="!this.userHashInfo" v-show="showTree" :treeData="treeData" @onTreeAddToStepClick="onTreeAddToStepClick" @onNodeClick="showModalEditContacts"/>
                            </vs-col>
                        </vs-row>
                    </vx-card>
                </div>
            </vs-popup>
            <!-- PAC_5-511 END -->
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
                      <vs-input v-model="outsideAccessCode" maxlength="6" :disabled="!(this.circularUserLastSend.child_send_order == 0 || (this.circularUserLastSend.parent_send_order > 0 && this.circularUserLastSend.child_send_order == 1))"/>
                      <template slot="append">
                        <div class="append-text btn-addon">
                          <vs-button color="primary" :disabled="!(this.circularUserLastSend.child_send_order == 0 || (this.circularUserLastSend.parent_send_order > 0 && this.circularUserLastSend.child_send_order == 1))" v-on:click="generateAccessCodeOutside"><i class="fas fa-sync-alt"></i></vs-button>
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
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="onBackClick"> 戻る</vs-button>
                    <vs-button v-if="checkShowButtonApply" class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState" v-on:click="onSendClick"><i class="fas fa-check" style="margin-right: 5px;"></i> 申請する</vs-button>
                    <vs-button v-else class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState" v-on:click="onSendClick();"><span><img :src="require('@assets/images/pages/home/admit.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 承認</vs-button>
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
            <vs-row v-if="!$store.state.home.usingPublicHash" class="mp-3 pt-6" vs-type="flex" style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px">
                <vs-checkbox :value="operationNotice" v-on:click="operationNotice = !operationNotice">次回から表示しない。</vs-checkbox>
            </vs-row>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onUpdateOperationNoticeClick" > 閉じる</vs-button>
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
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSyncOperationClick" > 閉じる</vs-button>
            </vs-row>
        </modal>

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
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSaveFileFailNoticeClick" > 閉じる</vs-button>
            </vs-row>
        </modal>
      <vs-popup class="holamundo"  title="エラー" :active.sync="showPopupErrosEmojiLength">
        <vs-row>
          <p>絵文字は入力できません。</p>
        </vs-row>
        <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
          <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosEmojiLength = false"> 閉じる</vs-button>
        </vs-row>
      </vs-popup>
        <!--// PAC_5-1973 Start-->
        <vs-popup class="holamundo"  title="エラー" :active.sync="showPopupErrosTextLength">
            <vs-row>
                <p>件名が50文字超えています。</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosTextLength = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
        <!--// PAC_5-1973 End-->

        <!--// PAC_5-2185 start-->
        <vs-popup class="holamundo"  title="エラー" :active.sync="showPopupErrosContentLength">
            <vs-row>
                <p>コメントが500文字超えています。</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosContentLength = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
        <!--// PAC_5-2185 end-->
        <vs-popup :active.sync="confirmDuplicateEmail" title="既に登録済みのメールアドレスです。このまま登録しますか？">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onAllowDuplicate" color="warning">はい</vs-button>
                    <vs-button @click="onCancelDuplicate" color="dark" type="border">いいえ</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup :active.sync="confirmDelete" title="アドレス帳から削除します。よろしいですか？">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDeleteContact" color="warning">はい</vs-button>
                    <vs-button @click="confirmDelete = false" color="dark" type="border">いいえ</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup title="アドレス帳編集" :active.sync="showPopupEditContacts">
            <div class="mt-5">
                <form>
                    <vs-row>
                        <vs-col vs-w="3" class="text-left pr-3 pt-3">グループ</vs-col>
                        <vs-col vs-type="" vs-w="9">
                            <vs-input placeholder="グループ" v-model="editContact.group_name" class="w-full" />
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="3" class="text-left pr-3 pt-3">名前<span class="ml-1 text-red">*</span></vs-col>
                        <vs-col vs-type="" vs-w="9">
                            <vs-input placeholder="名前" v-validate="'required'" v-model="editContact.name" name="name" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('name')">{{ errors.first('name') }}</span>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="3" class="text-left pr-3 pt-3">メールアドレス<span class="ml-1 text-red">*</span></vs-col>
                        <vs-col vs-type="" vs-w="9">
                            <vs-input type="email" v-validate="'required|email'" required name="email"
                                      placeholder="メールアドレス" v-model="editContact.email" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('email')">{{ errors.first('email') }}</span>
                        </vs-col>
                    </vs-row>
                </form>
            </div>

            <vs-row class="mt-5">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12" style="display:block;text-align:center">
                    <vs-button @click="confirmDelete = true" color="primary" type="border" v-if="editContact.id">削除する</vs-button>
                    <vs-button @click="onUpdateContact()" color="primary" v-if="editContact.id">変更する</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
        <vs-popup class="holamundo"  title="送信先選択" :active.sync="popupSelectFavoriteAccountActive">
          <p>複数の登録がある宛先です。</p>
          <p>送信先の環境を１つ選択してください。</p>
          <p class="mt-3" v-html="favoriteEmailSelect"></p>
          <vs-row class="mt-3" v-if="favoriteCheckEmailExisting.length">
            <vs-button class="square mb-3"
                       v-for="(account, indexAccount) in favoriteCheckEmailExisting" v-bind:key="indexAccount"
                       :color="arrBtnColorAccount[account.env_flg]"  type="filled"
                       v-on:click="selectFavoriteAccount(account)"
            >{{ account.system_name }}</vs-button>
          </vs-row>
          <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center" class="bdt">
            <vs-button class="square mt-3" color="#bdc3c7" type="filled" v-on:click="popupSelectFavoriteAccountActive = false"> 閉じる</vs-button>
          </vs-row>
        </vs-popup>
        <vs-popup title="回覧順編集" :active.sync="showEditFavorite" :class="'detail-popup ' + (isMobile?'mobile':'')">
          <div class="mail-steps">
            <div class="full-width range-0 group">
              <vs-row vs-justify="center" vs-type="flex" v-for="(user, index) in editFavoriteItem" :key="user.email + index"
                      :class="[(index === editFavoriteItem.length-1 && index + 1 >1)  ? 'last-row': '']">
                <vs-col
                    vs-w="10"
                    vs-type="flex"
                    vs-align="flex-start"
                    :class="['item child-order item-draggable', index + 1 === 1 ? 'first' : '',(index === editFavoriteItem.length-1 && index + 1 >1)  ? 'last': '', (index + 1 === 1 && editFavoriteItem.length > 1) ? 'return-flg': '',
                                                      (user.email === editFavoriteItem.email) ? 'me': '']" style="width: 90%;background:#C8EFC8;">
                  <div draggable
                       @dragstart="startDragFavorite(user, $event)"
                       @dragend="endDragFavorite($event)"
                       @dragover="overDragFavorite(user, $event)"
                       @drop="onDropFavorite(user,$event)"
                       @dragenter.prevent
                       class="dropable-item h-full w-full" style="padding: 10px;">
                    <div>{{index + 1}} - {{user.name || '社員'}}</div>
                    <div>【{{user.email}}】</div>
                    <span v-if="editFavoriteItem.length === (index + 1) && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</span>
                    <a href.prevent v-on:click="deleteFavoriteUser(user.id, index)" class="text-danger remove-flag" v-if="editFavoriteItem.length !== 1"><i class="fas fa-times"></i></a>
                  </div>
                </vs-col>
              </vs-row>
            </div>
          </div>
          <vs-col style="padding-left: 6%;padding-right: 3%;">
            <ContactTree :opened="showEditFavorite" :number="'1'"  :treeData="treeData" @onTreeAddToStepClick="onTreeAddToFavoriteClick"  @onNodeClick="showModalEditContacts"/>
          </vs-col>
          <vs-row class="mt-5">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12" style="display:block;text-align:center">
              <vs-button @click="showEditFavorite = false" color="primary" type="border">閉じる</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>

    </div>

        <!-- 5-277 mobile html -->
        <div id="circular-destination-page-mobile" :class="isMobile?'mobile':''">
            <div style="width:100%;margin-bottom:10px;border-bottom:1px solid #ddd;">
                <span @click="onBackClick"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
                <div style="display:inline;position:relative;top:-10px;">プレビュー・捺印</div>
            </div>

            <!--
            <div class="vx-row">
                <vs-col vs-align="left" vs-justify="left" class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                    <ul class="breadcrumb">
                        <li><p><span class="badge">1</span> プレビュー・捺印</p></li>
                        <li><p><span class="badge">2</span> 回覧先設定</p></li>
                    </ul>
                </vs-col>
            </div>
            -->
            <div class="vx-row">
                <div :class="'vx-col mb-4 lg:pr-0 ' + (allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ? 'w-full lg:w-1/2 ': 'w-full lg:pr-4')">

                    <vs-row vs-type="flex" class="mb-1" style="position: relative;">
                      <vs-col vs-type="flex" vs-w="8">
                        <div class="circular-destination-title">
                          <h4>宛先・回覧順</h4>
                          <div id="handleViewMailList" class="show" v-on:click="handleViewMailList">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><path d="M118.6 105.4l128 127.1C252.9 239.6 256 247.8 256 255.1s-3.125 16.38-9.375 22.63l-128 127.1c-9.156 9.156-22.91 11.9-34.88 6.943S64 396.9 64 383.1V128c0-12.94 7.781-24.62 19.75-29.58S109.5 96.23 118.6 105.4z"/></svg>
                          </div>
                        </div>
                      </vs-col>
                      <vs-col vs-type="flex" vs-w="4" v-show="(allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external))) && !isTemplateCircular && enable_any_address!=2">
                        <span @click="onDepartmentUsersSelect()" style="cursor: pointer; position: absolute;right:0;top:-5px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="2.5rem" height="2.5rem" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open "><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                        </span>
                      </vs-col>
                    </vs-row>
                    <!--
                    <div><p class="mt-2 mb-2">全ての回覧後に申請者に戻ります</p></div>
                    -->
                    <vx-card :hideLoading="true" id="mail-steps-card">

                        <div class="mail-steps">
                          <div :class="[allowAddDestination && (!$store.state.home.usingPublicHash || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) ? 'mail-list' : 'mailListViewOnly', (!selectUsersDisplay || selectUsersDisplay.length == 1 || !selectUsers[0].return_flg)?'not-return':'']">

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
                                  <vs-col vs-w="6" vs-xs="12" class="child1st-block" v-if="!isTemplateCircular">
                                      <draggable
                                              :list="selectUsersDisplay[0]"
                                              v-if="selectUsersDisplay"
                                              @change="onDragItemChange"
                                              @end="onDragEnd"
                                              animation="300"
                                              group="selectUsers"
                                              :class="['full-width range-0']"
                                              :move="onItemMoving"
                                              swap="true"  >
                                          <template v-for="(user, index) in selectUsersDisplay[0]" >
                                            <vs-row vs-justify="center" vs-type="flex" v-if="index > 0" :key="createKeyLoop(index, user.email)">
                                                <vs-col
                                                        vs-w="10"
                                                        vs-xs="12"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        :class="['item child-order item-draggable', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']"  v-if="!user.plan_users">
                                                    <div class="name">{{index}} - {{user.name || '社員'}}</div>
                                                    <div class="email">【{{user.email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)"
                                                          class="final"> 最終</span>
                                                    <a class="currentUser-flg"
                                                      v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                      href="#"><i class="far fa-flag"></i></a>
                                                    <!-- <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                      href.prevent v-on:click="onRemoveCircularUser(user.id)"
                                                      class="text-danger remove-flag"><i class="fas fa-times"></i></a> -->
                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                      href.prevent @touchend="onRemoveCircularUser(user.id)"
                                                      class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10"
                                                    vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="user.plan_users"
                                                    :class="['item child-order item-draggable', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']"  >
                                                    <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                    <div v-for="(user, index) in user.plan_users" :key="createKeyLoop(index, user.email)">
                                                        <div class="name">{{user.name || '社員'}}</div>
                                                        <div class="email">【{{user.email}}】</div>
                                                    </div>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)"
                                                          class="final"> 最終</span>
                                                    <div v-for="(user, index) in user.plan_users" :key="createKeyLoop(index, user.id)">
                                                        <a class="currentUser-flg"
                                                          v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                          href="#"><i class="far fa-flag"></i></a>
                                          <!--           <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                          <!--            href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                          <!--             class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                                    </div>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                          </template>
                                          <vs-row vs-type="flex" vs-justify="center">
                                              <vs-col vs-w="10"
                                                      vs-xs="12"
                                                      vs-type="flex"
                                                      vs-align="flex-start"
                                                      v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1 &&
                                                      (selectUsersDisplay[0][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                      class="item child-order me">
                                                  <div class="name">{{selectUsers[0].name || '社員'}}</div>
                                                  <div class="email">【{{selectUsers[0].email}}】</div>
                                              </vs-col>
                                          </vs-row>
                                      </draggable>
                                  </vs-col>
                                  <!-- template route users start -->
                                  <vs-col vs-w="6" vs-xs="12" class="child1st-block" v-if="isTemplateCircular">
                                      <div    v-if="templateUserRoutes"
                                              :class="['full-width range-0']"  >
                                          <vs-row vs-justify="center" vs-type="flex"
                                                  v-for="(userRoutes, userRoutesIndex) in templateUserRoutes" :key="createKeyLoop(userRoutesIndex)">
                                              <vs-col
                                                      vs-w="10"
                                                      vs-xs="12"
                                                      vs-type="flex"
                                                      vs-align="flex-start"
                                                      :class="['item child-order ', userRoutesIndex === 0 ? 'first' : '', (userRoutesIndex === templateUserRoutes.length-1 && userRoutesIndex >0)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                  <span>{{userRoutes[0].user_routes_name}}</span>
                                                  <div v-for="(user, index) in userRoutes" :key="createKeyLoop(index, user.email)">
                                                      <div class="name">{{user.name || '社員'}}</div> 
                                                      <div class="email">【{{user.email}}】</div>
                                                  </div>
                                                  <span v-if="templateUserRoutes.length === userRoutesIndex + 1"
                                                        class="final"> 最終</span>
                                                  <div v-for="(user, index) in userRoutes" :key="createKeyLoop(index, user.id)">
                                                      <a class="currentUser-flg"
                                                        v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                        href="#"><i class="far fa-flag"></i></a>
                                                      <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"
                                                        href.prevent @touchend="onRemoveCircularUser(user.id)"
                                                        class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                  </div>
                                              </vs-col>
                                          </vs-row>
                                          <vs-row vs-type="flex" vs-justify="center">
                                              <vs-col vs-w="10"
                                                      vs-xs="12"
                                                      vs-type="flex"
                                                      vs-align="flex-start"
                                                      v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1 &&
                                                      (selectUsersDisplay[0][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                      class="item child-order me">
                                                  <div class="name">{{selectUsers[0].name || '社員'}}</div>
                                                  <div class="email">【{{selectUsers[0].email}}】</div>
                                              </vs-col>
                                          </vs-row>
                                      </div>
                                  </vs-col>
                                  <!-- template route users end -->
                              </vs-row>

                            <template v-for="(group, idx) in selectUsersDisplay">
                              <vs-row
                                      vs-type="flex"
                                      vs-align="space-around"
                                      :class="['group parent-block not-return', (selectUsersDisplay[idx].length > 2) ? 'return-flg': '']"
                                      :key="createKeyLoop(idx)" v-if="idx > 0" >
                                  <vs-col vs-w="6" vs-xs="12">
                                      <vs-row
                                              vs-type="flex"
                                              vs-justify="space-around"
                                              :key="selectUsersDisplay[idx][0].email + '0'" >
                                          <vs-col vs-w="10" vs-xs="12"
                                                  :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                  vs-type="flex"
                                                  vs-align="flex-start" v-if="(!specialCircularFlg || specialCircularReceiveFlg || !selectUsersDisplay[idx][0].special_site_receive_flg)&&(!selectUsersDisplay[idx][0].plan_users)">                                            <span>{{selectUsersDisplay[idx][0].name}} - {{selectUsersDisplay[idx][0].mst_company_name}}</span>
                                              <div class="name">【{{selectUsersDisplay[idx][0].email}}】</div>
                                              <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                    class="final"> 最終</span>
                                              <a class="currentUser-flg"
                                                v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx][0].id"
                                                href="#"> <i class="far fa-flag"></i></a>
                                          </vs-col>
                                          <vs-col vs-w="10" vs-xs="12"
                                                  :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                  vs-type="flex"
                                                  vs-align="flex-start" v-if="specialCircularFlg && !specialCircularReceiveFlg && selectUsersDisplay[idx][0].special_site_receive_flg && !selectUsersDisplay[idx][0].plan_users">
                                            <span>{{groupName}}</span>
                                            <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                  class="final"> 最終</span>
                                            <a class="currentUser-flg"
                                              v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx][0].id"
                                              href="#"> <i class="far fa-flag"></i></a>
                                          </vs-col>
                                          <!--PAC_5-1698 S-->
                                          <vs-col
                                              vs-w="10" vs-xs="12"
                                              vs-type="flex"
                                              vs-align="flex-start"
                                              v-if="selectUsersDisplay[idx][0].plan_users"
                                              :class="['item item-draggable', idx % 3 == 1 ? 'bg-green': (idx % 3 == 2 ? 'bg-green' : 'bg-green')]">
                                              <span>合議 ({{selectUsersDisplay[idx][0].plan_mode==1?"全員必須":selectUsersDisplay[idx][0].plan_score+"人"}})</span>
                                              <div v-for="(user, index) in selectUsersDisplay[idx][0].plan_users" :key="createKeyLoop(index, user.email)">
                                                  <div class="name">{{user.name || '社員'}}</div> 
                                                  <div class="email">【{{user.email}}】</div>
                                              </div>
                                              <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                    class="final"> 最終</span>
                                              <div v-for="(user, index) in selectUsersDisplay[idx][0].plan_users" :key="createKeyLoop(index, user.id)">
                                                  <a class="currentUser-flg"
                                                    v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                    href="#"><i class="far fa-flag"></i></a>
                                                  <!--                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                                  <!--                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                                  <!--                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                              </div>
                                          </vs-col>
                                          <!--PAC_5-1698 E-->
                                          <vs-col vs-w="10" vs-xs="12" id="ckeckReturnCircular">
                                              <vs-checkbox :value="isNotReturnCircular"
                                                          @click="toggleReturnCircular(circularUserLastSend.id)"
                                                          v-if="selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].id == circularUserLastSend.id || (selectUsersDisplay[idx][0].plan_users && selectUsersDisplay[idx][0].plan_users.some(_=>_.id ==circularUserLastSend.id)))">
                                                  最終承認者から直接社外に送る
                                              </vs-checkbox>
                                          </vs-col>
                                      </vs-row>
                                  </vs-col>
                                  <vs-col  vs-w="6" vs-xs="12" class="child-block" >
                                      <draggable
                                              :list="selectUsersDisplay[idx]"
                                              @change="onDragItemChange"
                                              @end="onDragEnd"
                                              animation="300"
                                              group="selectUsers"
                                              :class="['full-width', 'child-range-'+idx]"
                                              :move="onItemMoving"
                                              swap="true"
                                              v-if="selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].parent_send_order == circularUserLastSend.parent_send_order)">

                                          <template v-for="(user, index) in selectUsersDisplay[idx]">
                                            <vs-row
                                                    v-if="index > 0"
                                                    vs-type="flex"
                                                    vs-justify="center"
                                                    :key="createKeyLoop(index, user.email)">
                                                <vs-col vs-w="10" vs-xs="12"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        v-if="!user.plan_users"
                                                        :class="['item child-order item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index === 1 ? 'first' : '', (index === selectUsersDisplay[idx].length-1 && index >1) ? 'last': '', (index === 1 && selectUsersDisplay[idx].length > 2) ? 'return-flg': '']" >
                                                    <div class="name">{{user.name || '社員'}} - {{ user.mst_company_name}}</div>
                                                    <div class="email">【{{user.email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === (index + 1)"
                                                          class="final"> 最終</span>
                                                    <a class="currentUser-flg"
                                                      v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                      href="#"> <i class="far fa-flag"></i></a>
                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                      href.prevent @touchend="onRemoveCircularUser(user.id)"
                                                      class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="user.plan_users"
                                                    :class="['item child-order ', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                    <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                    <div v-for="(user, index) in user.plan_users" :key="createKeyLoop(index, user.email)">
                                                        <div class="name">{{user.name || '社員'}}</div> 
                                                        <div class="email">【{{user.email}}】</div>
                                                    </div>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)"
                                                          class="final"> 最終</span>
                                                    <div v-for="(user, index) in user.plan_users" :key="createKeyLoop(index, user.id)">
                                                        <a class="currentUser-flg"
                                                          v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                          href="#"><i class="far fa-flag"></i></a>
                                                        <!--                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                                        <!--                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                                        <!--                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                                    </div>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                          </template>
                                          <vs-row
                                                  vs-type="flex"
                                                  vs-justify="center">
                                              <vs-col vs-w="10" vs-xs="12"
                                                      :class="['item child-order', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                      vs-type="flex"
                                                      vs-align="flex-start"
                                                      v-if="!selectUsersDisplay[idx][0].plan_users && !isNotReturnCircular  && selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].parent_send_order == circularUserLastSend.parent_send_order)">
                                                  <div class="name">{{selectUsersDisplay[idx][0].name}} - {{selectUsersDisplay[idx][0].mst_company_name}}</div>
                                                  <div class="email">【{{selectUsersDisplay[idx][0].email}}】</div>
                                              </vs-col>
                                              <!--PAC_5-1698 S-->
                                              <vs-col
                                                  vs-w="10" vs-xs="12"
                                                  vs-type="flex"
                                                  vs-align="flex-start"
                                                  v-if="selectUsersDisplay[idx][0].plan_users && !isNotReturnCircular &&selectUsersDisplay[idx][0].plan_users.length>0  && (selectUsersDisplay[idx][0].parent_send_order == circularUserLastSend.parent_send_order)"
                                                  :class="['item child-order ', 'bg-green']"  >
                                                  <span>合議 ({{selectUsersDisplay[idx][0].plan_mode==1?"全員必須":selectUsersDisplay[idx][0].plan_score+"人"}})</span>
                                                  <div v-for="(user, index) in selectUsersDisplay[idx][0].plan_users" :key="createKeyLoop(index, user.email)">
                                                      <div class="name">{{user.name || '社員'}}</div> 
                                                      <div class="email">【{{user.email}}】</div>
                                                  </div>
                                              </vs-col>
                                              <!--PAC_5-1698 E-->
                                          </vs-row>
                                      </draggable>
                                      <div :class="['full-width', 'child-range-'+idx]"
                                          v-if="selectUsersDisplay[idx].length > 1 && (selectUsersDisplay[idx][0].parent_send_order != circularUserLastSend.parent_send_order)">
                                          <template v-for="(user, index) in selectUsersDisplay[idx]">
                                            <vs-row
                                                    v-if="index > 0"
                                                    vs-type="flex"
                                                    vs-justify="space-around"
                                                    :key="createKeyLoop(index, user.email)">
                                                <vs-col vs-w="10" vs-xs="12"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        v-if="!user.plan_users"
                                                        :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index === 1 ? 'first' : '', (index === selectUsersDisplay[idx].length-1 && index >1) ? 'last': '', (index === 1 && selectUsersDisplay[idx].length > 2) ? 'return-flg': '']" >
                                                    <div class="name">{{user.name || '社員'}} - {{ ((loginUser && loginUser.email === selectUsers[0].email) || (userHashInfo && userHashInfo.email === selectUsers[0].email))  ? user.mst_company_name : index}}</div>
                                                    <div class="email">【{{user.email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === (index + 1)"
                                                          class="final"> 最終</span>
                                                    <a class="currentUser-flg"
                                                      v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                      href="#"> <i class="far fa-flag"></i></a>
                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                      href.prevent @touchend="onRemoveCircularUser(user.id)"
                                                      class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </vs-col>
                                                <!--PAC_5-1698 S-->
                                                <vs-col
                                                    vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="user.plan_users"
                                                    :class="['item child-order ', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                    <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                    <div v-for="(user, index) in user.plan_users" :key="createKeyLoop(index, user.email)">
                                                        <div class="name">{{user.name || '社員'}}</div> 
                                                        <div class="email">【{{user.email}}】</div>
                                                    </div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === (index + 1)"
                                                          class="final"> 最終</span>
                                                    <div v-for="(user, index) in user.plan_users" :key="createKeyLoop(index, user.id)">
                                                        <a class="currentUser-flg"
                                                          v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                          href="#"><i class="far fa-flag"></i></a>
                                                        <!--                                                    <a v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1 && !isTemplateCircular"-->
                                                        <!--                                                       href.prevent v-on:click="onRemoveCircularUser(user.id)"-->
                                                        <!--                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></a>-->
                                                    </div>
                                                </vs-col>
                                                <!--PAC_5-1698 E-->
                                            </vs-row>
                                          </template>
                                      </div>
                                  </vs-col>
                              </vs-row>
                            </template>
                          </div>

                      </div>
                    </vx-card>
                    <form v-if="enable_any_address!=2 && !checkShowAddress && allowAddDestination && (!$store.state.home.usingPublicHash  || ($store.state.home.usingPublicHash && userHashInfo && !userHashInfo.is_external)) && !isTemplateCircular">
                        <vs-row class="mt-8">
                            <div class="vx-col w-1/2 pr-2">
                                <vue-suggestion :items="userSuggestions"
                                                v-model="userSuggestModel"
                                                placeholder="回覧先の名前"
                                                :setLabel="setUsernameSuggestLabel"
                                                :itemTemplate="usernameTemplate"
                                                :minLen="1"
                                                :disabled="suggestDisabled"
                                                @onInputChange="getUsersSuggestionList"
                                                @onItemSelected="onSuggestSelect"
                                                @focus="getFocusUsersSuggestionList">
                                </vue-suggestion>
                                <!--<vue-simple-suggest placeholder="回覧先の名前" :disabled="suggestDisabled" v-model="usernameSelect" display-attribute="name" value-attribute="email" :list="getUsersSuggestionList" @select="onSuggestSelect"> </vue-simple-suggest>-->
                            </div>
                            <div class="vx-col w-1/2 pl-2">
                                <vue-suggestion :items="emailSuggestions"
                                                v-model="emailSuggestModel"
                                                placeholder="回覧先のメールアドレス"
                                                :setLabel="setEmailSuggestLabel"
                                                :itemTemplate="emailTemplate"
                                                :minLen="1"
                                                @onInputChange="getEmailsSuggestionList"
                                                @onItemSelected="onSuggestSelect"
                                                @focus="getFocusEmailsSuggestionList">
                                </vue-suggestion>
                                <span class="text-danger text-sm" v-show="emailSuggestValidateMsg">{{ emailSuggestValidateMsg }}</span>
                                <!--<vue-simple-suggest  display-attribute="email" value-attribute="email" :list="getUsersSuggestionList" @select="onSuggestSelect">
                                    <vs-input placeholder="回覧先のメールアドレス" v-validate="'required|email'" required name="email" class="inputx w-full"  @input="onEmailSuggestChange" v-model="emailSelect"  />
                                    <span class="text-danger text-sm" v-show="errors.has('email')">{{ errors.first('email') }}</span>
                                </vue-simple-suggest>-->
                            </div>

                        </vs-row>
                        <vs-row class="mt-4">
                            <vs-col vs-type="flex" vs-w="12"  vs-justify="flex-end" vs-align="center">
                                <vs-button @click.prevent="submitMailStepForm" class="square mr-0"  color="primary" type="filled"> 追加</vs-button>
                            </vs-col>
                        </vs-row>
                    </form>
                </div>
                <div :class="'vx-col mb-4 ' + ((circularUserLastSend.parent_send_order != 0 && circularUserLastSend.child_send_order == 1)? 'w-full lg:w-1/2 ': 'w-full lg:pr-4')">
                    <div class="mb-4">
                        <vs-row>
                          <h4>件名</h4>
                        </vs-row>
                        <vs-row class="mt-2">
                          <vs-input class="inputx w-full" placeholder="件名をつけて送信できます。" v-validate="'max:50'" name="subject" v-model="commentTitle" />
                        </vs-row>

                        <vs-row class="mt-4">
                            <h4>メッセージ</h4>
                        </vs-row>
                        <vs-row class="mt-2" style="background-color: white;">
                            <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" class="mb-0" v-model="emailContent" />
                        </vs-row>
                        <!--<vs-checkbox :value="addToCommentsFlg" v-on:click="addToCommentsFlg = !addToCommentsFlg">社内のみ閲覧可</vs-checkbox>-->
                    </div>
                </div>
                <div class="vx-col w-full mb-0 action">
                    <vs-row>
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="3" vs-xs="12" :vs-sm="isMobile?12:6" >
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="primary" type="filled" v-on:click="onBackClick"><div><img :src="require('@assets/images/mobile/back_white.svg')"></div>戻る</vs-button>
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="primary" type="filled" v-on:click="onSendClick();"><div><img :src="require('@assets/images/mobile/confirm_white.svg')"></div>承認</vs-button>
                        </vs-col>
                    </vs-row>
                </div>
            </div>
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
                <vs-row v-if="!$store.state.home.usingPublicHash" class="mp-3 pt-6" vs-type="flex" style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px">
                    <vs-checkbox :value="operationNotice" v-on:click="operationNotice = !operationNotice">次回から表示しない。</vs-checkbox>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onUpdateOperationNoticeClick" > 閉じる</vs-button>
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
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSyncOperationClick" > 閉じる</vs-button>
                </vs-row>
            </modal>
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
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSaveFileFailNoticeClick" > 閉じる</vs-button>
                </vs-row>
            </modal>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    import { CIRCULAR } from '../../enums/circular';
    import { CIRCULAR_USER } from '../../enums/circular_user';
    import LiquorTree from 'liquor-tree';
    import draggable from 'vuedraggable';

    import { Validator } from 'vee-validate';
    import config from "../../app.config";
    import Axios from "axios";

    import VueSuggestion from 'vue-suggestion'

    import usernameTemplate from './username-suggest-template.vue';
    import emailTemplate from './email-suggest-template.vue';
    import ContactTree from '../../components/contacts/ContactTree';
    import Utils from '../../utils/utils';
    import moment from 'moment'

    const dict = {
        custom: {
            content: {
                max: "500文字以上は入力できません。"
            },
            subject: {
                emoji:'絵文字は入力できません'
          },
        }
    };
    Validator.localize('ja', dict)
    const reg=/[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF][\u200D|\uFE0F]|[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF]|[0-9|*|#]\uFE0F\u20E3|[0-9|#]\u20E3|[\u203C-\u3299]\uFE0F\u200D|[\u203C-\u3299]\uFE0F|[\u2122-\u2B55]|\u303D|[\\A9|\\AE]\u3030|\\uA9|\\uAE|\u3030/ig
    Validator.extend('emoji', {
      messages: {
        ja:() => '絵文字は入力できません',
      },
      validate: value => {
        reg.lastIndex = 0
        return  !reg.test(value)
      }
    });
    export default {
        components: {
            //[LiquorTree.name]: LiquorTree,
            draggable,
            VueSuggestion,
            ContactTree
        },
        directives: {

        },
        data() {
            return {
                usernameTemplate: usernameTemplate,
                emailTemplate: emailTemplate,
                CIRCULAR: CIRCULAR,
                CIRCULAR_USER: CIRCULAR_USER,
                info: {},
                emailTemplateOptions: [],
                //  arrBtnTitleAccount:[['スタンダードAWS','プロフェッショナルAWS'],['スタンダードK5','プロフェッショナルK5']], // [env_flg, edition_flg]
              //  arrBtnTitleAccount:[['Corporate1','Business Pro1'],['Corporate2','Business Pro2']], // [env_flg, edition_flg]
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
                treeFilterOptions:{
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
                arrFavorite:[],
                checkShowButtonApply:false,
                dragOptions : {
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
                // addToCommentsFlg: true,
                isNotReturnCircular: false,
                selectUsersDisplay:[],
                searchAreaFlg:false,
                confirmEdit: false,
                outsideAccessCode: null,
                operationNotice: false,
                checkShowTitle: false, //件名表示フラグ
                showPopupEditContacts: false,
                // PAC_5-1973 Start
                showPopupErrosTextLength: false,
                // PAC_5-1973 End
                // PAC_5-2185 Start
                showPopupErrosContentLength: false,
                // PAC_5-2185 End
                editContact:{},
                confirmDelete:false,
                confirmDuplicateEmail: false,
                listCheckEmailContact:[],
                clickState: false, //二重チェック用
                showTree: true,
                changeTimes: 0,
                selectedComment: '',
                tableshow:{},
                searchFavorite:'',
                addFavoriteFlg:false,
                addFavoriteNameVal:'',
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
                checkUserInputTimeout: null,
                checkEmailInputTimeout: null,
                userSelectedFlg: false,
                emailSelectedFlg: false,
                userViewSelectedFlg: false,
                emailViewSelectedFlg: false,
                // 2630 回覧メールから文書を承認するとき承認ボタンを2回押すとエラーがでる START
                sendClickCountNum: 0,
                // 2630 回覧メールから文書を承認するとき承認ボタンを2回押すとエラーがでる END
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 End
                specialCircularReceiveFlg: false,//回覧ユーザーが特設サイトの受取側ですか
                circularUserLastSendIdIsSpecial: false,//現在未操作のユーザは特設サイトの受取側ですか
                specialCircularFlg:false,//特設サイト回覧
                specialButtonDisableFlg: false,//特設サイト申請画面、ボタン非アクティブ
                groupName: '',//特設サイト受取側組織名
                isMobile: false,
                templateNextUserCompletedFlg: false,
                /*PAC_5-2616 S*/
                enable_any_address:0,
                /*PAC_5-2616 E*/
                require_approve_flag: 0,//PAC_5-2705
                showEditFavorite: false,
                editFavoriteItem: [],
                editFavoriteItemIndex: 0,
                draggingFavoriteUser: null,
                showPopupErrosEmojiLength:false,
                finishedDate:'',
                popupSelectFavoriteAccountActive: false,//popupのflag
                favoriteEmailSelect: '',//選択するメール
                favoriteEmailSelects: [],//選択する複数のメール
                favoriteCheckEmailExisting: [],//選択する現在のデータ
                favoriteAllEmailSelects: [],//選択するお気に入りのすべてのメール
                arrBtnColorAccount:['primary','primary'],
            }
        },
        beforeRouteEnter(to, from, next) {
            next((vm) => {
                if (from.path != '/'){
                    vm.previousRoute = from;
                }
            });
        },
        computed: {
            ...mapState({
                files: state => state.home.files,
                fileSelected: state => state.home.fileSelected,
                circular: state => state.home.circular,
                checkSentCircular: state => state.home.checkSentCircular,
                selectUserView: state => state.application.selectUserView,
                checkOperationNotice: state => state.application.checkOperationNotice,
                //departmentUsers: state => state.application.departmentUsers,
                getUserView: state => state.application.getUserView,
                // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
                circularChangeListUserView: state => state.home.circularChangeListUserView,
                template_flg: state => state.home.template_flg,
            }),
            circularUserLastSend() {
                if(!this.circular || !this.circular.users) {
                    return null;
                }
                // 合議の場合
                let circular_user = null;
                if(this.loginUser && this.isTemplateCircular) {
                    // 終わった場合
                    if(this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_STATUS || this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS){
                        circular_user = null;
                    }else{
                        let circular_users = [];
                        // 差戻のcircular_user
                        let circular_user_send_back = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                        // 差戻の場合
                        if(circular_user_send_back){
                            // 差戻 同級のメール 除外する
                            circular_users = this.circular.users.slice().filter(item => (item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS) && item.child_send_order !== circular_user_send_back.child_send_order);
                        }else{
                            circular_users =  this.circular.users.slice().filter(item => (item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS));
                        }

                        // get 処理中 child_send_order
                        let distinct_child_orders = [];
                        for(let i = 0;i < circular_users.length;i ++){
                            if(!distinct_child_orders.includes(circular_users[i].child_send_order)){
                                // すべてのユーザ
                                let all_arr = this.circular.users.slice().filter(item => item.child_send_order === circular_users[i].child_send_order);
                                // 承認のユーザ
                                let approved_arr = this.circular.users.slice().filter(item => (item.child_send_order === circular_users[i].child_send_order && (item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS)));
                                if(all_arr.length > 1){
                                    // all承認
                                    if(approved_arr.length == all_arr.length){
                                        continue;
                                    }else{
                                        // 待つ
                                        if(all_arr[0].wait == 1){
                                            // 一つのノードが複数存在するからです。 loginUserのemail 選択  && item.email === this.loginUser.email
                                            let cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.loginUser.email);
                                            if(cir !== undefined){
                                                circular_user = cir;
                                            }else{
                                                circular_user = circular_users[i];
                                            }
                                            break;
                                        }else if(all_arr[0].wait == 0){
                                            if(approved_arr.length >= all_arr[0].score){
                                                continue;
                                            }else{
                                                // 一つのノードが複数存在するからです。 loginUserのemail 選択  && item.email === this.loginUser.email
                                                let cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.loginUser.email);
                                                if(cir !== undefined){
                                                    circular_user = cir;
                                                }else{
                                                    circular_user = circular_users[i];
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }else{
                                    circular_user = circular_users[i];
                                    break;
                                }
                                distinct_child_orders[distinct_child_orders.length + 1] = circular_users[i].child_send_order;
                            }
                        }
                    }
                }else{
                    circular_user =  this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS);
                }
                if(!circular_user) return  null;
                this.$store.commit('home/updateCurrentParentSendOrder', circular_user?circular_user.parent_send_order:0);
                return circular_user;
            },
            allowAddDestination: {
                get() {
                    if(!this.circular) return false;
                    return (this.circular.address_change_flg || (this.circularUserLastSend.parent_send_order > 0));
                }
            },
            selectUsers: {
                get() {
                    if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                    if(!this.circularUserLastSend) return [];
                    return this.$store.state.home.circular.users.filter(item => {
                        if (Object.prototype.hasOwnProperty.call(item, "plan_users")){
                            delete item.plan_users
                        }
                        return (item.parent_send_order === 0 && item.child_send_order === 0) || (this.circularUserLastSend.parent_send_order === item.parent_send_order) || (item.parent_send_order !== 0 && item.child_send_order === 1);
                    });

                },
                set(value) {
                    this.updateCircularUsers(value)
                }
            },
            selectUserView:{
                get() {
                    return this.$store.state.application.selectUserView
                },
            },
            getUserView:{
                get() {
                    return this.$store.state.application.getUserView
                },
            },
            updategetUserView:{
                get() {
                    return this.$store.state.application.getUserView
                },
            },
            ownerUser: {
                get() {
                    if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                    return this.$store.state.home.circular.users.filter(item => {
                        return item.parent_send_order === 0 && item.child_send_order === 0;
                    });
                },
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
                getDepartmentUsers: "application/getDepartmentUsers",
                addChildCircularUser: "application/addChildCircularUser",
                updateCircularUsers: "home/updateCircularUsers",
                updateFormatCircularUsers: "home/updateFormatCircularUsers",
                removeCircularUser: "application/removeCircularUser",
                sendNotifyContinue: "application/sendNotifyContinue",
                saveFileAndSignature: "home/saveFileAndSignature",
                getListContact: "contacts/getListContact",
                addLogOperation: "logOperation/addLog",
                getInfoByHash: "user/getInfoByHash",
                getListFavorite     : "favorite/getList",
                removeFavorite      : "favorite/remove",
                checkCircularUserNextSend: "home/checkCircularUserNextSend",
                getMyInfo: "user/getMyInfo",
                updateOperationNotice: "application/updateOperationNotice",
                getContact: "contacts/getContact",
                updateContact: "contacts/updateContact",
                deleteContact: "contacts/deleteContact",
                autoStorageBox: "application/autoStorageBox",
                getUserView: "application/getUserView",
                updategetUserView: "application/updategetUserView",
                templateSaveFileAndSignature: "home/templateSaveFileAndSignature",
                getTemplateInputComplete: "template/getTemplateInputComplete",
                editTemplate: "home/editTemplate",
                getTemplateEditStamp: "template/getTemplateEditStamp",
                getTemplateEditText: "template/getTemplateEditText",
                getTemplateNextUserCompletedFlg: "template/getTemplateNextUserCompletedFlg",
                tempEditStampInfoFix: "template/tempEditStampInfoFix",
                releaseTemplateEditFlg: "template/releaseTemplateEditFlg",
                templateEditS3delete: "template/templateEditS3delete",
                updateFavorite: "favorite/updateFavorite",
                sortFavoriteItem: "favorite/sortFavoriteItem",
                deleteFavoriteItem: "favorite/deleteFavoriteItem",
            }),
            setUsernameSuggestLabel (item) {
                return item.name;
            },
            setEmailSuggestLabel (item) {
                return item.email;
            },
            onChangeEmailTemplate(value) {
                 this.selectedComment = value;
                if (this.emailContent == null){
                    this.emailContent = '';
                }
                this.emailContent = this.emailContent.concat(value);
                this.selectedComment = value +' '
            },
            onTreeAddToStepClick: function (userChecked) {
                /*if(!this.$refs.tree) return;
                const userChecked = this.$refs.tree.checked().filter(tree => !tree.data.isGroup).map((tree,index) => {
                    return {
                        child_send_order: this.selectUsers.length + index,
                        email: tree.data.email,
                        name: tree.data.family_name + ' ' + tree.data.given_name,
                        is_maker: false
                    }
                });*/
                $(".circular-destination-page-dialog .vs-popup--close").click();
                const iterable = () => {
                    const item = userChecked.shift();
                    if(!item) return;
                    this.pushToSteps(item, ()=>{
                        iterable();
                    });
                }
                iterable();
            },
            // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
            getUsersSuggestionList(inputValue) {
                this.usernameSelect = inputValue;
                if (inputValue) {
                    const $this = this;
                    clearTimeout(this.checkUserInputTimeout);
                    this.checkUserInputTimeout = setTimeout(function () {
                        Axios.get(`${config.BASE_API_URL}${$this.$store.state.home.usingPublicHash ? '/public' : ''}/users?filter=${inputValue}`, {data: {nowait: true, usingHash: $this.$store.state.home.usingPublicHash}})
                    .then(response => {
                            if ($this.usernameSelect === inputValue) {
                        const users =  response.data ? response.data.data.map(item => {
                            item.name = item.family_name?item.family_name + ' ' + item.given_name:item.name;
                            return item
                        }) : [];
                                $this.userSuggestions = users;
                            }
                    })
                    .catch(error => {
                            if ($this.usernameSelect === inputValue) $this.userSuggestions = [];
                    });
                    }, 300);
                } else {
                    this.userSuggestions = [];
                }
            },
            getEmailsSuggestionList(inputValue) {
                this.emailSuggestValidateMsg ='';
                this.emailSelect = inputValue;
                if(this.userSuggestSelect && this.userSuggestSelect.email !== inputValue) {
                    this.suggestDisabled = false;
                }
                if (inputValue) {
                    const $this = this;
                    clearTimeout(this.checkEmailInputTimeout);
                    this.checkEmailInputTimeout = setTimeout(function () {
                        Axios.get(`${config.BASE_API_URL}${$this.$store.state.home.usingPublicHash ? '/public' : ''}/users?filter=${inputValue}`, {data: {nowait: true, usingHash: $this.$store.state.home.usingPublicHash}})
                    .then(response => {
                            if ($this.emailSelect === inputValue) {
                        const users =  response.data ? response.data.data.map(item => {
                            item.name = item.family_name?item.family_name + ' ' + item.given_name:item.name;
                            return item
                        }) : [];
                                $this.emailSuggestions = [...users];
                            }
                    })
                    .catch(error => {
                            if ($this.emailSelect === inputValue) $this.emailSuggestions = [];
                    });
                    }, 300);
                } else {
                    this.emailSuggestions = [];
                }
            },
            getFocusUsersSuggestionList() {
                if (this.usernameSelect && this.userSelectedFlg){
                    var inputValue = this.usernameSelect;
                    const $this = this;
                    this.userSelectedFlg = false;
                    Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                    .then(response => {
                        if ($this.usernameSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                            $this.userSuggestions = users;
                        }
                    })
                    .catch(error => {
                        if ($this.usernameSelect === inputValue) $this.userSuggestions = [];
                    });
                }
            },
            getFocusEmailsSuggestionList() {
                if (this.emailSelect && this.emailSelectedFlg) {
                    var inputValue = this.emailSelect;
                    const $this = this;
                    this.emailSelectedFlg = false;
                    Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                    .then(response => {
                        if ($this.emailSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                            $this.emailSuggestions = users;
                        }
                    })
                    .catch(error => {
                        if ($this.emailSelect === inputValue) $this.emailSuggestions = [];
                    });
                }
            },
            // PAC_5-2189 End
            onSuggestSelect: function (user) {
                this.emailSuggestValidateMsg ='';
                this.userSuggestModel = user;
                this.emailSuggestModel = user;
                this.userSuggestSelect = user;
                this.emailSelect = user.email;
                this.usernameSelect = user.name;
                this.suggestDisabled = true;
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
                this.userSuggestions = [];
                this.emailSuggestions = [];
                this.userSelectedFlg = true;
                this.emailSelectedFlg = true;
                // PAC_5-2189 End
            },
            onEmailSuggestChange: function (value) {
                if(this.userSuggestSelect && this.userSuggestSelect.email !== value) {
                    this.suggestDisabled = false;
                }
            },
            submitMailStepForm:  async function () {
              this.emailSuggestValidateMsg = '';
              if (!this.emailSelect) {
                this.emailSuggestValidateMsg = '必須項目です';
                return;
              }

              const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
              const mailPattern1 = /^[a-zA-Z0-9.!$%&@'*+/\\=?^_`{|}\[\]()"><:;~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
              if (this.emailSelect.match(mailPattern) === null && this.emailSelect.match(mailPattern1) === null) {
                this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
                return;
              }
              var result = await Axios.post(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public' : ''}/user/checkemail`, {email: this.emailSelect,usingHash: this.$store.state.home.usingPublicHash})
                  .then(response => {
                    let result_data = []
                    if (response.data){
                      result_data = response.data.data.filter(function (value) {
                        //企業IDが「１」（edition_flgが「０」）の時はゲストユーザー扱いにして、 それ以外は通常通りedition_flgが「０」のユーザーに回覧にしてほしいです
                        if (value.edition_flg !== 0 || value.company_id !== 1) return value;
                      })
                    }
                    return result_data;
                  })
                  .catch(error => {
                    return [];
                  });
              if (!result.length) {
                if (this.emailSelect.match(mailPattern) === null) {
                  this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
                  return;
                }
              }
              if (result.length == 1 && result[0].user_auth == 5 && result[0].company_id != this.selectUsers[0].mst_company_id) {
                this.$vs.dialog({
                  type: 'alert',
                  color: 'danger',
                  title: `確認`,
                  acceptText: '閉じる',
                  text: `受信専用利用者は社内文書しか受信できないです。`,
                  accept: () => {
                    return '';
                  }
                });
              }
              // 名前未入力の場合
              if (!this.usernameSelect || this.usernameSelect == '') {
                for (let i = 0; i < this.userSuggestions.length; i++) {
                  if (this.emailSelect == this.userSuggestions[i].email) {
                    this.usernameSelect = this.userSuggestions[i].name;
                    break;
                  }
                }
              }

              const user = {
                email: this.emailSelect,
                name: this.usernameSelect,
                is_maker: false
              };


              this.pushToSteps(user, (result) => {
                if (result) {
                  this.usernameSelect = '';
                  this.emailSelect = '';
                  this.userSuggestModel = {};
                  this.emailSuggestModel = {};
                  this.suggestDisabled = false;
                }
              });

            },
            pushToSteps: async function(user, callback) {
                const checkEmail = this.selectUsers.findIndex(item => item.email === user.email);
                if(this.getUserView.find((v) => v.email === user.email)){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `選択された利用者は既に閲覧ユーザーに存在したので、宛先に追加できません。`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                    return;
                }
                const data = {
                    parent_id: this.circularUserLastSend ? this.circularUserLastSend.id : null,
                    parent_company_id: this.circularUserLastSend ? this.circularUserLastSend.mst_company_id : null,
                    name: user.name ? user.name : (user.family_name ? user.family_name + ' ' + user.given_name : '社外'),
                    email: user.email,
                }
                let localUser ;
                let localEmail = this.circularUserLastSend.email ;
                this.selectUsers.forEach(function (value) {
                    if (value.email === localEmail)
                        localUser = value;
                });
                if(checkEmail >= 0) {
                    this.$vs.dialog({
                        type:'confirm',
                        color: 'primary',
                        title: `確認`,
                        acceptText: 'はい',
                        cancelText: 'いいえ',
                        text: `すでに回覧先に指定されているメールアドレスですが、よろしいですか？ ${user.email}`,
                        accept: async ()=> {
                            const ret = await this.addChildCircularUser(data);
                            if (ret) this.formatSelectUsers = this.loadFormatSelectUsers();
                            this.childUsersChange = !this.childUsersChange;
                            if(this.selectUserView.find((v) => v.email === ret.email && ret.edition_flg == localUser.edition_flg && ret.env_flg == localUser.env_flg && ret.server_flg == localUser.server_flg )){
                                this.deleteUserView(user.email);
                            }
                            //1723
                            if(this.getUserView.find((v) => v.email === this.emailSelect)){
                            const tmpSelectUsers = this.getUserView.slice();
                            tmpSelectUsers.splice(this.getUserView.findIndex((v) => v.email === this.emailSelect),1)
                             console.log(this.getUserView.findIndex((v) => v.email === this.emailSelect));
                            this.$store.commit('application/updategetUserView', tmpSelectUsers);
                            await Axios.post(`${config.BASE_API_URL}/deleteUserView?email=${encodeURIComponent(this.emailSelect)}&circular_id=${this.circular.id}`);
                            }

                            callback(ret);
                        }
                    })
                    return Promise.resolve(true);
                }else {
                    const ret = await this.addChildCircularUser(data);
                   //1723
                    if(this.getUserView.find((v) => v.email === this.emailSelect)){
                    const tmpSelectUsers = this.getUserView.slice();
                    tmpSelectUsers.splice(this.getUserView.findIndex((v) => v.email === this.emailSelect),1)
                     console.log(this.getUserView.findIndex((v) => v.email === this.emailSelect));
                    this.$store.commit('application/updategetUserView', tmpSelectUsers);
                    await Axios.post(`${config.BASE_API_URL}/deleteUserView?email=${encodeURIComponent(this.emailSelect)}&circular_id=${this.circular.id}`);
                            }
                    if (ret) {
                        this.formatSelectUsers = this.loadFormatSelectUsers();
                    }else{
                        this.usernameSelect = '';
                        this.emailSelect = '';
                        this.userSuggestModel = {};
                        this.emailSuggestModel = {};
                        this.suggestDisabled = false;
                    }
                    this.childUsersChange = !this.childUsersChange;
                    if(this.selectUserView.find((v) => v.email === ret.email && ret.edition_flg == localUser.edition_flg && ret.env_flg == localUser.env_flg && ret.server_flg == localUser.server_flg )){
                        this.deleteUserView(user.email);
                    }
                    callback(ret);
                    return Promise.resolve(true);
                }
            },
            onBackClick: async function() {
                if(this.circular && this.selectUserView ){
                    this.$store.commit('home/updateCircularChangeListUserView',{id:this.circular.id,data:this.selectUserView});
                }
                if (this.previousRoute){
                    this.previousRoute.query.back = true;
                    this.$router.push(this.previousRoute);
                }else{
                    this.$router.back()
                }
            },

          onSendClick: async function () {
             if(this.sendClickCountNum++ > 0){
               this.sendClickCountNum = 0
                return ;
             }

            const isTotallyConfidenceFiles = this.files.every(file => file.confidential_flg);
            const isInternalUser = this.selectUsers.every(user => (user.mst_company_id === this.selectUsers[0].mst_company_id && user.env_flg === this.selectUsers[0].env_flg && user.edition_flg === this.selectUsers[0].edition_flg && user.server_flg === this.selectUsers[0].server_flg));
            if (isTotallyConfidenceFiles && !isInternalUser) {
            this.$vs.dialog({
                type: 'alert',
                color: 'danger',
                title: `確認`,
                acceptText: '閉じる',
                text: `別企業のユーザーは設定できません`,
                accept: () => {
                    ()=>{};
                }
            });
            return;
            }
            // 二重チェック
            this.clickState = true;
            this.$modal.hide('operation-notice-modal');
            // PAC_5-1973 Start
            if(this.commentTitle.length > 50){
                this.showPopupErrosTextLength = true;
                this.clickState = false;
                this.sendClickCountNum = 0;
                return false;
            }

              if(this.emailContent.length > 500){
                  this.showPopupErrosContentLength = true;
                  this.clickState = false;
                  this.sendClickCountNum = 0;
                  return false;
              }
             reg.lastIndex = 0
              if(reg.test(this.commentTitle)){
                this.showPopupErrosEmojiLength = true;
                this.clickState = false;
                this.sendClickCountNum = 0;
                return false;
              }
            this.commentTitle = this.commentTitle.replace(/[\v|\t]/g,'');
            // PAC_5-1973 End
            //get index circularUser present
            if(this.$store.state.home.usingPublicHash){
              const userHashInfoPromise = this.getInfoByHash();
              userHashInfoPromise.then((item) => {
                this.finishedDate = item.finishedDate;
              })
            }
            var company = await Axios.get(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public': ''}/setting/getMyCompany`, this.$store.state.home.usingPublicHash ? {data: {usingHash: true, finishedDate: this.finishedDate}}:{})
                .then(response => {
                  return response.data ? response.data.data: [];
                })
                .catch(error => {
                  this.clickState = false;
                  this.sendClickCountNum = 0;
                  return []; });
                  if(company.template_flg == 1) {
                this.$store.commit('home/setTemplateFlg', true );
            }

            if(company.esigned_flg == 1){
              this.$store.commit('home/checkCircularUserNextSend', true);
            }else{
              this.$store.commit('home/checkCircularUserNextSend', false);
            }

            //PAC_1527 テンプレート編集ルート
            if(this.template_flg) {
                this.templateNextUserCompletedFlg = await this.getTemplateNextUserCompletedFlg(this.circular.id);
                this.$store.commit('home/setTemplateNextUserCompletedFlg', this.templateNextUserCompletedFlg);
            }

            let saveFileRes = await this.saveFileAndSignature();

            if(this.template_flg) {
                if(this.templateNextUserCompletedFlg){
                    let template_input_data = await this.getTemplateInputComplete({circular_id: this.circular.id});
                    const result = await this.editTemplate(template_input_data);
                    const edit_stamp = await this.getTemplateEditStamp(this.circular.id);
                    const edit_text = await this.getTemplateEditText(this.circular.id);
                    saveFileRes = await this.templateSaveFileAndSignature([edit_stamp,edit_text]);
                    const stamp_info_fix = await this.tempEditStampInfoFix(this.circular.id);
                    this.releaseTemplateEditFlg(this.circular.id);
                    this.templateEditS3delete(this.circular.id);
                }
            }

            if (saveFileRes !== true) {
              if (saveFileRes.statusCode == 406) {
                // 文書が修正された場合、ほかのユーザーのよって操作されました。再度文書を開きなおしてください。
                await this.$modal.show('sync-operation-modal');
                  this.sendClickCountNum = 0;
                this.clickState = false;
                return;
              } else {
                await this.$modal.show('save-file-fail-notice-modal');
                  this.sendClickCountNum = 0;
                this.clickState = false;
                return;
              }
            }

            const isAddStamp = this.$store.state.home.files.some(file => {
              return file.pages.filter(page => page.stamps && page.stamps.length > 0).length > 0;
            });
            // 最初回覧文書取得
            let circular_document_id = 0;
            this.$store.state.home.files.forEach(function (item) {
              if (circular_document_id == 0 || circular_document_id > item.circular_document_id) {
                circular_document_id = item.circular_document_id;
              }
            });
            let is_circular_completed = false;
            const sendNotifyContinue = async () => {
              const data = {
                userViews: this.selectUserView,
                title: this.commentTitle,
                text: this.emailContent,
                add_stamp: isAddStamp,
                sender_id: this.circularUserLastSend ? this.circularUserLastSend.id : null,
                // addToCommentsFlg: this.addToCommentsFlg,
                operationNotice: this.operationNotice,
                outsideAccessCode: this.outsideAccessCode,
                circular_document_id: circular_document_id,
                isTemplateCircular: this.isTemplateCircular,
              };

              await this.sendNotifyContinue(data).then(ret => {
                is_circular_completed = ret.is_circular_completed;
              });


              if (!this.$store.state.home.usingPublicHash && !this.info.operation_notice_flg) {
                this.$route.meta.isKeep=true
                var namePath = this.$route.name;
                if(namePath == 'expense_destination'){
                  this.$router.push('/expense/received');
                }else
                {
                  this.$router.push('/received');
                }
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
                    this.$route.meta.isKeep=true
                    this.$router.push('/received');
                  }, 2000);
                } else {
                  this.$route.meta.isKeep=true
                  this.$router.push('/received');
                }
              }
            }

            await sendNotifyContinue();
            if (is_circular_completed) {
              let arr_document = [];
              this.$store.state.home.files.forEach(function (item) {
                arr_document.push({
                  "circular_document_id": item.circular_document_id,
                  "circular_document_name": item.name,
                });
              });
              this.autoStorageBox({
                sender_email: this.ownerUser[0].email,
                company_id: this.ownerUser[0].mst_company_id,
                circular_id: this.$store.state.home.circular.id,
                circular_documents: arr_document,
                subject: this.$store.state.home.circular.users[0].title,
              });
            }
            if ((this.checkOperationNotice && this.info.operation_notice_flg) || (this.$store.state.home.usingPublicHash)) {
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
                  this.onUpdateOperationNoticeClick();
                }, 2000);
              } else {
                await this.$modal.show('operation-notice-modal');
              }

            }

            this.sendClickCountNum = 0;
            this.clickState = false;
          },
            onUpdateOperationNoticeClick: async function() {
                if(this.$store.state.home.usingPublicHash){
                    if (window.opener){
                        window.close();
                    }else{
                        if (this.userHashInfo){
                            if (parseInt(this.userHashInfo.current_env_flg)){
                                window.location.href = config.K5_API_URL;
                            }else{
                                if(parseInt(this.userHashInfo.current_edition_flg)){
                                    window.location.href = config.AWS_API_URL;
                                }else{
                                    window.location.href = config.OLD_AWS_API_URL;
                                }
                            }
                        }else{
                            window.location.href = config.LOCAL_API_URL;
                        }
                    }
                }else{
                    this.updateOperationNotice({operationNotice: this.operationNotice}).then(ret => {
                        if(ret) {
                            this.$route.meta.isKeep=true
                            var namePath = this.$route.name;
                            if(namePath == 'expense_destination'){
                                this.$router.push('/expense/received');
                            }else
                            {
                                this.$router.push('/received');
                            }
                        }
                    });
                }
            },
            onSaveFileFailNoticeClick: async function() {
                if(this.$store.state.home.usingPublicHash){
                    if (window.opener){
                        window.close();
                    }else{
                        if (this.userHashInfo){
                            if (parseInt(this.userHashInfo.current_env_flg)){
                                window.location.href = config.K5_API_URL;
                            }else{
                                if(parseInt(this.userHashInfo.current_edition_flg)){
                                    window.location.href = config.AWS_API_URL;
                                }else{
                                    window.location.href = config.OLD_AWS_API_URL;
                                }
                            }
                        }else{
                            window.location.href = config.LOCAL_API_URL;
                        }
                    }
                }else{
                    this.$route.meta.isKeep=true
                    var namePath = this.$route.name;
                    if(namePath == 'expense_destination'){
                        this.$router.push('/expense/received');
                    }else
                    {
                       this.$router.push('/received');
                    }
                }
            },

            async getEmailTrees(){
                const mapfunc = (item) => {
                    let newItem = {};
                    if(!item) return null;
                    if(!Object.prototype.hasOwnProperty.call(item, "parent_id")) {
                        newItem = {text: item.family_name + ' ' + item.given_name, data: item, isDepartment: false};
                        return newItem;
                    }else {
                        let children = [];
                        // PAC_5-2155  アドレス帳の表示方法の変更
                        if(Object.prototype.hasOwnProperty.call(item, "users")) {
                            if (item.users)
                                children.push(...item.users.map(mapfunc));
                        }
                        if(Object.prototype.hasOwnProperty.call(item, "children")) {
                            if(item.children)
                                children.push(...item.children.map(mapfunc));
                        }
                        newItem.text =  item.name;
                        newItem.children =  children;
                        newItem.data =  {isGroup: true};
                        return newItem;
                    }

                };
                let departments = [];
                if(this.$store.state.application.departmentUsers) departments = this.$store.state.application.departmentUsers.map(mapfunc);
                let listContact       = await this.getListContact({filter: '', type: 0});
                let listContactCommon = await this.getListContact({filter: '', type: 1});
                if(!listContact) return false;
                if(!listContactCommon) return false;

                const processContact = (contacts) => {
                    let groups = {};
                    contacts.forEach((contact, stt) => {
                        if(!contact.group_name) contact.group_name = 'グループなし';
                        if(!groups[contact.group_name]) groups[contact.group_name] = [];
                        contact.family_name = contact.name;
                        contact.given_name = "";
                        groups[contact.group_name].push({
                            text: contact.name, data: contact
                        });
                    });
                    contacts = [];
                    for(let group_name in groups){
                        contacts.push({text: group_name, children: groups[group_name], data: {isGroup: true}});
                    }
                    return contacts;
                };
                listContactCommon = processContact(listContactCommon);
                let arrAddressTree = [
                    {text:'共通', children: listContactCommon, data: {isGroup: true}},
                    {text:'部署', children: departments, data: {isGroup: true}},
                ];

                var limit = getLS("limit");
                limit = JSON.parse(limit);
                if(limit && limit.enable_any_address == 1){
                    this.checkShowAddress = true;
                }else{
                    this.checkShowAddress = false;
                }

                if(!this.checkShowAddress){
                    listContact = processContact(listContact);
                }else{
                    listContact = '';
                }

                if(listContact){
                    arrAddressTree.unshift({text:'個人', children: listContact, data: {isGroup: true}});
                }
                return arrAddressTree;
            },
            async onDragItemChange(e) {
                $('.final').show();
                $('.group.parent-block').removeClass('no-before')
                $('.first').removeClass('no-before')

                const newSelectUsers =  this.selectUsersDisplay.reduce((acc, val) => acc.concat(val), []);
                let hasPlanNewSelectUsers=[]
                newSelectUsers.forEach(user=>{
                    hasPlanNewSelectUsers.push(user)
                    if (user.plan_users){
                        let plan_users=user.plan_users.filter(_=>_.id!=user.id)
                        hasPlanNewSelectUsers.push(...plan_users)
                    }
                })
                this.updateCircularUsers(hasPlanNewSelectUsers);
                return true;
            },
            onDragEnd(e) {
                $('.final').show();
                $('.group.parent-block').removeClass('no-before')
                $('.first').removeClass('no-before')
                $('.last').removeClass('no-before')
                $('.group.applicant.return-flg').removeClass('no-before')
                $('.first.return-flg').removeClass('no-before')
            },
            onItemMoving(e) {
                $('.final').hide();
                $('.group.parent-block').addClass('no-before')
                $('.first').addClass('no-before')
                $('.last').addClass('no-before')
                $('.group.applicant.return-flg').addClass('no-before')
                $('.first.return-flg').addClass('no-before')

                // check circular user before drop
                if(!this.circular) return false;
                if(!this.allowAddDestination) return false;
                if(!e) return false;
                if(!e.draggedContext.element) return  false;
                if(e.draggedContext.element.id === this.circularUserLastSend.id) return false;
                if(e.draggedContext.element.child_send_order <= this.circularUserLastSend.child_send_order) return false;

                let draggableCompany = this.selectUsersDisplay.filter((item, index) => index === this.circularUserLastSend.parent_send_order);
                if (!draggableCompany) return false;

                let dropUser = draggableCompany[0][e.draggedContext.futureIndex];
                if(!dropUser) return false;
                if(this.circularUserLastSend.parent_send_order === dropUser.parent_send_order && dropUser.child_send_order <= this.circularUserLastSend.child_send_order) return false;

                return true;
            },
            async showModalEditContacts(nodeId){
                // this.addLogOperation({ action: 'r11-display-contacts', result: 0});
                // this.$store.commit('SET_ACTIVATE_STATE', 'showModalContacts');
                this.showPopupEditContacts = true;
                $(".circular-destination-page-dialog .vs-popup--close").click();
                this.$validator.reset();
                this.editContact = await this.getContact(nodeId);
            },
            loadFormatSelectUsers: function() {
                if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                if(!this.circularUserLastSend) return [];
                const circularUsers = this.$store.state.home.circular.users.slice();
                const formatCircularUsers = [];
                let formatCircularUser = {};
                formatCircularUser.user = circularUsers.length ? circularUsers[0] : null;
                formatCircularUser.children = [];
                formatCircularUsers.push(...circularUsers.filter(item => {return this.circularUserLastSend.parent_send_order === 0 ? item.parent_send_order === 0 : item.child_send_order === 0}).map(item => {
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
            },
            async onRemoveCircularUser(id) {
                const ret = await this.removeCircularUser(id);
                if (ret) {
                    this.formatSelectUsers = this.loadFormatSelectUsers();
                }
                this.childUsersChange = !this.childUsersChange;
            },
            async onCircularUsersEndDrag(e) {
                if(e.oldIndex !== e.newIndex) {
                    const ret = await this.updateFormatCircularUsers(this.formatSelectUsers);
                    this.formatSelectUsers = this.loadFormatSelectUsers();
                }
            },
            async onChildCircularUsersEndDrag() {
                if(e.oldIndex !== e.newIndex) {
                    const ret = await this.updateFormatCircularUsers(this.formatSelectUsers);
                    this.formatSelectUsers = this.loadFormatSelectUsers();
                }
            },
            async onRemoveFavorite(favorite_no){
                await this.removeFavorite(favorite_no);
                this.arrFavorite = await this.onSearchFavorite();
            },
            async onApplyFavorite(itemFavorite){
                let arrApply = itemFavorite.map((item) => {
                    return {
                        child_send_order: this.selectUsers.length,
                        email: item.email,
                        name: item.name,
                        is_maker: false,
                        edition_flg: item.email_edition_flg,
                        env_flg: item.email_env_flg,
                        server_flg: item.email_server_flg,
                        company_id: item.email_company_id
                    };
                });

                $(".circular-destination-page-dialog .vs-popup--close").click();
                this.confirmEdit = false;
                let is_plan=itemFavorite.some(v=>{
                    return v.plan_users&&v.plan_users.length>0
                })
                if(is_plan){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `回覧先がある場合、お気に入りから合議先を追加できません`,
                    });
                    return false
                }
              //お気に入りの場合
              let data = {favorite: itemFavorite, usingHash: this.$store.state.home.usingPublicHash};
              let resultStatus = await Axios.post(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public' : ''}/user/checkFavoriteUserStatus`, data)
                  .then(response => {
                    if (response.data.status == false) {
                      this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: '削除または無効の利用者が存在しています。お気に入りを再設定してください。',
                      });
                    }
                    return Promise.resolve(response.data);
                  })
                  .catch(error => {
                    error = error.response;
                    const message = (error && error.data && error.data.message) || error.statusText;
                    return Promise.reject(message);
                  });
              if(resultStatus.status == false){
                return ;
              }
                if (arrApply.length){
                    var selectedEmails = [];
                    this.mapUserEmail = {};
                    this.mapEnvEmail = {};
                    let newFavorite = false;
                    for(var i =0; i <arrApply.length; i++){
                        if (arrApply[i].edition_flg !== null && arrApply[i].env_flg !== null && arrApply[i].server_flg !== null) {
                            newFavorite = true;
                            break;
                        }
                        selectedEmails.push(arrApply[i].email);
                        this.mapUserEmail[arrApply[i].email] = arrApply[i];
                    }
                    if (!newFavorite) {
                        var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: selectedEmails})
                            .then(response => {
                              let result_data = []
                              if (response.data){
                                result_data = response.data.data.filter(function (value) {
                                  if (value.edition_flg !== 0 || value.company_id !== 1) return value;
                                })
                              }
                              return result_data;
                            })
                            .catch(error => {
                                return [];
                            });

                        this.confirmEmails = [];
                        this.mapConfirmEmails = [];
                        for (var i = 0; i < result.length; i++) {
                            this.mapEnvEmail[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_id;
                            if (this.mapUserEmail[result[i].email] && this.mapUserEmail[result[i].email].company_id) {
                                if (this.mapConfirmEmails[result[i].email] == null) {
                                    this.mapConfirmEmails[result[i].email] = [];
                                    this.mapConfirmEmails[result[i].email].push({
                                        env_flg: this.mapUserEmail[result[i].email].env_flg,
                                        edition_flg: this.mapUserEmail[result[i].email].edition_flg,
                                        server_flg: this.mapUserEmail[result[i].email].server_flg,
                                        company_id: this.mapUserEmail[result[i].email].company_id
                                    });
                                }
                                this.mapConfirmEmails[result[i].email].push({
                                    env_flg: result[i].env_flg,
                                    edition_flg: result[i].edition_flg,
                                    server_flg: result[i].server_flg,
                                    company_id: result[i].company_id
                                });
                                this.confirmEmails.push(result[i].email);
                            } else {
                                this.mapUserEmail[result[i].email].edition_flg = result[i].edition_flg;
                                this.mapUserEmail[result[i].email].env_flg = result[i].env_flg;
                                this.mapUserEmail[result[i].email].server_flg = result[i].server_flg;
                                this.mapUserEmail[result[i].email].company_id = result[i].company_id;
                            }
                        }
                        if (this.confirmEmails.length > 0) {
                            this.currentEmailConfirm = 0;
                            this.emailSelects = this.confirmEmails[this.currentEmailConfirm];
                            this.resultCheckEmailExisting = this.mapConfirmEmails[this.confirmEmails[this.currentEmailConfirm]];
                            this.popupSelectAccountActive = true;
                            return;
                        }
                    }
                    const iterable = () => {
                        const item = arrApply.shift();
                        if(!item) return;
                        this.pushToSteps(item, ()=>{
                            iterable();
                        });
                    }
                    iterable();
                }
            },
            setUserViewnameSuggestLabel (item) {
                return item.name;
            },
            setEmailViewSuggestLabel (item) {
                return item.email;
            },
            // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
            getUsersViewSuggestionList(inputValue) {
                this.userViewnameSelect = inputValue;
                if (inputValue) {
                    const $this = this;
                    clearTimeout(this.checkUserInputTimeout);
                    this.checkUserInputTimeout = setTimeout(function () {
                        Axios.get(`${config.BASE_API_URL}${$this.$store.state.home.usingPublicHash ? '/public' : ''}/users?filter=${inputValue}`, {data: {nowait: true, usingHash: $this.$store.state.home.usingPublicHash}})
                    .then(response => {
                            if ($this.userViewnameSelect === inputValue) {
                        const users =  response.data ? response.data.data.map(item => {
                            item.name = item.family_name?item.family_name + ' ' + item.given_name:item.name;
                            return item
                        }) : [];
                                $this.userViewSuggestions = users;
                            }
                    })
                    .catch(error => {
                            if ($this.userViewnameSelect === inputValue) $this.userViewSuggestions = [];
                    });
                    }, 300);
                } else {
                    this.userViewSuggestions = [];
                }
            },
            getEmailsViewSuggestionList(inputValue) {
                this.emailViewSuggestValidateMsg ='';
                this.emailViewSelect = inputValue;
                if(this.userViewSuggestSelect && this.userViewSuggestSelect.email !== inputValue) {
                    this.suggestViewDisabled = false;
                }
                if (inputValue) {
                    const $this = this;
                    clearTimeout(this.checkEmailInputTimeout);
                    this.checkEmailInputTimeout = setTimeout(function () {
                        Axios.get(`${config.BASE_API_URL}${$this.$store.state.home.usingPublicHash ? '/public' : ''}/users?filter=${inputValue}`, {data: {nowait: true, usingHash: $this.$store.state.home.usingPublicHash}})
                    .then(response => {
                            if ($this.emailViewSelect === inputValue) {
                        const users =  response.data ? response.data.data.map(item => {
                            item.name = item.family_name?item.family_name + ' ' + item.given_name:item.name;
                            return item
                        }) : [];
                                $this.emailViewSuggestions = users;
                            }
                    })
                    .catch(error => {
                            if ($this.emailViewSelect === inputValue) $this.emailViewSuggestions = [];
                    });
                    }, 300)
                } else {
                    this.emailViewSuggestions = [];
                }
            },
            getFocusUsersViewSuggestionList() {
                if (this.userViewnameSelect && this.userViewSelectedFlg) {
                    var inputValue = this.userViewnameSelect;
                    const $this = this;
                    this.userViewSelectedFlg = false;
                    Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                    .then(response => {
                        if ($this.userViewnameSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                            $this.userViewSuggestions = users;
                        }
                    })
                    .catch(error => {
                        if ($this.userViewnameSelect === inputValue) $this.userViewSuggestions = [];
                    });
                }
            },
            getFocusEmailsViewSuggestionList() {
                if (this.emailViewSelect && this.emailViewSelectedFlg) {
                    var inputValue = this.emailViewSelect;
                    const $this = this;
                    this.emailViewSelectedFlg = false;
                    Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                    .then(response => {
                        if ($this.emailViewSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                            $this.emailViewSuggestions = users;
                        }
                    })
                    .catch(error => {
                        if ($this.emailViewSelect === inputValue) $this.emailViewSuggestions = [];
                    });
                }
            },
            // PAC_5-2189 End
            onSuggestViewSelect: function (user) {
                this.emailViewSuggestValidateMsg ='';
                this.userViewSuggestModel = user;
                this.emailViewSuggestModel = user;
                this.userViewSuggestSelect = user;
                this.emailViewSelect = user.email;
                this.userViewnameSelect = user.name;
                this.suggestViewDisabled = true;
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
                this.userViewSuggestions = [];
                this.emailViewSuggestions = [];
                this.userViewSelectedFlg = true;
                this.emailViewSelectedFlg = true;
                // PAC_5-2189 End
            },
            clearViewSuggestionInput() {
                this.userViewnameSelect = '';
                this.emailViewSelect = '';
                this.userViewSuggestModel = {};
                this.emailViewSuggestModel = {};
                this.suggestViewDisabled = false;
            },
            async addUserView(){
                if (this.selectUserView.length > 0){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `設定できるユーザーは1名のみです`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                    return;
                }

                if(this.getUserView.find((v) => v.email === this.emailViewSelect)){
                        this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `すでに設定されているユーザーです。`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                    return;
                }
                this.emailViewSuggestValidateMsg ='';
                if(!this.emailViewSelect) {
                    this.emailViewSuggestValidateMsg = '必須項目です';
                    return;
                }
                if(!(/.+@.+\..+/.test(this.emailViewSelect))) {
                    this.emailViewSuggestValidateMsg = 'メールアドレスが正しくありません';
                    return;
                }
                var company_id = null;
                var result = await Axios.get(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public': ''}/userView/checkemail/${this.emailViewSelect}`, {data:{nowait: true,usingHash: this.$store.state.home.usingPublicHash} })
                    .then(response => {
                        return response.data ? response.data.data: [];
                    })
                    .catch(error => { return []; });
                const user = {
                    circular_id : this.circular.id,
                    parent_send_order: this.circularUserLastSend.parent_send_order,
                    mst_user_id : result ? result.id : '',
                    memo: "",
                    del_flg : 0,
                    create_user : this.circularUserLastSend.email,
                    update_user : this.circularUserLastSend.email,
                    email: this.emailViewSelect,
                    name: this.userViewnameSelect ? this.userViewnameSelect : (result ? (result.family_name ? result.family_name + ' ' + result.given_name : '社員') : '社員'),
                    company_id: result ? (result.mst_company_id ? result.mst_company_id : result.company_id) : '',
                    user_auth: result ? result.user_auth : 0,
                    option_flg: result ? (result.option_flg ? result.option_flg : 0) : 0,
                };
                if(this.userHashInfo) {
                    this.loginUser = null;
                }
                //現在ログインしているユーザーの情報を取得する
                let localUser ;
                let localEmail = this.circularUserLastSend.email ;
                this.selectUsers.forEach(function (value) {
                    if (value.email === localEmail)
                        localUser = value;
                });
                if (user.company_id == null ||(user.company_id != null && ((this.loginUser && user.company_id != this.loginUser.mst_company_id) || (this.userHashInfo && user.company_id != this.userHashInfo.mst_company_id)))) {
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `別企業のユーザーは設定できません`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                }else if(user.email == this.circularUserLastSend.email){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `ログインユーザーは設定できません`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                }else if(this.selectUsers.find((v) => v.email === user.email && v.edition_flg == localUser.edition_flg && v.env_flg == localUser.env_flg && v.server_flg == localUser.server_flg)) {
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `承認者は登録できません。`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                }else if(user.user_auth == 5 || user.option_flg == 2){
                  this.$vs.dialog({
                    type: 'alert',
                    color: 'danger',
                    title: `確認`,
                    acceptText: '閉じる',
                    text: `受信専用利用者は閲覧ユーザーとして追加できないです。`,
                    accept: () => {
                      this.clearViewSuggestionInput();
                    }
                  });
                }else{
                    this.$store.commit('application/addUserView', user);
                    this.clearViewSuggestionInput();
                }
            },
            deleteUserView: function(email) {
                const tmpSelectUsers = this.selectUserView.slice();
                tmpSelectUsers.splice(this.selectUserView.findIndex((item => item.name === email)))
                this.$store.commit('application/updateListUserView', tmpSelectUsers);
            },
            async toggleReturnCircular(circular_user_id) {
                this.isNotReturnCircular = !this.isNotReturnCircular
                const returnFlg = this.isNotReturnCircular?0:1;
                if (this.$store.state.home.usingPublicHash){
                    Axios.patch(`${config.BASE_API_URL}/public/circulars/${this.circular.id}/users/${circular_user_id}/updateReturnflg?returnFlg=${returnFlg}`, {nowait: true,usingHash: this.$store.state.home.usingPublicHash})
                }else{
                    Axios.patch(`${config.BASE_API_URL}/circulars/${this.circular.id}/users/${circular_user_id}/updateReturnflg?returnFlg=${returnFlg}`, {data:{nowait: true,usingHash: this.$store.state.home.usingPublicHash}})
                }
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
            onTreeAddToStepDoubleClick(node) {
                node.check();
                this.onTreeAddToStepClick();
            },
            onAroundArrow:function(){
                let obj = document.getElementById("arrow");
                if(this.searchAreaFlg){
                    obj.classList.add("around_return");
                    obj.classList.remove("around");
                }else{
                    obj.classList.add("around");
                    obj.classList.remove("around_return");
                }
                this.searchAreaFlg = !this.searchAreaFlg;
            },
            onFavoriteSelect() {
                this.searchFavorite = '';
                this.onSearchFavorite();
            },
            generateAccessCodeOutside: function() {
              this.outsideAccessCode = this.getAccessCode(6);
            },
            // アクセスコード作成（社内社外用）
            getAccessCode(len){
              var length = len ? len : 6;
              var text = "";
              var possible = "abcdefghijkmnpqrstuvwxyz0123456789";
              for( var i=0; i < length; i++ )
                text += possible.charAt(Math.floor(Math.random() * possible.length));
              return text;
            },
            async onDepartmentUsersSelect(){
              await this.getDepartmentUsers({filter: ''});
            },
            async onUpdateContact(allowDuplicate){
                this.$validator.validateAll().then(async result => {
                    if (result) {
                        if(!allowDuplicate && !await this.checkEmail(this.editContact.email, this.editContact.id)){
                            this.confirmDuplicateEmail = true;
                            return;
                        }
                        await this.updateContact(this.editContact, this.editContact.id);
                        this.showPopupEditContacts = false;
                        this.showEditFavorite = false;
                    }
                });
            },
            async checkEmail(email, id) {
                this.listCheckEmailContact       = await this.getListContact({filter: this.filter});
                for(var i in this.listCheckEmailContact){
                    var contact  = this.listCheckEmailContact[i];
                    if(id){
                        if(contact.email == email && id != contact.id) return false;
                    }else{
                        if(contact.email == email) return false;
                    }
                }
                return true;
            },
            async onDeleteContact(){
                await this.deleteContact(this.editContact.id);
                this.confirmDelete = false;
                this.showPopupEditContacts = false;
                this.showEditFavorite = false;
            },
            onAllowDuplicate(){
                this.confirmDuplicateEmail = false;
                this.onUpdateContact(true);
            },
            onCancelDuplicate(){
                this.confirmDuplicateEmail = false;
            },

            async onSearchFavorite(){
                //this.arrFavorite = await this.getListFavorite({favorite_name:this.searchFavorite});
                /*PAC_5-1698*/
                let res = await this.getListFavorite({favorite_name:this.searchFavorite});
                res.forEach(favorite=>{
                    favorite.sort((a,b)=>{
                        return (a.child_send_order-b.child_send_order)||(a.child_send_order-b.child_send_order)
                    })
                })
                let tmp=[]
                let planArray=[]

                res.forEach((_,i)=>{
                    planArray[i]=planArray[i]||[]
                    tmp[i]=_.map((user,index)=>{
                        if(user.child_send_order==0){
                            return user
                        }
                        if (!planArray[i][user.child_send_order+''+user.parent_send_order]){
                            planArray[i][user.child_send_order+''+user.parent_send_order]=[]
                            planArray[i][user.child_send_order+''+user.parent_send_order].push(user)
                            return user
                        }else{
                            planArray[i][user.child_send_order+''+user.parent_send_order].push(user)
                            return null
                        }
                    }).filter(user=>{
                        return user!=null
                    })
                    tmp[i].forEach(_=>{
                        if (planArray[i][_.child_send_order+''+_.parent_send_order]&&planArray[i][_.child_send_order+''+_.parent_send_order].length>1){
                            _.plan_users=planArray[i][_.child_send_order+''+_.parent_send_order]
                        }
                    })
                })
                this.arrFavorite=tmp
                return this.arrFavorite;
            },
            changeTableShow(index){
                if(this.tableshow[index]){
                    delete this.$delete(this.tableshow,index)
                }else{
                    this.$set(this.tableshow,index,1);
                }
            },
            onSyncOperationClick: async function(){
                if(this.$store.state.home.usingPublicHash){
                    if (window.opener){
                        window.close();
                    }else{
                        if (this.userHashInfo){
                            if (parseInt(this.userHashInfo.current_env_flg)){
                                window.location.href = config.K5_API_URL;
                            }else{
                                if(parseInt(this.userHashInfo.current_edition_flg)){
                                    window.location.href = config.AWS_API_URL;
                                }else{
                                    window.location.href = config.OLD_AWS_API_URL;
                                }
                            }
                        }else{
                            window.location.href = config.LOCAL_API_URL;
                        }
                    }
                }else{
                    this.$route.meta.isKeep=true
                    this.$router.push('/received');
                }
            },
            createKeyLoop:function(idx, key='') {
              let timeUnix = moment().unix().toString();
              return timeUnix.substring(timeUnix.length - 5) + idx + key;
            },
          async onEditFavorite(itemFavorite, index){
            this.showEditFavorite = true;
            this.editFavoriteItem = itemFavorite;
            this.editFavoriteItemIndex = index;
          },
          startDragFavorite(user, event){
            let target = event.target;
            setTimeout(function(){
              // eslint-disable-next-line no-undef
              $(target).addClass('item-dragging');
            }, 5);
            this.draggingFavoriteUser = user;
          },
          endDragFavorite(event){
            // eslint-disable-next-line no-undef
            $(event.target).removeClass('item-dragging');
          },
          overDragFavorite(user, event){
            if (this.draggingFavoriteUser){
              if (user != null && user.id === this.draggingFavoriteUser.id){
                event.dataTransfer.dropEffect  = "none";
                return;
              }
              event.preventDefault();
            }else{
              event.dataTransfer.dropEffect  = "none";
            }
          },
          onDropFavorite: async function(user){
            if (this.draggingFavoriteUser && user != null){
              const newSelectUsers = [];
              let arrFavoriteUsers = this.editFavoriteItem;
              for(let i = 0; i < arrFavoriteUsers.length; i++){
                if (arrFavoriteUsers[i].id === user.id){
                  newSelectUsers.push(this.draggingFavoriteUser);
                }else if (arrFavoriteUsers[i].id === this.draggingFavoriteUser.id){
                  newSelectUsers.push(user);
                }else{
                  newSelectUsers.push(arrFavoriteUsers[i]);
                }
              }
              // 位置スワップ後の集合を設定
              this.editFavoriteItem = await this.sortFavoriteItem({from_favorite: user,to_favorite: this.draggingFavoriteUser});
              this.arrFavorite[this.editFavoriteItemIndex] = this.editFavoriteItem;
            }
          },
          onTreeAddToFavoriteClick: async function(userChecked) {
            if ((this.isSendAll && (this.editFavoriteItem.length + userChecked.length) > 31)) {
              this.$vs.dialog({
                type: 'alert',
                color: 'danger',
                title: `確認`,
                acceptText: '閉じる',
                text: `一斉送信が可能な人数は最大30人です。`,
                accept: () => {
                }
              });
              return;
            }
            if (userChecked.length){
              let selectedEmails = [];
              let checkedEmails = [];
              for (let i = 0; i < userChecked.length; i++) {
                selectedEmails.push(userChecked[i].email);
              }
              let result = await Axios.post(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public' : ''}/user/checkemail`, {email: selectedEmails,usingHash: this.$store.state.home.usingPublicHash})
                  .then(response => {
                    let result_data = []
                    if (response && response.data){
                      result_data = response.data.data.filter(function (value) {
                        const limit = JSON.parse(getLS('limit'));
                        if (limit && limit.environmental_selection_dialog == 0){
                          if (value.edition_flg == 1){
                            return value;
                          }
                        }else{
                          //企業IDが「１」（edition_flgが「０」）の時はゲストユーザー扱いにして、 それ以外は通常通りedition_flgが「０」のユーザーに回覧にしてほしいです
                          if (value.edition_flg !== 0 || value.company_id !== 1){
                            checkedEmails.push(value.email)
                            return value;
                          }
                        }
                      })
                    }
                    return result_data;
                  }).catch(error => {
                    return [];
                  });
              if (result.length > 1){
                // Use Email as key ,as ['email1' => [0=>[..],1=>[]], 'email..'..]
                let selectAllEmail = [];
                result.forEach((value)=>{
                  if (selectAllEmail[value.email] == null){
                    selectAllEmail.push(value.email)
                    selectAllEmail[value.email] = []
                  }
                  selectAllEmail[value.email].push(value)
                })
                //このメールは複数の環境に存在します
                let needCheckedEmail = [];
                selectAllEmail.forEach((value) => {
                  if (selectAllEmail[value].length > 1 ){
                    needCheckedEmail.push(value)
                    this.favoriteEmailSelects.push(selectAllEmail[value])
                  }
                })
                //選択する必要のあるメールを削除する
                result = result.filter(function (item) {
                  if (needCheckedEmail.includes(item.email)){
                    return false;
                  }else{
                    return item;
                  }
                })
              }
              //ゲストユーザー追加
              selectedEmails.map(function (item) {
                if (!checkedEmails || (checkedEmails && !checkedEmails.includes(item))){
                  let user = {email: item, user_id: null}
                  result.push(user)
                  return true;
                }
              })
              //複数の環境を選択するユーザー
              if (this.favoriteEmailSelects.length > 0){
                this.favoriteEmailSelect = this.favoriteEmailSelects[0][0].email;
                this.favoriteCheckEmailExisting = this.favoriteEmailSelects[0];
                this.popupSelectFavoriteAccountActive = true;
                this.favoriteAllEmailSelects = result;
                return;
              }
              this.editFavoriteItem = await this.updateFavorite({favorite_no: this.editFavoriteItem[0].favorite_no,users: result})
              this.arrFavorite[this.editFavoriteItemIndex] = this.editFavoriteItem;
            }
          },
          deleteFavoriteUser: async function(favorite_route_id, index){
            this.editFavoriteItem = await this.deleteFavoriteItem(favorite_route_id);
            this.arrFavorite[this.editFavoriteItemIndex] = this.editFavoriteItem;
          },
          selectFavoriteAccount: async function(item){//複数の環境を選択するユーザー
            this.favoriteAllEmailSelects.push(item)
            this.favoriteEmailSelects.splice(0,1);
            if (this.favoriteEmailSelects.length > 0){
              this.popupSelectFavoriteAccountActive = true;
              this.favoriteEmailSelect = this.favoriteEmailSelects[0][0].email;
              this.favoriteCheckEmailExisting = this.favoriteEmailSelects[0];
              return;
            }else {
              this.popupSelectFavoriteAccountActive = false
            }
            this.editFavoriteItem = await this.updateFavorite({favorite_no: this.editFavoriteItem[0].favorite_no,users: this.favoriteAllEmailSelects})
            this.favoriteCheckEmailExisting = []
            this.favoriteAllEmailSelects = []
            this.favoriteEmailSelect = ''
            this.arrFavorite[this.editFavoriteItemIndex] = this.editFavoriteItem;
          },
          handleViewMailList: function(){
          $('#handleViewMailList').stop().toggleClass('show');

          $('#mail-steps-card').stop().toggle(300);
        }
        },
          replaceEmoji(e){
            reg.lastIndex = 0
            const isEmoj = reg.test(e.target.value)
            if(isEmoj){
              this.commentTitle=e.target.value.replace(reg,"").trim().replace(/\s/g,"")
            }
          },
        
        async mounted() {
          if (!this.$store.state.home.usingPublicHash) {
                this.info = await this.getMyInfo();
                this.emailTemplateOptions =  Utils.setEmailTemplateOptions(this.info);
            }
        },
        watch: {
            "treeData": function(newVal, oldVal) {
                this.treeLoaded++;
            },
            "$store.state.contacts.changePhoneBooks": async function (newVal, oldVal) {
                this.treeData = await this.getEmailTrees();
            },
            "$store.state.application.loadDepartmentUsersSuccess": async function (newVal, oldVal) {
                this.treeData = await this.getEmailTrees();
                this.confirmEdit = true;
            },
            "$store.state.application.selectUserChange": function (newVal, oldVal) {
                this.usersChange = !this.usersChange;
                this.buildSelectUsersDisplay();
            },
            "$store.state.home.selectUserChange": function (newVal, oldVal) {
                this.usersChange = !this.usersChange;
                this.buildSelectUsersDisplay();
            },
            outsideAccessCode:function(){
              this.outsideAccessCode=this.outsideAccessCode.replace(/[\W]/g,'');
            },
            showEditFavorite:function(newVal){
              this.confirmEdit = !newVal;
            },
        },
        async created() {

            // Check Mobile
            if (
              /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
                navigator.userAgent
              )
            ) {
              this.isMobile = true
            }

            var limit = getLS("limit");
            limit = JSON.parse(limit);
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            this.$store.commit('application/updateListUserView', []);
            if(limit && limit.enable_any_address == 1){
                this.checkShowAddress = true;
            }else{
                this.checkShowAddress = false;
            }
            /*PAC_5-2616 S*/
           if (limit) {
              this.enable_any_address = limit.enable_any_address
              this.require_approve_flag = limit.require_approve_flag; //PAC_2705
           }
           /*PAC_5-2616 E*/
            this.addLogOperation({ action: 'r11-display', result: 0});
            if(!this.$store.state.home.fileSelected || !this.$store.state.home.circular || this.$store.state.home.circular.circular_status === this.CIRCULAR.SAVING_STATUS) {
                //this.$router.push('/');
            }
            // 特設サイト
            this.specialCircularFlg = this.circular && this.circular.special_site_flg;
            const hash = this.$route.params.hash;
            if(hash) {
                localStorage.setItem('tokenPublic', hash);
                this.$store.commit('home/setUsingPublicHash', true);

                this.userHashInfo = await this.getInfoByHash();
                this.emailTemplateOptions = Utils.setEmailTemplateOptions(this.userHashInfo);
                localStorage.setItem('envFlg', this.userHashInfo.current_env_flg);
              /*PAC_5-2616 S*/
                this.enable_any_address = this.userHashInfo.enable_any_address
              /*PAC_5-2616 E*/
              /*PAC_5-2705 S*/
                this.require_approve_flag = this.userHashInfo.require_approve_flag;
              /*PAC_5-2705 E*/
            }
            // 申請者だけ、件名変更クリア表示
            if((hash && this.$store.state.home.circular.create_user == this.userHashInfo.email) ||
                (!hash && this.$store.state.home.circular.create_user == this.loginUser.email)){
              this.checkShowTitle = true;
              this.commentTitle = this.$store.state.home.title;
            }
            if(this.$store.state.home.circular){
              this.checkShowButtonApply = false;
                if(this.$store.state.home.circular.circular_status ===  CIRCULAR.SEND_BACK_STATUS){
                  if(this.circularUserLastSend && this.circularUserLastSend.parent_send_order == 0 && this.circularUserLastSend.child_send_order == 0){
                    this.checkShowButtonApply = true;
                  }
                }
            }
            //this.treeData = await this.getEmailTrees();
            this.formatSelectUsers = this.loadFormatSelectUsers();

            if(this.selectUsers && this.selectUsers.length > 0 ) {
                let loginUser = this.selectUsers.find(user => ((!hash && user.email === this.loginUser.email) || (hash && user.email === this.userHashInfo.email) ));
                if(loginUser) {
                    let applicant = this.selectUsers.find(user => user.parent_send_order === loginUser.parent_send_order && ((user.parent_send_order === 0 && user.child_send_order === 0) || (user.child_send_order === 1)));
                    if(applicant){
                        this.isNotReturnCircular = (applicant.return_flg == 0);
                        //PAC_5-2705
                        if(this.require_approve_flag && this.circularUserLastSend.parent_send_order > 0 && this.circularUserLastSend.child_send_order == 1 ){
                          this.isNotReturnCircular = true;
                          const returnFlg = 0;
                          let circular_user_id = this.circularUserLastSend.id;
                          Axios.patch(`${config.BASE_API_URL}/circulars/${this.circular.id}/users/${circular_user_id}/updateReturnflg`, {returnFlg});
                        }
                        let sendCompanyUsers = this.$store.state.home.circular.users.filter(user=>user.parent_send_order==0)
                        if (loginUser.return_flg != this.selectUsers[0].return_flg && sendCompanyUsers.length>1){
                            const returnFlg = this.selectUsers[0].return_flg
                            this.isNotReturnCircular = (returnFlg == 0);
                            if (this.$store.state.home.usingPublicHash){
                                Axios.patch(`${config.BASE_API_URL}/public/circulars/${this.circular.id}/users/${this.circularUserLastSend.id}/updateReturnflg?returnFlg=${returnFlg}`, {nowait: true,usingHash: this.$store.state.home.usingPublicHash})
                            }else{
                                Axios.patch(`${config.BASE_API_URL}/circulars/${this.circular.id}/users/${this.circularUserLastSend.id}/updateReturnflg?returnFlg=${returnFlg}`, {data:{nowait: true,usingHash: this.$store.state.home.usingPublicHash}})
                            }
                        }
                    }
            }
            }
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            if(this.circular && this.circularChangeListUserView && this.circularChangeListUserView[this.circular.id]){
                this.$store.commit('application/updateListUserView', this.circularChangeListUserView[this.circular.id]);
                if(this.circularChangeListUserView[this.circular.id].length > 0){
                    this.onAroundArrow();
                }
                this.$store.commit('home/updateCircularChangeListUserView',{id:this.circular.id,data:[]});
            }
            //PAC_5-1723
            this.$store.state.application.getUserView.length = 0;
            await Axios.get(`${config.BASE_API_URL}/getUserView?email=${encodeURIComponent(this.circularUserLastSend.email)}&circular_id=${this.circular.id}`)
                .then(res => {

                    for (let i = 0 ; i <=res.data.i ; i++){
                        const user = {
                        email: res.data.email[i],
                        name:  res.data.name[i],
                        };

                        this.$store.commit('application/getUserView', user);
                    }

                  return;
                })
                .catch(error => {
                  this.clickState = false;
                  return []; });
            //endPac_51723
            this.buildSelectUsersDisplay();
            // 特設サイト
          this.groupName = this.circular.special_site_group_name;
          if(this.specialCircularFlg){
            this.circular.users.forEach(item => {
              if(hash && item.email == this.userHashInfo.email && item.mst_company_id == this.userHashInfo.mst_company_id && item.special_site_receive_flg == 1){
                this.specialCircularReceiveFlg = true;
              }
              if(!hash && item.email == this.loginUser.email && item.mst_company_id == this.loginUser.mst_company_id && item.special_site_receive_flg == 1){
                this.specialCircularReceiveFlg = true;
              }
              if(item.special_site_receive_flg == 1 && item.id == this.circularUserLastSend){
                this.circularUserLastSendIdIsSpecial = true;
              }
            });
          }
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
        };
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
<style lang="scss" scope>
input{
  transform: scale(1)!important;
}
.checkcurrentclass{

  .item:hover {
      position: relative;
      z-index: 99;
      -webkit-box-shadow: 0 0 0 0 rgba(0,0,0,.2);
  }
  :hover{

      position: relative;
      z-index: 99;
      -webkit-box-shadow: 0 4px 15px 0 rgba(0,0,0,.2);
  }
}
.like_addrs{
    margin-top:5px;
    width: 100%;
    li{
        .like_addrs_content{
            white-space: break-spaces;
            background: #A1CFA1;
            margin:0 auto;
            padding: 12px 10px;
            border-radius: 5px;
            width: 80%;
            span{
                padding-right: 15px;
                margin-left:10px;
            }

        }
        .triangle{
            display: block;
            width: 0;
            height: 0;
            border-left: 12px solid transparent;
            border-right: 12px solid transparent;
            border-top: 12px solid #999;
            margin: 10px auto;
        }

        &:last-child{
            .triangle{
                display: none;
            }
        }
    }
}

.circular-destination-page-dialog.mobile{
  .append-text.btn-addon{
    button{
      margin: 0;
    }
  }
  .contact_tree_action {
    padding-right: 0 !important;
  }
  .favorite_dialog{
    .item{
      margin-bottom: 20px;
    }
    button{
      width: auto;
      white-space: nowrap;
      padding: 0.75rem 1rem !important;
    }
  }

  .like_addrs li .like_addrs_content{
    padding: 12px 5px;
    text-overflow: ellipsis;
    overflow: hidden;
  }
}


#circular-destination-page.mobile{
  display: none;
}

.detail-popup.mobile{
  .mail-steps {
    .final {
      right: 30px;
      top: 20px;
    }
    .remove-flag{
      top: 5px;
      right: 10px;
    }
  }
}

#circular-destination-page-mobile.mobile{
  display: block !important;

  input[type=text], select, textarea{
    transform: scale(1);
  }

  .vx-row{
    margin: 0;
  }

  .action {
    text-align: center;
    position: fixed;
    bottom: 10px;
    left: 5%;
    max-width: 90%;
    padding: 0;

    button{
      float: left;
      width: 48% !important;
      padding: .75rem 0;
      margin: 0;

      &:first-child{
        margin-right: 4%;
      }
    }
  }

  #handleViewMailList{
    position: relative;
    margin-top: 5px;
    margin-left: -5px;
    transition: 0.3s;

    &.show{
      margin-left: 0px;

      svg{
        transform: rotate(90deg);
      }
    }

    svg{
      height: 30px;
      margin-left: 5px;
      transition: 0.3s;
    }
  }

  #mail-steps-card{
    transition: 0s !important;

    .vx-card__body{
      padding: 0 0 0 0;
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

  .breadcrumb {

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
    }
  }

}

@media (min-width: 601px) {
  #circular-destination-page-mobile.mobile{
    margin-bottom: 70px;

    .circular-destination-title{
      margin-top: 10px;
    }
    .action{
      max-width: 550px ;
      left: calc(50% - 275px);

      button{
        padding: 1.2rem 0;
        font-size: 18px;
        width: 40% !important;

        &:first-child{
          margin-right: 20%;
        }

        img{
          height: 18px;
        }
      }
    }
  }
}

@media (max-width: 600px) {
  #circular-destination-page-mobile.mobile {
    top: -60px;
    position: relative;
    margin-top: 30px;

    .mail-steps {
      .group {
        position: relative;

        #ckeckReturnCircular{
          position: relative;
          top: -17px;
        }

        .item{
          &.first{
            &:before{
              top: -35px;
              left: calc(50% - 7px);
              transform:rotate(0deg);
              height: 50px;
            }
          }
        }
      }
    }
  }
}
@media (max-width: 600px) {
  #circular-destination-page-mobile.mobile {
    margin-bottom: 0;
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
.detail-popup {
  .vs-popup{
    width: 800px !important;
  }
}
</style>
