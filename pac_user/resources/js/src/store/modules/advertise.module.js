import advertisementService from "../../services/advertisement.service";

const state = { };

const actions = {
    getListAdvertisement({ dispatch, commit}, info) {
        return advertisementService.getListAdvertisement(info).then(
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

export const advertise = {
    namespaced: true,
    state,
    actions,
    mutations
};