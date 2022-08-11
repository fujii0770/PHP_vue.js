<template>
  <vs-dropdown vs-custom-content vs-trigger-click class="cursor-pointer">
    <button-icon icon-name="more_horiz" class="btnicon" color="white"></button-icon>

    <vs-dropdown-menu class="vx-navbar-dropdown" style="width:170px;height:470px;" id="categorymenu">
      <div tabindex="-1" class="vs-button-div" style="text-align:center;">
        <vs-button v-if="isDispEdit" class="nprimary" color="grey-light" style="color: inherit; width:95%; background-color: rgb(226, 240, 217);border-color: rgb(226, 240, 217);padding:10px;" @click="changeDisp('categorylist')">カテゴリ編集</vs-button>
        <vs-button v-if="isDispExpired" class="nprimary" color="grey-light" style="color: inherit; width:95%; background-color: rgb(226, 240, 217);border-color: rgb(226, 240, 217);padding:10px;margin-top: 5px;" @click="changeDisp('expiredtopiclist')">掲載終了内容一覧</vs-button>
        <vs-button v-if="isDispDraft" class="nprimary" color="grey-light" style="color: inherit; width:95%; background-color: rgb(226, 240, 217);border-color: rgb(226, 240, 217);padding:10px;margin-top: 5px;"  @click="changeDisp('draftlist')">下書き一覧</vs-button>
        <vs-button v-if="(!isDispEdit || !isDispExpired || !isDispDraft) && !isExpiredDetail" class="nprimary" color="grey-light" style="color: inherit; width:95%; background-color: rgb(226, 240, 217);border-color: rgb(226, 240, 217);padding:10px;margin-top: 5px;" @click="changeDisp('topiclist')">戻る</vs-button>
        <vs-button v-if="isExpiredDetail" class="nprimary" color="grey-light" style="color: inherit; width:95%; background-color: rgb(226, 240, 217);border-color: rgb(226, 240, 217);padding:10px;margin-top: 5px;" @click="changeDisp('expiredtopiclist')">戻る</vs-button>
      </div>
      <vs-divider style="margin:2px;"></vs-divider>
      <div class="v-list" style="overflow-y: auto;max-height: calc(420px);" v-if="bbsCategoryListShow">
        <p
          v-if="!bbsCategoryList || !bbsCategoryList.length"
          style="text-align: center"
        >
        データがありません。
        </p>
        <vs-row class="leftcategorylist ">
          <div class="selectrow allcategory" @click="selectCategory()" style="width:100%">
            <vs-col vs-w="9" style="width:90%;">
              <div class="v-list-item__title">全ての投稿</div>
            </vs-col>
          </div>
        </vs-row>

        <vs-row :data="item" :key="item.bbs_category_id" v-for="item in bbsCategoryList" class="leftcategorylist" >
          <div class="selectrow" @click="selectCategory(item)" v-bind:class="[{ 'v-list-item--active': item.bbs_category_id == category.bbs_category_id }]">
            <vs-col vs-w="9" style="width:90%;">
              <div class="v-list-item__title" >{{ item.name ? item.name :''}}</div>
            </vs-col> 
          </div>
        </vs-row>
      </div>

    </vs-dropdown-menu>
  </vs-dropdown>
</template>
<script>

import { mapState, mapActions } from "vuex";

export default {
  components: { 
    ButtonIcon: () => import('@/components/portal/bbs/ButtonIcon'),
  },
  props: {
    width:{default:0},
    height:{default:0},
    isExpired: {}
  },
  mounted() {
      this.getDispEdit();  
      this.getBbsCategory();
  },
  data() {
    return {
      category:{
        bbsCategoryId:'',
        name:''
      },
      isMobile:false,
      bbsCategoryList:[],
      isCategoryPage: false,
      isDispEdit:true,
      isDispExpired: true,
      isDispDraft: true,
    }
  },
  computed: {
    bbsCategoryListShow() {
        return this.$store.state.portal.bbsDispList !== 'draftlist' && this.isExpired != 1;
    },
    isExpiredDetail() {
        return this.isExpired === 1 && this.$store.state.portal.bbsDispList == 'topicdetail';
    },
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
  },
  methods: {
    ...mapActions({
      getBbsCategories: "portal/getBbsCategories",
    }),
   
    getBbsCategory: async function () {
      let infoTopic  = { 
        allflg:'1'       
      };
      let data = await this.getBbsCategories(infoTopic);
      this.bbsCategoryList       = data.data;
    },
    selectCategory: async function(vcategory) {
      this.category = {};
      if (vcategory) this.category={bbs_category_id: vcategory.bbs_category_id, name: vcategory.name};

      switch(this.$store.state.portal.bbsDispList){
        case 'topicdetail':
          this.changeDisp('topiclist');
          break;
        case 'categorylist':
          this.changeDisp('bbslist');
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
      this.isDispExpired = false;
      this.isDispDraft = false;
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
  }
}
</script>
<style lang="scss">

</style>