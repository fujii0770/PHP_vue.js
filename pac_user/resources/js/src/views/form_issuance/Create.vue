<script src="../../store/modules/expenseSettlement.module.js"></script>
<template>
    <div>
	<div id="main-home" class="form-issuance-page">
		<div style="margin-bottom: 15px">
        <vs-row class="mb-3">
            <vs-col vs-w="2" vs-align="center" vs-type="flex" vs-justify="center">
                <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-out-container"><vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out"><i class="fas fa-minus"></i> </vs-button></div></vs-col>
                <vs-col vs-w="6" vs-justify="center" vs-align="center"><div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{zoom}}%</label></div></vs-col>
                <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-in-container"><vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in"><i class="fas fa-plus"></i> </vs-button></div></vs-col>
            </vs-col>
            <vs-col vs-w="10" vs-align="flex-end" vs-justify="flex-end" vs-type="flex">
              <vs-button @click="onBack" style="color:#000;border:1px solid #dcdcdc;padding: .75rem 2rem !important;" color="white" type="filled" >戻る</vs-button>
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

                                <vs-navbar-item v-for="(file, index) in filesLessThanMaxTabShow " v-bind:key="index" :index="index" class="document">
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
                                            <vs-dropdown-item v-for="(file, index) in filesMoreThanMaxTabShow " v-bind:key="index" :index="index" :class="'more-document-item '">
                                                <p class="filename" v-tooltip.left-start="file.file_name" :style="'white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin-right: 50px;font-size: 14px;max-width: 185px;min-height:25px' + (index === tabSelected ? 'color:#0984e3':'')">{{file.file_name}}</p>
                                            </vs-dropdown-item>
                                        </vs-dropdown-menu>
                                    </vs-dropdown>
                                </vs-navbar-item>
                            </vs-navbar>
                        </vs-col>
                        <div id="pdfContent" ref="pageWrap" :style="(fileSelected == null ? 'display:none':'')" class="content vs-con-loading__container" v-on:scroll="onHandleScroll">
                            <div id="pageWrap">
                                <div ref="page" v-for="(item, index) in fileImage" class="page page_large" v-bind:key="index" :index="index">
                                    <!-- <img :src="require('@assets/images/sampleTemplate.jpg')" alt="a4" style="width: 100%"> -->
                                    <img :src="'data:image/png;base64,'+item" alt="a4" style="width: 100%">
                                </div>
                            </div>
                        </div>
                    </div>
                </vs-col>
                <vs-col vs-type="flex" vs-w="4" style="transition: width .2s;">
                    <div id="fields" class="tools fields py-2 px-4 vs-con-loading__container">
                        <div class="header" v-if="templateSetting">
                          <vs-row class="form-item mb-3">
                            <vs-col vs-w="11" vs-type="flex" vs-align="center"><label>明細名<span class="text-grey">（作成するPDFファイル名）</span></label></vs-col>
                          </vs-row>
                          <vs-row class="form-item mb-3">
                            <vs-col vs-w="11" vs-type="flex" vs-align="center">
                              <vs-input class="inputx w-full" placeholder="明細名" name="frm_name" :maxlength="128" v-validate='{ required: true, regex:/^[^\\\/:*?>"<|]+$/ }' label="" v-model="templateSetting.frm_default_name"/>
                              </vs-col>
                          </vs-row>
                          <span v-if="errors.has('frm_name')" style="color:red;">
                              {{ errors.first("frm_name") }}
                            </span>
                        </div>
                        <div class="body" v-if="placeholderData">
                          <template  v-for="(field, index) in placeholderData" :index="index">
                            <vs-row v-if="field.first_additional_flg">
                              <vs-col vs-w="11" class="pt-2 mb-2" style="border-top: solid 1px black;" >
                                <span class="fa-lg">以下は表示（印字）されない項目です。（データとして保持します）</span>
                              </vs-col>
                            </vs-row>
                            <vs-row class="form-item mb-3" v-bind:key="index">
                                <vs-col vs-w="4" vs-type="flex" vs-align="center" style="word-break: break-all"><label>{{field.frm_template_placeholder_name}}</label></vs-col>
                                <vs-col vs-w="7" vs-align="center">
                                  <vs-input :name="field.frm_template_placeholder_name" class="inputx w-full" label="" v-model="field.frm_template_placeholder_value" :maxlength="field.colSetting.max" v-if="field.colSetting && field.colSetting.type == 'string'"/>
                                  <vs-input :name="field.frm_template_placeholder_name" type="text" class="inputx w-full" label="" v-model="field.frm_template_placeholder_value" v-validate='{ regex:numberRegex }' v-else-if="field.colSetting && field.colSetting.type == 'number'" @blur="ChangeSetIndexValue(field,$event)"/>
                                  <vs-input :name="field.frm_template_placeholder_name" class="inputx w-full" v-model="field.frm_template_placeholder_value" v-else-if="field.colSetting && field.colSetting.type == 'date'" v-validate="{ leapYear:true, regex: dateRegex }"/>
                                  <vs-input :name="field.frm_template_placeholder_name" class="inputx w-full" label="" v-model="field.frm_template_placeholder_value" :maxlength="1000" v-else/>
                                  <div v-if="errors.has(field.frm_template_placeholder_name)" style="color:red;">
                                    {{ errors.first(field.frm_template_placeholder_name) }}
                                  </div>
                                </vs-col>
                            </vs-row>
                          </template>
                        </div>
                        <div class="footer">
                            <vs-button @click="onSave" color="primary" type="filled" :disabled="errors.any()">作成</vs-button>
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
    import { Validator } from 'vee-validate';
    import 'flatpickr/dist/flatpickr.min.css';
    import {Japanese} from 'flatpickr/dist/l10n/ja.js';
    import flatPickr from 'vue-flatpickr-component';
    import Utils from "../../utils/utils";

    Validator.extend('leapYear', {
      getMessage: field => '* は無効な形式です。',
      validate: (value) => {
        if (value != undefined){
          value = value.replace(/[\uFF10-\uFF19]/g, function(m) {
            return String.fromCharCode(m.charCodeAt(0) - 0xfee0);
          });
          let match = value.match(/^(明治|明|M|大正|大|T|昭和|昭|S|平成|平|H|令和|令|R|西暦|')?\s*(\d{1,4}|元)\s*[ .\-/年]\s*(\d{1,2})\s*[ .\-/月]\s*(\d{1,2})\s*日?$/);
          if (match) {
            let japanYear = match[1];
            let year = match[2];
            let month = parseInt(match[3]);
            let day = parseInt(match[4]);
            if (month == 2 && day == 29){
              if (year === "元") {
                year = 1;
              }else{
                year = parseInt(year);
              }
              let yoff = 0;
              if (japanYear == null || japanYear === "西暦") {
                if (year < 1000) {
                  yoff = 2000;
                }
              } else if (japanYear === "令和" || japanYear === "令" || japanYear === "R") {
                yoff = 2018;
              } else if (japanYear === "昭和" || japanYear === "昭" || japanYear === "S") {
                yoff = 1925;
              } else if (japanYear === "大正" || japanYear === "大" || japanYear === "T") {
                yoff = 1911;
              } else if (japanYear === "明治" || japanYear === "明" || japanYear === "M") {
                yoff = 1868;
              } else if (japanYear === "'") {
                yoff = 2000;
              }else {
                yoff = 0;
              }

              year += yoff;
              if (!(((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0))){
                return false;
              }
            }
          }
        }
        return true;
      },
    });

    const dict = {
      custom: {
        frm_name: {
          required: '* 必須項目です',
          regex: "* ファイル名に使用できない次の文字は使用不可 \\/:*?\"<>|"
        },
      },
      messages:{
        regex: () => '* 入力値は無効な形式です。',
        leapYear: () => '* 入力値は無効な形式です。'
      }
    };
    Validator.localize('ja', dict);
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
            flatPickr,
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
                fileImage: [],
                startPage:0,
                totalPage: 0,
                isScrollHandling:false,
                templateSetting: null,
                placeholderData: [],
                configDate: {
                  locale: Japanese,
                  wrap: true,
                  defaultHour: 0
                },
                dateRegex: /^(((明治|明|M|大正|大|T|昭和|昭|S|平成|平|H|令和|令|R|西暦|')\s*([0-9０-９]{1,2}|元))|([0-9０-９]{2,4}|元))\s*[ 　.．。･\-\－\ー\ｰ/／／/年]\s*((([0０]?[13578１３５７８]|[1１][02０２])\s*[ 　.．。･\-\－\ー\ｰ/／／/月]\s*([0０]?[1-9１-９]|[12１２][0-9０-９]|[3３][01０１]))|(([0０]?[469４６９]|[1１]{2})\s*[ 　.．。･\-\－\ー\ｰ/／／/月]\s*([0０]?[1-9１-９]|[12１２][0-9０-９]|[3３][0０]))|(([0０]?[2２])\s*[ 　.．。･\-\－\ー\ｰ/／／/月]\s*([0０]?[1-9１-９]|[12１２][0-9０-９])))\s*日?$/,
                numberRegex: /^[\\\¥￥]?[ ]?[0-9０-９]{1,3}(([,，])?([0-9０-９]{3})){0,3}[ ]?[円ー―－\-]?$/,
                dataCols: {'reference_date_col': {'type': 'date'},
                            'customer_name_col': {'type': 'string', 'max':1000},
                            'customer_code_col': {'type': 'string', 'max':1000},
                            'trading_date_col': {'type': 'date'},
                            'invoice_no_col': {'type': 'string', 'max':1000},
                            'invoice_date_col': {'type': 'date'},
                            'invoice_amt_col': {'type': 'number', 'max':12},
                            'payment_date_col': {'type': 'date'}}
              }
        },
        computed: {
            ...mapState({
                files: state => state.formIssuance.files,
                fileSelected: state => state.formIssuance.fileSelected,
            }),
            filesLessThanMaxTabShow () {
              return this.files.filter((file ,index) => index < this.maxTabShow)
            },
            filesMoreThanMaxTabShow () {
              return this.files.filter((file ,index) => index > this.maxTabShow - 1)
            },
        },
        methods: {
            ...mapActions({
                selectFile: "formIssuance/selectFile",
                changePositionFile: "formIssuance/changePositionFile",
                editFormIssuance: "formIssuance/editFormIssuance",
                convertExcelToImage: "formIssuance/convertExcelToImage",
                clearHomeState: "home/clearState",
                getFormIssuancePlaceholder: "formIssuance/getFormIssuancePlaceholder",
                saveInputData: "formIssuance/saveInputData",
              addLogOperation: "logOperation/addLog",
              getFormIssuancesIndex: "formIssuance/getFormIssuancesIndex",
            }),
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
                                page: this.startPage
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
            onBack: async function() {
              this.$router.push('/form-issuance');
            },
            onSave: async function() {
              this.$validator.validate().then(async valid => {
                if (valid) {
                  let data = {
                    templateId: this.files[0].id,
                    version: this.files[0].version,
                    frm_name: this.templateSetting.frm_default_name,
                    placeholder: {}
                  };
                  for (var key in this.placeholderData) {
                    data['placeholder'][this.placeholderData[key].frm_template_placeholder_name] = this.placeholderData[key].frm_template_placeholder_value;
                  }
                  this.$store.commit('home/checkCircularUserNextSend', false);
                  const circular = await this.editFormIssuance(data);
                  if (circular){
                    if(this.$store.state.home.circular){
                      this.$store.commit('application/updateCommentTitle', '');
                      this.$store.commit('application/updateCommentContent', '');
                      this.$store.commit('application/updateListUserView', []);
                    }
                    this.clearHomeState();
                    this.confirmEdit = false;
                    data.circularId = circular.id;
                    await this.saveInputData(data);

                    // 明細一覧
                    this.$router.push('/form-issuance/form-list');
                  }
                }
              });
            },
            ChangeSetIndexValue(item,e){
              if(item.colSetting && item.colSetting.type == 'number'){
                item.frm_template_placeholder_value=Utils.filterNum(e.target.value.replace(/\b(0+)/gi,""));
              }
            },
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
            if(this.files && this.files.length) {
              this.addLogOperation({action: 'frm4-form-issuance-create-display', result: 0});
                this.$store.dispatch('updateLoading', true);
                const frmIndex = await this.getFormIssuancesIndex();
                const fields = Object.values(frmIndex);
                for (const field of fields) {
                  if(field.data_type == 0){
                    this.dataCols['frm_index'+field.frm_index_number+'_col'] = {'type': 'number', 'max':12};
                  }else if(field.data_type == 1){
                    this.dataCols['frm_index'+field.frm_index_number+'_col'] = {'type': 'string', 'max':1000};
                  }else{
                    this.dataCols['frm_index'+field.frm_index_number+'_col'] = {'type': 'date'};
                  }
                }
                this.getFormIssuancePlaceholder({ templateId: this.files[0].id,frmType: this.files[0].frm_type}).then(response => {
                  this.placeholderData = response.placeholders;
                  this.templateSetting = response.templateSetting;
                  if (!this.templateSetting){
                    this.templateSetting = {'frm_default_name':''};
                  }
                  if (this.placeholderData && this.placeholderData.length > 0 && this.templateSetting){
                    var settingCols = Object.keys(this.templateSetting);
                    var settingPlaceholders = Object.values(this.templateSetting);

                    var foundFirstAdditional = false;
                    this.placeholderData.forEach((item) => {
                      item.colSetting = null;
                      var index = settingPlaceholders.indexOf(item.frm_template_placeholder_name);
                      if (index > -1 && index < settingCols.length){
                        var colName = settingCols[index];
                        if (Object.prototype.hasOwnProperty.call(this.dataCols, colName)){
                          item.colSetting = this.dataCols[colName];
                        }
                      }
                      if (!foundFirstAdditional && item.additional_flg){
                        foundFirstAdditional = true;
                        item.first_additional_flg = true;
                      }else{
                        item.false_additional_flg = true;
                      }
                    });
                  }
                });
                let data = {
                    templateId: this.files[0].id,
                    storageFileName: this.files[0].storage_file_name,
                    page: 0
                };
                let pageContent = await this.convertExcelToImage(data);
                this.$store.dispatch('updateLoading', false);
                if (pageContent){
                  this.startPage =  pageContent.startPage;
                  this.fileImage = this.fileImage.concat(pageContent.arrImage);
                  this.totalPage = pageContent.totalPagesLoaded;
                }
                this.selectFile(this.files[0]);
            } else {
              this.addLogOperation({action: 'frm4-form-issuance-create-display', result: 1});
            }
        }
    }
</script>

<style lang="scss">
.flatpickr-calendar .flatpickr-current-month {
  bottom: 0px;
}

.flatpickr-calendar .flatpickr-prev-month, .flatpickr-calendar .flatpickr-next-month {
    top: 0 !important;
}

#main-home .vs-button--text {
    font-size: inherit !important;
}
</style>
