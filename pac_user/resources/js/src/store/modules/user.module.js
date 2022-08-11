import userService from "../../services/user.service";
import config from "../../app.config";
import Axios from "axios/index";

const state = {
    info: null,
    enableSelector: false // unimplemented
};

const actions = {
    getMyInfo({ dispatch, commit }, info) {
        return userService.getMyInfo(info).then(
          response => {
            return Promise.resolve(response.data.info);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    getWorkTime({ dispatch, commit }) {
        return userService.getWorkTime().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getHoursTime({ dispatch, commit },id) {
        return userService.getHoursTime(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    verifyMyInfo({ dispatch, commit }, info) {
        return userService.verifyMyInfo(info).then(
            response => {
                return Promise.resolve(response.data.info);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
    getInfoByHash({ dispatch, commit }) {
      return userService.getInfoByHash().then(
        response => {
          return Promise.resolve(response.user);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
      );
    },

    // パラメーターから社外アクセスコード認証
    checkOutsideAccessCodeByHash({ dispatch, commit }, data) {
        return userService.checkOutsideAccessCodeByHash(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getInfoCheck({ dispatch, commit}, info) {
        return userService.getMyInfo(info).then(
            response => {
                return Promise.resolve(response.data.infoCheck);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },    

  updateMyInfo({ dispatch, commit}, info) {
      return userService.updateMyInfo(info).then(
          response => {
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },

    updatePassword({ dispatch, commit }, info) {
      return userService.updatePassword(info).then(
        response => {
          dispatch("alertSuccess", response.message, { root: true });
          return Promise.resolve(true);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
      );
    },

    updateMyInfoDisplays(state, data) {
      if(!data) return;
     // state.info = data;
    },

    getDepartment({ dispatch, commit }, info) {
        return userService.getDepartment(info).then(
            response => {
               /* dispatch("alertSuccess", response.message, { root: true });*/
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    updateComment({ dispatch, commit}, info) {
        return userService.updateComment(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true});
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        )
    },

    updateDisplaySetting({ dispatch, commit}, info) {
        return userService.updateDisplaySetting(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true});
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        )
    },

    getAvatarUser({ dispatch, commit}) {
      return userService.getAvatarUser().then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject(false);
          }
      );
    },

    deleteImageProfile({ dispatch, commit}, data) {
        return userService.deleteImageProfile(data).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateUserImageProfileGroupWare({dispatch, commit}, data) {
      return userService.updateUserImageProfileGroupWare(data).then(
        response => {
          return Promise.resolve(response);
        },
        error => {
          dispatch("alertError", error, { root: true });
          return Promise.reject(false);
        }
      );
    },
};

const mutations = {



};

export const user = {
    namespaced: true,
    state,
    actions,
    mutations
};
