<template>
    <div>
    <div id="sendback-page" :class="isMobile?'mobile':''">
        <vs-card style="margin-bottom: 0">
            <vs-row class="top-bar">
                <vs-col vs-type="flex" vs-w="9" vs-xs="12" vs-sm="6" vs-align="center" vs-justify="center" class="mb-3 sm:mb-0 md:mb-0 lg:mb-0">
                    <ul class="breadcrumb">
                        <li><p style="color: #27ae60;"><span class="badge badge-success">1</span> プレビュー・捺印</p></li>
                        <li><p style="color: #0984e3;"><span class="badge badge-primary">2</span> 回覧先設定</p></li>
                        <li><p style="background: transparent"></p></li>
                    </ul>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="3" vs-xs="12" vs-sm="6">
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="primary" type="filled" v-on:click="onBackClick"> 戻る</vs-button>
                    <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="danger" type="filled" :disabled="clickState" v-on:click="onSendClick"><i class="fas fa-check"></i> 差戻し</vs-button>
                </vs-col>
            </vs-row>
        </vs-card>
        <div class="vx-row">
            <div class="vx-col mb-4 lg:pr-0 w-full">
                <vx-card class="h-full">
                    <vs-row class="border-bottom pb-4">
                        <h4>差戻し先 <span class="text-danger">*</span></h4>
                    </vs-row>
                    <vs-table class="w-full mt-4" ref="table" :data="selectUsers" v-if="!isTemplateCircular">
                        <template slot="thead">
                            <vs-th>回覧順</vs-th>
                            <vs-th>名前</vs-th>
                            <vs-th>メールアドレス</vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <tbody>
                            <tr @click="onRowClick(tr)" :class="((((circularUserLastSend.parent_send_order - tr.parent_send_order) == 1 && circularUserLastSend.child_send_order == 1 && circularUserLastSend.circular_status !== CIRCULAR_USER.REVIEWING_STATUS)|| (circularUserLastSend.parent_send_order == tr.parent_send_order && tr.child_send_order < circularUserLastSend.child_send_order) || (circularUserLastSend.circular_status == CIRCULAR_USER.REVIEWING_STATUS && tr.parent_send_order == circularUserLastSend.parent_send_order)) ? '' : ' disabled') + (tr.id === rowSelected || (tr.plan_users && tr.plan_users.find(user=>{return user.id === rowSelected})) ? ' row-selected': '')" :data="tr" :key="indextr" v-for="(tr, indextr) in data">

                                <vs-td>
                                    <p class="product-name font-medium truncate">#{{ indextr + 1 }}</p>
                                </vs-td>

                                <vs-td>
                                    <p class="product-category"  v-for="(u,i) in tr.plan_users" :key="i + u.name">{{ u.name }}</p>
                                    <p class="product-category" v-if="!tr.plan_users">{{ tr.name }}</p>
                                </vs-td>

                                <vs-td>
                                    <p class="product-category"  v-for="(u,i) in tr.plan_users" :key="i + u.email">{{ u.email }}</p>
                                    <p class="product-category" v-if="!tr.plan_users">{{ tr.email }}</p>
                                </vs-td>

                            </tr>
                            </tbody>
                        </template>
                    </vs-table>
                    <!-- template route users start -->
                    <vs-table class="w-full mail-steps" :data="templateUserRoutesWithApplicant" v-if="isTemplateCircular">
                        <template slot="thead">
                            <vs-th>回覧順</vs-th>
                            <vs-th>名前</vs-th>
                            <vs-th>メールアドレス</vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <tbody>
                            <template v-for="(trs, indextr) in data">
                                <tr @click="onTemplateRowClick(trs)" :class="((((circularUserLastSend.parent_send_order - trs[0].parent_send_order) == 1 && circularUserLastSend.child_send_order == 1)|| (circularUserLastSend.parent_send_order == trs[0].parent_send_order && trs[0].child_send_order < circularUserLastSend.child_send_order)) ? '' : ' disabled') + (trs[trs.length - 1].id === rowSelected ? ' row-selected': '')" :data="trs" :key="indextr">

                                    <vs-td>
                                        <p class="product-name font-medium truncate">#{{ indextr + 1 }}</p>
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
                        <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="emailContent" />
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
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSaveFileFailNoticeClick" > 閉じる</vs-button>
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
    </div>

        <!-- 5-277 mobile html -->
        <div id="sendback-page-mobile" :class="isMobile?'mobile':''">
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
                                <tr @click="onRowClick(tr)" :class="((((circularUserLastSend.parent_send_order - tr.parent_send_order) == 1 && circularUserLastSend.child_send_order == 1 && circularUserLastSend.circular_status !== CIRCULAR_USER.REVIEWING_STATUS)|| (circularUserLastSend.parent_send_order == tr.parent_send_order && tr.child_send_order < circularUserLastSend.child_send_order) || (circularUserLastSend.circular_status == CIRCULAR_USER.REVIEWING_STATUS && tr.parent_send_order == circularUserLastSend.parent_send_order)) ? '' : ' disabled') + (tr.id === rowSelected || (tr.plan_users && tr.plan_users.find(user=>{return user.id === rowSelected})) ? ' row-selected': '')" :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <vs-td>
                                        <p class="product-category"  v-for="(u,i) in tr.plan_users" :key="i + u.name">{{ u.name }}</p>
                                        <p class="product-category" v-if="!tr.plan_users">{{ tr.name }}</p>
                                    </vs-td>

                                    <vs-td>
                                        <p class="product-category"  v-for="(u,i) in tr.plan_users" :key="i + u.email">{{ u.email }}</p>
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
                        <vs-textarea class="bg-white" placeholder="コメントをつけて送信できます。" rows="4" v-model="emailContent" />
                    </vs-row>
                </div>
                <div class="vx-col w-full mb-0 action">
                    <vs-row class="mt-4">
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12" vs-xs="12" vs-sm="12" vs-lg="12">
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="primary" type="filled" v-on:click="onBackClick"><div><img :src="require('@assets/images/mobile/back_white.svg')"></div><div>戻る</div></vs-button>
                            <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"  color="primary" type="filled" v-on:click="onSendClick"><div><img :src="require('@assets/images/mobile/refund_white.svg')"></div><div>差戻し</div></vs-button>
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
                    <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSaveFileFailNoticeClick" > 閉じる</vs-button>
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
    directives: {

    },
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
        isMobile: false
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
        fileSelected: state => state.home.fileSelected,
        circular: state => state.home.circular,
        departmentUsers: state => state.application.departmentUsers,
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
              circular_user = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS  || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS  || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS);
          }
        if(!circular_user) return  null;
        this.$store.commit('home/updateCurrentParentSendOrder', circular_user?circular_user.parent_send_order:0);
        return circular_user;
      },
      allowAddDestination: {
        get() {
          if(!this.circular) return false;
          return this.circular.address_change_flg;
        }
      },
      selectUsers: {
        get() {
          if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
          if(!this.circularUserLastSend) return [];
          // TODO review
          let plan=_.cloneDeep(this.$store.state.home.circular.plans);
          let circularUsers=_.cloneDeep(this.$store.state.home.circular.users)
          circularUsers.forEach(user=>{
              if (plan[user.plan_id] && plan[user.plan_id].id==user.plan_id){
                  plan[user.plan_id].users=plan[user.plan_id].users || [];
                  plan[user.plan_id].users.push(Object.assign({},user));
                  plan[user.plan_id].is_add=false;
              }
            })
            let newCircularUsers = circularUsers.map(user=>{
                if (plan[user.plan_id] && plan[user.plan_id].id==user.plan_id){
                    if (plan[user.plan_id].is_add){
                        return null
                    }else{
                        user.plan_mode=plan[user.plan_id].mode
                        user.plan_score=plan[user.plan_id].score
                        user.plan_users=plan[user.plan_id].users
                        plan[user.plan_id].is_add=true
                        return user
                    }
                }else{
                    return user
                }
            }).filter(user=>{
                return user!=null
            })
            let users= newCircularUsers.filter(item => {
                return item.child_send_order === 0 || this.circularUserLastSend.parent_send_order === item.parent_send_order || (item.parent_send_order && item.child_send_order === 1);
            });
          return users
        },
        set(value) {this.updateCircularUsers(value)}
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
        templateUserRoutesWithApplicant: {
            get() {
                let newArrUsers = [];
                if(this.isTemplateCircular){
                    // 合議の場合、同じ企業、parent_send_order同じです
                    let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                    for(let i = 0;i < arrUsers.length;i ++){
                        let child_send_order = arrUsers[i].child_send_order;
                        if(!Object.prototype.hasOwnProperty.call(newArrUsers, child_send_order)){
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
            set(value) {this.updateCircularUsers(value)}
        },
    },
    methods: {
      ...mapActions({
        saveFileAndSignature: "home/saveFileAndSignature",
        sendBack: "application/sendBack",
        addLogOperation: "logOperation/addLog",
        getMyInfo: "user/getMyInfo",
        getInfoByHash: "user/getInfoByHash",
      }),
      onChangeEmailTemplate(value) {
        this.selectedComment = value;
        this.emailContent = this.emailContent.concat(value);
          this.selectedComment = value +' '
        //this.optionSelected = '';
      },
      onBackClick: async function() {
        if (this.previousRoute){
            this.previousRoute.query.back = true;
            this.$router.push(this.previousRoute);
        }else{
            this.$router.back()
        }
      },
      onSendClick: async function() {
        //二重チェック
        this.clickState = true;
        this.$store.commit('home/checkCircularUserNextSend', false);
          let res = await this.saveFileAndSignature(true);
          if(!res){
              await this.$modal.show('save-file-fail-notice-modal');
              this.clickState = false;
              return;
          }
          if (res !== true) {
              if (res.statusCode == 406) {
                  await this.$modal.show('sync-operation-modal');
                  return;
              }
          }
        // 最初回覧文書取得
        let circular_document_id = 0;
        this.$store.state.home.files.forEach(function (item){
          if(circular_document_id == 0 || circular_document_id > item.circular_document_id){
            circular_document_id = item.circular_document_id;
          }
        });
        const  data = {
          isRequestSendBack: false,
          send_from_id: this.circularUserLastSend ? this.circularUserLastSend.id : null,
          send_to_id: this.rowSelected,
          text: this.emailContent,
          circular_document_id: circular_document_id,
          // addToCommentsFlg: this.addToCommentsFlg,
          is_template_circular_flg: this.isTemplateCircular, // 合議フラグ
          send_from_child_order: this.circularUserLastSend ? this.circularUserLastSend.child_send_order : null, // 合議用
        };
        this.sendBack(data).then(ret => {
            if(ret) {
                var self = this;
                $(".sendback_box").show();
                setTimeout(function() {
                    if (self.$store.state.home.usingPublicHash){
                        if (window.opener){
                            window.close();
                        }else{
                            window.location.href = config.LOCAL_API_URL;
                        }
                    }else{
                        self.$route.meta.isKeep=true
                        var namePath = self.$route.name;
                        if(namePath == 'expense_sendback'){
                          self.$router.push('/expense/received');
                        }else
                        {
                          self.$router.push('/received');
                        }
                    }
                },1000);
            }
        });
      },
      onRowClick: function (row) {
        if(!this.circularUserLastSend || !row) return;
        if((this.circularUserLastSend.child_send_order == 1 && this.circularUserLastSend.parent_send_order - row.parent_send_order == 1 && this.circularUserLastSend.circular_status !== this.CIRCULAR_USER.REVIEWING_STATUS)  || (this.circularUserLastSend.parent_send_order == row.parent_send_order && row.child_send_order < this.circularUserLastSend.child_send_order) || (this.circularUserLastSend.circular_status === this.CIRCULAR_USER.REVIEWING_STATUS && row.parent_send_order === this.circularUserLastSend.parent_send_order)) {
          this.rowSelected = row.id;
        }
      },
        onTemplateRowClick: function (rows) {
            // 合議の場合
            if(!this.circularUserLastSend || !rows) return;
            // rowsのparent_send_order同じです、child_send_order違います
            if((this.circularUserLastSend.child_send_order == 1 && this.circularUserLastSend.parent_send_order - rows[0].parent_send_order == 1)  || (this.circularUserLastSend.parent_send_order == rows[0].parent_send_order && rows[rows.length - 1].child_send_order < this.circularUserLastSend.child_send_order)) {
                this.rowSelected = rows[rows.length - 1].id; // 選択時に最大のidを設定します。
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
                if(namePath == 'expense_sendback'){
                  this.$router.push('/expense/received');
                }else
                {
                  this.$router.push('/received');
                }
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
                var namePath = this.$route.name;
                if(namePath == 'expense_sendback'){
                  this.$router.push('/expense/received');
                }else
                {
                  this.$router.push('/received');
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

    async created() {

      // Check Mobile
      if (
        /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
          navigator.userAgent
        )
      ) {
        this.isMobile = true
      }


      if(!this.$store.state.home.circular || this.$store.state.home.circular.circular_status === this.CIRCULAR.SAVING_STATUS) {
        // this.addLogOperation({ action: 'sendback', result: 1});
        this.$router.push('/');
      }
      // this.addLogOperation({ action: 'sendback', result: 0});
      const hash = this.$route.params.hash;
      if(hash) {
        localStorage.setItem('tokenPublic', hash);
        this.$store.commit('home/setUsingPublicHash', true);
        this.userHashInfo = await this.getInfoByHash();
        this.emailTemplateOptions = Utils.setEmailTemplateOptions(this.userHashInfo);
      }
      if(this.$store.state.home.circular.users && this.$store.state.home.circular.users.length > 0) {
          if (this.circularUserLastSend && this.circularUserLastSend.circular_status === this.CIRCULAR_USER.REVIEWING_STATUS) {
              let circularUsers = _.cloneDeep(this.$store.state.home.circular.users);
              const circularUserLastSend = circularUsers.reverse().find(item => {
                  return (item.circular_status === this.CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status === this.CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || this.CIRCULAR_USER.NODE_COMPLETED_STATUS) && item.parent_send_order <= this.circularUserLastSend.parent_send_order
              });
              this.rowSelected = circularUserLastSend && circularUserLastSend.id ? circularUserLastSend.id : 0;
          } else {
              const filter = this.$store.state.home.circular.users.filter(item => this.circularUserLastSend && ((item.parent_send_order == this.circularUserLastSend.parent_send_order && item.child_send_order < this.circularUserLastSend.child_send_order)
                  || (this.circularUserLastSend.parent_send_order - item.parent_send_order == 1 && item.child_send_order ==(item.parent_send_order?1:0))));
              this.rowSelected = filter && filter.length > 0 ? filter.pop().id : 0;
          }
      }
    }
  }
</script>


<style lang="scss">

  #sendback-page.mobile{
    display: none;
  }

  #sendback-page-mobile.mobile{
    display: block;

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

        &:after{
          border-left: 13px solid #f8f8f8 !important;
        }
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

    .vs-con-table {
      .vs-table {
        tr.row-selected {
          background: rgba(var(--vs-primary), 1) !important;
          color: #fff;
          font-weight: 500;
        }
      }
    }

    table{
      overflow: hidden;
      td{
        padding: 5px 10px;
      }
      tr.row-selected{
        display: table-row;
        color: #fff;
      }
    }

    .action{
      text-align: center;

      .vs-col{
        display: block !important;
        width: 100% !important;
      }

      button{
        padding: .75rem 4rem;
      }
    }

    table {
      tr {
        width: 100%;
        display: table-row;
        position: relative;

        &.row-selected::after{
          display: none;
          content: "";
          position: absolute;
          left: 35px;
          top: 100%;
          width: 15px;
          height: 20px;
          background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTEyLjE3MSA1MTIuMTcxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIuMTcxIDUxMi4xNzE7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgY2xhc3M9ImhvdmVyZWQtcGF0aHMiPjxnPjxnPgoJPGc+CgkJPHBhdGggZD0iTTQ3OS4wNDYsMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSw0Ljc3OSwzNDcuNTI2LDAsMzQxLjYzOCwwSDE3MC45NzEgICAgYy01Ljg4OCwwLTEwLjY2Nyw0Ljc3OS0xMC42NjcsMTAuNjY3djI2Ni42NjdINDIuOTcxYy00LjMwOSwwLTguMTkyLDIuNjAzLTkuODU2LDYuNTcxYy0xLjY0MywzLjk4OS0wLjc0Nyw4LjU3NiwyLjMwNCwxMS42MjcgICAgbDIxMi44LDIxMy41MDRjMi4wMDUsMi4wMDUsNC43MTUsMy4xMzYsNy41NTIsMy4xMzZzNS41NDctMS4xMzEsNy41NTItMy4xMTVsMjEzLjQxOS0yMTMuNTA0ICAgIEM0NzkuNzkzLDI5Mi41MDEsNDgwLjcxLDI4Ny45MTUsNDc5LjA0NiwyODMuOTI1eiIgZGF0YS1vcmlnaW5hbD0iIzAwMDAwMCIgY2xhc3M9ImhvdmVyZWQtcGF0aCBhY3RpdmUtcGF0aCIgc3R5bGU9ImZpbGw6IzA5ODRFMyIgZGF0YS1vbGRfY29sb3I9IiMwMDAwMDAiPjwvcGF0aD4KCTwvZz4KPC9nPjwvZz4gPC9zdmc+);
          background-size: contain;
          background-position: center;
          background-repeat: no-repeat;
          transform: rotate(0deg);
          z-index: 2;
        }

        td{
          width: auto;
        }
      }
    }
  }

@media ( min-width: 481px ){
  #sendback-page-mobile.mobile {
    .action {
      max-width: 400px;
      margin-left: calc(50% - 200px);

      button{
        margin: 0;
        padding: 1.2rem 0;
        width: 48% !important;
        float: left;
        font-size: 18px;

        &:first-child{
          margin-right: 4%;
        }

        img{
          height: 18px;
        }
      }
    
    }

    table {
      tr {
        &.row-selected::after{
          height: 10px;
        }
      }
    }
  }
}

@media ( max-width: 481px ){
  #sendback-page-mobile.mobile {
    padding: 0 1.2rem;
    
    .action {
      button {
        padding: .75rem 0;
        max-width: 48%;
        margin-right: 0%;

        &:first-child{
          margin-right: 2%;
        }
      }
    }
  }
}
</style>
