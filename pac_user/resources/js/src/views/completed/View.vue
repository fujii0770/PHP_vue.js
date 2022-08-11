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
                    <li><p style="color: #0984e3;"><span class="badge badge-primary">1</span> プレビュー</p></li>
                    <li><p style="background: transparent"></p></li>
                </ul>
            </vs-col>
			<vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6">
        		<vs-button id="button7" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="goBack"> 戻る</vs-button>
                <vs-button id="button9"  class="square" :style="!isShowAttachment || specialCircularFlg? 'display:none':'color:#000;border:1px solid #dcdcdc;'" color="#fff" type="filled"  :disabled="files.length <= 0" v-on:click="addAttachment"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</vs-button>
                <vs-button v-if="canStoreCircular && settingLimit.sanitizing_flg == 0" class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="onSaveLongtermModal">長期保管</vs-button>
                <vs-dropdown :vs-trigger-click="is_ipad" v-if="(settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox) && (fileSelected && !fileSelected.del_flg)">
                    <vs-button id="button5" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="!settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード</vs-button>
                    <vs-button id="button5_2" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード予約</vs-button>
                    <vs-dropdown-menu >
                        <li class="vx-dropdown--item">
                          <a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="default" @click.native.stop="changeDefaultStatus($event);"  vs-name="radioVal"  :disabled="countAllTabNum" v-model="radioVal"  >完了済みファイル</vs-radio></a>
                        </li>
                        <li class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addStampHistory" @click.native.stop="changeStampHistory($event)"   vs-name="radioVal"    v-model="radioVal"  >回覧履歴を付ける</vs-radio></a></li>
                        <li v-show="settingLimit.is_show_current_company_stamp" class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addTextHistory" @click.native.stop="changeTextHistory($event)"   vs-name="radioVal"  :disabled="countAllTabNum" v-model="radioVal"  >自社のみの回覧履歴を付ける</vs-radio></a></li>
                        <vs-dropdown-item v-if="settingLimit.storage_local && !settingLimit.sanitizing_flg">
                            <vs-button v-on:click="onDownloadFile"    :disabled="countAllTabNum"  color="primary" class="w-full download-item" type="filled"><i class="fas fa-download"></i>  ローカル</vs-button>
                        </vs-dropdown-item>
                        <vs-dropdown-item v-if="settingLimit.sanitizing_flg">
                              <vs-button v-on:click="showReserveFile"    :disabled="countAllTabNum"  color="primary" class="w-full download-item" type="filled"><i class="fas fa-download"></i>ダウンロード予約</vs-button>
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

                                <vs-navbar-item v-for="(file, index) in filesLessThanMaxTabShow" :key="file.circular_document_id" :index="index" :class="'document ' + (file.confidential_flg ? 'is-confidential': (file.hasOwnProperty('tabColor')?file.tabColor:''))">
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
                            :enable="false"
                            :areaSelectMode="(loginUser.checkLongTermFlgAll && settingLimit.long_term_storage_option_flg)&& longtermTextExtractField.index !== null"
                            @area-selected="onPageAreaSelected">
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
                                <vs-tabs v-model="tab_cir_info">
                                    <vs-tab label="回覧先">
                                        <div class="mail-list">
                                            <p><strong>ファイル名</strong></p>
                                            <p class="filename">{{fileSelected ? fileSelected.name : ''}}</p>
                                            <template  v-if="!isTemplateCircular
                                                    && hasPlanCircularUsers.length<=0 && (!specialCircularFlg || (specialCircularReceiveFlg ||  !user.special_site_receive_flg))">
                                              <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + (user.is_send_back === 1 ? ' sendback': '')"
                                                      vs-type="flex" v-for="(user, index) in circularHasReturnUsers" v-bind:key="user.email + index" :index="index">
                                                  <vs-col vs-w="10">
                                                      <p>{{user.name}} <span class="final" v-if="user.is_send_back === 1">差戻し</span></p>
                                                      <p>{{user.email}}</p>
                                                  </vs-col>
                                                  <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                      <p v-if="circularHasReturnUsers && index === (circularHasReturnUsers.length - 1)" href="#" class="final">最終</p>
                                                      <a v-if="user.is_flag === 1" href="#"> <i class="far fa-flag"></i></a>
                                                  </vs-col>
                                              </vs-row>
                                            </template>
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
                                                            <p :key="itemIndex">{{user.name}} 【{{user.email}}】<span class="final" v-if="user.is_send_back === 1">差戻し</span></p>
                                                        </template>
                                                    </vs-col>
                                                    <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                        <p v-if="index === hasPlanCircularUsers.length - 1" href="#" class="final">最終</p>
                                                        <template v-for="(user, itemIndex) in user.plan_users">
                                                            <a v-if="user.is_flag === 1" class="ml-1" href="#" :key="itemIndex"> <i class="far fa-flag"></i></a>
                                                        </template>
                                                    </vs-col>
                                                </template>
                                            </vs-row>
                                            <!--plan users end-->
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
                                              <vs-row class="item sended" vs-type="flex" v-for="(userRoute, index) in templateUserRoutes" :key="index" :index="index">
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
                                            <vs-row v-if="circularUsers && circularUsers.length > 0 && specialCircularFlg && !specialCircularReceiveFlg" class="item unsend" vs-type="flex">
                                                <vs-col vs-w="10">
                                                    <p>{{groupName}}</p>
                                                </vs-col>
                                                <vs-col vs-type="flex" vs-w="2" vs-justify="flex-end" vs-align="center">
                                                    <a v-if="circularUserLastSendIdIsSpecial" href="#" class="mr-2"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                            </vs-row>
                                            <vs-row v-if="circularUsers && circularUsers.length > 0 && !specialCircularFlg" class="item unsend" vs-type="flex">
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
                                    <vs-tab :label="stickyNoteFlg?'コメント/付箋':'コメント'">
                                      <div class="comments comment-height comment-position">
                                        <vs-tabs class="comment-height">
                                          <vs-tab label="社内宛">
                                            <!-- PAC_5-2988 S -->
                                            <vs-row class="item" v-for="(comment, index) in commentsNotPrivate" v-bind:key="comment.name + index" :index="index">
                                            <!-- PAC_5-2988 E -->
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                          </vs-tab>
                                          <vs-tab label="社外宛">
                                            <!-- PAC_5-2988 S -->
                                            <vs-row class="item" v-for="(comment, index) in commentsIsPrivate" v-bind:key="comment.name + index" :index="index">
                                            <!-- PAC_5-2988 E -->
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                          </vs-tab>
                                          <vs-tab label="付箋" class="comment-tab" v-if="stickyNoteFlg">
                                            <StickyNoteFlex :showStickNotes="showStickNotes" ></StickyNoteFlex>
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
                                    <vs-tab label="長期保管インデックス" vs-type="flex" v-if="canStoreCircular && loginUser.checkLongTermFlgAll && settingLimit.long_term_storage_option_flg" class="longterm-modal">
                                      <div id="fields" class="tools fields vs-con-loading__container">
                                        <div class="body mt-1">
                                          <template v-for="(field, index) in indexes" >
                                            <vs-row  v-bind:key="index" :index="index"  style="display: flex;margin-bottom: 10px">
                                              <div  style="width: 75%">
                                                <vs-row   >
                                                  <vs-select class="selectExample w-full" v-model="indexes[index].longterm_index_id"  @change="onChangeExample(indexes[index].longterm_index_id,index)">
                                                    <vs-select-item v-for="long in longtermIndex" v-bind:key="long.id" :value="long.id" :text="long.index_name" />
                                                  </vs-select>
                                                </vs-row>
                                                <vs-row  >
                                                  <vs-input  class="inputx"   v-model="indexes[index].value"  :type="indexes[index].type==='number'?'text':indexes[index].type" @blur="ChangeSetIndexValue(indexes[index],$event)"  @focus="longtermIndexSelectedIndex = index" />
                                                </vs-row>
                                              </div>
                                              <vs-row     class="mb-3 sm:pl-2"  style="width: 25%;height: 90%;align-self: end;position: relative;bottom: -8px">
                                                <vs-button  color="danger"  type="filled"  v-on:click="longtermIndexSelectedIndex=null;removeIndex(index)"> x </vs-button>
                                              </vs-row>
                                            </vs-row>

                                            <!-- one_line_extract -->
                                            <!-- eslint-disable-next-line vue/valid-v-for --> <!-- 1回しか現れないため、 key を固定 -->
                                           <template v-if="field.data_type!==2">
                                             <vs-row v-if="longtermTextExtractField.index === index" :key="index + 'extract'">
                                               <!-- hint -->
                                               <vs-col v-if="longtermTextExtract.hintMessage" type="vs-flex" vs-w="12">
                                                 {{ longtermTextExtract.hintMessage }}
                                               </vs-col>
                                               <!-- progress -->
                                               <vs-progress v-if="longtermTextExtract.processingPromise" indeterminate color="primary"></vs-progress>
                                               <!-- 選択候補表示 -->
                                               <!-- vs-w 12 にすると button の margin-right がはみ出るため 11 -->
                                               <vs-col v-for="(value, i) in longtermTextExtract.choices" type="vs-flex" vs-w="11" :key="i">
                                                 <vs-button type="line" @click="field.value = value;" style="width: 100%;">
                                                   {{ value }}
                                                 </vs-button>
                                               </vs-col>
                                               <vs-col type="flex" vs-w="12" class="mb-3"></vs-col>
                                             </vs-row>
                                           </template>
                                          </template>
                                            <vs-button  @click="addIndex"  color="primary" type="filled">+</vs-button>
                                          <vs-button v-on:click="onSaveLongtermIndex" color="primary" type="filled"> 保存 </vs-button>
                                        </div>
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

        <modal name="add-attachment-modal"
            :pivot-y="0.2"
            :width="700"
            :classes="['v--modal', 'upload-modal']"
            :height="'auto'"
            style="border-radius: 11px"
            :clickToClose="false" >
            <AddAttachmentModalInner
              :attachmentUploads="attachmentUploads"
              @file-click="onDownloadAttachment"
              @close-click="closeAttachmentModal()"
            />
        </modal>

        <vs-popup title="確認" :active.sync="activeSaveLongtermModal">
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
                <FolderTree ref="tree" v-show="showTree" :treeId="selectCompletedTree" @onNodeClick="setFolderId"></FolderTree>
            </div>
            <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2" color="primary" type="filled" v-on:click="onSaveLongTermAccept"> はい</vs-button>
                <vs-button class="square mr-0" color="#bdc3c7" type="filled" v-on:click="activeSaveLongtermModal = false"> キャンセル</vs-button>
            </vs-row>
        </vs-popup>
	</div>

        <!-- 5-277 mobile html -->
        <div id="main-home-mobile" :class="isMobile?'mobile':''">
        <div style="width:100%;" v-if="circularUsers && circularUsers.length > 0">
            <span @click="goBack" class="hidden"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
            <div style="display:inline-block;position:relative;width: 100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{fileSelected ? fileSelected.name : ''}}</div>
        </div>
        <div style="width:100%;height:73px;background-color: #f2f2f2;" v-if="circularUsers && circularUsers.length > 0">From : {{circularUsers && circularUsers.length > 0 ? circularUsers[0].name : ''}}<span style="float: right;">{{circularUsers[0].create_at | moment("YYYY/MM/DD")}}</span></div>

        <div id="handleMailList" v-on:click="handleMailList">
          <div class="icon hide">
            <vs-icon icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
          </div>
          回覧先・コメント
        </div>
        <div id="mail_list_box">
          <div style="position: relative; z-index: 2; display: inline-block; width: 100%;">
              <div id="mail-list-label" class="tab-active" v-on:click="changeTabMail()">回覧先</div>
              <div id="comments-label" class="tab" v-on:click="changeTabComments()">コメント</div>
              <div id="history-label" class="tab" v-on:click="changeTabHistory()">捺印履歴</div>
          </div>

          <!-- Mail step -->
          <div class="mail-list">
              <div :class="'item sended ' + (index === 0 ? ' maker ':'') + (user.is_send_back === 1 ? ' sendback': '')" v-for="(user, index) in circularHasReturnUsers" v-bind:key="user.email + index" :index="index">

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
              <vs-tabs class="comment-height">
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
                      <p class="w-full mr-3">
                      <span class="user-name">{{history.name}}</span> <br/>
                      <span class="date">{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span> <br/>
                      <span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span></p>
                  </vs-col>
              </vs-row>

              <vs-row class="item" v-for="(history, index) in histories.text" v-bind:key="history.email + index + '_text'" :index="index">
                  <vs-col vs-w="3" class="p-2 text-center">
                      テキスト：
                  </vs-col>
                  <vs-col vs-w="9" vs-type="flex" vs-align="center" vs-justify="flex-start">
                      <p class="w-full mr-3">
                          <strong style="word-wrap:break-word; overflow:hidden;display:block;" :title="history.text"  v-html="history.text"></strong>
                          <span class="user-name"  style="word-wrap:break-word; overflow:hidden;" >{{history.name}}</span><br/>
                          <span class="date"  style="word-wrap:break-word; overflow:hidden;" >{{history.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span>
                          <br/><span  style="word-wrap:break-word; overflow:hidden;display:block;" >{{ history.email }}</span>
                      </p>
                  </vs-col>
              </vs-row>
              <vs-row  v-if="histories.text" :style="`margin-top:5px`"></vs-row>
          </div>


        </div>



        <vs-card class="work-content">
            <vs-row v-if="mobilePages.length > 0">
                <vs-col vs-type="flex">
                    <div class="preview-list">
                      <div style="text-align: center;margin-bottom:10px;">
                        <div style="text-align: center;margin-bottom:10px;">{{pages.length}}件の文書があります</div>
                      </div>
                      <div class="tabSelected">
                        <div>
                          <select v-model="tabSelected" @change="changeSelectedFile(tabSelected)">
                            <option v-for="(file, index) in files" :value="index" :key="index" :selected="(index==0)?'selected':''">{{ file.name }}</option>
                          </select>
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="#0984e3" d="M311.9 335.1l-132.4 136.8C174.1 477.3 167.1 480 160 480c-7.055 0-14.12-2.702-19.47-8.109l-132.4-136.8C-9.229 317.8 3.055 288 27.66 288h264.7C316.9 288 329.2 317.8 311.9 335.1z"/></svg>
                        </div>
                      </div>
                      <div style="text-align: center; height:27px;">
                        <div style="display:inline-block;float:left;" @click="changePage(currentPageNo-1)">
                          <vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon>
                        </div>
                        <div style="display:inline-block;position:relative;top:10px;">
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
                <vs-col vs-type="flex" style="transition: width .2s;">
                    <div class="pdf-content">
                        <div v-show="fileSelected != null" class="content">
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
    import _ from "lodash"
    import draggable from 'vuedraggable'
    import utils from "../../utils/utils";
    import {cloneDeep} from "lodash/lang";
    import FolderTree from '../../components/long_term/FolderTree';
    import CloudUploadModalInner from "@/components/home/CloudUploadModalInner.vue"
    import UpdateCheckDocModalInner from "@/components/home/UpdateCheckDocModalInner";
    import BizcardPopUpInner from "@/components/home/BizcardPopUpInner.vue"
    import DownloadReservePopUpInner from "@/components/home/DownloadReservePopUpInner.vue"
    import AddAttachmentModalInner from "@/components/home/AddAttachmentModalInner.vue";
    import StickyNoteFlex from "../../components/stick-note/StickyNoteFlex";

    const PPI = 150;

    export default {
        components: {
            InfiniteLoading,
            PdfPages,
            PdfPageThumbnails,
            draggable,
            FolderTree,
            CloudUploadModalInner,
            UpdateCheckDocModalInner,
            BizcardPopUpInner,
            DownloadReservePopUpInner,
            AddAttachmentModalInner,
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
                tabSelected: 0,
                stampToolbarActive: true,
                isValidAccessCode: true,
                settingLimit:{},
                filename_upload: '',
                cloudFileItems: [],
                breadcrumbItems: [],
                cloudLogo: null,
                cloudName: null,
                currentCloudFolderId: 0,
                checkShowConfirmAddSignature: JSON.parse(getLS('user')).check_add_signature_time_stamp,
                canStoreCircular:false,
                activeSaveLongtermModal: false,
                keywords: '',
                checkKeywordsLenFlg: false,
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
                saveId: null,//上書きファイルID
                is_ipad: false,
                viewerWidth: 1,
                thumbnailViewerWidth: 0,
                visiblePageRange: [-1, -1],
                visibleThumbnailRange: [-1, -1],
                longtermIndex: [],
                finishedDate: 0,
                attachmentUploads : [],
                isShowAttachment:true,
                longtermIndexSelectedIndex: null, // 入力中の longtermIndex 要素の index
                longtermTextExtract: {
                  processingPromise: null,
                  hintMessage: null,
                  choices: [],
                },
                keywords_flg: null,
                optionFlg : JSON.parse(getLS('user')).option_flg,
                indexes : [],
                specialCircularReceiveFlg: false,//回覧ユーザーが特設サイトの受取側ですか
                circularUserLastSendIdIsSpecial: false,//現在未操作のユーザは特設サイトの受取側ですか
                specialCircularFlg:false,//特設サイト回覧
                specialButtonDisableFlg: false,//特設サイト申請画面、ボタン非アクティブ
                groupName: '',//特設サイト受取側組織名
                isMobile: false,
                showTree: false,
                folderId: '',
                folderSelect: false,
                selectCompletedTree: 'selectCompletedTree',
                showFolderFlg:false,
                radioVal: "default",
                countAllTabNum: false,
                confirmDownload: false,//選択文書ダウンロード予約 画面フラグ
                downloadReserveFilename: '',//ダウンロード予約のファイル名
                inputMaxLength: 46,//ファイル名の長さ
                showStickNotes:[],
                stickyNoteFlg: JSON.parse(getLS('user')).sticky_note_flg,
            }
        },
        computed: {
            ...mapState({
                title: state => state.home.title,
                files: state => state.home.files,
                fileSelected: state => state.home.fileSelected,
                circular: state => state.home.circular,
                currentViewingUser: state => state.home.currentViewingUser,
                addStampHistory: state => state.home.addStampHistory,
                companyLogos: state => state.home.company_logos,
                addTextHistory: state => state.home.addTextHistory,
                tempComments: state => state.home.tempComments, //社内社外宛先一時入力コメント
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
            filesLessThanMaxTabShow () {
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
                            if (config.APP_SERVER_ENV == item.env_flg && config.APP_SERVER_FLG == item.server_flg && this.loginUser.mst_company_id === item.mst_company_id && item.return_flg === 1 && ((item.child_send_order === 0 && item.parent_send_order === 0) || (item.child_send_order === 1 && item.parent_send_order > 0))) {
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
            longtermTextExtractField() {
              // 一行抽出をするフィールドの情報
              const asDisabled = {
                index: 0,
                choicesProcessor: null,
              };
              const index = this.longtermIndexSelectedIndex || 0;
              if (!this.indexes[index]) {
                return asDisabled;
              }
              const LONGTERM_INDEX_TYPE_NUMERIC = 0;
              const LONGTERM_INDEX_TYPE_STRING = 1;

              const choicesProcessors = {
                [LONGTERM_INDEX_TYPE_NUMERIC]: (choices) => {
                  const toAsciiDigits = (str) => {
                    const diff = "０".charCodeAt(0) - "0".charCodeAt(0);
                    return str.replace(/[０-９]/g, match => String.fromCharCode(match.charCodeAt(0) - diff));
                  };

                  const numericChoices = choices.map(x => {
                    const str = toAsciiDigits(x).replace(/,/g, "");
                    const numericMatch = str.match(/-?(?:\d+(?:\.\d+)?|\.\d+)/);
                    return numericMatch?.[0];
                  }).filter(Boolean);
                  return [...new Set(numericChoices)]; // 重複排除
                },
                [LONGTERM_INDEX_TYPE_STRING]: (choices) => choices,
              };

              const indexItem = this.indexes[index];
              const choicesProcessor = choicesProcessors[indexItem.data_type];
              const canExtract = choicesProcessor !== undefined;
              return canExtract ? {
                index,
                choicesProcessor,
              } : asDisabled;
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
                            if (config.APP_SERVER_ENV == item.env_flg && config.APP_SERVER_FLG == item.server_flg && this.loginUser.mst_company_id === item.mst_company_id && item.return_flg === 1 && ((item.child_send_order === 0 && item.parent_send_order === 0) || (item.child_send_order === 1 && item.parent_send_order > 0))) {
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
          isShowButton() {
            return this.title.trim().length>0
          }
        },
        methods: {
            ...mapActions({
                getPage: "home/getPage",
                extractPdfLine: "home/extractPdfLine",
                clearState: "home/clearState",
                selectFile: "home/selectFile",
                updateCurrentFileZoom: "home/updateCurrentFileZoom",
                changePositionFile: "home/changePositionFile",
                loadCircularForCompleted: "home/loadCircularForCompleted",
                getStampInfosForCompleted: "home/getStampInfosForCompleted",
                getBizcardById: "bizcard/getBizcardById",
                downloadSendFile: "home/downloadSendFile",
                getLimit: "setting/getLimit",
                getCloudItems: "cloud/getItems",
                uploadToCloud: "home/uploadToCloud",
                getMyInfo: "user/getMyInfo",
                storeCircular: "circulars/storeCircular",
                checkShowConfirmAddTimeStamp: "home/checkShowConfirmAddTimeStamp",
                addLogOperation: "logOperation/addLog",
                checkDeviceType: "home/checkDeviceType",
                getLongtermIndex: "circulars/getLongtermIndexOption",
                setLongtermIndex: "circulars/setLongtermIndex",
                getAttachment: "home/getAttachment",
                downloadAttachment:"home/downloadAttachment",
                getTermIndexValue:"circulars/getTermIndexValue",
                getMyFolders: "circulars/getMyFolders",
                reservePreviewFile: "home/reservePreviewFile",
                reserveAttachment: "home/reserveAttachment",
            }),
            goToMemo: async function () {
                localStorage.setItem('finishedDate', this.finishedDate);
                this.$router.push('/completed/' + this.circular.id + '/memo');
            },
            onZoomOutClick: function () {
                this.zoom = parseInt(this.zoom);
                this.zoom = Math.max(50, this.zoom - 10);
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
              this.$router.push(`/completed`);
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
                this.addLogOperation({ action: action, result: result ? 0 : 1, params:{filename: this.fileSelected.name}});
            },
            async onDownloadFile(){
              var checkAddUsingTas =  await this.checkShowConfirmAddTimeStamp(this.finishedDate);
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
            },
            onFileTabClick: function(file, index) {
              if(file.confidential_flg && file.mst_company_id !== this.loginUser.mst_company_id) return;
              if(this.fileSelected && file.circular_document_id === this.fileSelected.circular_document_id) return;

              if(index >= this.maxTabShow) {
                this.changePositionFile({from: index, to: 0});
              }

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
                const num = document.body.clientWidth < 1200 ? 1 : 0
                this.lockStampToolbarActive.apply(this,[num]);
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
            this.$store.commit('home/updateCloudBoxFlg',true);
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
            var checkAddUsingTas =  await this.checkShowConfirmAddTimeStamp(this.finishedDate);
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
            }else{
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
            //PAC_5-1368
            if(this.saveId){
                data["file_id"] = this.saveId;
            }

            const uploadRet = await this.uploadToCloud(data);
            if(uploadRet) {
              this.onCloudModalClosed();//PAC_5-3116
            }
          },
            onSaveLongtermModal: function() {
              this.showTree = false;
              this.folderSelect = false;
              this.checkKeywordsLenFlg = false;
              Axios.get(`${config.BASE_API_URL}/long-term/${this.circular.id}`)
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
              .catch(() => {
                 this.activeSaveLongtermModal = true;
              });
            },
            onSaveLongTermAccept: async function() {
              if(this.showTree && this.folderId == '') {
                  this.activeSaveLongtermModal = true;
                  this.folderSelect = true;
              }else {
                if(this.keywords && this.keywords.length > 200){
                  this.activeSaveLongtermModal = true;
                  this.checkKeywordsLenFlg = true;
                }else{
                  if (this.circular){
                    if(this.keywords == ''){
                      this.keywords_flg = 0;
                      await this.storeCircular({id: this.circular.id, keyword: this.keywords, finishedDate: this.finishedDate, keyword_flg: this.keywords_flg,folderId: this.folderId}).then(() => {
                        this.$route.meta.isKeep=true
                        this.$router.back();
                        this.activeSaveLongtermModal = false;
                      });
                    }else{
                      await this.storeCircular({id: this.circular.id, keyword: this.keywords, finishedDate: this.finishedDate,folderId: this.folderId}).then(() => {
                        this.$route.meta.isKeep=true
                        this.$router.back();
                        this.activeSaveLongtermModal = false;
                      });
                    }
                  }
                }
              }
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
            onSaveLongtermIndex: async function () {
                this.longtermIndexSelectedIndex = null;

                let data = {
                    cid: this.circular.id,
                    indexes: this.indexes,
                    finishedDate: this.finishedDate
                };
                let flg = true;
                let numberflg = true;
                let errArr = [];
                for (let v in this.indexes) {
                    if ((this.indexes[v].data_type === 1)) {
                        if (this.indexes[v].value && this.indexes[v].value.length > 128) {
                            errArr.push("長期保管インデックス文字列の長さは128ビット以上に設定できません。")
                            continue;
                        }
                    }
                    if (this.indexes[v].data_type === 0 && this.indexes[v].value) {
                        let re = /^\d+(\,\d+)*(\.\d+)?$/;
                        let tempNum = this.indexes[v].value.split(",").join('')

                        if(9999999999 < tempNum){
                            errArr.push("数字型は十億以上に設定できません。")
                            continue;
                        }
                        if(this.indexes[v].value &&  !re.exec(this.indexes[v].value)){
                            errArr.push("数字型の長期保管インデックスは数字に設定してください。")
                            continue;
                        }
                    }
                    if(this.indexes[v].data_type === 2 && this.indexes[v].value &&  !(isNaN(this.indexes[v].value)&&!isNaN(Date.parse(this.indexes[v].value)))){
                        errArr.push("日付型の長期保管インデックスは正しい日付に設定してください。！")
                        continue;
                    }

                    this.indexes[v][this.indexes[v].index_name] = this.indexes[v].value
                }

                if(errArr.length > 0){
                    errArr.forEach(item =>this.$store.dispatch("alertError", item, {root: true}))
                }else{
                    await this.setLongtermIndex(data);
                    await this.getLongTermIndexValue()
                }
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
            addAttachment: async function () {
                this.$modal.show('add-attachment-modal');
                if (!this.circular || !this.circular.users) {
                    return null;
                }
                if (this.attachmentUploads.length <= 0) {
                    this.attachmentUploads = [];
                    let ret = await this.getAttachment(this.circular.id);
                    ret.forEach((item,value)=>{
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
                cancel: async ()=> {
                  return null;
                },
              });
            },
            closeAttachmentModal: function() {
                this.$modal.hide('add-attachment-modal');
            },
            //PAC_5-2142 モバイルとPCでメッセージを切替
            async  validatHasAttachment(circular_id){
                let ret = await this.getAttachment(circular_id);
                if(ret.length > 0){
                  const dialogText =  this.isMobile ? "添付されたファイルを確認される際はPC版でご確認ください" : "右上の「添付ファイル」をクリックして文書をダウンロードしてご確認ください"
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
            onPageAreaSelected(event) {
              const {index, points} = event;

              const clampPoints = (points, imageSize) => {
                // 四捨五入し、0 ~ 画像の端に収める
                const values = [points.x1, points.x2, points.y1, points.y2].map(x => Math.round(x));

                const x = values.slice(0, 2).map(x => Math.min(Math.max(0, x), imageSize.width - 1));
                const y = values.slice(2, 4).map(x => Math.min(Math.max(0, x), imageSize.height - 1));

                return {
                  x1: x[0],
                  y1: y[0],
                  x2: x[1],
                  y2: y[1],
                }
              };

              this.execLongtermTextExtract({
                filename: this.fileSelected.server_file_name,
                page: index + 1,
                ppi: PPI,
                ...clampPoints(points, this.expectedPagesSize[index]),
              });
            },
            execLongtermTextExtract(params) {
              const setProcessing = (promise) => {
                const extract = this.longtermTextExtract;
                extract.processingPromise = promise;
                extract.hintMessage = null;
                extract.choices = [];
              };
              const releaseProcessingPromiseIfEquals = (promise) => {
                const extract = this.longtermTextExtract;
                const doRelease = extract.processingPromise == promise;
                if (doRelease) {
                  extract.processingPromise = null;
                }
                return doRelease;
              };
              const setResult = (choices, hintMessage) => {
                const extract = this.longtermTextExtract;
                extract.hintMessage = hintMessage;
                extract.choices = choices;
              };

              const processResponse = (({choices: strChoices, isMultilineDetected}) => {
                const processedChoices = this.longtermTextExtractField.choicesProcessor(strChoices);
                const hintMessage = (() => {
                  if (isMultilineDetected) {
                      return "複数行が検出されました。選択をやり直すと正しく認識される場合があります。";
                  }
                  if (processedChoices.length == 0) {
                      return "有効な文字列が検出されませんでした。範囲を狭めたり広めたりしてみてください。";
                  }

                  return "範囲を狭めたり広めたりすると、正しく認識される場合があります。";
                })();

                return [processedChoices, hintMessage];
              });
              const resultPromise = this.extractPdfLine(params);
              setProcessing(resultPromise);

              resultPromise.then(
                (data) => data,
                () => null,
              ).then((responseData) => {
                const isError = responseData === null;

                const isProcessing = releaseProcessingPromiseIfEquals(resultPromise);
                if (!isProcessing) {
                  // この結果は無視する
                  return;
                }

                const whenError = [[], "エラーが発生しました"];
                const [choices, hintMessage] = isError ? whenError : processResponse(responseData);

                setResult(choices, hintMessage);
              });
            },
            //PAC_5-2359 add item
            addIndex() {
                let index = {longterm_index_id: '', value: "", type: "",index_name:'',data_type:''};
                this.indexes.push(index);
            },
            //PAC_5-2359 remove item
            removeIndex(index) {
                this.indexes.splice(index,1);
            },
            onChangeExample: function (longterm_index_id,index) {
                for(var long in this.longtermIndex) {
                    if (this.longtermIndex[long].id == longterm_index_id) {
                        this.indexes[index].type = this.longtermIndex[long].vsInputType;
                        this.indexes[index].data_type = this.longtermIndex[long].data_type;
                        this.indexes[index].index_name=this.longtermIndex[long].index_name;
                        if(this.indexes[index].value && this.indexes[index].data_type===0){
                          this.indexes[index].value =Utils.filterNum(this.indexes[index].value)
                        }
                        if(this.indexes[index].type == 1){
                            this.indexes[index].value = '';
                        }
                    }
                }
            },
            // pac_5-2359 get field
            async  getLongTermIndexValue(){
                let tmp= await this.getTermIndexValue(this.$route.params.id);
                if(tmp.length>0){
                    const fields = Object.values(tmp);
                    for (const field of fields) {
                        field.type = ["number", "text", "date"][field.data_type];
                        if(field.data_type===0){
                            field.value=Utils.filterNum(field.num_value)
                        }else if(field.data_type===1){
                            field.value=field.string_value
                        }else if(field.data_type===2){
                            field.value=field.date_value
                        }
                        delete field.id
                    }
                  this.indexes=tmp
                }else {
                  this.getLongTermIndexName();
                }

            },
          ChangeSetIndexValue(item,e){
              if(item.data_type===0){
                item.value=Utils.filterNum(e.target.value)
              }
          },
          //2640
           getLongTermIndexName(){
            const newArr=JSON.parse(JSON.stringify(this.longtermIndex));
            const indexTmp=["取引年月日","金額","取引先"]
            const index1=[]
            newArr.forEach((item)=>{
              if(indexTmp.includes(item.index_name)){
                index1.push({longterm_index_id: item.id, value: "", type:item.vsInputType,index_name:'',data_type:item.data_type})
              }
            })
            this.indexes=index1;

          },
          setFolderId(id){
            this.folderId = id;
          },
          handleMailList() {
            $('#handleMailList .icon').stop().toggleClass('hide');
            $('#mail_list_box').stop().toggle(300);
          },
          onCloudModalClosed:function(){
            this.$modal.hide('cloud-upload-modal');
            this.$store.commit('home/updateCloudBoxFlg',false);
          },

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
                  info.usingTas = true;
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
                this.histories = await this.getStampInfosForCompleted({circular_document_id: newVal.circular_document_id, finishedDate: this.finishedDate})

                if (this.isMobile) {
                  // 初期表示用画像取得
                  const promises = getPageUtil.getPageImagesForMobile(this.pages, this.getPageImage);

                  const res = await promises[0]; // first page
                  if (res?.ok) {
                    this.selectPage(1);
                  }
                }
              }
              if(newVal){
                let showStickyNoteTmp = [];
                let value = newVal.sticky_notes
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
            "tabSelected": function (newIndex, oldIndex) {
                const newVal = this.files[newIndex];
                if(newVal.confidential_flg && newVal.mst_company_id !== this.loginUser.mst_company_id) {
                  this.tabSelected = oldIndex;
                }
            },
            "$store.state.home.files":function () {
                Utils.buildTabColorAndLogo(this.files, this.companyLogos, this.loginUser.mst_company_id, config.APP_EDITION_FLV, config.APP_SERVER_ENV);
            },
            longtermTextExtractField() {
              // 初期状態にする
              const extract = this.longtermTextExtract;
              extract.hintMessage = "プレビューからの範囲選択でも入力できます。一行に限ります。";
              extract.processingPromise = null;
              extract.choices = [];
            },
            tab_cir_info() {
              this.longtermIndexSelectedIndex = null;
            },
          "$store.state.home.cloudBoxFlg":function (value){
            document.body.style.setProperty('overflow-y', value ? 'hidden' : 'auto','important');
          },

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
           // PAC_5-1136 クラウドストレージとの連携失敗時にエラーメッセージを表示
        this.$ls.on('errormessage', (value) =>{
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
          this.$store.commit('home/updateCloudBoxFlg',false);
        },
        async created() {
          // Check Mobile
          if (
            /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
              navigator.userAgent
            )
          ) {
            this.isMobile = true
          }

          this.checkDeviceType();
          this.$store.commit('home/updateCloudBoxFlg',false);
          this.$store.commit('home/setUsingPublicHash', false);
          this.$store.state.home.title = '';
          this.startVisibilityWatch();

          this.$watch(
            () => [this.zoom, this.fileSelected],
            (newVal, oldVal) => {
              const [, newFileSelected] = newVal;
              const [, oldFileSelected] = oldVal;

              const isFileChanged = oldFileSelected != newFileSelected;
              if (!isFileChanged) {
                // ズームのみ変更されたとき
                // スクロール位置調整
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
            }else{
                if(this.addStampHistory == true){
                    this.radioVal = "addStampHistory";
                }
                if(this.addTextHistory == true){
                    this.radioVal = "addTextHistory";
                }
            }

            promises.push(
                (async () => {
                    var userInfo = await this.getMyInfo();

                    var cir_info = userInfo.circular_info_first;
                    if (cir_info === "回覧先") {
                        this.tab_cir_info = 0;
                    } else if (cir_info === "コメント") {
                        this.tab_cir_info = 1;
                    } else if (cir_info === "捺印履歴") {
                        this.tab_cir_info = 2;
                    } else {
                        console.log("Error");
                    }
                })(),
                (async () => {
                    const id = this.$route.params.id;
                    if (id) {
                        const ret = await this.loadCircularForCompleted({id: id, finishedDate: this.finishedDate});
                        this.specialCircularFlg = this.circular && this.circular.special_site_flg;
                        this.groupName = this.circular.special_site_group_name;
                        if (!this.$store.state.home.accessCodePopupActive) {
                            let redirectBack = false;
                            if (!ret) {
                              redirectBack = true;
                            } else if (!this.circular || this.circular.circular_status === CIRCULAR.SAVING_STATUS || this.circular.circular_status === CIRCULAR.RETRACTION_STATUS) {
                              redirectBack = true;
                            }
                            if (redirectBack){
                              let userInfo = await this.getMyInfo();
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
                })(),
                (async () => {
                    this.settingLimit = await this.getLimit();
                    if (this.settingLimit == null) {
                        this.settingLimit = {};
                    }
                    if(this.settingLimit && this.settingLimit.default_stamp_history_flg == 1 && !this.$route.query.back){
                            this.addStampHistory = true
                            this.radioVal = "addStampHistory";
                    }
                })(),
                (async () => {
                    if (!this.$store.state.home.usingPublicHash) {
                        var company = await Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                            .then(response => {
                                return response.data ? response.data.data : [];
                            })
                            .catch(error => {
                                return [];
                            });
                      if (this.optionFlg == 2){
                        this.canStoreCircular = false;
                      }else {
                        this.canStoreCircular = company && company.long_term_storage_flg;
                        this.showFolderFlg = company && company.long_term_folder_flg;
                      }
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
                  const longtermIndex = await this.getLongtermIndex(this.$route.params.id);
                  const fields = Object.values(longtermIndex); // workaround: array の場合とそうでない場合の両方に対応するため
                  for (const field of fields) {
                    field.vsInputType = ["number", "text", "date"][field.data_type];
                    if(field.data_type===0){
                      field.value=Utils.filterNum(field.num_value)
                    }else if(field.data_type===1){
                      field.value=field.string_value
                    }else if(field.data_type===2){
                      field.value=field.date_value
                    }
                  }
                  this.longtermIndex = longtermIndex;
                })(),
            );
            await Promise.all(promises);
            await this.getLongTermIndexValue();
            this.zoom = 100;
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
            }

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
          this.$store.commit('home/updateCloudBoxFlg',false);
        },
    }
</script>
<style scoped>

.tools{
    overflow-y: auto;
}
 .square-text >>> .vs-button--text{
    text-align: center;
    left: -3px;
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
.pre-msg .vs-dialog-text{
    white-space: pre-line;
}

input{
  transform: scale(1) !important;
}

body{
  overflow-y: auto !important;
}

#main-home.mobile{
  display: none;
}

#main-home-mobile.mobile{
  position:  relative;
  display: block!important;
  padding: 0 1.2rem;
  overflow: hidden;

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

  .pdf-content{
    margin-top: 10px;
    max-width: 100%;

    select{
      max-width: 100%;
      border-radius: 5px;
      padding: 0.25rem 0.75rem;
      max-width: 100%;
    }
  }

  .preview-list {
    padding:  5px 0px !important;
    width: 100%;
    background: #f2f2f2 !important;
    height: auto !important;

    .tabSelected{
      padding: 8px 0;
      position: relative;
      background: #fff;

      div{
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
        background: #fff;
        border-color: rgba(0, 0, 0, 0.2);
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
    .btn_selected {
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

  .tools {
    .tab, .tab-active{
      width: 50%;
      height: 60px;
      line-height: 60px;
      text-align: center;
      border: 1px solid #dcdcdc;
      display: block;
      float: left;
      margin-bottom: 20px;
    }
    .tab {
      background: #f2f2f2;
      color: #dcdcdc;
    }
    .tab-active{
      background: #fff;
      border-bottom: 0 solid #fff;
    }
    .mail-list {
      background: #fff;
      border: 1px solid #dcdcdc;
      .item{
        margin-bottom: 20px;
        position: relative;

        .mail_list_info{
          display: inline-block;
          position: relative;

          .mail_flag{
            position: absolute;
            left: -30px;
            top: 0;
          }

          .mail_flag_final{
            position: absolute;
            right: -50px;
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
          left: calc( 8.3% + 30px );
          top: -20px;
          width: 15px;
          height: 20px;
          background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIuMTcxIDUxMi4xNzEiIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiBjbGFzcz0iaG92ZXJlZC1wYXRocyI+PHBhdGggZD0iTTQ3OS4wNDYgMjgzLjkyNWMtMS42NjQtMy45ODktNS41NDctNi41OTItOS44NTYtNi41OTJIMzUyLjMwNVYxMC42NjdDMzUyLjMwNSA0Ljc3OSAzNDcuNTI2IDAgMzQxLjYzOCAwSDE3MC45NzFjLTUuODg4IDAtMTAuNjY3IDQuNzc5LTEwLjY2NyAxMC42Njd2MjY2LjY2N0g0Mi45NzFhMTAuNzAyIDEwLjcwMiAwIDAwLTkuODU2IDYuNTcxYy0xLjY0MyAzLjk4OS0uNzQ3IDguNTc2IDIuMzA0IDExLjYyN2wyMTIuOCAyMTMuNTA0YzIuMDA1IDIuMDA1IDQuNzE1IDMuMTM2IDcuNTUyIDMuMTM2czUuNTQ3LTEuMTMxIDcuNTUyLTMuMTE1bDIxMy40MTktMjEzLjUwNGExMC42NDUgMTAuNjQ1IDAgMDAyLjMwNC0xMS42Mjh6IiBkYXRhLW9yaWdpbmFsPSIjMDAwMDAwIiBjbGFzcz0iaG92ZXJlZC1wYXRoIGFjdGl2ZS1wYXRoIiBkYXRhLW9sZF9jb2xvcj0iIzAwMDAwMCIgZmlsbD0iIzA5ODRlMyIvPjwvc3ZnPg==);
          background-size: contain;
          background-position: 50%;
          background-repeat: no-repeat;
        }
      }
      p {
        position: relative;
      }
      .final {
        position: absolute;
        right: 0;
        top: 0;
        color: #0a84e3;
        border: 1px solid #0a84e3;
        border-radius: 5px 5px 5px 5px;
        margin:0px 0 0 10px;
      }
    }
    .comments {
      background: #fff;
      border: 1px solid #dcdcdc;
      border-top-width: 0;
      min-height: 20px;
      padding: 10px 0;
      margin-top: -5px;
    }
  }
}

@media ( min-width: 601px ){
#main-home-mobile.mobile{
  top: -113px;

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

  .preview-list-page{
    width: 100%;
  }
}
}
@media ( max-width: 600px ){
  #main-home-mobile.mobile {
    top: -160px;
    .btn_dialog{
      width: 90%;
      left: 5%;
    }
  }
}
.vs-button:not(.vs-radius):not(.includeIconOnly):not(.small):not(.large){
  padding: 0.65rem 2rem
}
@media (max-height: 800px){
  #main-home .vs-button:not(.vs-radius):not(.includeIconOnly):not(.small):not(.large) {
    padding: 0.65rem 0.5rem
  }
}
.vs-tabs--li{width:25%}
@media (max-width: 1024px) {
  .DivbuttonSmall {
    padding: 0.5rem !important;
  }
  .parentx{
    .v-nav-menu-swipe-area{
      width: 0 !important;
    }
  }
}
</style>
