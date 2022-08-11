import specialService from "../../services/special.service";

const state = {
    checkSpecialSend: false,
};
const actions = {
    getListReceived({dispatch, commit}, info) {
        return specialService.getListReceived(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },

    getListTemplate({dispatch, commit}, info) {
        return specialService.getListTemplate(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, {root: true});
                return Promise.reject(false);
            }
        );
    },
};
const mutations = {
    updateSpecialCircular(state, value) {
        state.checkSpecialSend = value;
    },
};

export const special = {
    namespaced: true,
    state,
    actions,
    mutations
};
