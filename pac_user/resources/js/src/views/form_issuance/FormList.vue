<template>
    <div>
    <div id="sends-page" style="position: relative;">
        <vs-row>
            <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?8:11.5" style="transition: width .2s;">
                <vs-card style="margin-bottom: 0">
                    <vs-tabs v-model="tab_report_info">
                        <vs-tab label="明細" @click="showReading=false">
                            <vs-row>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-input class="inputx w-full" label="明細ID" v-model="filter.idOther"/>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-input class="inputx w-full" label="明細名" v-model="filter.filenameOther"/>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-input class="inputx w-full" label="取引先" v-model="filter.receiverNameOther"/>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-select class="selectExample w-full" id="reviewStatusOther" v-model="reviewStatusOther" label="状況">
                                        <vs-select-item value="0" text="回覧前" />
                                        <vs-select-item value="1" text="回覧中" />
                                        <vs-select-item value="2" text="完了" />
                                    </vs-select>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-select class="selectExample w-full" id="finishedDateOther" v-model="finishedDateOther" label="完了日時">
                                        <vs-select-item :key="index" :value="index" :text="date" v-for="(date, index) in finishedTimeList" />
                                    </vs-select>
                                </vs-col>
                            </vs-row>
                          <vs-row>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                              <vs-input class="inputx w-full" label="基準日From" v-model="filter.fromReferenceDateOther"/>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                              <vs-input class="inputx w-full" label="基準日To" v-model="filter.toReferenceDateOther"/>
                            </vs-col>
                          </vs-row>
                          <vs-row v-for="(field, index) in selectIndex" v-bind:key="index" :index="index">
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                              <vs-select class="selectExample w-full" label="明細項目設定" v-model="filter.indexes[index].id" @change="onChangeExample(filter.indexes[index].id,index)">
                                <vs-select-item v-for="long in frmIndex" v-bind:key="long.id" :value="long.id" :text="long.index_name" />
                              </vs-select>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2" v-if="filter.indexes[index].type == 1">
                              <vs-input class="inputx w-full" label="設定内容" v-model="filter.indexes[index].fromvalue"/>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2"  v-if="filter.indexes[index].type != 1">
                              <vs-input class="inputx w-full" label="From" v-model="filter.indexes[index].fromvalue"/>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2" v-show="filter.indexes[index].type == 0 || filter.indexes[index].type == 2">
                              <vs-input class="inputx w-full" label="To" v-model="filter.indexes[index].tovalue"/>
                            </vs-col>
                            <vs-col vs-type="flex" vs-lg="1" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                              <vs-button label=" " class="square" color="danger" v-on:click="removeIndex(index)"> x </vs-button>
                            </vs-col>
                          </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-align="center" v-show="frmIndex.length > 0">
                                    <vs-button class="square" color="success" v-on:click="addIndex"> + </vs-button>
                                </vs-col>
                                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                                    <vs-button class="square" color="primary" v-on:click="onSearchOther(true)"><i class="fas fa-search"></i> 検索</vs-button>
                                    <vs-button class="square" color="primary" @click="showDialogExport"><i class="fas fa-download"></i> Export</vs-button>
                                </vs-col>
                            </vs-row>
                        </vs-tab>
                        <vs-tab label="請求書" @click="showReading=false">
                            <vs-row>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-input class="inputx w-full" label="明細ID" v-model="filter.id"/>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-input class="inputx w-full" label="明細名" v-model="filter.filename"/>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-input class="inputx w-full" label="取引先" v-model="filter.receiverName"/>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <vs-select class="selectExample w-full" id="reviewStatus" v-model="reviewStatus" label="状況">
                                        <vs-select-item value="0" text="回覧前" />
                                        <vs-select-item value="1" text="回覧中" />
                                        <vs-select-item value="2" text="完了" />
                                    </vs-select>
                                </vs-col>
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <vs-select class="selectExample w-full" id="finishedDate" v-model="finishedDate" label="完了日時">
                                            <vs-select-item  :key="index" :value="index" :text="date" v-for="(date, index) in finishedTimeList" />
                                        </vs-select>
                                        <span @click="onAroundArrow">
                                            <vs-icon id="arrow" class="mt-5 around_return" icon="keyboard_arrow_down" size="medium" color="primary"></vs-icon>
                                        </span>
                                    </vs-col>
                            </vs-row>
                            <vs-row v-show="searchAreaFlg">
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <div class="w-full">
                                        <label for="filter_fromdate" class="vs-input--label">売上計上日From</label>
                                        <div class="vs-con-input">
                                            <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" :config="configDate"></flat-pickr>
                                        </div>
                                    </div>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <div class="w-full">
                                        <label for="filter_todate" class="vs-input--label">売上日計上To</label>
                                        <div class="vs-con-input">
                                            <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" :config="configDate"></flat-pickr>
                                        </div>
                                    </div>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <div class="w-full">
                                        <label for="filter_fromdateKijitu" class="vs-input--label">入金期日From</label>
                                        <div class="vs-con-input">
                                            <flat-pickr class="w-full" v-model="filter.fromdateKijitu" id="filter_fromdateKijitu" :config="configDate"></flat-pickr>
                                        </div>
                                    </div>
                                </vs-col>
                                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                    <div class="w-full">
                                        <label for="filter_todateKijitu" class="vs-input--label">入金期日To</label>
                                        <div class="vs-con-input">
                                            <flat-pickr class="w-full" v-model="filter.todateKijitu" id="filter_todateKijitu" :config="configDate"></flat-pickr>
                                        </div>
                                    </div>
                                </vs-col>
                                <vs-row>
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <div class="w-full">
                                            <label for="filter_fromdateHakko" class="vs-input--label">請求日From</label>
                                            <div class="vs-con-input">
                                                <flat-pickr class="w-full" v-model="filter.fromdateHakko" id="filter_fromdateHakko" :config="configDate"></flat-pickr>
                                            </div>
                                        </div>
                                    </vs-col>
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <div class="w-full">
                                            <label for="filter_todateHakko" class="vs-input--label">請求日To</label>
                                            <div class="vs-con-input">
                                                <flat-pickr class="w-full" v-model="filter.todateHakko" id="filter_todateHakko" :config="configDate"></flat-pickr>
                                            </div>
                                        </div>
                                    </vs-col>
                                </vs-row>
                            </vs-row>
                            <vs-row class="mt-3">
                                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                                    <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                                    <vs-button class="square" color="primary" v-on:click="showDialogExport()"><i class="fas fa-download"></i> Export</vs-button>
                                </vs-col>
                            </vs-row>
                        </vs-tab>
                    </vs-tabs>
                </vs-card>
                
                <vs-card  v-if="tab_report_info">
<!--
                    <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary"  @click="showDialogDownload"
                               v-bind:disabled="selected.length == 0"><i class="fas fa-download"></i> PDFダウンロード</vs-button>
-->
                    <!-- PAC_5-2239 -->
                    <vs-dropdown>
                        <vs-button id="button5" class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary" type="filled" v-bind:disabled="!hasDownload">
                            <i class="fas fa-download"></i> ダウンロード予約</vs-button>
                        <vs-dropdown-menu v-show="hasDownload" class="download-complete">
                            <li v-if="hasDownload" class="vx-dropdown--item">
                              <a class="vx-dropdown--item-link" style="width: 202px;text-align: center;">
                                <vs-radio class="mb-2 mt-2" vs-value="default" vs-name="radioVal" @click.native.stop="clearRadioStatus($event,1)" v-model="radioVal" :disabled="countAllTabNum">完了済みファイル</vs-radio>
                              </a>
                            </li>
                            <li v-if="hasDownload" class="vx-dropdown--item">
                              <a class="vx-dropdown--item-link" style="width: 202px;text-align: center;">
                                <vs-radio class="mb-2 mt-2" vs-value="stampRequestHistory" vs-name="radioVal"  v-model="radioVal" @click.native.stop="changeRadioStatus($event,1)">回覧履歴を付ける</vs-radio>
                              </a>
                            </li>
                            <vs-button class="download-item download-complete-btn" type="filled" color="primary"  @click="showDialogDownload" style="width: 90%; margin: auto; display: flex;" :disabled="countAllTabNum">
                                <i class="fas fa-download"></i> ダウンロード予約</vs-button>
                        </vs-dropdown-menu>
                    </vs-dropdown>
                    <!-- PAC_5-2239 -->
                    <vs-button class="square"  color="danger" v-on:click="showDialogDelete"
                        v-bind:disabled="selected.length == 0 || this.hasReceived"  ><i class="far fa-trash-alt"></i> 削除</vs-button>

                    <vs-table class="mt-3 custome-event" :data="compListData" noDataText="データがありません。"
                        sst @sort="handleSort" stripe >
                        <template slot="thead">
                            <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                            <vs-th sort-key="company_frm_id" >明細ID </vs-th>
                            <vs-th sort-key="frm_name" >明細名 </vs-th>
                            <vs-th sort-key="customer_name" >取引先</vs-th>
                            <vs-th sort-key="invoice_amt">請求金額</vs-th>
                            <vs-th sort-key="trading_date">売上計上日</vs-th>
                            <vs-th sort-key="payment_date">入金期日</vs-th>
                            <vs-th sort-key="invoice_date">請求日</vs-th>
                            <vs-th sort-key="circular_status">状況</vs-th>
                            <vs-th sort-key="update_user">最終更新者</vs-th>
                            <vs-th sort-key="update_at">最終更新日</vs-th>
                            <vs-th></vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <vs-td><vs-checkbox  :value="tr.selected" @click="onRowCheckboxClick(tr)" /></vs-td>
                                <td class="min-width-100"  @click="onShowReading(tr)"><div v-text="tr.company_frm_id"></div></td>
                                <td class="min-width-180"  @click="onShowReading(tr)"><div v-text="tr.frm_name"></div></td>
                                <td class="min-width-180"  @click="onShowReading(tr)"><div v-text="tr.customer_name"></div></td>
                                <td class="min-width-50"   @click="onShowReading(tr)"><div v-if="tr.invoice_amt" class="text-right">{{tr.invoice_amt_comma}}</div></td>
                                <td class="min-width-100"  @click="onShowReading(tr)">{{tr.trading_date | moment("YYYY/MM/DD")}}</td>
                                <td class="min-width-100"  @click="onShowReading(tr)">{{tr.payment_date | moment("YYYY/MM/DD")}}</td>
                                <td class="min-width-100"  @click="onShowReading(tr)">{{tr.invoice_date | moment("YYYY/MM/DD")}}</td>
                                <td class="min-width-100"  @click="onShowReading(tr)">{{options_status[tr.circular_status]}}</td>
                                <td class="min-width-100"  @click="onShowReading(tr)"><div v-text="tr.update_user"></div></td>
                                <td class="min-width-100"  @click="onShowReading(tr)">{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                                <td @click="onShowReading(tr)"></td>
                            </vs-tr>
                        </template>
                    </vs-table>

                    <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示</div></div>
                    <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                </vs-card>

                <vs-card  v-if="!tab_report_info">
<!--
                    <vs-button class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary"  @click="showDialogDownloadOther"
                               v-bind:disabled="selectedRow.length == 0"><i class="fas fa-download"></i> PDFダウンロード</vs-button>
-->
                    <!-- PAC_5-2239 -->
                    <vs-dropdown>
                        <vs-button id="button5" class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary" type="filled" v-bind:disabled="!hasDownloadRow">
                            <i class="fas fa-download"></i> ダウンロード予約</vs-button>
                        <vs-dropdown-menu v-show="hasDownloadRow" class="download-complete">
                            <li v-if="hasDownloadRow" class="vx-dropdown--item">
                              <a class="vx-dropdown--item-link">
                                <vs-radio class="mb-2 mt-2" vs-value="default" vs-name="radioVal2" @click.native.stop="clearRadioStatus($event,2)" v-model="radioVal2" :disabled="countAllTabNum">完了済みファイル</vs-radio>
                              </a>
                            </li>
                            <li v-if="hasDownloadRow" class="vx-dropdown--item">
                                <a class="vx-dropdown--item-link">
                                    <vs-radio class="mb-2 mt-2" vs-value="stampOtherHistory" vs-name="radioVal2"  v-model="radioVal2" @click.native.stop="changeRadioStatus($event,2)">回覧履歴を付ける</vs-radio>
                                </a>
                            </li>
                            <vs-button class="download-item download-complete-btn" type="filled" color="primary"  @click="showDialogDownloadOther" style="width: 90%; margin: auto; display: flex;" :disabled="countAllTabNum">
                                <i class="fas fa-download"></i> ダウンロード予約</vs-button>
                        </vs-dropdown-menu>
                    </vs-dropdown>
                    <!-- PAC_5-2239 -->
                    <vs-button class="square"  color="danger" v-on:click="showDialogDeleteOther"
                        v-bind:disabled="selectedRow.length == 0 || this.hasReceived"  ><i class="far fa-trash-alt"></i> 削除</vs-button>

                    <vs-table class="mt-3 custome-event" :data="compListDataOther" noDataText="データがありません。"
                        sst @sort="handleSortOther" stripe >
                        <template slot="thead">
                            <vs-th class="width-50"><vs-checkbox :value="selectAllOther" @click="onSelectAllOther" /></vs-th>
                            <vs-th sort-key="company_frm_id">明細ID </vs-th>
                            <vs-th sort-key="frm_name">明細名 </vs-th>
                            <vs-th sort-key="customer_name">取引先</vs-th>
                            <vs-th sort-key="reference_date">基準日</vs-th>
                            <vs-th sort-key="circular_status">状況</vs-th>
                            <vs-th sort-key="update_user">最終更新者</vs-th>
                            <vs-th sort-key="update_at">最終更新日</vs-th>
                            <vs-th></vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <vs-td><vs-checkbox  :value="tr.selectedOther" @click="onRowCheckboxOtherClick(tr)" /></vs-td>
                                <td class="min-width-100"  @click="onShowReadingOther(tr)"><div v-text="tr.company_frm_id"></div></td>
                                <td class="min-width-200"  @click="onShowReadingOther(tr)"><div v-text="tr.frm_name"></div></td>
                                <td class="min-width-200"  @click="onShowReadingOther(tr)"><div v-text="tr.customer_name"></div></td>
                                <td class="min-width-100"  @click="onShowReadingOther(tr)">{{tr.reference_date | moment("YYYY/MM/DD")}}</td>
                                <td class="min-width-100"  @click="onShowReadingOther(tr)">{{options_status[tr.circular_status]}}</td>
                                <td class="min-width-200"  @click="onShowReadingOther(tr)"><div v-text="tr.update_user"></div></td>
                                <td class="min-width-100"  @click="onShowReadingOther(tr)">{{tr.update_at | moment("YYYY/MM/DD HH:mm")}}</td>
                                <td @click="onShowReadingOther(tr)"></td>
                            </vs-tr>
                        </template>
                    </vs-table>

                    <div><div class="mt-3">{{ paginationOther.totalItem }} 件中 {{ paginationOther.from }} 件から {{ paginationOther.to }} 件までを表示</div></div>
                    <vx-pagination :total="paginationOther.totalPage" :currentPage.sync="paginationOther.currentPage"></vx-pagination>
                </vs-card>

            </vs-col>

            <vs-col class="preview-wrapper" :vs-w="showReading?3:0.5" :vs-xs="showReading?12:0" :vs-sm="showReading?5:0.5" >
                <div  @click="showRead()">
                    <div class="button" v-if="!showReading" style="text-align: center; cursor: pointer;">
                        <i class="fas fa-caret-left" style="font-size: 40px; color:rgba(var(--vs-primary),1);"></i>
                        <div class="text" style="margin: 0 auto;line-height: 17px;"><p> 閲<br>覧<br>ウ<br>ィ<br>ン<br>ド<br>ウ<br>を<br>表<br>示<br>す<br>る</p></div>
                    </div>
                </div>

                <div v-if="showReading" style="overflow-y:scroll;overflow-x:hidden;">
                    <div style="height: calc(100vh - 110px);  flex-direction: column;background: #fff" class="show-flex">

                        <div class="button2 flex-item ml-3" @click="showReading=false" style="cursor: pointer; position: relative;">
                            <i class="fas fa-caret-right" style="font-size: 40px; color: rgba(var(--vs-primary),1);"></i>
                            <div class="text" style="position: absolute; top: 10px; left: 20px;">閉じる</div>
                        </div>
                        
                        <template>
                            <vs-card  v-if="itemReadingDetail.circular.first_page_data" class="main-flex-item" style="position: static; height: calc(100vh - 450px);">
                                <div class="preview" style="width: 100%; position: static; overflow: hidden; height: calc(100vh - 450px);">
                                    <img :src="'data:image/jpeg;base64,' + itemReadingDetail.circular.first_page_data" alt="" style="height: calc(100vh - 450px);">
                                </div>
                            </vs-card>
                            <vs-card v-else class="main-flex-item">プレビューするレコードを選択してください</vs-card>

                            <vs-row vs-type="flex" style="padding: 10px ">
                                <div class="break"></div>
                            </vs-row>

                            <vs-card class="detail flex-item mb-0">
                                <h3>詳細内容表示エリア</h3>
                                <vs-row class="mt-3">
                                    <vs-col vs-w="4" class="label">明細ID</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.company_frm_id }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">明細分類</vs-col>
                                    <vs-col  v-if="tab_report_info" vs-w="8" class="info">請求書</vs-col>
                                    <vs-col  v-if="!tab_report_info" vs-w="8" class="info">明細</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">明細名</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.frm_name }}</vs-col>
                                </vs-row>
                                <vs-row>
                                    <vs-col vs-w="4" class="label">取引先</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.customer_name }}</vs-col>
                                </vs-row>
                                <vs-row  v-if="tab_report_info">
                                    <vs-col vs-w="4" class="label">請求金額</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.invoice_amt_comma }}</vs-col>
                                </vs-row>
                                <vs-row  v-if="tab_report_info">
                                    <vs-col vs-w="4" class="label">売上計上日</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.trading_date | moment("YYYY/MM/DD") }}</vs-col>
                                </vs-row>
                                <vs-row  v-if="tab_report_info">
                                    <vs-col vs-w="4" class="label">入金期日</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.payment_date | moment("YYYY/MM/DD") }}</vs-col>
                                </vs-row>
                                <vs-row  v-if="tab_report_info">
                                    <vs-col vs-w="4" class="label">請求日</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.invoice_date | moment("YYYY/MM/DD") }}</vs-col>
                                </vs-row>
                                <vs-row  v-if="!tab_report_info">
                                    <vs-col vs-w="4" class="label">基準日</vs-col>
                                    <vs-col vs-w="8" class="info">{{ itemReading.reference_date | moment("YYYY/MM/DD") }}</vs-col>
                                </vs-row>
                                <vs-row v-for="(item, key) in itemReadingDetail.frm_data_array" :key="key">
                                     <vs-col vs-w="4" class="label">{{ key }}</vs-col>
                                     <vs-col vs-w="8" class="info">{{ item }}</vs-col>
                                </vs-row>

                            </vs-card>

                            <vs-row vs-type="flex" style="padding: 10px">
                                <div class="break"></div>
                            </vs-row>

                            <vs-card class="detail flex-item mb-1">
                                <h3>明細詳細</h3>
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
                                            {{ recived_status[userReceive.circular_status] }}
                                        </vs-col>
                                        <vs-col vs-w="4" class="info" v-if="userReceive.isOutCopany != 0">
                                            {{ out_status[userReceive.status] }}
                                        </vs-col>
                                    </vs-row>
                                </div>
                                <vs-row class="mt-1" v-if="itemReadingDetail.viewingUser[0]">
                                    <vs-col vs-w="11" class="label">閲覧</vs-col>
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
                        </template>
                        
                    </div>
                </div>
                <div v-if="showReading" style="max-height: 45px;">
                    <div style="flex-direction: column;background: #fff" class="show-flex">
                        <div v-if="tab_report_info" class="flex-item text-right mr-6">
                            <vs-row class="mr-0">
                                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end">
                                    <div v-if="isEmtyItemReading"><font color="red">詳細の表示権限がありません　</font></div>
                                    <vs-button v-if="!itemReadingDetail.circular.origin_circular_url" class="square mr-0" color="primary" @click="toView(itemReading.circular_id,itemReading.id)" :disabled="isEmtyItemReading"> 表示</vs-button>

                                    <vs-button v-if="itemReadingDetail.circular.origin_circular_url" class="square" color="primary"
                                            @click="openWindow(itemReadingDetail.circular.origin_circular_url)" :disabled="isEmtyItemReading"> 表示</vs-button>
                                            <label>{{itemReadingDetail.circular.origin_circular_url}}</label>
                                </vs-col>
                            </vs-row>
                        </div>
                        <div v-if="!tab_report_info" class="flex-item text-right mr-6">
                            <vs-row class="mr-0">
                                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end">
                                    <div v-if="isEmtyItemReading"><font color="red">詳細の表示権限がありません　</font></div>
                                    <vs-button v-if="!itemReadingDetail.circular.origin_circular_url" class="square mr-0" color="primary" @click="toViewOther(itemReading.circular_id,itemReading.id)" :disabled="isEmtyItemReading"> 表示</vs-button>

                                    <vs-button v-if="itemReadingDetail.circular.origin_circular_url" class="square" color="primary"
                                            @click="openWindow(itemReadingDetail.circular.origin_circular_url)" :disabled="isEmtyItemReading"> 表示</vs-button>
                                            <label>{{itemReadingDetail.circular.origin_circular_url}}</label>
                                </vs-col>
                            </vs-row>
                        </div>
                    </div>
                </div>

            </vs-col>
        </vs-row>
               
        <vs-popup classContent="popup-example"  title="明細の削除" :active.sync="confirmDelete">
            <div v-if="selected.length>1">{{ selected.length }}件の明細を削除します。</div>
            <div v-if="selected.length==1">
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">明細ID</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].company_frm_id }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">明細名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selected[0].frm_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">取引先</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360"><div v-text="selected[0].customer_name"></div></vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この明細を削除します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
        <vs-popup classContent="popup-example"  title="明細の削除" :active.sync="confirmDeleteOther">
            <div v-if="selectedRow.length>1">{{ selectedRow.length }}件の明細を削除します。</div>
            <div v-if="selectedRow.length==1">
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">明細ID</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selectedRow[0].company_frm_id }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">明細名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ selectedRow[0].frm_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">取引先</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-type="flex" vs-w="8" class="max-width-360"><div v-text="selectedRow[0].customer_name"></div></vs-col>
                </vs-row>
                <vs-row class="mt-3"><vs-col vs-type="flex" vs-w="12">この明細を削除します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDeleteOther" color="danger">削除</vs-button>
                    <vs-button @click="confirmDeleteOther=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="明細Export" :active.sync="confirmExport">
            <div>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex">明細種別：
                        <vs-col v-if="tab_report_info" vs-type="flex" vs-w="2">請求書</vs-col>
                        <vs-col v-if="!tab_report_info" vs-type="flex" vs-w="2">明細</vs-col>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex">条件　　：
                        <vs-col vs-type="flex" vs-w="10" >
                            <vs-row>
                                <div vs-type="flex" v-if="searchMessage">{{searchMessage}}</div>
                            </vs-row>
                        </vs-col>
                    </vs-col>
                </vs-row>

                <vs-row class="mt-3">
                    <vs-col vs-type="flex">明細Expテンプレートの選択</vs-col>
                </vs-row>

                    <vs-table class="mt-3" :data="compListDataTemplate" noDataText="テンプレートデータがありません。"
                        sst @sort="handleSortOther" stripe max-height="175px" style="border:ridge;">
                        <template slot-scope="{data}">
                            <label>
                                <input type="radio" id="radio-tmp" value="Simple" name="radio-tmp"
                                    v-model="radio_template">
                                シンプル（すべての項目を出力）
                            </label>
                            
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                <label>
                                    <input type="radio" id="radio-tmp" @click="onSetFileName(tr.template_name)" :value="tr.frm_template_id" name="radio-tmp"
                                        v-model="radio_template">
                                    {{tr.template_name}}（{{tr.remarks}}）
                                </label>
                            </vs-tr>
                        </template>
                    </vs-table>

                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="2">出力形式：</vs-col>
                    <label for="radio-tmp-1">
                        <input type="radio" id="radio-tmp-1" value="Excel" name="radio-tmp-1"
                               v-model="radio_output"  >
                        Excel　
                    </label>
                    <label for="radio-tmp-2">
                        <input type="radio" id="radio-tmp-2" value="CSV" name="radio-tmp-2"
                               v-model="radio_output">
                        CSV
                    </label>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="3">ファイル名：</vs-col>
                          <span v-if="errors.has('file_name')" style="color:red;">
                              {{ errors.first("file_name") }}
                          </span>
                    <vs-col >
                        <vs-input placeholder="ファイル名"  name="file_name" :maxlength="128" v-validate='{ regex:/^[^\\\/:*>"<|]+$/ }' v-model="export_file_name"/>
                    </vs-col>
                    <label>省略時は、選択した明細Expテンプレートのファイル名になります。</label>
                </vs-row>
                <vs-row class="mt-3">
                        <vs-col v-if="tab_report_info">
                            ※ 売上計上日の新しい順に最大{{max_export_count}}件のデータをExportできます。最大件数を超えるデータは切り捨てられます。
                        </vs-col>
                        <vs-col v-if="!tab_report_info">
                            ※ 基準日の新しい順に最大{{max_export_count}}件のデータをExportできます。最大件数を超えるデータは切り捨てられます。
                        </vs-col>
                </vs-row>

            </div>
            <vs-row class="mt-6">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="confirmExport=false" color="dark" type="border">キャンセル</vs-button>
                    <vs-button @click="exportFormIssuanceList" :disabled="errors.has('file_name')">Export</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
        <vs-popup classContent="popup-example"  title="選択文書ダウンロード" :active.sync="confirmDownload">
            <vs-row>
                <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="input.filename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
            </vs-row>
            <div v-if="hasUndownloaded" class="mt-3 text-red">※「回覧中」の明細をダウンロードされません。</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDownloadReserve" color="primary">ダウンロード</vs-button>
                    <vs-button @click="confirmDownload=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
        <vs-popup classContent="popup-example"  title="選択文書ダウンロード" :active.sync="confirmDownloadOther">
            <vs-row>
                <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="input.filename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
            </vs-row>
            <div v-if="hasUndownloaded" class="mt-3 text-red">※「回覧中」の明細をダウンロードされません。</div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDownloadReserveOther" color="primary">ダウンロード</vs-button>
                    <vs-button @click="confirmDownloadOther=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>

        <div id="sends-page-mobile" style="position: relative;">
            <vs-row>
                <vs-col :vs-w="showReading?9:11.5" vs-xs="12" :vs-sm="showReading?7:11.5" style="transition: width .2s;">
                    <div class="sends-mobile-title"><h3>明細一覧</h3></div>

                    <vs-tabs v-model="tab_report_info">
                        <vs-tab label="明細">
                            <vs-card style="margin-bottom: 0" class="select-panel">
                                <vs-row>
                                    <div style="width:100%" @click="onAroundArrowMobile">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mt-3 pr-2">
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
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <vs-input class="inputx w-full" label="ID" v-model="filter.id"/>
                                    </vs-col>
                                </vs-row>
                                <vs-row v-show="searchAreaFlg">
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <vs-input class="inputx w-full" label="明細名" v-model="filter.filename"/>
                                    </vs-col>
                                </vs-row>
                                <vs-row v-show="searchAreaFlg">
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <vs-input class="inputx w-full" label="取引先" v-model="filter.receiverName"/>
                                    </vs-col>
                                </vs-row>
                                <vs-row v-show="searchAreaFlg">
                                    <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                        <vs-select class="selectExample w-full" id="reviewStatusOther" v-model="reviewStatusOther" label="状況">
                                            <vs-select-item value="0" text="回覧前" />
                                            <vs-select-item value="1" text="回覧中" />
                                            <vs-select-item value="2" text="完了" />
                                        </vs-select>
                                    </vs-col>
                                </vs-row>
                                <vs-row class="mt-3" v-show="searchAreaFlg">
                                    <vs-col vs-type="flex">
                                        <vs-button class="square" color="primary" v-on:click="onSearchOther(true)">検索する</vs-button>
                                    </vs-col>
                                </vs-row>
                            </vs-card>

                            <vs-card>
                                <vs-table class="mt-3 custome-event" :data="compListDataOther" noDataText="データがありません。"
                                          sst @sort="handleSort" stripe >
                                    <template slot-scope="{data}">
                                        <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                            <td class="max-width-150"  @click="onShowReadingMobile(tr)">
                                                <div class="show-list">{{tr.company_frm_id}}</div>
                                                <div class="show-list">{{tr.frm_name}}</div>
                                            </td>
                                            <td @click="onShowReadingMobile(tr)">{{tr.update_at | moment("MM/DD HH:mm")}}</td>
                                        </vs-tr>
                                    </template>
                                </vs-table>
                                <div><div class="mt-3">{{ paginationOther.totalItem }} 件中 {{ paginationOther.from ? paginationOther.from : 0 }} 件から {{ pagination.to ? pagination.to : 0}} 件までを表示</div></div>
                                <vx-pagination :total="paginationOther.totalPage" :currentPage.sync="paginationOther.currentPage"></vx-pagination>
                            </vs-card>
                        </vs-tab>
                        <vs-tab label="請求書">
                            <vs-card style="margin-bottom: 0" class="select-panel">
                                    <vs-row>
                                        <div style="width:100%" @click="onAroundArrowMobile">
                                            <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mt-3 pr-2">
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
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <vs-input class="inputx w-full" label="ID" v-model="filter.id"/>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <vs-input class="inputx w-full" label="明細名" v-model="filter.filename"/>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <vs-input class="inputx w-full" label="取引先" v-model="filter.receiverName"/>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <vs-select class="selectExample w-full" id="reviewStatus" v-model="reviewStatus" label="状況">
                                                <vs-select-item value="0" text="回覧前" />
                                                <vs-select-item value="1" text="回覧中" />
                                                <vs-select-item value="2" text="完了" />
                                            </vs-select>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <div class="w-full">
                                                <label for="filter_fromdate" class="vs-input--label">売上計上日From</label>
                                                <div class="vs-con-input">
                                                    <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate_mobile" :config="configDate"></flat-pickr>
                                                </div>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <div class="w-full">
                                                <label for="filter_todate" class="vs-input--label">売上日計上To</label>
                                                <div class="vs-con-input">
                                                    <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate_mobile" :config="configDate"></flat-pickr>
                                                </div>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <div class="w-full">
                                                <label for="filter_fromdate" class="vs-input--label">入金期日From</label>
                                                <div class="vs-con-input">
                                                    <flat-pickr class="w-full" v-model="filter.fromdateKijitu" id="filter_fromdateKijitu_mobile" :config="configDate"></flat-pickr>
                                                </div>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <div class="w-full">
                                                <label for="filter_todate" class="vs-input--label">入金期日To</label>
                                                <div class="vs-con-input">
                                                    <flat-pickr class="w-full" v-model="filter.todateKijitu" id="filter_todateKijitu_mobile" :config="configDate"></flat-pickr>
                                                </div>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <div class="w-full">
                                                <label for="filter_fromdate" class="vs-input--label">請求日From</label>
                                                <div class="vs-con-input">
                                                    <flat-pickr class="w-full" v-model="filter.fromdateHakko" id="filter_fromdateHakko_mobile" :config="configDate"></flat-pickr>
                                                </div>
                                            </div>
                                        </vs-col>
                                    </vs-row>
                                    <vs-row v-show="searchAreaFlg">
                                        <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                                            <div class="w-full">
                                                <label for="filter_todate" class="vs-input--label">請求日To</label>
                                                <div class="vs-con-input">
                                                    <flat-pickr class="w-full" v-model="filter.todateHakko" id="filter_todateHakko_mobile" :config="configDate"></flat-pickr>
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
                                <vs-table class="mt-3 custome-event" :data="compListData" noDataText="データがありません。"
                                        sst @sort="handleSort" stripe >
                                    <template slot-scope="{data}">
                                        <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
                                            <td class="max-width-150"  @click="onShowReadingMobile(tr)">
                                                <div class="show-list">{{tr.company_frm_id}}</div>
                                                <div class="show-list">{{tr.frm_name}}</div>
                                            </td>
                                            <td @click="onShowReadingMobile(tr)">{{tr.update_at | moment("MM/DD HH:mm")}}</td>
                                        </vs-tr>
                                    </template>
                                </vs-table>
                                <div><div class="mt-3">{{ pagination.totalItem }} 件中 {{ pagination.from ? pagination.from : 0 }} 件から {{ pagination.to ? pagination.to : 0}} 件までを表示</div></div>
                                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
                            </vs-card>
                        </vs-tab>
                    </vs-tabs>
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
import Encoding from 'encoding-japanese';
import { Validator } from 'vee-validate';
import { DOWNLOAD_TYPE } from '../../enums/download_type';
import VxPagination from '@/components/vx-pagination/VxPagination.vue';

const dict = {
  custom: {
    file_name: {
      regex: "* ファイル名に使用できない次の文字は使用不可 \\/:*\"<>|"
    },
  }
    };
Validator.localize('ja', dict);

export default {
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination
    },
    data() {
        return {
            filter: {
                id: "",
                name: "",
                destEnv: "",
                fromdate: "",
                todate: "",
                fromdateKijitu: "",
                todateKijitu: "",
                fromdateHakko: "",
                todateHakko: "",
                fromReferenceDateOther: "",
                toReferenceDateOther: "",
                indexes : [],
            },
            tab_report_info: 0,
            finishedDate: "",
            finishedDateOther: "",
            selectAll: false,
            selectAllOther: false,
            listData:[],
            listDataOther:[],
            listDataTemplate:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            paginationOther:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "update_at",
            orderDir: "desc",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0
            },            
            confirmDelete: false,
            confirmDeleteOther: false,
            confirmSaveLongTerm: false,
            confirmDownload: false,
            confirmDownloadOther: false,
            confirmExport: false,
            confirmExportOther: false,
            hasUndownloaded: false,
            // 受信データが含まれる場合、削除ボタン非活性化
            hasReceived: false,
            // ダウンロード可能請求書帳票あれば、ダウンロードボタンを活性化
            hasDownload: false,
            // ダウンロード可能その他帳票あれば、ダウンロードボタンを活性化
            hasDownloadRow: false,
            showReading: false,
            //0,5:回覧前 1,4:回覧中 2,3:完了
            options_status: ['回覧前','回覧中','完了','完了','回覧中','回覧前','','','','削除'],
            recived_status: ['未通知','通知済/未読','既読','承認(捺印あり)','承認(捺印なし)','差戻し','差戻し(未読)'],
            circular_kind: ['受信', '送信'],
            out_status: ['-','未読','処理済'],
            itemPull: {},
            itemReading: {},
            itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}], frm_table_data: {} },
            // itemReadingDetail: {circular: {}, userSend: {}, userReceives:[{}]},
            click: 0,
            time: null,
            searchAreaFlg:false,
            keywords: '',
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
                stampOtherHistory: false,
                stampRequestHistory: false,
            },
          inputMaxLength: 46,
          radio_template:'Simple',
          radio_output:'Excel',
          export_file_name:'シンプル',
          searchMessage:'',
          finishedTimeList: ['当月', '1ヶ月前', '2ヶ月前', '3ヶ月前', '4ヶ月前', '5ヶ月前', '6ヶ月前', '7ヶ月前', '8ヶ月前', '9ヶ月前', '10ヶ月前', '11ヶ月前'],
          month : 0,
          monthOther : 0,
          max_export_count : 0,
          DOWNLOAD_TYPE : DOWNLOAD_TYPE,
          radioVal: "default",
          radioVal2: "default",
          countAllTabNum: false,
          selectIndex: [],
          frmIndex:[],
          frmIndexFlg:false,
            is_sanitizing: 0, // PAC_5-2853
          reviewStatusOther: 0,
          reviewStatus: 0,
        }
    },
    methods: {
        ...mapActions({
            addLogOperation: "logOperation/addLog",
            postActionMultiple: "formIssuance/postActionMultiple",
            search: "formIssuance/getListReport",
            searchOther: "formIssuance/getListReportOther",
            searchTemplate: "formIssuance/getListTemplate",
            searchTemplateOther: "formIssuance/getListTemplateOther",
            getDetailReport: "formIssuance/getDetailReport",
            getDetailReportOther: "formIssuance/getDetailReportOther",
            downloadFile: "circulars/downloadFile",
            getOriginCircularUrl: "circulars/getOriginCircularUrl",
            downloadReserve: "circulars/downloadReserve",
            exportFormIssuanceListToCSV: "formIssuance/exportFormIssuanceListToCSV",
            getFormIssuancesIndex: "formIssuance/getFormIssuancesIndex",
            getLimit: "setting/getLimit",
        }),
        onSearch: async function (resetPaging) {
            this.selectAll = false;
            let info = { id             : this.filter.id,  
                         filename       : this.filter.filename,
                         reviewStatus   : this.reviewStatus,
                         fromdate       : this.filter.fromdate,
                         todate         : this.filter.todate,  
                         fromdateKijitu : this.filter.fromdateKijitu,  
                         todateKijitu   : this.filter.todateKijitu,  
                         fromdateHakko  : this.filter.fromdateHakko,  
                         todateHakko    : this.filter.todateHakko,  
                         finishedDate   : this.finishedDate,
                         receiverName   : this.filter.receiverName,  
                         page           : resetPaging?1:this.pagination.currentPage,
                         limit          : this.pagination.limit,
                         orderBy        : this.orderBy,
                         orderDir       : this.orderDir,
                        };
            var data = await this.search(info);
            this.listData               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
            this.month                  = this.finishedDate;
            //this.selected               = [];
        },
        onSearchOther: async function (resetPaging) {
            this.selectAll = false;
            let info = { id             : this.filter.idOther,  
                         filename       : this.filter.filenameOther,
                         reviewStatus   : this.reviewStatusOther,
                         receiverName   : this.filter.receiverNameOther,
                         fromReferenceDate : this.filter.fromReferenceDateOther,
                         toReferenceDate : this.filter.toReferenceDateOther,
                         indexes        : this.filter.indexes,
                         finishedDate   : this.finishedDateOther,
                         page           : resetPaging?1:this.paginationOther.currentPage,
                         limit          : this.pagination.limit,
                         orderBy        : this.orderBy,
                         orderDir       : this.orderDir,
                        };
            var data = await this.searchOther(info);
            this.listDataOther          = data.data.map(item=> {item.selectedOther = false; return item});
            this.paginationOther.totalItem   = data.total;
            this.paginationOther.totalPage   = data.last_page;
            this.paginationOther.currentPage = data.current_page;
            this.paginationOther.limit       = data.per_page;
            this.paginationOther.from        = data.from;
            this.paginationOther.to          = data.to;
            this.monthOther                  = this.finishedDateOther;
            //this.selectedOther               = [];
        },
        onSearchTemplate: async function () {
            this.selectAll = false;
            let info = { id             : this.filter.id,  
                         filename       : this.filter.filename,
                         reviewStatus   : this.reviewStatus,
                         fromdate       : this.filter.fromdate,  
                         todate         : this.filter.todate,  
                         fromdateKijitu : this.filter.fromdateKijitu,  
                         todateKijitu   : this.filter.todateKijitu,  
                         fromdateHakko  : this.filter.fromdateHakko,  
                         todateHakko    : this.filter.todateHakko,  
                         receiverName   : this.filter.receiverName,  
                         orderBy        : this.orderBy,
                         orderDir       : this.orderDir,
                        };
            var data = await this.searchTemplate(info);
            this.editStatusChar(info);
            this.listDataTemplate               = data.data_export.data.map(item=> {item.selected = false; return item});
            this.max_export_count               = data.max_issu_export_count;
        },
        onSearchTemplateOther: async function (resetPaging) {
            this.selectAll = false;
            let info = { id             : this.filter.idOther,  
                         filename       : this.filter.filenameOther,
                         reviewStatus   : this.reviewStatusOther,
                         receiverName   : this.filter.receiverNameOther,  
                         orderBy        : this.orderBy,
                         orderDir       : this.orderDir,
                        };
            var data = await this.searchTemplateOther(info);
            this.editStatusChar(info);
            this.listDataTemplate          = data.data_export.data.map(item=> {item.selectedOther = false; return item});
            this.max_export_count          = data.max_issu_export_count;
        },
        editStatusChar: async function name(info) {
            var searchMessageEdit = [];
            var statusMessageEdit = [];
            if(info['id']){
                searchMessageEdit.push('ID:' +info['id']);
            }
            if(info['filename']){
                searchMessageEdit.push('明細名:' +info['filename']);
            }
            if(info['receiverName']){
                searchMessageEdit.push('取引先:' +info['receiverName']);
            }
            //状況表示の編集
            if(info['reviewStatus'] == 0){
                statusMessageEdit.push('回覧前');
            }else if(info['reviewStatus'] == 1){
                statusMessageEdit.push('回覧中');
            }else{
                statusMessageEdit.push('完了');
            }
            var searchMessage = statusMessageEdit.join('、');
            if(searchMessage){
                searchMessageEdit.push('状況:'+searchMessage);
            }
            //日付表示の編集
            if(info['fromdate'] || info['todate']){
                searchMessageEdit.push('売上計上日:' + info['fromdate'] + '～' + info['todate']);
            }
            if(info['fromdateKijitu'] || info['todateKijitu']){
                searchMessageEdit.push('入金期日:' + info['fromdateKijitu'] + '～' + info['todateKijitu']);
            }
            if(info['fromdateHakko'] || info['todateHakko']){
                searchMessageEdit.push('請求日:' + info['fromdateHakko'] + '～' + info['todateHakko']);
            }

            this.searchMessage = searchMessageEdit.join(' ／');
        },
        onSelectAll() {
          this.selectAll = !this.selectAll;
          this.listData.map(item=> {item.selected = this.selectAll; return item});

          //受信データが含まれる場合、削除ボタン非活性化
            if(this.selected.filter(e => e.circular_kind == 0).length>0){
                this.hasReceived = true;
            }else{
                this.hasReceived = false;
            }
            //0,5:回覧前 2,3:完了 データあれば、ダウンロード可能。
            if(this.selected.filter(e => e.circular_status == 0 || e.circular_status == 5 || e.circular_status == 2 || e.circular_status == 3).length>0){
                this.hasDownload = true;
            }else{
                this.hasDownload = false;
            }
        },
        onSelectAllOther() {
          this.selectAllOther = !this.selectAllOther;
          this.listDataOther.map(item=> {item.selectedOther = this.selectAllOther; return item});

          //受信データが含まれる場合、削除ボタン非活性化
            if(this.selectedRow.filter(e => e.circular_kind == 0).length>0){
                this.hasReceived = true;
            }else{
                this.hasReceived = false;
            }
            //0,5:回覧前 2,3:完了 データあれば、ダウンロード可能。
            if(this.selectedRow.filter(e => e.circular_status == 0 || e.circular_status == 5 || e.circular_status == 2 || e.circular_status == 3).length>0){
                this.hasDownloadRow = true;
            }else{
                this.hasDownloadRow = false;
            }
        },
        showDialogDelete(){ 
            this.confirmDelete = true;
        },         
        showDialogDeleteOther(){ 
            this.confirmDeleteOther = true;
        },

        // PAC_5-2239
        // 請求書ダウンロード
        showDialogDownload(){
            this.hasUndownloaded = false;
            for (let i in this.selected) {
                // 1,4:回覧中　データは、ダウンロードされません。
                if(this.selected[i].circular_status == 1 || this.selected[i].circular_status == 4){
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

        clearRadioStatus(e,type){
            // Because the native click event will be executed twice, the first time on the label tag 
            // and the second time on the input tag, this processing is required
            if (e.target.tagName === 'INPUT' || (type == 1 && this.input.stampOtherHistory == false) || (type == 2 && (this.input.stampOtherHistory == false || this.countAllTabNum == true))) return
            if(type == 1){
              this.input.stampRequestHistory = false;
              this.radioVal = "default";
              return ;
            }
            this.input.stampOtherHistory = false;
            this.radioVal2 = "default";
        },
        changeRadioStatus(e,type){
            // Because the native click event will be executed twice, the first time on the label tag 
            // and the second time on the input tag, this processing is required
            if (e.target.tagName === 'INPUT' || (type == 1 && this.input.stampOtherHistory == true) || (type == 2 && this.input.stampOtherHistory == true) || this.countAllTabNum == true){return}
            this.countAllTabNum = true;
            this.$vs.dialog({
              type: 'confirm',
              color: 'primary',
              title: `確認`,
              acceptText: 'はい',
              cancelText: 'いいえ',
              text: `電子署名が付与されている場合、回覧履歴を付けてダウンロードをすると回覧時の署名が無効になります。`,
              accept: async () => {
                this.countAllTabNum = false;
                if(type == 1){
                  this.input.stampRequestHistory = true;
                  this.radioVal = "stampRequestHistory";
                  return ;
                }
                this.input.stampOtherHistory = true;
                this.radioVal2 = "stampOtherHistory";
              },
              cancel: async () => {
                this.countAllTabNum = false;
                if(type == 1){
                  this.input.stampRequestHistory = false;
                  this.radioVal = "default";
                  return ;
                }
                this.input.stampOtherHistory = false;
                this.radioVal2 = "default";
                
              },
            });
        },
        // その他ダウンロード
        showDialogDownloadOther(){
            this.hasUndownloaded = false;
            for (let i in this.selectedRow) {
                // 1,4:回覧中　データは、ダウンロードされません。
                if(this.selectedRow[i].circular_status == 1 || this.selectedRow[i].circular_status == 4){
                    this.hasUndownloaded = true;
                    break;
                }
            }
            if(this.selectedRow.length == 1 && this.selectedRow[0].file_names.split(',').length == 1){
                // .pdf, .docx, .xlsx
                var pos = this.selectedRow[0].file_names.lastIndexOf('.');
                this.inputMaxLength = 50 - this.selectedRow[0].file_names.substr(pos).length;
            }else{
                // .zip
                this.inputMaxLength = 46;
            }
            this.input.filename = '';
            this.confirmDownloadOther = true;
        },
        // PAC_5-2239

        showDialogExport(){
            if(!this.tab_report_info){//帳票
                this.onSearchTemplateOther();
            }else{//請求書
                this.onSearchTemplate();
            }
            this.confirmExport = true;
        },         

        // PAC_5-2239
        // 請求書ダウンロード
        onDownloadReserve: async function (){
            this.input.ids = this.getSelectedCircularID();
            this.input.status = this.getSelectedStatus();
            this.input.finishedDate = this.month;
            this.input.stampHistory = this.input.stampRequestHistory;
            this.input.download_type = this.DOWNLOAD_TYPE.FORM_ISSUANCE_DOWNLOAD_RESERVE;//帳票一覧で予約
            this.confirmDownload = false;
            // 請求書ダウンロード
            this.input.frmFlg = 1;
            await this.downloadReserve(this.input);
        },
        // その他ダウンロード
        onDownloadReserveOther: async function (){
            this.input.ids = this.getSelectedCircularIDOther();
            this.input.status = this.getSelectedStatusOther();
            this.input.finishedDate = this.month;
            this.input.stampHistory = this.input.stampOtherHistory;
            this.input.download_type = this.DOWNLOAD_TYPE.FORM_ISSUANCE_DOWNLOAD_RESERVE;//帳票一覧で予約
            this.confirmDownloadOther = false;
            // その他ダウンロード
            this.input.frmFlg = 1;
            await this.downloadReserve(this.input);
        },
        // PAC_5-2239

        onDelete: async function () {
           this.confirmDelete = false;
           //await this.postActionMultiple({action: 'deleteCompleted', info: { cids: this.getSelectedID() }});
           await this.postActionMultiple({action: 'deleteReport', info: { cids: this.getSelectedID() }});
           this.onSearch(false);
        },
        onDeleteOther: async function () {
           this.confirmDeleteOther = false;
           await this.postActionMultiple({action: 'deleteReportOther', info: { cids: this.getSelectedIDOther() }});
           this.onSearchOther(false);
        },
        getSelectedID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
        },
        // PAC_5-2239
        // 請求書から、回覧IDを取得
        getSelectedCircularID(){
            let cids = [];
            this.selected.forEach((item, stt) => {
                cids.push(item.circular_id)
            });
            return cids;
        },
        // その他から、回覧IDを取得
        getSelectedCircularIDOther(){
            let cids = [];
            this.selectedRow.forEach((item, stt) => {
                cids.push(item.circular_id)
            });
            return cids;
        },
        // PAC_5-2239
        getSelectedIDOther(){
            let cids = [];
            this.selectedRow.forEach((item, stt) => {
                cids.push(item.id)
            });
            return cids;
        },
        //回覧情報を取得する
        getSelectedStatus(){
          let select_status = [];
          this.selected.forEach((item, stt) => {
            select_status.push(item.circular_status);
          });
          return select_status;
        },
        getSelectedStatusOther(){
          let select_status = [];
          this.selectedRow.forEach((item, stt) => {
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
                    if (tr.circular_status=='2' || tr.circular_status=='3'){ //完了の場合
                        root.itemReadingDetail = await root.getDetailReport({id: tr.id, finishedDate: root.month});
                    }else{
                        root.itemReadingDetail = await root.getDetailReport({id: tr.id, finishedDate: 0});
                    }
                    root.showReading = true;
                    root.$store.dispatch('updateLoading', false);
                },300)
            }else{
                clearTimeout(time);
                this.click = 0;
                localStorage.setItem('finishedDate', this.month);
                this.$router.push('/form-issuance/' + tr.circular_id + '/0/' + tr.id);
            }
        },
        async onShowReadingOther(tr){
            this.click++

            if(this.click == 1){
                var root = this;
                var time = setTimeout(async function(){
                    root.click = 0;
                    root.itemReading = tr;
                    root.$store.dispatch('updateLoading', true);
                    if (tr.circular_status=='2' || tr.circular_status=='3'){ //完了の場合
                        root.itemReadingDetail = await root.getDetailReportOther({id: tr.id, finishedDate: root.monthOther});
                    }else{
                        root.itemReadingDetail = await root.getDetailReportOther({id: tr.id, finishedDate: 0});
                    }
                    root.showReading = true;
                    root.$store.dispatch('updateLoading', false);
                },300)
            }else{
                clearTimeout(time);
                this.click = 0;
                localStorage.setItem('finishedDate', this.monthOther);
                this.$router.push('/form-issuance/' + tr.circular_id + '/1/' + tr.id);
            }
        },
        async onSetFileName(template_name){
            this.export_file_name = template_name.replace(/.xlsx$/,"");
        },
        showRead(){
            if(this.itemReadingDetail.viewingUser){
                this.showReading=true
            }
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        handleSortOther(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearchOther(false);
        },
        onRowCheckboxClick: function (tr) {
            tr.selected = !tr.selected;
            this.selectAll = this.compListData.every(item => item.selected);
            
            //受信データが含まれる場合、削除ボタン非活性化
            if(this.selected.filter(e => e.circular_kind == 0).length>0){
                this.hasReceived = true;
            }else{
                this.hasReceived = false;
            }
            // 0,5:回覧前 2,3:完了 データあれば、ダウンロード可能。
            if(this.selected.filter(e => e.circular_status == 0 || e.circular_status == 5 || e.circular_status == 2 || e.circular_status == 3).length>0){
                this.hasDownload = true;
            }else{
                this.hasDownload = false;
            }
        },
        onRowCheckboxOtherClick: function (tr) {
            tr.selectedOther = !tr.selectedOther;
            this.selectAllOther = this.compListDataOther.every(item => item.selectedOther);
            
            //受信データが含まれる場合、削除ボタン非活性化
            if(this.selectedRow.filter(e => e.circular_kind == 0).length>0){
                this.hasReceived = true;
            }else{
                this.hasReceived = false;
            }
            // 0,5:回覧前 2,3:完了 データあれば、ダウンロード可能。
            if(this.selectedRow.filter(e => e.circular_status == 0 || e.circular_status == 5 || e.circular_status == 2 || e.circular_status == 3).length>0){
                this.hasDownloadRow = true;
            }else{
                this.hasDownloadRow = false;
            }
        },
        openWindow(url){
            window.open(url,'_blank');
        },
      addIndex: function() {
        this.selectIndex.push("");
        let index = {id: "", fromvalue: "",tovalue: "", type: ""};
        this.filter.indexes.push(index);
      },
      removeIndex: function(index) {
        this.filter.indexes.pop(index);
        this.selectIndex.splice(index, 1);
      },
      onChangeExample: function (id,index) {
        for(var long in this.frmIndex) {
          if (this.frmIndex[long].id == id) {
            this.filter.indexes[index].type = this.frmIndex[long].data_type;
            this.filter.indexes[index].fromvalue = '';
            this.filter.indexes[index].tovalue = '';
          }
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
        onAroundArrowMobile:function(){
            let obj = document.getElementById("arrow_mobile");
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
            var getOrigin =  await this.getOriginCircularUrl(tr.id);
            if(getOrigin.originCircularUrl == null){
                if (!this.tab_report_info){//その他帳票
                   localStorage.setItem('finishedDate', this.monthOther);
                   this.$router.push('/form-issuance/'+ tr.circular_id +'/1/' + tr.id);
            }else{
                   localStorage.setItem('finishedDate', this.month);
                    this.$router.push('/form-issuance/'+ tr.circular_id +'/0/' + tr.id);
                }
            }else{
                this.openWindow(getOrigin.originCircularUrl);
            }
        },
        exportFormIssuanceList: async function() {
            let optionExport;
            let info;
            // if (this.radio_output == 'CSV'){//radio Csv選択時
                if (!this.tab_report_info){//帳票
                    optionExport = ['name', 'email','company_frm_id','frm_name','customer_name','reference_date','circular_status','update_user','update_at','frm_data'];
                    info = { id             : this.filter.idOther,
                            filename       : this.filter.filenameOther,
                            receiverName   : this.filter.receiverNameOther,
                            reviewStatus   : this.reviewStatusOther,
                            finishedDate   : this.finishedDateOther,
                            orderBy        : this.orderBy,
                            orderDir       : this.orderDir,
                            export_file_name      : this.export_file_name,
                            csv_flg : '1',
                            other_flg : this.tab_report_info,
                            template_id : this.radio_template,
                            export_work_list_columns : optionExport,
                            output_kind    : this.radio_output,
                            };
                }else{//請求書
                    optionExport = ['name', 'email','company_frm_id','frm_name','customer_name','invoice_amt','trading_date','payment_date','invoice_date','circular_status','update_user','update_at','frm_data'];
                    info = { id             : this.filter.id,
                            filename       : this.filter.filename,
                            reviewStatus   : this.reviewStatus,
                            fromdate       : this.filter.fromdate,
                            todate         : this.filter.todate,
                            fromdateKijitu : this.filter.fromdateKijitu,
                            todateKijitu   : this.filter.todateKijitu,
                            fromdateHakko  : this.filter.fromdateHakko,
                            todateHakko    : this.filter.todateHakko,
                            finishedDate   : this.finishedDate,
                            receiverName   : this.filter.receiverName,
                            orderBy        : this.orderBy,
                            orderDir       : this.orderDir,
                            export_file_name      : this.export_file_name,
                            csv_flg : '1',
                            other_flg : this.tab_report_info,
                            template_id : this.radio_template,
                            export_work_list_columns : optionExport,
                            output_kind    : this.radio_output,
                            };
                }
                let formIssuanceListExport =  await this.exportFormIssuanceListToCSV(info);
                let fileName = formIssuanceListExport.file_name;
                let csv_excel_data = formIssuanceListExport.csv_excel_data;
                let excel_data = formIssuanceListExport.excel_data;
                if(this.radio_output == 'CSV'){
                    let csvContent = '';
                    csv_excel_data.forEach(function(infoArray, index) {
                        csvContent += infoArray + '\r\n';
                    });

                    this.downloadFileExport(csvContent, fileName, 'text/csv');
                }else{
                    this.downloadFileExportExcel(csv_excel_data, fileName);
                }
            // }else{//radio Excel選択時

            // }
        },
        downloadFileExport (data, fileName, mineType) {
            mineType = mineType || "application/octet-stream";
            fileName = fileName + '.csv';

            // Convert data to SJIS
            const str_array = Encoding.stringToCode(data);
            const sjis_array = Encoding.convert(str_array, "SJIS", "UNICODE");
            data = new Uint8Array(sjis_array);

            let hiddenElement = document.createElement('a');
            if (hiddenElement.download !== undefined) {
                let blob = new Blob([data], { type: (mineType + ';charset=Shift_JIS;') });
                var url = URL.createObjectURL(blob);
                hiddenElement.href = url;
                hiddenElement.download =  fileName;
            }
            if (navigator.msSaveBlob) { // IE 10+
                hiddenElement.addEventListener("click", function (event) {
                    let blob = new Blob([data], {
                        "type": mineType + ";charset=Shift_JIS;"
                    });
                    navigator.msSaveBlob(blob, fileName);
                }, false);
            }
            hiddenElement.click();
            URL.revokeObjectURL(url);
        },
        downloadFileExportExcel (data, fileName) {
            fileName = fileName + '.xlsx';

            // Convert data to SJIS
            const str_array = Encoding.stringToCode(data);
            // const sjis_array = Encoding.convert(str_array, "SJIS", "UNICODE");
            // data = new Uint8Array(sjis_array);
            //const decoded_data = decodeURIComponent(atob(data));
            //const decoded_data = this.base64DecodeAsBlob(data, "image/jpeg");
                                const decode_data = data;
                                // filenameUpload = data.fileName;
                                // if (data.fileName && data.file_data) {
                                    const byteString = Base64.atob(decode_data);
                                    const ab = new ArrayBuffer(byteString.length);
                                    const ia = new Uint8Array(ab);
                                    for (let i = 0; i < byteString.length; i++) {
                                        ia[i] = byteString.charCodeAt(i);
                                    }
                                    //  this.dataBlob = new Blob([ia]);
                                // }

            let hiddenElement = document.createElement('a');
            if (hiddenElement.download !== undefined) {
                let blob = new Blob([ia], { type: ('application/octet-stream') });
                var url = URL.createObjectURL(blob);
                hiddenElement.href = url;
                hiddenElement.download =  fileName;
            }
            hiddenElement.click();
            URL.revokeObjectURL(url);
        },
        base64DecodeAsBlob(text, type = "text/plain;charset=UTF-8") {
        return fetch(`data:${type};base64,` + text).then(response => response.blob());
        },
        toView(circular_id,id) {
            localStorage.setItem('finishedDate', this.month);
            this.$router.push('/form-issuance/'+ circular_id +'/0/' + id);
        },
        toViewOther(circular_id,id) {
            localStorage.setItem('finishedDate', this.monthOther);
            this.$router.push('/form-issuance/'+ circular_id +'/1/' + id);
        },
    },
    computed:{
        compListData: function() {
            let data = [];
            this.listData.forEach((item) => {
                data.push(item);
            });
            return data;
        },
        compListDataOther: function() {
            let data = [];
            this.listDataOther.forEach((item) => {
                data.push(item);
            });
            return data;
        },
        compListDataTemplate: function() {
            let data = [];
            this.listDataTemplate.forEach((item) => {
                data.push(item);
            });
            return data;
        },
        selected() {
            return this.listData.filter(item => item.selected);
        },
        selectedRow() {
            return this.listDataOther.filter(item => item.selectedOther);
        },
        isEmtyItemReading() {//権限がない場合、v_circular_idに0がセットされている。回覧前データは表示ボタン活性とする。
            if (this.itemReading.v_circular_id ==0 && (this.itemReading.circular_status!=0) && (this.itemReading.circular_status!=5) ){
                return true;
            }
            for(let i in this.itemReading) return false;
            return true
        },
    },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        'paginationOther.currentPage': function (val) {
            this.onSearchOther(false);
        },
        'finishedDate': function () {//完了日時From/Toは帳票一覧にないので、ひとまず使わない
            // 完了日時From/Toの日付範囲を設定
            // let year = new Date().getFullYear();
            // let month = new Date().getMonth() + 1 - this.filter.finishedDate;

            // // 月は昨年の時
            // if (this.filter.finishedDate >= new Date().getMonth() + 1) {
            //     year = year - 1;
            //     month = month + 12;
            // }
            // // 月の日数
            // let lastDay = new Date(year, month, 0).getDate()
            // this.filter.fromdate = year + '-' + month + '-01';
            // this.filter.todate = year + '-' + month + '-' + lastDay;
            // this.completedConfigDate.minDate = year + '-' + month + '-01';
            // this.completedConfigDate.maxDate = year + '-' + month + '-' + lastDay;
        }
    },
    mounted() {
        this.onSearch(false);
        this.onSearchOther(false);
    },
    async created() {
        var $this = this;
        Axios.get(`${config.BASE_API_URL}/myinfo`)
        .then(response => {
          const myInfo = response.data ? response.data: [];

        Axios.get(`${config.BASE_API_URL}/setting/getMyCompany`)
            .then(response => {
                if (response.data && response.data.data){
                    this.is_sanitizing = response.data.data.sanitizing_flg; // PAC_5-2853
                    if(response.data.data.template_flg && response.data.data.template_search_flg == 1){
                        this.canUseTemplate = myInfo.data.info.template_flg;
                    }
                }
            })
            .catch(error => { return []; });
        })
        .catch(error => { return []; });
        await (async () => {
            this.settingLimit = await this.getLimit();
            if (this.settingLimit == null) {
                this.settingLimit = {};
            }
            if (this.settingLimit && this.settingLimit.default_stamp_history_flg == 1) {
                this.input.stampRequestHistory = true;
                this.radioVal = "stampRequestHistory";
                this.input.stampOtherHistory = true;
                this.radioVal2 = "stampOtherHistory";
            }
        })()
        this.frmIndex = await this.getFormIssuancesIndex();
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
    }
}

</script>

<style scoped lang="stylus">
    .detail{
        .label{ background: #b3e5fb; padding: 3px; word-break: break-all;}
        .info{  padding: 3px 3px 3px 5px; word-break: break-all;}
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