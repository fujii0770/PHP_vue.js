<template>
    <div>
	<div id="main-home">
		<div style="margin-bottom: 15px">
            <vs-row class="mb-3">
                <vs-col vs-w="2" vs-align="center" vs-type="flex" vs-justify="center">
                    <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-out-container"><vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out"><i class="fas fa-minus"></i> </vs-button></div></vs-col>
                    <vs-col vs-w="6" vs-justify="center" vs-align="center"><div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{zoom}}%</label></div></vs-col>
                    <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-in-container"><vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in"><i class="fas fa-plus"></i> </vs-button></div></vs-col>
                </vs-col>
            </vs-row>
		</div>
        <vs-card class="work-content template">
            <vs-row>
                <vs-col vs-type="flex" vs-w="8">
                    <div class="pdf-content template" ref="pdfViewer" style="position: relative;">
                        <vs-col vs-type="flex" vs-w="12" vs-align="flex-start" vs-justify="flex-start">
                            <vs-navbar v-model="tabSelected"
                                color="#fff"
                                active-text-color="rgb(9,132,227)"
                                class="filesNav">

                                <vs-navbar-item v-for="(file, index) in filesLessThanMaxTabShow" v-bind:key="index" :index="index" class="document">
                                    <template v-if="index < maxTabShow">
                                       <a v-tooltip.top-center="file.file_name" href="#">
                                           {{file.file_name}}
                                        </a>
                                    </template>
                                </vs-navbar-item>

                                <vs-spacer></vs-spacer>
                                <vs-navbar-item class="more-document" v-if="files.length > maxTabShow">
                                    <vs-dropdown >
                                        <a class="a-icon" href="#" :style="(tabSelected > (maxTabShow -1) ? 'color:#0984e3':'')">
                                            <i class="fas fa-ellipsis-h" style="font-size: 20px"></i>
                                            <vs-icon class="" icon="expand_more"></vs-icon>
                                        </a>
                                        <vs-dropdown-menu>
                                            <vs-dropdown-item v-for="(file, index) in filesMoreThanMaxTabShow" v-bind:key="index" :index="index" :class="'more-document-item '">
                                                <p class="filename" v-tooltip.left-start="file.file_name" :style="'white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin-right: 50px;font-size: 14px;max-width: 185px;min-height:25px' + (index === tabSelected ? 'color:#0984e3':'')">{{file.file_name}}</p>
                                            </vs-dropdown-item>
                                        </vs-dropdown-menu>
                                    </vs-dropdown>
                                </vs-navbar-item>
                            </vs-navbar>
                        </vs-col>
                        <div id="pdfContent" ref="pageWrap" :style="(fileSelected == null ? 'display:none':'')" class="content vs-con-loading__container" v-on:scroll="onHandleScroll">
                            <div id="pageWrap" v-show="show">
                                <div ref="page" v-for="(item, index) in fileImage" class="page page_large" v-bind:key="index" :index="index">
                                    <!-- <img :src="require('@assets/images/sampleTemplate.jpg')" alt="a4" style="width: 100%"> -->
                                    <img :src="'data:image/png;base64,'+item" alt="a4" style="width: 100%">
                                </div>
                            </div>
                        </div>
                    </div>
                </vs-col>
                <!-- <vs-col vs-type="flex" vs-w="4" style="transition: width .2s;"> -->
                <vs-col vs-type="flex" vs-w="4">
                    <div id="fields" class="tools fields py-2 px-4 vs-con-loading__container">
                        <div class="body">
                            <div  v-show="templateCsvFlg">
                                <vs-row class="form-item mb-2" v-for="(data,index) in emailFormList" v-bind:key="index" :index="index">
                                    <vs-col vs-type="flex" vs-w="20" >
                                        <vs-col vs-w="4" vs-type="flex" style="padding-top:8px">
                                            <label>CSV出力ユーザ {{ index+1 }}</label>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-lg="9" vs-sm="16" vs-xs="32" class="mb-3 sm:pl-2">
                                            <p style="line-height:40px" >【{{ data }}】</p>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="8" class="mb-3 sm:pl-2">
                                            <v-flex xs10 sm4 md4 text-center my-5><vs-button class="square" color="danger" v-on:click="removeInput(index)"> x </vs-button></v-flex>
                                        </vs-col>
                                    </vs-col>
                                </vs-row>
                                <vs-row class="form-item mb-2">
                                    <vs-col vs-type="flex" vs-w="20" >
                                        <vs-col vs-w="4" vs-type="flex" v-if="!isTextMax" style="padding-top:8px">
                                            <label>CSV出力ユーザ</label>
                                        </vs-col>
                                        <!-- :setLabel="setEmailSuggestLabel" -->
                                        <vs-col vs-col vs-type="flex" vs-lg="9" vs-sm="16" vs-xs="32" class="mb-3 sm:pl-2" style="padding-top:5px">
                                            <vue-suggestion :items="emailSuggestions"
                                                            v-model="emailSuggestModel"
                                                            placeholder="CSV出力ユーザのメールアドレス"
                                                            :itemTemplate="emailTemplate"
                                                            :minLen="1"
                                                            @onInputChange="getEmailsSuggestionList"
                                                            @onItemSelected="onSuggestSelect" 
                                                            :setLabel="setEmailSuggestLabel"
                                                            @focus="getFocusEmailsSuggestionList"
                                                            v-if="!isTextMax" 
                                                            >
                                            </vue-suggestion>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-lg="4" vs-sm="4" vs-xs="8" class="sm:pl-2">
                                            <v-flex xs10 sm4 md4 text-center my-5><vs-button class="square" color="success" @click="addEmailList" v-if="!isTextMax"> 追加 </vs-button></v-flex>
                                        </vs-col>
                                    </vs-col>
                                </vs-row>
                                <vs-col>
                                    <span class="text-danger text-sm" v-show="emailSuggestValidateMsg">{{ emailSuggestValidateMsg }}</span>
                                </vs-col>
                            </div>
                            <div>
                                <vs-col>
                                    <span class="text-danger text-sm" v-show="inputValueDigitMsg">{{ inputValueDigitMsg }}</span>
                                </vs-col>
                            </div>
                            <vs-row class="form-item mb-3" v-for="(field, index) in placeholderData" v-bind:key="index" :index="index">
                                <vs-col vs-w="4" vs-type="flex" vs-align="center"><label>{{field.template_placeholder_name}}</label></vs-col>
                                <vs-col vs-w="7" vs-type="flex" vs-align="center"><vs-input class="inputx w-full" label="" v-model="field.template_placeholder_value" :disabled="field.confirm_flg==1 || field.confirm_flg==2"/></vs-col>
                                <div v-if="send_circular_template_edit_flg">
                                    <vs-col v-if="field.confirm_flg!=2">
                                        <vs-col v-if="!field.confirm_flg"><vs-button class="square" color="success" @click="onAddConfirm(index)">確定</vs-button></vs-col>
                                        <vs-col v-if="field.confirm_flg"><vs-button class="square" color="danger" @click="onRelease(index)">解除</vs-button></vs-col>
                                    </vs-col>
                                </div>
                            </vs-row>
                        </div>
                        <div class="footer" v-show="show">
                            <vs-button @click="onSave" color="primary" type="filled" :disabled="clickState">送信</vs-button>
                        </div>
                    </div>
                </vs-col>
            </vs-row>
        </vs-card>
	</div>
    </div>
</template>
<script>
    import { mapState, mapActions } from "vuex";
    import InfiniteLoading from 'vue-infinite-loading';

    import config from "../../app.config";

    import VueSuggestion from 'vue-suggestion';

    import Axios from "axios";

    import emailTemplate from './email-suggest-template.vue';

    import usernameTemplate from './username-suggest-template.vue';

    function setZoom(zoom,el, a4Scale = 1) {

        if(!el) {
          return;
        }
        el.style["width"] = (zoom * a4Scale) + '%';
        el.style["margin"] = '0 auto';
    }


    export default {
        components: {
            InfiniteLoading,
            VueSuggestion,
        },
        directives: {
        },
        data() {
            return {
                pages: [],
                currentPageNo: 1,
                maxTabShow: 4,
                tabSelected: 0,
                oldZoom: 100,
                file_name: '', // ファイル名
                old_file_name: '', // 元ファイル名
                zoom: 100,
                oldTabSelected: null,
                oldDisplayHeight: 0,
                show: true,
                fileImage: [],
                startPage:0,
                totalPage: 0,
                isScrollHandling:false,
                emailSuggestions: [],
                emailSuggestModel: '',
                userSuggestions: [],
                userSuggestModel: '',
                usernameTemplate: usernameTemplate,
                suggestDisabled: false,
                emailTemplate: emailTemplate,
                maxTextCount:5,
                emailSuggestValidateMsg:'',
                inputValueDigitMsg:'',
                emailFormList:[],
                emailErrorFlg: false,
                templateCsvFlg:false,
                error_data:[],
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
                checkUserInputTimeout: null,
                checkEmailInputTimeout: null,
                emailSelectedFlg: false,
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 End
                special_sit_flg: false, //特設サイト回覧
              clickState: false, //二重チェック用
            }
        },
        computed: {
            ...mapState({
                files: state => state.template.files,
                fileSelected: state => state.template.fileSelected,
                homeFiles: state => state.template.homeFiles,
                stampUsed: state => state.template.stampUsed,
                send_circular_template_edit_flg: state => state.template.send_circular_template_edit_flg,
                no_placeHolder: state => state.template.no_placeHolder,            
            }),
            filesLessThanMaxTabShow() {
                return this.files.filter((item , index) => index < this.maxTabShow)
            },
            filesMoreThanMaxTabShow() {
                return this.files.filter((item , index) => index > (this.maxTabShow -1))
            },
            placeholderData() {
                if (!this.fileSelected) return null;
                return this.fileSelected.placeholderData;
            },
            isTextMax(){
                return(this.emailFormList.length >= this.maxTextCount);
            }
        },
        methods: {
            ...mapActions({
                selectFile: "template/selectFile",
                changePositionFile: "template/changePositionFile",
                editTemplate: "template/editTemplate",
                convertExcelToImage: "template/convertExcelToImage",
                clearHomeState: "home/clearState",
                saveInputData: "template/saveInputData",
                CsvDownloadUserForm:"template/CsvDownloadUserForm",
                getCsvFlg:"template/getCsvFlg",
                templateCsvCheckEmail:"template/templateCsvCheckEmail",
                saveStampInfo: "home/saveTemplateEditStamp",
                loadCircular: "template/loadCircular",
                saveFileAndSignature: "template/saveFileAndSignature",
                saveFile: "template/saveFile",
                getTemplateEditStamp: "template/getTemplateEditStamp",
                getTemplateEditText: "template/getTemplateEditText",
                sendTemplateEditFlg: "template/sendTemplateEditFlg",
                getCircularTempEdit: "template/getCircularTempEdit",
            }),
            setUsernameSuggestLabel (item) {
                return item.name;
            },
            onZoomOutClick: function () {
                this.zoom = parseInt(this.zoom);
                if(this.zoom > 0) {
                    this.zoom-=10;
                }
                if(this.zoom < 50) {
                    this.zoom = 50;
                }
            },
            onZoomInClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom+= 10;
                if(this.zoom > 200) {
                    this.zoom = 200;
                }
            },
            onHandleScroll: async function (e) {
                if(!this.files  || this.files.length <= 0 || this.startPage == 0) {
                  return;
                }
                //10px is padding of page
                const scrollHeight = e.target.scrollHeight - this.totalPage * 10 - 20;
                //calc page size in browser
                let pageSize = scrollHeight / this.totalPage;
                //scale if zoom less than 100 %
                if(this.zoom < 100) {
                  pageSize = pageSize * (this.zoom / 100);
                }

                if((e.target.clientHeight + e.target.scrollTop) >=  e.target.scrollHeight) {
                    if (!this.isScrollHandling){
                        this.isScrollHandling = true;
                        this.$store.dispatch('updateLoading', true);
                        if (this.startPage >0) {
                            let data = {
                                templateId: this.files[0].id,
                                storageFileName: this.files[0].storage_file_name,
                                page: this.startPage,
                                special_sit_flg: this.special_sit_flg,
                            };
                            let pageContent = await this.convertExcelToImage(data);
                            this.startPage =  pageContent.startPage;

                            this.fileImage = this.fileImage.concat(pageContent.arrImage)
                        }
                        this.$store.dispatch('updateLoading', false);
                        this.isScrollHandling = false;
                    }
                }                
            },

            onCloseDocumentClick: function(file, index) {
              this.$modal.show('delete-doc-modal');
              this.oldTabSelected = this.tabSelected;
            },
            beforeClose: function (event) {
              if(!event.params || !event.params.close) event.stop();
            },
            onSave: async function() {
              this.clickState = true;
              this.$store.dispatch('updateLoading', true);
              let data = {
                  templateId: this.files[0].id,
                  placeholderData: this.files[0].placeholderData[52],
                  special_sit_flg: this.special_sit_flg
              };
              for (var key in this.files[0].placeholderData) {
                    var valueType = isNaN(this.files[0].placeholderData[key].template_placeholder_value);
                    if(!valueType && this.files[0].placeholderData[key].template_placeholder_value){
                        let strNumberDigit = String(this.files[0].placeholderData[key].template_placeholder_value);
                        let strDigitIndex = strNumberDigit.indexOf( "." );
                        if(strDigitIndex !== -1) {
                            let NumberArray = strNumberDigit.split(".");
                            let IntegerLength = NumberArray[0].length;
                            let DecimalLength = NumberArray[1].length;
                            if(IntegerLength > 10 || DecimalLength > 5) {
                              this.$store.dispatch('updateLoading', false);
                                this.inputValueDigitMsg = '整数10桁以下小数5桁以下で入力してください';
                                return
                            }
                        }else{
                            let IntegerLength = strNumberDigit.length;
                            if(IntegerLength > 10) {
                              this.$store.dispatch('updateLoading', false);
                                this.inputValueDigitMsg = '整数10桁以下で入力してください';
                                return
                            }
                        }
                    }
                }

              for (var key in this.files[0].placeholderData) {
                data[this.files[0].placeholderData[key].template_placeholder_name]
                  = this.fileSelected.placeholderData[key].template_placeholder_value;
              }

              this.$store.commit('home/checkCircularUserNextSend', false);
              const circular_id = this.$route.params.id;
              let dataConfirm = {
                    circular_id: circular_id,
                    templateId: this.files[0].id,
                    placeholderData: this.files[0].placeholderData[52],
                    circular_temp_edit:null,
                    special_sit_flg: this.special_sit_flg
                };
                for (var key in this.files[0].placeholderData) {
                    dataConfirm[this.files[0].placeholderData[key].template_placeholder_name] 
                    = [this.fileSelected.placeholderData[key].template_placeholder_value,this.fileSelected.placeholderData[key].confirm_flg]
              }
              //const stampInfo = await this.saveStampInfo(this.homeFiles);]
              const result = await this.editTemplate(dataConfirm);
              const id = this.$store.state.template.circular_id;
              if(this.send_circular_template_edit_flg && this.no_placeHolder!=1) {
                let result = await this.sendTemplateEditFlg({circular_id:id});
              }
              if(this.$route.params.id !== undefined) {
                const edit_stamp = await this.getTemplateEditStamp(this.$route.params.id);
                const edit_text = await this.getTemplateEditText(this.$route.params.id);
                const template_edit_data = await this.saveFileAndSignature([edit_stamp,edit_text]);
              }
              if(this.$store.state.home.circular){
                this.$store.commit('application/updateCommentTitle', '');
                this.$store.commit('application/updateCommentContent', '');
                this.$store.commit('application/updateListUserView', []);
              }

              this.clearHomeState();
              this.confirmEdit = false;

              dataConfirm.circularId = id;
              await this.saveInputData(dataConfirm);
            //   data.circularId = id;
            //   let resultData = await this.saveInputData(dataConfirm);
            //   if(!resultData){
            //     return;
            //   }
              data.emailFormList = this.emailFormList;
            　await this.CsvDownloadUserForm(data);
            await this.clearTemplateStores();
              if(this.$route.params.id != undefined){
                  this.$store.commit('template/setDisplayTempEdit', 1);
              }
              this.$store.dispatch("alertSuccess", '更新に成功しました。', { root: true });
              if(this.$route.params.id) {
                  this.$router.push('/received/' + this.$route.params.id);
              }else{
              this.$router.push('/saves/' + id);
              }
            },
            clearTemplateStores () {
                this.$store.commit('template/setTemplateEditFlg', false);
                this.$store.commit('template/setHomeFilesMutation', []);
                this.$store.commit('template/selectFile', null);
                this.$store.commit('template/setHomeFileSelected', null);
                this.$store.commit('template/setCircularId', null);
                this.$store.commit('template/setCircular', null);
                this.$store.commit('template/setStorageFileName', '');
                this.$store.commit('template/setTemplateId', null);
                this.$store.commit('home/homeClearState','');
            },
            setEmailSuggestLabel (item) {
                return item.email;
            },
            // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
            getUsersSuggestionList(inputValue) {
                this.usernameSelect = inputValue;
                this.userSuggestions = [];
                if (inputValue){
                    const $this = this;
                    clearTimeout($this.checkUserInputTimeout);
                    this.checkUserInputTimeout = setTimeout(function () {
                        Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                        .then(response => {
                            if ($this.usernameSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                                $this.userSuggestions = users;
                            }
                        })
                        .catch(error => {
                            if ($this.usernameSelect === inputValue) $this.userSuggestions = [];
                        });
                    }, 300);
                }else{
                    this.userSuggestions = [];
                }
            },
            getEmailsSuggestionList(inputValue) {
                this.emailSuggestValidateMsg ='';
                this.emailSelect = inputValue;
                if(this.userSuggestSelect && this.userSuggestSelect.email !== inputValue) {
                    this.suggestDisabled = false;
                }
                this.emailSuggestions = [];
                if ( inputValue) {
                    const $this = this;
                    clearTimeout($this.checkEmailInputTimeout);
                    this.checkEmailInputTimeout = setTimeout(function () {
                        Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                        .then(response => {
                            if ($this.emailSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                                $this.emailSuggestions = users;
                            }
                        })
                        .catch(error => {
                            if ($this.emailSelect === inputValue) $this.emailSuggestions = [];
                        });
                    }, 300);
                }else{
                    this.emailSuggestions = [];
                }
            },
            getFocusEmailsSuggestionList() {
                if (this.emailSelect && this.emailSelectedFlg) {
                    var inputValue = this.emailSelect;
                    const $this = this;
                    this.emailSelectedFlg = false;
                    Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
                    .then(response => {
                        if ($this.emailSelect === inputValue) {
                            const users = response.data ? response.data.data.map(item => {
                                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                                return item
                            }) : [];
                            $this.emailSuggestions = users;
                        }
                    })
                    .catch(error => {
                        if ($this.emailSelect === inputValue) $this.emailSuggestions = [];
                    });
                }
            },
            // PAC_5-2189 End
            onSuggestSelect: function (user) {
                this.emailSuggestValidateMsg ='';
                this.userSuggestModel = user;
                this.userSuggestSelect = user;
                this.emailSelect = user.email;
                this.usernameSelect = user.name;
                this.suggestDisabled = true;
                // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
                this.userSuggestions = [];
                this.emailSuggestions = [];
                this.userSelectedFlg = true;
                this.emailSelectedFlg = true;
                // PAC_5-2189 End
            },
            removeInput(index){
                this.emailFormList.splice(index,1);
            },
            addInput(){
                this.mailtexts.push('');
            },
            submitMailStepForm() {
                this.emailSuggestValidateMsg ='';
                const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
                if(this.emailSelect.match(mailPattern) === null) {
                    this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
                    this.emailErrorFlg = true;
                    return;
                }
                this.ontemplateCsvCheckEmail();
            },
            async addEmailList(inputValue){
                this.emailErrorFlg = false;
                await this.ontemplateCsvCheckEmail();
                await this.emailListPush();
                await this.clearSuggestionInput();
                
            },
            emailListPush(){
                if(!(this.emailErrorFlg)){
                    this.emailFormList.push(this.emailSelect);
                    this.emailSelect = ' ';
                }
            },
            clearSuggestionInput() {
                this.usernameSelect = '';
                this.emailSelect = ' ';
                this.userSuggestModel = {};
                this.emailSuggestModel = ' ';
                this.suggestDisabled = false;
            },
            async ontemplateCsvCheckEmail() {
                this.emailSuggestValidateMsg ='';
                if(!this.emailSelect) {
                    this.emailSuggestValidateMsg = '必須項目です';
                    this.emailErrorFlg = true;
                    return;
                }
                const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
                if(this.emailSelect.match(mailPattern) === null) {
                    this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
                    this.emailErrorFlg = true;
                    return;
                }
                let data = {
                  input_email: this.emailSelect,
                  email_list: this.emailFormList
                };
            
                const error_data = await this.templateCsvCheckEmail(data);

                this.emailErrorFlg = error_data.error_flg;
                this.emailSuggestValidateMsg = error_data.message;
            },
            onAddConfirm(index) {
                this.$set(this.placeholderData[index], 'confirm_flg', 1);
            },
            onRelease(index) {
                this.$set(this.placeholderData[index], 'confirm_flg', 0);
            }
        },

        watch: {
            "zoom": function (newVal,oldVal) {
              newVal = parseInt(newVal);
              if(newVal) setZoom(newVal, document.getElementById('pageWrap'), this.maxScale);

            },
        },
        async mounted() {
            
        },
        async created() {
            if (this.$route.params.flg){
              this.special_sit_flg = this.$route.params.flg;
            }
            let tempEdit = await this.getCircularTempEdit({circular_id:this.$route.params.id});
            if(this.$route.params.id != undefined && tempEdit==1){
                const result = await this.loadCircular(this.$route.params.id);
                this.$store.commit('template/setTemplateEditFlg', true);
                this.$store.commit('template/setCirularTemplateEditFlg', true);
            }
            this.$store.commit('template/setTemplateId', this.files[0].id);
            this.templateCsvFlg = await this.getCsvFlg(this.templateCsvFlg);

            if(this.files && this.files.length) {
                this.$store.dispatch('updateLoading', true);
                let data = {
                    templateId: this.files[0].id,
                    storageFileName: this.files[0].storage_file_name,
                    page: 0,
                    special_sit_flg: this.special_sit_flg,
                    circular_id: this.$route.params.id
                };
                let pageContent = await this.convertExcelToImage(data);
                this.$store.dispatch('updateLoading', false);
                this.startPage =  pageContent.startPage;
                this.fileImage = this.fileImage.concat(pageContent.arrImage);
                this.totalPage = pageContent.totalPagesLoaded;
                this.selectFile(this.files[0]);
            }
        }
    }
</script>
