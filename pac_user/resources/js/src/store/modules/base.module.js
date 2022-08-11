import router from "../../router";

const state = {};

const actions = {
  handleError({ dispatch }, error) {
    if (error.status === 403) {
      dispatch("auth/logout", {}, { root: true });
    }
    if (error.status === 404) {
      router.go(-1);
    }
    dispatch("alert/error", error.message, { root: true });
  }
};

const mutations = {};

export default new {
  namespaced: true,
  state,
  actions,
  mutations
};
