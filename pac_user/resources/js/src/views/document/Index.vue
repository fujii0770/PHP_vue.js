<template>
    <div id="sends-page">
        <vs-row>
            <vs-col class="folder-wrapper" :vs-w="showTree? 2.2:0" vs-xs="12" :vs-sm="showTree? 2.2:0">
                <div style="height: calc(100vh - 100px); width: 100%; flex-direction: column;background: #fff;" class="show-folder">
                    <FolderTree ref="tree" id="folderTree" :treeId="selectDecumentTree" @onNodeClick="setFolderId" style="overflow: auto;"></FolderTree>
                </div>
            </vs-col>
            <vs-col :vs-w="tableWide" vs-xs="12" :vs-sm="tableSm" :style="showTree ? 'transition: width .2s;margin-left: 21.5%;':'transition: width .2s;'">
        <vs-card style="margin-bottom: 0">
            <vs-row>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="3" vs-xs="12" class="mb-3">
                    <vs-input class="inputx w-full" label="件名" v-model="filter.documentName"/>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="3" vs-xs="12" class="mb-3 sm:pl-2">
                    <vs-input class="inputx w-full" label="キーワード" v-model="filter.keyword"/>
                </vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-lg="6" vs-sm="6" vs-xs="12" v-show="!loginUser.checkLongTermFlgAll">
                  <vs-button class="square" color="primary" v-on:click="onSearch(true)" v-bind:disabled="showTree && folderId == ''"><i class="fas fa-search"></i> 検索</vs-button>
                </vs-col>
            </vs-row>
            <vs-row  v-show="loginUser.checkLongTermFlgAll">
                <vs-col vs-w="1" vs-type="flex" vs-align="center">
                    <label>取引年月日</label>
                </vs-col>
                <vs-col vs-w="3" vs-type="flex" vs-align="center" class="mb-3">
                    <flat-pickr class="w-full" v-model="filter.fromdate" id="filter_fromdate" :config="configDate"></flat-pickr>
                </vs-col>
                <vs-col vs-w="1" vs-type="flex" vs-align="center"></vs-col>
                <vs-col vs-w="1" vs-type="flex" vs-align="center">
                    <label> ～ </label>
                </vs-col>
                <vs-col vs-w="3" vs-type="flex" vs-align="center" class="mb-3">
                    <flat-pickr class="w-full" v-model="filter.todate" id="filter_todate" :config="configDate"></flat-pickr>
                </vs-col>
            </vs-row>
            <vs-row  v-show="loginUser.checkLongTermFlgAll">
                <vs-col vs-w="1" vs-type="flex" vs-align="center">
                    <label>金額</label>
                </vs-col>
                <vs-col vs-w="3" vs-type="flex" vs-align="center" class="mb-3">
                    <vs-input class="inputx w-full" type="text" v-model="filter.fromMoney" @change.lazy="SetFromMoneyValue($event)"/>
                </vs-col>
                <vs-col vs-w="1" vs-type="flex" vs-align="center"></vs-col>
                <vs-col vs-w="1" vs-type="flex" vs-align="center">
                    <label> ～ </label>
                </vs-col>
                <vs-col vs-w="3" vs-type="flex" vs-align="center" class="mb-3">
                    <vs-input class="inputx w-full" type="text" v-model="filter.toMoney" @change.lazy="SetToMoneyValue($event)"/>
                </vs-col>
            </vs-row>
            <vs-row  v-show="loginUser.checkLongTermFlgAll">
                <vs-col vs-w="1" vs-type="flex" vs-align="center">
                    <label>取引先</label>
                </vs-col>
                <vs-col vs-w="3" vs-type="flex" vs-align="center" class="mb-3">
                    <vs-input class="inputx w-full" type="text" v-model="filter.customer"/>
                </vs-col>
            </vs-row>
            <vs-row v-for="(field, index) in selectIndex" v-bind:key="index" :index="index">
                <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 pr-2">
                    <vs-select class="selectExample w-full" v-model="filter.indexes[index].id" @change="onChangeExample(filter.indexes[index].id,index)">
                        <vs-select-item v-for="long in longtermIndex" v-bind:key="long.id" :value="long.id" :text="long.index_name" />
                    </vs-select>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                    <vs-input class="inputx w-full" v-model="filter.indexes[index].fromvalue"/>
                </vs-col>
                <vs-col vs-w="1" vs-type="flex" vs-align="center" v-show="filter.indexes[index].type == 0 || filter.indexes[index].type == 2"></vs-col>
                <vs-col vs-w="1" vs-type="flex" vs-align="center" v-show="filter.indexes[index].type == 0 || filter.indexes[index].type == 2">
                    <label> ～ </label>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="3" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2" v-show="filter.indexes[index].type == 0 || filter.indexes[index].type == 2">
                    <vs-input class="inputx w-full" v-model="filter.indexes[index].tovalue"/>
                </vs-col>
                <vs-col vs-type="flex" vs-lg="1" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                    <vs-button class="square" color="danger" v-on:click="removeIndex(index)"> x </vs-button>
                </vs-col>
            </vs-row>
            <vs-row class="mt-3" v-show="loginUser.checkLongTermFlgAll">
                <vs-col vs-type="flex" vs-align="center">
                    <vs-button class="square" color="success" v-on:click="addIndex"> + </vs-button>
                </vs-col>
                 <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button class="square" color="primary" v-on:click="onSearch(true)" v-bind:disabled="showTree && folderId == ''"><i class="fas fa-search"></i> 検索</vs-button>
                </vs-col>
            </vs-row>
        </vs-card>

        <vs-card>

          <div class="diyDropDownMain">
            <vs-button id="button5" class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary" type="filled" v-bind:disabled="selected.length === 0"><i class="fas fa-download"></i> ダウンロード予約</vs-button>
            <div v-show="selected.length > 0" class="download-complete diyDropDownBody diyDropDownBody2" >
              <li v-if="selected.length > 0" class="vx-dropdown--item">
                <a class="vx-dropdown--item-link" style="width: 202px;text-align: center;">
                  <vs-radio class="mb-2 mt-2" vs-value="default" vs-name="radioVal" @click.native.stop="clearRadioStatus($event)" v-model="radioVal" :disabled="countAllTabNum">完了済みファイル</vs-radio>
                </a>
              </li>
              <li v-if="selected.length > 0 && upload_status" class="vx-dropdown--item">
                <a class="vx-dropdown--item-link" style="width: 202px;text-align: center;">
                  <vs-radio class="mb-2 mt-2" vs-value="stampHistory" vs-name="radioVal"  v-model="radioVal" @click.native.stop="changeRadioStatus($event)">回覧履歴を付ける</vs-radio>
                </a>
              </li>
              <vs-button class="download-item download-complete-btn" type="filled" color="primary"  @click="showDialogDownload" :class="{'upload_status_no':upload_status}" style="width: 90%; margin: auto; display: flex;" :disabled="countAllTabNum">
                <i class="fas fa-download"></i> ダウンロード予約</vs-button>
            </div>
          </div>
            <vs-button class="square"  color="warning" @click="onMoveFolderUpdate" v-bind:disabled="selected.length == 0" v-if="showTree && info!=null && info.long_term_storage_move_flg">移動</vs-button>
            <vs-button class="square"  color="danger" @click="onDeleteDocumentClick" v-if="info!=null && info.long_term_storage_delete_flg" v-bind:disabled="selected.length == 0"  ><i class="far fa-trash-alt"></i> 削除</vs-button>
            <vs-button v-show="loginUser.checkLongTermFlgAllStampFlg && loginUser.timeStampAssignFlg" class="square"  color="primary" @click="automaticUpdateClick(true)" v-bind:disabled="selected.length == 0"  >自動更新ON</vs-button>
            <vs-button v-show="loginUser.checkLongTermFlgAllStampFlg && loginUser.timeStampAssignFlg" class="square"  color="primary" @click="automaticUpdateClick(false)" v-bind:disabled="selected.length == 0"  >自動更新OFF</vs-button>
          <vs-button v-if="!loginUser.isAuditUser" class="square"  color="success" @click="confirmSaveLongTerm" > 新規登録</vs-button>

             <vs-table class="mt-3" noDataText="データがありません。" :data="listDocument" @sort="handleSort" sst stripe>
                <template slot="thead">
                    <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                    <vs-th sort-key="title" class="min-width-100">件名</vs-th>
                    <vs-th sort-key="fileSize">サイズ</vs-th>
                    <vs-th sort-key="LTD.upload_status">保管</vs-th>
                    <vs-th sort-key="LTD.create_at">保存日時</vs-th>
                    <vs-th sort-key="LTD.add_timestamp_automatic_date" v-show="loginUser.time_stamp_permission && loginUser.checkLongTermFlgAllStampFlg">タイムスタンプ付与日時</vs-th>
                    <vs-th v-show="loginUser.checkLongTermFlgAllStampFlg">タイムスタンプ自動更新</vs-th>
                </template>

                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
                        <vs-td><vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/></vs-td>
                        <td @click="onRowSelect(tr)" class="max-width-200"> {{tr.title}}</td>
                        <td @click="onRowSelect(tr)">{{convertByteToMByte(tr.file_size)}}MB</td>
                        <td @click="onRowSelect(tr)">{{tr.upload_status===1?'外部':'完了'}}</td>
                        <td @click="onRowSelect(tr)">{{tr.create_at | moment("YYYY/MM/DD HH:mm")}}</td>
                        <td @click="onRowSelect(tr)" :style="tr.add_timestamp_automatic_date ? 'padding-left:40px;':'padding-left:76px;'" v-show="loginUser.time_stamp_permission && loginUser.checkLongTermFlgAllStampFlg">{{tr.add_timestamp_automatic_date ? tr.add_timestamp_automatic_date.substr(0,tr.add_timestamp_automatic_date.length-3) : "なし"}}</td>
                        <td v-show="loginUser.checkLongTermFlgAllStampFlg" style="padding: 0 76px;" @click="onRowSelect(tr)">{{tr.timestamp_automatic_flg ? "ON" : "OFF"}}</td>
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
                            <vs-card  v-if="detailItem.first_page_data" class="main-flex-item" style="position: relative; height: 100%;">
                                <div class="preview" style="width: 100%; position: absolute; overflow: hidden; height: 100%;">
                                    <img :src="'data:image/jpeg;base64,' + detailItem.first_page_data" alt="" style="max-height: 100%;">
                                </div>
                            </vs-card>
                            <vs-card v-else class="main-flex-item">プレビューするレコードを選択してください</vs-card>

                            <vs-row vs-type="flex" style="padding: 10px">
                                <div class="break"></div>
                            </vs-row>

                            <vs-card class="detail flex-item mb-2" style="overflow: auto">
                                <h3>詳細内容表示エリア</h3>
                                <vs-row class="mt-3">
                                    <vs-col vs-w="4" class="label">件名</vs-col>
                                    <vs-col vs-w="8" class="info max-width-360">{{ detailItem.title }}</vs-col>
                                </vs-row>
                <vs-row>
                                    <vs-col vs-w="4" class="label"></vs-col>
                                    <vs-col vs-w="8" class="text-right">
                      <div class="diyDropDownMain">
                        <vs-button id="button5" class="square mb-2 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0" color="primary" type="filled"><i class="fas fa-download"></i> ダウンロード</vs-button>
                        <div  class="download-complete diyDropDownBody">
                          <li   class="vx-dropdown--item">
                            <a class="vx-dropdown--item-link">
                              <vs-radio class="mb-2 mt-2" vs-value="default" vs-name="radioVal2" @click.native.stop="clearRadioStatus($event)" v-model="radioVal" :disabled="countAllTabNum">完了済みファイル</vs-radio>
                            </a>
                          </li>
                          <li class="vx-dropdown--item"  v-if="!detailItem.upload_status">
                            <a class="vx-dropdown--item-link">
                              <vs-radio class="mb-2 mt-2" vs-value="stampHistory" vs-name="radioVal2"  v-model="radioVal" @click.native.stop="changeRadioStatus($event)">回覧履歴を付ける</vs-radio>
                            </a>
                          </li>
                          <vs-button class="download-item download-complete-btn" type="filled" color="primary"  @click="OnDirectDownLoad" style="width: 90%; margin: auto; display: flex;" :disabled="countAllTabNum">
                            <i class="fas fa-download"></i> ダウンロード</vs-button>
                        </div>
                      </div>
                    </vs-col>
                </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">ファイル名</vs-col>
                          <vs-col vs-w="8" class="info">{{ detailItem.file_name }}</vs-col>
                      </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">サイズ</vs-col>
                          <vs-col vs-w="8" class="info">{{ convertByteToMByte(detailItem.file_size) }}MB</vs-col>
                      </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">差出人</vs-col>
                          <vs-col vs-w="8" class="info">{{ detailItem.sender_emails  }}</vs-col>
                      </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">宛先</vs-col>
                          <vs-col vs-w="8" class="info" v-html="detailItem.destination_emails"></vs-col>
                      </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">申請日</vs-col>
                          <vs-col v-if="detailItem.upload_status" vs-w="8" class="info">-</vs-col>
                          <vs-col v-else vs-w="8" class="info">{{detailItem.request_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                      </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">承認完了日</vs-col>
                          <vs-col v-if="detailItem.upload_status" vs-w="8" class="info">-</vs-col>
                          <vs-col vs-w="8" v-else class="info">{{detailItem.completed_at | moment("YYYY/MM/DD HH:mm")}}</vs-col>
                      </vs-row>
                      <vs-row>
                          <vs-col vs-w="4" class="label">キーワード</vs-col>
                          <vs-col vs-w="5"><vs-textarea placeholder="コメントをつけて送信できます。" rows="2" v-model="detailItem.keyword" /></vs-col>
                          <vs-col vs-w="3"><p v-if="checkKeywordsLenFlg" style="color:red">入力できる文字数は200文字が最大です。</p></vs-col>
                      </vs-row>
                      <vs-row v-if="!detailItem.upload_status">
                          <vs-col vs-w="4" class="label">添付ファイル情報</vs-col>
                          <vs-col vs-w="8" class="info" v-html="detailItem.circular_attachment_name_string" style="word-break: break-all"></vs-col>
                      </vs-row>
                      <vs-row v-show="detailItem.circular_attachment_name_string.length>0">
                          <vs-col vs-w="4" class="label"></vs-col>
                          <vs-col vs-w="8" class="text-right">
                              <vs-button class="square " color="primary" @click="onSingleDownloadDocumentAttClick">
                                  <i class="fas fa-download"></i> ダウンロード
                              </vs-button>
                          </vs-col>
                      </vs-row>
              <!-- PAC_5-2359  add　インデックス : 「項目名」 「入力された内容」-->
              <template v-if="info &&info.long_term_storage_option_flg===1">
                    <vs-row>
                                        <vs-col vs-w="4" class="label">インデックス</vs-col>
                                        <vs-col vs-w="8" class="info" style="word-break: break-all">
                                            <vs-row>
                      <vs-col  vs-type="flex"  vs-justify="space-between" vs-align="center"  v-for="(item,index) in detailItem.circular_index" :key="index">
                        <span  style="flex:1;text-align: left" >{{item.index_name}}</span>
                        <span style="flex:1; text-align: left">{{indexVal(item)}}</span>
                      </vs-col>
                    </vs-row>
                  </vs-col>
                </vs-row>
              </template>
                            </vs-card>
                            <div class="flex-item text-right mb-6">
                                <vs-button class="square" color="primary" @click="onUpdateDocument"><i class="far fa-save"></i> 更新</vs-button>
                                <vs-button class="square" color="danger" v-if="detailItem && detailItem.create_user == loginUser.email && info!=null && info.long_term_storage_delete_flg " @click="onSingleDeleteDocumentClick"><i class="far fa-trash-alt"></i> 削除</vs-button>
            </div>
                        </template>

                    </div>
                </div>
                </vs-col>
            </vs-row>

      <vs-popup classContent="popup-example"  title="選択文書ダウンロード予約" :active.sync="confirmDownload">
        <vs-row>
          <vs-input class="inputx w-full" label="ファイル名" value="input.filename" v-model="input.filename" :maxlength="inputMaxLength" placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"/>
        </vs-row>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
            <vs-button @click="onDownloadReserve" color="primary">ダウンロード予約</vs-button>
            <vs-button @click="confirmDownload=false" color="dark" type="border">キャンセル</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>
<!--      2395 addd popup -->
      <vs-popup classContent="popup-example"  title="確認" :active.sync="confirmSaveLongTermFlg"  >
        <div >
          <div class="upload-wrapper mr-2 mb-2 mt-2">
              <div class="vx-col w-full upload-box" id="dropZone" @drop="handleFileSelect"
                   @dragleave="handleDragLeave" @dragover="handleDragOver">
                <label class="wrapper" for="longTermUploadFile">
                  <input type="file" ref="uploadFile"
                         accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                         id="longTermUploadFile" @change="onUploadFile"/>
                  <label for="longTermUploadFile"><strong>{{fileNames}}</strong></label>
                </label>
              </div>
            </div>
          <br>
          <div style=" width: 100%; border-top: 1px solid rgba(0, 0, 0, 0.1);"></div>
          <div class="mb-3 mt-3">キーワード登録</div>
          <vs-textarea v-model="filter.keywords"/>
          <span v-if="checkKeywordsLenFlg" style="color:red">入力できる文字数は200文字が最大です。</span>
          <span>キーワードを複数登録する場合は改行して下さい。</span>
        </div>
        <div id="fields" class="tools fields vs-con-loading__container" v-if="info && info.long_term_storage_option_flg">
          <div class="mb-3 mt-3">インデックス登録</div>
          <div class="body mt-1">
            <vs-row v-for="(field, index) in indexes" v-bind:key="index" :index="index">
              <vs-col vs-type="flex"  style="width: 30%">
                <vs-select class="selectExample w-full" v-model="indexes[index].longterm_index_id" style="width: 40%" @change="onChangeExample(indexes[index].longterm_index_id,index)">
                  <vs-select-item v-for="long in longtermIndex" v-bind:key="long.id" :value="long.id" :text="long.index_name" />
                </vs-select>
              </vs-col>
              <vs-col vs-type="flex" style="width: 45%;">
                <vs-input class="inputx "  v-model="indexes[index].value" :type="indexes[index].type==='number'?'text':indexes[index].type" @blur="ChangeSetIndexValue(indexes[index],$event)" />
              </vs-col>
              <vs-col vs-type="flex" vs-lg="2" vs-sm="6" vs-xs="12" class="mb-3 sm:pl-2">
                <vs-button class="square-text" color="danger" v-on:click="removeLongTermIndex(index)"> x </vs-button>
              </vs-col>
            </vs-row>
            <vs-button  @click="addLongTermIndex"  color="primary" type="filled">+</vs-button>
          </div>
        </div>
        <div id="folder" class="tools folder vs-con-loading__container" v-if="showTree">
          <div class="mb-3 mt-3">フォルダを選択</div>
          <div class="body mt-1">
            <div v-if="addLongTermFolderSelect" style="color:red">フォルダを選択してください。</div>
            <div style="border: 1px solid rgba(0, 0, 0, 0.1);border-radius: 5px;overflow: auto;height: 250px;">
              <FolderTreeAdd ref="tree" v-show="showAddLongTermFolderTree" :treeId="addLongTermFolderTree" @onNodeClick="setFolderId"></FolderTreeAdd>
            </div>
          </div>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
            <vs-button @click="onSaveLongTerm" color="primary">登録</vs-button>
            <vs-button @click="confirmSaveLongTermFlg=false;clearUploadFile();" color="dark" type="border">キャンセル</vs-button>
          </vs-col>
        </vs-row>
        <vs-popup classContent="popup-example"  title="確認" :active.sync="confirmStampPermission">
          <span>タイムスタンプを付与しますか？</span>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
              <vs-button @click="confirmPermission(true)" color="primary">はい</vs-button>
              <vs-button @click="confirmPermission(false)" color="dark" type="border">付与しない</vs-button>
            </vs-col>
          </vs-row>
        </vs-popup>
      </vs-popup>

<!--      2395 end-->

        <vs-popup classContent="popup-example"  title="フォルダ選択" :active.sync="onMoveFolder">
            <div v-if="onMoveFolderSelect" style="color:red">フォルダを選択してください。</div>
            <div style="border: 1px solid rgba(0, 0, 0, 0.1);border-radius: 5px;overflow: auto;height: 350px;">
                <FolderTreeUpdate ref="tree" id="folderTreeUpdate" :treeId="update" v-show="showFolderTree" @onNodeClick="setFolderId"></FolderTreeUpdate>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onSaveFolderId" color="primary">はい</vs-button>
                    <vs-button @click="onMoveFolder=false, folderEdit=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
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
import utils from "../../utils/utils";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import store from "../../store/store";
import {getPageUtil} from "../../utils/pagepreview";
import { DOWNLOAD_TYPE } from '../../enums/download_type';
import FolderTree from '../../components/long_term/FolderTree';
import FolderTreeUpdate from '../../components/long_term/FolderTree';
import FolderTreeAdd from '../../components/long_term/FolderTree';

export default {
    components: {
        InfiniteLoading,
        flatPickr,
        VxPagination,
        FolderTree,
        FolderTreeUpdate,
        FolderTreeAdd,
    },
    data() {
        return {
            filter: {
                documentName:"",
                fromdate : "",
                todate : "",
                fromMoney : "",
                toMoney : "",
                keyword  : "",
                indexes : [],
                customer : "",
                file_name: "",
            },
            selectAll: false,
            listDocument:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            orderBy: "LTD.create_at",
            orderDir: "desc",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                // defaultDate: new Date(),
            },
            confirmDetail: false,
            confirmDownload: false,
            checkKeywordsLenFlg: false,
            detailItem: null,
            keywordTex: true,
            listDepartment: {},
            loginUser: JSON.parse(getLS('user')),
            longtermIndex: [],
            selectIndex: [],
            input:{
              ids: [],
              filename: "",
              status: [],
              stampHistory: false,
              download:false,
              upload_id:[]
            },
            inputMaxLength: 46,
            showAttachmentData: [],
            info:{},
            showReading: false,
            month : 0,
            checkShowConfirmAddSignature: JSON.parse(getLS('user')).check_add_signature_time_stamp,
            longTermIndexes:[],
            DOWNLOAD_TYPE : DOWNLOAD_TYPE,
            radioVal: "default",
            tempFiles: [],
            confirmSaveLongTermFlg:false,
            keywords_flg: null,
            indexes : [],
            file_name:'',
            upload_id:'',
            unique_name:'',
            confirmStampPermission:false,
            confirmStampPermissionFlg:false,
            countAllTabNum: false,
            selectDecumentTree: 'selectDecumentTree',
            showFolder: false,
            folderId: '',
            confirmEdit: false,
            showTree: false,
            onMoveFolderSelect: false,
            onMoveFolder: false,
            onMoveFolderId: '',
            showFolderTree: false,
            folderEdit: false,
            update: 'update',
            tableWide: 11.5,
            tableSm: 11.5,
            showAddLongTermFolderTree: false,
            addLongTermFolderTree: 'addLongTermFolderTree',
            addLongTermFolderSelect: false,
            addLongTermFolderId: '',
            is_sanitizing: 0, // PAC_5-2853
            settingLimit:{},
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
      confirmTime_Stamp_PermissionFlg(){
       return this.loginUser.time_stamp_permission
      },

      selected() {
        return this.listDocument.filter(item => item.selected);
      },
      upload_status(){
       return this.selected.every(item=>!item.upload_status)
      },
      finishedDate() {
        return localStorage.getItem('finishedDate') ? localStorage.getItem('finishedDate') : ''
      },
      // addStampHistory: {
      //   get() {
      //     return this.$store.state.home.addStampHistory
      //   },
      //   set(value) {
      //     this.$store.commit('home/checkAddStampHistory', value);
      //   }
      // },
      fileNames() {
        if(!this.tempFiles || !this.tempFiles.length) return 'ファイル選択';
        return this.tempFiles.map(item => item.name).join(', ');
      }
    },
    methods: {
        ...mapActions({
            search: "circulars/getListDocument",
            clearHomeState: "home/clearState",
            addLogOperation: "logOperation/addLog",
            deleteDocument: "circulars/deleteDocument",
            updateDocument: "circulars/updateDocument",
            automaticUpdateTimestamp: "circulars/automaticUpdateTimestamp",
            downloadDocument: "circulars/downloadDocument",
            downloadDocumentList: "circulars/downloadDocumentList",
            getDepartment: "user/getDepartment",
            getLongtermIndex: "circulars/getLongtermIndex",
            getMyInfo: "user/getMyInfo",
            // pac_5-2377
            downloadAttachement:"circulars/downloadAttachement",
            checkShowConfirmAddTimeStamp: "home/checkShowConfirmAddTimeStamp",
            downloadSendFile: "home/downloadSendFile",
            downloadReserve: "circulars/downloadReserve",
            downloadFile: "circulars/downloadFile",
            setCircular: "home/setCircular",
            loadCircularForCompleted: "home/loadCircularForCompleted",
            getCircularPageData: "circulars/getCircularPageData",
            getMyFolders: "circulars/getMyFolders",
            updateFolderId: "circulars/updateFolderId",
            getDetailCircularUserForCompleted: "circulars/getDetailCircularUserForCompleted",
            getTermIndexValue:"circulars/getTermIndexValue",
            saveLongTermDocument:"circulars/saveLongTermDocument",
            longTermUpload:"circulars/longTermUpload",
            downloadLongTerm:"circulars/downloadLongTerm",
            getLimit: "setting/getLimit",
        }),
      indexVal(item){
        let str;
          switch (item.data_type) {
              case 0:
                str= utils.filterNum(item.num_value);
                break;
              case 1:
                str= item.string_value;
                break;
            case 2:
              if(item.date_value){
                str= utils.filterFormatDate(item.date_value,0);
              }
              break;
          }
      return str;
      },
      SetFromMoneyValue(e){
          let val=e.target.value.toString().replace(/,/g,"");
          this.filter.fromMoney=utils.filterNum(val);
      },
      SetToMoneyValue(e){
        let val=e.target.value.toString().replace(/,/g,"");
          this.filter.toMoney=utils.filterNum(val);
      },
        onSearch: async function (resetPaging) {
            this.selectAll = false;
            let info = { documentName         : this.filter.documentName,
                         fromdate         : this.filter.fromdate,
                         todate         : this.filter.todate,
                         fromMoney         : this.filter.fromMoney.replace(/,/g,""),
                         toMoney         : this.filter.toMoney.replace(/,/g,""),
                         fileName         : this.filter.fileName,
                         fromFileSize         : this.filter.fromFileSize,
                         toFileSize         : this.filter.toFileSize,
                         keyword         : this.filter.keyword,
                         customer        : this.filter.customer,
                         indexes    : this.filter.indexes,
                         page       : resetPaging?1:this.pagination.currentPage,
                         limit      : this.pagination.limit,
                         orderBy    : this.orderBy,
                         orderDir   : this.orderDir,
                        folderId    : this.folderId,
                        };
            var data = await this.search(info);
            for(let x in data.data){
                this.showAttachmentData[data.data[x].id] = data.data[x].circular_attachment_name_string;
            }
            this.listDocument               = data.data.map(item=> {item.selected = false; return item});
            this.pagination.totalItem   = data.total;
            this.pagination.totalPage   = data.last_page;
            this.pagination.currentPage = data.current_page;
            this.pagination.limit       = data.per_page;
            this.pagination.from        = data.from;
            this.pagination.to          = data.to;
        },
        onSelectAll() {
            this.selectAll = !this.selectAll;
			this.listDocument.map(item=> {item.selected = this.selectAll; return item});
			if(this.selectAll == true){
                let findOtherStatus = this.listDocument.find(item=> {return (item.selected == true && item.upload_status != 0)});
                if(findOtherStatus){
                    this.input.stampHistory = false;
                    this.radioVal = "default";
                }
            }
        },
        onSingleDownloadDocumentClick: async function () {
            let arrItem = [];
            if (this.detailItem){
                arrItem.push(this.detailItem.id);
            }
            if (arrItem.length){
                this.downloadDocument(arrItem);
            }
        },
        onUpdateDocument: async function() {
            if(this.detailItem) {
                if(this.detailItem.keyword.length > 200){
                    this.checkKeywordsLenFlg = true;
                }else{
                    this.checkKeywordsLenFlg = false;
                    await this.updateDocument(this.detailItem);
                    this.onSearch(false);
                }
            }
        },
        onSingleDeleteDocumentClick: async function () {
            let arrItem = [];
            if (this.detailItem){
                arrItem.push(this.detailItem.id);
            }

            this.confirmDetail = false;
            this.$vs.dialog({
                type:'confirm',
                color: 'danger',
                title: `確認`,
                acceptText: 'はい',
                cancelText: 'キャンセル',
                text: `選択した文書を削除します。よろしいですか？`,
                accept:  async () =>{
                  await this.deleteDocument(arrItem);
                  await this.onSearch(false);
                },
            })
        },
      getDocumentID(){
        let arrItem = [];
        this.selected.forEach((item, stt) => {
          arrItem.push(item.id)
        });
        return arrItem;
      },
      getSelectedID(){
        let arrItem = [];
        this.selected.forEach((item, stt) => {
          arrItem.push(item.circular_id)
        });
        return arrItem;
      },
      getSelectedUploadId(){
        let arrItem = [];
        this.selected.forEach((item, stt) => {
          if(item.circular_id===0){
            arrItem.push(item.upload_id)
          }

        });
        return arrItem;
      },
      // onDownloadReserve: async function (){
      //   debugger;
      //   this.input.ids = this.getSelectedID();
      //   this.confirmDownload = false;
      //   await this.downloadDocumentList(this.input);
      // },

      clearRadioStatus(e){
        // Because the native click event will be executed twice, the first time on the label tag
        // and the second time on the input tag, this processing is required
        if (e.target.tagName === 'INPUT' || this.input.stampHistory == false || this.countAllTabNum == true) return
        this.input.stampHistory = false;
        this.radioVal = "default";
      },
      changeRadioStatus(e){
        // Because the native click event will be executed twice, the first time on the label tag
        // and the second time on the input tag, this processing is required
        if (e.target.tagName === 'INPUT' || this.input.stampHistory == true || this.countAllTabNum  == true) return
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
            this.input.stampHistory = true;
            this.radioVal = "stampHistory";
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
        if(this.selected.length == 1 && this.selected[0].file_name.split(',').length == 1){
          // .pdf, .docx, .xlsx
          var pos = this.selected[0].file_name.lastIndexOf('.');
          this.inputMaxLength = 50 - this.selected[0].file_name.substr(pos).length;
        }else{
          // .zip
          this.inputMaxLength = 46;
        }
        this.input.filename = '';
        this.confirmDownload = true;
      },
      // PAC_5-2287
      onDownloadReserve: async function (){
        this.input.ids = this.getDocumentID();
        this.input.status = this.getSelectedStatus();
        this.input.finishedDate = this.month;
        this.input.download_type = this.DOWNLOAD_TYPE.LONG_TERM_DOWNLOAD_RESERVE;//長期保管で予約
        this.confirmDownload = false;
        this.input.upload_id=this.getSelectedUploadId();
        await this.downloadLongTerm(this.input);
        this.input.stampHistory=false;
        this.radioVal = "default";
      },
      OnDirectDownLoad:async function (){
        this.input.download=false
        this.input.filename=''
        this.input.ids=[this.detailItem.id]
        this.input.download_type = this.DOWNLOAD_TYPE.LONG_TERM_DOWNLOAD_RESERVE;//長期保管で予約
        this.input.upload_id=this.getDetailUploadId();
        await this.downloadLongTerm(this.input);
        this.input.stampHistory=false;
        this.input.download=false;
        this.radioVal = "default";
      },
      getDetailUploadId(){
        if(this.detailItem.upload_id){
          return [this.detailItem.upload_id];
        }
        return []
      },
      getSelectedStatus(){
        let select_status = [];
        this.selected.forEach((item, stt) => {
          select_status.push(item.circular_status);
        });
        return select_status;
      },
      addLogDownloadOperation: async function(result){
        const action = 'r9-14-download';
        // PAC_5-1027 ダウンロードの操作履歴が表示されない
        if(result){
          this.addLogOperation({ action: action, result: 0, params:{filename: this.fileSelected.name}});
        }else{
          this.addLogOperation({ action: action, result: 1, params:{filename: this.fileSelected.name}});
        }
      },
      changeStampHistory(){
        if(this.addStampHistory){
          this.addStampHistory = false;
        }else {
          this.addStampHistory = true;

        }
      },
        automaticUpdateClick: async function(automatic){
            let arrItem = {
                automatic : automatic,
                id : []
            };
            this.selected.forEach((item, stt) => {
                arrItem.id.push(item.id)
            });
            if (arrItem.id.length){
                await this.automaticUpdateTimestamp(arrItem);
            }
            this.onSearch(false);
        },
        onDeleteDocumentClick: async function () {
            let arrItem = [];
            let allValid = true;
            this.selected.forEach((item, stt) => {
                if ((item.user_id && item.user_id !=this.loginUser.id) && item.create_user != this.loginUser.email){
                    this.$vs.dialog({
                        type:'alert',
                        color: 'danger',
                        title: `確認`,
                        acceptText: '閉じる',
                        text: `削除権限のない文書が含まれています。`,
                    })
                    allValid = false;
                    return;
                }
                arrItem.push(item.id)
            });

            if (allValid){
                this.confirmDetail = false;
                this.$vs.dialog({
                    type:'confirm',
                    color: 'danger',
                    title: `確認`,
                    acceptText: 'はい',
                    cancelText: 'キャンセル',
                    text: `選択した文書を削除します。よろしいですか？`,
                    accept:  async () => {
                      await this.deleteDocument(arrItem);
                      await this.onSearch(false);
                    },
                })
            }
        },
        convertByteToMByte(byte){
            return Math.round(byte*100/(1024*1024))/100;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        async onRowSelect(tr) {
            this.$store.dispatch('updateLoading', true);
          this.checkKeywordsLenFlg = false;
            let complatedDate = tr.completed_at.split('-');
            let nowDate = this.$moment(new Date()).format('YYYY-MM-DD').split('-');
            this.month = parseInt(nowDate[0]) * 12 + parseInt(nowDate[1]) - (parseInt(complatedDate[0]) * 12 + parseInt(complatedDate[1]));
          let data = {
            id: tr.upload_status?0:tr.circular_id,
            finishedDate: this.month,
            longTermFlg:tr.upload_status,
            lid : tr.id
          };
          let result = {};
          if(this.showTree){
            result = await this.getCircularPageData(data);
          }else{
            result = await this.getDetailCircularUserForCompleted(data);
          }
          tr.first_page_data = result.circular.first_page_data;
          this.detailItem = tr;
          this.detailItem.sender_emails =this.detailItem.upload_status==1?'-': tr.sender_name + ' <' + tr.sender_email + '>';
          this.detailItem.destination_emails = '';
          let destination_email = tr.destination_email.split(',');
          let destination_name = tr.destination_name.split(',');
          for(var i = 0; i < destination_email.length; i++){
             if (i < destination_name.length){
                 this.detailItem.destination_emails +=this.detailItem.upload_status==1?'-': destination_name[i] + ' &lt;' + destination_email[i] + '&gt;<br/>';
             }else{
                 this.detailItem.destination_emails += this.detailItem.upload_status==1?'-':'&lt;' + destination_email[i] + '&gt;<br/>';
             }
          }
          this.detailItem.circular_attachment_name_string = ''
          if(this.showAttachmentData){
              this.detailItem.circular_attachment_name_string = this.showAttachmentData[tr.id]
          }
            this.showReading = true;
            this.$store.dispatch('updateLoading', false);
        },
        onRowCheckboxClick: function (tr) {
            tr.selected = !tr.selected
            let findOtherStatus = this.listDocument.find(item=> {return (item.selected == true && item.upload_status != 0)});
            if(findOtherStatus){
                this.input.stampHistory = false;
                this.radioVal = "default";
            }
            this.selectAll = this.listDocument.every(item => item.selected);
        },
        onSingleDownloadDocumentAttClick: async function () {
            let arrItem = [];
            if (this.detailItem){
                arrItem.push(this.detailItem.circular_id);
            }
            if (arrItem.length){
                this.downloadAttachement({circular_id:this.detailItem.circular_id,id:this.detailItem.id})
            }
        },
      // PAC_5-2395 add
      handleFileSelect: async function (evt) {
        const dropZone = document.getElementById('dropZone');
        dropZone.style.borderColor = '#D1ECFF';
        evt.stopPropagation();
        evt.preventDefault();
        const files = Array.from(evt.dataTransfer.files);
        if(files && files.length) this.tempFiles = [files.shift()];
      },
      handleDragLeave: function (evt) {
        const dropZone = document.getElementById('dropZone');
        dropZone.style.borderColor = '#D1ECFF';
        evt.stopPropagation();
        evt.preventDefault();
      },
      handleDragOver: function (evt) {
        const dropZone = document.getElementById('dropZone');
        dropZone.style.borderColor = '#55efc4';
        evt.stopPropagation();
        evt.preventDefault();
        evt.dataTransfer.dropEffect = 'copy';
      },
      async  onUploadFile(e) {

        const files = Array.from(e.target.files);

        // this.tempFiles = files;
        const {upload_id,file_name,unique_name}=await this.longTermUpload(files[0])
        this.file_name=file_name
        this.upload_id=upload_id
        this.unique_name=unique_name
        if(this.tempFiles[0]){
          this.tempFiles=[]
          this.tempFiles.push(files[0])
        }else{
          this.tempFiles.push(files[0])
        }
        e.target.value = ""
      },
      clearUploadFile(){
          this.filter.keywords=''
          this.tempFiles=[]
          this.confirmSaveLongTermFlg=false
          this.confirmStampPermissionFlg=false
          this.confirmStampPermission=false
          this.file_name=''
          this.upload_id=''
          this.unique_name=''
      },
      confirmPermission(val){

        this.confirmStampPermissionFlg = val

        let data = {
          indexes: this.indexes,
          file_name:this.file_name,
          upload_id:this.upload_id,
          keywords:this.filter.keywords,
          unique_name:this.unique_name,
          StampPermissionFlg:this.confirmStampPermissionFlg,
          folder_id:this.addLongTermFolderId,
        };
        for (let v in this.indexes){
          this.indexes[v][this.indexes[v].index_name]=this.indexes[v].value
        }
        if(this.upload_id && this.file_name){
          this.saveLongTermDocument(data)
          this.setLongTermIndex();
          this.filter.keywords=''
          this.tempFiles=[]
          this.confirmSaveLongTermFlg=false
          this.confirmStampPermissionFlg=false
          this.confirmStampPermission=false
          this.file_name=''
          this.upload_id=''
          this.unique_name=''
          this.onSearch(false);
        }else{
          this.$store.dispatch("alertError", '登録するファイルがアップロードされていません', { root: true });
        }
      },

      onSaveLongTerm(){
          if(!this.upload_id && !this.file_name){
            this.confirmStampPermission=false
            this.$store.dispatch("alertError", '登録するファイルがアップロードされていません', { root: true });
          }else{
            if(this.loginUser.time_stamp_permission){
              this.confirmStampPermission=true
            }else {
              this.confirmStampPermission=false
              if(this.showTree && this.addLongTermFolderId == ''){
                this.confirmSaveLongTermFlg = true;
                this.addLongTermFolderSelect = true;
              }else{
                this.confirmPermission(false)
              }
            }
          }

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
      addLongTermIndex() {
        let index = {longterm_index_id: '', value: "", type: "",index_name:'',data_type:''};
        this.indexes.push(index);
      },
      removeLongTermIndex(index) {
        this.indexes.splice(index,1);

      },
      onChangeExample: function (longterm_index_id,index) {
        for(var long in this.longtermIndex) {
          if (this.longtermIndex[long].id == longterm_index_id) {
            this.indexes[index].type = this.longtermIndex[long].vsInputType;
            this.indexes[index].data_type = this.longtermIndex[long].data_type;
            this.indexes[index].index_name=this.longtermIndex[long].index_name;
            if(this.indexes[index].type == 1){
              this.indexes[index].value = '';
            }
          }
        }
      },
      async  getLongTermIndexValue(){
        let tmp= await this.getTermIndexValue(this.$route.params.id);
        if(tmp.length>0){
          const fields = Object.values(tmp);
          for (const field of fields) {
            field.type = ["number", "text", "date"][field.data_type];
            if(field.data_type===0){
              field.value=utils.filterNum(field.num_value)
            }else if(field.data_type===1){
              field.value=field.string_value
            }else if(field.data_type===2){
              field.value=field.date_value
            }
            delete field.id
          }
          this.indexes=tmp
        }else {
          this.setLongTermIndex();
        }

      },
      async setLongTermIndex(){
        const longtermIndex = await this.getLongtermIndex();
        const fields = Object.values(longtermIndex); // workaround: array の場合とそうでない場合の両方に対応するため
        for (const field of fields) {
          field.vsInputType = ["number", "text", "date"][field.data_type];
        }

        this.longtermIndex = longtermIndex;
        this.longtermIndex = longtermIndex;
        const newArr=JSON.parse(JSON.stringify(longtermIndex));
        const indexTmp=["取引年月日","金額","取引先"]
        const index1=[]
        newArr.forEach((item)=>{
          if(indexTmp.includes(item.index_name)){
            index1.push({longterm_index_id: item.id, value: "", type:item.vsInputType,index_name:'',data_type:item.data_type})
          }
        })
        this.indexes=index1;
      },
      ChangeSetIndexValue(item,e){
        if(item.data_type===0){
          item.value=utils.filterNum(e.target.value.replace(/[^\d.]/g,''))
        }
      },
         async onSaveFolderId() {
             if(this.showTree && this.onMoveFolderId == ''){
                 this.onMoveFolderSelect = true;
                 this.onMoveFolder = true;
             }else{
                 await this.updateFolderId({
                     cids: this.getDocumentID(),
                     folderId: this.onMoveFolderId
                 });
                 this.onMoveFolder = false;
                 await this.onSearch(false);
             }
         },
        setFolderId(id){
            if(this.onMoveFolder){
                this.onMoveFolderId = id;
            }else if(this.confirmSaveLongTermFlg){
                this.addLongTermFolderId = id;
            }else{
                this.folderId = id;
            }
        },
        onMoveFolderUpdate(){
            this.onMoveFolderSelect = false;
            this.onMoveFolderId = '';
            this.onMoveFolder = true;
            this.showFolderTree = true;
            this.folderEdit = true;
        },
        confirmSaveLongTerm(){
            this.addLongTermFolderSelect = false;
            this.addLongTermFolderId = '';
            this.showAddLongTermFolderTree = true;
            this.confirmSaveLongTermFlg = true;
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
      confirmSaveLongTermFlg(val){
          if(val===false){
            this.tempFiles=[];
            this.setLongTermIndex();
            this.filter.keywords=''
          }
      },
        folderId: async function (){
            this.showReading = false;
            await this.onSearch(false);
        },
        showReading: function () {
            if(this.showTree || this.showReading){
                this.tableWide = 9;
                this.tableSm = 7;
            }
            if(this.showTree && this.showReading){
                this.tableWide = 6.35;
                this.tableSm = 5;
            }
            if(!this.showTree && !this.showReading){
                this.tableWide = 11.5;
                this.tableSm = 11.5;
            }
        },
        showTree: function () {
            if(this.showTree || this.showReading){
                this.tableWide = 9;
                this.tableSm = 7;
            }
            if(this.showTree && this.showReading){
                this.tableWide = 6.35;
                this.tableSm = 5;
            }
            if(!this.showTree && !this.showReading){
                this.tableWide = 11.5;
                this.tableSm = 11.5;
            }
        }
    },
    mounted() {
    },
     async created() {

         this.input.stampHistory = false;
         this.radioVal = "default";
         let loginUser =JSON.parse(getLS('user'))
         if (!loginUser.isAuditUser){
            this.info = await this.getMyInfo();
         }else{
            this.info = null
         }
         this.listDepartment = await this.getDepartment();
         await  this.setLongTermIndex();
         this.confirmEdit = true;
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
         let myCompany = this.$store.state.groupware.myCompany  ? this.$store.state.groupware.myCompany :[]
         this.is_sanitizing = myCompany && myCompany.sanitizing_flg ? myCompany.sanitizing_flg :0
         if (myCompany && myCompany.long_term_folder_flg == 1) {
           this.showTree = true;
         }
         if(!this.showTree){
           this.onSearch(false);
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
    }
}

</script>
<style lang="stylus" scoped>
.upload_status_no{
  margin: auto !important;
  width: 90% !important;
}
::v-deep .vs-popup  .pop-dialog{
  height: 500px !important;
  overflow: auto;
}
.detail{
    .label{ background: #b3e5fb; padding: 3px; }
    .info{  padding: 3px 3px 3px 5px; }
}

.upload-wrapper {
  height: calc(100% - 50px);
  width: 400px;
  display: inline-block;

}
.upload-wrapper .upload-box {
  border-radius: 10px;
  border: 3px dashed #D1ECFF;
}
.upload-wrapper .upload-box label[for="longTermUploadFile"], #template-list-page .upload-wrapper .upload-box label[for="longTermUploadFileLarge"] {
  cursor: pointer;
}
.upload-wrapper .upload-box label.wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  padding: 10px 20px;
  align-items: center;
}
.upload-wrapper .upload-box input[type="file"] {
  display: none;
}
.show-folder {
    margin-right: 10px;
    -webkit-box-shadow: 0 4px 25px 0 rgba(0, 0, 0, .1);
    box-shadow: 0 4px 25px 0 rgba(0, 0, 0, .1);
    -webkit-transition: all .3s ease;
    border-radius: 15px;
}
.folder-wrapper{
    position: fixed;
}
.diyDropDownMain{
  position :relative
  display :inline-block
}
.diyDropDownMain:hover .diyDropDownBody{
  display :block
}

.diyDropDownBody{
  position :absolute;
  padding:4px ;
  top :42px;
  right :1px;
  display :none
  background :#ffffff;
  text-align :center;
  box-sizing: content-box;
  border-radius: 6px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  font-size :14px;
  z-index :999
}
.diyDropDownBody::after{
  position: absolute;
  content: '';
  top: 1px;
  width: 10px;
  right: 16px;
  height: 10px;
  display: block;
  background: #fff;
  transform: rotate(45deg) translate(-7px);
  border-top: 1px solid rgba(0,0,0,0.1);
  border-left: 1px solid rgba(0,0,0,0.1);
  z-index: 141;
  box-sizing: border-box;
  }
.diyDropDownBody2{
  right:0px!important;
  padding :4px 0!important;
}
</style>
