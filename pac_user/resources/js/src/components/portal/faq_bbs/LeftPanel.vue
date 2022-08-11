<template>

  <div class="aside-category mr-3" :style="asideCategoryStyle">
    <div class="leftpanelwidth">
      <div tabindex="-1" class="vs-button-div">
        <vs-button color="primary" @click="onAddTopicShow" >投稿</vs-button>
      </div>
      <vs-divider></vs-divider>

      <div class="v-list">
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

        <vs-row :data="item" :key="item.id" v-for="item in bbsCategoryList" class="leftcategorylist" >
          <div class="selectrow" @click="selectCategory(item)" v-bind:class="[{ 'v-list-item--active': item.id == category.bbs_category_id }]">
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
    }
  },

  computed: {
    
    usersFeed() {
      return this.users.slice().map((user) => user.name)
    },
    asideCategoryStyle() {
      if (this.WindowWidth > 960) {
        return 'left: 55px'
      } else {
        return ''
      }
    }
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
      getBbsCategories: "portal/getFaqBbsCategories",
    }),

    selectCategory: async function(vcategory) {

      this.category = {};
      if (vcategory) this.category={bbs_category_id: vcategory.id, name: vcategory.name};

      switch(this.$store.state.portal.bbsDispList){
        case 'topicdetail':
        case 'categorylist':
          this.changeDisp('topiclist');
          break;
        case 'topiclist':
          await this.$emit('selectCategory', this.category)
          break;

      }

    },
    getDispEdit(){
      this.isDispEdit = false;
      if(this.$store.state.portal.bbsDispList != 'categorylist') this.isDispEdit = true;
    },
    changeDisp(val) {
      this.$store.commit('portal/setBbsDispList', val);
      this.getDispEdit();

      let infoDisp  = {
        category   : this.category
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

