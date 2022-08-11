<template>
    <div id="setting-page" style="position: relative;">
        <vs-row>
            <vs-col vs-xs="12" vs-lg="6" class="sm:pr-2" v-if="!isAuditUser && !received_only_flg && optionFlg != 1">
                <vs-card >
                    <div slot="header"><h4>印鑑の並び替え</h4></div>
                    <vs-row>
                        <vs-col vs-xs="12" vs-lg="8">印鑑を表示し、並び順を設定します。</vs-col>
                        <vs-col vs-xs="12" vs-lg="4" class="text-right mt-3 md:mt-0">
                            <vs-button class="square" color="primary" @click="showStampsOrdering = !showStampsOrdering">
                                <i class="fas fa-info-circle"></i> 印鑑表示
                            </vs-button>
                        </vs-col>
                    </vs-row>
                </vs-card>
                <vs-card v-if="!received_only_flg && infoCheck['enable_email'] == 1 && optionFlg != 1">
                    <div slot="header"><h4>メール受信設定</h4></div>
                    <vs-row class="tt-type">
                        メール
                        </vs-row>
                        <vs-row class="pl-50">
                            <label for="enable_email1" style="margin: 0px 48px 0 0;width: 41px;">
                                <input type="radio" id="enable_email1" value="1" name="enable_email" v-model="info.enable_email">
                                有効
                            </label>
                            <label for="enable_email0" style="margin: 0px 48px 0 0;width: 41px;">
                                <input type="radio" id="enable_email0" value="0" name="enable_email" v-model="info.enable_email">
                                無効
                            </label>
                        </vs-row>
                    

                    <ul class="centerx" v-if="info && info.enable_email == 1 && optionFlg != 1">
                        受信するメールにチェックを付けてください
                        <li class="mt-2">
                            <vs-checkbox :value="info.completion_sender_notice_flg"
                                         @click="info.completion_sender_notice_flg =! info.completion_sender_notice_flg">回覧完了メール（申請者時）
                            </vs-checkbox>
                        </li>
                        <li class="mt-2">
                          <vs-checkbox :value="info.completion_notice_flg"
                                       @click="info.completion_notice_flg =! info.completion_notice_flg">回覧完了メール（承認者時）
                          </vs-checkbox>
                        </li>
                        <li class="mt-2">
                            <vs-checkbox :value="info.approval_request_flg"
                                         @click="info.approval_request_flg =! info.approval_request_flg">承認（回覧）依頼メール
                            </vs-checkbox>
                        </li>
                        <li class="mt-2">
                          <vs-checkbox :value="info.pullback_notice_flg"
                                       @click="info.pullback_notice_flg =! info.pullback_notice_flg">引戻し通知メール
                          </vs-checkbox>
                        </li>
                        <li class="mt-2">
                          <vs-checkbox :value="info.sendback_notice_flg"
                                       @click="info.sendback_notice_flg =! info.sendback_notice_flg">差戻し通知メール
                          </vs-checkbox>
                        </li>
                        <li class="mt-2">
                          <vs-checkbox :value="info.download_notice_flg"
                                       @click="info.download_notice_flg =! info.download_notice_flg">ダウンロード処理完了通知メール
                          </vs-checkbox>
                        </li>
                        <li class="mt-2" v-if="infoCheck['updated_notification_email_flg'] == 1">
                            <vs-checkbox :value="info.update_notice_flg"
                                         @click="info.update_notice_flg =! info.update_notice_flg">更新通知メール
                            </vs-checkbox>
                        </li>
                        <li class="mt-2" v-if="infoCheck['view_notification_email_flg'] == 1">
                            <vs-checkbox :value="info.browsed_notice_flg"
                                         @click="info.browsed_notice_flg =! info.browsed_notice_flg">閲覧通知メール
                            </vs-checkbox>
                        </li>
                    </ul>

                    <div class="text-right mt-3 md:mt-0">
                        <vs-button class="square" color="primary" @click="onUpdateSetting"><i
                            class="fas fa-save"></i> 設定変更
                        </vs-button>
                    </div>
                </vs-card>

                <vs-card v-if="optionFlg != 1">
                    <div slot="header"><h4>コメント設定</h4></div>
                    送信時のコメントに追加できる定型文を設定します。
                    <br>
                    全角半角問わず、20文字まで入力できます。

                    <vs-row class="mt-5">
                      <vs-input class="inputx w-full" v-model = "comment.comment1" />
                    </vs-row>
                    <vs-row class="mt-3">
                      <vs-input class="inputx w-full" v-model = "comment.comment2"/>
                    </vs-row>
                    <vs-row class="mt-3">
                      <vs-input class="inputx w-full" v-model = "comment.comment3"/>
                    </vs-row>
                    <vs-row class="mt-3">
                      <vs-input class="inputx w-full" v-model = "comment.comment4"/>
                    </vs-row>
                    <vs-row class="mt-3">
                      <vs-input class="inputx w-full" v-model = "comment.comment5"/>
                    </vs-row>
                    <vs-row class="mt-3">
                      <vs-input class="inputx w-full" v-model = "comment.comment6"/>
                    </vs-row>
                    <vs-row class="mt-3">
                      <vs-input class="inputx w-full" v-model = "comment.comment7"/>
                    </vs-row>
                    <!-- PAC_5-732 コメント欄にパスワードのオートコンプリートが表示される ダミー入力欄を用意して対応 -->
                    <input type="text" name="comment.dummy" style="border-style:None; width: 0px;height: 0px"/>
                    <!-- PAC_5-732 End -->
                    <div class="text-right mt-3">
                      <vs-button class="square" color="#dddddd" style="color:black;border-color:#ffffff;padding-left: 2.3em; padding-right: 2.3em;" @click="onResetComment"><i class="fas fa-sync-alt"></i> 初期値に戻す</vs-button>
                    </div>
                    <div class="text-right mt-3">
                      <vs-button class="square" color="primary" @click="onUpdateComment"><i class="fas fa-save"></i> 設定を保存する</vs-button>
                    </div>

                    <!--                     <vs-row class="mt-3">-->
                    <!--                         <div class="text-right mt-3 md:mt-0">-->
                    <!--                             <vs-button class="square" color="primary" @click="onResetComment"><i class="fas fa-sync-alt"></i> 初期値に戻す</vs-button>-->
                    <!--                         </div>-->
                    <!--                     </vs-row>-->
                </vs-card>
            </vs-col>
            <vs-col vs-xs="12" vs-lg="6" class="sm:pl-2">
                <!--PAC_5-1878 Start SAML企業の利用者をログインパスワードの変更グループを見えないように修正する-->
                <vs-card v-show="showPassworEdit">
                <!--PAC_5-1878 End-->
                    <div slot="header"><h4>ログインパスワードの変更</h4></div>
                    新しいパスワードを入力して　［パスワード変更］　ボタンをクリックします。

                    <vs-row class="mt-3">
                        <vs-col vs-w="2"></vs-col>
                        <vs-col vs-w="8" class="v-notice">
                            <!--<vs-alert active="true" color="warning">-->
                                {{ passwordPolicy.min_length }}～32文字の半角英数字、記号が設定可能です。<br/>
                                必ず英字と数字を含めてください。<br/>
                                ※英字の大文字と小文字は区別されます。<br/>
                                （設定例）@shachihata1234, #1234shachihata など
                            <!-- </vs-alert> -->
                        </vs-col>
                        <vs-col vs-w="2"></vs-col>
                    </vs-row>


                    <vs-row class="mt-5">
                        <vs-col vs-w="4" class="text-right-lg pt-3 pr-3">新しいパスワード <span class="red">*</span></vs-col>
                        <vs-col vs-w="8">
                            <vs-input class="inputx w-full" :type="show_password?'text':'password'" v-model="password"/>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="4" class="text-right-lg pt-3 pr-3">新しいパスワードを再入力</vs-col>
                        <vs-col vs-w="8">
                            <vs-input class="inputx w-full" :type="show_password?'text':'password'"
                                      v-model="password_confirmation"/>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="4"></vs-col>
                        <vs-col vs-w="8">
                            <vs-row>
                                <vs-col vs-w="6">
                                    <vs-checkbox :value="show_password" @click="togglePassword">パスワードを表示</vs-checkbox>
                                </vs-col>
                                <vs-col vs-w="6" class="text-right">
                                    <vs-button class="square" color="primary" @click="onUpdatePassword"><i
                                        class="fas fa-sync-alt"></i> パスワード変更
                                    </vs-button>
                                </vs-col>
                            </vs-row>
                        </vs-col>
                    </vs-row>

                </vs-card>

                <vs-card v-if="!isAuditUser && !received_only_flg && optionFlg != 1">
                    <div slot="header"><h4>表示設定</h4></div>
                    <vs-row class="mt-5">
                        <vs-row class="tt-type">
                            ログイン後に表示するページ
                        </vs-row>
                        <vs-row class="pl-50">
                            <label for="start_page5" v-show="myCompany && myCompany.portal_flg && optionFlg != 2">
                                <input type="radio" id="start_page5" value="ポータル" name="start_page"
                                       v-model="displaySetting.start_page">
                                マイページ
                            </label>
                            <label for="start_page" v-show="optionFlg != 2 && !formUserFlg">
                                <input type="radio" id="start_page" value="ホーム" name="start_page"
                                       v-model="displaySetting.start_page">
                                新規作成
                            </label>
                            <label for="start_page2">
                                <input type="radio" id="start_page2" value="受信一覧" name="start_page"
                                       v-model="displaySetting.start_page">
                                受信一覧
                            </label>
                            <label for="start_page3" v-show="optionFlg != 2">
                                <input type="radio" id="start_page3" value="送信一覧" name="start_page"
                                       v-model="displaySetting.start_page">
                                送信一覧
                            </label>
                            <label for="start_page4">
                                <input type="radio" id="start_page4" value="完了一覧" name="start_page"
                                       v-model="displaySetting.start_page">
                                完了一覧
                            </label>
                            <label for="start_page1" v-show="optionFlg != 2 && !formUserFlg">
                                <input type="radio" id="start_page1" value="下書き一覧" name="start_page"
                                       v-model="displaySetting.start_page">
                                下書き一覧
                            </label>       
                        </vs-row>
                    </vs-row>
                    <vs-row class="mt-5">
                        <vs-row class="tt-type">
                            操作に関するメッセージ
                        </vs-row>
                        <vs-row class="pl-50">
                            <label for="display_notice">
                                <input type="radio" id="display_notice" name="operation_notice_flg" :value="1"
                                       v-model="displaySetting.operation_notice_flg">
                                表示する
                            </label>
                            <label for="do_not_display_notice">
                                <input type="radio" id="do_not_display_notice" name="operation_notice_flg" :value="0"
                                       v-model="displaySetting.operation_notice_flg">
                                表示しない
                            </label>
                        </vs-row>
                    </vs-row>
                    <vs-row class="mt-5">
                        <vs-row class="tt-type">
                            回覧文書の詳細情報(初期表示設定)
                        </vs-row>
                        <vs-row class="pl-50">
                            <label for="cir-info0">
                                <input type="radio" id="cir-info0" value="印鑑" name="cir-info"
                                       v-model="displaySetting.circular_info">
                                印鑑
                            </label>
                            <label for="cir-info1">
                                <input type="radio" id="cir-info1" value="回覧先" name="cir-info"
                                       v-model="displaySetting.circular_info">
                                回覧先
                            </label>
                            <label for="cir-info2">
                                <input type="radio" id="cir-info2" value="コメント" name="cir-info"
                                       v-model="displaySetting.circular_info">
                                コメント
                            </label>
                            <label for="cir-info3">
                                <input type="radio" id="cir-info3" value="捺印履歴" name="cir-info"
                                       v-model="displaySetting.circular_info">
                                捺印履歴
                            </label>
                        </vs-row>
                    </vs-row>
                    <vs-row class="mt-5">
                        <vs-row class="tt-type">
                            文書編集中のコーション表示（ページ離脱警告）
                        </vs-row>
                        <vs-row class="pl-50">
                            <label for="w_caution1">
                                <input type="radio" id="w_caution1" name="w_caution" :value="1"
                                       v-model="info.withdrawal_caution">
                                表示する
                            </label>
                            <label for="w_caution0">
                                <input type="radio" id="w_caution0" name="w_caution" :value="0"
                                       v-model="info.withdrawal_caution">
                                表示しない
                            </label>
                        </vs-row>
                    </vs-row>
                    <div class="text-right mt-3">
                        <vs-button class="square" color="primary" @click="onUpdateDisplaySetting"><i
                            class="fas fa-save"></i> 設定を保存する
                        </vs-button>
                    </div>
                </vs-card>
            </vs-col>
        </vs-row>

        <vs-popup classContent="popup-example" color="w-p20 vs-popup-min-w-300" title="ご利用可能な印鑑"
                  :active.sync="showStampsOrdering">
            <draggable v-model="stamps">
                <transition-group class="row-equal-height">
                    <vs-col vs-w="4" class="stamp-item" v-for="stamp in stamps" :key="stamp.id">
                        <img :src="'data:image/png;base64,'+stamp.url" alt="stamp-img"
                             v-tooltip.top-center="stamp.stamp_flg == 1 ? stamp.stamp_name : ''"
                             style="width: 100%; cursor: pointer; background: #fff;"/>
                    </vs-col>
                </transition-group>
            </draggable>

            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="center" vs-justify="center" vs-w="12">
                    <vs-button class="square" color="success" @click="onSaveStampsOrder"> 更新</vs-button>
                    <vs-button class="square" color="dark" type="border" @click="showStampsOrdering = false"> 閉じる
                    </vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        

    </div>
</template>


<script>
import config from "../../app.config";
import {mapState, mapActions} from "vuex";
import draggable from 'vuedraggable'
import Axios from "axios";

export default {
    components: {
        draggable,
    },
    data() {
        return {
            showStampsOrdering: false,
            password: "",
            password_confirmation: "",
            show_password: false,
            info: {},
            infoCheck: {},
            passwordPolicy: {min_length: 4},
            comment: {},
            displaySetting: {},
            showProfileImageRegister: false,
            headers: {
                "Authorization": "Bearer " + sessionStorage.getItem("token")
            },
            textImg: "プロファイル画像",
            uploadUrl: config.LOCAL_API_URL + "/uploadUserImage",
            myCompany:null,
            received_only_flg: JSON.parse(getLS('user')).received_only_flg,
            stampDisplays:[],
            optionFlg : JSON.parse(getLS('user')).option_flg,
            // PAC_5-1878 Start SAML企業の利用者をログインパスワードの変更グループを見えないように修正する
            showPassworEdit: false,
            // PAC_5-1878 End
            formUserFlg: JSON.parse(getLS('user')).form_user_flg,
            isAuditUser: JSON.parse(getLS('user')).isAuditUser
        }
    },
    methods: {
        ...mapActions({
            getMyInfo: "user/getMyInfo",
            getInfoCheck: "user/getInfoCheck",
            updateMyInfo: "user/updateMyInfo",
            updateComment: "user/updateComment",
            updateDisplaySetting: "user/updateDisplaySetting",
            updatePassword: "user/updatePassword",
            getStamps: "home/getStamps",
            saveStampsOrder: "home/saveStampsOrder",
            updateStampDisplays: "home/updateStampDisplays",
            addLogOperation: "logOperation/addLog",
            getPasswordPolicy: "setting/getPasswordPolicy",
            getAvatarUser: "user/getAvatarUser",
            deleteImageProfile: "user/deleteImageProfile",
            updateUserImageProfileGroupWare: "user/updateUserImageProfileGroupWare",
        }),

        onResetComment() {
            this.comment.comment1 = '承認をお願いします。';
            this.comment.comment2 = '至急確認をお願いします。';
            this.comment.comment3 = '了解。';
            this.comment.comment4 = '了解しました。';
            this.comment.comment5 = '承認しました。';
            this.comment.comment6 = '差戻します。';
            this.comment.comment7 = 'いつもお世話になっております。';
        },

        onUpdateSetting() {
            this.updateComment(this.info);
            this.addLogOperation({action: 'r06-update-mail', result: 0});
        },

        onUpdateComment() {
            this.info.comment1 = this.comment.comment1;
            this.info.comment2 = this.comment.comment2;
            this.info.comment3 = this.comment.comment3;
            this.info.comment4 = this.comment.comment4;
            this.info.comment5 = this.comment.comment5;
            this.info.comment6 = this.comment.comment6;
            this.info.comment7 = this.comment.comment7;
            this.updateComment(this.info);
            this.addLogOperation({action: 'r06-update-comment', result: 0});
        },

        onUpdateDisplaySetting() {
            this.$store.commit('setting/setWithdrawalCaution', this.info.withdrawal_caution );
            this.info.page_display_first = this.displaySetting.start_page;
            this.info.circular_info_first = this.displaySetting.circular_info;
            this.info.operation_notice_flg = this.displaySetting.operation_notice_flg;
            this.updateDisplaySetting(this.info);
            this.addLogOperation({action: 'r06-update-display', result: 0});
        },

        onUpdatePassword() {
            this.updatePassword({password: this.password, password_confirmation: this.password_confirmation});
        },

        async onSaveStampsOrder() {
            var ret = await this.saveStampsOrder({stampDisplays: this.stampDisplays});
            // if (ret) {
            //     this.addLogOperation({action: 'r06-stamp-order', result: 0});
            // } else {
            //     this.addLogOperation({action: 'r06-stamp-order', result: 1});
            // }
        },
        togglePassword() {
            this.show_password = !this.show_password;
        },

        closeExistedImage() {
            $('.image_profile_in_db > .img-upload').addClass('removeItem');
            $('.image_profile_in_db').hide();
            $('.vs-upload-container').removeClass('hidden');
            $('#uploadImageProfile').show();
            this.textImg = "画像を保存するため、登録ボタンをクリックしてください。";
        },

        onUploadImage() {
            if ($('.img-upload:not(.removeItem)').length > 0) {
                $('.img-upload:not(.removeItem) > button:nth-last-of-type(1)').trigger('click');
            } else {
                let data = { image: ''};
                this.deleteImageProfile(data);
                this.addLogOperation({action: 'r06-06-setting-update-profile-image', result: 0});
                let gwAccessToken = this.$cookie.get('accessToken');
                if (gwAccessToken){
                  let dataGroupWare = {
                    tokenGroupware  : gwAccessToken,
                    userProfileData : null
                  }
                  this.updateUserImageProfileGroupWare(dataGroupWare);
                }
            }
            this.textImg = 'プロファイル画像';
            // update Avatar when no image uploaded
            $(".avatar-user").html('<span><i style="width: 100%;" class="fas fa-user"></i></span>')
        },

        // update Avatar in navbar when image uploaded
        async successUpload () {
            let imgSrc = $('.img-upload:not(.removeItem) > img').attr("src");
            if(imgSrc){
                $( ".avatar-user" ).html('<img style="width: 100%;" src=" ' + imgSrc + ' " alt="avatar">');
                let gwAccessToken = this.$cookie.get('accessToken');
                if (gwAccessToken){
                  let userProfileData = await this.getAvatarUser();
                  let avatar = userProfileData.user_profile_data;
                  let data = {
                    tokenGroupware  : this.$cookie.get('accessToken'),
                    userProfileData : avatar
                  }
                  this.updateUserImageProfileGroupWare(data);
                }
            }
            this.addLogOperation({action: 'r06-06-setting-update-profile-image', result: 0});
        },

        errorUpload(event) {
            this.addLogOperation({action: 'r06-06-setting-update-profile-image', result: 1});
            let error = event.currentTarget.statusText;
            alert(error);
        },

        deleteUpload() {
            this.textImg = "画像を保存するため、登録ボタンをクリックしてください。";
        },
    },
    computed: {
        stamps: {
            get() {
                return this.stampDisplays;
            },
            set(value) {
                this.stampDisplays = value.map((item,index) => {
                    item.display_no = index + 1;
                    return item;
                });

                this.updateStampDisplays(value);
            }
        },
    },


    async mounted() {
        $('#ctl_startpage5').hide();
        let root = this;
        if (!this.isAuditUser) {
            this.info = await this.getMyInfo();
        }
        this.comment = {
            comment1: this.info.comment1,
            comment2: this.info.comment2,
            comment3: this.info.comment3,
            comment4: this.info.comment4,
            comment5: this.info.comment5,
            comment6: this.info.comment6,
            comment7: this.info.comment7,
        };
        this.displaySetting = {
            start_page: this.info.page_display_first,
            circular_info: this.info.circular_info_first,
            operation_notice_flg: this.info.operation_notice_flg
        };
        if (!this.isAuditUser) {
            this.infoCheck = await this.getInfoCheck();
        }
        this.passwordPolicy = await this.getPasswordPolicy();
        // this.getStamps({date: this.$moment(new Date()).format('YYYY-MM-DD')});
        if (!this.isAuditUser) {
            await Axios.get(`${config.BASE_API_URL}/myStamps?date=${this.$moment(new Date()).format('YYYY-MM-DD')}`, {data: {nowait: true}})
                .then(response => {
                  let id = response.data.data.length + 1;
                  this.stampDisplays = [];
                  response.data.data.forEach((item, index) => {
                    const stamp = {
                      id: id + index,
                      db_id: item.id,
                      sid: item.sid,
                      url: item.stamp_image,
                      stamp_division: item.stamp_division,
                      width: item.width * 0.001 * 3.7795275591,
                      height: item.height * 0.001 * 3.7795275591,
                      date_width: item.date_width * 3.7795275591,
                      date_height: item.date_height * 3.7795275591,
                      date_x: item.date_x * 3.7795275591,
                      date_y: item.date_y * 3.7795275591,
                      display_no: item.display_no,
                      stamp_flg: item.stamp_flg,//0：通常印 1：共通印 2：日付印
                      time_stamp_permission: item.time_stamp_permission,
                      serial: item.serial,
                      stamp_name: item.stamp_name, //印面の名称
                    };
                    // state.stamps.push(stamp);
                    this.stampDisplays.push(stamp);
                  });
                })
                .catch(error => {
                  return [];
                });
        }

        let profile_image = await this.getAvatarUser();
        let avatar = profile_image.user_profile_data;
        if (avatar) {
            $('.vs-upload-container').addClass('hidden');
            let img_dom = document.createElement("IMG");
            img_dom.src = `data:image/jpeg;base64,${avatar}`;
            $(img_dom).addClass('style_img_profile');
            $('.image_profile_in_db .img-upload').append(img_dom);
            $('#uploadImageProfile').hide();
        } else {
            $('.image_profile_in_db').hide();
        }
        $("#primaryImageUploadId[type=file]").on('change',function(){
            root.textImg = "画像を保存するため、登録ボタンをクリックしてください。";
        });
        Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
        .then(response => {
            root.myCompany = response.data ? response.data.data: [];
            if (!root.myCompany || !root.myCompany.portal_flg){
              if (this.displaySetting.start_page == 'ポータル'){
                this.displaySetting.start_page = 'ホーム';
              }
            }
            // PAC_5-1878 Start SAML企業の利用者をログインパスワードの変更グループを見えないように修正する
            if (!root.myCompany || typeof root.myCompany.login_type == 'undefined' || root.myCompany.login_type !== 1) {
                this.showPassworEdit = true;
            }
            // PAC_5-1878 End
        })
        .catch(error => { return []; });
    },
    async created() {
        await this.addLogOperation({action: 'r06-display', result: 0});
    }
}

</script>
<style lang="scss">
.v-notice {
        background-color: #fff1e3;
        color           : #ff9f43;
        border-radius:7px;
        font-size: 1rem;
        font-weight: 500;
        padding: 10px 10px 10px 7px;
}
</style>
