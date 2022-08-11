<template>

  <div>
    <div id="saves-list-page" :class="isMobile?'mobile':''">
        <vs-card style="margin-bottom: 0">
            <vs-row>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                    <vs-input class="inputx w-full" label="文書ID" v-model="filter.id"/>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                    <vs-input class="inputx w-full" label="文書名" v-model="filter.name"/>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 lg:pl-2">
                    <div class="w-full">
                        <label for="filter_fromdate" class="vs-input--label">更新日時From</label>
                        <div class="vs-con-input">
                            <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" :config="configDate"></flat-pickr>
                        </div>
                    </div>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                    <div class="w-full">
                        <label for="filter_todate" class="vs-input--label">更新日時To</label>
                        <div class="vs-con-input">
                            <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" :config="configDate"></flat-pickr>
                        </div>
                    </div>
                </vs-col>
            </vs-row>
            <vs-row class="mt-3">
                 <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                </vs-col>
            </vs-row>
        </vs-card>

        <vs-card>
            <!--<vs-button class="square" color="primary"
                v-bind:disabled="selected.length == 0"><i class="fas fa-play"></i> 再開</vs-button>-->
            <vs-button class="square"  color="danger" v-on:click="confirmDelete=true"
                v-bind:disabled="selected.length == 0"  ><i class="far fa-trash-alt"></i> 削除</vs-button>

             <vs-table class="mt-3" noDataText="データがありません。" :data="listSave" @sort="handleSort" stripe sst>
                <template slot="thead">
                    <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                    <vs-th sort-key="C.id" class="min-width-100">文書ID</vs-th>
                    <vs-th sort-key="file_names">文書名</vs-th>
                    <vs-th sort-key="C.final_updated_date">更新日時</vs-th>
                </template>

                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
                        <vs-td><vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/></vs-td>
                        <td @click="onRowSelect(tr)"> {{tr.id}}</td>
                        <td @click="onRowSelect(tr)" class="max-width-200">{{tr.file_names}}</td>
                        <td @click="onRowSelect(tr)">{{tr.final_updated_date | moment("YYYY/MM/DD HH:mm")}}</td>
                    </vs-tr>
                </template>
            </vs-table>
            <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
        </vs-card>

        <vs-popup classContent="popup-example"  title="保存文書の削除" :active.sync="confirmDelete">
            <div v-if="selected.length>1">{{ selected.length }}件の保存文書を削除します。</div>
            <div v-if="selected.length==1">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].title }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].final_updated_date | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この保存文書を削除します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="保存文書の再開" :active.sync="confirmEdit">
            <div v-if="saveItem">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ saveItem.title }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ saveItem.file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ saveItem.final_updated_date | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この保存文書から再開します</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onEdit" color="success">再開</vs-button>
                    <vs-button @click="confirmEdit=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

    </div>



    <!-- Mobile -->
    <div id="saves-list-page-mobile" :class="isMobile?'mobile':''">

        <h3 class="saves-list-page-mobile-title">下書き一覧</h3>

        <vs-card style="margin-bottom: 0">

            <vs-row>
                <div style="width:100%" @click="searchAreaFlg=!searchAreaFlg">
                    <vs-col vs-type="flex" vs-xs="12" class="pr-2">
                        <span class="sends-mobile-select-panel">
                              <p style="margin-top: 2px;"><font size="4">絞り込み検索</font></p>
                        </span>
                            <span class="saves-list-page-mobile-button">
                            <vs-icon id="arrow_mobile" class="around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                        </span>
                    </vs-col>
                </div>
            </vs-row>

            <vs-row v-show="searchAreaFlg">
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                    <vs-input class="inputx w-full" label="文書ID" v-model="filter.id"/>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                    <vs-input class="inputx w-full" label="文書名" v-model="filter.name"/>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 lg:pl-2">
                    <div class="w-full">
                        <label for="filter_fromdate" class="vs-input--label">更新日時From</label>
                        <div class="vs-con-input">
                            <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" placeholder="年/月/日" :config="configDate"></flat-pickr>
                        </div>
                    </div>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                    <div class="w-full">
                        <label for="filter_todate" class="vs-input--label">更新日時To</label>
                        <div class="vs-con-input">
                            <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" placeholder="年/月/日" :config="configDate"></flat-pickr>
                        </div>
                    </div>
                </vs-col>
            </vs-row>
            <vs-row class="mt-3 saves-list-page-search" v-show="searchAreaFlg">
                 <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button class="square" color="primary" v-on:click="onSearch(true)">検索する</vs-button>
                </vs-col>
            </vs-row>
        </vs-card>

        <vs-card>
            <!--<vs-button class="square" color="primary"
                v-bind:disabled="selected.length == 0"><i class="fas fa-play"></i> 再開</vs-button>
            <vs-button class="square"  color="danger" v-on:click="confirmDelete=true"
                v-bind:disabled="selected.length == 0"  ><i class="far fa-trash-alt"></i> 削除</vs-button>-->

             <vs-table class="mt-3" noDataText="データがありません。" :data="listSave" @sort="handleSort" stripe sst>


                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
                        <td @click="onRowSelect(tr)">
                          <div class="file_name">
                            {{tr.file_names}}
                          </div>
                        </td>
                        <td @click="onRowSelect(tr)">{{tr.final_updated_date | moment("MM/DD HH:mm")}}</td>
                    </vs-tr>
                </template>


            </vs-table>
            <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
            <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
        </vs-card>

        <vs-popup classContent="popup-example"  title="保存文書の削除" :active.sync="confirmDelete">
            <div v-if="selected.length>1">{{ selected.length }}件の保存文書を削除します。</div>
            <div v-if="selected.length==1">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].title }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].final_updated_date | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この保存文書を削除します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="保存文書の再開" :active.sync="confirmEdit">
            <div v-if="saveItem">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ saveItem.title }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ saveItem.file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ saveItem.final_updated_date | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この保存文書から再開します</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onEdit" color="success">再開</vs-button>
                    <vs-button @click="confirmEdit=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

    </div>

  </div>

</template>


<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from 'vue-infinite-loading';

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';

import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination,
    },
    data() {
        return {
            filter: {
                id: "",
                name: "",
                fromdate: "",
                todate: "",
            },
            selectAll: false,
            listSave:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "C.final_updated_date",
            orderDir: "desc",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                disableMobile: true
            },
            confirmDelete: false,
            confirmEdit: false,
            saveItem: null,
            isMobile: false,
            searchAreaFlg: false
        }
    },
    computed: {
      selected() {
        return this.listSave.filter(item => item.selected);
      }
    },
    methods: {
        ...mapActions({
            search: "circulars/getListSave",
            postActionMultiple: "circulars/postActionMultiple",
            clearHomeState: "home/clearState",
            addLogOperation: "logOperation/addLog",
        }),
        onSearch: async function (resetPaging) {
            this.selectAll = false;
            let info = { id         : this.filter.id,
                         filename   : this.filter.name,  
                         fromdate   : this.filter.fromdate,  
                         todate     : this.filter.todate,  
                         page       : resetPaging?1:this.pagination.currentPage,
                         limit      : this.pagination.limit,
                         orderBy    : this.orderBy,
                         orderDir   : this.orderDir,
                        };
            var data = await this.search(info);
            this.listSave               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
            
            if(this.isMobile){
                this.searchAreaFlg = false;
            }
        },
        onSelectAll() {
            this.selectAll = !this.selectAll;
			this.listSave.map(item=> {item.selected = this.selectAll; return item});
        },
        onDelete: async function () {
           await this.postActionMultiple({action: 'deleteSaved', info: { cids: this.getSelectedID() }});           
           this.confirmDelete = false;
           this.onSearch(false);
        },
        getSelectedID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        onRowSelect(tr) {
          this.confirmEdit = true;
          this.saveItem = tr;
        },
        onEdit: function () {
          this.$store.commit('home/checkCircularUserNextSend', false);
          if(!this.saveItem) return;
          if(this.$store.state.home.circular && this.saveItem.id != this.$store.state.home.circular.id){
              this.$store.commit('application/updateCommentTitle', '');
              this.$store.commit('application/updateCommentContent', '');
              this.$store.commit('application/updateListUserView', []);
          }
          this.clearHomeState();
          this.confirmEdit = false;
          setTimeout(()=> {
            this.addLogOperation({ action: 'r04-resume', result: 0, params:{circular_id: this.saveItem.id}});

            if( this.isMobile ) {
              this.$router.push('/create/'+this.saveItem.id);
            } else {
              this.$router.push('/saves/'+this.saveItem.id);
            }
          },300)
        },
        onRowCheckboxClick: function (tr) {
          tr.selected = !tr.selected
          this.selectAll = this.listSave.every(item => item.selected);
        },
    },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        searchAreaFlg:function (val){
            let obj = document.getElementById("arrow_mobile");
            if(val){
                obj.classList.add("around");
                obj.classList.remove("around_return");
            }else{
                obj.classList.add("around_return");
                obj.classList.remove("around");
            }
        },
        /*"listSave": {
          handler: function (val) {
            this.selectAll = this.selected.length === val.length;
            this.selectAllFlg = this.selected.length === val.length;
          },
          deep: true
        }*/
    },
    mounted() {
        this.onSearch(false);
    },
    created() {

        // Check Mobile
        if (
          /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
            navigator.userAgent
          )
        ) {
          this.isMobile = true
        }

        this.$nextTick(()=>{
            let popups = document.getElementsByClassName('vs-component con-vs-popup vs-popup-primary');
            for (let i = 0;i < popups.length;i ++){
                let div = document.createElement('div');
                div.style.width = '100%';
                div.style.height = '100%';
                div.style.position = 'fixed';
                div.style.left = 0;
                div.style.top = 0;
                div.style.zIndex = 50;
                //div.setAttribute('style','z-index:50');
                popups[i].appendChild(div);
            }
        });
        this.addLogOperation({ action: 'saved-display', result: 0});

    }
}

</script>


<style lang="scss">

#saves-list-page.mobile, #saves-list-page-mobile{
  display: none;
}

#saves-list-page-mobile.mobile{
  display: block;
  padding: 0 1.2rem;
  margin-top: -50px;

  .saves-list-page-mobile-title{
    text-align: center;
    margin-bottom: 10px;
  }

  .saves-list-page-mobile-button{
    margin-left: auto;
  }

  .saves-list-page-search{
    > .vs-col{
      display: inline-block !important;

      button{
        width: 100%;
        margin: 0 auto;
      }
    }
  }

  table{
    min-width: auto;
    max-width: 100%;
    display: block;

    tr{
      display: block;

      td{
        &:first-child{
          display: inline-block;
          width: calc( 100% - 115px );
        }
        &:last-child{
          display: inline-block;
          min-width: 110px;
        }

        .file_name{
          overflow: hidden;
          text-overflow: ellipsis;
        }
      }
    }
  }
}

@media( min-width: 601px ) {
  
  #saves-list-page-mobile.mobile{
    margin-top: 0;

    .saves-list-page-search{
      > .vs-col {
        text-align: center;

        button{
          width: auto;
        }
      }
    }
  }

}


@media( max-width: 600px ) {
  #saves-list-page-mobile.mobile{
    top: -45px;
  }
}

@media( max-width: 240px ) {
    #saves-list-page-mobile.mobile{
      .con-vs-card{
        margin-bottom: 15px !important;
      }
      .vs-card--content{
        margin-bottom: 0;
      }

      table{
        td{
          padding: 10px 0;

          width: 50px !important;
          min-width: auto !important;

          &:first-child{
            width: calc( 100% - 70px ) !important;
          }


          >div{
            width: 90%;
          }
        }
      }

      .bm-pagination{
        display: none;
      }
    }
  }

</style>
