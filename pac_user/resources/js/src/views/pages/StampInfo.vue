<template>
    <div id="StampInfo" :class="stampInfoClass">
        <div class="vx-col flex items-center justify-center flex-col xs:w-full sm:w-full md:w-2/5 mx-auto text-center stamp_select">
            <vs-card>
                <div slot="header" class="bg-primary">
                    <vs-row>
                        <vs-col vs-type="flex" vs-w="4">
                            <img src="@assets/images/pages/logo.png" alt="Logo">
                        </vs-col>
                        <vs-col vs-type="flex" vs-w="1">
                        </vs-col>
                        <vs-col vs-type="flex" vs-w="7" class="items-center">
                            <h3>捺印プロパティ</h3>
                        </vs-col>
                    </vs-row>
                </div>
                <vs-row class="mt-4 mb-4">
                    <vs-col vs-w="4" style="text-align: center">
                        <div oncontextmenu="return false" style="padding:20px;border:solid 1px #e5e5e5" >
                        <img :src="'data:image/png;base64,' + stamp_info.stamp_image" alt="Logo" style="max-height: 100px; max-width: 100%; object-fit: cover; overflow-x: hidden" draggable="false" >
                        <p style="font-size: 12px">🄫Shachihata</p>
                          <p>&nbsp;</p>
                        <p style="font-size: 14px">印鑑シリアル</p>
                        <p style="font-weight: 500">{{stamp_info.serial}}</p>
                        </div>
                    </vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="pl-6 items-center">
                        <table>
                            <tr>
                                <td><strong>メールアドレス</strong></td>
                                <td>{{stamp_info.email}}</td>
                            </tr>
                            <tr>
                                <td><strong>捺印日時</strong></td>
                                <td>{{stamp_info.create_at}}</td>
                            </tr>
                            <tr>
                                <td><strong>ファイル名</strong></td>
                                <td>{{stamp_info.file_name}}</td>
                            </tr>
                            <tr v-if="stamp_info.time_stamp">
                                <td><strong>タイムスタンプ発行日時</strong></td>
                                <td>{{stamp_info.time_stamp}}</td>
                            </tr>
                        </table>
                    </vs-col>
                    <vs-col v-if="bizcard_info" vs-type="flex" vs-w="12" class="items-center">
                        <div style="width: 100%;">
                            <div class="bizcard_info_title">
                                名刺情報
                            </div>
                            <div>
                                <img :src="bizcard_info.bizcard" class="bizcard_image">
                            </div>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">名刺ID</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.bizcard_id}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">名前</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.name}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">会社名</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.company_name}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">電話番号</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.phone_number}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">住所</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.address}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">メールアドレス</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.email}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">部署</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.department}}</vs-col>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-w="3" class="item_title">役職</vs-col>
                                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                                <vs-col vs-type="flex" vs-w="8">{{bizcard_info.position}}</vs-col>
                            </vs-row>
                        </div>
                    </vs-col>
                    <vs-col vs-w="12" vs-type="flex" class="pt-3 pb-3 mt-5 bg-primary items-center justify-center" style="color: #fff">
                        この印影は、Shachihata Cloudで捺印されました。
                    </vs-col>
                </vs-row>
            </vs-card>
        </div>
    </div>
</template>
<script>
    import config from "../../app.config";
    import Axios from "axios";
    export default {
        data() {
            return {
                stamp_info: {},
                bizcard_info: null,
                base64_prefix: {
                    jpeg : "data:image/jpeg;base64,",
                    png : "data:image/png;base64,",
                    gif : "data:image/gif;base64,"
                },
                stampInfoClass: "h-screen flex w-full",
            }
        },
        methods: {
          loadInfo() {
            const info_id = this.$route.params.info_id;

            Axios.get(`${config.BASE_API_URL}/stamp_infos/${info_id}`, {data: {nowait: true}})
              .then(response => {
                this.stamp_info = response.data.data
                // 暫定対応として名刺情報は一括非表示
                // if (this.stamp_info.bizcard_id != null) {
                //     // 捺印履歴に名刺IDが紐づけられている場合は名刺情報を取得
                //     this.loadBizcardInfo(
                //         this.stamp_info.bizcard_id,
                //         this.stamp_info.env_flg,
                //         this.stamp_info.server_flg,
                //         this.stamp_info.edition_flg
                //     );
                // }
              })
              .catch(error => {
                this.$router.push('/pages/error-404');
              });
          },
          loadBizcardInfo(bizcardId, env_flg, server_flg, edition_flg) {
            Axios.get(`${config.BASE_API_URL}/bizcard/fromPDF/${bizcardId}?env_flg=${env_flg}&server_flg=${server_flg}&edition_flg=${edition_flg}`)
            .then(response => {
                this.bizcard_info = response.data.data.bizcard;
                if (this.bizcard_info != null) {
                    this.stampInfoClass += " card_margin";
                    switch (this.bizcard_info.bizcard.charAt(0)) {
                        case "/":
                            this.bizcard_info.bizcard = this.base64_prefix.jpeg + this.bizcard_info.bizcard;
                            break;
                        case "i":
                            this.bizcard_info.bizcard = this.base64_prefix.png + this.bizcard_info.bizcard;
                            break;
                        case "R":
                            this.bizcard_info.bizcard = this.base64_prefix.gif + this.bizcard_info.bizcard;
                            break;
                    }
                }
            }).catch(error => {
                console.error(error);
            })
          },
        },
        created() {
            this.loadInfo();
        }
    }
</script>
<style scoped>
.bizcard_info_title {
    background: #f2f2f2;
    border-left: 4px solid #8a8a8a;
    text-align: left;
    padding: 3px 10px;
    font-weight: 600;
    margin-top: 10px;
}
.item_title {
    padding-left: 20px;
}
.bizcard_image {
    width: 50%;
    vertical-align: bottom;
    margin-top: 10px;
}
.card_margin {
    margin-top: 15px;
}
.h-screen {
    min-height: 100vh;
    height: auto !important;
}
.stamp_select{
  -webkit-touch-callout: none;
  -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
  -khtml-user-select: none;
  user-select: none;
}
</style>