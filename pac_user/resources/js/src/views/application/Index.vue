<template>
    <div id="application-page">
        <div style="margin-bottom: 15px">
            <vs-row>
                <vs-col vs-type="flex" vs-lg="6" vs-xs="12">

                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-lg="6" vs-xs="12">
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="goBack"> 戻る</vs-button>
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState" v-on:click="onSaveCircularClick"><i class="fas fa-check" style="margin-right: 5px;"></i> 申請する</vs-button>
                </vs-col>
            </vs-row>
        </div>
        <div class="vx-row">
            <div class="vx-col w-full mb-base lg:pr-0">
                <vx-card :hideLoading="true"  :key="isShowCurrent">
                    <div slot="no-body">

                    </div>
                    <vs-row class="border-bottom pb-4">
                        <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12">
                            <h4>宛先、回覧順 <span class="text-danger">*</span></h4>
                          <span v-if="myCompanyInfo.is_together_send == 1"  :title="`一斉送信`" :style="`margin-left:10px;`"><vs-switch v-model="isSendAll" :title="`一斉送信`"/></span>
                        </vs-col>
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6" vs-xs="12" v-if="!isTemplateRoute">
                            <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12" style="padding-left: 43%">
                              <span @click="onDepartmentUsersSelect()" style="cursor:pointer;">
                                  <!--PAC_5-2193 回覧先設定のアドレス帳のマークの表示を大きくする-->
                                  <svg xmlns="http://www.w3.org/2000/svg" width="2.5rem" height="2.5rem" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open "><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                              </span>
                            </vs-col>
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto ipad_size"  color="danger" type="filled" v-on:click="clearSelectUsers">全て削除</vs-button>
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto mr-0 pl-6 sm:pl-8 md:pl-8 lg:pl-8 xl:pl-8 ipad_size" v-bind:disabled="selectUsers.length < 2" color="primary"
                                       type="filled" @click="onShowAddFavorite">お気に入り登録＋</vs-button>
                        </vs-col>
                    </vs-row>
                    <vs-popup class="application-page-dialog" title=""  :active.sync="confirmEdit">
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
                                                    <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="6">
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
                                                                        <div style="width: 60px;">
                                                                          <vs-button class="vs-button_dialog square action action_dialog" v-if="!itemFavorite[0].use_plan_flg" color="primary" @click="onEditFavorite(itemFavorite,indexFavorite)">編集</vs-button>
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
                                                    <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="6">
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
                    <div class="mail-steps">
                        <div class="mail-list">
                            <vs-row v-if="selectUsers.length > 0" vs-type="flex" :class="['group applicant', (selectUsersDisplay && (selectUsersDisplay.length > 0) && selectUsersDisplay[0].length > 2) ? 'return-flg': '', 'not-return']">
                                <vs-col vs-w="6">
                                    <vs-row vs-type="flex" vs-justify="center">
                                        <vs-col vs-w="10" class="item me" vs-type="flex" vs-align="flex-start">
                                            <div>{{selectUsers[0].name}}</div>
                                            <div>【{{selectUsers[0].email}}】</div>
                                            <span v-if="selectUsers.length === 1 && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</span>
                                            <a href="#" class="currentUser-flg"> <i class="far fa-flag"></i></a>
                                        </vs-col>
                                        <vs-col vs-w="10">
                                            <vs-checkbox :value="isNotReturnCircular" v-if="!isSendAll && (!onlyInternalUser || specialCircularFlg) && selectUsersDisplay[0].length > 1" @click="toggleReturnCircular(selectUsers[0].id)" style="">最終承認者から直接社外に送る</vs-checkbox>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>
                                <vs-col vs-w="6" class="child1st-block full-width" v-if="(loginUser.email === selectUsers[0].email)" >
                                    <div class="full-width range-0">
                                        <vs-row vs-justify="center" vs-type="flex" v-for="(user, index) in setSelectUsersDisplay" :key="user.email + index + user.changeTimes"
                                                :class="[(index+1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last-row': '']">
                                            <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    :class="['item child-order item-draggable', index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[0].length-1 && index + 1 >1)  ? 'last': '', (index + 1 === 1 && selectUsersDisplay[0].length > 1) ? 'return-flg': '',
                                                    (user.email === selectUsers[0].email) ? 'me': '']" :style="{background:user.is_plan?'rgb(250,168,62)':'#C8EFC8'}" >
                                                <div v-if="!user.is_plan" draggable @dragstart="startDrag(user, $event)" @dragend="endDrag($event)" @dragover="overDrag(user, user.child_send_order, false, $event)"
                                                     @drop="onDrop(user, user.child_send_order, false, $event)" @dragenter.prevent
                                                     class="dropable-item h-full w-full">
                                                    <div>{{index + 1}} - {{user.name || '社員'}}</div>
                                                    <div>【{{user.email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2) && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</span>
                                                    <a href.prevent v-on:click="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </div>
                                                <!--PAC_5-1698 S-->
                                                <div v-if="user.is_plan" draggable @dragstart="startDrag(user, $event)" @dragend="endDrag($event)" @dragover="overDrag(user, user.child_send_order, false, $event)"
                                                     @drop="onDrop(user, user.child_send_order, false, $event)" @click="showPlanDetail(user.plan_users)" @dragenter.prevent

                                                     class="dropable-item h-full w-full"  style="display: flex;justify-content: space-between;align-items: center;">
                                                    <div class="plan-left">
                                                        <div>{{index + 1}} - {{'合議設定'}}</div>
                                                        <div>【{{user.plan_users.length+'人'}}】</div>
                                                      <!--PAC_5-2795 S-->
                                                        <div class="plan-left-remark">※クリックすると回覧の詳細が表示されます。</div>
                                                      <!--PAC_5-2795 E-->
                                                    </div>
                                                    <div class="plan-txt" style="display:flex;justify-content: center;align-items: center">
                                                        合議
                                                    </div>
                                                    <div class="plan-form" style="margin-right: 90px; display: flex;justify-content: flex-start; align-items: center ">
                                                        <div class="plan-radio" style="display: flex;flex-direction: column;justify-content: center;align-items: flex-start">
                                                            <label>
                                                                <input type="radio"  value="1"
                                                                       v-model="user.plan_mode"
                                                                       @change="updatePlan(user)"
                                                                       @click.stop="()=>{}"
                                                                >
                                                                <span>全員必須</span>
                                                            </label>
                                                            <label>
                                                                <input type="radio"  value="3"
                                                                       v-model="user.plan_mode"
                                                                       @change="updatePlan(user)"
                                                                       @click.stop="()=>{}"
                                                                >
                                                                <span>人数指定</span>
                                                            </label>
                                                        </div>
                                                        <div class="plan-input" style="display: flex;justify-content: center;align-items: center; width: 85px;flex-shrink: 0">
                                                            <vs-input class="inputx w-full" type="number"  v-if="user.plan_mode==3" @click.stop="()=>{}"   @change="updatePlan(user)"   v-model="user.score" />
                                                            <vs-input class="inputx w-full" v-else  disabled   name="subject"  />
                                                            <span style="padding-left: 10px">人</span>
                                                        </div>
                                                    </div>
                                                    <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 2)" class="final"> 最終</span>
                                                    <a href.prevent v-on:click.stop="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </div>
                                                <!--PAC_5-1698 E-->
                                            </vs-col>
                                        </vs-row>
                                        <template v-if="isSendAll && selectAllSendUsersDisplay[0].length > 0">
                                            <vs-row id="template_route" vs-justify="center" vs-type="flex" v-for="(userRoute, index) in selectAllSendUsersDisplay"
                                                    :class="[(index === selectAllSendUsersDisplay.length-1)  ? 'last-row': '']" :key="index">
                                            <vs-col
                                                vs-w="10"
                                                vs-type="flex"
                                                vs-align="flex-start"
                                                :class="['item child-order', index === 0 ? 'first' : '', (index > 0 && index === selectAllSendUsersDisplay[0].length-1)  ? 'last': '',  (index === 0 && selectAllSendUsersDisplay[0].length > 1) ? 'return-flg': '']" >
                                                <div class="h-full w-full">
                                                <template v-for="(user, itemIndex) in userRoute">
                                                    <div style="position: relative;" :key="itemIndex">
                                                    {{itemIndex + 1 }} - {{user.mst_company_name ? user.mst_company_name : "社外" }} - {{ user.mst_company_name  ? (user.name ? user.name :"社員") : (user.name ? user.name : '社外')}}<br />【{{user.email}}】
                                                    <a href.prevent v-on:click="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag" style="position: absolute;top: 50%;right: 20px;margin-top: -0.8em;"><i class="fas fa-times"></i></a>
                                                    </div>
                                                    <br :key="itemIndex + user.name"/>
                                                </template>
                                                </div>
                                            </vs-col>
                                            </vs-row>
                                        </template>
                                        <!-- template route users start -->
                                        <template v-if="!isSendAll && isTemplateCircular">
                                            <vs-row id="template_route" vs-justify="center" vs-type="flex" v-for="(userRoute, index) in selectTemplateUsersDisplay"
                                                    :class="[(index === selectTemplateUsersDisplay.length-1)  ? 'last-row': '']" :key="index">
                                                <vs-col
                                                        vs-w="10"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        :class="['item child-order', index === 0 ? 'first' : '', (index > 0 && index === selectTemplateUsersDisplay.length-1)  ? 'last': '',  (index === 0 && selectTemplateUsersDisplay.length > 1) ? 'return-flg': '']" >
                                                    <div class="h-full w-full">
                                                        <div>{{userRoute[0].user_routes_name}}</div>
                                                        <template v-for="(user, itemIndex) in userRoute">
                                                            <div :key="itemIndex">{{user.name || '社員'}} 【{{user.email}}】</div>
                                                        </template>
                                                        <span v-if="selectTemplateUsersDisplay.length === (index + 1)" class="final"> 最終</span>
                                                        <a href.prevent v-if="!userRoute[0].template_id" v-on:click="clearSelectUsers" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                    </div>
                                                </vs-col>
                                            </vs-row>
                                        </template>
                                        <!-- template route users end -->
                                        <vs-row vs-justify="center" vs-type="flex" v-if="!isSendAll && (!onlyInternalUser || specialCircularFlg) && selectUsersDisplay[0].length > 1 && !isNotReturnCircular">
                                            <vs-col vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    :class="['item child-order item-draggable me']">
                                                <div>{{selectUsersDisplay[0].length}} - {{selectUsers[0].name}}</div>
                                                <div>【{{selectUsers[0].email}}】</div>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row vs-type="flex" vs-justify="center" class="row-drop-area">
                                            <vs-col
                                                    vs-w="10"
                                                    vs-type="flex"
                                                    vs-align="flex-start"
                                                    class="drop-area hidden item item-dropable" >
                                                <div @drop="onDrop(selectUsers[0], selectUsersDisplay[0].length, true, $event)" @dragenter.prevent
                                                     @dragover="overDrag(selectUsers[0], selectUsersDisplay[0].length, true ,$event)"
                                                     class="dropable-item h-full w-full">
                                                    <div>&nbsp;</div>
                                                </div>
                                            </vs-col>
                                        </vs-row>
                                    </div>
                                </vs-col>
                            </vs-row>
                            <div class='full-width range-1'>
                                <vs-row
                                        v-for="(group, idx) in (setSelectUsersIsDisplay)"
                                        vs-type="flex"
                                        vs-align="space-around"
                                        class='group parent-block'
                                        :key="group+''+idx"
                                        >
                                    <vs-col vs-w="6" v-if="selectUsersDisplay && selectUsersDisplay[idx + 1]">
                                        <vs-row v-if="!isSendAll && !Array.isArray(selectUsersDisplay[idx + 1])"
                                                vs-type="flex"
                                                vs-justify="space-around"
                                                :key="selectUsersDisplay[idx + 1].email + '0'"  >
                                            <vs-col vs-w="10"
                                                    :class="['item item-draggable',(idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                    vs-type="flex"
                                                    vs-align="flex-start" >
                                                <div draggable @dragstart="startDrag(selectUsersDisplay[idx + 1], $event)" @dragend="endDrag($event)"
                                                     @drop="onDrop(selectUsersDisplay[idx + 1], selectUsersDisplay[idx + 1].child_send_order, false, $event)"
                                                     @dragover="overDrag(selectUsersDisplay[idx + 1], selectUsersDisplay[idx + 1].child_send_order, false, $event)" @dragenter.prevent
                                                     class="dropable-item h-full w-full">
                                                    <div>{{selectUsersDisplay[idx + 1].mst_company_id ? selectUsersDisplay[idx + 1].name : '社外'}} - {{selectUsersDisplay[idx + 1].mst_company_name || '社外'}}</div>
                                                    <div>【{{selectUsersDisplay[idx + 1].email}}】</div>
                                                    <div v-if="selectUsersDisplay.length === (idx + 2) && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</div>
                                                    <a href.prevent v-on:click="onRemoveSelectUserClick(selectUsersDisplay[idx + 1].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </div>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row v-if="!isSendAll &&  Array.isArray(selectUsersDisplay[idx + 1]) &&  selectUsersDisplay[idx + 1].length > 0 && selectUsersDisplay[idx + 1][0]"
                                                vs-type="flex"
                                                vs-justify="space-around"
                                                :key="selectUsersDisplay[idx + 1][0].email + '0'" >
                                            <vs-col vs-w="10"
                                                    :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                                    vs-type="flex"
                                                    vs-align="flex-start" :style="{background:selectUsersDisplay[idx + 1][0].is_plan?'rgb(250,168,62)!important':'#ffb6c1'}" >
                                                <div v-if="!selectUsersDisplay[idx + 1][0].is_plan" draggable @dragstart="startDrag(selectUsersDisplay[idx + 1][0], $event)" @dragend="endDrag($event)"
                                                     @drop="onDrop(selectUsersDisplay[idx + 1][0],selectUsersDisplay[idx + 1][0].child_send_order, false, $event)"
                                                     @dragover="overDrag(selectUsersDisplay[idx + 1][0],selectUsersDisplay[idx + 1][0].child_send_order, false, $event)"
                                                     @dragenter.prevent
                                                     class="dropable-item h-full w-full">
                                                    <div>{{selectUsersDisplay[idx + 1][0].name || '社外'}} - {{selectUsersDisplay[idx + 1][0].mst_company_name || '社外'}}</div>
                                                    <div>【{{selectUsersDisplay[idx + 1][0].email}}】</div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1 && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</span>
                                                    <a href.prevent v-on:click="onRemoveSelectUserClick(selectUsersDisplay[idx + 1][0].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </div>
                                                <!--PAC_5-1698 S-->
                                                <div v-if="selectUsersDisplay[idx + 1][0].is_plan" draggable @dragstart="startDrag(selectUsersDisplay[idx + 1][0], $event)" @dragend="endDrag($event)"
                                                     @dragover="overDrag(selectUsersDisplay[idx + 1][0], selectUsersDisplay[idx + 1][0].child_send_order, false, $event)"
                                                     @drop="onDrop(selectUsersDisplay[idx + 1][0], selectUsersDisplay[idx + 1][0].child_send_order, false, $event)" @click="showPlanDetail(selectUsersDisplay[idx][0].plan_users)" @dragenter.prevent

                                                     class="dropable-item h-full w-full"  style="display: flex;justify-content: space-between;align-items: center;">
                                                    <div class="plan-left">
                                                        <div>{{'合議設定'}}</div>
                                                        <div>【{{selectUsersDisplay[idx + 1][0].plan_users.length+'人'}}】</div>
                                                    </div>
                                                    <div class="plan-txt" style="display:flex;justify-content: center;align-items: center">
                                                        合議
                                                    </div>
                                                    <div class="plan-form" style="margin-right: 90px; display: flex;justify-content: flex-start; align-items: center ">
                                                        <div class="plan-radio" style="display: flex;flex-direction: column;justify-content: center;align-items: flex-start">
                                                            <label>
                                                                <input type="radio"  value="1"
                                                                       v-model="selectUsersDisplay[idx + 1][0].plan_mode"
                                                                       @change="updatePlan(selectUsersDisplay[idx + 1][0])"
                                                                       @click.stop="()=>{}"
                                                                >
                                                                <span>全員必須</span>
                                                            </label>
                                                            <label>
                                                                <input type="radio"  value="3"
                                                                       v-model="selectUsersDisplay[idx + 1][0].plan_mode"
                                                                       @change="updatePlan(selectUsersDisplay[idx + 1][0])"
                                                                       @click.stop="()=>{}"
                                                                >
                                                                <span>人数指定</span>
                                                            </label>
                                                        </div>
                                                        <div class="plan-input" style="display: flex;justify-content: center;align-items: center; width: 85px;flex-shrink: 0">
                                                            <vs-input class="inputx w-full" type="number" v-if="selectUsersDisplay[idx + 1][0].plan_mode==3" @click.stop="()=>{}"   @change="updatePlan(selectUsersDisplay[idx + 1][0])"   v-model="selectUsersDisplay[idx + 1][0].score" />
                                                            <vs-input class="inputx w-full" v-else  disabled   name="subject"  />
                                                            <span style="padding-left: 10px">人</span>
                                                        </div>
                                                    </div>
                                                    <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1" class="final"> 最終</span>
                                                    <a href.prevent v-on:click.stop="onRemoveSelectUserClick(selectUsersDisplay[idx + 1][0].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                </div>
                                                <!--PAC_5-1698 E-->
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                    <vs-col vs-w="6"  class="child-block full-width"  v-if="!isSendAll && selectUsersDisplay[idx + 1]" >
                                        <div class="full-width" >
                                            <vs-row
                                                    v-for="(user, index) in setSelectUserDisplayIndex(selectUsersDisplay[idx + 1])"
                                                    vs-type="flex"
                                                    vs-justify="center"
                                                    :key="user.email + index"
                                                    :class="['child-range-'+idx + 1, index + 1 === 1 ? 'start-item' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'end-item': '', (index + 1 ===0) ? 'blank-item': '', (selectUsersDisplay[idx + 1].length < 3 )?'only-item':'']">
                                                <vs-col vs-w="10"
                                                        v-if="index + 1 > 0 && selectUsersDisplay[idx + 1][index + 1]"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        :style="{background:selectUsersDisplay[idx + 1][index + 1].is_plan?'rgb(250,168,62)!important':'#ffb6c1'}"
                                                        :class="['item child-order item-draggable', idx + 1 % 3 == 1 ? 'bg-orange': (idx + 1 % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'last': '', (index + 1 === 1 && selectUsersDisplay[idx + 1].length > 1) ? 'return-flg': '']" >
                                                    <div v-if="!selectUsersDisplay[idx + 1][index + 1].is_plan" draggable @dragstart="startDrag(user, $event)" @dragend="endDrag($event)"
                                                         @drop="onDrop(selectUsersDisplay[idx + 1][index + 1], selectUsersDisplay[idx + 1][index + 1].child_send_order, false, $event)"
                                                         @dragover="overDrag(selectUsersDisplay[idx + 1][index + 1], selectUsersDisplay[idx + 1][index + 1].child_send_order, false, $event)"
                                                         @dragenter.prevent class="dropable-item h-full w-full">
                                                        <div>{{user.name || '社外'}} - {{user.mst_company_name || '社外'}}</div>
                                                        <div>【{{user.email}}】</div>
                                                        <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 1) && (!specialCircularFlg || specialCircularReceiveFlg)" class="final"> 最終</span>
                                                        <a href.prevent v-on:click="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                    </div>
                                                    <!--PAC_5-1698 S-->
                                                    <div v-if="selectUsersDisplay[idx + 1][index + 1].is_plan" draggable @dragstart="startDrag(selectUsersDisplay[idx + 1][index + 1], $event)" @dragend="endDrag($event)"
                                                         @dragover="overDrag(selectUsersDisplay[idx + 1][index + 1], selectUsersDisplay[idx + 1][index + 1].child_send_order, false, $event)"
                                                         @drop="onDrop(selectUsersDisplay[idx + 1][index + 1], selectUsersDisplay[idx + 1][index + 1].child_send_order, false, $event)" @click="showPlanDetail(selectUsersDisplay[idx + 1][index + 1].plan_users)" @dragenter.prevent

                                                         class="dropable-item h-full w-full"  style="display: flex;justify-content: space-between;align-items: center;">
                                                        <div class="plan-left">
                                                            <div>{{'合議設定'}}</div>
                                                            <div>【{{selectUsersDisplay[idx + 1][index + 1].plan_users.length+'人'}}】</div>
                                                        </div>
                                                        <div class="plan-txt" style="display:flex;justify-content: center;align-items: center">
                                                            合議
                                                        </div>
                                                        <div class="plan-form" style="margin-right: 90px; display: flex;justify-content: flex-start; align-items: center ">
                                                            <div class="plan-radio" style="display: flex;flex-direction: column;justify-content: center;align-items: flex-start">
                                                                <label>
                                                                    <input type="radio"  value="1"
                                                                           v-model="selectUsersDisplay[idx + 1][index + 1].plan_mode"
                                                                           @change="updatePlan(selectUsersDisplay[idx + 1][index + 1])"
                                                                           @click.stop="()=>{}"
                                                                    >
                                                                    <span>全員必須</span>
                                                                </label>
                                                                <label>
                                                                    <input type="radio"  value="3"
                                                                           v-model="selectUsersDisplay[idx + 1][index + 1].plan_mode"
                                                                           @change="updatePlan(selectUsersDisplay[idx + 1][index + 1])"
                                                                           @click.stop="()=>{}"
                                                                    >
                                                                    <span>人数指定</span>
                                                                </label>
                                                            </div>
                                                            <div class="plan-input" style="display: flex;justify-content: center;align-items: center; width: 85px;flex-shrink: 0">
                                                                <vs-input class="inputx w-full" type="number" v-if="selectUsersDisplay[idx + 1][index + 1].plan_mode==3" @click.stop="()=>{}"   @change="updatePlan(selectUsersDisplay[idx + 1][index + 1])"   v-model="selectUsersDisplay[idx + 1][index + 1].score" />
                                                                <vs-input class="inputx w-full" v-else  disabled   name="subject"  />
                                                                <span style="padding-left: 10px">人</span>
                                                            </div>
                                                        </div>
                                                        <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 2)" class="final"> 最終</span>
                                                        <a href.prevent v-on:click.stop="onRemoveSelectUserClick(selectUsersDisplay[idx + 1][index + 1].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                                    </div>
                                                    <!--PAC_5-1698 E-->
                                                </vs-col>
                                            </vs-row>
                                            <vs-row class="row-drop-area"
                                                    vs-type="flex"
                                                    vs-justify="center" >
                                                <vs-col vs-w="10"
                                                        vs-type="flex"
                                                        vs-align="flex-start"
                                                        class="drop-area hidden item item-dropable" >
                                                    <div @drop="onDrop(selectUsersDisplay[idx + 1][0], selectUsersDisplay[idx + 1].length + 1, true, $event)"
                                                         @dragover="overDrag(selectUsersDisplay[idx + 1][0], selectUsersDisplay[idx + 1].length + 1, true, $event)"
                                                         @dragenter.prevent class="dropable-item h-full w-full">
                                                        <div>&nbsp;</div>
                                                    </div>
                                                </vs-col>

                                            </vs-row>
                                        </div>
                                    </vs-col>
                                </vs-row>
                              <!-- 特設サイト受取側組織名表示 start -->
                              <vs-row
                                  vs-type="flex"
                                  vs-align="space-around"
                                  v-if="specialCircularFlg"
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

                                <vs-row vs-type="flex"
                                        vs-align="space-around"
                                        class='group'>
                                    <vs-col vs-w="6">
                                        <vs-row vs-type="flex" vs-justify="space-around">
                                            <vs-col vs-w="10"
                                                    class="drop-area item item-dropable hidden"
                                                    vs-type="flex"
                                                    vs-align="flex-start">
                                                <div @drop="onDrop(null, -1, true, $event)" @dragover="overDrag(null, -1, true, $event)"
                                                     @dragenter.prevent class="dropable-item h-full w-full">
                                                    <div>&nbsp;</div>
                                                </div>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                </vs-row>
                            </div>
                        </div>

                        <div slot="no-body">
                        </div><br>
                        <div class="mail-form" v-if="infoCheck['addressbook_only_flag'] == 0 && enable_any_address!=2　&& !isTemplateRoute">
                            <form v-if="!checkShowAddress">
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
                                <vs-row class="mt-4" v-if="!isTemplateRoute">
                                    <vs-col vs-type="flex" vs-w="6" vs-xs="12">
                                        <vs-checkbox :value="isTemplateCircular ? false : addToContactsFlg" v-on:click="addToContactsFlg = !addToContactsFlg" :disabled="isTemplateCircular">回覧時にアドレス帳に追加</vs-checkbox>
                                    </vs-col>
                                    <vs-col vs-type="flex" vs-w="6" vs-xs="12" vs-justify="flex-end" vs-align="center">
                                        <vs-button @click.prevent="submitMailStepForm" class="square mr-0"  color="primary" type="filled" :disabled="isTemplateCircular"> 追加</vs-button>
                                    </vs-col>
                                </vs-row>
                            </form>
                        </div>
                    </div>
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
                    <div class="mail-steps" v-show="searchAreaFlg">
                        <div class="mail-view-list">
                            <draggable v-model="selectUserView" >
                                <vs-row vs-w="12" class="item me item-user-view" vs-type="flex" v-for="(user, index) in selectUserView" v-bind:key="user.email + index" :index="index">
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
                                    <!-- PAC_5-1982 S -->
                                    <vs-col vs-type="flex" vs-w="12" vs-xs="12" vs-justify="flex-end" vs-align="flex-end">
                                      <vs-button  style="margin-right: 0.5rem !important;" class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto mr-0 pl-6 sm:pl-8 md:pl-8 lg:pl-8 xl:pl-8 ipad_size" color="primary"
                                                  type="filled" @click="onViewFavoriteSelect()">お気に入り追加</vs-button>
                                      <vs-button  style="margin-right: 0.5rem !important;" class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto mr-0 pl-6 sm:pl-8 md:pl-8 lg:pl-8 xl:pl-8 ipad_size" v-bind:disabled="selectUserView.length < 1" color="primary"
                                                 type="filled" @click="onViewShowAddFavorite()">お気に入り登録＋</vs-button>
                                      <!-- PAC_5-1982 E -->
                                      <vs-button @click.prevent="addUserView" class="square mr-0"  color="primary" type="filled" > 追加</vs-button>
                                    </vs-col>
                                </vs-row>
                              <!-- PAC_5-1982 S -->
                              <vs-popup class="application-page-dialog" title="お気に入り"  :active.sync="confirmViewEdit">
                                <ViewFavorite ref="searchViewFavorite" :opened="confirmViewEdit" :select-user-view="selectUserView" :max-viewer="maxViewer" :select-users="selectUsers" :login-user="loginUser"/>
                              </vs-popup>
                              <!-- PAC_5-1982 E -->
                            </form>
                        </div>
                    </div>
                </vx-card>
            </div>
        </div>
        <div class="vx-row">
            <div class="vx-col w-full lg:w-1/2 mb-8 sm:mb-0 md:mb-0 lg:mb-0 lg:pr-0">
                <vx-card class="mb-4">
                    <vs-row class="border-bottom pb-4">
                        <h4>件名・メッセージ</h4>

                    </vs-row>
                    <vs-row class="mb-4 mt-6">
                        <vs-input class="inputx w-full" placeholder="件名をつけて送信できます。" v-validate="'max:50|emoji'" name="subject"  v-model="commentTitle" @change="toChangeCommentTitle($event)" />
                        <span class="text-danger text-sm" v-show="errors.has('subject')">{{ errors.first('subject') }}</span>
                    </vs-row>
                    <vs-row style="margin-bottom: 16px">
                        <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="commentContent" v-validate="'max:500'" name="content" @change="changeCommentContent" style="margin-bottom: 0" />
                        <span class="text-danger text-sm" v-show="errors.has('content')">{{ errors.first('content') }}</span>
                    </vs-row>
                    <vs-row class="mb-6">
                        <!--PAC_5-1413 欄外クリック時にボックスが閉じない　vuesax側のバグの為、vue selectに切り替え-->
                        <div class="w-full">
                          <v-select :options="emailTemplateOptions" :clearable="false" :searchable ="false" :value="selectedComment" @input="onChangeEmailTemplate" />
                        </div>
                    </vs-row>
<!--                    <vs-checkbox :value="addToCommentsFlg" v-on:click="addToCommentsFlg = !addToCommentsFlg">社内のみ閲覧可</vs-checkbox>-->
                  <vs-row>※メッセージは次の回覧者への送信メールに記載されます。<br/>
                    　また、プレビュー画面「コメント」タブの「社内宛」に表示されます。</vs-row>
                </vx-card>
            </div>

            <div class="vx-col w-full lg:w-1/2 mb-4">
                <vx-card class="mb-4" style="height: 100%">
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
                    <vs-row class="border-bottom pb-4">
                        <h4>再通知設定</h4>
                    </vs-row>
                    <vs-row class="mb-4 mt-6">
                        <vs-col vs-w="6" vs-xs="10">
                            <vx-input-group  class="w-full mb-0">
                                <flat-pickr ref="calendar" class="w-full" :config="configdateTimePicker" v-model="reNotificationDay" @on-change="changeReNotificationDay"></flat-pickr>
                                <template slot="append">
                                    <div class="append-text btn-addon">
                                        <vs-button data-toggle color="primary" v-on:click="openCalendarClick"><i class="fas fa-calendar-alt"></i></vs-button>
                                    </div>
                                </template>
                            </vx-input-group>

                        </vs-col>
                        <vs-col vs-w="2" vs-xs="2">
                            <vs-button v-tooltip.top-center="{
                                html: true,
                                content: '設定日にまだ回覧が終了していない場合、<br/>その時点で回覧中の宛先にメールで通知します。<br/>設定日以降も回覧終了になるまで通知します。'
                            }" radius color="danger" type="filled" style="width: 37px">
                                <i class="fas fa-info"></i>
                            </vs-button>
                        </vs-col>
                    </vs-row>
                </vx-card>
            </div>
        </div>
        <modal name="confirm-no-comment-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'confirm-no-comment-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="block" v-if="!this.commentTitle">
                    <p>件名が未入力ですが、このまま申請しますか？</p>
                </vs-col>
                <vs-col vs-w="12" vs-type="block" v-if="this.commentTitle">
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
        <div style="margin-bottom: 15px">
            <vs-row>
                <vs-col vs-type="flex" vs-lg="6" vs-xs="12">

                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-lg="6" vs-xs="12">
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="goBack"> 戻る</vs-button>
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  style="color:#fff;" color="#22AD38" type="filled" :disabled="clickState" v-on:click="onSaveCircularClick"><i class="fas fa-check"></i> 申請する</vs-button>
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
        <vs-popup class="holamundo"  title="メッセージ" :active.sync="showPopupErrosNoAddress">
            <vs-row>
                <p>回覧先を省略することはできません</p>
            </vs-row>
            <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosNoAddress = false"> 閉じる</vs-button>
            </vs-row>
        </vs-popup>
        <vs-popup class="holamundo"  title="エラー" :active.sync="showPopupErrosEmojiLength">
          <vs-row>
            <p>絵文字は入力できません。</p>
          </vs-row>
          <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="showPopupErrosEmojiLength = false"> 閉じる</vs-button>
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
                          <!--PAC_5-2795 S-->
                            <div> ■ - {{user.name || '社員'}}</div>
                          <!--PAC_5-2795 E-->
                            <div>【{{user.email}}】</div>
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
        <!-- PAC_5-1982 閲覧ユーザー設定に「」気に入り登録＋」を追加 S -->
        <vs-popup class="template_preview_dialog"  title="お気に入り名称" :active.sync="addViewFavoriteFlg">
          <vs-row style="margin-bottom: 12px;width: 100%">
            <vs-col class="mt-2 w-full " vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="12" style="width:100%">
              <span style="width:15%;">名称：</span>
              <vx-input-group  class="mb-0  w-full">
                <vs-input v-model="addViewFavoriteNameVal"  maxlength="20" />
              </vx-input-group>
            </vs-col>
          </vs-row>
          <vs-row vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button @click="onAddViewFavorite()" v-bind:disabled="!addViewFavoriteNameVal || addViewFavoriteNameVal.length > 20" color="primary">登録</vs-button>
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="addViewFavoriteFlg = false;addViewFavoriteNameVal = ''"> 閉じる</vs-button>
          </vs-row>
        </vs-popup>
        <!-- PAC_5-1982 E -->

        <vs-popup title="回覧順編集" :active.sync="showEditFavorite" class="detail-popup">
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
</template>

<script>
    import { mapState, mapActions } from "vuex";
    import config from "../../app.config";
    import Axios from "axios";
    //import LiquorTree from 'liquor-tree';
    import draggable from 'vuedraggable';

    import VueSuggestion from 'vue-suggestion'

    import flatPickr from 'vue-flatpickr-component'
    import 'flatpickr/dist/flatpickr.min.css';
    import {Japanese} from 'flatpickr/dist/l10n/ja.js';

    import usernameTemplate from './username-suggest-template.vue';
    import emailTemplate from './email-suggest-template.vue';
    import ContactTree from '../../components/contacts/ContactTree';
    import { Validator } from 'vee-validate'
    import Utils from '../../utils/utils';
    import {userService} from "../../services/user.service";
    import _ from "lodash";
    import ViewFavorite from "../../components/v-favorite/ViewFavorite";
    const dict = {
        custom: {
            subject: {
                 max: "50文字以上は入力できません。",
                 emoji:'絵文字は入力できません'
            },
            content: {
                max: "500文字以上は入力できません。"
            }
         }
    };
    const reg=/[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF][\u200D|\uFE0F]|[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF]|[0-9|*|#]\uFE0F\u20E3|[0-9|#]\u20E3|[\u203C-\u3299]\uFE0F\u200D|[\u203C-\u3299]\uFE0F|[\u2122-\u2B55]|\u303D|[\\A9|\\AE]\u3030|\\uA9|\\uAE|\u3030/ig
    Validator.localize('ja', dict)
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
            flatPickr,
            VueSuggestion,
            ContactTree,
            ViewFavorite,
        },
        data() {
            return {
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
                confirmViewEdit:false,// PAC_5-1982
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
                isTemplateCircular: false, // 現在の回覧は合議ですか？
                templateSearchModel: '',
                myCompanyInfo: {
                  template_route_flg:1
                },
                selectTemplateRoutes: [],
                tableshow:{},
                searchFavorite:'',
                addFavoriteFlg:false,
                addViewFavoriteFlg:false,//PAC_5-1982
                addViewFavoriteNameVal:'',//PAC_5-1982
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
                circularSetting:{},
                notificationDay:'',
                isSendAll: false,
                selectAllSendUsersDisplay: [],
                isShowCurrent: 0,
                /*PAC_5-2616 S*/
                enable_any_address:0,
                /*PAC_5-2616 E*/
                /*PAC_5-2705 S*/
                selectUsersCheck: 0,
                /*PAC_5-2705 E*/
                showEditFavorite: false,
                editFavoriteItem: [],
                editFavoriteItemIndex: 0,
                draggingFavoriteUser: null,
                showPopupErrosEmojiLength:false,
                isTemplateRoute:false,
                popupSelectFavoriteAccountActive: false,//popupのflag
                favoriteEmailSelect: '',//選択するメール
                favoriteEmailSelects: [],//選択する複数のメール
                favoriteCheckEmailExisting: [],//選択する現在のデータ
                favoriteAllEmailSelects: [],//選択するお気に入りのすべてのメール
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
            companyLimit(){
                let obj={
                    text_append_flg:this.circular?this.circular.limit_text_append_flg:0
                }
                return obj;
            },
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
                getCircularSetting: "application/getCircularSetting",
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
                updateFavorite: "favorite/updateFavorite",
                sortFavoriteItem: "favorite/sortFavoriteItem",
                deleteFavoriteItem: "favorite/deleteFavoriteItem",
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
            //PAC_5-582 保護設定保存 Start
            //メッセージ
            async changeCommentContent(){
                 this.toSaveCircularSetting();
                 this.$store.commit('application/updateCommentContent', this.commentContent);
            },
            // PAC_5-2245 Start
            //捺印設定
            async changeRequirePrint() {
                this.protectionSetting.require_print = !this.protectionSetting.require_print;
                this.toSaveCircularSetting();
                this.$store.commit('application/updateRequirePrint', this.protectionSetting.require_print);
            },
            //PAC_5-582 保護設定保存 End
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
                if (this.commentContent == null){
                    this.commentContent = '';
                }
                this.commentContent = this.commentContent.concat(value);
                this.selectedComment = value +' '
                this.toSaveCircularSetting();
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

                if (this.isSendAll == true && (this.$store.state.home.circular.users.length + 1) > 31) {
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
                result = this.checkEnvironmentalSelectFlg(result,0);
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
            onTreeAddToStepClick: async function(userChecked) {
                // 現在の回覧は合議です、 アドレス帳から追加できません
                if(this.isTemplateCircular){
                    this.confirmEdit = false;
                    // 宛先、回覧順に合議でないものが存在するため、アドレス帳から追加できません。
                    this.showPopupErrorTemplateMessage = "アドレス帳から追加";
                    this.showPopupErrorTemplate = true;
                    return false;
                }
                if (this.isSendAll && userChecked && userChecked[0].is_plan > 0) {
              this.confirmEdit = false;
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `宛先、回覧順に合議先が存在するため、
一斉送信できません。`,
                        accept: () => {
                        }
                    });
                    return;
                }
                if ((this.isSendAll && (this.$store.state.home.circular.users.length + userChecked.length) > 31)) {
                    this.confirmEdit = false;
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

                $(".application-page-dialog .vs-popup--close").click();
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
                    if (userChecked[0].is_plan == 1 && userChecked.length>30){
                      this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `メッセージ`,
                        acceptText: '閉じる',
                        text: `1つの合議に設定できる人数は最大30人です。`,
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
                    result = this.checkEnvironmentalSelectFlg(result,userChecked[0].is_plan);
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
                  selectedEmails.forEach((email)=>{
                    if (!result.some(item => item.email.toLowerCase() == email.toLowerCase())){
                      result.push(this.mapUserEmail[email])
                    }
                  })

                  let validatePlanWithOthre=result.some(user=>{
                    return (user.company_id==null || (user.edition_flg != config.APP_EDITION_FLV || user.env_flg != config.APP_SERVER_ENV || user.server_flg != config.APP_SERVER_FLG
                        || user.company_id!=this.loginUser.mst_company_id)) && user.is_plan>0
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
                    /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ call function to validate multiple circular user before call api one time */
                    var users2Add = [];
                    if (userChecked.length > 0 && userChecked[0].is_plan && userChecked[0].is_plan > 0){
                      result = result.map(item=>{
                          item.is_plan=1
                        return item
                      })
                    }
                    const iterable = () => {
                        let item = null;
                        if (userChecked.length > 0 && userChecked[0].is_plan && userChecked[0].is_plan > 0){
                          item = result.shift();
                        }else {
                          item = userChecked.shift();
                        }
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
            checkEnvironmentalSelectFlg(arrAccount,is_plan) {
                const limit = JSON.parse(getLS('limit'));
                let uniqs = null;
                if(arrAccount && arrAccount.length > 0) {
                  if(limit && limit.environmental_selection_dialog == 0){
                    uniqs = arrAccount.filter((user) => {
                      return user.edition_flg === this.selectUsers[0].edition_flg
                    });
                  }
                  if (is_plan > 0){
                    uniqs = arrAccount.filter((user) => {
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
            //PAC_5-582 保護設定保存 Start
            //アクセスコード保護（社内用）
            changeAccessCodeFlg: function(){
                this.accessCodeFlg = !this.accessCodeFlg;
                if(this.accessCodeFlg && (this.accessCode == '' || this.accessCode == null)){
                    this.generateAccessCode();
                }else{
                    this.toSaveCircularSetting();
                }
            },
            //アクセスコード保護（社外用）
            changeOutsideAccessCodeFlg: function(){
                this.outsideAccessCodeFlg = !this.outsideAccessCodeFlg;
                if(this.outsideAccessCodeFlg && (this.outsideAccessCode == '' || this.outsideAccessCode == null)){
                    this.generateAccessCodeOutside();
                }else{
                    this.toSaveCircularSetting();
                }
            },
            //宛先、回覧順の変更許可
            changeDestinationFlg() {
                this.allowChangeDestinationFlg = !this.allowChangeDestinationFlg;
                this.toSaveCircularSetting();
            },
            //テキスト追加許可
            changTextAppendFlg() {
                this.protectionSetting.text_append_flg = !this.protectionSetting.text_append_flg;
                this.toSaveCircularSetting();
            },
            //メール内の文書のサムネイル表示
            changShowThumbnailFlg() {
                this.showThumbnailFlg = !this.showThumbnailFlg;
                this.toSaveCircularSetting();
            },
            //件名
            toChangeCommentTitle(e) {
              reg.lastIndex = 0
              const isEmoj = reg.test(e.target.value)
              isEmoj &&(this.commentTitle=e.target.value.replace(reg,"").trim().replace(/\s/g,""))
                this.toSaveCircularSetting();
                this.changeCommentTitle();
            },
            //再通知設定日付
            changeReNotificationDay() {
                if(this.reNotificationDay != this.notificationDay){
                    this.toSaveCircularSetting();
                    this.notificationDay = this.reNotificationDay;
                }
            },
            //保護設定保存
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
            //PAC_5-582 保護設定保存 End
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
                if(this.isSendAll == true){
                    data.allSendUser = this.selectAllSendUsersDisplay;
                    data.isSendAllUser = true;
                }
                await this.sendNotifyFirst(data).then(ret => {
                    if(ret && this.info && !this.info.operation_notice_flg == 1) {
                        this.$store.dispatch('home/clearState', null);
                        this.$router.push('/');
                    }
                });
                if(this.checkOperationNotice && this.info.operation_notice_flg){
                    await this.$modal.show('confirm-sent-modal');
                }
            },

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
                reg.lastIndex = 0
                if(reg.test(this.commentTitle)){
                  this.showPopupErrosEmojiLength = true;
                  this.clickState = false
                  return false;
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
                this.toSaveCircularSetting();
            },
            generateAccessCodeOutside: function() {
                this.outsideAccessCode = this.getAccessCode(6);
                this.toSaveCircularSetting();
            },
            goBack: function() {
                if(this.circular && this.selectUserView ){
                    this.$store.commit('home/updateCircularChangeListUserView',{id:this.circular.id,data:this.selectUserView});
                }
                if(this.circular) this.$router.push(`/saves/${this.circular.id}`);
                else this.$router.push(`/`);
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
            /*PAC_5-1982 S*/
            onViewShowAddFavorite(){
              this.addViewFavoriteFlg = true;
            },
            /*PAC_5-1982 E*/
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
          /* PAC_5-1982 S*/
          async onAddViewFavorite(){
            
            let str_key = "";

            // get list user
            let items = this.selectUserView.map((user, index) => {
              if(index!=0) str_key += user.email;
              return { favorite_name:this.addViewFavoriteNameVal,favorite_flg:1,name: user.name, email: user.email, email_company_id: this.loginUser.mst_company_id, email_company_name: this.loginUser.mst_company_name, email_user_id: user.mst_user_id,plan_id:0 }
            });
            this.addViewFavoriteFlg = false;
            this.addViewFavoriteNameVal = '';
            await this.addFavorite({items});
            this.$refs.searchViewFavorite.searchFavorite = '';
          },
          /* PAC_5-1982 E*/
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
            /* PAC_5-1982 S*/
            async onViewFavoriteSelect(){
              this.confirmViewEdit = true;
            },
            /* PAC_5-1982 E*/
          
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
                if (this.isSendAll == true && (this.$store.state.home.circular.users.length + 1) > 31) {
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

                let newFavorite = [];
                let vCloneF = _.cloneDeep(itemFavorite);
                for (const v of vCloneF) {
                    if (v.plan_users){
                        v.plan_users.forEach(user=>{
                            user.plan_users = [];
                        })
                        newFavorite.push(...v.plan_users);
                        continue;
                    }
                    newFavorite.push(v);
                }
              //お気に入りの場合
              let data = {favorite: newFavorite, usingHash: this.$store.state.home.usingPublicHash};
              let result = await Axios.post(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public' : ''}/user/checkFavoriteUserStatus`, data)
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
                if(result.status == false){
                  return ;
                }
                for (const v of itemFavorite) {
                    if (v.plan_users){
                        if(this.isSendAll == true){
                          this.$vs.dialog({
                            type: 'alert',
                            color: 'danger',
                            title: `確認`,
                            acceptText: '閉じる',
                            text: `宛先、回覧順に合議先が存在するため、
一斉送信できません。`,
                            accept: () => {}
                          });
                          return;
                        }
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
                        result = this.checkEnvironmentalSelectFlg(result,0);
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
                if(this.isSendAll){this.$vs.loading()}
                let plan_list = (await Axios.get(`${config.BASE_API_URL}/circulars/${this.circular.id}/get_plan`)).data.data
                this.selectUsersDisplay.length = 0;
                this.selectAllSendUsersDisplay.length = 0;
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

                    if(this.isSendAll){
                      newSelectUser = arrSelectUsers.reduce((accumulator, user) => {
                        accumulator[0] = accumulator[0] || [];
                        accumulator[0].push(user);
                        return accumulator
                      }, []);
                      newSelectUser = newSelectUser.filter(function (el) {
                        return el != null;
                      });
                      newSelectUser[0].shift();
                      this.selectAllSendUsersDisplay.push.apply(this.selectAllSendUsersDisplay, newSelectUser);
                      this.$vs.loading.close()
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
              if(hasOutsideUser > 0 || this.specialCircularFlg){
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
            async changeSendAllFlg(){
                if (this.isTemplateCircular) {
                    this.isSendAll = false;
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `宛先、回覧順に承認ルートが存在するため、
                    一斉送信できません。`,
                        accept: () => {
                        }
                    });
                    return;
                }
                if (this.$store.state.home.circular.users.length > 31) {
                    this.isSendAll = false;
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

                for (let index in this.selectUsersDisplay[0]) {
                    if (this.selectUsersDisplay[0][index].is_plan) {
                        this.isSendAll = false;
                        this.$vs.dialog({
                            type: 'alert',
                            color: 'danger',
                            title: `確認`,
                            acceptText: '閉じる',
                            text: `宛先、回覧順に合議先が存在するため、
                        一斉送信できません。`,
                            accept: () => {
                            }
                        });
                        return;
                    }
                }
            },
        async updatePlan(user){

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
                let res= await Axios.post(`${config.BASE_API_URL}/circulars/`+user.circular_id+'/plan', user , {data:{nowait: true} })
        },
        async deletePlan(user){
                await Axios.get(`${config.BASE_API_URL}/circulars/${user.circular_id}/del_plan/${user.plan_id}`)
            },
        showPlanDetail(list){
                this.planDetailShow=true
                this.planDetailList=list
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
              let result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: selectedEmails})
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
          /*PAC_5-2616 S*/
          confirmEdit:function (val){
            if (val && this.enable_any_address == 2 ){
              this.$nextTick(()=>{
                if (this.$refs.template_route){
                  this.onTemplateSelect()
                }
              })
            
            }
          },
          /*PAC_5-2616 S*/
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
          showEditFavorite:function(newVal){
            this.confirmEdit = !newVal;
          },
        },
        async created() {
            var limit = getLS("limit");
            limit = JSON.parse(limit);
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            this.$store.commit('application/updateListUserView', []);
            // 特設サイト

            if(this.circular && this.circular.special_site_flg){
              this.specialCircularFlg = true;
              this.outsideAccessCodeShowFlg = true;
              this.outsideAccessCodeFlg = true;
            }
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
            if(this.selectUsers && this.selectUsers.length>1){
              this.isTemplateRoute = this.selectUsers[1].template_id;
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
            if(this.circular && this.protectionSetting) {
              // デフォルト値:OFF
              // this.showThumbnailFlg = this.protectionSetting.enable_email_thumbnail;
              this.allowChangeDestinationFlg = this.protectionSetting.destination_change_flg;
              this.accessCode = this.circular.access_code ? this.circular.access_code : this.accessCode;
              // PAC_5-1115 メールリダイレクト時認証フラグに関わらず、アクセスコード保護設定のみを参照する
              //if(limit && limit.link_auth_flg == 0){
              //    this.accessCodeFlg = true;
              //    this.outsideAccessCodeFlg = true;
              //    this.checkAuthFlg = true;
              //}else{
                this.accessCodeFlg = this.protectionSetting.access_code_protection;
                this.outsideAccessCodeFlg = this.protectionSetting.access_code_protection;
              // PAC_5-1115 終了

              //check hide thumbnail images in email by default
              // if(!this.showEmailThumbnailOption){
              //     this.showThumbnailFlg = false;
              // }
              // await this.addLogOperation({ action: 'r08-display', result: 0});
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
            // PAC_5-582 保護設定取得 Start
            this.circularSetting = await this.getCircularSetting();
            if(this.circular && this.protectionSetting && this.circularSetting && this.circularSetting.length) {
                //保護設定の変更を申請時に許可する
                if(this.protectionSetting.protection_setting_change_flg && this.circularSetting[0].outside_access_code!=null){
                    this.allowChangeDestinationFlg = this.circularSetting[0].address_change_flg;
                    this.protectionSetting.text_append_flg = (this.settingLimit?this.settingLimit.text_append_flg==1:false)?this.circularSetting[0].text_append_flg:this.protectionSetting.text_append_flg;
                    this.showThumbnailFlg = this.showEmailThumbnailOption?(!this.circularSetting[0].hide_thumbnail_flg):0;
                    this.protectionSetting.require_print = this.circularSetting[0].require_print;
                    this.accessCodeFlg = this.circularSetting[0].access_code_flg;
                    this.accessCode = this.accessCodeFlg?this.circularSetting[0].access_code:'';
                    this.outsideAccessCodeFlg = this.circularSetting[0].outside_access_code_flg;
                    this.outsideAccessCode = this.outsideAccessCodeFlg?this.circularSetting[0].outside_access_code:'';
                }
            this.notificationDay = this.circularSetting[0].re_notification_day;
            this.reNotificationDay = this.circularSetting[0].re_notification_day;
            this.commentTitle = this.circularSetting[0].title?this.circularSetting[0].title:'';
            this.commentContent = this.circularSetting[0].text?this.circularSetting[0].text:'';
            await this.addLogOperation({ action: 'r08-display', result: 0});
            }
            // PAC_5-582 End
            // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
            if(this.circular && this.circularChangeListUserView && this.circularChangeListUserView[this.circular.id]){
                this.$store.commit('application/updateListUserView', this.circularChangeListUserView[this.circular.id]);
                if(this.circularChangeListUserView[this.circular.id].length > 0){
                    this.onAroundArrow();
                }
                this.$store.commit('home/updateCircularChangeListUserView',{id:this.circular.id,data:[]});
            }
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
    .templateButton{
        width:85px;
    }
</style>
<style lang="scss">
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
/*PAC_5-2206 ボタンをipad・ipadproで横表示なるように調整の為追加*/
@media (max-width:1024px){
  .ipad_size:not(.vs-radius):not(.includeIconOnly):not(.small):not(.large) {
    padding: .75rem 0.2rem;
    font-size : 0.8rem;
    margin-right: 0.2rem;
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
/*PAC_5-2795 合議設定について文言のサイズ変更　s*/
.plan-left-remark{
  font-size: .9rem;
  line-height: 1.25;
}
/*PAC_5-2795 合議設定について文言のサイズ変更　e*/
</style>

<style>
.vs-dialog-text{
    white-space: pre-line;
}
</style>
