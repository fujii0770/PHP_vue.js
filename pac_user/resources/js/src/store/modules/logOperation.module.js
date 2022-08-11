import config from "../../app.config";
import logOperationService from "../../services/logOperation.service";

const state = {  };

const actions = {
  addLog({ dispatch, commit }, data) {
      var log_info = {
          auth_flg: config.LOG_AUTH_FLG,
          mst_display_id: "",
          mst_operation_id: "",
          result: 0,
          detail_info: "",
        };
        if(data.result != undefined) {
            log_info.result = data.result;
        }
        if(data.detail_info != undefined) {
            log_info.detail_info   = data.detail_info;
        }
        if(data.mst_display_id != undefined) {
            log_info.mst_display_id   = data.mst_display_id;
        }
        if(data.mst_operation_id != undefined) {
            log_info.mst_operation_id  = data.mst_operation_id;
        }

        if(data.action) {
            log_info.mst_display_id = config.OPERATION_MESSAGE[data.action][0];
            log_info.mst_operation_id = config.OPERATION_MESSAGE[data.action][1];
            if(!log_info.detail_info) {
              // result = 1 => message error, result = 0 => message success
              log_info.detail_info = config.OPERATION_MESSAGE[data.action][log_info.result == 1 ? 3 : 2];

              // process bind value
              if(data.params) {
                for(var param in data.params) {
                  var re = new RegExp(':'+param, 'ig');
                  log_info.detail_info = log_info.detail_info.replace(re, data.params[param]);
                }

                const user = JSON.parse(getLS('user'));
                log_info.detail_info = log_info.detail_info.replace(/:login_username/ig, user ? (user.family_name + user.given_name): '');
                log_info.detail_info = log_info.detail_info.replace(/:login_usermail/ig, user ? user.email : '');
              }
            }
        }

      return logOperationService.addLog(log_info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            return Promise.reject(false);
          }
      );
    },

    getLastLogin({ dispatch, commit }, info) {
      return logOperationService.getLastLogin(info).then(
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
};

export const logOperation = {
    namespaced: true,
    state,
    actions,
    mutations
};
