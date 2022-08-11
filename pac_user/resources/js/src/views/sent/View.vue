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
                    <li><p style="color: #0984e3;"><span class="badge badge-primary">1</span> プレビュー・捺印</p></li>
                    <li><p><span class="badge badge-default">2</span> 回覧先設定</p></li>
                    <li><p style="background: transparent"></p></li>
                </ul>
            </vs-col>
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="6">
                <template  v-if="currentCompanySkipFlg == 1 && isShowSkipFlg == true && circularUsers[0].email == loginUser.email && settingLimit.limit_skip_flg == 1 " >
                  <vs-td>
                    <vs-button class="square" style="width: 122px;"  color="primary" v-on:click="goSkipRoute">スキップ</vs-button>
                  </vs-td>
                </template>
                <template v-if="itemReqSendBack.showBtnRequestSendBack">
                  <vs-td>
                    <vs-button class="square" style="width: 132px;" color="primary" v-on:click="confirmReqSendBack = true">差戻し依頼</vs-button>
                  </vs-td>
                </template>
                <template v-else-if="itemReqSendBack.showBtnBack">
                  <vs-td>
                    <vs-button class="square" style="width: 112px;"  color="primary" v-on:click="confirmPullBack = true">引戻し</vs-button>
                  </vs-td>
                </template>
                <vs-button id="button7" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-on:click="goBack"> 戻る</vs-button>
                <vs-button id="button9"  class="square" :style="!isShowAttachment || specialCircularFlg? 'display:none':'color:#000;border:1px solid #dcdcdc;'" color="#fff" type="filled"  :disabled="files.length <= 0" v-on:click="addAttachment"><i class="fa fa-paperclip" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>添付ファイル</vs-button>
                <vs-dropdown :vs-trigger-click="is_ipad" v-if="(settingLimit.storage_local || settingLimit.storage_box||settingLimit.storage_onedrive||settingLimit.storage_google||settingLimit.storage_dropbox)  && fileSelected && !fileSelected.del_flg">
                    <vs-button id="button5" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="!settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード</vs-button>
                    <vs-button id="button5_2" class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" v-if="settingLimit.sanitizing_flg"><span><img class="download-icon" :src="require('@assets/images/pages/home/download.svg')" style="width: 17.5px;height: 14px;margin-right: 5px;"></span> ダウンロード予約</vs-button>
                    <vs-dropdown-menu >
                        <li class="vx-dropdown--item">
                          <a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="default" @click.native.stop="changeDefaultStatus($event);"  vs-name="radioVal"  v-model="radioVal"  :disabled="countAllTabNum" >完了済みファイル</vs-radio></a>
                        </li>
                        <li class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addStampHistory" @click.native.stop="changeStampHistory($event)"   vs-name="radioVal"    v-model="radioVal"  >回覧履歴を付ける</vs-radio></a></li>
                        <li v-show="settingLimit.is_show_current_company_stamp" class="vx-dropdown--item"><a class="vx-dropdown--item-link"><vs-radio class="mb-2 mt-2" vs-value="addTextHistory" @click.native.stop="changeTextHistory($event)"   vs-name="radioVal"    v-model="radioVal"  :disabled="countAllTabNum">自社のみの回覧履歴を付ける</vs-radio></a></li>
                        <vs-dropdown-item v-if="settingLimit.storage_local && !settingLimit.sanitizing_flg">
                            <vs-button v-on:click="onDownloadFile" color="primary" class="w-full download-item" type="filled" :disabled="countAllTabNum" ><i class="fas fa-download"></i>  ローカル</vs-button>
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

                                <vs-navbar-item v-for="(file, index) in files" :key="file.circular_document_id" :index="index" :class="'document ' + (file.confidential_flg ? 'is-confidential': (file.hasOwnProperty('tabColor')?file.tabColor:''))">
                                    <template>
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
                                <vs-tabs v-model="tab_cir_info">
                                    <vs-tab label="回覧先">
                                        <div class="mail-list">
                                            <p><strong>ファイル名</strong></p>
                                            <p class="filename">{{fileSelected ? fileSelected.name : ''}}</p>
                                            <template v-if="!isTemplateCircular">
                                              <vs-row :class="'item sended ' + (index === 0 ? ' maker ':'') + (user.is_send_back === 1 ? ' sendback': '')"
                                                      vs-type="flex" v-for="(user, index) in showCircularHasReturnUsers" v-bind:key="user.email + index" :index="index"
                                                    >
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
                                            <vs-row class="item sended maker" v-if="isTemplateCircular">
                                                <vs-col vs-w="10">
                                                    <p>{{circularUsers[0].name}} <span class="final" v-if="circularUsers[0].circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                    <p>{{circularUsers[0].email}}</p>
                                                </vs-col>
                                                <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                    <p v-if="1 === circularUsers.length" href="#" class="final">最終</p>
                                                    <a v-if="circularUserLastSendId == circularUsers[0].id" class="ml-1" href="#"> <i class="far fa-flag"></i></a>
                                                </vs-col>
                                            </vs-row>
                                            <template v-if="isTemplateCircular">
                                              <vs-row class="item sended" vs-type="flex" v-for="(userRoute, index) in templateUserRoutes" :index="index" :key="index">
                                                  <vs-col vs-w="10" :class="(circularUserSendBack && userRoute.find(item => item.id == circularUserSendBack.id)) ? ' sendback': ''">
                                                      <p>{{userRoute[0].user_routes_name}}</p>
                                                      <template v-for="(user, itemIndex) in userRoute">
                                                          <p :key="itemIndex + user.name">{{user.name}} 【{{user.email}}】<span class="final" v-if="user.circular_status === CIRCULAR_USER.SEND_BACK_STATUS">差戻し</span></p>
                                                      </template>
                                                  </vs-col>
                                                  <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                      <p v-if="index === templateUserRoutes.length - 1" href="#" class="final">最終</p>
                                                      <template v-for="(user, itemIndex) in userRoute">
                                                          <a v-if="circularUserLastSendId == user.id" class="ml-1" href="#" :key="itemIndex + user.email"> <i class="far fa-flag"></i></a>
                                                      </template>
                                                  </vs-col>
                                              </vs-row>
                                            </template>
                                            <!-- template route users end -->
                                          <!--special site 受取側表示-->
                                            <vs-row v-if="circularUsers && circularUsers.length > 0 && specialCircularFlg" class="item unsend" vs-type="flex">
                                              <vs-col vs-w="10">
                                                <p>{{groupName}}</p>
                                                <p></p>
                                              </vs-col>
                                              <vs-col  vs-type="flex" vs-w="2"  vs-justify="flex-end" vs-align="center">
                                                <p href="#" class="final">最終</p>
                                                <a v-if="circularUserLastSendIdIsSpecial" href="#"> <i class="far fa-flag"></i></a>
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
                                            <vs-row class="item" v-for="(comment, index) in commentsNotPrivate" v-bind:key="comment.name + index" :index="index">
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                          </vs-tab>
                                          <vs-tab label="社外宛">
                                            <vs-row class="item" v-for="(comment, index) in commentsIsPrivate" v-bind:key="comment.name + index" :index="index">
                                              <vs-col class="comment-panel" vs-w="12">
                                                <p><span class="user-name">{{comment.name}}</span> <span class="date">{{comment.create_at | moment('YYYY/MM/DD HH:mm:ss')}}</span></p>
                                                <p :style="{whiteSpace: 'pre-line'}">{{comment.text}}</p>
                                              </vs-col>
                                            </vs-row>
                                          </vs-tab>
                                          <vs-tab label="付箋" v-if="stickyNoteFlg">
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

        <vs-popup id="confirmPullBack" classContent="popup-example"  title="回覧の引戻し" :active.sync="confirmPullBack">
          <vs-row>
            <vs-col vs-type="flex" vs-w="3">件名</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ itemReqSendBack.subject }}</vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.file_names }}</vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>

              <div class="mb-3 mt-3">コメント</div>
              <vs-textarea v-model="pullback_remark"  />
              <div v-if="pullback_remark.length > pullback_remark_max_length" style="color:red">入力できる文字数は{{pullback_remark_max_length}}文字が最大です。</div>
          </vs-row>
          <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この回覧を引戻して、下書き一覧に移動します。</vs-col></vs-row>

          <vs-row class="mt-3">
            <vs-col vs-type="flex" class="confirm-button" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
              <vs-button @click="onPullBack()" color="primary">引戻し</vs-button>
              <vs-button @click="confirmPullBack=false;pullback_remark=''" color="dark" type="border">キャンセル</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>

        <vs-popup id="confirmReqSendBack" classContent="popup-example"  title="差戻し依頼" :active.sync="confirmReqSendBack">
          <vs-row>
            <vs-col vs-type="flex" vs-w="3">件名</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ itemReqSendBack.subject }}</vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.file_names }}</vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="3">差戻し依頼先</vs-col>
            <vs-col vs-type="flex" vs-w="1">:</vs-col>
            <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
          </vs-row>
          <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">差戻し依頼先に通知メールを送信します。</vs-col></vs-row>

          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
              <vs-button @click="onRequestSendBack()" color="primary">差戻し依頼</vs-button>
              <vs-button @click="confirmReqSendBack=false" color="dark" type="border">キャンセル</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>
        <!-- ほかのユーザーのよって操作されました。再度文書を開きなおしてください。 -->
        <modal name="sync-operation-modal"
               :pivot-y="0.2"
               :width="400"
               :classes="['v--modal', 'sync-operation-modal', 'p-4']"
               :height="'auto'"
               :clickToClose="false">
            <vs-row>
                <vs-col vs-w="12" vs-type="block">
                    <p>ほかのユーザーのよって操作されました。再度文書を開きなおしてください。</p>
                </vs-col>
            </vs-row>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button class="square mr-2 " color="primary" type="filled" v-on:click="onSyncOperationClick" > 閉じる</vs-button>
            </vs-row>
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
  </div>

    <!-- Mobile -->
    <div id="main-home-mobile" :class="isMobile?'mobile':''">
      <div style="width:100%;" v-if="circularUsers && circularUsers.length > 0">
          <span @click="goBack" class="hidden"><vs-icon icon="keyboard_arrow_left" size="medium" color="primary"></vs-icon></span>
          <div style="display:inline-block;position:relative;width: 100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{fileSelected ? fileSelected.name : ''}}</div>
      </div>
      <div style="width:100%;height:73px;background-color: #f2f2f2;padding-top:20px;" v-if="circularUsers && circularUsers.length > 0">From : {{circularUsers && circularUsers.length > 0 ? circularUsers[0].name : ''}}<span style="float: right;">{{circularUsers[0].create_at | moment("YYYY/MM/DD")}}</span></div>

      <div id="handleMailList" v-on:click="handleMailList">
        <div class="icon hide">
          <vs-icon icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
        </div>
        回覧先・コメント
      </div>

      <div id="mail_list_box">
          <div class="tools" :style="!stampToolbarActive && !isMobile ? 'display:none':'' ">
              <div>
                  <div style="padding: 0">
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
              </div>
          </div>
      </div>

      <div class="btn_dialog" v-if="itemReqSendBack.showBtnRequestSendBack || itemReqSendBack.showBtnBack">

        <div v-if="itemReqSendBack.showBtnRequestSendBack" v-on:click="confirmReqSendBack = true">
          <div class="icon" style="transform: rotate(90deg);"><i class="fas fa-undo-alt"></i></div>
          <div class="label">差戻依頼</div>
        </div>
        <div v-else-if="itemReqSendBack.showBtnBack" v-on:click="confirmPullBack = true">
          <div class="icon"><img :src="require('@assets/images/mobile/refund.svg')"></div>
          <div class="label">引戻し</div>
        </div>

      </div>

      <vs-card class="work-content">
          <vs-row v-if="mobilePages.length > 0">
              <vs-col vs-type="flex">
                  <div class="preview-list" style="background-color: rgb(242, 242, 242);width:100%;height:80px; padding: 5px 0 0 0 !important;">
                    <div style="text-align: center;margin-bottom:10px;">{{pages.length}}件の文書があります</div>
                    <div class="tabSelected">
                      <div>
                        <select v-model="tabSelected" @change="changeSelectedFile(tabSelected)">
                          <option v-for="(file, index) in files" :value="index" :key="index" :selected="(index==0)?'selected':''">{{ file.name }}</option>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="#0984e3" d="M311.9 335.1l-132.4 136.8C174.1 477.3 167.1 480 160 480c-7.055 0-14.12-2.702-19.47-8.109l-132.4-136.8C-9.229 317.8 3.055 288 27.66 288h264.7C316.9 288 329.2 317.8 311.9 335.1z"/></svg>
                      </div>
                    </div>
                    <div style="text-align: center; height: 32px;">
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
import {mapActions, mapState} from "vuex";
import InfiniteLoading from 'vue-infinite-loading';
import PdfPages from "../../components/home/PdfPages";
import PdfPageThumbnails from "../../components/home/PdfPageThumbnails";

import {dragscroll} from 'vue-dragscroll'
import {CIRCULAR} from '../../enums/circular';
import {CIRCULAR_USER} from '../../enums/circular_user';
import Utils from '../../utils/utils';
import {getPageUtil} from '../../utils/pagepreview';
import config from "../../app.config";
import Axios from "axios";
import draggable from 'vuedraggable';
import {cloneDeep} from "lodash/lang";
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
                settingLimit:{},
                filename_upload: '',
                cloudFileItems: [],
                breadcrumbItems: [],
                cloudLogo: null,
                cloudName: null,
                currentCloudFolderId: 0,
                checkShowConfirmAddSignature: JSON.parse(getLS('user')).check_add_signature_time_stamp,
                tab_cir_info: 0,
                itemReqSendBack: {},
                confirmPullBack: false,
                confirmReqSendBack: false,
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
                bizcardInfo: false,
                is_ipad: false,
                viewerWidth: 1,
                thumbnailViewerWidth: 0,
                visiblePageRange: [-1, -1],
                visibleThumbnailRange: [-1, -1],
                attachmentUploads : [],
                isShowAttachment:true,
                specialCircularFlg:false, //特設サイト回覧判断フラグ
                groupName: '', //特設サイト受取連携宛先情報
                circularUserLastSendIdIsSpecial: false,
                pullback_remark: '',
                pullback_remark_max_length: 500,
                currentCompanySkipFlg: 0,
                radioVal: "default",
                countAllTabNum: false,
                isMobile: false,
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
            commentsFilter() {
              return this.comments.filter(comment => comment.text && comment.text.trim())
            },
            showCircularHasReturnUsers() {
              return this.circularHasReturnUsers.filter(user => !user.special_site_receive_flg && this.hasPlanCircularUsers.length <= 0)
            },
            commentsNotPrivate () {
              return this.comments.filter(comment => comment.private_flg == 0 && comment.text && comment.text.trim())
            },
            commentsIsPrivate () {
              return this.comments.filter(comment => comment.private_flg == 1 && comment.text && comment.text.trim())
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
              let circular_user =  this.circular.users.slice().reverse().find(item => item.circular_status === CIRCULAR_USER.NOTIFIED_UNREAD_STATUS || item.circular_status === CIRCULAR_USER.READ_STATUS || item.circular_status === CIRCULAR_USER.PULL_BACK_TO_USER_STATUS || item.circular_status === CIRCULAR_USER.END_OF_REQUEST_SEND_BACK || item.circular_status === CIRCULAR_USER.REVIEWING_STATUS);
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
                  return this.$store.state.home.circular.users
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
                            if (item.return_flg === 1 && ((item.child_send_order === 0 && item.parent_send_order === 0) || (item.child_send_order === 1 && item.parent_send_order > 0))) {
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
            hasPlanCircularUsers:{
                get() {
                    if(!this.$store.state.home.circular || this.$store.state.home.circular.plans.length<=0) return [];
                    let plan=JSON.parse(JSON.stringify(this.$store.state.home.circular.plans))
                    let circularUsers=JSON.parse(JSON.stringify(this.$store.state.home.circular.users))
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
                            if (item.return_flg === 1 && ((item.child_send_order === 0 && item.parent_send_order === 0) || (item.child_send_order === 1 && item.parent_send_order > 0))) {
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
            isShowSkipFlg:{
              get() {
                if(!this.circular || !this.circular.users) {
                  return false;
                }

                let circular_user =  this.circular.users.slice().filter(item =>
                    item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS ||
                    item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS
                );

                if( circular_user && circular_user[0] && circular_user[0].create_user == this.loginUser.email &&
                    ( this.circular.users[this.circular.users.length - 1].circular_status == CIRCULAR_USER.NOT_NOTIFY_STATUS ||
                      this.circular.users[this.circular.users.length - 1].circular_status == CIRCULAR_USER.SEND_BACK_STATUS
                    ) && (
                        this.circular.users[0].circular_status != CIRCULAR_USER.NOTIFIED_UNREAD_STATUS &&
                        this.circular.users[0].circular_status != CIRCULAR_USER.NOT_NOTIFY_STATUS &&
                        this.circular.users[0].circular_status != CIRCULAR_USER.READ_STATUS
                    )
                ) {
                    return  true;
                }else{
                    return false;
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
                clearState: "home/clearState",
                selectFile: "home/selectFile",
                updateCurrentFileZoom: "home/updateCurrentFileZoom",
                changePositionFile: "home/changePositionFile",
                loadCircular: "home/loadCircular",
                getStampInfos: "home/getStampInfos",
                getBizcardById: "bizcard/getBizcardById",
                downloadSendFile: "home/downloadSendFile",
                getLimit: "setting/getLimit",
                getCloudItems: "cloud/getItems",
                uploadToCloud: "home/uploadToCloud",
                getMyInfo: "user/getMyInfo",
                pullback: "circulars/pullback",
                reqSendBack: "circulars/reqSendBack",
                search: "circulars/getListSent",
                addLogOperation: "logOperation/addLog",
                checkDeviceType: "home/checkDeviceType",
                getAttachment: "home/getAttachment",
                downloadAttachment:"home/downloadAttachment",
                reservePreviewFile: "home/reservePreviewFile",
                reserveAttachment: "home/reserveAttachment",
            }),
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
              this.$router.push(`/sent`);
            },
            async onDownloadFile(){
              await this.$store.commit('home/checkAddUsingTas', false);
              var ret = await this.downloadSendFile(0);
              const action = 'r9-14-download';
              // PAC_5-1027 ダウンロードの操作履歴が表示されない
              this.addLogOperation({ action: action, result: ret ? 0 : 1, params:{filename: this.fileSelected.name}});
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
          //PAC_5-1216 Start boxファイル上書き保存
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
            if (e.target.tagName === 'INPUT' || this.countAllTabNum ) return
            this.addStampHistory = false;
            this.addTextHistory = false;
            this.radioVal = "default"
          },
          changeStampHistory(e){
            // Because the native click event will be executed twice, the first time on the label tag
            // and the second time on the input tag, this processing is required
            if (e.target.tagName === 'INPUT' || this.addStampHistory  || this.countAllTabNum ){return}
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
            if (e.target.tagName === 'INPUT' || this.addTextHistory  || this.countAllTabNum ) return
            this.addTextHistory = true;
            this.addStampHistory = false;
            this.radioVal = "addTextHistory";
          },
          onPullBack: async function(){
            if(this.pullback_remark.length > this.pullback_remark_max_length){
                return false;
            }
            this.$store.dispatch('updateLoading', true);
            const data = {
              id : this.itemReqSendBack.id,
              parent_send_order : 0,
              child_send_order : 0,
              update_at : this.itemReqSendBack.upd_at,
              pullback_remark:this.pullback_remark,
            };
              let ret = await this.pullback(data);
              this.pullback_remark = '';
              if (ret !== true) {
                  if (ret.statusCode == 406) {
                      await this.$modal.show('sync-operation-modal');
                      $("#confirmPullBack").hide();
                      this.$store.dispatch('updateLoading', false);
                  }
              }else {
                  $("#confirmPullBack").hide();
                  this.$store.dispatch('updateLoading', false);
                  this.$router.push(`/sent`);
              }
          },
          onRequestSendBack: async function(){
            this.$store.dispatch('updateLoading', true);
            const data = {
              id : this.itemReqSendBack.id,
              parent_send_order : 0,
              child_send_order : 0,
              update_at : this.itemReqSendBack.upd_at,
            };
            let ret = await this.reqSendBack(data);
              if (ret !== true) {
                  if (ret.statusCode == 406) {
                      $("#confirmReqSendBack").hide();
                      await this.$modal.show('sync-operation-modal');
                  }
              } else {
                  $("#confirmReqSendBack").hide();
                  this.$store.dispatch('updateLoading', false);
                  this.$router.push(`/sent`);
              }
          },
          onSyncOperationClick: async function(){
              if(this.$store.state.home.usingPublicHash){
                  if (window.opener){
                      window.close();
                  }else{
                      if (this.userHashInfo){
                          if (parseInt(this.userHashInfo.current_env_flg)){
                              window.location.href = config.K5_API_URL;
                          }else{
                              if(parseInt(this.userHashInfo.current_edition_flg)){
                                  window.location.href = config.AWS_API_URL;
                              }else{
                                  window.location.href = config.OLD_AWS_API_URL;
                              }
                          }
                      }else{
                          window.location.href = config.LOCAL_API_URL;
                      }
                  }
              }else{
                  this.$router.go(0);
                  await this.$modal.hidden('sync-operation-modal');
              }
          },
          changeSelectedFile(index) {
            if (this.files[index].circular_document_id != this.fileSelected.circular_document_id) {
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
            async  validatHasAttachment(circular_id){
              let ret = await this.getAttachment(circular_id);
              const dialogText = this.isMobile ? "添付されたファイルを確認される際はPC版でご確認ください" : "右上の「添付ファイル」をクリックして文書をダウンロードしてご確認ください"
              if(ret.length > 0){
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
          goSkipRoute(){
              this.$router.push(`/skipCurrentAction`);
          },

          handleMailList() {
            $('#handleMailList .icon').stop().toggleClass('hide');
            $('#mail_list_box').stop().toggle(300);
          },
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
            const downloadFile = async ()=>{
              let info = {
                reserve_file_name: this.downloadReserveFilename  ?  this.downloadReserveFilename + this.fileSelected.name.substr(this.fileSelected.name.lastIndexOf('.')) : this.fileSelected.name,
                document_id: this.fileSelected.circular_document_id,
                stampHistory: this.addStampHistory,
                addTextHistory: this.addTextHistory,
                finishedDate: 0,
                usingTas: false,
              }
              let ret = await this.reservePreviewFile(info);

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
                this.histories = await this.getStampInfos(newVal.circular_document_id);

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
            this.isMobile = true
          }

          this.checkDeviceType();
          this.$store.commit('home/setUsingPublicHash', false);
          this.$store.state.home.title = '';
          this.itemReqSendBack = JSON.parse(localStorage.getItem("tr"));
          this.startVisibilityWatch();
          this.$watch(
            () => [this.zoom, this.fileSelected],
            (newVal, oldVal) => {
              const [, newFileSelected] = newVal;
              const [, oldFileSelected] = oldVal;

              const isFileChanged = oldFileSelected != newFileSelected;
              if (!isFileChanged) {
                // ズームのみ変更されたとき スクロール位置調整
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

          if(!this.$route.query.back) {
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
            // PAC_5-2352 回覧スキップ機能 START
            (async () => {
              Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                .then(response => {
                  if(response.data){
                      this.currentCompanySkipFlg = response.data.data.skip_flg
                  }
                })
                .catch(error => { return []; });
            })(),
            // PAC_5-2352 回覧スキップ機能 END
            (async () => {
              const id = this.$route.params.id;
              if(id ) {
                const ret = await this.loadCircular({id: id});
                let redirectBack = false;
                if(!ret) {
                  redirectBack = true;
                }else if(!this.circular || this.circular.circular_status === CIRCULAR.SAVING_STATUS || this.circular.circular_status === CIRCULAR.RETRACTION_STATUS) {
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
            })(),
              (async () => {
                  this.settingLimit = await this.getLimit();
                  if (this.settingLimit == null) {
                      this.settingLimit = {};
                  }
                  if (this.settingLimit && this.settingLimit.default_stamp_history_flg == 1 && !this.$route.query.back) {
                      this.addStampHistory = true
                      this.radioVal = "addStampHistory";
                  }
              })(),
              (async () => {
                //回覧作成者の会社
                let createCircularCompany  = await Axios.get(`${config.BASE_API_URL}/setting/getCreateCircularCompany?circular_id=${this.$route.params.id}`)
                    .then(response => {
                      return response.data ? response.data.data: [];
                    })
                    .catch(error => { return []; });
                this.isShowAttachment = createCircularCompany && createCircularCompany.attachment_flg;
              })(),
          );

          await Promise.all(promises);
          // 特設サイト
          if (this.circular && this.circular.special_site_flg) {
            this.specialCircularFlg = this.circular.special_site_flg;
            this.groupName = this.circular.special_site_group_name;
            this.circularUsers.forEach((item) => {
              if (item.special_site_receive_flg == 1 && item.id == this.circularUserLastSendId) {
                this.circularUserLastSendIdIsSpecial = true;
              }
            });
          }
            this.zoom = 100;

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
                this.validatHasAttachment(this.circular.id)
            });

            this.is_ipad = /(iPad)/i.test(navigator.userAgent);
            /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
            this.$store.commit('home/updateCloudBoxFlg',false);
            /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        },
        beforeDestroy() {
          // 取得を止めるため
          this.visiblePageRange = [-1, -1];
          this.visibleThumbnailRange = [-1, -1];
          /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する S*/
          this.$store.commit('home/updateCloudBoxFlg',false);
          /*PAC_5-3116 Boxを表示する時Boxを移動できるスライドバーだけを表示する E*/
        },
    }
</script>
<style scoped>

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

body{
  overflow-y: auto !important;
}

#main-home.mobile{
  display: none;
}

#confirmPullBack {
  .confirm-button{
    button{
      padding: 0.75rem 2rem;
    }
  }
}

#main-home-mobile.mobile{
  position:  relative;
  display: block!important;
  padding: 0 1.2rem;
  overflow-x: hidden !important;

  .pdf-content{
    margin-top: 10px;
    max-width: 100%;

    select{
      border-radius: 5px;
      padding: 0.25rem 0.75rem;
      max-width: 100%;
    }
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
    margin-bottom: 0;
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
    width: 400px;
    height: 60px;
    left: calc( 50% - 200px );
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

  }

  .preview-list {
    padding:  5px 0px !important;
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

}

@media ( min-width: 601px ){
  #main-home-mobile.mobile{
    top: -113px;
    padding: 0;

    .preview-list-page{
      width: 100%;
    }
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
    .btn_dialog{
      height: 90px !important;
      width: calc(100% - 2.4rem + 6px);
      left: calc(1.2rem - 3px);

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
