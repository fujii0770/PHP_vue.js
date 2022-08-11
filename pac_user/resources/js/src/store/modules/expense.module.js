import expenseService from "../../services/expense.service";

const state = {
t_app_id: '',
};

const actions = {
  getListReceived({ dispatch, commit }, info) {        
    return expenseService.getListReceived(info).then(
      response => {
        return Promise.resolve(response.data);
      },
      error => {
        dispatch("alertError", error, { root: true });
        return Promise.reject(false);
      }
    );
  }, 
}

const mutations = {
  get_t_app_id(state, value){
    state.t_app_id = value;
  },
};

export const expense = {
    namespaced: true,
    state,
    actions,
    mutations
};
