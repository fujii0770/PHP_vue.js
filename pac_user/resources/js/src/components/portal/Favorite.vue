<template>
    <div class="comp-portal-favorite">
      <vx-card class="mb-4">
        <HeaderComponent title="お気に入り" class="whitespace-no-wrap" @hiddenAppPortal="$emit('hiddenAppPortal')"></HeaderComponent>
        <div v-if="issetFavoriteInternal">
            <vs-row>
                <div style="width:100%">
                    <vs-tabs class="tab-parent comment-height" v-model="tab_shachihata">
                        <vs-tab label="お気に入り" style="padding: 0; overflow-y: auto; height: 417px;">
                             <vs-row class="item-favorite item-favorite-internal mouse-hover" v-for="(favoriteInternal) in listFavoriteInternal" :key="favoriteInternal.id">
                                <div @click="tab_shachihata = 1" style="display: flex;  width: 100%;">
                                    <vs-col vs-w="4"></vs-col>
                                    <vs-col class="service-logo" style="max-width: 100%;" >
                                        <img class="logo-xternal" v-if="listService && listService.length > 0" :src="'data:image/jpeg;base64,' + listService[0].logo_src" alt="Logo service">
                                    </vs-col>
                                    <vs-col vs-w="4"></vs-col>
                                </div>
                                <vs-col class="delete-service" vs-w="1">
                                    <span @click="confirmDeleteFavorite(favoriteInternal.id)"><i class="fas fa-trash-alt" style="color:white"></i></span>
                                </vs-col>
                            </vs-row>

                            <vs-row class="item-favorite item-favorite-external mouse-hover" v-for="(favoriteExternal) in listFavoriteExternal" :key="favoriteExternal.id">
                                <div @click="onClickFavorite(favoriteExternal.url)" style="display: flex;  width: 100%;">
                                    <vs-col vs-w="4"></vs-col>
                                    <vs-col class="service-logo service-name" vs-type="flex" vs-align="left" style="max-width: 100%;">
                                        <img v-if="favoriteExternal.logo_src === ''" class="logo-internal" :src="require('@assets/images/pages/portal/default-logo.svg')" alt="Logo service">
                                        <img v-else class="logo-internal" :src="'data:image/jpeg;base64,' + favoriteExternal.logo_src" alt="Logo service">
                                        <span class="pl-4 pt-4"><h4 style="font-weight: 680">{{favoriteExternal.service_name}}</h4></span>
                                    </vs-col>
                                    <vs-col vs-w="4"></vs-col>
                                </div>
                                <vs-col class="delete-service" vs-w="1">
                                    <span @click="confirmDeleteFavorite(favoriteExternal.id)"><i class="fas fa-trash-alt" style="color:white"></i></span>
                                </vs-col>
                            </vs-row>

                            <vs-row class="item-favorite item-favorite-internal mouse-hover">
                                <div @click="onClickFavorite3()" style="display: flex;  width: 100%;">
                                    <vs-col vs-w="4"></vs-col>
                                    <vs-col class="service-logo" vs-type="flex" vs-align="left" style="max-width: 100%;" >
                                        <img class="logo-xternal" src="@assets/images/logo/logo_blue.png" alt="Logo service">
                                        <span class="pl-4 pt-4"><h4 style="font-weight: 680">管理者向けログインはこちら</h4></span>
                                    </vs-col>
                                    <vs-col vs-w="4"></vs-col>
                                </div>
                            </vs-row>

                            <vs-row class="item-favorite item-favorite-internal mouse-hover">
                                <div @click="onClickFavorite4()" style="display: flex;  width: 100%;">
                                    <vs-col vs-w="4"></vs-col>
                                    <vs-col class="service-logo" vs-type="flex" vs-align="left" style="max-width: 100%;" >
                                        <img class="logo-xternal" src="@assets/images/logo/logo_blue.png" alt="Logo service">
                                        <span class="pl-4 pt-4"><h4 style="font-weight: 680">利用者向けログインはこちら</h4></span>
                                    </vs-col>
                                    <vs-col vs-w="4"></vs-col>
                                </div>
                            </vs-row>

                            <vs-row class="item-favorite item-favorite-internal mouse-hover">
                                <div @click="onClickFavorite5()" style="display: flex;  width: 100%;">
                                    <vs-col vs-w="4"></vs-col>
                                    <vs-col class="service-logo" vs-type="flex" vs-align="left" style="max-width: 100%;" >
                                        <img class="logo-xternal" src="@assets/images/logo/logo_blue.png" alt="Logo service">
                                        <span class="pl-4 pt-4"><h4 style="font-weight: 680">ヘルプサイトはこちら</h4></span>
                                    </vs-col>
                                    <vs-col vs-w="4"></vs-col>
                                </div>
                            </vs-row>

                            <vs-row v-show ="countFavorite < 15" class="form-add-favorite">
                                <span @click="onAddFavorite()"><i class="fas fa-plus-circle" style="font-size: 40px;"></i></span>
                            </vs-row>
                        </vs-tab>
                        <vs-tab label="シヤチハタ" style="padding: 0;">
                            <circular-component :isShachihataImage="isShachihataImage"
                                                :logoSrcFavoriteInternal="logoSrcDefault"
                                                :issetFavoriteInternal="issetFavoriteInternal"
                                                :onSearchAfterSaveFavorite="onSearchAfterSaveFavorite"></circular-component>
                        </vs-tab>
                    </vs-tabs>
                </div>
            </vs-row>
        </div>
        <div v-else>
            <div class="list-favorite" style="overflow-y: auto; height: 417px;">
                <vs-row class="item-favorite item-favorite-external mouse-hover" v-for="(favoriteExternal) in listFavoriteExternal" :key="favoriteExternal.id">
                    <div @click="onClickFavorite(favoriteExternal.url)" style="display: flex;  width: 100%;">
                        <vs-col vs-w="3" class="service-logo">
                            <img v-if="favoriteExternal.logo_src === ''" class="logo-internal" :src="require('@assets/images/pages/portal/default-logo.svg')" alt="Logo service">
                            <img v-else class="logo-internal" :src="'data:image/jpeg;base64,' + favoriteExternal.logo_src" alt="Logo service">
                        </vs-col>
                        <vs-col vs-w="8" class="service-name" vs-type="flex" vs-align="center">
                            <span>{{favoriteExternal.service_name}}</span>
                        </vs-col>
                    </div>
                    <vs-col class="delete-service" vs-w="1">
                        <span @click="confirmDeleteFavorite(favoriteExternal.id)"><i class="fas fa-trash-alt" style="color:white"></i></span>
                    </vs-col>
                </vs-row>

                <vs-row class="item-favorite item-favorite-internal mouse-hover">
                    <div @click="onClickFavorite3()" style="display: flex;  width: 100%;">
                        <vs-col vs-w="3" class="service-logo">
                            <img class="logo-internal" src="@assets/images/logo/logo_blue.png" alt="Logo service">
                        </vs-col>
                        <vs-col vs-w="8" class="service-name" vs-type="flex" vs-align="center">
                            <span>管理者向けログインはこちら</span>
                        </vs-col>
                    </div>
                </vs-row>
 
                <vs-row class="item-favorite item-favorite-internal mouse-hover">
                    <div @click="onClickFavorite4()" style="display: flex;  width: 100%;">
                        <vs-col vs-w="3" class="service-logo">
                            <img class="logo-internal" src="@assets/images/logo/logo_blue.png" alt="Logo service">
                        </vs-col>
                        <vs-col vs-w="8" class="service-name" vs-type="flex" vs-align="center">
                            <span>利用者向けログインはこちら</span>
                        </vs-col>
                    </div>
                </vs-row>

                <vs-row class="item-favorite item-favorite-internal mouse-hover">
                    <div @click="onClickFavorite5()" style="display: flex;  width: 100%;">
                        <vs-col vs-w="3" class="service-logo">
                            <img class="logo-internal" src="@assets/images/logo/logo_blue.png" alt="Logo service">
                        </vs-col>
                        <vs-col vs-w="8" class="service-name" vs-type="flex" vs-align="center">
                            <span>ヘルプサイトはこちら</span>
                        </vs-col>
                    </div>
                </vs-row>

                <vs-row class="form-add-favorite" v-show ="countFavorite < 15">
                    <span @click="onAddFavorite()"><i class="fas fa-plus-circle" style="font-size: 40px;"></i></span>
                </vs-row>
            </div>
        </div>

        <modal name="add-favorite-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'add-favorite-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row class="border-bottom pb-4 mb-2">
                <h4>お気に入り</h4>
            </vs-row>
            <!-- <vs-row class="mp-3" vs-type="flex" style="border-bottom: 1px solid #cdcdcd; padding-bottom: 15px"> -->
            <div class=" favorite-internal border-bottom pb-6 mb-2">
                <vs-row class="mb-3" vs-type="flex">
                    <span>内部サービスは下記より選択</span>
                </vs-row>
                <vs-row>
                    <vs-col v-for="(serviceInternal,index) in listService" :key="index" vs-w="6">
                        <div v-if="!listFavoriteState.some(item => item.is_shachihata === 1 && item.url === serviceInternal.url && item.service_name === serviceInternal.service_name)" class="item-favorite service-logo" style="padding: 10px; margin-right: 10px; display: flex; justify-content: center;" @click="confirmAddFavoriteInternal(serviceInternal)">
                            <img :src="'data:image/jpeg;base64,' + serviceInternal.logo_src" alt="Logo service"  class="logo-xternal">
                        </div>
                        <div v-else class="item-favorite service-logo disable" style="padding: 10px; margin-right: 10px; display: flex; justify-content: center;">
                            <img :src="'data:image/jpeg;base64,' + serviceInternal.logo_src" alt="Logo service"  class="logo-xternal">
                        </div>
                    </vs-col>
                </vs-row>
            </div>

            <!-- <div class="border-bottom pb-6 mb-2" style="margin: 0 -1rem;"></div> -->

            <form class="favorite-external">
                <vs-row vs-type="flex">
                    <span>外部サービスは下記よりリンクを追加</span>
                </vs-row>
                <vs-row class="mt-3">
                    <h4 class="pb-2">登録名</h4>
                </vs-row>
                <vs-row>
                    <vs-input placeholder="シヤチハタ" v-validate="'required'" name="service_name" id="service_name" v-model="favorite.service_name" :maxlength="50" class="inputx w-full" />
                </vs-row>
                <span v-if="errors.has('service_name')" style="color:red;">
                  {{ errors.first("service_name") }}
                </span>
                <vs-row class="mt-3">
                    <h4 class="pb-2">URL</h4>
                </vs-row>
                <vs-row>
                    <vs-input placeholder="https://www.shachihata.co.jp/" v-validate="{ required: true, regex:/^(http|https)?:\/\/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/ }" name="url" class="inputx w-full" v-model="favorite.url" />
                </vs-row>
                <span v-if="errors.has('url')" style="color:red;">
                  {{ errors.first("url") }}
                </span>

                <vs-row class="pt-5 pb-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2 " color="primary" type="filled"  @click.prevent="onSaveFavorite(null)"> 保存</vs-button>
                    <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="$modal.hide('add-favorite-modal')"> キャンセル</vs-button>
                </vs-row>
            </form>
        </modal>
      </vx-card>
    </div>
</template>

<script>
    import { mapState, mapActions } from "vuex";
    import VxPagination from '@/components/vx-pagination/VxPagination.vue';

    import { Validator } from 'vee-validate';
    import HeaderComponent from "./HeaderComponent";
    import CircularComponent from "./CircularComponent";
    import config from "../../app.config";

    const dict = {
        custom: {
            service_name: {
                required: '* 必須項目です',
            },
            url: {
                required: '* 必須項目です',
                regex: "* URLフォマットが正しくない"
            }
        }
    };
  Validator.localize('ja', dict);

    export default {
        components: {
          HeaderComponent,
          VxPagination,
          CircularComponent,
        },
        props: [],
        data() {
            return {
                isShachihataImage: true,
                // id: "",
                // name: "",
                // issetFavoriteInternal: false,
                listServiceInternal: {},
                listFavorite: {},
                // listFavoriteInternal: [],
                // listFavoriteExternal: [],
                favorite: {},
                favorite_default: {
                    service_name: '',
                    favorite: ''
                },
                tab_shachihata: 0,
                onSearchAfterSaveFavorite: ''
            };
        },
        methods: {
             ...mapActions({
                getListFavorite: "portal/getListFavorite",
                addFavorite: "portal/addFavorite",
                deleteFavorite: "portal/deleteFavorite",
                getListServiceInternal: "portal/getListServiceInternal",
            }),

            onClickFavorite: function (url) {
                window.open(url, "_blank");
            },

            onClickFavorite2: function (url) {
                url = "https://dstmp-order.shachihata.com/mypage/";
                window.open(url, "_blank");
            },

            onClickFavorite3: function (url) {
                url = config.ADMIN_API_URL;
                window.open(url, "_blank");
            },

            onClickFavorite4: function (url) {
                url = config.LOCAL_API_URL;
                window.open(url, "_blank");
            },

            onClickFavorite5: function (url) {
                url = "https://help.dstmp.com/";
                window.open(url, "_blank");
            },

            onAddFavorite() {
                // this.addFavorite = true;
                this.favorite = Object.assign({}, this.favorite_default);
                this.$modal.show('add-favorite-modal');
            },
            async confirmAddFavoriteInternal(serviceInternal){
                this.$vs.dialog({
                    type: 'confirm',
                    color: 'primary',
                    title: `確認`,
                    acceptText: 'はい',
                    cancelText: 'いいえ',
                    text: `内部サービスをお気に入り登録しますか？`,
                    accept: async () => {
                        await this.onSaveFavorite(serviceInternal);
                    }
                });
            },
            async confirmDeleteFavorite(favoriteId){
                this.$vs.dialog({
                    type: 'confirm',
                    color: 'primary',
                    title: `確認`,
                    acceptText: 'はい',
                    cancelText: 'いいえ',
                    text: `お気に入りから削除しますか？`,
                    accept: async () => {
                        // await this.onDeleteFavorite(favoriteId);
                        await this.deleteFavorite(favoriteId);
                        this.getListFavorite(this.mypage_id);
                    }
                });
            },

            async onSaveFavorite(serviceInternal){
                let data = {
                    mypage_id : this.mypage_id,
                    is_shachihata : serviceInternal ? 1 : 0,
                    service_name : serviceInternal?serviceInternal.service_name:this.favorite.service_name,
                    logo_src : "https://www.google.com/s2/favicons?sz=64&domain=" + encodeURIComponent(serviceInternal?serviceInternal.url:this.favorite.url),
                    url : serviceInternal?serviceInternal.url:this.favorite.url,
                }
                if(serviceInternal){
                    await this.addFavorite(data);
                    if (this.onSearchAfterSaveFavorite === '') {
                        this.onSearchAfterSaveFavorite = true;
                    } else {
                        this.onSearchAfterSaveFavorite = !this.onSearchAfterSaveFavorite;
                    }
                    this.getListFavorite(this.mypage_id);
                    this.$modal.hide('add-favorite-modal');
                }else{
                    this.$validator.validate().then(async valid => {
                        if (valid) {
                            await this.addFavorite(data);
                            this.getListFavorite(this.mypage_id);
                            this.$modal.hide('add-favorite-modal');
                        }
                    });
                }
            },

            validateUrl(value) {
                if(value && value.match(/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/))
                {
                    return true
                }
                return 'False'
            }
        },
        computed: {
             ...mapState({
                listFavoriteState: state => state.portal.listFavorite,
                listService: state => state.portal.listService,
                mypage_id: state => state.portal.currentMyPage,
            }),
            logoSrcDefault: {
                get() {
                    if (this.listService &&
                        this.listService.length > 0 &&
                        this.listService[0].logo_src) {
                        return this.listService[0].logo_src;
                    } else {
                        return null;
                    }
                }
            },

            listFavoriteExternal: {
                get() {return this.listFavoriteState.filter((item) => item.is_shachihata == 0);},
            },

            listFavoriteInternal: {
                get() {return this.listFavoriteState.filter((item) => item.is_shachihata == 1);},
            },

            issetFavoriteInternal: {
                get() {return  this.listFavoriteInternal.length;},
            },

            countFavorite: {
                get() {return  this.$store.state.portal.listFavorite.length;},
            },

        },

        watch:{
        },

        async mounted() {
            if (this.mypage_id){
                await this.getListFavorite(this.mypage_id);
            }
        },

        async created() {
          await this.getListServiceInternal();
        }
    }
</script>
