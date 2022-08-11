import router from "../../router";
import applicationService from "../../services/application.service"
import circularUserService from "../../services/circular_user.service"
import store from '../store';

const state = {
  departmentUsers: [],
  companyUsers: [],
  selectUserChange: false,
  loadDepartmentUsersSuccess: false,
  commentTitle: '',
  commentContent: '',
  selectUserView: [],
  checkOperationNotice: false,
  selectTemplateUserChange: false,
  getUserView: [],
  requirePrint:null, // PAC_5-2245
};

const actions = {
  getDepartmentUsers({ dispatch, commit }, options) {
    applicationService.getDepartmentUsers(options).then(
      response => {
        if(!response) return;
        commit('getDepartmentUsersSuccess', response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
      }
    );
  },
  getDepartmentUsersWithOption({ dispatch, commit }, options) {
    applicationService.getDepartmentUsersWithOption(options).then(
      response => {
        if(!response) return;
        commit('getDepartmentUsersSuccess', response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
      }
    );
  },
  saveCircularSetting({ dispatch, commit }, data){
    return applicationService.saveCircularSetting(data).then(
      response => {
        if(!response) return;
        return Promise.resolve(true);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  getCircularSetting({ dispatch, commit }) {
    return applicationService.getCircularSetting().then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  },
  sendNotifyFirst({ dispatch, commit }, data) {
    if(store.state.home.checkSentCircular){
       return applicationService.signatureCircular().then(
         response => {
            if(!response) return;
            if(data.isSendAllUser && data.isSendAllUser == true){
                return applicationService.sendAllUserFirst(data).then(
                    response => {
                        commit('updateCommentTitle', '');
                        commit('updateCommentContent', '');
                        commit('updateListUserView', []);
                        commit('updateRequirePrint', null); // PAC_5-2245
                        if(!response) return;
                        commit('updateShowOperationNoticeFlg', true);
                        dispatch("alertSuccess", response.message, { root: true });
                        return Promise.resolve(true);
                    },
                    error => {
                        commit('updateShowOperationNoticeFlg', false);
                        dispatch("alertError", error, { root: true });
                        return Promise.resolve(false);
                    }
                );
            }
        
            return applicationService.sendNotifyFirst(data).then(
               response => {
                 commit('updateCommentTitle', '');
                 commit('updateCommentContent', '');
                 commit('updateListUserView', []);
                 commit('updateRequirePrint', null); // PAC_5-2245
                 if(!response) return;
                 commit('updateShowOperationNoticeFlg', true);
                 dispatch("alertSuccess", response.message, { root: true });
                 return Promise.resolve(true);
               },
               error => {
                 commit('updateShowOperationNoticeFlg', false);
                 dispatch("alertError", error, { root: true });
                 return Promise.resolve(false);
               }
             );
         },
         error => {
             dispatch("alertError", error, { root: true });
             return Promise.resolve(false);
         }
       );
    }else{
        // PAC_5-2353 START
        if(data.isSendAllUser && data.isSendAllUser == true){
            return applicationService.sendAllUserFirst(data).then(
                response => {
                    commit('updateCommentTitle', '');
                    commit('updateCommentContent', '');
                    commit('updateListUserView', []);
                    commit('updateRequirePrint', null); // PAC_5-2245
                    if(!response) return;
                    commit('updateShowOperationNoticeFlg', true);
                    dispatch("alertSuccess", response.message, { root: true });
                    return Promise.resolve(true);
                },
                error => {
                    commit('updateShowOperationNoticeFlg', false);
                    dispatch("alertError", error, { root: true });
                    return Promise.resolve(false);
                }
            );
        } 
        // PAC_5-2353 END  
      return applicationService.sendNotifyFirst(data).then(
        response => {
          commit('updateCommentTitle', '');
          commit('updateCommentContent', '');
          commit('updateListUserView', []);
          commit('updateRequirePrint', null); // PAC_5-2245
          if(!response) return;
          commit('updateShowOperationNoticeFlg', true);
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(true);
        },
        error => {
          commit('updateShowOperationNoticeFlg', false);
          dispatch("alertError", error, { root: true });
          return Promise.resolve(false);
        }
      );
    }
  },
  sendNotifyContinue({ dispatch, commit }, data) {
    return applicationService.sendNotifyContinue(data).then(
      response => {
        if(!response) return;
        commit('updateShowOperationNoticeFlg', true);
        return Promise.resolve(response.data);
      },
      error => {
        commit('updateShowOperationNoticeFlg', false);
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },  
  updateOperationNotice({ dispatch, commit }, data) {
    return applicationService.updateOperationNotice(data).then(
      response => {
        if(!response) return;
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(true);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  addCircularUsers({ dispatch, commit }, data) {
    return circularUserService.adds(data).then(
      response => {
        if(!response) return;
      //  commit("contacts/notifyChangedPhoneBook", null,{ root: true });
        commit("home/addCircularUserSuccess", response.data, { root: true });
        commit("setSelectUserChange");
          commit("setSelectTemplateUserChange");
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  addChildCircularUser({ dispatch, commit }, data) {
    return circularUserService.addChild(data).then(
      response => {
        if(!response) return;
        commit("home/addChildCircularUserSuccess", response.data, { root: true });
        commit("setSelectUserChange");
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  removeCircularUser({ dispatch, commit }, circular_user_id) {
    return circularUserService.remove(circular_user_id).then(
      response => {
        commit("home/removeCircularUserSuccess", response.data, { root: true });
        commit("setSelectUserChange");
        return Promise.resolve(true);
      },
      error => {
        commit("home/removeCircularUserSuccess", circular_user_id, { root: true });
        commit("setSelectUserChange");
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  clearCircularUsers({ dispatch, commit }, circular_id) {
    return circularUserService.clear(circular_id).then(
      response => {
        if(!response) return;
        commit("home/clearCircularUsersSuccess", null, { root: true });
        commit("setSelectUserChange");
        commit("setSelectTemplateUserChange");
        return Promise.resolve(true);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  updateCircularUser({ dispatch, commit }, circular_user) {
    circularUserService.update(circular_user).then(
      response => {
        commit("home/updateCircularUserSuccess", response.data, { root: true });
        commit("setSelectUserChange");
      },
      error => {
        dispatch("alertError", error, { root: true });
      }
    );
  },
  sendMailViewed({ dispatch, commit }, data) {
    commit('circulars/getUnreadTotalSuccess', -1, { root: true })
    circularUserService.sendMailViewed(data).then(
      response => {

      },
      error => {
        dispatch("alertError", error, { root: true });
      }
    );
  },
  sendBack({ dispatch, commit }, data) {
    return circularUserService.sendBack(data).then(
      response => {
        if(!response) return;
        dispatch("alertSuccess", response.message, { root: true });
        return Promise.resolve(true);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
    autoStorageBox({ dispatch, commit }, data) {
        return applicationService.autoStorageBox(data).then(
            response => {
                if(!response) return;
                return Promise.resolve(true);
            },
            error => {
                return Promise.resolve(false);
            }
        );
    },
};

const mutations = {
    getDepartmentUsersSuccess(state, data) {
      state.departmentUsers = [];
      data = data?data:[];
      state.departmentUsers.push(...data);
      state.loadDepartmentUsersSuccess = !state.loadDepartmentUsersSuccess;
    },
    setSelectUserChange(state) {
      state.selectUserChange = !state.selectUserChange;
    },
    updateCommentTitle(state, value) {
      state.commentTitle = value;
    },    
    updateCommentContent(state, value) {
      state.commentContent = value;
    },
    addUserView(state, value) {
      state.selectUserView.push(value);
    },
    updateListUserView(state, value){
      state.selectUserView = value;
    },    
    updateShowOperationNoticeFlg(state, value){
      state.checkOperationNotice = value;
    },
    setSelectTemplateUserChange(state) {
        state.selectTemplateUserChange = !state.selectTemplateUserChange;
    },
    getUserView(state, value) {
      state.getUserView.push(value);
      
    },
    updategetUserView(state, value){
      state.getUserView = value;
    }, 
    // PAC_5-2245 Start
    updateRequirePrint(state, value) {
        state.requirePrint = value;
    },
    // PAC_5-2245 End
};

export const application = {
  namespaced: true,
  state,
  actions,
  mutations
};