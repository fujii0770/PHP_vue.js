<template>
    <div>
    <div id="main-home" :class="isMobile?'mobile':''">
        <div style="margin-bottom: 15px">
            <vs-row>
                <vs-col  vs-w="3" >
                    <div  v-if="title.trim().length" :title="title" >
                        <p><strong>件名：</strong></p>
                        <p style="
                            width: 90%;
                            padding:1px 0;
                            word-break: break-all;
                        "><strong>{{title.length > 20 ? title.slice(0,20) + '...' : title}}</strong></p>
                    </div>
                  <div style="display: flex;flex-wrap: nowrap;"  v-else-if=" !showLeftToolbar">
                    <vs-row class="mb-3" vs-align="center" vs-type="flex" vs-justify="center">
                      <vs-col vs-w="3" vs-justify="center" vs-align="center">
                        <div class="zoom-out-container">
                          <vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out DivbuttonSmall"><i
                              class="fas fa-minus"></i></vs-button>
                        </div>
                      </vs-col>
                      <vs-col vs-w="6" vs-justify="center" vs-align="center">
                        <div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{ zoom }}%</label></div>
                      </vs-col>
                      <vs-col vs-w="3" vs-justify="center" vs-align="center">
                        <div class="zoom-in-container">
                          <vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in DivbuttonSmall"><i
                              class="fas fa-plus"></i></vs-button>
                        </div>
                      </vs-col>
                    </vs-row>
                  </div>
                </vs-col>
                <vs-col vs-type="flex" vs-w="3" vs-align="center" vs-justify="center">
                    <ul class="breadcrumb">
                        <li v-if="$store.state.home.circular && ($store.state.home.circular.circular_status == CIRCULAR.CIRCULAR_COMPLETED_STATUS || $store.state.home.circular.circular_status == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS)">
                            <p style="color: #0984e3;"><span class="badge badge-primary">1</span> プレビュー</p>
                        </li>
                        <li v-else>
                            <p style="color: #0984e3;"><span class="badge badge-primary">1</span> プレビュー・捺印</p>
                        </li>
                        <li v-if="!($store.state.home.circular && ($store.state.home.circular.circular_status == CIRCULAR.CIRCULAR_COMPLETED_STATUS || $store.state.home.circular.circular_status == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS))"><p><span class="badge badge-default">2</span> 回覧先設定</p></li>
                        <li><p style="background: transparent"></p></li>
                    </ul>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6">
                    <vs-button id="button9"  class="square" :style="!isShowAttachment || specialCircularFlg ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" color="#fff" type="filled" :disabled="files.length <= 0" v-on:click="addAttachment"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</vs-button>
                    <vs-button v-if="showLongTermSave" class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="onSaveLongtermModal">長期保管</vs-button>
                    <!--PAC_5-1488 クラウドストレージを追加する Start-->
                    <vs-dropdown :vs-trigger-click="is_ipad"  v-if="(settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox) && $store.state.home.fileSelected && !$store.state.home.fileSelected.del_flg && sanitizing_flg==0">
                    <!--PAC_5-1488 End-->
                        <vs-button id="button5" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" :disabled="!showDownloadBtn"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード</vs-button>
                        <vs-dropdown-menu v-show="showDownloadBtn">
                            <!--PAC_5-2288 S-->
                            <template v-if="showDownloadBtn">
                            <!--PAC_5-2288 E-->
                            <!--PAC_5-1488 クラウドストレージを追加する Start-->
                            <li class="vx-dropdown--item">
                                <a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="default" @click.native.stop="changeDefaultStatus($event);"  vs-name="radioVal"  v-model="radioVal"  :disabled="countAllTabNum" >完了済みファイル</vs-radio></a>
                            </li>
                            <li class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addStampHistory" @click.native.stop="changeStampHistory($event)"   vs-name="radioVal"    v-model="radioVal"  >回覧履歴を付ける</vs-radio></a></li>
                            <li v-show="settingLimit.is_show_current_company_stamp" class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addTextHistory" @click.native.stop="changeTextHistory($event)"   vs-name="radioVal"    v-model="radioVal"  :disabled="countAllTabNum"  >自社のみの回覧履歴を付ける</vs-radio></a></li>
                            <!--PAC_5-1488 End-->
                            <vs-dropdown-item>
                                <vs-button v-on:click="onDownloadFileClick" :disabled="countAllTabNum"  color="primary" class="w-full download-item" style="min-width: 150px;" type="filled"><i class="fas fa-download"></i>  ローカル</vs-button>
                            </vs-dropdown-item>
                            <!--PAC_5-1488 クラウドストレージを追加する Start-->
                            <vs-dropdown-item v-if="settingLimit.storage_box">
                                <vs-button color="primary" v-on:click="onDownloadExternalClick('box')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box" > <span class="download-item-text">Box</span></vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item v-if="settingLimit.storage_onedrive">
                                <vs-button color="primary" v-on:click="onDownloadExternalClick('onedrive')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive" > <p>OneDrive</p></vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item v-if="settingLimit.storage_google">
                                <vs-button color="primary" v-on:click="onDownloadExternalClick('google')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive" > <span class="download-item-text">Google Drive</span></vs-button>
                            </vs-dropdown-item>
                            <vs-dropdown-item v-if="settingLimit.storage_dropbox">
                                <vs-button color="primary" v-on:click="onDownloadExternalClick('dropbox')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="pdf" > <span class="download-item-text">Dropbox</span></vs-button>
                            </vs-dropdown-item>
                            <!--PAC_5-1488 End-->
                             <!--PAC_5-2288 S-->
                             </template>
                             <!--PAC_5-2288 E-->
                        </vs-dropdown-menu>
                    </vs-dropdown>

                    <vs-button v-if="doPermission" :style="(circularUserLastSend && circularUserLastSend.child_send_order === 0) ? 'color:#000;border:1px solid #dcdcdc;':'display:none'" :disabled="files.length <= 0" class="square submit-circular"  color="#fff" type="filled" v-on:click="onDiscardCircularClick"><i class="far fa-window-close" style="color:#107fcd;margin-right: 5px;"></i> 回覧破棄</vs-button>
                    <vs-button v-if="doPermission" :style="(circularUserLastSend && circularUserLastSend.child_send_order === 0) ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" :disabled="files.length <= 0" class="square submit-circular"  color="#fff" type="filled" v-on:click="goToSendback"><span><img :src="require('@assets/images/pages/home/back_to_former_person.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 差戻し</vs-button>
                    <!-- PAC_5-512 START -->
                    <vs-button v-if="doPermission" style="color:#000;border:1px solid #dcdcdc;" class="square mr-0 submit-circular2" :disabled="(!$store.state.home.fileSelected)" v-on:click="goToDestination" color="#fff" type="filled"><i class="far fa-envelope" style="color:#107fcd;margin-right: 5px;"></i> 次へ</vs-button>
                    <!-- PAC_5-512 END -->
                    <vs-button v-if="reviewPermission" style="color:#000;border:1px solid #dcdcdc;" :disabled="files.length <= 0" class="square submit-circular"  color="#fff" type="filled" v-on:click="goToSendback"><span><img :src="require('@assets/images/pages/home/back_to_former_person.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 差戻し</vs-button>
                    <vs-button class="square mr-0 submit-circular" v-if="hasRequestSendBack && isParentSendOrder && hasRequestApprovalSendBack" style="color:#fff;" color="#22AD38" :disabled="clickState" v-on:click="onApprovalRequest" type="filled"><span><img :src="require('@assets/images/pages/home/admit.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> 差戻し承認</vs-button>
                    <vs-button id="button2" :style="isCreateScreen ? 'display:none':'color:#000;border:1px solid #dcdcdc;'" class="square mr-0 submit-circular2" v-if="reviewPermission" :disabled="clickState" v-on:click="goToReDestination" color="#fff" type="filled"><i class="far fa-envelope" style="color:#107fcd;margin-right: 5px;"></i> 次へ</vs-button>
                    <vs-button style="color:#000;border:1px solid #dcdcdc;" class="square mr-0 submit-circular" v-if="currentViewingUser" v-on:click="goToMemo" color="#fff"  type="filled"><i class="fas fa-envelope-square" style="color:#107fcd;margin-right: 5px;"></i> メモ</vs-button>
                </vs-col>
            </vs-row>
          <template v-if="!showLeftToolbar && isShowButton">
            <div style="display: flex;flex-wrap: nowrap;width: 200px;margin-left: -10px" >
              <vs-col vs-type="flex"  >
                <vs-row class="mb-3" vs-align="center" vs-type="flex" vs-justify="center">
                  <vs-col vs-w="3" vs-justify="center" vs-align="center">
                    <div class="zoom-out-container">
                      <vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out DivbuttonSmall"><i
                          class="fas fa-minus"></i></vs-button>
                    </div>
                  </vs-col>
                  <vs-col vs-w="6" vs-justify="center" vs-align="center">
                    <div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{ zoom }}%</label></div>
                  </vs-col>
                  <vs-col vs-w="3" vs-justify="center" vs-align="center">
                    <div class="zoom-in-container">
                      <vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in DivbuttonSmall"><i
                          class="fas fa-plus"></i></vs-button>
                    </div>
                  </vs-col>
                </vs-row>
              </vs-col>
            </div>
          </template>
        </div>
        <vs-card class="work-content">
            <vs-row>
                <div style="display: flex;flex-wrap: nowrap;width: 100%;">
                <vs-col vs-type="flex" vs-w="1.5" :style="showLeftToolbar?'width: 200px;min-width: 200px;flex:0 0 auto;flex-direction: column;border: 1px solid #cdcdcd;':'width:0;overflow:hidden;'">
                    <div class="preview-list-tool">
                        <vs-row class="mb-3" vs-align="center" vs-type="flex" vs-justify="center">
                            <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-out-container"><vs-button v-on:click="onZoomOutClick" color="primary" radius type="flat" class="zoom-out"><i class="fas fa-minus"></i> </vs-button></div></vs-col>
                            <vs-col vs-w="6" vs-justify="center" vs-align="center"><div class="zoom-text-container"><label class="zoom-text inline-block w-100">{{zoom}}%</label></div></vs-col>
                            <vs-col vs-w="3" vs-justify="center" vs-align="center"><div class="zoom-in-container"><vs-button v-on:click="onZoomInClick" color="primary" radius type="flat" class="zoom-in"><i class="fas fa-plus"></i> </vs-button></div></vs-col>
                        </vs-row>
                        <vs-row class="mb-3 confidential" v-if="userHashInfo && fileSelected && fileSelected.create_user_id === userHashId && fileSelected.enableDelete && enableAdd && companyConfidentialFlg" v-show="specialCircularFlg">
                            <vs-col vs-w="2"><vs-checkbox class="confidentialCheck" :value="confidentialFlg" v-on:click="onConfidentialFlgLabelClick"></vs-checkbox></vs-col>
                            <vs-col vs-w="10" class="pl-2"><p v-on:click="onConfidentialFlgLabelClick" class="confidential-label">社外秘に設定</p></vs-col>
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
                <vs-col vs-type="flex" :vs-w="stampToolbarActive ? '8':'10.3'" style="flex:1 1 auto;transition: width .2s;width:0 !important;">
                    <div class="pdf-content" ref="pdfViewer" style="position: relative;">
                        <vs-col vs-type="flex" vs-w="12" vs-align="flex-start" vs-justify="flex-start">
                            <vs-navbar v-model="tabSelected"
                                       color="#fff"
                                       active-text-color="rgb(9,132,227)"
                                       class="myNavbar">

                                <vs-navbar-item v-for="(file, index) in files" :key="file.circular_document_id" :index="index" :class="'document ' + (file.confidential_flg ? 'is-confidential': (file.hasOwnProperty('tabColor')?file.tabColor:''))">
                                    <template>
                                        <a :class="[(file) !== '' && file.tabLogo?'no-padding':'', `filename`]" @dblclick="renameFileNameClick(file.name != ''? file.name.split('.pdf')[0]:'')" v-tooltip.top-center="file.name" v-on:click="onFileTabClick(file, index)" href="#">
                                            <i v-if="file.confidential_flg && loginCircularUser && file.mst_company_id !== loginCircularUser.mst_company_id" class="fas fa-lock" style="color: #fdcb6e"></i>
                                            <span v-if="file.confidential_flg && loginCircularUser && file.mst_company_id !== loginCircularUser.mst_company_id"> ー </span>
                                            <i v-if="!file.tabLogo && file.timestampLogo" class="far fa-clock fa-lg" style="color: dimgrey"></i>
                                            <img v-if="!file.confidential_flg && (file) !== '' && file.tabLogo" :src="`data:image/png;base64,${file.tabLogo}`"  alt="logo" class="logo-format">
                                            <template v-if="(file) === '' || !file.tabLogo">{{file.name}}</template>
                                        </a>
                                        <!--PAC_5-2242 Start-->
                                        <a v-if="repagePreviewFlg && registeredDocInfoList.some(docInfo => docInfo.circular_document_id === file.circular_document_id)"
                                           v-tooltip.top-center="(repagePreviewFlg && registeredDocInfoList.some(docInfo => docInfo.circular_document_id === file.circular_document_id))?'改ページ調整':''"
                                           class="edit" v-on:click="onEditPageBreaksClick(file.circular_document_id)"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!--PAC_5-2242 End-->
                                        <a :key="confidentialFlg + index + 'delete'" v-if="(!file.confidential_flg || (file.confidential_flg && file.mst_company_id === userMstCompanyId)) && file.enableDelete && enableAdd  && isFileCreatedByOwn(file)" class="close" v-on:click="onCloseDocumentClick(file, index)" v-show="!specialCircularFlg">
                                            <i  class="close fas fa-times"></i>
                                        </a>
                                    </template>
                                </vs-navbar-item>

                                <vs-navbar-item index="99999" class="add-document" v-if="userHashInfo && !userHashInfo.is_external && enableAdd">
                                    <p v-tooltip.top-center="'Add File'" v-if="files.length > 0 && doPermission && files.length < 5"  v-show="!specialCircularFlg" v-on:click="onAddFileClick"> <i class="fas fa-plus"></i></p>
                                </vs-navbar-item>
                                <vs-spacer></vs-spacer>
                            </vs-navbar>
                        </vs-col>
                        <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="upload-wrapper" v-if="!fileSelected && userHashInfo && !userHashInfo.is_external">
                            <!--PAC_5-1488 クラウドストレージを追加する Start-->
                            <div class="vx-col w-full md:w-1/2 upload-box" id="dropZone" @drop="handleFileSelect" @dragleave="handleDragLeave" @dragover="handleDragOver" v-if="settingLimit && settingLimit.storage_local === 1">
                            <!--PAC_5-1488 End-->
                                <label class="wrapper"  for="uploadFile">
                                <input type="file" ref="uploadFile" multiple accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf" id="uploadFile" v-on:change="onUploadFile"/>
                                <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                                    <label for="uploadFile" class="pb-5"><strong>クリックしてファイルを選択してください</strong></label>
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

                            <!--PAC_5-1488 クラウドストレージを追加する Start-->
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
                            <vs-divider color="primary" style="font-size:1.5rem">クラウドストレージからファイルを選択</vs-divider>
                            <vs-row
                                vs-align="center"
                                vs-type="flex" vs-justify="center" vs-w="12">
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <span class="download-item-text">OneDrive</span></vs-button>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                                <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="Dropbox"> <span class="download-item-text">Dropbox</span></vs-button>
                            </vs-row>
                            <!--PAC_5-1488 End-->
                        </vs-row>

                        <template v-if="fileSelected != null">
                          <pdf-pages v-show="!hasRequestFailedImage"
                            ref="pages"
                            :expectedPagesSize="expectedPagesSize" :pages="pages"
                            :rotateAngle="rotateAngle" :opacity="realOpacity" :imageScale="pageImageScale"
                            :deleteFlg="fileSelected.del_flg" :deleteWatermark="fileSelected.delete_watermark"
                            @visible-page-changed="onVisiblePageChanged"
                            :isPublic="userHashInfo && userHashInfo.is_external"
                            :enable="doPermission?true:false"
                            :stamps="stampUsed"
                            @generateStamp="onGenerateStampClick"
                            :key="zoom">
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
                <vs-col vs-type="flex" oncontextmenu="return false" :vs-w="stampToolbarActive ? '2.5':'.2'" :class="stampToolbarActive?'right-toolbar':'right-toolbar hide'">
                    <div class="tools " :style="!stampToolbarActive ? 'display:none':'' ">
                        <div :class="'is-approval comment-height ' + (doPermission ? 'allow-edit': '') + (userHashInfo && userHashInfo.is_external ? ' is-external': '')" >
                            <div style="padding: 0;height:100%">
                                <vs-tabs v-model="tab_cir_info" class="tab-parent comment-height cirInfo">
                                    <vs-tab label="印鑑">
                                        <div v-show="userHashInfo && userHashInfo.is_external && doPermission">
                                            <div class="stamps-processing" style="position:relative;z-index:100;border:1px solid #dcdcdc;border-radius:30px;padding-left:15px;padding-right:15px;background-color:#000000;width:max-content;margin:auto;text-align:center;">
                                                <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="undoAction" :disabled="disabledUndo">
                                                    <i class="fas fa-undo-alt" style="color:#ffffff;margin-right: 5px;"></i><br> 元に戻す</vs-button>
                                                <vs-button class="square stamps-processing-button" style="background-color:#000000;" color="#ffffff" type="filled" @click="AddStampsConfirmation(currentPageNo)" :disabled="this.$store.state.home.disabledProceed">
                                                    <i class="fa" style="color:#ffffff;margin-right: 5px;">&#xf01e;</i><br> やり直し</vs-button>
                                            </div>
                                            <vs-row vs-type="flex" style="padding: 10px 10px 0">
                                                <div class="break"></div>
                                            </vs-row>
                                            <!--PAC_5-1488 クラウドストレージを追加する Start-->
                                            <template v-if="(circular?circular.text_append_flg==1:false)&&(companyLimit?companyLimit.text_append_flg==1:false)">
                                            <!--PAC_5-1488 End-->
                                                <h2 class="title">テキスト追加</h2>
                                                <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 0 10px" class="stamp-text">
                                                    <vs-col vs-w="4" :class="'stamp-item ' +  (isTextSelected ? 'selected': '')" >
                                                        <img class="add-text-icon" width="100" height="100" :src="require('@assets/images/text_add.svg')" alt="stamp-img" v-on:click="selectText()">
                                                    </vs-col>
                                                </vs-row>
                                            </template>
                                        </div>
                                        <div v-show="userHashInfo && !userHashInfo.is_external && doPermission">
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
                                                    <vs-button class="square" :style="(!receivedOnlyFlg) ? {width: '95%'}:{width: '100%'}" data-toggle color="primary" type="filled" v-show="!(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (userHashInfo && userHashInfo.date_stamp_config === 0))">捺印日付変更</vs-button>
                                                </vs-col>
                                                <vs-col v-if="(!receivedOnlyFlg)" :vs-w="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (userHashInfo && userHashInfo.date_stamp_config === 0)) ? 12 : 6">
                                                    <v-popover offset="0" :auto-hide="false" :popoverClass="['change-stamp-angle']">
                                                        <vs-button class="square tooltip-target b3 change-angle-stamp" color="primary" type="filled" :style="(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (userHashInfo && userHashInfo.date_stamp_config === 0)) ?{width: '100%'}:{width: '95%'}">捺印設定</vs-button>
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
                                            <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 5px 10px" :class="'stamp-list isEdit'">
                                                <vs-col vs-w="6"  v-for="stamp in stamps" :key="stamp.id">
                                                    <div class="wrap-item row-equal-height" style="margin-bottom: 15px; display: flex; justify-content: center;">
                                                        <div :class="'stamp-item ' + (stampSelected && stamp.id === stampSelected.id ? 'selected': '')" @click="clickStamp(stamp.id)" >
                                                            <img :src="'data:image/png;base64,'+stamp.url" alt="stamp-img" v-tooltip.top-center="stamp.stamp_flg == 1 || stamp.stamp_flg == 3 ? stamp.stamp_name : ''">
                                                        </div>
                                                    </div>
                                                </vs-col>
                                                <vs-col vs-w="6" v-if="stickyNoteFlg">
                                                  <div class="wrap-item row-equal-height" style="margin-bottom: 15px; display: flex; justify-content: center;">
                                                    <div :class="'stamp-item ' +  (isStickySelected ? 'selected': '')">
                                                      <img class="add-text-icon" width="100" height="100" :src="require('@assets/images/sticky_icon.svg')" alt="stamp-img" v-on:click="selectSticky()">
                                                    </div>
                                                  </div>
                                                </vs-col>
                                            </vs-row>
                                            <!--PAC_5-1488 クラウドストレージを追加する Start-->
                                            <template v-if="(circular?circular.text_append_flg==1:false)&&(companyLimit?companyLimit.text_append_flg==1:false)">
                                            <!--PAC_5-1488 End-->
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
                                            <template v-if="!isTemplateCircular && hasPlanCircularUsers.length <= 0">
                                              <vs-row :class="'item sended ' + (user.circular_status === CIRCULAR_USER.NOT_NOTIFY_STATUS ? 'unsend': 'sended') + ' ' + (index === 0 ? ' maker ':'') + (user.is_send_back === 1 ? ' sendback': '')" vs-type="flex" v-for="(user, index) in showCircularHasReturnUsers" v-bind:key="user.email + index" :index="index">
                                                  <vs-col vs-w="10">
                                                      <p>{{user.name}} <span class="final" v-if="user.is_send_back === 1">差戻し</span></p>
                                                      <p>{{user.email}}</p>
                                                  </vs-col>
                                                  <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                      <p v-if="circularHasReturnUsers && index === circularHasReturnUsers.length - 1" class="final">最終</p>
                                                      <a v-if="user.is_flag === 1" href="#" class="ml-1"> <i class="far fa-flag"></i></a>
                                                  </vs-col>
                                              </vs-row>
                                            </template>
											                      <!-- 特設サイト受取側組織名表示 start -->
                                            <vs-row v-if="specialCircularFlg && !specialCircularReceiveFlg" class="item unsend" vs-type="flex">
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
                                            <!--plan users start-->
                                            <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + ((user.is_send_back === 1) ? ' sendback': '')" vs-type="flex" v-for="(user, index) in hasPlanCircularUsers" v-bind:key="user.email + index" :index="index">
                                                <template v-if="!user.plan_users">
                                              <vs-col vs-w="10">
                                                        <p>{{user.name}} <span class="final" v-if="user.is_send_back === 1">差戻し</span></p>
                                                        <p>{{user.email}}</p>
                                              </vs-col>
                                              <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                        <p v-if="hasPlanCircularUsers.length>0 && index === (hasPlanCircularUsers.length - 1)" href="#" class="final">最終</p>
                                                        <a v-if="user.is_flag === 1" href="#"> <i class="far fa-flag"></i></a>
                                              </vs-col>
                                                </template>
                                                <template v-if="user.plan_users">
                                                    <vs-col vs-w="10">
                                                        <p>合議 ({{user.plan_mode==1?"全員必須":user.plan_score+"人"}})</p>
                                                        <template v-for="(user, itemIndex) in user.plan_users">
                                                            <p :key="itemIndex + user.name">{{user.name}} 【{{user.email}}】<span class="final" v-if="user.is_send_back === 1">差戻し</span></p>
                                                        </template>
                                                    </vs-col>
                                                    <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                        <p v-if="index === hasPlanCircularUsers.length - 1" href="#" class="final">最終</p>
                                                        <template v-for="(user, itemIndex) in user.plan_users">
                                                            <a v-if="user.is_flag === 1" class="ml-1" href="#" :key="itemIndex + user.email"> <i class="far fa-flag"></i></a>
                                                        </template>
                                                    </vs-col>
                                                </template>
                                            </vs-row>
                                            <!--plan users end-->
                                            <!-- template route users start -->
                                            <vs-row :class="'item sended maker ' + (circularUsers[0].circular_status === CIRCULAR_USER.NOT_NOTIFY_STATUS ? 'unsend': 'sended') + ' ' + ((circularUserSendBack && circularUsers[0].id === circularUserSendBack.id) ? ' sendback': '')" vs-type="flex" v-if="isTemplateCircular">
                                                <vs-col vs-w="10">
                                                    <p>{{circularUsers[0].name}} <span class="final" v-if="circularUsers[0].circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                    <p>{{circularUsers[0].email}}</p>
                                                </vs-col>
                                                <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                    <p v-if="1 === circularUsers.length" href="#" class="final">最終</p>
                                                    <a v-if="circularUserLastSend && circularUserLastSend.id === circularUsers[0].id" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                            </vs-row>
                                            <template v-if="isTemplateCircular">
                                              <vs-row :class="'item sended ' + (userRoute[0].circular_status === CIRCULAR_USER.NOT_NOTIFY_STATUS ? 'unsend': 'sended') + ' ' + ((circularUserSendBack && userRoute[0].child_send_order === circularUserSendBack.child_send_order) ? ' sendback': '')" vs-type="flex" v-for="(userRoute, index) in templateUserRoutes" :index="index" :key="index">
                                                  <vs-col vs-w="10">
                                                      <p>{{userRoute[0].user_routes_name}}</p>
                                                      <template v-for="(user, itemIndex) in userRoute">
                                                          <p :key="itemIndex + user.name">{{user.name}} 【{{user.email}}】<span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                      </template>
                                                  </vs-col>
                                                  <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                      <p v-if="index === templateUserRoutes.length - 1" class="final">最終</p>
                                                      <template v-for="(user, itemIndex) in userRoute">
                                                          <a v-if="circularUserLastSend && circularUserLastSend.id === user.id" href="#" class="ml-1" :key="itemIndex + user.email"> <i class="far fa-flag"></i></a>
                                                      </template>
                                                  </vs-col>
                                              </vs-row>
                                            </template>
                                            <!-- template route users end -->
                                            <vs-row v-if="circularUsers && circularUsers.length > 0 && !specialCircularFlg" class="item unsend" vs-type="flex">
                                                <vs-col vs-w="10">
                                                    <p>{{circularUsers[0].name}}</p>
                                                    <p>{{circularUsers[0].email}}</p>
                                                </vs-col>
                                                <vs-col vs-type="flex" vs-w="2" vs-justify="flex-end" vs-align="center">
                                                    <a v-if="!circularUserLastSend" href="#" class="mr-2"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                            </vs-row>
                                        </div>
                                    </vs-tab>
                                    <vs-tab :label="stickyNoteFlg?'コメント/付箋':'コメント'" class="comment-height"  v-on:click="clickComments()">
                                        <div class="comments comment-height comment-position">
                                            <vs-tabs class="comment-height" v-model="tab_comment_info">
                                                <vs-tab label="社内宛" class="comment-tab">
                                                    <vs-row class="item" v-for="(comment, index) in commentsNotPrivate" v-bind:key="comment.name + index" :index="index">
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
                                                    <vs-row class="item" v-for="(comment, index) in commentsIsPrivate" v-bind:key="comment.name + index" :index="index">
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
                                                <vs-tab label="付箋" class="comment-tab" v-if="stickyNoteFlg">
                                                  <StickyNoteFlex :showStickNotes="showStickNotes"
                                                                  @editStickyNoteParent="editStickyNoteParent"></StickyNoteFlex>
                                                </vs-tab>
                                            </vs-tabs>

                                            <div style="position: absolute;bottom:0;width:100%;" v-show="doPermission && !userHashInfo.is_external && tab_comment_info < 2">
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
                                                    <p class="w-full">
                                                      <span class="user-name">{{history.name}}</span><br/>
                                                      <span class="date">{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span> <br/>
                                                      <span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span>
                                                    </p>
                                                </vs-col>
                                            </vs-row>

                                            <vs-row class="item" v-for="(history, index) in histories.text" v-bind:key="history.email + index + '_text'" :index="index">
                                                <vs-col vs-w="3" class="p-2 text-center">
                                                    テキスト：
                                                </vs-col>
                                                <vs-col vs-w="9" vs-type="flex" vs-align="center" vs-justify="flex-start">
                                                    <p class="w-full">
                                                        <strong style="word-wrap:break-word; overflow:hidden;display:block;" :title="history.text"  v-html="history.text"></strong>
                                                        <span class="user-name"  style="word-wrap:break-word; overflow:hidden;" >{{history.name}}</span><br/>
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
                        </div>
                        <a class="close-stamp-sidebar" v-on:click="onStampToolbarActiveChange(false)" href.prevent><i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="open-stamp-sidebar" :style="stampToolbarActive ? 'display:none':'' ">
                        <vs-col style="height: 100%" vs-type="flex" vs-align="center" vs-justify="center" vs-w="12">
                            <a href.prevent v-on:click="onStampToolbarActiveChange(true)"><i class="fas fa-angle-left"></i></a>
                        </vs-col>
                    </div>
                </vs-col>
                </div>
            </vs-row>
        </vs-card>
        <vs-popup class="holamundo" v-if="doPermission"  title="印面登録" :active.sync="generateStampPopupActive">
            <vs-row class="mb-4">
                <vx-input-group  class="w-full mb-0">
                    <vs-input v-model="generateStampName" />
                    <template slot="append">
                        <div class="append-text btn-addon">
                            <vs-button :disabled="!generateStampName" color="primary" v-on:click="onSearchStammp">検索</vs-button>
                        </div>
                    </template>
                </vx-input-group>
            </vs-row>
            <vs-row v-if="stamps && stamps.length > 0" class="mb-4">
                <p>印面を選択して下さい</p>
                <div class="stamp-search-list p-4 w-full">
                    <vs-col vs-w="4"  :class="'stamp-item ' + (stampSelected && stamp.id === stampSelected.id ? 'selected': '')" v-for="stamp in stamps" :key="stamp.id">
                        <img v-on:click="clickStamp(stamp.id)" :src="'data:image/png;base64,'+stamp.url" alt="stamp-img">
                    </vs-col>
                </div>
                <p>印面は後で変更することができます</p>
            </vs-row>
            <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="primary" type="filled" :disabled="!stampSelected || !onSearchAfter" v-on:click="onChooseStamp"> 登録</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="generateStampPopupActive = false"> キャンセル</vs-button>
            </vs-row>
        </vs-popup>
    </div>

        <!-- 5-277 mobile html -->
        <div id="main-home-mobile" v-if="isMobile"  :class="isMobile?'mail mobile':''">
            <div style="width:100%;height:37px;position:relative;" v-if="circularUsers && circularUsers.length > 0">
                <span style="top:-10px;" v-if="showEdit" @click="showEdit=false"><vs-icon style="top:-5px;" icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
                <div class="docName" style="display:inline;position:absolute;">{{files.length?files[0].name:''}}</div>
            </div>
            <div style="width:100%;height:62px;background-color: #f2f2f2;padding-top:20px;" v-if="circularUsers && circularUsers.length > 0">From : {{circularUsers && circularUsers.length > 0 ? circularUsers[0].name : ''}}<span style="float: right;">{{circularUsers[0].create_at | moment("YYYY/MM/DD")}}</span></div>
            <div v-if="!showEdit">
                <div class="tools" :style="!stampToolbarActive ? 'display:none':'' ">
                    <div>
                        <div style="padding: 0">
                            <div v-if="circularHasReturnUsers && circularHasReturnUsers.length > 0">
                                <div>
                                    <div id="mail-list-label" class="tab-active" v-on:click="changeTabMail()">回覧先</div>
                                    <div id="comments-label" class="tab" v-on:click="changeTabComments()">コメント</div>
                                </div>
                                <div>
                                    <div class="mail-list">
                                        <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + (user.is_send_back === 1 ? ' sendback': '')" vs-type="flex" v-for="(user, index) in circularUsers" v-bind:key="user.email + index" :index="index">
                                            <vs-col vs-w="1" vs-align="center">
                                                <a v-if="user.is_flag === 1" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                            </vs-col>
                                            <vs-col vs-w="5">
                                                <p>{{user.name?user.name:user.email}} <span class="final" v-if="user.is_send_back === 1">差戻し</span>
                                                    <span v-if="index === circularHasReturnUsers.length - 1" href="#" class="final">最終</span>
                                                </p>
                                            </vs-col>
                                            <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                                <p>依頼日：{{user.update_at | moment("YYYY/MM/DD")}}</p>
                                            </vs-col>
                                        </vs-row>
                                        <vs-row v-if="circularHasReturnUsers && circularHasReturnUsers.length > 0" class="item unsend" vs-type="flex">
                                            <vs-col vs-type="flex" vs-w="1" vs-align="center">
                                            </vs-col>
                                            <vs-col vs-w="5">
                                                <p>{{circularHasReturnUsers[0].name}}</p>
                                            </vs-col>
                                            <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                                <p>依頼日：{{circularHasReturnUsers[0].create_at| moment("YYYY/MM/DD")}}</p>
                                            </vs-col>
                                        </vs-row>
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

            <div v-if="reviewPermission" class="btn_dialog">
                <div v-on:click="goToSendback" :disabled="files.length <= 0">
                  <div><img :src="require('@assets/images/pages/home/back_to_former_person.svg')"></div>
                  <div>差戻し</div>
                </div>

                <div v-on:click="onApprovalRequest" :disabled="files.length <= 0" v-if="hasRequestSendBack && hasRequestApprovalSendBack && isParentSendOrder">
                  <div><i class="far fa-envelope" style="color:#107fcd;margin-right: 5px;"></i></div>
                  <div>差戻し承認</div>
                </div>


                <div v-on:click="goToReDestination" :disabled="files.length <= 0">
                  <div><i class="far fa-envelope" style="color:#107fcd;margin-right: 5px;"></i></div>
                  <div>次へ</div>
                </div>
            </div>


            <div v-if="circularUsers && circularUsers.length > 0 && !showEdit && doPermission && !reviewPermission" class="btn_dialog">
                <div v-on:click="goToDestination" :disabled="(!$store.state.home.fileSelected)"><div><img :src="require('@assets/images/mobile/approval.svg')"></div><div>承認</div></div>
                <div v-on:click="goToSendback" :style="(circularUserLastSend && circularUserLastSend.child_send_order === 0) ? 'display:none':''" :disabled="files.length <= 0"><div><img :src="require('@assets/images/mobile/refund.svg')"></div><div>差戻し</div></div>
                <div v-on:click="onDiscardCircularClick" :style="(circularUserLastSend && circularUserLastSend.child_send_order === 0) ? '':'display:none'" :disabled="files.length <= 0"><div><img :src="require('@assets/images/mobile/delete.svg')"></div><div>回覧破棄</div></div>
                <div v-on:click="docEdit"><div><img :src="require('@assets/images/mobile/edit.svg')"></div><div>文書編集</div></div>
            </div>
            <div v-if="circularUsers && circularUsers.length > 0 && showEdit && doPermission && !reviewPermission && file_is_open" :class="'btn_dialog edit' + ( isShowTextFunction?' full':' nofull' ) " @touchstart="handleDialogStart" @touchend="handleDialogEnd" @touchmove="handleDialogMove">
                <div class="btn_dialog_status"
                    v-on:click="handleClickDialogStatus"
                    @touchstart="dragStartDialogStatus"
                    @touchmove="dragMoveDialogStatus"
                    @touchend="dragEndDialogStatus"
                    v-if="isShowTextFunction"
                  >

                  <span :class="'status_name '+(isTextSelected?'text':'')">{{ isTextSelected ? 'T' : '印' }}</span>
                  <span class="status_icon"><i style="color: #0984e3;" class="fa fa-cloud"></i></span>
                </div>
                <div v-if="doPermission && !userHashInfo.is_external" v-on:click="showStamps"><div class="icon"><img :src="require('@assets/images/mobile/stamp.svg')"></div><div class="label">押印</div></div>
                <!-- External -->
                <div v-if="doPermission && userHashInfo.is_external" v-on:click="handleButtonShowStamp"><div><img :src="require('@assets/images/mobile/stamp.svg')"></div><div>押印</div></div>

                <div v-on:click="selectTextCustom(currentPageNo)" v-if="doPermission && isShowTextFunction">
                  <div class="icon"><img :src="require('@assets/images/mobile/text.png')"></div>
                  <div class="label">テキスト</div>
                </div>

                <div v-on:click="goToDestination" :disabled="(!$store.state.home.fileSelected)"><div class="icon"><i class="far fa-envelope" style="color:#107fcd;"></i></div><div class="label">承認</div></div>

                <div @click="undoAction">
                  <div class="icon"><i class="fas fa-undo-alt"></i></div><div class="label">元に戻す</div>
                </div>
                <div @click="AddStampsConfirmationMobile(currentPageNo)">
                  <div class="icon"><i class="fa">&#xf01e;</i></div><div class="label">やり直し</div>
                </div>

                <div :style="isShowTextFunction?'margin-top:10px;':''">
                  <div v-on:click="goToSendback" :style="(circularUserLastSend && circularUserLastSend.child_send_order === 0) ? 'display:none':''" :disabled="files.length <= 0"><div class="icon"><img :src="require('@assets/images/mobile/refund.svg')"></div><div class="label">差戻し</div></div>
                  <div v-on:click="onDiscardCircularClick" :style="(circularUserLastSend && circularUserLastSend.child_send_order === 0) ? '':'display:none'" :disabled="files.length <= 0"><div class="icon"><img :src="require('@assets/images/mobile/delete.svg')"></div><div class="label">回覧破棄</div></div>
                </div>

                <!--
                <div v-on:click="undoAction" :disabled="disabledUndo"><div><img :src="require('@assets/images/mobile/back.svg')"></div><div>戻る</div></div>  PAC_5-2144 戻るボタンで捺印・テキスト取り消し
                <div v-on:click="showEdit=false"><div><img :src="require('@assets/images/mobile/confirm.svg')"></div><div>確定</div></div>
                -->
            </div>
            <div class="work-content">
                <!-- ファイル・ページナビゲーション 表示 -->

                <div v-show="showEdit">
                  <div id="handleMailList" v-on:click="handleMailList">
                    <div class="icon hide">
                      <vs-icon icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                    </div>
                    回覧先 &nbsp; &bull; &nbsp; コメント
                  </div>

                  <div id="mail_list_box">
                    <div style="position: relative; z-index: 2; display: inline-block; width: 100%;">
                        <div id="mail-list-label" class="tab-active" v-on:click="changeTabMail()">回覧先</div>
                        <div id="comments-label" class="tab" v-on:click="changeTabComments()">コメント</div>
                        <div id="history-label" class="tab" v-on:click="changeTabHistory()">捺印履歴</div>
                    </div>

                    <div class="mail-list">
                      <div :class="'item sended ' + (index === 0 ? ' maker ':'') + (user.is_send_back === 1 ? ' sendback': '')" v-for="(user, index) in circularUsers" v-bind:key="user.email + index" :index="index">

                        <div class="mail_list_info">

                          <div class="mail_flag"><a v-if="user.is_flag === 1" class="ml-1" href="#"> <i class="far fa-flag"></i></a></div>

                          <p class="name">{{user.name}}</p>
                          <p class="email">{{user.email}}</p>

                          <div class="mail_flag_final">
                            <span class="final" v-if="user.is_send_back === 1">差戻し</span>
                            <span v-if="index === circularHasReturnUsers.length - 1" href="#" class="final">最終</span>
                          </div>

                        </div>

                      </div>

                      <div v-if="circularHasReturnUsers && circularHasReturnUsers.length > 0" class="item unsend">
                        <div class="mail_list_info">
                          <p class="name">{{circularHasReturnUsers[0].name}}</p>
                          <p class="email">{{circularHasReturnUsers[0].email}}</p>
                        </div>
                      </div>

                    </div>

                    <!-- Comment -->
                    <div class="comments" style="display: none;">
                      <vs-tabs class="comment-height" v-model="tab_comment_info">
                        <vs-tab label="社内宛" class="comment-tab">
                          <vs-row class="item comment-tab" v-for="(comment, index) in commentsNotPrivate" v-bind:key="comment.name + index" :index="index">
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
                          <vs-row class="item comment-tab" v-for="(comment, index) in commentsIsPrivate" v-bind:key="comment.name + index" :index="index">
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
                    </div>

                    <!-- History -->
                    <div class="histories" style="display: none;">
                        <vs-row class="item" v-for="(history, index) in histories.stamp" v-bind:key="history.email + index" :index="index"
                                @click.native="onClickStampHistory(history)">
                            <vs-col vs-w="3" class="p-2">
                                <img :src="'data:image/png;base64,'+history.stamp_image" alt="stamp-img">
                            </vs-col>
                            <vs-col vs-w="9" vs-type="flex" vs-align="center" vs-justify="flex-start">
                                <p class="w-full mr-3"><span class="user-name">{{history.name}}</span> <span class="date">{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span> <br/><span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span></p>
                            </vs-col>
                        </vs-row>

                        <vs-row class="item" v-for="(history, index) in histories.text" v-bind:key="history.email + index + '_text'" :index="index">
                            <vs-col vs-w="3" class="p-2 text-center">
                                テキスト：
                            </vs-col>
                            <vs-col vs-w="9" vs-type="flex" vs-align="center" vs-justify="flex-start">
                                <p class="w-full mr-3">
                                    <strong style="word-wrap:break-word; overflow:hidden;display:block;" :title="history.text"  v-html="history.text"></strong>
                                    <span class="user-name"  style="word-wrap:break-word; overflow:hidden;" >{{history.name}}</span>
                                    <span class="date"  style="word-wrap:break-word; overflow:hidden;" >{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span>
                                    <br/><span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span>
                                </p>
                            </vs-col>
                        </vs-row>
                        <vs-row  v-if="histories.text" :style="`margin-top:5px`"></vs-row>
                    </div>


                  </div>

                </div>

                <!--
                <div v-show="showEdit" class="docName" style="text-align: center; margin:0 auto; line-height: 40px;">{{files.length?files[0].name:''}}</div>
                -->

                <div v-show="showEdit"
                      :class="'tabSelected ' +
                      ( (enableAdd  && isFileCreatedByOwn(fileSelected)) ? ' is-remove-icon ' : '' ) +
                      ( (userHashInfo && !userHashInfo.is_external && enableAdd && files.length > 0 && doPermission && files.length < 5) ? ' is-add-icon ' : '' )"
                    >
                  <div>
                    <select v-model="tabSelected" @change="changeSelectedFile(tabSelected)" >
                      <option v-for="(file,index) in files" :key="index" :value="index" :selected="(index==0)?'selected':''" >{{file.name}}</option>
                    </select>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="#0984e3" d="M311.9 335.1l-132.4 136.8C174.1 477.3 167.1 480 160 480c-7.055 0-14.12-2.702-19.47-8.109l-132.4-136.8C-9.229 317.8 3.055 288 27.66 288h264.7C316.9 288 329.2 317.8 311.9 335.1z"/></svg>

                    <a class="remove-document" :key="confidentialFlg + tabSelected + 'delete'" v-if="enableAdd  && isFileCreatedByOwn(fileSelected)" v-on:click="onCloseDocumentClick(fileSelected, tabSelected)" v-show="!specialCircularFlg">
                        <i  class="close fas fa-times"></i>
                    </a>

                    <a index="99999" class="add-document" v-if="userHashInfo && !userHashInfo.is_external && enableAdd">
                        <span v-if="files.length > 0 && doPermission && files.length < 5"  v-show="!specialCircularFlg" v-on:click="onAddFileClick">
                          <span v-show="file_is_open"><i class="fas fa-plus"></i></span>
                          <span v-show="!file_is_open"><i class="fas fa-minus"></i></span>
                        </span>
                    </a>

                  </div>
                </div>

                <div class="confidential" v-if="userHashInfo && fileSelected && fileSelected.create_user_id === userHashId && fileSelected.enableDelete && enableAdd && companyConfidentialFlg" v-show="specialCircularFlg">
                    <div class="confidential-input"><vs-checkbox class="confidentialCheck" :value="confidentialFlg" v-on:click="onConfidentialFlgLabelClick"></vs-checkbox></div>
                    <div v-on:click="onConfidentialFlgLabelClick" class="confidential-label">社外秘に設定</div>
                </div>

                <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="upload-wrapper" v-if="!fileSelected && !file_is_open">

                    <div class="vx-col w-full md:w-1/2 upload-box" id="dropZone" @drop="handleFileSelect" @dragleave="handleDragLeave" @dragover="handleDragOver" v-if="settingLimit && settingLimit.storage_local === 1">
                        <label class="wrapper" for="uploadFile">
                        <input type="file" ref="uploadFile" multiple accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf" id="uploadFile" v-on:change="onUploadFile" />
                        <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                            <label for="uploadFile" class="pb-5"><strong>クリックしてファイルを選択してください</strong></label>
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
                            <input type="file" ref="uploadFile" multiple accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf" id="uploadFile" v-on:change="onUploadFile" />
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
                    <vs-divider class="label-upload-cloud" color="primary" style="font-size:1.5rem">クラウドストレージからファイルを選択</vs-divider>
                    <vs-row
                        vs-align="center"
                        vs-type="flex" vs-justify="center" vs-w="12" class="btn-cloud">
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <span class="download-item-text">OneDrive</span></vs-button>
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox',circular_file)" class="w-25 download-item" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="Dropbox"> <span class="download-item-text">Dropbox</span></vs-button>
                    </vs-row>

                </vs-row>

                <vs-row v-if="mobilePages.length > 0">
                  <vs-col v-show="!showEdit" vs-type="flex" vs-w="12" vs-lg="12" vs-sm="12">
                      <div class="preview-list">
                        <div style="text-align: center;margin-bottom:10px;">
                          <select v-model="tabSelected" @change="changeSelectedFile(tabSelected)">
                            <option v-for="(file,index) in files" :key="index" :value="index">{{file.name}}</option>
                          </select>

                        </div>

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
                  <vs-col v-show="showEdit"
                    vs-type="flex">
                    <div class="preview-list-page">
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
                                    <div  v-if="fileSelected != null">
                              <div class="page page_large" v-for="(page, index) in mobilePages" v-bind:key="index" :index="index" v-show="currentPageNo == index+1">
                                <pdf-page-editor-mobile @showBtn="showDialog" @hideBtn="hideDialog"
                                  ref="editorMobile"
                                  :data-index="index"
                                  @prev="changePage(currentPageNo-1)" @next="changePage(currentPageNo+1)"
                                  :showEdit="showEdit" :config="configKonvaMobile"
                                  :page="page.editorParam" :imageUrl="page.imageUrl" :selected="currentPageNo == index+1"
                                  :deleteFlg="fileSelected.del_flg" :deleteWatermark="fileSelected.delete_watermark"
                                  :isExternal="!(doPermission && !userHashInfo.is_external)"
                                  :isPublic="userHashInfo && userHashInfo.is_external"
                                  :enable="doPermission" :stamps="stampUsed"
                                  @func="docEdit"
                                  @generateStamp="onGenerateStampClickMobile" :isOptionText="isOptionText">
                                </pdf-page-editor-mobile>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </vs-col>
                </vs-row>
            </div>
            <vs-popup class="holamundo" v-if="doPermission"  title="印面登録" :active.sync="generateStampPopupActiveMobile">
                <vs-row class="mb-4">
                    <vx-input-group  class="w-full mb-0">
                        <vs-input v-model="generateStampName" />
                        <template slot="append">
                            <div class="append-text btn-addon">
                                <vs-button :disabled="!generateStampName" color="primary" v-on:click="onSearchStammp">検索</vs-button>
                            </div>
                        </template>
                    </vx-input-group>
                </vs-row>
                <vs-row v-if="stamps && stamps.length > 0" class="mb-4">
                    <p>印面を選択して下さい</p>
                    <div class="stamp-search-list p-4 w-full">
                        <vs-col vs-w="4" :data-cid="stamp.cid" :class="'stamp-item ' + (stampSelected && stamp.cid === stampSelected.cid ? 'selected': '') + ' cid_' + stamp.cid" v-for="stamp in stamps" :key="stamp.id">
                            <!-- <img v-on:click="clickStamp(stamp.id)" :src="'data:image/png;base64,'+stamp.url" alt="stamp-img"> -->
                            <img v-on:click="customChooseStamp($event, stamp.id, stamp.cid)" :src="'data:image/png;base64,'+stamp.url" alt="stamp-img">
                        </vs-col>
                    </div>
                    <p>印面は後で変更することができます</p>
                </vs-row>
                <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <!--<vs-button class="square mr-2" color="primary" type="filled" :disabled="!stampSelected || !onSearchAfter" v-on:click="onChooseStampMobile"> 登録</vs-button>-->
                    <vs-button class="square mr-2" color="primary" type="filled" :disabled="!onSearchAfter" v-on:click="onChooseStampMobile"> 登録</vs-button>
                    <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelChooseStampMobile"> キャンセル</vs-button>
                </vs-row>
            </vs-popup>
            <modal name="access-modal"
                   :pivot-y="0.2"
                   :width="300"
                   :classes="['v--modal', 'upload-modal', 'p-6']"
                   :height="'auto'"
                   @before-close="beforeClose">
                <h2 class="mb-4 pb-2" style="font-size: 18px;">アクセスコードを入力してください</h2>
                <vs-row class="mb-4">
                    <vs-input class="w-full" v-model="access_code" />
                    <p class="text-danger" v-if="showAccessCodeMessage">アクセスコードが正しくありません</p>
                </vs-row>
                <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button class="square mr-2" color="primary" type="filled" :disabled="!access_code" v-on:click="onSubmitAccessCode"> OK</vs-button>
                    <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="onCancelAccessModal"> キャンセル</vs-button>
                </vs-row>
            </modal>
            <modal name="stamps-modal"
                   :pivot-y="0.2"
                   :width="isMobile?500:300"
                   :classes="'v--modal'"
                   :height="'auto'"
                   id="stamps-modal"
                   @before-close="beforeClose">
                <div class="mb-4 mt-4 date_stamp_config" style="text-align: center;" v-show="!(stamps.every(stamp => (stamp.stamp_division != null ? stamp.stamp_division !== 1 : false)) || (userHashInfo && userHashInfo.date_stamp_config === 0))">
                    <flat-pickr class="date_stamp_input"  style="position: absolute;z-index: 1;width: 50px;right:10px;border:0;color:#fff" :config="configdateTimePicker" v-model="date" @on-change="onChangeStampDate" />
                    <vs-button class="stamp-change-date" data-toggle color="primary" type="filled">日付印日時変更</vs-button>
                </div>
                <div class="stamp-list swiper-container mb-4">
                    <swiper :options="swiperOption" class="swiper-wrapper" style="margin:0 40px;">
                        <swiper-slide v-for="stamp in stamps" :key="stamp.id" style="width: 60px;">
                            <div class="wrap-item" style="display: flex; justify-content: center;">
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


        <modal name="upload-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               @before-close="beforeClose"
               id="upload-modal"
               >

            <vs-row class="mb-4 pb-2" vs-justify="flex-end" vs-align="center">
                <label>
                   <!-- <input id="uploadMoreFile" ref="uploadMoreFile" type="file" style="display: none" multiple accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf"  v-on:change="onUploadFile"/>-->
                    <!--PAC_5-1488 クラウドストレージを追加する Start-->
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
                    <!--<vs-button class="square mr-0" color="success" type="filled" :disabled="!uploadCompleted" v-on:click="onAddMoreFileClick"><i class="fas fa-plus"></i> ファイル追加</vs-button>-->
                    <!--PAC_5-1488 End-->
                </label>
            </vs-row>
            <vs-row class="mb-2 pb-4 pt-4 border-bottom">
                <vs-list class=" mb-2 pb-6 border-bottom">
                    <div class="" v-for="(file, index) in fileUploads" v-bind:key="file.name + index" :index="index">
                        <vs-progress v-if="file.loading" indeterminate color="success"></vs-progress>
                        <vs-list-item v-if="!file.loading" :subtitle="`${index + 1}. ${file.name}`" >
                            <vs-chip :color="file.success ? 'success': 'danger'">{{file.success ? '成功': 'エラー'}}</vs-chip>
                        </vs-list-item>
                    </div>

                </vs-list>
                <p>ファイルの保存期間は366日間です。保存期間を過ぎたファイルは削除されます。必要なファイルは、保存期間内にダウンロードしてください。</p>
            </vs-row>
            <vs-row class="mt-2 upload-button" vs-type="flex" vs-justify="flex-end" vs-align="center">
              <!--PAC_5-2242 Start-->
              <vs-button class="square mr-2 choose-upload" style="padding: 0.75rem 1rem;" color="primary" type="filled" :disabled="!fileUploads || fileUploads.length <= 0 || !uploadCompleted || isProcessing" v-on:click="onChooseUpload"><i class="fas fa-arrow-right"></i> プレビュー・捺印へ</vs-button>
              <!--PAC_5-2242 End-->
              <vs-dropdown vs-trigger-click style="font-family: inherit !important;" v-if="!isMobile">
                <vs-button class="square mr-2" style="margin-left: -11px;padding: 0.75rem 1rem;"><i class="fa fa-caret-down" aria-hidden="true"></i></vs-button>
                <vs-dropdown-menu >
                  <li class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" v-model="changeFlg" vs-value="default" v-on:click="changConvertFlg1">デフォルト</vs-radio></a></li>
                  <!--PAC_5-2242 Start-->
                  <li class="vx-dropdown--item" v-if="repagePreviewFlg"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" :disabled="!fileUploads || fileUploads.length !== 1 || !fileUploads[0].isExcel || !uploadCompleted" v-model="changeFlg" vs-value="pageChange" v-on:click="changConvertFlg2">レイアウトが崩れる場合</vs-radio></a></li>
                  <!--PAC_5-2242 End-->
                </vs-dropdown-menu>
              </vs-dropdown>
              <vs-button class="square mr-0 reject-upload" color="#bdc3c7" type="filled" :disabled="!uploadCompleted" v-on:click="onRejectUpload"><i class="fas fa-times"></i> アップロード中止</vs-button>
            </vs-row>
        </modal>
        <modal name="access-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               @before-close="beforeClose">
            <h2 class="mb-4 pb-2" style="font-size: 10px;">アクセスコードを入力してください。</h2>
            <h2 class="mb-4 pb-2" style="font-size: 10px;">アクセスコードをお忘れの場合は、ログインすることで文書を閲覧できます。</h2>
            <vs-row class="mb-4">
                <vs-input class="w-full" v-model="access_code" />
                <p class="text-danger" v-if="showAccessCodeMessage">アクセスコードが正しくありません</p>
            </vs-row>
            <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="primary" type="filled" :disabled="!access_code" v-on:click="onSubmitAccessCode"> OK</vs-button>
                <vs-button class="square mr-0" color="primary" type="filled" v-on:click="onCancelAccessModal"> ログイン画面へ移動する</vs-button>
            </vs-row>
        </modal>
        <modal name="pullbackshow-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               @before-close="beforeClose">
          <br />
          <br />
          <vs-row class="mb-4">
            <p class="text-info" >引戻しまたは差戻しされた可能性があります。</p>
          </vs-row>
          <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button class="square mr-2" color="primary" type="filled" v-on:click="onCancelAccessModal"> ログイン画面に戻る</vs-button>
            <vs-button class="square mr-0" color="primary" type="filled" v-on:click="onCloseCurrentWindows"> 閉じる</vs-button>
          </vs-row>
        </modal>
        <modal name="special-site-modal"
               :pivot-y="0.2"
               :width="500"
               :classes="['v--modal', 'upload-modal', 'p-6']"
               :height="'auto'"
               @before-close="beforeClose">
          <h2 class="mb-4 pb-2" style="font-size: 15px;">現在受信中です。もうしばらくお待ちください。</h2>
          <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button class="square mr-0" color="primary" type="filled" v-on:click="onCloseSpecialModal"> 閉じる</vs-button>
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

        <!-- PAC_5-3116 S -->
        <!--PAC_5-1488 クラウドストレージを追加する Start-->
        <modal name="upload-from-external-modal"
              :pivot-y="0.2"
              :classes="['v--modal', 'upload-from-external-modal', 'p-6', 'custom-template-view-cloud-list']"
              :min-width="200"
              :min-height="200"
              :scrollable="true"
              :reset="true"
              width="40%"
              height="auto"
              @opened="onUploadFromCloudModalOpened"
              :clickToClose="false">
          <!-- PAC_5-3116 E -->
          <vs-row>
            <vs-col vs-w="12" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
              <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="onUploadFromCloudModalClosed()"> <i class="fas fa-times"></i></vs-button>
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
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="onUploadFromCloudModalClosed()">キャンセル</vs-button>
          </vs-row>
        </modal>

        <modal name="updatecheck-doc-modal"
              :pivot-y="0.2"
              :width="500"
              :classes="['v--modal', 'upload-modal', 'p-6']"
              :height="'auto'"
              :clickToClose="false">
          <vs-row>
            <vs-col vs-w="8" vs-type="flex" vs-align="center">
              <h2 class="mb-2 pb-2" style="font-size: 18px;">保存先の確認</h2>
            </vs-col>
            <vs-col vs-w="4" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
              <vs-button radius color="danger" type="flat" style="font-size: 18px;position: absolute;top: 10px;right: 0;" v-on:click="cancelConfirmUpdate"> <i class="fas fa-times"></i></vs-button>
            </vs-col>
          </vs-row>
          <vs-row class="mb-3 pt-3" style="border-top: 1px solid #cdcdcd">
            <p class="mb-4">同名のファイルが存在します。<br>
            <p>上書き保存しますか？</p>
          </vs-row>
          <vs-row class="pt-3" vs-type="flex" vs-justify="flex-end" vs-align="center" style="border-top: 1px solid #cdcdcd">
            <vs-button class="square mr-2" color="success" type="filled" v-on:click="onUploadToCloudClick(true)"> 上書き保存</vs-button>
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="cancelConfirmUpdate"> キャンセル</vs-button>
          </vs-row>
        </modal>

        <modal name="cloud-upload-modal"
                :pivot-y="0.2"
                :classes="['v--modal', 'cloud-upload-modal', 'p-6']"
                :min-width="200"
                :min-height="200"
                :scrollable="true"
                :reset="true"
                width="40%"
                height="auto"
                @opened="onCloudModalOpened"
                :clickToClose="false">
                <CloudUploadModalInner
                  :cloudLogo="cloudLogo"
                  :cloudName="cloudName"
                  :breadcrumbItems="breadcrumbItems"
                  :cloudFileItems="cloudFileItems"
                  :filenameUpload.sync="filename_upload"
                  @breadcrumb-item-click="onBreadcrumbItemClick"
                  @cloud-item-click="onCloudItemClick"
                  @store-click="onUploadCheck()"
                  @cancel-click="onCloudModalClosed()"
                />
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

            <vs-row class="pdf-content" v-if="!(circularStatus == CIRCULAR.CIRCULAR_COMPLETED_STATUS || circularStatus == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS) && !(loginUserCircularStatus == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || loginUserCircularStatus == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS ) && doPermission && !currentViewingUser" style="height:20%;padding-top: 3%;border: 0px;">

                <!--PAC_5-2380 START-->
                <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                    <label for="onUploadAttachment" class="pb-5" style="color: #FF0000; font-size: 17px">
                        <strong>同一企業内のみ確認できる文書です 。</strong>
                    </label>
                </vs-row>
                <!--PAC_5-2380 END-->

                <vs-row vs-type="flex" vs-align="center" vs-justify="center" class="upload-wrapper" v-if="userHashId">

                    <div class="vx-col w-full md:w-5/6 upload-box" style="height: 100px; border-radius: 10px;border: 3px dashed #d1ecff;padding-top: 30px;padding-bottom: 30px;" id="dropZone1" >
                        <label class="wrapper" style="padding: 0px 0px 0px;" for="onUploadAttachment">
                            <input type="file" ref="onUploadAttachment" multiple accept="*/*" id="onUploadAttachment" v-on:change="onUploadAttachment" />
                            <vs-row vs-w="12" vs-type="flex" vs-align="center" vs-justify="center">
                                <label for="onUploadAttachment" class="pb-5"><strong>クリックしてファイルを選択してください</strong></label>
                            </vs-row>
                        </label>
                    </div>
                    <!--PAC_5-1488 クラウドストレージを追加する Start-->
                    <vs-divider color="primary" style="font-size:1.5rem;padding-top: 15px;">クラウドストレージからファイルを選択</vs-divider>
                    <vs-row
                        vs-align="center"
                        vs-type="flex" vs-justify="center" vs-w="12">
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_box : 0 === 1" v-on:click="onUploadFromExternalClick('box',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_onedrive : 0 === 1" v-on:click="onUploadFromExternalClick('onedrive',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <span class="download-item-text">OneDrive</span></vs-button>
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_google : 0 === 1" v-on:click="onUploadFromExternalClick('google',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                        <vs-button color="primary" v-if="settingLimit ? settingLimit.storage_dropbox : 0 === 1" v-on:click="onUploadFromExternalClick('dropbox',attachment_file)" class="w-15 download-item" style="min-width: 140px" type="border"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="Dropbox"> <span class="download-item-text">Dropbox</span></vs-button>
                    </vs-row>
                    <!--PAC_5-1488 End-->
                </vs-row>
            </vs-row>
            <vs-row class="mb-2 pb-4 pt-4 " >
                <vs-list class="vx-list mb-2 pb-6 " style="padding-left: 15%">
                    <div class="" v-for="(file, index) in attachmentUploads" v-bind:key="file.file_name + index" :index="index">
                        <vs-progress v-if="file.loading" indeterminate color="success"></vs-progress>
                        <vs-row class="mb-3">
                            <vs-col vs-w="5" v-if="file.create_user_id == userHashId && file.env_flg == currentEnvFlg && file.server_flg == currentServerFlg && !(circularStatus == CIRCULAR.CIRCULAR_COMPLETED_STATUS || circularStatus == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS)">
                                {{index+1}}.<a href="#" class="link" style="text-decoration:none;color:#000000;" :style=" sanitizing_flg? 'pointer-events: none;':''" v-on:click="onDownloadAttachment(index)">&nbsp;{{ file.file_name }}&nbsp;&nbsp;&nbsp;</a>
                                <a href="#" v-on:click="onDeleteAttachment(index)"><i color="#000" class="fa fa-trash-alt" aria-hidden="true" ></i></a>
                            </vs-col>
                            <vs-col v-else>
                              {{index+1}}.<a href="#" class="link" style="text-decoration:none;color:#000000;" :style=" sanitizing_flg ? 'pointer-events: none;':''" v-on:click="onDownloadAttachment(index)">&nbsp;{{ file.file_name }}&nbsp;&nbsp;&nbsp;</a>
                            </vs-col>
                            <vs-col vs-w="1"></vs-col>
                            <vs-col vs-w="1" v-if="file.create_user_id == userHashId && file.env_flg == currentEnvFlg && file.server_flg == currentServerFlg && !(circularStatus == CIRCULAR.CIRCULAR_COMPLETED_STATUS || circularStatus == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS) && false" vs-align="center" ><vs-checkbox  v-model="file.confidential_flg"  v-on:click="onAttachmentConfidentialFlgClick(index)"></vs-checkbox></vs-col>
                            <vs-col vs-w="3" v-if="file.create_user_id == userHashId && file.env_flg == currentEnvFlg && file.server_flg == currentServerFlg && !(circularStatus == CIRCULAR.CIRCULAR_COMPLETED_STATUS || circularStatus == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS) && false" ><p v-on:click="onAttachmentConfidentialFlgClick(index)" class="confidential-label">社外秘に設定</p></vs-col>
                        </vs-row>
                        <vs-row  v-if="histories.text" :style="`margin-top:5px`"></vs-row>
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
        <vs-popup classContent="popup-example" title="名刺情報" :active.sync="showBizcardInfo" class="bizcard-info-modal">
            <div v-if="bizcardData != null">
                <div style="display: flex; justify-content: center;">
                    <img :src="bizcardData.bizcard" class="bizcard_image">
                </div>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">名刺ID</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.bizcard_id }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">名前</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">会社名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.company_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">電話番号</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.phone_number }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">住所</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.address }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">メールアドレス</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.email }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">部署</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.department }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">役職</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.position }}</vs-col>
                </vs-row>
            </div>
            <div v-else>
                名刺情報がありません
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="showBizcardInfo = false;" color="primary">閉じる</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup
                title="確認"
                :active.sync="activeSaveLongtermModal"
        >
            <div class="mb-0">長期保管を行います。よろしいですか？</div><br>
            <div style=" width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.1);"></div>
            <div class="mb-3 mt-3">キーワード登録</div>
            <vs-textarea v-model="keywords"/>
            <div v-if="checkKeywordsLenFlg" style="color:red">入力できる文字数は200文字が最大です。</div>
            <div>キーワードを複数登録する場合は改行して下さい。</div>
            <br v-if="showTree">
            <div v-if="showTree" style=" width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.1);"></div>
            <div v-if="showTree" class="mb-3 mt-3">フォルダ選択</div>
            <div v-if="folderSelect" style="color:red">フォルダを選択してください。</div>
            <div v-if="showTree" style="border: 1px solid rgba(0, 0, 0, 0.1);border-radius: 5px;overflow: auto;height: 250px;">
                <FolderTree ref="tree" v-show="showTree" :treeId="selectApprovalTree" @onNodeClick="setFolderId"></FolderTree>
            </div>
            <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="primary" type="filled" v-on:click="onSaveLongTermAccept"> はい</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="activeSaveLongtermModal = false"> キャンセル</vs-button>
            </vs-row>
        </vs-popup>

    </div>
</template>
<script>
  import { mapState, mapActions } from "vuex";
  import InfiniteLoading from 'vue-infinite-loading';
  import PdfPages from "../../components/home/PdfPages";
  import PdfPageThumbnails from "../../components/home/PdfPageThumbnails";
  import PdfPageEditorMobile from "../../components/home/PdfPageEditorMobile";

  import flatPickr from 'vue-flatpickr-component'
  import 'flatpickr/dist/flatpickr.min.css';
  import {Japanese} from 'flatpickr/dist/l10n/ja.js';
  import { dragscroll } from 'vue-dragscroll'
  import { CIRCULAR } from '../../enums/circular';
  import { CIRCULAR_USER } from '../../enums/circular_user';
  import Utils from '../../utils/utils';
  import { getPageUtil } from '../../utils/pagepreview';
  import Axios from "axios";

  import config from "../../app.config";

  import draggable from 'vuedraggable'
  import 'swiper/dist/css/swiper.css'
  import { swiper, swiperSlide } from 'vue-awesome-swiper'
  import _ from "lodash";
  import {cloneDeep} from "lodash/lang";
  import FolderTree from '../../components/long_term/FolderTree';
  import CloudUploadModalInner from "@/components/home/CloudUploadModalInner.vue"
  import UpdateCheckDocModalInner from "@/components/home/UpdateCheckDocModalInner";
  import BizcardPopUpInner from '@/components/home/BizcardPopUpInner.vue';
  import StickyNoteFlex from "../../components/stick-note/StickyNoteFlex";

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
      FolderTree,
      CloudUploadModalInner,
      UpdateCheckDocModalInner,
      BizcardPopUpInner,
      StickyNoteFlex
    },
    directives: {
      dragscroll
    },
    data() {
      return {
        CIRCULAR: CIRCULAR,
        CIRCULAR_USER: CIRCULAR_USER,
        histories: [],
        pages: [],
        thumbnails: [],
        currentPageNo: 1,
        maxTabShow: 3,
        rotateAngle: 0,
        opacity:0,
        configKonvaMobile: {
            width: 400,
            height: 0
        },
        tabSelected: 0,
        date: null,
        configdateTimePicker: {
          locale: Japanese,
          wrap: true,
          disableMobile: true
        },
        tab_comment_info: 0, //社内社外宛先デフォルトtab
        stampToolbarActive: true,
        generateStampName: '',
        generateStampPopupActive: false,
        generateStampPopupActiveMobile: false,
        eCanvas: null,
        access_code: '',
        isValidAccessCode: true,
        showAccessCodeMessage: false,
        fileUploads: [],
        fileAfterUploads: [],
        uploadCompleted: false,
        zoom: 100,
        userHashInfo: null,
        deleteItem: null,
        deleteIndex: null,
        oldTabSelected: null,

        disabledUndo: true,
        filename_upload: '',
        enableAdd: true,
        confirmAddSignature: false,
        tab_cir_info: 0,
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
        file_name: '', // ファイル名
        old_file_name: '', // 元ファイル名
        docName: '',
        clickState: false, // 二重チェック用
        onSearchAfter: false,
        documentComment:'', //社内社外宛先コメント
        dialogDispFlag: false,
        canStoreCircular:false,
        sanitizing_flg:0,
        keywords: '',
        checkKeywordsLenFlg: false,
        activeSaveLongtermModal: false,
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
          //PAC_5-1053　コメント有無お知らせ機能追加
        commentsExist: false,
        commentsChecked:[], //コメント既読をした回覧ファイルID
        noticeLeft: '0px',
        noticeWidth: '10px', //お知らせアイコンサイズ
        is_ipad: false,
        viewerWidth: 1,
        thumbnailViewerWidth: 0,
        visiblePageRange: [-1, -1],
        visibleThumbnailRange: [-1, -1],
        longtermIndex: [],
        finishedDate: '',
        isProcessing: false,
        attachmentUploads : [],
        attachmentAfterUploads : [],
        isShowAttachment:true,
        stampDisplays:[],
        stampUsed:[],
        changeFlg:'default',
        keywords_flg: null,
        // PAC_5-1488 クラウドストレージを追加する Start
        settingLimit:{},
        cloudFileItems: [],
        breadcrumbItems: [],
        cloudLogo: null,
        cloudName: null,
        currentCloudFolderId: 0,
        filename_selected_from_cloud: '',
        fileid_selected_from_cloud: 0,
        is_download_external:null,
        excelSupportExtensions: ['xls', 'xlt', 'xlm', 'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xla', 'xlam', 'xll', 'xlw'],
        wordSupportExtensions: ['doc', 'dot', 'wbk', 'docx', 'docm', 'dotx', 'dotm', 'docb'],
        isAttachmentFlg:false,
        circular_file:'circular',
        // PAC_5-1488 End
        companyConfidentialFlg: 0,
        specialCircularReceiveFlg: false,//回覧ユーザーが特設サイトの受取側ですか
        circularUserLastSendIdIsSpecial: false,//現在未操作のユーザは特設サイトの受取側ですか
        specialCircularFlg:false,//特設サイト回覧
        specialButtonDisableFlg: false,//特設サイト申請画面、ボタン非アクティブ
        groupName: '',//特設サイト受取側組織名
        radioVal: "default",
        isMobile: false,
        isTablet: false,
        countAllTabNum: false,
        showTree: false,
        folderId: '',
        folderSelect: false,
        selectApprovalTree: 'selectApprovalTree',
        showFolderFlg:false,
        dialog_y: 0,
        isOptionText: false,
        stamp_id_temp: 1,
        stamp_cid_temp: 1,
        file_is_open: true,
        default_stamp_history_flg: 0,
        showStickNotes:[],
        isShowTextFunction: true
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
        currentViewingUser: state => state.home.currentViewingUser,
        addTextHistory: state => state.home.addTextHistory,
        tempComments: state => state.home.tempComments, //社内社外宛先一時入力コメント
        deviceType: state => state.home.deviceType,
        isStickySelected: state => state.home.StickySelected,
        stickyNoteFlg: state => state.user.sticky_note_flg,
        expectedPagesSize: state => {
          // ページ画像
          // 推定サイズ: 実際の画像と誤差あることあり (±1px程度)
          const pagesInfo = state.home.fileSelected?.pagesInfo ?? [];
          return pagesInfo.map(pageInfo => ({
            width: pageInfo.width_pt / 72 * PPI,
            height: pageInfo.height_pt / 72 * PPI,
          }));
        },
        // PAC_5-2242 Start
        registeredDocInfoList: state => {
          if (!state.home.circular || state.home.circular.id !== state.pageBreaks.circularIdForRegisteredDocs) {
              return [];
          }
          return state.pageBreaks.registeredDocInfoList;
        },
        // PAC_5-2242 End
      }),
      realOpacity(){
        return (1-this.opacity/100);
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
        let circular_user = null;
        // 合議の場合
        if(this.userHashInfo && this.isTemplateCircular){
            // 終わった場合
            if(this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_STATUS || this.circular.circular_status === this.CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS){
                circular_user = null;
            }else{
                let circular_users = [];
                // 差戻のcircular_user
                let circular_user_send_back = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                // 差戻の場合
                circular_users = this.circular.users.slice().filter(item =>
                (item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS ||
                item.circular_status === CIRCULAR_USER.READ_STATUS ||
                item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS ||
                item.circular_status === CIRCULAR_USER.END_OF_REQUEST_SEND_BACK ||
                item.circular_status === CIRCULAR_USER.REVIEWING_STATUS));
                if(circular_user_send_back){
                    // 差戻 同級のメール 除外する
                    circular_users = circular_users.filter(item => item.child_send_order !== circular_user_send_back.child_send_order);
                }

                // get 処理中 child_send_order
                let distinct_child_orders = [];
                for(let i = 0;i < circular_users.length;i ++){
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
                                    // 一つのノードが複数存在するからです。 userHashInfoのemail 選択  && item.email === this.userHashInfo.email
                                    let cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.userHashInfo.email);
                                    if(cir !== undefined){
                                        circular_user = cir;
                                    }else{
                                        circular_user = circular_users[i];
                                    }
                                    break;
                                }else if(all_arr[0].wait == 0){
                                    if(approved_arr.length >= all_arr[0].score){
                                        continue;
                                    }else{
                                        // 一つのノードが複数存在するからです。 userHashInfoのemail 選択  && item.email === this.userHashInfo.email
                                        let cir = circular_users.find(item => item.child_send_order === circular_users[i].child_send_order && item.email === this.userHashInfo.email);
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
            circular_user = this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.END_OF_REQUEST_SEND_BACK || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS);
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

        const findUser = this.circularUsers.find(item => circular_user.id === item.id);
        if(findUser) return circular_user;
        return this.circularUsers.find(item => circular_user.parent_send_order === item.parent_send_order && item.child_send_order === 1);
      },
      // DBから入力したコメント取得
      comments: {
           get() {return this.fileSelected && this.fileSelected.comments ? this.fileSelected.comments : []}
      },
      // 画面入力した一時コメント取得
      tempComments: {
        get() {
          return this.fileSelected && this.fileSelected.tempComments ? this.fileSelected.tempComments : []}
      },
      circularUsers: {
        get() {
          if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
          if(!this.circularUserLastSend) {
            // 他会社のウィンドウ以外の人員を削除
            return this.$store.state.home.circular.users.filter(item => {
                return item.child_send_order === 0 || this.userHashInfo.mst_company_id === item.mst_company_id || (item.parent_send_order && item.child_send_order === 1);
            });
          }
          return this.$store.state.home.circular.users.filter(item => {
            return item.child_send_order === 0 || this.circularUserLastSend.parent_send_order === item.parent_send_order || (item.parent_send_order && item.child_send_order === 1);
          });

        },
      },
      sendBackCircularUsersDesc: {
        get() {
          if (this.circularUsers && this.circularUsers.length > 0) {
            let oldArray = this.circularUsers.filter(item=>{return item.child_send_order === 1 && (item.circular_status==CIRCULAR_USER.NOTIFIED_UNREAD_STATUS
                || item.circular_status==CIRCULAR_USER.READ_STATUS || item.circular_status==CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status==CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || item.circular_status==CIRCULAR_USER.PULL_BACK_TO_USER_STATUS)});
            let newArray= oldArray.sort((a, b) => b.parent_send_order- a.parent_send_order)
            return newArray;
          }else{
            return [];
          }
        },
      },
      circularHasReturnUsers: {
        get() {
          if (this.circularUsers && this.circularUsers.length > 0) {
            const circularReturnUsers = [];
            const return_users = [];
            for (let index in this.circularUsers) {
              const item = cloneDeep(this.circularUsers[index]);
              if (!item || item.id === undefined ) continue;
              item.is_flag = 0;
              item.is_return = 0;
              item.has_return = 0;
              item.is_send_back = 0;
              item.is_return_send_back = 0;
              circularReturnUsers.push(item);
              if (return_users.length > 0 && ((!this.circularUsers[parseInt(index) + 1] && item.parent_send_order > 0) || (this.circularUsers[parseInt(index) + 1] && this.circularUsers[parseInt(index) + 1].parent_send_order !== item.parent_send_order))) {
                circularReturnUsers.push(...return_users);
                return_users.length = 0;
              }

              if (this.currentEnvFlg == item.env_flg && this.currentServerFlg == item.server_flg && this.userHashInfo.mst_company_id === item.mst_company_id && item.return_flg === 1 && ((item.child_send_order === 0 && item.parent_send_order === 0) || (item.child_send_order === 1 && item.parent_send_order > 0))) {
                if (!this.circularUsers[parseInt(index) + 1] || this.circularUsers[parseInt(index) + 1].parent_send_order !== item.parent_send_order) continue;
                const return_user = cloneDeep(item);
                return_user.is_return = 1;
                item.has_return = 1;
                if (return_user.return_send_back && return_user.return_send_back === 1 && (return_user.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || return_user.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || return_user.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || return_user.circular_status === CIRCULAR_USER.READ_STATUS)) {
                  return_user.is_return_send_back = 1;
                }
                return_users.push(return_user);
              }
            }
            return circularReturnUsers.filter(item => {
                if ((item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS && item.is_return === 0) || item.is_return_send_back === 1) {
                    item.is_send_back = 1;
                }
                let last_send_id = this.circularUserLastSend ? this.circularUserLastSend.id : this.circularUserLastSendId;
                last_send_id = last_send_id === null || last_send_id === undefined ? -1 : last_send_id;
                if (last_send_id === item.id && ((item.has_return === 0 && item.is_return === 0) || (item.circular_status === CIRCULAR_USER.REVIEWING_STATUS && item.is_return === 1) || ((item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS) && item.is_return === 0))) {
                    item.is_flag = 1;
                }
                return item.id
            });
          }
          return [];
        }
      },
      doPermission: {
        get() {
          /*PAC_5-1698 S*/
          if (this.circularUserLastSend && this.circularUserLastSend.plan_id > 0) {
            let circularUserLastSendPlanUsers = this.hasPlanCircularUsers.find(user => {
              return user.plan_id == this.circularUserLastSend.plan_id && user.plan_users.length > 0
            }).plan_users.filter(item=>{
              return item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.END_OF_REQUEST_SEND_BACK || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS;
            });
            return this.circularUserLastSend && this.userHashInfo && circularUserLastSendPlanUsers.some(user => this.userHashInfo.email === user.email) && !this.hasRequestSendBack && circularUserLastSendPlanUsers.every(user => CIRCULAR_USER.REVIEWING_STATUS != user.circular_status);
          }
          /*PAC_5-1698 E*/
          return this.circularUserLastSend && this.userHashInfo && this.userHashInfo.email === this.circularUserLastSend.email && !this.hasRequestSendBack && this.circularUserLastSend.circular_status != CIRCULAR_USER.REVIEWING_STATUS;
        }
      },
      reviewPermission: {
        get() {
          /*PAC_5-1698 S*/
          if (this.circularUserLastSend && this.circularUserLastSend.plan_id > 0) {
            let circularUserLastSendPlanUsers = this.hasPlanCircularUsers.find(user => {
              return user.plan_id == this.circularUserLastSend.plan_id && user.plan_users.length > 0
            }).plan_users.filter(item=>{
              return item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.END_OF_REQUEST_SEND_BACK || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS;
            });
            return this.circularUserLastSend && this.userHashInfo && circularUserLastSendPlanUsers.some(user => this.userHashInfo.email === user.email) && !this.hasRequestSendBack && circularUserLastSendPlanUsers.some(user => CIRCULAR_USER.REVIEWING_STATUS == user.circular_status);
          }
          /*PAC_5-1698 E*/
          return this.circularUserLastSend && this.userHashInfo && this.userHashInfo.email === this.circularUserLastSend.email && !this.hasRequestSendBack && this.circularUserLastSend.circular_status == CIRCULAR_USER.REVIEWING_STATUS;
        }
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
      userMstCompanyId() {
        if(!this.userHashInfo) return null;
        if(this.userHashInfo.is_external) return null;
        return this.userHashInfo.mst_company_id;
      },
      receivedOnlyFlg() {
          if(!this.userHashInfo) return 1;
          return this.userHashInfo.received_only_flg;
      },
      rotateAngleFlg() {
          if(!this.userHashInfo) return 1;
          return this.userHashInfo.rotate_angle_flg;
      },
      // PAC_5-2242 Start
      repagePreviewFlg() {
          if(!this.userHashInfo) return 1;
          return this.userHashInfo.repage_preview_flg;
      },
      // PAC_5-2242 End
      hasRequestSendBack() {
        return this.circularUsers.some(item => item.circular_status === CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK);
      },
      hasRequestApprovalSendBack() {
        let flag = false;
        let planId = 0;
        flag = this.circularUsers.some(item => item.circular_status === CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK) && this.circularUsers.some(item => item.email === this.loginCircularUser.email && item.parent_send_order > 0 && item.child_send_order === 1 && item.circular_status != CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK);
        if(flag){
          flag = this.sendBackCircularUsersDesc.some((item,index) => {
            if(index === 0){
              planId = item.plan_id;
              if(planId === 0 && item.email === this.loginCircularUser.email && item.parent_send_order > 0
                  && item.child_send_order === 1 && item.circular_status != CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK){
                return true;
              }
            }
            if (planId > 0) {
              if (item.plan_id == planId && item.email === this.loginCircularUser.email && item.parent_send_order > 0
                  && item.child_send_order === 1 && item.circular_status != CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK) {
                return true;
              }
            }
          });
        }
        return flag;
      },
      isParentSendOrder() {
        return this.circularUsers.some(item => item.email === this.userHashInfo.email && item.parent_send_order > 0 && item.child_send_order === 1);
      },
      loginCircularUser() {
        if(!this.circular || !this.circular.users || !this.userHashInfo) {
          return null;
        }
        return this.circular.users.slice().reverse().find(item => item.email === this.userHashInfo.email);
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
                    let arrUsers = this.$store.state.home.circular ? this.$store.state.home.circular.users : [];
                    for(let i = 1;i < arrUsers.length;i ++){
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
          if(!this.userHashInfo) return null;
          return this.userHashInfo.max_attachment_size;
      },
      //PAC_5-1783 メールリンクから文書を表示させたとき、プレビューが表示されない
      userHashId(){
          if(!this.userHashInfo) return null;
          return this.userHashInfo.id;
      },
        currentEnvFlg(){
            if(!this.userHashInfo) return null;
            return this.userHashInfo.current_env_flg;
        },
        currentServerFlg(){
            if(!this.userHashInfo) return null;
            return this.userHashInfo.current_server_flg;
        },
        circularStatus(){
          return this.$store.state.home.circular ? this.$store.state.home.circular.circular_status : null;
        },
        loginUserCircularStatus(){
          return this.loginCircularUser ? this.loginCircularUser.circular_status : 0;
        },
        showLongTermSave(){
            return this.canStoreCircular && this.$store.state.home.circular && (this.$store.state.home.circular.circular_status == this.CIRCULAR.CIRCULAR_COMPLETED_STATUS ||
                    this.$store.state.home.circular.circular_status == this.CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS);
        },
        showLongTermSaveIndex(){
          return this.userHashInfo && this.userHashInfo.long_term_storage_option_flg;
        },
        companyLimit(){
          return  { text_append_flg:this.circular ? this.circular.limit_text_append_flg : 0 }
        },
        hasPlanCircularUsers:{
            get() {
                if(!this.$store.state.home.circular || this.$store.state.home.circular.plans.length<=0) return [];
                let plan=JSON.parse(JSON.stringify(this.$store.state.home.circular.plans))
                let circularUsers=cloneDeep(this.circularUsers)
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
                      }else{
                          user.plan_mode=plan[user.plan_id].mode
                          user.plan_score=plan[user.plan_id].score
                          user.plan_users=plan[user.plan_id].users
                          plan[user.plan_id].is_add=true
                          return user
                      }
                  }else{
                      return user
                  }
                }).filter(user=>{
                    return user!=null
                })
                // 最終承認者を追加
                if (circularUsers && circularUsers.length > 0) {
                    const circularReturnUsers = [];
                    const return_users = [];
                    for (let index in circularUsers) {
                        const item = cloneDeep(circularUsers[index]);
                        if (!item || item.id === undefined ) continue;
                        item.is_flag = 0;
                        item.is_return = 0;
                        item.has_return = 0;
                        item.is_send_back = 0;
                        item.is_return_send_back = 0;
                        circularReturnUsers.push(item);
                        if (return_users.length > 0 && ((!circularUsers[parseInt(index) + 1] && item.parent_send_order > 0) || (circularUsers[parseInt(index) + 1] && circularUsers[parseInt(index) + 1].parent_send_order !== item.parent_send_order))) {
                            circularReturnUsers.push(...return_users);
                            return_users.length = 0;
                        }
                        if (this.currentEnvFlg == item.env_flg && this.currentServerFlg == item.server_flg && this.userHashInfo.mst_company_id === item.mst_company_id && item.return_flg === 1 && ((item.child_send_order === 0 && item.parent_send_order === 0) || (item.child_send_order === 1 && item.parent_send_order > 0))) {
                            if (!circularUsers[parseInt(index) + 1] || circularUsers[parseInt(index) + 1].parent_send_order !== item.parent_send_order) continue;
                            const return_user = cloneDeep(item);
                            return_user.is_return = 1;
                            item.has_return = 1;
                            if (return_user.return_send_back && return_user.return_send_back === 1 && (return_user.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || return_user.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || return_user.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || return_user.circular_status === CIRCULAR_USER.READ_STATUS || return_user.circular_status === CIRCULAR_USER.NODE_COMPLETED_STATUS)) {
                                return_user.is_return_send_back = 1;
                            }
                            return_users.push(return_user);
                        }
                    }
                    return circularReturnUsers.filter(item => {
                        let last_send_id = this.circularUserLastSend ? this.circularUserLastSend.id : this.circularUserLastSendId;
                        last_send_id = last_send_id === null || last_send_id === undefined ? -1 : last_send_id;

                        if (item.plan_users && item.plan_users.length > 0) {
                            let send_back_user = item.plan_users.filter(user=>{return user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS});
                            if ((send_back_user.length >0 && item.is_return === 0) || item.is_return_send_back === 1) {
                                item.is_send_back = 1;
                            }
                            let flag_user = item.plan_users.filter(user=>{return user.id === last_send_id && ((item.has_return === 0 && item.is_return === 0) || (user.circular_status === CIRCULAR_USER.REVIEWING_STATUS && item.is_return === 1) || ((user.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || user.circular_status === CIRCULAR_USER.READ_STATUS) && item.is_return === 0)) });
                            if (flag_user.length > 0 ) {
                                item.is_flag = 1;
                            }
                            item.plan_users.filter(user=>{
                                user.is_send_back = 0;
                                user.is_flag = 0;
                                if (item.id === user.id) {
                                    if (item.is_send_back === 1) user.is_send_back = 1;
                                    if (item.is_flag === 1) user.is_flag = 1;
                                }
                            });
                        } else {
                            if ((item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS && item.is_return === 0) || item.is_return_send_back === 1) {
                                item.is_send_back = 1;
                            }
                            if (last_send_id === item.id && ((item.has_return === 0 && item.is_return === 0) || (item.circular_status === CIRCULAR_USER.REVIEWING_STATUS && item.is_return === 1) || ((item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS) && item.is_return === 0))) {
                                item.is_flag = 1;
                            }
                        }
                        return item.id
                    });
                } else {
                    return []
                }
            },
        },

        currentCloudDrive: {
          get() {
            return this.$store.state.cloud.drive
          },
          set(value) {
            this.$store.commit('cloud/setDrive', value);
          }
        },
        printedStampCount(){
            let printedStampCount=0
            this.$store.state.home.files.forEach(file=>{
                file.pages.forEach(page => {
                    printedStampCount += page.stamps.length
                })
            })
            return printedStampCount
        },
        /*PAC_5-2288 S*/
        showDownloadBtn(){
            let printedStampCount=0
            let printedTextsCount=0
            if (this.circular.circular_status == CIRCULAR.CIRCULAR_COMPLETED_STATUS || this.circular.circular_status == CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS ){
                return true
            }
            this.$store.state.home.files.forEach(file=>{
                file.pages.forEach(page=>{
                    printedTextsCount+=page.texts.filter(_=>_.text.length>0).length
                    printedStampCount+=page.stamps.filter(_=>!_.selected).length
                })
            })
            if (this.circular.esigned_flg==1 && (printedTextsCount>0 || printedStampCount>0)){
                return false
            }
            return true
        },
        /*PAC_5-2288 E*/
        showCircularHasReturnUsers () {
          return this.circularHasReturnUsers.filter(user => (!this.specialCircularFlg || (this.specialCircularReceiveFlg ||  !user.special_site_receive_flg)))
        },
        commentsNotPrivate () {
          return this.comments.filter(comment => comment.private_flg === 0 && comment.text && comment.text.trim())
        },
        commentsIsPrivate () {
          return this.comments.filter(comment => comment.private_flg === 1 && comment.text && comment.text.trim())
        },
        commentsFilter() {
          return this.comments.filter(comment => comment.text && comment.text.trim())
        },
        isShowButton() {
          return this.title.trim().length>0
      }
    },
    methods: {
      ...mapActions({
        uploadFile: "home/uploadFile",
        acceptUpload: "home/acceptUpload",
        rejectUpload: "home/rejectUpload",
        getPage: "home/getPage",
        clearState: "home/clearState",
        selectFile: "home/selectFile",
        addEmptyFile: "home/addEmptyFile",
        closeFile: "home/closeFile",
        updateCurrentFileZoom: "home/updateCurrentFileZoom",
        getStampsByHash: "home/getStampsByHash",
        selectStamp: "home/selectStamp",
        selectText: "home/selectText",
        undoAction: "home/undoAction",
        saveFile: "home/saveFile",
        downloadFile: "home/downloadFile",
        downloadSendFile: "home/downloadSendFile",
        updateStampDisplays: "home/updateStampDisplays",
        editFileAndSignature: "home/editFileAndSignature",
        saveFileAndSignature: "home/saveFileAndSignature",
        deleteCircularDocument: "home/deleteCircularDocument",
        setFirstPageImage: "home/setFirstPageImage",
        changePositionFile: "home/changePositionFile",
        loadCircularByHash: "home/loadCircularByHash",
        getStampInfos: "home/getStampInfos",
        getBizcardByIdPublic: "bizcard/getBizcardByIdPublic",
        getPublicStamps: "home/getPublicStamps",
        addFileStamp: "home/addFileStamp",
        afterCheckAccessCode: "home/afterCheckAccessCodeByHash",
        updateCircularUserStatus: "application/updateCircularUser",
        sendMailViewed: "application/sendMailViewed",
        checkAccessCode: "circulars/checkAccessCode",
        getInfoByHash: "user/getInfoByHash",
        addLogOperation: "logOperation/addLog",
        getLimit: "setting/getLimit",
        approvalRequestSendBack: "home/approvalRequestSendBack",
        discardCircular: "home/discardCircular",
        sendNotifyContinue: "application/sendNotifyContinue",
        renameCircularDocument: "home/renameCircularDocument",
        checkShowConfirmAddTimeStamp: "home/checkShowConfirmAddTimeStamp",
        checkOutsideAccessCodeByHash: "user/checkOutsideAccessCodeByHash",
        updateFileComment: "home/updateFileComment",
        deleteFileComment: "home/deleteFileComment",
        storeCircular: "circulars/storeCircular",
        checkDeviceType: "home/checkDeviceType",
        getAttachment: "home/getAttachment",
        downloadAttachment:"home/downloadAttachment",
        attachmentUpload: "home/attachmentUpload",
        deleteAttachment: "home/deleteAttachment",
        attachmentConfidentialFlg:'home/attachmentConfidentialFlg',
        setApprovalLongtermIndex: "circulars/setApprovalLongtermIndex",
        // PAC_5-1488 クラウドストレージを追加する Start
        getCloudItems: "cloud/getItems",
        downloadCloudAttachment:"cloud/downloadCloudAttachment",
        downloadCloudItem: "cloud/downloadItem",
        uploadToCloud: "home/uploadToCloud",
        // PAC_5-1488 End
        // PAC_5-2242 Start
        setUploadFileInfoList: "pageBreaks/setUploadFileInfoList",
        setCircularDocIdBeforeMod: "pageBreaks/setCircularDocIdBeforeMod",
        setCircularDocIdAfterMod: "pageBreaks/setCircularDocIdAfterMod",
        // PAC_5-2242 End
        getMyFolders: "circulars/getMyFolders",
        selectSticky: "home/selectSticky",
      }),
        // 付箋を編集
        editStickyNoteParent (edit_id) {
          this.$refs.pages.editStickyNote(edit_id)
        },
        //PAC_1398 添付ファイルを削除
        onDeleteAttachment: async function(index){
            this.attachmentUploads.splice(index,1);
            await this.deleteAttachment(this.attachmentAfterUploads[index].circular_attachment_id ? this.attachmentAfterUploads[index].circular_attachment_id : this.attachmentAfterUploads[index].id);
            this.attachmentAfterUploads.splice(index,1);
        },
        //PAC_1398 添付ファイルをローカルからアップロードします。
        onUploadAttachment: async function(e) {
            const files = Array.from(e.target.files);
            let isUpload = false;
            files.forEach(file =>{
                file.file_max_attachment_size = this.userHashInfo.max_attachment_size;
                if (file.size > this.userHashInfo.max_attachment_size * 1024 * 1024) {
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
                    let item = {file_name:file.name,success:false,loading:true,confidential_flg:false,create_user_id:this.userHashInfo.id,env_flg: this.userHashInfo.current_env_flg,server_flg: this.userHashInfo.current_server_flg };
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
        //PAC_1398 「社外秘に設定」の状態を修正します。
        onAttachmentConfidentialFlgClick: async function(index){
            let data = {
                circular_attachment_id : this.attachmentAfterUploads[index].circular_attachment_id ? this.attachmentAfterUploads[index].circular_attachment_id : this.attachmentAfterUploads[index].id,
                confidentialFlg : this.attachmentAfterUploads[index].circular_attachment_id ? (!this.attachmentUploads[index].confidentialFlg ? 1 : 0) : (!this.attachmentUploads[index].confidential_flg ? 1 : 0),
            };
            await this.attachmentConfidentialFlg(data);
        },
        //PAC_1398 添付ファイルの情報をすべて取得します。
        addAttachment: async function () {
            this.$modal.show('add-attachment-modal');
            if (!this.circular || !this.circular.users) {
                return null;
            }
            if (this.attachmentAfterUploads.length <= 0) {
                this.attachmentUploads = [];
                this.attachmentAfterUploads = [];
                let ret = await this.getAttachment(this.circular.id);
                ret.forEach((item,value)=>{
                    this.attachmentUploads.push(item);
                    this.attachmentAfterUploads.push(item);
                });
            }
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
              await this.downloadAttachment(this.attachmentAfterUploads[index].circular_attachment_id ? this.attachmentAfterUploads[index].circular_attachment_id : this.attachmentAfterUploads[index].id);
            },
            cancel: async ()=> {
              return null;
            },
          });
        },
        onOverFileSizeClick: async function(){
            await this.$modal.hide('over-file-size-modal');
            this.addAttachment();
        },
        closeAttachmentModal: function() {
            this.$modal.hide('add-attachment-modal');
        },
      onZoomOutClick: function () {
        this.zoom = parseInt(this.zoom);
        this.zoom = Math.max(50, this.zoom - 10);
      },
      onZoomInClick: function () {
        this.zoom = parseInt(this.zoom);
        this.zoom = Math.min(200, this.zoom + 10)
      },
      onClickStampHistory: async function(history) {
        // 名刺機能のON/OFFを取得
        var getBizcardFlgResult = await Axios.get(`${config.BASE_API_URL}/public/setting/getBizcardFlg`, {data: {usingHash: true}})
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
            let info = {
                bizcard_id: history.bizcard_id,
                env_flg: history.env_flg,
                server_flg: history.server_flg,
                edition_flg: history.edition_flg
            }
            let response = await this.getBizcardByIdPublic(info);
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
      onSaveLongtermIndex: async function() {
        let data = {
          usingHash: true,
          cid: this.circular.id,
          indexes: {},
        };
        for (var key in this.longtermIndex) {
          data.indexes[this.longtermIndex[key].index_name] = this.longtermIndex[key].index_value;
        }
        await this.setApprovalLongtermIndex(data);
      },
      onAddFileClick: function () {
        if(this.userHashInfo && this.userHashInfo.is_external) return;

        if( this.file_is_open ) {
          this.addEmptyFile();
          this.oldTabSelected = this.tabSelected;
          this.commentsExist = false;
        } else {
          this.onFileTabClick(this.files[this.tabSelected], this.tabSelected);
        }

        this.file_is_open = !this.file_is_open;
      },
      changeSelectedFile(index){
          if(this.files[index].circular_document_id != this.fileSelected.circular_document_id){
              this.onFileTabClick(this.files[index], index);
          }
      },
      goToDestination: function () {
          if (this.circular.limit_require_print===1 && this.printedStampCount==0 && !this.userHashInfo.is_external){
              this.$vs.dialog({
                  type: 'alert',
                  color: 'danger pre-msg',
                  title: `メッセージ`,
                  acceptText: 'OK',
                  text: `捺印してください。`,
              });
              return false
          }
          if( this.isMobile ){
            let pageIndex = this.currentPageNo - 1;
            const editor = this.$refs.editorMobile.find(x => x.$el.dataset.index == pageIndex)
            editor.confirmStamp()
          }
          this.editFileAndSignature({stampDisplays: this.stampUsed});
          this.$router.push(`/site/destination/${this.$route.params.hash}`);
      },
        goToSendback: function () {
            // 差戻し
            this.editFileAndSignature({stampDisplays: this.stampUsed});
            this.$router.push(`/site/sendback/${this.$route.params.hash}`);
        },
        goToReDestination: function () {
            // 二重チェック追加
            this.clickState = true;
            this.$router.push(`/site/receive/${this.$route.params.hash}`);
            this.clickState = false;
        },
      onUploadFile: async  function (e) {
        if(this.userHashInfo && this.userHashInfo.is_external) return;
        this.uploadCompleted = false;
        this.$modal.show('upload-modal');
        const files = Array.from(e.target.files);
          files.forEach(file => {
              Object.defineProperty(file, 'max_document_size', {
                  value: '8MB',
                  writable: true
              });
              file.max_document_size = this.userHashInfo.max_document_size;
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
              // PAC_5-2242 Start
              item.isExcel = !!(ret.server_file_name_for_office_soft && [".xls", ".xlsx"]
              .some((excelExtension) =>
                  ret.server_file_name_for_office_soft.endsWith(excelExtension)
              ));
              // PAC_5-2242 End
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
        if(file.confidential_flg && file.mst_company_id !== this.userMstCompanyId) return;
        if(this.fileSelected && file.circular_document_id === this.fileSelected.circular_document_id) return;

        if(index >= this.maxTabShow) {
          this.changePositionFile({from: index, to: 0});
        }

        this.selectFile(file);
        // tabSelected は vs-navbar 等により変更される
      },
      onCloseDocumentClick: function(file, index) {
        if(this.userHashInfo && this.userHashInfo.is_external) return;
        this.$modal.show('delete-doc-modal');
        this.deleteItem = $.extend({}, file);
        this.deleteIndex = index;
        this.oldTabSelected = this.tabSelected;
      },
      // PAC_5-2242 Start
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
                this.$router.push({
                    path: `/site/page-breaks/${this.$route.params.hash}`,
                    query: { create_new: false },
                });
            },
            cancel: () => {
                this.tabSelected = this.oldTabSelected;
            }
        });
      },
      // PAC_5-2242 End
      acceptConfirmDelete: async function() {
        if(this.userHashInfo && this.userHashInfo.is_external) return;
        this.$modal.hide('delete-doc-modal');

        var isCurrentTab = (this.fileSelected && this.deleteItem.server_file_name === this.fileSelected.server_file_name);

        if (isCurrentTab) {
          this.tabSelected = this.deleteIndex + 1;
          if(this.deleteIndex < this.files.length - 1) {
            this.tabSelected = this.deleteIndex + 1;
          }else {
            this.tabSelected = this.deleteIndex > 0 ? this.deleteIndex - 1: 0;
          }
        }

        var ret = await this.deleteCircularDocument({circular_id: this.circular ? this.circular.id: null,file_path: this.deleteItem.server_file_path, circular_document_id: this.deleteItem.circular_document_id});
        this.closeFile(this.deleteItem);

        const action = {'save_detail': 'r04-delete'};
        if(action[this.$route.name]){
            this.addLogOperation({ action: action[this.$route.name], result: ret ? 0 : 1, params:{filename: this.deleteItem.name,circular_id: this.circular.id}});
        }
        this.deleteItem = null;
        this.deleteIndex = null;

        if(!isCurrentTab) {
          if(this.oldTabSelected === this.files.length) {
            this.oldTabSelected = this.oldTabSelected - 1;
          }
          this.tabSelected = this.oldTabSelected;
        }
      },
      cancelConfirmDelete: function() {
        this.tabSelected = this.oldTabSelected;
        this.$modal.hide('delete-doc-modal');
      },
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
        this.maxTabShow = Math.floor((width - 100) / 200);

        const viewerWidth = width - 40;
        if (this.deviceType.isTablet) {
          this.viewerWidth = viewerWidth;
        } else {
          this.viewerWidth = Math.max(820, viewerWidth);
          const num = document.body.clientWidth < 1200 ? 1 : 0;
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
            if (!this.userHashInfo.is_external){
                this.getStampsByHash({date: this.$moment(values[0]).format('YYYY-MM-DD')});
                await Axios.get(`${config.BASE_API_URL}/public/getStampsByHash?date=${this.$moment(values[0]).format('YYYY-MM-DD')}`, {data: {nowait: true,usingHash: true}})
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
                    let newStampId = this.stamps.filter(newStamp => newStamp.db_id === this.stampSelected.db_id)[0];
                    if (newStampId) {
                        await this.clickStamp(newStampId.id, true);
                    }
                }
                if(action[this.$route.name]){
                    this.addLogOperation({ action: action[this.$route.name], result: ret ? 0 : 1});
                }
            }
        },
      onStampToolbarActiveChange: function (value) {
        this.stampToolbarActive = value;
        const $this = this;
        setTimeout(function() {
          $this.calcPdfViewerWidth();
          $this.selectPage($this.currentPageNo);
          $this.noticePosition();
        },300);
      },
      handleFileSelect: async function(evt) {
        if(this.userHashInfo && this.userHashInfo.is_external) return;
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
            file.max_document_size = this.userHashInfo.max_document_size;
            let item = {name: file.name, success: false, loading: true};
            this.fileUploads.push(item);
            const ret = await this.uploadFile(file);
            item.loading = false;
            if(ret) {
              this.fileAfterUploads.push(ret);
              item.success = true;
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
      onGenerateStampClick: function (e) {
        if(!this.doPermission) return;
        if(this.userHashInfo && this.userHashInfo.is_external) {
          this.eCanvas = e;
          this.generateStampPopupActive = true;
        }
      },
      onChooseStamp: function(e) {
        if(!this.eCanvas) return;
        const scale = this.$store.state.home.fileSelected.zoom / 100;
        const stampWithoutUrl = {
          index: this.eCanvas.stampIndex,
          id: this.stampSelected.id,
          width: this.stampSelected.width,
          height: this.stampSelected.height,
          x: ((this.eCanvas.event.evt.layerX - (this.stampSelected.width * scale) / 2) / scale) / this.eCanvas.realScale,
          y: ((this.eCanvas.event.evt.layerY - (this.stampSelected.height * scale) / 2) / scale) / this.eCanvas.realScale,
          scaleX: 1,
          scaleY: 1,
          rotation: 0,
          selected: true,
        };
        this.addFileStamp({stamp: stampWithoutUrl, pageno: this.eCanvas.page.no});
        this.generateStampPopupActive = false;
        this.eCanvas.callback();
      },
      onSearchStammp: function(e) {
        if(!this.generateStampName) return;
        this.onSearchAfter = true;
          Axios.get(`${config.BASE_API_URL}/public/generateStamp?name=${encodeURIComponent(this.generateStampName)}&date=${this.$moment().format('YYYY-MM-DD')}`, {data: {usingHash: true}})
              .then(response => {
                  let id = this.stampUsed.length + 1;
                  let cid = 1;
                  let current_selected = 0;
                  this.stampDisplays = [];
                  response.data.data.forEach((item, index) => {
                      cid = index + 1;
                      if( this.stampSelected && this.stampSelected.cid == cid ) current_selected = id + index;
                      const stamp = {
                          id: id + index,
                          db_id: item.id,
                          sid: item.sid,
                          cid: cid,
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
                      if(this.isMobile) {
                        current_selected = current_selected ? current_selected : 1;
                        this.clickStamp( current_selected, 'trigger' );
                      }
                  });
              })
              .catch(error => { return []; });
      },
      onSubmitAccessCode: async function() {
        this.showAccessCodeMessage = false;
        const ret = await this.checkAccessCode({id: this.$store.state.home.tmpData.circular.id, access_code:this.access_code, current_user_identity:this.$store.state.home.currentUserIdentity, finishedDate: this.finishedDate});
        this.isValidAccessCode = ret;
        if(ret) {
          this.$modal.hide('access-modal', {close: true});
          this.afterCheckAccessCode(this.userHashInfo);

          //PAC_5-1348 アクセスコード使用時のファイル読み込みタイミングでcheckEnableAdd取得
          if(this.$store.state.home.circular){
            if(this.checkCircularStatus() === true){
              this.$store.commit("home/homeClearState", null, { root: true });
              this.isValidAccessCode = false;
              this.$modal.show('pullbackshow-modal');
              return ;
            }
            let createCircularCompany  = await Axios.get(`${config.BASE_API_URL}/public/setting/getCreateCircularCompany?circular_id=${this.circular.id}&finishedDate=${this.finishedDate}`,{data: {usingHash: true}})
                .then(response => {
                  return response.data ? response.data.data: [];
                })
                .catch(error => { return []; });
            this.isShowAttachment = createCircularCompany.attachment_flg == 1;

            this.enableAdd = this.$store.state.home.circular.checkEnableAdd;
            // アクセスコード入力要の場合
            this.specialCircularFlg = this.circular.special_site_flg;
            this.groupName = this.circular.special_site_group_name;
            // 特設サイト
            if(this.specialCircularFlg){
              if (this.files.length < 1) {
                this.$modal.show('special-site-modal');
              }
              this.circular.users.forEach(item => {
                if(item.email == this.loginCircularUser.email && item.mst_company_id == this.loginCircularUser.mst_company_id && item.special_site_receive_flg == 1){
                  this.specialCircularReceiveFlg = true;
                }
                if(item.special_site_receive_flg == 1 && item.id == this.circularUserLastSend){
                  this.circularUserLastSendIdIsSpecial = true;
                }
              });
            }
          }

          // PAC_5-2242 Start
          const circularDocIdAfterMod = this.$store.state.pageBreaks.circularDocIdAfterMod;
          if (circularDocIdAfterMod !== null && this.files && this.files.length > 1) {
            // setTimeoutを500で呼んでいるので、500より大きい時間でタブを切り替える
            const modifiedFileIndex = this.files.findIndex(file => file.circular_document_id === circularDocIdAfterMod);
            if (modifiedFileIndex > 0) {
              const $this = this;
              setTimeout(() => {
                  $this.onFileTabClick($this.files[modifiedFileIndex], modifiedFileIndex);
              }, 1000);
            }
          } else {
            if(this.files.length > 0 && this.$store.state.home.fileSelected) {
              const fileIndex = this.$store.state.home.files.findIndex(file => file.server_file_name === this.$store.state.home.fileSelected.server_file_name);
              this.tabSelected = fileIndex;
              this.selectFile(this.files[fileIndex]);
            }
          }

          if(this.$store.state.home.title && (this.$store.state.home.title).trim() !== ""){
            this.docName = this.$store.state.home.title;
          }else{
              let docName = '';
              for(let i=0;i<this.files.length;i++){
                  if(i===0){
                      docName += this.files[i].name;
                  }else{
                      docName += ',' + this.files[i].name;
                  }
              }
              this.docName = docName;
          }

          // リロード時にタブが選択されないようにする
          this.setCircularDocIdAfterMod(null);
          // PAC_5-2242 End

          if(this.circularUserLastSend && this.circularUserLastSend.circular_status === this.CIRCULAR_USER.NOTIFIED_UNREAD_STATUS) {
            this.sendMailViewed({circular_user_id: this.circularUserLastSend.id, is_template_circular: this.isTemplateCircular});
          }
          this.specialCircularFlg = this.circular.special_site_flg;
          this.zoom = 100;
          this.validatHasAttachment(this.circular.id)
        }else {
          this.showAccessCodeMessage = true;
        }
        this.isShowTextFunction = (this.circular?this.circular.text_append_flg==1:false)&&(this.companyLimit?this.companyLimit.text_append_flg==1:false);
      },
      beforeClose: function (event) {
        if(!event.params || !event.params.close) {
          if( !this.isMobile )  event.stop();
        }
        this.file_is_open = true;
      },
      // PAC_5-2242 Start
      onChooseUpload:function (){
        if(this.changeFlg === 'pageChange'){
            this.onPageBreaksPreview();
        }else{
            this.onCompletedUpload();
        }
        this.file_is_open = true;
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
                const queryObj = { create_new: false };
                this.$router.push({
                    path: `/site/page-breaks/${this.$route.params.hash}`,
                    query: queryObj,
                });
            },
        });
      },
      // PAC_5-2242 End
      onCompletedUpload: async function() {
        this.isProcessing = true; // 処理中はボタンを非活性にし、押下できなくする
        if(this.userHashInfo && this.userHashInfo.is_external) {
          this.isProcessing = false; // ボタンを活性化
          return;
        }
        const ret = await this.acceptUpload(this.fileAfterUploads);
        if(!ret) {
          this.isProcessing = false; // ボタンを活性化
          return;
        }
        this.$modal.hide('upload-modal', {close: true});
        this.tabSelected = this.files.length - 1;

        if (this.tabSelected >= this.maxTabShow) {
          this.changePositionFile({from: this.tabSelected, to: 0});
          this.tabSelected = 0;
        }
        // PAC_5-2022 自動展開 Start
        if (document.body.clientWidth > 1200) {
          const $this = this;
          this.stampToolbarActive = true;
          setTimeout(function () {
            $this.calcPdfViewerWidth();
            $this.selectPage($this.currentPageNo);
            $this.noticePosition();
          }, 300);
        }
        // PAC_5-2022 End
        this.fileAfterUploads = [];
        this.fileUploads = [];
        this.isProcessing = false; // ボタンを活性化
      },
      onRejectUpload: async function() {
        if(this.userHashInfo && this.userHashInfo.is_external) return;
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
        if(this.userHashInfo && this.userHashInfo.is_external) return;
        $('#uploadFile').click();
      },
      addLogDownloadOperation: async function(result){
          const action = 'r9-14-download';
          // PAC_5-1027 ダウンロードの操作履歴が表示されない
          this.addLogOperation({ action: action, result: result ? 0 : 1, params:{filename: this.fileSelected.name}});
      },
      onDownloadFileClick: async function () {
          if(this.doPermission) {
              this.editFileAndSignature({stampDisplays: this.stampUsed});
              var ret = this.downloadFile();
              // PAC_5-1027 ダウンロードの操作履歴が表示されない
              this.addLogDownloadOperation(ret);
              this.$store.state.home.fileSelected.pages.forEach(page =>{
                page.stamps.forEach(stamp => {
                  stamp.repeated = true;
                });
              });
              this.disabledUndo = true; //PAC_5-1036 ダウンロード時元に戻すボタン無効化
          }else{
              var checkAddUsingTas =  await this.checkShowConfirmAddTimeStamp(this.finishedDate);
              this.$store.commit('home/checkAddUsingTas', checkAddUsingTas);
              if(checkAddUsingTas){
                  this.$vs.dialog({
                      type:'confirm',
                      color: 'primary',
                      title: `確認`,
                      acceptText: 'はい',
                      cancelText: 'いいえ',
                      text: `タイムスタンプを付与しますか？`,
                      accept: async ()=> {
                          var ret = await this.downloadSendFile(this.finishedDate);
                          // PAC_5-1027 ダウンロードの操作履歴が表示されない
                          this.addLogDownloadOperation(ret);
                      },
                      cancel: async ()=> {
                          await this.$store.commit('home/checkAddUsingTas', false);
                          var ret = await this.downloadSendFile(this.finishedDate);
                          // PAC_5-1027 ダウンロードの操作履歴が表示されない
                          this.addLogDownloadOperation(ret);
                      },
                  });
              }else{
                  var ret = await this.downloadSendFile(this.finishedDate);
                  // PAC_5-1027 ダウンロードの操作履歴が表示されない
                  this.addLogDownloadOperation(ret);
              }
          }
      },
       onConfidentialFlgLabelClick: async function() {
        await this.$forceNextTick();
        if(this.userHashInfo && this.userHashInfo.is_external) return;
        this.confidentialFlg = !this.confidentialFlg;
      },
      onApprovalRequest: async function () {
        //二重チェック
        this.clickState = true
        if(this.loginCircularUser) await this.approvalRequestSendBack({approvalUser: this.loginCircularUser});
        setTimeout(function(){
            if (window.opener){
                window.close();
            }else{
                window.location.href = config.LOCAL_API_URL;
            }
        }, 2000)
      },
      onDiscardCircularClick: async function() {
        const ret = await this.discardCircular();
        if(ret) {
          if (window.opener){
            window.close();
          }else{
            window.location.href = config.LOCAL_API_URL;
          }
        }
      },
      isFileCreatedByOwn(file) {
        if(!file) return false;
        if(!this.circular) return false;
        if(!this.circularUserLastSend || !this.circularUserLastSend.received_date) {
          return true;
        }
        let fileCreateDate = new Date(file.create_at.replace(' ', 'T')).getTime();
        let circularUserReceivedDate = new Date(this.circularUserLastSend.received_date.replace(' ', 'T')).getTime();
        return fileCreateDate > circularUserReceivedDate;
      },
      onCancelAccessModal() {
        const return_url = localStorage.getItem('return_url_original');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('expires_time');
        localStorage.removeItem('loggedInAdmin');
        localStorage.removeItem('branding');
        localStorage.removeItem('limit');
        window.location.href = return_url;
        this.$ls.set(`logout`, Date.now());
      },
      onCloseSpecialModal() {
        window.opener=null;
        window.open('','_self');
        window.close();
      },
      onCloseCurrentWindows(){
        window.location.href="about:blank";
        window.close();
      },
      goToMemo: async function () {
          localStorage.setItem('finishedDate', this.finishedDate);
          this.$router.push(`/site/memo/${this.$route.params.hash}`);
      },
      handleButtonShowStamp: function(){

        let stamp_id = this.stamp_cid_temp ? this.stamp_cid_temp : 1;

        $('.stamp-search-list .stamp-item').removeClass('selected');
        $(`.stamp-search-list .stamp-item.cid_${stamp_id}`).addClass('selected')

        this.generateStampPopupActiveMobile = true;
        this.eCanvas = null;
      },
      onGenerateStampClickMobile: function (e) {
          if(!this.doPermission) return;
          if(this.userHashInfo && this.userHashInfo.is_external && this.showEdit) {
              this.eCanvas = e;
              this.generateStampPopupActiveMobile = true;
          }
      },
      onChooseStampMobile(){

          // For Public External
          if ( this.userHashInfo && this.userHashInfo.is_external && this.showEdit && this.stamp_id_temp ) {
            this.clickStamp(this.stamp_id_temp);
            this.generateStampPopupActiveMobile = false;
            return false;
          }

          if( this.temp_stamp_id )  this.clickStamp( this.temp_stamp_id );

          const scale = this.$store.state.home.fileSelected.zoom / 100;
          let pagex = localStorage.getItem('pagex');
          let pagey = localStorage.getItem('pagey');

          let topDistance = document.getElementsByClassName('sp-navi')[0].offsetHeight + 60;
          let wx = ( ((pagex-15) / scale) / this.eCanvas.realScale ) - ( this.stampSelected.width / 2 );
          let wy = 0;
          let pdfShiftDistanceX = localStorage.getItem('pdfShiftDistanceX');
          let pdfShiftDistanceY = localStorage.getItem('pdfShiftDistanceY');
          pdfShiftDistanceX = pdfShiftDistanceX ? pdfShiftDistanceX : 0;
          pdfShiftDistanceY = pdfShiftDistanceY ? pdfShiftDistanceY : 0;

          if( scale+this.eCanvas.realScale > 2 )
            wy = ( ((pagey-topDistance+15) / scale) / this.eCanvas.realScale ) - this.stampSelected.height;
          else
            wy = ( ((pagey-topDistance) / scale) / this.eCanvas.realScale ) - this.stampSelected.height;

          if ( pdfShiftDistanceX ) wx = wx - ( (pdfShiftDistanceX / scale) / this.eCanvas.realScale );
          if ( pdfShiftDistanceY ) wy = wy - ( (pdfShiftDistanceY / scale) / this.eCanvas.realScale );

          const stampWithoutUrl = {
              index: this.eCanvas.stampIndex,
              id: this.stampSelected.id,
              width: this.stampSelected.width,
              height: this.stampSelected.height,
              x: wx,
              y: wy,
              scaleX: 1,
              scaleY: 1,
              rotation: 0,
              selected: true,
              clientY: pagey,
          };
          this.addFileStamp({stamp: stampWithoutUrl, pageno: this.eCanvas.page.no});
          this.generateStampPopupActiveMobile = false;
          this.eCanvas.callback();
      },
      cancelChooseStampMobile(){
          this.generateStampPopupActiveMobile = false;
      },
      changeTabMail(){
          if($("#mail-list-label").hasClass("tab-active")) {

          }else{
              $("#comments-label, #history-label").removeClass("tab-active");
              $("#comments-label, #history-label").addClass("tab");

              $(".comments, .histories").hide();

              $("#mail-list-label").addClass("tab-active");
              $("#mail-list-label").removeClass("tab");
              $(".mail-list").show();
          }
      },
      changeTabComments(){
          if($("#comments-label").hasClass("tab-active")) {

          }else{
              $("#mail-list-label, #history-label").removeClass("tab-active");
              $("#mail-list-label, #history-label").addClass("tab");

              $(".mail-list, .histories").hide();

              $("#comments-label").addClass("tab-active");
              $("#comments-label").removeClass("tab");
              $(".comments").show();
          }
      },
      changeTabHistory(){
          if($("#history-label").hasClass("tab-active")) {

          }else{
              $("#mail-list-label, #comments-label").removeClass("tab-active");
              $("#mail-list-label, #comments-label").addClass("tab");

              $(".mail-list, .comments").hide();

              $("#history-label").addClass("tab-active");
              $("#history-label").removeClass("tab");
              $(".histories").show();
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
          if (this.doPermission){
              this.showEdit = true;
          }
      },
      showStamps(){
          this.$modal.show('stamps-modal');
      },
      async updateRotateAngle(){
          if(this.userHashInfo.current_edition_flg == config.APP_EDITION_FLV) {
                await Axios.post(`${config.BASE_API_URL}/public/default-stamp`, {default_rotate_angle: this.rotateAngle,default_opacity:this.opacity , usingHash: true, nowait:true })
                    .then(response => {
                        return response.data ? response.data.data: [];
                    })
                    .catch(error => { return []; });
          }
      },
      async clickStamp(stampId, event=null){
          var selStamp = this.stampDisplays.find(item => item.id === stampId);
          this.selectStamp(selStamp);
          if(this.userHashInfo.current_edition_flg == config.APP_EDITION_FLV) {
              await Axios.post(`${config.BASE_API_URL}/public/default-stamp`, {stamp_id: stampId , usingHash: true, nowait:true })
                  .then(response => {
                      return response.data ? response.data.data: [];
                  })
                  .catch(error => { return []; });
          }
          Utils.setCookie(`lastStampSelectedId_${this.userHashInfo.email}_${this.userHashInfo.current_env_flg}_${this.userHashInfo.current_server_flg}`, stampId, 10);

          if( !event && this.isMobile ) this.$modal.hide('stamps-modal', {close: true});

      },
      showDialog: function() {
          this.showInput = false;
      },
      hideDialog: function() {
          this.showInput = true;
      },

      // ファイル名ダブルクリック
      renameFileNameClick(filename) {
        // ファイル名変更:申請者だけ
        if(this.userHashInfo && this.userHashInfo.id == this.$store.state.home.fileSelected.create_user_id && this.userHashInfo.mst_company_id == this.$store.state.home.fileSelected.mst_company_id){
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
        if(this.userHashInfo && this.userHashInfo.is_external) return;
        this.$modal.hide('rename-file-modal');
        if(this.file_name == this.old_file_name){
          // ファイル名変更の場合、API呼出
          return;
        }
        var ret = await this.renameCircularDocument({circular_id: this.circular ? this.circular.id: null, circular_document_id: this.fileSelected.circular_document_id, file_name: this.file_name + '.pdf'});
        const action = {'save_detail': 'r04-delete'};
        if(action[this.$route.name]){
          this.addLogOperation({ action: action[this.$route.name], result: ret ? 0 : 1, params:{filename: this.file_name + '.pdf', circular_document_id: this.fileSelected.circular_document_id}});
        }
        this.fileSelected.name = this.file_name + '.pdf';
      },
      AddStampsConfirmation: function(currentPageNo) {
          setTimeout(()=>{
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
        if (e.target.tagName === 'INPUT' || this.countAllTabNum == true) return
        this.addStampHistory = false;
        this.addTextHistory = false;
        this.radioVal = "default"
      },
      changeStampHistory(e) {
          // Because the native click event will be executed twice, the first time on the label tag
          // and the second time on the input tag, this processing is required
          if (e.target.tagName === 'INPUT' || this.addStampHistory == true || this.countAllTabNum == true){return}
          this.countAllTabNum = true;
          this.$vs.dialog({
              type: 'confirm',
              color: 'primary',
              title: `確認`,
              acceptText: 'はい',
              cancelText: 'いいえ',
              text: `電子署名が付与されている場合、回覧履歴を付けてダウンロードをすると回覧時の署名が無効になります。`,
              accept: async () => {
                  this.addStampHistory = true;
                  this.addTextHistory = false;
                  this.radioVal = "addStampHistory";
                  this.countAllTabNum = false;
              },
              cancel: async () => {
                  this.addStampHistory = false;
                  if(this.addTextHistory == true){
                      this.radioVal = "addTextHistory";
                  }else{
                      this.radioVal = "default"
                  }
                  this.countAllTabNum = false;
              },
          });
      },
      changeTextHistory(e){
        // Because the native click event will be executed twice, the first time on the label tag
        // and the second time on the input tag, this processing is required
        if (e.target.tagName === 'INPUT' || this.addTextHistory == true || this.countAllTabNum == true) return
        this.addTextHistory = true;
        this.addStampHistory = false;
        this.radioVal = "addTextHistory";
      },
      // 社内社外宛先追加
      addComment: function() {
        if (!this.documentComment) return ;
        this.updateFileComment({private_flg: this.tab_comment_info, text: this.documentComment, parent_send_order: this.circularUserLastSend.parent_send_order? this.circularUserLastSend.parent_send_order:0});
        this.documentComment = '';
      },
      // 社内社外宛先削除
      removeComment: function(){
        this.deleteFileComment({private_flg: this.tab_comment_info});
      },
      onSaveLongtermModal: function() {
        this.showTree = false;
        this.checkKeywordsLenFlg = false;
        this.folderSelect = false;
        Axios.get(`${config.BASE_API_URL}/public/long-term/${this.circular.id}`, {data: {usingHash: true}})
            .then(response => {
                if (response.data.item){
                    this.keywords = response.data.item.keyword;
                }else{
                    this.keywords = '';
                    if(this.showFolderFlg){
                        this.showTree = true;
                    }
                }
                this.activeSaveLongtermModal = true;
            })
            .catch(error => {
                this.activeSaveLongtermModal = true;
            });
      },
      onSaveLongTermAccept: async function() {
        if(this.showTree && this.folderId == '') {
          this.activeSaveLongtermModal = true;
          this.folderSelect = true;
        }else {
          if(this.keywords.length > 200){
            this.checkKeywordsLenFlg = true;
            this.activeSaveLongtermModal = true;
          }else {
            if (this.circular){
              // PAC_5-2070対応
              if(this.keywords == ''){
                // PAC_5-2070対応
                this.keywords_flg = 0;
                await this.storeCircular({id: this.circular.id, keyword: this.keywords, finishedDate: this.finishedDate, keyword_flg: this.keywords_flg, folderId: this.folderId}).then(() => {
                  this.$router.back();
                  this.activeSaveLongtermModal = false;
                });
              }else{
                await this.storeCircular({id: this.circular.id, keyword: this.keywords, finishedDate: this.finishedDate, folderId: this.folderId}).then(() => {
                  this.$router.back();
                  this.activeSaveLongtermModal = false;
                });
              }
            }
          }
        }
      },
      handleResize: function() {
          this.window.width = window.innerWidth;
          this.window.height = window.innerHeight;

          this.calcPdfViewerWidth();
          this.selectPage(this.currentPageNo);
          this.noticePosition();
          //PAC_5-2300 S
          var element = document.getElementsByClassName("con-slot-tabs");
          for (var i = 0; i < element.length; i++){
              element[i].style.overflow = "auto";
              element[i].style.height = "94%";
          }
          //PAC_5-2300 E
      },
      //PAC_5-1053 コメントの存在を気づかせるようにしたい
      checkCommentsExist: function(fileSelected){
        if(fileSelected.comments.length && !this.commentsChecked.includes(fileSelected.circular_document_id)){
          this.commentsExist = true;
          this.noticePosition();
        }else{
          this.commentsExist = false;
        }
      },
      noticePosition: function(){
        if(!this.commentsExist) {
          return ; //コメントが存在しなければ終了
        }

        let tab = document.querySelector(".cirInfo > div.con-ul-tabs > ul > li:nth-child(1)") //印鑑タブ要素取得
        let tab2 = document.querySelector(".cirInfo > div.con-ul-tabs > ul > li:nth-child(2)") //回覧先タブ要素取得
        let tab3 = document.querySelector(".cirInfo > div.con-ul-tabs > ul > li:nth-child(3)") //コメントタブ要素取得
        let tabClientRect = tab.getBoundingClientRect();
        let tab2ClientRect = tab2.getBoundingClientRect();
        let tab3ClientRect = tab3.getBoundingClientRect();
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
      //PAC_5-2142 モバイルとPCでメッセージを切替
      async  validatHasAttachment(circular_id){
          let ret = await this.getAttachment(circular_id);
          if(ret.length > 0){
            const isMobile = navigator.userAgent.match(/phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/);
            const diagText = isMobile ? "添付されたファイルを確認される際はPC版でご確認ください": "右上の「添付ファイル」をクリックして文書をダウンロードしてご確認ください"
            this.$vs.dialog({
              type: 'alert',
              color: 'danger pre-msg',
              title: `メッセージ`,
              acceptText: 'OK',
              text: `文書が添付されています。
                  ${diagText}`,
            });
          }

      },
          //PAC_5-2142 モバイルとPCでメッセージを切替　END
      changConvertFlg1(){
        this.changeFlg = 'default';
      },
      // PAC_5-2242 Start
      changConvertFlg2(){
        this.changeFlg = 'pageChange';
      },
      // PAC_5-2242 End
      changConvertFlg3(){
        this.changeFlg = 'fontChange';
      },
      loadLastStampSelected() {
        let lastStampId = Utils.getCookie(`lastStampSelectedId_${this.userHashInfo.email}_${this.userHashInfo.current_env_flg}_${this.userHashInfo.current_server_flg}`);
        if (this.userHashInfo && this.userHashInfo.last_stamp_id) {
          var selStamp = this.stampDisplays.find(item => item.id == this.userHashInfo.last_stamp_id);
          if (selStamp == undefined && this.stamps && this.stamps.length > 0) selStamp = this.stampDisplays.find(item => item.id === this.stamps[0].id);
          this.selectStamp(selStamp);
        } else if (lastStampId) {
          var selStamp = this.stampDisplays.find(item => item.id == lastStampId);
          if (selStamp == undefined && this.stamps && this.stamps.length > 0) selStamp = this.stampDisplays.find(item => item.id === this.stamps[0].id);
          this.selectStamp(selStamp);
        } else if (this.stamps && this.stamps.length > 0) {
          var selStamp = this.stampDisplays.find(item => item.id == this.stamps[0].id);
          this.selectStamp(selStamp);
        }
      },

      // PAC_5-1488 クラウドストレージを追加する Start
      onUploadFromCloud: async  function (fileId, filename) {
        let file_data = {
          file_id: encodeURIComponent(fileId),
          filename: encodeURIComponent(filename),
          file_max_document_size: this.userHashInfo.max_document_size
        };
        this.$vs.loading({
          container: '#itemsCloudToUpload',
          scale: 0.6
        });
        this.onUploadFromCloudModalClosed();//PAC_5-3116
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
      onGetBoxItemsDone: function(ret) {
        if(ret.statusCode === 401){
          this.onCloudModalClosed();//PAC_5-3116
          this.$ls.remove('boxAccessToken');
          window.open(`${config.LOCAL_API_URL}/uploadExternal?drive=` + this.currentCloudDrive, '_blank');
        }
        if(ret.statusCode === 200 && ret.data) {
          this.cloudFileItems = ret.data.item_collection.entries.filter(item => {
            return item.type === 'folder' || (item.type === 'file' && item.name.includes('.pdf'))
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
          this.onUploadFromCloudModalClosed();//PAC_5-3116
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
        /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
        this.$store.commit('home/updateCloudBoxFlg',true);
        /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
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
      onCloudModalOpened: async function() {
        /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
        this.$store.commit('home/updateCloudBoxFlg',true);
        /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        const ret = await this.getCloudItems(0);
        this.onGetBoxItemsDone(ret);

      },
      onCloudItemClick: async function(item) {
        if(item.type !== 'folder') return;
        const ret = await this.getCloudItems(item.id);
        this.onGetBoxItemsDone(ret);
      },
      onBreadcrumbItemClick: async function(folder_id) {
        const ret = await this.getCloudItems(folder_id);
        this.onGetBoxItemsDone(ret);
      },
      cancelConfirmUpdate: function() {
        this.$modal.hide('updatecheck-doc-modal');
      },
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
          saveFile: false,
          finishedDate: 0,
        }
        if(this.saveId){
          data["file_id"] = this.saveId;
        }
        //PAC_5-1216 END
        const uploadRet = await this.uploadToCloud(data);
        if(uploadRet) {
          this.onCloudModalClosed();//PAC_5-3116
          this.$store.state.home.fileSelected.pages.forEach(page =>{
            page.stamps.forEach(stamp => {
              stamp.repeated = true;
            });
          });
        }
      },
      addToFileUpload(fileId, filename) {
        this.fileid_selected_from_cloud = this.fileid_selected_from_cloud === fileId ? '' : fileId;
        this.filename_selected_from_cloud = this.filename_selected_from_cloud === filename ? '' : filename;
      },
      onUploadAttachmentFromCloud: async  function (fileId, filename) {
        let file_data = {
          file_id: encodeURIComponent(fileId),
          filename: encodeURIComponent(filename),
          file_max_attachment_size: this.userHashInfo.max_attachment_size,
          circular_id: this.circular ? this.circular.id: null,
        };
        this.$vs.loading({
          container: '#itemsCloudToUpload',
          scale: 0.6
        });
        this.onUploadFromCloudModalClosed();//PAC_5-3116
        this.$modal.show('add-attachment-modal');
        const iterable = async () => {
          let item = {file_name: filename, success: false, loading: true,confidential_flg:false,create_user_id:this.userHashInfo.id,env_flg: this.userHashInfo.current_env_flg,server_flg: this.userHashInfo.current_server_flg};
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
      setFolderId(id){
        this.folderId = id;
      },
      // PAC_5-1488 End

      customChooseStamp: function(e, stamp_id, stamp_cid) {
        this.stamp_id_temp = stamp_id;
        this.stamp_cid_temp = stamp_cid;

        $( e.target ).parents('.stamp-search-list').find('.stamp-item').removeClass('selected');
        $( e.target ).parent().addClass('selected');
      },
      handleDialogStart: function(e) {
        if( e.changedTouches.length != 1 ) return false;
        this.dialog_y = e.changedTouches[0].pageY;
        $(`<div id="bg_custom"></div>`).appendTo('body');
      },
      handleDialogMove: function(e){
        if(!this.dialog_y || e.changedTouches[0].pageY == this.dialog_y || $(e.target).parents('.btn_dialog_status').length == 1) return false;
        $('body').addClass('disabledScroll');
          e.preventDefault();
      },
      handleDialogEnd: function(e) {
        $('body').removeClass('disabledScroll');
        setTimeout( function() {
          $('#bg_custom').remove();
        }, 500);
        if(
            !this.dialog_y || e.changedTouches[0].pageY == this.dialog_y ||
            $(e.target).parents('.btn_dialog_status').length == 1 ||
            $('#main-home-mobile.mobile .btn_dialog.edit').hasClass('nofull')
          ) return false;

        if( e.changedTouches[0].pageY > this.dialog_y ) {
          $('#main-home-mobile.mobile .btn_dialog.edit').removeClass('full');
        } else {
          $('#main-home-mobile.mobile .btn_dialog.edit').addClass('full');
        }

        this.dialog_y = 0;
        e.preventDefault();
      },
      selectTextCustom: function( currentPageNo ) {
        var pageIndex = currentPageNo - 1;
        const editor = this.$refs.editorMobile.find(x => x.$el.dataset.index == pageIndex)
        editor.openTextOption( true );
      },
      handleMailList() {
        $('#handleMailList .icon').stop().toggleClass('hide');
        $('#mail_list_box').stop().toggle(300);
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
      handleClickDialogStatus: function(){
        if( this.isTextSelected ) {
          this.loadLastStampSelected();
          this.$store.state.home.textSelected = false;

        } else {
         this.selectText();
        }
      },
      /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
      onCloudModalClosed:function(){
        this.$modal.hide('cloud-upload-modal');
        this.$store.commit('home/updateCloudBoxFlg',false);
      },
      onUploadFromCloudModalClosed:function(){
        this.$modal.hide('upload-from-external-modal');
        this.$store.commit('home/updateCloudBoxFlg',false);
      },
      /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/

      checkCircularStatus: function () {
        if (!this.circular || !this.circularUsers) {
          return;
        }
        let currentIsBack = false;
        let circularFind = null;
        let currentReadAndNoRead = this.circularUsers.find(item => (item.circular_status == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status == CIRCULAR_USER.READ_STATUS) && item.email == this.userHashInfo.email);
        // 差戻　（差戻直後のみこの状態。差戻後に再度承認を行うと回覧中に戻る。）
        if (this.circular.circular_status == CIRCULAR.SEND_BACK_STATUS) {
          if (!currentReadAndNoRead) {
            currentIsBack = true;
          }
        }
        if (this.circular.circular_status == CIRCULAR.CIRCULATING_STATUS) {
          circularFind = this.circularUsers.filter(item => (item.circular_status == CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status == CIRCULAR_USER.SUBMIT_REQUEST_SEND_BACK));
          if (circularFind.length > 0  && !currentReadAndNoRead) {
            currentIsBack = true;
          }
        }
        if (this.circular.circular_status == CIRCULAR.RETRACTION_STATUS) {
          circularFind = this.circularUsers.filter(item => { return (item.parent_send_order == 0 && item.child_send_order == 0)});
          if (circularFind.length > 0  && circularFind[0].email != this.userHashInfo.email) {
            currentIsBack = true;
          }
        }
        return currentIsBack;
      }
    },

    watch: {
      "zoom": function (newVal,oldVal) {
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
        if(newVal){
          if(this.$refs.pages){
            this.$refs.pages.showStickyNote();
          }
        }
      },
      "$store.state.home.hasAction": async function(newVal, oldVal) {
        if(!this.$store.state.home.fileSelected || !this.$store.state.home.fileSelected.actions) {
          this.disabledUndo = true;
          return;
        }
        this.disabledUndo = this.$store.state.home.fileSelected.actions.length <= 0;
      },
      "tabSelected": function (newIndex, oldIndex) {
        const newVal = this.files[newIndex];
        if(!newVal) return;
        if(newVal.confidential_flg && newVal.mst_company_id !== this.userMstCompanyId) {
          this.tabSelected = oldIndex;
        }
      },
       "$store.state.home.files":{
           handler () {
               if (this.loginCircularUser){
                   Utils.buildTabColorAndLogo(this.files, this.companyLogos, this.loginCircularUser.mst_company_id, this.loginCircularUser.edition_flg, this.loginCircularUser.env_flg);
               }
           },
           deep:true
       },
        "$store.state.home.circular.circular_status":function () {
            var self = this;
            // circular_status = 1 : 回覧中　（送信直後の状態）
            // 回覧中の場合のみ、承認、差し戻しのダイアログを表示させる
            if (this.$store.state.home.circular
                && typeof this.$store.state.home.circular.circular_status !== "undefined"
                && this.$store.state.home.circular.circular_status !== null
                && this.$store.state.home.circular.circular_status == this.CIRCULAR.CIRCULATING_STATUS){
              this.dialogDispFlag = true;
            }

            if(this.$store.state.home.circular && (this.$store.state.home.circular.circular_status == this.CIRCULAR.CIRCULAR_COMPLETED_STATUS || this.$store.state.home.circular.circular_status == this.CIRCULAR.CIRCULAR_COMPLETED_SAVED_STATUS)){
                $('.vs-tabs--li').each(function(e){
                  if($($('.vs-tabs--li')[e]).children().children().html() == "印鑑"){
                    var del_tab_width = $($('.vs-tabs--li')[e]).width();
                    $($('.vs-tabs--li')[e]).remove();
                    if(self.tab_cir_info === 0){
                      self.tab_cir_info = 1;
                      $('.tab-parent>.con-ul-tabs>.line-vs-tabs').width($('.tab-parent>.con-ul-tabs>.vs-tabs--ul>.activeChild').width());
                    }else{
                      $('.tab-parent>.con-ul-tabs>.line-vs-tabs').css('left',$('.tab-parent>.con-ul-tabs>.line-vs-tabs').position().left-del_tab_width)+'px';
                      $('.tab-parent>.con-ul-tabs>.line-vs-tabs').width($('.tab-parent>.con-ul-tabs>.vs-tabs--ul>.activeChild').width());
                    }
                  }
                });
            }
        },
        /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
        "$store.state.home.cloudBoxFlg":function (value){
          if(value){
            document.body.style.setProperty('overflow-y','hidden','important');
          }else{
            document.body.style.setProperty('overflow-y','auto','important');
          }
        },
        /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        "$store.state.home.fileSelected.sticky_notes": async function (value) {
          if (!this.fileSelected) {
            return;
          }
          let showStickyNoteTmp = [];
          for (var i = 0; i < value.length; i++) {
            if (!showStickyNoteTmp[value[i].page_num]) {
              showStickyNoteTmp[value[i].page_num] = [];
            }
            let stickItem = value[i];
            stickItem.stickIndex = i;
            showStickyNoteTmp[value[i].page_num].push(stickItem);
          }
          this.showStickNotes = showStickyNoteTmp;
          if(this.$refs.pages){
            this.$refs.pages.showStickyNote();
          }
        }
    },
    async mounted() {
      document.body.style.overflow = 'hidden';
      this.stampUsed.push(...this.$store.state.home.stamps);
      this.handleResize();

      if(!this.$store.state.home.fileSelected || !this.$store.state.home.fileSelected.actions) {
        this.disabledUndo = true;
        return;
      }
      this.disabledUndo = this.$store.state.home.fileSelected.actions.length <= 0;
      /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
      this.$store.commit('home/updateCloudBoxFlg',false);
      /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/

    },
    async created() {
      // Check Mobile
      if (
        /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
          navigator.userAgent
        )
      ) {
        this.isMobile = true;
        this.showEdit = true;
      }

      if(
        /(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/.test( navigator.userAgent.toLowerCase() )
      )
      {
        this.isTablet = true;
      }


      this.checkDeviceType();
      this.date = new Date();
      const hash = this.$route.params.hash;
      this.$store.state.home.title = '';
      /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
      this.$store.commit('home/updateCloudBoxFlg',false);
      /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/

      this.startVisibilityWatch();

      const promises = [];
        if (hash) {
            localStorage.setItem('tokenPublic', hash);
            this.$store.commit('home/setUsingPublicHash', true);

            const backAndHasFiles = this.$route.query.back && this.files && this.files.length;
            if (!backAndHasFiles) {
                promises.push(this.clearState(null));
            }

            const userHashInfoPromise = this.getInfoByHash();
            promises.push(userHashInfoPromise);

            userHashInfoPromise.then((item) => {
                this.finishedDate = item.finishedDate;
                this.$store.state.user = item;
            })

            if (!backAndHasFiles) {
                const accessCodeHashFlgPromise = (async () => {
                    // 社外アクセスコード認証
                    const accessCodeHash = window.location.href.split("?").length > 1 ? window.location.href.split("?")[1] : '';
                    if (accessCodeHash) {
                        const result = await this.checkOutsideAccessCodeByHash({accessCodeHash: accessCodeHash});
                        return result.accessCodeAuth;
                    } else {
                        return false;
                    }
                })();
                promises.push(accessCodeHashFlgPromise);

                const loadCircularPromise = (async () => {
                    // getInfoByHash を呼ぶとサーバーセッションが変更され loadCircularByHash(等)の処理が変わる
                    // ↓ 変わるのを待つ必要がある
                    await userHashInfoPromise;
                    const ret = await this.loadCircularByHash({
                        hashUserPromise: userHashInfoPromise,
                        accessCodeHashFlgPromise
                    });
                    if (!ret) {
                        this.$router.back();
                    }
                    if(!(!this.$route.query.back && this.$store.state.home.accessCodePopupActive) && this.checkCircularStatus() === true){
                        this.$store.commit("home/homeClearState", null, { root: true });
                        this.isValidAccessCode = false;
                        this.$modal.show('pullbackshow-modal');
                        return ;
                    }
                })();
                promises.push(loadCircularPromise);
            }

            const useHashInfoPromise = (async () => {
                this.userHashInfo = await userHashInfoPromise;
                // PAC_5-1488 クラウドストレージを追加する Start
                if (this.userHashInfo && this.userHashInfo.storage) {
                  this.settingLimit = this.userHashInfo.storage;
                }
                // PAC_5-1488 End
                localStorage.setItem('envFlg', this.userHashInfo.current_env_flg);

                // PAC_5-445 回覧者所属環境に遷移すること
                if (this.userHashInfo.current_env_flg == 0) {
                    if (this.userHashInfo.current_edition_flg == 0) {
                        localStorage.setItem('return_url_original', config.OLD_AWS_API_URL);
                    } else {
                        localStorage.setItem('return_url_original', config.AWS_API_URL);
                    }
                } else {
                    localStorage.setItem('return_url_original', config.K5_API_URL);
                }

                if (backAndHasFiles) {
                    this.selectFile(null);
                    this.onFileTabClick(this.files[0], 0);
                    this.$nextTick(function () {
                        this.calcPdfViewerWidth();
                    })
                } else {
                    this.rotateAngle = this.userHashInfo.default_rotate_angle;
                    this.opacity = this.userHashInfo.default_opacity;
                    const cir_info = this.userHashInfo.circular_info_first;
                    if (cir_info === "回覧先") {
                        this.tab_cir_info = 1;
                    } else if (cir_info === "コメント") {
                        this.tab_cir_info = 2;
                    } else if (cir_info === "捺印履歴") {
                        this.tab_cir_info = 3;
                    } else {
                        this.tab_cir_info = 0
                    }
                }
                if (this.userHashInfo.is_external != undefined && !this.userHashInfo.is_external) {
                    await Axios.get(`${config.BASE_API_URL}/public/getStampsByHash?date=${this.$moment(this.date).format('YYYY-MM-DD')}`, {data: {nowait: true,usingHash: true}})
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
                                // state.stamps.push(stamp);
                                this.stampDisplays.push(stamp);
                                this.stampUsed.push(stamp);
                            });
                        })
                        .catch(error => { return []; });

                    const defaultStampId = this.userHashInfo.last_stamp_id;
                    const lastStampId = Utils.getCookie(`lastStampSelectedId_${this.userHashInfo.email}_${this.userHashInfo.current_env_flg}_${this.userHashInfo.current_server_flg}`);

                    if (defaultStampId) {
                        var selStamp = this.stampDisplays.find(item => item.id === defaultStampId);
                        this.selectStamp(selStamp);
                    } else if (lastStampId) {
                        var selStamp = this.stampDisplays.find(item => item.id === lastStampId);
                        this.selectStamp(selStamp);
                    }
                }
            })();

            promises.push(useHashInfoPromise);
        }
      promises.push(
        (async () => {
          /* ▼ PAC_5-778　「長期保管」ボタンが表示されない start */
          var company = await Axios.get(`${config.BASE_API_URL}/public/setting/getMyCompany`, {data: {usingHash: true, finishedDate: this.finishedDate}})
              .then(response => {
                  return response.data ? response.data.data : [];
              })
              .catch(() => {return []});
          this.canStoreCircular = company && company.long_term_storage_flg && this.$store.state.user.option_flg == 0;
          this.showFolderFlg = company && company.long_term_folder_flg;
          this.sanitizing_flg = company.sanitizing_flg;
          this.companyConfidentialFlg = company.confidential_flg;
            /* ▲ PAC_5-778　「長期保管」ボタンが表示されない  end  */

          this.default_stamp_history_flg = company.default_stamp_history_flg

        })(),
          (async () => {
            this.longtermIndex = await Axios.get(`${config.BASE_API_URL}/public/longTermIndex/getLongTermIndex`, {data: {usingHash: true}})
                .then(response => {
                  return response.data ? response.data.data : [];
                })
                .catch(() => {return []});
          })(),
      );

      await Promise.all(promises);

      if(!this.$route.query.back && this.$store.state.home.accessCodePopupActive) {
        this.$modal.show('access-modal');
        this.isValidAccessCode = false;
      }
      if(!this.$route.query.back){
          this.addStampHistory = false;
          this.addTextHistory = false;
      }else{
          if(this.addStampHistory == true){
              this.radioVal = "addStampHistory";
          }
          if(this.addTextHistory == true){
              this.radioVal = "addTextHistory";
          }
      }

      if (this.default_stamp_history_flg == 1) {
          this.addStampHistory = true;
          this.radioVal = "addStampHistory";
      }
      if(this.$store.state.home.circular){
        // アクセスコード入力不要の場合
        this.enableAdd = this.$store.state.home.circular.checkEnableAdd;
        this.specialCircularFlg = this.circular.special_site_flg;
        this.groupName = this.circular.special_site_group_name;
        // 特設サイト
        if(this.specialCircularFlg){
          if (this.files.length < 1) {
            this.$modal.show('special-site-modal');
          }
          this.circular.users.forEach(item => {
            if(item.email == this.loginCircularUser.email && item.mst_company_id == this.loginCircularUser.mst_company_id && item.special_site_receive_flg == 1){
              this.specialCircularReceiveFlg = true;
            }
            if(item.special_site_receive_flg == 1 && item.id == this.circularUserLastSend){
              this.circularUserLastSendIdIsSpecial = true;
            }
          });
        }
      }

      this.$nextTick(()=>{
          if (this.isValidAccessCode){
              this.validatHasAttachment(this.circular.id)
          }
          if(this.circularUserLastSend && this.circularUserLastSend.circular_status === this.CIRCULAR_USER.NOTIFIED_UNREAD_STATUS) {
              this.sendMailViewed({circular_user_id: this.circularUserLastSend.id, is_template_circular: this.isTemplateCircular});
          }
      })

      this.zoom = 100;

      if(this.$store.state.home.title && (this.$store.state.home.title).trim() !== ""){
          this.docName = this.$store.state.home.title;
      }else{
          let docName = '';
          for(let i=0;i<this.files.length;i++){
              if(i===0){
                  docName += this.files[i].name;
              }else{
                  docName += ',' + this.files[i].name;
              }
          }
          this.docName = docName;
      }

        window.addEventListener('resize', this.handleResize);
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
        // PAC_5-1398添付ファイル機能　「添付ファイル」表示
      if (this.circular){
        let createCircularCompany  = await Axios.get(`${config.BASE_API_URL}/public/setting/getCreateCircularCompany?circular_id=${this.circular.id}&finishedDate=${this.finishedDate}`,{data: {usingHash: true}})
            .then(response => {
              return response.data ? response.data.data: [];
            })
            .catch(() => { return []; });
        this.isShowAttachment = createCircularCompany.attachment_flg == 1;
      }
        this.is_ipad = /(iPad)/i.test(navigator.userAgent);
      
      this.isShowTextFunction = (this.circular?this.circular.text_append_flg==1:false)&&(this.companyLimit?this.companyLimit.text_append_flg==1:false);
    },
    beforeDestroy() {
      // 取得を止めるため
      $('body').removeClass('disabledScroll');
      this.visiblePageRange = [-1, -1];
      this.visibleThumbnailRange = [-1, -1];
      /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
      this.$store.commit('home/updateCloudBoxFlg',false);
      /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
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
  background: -webkit-linear-gradient(left, #FF0000,#FFB5B5);   /*Safari5.1 Chrome 10+*/
}
</style>

<style lang="scss">

input[type=text], select, textarea{
  transform: scale(1) !important;
}

body{
  overflow-y: auto !important;

  &.disabledScroll{
    overflow: hidden !important;
    position: absolute;
    touch-action:none;
    -webkit-overflow-scrolling: touch;
  }

  #bg_custom{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 1000px;
    overflow: hidden;
    z-index: 800;
  }
}

.pre-msg .vs-dialog-text{
  white-space: pre-line;
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
      .con-select{
        width: 150px;
      }
    }
    .font-size{
      margin: 0 3px;

      .con-select{
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

#main-home.mobile{
display: none;
}

#main-home-mobile.mobile {

  display: block;
  position: relative;
  padding: 0 1.2rem;
  overflow: hidden !important;

  .upload-wrapper {
    background: #fff;
    box-shadow: 0 4px 25px 0 rgb(0 0 0 / 10%);
    border-radius: 8px;
    padding: 10px;

    .upload-box {
      border-radius: 10px;
      border: 3px dashed #D1ECFF;
      height: 45vh;
      width: 80% !important;
      margin: 3vh 0;

      label.wrapper{
        padding: 60px 15px 80px;
        float: left;
        width: 100%;
        height: 100%;
      }
      input[type="file"]{
        display: none;
      }
      img{
        &.file{
          width: 32px;
          margin-right: 1rem;
        }
        &.cloud{
          width: 64px;
        }
      }
    }

    .label-upload-cloud{
      .vs-divider--text{
        white-space: break-spaces;
        font-size: 16px;
      }
    }
    .btn-cloud{
      margin-bottom: 7rem;
      button{
        width: 48%;
        margin: 0 4% 1rem 0;

        .download-item-text{
          font-size: 0.8rem;
        }
        &:nth-child(2), &:nth-child(4){
          margin-right: 0;
        }

        span.vs-button--background{
          background: transparent!important;
          outline: none;
        }
      }
    }
  }

  .swiper-wrapper{
    width: calc( 100% - 80px );

    img{
      max-width: 100%;
    }
  }
  .stamp-item.selected{
    display: inline-flex;
    border: 4px solid rgba(var(--vs-primary), 1);
  }

  .pdf-content{
    canvas{
      border: 1px solid #dcdcdc !important;
    }
  }

  .tabSelected{
    padding: 8px 0;
    position: relative;
    background: #f8f8f8;

    > div{
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

      > svg{
        position: absolute;
        top: 5px;
        right: 5px;
        width: 12px;
        height: 20px;
        color: #0984e3;
        z-index: 3;
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
      transition: 0.5s;

      &::-ms-expand {
        display: none;
      }
    }

    .add-document, .remove-document{
      display: inline-block;
      width: 30px;
      height: 40px;
      line-height: 40px;
      position: absolute;
      top: 0;
      text-align: center;
      right: 0;
    }
    .remove-document{
      color: #EA5455 ;
    }

    &.is-remove-icon, &.is-add-icon{
      > div{
        &:before{
          right: 31px;
        }

        > svg{
          right: 35px;
        }

        select{
          width: calc( 100% - 30px );
        }
      }
    }

    &.is-remove-icon.is-add-icon{
      > div{
        &:before{
          right: 61px;
        }

        > svg{
          right: 65px;
        }

        select{
          width: calc( 100% - 60px );
        }
        .add-document{
          right: 25px;
        }
      }
    }
  }
  .confidential{
    display: inline-block;
    width: 100%;
    margin-bottom: 3px;

    .confidential-input, .confidential-label{
      float: left;
    }

    .confidential-input{
      width: 30px;
      margin-right: 5px;

      .confidentialCheck {
        width: 25px;
        height: 20px;
      }
    }
    .confidential-label{
      width: calc( 100% - 35px );
    }
  }

  .preview-list-page{
    height: 32px!important;
  }

#handleMailList{
  background: #f2f2f2;
  text-align: center;
  position: relative;
  height: 40px;
  line-height: 40px;

  .icon{
    display: inline-block;
    position: absolute;
    top: 5px;
    left: 0;

    i{
      transition: 0.3s;
    }

    &.hide{
      i{
        transform: rotate(-90deg);
      }
    }
  }
}

#mail_list_box{
  display: none;
}

.tab, .tab-active{
  width: 33.3333%;
  height: 60px;
  line-height: 60px;
  text-align: center;
  border: 1px solid #dcdcdc;
  display: block;
  float: left;
}
.tab {
  background: #f2f2f2;
  color: #dcdcdc;
}
.tab-active{
  background: #fff;
  border-bottom: 0 solid #fff;
}

.comments {
  background: #fff;
  border: 1px solid #dcdcdc;
  border-top-width: 0;
  min-height: 20px;
  padding: 10px 0;
  margin-top: -5px;

  .date{
    float: none;
    margin-left: 20px;
  }

  .con-ul-tabs{
    ul{
      margin: 0 auto 0 calc( 50% - 90px );
      display: inline-block;
      width: 180px;
      text-align: center;

      li{
        display: inline-block;
        width: 49%;

        &.activeChild{
          border-bottom: 2px solid #0984e3;
        }
      }
    }

    .line-vs-tabs{
      display: none;
    }
  }
}
.histories {
  background: #fff;
  border: 1px solid #dcdcdc;
  border-top-width: 0;
  min-height: 20px;
  padding: 10px 0;
  margin-top: -5px;
  overflow: auto;

  .item{
    border-top: 1px solid #cdcdcd;

    &:first-child{
      border-top-width: 0;
    }

    img{
      max-width: 80%;
    }
  }
}
.mail-list {
  background: #fff;
  border: 1px solid #dcdcdc;
  border-top-width: 0;
  min-height: 100px;
  padding: 10px 0;
  margin-top: -5px;

  .item{
    margin: 0 10px 20px 10px;
    position: relative;
    padding: 5px 0;
    border-radius: 5px;

    &:last-child{
      margin-bottom: 0;
    }

    &.sended{
      background: #c8efc8;
    }

    &.maker, &.unsend{
      background: #d1ecff;
    }

    .mail_list_info{
      display: inline-block;
      position: relative;
      width: 100%;
      padding-left: 10px;

      .mail_flag{
        position: absolute;
        right: 70px;
        top: 0;
      }

      .name {
        max-width: 50%;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
      }

      .email {
        max-width: 90%;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
      }

      .mail_flag_final{
        position: absolute;
        right: 10px;
        top: 1px;

        .final{
          color: #0a84e3;
          border: 1px solid #0a84e3;
          border-radius: 5px 5px 5px 5px;
          margin:0px 0 0 10px;
          padding: 0 5px;
        }
      }
    }

    &:not(.maker):after{
      content: "";
      position: absolute;
      left: 40px;
      top: -20px;
      width: 15px;
      height: 20px;
      background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIuMTcxIDUxMi4xNzEiIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiBjbGFzcz0iaG92ZXJlZC1wYXRocyI+PHBhdGggZD0iTTQ3OS4wNDYgMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSA0Ljc3OSAzNDcuNTI2IDAgMzQxLjYzOCAwSDE3MC45NzFjLTUuODg4IDAtMTAuNjY3IDQuNzc5LTEwLjY2NyAxMC42Njd2MjY2LjY2N0g0Mi45NzFhMTAuNzAyIDEwLjcwMiAwIDAwLTkuODU2IDYuNTcxYy0xLjY0MyAzLjk4OS0uNzQ3IDguNTc2IDIuMzA0IDExLjYyN2wyMTIuOCAyMTMuNTA0YzIuMDA1IDIuMDA1IDQuNzE1IDMuMTM2IDcuNTUyIDMuMTM2czUuNTQ3LTEuMTMxIDcuNTUyLTMuMTE1bDIxMy40MTktMjEzLjUwNGExMC42NDUgMTAuNjQ1IDAgMDAyLjMwNC0xMS42Mjh6IiBkYXRhLW9yaWdpbmFsPSIjMDAwMDAwIiBjbGFzcz0iaG92ZXJlZC1wYXRoIGFjdGl2ZS1wYXRoIiBkYXRhLW9sZF9jb2xvcj0iIzAwMDAwMCIgZmlsbD0iIzA5ODRlMyIvPjwvc3ZnPg==);
      background-size: contain;
      background-position: 50%;
      background-repeat: no-repeat;
    }
  }



}

.btn_dialog{
  z-index: 900;
  position: fixed;
  bottom: 0px;
  border: 1px solid #dcdcdc;
  background: #f2f2f2;
  width: calc( 100% - 2.4rem );
  height: 60px;
  left: 1.2rem;
  border-radius: 0;
  line-height: 16px;
  padding: 10px 0 5px;
  display: inline-block;
  text-align: center;
  transition: 0.3s;
  transition-delay: 0.4s;

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

  &.full{
    height: 120px;
  }

  .btn_dialog_status{
    position: fixed;
    right: 10px;
    top: 160px;
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

.stamps-confirm-modal{
  opacity: 0;
  position: fixed;
  z-index: -1;
  display: none !important;
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
}
}

.v--modal-overlay{
  z-index: 10001;
}

.custom-template-view-cloud-list{
  .vs-list{
    .vs-list--slot{
      margin-left: 0 !important;
    }
  }
}

@media ( min-width: 601px ){
  #main-home-mobile.mobile{
    top: -110px;
    padding: 0 0.2rem;

    .upload-wrapper {
      .label-choose-file{
        margin: 1rem 0 3rem 0;
      }
      .label-upload-cloud{
        margin: 3rem 0 5rem 0;

        .vs-divider--text{
          white-space: nowrap  !important;
        }
      }

      .btn-cloud {
        button{
          max-width: 150px;

          &:not(:last-child){
            margin-right: 10px !important;
          }

        }
      }
    }

    .preview-list-page{
      width: 100%;
    }
    .tabSelected{
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
  }
}
@media ( max-width: 600px ){
  #main-home-mobile.mobile {
    top: -160px;
    position: relative;

    .preview-list-page{
      width: 100%;
    }

    .upload-wrapper {
      .upload-box{
        width: 100% !important;
        margin: 1rem 0;
      }
    }

    .tabSelected {
      .add-document, .remove-document{
        line-height: 30px;
      }
      > div {
        > svg{
          top: 2px;
        }
      }
    }

    #stamps-modal .v--modal-box{
      width: 90% !important;
      left: 5%!important;
    }
  }
  .v--modal{
    width: 90% !important;
    left: 5% !important;
  }
  #upload-modal{
    .upload-button{
      button{
        margin: 5px 0 5px 5px !important;
        padding: 0.75rem 1rem !important;
      }
    }
  }
}

@media ( max-width: 240px ){
  #main-home-mobile.mobile{
    top: -165px;
    .btn_dialog {
      padding: 0;
      height: 60px;

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

.holamundo {
  button, .vx-input-group .append-text.btn-addon .vs-button{
    white-space: nowrap;
  }
}

</style>
