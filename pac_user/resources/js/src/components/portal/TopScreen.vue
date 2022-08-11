<template>
    <div class="comp-portal-announces" v-if="!isMobile">
        <div class="vx-col w-full mb-8 sm:mb-0 md:mb-0 lg:mb-0 lg:pr-0">
            <div class="vs-row notice pt-0 sm:pt-0 md:pt-0 lg:pt-0" vs-justify="left" v-if="urgentNotification && urgentNotification !== ''">
                <span class="text-danger">{{urgentNotification}}</span>
            </div>
            <vx-card class="mb-4">
                <vs-row class="pt-2 sm:pt-2 md:pt-2 lg:pt-2 resize-text">
                    <!--PAC_5-2977 S-->
                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" vs-w="1" v-if="!optionFlg" @click.native="$router.push('/received')">
                        <vs-row vs-justify="center" vs-align="center">
                            <img src="@assets/images/pages/home/received.svg" class="resize-image">
                        </vs-row>
                        <vs-row vs-justify="center" vs-align="center">
                            受信
                        </vs-row>
                        <vs-row vs-justify="center">
                            <span class="noti-big-size">{{num_text1}}</span>
                            <span class="noti-small-size">件</span>
                        </vs-row>
                    </vs-col>
                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" vs-w="1" v-if="!optionFlg" @click.native="$router.push('/sent')">
                        <vs-row vs-justify="center" vs-align="center">
                            <i class="fas fa-check" style="color: #107fcd;"></i>
                        </vs-row>
                        <vs-row vs-justify="center" vs-align="center">
                            申請
                        </vs-row>
                        <vs-row vs-justify="center">
                            <span class="noti-big-size">{{num_text2}}</span>
                            <span class="noti-small-size">件</span>
                        </vs-row>
                    </vs-col>
                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" vs-w="1" v-if="!optionFlg" @click.native="$router.push('/received')">
                        <vs-row vs-justify="center" vs-align="center">
                            <div class="resize-image"></div>
                        </vs-row>
                        <vs-row vs-justify="center" vs-align="center">
                            未処理
                        </vs-row>
                        <vs-row vs-justify="center">
                            <span class="noti-big-size">{{num_text3}}</span>
                            <span class="noti-small-size">件</span>
                        </vs-row>
                    </vs-col>
                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" vs-w="1" v-if="!optionFlg" @click.native="$router.push('/received')">
                        <vs-row vs-justify="center" vs-align="center">
                            <div class="resize-image"></div>
                        </vs-row>
                        <vs-row vs-justify="center" vs-align="center">
                            差戻
                        </vs-row>
                        <vs-row vs-justify="center">
                            <span class="noti-big-size">{{num_text4}}</span>
                            <span class="noti-small-size">件</span>
                        </vs-row>
                    </vs-col>
                    <vs-col class="cursor-pointer" vs-justify="center" vs-align="center" vs-w="1" v-if="!optionFlg && !formUserFlg" @click.native="$router.push('/saved')">
                        <vs-row vs-justify="center" vs-align="center">
                            <div class="resize-image"></div>
                        </vs-row>
                        <vs-row vs-justify="center" vs-align="center">
                            下書き
                        </vs-row>
                        <vs-row vs-justify="center">
                            <span class="noti-big-size">{{num_text5}}</span>
                            <span class="noti-small-size">件</span>
                        </vs-row>
                    </vs-col>
                    <vs-col vs-justify="center" vs-align="center" vs-w="1">
                    </vs-col>
                    <!--PAC_5-2977 E-->
                  
                    <vs-col vs-w="6" style="flex: 1;height: 58px;width: auto">
                        <div class="last-login-font-size" >
                            前回ログイン日時 {{lastLogin}}
                        </div>
                    </vs-col>
                </vs-row>
            </vx-card>
        </div>
    </div>
    
    <!-- Mobile -->
    <div class="comp-portal-announces top-screen-mobile" v-else>
      <!-- statistics -->
      <vx-card class="statistics">
        <div class="resize-text">
          <!--PAC_5-2977 S-->
          <div class="cursor-pointer" vs-justify="center" vs-align="center" v-if="!optionFlg" @click="$router.push('/received')">
              <vs-row vs-justify="center" vs-align="center">
                  <img src="@assets/images/pages/home/received.svg" class="resize-image">
              </vs-row>
              <vs-row vs-justify="center" vs-align="center">
                  受信
              </vs-row>
              <vs-row vs-justify="center">
                  <span class="noti-big-size">{{num_text1}}</span>
                  <span class="noti-small-size">件</span>
              </vs-row>
          </div>
          <div class="cursor-pointer" vs-justify="center" vs-align="center" v-if="!optionFlg" @click="$router.push('/sent')">
              <vs-row vs-justify="center" vs-align="center">
                  <i class="fas fa-check" style="color: #107fcd;"></i>
              </vs-row>
              <vs-row vs-justify="center" vs-align="center">
                  申請
              </vs-row>
              <vs-row vs-justify="center">
                  <span class="noti-big-size">{{num_text2}}</span>
                  <span class="noti-small-size">件</span>
              </vs-row>
          </div>
          <div class="cursor-pointer" vs-justify="center" vs-align="center" v-if="!optionFlg" @click="$router.push('/received')">
              <vs-row vs-justify="center" vs-align="center">
                  <div class="resize-image"></div>
              </vs-row>
              <vs-row vs-justify="center" vs-align="center">
                  未処理
              </vs-row>
              <vs-row vs-justify="center">
                  <span class="noti-big-size">{{num_text3}}</span>
                  <span class="noti-small-size">件</span>
              </vs-row>
          </div>
          <div class="cursor-pointer" vs-justify="center" vs-align="center" v-if="!optionFlg" @click="$router.push('/received')">
              <vs-row vs-justify="center" vs-align="center">
                  <div class="resize-image"></div>
              </vs-row>
              <vs-row vs-justify="center" vs-align="center">
                  差戻
              </vs-row>
              <vs-row vs-justify="center">
                  <span class="noti-big-size">{{num_text4}}</span>
                  <span class="noti-small-size">件</span>
              </vs-row>
          </div>
          <div class="cursor-pointer" vs-justify="center" vs-align="center" v-if="!optionFlg && !formUserFlg" @click="$router.push('/saved')">
              <vs-row vs-justify="center" vs-align="center">
                  <div class="resize-image"></div>
              </vs-row>
              <vs-row vs-justify="center" vs-align="center">
                  下書き
              </vs-row>
              <vs-row vs-justify="center">
                  <span class="noti-big-size">{{num_text5}}</span>
                  <span class="noti-small-size">件</span>
              </vs-row>
          </div>
        </div>
      </vx-card>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

export default {
    name: "TopScreen",
    props: {
      isMobile: Boolean,
    },
    async mounted() {
        var root = this;
        this.getCountCircularStatus().then(function (resp) {
            root.num_text1 = resp.num_untreated;
            root.num_text2 = resp.num_sent;
            root.num_text3 = Number(resp.num_unread) + Number(resp.num_read) + Number(resp.num_return) + Number(resp.num_unread_return);
            root.num_text4 = Number(resp.num_return) + Number(resp.num_unread_return);
            root.num_text5 = resp.num_saved;       
        });

        this.getLastLogin().then(function (res) {
            if (res && res.create_at) {
                let date = root.$moment(res.create_at, 'YYYY-MM-DD HH:mm:ss');
                root.lastLogin = date.format('YYYY/MM/DD HH:mm');
            } else {
                root.lastLogin = '';
            }
        });

        let infoUrgent  = {
            limit      : 1,
            type       : 9,
        };
        const data = await this.getListNotification(infoUrgent);
        let urgentNotice = data.notices.data;
        if (urgentNotice.length>0) {
            this.urgentNotification = urgentNotice[0].contents;
            this.$nextTick(()=> {
                this.$emit('dangerNoticeResize');
            })
        } else {
            this.urgentNotification = '';
        }
    },
    data() {
        return {
            num_text1: 0,
            num_text2: 0,
            num_text3: 0,
            num_text4: 0,
            num_text5: 0,
            lastLogin: '',
            urgentNotification: '',
            optionFlg : JSON.parse(getLS('user')).option_flg,
            formUserFlg: JSON.parse(getLS('user')).form_user_flg,
        };
    },
    methods: {
        ...mapActions({
            getCountCircularStatus: "circulars/getCountCircularStatus",
            getLastLogin: "logOperation/getLastLogin",
            getListNotification: "notice/getListNotification",
        }),
    },
    computed: {}
}
</script>

<style scoped>

</style>
