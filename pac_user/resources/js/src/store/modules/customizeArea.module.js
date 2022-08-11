import customizeAreaService from "../../services/customizeArea.service";

const state = { };

const actions = {
    getCustomizeAreaList({ dispatch, commit}, info) {
        return customizeAreaService.getCustomizeAreaList(info).then(
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

export const customizeArea = {
    namespaced: true,
    state,
    actions,
    mutations
};