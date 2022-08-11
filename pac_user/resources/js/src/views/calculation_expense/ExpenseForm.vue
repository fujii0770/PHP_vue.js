<template>
    <div id="expense-page">
        <div class="over-info">
            <vs-card>
                <vs-row class="mb-8">
                    <h1>{{ formName }}</h1>
                </vs-row>
                <vs-row vs-type="flex" class="mb-6">
                    <vs-col vs-w="8">
                        <vs-row vs-type="flex" class="mb-4">
                            <vs-col vs-w="6">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3" vs-type="flex" class="pr-6"
                                            vs-align="center" vs-justify="end">
                                        <label class=" label-from">目的 :</label>
                                    </vs-col>
                                    <vs-col vs-w="7" >
                                        <vs-select :disabled="viewForm || isCreateFormnAdvanceComplete"
                                                   class=" w-full"
                                                   label="" v-model="purposeName">
<!--                                            <vs-select-item value="" text="-&#45;&#45;"/>-->
                                            <vs-select-item v-for="item in listMFormPurposeSelect"
                                                            :key="item.purpose_name"
                                                            :value="item.purpose_name" :text="item.purpose_name" />
                                        </vs-select>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-w="3" vs-type="flex"
                                    vs-align="center" v-if="isSettlementForm && !createForm && !isCreateFormnAdvanceComplete">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3">
                                        <label class=" label-from">申請ID :</label>
                                    </vs-col>
                                    <vs-col vs-w="7">
                                        <label class=" label-from">
                                            {{ formCode }}
                                        </label>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-w="3" vs-type="flex"
                                    vs-align="center" v-if="isSettlementForm && !createForm && !isCreateFormnAdvanceComplete
                                    || isAdvanceForm && viewForm && !isCreateFormnAdvanceComplete">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3">
                                        <label class=" label-from">状況 :</label>
                                    </vs-col>
                                    <vs-col vs-w="7">
                                        <label class=" label-from">
                                            {{ statusDisplay[tAppStatus] }}
                                        </label>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                        <vs-row vs-type="flex" class="mb-4">
                            <vs-col vs-w="6">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3" vs-type="flex" class="pr-6"
                                            vs-align="center" vs-justify="end">
                                        <label class=" label-from">所属 :</label>
                                    </vs-col>

                                    <vs-col vs-w="7" vs-type="flex"
                                            vs-align="center">
                                        <label class=" label-from">
                                            {{
                                                departmentInfo.department_name ?
                                                departmentInfo.department_name : ''
                                            }}
                                        </label>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-w="3" vs-type="flex"
                                    vs-align="center">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3">
                                        <label class=" label-from">氏名 :</label>
                                    </vs-col>
                                    <vs-col vs-w="7">
                                        <label class=" label-from">
                                            {{
                                                departmentInfo.full_name ?
                                                    departmentInfo.full_name : ''
                                            }}</label>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-w="3" vs-type="flex"
                                    vs-align="center">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3">
                                        <label class=" label-from">ID :</label>
                                    </vs-col>
                                    <vs-col vs-w="7">
                                        <label class=" label-from">
                                            {{
                                                departmentInfo.id ?
                                                    departmentInfo.id : ''
                                            }}
                                        </label>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                        <vs-row vs-type="flex" class="mb-4">
                            <vs-col vs-w="6">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3" vs-type="flex" class="pr-6"
                                            vs-align="center" vs-justify="end">
                                        <label class=" label-from">期間 :</label>
                                    </vs-col>
                                    <vs-col vs-w="7">
                                        <div class="vs-con-input"  style="position: relative; width:100%">
                                            <flat-pickr class="w-full"
                                                        :class="{disabled:viewForm}"
                                                        :disabled="viewForm"
                                                        v-validate="'required'" data-vv-scope="form"
                                                        name="target_period_from"
                                                        v-model="target_period_from"
                                                        id="target_period_from"
                                                        :config="completedConfigDate"
                                                        style="position: relative">

                                            </flat-pickr>
                                            <span class="text-danger" v-show="errors.has('form.target_period_from')">開始期間は必須項目です。</span>
                                        </div>
                                    </vs-col>
                                    <vs-col vs-w="2" vs-type="flex" vs-align="center"
                                            vs-justify="center" style="font-size: 16px">
                                            ～
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-w="6" vs-type="flex"
                                    vs-align="center">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="7">
                                        <div class="vs-con-input">
                                            <flat-pickr class="w-full"
                                                        :class="{disabled:viewForm}"
                                                        :disabled="viewForm"
                                                        v-model="target_period_to"
                                                        id="target_period_to"
                                                        :config="completedConfigDate">

                                            </flat-pickr>
                                        </div>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                        </vs-row>

                        <vs-row vs-type="flex"  vs-justify="center">
                            <vs-col vs-w="1" vs-type="flex" class="pr-6"
                                    vs-justify="end">
                                <label class=" label-from mb-6">詳細 :</label>
                            </vs-col>
                            <vs-col vs-type="flex" vs-w="10">
                                <vs-col vs-type="flex" vs-w="11.5">
                                    <vs-row vs-w="10">
                                        <vs-textarea class="" placeholder=""
                                                     :disabled="viewForm"
                                                     rows="3" v-model="form_dtl"
                                        />
                                    </vs-row>
                                </vs-col>
                            </vs-col>
                        </vs-row>
                        <vs-row vs-type="flex">
                            <vs-col vs-type="flex" vs-w="6">
                                <vs-row vs-type="flex" style="font-size: 13px">
                                    <vs-col vs-w="3" vs-type="flex"
                                            class="pr-6" vs-justify="end">
                                        <span v-if="!isSettlementForm">予定支出金額 :</span>
                                        <span v-if="isSettlementForm">事前仮払金額 :</span>
                                    </vs-col>
                                    <vs-col vs-w="9">
                                        <span v-if="!isSettlementForm">{{ formatPrice(expected_amt) }} 円</span>
                                        <span v-if="isSettlementForm">{{ formatPrice(suspay_amt) }} 円</span>

                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-type="flex" vs-w="6">
                                <vs-row vs-type="flex" vs-justify="space-between" class="mb-6 label-from">
                                    <vs-col vs-type="flex"
                                            vs-justify="end"
                                            vs-align="center" vs-w="7">
                                        <vs-row vs-type="flex" style="font-size: 13px">
                                            <vs-col vs-w="3" v-if="isSettlementForm">
                                                <span>精算金額 :</span>
                                            </vs-col>
                                            <vs-col vs-w="8" v-if="isSettlementForm">
                                                <span>{{ formatPrice(eps_amt) }}円</span>
                                            </vs-col>
                                            <vs-col vs-w="6"  v-if="!isSettlementForm"
                                                    vs-type="flex" vs-justify="end">
                                                <span class="pr-6">希望仮払金額:</span>
                                            </vs-col>
                                            <vs-col vs-w="6" v-if="!isSettlementForm">
                                                <span v-if="viewForm">
                                                    <span>{{ desired_suspay_amt }}円</span>
                                                </span>
                                                <vs-input class="inputx " vs-w="6" style="margin-top: -10px"
                                                          @blur="changeSetIndexValue(desired_suspay_amt, $event.target.value, 'desired_suspay_amt')"
                                                          @keypress="isNumber($event)"
                                                          min="0" v-else maxlength="10"
                                                          label="" v-model="desired_suspay_amt"/>
<!--                 TODO put into input        @blur="changeSetIndexValue(desired_suspay_amt, $event.target.value, 'desired_suspay_amt')"-->
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                    <vs-col vs-type="flex" v-if="isSettlementForm"
                                            vs-justify="center"
                                            vs-align="center" vs-w="5">過不足金額：{{ formatPrice(epsDiff) }} 円
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                        <vs-row vs-type="flex">
                            <vs-col vs-w="6">
                                <vs-row vs-type="flex">
                                    <vs-col vs-w="3" vs-type="flex" class="pr-6"
                                            vs-align="center" vs-justify="end">
                                        <label class=" label-from">明細 :</label>
                                    </vs-col>
                                    <vs-col vs-w="7" vs-type="flex" vs-align="center">
                                        <vs-select class=" w-full" label=""
                                                   :disabled="viewForm" v-model="wtsmNameSelected">
                                            <vs-select-item value="" text="---"/>
                                            <vs-select-item v-for="item in listWtsmNameSelect" :key="item.wtsm_name"
                                                            :value="item.wtsm_name" :text="item.wtsm_name"/>
                                        </vs-select>
                                    </vs-col>
                                    <vs-col vs-w="2" vs-type="flex" vs-align="center"
                                            vs-justify="center" style="font-size: 21px">
                                        <vs-button radius color="primary" class="btn-ex"
                                                   :disabled="viewForm || !wtsmNameSelected || isDisableAddItem"
                                                   @click="onShowDetailDialog"> +
                                        </vs-button>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                    </vs-col>
                    <vs-col vs-w="4">
                        <vs-row v-if="isSettlementForm " vs-type="flex"
                                class="mb-6" vs-justify="center">
                            申請書：{{ formCode }} {{ formName }}
                        </vs-row>
                        <vs-row vs-type="flex" class=" upload_files h-full">
                            <div class="centerx w-full p-2">
                                <div class="file-upload-box"
                                     :class="{'disabled':viewForm ,
                                      'file-upload-box_view':viewForm,
                                      'file_upload_btn':editForm }"
                                     @dragover="dragover" @dragleave="dragleave" @drop="drop"
                                     @click="() => $refs.fileUpload.click()">
                                    <div>
                                        <span>ファイル添付</span><br/>
                                        <p v-if="!viewForm">クリックまたはドラッグ＆ドロップ</p>
                                    </div>

                                </div>
                                <input v-if="!viewForm" type="file" multiple ref="fileUpload" @change="onFileChange">
                                <ul class="file-upload-list">
                                    <li v-for="(file, index) in uploadFiles" :key="`upload-${index}`">
                                        <span class="text-primary">{{ file.name }}</span>
                                        <feather-icon
                                            icon="XIcon" class="cursor-pointer"
                                            @click.stop="() => onDeleteUploadFile(index)"
                                            svg-classes="h-4 w-4 stroke-current text-danger">
                                        </feather-icon>
                                    </li>
                                    <li v-if="viewForm" v-for="(file, index) in infoFiles" :key="`file-${index}`"
                                        class="cursor-pointer" @click.stop="() => onDownloadFile(file)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                        <feather-icon
                                            icon="DownloadIcon" class="cursor-pointer ml-2"
                                            svg-classes="h-4 w-4 stroke-current text-danger">
                                        </feather-icon>
                                    </li>
                                    <li v-if="!viewForm" v-for="(file, index) in infoFiles" :key="`file-${index}`"
                                        class="cursor-pointer" @click.stop="() => onDeleteFile(file, index)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                        <feather-icon
                                            icon="XIcon" class="cursor-pointer ml-2"
                                            svg-classes="h-4 w-4 stroke-current text-danger">
                                        </feather-icon>
                                    </li>
                                </ul>
                            </div>
                        </vs-row>
<!--                        <div>-->
<!--                            <div class="p-12 bg-gray-100 border border-gray-300" @dragover="dragover" @dragleave="dragleave" @drop="drop">-->
<!--                                <input type="file" multiple name="fields[assetsFieldHandle][]" id="assetsFieldHandle"-->
<!--                                       class="w-px h-px opacity-0 overflow-hidden absolute" @change="onChange" ref="file" accept=".pdf,.jpg,.jpeg,.png" />-->

<!--                                <label for="assetsFieldHandle" class="block cursor-pointer">-->
<!--                                    <div>-->
<!--                                        Explain to our users they can drop files in here-->
<!--                                        or <span class="underline">click here</span> to upload their files-->
<!--                                    </div>-->
<!--                                </label>-->
<!--                                <ul class="mt-4" v-if="this.filelist.length">-->
<!--                                    <li class="text-sm p-1" v-for="file in filelist">-->
<!--                                        ${ file.name }<button class="ml-2" type="button" @click="remove(filelist.indexOf(file))" title="Remove file">remove</button>-->
<!--                                    </li>-->
<!--                                </ul>-->
<!--                            </div>-->
<!--                        </div>-->
                    </vs-col>
                </vs-row>
            </vs-card>
        </div>
        <vs-card style="margin-bottom: 0">
            <vs-table class="mt-3 custome-event" :data="dataTable" noDataText="データがありません。"
                      sst stripe>
                <template slot="thead">
                    <!--                    <vs-th class="width-50">-->
                    <!--                        <vs-checkbox :value="selectAll" @click=""/>-->
                    <!--                    </vs-th>-->
                    <vs-th class="min-width-250 pl-16">用途</vs-th>
                    <vs-th>
                        <p v-if="!isSettlementForm">日付</p>
                        <p v-else>支払日</p>
                    </vs-th>
                    <vs-th class="min-width-250">
                        <p v-if="!isSettlementForm">内容</p>
                        <p v-else>概要</p>
                    </vs-th>
                    <vs-th class="min-width-200">金額</vs-th>
                    <vs-th>
                        <p v-if="isSettlementForm">領収証/証憑</p>

                    </vs-th>
                    <vs-th  class="flex-end-ex">
                        <span v-if="createForm" class="btn-icon_th mr-5" @click="onHandleAction('up')">
                            <i class="fas fa-solid fa-caret-up"></i>
                        </span>
                        <span v-if="createForm" class="btn-icon_th" @click="onHandleAction('down')">
                            <i class="fas fa-solid fa-caret-down"></i>
                        </span>
                    </vs-th>
                </template>
                <template slot-scope="{data}">
                    <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data"
                           :class="{active:indextr===indexItemSelect ,'createform':createForm ,'viewForm':viewForm}">
                        <!--                        <vs-td><vs-checkbox  :value="tr.selected" @click="onRowCheckboxClick(tr)" /></vs-td>-->
                        <td class="pl-10" @click="handleCheckAppItem(indextr)">{{ tr.wtsm_name }}</td>
                        <td @click="handleCheckAppItem(indextr)">
                            {{ tr.expected_pay_date | moment("MM/DD") }}
                        </td>
<!--                        <td v-if="editForm" class="td-date">-->
<!--                            <flat-pickr class="date-view" :class="{is_createform:indextr===indexItemSelect}"  v-model="tr.expected_pay_date"-->
<!--                                    v-validate="'required'" data-vv-scope="item_form"-->
<!--                                    name="expected_pay_date"-->
<!--                                    @input="fomatDatePay(indextr)"-->
<!--                                    :config="completedConfigDatePaydataDate">-->
<!--                            </flat-pickr>-->
<!--                            <span class="input-date" :class="{'is_createform':indextr===indexItemSelect , 'is_edit':editForm}"></span>-->
<!--                        </td>-->


                        <td class="td_text-rank" @click="handleCheckAppItem(indextr)">
                            <p v-if="(tr.wtsm_name != listElementForTransportForm)">{{ tr.remarks }}</p>
                            <p v-if="(tr.wtsm_name == listElementForTransportForm) && (tr.from_station && !tr.to_station)">
                                {{ tr.from_station }}</p>
                            <p v-if="(tr.wtsm_name == listElementForTransportForm) && (!tr.from_station && tr.to_station)">
                                {{ tr.to_station }}</p>
                            <p v-if="(tr.wtsm_name == listElementForTransportForm) && (tr.from_station && tr.to_station)">
                                {{ tr.from_station }} → {{ tr.to_station }}</p>
                        </td>
                        <td @click="handleCheckAppItem(indextr)">{{ formatPrice(tr.expected_pay_amt) }}</td>
                        <td v-if="isSettlementForm"
                            @click="handleCheckAppItem(indextr)">
                            <div v-for="vocherOp in listWtsmNameSelect">
                                <div v-if="tr.wtsm_name === vocherOp.wtsm_name" >
                                        <p v-if="tr.submit_method == 1 &&
                                        (tr.files && tr.newFiles && (tr.files.length + tr.newFiles.length ) > 0)" >✓</p>
                                        <p v-else>―</p>
                                </div>
                            </div>
                        </td>
                        <td v-if="!isSettlementForm">
                        </td>
                        <td class="flex-end-ex">
                            <div v-if="createForm || editForm ||isBackupData">
                                <span @click="onShowDetailTAppItem(indextr)" class="btn-icon_tb ">
                                    <i class="fas fa-solid fa-pen"></i>
                                </span>
                                <button v-if="!isDisableAddItem" class="btn-icon_tb ml-5"
                                        :class="{un_active:isDisableAddItem}"
                                        @click="onCloneEpsTAppItems(indextr)">
                                    <i class="fas fa-solid fa-copy"></i>
                                </button>
                                <span v-if="createForm || editForm || isCreateFormnAdvanceComplete"
                                      @click="onShowRemoveTAppItem(indextr)" class="btn-icon_tb ml-5">
                                    <i class="fas fa-solid fa-trash"></i>
                                </span>
                            </div>
                        </td>
                    </vs-tr>
                </template>
            </vs-table>

<!--            <flat-pickr class="w-full ml-5 edit-times" :class="{active:isChangeItemDateTime}" v-model="formDetail.expected_pay_date"-->
<!--                        v-validate="'required'" data-vv-scope="item_form"-->
<!--                        name="expected_pay_date"-->
<!--                        :disabled="viewForm" :config="completedConfigDatePayDate">-->
<!--            </flat-pickr>-->

<!--            <span class="text-danger" v-show="!dataTable || !dataTable.length">データがありません。</span>-->
            <div>
                <vs-row vs-type="flex" class="w-full mt-12">
                    <vs-col vs-w="2" >
                        <vs-button class="square ml-12" color="#bdc3c7"
                                   @click="onGoBack"> 戻る
                        </vs-button>
                    </vs-col>
                    <vs-col vs-w="10">
                        <vs-row vs-type="flex" vs-justify="space-around">
                            <vs-button class="square ml-12 " color="primary"
                                       v-if="circularStatusToDo && viewForm"
                                       @click="onEdit()">編集
                            </vs-button>
<!--                            <vs-button class="square ml-12 " color="primary"-->
<!--                                       v-if="circularStatusToDo  && viewForm"-->
<!--                                       @click="onCloneEpsTAppAndItems">複製-->
<!--                            </vs-button>-->
                            <vs-col offset="4" v-if="createForm || editForm || isBackupData" vs-w="4">
                                <vs-button class="square ml-4" color="primary" @click="onSave">
                                    回覧作成
                                </vs-button>
                            </vs-col>

                            <vs-button class="square " v-if="circularStatusDoing && circularId"
                                       color="primary" @click="onMoveSentCircular">回覧文書
                            </vs-button>
                            <vs-button class="square " v-if="circularStatusDone && isAdvanceForm"
                                       color="primary" @click="onShowPopupCreateFormSettlement">精算書作成
                            </vs-button>
                            <vs-button class="square " v-if="circularStatusDone && circularId"
                                       color="primary" @click="onMoveToCompleteScreen">回覧文書
                            </vs-button>
                            <vs-button class="square ml-12 " color="primary"
                                       v-if="circularStatusToDo && viewForm"
                                       @click="onMoveToExpenseCircularScreen(true)">回覧文書
                            </vs-button>
                            <vs-button class="square ml-12 " color="primary"
                                       v-if="circularStatusToDo && viewForm"
                                       @click="onDeleteEpsTAppItems">削除
                            </vs-button>

                        </vs-row>
                    </vs-col>
                </vs-row>
            </div>
        </vs-card>

        <vs-popup class="popup-dialogs detail-popup"
                  classContent="popup-example_ex"
                  :title="titlePopup"
                  @close="onCloseDialog"
                  :active.sync="showDialog">
            <div class="mb-3"
                 :class="{'height_popup_nor':showNormalForm && !viewForm,
                 'height_popup_ex':!viewForm} ">
                <vs-row class=" mt-5" :class="{normal_form:!viewForm}">
                    <vs-row>
                        <div>
                            <label class="label-from" style="font-size: 16px;">
                                用途：{{ wtsmNameSelected }}
                            </label>
                        </div>
                    </vs-row>
                    <!--                    Normal Form -->
                    <vs-row vs-type="flex"
                            class="w-full mt-10">
                        <vs-col vs-lg="8" vs-type="flex">
                            <vs-row vs-type="flex" class="mb-6">
                                <vs-col vs-type="flex" vs-align="center"
                                        vs-justify="flex-end" vs-w="3">
                                    <label v-if="isSettlementForm && showNormalForm && !viewForm"
                                           class="label-from">支払日<span
                                        class="text-danger">(*)</span>:
                                    </label>
                                    <label v-if="!isSettlementForm && showNormalForm && !viewForm"
                                           class="label-from">支払予定日<span
                                        class="text-danger">(*)</span>:
                                    </label>
                                    <label v-if="viewForm && showNormalForm" class=" label-from">予定日 :</label>
                                    <label v-if="!showNormalForm" class=" label-from">
                                        <p v-if=" viewForm">予定日:</p>
                                        <p v-else>日付
                                            <span class="text-danger">(*)</span>:
                                        </p>
                                    </label>
                                </vs-col>
                                <vs-col vs-w="8">
                                    <div class="vs-con-input input-mess" v-if="!viewForm" >
                                        <flat-pickr class="w-full ml-5 " v-model="formDetail.expected_pay_date"
                                                    v-validate="'required'" data-vv-scope="item_form"
                                                    name="expected_pay_date"
                                                    :disabled="viewForm" :config="completedConfigDatePayDate">
                                        </flat-pickr>
                                        <span class="text-danger ml-5 label-err"
                                              v-show="errors.has('item_form.expected_pay_date')">必須項目です。</span>
<!--                                        <span class="text-danger ml-5" v-if="!showNormalForm"-->
<!--                                              v-show="errors.has('item_form.expected_pay_date')">必須項目です。</span>-->
                                    </div>
                                    <label v-else class="label-from ml-4">
                                        {{ formDetail.expected_pay_date| moment("YYYY/MM/DD") }}
                                    </label>
                                </vs-col>
                            </vs-row>
                        </vs-col>
                    </vs-row>

                    <vs-row>
                        <div v-if="showNormalForm" class="w-full">
                            <vs-row vs-type="flex" class="w-full" >
                                <vs-col vs-lg="8" vs-type="flex">
                                    <vs-row vs-type="flex" class="mb-6 from-input">
                                        <vs-col vs-type="flex" vs-align="center"
                                                vs-justify="flex-end" vs-w="3">
                                            <label v-if="viewForm" class=" label-from">金額 :</label>
                                            <label v-else class=" label-from">単価 :</label>
                                        </vs-col>
                                        <vs-col vs-w="8" v-if="!viewForm">
                                            <vs-input :disabled="viewForm"
                                                      @blur="changeSetIndexValue(formDetail, $event.target.value, 'unit_price')"
                                                      min="0" maxlength="10"
                                                      class="inputx w-full ml-5"
                                                      v-model="formDetail.unit_price"
                                                      @keypress="isNumber($event)"/>
<!--                 TODO put into input    @blur="changeSetIndexValue(formDetail, $event.target.value, 'unit_price')"-->
                                        </vs-col>
                                        <label v-else class="label-from ml-4">
                                            {{ formDetail.expected_pay_amt }} 円
                                        </label>
                                    </vs-row>
                                </vs-col>
                                <vs-col vs-lg="4" v-if="!viewForm">
                                    <vs-row vs-type="flex" class="mb-6 from-input input-mess">
                                        <vs-col vs-type="flex" vs-justify="flex-end"
                                                vs-align="center" vs-w="3">
                                            <label class=" label-from">数量<span
                                                v-if="!viewForm"
                                                class="text-danger">(*)</span> :</label>
                                        </vs-col>
                                        <vs-col vs-w="6">
                                            <vs-input v-if="!isViewOnlyFormDetail" type="number"
                                                      @blur="changeSetIndexValue(formDetail, $event.target.value, 'quantity')"
                                                      class="inputx w-full ml-5"
                                                      v-validate="'required|min_value:1'"
                                                      data-vv-scope="item_form"
                                                      name="quantity"
                                                      min="0" @keypress="isNumber($event)"
                                                      :disabled="viewForm"
                                                      v-model="formDetail.quantity"/>
                                        </vs-col>
                                        <vs-col class="label-err text-error" style="text-align: center">
                                             <span class="text-danger ml-4"
                                                   v-show="errors.has('item_form.quantity')">{{ errors.first('item_form.quantity')}}</span>
                                        </vs-col >
                                    </vs-row>
                                </vs-col>
                            </vs-row>
                        </div>
                        <div v-else class="w-full">
                            <vs-row vs-type="flex" class="w-full">
                                <vs-col vs-lg="8" vs-type="flex">
                                    <vs-row vs-type="flex" class="mb-6 from-input">
                                        <vs-col vs-type="flex" vs-align="center"
                                                vs-justify="flex-end" vs-w="3">
                                            <label class=" label-from">交通機関 :</label>
                                        </vs-col>
                                        <vs-col vs-w="8">
                                            <vs-input class="inputx w-full ml-5" v-if="!viewForm"
                                                      :disabled="viewForm" maxlength="50"
                                                      v-model="formDetail.traffic_facility_name"/>
                                            <label v-else class="label-from ml-4">
                                                {{ formDetail.traffic_facility_name }}
                                            </label>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>
                            </vs-row>
                            <vs-row vs-type="flex" class="mb-1">
                                <vs-col vs-type="flex" vs-align="center"
                                        vs-justify="flex-end" vs-w="2">
                                    <label class=" label-from">利用区間 :</label>
                                </vs-col>
                                <vs-col vs-type="flex" vs-align="center"
                                        vs-w="8" class="ml-4" style="word-break:break-all;"
                                        v-if="viewForm">
                                    <label class=" label-from">{{ formDetail.from_station }} ～
                                        {{ formDetail.to_station }}</label>
                                    <label class="ml-4 label-from">
                                        ( {{ listRoundTrips[formDetail.roundtrip_flag] }} )
                                    </label>
                                </vs-col>
                            </vs-row>
                            <vs-row class="w-full" v-if="!viewForm">
                                <vs-col vs-lg="8" vs-type="flex">
                                    <vs-row vs-type="flex" class="mb-6 from-input">
                                        <vs-col vs-type="flex" vs-align="center"
                                                vs-justify="flex-end" vs-w="3">
                                            <label class=" label-from">出発 :</label>
                                        </vs-col>
                                        <vs-col vs-w="8">
                                            <vs-input class="inputx w-full ml-5"
                                                      :disabled="viewForm" maxlength="50"
                                                      v-model="formDetail.from_station"/>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>
                            </vs-row>
                            <vs-row vs-type="flex" class="w-full" v-if="!viewForm">
                                <vs-col vs-lg="8" vs-type="flex">
                                    <vs-row vs-type="flex" class="mb-6 from-input">
                                        <vs-col vs-type="flex" vs-align="center"
                                                vs-justify="flex-end" vs-w="3">
                                            <label class=" label-from">到着 :</label>
                                        </vs-col>
                                        <vs-col vs-w="8">
                                            <vs-input class="inputx w-full ml-4"
                                                      :disabled="viewForm" maxlength="50"
                                                      v-model="formDetail.to_station"/>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>
                            </vs-row>
                            <vs-row vs-type="flex" class="w-full ">
                                <vs-col vs-lg="8" vs-type="flex">
                                    <vs-row vs-type="flex" class="mb-6">
                                        <vs-col vs-type="flex" vs-align="center"
                                                vs-justify="flex-end" vs-w="3">
                                        </vs-col>
                                        <vs-col vs-w="8" class="ml-5" v-if="!viewForm">
                                            <vs-radio class="mr-12"
                                                      :key="TripIndex"
                                                      v-for="(listRoundTrip,TripIndex) in listRoundTrips"
                                                      v-model="formDetail.roundtrip_flag"
                                                      :disabled="viewForm"
                                                      vs-name="roundtrip_flag"
                                                      :vs-value="TripIndex">
                                                {{ listRoundTrip }}
                                            </vs-radio>
                                        </vs-col>
                                    </vs-row>
                                </vs-col>
                            </vs-row>
                        </div>
                    </vs-row>
                    <vs-row vs-type="flex" class="w-full">
                        <vs-col vs-lg="8" vs-type="flex" v-if="!viewForm" class="mb-6">
                            <vs-row vs-type="flex" class="from-input">
                                <vs-col vs-type="flex" vs-align="center"
                                        vs-justify="flex-end" vs-w="3">
                                    <label v-if="isSettlementForm && showNormalForm "
                                           class=" label-from">支払金額 :</label>
                                    <label v-if="!isSettlementForm && showNormalForm "
                                           class=" label-from">支払予定金額 :</label>
                                    <label v-if="isSettlementForm && !showNormalForm "
                                           class=" label-from">支払金額 :</label>
                                    <label v-if="!isSettlementForm && !showNormalForm "
                                           class=" label-from">金額 :</label>
                                </vs-col>
                                <vs-col vs-type="flex" vs-align="center"
                                        v-if="viewForm && !showNormalForm"
                                        vs-justify="flex-end" vs-w="3">
                                    <label class=" label-from">金額 :</label>
                                </vs-col>
                                <vs-col vs-w="8" vs-type="flex" class="ml-5">
                                    <vs-input v-if="!viewForm" maxlength="10"
                                              @blur="changeSetIndexValue(formDetail, $event.target.value, 'expected_pay_amt')"
                                              class="inputx w-full"
                                              min="0" @keypress="isNumber($event)"
                                              :disabled="viewForm"
                                              v-model="formDetail.expected_pay_amt"/>
<!--          TODO put into input   @blur="changeSetIndexValue(formDetail, $event.target.value, 'expected_pay_amt')"-->
                                    <label  v-if="viewForm && !showNormalForm"
                                            class="label-from ml-4">{{formDetail.expected_pay_amt }} 円
                                    </label>
                                </vs-col>
                            </vs-row>
                        </vs-col>
                        <vs-col vs-lg="8" vs-type="flex" class="mb-6" v-if="viewForm && !showNormalForm">
                            <vs-row vs-type="flex" class="from-input">
                                <vs-col vs-type="flex" vs-align="center"
                                        vs-justify="flex-end" vs-w="3">
                                    <label class=" label-from">金額 :</label>
                                </vs-col>
                                <vs-col vs-w="8" vs-type="flex" class="ml-4 ">
                                    <label class="label-from ">
                                        {{ formDetail.expected_pay_amt }} 円
                                    </label>
                                </vs-col>
                            </vs-row>
                        </vs-col>
                        <vs-col vs-lg="4" style="position: relative"
                                v-if="isSettlementForm && showNormalForm && !viewForm">
                            <vs-row vs-type="flex" class="from-input" v-if="taxOption != 0">
                                <vs-col vs-type="flex" vs-justify="flex-end"
                                        vs-align="center" vs-w="4">
                                    <label class=" label-from">消費税<span
                                        v-if="taxOption === 1 && !viewForm"
                                        class="text-danger">(*)</span> ：</label>
                                </vs-col>
                                <vs-col vs-w="6" class="ml-5">
                                    <vs-col vs-w="10">
                                        <vs-input type="number" class="inputx w-full" v-if="!viewForm"
                                                  min="1" @keypress="isNumber($event)"
                                                  @blur="changeSetIndexValue(formDetail, $event.target.value, 'tax')"
                                                  v-validate.continues="{ required: taxOption === 1,min_value:1} "
                                                  data-vv-scope="item_form" name="tax"
                                                  data-vv-as="消費税"
                                                  :disabled="viewForm"
                                                  v-model="formDetail.tax"/>
<!--                                        <span class="text-danger ml-5"-->
<!--                                              v-show="errors.has('item_form.tax')">消費税は必須項目です。</span>-->
                                    </vs-col>
                                    <vs-col vs-w="2" vs-type="flex"
                                            vs-align="center" style="height: 35px; ">
                                        <label class="label-from w-2 ml-2"
                                               style="font-size: 18px;">%</label>
                                    </vs-col>
                                </vs-col>
                            </vs-row>
                            <vs-row style="position: absolute">
                                 <span class="text-danger w-100 mr-12" style="text-align: end"
                                       v-show="errors.has('item_form.tax')">{{ errors.first('item_form.tax')}}</span>
                            </vs-row>
                        </vs-col>
                    </vs-row>
                    <vs-row v-if="showNormalForm" vs-type="flex" class="w-full">
                        <vs-col vs-lg="8" vs-type="flex">
                            <vs-row vs-type="flex" class="mb-6 from-input" v-if="numPeopleOption != 0">
                                <vs-col vs-type="flex" vs-align="center"
                                        vs-justify="flex-end" vs-w="3">
                                    <label class=" label-from">人数<span
                                        v-if="numPeopleOption === 2 && !viewForm"
                                        class="text-danger">(*)</span> :</label>
                                </vs-col>
                                <vs-col vs-w="8" class="input-mess"
                                        vs-align="center" style="flex-wrap: wrap">
                                    <vs-input type="number" value="1" v-if="!viewForm"
                                              class="w-full ml-5"
                                              min="0" @keypress="isNumber($event)"
                                              @blur="changeSetIndexValue(formDetail, $event.target.value, 'numof_ppl')"
                                              v-validate=" { required: numPeopleOption === 2,min_value:1 }"
                                              data-vv-scope="item_form" name="numof_ppl"
                                              :disabled="viewForm"
                                              v-model="formDetail.numof_ppl"/>
                                    <label v-else class="label-from ml-4">{{ formDetail.numof_ppl }} 名</label>
                                    <span class="text-danger w-100 ml-5 label-err"
                                          v-show="errors.has('item_form.numof_ppl')">{{ errors.first('item_form.numof_ppl')}}</span>
                                </vs-col>
                            </vs-row>
                        </vs-col>
                    </vs-row>
                    <vs-row vs-type="flex" class="mb-6"
                            v-if="isSettlementForm && showNormalForm
                            && !viewForm && numPeopleOption != 0">
                        <vs-col vs-type="flex" vs-justify="flex-end" vs-w="2">
                        </vs-col>
                        <vs-col vs-w="10">
                            <vs-col vs-type="flex" vs-w="11" style="flex-wrap: wrap">
                                <vs-col  class="ml-5" style="flex-wrap: wrap">
                                    {{ formDetail.num_people_describe }}
                                </vs-col>
                            </vs-col>
                        </vs-col>
                    </vs-row>
                    <vs-row vs-type="flex" class="mb-6" v-if="detailOption != 0">
                        <vs-col vs-type="flex" vs-justify="flex-end" vs-w="2">
                            <label class=" label-from">
                                <span v-if="showNormalForm">詳細 <span
                                    v-if="detailOption === 2 && !viewForm"
                                    class="text-danger">(*)</span> :
                                </span>
                                <span v-else>備考 <span
                                    v-if="detailOption === 2 && !viewForm"
                                    class="text-danger">(*)</span> : </span>
                            </label>
                        </vs-col>
                        <vs-col vs-w="10">
                            <vs-col vs-type="flex" vs-w="11" style="flex-wrap: wrap">
                                <vs-textarea class="ml-5" placeholder="" v-if="!viewForm"
                                             :disabled="viewForm" maxlength="1000"
                                             v-validate=" { required: detailOption === 2 }" data-vv-scope="item_form"
                                             name="remarks"
                                             v-model="formDetail.remarks" rows="3"/>
                                <label vs-w="10" v-else
                                       class="label-from ml-4" style="word-break:break-all;">
                                    {{ formDetail.remarks }}
                                </label>
                                <span class="text-danger ml-5"
                                      v-show="errors.has('item_form.remarks')">必須項目です。</span>
<!--                                <span class="text-danger ml-5"-->
<!--                                     v-if="!isSettlementForm" v-show="errors.has('item_form.remarks')">必須項目です。</span>-->
                            </vs-col>
                        </vs-col>
                    </vs-row>
                    <vs-row vs-type="flex" class="mb-6"
                            v-if="isSettlementForm && showNormalForm
                            && !viewForm && detailOption != 0">
                        <vs-col vs-type="flex" vs-justify="flex-end" vs-w="2">
                        </vs-col>
                        <vs-col vs-w="10">
                            <vs-col vs-type="flex" vs-w="11" style="flex-wrap: wrap">
                                <vs-col  class="ml-5" style="flex-wrap: wrap">
                                    {{ formDetail.detail_describe }}
                                </vs-col>
                            </vs-col>
                        </vs-col>
                    </vs-row>
                    <vs-row v-if="voucherOption != 0">
                        <vs-row v-if="isSettlementForm"
                                vs-type="flex" class="mb-6">
                            <vs-col vs-type="flex"
                                    vs-justify="flex-end" vs-w="2">
                                <label class=" label-from">領収証／証憑<span
                                    v-if="formDetail.submit_method == '1' && !viewForm && !showNormalForm"
                                    class="text-danger">(*)</span> : </label>
                            </vs-col>
                            <vs-col vs-w="9" v-if="isSettlementForm && !showNormalForm && !viewForm">
                                <vs-row class="mb-6">
                                    <vs-col vs-w="12" class="ml-5">
                                        <vs-row vs-type="flex" class="upload_files h-full">
                                            <div v-if="!viewForm" class="centerx w-full p-2">
                                                <div class="file-upload-box" @click="() => $refs.itemFileUpload.click()"
                                                     @dragover="dragover" @dragleave="dragleave" @drop="dropItem">
                                                    <p>
                                                        <span>ファイル添付</span><br/>
                                                        クリックまたはドラッグ＆ドロップ
                                                    </p>

                                                </div>
                                            </div>
                                            <input v-if="!viewForm" type="file" multiple ref="itemFileUpload"
                                                   @change="onItemFileChange">
                                            <ul class="file-upload-list ml-4">
                                                <li v-for="(file, index) in itemUploadFiles" :key="`upload-${index}`">
                                                    <span class="text-primary">{{ file.name }}</span>
                                                    <feather-icon
                                                        icon="XIcon" class="cursor-pointer"
                                                        @click.stop="() => onDeleteItemUploadFile(index)"
                                                        svg-classes="h-4 w-4 stroke-current text-danger">

                                                    </feather-icon>
                                                </li>
                                                <li v-if="viewForm" v-for="(file, index) in formDetail.files"
                                                    :key="`file-${index}`" class="cursor-pointer"
                                                    @click.stop="() => onDownloadFile(file)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                                    <feather-icon
                                                        icon="DownloadIcon" class="cursor-pointer ml-2"

                                                        svg-classes="h-4 w-4 stroke-current text-danger">

                                                    </feather-icon>
                                                </li>
                                                <li v-if="!viewForm" v-for="(file, index) in formDetail.files"
                                                    :key="`file-${index}`" class="cursor-pointer"
                                                    @click.stop="() => onDeleteItemFile(file, index)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                                    <feather-icon
                                                        icon="XIcon" class="cursor-pointer ml-2"
                                                        svg-classes="h-4 w-4 stroke-current text-danger">
                                                    </feather-icon>
                                                </li>
                                            </ul>
                                            <span class="text-danger ml-4"
                                                  v-show="errorsHandle.requireFileItem">{{ errorHandleMessages.requireFileItem }}</span>
                                        </vs-row>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                            <vs-col vs-w="9" v-if="isSettlementForm && viewForm">
                                <vs-row class="mb-6" v-if="formDetail.files && formDetail.files.length > 0">
                                    <vs-row vs-type="flex" class="h-full">
                                        <vs-col vs-w="12" class="ml-5">
                                            <ul class="file-upload-list">
                                                <li v-for="(file, index) in itemUploadFiles" :key="`upload-${index}`">
                                                    <span class="text-primary">{{ file.name }}</span>
                                                </li>
                                                <li v-if="viewForm" v-for="(file, index) in formDetail.files"
                                                    :key="`file-${index}`" class="cursor-pointer"
                                                    @click.stop="() => onDownloadFile(file)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                                    <feather-icon
                                                        icon="DownloadIcon" class="cursor-pointer ml-2"
                                                        svg-classes="h-4 w-4 stroke-current text-danger">
                                                    </feather-icon>
                                                </li>
                                            </ul>
                                        </vs-col>
                                    </vs-row>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="isSettlementForm && showNormalForm && !viewForm"
                                class="mb-6">
                            <vs-col vs-w="2" vs-type="flex"
                                    vs-justify="flex-end" >
                                <span class="label-from mr-2" vs-type="flex">添付 <span
                                    v-if="formDetail.submit_method == '1' && !viewForm"
                                    class="text-danger">(*)</span> </span>
                                <div>
                                    <vs-radio
                                        v-model="formDetail.submit_method"
                                        vs-name="radioSubmitMethod" vs-value="1">
                                    </vs-radio>
                                </div>
                            </vs-col>
                            <vs-col vs-w="9" class="ml-5">
                                <vs-row vs-type="flex" vs-col="2" class="mb-6" >
                                    <vs-row vs-type="flex" class="upload_files h-full">
                                        <div class="centerx w-full p-2">

                                            <button v-if="!viewForm && formDetail.submit_method == '1'"
                                                    class="file-upload-box w-full"
                                                    @click="() => $refs.itemFileUpload.click()"
                                                    @dragover="dragover" @dragleave="dragleave" @drop="dropItem">
                                                <p>
                                                    <span>ファイル添付</span><br/>
                                                    クリックまたはドラッグ＆ドロップ
                                                </p>
                                            </button>
                                            <button v-if="!viewForm && formDetail.submit_method != '1'"
                                                    class="file-upload-box w-full" disabled
                                                    @click="() => $refs.itemFileUpload.click()">
                                                <p>
                                                    <span>ファイル添付</span><br/>
                                                    クリックまたはドラッグ＆ドロップ
                                                </p>
                                            </button>
                                            <input v-if="!viewForm" type="file" multiple ref="itemFileUpload"
                                                   @change="onItemFileChange"
                                                   name="uploadFile">
                                            <ul class="file-upload-list" v-if="formDetail.submit_method == '1'" >
                                                <li v-for="(file, index) in itemUploadFiles" :key="`upload-${index}`">
                                                    <span class="text-primary">{{ file.name }}</span>
                                                    <feather-icon
                                                        icon="XIcon" class="cursor-pointer"
                                                        @click.stop="() => onDeleteItemUploadFile(index)"
                                                        svg-classes="h-4 w-4 stroke-current text-danger">
                                                    </feather-icon>
                                                </li>
                                                <li v-if="viewForm" v-for="(file, index) in formDetail.files"
                                                    :key="`item-file-${index}`" class="cursor-pointer"
                                                    @click.stop="() => onDownloadFile(file)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                                    <feather-icon
                                                        icon="DownloadIcon" class="cursor-pointer ml-2"
                                                        svg-classes="h-4 w-4 stroke-current text-danger">
                                                    </feather-icon>
                                                </li>
                                                <li v-if="!viewForm" v-for="(file, index) in formDetail.files"
                                                    :key="`item-file-${index}`" class="cursor-pointer"
                                                    @click.stop="() => onDeleteItemFile(file, index)">
                                                <span class="text-primary">
                                                    {{ file.original_file_name }}
                                                </span>
                                                    <feather-icon
                                                        icon="XIcon" class="cursor-pointer ml-2"
                                                        svg-classes="h-4 w-4 stroke-current text-danger">
                                                    </feather-icon>
                                                </li>
                                            </ul>
                                            <span class="text-danger ml-4"
                                                  v-if="formDetail.submit_method == '1'"
                                                  v-show="errorsHandle.requireFileItem">{{ errorHandleMessages.requireFileItem }}</span>
                                        </div>
                                    </vs-row>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="isSettlementForm && showNormalForm && !viewForm"
                                class="mb-6">
                            <vs-col vs-w="2" vs-type="flex"
                                    vs-justify="flex-end" vs-align="center">
                                <span class="label-from mr-2 "> 別途提出<span
                                    v-if="formDetail.submit_method == '2' && !viewForm"
                                    class="text-danger">(*)</span> </span>
                                <vs-radio v-model="formDetail.submit_method"
                                          vs-type="flex" vs-align="flex-start"
                                          vs-name="radioSubmitMethod" vs-value="2">
                                </vs-radio>
                            </vs-col>
                            <vs-col vs-w="9" class="ml-5">
                                <vs-row vs-type="flex" vs-col="2">
                                    <vs-input class="inputx w-full"
                                              v-model="formDetail.submit_other_memo" maxlength="1000"
                                              v-if="formDetail.submit_method == '2'"
                                              v-validate=" { required: formDetail.submit_method == '2' }"
                                              data-vv-scope="item_form" name="formDetail.submit_method"
                                              :disabled="viewForm"
                                    />
                                    <vs-input class="inputx w-full"
                                              formDetail.submit_other_memo="" disabled="true" v-else/>
                                    <span class="text-danger ml-5"
                                          v-show="errors.has('item_form.formDetail.submit_method')">別途提出内容は必須項目です。</span>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                        <vs-row v-if="isSettlementForm && showNormalForm && !viewForm"
                                class="mb-6">
                            <vs-col vs-w="2" vs-type="flex"
                                    vs-justify="flex-end">
                                <span class="label-from mr-2 " vs-type="flex"
                                      vs-align="center"> 無し</span>
                                <div>
                                    <vs-radio v-model="formDetail.submit_method"
                                              vs-type="flex" vs-align="flex-start"
                                              vs-name="radioSubmitMethod" vs-value="9">
                                    </vs-radio>
                                </div>
                            </vs-col>
                            <vs-col vs-w="9" class="ml-5">
                                <vs-row vs-type="flex" vs-col="2"
                                        class="mb-6">
                                    <vs-col vs-w="2" vs-type="flex"
                                            vs-justify="center">
                                        理由
                                    </vs-col>
                                    <vs-col vs-w="10">
                                        <vs-row vs-type="flex" class="mb-6">
                                            <vs-radio v-model="formDetail.nonsubmit_type" vs-name="radioNonSubmitType"
                                                      vs-value="0" this.checked=false class="mr-12 ml-4"
                                                      v-if="formDetail.submit_method == '9'">
                                                未発行
                                            </vs-radio>
                                            <vs-radio formDetail.nonsubmit_type="false"
                                                      vs-name="radioNonSubmitType" class="mr-12 ml-4"
                                                      vs-value="0" this.checked=false disabled="true" v-else>
                                                未発行
                                            </vs-radio>
                                            <vs-radio v-model="formDetail.nonsubmit_type"
                                                      class="ml-10"
                                                      vs-name="radioNonSubmitType"
                                                      vs-value="1" this.checked=false
                                                      v-if="formDetail.submit_method == '9'">
                                                紛失
                                            </vs-radio>
                                            <vs-radio formDetail.nonsubmit_type="false"
                                                      class="ml-10"
                                                      vs-name="radioNonSubmitType"
                                                      vs-value="1" this.checked=false disabled="true" v-else>
                                                紛失
                                            </vs-radio>
                                        </vs-row>
                                        <vs-row>
                                            <vs-col vs-w="3" class="ml-4">
                                                <vs-radio v-model="formDetail.nonsubmit_type"
                                                          vs-name="radioNonSubmitType" vs-value="9" this.checked=false
                                                          v-if="formDetail.submit_method == '9'">
                                                    その他<span
                                                    v-if="formDetail.submit_method == '9'
                                                    && !viewForm && formDetail.nonsubmit_type=='9'"
                                                    class="text-danger">(*)</span>
                                                </vs-radio>
                                                <vs-radio  formDetail.nonsubmit_type="false"
                                                          vs-name="radioNonSubmitType" vs-value="9" this.checked=false
                                                          disabled="true"
                                                          v-else>
                                                    その他
                                                </vs-radio>
                                            </vs-col>
                                            <vs-col vs-w="8">
                                                <vs-input class="inputx w-full ml-8" maxlength="1000"
                                                          v-model="formDetail.nonsubmit_reason"
                                                          v-validate=" { required: formDetail.nonsubmit_type== '9'
                                                          && formDetail.submit_method== '9' }"
                                                          data-vv-scope="item_form" name="formDetail.nonsubmit_reason"
                                                          v-if="formDetail.nonsubmit_type=='9' && formDetail.submit_method =='9'"/>
                                                <vs-input class="inputx w-full ml-8" formDetail.nonsubmit_reason=""
                                                          v-else disabled="true"/>
                                                <span class="text-danger ml-5"
                                                      v-show="errors.has('item_form.formDetail.nonsubmit_reason')">その他未提出理由は必須項目です。</span>
                                            </vs-col>
                                        </vs-row>
                                    </vs-col>
                                </vs-row>
                            </vs-col>
                        </vs-row>
                    </vs-row>
                    <vs-row vs-type="flex" class="w-full mb-4 mt-4">
                        <vs-col vs-type="flex" vs-lg="5">
                            <vs-button class="square ml-12 " color="#bdc3c7" v-if="!viewForm"
                                       @click="onCloseDialog">キャンセル
                            </vs-button>
                            <vs-button class="square ml-12 " color="#bdc3c7" v-else
                                       @click="onCloseDialog">閉じる
                            </vs-button>
                        </vs-col>
                        <vs-col  vs-lg="4" v-if="!viewForm">
                            <vs-button class="square ml-12 " color="primary"
                                       @click="onSaveEpsTAppItems">追加
                            </vs-button>
                        </vs-col>
                    </vs-row>
                </vs-row>
            </div>
        </vs-popup>
        <vs-popup class="popup-dialog detail-popup"
                  classContent="popup-example"
                  @close=""
                  title="精算書作成"
                  :active.sync="showPopupCreateFormSettlement">
            <div class="mb-3">
                <div class="mb-4">
                    申請書：{{ formName }} 目的：{{ purposeName }}
                </div>
                <div class="mb-6">
                    <vs-row class="mb-3" v-if="mFormRelationDataPopup"
                            vs-type="flex">
                        <vs-col vs-w="3"
                                vs-type="flex"
                                vs-justify="flex-end"
                                vs-align="center"
                                class="text-decoration-underline
                            font-color-link-primary">
                            <a><span class="text-decoration-underline font-color-link-primary"
                                  style="cursor: pointer"
                                  @click="moveToScreenSettlement(mFormRelationDataPopup)">
                                {{ mFormRelationDataPopup.form_name }}</span>
                            </a>
                        </vs-col>
                        <vs-col vs-w="1"></vs-col>
                        <vs-col vs-w="8" class=""
                                vs-type="flex"
                                vs-align="center">{{ mFormRelationDataPopup.form_describe }}
                        </vs-col>
                    </vs-row>
                </div>
            </div>
        </vs-popup>
        <vs-popup classContent="popup-example"
                  :title="isDeleteTappItem ? '用途削除' : '経費申請書の削除'"
                  :active.sync="confirmDelete">
            <vs-row class="mt-3">
                <vs-col v-if="isDeleteTappItem" vs-type="flex" vs-w="12">選択されている用途を削除しますか。</vs-col>
                <vs-col v-else vs-type="flex" vs-w="12">選択されている経費申請書を削除します。</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <div>
                        <vs-button v-if="isDeleteTappItem" @click="deleteEpsTAppItem" color="danger">削除 </vs-button>
                        <vs-button v-else @click="deleteEpsTAppData" color="danger">削除</vs-button>
                    </div>
                    <vs-button @click="onCloseDialogConfirmDelete" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
</template>
<script>


import {mapActions, mapState} from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import {EXPENSE} from '../../enums/expense';
import utils from '../../utils/utils';

import {Validator} from 'vee-validate';

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import ja from 'vee-validate/dist/locale/ja';
// import { ValidationProvider } from 'vee-validate/dist/vee-validate.esm';
const dict = {
    custom: {
        tax: {
            required: '必須項目です。',
            min_value: '１から入力してください。'
        },
        numof_ppl: {
            required: '必須項目です。',
            min_value: '１から入力してください。'
        },
        uploadFile: {
            required: 'ファイル添付を選択してください。',
            size: '容量は3MB以下で添付してください。'
        },
        quantity:{
            required: '必須項目です。',
            min_value: '必須項目です。'
        }
    }
};

Validator.localize('ja', ja);

// Override and merge the dictionaries
Validator.localize('ja', dict);

export default {

    components: {
        VxPagination,
        flatPickr
        // ,
        // ValidationProvider
    },
    data() {
        return {
            // filelist: [],
            dateFrom: '',
            dateTo: '',
            radioSubmitMethod: '1',
            itemMax: 0,
            isDisableAddItem: false,
            numPeopleOption: EXPENSE.M_WTSM_NUM_PEOPLE_OPTION_NONE,
            detailOption: EXPENSE.M_WTSM_DETAIL_OPTION_NONE,
            voucherOption: EXPENSE.M_WTSM_VOUCHER_OPTION_NONE,
            taxOption: EXPENSE.M_WTSM_TAX_OPTION_NONE,
            statusDisplay: EXPENSE.T_APP_STATUS_DISPLAY,
            pagination: {
                totalPage: 0,
                currentPage: 1,
                limit: 10,
                totalItem: 0,
                from: 1,
                to: 0
            },
            currentIndex: -1,
            listMFormPurposeSelect: [],
            listWtsmNameSelect: [],
            showPopupCreateFormSettlement: false,
            id: null,
            tAppItemsId: null,
            target_period_from: null,
            target_period_to: null,
            form_dtl: "",
            expected_amt: 0,
            desired_suspay_amt: 0,
            epsDiff: 0,
            eps_amt: 0,
            suspay_amt: 0,

            formDetail: {
                expected_pay_date: "",
                to_station: "",
                from_station: "",
                traffic_facility_name: "",
                roundtrip_flag: EXPENSE.EPS_T_APP_ITEMS_ROUNDTRIP_FLAG_DEFAULT,
                unit_price: 0,
                expected_pay_amt: 0,
                numof_ppl: 0,
                remarks: "",
                quantity: 0,
                submit_method: EXPENSE.EPS_T_APP_ITEMS_SUBMIT_METHOD_DEFAULT,
                tax: 0,
                num_people_describe: "",
                detail_describe: "",
                voucher_option: 0,
                submit_other_memo: "",
                nonsubmit_type: EXPENSE.EPS_T_APP_ITEMS_NONSUBMIT_TYPE_DEFAULT,
                nonsubmit_reason: "",
                files: []
            },
            completedConfigDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                dateFormat: "Y/m/d",
                maxDate: '',
                minDate: '',
                static: true,
            },
            completedConfigDatePayDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0,
                dateFormat: "Y/m/d",
                maxDate: '',
                minDate: '',
            },
            // completedConfigDatePaydataDate: {
            //     locale: Japanese,
            //     wrap: true,
            //     defaultHour: 0,
            //     dateFormat: "Y/m/d",
            //     maxDate: '',
            //     minDate: '',
            // },
            errorsHandle: {
              requireFileItem: false,
            },
            errorHandleMessages: {
              requireFileItem: 'ファイル添付を選択してください。'
            },
            dataTable: [],
            confirmDelete: false,
            showTransportationForm: false,
            showNormalForm: false,
            wtsmNameSelected: '',
            listElementForTransportForm: [
                '交通費'
            ],
            isAdvanceForm: false,
            isSettlementForm: false,
            circularStatusToDo: false,
            circularStatusDoing: false,
            circularStatusDone: false,
            departmentInfo: {
                department_name: '',
                full_name: '',
                id: '',
            },
            isViewOnlyFormDetail: false,
            showDialog: false,
            mFormRelationDataPopup: {
                form_code: null,
                form_describe: null,
                form_name: null,
                url_name: '',
            },
            isCreateFromSettlement: false,
            listRoundTrips: ['片道', '往復'],
            indexItemSelect: null,
            uploadFiles: [],
            itemUploadFiles: [],
            infoFiles: [],
            titlePopup:"",
            circularId: null,
            totalMaxFilesSize: 3145728, // 3MB to byte
            isDeleteTappItem:false,
            selectTappItem:'',
            itemsDelete:[],
            // editDate:'2022-05-17',
            // isChangeItemDateTime: false
        }
    },

    computed: {
        ...mapState({
            formName: state => state.expenseSettlement.formName,
            formCode: state => state.expenseSettlement.formCode,
            formType: state => state.expenseSettlement.formType,
            tAppStatus: state => state.expenseSettlement.tAppStatus,
            createForm: state => state.expenseSettlement.createForm,
            viewForm: state => state.expenseSettlement.viewForm,
            editForm: state => state.expenseSettlement.editForm,
            isBackupData: state => state.expenseSettlement.isBackupData,
            tAppDuplicateData: state => state.expenseSettlement.tAppDuplicateData,
            isCreateFormnAdvanceComplete: state => state.expenseSettlement.isCreateFormnAdvanceComplete,
        }),

        purposeName: {
            get() {
                return this.$store.state.expenseSettlement.purposeName
            },
            set(newVal) {
                this.$store.commit('expenseSettlement/updatePurposeName', newVal)
            }
        },

    },


    methods: {
        ...mapActions({
            getMFormPurposeDataSelect: "expenseSettlement/getMFormPurposeDataSelect",
            getEpsMFormRelation: "expenseSettlement/getEpsMFormRelation",
            getCurrentUserDepartmentInfo: "expenseSettlement/getCurrentUserDepartmentInfo",
            getListTAppItems: "expenseSettlement/getListTAppItems",
            saveExpense: "expenseSettlement/saveExpense",
            updateExpense: "expenseSettlement/updateExpense",
            createEpsTAppItem: "expenseSettlement/createEpsTAppItem",
            updateEpsTAppItem: "expenseSettlement/updateEpsTAppItem",
            duplicateEpsTAppItem: "expenseSettlement/duplicateEpsTAppItem",
            duplicateEpsTAppAndEpsTAppItems: "expenseSettlement/duplicateEpsTAppAndEpsTAppItems",
            getEpsMWtsmName: "expenseSettlement/getEpsMWtsmName",
            getEpsTAppItemDetail: "expenseSettlement/getEpsTAppItemDetail",
            createFormSettlementFromAdvanceForm: "expenseSettlement/createFormSettlementFromAdvanceForm",
            deleteEpsTAppAndItems: "expenseSettlement/deleteEpsTAppAndItems",
            displayAndValidatePrice: "expenseSettlement/displayAndValidatePrice",
            downloadFile: "expenseSettlement/downloadFile",
            deleteFile: "expenseSettlement/deleteFile",
            updateExpenseFormInput: "expenseSettlement/updateExpenseFormInput",
            updateExpenseCircularInfo: "expenseSettlement/updateExpenseCircularInfo",
            getCircularSentById: "expenseSettlement/getCircularSentById",
        }),


        // fomatDatePay(index){
        //
        //     let result = this.dataTable[index]
        //     if (result ) {
        //         const expected_pay_date = this.$moment(result .expected_pay_date, 'YYYY/MM/DD')
        //         if (expected_pay_date && expected_pay_date.isValid())
        //             this.dataTable[index].expected_pay_date = expected_pay_date.format('YYYY-MM-DD')
        //     }
        //
        //     // this.onValidateDateTime()
        //
        // },

        onValidateDateTime(){
            if (this.target_period_to){
                this.completedConfigDatePayDate.maxDate = this.target_period_to;
                const maxDate = this.$moment(this.completedConfigDatePayDate.maxDate, 'YYYY/MM/DD')
                if (maxDate && maxDate.isValid())
                    this.completedConfigDatePayDate.maxDate = maxDate.format('YYYY-MM-DD')
                    // this.completedConfigDatePaydataDate.maxDate = maxDate.format('YYYY-MM-DD')

            } else {
                this.completedConfigDatePayDate.maxDate = this.dateTo
                // this.completedConfigDatePaydataDate.maxDate = this.dateTo
            }

            if (this.target_period_from){
                this.completedConfigDatePayDate.minDate = this.target_period_from;
                const minDate = this.$moment(this.completedConfigDatePayDate.minDate, 'YYYY/MM/DD')
                if (minDate && minDate.isValid())
                    this.completedConfigDatePayDate.minDate = minDate.format('YYYY-MM-DD')
                    // this.completedConfigDatePaydataDate.minDate = minDate.format('YYYY-MM-DD')
            } else {
                this.completedConfigDatePayDate.minDate = this.dateFrom
                // this.completedConfigDatePaydataDate.maxDate  = this.dateFrom
            }
        },



        onHandleAction(action){
            if(this.dataTable.length > 1 && this.indexItemSelect != null) {
                if(action === 'up' ) {
                    if(this.indexItemSelect === 0 ) {
                        return;
                    }
                    let priorIndex = this.indexItemSelect - 1;
                    let itemCopy = {...this.dataTable[this.indexItemSelect]};
                    let priorItemCopy = {...this.dataTable[priorIndex]};
                    this.$set(this.dataTable, priorIndex, itemCopy);
                    this.$set(this.dataTable, this.indexItemSelect, priorItemCopy);
                    this.indexItemSelect = priorIndex;
                }
                else {
                    if(this.indexItemSelect === this.dataTable.length -1 ){
                        return;
                    }
                    let subsequentIndex = this.indexItemSelect + 1;
                    let itemCopy = {...this.dataTable[this.indexItemSelect]};
                    let subsequentItemCopy = {...this.dataTable[subsequentIndex]};
                    this.$set(this.dataTable, subsequentIndex, itemCopy);
                    this.$set(this.dataTable, this.indexItemSelect, subsequentItemCopy);
                    this.indexItemSelect = subsequentIndex;
                }
            }
        },
        onShowRemoveTAppItem(indextr) {
            if(Number.isInteger(indextr)){
                this.confirmDelete = true;
                this.isDeleteTappItem = true;
                this.selectTappItem = indextr;
            }
            // if (this.dataTable.length < this.itemMax) {
            //     this.isDisableAddItem = false
            // }
        },
        deleteEpsTAppItem(){
            if( this.isDeleteTappItem) {
                if(this.editForm) {
                    this.itemsDelete.push(this.dataTable[this.selectTappItem].id);
                }
                this.dataTable.splice(this.selectTappItem,1)
                this.confirmDelete = false;
            }
            if (this.dataTable.length < this.itemMax) {
                this.isDisableAddItem = false
            }
            this.onGetSumClient()
        },

        onGoBack() {
            if (!this.viewForm && !this.createForm) {
                if(this.isCreateFormnAdvanceComplete){
                    this.$router.push(`/calculation-expense`)
                } else {
                    this.$store.commit('expenseSettlement/updateCreateForm', false)
                    this.$store.commit('expenseSettlement/updateEditForm', false)
                    this.$store.commit('expenseSettlement/updateViewForm', true)
                    this.$store.commit('expenseSettlement/updateBackupData', false)
                    this.$store.commit('expenseSettlement/updateCreateFormnAdvanceComplete', false)
                    let data = {
                        id: this.id,
                    }
                    if (data.id) {
                        let params = {
                            form_type: this.formType
                        }
                        let url = utils.getUrlByFormType(params)
                        this.$router.push(`/calculation-expense/${url}/${data.id}`);
                    }
                }
            }
            else {
                this.$router.push(`/calculation-expense`)
            }
        },

        onShowDetailDialog() {
            this.onChangeTitle()
            this.currentIndex = -1
            this.checkPopupDisplayWithWtsmNameSelected()
            this.onGetOptionFromWtsmName()
            this.onValidateDateTime()
        },
        async onShowPopupCreateFormSettlement() {
            this.showPopupCreateFormSettlement = true
            if (this.formCode) {
                let params = {
                    form_code: this.formCode
                }
                let result = await this.getEpsMFormRelation(params)
                if (result) {
                    result.url_name = utils.getUrlByFormType(result)
                    this.mFormRelationDataPopup = result
                }
            }

        },
        onCloseDialogConfirmDelete() {
            this.confirmDelete = false
        },
        handleCheckAppItem(tr) {
            if (this.createForm) {
                if(this.indexItemSelect === tr){
                    this.indexItemSelect = null;
                }else {
                    this.indexItemSelect = tr
                }
            }
            if ( this.viewForm){
                this.onShowDetailTAppItem(tr)
                this.onChangeTitle()
            } else {
                this.onChangeTitle()
            }
        }
        ,
        onChangeTitle() {
            if(this.createForm && !this.viewForm || this.isAdvanceForm ) {
                this.titlePopup = "経費事前申請書明細入力"
            }
            if(this.isSettlementForm || !this.isAdvanceForm  ){
                this.titlePopup = "経費申請書明細入力"
            }
            if(this.viewForm && !this.createForm ){
                if(this.isSettlementForm ){
                    this.titlePopup = "経費精算書明細"
                }
                else {
                    this.titlePopup = "経費申請書明細"
                }
            }
            // if(this.viewForm && !this.createForm && this.isSettlementForm ){
            //
            // }

        },
        async onShowDetailTAppItem(index) {
            if (this.dataTable.length > index) {
                this.currentIndex = index
                let result = this.dataTable[index]
                if (result) {
                    if (result.id) {
                        this.tAppItemsId = result.id
                    }
                    if (result.hasOwnProperty('wtsm_name')) {
                        this.wtsmNameSelected = result.wtsm_name
                    }
                    if (result.hasOwnProperty('expected_pay_date')) {
                        this.formDetail.expected_pay_date = result.expected_pay_date
                    }
                    if (result.hasOwnProperty('unit_price')) {
                        this.formDetail.unit_price = result.unit_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                    if (result.hasOwnProperty('expected_pay_amt')) {
                        this.formDetail.expected_pay_amt = result.expected_pay_amt.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                    if (result.hasOwnProperty('numof_ppl')) {
                        this.formDetail.numof_ppl = result.numof_ppl
                    }
                    if (result.hasOwnProperty('remarks')) {
                        this.formDetail.remarks = result.remarks
                    }
                    if (result.hasOwnProperty('quantity')) {
                        this.formDetail.quantity = result.quantity
                    }
                    if (result.hasOwnProperty('submit_method')) {
                        this.formDetail.submit_method = result.submit_method
                    }
                    if (result.hasOwnProperty('traffic_facility_name')) {
                        this.formDetail.traffic_facility_name = result.traffic_facility_name
                    }
                    if (result.hasOwnProperty('to_station')) {
                        this.formDetail.to_station = result.to_station
                    }
                    if (result.hasOwnProperty('from_station')) {
                        this.formDetail.from_station = result.from_station
                    }
                    if (result.hasOwnProperty('roundtrip_flag')) {
                        this.formDetail.roundtrip_flag = result.roundtrip_flag
                    }
                    if (result.hasOwnProperty('nonsubmit_type')) {
                        this.formDetail.nonsubmit_type = result.nonsubmit_type
                    }
                    if (result.hasOwnProperty('submit_other_memo')) {
                        this.formDetail.submit_other_memo = result.submit_other_memo
                    }
                    if (result.hasOwnProperty('nonsubmit_reason')) {
                        this.formDetail.nonsubmit_reason = result.nonsubmit_reason
                    }
                    if (result.hasOwnProperty('tax')) {
                        this.formDetail.tax = result.tax
                    }
                    this.formDetail.files = result.files || []
                    this.itemUploadFiles = result.newFiles || []
                }
            }
            this.onChangeTitle()
            this.checkPopupDisplayWithWtsmNameSelected()
            this.onGetOptionFromWtsmName()

            this.onValidateDateTime()

        },
        onEdit() {
            if (this.id && this.circularId) {
                this.dispatchAlertError('すでに回覧文書が作成されているため編集ができません。\n' +
                    '下書き一覧から文書ID ' + this.circularId + 'の文書を削除してください。')
            }
            else if (this.id) {
                this.$store.commit('expenseSettlement/updateEditForm', true)
                this.$store.commit('expenseSettlement/updateViewForm', false)
                this.$store.commit('expenseSettlement/updateBackupData', true)
                this.isDisableAddItem = this.checkNumberItemOver()
                let dataEdit = {
                    t_app: {
                        id: this.id,
                        circularId: this.circularId,
                        isDisableAddItem: this.isDisableAddItem,
                        target_period_from: this.target_period_from,
                        target_period_to: this.target_period_to,
                        form_dtl: this.form_dtl,
                        desired_suspay_amt: this.desired_suspay_amt,
                        eps_amt: this.eps_amt,
                        epsDiff: this.epsDiff,
                        expected_amt: this.expected_amt,
                        suspay_amt: this.suspay_amt,
                        eps_diff: this.desired_suspay_amt - this.eps_amt,
                    },

                    t_app_items: this.dataTable.map(item => {
                        const {files, newFiles, ...resultData} = item
                        return resultData
                    })
                }
                this.$store.commit('expenseSettlement/updateTAppDuplicateData', dataEdit)
                let params = {
                    form_type: this.formType
                }
                let url = utils.getUrlByFormType(params)
                this.$router.push(`/calculation-expense/${url}`);
            }

        },
        async checkPopupDisplayWithWtsmNameSelected() {
            this.showDialog = true
            if (this.wtsmNameSelected) {
                if (this.listElementForTransportForm &&
                    this.listElementForTransportForm.indexOf(this.wtsmNameSelected) >= 0) {
                    this.showTransportationForm = true
                } else {
                    await this.$validator.reset({scope: 'item_form'})
                    this.showNormalForm = true
                }
            }
        },

        async onCloseDialog() {
            this.currentIndex = null
            this.showDialog = false
            this.showNormalForm = false
            this.showTransportationForm = false
            this.itemUploadFiles = []
            await this.$validator.reset({scope: 'item_form'})
            this.errorsHandle.requireFileItem = false
            this.formDetail = {
                expected_pay_date: "",
                to_station: "",
                from_station: "",
                traffic_facility_name: "",
                roundtrip_flag: EXPENSE.EPS_T_APP_ITEMS_ROUNDTRIP_FLAG_DEFAULT,
                unit_price: 0,
                expected_pay_amt: 0,
                numof_ppl: 0,
                remarks: "",
                tax: 0,
                quantity: 0,
                submit_method: EXPENSE.EPS_T_APP_ITEMS_SUBMIT_METHOD_DEFAULT,
                submit_other_memo: "",
                nonsubmit_type: EXPENSE.EPS_T_APP_ITEMS_NONSUBMIT_TYPE_DEFAULT,
                nonsubmit_reason: "",
                files: [],
            }

            if (this.tAppItemsId) {
                this.tAppItemsId = null;
            }

        },
        async moveToScreenSettlement(formInfo) {
            await setTimeout(
                this.showPopupCreateFormSettlement = false, 500);
            if (this.id) {
                let formType = EXPENSE.M_FORM_FORM_TYPE_SETTLEMENT
                let tAppStatus = null
                let formName = null
                let formCode = null
                if (formInfo.hasOwnProperty('form_code')) {
                    formCode = formInfo.form_code
                }
                if (formInfo.hasOwnProperty('form_name')) {
                    formName = formInfo.form_name
                }
                let purposeName = this.purposeName

                this.$store.commit('expenseSettlement/updateFormType', formType)
                this.$store.commit('expenseSettlement/updateCreateForm', false)
                this.$store.commit('expenseSettlement/updateTAppStatus', tAppStatus)
                this.$store.commit('expenseSettlement/updateFormName', formName)
                this.$store.commit('expenseSettlement/updateFormCode', formCode)
                this.$store.commit('expenseSettlement/updatePurposeName', purposeName)
                this.$store.commit('expenseSettlement/updateEditForm', false)
                this.$store.commit('expenseSettlement/updateViewForm', false)
                this.$store.commit('expenseSettlement/updateBackupData', true)
                this.$store.commit('expenseSettlement/updateCreateFormnAdvanceComplete', true)

                let dataDuplicate = {
                    t_app: {
                        target_period_from: this.target_period_from,
                        target_period_to: this.target_period_to,
                        form_dtl: this.form_dtl,
                        desired_suspay_amt: this.desired_suspay_amt,
                        eps_amt: this.eps_amt,
                        epsDiff: this.epsDiff,
                        expected_amt: this.expected_amt,
                        suspay_amt: this.suspay_amt,
                    },
                    t_app_items: this.dataTable.map(item => {
                        const {id, t_app_id, ...resultData} = item
                        return resultData
                    })
                }
                this.$store.commit('expenseSettlement/updateTAppDuplicateData', dataDuplicate)
                let params = {
                    form_type: EXPENSE.M_FORM_FORM_TYPE_SETTLEMENT
                }
                let url = utils.getUrlByFormType(params)
                this.$router.push(`/calculation-expense/${url}`);
            }

        },
        onSave: async function () {
            const res = await this.$validator.validateAll('form')
            if (this.dataTable.length <= 0) {
                this.dispatchAlertError('データがありません。')
                return
            }
            if (!res || !this.dataTable || !this.dataTable.length) return
            if (this.target_period_from) {
                const target_period_from = this.$moment(this.target_period_from, 'YYYY/MM/DD')
                if (target_period_from && target_period_from.isValid())
                    this.target_period_from = target_period_from.format('YYYY-MM-DD')
            }

            if (this.target_period_to) {
                const target_period_to = this.$moment(this.target_period_to, 'YYYY/MM/DD')
                if (target_period_to && target_period_to.isValid())
                    this.target_period_to = target_period_to.format('YYYY-MM-DD')
            }

            if ((this.desired_suspay_amt && (typeof this.desired_suspay_amt === 'string')) &&
                (this.desired_suspay_amt.toString().indexOf(',') > -1)) {
                this.desired_suspay_amt = parseInt(this.desired_suspay_amt.replace(/,/g, ''))
            }

            if (!this.desired_suspay_amt) {
                this.desired_suspay_amt = 0
            }

            let data = {
                form_code: this.formCode,
                purpose_name: this.purposeName,
                target_period_from: this.target_period_from,
                form_dtl: this.form_dtl,
                expected_amt: this.expected_amt,
                desired_suspay_amt: this.desired_suspay_amt,
                eps_amt: this.eps_amt,
                suspay_amt: this.suspay_amt,
                items_delete: this.itemsDelete,
            }
            if (this.target_period_to) {
                data.target_period_to = this.target_period_to
            } else {
                data.target_period_to = ''
            }
            let dataItems = null
            let checkFileRequire = true
            if (this.dataTable.length) {
                this.dataTable.forEach(item => {
                    if (!item.hasOwnProperty('voucher_option')) {
                        let wtsmItem = this.findWtsmItemByName(item.wtsm_name)
                        if (wtsmItem) {
                            item.voucher_option = wtsmItem.voucher_option
                        }

                    }
                    if (item.submit_method == EXPENSE.EPS_T_APP_ITEMS_SUBMIT_METHOD_DEFAULT &&
                        item.voucher_option == EXPENSE.M_WTSM_VOUCHER_OPTION_REQUIRE &&
                        this.isSettlementForm) {
                        let totalFile = 0
                        if (item.files) {
                            totalFile += item.files.length
                        }
                        if (item.newFiles) {
                            totalFile += item.newFiles.length
                        }

                        if (totalFile <= 0 ) {
                            checkFileRequire = false
                        }

                    }
                })
                if (!checkFileRequire) {
                    this.dispatchAlertError('ファイル添付を選択してください。')
                    return
                }

                dataItems = this.dataTable.map(item => {
                    const {voucher_option, ...resultData} = item
                    return resultData
                })
            }

            const formData = new FormData()
            for (const dataKey in data) {
                formData.append(`t_app[${dataKey}]`, data[dataKey]);
            }
            let index = 0;
            let result
            for (const item of dataItems) {
                for (let itemKey in item) {
                    if (itemKey === 'newFiles' && item[itemKey]) {
                        for (const file of item[itemKey]) {
                            formData.append(`t_app_items[${index}][files][]`, file)
                        }
                        continue
                    }
                    if (itemKey === 'files') {
                        continue
                    }
                    formData.append(`t_app_items[${index}][${itemKey}]`, item[itemKey]);
                }
                index++
            }
            for (const file of this.uploadFiles) {
                formData.append('files[]', file)
            }
            formData.append(`form_type`, this.formType);
            if (this.id) {
                formData.append(`id`, this.id);
                result = await this.updateExpense(formData)
            } else {
                result = await this.saveExpense(formData);
                if (result && result.id) {
                    this.id = result.id
                }
            }
            if (result) {
                await this.createFileCircular()
            }
            this.uploadFiles = []
            this.onMoveToExpenseCircularScreen(false)

        },

        onGetEpsMWtsmName: async function () {
            let queries = {
                form_code: this.formCode,
            };
            const data = await this.getEpsMWtsmName(queries);
            if (data) {
                if (data.hasOwnProperty('items_max')) {
                    this.itemMax = data.items_max
                }
                if (data.hasOwnProperty('wtsm_name_data')) {
                    this.listWtsmNameSelect = data.wtsm_name_data
                }
                if (data.hasOwnProperty('validity_period')) {
                    const scopeDate = data.validity_period;
                    this.completedConfigDate.maxDate = scopeDate.validity_period_to;
                    this.completedConfigDate.minDate = scopeDate.validity_period_from;
                    this.completedConfigDatePayDate.maxDate = scopeDate.validity_period_to;
                    this.completedConfigDatePayDate.minDate = scopeDate.validity_period_from;
                    this.dateTo = scopeDate.validity_period_to
                    this.dateFrom = scopeDate.validity_period_from
                }

            }
        },
        findWtsmItemByName(wtsmName) {
            let result = null
            if (this.listWtsmNameSelect) {
                this.listWtsmNameSelect.forEach(element => {
                    if (element.wtsm_name == wtsmName) {
                        result = element
                    }
                })
            }
            return result
        },
        async onGetEpsMFormRelation() {
            if (this.formCode) {
                let params = {
                    form_code: this.formCode
                }
                let result = await this.getEpsMFormRelation(params)
                if (result) {
                    result.url_name = utils.getUrlByFormType(result)
                    this.mFormRelationDataPopup = result
                }
            }
        },

        async onSaveEpsTAppItems() {
            const res = await this.$validator.validateAll('item_form')
            let handleValidate = true
            let quantityOfSubmitMethodError = 0;
            if (this.formDetail.submit_method == EXPENSE.EPS_T_APP_ITEMS_SUBMIT_METHOD_DEFAULT
                && this.isSettlementForm && this.voucherOption == EXPENSE.M_WTSM_VOUCHER_OPTION_REQUIRE) {
                if (this.formDetail.files && this.itemUploadFiles &&
                    (this.itemUploadFiles.length + this.formDetail.files) <= 0) {
                    quantityOfSubmitMethodError++;
                }
            }
            if (quantityOfSubmitMethodError) {
                handleValidate = false;
                this.errorsHandle.requireFileItem = true
            } else {
                this.errorsHandle.requireFileItem = false
            }
            if (!res) return
            if (!handleValidate) return
            if (this.formDetail.expected_pay_date) {
                const expected_pay_date = this.$moment(this.formDetail.expected_pay_date, 'YYYY/MM/DD')
                if (expected_pay_date && expected_pay_date.isValid())
                    this.formDetail.expected_pay_date = expected_pay_date.format('YYYY-MM-DD')
            }

            if (this.formDetail.unit_price) {
                this.formDetail.unit_price = parseInt(this.formDetail.unit_price.replace(/,/g, ''))
            }
            if (this.formDetail.expected_pay_amt) {
                this.formDetail.expected_pay_amt = parseInt(this.formDetail.expected_pay_amt.replace(/,/g, ''))
            }

            if(!this.formDetail.nonsubmit_type){
                this.formDetail.nonsubmit_type = EXPENSE.EPS_T_APP_ITEMS_NONSUBMIT_TYPE_DEFAULT
            }
            if(!this.formDetail.tax){
                this.formDetail.tax = 0
            }
            if(!this.formDetail.unit_price){
                this.formDetail.unit_price = 0
            }
            if(!this.formDetail.quantity){
                this.formDetail.quantity = 0
            }
            if(!this.formDetail.expected_pay_amt){
                this.formDetail.expected_pay_amt = 0
            }
            if(!this.formDetail.numof_ppl){
                this.formDetail.numof_ppl = 0
            }
            let data = {
                wtsm_name: this.wtsmNameSelected,
                expected_pay_date: this.formDetail.expected_pay_date,
                unit_price: this.formDetail.unit_price,
                traffic_facility_name: this.formDetail.traffic_facility_name,
                to_station: this.formDetail.to_station,
                from_station: this.formDetail.from_station,
                roundtrip_flag: this.formDetail.roundtrip_flag,
                expected_pay_amt: this.formDetail.expected_pay_amt,
                numof_ppl: this.formDetail.numof_ppl,
                remarks: this.formDetail.remarks,
                quantity: this.formDetail.quantity,
                submit_method: this.formDetail.submit_method,
                submit_other_memo: this.formDetail.submit_other_memo,
                nonsubmit_type: this.formDetail.nonsubmit_type,
                nonsubmit_reason: this.formDetail.nonsubmit_reason,
                tax: this.formDetail.tax,
                newFiles: [],
                files: this.formDetail.files,
                voucher_option: this.voucherOption,
            };
            if (!this.showNormalForm) {
                if (this.formDetail.expected_pay_date <= 0) {
                    this.dispatchAlertError('日付は必須項目です。')
                } else if ((this.detailOption == 2) && !this.formDetail.remarks) {
                    this.dispatchAlertError('詳細は必須項目です。')
                } else {
                    if (this.tAppItemsId) {
                        data.id = this.tAppItemsId
                    }
                    if (this.itemUploadFiles && this.itemUploadFiles.length) {
                        data.newFiles = this.itemUploadFiles
                    }
                    if (this.currentIndex >= 0) {
                        if (this.currentIndex < this.dataTable.length) {
                            this.dataTable[this.currentIndex] = data
                            this.onGetSumClient()
                        }
                    } else {
                        this.dataTable.push(data)
                        this.onGetSumClient()
                        this.currentIndex = this.dataTable.length - 1
                    }
                    this.onCloseDialog()
                    if (this.dataTable.length >= this.itemMax) {
                        this.dispatchAlertSuccess('最大明細数は十分です。')
                        this.isDisableAddItem = true;
                    }
                }
            } else {
                if (this.tAppItemsId) {
                    data.id = this.tAppItemsId
                }
                if (this.formDetail.submit_method == EXPENSE.EPS_T_APP_ITEMS_SUBMIT_METHOD_DEFAULT &&
                    this.itemUploadFiles && this.itemUploadFiles.length) {
                    data.newFiles = this.itemUploadFiles
                }
                if (this.currentIndex >= 0) {
                    if (this.currentIndex < this.dataTable.length) {
                        this.dataTable[this.currentIndex] = data
                        this.onGetSumClient()
                    }
                } else {
                    this.dataTable.push(data)
                    this.onGetSumClient()
                    this.currentIndex = this.dataTable.length - 1
                }

                this.onCloseDialog()

                // await this.onGetEpsMWtsmName()
                if (this.dataTable.length >= this.itemMax) {
                    this.dispatchAlertSuccess('最大明細数は十分です。')
                    this.isDisableAddItem = true;
                }
            }
        },

        async onCloneEpsTAppItems(index) {
            if (this.dataTable.length >= this.itemMax) {
                this.dispatchAlertError('最大明細数を超えている。')
            } else {
                if (this.dataTable.length > index) {
                    this.currentIndex = index
                    let result = this.dataTable[index]
                    if (!result.hasOwnProperty('roundtrip_flag')) {
                        result.roundtrip_flag = EXPENSE.EPS_T_APP_ITEMS_ROUNDTRIP_FLAG_DEFAULT
                    }
                    if (!result.hasOwnProperty('unit_price')) {
                        result.unit_price = 0
                    }
                    if (!result.hasOwnProperty('expected_pay_amt')) {
                        result.expected_pay_amt = 0
                    }
                    if (!result.hasOwnProperty('quantity')) {
                        result.quantity = 0
                    }
                    if (!result.hasOwnProperty('numof_ppl')) {
                        result.numof_ppl = 0
                    }
                    if (!result.hasOwnProperty('submit_method')) {
                        result.submit_method = EXPENSE.EPS_T_APP_ITEMS_SUBMIT_METHOD_DEFAULT
                    }
                    if (!result.hasOwnProperty('tax')) {
                        result.tax = 0
                    }
                    let newResult
                    newResult = structuredClone(result)
                    delete newResult.id
                    this.dataTable.push(newResult)
                    this.onGetSumClient()
                    this.currentIndex = this.dataTable.length - 1

                    if (this.dataTable.length >= this.itemMax) {
                        this.dispatchAlertSuccess('最大明細数は十分です。')
                        this.isDisableAddItem = true;
                    }

                }
                if (this.tAppItemsId) {
                    this.tAppItemsId = null;
                }
            }
        },

        async onMoveSentCircular() {
            if (this.circularId) {
                let dataRequest = {
                    circular_id: this.circularId,
                }
                let result = await this.getCircularSentById(dataRequest)
                if (result.data && result.data.length > 0) {
                    let tr = result.data[0]
                    localStorage.setItem("tr", JSON.stringify(tr));
                    await this.$router.push(`/sent/${this.circularId}`);
                }
                this.$store.commit('home/setViewDetailExpense', true)
            }

        },
        async onMoveToHomeCircularScreen() {
            if (this.circularId) {
                if (this.id) {
                    this.$store.commit('expense/get_t_app_id', this.id)
                }
                await this.$router.push(`/expense/preview/${this.circularId}`);
            }
        },
        async onMoveToExpenseCircularScreen(isViewDetailExpense = false) {
            if (!this.circularId) {
                await this.createFileCircular()
            }
            if (this.circularId) {
                setTimeout(() => {
                    this.$router.push('/expenses/' + this.circularId)
                }, 300)
                this.$store.commit('home/setViewDetailExpense', isViewDetailExpense)
            }
        },
        async createFileCircular() {
            // create new file circular
            let resultUpdateExpenseFormInput = await this.onUpdateExpenseFormInput()
            if (resultUpdateExpenseFormInput && resultUpdateExpenseFormInput.circular
                && resultUpdateExpenseFormInput.circular.id) {
                this.circularId = resultUpdateExpenseFormInput.circular.id
            }
            let paramsUpdateCircularInfo = {
                circular_id: this.circularId,
                t_app_id: this.id
            }
            await this.updateExpenseCircularInfo(paramsUpdateCircularInfo)
        },
        async onMoveToCompleteScreen() {
            if (this.circularId) {
                this.$store.commit('home/updateIsGoBackExpense', true)
                await this.$router.push(`/completed/${this.circularId}`);
            }

        },
        async onUpdateExpenseFormInput() {
            let params = {
                update_expense: {
                    form_code: this.formCode,
                    t_app_id: this.id,
                },
            }
            let circularInfo = await this.updateExpenseFormInput(params)
            if (circularInfo && circularInfo.circular && circularInfo.circular.id) {
                this.circularId = circularInfo.circular.id
            } else {
                this.circularId = null
            }
        },

        async onCloneEpsTAppAndItems() {
            // if (this.id) {
            //     let formType = this.formType
            //     let tAppStatus = null
            //     let formName = this.formName
            //     let formCode = this.formCode
            //     let purposeName = this.purposeName
            //
            //     this.$store.commit('expenseSettlement/updateFormType', formType)
            //     this.$store.commit('expenseSettlement/updateCreateForm', true)
            //     this.$store.commit('expenseSettlement/updateTAppStatus', tAppStatus)
            //     this.$store.commit('expenseSettlement/updateFormName', formName)
            //     this.$store.commit('expenseSettlement/updateFormCode', formCode)
            //     this.$store.commit('expenseSettlement/updatePurposeName', purposeName)
            //     this.$store.commit('expenseSettlement/updateEditForm', false)
            //     this.$store.commit('expenseSettlement/updateViewForm', false)
            //     this.$store.commit('expenseSettlement/updateBackupData', true)
            //     this.isDisableAddItem = this.checkNumberItemOver()
            //     let dataDuplicate = {
            //         t_app: {
            //             target_period_from: this.target_period_from,
            //             target_period_to: this.target_period_to,
            //             form_dtl: this.form_dtl,
            //             desired_suspay_amt: this.desired_suspay_amt,
            //             eps_amt: this.eps_amt,
            //             epsDiff: this.epsDiff,
            //             expected_amt: this.expected_amt,
            //             suspay_amt: this.suspay_amt,
            //             isDisableAddItem: this.isDisableAddItem,
            //             eps_diff: this.desired_suspay_amt - this.eps_amt,
            //         },
            //         t_app_items: this.dataTable.map(item => {
            //             const {id, t_app_id, files, newFiles, ...resultData} = item
            //             return resultData
            //         })
            //     }
            //     this.$store.commit('expenseSettlement/updateTAppDuplicateData', dataDuplicate)
            //
            //     let params = {
            //         form_type: this.formType
            //     }
            //     let url = utils.getUrlByFormType(params)
            //     this.$router.push(`/calculation-expense/${url}`);
            // }
        },

        onDeleteEpsTAppItems() {
            this.confirmDelete = true
        },
        async deleteEpsTAppData() {
            setTimeout(
                this.confirmDelete = false, 500);
            if (this.id) {
                let dataPayloadTAppAndItems = {
                    id: this.id,
                    t_app_id: this.id,
                }
                await this.deleteEpsTAppAndItems(dataPayloadTAppAndItems)
                await this.$router.push('/calculation-expense')
            }
        },
        checkDisableForm() {
            if (this.viewForm) {
                this.isDisableAddItem = true
            }
            if (this.checkNumberItemOver()) {
                this.isDisableAddItem = true
            }

        },
        checkNumberItemOver() {
            let result = false
            if (this.itemMax && this.dataTable.length) {
                if (this.dataTable.length >= this.itemMax) {
                    result = true;
                }
            }
            return result
        },

        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active ? "DESC" : "ASC";
        },

        async onGetInfoEpsTAppItem() {
            let queries = {}
            if (this.id) {
                queries.id = this.id
            }

            const result = await this.getListTAppItems(queries)
            if (result) {
                if (result.data) {
                    this.dataTable = result.data.map(item => {
                        if (!item.remarks) {
                            item.remarks = ''
                        }
                        if (!item.nonsubmit_reason) {
                            item.nonsubmit_reason = ''
                        }
                        if (!item.submit_other_memo) {
                            item.submit_other_memo = ''
                        }
                        if (!item.traffic_facility_name) {
                            item.traffic_facility_name = ''
                        }
                        if (!item.from_station) {
                            item.from_station = ''
                        }
                        if (!item.to_station) {
                            item.to_station = ''
                        }
                        item.newFiles = []
                        return item
                    });
                }
                if (result.info) {
                    const info = result.info
                    if (info.hasOwnProperty('form_dtl') && info.form_dtl) {
                        this.form_dtl = info.form_dtl
                    }
                    if (info.hasOwnProperty('expected_amt')) {
                        this.expected_amt = info.expected_amt
                    }
                    if (info.hasOwnProperty('suspay_amt')) {
                        this.suspay_amt = info.suspay_amt
                    }
                    if (info.hasOwnProperty('desired_suspay_amt')) {
                        this.desired_suspay_amt = info.desired_suspay_amt.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                    }
                    if (info.hasOwnProperty('eps_amt')) {
                        this.eps_amt = info.eps_amt
                    }
                    if (info.hasOwnProperty('target_period_from')) {
                        this.target_period_from = info.target_period_from
                    }
                    if (info.hasOwnProperty('target_period_to')) {
                        this.target_period_to = info.target_period_to
                    }
                    if (info.hasOwnProperty('eps_diff')) {
                        this.epsDiff = info.eps_diff
                    }
                    if (info.hasOwnProperty('circular_id') && info.circular_id) {
                        this.circularId = info.circular_id
                    }
                    this.infoFiles = info.files || []
                }
            }

        },
        onFileChange(e) {
            if (!this.uploadFiles) return
            // check total file size
            let totalSize = 0;
            // file upload and file saved
            if (this.$refs.fileUpload.files.length > 0) {
                for (let i = 0; i < this.$refs.fileUpload.files.length; i++) {
                    totalSize += this.$refs.fileUpload.files[i].size
                }
            }
            // if (e.target.files) {
            //     for (let i = 0; i < e.target.files.length; i++) {
            //         totalSize += e.target.files[i].size
            //     }
            // }
            for (let i = 0; i < this.uploadFiles.length; i++) {
                totalSize += this.uploadFiles[i].size
            }
            if (totalSize > this.totalMaxFilesSize) {
                this.dispatchAlertError('容量は3MB以下で添付してください。')
                return
            }
            this.uploadFiles.push(...Array.from(this.$refs.fileUpload.files))
            // this.uploadFiles.push(...Array.from(e.target.files))

            this.$refs.fileUpload.value = ''
            this.dispatchAlertSuccess('アップロード処理に成功しました。')
        },
        async onItemFileChange(e) {
            if (!this.itemUploadFiles) return
            let totalSize = 0;
            // file upload and file saved
            if (this.$refs.itemFileUpload.files.length > 0) {
                for (let i = 0; i < this.$refs.itemFileUpload.files.length; i++) {
                    totalSize += this.$refs.itemFileUpload.files[i].size
                }
            }
            // if (e.target.files) {
            //     for (let i = 0; i < e.target.files.length; i++) {
            //         totalSize += e.target.files[i].size
            //     }
            // }
            for (let i = 0; i < this.itemUploadFiles.length; i++) {
                totalSize += this.itemUploadFiles[i].size
            }
            if (totalSize > this.totalMaxFilesSize) {
                this.dispatchAlertError('容量は3MB以下で添付してください。')
                return
            }
            this.itemUploadFiles.push(...Array.from(this.$refs.itemFileUpload.files))
            // this.itemUploadFiles.push(...Array.from(e.target.files))
            if(this.itemUploadFiles.length === 0) this.$refs.itemFileUpload.value = ''
            if (this.errorsHandle.requireFileItem) {
                this.errorsHandle.requireFileItem = false
            }
            this.dispatchAlertSuccess('アップロード処理に成功しました。')

        },

        onDeleteUploadFile(index) {
            if (!this.uploadFiles) return
            this.uploadFiles.splice(index, 1)
        },
        onDeleteFileUploadFile(index) {
            if (!this.uploadFiles) return
            this.uploadFiles.splice(index, 1)
        },
        async onDeleteItemUploadFile(index) {
            if (!this.itemUploadFiles) return
            this.itemUploadFiles.splice(index, 1)
        },
        onDownloadFile(file) {
            if (!file) return
            this.downloadFile(file.id)
        },
        async onDeleteFile(file, index) {
            if (!file) return
            await this.$store.dispatch('updateLoading', true);
            const res = await this.deleteFile(file.id)
            await this.$store.dispatch('updateLoading', false);
            if (!res) return
            await this.infoFiles.splice(index, 1)
        },
        async onDeleteItemFile(file, index) {
            if (!file) return
            await this.$store.dispatch('updateLoading', true);
            const res = await this.deleteFile(file.id)
            await this.$store.dispatch('updateLoading', false);
            if (!res) return
            await this.formDetail.files.splice(index, 1)
        },

        formatPrice(value) {
            let val = (value / 1).toFixed(0).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        },

        async dispatchAlertError(message) {
            await this.$store.dispatch("alertError", message, {root: true})
        },
        async dispatchAlertSuccess(message) {
            await this.$store.dispatch("alertSuccess", message, {root: true})
        },
        isNumber: function (evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                evt.preventDefault();
            } else {
                return true;
            }
        },
        async onGetMFormPurposeDataSelect() {
            let params = {
                form_code: this.formCode
            }
            this.listMFormPurposeSelect = await this.getMFormPurposeDataSelect(params);
        },
        async onGetCurrentUserDepartmentInfo() {
            let departmentResult = await this.getCurrentUserDepartmentInfo()
            if (departmentResult) {
                this.departmentInfo = departmentResult
            }
        },

        async onGetSumClient (){
            let newData = [];
            this.dataTable.forEach((value, index) => {
                newData.push(Number(value.expected_pay_amt))
                let sum = 0
                for (let i = 0; i < newData.length; i++) {
                    sum += newData[i];
                }
                if (!this.isSettlementForm) {
                    this.expected_amt = sum
                } else {
                    this.eps_amt = sum
                    this.epsDiff = this.suspay_amt - this.eps_amt
                }
            });
        },

        async onGetOptionFromWtsmName() {
            if (this.listWtsmNameSelect && this.wtsmNameSelected) {
                let valObj
                for (let i = 0; i < this.listWtsmNameSelect.length; i++) {
                    if (this.listWtsmNameSelect[i].wtsm_name == this.wtsmNameSelected) {
                        valObj = this.listWtsmNameSelect[i]
                    }
                }
                if (valObj) {
                    this.numPeopleOption = valObj.num_people_option
                    this.detailOption = valObj.detail_option
                    this.voucherOption = valObj.voucher_option
                    this.taxOption = valObj.tax_option
                    this.formDetail.num_people_describe = valObj.num_people_describe
                    this.formDetail.detail_describe = valObj.detail_describe
                }
            }
        },

        changeSetIndexValue(item, value, nameOfInput){
            if((typeof item) === 'object') {
                if (nameOfInput === 'numof_ppl') {
                    item.numof_ppl = parseInt(item.numof_ppl.toString().replace(/[^0-9]/g, ''));
                    item.numof_ppl = parseInt(item.numof_ppl.toString().replace(/^0+(?=\d)/, ''))
                }
                if (nameOfInput === 'tax') {
                    item.tax = parseInt(item.tax.toString().replace(/[^0-9]/g, ''));
                    item.tax = parseInt(item.tax.toString().replace(/^0+(?=\d)/, ''))
                }
                if (nameOfInput === 'quantity') {
                    item.quantity = parseInt(item.quantity.toString().replace(/[^0-9]/g, ''));
                    item.quantity = parseInt(item.quantity.toString().replace(/^0+(?=\d)/, ''))
                }
                if (nameOfInput === 'unit_price') {
                    if (typeof item.unit_price === 'string' && /[^a-zA-Z]/.test(item.unit_price)) {
                        item.unit_price = item.unit_price.replace(/[^0-9]/g, '');
                    }
                    if (typeof item.unit_price === 'string' && item.unit_price.indexOf(0) > -1) {
                        item.unit_price = item.unit_price.replace(/^0+(?=\d)/, '')
                    }
                    item.unit_price = item.unit_price.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
                }
                if (nameOfInput === 'expected_pay_amt') {
                    if (typeof item.expected_pay_amt === 'string' && /[^a-zA-Z]/.test(item.expected_pay_amt)) {
                        item.expected_pay_amt = item.expected_pay_amt.replace(/[^0-9]/g, '');
                    }
                    if (typeof item.expected_pay_amt === 'string' && item.expected_pay_amt.indexOf(0) > -1) {
                        item.expected_pay_amt = item.expected_pay_amt.replace(/^0+(?=\d)/, '')
                    }
                    item.expected_pay_amt = item.expected_pay_amt.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
                }
            } else {
                if (nameOfInput === 'desired_suspay_amt'){
                    if (typeof this.desired_suspay_amt === 'string' && /[^a-zA-Z]/.test(this.desired_suspay_amt)) {
                        this.desired_suspay_amt = this.desired_suspay_amt.replace(/[^0-9]/g, '');
                    }
                    if (typeof this.desired_suspay_amt === 'string' && this.desired_suspay_amt.indexOf(0) > -1) {
                        this.desired_suspay_amt = this.desired_suspay_amt.replace(/^0+(?=\d)/, '')
                    }
                    // if (typeof this.desired_suspay_amt === 'string' && this.desired_suspay_amt.indexOf(',') > -1) {
                    //     this.desired_suspay_amt = this.desired_suspay_amt.replace(/,/gi, "")
                    // }
                    // if (typeof this.desired_suspay_amt === 'string' && this.desired_suspay_amt.indexOf('.') > -1) {
                    //     this.desired_suspay_amt = this.desired_suspay_amt.split('.').join("");
                    // }
                    this.desired_suspay_amt = this.desired_suspay_amt.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
                }
            }
        },

        dragover(event) {
            event.preventDefault();
            // Add some visual fluff to show the user can drop its files
            if (!event.currentTarget.classList.contains('bg-green-300')) {
                event.currentTarget.classList.remove('bg-gray-100');
                event.currentTarget.classList.add('bg-green-300');
            }
        },
        dragleave(event) {
            // Clean up
            event.currentTarget.classList.add('bg-gray-100');
            event.currentTarget.classList.remove('bg-green-300');
        },
        drop(event) {
            event.preventDefault();
            this.$refs.fileUpload.files = event.dataTransfer.files;
            this.onFileChange(event); // Trigger the onChange event manually
            // Clean up
            event.currentTarget.classList.add('bg-gray-100');
            event.currentTarget.classList.remove('bg-green-300');
        },
        dropItem(event) {
            event.preventDefault();
            this.$refs.itemFileUpload.files = event.dataTransfer.files;
            this.onItemFileChange(event); // Trigger the onChange event manually
            // Clean up
            event.currentTarget.classList.add('bg-gray-100');
            event.currentTarget.classList.remove('bg-green-300');
        },
    },
    mounted() {
        this.checkDisableForm()
        this.onGetSumClient()
    },


    created() {
        if (this.$route.params.id) {
            this.id = this.$route.params.id
        }
        this.onGetMFormPurposeDataSelect()

        if (this.formType === EXPENSE.M_FORM_FORM_TYPE_SETTLEMENT) {
            this.isSettlementForm = true
        } else if (this.formType === EXPENSE.M_FORM_FORM_TYPE_ADVANCE) {
            this.isAdvanceForm = true
        }

        if (EXPENSE.T_APP_STATUS_CIRCULATING.includes(this.tAppStatus)) {
            this.circularStatusDoing = true
        } else if (EXPENSE.T_APP_STATUS_BEFORE_CIRCULAR.includes(this.tAppStatus)) {
            this.circularStatusToDo = true
        } else if (EXPENSE.T_APP_STATUS_AFTER_CIRCULAR.includes(this.tAppStatus)) {
            this.circularStatusDone = true
        }

        if (this.isBackupData) {
            if (this.tAppDuplicateData) {
                if (this.tAppDuplicateData.t_app) {
                    const tApp = this.tAppDuplicateData.t_app
                    if (tApp.hasOwnProperty('desired_suspay_amt')) {
                        this.desired_suspay_amt = tApp.desired_suspay_amt
                    }
                    if (tApp.hasOwnProperty('eps_amt')) {
                        this.eps_amt = tApp.eps_amt
                    }
                    if (tApp.hasOwnProperty('epsDiff')) {
                        this.epsDiff = tApp.epsDiff
                    }
                    if (tApp.hasOwnProperty('expected_amt')) {
                        this.expected_amt = tApp.expected_amt
                    }
                    if (tApp.hasOwnProperty('suspay_amt')) {
                        this.suspay_amt = tApp.suspay_amt
                    }
                    if (tApp.hasOwnProperty('epsDiff')) {
                        this.epsDiff = tApp.epsDiff
                    }
                    if (tApp.hasOwnProperty('form_dtl')) {
                        this.form_dtl = tApp.form_dtl
                    }
                    if (tApp.hasOwnProperty('target_period_from')) {
                        this.target_period_from = tApp.target_period_from
                    }
                    if (tApp.hasOwnProperty('target_period_to')) {
                        this.target_period_to = tApp.target_period_to
                    }
                    if (tApp.hasOwnProperty('isDisableAddItem')) {
                        this.isDisableAddItem = tApp.isDisableAddItem
                    }
                    if (tApp.hasOwnProperty('id') && this.editForm) {
                        this.id = tApp.id
                        this.circularId = tApp.circularId
                    }
                }
                if (this.tAppDuplicateData.t_app_items) {
                    this.dataTable = this.tAppDuplicateData.t_app_items
                }
            }
        }
        this.onGetCurrentUserDepartmentInfo()
        this.onGetInfoEpsTAppItem()
        this.onGetEpsMWtsmName()
        if (this.circularStatusDone) {
            this.onGetEpsMFormRelation()
        }
        this.onGetEpsMWtsmName()
        this.onValidateDateTime()
    },

    watch: {},


}
</script>

<style lang="scss">
#expense-page {
    .ml-7 {
        margin-left: 2rem !important;
    }

    .td_text-rank {
        max-width: 500px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .height_popup_ex {
        min-height: 625px;
        margin-bottom: -14px !important;
    }

    .height_popup_nor {
        min-height: 500px;
        height: auto;
    }

    .vs-table--tbody-table .tr-values:last-child {
        cursor: default;
    }

    .btn-ex {
        width: 40px;
        font-size: 20px;
        line-height: 20px;
    }

    .flex-end-ex > .vs-table-text {
        justify-content: flex-end;
    }

    .flex-end-ex {
        display: flex;
        justify-content: flex-end;
    }

    .btn-icon_th {
        font-size: 30px;
        cursor: pointer;
    }

    .btn-icon_th, .btn-icon_tb, .un_active {
        color: #626262;
    }

    .selected.active.createform {
        .btn-icon_tb {
            background-color: rgba(var(--vs-primary), 1);
        }
    }
    .btn-icon_tb {
        font-size: 20px;
        border: none;
    }

    .btn-icon_th :hover {
        color: #000000;
        transition: color 0.25s;
    }

    .btn-icon_tb:not(.un_active):hover {
        color: #000000;
        transition: color 0.25s;
        cursor: pointer;
    }

    .row-table {
        border: 1.25px solid rgb(16, 127, 205);
        opacity: 0.6;
    }

    .upload_files::-webkit-scrollbar-track {
        padding: 20px 0;
        background-color: none;
    }

    .upload_files::-webkit-scrollbar {
        width: 1px;
    }

    .upload_files::-webkit-scrollbar-thumb {
        border-radius: 4px;
        box-shadow: inset 0 0 6px none;
        background-color: none;
    }

    .con-img-upload .img-upload .text-archive span {
        font-size: 15px;
    }

    .con-img-upload {
        display: flex;
        flex-direction: column-reverse;
        margin-left: 0.75rem;
    }

    .material-icons {
        font-size: 18px;
    }

    .from-input {
    }

    .label-from {
        font-size: 13px;

    }

    .popup-dialogs > .vs-popup {
        min-width: 850px;
    }

    .text-decoration-underline {
        text-decoration: underline;
    }

    .font-color-link-primary {
        color: rgba(var(--vs-primary), 1)
    }

    .vs-table--tbody-table .tr-values.selected.viewForm td {
        cursor: pointer !important;
    }

    .tr-values.vs-table--tr.tr-table-state-null.selected.createform td {
        cursor: default !important;
    }

    .vs-table--tbody-table .tr-values.selected {
        cursor: default !important;
    }

    .tr-values.vs-table--tr.active.tr-table-state-null.selected td {
        color: #fff0ff;
        background-color: rgba(var(--vs-primary), 1);
        cursor: default !important;
    }
    .vs-table--tbody-table .tr-values:last-child {
        cursor: pointer;
    }
    .flatpickr-wrapper {
        width: 100%;
    }
    .normal_form {
        margin-bottom: -50px;
    }

    .td-date {
        overflow: hidden;
        position: relative;

        .date-view {
            position: relative;
            background: transparent;
            border : none;
            margin-left: -26px;
            width: 100px;
            color: #626262;
        }
        .date-view.flatpickr-input{
            margin-left: -36px;

        }

        .input-date {
            position: absolute;
            width: 55px;
            height: 50%;
            background-color: #fff;
            left: -36px;
            z-index: 1;
        }
        .input-date.is_createform {
            background-color: rgba(var(--vs-primary), 1);
            left: -46px;
        }
        .input-date.is_edit {
            left: -36px;
        }

        .date-view.is_createform{
            margin-left: -35px;
            color: #fff;
            border: none;
            outline: none;
            background-color: rgba(var(--vs-primary), 1);
        }
    }
    .vs-con-table.stripe .tr-values:nth-child(2n) .input-date {
        background-color: #f8f8f8;
    }
    .disabled.flatpickr-input , .file-upload-box.disabled  {
        cursor: default;
    }

   .vs-con-table.stripe .tr-values:nth-child(2n) .input-date.is_createform {
        background-color: rgba(var(--vs-primary), 1);
    }
}


.upload_files {
    margin-left: 0%;
    overflow-y: auto;
    max-height: 145px;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    z-index: 1;

    .file-upload-box {
        padding: 5px;
        border-radius: 5px;
        text-align: center;
        border: 2px dashed rgb(180, 180, 180);
        color: rgb(180, 180, 180);
        cursor: pointer;
        span {
            color: #000;
            font-size: 1rem;
        }
    }

    .file-upload-box.file_upload_btn:hover {
        background-color: #f8f8f8;
    }

    .file-upload-box.file-upload-box_view {
        border: none;
    }

    input[type="file"] {
        width: 0;
        height: 0;
    }

    .file-upload-list {
        li {
            display: flex;
            align-items: center;
        }
    }
}

.input-mess {
    position: relative;
}
.label-err {
    position: absolute;
}
.text-error {
    top: 37px;
    width: 100%;
    text-align: end;
}
.flatpickr-calendar {
    position: absolute !important;
    .flatpickr-current-month {
        height: 35px;
        bottom: -10px;
    }
}



</style>
