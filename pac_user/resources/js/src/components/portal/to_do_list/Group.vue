<template>
  <div class="to-do-group">
    <div class="top-action">
      <button class="back" @click="$emit('setViewPage', 'task_list')">戻る</button>
    </div>
    <button class="add-group" @click="showModal('addUpdateGroupModal')">グループを追加する</button>
    <vs-table class="mt-3 table-favorite-width"
              :data="groupList"
              noDataText="データがありません。"
              sst stripe
              @selected="updateGroupShow">
      <template slot="thead">
        <vs-th class="pr-3">グループ名</vs-th>
        <vs-th>所属メンバー</vs-th>
      </template>

      <template slot-scope="{data}">
        <vs-tr :data="tr" :key="tr.id" v-for="(tr) in data">
          <vs-td class="max-width-200">
            {{ tr.title }}
          </vs-td>
          <vs-td class="max-width-200">
            <span v-for="(item, index) in tr.group" class="auth-item" :key="index">{{ item }}</span>
            <span class="auth-num" v-if="tr.num > 0">{{ tr.num }}+</span>
          </vs-td>
        </vs-tr>
      </template>
    </vs-table>

    <!--  popup  -->
    <vs-popup title="グループの削除" :active.sync="delGroupModal">
      <div>
        <vs-row class="mt-3">
          <vs-col vs-type="flex" vs-w="8" class="max-width-360">このグループを削除してもよろしいですか？</vs-col>
        </vs-row>
      </div>
      <vs-row class="mt-3">
        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
          <vs-button @click="delGroup" color="warning">はい</vs-button>
          <vs-button @click="delGroupModal = false" color="dark" type="border">いいえ</vs-button>
        </vs-col>
      </vs-row>
    </vs-popup>

    <vs-popup class="to-do-list group modal-item" :class="isMypage ? 'mypage' : ''" title="" :active.sync="addUpdateGroupModal">
      <header class="modal-header">
        <div @click.stop="closeModal">
          <h2>グループ追加</h2>
          <vs-icon class="v-icon cursor-pointer close">close</vs-icon>
        </div>
      </header>

      <vs-card>
        <form onsubmit="return false;">
          <vs-row class="mt-3">
            <vs-col vs-w="3" class="text-left pr-3 pt-2 label">グループ名　<span class="text-red">*</span></vs-col>
            <vs-col vs-type="" vs-w="9">
              <vs-input placeholder="グループ名を入力" v-validate="'required'" v-model="updateData.title" required
                        name="title" class="title w-full" maxlength="50" @keyup.enter="()=>{return false;}"/>
              <span class="text-danger text-sm" v-show="errors.has('title')">{{ errors.first('title') }}</span>
            </vs-col>
          </vs-row>
          <vs-row class="mt-3">
            <vs-col vs-w="3" class="text-left pr-3 pt-2 label">所属メンバー　<span class="text-red">*</span></vs-col>
            <vs-col vs-type="" vs-w="9">
              <cascader
                ref="authGroupSelector"
                v-model="updateData.auth_group"
                placeholder="所属メンバーを選択"
                :options="options"
                :props="{ multiple: true, emitPath: false, expandTrigger: 'hover' }"
                size="medium"
                :show-all-levels="false"
                filterable
                clearable
                name="auth_group"
                v-validate="'required'"
                @expand-change="expandChange"
                @visible-change="visibleChange"
              ></cascader>
              <span class="text-danger text-sm" v-show="errors.has('auth_group')">{{ errors.first('auth_group') }}</span>
              <span class="text-danger text-sm" v-show="!hasSelf"><br/>自分自身を含めてください。</span>
            </vs-col>
          </vs-row>
        </form>
        <vs-row class="pt-3 pb-2 modal-action" vs-type="flex" vs-justify="flex-end" vs-align="center">
          <vs-button v-show="!updateData.id" color="success" @click="addGroup()"><i class="fas fa-plus-circle"></i> 登録</vs-button>
          <vs-button v-show="updateData.id" :class="'update-btn'" color="success" @click="updateGroup">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                 stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            更新
          </vs-button>
          <vs-button v-if="updateData.id" color="danger" @click="delGroupModal = true"><i class="fas fa-trash-alt"></i> 削除</vs-button>
        </vs-row>
      </vs-card>
    </vs-popup>
  </div>
</template>

<script>

import Vue from 'vue';
import {Validator} from 'vee-validate';
import {mapState, mapActions} from "vuex";
import { Cascader } from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';

const dict = {
  custom: {
    title: {
      required: '* 必須項目です',
      max: '* 50文字以上は入力できません。',
    },
    auth_group: {
      required: '* 必須項目です',
    },
  }
};
Validator.localize('ja', dict);
export default {
  name: "Group",
  components: {
    Cascader
  },
  props: {
    isMypage: {
      type: Boolean,
      default: false
    },
  },
  data() {
    return {
      groupList: [],
      updateData: {id: 0, title: '', auth_group: []},
      options: [],
      loginUser: JSON.parse(getLS('user')),
      addUpdateGroupModal: false,
      delGroupModal: false,
    }
  },
  computed: {
    hasSelf: {
      get() {
        return Object.assign([], this.updateData.auth_group).includes('users_' + this.loginUser.id);
      }
    },
    isIE(){
      return window && window.navigator && window.navigator.userAgent && (window.navigator.userAgent.indexOf('Trident') > -1 && window.navigator.userAgent.indexOf('rv:11.0') > -1)
    },
  },
  async created() {
    if (this.isIE) {
      // IE表示異常問題の処理
      this.$nextTick(() => {
        setTimeout(() => {
          $('.el-popper.el-cascader__dropdown').hide();
        }, 500);
      })
    }
    let users_data = await this.getToDoUsers();
    if (users_data && users_data.data && users_data.data.length) {
      users_data = Object.assign([], users_data.data);
      users_data.map((item)=> {
        item.label = item.username;
        item.value = 'users_' + item.id;
        return item;
      })
      this.options.push({label: '利用者一覧', value: 'users', children: [...users_data]});
    }

    let department_data = await this.getToDoDepartment();
    if (department_data && department_data.data && department_data.data.length) {
      department_data = Object.assign([], department_data.data);
      this.traverseDepartmentTree(department_data);
      this.options.push({label: '部署一覧', value: 'department', children: [...department_data]});
    }
  },
  mounted() {
    this.loadGroupList();
  },
  methods: {
    ...mapActions({
      addLogOperation: "logOperation/addLog",
      getToDoGroup: 'portal/getToDoGroup',
      addToDoGroup: 'portal/addToDoGroup',
      updateToDoGroup: 'portal/updateToDoGroup',
      deleteToDoGroup: 'portal/deleteToDoGroup',
      getToDoGroupDetail: 'portal/getToDoGroupDetail',
      getToDoDepartment: 'portal/getToDoDepartment',
      getToDoUsers: 'portal/getToDoUsers',
    }),
    async loadGroupList() {
      const data = await this.getToDoGroup();
      if (data && data.data) {
        let groupList = Object.assign([], data.data);
        this.groupList = groupList.map((item)=>{
          item.group = [];
          if (item.name) item.group = item.name.split(',');
          item.num = item.num - item.group.length;
          return item;
        });
      } else {
        this.groupList = [];
      }
    },
    async addGroup() {
      const validate = await this.$validator.validate();
      if (!validate) return;
      let auth_group = Object.assign([], this.updateData.auth_group);
      let users = [];
      let department = [];
      auth_group.map((item)=> {
        if (item.indexOf('users_') !== -1) {
          users.push(item.replace('users_', ''));
        } else if (item.indexOf('department_') !== -1) {
          department.push(item.replace('department_', ''));
        }
      })
      if (!this.hasSelf) {
        users.push(this.loginUser.id);
      }
      const param = {
        title: this.updateData.title,
        users: users,
        department: department,
      };
      let result = await this.addToDoGroup(param);
      if (result) {
        this.closeModal();
        this.loadGroupList();
        this.addLogOperation({action: 'portal-add-to-do-group', result: 0})
      } else {
        this.addLogOperation({action: 'portal-add-to-do-group', result: 1})
      }
    },
    updateGroupShow(tr) {
      let data = Object.assign({}, tr);
      data.id = data.id ? data.id : 0;
      this.showModal('addUpdateGroupModal', data);
    },
    async updateGroup() {
      const validate = await this.$validator.validate();
      if (!validate) return;
      let auth_group = Object.assign([], this.updateData.auth_group);
      let users = [];
      let department = [];
      auth_group.map((item)=> {
        if (item.indexOf('users_') !== -1) {
          users.push(item.replace('users_', ''));
        } else if (item.indexOf('department_') !== -1) {
          users.push(item.replace('department_', ''));
        }
      })
      if (!this.hasSelf) {
        users.push(this.loginUser.id);
      }
      const param = {
        id: this.updateData.id,
        title: this.updateData.title,
        users: users,
        department: department,
      };

      let result = await this.updateToDoGroup(param);
      if (result) {
        this.closeModal();
        this.loadGroupList();
        this.addLogOperation({action: 'portal-update-to-do-group', result: 0})
      } else {
        this.addLogOperation({action: 'portal-update-to-do-group', result: 1})
      }
    },
    async delGroup() {
      let result = await this.deleteToDoGroup(this.updateData.id);
      if (result) {
        this.closeModal();
        this.loadGroupList();
        this.addLogOperation({action: 'portal-del-to-do-group', result: 0})
      } else {
        this.addLogOperation({action: 'portal-del-to-do-group', result: 1})
      }
    },
    async showModal(modal, data = {}) {
      this.clearUpdateData();
      let updateData = Object.assign({}, data);
      switch (modal) {
        case 'addUpdateGroupModal':
          if (updateData && updateData.id) {
            const data = await this.getToDoGroupDetail(updateData.id);
            if (data && data.data) {
              updateData = Object.assign({}, data.data);
              if (updateData.group_auth) {
                updateData.group_auth = Object.assign([], updateData.group_auth);
                updateData.auth_group = [];
                updateData.group_auth.map((item)=> {
                  let auth_type = parseInt(item.auth_type);
                  if (auth_type === 1) {
                    updateData.auth_group.push('department_' + item.auth_department_id);
                  } else if (auth_type === 2){
                    updateData.auth_group.push('users_' + item.auth_user_id);
                  }
                });
              }
            } else {
              this.clearUpdateData();
              await this.loadGroupList(true);
              return;
            }
          } else {
            updateData = {title: '', auth_group: []};
            updateData.auth_group.push('users_' + this.loginUser.id);
          }
          this.updateData = updateData;
          break;
        default:
          break;
      }
      this.$validator.reset();
      this[modal] = true;
    },
    expandChange: function () {
      this.$nextTick(this.bindLabelClick)
    },
    visibleChange: function () {
      this.$nextTick(this.bindLabelClick)
    },
    bindLabelClick() {
      const elements = document.querySelectorAll('.el-cascader-node__label')
      for (let i = 0; i < elements.length; i++) {
        elements[i].onclick = () => {
          elements[i].previousSibling.click()
        }
      }
    },
    closeModal: function () {
      this.addUpdateGroupModal = false;
      this.delGroupModal = false;
      setTimeout(()=> {
        this.clearUpdateData();
      }, 100);
      if (this.isIE) {
        // IE表示異常問題の処理
        setTimeout(()=> {
          $('.el-popper.el-cascader__dropdown').hide();
        }, 500);
      }
    },
    clearUpdateData: function () {
      this.updateData = {id: 0, title: '', auth: []};
    },
    traverseDepartmentTree(nodes) {
      nodes.map((item)=> {
        item.label = item.name
        item.value = 'department_' + item.id
        if (item.children && item.children.length) {
          this.traverseDepartmentTree(item.children)
        }
        return item;
      })
    },
  },
}
</script>

<style scoped>

</style>