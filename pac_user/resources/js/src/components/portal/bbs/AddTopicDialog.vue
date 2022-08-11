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

      <vs-row style="padding: 0px 20px 0px 20px;">
        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">カテゴリ<span class="text-danger">*</span></div>
        </vs-col>
        <vs-col cols="12" vs-w="10" vs-xs="12">
          <v-select
            class="dropdown clistDetail"
            :value="selCategory"
            :options="bbsCategoryList"
            v-validate="{ required: true}"
            name="categoryid"
            label="name"
            dense
            no-data-text="データがありません。"
            placeholder="選択してください"
            style="width: 100%"
            @input="onChangeBbsCategory">
          </v-select>
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
          <p v-if="isContent" class="text-danger text-sm" style="padding-left:12px;" >* 必須項目です。</p>
        </vs-col>  

        <vs-col cols="12" vs-w="2" vs-xs="12">
          <div class="clistDetail">掲示期間</div>
        </vs-col>
        <vs-col vs-w="4" vs-sm="10" vs-xs="12" style="padding: 12px 12px 0px;">
            <div>掲示開始日</div>
            <vx-input-group  class="w-full mb-0">
                <flat-pickr ref="calendar" class="w-full" :config="configs.start" v-model="start_date" v-validate="{ required: true}" name="strdate" @on-change="onStartChange"></flat-pickr>
                <template slot="append">
                    <div class="append-text btn-addon">
                        <vs-button data-toggle color="primary" v-on:click="openCalendarClick"><i class="fas fa-calendar-alt"></i></vs-button>
                    </div>
                </template>
            </vx-input-group>
            <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('strdate')">{{ errors.first('strdate') }}</div>
        </vs-col>
        <vs-col cols="12" vs-w="2" vs-xs="12">
          &nbsp;
        </vs-col>
        <vs-col vs-w="4" vs-sm="10" vs-xs="12" style="padding: 12px 12px 0px;">
            <div>掲示終了日</div>
            <vx-input-group  class="w-full mb-0">
                <flat-pickr ref="calendar" class="w-full" :config="configs.end" v-model="end_date"  @on-change="onEndChange" ></flat-pickr>
                <template slot="append">
                    <div class="append-text btn-addon">
                        <vs-button data-toggle color="primary" v-on:click="openCalendarClick"><i class="fas fa-calendar-alt"></i></vs-button>
                    </div>
                </template>
            </vx-input-group>
            <div class="text-danger text-sm" style="padding-left:12px;" v-show="errors.has('enddate')">{{ errors.first('enddate') }}</div>
        </vs-col>
        <vs-col vs-w="3" vs-xs="10">
        </vs-col>
        <attachemnt v-model="attachments" :isMobile="isMobile" :initFiles="initFiles"></attachemnt>
      </vs-row>
      <vs-row>
        <div style="padding: 0px 20px 20px 20px;display: flex;width: 100%;" class="add-update-dialog-button">
          <vs-spacer></vs-spacer>
          <vs-button color="primary" @click="onSaveClick(1)">投稿</vs-button>
          <vs-button v-if="addFlg || (bbsTopic.state == 0)" color="primary" @click="onSaveClick(0)">一時保存</vs-button>
          <vs-button color="grey-light" class="nprimary"  @click="$emit('onAddTopicHide')" style="color: inherit;">キャンセル</vs-button>
        </div>
      </vs-row>
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
    isMobile: false
  },
  data() {
    return {
      isMobile: false,
      bbsCategoryList:[],
      bbsTopic:{},
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
      configs: {
        locale: Japanese,
        wrap: true,
        start: {
          maxDate: null,
          enableTime:true,
          altFormat:'Y-m-d H:i',
          time_24hr:true,
          defaultHour:0,
          minuteIncrement:1,
        },
        end: {
          minDate: null,
          enableTime:true,
          altFormat:'Y-m-d H:i',
          time_24hr:true,
          defaultHour:0,
          minuteIncrement:1,
        },
      },
      start_date:null,
      end_date:null,
      attachments:[],
    }
  },
  mounted() {
    this.getBbsCategory();
    this.addFlg = this.editData.addFlg;
    if (this.addFlg==false){
      this.onSearch();
      this.configs.end.minDate = this.start_date;
      this.configs.start.maxDate = this.end_date;
    }else{
      this.start_date =  this.$moment().format("YYYY-MM-DD");
      this.end_date = null;
      this.configs.end.minDate = this.$moment().format("YYYY-MM-DD");
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
          ['heading', 'bold', 'italic', 'strike'],
          ['hr', 'quote'],
          ['ul', 'ol', 'task'],
        ],
        plugins: [[ColorSyntax,colorSyntaxOptions]],
        placeholder: '内容'
      })
      window.tuieditor.setHTML(this.bbsTopic.content, true)
        $('#bulletin_board:not(.mobile) .selectrow.v-list-item--active .v-list-item__title').css('white-space', 'normal');
        setTimeout(() => {
          $('#bulletin_board:not(.mobile) .selectrow.v-list-item--active .v-list-item__title').css('white-space', 'nowrap');
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
    }
  },
  watch: {
    showPostDialog(val) {
      this.combErrorFlg = false
      this.combErrorMessage = ''
    },
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
      getBbsCategories: "portal/getBbsCategories",
    }),
    async onSaveClick(state) {
      this.isContent = false     
      const validate = await this.$validator.validateAll()
      this.bbsTopic.start_date = this.start_date
      if (this.end_date === '') this.end_date = null 
      this.bbsTopic.end_date = this.end_date
      this.bbsTopic.content = window.tuieditor.getHTML()
      let validateHtml = window.tuieditor.getEditorElements().wwEditor.innerText.replace(/\n/g,'').trim();
      if (!this.bbsTopic.content || validateHtml.length === 0) {
        this.isContent = true
        return
      }
      if (!validate) return

      this.bbsTopic.state = state;
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

      if (this.addFlg==false){
        let catdata = this.bbsCategoryList.find(item => item.bbs_category_id == this.bbsTopic.bbs_category_id);
          if (Object.keys(catdata).length>0) {
            this.onChangeBbsCategory(catdata);
          }

      }
    },
    onSearch: async function () {
      var editobj = JSON.parse(JSON.stringify(this.editData));

      this.bbsTopic = editobj.value;
      this.bbsTopic.contentbf = this.bbsTopic.content;
      this.start_date = this.bbsTopic.start_date ? this.bbsTopic.start_date: new Date().toISOString().slice(0,10);
      this.end_date = this.bbsTopic.end_date ? this.bbsTopic.end_date: null;
      if (!this.bbsTopic.attachments) {
        this.dispAttachments = [];
      } else {
        this.bbsTopic.attachments.forEach(value => {
          this.attachments.push({
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
    openCalendarClick: function() {
      this.$refs.calendar.fp.toggle();
    },
    onStartChange(selectedDates, dateStr, instance) {       
      this.configs.end.minDate = dateStr
    },
    onEndChange(selectedDates, dateStr, instance) {
      this.configs.start.maxDate = dateStr
    }
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
      text-decoration: line-through;
      text-decoration-color: #000000;
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

  .add-update-dialog-button{
    padding: 0 12px !important;
    button{
      padding: .75rem 1rem !important;

      &:last-child{
        margin-right: 0 !important;
      }
    }
  }
}
</style>