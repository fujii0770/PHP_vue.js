<template>
  <div class="circular">
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
    </vs-row>
    <vs-card class="circular-list">
      <vs-table class="mt-3 table-favorite-width"
                :data="circulars"
                noDataText="データがありません。"
                sst stripe
                @selected="updateCircularTask"
                @sort="handleSort">
        <template slot="thead">
          <vs-th sort-key="title" class="tex-list-received pr-3">文書名</vs-th>
          <vs-th sort-key="A.email" class="tex-list-received">差出人</vs-th>
          <vs-th sort-key="update_at" class="tex-list-received width-date">受信日時</vs-th>
          <vs-th sort-key="U.circular_status" class="tex-list-received">回覧状况</vs-th>
          <vs-th sort-key="tdct.deadline" class="tex-list-received width-date">期限日</vs-th>
          <vs-th sort-key="tdct.important" class="tex-list-received">重要度</vs-th>
        </template>

        <template slot-scope="{data}">
          <vs-tr :data="tr" :key="tr.circular_user_id" v-for="tr in data">
            <vs-td class="max-width-200">
              {{ tr.title }}
            </vs-td>
            <vs-td class="max-width-200">
              <div v-html="tr.email"></div>
            </vs-td>
            <vs-td>
              {{ tr.update_at | moment("YYYY/MM/DD HH:mm") }}
            </vs-td>
            <vs-td>
              {{ tr.is_skip ? "スキップ(手動)" : getTrStatus(tr, false) }}
            </vs-td>
            <vs-td>
              {{ tr.deadline | moment("YYYY/MM/DD HH:mm") }}
            </vs-td>
            <vs-td :style="'color:' + getImportantColor(tr.important)">
              {{ getImportantText(tr.important) }}
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
      <div>
        <div class="mt-3 mb-5 whitespace-no-wrap">{{ pagination.totalItem }} 件中 {{ pagination.from }} 件から
          {{ pagination.to }} 件までを表示
        </div>
      </div>
      <vx-pagination :total="pagination.totalPage"
                     :currentPage.sync="pagination.currentPage"></vx-pagination>
    </vs-card>
  </div>
</template>

<script>

import {mapState, mapActions} from "vuex";
import {CIRCULAR_USER} from '../../../enums/circular_user';
import {CIRCULAR} from '../../../enums/circular';

export default {
  components: {
    VxPagination: () => import('@/components/vx-pagination/VxPagination.vue'),
  },
  name: "Circular",
  props: {
    title: {
      type: String,
      default: () => '',
    },
    circulars: {
      type: Array,
      default: () => [],
    },
    importantList: {
      type: Array,
      default: () => [],
    },
    pagination: {
      type: Object,
      default: () => {
        return {
          totalPage: 0,
          currentPage: 1,
          limit: 10,
          totalItem: 0,
          from: 1,
          to: 10
        }
      },
    },
  },
  data() {
    return {
      options_status: ['', '通知済/未読', '既読', '承認(捺印あり)', '承認(捺印なし)', '差戻し(既読)', '差戻し(未読)', '', '引戻し', '', '既読'],
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
  },
  methods: {
    ...mapActions({}),
    handleSort(key, active) {
      const orderBy = key;
      const orderDir = active ? "DESC" : "ASC";
      this.$emit('loadCircularList', {orderBy: orderBy, orderDir: orderDir})
    },
    getTrStatus(tr, type) {
      if (type && tr.circular_status == CIRCULAR_USER.READ_STATUS)
        return '回覧中';
      if (tr.hasRequestSendBack) {
        if (tr.circular_status == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS)
          return '差戻し依頼(未読)';
        if (tr.circular_status == CIRCULAR_USER.READ_STATUS)
          return '差戻し依頼(既読)';
        return '差戻し依頼'
      }
      if (tr.status == CIRCULAR.SEND_BACK_STATUS && tr.circular_status == CIRCULAR_USER.NOTIFIED_UNREAD_STATUS)
        return '差戻し(未読)';
      if (tr.status == CIRCULAR.SEND_BACK_STATUS && tr.circular_status == CIRCULAR_USER.READ_STATUS)
        return '差戻し(既読)';
      //PAC_5-2250 S
      if ((tr.status == CIRCULAR.SEND_BACK_STATUS || tr.status == CIRCULAR.CIRCULATING_STATUS) && tr.circular_status == CIRCULAR_USER.NODE_COMPLETED_STATUS) {
        return 'スキップ';
      }
      //PAC_5-2250 S
      return this.options_status[tr.circular_status];
    },
    updateCircularTask(tr) {
      let data = Object.assign({}, tr);
      data.id = data.task_id ? data.task_id : 0;
      data.content = data.content ? data.content : '';
      data.important = data.important ? data.important : 0;
      data.schedule_id = data.important ? data.schedule_id : 0;
      this.$emit('showModal', 'updateCircularTaskModal', data);
    },
  },
  watch: {}
}
</script>

<style scoped>

</style>