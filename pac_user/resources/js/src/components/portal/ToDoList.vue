<template>
  <div class="comp-portal-to-do-list">
    <!--  Page List  -->
    <template v-if="viewPage === 'task_list'">
      <split-pane :min-percent='0' :default-percent='10' split="vertical">
        <template slot="paneL">
          <vs-row class="left-part">
            <div class="to-do-action">
              <button @click="addToDo">
                <i class="fa fa-plus" aria-hidden="true"></i>
              </button>
              <button @click="showModal('delToDoModal')"
                      :disabled="(selectedPersonalId === 0 && currentType === 1) || (selectedPublicId === 0 && currentType === 2)">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </div>
            <vs-tabs v-model="selectedTab">
              <vs-tab :label="'個人'" class="to-do-list" @click="changeType(1)">
                <div v-if="!optionFlg" class="to-do-item" :class="selectedPersonalId === 0 ? 'active' : ''"
                     @click="selectToDo({})">受信一覧
                </div>
                <div v-for="to_do in toDoList" class="to-do-item"
                      :class="selectedPersonalId === to_do.id ? 'active' : ''"
                      @click="selectToDo(to_do)"
                      @dblclick="showModal('updateToDoModal', to_do)" :key="to_do.id">{{ to_do.title }}
                </div>
                <div class="text-center">
                  <button v-if="hasMore('list')" class="show-more cursor-pointer" @click="showMore('list')">
                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                  </button>
                </div>
              </vs-tab>

              <vs-tab :label="'共有'" class="to-do-list" @click="changeType(2)">
                <div v-for="to_do in toDoList" class="to-do-item"
                      :class="selectedPublicId === to_do.id ? 'active' : ''"
                      @click="selectToDo(to_do)"
                      @dblclick="showModal('updateToDoModal', to_do)" :key="to_do.id">{{ to_do.title }}
                </div>
                <div class="text-center">
                  <button v-if="hasMore('list')" class="show-more cursor-pointer" @click="showMore('list')">
                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                  </button>
                </div>
              </vs-tab>
            </vs-tabs>
          </vs-row>
        </template>
        <template slot="paneR">
          <vs-row class="right-part">
            <template v-if="!selectedPersonalId && currentType === 1 && !optionFlg">
              <circular
                :circulars="circularList"
                :title="circularTitle"
                :pagination="circularPagination"
                :important-list="importantList"
                @showModal="showModal"
                @loadCircularList="loadCircularList"
                @setViewPage="setViewPage"></circular>
            </template>
            <template v-if="(currentType === 2 && selectedPublicId) || (currentType === 1 && selectedPersonalId)">
              <task
                :has-more="hasMore('task')"
                :done-has-more="hasMore('doneTask')"
                :tasks="toDoTaskList"
                :done-tasks="doneTaskList"
                :title="toDoTitle"
                :important-list="importantList"
                :order-by="taskOrderBy"
                :order-dir="taskOrderDir"
                :done-order-by="doneOrderBy"
                :done-order-dir="doneOrderDir"
                :done-list-show="doneListShow"
                @showModal="showModal"
                @showMore="showMore"
                @loadTaskList="loadTaskList"
                @loadDoneTaskList="loadDoneTaskList"
                @setViewPage="setViewPage"
                @showDoneList="showDoneList"
                @hideDoneList="hideDoneList"></task>
            </template>
          </vs-row>
        </template>
      </split-pane>
    </template>
    <template v-if="viewPage === 'group'">
      <group :isMypage="isMypage" @setViewPage="setViewPage"></group>
    </template>

    <div class="popup-list">
      <vs-popup title="リストの削除" :active.sync="delToDoModal">
        <div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">{{ toDoTitle }}を削除してもよろしいですか？</vs-col>
          </vs-row>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
            <vs-button @click="delToDo" color="warning">はい</vs-button>
            <vs-button @click="delToDoModal = false" color="dark" type="border">いいえ</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>
      
      <vs-popup title="タスクの削除" :active.sync="delTaskModal">
        <div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">このタスクを削除してもよろしいですか？</vs-col>
          </vs-row>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
            <vs-button @click="delTask" color="warning">はい</vs-button>
            <vs-button @click="delTaskModal = false" color="dark" type="border">いいえ</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>
      
      <vs-popup title="タスク完了" :active.sync="doneTaskModal">
        <div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">このタスクを完了してもよろしいですか？</vs-col>
          </vs-row>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
            <vs-button @click="doneTask" color="warning">はい</vs-button>
            <vs-button @click="doneTaskModal = false" color="dark" type="border">いいえ</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>

      <vs-popup title="タスクを元に戻す" :active.sync="revokeTaskModal">
        <div>
          <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-w="8" class="max-width-360">このタスクを元に戻してもよろしいですか？</vs-col>
          </vs-row>
        </div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
            <vs-button @click="revokeTask" color="warning">はい</vs-button>
            <vs-button @click="revokeTaskModal = false" color="dark" type="border">いいえ</vs-button>
          </vs-col>
        </vs-row>
      </vs-popup>
    </div>

    <div class="modal-list">
      <vs-popup class="to-do-list modal-item" :class="isMypage ? 'mypage' : ''" title="" :active.sync="updateToDoModal">
        <header class="modal-header">
          <div @click.stop="closeModal">
            <vs-icon class="v-icon cursor-pointer close">close</vs-icon>
          </div>
        </header>
        
        <vs-card>
          <form onsubmit="return false;">
            <vs-row class="mt-3">
              <vs-col vs-w="3" class="text-left pr-3 pt-2 label">リスト名　<span class="text-red">*</span></vs-col>
              <vs-col vs-type="" vs-w="9">
                <vs-input placeholder="リスト名を入力" v-validate="'required'" v-model="updateToDoData.title" required
                          name="title" class="title w-full" maxlength="50" @keyup.enter="()=>{return false;}"/>
                <span class="text-danger text-sm" v-show="errors.has('title')">{{ errors.first('title') }}</span>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3" v-if="currentType===2">
              <vs-col vs-w="3" class="text-left pr-3 pt-2 label">共有先グループ　</vs-col>
              <vs-col vs-type="" vs-w="9">
                <v-select class="dropdown"
                          v-model="updateToDoData.group_id"
                          :options="group_list"
                          :reduce="(option) => option.id"
                          name="group_id"
                          label="title"
                          no-data-text="データがありません。"
                          placeholder="選択してください"
                          :value="updateToDoData.group_id"
                          :disabled="isDone"
                >
                </v-select>
              </vs-col>
            </vs-row>
          </form>
          <vs-row class="pt-3 pb-2 modal-action" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button :class="'update-btn'" color="success" @click="updateToDo">
              <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                   stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              更新
            </vs-button>
          </vs-row>
        </vs-card>
      </vs-popup>

      <vs-popup class="to-do-list modal-item" :class="isMypage ? 'mypage' : ''" title="" :active.sync="addUpdateTaskModal">
        <header class="modal-header">
          <div @click.stop="closeModal">
            <vs-icon class="v-icon cursor-pointer close">close</vs-icon>
          </div>
        </header>

        <vs-card>
          <form onsubmit="return false;" :class="isDone ? 'done' : ''">
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">タスク名　<span class="text-red">*</span></vs-col>
              <vs-col vs-type="" vs-w="9">
                <vs-input placeholder="タスク名を入力" v-validate="'required'" v-model="updateTaskData.title" required
                          name="task_title" maxlength="50" :disabled="isDone"/>
                <span class="text-danger text-sm" v-show="errors.has('task_title')">{{ errors.first('task_title') }}</span>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">詳細</vs-col>
              <vs-col vs-type="" vs-w="9">
                <vs-textarea placeholder="詳細を入力" v-model="updateTaskData.content" name="content" rows="3" auto-grow :disabled="isDone"/>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">期限日</vs-col>
              <vs-col vs-type="" vs-w="4" class="calendar-box update-task">
                <i class="fa fa-calendar-day"></i>
                <flat-picker ref="calendar" class="w-full" :config="picker_config" :events="picker_events"
                             v-model="updateTaskData.deadline" name="deadline" @on-open="getConfig"
                             @on-value-update="initDate('update-task', 'deadline', updateTaskData)" :disabled="isDone"></flat-picker>
              </vs-col>
            </vs-row>
            <span class="text-sm" v-if="this.checkCalendarApp && (currentType === 1 || this.checkSharedScheduler)" style="margin-left: 16.667%;">スケジューラへの連携は期限日が必須です。</span>
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">優先</vs-col>
              <vs-col vs-type="" vs-w="4">
                <v-select class="dropdown"
                          v-model="updateTaskData.important"
                          :options="importantList"
                          :reduce="(option) => option.id"
                          name="task_important"
                          label="text"
                          :clearable="false"
                          :searchable="false"
                          no-data-text="データがありません。"
                          placeholder="選択してください"
                          :value="updateTaskData.important"
                          :disabled="isDone"
                >
                </v-select>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3" v-if="this.checkCalendarApp && (currentType === 1 || this.checkSharedScheduler)">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">スケジューラ</vs-col>
              <vs-col vs-type="" vs-w="8" class="scheduler-box">
                <v-select class="dropdown"
                          name="scheduler_id"
                          label="name"
                          no-data-text="データがありません。"
                          placeholder="選択してください"
                          v-model="updateTaskData.scheduler_id"
                          :disabled="!updateTaskData.deadline || isDone"
                          :options="schedulerList"
                          :reduce="(option) => option.id"
                          :value="updateTaskData.scheduler_id">
                </v-select>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3" v-if="updateTaskData.id && !updateTaskData.parent_id">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">サブタスク</vs-col>
              <vs-col vs-type="" vs-w="9">
                <div class="sub-list" v-if="sub_task && sub_task.length > 0">
                  <span class="sub-item" v-for="(task, index) in sub_task" :key="index">
                      {{ task }}<feather-icon icon="XIcon" aria-hidden="true" @click="delSubTask(index)"></feather-icon>
                  </span>
                </div>
                <vs-input v-model="sub_title_input" placeholder="Enterキーを押して追加します" name="sub_title_input"
                          @keyup.enter="addSubTask" @change="addSubTaskDefault($event)" maxlength="50" :disabled="isDone"/>
              </vs-col>
            </vs-row>
          </form>
          <vs-row class="pt-3 pb-2 modal-action" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <div v-show="isDone">
              <vs-button class="revoke" @click="showModal('revokeTaskModal', updateTaskData)">このタスクを未完了にする</vs-button>
              <vs-button color="danger" @click="delTaskModal = true"><i class="fas fa-trash-alt"></i> 削除</vs-button>
            </div>
            <div v-show="!isDone">
              <vs-button v-show="!updateTaskData.id" color="success" @click="addTask()"><i class="fas fa-plus-circle"></i> 登録</vs-button>
              <vs-button v-show="updateTaskData.id" :class="'update-btn'" color="success" @click.stop="updateTask">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                  <polyline points="17 21 17 13 7 13 7 21"></polyline>
                  <polyline points="7 3 7 8 15 8"></polyline>
                </svg>更新
              </vs-button>
              <vs-button v-if="updateTaskData.id" color="danger" @click="delTaskModal = true"><i class="fas fa-trash-alt"></i> 削除</vs-button>
            </div>
          </vs-row>
        </vs-card>
      </vs-popup>
      
      <vs-popup class="to-do-list modal-item" :class="isMypage ? 'mypage' : ''" title="" :active.sync="updateCircularTaskModal">
        <header class="modal-header">
          <div @click.stop="closeModal">
            <vs-icon class="v-icon cursor-pointer close">close</vs-icon>
          </div>
        </header>
        
        <vs-card>
          <form onsubmit="return false;">
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">タスク名</vs-col>
              <vs-col vs-type="" vs-w="9">
                <div class="w-full flatpickr-input vs-input-no-border bold">{{ updateCircularTaskData.title }}</div>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">詳細</vs-col>
              <vs-col vs-type="" vs-w="9">
                <vs-textarea placeholder="詳細を入力" v-model="updateCircularTaskData.content" name="content" rows="3"
                             auto-grow/>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">期限日</vs-col>
              <vs-col vs-type="" vs-w="4" class="calendar-box update-circular-task">
                <i class="fa fa-calendar-day"></i>
                <flat-picker ref="calendar" class="w-full" :config="picker_config" :events="picker_events"
                             v-model="updateCircularTaskData.deadline" name="circular_deadline" @on-open="getConfig"
                             @on-value-update="initDate('update-circular-task', 'circular_deadline', updateCircularTaskData)"></flat-picker>
              </vs-col>
            </vs-row>
            <span class="text-sm" v-if="this.checkCalendarApp" style="margin-left: 16.667%;">スケジューラへの連携は期限日が必須です。</span>
            <vs-row class="mt-3">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">優先</vs-col>
              <vs-col vs-type="" vs-w="4">
                <v-select class="dropdown"
                          v-model="updateCircularTaskData.important"
                          :options="importantList"
                          :reduce="(option) => option.id"
                          name="task_important"
                          label="text"
                          :clearable="false"
                          :searchable="false"
                          no-data-text="データがありません。"
                          placeholder="選択してください"
                          :value="updateCircularTaskData.important">
                </v-select>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3" v-if="this.checkCalendarApp">
              <vs-col vs-w="2" class="text-left pr-3 pt-2 label">スケジューラ</vs-col>
              <vs-col vs-type="" vs-w="8" class="scheduler-box">
                <v-select class="dropdown"
                          name="circular_scheduler_id"
                          label="name"
                          no-data-text="データがありません。"
                          placeholder="選択してください"
                          v-model="updateCircularTaskData.scheduler_id"
                          :disabled="!updateCircularTaskData.deadline"
                          :options="schedulerList"
                          :reduce="(option) => option.id"
                          :value="updateCircularTaskData.scheduler_id">
                </v-select>
              </vs-col>
            </vs-row>
          </form>
          <vs-row class="pt-3 pb-2 modal-action" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button :class="'update-btn'" color="success" @click.stop="updateCircularTask">
              <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                   stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              更新
            </vs-button>
          </vs-row>
        </vs-card>
      </vs-popup>
      
      <vs-popup class="to-do-list modal-item" :class="isMypage ? 'mypage' : ''" title="" :active.sync="settingNoticeConfigModal">
        <header class="modal-header">
          <div @click.stop="closeModal">
            <vs-icon class="v-icon cursor-pointer close">close</vs-icon>
          </div>
        </header>
        
        <vs-card>
          <form onsubmit="return false;">
            <vs-row class="mt-3">
              <vs-col vs-type="" vs-w="4" class="padding-9">
                <vs-radio v-model="noticeConfigData.state" vs-name="notice_state" vs-value="0">通知しない</vs-radio>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3">
              <vs-col vs-type="" vs-w="3" class="padding-9">
                <vs-radio v-model="noticeConfigData.state" vs-name="notice_state" vs-value="1">通知する</vs-radio>
              </vs-col>
              <vs-col vs-type="" vs-w="2" class="padding-9" v-if="noticeConfigData.state === 1">
                <vs-checkbox v-model="noticeConfigData.email_flg" vs-value="1">メール</vs-checkbox>
              </vs-col>
              <vs-col vs-type="" vs-w="3" class="padding-9" v-if="noticeConfigData.state === 1">
                <vs-checkbox v-model="noticeConfigData.notice_flg" vs-value="1">プッシュ通知</vs-checkbox>
              </vs-col>
            </vs-row>
            <vs-row class="mt-3" v-if="noticeConfigData.state === 1">
              <vs-col vs-type="" vs-w="4" class="advance-time-box">
                <v-select class="dropdown"
                          v-model="noticeConfigData.advance_time"
                          :options="advanceTimeList"
                          :reduce="(option) => option.value"
                          name="notice_advance_time"
                          label="name"
                          :clearable="false"
                          :searchable="false">
                </v-select>
                <input type="hidden" name='advance_time' :value="noticeConfigData.advance_time">
              </vs-col>
              <vs-col vs-type="" vs-w="4" class="padding-9">
                <span class="active-time-label">に通知</span>
              </vs-col>
            </vs-row>
          </form>
          <vs-row class="pt-3 pb-2 modal-action" vs-type="flex" vs-justify="flex-end" vs-align="center">
            <vs-button :class="'update-btn'" color="success" @click.stop="settingNotice">
              <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                   stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              変更
            </vs-button>
          </vs-row>
        </vs-card>
      </vs-popup>
    </div>
  </div>
</template>

<script>

import {Validator} from 'vee-validate';
import {mapState, mapActions} from "vuex";
import 'flatpickr/dist/flatpickr.min.css';

const dict = {
  custom: {
    title: {
      required: '* 必須項目です',
      max: '* 50文字以上は入力できません。',
    },
    task_title: {
      required: '* 必須項目です',
      max: '* 50文字以上は入力できません。',
    },
  }
};
Validator.localize('ja', dict);
export default {
  components: {
    splitPane: () => import('vue-splitpane'),
    Task: () => import('./to_do_list/Task'),
    Group: () => import('./to_do_list/Group'),
    Circular: () => import('./to_do_list/Circular'),
    flatPicker: () => import('vue-flatpickr-component'),
  },
  name: "ToDoList",
  props: {
    isMypage: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      currentType: 1,
      selectedPersonalId: 0,
      selectedPublicId: 0,
      toDoTitle: '',
      circularTitle: '受信一覧',
      advanceTimeList: [
        {'name': '7日前', 'value': 604800},
        {'name': '6日前', 'value': 518400},
        {'name': '5日前', 'value': 432000},
        {'name': '4日前', 'value': 345600},
        {'name': '3日前', 'value': 259200},
        {'name': '2日前', 'value': 172800},
        {'name': '1日前', 'value': 86400},
        {'name': '12時間前', 'value': 43200},
        {'name': '6時間前', 'value': 21600},
        {'name': '3時間前', 'value': 10800},
        {'name': '1時間前', 'value': 3600},
      ],
      importantList: [{id: 0, text: '選択してください'}, {id: 3, text: '高'}, {id: 2, text: '中'}, {id: 1, text: '低'},],
      schedulerList: [],
      toDoList: [],
      group_list: [],
      toDoTaskList: [],
      doneTaskList: [],
      toDoTaskListData: [],
      circularList: [],
      update_scheduler_id: 0,
      update_group_id: 0,
      personalPagination: {totalPage: 0, currentPage: 1, limit: 5, totalItem: 0, from: 1, to: 5, defaultLimit: 5},
      publicPagination: {totalPage: 0, currentPage: 1, limit: 5, totalItem: 0, from: 1, to: 5, defaultLimit: 5},
      circularPagination: {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10},
      taskPagination: {totalPage: 0, currentPage: 1, limit: 5, totalItem: 0, from: 1, to: 5, defaultLimit: 5},
      doneTaskPagination: {totalPage: 0, currentPage: 1, limit: 5, totalItem: 0, from: 1, to: 5, defaultLimit: 5},
      orderBy: "update_at",
      orderDir: "DESC",
      taskOrderBy: "created_at",
      taskOrderDir: "desc",
      doneOrderBy: "updated_at",
      doneOrderDir: "desc",
      doneListShow: false,
      defaultToDoTitle: '新規リスト',
      updateToDoData: {id: 0, title: '', type: 1},
      updateTaskData: {
        id: 0,
        parent_id: 0,
        title: '',
        content: '',
        deadline: '',
        important: 0,
        scheduler_id: 0,
        scheduler_title: ''
      },
      updateCircularTaskData: {
        id: 0,
        circular_user_id: 0,
        title: '',
        content: '',
        deadline: '',
        important: 0,
        scheduler_id: 0,
        scheduler_title: ''
      },
      noticeConfigData: {email_flg: 0, notice_flg: 1, state: 1, advance_time: 86400},
      sub_title_input: '',
      sub_task: [],
      delToDoModal: false,
      revokeTaskModal: false,
      delTaskModal: false,
      updateToDoModal: false,
      addUpdateTaskModal: false,
      updateCircularTaskModal: false,
      settingNoticeConfigModal: false,
      doneTaskModal: false,
      optionFlg: JSON.parse(getLS('user')).option_flg,
      picker_config: {
        minDate: '',
        defaultMinute: '',
        minuteIncrement: 1,
        altFormat: 'Y-m-d H:i',
        enableTime: true,
        defaultHour: '',
        time_24hr: true,
      },
      picker_events: ['onOpen', 'onValueUpdate'],
      isDone:false,
      viewPage: 'task_list',
      selectToDoTimer: null,
    }
  },
  computed: {
    selectedTab: {
      get() {
        return this.currentType - 1;
      },
      set(value) {
        this.currentType = parseInt(value) + 1;
      }
    },
    selectedId: {
      get() {
        if (this.currentType === 1) return parseInt(this.selectedPersonalId);
        if (this.currentType === 2) return parseInt(this.selectedPublicId);
        return 0;
      },
      set(value) {
        if (this.currentType === 1) return this.selectedPersonalId = parseInt(value);
        if (this.currentType === 2) return this.selectedPublicId = parseInt(value);
      },
    },
    checkSharedScheduler: {
      get() {
        return this.$store.state.groupware.checkSharedScheduler
      },
    },
    checkCalendarApp: {
      get() {
        return this.$store.state.groupware.checkCalendarApp
      },
    },
    tokenGroupware: {
      get() {
        let tokenGroupware = this.$cookie.get('accessToken');
        if ((this.currentType !== 1 && !this.checkSharedScheduler) || !this.checkCalendarApp) return null;
        if (!tokenGroupware) return null;
        return tokenGroupware;
      },
    },
    pagination: {
      get() {
        if (this.currentType === 1) return this.personalPagination;
        if (this.currentType === 2) return this.publicPagination;
      },
      set(value) {
        if (this.currentType === 1) return this.personalPagination = value;
        if (this.currentType === 2) return this.publicPagination = value;
      },
    },
    getNextTaskPage() {
      return function (type) {
        let page = null;
        switch (type) {
          case 'list':
            page = this.pagination;
            break;
          case 'task':
            page = this.taskPagination;
            break;
          case 'doneTask':
            page = this.doneTaskPagination;
            break;
          default:
            break;
        }
        if (page) return page.limit > 5 ? (page.limit / 5) + 1 : page.currentPage + 1;
        return 0;
      }
    },
    hasMore() {
      return function (type) {
        let page = null;
        let data = [];
        switch (type) {
          case 'list':
            page = this.pagination;
            data = this.toDoList;
            break;
          case 'task':
            page = this.taskPagination;
            data = this.toDoTaskList;
            break;
          case 'doneTask':
            page = this.doneTaskPagination;
            data = this.doneTaskList;
            break;
        }
        return page && data.length > 0 && page.totalItem > 0 && page.to < page.totalItem && page.currentPage * page.limit < page.totalItem;
      }
    },
  },
  async mounted() {
    this.clearUpdateData();
    this.currentType = 1;
    this.initPage('task');
    this.initPage('doneTask');
    this.toDoTitle = '';
    this.clearCircularData();
    if (!this.$store.state.groupware.checkToDoList) {
      window.location.href = '/app/pages/error-404';
    }
    await this.loadToDoList(true);
    if (this.toDoList.length === 0 && !this.optionFlg) {
      this.selectToDo({});
    }
  },
  methods: {
    ...mapActions({
      addLogOperation: "logOperation/addLog",
      getToDoList: 'portal/getToDoList',
      addToDoList: 'portal/addToDoList',
      updateToDoList: 'portal/updateToDoList',
      deleteToDoList: 'portal/deleteToDoList',
      getToDoTask: 'portal/getToDoTask',
      getToDoGroupList: 'portal/getToDoGroupList',
      getToDoTaskDetail: 'portal/getToDoTaskDetail',
      addToDoTask: 'portal/addToDoTask',
      updateToDoTask: 'portal/updateToDoTask',
      deleteToDoTask: 'portal/deleteToDoTask',
      doneToDoTask: 'portal/doneToDoTask',
      revokeToDoTask: 'portal/revokeToDoTask',
      getToDoCircular: 'portal/getToDoCircular',
      getToDoCircularDetail: 'portal/getToDoCircularDetail',
      updateToDoCircularTask: 'portal/updateToDoCircularTask',
      getToDoPublicSchedulerList: 'portal/getToDoPublicSchedulerList',
      settingToDoNotice: 'portal/settingToDoNotice',
      getToDoNoticeConfig: 'portal/getToDoNoticeConfig',
    }),
    async selectToDo(to_do) {
      clearTimeout(this.selectToDoTimer);
      this.selectToDoTimer = setTimeout(async ()=> {
        if (to_do && to_do.id) {
          this.initPage('task');
          this.initPage('doneTask');
          this.toDoTitle = to_do.title;
          this.selectedId = parseInt(to_do.id);
          await this.loadTaskList({});
          if (this.doneListShow) await this.loadDoneTaskList({});
        } else {
          this.selectedId = 0;
          if (this.currentType === 1) {
            this.clearCircularData();
            if (!this.optionFlg) await this.loadCircularList();
          }
        }
      }, 300);
    },
    async loadToDoList(initPage = false, refresh = false) {
      if (initPage) {
        this.initPage('list');
      }
      if (refresh && this.pagination.limit === 5) {
        this.pagination.limit = this.pagination.limit * this.pagination.currentPage;
        this.pagination.currentPage = 1;
      }
      let param = {
        limit: this.pagination.limit,
        page: this.pagination.currentPage,
        type: this.currentType,
      };
      const data = await this.getToDoList(param);
      if (data && data.data) {
        const toDoList = Object.assign([], data.data);
        if (this.pagination.limit > 5 || this.pagination.currentPage === 1) {
          let selected = toDoList[0] ? toDoList[0] : 0;
          if (this.pagination.currentPage === 1) {
            
            toDoList.filter((item) => {
              if (item.id === this.selectedId) selected = Object.assign({}, item);
            })
          }
          this.toDoList = toDoList;
          if (this.pagination.currentPage === 1) await this.selectToDo(selected);
        } else {
          if (this.pagination.totalItem !== data.total) {
            this.loadToDoList(false, true);
            return;
          }
          this.toDoList.push(...toDoList);
        }
        this.pagination.totalItem = data.total;
        this.pagination.totalPage = data.last_page;
        this.pagination.limit = data.per_page;
        this.pagination.from = data.from;
        this.pagination.to = data.to;
      } else {
        if (this.pagination.currentPage > 1) {
          this.pagination.currentPage--;
        } else {
          this.toDoList = [];
          this.selectedId = 0;
        }
      }
    },
    async loadTaskList(params = {}, refresh = false) {
      if (this.selectedId === 0) return false;
      if (refresh && this.taskPagination.limit === 5) {
        this.taskPagination.limit = this.taskPagination.limit * this.taskPagination.currentPage;
        this.taskPagination.currentPage = 1;
      }
      
      let param = {
        limit: this.taskPagination.limit,
        page: this.taskPagination.currentPage,
        orderBy: this.taskOrderBy,
        orderDir: this.taskOrderDir,
        to_do_id: this.selectedId,
      };
      if (params.orderBy) {
        const orderBy = params.orderBy;
        const orderDir = params.orderDir;
        
        param.orderBy = orderBy;
        param.orderDir = orderDir;
        this.taskOrderBy = orderBy;
        this.taskOrderDir = orderDir;
      }
      const data = await this.getToDoTask(param);
      if (data && data.data) {
        
        const toDoTaskList = Object.assign([], data.data);
        
        if (this.taskPagination.limit > 5 || this.taskPagination.currentPage === 1) {
          this.toDoTaskList = toDoTaskList;
        } else {
          if (this.taskPagination.totalItem !== data.total) {
            this.loadTaskList({}, true);
            return;
          }
          this.toDoTaskList.push(...toDoTaskList);
        }
        this.taskPagination.totalItem = data.total;
        this.taskPagination.totalPage = data.last_page;
        this.taskPagination.limit = data.per_page;
        this.taskPagination.from = data.from;
        this.taskPagination.to = data.to;
      } else {
        this.toDoTaskList = [];
      }
    },
    async loadDoneTaskList(params = {}, refresh = false) {
      if (refresh && this.doneTaskPagination.limit === 5) {
        this.doneTaskPagination.limit = this.doneTaskPagination.limit * this.doneTaskPagination.currentPage;
        this.doneTaskPagination.currentPage = 1;
      }

      let param = {
        limit: this.doneTaskPagination.limit,
        page: this.doneTaskPagination.currentPage,
        orderBy: this.taskOrderBy,
        orderDir: this.doneOrderDir,
        to_do_id: this.selectedId,
        done: 1,
      };
      if (params.orderBy) {
        const orderBy = params.orderBy;
        const orderDir = params.orderDir;

        param.orderBy = orderBy;
        param.orderDir = orderDir;
        this.doneOrderBy = orderBy;
        this.doneOrderDir = orderDir;
      }
      const data = await this.getToDoTask(param);
      if (data && data.data) {

        const doneTaskList = Object.assign([], data.data);

        if (this.doneTaskPagination.limit > 5 || this.doneTaskPagination.currentPage === 1) {
          this.doneTaskList = doneTaskList;
        } else {
          if (this.doneTaskPagination.totalItem !== data.total) {
            this.loadDoneTaskList({}, true);
            return;
          }
          this.doneTaskList.push(...doneTaskList);
        }
        this.doneTaskPagination.totalItem = data.total;
        this.doneTaskPagination.totalPage = data.last_page;
        this.doneTaskPagination.limit = data.per_page;
        this.doneTaskPagination.from = data.from;
        this.doneTaskPagination.to = data.to;
      } else {
        this.doneTaskList = [];
      }
    },
    showDoneList() {
      this.doneListShow = true;
      if (!this.doneTaskList || this.doneTaskList.length === 0) {
        this.loadDoneTaskList({});
      }
    },
    hideDoneList() {
      this.doneListShow = false;
    },
    async showMore(type) {
      switch (type) {
        case 'list':
          this.pagination.currentPage = this.getNextTaskPage(type);
          this.pagination.limit = this.pagination.defaultLimit;
          this.loadToDoList();
          break;
        case 'task':
          this.taskPagination.currentPage = this.getNextTaskPage(type);
          this.taskPagination.limit = this.taskPagination.defaultLimit;
          this.loadTaskList();
          break;
        case 'doneTask':
          this.doneTaskPagination.currentPage = this.getNextTaskPage(type);
          this.doneTaskPagination.limit = this.doneTaskPagination.defaultLimit;
          this.loadDoneTaskList();
          break;
        default:
          break;
      }
    },
    async loadCircularList(params = {}) {
      let param = {
        limit: this.circularPagination.limit,
        page: this.circularPagination.currentPage,
        orderBy: this.orderBy,
        orderDir: this.orderDir,
      };
      if (params.orderBy) {
        const orderBy = params.orderBy;
        const orderDir = params.orderDir;
        
        param.orderBy = orderBy;
        param.orderDir = orderDir;
        this.orderBy = orderBy;
        this.orderDir = orderDir;
      }
      const data = await this.getToDoCircular(param);
      if (data && data.data) {
        const circularData = data.data;
        this.circularList = Object.assign([], circularData.data);
        this.circularPagination.totalItem = circularData.total;
        if (this.circularPagination.totalPage !== circularData.last_page
          && this.circularPagination.currentPage > circularData.last_page
          && circularData.last_page > 0
        ) {
          this.circularPagination.currentPage = circularData.last_page;
        }
        this.circularPagination.totalPage = circularData.last_page;
        this.circularPagination.limit = circularData.per_page;
        this.circularPagination.from = circularData.from;
        this.circularPagination.to = circularData.to;
      }
    },
    async changeType(type) {
      this.initPage('task');
      this.initPage('doneTask');
      this.toDoTitle = '';
      this.clearCircularData();
      this.toDoList = [];
      await this.loadToDoList(false, true);
      if (type === 1 && this.selectedId === 0) {
        await this.defaultHandlerList()
      }
    },
    async defaultHandlerList() {
      await this.selectToDo({})
    },
    async showModal(modal, data = {}) {
      if (modal !== 'revokeTaskModal') this.isDone = false;
      this.clearUpdateData();
      let updateData = Object.assign({}, data);
      switch (modal) {
        case 'updateToDoModal':
          clearTimeout(this.selectToDoTimer);
          this.selectToDoTimer = null;
          if (this.currentType === 2) {
            const group_list_data = await this.getToDoGroupList();
            if (group_list_data && group_list_data.data) {
              const group_list = Object.assign([], group_list_data.data);
              if (updateData && updateData.group_id && updateData.group_id > 0) {
                const title = updateData.group_title;
                let has = false;
                group_list.map((item) => {
                  if (item.id === updateData.group_id) {
                    has = true;
                  }
                })
                if (!has && title && typeof title === 'string' && title !== '') {
                  const id = updateData.group_id;
                  updateData.group_id = title;
                  this.update_group_id = id;
                }
              }
              this.group_list = group_list
            }
          }
          this.updateToDoData = updateData;
          break;
        case 'addUpdateTaskModal':
          if (updateData && updateData.id) {
            const param = {
              id: updateData.id,
              tokenGroupware: this.tokenGroupware,
            };
            const data = await this.getToDoTaskDetail(param);
            if (data && data.data) {
              updateData = Object.assign({}, data.data);
              if (parseInt(updateData.state) === 2) this.isDone = true;
            } else {
              this.clearUpdateData();
              await this.loadTaskList({}, true);
              if (this.doneListShow || this.doneTaskList.length > 0) await this.loadDoneTaskList({}, true);
              return;
            }
          }
          if (!this.checkCalendarApp || (this.currentType !== 1 && !this.checkSharedScheduler)) {
            updateData.scheduler_id = 0;
          }
          if (this.checkCalendarApp && (this.currentType === 1 || this.checkSharedScheduler)) await this.loadSchedulerList(updateData);
          this.updateTaskData = updateData;
          break;
        case 'updateCircularTaskModal':
          const param = {
            circular_user_id: updateData.circular_user_id,
            tokenGroupware: this.tokenGroupware,
          };
          const data = await this.getToDoCircularDetail(param);
          if (data && data.data) {
            if (data.data.id > 0) updateData = Object.assign({}, data.data);
          } else {
            this.clearUpdateData();
            this.clearCircularData();
            await this.loadCircularList();
            return;
          }
          if (updateData.title.length > 50) {
            updateData.title = updateData.title.substring(0, 50);
          }
          if (!this.checkCalendarApp) {
            updateData.scheduler_id = 0;
          }
          if (this.checkCalendarApp) await this.loadSchedulerList(updateData);
          this.updateCircularTaskData = updateData;
          break;
        case 'settingNoticeConfigModal':
          $('body').click();
          await this.loadNoticeConfig();
          break;
        case 'doneTaskModal':
          this.updateTaskData = updateData;
          break;
        case 'revokeTaskModal':
          this.updateTaskData = updateData;
          break;
        default:
          break;
      }
      this.$validator.reset();
      this[modal] = true;
    },
    async addToDo() {
      const param = {
        type: this.currentType,
        title: this.defaultToDoTitle,
      }
      const data = await this.addToDoList(param);
      if (data && data.to_do_list_id) {
        this.selectedId = data.to_do_list_id;
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-add-to-do-list', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-add-to-do-list', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-add-to-do-list', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-add-to-do-list', result: 1})
        }
      }
      await this.loadToDoList(false, true);
    },
    async updateToDo() {
      const validate = await this.$validator.validate('title');
      if (!validate) return;
      const param = this.updateToDoData;
      if (!param.group_id) param.group_id = 0;
      if (this.update_group_id && this.update_group_id > 0 && typeof param.group_id === 'string') {
        param.group_id = this.update_group_id;
      }
      let result = await this.updateToDoList(param);
      if (result) {
        this.closeModal();
        this.loadToDoList(false, true);
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-list', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-list', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-list', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-list', result: 1})
        }
      }
    },
    async delToDo() {
      const param = {
        id: this.selectedId,
        tokenGroupware: this.tokenGroupware,
      }
      if (this.selectedId) {
        let result = await this.deleteToDoList(param);
        if (result) {
          if (this.currentType === 1) {
            this.addLogOperation({action: 'portal-personal-del-to-do-list', result: 0})
          } else {
            this.addLogOperation({action: 'portal-public-del-to-do-list', result: 0})
          }
        } else {
          if (this.currentType === 1) {
            this.addLogOperation({action: 'portal-personal-del-to-do-list', result: 1})
          } else {
            this.addLogOperation({action: 'portal-public-del-to-do-list', result: 1})
          }
        }
      }
      this.closeModal();
      this.loadToDoList(false, true);
      if (this.selectedId === 0) {
        await this.defaultHandlerList()
      }
    },
    async addTask() {
      const validate = await this.$validator.validate('task_title');
      if (!validate) return;
      const deadline = this.updateTaskData.deadline;
      const important = this.updateTaskData.important;
      const participant_ids = [];
      if (this.updateTaskData.scheduler_id > 0 && this.schedulerList.length > 0) {
        this.schedulerList.filter((item) => {
          if (item.id === parseInt(this.updateTaskData.scheduler_id) && item.participantUserList) {
            item.participantUserList.filter((par_item) => {
              participant_ids.push(par_item.id);
            })
            if (item.participantUserList.length === 0 && item.name === 'Ｍｙスケジューラ') {
              participant_ids.push(item.id)
            }
          }
        })
      }
      const param = {
        to_do_list_id: this.selectedId,
        title: this.updateTaskData.title,
        task_content: this.updateTaskData.content || '',
        important: important || 0,
        scheduler_id: this.updateTaskData.scheduler_id || 0,
        deadline: deadline,
        participant_ids: participant_ids,
        tokenGroupware: this.tokenGroupware,
      };
      let result = await this.addToDoTask(param);
      if (result) {
        this.closeModal();
        this.loadTaskList({}, true);
        if (this.doneListShow || this.doneTaskList.length > 0) this.loadDoneTaskList({}, true);
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-add-to-do-task', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-add-to-do-task', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-add-to-do-task', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-add-to-do-task', result: 1})
        }
      }
    },
    async updateTask() {
      const validate = await this.$validator.validate('task_title');
      if (!validate) return;
      
      const deadline = this.updateTaskData.deadline;
      const important = this.updateTaskData.important;
      const sub_task = Object.assign([], this.sub_task);
      if (this.sub_title_input.trim() !== '') sub_task.push(this.sub_title_input);
      let scheduler_id = this.updateTaskData.scheduler_id;
      if (this.update_scheduler_id && typeof this.updateTaskData.scheduler_id === 'string') {
        scheduler_id = this.update_scheduler_id;
      }
      const participant_ids = [];
      if (scheduler_id > 0 && this.schedulerList.length > 0) {
        this.schedulerList.filter((item) => {
          if (item.id === parseInt(scheduler_id) && item.participantUserList) {
            item.participantUserList.filter((par_item) => {
              participant_ids.push(par_item.id);
            })
            if (item.participantUserList.length === 0 && item.name === 'Ｍｙスケジューラ') {
              participant_ids.push(item.id)
            }
          }
        })
      }
      const param = {
        id: this.updateTaskData.id,
        title: this.updateTaskData.title,
        task_content: this.updateTaskData.content || '',
        important: important || 0,
        scheduler_id: scheduler_id || 0,
        deadline: deadline,
        sub_task: this.sub_task,
        participant_ids: participant_ids,
        tokenGroupware: this.tokenGroupware,
      };
      
      let result = await this.updateToDoTask(param);
      if (result) {
        this.closeModal();
        this.loadTaskList({}, true);
        if (this.doneListShow || this.doneTaskList.length > 0) this.loadDoneTaskList({}, true);
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-task', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-task', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-task', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-task', result: 1})
        }
      }
    },
    async addSubTask() {
      const title = this.sub_title_input;
      if (title) {
        this.sub_task.push(title);
        this.sub_title_input = '';
      }
    },
    async delSubTask(index) {
      this.sub_task.splice(index, 1);
    },
    async delTask() {
      const param = {
        id: this.updateTaskData.id,
        tokenGroupware: this.tokenGroupware,
      }
      let result = await this.deleteToDoTask(param);
      if (result) {
        this.closeModal();
        this.loadTaskList({}, true);
        if (this.doneListShow || this.doneTaskList.length > 0) this.loadDoneTaskList({}, true);
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-del-to-do-task', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-del-to-do-task', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-del-to-do-task', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-del-to-do-task', result: 1})
        }
      }
    },
    async doneTask() {
      let result = await this.doneToDoTask(this.updateTaskData.id);
      if (result) {
        this.closeModal();
        this.loadTaskList({}, true);
        if (this.doneListShow || this.doneTaskList.length > 0) this.loadDoneTaskList({}, true);
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-task', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-task', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-task', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-task', result: 1})
        }
      }
    },
    async revokeTask() {
      let result = await this.revokeToDoTask(this.updateTaskData.id);
      if (result) {
        this.closeModal();
        this.loadTaskList({}, true);
        this.loadDoneTaskList({}, true);
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-task', result: 0})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-task', result: 0})
        }
      } else {
        if (this.currentType === 1) {
          this.addLogOperation({action: 'portal-personal-update-to-do-task', result: 1})
        } else {
          this.addLogOperation({action: 'portal-public-update-to-do-task', result: 1})
        }
      }
    },
    async updateCircularTask() {
      const deadline = this.updateCircularTaskData.deadline;
      const important = this.updateCircularTaskData.important;
      const participant_ids = [];
      let scheduler_id = this.updateCircularTaskData.scheduler_id;
      if (scheduler_id > 0 && this.schedulerList.length > 0) {
        this.schedulerList.filter((item) => {
          if (item.id === parseInt(scheduler_id) && item.participantUserList) {
            item.participantUserList.filter((par_item) => {
              participant_ids.push(par_item.id);
            })
            if (item.participantUserList.length === 0 && item.name === 'Ｍｙスケジューラ') {
              participant_ids.push(item.id)
            }
          }
        })
      }
      const param = {
        circular_user_id: this.updateCircularTaskData.circular_user_id,
        title: this.updateCircularTaskData.title,
        task_content: this.updateCircularTaskData.content || '',
        important: important || 0,
        scheduler_id: scheduler_id || 0,
        deadline: deadline,
        sub_task: this.sub_task,
        participant_ids: participant_ids,
        tokenGroupware: this.tokenGroupware,
      };
      
      let result = await this.updateToDoCircularTask(param);
      if (result) {
        this.closeModal();
        this.loadCircularList();
        this.addLogOperation({action: 'portal-received-update-to-do-task', result: 0})
      } else {
        this.addLogOperation({action: 'portal-received-update-to-do-task', result: 1})
      }
    },
    async loadNoticeConfig() {
      const data = await this.getToDoNoticeConfig();
      if (data && data.data) {
        const noticeData = data.data;
        this.noticeConfigData = {
          email_flg: noticeData.email_flg,
          notice_flg: noticeData.notice_flg,
          state: noticeData.state,
          advance_time: noticeData.advance_time,
        }
      }
    },
    async settingNotice() {
      const noticeData = this.noticeConfigData;
      const param = {
        email_flg: noticeData.email_flg || 0,
        notice_flg: noticeData.notice_flg || 0,
        state: noticeData.state,
        advance_time: noticeData.advance_time,
      };
      
      let result = await this.settingToDoNotice(param);
      if (result) {
        this.closeModal();
        if (!this.optionFlg) this.loadCircularList();
        this.addLogOperation({action: 'portal-update-to-do-notice-config', result: 0})
      } else {
        this.addLogOperation({action: 'portal-update-to-do-notice-config', result: 0})
      }
    },
    async loadSchedulerList(updateData = null) {
      const scheduler_id = updateData && updateData.scheduler_id ? updateData.scheduler_id : 0;
      const param = {
        type: this.currentType,
        tokenGroupware: this.tokenGroupware,
        shared: this.checkSharedScheduler,
      }
      let data = await this.getToDoPublicSchedulerList(param);
      if (data && data.data) {
        let schedulerList = Object.assign([], data.data);
        if (!this.checkSharedScheduler) {
          let filter_flg = false;
          schedulerList = schedulerList.filter((item) => {
            if (!filter_flg && item.name === 'Ｍｙスケジューラ') {
              filter_flg = true;
              return item;
            }
          })
        }
        if (updateData && scheduler_id > 0) {
          const title = updateData.scheduler_title;
          let has = false;
          schedulerList.map((item) => {
            if (item.id === scheduler_id) {
              has = true;
            }
          })
          if (!has && title && typeof title === 'string' && title !== '') {
            const id = scheduler_id;
            updateData.scheduler_id = title;
            this.update_scheduler_id = id;
          }
        }
        this.schedulerList = Object.assign([], schedulerList);
      }
    },
    closeModal() {
      this.delTaskModal = false;
      this.delToDoModal = false;
      this.updateToDoModal = false;
      this.addUpdateTaskModal = false;
      this.updateCircularTaskModal = false;
      this.settingNoticeConfigModal = false;
      this.doneTaskModal = false;
      this.revokeTaskModal = false;
      $('.flatpickr-calendar.hasTime').removeClass('open');
      setTimeout(() => {
        this.clearUpdateData();
      }, 100)
    },
    clearUpdateData() {
      this.$validator.reset();
      this.sub_task = [];
      this.sub_title_input = '';
      this.updateToDoData = {id: 0, title: '', type: 1,};
      this.updateTaskData = {
        id: 0,
        parent_id: 0,
        title: '',
        content: '',
        deadline: '',
        important: 0,
        scheduler_id: 0,
        scheduler_title: ''
      };
      this.updateCircularTaskData = {
        id: 0,
        circular_user_id: 0,
        title: '',
        content: '',
        deadline: '',
        important: 0,
        scheduler_id: 0,
        scheduler_title: ''
      };
      this.noticeConfigData = {email_flg: 0, notice_flg: 0, state: 0, advance_time: ''};
      this.schedulerList = [];
      this.update_scheduler_id = 0;
      this.update_group_id = 0;
    },
    clearCircularData() {
      this.orderBy = "update_at";
      this.orderDir = "desc";
      this.circularList = [];
      this.circularPagination = {totalPage: 0, currentPage: 1, limit: 10, totalItem: 0, from: 1, to: 10};
    },
    getConfig() {
      let dateObj = new Date();
      let minDate = this.$moment(dateObj).format('YYYY-MM-DD HH:mm:ss');
      let defaultHour = this.$moment(dateObj).format('HH');
      let defaultMinute = this.$moment(dateObj).format('mm');
      this.picker_config.minDate = minDate;
      this.picker_config.defaultMinute = defaultMinute;
      this.picker_config.defaultHour = defaultHour;
    },
    addSubTaskDefault(e) {
      if (e.target.value) {
        this.sub_task.push(e.target.value);
        this.sub_title_input = '';
      }
    },
    initDate(class_name, input_name, updateData) {
      let input_obj = $(`.calendar-box.${class_name} input[name=${input_name}]`);
      if (updateData.deadline && input_obj.val() === '') {
        input_obj.val(updateData.deadline);
      }
    },
    initPage(type) {
      let page = null;
      switch (type) {
        case 'list':
          page = this.pagination;
          this.toDoList = [];
          break;
        case 'task':
          page = this.taskPagination;
          this.toDoTaskList = [];
          break;
        case 'doneTask':
          page = this.doneTaskPagination;
          this.doneTaskList = [];
          break;
      }
      if (page) {
        page.totalPage = 0;
        page.currentPage = 1;
        page.limit = page.defaultLimit;
        page.totalItem = 0;
        page.from = 1;
        page.to = 5;
      }
    },
    setViewPage(page) {
      this.viewPage = page;
    }
  },
  watch: {
    'circularPagination.currentPage': function (val) {
      if (this.currentType === 1 && this.selectedId === 0) {
        if (!this.optionFlg) this.loadCircularList();
      }
    },
  },
}
</script>

<style lang="scss">
.to-do-setting {
  .config-button {
    padding: 8px 50px;
    white-space: nowrap;
  }
}
</style>