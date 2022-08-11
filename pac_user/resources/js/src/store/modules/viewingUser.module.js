import viewingUserService from "../../services/viewingUser.service";

const actions = {
    updateViewingUser({ dispatch, commit }, info) {
      return viewingUserService.updateViewingUser(info).then(
          response => {
            dispatch("alertSuccess", response.message, { root: true });
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

export const viewingUser = {
    namespaced: true,
    actions,
    mutations
};
