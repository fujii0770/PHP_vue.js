import timeCardDetailService from "../../services/time_card_detail.service";

const state = { };

const actions = {
    exportWorkListToCSV ({ dispatch, commit}, info) {
        return timeCardDetailService.exportWorkListToCSV(info).then(
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
};

export const timeCardDetail = {
    namespaced: true,
    state,
    actions,
    mutations
}
