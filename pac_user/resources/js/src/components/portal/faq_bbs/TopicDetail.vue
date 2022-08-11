
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
        <category-menu @selectCategory="selectCategory" @changeDisp="changeDisp" :class="!isDispWide?'float-right':''" ></category-menu>
      </div>
      <vs-divider></vs-divider>
      <vs-row style="padding:16px;"  >
        <div :class="bbsTopic.author_type ==1 ? 'topic-right': 'topic-left'">
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
                  <div style="font-size:13px;">{{bbsTopic.isUpdate ? bbsTopic.created_at :  bbsTopic.updated_at}}
                    <feather-icon v-if="bbsTopic.isUpdate" icon="Edit2Icon" svgClasses="w-4 h-4"></feather-icon>
                  </div>
                </vs-col>
                <vs-col cols="12" vs-w="12">
                  <div style="font-size:13px;">表示設定：{{view_type_list[bbsTopic.view_type].name}}    通知設定：{{notify_type_list[bbsTopic.notify_type].name}}</div>
                </vs-col>
              </vs-row>
            </vs-col>
          </vs-col>
          <vs-col cols="12" style="min-height:48px;">
            <div class="tui-editor-contents" v-html="bbsTopic.content" style="font-size:13px;padding:12px 16px;"></div>
          </vs-col>
        </vs-col>
        <vs-col style="height:100%;width:30%;font-size: 13px;" v-if="!isMobile">
          <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
          <div  class="v-list-file__list_c">
            <template :data="file"  v-for="(file, itemIndex) in bbsTopic.attachments">
              <div class="v-list-file__block_t" :key="itemIndex">
                <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:file.id,filename:file.name})"></button-icon>
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
                  <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:file.id,filename:file.name})"></button-icon>
                </div>
              </template>
            </div>
          </vs-col>
          <div v-if="bbsTopic.isAuthEditAndDelete == 1" style="width: 100%;display: flex;padding:12px 0px 12px 0px">
            <div style="flex-grow: 1!important;"></div>
            <vs-button @click="onAddTopicShow">編集</vs-button>
            <vs-button class="square"  color="danger" @click="deleteTopic"><i class="far fa-trash-alt"></i> 削除</vs-button>
          </div>
        </vs-col>
        </div>
     
      </vs-row>
      <vs-row>
      <vs-col cols="12">
        <div style="font-size:17.5px;padding:12px 16px;">コメント {{ bbsTopic.com_cnt  ? bbsTopic.com_cnt: 0 }} </div>
      </vs-col>
      </vs-row>
      <div :data="item" :key="item.id" v-for="item in bbsComment" style="padding: 0px 20px 0px 20px;" >
        <div :class="item.author_type ==1 ? 'topic-right': 'topic-left'" >
        <vs-row style="mi-height:64px;padding:16px;" >
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
            <div style="font-size:13px;">{{item.isUpdate ? item.updated_at :  item.created_at}}
              <feather-icon v-if="item.isUpdate" icon="Edit2Icon" svgClasses="w-4 h-4"></feather-icon>
            </div>
          </div>
          <vs-col style="height:100%;width:36%;font-size: 13px;" v-if="!isMobile">
            <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
            <div  class="v-list-file__list_c">
              <template :data="file"  v-for="(file, itemIndex) in item.attachments">
                <div class="v-list-file__block_t" :key="itemIndex">
                  <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:file.id,filename:file.name})"></button-icon>
                </div>
              </template>
            </div>
          </vs-col>
        </vs-row>
        <vs-row>
          <vs-col cols="12"  :style="{width:isMobile?'90':'54%'}">
            <div style="white-space: pre-line;font-size:14px;padding:12px 16px;">{{item.content}}</div>
          </vs-col>
          <vs-col style="height:100%;width:100%;font-size: 13px;padding-bottom: 16px" v-if="isMobile">
            <div style="margin-left: 10px;"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</div>
            <div  class="v-list-file__list_c">
              <template :data="file"  v-for="(file, itemIndex) in item.attachments">
                <div class="v-list-file__block_t" :key="itemIndex">
                  <div class="v-list-file__name_t" v-tooltip.top-center="file.name">{{file.name ? file.name: ''}}</div><button-icon icon-name="file_download" class="btnicon_fd" color="white" @clickButton="download({id:file.id,filename:file.name})"></button-icon>
                </div>
              </template>
            </div>
          </vs-col>
          <vs-col cols="12"  :style="{width:isMobile?'90%':'36%'}">
            <div style="flex-grow: 1!important;"></div>

            <div v-if="item.isAuthEditAndDelete == 1" style="display:flex;" class="comment_action">
              <vs-spacer></vs-spacer>
              <vs-button @click="editComment(item)">編集</vs-button>
              <vs-button class="square"  color="danger" @click="deleteComment(item)"><i class="far fa-trash-alt"></i> 削除</vs-button>
            </div>
          </vs-col>
        </vs-row>
        </div>
      </div>
      <vs-row>
        <vs-col cols="12" vs-w="12">
          <div style="position: relative;" v-if="bbsTopic.isCommentAuth=='1'">
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
            <Attachemnt v-model="addAttachment" :initFiles="attachmentsNames" ></Attachemnt>
          </div>
        </vs-col>
      </vs-row>
    </vs-card>
    <modal name="edit-faq-comment-modal"
      :pivot-y="0.2"
      :width="500"
      :classes="['v--modal', 'edit-comment-modal']"
      :height="'auto'"
      :clickToClose="false">
      <div style="font-size: 17.5px;padding: 16px 24px 24px 10px;">返信を編集する</div>
      <div style="padding:0px 24px 24px 20px;margin-top:35px;">
        <vs-textarea
          v-model="currentComment.content"
          v-validate="{ required: true, max: 65535 }"
          name="updcomment"
          rows="3"
          outlined
          class="pa-1 send-reply"
        >
        </vs-textarea>
        <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('updcomment')">{{ errors.first('updcomment') }}</div>
        <Attachemnt v-model="currentComment.attachments"  :initFiles="attachmentsNames"></Attachemnt>
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
    AddUpdateDialog: () => import('@/components/portal/faq_bbs/AddTopicDialog'),
    CategoryMenu: () => import('@/components/portal/faq_bbs/CategoryMenu'),
  },
  props: {
    category : {},
    bbsId    : {}
  },
  mounted() {
    this.isDispWide = this.$store.state.portal.isDispWide;
    this.onSearch();
  },
  data() {
    return {
      posts: [],
      bbsTopic:{
        view_type:0,
        notify_type:0
      },
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
      view_type_list:[
        {
          id:0,
          name:'自分のみに表示'
        },
        {
          id:1,
          name:'全員に表示'
        },
      ],
      notify_type_list:[
        {
          id:0,
          name:'自分のみに通知'
        },
        {
          id:1,
          name:'全員に通知'
        },
      ],
    }
  },
  computed: {
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
    $('body').on('click' ,'.tui-editor-contents a',function (){
      event.preventDefault()
      window.open($(this).prop('href'),'_blank')
    })
  },
  methods: {
    ...mapActions({
      getTopicList: "portal/getFaqTopicList",
      deleteBbsTopic:"portal/deleteFaqBbsTopic",
      deleteBbsComment:"portal/deleteFaqBbsComment",
      updateBbsComment:"portal/updateFaqBbsComment",
      addBbsComment:"portal/addFaqBbsComment",
      getFaqBbsUnreadNoticeCount: "portal/getFaqBbsUnreadNoticeCount",
      addLogOperation: "logOperation/addLog",
    }),
    onSearch: async function (val) {
      let RecalcFlg = 0;
      if (val) RecalcFlg = 1;

      let infoTopic  = {
        procKbn    : '1',
        bbsId      : this.bbsId,
        RecalcFlg  : RecalcFlg,
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
      /*PAC_5-3156 S*/
      Axios.put(`${config.BASE_API_URL}/faq_bbs_notice_read_by_bbs/${this.bbsId}`).then(()=>{this.getFaqBbsUnreadNoticeCount()});
      /*PAC_5-3156 E*/
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
      this.$modal.show('edit-faq-comment-modal')
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

      let res =await this.updateBbsComment(info);
      if(res){
        this.addLogOperation({action: 'portal-comment-update-faq-bbs', result: 0})
      }else{
        this.addLogOperation({action: 'portal-comment-update-faq-bbs', result: 1})
      }
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
      let res = await this.addBbsComment(info);
      if(res){
        this.addLogOperation({action: 'portal-comment-add-faq-bbs', result: 0})
      }else{
        this.addLogOperation({action: 'portal-comment-add-faq-bbs', result: 1})
      }
      this.addAttachment=[]
      this.comment='';
      this.onSearch(1);      
    },
　　cancelComment(){
      this.$modal.hide('edit-faq-comment-modal')
    },
    isDispChange(){
      this.isDispWide = this.$store.state.portal.isDispWide;
    },
    doDeleteTopic: async function () {
      let info={
        ids:[this.bbsTopic.id]
      }

      this.deleteTopicDialog = false;

      let res = await this.deleteBbsTopic(info);
      if(res){
        this.addLogOperation({action: 'portal-topic-del-faq-bbs', result: 0})
      }else{
        this.addLogOperation({action: 'portal-topic-del-faq-bbs', result: 1})
      }

      this.$store.commit('portal/setBbsDispList', 'topiclist'); 
      this.changeDisp();

    },
    doDeleteComment: async function () {
  
      let info={
        s3path:this.bbsTopic.s3path,
        value:this.currentComment
      }
      let res = await this.deleteBbsComment(info);
      if(res){
        this.addLogOperation({action: 'portal-comment-del-faq-bbs', result: 0})
      }else{
        this.addLogOperation({action: 'portal-comment-del-faq-bbs', result: 1})
      }
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
      Axios.post(`${config.BASE_API_URL}/getFaqBbsFile` ,obj).then(res=>{
        window.open(res.data.data.url)
      }).catch(error=>{
        this.$store.dispatch("alertError", '添付ファイルが見つかりませんでした。', { root: true });
      })
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
    }
  }
}
</script>
<style>
del {
  text-decoration: line-through!important;
}
.topic-left ,.topic-right {
  position: relative;
  border: 2px solid #294D8C;
  border-radius: 10px;
  padding: 1.25rem;
  margin-bottom: .75rem;
  width: 100%;
  height: 100%;
}
.topic-left:before, .topic-right:before {
  content: '';
  display: block;
  width: 0;
  height: 0;
  border: 16px solid transparent;
  position: absolute;
  bottom: 11px;
  z-index: 99;
}

.topic-left:after, .topic-right:after {
  content: '';
  display: block;
  width: 0;
  height: 0;
  border: 15px solid transparent;
  position: absolute;
  bottom: 12px;
  z-index: 100;
}

.topic-left:before {
  border-right: 16px solid #294D8C;
  left: -33px;
}

.topic-left:after {
  border-right: 15px solid #fff;
  left: -29px;
}

.topic-right:before {
  border-left: 16px solid #294D8C;
  right: -33px;
}

.topic-right:after {
  border-left: 15px solid #fff;
  right: -29px;
}
</style>
<style>
.tui-editor-contents a{
  text-decoration: underline;
}
</style>