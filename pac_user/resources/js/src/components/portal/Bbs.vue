<template>
  <div>
      <div id="bbs" :class="'containar ' + (isMobile?'mobile':'')" v-if="!isMypage" style="display: contents;">
          <split-pane :min-percent='!isMobile ? 0 : 100' :default-percent='!isMobile ? 10 : 0' split="vertical">
              <template slot="paneL">
                  <bbs-left-panel v-if="!isMobile"
                    id="leftpanel"
                    ref="leftpanel"
                    @changeDisp="changeDisp"
                    @selectCategory="selectCategory"
                    @onAddTopicShow="onAddTopicShow"
                    :isExpired="isExpired">
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
                  <expired-topic-list v-if="isDispExpired"
                    id="expiredtopiclist"
                    v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
                    ref="expiredtopiclist"
                    @changeDisp="changeDisp"
                    :pcategory="category" >
                  </expired-topic-list>
                  <draft-list v-if="isDispDraft"
                    id="draftlist"
                    v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
                    ref="draftlist"
                    @changeDisp="changeDisp"
                    @onAddTopicShow="onAddTopicShow"
                    :pcategory="category" >
                  </draft-list>
                  <bbs-category-list v-if="isDispCategory"
                    id="categorylist"
                    v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
                    ref="categorylist"
                    @changeDisp="changeDisp"
                    @dispCategory="dispCategory" >
                  </bbs-category-list>
                  <topic-detail v-if="isDispTDetail"
                    id="topicdetail"
                    v-bind:class="[isDispWide ? 'listdwh_w' : 'listdwh_s']"
                    ref="topicdetail"
                    @changeDisp="changeDisp"
                    @onAddTopicShow="onAddTopicShow"
                    :category="category"
                    :bbsId="bbsId"
                    :loginUserProfileData="loginUserProfileData"
                    :isExpired="isExpired">
                  </topic-detail>
              </template>
          </split-pane>
        <modal name="add-topic-modal"
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
            :isMobile="isMobile"            
          >
          </add-update-dialog>
        </modal>
      </div>

      <div id="bbs" class="mobile" v-else>
          <topic-list v-if="isDispTList"
            id="topiclist"
            v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
            ref="topiclist"
            @changeDisp="changeDisp"
            @onAddTopicShow="onAddTopicShow"
            :pcategory="category" 
            :isMypage="isMypage"
            >
          </topic-list>
          <expired-topic-list v-if="isDispExpired"
            id="expiredtopiclist"
            v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
            ref="expiredtopiclist"
            @changeDisp="changeDisp"
            :pcategory="category" >
          </expired-topic-list>
          <draft-list v-if="isDispDraft"
            id="draftlist"
            v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
            ref="draftlist"
            @changeDisp="changeDisp"
            @onAddTopicShow="onAddTopicShow"
            :pcategory="category" >
          </draft-list>
          <bbs-category-list v-if="isDispCategory"
            id="categorylist"
            v-bind:class="[isDispWide ? 'listwidth_w' : 'listwidth_s']"
            ref="categorylist"
            @changeDisp="changeDisp"
            @dispCategory="dispCategory" >
          </bbs-category-list>
          <topic-detail v-if="isDispTDetail"
            id="topicdetail"
            v-bind:class="[isDispWide ? 'listdwh_w' : 'listdwh_s']"
            ref="topicdetail"
            @changeDisp="changeDisp"
            @onAddTopicShow="onAddTopicShow"
            :category="category"
            :bbsId="bbsId"
            :loginUserProfileData="loginUserProfileData"
            :isExpired="isExpired">
          </topic-detail>

      </div>

  </div>

</template>

<script>

import config from "../../app.config";
import { mapState, mapActions } from "vuex";
import qs from 'qs';
import splitPane from 'vue-splitpane';

export default {
  components: {
    BbsLeftPanel: () => import('@/components/portal/bbs/LeftPanel'),
    TopicList: () => import('@/components/portal/bbs/TopicList'),
    ExpiredTopicList: () => import('@/components/portal/bbs/ExpiredTopicList'),
    DraftList: () => import('@/components/portal/bbs/DraftList'),
    BbsCategoryList: () => import('@/components/portal/bbs/CategoryList'),
    TopicDetail: () => import('@/components/portal/bbs/TopicDetail'),
    AddUpdateDialog: () => import('@/components/portal/bbs/AddTopicDialog'),
    splitPane,
  },
  name: "Bbs",
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
    isMypage: false
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
      isDispExpired:false,
      isDispTDetail:false,
      isDispDraft: false,
      editData:{},
      loginUserProfileData: '',
      isExpired: 0,
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
    this.getAvatarUser().then(data => {
        if (data && data.user_profile_data) this.loginUserProfileData = data.user_profile_data;
    });
      window.addEventListener('resize', this.detectWindowSize, false);
  },
  mounted: function() {
    this.detectWindowSize();
    this.isMounted=true;
  },
  methods: {
    ...mapActions({
      addBbsTopic: "portal/addBbsTopic",
      updateBbsTopic:"portal/updateBbsTopic",
      getAvatarUser: "user/getAvatarUser",
      addBbsDraftTopic: "portal/addBbsDraftTopic",
      updateBbsDraftTopic:"portal/updateBbsDraftTopic",
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
      if (val.isExpired !== undefined && val.isExpired !== null) {
          this.isExpired=val.isExpired;
      } else {
          this.isExpired = 0;
      }

      this.getDispList();
    },
    selectCategory(val)
    {
      if (this.$store.state.portal.bbsDispList == 'expiredtopiclist') {
          this.$refs.expiredtopiclist.selectCategory(val);
      } else {
          this.$refs.topiclist.selectCategory(val);
      }
    },
    dispCategory()
    {
      if (this.$refs.leftpanel){
        this.$refs.leftpanel.getBbsCategory(); 
      }
    },
    onAddTopicShow(val) {
      this.editData = {
        addFlg:val.addFlg, 
        value:val.value,
        attachmentsNames:val.attachmentsNames
      }
      this.$modal.show('add-topic-modal')
    },
    onAddTopicHide() {
      this.$modal.hide('add-topic-modal')
    },
    async savePost(value) {
      let data = value.data;

      if (value.addFlg == true){
        const info = {
          value:{
            title: data.title,
            content: data.content,
            bbs_category_id: data.bbs_category_id,
            mst_user_id:'',
            start_date:data.start_date,
            end_date:data.end_date,
            state: data.state,
          },
          attachment:value.files,
          state: data.state,
        }
        if (data.state == 0) {
            await this.addBbsDraftTopic(info);
        } else {
            await this.addBbsTopic(info);
        }
      }else{
        let info={
          id:data.id,
          value:data,
          attachment:value.files,
          state: data.state,
        }
        if (data.state == 0) {
            await this.updateBbsDraftTopic(info);
        } else {
            await this.updateBbsTopic(info);
        }
      }
      this.onAddTopicHide();
      switch(this.$store.state.portal.bbsDispList){
        case 'categorylist' :
          break;
        case 'topiclist' :
            this.$refs.topiclist.onSearch();
          break;
        case 'expiredtopiclist':
          this.$refs.expiredtopiclist.onSearch();
          break;
        case 'topicdetail' :
          this.$refs.topicdetail.onSearch(1);
          break;
        case 'draftlist':
          this.$refs.draftlist.onSearch(1);
          break;
      } 
    },

    getDispList(){
      this.isDispCategory=false;
      this.isDispTList=false;
      this.isDispExpired = false;
      this.isDispTDetail=false;
      this.isDispDraft = false;
      switch(this.$store.state.portal.bbsDispList){
        case 'categorylist' :
          this.isDispCategory=true;
          break;
        case 'topiclist' :
          this.isDispTList=true;
          break;
        case 'expiredtopiclist':
          this.isDispExpired=true;
          break;
        case 'topicdetail' :
          this.isDispTDetail=true;
          break;
        case 'draftlist':
          this.isDispDraft = true;
          break;
      }      
    },
    detectWindowSize: function() {
      if (document.getElementById('bulletin_board')) {
        var innerwidth = document.getElementById('bulletin_board').clientWidth;
      
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
        this.isDispExpired = false;
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
          case 'expiredtopiclist':
            this.isDispExpired=true;
            this.$refs.expiredtopiclist.isDispChange();
            break;
          case 'topicdetail' :
            this.isDispTDetail=true;
            this.$refs.topicdetail.isDispChange();
            break;
          case 'draftlist':
            this.isDispDraft = true;
            this.$refs.draftlist.isDispChange();
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
  #bulletin_board{
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

        &.mobile{
          margin-top: -50px;
        }
      }
    }
  }
}
</style>