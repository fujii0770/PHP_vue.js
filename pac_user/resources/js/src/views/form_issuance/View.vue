<template>
    <div>
	<div id="main-home">
		<div style="margin-bottom: 15px">
			<vs-row>
			<vs-col vs-type="flex" vs-w="3">
        <div style="display: flex;flex-wrap: nowrap;"  class="buttonMain"  v-if=" !showLeftToolbar">
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
                    <li><p style="color: #0984e3;"><span class="badge badge-primary">1</span> プレビュー</p></li>
                    <li><p style="background: transparent"></p></li>
                </ul>
            </vs-col>
			<vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6">
        		<vs-button id="button7" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="goBack"> 戻る</vs-button>
                <vs-dropdown :vs-trigger-click="is_ipad" v-if="(settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox) && (fileSelected && !fileSelected.del_flg)">
                    <vs-button id="button5" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="!settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード</vs-button>
                    <vs-button id="button5_2" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード予約</vs-button>
                    <vs-dropdown-menu >
                        <li class="vx-dropdown--item">
                          <a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="default" @click.native.stop="changeDefaultStatus($event);"  vs-name="radioVal"  v-model="radioVal" :disabled="countAllTabNum" >完了済みファイル</vs-radio></a>
                        </li>
                        <li class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addStampHistory" @click.native.stop="changeStampHistory($event)"   vs-name="radioVal"    v-model="radioVal"  >回覧履歴を付ける</vs-radio></a></li>
                        <li v-show="settingLimit.is_show_current_company_stamp" class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addTextHistory" @click.native.stop="changeTextHistory($event)"   vs-name="radioVal"  :disabled="countAllTabNum"  v-model="radioVal"  >自社のみの回覧履歴を付ける</vs-radio></a></li>
                        <vs-dropdown-item v-if="settingLimit.storage_local && !settingLimit.sanitizing_flg">
                            <vs-button  :disabled="countAllTabNum" v-on:click="onDownloadFile" color="primary" class="w-full download-item" type="filled"><i class="fas fa-download"></i>  ローカル</vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.sanitizing_flg">
                          <vs-button color="primary" v-on:click="showReserveFile" class="w-full download-item" type="filled" style="padding: 0.75rem 1rem;"><i class="fas fa-download"></i>ダウンロード予約</vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_box && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('box')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/box.svg')" alt="Box"> <span class="download-item-text">Box</span></vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_onedrive && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('onedrive')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/onedrive.svg')" alt="OneDrive"> <p>OneDrive</p></vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_google && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('google')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/google-drive.png')" alt="Google Drive"> <span class="download-item-text">Google Drive</span></vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.storage_dropbox && !settingLimit.sanitizing_flg">
                            <vs-button color="primary" v-on:click="onDownloadExternalClick('dropbox')" class="w-full download-item" type="border" :disabled="countAllTabNum"><img class="download-icon" :src="require('@assets/images/dropbox.svg')" alt="pdf"> <span class="download-item-text">Dropbox</span></vs-button>
                        </vs-dropdown-item>
                    </vs-dropdown-menu>
                </vs-dropdown>
            </vs-col>
			</vs-row>
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
                    <div class="pdf-content" ref="pdfViewer">
                        <vs-col vs-type="flex" vs-w="12" vs-align="flex-start" vs-justify="flex-start">
                            <vs-navbar v-model="tabSelected"
                                color="#fff"
                                active-text-color="rgb(9,132,227)"
                                class="filesNav">

                                <vs-navbar-item v-for="(file, index) in filesLessMaxTabShow" :key="file.circular_document_id" :index="index" :class="'document ' + (file.confidential_flg ? 'is-confidential': (file.hasOwnProperty('tabColor')?file.tabColor:''))">
                                    <template v-if="index < maxTabShow">
                                        <a :class="[(file) !== '' && file.tabLogo?'no-padding':'', `filename`]" v-tooltip.top-center="file.name" v-on:click="onFileTabClick(file, index)" href="#">
                                            <i v-if="file.confidential_flg && file.mst_company_id !== loginUser.mst_company_id" class="fas fa-lock" style="color: #fdcb6e"></i>
                                            <span v-if="file.confidential_flg && file.mst_company_id !== loginUser.mst_company_id"> ー </span>
                                            <i v-if="!file.tabLogo && file.timestampLogo" class="far fa-clock fa-lg" style="color: dimgrey"></i>
                                            <img v-if="!file.confidential_flg && (file) !== '' && file.tabLogo" :src="`data:image/png;base64,${file.tabLogo}`"  alt="logo" class="logo-format">
                                            <template v-if="(file) === '' || !file.tabLogo">{{file.name}}</template>
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
                                            <vs-dropdown-item v-for="(file, index) in filesMoreThanMaxTabShow" :key="file.circular_document_id" :index="index" :class="'more-document-item ' + (file.confidential_flg ? 'is-confidential':'')">
                                                <p class="filename" v-tooltip.left-start="file.name" v-on:click="onFileTabClick(file, index)" :style="'white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin-right: 50px;font-size: 14px;max-width: 185px;min-height:25px' + (index === tabSelected ? 'color:#0984e3':'')"><i v-if="file.confidential_flg && file.mst_company_id !== loginUser.mst_company_id" class="fas fa-lock" style="color: #fdcb6e"></i> <span v-if="file.confidential_flg && file.mst_company_id !== loginUser.mst_company_id"> ー </span>{{file.name}}</p>
                                            </vs-dropdown-item>
                                        </vs-dropdown-menu>
                                    </vs-dropdown>
                                </vs-navbar-item>
                            </vs-navbar>
                        </vs-col>

                        <template v-if="fileSelected != null">
                          <pdf-pages v-show="!hasRequestFailedImage"
                            ref="pages"
                            :expectedPagesSize="expectedPagesSize" :pages="pages"
                            :imageScale="pageImageScale"
                            :deleteFlg="fileSelected.del_flg" :deleteWatermark="fileSelected.delete_watermark"
                            @visible-page-changed="onVisiblePageChanged"
                            :enable="false">
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
                    <div class="tools" :style="!stampToolbarActive ? 'display:none':'' ">
                        <div class="is-send-screen">
                            <div style="padding: 0">
                                <vs-tabs v-model="tab_cir_info" >
                                    <vs-tab label="詳細">
                                      <vs-card class="detail flex-item mb-1"  style="position: static; height: calc(100vh - 260px);overflow-y:scroll;overflow-x:hidden;">
                                          <h3>明細詳細</h3>
                                          <vs-row>
                                              <vs-col vs-w="4" class="label">明細ID</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.company_frm_id }}</vs-col>
                                          </vs-row>
                                          <vs-row>
                                              <vs-col vs-w="4" class="label">明細分類</vs-col>
                                              <vs-col v-if="other_flg == 0 " vs-w="8" class="info">請求書</vs-col>
                                              <vs-col v-if="other_flg == 1 " vs-w="8" class="info">明細</vs-col>
                                          </vs-row>
                                          <vs-row>
                                              <vs-col vs-w="4" class="label">明細名</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.frm_name }}</vs-col>
                                          </vs-row>
                                          <vs-row>
                                              <vs-col vs-w="4" class="label">取引先</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.customer_name }}</vs-col>
                                          </vs-row>
                                          <vs-row v-if="other_flg == 0 ">
                                              <vs-col vs-w="4" class="label">請求金額</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.invoice_amt_comma }}</vs-col>
                                          </vs-row>
                                          <vs-row v-if="other_flg == 0 ">
                                              <vs-col vs-w="4" class="label">売上計上日</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.trading_date | moment("YYYY/MM/DD") }}</vs-col>
                                          </vs-row>
                                          <vs-row v-if="other_flg == 0 ">
                                              <vs-col vs-w="4" class="label">入金期日</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.payment_date | moment("YYYY/MM/DD") }}</vs-col>
                                          </vs-row>
                                          <vs-row v-if="other_flg == 0 ">
                                              <vs-col vs-w="4" class="label">請求日</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.invoice_date | moment("YYYY/MM/DD") }}</vs-col>
                                          </vs-row>
                                          <vs-row v-if="other_flg == 1 ">
                                              <vs-col vs-w="4" class="label">基準日</vs-col>
                                              <vs-col vs-w="8" class="info">{{ itemReadingDetail.frm_table_data.reference_date | moment("YYYY/MM/DD") }}</vs-col>
                                          </vs-row>
                                          <vs-row v-for="(item, key, index) in itemReadingDetail.frm_data_array" :key="index">
                                              <vs-col vs-w="4" class="label">{{ key }}</vs-col>
                                              <vs-col vs-w="8" class="info">{{ item }}</vs-col>
                                          </vs-row>
                                      </vs-card>
                                    </vs-tab>

                                    <vs-tab label="回覧先">
                                        <div class="mail-list">
                                            <p><strong>ファイル名</strong></p>
                                            <p class="filename">{{fileSelected ? fileSelected.name : ''}}</p>
                                            <template v-if="!isTemplateCircular">
                                              <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + ((circularUserSendBack && user.id === circularUserSendBack.id) ? ' sendback': '')" vs-type="flex" v-for="(user, index) in circularUsers" v-bind:key="user.email + index" :index="index">
                                                  <vs-col vs-w="10">
                                                      <p>{{user.name}} <span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                      <p>{{user.email}}</p>
                                                  </vs-col>
                                                  <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                      <p v-if="circularUsers && index === (circularUsers.length - 1)" href="#" class="final">最終</p>
                                                      <a v-if="circularUserLastSendId === user.id" href="#"> <i class="far fa-flag"></i></a>
                                                  </vs-col>
                                              </vs-row>
                                            </template>
                                            <!-- template route users start -->
                                            <vs-row class="item sended maker" v-if="isTemplateCircular">
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
                                              <vs-row class="item sended" vs-type="flex" v-for="(userRoute, index) in templateUserRoutes" :index="index" :key="index">
                                                  <vs-col vs-w="10" :class="(circularUserSendBack && user.id === circularUserSendBack.id) ? ' sendback': ''">
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
                                            <vs-row v-if="circularUsers && circularUsers.length > 0" class="item unsend" vs-type="flex">
                                                <vs-col vs-w="10">
                                                    <p>{{circularUsers[0].name}}</p>
                                                    <p>{{circularUsers[0].email}}</p>
                                                </vs-col>
                                                <vs-col vs-type="flex" vs-w="2" vs-justify="flex-end" vs-align="center">
                                                    <a v-if="!circularUserLastSendId" href="#" class="mr-2"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                            </vs-row>
                                        </div>
                                    </vs-tab>
                                    <vs-tab label="コメント">
                                      <div class="comments comment-height comment-position">
                                        <vs-tabs class="comment-height">
                                          <vs-tab label="社内宛">
                                            <vs-row class="item" v-for="(comment, index) in commentsNotPrivate" v-bind:key="comment.name + index" :index="index">
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                          </vs-tab>
                                          <vs-tab label="社外宛">
                                            <vs-row class="item" v-for="(comment, index) in commentsIsPrivate " v-bind:key="comment.name + index" :index="index">
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                          </vs-tab>
                                        </vs-tabs>
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
                                                    <p class="w-full"><span class="user-name">{{history.name}}</span> <span class="date">{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span> <br/><span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{history.email}}</span></p>
                                                </vs-col>
                                            </vs-row>
                                        </div>
                                    </vs-tab>
                                </vs-tabs>
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

        <vs-popup
          title="名刺情報"
          :active.sync="showBizcardInfo">
          <BizcardPopUpInner :bizcardData="bizcardData"/>
        </vs-popup>

        <vs-popup 
          title="選択文書ダウンロード予約" 
          :active.sync="confirmDownload">
          <DownloadReservePopUpInner
          :inputMaxLength="inputMaxLength"
          :downloadReserveFilename.sync="downloadReserveFilename"
          @accept-click="onDownloadReserve()"
          @close-click="confirmDownload=false"
          />
        </vs-popup>

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
	</div>

        <div id="main-home-mobile">
        <div style="width:100%;margin-bottom:10px;" v-if="circularUsers && circularUsers.length > 0">
            <span @click="goBack"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
            <div style="display:inline;position:relative;top:-10px;">{{fileSelected ? fileSelected.name : ''}}</div>
        </div>
        <div style="width:100%;height:60px;background-color: #f2f2f2;padding-top:20px;" v-if="circularUsers && circularUsers.length > 0">From : {{circularUsers && circularUsers.length > 0 ? circularUsers[0].name : ''}}<span style="float: right;">{{circularUsers[0].create_at | moment("YYYY/MM/DD")}}</span></div>
        <div>
            <div class="tools" :style="!stampToolbarActive ? 'display:none':'' ">
                <div>
                    <div style="padding: 0">
                        <div>
                            <div>
                                <div id="mail-list-label" class="tab-active" v-on:click="changeTabMail()">回覧先</div>
                                <div id="comments-label" class="tab" v-on:click="changeTabComments()">コメント</div>
                            </div>
                            <div>
                                <div class="mail-list">
                                    <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + ((circularUserSendBack && user.id === circularUserSendBack.id) ? ' sendback': '')" vs-type="flex" v-for="(user, index) in circularUsers" v-bind:key="user.email + index" :index="index">
                                        <vs-col vs-w="1" vs-align="center">
                                            <a v-if="circularUserLastSendId === user.id" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                        </vs-col>
                                        <vs-col vs-w="5">
                                            <p>{{user.name}} <span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span>
                                                <span v-if="index === circularUsers.length - 1" href="#" class="final">最終</span>
                                            </p>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                            <p>依頼日：{{user.update_at | moment("YYYY/MM/DD")}}</p>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-if="circularUsers && circularUsers.length > 0" class="item unsend" vs-type="flex">
                                        <vs-col vs-type="flex" vs-w="1" vs-align="center">
                                        </vs-col>
                                        <vs-col vs-w="5">
                                            <p>{{circularUsers[0].name}}</p>
                                        </vs-col>
                                        <vs-col vs-type="flex" vs-w="5" vs-justify="flex-end" vs-align="center">
                                            <p>依頼日：{{circularUsers[0].create_at| moment("YYYY/MM/DD")}}</p>
                                        </vs-col>
                                    </vs-row>
                                </div>
                            </div>
                            <div>
                                <div class="comments" style="display: none;">
                                    <vs-row class="item" style="width: 330px; height: 130px; overflow: scroll;" v-for="(comment, index) in commentsFilter" v-bind:key="comment.name + index" :index="index">
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
        <vs-card class="work-content">
            <vs-row v-if="mobilePages.length > 0">
                <vs-col vs-type="flex">
                    <div class="preview-list" style="background-color: rgb(242, 242, 242);width:100%;height:80px;">
                        <div style="text-align: center;margin-bottom:10px;">{{mobilePages.length}}件の文書があります</div>
                        <div style="text-align: center;">
                                <span @click="changePage(currentPageNo-1)">
                                    <vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon>
                                </span>
                            <div style="display:inline-block;position:relative;top:-10px;" class="page"
                              v-for="(page, index) in mobilePages"
                              v-bind:key="index">
                                <div :class="currentPageNo == index + 1 ?'btn_selected':'btn'" v-on:click="changePage(index + 1)">{{index + 1}}</div>
                            </div>
                            <span @click="changePage(currentPageNo+1)">
                                    <vs-icon icon="keyboard_arrow_right" size="medium" color="primary"></vs-icon>
                                </span>
                        </div>
                    </div>
                </vs-col>
            </vs-row>
            <vs-row>
                <vs-col vs-type="flex" style="transition: width .2s;">
                    <div class="pdf-content">
                        <div v-show="fileSelected != null" class="content">
                            <div style="text-align: center;margin-bottom:10px;">
                              <select v-model="tabSelected" @change="changeSelectedFile(tabSelected)">
                                <option v-for="(file, index) in files" :value="index" :key="index">{{ file.name }}</option>
                              </select>
                            </div>
                            <div>
                                <div  v-if="fileSelected != null">
                                    <div class="page page_large" v-for="(page, index) in mobilePages" v-bind:key="index" :index="index">
                                        <div v-show="currentPageNo == index + 1">
                                            <img :src="page.imageUrl" alt="a4" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    import PdfPages from "../../components/home/PdfPages";
    import PdfPageThumbnails from "../../components/home/PdfPageThumbnails";

    import { dragscroll } from 'vue-dragscroll'
    import { CIRCULAR } from '../../enums/circular';
    import { CIRCULAR_USER } from '../../enums/circular_user';
    import Utils from '../../utils/utils';
    import { getPageUtil } from '../../utils/pagepreview';
    import config from "../../app.config";
    import Axios from "axios";

    import draggable from 'vuedraggable'
    import CloudUploadModalInner from "@/components/home/CloudUploadModalInner.vue"
    import UpdateCheckDocModalInner from "@/components/home/UpdateCheckDocModalInner";
    import BizcardPopUpInner from '@/components/home/BizcardPopUpInner.vue'
    import DownloadReservePopUpInner from "@/components/home/DownloadReservePopUpInner.vue";

    const PPI = 150;

    export default {
        components: {
            InfiniteLoading,
            PdfPages,
            PdfPageThumbnails,
            draggable,
            CloudUploadModalInner,
            UpdateCheckDocModalInner,
            BizcardPopUpInner,
            DownloadReservePopUpInner,
        },
        directives: {
            dragscroll
        },
        data() {
            return {
                item: {},
                confirmDelete: false,
                itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}], frm_table_data: {} },
                CIRCULAR: CIRCULAR,
                CIRCULAR_USER: CIRCULAR_USER,
                histories: [],
                pages: [],
                thumbnails: [],
                currentPageNo: 1,
                maxTabShow: 3,
                tabSelected: 0,
                stampToolbarActive: true,
                access_code: '',
                isValidAccessCode: true,
                showAccessCodeMessage: false,
                settingLimit:{},
                filename_upload: '',
                cloudFileItems: [],
                breadcrumbItems: [],
                cloudLogo: null,
                cloudName: null,
                currentCloudFolderId: 0,
                checkShowConfirmAddSignature: JSON.parse(getLS('user')).check_add_signature_time_stamp,
                canStoreCircular:false,
                keywords: '',
                tab_cir_info: 0,
                showBizcardInfo: false,
                bizcardData: null,
                base64_prefix: {
                    jpeg : "data:image/jpeg;base64,",
                    png : "data:image/png;base64,",
                    gif : "data:image/gif;base64,"
                },

                firstIndentation: true,
                showLeftToolbar: true,
                // PAC_5-1216対応 ▼
                saveId: null,//上書きファイルID
                // PAC_5-1216対応 ▲
                is_ipad: false,
                viewerWidth: 1,
                thumbnailViewerWidth: 0,
                visiblePageRange: [-1, -1],
                visibleThumbnailRange: [-1, -1],
                longtermIndex: [],
                finishedDate: 0,
                attachmentUploads : [],
                isShowAttachment:true,
                other_flg: 0,
                radioVal: "default",
                countAllTabNum: false,
                confirmDownload: false,//選択文書ダウンロード予約 画面フラグ
                downloadReserveFilename: '',//ダウンロード予約のファイル名
                inputMaxLength: 46,//ファイル名の長さ
            }
        },
        computed: {
            ...mapState({
                files: state => state.home.files,
                fileSelected: state => state.home.fileSelected,
                circular: state => state.home.circular,
                currentViewingUser: state => state.home.currentViewingUser,
                addStampHistory: state => state.home.addStampHistory,
                companyLogos: state => state.home.company_logos,
                addTextHistory: state => state.home.addTextHistory,
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
            }),
            filesLessMaxTabShow () {
              return this.files.slice(0,this.maxTabShow)
            },
            filesMoreThanMaxTabShow () {
              return this.files.slice(this.maxTabShow)
            },
            commentsNotPrivate () {
              return this.comments.filter(comment => comment.private_flg===0 && comment.text && comment.text.trim())
            },
            commentsIsPrivate () {
              return this.comments.filter(comment => comment.private_flg===1 && comment.text && comment.text.trim())
            },
            commentsFilter() {
              return this.comments.filter(comment => comment.text && comment.text.trim())
            },
            zoom: {
                get() {return this.$store.state.home.fileSelected ? this.$store.state.home.fileSelected.zoom : 100;},
                set(value) { this.updateCurrentFileZoom(value)}
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
            circularUserLastSendId() {
              if(!this.circular || !this.circular.users) {
                return null;
              }
              let circular_user =  this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.END_OF_REQUEST_SEND_BACK);
              if(!circular_user) return  null;
              if(this.circularUsers.findIndex(item => item.id === circular_user.id) < 0) circular_user = this.circular.users.find(item => circular_user.parent_send_order === item.parent_send_order && item.child_send_order === 1);
              return circular_user.id;
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
            copyFiles: function() {
                if (this.files && this.files.length > 0) {
                    let _files = [];
                    for(let file of this.files) {
                        _files.push(Object.assign({}, file));
                    }
                    _files = _files
                        .sort(function (a, b) {
                            return a.circular_document_id - b.circular_document_id;
                        })
                        .map(function (item) {
                            item.tabColor = "";
                            return item;
                        });

                    if (_files && _files.length > 0) {
                        _files[0].parent_send_order = 0;
                        for (let i = 1; i < _files.length; i++) {
                            if (_files[i-1].mst_company_id !== _files[i].mst_company_id) {
                                _files[i].parent_send_order = _files[i-1].parent_send_order + 1;
                            } else {
                                _files[i].parent_send_order = _files[i-1].parent_send_order;
                            }
                            if (_files[i].parent_send_order !== 0) {
                                if (_files[i].parent_send_order % 3 === 1) {
                                    _files[i].tabColor = "first";
                                } else if (_files[i].parent_send_order % 3 ===2) {
                                    _files[i].tabColor = "second";
                                } else {
                                    _files[i].tabColor = "third";
                                }
                            }
                        }
                        return _files;
                    }
                }
                return [];
            },
            comments: {
                 get() {return this.fileSelected && this.fileSelected.comments ? this.fileSelected.comments : []}
            },
            loginUser: {
                get() {
                  if(this.$store.state.home.usingPublicHash) return {};
                  return JSON.parse(getLS('user'));
                }
            },
            circularUsers: {
                get() {
                  if(!this.$store.state.home.circular || !this.$store.state.home.circular.users) return [];
                  return this.$store.state.home.circular.users.filter(item => {
                    return item.child_send_order === 0 || this.loginUser.mst_company_id === item.mst_company_id || (item.parent_send_order && item.child_send_order === 1);
                  });
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
        },
        methods: {
            ...mapActions({
                getPage: "home/getPage",
                clearState: "home/clearState",
                selectFile: "home/selectFile",
                updateCurrentFileZoom: "home/updateCurrentFileZoom",
                changePositionFile: "home/changePositionFile",
                loadCircular: "home/loadCircular",
                loadCircularForCompleted: "home/loadCircularForCompleted",
                getStampInfos: "home/getStampInfos",
                getStampInfosForCompleted: "home/getStampInfosForCompleted",
                getBizcardById: "bizcard/getBizcardById",
                downloadSendFile: "home/downloadSendFile",
                getLimit: "setting/getLimit",
                getCloudItems: "cloud/getItems",
                uploadToCloud: "home/uploadToCloud",
                getMyInfo: "user/getMyInfo",
                checkAccessCode: "circulars/checkAccessCode",
                afterCheckAccessCode: "home/afterCheckAccessCode",
                sendMailViewed: "application/sendMailViewed",
                storeCircular: "circulars/storeCircular",
                checkShowConfirmAddTimeStamp: "home/checkShowConfirmAddTimeStamp",
                addLogOperation: "logOperation/addLog",
                checkDeviceType: "home/checkDeviceType",
                getLongtermIndex: "circulars/getLongtermIndex",
                setLongtermIndex: "circulars/setLongtermIndex",
                getAttachment: "home/getAttachment",
                downloadAttachment:"home/downloadAttachment",
                getDetailReport: "formIssuance/getDetailReport",
                getDetailReportOther: "formIssuance/getDetailReportOther",
                reservePreviewFile: "home/reservePreviewFile",
                reserveAttachment: "home/reserveAttachment",
            }),
            onZoomOutClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom = Math.max(50, this.zoom - 10);
            },
            onZoomInClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom = Math.min(200, this.zoom + 10 );
            },
            onClickStampHistory: async function(history) {
                // 名刺機能のON/OFFを取得
                const getBizcardFlgResult = await Axios.get(`${config.BASE_API_URL}/setting/getBizcardFlg`)
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
                    let response = await this.getBizcardById(info);
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
            goBack: function() {
              this.$route.meta.isKeep=true
              this.$router.push(`/form-issuance/form-list`);
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
            addLogDownloadOperation: async function(result){
                const action = 'r9-14-download';
                // PAC_5-1027 ダウンロードの操作履歴が表示されない
                const resultCode = result ? 0 : 1;
                this.addLogOperation({ action: action, result: resultCode, params:{filename: this.fileSelected.name}});
            },
            async onDownloadFile(){
              const checkAddUsingTas =  await this.checkShowConfirmAddTimeStamp(this.finishedDate);
              this.$store.commit('home/checkAddUsingTas', checkAddUsingTas);
              if(checkAddUsingTas && this.checkShowConfirmAddSignature){
                this.$vs.dialog({
                      type:'confirm',
                      color: 'primary',
                      title: `確認`,
                      acceptText: 'はい',
                      cancelText: 'いいえ',
                      text: `タイムスタンプを付与しますか？`,
                      accept: async () => {
                          const ret = await this.downloadSendFile(this.finishedDate);
                          this.addLogDownloadOperation(ret);
                      },
                      cancel: async () => {
                          this.$store.commit('home/checkAddUsingTas', false);
                          const ret = await this.downloadSendFile(this.finishedDate);
                          this.addLogDownloadOperation(ret);
                      },
                  });
              } else {
                 const ret = await this.downloadSendFile(this.finishedDate);
                 this.addLogDownloadOperation(ret);
              }
            },
            onFileTabClick: function(file, index) {
              if(file.confidential_flg && file.mst_company_id !== this.loginUser.mst_company_id) return;
              if(this.fileSelected && file.circular_document_id === this.fileSelected.circular_document_id) return;
              if(index >= this.maxTabShow) this.changePositionFile({from: index, to: 0});

              this.selectFile(file);
              // tabSelected は vs-navbar 等により変更される
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
              this.currentPageNo = pageno;
            },
            onStampToolbarActiveChange: function (value) {
              this.stampToolbarActive = value;
              const $this = this;
              setTimeout(function() {
                $this.calcPdfViewerWidth();
                $this.selectPage($this.currentPageNo);
              },300);
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
          //PAC_5-1216 Start
          cancelConfirmUpdate: function() {
            this.$modal.hide('updatecheck-doc-modal');
          },
          onUploadCheck:function() {
              this.saveId = null;
              let isUpdate = false;
                this.cloudFileItems.forEach(function(File){
                  if(this.filename_upload== File.filename){
                    this.saveId = File.id;
                    isUpdate = true;
                  }
                },this);

              if (isUpdate && this.currentCloudDrive == 'box') {
                  this.$modal.show('updatecheck-doc-modal');
              } else {
                this.onUploadToCloudClick(false);
              }
          },
          onUploadToCloudClick: async function(confirm) {
            if(confirm){
                this.$modal.hide('updatecheck-doc-modal');
            }
            const checkAddUsingTas =  await this.checkShowConfirmAddTimeStamp(this.finishedDate);
            this.$store.commit('home/checkAddUsingTas', checkAddUsingTas);
            if(checkAddUsingTas && this.checkShowConfirmAddSignature){
              this.$vs.dialog({
                type:'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: `タイムスタンプを付与しますか？`,
                accept: async ()=> {
                  await this.doUploadToCloudClick();
                },
                cancel: async ()=> {
                  await this.$store.commit('home/checkAddUsingTas', false);
                  await this.doUploadToCloudClick();
                },
              });
            } else {
              await this.doUploadToCloudClick();
            }
          },
          doUploadToCloudClick: async function() {
            let data = {
              drive : this.currentCloudDrive,
              folder_id : this.currentCloudFolderId,
              filename : this.filename_upload,
              saveFile: false,
              finishedDate: this.finishedDate
            }
            if (this.saveId) data["file_id"] = this.saveId;

            const uploadRet = await this.uploadToCloud(data);
            if(uploadRet) {
              this.onCloudModalClosed();//PAC_5-3116
            }
          },
            changeTabMail () {
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
            changeTabComments () {
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
              if (pageno < 1) return;

              // 未取得ページ(ボタンが表示されていないページ)の場合、取得する
              if (pageno > this.mobilePages.length) {
                const res = await this.getPageImage(pageno, false);
                if (!res.ok) return;
              }
              this.selectPage(pageno);
            },
            changeDefaultStatus(e){
              // Because the native click event will be executed twice, the first time on the label tag 
              // and the second time on the input tag, this processing is required
              if (e.target.tagName === 'INPUT' || this.countAllTabNum == true) { return };
              this.addStampHistory = false;
              this.addTextHistory = false;
              this.radioVal = "default"
            },
            changeStampHistory(e) {
                // Because the native click event will be executed twice, the first time on the label tag 
                // and the second time on the input tag, this processing is required
                if (e.target.tagName === 'INPUT' || this.addStampHistory || this.countAllTabNum ) { return }
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
           if (e.target.tagName === 'INPUT' || this.addTextHistory || this.countAllTabNum ) {return};
              this.addTextHistory = true;
              this.addStampHistory = false;
              this.radioVal = "addTextHistory";
            },
            changeSelectedFile(index){
              if(this.files[index].circular_document_id != this.fileSelected.circular_document_id){
                this.onFileTabClick(this.files[index], index);
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
              if (firstIndex == -1) return;

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
            addAttachment: async function () {
                this.$modal.show('add-attachment-modal');
                if (!this.circular || !this.circular.users) return null;
                if (this.attachmentUploads.length <= 0) {
                    this.attachmentUploads = [];
                    let ret = await this.getAttachment(this.circular.id);
                    ret.forEach((item)=>{
                        this.attachmentUploads.push(item);
                    });
                }
            },
            onDownloadAttachment: async function(index){
              this.$vs.dialog({
                type:'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'OK',
                cancelText: 'キャンセル',
                text: `送信者が信頼できる場合、OKボタンをクリックしてファイルをダウンロードします。`,
                accept: async ()=> {
                  if (this.settingLimit.sanitizing_flg) {//ダウンロード予約
                    let info = {
                      circular_attachment_id: this.attachmentUploads[index].id,
                      file_name: this.attachmentUploads[index].file_name,
                    }
                    await this.reserveAttachment(info);
                  }else {//ダウンロード
                    await this.downloadAttachment(this.attachmentUploads[index].id);
                  }
                },
                cancel: async () => {
                  return null;
                },
              });
            },
            closeAttachmentModal: function() {
                this.$modal.hide('add-attachment-modal');
            },
            //PAC_5-2142 モバイルとPCでメッセージを切替
            //PAC_5-2888 iPadから添付ファイルあり文書を開いた時のメッセージをスマホと同じように修正
            async  validatHasAttachment (circular_id) {
              const ret = await this.getAttachment(circular_id);
              if(ret.length > 0){
                const isMobile = navigator.userAgent.match(/iPhone|iPad|Android.+Mobile/);
                const dialogText = isMobile ? "添付されたファイルを確認される際はPC版でご確認ください" : "右上の「添付ファイル」をクリックして文書をダウンロードしてご確認ください";
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
            /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
            onCloudModalClosed:function(){
              this.$modal.hide('cloud-upload-modal');
              this.$store.commit('home/updateCloudBoxFlg',false);
            },
            /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
          showReserveFile(){
            this.confirmDownload = true;
            let pos = this.fileSelected.name.lastIndexOf('.');
            this.inputMaxLength = 50 - this.fileSelected.name.substr(pos).length;
            this.downloadReserveFilename = '';
          },
          //ダウンロード予約
          onDownloadReserve: async function(){
            this.confirmDownload = false;
            let info = {
              reserve_file_name: this.downloadReserveFilename  ?  this.downloadReserveFilename + this.fileSelected.name.substr(this.fileSelected.name.lastIndexOf('.')) : this.fileSelected.name,
              document_id: this.fileSelected.circular_document_id,
              stampHistory: this.addStampHistory,
              addTextHistory: this.addTextHistory,
              finishedDate: this.finishedDate,
              usingTas: false,
            }
            let checkAddUsingTas =  await this.checkShowConfirmAddTimeStamp(this.finishedDate);
            this.$store.commit('home/checkAddUsingTas', checkAddUsingTas);
            if(checkAddUsingTas && this.checkShowConfirmAddSignature){
              this.$vs.dialog({
                type:'confirm',
                color: 'primary',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'いいえ',
                text: `タイムスタンプを付与しますか？`,
                accept: async ()=> {
                  info.usingTas = this.$store.state.home.usingTas;
                  await this.reservePreviewFile(info);
                },
                cancel: async ()=> {
                  await this.$store.commit('home/checkAddUsingTas', false);
                  await this.reservePreviewFile(info);
                },
              });
            }else{
              await this.reservePreviewFile(info);
            }
          },
         },
        watch: {
            "$store.state.reduceButton": function(newVal, oldVal) {
              const $this = this;
              setTimeout(function() {
                $this.calcPdfViewerWidth();
                $this.selectPage($this.currentPageNo);
              },300);
            },
            "$store.state.home.fileSelected": async function(newVal) {
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
                this.histories = await this.getStampInfosForCompleted({circular_document_id: newVal.circular_document_id, finishedDate: this.finishedDate})

                const isMobile = window.innerWidth <= 480;
                if (isMobile) {
                  // 初期表示用画像取得
                  const promises = getPageUtil.getPageImagesForMobile(this.pages, this.getPageImage);
                  const res = await promises[0]; // first page
                  if (res?.ok)  this.selectPage(1);
                }
              }
            },
            "tabSelected": function (newIndex, oldIndex) {
                const newVal = this.files[newIndex];
                if(newVal.confidential_flg && newVal.mst_company_id !== this.loginUser.mst_company_id) {
                  this.tabSelected = oldIndex;
                }
            },
            "$store.state.home.files":function () {
                Utils.buildTabColorAndLogo(this.files, this.companyLogos, this.loginUser.mst_company_id, config.APP_EDITION_FLV, config.APP_SERVER_ENV);
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
        },
        async mounted() {
          document.body.style.overflow = 'hidden';
          const $this = this;
          this.$ls.on('boxAccessToken', (value)=> {
            if(value) {
              $this.filename_upload = $this.fileSelected.name;
              $this.$modal.show('cloud-upload-modal')
              $this.cloudLogo = require('@assets/images/box.svg');
              $this.cloudName = 'Box';
            }
          });
          this.$ls.on('onedriveAccessToken', (value)=> {
            if(value) {
              $this.filename_upload = $this.fileSelected.name;
              $this.$modal.show('cloud-upload-modal')
              $this.cloudLogo = require('@assets/images/onedrive.svg');
              $this.cloudName = 'OneDrive';
            }
          });
          this.$ls.on('googleAccessToken', (value)=> {
            if(value) {
              $this.filename_upload = $this.fileSelected.name;
              $this.$modal.show('cloud-upload-modal')
              $this.cloudLogo = require('@assets/images/google-drive.png');
              $this.cloudName = 'Google Drive';
            }
          });
          this.$ls.on('dropboxAccessToken', (value)=> {
            if(value) {
              $this.filename_upload = $this.fileSelected.name;
              $this.$modal.show('cloud-upload-modal')
              $this.cloudLogo = require('@assets/images/dropbox.svg');
              $this.cloudName = 'Dropbox';
            }
          });
          this.$ls.on('errormessage', (value) => {
            console.log("failed to Cloud Connection");
            if(value){
              this.$vs.notify({color: 'danger',text: message,position: 'bottom-left'});
            }
            this.$ls.remove('errormessage');
          });
          this.calcPdfViewerWidth();

          const handleResize = () => {
              this.calcPdfViewerWidth();
              this.selectPage(this.currentPageNo);
          };

          window.addEventListener("resize", handleResize);
          this.$once('hook:beforeDestroy', () => {
            window.removeEventListener("resize", handleResize);
            });
          /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
          this.$store.commit('home/updateCloudBoxFlg',false);
          /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        },
        async created() {
          this.checkDeviceType();
          this.$store.commit('home/setUsingPublicHash', false);
          /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
          this.$store.commit('home/updateCloudBoxFlg',false);
          /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
          this.startVisibilityWatch();

          this.$watch(
            () => [this.zoom, this.fileSelected],
            (newVal, oldVal) => {
              const [, newFileSelected] = newVal; 
              const [, oldFileSelected] = oldVal;

              const isFileChanged = oldFileSelected != newFileSelected;
              if (!isFileChanged) {
                // ズームのみ変更されたとき, スクロール位置調整
                const currentFirstVisiblePageIndex = this.firstVisiblePageIndex;
                if (currentFirstVisiblePageIndex != -1) {
                  this.$nextTick(() => {
                    this.$refs.pages.jumpTo(currentFirstVisiblePageIndex);
                  });
                }
              }
            }
          );

            const promises = [];
            this.finishedDate = localStorage.getItem('finishedDate') ? localStorage.getItem('finishedDate') : '';
            if (!this.$route.query.back) {
                this.addStampHistory = false;
                this.addTextHistory = false;
                promises.push(this.clearState());
            } else {
                if(this.addStampHistory == true){
                    this.radioVal = "addStampHistory";
                }
                if(this.addTextHistory == true){
                    this.radioVal = "addTextHistory";
                }
            }

            promises.push(
                (async () => {
                    const userInfo = await this.getMyInfo();
                    const cir_info = userInfo.circular_info_first;
                    switch(cir_info){
                      case "回覧先":
                        this.tab_cir_info = 0;
                        break;
                      case "コメント":
                        this.tab_cir_info = 1;
                        break;
                      case "捺印履歴":
                        this.tab_cir_info = 2;
                        break;
                      default:
                        console.log("Error");
                        break;
                    }
                })(),
                (async () => {
                    const id = this.$route.params.id;
                    if (id) {
                        const ret = await this.loadCircularForCompleted({id: id, finishedDate: this.finishedDate});
                        if (!this.$store.state.home.accessCodePopupActive) {
                            if (!ret) this.$router.back();
                        }
                    }
                })(),
                (async () => {
                  const issu_id = this.$route.params.issu_id;
                  this.other_flg = this.$route.params.other;
                  const getReport = this.other_flg == 0 ? this.getDetailReport : this.getDetailReportOther;
                  this.itemReadingDetail = await getReport({id: issu_id, finishedDate: this.finishedDate});
                })(),
                (async () => {
                    this.settingLimit = await this.getLimit();
                    if (this.settingLimit == null) {
                        this.settingLimit = {};
                    }
		    if(this.settingLimit && this.settingLimit.default_stamp_history_flg == 1){
		        this.addStampHistory = true
                        this.radioVal = "addStampHistory";
		    }
                })(),
                (async () => {
                    if (!this.$store.state.home.usingPublicHash) {
                        const company = await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                            .then(response => {
                                return response.data ? response.data.data : [];
                            })
                            .catch(error => {
                                return [];
                            });
                        this.canStoreCircular = company && company.long_term_storage_flg;
                    } else {
                        this.canStoreCircular = false;
                    }
                    //回覧作成者の会社
                    let createCircularCompany  = await Axios.get(`${config.BASE_API_URL}/setting/getCreateCircularCompany?circular_id=${this.$route.params.id}&finishedDate=${this.finishedDate}`)
                        .then(response => {
                          return response.data ? response.data.data: [];
                        })
                        .catch(error => { return []; });
                    this.isShowAttachment = createCircularCompany && createCircularCompany.attachment_flg;
            })(),
            (async () => {
              this.longtermIndex = await this.getLongtermIndex();
            })(),
            );
            await Promise.all(promises);
            this.zoom = 100;
            this.$nextTick(() => {
                let popups = document.getElementsByClassName('vs-component con-vs-popup vs-popup-primary');
                for (let i = 0; i < popups.length; i++) {
                    let div = document.createElement('div');
                    div.style.width = '100%';
                    div.style.height = '100%';
                    div.style.position = 'fixed';
                    div.style.left = 0;
                    div.style.top = 0;
                    div.style.zIndex = 50;
                    popups[i].appendChild(div);
                }
                this.validatHasAttachment(this.circular.id)
            });
            this.is_ipad = /(iPad)/i.test(navigator.userAgent);
        },
        beforeDestroy() {
          // 取得を止めるため
          this.visiblePageRange = [-1, -1];
          this.visibleThumbnailRange = [-1, -1];
          /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
          this.$store.commit('home/updateCloudBoxFlg',false);
          /* PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        },
    }
</script>

<style scoped lang="stylus">
.detail{
    .label{ background: #b3e5fb; padding: 3px; word-break: break-all; }
    .info{  padding: 3px 3px 3px 5px; word-break: break-all; }
}
</style>
<style>
.pre-msg .vs-dialog-text{
    white-space: pre-line;
}
.buttonMain{
  width: 200px;
}
@media (max-width: 1024px) {
  .DivbuttonSmall {
    padding: 0.5rem !important;
  }
  .parentx .v-nav-menu-swipe-area{
    width: 0 !important;
  }
}
@media (max-width: 800px) {
  .buttonMain{
    width: 150px !important;
  }
}
@media (max-width: 750px) {
  .buttonMain{
    width: 100px !important;
  }
}
</style>
