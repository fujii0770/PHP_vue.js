<template>
  <div class="main-article">
    <div v-if="isDispWide">
      <search-box v-model="filter.keyword" @clearSearchString="clearSearchModel" @clickSearch="onSearch(true)"></search-box>
      <vs-card class="mx-auto" max-width="100vw">

        <div class="headline title font-weight-bold">
          {{ category && category.name ? category.name : '全ての投稿' }}
        </div>
        <vs-row>
          <vs-col cols="12" style="width:10%">
            <vs-checkbox 
            v-if="isCurrentTopic"
            v-model="checkAll"
            class=""
            style="padding-left: 15px;padding-top:10px;"
            color="primary">
            </vs-checkbox>
          </vs-col>
          <vs-col  cols="12"  style="width:30%">
            <vs-button class="square"  color="danger" @click="onBulkDelete"><i class="far fa-trash-alt"></i> 削除</vs-button>
          </vs-col>
          <vs-col cols="12"  style="width:60%" class="actionrow">
            <div style="margin-left:auto;margin-top: 5px;">
              <div style="min-width:100px;padding-right:10px;margin-top:10px;">
              <span :style="howManyItemsStyle">
                {{pagination.from ? pagination.from :0}}-{{pagination.to ? pagination.to :0}}件目 / {{ pagination.totalItem ? pagination.totalItem:0}}件</span>
              </div>
              <div style="padding-right:10px;">
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
              </div>
            </div>
          </vs-col> 
        </vs-row>
        <vs-table class="mt-3 table-topic-width"
          :data="bbsTopicList"
          noDataText="データがありません。"
          sst stripe>
          <template slot-scope="{data}">
            <vs-tr :data="item" :key="index" v-for="(item, index) in data">
              <vs-td style="width:10%">
                <vs-checkbox
                  v-if="item.isAuthEditAndDelete"
                  :id="`${item.id}`"
                  v-model="item.checked"
                  color="primary">
                </vs-checkbox>
              </vs-td>
              <vs-td style="width:90%" @click="onDetailTopicShow(item)">
                <div @click="onDetailTopicShow(item)">
                <div>{{ item.title ? item.title : ''}}</div>
                  <div>表示設定：{{view_type_list[item.view_type].name}}</div>
                  <div>通知設定：{{notify_type_list[item.notify_type].name}}</div>
                <div>登録日時：{{item.created_at}}</div>                            
                </div>
              </vs-td>
            </vs-tr>
          </template>
        </vs-table>
      </vs-card>
    </div>
    <div v-else>
      <vs-card class="mx-auto" max-width="100vw">
        <div  v-if="!showSearchBox">

          <div class="topic_menubar">
            <div class="headline title font-weight-bold">
              {{ category && category.name ? category.name : '全ての投稿' }}
            </div>
            <vs-spacer style="flex-grow: 1!important;"/>
            <vs-button color="primary" @click="onAddTopicShow" id="btnt" >投稿</vs-button>
            <button-icon icon-name="search" class="btnicon" color="white" @clickButton="showSearchBox = !showSearchBox"></button-icon>
            <category-menu @selectCategory="selectCategory" @changeDisp="changeDisp"></category-menu>
          </div>
          <div style="display:flex;">
            <vs-spacer style="flex-grow: 1!important;"/>
              <div style="min-width:100px;padding-right:10px;padding-top:10px;">
              <span :style="howManyItemsStyle">
                {{pagination.from}}-{{pagination.to}}件目 / {{ pagination.totalItem }}件</span>
              </div>

              <div style="padding-right:10px;">
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
              </div>
          </div>
        </div>
        <div  v-if="showSearchBox" style="display: flex;">
          <button-icon icon-name="arrow_back" class="btnicon" color="white" @clickButton="backToList"></button-icon>
          <search-box v-model="filter.keyword" @clearSearchString="clearSearchModel" @clickSearch="onSearch"></search-box>
        </div>

        <vs-table class="mt-3 table-topic-width"
          :data="bbsTopicList"
          noDataText="データがありません。"
          sst stripe>
          <template slot-scope="{data}">
            <vs-tr :data="item" :key="index" v-for="(item, index) in data">
              <vs-td vs-w="11" @click="onDetailTopicShow(item)">
                <div @click="onDetailTopicShow(item)">
                <div>{{ item.title ? item.title:''}}</div>
                  <div>表示設定：{{view_type_list[item.view_type].name}}</div>
                  <div>通知設定：{{notify_type_list[item.notify_type].name}}</div>
                  <div>登録日時：{{item.created_at}}</div>                            
                </div>
              </vs-td>
            </vs-tr>
          </template>
        </vs-table>
      </vs-card>    
    </div>
    <vs-popup title="確認" :active.sync="deleteModal">
      <div class="mb-0">投稿を削除してよろしいですか?</div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button @click="doBulkDelete" color="warning">OK</vs-button>
        <vs-button @click="deleteModal = false" color="dark" type="border">キャンセル</vs-button>
      </vs-row>
    </vs-popup> 
    <vs-popup title="確認" :active.sync="deleteErrorDialog">
      <div class="mb-0">削除できるトピックがありません。</div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button @click="deleteErrorDialogClose = false;deleteErrorDialog=false;" color="dark" type="border">閉じる</vs-button>
      </vs-row>
    </vs-popup> 
  </div>
</template>
<script>

import { mapState, mapActions } from "vuex";

export default {
  components: { 
    SearchBox: () => import('@/components/portal/faq_bbs/SearchBox'),
    ButtonIcon: () => import('@/components/portal/faq_bbs/ButtonIcon'),
    VxPagination: () => import('@/components/vx-pagination/VxPagination.vue'),
    CategoryMenu: () => import('@/components/portal/faq_bbs/CategoryMenu'),
  },
  props: {
    topicChange: { type: Boolean, default: false },
    pcategory:{},
  },
  mounted() {
    this.isDispWide = this.$store.state.portal.isDispWide;

    if(this.pcategory){
      this.selectCategory(this.pcategory);
    }else{
      this.onSearch();
    }
  },
  data() {
    return {
      bbsTopicList:[],
      pagination: {
        totalPage: 0,
        currentPage: 1,
        limit: 10,
        totalItem: 0,
        from: 1,
        to: 10
      },
      filter: {
        bbsCategoryId: '',
        keyword: ''
      },
      deleteModal: false,
      deleteErrorDialog: false,
      dispSizeDef:10,
      category:{
        bbsCategoryId:'',
        name:''
      },
      isMobile:false,
      editData:{},
      showSearchBox :false,
      isDispWide:false,
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
    checkAll: {
      get() {
        if (!this.bbsTopicList || !this.bbsTopicList.length) return false
        // 削除できるトピック（自分が作成者のトピック）を抽出
        const postFilter = this.bbsTopicList.filter((x) => {
          return x.isAuthEditAndDelete
        })
        // 削除できるトピックがなければfalseをreturn
        if (!postFilter.length) return false
        // 削除できるトピックが全てチェックされていればtrueをreturn
        return postFilter.every((item) => item.checked)
      },
      set(value) {
        if (!this.bbsTopicList) return

        this.bbsTopicList = this.bbsTopicList.map((item) => {
          const newItem = Object.assign({}, item)

          // 削除できるトピック全てにチェックを入れる
          if (newItem.isAuthEditAndDelete)
            newItem.checked = value
          else newItem.checked = false
          return newItem
        })
      }

    },
    params() {
      return { ...this.pagination, ...this.filter }
    },
    howManyItemsStyle() {
      if (this.breakpointWidth > 693) {
        return 'min-width: 120px;text-align: center;font-size: 1rem;'
      } else {
        return 'min-width: 105px;text-align: center;font-size: 0.9rem;'
      }
    },
    isCurrentTopic() {
      const postFilter = this.bbsTopicList.filter((x) => {
        return x.isAuthEditAndDelete
      })
      if (postFilter.length) return true
      return false
    },

  },
  watch: {
    'pagination.currentPage': function (val) {
       this.onSearch();
    },
    'paginationSystem.currentPage': function (val) {
        this.onSearch();
    },
    'pagination.limit': function (val) {
       this.onSearch();
    },
    topicChange(val) {
        this.onSearch();
    },

    deleteErrorDialog(val) {
      val || this.deleteErrorDialogClose()
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
      getTopicList: "portal/getFaqTopicList",
      deleteBbsTopic:"portal/deleteFaqBbsTopic",
      getBbsCategories: "portal/getBbsCategories",
      addLogOperation: "logOperation/addLog",
    }),
   
    onSearch: async function (addLog = false) {
      let infoTopic  = {
        procKbn    : '0',
        categoryId : this.filter.bbsCategoryId,
        keyword    : this.filter.keyword,
        limit      : this.pagination.limit,
        page       : this.pagination.currentPage,
        type       : 1,
      };
      let data = await this.getTopicList(infoTopic);
      if (addLog) {
        if (data) {
          this.addLogOperation({action: 'portal-faq-bbs-search-topic', result: 0})
        } else {
          this.addLogOperation({action: 'portal-faq-bbs-search-topic', result: 1})
        }
      }

      this.bbsTopicList           = data.data;

　    this.pagination.totalItem   = data.total;
      this.pagination.totalPage   = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.limit       = data.per_page;
      this.pagination.from        = data.from;
      this.pagination.to          = data.to;

      if (this.bbsTopicList) {
        this.bbsTopicList = this.bbsTopicList.map((item) => {
          const newItem = Object.assign({}, item)
          newItem.checked = false
          return newItem
        })
      }
    },
    async clearSearchModel() {
      this.filter.keyword = '';
      this.onSearch();
    },
    onBulkDelete() {
      if (!this.isCurrentTopic) return (this.deleteErrorDialog = true)
      if (!this.bbsTopicList) return
      if (!this.bbsTopicList.some((item) => item.checked)) return
      this.deleteModal = true
    },
    async doBulkDelete() {
      if (!this.bbsTopicList) return
      const bbsTopicsIds = this.bbsTopicList
        .filter((item) => item.checked)
        .map((item) => item.id)
      if (!bbsTopicsIds.length) return

      this.deleteModal = false
      let info={
        ids:bbsTopicsIds
      }

      let res = await this.deleteBbsTopic(info)
      if(res){
        this.addLogOperation({action: 'portal-topic-del-faq-bbs', result: 0})
      }else{
        this.addLogOperation({action: 'portal-topic-del-faq-bbs', result: 1})
      }
      this.onSearch();

    },
    changePage(num) {
      const page = this.pagination.page + num
      if (page <= 0) return
      if (page > this.totalPage) return
      this.pagination.page = page
    },
    deleteErrorDialogClose() {
      this.deleteErrorDialog = false
    },
    selectCategory(val){
      if(!val){
        this.category.bbsCategoryId = ''
        this.category.name = ''
        this.filter.bbsCategoryId = ''
      }else{
        this.category.bbsCategoryId = val.bbs_category_id
        this.category.name = val.name
        this.filter.bbsCategoryId = val.bbs_category_id
      }

      this.onSearch()
    },

    onDetailTopicShow: async function (data) {
      this.$store.commit('portal/setBbsDispList', 'topicdetail');        
      let infoDisp  = {
        category   : this.category,
        bbsId      : data.id,          
      }
      this.$emit('changeDisp', infoDisp)
    
    },
    onAddTopicShow() {
      this.editData = {
        addFlg:true,
        value:null
      }

      this.$emit('onAddTopicShow',  this.editData)
    },

    isDispChange(){
      this.isDispWide = this.$store.state.portal.isDispWide;
    },
    async backToList() {
      this.filter.keyword = '';
      this.showSearchBox = false;
      await this.onSearch();
    },
    changeDisp: async function (data) {
      let infoDisp  = {
          category   : this.category,      
      }
      this.$emit('changeDisp', infoDisp)
    
    },
  }
}
</script>
<style lang="scss">
.vs-table--tbody{
  z-index:0;
}

</style>