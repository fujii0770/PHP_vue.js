<template>
  <div class="task" :class="isIE ? 'IE' : ''">
    <vs-row vs-w="12" class="top-action">
      <vs-col vs-w="12" class="to-do-title">
        <h2>{{ title }}</h2>
      </vs-col>
      <vs-col vs-w="12" class="to-do-notice">
        <vs-dropdown vs-custom-content vs-trigger-click class="cursor-pointer">
          <vs-icon size="30px">personal</vs-icon>
          <vs-dropdown-menu class="to-do-setting">
            <ul>
              <li class="config-button cursor-pointer" @click="$emit('setViewPage', 'group')"><span>グループ設定</span></li>
              <li class="config-button cursor-pointer" @click="$emit('showModal', 'settingNoticeConfigModal')"><span>通知設定</span></li>
            </ul>
          </vs-dropdown-menu>
        </vs-dropdown>
      </vs-col>
      <button class="add-task" @click="$emit('showModal', 'addUpdateTaskModal')">
        <i class="fa fa-plus" aria-hidden="true"></i>
      </button>
    </vs-row>
    <split-pane :min-percent='0' :default-percent='75' split="horizontal">
      <template slot="paneL">
        <vs-card class="task-list">
          <div class="sort-box">
            <div class="item pr-6">
              <span>期限日</span>
              <div class="sort-icon cursor-pointer" @click="taskSort('deadline', getSortDir('task', 'deadline'))">
                <div class="icon up" :class="orderBy === 'deadline' && orderDir === 'ASC' ? 'active' : ''">
                  <i class="fa fa-sort-up"></i>
                </div>
                <div class="icon down" :class="orderBy === 'deadline' && orderDir === 'DESC' ? 'active' : ''">
                  <i class="fa fa-sort-down"></i>
                </div>
              </div>
            </div>
            <div class="item">
              <span>優先度</span>
              <div class="sort-icon cursor-pointer" @click="taskSort('important', getSortDir('task', 'important'))">
                <div class="icon up" :class="orderBy === 'important' && orderDir === 'ASC' ? 'active' : ''">
                  <i class="fa fa-sort-up"></i>
                </div>
                <div class="icon down" :class="orderBy === 'important' && orderDir === 'DESC' ? 'active' : ''">
                  <i class="fa fa-sort-down"></i>
                </div>
              </div>
            </div>
          </div>
          <div v-for="task in tasks" class="task-item" :key="task.id">
            <div class="task-title">
              <button class="done-task" @click="$emit('showModal', 'doneTaskModal', task)">
                <i class="fa fa-check" aria-hidden="true"></i>
              </button>
              <div class="cursor-pointer" @click="$emit('showModal', 'addUpdateTaskModal', task)">
                <span>{{ task.title }}</span>
                <span v-if="task.content">{{ task.content }}</span>
              </div>
            </div>
            <div class="information cursor-pointer" @click="$emit('showModal', 'addUpdateTaskModal', task)">
              <div class="task-info" v-if="task.deadline">{{ task.deadline | moment("YYYY/MM/DD HH:mm") }}</div>
              <div class="task-info" v-if="getImportantText(task.important)"
                    :style="'color:' + getImportantColor(task.important)">{{ getImportantText(task.important) }}
              </div>
            </div>

            <div class="task-child" v-if="task.child_task && task.child_task.length > 0">
              <div v-for="task_child in task.child_task" class="task-child-item" :key="task_child.id">
                <div class="task-title">
                  <button class="done-task" @click="$emit('showModal', 'doneTaskModal', task_child)">
                    <i class="fa fa-check" aria-hidden="true"></i>
                  </button>
                  <div class="cursor-pointer" @click="$emit('showModal', 'addUpdateTaskModal', task_child)">
                    <span>{{ task_child.title }}</span>
                    <span v-if="task_child.content">{{ task_child.content }}</span>
                  </div>
                </div>
                <div class="information cursor-pointer" @click="$emit('showModal', 'addUpdateTaskModal', task_child)">
                  <div class="task-info" v-if="task_child.deadline">
                    {{ task_child.deadline | moment("YYYY/MM/DD HH:mm") }}
                  </div>
                  <div class="task-info" v-if="getImportantText(task_child.important)"
                        :style="'color:' + getImportantColor(task_child.important)">
                    {{ getImportantText(task_child.important) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </vs-card>
        <button v-if="hasMore" class="show-more cursor-pointer" @click="$emit('showMore', 'task')">
          <i class="fa fa-chevron-down" aria-hidden="true"></i>
        </button>
      </template>
    <!--  Done List  -->
      <template slot="paneR">
        <vs-row vs-w="12" class="top-action done" :class="doneListShow ? 'show' : 'close'">
          <vs-col vs-w="12" class="to-do-title">
            <h3>完了タスク
              <span v-show="doneListShow" @click="$emit('hideDoneList')">
                <i class="fa fa-sort-up cursor-pointer"></i>
              </span>
              <span v-show="!doneListShow" @click="$emit('showDoneList')">
                <i class="fa fa-sort-down cursor-pointer"></i>
              </span>
            </h3>
          </vs-col>
        </vs-row>
        <vs-card class="task-list done">
          <template v-if="doneListShow">
            <div class="sort-box">
              <div class="item pr-6">
                <span>期限日</span>
                <div class="sort-icon cursor-pointer" @click="doneTaskSort('deadline', getSortDir('doneTask', 'deadline'))">
                  <div class="icon up" :class="doneOrderBy === 'deadline' && doneOrderDir === 'ASC' ? 'active' : ''">
                    <i class="fa fa-sort-up"></i>
                  </div>
                  <div class="icon down" :class="doneOrderBy === 'deadline' && doneOrderDir === 'DESC' ? 'active' : ''">
                    <i class="fa fa-sort-down"></i>
                  </div>
                </div>
              </div>
              <div class="item pr-6">
                <span>優先度</span>
                <div class="sort-icon cursor-pointer" @click="doneTaskSort('important', getSortDir('doneTask', 'important'))">
                  <div class="icon up" :class="doneOrderBy === 'important' && doneOrderDir === 'ASC' ? 'active' : ''">
                    <i class="fa fa-sort-up"></i>
                  </div>
                  <div class="icon down" :class="doneOrderBy === 'important' && doneOrderDir === 'DESC' ? 'active' : ''">
                    <i class="fa fa-sort-down"></i>
                  </div>
                </div>
              </div>
              <div class="item">
                <span>完了日</span>
                <div class="sort-icon cursor-pointer" @click="doneTaskSort('updated_at', getSortDir('doneTask', 'updated_at'))">
                  <div class="icon up" :class="doneOrderBy === 'updated_at' && doneOrderDir === 'ASC' ? 'active' : ''">
                    <i class="fa fa-sort-up"></i>
                  </div>
                  <div class="icon down" :class="doneOrderBy === 'updated_at' && doneOrderDir === 'DESC' ? 'active' : ''">
                    <i class="fa fa-sort-down"></i>
                  </div>
                </div>
              </div>
            </div>
            <div v-for="task in doneTasks" class="task-item" :key="task.id">
              <div class="task-title">
                <button class="done-task" @click="$emit('showModal', 'revokeTaskModal', task)">
                  <img class="icon" :src="require('@assets/images/pages/portal/revoke.svg')" alt="revoke">
                </button>
                <div class="cursor-pointer" @click="$emit('showModal', 'addUpdateTaskModal', task)">
                  <span>{{ task.title }}</span>
                  <span v-if="task.content">{{ task.content }}</span>
                </div>
              </div>
              <div class="task-child" v-if="task.child_task && task.child_task.length > 0">
                <div v-for="task_child in task.child_task" class="task-child-item" :key="task_child.id">
                  <div class="task-title">
                    <button class="done-task" @click="$emit('showModal', 'revokeTaskModal', task_child)">
                      <img class="icon" :src="require('@assets/images/pages/portal/revoke.svg')" alt="revoke">
                    </button>
                    <div class="cursor-pointer" @click="$emit('showModal', 'addUpdateTaskModal', task_child)">
                      <span>{{ task_child.title }}</span>
                      <span v-if="task_child.content">{{ task_child.content }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </vs-card>
        <button v-if="doneHasMore && doneListShow" class="show-more cursor-pointer" @click="$emit('showMore', 'doneTask')">
          <i class="fa fa-chevron-down" aria-hidden="true"></i>
        </button>
      </template>
    </split-pane>
  </div>
</template>

<script>

import {mapState, mapActions} from "vuex";
import splitPane from 'vue-splitpane';

export default {
  components: {
    splitPane,
  },
  name: "Task",
  props: {
    title: {
      type: String,
      default: () => '',
    },
    tasks: {
      type: Array,
      default: () => [],
    },
    doneTasks: {
      type: Array,
      default: () => [],
    },
    importantList: {
      type: Array,
      default: () => [],
    },
    hasMore: {
      type: Boolean,
      default: false,
    },
    doneHasMore: {
      type: Boolean,
      default: false,
    },
    orderBy: {
      type: String,
      default: () => 'update_at',
    },
    orderDir: {
      type: String,
      default: () => 'DESC',
    },
    doneOrderBy: {
      type: String,
      default: () => 'update_at',
    },
    doneOrderDir: {
      type: String,
      default: () => 'DESC',
    },
    doneListShow: {
      type: Boolean,
      default: false,
    }
  },
  data() {
    return {
      importantColor: ['', '#0000FF', '#FFC000', '#FF0000'],
    }
  },
  computed: {
    getImportantText() {
      return (important) => {
        let text = '';
        this.importantList.filter((item) => {
          if (important && item.id === important) {
            text = item.text;
          }
        })
        return text;
      }
    },
    getImportantColor() {
      return (important) => {
        return this.importantColor[important] ? this.importantColor[important] : '';
      }
    },
    isIE(){
      return window && window.navigator && window.navigator.userAgent && (window.navigator.userAgent.indexOf('Trident') > -1 && window.navigator.userAgent.indexOf('rv:11.0') > -1)
    },
    getSortDir() {
      return function (type, order_by) {
        let orderBy = '';
        let orderDir = '';
        switch (type) {
          case 'task':
            orderBy = this.orderBy;
            orderDir = this.orderDir;
            break;
          case 'doneTask':
            orderBy = this.doneOrderBy;
            orderDir = this.doneOrderDir;
            break;
          default:
            break;
        }
        return orderBy === order_by && orderDir === 'ASC' ? 'DESC' : 'ASC';
      }
    },
  },
  methods: {
    ...mapActions({}),
    taskSort: function (key, type) {
      this.$emit('loadTaskList', {
        orderBy: key,
        orderDir: type
      }, true);
    },
    doneTaskSort: function (key, type) {
      this.$emit('loadDoneTaskList', {
        orderBy: key,
        orderDir: type
      }, true);
    },
  },
  watch: {}
}
</script>

<style scoped>

</style>