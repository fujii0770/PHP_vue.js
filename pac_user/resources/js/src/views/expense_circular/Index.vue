<template>
    <div>
	<div id="main-home" class="mobile create_new" v-if="!files.length">
		<div style="margin-bottom: 15px" v-if="!isMobile">
			<vs-row vs-type="flex" vs-align="center">
                <vs-col  vs-w="3" >
                    <div  v-if="title.trim().length" :title="title" >
                        <p><strong>件名：</strong></p>
                        <p style="
                            width: 90%;
                            padding:1px 0;
                            word-break: break-all;
                        "><strong>{{title.length > 20 ? title.slice(0,20) + '...' : title}}</strong></p>
                    </div>
                </vs-col>
            <vs-col vs-type="flex" vs-w="3" vs-align="center" vs-justify="center">
                <ul v-if="isEditScreen" class="breadcrumb">
                    <li><p style="color: #0984e3;"><span class="badge badge-primary">1</span> プレビュー・捺印</p></li>
                    <li><p><span class="badge badge-default">2</span> 回覧先設定</p></li>
                    <li><p style="background: transparent"></p></li>
                </ul>
            </vs-col>
			<vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6" vs-xs="12">
                <vs-button id="button8" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="getNamePath != 'create_circular'" v-on:click="goBack"> 戻る</vs-button>
                <vs-button id="button9"  class="square" :style="!isShowAttachment || specialCircularFlg ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" color="#fff" type="filled"  :disabled="files.length <= 0" v-on:click="addAttachment" ><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</vs-button>
                <vs-button id="button6" v-if="!$store.state.home.fileSelected && settingLimit && (settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox) && getNamePath != 'create_circular' && settingLimit.sanitizing_flg == 0"
                            style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" disabled><i class="fas fa-cloud-download-alt" style="color:#107fcd;padding-right:5px;"></i> ダウンロード</vs-button>
                <vs-dropdown :vs-trigger-click="is_ipad" v-if="$store.state.home.fileSelected && settingLimit && (settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox)">
                    <vs-button id="button5" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" :disabled="!showDownloadBtn" v-if="!settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード</vs-button>
                    <vs-button id="button5_2" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" :disabled="!showDownloadBtn" v-if="settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード予約</vs-button>
                    <vs-dropdown-menu v-show="showDownloadBtn">
                        <template v-if="showDownloadBtn">
                        <!--  受信時のみ捺印履歴メニューを表示する PAC_5-1467  -->
                        <li v-if="settingLimit.storage_local" class="vx-dropdown--item">
                          <a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="default" @click.native.stop="changeDefaultStatus($event);"  vs-name="radioVal"  v-model="radioVal" >完了済みファイル</vs-radio></a>
                        </li>
                        <li v-if="settingLimit.storage_local && isEditScreen" class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addStampHistory" @click.native.stop="changeStampHistory($event)"   vs-name="radioVal"    v-model="radioVal"  >回覧履歴を付ける</vs-radio></a></li>
                        <li v-if="settingLimit.storage_local && isEditScreen" v-show="settingLimit.is_show_current_company_stamp" class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addTextHistory" @click.native.stop="changeTextHistory($event)"   vs-name="radioVal"    v-model="radioVal"  >自社のみの回覧履歴を付ける</vs-radio></a></li>
                        <vs-dropdown-item v-if="settingLimit.storage_local && !settingLimit.sanitizing_flg">
                            <vs-button v-on:click="onDownloadFile" color="primary" class="w-full download-item" type="filled"><i class="fas fa-download"></i>  ローカル</vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="showReserveFile" class="w-full download-item" type="filled" style="padding: 0.75rem 1rem;"><i class="fas fa-download"></i>ダウンロード予約</vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_box && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('box')" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_onedrive && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('onedrive')" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <p>OneDrive</p></vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_google && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('google')" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_dropbox && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('dropbox')" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="pdf"> <span class="download-item-text">Dropbox</span></vs-button>
                        </vs-dropdown-item>
                        </template>
                    </vs-dropdown-menu>
                </vs-dropdown>

                <vs-button :style="(!isCreateScreen && circularUserLastSend && circularUserLastSend.child_send_order === 0) ? 'color:#000;border:1px solid #dcdcdc;':'display:none'" :disabled="files.length <= 0" class="square submit-circular"  color="#fff" type="filled" v-on:click="onDiscardCircularClick"><i class="far fa-window-close" style="color:#107fcd;margin-right: 5px;"></i> 回覧破棄</vs-button>
                <vs-button :style="(isCreateScreen || (circularUserLastSend && circularUserLastSend.child_send_order === 0)) ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" :disabled="files.length <= 0 || clickState" class="square submit-circular"  color="#fff" type="filled" v-on:click="goToSendback"><span><img :src="require('@assets/images/pages/home/back_to_former_person.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 差戻し</vs-button>
                <vs-button id="button2" :style="isCreateScreen ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" class="square mr-0 submit-circular2" :disabled="files.length <= 0 || clickState" v-on:click="goToDestination" color="#fff" type="filled"><i class="far fa-envelope" style="color:#107fcd;margin-right: 5px;"></i> <template v-if="checkShowButtonApply">回覧設定</template> <template v-else>次へ</template> </vs-button>
                <vs-button id="button4" :style="isEditScreen ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" color="#fff" class="square mr-0 submit-circular" :disabled="files.length <= 0 || clickState" v-on:click="goToApplication" type="filled"><i class="far fa-envelope" style="color:#107fcd;margin-right: 5px;"></i> 回覧設定</vs-button>
            </vs-col>
			</vs-row>
		</div>
        <vs-card :class="'work-content ' + (isEditScreen ? 'isEdit':'')" v-show="!$route.params.id">
            <vs-row>
                <div style="display: flex;flex-wrap: nowrap;width: 100%;" class="upload-file">
                <vs-col vs-type="flex" vs-w="1.5" class="preview-scale" :style="showLeftToolbar?'width: 200px;min-width: 200px;flex:0 0 auto;flex-direction: column;border: 1px solid #cdcdcd;':'width:0;overflow:hidden;'">
                    <div class="preview-list-tool">
                        <vs-row class="mb-3" vs-align="center" vs-type="flex" vs-justify="center">
                            <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-out-container"><vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out"><i class="fas fa-minus"></i> </vs-button></div></vs-col>
                            <vs-col vs-w="6" vs-justify="center" vs-align="center"><div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{zoom}}%</label></div></vs-col>
                            <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-in-container"><vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in"><i class="fas fa-plus"></i> </vs-button></div></vs-col>
                        </vs-row>
                        <vs-row class="mb-3 confidential" v-if="fileSelected && fileSelected.create_user_id === loginUser.id && fileSelected.enableDelete && companyConfidentialFlg" v-show="!specialButtonDisableFlg">
                            <vs-col vs-w="2"><vs-checkbox class="confidentialCheck" :value="confidentialFlg" v-on:click="onConfidentialFlgLabelClick"></vs-checkbox></vs-col>
                            <vs-col vs-w="10" class="pl-2">
                                <p v-on:click="onConfidentialFlgLabelClick" class="confidential-label">社外秘に設定</p>
                            </vs-col>
                        </vs-row>
                        <!-- 網掛を外す -->
                        <vs-row class="mb-3 confidential" v-if=" fileSelected && fileSelected.create_user_id !== loginUser.id && fileSelected.confidential_flg ">
                            <vs-col vs-w="2"><vs-checkbox class="confidentialCheck" :value="overlayHiddenFlg" v-on:click="onOverlayHiddenFlgLabelClick"></vs-checkbox></vs-col>
                            <vs-col vs-w="10" class="pl-2"><p v-on:click="onOverlayHiddenFlgLabelClick" class="confidential-label">網掛を外す</p></vs-col>
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
                <vs-col vs-type="flex" class="main-content" :vs-w="stampToolbarActive ? '8':'10.3'" style="flex:1 1 auto;transition: width .2s;width:0;">
                    <div class="pdf-content" ref="pdfViewer" style="position: relative;">
                        <vs-col vs-type="flex" vs-w="12" vs-align="flex-start" vs-justify="flex-start">
                            <vs-navbar v-model="tabSelected"
                                color="#fff"
                                active-text-color="rgb(9,132,227)"
                                class="filesNav">

                                <vs-navbar-item v-for="(file, index) in files" :key="file.circular_document_id" :index="index" :class="'document ' + (file.confidential_flg ? 'is-confidential': (file.hasOwnProperty('tabColor')?file.tabColor:''))">
                                    <template>
                                       <a :class="[(file) !== '' && file.tabLogo?'no-padding':'', `filename`]" @dblclick="renameFileNameClick(file.name != ''? file.name.split('.pdf')[0]:'')" v-tooltip.top-center="file.name" v-on:click="onFileTabClick(file, index)" href="#">
                                            <i v-if="file.confidential_flg && file.mst_company_id !== loginUser.mst_company_id" class="fas fa-lock" style="color: #fdcb6e"></i>
                                            <span v-if="file.confidential_flg && file.mst_company_id !== loginUser.mst_company_id"> ー </span>
                                            <i v-if="!file.tabLogo && file.timestampLogo" class="far fa-clock fa-lg" style="color: dimgrey"></i>
                                            <img v-if="!file.confidential_flg && (file) !== '' && file.tabLogo" :src="`data:image/png;base64,${file.tabLogo}`"  alt="logo" class="logo-format">
                                            <template v-if="(file) === '' || !file.tabLogo">{{file.name}}</template>
                                        </a>
                                        <a v-if="repagePreviewFlg && registeredDocInfoList.some(docInfo => docInfo.circular_document_id === file.circular_document_id)"
                                          v-tooltip.top-center="(repagePreviewFlg && registeredDocInfoList.some(docInfo => docInfo.circular_document_id === file.circular_document_id))?'改ページ調整':''"
                                          class="edit" v-on:click="onEditPageBreaksClick(file.circular_document_id)"
                                        >
                                          <i class="fas fa-edit"></i>
                                        </a>
                                        <a v-if="(!file.confidential_flg || (file.confidential_flg && file.mst_company_id === loginUser.mst_company_id)) && file.enableDelete && ( enableAdd || checkNoExistCircularUser) && isFileCreatedByOwn(file)" v-show="!specialButtonDisableFlg" class="close" v-on:click="onCloseDocumentClick(file, index)" ><i  class="close fas fa-times"></i></a>
                                    </template>
                                </vs-navbar-item>

                               <vs-navbar-item index="99999" class="add-document" v-if=" enableAdd || checkNoExistCircularUser" v-show="!specialCircularFlg">
                                    <p v-tooltip.top-center="'Add File'" v-if="files.length > 0 && files.length < 5" v-on:click="onAddFileClick"> <i class="fas fa-plus"></i></p>
                                </vs-navbar-item>
                                <vs-spacer></vs-spacer>
                            </vs-navbar>
                        </vs-col>
                        <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="upload-wrapper" v-if="!fileSelected">

                            <div class="vx-col w-full md:w-1/2 upload-box" id="dropZone" @drop="handleFileSelect" @dragleave="handleDragLeave" @dragover="handleDragOver" v-if="settingLimit && settingLimit.storage_local === 1">
                                <label class="wrapper" for="uploadFile">
                                <input type="file" ref="uploadFile" multiple accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf" id="uploadFile" v-on:change="onUploadFile" />
                                <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                                    <label for="uploadFile" class="pb-5 label-upload"><strong>クリックしてファイルを選択してください</strong></label>
                                </vs-row>
                                <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="mb-20">
                                    <img class="file" :src="require('@assets/images/pdf.png')" alt="pdf">
                                    <img class="file" :src="require('@assets/images/word.svg')" alt="word">
                                    <img class="file" :src="require('@assets/images/excel.svg')" alt="excel">
                                </vs-row>
                                <vs-row vs-type="flex" vs-align="center" vs-justify="center">
                                    <img class="cloud" :src="require('@assets/images/upload.svg')">
                                </vs-row>
                                </label>
                            </div>
                            <div class="vx-col w-full md:w-1/2 upload-box" id="dropZone" v-else>
                                <label class="wrapper" for="uploadFile" :style="'cursor:default'">
                                    <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                                        <label for="uploadFile" :style="'visibility:hidden'" class="pb-5"><strong>クリックしてファイルを選択してください</strong></label>
                                    </vs-row>
                                    <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="mb-20">
                                        <img class="file" :src="require('@assets/images/pdf.png')" alt="pdf">
                                        <img class="file" :src="require('@assets/images/word.svg')" alt="word">
                                        <img class="file" :src="require('@assets/images/excel.svg')" alt="excel">
                                    </vs-row>
                                    <vs-row vs-type="flex" vs-align="center" vs-justify="center">
                                        <img class="cloud" :src="require('@assets/images/upload.svg')">
                                    </vs-row>
                                </label>
                            </div>
                            <vs-divider color="primary" style="font-size:1.5rem" class="select_from_cloud">クラウドストレージからファイルを選択</vs-divider>
                            <vs-row
                                vs-align="center"
                                vs-type="flex" vs-justify="center" vs-w="12" class="select_from_cloud_button">
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box',circular_file)" class="w-25 download-item box_cloud" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <span class="download-item-text">OneDrive</span></vs-button>
                                <span class="break_line"></span>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google',circular_file)" class="w-25 download-item google_cloud" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="Dropbox"> <span class="download-item-text">Dropbox</span></vs-button>
                            </vs-row>

                        </vs-row>

                        <template v-if="fileSelected != null">
                          <pdf-pages v-show="!hasRequestFailedImage"
                            ref="pages"
                            :expectedPagesSize="expectedPagesSize" :pages="pages"
                            :rotateAngle="rotateAngle" :opacity="realOpacity" :imageScale="pageImageScale"
                            :deleteFlg="fileSelected.del_flg" :deleteWatermark="fileSelected.delete_watermark"
                            :stamps="stampUsed"
                            @visible-page-changed="onVisiblePageChanged">
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
                <vs-col vs-type="flex" oncontextmenu="return false" :vs-w="stampToolbarActive ? '2.5':'.2'" :class="stampToolbarActive?'stamp-tool right-toolbar vs-con-loading__container':'stamp-tool right-toolbar hide'" >
                    <div class="tools stamp-tool-bar" :style="!stampToolbarActive ? 'display:none':'' ">
                        <div v-if="!isEditScreen" style="height:100%">
                          <vs-tabs class="tab-parent comment-height" v-model="tab_home_info">
                            <vs-tab label="印鑑">
                              <div class="stamps-processing" style="position:relative;z-index:100;border:1px solid #dcdcdc;border-radius:30px;padding-left:15px;padding-right:15px;background-color:#000000;width:max-content;margin:auto;text-align:center;user-select:none;-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;-khtml-user-select:none;">
                                <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="undoAction" :disabled="disabledUndo">
                                    <i class="fas fa-undo-alt" style="color:#ffffff;margin-right: 5px;"></i><br> 元に戻す</vs-button>
                                <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="AddStampsConfirmation(currentPageNo)" :disabled="this.$store.state.home.disabledProceed">
                                    <i class="fa" style="color:#ffffff;margin-right: 5px;">&#xf01e;</i><br> やり直し</vs-button>
                              </div>
                              <vs-row vs-type="flex" style="padding: 10px 10px 0">
                                  <div class="break"></div>
                              </vs-row>
                              <h2 class="title">ご利用可能な印鑑</h2>
                              <vs-row vs-type="flex" vs-align="center" vs-justify="center" vs-w="12" style="padding: 10px;position: relative">
                                <vs-col :vs-w="(!receivedOnlyFlg) ? 6 : 12">
                                  <flat-pickr style="position: absolute;z-index: 1;width: 50px;right:10px;border:0;color:#fff" :config="configdateTimePicker" v-model="date" @on-change="onChangeStampDate" />
                                  <vs-button class="square" :style="(!receivedOnlyFlg) ? {width: '95%'}:{width: '100%'}" data-toggle color="primary" type="filled" v-show="!(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0))">捺印日付変更</vs-button>
                                </vs-col>
                                <vs-col v-if="(!receivedOnlyFlg)" :vs-w="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0)) ? 12 : 6">
                                  <v-popover offset="0" :auto-hide="false" :popoverClass="['change-stamp-angle']">
                                    <vs-button class="square tooltip-target b3 change-angle-stamp" color="primary" type="filled" :style="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0)) ?{width: '100%'}:{width: '95%'}">捺印設定</vs-button>
                                    <template slot="popover">
                                      <div style="position: relative;margin-top: 20px" v-if="rotateAngleFlg">
                                        <div class="stamp-setting-title">印鑑の角度</div>
                                        <div class="rotate-angle">
                                      <vs-input-number  id="rotate_angle" min="0" max="360" style="width: 33%; margin-top: 15px;" v-model="rotateAngle" @change="rotateAngle =  rotateAngle ? parseInt(rotateAngle) : 0"></vs-input-number>

                                          <vs-button style="margin-right: 10px;margin-top: 5px;height: 40px" size="small" @click="rotateAngle = 0;">規程値に戻す</vs-button>
                                        </div>
                                      </div>
                                      <div style="position: relative;margin-top: 20px">
                                        <div class="stamp-setting-title">印鑑の濃さ</div>
                                        <div class="rotate-opacity">
                                          <div>
                                            <div style="padding-left: 10px;padding-right: 10px">
                                              <vs-slider v-model="opacity" :ticks="true" :min="0" :max="50"></vs-slider>
                                            </div>
                                            <div class="linear" ></div>
                                          </div>
                                          <vs-button style="margin-right: 10px;margin-top: 15px;height: 40px" size="small" @click="opacity = 0;">規程値に戻す</vs-button>

                                        </div>
                                      </div>
                                      <vs-row vs-justify="flex-end" style="margin-top: 5px;">
                                        <vs-button v-close-popover color="success" icon="add_circle_outline" style="padding:10px;" @click="updateRotateAngle">登録</vs-button>
                                      </vs-row>

                                    </template>
                                  </v-popover>
                                </vs-col>
                              </vs-row>
                              <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 5px 10px" :class="'stamp-list ' + (isEditScreen ? 'isEdit':'')">
                                <draggable v-model="stamps">
                                    <vs-col vs-w="6" v-for="stamp in stamps" :key="stamp.id">
                                        <div class="wrap-item row-equal-height" style="margin-bottom: 15px; display: flex; justify-content: center;">
                                            <div :class="'stamp-item ' + (stampSelected && stamp.id === stampSelected.id ? 'selected': '')" @click="clickStamp(stamp.id)" >
                                                <img :src="'data:image/png;base64,'+stamp.url" alt="stamp-img" v-tooltip.top-center="stamp.stamp_flg == 1 || stamp.stamp_flg == 3 ? stamp.stamp_name : ''">
                                        </div>
                                        </div>
                                    </vs-col>
                                </draggable>
                              </vs-row>
                              <template v-if="settingLimit?settingLimit.text_append_flg==1:false">
                                  <vs-row vs-type="flex" style="padding: 10px 10px 0">
                                      <div class="break"></div>
                                  </vs-row>
                                  <h2 class="title">テキスト追加</h2>
                                  <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 0 10px" class="stamp-text">
                                      <vs-col vs-w="4" :class="'stamp-item ' +  (isTextSelected ? 'selected': '')" >
                                          <img class="add-text-icon" width="100" height="100" :src="require('@assets/images/text_add.svg')" alt="stamp-img" v-on:click="selectText()">
                                      </vs-col>
                                  </vs-row>
                              </template>
                            </vs-tab>
                            <vs-tab label="コメント" class="comment-height" >
                              <div class="comments comment-height comment-position">
                                <vs-tabs class="comment-height" v-model="tab_comment_info">
                                  <vs-tab label="社内宛" class="comment-tab">
                                    <vs-row class="item comment-tab" v-for="(comment, index) in handleComments(0)" v-bind:key="comment.name + index" :index="index">
                                      <vs-col class="comment-panel" vs-w="12">
                                        <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                        <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                      </vs-col>
                                    </vs-row>
                                    <template v-for="(tempComment,index) in tempComments" class="item">
                                      <vs-row v-if="tempComment.private_flg==0" class="item" :key="index">
                                        <vs-col vs-w="11" class="comment-panel"><p :style="{whiteSpace: 'pre-line'}">{{tempComment.text}}</p></vs-col>
                                        <vs-col vs-w="1"><a style="cursor:pointer;" v-on:click="removeComment"><i class="fas fa-times"></i></a></vs-col>
                                      </vs-row>
                                    </template>
                                  </vs-tab>
                                  <vs-tab label="社外宛" class="comment-tab">
                                    <vs-row class="item comment-tab" v-for="(comment, index) in handleComments(1)" v-bind:key="comment.name + index" :index="index">
                                      <vs-col class="comment-panel" vs-w="12">
                                        <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                        <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                      </vs-col>
                                    </vs-row>
                                    <template v-for="(tempComment,index) in tempComments" class="item">
                                      <vs-row v-if="tempComment.private_flg==1" class="item" :key="index">
                                        <vs-col vs-w="11" class="comment-panel"><p :style="{whiteSpace: 'pre-line'}">{{tempComment.text}}</p></vs-col>
                                        <vs-col vs-w="1"><a style="cursor:pointer;" v-on:click="removeComment"><i class="fas fa-times"></i></a></vs-col>
                                      </vs-row>
                                    </template>
                                  </vs-tab>
                                </vs-tabs>

                                <div style="position: absolute;bottom:0;width:100%;">
                                  <vs-row>
                                    <vs-col vs-w="10">
                                      <vs-textarea style="margin-bottom:0px" placeholder="コメント記入欄" rows="2" v-model="documentComment" />
                                    </vs-col>
                                    <vs-col vs-w="2">
                                      <vs-button class="square comment-input" color="primary" type="filled" v-on:click="addComment()"> 登録</vs-button>
                                    </vs-col>
                                  </vs-row>
                                </div>
                              </div>
                            </vs-tab>
                          </vs-tabs>
                        </div>
                        <div v-else class ="tab-home" >
                            <vs-tabs class="tab-parent comment-height cirInfo" v-model="tab_cir_info">
                                <vs-tab label="印鑑">
                                    <div class="stamp-tab">
                                        <div class="stamps-processing" style="position:relative;z-index:100;border:1px solid #dcdcdc;border-radius:30px;padding-left:15px;padding-right:15px;background-color:#000000;width:max-content;margin:auto;text-align:center;">
                                          <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="undoAction" :disabled="disabledUndo">
                                              <i class="fas fa-undo-alt" style="color:#ffffff;margin-right: 5px;"></i><br> 元に戻す</vs-button>
                                          <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="AddStampsConfirmation(currentPageNo)" :disabled="this.$store.state.home.disabledProceed">
                                              <i class="fa" style="color:#ffffff;margin-right: 5px;">&#xf01e;</i><br> やり直し</vs-button>
                                        </div>
                                        <vs-row vs-type="flex" style="padding: 10px 10px 0">
                                            <div class="break"></div>
                                        </vs-row>
                                        <h2 class="title">ご利用可能な印鑑</h2>
                                        <vs-row vs-type="flex" vs-align="center" vs-justify="center" vs-w="12" style="padding: 10px;position: relative">
                                            <vs-col :vs-w="(!receivedOnlyFlg) ? 6 : 12">
                                                <flat-pickr style="position: absolute;z-index: 1;width: 50px;right:10px;border:0;color:#fff" :config="configdateTimePicker" v-model="date" @on-change="onChangeStampDate" />
                                                <vs-button class="square" :style="(!receivedOnlyFlg) ? {width: '95%'}:{width: '100%'}" data-toggle color="primary" type="filled" v-show="!(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0))">捺印日付変更</vs-button>
                                            </vs-col>
                                            <vs-col v-if="(!receivedOnlyFlg)" :vs-w="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0)) ? 12 : 6">
                                                <v-popover
                                                    offset="0"
                                                    :auto-hide="false"
                                                    :popoverClass="['change-stamp-angle']"
                                                >
                                                    <vs-button class="square tooltip-target b3 change-angle-stamp" color="primary" type="filled" :style="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0)) ?{width: '100%'}:{width: '95%'}">捺印設定</vs-button>
                                                    <template slot="popover">
                                                    <div style="position: relative;margin-top: 20px" v-if="rotateAngleFlg">
                                                      <div class="stamp-setting-title">印鑑の角度</div>
                                                      <div class="rotate-angle">
                                                        <vs-input-number  id="rotate_angle" min="0" max="360" style="width: 33%; margin-top: 15px;" v-model="rotateAngle" @change="rotateAngle =  rotateAngle ? parseInt(rotateAngle) : 0"></vs-input-number>

                                                        <vs-button style="margin-right: 10px;margin-top: 5px;height: 40px" size="small" @click="rotateAngle = 0;">規程値に戻す</vs-button>
                                                      </div>
                                                    </div>
                                                    <div style="position: relative;margin-top: 20px">
                                                      <div class="stamp-setting-title">印鑑の濃さ</div>
                                                      <div class="rotate-opacity">
                                                        <div>
                                                          <div style="padding-left: 10px;padding-right: 10px">
                                                            <vs-slider v-model="opacity" :ticks="true" :min="0" :max="50"></vs-slider>
                                                          </div>
                                                          <div class="linear"></div>
                                                        </div>
                                                        <vs-button style="margin-right: 10px;margin-top: 15px;height: 40px" size="small" @click="opacity = 0;">規程値に戻す</vs-button>

                                                      </div>
                                                    </div>
                                                    <vs-row vs-justify="flex-end" style="margin-top: 5px;">
                                                            <vs-button v-close-popover color="success" icon="add_circle_outline" style="padding:10px;" @click="updateRotateAngle">登録</vs-button>
                                                        </vs-row>

                                                    </template>
                                                </v-popover>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 5px 10px" :class="'stamp-list ' + (isEditScreen ? 'isEdit':'')">
                                            <vs-col vs-w="6" v-for="stamp in stamps" :key="stamp.id">
                                                <div class="wrap-item row-equal-height" style="margin-bottom: 15px; display: flex; justify-content: center;">
                                                    <div :class="'stamp-item ' + (stampSelected && stamp.id === stampSelected.id ? 'selected': '')" @click="clickStamp(stamp.id)" >
                                                        <img :src="'data:image/png;base64,'+stamp.url" alt="stamp-img" v-tooltip.top-center="stamp.stamp_flg == 1 || stamp.stamp_flg == 3 ? stamp.stamp_name : ''">
                                                    </div>
                                                </div>
                                            </vs-col>
                                        </vs-row>
                                        <template v-if="(circular?circular.text_append_flg==1:false)&&(companyLimit?companyLimit.text_append_flg==1:false)">
                                            <vs-row vs-type="flex" style="padding: 10px 10px 0">
                                                <div class="break"></div>
                                            </vs-row>
                                            <h2 class="title">テキスト追加</h2>
                                            <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 0 10px" class="stamp-text">
                                                <vs-col vs-w="4" :class="'stamp-item ' +  (isTextSelected ? 'selected': '')" >
                                                    <img class="add-text-icon" width="100" height="100" :src="require('@assets/images/text_add.svg')" alt="stamp-img" v-on:click="selectText()">
                                                </vs-col>
                                            </vs-row>
                                        </template>
                                    </div>
                                </vs-tab>
                                <vs-tab label="回覧先">
                                    <div class="mail-list">
                                        <p><strong>ファイル名</strong></p>
                                        <p class="filename">{{fileSelected ? fileSelected.name : ''}}</p>
                                        <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + ((circularUserSendBack && user.id === circularUserSendBack.id) ? ' sendback': '')" vs-type="flex"
                                                v-for="(user, index) in setShowSelectUsers" v-bind:key="user.email + index" :index="index">
                                            <vs-col vs-w="10">
                                                <p>{{user.name}} <span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                <p>{{user.email}}</p>
                                            </vs-col>
                                            <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                <p v-if="index === selectUsers.length - 1" href="#" class="final">最終</p>
                                                <a v-if="circularUserLastSend && circularUserLastSend.id === user.id" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                            </vs-col>
                                        </vs-row>
                                        <!--plan users start-->
                                        <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + ((circularUserSendBack && user.id === circularUserSendBack.id) ? ' sendback': '')" vs-type="flex" v-for="(user, index) in hasPlanCircularUsers" v-bind:key="user.email + index" :index="index">
                                            <template v-if="!user.plan_users">
                                                <vs-col vs-w="10">
                                                    <p>{{user.name}}<span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                    <p>{{user.email}}</p>
                                                </vs-col>
                                                <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                    <p v-if="hasPlanCircularUsers.length>0 && index === (hasPlanCircularUsers.length - 1)" href="#" class="final">最終</p>
                                                    <a v-if="circularUserLastSend && circularUserLastSend.id === user.id" href="#"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                            </template>
                                            <template v-else>
                                                <vs-col vs-w="10">
                                                    <p>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</p>
                                                    <template v-for="(user, itemIndex) in user.plan_users" v>
                                                        <p :key="itemIndex">{{user.name}} 【{{user.email}}】<span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                    </template>
                                                </vs-col>
                                                <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                    <p v-if="index === hasPlanCircularUsers.length - 1" href="#" class="final">最終</p>
                                                    <template v-for="(user, itemIndex) in user.plan_users">
                                                        <a v-if="circularUserLastSend && circularUserLastSend.id === user.id" class="ml-1" href="#" :key="itemIndex"> <i class="far fa-flag"></i></a>
                                                    </template>
                                                </vs-col>
                                            </template>
                                        </vs-row>
                                        <!--plan users end-->
                                        <!-- template route users start -->
                                        <vs-row class="item sended maker" v-if="isTemplateCircular">
                                            <vs-col vs-w="10">
                                                <p>{{selectUsers[0].name}} <span class="final" v-if="selectUsers[0].circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                <p>{{selectUsers[0].email}}</p>
                                            </vs-col>
                                            <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                <p v-if="1 === selectUsers.length" href="#" class="final">最終</p>
                                                <a v-if="circularUserLastSend && circularUserLastSend.id === selectUsers[0].id" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                            </vs-col>
                                        </vs-row>
                                        <template v-if="isTemplateCircular">
                                          <vs-row :class="'item sended ' + ((circularUserSendBack && userRoute[0].child_send_order === circularUserSendBack.child_send_order) ? ' sendback': '')" vs-type="flex" v-for="(userRoute, index) in templateUserRoutes" :index="index"
                                          v-bind:key="userRoute[0].user_routes_name + index">
                                              <vs-col vs-w="10">
                                                  <p>{{userRoute[0].user_routes_name}}</p>
                                                  <template v-for="(user, itemIndex) in userRoute">
                                                      <p :key="itemIndex">{{user.name}} 【{{user.email}}】<span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                  </template>
                                              </vs-col>
                                              <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                  <p v-if="index === templateUserRoutes.length - 1" href="#" class="final">最終</p>
                                                  <template v-for="(user, itemIndex) in userRoute">
                                                      <a v-if="circularUserLastSend && circularUserLastSend.id === user.id" class="ml-1" href="#" :key="itemIndex"> <i class="far fa-flag"></i></a>
                                                  </template>
                                              </vs-col>
                                          </vs-row>
                                        </template>
                                        <!-- template route users end -->
                                        <!-- 特設サイト受取側組織名表示 start -->
                                        <vs-row v-if="selectUsers && selectUsers.length > 0 && specialCircularFlg && !specialCircularReceiveFlg" class="item unsend" vs-type="flex">
                                          <vs-col vs-w="10">
                                            <p>{{groupName}}</p>
                                            <p></p>
                                          </vs-col>
                                          <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                            <p href="#" class="final">最終</p>
                                            <a v-if="circularUserLastSendIdIsSpecial" href="#"> <i class="far fa-flag"></i></a>
                                          </vs-col>
                                        </vs-row>
                                        <!-- 特設サイト受取側組織名表示 end -->
                                        <!-- 申請者 -->
                                        <vs-row v-if="selectUsers && selectUsers.length > 0" class="item unsend" vs-type="flex">
                                            <vs-col vs-w="10">
                                                <p>{{selectUsers[0].name}}</p>
                                                <p>{{selectUsers[0].email}}</p>
                                            </vs-col>
                                        </vs-row>
                                    </div>
                                </vs-tab>
                                <vs-tab label="コメント" class="comment-height"  v-on:click="clickComments()">
                                    <div class="comments comment-height comment-position">
                                        <vs-tabs class="comment-height" v-model="tab_comment_info">
                                          <vs-tab label="社内宛" class="comment-tab">
                                            <vs-row class="item" v-for="(comment, index) in handleComments(0)" v-bind:key="comment.name + index" :index="index">
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                            <template v-for="(tempComment,index) in tempComments" class="item">
                                              <vs-row v-if="tempComment.private_flg==0" class="item" :key="index">
                                                <vs-col vs-w="11" class="comment-panel"><p :style="{whiteSpace: 'pre-line'}">{{tempComment.text}}</p></vs-col>
                                                <vs-col vs-w="1"><a style="cursor:pointer;" v-on:click="removeComment"><i class="fas fa-times"></i></a></vs-col>
                                              </vs-row>
                                            </template>
                                          </vs-tab>
                                          <vs-tab label="社外宛" class="comment-tab">
                                            <vs-row class="item" v-for="(comment, index) in handleComments(1)" v-bind:key="comment.name + index" :index="index">
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                            <template v-for="(tempComment,index) in tempComments" class="item">
                                              <vs-row v-if="tempComment.private_flg==1" class="item" :key="index">
                                                <vs-col vs-w="11" class="comment-panel"><p :style="{whiteSpace: 'pre-line'}">{{tempComment.text}}</p></vs-col>
                                                <vs-col vs-w="1"><a style="cursor:pointer;" v-on:click="removeComment"><i class="fas fa-times"></i></a></vs-col>
                                              </vs-row>
                                            </template>
                                          </vs-tab>
                                        </vs-tabs>

                                      <div style="position: absolute;bottom:0;width:100%;">
                                        <vs-row>
                                          <vs-col vs-w="10">
                                            <vs-textarea style="margin-bottom:0px" placeholder="コメント記入欄" rows="2" v-model="documentComment" />
                                          </vs-col>
                                          <vs-col vs-w="2">
                                            <vs-button class="square comment-input" color="primary" type="filled" v-on:click="addComment()"> 登録</vs-button>
                                          </vs-col>
                                        </vs-row>
                                      </div>
                                    </div>
                                </vs-tab>
                                <vs-tab label="捺印履歴">
                                    <div class="histories">
                                        <vs-row class="item" v-for="(history, index) in histories.stamp" v-bind:key="history.email + index" :index="index"
                                        @click.native="onClickStampHistory(history)">
                                            <vs-col vs-w="3" class="p-2">
                                                <img :src="'data:image/png;base64,'+history.stamp_image" alt="stamp-img">
                                            </vs-col>
                                            <vs-col vs-w="9" vs-type="flex" vs-align="center" vs-justify="flex-start">
                                                <p class="w-full"><span class="user-name">{{history.name}}</span> <span class="date">{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span> <br/><span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span></p>
                                            </vs-col>
                                        </vs-row>


                                        <vs-row class="item" v-for="(history, index) in histories.text" v-bind:key="history.email + index + '_text'" :index="index">
                                            <vs-col vs-w="3" class="p-2 text-center">
                                                テキスト：
                                            </vs-col>
                                            <vs-col vs-w="9" vs-type="flex" vs-align="center" vs-justify="flex-start">
                                                <p class="w-full">
                                                    <strong style="word-wrap:break-word; overflow:hidden;display:block;" :title="history.text"  v-html="history.text"></strong>
                                                    <span class="user-name"  style="word-wrap:break-word; overflow:hidden;" >{{history.name}}</span>
                                                    <span class="date"  style="word-wrap:break-word; overflow:hidden;" >{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span>
                                                    <br/><span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span>
                                                </p>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row  v-if="histories.text" :style="`margin-top:5px`"></vs-row>
                                    </div>
                                </vs-tab>
                            </vs-tabs>
                            <vs-chip id="notice" v-if="commentsExist" color="danger" :style="{top:'2px',left:noticeLeft}"></vs-chip>
                        </div>
                        <a class="close-stamp-sidebar" v-on:click="onStampToolbarActiveChange(false)" href.prevent><i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="open-stamp-sidebar" :style="stampToolbarActive || specialButtonDisableFlg ? 'display:none':'' ">
                        <vs-col style="height: 100%" vs-type="flex" vs-align="center" vs-justify="center" vs-w="12">
                            <a href.prevent v-on:click="onStampToolbarActiveChange(true)"><i class="fas fa-angle-left"></i></a>
                        </vs-col>
                    </div>
                </vs-col>
                </div>
            </vs-row>
        </vs-card>
        <modal name="upload-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6', 'btn-groups']"
               :height="'auto'"
               :clickToClose="false">

            <vs-row class="mb-4 pb-2" vs-justify="flex-end" vs-align="center">
                <label>
                    <vs-dropdown>
                        <vs-button class="square mr-0" color="success" type="filled" :disabled="!uploadCompleted"><i class="fas fa-plus"></i> ファイル追加</vs-button>
                        <vs-dropdown-menu>
                            <vs-dropdown-item>
                                <vs-button v-if="settingLimit ? settingLimit.storage_local : 0 === 1" v-on:click="onAddMoreFileClick" color="primary" class="w-full download-item" type="filled"><i class="fas fa-upload"></i>  ローカル</vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box',circular_file)" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive',circular_file)" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <p>OneDrive</p></vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google',circular_file)" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox',circular_file)" class="w-full download-item" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="pdf"> <span class="download-item-text">Dropbox</span></vs-button>
                            </vs-dropdown-item>
                        </vs-dropdown-menu>
                    </vs-dropdown>
                </label>
            </vs-row>
            <vs-row class="mb-2 pb-4 pt-4 border-bottom">
                <vs-list class="vx-list mb-2 pb-6 border-bottom">
                    <div class="" v-for="(file, index) in fileUploads" v-bind:key="file.name + index" :index="index">
                        <vs-progress v-if="file.loading" indeterminate color="success"></vs-progress>
                        <vs-list-item v-if="!file.loading" :subtitle="`${index + 1}. ${file.name}`" >
                          <vs-chip :color="file.success ? 'success': 'danger'">{{file.success ? '成功': 'エラー'}}</vs-chip>
                        </vs-list-item>
                    </div>

                    </vs-list>
                    <p>ファイルの保存期間は366日間です。保存期間を過ぎたファイルは削除されます。必要なファイルは、保存期間内にダウンロードしてください。</p>
                </vs-row>

          <vs-row class="mt-2" vs-type="flex" vs-justify="flex-end" vs-align="center" :style="{flexDirection:'row',alignItems:'center'}">
            <vs-button class="square mr-2" style="padding: 0.75rem 1rem;" color="primary" type="filled" :disabled="!fileUploads || fileUploads.length <= 0 || !uploadCompleted || isProcessing" v-on:click="onChooseUpload"><i class="fas fa-arrow-right"></i> プレビュー・捺印へ</vs-button>
            <vs-dropdown class="preview-option" vs-trigger-click style="font-family: inherit !important;">
              <vs-button class="square mr-2" style="margin-left: -11px;padding: 0.75rem 1rem;"><i class="fa fa-caret-down" aria-hidden="true"></i></vs-button>
              <vs-dropdown-menu >
                <li class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" v-model="changeFlg" vs-value="default" v-on:click="changConvertFlg1">デフォルト</vs-radio></a></li>
                <li class="vx-dropdown--item" v-if="repagePreviewFlg"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" :disabled="!fileUploads || fileUploads.length !== 1 || !fileUploads[0].isExcel || !uploadCompleted" v-model="changeFlg" vs-value="pageChange" v-on:click="changConvertFlg2">レイアウトが崩れる場合</vs-radio></a></li>
              </vs-dropdown-menu>
            </vs-dropdown>

            <vs-button class="square mr-0" color="#bdc3c7" :style="{alignSelf:(repagePreviewFlg?'flex-start':'center'),fontWeight:''}" type="filled" :disabled="!uploadCompleted" v-on:click="onRejectUpload"><i class="fas fa-times"></i> アップロード中止</vs-button>
          </vs-row>
        </modal>

        <modal name="delete-doc-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="8" vs-type="flex" vs-align="center">
                    <h2 class="mb-2 pb-2" style="font-size: 18px;">回覧文書の削除</h2>
                </vs-col>
                <vs-col vs-w="4" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
                    <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="cancelConfirmDelete"> <i class="fas fa-times"></i></vs-button>
                </vs-col>
            </vs-row>
            <vs-row class="mb-3 pt-3" style="border-top: 1px solid #cdcdcd">
                <p class="mb-4">文書を削除してもよろしいですか？</p>
            </vs-row>
            <vs-row class="pt-3" vs-type="flex" vs-justify="flex-end" vs-align="center" style="border-top: 1px solid #cdcdcd">
                <vs-button class="square mr-2" color="danger" type="filled" v-on:click="acceptConfirmDelete"> 削除</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelConfirmDelete"> キャンセル</vs-button>
            </vs-row>
        </modal>

        <modal name="cloud-upload-modal"
               :pivot-y="0.2"
               :width="700"
               :classes="['v--modal', 'cloud-upload-modal', 'p-6']"
               :height="'auto'"
               @opened="onCloudModalOpened"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
                    <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="$modal.hide('cloud-upload-modal')"> <i class="fas fa-times"></i></vs-button>
                </vs-col>
            </vs-row>
            <vs-row>
                <vs-col vs-w="12" vs-type="block">
                    <img style="height: 40px" :src="cloudLogo" alt="Box">
                    <p><strong>{{cloudName}}にファイル保存</strong></p>
                </vs-col>
            </vs-row>
            <vs-row class="mb-3 pt-3">
                <vs-col vs-w="12" vs-type="flex" vs-justify="flex-start" vs-align="center" class="breadcrumb-container">
                    <vs-breadcrumb>
                        <li v-for="(item, index) in breadcrumbItems" v-bind:key="item.id + index" :index="index">
                            <a href="#" v-if="!item.active" v-on:click="onBreadcrumbItemClick(item.id)">{{decodeURIComponent(item.title)}} <span v-if="!item.active" class="vs-breadcrum--separator">/</span></a>
                            <p v-if="item.active">{{decodeURIComponent(item.title)}}</p>
                        </li>
                    </vs-breadcrumb>
                </vs-col>
                <vs-col vs-w="12" class="files pt-3 pb-3 vs-con-loading__container" id="cloudItems">
                    <vs-list >
                        <vs-list-item v-for="(file, index) in cloudFileItems" v-bind:key="file.id + index" :index="index">
                            <img v-on:click="onCloudItemClick(file)" v-if="file.type === 'folder'" style="height: 25px" :src="require('@assets/images/folder.svg')">
                            <img v-if="file.type === 'pdf'" style="height: 25px" :src="require('@assets/images/pdf.png')">
                            <a v-on:click="onCloudItemClick(file)" v-if="file.type === 'folder'" href="#">{{file.filename}}</a>
                            <p v-if="file.type === 'pdf'" href="#">{{file.filename}}</p>
                        </vs-list-item>
                    </vs-list>
                </vs-col>
            </vs-row>
            <vs-row class="mt-3 pt-6" vs-type="flex" style="border-top: 1px solid #cdcdcd">
                <vs-col vs-w="3" vs-type="flex" vs-justify="flex-end" vs-align="center" class="pr-6"><label><strong>ファイル名:</strong></label></vs-col>
                <vs-col vs-w="9" vs-type="flex" vs-justify="flex-start" vs-align="center"><vs-input class="inputx w-full" placeholder="ファイル名" v-model="filename_upload" /></vs-col>
            </vs-row>
            <vs-row class="pt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="success" type="filled" v-on:click="onUploadCheck()" :disabled="!filename_upload"> ファイル保存</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="$modal.hide('cloud-upload-modal')"> キャンセル</vs-button>
            </vs-row>
        </modal>

        <modal name="updatecheck-doc-modal"
            :pivot-y="0.2"
            :width="500"
            :classes="['v--modal', 'upload-modal', 'p-6']"
            :height="'auto'"
            :clickToClose="false">
            <UpdateCheckDocModalInner
              @cancel="cancelConfirmUpdate"
              @accept="onUploadToCloudClick(true)"
            />
        </modal>

        <modal name="upload-from-external-modal"
               :pivot-y="0.2"
               :width="700"
               :classes="['v--modal', 'upload-from-external-modal', 'p-6']"
               :height="'auto'"
               @opened="onUploadFromCloudModalOpened"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
                    <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="$modal.hide('upload-from-external-modal')"> <i class="fas fa-times"></i></vs-button>
                </vs-col>
            </vs-row>
            <vs-row>
                <vs-col vs-w="12" vs-type="block">
                    <img style="height: 40px" :src="cloudLogo" alt="Box">
                    <p><strong>{{cloudName}}からファイルアップロード</strong></p>
                </vs-col>
            </vs-row>
            <vs-row class="mb-3 pt-3">
                <vs-col vs-w="12" vs-type="flex" vs-justify="flex-start" vs-align="center" class="breadcrumb-container">
                    <vs-breadcrumb>
                        <li v-for="(item, index) in breadcrumbItems" v-bind:key="item.id + index" :index="index">
                            <a href="#" v-if="!item.active" v-on:click="onUploadFromCloudBreadcrumbClick(item.id)">{{decodeURIComponent(item.title)}} <span v-if="!item.active" class="vs-breadcrum--separator">/</span></a>
                            <p v-if="item.active">{{decodeURIComponent(item.title)}}</p>
                        </li>
                    </vs-breadcrumb>
                </vs-col>
                <vs-col vs-w="12" class="files pt-3 pb-3 vs-con-loading__container " id="itemsCloudToUpload">
                    <vs-list >
                        <vs-list-item v-for="(file, index) in cloudFileItems" v-bind:key="file.id + index" :index="index">
                            <img v-on:click="onUploadFromCloudFolderClick(file)" v-if="file.type === 'folder'" style="height: 25px" :src="require('@assets/images/folder.svg')">
                            <img v-if="file.type === 'pdf'" style="height: 25px" :src="require('@assets/images/pdf.png')">
                            <img v-else-if="excelSupportExtensions.includes(file.type)" style="height: 25px" :src="require('@assets/images/excel.svg')">
                            <img v-else-if="wordSupportExtensions.includes(file.type)" style="height: 25px" :src="require('@assets/images/word.svg')">
                            <img v-else-if="file.type !== 'folder'" style="height: 25px" :src="require('@assets/images/unresolve_file.svg')">
                            <a @click="onUploadFromCloudFolderClick(file)" v-if="file.type === 'folder'" href="#">{{file.filename}}</a>
                            <a v-if="isAttachmentFlg && file.type !== 'folder'" href="#" @dblclick="onUploadAttachmentFromCloud(file.id, file.filename)" :class="file.id === fileid_selected_from_cloud ? 'vs-file-item-selected' : '' "  @click="addToFileUpload(file.id, file.filename)">{{file.filename}}</a>
                            <a v-else-if="!isAttachmentFlg && file.type !== 'folder'" href="#" @dblclick="onUploadFromCloud(file.id, file.filename)" :class="file.id === fileid_selected_from_cloud ? 'vs-file-item-selected' : '' "  @click="addToFileUpload(file.id, file.filename)">{{file.filename}}</a>
                        </vs-list-item>
                    </vs-list>
                </vs-col>
            </vs-row>
            <vs-row class="mt-3 pt-6" vs-type="flex" style="border-top: 1px solid #cdcdcd">
                <vs-col vs-w="3" vs-type="flex" vs-justify="flex-end" vs-align="center" class="pr-6"><label><strong>ファイル名:</strong></label></vs-col>
                <vs-col vs-w="9" vs-type="flex" vs-justify="flex-start" vs-align="center"><vs-input class="inputx w-full" v-model="filename_selected_from_cloud" /></vs-col>
            </vs-row>
            <vs-row class="pt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="success" type="filled" v-if="isAttachmentFlg" v-on:click="onUploadAttachmentFromCloud(fileid_selected_from_cloud, filename_selected_from_cloud)" :disabled="!fileid_selected_from_cloud">開く</vs-button>
                <vs-button class="square mr-2" color="success" type="filled" v-else v-on:click="onUploadFromCloud(fileid_selected_from_cloud, filename_selected_from_cloud)" :disabled="!fileid_selected_from_cloud">開く</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="$modal.hide('upload-from-external-modal')">キャンセル</vs-button>
            </vs-row>
        </modal>

        <modal name="rename-file-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               :clickToClose="false">
          <vs-row class="mb-4">
            <vs-input class="w-full" v-model="file_name" />
          </vs-row>
          <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button class="square mr-2" color="primary" type="filled" :disabled="!file_name" v-on:click="onSubmitRenameFileNme"> OK</vs-button>
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelRenameFileNme"> キャンセル</vs-button>
          </vs-row>
        </modal>

        <modal name="over-file-size-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'sync-operation-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="block">
                    <p>{{max_attachment_size}}MB以上はアップロードできない</p>
                </vs-col>
            </vs-row>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onOverFileSizeClick" > 閉じる</vs-button>
            </vs-row>
        </modal>

        <modal name="add-attachment-modal"
               :pivot-y="0.2"
               :width="700"
               :classes="['v--modal', 'upload-modal']"
               :height="'auto'"
               style="border-radius: 11px"
               :clickToClose="false" >
            <header class="vs-popup--header">
                <div class="vs-popup--title"><h3>添付ファイル</h3></div>
            </header>

             <vs-row class="pdf-content" style="height:20%;padding-top: 3%;border: 0px;">
                 <!--PAC_5-2380 START-->
                 <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                     <label for="onUploadAttachment" class="pb-5" style="color: #FF0000; font-size: 17px">
                         <strong>同一企業内のみ確認できる文書です 。</strong>
                     </label>
                 </vs-row>
                 <!--PAC_5-2380 END-->
                    <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="upload-wrapper">
                        <div class="vx-col w-full md:w-5/6 upload-box" style="height: 100px; border-radius: 10px;border: 3px dashed #d1ecff;padding-top: 30px;padding-bottom: 30px;" id="dropZone1" >
                            <label class="wrapper" style="padding: 0px 0px 0px;" for="onUploadAttachment">
                                <input type="file" ref="onUploadAttachment" multiple accept="*/*" id="onUploadAttachment" v-on:change="onUploadAttachment" />
                                <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                                    <label for="onUploadAttachment" class="pb-5"><strong>クリックしてファイルを選択してください</strong></label>
                                </vs-row>
                            </label>
                        </div>
                        <vs-divider color="primary" style="font-size:1.5rem;padding-top: 15px;">クラウドストレージからファイルを選択</vs-divider>
                        <vs-row
                                vs-align="center"
                                vs-type="flex" vs-justify="center" vs-w="12">
                            <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                            <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <span class="download-item-text">OneDrive</span></vs-button>
                            <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                            <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="Dropbox"> <span class="download-item-text">Dropbox</span></vs-button>
                        </vs-row>

                    </vs-row>
                </vs-row>
             <vs-row class="mb-2 pb-4 pt-4 " >
                    <vs-list class="vx-list mb-2 pb-6 " style="padding-left: 15%">
                        <div class="" v-for="(file, index) in attachmentUploads" v-bind:key="file.file_name + index" :index="index">
                            <vs-progress v-if="file.loading" indeterminate color="success"></vs-progress>
                            <vs-row class="mb-3">
                                <vs-col vs-w="5">
                                     {{index+1}}.<a  href="#" class="link" style="text-decoration:none;color:#000000;word-wrap:break-word;"  v-on:click="onDownloadAttachment(index)">&nbsp;{{ file.file_name }}&nbsp;&nbsp;&nbsp;</a>
                                    <a href="#" v-if="file.create_user_id == userInfo.mst_user_id"  v-on:click="onDeleteAttachment(index)"><i color="#000" class="fas fa-trash-alt" aria-hidden="true" ></i></a>
                                </vs-col>
                                <vs-col vs-w="1"></vs-col>
                                <vs-col vs-w="1" v-if="file.create_user_id == userInfo.mst_user_id && false" vs-align="center" ><vs-checkbox  v-model="file.confidential_flg"  v-on:click="onAttachmentConfidentialFlgClick(index)"></vs-checkbox></vs-col>
                                <vs-col vs-w="3" v-if="file.create_user_id == userInfo.mst_user_id && false" ><p v-on:click="onAttachmentConfidentialFlgClick(index)" class="confidential-label">社外秘に設定</p></vs-col>
                            </vs-row>
                        </div>
                    </vs-list>
                </vs-row>

                <vs-row>
                    <vs-col><hr style='border:1.5px inset #fff;'></vs-col>
                </vs-row>

                <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center" style="padding-right: 2%;padding-bottom: 3%;">
                    <vs-button class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="closeAttachmentModal"><i class="fa fa-times" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>閉じる</vs-button>
                </vs-row>

        </modal>

        <vs-popup
          title="名刺情報"
          :active.sync="showBizcardInfo">
          <BizcardPopUpInner :bizcardData="bizcardData"/>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="選択文書ダウンロード予約" :active.sync="confirmDownload">
          <vs-row>
            <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="downloadReserveFilename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
          </vs-row>
          <div class="mt-3 text-red">※ダウンロードが行われていない回覧です。</div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
              <vs-button @click="onDownloadReserve" color="primary">ダウンロード予約</vs-button>
              <vs-button @click="confirmDownload=false" color="dark" type="border">キャンセル</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>
	</div>

        <!-- 5-277 mobile html -->
        <div id="main-home-mobile" class="create_new">
            <div style="width:100%;" v-if="files.length">
                <span v-if="!showEdit" @click="goBack"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
                <span v-if="showEdit" @click="showEdit=false"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
                <div class="docName" style="display:inline;position:absolute;top:5px;">{{docName}}</div>
            </div>
            <div style="width:100%;height:60px;padding-top:20px;" v-if="selectUsers && selectUsers.length > 0">From : {{selectUsers && selectUsers.length > 0 ? selectUsers[0].name : ''}}<span style="float: right;">{{selectUsers[0].create_at | moment("YYYY/MM/DD")}}</span></div>
            <div style="width:100%;height:60px;padding-top:20px;" v-else-if="files.length"> </div>
            <div v-if="!showEdit">
                <div class="tools" :style="(!stampToolbarActive || selectUsers.length == 0) ? 'display:none':'' ">
                    <div>
                        <div style="padding: 0">
                            <div>
                                <div>
                                    <div id="mail-list-label" class="tab-active" v-on:click="changeTabMail()">回覧先</div>
                                    <div id="comments-label" class="tab" v-on:click="changeTabComments()">コメント</div>
                                </div>
                                <div>
                                    <div class="mail-list">
                                        <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + ((circularUserSendBack && user.id === circularUserSendBack.id) ? ' sendback': '')" vs-type="flex" v-for="(user, index) in selectUsers" v-bind:key="user.email + index" :index="index">
                                            <vs-col vs-w="1" vs-align="center">
                                                <a v-if="circularUserLastSend && circularUserLastSend.id === user.id" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                            </vs-col>
                                            <vs-col vs-w="5">
                                                <p>{{user.name}} <span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span>
                                                    <span v-if="index === selectUsers.length - 1" href="#" class="final">最終</span>
                                                </p>
                                            </vs-col>
                                            <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                                <p>依頼日：{{user.update_at | moment("YYYY/MM/DD")}}</p>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row v-if="selectUsers && selectUsers.length > 0" class="item unsend" vs-type="flex">
                                            <vs-col vs-type="flex" vs-w="1" vs-align="center">
                                            </vs-col>
                                            <vs-col vs-w="5">
                                                <p>{{selectUsers[0].name}}</p>
                                            </vs-col>
                                            <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                                <p>依頼日：{{selectUsers[0].create_at| moment("YYYY/MM/DD")}}</p>
                                            </vs-col>
                                        </vs-row>

                                        <vs-row v-else class="item unsend" vs-type="flex" :data="loginUser.family_name">
                                            <vs-col vs-type="flex" vs-w="1" vs-align="center">
                                            </vs-col>
                                            <vs-col vs-w="5">
                                                <p>{{loginUser.family_name}} {{loginUser.given_name}}</p>
                                            </vs-col>
                                            <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                                <p>依頼日：{{new Date() | moment("YYYY/MM/DD")}}</p>
                                            </vs-col>
                                        </vs-row>
                                <div>
                                    <div class="comments" style="display: none;">
                                        <vs-row  class="item" style="width: 330px; height: 130px; overflow: scroll;" v-for="(comment, index) in commentsFilter" v-bind:key="comment.name + index" :index="index">
                                            <vs-col class="p-3" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD')}}</span></p>
                                                <div :style="{whiteSpace: 'pre-line'}"><div class="comment_text" >{{comment.text}}</div></div>
                                            </vs-col>
                                        </vs-row>
                                    </div>
                                </div>
                    </div>
                  </div>
                  <div>
                    <div class="comments" style="display: none;">
                      <vs-row  class="item" style="width: 330px; height: 130px; overflow: scroll;" v-for="(comment, index) in commentsFilter" v-bind:key="comment.name + index" :index="index">
                        <vs-col class="p-3" vs-w="12">
                          <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD')}}</span></p>
                          <div :style="{whiteSpace: 'pre-line'}"><div class="comment_text" >{{comment.text}}</div></div>
                        </vs-col>
                      </vs-row>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="files.length && !showEdit" class="btn_dialog">
          <div v-on:click="approvalFile"><div><img :src="require('@assets/images/mobile/approval.svg')"></div><div class="label">回覧設定</div></div>
          <div :style="(isCreateScreen || (circularUserLastSend && circularUserLastSend.child_send_order === 0)) ? 'display:none':''" :disabled="files.length <= 0" v-on:click="goToSendback"><div><img :src="require('@assets/images/mobile/refund.svg')"></div><div class="label">差戻し</div></div>
          <div :style="(!isCreateScreen && circularUserLastSend && circularUserLastSend.child_send_order === 0) ? '':'display:none'" :disabled="files.length <= 0" v-on:click="onDiscardCircularClick"><div><img :src="require('@assets/images/mobile/delete.svg')"></div><div class="label">回覧破棄</div></div>
          <div v-on:click="docEdit"><div><img :src="require('@assets/images/mobile/edit.svg')"></div><div class="label">文書編集</div></div>
        </div>
        <div v-if="files.length && showEdit" class="btn_dialog tool edit" @touchstart="handleDialogStart" @touchend="handleDialogEnd"> <!--  @touchstart="handleDialogStart" @touchend="handleDialogEnd" -->
          <div class="btn_dialog_status" v-on:click="handleClickDialogStatus" @touchstart="dragStartDialogStatus" @touchmove="dragMoveDialogStatus" @touchend="dragEndDialogStatus"> <span :class="'status_name '+(isTextSelected?'text':'')">{{ isTextSelected ? 'T' : '印' }}</span> <span class="status_icon"><i style="color: #0984e3;" class="fa fa-cloud"></i></span> </div>
          <div v-on:click="showStamps"><div class="icon"><img :src="require('@assets/images/mobile/stamp.svg')"></div><div class="label">押印</div></div>
          <div type="input" v-on:click="selectTextCustom(currentPageNo)"><div class="icon"><img :src="require('@assets/images/mobile/text.png')"></div><div class="label">テキスト</div></div>

          <div v-on:click="approvalFile"><div class="icon"><i class="far fa-envelope" style="color:#107fcd;"></i></div><div class="label">回覧設定</div></div>

          <div @click="undoAction">
            <div class="icon"><i class="fas fa-undo-alt"></i></div><div class="label">元に戻す</div>
          </div>
          <div @click="AddStampsConfirmationMobile(currentPageNo)">
            <div class="icon"><i class="fa">&#xf01e;</i></div><div class="label">やり直し</div>
          </div>


          <!--
          <div v-on:click="undoAction" :disabled="disabledUndo"><div><img :src="require('@assets/images/mobile/back.svg')"></div><div>戻る</div></div>  PAC_5-2144 戻るボタンで捺印・テキスト取り消し

          <div v-on:click="showEdit=false"><div><img :src="require('@assets/images/mobile/confirm.svg')"></div><div>確定</div></div>
          -->
        </div>
        <div class="work-content">
          <!-- ファイル・ページナビゲーション 表示
          <div class="docName" style="text-align: center; margin:0 auto; line-height: 40px;">{{files.length?files[0].name:''}}</div>
          -->

          <div class="tabSelected" v-if="files.length && showEdit">
            <div>
              <select v-model="tabSelected" @change="changeSelectedFile(tabSelected)">
                <option v-for="(file,index) in files" :value="index" :key="index" :selected="(index==0)?'selected':''">{{file.name}}</option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="#0984e3" d="M311.9 335.1l-132.4 136.8C174.1 477.3 167.1 480 160 480c-7.055 0-14.12-2.702-19.47-8.109l-132.4-136.8C-9.229 317.8 3.055 288 27.66 288h264.7C316.9 288 329.2 317.8 311.9 335.1z"/></svg>
            </div>
          </div>

          <vs-row v-if="mobilePages.length > 0">

            <vs-col vs-type="flex" vs-lg="12" style="flex-wrap: wrap; height: 32px;">
              <div class="preview-list-page" style="height: 32px!important;">
                <div style="text-align: center;">
                  <div style="display:inline-block;float:left;" @click="changePage(currentPageNo-1)">
                    <vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon>
                  </div>
                  <div style="display:inline-block;position:relative;top:5px;">
                    {{currentPageNo}}/{{pages.length}}
                  </div>
                  <div style="display:inline-block;float:right;" @click="changePage(currentPageNo+1)">
                    <vs-icon icon="keyboard_arrow_right" size="medium" color="primary"></vs-icon>
                  </div>
                </div>
              </div>
            </vs-col>
          </vs-row>
          <vs-row>
            <vs-col vs-type="flex" style="transition: width .2s;width:100%;">
              <div class="pdf-content">
                <div v-show="fileSelected != null" class="content">
                  <div>
                    <div v-if="fileSelected != null">
                      <div class="page page_large" v-for="(page, index) in mobilePages" v-bind:key="index" :index="index" v-show="currentPageNo == index+1">
                        <pdf-page-editor-mobile @showBtn="showDialog" @hideBtn="hideDialog"
                          ref="editorMobile"
                          :data-index="index"
                          @prev="changePage(currentPageNo-1)" @next="changePage(currentPageNo+1)"
                          :showEdit="showEdit" :config="configKonvaMobile"
                          :page="page.editorParam" :imageUrl="page.imageUrl" :selected="currentPageNo == index+1"
                          :deleteFlg="fileSelected.del_flg" :deleteWatermark="fileSelected.delete_watermark"
                          :stamps="stampUsed" :isOptionText="isOptionText">
                        </pdf-page-editor-mobile>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </vs-col>
          </vs-row>
        </div>

        <modal name="stamps-modal"
               :pivot-y="0.2"
               :width="isMobile?500:300"
               :classes="'v--modal'"
               :height="'auto'"
               id="stamps-modal">
          <div class="mb-4 mt-4 date_stamp_config" style="text-align: center;" v-show="loginUser && loginUser.date_stamp_config === 1">
            <flat-pickr class="date_stamp_input" style="position: absolute;z-index: 1;width: 50px;right:10px;border:0;color:#fff" @on-open="MobileOpenFlatpickr" @on-close="MobileCloseFlatpickr" :config="configdateTimePicker" v-model="date" @on-change="onChangeStampDate" />
            <vs-button class="stamp-change-date" data-toggle color="primary" type="filled" v-show="!(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (loginUser && loginUser.date_stamp_config === 0))">日付印日時変更</vs-button>
          </div>
          <div class="stamp-list swiper-container mb-4">
            <swiper :options="swiperOption" class="swiper-wrapper" style="margin:0 40px;">
              <swiper-slide v-for="stamp in stamps" :key="stamp.id" style="width: 60px;">
                <div class="wrap-item" style="margin-bottom: 15px; display: flex; justify-content: center;">
                  <div :class=" 'stamp-item ' +(stampSelected && stamp.id === stampSelected.id ? 'selected': '')" @click="clickStamp(stamp.id)">
                    <img :src="'data:image/png;base64,'+stamp.url" alt="stamp-img">
                  </div>
                </div>
              </swiper-slide>
            </swiper>
            <div class="swiper-button-next" style="width: 13px;height:22px;background-size:13px 22px;"></div>
            <div class="swiper-button-prev" style="width: 13px;height:22px;background-size:13px 22px;"></div>
          </div>
        </modal>
      </div>
    </div>
</template>
<script>
import {mapActions, mapState} from "vuex";
import InfiniteLoading from 'vue-infinite-loading';
import PdfPages from "../../components/home/PdfPages";
import PdfPageThumbnails from "../../components/home/PdfPageThumbnails";
import PdfPageEditorMobile from "../../components/home/PdfPageEditorMobile";

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import {dragscroll} from 'vue-dragscroll'
import {CIRCULAR} from '../../enums/circular';
import {CIRCULAR_USER} from '../../enums/circular_user';
import Utils from '../../utils/utils';
import {getPageUtil} from '../../utils/pagepreview';

import config from "../../app.config";

import draggable from 'vuedraggable'
import 'swiper/dist/css/swiper.css'
import {swiper, swiperSlide} from 'vue-awesome-swiper'
import Axios from "axios";
import {cloneDeep} from "lodash/lang";
import UpdateCheckDocModalInner from "@/components/home/UpdateCheckDocModalInner";
import BizcardPopUpInner from "@/components/home/BizcardPopUpInner.vue";

const PPI = 150;

    export default {
        components: {
            InfiniteLoading,
            PdfPages,
            PdfPageThumbnails,
            PdfPageEditorMobile,
            flatPickr,
            draggable,
            swiper,
            swiperSlide,
            UpdateCheckDocModalInner,
            BizcardPopUpInner,
        },
        directives: {
            dragscroll
        },
        data() {
            return {
                isMobile: false,
                isTablet: false,
                CIRCULAR: CIRCULAR,
                CIRCULAR_USER: CIRCULAR_USER,
                histories: [],
                pages: [],
                thumbnails: [],
                currentPageNo: 1,
                maxTabShow: 5,
                rotateAngle: 0,
                opacity:0,
                configKonvaMobile: {
                    width: 400,
                    height: 0
                },
                tabSelected: 99999, // plus button
                date: null,
                configdateTimePicker: {
                    locale: Japanese,
                    wrap: true,
                    disableMobile: true
                },
                // PAC_5-2022 Start
                stampToolbarActive: false,
                stamp_load_flg: true,
                // PAC_5-2022 End
                access_code: '',
                file_name: '', // ファイル名
                old_file_name: '', // 元ファイル名
                isValidAccessCode: true,
                showAccessCodeMessage: false,
                settingLimit:{},
                fileUploads: [],
                isShowAttachment:true,
                attachmentUploads: [],
                attachmentAfterUploads: [],
                localUserId:0,
                localCircularUserId:0,
                isAttachmentFlg:false,
                circular_file:'circular',
                attachment_file:'attachment',
                fileAfterUploads: [],
                uploadCompleted: false,
                zoom: 100,
                deleteItem: null,
                deleteIndex: null,
                oldTabSelected: null,

                disabledUndo: true,
                filename_upload: '',
                cloudFileItems: [],
                breadcrumbItems: [],
                cloudLogo: null,
                cloudName: null,
                currentCloudFolderId: 0,
                enableAdd: true,
                checkNoExistCircularUser: true,
                userSendMailId: '',
                checkShowButtonApply: false,
                getNamePath: null,
                checkShowConfirmAddSignature: JSON.parse(getLS('user')).check_add_signature_time_stamp,
                receivedOnlyFlg: JSON.parse(getLS('user')).received_only_flg,
                rotateAngleFlg: JSON.parse(getLS('user')).rotate_angle_flg,
                repagePreviewFlg: JSON.parse(getLS('user')).repage_preview_flg,
                filename_selected_from_cloud: '',
                fileid_selected_from_cloud: 0,
                is_download_external:null,
                excelSupportExtensions: ['xls', 'xlt', 'xlm', 'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xla', 'xlam', 'xll', 'xlw'],
                wordSupportExtensions: ['doc', 'dot', 'wbk', 'docx', 'docm', 'dotx', 'dotm', 'docb'],
                tab_cir_info: 0,
                tab_cir_info_back: 0,
                tab_home_info: 0,
                tab_comment_info: 0, //社内社外宛先デフォルトtab
                showEdit:false,
                showInput:false,
                swiperOption:{
                    autoplay: false,
                    initialSlide: 0,
                    direction: 'horizontal',
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    loop: false,
                    loopedSlides: 3,
                    loopAdditionalSlides: 0,
                    slidesPerView: 5,
                    observer: true,
                    observeParents: true
                },
                userInfo:null,
                docName: '',
                clickState: false, // 二重チェック用
                documentComment:'', //社内社外宛先コメント
                // PAC_5-842　対応 ▲
                showBizcardInfo: false,
                bizcardData: null,
                base64_prefix: {
                    jpeg : "data:image/jpeg;base64,",
                    png : "data:image/png;base64,",
                    gif : "data:image/gif;base64,"
                },
                window: {
                    width: 0,
                    height: 0
                },
                firstIndentation: true,
                showLeftToolbar: true,
                saveId: null,//上書きファイルID
                commentsExist: false,
                commentsChecked:[], //コメント既読をした回覧ファイルID
                noticeLeft: '0px',
                noticeWidth: '10px', //お知らせアイコンサイズ
                is_ipad: false,
                viewerWidth: 1,
                thumbnailViewerWidth: 0,
                visiblePageRange: [-1, -1],
                visibleThumbnailRange: [-1, -1],
                isProcessing: false,
                stampDisplays: [],
                stampUsed: [],
                changeFlg: 'default',
                protectionSetting:{require_print:0},
                //PAC_5-1576-2 S
                InitPrintStampCount:0,
                //PAC_5-1576-2 E
                companyConfidentialFlg: 0,
                /*PAC_5-2288 S*/
                esigned_flg: 0,
                /*PAC_5-2288 E*/
                specialCircularReceiveFlg: false,//回覧ユーザーが特設サイトの受取側ですか
                circularUserLastSendIdIsSpecial: false,//現在未操作のユーザは特設サイトの受取側ですか
                specialCircularFlg:false,//特設サイト回覧
                specialButtonDisableFlg: false,//特設サイト申請画面、ボタン非アクティブ
                groupName: '',//特設サイト受取側組織名
                radioVal: "default",
                dialog_y: 0,
                isOptionText: false,
                confirmDownload: false,//選択文書ダウンロード予約 画面フラグ
                downloadReserveFilename: '',//ダウンロード予約のファイル名
                inputMaxLength: 46,//ファイル名の長さ
            }
        },
        computed: {
            ...mapState({
                title: state => state.home.title,
                files: state => state.home.files,
                fileSelected: state => state.home.fileSelected,
                stampSelected: state => state.home.stampSelected,
                isTextSelected: state => state.home.textSelected,
                circular: state => state.home.circular,
                addStampHistory: state => state.home.addStampHistory,
                companyLogos: state => state.home.company_logos,
                addTextHistory: state => state.home.addTextHistory,
                tempComments: state => state.home.tempComments, //社内社外宛先一時入力コメント
                deviceType: state => state.home.deviceType,
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
                registeredDocInfoList: state => {
                  if (!state.home.circular || state.home.circular.id !== state.pageBreaks.circularIdForRegisteredDocs) {
                    return [];
                  }
                  return state.pageBreaks.registeredDocInfoList;
                },
            }),
            setShowSelectUsers () {
              const $this = this
              return this.selectUsers.filter((item)=>!$this.isTemplateCircular && $this.hasPlanCircularUsers.length<=0 &&
                  (!$this.specialCircularFlg || ($this.specialCircularReceiveFlg ||  !item.special_site_receive_flg))
              )
            },
            commentsFilter() {
              return this.comments.filter(comment => comment.text && comment.text.trim())
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
            realOpacity(){
              return ( 1 - this.opacity/100);
            },
            addStampHistory: {
              get() {
                return this.$store.state.home.addStampHistory
              },
              set(value) {
                this.$store.commit('home/checkAddStampHistory', value);
              }
            },
            addTextHistory: {
                get() {
                    return this.$store.state.home.addTextHistory
                },
                set(value) {
                    this.$store.commit('home/checkAddTextHistory', value);
                }
            },
            circularUserLastSend() {
              if(!this.circular || !this.circular.users) {
                return null;
              }
              // 合議の場合
              let circular_user = null;
              if(this.loginUser && this.isTemplateCircular) {
                  // 終わった場合
                  if(this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_STATUS || this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS){
                      circular_user = null;
                  }else{
                      let circular_users = [];
                      // 差戻のcircular_user
                      let circular_user_send_back = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                      // 差戻の場合
                      circular_users = this.circular.users.slice()
                        .filter(item =>
                          (item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS ||
                          item.circular_status === CIRCULAR_USER.READ_STATUS ||
                          item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS ||
                          item.circular_status === CIRCULAR_USER.REVIEWING_STATUS));
                      if(circular_user_send_back){
                          // 差戻 同級のメール 除外する
                          circular_users = circular_users
                            .filter(item => item.child_send_order !== circular_user_send_back.child_send_order);
                      }


                      // get 処理中 child_send_order
                      let distinct_child_orders = [];
                      for(let i = 0; i < circular_users.length; i++){
                          if(!distinct_child_orders.includes(circular_users[i].child_send_order)){
                              // すべてのユーザ
                              let all_arr = this.circular.users.slice().filter(item => item.child_send_order === circular_users[i].child_send_order);
                              // 承認のユーザ
                              let approved_arr = this.circular.users.slice().filter(item => (item.child_send_order === circular_users[i].child_send_order && (item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS)));
                              if(all_arr.length > 1){
                                  // all承認
                                  if(approved_arr.length == all_arr.length){
                                      continue;
                                  }else{
                                      // 待つ
                                      if(all_arr[0].wait == 1){
                                          // 一つのノードが複数存在するからです。 loginUserのemail 選択  && item.email === this.loginUser.email
                                          const cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.loginUser.email);
                                          if(cir !== undefined){
                                              circular_user = cir;
                                          }else{
                                              circular_user = circular_users[i];
                                          }
                                          break;
                                      } else if (all_arr[0].wait == 0){
                                          if(approved_arr.length >= all_arr[0].score){
                                              continue;
                                          }else{
                                              // 一つのノードが複数存在するからです。 loginUserのemail 選択  && item.email === this.loginUser.email
                                              const cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.loginUser.email);
                                              if(cir !== undefined){
                                                  circular_user = cir;
                                              }else{
                                                  circular_user = circular_users[i];
                                              }
                                              break;
                                          }
                                      }
                                  }
                              }else{
                                  circular_user = circular_users[i];
                                  break;
                              }
                              distinct_child_orders[distinct_child_orders.length + 1] = circular_users[i].child_send_order;
                          }
                      }
                  }
              }else{
                  circular_user = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS  || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS  || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS);
              }
              this.$store.commit('home/updateCurrentParentSendOrder', circular_user?circular_user.parent_send_order:0);
              return circular_user;
            },
            circularUserSendBack() {
                if(!this.circular || !this.circular.users) {
                  return null;
                }
                const circular_user = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                if(!circular_user) return null;

                const findUser = this.selectUsers.find(item => circular_user.id === item.id);
                if(findUser) return circular_user;
                return this.selectUsers.find(item => circular_user.parent_send_order === item.parent_send_order && item.child_send_order === 1);
            },
            // DBから入力したコメント取得
            comments: {
              get() {
                return this.fileSelected && this.fileSelected.comments ? this.fileSelected.comments : []}
            },
            // 画面入力した一時コメント取得
            tempComments: {
              get() {
                return this.fileSelected && this.fileSelected.tempComments ? this.fileSelected.tempComments : []}
            },
            isCreateScreen: {
              get() {
                return !this.circular || this.circular.circular_status === CIRCULAR.SAVING_STATUS || this.circular.circular_status === CIRCULAR.RETRACTION_STATUS;
              }
            },
            isEditScreen: {
              get() {
                return this.circular && this.circular.circular_status !== CIRCULAR.SAVING_STATUS && this.circular.circular_status !== CIRCULAR.RETRACTION_STATUS;
              },
              set(value) {
                  this.value = value;
              }
            },
            loginUser: {
                get() {
                  if(this.$store.state.home.usingPublicHash) return {};
                  return JSON.parse(getLS('user'));
                }
            },
            selectUsers: {
                get() {
                  if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                  return this.$store.state.home.circular.users.filter(item => {
                    return item.child_send_order === 0 || this.loginUser.mst_company_id === item.mst_company_id || (item.parent_send_order && item.child_send_order === 1);
                  });
                },
            },
            confidentialFlg: {
              get() {
                if(!this.fileSelected) return 0;
                return this.fileSelected.confidential_flg;
              },
              set(value) {
                this.$store.commit('home/updateConfidentialFlg', value);
              }
            },
            overlayHiddenFlg: {
              get() {
                if(!this.fileSelected) return 0;
                return this.fileSelected.overlay_hidden_flg;
              },
              set(value) {
                this.$store.commit('home/updateOverlayHiddenFlg', value);
              }
            },
            currentCloudDrive: {
              get() {
                return this.$store.state.cloud.drive
              },
              set(value) {
                this.$store.commit('cloud/setDrive', value);
              }
            },
            isTemplateCircular: {
                get() {
                    let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                    let cnt = arrUsers.findIndex(function($item){
                        return (Object.prototype.hasOwnProperty.call($item, "user_routes_id") && $item.user_routes_id != null);
                    });
                    return cnt >= 0 ? true : false;
        		    },
            },
            templateUserRoutes: {
                get() {
                    let newArrUsers = [];
                    if(this.isTemplateCircular){
                        // 合議の場合、同じ企業、parent_send_order同じです
                      const arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                      for(let i = 1;i < arrUsers.length; i++){
                            let child_send_order = arrUsers[i].child_send_order - 1;
                            if(!Object.prototype.hasOwnProperty.call(newArrUsers, child_send_order)){
                                newArrUsers[child_send_order] = [];
                            }
                            arrUsers[i]['user_routes_name'] = JSON.parse(arrUsers[i].detail).summary;
                            newArrUsers[child_send_order].push(arrUsers[i]);
                        }
                    }
                    return newArrUsers;
                },
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
            max_attachment_size() {
                if(!this.userInfo) return null;
                return this.userInfo.max_attachment_size;
            },
            companyLimit() {
                return {text_append_flg: (this.circular && this.circular.limit_text_append_flg) ? this.circular.limit_text_append_flg : 0};
            },
            printedStampCount(){
                let printedStampCount=0
                if (this.$route.name=='save_detail'){
                    printedStampCount=printedStampCount+ this.InitPrintStampCount
                }
                this.$store.state.home.files.forEach(file=>{
                    file.pages.forEach(page=>{
                        printedStampCount+=page.stamps.length
                    })
                })
                return printedStampCount
            },
          hasPlanCircularUsers:{
            get() {
              if(!this.$store.state.home.circular || this.$store.state.home.circular.plans.length<=0) return [];
              let plan=JSON.parse(JSON.stringify(this.$store.state.home.circular.plans))
              let circularUsers=cloneDeep(this.selectUsers)

              circularUsers.forEach(user=>{
                if (plan[user.plan_id] && plan[user.plan_id].id==user.plan_id){
                  plan[user.plan_id].users=plan[user.plan_id].users || [];
                  plan[user.plan_id].users.push(Object.assign({},user));
                  plan[user.plan_id].is_add=false;
                }
              })

              circularUsers=circularUsers.map(user=>{

                if (plan[user.plan_id] && plan[user.plan_id].id==user.plan_id){
                  if (plan[user.plan_id].is_add){
                    return null
                  } else {
                    user.plan_mode=plan[user.plan_id].mode
                    user.plan_score=plan[user.plan_id].score
                    user.plan_users=plan[user.plan_id].users
                    plan[user.plan_id].is_add=true
                    return user
                  }
                } else {
                  return user
                }
              }).filter(user=>{
                return user!=null
              })
              return circularUsers
            },
          },
            /*PAC_5-2288 S*/
           showDownloadBtn(){
                let printedStampCount=0
                let printedTextsCount=0
                this.$store.state.home.files.forEach(file=>{
                    file.pages.forEach(page=>{
                        printedTextsCount+=page.texts.filter(_=>_.text.length>0).length
                        printedStampCount+=page.stamps.filter(_=>!_.selected).length
                    })
                })
                if (this.$route.name=='save_detail'){
                    if (this.esigned_flg == 1 && (printedTextsCount>0 || printedStampCount>0)){
                        return false
                    }
                    return true
                }
                if (this.$route.name=='received_detail'){
                    if (this.circular.is_other_env==1){
                        return false
                    }
                    if (this.circular.esigned_flg==1 && (printedTextsCount>0 || printedStampCount>0)){
                       return false
                    }
                    return true
                }
                return true
            },
            /*PAC_5-2288 E*/
        },
        methods: {
            ...mapActions({
                uploadFile: "home/uploadFile",
                acceptUpload: "home/acceptUpload",
                attachmentUpload: "home/attachmentUpload",
                deleteAttachment: "home/deleteAttachment",
                downloadAttachment:"home/downloadAttachment",
                attachmentConfidentialFlg:'home/attachmentConfidentialFlg',
                getAttachment: "home/getAttachment",
                downloadCloudAttachment:"cloud/downloadCloudAttachment",
                rejectUpload: "home/rejectUpload",
                getPage: "home/getPage",
                clearState: "home/clearState",
                selectFile: "home/selectFile",
                addEmptyFile: "home/addEmptyFile",
                closeFile: "home/closeFile",
                updateCurrentFileZoom: "home/updateCurrentFileZoom",
                getStamps: "home/getStamps",
                selectStamp: "home/selectStamp",
                selectText: "home/selectText",
                undoAction: "home/undoAction",
                saveFile: "home/saveFile",
                downloadFile: "home/downloadFile",
                updateStampDisplays: "home/updateStampDisplays",
                saveStampsOrder: "home/saveStampsOrder",
                saveFileAndSignature: "home/saveFileAndSignature",
                editFileAndSignature: "home/editFileAndSignature",
                deleteCircularDocument: "home/deleteCircularDocument",
                setFirstPageImage: "home/setFirstPageImage",
                changePositionFile: "home/changePositionFile",
                loadCircular: "home/loadCircular",
                getStampInfos: "home/getStampInfos",
                getBizcardById: "bizcard/getBizcardById",
                afterCheckAccessCode: "home/afterCheckAccessCode",
                updateCircularUserStatus: "application/updateCircularUser",
                checkAccessCode: "circulars/checkAccessCode",
                sendMailViewed: "application/sendMailViewed",
                getLimit: "setting/getLimit",
                getCloudItems: "cloud/getItems",
                downloadCloudItem: "cloud/downloadItem",
                uploadToCloud: "home/uploadToCloud",
                addLogOperation: "logOperation/addLog",
                getMyInfo: "user/getMyInfo",
                verifyMyInfo: "user/verifyMyInfo",
                discardCircular: "home/discardCircular",
                renameCircularDocument: "home/renameCircularDocument",
                updateMyInfo: "user/updateMyInfo",
                updateFileComment: "home/updateFileComment",
                deleteFileComment: "home/deleteFileComment",
                checkDeviceType: "home/checkDeviceType",
                setUploadFileInfoList: "pageBreaks/setUploadFileInfoList",
                setCircularDocIdBeforeMod: "pageBreaks/setCircularDocIdBeforeMod",
                setCircularDocIdAfterMod: "pageBreaks/setCircularDocIdAfterMod",
                downloadPreviewFile: "home/downloadCreatedPreviewFile",
                reserveAttachment: "home/reserveAttachment",
            }),
            handleComments(e) {
              return this.comments.filter(comment => comment.private_flg==e && comment.text && comment.text.trim())
            },
            onZoomOutClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom = Math.max(30, this.zoom - 10);
            },
            onZoomInClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom = Math.min(200, this.zoom + 10);
            },

            onClickStampHistory: async function(history) {
                // 名刺機能のON/OFFを取得
                var getBizcardFlgResult = await Axios.get(`${config.BASE_API_URL}/setting/getBizcardFlg`)
                .then(response => {
                    return response.data ? response.data.data : null;
                })
                .catch(error => {
                    console.error(error);
                    return null;
                });
                // 名刺機能OFFの場合は名刺情報を表示しない
                if (!getBizcardFlgResult || !getBizcardFlgResult.bizcard_flg) {
                    return;
                }
                this.bizcardData = null;
                // 捺印履歴に名刺IDが紐づけられている場合は名刺情報を取得
                if (history.bizcard_id != null) {
                    const info = {
                        bizcard_id: history.bizcard_id,
                        env_flg: history.env_flg,
                        server_flg: history.server_flg,
                        edition_flg: history.edition_flg
                    }
                    const response = await this.getBizcardById(info);
                    this.bizcardData = response.bizcard;
                    if (this.bizcardData != null) {
                        switch (this.bizcardData.bizcard.charAt(0)) {
                            case "/":
                                this.bizcardData.bizcard = this.base64_prefix.jpeg + this.bizcardData.bizcard;
                                break;
                            case "i":
                                this.bizcardData.bizcard = this.base64_prefix.png + this.bizcardData.bizcard;
                                break;
                            case "R":
                                this.bizcardData.bizcard = this.base64_prefix.gif + this.bizcardData.bizcard;
                                break;
                        }
                    }
                }
                this.showBizcardInfo = true;
            },

            onAddFileClick: function () {
                this.addEmptyFile();

                this.disabledUndo = true;
                this.oldTabSelected = this.tabSelected;
                this.commentsExist = false;
            },
            changeSelectedFile(index){
              if (this.files[index].circular_document_id != this.fileSelected.circular_document_id) {
                this.onFileTabClick(this.files[index], index);
              }
            },
            goToApplication: async function () {
               if (this.settingLimit.require_print === 1 && this.printedStampCount == 0){
                    this.$vs.dialog({
                        type: 'alert',
                        color: 'danger pre-msg',
                        title: `メッセージ`,
                        acceptText: 'OK',
                        text: `捺印してください。`,
                    });
                    return false
                }
              // 二重チェック追加
              this.clickState = true;
              if (this.files.length && !this.fileSelected) {
                this.selectFile(this.files[0]);
              }
              this.$store.commit('home/setCloseCheck', false );
              await this.editFileAndSignature({stampDisplays: this.stampUsed});
              await this.saveFileAndSignature();
              this.$router.push('/application');
              this.clickState = false;
            },
            goToDestination: function () {
              if (this.circular.limit_require_print === 1 && this.printedStampCount == 0) {
                  this.$vs.dialog({
                      type: 'alert',
                      color: 'danger pre-msg',
                      title: `メッセージ`,
                      acceptText: 'OK',
                      text: `捺印してください。`,
                  });
                  return false
              }
              // 二重チェック追加
              this.clickState = true;
              setTimeout(() => {
                  this.editFileAndSignature({stampDisplays: this.stampUsed});
                  this.$router.push('/destination');
              },500);
              this.clickState = false;
            },
            goToSendback: function () {
                // 差戻し
                this.editFileAndSignature({stampDisplays: this.stampUsed});
                this.$router.push(`/sendback`);
            },
            goBack: function() {
               var namePath = this.$route.name;
               if(namePath == 'save_detail'){
                 this.$router.push(`/saved`);
               }else if(namePath == 'received_detail'){
                 this.$route.meta.isKeep=true;
                 this.$route.meta.keepReading=true
                 this.$router.push(`/received`);
               }else{
                  this.$router.push(`/`);
               }
            },
            onUploadFile: async  function (e) {
                this.uploadCompleted = false;
                this.$modal.show('upload-modal');
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    Object.defineProperty(file, 'max_document_size', {
                        value: '8MB',
                        writable: true
                    });
                    file.max_document_size = this.userInfo.max_document_size;
                })
                const iterable = async () => {
                    if(files.length > 0) {
                      const file = files.shift();
                      let item = {name: file.name, success: false, loading: true};
                      this.fileUploads.push(item);
                      const ret = await this.uploadFile(file);
                      item.loading = false;
                      if(ret) {
                        this.fileAfterUploads.push(ret);
                        item.success = true;
                        item.isExcel = ret.server_file_name_for_office_soft && [".xls", ".xlsx"]
                          .some((excelExtension) =>
                            ret.server_file_name_for_office_soft.endsWith(excelExtension)
                          ) ? true : false;
                      }else {
                        item.success = false;
                      }
                      this.addLogOperation({ action: 'r01-upload', result: ret ? 0 : 1, params:{filename: item.name}});
                      await iterable();
                    }
                };
                await iterable();
                this.uploadCompleted = true;
                this.$refs.uploadFile.value = '';
            },
            onUploadFromCloud: async  function (fileId, filename) {
                let file_data = {
                    file_id: encodeURIComponent(fileId),
                    filename: encodeURIComponent(filename),
                    file_max_document_size: this.userInfo.max_document_size
                };
                this.$vs.loading({
                    container: '#itemsCloudToUpload',
                    scale: 0.6
                });
                this.$modal.hide('upload-from-external-modal');
                this.$modal.show('upload-modal');
                this.uploadCompleted = false;
                const iterable = async () => {
                    let item = {name: filename, success: false, loading: true};
                    this.fileUploads.push(item);
                    const ret = await this.downloadCloudItem(file_data);
                    item.loading = false;
                    if(ret) {
                        this.fileAfterUploads.push(ret.data);
                        item.success = true;
                        item.isExcel = ret.server_file_name_for_office_soft && [".xls", ".xlsx"]
                          .some((excelExtension) =>
                            ret.server_file_name_for_office_soft.endsWith(excelExtension)
                          ) ? true : false;
                    }else {
                        item.success = false;
                    }
                    this.addLogOperation({ action: 'r01-upload', result: ret ? 0 : 1, params:{filename: item.name}});
                };
                await iterable();
                this.uploadCompleted = true;
                this.$refs.uploadFile.value = '';
            },
            async onDownloadFile(){
              const downloadFile = async () => {
                this.editFileAndSignature({stampDisplays: this.stampUsed});
                var ret = await this.downloadFile();
                const action = {'create_circular': 'r01-download','save_detail': 'r01-download','received_detail': 'r9-14-download'};
                // PAC_5-1027 ダウンロードの操作履歴が表示されない
                if(action[this.$route.name]){
                  this.addLogOperation({ action: action[this.$route.name], result: ret ? 0 : 1, params:{filename: this.fileSelected.name}});
                  if (ret) {
                    this.$store.state.home.fileSelected.pages.forEach(page =>{
                      page.stamps.forEach(stamp => {
                        stamp.repeated = true;
                      });
                    });
                  }
                }
              }
              await downloadFile();
              this.disabledUndo = true; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
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
            onFileTabClick: function(file, index) {
              if (file.confidential_flg && file.mst_company_id !== this.loginUser.mst_company_id) return;
              if (this.fileSelected && file.circular_document_id === this.fileSelected.circular_document_id) return;

              if (index >= this.maxTabShow) {
                this.changePositionFile({from: index, to: 0});
              }

              this.selectFile(file);
              // tabSelected は vs-navbar 等により変更される
            },
            onCloseDocumentClick: function(file, index) {
              this.$modal.show('delete-doc-modal');
              this.deleteItem = $.extend({}, file);
              this.deleteIndex = index;
              this.oldTabSelected = this.tabSelected;
            },
            onEditPageBreaksClick: function(circular_document_id) {
              this.oldTabSelected = this.tabSelected;
              this.$vs.dialog({
                type: "confirm",
                color: "primary",
                title: "改ページ調整確認",
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: "改ページ調整しますと保存されていない状態は削除されてしまいます。改ページ調整画面へ移動しますか。",
                accept: () => {
                  this.$store.commit('home/setCloseCheck', false );

                  this.setCircularDocIdBeforeMod(circular_document_id);
                  this.setUploadFileInfoList(
                    this.registeredDocInfoList.find(
                      (docInfo) => docInfo.circular_document_id === circular_document_id
                    ).fileAfterUploads
                  );
                  const currentPath = this.$router.currentRoute.path;
                  const createNew = !currentPath.startsWith("/received/") && !currentPath.startsWith("/saves/");
                  const queryObj = { create_new: createNew };
                  if (createNew && this.circular && this.circular.id) {
                    queryObj.circular_id = this.circular.id;
                  }
                  this.$router.push({
                    path: '/page-breaks',
                    query: queryObj,
                  });
                },
                cancel: () => {
                  this.tabSelected = this.oldTabSelected;
                }
              });
            },
            acceptConfirmDelete: async function() {
              this.$modal.hide('delete-doc-modal');

              const isCurrentTab = (this.fileSelected && this.deleteItem.server_file_name === this.fileSelected.server_file_name);

              if (isCurrentTab) {
                this.tabSelected = this.deleteIndex + 1;
                if (this.deleteIndex < this.files.length - 1) {
                  this.tabSelected = this.deleteIndex + 1;
                } else {
                  this.tabSelected = this.deleteIndex > 0 ? this.deleteIndex - 1 : 0;
                }
              }

              const ret = await this.deleteCircularDocument({circular_id: this.circular ? this.circular.id: null,file_path: this.deleteItem.server_file_path, circular_document_id: this.deleteItem.circular_document_id});
              this.closeFile(this.deleteItem);

              const action = {'save_detail': 'r04-delete'};
              if(action[this.$route.name]){
                this.addLogOperation({ action: action[this.$route.name], result: ret ? 0 : 1, params:{filename: this.deleteItem.name,circular_id: this.circular.id}});
              }
              this.deleteItem = null;
              this.deleteIndex = null;

              if (!isCurrentTab) {
                if (this.oldTabSelected === this.files.length) {
                  this.oldTabSelected = this.oldTabSelected - 1;
                }
                this.tabSelected = this.oldTabSelected;
              }

            },
            cancelConfirmDelete: function() {
              this.tabSelected = this.oldTabSelected;
              this.$modal.hide('delete-doc-modal');
            },
            //PAC_5-1216 Start
            cancelConfirmUpdate: function() {
              this.$modal.hide('updatecheck-doc-modal');
            },
            //PAC_5-1216 End
            lockStampToolbarActive: async function(parm){
              if(parm && this.firstIndentation) {
                  this.stampToolbarActive = false;
                  this.firstIndentation = false;
                  await this.checkDeviceType();
                  if(!this.deviceType.isTablet) {
                      this.showLeftToolbar = false;
                  }
              }else if(!parm){
                  this.showLeftToolbar = true;
                  this.firstIndentation = true;
              }
            },
            calcPdfViewerWidth: function () {
              if(!this.$refs.pdfViewer) return;

              const width = this.$refs.pdfViewer.clientWidth;

              const viewerWidth = width - 40;
              if (this.deviceType.isTablet) {
                this.viewerWidth = viewerWidth;
              } else {
                this.viewerWidth = Math.max(820, viewerWidth);

                const num = document.body.clientWidth < 1200 ? 1 : 0 ;
                this.lockStampToolbarActive.apply(this, [num]);
              }

              this.thumbnailViewerWidth = this.$refs.thumbnails.$el.clientWidth - 80;
            },
            selectPage: function (pageno) {
              const index = pageno - 1;

              // set mobile canvas width and height
              const mobilePage = this.mobilePages?.[index];
              if (mobilePage) {
                const editorParam = mobilePage.editorParam;

                this.configKonvaMobile.width = $('#main-home-mobile').width();
                const scale3 = this.configKonvaMobile.width / editorParam.width;
                this.configKonvaMobile.height = scale3 * editorParam.height;
              }

              this.currentPageNo = pageno;
            },
            onChangeStampDate: async function(values) {
                if (Object.prototype.hasOwnProperty.call(this.loginUser, "isAuditUser") && this.loginUser.isAuditUser) {
                    return;
                }
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
                            };
                            this.stampDisplays.push(stamp);
                            this.stampUsed.push(stamp);
                        });
                    })
                    .catch(() => { return []; });

                if (this.stampSelected){
                    let changedDateSelectedStamp = this.stamps.filter(newStamp => newStamp.db_id === this.stampSelected.db_id)[0];
                    if(changedDateSelectedStamp){
                        var selStamp = this.stampDisplays.find(item => item.id === changedDateSelectedStamp.id);
                        this.selectStamp(selStamp);
                        Utils.setCookie(`lastStampSelectedId_${this.loginUser.email}_${config.APP_SERVER_ENV}_${config.APP_SERVER_FLG}`, changedDateSelectedStamp.id, 10);
                    }
                }
            },
            onStampToolbarActiveChange: function (value) {
              this.stampToolbarActive = value;
              const $this = this;
              // PAC_5-2022 速度改善 Start
              if (this.stamp_load_flg) {
                $this.stamp_load_flg = false;
                $this.$vs.loading({
                    container: '.stamp-tool-bar',
                })
                $this.onChangeStampDate($this.date)
                .then(()=>{
                    // PAC_5-2150 印面リスト初期デフォルト選択
                    $this.loadLastStampSelected();
                    setTimeout(function() {
                        $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                        $this.calcPdfViewerWidth();
                        $this.selectPage($this.currentPageNo);
                        $this.noticePosition();
                    },300);
                })
                .catch(() => {
                    $this.stamp_load_flg = true;
                    $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                    return [];
                });
              } else {
                  setTimeout(() => {
                      $this.calcPdfViewerWidth();
                      $this.selectPage($this.currentPageNo);
                      $this.noticePosition();
                  },300);
              }
              // PAC_5-2022 End
            },
            handleFileSelect: async function(evt) {
              const dropZone = document.getElementById('dropZone');
              dropZone.style.borderColor = '#D1ECFF';
              evt.stopPropagation();
              evt.preventDefault();

              this.uploadCompleted = false;
              this.$modal.show('upload-modal');
              const files = Array.from(evt.dataTransfer.files);
              const iterable = async () => {
                if(files.length > 0) {
                  const file = files.shift();
                  file.max_document_size = this.userInfo.max_document_size;
                  let item = {name: file.name, success: false, loading: true};
                  this.fileUploads.push(item);
                  const ret = await this.uploadFile(file);
                  item.loading = false;
                  if(ret) {
                    this.fileAfterUploads.push(ret);
                    item.success = true;
                    item.isExcel = ret.server_file_name_for_office_soft && [".xls", ".xlsx"]
                      .some((excelExtension) =>
                        ret.server_file_name_for_office_soft.endsWith(excelExtension)
                      ) ? true : false;
                  }else {
                    item.success = false;
                  }
                  await iterable();
                }
              };
              await iterable();
              this.uploadCompleted = true;
            },
            handleDragLeave: function(evt) {
              const dropZone = document.getElementById('dropZone');
              dropZone.style.borderColor = '#D1ECFF';
              evt.stopPropagation();
              evt.preventDefault();
            },
            handleDragOver: function(evt) {
              const dropZone = document.getElementById('dropZone');
              dropZone.style.borderColor = '#55efc4';
              evt.stopPropagation();
              evt.preventDefault();
              evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
            },
            onSaveFileClick: async function() {
              this.isProcessing = true; // 処理中はボタンを非活性にし、押下できなくする
              var circular_id = this.$store.state.home.circular.id;
              this.editFileAndSignature({stampDisplays: this.stampUsed});
              const ret = await this.saveFile();
              if (ret) {
                this.$store.commit('home/setCloseCheck', false );
              }
              this.addLogOperation({ action: 'r01-tmp-save', result: ret ? 0 : 1, params:{ circular_id: circular_id}});

              if( this.isMobile ) {
                this.showEdit = false;
              } else {
                this.isProcessing = false; // ボタンを活性化
                if(ret) await this.$router.push('/saved');
              }
            },
            onPageBreaksPreview: function () {
              this.$vs.dialog({
                type: "confirm",
                color: "primary",
                title: "改ページ調整確認",
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: "改ページ調整しますと保存されていない状態は削除されてしまいます。改ページ調整画面へ移動しますか。",
                accept: () => {
                  this.$store.commit('home/setCloseCheck', false );

                  this.setCircularDocIdBeforeMod(null);
                  this.setUploadFileInfoList(this.fileAfterUploads);
                  const currentPath = this.$router.currentRoute.path;
                  const createNew = !currentPath.startsWith("/received/") && !currentPath.startsWith("/saves/");
                  const queryObj = { create_new: createNew };
                  if (createNew && this.circular && this.circular.id) {
                    queryObj.circular_id = this.circular.id;
                  }
                  this.$router.push({
                    path: '/page-breaks',
                    query: queryObj,
                  });
                },
              });
            },
            onChooseUpload:function (){
              if (this.changeFlg === 'pageChange') {
                this.onPageBreaksPreview();
              } else {
                this.onCompletedUpload();
              }
            },
            onCompletedUpload: async function() {
              this.isProcessing = true; // 処理中はボタンを非活性にし、押下できなくする
              this.$store.commit('home/checkCircularUserNextSend', false);
              const ret = await this.acceptUpload(this.fileAfterUploads);
              if (!ret) {
                this.isProcessing = false; // ボタンを活性化
                return;
              }
              this.$modal.hide('upload-modal', {close: true});
              this.tabSelected = this.files.length - 1;

              if (this.tabSelected >= this.maxTabShow) {
                this.changePositionFile({from: this.tabSelected, to: 0});
                this.tabSelected = 0;
              }
              // PAC_5-2022 速度改善 Start
              const $this = this;
              if (this.getNamePath === 'create_circular') {
                if (this.stamp_load_flg) {
                  this.stamp_load_flg = false;
                  this.stampToolbarActive = true;

                  if (!this.isMobile) {
                    $this.$vs.loading({
                        container: '.stamp-tool-bar',
                    })

                    $this.onChangeStampDate($this.date)
                    .then(()=>{
                        // PAC_5-2150 印面リスト初期デフォルト選択
                        $this.loadLastStampSelected();
                        $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                        setTimeout(function() {
                            $this.calcPdfViewerWidth();
                            $this.selectPage($this.currentPageNo);
                            $this.noticePosition();
                        },300);
                    })
                    .catch(() => {
                        $this.stamp_load_flg = true;
                        $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                        return [];
                    });
                  } else {
                    this.onChangeStampDate(this.date).then(() => {
                        $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                    })
                    .catch(() => {
                        $this.stamp_load_flg = true;
                        $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                        return [];
                    });
                  }
                } else {
                  if (!this.isMobile) {
                    this.stampToolbarActive = true;
                    setTimeout(function () {
                        $this.calcPdfViewerWidth();
                        $this.selectPage($this.currentPageNo);
                        $this.noticePosition();
                    }, 300);
                  }
                }
              } else {
                if (!this.isMobile) {
                  this.stampToolbarActive = true;
                  setTimeout(function () {
                    $this.calcPdfViewerWidth();
                    $this.selectPage($this.currentPageNo);
                    $this.noticePosition();
                  }, 300);
                }
              }
              // PAC_5-2022 End
              this.fileAfterUploads = [];
              this.fileUploads = [];
              this.isProcessing = false; // ボタンを活性化

            },
            onRejectUpload: async function() {
              const ret = await this.rejectUpload(this.fileAfterUploads);
              if(!ret) return;
              this.$modal.hide('upload-modal', {close: true});
              this.fileAfterUploads = [];
              this.fileUploads = [];
              if(this.files.length && !this.fileSelected){
                  let index = 0
                  if ( this.oldTabSelected !== null && this.oldTabSelected < this.files.length){
                      index = this.oldTabSelected;
                  }else{
                      this.oldTabSelected = 0;
                  }
                  this.onFileTabClick(this.files[index], index);
              }
            },
            onAddMoreFileClick: function () {
              $('#uploadFile').click();
            },
            onGetBoxItemsDone: function(ret) {
              this.$vs.loading.close('#cloudItems > .con-vs-loading');
              if(ret.statusCode === 401){
                this.$modal.hide('cloud-upload-modal');
                this.$ls.remove('boxAccessToken');
                window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + this.currentCloudDrive, '_blank');
              }
              if(ret.statusCode === 200 && ret.data) {
                this.cloudFileItems = ret.data.item_collection.entries.filter(item => {
                  // PAC_5-736 サポート対象ファイル以外は選択できない（表示されない）
                  let suffix = item.name.toLowerCase().split('.').slice(-1)[0];
                  const suffixArr = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
                  return item.type === 'folder' || (item.type === 'file' && suffixArr.indexOf(suffix) > -1)
                }).map(item => {
                  return {id: item.id, type: item.type === 'folder' ?'folder':'pdf', filename: item.name}
                });
                this.breadcrumbItems = ret.data.path_collection.entries.map(item => {
                  return {id: item.id, title: item.id === '0' ? 'ルート': item.name}
                })
                this.currentCloudFolderId = ret.data.id;
                this.breadcrumbItems.push({id: ret.data.id, title: ret.data.id === '0' ? 'ルート': ret.data.name, active: true});
              }
            },
            onGetCloudItemsToSystemUpload: function(ret) {
                this.$vs.loading.close('#itemsCloudToUpload > .con-vs-loading');
                if(ret.statusCode === 401){
                    this.$modal.hide('upload-from-external-modal');
                    this.$ls.remove('boxAccessToken');
                    window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + this.currentCloudDrive, '_blank');
                }
                if(ret.statusCode === 200 && ret.data) {
                    this.cloudFileItems = ret.data.item_collection.entries.filter(item => {
                      // PAC_5-736 サポート対象ファイル以外は選択できない（表示されない）
                      let suffix = item.name.toLowerCase().split('.').slice(-1)[0];
                      const suffixArr = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
                      return item.type === 'folder' || (item.type === 'file' && suffixArr.indexOf(suffix) > -1)
                    }).map(item => {
                        let item_type = item.type === 'folder' ? 'folder' : item.name.match('[^.]*$')[0];
                        return {id: item.id, type: item_type, filename: item.name}
                    });
                    this.breadcrumbItems = ret.data.path_collection.entries.map(item => {
                        return {id: item.id, title: item.id === '0' ? 'ルート': item.name}
                    });
                    this.currentCloudFolderId = ret.data.id;
                    this.breadcrumbItems.push({id: ret.data.id, title: ret.data.id === '0' ? 'ルート': ret.data.name, active: true});
                }
            },
            onUploadFromExternalClick: function(drive,type) {
                this.currentCloudDrive = drive;
                this.filename_selected_from_cloud = '';
                this.is_download_external = false;
                if (type === this.attachment_file){
                    this.isAttachmentFlg = true;
                    this.$modal.hide('add-attachment-modal');
                } else {
                    this.isAttachmentFlg = false;
                }
                if(this.$ls.get(drive + 'AccessToken')) {
                    this.$modal.show('upload-from-external-modal');
                    if(drive === 'box') {
                        this.cloudLogo = require('@assets/images/box.svg');
                        this.cloudName = 'Box';
                    }
                    if(drive === 'onedrive') {
                        this.cloudLogo = require('@assets/images/onedrive.svg');
                        this.cloudName = 'OneDrive';
                    }
                    if(drive === 'google') {
                        this.cloudLogo = require('@assets/images/google-drive.png');
                        this.cloudName = 'Google Drive';
                    }
                    if(drive === 'dropbox') {
                        this.cloudLogo = require('@assets/images/dropbox.svg');
                        this.cloudName = 'Dropbox';
                    }
                }else {
                    window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + drive, '_blank');
                }
            },
            onDownloadExternalClick: function (drive) {
                this.currentCloudDrive = drive;
                this.is_download_external = true;
                if(this.$ls.get(drive + 'AccessToken')) {
                  this.$modal.show('cloud-upload-modal');
                  this.filename_upload = this.fileSelected.name;
                  if(drive === 'box') {
                    this.cloudLogo = require('@assets/images/box.svg');
                    this.cloudName = 'Box';
                  }
                  if(drive === 'onedrive') {
                    this.cloudLogo = require('@assets/images/onedrive.svg');
                    this.cloudName = 'OneDrive';
                  }
                  if(drive === 'google') {
                    this.cloudLogo = require('@assets/images/google-drive.png');
                    this.cloudName = 'Google Drive';
                  }
                  if(drive === 'dropbox') {
                    this.cloudLogo = require('@assets/images/dropbox.svg');
                    this.cloudName = 'Dropbox';
                  }
                }else {
                  window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + drive, '_blank');
                }
            },
            onUploadFromCloudModalOpened: async function() {
                this.$vs.loading({
                    container: '#itemsCloudToUpload',
                    scale: 0.6
                });
                this.fileid_selected_from_cloud = 0;
                this.filename_selected_from_cloud = '';
                const ret = await this.getCloudItems(0);
                this.onGetCloudItemsToSystemUpload(ret);
            },
            onUploadFromCloudFolderClick: async function(item) {
                if(item.type !== 'folder') return;
                this.$vs.loading({
                    container: '#itemsCloudToUpload',
                    scale: 0.6
                });
                const ret = await this.getCloudItems(item.id);
                this.onGetCloudItemsToSystemUpload(ret);
            },
            onUploadFromCloudBreadcrumbClick: async function(folder_id) {
                this.$vs.loading({
                    container: '#itemsCloudToUpload',
                    scale: 0.6
                });
                const ret = await this.getCloudItems(folder_id);
                this.onGetCloudItemsToSystemUpload(ret);
            },
            addToFileUpload(fileId, filename) {
                this.fileid_selected_from_cloud = this.fileid_selected_from_cloud === fileId ? '' : fileId;
                this.filename_selected_from_cloud = this.filename_selected_from_cloud === filename ? '' : filename;
            },
            onCloudModalOpened: async function() {
              this.$vs.loading({
                    container: '#cloudItems',
                    scale: 0.6
                  });
              const ret = await this.getCloudItems(0);
              this.onGetBoxItemsDone(ret);

            },
            onCloudItemClick: async function(item) {
              if(item.type !== 'folder') return;
              this.$vs.loading({
                container: '#cloudItems',
                scale: 0.6
              });
              const ret = await this.getCloudItems(item.id);
              this.onGetBoxItemsDone(ret);
            },
            onBreadcrumbItemClick: async function(folder_id) {
              this.$vs.loading({
                container: '#cloudItems',
                scale: 0.6
              });
              const ret = await this.getCloudItems(folder_id);
              this.onGetBoxItemsDone(ret);
            },
            onConfidentialFlgLabelClick: function() {
              this.confidentialFlg = !this.confidentialFlg;
            },
            onOverlayHiddenFlgLabelClick: function() {
              this.overlayHiddenFlg = !this.overlayHiddenFlg;
            },
            //PAC_5-1216 Start
            onUploadCheck:function(){
              this.saveId = null;
              var isUpdate = false;
              this.cloudFileItems.forEach(function(File){
                if(this.filename_upload== File.filename){
                  this.saveId = File.id;
                  isUpdate = true;
                }
              },this);

              if(isUpdate && this.currentCloudDrive == 'box'){
                this.$modal.show('updatecheck-doc-modal');
              }else{
                this.onUploadToCloudClick(false);
              }
            },
            onUploadToCloudClick: async function(confirm) {
              if(confirm){
                this.$modal.hide('updatecheck-doc-modal');
              }
              let data = {
                drive : this.currentCloudDrive,
                folder_id : this.currentCloudFolderId,
                filename : this.filename_upload,
                saveFile: true,
                stampDisplays: this.stampUsed,
              }
              if(this.saveId){
                data["file_id"] = this.saveId;
              }
              //PAC_5-1216 END
              this.$vs.loading({
                container: '#cloudItems',
                scale: 0.6
              });
              this.editFileAndSignature({stampDisplays: this.stampUsed});
              const uploadRet = await this.uploadToCloud(data);
              if(uploadRet) {
                this.disabledUndo = true; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
                this.$modal.hide('cloud-upload-modal');
                this.$store.state.home.fileSelected.pages.forEach(page =>{
                  page.stamps.forEach(stamp => {
                    stamp.repeated = true;
                  });
                });
              }else {
                this.$vs.loading.close('#cloudItems > .con-vs-loading');
              }
            },
            onDiscardCircularClick: async function() {
                const ret = await this.discardCircular();
                if(ret) {
                  this.$route.meta.isKeep=true
                  await this.$router.push('/received');
                }
            },
            isFileCreatedByOwn(file) {
                if(!file) return false;
                if(!this.circular) return false;
                if(!this.circularUserLastSend || !this.circularUserLastSend.received_date) {
                    return true;
                }
                const fileCreateDate = new Date(file.create_at.replace(' ', 'T')).getTime();
                const circularUserReceivedDate = new Date(this.circularUserLastSend.received_date.replace(' ', 'T')).getTime();
                return fileCreateDate > circularUserReceivedDate;
            },
            changeTabMail(){
                const isActive = $("#mail-list-label").hasClass("tab-active");
                if (!isActive) {
                  $("#comments-label").removeClass("tab-active");
                  $("#comments-label").addClass("tab");
                  $("#mail-list-label").removeClass("tab");
                  $("#mail-list-label").addClass("tab-active");
                  $(".mail-list").show();
                  $(".comments").hide();
                }
            },
            changeTabComments(){
                const isActive = $("#comments-label").hasClass("tab-active")
                if (!isActive) {
                  $("#mail-list-label").removeClass("tab-active");
                  $("#mail-list-label").addClass("tab");
                  $("#comments-label").removeClass("tab");
                  $("#comments-label").addClass("tab-active");
                  $(".mail-list").hide();
                  $(".comments").show();
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

              let el_current = pageno+1
              $(`#main-home-mobile .preview-list .page:nth-child(${el_current})`).fadeIn(0)

              // Left
              if( pageno - 8 > 0 ) {
                el_current = pageno-7
                $(`#main-home-mobile .preview-list .page:nth-child(${el_current})`).fadeOut(0)
              }

              // Right
              el_current = pageno+9
              $(`#main-home-mobile .preview-list .page:nth-child(${el_current})`).fadeOut(0)

              this.selectPage(pageno);
            },
            docEdit(){
                this.showEdit = true;
            },
            showStamps(){
                this.$modal.show('stamps-modal');
            },
            async clickStamp(stampId){
                const selStamp = this.stampDisplays.find(item => item.id === stampId);
                this.selectStamp(selStamp);
                if (this.userInfo) {
                    this.userInfo.last_stamp_id = stampId;
                    await Axios.post(`${config.BASE_API_URL}/myinfo`, {info: this.userInfo, nowait: true})
                        .then(response => {
                            return response.data ? response.data.data: [];
                        })
                        .catch(() => { return []; });
                }
                Utils.setCookie(`lastStampSelectedId_${this.loginUser.email}_${config.APP_SERVER_ENV}_${config.APP_SERVER_FLG}`, stampId, 10);
                this.$modal.hide('stamps-modal', {close: true});
            },
            closeStamps(){
                this.$modal.hide('stamps-modal', {close: true});
            },
            async updateRotateAngle(){
                this.userInfo.default_rotate_angle = this.rotateAngle;
                this.userInfo.default_opacity = this.opacity;
                this.updateMyInfo(this.userInfo);
            },
            // ファイル名ダブルクリック
            renameFileNameClick(filename) {
              // ファイル名変更:申請者だけ
              if (this.$store.state.home.fileSelected.create_user_id == this.loginUser.id && this.$store.state.home.fileSelected.mst_company_id == this.loginUser.mst_company_id) {
                this.$modal.show('rename-file-modal');
                this.old_file_name = filename;
                this.file_name = filename;
              }
            },
            // ファイル名変更キャンセル
            cancelRenameFileNme: function() {
                this.tabSelected = this.oldTabSelected;
                this.$modal.hide('rename-file-modal');
            },
            // ファイル変更確認後
            onSubmitRenameFileNme: async function() {
              this.$modal.hide('rename-file-modal');
              if(this.file_name == this.old_file_name){
                // ファイル名変更の場合、API呼出
                return;
              }
              const ret = await this.renameCircularDocument({circular_id: this.circular ? this.circular.id: null, circular_document_id: this.fileSelected.circular_document_id, file_name: this.file_name + '.pdf'});
              const action = {'save_detail': 'r04-delete'};
              if(action[this.$route.name]){
                this.addLogOperation({ action: action[this.$route.name], result: ret ? 0 : 1, params:{filename: this.file_name + '.pdf', circular_document_id: this.fileSelected.circular_document_id}});
              }
              this.fileSelected.name = this.file_name + '.pdf';
              this.fileSelected.update_at = ret.update_at;
            },
            showDialog: function() {
                this.showInput = false;
            },
            hideDialog: function() {
                this.showInput = true;
            },
            AddStampsConfirmation: function(currentPageNo) {
              setTimeout(() => {
                this.$refs.pages.addStampsConfirmation(currentPageNo - 1);
              },10);
            },
            AddStampsConfirmationMobile: function(currentPageNo) {
              const pageIndex = currentPageNo - 1;
              setTimeout(()=>{
                const editor = this.$refs.editorMobile.find(x => x.$el.dataset.index == pageIndex); // :data-index
                if (editor) editor.AddStampsConfirmation();
              },10);
            },
            changeDefaultStatus(e){
              // Because the native click event will be executed twice, the first time on the label tag
              // and the second time on the input tag, this processing is required
              if (e.target.tagName === 'INPUT') return
              this.addStampHistory = false;
              this.addTextHistory = false;
              this.radioVal = "default"
            },
            changeStampHistory(e){
              // Because the native click event will be executed twice, the first time on the label tag
              // and the second time on the input tag, this processing is required
              if (e.target.tagName === 'INPUT' || this.addStampHistory == true) return
              this.$vs.dialog({
                type: 'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: `電子署名が付与されている場合、回覧履歴を付けてダウンロードをすると回覧時の署名が無効になります。`,
                accept: () => {
                  this.addStampHistory = true;
                  this.addTextHistory = false;
                  this.radioVal = "addStampHistory";
                },
                cancel: () => {
                    this.addStampHistory = false;
                    if(this.addTextHistory == true){
                        this.radioVal = "addTextHistory";
                    }else{
                        this.radioVal = "default"
                    }
                },
              });
            },
            changeTextHistory(e){
              // Because the native click event will be executed twice, the first time on the label tag
              // and the second time on the input tag, this processing is required
              if (e.target.tagName === 'INPUT' || this.addTextHistory) return
              this.addTextHistory = true;
              this.addStampHistory = false;
              this.radioVal = "addTextHistory";
            },
            // 社内社外宛先追加
            addComment: function() {
              if (!this.documentComment) return ;
              this.updateFileComment({private_flg: this.tab_comment_info, text: this.documentComment, parent_send_order: this.circularUserLastSend ? this.circularUserLastSend.parent_send_order:0});
              this.documentComment = '';
            },
            // 社内社外宛先削除
            removeComment: function(){
              this.deleteFileComment({private_flg: this.tab_comment_info});
            },
            handleResize: function() {
              this.window.width = window.innerWidth;
              this.window.height = window.innerHeight;

              this.calcPdfViewerWidth();
              this.selectPage(this.currentPageNo);
              this.noticePosition();
            },
            //PAC_5-1053 コメントの存在を気づかせるようにしたい
            checkCommentsExist: function(fileSelected){
              //isEditScreen(受信時と、新規作成or下書き時をわけるフラグ)がnullになるタイミングがあるため、個別にチェックをいれる
              if(this.circular && this.circular.circular_status !== CIRCULAR.SAVING_STATUS && this.circular.circular_status !== CIRCULAR.RETRACTION_STATUS){
                if(fileSelected.comments.length && !this.commentsChecked.includes(fileSelected.circular_document_id)){
                  this.commentsExist = true;
                  this.noticePosition();
                }else{
                  this.commentsExist = false;
                }
              }
            },
            noticePosition: function(){
              if(!this.commentsExist) {
                return ; //コメントが存在しなければ終了
              }

              const tab = document.querySelector(".cirInfo > div.con-ul-tabs > ul > li:nth-child(1)") //印鑑タブ要素取得
              const tab2 = document.querySelector(".cirInfo > div.con-ul-tabs > ul > li:nth-child(2)") //回覧先タブ要素取得
              const tab3 = document.querySelector(".cirInfo > div.con-ul-tabs > ul > li:nth-child(3)") //コメントタブ要素取得
              const tabClientRect = tab.getBoundingClientRect();
              const tab2ClientRect = tab2.getBoundingClientRect();
              const tab3ClientRect = tab3.getBoundingClientRect();
              //印鑑タブ幅+回覧先タブ幅+コメントタブ幅-noticeアイコン幅 TODO アイコン幅の共有化
              this.noticeLeft = (tabClientRect.width + tab2ClientRect.width + tab3ClientRect.width - 10) + 'px'
            },
            //コメントタブをクリックしたとき、既読状態と見なす
            clickComments: function(){
              if(this.commentsExist){
                this.commentsExist = false; //タブクリック＝コメント既読としてアイコンを非表示にする
                this.commentsChecked.push(this.fileSelected.circular_document_id); //再度同じ文書を開いたとき既読状態を保つ
              }
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
          confirmSave (event) {
            if( this.userInfo.withdrawal_caution != 1 ) {
              return;
            }
            if( this.$store.state.home.closeCheck == false ) {
              return;
            }
            if( this.$store.state.home.files.length == 0 ) {
              this.$store.commit('home/setCloseCheck', true );
              return;
            }
            event.returnValue = "編集中のものは保存されませんが、よろしいですか？";  /* Edge, Chromeではメッセージが表示されない */
          },
          haveFiles: function() {
            return this.files.length == 0;
          },
            //PAC_1398 cloudから添付ファイルを追加します
          onUploadAttachmentFromCloud: async  function (fileId, filename) {
              let file_data = {
                  file_id: encodeURIComponent(fileId),
                  filename: encodeURIComponent(filename),
                  file_max_attachment_size: this.userInfo.max_attachment_size,
                  circular_id: this.$store.state.home.circular.id
              };
              this.$vs.loading({
                  container: '#itemsCloudToUpload',
                  scale: 0.6
              });
              this.$modal.hide('upload-from-external-modal');
              this.$modal.show('add-attachment-modal');
              const iterable = async () => {
                  let item = {file_name: filename, success: false, loading: true,confidential_flg:false,create_user_id:this.userInfo.mst_user_id};
                  this.attachmentUploads.push(item); //画面に追加して表示します。
                  const ret = await this.downloadCloudAttachment(file_data); //ファイルをサーバにアップロードして保存します
                  item.loading = false;
                  if(ret) {
                      this.attachmentAfterUploads.push(ret.data);//添付ファイルの関連情報を保存します。
                      item.success = true;
                  }else {
                      this.attachmentUploads.pop();
                      item.success = false;
                  }
                  this.addLogOperation({ action: 'r01-upload', result: ret ? 0 : 1, params:{filename: item.file_name}});
              };
              await iterable();
          },
            //PAC_1398 添付ファイルの情報をすべて取得します。
            addAttachment: async function () {
                //申請者ページかどうかを判断する
                if (this.getNamePath != 'create_circular' && (this.localUserId !== 0 && this.localCircularUserId !== 0)) {
                    this.localUserId = this.userInfo.mst_user_id;
                    this.localCircularUserId = this.circular.users[0].mst_user_id;
                }
                this.$modal.show('add-attachment-modal');
                if (!this.circular || !this.circular.users) {
                    return null;
                }
                if (this.attachmentAfterUploads.length <= 0 || this.getNamePath == 'create_circular') {
                    this.attachmentUploads = [];
                    this.attachmentAfterUploads = [];
                    let ret = await this.getAttachment(this.circular.id);
                    ret.forEach((item,value) => {
                        this.attachmentUploads.push(item);
                        this.attachmentAfterUploads.push(item);
                    });
                }

            },
            //PAC_1398 添付画面を閉じます。
            closeAttachmentModal: function() {
                this.$modal.hide('add-attachment-modal');
            },
            //PAC_1398 「社外秘に設定」の状態を修正します。
            onAttachmentConfidentialFlgClick: async function (index) {
                const data = {
                    circular_attachment_id : this.attachmentAfterUploads[index].circular_attachment_id ? this.attachmentAfterUploads[index].circular_attachment_id : this.attachmentAfterUploads[index].id,
                    confidentialFlg : this.attachmentAfterUploads[index].circular_attachment_id ? (!this.attachmentUploads[index].confidentialFlg ? 1 : 0) : (!this.attachmentUploads[index].confidential_flg ? 1 : 0) ,
                };
                await this.attachmentConfidentialFlg(data);
            },
            //PAC_1398 添付ファイルをダウンロード
            onDownloadAttachment: async function(index){
              this.$vs.dialog({
                type:'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'OK',
                cancelText: 'キャンセル',
                text: `送信者が信頼できる場合、OKボタンをクリックしてファイルをダウンロードします。`,
                accept: async ()=> {
                  let circular_attachment_id = this.attachmentAfterUploads[index].circular_attachment_id ?
                      this.attachmentAfterUploads[index].circular_attachment_id : this.attachmentAfterUploads[index].id;
                  if (this.settingLimit.sanitizing_flg){//ダウンロード予約
                    let info = {
                      circular_attachment_id: circular_attachment_id,
                      file_name: this.attachmentUploads[index].file_name,
                    }
                    await this.reserveAttachment(info);
                  }else {//ダウンロード
                    await this.downloadAttachment(circular_attachment_id);
                  }
                },
                cancel: async ()=> {
                  return null;
                },
              });
            },
            //PAC_1398 添付ファイルを削除
            onDeleteAttachment: async function (index) {
                this.attachmentUploads.splice(index,1);
                await this.deleteAttachment(this.attachmentAfterUploads[index].circular_attachment_id ? this.attachmentAfterUploads[index].circular_attachment_id : this.attachmentAfterUploads[index].id);
                this.attachmentAfterUploads.splice(index,1);
            },
            //PAC_1398 添付ファイルをローカルからアップロードします。
            onUploadAttachment:async function(e) {
                const files = Array.from(e.target.files);
                let isUpload = false;
                files.forEach(file =>{
                    file.max_attachment_size = this.userInfo.max_attachment_size;
                    if (file.size > this.userInfo.max_attachment_size * 1024 * 1024) {
                        isUpload = true;
                    }
                });
                if (isUpload){
                    this.$modal.hide('add-attachment-modal');
                    this.$modal.show('over-file-size-modal');
                    return;
                }
                const iterable = async () =>{
                    if (files.length > 0){
                        const file = files.shift();
                        let item = {file_name:file.name,success:false,loading:true,confidential_flg:false,create_user_id:this.userInfo.mst_user_id};
                        this.attachmentUploads.push(item);
                        const ret = await this.attachmentUpload(file);
                        item.loading = false;
                        if (ret){
                            item.success = true;
                            this.attachmentAfterUploads.push(ret);
                        }else {
                            this.attachmentUploads.pop();
                            item.success = false;
                        }
                        this.addLogOperation({ action: 'r01-upload', result: ret ? 0 : 1, params:{filename: item.name}});
                        await iterable();
                    }
                };
                await iterable();
                this.addAttachment();
            },
            onOverFileSizeClick: async function(){
                await this.$modal.hide('over-file-size-modal');
                this.addAttachment();
            },
            //PAC_5-2142 モバイルとPCでメッセージを切替
            async validatHasAttachment(circular_id) {
                const ret = await this.getAttachment(circular_id);
                if(ret.length > 0){
                  const dialogText = this.isMobile ? "添付されたファイルを確認される際はPC版でご確認ください" : "右上の「添付ファイル」をクリックして文書をダウンロードしてご確認ください";
                  this.$vs.dialog({
                    type: 'alert',
                    color: 'danger pre-msg',
                    title: `メッセージ`,
                    acceptText: 'OK',
                    text: `文書が添付されています。
                          ${dialogText}`,
                  });
                }
            },
          //PAC_5-2142 モバイルとPCでメッセージを切替　END
          changConvertFlg1(){
            this.changeFlg = 'default';
          },
          changConvertFlg2(){
            this.changeFlg = 'pageChange';
          },
          loadLastStampSelected() {
            let lastStampId = Utils.getCookie(`lastStampSelectedId_${this.loginUser.email}_${config.APP_SERVER_ENV}_${config.APP_SERVER_FLG}`)
            if (this.userInfo && this.userInfo.last_stamp_id) {
              var selStamp = this.stampDisplays.find(item => item.id === this.userInfo.last_stamp_id);
              if (selStamp == undefined && this.stamps && this.stamps.length > 0) selStamp = this.stampDisplays.find(item => item.id === this.stamps[0].id);
              this.selectStamp(selStamp);
            } else if (lastStampId) {
              var selStamp = this.stampDisplays.find(item => item.id === lastStampId);
              if (selStamp == undefined && this.stamps && this.stamps.length > 0) selStamp = this.stampDisplays.find(item => item.id === this.stamps[0].id);
              this.selectStamp(selStamp);
            } else if (this.stamps && this.stamps.length > 0) {
              var selStamp = this.stampDisplays.find(item => item.id === this.stamps[0].id);
              this.selectStamp(selStamp);
            }
          },
          async isPrintStamp(id){
              const history = await this.getStampInfos(id)
              return history.stamp.reduce((total,_) => {
                  if(_.email==this.loginUser.email){
                        total++
                  }
                  return total
              },0)
          },
          async approvalFile() {
            if( this.isMobile ){
              let pageIndex = this.currentPageNo - 1;
              const editor = this.$refs.editorMobile.find(x => x.$el.dataset.index == pageIndex)
              editor.confirmStamp()
            }

            this.$store.commit('home/setCloseCheck', false );
            await this.editFileAndSignature({stampDisplays: this.stampUsed});
            await this.saveFileAndSignature();
            this.$router.push('/approval');
          },

          MobileOpenFlatpickr: function(){
            $('.flatpickr-calendar').addClass('isMobile');
          },

          MobileCloseFlatpickr: function(){
            $('.flatpickr-calendar').removeClass('isMobile');
          },
          handleDialogStart: function(e) {
            if( e.changedTouches.length != 1 ) return false;
            this.dialog_y = e.changedTouches[0].pageY;

            $('body').addClass('disabledScroll');
          },
          handleDialogEnd: function(e) {
            if(!this.dialog_y) return false;



            if( e.changedTouches[0].pageY > this.dialog_y ) {
              $('#main-home-mobile.create_new .btn_dialog.edit').css('height', '60px');
            } else {
              $('#main-home-mobile.create_new .btn_dialog.edit').css('height', '120px');
            }
            $('body').removeClass('disabledScroll');
            this.dialog_y = 0;
          },
          selectTextCustom: function( currentPageNo ) {
            var pageIndex = currentPageNo - 1;
            const editor = this.$refs.editorMobile.find(x => x.$el.dataset.index == pageIndex)
            editor.openTextOption( true );
          },
          dragMoveDialogStatus(e) {
            let positionEnd = e.changedTouches[0];
            $('.btn_dialog_status').css('top', `${positionEnd.clientY-25}px`);
            $('.btn_dialog_status').css('left', `${positionEnd.clientX-25}px`);

          },
          dragStartDialogStatus(){
            $('body').addClass('disabledScroll');
          },
          dragEndDialogStatus(e){
            $('body').removeClass('disabledScroll');
            let positionEnd = e.changedTouches[0];

            if( this.isTablet ) {
              if( positionEnd.clientX > screen.width - 80 ) $('.btn_dialog_status').css('left', `${screen.width - 80}px`);
              if( positionEnd.clientX < 30 ) $('.btn_dialog_status').css('left', `10px`);

              if( positionEnd.clientY > screen.height - 150 ) $('.btn_dialog_status').css('top', `${screen.height - 150}px`);
              if( positionEnd.clientY < 70 ) $('.btn_dialog_status').css('top', `70px`);


            } else {
              if( positionEnd.clientX > screen.width - 60 ) $('.btn_dialog_status').css('left', `${screen.width - 60}px`);
              if( positionEnd.clientX < 30 ) $('.btn_dialog_status').css('left', `10px`);

              if( positionEnd.clientY > screen.height - 100 ) $('.btn_dialog_status').css('top', `${screen.height - 100}px`);
              if( positionEnd.clientY < 50 ) $('.btn_dialog_status').css('top', `50px`);

            }

        },
          handleClickDialogStatus(){
            if( this.isTextSelected ) {
              this.loadLastStampSelected();
            } else {
              this.selectText();
            }
          },
          //選択文書ダウンロード予約の画面表示
          showReserveFile(){
            this.confirmDownload = true;
            var pos = this.fileSelected.name.lastIndexOf('.');
            this.inputMaxLength = 50 - this.fileSelected.name.substr(pos).length;
            this.downloadReserveFilename = '';
          },
          //ダウンロード予約
          onDownloadReserve: async function(){
            this.confirmDownload = false;
            let info = {
              reserveFileName: this.downloadReserveFilename  ?  this.downloadReserveFilename + this.fileSelected.name.substr(this.fileSelected.name.lastIndexOf('.')) : this.fileSelected.name,
            }
            const downloadFile = async ()=>{
              this.editFileAndSignature({stampDisplays: this.stampUsed});
              var ret = await this.downloadPreviewFile(info);

              // PAC_5-1027 ダウンロードの操作履歴が表示されない
              if(ret){
                this.$store.state.home.fileSelected.pages.forEach(page =>{
                  page.stamps.forEach(stamp => {
                    stamp.repeated = true;
                  });
                });
              }
            }
            await downloadFile();
            this.disabledUndo = true; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
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
                this.checkCommentsExist(newVal);

                const fileIndex = this.$store.state.home.files.findIndex(file => file.server_file_name === this.$store.state.home.fileSelected.server_file_name);
                this.tabSelected = fileIndex;

                this.zoom = newVal.zoom;
                this.histories = await this.getStampInfos(newVal.circular_document_id);
                this.disabledUndo = this.$store.state.home.fileSelected.actions.length <= 0;

                if (this.isMobile) {
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
              if (!newVal) return;
              if (newVal.confidential_flg && newVal.mst_company_id !== this.loginUser.mst_company_id) {
                this.tabSelected = oldIndex;
              }
            },
            "$store.state.home.files":{
                handler () {
                    Utils.buildTabColorAndLogo(this.files, this.companyLogos, this.loginUser.mst_company_id, config.APP_EDITION_FLV, config.APP_SERVER_ENV);
                },
                deep:true
            },
            "isEditScreen":function (newIndex, oldIndex){
              if(newIndex === true){
                setTimeout(function(_this){
                  $(".tab-home").children().children().children().children().eq(1).children().children().html('回覧先');
                  if(_this.tab_cir_info_back != null){
                    _this.tab_cir_info = _this.tab_cir_info_back;
                  }
                },500, this)
              }
            },
        },
        async mounted() {
          document.body.style.overflow = 'hidden';

          this.handleResize();
          const $this = this;
          this.$ls.on('boxAccessToken', (value) => {
            if (value) {
              if (this.is_download_external) {
                $this.filename_upload = $this.$store.state.home.fileSelected.name;
                $this.$modal.show('cloud-upload-modal')
                $this.cloudLogo = require('@assets/images/box.svg');
                $this.cloudName = 'Box';
              } else if (this.is_download_external !== null) {
                this.$modal.show('upload-from-external-modal');
                $this.cloudLogo = require('@assets/images/box.svg');
                $this.cloudName = 'Box';
              }
            }
          });
          this.$ls.on('onedriveAccessToken', (value) => {
            if (value) {
              if (this.is_download_external) {
                $this.filename_upload = $this.$store.state.home.fileSelected.name;
                $this.$modal.show('cloud-upload-modal')
                $this.cloudLogo = require('@assets/images/onedrive.svg');
                $this.cloudName = 'OneDrive';
              } else if (this.is_download_external !== null) {
                this.$modal.show('upload-from-external-modal');
                $this.cloudLogo = require('@assets/images/onedrive.svg');
                $this.cloudName = 'OneDrive';
              }

            }
          });
          this.$ls.on('googleAccessToken', (value) => {
            if (value) {
              if (this.is_download_external) {
                $this.filename_upload = $this.$store.state.home.fileSelected.name;
                $this.$modal.show('cloud-upload-modal')
                $this.cloudLogo = require('@assets/images/google-drive.png');
                $this.cloudName = 'Google Drive';
              } else if (this.is_download_external !== null) {
                this.$modal.show('upload-from-external-modal');
                 $this.cloudLogo = require('@assets/images/google-drive.png');
                 $this.cloudName = 'Google Drive';
              }
            }
          });
          this.$ls.on('dropboxAccessToken', (value) => {
            if (value) {
              if (this.is_download_external) {
                $this.filename_upload = $this.$store.state.home.fileSelected.name;
                $this.$modal.show('cloud-upload-modal')
                $this.cloudLogo = require('@assets/images/dropbox.svg');
                $this.cloudName = 'Dropbox';
              } else if (this.is_download_external !== null) {
                this.$modal.show('upload-from-external-modal');
                $this.cloudLogo = require('@assets/images/dropbox.svg');
                $this.cloudName = 'Dropbox';
              }

            }
          });
          // PAC_5-1136 クラウドストレージとの連携失敗時にエラーメッセージを表示
          this.$ls.on('errormessage', () => {
              console.log("failed to Cloud Connection");
              let message = this.$ls.get('errormessage');
              //複数インスタンス対策
              if (message) {
                  this.$vs.notify({color: 'danger',text: message,position: 'bottom-left'});
              }
              this.$ls.remove('errormessage');
          });

          // 印鑑,コメント欄にスクロールバーを追加
          let element = document.getElementsByClassName("con-slot-tabs");
          for (let i = 0; i < element.length; i++) {
            element[i].style.overflow = "auto";
            element[i].style.height = "94%";
          }

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
          // Checkmobile
          if (
            /Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(
              navigator.userAgent
            )
          ) {
            this.isMobile = true;
            this.docEdit();
          } else {
            this.$router.push('/');
          }
          // Check tablet
          if(
            /(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/.test( navigator.userAgent.toLowerCase() )
          )
          {
            this.isTablet = true;
          }

          this.checkDeviceType();
          // すすむボタンの状態の初期化
          this.$store.state.home.disabledProceed = true;
          this.$store.state.home.title = '';
          this.getNamePath = this.$route.name;
          this.$store.commit('home/setUsingPublicHash', false);
          this.$store.commit('home/disableAccessCodeFlg');
          this.date = new Date();

          this.isEditScreen = this.circular && this.circular.circular_status !== CIRCULAR.SAVING_STATUS && this.circular.circular_status !== CIRCULAR.RETRACTION_STATUS;
          this.isEditScreen = true;

          this.startVisibilityWatch();

          const action = {'create_circular': 'r01-display','received_detail': 'r9-14-display'};

          const promises = [];

          if(!this.$route.query.back) {
            promises.push(this.clearState());
          }

          promises.push(
          (async () => {
          this.userInfo = await this.getMyInfo();
          let path_param = this.$route.fullPath.split('?');
          if(this.$route.path.indexOf('received') >= 0 && path_param.length > 1){
            let data = {
              hash  : path_param[1],
              email : this.loginUser.email
            }
            await this.verifyMyInfo(data);
          }
          this.$store.state.user = this.userInfo;
          this.$store.commit('setting/setWithdrawalCaution', this.userInfo.withdrawal_caution );
          this.rotateAngle = this.userInfo.default_rotate_angle;
          this.opacity = this.userInfo.default_opacity;
              var cir_info = this.userInfo.circular_info_first;
              switch(cir_info){
                case "印鑑":
                  this.tab_cir_info = 0;
                  break;
                case "回覧先":
                  this.tab_cir_info = 1;
                  break;
                case "コメント":
                  this.tab_cir_info = 2;
                  break;
                case "捺印履歴":
                  this.tab_cir_info = 3;
                  break;
                default:
                  this.tab_cir_info = 0;
                  break;
              }
              this.tab_cir_info_back = this.tab_cir_info;

          })(),
          ( async () => {
          const id = this.$route.params.id;
          if(id && !this.$route.query.back) {
                const ret = await this.loadCircular({id: id});
                for (const _ of this.$store.state.home.files) {
                    this.InitPrintStampCount+=await this.isPrintStamp(_.circular_document_id);
                }
              if(!this.$store.state.home.accessCodePopupActive){
                let redirectBack = false;
                if(!ret) {
                  if(action[this.$route.name]) {
                      await this.addLogOperation({action: action[this.$route.name], result: 1});
                  }
                  redirectBack = true;
                }else if(this.$store.state.home.circular.circular_status == 2 || this.$store.state.home.circular.circular_status == 3 || (this.$store.state.home.circular.users && this.$store.state.home.circular.users.some(item => item.circular_status === CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK))){
                  redirectBack = true;
                }
                if (redirectBack){
                  let userInfo = this.userInfo;
                  if (!userInfo){
                    userInfo = await this.getMyInfo();
                  }
                  if (userInfo.login_type == 1){ // SAML login
                    let redirectionUrl = '/'
                    if (userInfo.page_display_first === "下書き一覧") {
                      redirectionUrl = '/saved';
                    } else if (userInfo.page_display_first === "受信一覧") {
                      redirectionUrl = '/received';
                    } else if (userInfo.page_display_first === "送信一覧") {
                      redirectionUrl = '/sent';
                    } else if (userInfo.page_display_first === "完了一覧") {
                      redirectionUrl = '/completed';
                    } else if (userInfo.page_display_first === "ポータル") {
                      redirectionUrl = '/portal';
                    }
                    await this.$router.push(redirectionUrl);
                  }else{
                    this.$router.back();
                  }
                }
              }
          }

          // 改ページ調整後の場合、調整されたファイルのタブが選択されるようにする
          const circularDocIdAfterMod = this.$store.state.pageBreaks.circularDocIdAfterMod;
          if (circularDocIdAfterMod !== null && this.files && this.files.length > 1) {
            // 上記のthis.loadCircular内でcommit('initFileSelected')を
            // setTimeoutを500で呼んでいるので、500より大きい時間でタブを切り替える
            const modifiedFileIndex = this.files.findIndex(file => file.circular_document_id === circularDocIdAfterMod);
            if (modifiedFileIndex > 0) {
              setTimeout(() => {
                this.onFileTabClick(this.files[modifiedFileIndex], modifiedFileIndex);
              }, 1000);
            }
          }
          // リロード時にタブが選択されないようにする
          this.setCircularDocIdAfterMod(null);

          if (this.$store.state.home.circular){
              this.enableAdd = this.$store.state.home.circular.checkEnableAdd;
              this.checkNoExistCircularUser = this.$store.state.home.circular.checkNoExistCircularUser;
              this.userSendMailId = this.$store.state.home.circular.userSendMail_id;
              this.specialCircularFlg = this.circular && this.circular.special_site_flg;
              this.groupName = this.circular.special_site_group_name;
              this.checkShowButtonApply = false;
              if(this.$store.state.home.circular.circular_status ===  CIRCULAR.SEND_BACK_STATUS){
                if(this.circularUserLastSend && this.circularUserLastSend.parent_send_order == 0 && this.circularUserLastSend.child_send_order == 0){
                  this.checkShowButtonApply = true;
                }
              }
          }

          if(action[this.$route.name]) this.addLogOperation({ action: action[this.$route.name], result: 0});

          if(this.circularUserLastSend && this.isEditScreen && (this.circularUserLastSend.circular_status === this.CIRCULAR_USER.NOTIFIED_UNREAD_STATUS)) {
            if(this.loginUser && this.loginUser.email === this.circularUserLastSend.email) {
                this.sendMailViewed({circular_user_id: this.circularUserLastSend.id, is_template_circular: this.isTemplateCircular});
            }
          }

          if(this.$store.state.home.title && (this.$store.state.home.title).trim() !== ""){
              this.docName = this.$store.state.home.title;
          }else{
              let docName = '';
              for(let i = 0; i < this.files. length; i++){
                  if (i === 0) {
                      docName += this.files[i].name;
                  } else {
                      docName += ',' + this.files[i].name;
                  }
              }
              this.docName = docName;
          }
            })(),
            (async () => {
              this.settingLimit = null;
              if (!Object.prototype.hasOwnProperty.call(this.loginUser, "isAuditUser") || !this.loginUser.isAuditUser) {
                  this.settingLimit = await this.getLimit();
              }
              if (this.settingLimit == null){
                  this.settingLimit = {};
              }
            })(),
            (async () => {
            // PAC_5-842　対応　▼
            // 所属会社情報取得
            var company = await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                .then(response => {
                    return response.data ? response.data.data: [];
                })
                .catch(() => { return []; });
            /*PAC_5-2288 S*/
            this.esigned_flg = company.esigned_flg
            /*PAC_5-2288 E*/
            this.companyConfidentialFlg = company.confidential_flg;
              //PAC_5-1398添付ファイル機能　「添付ファイル」表示
                if (this.getNamePath != 'create_new') {
                    let createCircularCompany  = await Axios.get(`${config.BASE_API_URL}/setting/getCreateCircularCompany?circular_id=${this.$route.params.id}`)
                        .then(response => {
                            return response.data ? response.data.data: [];
                        })
                        .catch(() => { return []; });
                    this.isShowAttachment = createCircularCompany.attachment_flg == 1;
                }else {
                    this.isShowAttachment = company.attachment_flg != 0;
                }

            // PAC_5-842　対応　▲
            })(),
            // PAC_5-2022 速度改善 Start
            (async () => {
                this.$nextTick(() => {
                    if (this.getNamePath !== 'create_circular') {
                      const $this = this;
                      $this.stamp_load_flg = false;
                      $this.$vs.loading({
                          container: '.stamp-tool-bar',
                      })
                      $this.onChangeStampDate($this.date)
                      .then(()=>{
                          // PAC_5-2150 印面リスト初期デフォルト選択
                          $this.loadLastStampSelected();
                          $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                          setTimeout(function() {
                              $this.calcPdfViewerWidth();
                              $this.selectPage($this.currentPageNo);
                              $this.noticePosition();
                          },300);
                      })
                      .catch(() => {
                          $this.stamp_load_flg = true;
                          $this.$vs.loading.close('.stamp-tool-bar > .con-vs-loading');
                          return [];
                      });
                    }
                })
            })(),
            // PAC_5-2022 End
          );


          await Promise.all(promises);

          // 特設サイト
          if(this.specialCircularFlg){
            this.circular.users.forEach(item => {
              if(item.email == this.loginUser.email && item.mst_company_id == this.loginUser.mst_company_id && item.special_site_receive_flg == 1){
                this.specialCircularReceiveFlg = true;
              }
              if(item.special_site_receive_flg == 1 && item.id == this.circularUserLastSend){
                this.circularUserLastSendIdIsSpecial = true;
              }
            });
            // 捺印・プレビュー画面(新規作成)画面は、「回覧作成」ボタンのみ操作可能
            if(this.$router.history.current.meta.parent === 'saves'){
              this.specialButtonDisableFlg = true;
            }
          }

          this.$nextTick(()=>{
              if (this.circular && this.circular.id){
                  this.validatHasAttachment(this.circular.id)
              }
          })

            // PAC_5-1728 ダウンロードオプションの初期化
            this.$nextTick(() => {
                if (!this.settingLimit || !this.settingLimit.storage_local || !this.isEditScreen) {
                    this.addStampHistory = false;
                    this.addTextHistory = false;
                }

                if(this.settingLimit && this.settingLimit.default_stamp_history_flg == 1){
                    this.addStampHistory = true
                    this.radioVal = "addStampHistory";
                }
            })
          if(!this.isMobile) this.tabSelected = 99999;
          else this.tabSelected = 0;

          this.zoom = 100;
          this.$store.commit('home/homeUnSelectText');
          if (this.$router.history.current.meta.parent === 'saves') {
            this.specialButtonDisableFlg = true;
          }
          window.addEventListener('resize', this.handleResize);

          this.is_ipad = /(iPad)/i.test(navigator.userAgent);

          window.addEventListener("beforeunload", this.confirmSave);
          this.$store.commit('home/setCloseCheck', true );
        },
        beforeDestroy() {
          // 取得を止めるため
          this.visiblePageRange = [-1, -1];
          this.visibleThumbnailRange = [-1, -1];
        },
        destroyed() {
          window.removeEventListener('resize', this.handleResize);
          window.removeEventListener("beforeunload", this.confirmSave);
        },
    }
</script>

<style lang="scss" scoped>
  #notice{
    position: absolute;
    width: 10px;
    min-width: 10px !important;
    min-height: 10px !important;
  }

  .btn-groups{
      .vs-button{
          font-family: inherit!important;
      }
  }
  .rotate-angle{
    border-radius: 5px;
    border: #999999 solid 1px ;
    display: flex;
    justify-content: space-between;
  }
  .rotate-opacity{
    border-radius: 5px;
    border: #999999 solid 1px ;
    display: flex;
    justify-content: space-between;
  }
  .stamp-setting-title{
    position: absolute;
    left: 50%;
    top: -10px;
    background-color: #fff;
    width: 150px;
    text-align: center;
    margin-left: -75px;
  }
  .linear{
    height: 20px;
    margin: 10px;
    background: -webkit-linear-gradient(left,#FF0000,#FFB5B5);   /*Safari5.1 Chrome 10+*/
  }
  /*PAC_5-1724 ボタンをipad・ipadproで横表示なるように調整の為追加*/
  @media (max-width:1024px){
    #main-home .vs-button {
      padding: .75rem 0.2rem;
      font-size : 0.8rem;
      margin-right: 0.2rem;
    }
    .vs-button:not(.vs-radius):not(.includeIconOnly):not(.small):not(.large) {
      padding: .75rem 0.2rem;
      font-size : 0.8rem;
      margin-right: 0.2rem;
  }
  }
  /*PAC_5-1724 ボタンをipad・ipadproで横表示なるように調整の為追加　end*/
</style>


<style lang="scss">
.flatpickr-calendar.isMobile{
width: 350px !important;
left: calc( 50% - 175px ) !important;
margin-top: -30px;

.flatpickr-innerContainer{
  display: block;
  margin: 0 auto;
}
}

.pre-msg .vs-dialog-text{
  white-space: pre-line;
}

body{
overflow-y: auto !important;

&.disabledScroll{
  overflow-y: hidden !important;
  position: fixed;
  touch-action: none;
}
}

.router-view{
padding: 1.2rem !important;
}

#main-home-mobile.create_new{
display: block !important;
position: relative;
overflow: hidden;



  .tabSelected{
    margin: 8px 0;
    position: relative;
    background: #f8f8f8;

    >div{
      position: relative;

      &:before{
        position: absolute;
        top: 1px;
        right: 1px;
        content: "";
        font-size: 23px;
        color: #0984e3;
        background: linear-gradient(to right, rgba(255,255,255,0.7), white);
        pointer-events: none;
        width: 40px;
        height: 30px;
        z-index: 3;
        border-radius: 0px 5px 5px 0;
      }
    }

    select{
      width: 100%;
      -webkit-appearance: none;
      -moz-appearance: none;
      padding: 0 15px 0 5px;
      line-height: 30px;
      transform: scale(1);
      border-radius: 5px;
      border-color: rgba(0, 0, 0, 0.2);
      background: #fff;
      color: #000;

      &::-ms-expand {
        display: none;
      }
    }
    svg{
      position: absolute;
      top: 1px;
      right: 5px;
      width: 12px;
      height: 20px;
      color: #0984e3;
      z-index: 3;
    }
  }

#stamps-modal {
  .date_stamp_config {
    padding-top: 50px;
    .stamp-change-date{
      top: 20px;
          position: absolute;
          left: 10%;
          width: 80%;
          border-radius: 20px;
    }
    .date_stamp_input{
      right: auto !important;
      left: 0;
      z-index: -1;
    }
  }
  .stamp-list {
    padding-top: 20px;
    .swiper-button-prev, .swiper-button-next{
      margin-top: -7px;
    }
  }

  .swiper-wrapper{
    width: calc( 100% - 80px );
    img{
      max-width: 100%;
    }
  }
}

.pdf-content{
  height: auto;
  canvas{
    position: relative;
    border: 1px solid #dcdcdc !important;
    width: calc( 100% - 2px );
  }
}

.btn_dialog{
  z-index: 900;
  position: fixed;
  bottom: 0px;
  border: 1px solid #dcdcdc;
  background: #f2f2f2;
  width: 400px;
  height: 60px !important;
  left: calc( 50% - 200px );
  border-radius: 0;
  line-height: 16px;
  padding: 10px 0 5px;
  display: inline-block;
  text-align: center;
  transition: 0.5s;

  > div{
    display: inline-block;
    white-space: nowrap;
    width: 20%;
    text-align: center;
    min-height: 45px;
  }

  .label{
    margin-top: 5px;
  }

  .icon{
    height: 20px;
    line-height: 20px;
  }

  svg{
    color: rgb(16, 127, 205);
    font-size: 18px;
  }

  img{
    height: 18px;
  }

  &.edit{
    > div{
      float: left;
    }
  }
}

.stamps-confirm-modal{
  position: fixed;
  bottom: 120px;
  left: calc(50% - 140px);
  z-index: -1;
  display: none !important;
  opacity: 0;
}

.mail-list{
  .item:first-child{
    &::after{
      display: none;
    }
  }
}

.preview-list {
  width: 100%;
  background: #f2f2f2 !important;

  .btn{
    background-color: white;
    color: #dcdcdc;
    margin: 0 3px;
    line-height: 30px;
    text-align: center;
    border: 0 solid black;
    border-radius: 5px 5px 5px 5px;
    width: 25px;
    height: 30px;
  }
  .btn_selected{
    color: #0a84e3;
    border: 1px solid #0a84e3;
    background-color: white;
    margin: 0 3px;
    line-height: 30px;
    text-align: center;
    border-radius: 5px 5px 5px 5px;
    width: 25px;
    height: 30px;
  }
}

.preview-tool-mobile{
  width: 100%;
  display: inline-block;
  margin: 10px 0;
  flex: 1 1 100%;

  >div{
    width: 100%;
    max-width: 200px;
    margin: 0 auto;

    >div{
      float: left;
      text-align: center;

      &:first-child, &:last-child{
        width: 35%;
      }
      &:nth-child(2){
        width: 30%;
      }

      .zoom-in, .zoom-out{
        padding: 0.5rem;
        border: 2px solid rgba(var(--vs-primary), 1) !important;
        margin: auto;
        line-height: 1px;
      }
      .zoom-text{
        font-size: 18px;
        background: #dcdcdc;
        padding: 0 8px 0 8px;
        border-radius: 10px 10px 10px 10px;
      }
    }
  }
}

#text-option-modal{
  .v--modal{
    overflow: visible;
  }

  .text-option{
    display: block;
    padding: 30px 20px 20px;
    text-align: center;

    input{
      transform: scale(1);
    }

    .font-family, .font-size, .font-color{
      display: inline-block;
      text-align: center;
    }
    .font-family{
      .con-select.dropdown{
        width: 150px;
      }
    }
    .font-size{
      margin: 0 3px;

      .con-select.dropdown{
        width: 60px;
      }
    }
    .font-color{
      min-width: 30px;

      .box {
        right: 0;
      }
    }
  }

  .text-option-button{
    display: inline-block;
    text-align: center;
    color: #fff;
    width: 100%;
    margin-bottom: 30px;

    .btn_save, .btn_cancel{
      padding: 0.75rem 1rem;
      border-radius: 6px;
      display: inline-block;
      font-size: 1rem;
    }

    .btn_save{
      background: rgb(34, 173, 56);
      color: #fff;
      margin-right: 10px;
    }
    .btn_cancel{
      background: #d8d8d8;
      color: #000;
      margin-left: 10px;
    }
  }
}

.btn_dialog{

  .btn_dialog_status{
    position: fixed;
    right: 10px;
    top: 120px;
    height: 50px;
    width: 50px;
    line-height: 30px;
    text-align: center;
    color: #ffffff;
    text-transform: uppercase;
    min-height: auto !important;

    .status_icon{
      position: absolute;
      top: 1px;
      left: 3px;
      height: 50px;
      width: 50px;

      svg{
        height: 50px;
        width: 50px;
        -webkit-filter: drop-shadow( 0px 2px 3px rgba(0, 0, 0, .3));
        filter: drop-shadow( 0px 2px 3px rgba(0, 0, 0, 0.3));
      }
    }
    .status_name{
      position: absolute;
      z-index: 2;
      top: 13px;
      left: 19px;
      font-size: 16px;
      font-weight: 700;

      &.text{
        left: 22px;
      }
    }
  }

  &.full{
    .btn_dialog_status{
      bottom: 125px;
    }
  }

}
}

#main-home.create_new.mobile{
  display: block !important;
  .break_line{
    display: inline-block;
    width: 100%;
    height: 1vh;
  }

.upload-from-external-modal{
  #itemsCloudToUpload{
    max-height: calc( 100vh - 350px );
  }
}

.swiper-wrapper{
    width: calc( 100% - 80px );
    img{
      max-width: 100%;
    }
  }

.vs-divider--text{
  white-space: break-spaces !important;
}

.preview-option{
  display: none;
}

.work-content{
  height: auto;
  position: relative;

  .upload-file{
    display: inline-block !important;

    .preview-scale, .main-content{
      display: inline-block !important;
      width: 100% !important;
    }

    .preview-scale, .stamp-tool{
      display: none !important;
    }

    .main-content{
      margin-bottom: 30px;

      .upload-wrapper{
        height: auto;
      }
      .pdf-content{
        height: auto;
        border-width: 0;

        >div:first-child{
          display: none !important;
        }
      }
    }

  }
}

#text-option-modal{
  .v--modal{
    overflow: visible;
  }

  .text-option{
    display: block;
    padding: 30px 20px 20px;
    text-align: center;

    input{
      transform: scale(1);
    }

    .font-family, .font-size, .font-color{
      display: inline-block;
      text-align: center;
    }
    .font-family{
      .con-select.dropdown{
        width: 150px;
      }
    }
    .font-size{
      margin: 0 3px;

      .con-select.dropdown{
        width: 60px;
      }
    }
    .font-color{
      min-width: 30px;

      .box {
        right: 0;
      }
    }
  }

  .text-option-button{
    display: inline-block;
    text-align: center;
    color: #fff;
    width: 100%;
    margin-bottom: 30px;

    .btn_save, .btn_cancel{
      padding: 0.75rem 1rem;
      border-radius: 6px;
      display: inline-block;
      font-size: 1rem;
    }

    .btn_save{
      background: rgb(34, 173, 56);
      color: #fff;
      margin-right: 10px;
    }
    .btn_cancel{
      background: #d8d8d8;
      color: #000;
      margin-left: 10px;
    }
  }
}

}



@media ( max-width: 1024px ){
  .preview-list{
    background-color: #f2f2f2 !important;
    width: 100% !important;
    height: 80px !important;
  }

  .preview-list-page{
    width: 100%;
    background: #f2f2f2 !important;
  }

}

@media ( min-width: 768px ){
  #main-home.create_new.mobile {

    .break_line{
      height: 2rem;
    }

    .select_from_cloud{
      margin: 7vh 0;

      .vs-divider--text{
        white-space: nowrap !important;
      }
    }

    .select_from_cloud_button{
      button {
        padding: 1.2rem 0;
        width: 200px;
        font-size: 1.2rem;

        &.box_cloud, &.google_cloud{
          margin-right: 30px;
        }

        .download-icon{
          height: 25px !important;
        }
      }
    }

    .label-upload{
      margin: 4vh 0;
    }

    .upload-file{
      min-height: calc( 100vh - 200px );
    }

    .pdf-content {
      .upload-wrapper {
        .upload-box{
          height: 45vh;
          width: 80% !important;
          margin: 3vh 0;
        }
      }
    }
  }

  #main-home-mobile.create_new{
    top: -110px;
    margin-bottom: 80px;
    padding: 0;

     .work-content .tabSelected{
        select{
          font-size: 18px;
          padding: 5px 10px;
        }
        svg{
          top: 5px;
          right: 10px;
        }
     }

    .btn_dialog {
      height: 90px !important;
      width: calc(100% - 2.4rem);
      left: 1.2rem;

      >div:not(.btn_dialog_status){
        width: 110px;
        float: none !important;
        margin-top: 10px !important;

      }

      .icon{
        height: 30px;
      }

      img{
        height: 28px;
      }

      svg{
        font-size: 28px;
      }

      .btn_dialog_status{
        top: 180px;
        height: 70px;
        width: 70px;

        .status_name{
          top: 22px;
          left: 25px;
          font-size: 22px;

          &.text{
            left: 30px;
          }
        }

        .status_icon{
          height: 70px;
          width: 70px;

          svg{
            height: 70px;
            width: 70px;
          }
        }
      }
    }

    .stamps-confirm-modal button{
      width: 130px;

      &:last-child{
        margin-left: 20px;
      }
    }
  }
}

@media ( max-width: 600px ){
  #main-home .pdf-content .upload-wrapper{
    .upload-box{
      height: 45vh;
    }
    .select_from_cloud{
      margin: 5vh 0;
    }
  }

  #main-home.create_new.mobile{
    margin-top: -45px;
  }
  #main-home-mobile.create_new{
    top: -150px;

    .btn_dialog{
      width: calc( 100% - 2.4rem );
      left: 1.2rem;
      height: 60px;
    }

    #stamps-modal .v--modal-box, #stamps-modal .v--modal, .v--modal-box, .v--modal{
      width: 90% !important;
      left: 5% !important;
    }
  }
  #main-home.mobile.create_new{
    #stamps-modal .v--modal-box, #stamps-modal .v--modal, .v--modal-box, .v--modal{
      width: 90% !important;
      left: 5% !important;
    }
  }
}

@media ( max-width: 480px ){
  #main-home-mobile.create_new{
    top: -155px;
  }
}

@media ( max-width: 240px ){
  #main-home {
    .pdf-content .upload-wrapper{
      .upload-box{
        height: auto;
      }
    }
    .vs-row.mb-20{
      margin-bottom: 10px !important;
    }
    .pdf-content .upload-wrapper .upload-box label.wrapper{
      padding: 10px 15px;
    }

    .select_from_cloud_button{
      button{
        margin: 0 0 10px 0 !important;
      }
    }
  }
  #main-home-mobile.create_new{
    top: -165px;
    .btn_dialog {
      padding: 0;
      height: 120px;

      >div:not(.btn_dialog_status){
        width: 33%;
        margin-top: 10px;
      }
      svg, img {
        width: 15px;
      }
    }
  }
}


</style>
