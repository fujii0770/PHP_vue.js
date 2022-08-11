<template>
    <div>
        <vs-row>
            <vs-col vs-w="3" :class="largeMidiumDisplayClass"></vs-col>
            <vs-col vs-lg="6" vs-sm="6" vs-xs="12">
                <vs-card>
                    <div v-if="bizcard" style="font-size: 16px;">
                        <vs-row class="mt-3">
                            <vs-col vs-w="3" vs-sm="2" vs-xs="3"></vs-col>
                            <vs-col vs-type="flex" vs-lg="6" vs-sm="8" vs-xs="6" style="align-items: flex-start;">
                                <img :src="bizcard.bizcard" class="bizcard_image">
                            </vs-col>
                            <vs-col vs-lg="3" vs-sm="2" vs-xs="3"></vs-col>
                        </vs-row>
                        <vs-row class="mt-3" :class="mediumSmallDisplayClass">
                            <vs-col vs-lg="2" vs-sm="1" vs-xs="1"></vs-col>
                            <vs-col vs-type="flex" vs-lg="8" vs-sm="10" vs-xs="10">
                                <vs-list style="overflow: auto;">
                                    <vs-list-item title="名前">{{ bizcard.name }}</vs-list-item>
                                    <vs-list-item title="会社名">{{ bizcard.company_name }}</vs-list-item>
                                    <vs-list-item title="電話番号">{{ bizcard.phone_number }}</vs-list-item>
                                    <vs-list-item title="住所">{{ bizcard.address }}</vs-list-item>
                                    <vs-list-item title="メールアドレス">{{ bizcard.email }}</vs-list-item>
                                    <vs-list-item title="部署">{{ bizcard.department }}</vs-list-item>
                                    <vs-list-item title="役職">{{ bizcard.position }}</vs-list-item>
                                </vs-list>
                            </vs-col>
                            <vs-col vs-lg="2" vs-sm="1" vs-xs="1"></vs-col>
                        </vs-row>
                        <vs-row class="mt-3" :class="largeDisplayClass">
                            <vs-col vs-w="1"></vs-col>
                            <vs-col vs-type="flex" vs-w="10" vs-justify="center">
                                <vs-table :data="bizcard" style="overflow: auto; font-size: 16px;">
                                    <template slot-scope="{data}">
                                        <vs-tr>
                                            <vs-td class="item_name">名前</vs-td>
                                            <vs-td>{{ data.name }}</vs-td>
                                        </vs-tr>
                                        <vs-tr>
                                            <vs-td class="item_name">会社名</vs-td>
                                            <vs-td>{{ data.company_name }}</vs-td>
                                        </vs-tr>
                                        <vs-tr>
                                            <vs-td class="item_name">電話番号</vs-td>
                                            <vs-td>{{ data.phone_number }}</vs-td>
                                        </vs-tr>
                                        <vs-tr>
                                            <vs-td class="item_name">住所</vs-td>
                                            <vs-td>{{ data.address }}</vs-td>
                                        </vs-tr>
                                        <vs-tr>
                                            <vs-td class="item_name">メールアドレス</vs-td>
                                            <vs-td>{{ data.email }}</vs-td>
                                        </vs-tr>
                                        <vs-tr>
                                            <vs-td class="item_name">部署</vs-td>
                                            <vs-td>{{ data.department }}</vs-td>
                                        </vs-tr>
                                        <vs-tr>
                                            <vs-td class="item_name">役職</vs-td>
                                            <vs-td>{{ data.position }}</vs-td>
                                        </vs-tr>
                                    </template>
                                </vs-table>
                            </vs-col>
                            <vs-col vs-w="1"></vs-col>
                        </vs-row>
                    </div>
                    <div v-else class="no_bizcard_text">
                        名刺がありません。
                    </div>
                </vs-card>
            </vs-col>
            <vs-col vs-w="3" :class="largeMidiumDisplayClass"></vs-col>
        </vs-row>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

export default {
    data() {
        return {
            base64_prefix: {
                jpeg : "data:image/jpeg;base64,",
                png : "data:image/png;base64,",
                gif : "data:image/gif;base64,"
            },
            bizcard: null,
            largeDisplayClass: "",
            largeMidiumDisplayClass: "",
            mediumSmallDisplayClass: "",
        }
    },
    created: function() {
        window.addEventListener('resize', this.detectWindowSize, false);
    },
    mounted: function() {
        this.getBizcardInfo();
        this.detectWindowSize();
    },
    methods: {
        ...mapActions({
            getByLinkPageURL: "bizcard/getByLinkPageURL",
        }),
        getBizcardInfo: async function() {
            // ページのURLから名刺情報を取得
            let info  = {
                link_page_url : location.href,
            };
            let data = await this.getByLinkPageURL(info);
            
            if(data.bizcard != null) {
                this.bizcard = data.bizcard;

                switch (this.bizcard.bizcard.charAt(0)) {
                    case "/":
                        this.bizcard.bizcard = this.base64_prefix.jpeg + this.bizcard.bizcard;
                        break;
                    case "i":
                        this.bizcard.bizcard = this.base64_prefix.png + this.bizcard.bizcard;
                        break;
                    case "R":
                        this.bizcard.bizcard = this.base64_prefix.gif + this.bizcard.bizcard;
                        break;
                }
            }
        },
        detectWindowSize: function() {
            // ウインドウ幅に合わせた表示に変更する
            if (window.innerWidth <= 600) {
                // ウインドウ幅：小
                this.largeDisplayClass = "hidden";
                this.largeMidiumDisplayClass = "hidden";
                this.mediumSmallDisplayClass = "";
            } else if (window.innerWidth > 900) {
                // ウインドウ幅：大
                this.largeDisplayClass = "";
                this.largeMidiumDisplayClass = "";
                this.mediumSmallDisplayClass = "hidden";
            } else {
                // ウインドウ幅：中
                this.largeDisplayClass = "hidden";
                this.largeMidiumDisplayClass = "";
                this.mediumSmallDisplayClass = "";
            }
        }
    },
    destroyed: function() {
        window.removeEventListener('resize', this.detectWindowSize);
    },
}
</script>

<style lang="stylus" scoped>
.bizcard_image {
    width: 100%;
}
.hidden {
    display: none;
}
.no_bizcard_text {
    display: flex;
    justify-content: center;
    font-size: 16px;
}
.item_name {
    background-color: #b3e5fb;
}
.vs-table--td {
    font-size: 16px;
}
</style>