<template>
  <div id="addtopic">
    <vs-card>
      <header 
        class="vs_dialog_header_primary" 
        style="height: 56px;">
        <div class="v-toolbar__content">
          <h2 class="text-center">
              <span class="headline">投稿</span>
          </h2>
          <vs-spacer></vs-spacer>
          <button-icon icon-name="close" class="btnicon_c" color="primary" @clickButton="$emit('onAddTopicHide')"></button-icon>    
        </div>
      </header>
    <form class="faq_topic_form">
      <vs-row style="padding: 0px 20px 0px 20px;">
        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">カテゴリ<span class="text-danger">*</span></div>
        </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <v-select
            class="dropdown clistDetail"
            v-model="bbsTopic.bbs_category_id"
            :reduce="(option) => option.id"
            :options="bbsCategoryList"
            v-validate="{ required: true}"
            name="categoryid"
            label="name"
            dense
            no-data-text="データがありません。"
            placeholder="選択してください"
            style="width: 100%">
          </v-select>
          <input type="hidden" name ='bbs_category_id' :value="bbsTopic.bbs_category_id">
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12" style="min-height: 19px"> </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('categoryid')">{{ errors.first('categoryid') }}</div>
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">タイトル<span class="text-danger">*</span></div>
        </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <vs-input
            v-model="bbsTopic.title"
            v-validate="{ required: true, max: 45 }"
            data-vv-as="タイトル"
            name="title"
            class="clistDetail"
          >
          </vs-input>
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12" style="min-height: 19px"> </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('title')">{{ errors.first('title') }}</div>
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">内容<span class="text-danger">*</span></div>
        </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <div id="editor" style="padding:12px 12px 0px 12px;"></div>
          <input type="hidden" name ='content' :value="bbsTopic.content">
          <p v-if="isContent" class="text-danger text-sm" style="padding-left:12px;" >* 必須項目です</p>
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">表示設定<span class="text-danger">*</span></div>
        </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <v-select
              class="dropdown clistDetail"
              v-model="bbsTopic.view_type"
              :options="view_type_list"
              :reduce="(option) => option.id"
              v-validate="{ required: true}"
              name="sview_type"
              label="name"
              dense
              no-data-text="データがありません。"
              placeholder="選択してください"
              style="width: 100%"
              >
          </v-select>
          <input type="hidden" name ='view_type' :value="bbsTopic.view_type">
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12" style="min-height: 19px"> </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('sview_type')">{{ errors.first('sview_type') }}</div>
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">通知設定<span class="text-danger">*</span></div>
        </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <v-select
              class="dropdown clistDetail"
              v-model="bbsTopic.notify_type"
              :options="notify_type_list"
              v-validate="{ required: true}"
              :reduce="(option) => option.id"
              label="name"
              name="snotify_type"
              dense
              no-data-text="データがありません。"
              placeholder="選択してください"
              style="width: 100%">
          </v-select>
          <input type="hidden" name ='notify_type' :value="bbsTopic.notify_type">
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12" style="min-height: 19px"> </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('snotify_type')">{{ errors.first('snotify_type') }}</div>
        </vs-col>
        <attachemnt v-model="attachments" :initFiles="initFiles"></attachemnt>
        <input type="hidden" name ='attachments' :value="jsonAttachments">
      </vs-row>
      <vs-row>
        <div style="padding: 0px 20px 20px 20px;display: flex;width: 100%;">
          <vs-spacer></vs-spacer>
          <vs-button color="primary" @click="onSaveClick">投稿</vs-button>
          <vs-button color="grey-light" class="nprimary"  @click="$emit('onAddTopicHide')" style="color: inherit;">キャンセル</vs-button>
        </div>
      </vs-row>
    </form>
    </vs-card>
  </div>
</template>
<script>
import 'codemirror/lib/codemirror.css' // Editor's Dependency Style
import '@toast-ui/editor/dist/toastui-editor.css' // Editor's Style
import '@toast-ui/editor/dist/i18n/ja-jp'
import 'tui-color-picker/dist/tui-color-picker.css'
import '@toast-ui/editor-plugin-color-syntax/dist/toastui-editor-plugin-color-syntax.css'
import { Validator } from 'vee-validate'
import 'flatpickr/dist/flatpickr.min.css'
import {Japanese} from 'flatpickr/dist/l10n/ja.js'
import Editor from '@toast-ui/editor'
import { mapState, mapActions, createNamespacedHelpers } from "vuex"
import Attachemnt from "./Attachemnt";
const BreakpointModule = createNamespacedHelpers('helpers/Breakpoint')
const dict = { 
  custom: {
    title: { 
      required: '* 必須項目です',
      max: '* 45文字以上は入力できません。',
    },
    categoryid: {
      required: '* 必須項目です',
    },
    sview_type: {
      required: '* 必須項目です',
    },
    snotify_type: {
      required: '* 必須項目です',
    },
    strdate: {
      required: '* 必須項目です',
    },
  }
};
Validator.localize('ja', dict);

export default {
  components: {
    Attachemnt,
    Editor: () => import('@toast-ui/editor'),
    // colorSyntax:() =>import('@toast-ui/editor-plugin-color-syntax'),
    ButtonIcon: () => import('@/components/portal/bbs/ButtonIcon'),
    flatPickr: () => import('vue-flatpickr-component'),
  },
  props: {
    value: { type: Boolean, default: false },
    users: { type: Array, default: () => [] },
    bbsId:{type: Number, default: null},
    editData:{ 
      addFlg:{type: Boolean, default: false},
      value:{},
    },
  },
  data() {
    return {
      isMobile: false,
      bbsCategoryList:[],
      bbsTopic:{
        bbs_category_id:'',
        view_type:0,
        notify_type:0,
        title:'',
        content:''
      },
      name: null,
      rules: {
        requiredField: [
          (val) => (val || '').length > 0 || 'This field is required'
        ]
      },
      editorHtml: '',
      selCategory:'',
      data: null,
      isContent: false,
      combErrorFlg: false, // カテゴリ入力フォームエラーフラグ
      combErrorMessage: '', // カテゴリエラーメッセージ
      addFlg :true,
      attachments:[],
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
       
    }
  },
  mounted() {
    this.getBbsCategory();
    this.addFlg = this.editData.addFlg;
    if (this.addFlg==false){
      this.onSearch();
    }
    // const Editor = require('@toast-ui/editor');
    const ColorSyntax = require('@toast-ui/editor-plugin-color-syntax');
    const colorSyntaxOptions = {
      preset: [
        '#000000',
        '#7f7f7f',
        '#ed1c24',
        '#ff7f27',
        '#fff200',
        '#22b14c',
        '#3f48cc',
        '#a349a4',
        '#ffffff',
        '#c3c3c3',
        '#ffaec9',
        '#ffc90e',
        '#efe4b0',
        '#b5e61d',
        '#7092be',
        '#c8bfe7'
      ]
    };

    this.$nextTick(() => {

      window.tuieditor = new Editor({
        el: document.querySelector('#editor'),
        language: 'ja-JP',
        height: '200px',
        view: true,
        initialEditType: 'wysiwyg',
        previewStyle: 'vertical',
        toolbarItems: [
          ['heading', 'bold', 'italic', 'strike', 'link'],
          ['hr', 'quote'],
          ['ul', 'ol', 'task'],
        ],
        plugins: [[ColorSyntax,colorSyntaxOptions]],
      })
      setTimeout(()=>{
        window.tuieditor.eventEmitter.events.values[15][0] =function (query, payload) {
          var popupName = payload.popupName;
          var selectionNode = window.tuieditor.getSelectedText()?window.getSelection().focusNode.parentNode:null
          var linkUrl = selectionNode?selectionNode.href:null
          linkUrl = linkUrl?linkUrl:null
          linkUrl = window.tuieditor.getSelectedText()?linkUrl :null
          return (popupName === 'link' && window.tuieditor.getSelectedText()) ? { linkText: window.tuieditor.getSelectedText(),linkUrl:linkUrl } : {};
        }
      })
      window.tuieditor.eventEmitter.removeEventHandler('addImageBlobHook')
      window.tuieditor.setHTML(this.bbsTopic.content, true)
        $('#bulletin_board:not(.mobile) .selectrow.v-list-item--active .v-list-item__title').css('white-space', 'normal');
        $('#faq_bulletin_board:not(.mobile) .selectrow.v-list-item--active .v-list-item__title').css('white-space', 'normal');
        setTimeout(() => {
          $('#bulletin_board:not(.mobile) .selectrow.v-list-item--active .v-list-item__title').css('white-space', 'nowrap');
          $('#faq_bulletin_board:not(.mobile) .selectrow.v-list-item--active .v-list-item__title').css('white-space', 'nowrap');
          window.tuieditor.moveCursorToEnd()
        }, 0)
    });
    this.$validator.reset();
    this.isContent = false;
  },
  computed: {
    initFiles(){
      if(this.editData.addFlg){
        return []
      }else{
        return this.editData.attachmentsNames
      }
    },
    notify_type_list(){
     let list=[];
     if (this.bbsTopic.view_type==1){
       list =[
         {
           id:0,
           name:'自分のみに通知',
           selected:true,
           show:true
         },
         {
           id:1,
           name:'全員に通知',
           selected:true,
           show:false
         }
       ]
     }else{
       list = [
         {
           id:0,
           name:'自分のみに通知',
           selected:true,
           show:true
         }
       ]
     }
      return list
    },
    jsonAttachments(){
      return JSON.stringify(this.attachments)
    }
  },
  watch: {
    showPostDialog(val) {
      this.combErrorFlg = false
      this.combErrorMessage = ''
    },
    bbsTopic:{
      deep:true,
      handler(val){
        if(val.view_type == 0){
          this.bbsTopic.notify_type = 0
        }
      }
    }
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
  },
  methods: {
    ...mapActions({
      getBbsCategories: "portal/getFaqBbsCategories",
    }),
    async onSaveClick() {
      this.isContent = false     
      const validate = await this.$validator.validateAll()
      this.bbsTopic.start_date = this.start_date
      if (this.end_date === '') this.end_date = null 
      this.bbsTopic.end_date = this.end_date
      this.bbsTopic.content = window.tuieditor.getHTML()
      let validateHtml = window.tuieditor.getEditorElements().wwEditor.innerText.replace(/\n/g,'').trim();
      if (!this.bbsTopic.content || validateHtml.length ===0) {
        this.isContent = true
        return
      }
      if (!validate) return

      this.$emit('savePost', {addFlg: this.addFlg, data:this.bbsTopic, files:this.attachments})

    },
    handleInput(item) {
      if (!item) {
        this.data.category = null
        return
      }
      this.$validator.errors.remove('name')
      if (typeof item === 'string') {
        this.data.category = item
      } else {
        this.data.category = item.name
      }
    },

    getBbsCategory: async function () {
      let infoTopic  = { 
        allflg:'1'       
      };
      let data = await this.getBbsCategories(infoTopic);

      this.bbsCategoryList       = data.data;

      
    },
    onSearch: async function () {
      var editobj = JSON.parse(JSON.stringify(this.editData));

      this.bbsTopic = editobj.value;
      this.bbsTopic.contentbf = this.bbsTopic.content;
      if (!this.bbsTopic.attachments) {
        this.dispAttachments = [];
      } else {
        this.bbsTopic.attachments.forEach(value => {
          this.attachments.push({
            id:value.id,
            name: value.name,
            size: value.size,
            type: 'history',
            createAt : value.createAt
          });
        });
      }

    },
    onChangeBbsCategory(val) {
      this.selCategory=val.name;
      this.bbsTopic.bbs_category_id = val.bbs_category_id;
    },
    onChangeBbsNotify(val) {
      this.notify_type=val.name;
      this.bbsTopic.notify_type = val.id;
    },
    onChangeBbsView(val) {
      this.view_type=val.name;
      this.bbsTopic.view_type = val.id;
    },
   }
}
</script>
<style lang="scss">

#editor {
  .tui-editor-contents {
    min-height: 0 !important;
  }
  .toastui-editor-contents {
    font-family: -apple-system, BlinkMacSystemFont, "Noto Sans JP", MS Gothic, "Montserrat", Helvetica, Arial, sans-serif !important;
    del{
      text-decoration: line-through #000000 !important;
      color: #222 !important;
    }
    strong{
      font-weight: 900 !important;
    }
  }
}
#form-category {
  .v-text-field__details {
    display: none;
  }
}
#form-category-mobile {
  .v-text-field__details {
    display: none;
  }
}
.headline
{
  font-size: large;
  font-weight: 500;
}
.headline
{
  font-size: large;
  font-weight: 500;
}
.v-toolbar__content, .v-toolbar__extension {
  align-items: center;
  display: flex;
  position: relative;
  z-index: 0;
  padding: 4px 16px;
}
.vs_dialog_header_primary
{
  background-color: rgba(var(--vs-primary),1)!important;
}
.vs_dialog_header_primary h2{
  color:#FFF;
}
@media only screen and (max-width: 576px) {
  #addtopic{
    .vs-row{
      padding: 20px 0 !important;
    }
  }
}
</style>