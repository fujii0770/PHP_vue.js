<template>
  <vs-dropdown vs-custom-content vs-trigger-click class="cursor-pointer">
    <button-icon icon-name="more_horiz" class="btnicon" color="white"></button-icon>

    <vs-dropdown-menu class="vx-navbar-dropdown" style="width:170px;height:470px;" id="categorymenu">
      
      <vs-divider style="margin:2px;"></vs-divider>
      <div class="v-list" style="overflow-y: auto;max-height: calc(420px);">
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

        <vs-row :data="item" :key="item.id" v-for="item in bbsCategoryList" class="leftcategorylist" >
          <div class="selectrow" @click="selectCategory(item)" v-bind:class="[{ 'v-list-item--active': item.id == category.bbs_category_id }]">
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
    height:{default:0}
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
  },
  methods: {
    ...mapActions({
      getBbsCategories: "portal/getFaqBbsCategories",
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
      if (vcategory) this.category={bbs_category_id: vcategory.id, name: vcategory.name};

      switch(this.$store.state.portal.bbsDispList){
        case 'topicdetail':
        case 'categorylist':
          this.changeDisp('bbslist');
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
  }
}
</script>
<style lang="scss">

</style>