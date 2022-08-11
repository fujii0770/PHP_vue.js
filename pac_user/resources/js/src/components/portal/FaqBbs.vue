<template>
    <div id="bbs" class="containar">
        <split-pane :min-percent='!isMobile ? 0 : 100' :default-percent='!isMobile ? 10 : 0' split="vertical">
            <template slot="paneL">
              <bbs-left-panel v-if="!isMobile"
                id="leftpanel"
                ref="leftpanel"
                @changeDisp="changeDisp"
                @selectCategory="selectCategory"
                @onAddTopicShow="onAddTopicShow" >
              </bbs-left-panel>
            </template>
            <template slot="paneR">
              <topic-list v-if="isDispTList"
                id="topiclist"
                v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
                ref="topiclist"
                @changeDisp="changeDisp"
                @onAddTopicShow="onAddTopicShow"
                :pcategory="category" >
              </topic-list>
              <topic-detail v-if="isDispTDetail"
                id="topicdetail"
                v-bind:class="[isDispWide ? 'listdwh_w' : 'listdwh_s']"
                ref="topicdetail"
                @changeDisp="changeDisp"
                @onAddTopicShow="onAddTopicShow"
                :category="category"
                :bbsId="bbsId">
              </topic-detail>
            </template>
        </split-pane>
      <modal name="add-faq_topic-modal"
            :pivot-y="0.2"
            :width="!isMobile?750:'90%'"
            :classes="['v--modal', 'add-topic-modal']"
            :styles="['font-size:14px;']"
            :height="'auto'"
            :scrollable="true"
            :clickToClose="false">
      <add-update-dialog
        :edit-data="editData"
        @onAddTopicHide="onAddTopicHide"
        @savePost="savePost"            
      >
      </add-update-dialog>
    </modal>
</div>

</template>

<script>

import config from "../../app.config";
import { mapState, mapActions } from "vuex";
import qs from 'qs';
import splitPane from 'vue-splitpane';

export default {
  components: {
    BbsLeftPanel: () => import('@/components/portal/faq_bbs/LeftPanel'),
    TopicList: () => import('@/components/portal/faq_bbs/TopicList'),
    BbsCategoryList: () => import('@/components/portal/faq_bbs/CategoryList'),
    TopicDetail: () => import('@/components/portal/faq_bbs/TopicDetail'),
    AddUpdateDialog: () => import('@/components/portal/faq_bbs/AddTopicDialog'),
    splitPane,
  },
  name: "FaqBbs",
  props: {
    componentType: {
      default: '',
      type: String
    },
    currentLayout : {
      default: null,
      type: Object
    },

    width:{default:0},
    height:{default:0},
  },
  data() {
    return {
      heightBBS: 0,
      widthBBS: 0,
      topicChange: false,
      category:{},
      isMobile:false,
      isMounted:false,
      isDispWide:false,
      isDispCategory:false,
      isDispTList:true,
      isDispTDetail:false,
      editData:{},
    }
  },
  created() {
    if (
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      )
    ) {
      this.isMobile = true
    }
    
    this.$store.commit('portal/setBbsDispList', 'topiclist'); 
      window.addEventListener('resize', this.detectWindowSize, false);
  },
  mounted: function() {
    this.detectWindowSize();
    this.isMounted=true;
  },
  methods: {  
    ...mapActions({
      addBbsTopic: "portal/addFaqBbsTopic",
      updateBbsTopic:"portal/updateFaqBbsTopic",
      addLogOperation: "logOperation/addLog",
    }),
    goViewCategories() {
      this.$router.push('/bbs')

    },
    onBbsCategory() {
      this.$router.push('/bbs/categories')

    },
    changeDisp (val)
    {
      if (val.category) this.category=val.category;
      if (val.bbsId) this.bbsId=val.bbsId;

      this.getDispList();
    },
    selectCategory(val)
    {
      this.$refs.topiclist.selectCategory(val);
    },
    dispCategory()
    {
      this.$refs.leftpanel.getBbsCategory();
    },
    onAddTopicShow(val) {
      this.editData = {
        addFlg:val.addFlg, 
        value:val.value,
        attachmentsNames:val.attachmentsNames
      }
      this.$modal.show('add-faq_topic-modal')
    },
    onAddTopicHide() {
      this.$modal.hide('add-faq_topic-modal')
    },
    async savePost(value) {
      let data = value.data;

      if (value.addFlg == true){
        const info = {
          value:{
            title: data.title,
            content: data.content,
            bbs_category_id: data.bbs_category_id,
            
            view_type:data.view_type,
            notify_type:data.notify_type
          },
          attachment:value.files
        }
        let res = await this.addBbsTopic(info);
        if(res){
          this.addLogOperation({action: 'portal-topic-add-faq-bbs', result: 0})
        }else{
          this.addLogOperation({action: 'portal-topic-add-faq-bbs', result: 1})
        }
      }else{
        let info={
          id:data.id,
          value:data,
          attachment:value.files
        }
        let res = await this.updateBbsTopic(info);
        if(res){
          this.addLogOperation({action: 'portal-topic-update-faq-bbs', result: 0})
        }else{
          this.addLogOperation({action: 'portal-topic-update-faq-bbs', result: 1})
        }
      }
      this.onAddTopicHide();
      switch(this.$store.state.portal.bbsDispList){
        case 'categorylist' :
          break;
        case 'topiclist' :
            this.$refs.topiclist.onSearch();
          break;
        case 'topicdetail' :
          this.$refs.topicdetail.onSearch(1);
          break;
      } 
    },

    getDispList(){
      this.isDispCategory=false;
      this.isDispTList=false;
      this.isDispTDetail=false;
      switch(this.$store.state.portal.bbsDispList){
        case 'categorylist' :
          this.isDispCategory=true;
          break;
        case 'topiclist' :
          this.isDispTList=true;
          break;
        case 'topicdetail' :
          this.isDispTDetail=true;
          break;
      }      
    },
    detectWindowSize: function() {
      if (document.getElementById('faq_bulletin_board')) {
        var innerwidth = document.getElementById('faq_bulletin_board').clientWidth;
      
        if (!this.isMobile && innerwidth > 590) {
          this.$store.commit('portal/setDispWide', true);
          this.isDispWide = true;
        } else if (this.isMobile || innerwidth < 591) {
          this.$store.commit('portal/setDispWide', false);
          this.isDispWide = false;
        } else {
          this.$store.commit('portal/setDispWide', false);
          this.isDispWide = false;
        }
      }

      if (this.isMounted) {
        this.isDispCategory=false;
        this.isDispTList=false;
        this.isDispTDetail=false;
        switch(this.$store.state.portal.bbsDispList){
          case 'categorylist' :
            this.isDispCategory=true;
            this.$refs.categorylist.isDispChange();
            break;
          case 'topiclist' :
            this.isDispTList=true;
            if (this.$refs.topiclist === undefined) {
                const $this = this;
                setTimeout(function () {
                    if ($this.$refs.topiclist) $this.$refs.topiclist.isDispChange();
                },1000)
            } else {
                this.$refs.topiclist.isDispChange();
            }
            break;
          case 'topicdetail' :
            this.isDispTDetail=true;
            this.$refs.topicdetail.isDispChange();
            break;
        }
      }
    }
  },
  destroyed: function() {
      window.removeEventListener('resize', this.detectWindowSize);
  },
}
</script>

<style lang="scss">
@media only screen and (max-width: 576px) {
  input[type=text], select, textarea{
    transform: scale(1);
  }
  button{
    line-height: 10px;
  }
  .vs-table--tbody-table{
    min-width: auto;
    // max-width: calc(100% - 2px) !important;
  }
  #faq_bulletin_board{
    .bbs{
      width: 100%;
      #bbs{
        #topiclist{
          .vs-table--tbody-table{
            min-width: auto;
          }
          .topic_menubar button#btnt{
            padding: 0.25rem;
            min-width: 45px;
          }
        }
        .v--modal{
          max-width: 90%;
          left: 5% !important;
        }
        #categorylist{
          .vs-table--tbody{
            overflow: hidden;
          }
          table {
            > div{
              background-color: #fff;
            }
          }
          .action-detail{
            button{
              padding: 1rem;
            }
          }
          .clistDetailEdit{
            padding-top: 5px;

            &.vs-input{
              margin-bottom: 20px;
            }
          }
        }
      }
    }
  }
}
</style>