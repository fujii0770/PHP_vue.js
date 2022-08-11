<template>
  <div>

    <div id="sends-page" style="position: relative;" :class="isMobile?'mobile':''">

        <vs-row>
            <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                <vs-card style="margin-bottom: 0">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                            <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                            <vs-input class="inputx w-full" label="宛先名" v-model="filter.userName"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                            <vs-input class="inputx w-full" label="宛先のメールアドレス	" v-model="filter.userEmail"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mt-3 sm:pl-2">
                          <span @click="onAroundArrow">
                            <vs-icon id="arrow" class="mt-5 around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                          </span>
                        </vs-col>
                    </vs-row>
                    <vs-row v-show="searchAreaFlg">
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                            <vs-select class="selectExample w-full" v-model="filter.status" label="回覧状況">
                                <vs-select-item value="" text="--" />
                                <vs-select-item value="1" :text="options_status[1]" />
                              <!--  <vs-select-item value="2" :text="options_status[2]" />
                                <vs-select-item value="3" :text="options_status[3]" />-->
                                <vs-select-item value="4" :text="options_status[4]" />
                            </vs-select>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                            <div class="w-full">
                                <label for="filter_fromdate" class="vs-input--label">送信日時From</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" :config="configDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                            <div class="w-full">
                                <label for="filter_todate" class="vs-input--label">送信日時To</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" :config="configDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                            <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                        </vs-col>
                    </vs-row>

                </vs-card>

                <vs-card>
                    <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary"
                        v-bind:disabled="selected.length == 0"  v-on:click="confirmReNotification = true"><i class="fas fa-bell"></i> 再通知</vs-button>
                    <vs-button class="square"  color="danger" v-on:click="showDialogDelete"
                        v-bind:disabled="selected.length == 0"  ><i class="far fa-trash-alt"></i> 削除</vs-button>

                    <vs-table class="mt-3 custome-event" :data="listData" noDataText="データがありません。"
                        sst @sort="handleSort" stripe >
                        <template slot="thead">
                            <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                            <vs-th sort-key="title" class="max-width-200">文書名 </vs-th>
                            <vs-th sort-key="emails" class="min-width-200">宛先＜メールアドレス＞</vs-th>
                           <!-- <vs-th sort-key="dests">送信先環境</vs-th>-->
                            <vs-th sort-key="C.access_code">アクセスコード</vs-th>
                            <vs-th sort-key="update_at">送信日時 </vs-th>
                            <vs-th colspan="2" sort-key="C.circular_status">状況</vs-th>
                            <vs-th >再通知設定 </vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <vs-td><vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/></vs-td>
                                <td class="max-width-200"  @click="onShowReading(tr)">{{tr.title}}</td>
                                <td class="min-width-200"  @click="onShowReading(tr)"><div v-html="tr.emails"></div></td>
                                <td @click="onShowReading(tr)"> 社内 {{tr.access_code}}<br/> 社外 {{tr.outside_access_code}}</td>
                                <td @click="onShowReading(tr)">{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                                <template v-if="tr.showBtnRequestSendBack">
                                    <td @click="onShowReading(tr)">{{ getTrStatus(tr)}}</td>
                                    <vs-td>
                                        <vs-button class="square" style="width: 112px;" color="primary" v-on:click="itemReqSendBack= tr; confirmReqSendBack = true">差戻し依頼</vs-button>
                                    </vs-td>
                                </template>
                                <template v-else-if="tr.showBtnBack && !tr.special_site_flg">
                                    <td @click="onShowReading(tr)">{{ getTrStatus(tr)}}</td>
                                    <vs-td>
                                        <vs-button class="square" style="width: 112px;"  color="primary" v-on:click="itemPull = tr; confirmPullBack = true">引戻し</vs-button>
                                    </vs-td>
                                </template>
                                <template v-else>
                                    <td colspan="2" @click="onShowReading(tr)">
                                        {{ getTrStatus(tr)}}
                                        <span v-if="tr.circular_status == 1 || tr.circular_status == 2">（{{ tr.update_at | moment("YYYY/MM/DD HH:mm" ) }}）</span>
                                    </td>
                                </template>

                                <td @click="onShowReading(tr)">
                                    <template v-if="tr.re_notification_day">
                                        <!--PAC_5-1857 送信一覧の再通知設定の項目の表記を日付のみで表示するように修正 Start-->
                                        {{ tr.re_notification_day  | moment("YYYY/MM/DD" ) }}
                                        <!--PAC_5-1857 End-->
                                    </template>
                                    <template v-else>（なし）</template>
                                </td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                    <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                </vs-card>
            </vs-col>

            <vs-col class="preview-wrapper" :vs-w="showReading?3:0.5" :vs-xs="showReading?12:0" :vs-sm="showReading?5:0.5" >
                <div @click="showRead()">
                    <div class="button" v-if="!showReading" style="text-align: center; cursor: pointer;">
                        <i class="fas fa-caret-left" style="font-size: 40px; color:rgba(var(--vs-primary),1);"></i>
                        <div class="text" style="margin: 0 auto;line-height: 17px;"><p> 閲<br>覧<br>ウ<br>ィ<br>ン<br>ド<br>ウ<br>を<br>表<br>示<br>す<br>る</p></div>
                    </div>
                </div>

                <div v-if="showReading" style="">
                    <div style="height: calc(100vh - 60px);  flex-direction: column;background: #fff" class="show-flex">

                        <div class="button2 flex-item ml-3" @click="showReading=false" style="cursor: pointer; position: relative;">
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
                                    <vs-col vs-w="8" class="info">{{ getTrStatus(itemReading) }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">依頼者名</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReadingDetail.userSend.family_name }} {{ itemReadingDetail.userSend.given_name }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">依頼日時</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReadingDetail.circular.applied_date | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                                </vs-row>

                                <vs-row>
                                    <vs-col vs-w="4" class="label">社内アクセスコード</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReadingDetail.circular.access_code }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">社外アクセスコード</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReadingDetail.circular.outside_access_code }}</vs-col>
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
                                      <vs-col vs-w="4" class="info">
                                        {{ userReceive.is_skip ? "スキップ(手動)" : received_status[userReceive.circular_status] }}
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
                                <vs-button v-if="itemReadingDetail.circular.special_site_flg" class="square" color="primary" @click="openWindow(itemReadingDetail.circular.origin_circular_url)" :disabled="isEmtyItemReading"> 表示</vs-button>
                                <vs-button v-else class="square" color="primary" @click="$router.push('/sent/'+itemReading.id)" :disabled="isEmtyItemReading"> 表示</vs-button>
                            </div>
                        </template>

                    </div>
                </div>
            </vs-col>
        </vs-row>

        <vs-popup classContent="popup-example"  title="回覧の削除" :active.sync="confirmDelete">
            <div v-if="selected.length>1">{{ selected.length }}件の回覧を削除します。</div>
            <div v-if="selected.length==1">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].title }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">送信日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">宛先</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8"><span v-html="selected[0].emails"></span></vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この回覧を削除します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="回覧の再通知" :active.sync="confirmReNotification">
            <div v-if="selected.length>1">{{ selected.length }}件の回覧を承認者に再通知します。</div>
            <div v-if="selected.length==1">
                <vs-row>
                    <vs-col vs-type="flex" vs-w="3">件名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].title }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].file_names }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">送信日時</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8">{{ selected[0].update_at | moment("YYYY/MM/DD HH:mm") }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">宛先</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8"><span v-html="selected[0].emails"></span></vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この回覧を承認者に再通知します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onRenotification" color="success">再通知</vs-button>
                    <vs-button @click="confirmReNotification=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

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
                <vs-textarea v-model="pullback_remark"  />
                <div v-if="pullback_remark.length > pullback_remark_max_length"  style="color:red">入力できる文字数は{{pullback_remark_max_length}}文字が最大です。</div>
            </vs-row>

            <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この回覧を引戻して、下書き一覧に移動します。</vs-col></vs-row>

            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onPullBack" color="primary">引戻し</vs-button>
                    <vs-button @click="confirmPullBack=false;pullback_remark = '';" color="dark" type="border">キャンセル</vs-button>
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

        <vs-popup classContent="popup-example"  title="選択文書ダウンロード" :active.sync="confirmDownload">
            <vs-row>
                <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="input.filename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
            </vs-row>
            <div v-if="hasUndownloaded && selected.length==1" class="mt-3 text-red">※ダウンロードが行われていない回覧です。</div>
            <div v-if="hasUndownloaded && selected.length>1" class="mt-3 text-red">※ダウンロードが行われていない回覧が含まれています。</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDownloadReserve" color="primary">ダウンロード</vs-button>
                    <vs-button @click="confirmDownload=false" color="dark" type="border">キャンセル</vs-button>
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

    <!-- Mobile -->
    <div id="sends-page-mobile" :class="isMobile?'mobile':''">

        <h3 class="sends-page-mobile-title">送信一覧</h3>

        <vs-row>
          <vs-col vs-w="12" vs-xs="12" vs-sm="12">
              <vs-card style="margin-bottom: 0">
                  <vs-row>
                      <div style="width:100%" @click="searchAreaFlgMobile=!searchAreaFlgMobile">
                          <vs-col vs-type="flex" class="pr-2">
                              <span class="sends-mobile-select-panel">
                                  <p style="margin-top: 5px;"><font size="4">絞り込み検索</font></p>
                              </span>
                                  <span class="sends-mobile-select-button" style="margin-top: 5px;">
                                  <vs-icon id="arrow_mobile" class="around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                              </span>
                          </vs-col>
                      </div>
                  </vs-row>


                  <vs-row v-show="searchAreaFlgMobile">
                      <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                          <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                      </vs-col>
                      <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                          <vs-input class="inputx w-full" label="宛先名" v-model="filter.userName"/>
                      </vs-col>
                      <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                          <vs-input class="inputx w-full" label="宛先のメールアドレス	" v-model="filter.userEmail"/>
                      </vs-col>
                      <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                          <vs-select class="selectExample w-full" v-model="filter.status" label="回覧状況">
                              <vs-select-item value="" text="--" />
                              <vs-select-item value="1" :text="options_status[1]" />
                            <!--  <vs-select-item value="2" :text="options_status[2]" />
                              <vs-select-item value="3" :text="options_status[3]" />-->
                              <vs-select-item value="4" :text="options_status[4]" />
                          </vs-select>
                      </vs-col>
                      <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3">
                          <div class="w-full">
                              <label for="filter_fromdate" class="vs-input--label">送信日時From</label>
                              <div class="vs-con-input">
                                  <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" placeholder="年/月/日" :config="configDate"></flat-pickr>
                              </div>
                          </div>
                      </vs-col>
                      <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3  sm:pl-2">
                          <div class="w-full">
                              <label for="filter_todate" class="vs-input--label">送信日時To</label>
                              <div class="vs-con-input">
                                  <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" placeholder="年/月/日" :config="configDate"></flat-pickr>
                              </div>
                          </div>
                      </vs-col>
                  </vs-row>
                  <vs-row class="mt-3" v-show="searchAreaFlgMobile">
                      <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                          <vs-button class="square" color="primary" v-on:click="onSearch(true)"> 検索する </vs-button>
                      </vs-col>
                  </vs-row>
              </vs-card>

              <vs-card>

                <vs-table class="mt-3 custome-event" :data="listData" noDataText="データがありません。"
                        sst @sort="handleSort" stripe >

                    <template slot-scope="{data}">
                        <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                          <td @click="onShowReadingMobile(tr)">
                            <div class="width-100">
                              {{tr.title}}
                            </div>
                          </td>
                          <td @click="onShowReadingMobile(tr)">
                            <div class="width-100">
                              {{tr.update_at | moment("MM/DD HH:mm")}}
                            </div>
                          </td>

                          <td>
                            <div class="item_pull_back">
                              <template v-if="tr.showBtnRequestSendBack">
                                  <vs-button class="square" style="width: 112px;" color="primary" v-on:click="itemReqSendBack= tr; confirmReqSendBack = true">差戻し依頼</vs-button>
                              </template>
                              <template v-else-if="tr.showBtnBack && !tr.special_site_flg">
                                  <vs-button class="square" style="width: 112px;"  color="primary" v-on:click="itemPull = tr; confirmPullBack = true">引戻し</vs-button>
                              </template>
                              <template v-else>
                                  <p>{{ getTrStatus(tr) }}</p>
                                  <span v-if="tr.circular_status == 1 || tr.circular_status == 2">{{ tr.update_at | moment("YYYY/MM/DD HH:mm" ) }}</span>
                              </template>
                            </div>
                          </td>

                        </vs-tr>
                    </template>
                </vs-table>
                <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>

              </vs-card>
          </vs-col>

        </vs-row>


    </div>

  </div>
</template>


<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from 'vue-infinite-loading';

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';

import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination,
    },
    data() {
        return {
            filter: {
                id: "",
                name: "",
                fromdate: "",
                todate: "",
                destEnv: "",
            },
            selectAll: false,
            listData:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "update_at",
            orderDir: "desc",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                disableMobile: true
            },
            confirmDelete: false,
            showReading: false,
            options_status: ['保存中','回覧中','回覧完了','回覧完了(保存済)','差戻し','引戻','','','','削除'],
            received_status: ['未通知','通知済/未読','既読','承認(捺印あり)','承認(捺印なし)','差戻し','差戻し(未読)','','','既読','','スキップ'],
         //   dest_env: {'00':'スタンダードAWS', '01':'スタンダードK5', '10':'プロフェッショナルAWS', '11':'プロフェッショナルK5'},
         //   dest_env: {'00':'Corporate1', '01':'Corporate2', '10':'Business Pro1', '11':'Business Pro2'},
            out_status: ['-','未読','処理済'],
            confirmPullBack: false,
            confirmReqSendBack: false,
            itemPull: {},
            itemReqSendBack: {},
            itemReading: {},
            itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}]},
            confirmReNotification: false,
            click: 0,
            time: null,
            searchAreaFlg:false,
            input:{
                ids: [],
                filename: "",
            },
            inputMaxLength: 50,
            pullback_remark: '',
            pullback_remark_max_length: 500,
            isMobile: false,
            searchAreaFlgMobile: false,
            confirmDownload: false,
            hasUndownloaded: false,
        }
    },
    computed: {
      selected() {
        return this.listData.filter(item => item.selected);
      },
        isEmtyItemReading() {
            for(let i in this.itemReading) return false;
            return true
        }
    },
    methods: {
        ...mapActions({
            postActionMultiple: "circulars/postActionMultiple",
            search: "circulars/getListSent",
            delete: "circulars/deleteSend",
            getDetailCircularUser: "circulars/getDetailCircularUser",
            renotification: "circulars/renotification",
            pullback: "circulars/pullback",
            reqSendBack: "circulars/reqSendBack",
            addLogOperation: "logOperation/addLog",
            getOriginCircularUrl: "circulars/getOriginCircularUrl",
        }),
        onSearch: async function (resetPaging) {
            this.selectAll = false;
            let info = { status     : this.filter.status,
                         filename   : this.filter.filename,
                         userName   : this.filter.userName,
                         userEmail  : this.filter.userEmail,
                         fromdate   : this.filter.fromdate,
                         todate     : this.filter.todate,
                         destEnv    : this.filter.destEnv,
                         page       : resetPaging?1:this.pagination.currentPage,
                         limit      : this.pagination.limit,
                         orderBy    : this.orderBy,
                         orderDir   : this.orderDir,
                        };
            var data = await this.search(info);
            this.listData               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
            if(this.isMobile){
                this.searchAreaFlgMobile = false;
            }            
        },
        openWindow(url){
          window.open(url,'_blank');
        },
        onSelectAll() {
            this.selectAll = !this.selectAll;
            this.listData.map(item=> {item.selected = this.selectAll; return item});
        },
        showDialogDelete(){
            this.confirmDelete = true;
        },
        onDownloadReserve: async function (){
            this.input.ids = this.getSelectedID();
            this.input.status = this.getSelectedStatus();
            this.input.finishedDate = this.month;
            this.input.download_type = this.DOWNLOAD_TYPE.COMPLETED_DOWNLOAD_RESERVE;//完了一覧で予約
            this.confirmDownload = false;
            await this.downloadReserve(this.input);
        },

        onDelete: async function () {
            this.confirmDelete = false;
            await this.postActionMultiple({action: 'deleteSent', info: { cids: this.getSelectedID() }});
            this.onSearch(false);
        },

        onRenotification: async function () {
           await this.postActionMultiple({action: 'reNotification', info: { cids: this.getSelectedID() }});
           this.confirmReNotification = false;
        },

        onPullBack: async function(){
            if(this.pullback_remark.length > this.pullback_remark_max_length){
                return false;
            }
            this.$store.dispatch('updateLoading', true);
            const data = {
              id : this.itemPull.id,
              parent_send_order : 0,
              child_send_order : 0,
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
              parent_send_order : 0,
              child_send_order : 0,
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
                this.onSearch(false);
            }
        },

        getSelectedID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
        },
        async onShowReading(tr){
            this.click++;
            localStorage.setItem("tr", JSON.stringify(tr));
            localStorage.setItem("tr", JSON.stringify(tr));
            if(this.click == 1){
                var root = this;
                var time = setTimeout(async function(){
                    root.click = 0;
                    root.itemReading = tr;
                    root.$store.dispatch('updateLoading', true);
                    root.itemReadingDetail = await root.getDetailCircularUser(tr.id);
                    root.showReading = true;
                    root.$store.dispatch('updateLoading', false);
                },300)
            }else{
                clearTimeout(time);
                this.click = 0;
                if (tr.special_site_flg) {
                  //特設サイトの場合、申請者が受取側に遷移が必要です。
                  var getOrigin = await this.getOriginCircularUrl(tr.id);
                  this.openWindow(getOrigin.originCircularUrl)
                } else {
                  this.$router.push('/sent/' + tr.id);
                }
            }
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        onRowCheckboxClick: function (tr) {
            tr.selected = !tr.selected;
            this.selectAll = this.listData.every(item => item.selected);
        },
        getTrStatus(tr) {
            if(tr.hasRequestSendBack) return '差戻し依頼';
            return this.options_status[tr.circular_status];
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
      async onShowReadingMobile(tr){
          localStorage.setItem("tr", JSON.stringify(tr));
          localStorage.setItem("tr", JSON.stringify(tr));
          this.click = 0;
          if (tr.special_site_flg) {
            //特設サイトの場合、申請者が受取側に遷移が必要です。
            var getOrigin = await this.getOriginCircularUrl(tr.id);
            this.openWindow(getOrigin.originCircularUrl)
          } else {
            this.$router.push('/sent/' + tr.id);
          }
      },

    },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        searchAreaFlgMobile:function (val){
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
        this.addLogOperation({ action: 'sent-display', result: 0});
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

  #sends-page-mobile{
    display: none;
  }

  #sends-page-mobile.mobile{
    display: block;
    padding: 0 1.2rem;

    button{
      margin: 0 auto;
    }

    input{
      transform: scale(1);
    }

    .sends-mobile-select-button{
      margin-left: auto;
    }

    .sends-page-mobile-title{
      text-align: center;
      margin-bottom: 15px;
    }

    .custome-event{
      table{
        max-width: 100%;

        td{
          padding: 10px 5px;

          .item_pull_back{
            text-align: center;

            button{
              padding: 0.75rem 0.5rem;
            }
          }

          .width-100{
            width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
          }

          &:nth-child(1){
            div{
              width: 120px;
            }
          }

          &:nth-child(2){
            div{
              width: 50px;
            }
          }
        }


      }
    }


  }

@media ( max-width: 600px ){
  #sends-page-mobile.mobile{
    margin-top: -50px;
  }
}

@media ( max-width: 240px ){
  #sends-page-mobile.mobile{
    table{
      td{
        padding: 10px 0;

        &:first-child{
          div{
            max-width: 90%;
            margin-right: 5%;
          }
        }
      }
      .width-100{
        width: auto !important;
      }
    }
  }
}

</style>
