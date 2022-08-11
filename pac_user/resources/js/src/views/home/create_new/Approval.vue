<template>
    <div id="approval-page">
        <div style="width:100%;margin-bottom:10px;padding-top:5px; border-bottom: 1px solid #ddd;">
            <span @click="goBack"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
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
            <div class="vx-col w-full mb-base lg:pr-0">
                <vs-row vs-type="flex" class="mb-1">
                  <vs-col vs-type="flex" vs-w="8">
                    <div>
                      <h4 style="position:relative;top:3px;">宛先・回覧順</h4>
                      <div id="handleViewMailList" class="show" v-on:click="handleViewMailList">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><path d="M118.6 105.4l128 127.1C252.9 239.6 256 247.8 256 255.1s-3.125 16.38-9.375 22.63l-128 127.1c-9.156 9.156-22.91 11.9-34.88 6.943S64 396.9 64 383.1V128c0-12.94 7.781-24.62 19.75-29.58S109.5 96.23 118.6 105.4z"/></svg>
                      </div>
                    </div>
                  </vs-col>
                  <vs-col vs-type="flex" vs-w="4" style="position:relative;">
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

                      <div class="mail-list">
                            <vs-row v-if="selectUsers.length > 0" vs-type="flex" :class="['group applicant', (selectUsersDisplay && (selectUsersDisplay.length > 0) && selectUsersDisplay[0].length > 2) ? 'return-flg': '', 'not-return']">
                                <vs-col vs-w="6" vs-xs="12">
                                    <vs-row vs-type="flex" vs-justify="center" class="first_me">
                                        <vs-col vs-w="10" vs-xs="12" class="item me" vs-type="flex" vs-align="flex-start">
                                            <div class="name">{{selectUsers[0].name}}</div>
                                            <div class="email">【{{selectUsers[0].email}}】</div>
                                            <span v-if="selectUsers.length === 1 && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</span>
                                            <a href="#" class="currentUser-flg"> <i class="far fa-flag"></i></a>
                                        </vs-col>
                                        <vs-col vs-w="10" vs-xs="12" class="mt-2">
                                            <vs-checkbox :value="isNotReturnCircular" v-if="!isSendAll && (!onlyInternalUser || specialCircularFlg) && selectUsersDisplay[0].length > 1" @click="toggleReturnCircular(selectUsers[0].id)" style="">最終承認者から直接社外に送る</vs-checkbox>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>

                                <vs-col vs-w="6"  vs-xs="12" class="child1st-block" v-if="!isTemplateCircular">
                                    <draggable
                                            :list="selectUsersDisplay[0]"
                                            v-if="selectUsersDisplay"
                                            @change="onDragItemChange"
                                            @end="onDragEnd"
                                            animation="500"
                                            group="selectUsers"
                                            :class="['full-width range-0']"
                                            :move="onItemMoving"
                                            swap="true"  
                                            >
                                            <template v-for="(user, index) in selectUsersDisplay[0]">
                                              <vs-row vs-justify="center" vs-type="flex"
                                                  
                                                      :key="user.email + index + changeTimes"
                                                      v-if="index > 0">
                                                      
                                                  <vs-col
                                                          vs-w="10"
                                                          vs-xs="12"
                                                          vs-type="flex"
                                                          vs-align="flex-start"
                                                          style="background: #C8EFC8"
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
                                                      <span @touchend="onRemoveCircularUser(user.id)"
                                                        class="text-danger remove-flag"><i class="fas fa-times"></i></span>


                                                  </vs-col>
                                                  <!--PAC_5-1698 S-->
                                                  <vs-col
                                                      vs-w="10"
                                                      vs-xs="12"
                                                      vs-type="flex"
                                                      vs-align="flex-start"
                                                      v-if="user.plan_users"
                                                      style="background:rgb(250,168,62)"
                                                      :class="['item child-order item-draggable', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 1 && selectUsersDisplay[0].length > 2) ? 'return-flg': '',(user.email === selectUsers[0].email) ? 'me': '']" 
                                                      >

                                                      <div class="plan-left" @touchend="showPlanDetail(user.plan_users)">
                                                        <div>{{index}} - {{'合議設定'}}</div>
                                                        <div>【{{user.plan_users.length+'人'}}】</div>
                                                      <!--PAC_5-2795 S-->
                                                        <div class="plan-left-remark">クリックすると回覧の詳細が表示されます。</div>
                                                      <!--PAC_5-2795 E-->
                                                    </div>

                                                    <div class="plan-form">
                                                    
                                                        <div class="plan-txt">
                                                            <span>合議</span>
                                                        </div>
                                                        <div class="plan-radio">
                                                            <label for="plan_mode_1" @touchend="updatePlan(user, 1)">
                                                                <span :class="'checkmark '+(user.plan_mode==1?'checked':'')"></span>
                                                                <span>全員必須</span>
                                                            </label>
                                                            <label for="plan_mode_3" @touchend="updatePlan(user, 3)">
                                                                <span :class="'checkmark '+(user.plan_mode==3?'checked':'')"></span>
                                                                <span>人数指定</span>
                                                            </label>
                                                        </div>

                                                        <div class="plan-input">
                                                            <vs-input class="inputx" type="number"  v-if="user.plan_mode==3" @click.stop="()=>{}" @change="updatePlan(user)" @touchend="focusInput"  v-model="user.score" />
                                                            <vs-input class="inputx" v-else  disabled   name="subject"  />
                                                            <span style="padding-left: 10px; position: relative;top:10px;">人</span>
                                                        </div>
                                                    </div>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)" class="final plan_mode"> 最終</span>
                                                    <span @touchend="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></span>

                                                  </vs-col>
                                                  <!--PAC_5-1698 E-->
                                              </vs-row>
                                            </template>

                                        <vs-row vs-type="flex" vs-justify="center" v-if="!isSendAll && (!onlyInternalUser || specialCircularFlg) && selectUsersDisplay[0].length > 1 && !isNotReturnCircular">
                                            <vs-col vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    class="item child-order me">
                                                <div class="name">{{selectUsersDisplay[0].length}} - {{selectUsers[0].name}}</div>
                                                <div class="email">【{{selectUsers[0].email}}】</div>
                                            </vs-col>
                                        </vs-row>

                                    </draggable>
                                </vs-col>

                                <vs-col vs-w="6" vs-xs="12" class="child1st-block" v-else>
                                    <div  v-if="templateUserRoutes"
                                            :class="['full-width range-0']"  >
                                        <vs-row vs-justify="center" vs-type="flex"
                                                v-for="(userRoutes, userRoutesIndex) in templateUserRoutes" :key="userRoutesIndex + changeTimes">
                                            <vs-col
                                                    vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    :class="['item child-order ', userRoutesIndex === 0 ? 'first' : '', (userRoutesIndex === templateUserRoutes.length-1 && userRoutesIndex >0)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>{{userRoutes[0].user_routes_name}}</span>
                                                <template v-for="(user, index) in userRoutes">
                                                    <div class="name" :key="index">{{user.name || '社員'}} 【{{user.email}}】</div>
                                                </template>
                                                <span v-if="templateUserRoutes.length === userRoutesIndex + 1"
                                                      class="final"> 最終</span>
                                                <template v-for="(user, index) in userRoutes">
                                                    <a class="currentUser-flg" :key="index"
                                                       v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                       href="#"><i class="far fa-flag"></i></a>
                                                    <span @touchend="clearSelectUsers"
                                                       :key="index"
                                                       class="text-danger remove-flag"><i class="fas fa-times"></i></span>
                                                </template>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row vs-type="flex" vs-justify="center" v-if="!isSendAll && (!onlyInternalUser || specialCircularFlg) && selectUsersDisplay[0].length > 1 && !isNotReturnCircular">
                                            <vs-col vs-w="10" vs-xs="12"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    v-if="!isNotReturnCircular && selectUsersDisplay && selectUsersDisplay.length > 0 && selectUsersDisplay[0].length > 1"
                                                    class="item child-order me">
                                                <div class="name">{{selectUsers[0].name}}</div>
                                                <div class="email">【{{selectUsers[0].email}}】</div>
                                            </vs-col>
                                        </vs-row>
                                    </div>
                                </vs-col>


                            </vs-row>

                            <template v-for="(group, idx) in (selectUsersDisplay)">
                            <vs-row
                                    
                                    vs-type="flex"
                                    vs-align="space-around"
                                    :class="['group parent-block not-return', (selectUsersDisplay[idx].length > 2) ? 'return-flg': '']"
                                    :key="group+''+idx"
                                    v-if="idx > 0" >
                                <vs-col vs-w="6" vs-xs="12">
                                    <vs-row
                                            vs-type="flex"
                                            vs-justify="space-around"
                                            :key="selectUsersDisplay[idx][0].email + '0'" >
                                        <vs-col vs-w="10" vs-xs="12"
                                                :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                vs-type="flex"
                                                vs-align="flex-start" v-if="(!specialCircularFlg || specialCircularReceiveFlg || !selectUsersDisplay[idx][0].special_site_receive_flg)&&(!selectUsersDisplay[idx][0].plan_users)">                                            
                                            <div class="name">{{selectUsersDisplay[idx][0].name}} - {{selectUsersDisplay[idx][0].mst_company_name}}</div>
                                            <div class="email">【{{selectUsersDisplay[idx][0].email}}】</div>
                                            <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                  class="final"> 最終</span>
                                            <a class="currentUser-flg"
                                               v-if="circularUserLastSend && circularUserLastSend.id === selectUsersDisplay[idx][0].id"
                                               href="#"> <i class="far fa-flag"></i></a>
                                            <span @touchend="onRemoveCircularUser(selectUsersDisplay[idx][0].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></span>
                                        </vs-col>
                                        <vs-col vs-w="10" vs-xs="12"
                                                :class="['item item-draggable', idx % 3 == 1 ? 'bg-orange': (idx % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                vs-type="flex"
                                                vs-align="flex-start" v-if="specialCircularFlg && !specialCircularReceiveFlg && selectUsersDisplay[idx][0].special_site_receive_flg && !selectUsersDisplay[idx][0].plan_users">
                                          <div class="name">{{groupName}}</div>
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
                                            <template v-for="(user, index) in selectUsersDisplay[idx][0].plan_users">
                                                <div class="name" :key="index">{{user.name || '社員'}} 【{{user.email}}】</div>
                                            </template>
                                            <span v-if="selectUsersDisplay.length === (idx + 1) && selectUsersDisplay[idx].length === 1"
                                                  class="final"> 最終</span>
                                            <template v-for="(user, index) in selectUsersDisplay[idx][0].plan_users">
                                                <a class="currentUser-flg"
                                                  :key="index"
                                                   v-if="circularUserLastSend && circularUserLastSend.id === user.id"
                                                   href="#"><i class="far fa-flag"></i></a>
                                                <span @touchend="onRemoveCircularUser(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></span>
                                            </template>
                                        </vs-col>
                                        <!--PAC_5-1698 E-->
                                        <vs-col vs-w="10" vs-xs="12">
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

                                            <template  v-for="(user, index) in selectUsersDisplay[idx]">
                                        <vs-row
                                               
                                                v-if="index > 0"
                                                vs-type="flex"
                                                vs-justify="center"
                                                :key="user.email + index + changeTimes" >
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
                                                <span v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                    @touchend="onRemoveCircularUser(user.id)"
                                                   class="text-danger remove-flag"><i class="fas fa-times"></i></span>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10" vs-xs="12"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users"
                                                :class="['item child-order ', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <div class="name" :key="index">{{user.name || '社員'}} 【{{user.email}}】</div>
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
                                                <template v-for="(user, index) in selectUsersDisplay[idx][0].plan_users">
                                                    <div class="name" :key="index">{{user.name || '社員'}} 【{{user.email}}】</div>
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
                                                <span v-if="circular && allowAddDestination && user.id != circularUserLastSend.id && ((loginUser && user.create_user === loginUser.email) || (userHashInfo && user.create_user === userHashInfo.email)) && circularUserLastSend.parent_send_order === user.parent_send_order && user.child_send_order > circularUserLastSend.child_send_order  && user.child_send_order > 1"
                                                    @touchend="onRemoveCircularUser(user.id)"
                                                   class="text-danger remove-flag"><i class="fas fa-times"></i></span>
                                            </vs-col>
                                            <!--PAC_5-1698 S-->
                                            <vs-col
                                                vs-w="10" vs-xs="12"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                v-if="user.plan_users"
                                                :class="['item child-order ', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '',  (index === 0 && selectUsersDisplay[0].length > 1) ? 'return-flg': '']"  >
                                                <span>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</span>
                                                <template v-for="(user, index) in user.plan_users">
                                                    <div class="name" :key="index">{{user.name || '社員'}} 【{{user.email}}】</div>
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

                    </div>
                    









                </vx-card>
                <div class="mail-form" v-if="infoCheck['addressbook_only_flag'] == 0">
                    <form v-if="!checkShowAddress && enable_any_address!=2">
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
                            </div>
                        </vs-row>
                        <vs-row class="mt-4">
                            <vs-col vs-type="flex" vs-w="9">
                                <vs-checkbox :value="isTemplateCircular ? false : addToContactsFlg" v-on:click="addToContactsFlg = !addToContactsFlg" :disabled="isTemplateCircular">回覧時にアドレス帳に追加</vs-checkbox>
                            </vs-col>
                            <vs-col vs-type="flex" vs-w="3" vs-justify="flex-end" vs-align="right">
                                <vs-button @click.prevent="submitMailStepForm" class="square mr-0"  color="primary" type="filled" :disabled="isTemplateCircular" style="white-space: nowrap; padding: 0.75rem 0;min-width:65px;"> 追加</vs-button>
                            </vs-col>
                        </vs-row>
                    </form>
                </div>

                <vx-card class="mt-6 bg-none">
                  <vs-row class="pb-4">
                    <h4>件名</h4>
                  </vs-row>
                  <vs-row>
                    <vs-input class="inputx w-full" placeholder="件名をつけて送信できます。" v-validate="'max:50'" name="subject" v-model="commentTitle" @change="toChangeCommentTitle" />
                  </vs-row>
                </vx-card>

                <vx-card class="mt-6 bg-none">
                  <vs-row class="pb-4">
                    <h4>メッセージ</h4>
                  </vs-row>
                  <vs-row>
                    <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="commentContent" v-validate="'max:500'" name="content" @change="changeCommentContent" />
                  </vs-row>
                </vx-card>
                    
            </div>


            <div class="vx-col w-full" style="margin-top: -15px;">
                <vx-card class="mb-4 bg-none" style="height: 100%">
                    <vs-row class="border-bottom pb-4">
                        <h4>保護設定</h4>
                    </vs-row>
                    <div class="mb-4 mt-6">
                        <vs-row>
                            <vs-checkbox class="mb-2 mt-3" :value="isTemplateCircular ? false : allowChangeDestinationFlg" :disabled="!protectionSetting.protection_setting_change_flg || isTemplateCircular" v-on:click="changeDestinationFlg">宛先、回覧順の変更を許可する</vs-checkbox>
                        </vs-row>
                        <vs-row v-if="settingLimit?settingLimit.text_append_flg==1:false">
                            <vs-checkbox class="mb-2 mt-3" :value="(settingLimit?settingLimit.text_append_flg==1:false)?protectionSetting.text_append_flg:false" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="changTextAppendFlg">テキスト追加を許可する</vs-checkbox>
                        </vs-row>
                        <vs-row v-if ="showEmailThumbnailOption">
                            <vs-checkbox class="mb-2 mt-3" :value="showThumbnailFlg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="changShowThumbnailFlg">メール内の文書のサムネイル を表示する</vs-checkbox>
                        </vs-row>
                        <vs-row>
                            <!--PAC_5-2245-->
                            <vs-checkbox class="mb-2 mt-3" :value="protectionSetting.require_print" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="changeRequirePrint">捺印設定</vs-checkbox>
                        </vs-row>
                        <vs-row>
                            <vs-col vs-w="6" vs-xs="12" class="mb-2" vs-align="center"><vs-checkbox class="mt-3" :value="accessCodeFlg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="changeAccessCodeFlg">アクセスコードで保護する（社内用）</vs-checkbox></vs-col>
                            <vs-col vs-w="6" vs-xs="12" vs-align="center">
                                <vx-input-group v-if="accessCodeFlg"  class="w-full mb-0">
                                    <vs-input v-model="accessCode" @change="toSaveCircularSetting" maxlength="6" />
                                    <template slot="append">
                                        <div class="append-text btn-addon">
                                            <vs-button color="primary" v-on:click="generateAccessCode"><i class="fas fa-sync-alt"></i></vs-button>
                                        </div>
                                    </template>
                                </vx-input-group>
                            </vs-col>
                        </vs-row>
                        <vs-row :style="!outsideAccessCodeShowFlg ? 'visibility:hidden;' : ''">
                          <vs-col vs-w="6" vs-xs="12" class="mb-2" vs-align="center"><vs-checkbox class="mt-3" :value="outsideAccessCodeFlg" :disabled="checkAuthFlg || !protectionSetting.protection_setting_change_flg" v-on:click="changeOutsideAccessCodeFlg">アクセスコードで保護する（社外用）</vs-checkbox></vs-col>
                          <vs-col vs-w="6" vs-xs="12" vs-align="center">
                            <vx-input-group v-if="outsideAccessCodeFlg"  class="w-full mb-0">
                              <vs-input v-model="outsideAccessCode" @change="toSaveCircularSetting" maxlength="6" />
                              <template slot="append">
                                <div class="append-text btn-addon">
                                  <vs-button color="primary" v-on:click="generateAccessCodeOutside"><i class="fas fa-sync-alt"></i></vs-button>
                                </div>
                              </template>
                            </vx-input-group>
                          </vs-col>
                        </vs-row>
                    </div>
                </vx-card>
              </div>

        </div>
        <vs-popup class="application-page-dialog mobile" title=""  :active.sync="confirmEdit">
            <div class="vx-col w-full mb-base">
                <vx-card class="h-full">
                    <vs-row>
                        <vs-col vs-w="12">
                            <vs-tabs :key="this.myCompanyInfo.template_route_flg">
                                <vs-tab @click="showTree = true" label="アドレス帳" v-if="enable_any_address!=2">
                                    <vs-row>
                                    </vs-row>
                                </vs-tab>
                                <vs-tab  @click="onFavoriteSelect(),showTree = false" label="お気に入り" v-if="!specialCircularFlg && !isSendAll && enable_any_address!=2">
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
                                        <draggable v-model="arrFavorite" @change="onSortFavorite">
                                            <transition-group>
                                                <div v-for="(itemFavorite, indexFavorite) in arrFavorite" :key="'favorites-'+indexFavorite" class="item" @click="changeTableShow(indexFavorite)" >
                                                        <div class="mt-0" style="margin-top:5px;line-height:2.5rem;border-bottom: none;margin-bottom: -1px;">{{indexFavorite+1}}.{{itemFavorite[0].favorite_name ? itemFavorite[0].favorite_name:'名称未設定'}}</div>
                                                        <div  class="mt-0"  style="display: flex;justify-content: space-between;border-top:none">
                                                            <div style="width: 60px;">
                                                                <vs-button class="vs-button_dialog square action action_dialog" color="primary" @click="onApplyFavorite(itemFavorite)">追加</vs-button>
                                                            </div>
                                                            <ul class="like_addrs"  v-if="tableshow.hasOwnProperty(indexFavorite)">
                                                                <li v-for="(uval,uindex)  in itemFavorite" :key="uindex">
                                                                    <template v-if="uval.plan_users" >
                                                                        <div class="like_addrs_content" style="border-radius: 5px 5px 0 0"><span>{{uindex + 1}}:</span> 合議<template v-if="uval.mode!=null">（{{uval.mode==1?"全員必須":uval.score+"人"}}）</template></div>
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
                                                                <a href="#" class="text-danger" @click="onRemoveFavorite(itemFavorite[0].favorite_no)"><i class="fas fa-times"></i></a>
                                                            </div>
                                                        </div>
                                                </div>
                                            </transition-group>
                                        </draggable>
                                    </div>
                                </vs-tab>
                                <vs-tab    v-if="this.myCompanyInfo.template_route_flg && !specialCircularFlg && !isSendAll" @click="onTemplateSelect(),showTree = false" label="承認ルート"  ref="template_route">
                                    <vs-row style="align-items:baseline">
                                        テンプレート名称：
                                        <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="12">
                                            <vx-input-group  class="w-full mb-0">
                                                <vs-input v-model="templateSearchModel"/>
                                                <template slot="append">
                                                    <div class="append-text btn-addon">
                                                        <vs-button color="primary" @click="templateSearch"><i class="fas fa-search"></i></vs-button>
                                                    </div>
                                                </template>
                                            </vx-input-group>
                                        </vs-col>
                                    </vs-row>
                                    <div class="template_dialog">
                                        <div class="checkbox" style="margin: 10px 10px">
                                            <label>
                                                <input type="checkbox" @click="tempWithMe()" id="tempWithMeBox" v-model="info.template_route_flg">　関係部署を表示
                                            </label>
                                        </div>
                                        <div class="item mt-1" v-for="(itemTemplates, indexTemplates) in arrTemplate" style="display: inline-block;flex-direction:column;" :key="indexTemplates">
                                            <div class="mb-0 w-100">{{ itemTemplates.name }}</div>
                                            <div style="display: flex;">
                                                <div style="justify-items: flex-end; align-items: center;margin: auto 0;">
                                                    <vs-button class="square mr-2 templateButton" color="primary" type="filled" v-bind:disabled="!itemTemplates.template_valid" @click="selectTemplate(itemTemplates.template_rotes)"> 適用</vs-button>
                                                </div>
                                                <div style="display: flex; flex-grow: 1;">
                                                    <template v-for="(itemTemplate, indexTemplate) in itemTemplates.template_rotes">
                                                        <div :class="itemTemplate.template_route_valid ? 'name template-route-ok':'name template-route-ng'" :key="indexTemplate">
                                                            {{ itemTemplate.department_name }}<br>
                                                            {{ itemTemplate.position_name }}<br>
                                                            {{ itemTemplate.option != 0 ? itemTemplate.option : '' }}{{ template_modes[itemTemplate.mode] }}
                                                        </div>
                                                        <div class="toright" :key="'template-'+indexTemplate+'-icon'" v-if="indexTemplate<itemTemplates.template_rotes.length-1"><i class="fas fa-caret-right"></i></div>
                                                    </template>
                                                </div>
                                                <div style="justify-items: flex-end; align-items: center;margin: auto 0;">
                                                    <vs-button class="square mr-0 templateButton"  color="primary" type="filled" style="margin-left:5px" @click="onShowTemplateDetail(itemTemplates.name, itemTemplates.template_valid, itemTemplates.template_rotes)"> 詳細</vs-button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </vs-tab>
                            </vs-tabs>
                        </vs-col>
                        <vs-col vs-w="12">
                            <ContactTree ref="tree" :not-all-send="!isSendAll" :show-plan="myCompanyInfo.user_plan_flg==1" v-show="showTree && enable_any_address!=2" :opened="confirmEdit" :treeData="treeData" @onTreeAddToStepClick="onTreeAddToStepClick" @onNodeClick="showModalEditContacts"/>
                        </vs-col>
                    </vs-row>
                    <div slot="no-body-bottom">
                    </div>
                </vx-card>
            </div>
        </vs-popup>
        
        <modal name="confirm-no-comment-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'confirm-no-comment-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="block" v-if="!this.$store.state.application.commentTitle">
                    <p>件名が未入力ですが、このまま申請しますか？</p>
                </vs-col>
                <vs-col vs-w="12" vs-type="block" v-if="this.$store.state.application.commentTitle">
                    <p>送信します。よろしいですか？</p>
                </vs-col>
            </vs-row>
            <vs-row class="mp-3 pt-6" vs-type="flex" style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px">
                <vs-checkbox :value="operationNotice" v-on:click="operationNotice = !operationNotice">次回から表示しない。</vs-checkbox>
            </vs-row>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="doSendNotifyFirst" > はい</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="$modal.hide('confirm-no-comment-modal')"> いいえ</vs-button>
            </vs-row>
        </modal>

        <modal name="confirm-sent-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'confirm-sent-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="block">
                    <p>送信しました</p>
                </vs-col>
            </vs-row>
            <vs-row class="mp-3 pt-6" vs-type="flex" style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px">
                <vs-checkbox :value="operationNoticeConfirm" v-on:click="operationNoticeConfirm = !operationNoticeConfirm">次回から表示しない。</vs-checkbox>
            </vs-row>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2 " color="#bdc3c7" type="filled" v-on:click="onUpdateOperationNoticeClick" > 閉じる</vs-button>
            </vs-row>
        </modal>
        <div style="margin-bottom: 15px" id="action">

            <vs-row class="top-bar">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-sm="12" vs-xs="12">
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                style="color:#fff;border:1px solid #dcdcdc;" color="#fff" type="filled"
                                v-on:click="goBack"><span><img
                            :src="require('@assets/images/mobile/back_white.svg')"
                            style="width: 17.5px;height: 14px;margin-right: 5px;"></span><br />戻る
                    </vs-button>
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState"
                                v-on:click="onSaveCircularClick"><span><img
                            :src="require('@assets/images/mobile/confirm_white.svg')"
                            style="width: 17.5px;height: 14px;margin-right: 5px;"></span><br />申請
                    </vs-button>
                </vs-col>
            </vs-row>
            
        </div>
        <vs-popup class="holamundo"  title="承認ルートプレビュー" :active.sync="previewPopupActive">
            <vs-row>
                <vs-col vs-w="12"><p>XXXXX</p></vs-col>
                <vs-col vs-w="12" class="preview-content">
                    <p class="title">{{commentTitle}}</p>
                    <p class="text" v-html="commentContent"></p>
                </vs-col>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="primary" type="filled" :disabled="clickState" v-on:click="onSaveCircularClick"> 回覧先に適用</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="previewPopupActive = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
        <vs-popup class="holamundo"  title="送信先選択" :active.sync="popupSelectAccountActive">
              <p>複数の登録がある宛先です。</p>
              <p>送信先の環境を１つ選択してください。</p>
              <p class="mt-3" v-html="emailSelect?emailSelect:emailSelects"></p>
              <vs-row class="mt-3" v-if="resultCheckEmailExisting.length">
                  <vs-button class="square mb-3"
                  v-for="(account, indexAccount) in resultCheckEmailExisting" v-bind:key="indexAccount"
                  :color="arrBtnColorAccount[account.env_flg]"  type="filled"
                  v-on:click="selectAccount(account.edition_flg,account.env_flg,account.server_flg, account.company_id, account.company_name, account.name)"
                  >{{ account.system_name }}</vs-button>
              </vs-row>
              <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center" class="bdt">
                <vs-button class="square mt-3" color="#bdc3c7" type="filled" v-on:click="popupSelectAccountActive = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
        <vs-popup class="holamundo"  title="メッセージ" :active.sync="showPopupErrosNoAddress">
            <vs-row>
                <p>回覧先を省略することはできません</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosNoAddress = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>

        <vs-popup class="holamundo"  title="エラー" :active.sync="showPopupErrosTextLength">
            <vs-row>
                <p>件名が50文字超えています。</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosTextLength = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>

        <vs-popup class="holamundo"  title="エラー" :active.sync="showPopupErrosContentLength">
            <vs-row>
                <p>コメントが500文字超えています。</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosContentLength = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>

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

        <!-- 承認ルートプレビュー -->
        <vs-popup title="承認ルートプレビュー" :active.sync="showTemplateDetail" class="template_preview_dialog">
            <div>{{ templateDetail.template_name ? templateDetail.template_name : '' }}</div>
            <template v-for="(itemDetail, indexDetail) in templateDetail.template_rotes">
                <div style="display: flex;flex-direction:column;" :class="!itemDetail.template_route_valid ? 'item mt-1 template-route-ng' : 'item mt-1 template-route-ok'" :key="indexDetail">
                    <div class="item-title">
                        {{ itemDetail.department_name }} {{ itemDetail.position_name }} {{ itemDetail.option ? itemDetail.option : '' }}{{ template_modes[itemDetail.mode] }}
                    </div>
                    <div class="item-user" v-if="itemDetail.users.length <= 0">
                        該当者なし
                    </div>
                    <template v-for="(item) in itemDetail.users">
                        <div class="item-user" :key="item.id">
                            {{ item.family_name ? item.family_name : '' }} {{ item.given_name ? item.given_name : '' }} [{{ item.email }}]
                        </div>
                    </template>
                </div>
                <div class="toright" :key="'template-'+indexDetail+'-icon'" v-if="indexDetail<templateDetail.template_rotes.length-1"><i class="fas fa-caret-down"></i></div>
            </template>

            <vs-row class="mt-5">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12" style="display:block;text-align:center">
                    <vs-button @click="showTemplateDetail = false" color="primary" type="border">閉じる</vs-button>
                    <vs-button @click="selectTemplate(templateDetail.template_rotes)" v-bind:disabled="!templateDetail.template_valid" color="primary">回覧先に適用</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <!-- 宛先、回覧順に合議でないものが存在するため -->
        <vs-popup class="holamundo"  title="メッセージ" :active.sync="showPopupErrorTemplate">
            <vs-row>
                <p>宛先、回覧順に合議でないものが存在するため、<br>{{showPopupErrorTemplateMessage}}できません。</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrorTemplate = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
        <!--PAC_5-1698 S-->
        <vs-popup class="holamundo"  title="" :active.sync="planDetailShow">
            <vs-row style="flex-direction: column">
                <p>合議詳細</p>
                <div class="full-width range-0">
                    <vs-col
                        v-for="(user,index) in planDetailList" :key="index"
                        vs-w="12"
                        vs-type="flex"
                        vs-align="flex-start"
                        :class="['item child-order item-draggable']" :style="{background:'rgb(250,168,62)'}" style="margin-top: 15px" >
                        <div class="dropable-item h-full w-full plan_detail ">
                            <div class="name">{{index+1}} - {{user.name || '社員'}}</div>
                            <div class="email">【{{user.email}}】</div>
                        </div>
                    </vs-col>
                </div>
            </vs-row>
        </vs-popup>
        <!--PAC_5-1698 E-->
        <vs-popup class="template_preview_dialog"  title="お気に入り名称" :active.sync="addFavoriteFlg">
            <vs-row style="margin-bottom: 12px;width: 100%">
                <vs-col class="mt-2 w-full " vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="12" style="width:100%">
                    <span style="width:15%;">名称：</span>
                    <vx-input-group  class="mb-0  w-full">
                        <vs-input v-model="addFavoriteNameVal"  maxlength="20" />
                    </vx-input-group>
                </vs-col>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button @click="onAddFavorite()" v-bind:disabled="!addFavoriteNameVal || addFavoriteNameVal.length > 20" color="primary">登録</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="addFavoriteFlg = false;addFavoriteNameVal = ''"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    import config from "../../../app.config";
    import { CIRCULAR } from '../../../enums/circular';
    import { CIRCULAR_USER } from '../../../enums/circular_user';
    import Axios from "axios";
    //import LiquorTree from 'liquor-tree';
    import draggable from 'vuedraggable';

    import VueSuggestion from 'vue-suggestion'

    import flatPickr from 'vue-flatpickr-component'
    import 'flatpickr/dist/flatpickr.min.css';
    import {Japanese} from 'flatpickr/dist/l10n/ja.js';

    import usernameTemplate from './username-suggest-template.vue';
    import emailTemplate from './email-suggest-template.vue';
    import ContactTree from '../../../components/contacts/ContactTree';
    import { Validator } from 'vee-validate'
    import Utils from '../../../utils/utils';
    import {userService} from "../../../services/user.service";

    const dict = {
        custom: {
            subject: {
                 max: "50文字以上は入力できません。"
            },
            content: {
                max: "500文字以上は入力できません。"
            }
         }
    };
    Validator.localize('ja', dict)


    export default {
        components: {
            //[LiquorTree.name]: LiquorTree,
            draggable,
            flatPickr,
            VueSuggestion,
            ContactTree
        },
        data() {
            return {
                CIRCULAR: CIRCULAR,
                CIRCULAR_USER: CIRCULAR_USER,
                usernameTemplate: usernameTemplate,
                emailTemplate: emailTemplate,
                accessCodeFlg: false,
                outsideAccessCodeFlg: false,
                checkAuthFlg: false,
                outsideAccessCodeShowFlg: false,
                accessCode: '',
                outsideAccessCode: '',
                allowChangeDestinationFlg: false,
                showThumbnailFlg: false,
                previewPopupActive: false,
                popupSelectAccountActive: false,
                departmentUserFilter: '',
                usernameSelect: '',
                emailContent: '',
                emailSelect: '',
                emailSelects: '',
                currentEmailConfirm: 0,
                mapUserEmail:[],
                mapEnvEmail:[],
                mapEnvCompany:[],
                confirmEmails:[],
                mapConfirmEmails:[],
                userSuggestSelect: null,
                suggestDisabled: false,
                addToContactsFlg: false,
                selected: [],
                itemsPerPage: 4,
                isMounted: false,

                // Data Sidebar
                addNewDataSidebar: false,
                sidebarData: {},
                info: {},
                infoCheck: {},
                emailTemplateOptions: [],
                optionSelected: '',
                configdateTimePicker: {
                    locale: Japanese,
                    wrap: true
                },
                reNotificationDay: null,
                arrFavorite:[],
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
                resultCheckEmailExisting: [],
                arrBtnColorAccount:['primary','primary'],
                userSuggestions: [],
                emailSuggestions: [],
                userSuggestModel: '',
                emailSuggestModel: '',
                emailSuggestValidateMsg: '',
                showEmailThumbnailOption: true,
                checkShowAddress: true,
                showPopupErrosNoAddress: false,
                showPopupErrosTextLength: false,
                showPopupErrosContentLength: false,
                checkShowConfirmAddSignature: false,
                selectUsersChange: false,
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
                selectUsersDisplay: [],
                onlyInternalUser: true,
                searchAreaFlg:false,
                confirmEdit: false,
                operationNotice: false,
                operationNoticeConfirm: false,
                protectionSetting: {destination_change_flg: 0, access_code_protection:0, enable_email_thumbnail:0, text_append_flg:0, require_print:0},
                draggingUser: null,
                showPopupEditContacts: false,
                editContact:{},
                confirmDelete:false,
                confirmDuplicateEmail: false,
                listCheckEmailContact:[],
                clickState: false, //二重チェック用
                isTemplateCircular: false,
                showTree: true,
                newSelectUsers: [], // 位置スワップ後の集合
                commentTitle: '',
                commentContent: '',
                changeTimes:0,
                selectedComment: '',
                maxViewer:1, //閲覧人数上限値
                arrTemplate:[], // 合議リスト
                showTemplateDetail:false, // 詳細表示フラッグ
                isShowTemplateDetailBack:true, // 詳細表示フラッグ 連動ウィンドウに戻るかどうか
                templateDetail: {}, // 合議詳細
                template_modes: ['','全員必須','','人'], // 1:全員必須 3:人
                showPopupErrorTemplate: false, // 宛先、回覧順に合議でないものが存在するため、合議を適用できません。Popup表示用
                showPopupErrorTemplateMessage: "", // Popup表示用 メッセージ
                selectTemplateUsersDisplay: [], // 宛先表示
                templateSearchModel: '',
                myCompanyInfo: {},
                selectTemplateRoutes: [],
                tableshow:{},
                searchFavorite:'',
                addFavoriteFlg:false,
                addFavoriteNameVal:'',
                settingLimit:{},
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
                checkUserInputTimeout: null,
                checkEmailInputTimeout: null,
                userSelectedFlg: false,
                emailSelectedFlg: false,
                userViewSelectedFlg: false,
                emailViewSelectedFlg: false,
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 End
                specialCircularReceiveFlg: false,//回覧ユーザーが特設サイトの受取側ですか
                circularUserLastSendIdIsSpecial: false,//現在未操作のユーザは特設サイトの受取側ですか
                specialCircularFlg:false,//特設サイト回覧
                specialButtonDisableFlg: false,//特設サイト申請画面、ボタン非アクティブ
                groupName: '',//特設サイト受取側組織名
                is_plan:false,
                planDetailShow:false,
                planDetailList:[],
                /*PAC_5-2616 S*/
                enable_any_address:0,
                /*PAC_5-2616 E*/
                selectUsersCheck:0, //PAC_5-2705
                isSendAll: false,
            }
        },
        computed: {
            ...mapState({
                //departmentUsers: state => state.application.departmentUsers,
                files: state => state.home.files,
                companyUsers: state => state.application.companyUsers,
                circular: state => state.home.circular,
                selectUserView: state => state.application.selectUserView,
                checkOperationNotice: state => state.application.checkOperationNotice,
                //selectUsers: state => state.home.circular ? state.home.circular.users : [],
                // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
                circularChangeListUserView: state => state.home.circularChangeListUserView,
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
                    if(!this.circular && !this.circularUserLastSend) return false;
                    return (this.circular.address_change_flg || (this.circularUserLastSend.parent_send_order > 0));
                }
            },
            showSelectUsersDisplay () {
                return this.selectUsersDisplay.length > 0 ? this.selectUsersDisplay[0].filter((item,index)=>index > 0) : []
            },
            currentPage() {
                if(this.isMounted) {
                    return this.$refs.table.currentx
                }
                return 0
            },
            selectUsers: {
                get() {return  this.$store.state.home.circular ? this.$store.state.home.circular.users : []},
                set(value) {this.updateCircularUsers(value)}
            },
            loginUser: {
                get() {return JSON.parse(getLS('user'));}
            },
            selectUserView:{
                get() {
                    return this.$store.state.application.selectUserView
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
                            if(!newArrUsers.hasOwnProperty(child_send_order)){
                                newArrUsers[child_send_order] = [];
                            }
                            arrUsers[i]['user_routes_name'] = JSON.parse(arrUsers[i].detail).summary;
                            newArrUsers[child_send_order].push(arrUsers[i]);
                        }
                    }
                    return newArrUsers;
                },
            },
            companyLimit(){
                let obj={
                    text_append_flg:this.circular?this.circular.limit_text_append_flg:0
                }
                return obj;
            },
            //PAC_5-2705 S
            showCheckUser() {
              return (!this.onlyInternalUser || this.specialCircularFlg) && this.selectUsersDisplay[0].length > 1
            },
            //PAC_5-2705 E
            setSelectUsersDisplay() {
               return this.selectUsersDisplay.length > 0 ? this.selectUsersDisplay[0].filter((item,index)=> !this.isSendAll && index > 0 && !this.isTemplateCircular) : []
            },
            setSelectUsersIsDisplay () {
                return this.selectUsersDisplay.filter((item,index)=> !this.isSendAll && index > 0 )
            },
            showCheckUser() {
                return !this.isSendAll && (!this.onlyInternalUser || this.specialCircularFlg) && this.selectUsersDisplay[0].length > 1
            }
        },
        methods: {
            ...mapActions({
                getDepartmentUsers: "application/getDepartmentUsers",
                getListContact: "contacts/getListContact",
                sendNotifyFirst: "application/sendNotifyFirst",
                saveCircularSetting: "application/saveCircularSetting",
                addCircularUsers: "application/addCircularUsers",
                removeCircularUser: "application/removeCircularUser",
                clearCircularUsers: "application/clearCircularUsers",
                updateCircularUsers: "home/updateCircularUsers",
                getListFavorite     : "favorite/getList",
                addFavorite         : "favorite/add",
                removeFavorite      : "favorite/remove",
                sortFavorite        : "favorite/updateSort",
                addLogOperation: "logOperation/addLog",
                checkCircularUserNextSend: "home/checkCircularUserNextSend",
                getMyInfo: "user/getMyInfo",
                getInfoCheck: "user/getInfoCheck",
                getProtection: "setting/getProtection",
                updateOperationNotice: "application/updateOperationNotice",
                getContact: "contacts/getContact",
                updateContact: "contacts/updateContact",
                deleteContact: "contacts/deleteContact",
                getListTemplate: "templateRoute/getList",
                getLimit: "setting/getLimit",
            }),
            setSelectUserDisplayIndex(e) {
                return e.filter((user,index)=>index > 0 && user)
            },
            setUsernameSuggestLabel (item) {
                return item.name;
            },
            setEmailSuggestLabel (item) {
                return item.email;
            },
            onRemoveSelectUserClick: function(id) {
                this.removeCircularUser(id);
            },
            clearSelectUsers: function () {
                this.clearCircularUsers(this.circular.id);
            },
            setUserViewnameSuggestLabel (item) {
                return item.name;
            },
            setEmailViewSuggestLabel (item) {
                return item.email;
            },
            async changeCommentTitle() {
                 this.$store.commit('application/updateCommentTitle', this.commentTitle);
            },
            async changeCommentContent(){
                 this.toSaveCircularSetting();
                 this.$store.commit('application/updateCommentContent', this.commentContent);
            },
            // PAC_5-2245 Start
            async changeRequirePrint() {
                this.protectionSetting.require_print = !this.protectionSetting.require_print;
                this.toSaveCircularSetting();
                this.$store.commit('application/updateRequirePrint', this.protectionSetting.require_print);
            },
            // PAC_5-2245 End
            async getUsersViewSuggestionList(inputValue) {
                this.userViewnameSelect = inputValue;
                if ( inputValue){
                    const $this = this;
                    clearTimeout(this.checkUserInputTimeout);
                    this.checkUserInputTimeout = setTimeout(function () {
                        Axios.get(`${config.BASE_API_URL}/users?filter=${inputValue}`, {data: {nowait: true}})
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
                }else{
                    this.userViewSuggestions = [];
                }
            },
            getEmailsViewSuggestionList(inputValue) {
                this.emailViewSuggestValidateMsg ='';
                this.emailViewSelect = inputValue;
                if(this.userViewSuggestSelect && this.userViewSuggestSelect.email !== inputValue) {
                    this.suggestViewDisabled = false;
                }
                if ( inputValue) {
                    const $this = this;
                    clearTimeout(this.checkEmailInputTimeout);
                    this.checkEmailInputTimeout = setTimeout(function () {
                        Axios.get(`${config.BASE_API_URL}/users?filter=${inputValue}`, {data: {nowait: true}})
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
                    }, 300);
                }else{
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
            // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
            getUsersSuggestionList(inputValue) {
                this.usernameSelect = inputValue;
                this.userSuggestions = [];
                if ( inputValue){
                    const $this = this;
                    clearTimeout(this.checkUserInputTimeout);
                    this.checkUserInputTimeout = setTimeout(function () {
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
                    }, 300);
                }else{
                    this.userSuggestions = [];
                }
            },
            getEmailsSuggestionList(inputValue) {
                this.emailSuggestValidateMsg ='';
                this.emailSelect = inputValue;
                if(this.userSuggestSelect && this.userSuggestSelect.email !== inputValue) {
                    this.suggestDisabled = false;
                }
                this.emailSuggestions = [];
                if ( inputValue) {
                    const $this = this;
                    clearTimeout(this.checkEmailInputTimeout);
                    this.checkEmailInputTimeout = setTimeout(function () {
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
                    },300);
                }else{
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
            onChangeEmailTemplate(value) {
                this.selectedComment = value;
                if (this.emailContent == null){
                    this.emailContent = '';
                }
                this.emailContent = this.emailContent.concat(value);
                this.selectedComment = value +' '
            },
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
            clearSuggestionInput() {
                this.usernameSelect = '';
                this.emailSelect = '';
                this.userSuggestModel = {};
                this.emailSuggestModel = {};
                this.suggestDisabled = false;
            },
            changeTableShow(index){
                if(this.tableshow[index]){
                    delete this.$delete(this.tableshow,index)
                }else{
                    this.$set(this.tableshow,index,1);
                }
            },
            async addUserView(){
                if (this.selectUserView.length >= this.maxViewer){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `閲覧ユーザーに設定できるのは`+this.maxViewer+'名までです',
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
                const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
                if(this.emailViewSelect.match(mailPattern) === null) {
                    this.emailViewSuggestValidateMsg = 'メールアドレスが正しくありません';
                    return;
                }
                var result = await Axios.get(`${config.BASE_API_URL}/userView/checkemail/${this.emailViewSelect}`, {data: {}})
                    .then(response => {
                        return response.data ? response.data.data: [];
                    })
                    .catch(error => { return []; });
                const user = {
                    circular_id : this.circular.id,
                    parent_send_order: this.selectUsers[0].parent_send_order,
                    mst_user_id : result ? result.id : '',
                    memo: "",
                    del_flg : 0,
                    create_user : this.selectUsers[0].email,
                    update_user : this.selectUsers[0].email,
                    email: this.emailViewSelect,
                    name: this.userViewnameSelect ? this.userViewnameSelect : (result ? (result.family_name ? result.family_name + ' ' + result.given_name : '社員') : '社員'),
                    company_id: result ? (result.mst_company_id ? result.mst_company_id : result.company_id) : '',
                    user_auth: result ? result.user_auth : 0,
                    option_flg: result ? (result.option_flg ? result.option_flg : 0) : 0,
                };
                if (user.company_id == null ||(user.company_id != null && user.company_id != this.loginUser.mst_company_id)) {
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
                }else if(user.email == this.selectUsers[0].email){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `申請者は登録できません`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                }else if(this.selectUserView.find((v) => v.email === user.email)){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `既に登録されています`,
                        accept: () => {
                            this.clearViewSuggestionInput();
                        }
                    });
                }else if(this.selectUsers.find((v) => v.email === user.email && v.edition_flg == this.selectUsers[0].edition_flg && v.env_flg == this.selectUsers[0].env_flg && v.server_flg == this.selectUsers[0].server_flg)) {
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
                tmpSelectUsers.splice(this.selectUserView.findIndex((item => item.email === email)),1);
                this.$store.commit('application/updateListUserView', tmpSelectUsers);
            },

            async submitMailStepForm() {
                this.emailSuggestValidateMsg ='';
                if(!this.emailSelect) {
                    this.emailSuggestValidateMsg = '必須項目です';
                    return;
                }
                const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
                const mailPattern1 = /^[a-zA-Z0-9.!$%&@'*+/\\=?^_`{|}\[\]()"><:;~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
                if(this.emailSelect.match(mailPattern) === null && this.emailSelect.match(mailPattern1) === null) {
                    this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
                    return;
                }
                var edition_flg = null, env_flg = null, server_flg = null, company_id = null, company_name = null, user_name = null;
                var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: this.emailSelect})
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
                    .catch(error => { return []; });
              if (!result.length){
                if(this.emailSelect.match(mailPattern) === null) {
                  this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
                  return;
                }
              }
              if (result.length == 1 && result[0].user_auth == 5 && result[0].company_id != this.selectUsers[0].mst_company_id){
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
              }else{
                result = this.checkEnvironmentalSelectFlg(result);
                if(result.length > 1){
                  this.resultCheckEmailExisting = result;
                  this.popupSelectAccountActive = true;
                  return;
                }else if(result.length == 1){
                  edition_flg = result[0].edition_flg;
                  env_flg     = result[0].env_flg;
                  server_flg  = result[0].server_flg;
                  company_id  = result[0].company_id;
                  company_name  = result[0].company_name;
                  this.usernameSelect  = result[0].name;
                }

                const user = {
                  child_send_order: this.selectUsers.length,
                  email: this.emailSelect,
                  name: this.usernameSelect,
                  edition_flg: edition_flg,
                  env_flg: env_flg,
                  server_flg: server_flg,
                  company_id: company_id,
                  company_name: company_name,
                  is_maker: false
                };

                this.pushToSteps(user, this.clearSuggestionInput);
              }
            },
            async selectAccount(edition_flg, env_flg, server_flg, company_id, company_name, user_name){
                this.popupSelectAccountActive = false;
                this.usernameSelect = user_name;
                if (this.confirmEmails.length > 0){
                    this.mapUserEmail[this.confirmEmails[this.currentEmailConfirm]].edition_flg = edition_flg;
                    this.mapUserEmail[this.confirmEmails[this.currentEmailConfirm]].env_flg = env_flg;
                    this.mapUserEmail[this.confirmEmails[this.currentEmailConfirm]].server_flg = server_flg;
                    this.mapUserEmail[this.confirmEmails[this.currentEmailConfirm]].company_id = this.mapEnvEmail[edition_flg + '#' + env_flg + '#' + server_flg + '#' + this.confirmEmails[this.currentEmailConfirm]];
                    this.mapUserEmail[this.confirmEmails[this.currentEmailConfirm]].company_name = this.mapEnvCompany[edition_flg + '#' + env_flg + '#' + server_flg + '#' + this.confirmEmails[this.currentEmailConfirm]];
                    this.mapUserEmail[this.confirmEmails[this.currentEmailConfirm]].name = user_name;
                    if (this.currentEmailConfirm < this.confirmEmails.length - 1){
                        this.currentEmailConfirm++;
                        this.emailSelects = this.confirmEmails[this.currentEmailConfirm];
                        this.resultCheckEmailExisting = this.mapConfirmEmails[this.confirmEmails[this.currentEmailConfirm]];
                        this.popupSelectAccountActive = true;
                        return;
                    }else{
                        var entries = Object.entries(this.mapUserEmail);
                        var users2Add = [];
                        const iterable = () => {
                            const item = entries.shift();
                            if(!item) {
                                if (users2Add.length > 0){
                                    const data = {
                                        users: users2Add,
                                    };
                                    this.addCircularUsers(data);

                                    this.emailSelects = '';
                                    this.confirmEmails = [];
                                    this.currentEmailConfirm = 0;
                                    this.mapConfirmEmails = [];
                                    this.clearSuggestionInput();
                                }
                                return;
                            }
                            this.pushMultipleToSteps(item[1], (user)=>{
                                users2Add.push(user);
                                iterable();
                            },()=>{
                                iterable();
                            });
                        }
                        iterable();
                    }
                }else{
                    const user = {
                        child_send_order: this.selectUsers.length,
                        email: this.emailSelect,
                        name: this.usernameSelect,
                        edition_flg: edition_flg,
                        env_flg: env_flg,
                        server_flg: server_flg,
                        company_id: company_id,
                        company_name: company_name,
                        is_maker: false
                    };
                    this.pushToSteps(user, this.clearSuggestionInput);
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

                const processContact = (contacts) => {
                    let groups = {};
                    var id = 0;
                    contacts.forEach((contact, stt) => {
                        if (!contact.group_name) contact.group_name = 'グループなし';
                        if(!groups[contact.group_name]) groups[contact.group_name] = [];
                        contact.family_name = contact.name;
                        contact.given_name = "";
                        groups[contact.group_name].push({id:id,
                            text: contact.name, data: contact
                        });
                        id++;
                    });
                    contacts = [];
                    for(let group_name in groups){
                        contacts.push({text: group_name, children: groups[group_name], data: {isGroup: true}});
                    }
                    return contacts;
                }

                let listContact = "";
                if(!this.checkShowAddress){
                    listContact = await this.getListContact({filter: '', type: 0});
                    listContact = processContact(listContact);
                    if(!listContact) return false;
                }

                let listContactCommon = await this.getListContact({filter: '', type: 1});
                if(!listContactCommon) return false;

                listContactCommon = processContact(listContactCommon);

                let arrAddressTree = null;

                if(listContact){
                    arrAddressTree = [
                        {text:'個人', children: listContact, data: {isGroup: true}},
                    {text:'共通', children: listContactCommon, data: {isGroup: true}},
                    {text:'部署', children: departments, data: {isGroup: true}},
                ];
                }else{
                    arrAddressTree = [
                        {text:'共通', children: listContactCommon, data: {isGroup: true}},
                        {text:'部署', children: departments, data: {isGroup: true}},
                    ];
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


                return true;
            },
            onTreeAddToStepClick: async function(userChecked) {
                // 現在の回覧は合議です、 アドレス帳から追加できません
                if(this.isTemplateCircular){
                    this.confirmEdit = false;
                    // 宛先、回覧順に合議でないものが存在するため、アドレス帳から追加できません。
                    this.showPopupErrorTemplateMessage = "アドレス帳から追加";
                    this.showPopupErrorTemplate = true;
                    return false;
                }

                this.confirmEdit = false;
                if (userChecked.length){
                    var selectedEmails = [];
                    this.mapUserEmail = {};
                    // PAC_5-1599 バグ処理 Start
                    let mapUserEmailRepeat = {};
                    // PAC_5-1599 バグ処理 End
                    this.mapEnvEmail = {};
                    this.mapEnvCompany = {};
                    let validatePlan=userChecked.some(user=>{
                        if (user.is_plan == 1 && userChecked.length < 2){
                            return true
                        }
                    })
                    if (validatePlan){
                        this.$vs.dialog({
                            type: 'alert',
                            color: 'danger',
                            title: `メッセージ`,
                            acceptText: '閉じる',
                            text: `二人以上を選択してください`,
                        });
                        return false;
                    }
                    for(var i =0; i <userChecked.length; i++){
                        selectedEmails.push(userChecked[i].email);
                        this.mapUserEmail[userChecked[i].email] = userChecked[i];
                        // PAC_5-1599 バグ処理 Start
                        if (this.mapUserEmail[userChecked[i].email]) {
                            if (!mapUserEmailRepeat[userChecked[i].email]) mapUserEmailRepeat[userChecked[i].email] = {};
                            mapUserEmailRepeat[userChecked[i].email][i] = userChecked[i];
                        } else {
                            this.mapUserEmail[userChecked[i].email] = userChecked[i];
                        }
                        // PAC_5-1599 バグ処理 End
                    }
                    var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: selectedEmails})
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
                        .catch(error => { return []; });
                    result = this.checkEnvironmentalSelectFlg(result);
                    this.confirmEmails = [];
                    this.mapConfirmEmails = [];
                    for(var i=0; i< result.length; i++){
                        this.mapEnvEmail[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_id;
                        this.mapEnvCompany[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_name;
                        if (this.mapUserEmail[result[i].email] && this.mapUserEmail[result[i].email].company_id){
                            if (this.mapConfirmEmails[result[i].email] == null){
                                this.confirmEmails.push(result[i].email);
                                this.mapConfirmEmails[result[i].email] = [];
                                this.mapConfirmEmails[result[i].email].push({env_flg: this.mapUserEmail[result[i].email].env_flg, edition_flg:this.mapUserEmail[result[i].email].edition_flg, server_flg:this.mapUserEmail[result[i].email].server_flg,
                                    company_id:this.mapUserEmail[result[i].email].company_id, company_name:this.mapUserEmail[result[i].email].company_name,
                                    name:this.mapUserEmail[result[i].email].name, system_name:this.mapUserEmail[result[i].email].system_name});
                            }
                            this.mapConfirmEmails[result[i].email].push({env_flg: result[i].env_flg, edition_flg:result[i].edition_flg, server_flg:result[i].server_flg, company_id:result[i].company_id, company_name:result[i].company_name,
                              name:result[i].name, system_name:result[i].system_name});

                        }else{
                            this.mapUserEmail[result[i].email].edition_flg = result[i].edition_flg;
                            this.mapUserEmail[result[i].email].env_flg = result[i].env_flg;
                            this.mapUserEmail[result[i].email].server_flg = result[i].server_flg;
                            this.mapUserEmail[result[i].email].company_id = result[i].company_id;
                            this.mapUserEmail[result[i].email].company_name = result[i].company_name;
                            this.mapUserEmail[result[i].email].name = result[i].name;
                            this.mapUserEmail[result[i].email].system_name = result[i].system_name;

                            // PAC_5-1599 バグ処理 Start
                            if (mapUserEmailRepeat[result[i].email]) {
                                for (let index in mapUserEmailRepeat[result[i].email]) {
                                    mapUserEmailRepeat[result[i].email][index].edition_flg = result[i].edition_flg;
                                    mapUserEmailRepeat[result[i].email][index].env_flg = result[i].env_flg;
                                    mapUserEmailRepeat[result[i].email][index].server_flg = result[i].server_flg;
                                    mapUserEmailRepeat[result[i].email][index].company_id = result[i].company_id;
                                    mapUserEmailRepeat[result[i].email][index].company_name = result[i].company_name;
                                    mapUserEmailRepeat[result[i].email][index].name = result[i].name;
                                    mapUserEmailRepeat[result[i].email][index].system_name = result[i].system_name;
                                }
                            }
                            // PAC_5-1599 バグ処理 End
                        }
                    }
                    if(this.confirmEmails.length > 0){
                        this.currentEmailConfirm = 0;
                        this.emailSelects = this.confirmEmails[this.currentEmailConfirm];
                        this.resultCheckEmailExisting = this.mapConfirmEmails[this.confirmEmails[this.currentEmailConfirm]];
                        this.popupSelectAccountActive = true;
                        return;
                    }

                    let arrSelectUsers = Object.assign([],this.$store.state.home.circular.users);
                    let hasOther = arrSelectUsers.some(user=>{
                        return user.mst_company_id==null || user.mst_company_id!=this.loginUser.mst_company_id
                    })
                    let validatePlanWithOthre=userChecked.some(user=>{
                        return (user.company_id==null || user.company_id!=this.loginUser.mst_company_id)&&user.is_plan>0
                    })
                    if(validatePlanWithOthre){
                        this.$vs.dialog({
                            type: 'alert',
                            color: 'danger',
                            title: `メッセージ`,
                            acceptText: '閉じる',
                            text: `他社利用者を合議先に追加することはできません`,
                        });
                        return false
                    }
                    /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ call function to validate multiple circular user before call api one time */
                    var users2Add = [];
                    const iterable = () => {
                        const item = userChecked.shift();
                        if(!item) {
                            if (users2Add.length > 0){
                                const data = {
                                    users: users2Add,
                                };
                                this.$nextTick(_=>{
                                  this.addCircularUsers(data);
                                })
                                this.$forceUpdate();
                                //PAC_5-1565 アドレス帳追加時に、閲覧ユーザー削除処理が走らない
                                data.users.forEach(function(user){
                                    if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                                        this.deleteUserView(user.email);
                                    }
                                },this);
                            }
                            return;
                        }
                        this.pushMultipleToSteps(item, (user)=>{
                            users2Add.push(user);
                            iterable();
                        },()=>{
                            iterable();
                        });
                    }
                    iterable();
                }
            } ,
            checkEnvironmentalSelectFlg(arrAccount) {
                const limit = JSON.parse(getLS('limit'));
                let uniqs = null;
                if(arrAccount && arrAccount.length > 0) {
                    if(limit && limit.environmental_selection_dialog == 0){
                        uniqs = arrAccount.filter((user) => {
                            if(user.env_flg !== this.selectUsers[0].env_flg || user.edition_flg !== this.selectUsers[0].edition_flg || user.server_flg !== this.selectUsers[0].server_flg) {
                                return !arrAccount.some(dupUser => dupUser.email.toLowerCase() == user.email.toLowerCase() && dupUser.env_flg === this.selectUsers[0].env_flg && dupUser.edition_flg === this.selectUsers[0].edition_flg && dupUser.server_flg === this.selectUsers[0].server_flg);
                            }
                            return user.env_flg === this.selectUsers[0].env_flg && user.edition_flg === this.selectUsers[0].edition_flg && user.server_flg === this.selectUsers[0].server_flg;
                        });
                    }
                }
                return uniqs || arrAccount;
            },
            pushToSteps: async function (user, callback) {
                if(this.loginUser && this.loginUser.isGuestCompany) {
                    if((user.company_id != this.loginUser.mst_company_id || user.env_flg != config.APP_SERVER_ENV || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != config.APP_SERVER_FLG)
                        && (user.company_id != this.loginUser.parent_company_id || user.env_flg != this.loginUser.host_app_env || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != this.loginUser.host_contract_server)) {
                        this.$vs.dialog({
                            type: 'alert',
                            color: 'danger',
                            title: `メッセージ`,
                            acceptText: '閉じる',
                            text: `回覧先の設定が誤っています`,
                        });
                        accept: () => {
                            callback();
                        }
                        return Promise.resolve(true);
                    }
                }

                const isTotallyConfidenceFiles = this.files.every(file => file.confidential_flg);
                const isInternalUser =
                    (
                        this.selectUsers[0].mst_company_id === user.company_id &&
                        this.selectUsers[0].env_flg === user.env_flg &&
                        this.selectUsers[0].edition_flg === user.edition_flg &&
                        this.selectUsers[0].server_flg === user.server_flg
                    );

                if (isTotallyConfidenceFiles && !isInternalUser) {
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `別企業のユーザーは設定できません`,
                        accept: () => {
                            callback();
                        }
                    });
                }
                if (isTotallyConfidenceFiles && isInternalUser || !isTotallyConfidenceFiles) {
                    //const checkEmail = this.selectUsers.findIndex(item => item.email === user.email);
                    const checkEmail = this.selectUsersDisplay[0].findIndex(item => item.email === user.email);
                    const data = {
                        users: [user],
                    };

                    if (checkEmail >= 0 || this.loginUser.email === user.email) {
                        this.$vs.dialog({
                            type: 'confirm',
                            color: 'primary',
                            title: `確認`,
                            acceptText: 'はい',
                            cancelText: 'いいえ',
                            text: `すでに回覧先に指定されているメールアドレスですが、よろしいですか？ ${user.email}`,
                            accept: async () => {
                                await this.addCircularUsers(data);
                                this.$forceUpdate();
                                if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                                    this.deleteUserView(user.email);
                                }
                                callback();
                            },
                            cancel: async () => {
                                callback();
                            }
                        });

                    //    return Promise.resolve(true);
                    } else {
                        await this.addCircularUsers(data);
                        this.$forceUpdate();
                        if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                            //remove users with the same email address as the approver
                            this.deleteUserView(user.email);
                        }
                        callback();
                    //    return Promise.resolve(true);
                    }
                }

            },
            /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ function to validate multiple circular user before call api */
            pushMultipleToSteps: async function (user, callbackAccept, callbackReject) {
                if(this.loginUser && this.loginUser.isGuestCompany) {
                    if((user.company_id != this.loginUser.mst_company_id || user.env_flg != config.APP_SERVER_ENV || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != config.APP_SERVER_FLG)
                        && (user.company_id != this.loginUser.parent_company_id || user.env_flg != this.loginUser.host_app_env || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != this.loginUser.host_contract_server)) {
                        this.$vs.dialog({
                            type: 'alert',
                            color: 'danger',
                            title: `メッセージ`,
                            acceptText: '閉じる',
                            text: `回覧先の設定が誤っています`,
                        });
                        accept: () => {
                            callbackReject();
                        }
                        return Promise.resolve(true);
                    }
                }
                const isTotallyConfidenceFiles = this.files.every(file => file.confidential_flg);
                const isInternalUser =
                    (
                        this.selectUsers[0].mst_company_id === user.company_id &&
                        this.selectUsers[0].env_flg === user.env_flg &&
                        this.selectUsers[0].edition_flg === user.edition_flg &&
                        this.selectUsers[0].server_flg === user.server_flg
                    );

                if (isTotallyConfidenceFiles && !isInternalUser) {
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `別企業のユーザーは設定できません`,
                        accept: () => {
                            callbackReject();
                        }
                    });
                }
                if (isTotallyConfidenceFiles && isInternalUser || !isTotallyConfidenceFiles) {
                    const checkEmail = this.selectUsers.findIndex(item => item.email === user.email);
                    if (checkEmail >= 0 || this.loginUser.email === user.email) {
                        this.$vs.dialog({
                            type: 'confirm',
                            color: 'primary',
                            title: `確認`,
                            acceptText: 'はい',
                            cancelText: 'いいえ',
                            text: `すでに回覧先に指定されているメールアドレスですが、よろしいですか？ ${user.email}`,
                            accept: async () => {
                                callbackAccept(user);
                            },
                            cancel: async () => {
                                callbackReject();
                            }
                        });

                    //    return Promise.resolve(true);
                    } else {
                        callbackAccept(user);
                    //    return Promise.resolve(true);
                    }
                }

            },
            changeAccessCodeFlg: function(){
                this.accessCodeFlg = !this.accessCodeFlg;
                if(this.accessCodeFlg && (this.accessCode == '' || this.accessCode == null)){
                    this.generateAccessCode();
                }else{
                    this.toSaveCircularSetting();
                }
            },
            changeOutsideAccessCodeFlg: function(){
                this.outsideAccessCodeFlg = !this.outsideAccessCodeFlg;
                if(this.outsideAccessCodeFlg && (this.outsideAccessCode == '' || this.outsideAccessCode == null)){
                    this.generateAccessCodeOutside();
                }else{
                    this.toSaveCircularSetting();
                }
            },
            changeDestinationFlg() {
                this.allowChangeDestinationFlg = !this.allowChangeDestinationFlg;
                this.toSaveCircularSetting();
            },
            changTextAppendFlg() {
                this.protectionSetting.text_append_flg = !this.protectionSetting.text_append_flg;
                this.toSaveCircularSetting();
            },
            changShowThumbnailFlg() {
                this.showThumbnailFlg = !this.showThumbnailFlg;
                this.toSaveCircularSetting();
            },
            toChangeCommentTitle() {
                this.toSaveCircularSetting();
                this.changeCommentTitle();
            },
            toSaveCircularSetting : async function() {
                if(this.commentTitle.length > 50){
                    this.showPopupErrosTextLength = true;
                    return false;
                }
                if(this.commentContent.length > 500){
                    this.showPopupErrosContentLength = true;
                    return false;
                }
                const  data = {
                    title: this.commentTitle,
                    text: this.commentContent,
                    address_change_flg: this.allowChangeDestinationFlg,
                    access_code_flg: this.accessCodeFlg,
                    access_code: this.accessCodeFlg ? this.accessCode : '',
                    outside_access_code_flg: this.outsideAccessCodeFlg,
                    outside_access_code: this.outsideAccessCodeFlg ? this.outsideAccessCode : '',
                    hide_thumbnail_flg: !this.showThumbnailFlg,
                    re_notification_day: this.reNotificationDay ? this.$moment(this.reNotificationDay).format('YYYY-MM-DD'): null,
                    text_append_flg: this.protectionSetting.text_append_flg,
                    require_print: this.protectionSetting.require_print,
                };
                await this.saveCircularSetting(data);
            },
            doSendNotifyFirst: async function() {
                this.$modal.hide('confirm-sent-modal');
                this.$modal.hide('confirm-no-comment-modal');

                const mail_to = this.selectUsers.length > 1 ? this.selectUsers[1].email : '';
                // 最初回覧文書取得
                let circular_document_id = 0;
                this.$store.state.home.files.forEach(function (item){
                  if(circular_document_id == 0 || circular_document_id > item.circular_document_id){
                    circular_document_id = item.circular_document_id;
                  }
                });
                const  data = {
                    userViews : this.selectUserView,
                    mail_to: mail_to,
                    title: this.commentTitle,
                    text: this.commentContent,
                    address_change_flg: this.allowChangeDestinationFlg,
                    filename:  this.$store.state.home.fileSelected.name,
                    access_code_flg: this.accessCodeFlg,
                    access_code: this.accessCodeFlg ? this.accessCode : '',
                    outside_access_code_flg: this.outsideAccessCodeShowFlg ?　this.outsideAccessCodeFlg : 0,
                    outside_access_code: this.outsideAccessCodeShowFlg && this.outsideAccessCodeFlg ? this.outsideAccessCode : '',
                    hide_thumbnail_flg: !this.showThumbnailFlg,
                    addToContactsFlg: this.addToContactsFlg,
                    // addToCommentsFlg: this.addToCommentsFlg,
                    operationNotice: this.operationNotice? this.operationNotice :this.operationNoticeConfirm,
                    re_notification_day: this.reNotificationDay ? this.$moment(this.reNotificationDay).format('YYYY-MM-DD'): null,
                    circular_document_id: circular_document_id,
                    circular_id: this.circular.id,
                    text_append_flg: this.protectionSetting.text_append_flg,
                    require_print: this.protectionSetting.require_print,
                    specialFile: this.specialCircularFlg,
                    user:this.loginUser.id,
                };
                if (!this.protectionSetting.protection_setting_change_flg) {
                    data.address_change_flg = this.protectionSetting.destination_change_flg;
                    data.access_code_flg = this.protectionSetting.access_code_protection;
                    data.access_code = this.protectionSetting.access_code_protection ? this.accessCode : '';
                    data.hide_thumbnail_flg = !this.protectionSetting.enable_email_thumbnail;
                }
                await this.sendNotifyFirst(data).then(ret => {
                    if(ret && this.info && !this.info.operation_notice_flg == 1) {

                        let $notifySuccess = $(`
                                                <div id="send_success">
                                                    <div class="background"></div>
                                                    <div class="info">
                                                      <div class="icon"><i style="font-size: 100px;" class="fa fa-check"></i></div><div class="text">申請しました</div>
                                                    </div>
                                                </div>
                                              `);

                        $notifySuccess.appendTo('body');

                        setTimeout( ()=> {
                          $notifySuccess.remove();
                          this.$store.dispatch('home/clearState', null);
                          this.$router.push('/');
                        }, 2000)

                        
                    }
                });
                if(this.checkOperationNotice && this.info.operation_notice_flg){
                    await this.$modal.show('confirm-sent-modal');
                }
            },
            /*showOperationNoticeFlg: async function() {
                this.$modal.show('confirm-no-comment-modal');
            },*/

            onSaveCircularClick: async function() {
              
                /*PAC_5-1698 合議 submit validate*/
                let validatePlan=this.selectUsersDisplay.some((select)=>{
                    return select.some(user=>{
                        if(user.plan_id>0&&(user.plan_mode==null||(user.plan_mode==3&&(!user.score||user.score<1||user.score>user.plan_users.length)))){
                            return true
                        }
                    })
                })
                if (validatePlan){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `合議設定をしてください`,
                    });
                    return false
                }
                 // 二重チェック
                 this.clickState = true;
                 const isTotallyConfidenceFiles = this.files.every(file => file.confidential_flg);
                const isInternalUser = this.selectUsers.every(user => (user.mst_company_id === this.selectUsers[0].mst_company_id && user.env_flg === this.selectUsers[0].env_flg && user.edition_flg === this.selectUsers[0].edition_flg && user.server_flg === this.selectUsers[0].server_flg));

                if(this.commentTitle.length > 50){
                    this.showPopupErrosTextLength = true;
                    this.clickState = false;
                    return false;
                }

                if(this.commentContent.length > 500){
                    this.showPopupErrosContentLength = true
                    this.clickState = false
                    return false
                }

                // PAC_5-1973 Start
                this.commentTitle = this.commentTitle.replace(/[\v|\t]/g,'');
                this.changeCommentTitle();
                // PAC_5-1973 End
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
                }else{
                     if(this.loginUser && this.loginUser.isGuestCompany) {
                        let lastUser = this.selectUsers[this.selectUsers.length-1];
                       　// 位置スワップ後の集合にデータがあるの場合
                        if (this.newSelectUsers.length > 0) {
                          lastUser = this.newSelectUsers[this.newSelectUsers.length-1];
                        }

                         if((lastUser.mst_company_id !== this.loginUser.parent_company_id || lastUser.env_flg !== this.loginUser.host_app_env || lastUser.server_flg !== this.loginUser.host_contract_server)) {
                            this.$vs.dialog({
                                type: 'alert',
                                color: 'danger',
                                title: `メッセージ`,
                                acceptText: '閉じる',
                                text: `回覧先の設定が誤っています`,
                            });
                            this.clickState = false;
                            return false;
                        }
                    }
                    //get index circularUser present
                    var company = await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                        .then(response => {
                            return response.data ? response.data.data: [];
                        })
                        .catch(error => {
                          this.clickState = false;
                          return []; });

                    if(company.esigned_flg == 1){
                        this.$store.commit('home/checkCircularUserNextSend', true);
                        if(company.stamp_flg == 1){
                            this.checkShowConfirmAddSignature = true;
                        }else{
                            this.checkShowConfirmAddSignature = false;
                        }
                    }else{
                        this.checkShowConfirmAddSignature = false;
                        this.$store.commit('home/checkCircularUserNextSend', false);
                    }
                    if(this.$store.state.home.circular.users.length <=1  && !this.specialCircularFlg){
                        this.showPopupErrosNoAddress = true;
                    }else{
                        if (this.info.operation_notice_flg){
                            this.$modal.show('confirm-no-comment-modal');
                        }else{
                            await this.doSendNotifyFirst();
                        }
                    }

                }
              this.clickState = false;
            },
            onUpdateOperationNoticeClick: async function() {
                this.updateOperationNotice({operationNotice: this.operationNotice? this.operationNotice :this.operationNoticeConfirm}).then(ret => {
                    if(ret) {
                        this.$store.dispatch('home/clearState', null);
                        this.$router.push('/');
                    }
                });
            },
            generateAccessCode: function() {
                // this.accessCode = Math.floor(Math.random() * (999999 - 100000 + 1) + 100000);
                this.accessCode = this.getAccessCode(6);
            },
            generateAccessCodeOutside: function() {
                this.outsideAccessCode = this.getAccessCode(6);
            },
            goBack: function() {
                if(this.circular && this.selectUserView ){
                    this.$store.commit('home/updateCircularChangeListUserView',{id:this.circular.id,data:this.selectUserView});
                }
                
                this.$router.push(`/create/${this.circular.id}`);
                return false;
            },
            startDrag(user, event){
                let target = event.target;
                setTimeout(function(){
                    $(target).addClass('item-dragging');
                    $('.drop-area').removeClass('hidden');
                }, 5);

                this.draggingUser = user;
            },
            endDrag(event){
                $(event.target).removeClass('item-dragging');
                $('.drop-area').addClass('hidden');
            },
            overDrag(user, userChildSendOrder, isHolder, event){
                if (this.draggingUser){
                    if (user != null){
                        let userId = this.draggingUser.id;
                        let envFlg = this.draggingUser.env_flg;
                        let editionFlg = this.draggingUser.edition_flg;
                        let serverFlg = this.draggingUser.server_flg;
                        let companyId = this.draggingUser.mst_company_id;
                        let parent_send_order = this.draggingUser.parent_send_order;
                        let child_send_order = this.draggingUser.child_send_order;

                        if (user.id == userId && !isHolder){
                            event.dataTransfer.dropEffect  = "none";
                            return;
                        }
                        if (user.env_flg != envFlg || user.edition_flg != editionFlg || user.server_flg != serverFlg || !user.mst_company_id || user.mst_company_id != companyId || (!user.mst_company_id && !companyId)){
                            let contactUser = (parent_send_order != 0 && child_send_order == 1);
                            if (!contactUser  && (userChildSendOrder != 1 || Math.abs(user.parent_send_order - parent_send_order) == 1)){
                                event.dataTransfer.dropEffect  = "none";
                                return;
                            }
                            if (contactUser  && userChildSendOrder != 1){
                                event.dataTransfer.dropEffect  = "none";
                                return;
                            }
                        }else{
                            if (parent_send_order == user.parent_send_order && isHolder && Math.abs(userChildSendOrder - child_send_order) == 1){
                                event.dataTransfer.dropEffect  = "none";
                                return;
                            }
                        }
                    }
                    event.preventDefault();
                }else{
                    event.dataTransfer.dropEffect  = "none";
                }
            },
            onDrop(user, userChildSendOrder, isHolder, event){
                if (this.draggingUser){
                    const newSelectUsers = [];
                    let arrSelectUsers = this.$store.state.home.circular.users;
                    if (user != null && !isHolder){
                        for(var i = 0; i < arrSelectUsers.length; i++){
                            if (arrSelectUsers[i].id == user.id){
                                newSelectUsers.push(this.draggingUser);
                            }else if (arrSelectUsers[i].id == this.draggingUser.id){
                                newSelectUsers.push(user);
                            }else{
                                newSelectUsers.push(arrSelectUsers[i]);
                            }
                        }
                    }else if (user == null){
                        for(var i = 0; i < arrSelectUsers.length; i++){
                            if (arrSelectUsers[i].id == this.draggingUser.id){
                                continue;
                            }else{
                                newSelectUsers.push(arrSelectUsers[i]);
                            }
                        }

                        newSelectUsers.push(this.draggingUser);
                    }else{
                        for(var i = 0; i < arrSelectUsers.length; i++){
                            if (arrSelectUsers[i].id == this.draggingUser.id){
                                continue;
                            }
                            newSelectUsers.push(arrSelectUsers[i]);

                            if (user.parent_send_order == arrSelectUsers[i].parent_send_order && (userChildSendOrder - arrSelectUsers[i].child_send_order == 1)){
                                newSelectUsers.push(this.draggingUser);
                            }
                        }
                    }
                    // 位置スワップ後の集合を設定
                    this.newSelectUsers = newSelectUsers;
                    return this.updateCircularUsers(newSelectUsers);
                }
            },
            onShowAddFavorite(){
                this.addFavoriteFlg = true;
            },
            async onAddFavorite(){
                // 現在の回覧は合議です
                if(this.isTemplateCircular){
                    // 宛先、回覧順に合議でないものが存在するため、お気に入りに登録できません。
                    this.showPopupErrorTemplateMessage = "お気に入りに登録";
                    this.showPopupErrorTemplate = true;
                    return false;
                }

                let str_key = "";

                // get list user
                let items = this.circular.users.map((user, index) => {
                    if(index!=0) str_key += user.email;
                    /*PAC_5-1698 合議 favorite*/
                    return { favorite_name:this.addFavoriteNameVal,name: user.name, email: user.email, email_company_id: user.mst_company_id, email_company_name: user.mst_company_name, email_user_id: user.mst_user_id, email_env_flg: user.env_flg, email_edition_flg: user.edition_flg, email_server_flg: user.server_flg, child_send_order:this.is_plan
                            ?user.child_send_order:0, parent_send_order:this.is_plan
                            ?user.parent_send_order:0 ,plan_id:user.plan_id }
                });
                items.shift();
                // check exits
                for(let i in this.arrFavorite){
                    let favorites = this.arrFavorite[i],
                        str_check = '';
                    for(let j in favorites){
                        str_check += favorites[j].email;
                    }
                    if(str_key == str_check) return;
                }
                this.addFavoriteFlg = false;
                this.addFavoriteNameVal = ''
                await this.addFavorite({items});
                this.searchFavorite = '';
                this.arrFavorite = await this.onSearchFavorite();
            },
            async onRemoveFavorite(favorite_no){
                await this.removeFavorite(favorite_no);
                this.arrFavorite = await this.onSearchFavorite();
            },
            onSortFavorite(){
                let arrSort = this.arrFavorite.map((favotites) => {
                    let ids=[]
                     favotites.map((favotite) => {
                        if(favotite.plan_users && favotite.plan_users.length>0){
                            favotite.plan_users.forEach(v=>{
                                ids.push(v.id)
                            })
                        }else{
                            ids.push(favotite.id)
                        }
                    });
                    return ids
                });
                this.sortFavorite({'sorts': arrSort}).then(()=>{
                  this.onSearchFavorite();
                });
            },
            async onDepartmentUsersSelect(){
                await this.getDepartmentUsers({filter: ''});
            },
            async onTemplateSelect(){
                // PAC_5-2027 承認ルートの関係部署表示問題修正 Start
                let info = {
                    templateRouteName : this.templateSearchModel,
                    template_route_flg : this.info.template_route_flg === undefined ? 0 : this.info.template_route_flg,
                };
                this.arrTemplate = await this.getListTemplate(info);
                // PAC_5-2027 End
                return this.arrTemplate;
            },
            async onSearchFavorite(){
                //this.arrFavorite = await this.getListFavorite({favorite_name:this.searchFavorite});
                /*PAC_5-1698*/
                let res = await this.getListFavorite({favorite_name:this.searchFavorite});
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
            async onApplyFavorite(itemFavorite){
                $(".application-page-dialog .vs-popup--close").click();
                // 現在の回覧は合議です
                if(this.isTemplateCircular){
                    this.confirmEdit = false;
                    // 宛先、回覧順に合議でないものが存在するため、回覧順に追加できません。
                    this.showPopupErrorTemplateMessage = "回覧順に追加";
                    this.showPopupErrorTemplate = true;
                    return false;
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
                for (const v of itemFavorite) {
                    if (v.plan_users){
                        v.plan_users.forEach(user=>{
                            user.is_plan=1
                        })
                        await this.onApplyFavoriteNext(v.plan_users)
                    }else{
                        v.is_plan=0
                        await this.onApplyFavoriteNext([v])
                    }
                }


            },
            async onApplyFavoriteNext(itemFavorite){
                let arrApply = itemFavorite.map((item) => {
                    return {
                        child_send_order: this.selectUsers.length,
                        email: item.email,
                        name: item.name,
                        is_maker: false,
                        edition_flg: item.email_edition_flg,
                        env_flg: item.email_env_flg,
                        server_flg: item.email_server_flg,
                        company_id: item.email_company_id,
                        company_name: item.email_company_name,
                        is_plan:item.is_plan
                    };
                });


                if (arrApply.length){
                    var selectedEmails = [];
                    this.mapUserEmail = {};
                    this.mapEnvEmail = {};
                    this.mapEnvCompany = {};
                    let newFavorite = false;
                    for(var i =0; i <arrApply.length; i++){
                        if (arrApply[i].edition_flg !== null && arrApply[i].env_flg !== null && arrApply[i].server_flg !== null) {
                            newFavorite = true;
                            break;
                        }
                        selectedEmails.push(arrApply[i].email);
                        this.mapUserEmail[arrApply[i].email] = arrApply[i];
                    }
                    if (!newFavorite){
                        var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: selectedEmails})
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
                            .catch(error => { return []; });
                        result = this.checkEnvironmentalSelectFlg(result);
                        this.confirmEmails = [];
                        this.mapConfirmEmails = [];
                        for(var i=0; i< result.length; i++){
                            this.mapEnvEmail[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_id;
                            this.mapEnvCompany[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_name;
                            if (this.mapUserEmail[result[i].email] && this.mapUserEmail[result[i].email].company_id){
                                if (this.mapConfirmEmails[result[i].email] == null){
                                    this.mapConfirmEmails[result[i].email] = [];
                                    this.mapConfirmEmails[result[i].email].push({env_flg: this.mapUserEmail[result[i].email].env_flg, edition_flg:this.mapUserEmail[result[i].email].edition_flg, server_flg:this.mapUserEmail[result[i].email].server_flg,
                                        company_id:this.mapUserEmail[result[i].email].company_id, company_name:this.mapUserEmail[result[i].email].company_name});
                                }
                                this.mapConfirmEmails[result[i].email].push({env_flg: result[i].env_flg, edition_flg:result[i].edition_flg, server_flg:result[i].server_flg, company_id:result[i].company_id, company_name:result[i].company_name});
                                this.confirmEmails.push(result[i].email);
                            }else{
                                this.mapUserEmail[result[i].email].edition_flg = result[i].edition_flg;
                                this.mapUserEmail[result[i].email].env_flg = result[i].env_flg;
                                this.mapUserEmail[result[i].email].server_flg = result[i].server_flg;
                                this.mapUserEmail[result[i].email].company_id = result[i].company_id;
                                this.mapUserEmail[result[i].email].company_name= result[i].company_name;
                            }
                        }
                        if(this.confirmEmails.length > 0){
                            this.currentEmailConfirm = 0;
                            this.emailSelects = this.confirmEmails[this.currentEmailConfirm];
                            this.resultCheckEmailExisting = this.mapConfirmEmails[this.confirmEmails[this.currentEmailConfirm]];
                            this.popupSelectAccountActive = true;
                            return;
                        }
                    }

                    /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ call function to validate multiple circular user before call api one time */
                    var users2Add = [];
                    const iterable = (resolve) => {
                        const item = arrApply.shift();
                        if(!item) {
                            if (users2Add.length > 0){
                                const data = {
                                    users: users2Add,
                                };
                                this.addCircularUsers(data).then(res=> {
                                    let planUsers=[];
                                     res.map(user=>{
                                         if (user.plan_id>0){
                                             planUsers.push(JSON.parse(JSON.stringify(user)));
                                         }
                                     })
                                    if (planUsers.length>0){
                                        let obj=JSON.parse(JSON.stringify(planUsers[0]))
                                        obj.plan_mode=itemFavorite[0].mode
                                        obj.score=itemFavorite[0].score
                                        obj.plan_users=planUsers
                                        this.updatePlan(obj).then(res=>this.buildSelectUsersDisplay())
                                    }
                                }).then(res=>{
                                    this.$forceUpdate();
                                    //PAC_5-1565 お気に入り追加時に、閲覧ユーザー削除処理が走らない
                                    data.users.forEach(function(user){
                                        if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                                            this.deleteUserView(user.email);
                                        }
                                    },this);
                                    resolve(true);
                                });
                            }
                            return;
                        }
                        this.pushMultipleToSteps(item, (user)=>{
                            users2Add.push(user);
                            iterable(resolve);
                        },()=>{
                            iterable(resolve);
                        });
                    }
                    return new Promise(resolve => {
                        iterable(resolve)
                    });
                }
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

            async showModalEditContacts(nodeId){
                // this.$store.commit('SET_ACTIVATE_STATE', 'showModalEditContacts');
                // this.addLogOperation({ action: 'r08-display-contacts', result: 0});
                this.showPopupEditContacts = true;
                $(".application-page-dialog .vs-popup--close").click();
                this.$validator.reset();
                this.editContact = await this.getContact(nodeId);
            },
            openCalendarClick: function() {
                this.$refs.calendar.fp.toggle();
            },
            async toggleReturnCircular(circular_user_id) {
                this.isNotReturnCircular = !this.isNotReturnCircular;
                const returnFlg =  this.isNotReturnCircular?0:1;
                await Axios.patch(`${config.BASE_API_URL}/circulars/${this.circular.id}/users/${circular_user_id}/updateReturnflg`, {returnFlg});
            },
            async buildSelectUsersDisplay() {
                let plan_list = (await Axios.get(`${config.BASE_API_URL}/circulars/${this.circular.id}/get_plan`)).data.data
                this.selectUsersDisplay.length = 0;
                this.changeTimes++;
                this.is_plan=false
                if (this.selectUsers) {
                    //let arrSelectUsers = this.$store.state.home.circular.users;
                    let arrSelectUsers = Object.assign([],this.$store.state.home.circular ? this.$store.state.home.circular.users : []);
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
                                    obj.score=plan_list[user.plan_id] ? plan_list[user.plan_id].score : ""
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
                    this.selectUsersDisplay[0].map(s=>{
                       s.changeTimes = this.changeTimes;
                    });
                    this.onlyInternalUser = arrSelectUsers.every(user => user.parent_send_order === 0);
                }else{
                    this.onlyInternalUser = true;
                }
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
            onTreeAddToStepDoubleClick(node) {
                node.check();
                this.onTreeAddToStepClick();
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
            // 社外用回覧者存在チェック
            checkOutsideCompanyCircularUser(){
              var selectUsers = this.$store.state.home.circular.users;
                var hasOutsideUser = selectUsers.findIndex((item => item.mst_company_id != selectUsers[0].mst_company_id || item.edition_flg != selectUsers[0].edition_flg || item.env_flg != selectUsers[0].env_flg || item.server_flg != selectUsers[0].server_flg))
              // 社外回覧者存在の場合、社外アクセスコード保護表示する
              if(hasOutsideUser > 0){
                this.outsideAccessCodeShowFlg = true;
              }else {
                this.outsideAccessCodeShowFlg = false;
              }
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
            },
            onAllowDuplicate(){
                this.confirmDuplicateEmail = false;
                this.onUpdateContact(true);
            },
            onCancelDuplicate(){
                this.confirmDuplicateEmail = false;
            },
            async onShowTemplateDetail(template_name, template_valid, template_rotes){
                this.templateDetail.template_name = template_name;
                this.templateDetail.template_rotes = template_rotes;
                this.templateDetail.template_valid = template_valid;
                this.isShowTemplateDetailBack = true;
                this.showTemplateDetail = true;
            },
            async selectTemplate(template_rotes){

                if(this.isSendAll){
                    this.confirmEdit = false;
                    this.$vs.dialog({
                      type: 'alert',
                      color: 'danger',
                      title: `確認`,
                      acceptText: '閉じる',
                      text: `宛先、回覧順に承認ルートが存在するため、
                      一斉送信できません。`,
                      accept: () => {}
                    });
                    return;
                }
                if(this.selectUsers.length > 1){
                    this.confirmEdit = false;
                    // 宛先、回覧順に合議でないものが存在するため、合議を適用できません。
                    this.showPopupErrorTemplateMessage = "合議を適用";
                    this.isShowTemplateDetailBack = false;
                    this.showTemplateDetail = false;
                    this.showPopupErrorTemplate = true;
                    return false;
                }
                //this.isTemplateCircular = true;
                let uers = [];
                for(let i = 0; i < template_rotes.length; i++){
                    if(!template_rotes[i].template_route_valid){
                        continue;
                    }
                    for(let j = 0; j < template_rotes[i].users.length; j++){
                        let user = {
                            child_send_order: i + 1,
                            email: template_rotes[i].users[j].email,
                            name: template_rotes[i].users[j].family_name + ' ' + template_rotes[i].users[j].given_name,
                            edition_flg: this.selectUsers[0].edition_flg,
                            env_flg: this.selectUsers[0].env_flg,
                            server_flg: this.selectUsers[0].server_flg,
                            company_id: this.selectUsers[0].mst_company_id,
                            company_name: this.selectUsers[0].mst_company_name,
                            is_maker: false,
                            template_rotes_id: template_rotes[i].route_id,
                            template_mode: template_rotes[i].mode,
                            template_wait: template_rotes[i].wait,
                            template_score: template_rotes[i].option,
                            template_detail: JSON.stringify({
                                summary: template_rotes[i].department_name + " " + template_rotes[i].position_name + " (" + (template_rotes[i].option === 0 ? "" : template_rotes[i].option) + this.template_modes[template_rotes[i].mode] + ")",
                                agreement: {
                                    type: template_rotes[i].mode,
                                    detail: template_rotes[i].option,
                                    wait: template_rotes[i].wait,
                                }
                            }),
                        };
                        if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                            this.deleteUserView(user.email);
                        }
                        uers.push(user);
                    }
                }
                this.pushTemplateToSteps(uers, this.clearSuggestionInput);
                // dialog hide
                this.isShowTemplateDetailBack = false;
                this.showTemplateDetail = false;
                this.confirmEdit = false;
            },
            pushTemplateToSteps: async function (users, callback) {
                if(this.loginUser && this.loginUser.isGuestCompany) {
                    for(let i = 0; i < users.length; i ++){
                        let user = users[i];
                        if((user.company_id != this.loginUser.mst_company_id || user.env_flg != config.APP_SERVER_ENV || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != config.APP_SERVER_FLG)
                            && (user.company_id != this.loginUser.parent_company_id || user.env_flg != this.loginUser.host_app_env || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != this.loginUser.host_contract_server)) {
                            this.$vs.dialog({
                                type: 'alert',
                                color: 'danger',
                                title: `メッセージ`,
                                acceptText: '閉じる',
                                text: `回覧先の設定が誤っています`,
                            });
                            accept: () => {
                                callback();
                            }
                            return Promise.resolve(true);
                        }
                    }
                }

                const isTotallyConfidenceFiles = this.files.every(file => file.confidential_flg);
                let isInternalUser = true;
                for(let i = 0; i < users.length; i ++){
                    let user = users[i];
                    if(this.selectUsers[0].mst_company_id === user.company_id &&
                        this.selectUsers[0].env_flg === user.env_flg &&
                        this.selectUsers[0].edition_flg === user.edition_flg &&
                        this.selectUsers[0].server_flg === user.server_flg){
                    }else{
                        isInternalUser = false;
                        break;
                    }
                }

                if (isTotallyConfidenceFiles && !isInternalUser) {
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `別企業のユーザーは設定できません`,
                        accept: () => {
                            callback();
                        }
                    });
                }
                if (isTotallyConfidenceFiles && isInternalUser || !isTotallyConfidenceFiles) {
                    const data = {
                        users: users,
                    };

                    await this.addCircularUsers(data);
                    this.$forceUpdate();
                    callback();
                }

            },
            buildTemplateSelectUsersDisplay() {
                this.isTemplateCircular = false;
                this.selectTemplateUsersDisplay.length = 0;
                // 合議の場合、同じ企業、parent_send_order同じです
                let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                // i = 0 申請者 除外する
                for(let i = 1;i < arrUsers.length;i ++){
                    // 合議userの場合 user_routes_id存在する
                    if(Object.prototype.hasOwnProperty.call(arrUsers[i], "user_routes_id") && arrUsers[i].user_routes_id){
                        this.isTemplateCircular = true; // true:合議

                        let child_send_order = arrUsers[i].child_send_order - 1;
                        if(!Object.prototype.hasOwnProperty.call(this.selectTemplateUsersDisplay,child_send_order)){
                            this.selectTemplateUsersDisplay[child_send_order] = [];
                        }
                        arrUsers[i]['user_routes_name'] = JSON.parse(arrUsers[i].detail).summary;
                        this.selectTemplateUsersDisplay[child_send_order].push(arrUsers[i]);
                    }
                }
            },
            async templateSearch() {
                let info = {
                    templateRouteName : this.templateSearchModel,
                    template_route_flg : $('#tempWithMeBox').prop('checked') ? 1 : 0,
                };

                this.arrTemplate = await this.getListTemplate(info);
            },
                async tempWithMe(){
                    let info = {
                        templateRouteName : this.templateSearchModel,
                        template_route_flg : $('#tempWithMeBox').prop('checked') ? 1 : 0,
                    };
                    this.arrTemplate = await this.getListTemplate(info);
                    this.info.template_route_flg = info.template_route_flg;
                    userService.updateDisplaySetting(this.info);
                },
            async updatePlan(user, plan_mode=null){

                if( plan_mode !== null ) user.plan_mode = plan_mode;

                if(user.plan_mode == 3 && (user.score=='' || user.score==null)){
                    return false
                }
                if (user.plan_mode == 3 && (user.score<=0 || user.score > user.plan_users.length)){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `合議人数を確認してください`,
                        accept: () => {
                            ()=>{};
                        }
                    });
                    return
                }
                this.$vs.loading({
                    container: '#approval-page',
                })
                let res= await Axios.post(`${config.BASE_API_URL}/circulars/`+user.circular_id+'/plan', user , {data:{nowait: true} });
                this.$vs.loading.close('#approval-page > .con-vs-loading');
            },
            focusInput: function(e){
              $(e.target).focus();
            },
            async deletePlan(user){
                    await Axios.get(`${config.BASE_API_URL}/circulars/${user.circular_id}/del_plan/${user.plan_id}`)
                },
            showPlanDetail(list){
                this.planDetailShow=true
                this.planDetailList=list
            },

            handleViewMailList: function(){
              $('#handleViewMailList').stop().toggleClass('show');

              $('#mail-steps-card').stop().toggle(500);
            }
            
        },
        async mounted() {
            this.info = await this.getMyInfo();
            this.emailTemplateOptions =  Utils.setEmailTemplateOptions(this.info);
            //PAC_5-1400 閲覧ユーザー複数設定
            this.maxViewer = this.info.max_viwer_count;
            this.infoCheck = await this.getInfoCheck();

        },
        watch: {
            "treeData": function(newVal, oldVal) {
                this.treeLoaded++;
            },
            isSendAll: function(newVal, oldVal) {
              this.changeSendAllFlg();
              this.buildSelectUsersDisplay();
              this.isShowCurrent += Math.random();
              if(newVal == true){
                  this.showTree = true
              }
            },
            "$store.state.contacts.changePhoneBooks": async function (newVal, oldVal) {
                this.treeData = await this.getEmailTrees();
            },
            "$store.state.application.loadDepartmentUsersSuccess": async function (newVal, oldVal) {
                this.treeData = await this.getEmailTrees();
                this.addLogOperation({ action: 'r08-display-contacts', result: 0});
                this.confirmEdit = true;

                $("body").addClass('disabledScroll');
            },
            "$store.state.application.selectUserChange": function (newVal, oldVal) {
                this.usersChange = !this.usersChange;
                this.buildSelectUsersDisplay();
              this.checkOutsideCompanyCircularUser();
            },
            "$store.state.home.selectUserChange": function (newVal, oldVal) {
                this.usersChange = !this.usersChange;
                this.buildSelectUsersDisplay();
                this.checkOutsideCompanyCircularUser();
            },
            "$store.state.application.selectTemplateUserChange": function (newVal, oldVal) {
                this.buildTemplateSelectUsersDisplay(); // 合議ユーザー表示作成
            },
            accessCode:function(){
                this.accessCode=this.accessCode.replace(/[\W]/g,'');
            },
            outsideAccessCode:function(){
                this.outsideAccessCode=this.outsideAccessCode.replace(/[\W]/g,'');
            },
            showTemplateDetail:function(newVal, oldVal){
                if(this.isShowTemplateDetailBack){
                    this.confirmEdit = !newVal;
                }
            },
            //PAC_5-2705 S
            showCheckUser: function (val) {
              if(val && this.settingLimit.require_approve_flag && this.selectUsersCheck==0 ){
                this.selectUsersCheck++;
                this.isNotReturnCircular = true;
                const returnFlg = 0;
                let circular_user_id = this.selectUsers[0].id;
                Axios.patch(`${config.BASE_API_URL}/circulars/${this.circular.id}/users/${circular_user_id}/updateReturnflg`, {returnFlg});
              }
            },
            //PAC_5-2705 E
            confirmEdit: function(val){
              if( !val ){
                $('body').removeClass('disabledScroll');
              }
            }
        },
        async created() {
            if (
              !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
                navigator.userAgent
              )
            ) {
              this.$router.push('/');
            }
            var limit = getLS("limit");
            limit = JSON.parse(limit);
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            this.$store.commit('application/updateListUserView', []);
            // 特設サイト
            this.specialCircularFlg = this.circular && this.circular.special_site_flg;
            if(limit && limit.enable_any_address == 1){
                this.checkShowAddress = true;
            }else{
                this.checkShowAddress = false;
            }
            this.enable_any_address = limit.enable_any_address;
            if(!this.$store.state.home.fileSelected || !this.$store.state.home.circular) {
                await this.addLogOperation({ action: 'r08-display', result: 1});
                this.$router.push('/');
            }
            this.accessCode = this.getAccessCode(6);
            this.outsideAccessCode = this.getAccessCode(6);
            const circular = {
                circular : this.$store.state.home.circular
            }
            let user;
            if(this.selectUsers.length <= 0) {
                const data = {users: [{
                        child_send_order: 0,
                        name: this.loginUser.family_name + ' ' + this.loginUser.given_name,
                        email: this.loginUser.email,
                        is_maker: true,
                        company_id: this.loginUser.mst_company_id,
                        company_name: this.loginUser.mst_company_name,
                    }]};
                user = await this.addCircularUsers(data);
            }
            if(!this.$store.state.home.circular){
                this.$store.commit('home/createCircular', circular);
                this.$store.commit("home/addCircularUserSuccess", user, { root: true });
            }

          // 特設サイト
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
            this.buildTemplateSelectUsersDisplay();
            this.checkOutsideCompanyCircularUser();

          this.settingLimit = null;
          if (!Object.prototype.hasOwnProperty.call(this.loginUser, "isAuditUser") || !this.loginUser.isAuditUser) {
            this.settingLimit = await this.getLimit();
          }
          if (this.settingLimit==null){
            this.settingLimit={};
          }
            
            if(this.selectUsers && this.selectUsers.length > 0 ) {
                let applicant = this.selectUsers.find(user => user.email === this.loginUser.email);
                if(applicant) {
                    this.isNotReturnCircular = (applicant.return_flg == 0);
                }
              //PAC_5-2705 S
              if(this.settingLimit.require_approve_flag){
                this.isNotReturnCircular = true;
              }
            }
            this.protectionSetting = await this.getProtection();

            if (this.protectionSetting.enable_email_thumbnail == 1){
                this.showEmailThumbnailOption = true;
            }else{
                this.showEmailThumbnailOption = false;
            }
            if (this.circular && this.protectionSetting) {
              // デフォルト値:OFF
              this.allowChangeDestinationFlg = this.protectionSetting.destination_change_flg;
              this.accessCode = this.circular.access_code ? this.circular.access_code : this.accessCode;
              this.accessCodeFlg = this.protectionSetting.access_code_protection;
              this.outsideAccessCodeFlg = this.protectionSetting.access_code_protection;
              // PAC_5-1115 終了
              await this.addLogOperation({action: 'r08-display', result: 0});
            }

            if (this.$store.state.application.commentTitle){
                this.commentTitle = this.$store.state.application.commentTitle;
            }
            else if(this.$store.state.home.circular && this.$store.state.home.circular.users){
                this.commentTitle = this.$store.state.home.circular.users[0].title;
            }
            if (this.$store.state.application.commentContent){
                this.commentContent = this.$store.state.application.commentContent;
            }
            // PAC_5-2245 Start
            if (this.protectionSetting && this.protectionSetting.protection_setting_change_flg && this.$store.state.application.requirePrint){
                this.protectionSetting.require_print = this.$store.state.application.requirePrint;
            }
            // PAC_5-2245 End
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

            this.myCompanyInfo = await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                .then(response => {
                    return response.data ? response.data.data : [];
                })
                .catch(error => {
                    return [];
                });
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            if(this.circular && this.circularChangeListUserView && this.circularChangeListUserView[this.circular.id]){
                this.$store.commit('application/updateListUserView', this.circularChangeListUserView[this.circular.id]);
                if(this.circularChangeListUserView[this.circular.id].length > 0){
                    this.onAroundArrow();
                }
                this.$store.commit('home/updateCircularChangeListUserView',{id:this.circular.id,data:[]});
            }
        },
        beforeDestroy() {
          $('#send_success').remove();
          $('body').removeClass('disabledScroll');
        }
    }

</script>

<style lang="scss">

body{
  &.disabledScroll{
    overflow: hidden!important;
  }
}

.router-view{
  padding: 1.2rem !important;
}

input {
  transform: scale(1) !important;
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

#approval-page {

  
  input[type=text], select, textarea{
    transform: scale(1);
  }

  #action{

    position: fixed;
    bottom: -5px;
    left: 5%;
    width: calc( 90% - 1px );

    button{
      background: rgba(var(--vs-primary),1)!important;
      float: left;
      width: 48% !important;
      padding: .75rem 0;
      margin: 0;

      &:last-child{
        margin: 0;
      }
      &:first-child{
        margin-right: 1rem;
      }

      img{
        margin: 0 !important;
      }
    }
  }

  .bg-none{
    background: transparent!important;
    box-shadow: 0 0 0 0;

    .vx-card__body{
      padding: 0;
    }
  }

  display: block;
  top: -60px;
  position: relative;
  .breadcrumb {
    list-style: none;
    overflow: hidden;
    .badge{
      width:50%;
      color:#0a84e3;
    }

    li {
      float: left;
      width:50%;
      border-top:1px solid #dcdcdc;
      border-bottom:1px solid #dcdcdc;

      p {
        text-decoration: none;
        padding: 10px 0 10px 40px;
        position: relative;
        display: block;
        float: left;
        font-size: 14px;
        width:100%;
      }
    }
    li:first-child p {
      padding-left: 20px;
    }
    li:first-child p::before{
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
    li:first-child p::after {
      content: " ";
      display: block;
      width: 0;
      height: 0;
      border-top: 21px solid transparent; /* Go big on the size, and let overflow hide */
      border-bottom: 21px solid transparent;
      border-left: 13px solid white;
      position: absolute;
      top: 50%;
      margin-top: -21px;
      left: 100%;
      z-index: 2;
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
    padding: 0 20px 15px 20px;

    .group{

      .bg-orange{
        background: #ffdab9 !important;
      }

      .bg-pinkle{
        background: #ffb6c1 !important;
      }

      &.parent-block{
        position: relative;

        &::before{
          content: "";
          position: absolute;
          left: calc( 25% - 15px );
          top: 0;
          width: 30px;
          height: 18px;
          background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
          background-size: contain;
          background-position: center;
          background-repeat: no-repeat;
          transition: transform 0.5s ease-in;
        }
      }
      
      .item{
        border-radius: 5px 5px 5px 5px;
        background: #C8EFC8;
        display: block !important;
        padding: 5px 5px 7px 10px;
        position: relative;
        margin-top: 20px;

        &.me{
          background: #d1ecff;
        }

        .remove-flag, .currentUser-flg, .final{
          position: absolute;
          right: 10px;
          top: 5px;
          z-index: 3;
        }

        .currentUser-flg{
          right: 25px;
        }

        .final{
          padding: 1px 3px;
          right: 45px;
          border: 1px solid #0a84e3;
          color:#0a84e3;
          border-radius:5px 5px 5px 5px;
          white-space: nowrap;

          &.plan_mode{
            background: #fff;
            color: #e1ba53;
            border-color: #fff;
          }
        }

        &.first{
          &::before{
            content: "";
            position: absolute;
            left: -40px;
            top: 20px;
            width: 30px;
            height: 18px;
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            transform: rotate(-90deg);
          }
        }

        &.last{
          &::before{
            content: "";
            position: absolute;
            left: calc( 50% - 15px );
            top: -20px;
            width: 30px;
            height: 18px;
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
          }
        }

        .name{
          display: block;
          width: 90%;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          max-width: calc( 100% - 65px );
        }

        .email{
          display: block;
          width: 95%;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }
      }

      .child1st-block{
        .mail-box{
          margin-top: 20px;
          position: relative;

          &::before{
            content: "";
            position: absolute;
            left: 25px;
            top: -35px;
            width: 15px;
            height: 50px;
            background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+");
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            transform: rotate(-0deg);
          }
        }
      }

      .child-order:not(.first):not(.last):after{
        content: "";
        position: absolute;
        left: 45%;
        top: -20px;
        width: 30px;
        height: 18px;
        background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
      }
      
      .first.return-flg.no-before::after,
      .no-before::before,
      .no-before::after {
        content: none;
      }

      &.parent-block{
        position: relative;
        
        .first{
          &.return-flg{
            &::before{
              content: "";
              position: absolute;
              left: -30px;
              top: 10px;
              width: 20px;
              height: 20px;
              background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
              background-size: contain;
              background-position: center;
              background-repeat: no-repeat;
              transform: rotate(-90deg);
              transition: transform 0.5s ease-in;
            }

            &::after{
              content: "";
              position: absolute;
              left: -45px;
              top: 20px;
              width: 20px;
              height: 30px;
              background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
              background-size: contain;
              background-position: center;
              background-repeat: no-repeat;
              transform: rotate(90deg);
              transition: transform 0.5s ease-in;
            }
          }
        }

        &::before{
          content: "";
          position: absolute;
          left: calc( 25% - 10px );
          top: 0;
          width: 30px;
          height: 18px;
          background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
          background-size: contain;
          background-position: center;
          background-repeat: no-repeat;
          transition: transform 0.5s ease-in;
        }
      }
    }
    .item-div{
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .plan-form{
      border-top: 1px solid #fcc783;
      padding-top: 10px;
      margin-top: 5px;
      display: inline-block;
      width: 100%;

      .plan-txt{
        display: inline-block;
        width: 50px;
        position: relative;
        float: left;
      }

      .plan-radio{
        padding: 0;
        min-width: 90px;
        display: inline-block;
        float: left;
        
        label{
          display: block;
          margin-bottom: 10px;

          input{
            transform: scale(1.5) !important; 
          }

          &:first-child{
            margin-right: 20px;
          }

          .checkmark{
            display: inline-block;
            width: 13px;
            height: 13px;
            background: #fff;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.3);
            position: relative;

            &.checked{
              border-color: #0984e3;

              &:after{
                position: absolute;
                left: 2px;
                top: 2px;
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #0984e3;
                content: '';
              }
            }
          }
        }
      }

      .plan-input{
        text-align: center;
        display: flex;
        position: relative;
        bottom: -7px;
        float: left;
        min-width: 85px;

        .vs-input{
          display: inline-block;
          width: auto;
        }
        input{
          max-width: 50px;
        }
      }
    }

    
  }

  .confirm_box{
    position:fixed;
    top:calc(50% - 100px);
    left:calc(50% - 100px);
    width:200px;
    height:200px;
    border-radius: 50% 50% 50% 50%;
    border:1px solid rgb(9,132,227);
    background-color:rgb(9,132,227);
    text-align: center;
    z-index:900;
    div:first-child{
      position:relative;
      top:50px;
    }
    div:last-child{
      position:relative;
      top:50px;
      font-size: 18px;
      color: white;
    }
  }
}
/*PAC_5-2206 ボタンをipad・ipadproで横表示なるように調整の為追加　end*/
.plan-radio{
    padding-right: 15px;
    label{
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        &:first-child{
            margin-bottom: 10px;
        }
    }
}
.plan_detail{
    padding: 10px 15px;
}

.application-page-dialog.mobile{
  .append-text.btn-addon{
    button{
      margin: 0;
    }
  }
  .contact_tree_action {
    padding-right: 0 !important;

    button{
      padding: 0.75rem 1rem !important;
    }
  }
  .template_dialog{
    button{
      white-space: nowrap;
      padding: 0.75rem 1rem !important;
    }
  }
  .vs-popup--content{
    overflow: hidden;

    .tree-wrap_dialog{
      max-height: calc( 100vh - 350px );
    }
  }
}


@media ( min-width: 601px ){
  #approval-page {
    top: -15px;
    #action {
      max-width: 550px;
      left: calc( 50% - 275px );

      .vs-col{
        display: inline-block !important;
        text-align: center;
      }
      button{
        padding: 1.2rem 0;
        font-size: 18px;
        width: 40% !important;
        height: 85px;

        &:first-child{
          margin-right: 20%;
        }

        img{
          height: 18px !important;
          width: auto !important;
        }
      }
    }
    .mail-steps{
      padding: 0 0 15px 0;
    }
  }
}

@media ( max-width: 600px ){
  #approval-page {
    margin-bottom: 20px;

    #action {
      button{
        height: 61px;
      }
    }

    .mail-steps {
      .first_me{
        margin: 0;
        display: inline-block;
      }

      .group{
        .item.first::before{
          transform: rotate(0deg);
          left: calc(50% - 15px);
          top: -20px;
        }

        &.parent-block::before{
          left: calc(50% - 15px);
        }
      }
      
    }
  }
}

@media (min-width: 768px) and ( max-width: 1024px ){
  #approval-page{
    margin-bottom: 90px;

    .mail-steps {
        .child1st-block {
          padding-left: 10px;

          .full-width{
            .mail-box:first-child{
              margin-top: 0;

              &::before{
                display: none;
              }
            }
          }
        }
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

</style>
