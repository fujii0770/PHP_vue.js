
<template>
  <div>
    <vs-card class="category-list mx-auto" max-width="100vw">
      <div v-if="isDispWide">
        <div class="v-card__title headline title font-weight-bold">
          {{ bbsTopic && bbsTopic.name ? bbsTopic.name : '全ての投稿' }}
        </div>
      </div>
      <div v-if="!isDispWide">
        <div class="v-card__title headline title font-weight-bold">
          {{ bbsTopic && bbsTopic.name ? bbsTopic.name : '全ての投稿' }}
        </div>
        <vs-spacer style="flex-grow: 1!important;"/>
        <category-menu @selectCategory="selectCategory" @changeDisp="changeDisp" :class="!isDispWide?'float-right':''" :isExpired="isExpired"></category-menu>
      </div>
      <vs-divider></vs-divider>
      <vs-row style="padding:16px;">
        <vs-col cols="12" style="min-height:48px;">
          <div class="post-title" style="padding: 12px 16px;font-size:17.5px;">{{bbsTopic.title ? bbsTopic.title:''}}</div>
        </vs-col>
        <vs-col :style="{width:isMobile?'100%':'70%'}">
          <vs-col cols="12" style="padding: 12px 16px;min-height:64px;">
            <vs-col cols="12" style="width:40px;height:40px;margin-right: 16px;padding-top: 5px;">
              <div v-if="bbsTopic.user_profile_data">
                <img
                    style="width:40px;height:40px;"
                    class=""
                    alt=""
                    :src="bbsTopic.user_profile_data
                  ? 'data:image/jpeg;base64,' +
                    bbsTopic.user_profile_data
                  : ''">
              </div>
              <div v-else >
                <span><i style="width: 100%;font-size: 30px;" class="fas fa-user"></i></span>
              </div>
            </vs-col>
            <vs-col cols="12" vs-w="9">
              <vs-row>
                <vs-col cols="12" vs-w="12">
                  <div style="font-size:14px;">{{bbsTopic.username ? bbsTopic.username : ''}}</div>
                </vs-col>
                <vs-col cols="12" vs-w="12">
                  <div style="font-size:13px;">{{bbsTopic.created_at ? bbsTopic.created_at : ''}}
                    <feather-icon v-if="bbsTopic.isUpdate" icon="Edit2Icon" svgClasses="w-4 h-4"></feather-icon>
                  </div>
                </vs-col>
                <vs-col cols="12" vs-w="12">
                  <div style="font-size:13px;">掲示期間：{{bbsTopic.disp_start_date ? bbsTopic.disp_start_date : ''}}～{{bbsTopic.disp_end_date ? bbsTopic.disp_end_date : ''}}</div>
                </vs-col>
              </vs-row>
            </vs-col>
            <vs-col cols="12" vs-w="12">
              <vs-row>
                <div class="font-bold" style="font-size: 13px;margin-top: 30px;">
                    <span>新規</span>
                    <span style="padding-left: .7rem">{{bbsTopic.created_time}}</span>
                </div>
              </vs-row>
              <vs-row v-if="bbsTopic.updated_time">
                <div class="font-bold" style="font-size: 13px;margin-top: 10px;">
                    <span>更新</span>
                    <span style="padding-left: .7rem">{{bbsTopic.updated_time}}（編集）</span>
                </div>
              </vs-row>
            </vs-col>
          </vs-col>
          <vs-col cols="12" style="min-height:48px;">
            <div class="tui-editor-contents" v-html="bbsTopic.content" style="font-size:13px;padding:12px 16px;"></div>
          </vs-col>
          <vs-col cols="12" vs-w="12" v-if="canLikeAction" style="padding: 12px 16px;">
              <button v-if="hasLiked" type="border" class="button-like cancel small" color="danger" @click="cancelLikeTopic">
                  <vs-icon icon="favorite"></vs-icon>
                  いいね
              </button>
              <button v-if="!hasLiked" type="border" class="button-like small" color="#626262" @click="likeTopic">
                  <vs-icon icon="favorite_border"></vs-icon>
                  いいね
              </button>
              <div class="speech-bubble" v-if="likesList && likesList.length > 0">
                  <span v-for="user in likesList" style="display: flex;padding: 7px;" :key="user.mst_user_id">
                      <div v-if="user.user_profile_data">
                        <img
                            style="width:18px;height:18px;"
                            class=""
                            alt=""
                            :src="user.user_profile_data
                          ? 'data:image/jpeg;base64,' +
                            user.user_profile_data
                          : ''">
                      </div>
                      <div v-else >
                        <span><i style="width: 18px;font-size: 18px;" class="fas fa-user"></i></span>
                      </div>
                      <div style="margin-left: 3px;">{{user.username ? user.username : ''}}</div>
                  </span>
                  <span v-if="likesListCount > 10 && !likesListGetAll" style="padding: 5px;">...</span>
                  <a v-if="likesListCount > 10 && !likesListGetAll" @click="getTopicLikesList(true)">{{likesListCount}}名</a>
                  <a v-if="likesListCount > 10 && likesListGetAll" @click="likesShrink()">折りたたむ</a>
              </div>
          </vs-col>
        </vs-col>
        <vs-col style="height:100%;width:30%;font-size: 13px;" v-if="!isMobile">
          <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
          <div  class="v-list-file__list_c">
            <template :data="file"  v-for="(file, itemIndex) in bbsTopic.attachments">
              <div class="v-list-file__block_t" :key="itemIndex">
                <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:bbsTopic.id,filename:file.name})"></button-icon>
              </div>
            </template>
          </div>
        </vs-col>
        <vs-col cols="12">
          <vs-col style="width:100%;font-size: 13px;" v-if="isMobile">
            <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
            <div  class="v-list-file__list_c">
              <template :data="file"  v-for="(file, itemIndex) in bbsTopic.attachments">
                <div class="v-list-file__block_t" :key="itemIndex">
                  <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:bbsTopic.id,filename:file.name})"></button-icon>
                </div>
              </template>
            </div>
          </vs-col>
          <div v-if="bbsTopic.isAuthEditAndDelete && isExpired != 1" style="width: 100%;display: flex;padding:12px 12px 12px 0px">
            <div style="flex-grow: 1!important;"></div>
            <vs-button @click="onAddTopicShow">編集</vs-button>
            <vs-button class="square mr-0"  color="danger" @click="deleteTopic"><i class="far fa-trash-alt"></i> 削除</vs-button>
          </div>
        </vs-col>
        <vs-divider style="padding-top:5px;padding-bottom:5px"></vs-divider>
        <vs-col cols="12">
          <div style="font-size:17.5px;padding:12px 16px;">コメント {{ bbsTopic.com_cnt  ? bbsTopic.com_cnt: 0 }} </div>
        </vs-col>
      </vs-row>

      <div :data="item" :key="item.id" v-for="item in bbsComment" style="padding: 0px 0px 0px 40px;">
        <vs-row style="mi-height:64px;padding:16px;">
          <vs-col cols="12" vs-xs="3" style="width:10%;padding-top: 5px;">
            <div v-if="item.user_profile_data" style="width:40px;height:40px;">
              <img
                style="width:40px;height:40px;"
                class=""
                alt=""
                :src="item.user_profile_data
                  ? 'data:image/jpeg;base64,' +
                  item.user_profile_data
                  : ''">
            </div>
            <div v-else>
              <span><i style="width: 100%;font-size:30px;" class="fas fa-user"></i></span>
            </div>
          </vs-col>
          <div :style="{width:isMobile?'90%':'54%'}">
                <div>{{item.username ? item.username: ''}}</div>
                <div>{{item.disp_created_at ? item.disp_created_at : ''}}
                  <feather-icon v-if="item.isUpdate" icon="Edit2Icon" svgClasses="w-4 h-4"></feather-icon>
                </div>
          </div>
          <vs-col style="height:100%;width:36%;font-size: 13px;" v-if="!isMobile">
            <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
            <div  class="v-list-file__list_c">
              <template :data="file"  v-for="(file, itemIndex) in item.attachments">
                <div class="v-list-file__block_t" :key="itemIndex">
                  <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:bbsTopic.id,filename:file.name})"></button-icon>
                </div>
              </template>
            </div>
          </vs-col>
        </vs-row>
        <vs-row>
          <vs-col cols="12"  :style="{width:isMobile?'90':'54%'}">
            <div style="white-space: pre-line;font-size:14px;padding:12px 16px;">{{item.comment}}</div>
          </vs-col>
          <vs-col style="height:100%;width:100%;font-size: 13px;padding-bottom: 16px" v-if="isMobile">
            <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
            <div  class="v-list-file__list_c">
              <template :data="file"  v-for="(file, itemIndex) in item.attachments">
                <div class="v-list-file__block_t" :key="itemIndex">
                  <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:bbsTopic.id,filename:file.name})"></button-icon>
                </div>
              </template>
            </div>
          </vs-col>
          <vs-col cols="12"  :style="{width:isMobile?'100%':'36%'}">
            <div style="flex-grow: 1!important;"></div>

            <div v-if="item.isAuthEditAndDelete && isExpired != 1" style="display:flex; margin-right: 12px;" class="comment_action">
              <vs-spacer></vs-spacer>
              <vs-button @click="editComment(item)">編集</vs-button>
              <vs-button class="square mr-0" color="danger" @click="deleteComment(item)"><i class="far fa-trash-alt"></i> 削除</vs-button>
            </div>
          </vs-col>
        </vs-row>
        <vs-divider style="padding-top:5px;padding-bottom:5px;"></vs-divider>
      </div>
      <vs-row>
        <vs-col cols="12" vs-w="12">
          <div style="position: relative;" v-if="bbsTopic.isCommentAuth=='1' && isExpired != 1">
            <vs-textarea
              v-model="comment"
              background-color="grey lighten-3"
              rows="3"
              class="pa-1 send-reply"
              auto-grow
              @blur="blurEvent"
              v-validate="{ required: true, max:65535 }"
              data-vv-as="返信"
              name="comment"
              style="padding-right:30px;margin-bottom:0px;"
            ></vs-textarea>
            <div style="position: absolute;right: 5px;top: 36px;">
              <feather-icon icon="SendIcon" @click="addComment"></feather-icon>
            </div>
            <div class="text-danger text-sm" style="min-height:18px;padding-left:12px;" v-show="errors.has('comment')">{{ errors.first('comment') }}</div>
            <Attachemnt v-model="addAttachment" :initFiles="attachmentsNames" :isMobile="isMobile" v-if="isExpired != 1"></Attachemnt>
          </div>
        </vs-col>
      </vs-row>
    </vs-card>
    <modal name="edit-comment-modal"
      :pivot-y="0.2"
      :width="500"
      :classes="['v--modal', 'edit-comment-modal']"
      :height="'auto'"
      :clickToClose="false">
      <div style="font-size: 17.5px;padding: 16px 24px 24px 10px;">返信を編集する</div>
      <div style="padding:0px 24px 24px 20px;margin-top:35px;">
        <vs-textarea
          v-model="currentComment.comment"
          v-validate="{ required: true, max: 65535 }"
          name="updcomment"
          rows="3"
          outlined
          class="pa-1 send-reply"
        >
        </vs-textarea>
        <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('updcomment')">{{ errors.first('updcomment') }}</div>
        <Attachemnt v-model="currentComment.attachments"  :initFiles="attachmentsNames" v-if="isExpired != 1"></Attachemnt>
      </div>
      <div style="width:100%;padding:8px 16px;display:flex;">
        <vs-spacer></vs-spacer>
        <vs-button small color="primary" @click.stop="updateComment">OK</vs-button>
        <vs-button color="grey-light" class="nprimary"  @click="cancelComment" style="color: inherit;">キャンセル</vs-button>
      </div>
    </modal>
    <vs-popup title="確認" :active.sync="deleteTopicDialog">
      <div class="mb-0">投稿を削除してよろしいですか?</div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button @click="doDeleteTopic" color="warning">OK</vs-button>
        <vs-button @click="cancelDeleteTopic" color="dark" type="border">キャンセル</vs-button>
      </vs-row>
    </vs-popup>
    <vs-popup title="確認" :active.sync="deleteCommentDialog">
      <div class="mb-0">コメントを削除してよろしいですか?</div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button @click="doDeleteComment" color="warning">OK</vs-button>
        <vs-button @click="cancelDeleteComment" color="dark" type="border">キャンセル</vs-button>
      </vs-row>
    </vs-popup>
  </div>
</template>
<script>
import fileDownload from "js-file-download";
import config from "../../../app.config";
import { mapState, mapActions } from "vuex";
import FeatherIcon from '../../FeatherIcon.vue';
import { Validator } from 'vee-validate';
import Attachemnt from "./Attachemnt";
import {cloneDeep} from "lodash/lang";
import ButtonIcon from "./ButtonIcon";
import Axios from "axios";
const dict = {
  custom: {
    comment: {
      required: '* 必須項目です',
      max: '* 65535文字以上は入力できません。',
    },
    updcomment: {
      required: '* 必須項目です',
      max: '* 65535文字以上は入力できません。',
    },
  }
};
Validator.localize('ja', dict);
export default {
  components: {
    ButtonIcon,
    Attachemnt,
    AddUpdateDialog: () => import('@/components/portal/bbs/AddTopicDialog'),
    CategoryMenu: () => import('@/components/portal/bbs/CategoryMenu'),
  },
  props: {
    category : {},
    bbsId    : {},
    loginUserProfileData: {},
    isExpired: {},
  },
  mounted() {
    this.isDispWide = this.$store.state.portal.isDispWide;
    this.onSearch();
  },
  data() {
    return {
      posts: [],
      bbsTopic:{},
      bbsComment:[],
      deleteModal: false,
      deleteErrorDialog: false,
      commentEditting: {},
      deleteTopicDialog:false,
      deleteCommentDialog:false,
      editData:{ addFlg:false, bbsId:null},
      comment:'',
      currentComment:{},
      isMobile:false,
      isDispWide:false,
      addAttachment:[],
      attachmentsNames:[],
      loadedlikesList: false,
      likesList:[],
      likesListGetAll:false,
      hasLiked: false,
      loginUser: JSON.parse(getLS('user')),
    }
  },
  computed: {
      canLikeAction() {
          return (this.bbsTopic.state === undefined || this.bbsTopic.state == 1) && !this.isExpired && this.loadedlikesList;
      }
  },
  watch: {
  },
  created() {
    if (process.client) {
      if (
        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
          navigator.userAgent
        )
      ) {
        this.isMobile = true
      }
    }
    this.validateDevice();
    window.addEventListener('resize',this.validateDevice)
  },
  methods: {
    ...mapActions({
      getTopicList: "portal/getTopicList",
      deleteBbsTopic:"portal/deleteBbsTopic",
      deleteBbsDraftTopic: 'portal/deleteBbsDraftTopic',
      deleteBbsComment:"portal/deleteBbsComment",
      updateBbsComment:"portal/updateBbsComment",
      addBbsComment:"portal/addBbsComment",
      addLogOperation: "logOperation/addLog",
      getBbsTopicLikes: "portal/getBbsTopicLikes",
      addBbsTopicLike: "portal/addBbsTopicLike",
      deleteBbsTopicLike: 'portal/deleteBbsTopicLike',
      reserveBbsAttachment: "portal/reserveBbsAttachment",
    }),
    onSearch: async function (val) {
      
      if( this.isMobile ) this.$vs.loading({ container: '.category-list' });

      let RecalcFlg = 0;
      if (val) RecalcFlg = 1;

      let infoTopic  = {
        procKbn    : '1',
        bbsId      : this.bbsId,
        RecalcFlg  : RecalcFlg,
        isExpired  : this.isExpired ? this.isExpired : 0,
      };
      let data = await this.getTopicList(infoTopic);
      
      if (!data) {
        this.$store.commit('portal/setBbsDispList', 'topiclist');
        this.changeDisp();
        return;
      }

      this.bbsTopic               = data.topic;
      this.bbsComment = data.commentList
      this.attachmentsNames = data.attachmentsNames

      this.getTopicLikesList(this.likesListGetAll);
      
      if( this.isMobile ) this.$vs.loading.close('.category-list > .con-vs-loading');
    },
    onAddTopicShow() {
      let editData=
      {
        addFlg:false,
        value:this.bbsTopic,
        bbsComment : this.bbsComment,
        attachmentsNames:this.attachmentsNames
      }
      this.$emit('onAddTopicShow', editData);
    },

　　deleteTopic(){
      this.deleteTopicDialog = true;
    },
　　editComment(val){
      this.currentComment = cloneDeep(val);
      if (this.currentComment.attachments){
        this.currentComment.attachments.forEach(_=>{
          _.type = 'history'
        })
      }else{
        this.currentComment.attachments =[]
      }
      this.$modal.show('edit-comment-modal')
    },
　　deleteComment(val){
      this.deleteCommentDialog=true;
      this.currentComment = val;
    },
　　updateComment: async function (){
      const validate = await this.$validator.validateAll()
      if (!validate && this.errors.has('updcomment')) return
      let info={
        s3path:this.bbsTopic.s3path,
        value:this.currentComment,
        attachment:this.currentComment.attachments
      }

      await this.updateBbsComment(info);
      this.cancelComment();
      this.onSearch(1);
    },
    addComment: async function (){
      const validate = await this.$validator.validateAll()
      if (!validate) return
      let info={
        s3path:this.bbsTopic.s3path,
        value:{
          bbs_id:this.bbsTopic.id,
          mst_user_id:'',
          comment:this.comment,
        },
        attachment:this.addAttachment
      }
      await this.addBbsComment(info);
      this.addAttachment=[]
      this.comment='';
      this.onSearch(1);
    },
　　cancelComment(){
      this.$modal.hide('edit-comment-modal')
    },
    isDispChange(){
      this.isDispWide = this.$store.state.portal.isDispWide;
    },
    doDeleteTopic: async function () {
      const state = this.bbsTopic.state != undefined ? this.bbsTopic.state : 1;
      let info={
        ids:[this.bbsTopic.id],
        state:state
      }

      this.deleteTopicDialog = false;

      if (state == 0) {
          await this.deleteBbsDraftTopic(info);
      } else {
          await this.deleteBbsTopic(info);
      }

      this.$store.commit('portal/setBbsDispList', 'topiclist');
      this.changeDisp();

    },
    doDeleteComment: async function () {
  
      let info={
        s3path:this.bbsTopic.s3path,
        value:this.currentComment
      }
      await this.deleteBbsComment(info);
      this.onSearch(1);
      this.deleteCommentDialog = false;
      this.currentComment ={};
    },
    cancelDeleteTopic(){
      this.deleteTopicDialog = false;
    },
    cancelDeleteComment(){
      this.deleteCommentDialog = false;
    },
    changeDisp(){
      let infoDisp  = {
        category   : this.category
      };
      this.$emit('changeDisp', infoDisp)
    },
    blurEvent() {
      this.$validator.reset()
    },
    selectCategory: async function (val) {
      let infoDisp  = {
        category   : this.category,
      }
      this.$emit('changeDisp', infoDisp)
    
    },
    download(obj){
      if (this.bbsTopic.sanitizing_flg) {//ダウンロード予約
        let info = {
          bbs_id: obj.id,
          file_name: obj.filename,
        }
        this.reserveBbsAttachment(info);
      }else{
        Axios.post(`${config.BASE_API_URL}/getBbsFile` ,obj).then(res=>{

          const byteString = Base64.atob(res.data.file)
          const ab = new ArrayBuffer(byteString.length)
          const ia = new Uint8Array(ab)
          for (let i = 0 ;i<byteString.length;i++){
            ia[i] = byteString.charCodeAt(i)
          }
          const blob = new Blob([ab])
          fileDownload(blob,res.data.file_name)
        }).catch(error=>{
          this.$store.dispatch("alertError", '添付ファイルが見つかりませんでした。', { root: true });
        })
      }
    },
    validateDevice(){
      if (
          /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
              navigator.userAgent
          ) || window.innerWidth<=480
      ) {
        this.isMobile = true
      }else{
        this.isMobile = false
      }
    },
    getTopicLikesList: async function (get_all = false) {
        let limit = 10;
        if (get_all) {
            limit = 'all';
        }
        let info = {
            bbs_id: this.bbsId,
            limit: limit
        }
        let data = await this.getBbsTopicLikes(info);
        if (data) {
            this.likesList = data.likesList;
            this.hasLiked = data.hasLiked ? data.hasLiked : false;
            this.likesListCount = data.likesListCount;
            this.loadedlikesList = true;
            if (get_all) {
                this.likesListGetAll = true;
            } else {
                this.likesListGetAll = false;
            }
        }
    },
    likesShrink: async function () {
        this.getTopicLikesList();
    },
    likeTopic: async function () {
        if (this.hasLiked) return false;
        let info = {
            bbs_id: this.bbsId,
        }
        const userInfo = this.loginUser;
        const loginUserProfileData = this.loginUserProfileData ? this.loginUserProfileData : '';
        let hasUser = this.likesList.find(item=>{
            return item.mst_user_id == userInfo.id;
        });
        if (hasUser) {
            this.hasLiked = true;
        } else {
            let data = await this.addBbsTopicLike(info);
            if (data) {
                let hasUser = this.likesList.find(item => {
                    return item.mst_user_id == userInfo.id;
                });
                if (!hasUser && userInfo) {
                    let user = {
                        'mst_user_id': userInfo.id,
                        'user_profile_data': loginUserProfileData ? loginUserProfileData : '',
                        'username': userInfo.family_name + userInfo.given_name,
                    };
                    if (this.likesListCount + 1 <= 10 || this.likesListGetAll) this.likesList.push(user);
                    this.likesListCount++;
                }
                this.hasLiked = true;
            }
        }
    },
    cancelLikeTopic: async function () {
        if (!this.hasLiked) return false;
        let info = {
            bbs_id: this.bbsId,
        }
        const userInfo = this.loginUser;
        let hasUser = this.likesList.find(item=>{
            return item.mst_user_id == userInfo.id;
        });
        if ((this.likesListCount + 1 <= 10 || this.likesListGetAll) && !hasUser) {
            this.hasLiked = false;
        } else {
            let data = await this.deleteBbsTopicLike(info);
            if (data) {
                let hasUser = this.likesList.find(item => {
                    return item.mst_user_id == userInfo.id;
                });
                if (hasUser && userInfo) {
                    this.likesList = this.likesList.filter(item => {
                        return item.mst_user_id != userInfo.id
                    })
                }
                this.likesListCount--;
                this.hasLiked = false;
            }
        }
    },
  }
}
</script>
<style lang="scss">
.tui-editor-contents {
  font-family: -apple-system, BlinkMacSystemFont, "Noto Sans JP", MS Gothic, "Montserrat", Helvetica, Arial, sans-serif !important;
  del{
    text-decoration: line-through;
    text-decoration-color: #000000;
    color: #222 !important;
  }
  strong{
    font-weight: 900 !important;
  }
}
.speech-bubble {
    display: flex;
    margin-top: 10px;
    flex-wrap: wrap;
    line-height: 18px;
    background: rgb(248 248 248);
}
.speech-bubble::before {
    position: relative;
    content: '';
    display: block;
    border-left: 1px;
    border-top: 1px;
    width: 10px;
    height: 10px;
    top: -5px;
    left: 20px;
    background: rgb(248, 248, 248);
    transform: rotate(45deg);
}
.speech-bubble a {
    cursor: pointer;
    text-decoration: underline;
    font-size: 13px;
    padding: 5px;
    line-height: 23px;
}
button.button-like {
    border: 1px solid rgb(234, 84, 85) !important;
    background: #FFFFFF!important;
    font-size: 13px;
    padding: 0.25rem 1rem;
    -webkit-transition: all .2s ease;
    transition: all .2s ease;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    color: #fff;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont,'Noto Sans JP',MS Gothic,"Montserrat", Helvetica, Arial, sans-serif;
}
button.button-like {
    color: #626262!important;
}
button.button-like.cancel {
    color: rgb(234, 84, 85)!important;
}
button.button-like i,button.button-like .vs-icon{
    font-size: 18px;vertical-align: middle;
}
</style>