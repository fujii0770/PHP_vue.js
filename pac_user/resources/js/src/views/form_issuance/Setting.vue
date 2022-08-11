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
          <vs-col vs-w="9"></vs-col>
          <vs-col vs-w="1" vs-type="flex" vs-justify="flex-end">
            <vs-button style="color:#000;border:1px solid #178BE5; padding: .75rem 2rem !important;" color="#fff" type="filled"  @click="onReturn">戻る</vs-button>
          </vs-col>
        </vs-row>
      </div>
      <vs-card :class="'work-content form_issuance'">
        <vs-row>
          <div style="display: flex;flex-wrap: nowrap;width: 100%;">
            <vs-col vs-type="flex" vs-w="1.5" :style="showLeftToolbar?'width: 200px;min-width: 200px;flex:0 0 auto;flex-direction: column;border: 1px solid #cdcdcd;':'width:0;overflow:hidden;'">
              <div class="preview-list-tool">
                <vs-row class="mb-3" vs-align="center" vs-type="flex" vs-justify="center">
                  <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-out-container"><vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out"><i class="fas fa-minus"></i> </vs-button></div></vs-col>
                  <vs-col vs-w="6" vs-justify="center" vs-align="center"><div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{zoom}}%</label></div></vs-col>
                  <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-in-container"><vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in"><i class="fas fa-plus"></i> </vs-button></div></vs-col>
                </vs-row>
              </div>
              <pdf-page-thumbnails
                  ref="thumbnails"
                  :thumbnails="thumbnails" :thumbnailImagesSize="thumbnailImagesSize"
                  :selectedIndex="firstVisiblePageIndex"
                  @visible-page-changed="onVisibleThumbnailChanged"
                  @click="onPageThumbnailClick">
              </pdf-page-thumbnails>
            </vs-col>
            <vs-col vs-type="flex" :vs-w="8" style="flex:1 1 auto;transition: width .2s;width:0 !important;">
              <div class="pdf-content" ref="pdfViewer" style="position: relative;">
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
                  </vs-navbar>
                </vs-col>

                <template v-if="fileSelected != null">
                  <pdf-pages v-show="!hasRequestFailedImage"
                             ref="pages"
                             :expectedPagesSize="expectedPagesSize" :pages="pages"
                             :rotateAngle="rotateAngle" :imageScale="pageImageScale"
                             :deleteFlg="fileSelected.del_flg" :deleteWatermark="fileSelected.delete_watermark"
                             @visible-page-changed="onVisiblePageChanged"
                             :stamps="stampUsed"
                             >
                  </pdf-pages>

                  <!-- 画像取得エラー発生時 -->
                  <div v-if="hasRequestFailedImage"
                       class="content on-error">
                    <p>画像を取得できませんでした。</p>
                    <vs-button icon="refresh" size="large" @click="clearImageErrors">再取得</vs-button>
                  </div>
                </template>

              </div>
            </vs-col>
            <vs-col vs-type="flex" vs-w="4">
              <div class="tools frm-template">
                <vs-tabs class="tab-parent comment-height" style="position: relative;" v-model="showTab">
                  <vs-tab label="項目設定">
                    <vs-row>
                      <vs-col vs-type="block">
                        <div style="display: flex; align-items: center;">
                          <span><label>明細名</label></span>
                          <span style="font-size: 11px; color: gray;">（画面から入力する際の初期値、およびインポート時の件名）</span>
                        </div>
                        <vs-input class="inputx w-full" label="" v-model="settingItem.frm_default_name" :maxlength="100" v-on:keyup="onChangeFormIssuance" v-on:change="onEditFormIssuance" v-validate='{ required: true, regex:/^[^\\\/\:\*?\>\"\<\>\|]*$/ }' name="form_name"/>
                        <div><span class="form-issuance-errors" v-if="errors.has('form_name')" style="color:red;">
                                      {{ errors.first("form_name") }}
                                    </span></div>
                        <div><span style="color:gray;">インポート時は、自動で作成した明細IDを後ろに追加した明細名となります。</span></div>
                      </vs-col>
                    </vs-row>
                    <vs-row >
                      <vs-col>
                        <vs-row class="form-item mb-3">
                          <vs-col vs-w="5" vs-type="flex" vs-align="center"><label>テンプレート項目</label></vs-col>
                          <vs-col vs-w="3" vs-type="flex" vs-align="center"><label>検索項目</label></vs-col>
                          <vs-col vs-w="4" vs-type="flex" vs-align="center" vs-justify="center"><label>CSV項目</label></vs-col>
                        </vs-row>
                        <div class="style-list-placeholder">
                          <vs-row class="form-item mb-3" v-for="(field, index) in placeholderData" v-bind:key="index" :index="index">
                            <vs-col vs-w="4" vs-type="flex" vs-align="center" style="word-break: break-all"><label>{{field.frm_template_placeholder_name}}</label></vs-col>
                            <vs-col vs-w="4" vs-type="flex" vs-align="center" class="pr-2">
                              <vs-select v-model="field.frm_invoice_cols" v-on:change="onEditFormIssuance">
                                <vs-select-item :text="'--'"/>
                                <vs-select-item v-for="option in optionSetting.filter(item => !placeholderDataSelecteds.includes(item.value) || field.frm_invoice_cols == item.value)"
                                                :key="option.value"
                                                :value="option.value"
                                                :text="option.text"/>
                              </vs-select>
                            </vs-col>
                            <vs-col vs-w="4" vs-type="flex" vs-align="center" class="pl-2"><vs-input class="inputx w-full" v-model="field.frm_imp_cols" :maxlength="128" v-on:change="onEditFormIssuance"/></vs-col>
                          </vs-row>

                          <vs-row style="border-top: solid 1px #178BE5;" class="pb-2 ">
                            <vs-col class="mt-2">
                              <span>非表示の項目。（印字はせずにデータとしてのみ登録可能する項目）</span>
                              <vs-row class="form-item mb-3">
                                <vs-col vs-w="4" vs-type="flex" vs-align="center" vs-justify="center"><label>項目名</label></vs-col>
                                <vs-col vs-w="4" vs-type="flex" vs-align="center" vs-justify="center"><label>検索項目</label></vs-col>
                                <vs-col vs-w="4" vs-type="flex" vs-align="center" vs-justify="center"><label>CSV項目</label></vs-col>
                              </vs-row>
                              <div v-for="placeholder in placeholderNew" :key="placeholder.id">
                                <vs-row class="form-item mb-3">
                                  <vs-col vs-w="4" vs-type="flex" vs-align="center" class="pr-2"><vs-input :for="placeholder.id" class="inputx w-full" v-model="placeholder.frm_template_placeholder_name" v-on:keyup="onChangeFormIssuance" v-on:change="onEditFormIssuance" v-validate='{ required: ((placeholder.frm_invoice_cols || placeholder.frm_imp_cols)?true:false)}' :name="'placeholder'+placeholder.id" :maxlength="128"/></vs-col>
                                  <vs-col vs-w="4" vs-type="flex" vs-align="center" class="pr-2">
                                    <vs-select v-model="placeholder.frm_invoice_cols" v-on:change="onEditFormIssuance">
                                      <vs-select-item :text="'--'"/>
                                      <vs-select-item v-for="option in optionSetting.filter(item => !placeholderDataSelecteds.includes(item.value) || placeholder.frm_invoice_cols == item.value)"
                                                      :key="option.value"
                                                      :value="option.value"
                                                      :text="option.text"/>
                                    </vs-select>
                                  </vs-col>
                                  <vs-col vs-w="4" vs-type="flex" vs-align="center" class="pl-2"><vs-input :for="placeholder.id" class="inputx w-full" v-model="placeholder.frm_imp_cols" v-on:change="onEditFormIssuance" :maxlength="128"/></vs-col>
                                </vs-row>
                                <vs-row class="form-item mb-3" v-if="errors.has('placeholder' + placeholder.id) && (placeholder.frm_invoice_cols || placeholder.frm_imp_cols)">
                                  <vs-col vs-w="4">
                                    <span class="form-issuance-errors" style="color:red;">
                                      * 必須項目です
                                    </span>
                                  </vs-col>
                                </vs-row>
                              </div>
                              <vs-row>
                                <vs-col vs-w="1" vs-type="flex" vs-justify="flex-start" vs-align="center"><div style="border-radius: 50%;" type="flat" class="zoom-in cursor-pointer" @click="addRecordPlaceholder"><i class="fas fa-plus"></i></div></vs-col>
                                <vs-col vs-w="11" vs-type="flex" vs-align="center" vs-justify="flex-start">非表示の項目を追加</vs-col>
                              </vs-row>
                            </vs-col>
                          </vs-row>
                        </div>
                      </vs-col>
                    </vs-row>
                  </vs-tab>
                  <vs-tab label="基本設定">
                    <div>
                      <vs-row>
                        <vs-col vs-type="flex" vs-w="4"><label>文書名</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7"><label>{{files[0].file_name}}</label></vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>更新日時</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7"><label>{{files[0].create_at | moment("YYYY/MM/DD HH:mm")}}</label></vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>文書種別</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7" v-if="files[0].frm_template_code != ''">
                          <label>{{editItem.frm_type == 1 ? '請求書' : '明細'}}</label>
                        </vs-col>
                        <vs-col vs-type="block" vs-w="7" v-if="files[0].frm_template_code == ''">
                          <vs-col vs-type="flex" vs-align="center">
                            <label for="frm_type_flg_0" class="mr-2">
                              <input type="radio" id="frm_type_flg_0" value="0" name="frm_type_flg" v-model="editItem.frm_type">
                              明細
                            </label>
                            <label for="frm_type_flg_1" class="mr-2">
                              <input type="radio" id="frm_type_flg_1" value="1" name="frm_type_flg" v-model="editItem.frm_type">
                              請求書
                            </label>
                          </vs-col>
                          <vs-col style="color: red; font-size: 11px;">（ 登録後の変更不可 ）</vs-col>
                        </vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>テンプレートコード</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7" v-if="files[0].frm_template_code != ''">
                          <label>{{editItem.frm_template_code}}</label>
                        </vs-col>
                        <vs-col vs-type="flex" vs-w="7" v-if="files[0].frm_template_code == ''">
                          <vs-col vs-type="block" vs-w="9">
                            <vs-input class="input w-full" @blur="handleBlur" name="frm_template_code" :maxlength="15" v-model="editItem.frm_template_code"/>
                            <vs-col style="color: red;">（ 社内で重複する値は設定できません ）</vs-col>
                            <vs-col style="color: red;">（ 必須  登録後の変更不可 ）</vs-col>
                            <vs-col style="color: gray;">（ 半角英大文字または数字を15文字まで）</vs-col>
                          </vs-col>
                        </vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>使用権限</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7"><label>{{files[0].frm_template_access_flg == 0 ? '社内' : (files[0].frm_template_access_flg == 1 ? '部署' : '登録者')}}</label></vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>編集権限</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7"><label>{{files[0].frm_template_edit_flg == 0 ? '社内' : (files[0].frm_template_edit_flg == 1 ? '部署' : '登録者')}}</label></vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>備考</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-w="7"><label>{{files[0].remarks}}</label></vs-col>
                      </vs-row>
                      <vs-row class="mt-5">
                        <vs-col vs-type="flex" vs-w="4"><label>基本設定の変更</label></vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>使用権限</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-align="center" vs-w="7">
                          <label for="frm_template_access_flg_0" class="mr-2">
                            <input type="radio" v-on:change="onChooseTemplateAccessFlg" :disabled="loginUser.department_id != departmentId" id="frm_template_access_flg_0" :value="0" v-model="editItem.frm_template_access_flg">
                            社内
                          </label>
                          <label for="frm_template_access_flg_1" class="mr-2">
                            <input type="radio" v-on:change="onChooseTemplateAccessFlg" :disabled="loginUser.department_id != departmentId" id="frm_template_access_flg_1" :value="1"  v-model="editItem.frm_template_access_flg">
                            部署
                          </label>
                          <label for="frm_template_access_flg_2" class="mr-2">
                            <input type="radio" v-on:change="onChooseTemplateAccessFlg" :disabled="files[0].mst_user_id != loginUser.id"  id="frm_template_access_flg_2" :value="2" v-model="editItem.frm_template_access_flg">
                            登録者
                          </label>
                        </vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>編集権限</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-align="center" vs-w="7">
                          <label for="frm_template_edit_flg_0" class="mr-2">
                            <input type="radio" id="frm_template_edit_flg_0" v-on:change="onEditFormIssuance" :disabled="editItem.frm_template_access_flg != 0 || loginUser.department_id != departmentId" :value="0" v-model="editItem.frm_template_edit_flg">
                            社内
                          </label>
                          <label for="frm_template_edit_flg_1" class="mr-2">
                            <input type="radio" id="frm_template_edit_flg_1" v-on:change="onEditFormIssuance" :checked="'checked'" :disabled="(editItem.frm_template_access_flg != 0 && editItem.frm_template_access_flg != 1) || loginUser.department_id != departmentId" :value="1" v-model="editItem.frm_template_edit_flg">
                            部署
                          </label>
                          <label for="frm_template_edit_flg_2" class="mr-2">
                            <input type="radio" id="frm_template_edit_flg_2" v-on:change="onEditFormIssuance" :disabled="files[0].mst_user_id != loginUser.id" :value="2" v-model="editItem.frm_template_edit_flg">
                            登録者
                          </label>
                        </vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-w="4"><label>項目入力後動作</label></vs-col>
                        <vs-col vs-type="flex" vs-w="1">:</vs-col>
                        <vs-col vs-type="flex" vs-align="center" vs-w="7">
                          <label for="frm_template_auto_ope_flg_0" class="mr-2">
                            <input type="radio" id="frm_template_auto_ope_flg_0" v-on:change="onChangeAutoOpeFlg(0)" :value="0" v-model="editItem.auto_ope_flg">
                            保存
                          </label>
                          <label for="frm_template_auto_ope_flg_1" class="mr-2">
                            <input type="radio" id="frm_template_auto_ope_flg_1" v-on:change="onChangeAutoOpeFlg(1)" :checked="'checked'" :value="1" v-model="editItem.auto_ope_flg">
                            完了保存
                          </label>
                          <label for="frm_template_auto_ope_flg_2" class="mr-2">
                            <input type="radio" id="frm_template_auto_ope_flg_2" v-on:change="onChangeAutoOpeFlg(2)" :value="2" v-model="editItem.auto_ope_flg">
                            自動回覧
                          </label>
                        </vs-col>
                      </vs-row>
                      <vs-row class="mt-3">
                        <vs-col vs-type="block" vs-align="center">
                          <div style="display: flex; align-items: center;">
                            <span><label>備考</label></span>
                            <span style="font-size: 11px; color: gray;">（ 100文字まで ）</span>
                          </div>
                          <vs-input class="input w-full" v-on:change="onEditFormIssuance" v-model="editItem.remarks" :maxlength="100"/>
                        </vs-col>
                      </vs-row>
                    </div>
                  </vs-tab>
                  <vs-tab label="自動捺印">
                    <vs-row>
                      <vs-col><label>明細作成時に自動で捺印する印鑑を捺印してください。</label></vs-col>
                    </vs-row>
                    <div style="padding: 10px 70px;">
                      <div class="stamps-processing" style="position:relative;z-index:100;border:1px solid #dcdcdc;border-radius:30px;padding-left:15px;padding-right:15px;background-color:#000000;width:max-content;margin:auto;text-align:center;user-select:none;-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;-khtml-user-select:none;">
                        <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="undoAction" :disabled="disabledUndo">
                          <i class="fas fa-undo-alt" style="color:#ffffff;margin-right: 5px;"></i><br> 元に戻す</vs-button>
                        <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="AddStampsConfirmation(currentPageNo)" :disabled="this.$store.state.home.disabledProceed">
                          <i class="fa" style="color:#ffffff;margin-right: 5px;">&#xf01e;</i><br> やり直し</vs-button>
                      </div>
                      <vs-row vs-type="flex" style="padding: 10px 10px 0">
                        <div class="break"></div>
                      </vs-row>
                      <h4 class="title">ご利用可能な印鑑</h4>
                      <vs-row vs-type="flex" vs-align="center" vs-justify="center" vs-w="12" style="padding: 10px;position: relative;">
                        <vs-col :vs-w="(rotateAngleFlg) ? 6 : 12">
                          <flat-pickr style="position: absolute;z-index: 1;width: 50px;right:10px;border:0;color:#fff" :config="configdateTimePicker" v-model="date" @on-change="onChangeStampDate" />
                          <vs-button class="square" :style="(rotateAngleFlg) ? {width: '95%'}:{width: '100%'}" data-toggle color="primary" type="filled" v-show="!(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0))">捺印日付変更</vs-button>
                        </vs-col>
                        <vs-col v-if="(rotateAngleFlg)" :vs-w="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0)) ? 12 : 6">
                          <v-popover offset="0" :auto-hide="false" :popoverClass="['change-stamp-angle']">
                            <vs-button class="square tooltip-target b3 change-angle-stamp" color="primary" type="filled" :style="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0)) ?{width: '100%'}:{width: '95%'}">かたむけて捺印</vs-button>
                            <template slot="popover">
                              <label for="rotate_angle" class="mb-3">
                                印鑑の角度
                              </label>
                              <vs-input-number  id="rotate_angle" min="0" max="360" style="width: 33%; margin-top: 15px;" v-model="rotateAngle" @change="rotateAngle =  rotateAngle ? parseInt(rotateAngle) : 0"></vs-input-number>
                              <vs-row vs-justify="flex-end">
                                <vs-button v-close-popover color="success" icon="add_circle_outline" style="padding:10px;" @click="updateRotateAngle">登録</vs-button>
                              </vs-row>
                            </template>
                          </v-popover>
                        </vs-col>
                      </vs-row>
                      <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 5px 10px; overflow: auto; height: 240px;" class="stamp-list">
                        <vs-col vs-w="6" v-for="stamp in stamps" :key="stamp.id">
                          <div class="wrap-item row-equal-height" style="margin-bottom: 15px; display: flex; justify-content: center;">
                            <div style="width: 55%;" :class="'stamp-item ' + (stampSelected && stamp.id === stampSelected.id ? 'selected': '')" @click="clickStamp(stamp.id)" >
                              <img :src="'data:image/png;base64,'+stamp.url" alt="stamp-img" v-tooltip.top-center="stamp.stamp_flg == 1 ? stamp.stamp_name : ''">
                            </div>
                          </div>
                        </vs-col>
                      </vs-row>
                    </div>
                  </vs-tab>
                  <vs-tab label="宛先設定">
                    <vx-card :hideLoading="true">
                      <div slot="no-body">

                      </div>
                      <vs-row class="border-bottom pb-4">
                        <vs-col class="mr-2 mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-w="12" vs-xs="12">
                          <h4 class="mb-3">宛先、回覧順 <span class="text-danger">*</span></h4>
                        </vs-col>
                        <vs-col vs-w="12" vs-xs="12" vs-type="flex">
                          <div class="mb-3 mr-2 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0">
                              <span @click="onDepartmentUsersSelect()" style="cursor:pointer;">
                                  <!--PAC_5-2193 回覧先設定のアドレス帳のマークの表示を大きくする-->
                                  <svg xmlns="http://www.w3.org/2000/svg" width="2.5rem" height="2.5rem" viewBox="0 0 24 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open "><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                              </span>
                          </div>
                          <vs-button class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto ipad_size"  color="danger" type="filled" v-on:click="clearSelectUsers">全て削除</vs-button>
                        </vs-col>
                      </vs-row>
                      <vs-popup class="application-page-dialog" title=""  :active.sync="confirmEdit">
                        <div class="vx-col w-full mb-base">
                          <vx-card class="h-full">
                            <vs-row>
                              <vs-col vs-w="12">
                                <vs-tabs>
                                  <vs-tab @click="showTree = true" label="アドレス帳">
                                    <vs-row>
                                    </vs-row>
                                  </vs-tab>
                                  <vs-tab @click="onFavoriteSelect(),showTree = false" label="お気に入り">
                                    <vs-row style="align-items:baseline;margin-bottom: 10px">
                                      <vs-col class="mt-4" vs-type="flex" vs-align="center" vs-w="7.5" vs-xs="6">
                                        <vx-input-group  class="w-full mb-0">
                                          <vs-input v-model="searchFavorite" />
                                          <template slot="append">
                                            <div class="append-text btn-addon">
                                              <vs-button color="primary" @click="onSearchFavorite()"><i class="fas fa-search"></i></vs-button>
                                            </div>
                                          </template>
                                        </vx-input-group>
                                      </vs-col>
                                    </vs-row>
                                    <div class="favorite_dialog">
                                      <draggable v-model="arrFavorite" @change="onSortFavorite">
                                        <transition-group>
                                          <div v-for="(itemFavorite, indexFavorite) in arrFavorite" :key="'favorites-'+indexFavorite" class="item" @click="changeTableShow(indexFavorite)" >
                                            <div class="mt-0" style="margin-top:5px;line-height:2.5rem;border-bottom: none;margin-bottom: -1px;">{{indexFavorite+1}}.{{itemFavorite[0].favorite_name ? itemFavorite[0].favorite_name:'名称未設定'}}</div>
                                            <div  class="mt-0"  style="display: flex;justify-content: space-between;border-top:none">
                                              <div style="width: 60px;">
                                                <vs-button class="vs-button_dialog square action action_dialog" color="primary" @click="onApplyFavorite(itemFavorite)">追加</vs-button>
                                              </div>
                                              <ul class="like_addrs"  v-if="tableshow.hasOwnProperty(indexFavorite)">
                                                <li v-for="(uval,uindex)  in itemFavorite" :key="uindex">
                                                  <div class="like_addrs_content"><span>{{uindex + 1}}:</span>{{uval.name}} [{{uval.email}}]</div>
                                                  <div class="triangle"></div>
                                                </li>
                                              </ul>
                                              <div style="flex-grow: 1;" vs-type="flex"  v-if="!tableshow.hasOwnProperty(indexFavorite)">
                                                <template v-for="(itemUser, index) in itemFavorite">
                                                  <template v-if="itemFavorite.length<=6 || (itemFavorite.length > 6 && index<5) ">
                                                    <div class="name" :key="'favorite-'+index+'-name'">{{ itemUser.name.split(" ").map((w) => w[0]).join(" ") }}</div>
                                                    <div class="toright" :key="'favorite-'+index+'-icon'" v-if="index<itemFavorite.length-1"><i class="fas fa-caret-right"></i></div>
                                                  </template>
                                                </template>
                                                <template v-if="itemFavorite.length>6">
                                                  <div class="name">...</div>
                                                </template>
                                              </div>
                                              <div style="width: 30px; text-align: right; line-height: 12px;">
                                                <a href="#" class="text-danger" @click="onRemoveFavorite(itemFavorite[0].favorite_no)"><i class="fas fa-times"></i></a>
                                              </div>
                                            </div>
                                          </div>
                                        </transition-group>
                                      </draggable>
                                    </div>
                                  </vs-tab>
                                </vs-tabs>
                              </vs-col>
                              <vs-col vs-w="12">
                                <ContactTree ref="tree" v-show="showTree" :opened="confirmEdit" :treeData="treeData" @onTreeAddToStepClick="onTreeAddToStepClick" @onNodeClick="showModalEditContacts"/>
                              </vs-col>
                            </vs-row>
                            <div slot="no-body-bottom">
                            </div>
                          </vx-card>
                        </div>
                      </vs-popup>
                      <div class="mail-steps">
                        <div class="mail-list">
                          <vs-row v-if="selectUsers.length > 0" vs-type="flex" :class="['group applicant', (selectUsersDisplay && (selectUsersDisplay.length > 0) && selectUsersDisplay[0].length > 2) ? 'return-flg': '', 'not-return']">
                            <vs-col vs-w="12">
                              <vs-row vs-type="flex" vs-justify="center">
                                <vs-col vs-w="12" class="item me" >
                                  <div>{{selectUsers[0].name}}</div>
                                  <div>【{{selectUsers[0].email}}】</div>
                                  <span v-if="selectUsers.length === 1" class="final"> 最終</span>
                                  <a href="#" class="currentUser-flg"> <i class="far fa-flag"></i></a>
                                </vs-col>
                                <vs-col vs-w="12">
                                  <vs-checkbox :value="isNotReturnCircular" v-if="!onlyInternalUser && selectUsersDisplay[0].length > 1" @click="toggleReturnCircular(selectUsers[0].id)" style="">最終承認者から直接社外に送る</vs-checkbox>
                                </vs-col>
                              </vs-row>
                            </vs-col>
                            <vs-col vs-w="12" class="child1st-block full-width" >
                              <div class="full-width range-0">
                                <template v-if="!this.isTemplateCircular">
                                  <vs-row vs-justify="center" vs-type="flex" v-for="(user, index) in setSelectUsersDisplay(selectUsersDisplay[0])" :key="user.email + index + user.changeTimes"
                                          :class="[(index === selectUsersDisplay[0].length-1 && index >1)  ? 'last-row': '']">
                                    <vs-col
                                            vs-w="12"
                                            vs-type="flex"
                                            vs-align="flex-start"
                                            :class="['item child-order item-draggable', index === 1 ? 'first' : '', (index === selectUsersDisplay[0].length-1 && index >1)  ? 'last': '', (index === 1 && selectUsersDisplay[0].length > 1) ? 'return-flg': '',
                                                      (user.email === selectUsers[0].email) ? 'me': '']" >
                                      <div class="dropable-item h-full w-full">
                                        <div>{{index}} - {{user.name || '社員'}}</div>
                                        <div>【{{user.email}}】</div>
                                        <span v-if="selectUsersDisplay.length === 1 && selectUsersDisplay[0].length === (index + 1)" class="final"> 最終</span>
                                        <a style="position: absolute;right: 6px;top: 50%;transform: translateY(-50%);" href.prevent v-on:click="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                      </div>
                                    </vs-col>
                                  </vs-row>
                                </template>
                                <!-- template route users start -->
                                <template v-else>
                                  <vs-row id="template_route" vs-justify="center" vs-type="flex" v-for="(userRoute, index) in selectTemplateUsersDisplay" 
                                          :class="[(index === selectTemplateUsersDisplay.length-1)  ? 'last-row': '']" :key="index">
                                    <vs-col
                                            vs-w="12"
                                            vs-type="flex"
                                            vs-align="flex-start"
                                            :class="['item child-order', index === 0 ? 'first' : '', (index > 0 && index === selectTemplateUsersDisplay.length-1)  ? 'last': '',  (index === 0 && selectTemplateUsersDisplay.length > 1) ? 'return-flg': '']" >
                                      <div class="h-full w-full">
                                        <div>{{userRoute[0].user_routes_name}}</div>
                                        <template v-for="(user, itemIndex) in userRoute">
                                          <div :key="itemIndex">{{user.name || '社員'}} 【{{user.email}}】</div>
                                        </template>
                                        <span v-if="selectTemplateUsersDisplay.length === (index + 1)" class="final"> 最終</span>
                                        <a style="position: absolute;right: 6px;top: 50%;transform: translateY(-50%);" href.prevent v-on:click="clearSelectUsers" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                      </div>
                                    </vs-col>
                                  </vs-row>
                                </template>
                                <!-- template route users end -->
                                <vs-row vs-justify="center" vs-type="flex" v-if="!onlyInternalUser && selectUsersDisplay[0].length > 1 && !isNotReturnCircular">
                                  <vs-col vs-w="12"
                                          vs-type="flex"
                                          vs-align="flex-start"
                                          :class="['item child-order item-draggable me']">
                                    <div>{{selectUsersDisplay[0].length}} - {{selectUsers[0].name}}</div>
                                    <div>【{{selectUsers[0].email}}】</div>
                                  </vs-col>
                                </vs-row>
                                <vs-row vs-type="flex" vs-justify="center" class="row-drop-area">
                                  <vs-col
                                          vs-w="12"
                                          vs-type="flex"
                                          vs-align="flex-start"
                                          class="drop-area hidden item item-dropable" >
                                    <div class="dropable-item h-full w-full">
                                      <div>&nbsp;</div>
                                    </div>
                                  </vs-col>
                                </vs-row>
                              </div>
                            </vs-col>
                          </vs-row>
                          <div class='full-width range-1'>
                            <vs-row
                                    v-for="(group, idx) in (setSelectUsersDisplay(selectUsersDisplay))"
                                    vs-type="flex"
                                    vs-align="space-around"
                                    class='group parent-block'
                                    :key="group+''+idx"
                                    >
                              <vs-col vs-w="6" v-if="selectUsersDisplay && selectUsersDisplay[idx + 1]">
                                <vs-row v-if="!Array.isArray(selectUsersDisplay[idx + 1])"
                                        vs-type="flex"
                                        vs-justify="space-around"
                                        :key="selectUsersDisplay[idx + 1].email + '0'"  >
                                  <vs-col vs-w="12"
                                          :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                          vs-type="flex"
                                          vs-align="flex-start" >
                                    <div class="dropable-item h-full w-full">
                                      <div>{{selectUsersDisplay[idx + 1].mst_company_id ? selectUsersDisplay[idx + 1].name : '社外'}} - {{selectUsersDisplay[idx + 1].mst_company_name || '社外'}}</div>
                                      <div>【{{selectUsersDisplay[idx + 1].email}}】</div>
                                      <div v-if="selectUsersDisplay.length === (idx + 2) " class="final"> 最終</div>
                                      <a style="position: absolute;right: 6px;top: 50%;transform: translateY(-50%);" href.prevent v-on:click="onRemoveSelectUserClick(selectUsersDisplay[idx + 1].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                    </div>
                                  </vs-col>
                                </vs-row>
                                <vs-row v-if="Array.isArray(selectUsersDisplay[idx + 1]) &&  selectUsersDisplay[idx + 1].length > 0 && selectUsersDisplay[idx + 1][0]"
                                        vs-type="flex"
                                        vs-justify="space-around"
                                        :key="selectUsersDisplay[idx + 1][0].email + '0'" >
                                  <vs-col vs-w="12"
                                          :class="['item item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green')]"
                                          vs-type="flex"
                                          vs-align="flex-start" >
                                    <div class="dropable-item h-full w-full">
                                      <div>{{selectUsersDisplay[idx + 1][0].name || '社外'}} - {{selectUsersDisplay[idx + 1][0].mst_company_name || '社外'}}</div>
                                      <div>【{{selectUsersDisplay[idx + 1][0].email}}】</div>
                                      <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === 1" class="final"> 最終</span>
                                      <a style="position: absolute;right: 6px;top: 50%;transform: translateY(-50%);" href.prevent v-on:click="onRemoveSelectUserClick(selectUsersDisplay[idx + 1][0].id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                    </div>
                                  </vs-col>
                                </vs-row>
                              </vs-col>
                              <vs-col vs-w="6"  class="child-block full-width"  v-if="selectUsersDisplay[idx + 1]" >
                                <div class="full-width" >
                                  <vs-row
                                          v-for="(user, index) in setShowUserDisplayIsSelect(selectUsersDisplay[idx + 1])"
                                          vs-type="flex"
                                          vs-justify="center"
                                          :key="user.email + index"
                                          :class="['child-range-'+(idx + 1), index + 1 === 1 ? 'start-item' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'end-item': '', (index + 1 ===0) ? 'blank-item': '', (selectUsersDisplay[idx + 1].length < 3 )?'only-item':'']">
                                    <vs-col vs-w="12"
                                            v-if="(index + 1) > 0 && selectUsersDisplay[idx + 1][index + 1]"
                                            vs-type="flex"
                                            vs-align="flex-start"
                                            :class="['item child-order item-draggable', (idx + 1) % 3 == 1 ? 'bg-orange': ((idx + 1) % 3 == 2 ? 'bg-pinkle' : 'bg-green'), index + 1 === 1 ? 'first' : '', (index + 1 === selectUsersDisplay[idx + 1].length-1 && index + 1 >1) ? 'last': '', (index + 1 === 1 && selectUsersDisplay[idx + 1].length > 1) ? 'return-flg': '']" >
                                      <div class="dropable-item h-full w-full">
                                        <div>{{user.name || '社外'}} - {{user.mst_company_name || '社外'}}</div>
                                        <div>【{{user.email}}】</div>
                                        <span v-if="selectUsersDisplay.length === (idx + 2) && selectUsersDisplay[idx + 1].length === (index + 2)" class="final"> 最終</span>
                                        <a style="position: absolute;right: 6px;top: 50%;transform: translateY(-50%);" href.prevent v-on:click="onRemoveSelectUserClick(user.id)" class="text-danger remove-flag"><i class="fas fa-times"></i></a>
                                      </div>
                                    </vs-col>
                                  </vs-row>
                                  <vs-row class="row-drop-area"
                                          vs-type="flex"
                                          vs-justify="center" >
                                    <vs-col vs-w="12"
                                            vs-type="flex"
                                            vs-align="flex-start"
                                            class="drop-area hidden item item-dropable" >
                                      <div class="dropable-item h-full w-full">
                                        <div>&nbsp;</div>
                                      </div>
                                    </vs-col>

                                  </vs-row>
                                </div>
                              </vs-col>
                            </vs-row>
                            <vs-row vs-type="flex"
                                    vs-align="space-around"
                                    class='group'>
                              <vs-col vs-w="6">
                                <vs-row vs-type="flex" vs-justify="space-around">
                                  <vs-col vs-w="12"
                                          class="drop-area item item-dropable hidden"
                                          vs-type="flex"
                                          vs-align="flex-start">
                                    <div class="dropable-item h-full w-full">
                                      <div>&nbsp;</div>
                                    </div>
                                  </vs-col>
                                </vs-row>
                              </vs-col>
                            </vs-row>
                          </div>
                        </div>
                        <div slot="no-body">
                        </div><br>
                        <div class="mail-form" v-if="infoCheck['addressbook_only_flag'] == 0">
                          <form v-if="!checkShowAddress">
                            <vs-row class="mt-8">
                              <div class="vx-col mb-3" style="width: 100%;">
                                <vue-suggestion :items="userSuggestions"
                                                v-model="userSuggestModel"
                                                placeholder="回覧先の名前"
                                                :setLabel="setUsernameSuggestLabel"
                                                :itemTemplate="usernameTemplate"
                                                :minLen="1"
                                                :disabled="suggestDisabled"
                                                @onInputChange="getUsersSuggestionList"
                                                @onItemSelected="onSuggestSelect"
                                                @focus="getFocusUsersSuggestionList">
                                </vue-suggestion>
                              </div>
                              <div class="vx-col" style="width: 100%;">
                                <vue-suggestion :items="emailSuggestions"
                                                v-model="emailSuggestModel"
                                                placeholder="回覧先のメールアドレス"
                                                :setLabel="setEmailSuggestLabel"
                                                :itemTemplate="emailTemplate"
                                                :minLen="1"
                                                @onInputChange="getEmailsSuggestionList"
                                                @onItemSelected="onSuggestSelect"
                                                @focus="getFocusEmailsSuggestionList">
                                </vue-suggestion>
                                <span class="text-danger text-sm" v-show="emailSuggestValidateMsg">{{ emailSuggestValidateMsg }}</span>
                              </div>

                            </vs-row>
                            <vs-row class="mt-4">
                              <vs-col vs-type="flex" vs-w="12" vs-xs="12" vs-justify="flex-end" vs-align="flex-end">
                                <vs-button @click.prevent="submitMailStepForm" class="square mr-0"  color="primary" type="filled" :disabled="isTemplateCircular"> 追加</vs-button>
                              </vs-col>
                            </vs-row>
                          </form>
                        </div>
                      </div>
                      <div slot="no-body">

                      </div><br>
                      <vs-row class="border-bottom pb-4">
                        <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12">
                          <h4>閲覧ユーザー設定</h4>
                        </vs-col>
                        <vs-col class="mb-3 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0" vs-type="flex" vs-align="center" vs-w="6" vs-xs="12" style="padding-left: 47%;">
                              <span @click="onAroundArrow">
                                <vs-icon id="arrow" class="mt-5 around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                              </span>
                        </vs-col>
                      </vs-row>
                      <div class="mail-steps" v-show="searchAreaFlg">
                        <div class="mail-view-list">
                          <draggable v-model="selectUserView" >
                            <vs-row vs-w="12" class="item me item-user-view" vs-type="flex" v-for="(user, index) in selectUserView" v-bind:key="user.email + index" :index="index">
                              <vs-col vs-w="10">
                                <span>{{user.name}}【{{user.email}}】</span>
                              </vs-col>
                              <vs-col vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                <a href.prevent v-on:click="deleteUserView(user.email)" class="text-danger"><i class="fas fa-times"></i></a>
                              </vs-col>
                            </vs-row>
                          </draggable>
                        </div>
                        <div class="mail-form">
                          <form>
                            <vs-row class="mt-8">
                              <div class="vx-col mb-3" style="width: 100%;">
                                <vue-suggestion :items="userViewSuggestions"
                                                v-model="userViewSuggestModel"
                                                placeholder="閲覧ユーザーの名前"
                                                :setLabel="setUserViewnameSuggestLabel"
                                                :itemTemplate="usernameTemplate"
                                                :minLen="1"
                                                :disabled="suggestViewDisabled"
                                                @onInputChange="getUsersViewSuggestionList"
                                                @onItemSelected="onSuggestViewSelect"
                                                @focus="getFocusUsersViewSuggestionList">
                                </vue-suggestion>
                              </div>
                              <div class="vx-col" style="width: 100%;">
                                <vue-suggestion :items="emailViewSuggestions"
                                                v-model="emailViewSuggestModel"
                                                placeholder="閲覧先のメールアドレス"
                                                :setLabel="setEmailViewSuggestLabel"
                                                :itemTemplate="emailTemplate"
                                                :minLen="1"
                                                @onInputChange="getEmailsViewSuggestionList"
                                                @onItemSelected="onSuggestViewSelect"
                                                @focus="getFocusEmailsViewSuggestionList">
                                </vue-suggestion>
                                <span class="text-danger text-sm" v-show="emailViewSuggestValidateMsg">{{ emailViewSuggestValidateMsg }}</span>
                              </div>

                            </vs-row>
                            <vs-row class="mt-4">
                              <vs-col vs-type="flex" vs-w="12" vs-xs="12" vs-justify="flex-end" vs-align="flex-end">
                                <vs-button @click.prevent="addUserView" class="square mr-0"  color="primary" type="filled" > 追加</vs-button>
                              </vs-col>
                            </vs-row>
                          </form>
                        </div>
                      </div>
                    </vx-card>
                  </vs-tab>
                  <vs-tab label="件名・保護">
                    <vs-row>
                      <vs-col class="mb-6"><label>件名・メッセージ</label></vs-col>
                      <div style="width: 100%;">
                        <vs-row class="mb-4">
                          <vs-input class="inputx w-full" placeholder="件名をつけて送信できます。" v-validate="'max:50|emoji'" name="subject"  v-model="editItem.title" @change="changeCommentTitle($event)" />
                          <span class="text-danger text-sm" v-show="errors.has('subject')">{{ errors.first('subject') }}</span>
                        </vs-row>
                        <vs-row style="margin-bottom: 16px">
                          <vs-textarea placeholder="コメントをつけて送信できます。" rows="4" v-model="editItem.message" v-validate="'max:500'" name="content" @change="changeCommentContent" style="margin-bottom: 0" />
                          <span class="text-danger text-sm" v-show="errors.has('content')">{{ errors.first('content') }}</span>
                        </vs-row>
                        <vs-row class="mb-6">
                          <!--PAC_5-1413 欄外クリック時にボックスが閉じない　vuesax側のバグの為、vue selectに切り替え-->
                          <div class="w-full">
                            <v-select :options="emailTemplateOptions" :clearable="false" :searchable ="false" :value="selectedComment" @input="onChangeEmailTemplate" />
                          </div>
                        </vs-row>
                        <vs-row>※メッセージは次の回覧者への送信メールに記載されます。<br/>
                          　また、プレビュー画面「コメント」タブの「社内宛」に表示されます。</vs-row>
                      </div>
                      <vs-col class="mb-6 mt-6"><label>保護設定</label></vs-col>
                      <div style="width: 100%;">
                        <vs-row>
                          <vs-checkbox class="mb-2 mt-3" :value="editItem.address_change_flg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="addressChangeFlg">宛先、回覧順の変更を許可する</vs-checkbox>
                        </vs-row>
                        <vs-row v-if="settingLimit?settingLimit.text_append_flg==1:false">
                          <vs-checkbox class="mb-2 mt-3" :value="editItem.text_append_flg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="textAppendFlg">テキスト追加を許可する</vs-checkbox>
                        </vs-row>
                        <vs-row v-if ="showEmailThumbnailOption">
                          <vs-checkbox class="mb-2 mt-3" :value="editItem.hide_thumbnail_flg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="hideThumbnailFlg">メール内の文書のサムネイル を表示する</vs-checkbox>
                        </vs-row>
                        <vs-row>
                          <vs-checkbox class="mb-2 mt-3" :value="editItem.require_print" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="requirePrint">捺印設定</vs-checkbox>
                        </vs-row>
                        <vs-row>
                          <vs-col vs-w="12" vs-xs="12" class="mb-2" vs-align="center"><vs-checkbox class="mt-3" :value="accessCodeFlg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="changeAccessCodeFlg">アクセスコードで保護する（社内用）</vs-checkbox></vs-col>
                          <vs-col vs-w="12" vs-xs="12" vs-align="center">
                            <vx-input-group v-if="accessCodeFlg"  class="w-full mb-0">
                              <vs-input v-model="accessCode" maxlength="6" v-on:change="onChangeAccessCode"/>
                              <template slot="append">
                                <div class="append-text btn-addon">
                                  <vs-button color="primary" v-on:click="generateAccessCode"><i class="fas fa-sync-alt"></i></vs-button>
                                </div>
                              </template>
                            </vx-input-group>
                          </vs-col>
                        </vs-row>
                        <vs-row>
                          <vs-col vs-w="12" vs-xs="12" class="mb-2" vs-align="center"><vs-checkbox class="mt-3" :value="outsideAccessCodeFlg" :disabled="!protectionSetting.protection_setting_change_flg" v-on:click="changeOutSideAccessCodeFlg">アクセスコードで保護する（社外用）</vs-checkbox></vs-col>
                          <vs-col vs-w="12" vs-xs="12" vs-align="center">
                            <vx-input-group v-if="outsideAccessCodeFlg"  class="w-full mb-0">
                              <vs-input v-model="outsideAccessCode" maxlength="6"  v-on:change="onChangeOutSideAccessCode"/>
                              <template slot="append">
                                <div class="append-text btn-addon">
                                  <vs-button color="primary" v-on:click="generateAccessCodeOutside"><i class="fas fa-sync-alt"></i></vs-button>
                                </div>
                              </template>
                            </vx-input-group>
                          </vs-col>
                        </vs-row>
                      </div>
                      <vs-col class="mb-6 mt-6"><label>再通知設定</label></vs-col>
                      <div>
                      <vs-row class="mb-4 mt-6" style="align-items: center;">
                        <vs-col vs-w="10" vs-xs="10">
                          <vx-input-group  class="w-full mb-0">
                            <flat-pickr ref="calendar" class="w-full reNotification" :config="configdateTimePicker" v-model="editItem.re_notification_day" ></flat-pickr>
                            <template slot="append">
                              <div class="append-text btn-addon">
                                <vs-button data-toggle color="primary" v-on:click="openCalendarClick"><i class="fas fa-calendar-alt"></i></vs-button>
                              </div>
                            </template>
                          </vx-input-group>

                        </vs-col>
                        <vs-col vs-w="2" vs-xs="2">
                          <vs-button v-tooltip.top-center="{
                                html: true,
                                content: '設定日にまだ回覧が終了していない場合、<br/>その時点で回覧中の宛先にメールで通知します。<br/>設定日以降も回覧終了になるまで通知します。'
                            }" radius color="danger" type="filled" style="width: 37px">
                            <i class="fas fa-info"></i>
                          </vs-button>
                        </vs-col>
                      </vs-row>
                      </div>
                    </vs-row>
                  </vs-tab>
                  <vs-row style="margin-bottom: 5px !important;">
                    <vs-col>
                      <div class="footer">
                        <vs-button class="save-button" @click="onSave" color="primary" type="filled" :disabled="disableButtonSave">登録</vs-button>
                      </div>
                    </vs-col>
                    <vs-col vs-type="flex" vs-align="center" vs-justify="flex-start" style="color: black;">この明細テンプレートを使用する場合は「明細テンプレートの詳細」にて有効化してください</vs-col>
                  </vs-row>
                </vs-tabs>
              </div>
            </vs-col>
          </div>
        </vs-row>
      </vs-card>
        <vs-popup :active.sync="confirmDelete" title="アドレス帳から削除します。よろしいですか？">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDeleteContact" color="warning">はい</vs-button>
                    <vs-button @click="confirmDelete = false" color="dark" type="border">いいえ</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup title="アドレス帳編集" :active.sync="showPopupEditContacts">
            <div class="mt-5">
                <form>
                    <vs-row>
                        <vs-col vs-w="3" class="text-left pr-3 pt-3">グループ</vs-col>
                        <vs-col vs-type="" vs-w="9">
                            <vs-input placeholder="グループ" v-model="editContact.group_name" class="w-full" />
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="3" class="text-left pr-3 pt-3">名前<span class="ml-1 text-red">*</span></vs-col>
                        <vs-col vs-type="" vs-w="9">
                            <vs-input placeholder="名前" v-model="editContact.name" name="name" class="w-full" v-on:change="onChangeContactName"/>
                            <span class="text-danger text-sm" v-show="editContactNameRequireMsg">{{ editContactNameRequireMsg }}</span>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="3" class="text-left pr-3 pt-3">メールアドレス<span class="ml-1 text-red">*</span></vs-col>
                        <vs-col vs-type="" vs-w="9">
                            <vs-input type="email" required name="email" placeholder="メールアドレス" v-model="editContact.email" class="w-full" v-on:change="onChangeContactEmail"/>
                            <span class="text-danger text-sm" v-show="editContactEmailRequireMsg">{{ editContactEmailRequireMsg }}</span>
                            <span class="text-danger text-sm" v-show="editContactEmailValidateMsg">{{ editContactEmailValidateMsg }}</span>
                        </vs-col>
                    </vs-row>
                </form>
            </div>

            <vs-row class="mt-5">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12" style="display:block;text-align:center">
                    <vs-button @click="confirmDelete = true" color="primary" type="border" v-if="editContact.id">削除する</vs-button>
                    <vs-button @click="onUpdateContact()" color="primary" v-if="editContact.id">変更する</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from 'vue-infinite-loading';
import PdfPages from "../../components/form_issuance/PdfPages";
import PdfPageThumbnails from "../../components/form_issuance/PdfPageThumbnails";

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import { dragscroll } from 'vue-dragscroll'
import Utils from '../../utils/utils';
import { getPageUtil } from '../../utils/pagepreview';

import config from "../../app.config";

import draggable from 'vuedraggable'
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import { Validator } from 'vee-validate';

import Axios from "axios";

import VueSuggestion from 'vue-suggestion'


import usernameTemplate from '../application/username-suggest-template.vue';
import emailTemplate from '../application/email-suggest-template.vue';
import ContactTree from '../../components/contacts/ContactTree';

const dict = {
  custom: {
    service_name: {
      required: '* 必須項目です',
    },
    form_name: {
      required: '* 必須項目です',
      regex: '* ファイル名に使用できない次の文字は使用不可\\\/:*?>"<>|',
    },
    subject: {
      max: "50文字以上は入力できません。",
      emoji:'絵文字は入力できません'
    },
    content: {
      max: "500文字以上は入力できません。"
    }
  }
};
Validator.localize('ja', dict);
const PPI = 150;
const reg=/[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF][\u200D|\uFE0F]|[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF]|[0-9|*|#]\uFE0F\u20E3|[0-9|#]\u20E3|[\u203C-\u3299]\uFE0F\u200D|[\u203C-\u3299]\uFE0F|[\u2122-\u2B55]|\u303D|[\\A9|\\AE]\u3030|\\uA9|\\uAE|\u3030/ig
Validator.extend('emoji', {
  messages: {
    ja:() => '絵文字は入力できません',
  },
  validate: value => {
    reg.lastIndex = 0
    return  !reg.test(value)
  }
});
export default {
  components: {
    InfiniteLoading,
    PdfPages,
    PdfPageThumbnails,
    flatPickr,
    draggable,
    swiper,
    VueSuggestion,
    ContactTree,
    swiperSlide
  },
  directives: {
    dragscroll
  },
  data() {
    return {
      disableButtonSave: false,
      departmentId: '',
      settingItem: {
        frm_default_name: '',
        to_email_name_imp: '',
        to_email_addr_imp: ''
      },
      placeholderData: [],
      templateSetting: null,
      settingItemOld: {
        frm_default_name: '',
        to_email_name_imp: '',
        to_email_addr_imp: ''
      },
      editItemOld: {},
      placeholderDataOld: [],
      placeholderOld: [],
      show: true,
      editItem: {},
      counter: 0,
      placeholderNew: [],
      currentPageNo: 1,
      pages: [],
      thumbnails: [],
      maxTabShow: 5,
      rotateAngle: 0,
      tabSelected: 99999, // plus button
      date: null,
      configdateTimePicker: {
        locale: Japanese,
        wrap: true
      },
      file_name: '', // ファイル名
      zoom: 100,

      disabledUndo: true,
      rotateAngleFlg: JSON.parse(getLS('user')).rotate_angle_flg,
      userInfo:null,
      window: {
        width: 0,
        height: 0
      },
      showLeftToolbar: false,
      viewerWidth: 1,
      thumbnailViewerWidth: 0,
      visiblePageRange: [-1, -1],
      visibleThumbnailRange: [-1, -1],


      usernameTemplate: usernameTemplate,
      emailTemplate: emailTemplate,
      accessCodeFlg: false,
      outsideAccessCodeFlg: false,
      outsideAccessCodeShowFlg: false,
      accessCode: '',
      outsideAccessCode: '',
      allowChangeDestinationFlg: false,
      popupSelectAccountActive: false,
      usernameSelect: '',
      emailSelect: '',
      emailSelects: '',
      currentEmailConfirm: 0,
      mapUserEmail:[],
      mapEnvEmail:[],
      mapEnvCompany:[],
      confirmEmails:[],
      mapConfirmEmails:[],
      userSuggestSelect: null,
      suggestDisabled: false,
      selected: [],

      // Data Sidebar
      info: {},
      infoCheck: {},
      emailTemplateOptions: [],
      arrFavorite:[],
      treeLoaded: 0,
      treeData: [],
      usersChange: false,
      resultCheckEmailExisting: [],
      userSuggestions: [],
      emailSuggestions: [],
      userSuggestModel: '',
      emailSuggestModel: '',
      emailSuggestValidateMsg: '',
      showEmailThumbnailOption: true,
      checkShowAddress: true,
      userViewSuggestions: [],
      userViewSuggestModel: '',
      emailViewSuggestions: [],
      emailViewSuggestModel: '',
      userViewnameSelect: '',
      emailViewSelect: '',
      suggestViewDisabled: false,
      userViewSuggestSelect: null,
      emailViewSuggestValidateMsg: '',
      isNotReturnCircular: false,
      selectUsersDisplay: [],
      onlyInternalUser: true,
      searchAreaFlg:false,
      confirmEdit: false,
      protectionSetting: {destination_change_flg: 0, access_code_protection:0, enable_email_thumbnail:0, text_append_flg:0, require_print:0},
      showPopupEditContacts: false,
      editContact:{},
      confirmDelete:false,
      confirmDuplicateEmail: false,
      listCheckEmailContact:[],
      showTree: true,
      commentTitle: '',
      commentContent: '',
      changeTimes:0,
      selectedComment: '',
      maxViewer:1, //閲覧人数上限値
      showPopupErrorTemplate: false, // 宛先、回覧順に合議でないものが存在するため、合議を適用できません。Popup表示用
      showPopupErrorTemplateMessage: "", // Popup表示用 メッセージ
      selectTemplateUsersDisplay: [], // 宛先表示
      isTemplateCircular: false, // 現在の回覧は合議ですか？
      tableshow:{},
      searchFavorite:'',
      settingLimit:{},
      checkUserInputTimeout: null,
      checkEmailInputTimeout: null,
      userSelectedFlg: false,
      emailSelectedFlg: false,
      userViewSelectedFlg: false,
      emailViewSelectedFlg: false,
      savedCircularUsers: [],
      savedViewingUsers: [],
      editContactNameRequireMsg: '',
      editContactEmailRequireMsg: '',
      editContactEmailValidateMsg: '',
      showTab: 0,
      stampDisplays: [],
      stampUsed: [],
    }
  },
  computed: {
    ...mapState({
      files: state => state.formIssuance.files,
      optionSetting: state => state.formIssuance.optionSetting || [],
      fileSelected: state => state.home.fileSelected,
      stampSelected: state => state.home.stampSelected,
      deviceType: state => state.home.deviceType,
      expectedPagesSize: state => {
        // ページ画像
        // 推定サイズ: 実際の画像と誤差あることあり (±1px程度)
        const pagesInfo = state.home.fileSelected?.pagesInfo ?? [];
        return pagesInfo.map(pageInfo => ({
          width: pageInfo.width_pt / 72 * PPI,
          height: pageInfo.height_pt / 72 * PPI,
        }));
      },
      filesLessThanMaxTabShow () {
        return this.files.slice(0,this.maxTabShow)
      },
    }),
    placeholderDataSelecteds() {
      if(!this.placeholderData) return []
      return [...this.placeholderData, ...this.placeholderNew].filter(item => item.frm_invoice_cols).map(item => item.frm_invoice_cols)
    },
    stamps: {
      get() {return this.stampDisplays;},

      set(value) {
          this.stampDisplays = value.map((item,index) => {
              item.display_no = index + 1;
              return item;
          });

          this.updateStampDisplays(value)
      }
    },
    loginUser: {
      get() {
        if(this.$store.state.home.usingPublicHash) return {};
        return JSON.parse(getLS('user'));
      }
    },
    pageImageScale() {
      const inchesToFit = 8.27; // 8.27 = A4 短辺
      const margin = 50; // 少し余裕をもつ
      return this.viewerWidth / (inchesToFit * PPI + margin);
    },
    // サムネイル表示用
    largestPageImageWidth() {
      // expectedPagesSize 誤差あることがあるが無視
      return this.expectedPagesSize.reduce((max, current) => Math.max(max, current.width), 0);
    },
    thumbnailImageScale() {
      return this.thumbnailViewerWidth / this.largestPageImageWidth;
    },
    thumbnailImagesSize() {
      // expectedPagesSize 誤差あることがあるが無視
      return this.expectedPagesSize.map(x => ({
        width: x.width * this.thumbnailImageScale,
        height: x.height * this.thumbnailImageScale,
      }));
    },
    firstVisiblePageIndex() {
      const [start, ] = this.visiblePageRange;
      return start;
    },
    // 画像取得用
    hasRequestFailedImage() {
      return getPageUtil.hasRequestFailedImage(this.pages, this.thumbnails);
    },
    nextRequestImage() {
      return getPageUtil.nextRequestImage(this.pages, this.thumbnails, this.hasRequestFailedImage,
          this.showLeftToolbar, this.visiblePageRange, this.visibleThumbnailRange);
    },
    mobilePages() {
      return getPageUtil.mobilePages(this.pages);
    },
    selectUsers: {
      get() {return  this.$store.state.formIssuance.frmTemplate ? this.$store.state.formIssuance.frmTemplate.users : []},
      set(value) {this.updateCircularUsers(value)}
    },
    selectUserView:{
      get() {
        return this.$store.state.formIssuance.selectUserView
      },
    },
  },
  methods: {
    ...mapActions({
      selectFile: "formIssuance/selectFile",
      getFormIssuancePlaceholder: "formIssuance/getFormIssuancePlaceholder",
      getTemplateDepartment: "formIssuance/getTemplateDepartment",
      saveSettingFormIssuance: "formIssuance/saveSettingFormIssuance",
      clearState: "home/clearState",
      clearFormIssuanceState: "formIssuance/clearState",
      updateCurrentFileZoom: "home/updateCurrentFileZoom",
      getCompanyStamps: "home/getCompanyStamps",
      selectStamp: "home/selectStamp",
      undoAction: "home/undoAction",
      getMyInfo: "user/getMyInfo",
      updateMyInfo: "user/updateMyInfo",
      checkDeviceType: "home/checkDeviceType",
      loadFormIssuances: "formIssuance/loadFormIssuances",
      getPage: "formIssuance/getFormIssuancesPage",
      addLogOperation: "logOperation/addLog",


      getDepartmentUsers: "formIssuance/getDepartmentUsers",
      getListContact: "contacts/getListContact",
      addCircularUsers: "formIssuance/addCircularUsers",
      removeCircularUser: "formIssuance/removeCircularUser",
      clearCircularUsers: "formIssuance/clearCircularUsers",
      updateCircularUsers: "formIssuance/updateCircularUsers",
      getListFavorite     : "favorite/getList",
      removeFavorite      : "favorite/remove",
      sortFavorite        : "favorite/updateSort",
      getInfoCheck: "user/getInfoCheck",
      getProtection: "setting/getProtection",
      getContact: "contacts/getContact",
      updateContact: "contacts/updateContact",
      deleteContact: "contacts/deleteContact",
      getLimit: "setting/getLimit",
      getSavedCircularUsers: "formIssuance/getSavedCircularUsers",
      getSavedViewingUsers: "formIssuance/getSavedViewingUsers",
      addViewingUser: "formIssuance/addViewingUser",
      removeViewingUser: "formIssuance/removeViewingUser",
      getFormIssuancesIndex: "formIssuance/getFormIssuancesIndex",
    }),
    setSelectUsersDisplay (items) {
      return typeof items != "undefined" ? items.slice(1) : []
    },
    setShowUserDisplayIsSelect (e) {
      return e.filter((item,index)=> index > 0 && item)
    },
    onZoomOutClick: function () {
      this.zoom = parseInt(this.zoom);
      this.zoom = Math.max(50, this.zoom - 10);
    },
    onZoomInClick: function () {
      this.zoom = parseInt(this.zoom);
      this.zoom = Math.min(200, this.zoom + 10);
    },
    addRecordPlaceholder() {
      this.placeholderNew.push({
        id: `Add${++this.counter}`,
        frm_template_placeholder_name: '',
        frm_imp_cols: '',
      });
    },
    onChooseTemplateAccessFlg: async function(e) {
      this.onEditFormIssuance();
      if(this.editItem.frm_template_access_flg == 1 &&  this.editItem.frm_template_edit_flg < 1){
        this.editItem.frm_template_edit_flg = 1;
      }else if(this.editItem.frm_template_access_flg == 2 &&  this.editItem.frm_template_edit_flg < 2){
        this.editItem.frm_template_edit_flg = 2;
      }
    },
    onChangeFormIssuance() {
      if($('span').hasClass('form-issuance-errors')){
        this.disableButtonSave = true;
      }else{
        this.disableButtonSave = false;
      }
    },

    onEditFormIssuance() {
      var placeholderNew = this.placeholderNew.filter(item => {
        if(!item.frm_invoice_cols){
          return item.frm_template_placeholder_name || item.frm_imp_cols
        }
        return item
      });
      if(!this.disabledUndo || JSON.stringify(this.editItem) != JSON.stringify(this.editItemOld) || JSON.stringify(this.settingItem) != JSON.stringify(this.settingItemOld) || JSON.stringify(this.placeholderData) != JSON.stringify(this.placeholderDataOld) || JSON.stringify(placeholderNew) != JSON.stringify(this.placeholderOld)){
        this.onChangeFormIssuance();
      }else{
        this.disableButtonSave = true;
      }
    },
    onChangeAutoOpeFlg(value) {
        this.editItem.auto_ope_flg = value;
        this.disableButtonSave = false;
    },
    handleBlur() {
      if(this.editItem.frm_template_code){
        this.editItem.frm_template_code = this.editItem.frm_template_code.toUpperCase();
      }
      if(this.editItem.frm_template_code != this.files[0].frm_template_code) this.disableButtonSave = false;
    },
    onSave: async function() {
        if((this.editItem.auto_ope_flg == 1 || this.editItem.auto_ope_flg == 2) && this.selectUsers.length <= 1){
            this.$vs.dialog({
                type: 'alert',
                color: 'danger',
                title: `確認`,
                acceptText: '閉じる',
                text: `完了保存又は自動回覧すれば、最低1人回覧宛先を指定してください`,
            });
        } else if (this.editItem.frm_template_code == '') {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `確認`,
            acceptText: '閉じる',
            text: `明細テンプレートコードを入力してください`,
          });
        } else {
            this.$validator.validate().then(async valid => {
                if (valid) {
                    this.$store.dispatch('updateLoading', true);
                    this.editItem.access_code_flg = this.accessCodeFlg;
                    if(this.accessCodeFlg){
                        this.editItem.access_code = this.accessCode;
                    }else{
                        this.editItem.access_code = '';
                    }
                    this.editItem.outside_access_code_flg = this.outsideAccessCodeFlg;
                    if(this.outsideAccessCodeFlg){
                        this.editItem.outside_access_code = this.outsideAccessCode;
                    }else{
                        this.editItem.outside_access_code = '';
                    }
                    let data = {
                        templateId: this.files[0].id,
                        frmType: this.files[0].frm_type,
                        templateSetting : this.settingItem,
                        placeholderNew: {},
                        editItem : this.editItem,
                        version: this.files[0].version ? this.files[0].version : 0,
                        selectUsers : this.selectUsers,
                    };
                    var csvSetting = new Object();

                    for (var key in this.placeholderData) {
                        if(this.placeholderData[key].frm_imp_cols){
                            csvSetting[this.placeholderData[key].frm_template_placeholder_name] = this.placeholderData[key].frm_imp_cols;
                        }
                        if(this.placeholderData[key].frm_invoice_cols != null){
                            data.templateSetting[this.placeholderData[key].frm_invoice_cols] = this.placeholderData[key].frm_template_placeholder_name;
                        }
                    }

                    for(var index in this.placeholderNew){
                        if(this.placeholderNew[index].frm_template_placeholder_name){
                            data.placeholderNew[index] = {
                                frm_template_placeholder_name: this.placeholderNew[index].frm_template_placeholder_name,
                                frm_invoice_cols: this.placeholderNew[index].frm_invoice_cols ? this.placeholderNew[index].frm_invoice_cols : '',
                                frm_imp_cols: this.placeholderNew[index].frm_imp_cols ? this.placeholderNew[index].frm_imp_cols : ''
                            };

                            if(this.placeholderNew[index].frm_invoice_cols){
                                data.templateSetting[this.placeholderNew[index].frm_invoice_cols] = this.placeholderNew[index].frm_template_placeholder_name;
                            }
                            if(this.placeholderNew[index].frm_imp_cols){
                                csvSetting[this.placeholderNew[index].frm_template_placeholder_name] = this.placeholderNew[index].frm_imp_cols;
                            }
                        }
                    }
                    data.templateSetting.frm_imp_cols = JSON.stringify(csvSetting);

                    data.stamps = [];
                    this.$store.state.home.files.map(file=> {
                        file.pages.forEach(page=> {
                            const _stamp = page.stamps.map(stamp => {
                                const stamp_info = this.stampUsed.find(item => item.id === stamp.id);
                                if (stamp_info){
                                    const height = stamp.height / 3.7795275591;
                                    return {
                                        stamp_page: page.no,
                                        stamp_left: stamp.x / 3.7795275591,
                                        stamp_top: (stamp.y / 3.7795275591) + height,
                                        stamp_deg: stamp.rotateAngle,
                                        stamp_flg: stamp_info ? stamp_info.stamp_flg : null,
                                        stamp_assign_id: stamp_info ? stamp_info.db_id : null,
                                        stamp_deg: stamp.rotateAngle,
                                        stamp_date: stamp_info.stamp_date,
                                    };
                                }else{
                                    return null;
                                }
                            });
                            if (_stamp){
                                data.stamps.push(..._stamp);
                            }
                        })
                    });

                    const result = await this.saveSettingFormIssuance(data);
                    if(result){
                        this.clearFormIssuanceState();
                        this.$router.push('/form-issuance');
                    }
                }else{
                    this.disableButtonSave = true;
                    this.$store.dispatch('updateLoading', false);
                }
            });
        }
    },
    onReturn:function() {
        this.$router.push('/form-issuance');
        this.clearFormIssuanceState();
    },
    onPageThumbnailClick:function(index) {
      // PC
      if (index == this.firstVisiblePageIndex) {
        return;
      }
      const pageno = index + 1;
      this.selectPage(pageno);

      this.$refs.pages.jumpTo(index);
    },
    calcPdfViewerWidth: function () {
      if(!this.$refs.pdfViewer) return;

      const width = this.$refs.pdfViewer.clientWidth;

      const viewerWidth = width - 40;
      if (this.deviceType.isTablet) {
        this.viewerWidth = viewerWidth;
      } else {
        this.viewerWidth = Math.max(820, viewerWidth);
      }

      this.thumbnailViewerWidth = this.$refs.thumbnails.$el.clientWidth - 80;
    },
    selectPage: function (pageno) {
      this.currentPageNo = pageno;
    },
    onChangeStampDate: async function(values) {
      await Axios.get(`${config.BASE_API_URL}/myStamps?date=${this.$moment(values[0]).format('YYYY-MM-DD')}`, {data: {nowait: true}})
            .then(response => {
                let id = this.stampUsed.length + 1;
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
                        stamp_date:this.$moment(values[0]).format('YYYY-MM-DD'),//印面にある日付
                    };
                    // state.stamps.push(stamp);
                    this.stampDisplays.push(stamp);
                    this.stampUsed.push(stamp);
                });
            })
            .catch(error => { return []; });
      // var ret = await this.getCompanyStamps({date: this.$moment(values[0]).format('YYYY-MM-DD')});
      if (this.stampSelected){
        let changedDateSelectedStamp = this.stamps.filter(newStamp => newStamp.db_id === this.stampSelected.db_id)[0];
        if(changedDateSelectedStamp){
          var selStamp = this.stampDisplays.find(item => item.id === changedDateSelectedStamp.id);
          this.selectStamp(selStamp);
        }
      }
    },
    async changePage(pageno){
      pageno = Math.min(pageno, this.fileSelected.maxpages);
      if (pageno < 1) {
        return;
      }

      // 未取得ページ(ボタンが表示されていないページ)の場合、取得する
      if(pageno > this.mobilePages.length){
        const res = await this.getPageImage(pageno, false);
        if (!res.ok) {
          return;
        }
      }
      this.selectPage(pageno);
    },
    async clickStamp(stampId){
      var selStamp = this.stampDisplays.find(item => item.id === stampId);
      this.selectStamp(selStamp);
    },
    async updateRotateAngle(){
      this.userInfo.default_rotate_angle = this.rotateAngle;
      this.updateMyInfo(this.userInfo);
    },
    AddStampsConfirmation: function(currentPageNo) {
      setTimeout(()=>{
        this.$refs.pages.addStampsConfirmation(currentPageNo - 1);
      },10);
    },
    handleResize: function() {
      this.window.width = window.innerWidth;
      this.window.height = window.innerHeight;

      this.calcPdfViewerWidth();
      this.selectPage(this.currentPageNo);
    },
    getPageImage(pageno, isThumbnail) {
      const getPagePromise = this.getPage({
        page: pageno,
        filename: this.fileSelected.server_file_name,
        isThumbnail,
      });

      const storeArray = isThumbnail ? this.thumbnails : this.pages;
      const storeTo = storeArray[pageno - 1];

      return getPageUtil.handleGetPageResult(storeTo, isThumbnail, getPagePromise);
    },
    onVisiblePageChanged(range) {
      this.visiblePageRange = range;

      const firstIndex = this.firstVisiblePageIndex;
      if (firstIndex == -1) {
        return;
      }

      const pageno = firstIndex + 1;
      if (this.currentPageNo !== pageno) {
        this.selectPage(pageno);
      }

      const [thumbnailStart, thumbnailEnd] = this.visibleThumbnailRange;
      const isThumbnailVisible = thumbnailStart <= firstIndex && firstIndex < thumbnailEnd;
      if (!isThumbnailVisible) {
        this.$refs.thumbnails.jumpTo(firstIndex);
      }
    },
    onVisibleThumbnailChanged(range) {
      this.visibleThumbnailRange = range;
    },
    startVisibilityWatch() {
      // 1. nextRequestImage を監視し、取得すべき状態になったら取得開始
      // 2. 取得が終わったら1へ戻る
      const unwatch = this.$watch("nextRequestImage", (val) => {
        if (val) {
          unwatch();

          getPageUtil.getRequiredImages(
              () => this.nextRequestImage, this.getPageImage
          ).then(() => {
            this.startVisibilityWatch();
          });
        }
      });
    },
    clearImageErrors() {
      getPageUtil.clearImageErrors(this.pages, this.thumbnails);
    },


    setUsernameSuggestLabel (item) {
      return item.name;
    },
    setEmailSuggestLabel (item) {
      return item.email;
    },
    onRemoveSelectUserClick: function(id) {
      this.$store.dispatch('updateLoading', true);
      this.removeCircularUser(id);
      this.disableButtonSave = false;
      this.$store.dispatch('updateLoading', false);
    },
    clearSelectUsers: function () {
      this.clearCircularUsers(this.files[0].id);
      this.disableButtonSave = false;
    },
    setUserViewnameSuggestLabel (item) {
      return item.name;
    },
    setEmailViewSuggestLabel (item) {
      return item.email;
    },
    async changeCommentTitle(e) {
      reg.lastIndex = 0
      const isEmoj = reg.test(e.target.value)
      isEmoj &&(this.editItem.title=e.target.value.replace(reg,"").trim().replace(/\s/g,""))
      this.$store.commit('formIssuance/updateCommentTitle', this.editItem.title);
      this.disableButtonSave = false;
    },
    async changeCommentContent(){
      this.$store.commit('formIssuance/updateCommentContent', this.editItem.message);
      this.disableButtonSave = false;
    },
    // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
    getUsersViewSuggestionList(inputValue) {
      this.userViewnameSelect = inputValue;
      if ( inputValue){
        const $this = this;
        clearTimeout(this.checkUserInputTimeout);
        this.checkUserInputTimeout = setTimeout(function () {
          Axios.get(`${config.BASE_API_URL}/users?filter=${inputValue}`, {data: {nowait: true}})
            .then(response => {
              if ($this.userViewnameSelect === inputValue) {
                const users = response.data ? response.data.data.map(item => {
                  item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                  return item
                }) : [];
                $this.userViewSuggestions = users;
              }
            })
            .catch(error => {
              if ($this.userViewnameSelect === inputValue) $this.userViewSuggestions = [];
            });
        }, 300);
      }else{
        this.userViewSuggestions = [];
      }
    },
    getEmailsViewSuggestionList(inputValue) {
      this.emailViewSuggestValidateMsg ='';
      this.emailViewSelect = inputValue;
      if(this.userViewSuggestSelect && this.userViewSuggestSelect.email !== inputValue) {
        this.suggestViewDisabled = false;
      }
      if ( inputValue) {
        const $this = this;
        clearTimeout(this.checkEmailInputTimeout);
        this.checkEmailInputTimeout = setTimeout(function () {
          Axios.get(`${config.BASE_API_URL}/users?filter=${inputValue}`, {data: {nowait: true}})
            .then(response => {
              if ($this.emailViewSelect === inputValue) {
                const users = response.data ? response.data.data.map(item => {
                  item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                  return item
                }) : [];
                $this.emailViewSuggestions = users;
              }
            })
            .catch(error => {
              if ($this.emailViewSelect === inputValue) $this.emailViewSuggestions = [];
            });
        }, 300);
      }else{
        this.emailViewSuggestions = [];
      }
    },
    getFocusUsersViewSuggestionList() {
      if (this.userViewnameSelect && this.userViewSelectedFlg) {
        var inputValue = this.userViewnameSelect;
        const $this = this;
        this.userViewSelectedFlg = false;
        Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
          .then(response => {
            if ($this.userViewnameSelect === inputValue) {
              const users = response.data ? response.data.data.map(item => {
                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                return item
              }) : [];
              $this.userViewSuggestions = users;
            }
          })
          .catch(error => {
            if ($this.userViewnameSelect === inputValue) $this.userViewSuggestions = [];
          });
      }
    },
    getFocusEmailsViewSuggestionList() {
      if (this.emailViewSelect && this.emailViewSelectedFlg) {
        var inputValue = this.emailViewSelect;
        const $this = this;
        this.emailViewSelectedFlg = false;
        Axios.get(encodeURI(`${config.BASE_API_URL}/users?filter=${inputValue}`), {data: {nowait: true}})
          .then(response => {
            if ($this.emailViewSelect === inputValue) {
              const users = response.data ? response.data.data.map(item => {
                item.name = item.family_name ? item.family_name + ' ' + item.given_name : item.name;
                return item
              }) : [];
              $this.emailViewSuggestions = users;
            }
          })
          .catch(error => {
            if ($this.emailViewSelect === inputValue) $this.emailViewSuggestions = [];
          });
      }
    },
    // PAC_5-2189 End
    onSuggestViewSelect: function (user) {
      this.emailViewSuggestValidateMsg ='';
      this.userViewSuggestModel = user;
      this.emailViewSuggestModel = user;
      this.userViewSuggestSelect = user;
      this.emailViewSelect = user.email;
      this.userViewnameSelect = user.name;
      this.suggestViewDisabled = true;
      this.userViewSuggestions = [];
      this.emailViewSuggestions = [];
      this.userViewSelectedFlg = true;
      this.emailViewSelectedFlg = true;
    },
    clearViewSuggestionInput() {
      this.userViewnameSelect = '';
      this.emailViewSelect = '';
      this.userViewSuggestModel = {};
      this.emailViewSuggestModel = {};
      this.suggestViewDisabled = false;
    },
    // PAC_5-2189 宛先は入力内容にかかわらず全件表示される問題対応 Start
    getUsersSuggestionList(inputValue) {
      this.usernameSelect = inputValue;
      this.userSuggestions = [];
      if ( inputValue){
        const $this = this;
        clearTimeout(this.checkUserInputTimeout);
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
        clearTimeout(this.checkEmailInputTimeout);
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
        },300);
      }else{
        this.emailSuggestions = [];
      }
    },
    getFocusUsersSuggestionList() {
      if (this.usernameSelect && this.userSelectedFlg){
        var inputValue = this.usernameSelect;
        const $this = this;
        this.userSelectedFlg = false;
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
    onChangeEmailTemplate(value) {
      this.selectedComment = value;
      if (this.editItem.message == null){
        this.editItem.message = '';
      }
      this.editItem.message = this.editItem.message.concat(value);
      this.selectedComment = value +' '
      this.disableButtonSave = false;
    },
    onSuggestSelect: function (user) {
      this.emailSuggestValidateMsg ='';
      this.userSuggestModel = user;
      this.emailSuggestModel = user;
      this.userSuggestSelect = user;
      this.emailSelect = user.email;
      this.usernameSelect = user.name;
      this.suggestDisabled = true;
      this.userSuggestions = [];
      this.emailSuggestions = [];
      this.userSelectedFlg = true;
      this.emailSelectedFlg = true;
    },
    clearSuggestionInput() {
      this.usernameSelect = '';
      this.emailSelect = '';
      this.userSuggestModel = {};
      this.emailSuggestModel = {};
      this.suggestDisabled = false;
    },
    changeTableShow(index){
      if(this.tableshow[index]){
        delete this.$delete(this.tableshow,index)
      }else{
        this.$set(this.tableshow,index,1);
      }
    },
    async addUserView(){
      if (this.selectUserView.length >= this.maxViewer){
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `閲覧ユーザーに設定できるのは`+this.maxViewer+'名までです',
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
        return;
      }
      this.emailViewSuggestValidateMsg ='';
      if(!this.emailViewSelect) {
        this.emailViewSuggestValidateMsg = '必須項目です';
        return;
      }
      const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
      if(this.emailViewSelect.match(mailPattern) === null) {
        this.emailViewSuggestValidateMsg = 'メールアドレスが正しくありません';
        return;
      }
      var result = await Axios.get(`${config.BASE_API_URL}/userView/checkemail/${this.emailViewSelect}`, {data: {}})
                    .then(response => {
                      return response.data ? response.data.data: [];
                    })
                    .catch(error => { return []; });
      const user = {
        frm_template_id : this.files[0].id,
        parent_send_order: this.selectUsers[0].parent_send_order,
        mst_user_id : result ? result.id : '',
        memo: "",
        del_flg : 0,
        create_user : this.selectUsers[0].email,
        update_user : this.selectUsers[0].email,
        email: this.emailViewSelect,
        name: this.userViewnameSelect ? this.userViewnameSelect : (result ? (result.family_name ? result.family_name + ' ' + result.given_name : '社員') : '社員'),
        company_id: result ? (result.mst_company_id ? result.mst_company_id : result.company_id) : '',
        user_auth: result ? result.user_auth : 0,
        option_flg: result ? (result.option_flg ? result.option_flg : 0) : 0,
      };
      if (user.company_id == null ||(user.company_id != null && user.company_id != this.loginUser.mst_company_id)) {
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `別企業のユーザーは設定できません`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
      }else if(user.email == this.selectUsers[0].email){
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `申請者は登録できません`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
      }else if(this.selectUserView.find((v) => v.email === user.email)){
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `既に登録されています`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
      }else if(this.selectUsers.find((v) => v.email === user.email && v.edition_flg == this.selectUsers[0].edition_flg && v.env_flg == this.selectUsers[0].env_flg && v.server_flg == this.selectUsers[0].server_flg)) {
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `承認者は登録できません。`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
      }else if(user.user_auth == 5 || user.option_flg == 2){
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `受信専用利用者は閲覧ユーザーとして追加できないです。`,
          accept: () => {
            this.clearViewSuggestionInput();
          }
        });
      }else{
        this.$store.commit('formIssuance/addUserView', user);
        this.addViewingUser(user);
        this.disableButtonSave = false;
        this.clearViewSuggestionInput();
      }
    },

    deleteUserView: function(email) {
      const tmpSelectUsers = this.selectUserView.slice();
      tmpSelectUsers.splice(this.selectUserView.findIndex((item => item.email === email)),1);
      const data = {
        email: email,
        frm_template_id: this.files[0].id,
      }
      this.removeViewingUser(data);
      this.$store.commit('formIssuance/updateListUserView', tmpSelectUsers);
      this.disableButtonSave = false;
    },

    async submitMailStepForm() {
      this.emailSuggestValidateMsg ='';
      if(!this.emailSelect) {
        this.emailSuggestValidateMsg = '必須項目です';
        return;
      }
      const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
      const mailPattern1 = /^[a-zA-Z0-9.!$%&@'*+/\\=?^_`{|}\[\]()"><:;~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/i
      if(this.emailSelect.match(mailPattern) === null && this.emailSelect.match(mailPattern1) === null) {
        this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
        return;
      }
      var edition_flg = null, env_flg = null, server_flg = null, company_id = null, company_name = null, user_name = null;
      var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: this.emailSelect})
          .then(response => {
            let result_data = []
            if (response.data){
              result_data = response.data.data.filter(function (value) {
                //企業IDが「１」（edition_flgが「０」）の時はゲストユーザー扱いにして、 それ以外は通常通りedition_flgが「０」のユーザーに回覧にしてほしいです
                if (value.edition_flg !== 0 || value.company_id !== 1) return value;
              })
            }
            return result_data;
          })
          .catch(error => { return []; });
      if (!result.length && this.emailSelect.match(mailPattern) === null){
        this.emailSuggestValidateMsg = 'メールアドレスが正しくありません';
        return;
      }
      if (result.length == 1 && result[0].user_auth == 5 && result[0].company_id != this.selectUsers[0].mst_company_id){
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `受信専用利用者は社内文書しか受信できないです。`,
          accept: () => {
            return '';
          }
        });
      }else {
        result = this.checkEnvironmentalSelectFlg(result);
        if(result.length > 1){
          this.resultCheckEmailExisting = result;
          this.popupSelectAccountActive = true;
          return;
        }else if(result.length == 1){
          edition_flg = result[0].edition_flg;
          env_flg     = result[0].env_flg;
          server_flg  = result[0].server_flg;
          company_id  = result[0].company_id;
          company_name  = result[0].company_name;
          this.usernameSelect  = result[0].name;
        }

        const user = {
          frm_template_id: this.files[0].id,
          child_send_order: this.selectUsers.length,
          email: this.emailSelect,
          name: this.usernameSelect,
          edition_flg: edition_flg,
          env_flg: env_flg,
          server_flg: server_flg,
          company_id: company_id,
          company_name: company_name,
          is_maker: false
        };

        this.pushToSteps(user, this.clearSuggestionInput);
      }
    },

    async getEmailTrees(){
      const mapfunc = (item) => {
        let newItem = {};
        if(!item) return null;
        if(!Object.prototype.hasOwnProperty.call(item, "parent_id")) {
          newItem = {text: item.family_name + ' ' + item.given_name, data: item, isDepartment: false};
          return newItem;
        }else {
          let children = [];
          // PAC_5-2155  アドレス帳の表示方法の変更
          if(Object.prototype.hasOwnProperty.call(item, "users")) {
            if (item.users)
              children.push(...item.users.map(mapfunc));
          }
          if(Object.prototype.hasOwnProperty.call(item, "children")) {
            if(item.children)
              children.push(...item.children.map(mapfunc));
          }
          newItem.text =  item.name;
          newItem.children =  children;
          newItem.data =  {isGroup: true};
          return newItem;
        }

      };
      let departments = [];
      if(this.$store.state.application.departmentUsers) departments = this.$store.state.formIssuance.departmentUsers.map(mapfunc);

      const processContact = (contacts) => {
        let groups = {};
        var id = 0;
        contacts.forEach((contact, stt) => {
          if (!contact.group_name) contact.group_name = 'グループなし';
          if(!groups[contact.group_name]) groups[contact.group_name] = [];
          contact.family_name = contact.name;
          contact.given_name = "";
          groups[contact.group_name].push({id:id,
            text: contact.name, data: contact
          });
          id++;
        });
        contacts = [];
        for(let group_name in groups){
          contacts.push({text: group_name, children: groups[group_name], data: {isGroup: true}});
        }
        return contacts;
      }

      let listContact = "";
      if(!this.checkShowAddress){
        listContact = await this.getListContact({filter: '', type: 0});
        listContact = processContact(listContact);
        if(!listContact) return false;
      }

      let listContactCommon = await this.getListContact({filter: '', type: 1});
      if(!listContactCommon) return false;

      listContactCommon = processContact(listContactCommon);

      let arrAddressTree = [];
      if(listContact){
        arrAddressTree = [ {text:'個人', children: listContact, data: {isGroup: true}} ]
      }
      arrAddressTree.push(
        {text:'共通', children: listContactCommon, data: {isGroup: true}},
        {text:'部署', children: departments, data: {isGroup: true}}
      )


      return arrAddressTree;
    },
    onTreeAddToStepClick: async function(userChecked) {
      // 現在の回覧は合議です、 アドレス帳から追加できません
      if(this.isTemplateCircular){
        this.confirmEdit = false;
        // 宛先、回覧順に合議でないものが存在するため、アドレス帳から追加できません。
        this.showPopupErrorTemplateMessage = "アドレス帳から追加";
        this.showPopupErrorTemplate = true;
        return false;
      }

      $(".application-page-dialog .vs-popup--close").click();
      if (userChecked.length){
        var selectedEmails = [];
        this.mapUserEmail = {};
        let mapUserEmailRepeat = {};
        this.mapEnvEmail = {};
        this.mapEnvCompany = {};
        for(var i =0; i <userChecked.length; i++){
          selectedEmails.push(userChecked[i].email);
          this.mapUserEmail[userChecked[i].email] = userChecked[i];
          if (this.mapUserEmail[userChecked[i].email]) {
            if (!mapUserEmailRepeat[userChecked[i].email]) mapUserEmailRepeat[userChecked[i].email] = {};
            mapUserEmailRepeat[userChecked[i].email][i] = userChecked[i];
          } else {
            this.mapUserEmail[userChecked[i].email] = userChecked[i];
          }
        }
        var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: selectedEmails})
                .then(response => {
                  let result_data = []
                  if (response.data){
                    result_data = response.data.data.filter(function (value) {
                      //企業IDが「１」（edition_flgが「０」）の時はゲストユーザー扱いにして、 それ以外は通常通りedition_flgが「０」のユーザーに回覧にしてほしいです
                      if (value.edition_flg !== 0 || value.company_id !== 1) return value;
                    })
                  }
                  return result_data;
                })
                .catch(error => { return []; });
        result = this.checkEnvironmentalSelectFlg(result);
        this.confirmEmails = [];
        this.mapConfirmEmails = [];
        for(var i=0; i< result.length; i++){
          this.mapEnvEmail[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_id;
          this.mapEnvCompany[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_name;
          if (this.mapUserEmail[result[i].email] && this.mapUserEmail[result[i].email].company_id){
            if (this.mapConfirmEmails[result[i].email] == null){
              this.confirmEmails.push(result[i].email);
              this.mapConfirmEmails[result[i].email] = [];
              this.mapConfirmEmails[result[i].email].push({env_flg: this.mapUserEmail[result[i].email].env_flg, edition_flg:this.mapUserEmail[result[i].email].edition_flg, server_flg:this.mapUserEmail[result[i].email].server_flg,
                company_id:this.mapUserEmail[result[i].email].company_id, company_name:this.mapUserEmail[result[i].email].company_name,
                name:this.mapUserEmail[result[i].email].name, system_name:this.mapUserEmail[result[i].email].system_name});
            }
            this.mapConfirmEmails[result[i].email].push({env_flg: result[i].env_flg, edition_flg:result[i].edition_flg, server_flg:result[i].server_flg, company_id:result[i].company_id, company_name:result[i].company_name,
              name:result[i].name, system_name:result[i].system_name});

          }else{
            this.mapUserEmail[result[i].email].edition_flg = result[i].edition_flg;
            this.mapUserEmail[result[i].email].env_flg = result[i].env_flg;
            this.mapUserEmail[result[i].email].server_flg = result[i].server_flg;
            this.mapUserEmail[result[i].email].company_id = result[i].company_id;
            this.mapUserEmail[result[i].email].company_name = result[i].company_name;
            this.mapUserEmail[result[i].email].name = result[i].name;
            this.mapUserEmail[result[i].email].system_name = result[i].system_name;

            if (mapUserEmailRepeat[result[i].email]) {
              for (let index in mapUserEmailRepeat[result[i].email]) {
                mapUserEmailRepeat[result[i].email][index].edition_flg = result[i].edition_flg;
                mapUserEmailRepeat[result[i].email][index].env_flg = result[i].env_flg;
                mapUserEmailRepeat[result[i].email][index].server_flg = result[i].server_flg;
                mapUserEmailRepeat[result[i].email][index].company_id = result[i].company_id;
                mapUserEmailRepeat[result[i].email][index].company_name = result[i].company_name;
                mapUserEmailRepeat[result[i].email][index].name = result[i].name;
                mapUserEmailRepeat[result[i].email][index].system_name = result[i].system_name;
              }
            }
          }
        }
        if(this.confirmEmails.length > 0){
          this.currentEmailConfirm = 0;
          this.emailSelects = this.confirmEmails[this.currentEmailConfirm];
          this.resultCheckEmailExisting = this.mapConfirmEmails[this.confirmEmails[this.currentEmailConfirm]];
          this.popupSelectAccountActive = true;
          return;
        }
        /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ call function to validate multiple circular user before call api one time */
        var users2Add = [];
        const iterable = () => {
          const item = userChecked.shift();
          if(!item) {
            if (users2Add.length > 0){
              const data = {
                users: users2Add,
                  frm_template_id: this.files[0].id
              };
              this.addCircularUsers(data);
              this.$forceUpdate();

              //PAC_5-1565 アドレス帳追加時に、閲覧ユーザー削除処理が走らない
              data.users.forEach(function(user){
                if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                  this.deleteUserView(user.email);
                }
              },this);
            }
            return;
          }
          this.pushMultipleToSteps(item, (user)=>{
            users2Add.push(user);
            iterable();
          },()=>{
            iterable();
          });
        }
        iterable();
      }
    } ,
    checkEnvironmentalSelectFlg(arrAccount) {
      const limit = JSON.parse(getLS('limit'));
      let uniqs = null;
      if(arrAccount && arrAccount.length > 0) {
        if(limit && limit.environmental_selection_dialog == 0){
          uniqs = arrAccount.filter((user) => {
            if(user.env_flg !== this.selectUsers[0].env_flg || user.edition_flg !== this.selectUsers[0].edition_flg || user.server_flg !== this.selectUsers[0].server_flg) {
              return !arrAccount.some(dupUser => dupUser.email.toLowerCase() == user.email.toLowerCase() && dupUser.env_flg === this.selectUsers[0].env_flg && dupUser.edition_flg === this.selectUsers[0].edition_flg && dupUser.server_flg === this.selectUsers[0].server_flg);
            }
            return user.env_flg === this.selectUsers[0].env_flg && user.edition_flg === this.selectUsers[0].edition_flg && user.server_flg === this.selectUsers[0].server_flg;
          });
        }
      }
      return uniqs || arrAccount;
    },
    pushToSteps: async function (user, callback) {
      if(this.loginUser && this.loginUser.isGuestCompany) {
        if((user.company_id != this.loginUser.mst_company_id || user.env_flg != config.APP_SERVER_ENV || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != config.APP_SERVER_FLG)
                && (user.company_id != this.loginUser.parent_company_id || user.env_flg != this.loginUser.host_app_env || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != this.loginUser.host_contract_server)) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `メッセージ`,
            acceptText: '閉じる',
            text: `回覧先の設定が誤っています`,
          });
          accept: () => {
            callback();
          }
          return Promise.resolve(true);
        }
      }


      const isInternalUser = (
        this.selectUsers[0].mst_company_id === user.company_id &&
        this.selectUsers[0].env_flg === user.env_flg &&
        this.selectUsers[0].edition_flg === user.edition_flg &&
        this.selectUsers[0].server_flg === user.server_flg
      );

      if (!isInternalUser) {
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `別企業のユーザーは設定できません`,
          accept: () => {
            callback();
          }
        });
      }
      if (isInternalUser) {
        const checkEmail = this.selectUsersDisplay[0].findIndex(item => item.email === user.email);
        const data = {
          users: [user],
          frm_template_id: this.files[0].id
        };

        if (checkEmail >= 0 || this.selectUsers[0].email === user.email) {
          this.$vs.dialog({
            type: 'confirm',
            color: 'primary',
            title: `確認`,
            acceptText: 'はい',
            cancelText: 'いいえ',
            text: `すでに回覧先に指定されているメールアドレスですが、よろしいですか？ ${user.email}`,
            accept: async () => {
              await this.addCircularUsers(data);
              this.$forceUpdate();
              if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                this.deleteUserView(user.email);
              }
                this.disableButtonSave = false;
                callback();
            },
            cancel: async () => {
              callback();
            }
          });

          } else {
          await this.addCircularUsers(data);
          this.$forceUpdate();
          if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
            //remove users with the same email address as the approver
            this.deleteUserView(user.email);
          }
            this.disableButtonSave = false;
            callback();
        }
      }

    },
    /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ function to validate multiple circular user before call api */
    pushMultipleToSteps: async function (user, callbackAccept, callbackReject) {
      if(this.loginUser && this.loginUser.isGuestCompany) {
        if((user.company_id != this.loginUser.mst_company_id || user.env_flg != config.APP_SERVER_ENV || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != config.APP_SERVER_FLG)
                && (user.company_id != this.loginUser.parent_company_id || user.env_flg != this.loginUser.host_app_env || user.edition_flg != config.APP_EDITION_FLV || user.server_flg != this.loginUser.host_contract_server)) {
          this.$vs.dialog({
            type: 'alert',
            color: 'danger',
            title: `メッセージ`,
            acceptText: '閉じる',
            text: `回覧先の設定が誤っています`,
          });
          accept: () => {
            callbackReject();
          }
          return Promise.resolve(true);
        }
      }
      const isInternalUser = (
        this.selectUsers[0].mst_company_id === user.company_id &&
        this.selectUsers[0].env_flg === user.env_flg &&
        this.selectUsers[0].edition_flg === user.edition_flg &&
        this.selectUsers[0].server_flg === user.server_flg
      );

      if (!isInternalUser) {
        this.$vs.dialog({
          type: 'alert',
          color: 'danger',
          title: `確認`,
          acceptText: '閉じる',
          text: `別企業のユーザーは設定できません`,
          accept: () => {
            callbackReject();
          }
        });
      }
      if (isInternalUser) {
        const checkEmail = this.selectUsers.findIndex(item => item.email === user.email);
        if (checkEmail >= 0 || this.selectUsers[0].email === user.email) {
          this.$vs.dialog({
            type: 'confirm',
            color: 'primary',
            title: `確認`,
            acceptText: 'はい',
            cancelText: 'いいえ',
            text: `すでに回覧先に指定されているメールアドレスですが、よろしいですか？ ${user.email}`,
            accept: async () => {
              callbackAccept(user);
            },
            cancel: async () => {
              callbackReject();
            }
          });
        } else {
          callbackAccept(user);
        }
      }
    },

    onChangeAccessCode: function () {
      this.disableButtonSave = false;
    },
    generateAccessCode: function() {
      this.accessCode = this.getAccessCode(6);
      this.disableButtonSave = false;
    },
    onChangeOutSideAccessCode: function () {
      this.disableButtonSave = false;
    },
    generateAccessCodeOutside: function() {
      this.outsideAccessCode = this.getAccessCode(6);
      this.disableButtonSave = false;
    },
    onChangeContactName: function() {
      if(this.editContact.name == '') {
        this.editContactNameRequireMsg = '必須項目です';
        return;
      }
    },
    onChangeContactEmail: function() {
      if(this.editContact.email == '') {
          this.editContactEmailRequireMsg = '必須項目です';
          this.editContactEmailValidateMsg = '';
          return;
      }else{
        const mailPattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/
        if(this.editContact.email.match(mailPattern) === null) {
            this.editContactEmailValidateMsg = 'メールアドレスが正しくありません';
            this.editContactEmailRequireMsg = '';
            return;
        }
      }
    },
    addressChangeFlg: function(){
        this.editItem.address_change_flg = !this.editItem.address_change_flg;
        this.disableButtonSave = false;
    },
    textAppendFlg: function(){
        this.editItem.text_append_flg = !this.editItem.text_append_flg;
        this.disableButtonSave = false;
    },
    hideThumbnailFlg: function(){
        this.editItem.hide_thumbnail_flg = !this.editItem.hide_thumbnail_flg;
        this.disableButtonSave = false;
    },
    requirePrint: function(){
        this.editItem.require_print = !this.editItem.require_print;
        this.disableButtonSave = false;
    },
    changeAccessCodeFlg: function(){
        this.accessCodeFlg = !this.accessCodeFlg;
        this.disableButtonSave = false;
        if(this.accessCodeFlg && (this.accessCode == '' || this.accessCode == null)){
            this.generateAccessCode();
        }
    },
    changeOutSideAccessCodeFlg: function(){
        this.outsideAccessCodeFlg = !this.outsideAccessCodeFlg;
        this.disableButtonSave = false;
        if(this.outsideAccessCodeFlg && (this.outsideAccessCode == '' || this.outsideAccessCode == null)){
            this.generateAccessCodeOutside();
        }
    },
    async onRemoveFavorite(favorite_no){
      await this.removeFavorite(favorite_no);
      this.arrFavorite = await this.onSearchFavorite();
    },
    onSortFavorite(){
      let arrSort = this.arrFavorite.map((favotites) => {
        return favotites.map((favotite) => {
          return favotite.id;
        });
      });
      this.sortFavorite({'sorts': arrSort}).then(()=>{
          this.onSearchFavorite();
      });
    },
    async onDepartmentUsersSelect(){
      await this.getDepartmentUsers({filter: ''});
    },
    async onSearchFavorite(){
      this.arrFavorite = await this.getListFavorite({favorite_name:this.searchFavorite});
      return this.arrFavorite;
    },
    async onApplyFavorite(itemFavorite){
      $(".application-page-dialog .vs-popup--close").click();
      // 現在の回覧は合議です
      if(this.isTemplateCircular){
        this.confirmEdit = false;
        // 宛先、回覧順に合議でないものが存在するため、回覧順に追加できません。
        this.showPopupErrorTemplateMessage = "回覧順に追加";
        this.showPopupErrorTemplate = true;
        return false;
      }
      //お気に入りの場合
      let data = {favorite: itemFavorite, usingHash: this.$store.state.home.usingPublicHash};
      let resultStatus = await Axios.post(`${config.BASE_API_URL}${this.$store.state.home.usingPublicHash ? '/public' : ''}/user/checkFavoriteUserStatus`, data)
          .then(response => {
            if (response.data.status == false) {
              this.$vs.dialog({
                type: 'alert',
                color: 'danger',
                title: `確認`,
                acceptText: '閉じる',
                text: '削除または無効の利用者が存在しています。お気に入りを再設定してください。',
              });
            }
            return Promise.resolve(response.data);
          })
          .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
          });
      if(resultStatus.status == false){
        return ;
      }
      let arrApply = itemFavorite.map((item) => {
        return {
          child_send_order: this.selectUsers.length,
          email: item.email,
          name: item.name,
          is_maker: false,
          edition_flg: item.email_edition_flg,
          env_flg: item.email_env_flg,
          server_flg: item.email_server_flg,
          company_id: item.email_company_id,
          company_name: item.email_company_name
        };
      });

      if (arrApply.length){
        var selectedEmails = [];
        this.mapUserEmail = {};
        this.mapEnvEmail = {};
        this.mapEnvCompany = {};
        let newFavorite = false;
        for(var i =0; i <arrApply.length; i++){
          if (arrApply[i].edition_flg !== null && arrApply[i].env_flg !== null && arrApply[i].server_flg !== null) {
            newFavorite = true;
            break;
          }
          selectedEmails.push(arrApply[i].email);
          this.mapUserEmail[arrApply[i].email] = arrApply[i];
        }
        if (!newFavorite){
          var result = await Axios.post(`${config.BASE_API_URL}/user/checkemail`, {email: selectedEmails})
                  .then(response => {
                    let result_data = []
                    if (response.data){
                      result_data = response.data.data.filter(function (value) {
                        //企業IDが「１」（edition_flgが「０」）の時はゲストユーザー扱いにして、 それ以外は通常通りedition_flgが「０」のユーザーに回覧にしてほしいです
                        if (value.edition_flg !== 0 || value.company_id !== 1) return value;
                      })
                    }
                    return result_data;
                  })
                  .catch(error => { return []; });
          result = this.checkEnvironmentalSelectFlg(result);
          this.confirmEmails = [];
          this.mapConfirmEmails = [];
          for(var i=0; i< result.length; i++){
            this.mapEnvEmail[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_id;
            this.mapEnvCompany[result[i].edition_flg + "#" + result[i].env_flg + "#" + result[i].server_flg + "#" + result[i].email] = result[i].company_name;
            if (this.mapUserEmail[result[i].email] && this.mapUserEmail[result[i].email].company_id){
              if (this.mapConfirmEmails[result[i].email] == null){
                this.mapConfirmEmails[result[i].email] = [];
                this.mapConfirmEmails[result[i].email].push({env_flg: this.mapUserEmail[result[i].email].env_flg, edition_flg:this.mapUserEmail[result[i].email].edition_flg, server_flg:this.mapUserEmail[result[i].email].server_flg,
                  company_id:this.mapUserEmail[result[i].email].company_id, company_name:this.mapUserEmail[result[i].email].company_name});
              }
              this.mapConfirmEmails[result[i].email].push({env_flg: result[i].env_flg, edition_flg:result[i].edition_flg, server_flg:result[i].server_flg, company_id:result[i].company_id, company_name:result[i].company_name});
              this.confirmEmails.push(result[i].email);
            }else{
              this.mapUserEmail[result[i].email].edition_flg = result[i].edition_flg;
              this.mapUserEmail[result[i].email].env_flg = result[i].env_flg;
              this.mapUserEmail[result[i].email].server_flg = result[i].server_flg;
              this.mapUserEmail[result[i].email].company_id = result[i].company_id;
              this.mapUserEmail[result[i].email].company_name= result[i].company_name;
            }
          }
          if(this.confirmEmails.length > 0){
            this.currentEmailConfirm = 0;
            this.emailSelects = this.confirmEmails[this.currentEmailConfirm];
            this.resultCheckEmailExisting = this.mapConfirmEmails[this.confirmEmails[this.currentEmailConfirm]];
            this.popupSelectAccountActive = true;
            return;
          }
        }

        /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ call function to validate multiple circular user before call api one time */
        var users2Add = [];
        const iterable = () => {
          const item = arrApply.shift();
          if(!item) {
            if (users2Add.length > 0){
              const data = {
                users: users2Add,
                frm_template_id: this.files[0].id
              };
              this.addCircularUsers(data);
              this.$forceUpdate();

              //PAC_5-1565 お気に入り追加時に、閲覧ユーザー削除処理が走らない
              data.users.forEach(function(user){
                if(this.selectUserView.find((v) => v.email === user.email && user.edition_flg == this.selectUsers[0].edition_flg && user.env_flg == this.selectUsers[0].env_flg && user.server_flg == this.selectUsers[0].server_flg )){
                  this.deleteUserView(user.email);
                }
              },this);
            }
            return;
          }
          this.pushMultipleToSteps(item, (user)=>{
            users2Add.push(user);
            iterable();
          },()=>{
            iterable();
          });
        }
        iterable();
      }
    },

    async showModalEditContacts(nodeId){
      this.showPopupEditContacts = true;
      $(".application-page-dialog .vs-popup--close").click();
      this.$validator.reset();
      this.editContact = await this.getContact(nodeId);
      this.editContactNameRequireMsg = '';
      this.editContactEmailRequireMsg = '';
      this.editContactEmailValidateMsg = '';
    },
    openCalendarClick: function() {
      this.$refs.calendar.fp.toggle();
      this.disableButtonSave = false;
    },
    async toggleReturnCircular(circular_user_id) {
      this.isNotReturnCircular = !this.isNotReturnCircular;
      const returnFlg =  this.isNotReturnCircular?0:1;
      await Axios.patch(`${config.BASE_API_URL}/form-issuances/template/${this.files[0].id}/users/${circular_user_id}/updateReturnflg`, {returnFlg});
    },
    buildSelectUsersDisplay() {
      this.selectUsersDisplay.length = 0;
      this.changeTimes++;
      if (this.selectUsers) {
        let arrSelectUsers = this.$store.state.formIssuance.frmTemplate.users;
        let newSelectUser = arrSelectUsers.reduce((accumulator, user) => {
          accumulator[user['parent_send_order']] = accumulator[user['parent_send_order']] || [];
          accumulator[user['parent_send_order']].push(user);
          return accumulator
        }, []);
        if (Array.isArray(newSelectUser)) {
          newSelectUser = newSelectUser.filter(function (el) {
            return el != null;
          });
          this.selectUsersDisplay.push.apply(this.selectUsersDisplay, newSelectUser);
        } else if (newSelectUser) {
          this.selectUsersDisplay.push.apply(this.selectUsersDisplay, [newSelectUser]);
        }
        this.selectUsersDisplay[0].map(s=>{
          s.changeTimes = this.changeTimes;
        });
        this.onlyInternalUser = arrSelectUsers.every(user => user.parent_send_order === 0);
      }else{
        this.onlyInternalUser = true;
      }
    },
    onAroundArrow:function(){
      let obj = document.getElementById("arrow");
      if(this.searchAreaFlg){
        obj.classList.add("around_return");
        obj.classList.remove("around");
      }else{
        obj.classList.add("around");
        obj.classList.remove("around_return");
      }
      this.searchAreaFlg = !this.searchAreaFlg;
    },
    onFavoriteSelect() {
      this.searchFavorite = '';
      this.onSearchFavorite();
    },
    // アクセスコード作成（社内社外用）
    getAccessCode(len){
      var length = len ? len : 6;
      var text = "";
      var possible = "abcdefghijkmnpqrstuvwxyz0123456789";
      for( var i=0; i < length; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      return text;
    },
    // 社外用回覧者存在チェック
    checkOutsideCompanyCircularUser(){
      var selectUsers = this.$store.state.formIssuance.frmTemplate.users;
      var hasOutsideUser = selectUsers.findIndex((item => item.mst_company_id != selectUsers[0].mst_company_id || item.edition_flg != selectUsers[0].edition_flg || item.env_flg != selectUsers[0].env_flg || item.server_flg != selectUsers[0].server_flg))
      // 社外回覧者存在の場合、社外アクセスコード保護表示する
      if(hasOutsideUser > 0){
        this.outsideAccessCodeShowFlg = true;
      }else {
        this.outsideAccessCodeShowFlg = false;
      }
    },
    async onUpdateContact(allowDuplicate){
      this.$validator.validateAll().then(async result => {
        if (result) {
          if(!allowDuplicate && !await this.checkEmail(this.editContact.email, this.editContact.id)){
            this.confirmDuplicateEmail = true;
            return;
          }
          await this.updateContact(this.editContact, this.editContact.id);
          this.showPopupEditContacts = false;
        }
      });
    },
    async checkEmail(email, id) {
      this.listCheckEmailContact  = await this.getListContact({filter: this.filter});
      for(var i in this.listCheckEmailContact){
        var contact  = this.listCheckEmailContact[i];
        if(id){
          if(contact.email == email && id != contact.id) return false;
        }else{
          if(contact.email == email) return false;
        }
      }
      return true;
    },
    async onDeleteContact(){
      await this.deleteContact(this.editContact.id);
      this.confirmDelete = false;
      this.showPopupEditContacts = false;
    },
    buildTemplateSelectUsersDisplay() {
      this.isTemplateCircular = false;
      this.selectTemplateUsersDisplay.length = 0;
      // 合議の場合、同じ企業、parent_send_order同じです
      let arrUsers = this.$store.state.formIssuance.frmTemplate ? this.$store.state.formIssuance.frmTemplate.users : [];
      // i = 0 申請者 除外する
      for(let i = 1;i < arrUsers.length;i ++){
        // 合議userの場合 user_routes_id存在する
        if(Object.prototype.hasOwnProperty.call(arrUsers[i], "user_routes_id") && arrUsers[i].user_routes_id){
          this.isTemplateCircular = true; // true:合議

          let child_send_order = arrUsers[i].child_send_order - 1;
          if(!Object.prototype.hasOwnProperty.call(this.selectTemplateUsersDisplay, child_send_order)){
            this.selectTemplateUsersDisplay[child_send_order] = [];
          }
          arrUsers[i]['user_routes_name'] = JSON.parse(arrUsers[i].detail).summary;
          this.selectTemplateUsersDisplay[child_send_order].push(arrUsers[i]);
        }
      }
    },

  },

  watch: {
    "zoom": function (newVal,oldVal) {
      newVal = parseInt(newVal);
      this.updateCurrentFileZoom(newVal);

      // スクロール位置調整
      const currentFirstVisiblePageIndex = this.firstVisiblePageIndex;
      if (currentFirstVisiblePageIndex != -1) {
        this.$nextTick(() => {
          this.$refs.pages.jumpTo(currentFirstVisiblePageIndex);
        });
      }
    },
    "$store.state.reduceButton": function() {
      const $this = this;
      setTimeout(function() {
        $this.calcPdfViewerWidth();
        $this.selectPage($this.currentPageNo);
      },300);
    },
    "$store.state.home.fileSelected": async function(newVal, oldVal) {
      // 表示ページ なしとする
      this.visiblePageRange = [-1, -1];
      this.visibleThumbnailRange = [-1, -1];

      this.currentPageNo = 1;

      const pageCount = newVal?.maxpages ?? 0;
      this.pages = getPageUtil.createPages(pageCount, newVal?.pages);
      this.thumbnails = getPageUtil.createThumbnails(pageCount);

      if (newVal) {

        const fileIndex = this.$store.state.home.files.findIndex(file => file.server_file_name === this.$store.state.home.fileSelected.server_file_name);
        this.tabSelected = fileIndex;

        this.zoom = newVal.zoom;
        this.histories = [];
        this.disabledUndo = this.$store.state.home.fileSelected.actions.length <= 0;

        const isMobile = this.window.width <= 480;
        if (isMobile) {
          // 初期表示用画像取得
          const promises = getPageUtil.getPageImagesForMobile(this.pages, this.getPageImage);

          const res = await promises[0]; // first page
          if (res?.ok) {
            this.selectPage(1);
          }
        }
      } else {
        this.zoom = 100;
      }
    },
    "$store.state.home.hasAction": async function() {
      if(!this.$store.state.home.fileSelected || !this.$store.state.home.fileSelected.actions) {
        this.disabledUndo = true;
        return;
      }
      this.disabledUndo = this.$store.state.home.fileSelected.actions.length <= 0;
    },
    "tabSelected": function (newIndex, oldIndex) {
      const newVal = this.files[newIndex];
      if(!newVal) return;
      if(newVal.confidential_flg && newVal.mst_company_id !== this.loginUser.mst_company_id) {
        this.tabSelected = oldIndex;
      }
    },
    "$store.state.home.files":function () {
      Utils.buildTabColorAndLogo(this.files, this.companyLogos, this.loginUser.mst_company_id, config.APP_EDITION_FLV, config.APP_SERVER_ENV);
    },
    "disabledUndo": function (){
      this.onEditFormIssuance();
    },

    "treeData": function() {
      this.treeLoaded++;
    },
    "$store.state.contacts.changePhoneBooks": async function () {
      this.treeData = await this.getEmailTrees();
    },
    "$store.state.formIssuance.loadDepartmentUsersSuccess": async function () {
      this.treeData = await this.getEmailTrees();
      this.addLogOperation({ action: 'r08-display-contacts', result: 0});
      this.confirmEdit = true;
    },
    "$store.state.application.selectUserChange": function () {
      this.usersChange = !this.usersChange;
      this.buildSelectUsersDisplay();
      this.checkOutsideCompanyCircularUser();
    },
    "$store.state.formIssuance.selectUserChange": function () {
        this.usersChange = !this.usersChange;
        this.buildSelectUsersDisplay();
        this.checkOutsideCompanyCircularUser();
    },
    "$store.state.home.selectUserChange": function () {
      this.usersChange = !this.usersChange;
      this.buildSelectUsersDisplay();
      this.checkOutsideCompanyCircularUser();
    },
    "$store.state.application.selectTemplateUserChange": function () {
      this.buildTemplateSelectUsersDisplay(); // 合議ユーザー表示作成
    },
    accessCode:function(){
      this.accessCode=this.accessCode.replace(/[\W]/g,'');
    },
    outsideAccessCode:function(){
      this.outsideAccessCode=this.outsideAccessCode.replace(/[\W]/g,'');
    },
    selectUserView:function(newVal, oldVal){
      if(newVal.length){
        this.searchAreaFlg = true;
      }
    },
  },
  async mounted() {
    document.body.style.overflow = 'hidden';

    this.handleResize();
    // PAC_5-1136 クラウドストレージとの連携失敗時にエラーメッセージを表示
    this.$ls.on('errormessage', (value) =>{
      console.log("failed to Cloud Connection");
      let message = this.$ls.get('errormessage');
      //複数インスタンス対策
      if(message){
        this.$vs.notify({color: 'danger',text: message,position: 'bottom-left'});
      }
      this.$ls.remove('errormessage');
    });

    // 印鑑,コメント欄にスクロールバーを追加
    var element = document.getElementsByClassName("con-slot-tabs");
    for (var i = 0; i < element.length; i++){
      element[i].style.overflow = "auto";
      element[i].style.height = "94%";
    }

      this.info = await this.getMyInfo();
      this.emailTemplateOptions =  Utils.setEmailTemplateOptions(this.info);
      //PAC_5-1400 閲覧ユーザー複数設定
      this.maxViewer = this.info.max_viwer_count;
      this.infoCheck = await this.getInfoCheck();

      // ↓ back=trueの時のため？
    if (this.$route.query.back && this.files && this.files.length){
      this.selectFile(null);
      this.onFileTabClick(this.files[0], 0);
    }
    if (!this.$store.state.home.fileSelected || !this.$store.state.home.fileSelected.actions) {
      this.disabledUndo = true;
      return;
    }
    this.disabledUndo = this.$store.state.home.fileSelected.actions.length <= 0;

  },
  async created() {
    this.checkDeviceType();
    // すすむボタンの状態の初期化
    this.$store.state.home.disabledProceed = true;
    this.date = new Date();

    this.startVisibilityWatch();

    const promises = [];

    if(!this.$route.query.back) {
      promises.push(this.clearState());
    }
    if(this.files && this.files.length) {
      this.addLogOperation({action: 'frm3-form-issuance-setting-display', result: 0});
      this.accessCodeFlg = this.files[0].access_code_flg;
      this.accessCode = this.files[0].access_code ?? '';
      this.outsideAccessCodeFlg = this.files[0].outside_access_code_flg;
      this.outsideAccessCode = this.files[0].outside_access_code ?? '';
      this.editItem = {
        frm_template_edit_flg: this.files[0].frm_template_edit_flg,
        frm_template_access_flg: this.files[0].frm_template_access_flg,
        remarks: this.files[0].remarks,
        auto_ope_flg: this.files[0].auto_ope_flg, // 項目入力後動作
        title: this.files[0].title, // 件名
        message: this.files[0].message, // メッセージ
        address_change_flg: this.files[0].address_change_flg, // 回覧順変更許可
        text_append_flg: this.files[0].text_append_flg, // テキスト追加許可
        hide_thumbnail_flg: this.files[0].hide_thumbnail_flg, // サムネイル非表示
        require_print: this.files[0].require_print, // 捺印設定
        access_code_flg: this.files[0].access_code_flg, // アクセス_社内利用
        access_code: this.files[0].access_code, // アクセス_社内コード
        outside_access_code_flg: this.files[0].outside_access_code_flg, // アクセス_社外利用
        outside_access_code: this.files[0].outside_access_code, // アクセス_社外コード
        re_notification_day: this.files[0].re_notification_day, // 再通知日
        frm_template_code: this.files[0].frm_template_code,
        frm_type: this.files[0].frm_type,
      }
      if(this.files[0].frm_template_code == ''){
        this.showTab = 1;
      }

      var userCreate = await this.getTemplateDepartment(this.files[0].id);
      this.departmentId = userCreate.mst_department_id;
          await this.getFormIssuancePlaceholder({ templateId: this.files[0].id,frmType: this.files[0].frm_type}).then(response => {
            this.templateSetting = response.templateSetting;
            if(response.templateSetting && response.templateSetting.id){
              this.disableButtonSave = true;
              var csv = JSON.parse(response.templateSetting.frm_imp_cols);

              this.settingItem = {
                frm_default_name: response.templateSetting.frm_default_name,
                to_email_name_imp: response.templateSetting.to_email_name_imp,
                to_email_addr_imp: response.templateSetting.to_email_addr_imp
              }

              const templateSettingEntries = Object.entries(this.templateSetting)

              const items = response.placeholders.map(item => {
                item.frm_imp_cols = csv[item.frm_template_placeholder_name] ? csv[item.frm_template_placeholder_name] : '';
                item.frm_invoice_cols = null
                const templateSettingMatch = templateSettingEntries.find(_item => _item.includes(item.frm_template_placeholder_name))
                if(templateSettingMatch && templateSettingMatch.length)
                  item.frm_invoice_cols = templateSettingMatch[0]
                return item;
              });
              this.placeholderNew  =  items.filter(item => item.additional_flg)
              this.placeholderData =  items.filter(item => !item.additional_flg)
            }else{
              this.settingItem.frm_default_name = this.files[0].frm_type == 0 ? '明細' : '請求書';

              this.placeholderData = response.placeholders.map(item => {
                var frm_imp_cols = item.frm_template_placeholder_name.replace('${','');
                item.frm_imp_cols = frm_imp_cols.replace('}','');
                return item;
              });
            }
          });

          this.settingItemOld = Object.assign({}, this.settingItem);
          this.placeholderDataOld = this.placeholderData.map(item => ({...item}));
          this.placeholderOld = this.placeholderNew.map(item => ({...item}));
          this.editItemOld = Object.assign({}, this.editItem);


      var $dataOption = [];
      if(this.files[0].frm_type == 1){
        $dataOption = [
          {value: 'trading_date_col', text: '売上計上日'},
          {value: 'invoice_no_col', text: '請求伝票番号'},
          {value: 'invoice_date_col', text: '請求日'},
          {value: 'customer_name_col', text: '取引先名'},
          {value: 'customer_code_col', text: '取引先コード'},
          {value: 'invoice_amt_col', text: '請求金額'},
          {value: 'payment_date_col', text: '支払期日'},
        ]
      }else{
        $dataOption = [
          {value: 'reference_date_col', text: '基準日'},
          {value: 'customer_name_col', text: '取引先名'},
          {value: 'customer_code_col', text: '取引先コード'}
        ]
        const frmIndex = await this.getFormIssuancesIndex();
        const fields = Object.values(frmIndex);
        for (const field of fields) {
          $dataOption.push({value: 'frm_index'+field.frm_index_number+'_col', text: field.index_name});
        }
      }

      this.$store.commit('formIssuance/setOptionSetting', $dataOption);
      this.selectFile(this.files[0]);
    } else {
      this.addLogOperation({action: 'frm5-form-issuance-form-list-display', result: 1});
    }
    promises.push(
        (async () => {
          this.userInfo = await this.getMyInfo();
          this.rotateAngle = this.userInfo.default_rotate_angle;
        })(),
        (async () => {
          const id = this.$route.params.id;
          if(id) {
            const ret = await this.loadFormIssuances({templateId: id});
            this.$store.commit('home/loadCircularSuccess', ret);
            this.$store.commit('home/pushFiles', ret.files);
            setTimeout(()=> {
              this.$store.commit('home/initFileSelected');
            },500);
          }
        })(),
        (async () => {
            const resultCircular = await this.getSavedCircularUsers({ templateId: this.files[0].id});
            this.savedCircularUsers = resultCircular.savedCircularUsers;
        })(),
        (async () => {
            const resultViewing = await this.getSavedViewingUsers({ templateId: this.files[0].id});
            this.savedViewingUsers = resultViewing.savedViewingUsers;
        })(),
        // 印面リスト初期表示
        this.onChangeStampDate(this.date)
    );

    await Promise.all(promises);

    this.$store.commit('home/homeUnSelectText');

    window.addEventListener('resize', this.handleResize);

    var limit = getLS("limit");
    limit = JSON.parse(limit);
    // PAC_5-1702 別回覧で登録した閲覧者が、データ上は登録されていないものの画面上にのみ表示される
    if(limit && limit.enable_any_address == 1){
      this.checkShowAddress = true;
    }else{
      this.checkShowAddress = false;
    }
    if(this.selectUsers.length <= 0) {
      const data = {users: [{
          child_send_order: 0,
          name: this.loginUser.family_name + ' ' + this.loginUser.given_name,
          email: this.loginUser.email,
          is_maker: true,
          company_id: this.loginUser.mst_company_id,
          company_name: this.loginUser.mst_company_name,
          frm_template_id: this.files[0].id,
        }],
          frm_template_id: this.files[0].id,};
      await this.addCircularUsers(data);
    }
    this.buildSelectUsersDisplay();
    this.buildTemplateSelectUsersDisplay();
    this.checkOutsideCompanyCircularUser();

    if(this.selectUsers && this.selectUsers.length > 0 ) {
      let applicant = this.selectUsers.find(user => user.email === this.loginUser.email);
      if(applicant) {
        this.isNotReturnCircular = (applicant.return_flg == 0);
      }
    }
    this.protectionSetting = await this.getProtection();

    if (this.protectionSetting.enable_email_thumbnail == 1){
      this.showEmailThumbnailOption = true;
    }else{
      this.showEmailThumbnailOption = false;
    }

    if(this.circular && this.protectionSetting) {
      // デフォルト値:OFF
      this.allowChangeDestinationFlg = this.protectionSetting.destination_change_flg;
      this.accessCode = this.circular.access_code ? this.circular.access_code : this.accessCode;
      this.accessCodeFlg = this.protectionSetting.access_code_protection;
      this.outsideAccessCodeFlg = this.protectionSetting.access_code_protection;
      await this.addLogOperation({ action: 'r08-display', result: 0});
    }

    if (this.$store.state.application.commentTitle){
      this.commentTitle = this.$store.state.application.commentTitle;
    }
    else if(this.$store.state.home.circular && this.$store.state.home.circular.users){
      this.commentTitle = this.$store.state.home.circular.users[0].title;
    }
    if (this.$store.state.application.commentContent){
      this.commentContent = this.$store.state.application.commentContent;
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
        popups[i].appendChild(div);
      }
    });

    this.settingLimit = null;
    if (!Object.prototype.hasOwnProperty.call(this.loginUser, "isAuditUser") || !this.loginUser.isAuditUser) {
      this.settingLimit = await this.getLimit();
    }
    if (this.settingLimit==null){
      this.settingLimit={};
    }
  },
  beforeDestroy() {
    // 取得を止めるため
    this.visiblePageRange = [-1, -1];
    this.visibleThumbnailRange = [-1, -1];
  },
  destroyed() {
    window.removeEventListener('resize', this.handleResize);
  },
}
</script>

<style lang="scss">
.frm-template .vs-tabs-primary button.save-button:hover {
    color: white !important;
}
#main-home .vs-button--text {
    font-size: inherit !important;
}
input{
  padding: 5px 10px !important;
}
input[name="subject"]+.vs-input--placeholder.normal{
  padding: 3px 10px !important;
}
.reNotification{
  visibility: visible !important;
  border: 1px solid #dcdcdc !important;
  color: #000 !important;
}
@media (max-height:901px){
  .style-list-placeholder{
    overflow: auto;
    height: 205px;  
  }
}
@media (min-height:900px){
  .style-list-placeholder{
    overflow: auto;
    height: 385px !important;
  }
}
.vs-row.applicant .vs-col:first-child .item:after{
  display: none;
}
.mail-steps .mail-view-list .item.me.item-user-view {
  background: #e6e6fa;
  margin-top: 10px;
  padding: 10px;
  font-size: 16px;
  transition: all .2s ease-out;
}
</style>
