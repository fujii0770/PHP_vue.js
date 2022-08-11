<template>

  <div class="aside-category mr-3" :style="asideCategoryStyle">
    <div class="leftpanelwidth">
      <div tabindex="-1" class="vs-button-div">
        <vs-button color="primary" @click="onAddTopicShow" v-if="boardPermission.topics_append">投稿</vs-button>
      </div>
      <div tabindex="-1" class="vs-button-div">
        <vs-button v-if="isDispEdit" class="nprimary" color="grey-light" @click="changeDisp('categorylist')">カテゴリ編集</vs-button>
        <vs-button v-if="isDispExpired" class="nprimary" color="grey-light" @click="changeDisp('expiredtopiclist')">掲載終了内容一覧</vs-button>
        <vs-button v-if="isDispDraft" class="nprimary" color="grey-light" @click="changeDisp('draftlist')">下書き一覧</vs-button>
        <vs-button v-if="(!isDispEdit || !isDispExpired || !isDispDraft) && !isExpiredDetail" class="nprimary" color="grey-light" @click="changeDisp('topiclist')">戻る</vs-button>
        <vs-button v-if="isExpiredDetail" class="nprimary" color="grey-light" @click="changeDisp('expiredtopiclist')">戻る</vs-button>
      </div>
      <vs-divider></vs-divider>

      <div class="v-list" v-if="bbsCategoryListShow" :style="getBbsDispList === 'topiclist' ? 'height:calc(100vh - 327px)' : ''">
        <p class="nodata" v-if="!bbsCategoryList || !bbsCategoryList.length">
          データがありません。
        </p>
        <vs-row class="leftcategorylist ">
          <div class="selectrow allcategory" @click="selectCategory()">
            <vs-col vs-w="3" class="cate-icon"><feather-icon icon="FileTextIcon"></feather-icon></vs-col>
            <vs-col vs-w="9" class="cate-title">
              <div class="v-list-item__title">全ての投稿</div>
            </vs-col>
          </div>
        </vs-row>

        <vs-row :data="item" :key="item.bbs_category_id" v-for="item in bbsCategoryList" class="leftcategorylist" >
          <div class="selectrow" @click="selectCategory(item)" v-bind:class="[{ 'v-list-item--active': item.bbs_category_id == category.bbs_category_id }]">
            <vs-col vs-w="3" class="cate-icon"><feather-icon icon="FileTextIcon"></feather-icon></vs-col>
            <vs-col vs-w="9" class="cate-title">
              <div class="v-list-item__title" >{{ item.name ? item.name :'' }}</div>
            </vs-col>

          </div>
        </vs-row>

      </div>
      <vs-divider></vs-divider>
    </div>
    <vs-popup title="確認" :active.sync="accessErrorDialog">
      <div class="mb-0">アクセス権限がないため、投稿できません。</div><br>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="activeSaveLongtermModal = false">閉じる</vs-button>
      </vs-row>
    </vs-popup>
  </div>
</template>
<script>
import { mapState, mapActions } from "vuex";

export default {

components: {
  AddUpdateDialog: () => import('@/components/portal/bbs/AddTopicDialog'),
},
  props: {
      isExpired: {},
  },
  data() {
    return {
      showPostDialog: false,
      post: {},
      postDefault: {},
      accessErrorDialog: false,
      bbsCategoryList:[],
      isCategoryPage: false,
      editData:{},
      isMobile:false,
      category:{},
      isDispEdit:true,
      isDispExpired: true,
      isDispDraft: true,
    }
  },

  computed: {
    /*PAC_5-2376 S*/
    ...mapState({
      boardPermission:state => state.groupware.boardPermission
    }),
    /*PAC_5-2376 E*/
    usersFeed() {
      return this.users.slice().map((user) => user.name)
    },
    asideCategoryStyle() {
      if (this.WindowWidth > 960) {
        return 'left: 55px'
      } else {
        return ''
      }
    },
    getBbsDispList() {
        return this.$store.state.portal.bbsDispList;
    },
    bbsCategoryListShow() {
        return this.$store.state.portal.bbsDispList !== 'draftlist' && this.isExpired != 1;
    },
    isExpiredDetail() {
        return this.isExpired === 1 && this.$store.state.portal.bbsDispList == 'topicdetail';
    },
  },
  mounted() {
 
    this.getDispEdit();  
    this.getBbsCategory();
  },
  watch: {
    accessErrorDialog(val) {
      val || this.accessErrorDialogClose()
    }
  },
  async created() {

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
      addLogOperation: "logOperation/addLog",
    }),

    selectCategory: async function(vcategory) {

      this.category = {};
      if (vcategory) this.category={bbs_category_id: vcategory.bbs_category_id, name: vcategory.name};

      switch(this.$store.state.portal.bbsDispList){
        case 'topicdetail':
        case 'categorylist':
          this.changeDisp('topiclist');
          break;
        case 'topiclist':
          await this.$emit('selectCategory', this.category)
          break;
        case 'expiredtopiclist':
          await this.$emit('selectCategory', this.category)
          break;
      }

    },
    getDispEdit(){
      this.isDispEdit = false;
      this.isDispDraft = false;
      this.isDispExpired = false;
      if(this.$store.state.portal.bbsDispList == 'topiclist'){
        this.isDispEdit = true;
        this.isDispExpired = true;
          this.isDispDraft = true;
      }
    },
    changeDisp(val) {
      this.$store.commit('portal/setBbsDispList', val); 
      this.getDispEdit();

      let infoDisp  = {
        category   : this.category,
        isExpired  : 0,
      };
      this.$emit('changeDisp', infoDisp)
    },
    onAddTopicShow() {
      this.editData = {
        addFlg:true,
        value:null
      }
      this.$emit('onAddTopicShow',  this.editData)
    },
    createPost() {
      if (
        this.getRoleUserPrivilegesCodeListForTopic.includes(
          this.getApplicationPrivilegesForTopic.create
        )
      ) {
        this.post = Object.assign({}, this.postDefault)
        this.post.category = this.category ? this.category.name : null
        this.showPostDialog = true
      } else {
        this.accessErrorDialog = true
      }
    },
    closePostDialog() {
      this.showPostDialog = false
    },
    async savePost(data) {
      const info = {
        value:{
          title: data.title,
          content: data.content,
          bbs_category_id: data.bbs_category_id,
          mst_user_id:'',
        }
      }
      await this.addBbsTopic(info);
      this.onAddTopicHide();
      await this.getBbsCategories();
      await this.$emit('selectCategory', this.category)
    },
    accessErrorDialogClose() {
      this.accessErrorDialog = false
    },
    getBbsCategory: async function () {
      let infoTopic  = { 
        allflg:'1'       
      };
      let data = await this.getBbsCategories(infoTopic);
      this.bbsCategoryList       = data.data;
    }
  }

}
</script>
<style>


</style>

