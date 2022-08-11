<template>
    <div>
    <div id="sends-page" style="position: relative;"  :class="isMobile?'mobile':''">
        <vs-row>
            <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                <vs-card style="margin-bottom: 0">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-select class="selectExample w-full" v-model="filter.kind" label="回覧種類">
                                <vs-select-item value="" text="--" />
                                <vs-select-item value="0" :text="circular_kind[0]" />
                                <vs-select-item value="1" :text="circular_kind[1]" />
                            </vs-select>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-select class="selectExample w-full" id="finishedDate" v-model="filter.finishedDate" label="完了日時">
                                <vs-select-item :key="index" :value="index" :text="date" v-for="(date, index) in finishedTimeList" />
                            </vs-select>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                        </vs-col>

                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mt-3 pr-2">
                          <span @click="onAroundArrow">
                            <vs-icon id="arrow" class="mt-5 around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                          </span>
                        </vs-col>
                    </vs-row>
                    <vs-row v-show="searchAreaFlg">
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="差出人名" v-model="filter.senderName"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="宛先名" v-model="filter.receiverName"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="差出人アドレス	" v-model="filter.senderEmail"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3  pr-2">
                            <vs-input class="inputx w-full" label="宛先アドレス	" v-model="filter.receiverEmail"/>
                        </vs-col>
                    </vs-row>
                    <vs-row v-show="searchAreaFlg">
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <div class="w-full">
                                <label for="filter_fromdate" class="vs-input--label">完了日時From</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" :config="completedConfigDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <div class="w-full">
                                <label for="filter_todate" class="vs-input--label">完了日時To</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" :config="completedConfigDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                    </vs-row>
                    <vs-row v-if="canUseTemplate">
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <label class="vs-input--label">テンプレート検索</label>
                        </vs-col>
                    </vs-row>
                    <vs-row v-if="canUseTemplate">
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <div class="w-full">
                                <label for="filter_fromdate" class="vs-input--label">テンプレート日時From</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.templateFrom" id="filter_templateFrom" :config="configDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <div class="w-full">
                                <label for="filter_todate" class="vs-input--label">テンプレート日時To</label>
                                <div class="vs-con-input">
                                    <flat-pickr class="w-full" v-model="filter.templateTo" id="filter_templateTo" :config="configDate"></flat-pickr>
                                </div>
                            </div>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="数値データ" v-model="filter.templateNum"/>
                        </vs-col>
                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                            <vs-input class="inputx w-full" label="文字データ" v-model="filter.templateText"/>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                            <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                        </vs-col>
                    </vs-row>
                    
                </vs-card>

                <vs-card>
                  <vs-col vs-type="flex" vs-align="center" vs-justify="end" >
                    <div style="flex:1;justify-self: start;display: flex;justify-content: start;align-items: center">
                     <div style="font-size: .85rem;font-weight: bold;color: rgba(0,0,0,.7)">表示件数：</div>
                      <div>
                        <vs-select
                            width="100px"
                            v-model="pagination.limit"
                            @change="onSearch(false)"
                        >
                          <vs-select-item :key="index" :value="item" :text="item" v-for="(item,index) in pageNum" />
                        </vs-select>
                      </div>
                    </div>
                    <vs-dropdown>
                      <vs-button id="button5" class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary" type="filled" v-bind:disabled="selected.length === 0"><i class="fas fa-download"></i> ダウンロード予約</vs-button>
                      <vs-dropdown-menu v-show="selected.length > 0" class="download-complete">
                        <li v-if="selected.length > 0" class="vx-dropdown--item">
                          <a class="vx-dropdown--item-link" style="width: 202px;text-align: center;">
                            <vs-radio class="mb-2 mt-2" vs-value="default" vs-name="radioVal" @click.native.stop="clearRadioStatus($event)" v-model="radioVal" :disabled="countAllTabNum">完了済みファイル</vs-radio>
                          </a>
                        </li>
                        <li v-if="selected.length > 0" class="vx-dropdown--item">
                          <a class="vx-dropdown--item-link" style="width: 202px;text-align: center;">
                            <vs-radio class="mb-2 mt-2" vs-value="stampHistory" vs-name="radioVal"  v-model="radioVal" @click.native.stop="changeRadioStatus($event)">回覧履歴を付ける</vs-radio>
                          </a>
                        </li>
                        <vs-button class="download-item download-complete-btn" type="filled" color="primary"  @click="showDialogDownload" style="width: 90%; margin: auto; display: flex;" :disabled="countAllTabNum">
                          <i class="fas fa-download"></i> ダウンロード予約</vs-button>
                      </vs-dropdown-menu>
                    </vs-dropdown>
                    <vs-button v-if="canStoreCircular" class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0"  color="primary" v-on:click="onSaveLongtermModal"
                               v-bind:disabled="selected.length == 0">長期保管</vs-button>
                    <!--                    <vs-button class="download-item" v-bind:disabled="selected.length == 0" @click="onDownloadCsvReserve" color="warning" style="min-width: unset;"><i class="fas fa-download"></i> CSV出力</vs-button>-->
                    <vs-button class="square"  color="danger" v-on:click="showDialogDelete"
                               v-bind:disabled="selected.length == 0 || this.hasReceived"  ><i class="far fa-trash-alt"></i> 削除</vs-button>
                  </vs-col>
                 

                    <vs-table class="mt-3 custome-event" :data="compListData" noDataText="データがありません。"
                        sst @sort="handleSort" stripe >
                        <template slot="thead">
                            <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                            <vs-th sort-key="circular_kind" class="">回覧種類 </vs-th>
                            <vs-th sort-key="file_names" class="max-width-200">文書名 </vs-th>
                            <vs-th sort-key="sender" class="max-width-200">差出人</vs-th>
                            <vs-th sort-key="emails" class="min-width-200">宛先</vs-th>
                        <!--    <vs-th sort-key="dests">送信先環境</vs-th>-->
                            <vs-th sort-key="C.access_code">アクセスコード</vs-th>
                            <vs-th sort-key="update_at">完了日時</vs-th>
                            <vs-th></vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <vs-td><vs-checkbox  :value="tr.selected" @click="onRowCheckboxClick(tr)" /></vs-td>
                                <td @click="onShowReading(tr)">{{tr.kind}}</td>
                                <td class="max-width-200"  @click="onShowReading(tr)">{{tr.file_names}}</td>
                                <td @click="onShowReading(tr)"><div v-html="tr.sender"></div></td>
                                <td class="min-width-200"  @click="onShowReading(tr)"><div v-html="tr.emails"></div></td>
                                <td @click="onShowReading(tr)"> 社内 {{tr.access_code}}<br/> 社外 {{tr.outside_access_code}}</td>
                            <!--    <td @click="onShowReading(tr)"><div v-html="tr.dests"></div></td>-->
                                <td @click="onShowReading(tr)">{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                                <td @click="onShowReading(tr)">{{tr.result === 1 ? '[自動保管済]' : ''}}</td>
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
                                    <vs-col vs-w="8" class="info max-width-360">{{ itemReading.file_names }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">回覧状況</vs-col>
                                    <vs-col vs-w="8" class="info">{{ options_status[itemReading.circular_status] }}</vs-col>
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
                                        <vs-col vs-w="4" class="info" v-if="userReceive.isOutCopany == 0">
                                            {{  userReceive.is_skip ? "スキップ(手動)" : recived_status[userReceive.circular_status]}}
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
                                <vs-button v-if="!itemReadingDetail.circular.origin_circular_url" class="square" color="primary" @click="toView(itemReading.id)" :disabled="isEmtyItemReading"> 表示</vs-button>
                                <vs-button v-if="itemReadingDetail.circular.origin_circular_url" class="square" color="primary"
                                           @click="openWindow(itemReadingDetail.circular.origin_circular_url)" :disabled="isEmtyItemReading"> 表示</vs-button>
                            </div>
                        </template>
                        
                    </div>
                </div>
            </vs-col>
        </vs-row>
               
        <vs-popup classContent="popup-example"  title="回覧の削除" :active.sync="confirmDelete">
            <div v-if="selected.length>1">{{ selected.length }}件の回覧を削除します。</div>
            <div v-if="selected.length==1">
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">文書名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].file_names }}</vs-col>
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
            <!--   PAC_5-1057 ダウンロードするかどうか       -->
            <div v-if="hasUndownloaded && selected.length==1 && selected[0].status !== 3" class="mt-3 text-red">※ダウンロードが行われていない回覧です。</div>
            <div v-if="hasUndownloaded && selected.length>1 && selected[0].status !== 3" class="mt-3 text-red">※ダウンロードが行われていない回覧が含まれています。</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="確認" :active.sync="confirmSaveLongTerm">
            <div v-if="selected.length>1">選択した文書の長期保管を行います。よろしいですか？</div>
            <div v-if="selected.length==1">
                <div class="mb-0">選択した文書の長期保管を行います。よろしいですか？</div><br>
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
                    <FolderTree ref="tree" v-show="showTree" :treeId="selectCompletedIndexTree" @onNodeClick="setFolderId"></FolderTree>
                </div>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onSaveLongTerm" color="primary">はい</vs-button>
                    <vs-button @click="confirmSaveLongTerm=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="選択文書ダウンロード予約" :active.sync="confirmDownload">
            <vs-row>
                <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="input.filename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
            </vs-row>
            <div v-if="hasUndownloaded && selected.length==1" class="mt-3 text-red">※ダウンロードが行われていない回覧です。</div>
            <div v-if="hasUndownloaded && selected.length>1" class="mt-3 text-red">※ダウンロードが行われていない回覧が含まれています。</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDownloadReserve" color="primary">ダウンロード予約</vs-button>
                    <vs-button @click="confirmDownload=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>

        <!-- 5-277 mobile html -->
        <div id="sends-page-mobile" style="position: relative;"  :class="isMobile?'mobile':''">
            <vs-row>
                <vs-col :vs-w="showReading?9:(isMobile?12:11.5)" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                    <div class="sends-mobile-title"><h3>完了一覧</h3></div>

                    <vs-card style="margin-bottom: 0" class="select-panel">
                        <vs-row>
                            <div style="width:100%" @click="searchAreaFlgMobile=!searchAreaFlgMobile">
                                <vs-col vs-type="flex" vs-lg="12" :vs-sm="isMobile?12:6" vs-xs="12" class="mt-3 pr-2">
                                    <span class="sends-mobile-select-panel">
                                        <p style="margin-top: 2px;"><font size="4">絞り込み検索</font></p>
                                    </span>
                                        <span class="sends-mobile-select-button">
                                        <vs-icon id="arrow_mobile" class="around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                                    </span>
                                </vs-col>
                            </div>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-input class="inputx w-full" label="文書名" v-model="filter.filename"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-select class="selectExample w-full" id="finishedDate" v-model="filter.finishedDate" label="完了日時">
                                    <vs-select-item :key="index" :value="index" :text="date" v-for="(date, index) in finishedTimeList" />
                                </vs-select>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-input class="inputx w-full" label="差出人名" v-model="filter.senderName"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-input class="inputx w-full" label="差出人アドレス	" v-model="filter.senderEmail"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <div class="w-full">
                                    <label for="filter_fromdate" class="vs-input--label">完了日時From</label>
                                    <div class="vs-con-input">
                                        <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate_mobile" :config="completedConfigDate"></flat-pickr>
                                    </div>
                                </div>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <div class="w-full">
                                    <label for="filter_todate" class="vs-input--label">完了日時To</label>
                                    <div class="vs-con-input">
                                        <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate_mobile" :config="completedConfigDate"></flat-pickr>
                                    </div>
                                </div>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-input class="inputx w-full" label="宛先名" v-model="filter.receiverName"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3  pr-2">
                                <vs-input class="inputx w-full" label="宛先アドレス	" v-model="filter.receiverEmail"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="canUseTemplate" v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <span class="sends-mobile-select-panel">
                                    テンプレート検索
                                </span>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="canUseTemplate" v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <div class="w-full">
                                    <label for="filter_fromdate" class="vs-input--label">テンプレート日時From</label>
                                    <div class="vs-con-input">
                                        <flat-pickr class="w-full" v-model="filter.templateFrom" id="filter_templateFrom_sp" :config="configDate"></flat-pickr>
                                    </div>
                                </div>
                            </vs-col>
                        <vs-row v-if="canUseTemplate" v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <div class="w-full">
                                    <label for="filter_todate" class="vs-input--label">テンプレート日時To</label>
                                    <div class="vs-con-input">
                                        <flat-pickr class="w-full" v-model="filter.templateTo" id="filter_templateTo_sp" :config="configDate"></flat-pickr>
                                    </div>
                                </div>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="canUseTemplate" v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-input class="inputx w-full" label="数値データ" v-model="filter.templateNum"/>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="canUseTemplate" v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" :vs-lg="isMobile?12:2" :vs-sm="isMobile?12:6" vs-xs="12" class="mb-3 pr-2">
                                <vs-input class="inputx w-full" label="文字データ" v-model="filter.templateText"/>
                            </vs-col>
                        </vs-row>
                    </vs-row>
                        <vs-row class="mt-3" v-show="searchAreaFlgMobile">
                            <vs-col vs-type="flex" vs-w="12">
                                <vs-button class="square" color="primary" v-on:click="onSearch(true)">検索する</vs-button>
                            </vs-col>
                        </vs-row>
                    </vs-card>

                    <vs-card>
                        <vs-table class="mt-3 custome-event" :data="compListData" noDataText="データがありません。"
                                  sst @sort="handleSort" stripe >
                            <template slot-scope="{data}">
                                <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                    <td class="max-width-150"  @click="onShowReadingMobile(tr)">
                                        <div class="show-list">{{tr.file_names}}</div>
                                        <div class="show-list">差出人:{{tr.sender_name}}</div>
                                    </td>
                                    <td @click="onShowReadingMobile(tr)">{{tr.update_at | moment("MM/DD HH:mm")}}</td>
                                    <!-- ↓ PAC_5-938 対応-->
                                    <!-- <td @click="postActionMultiple({action: 'downloadFile', info: { cids: [tr.id] }})"><i class="fas fa-download"></i></td> -->
                                </vs-tr>
                            </template>
                        </vs-table>
                        <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from ? pagination.from : 0 }} 件から {{ pagination.to ? pagination.to : 0}} 件までを表示</div></div>
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
import config from "../../app.config";
import Axios from "axios";
import { DOWNLOAD_TYPE } from '../../enums/download_type';
import FolderTree from '../../components/long_term/FolderTree';

import VxPagination from '@/components/vx-pagination/VxPagination.vue';

export default {
    name:'completed_list',
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination,
        FolderTree
    },
    data() {
        return {
            filter: {
                id: "",
                kind: "",
                name: "",
                destEnv: "",
                fromdate: "",
                todate: "",
                finishedDate: "",
            },
            selectAll: false,
            listData:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "update_at",
            orderDir: "desc",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0
            },
            completedConfigDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                minDate: "",
                maxDate: ""
            },
            confirmDelete: false,
            confirmSaveLongTerm: false,
            confirmDownload: false,
            hasUndownloaded: false,
            //PAC_5-976 受信データが含まれる場合、削除ボタン非活性化
            hasReceived: false,

            showReading: false,
            options_status: ['保存中','回覧中','回覧完了','回覧完了(保存済)','差戻し','引戻','','','','削除'],
            recived_status: ['未通知','通知済/未読','既読','承認(捺印あり)','承認(捺印なし)','差戻し','差戻し(未読)','','','','','スキップ'],
            circular_kind: ['受信', '送信'],
        //    env: {'00':'スタンダードAWS', '01':'スタンダードK5', '10':'プロフェッショナルAWS', '11':'プロフェッショナルK5'},
        //    env: {'00':'Corporate1', '01':'Corporate2', '10':'Business Pro1', '11':'Business Pro2'},
            out_status: ['-','未読','処理済'],
            itemPull: {},
            itemReading: {},
            itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}]},
            click: 0,
            time: null,
            searchAreaFlg:false,
            searchAreaFlgMobile:false,
            canStoreCircular:false,
            keywords: '',
            checkKeywordsLenFlg: false,
            canUseTemplate:false,
            settingLimit:{},
            filename_upload: '',
            cloudLogo: null,
            cloudName: null,
            cloudFileItems: [],
            breadcrumbItems: [],
            currentCloudFolderId: 0,
            dataBlob: '',
            input:{
                ids: [],
                status: [],
                filename: "",
                stampHistory: false,
            },
            inputMaxLength: 46,
            finishedTimeList: ['当月', '1ヶ月前', '2ヶ月前', '3ヶ月前', '4ヶ月前', '5ヶ月前', '6ヶ月前', '7ヶ月前', '8ヶ月前', '9ヶ月前', '10ヶ月前', '11ヶ月前', '12ヶ月前'],
            month : 0,
            keywords_flg: null,
            optionFlg : JSON.parse(getLS('user')).option_flg,
            DOWNLOAD_TYPE : DOWNLOAD_TYPE,
            isMobile: false,
            downloadCsvParam: null,
            showTree: false,
            folderId: '',
            folderSelect: false,
            selectCompletedIndexTree: 'selectCompletedIndexTree',
            showFolderFlg:false,
            radioVal: "default",
            countAllTabNum: false,
            is_sanitizing: 0, // PAC_5-2853
            pageNum:[10,20,50]
        }
    },
    methods: {
        ...mapActions({
            postActionMultiple: "circulars/postActionMultiple",
            search: "circulars/getListCompleted",
            delete: "circulars/deleteSend",
            getDetailCircularUserForCompleted: "circulars/getDetailCircularUserForCompleted",
            downloadFile: "circulars/downloadFile",
            getOriginCircularUrlForCompleted: "circulars/getOriginCircularUrlForCompleted",
            downloadReserve: "circulars/downloadReserve",
            downloadCsvReserve: "circulars/downloadCsvReserve",
            getMyFolders: "circulars/getMyFolders",
            getLimit: "setting/getLimit",
        }),
        onSearch: async function (resetPaging) {
            this.selectAll = false;
            let info = {
                kind            : this.filter.kind,
                filename        : this.filter.filename,
                senderName      : this.filter.senderName,
                senderEmail     : this.filter.senderEmail,
                destEnv         : this.filter.destEnv,
                fromdate        : this.filter.fromdate,
                todate          : this.filter.todate,
                finishedDate    : this.filter.finishedDate,
                receiverName    : this.filter.receiverName,
                receiverEmail   : this.filter.receiverEmail,
                page            : resetPaging ? 1 : this.pagination.currentPage,
                limit           : this.pagination.limit,
                orderBy         : this.orderBy,
                orderDir        : this.orderDir,
                         templateFrom   : this.filter.templateFrom,
                         templateTo     : this.filter.templateTo,
                         templateNum    : this.filter.templateNum,
                         templateText   : this.filter.templateText,
            };
            var data = await this.search(info);
            this.listData               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
            this.month                  = this.filter.finishedDate;
        //    this.selected               = [];

            this.updateDownloadCsvParam();

            if(this.isMobile){
                this.searchAreaFlgMobile = false;
            }
        },
        onSelectAll() {
          this.selectAll = !this.selectAll;
          this.listData.map(item=> {item.selected = this.selectAll; return item});

          //PAC_5-976 受信データが含まれる場合、削除ボタン非活性化
            if(this.selected.filter(e => e.circular_kind == 0).length>0){
                this.hasReceived = true;
            }else{
                this.hasReceived = false;
            }
        },
        showDialogDelete(){ 
            this.hasUndownloaded = false;            
            for (let i in this.selected) {
                if(this.selected[i].circular_status == 2){
                    this.hasUndownloaded = true;
                    break;
                }                
            }
            this.confirmDelete = true;
        },
        clearRadioStatus(e){
          // Because the native click event will be executed twice, the first time on the label tag 
          // and the second time on the input tag, this processing is required
          if (e.target.tagName === 'INPUT' || this.input.stampHistory == false || this.countAllTabNum == true){return}
          this.input.stampHistory = false;
          this.radioVal = "default";
        },
        changeRadioStatus(e){
          // Because the native click event will be executed twice, the first time on the label tag 
          // and the second time on the input tag, this processing is required
          if (e.target.tagName === 'INPUT' || this.input.stampHistory == true || this.countAllTabNum == true){return}
          this.countAllTabNum = true;
          this.$vs.dialog({
            type: 'confirm',
            color: 'primary',
            title: `確認`,
            acceptText: 'はい',
            cancelText: 'いいえ',
            text: `電子署名が付与されている場合、回覧履歴を付けてダウンロードをすると回覧時の署名が無効になります。`,
            accept: async () => {
              this.input.stampHistory = true;
              this.radioVal = "stampHistory";
              this.countAllTabNum = false;
            },
            cancel: async () => {
              this.input.stampHistory = false;
              this.radioVal = "default";
              this.countAllTabNum = false;
            },
          });
        },
        showDialogDownload(){
            this.hasUndownloaded = false;
            for (let i in this.selected) {
                if(this.selected[i].circular_status == 2){
                    this.hasUndownloaded = true;
                    break;
                }
            }
            if(this.selected.length == 1 && this.selected[0].file_names.split(',').length == 1){
                // .pdf, .docx, .xlsx
                var pos = this.selected[0].file_names.lastIndexOf('.');
                this.inputMaxLength = 50 - this.selected[0].file_names.substr(pos).length;
            }else{
                // .zip
                this.inputMaxLength = 46;
            }
            this.input.filename = '';
            this.confirmDownload = true;
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
            await this.postActionMultiple({
                action: 'deleteCompleted',
                info: {cids: this.getSelectedID(), finishedDate: this.month}
            });
            this.onSearch(false);
        },
        onSaveLongtermModal: function() {
            this.folderId = '';
            this.showTree = false;
            this.folderSelect = false;
            this.checkKeywordsLenFlg = false;
            if (this.selected.length > 1){
                this.keywords = '';
                this.confirmSaveLongTerm = true;
            }else {
                Axios.get(`${config.BASE_API_URL}/long-term/${this.selected[0].id}`)
                    .then(response => {
                        if (response.data.item){
                            this.keywords = response.data.item.keyword;
                        }else{
                            this.keywords = '';
                            if(this.showFolderFlg){
                                this.showTree = true;
                            }
                        }
                        this.confirmSaveLongTerm = true;
                    })
                    .catch(error => {
                        this.confirmSaveLongTerm = true;
                    });
            }
        },
        onSaveLongTerm: async function () {
            this.confirmSaveLongTerm = false;
            if(this.showTree && this.folderId == '') {
                this.folderSelect = true;
                this.confirmSaveLongTerm = true;
            }else{
                //PAC_5-2070対応
                if(this.keywords == ''){
                    //PAC_5-2070対応
                    this.keywords_flg = 0;
                    await this.postActionMultiple({
                        action: 'storeMultipleCircular',
                        info: {cids: this.getSelectedID(), keyword: this.keywords, finishedDate: this.month, keyword_flg: this.keywords_flg, folderId: this.folderId}
                    });
                    this.onSearch(false);
                    this.keywords = null;
                }else{
                    if(this.keywords && this.keywords.length > 200){
                        this.checkKeywordsLenFlg = true;
                        this.confirmSaveLongTerm = true;
                    }else{
                        this.confirmSaveLongTerm = false;
                        await this.postActionMultiple({
                            action: 'storeMultipleCircular',
                            info: {cids: this.getSelectedID(), keyword: this.keywords, finishedDate: this.month, folderId: this.folderId}
                        });
                        this.onSearch(false);
                        this.keywords = null;
                    }
                }
            }
        },
        getSelectedID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
        },
        // PAC_5-1466　回覧情報を取得する
        getSelectedStatus(){
          let select_status = [];
          this.selected.forEach((item, stt) => {
            select_status.push(item.circular_status);
          });
          return select_status;
        },
        async onShowReading(tr){
            this.click++

            if(this.click == 1){
                var root = this;
                var time = setTimeout(async function(){
                    root.click = 0;
                    root.itemReading = tr;
                    root.$store.dispatch('updateLoading', true);
                    root.itemReadingDetail = await root.getDetailCircularUserForCompleted({id: tr.id, finishedDate: root.month});
                    root.showReading = true;
                    root.$store.dispatch('updateLoading', false);
                },300)
            }else{
                clearTimeout(time);
                this.click = 0;
                var getOrigin =  await this.getOriginCircularUrlForCompleted({id: tr.id, finishedDate: this.month});
                if(getOrigin.originCircularUrl == null){
                    localStorage.setItem('finishedDate', this.month);
                    this.$router.push('/completed/' + tr.id);
                }else{
                      this.openWindow(getOrigin.originCircularUrl);
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
            this.selectAll = this.compListData.every(item => item.selected);
            
            //PAC_5-976 受信データが含まれる場合、削除ボタン非活性化
            if(this.selected.filter(e => e.circular_kind == 0).length>0){
                this.hasReceived = true;
            }else{
                this.hasReceived = false;
            }
        },
        openWindow(url){
            window.open(url,'_blank');
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
        async onShowReadingMobile(tr){
            var getOrigin =  await this.getOriginCircularUrlForCompleted({id: tr.id, finishedDate: this.month});
            if(getOrigin.originCircularUrl == null){
                localStorage.setItem('finishedDate', this.month);
                this.$router.push('/completed/'+tr.id);
            }else{
                this.openWindow(getOrigin.originCircularUrl);
            }
        },
        toView(id) {
            localStorage.setItem('finishedDate', this.month);
            this.$router.push('/completed/' + id);
        },
        updateDownloadCsvParam() {
            this.downloadCsvParam = {
                kind            : this.filter.kind,
                filename        : this.filter.filename,
                senderName      : this.filter.senderName,
                senderEmail     : this.filter.senderEmail,
                destEnv         : this.filter.destEnv,
                fromdate        : this.filter.fromdate,
                todate          : this.filter.todate,
                finishedDate    : this.filter.finishedDate,
                receiverName    : this.filter.receiverName,
                receiverEmail   : this.filter.receiverEmail,
                orderBy         : this.orderBy,
                orderDir        : this.orderDir,
                templateFrom    : this.filter.templateFrom,
                templateTo      : this.filter.templateTo,
                templateNum     : this.filter.templateNum,
                templateText    : this.filter.templateText,
                circular_type   : 'completed'
            };
        },
        onDownloadCsvReserve() {
            this.downloadCsvParam.selected_ids = Object.assign([],this.getSelectedID());
            this.downloadCsvReserve(this.downloadCsvParam);
        },
        setFolderId(id){
            this.folderId = id;
        }
    },
    computed:{
        compListData: function() {
            let data = [];
            this.listData.forEach((item) => {
                item.kind = this.circular_kind[item.circular_kind];
                /*if (item.circular_kind == 0) {
                    item.kind += '（'+ this.env[item.sender_env] + '）';
                }*/
                data.push(item);
            });
            return data;
        },
        selected() {
            return this.listData.filter(item => item.selected);
        },
        isEmtyItemReading() {
            for(let i in this.itemReading) return false;
            return true
        },
            },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        'filter.finishedDate': function () {
            // 完了日時From/Toの日付範囲を設定
            let year = new Date().getFullYear();
            let month = new Date().getMonth() + 1 - this.filter.finishedDate;

            // 月は昨年の時
            if (this.filter.finishedDate >= new Date().getMonth() + 1) {
                year = year - 1;
                month = month + 12;
            }
            // 月の日数
            let lastDay = new Date(year, month, 0).getDate()
            this.filter.fromdate = year + '-' + month + '-01';
            this.filter.todate = year + '-' + month + '-' + lastDay;
            this.completedConfigDate.minDate = year + '-' + month + '-01';
            this.completedConfigDate.maxDate = year + '-' + month + '-' + lastDay;
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
    async created() {
      
        // Check Mobile
        if (
          /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
            navigator.userAgent
          )
        ) {
          this.isMobile = true
        }
        this.input.stampHistory = false;
        this.radioVal = "default";
        var $this = this;
        Axios.get(`${config.BASE_API_URL}/myinfo`)
            .then(response => {
                const myInfo = response.data ? response.data : [];

                Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
                    .then(response => {
                        if (response.data && response.data.data) {
                            this.is_sanitizing = response.data.data.sanitizing_flg; // PAC_5-2853
                          if (this.optionFlg == 2){
                            this.canStoreCircular = false;
                          }else {
                            this.canStoreCircular = response.data.data.long_term_storage_flg;
                            this.showFolderFlg = response.data.data.long_term_folder_flg;
                          }
                            if (response.data.data.template_flg && response.data.data.template_search_flg == 1) {
                                this.canUseTemplate = myInfo.data.info.template_flg;
                            }
                        }
                    })
                    .catch(error => {
                        return [];
                    });
            })
            .catch(error => {
                return [];
            });
        await (async () => {
            this.settingLimit = await this.getLimit();
            if (this.settingLimit == null) {
                this.settingLimit = {};
            }
            if (this.settingLimit && this.settingLimit.default_stamp_history_flg == 1) {
                this.input.stampHistory = true;
                this.radioVal = "stampHistory";
            }
        })()
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

        // 完了日時From/Toの日付範囲を設定
        let year = new Date().getFullYear();
        let month = new Date().getMonth() + 1;
        // 月の日数
        let lastDay = new Date(year, month, 0).getDate()
        this.completedConfigDate.minDate = year + '-' + month + '-01';
        this.completedConfigDate.maxDate = year + '-' + month + '-' + lastDay;
    },
    activated() {
        this.$route.meta.back=false
    }
}

</script>

<style lang="stylus">
    .detail{
        .label{ background: #b3e5fb; padding: 3px; }
        .info{  padding: 3px 3px 3px 5px; }
    }
    @media only screen and (min-width: 901px) {
        .vs-lg-2 {
            width: 20%!important;
    }
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
  
  input{
    transform: scale(1);
  }

  .sends-mobile-title{
    text-align: center;
    margin-bottom: 15px;
    padding-top: 15px;
  }

  .square{
    margin: 0 auto;
    text-align: center;
  }

  .sends-mobile-select-button{
    margin-left: auto;
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

@media( max-width: 600px ) {
  #sends-page-mobile.mobile{
    top: -65px;
  }
}

@media( max-width: 240px ) {
  #sends-page-mobile.mobile{
    .con-vs-card.select-panel{
      margin-bottom: 15px !important;
    }
    .vs-card--content{
      margin-bottom: 0;
    }

    table{
      td{
        padding: 10px 0;
        >div{
          width: 90%;
        }
      }
    }
  }
}

</style>