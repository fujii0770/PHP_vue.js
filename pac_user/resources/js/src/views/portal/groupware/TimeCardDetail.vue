<template>
    <div id="bulletin_board">
        <top-menu></top-menu>

        <vs-row>
<!--            <div class="mr-2 leftpanel">-->
<!--                <menu-left></menu-left>-->
<!--            </div>-->
            <div class="bbs" style="width: 100%">
                <div id="bbs" class="containar">
                    <vs-card>
                        <vs-button @click="jumpToPrev" id="pre-page"
                                   class="square w-1/2 sm:w-auto md:w-auto lg:w-auto xl:w-auto"
                                   style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled"
                        ><i class="fas fa-long-arrow-alt-left"></i> 戻る
                        </vs-button>
                        <div style="padding: 0 140px 40px 140px; margin-top: -35px" id="data-table">
                            <div style="margin-top: 20px;position: relative">
                                <div style="display: inline-block">
                                    <label for="filter_todate" class="vs-input--label"></label>
                                    <div class="vs-con-input">
                                        <flat-pickr v-model="filter.todate" id="filter_todate" :config="configDate"
                                                    @on-change="changeMonth"></flat-pickr>
                                    </div>
                                </div>
                                <vs-button color="rgb(59,222,200)" gradient @click="openPop"
                                           style="margin-left: 35px;padding: 0.68rem 2rem;">CSV出力
                                </vs-button>
                            </div>

                            <vs-table class="mt-3 custome-event" :data="listData" noDataText="データがありません。"
                                      sst stripe>
                                <template slot="thead">
                                    <vs-th class="max-width-200">日付</vs-th>
                                    <vs-th>出勤1</vs-th>
                                    <vs-th>退勤1</vs-th>
                                    <vs-th>出勤2</vs-th>
                                    <vs-th>退勤2</vs-th>
                                    <vs-th>出勤3</vs-th>
                                    <vs-th>退勤3</vs-th>
                                    <vs-th>出勤4</vs-th>
                                    <vs-th>退勤4</vs-th>
                                    <vs-th>出勤5</vs-th>
                                    <vs-th>退勤5</vs-th>
                                </template>
                                <tr v-for="(tr, trindex) in listData" @click="showDetail(tr,trindex)" :key="trindex">
                                    <vs-td style="text-align: left">{{ trindex }}</vs-td>
                                    <td>{{ tr.punch_data.start1 }}</td>
                                    <td>{{ tr.punch_data.end1 }}</td>
                                    <td>{{ tr.punch_data.start2 }}</td>
                                    <td>{{ tr.punch_data.end2 }}</td>
                                    <td>{{ tr.punch_data.start3 }}</td>
                                    <td>{{ tr.punch_data.end3 }}</td>
                                    <td>{{ tr.punch_data.start4 }}</td>
                                    <td>{{ tr.punch_data.end4 }}</td>
                                    <td>{{ tr.punch_data.start5 }}</td>
                                    <td>{{ tr.punch_data.end5 }}</td>
                                </tr>
                            </vs-table>
                        </div>
                    </vs-card>
                </div>
            </div>
        </vs-row>

        <modal name="time-card-show-detail"
               :pivot-y="0.4"
               :width="600"
               :classes="['v--modal', 'time-card-show-detail']"
               :styles="['font-size:14px;']"
               :height="'auto'"
               :scrollable="true"
               :clickToClose="false">

            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">打刻編集</h4>
                    <button type="button" class="close" data-dismiss="modal"
                            @click="modalClosed">
                        ×
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="card">
                        <div class="card-header">{{ selectTrTitle }}</div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">出勤 1</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time" v-model="showRowData.punch_data.start1">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('start1')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">退勤 1</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time" v-model="showRowData.punch_data.end1">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('end1')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">出勤 2</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time" v-model="showRowData.punch_data.start2">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('start2')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">退勤 2</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.end2">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('end2')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">出勤 3</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.start3">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('start3')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">退勤 3</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.end3">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('end3')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">出勤 4</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.start4">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('start4')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">退勤 4</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.end4">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('end4')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">出勤 5</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.start5">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('start5')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-sm-2 col-12 control-label">退勤 5</label>
                                    <div class="col-md-10 col-sm-10 col-12">
                                        <div class="input-group mb-1">
                                            <input class="form-control" type="time"
                                                   v-model="showRowData.punch_data.end5">
                                            <button class="btn btn btn-danger del-btn" type="button"
                                                    @click="deleteTime('end5')">
                                                <svg class="svg-inline--fa fa-trash-alt fa-w-14" aria-hidden="true"
                                                     focusable="false" data-prefix="fas" data-icon="trash-alt"
                                                     role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                                     data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                                </svg><!-- <i class="fas fa-trash-alt"></i> --> 削除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success confirm-btn" @click="updatePunched">
                                <svg class="svg-inline--fa fa-save fa-w-14" aria-hidden="true" focusable="false"
                                     data-prefix="far" data-icon="save" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="">
                                    <path fill="currentColor"
                                          d="M433.941 129.941l-83.882-83.882A48 48 0 0 0 316.118 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V163.882a48 48 0 0 0-14.059-33.941zM272 80v80H144V80h128zm122 352H54a6 6 0 0 1-6-6V86a6 6 0 0 1 6-6h42v104c0 13.255 10.745 24 24 24h176c13.255 0 24-10.745 24-24V83.882l78.243 78.243a6 6 0 0 1 1.757 4.243V426a6 6 0 0 1-6 6zM224 232c-48.523 0-88 39.477-88 88s39.477 88 88 88 88-39.477 88-88-39.477-88-88-88zm0 128c-22.056 0-40-17.944-40-40s17.944-40 40-40 40 17.944 40 40-17.944 40-40 40z"></path>
                                </svg><!-- <i class="far fa-save"></i> --> 更新
                            </button>

                            <button type="button" class="btn btn-default cancel-btn" data-dismiss="modal"
                                    @click="modalClosed">
                                <svg class="svg-inline--fa fa-times-circle fa-w-16" aria-hidden="true" focusable="false"
                                     data-prefix="fas" data-icon="times-circle" role="img"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                    <path fill="currentColor"
                                          d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"></path>
                                </svg><!-- <i class="fas fa-times-circle"></i> --> 閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </modal>

        <vs-popup classContent="popup-example" title="CSV出力" :active.sync="confirmPullBack">
            <vs-row class="mt-3">
                <vs-col style="margin-bottom: 15px;">打刻履歴データを出力します。実行しますか？</vs-col>
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="confirmPullBack=false" color="dark" type="border">
                        <svg class="svg-inline--fa fa-times-circle fa-w-16" aria-hidden="true" focusable="false"
                             data-prefix="fas" data-icon="times-circle" role="img" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 512 512" data-fa-i2svg="">
                            <path fill="currentColor"
                                  d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"></path>
                        </svg>
                        キャンセル
                    </vs-button>
                    <vs-button @click="downloadCSV" color="primary" style="padding: 0.77rem 2.5rem">
                        <svg class="svg-inline--fa fa-check fa-w-16" aria-hidden="true" focusable="false"
                             data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 512 512" data-fa-i2svg="">
                            <path fill="currentColor"
                                  d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
                        </svg>
                        はい
                    </vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
</template>
<script>
import {mapState, mapActions} from "vuex";
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect/index.js'
import 'flatpickr/dist/plugins/monthSelect/style.css'
import TopMenu from "../../../components/portal/TopMenu";

export default {
    components: {
        flatPickr,
        TopMenu
    },
    data() {
        return {
            listData: [],
            searchMonth: '',
            selectMonth: '',
            selectTrTitle: '',
            weekIndex: [
                '日', '月', '火', '水', '木', '金', '土'
            ],
            showRowData: {
                punch_data: {
                    start1: '',
                    end1: '',
                    start2: '',
                    end2: '',
                    start3: '',
                    end3: '',
                    start4: '',
                    end4: '',
                    start5: '',
                    end5: '',
                }
            },
            filter: {
                todate: "",
            },
            configDate: {
                locale: Japanese,
                'nextArrow': '',
                'prevArrow': '',
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y年m月",
                    })
                ]
            },
            confirmPullBack: false
        }
    },
    computed: {},
    methods: {
        ...mapActions({
            timeCardList: "portal/timeCardList",
            timeCardUpdate: "portal/timeCardUpdate",
            timeCardDownloadCSV: "portal/timeCardDownloadCSV"
        }),

        dataInit: async function () {
            await this.timeCardList({'search': this.filter.todate}).then(
                response => {
                    this.listData = response.data
                },
                error => {
                    console.log(error)
                }
            )
        },

        changeMonth: function (selectedDates, dateStr, instance) {
            this.filter.todate = dateStr
            this.dataInit()
        },

        showDetail: function (data, index) {
            this.selectMonth = index
            let date = new Date(index)
            this.selectTrTitle = this.formatDate("YYYY年mm月dd日", date) + ' (' + this.weekIndex[date.getDay()] + ')'
            this.showRowData = JSON.parse(JSON.stringify(data))
            this.$modal.show('time-card-show-detail')
        },

        updatePunched: async function () {
            let data = {
                date_index: this.selectMonth,
                data: this.showRowData
            }
            await this.timeCardUpdate(data).then(
                response => {
                    this.dataInit()
                    this.showRowData.punch_data = this.tempRowData()
                    this.$modal.hide('time-card-show-detail')
                },
                error => {
                    console.log(error)
                }
            )
        },

        formatDate: function (fmt, date) {
            let ret
            const opt = {
                "Y+": date.getFullYear().toString(),        // 年
                "m+": (date.getMonth() + 1).toString(),     // 月
                "d+": date.getDate().toString(),            // 日
                "H+": date.getHours().toString(),           // 时
                "M+": date.getMinutes().toString(),         // 分
                "S+": date.getSeconds().toString()          // 秒
                //　増加し続けます
            }
            for (let k in opt) {
                ret = new RegExp("(" + k + ")").exec(fmt)
                if (ret) {
                    fmt = fmt.replace(ret[1], (ret[1].length == 1) ? (opt[k]) : (opt[k].padStart(ret[1].length, "0")))
                }
            }
            return fmt
        },

        deleteTime: function (rowNum) {
            this.showRowData.punch_data[rowNum] = ''
        },

        tempRowData: function () {
            return {
                start1: '',
                end1: '',
                start2: '',
                end2: '',
                start3: '',
                end3: '',
                start4: '',
                end4: '',
                start5: '',
                end5: '',
            }
        },

        modalClosed: function () {
            this.$modal.hide('time-card-show-detail')
            this.selectTrTitle = ''
        },

        openPop: function () {
            this.confirmPullBack = true
        },
        downloadCSV: function () {
            this.confirmPullBack = false
            this.timeCardDownloadCSV({'targetMonth': this.filter.todate}).then(
                res => {
                },
                err => {
                    console.log(err)
                }
            )
        },

        jumpToPrev: function () {
            this.$router.push('/groupware/time-card');
        }
    },

    created() {
        let myDate = new Date;
        this.filter.todate = myDate.getFullYear() + '-' + (myDate.getMonth() + 1);
        this.dataInit()
    }
}

</script>
<style lang="scss">
.iframe-groupware {
    height: calc(100vh - 87px);
}

td {
    text-align: right;
}

.vs-card--content {
    background-color: white !important;
}

.flatpickr-input {
    padding: 8px 8px !important;
    width: 175px !important;
    height: 40px;
    font-size: 18px;
    font-size: 1.22rem;
}

.numInput {
    padding-left: 30px !important;
}

.punched-time {
    width: 300px;
    height: 35px;
    border: #bbb solid 2px;
    font-size: 16px;
    font-weight: bold;
    color: #444;
}

.vs-con-table .vs-con-tbody .vs-table--tbody-table {
    border-collapse: separate !important;
}

.vs-table--tbody-table th, .vs-table--tbody-table td {
    font-size: 16px;
    padding: 5px;

}

#pre-page {
    height: 40px;
    color: #444 !important;
    font-size: 1rem;
    //border: 1px solid rgb(68, 68, 68);
    top: 42px;
    left: 18px;
    padding: 7px 23px;
    //background-color: #d9d9d9 !important;
}

.modal-content {
    position: relative;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-direction: column;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, .2);
    border-radius: 0.3rem;
    outline: 0;
}

.modal-header {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-align: start;
    align-items: flex-start;
    -ms-flex-pack: justify;
    justify-content: space-between;
    padding: 1rem 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: 0.3rem;
    border-top-right-radius: 0.3rem;
}

.modal-body {
    position: relative;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    padding: 1rem;
}

.card {
    position: relative;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-direction: column;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, .125);
    border-radius: 0.25rem;
    //margin-bottom: 50px;
}

.card-header {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    background-color: rgba(0, 0, 0, .03);
    border-bottom: 1px solid rgba(0, 0, 0, .125);
    font-size: 18px;
    border-radius: calc(0.25rem - 1px) calc(0.25rem - 1px) 0 0;
}

.card-body {
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    padding: 1.25rem;
}

.modal-footer {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-align: center;
    align-items: center;
    -ms-flex-pack: end;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    border-bottom-right-radius: 0.3rem;
    border-bottom-left-radius: 0.3rem;
}

.form-group {
    margin-bottom: 1rem;
}

.row {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin-right: -15 px;
    margin-left: -15 px;
}

.form-group .control-label {
    line-height: 38px;
    margin: 0;
    font-weight: 500;
    text-align: right;
}

.col-md-10 {
    position: relative;
    width: 100%;
    padding-right: 15px;
    //padding-left: 15px;
    flex: 0 0 83.333333%;
    max-width: 83.333333%;
}

.input-group {
    position: relative;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    -ms-flex-align: stretch;
    align-items: stretch;
    width: 100%;
}

.form-control {
    height: 34px;
    padding: 6px 12px;
    border: 1px solid #ced4da;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-size: inherit;
    line-height: 1.42857143;
    position: relative;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    width: 1%;
    margin-bottom: 0;
}

.form-group .control-label {
    font-size: 15px;
    line-height: 33px;
    margin: 0;
    font-weight: 500;
    text-align: center;
    flex: 0 0 16.666667%;
    max-width: 16.666667%;
}

.del-btn {
    display: inline-block;
    font-weight: 400;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    background-color: #EA5455;
    color: white;
    cursor: pointer;
}

.cancel-btn {
    display: inline-block;
    font-weight: 400;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.63rem 0.5rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    background: #d9d9d9;
    color: #212529;
    cursor: pointer;
}

.confirm-btn {
    display: inline-block;
    font-weight: 400;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.63rem 1.5rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    border: none;
    color: #fff;
    background: #28C76F;
    margin-right: 0.25rem;
    cursor: pointer;
}

.close {
    float: right;
    font-size: 2.5rem;
    //font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: .5;
    background-color: transparent;
    border: 0;
    padding: 0.5rem 1rem;
    margin: -1rem -1rem -1rem auto;
    cursor: pointer;
}

.disabled-input {
    background-color: #e9ecef;
}
</style>
