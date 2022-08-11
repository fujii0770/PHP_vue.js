import groupwareService from "../../services/groupware.service";
// import Vue from 'vue'
// import VueCookies from 'vue-cookies'
// Vue.use(VueCookies)

const state = {
  listNoticeGroupware: [],
  checkBulletinBoardApp: false,
  checkCalendarApp: false,
  checkTimeCardApp: false,
  checkCaldavApp: false,
  checkToDoList: false,
  checkSharedScheduler: false,
  checkFileMailApp: false,
  checkAddressListApp: false,
  /*PAC_5-2376 S*/
  boardPermission: {
      category_append: 0,
      topics_append: 0
  },
  /*PAC_5-2376 E*/
  checkFaqBulletinBoardApp: false,  
  /*PAC_5-2376 E*/
  myCompany: null,
};

const actions = {
    getTokenGroupware({ dispatch, commit }, data) {
      return groupwareService.getTokenGroupware(data).then(
          response => {
              return Promise.resolve(response);
          },
          error => {
              return Promise.resolve(false);
          }
      );
    },

    getUserAppUsageStatus({ dispatch, commit }, tokenGroupware) {
        return groupwareService.getUserAppUsageStatus(tokenGroupware).then(
            response => {
                return Promise.resolve(response);
            },
            error => {
              if(error.status != 403){
                dispatch("alertError", error.data.message, { root: true });
              }
                return Promise.resolve(false);
            }
        );
    },

    getUnreadNoticeGroupware({ dispatch, commit },tokenGroupware) {
        return groupwareService.getUnreadNoticeGroupware(tokenGroupware).then(
          response => {
            return Promise.resolve(response);
          },
          error => {
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.resolve(error);
          }
        );
    },

    getListNoticeGroupware({ dispatch, commit },data) {
        return groupwareService.getListNoticeGroupware(data).then(
          response => {
            return Promise.resolve(response);
          },
          error => {
            return Promise.resolve(false);
          }
        );
    },

    markAllReadNoticeGroupware({ dispatch, commit },tokenGroupware) {
        return groupwareService.markAllReadNoticeGroupware(tokenGroupware).then(
          response => {
            return Promise.resolve(response);
          },
          error => {
            return Promise.resolve(false);
          }
        );
    },

    markReadNoticeGroupware({ dispatch, commit },data) {
        return groupwareService.markReadNoticeGroupware(data).then(
          response => {
            return Promise.resolve(response);
          },
          error => {
            return Promise.resolve(false);
          }
        );
    },

    updateRefreshToken({ dispatch, commit },refreshToken) {
        return groupwareService.updateRefreshToken(refreshToken).then(
          response => {
            return Promise.resolve(response);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
          }
        );
    },

    getAppRole({ dispatch, commit }, info) {
      return groupwareService.getAppRole(info).then(
          response => {
              return Promise.resolve(response.data);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.resolve(false);
          }
      );
  },

};

const mutations = {

  updateListNoticeGroupware(state, value) {
    state.listNoticeGroupware = value;
  },

  updateCheckBulletinBoardApp(state, value) {
    state.checkBulletinBoardApp = value;
  },

  updateCheckCalendarApp(state, value) {
    state.checkCalendarApp = value;
  },

  updateCheckTimeCardApp(state, value) {
      state.checkTimeCardApp = value;
  },

  updateCheckCaldavApp(state, value) {
    state.checkCaldavApp = value;
  },

  updateMyCompany(state, value) {
    state.myCompany = value;
  },
  updateCheckFileMailApp(state, value) {
      state.checkFileMailApp = value;
  },
  updateCheckAddressListApp(state, value) {
      state.checkAddressListApp = value;
  },
  /*PAC_5-2376 S*/
  updateBoardPermission(state, value) {
      state.boardPermission = value;
  },
  /*PAC_5-2376 E*/
  /*PAC_5-2648 S*/
  updateCheckFaqBulletinBoardApp(state, value) {
      state.checkFaqBulletinBoardApp = value;
  },
  /*PAC_5-2648 E*/

  updateToDoList(state, value) {
      state.checkToDoList = value;
  },

  updateSharedScheduler(state, value) {
      state.checkSharedScheduler = value;
  },
};

export const groupware = {
    namespaced: true,
    state,
    actions,
    mutations
};
