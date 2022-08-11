<template>
  <div class="main-article">
    <vs-card class="category-list mx-auto" max-width="100vw">
      <div v-if="isMobile">
        <vs-row>
          <vs-col  cols="12"  style="width:30%">
            <vs-button @click="onEditCategoryAdd" class="addCategory" v-if="boardPermission.category_append">カテゴリ追加</vs-button>
          </vs-col>
          <vs-col cols="12"  style="width:70%" class="actionrow">
            <div style="margin-left:auto;margin-top: 10px;">
              <div style="min-width:100px;padding-right:10px;margin-top:10px;">
                <span :style="howManyItemsStyle">
                  {{pagination.from ? pagination.from:0}}-{{pagination.to?pagination.to:0}}件目 / {{ pagination.totalItem?pagination.totalItem:0 }}件</span>
              </div>
              <div style="padding-right:10px;">
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
              </div>
            </div>
          </vs-col> 
        </vs-row>

        <vs-table class="mt-3 table-category-width"
          :data="bbsCategoryList"
          noDataText="データがありません。"
          @selected="onDetailCategoryShow"
          sst stripe>
          <template slot="thead">
            <vs-th class="tex-list-received pr-3">カテゴリ名</vs-th>
            <vs-th class="tex-list-received width-date">作成者</vs-th>
          </template> 

          <template slot-scope="{data}">
            <vs-tr :data="item" :key="index" v-for="(item, index) in data" >
              <vs-td class="tex-list-received pr-3">{{item.name?item.name:''}}</vs-td>
              <vs-td class="tex-list-received width-date">{{item.username?item.username:''}}</vs-td>
            </vs-tr>
          </template>
        </vs-table>
      </div>
      <div v-else>
        <div class="category_menubar">
          <vs-button @click="onEditCategoryAdd" v-if="boardPermission.category_append">カテゴリ追加</vs-button>
          <vs-spacer style="flex-grow: 1!important;"/>
          <category-menu @selectCategory="selectCategory" @changeDisp="changeDisp" ></category-menu>
        </div>
        <div style="display:flex;">
          <vs-spacer style="flex-grow: 1!important;"/>
          <div style="min-width:100px;padding-right:10px;margin-top:10px;">
          <span :style="howManyItemsStyle">
            {{pagination.from ? pagination.from :0}}-{{pagination.to ? pagination.to :0}}件目 / {{ pagination.totalItem ? pagination.totalItem :0}}件</span>
          </div>

          <div style="padding-right:10px;">
            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
          </div>
        </div>
        <vs-table class="mt-3 table-category-width"
          :data="bbsCategoryList"
          noDataText="データがありません。"
          @selected="onDetailCategoryShow"
          >
          <template slot-scope="{data}">
            <div v-for="(item, index) in data " :key="index">
              <vs-divider style="margin:2px;"></vs-divider>
              <vs-tr > 
                <vs-td class="tex-list-received pr-3" :style="isDispWide?'width:20%;':'width:35%;'">カテゴリ名</vs-td>
                <vs-td class="tex-list-received pr-3" :style="isDispWide?'width:80%;':'width:65%;'">{{item.name?item.name:''}}</vs-td>
              </vs-tr>
              <vs-tr> 
                <vs-td class="tex-list-received width-date" :style="isDispWide?'width:20%;':'width:35%;'">作成者</vs-td>
                <vs-td class="tex-list-received width-date" :style="isDispWide?'width:80%;':'width:65%;'">{{item.username?item.username:''}}</vs-td>
              </vs-tr>
            </div>
          </template>
        </vs-table>
      </div>
    </vs-card>
    <modal name="disp-category-modal"
      :pivot-y="0.2"
      :width="!isMobile?400:'90%'"
      :classes="['v--modal', 'add-update-modal']"
      :height="'auto'"
      :scrollable="true"
      :clickToClose="false">
      <header 
        class="vs_dialog_header_primary" 
        style="height: 56px;">
          <div class="v-toolbar__content">
            <h2 class="text-center">
              <span class="headline">カテゴリ詳細</span>
            </h2>
            <vs-spacer></vs-spacer>
            <button-icon icon-name="close" class="btnicon_c" color="primary" @clickButton="onDetailCategoryHide"></button-icon>    
          </div>
      </header>
      <vs-row style="padding:20px;">
        <vs-col cols="12" vs-w="3" vs-sm="12">
          <div class="clistDetail">カテゴリ名</div>
        </vs-col>
        <vs-col cols="12" vs-w="9" vs-sm="12">
          <div class="clistDetail">{{ category.name ? category.name : '' }}</div>
        </vs-col>

        <vs-col cols="12" vs-w="3" vs-sm="12">
          <div class="clistDetail">メモ</div>
        </vs-col>
        <vs-col cols="12" vs-w="9" vs-sm="12">
          <div
            class="clistDetail"
            style="white-space: pre-wrap;"
            v-html="category.memo ? category.memo : ''"
          ></div>
        </vs-col>

        <vs-col cols="12" vs-w="3" vs-sm="12">
          <div class="clistDetail">閲覧/返信</div>
        </vs-col>
        <vs-col cols="12" vs-w="9" vs-sm="12">
          <div class="clistDetail">{{ category.auth_content ? category.auth_content : '' }}</div>
        </vs-col>

        <vs-col cols="12" vs-w="3" vs-sm="12">
          <div class="clistDetail">作成者</div>
        </vs-col>
        <vs-col cols="12" vs-w="9" vs-sm="12">
          <div class="clistDetail">{{ category.username ? category.username : '' }}</div>
        </vs-col>

        <vs-col cols="12" vs-w="3" vs-sm="12">
          <div class="clistDetail">作成日</div>
        </vs-col>
        <vs-col cols="12" vs-w="9" vs-sm="12">
          <div class="clistDetail">{{category.created_at ? category.created_at : ''}}</div>
        </vs-col>

        <vs-col cols="12" vs-w="3" vs-sm="12">
          <div class="clistDetail">更新日</div>
        </vs-col>
        <vs-col cols="12" vs-w="9" vs-sm="12">
          <div class="clistDetail">{{category.updated_at ? category.updated_at : ''}}</div>
        </vs-col>
      </vs-row>
      <vs-row>
        <div style="flex-grow: 1!important;"></div>
        <div class="action-detail" style="padding:20px;">
          <vs-button v-if="this.category.isAuthEditAndDelete" color="primary" @click="onEditCategoryEdit">編集</vs-button>
          <vs-button v-if="this.category.isAuthEditAndDelete" class="square"  color="danger" @click="onDeleteCategory"><i class="far fa-trash-alt"></i> 削除</vs-button>
          <vs-button color="grey-light" class="nprimary"  @click="onDetailCategoryHide" style="color: inherit;">キャンセル</vs-button>
        </div>
      </vs-row>
    </modal>
    <modal name="edit-category-modal"
      :pivot-y="0.2"
      :width="!isMobile?800:'90%'"
      :classes="['v--modal', 'add-update-modal']"
      :height="'auto'"
      :scrollable="true"
      :clickToClose="false">
        <header 
          class="vs_dialog_header_primary" 
          style="height: 56px;">
            <div class="v-toolbar__content">
              <h2 class="text-center">
                <span class="headline">{{titleFix}}</span>
              </h2>
              <vs-spacer></vs-spacer>
              <button-icon icon-name="close" class="btnicon_c" color="primary" @clickButton="onEditCategoryHide"></button-icon>    
            </div>
        </header>

        <vs-row :style="isMobile?'padding: 0 24px 20px;':'padding: 0'">

            <vs-col cols="12" vs-w="4" vs-xs="12">
              <div class="clistDetailEdit ">
                カテゴリ名<span class="text-danger">*</span>
              </div>
            </vs-col>
            <vs-col cols="12" vs-w="8" vs-xs="12">
              <vs-input 
                v-model="editcategory.name"
                v-validate="{ required: true, max: 45 }"
                data-vv-as="カテゴリ名"
                name="name"
                placeholder="カテゴリ名を入力"
                  class="clistDetailEdit"
                >
              </vs-input>
            </vs-col>
            <vs-col cols="12" vs-w="4" vs-xs="12"></vs-col>
            <vs-col cols="12" vs-w="8" vs-xs="12">
              <div class="text-danger text-sm" style="padding-left:20px;" v-show="errors.has('name')">{{ errors.first('name') }}</div>
            </vs-col>          
            <vs-col cols="12" vs-w="4" vs-xs="12">
              <div class="clistDetailEdit" >メモ</div>
            </vs-col>
            <vs-col cols="12" vs-w="8" vs-xs="12">
              <vs-input
                v-model="editcategory.memo"
                v-validate="{  max: 65534 }"
                name="memo"
                outlined
                placeholder=""
                rows="1"
                auto-grow
                class="clistDetailEdit"

              ></vs-input>
            </vs-col>
            <vs-col cols="12" vs-w="4" vs-xs="12"></vs-col>
            <vs-col cols="12" vs-w="8" vs-xs="12">
              <div class="text-danger text-sm" style="padding-left:20px;" v-show="errors.has('memo')">{{ errors.first('memo') }}</div>
            </vs-col>          

            <vs-col cols="12" vs-w="4" vs-xs="12">
              <div class="clistDetailEdit" >
                トピックの閲覧/返信<span class="text-danger">*</span>
              </div>
            </vs-col>
            <vs-col cols="12" vs-w="8" vs-xs="12">
            <v-select
              class="dropdown clistDetailEdit"
              name="bbsauth"
              v-validate="{ required: true }"
              :value="selAuth"
              :options="bbsAuth"
              label="auth_content"
              dense
              no-data-text="データがありません。"
              placeholder="選択してください"
              @input="onChangeBbsAuth"></v-select>
            </vs-col>
                        <vs-col cols="12" vs-w="4"></vs-col>
            <vs-col cols="12" vs-w="8">
              <div class="text-danger text-sm" style="padding-left:20px;" v-show="errors.has('bbsauth')">{{ errors.first('bbsauth') }}</div>
            </vs-col>          

            <vs-col v-if="isDefineUsers" cols="12" vs-w="4">
              <div class="clistDetailEdit">
                所属メンバー一覧作成<span class="text-danger">*</span>
              </div>
            </vs-col>
            <vs-col v-if="isDefineUsers" cols="12" vs-w="8">
            <v-select
              class="dropdown clistDetailEdit"
              v-validate="{ required: true }"
              v-model="selMember"
              :options="bbsMember"
              name="bbsusers"
              label="username"
              dense
              multiple
              no-data-text="データがありません。"
              placeholder="選択してください"
              style="width: 100%; "
              @input="onClickUsers">
              </v-select>

            </vs-col>
            <vs-col v-if="isDefineUsers" cols="12" vs-w="4"></vs-col>
            <vs-col v-if="isDefineUsers" cols="12" vs-w="8">
              <div class="text-danger text-sm" style="padding-left:20px;" v-show="errors.has('bbsusers')">{{ errors.first('bbsusers') }}</div>
            </vs-col>          

        </vs-row>
        <vs-row style="margin-top:30px;">
          <div style="flex-grow: 1!important;"></div>
          <div  style="padding:20px;">
          <vs-button color="primary" @click="onSaveClick">{{btnFix}}</vs-button>
          <vs-button color="grey-light" class="nprimary"  @click="onEditCategoryHide" style="color: inherit;">キャンセル</vs-button>
          </div>
        </vs-row>
    </modal>

    <vs-popup title="確認" :active.sync="deleteDialog">
      <div class="mb-0">この掲示板カテゴリを削除してよろしいですか？<br />
        なお、カテゴリに含まれるトピックはすべて削除されます。</div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button @click="doDelete" color="warning">OK</vs-button>
        <vs-button @click="cancelDeleteModal" color="dark" type="border">キャンセル</vs-button>
      </vs-row>
    </vs-popup> 
    <vs-popup title="確認" :active.sync="accessErrorDialog">
      <div class="mb-0">アクセス権限がないため、{{ errorWords }}できません。</div>
      <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
        <vs-button class="square mr-2 " color="primary" type="filled" @click="cancelDeleteModal" >閉じる</vs-button>
      </vs-row>
    </vs-popup> 

  </div>
</template>
<script>
import { mapState, mapActions } from "vuex";
import { Validator } from 'vee-validate';
const dict = {
  custom: {
    name: {
      required: '* 必須項目です',
      max: '* 45文字以上は入力できません。',
    },
    memo: {
      max: '* 65535文字以上は入力できません。',
    } ,
    bbsauth: {
      required: '* 必須項目です',
    },                 
    bbsusers: {
      required: '* 必須項目です',
    }                  
  }
};
Validator.localize('ja', dict);
export default {
  components: { 
    VxPagination: () => import('@/components/vx-pagination/VxPagination.vue'),
    CategoryMenu: () => import('@/components/portal/bbs/CategoryMenu'),   
    ButtonIcon: () => import('@/components/portal/bbs/ButtonIcon'),
  },
  data() {
    return {
      deleteDialog: false,
      accessErrorDialog: false,
      bbsCategoryList:[],
      pagination: {
        totalPage: 0,
        currentPage: 1,
        limit: 10,
        totalItem: 0,
        from: 1,
        to: 10
      },
      category: {},
      editcategory: {
        name:'',
        memo:'',
        bbs_auth_id:'',
      },
      selected: {},
      addFlg:0,
      bbsAuth:[],
      bbsMember:[],
      isMobile:false,
      isDefineUsers:false,
      selAuth:'',
      selMember:[],
      isDispWide:false,
      loginUser: JSON.parse(getLS('user')),
      selAuthid: '',
    }
  },
  mounted() {
    this.isDispWide = this.$store.state.portal.isDispWide;

    this.getBbsCategoryList();
    this.getBbsAuthList();     
    this.getBbsMemberList();
  },
  computed: {
    /*PAC_5-2376 S*/
    ...mapState({
      boardPermission:state => state.groupware.boardPermission
    }),
    /*PAC_5-2376 E*/
    howManyItemsStyle() {
      if (this.breakpointWidth > 693) {
        return 'min-width: 120px;text-align: center;font-size: 1rem;'
      } else {
        return 'min-width: 105px;text-align: center;font-size: 0.9rem;'
      }
    },
    showCategoryDetail: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    },

    errorWords() {
      if (this.errorFlg) {
        return '追加'
      }
      return '編集または削除'
    },
    titleFix() {
      if (this.addFlg) return 'カテゴリ追加'
      return 'カテゴリ編集'
    },    
     btnFix() {
      if (this.addFlg) return '新規登録'
      return '更新'
    },
  },
  methods: {
    ...mapActions({
      getBbsCategories: "portal/getBbsCategories",
      getBbsAuth: "portal/getBbsAuth",
      getBbsMember: "portal/getBbsMember",
      deleteBbsCategory:"portal/deleteBbsCategory",
      updateBbsCategory:"portal/updateBbsCategory",
      addBbsCategory:"portal/addBbsCategory",
    }),
    getBbsCategoryList: async function () {
      let infoTopic  = {
        limit      : this.pagination.limit,
        page       : this.pagination.currentPage,
        type       : 1,
      };
      let data = await this.getBbsCategories(infoTopic);

      this.bbsCategoryList       = data.data;
      this.pagination.totalItem   = data.total;
      this.pagination.totalPage   = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.limit       = data.per_page;
      this.pagination.from        = data.from;
      this.pagination.to          = data.to;
    },
    onDetailCategoryShow: async function (data) {
     this.selected = Object.assign({}, data);
      let info  = {
        categoryId : this.selected.bbs_category_id,
        editflg:'1',
      };

      let categorydata = await this.getBbsCategories(info);
      this.category = categorydata.data;

      if (this.category) {

        let usersid = this.category.categoryUsers.split(',');
        this.selMember=[];
      
        for (let i = 0; i < usersid.length; i++) {
    
          let fildata = this.bbsMember.find(item => item.id == usersid[i]);

          if(Object.keys(fildata).length>0){
            this.selMember.push(fildata);
          }
        }
      
        this.selAuthid= this.category.bbs_auth_id;
        this.$modal.show('disp-category-modal');
      }
      
    },
    getBbsAuthList: async function () {
      let data = await this.getBbsAuth();
      this.bbsAuth       = data.data;
    },
    getBbsMemberList: async function () {
      let data = await this.getBbsMember();
      this.bbsMember       = data.data;
    },
    onDetailCategoryHide() {
      this.$modal.hide('disp-category-modal')
    },

    onEditCategoryAdd(){

      this.addFlg = 1;
      this.editcategory={};
      this.selAuth="";
      this.selAuthid = "";
      this.isDefineUsers= false;
      this.selMember=[];
      this.onEditCategoryShow();
    },
    onDeleteCategory(){
      this.deleteDialog=true;
    },
    onEditCategoryEdit(){

      this.addFlg = 0;
      this.editcategory=this.category;   
      let authdata = this.bbsAuth.find(item => item.id == this.editcategory.bbs_auth_id);
      if (Object.keys(authdata).length>0) {
        this.onChangeBbsAuth(authdata);
      }
      this.onEditCategoryShow();
    },
    onEditCategoryShow() {     
      if (this.category) this.$modal.show('edit-category-modal')
    },
    onEditCategoryHide() {
      this.$modal.hide('edit-category-modal')
    },
    getUserMembers(){
      let members = [];

      let catId = '';
      if (this.addFlg == false) catId = this.editcategory.bbs_category_id;
      if (this.editcategory.bbs_auth_id == 1){
        for(let idx =0; idx < this.bbsMember.length ; idx ++){
          let val = this.bbsMember[idx];   
          members.push({bbs_category_id: catId ,mst_user_id:val.id});
        } 
      }else if(this.editcategory.bbs_auth_id == 4){
        members.push({bbs_category_id: catId ,mst_user_id:this.loginUser.id});
      }else{
        let chkData = this.bbsMember.filter(val => val.checked=='1');
        if (typeof(chkData) != 'undefined'){

          for(let idx =0; idx < Object.keys(chkData).length ; idx ++){
            let val = chkData[idx];   
            members.push({bbs_category_id: catId ,mst_user_id:val.id});
          } 
        }
      }
      return members;
    },
    onSaveClick: async function() {
      const validate = await this.$validator.validateAll()
      if (!validate) return
      let members = this.getUserMembers();

      if(this.addFlg== true){
        let info={
          value:{
            name:this.editcategory.name,
            memo:this.editcategory.memo,
            bbs_auth_id:this.editcategory.bbs_auth_id,
          },
          valueuser: members
        }
        await this.addBbsCategory(info);
      }else{
        let info={
          id:this.editcategory.bbs_category_id,
          value:{
            name:this.editcategory.name,
            memo:this.editcategory.memo,
            bbs_auth_id:this.editcategory.bbs_auth_id,
          },
          valueuser: members
        }
        await this.updateBbsCategory(info);
      }

      this.onEditCategoryHide()
      this.onDetailCategoryHide()
      this.getBbsCategoryList()
      this.$emit('dispCategory')
    },
    doDelete: async function() {
      if (!this.category || !this.category.bbs_category_id) return

      let info={
        id:this.category.bbs_category_id
      }   
      await this.deleteBbsCategory(info)
      this.deleteDialog = false

      this.onDetailCategoryHide()
      this.getBbsCategoryList()
      this.$emit('dispCategory')
    },
    cancelDeleteModal() {
      this.deleteModal = false
    },
    cancelCategoryDetailDialog() {
      this.addFlg = false
      this.categoryDetailDialog = false
      this.category = Object.assign({}, this.categoryDefault)
    },  
    onChangeBbsAuth(val) {

      this.selAuth=val.auth_content;
      this.editcategory.bbs_auth_id = val.id;

      this.getIsDefineUsers();
    }, 
    getIsDefineUsers(){  

      if (this.editcategory.bbs_auth_id === 2 || this.editcategory.bbs_auth_id === 3) {
        this.isDefineUsers=true;
        if (this.selAuthid != this.editcategory.bbs_auth_id) {
          this.selMember=[];
          for(let idx =0; idx < this.bbsMember.length ; idx ++){
            let val = this.bbsMember[idx];   
            val.checked='0';
            if (this.loginUser.id == val.id) val.checked = '1';
          } 
          let fildata = this.bbsMember.find(item => item.id == this.loginUser.id);

          if(Object.keys(fildata).length>0){
            this.selMember.push(fildata);
          }       
        }
        this.selAuthid = this.editcategory.bbs_auth_id;
      }else{
        this.isDefineUsers=false;        
      }

    },
    onClickUsers(param){
      for(let idx =0; idx < this.bbsMember.length ; idx ++){
        let val = this.bbsMember[idx];   
        val.checked='0';
        let chkData = param.find(memb => memb.id == val.id);
        if (typeof(chkData) != 'undefined') val.checked='1';
      }
    
    },
    isDispChange(){
      this.isDispWide = this.$store.state.portal.isDispWide;
    },
    selectCategory: async function (val) {
      let infoDisp  = {
        category   : this.category,      
      }
      this.$emit('changeDisp', infoDisp)
    
    },
    changeDisp: async function (data) {
   
      let infoDisp  = {
        category   : this.category,      
      }
      this.$emit('changeDisp', infoDisp)
    
    },
  },
  watch: {
    'pagination.currentPage': function (val) {
      this.getBbsCategoryList();
    },
    'paginationSystem.currentPage': function (val) {
      this.getBbsCategoryList();
    },
    'pagination.limit': function (val) {
      this.getBbsCategoryList();
    },
  },
  created() {
    if (this.category) {

    }
    if (
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      )
    ) {
      this.isMobile = true
    }
    this.getIsDefineUsers();
  },
      
}
</script>

<style lang="scss">
.v-application--is-ltr .v-data-footer__select .v-select {
  margin: 5px 0 12px 5px !important;
}
.v-application--is-ltr .v-data-footer__select {
  margin-right: 0 !important;
}
.v-application--is-ltr .v-data-footer__pagination {
  margin: 0 12px 0 14px !important;
}
.v-application--is-ltr .v-data-footer__icons-before .v-btn:last-child {
  margin-right: 0 !important;
}
.v-application--is-ltr .v-data-footer__icons-after .v-btn:first-child {
  margin-left: 0 !important;
}
.v-select__selection--comma {
  margin: 0 2px !important;
  margin-bottom: 5px !important;
}
.v-input--hide-details > .v-input__control > .v-input__slot {
  height: 22px !important;
}
.v-text-field .v-input__prepend-inner,
.v-text-field .v-input__append-inner {
  margin-top: 5px !important;
}
.v-data-footer__select .v-select__selections .v-select__selection--comma {
  font-size: 1rem !important;
}
#categorylist .bm-pagination--ul {
  display: none;
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
</style>
