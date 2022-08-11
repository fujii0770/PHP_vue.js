<template>
    <div>
    <div id="sends-page" style="position: relative;" :class="isMobile?'mobile':''">

        <vs-row>
            <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                <div class="text-right">未読回覧文書【 <span class="text-red">{{ num_unread }}</span> 】件</div>
                <vs-card class="mb-0 mt-3">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                            <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                            <vs-input class="inputx w-full" label="差出人名" v-model="filter.userName"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                            <vs-input class="inputx w-full" label="差出人アドレス" v-model="filter.userEmail"/>
                        </vs-col>
                    </vs-row>
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                          <vs-select class="selectExample w-full" v-model="filter.status" label="回覧状況" placeholder="回覧状況">
                              <vs-select-item value="" text="--" />
                              <vs-select-item value="1" text="未読" />
                              <vs-select-item value="2" text="既読" />

                              <!--PAC_5-2375 START-->
                              <vs-select-item value="3" text="承認" />
                              <vs-select-item value="5" text="差戻し" />
                            <!--PAC_5-508 回覧状況に差戻し依頼を追加 引戻しは、下書き一覧に入るため、回覧状況から削除-->
                            <!--  <vs-select-item value="8" text="引戻し" />-->
                              <vs-select-item value="7" text="差戻し依頼" />
                              <!--PAC_5-2375 END-->
                              <!--PAC_5-2250 S-->
                              <vs-select-item value="14" text="スキップ" />
                              <!--PAC_5-2250 E-->
                          </vs-select>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                            <div class="w-full">
                                <label for="filter_fromdate" class="vs-input--label">受信日時From</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" :config="configDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                            <div class="w-full">
                                <label for="filter_todate" class="vs-input--label">受信日時To</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" :config="configDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                            <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                        </vs-col>
                    </vs-row>

                </vs-card>

                <vs-card>
                    <vs-table class="mt-3 custome-event" :data="listData" noDataText="データがありません。"
                        sst @sort="handleSort" stripe @selected="onShowReading">
                        <template slot="thead">
                            <vs-th sort-key="title" class="max-width-200">文書名 </vs-th>
                            <vs-th sort-key="A.email" class="min-width-200">差出人＜メールアドレス＞</vs-th>
                        <!--    <vs-th sort-key="C.edition_flg,C.env_flg">送信元</vs-th>-->
                            <vs-th sort-key="update_at">受信日時</vs-th>
                            <vs-th sort-key="U.circular_status">回覧状況</vs-th>
                            <vs-th >再通知設定 </vs-th>
                            <vs-th  class="width-150"> </vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" :style="{fontWeight:((getTrStatus(tr,false).indexOf('既読')>=0 || getTrStatus(tr,false).indexOf('未読')>=0)?'bold':'normal')}">
                                <vs-td class="max-width-200">
                                    {{tr.title}}
                                </vs-td>
                                <vs-td class="min-width-200">
                                    <div v-html="tr.email"></div>
                                </vs-td>
                               <!-- <vs-td>
                                    {{ sender_flg[tr.sender]}}
                                </vs-td>-->
                                <vs-td>{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</vs-td>
                                <vs-td>
                                    {{ tr.is_skip ? "スキップ(手動)" : getTrStatus(tr,false)}}
                                </vs-td>
                                <vs-td>

                                    <template v-if="tr.re_notification_day">
                                        <!--PAC_5-1857 送信一覧の再通知設定の項目の表記を日付のみで表示するように修正 Start-->
                                        {{ tr.re_notification_day  | moment("YYYY/MM/DD" ) }}
                                        <!--PAC_5-1857 End-->
                                    </template>
                                    <template v-else>（なし）</template>
                                </vs-td>
                                <vs-td>
                                    <template v-if="tr.showBtnRequestSendBack && !tr.hasOperationNotice">
                                        <vs-button class="square" style="width: 112px;" color="primary" v-on:click="itemReqSendBack= tr; confirmReqSendBack = true">差戻し依頼</vs-button>
                                    </template>
                                    <template v-else-if="tr.showBtnBack && !tr.special_site_flg">
                                        <vs-button class="square" style="width: 112px;"  color="primary" v-on:click="itemPull = tr; confirmPullBack = true">引戻し</vs-button>
                                    </template>
                                </vs-td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                    <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                </vs-card>
            </vs-col>

            <vs-col class="preview-wrapper" :vs-w="showReading?3:0.5" :vs-xs="showReading?12:0" :vs-sm="showReading?5:0.5" >
                <div  @click="showRead()">
                    <div class="button" v-if="!showReading" style="text-align: center; cursor: pointer;">
                        <i class="fas fa-caret-left" style="font-size: 40px; color: rgba(var(--vs-primary),1);"></i>
                        <div class="text" style="margin: 0 auto;line-height: 17px;"><p>閲<br>覧<br>ウ<br>ィ<br>ン<br>ド<br>ウ<br>を<br>表<br>示<br>す<br>る</p></div>
                    </div>
                </div>
                <div v-if="showReading" style="">
                    <div style="height: calc(100vh - 60px);  flex-direction: column;background: #fff" class="show-flex">
                        <div class="button2 ml-3 flex-item" @click="showReading=false" style="cursor: pointer; position: relative;">
                            <i class="fas fa-caret-right" style="font-size: 40px; color: rgba(var(--vs-primary),1);"></i>
                            <div class="text" style="position: absolute; top: 10px; left: 20px;">閉じる</div>
                        </div>

                        <template>
                            <vs-card  v-if="itemReadingDetail.circular.first_page_data" class="main-flex-item" style="position: relative; height: 100%;">
                                <div class="preview" style="width: 100%; position: absolute; overflow: hidden; height: 100%;">
                                    <img :src="'data:image/jpeg;base64,' + itemReadingDetail.circular.first_page_data" alt="" style="max-height: 100%;">
                                </div>
                            </vs-card>
                            <vs-card v-else class="main-flex-item">プレビューするレコードを選択してください</vs-card>

                            <vs-row vs-type="flex" style="padding: 10px">
                                <div class="break"></div>
                            </vs-row>

                            <vs-card class="detail flex-item mb-2">
                                <h3>詳細内容表示エリア</h3>
                                <vs-row class="mt-3">
                                    <vs-col vs-w="4" class="label">ファイル名</vs-col>
                                    <vs-col vs-w="8" class="info max-width-360">{{ itemReading.title }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">回覧状況</vs-col>
                                    <vs-col vs-w="8" class="info">{{ getTrStatus(itemReading,true) }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">依頼者名</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReadingDetail.userSend.family_name }} {{ itemReadingDetail.userSend.given_name }}</vs-col>
                                </vs-row>
                                <vs-row v-if="itemReadingDetail.userReceives.length">
                                    <vs-col vs-w="4" class="label">依頼日時</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReadingDetail.circular.applied_date | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                                </vs-row>
                               
                                <vs-row  class="mt-1">
                                    <vs-col vs-w="8" class="label">宛先</vs-col>
                                    <vs-col vs-w="4" class="label">捺印状況</vs-col>
                                </vs-row>
                                <div style="max-height: 130px; overflow: auto;">
                                    <vs-row v-for="(userReceive,index) in itemReadingDetail.userReceives" :key="index"  class="mt-1">
                                        <vs-col vs-w="8" class="info">
                                            <span v-if="userReceive.email">
                                                {{ userReceive.name }} &lt;{{ userReceive.email }}&gt;
                                            </span>
                                        </vs-col>
                                        <vs-col vs-w="4" class="info" v-if="userReceive.isOutCopany == 0">
                                            {{ userReceive.is_skip ? "スキップ(手動)" : received_status[userReceive.circular_status] }}
                                        </vs-col>
                                        <vs-col vs-w="4" class="info" v-if="userReceive.isOutCopany != 0">
                                            {{ out_status[userReceive.status] }}
                                        </vs-col>
                                    </vs-row>
                                </div>
                                <vs-row class="mt-1" v-if="itemReadingDetail.viewingUser[0]">
                                    <vs-col vs-w="12" class="label">閲覧</vs-col>
                                </vs-row>
                                <div v-if="itemReadingDetail.viewingUser[0]" style="max-height: 130px; overflow: auto;">
                                    <vs-row v-for="(viewer,index) in itemReadingDetail.viewingUser" :key="index"  class="mt-1">
                                        <vs-col vs-w="8" class="info">
                                            <span>
                                                {{ viewer.name }} &lt;{{ viewer.email }}&gt;
                                            </span>
                                        </vs-col>
                                    </vs-row>
                                </div>
                            </vs-card>
                            <div class="flex-item text-right mb-6">
                                <vs-button v-if="!itemReadingDetail.circular.origin_circular_url && !itemReadingDetail.hasRequestSendBack && itemReading.circular_status == CIRCULAR_USER.REVIEWING_STATUS" class="square" color="primary"
                                           @click="$router.push('/received-reviewing/'+itemReading.id);" :disabled="isEmtyItemReading"> 表示</vs-button>
                                <vs-button v-if="!itemReadingDetail.circular.origin_circular_url && !itemReadingDetail.hasRequestSendBack && itemReading.circular_status != CIRCULAR_USER.REVIEWING_STATUS" class="square" color="primary"
                                    @click="$router.push('/received'+(itemReading.circular_status == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || itemReading.circular_status == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || itemReading.circular_status == CIRCULAR_USER.NODE_COMPLETED_STATUS || showView?'-view':'')+'/'+itemReading.id);" :disabled="isEmtyItemReading"> 表示</vs-button>
                                <vs-button v-if="itemReadingDetail.circular.origin_circular_url && !itemReadingDetail.hasRequestSendBack" class="square" color="primary"
                                           @click="openWindow(itemReadingDetail.circular.origin_circular_url)" :disabled="isEmtyItemReading"> 表示</vs-button>
                                <vs-button v-if="!itemReadingDetail.circular.origin_circular_url && itemReadingDetail.hasRequestSendBack" class="square" color="primary"
                                           @click="$router.push('/received-approval-sendback/'+itemReading.id)" :disabled="isEmtyItemReading"> 表示</vs-button>
                                <vs-button v-if="itemReadingDetail.circular.origin_circular_url && itemReadingDetail.hasRequestSendBack" class="square" color="primary"
                                           @click="openWindow(itemReadingDetail.circular.origin_circular_url)" :disabled="isEmtyItemReading"> 表示</vs-button>
                            </div>
                        </template>

                    </div>
                </div>
            </vs-col>
        </vs-row>

        <vs-popup classContent="popup-example"  title="回覧の引戻し" :active.sync="confirmPullBack">
            <vs-row>
                <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ itemPull.subject }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-type="flex" vs-w="8">{{ itemPull.file_names }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-type="flex" vs-w="8">{{ itemPull.update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>

                <div class="mb-3 mt-3">コメント</div>
                <vs-textarea v-model="pullback_remark"   />
                <div v-if="pullback_remark.length > pullback_remark_max_length" style="color:red">入力できる文字数は{{pullback_remark_max_length}}文字が最大です。</div>
            </vs-row>
            <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この回覧を引戻して、受信一覧に移動します。</vs-col></vs-row>

            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onPullBack" color="primary">引戻し</vs-button>
                    <vs-button @click="confirmPullBack=false;pullback_remark=''" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="差戻し依頼" :active.sync="confirmReqSendBack">
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
    </div>

        <!-- 5-277 mobile html -->
        <div id="sends-page-mobile" style="position: relative;" :class="isMobile?'mobile':''">
            <div class="sends-mobile-title"><h3>受信一覧</h3></div>

            <vs-row>
                <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                    <vs-card class="mb-0 mt-3 select-panel">
                        <vs-row>
                            <div style="width:100%" @click="searchAreaFlg=!searchAreaFlg">
                                <vs-col vs-type="flex" vs-xs="12" class="mt-3 pr-2">
                                    <span class="sends-mobile-select-panel">
                                         <p style="margin-top: 2px;"><font size="4">絞り込み検索</font></p>
                                    </span>
                                        <span class="sends-mobile-select-button">
                                        <vs-icon id="arrow_mobile" class="around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                                    </span>
                                </vs-col>
                            </div>
                        </vs-row>
                        <vs-row v-show="searchAreaFlg">
                            <vs-col vs-type="flex" vs-xs="12" class="mb-3">
                                <vs-select class="selectExample w-full" v-model="filter.status" label="回覧状況" v-if="searchAreaFlg">
                                    <vs-select-item value="" text="--" />
                                    <vs-select-item value="1" text="未読" />
                                    <vs-select-item value="2" text="既読" />
                                    <vs-select-item value="3" text="承認" />
                                    <vs-select-item value="5" text="差戻し" />
                                    <vs-select-item value="7" text="差戻し依頼" />
                                    <vs-select-item value="14" text="スキップ" />
                                </vs-select>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlg">
                            <vs-col vs-type="flex" vs-xs="12" class="mb-3  sm:pl-2">
                                <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlg">
                            <vs-col vs-type="flex" vs-xs="12" class="mb-3  sm:pl-2">
                                <vs-input class="inputx w-full" label="差出人名" v-model="filter.userName"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlg">
                            <vs-col vs-type="flex" vs-xs="12" class="mb-3  sm:pl-2">
                                <vs-input class="inputx w-full" label="差出人アドレス" v-model="filter.userEmail"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlg">
                            <vs-col vs-type="flex" vs-xs="12" class="mb-3 sm:pl-2">
                                <div class="w-full">
                                    <label for="filter_fromdate" class="vs-input--label">受信日時From</label>
                                    <div class="vs-con-input">
                                        <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate_mobile" :config="configDate"></flat-pickr>
                                    </div>
                                </div>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlg">
                            <vs-col vs-type="flex" vs-xs="12" class="mb-3 sm:pl-2">
                                <div class="w-full">
                                    <label for="filter_todate" class="vs-input--label">受信日時To</label>
                                    <div class="vs-con-input">
                                        <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate_mobile" :config="configDate"></flat-pickr>
                                    </div>
                                </div>
                            </vs-col>
                        </vs-row>
                        <vs-row class="mt-3" v-show="searchAreaFlg">
                            <vs-col vs-type="flex">
                                <vs-button class="square" color="primary" v-on:click="onSearch(true)">検索する</vs-button>
                            </vs-col>
                        </vs-row>

                    </vs-card>

                    <vs-card>
                        <vs-table class="mt-3 w-100 custome-event" style="word-wrap: break-word;word-break: break-all;" :data="listData" noDataText="データがありません。"
                                  sst @sort="handleSort" stripe @selected="onShowReadingMobile">
                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <vs-td class="width-90" style="padding: 10px;">
                                        <div class="circle">{{ tr.is_skip ? "スキップ(手動)" : getTrStatusMobile(tr)}}</div>
                                    </vs-td>
                                    <vs-td class="width-100" style="padding: 10px;" nowrap="nowrap">
                                        <div class="width-100 show-list">{{tr.title}}</div>
                                        <div class="width-100 show-list">From:{{tr.name}}</div>
                                    </vs-td>
                                    <vs-td class="padding-left-0" style="padding: 10px;">{{tr.update_at | moment("MM/DD HH:mm")}}</vs-td>
                                </vs-tr>
                            </template>
                        </vs-table>
                        <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from ? pagination.from : 0 }} 件から {{ pagination.to ? pagination.to : 0 }} 件までを表示</div></div>
                        <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                    </vs-card>
                </vs-col>
            </vs-row>

            <vs-popup classContent="popup-example"  title="回覧の引戻し" :active.sync="confirmPullBack">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ itemPull.subject }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ itemPull.file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">更新日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ itemPull.update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>

                    <div class="mb-3 mt-3">コメント</div>
                    <vs-textarea v-model="pullback_remark"    />
                    <div v-if="pullback_remark.length > pullback_remark_max_length " style="color:red">入力できる文字数は{{pullback_remark_max_length}}文字が最大です。</div>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この回覧を引戻して、受信一覧に移動します。</vs-col></vs-row>

                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                        <vs-button @click="onPullBack" color="primary">引戻し</vs-button>
                        <vs-button @click="confirmPullBack=false;pullback_remark=''" color="dark" type="border">キャンセル</vs-button>
                    </vs-col>
                </vs-row>
            </vs-popup>

            <vs-popup classContent="popup-example"  title="差戻依頼" :active.sync="confirmReqSendBack">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.subject }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">差戻依頼先</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ itemReqSendBack.update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">差戻依頼先に通知メールを送信します。</vs-col></vs-row>

                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                        <vs-button @click="onRequestSendBack()" color="primary">差戻依頼</vs-button>
                        <vs-button @click="confirmReqSendBack=false" color="dark" type="border">キャンセル</vs-button>
                    </vs-col>
                </vs-row>
            </vs-popup>
            <!-- ほかのユーザーのよって操作されました。再度文書を開きなおしてください。 -->
            <modal name="sync-operation-modal"
                   :pivot-y="0.2"
                   :width="300"
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
        </div>
    </div>
</template>


<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from 'vue-infinite-loading';
import { CIRCULAR_USER } from '../../enums/circular_user';
import { CIRCULAR } from '../../enums/circular';

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';

import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    name:'received_list',
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination,
    },
    data() {
        return {
            CIRCULAR_USER: CIRCULAR_USER,
            filter: {
                id: "",
                name: "",
                fromdate: "",
                todate: "",
                sender: "",
                status: "",
            },
            listData:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "update_at",
            orderDir: "desc",
            num_unread: 0,
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0
            },
            showReading: false,
            options_status: ['','通知済/未読','既読','承認(捺印あり)','承認(捺印なし)','差戻し(既読)','差戻し(未読)','','引戻し','','既読'],
            received_status: ['未通知','通知済/未読','既読','承認(捺印あり)','承認(捺印なし)','差戻し(既読)','差戻し(未読)','','引戻し','既読','','スキップ'],
            options_status_mobile: ['','未読','既読','承認','承認','差戻し','差戻し','','引戻し','既読'],
            //    sender_flg: {'00':'スタンダードAWS', '01':'スタンダードK5', '10':'プロフェッショナルAWS', '11':'プロフェッショナルK5'},
           // sender_flg: {'00':'Corporate1', '01':'Corporate2', '10':'Business Pro1', '11':'Business Pro2'},
            out_status: ['-','未読','処理済'],
            itemReading: {},
            itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}]},
            itemPull: {},
            itemReqSendBack: {},
            confirmPullBack: false,
            confirmReqSendBack: false,
            click: 0,
            time: null,
            searchAreaFlg:false,
            showView:false, // received-view表示
            isMobile: false,
            pullback_remark: '',
            pullback_remark_max_length: 500,
        }
    },
    methods: {
        ...mapActions({
            postActionMultiple: "circulars/postActionMultiple",
            search: "circulars/getListReceived",
            getDetailCircularUser: "circulars/getDetailCircularUser",
            addLogOperation: "logOperation/addLog",
            pullback: "circulars/pullback",
            reqSendBack: "circulars/reqSendBack",
            getOriginCircularUrl: "circulars/getOriginCircularUrl",
        }),
        onSearch: async function (resetPaging) {
            let info = { status     : this.filter.status,
                         filename   : this.filter.filename,
                         userName   : this.filter.userName,
                         userEmail  : this.filter.userEmail,
                         fromdate   : this.filter.fromdate,
                         todate     : this.filter.todate,
                         sender     : this.filter.sender,
                         page       : resetPaging?1:this.pagination.currentPage,
                         limit      : this.pagination.limit,
                         orderBy    : this.orderBy,
                         orderDir   : this.orderDir,
                        };
            var data = await this.search(info);
            this.num_unread             = data.num_unread;
            data                        = data.data;
            this.listData               = data.data;
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;

            if(this.isMobile){
                this.searchAreaFlg = false;
            }
        },
        async onShowReading(tr){
            this.click++;
            if(this.click == 1){
                var root = this;
                var time = setTimeout(async function(){
                    root.click = 0;
                    if(root.confirmPullBack) return;
                    if(root.confirmReqSendBack) return;
                    root.$store.dispatch('updateLoading', true);
                    root.itemReading = tr;
                    root.itemReadingDetail = await root.getDetailCircularUser(tr.id);
                    root.showReading = true;
                    root.$store.dispatch('updateLoading', false);
                    // 選択tr同じノード 処理済み フラッグ
                    let approved_flag = false;
                    // 選択tr同じノード 差戻しのユーザindex
                    let back_index = root.itemReadingDetail.userReceives.findIndex(item => item.parent_send_order === root.itemReading.parent_send_order && item.child_send_order === root.itemReading.child_send_order
                        && item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                    // 選択tr同じノード すべてのユーザ
                    let all_arr = root.itemReadingDetail.userReceives.filter(item => item.parent_send_order === root.itemReading.parent_send_order && item.child_send_order === root.itemReading.child_send_order);
                    // 選択tr同じノード 承認のユーザ
                    let approved_arr = root.itemReadingDetail.userReceives.filter(item => item.parent_send_order === root.itemReading.parent_send_order && item.child_send_order === root.itemReading.child_send_order
                        && (item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS));
                    // 選択tr同じノード 複数の場合
                    if(back_index >= 0){
                        approved_flag = true;
                    }else{
                        if(all_arr.length > 1){
                            // all承認
                            if(approved_arr.length == all_arr.length){
                                approved_flag = true;
                            }else{
                                // 待つ
                                if(all_arr[0].wait == 1){
                                    approved_flag = false;
                                }else if(all_arr[0].wait == 0){
                                    approved_flag = (approved_arr.length >= all_arr[0].score);
                                }
                            }
                        }
                    }
                    // received-view表示
                    root.showView = approved_flag;
                },300)

            }else{
                clearTimeout(time);
                this.click = 0;
                var getOrigin =  await this.getOriginCircularUrl(tr.id);
                this.itemReadingDetail = await this.getDetailCircularUser(tr.id);
                // 選択tr同じノード 処理済み フラッグ
                let approved_flag = false;
                // 選択tr同じノード 差戻しのユーザindex
                let back_index = this.itemReadingDetail.userReceives.findIndex(item => item.parent_send_order === this.itemReading.parent_send_order && item.child_send_order === this.itemReading.child_send_order
                    && item.circular_status === CIRCULAR_USER.SEND_BACK_STATUS);
                // 選択tr同じノード すべてのユーザ
                let all_arr = this.itemReadingDetail.userReceives.filter(item => item.parent_send_order === this.itemReading.parent_send_order && item.child_send_order === this.itemReading.child_send_order);
                // 選択tr同じノード 承認のユーザ
                let approved_arr = this.itemReadingDetail.userReceives.filter(item => item.parent_send_order === this.itemReading.parent_send_order && item.child_send_order === this.itemReading.child_send_order
                    && (item.circular_status === CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || item.circular_status === CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS));
                // 選択tr同じノード 複数の場合
                if(back_index >= 0){
                    approved_flag = true;
                }else{
                    if(all_arr.length > 1){
                        // all承認
                        if(approved_arr.length == all_arr.length){
                            approved_flag = true;
                        }else{
                            // 待つ
                            if(all_arr[0].wait == 1){
                                approved_flag = false;
                            }else if(all_arr[0].wait == 0){
                                approved_flag = (approved_arr.length >= all_arr[0].score);
                            }
                        }
                    }
                }
                // received-view表示
                this.showView = approved_flag;
                if(!getOrigin.originCircularUrl && getOrigin.hasRequestSendBack){
                    this.$router.push('/received-approval-sendback/'+tr.id);
                }else if(!getOrigin.originCircularUrl && !getOrigin.hasRequestSendBack){
                    if(tr.circular_status == CIRCULAR_USER.REVIEWING_STATUS){
                        this.$router.push('/received-reviewing/'+tr.id);
                    }else if(tr.circular_status == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || tr.circular_status == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || tr.circular_status == CIRCULAR_USER.NODE_COMPLETED_STATUS || this.showView){
                        this.$router.push('/received-view/'+tr.id);
                    }else{
                        this.$router.push('/received/'+tr.id);
                    }
                }else if(getOrigin.originCircularUrl){
                    this.openWindow(getOrigin.originCircularUrl)
                }
            }

        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        openWindow(url){
            window.open(url,'_blank');
        },
        onPullBack: async function(){
            if(this.pullback_remark.length > this.pullback_remark_max_length){
                return false;
            }
            this.$store.dispatch('updateLoading', true);
            const data = {
              id : this.itemPull.id,
              parent_send_order : this.itemPull.parent_send_order,
              child_send_order : this.itemPull.child_send_order,
              update_at : this.itemPull.upd_at,
              pullback_remark:this.pullback_remark,
            };
            let ret = await this.pullback(data);
            this.pullback_remark = '';
            if (ret !== true) {
                if (ret.statusCode == 406) {
                    await this.$modal.show('sync-operation-modal');
            this.confirmPullBack = false;
            this.$store.dispatch('updateLoading', false);
                }
            }else {
                this.confirmPullBack = false;
                this.$store.dispatch('updateLoading', false);
            this.onSearch();
            }
        },
        onRequestSendBack: async function(){
            this.$store.dispatch('updateLoading', true);
            const data = {
              id : this.itemReqSendBack.id,
              parent_send_order : this.itemReqSendBack.parent_send_order,
              child_send_order : this.itemReqSendBack.child_send_order,
              update_at : this.itemReqSendBack.upd_at,
            };
            let ret = await this.reqSendBack(data);
            if (ret !== true) {
                if (ret.statusCode == 406) {
            this.confirmReqSendBack = false;
                    await this.$modal.show('sync-operation-modal');
                }
            } else {
                this.confirmReqSendBack = false;
            this.$store.dispatch('updateLoading', false);
            this.onSearch();
            }
        },
        getTrStatus(tr,type) {
            if (type && tr.circular_status == CIRCULAR_USER.READ_STATUS)
                return '回覧中';
          if(tr.hasRequestSendBack){
            if(tr.circular_status == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS)
              return '差戻し依頼(未読)';
            if(tr.circular_status == CIRCULAR_USER.READ_STATUS)
              return '差戻し依頼(既読)';
            return '差戻し依頼'
          }
          if (tr.status == CIRCULAR.SEND_BACK_STATUS && tr.circular_status == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS)
            return '差戻し(未読)';
          if (tr.status == CIRCULAR.SEND_BACK_STATUS && tr.circular_status == CIRCULAR_USER.READ_STATUS)
            return '差戻し(既読)';
          //PAC_5-2250 S
          if ((tr.status == CIRCULAR.SEND_BACK_STATUS || tr.status == CIRCULAR.CIRCULATING_STATUS) && tr.circular_status == CIRCULAR_USER.NODE_COMPLETED_STATUS){
              return 'スキップ';
            }
          //PAC_5-2250 S
          return this.options_status[tr.circular_status];
        },
        getTrStatusMobile(tr) {
            if(tr.hasRequestSendBack) return '差戻依頼';
            return this.options_status_mobile[tr.circular_status];
        },
        async onShowReadingMobile(tr){
            var getOrigin =  await this.getOriginCircularUrl(tr.id);
            this.itemReadingDetail = await this.getDetailCircularUser(tr.id);
            if(!getOrigin.originCircularUrl && getOrigin.hasRequestSendBack){
                this.$router.push('/received-approval-sendback/'+tr.id);
            }else if(!getOrigin.originCircularUrl && !getOrigin.hasRequestSendBack){
                if(tr.circular_status == CIRCULAR_USER.REVIEWING_STATUS){
                    this.$router.push('/received-reviewing/'+tr.id);
                }else if(tr.circular_status == CIRCULAR_USER.APPROVED_WITH_STAMP_STATUS || tr.circular_status == CIRCULAR_USER.APPROVED_WITHOUT_STAMP_STATUS || tr.circular_status == CIRCULAR_USER.NODE_COMPLETED_STATUS || this.showView){
                    this.$router.push('/received-view/'+tr.id);
                }else{
                    this.$router.push('/received/'+tr.id);
                }
            }else if(getOrigin.originCircularUrl){
                this.openWindow(getOrigin.originCircularUrl)
            }
        },
        showRead(){
            if(this.itemReadingDetail.viewingUser){
                this.showReading=true
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
    },
    computed: {
      isEmtyItemReading() {
          for(let i in this.itemReading) return false;
          return true
      }
    },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        searchAreaFlg:function (val){
            let obj = document.getElementById("arrow_mobile");
            if(val){
                obj.classList.add("around");
                obj.classList.remove("around_return");
            }else{
                obj.classList.add("around_return");
                obj.classList.remove("around");
            }
        },
    },
    mounted() {
        this.onSearch(false);
    },
    activated() {
        if (this.$route.meta.back && !this.$route.meta.keepReading){
            this.onSearch(true);
            this.showReading=false
        }
        this.$route.meta.back=false
        this.$route.meta.keepReading=false
    },
    created() {

      // Check Mobile
      if (
        /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
          navigator.userAgent
        )
      ) {
        this.isMobile = true
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
                //div.setAttribute('style','z-index:50');
                popups[i].appendChild(div);
            }
        });
        this.addLogOperation({ action: 'received-display', result: 0});
    }
}

</script>

<style lang="stylus">
    .detail{
        .label{ background: #b3e5fb; padding: 3px; }
        .info{  padding: 3px 3px 3px 5px; }
    }
    #arrow{
        cursor:pointer;
    }
    .around{
        animation:0.5s around_arrow;
        animation-fill-mode:forwards;
    }
    .around_return{
        animation:0.5s around_arrow_return;
        animation-fill-mode:forwards;
    }
    @keyframes around_arrow{
        0%{ transform:rotate(0);}
        100%{ transform:rotate(180deg); }
    }
    @keyframes around_arrow_return{
        0%{ transform:rotate(180deg);}
        100%{ transform:rotate(0); }
    }
</style>

<style lang="scss">
  #sends-page.mobile{
    display: none;
  }

  #sends-page-mobile.mobile{
    display: block;
    padding: 0 1.2rem;

    .circle{
      border: 1.5px solid #1E90FF;
      color: #1E90FF;
      font-size: 16px;
      border-radius: 100%;
      position: relative;
      z-index: 2;
      display: inline-block;
      width: 70px;
      height: 70px;
      line-height: 60px;
      background-color: #FFF;
      text-align: center;
      box-sizing: border-box;
      -webkit-box-sizing: border-box;
    }

    .sends-mobile-title{
      text-align: center;
      padding-top: 15px;
      margin-bottom: 5px;
    }

    button{
      margin: 0 auto;
    }

    .sends-mobile-select-button{
      margin-left: auto;
    }
    
    .vs-col{
      width: 100% !important;
    }

    table {
      tr {
        td{
          width: auto;

          div{
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }
        }
      }
    }
  
  }

  @media( min-width: 601px ) {
    #sends-page-mobile.mobile{
      padding: 0;
      top: -20px;
    }
  }

  @media( max-width: 600px ) {
    #sends-page-mobile.mobile{
      top: -60px;
    }
  }

  @media( max-width: 240px ) {
    #sends-page-mobile.mobile{
      table{
        tr td{
          &:first-child{
            display: none;
          }
        }
      }
    }
  }
</style>
