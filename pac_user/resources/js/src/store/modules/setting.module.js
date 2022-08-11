import settingService from "../../services/setting.service";

const state = {
  withdrawal_caution: 0, // 設定画面の確認コーション表示/非表示
 };

const actions = {
    getLimit({ dispatch, commit }, info) {        
        return settingService.getLimit(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
    
    getPasswordPolicy({ dispatch, commit }, info) {        
        return settingService.getPasswordPolicy(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },

    getProtection({ dispatch, commit }, info) {
        return settingService.getProtection(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },
};

const mutations = {
  setWithdrawalCaution(state, value){
    state.withdrawal_caution = value;
  }
};

export const setting = {
    namespaced: true,
    state,
    actions,
    mutations
};
