<template>
  <vs-card class="receive-plan-content">
    <vs-row>
      <div class="title">
        契約情報
      </div>
    </vs-row>
    <vs-row style="margin-top: 20px; border: 1px solid rgb(232,232,232)">
      <vs-col vs-w="2" vs-type="flex" vs-justify="flex-start" vs-align="center"
              style="font-size:1.1rem;background: rgb(244,244,244);color: #000;min-height: 100px; padding-left: 20px">
        ご紹介リンク
      </vs-col>
      <vs-col vs-w="10" vs-type="flex" vs-justify="flex-start" vs-align="center"
              style="font-size:1rem;color: #000;min-height: 100px;padding-left: 20px; line-height: 1.2">
        <a style="color: #000;word-break: break-all" v-if="receivePlanUrl" :href="receivePlanUrl" target="_blank">{{receivePlanUrl}}</a>

      </vs-col>
    </vs-row>

  </vs-card>
</template>
<script>
import config from "../../app.config";
import GroupwarePage from "./GroupwarePage"
import TopMenu from "./TopMenu";
import Axios from "axios";

export default {
  components: {
    TopMenu,
    GroupwarePage
  },
  data() {
    return {
      receivePlanUrl: "" ,
    }
  },
  methods: {
    async getUrl() {
       let receive_plan_flg = this.$store.state.groupware.myCompany && this.$store.state.groupware.myCompany.receive_plan_flg ? this.$store.state.groupware.myCompany.receive_plan_flg :0
       let limit = JSON.parse(getLS('limit'))
       let limit_receive_plan_flg = limit?limit.limit_receive_plan_flg:0;
      if (receive_plan_flg && limit_receive_plan_flg){
        Axios.get(`${config.BASE_API_URL}/receive_plan/get_url`).then(res=>{
          this.receivePlanUrl = res.data.data || ""
        }).catch(error => {
          error = error.response;
          const message = (error && error.data && error.data.message) || error.statusText;
          this.$store.dispatch("alertError", message, { root: true });
          //return Promise.reject(message);
        });
      }else{
        window.location.href = '/app/pages/error-404';
      }
    }
  },
  mounted() {
    this.getUrl()
  }
}

</script>
<style lang="scss">
.receive-plan-content {
  height: calc(100vh - 80px);

  .title {
    position: relative;
    padding-left: 20px;
    font-size: 1.3rem;
    color: #000;

    &:before {
      content: "";
      position: absolute;
      width: 12px;
      height: 12px;
      top: 50%;
      margin-top: -6px;
      background: rgb(180, 180, 180);
      left: 0;
      border-radius: 50%;
    }
  }
}
</style>