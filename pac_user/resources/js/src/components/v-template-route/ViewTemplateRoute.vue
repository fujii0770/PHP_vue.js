<template>
  <div>
  <vx-card>
    <div class="vx-col w-full mb-base m-auto">
      <vs-row class="mt-3">
        <vs-col vs-type="flex" vs-w="12" class="font-semibold">「{{templateData.file_name}}」設定ルート</vs-col>
      </vs-row>
      <vs-row class="mt-3">
        <vs-col vs-type="flex" vs-w="12">{{selectRouteData?selectRouteData.name:''}}</vs-col>
      </vs-row>
      <vs-row class="mt-3">
        <vs-col vs-w="8" v-html="selectRouteData?selectRouteData.dep_pos_name:''">{{selectRouteData?selectRouteData.dep_pos_name:''}}</vs-col>
        <vs-col vs-w="4" class="dep-mode" v-html="selectRouteData?selectRouteData.modes:''">{{selectRouteData?selectRouteData.modes:''}}=</vs-col>
      </vs-row>
    </div>
  </vx-card>
  <div class="vx-col w-full mb-base">
    <vs-card class="h-full">
      <vs-row style="align-items:baseline;margin-bottom: 10px">
        <span class="font-semibold">承認ルート名称：</span>
        <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="6">
          <vx-input-group class="w-full mb-0">
            <vs-input v-model="searchTemplateRouteText" />
            <template slot="append">
              <div class="append-text btn-addon">
                <vs-button color="primary" @click="onSearchTemplateRoute(true)"><i class="fas fa-search"></i></vs-button>
              </div>
            </template>
          </vx-input-group>
        </vs-col>
      </vs-row>
      <!-- 承認ルート一覧-->
      <div class="template_dialog">
        <vs-table class="mt-3" noDataText="データがありません。" :data="arrTemplateRoute" @sort="handleSort" stripe sst>
          <template slot="thead">
            <vs-th class="fl-area" sort-key="name">名称</vs-th>
            <vs-th class="fl-area">回覧先</vs-th>
            <vs-th class="fl-area">合議設定</vs-th>
            <vs-th class="fl-area">承認ルート</vs-th>
          </template>

          <template slot-scope="{data}">
            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
              <td style="width: 100px;">{{tr.name}}</td>
              <td v-html="tr.dep_pos_name">
                {{tr.dep_pos_name}}
              </td>
              <td v-html="tr.modes" style="width: 150px;">
                {{tr.modes}}
              <td style="width: 120px;"><vs-button class="square" color="primary" v-on:click="selectTemplateRoute(indextr,tr.id)" v-if="tr.id!=templateRouteId">
                設定
              </vs-button></td>
            </vs-tr>
          </template>
        </vs-table>
        <div>
          <div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示
          </div>
        </div>
        <vx-pagination v-if="pagination.totalItem" :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
      </div>
    </vs-card>
  </div>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import config from "../../app.config";
import Axios from "axios";
import InfiniteLoading from "vue-infinite-loading";
import flatPickr from "vue-flatpickr-component";

import VxPagination from '@/components/vx-pagination/VxPagination.vue';
export default {
  components: {
    VxPagination,
  },
  props: {
    routeId:{type: Number, default: null},
    opened:{type:Boolean,default:false},
    templateData:{type:Object,default:()=>{}},
    templateId:{type:Number,default:null},
  },
  data() {
    return {
      searchTemplateRouteText: '',
      arrTemplateRoute:[],
      template_modes: ['','全員必須','','人'], // 1:全員必須 3:人
      pagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
      orderBy: "",
      orderDir: "",
      selectRouteData:{},
      templateRouteId:null,
      selectedRouteId:0,
    }
  },
  watch: {
    'pagination.currentPage': function (val) {
      this.onSearchTemplateRoute(false);
    },
    opened: async function (val) {
      if (val) {
        await this.getOpenData();
      }
      this.searchTemplateRouteText = '';
    },
  },
  methods: {
    ...mapActions({
      getListRoute : "templateRoute/getTemplateRouteList",
      updateTemplateRoute: "template/updateTemplateRoute",
      searchTemplateRoute: "template/getTemplateRoute",
    }),
    async onSearchTemplateRoute(resetPaging){
      let info = {
        routeId:this.selectedRouteId ? this.selectedRouteId : 0,
        templateRouteName:this.searchTemplateRouteText,
        page: resetPaging ? 1 : this.pagination.currentPage,
        limit: this.pagination.limit,
        orderBy: this.orderBy,
        orderDir: this.orderDir,
      };

      const data = await this.getListRoute(info);
      if(this.selectedRouteId) {
        this.selectedRouteId = 0;
        this.selectRouteData = data[0];
        return true;
      }
      this.pagination.totalItem = data.total;
      this.pagination.totalPage = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.limit = data.per_page;
      this.pagination.from = data.from;
      this.pagination.to = data.to;
      this.arrTemplateRoute = data.data;
    },
    handleSort(key, active) {
      this.orderBy = key;
      this.orderDir = active ? "DESC" : "ASC";
      this.onSearchTemplateRoute(false);
    },
    async getOpenData(){
      await this.onSearchTemplateRoute();
      if(this.routeId){
        this.templateRouteId = this.routeId;
        await this.getTemplateRouteData();
      }
     
    },
    async getTemplateRouteData(){
      this.selectedRouteId = this.routeId;
      await this.onSearchTemplateRoute();
      
    },
    async selectTemplateRoute(index,template_route_id) {
      const data = {
        templateId: this.templateData.id,
        template_route_id: template_route_id,
      }
      await this.updateTemplateRoute(data);
      this.selectRouteData = this.arrTemplateRoute[index];
      await this.onSearchTemplateRoute(false);
      this.templateRouteId = template_route_id;
    },
  },
  created() {
    if(this.opened) {
      this.getOpenData();
    }
  }
}
</script>

<style>
.dep-pos-label {
  display: flex;
  flex-wrap: nowrap;
  align-items: flex-start;
}
.dep-pos-label > label {
  width: 80%;
  margin-bottom: 0;
}
.dep-pos-label > span {
  width: 100px;
  margin-left: 20px;
}
.dep-mode{
  padding-left: 10px;
  border-left: 1px solid #e1d7d7;
}
.fl-area .vs-table-text {
  justify-content: center;
}
</style>